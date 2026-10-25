<?php

class Checkout
{
    private $secretKey;
    private $baseUrl;
    private $processingChannelId;
    public function __construct(string $environment = 'sandbox')
    {
        // Production
        $secretKeyProduction = getenv('CHECKOUT_PRODUCTION_SECRET_KEY') ?: '';
        $productionChannelId = getenv('CHECKOUT_PRODUCTION_CHANNEL_ID') ?: '';
        // Sandbox
        $secretKeySandbox    = getenv('CHECKOUT_SANDBOX_SECRET_KEY') ?: '';
        $sandboxChannelId    = getenv('CHECKOUT_SANDBOX_CHANNEL_ID') ?: '';

        $this->processingChannelId = $environment == 'production' ? $productionChannelId : $sandboxChannelId;
        $this->secretKey = $environment == 'production' ? $secretKeyProduction : $secretKeySandbox;
        $this->baseUrl = $environment == 'production' ? 'https://api.checkout.com' : 'https://api.sandbox.checkout.com';
    }

    private function sendRequest(string $method, string $path, array $data)
    {
        $url = $this->baseUrl . $path;
        $ch  = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
            'Cko-Idempotency-Key: ' . hash('sha256', $reference . '|' . $amount . '|' . $currency),
            'Cko-Version: 2025-06-11'
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
            // hata detayını inceleyin
            //throw new Exception("HTTP $httpCode: $resp", $httpCode);
            throw new RuntimeException('Checkout provider request failed.', $httpCode);
        }

        $decoded = json_decode($resp, true);
        return $decoded;
    }

    public function createPayment(
        array  $source,
        int    $amount,
        string $currency,
        string $reference,
        bool   $capture = true,
        bool   $threeDS = false
    ): array {
        $body = [
            'source'    => $source,
            'amount'    => $amount,
            'currency'  => $currency,
            'reference' => $reference,
            'capture'   => $capture,
        ];

        // **YENİ**: processing channel ID’yi ekliyoruz
        if ($this->processingChannelId) {
            $body['processing_channel_id'] = $this->processingChannelId;
        }

        if ($threeDS) {
            $body['3ds'] = ['enabled' => true];
        }

        return $this->sendRequest('POST', '/payments', $body);  // :contentReference[oaicite:1]{index=1}
    }

    public function createHostedPaymentSession(
        $amount,
        string $currency,
        string $reference,
        string $returnUrl = 'https://topupcenter.com/tr-TR/test/functionCheckoutCallback',
        string $notifyUrl = 'https://topupcenter.com/tr-TR/test/functionCheckoutCallback',
        string $failureUrl = 'https://topupcenter.com/tr-TR/test/functionCheckoutCallback',
        string $success_url = 'https://topupcenter.com/tr-TR/test/functionCheckoutCallback',
        string $cancel_url = 'https://topupcenter.com/tr-TR/test/functionCheckoutCallback',
        string $locale = 'TR'
    ): string {
        $body = [
            'amount'                => $amount,
            'currency'              => $currency,
            'reference'             => $reference,
            'return_url'            => $returnUrl,
            'notify_url'            => $notifyUrl,
            'failure_url'           => $failureUrl,
            'success_url'           => $success_url,
            'cancel_url'            => $cancel_url,
            'billing' => [
                'address' => [
                    'line1' => '',
                    'city'  => '',
                    'state' => '',
                    'zip'   => '',
                    'country' => 'TR'
                ],
            ],
        ];
        if ($this->processingChannelId) {
            $body['processing_channel_id'] = $this->processingChannelId;
        }
        if ($locale) {
            $body['locale'] = $locale;
        }

        $response = $this->sendRequest('POST', '/hosted-payments', $body);
        pre($response); // Debug amaçlı, gerçek uygulamada kaldırılabilirdie;
        die;
        if (empty($response['href'])) {
            throw new Exception('Hosted Payments Page URL bulunamadı: ' . json_encode($response));
        }

        return $response['href'];
    }

    /**
     * Kartla 3DS’li ödeme
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
        return $this->createPayment($source, $minor, $currency, $reference, true, true);
    }

    /**
     * Token’la ödeme
     */
    public function paymentWithToken(
        string $token,
        float  $amount,
        string $currency,
        string $reference
    ): array {
        $source = [
            'type'  => 'token',
            'token' => $token,
        ];
        $minor = (int) round($amount * 100);
        return $this->createPayment($source, $minor, $currency, $reference, true, false);
    }

    // … getPaymentDetails(), capturePayment(), refundPayment() aynı kalabilir
}
