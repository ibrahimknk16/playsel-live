<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @Author: İbrahim KONUK
 * Dijipin API Integration Library
 * Bu sınıf, Dijipin'in sağladığı API'ye güvenli ve performanslı bir şekilde bağlanmak için tasarlanmıştır.
 * ortamında en hızlı, en stabil cURL handling ve strict type validation kuralları ile kurgulanmıştır.
 */
class Dijipin
{
    protected $ci;
    protected $apiUrl;
    protected $apiToken;
    protected $timeout = 30;
    protected $orderPrefix = '';

    /**
     * CI3 Library Constructor
     * Parametreler config'den veya load->library('dijipin', $params) şeklinde gelebilir.
     */
    public function __construct($params = [])
    {
        $this->ci = &get_instance();

        // Sunucu/domain taşındığında tedarikçide "OrderID already exists" çakışmasını önlemek için
        $this->orderPrefix = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) (isset($params['orderPrefix']) ? $params['orderPrefix'] : getenv('DIJIPIN_ORDER_PREFIX')));

        // Parametre olarak gelmişse kullan, gelmemişse fallback değerlerini ata
        $this->apiUrl   = isset($params['apiUrl']) ? $params['apiUrl'] : 'https://api.dijipin.com/v1/';
        $this->apiToken = isset($params['apiToken']) ? $params['apiToken'] : 'bq7tdgbvng6oibhlfr8xdedty5c04y1fzymzyoshbv3t1f0d'; // Sandbox/Production Token

        if (isset($params['timeout'])) {
            $this->timeout = (int) $params['timeout'];
        }
    }

    /**
     * Dış API'den Yetkili Ürünleri Çeker
     * * @return array|false
     */
    public function getProducts()
    {
        $endpoint = 'getResellerAuthorizedProducts';
        $response = $this->request($endpoint, [], 'GET');

        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }

        return false;
    }

    public function getCategoryProducts($categoryID)
    {
        $endpoint = 'categoryProducts/' . (int)$categoryID;
        $response = $this->request($endpoint, [], 'GET');

        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }

        return false;
    }

    public function productData($productID)
    {
        $endpoint = 'productData/' . (int)$productID;
        $response = $this->request($endpoint, [], 'GET');

        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }

        return false;
    }

    /**
     * Birden fazla siparişi aynı anda oluşturur.
     *
     * @param array $orders
     * @return array
     */
    /**
     * Yerel sipariş ID'sini tedarikçiye gönderilecek (prefix'li) ID'ye çevirir.
     */
    public function supplierOrderID($localOrderID)
    {
        return $this->orderPrefix . trim((string) $localOrderID);
    }

    /**
     * Tedarikçiden dönen referans (prefix'li veya prefix'siz eski kayıt) değerini yerel sipariş ID'sine çevirir.
     */
    public function localOrderID($supplierReference)
    {
        $reference = trim((string) $supplierReference);
        if ($this->orderPrefix !== '' && strpos($reference, $this->orderPrefix) === 0) {
            $stripped = substr($reference, strlen($this->orderPrefix));
            if ($stripped !== '' && ctype_digit($stripped)) {
                return $stripped;
            }
        }
        return $reference;
    }

    public function sendOrdersMulti(array $orders)
    {
        if (empty($orders)) {
            return [];
        }

        $multiCurl = curl_multi_init();

        $curlArray = [];
        $responses = [];

        $url = rtrim($this->apiUrl, '/') . '/createOrder';

        foreach ($orders as $i => $order) {

            $payload = [
                'productID' => (int) ($order['productID'] ?? 0),
                'orderID'   => $this->supplierOrderID($order['orderID'] ?? ''),
                'quantity'  => (int) ($order['quantity'] ?? 0),
            ];

            /*
         * Sadece değer varsa gönder.
         * Boş user_id / server_id göndermiyoruz.
         */
            if (
                isset($order['user_id']) &&
                $order['user_id'] !== null &&
                $order['user_id'] !== ''
            ) {
                $payload['user_id'] = trim((string) $order['user_id']);
            }

            if (
                isset($order['server_id']) &&
                $order['server_id'] !== null &&
                $order['server_id'] !== ''
            ) {
                $payload['server_id'] = trim((string) $order['server_id']);
            }

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,

                // Dijipin V1 multipart/form-data bekliyor
                CURLOPT_POSTFIELDS     => $payload,

                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->apiToken,
                    'Accept: application/json',
                    'Cache-Control: no-cache'
                ],

                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => $this->timeout,
            ]);

            curl_multi_add_handle($multiCurl, $ch);

            $curlArray[$i] = $ch;

            /*
         * Local order ID'yi response ile eşleştirmek için saklıyoruz.
         */
            $responses[$i] = [
                'order_id' => $order['localOrderID'] ?? $order['orderID'],
                'request'  => $payload,
                'http_code' => 0,
                'curl_error' => '',
                'response' => null,
            ];
        }

        /*
     * Bütün request'leri paralel çalıştır.
     */
        $active = null;

        do {

            $multiStatus = curl_multi_exec(
                $multiCurl,
                $active
            );

            /*
         * CPU'yu %100'e vurdurmamak için
         * socket üzerinde bekliyoruz.
         */
            if ($active && $multiStatus === CURLM_OK) {
                $selectResult = curl_multi_select(
                    $multiCurl,
                    1.0
                );

                /*
             * curl_multi_select bazen -1 dönebilir.
             * Bu durumda çok kısa bekleyip devam ediyoruz.
             */
                if ($selectResult === -1) {
                    usleep(1000);
                }
            }
        } while (
            $active &&
            $multiStatus === CURLM_OK
        );

        /*
     * Response'ları topla.
     */
        foreach ($curlArray as $i => $ch) {

            $result = curl_multi_getcontent($ch);

            $httpCode = curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

            $curlError = curl_error($ch);

            $responses[$i]['http_code'] = $httpCode;
            $responses[$i]['curl_error'] = $curlError;

            /*
         * cURL bağlantı hatası
         */
            if ($result === false || $curlError !== '') {

                $responses[$i]['response'] = null;

                log_message(
                    'error',
                    'Dijipin Multi cURL Error' .
                        ' | Order ID: ' .
                        $responses[$i]['order_id'] .
                        ' | Error: ' .
                        $curlError
                );
            } else {

                $decoded = json_decode(
                    $result,
                    true
                );

                /*
             * JSON parse edilemezse raw response'u
             * kaybetmiyoruz.
             */
                if (
                    json_last_error() !== JSON_ERROR_NONE
                ) {

                    $responses[$i]['response'] = [
                        'status' => 'error',
                        'message' => 'Invalid JSON response',
                        'data' => [],
                        'raw_response' => $result
                    ];

                    log_message(
                        'error',
                        'Dijipin Multi JSON Parse Error' .
                            ' | Order ID: ' .
                            $responses[$i]['order_id'] .
                            ' | Response: ' .
                            $result
                    );
                } else {

                    $responses[$i]['response'] = $decoded;
                }
            }

            curl_multi_remove_handle(
                $multiCurl,
                $ch
            );

            curl_close($ch);
        }

        curl_multi_close($multiCurl);

        return $responses;
    }

    /**
     * Dış API Üzerinde Yeni Sipariş Oluşturur (Top-up / Voucher)
     * @param int $externalProductID Tedarikçi tarafındaki productID
     * @param string $clientOrderID Senin sistemindeki benzersiz orderID veya insertID
     * @param int $quantity Adet
     * @param array $requireData Müşterinin girdiği user_id, server_id, zone_id verileri
     * @return array|false
     */
    public function sendOrder($externalProductID, $clientOrderID, $quantity, $requireData = [])
    {
        $endpoint = 'createOrder';

        // Payload, senin V1 controller'ının beklediği strict kurallara göre mapleniyor
        $payload = [
            'productID' => (int) $externalProductID,
            'orderID'   => $this->supplierOrderID($clientOrderID),
            'quantity'  => (int) $quantity
        ];

        if (isset($requireData['user_id'])) {
            $payload['user_id'] = trim((string) $requireData['user_id']);
        }
        if (isset($requireData['server_id'])) {
            $payload['server_id'] = trim((string) $requireData['server_id']);
        }
        if (isset($requireData['zone_id'])) {
            $payload['zone_id'] = trim((string) $requireData['zone_id']);
        }

        $response = $this->request($endpoint, $payload, 'POST');

        if ($response && isset($response['status'])) {
            return $response;
        }

        return false;
    }

    /**
     * Dış API üzerinden sipariş durumunu sorgular
     * * @param string $clientOrderID
     * @return array|false
     */
    public function checkOrderStatus($clientOrderID)
    {
        $endpoint = 'getResellerOrderByID/' . $this->supplierOrderID($clientOrderID);
        $response = $this->request($endpoint, [], 'GET');

        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }

        return false;
    }

    /**
     * Güncel API Bakiyesini sorgular
     * * @return float|false
     */
    public function checkBalance()
    {
        $endpoint = 'getBalance';
        $response = $this->request($endpoint, [], 'GET');

        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return isset($response['balance']) ? (float)$response['balance'] : 0.00;
        }

        return false;
    }

    /**
     * MLBB oyuncu doğrulama (POST: user_id, server_id)
     * @return array|false
     */
    public function mlbbUserCheck($userID, $serverID)
    {
        return $this->request('mlbbUserCheck', [
            'user_id'   => trim((string) $userID),
            'server_id' => trim((string) $serverID),
        ], 'POST');
    }

    /**
     * Güvenli ve Performanslı HTTP cURL Motoru
     * * @param string $endpoint
     * @param array $params
     * @param string $method
     * @return array|false
     */
    private function request($endpoint, $params = [], $method = 'POST')
    {
        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpoint, '/');
        $ch  = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->apiToken,
            'Accept: application/json',
            'Cache-Control: no-cache'
        ];

        if (strtoupper($method) === 'GET') {
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); // V1 post() x-www-form-urlencoded bekliyor
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Üretim ortamında SSL güvenliği şart
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($output === false) {
            // Sistem mimarı refleksi: Debian üzerindeki log mekanizmasına sessizce yazıyoruz
            log_message('error', 'Dijipin API cURL Error: ' . $curlError . ' | URL: ' . $url);
            return false;
        }

        $decoded = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'Dijipin API JSON Parse Error. Raw Output: ' . $output);
            return false;
        }

        return $decoded;
    }
}
