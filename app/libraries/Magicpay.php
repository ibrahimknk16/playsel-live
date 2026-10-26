<?php

declare(strict_types=1); // PHP 8.1+ Katı Tip Bildirimi

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @Author İbrahim KONUK
 * @version 1.0.0
 * (Dijigate Gateway API) PHP Library 8.1+ için geliştirilmiş bir entegrasyon kütüphanesidir.
 * Entegrasyon Dökümanı: https://digigate.gitbook.io/digigate-gateway-api-entegrasyon-dokumani
 */
class Magicpay
{

    private string $api_key = 'magicpay-TjXUbQyzjUhCitsyC1ivv5v1NjPU9dJoJ5RHGD-K62g';
    private string $secret_key = 'magicpay-fTfMK6enW3Ca763pph3moRgZSAct1-dNdLzLAyYaWgQ';
    private string $base_url = 'https://appapi.dijigate.com';
    private string $auth_version = 'V1';

    public function __construct(array $params = [])
    {
        if (!empty($params)) {
            $this->initialize($params);
        }
    }

    /**
     * Kütüphaneyi config dizisi ile başlatır.
     */
    public function initialize(array $params = []): void
    {
        $this->api_key      = (string)($params['api_key'] ?? $this->api_key);
        $this->secret_key   = (string)($params['secret_key'] ?? $this->secret_key);
        $this->auth_version = (string)($params['auth_version'] ?? $this->auth_version);

        if (isset($params['base_url'])) {
            $this->base_url = rtrim((string)$params['base_url'], '/');
        }
    }

