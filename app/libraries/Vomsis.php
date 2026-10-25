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
        curl_setopt_array($curl, [
            CURLOPT_URL => self::$url . 'authenticate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query(['app_key' => self::$appKey, 'app_secret' => self::$appSecret]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($curl);
        if ($response === false) {
            log_message('error', 'Vomsis authentication failed: ' . curl_error($curl));
            curl_close($curl);
            return null;
        }
        curl_close($curl);
        $decoded = json_decode($response, true);
        if (!is_array($decoded) || ($decoded['status'] ?? null) !== 'success') {
            log_message('error', 'Vomsis authentication API error: ' . (is_array($decoded) ? ($decoded['error_type'] ?? '') . ' ' . ($decoded['message'] ?? '') : 'invalid response'));
            return null;
        }
        return $decoded['token'] ?? null;
    }

    public function vomsisTransactions($beginDate = null, $endDate = null)
    {
        $token = $this->vomsisAuth();
        if (!$token) return [];
        $beginDate = $beginDate ?: date('d-m-Y H:i:s', strtotime('-24 hours'));
        $endDate = $endDate ?: date('d-m-Y H:i:s');
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => self::$url . 'transactions?beginDate=' . urlencode($beginDate) . '&endDate=' . urlencode($endDate),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Authorization: Bearer ' . $token],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $decoded = json_decode((string)$response, true);
        return is_array($decoded) ? $decoded : [];
    }
}
