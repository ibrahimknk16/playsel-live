<?php
class Vomsis
{
    protected static $url, $appKey, $appSecret;
    public function __construct()
    {
        self::$url = rtrim(self::env('VOMSIS_API_URL') ?: 'https://developers.vomsis.com/api/v2/', '/') . '/';
        self::$appKey = self::env('VOMSIS_APP_KEY');
        self::$appSecret = self::env('VOMSIS_APP_SECRET');
    }

    protected static function env($name)
    {
        $value = getenv($name);
        if ($value === false || $value === '') $value = $_ENV[$name] ?? ($_SERVER[$name] ?? '');
        return trim((string)$value);
    }

    public function vomsisAuth()
    {
        if (!self::$appKey || !self::$appSecret) {
            log_message('error', 'Vomsis credentials are not configured.');
            return null;
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(CURLOPT_URL => self::$url . 'authenticate', CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CUSTOMREQUEST => 'POST', CURLOPT_POSTFIELDS => http_build_query(['app_key' => self::$appKey, 'app_secret' => self::$appSecret]), CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded')));

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            log_message('error', 'Vomsis authentication failed: ' . $error);
            return null;
        }

        curl_close($curl);

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'Vomsis authentication returned invalid JSON.');
            return null;
        }
        if (!is_array($decoded) || ($decoded['status'] ?? null) !== 'success') {
            log_message('error', 'Vomsis authentication API error: ' . (is_array($decoded) ? ($decoded['error_type'] ?? '') . ' ' . ($decoded['message'] ?? '') : 'invalid response'));
            return null;
        }
        return $decoded['token'] ?? null;
    }

    public function vomsisTransactions($beginDate = null, $endDate = null)
    {
        $curl = curl_init();
        // şu andan önceki 1 dakika ile şu andan sonraki 1 dakika arasındaki işlemleri çekelim
        $begindate = $beginDate ?: date('d-m-Y H:i:s', strtotime('-24 hours'));
        $enddate = $endDate ?: date('d-m-Y H:i:s');

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::$url . 'transactions?beginDate=' . urlencode($begindate) . '&endDate=' . urlencode($enddate),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Authorization: Bearer ' . (string)$this->vomsisAuth()
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        $response = json_decode($response, true);
        return $response;
    }
    
}