    /**
     * Kriptografik olarak güvenli UUID v4 üretir (PHP 7.0+)
     */
    private function generateRndKey(): string
    {
        try {
            $data = random_bytes(16);
        } catch (Exception $e) {
            // random_bytes başarısız olursa fallback (Çok nadir bir OS hatası)
            $data = openssl_random_pseudo_bytes(16);
        }

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * SHA-256 ile x-signature değerini üretir
     */
    private function generateSignature(string $requestUrl, string $rndKey): string
    {
        $str = $requestUrl . $this->api_key . $this->secret_key . $rndKey;
        return hash('sha256', $str);
    }

    /**
     * API'ye güvenli cURL isteği atar
     */
    private function sendRequest(string $method, string $endpoint, array $data = []): array
    {
        $endpoint = ltrim($endpoint, '/');
        $requestUrl = $this->base_url . '/' . $endpoint;
    
        $method = strtoupper($method);
        if ($method === 'GET' && !empty($data)) {
            $requestUrl .= '?' . http_build_query($data);
        }

        $rndKey = $this->generateRndKey();
        $signature = $this->generateSignature($requestUrl, $rndKey);

        $headers = [
            'x-api-key: ' . $this->api_key,
            'x-rnd-key: '  . $rndKey,
            'x-auth-version: ' . $this->auth_version,
            'x-signature: ' . $signature,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        // Prod ve Test ortamı için dinamik SSL doğrulama
        $isProd = (strpos($this->base_url, 'dggateapi') === false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $isProd);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $isProd ? 2 : 0);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            }
        } elseif (in_array($method, ['PUT', 'DELETE'], true)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'status'  => 'error',
                'transport_error' => true,
                'message' => 'cURL Error: ' . $error
            ];
        }

        $decodedResponse = json_decode((string)$response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status'       => 'error',
                'transport_error' => true,
                'http_code'    => $httpCode,
                'message'      => 'Invalid JSON response from API',
                'raw_response' => $response
            ];
        }

        if (isset($decodedResponse['errors']) && is_array($decodedResponse['errors'])) {
            $decodedResponse['message'] = $decodedResponse['errors']['error_message']
                ?? $decodedResponse['errors']['errorMessage']
                ?? $decodedResponse['message']
                ?? null;
        }

        $decodedResponse = (array)$decodedResponse;
        $decodedResponse['http_code'] = $httpCode;

        return $decodedResponse;
    }

    /**
     * İstemci IP bilgisini çözümler (Framework bağımsız, Proxy/WAF destekli)
     */
    private function resolveClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return (string)$_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        // CLI ortamlarında $_SERVER tanımsız kalabileceğinden null coalescing ile fallback
        return (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    /**
     * 3D ödeme verisini normalize eder
     */
    private function normalize3dPaymentData(array $data): array
    {
        $data['paymentGroup']   = $data['paymentGroup'] ?? 'PRODUCT';
        $data['paymentChannel'] = $data['paymentChannel'] ?? 'WEB';
        $data['clientIp']       = $data['clientIp'] ?? $this->resolveClientIp();

        // Eğer items tek boyutlu bir diziyse (associative), onu çok boyutlu hale getir
        if (isset($data['items']) && is_array($data['items']) && array_keys($data['items']) !== range(0, count($data['items']) - 1)) {
            $data['items'] = [$data['items']];
        }

        return $data;
    }

    // ==========================================
    // ÖDEME (3D SECURE) İŞLEMLERİ
    // ==========================================

    public function init3dPayment(array $data): array
    {
        return $this->sendRequest('POST', 'payment/v1/card-payments/3ds-init', $this->normalize3dPaymentData($data));
    }

    public function initMobilePayment(array $data): array
    {
        $normalized = $this->normalize3dPaymentData($data);
        $normalized['paymentChannel'] = 'MOBILE';
        $normalized['installment'] = (int)($normalized['installment'] ?? 1);

        if (empty($normalized['items']) || !is_array($normalized['items'])) {
            return [
                'status' => 'error',
                'message' => 'Mobil ödeme için en az bir ürün kalemi gereklidir.',
            ];
        }

        return $this->sendRequest('POST', 'payment/v1/card-payments/mobilepay', $normalized);
    }

    public function complete3dPayment(string|int $paymentId): array
    {
        return $this->sendRequest('POST', 'payment/v1/card-payments/3ds-complete', [
            'paymentId' => (int)$paymentId
        ]);
    }

    public function query3dPayment(string|int $paymentId): array
    {
        return $this->sendRequest('GET', "payment/v1/card-payments/{$paymentId}");
    }

    public function query3dPaymentsMulti(array $paymentIds): array
    {
        $paymentIds = array_values(array_unique(array_filter(array_map('intval', $paymentIds), static function ($id) {
            return $id > 0;
        })));
        if (empty($paymentIds)) {
            return [];
        }

        $multiHandle = curl_multi_init();
        $handles = [];
        foreach ($paymentIds as $paymentId) {
            $requestUrl = $this->base_url . '/payment/v1/card-payments/' . $paymentId;
            $rndKey = $this->generateRndKey();
            $headers = [
                'x-api-key: ' . $this->api_key,
                'x-rnd-key: ' . $rndKey,
                'x-auth-version: ' . $this->auth_version,
                'x-signature: ' . $this->generateSignature($requestUrl, $rndKey),
                'Content-Type: application/json',
                'Accept: application/json',
            ];
            $handle = curl_init($requestUrl);
            $isProd = (strpos($this->base_url, 'dggateapi') === false);
            curl_setopt_array($handle, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => $isProd,
                CURLOPT_SSL_VERIFYHOST => $isProd ? 2 : 0,
            ]);
            curl_multi_add_handle($multiHandle, $handle);
            $handles[$paymentId] = $handle;
        }

        do {
            $status = curl_multi_exec($multiHandle, $active);
            if ($active) {
                curl_multi_select($multiHandle, 1.0);
            }
        } while ($active && $status === CURLM_OK);

        $responses = [];
        foreach ($handles as $paymentId => $handle) {
            $raw = curl_multi_getcontent($handle);
            $httpCode = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $error = curl_error($handle);
            $decoded = json_decode((string)$raw, true);
            $responses[$paymentId] = is_array($decoded)
                ? $decoded
                : [
                    'status' => 'error',
                    'transport_error' => true,
                    'http_code' => $httpCode,
                    'message' => $error !== '' ? 'cURL Error: ' . $error : 'Invalid JSON response from API',
                    'raw_response' => (string)$raw,
                ];
            curl_multi_remove_handle($multiHandle, $handle);
            curl_close($handle);
        }
        curl_multi_close($multiHandle);

        return $responses;
    }

    // ==========================================
    // İADE İŞLEMLERİ
    // ==========================================

    public function refundPayment(array $data): array
    {
        return $this->sendRequest('POST', 'payment/v1/refunds', $data);
    }

    // ==========================================
    // GENEL METODLAR
    // ==========================================

    public function get(string $endpoint, array $params = []): array
    {
        return $this->sendRequest('GET', $endpoint, $params);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->sendRequest('POST', $endpoint, $data);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->sendRequest('PUT', $endpoint, $data);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->sendRequest('DELETE', $endpoint, $data);
    }
}

