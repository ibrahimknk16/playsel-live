<?php

class Checkout2
{
    private string $environment;
    private string $baseUrl;
    private string $tokenUrl;
    private string $clientId;
    private string $clientSecret;
    private ?string $accessToken = null;
    private int $tokenExpiresAt = 0;
    private string $processingChannelId;

    /**
     * @param 'sandbox'|'production' $environment
     * @param string $clientId      OAuth2 Client ID (Access key identifier)
     * @param string $clientSecret  OAuth2 Client Secret (Access key secret)
     */
    public function __construct()
    {
        $environment = 'production'; // or 'sandbox'
        $this->environment  = $environment;
        $this->clientId     = 'ack_rbqlzvngbupuli5zjyyxnndt3i'; //  ack_uys4747hhlcu5cimr3ztm6s3ru - sandbox
        $this->clientSecret = '6UlQaSajmfhnD0E6F8vWqe2avXfrkBaiM8dNE6FTBC-4nhMS3EUQupElEB-1Hec8t-9Mx5H5xlAfCVuSLZ43Tg'; //yTnckYYska7nss6Vx3Ghyo6Gtgshd2d5XZnp1vdVsCV3XTjXU63iiYrFXlJRxS9XzXr2AbCe0q1_dVOR3fkTZQ - sandbox

        // API ve Token uç noktaları
        $this->baseUrl  = $environment === 'production'
            ? 'https://api.checkout.com'
            : 'https://api.sandbox.checkout.com';
        $this->tokenUrl = $environment === 'production'
            ? 'https://access.checkout.com/connect/token'
            : 'https://access.sandbox.checkout.com/connect/token';

        // İşlem kanal ID’si (sandbox ve prod ayrı)
        $this->processingChannelId = $environment === 'production'
            ? 'pc_374hnz44ec3ezg2d4rbi6lilvq'
            : 'pc_pgssb6uovfnu7ila66teny6fiu';
    }

    /**
     * OAuth2 Client Credentials ile token alır ve cache’ler.
     * 
     * @return string Access token (JWT)
     * @throws Exception
     */
    private function getAccessToken(): string
    {
        // Hâlâ geçerli bir token varsa direkt döner
        if ($this->accessToken !== null && time() < $this->tokenExpiresAt) {
            return $this->accessToken;
        }

        $ch = curl_init($this->tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode("{$this->clientId}:{$this->clientSecret}")
            ],
            CURLOPT_POSTFIELDS     => http_build_query(['grant_type' => 'client_credentials']),
        ]);

        $resp     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            throw new Exception('Token isteği hatası: ' . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Token alınamadı, HTTP $httpCode: $resp");
        }

        $data = json_decode($resp, true);
        if (empty($data['access_token'])) {
            throw new Exception('Access token yanıtı bekleniyor: ' . $resp);
        }

        $this->accessToken   = $data['access_token'];
        $expiresIn           = $data['expires_in'] ?? 3600;
        // 60 sn güvenlik marjı
        $this->tokenExpiresAt = time() + $expiresIn - 60;

        return $this->accessToken;
    }

    /**
     * Genel HTTP isteği. JWT token’ı alır ve Bearer olarak ekler.
     *
     * @param string     $method  HTTP method (GET, POST, ...)
     * @param string     $path    API yolu (örn. '/payments')
     * @param array|null $data    Gönderilecek JSON-verisi
     * @return array              JSON-decode edilmiş yanıt
     * @throws Exception
     */
    private function sendRequest(string $method, string $path, ?array $data = null): array
    {
        $url = $this->baseUrl . $path;
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Bearer <token>
        $token = $this->getAccessToken();
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Cko-Idempotency-Key: ' . uniqid(),
        ];

        if ($data !== null) {
            $json = json_encode($data, JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $headers[] = 'Content-Length: ' . strlen($json);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $resp     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("HTTP $httpCode: $resp", $httpCode);
        }

        return json_decode($resp, true);
    }

    /**
     * Kartla 3DS’li ödeme örneği.
     */
    public function paymentWithCard(
        string $number,
        int    $expiryMonth,
        int    $expiryYear,
        string $cvv,
        string $name,
        float  $amount,
        string $currency,
        string $reference
    ): array {
        $source = [
            'type'         => 'card',
            'number'       => $number,
            'expiry_month' => $expiryMonth,
            'expiry_year'  => $expiryYear,
            'cvv'          => $cvv,
            'name'         => $name,
        ];
        $minor = (int) round($amount * 100);

        $body = [
            'source'                 => $source,
            'amount'                 => $minor,
            'currency'               => $currency,
            'reference'              => $reference,
            'capture'                => true,
            'processing_channel_id'  => $this->processingChannelId,
            '3ds'                    => ['enabled' => true],
        ];

        return $this->sendRequest('POST', '/payments', $body);
    }

    /**
     * Hosted Payments Page oturumu oluşturur.
     */
    public function createHostedPaymentSession(
        float  $amount,
        string $currency,
        string $reference,
        string $returnUrl,
        string $notifyUrl,
        string $failureUrl,
        string $successUrl,
        string $cancelUrl,
        string $locale = 'TR'
    ): string {
        $body = [
            'amount'                => (int) round($amount * 100),
            'currency'              => $currency,
            'reference'             => $reference,
            'return_url'            => $returnUrl,
            'notify_url'            => $notifyUrl,
            'failure_url'           => $failureUrl,
            'success_url'           => $successUrl,
            'cancel_url'            => $cancelUrl,
            'processing_channel_id' => $this->processingChannelId,
            'locale'                => $locale,
            'billing' => [
                'address' => [
                    'line1'   => '',
                    'city'    => '',
                    'state'   => '',
                    'zip'     => '',
                    'country' => 'TR',
                ],
            ],
        ];

        $response = $this->sendRequest('POST', '/hosted-payments', $body);

        if (empty($response['href'])) {
            throw new Exception('Hosted Payments URL bulunamadı: ' . json_encode($response));
        }

        return $response['href'];
    }

    /**
     * Ödeme linki detaylarını getirir.
     */
    public function getPaymentLinkDetails(string $linkId): array
    {
        return $this->sendRequest('GET', "/payment-links/{$linkId}");
    }

    // ... diğer metodlar (capturePayment, refundPayment, vb.) aynı şekilde devam eder.
}
