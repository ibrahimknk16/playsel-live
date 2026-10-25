<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Cloudflare R2 - S3 Compatible Storage Library
 * Author: Gemini (For Ibrahim - Senior PHP Developer)
 * Environment: Debian 13, CI 3.x, PHP 8.x
 */
class R2_Storage
{

    private $access_key;
    private $secret_key;
    private $bucket;
    private $endpoint;
    private $region = 'auto'; // R2 varsayılanı

    public function __construct($params = [])
    {
        $this->access_key = $params['access_key'] ?? '';
        $this->secret_key = $params['secret_key'] ?? '';
        $this->bucket     = $params['bucket']     ?? '';
        $this->endpoint   = $params['endpoint']   ?? 'https://4d9e7972333b8e93a2a519326fb83bbd.r2.cloudflarestorage.com/cdn'; // Örn: <account_id>.r2.cloudflarestorage.com
    }

    /**
     * Dosya Yükle (PUT)
     */
    public function upload($file_path, $r2_path, $content_type = 'application/octet-stream')
    {
        if (!file_exists($file_path)) return false;
        return $this->_request('PUT', $r2_path, file_get_contents($file_path), $content_type);
    }

    /**
     * Dosya Sil (DELETE)
     */
    public function delete($r2_path)
    {
        return $this->_request('DELETE', $r2_path);
    }

    /**
     * Ana İstek Motoru (AWS Signature V4)
     */
    private function _request($method, $path, $body = '', $content_type = '')
    {
        $path = '/' . ltrim($path, '/');
        $amz_date = gmdate('Ymd\THis\Z');
        $datestamp = gmdate('Ymd');
        $payload_hash = hash('sha256', $body);

        $headers = [
            'host' => $this->endpoint,
            'x-amz-content-sha256' => $payload_hash,
            'x-amz-date' => $amz_date
        ];

        if ($content_type) {
            $headers['content-type'] = $content_type;
        }

        // 1. Canonical Request
        ksort($headers);
        $canonical_headers = "";
        foreach ($headers as $k => $v) $canonical_headers .= $k . ":" . trim($v) . "\n";
        $signed_headers = implode(';', array_keys($headers));

        $canonical_request = "$method\n/{$this->bucket}$path\n\n$canonical_headers\n$signed_headers\n$payload_hash";

        // 2. String to Sign
        $credential_scope = "$datestamp/$this->region/s3/aws4_request";
        $string_to_sign = "AWS4-HMAC-SHA256\n$amz_date\n$credential_scope\n" . hash('sha256', $canonical_request);

        // 3. Signature Calculation
        $k_date    = hash_hmac('sha256', $datestamp, "AWS4" . $this->secret_key, true);
        $k_region  = hash_hmac('sha256', $this->region, $k_date, true);
        $k_service = hash_hmac('sha256', "s3", $k_region, true);
        $k_signing = hash_hmac('sha256', "aws4_request", $k_service, true);
        $signature = hash_hmac('sha256', $string_to_sign, $k_signing);

        $authorization = "AWS4-HMAC-SHA256 Credential={$this->access_key}/$credential_scope, SignedHeaders=$signed_headers, Signature=$signature";

        // 4. cURL Execution
        $url = "https://{$this->endpoint}/{$this->bucket}$path";
        $curl_headers = ["Authorization: $authorization"];
        foreach ($headers as $k => $v) $curl_headers[] = "$k: $v";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
        if ($method === 'PUT') curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code >= 200 && $http_code < 300);
    }
}