/*
|--------------------------------------------------------------------------
| PHP 8.1+ Kullanım Örneği (Standalone / Service Integration)
|--------------------------------------------------------------------------
|
| // 1. Başlatma (Initialization)
| $magicpay = new Magicpay([
|     'api_key'      => 'SİZİN_API_ANAHTARINIZ',
|     'secret_key'   => 'SİZİN_GİZLİ_ANAHTARINIZ',
        // Test URL: https://appapi-dev.dijigate.com/
        // Live URL: https://appapi.dijigate.com/
|     'base_url'     => 'https://appapi-dev.dijigate.com/',
|     'auth_version' => 'V1'
| ]);
|
| // 2. 3D Ödeme Başlatma (Initiation)
| try {
|     $paymentData = [
|         'price'          => 150.00,
|         'paidPrice'      => 150.00,
|         'currency'       => 'TRY',
|         'bankOrderId'    => 'ORD-' . bin2hex(random_bytes(4)),
|         'redirectUrl'    => 'https://siteniz.com/payment/callback',
|         'items' => [
|             [
|                 'id'       => 'PRD-001',
|                 'name'     => 'Premium Lisans',
|                 'category' => 'Yazılım',
|                 'price'    => 150.00
|             ]
|         ],
|         'card' => [
|             'cardHolderName' => 'JOHN DOE',
|             'cardNumber'     => '4111111111111111',
|             'expireYear'     => '2030',
|             'expireMonth'    => '12',
|             'cvc'            => '000'
|         ]
|     ];
|
|     $initResult = $magicpay->init3dPayment($paymentData);
|
|     if (isset($initResult['htmlContent'])) {
|         // Formu ekrana basarak kullanıcıyı bankaya yönlendiriyoruz.
|         // Session veya DB'ye paymentId kaydı yapılması önerilir.
|         echo $initResult['htmlContent'];
|     } else {
|         throw new Exception($initResult['message'] ?? 'Ödeme başlatılamadı.');
|     }
|
| } catch (Exception $e) {
|     error_log("Magicpay Init Error: " . $e->getMessage());
|     echo "Bir hata oluştu: " . $e->getMessage();
| }
|
| // 3. 3D Ödeme Tamamlama (Callback / Completion)
| // Bankadan dönen request içerisinde paymentId aranır.
| $paymentId = $_POST['paymentId'] ?? $_GET['paymentId'] ?? null;
|
| if ($paymentId) {
|     $result = $magicpay->complete3dPayment($paymentId);
|
|     $status = match($result['paymentStatus'] ?? null) {
|         'SUCCESS' => "Ödeme başarılı. Sipariş: {$result['orderId']}",
|         'FAILURE' => "Ödeme başarısız: " . ($result['errorMessage'] ?? 'Bilinmeyen hata'),
|         default   => "İşlem durumu belirsiz."
|     };
|
|     echo $status;
| }
|
*/
