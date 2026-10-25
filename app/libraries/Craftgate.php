<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");
require_once '/home/dijipin-api/htdocs/api.dijipin.com/vendor/autoload.php';
class Craftgate
{
    private $craftgate;

    public function __construct()
    {
        $testMode = false; // true ise test modunda çalışır, false ise canlı modda çalışır
        if ($testMode) {
            $this->craftgate = new \Craftgate\Craftgate(array(
                'apiKey' => 'sandbox-lNLUroSdGfDXnBCbchYbgunsNNzEyNTn', // sdqYNNXfYY4x9a1856wmTEIByJHQoqym
                'secretKey' => 'sandbox-rqGCXquRIHtVbNJsfLbKJvxxpVHQErhc', // iK33CDoCfaEFc1K0uUcgt11UU3GTDCSQ
                'baseUrl' => 'https://sandbox-api.craftgate.io', // https://api.craftgate.io
            ));
        } else {
            $this->craftgate = new \Craftgate\Craftgate(array(
                'apiKey' => 'HRLFikPQPbB039cFzcVL7H7Sf2HxdScR', // sdqYNNXfYY4x9a1856wmTEIByJHQoqym
                'secretKey' => 'MQWGVYrHaqCA5mYDmiwsY4bcTTnF9DUw', // iK33CDoCfaEFc1K0uUcgt11UU3GTDCSQ
                'baseUrl' => 'https://api.craftgate.io', // https://api.craftgate.io
            ));
        }
    }

    public function payment($oid, $productName, $total, $currency = 'TRY') // TRY USD EUR GBP CNY ARS BRL AED IQD AZN KZT KWD SAR BHD RUB JPY EGP
    {
        $request = array(
            'price' => $total,
            'paidPrice' => $total,
            'currency' => $currency,
            'paymentGroup' => \Craftgate\Model\PaymentGroup::PRODUCT,
            'conversationId' => $oid,
            'installment' => 1,
            'retry' => true,
            'clientIp' => $_SERVER['REMOTE_ADDR'],
            'callbackUrl' => topupcenter() . 'payment/callback',
            'items' => array(
                array(
                    'externalId' => \Craftgate\Util\Guid::generate(),
                    'name' => $productName,
                    'price' => $total
                )
            )
        );
        $response = $this->craftgate->payment()->initCheckoutPayment($request);
        $response = json_decode($response);
        return $response;
    }

    public function threeDSPayment($oid, $productName, $total, $currency = 'TRY')
    {
        $request = array(
            'price' => $total,
            'paidPrice' => $total,
            'currency' => $currency,
            'installment' => 1,
            'paymentGroup' => \Craftgate\Model\PaymentGroup::PRODUCT,
            'callbackUrl' => topupcenter() . 'payment/callback',
            'card' => array(
                'cardHolderName' => 'John Doe',
                'cardNumber' => '4111111111111111',
                'expireYear' => '2030',
                'expireMonth' => '12',
                'cvc' => '123'
            ),
            'buyer' => array(
                'id' => '1',
                'name' => 'John',
                'surname' => 'Doe',
                'identityNumber' => '12345678901',
                'email' => 'john@example.com',
                'gsmNumber' => '+905555555555',
                'registrationAddress' => 'Istanbul',
                'city' => 'Istanbul',
                'country' => 'Turkey',
                'zipCode' => '34000'
            ),
            'items' => array(
                array(
                    'externalId' => \Craftgate\Util\Guid::generate(),
                    'name' => $productName,
                    'price' => $total
                )
            )
        );

        $response = $this->craftgate->payment()->init3DSPayment($request);
        $reponse = json_decode($response);
        return $reponse; // HTML form içeren yanıt gelecek
    }

    public function paymentWithStoredCard($cardUserKey, $cardToken, $oid, $total)
    {
        $request = array(
            'price' => $total,
            'paidPrice' => $total,
            'installment' => 1,
            'currency' => 'TRY',
            'conversationId' => $oid,
            'paymentGroup' => \Craftgate\Model\PaymentGroup::PRODUCT,
            'cardUserKey' => $cardUserKey,
            'cardToken' => $cardToken,
            'items' => array(
                array(
                    'externalId' => \Craftgate\Util\Guid::generate(),
                    'name' => 'Tekrarlı Üyelik Ödemesi',
                    'price' => $total
                )
            )
        );

        $response = $this->craftgate->payment()->createPayment($request);
        return $response;
    }


    public function orderCheck($token)
    {
        $response = $this->craftgate->payment()->retrieveCheckoutPayment($token);
        return json_decode($response);
    }

    public function orderRefund($paymentID)
    {
        $request = array( //317304207
            'paymentId' => $paymentID,
            'refundDestinationType' => \Craftgate\Model\RefundDestinationType::PROVIDER
        );
        $response = $this->craftgate->payment()->refundPayment($request);
        return json_decode($response);
    }
}
