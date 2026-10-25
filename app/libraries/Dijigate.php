<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dijigate
{
    private $baseUrl = 'https://apigateway.dijigate.com/api';
    private $username;
    private $password;
    private $token;

    public function __construct()
    {
        // config/dijigate.php önerilir ama direkt yazıyorum
        $this->username = '5466987264';
        $this->password = '349349';
    }

    /* =====================================================
       TOKEN
    ===================================================== */

    private function createToken()
    {
        $url = $this->baseUrl . '/token/createToken';

        $payload = [
            'username' => $this->username,
            'password' => $this->password
        ];

        $response = $this->curl($url, $payload, false);

        if (!$response || !$response->success) {
            return null;
        }
        return $response->data->token;
    }

    /* =====================================================
       CREATE ORDER (ÖDEME BAŞLAT)
    ===================================================== */

    public function createOrder(array $data)
    {
        $this->token = $this->createToken();
        /*
            ZORUNLU ALANLAR:
            amount
            currencyCode (949)
            orderId
            cardHolderName
            cardNumber
            expireYear
            expireMonth
            cvc
            saleProduct
            saleProductPrice
            isSecureTransaction (true/false)
            callbackUrl
        */

        $data['ClientIP'] = $_SERVER['REMOTE_ADDR'];

        $url = $this->baseUrl . '/pos/createOrder';

        return $this->curl($url, $data, true);
    }

    public function createPaymentPage(array $data)
    {
        $this->token = $this->createToken();
        /*
            ZORUNLU ALANLAR:
            amount
            currencyCode (949)
            orderId
            cardHolderName
            cardNumber
            expireYear
            expireMonth
            cvc
            saleProduct
            saleProductPrice
            isSecureTransaction (true/false)
            callbackUrl
        */

        $data['ClientIP'] = $_SERVER['REMOTE_ADDR'];

        $url = $this->baseUrl . '/pos/redirect-payment-page';

        return $this->curl($url, $data, true);
    }

    /* =====================================================
       ORDER RESULT (SONUÇ SORGULA)
    ===================================================== */

    public function orderResult($paymentId, $orderId)
    {
        $this->token = $this->createToken();
        $url = $this->baseUrl . '/pos/orderResult'
            . '?paymentId=' . urlencode($paymentId)
            . '&orderId=' . urlencode($orderId);

        return $this->curl($url, [], true);
    }

    /* =====================================================
       CURL CORE
    ===================================================== */

    private function curl($url, $data = [], $auth = true)
    {
        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json'
        ];

        if ($auth && $this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => empty($data) ? null : json_encode($data),
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return null;
        }

        return json_decode($response);
    }
}
