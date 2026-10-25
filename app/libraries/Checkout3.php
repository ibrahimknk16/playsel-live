<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Composer autoload (Checkout.com SDK veya Guzzle yüklüyse de buradan çekersiniz)
require_once FCPATH . 'vendor/autoload.php';

class Checkout3
{
    /** @var CI_Controller */
    protected $ci;

    /** @var string */
    protected $secretKey;

    /** @var string */
    protected $baseUrl;

    public function __construct()
    {
        // CI superobject
        $this->ci =& get_instance();

        // Config dosyasını yükle
        $this->ci->config->load('checkout', true);
        $this->secretKey = $this->ci->config->item('checkout_secret_key', 'checkout');
        $this->baseUrl   = rtrim($this->ci->config->item('checkout_base_url',   'checkout'), '/'); //pk_5gfimc4k62em6hfmahzzim2ei4c
    }

    /**
     * Hosted Payments Page oturumu oluşturur.
     *
     * @param array $data Checkout.com’un API dokümanındaki JSON gövde parametreleri
     * @return array  API cevabı (assosiative array)
     * @throws Exception  HTTP hatalarında veya cURL hatalarında
     */
    public function createHostedPaymentSession(array $data): array
    {
        return $this->request('POST', '/hosted-payments', $data);
    }

    /**
     * Var olan bir hosted session bilgisini çeker.
     *
     * @param string $sessionId
     * @return array
     * @throws Exception
     */
    public function getHostedPaymentSession(string $sessionId): array
    {
        return $this->request('GET', '/hosted-payments/' . urlencode($sessionId));
    }

    private function request(string $method, string $path, array $data = null): array
    {
        $url = $this->baseUrl . $path;
        $ch  = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $headers = [
            "Authorization: Bearer {$this->secretKey}",
            "Cko-Idempotency-Key: " . uniqid('', true),
            "Content-Type: application/json",
        ];
        $data['processing_channel_id'] = 'pc_374hnz44ec3ezg2d4rbi6lilvq';
        if ($data !== null) {
            $json = json_encode($data, JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $headers[] = 'Content-Length: ' . strlen($json);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // İsteği yap
        $response = curl_exec($ch);
        $info     = curl_getinfo($ch);
        $err      = curl_error($ch);
        curl_close($ch);
        
        // cURL hatası
        if ($err) {
            log_message('error', "Checkout3 cURL error: {$err}");
            throw new Exception("cURL error: {$err}");
        }

        // HTTP 2xx dışında dönmüşse hata fırlat
        if ($info['http_code'] < 200 || $info['http_code'] >= 300) {
            // Opsiyonel: tam cevabı logla
            log_message('error', "Checkout3 HTTP {$info['http_code']}: {$response}");
            throw new Exception("HTTP {$info['http_code']}: {$response}", $info['http_code']);
        }

        // JSON decode et ve dön
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Checkout: ' . json_last_error_msg());
        }
        return $decoded;
    }
}
