<?php

/**
 * Dijipin PHP SDK
 *
 * @author İbrahim KONUK
 * @version 1.0.0
 */

class Dijipin
{
    /**
     * API Base URL
     * @var string
     */
    protected $apiUrl = 'https://api.dijipin.com/v1/';

    /**
     * API Token
     * @var string
     */
    protected $apiToken = '';

    /**
     * Request Timeout
     * @var int
     */
    protected $timeout = 30;

    // Domain/sunucu taşımalarında tedarikçide "OrderID already exists" çakışmasını önler.
    protected function supplierOrderID($clientOrderID)
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '', (string) getenv('DIJIPIN_ORDER_PREFIX')) . trim((string)$clientOrderID);
    }

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (isset($config['apiUrl'])) {
            $this->apiUrl = rtrim($config['apiUrl'], '/') . '/';
        }

        if (isset($config['apiToken'])) {
            $this->apiToken = trim($config['apiToken']);
        }

        if (isset($config['timeout'])) {
            $this->timeout = (int)$config['timeout'];
        }
    }

    /**
     * Yetkili ürünleri getir
     *
     * @return array|false
     */
    public function getProducts()
    {
        $response = $this->request('getResellerAuthorizedProducts', [], 'GET');

        if (isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }

        return false;
    }

    /**
     * Kategori ürünleri
     *
     * @param int $categoryID
     * @return array|false
     */
    public function getCategoryProducts($categoryID)
    {
        $response = $this->request('categoryProducts/' . (int)$categoryID, [], 'GET');

        if (isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }

        return false;
    }

    /**
     * Ürün detayları
     *
     * @param int $productID
     * @return array|false
     */
    public function productData($productID)
    {
        $response = $this->request('productData/' . (int)$productID, [], 'GET');

        if (isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }

        return false;
    }

    /**
     * Sipariş oluştur
     *
     * @param int $externalProductID
     * @param string $clientOrderID
     * @param int $quantity
     * @param array $requireData
     * @return array|false
     */
    public function sendOrder($externalProductID, $clientOrderID, $quantity, array $requireData = [])
    {
        $payload = [
            'productID' => (int)$externalProductID,
            'orderID'   => $this->supplierOrderID($clientOrderID),
            'quantity'  => (int)$quantity
        ];

        if (!empty($requireData['user_id'])) {
            $payload['user_id'] = trim((string)$requireData['user_id']);
        }

        if (!empty($requireData['server_id'])) {
            $payload['server_id'] = trim((string)$requireData['server_id']);
        }

        if (!empty($requireData['zone_id'])) {
            $payload['zone_id'] = trim((string)$requireData['zone_id']);
        }

        $response = $this->request('createOrder', $payload, 'POST');

        if (isset($response['status'])) {
            return $response;
        }

        return false;
    }

    /**
     * Sipariş sorgula
     *
     * @param string $clientOrderID
     * @return array|false
     */
    public function checkOrderStatus($clientOrderID)
    {
        $response = $this->request(
            'getResellerOrderByID/' . urlencode($this->supplierOrderID($clientOrderID)),
            [],
            'GET'
        );

        if (isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }

        return false;
    }

    /**
     * Bakiye sorgula
     *
     * @return float|false
     */
    public function checkBalance()
    {
        $response = $this->request('getBalance', [], 'GET');

        if (isset($response['status']) && $response['status'] === 'success') {
            return isset($response['balance']) ? (float)$response['balance'] : 0.00;
        }

        return false;
    }

    /**
     * HTTP Request
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @return array|false
     */
    private function request($endpoint, array $params = [], $method = 'POST')
    {
        $url = $this->apiUrl . ltrim($endpoint, '/');

        $ch = curl_init();

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
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'Dijipin PHP SDK/1.0'
        ]);

        $output = curl_exec($ch);

        if ($output === false) {
            $this->log(curl_error($ch));
            curl_close($ch);
            return false;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->log("HTTP {$httpCode} Response: {$output}");
            return false;
        }

        $decoded = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log('JSON Error: ' . json_last_error_msg());
            return false;
        }

        return $decoded;
    }

    /**
     * Log
     *
     * @param string $message
     * @return void
     */
    private function log($message)
    {
        error_log('[Dijipin SDK] ' . $message);
    }
}

/* Örnek Kullanım:

require 'Dijipin.php';

$dijipin = new Dijipin([
    'apiToken' => 'API_TOKENINIZ'
]);

$balance = $dijipin->checkBalance();

echo "<pre>";
print_r($balance);
*/
