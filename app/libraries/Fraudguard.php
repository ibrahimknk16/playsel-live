<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FraudGuard - Advanced KYC & Anti-Fraud Library for CI3
 * Author: Ibrahim (Senior PHP Developer)
 */
class FraudGuard
{

    protected $CI;
    private $risk_score = 0;
    private $logs = [];

    // Ayarlar - Bunları config/config.php içine de taşıyabilirsin
    private $proxycheck_api_key = 'SENIN_API_KEYIN';
    private $allowed_email_domains = ['gmail.com', 'hotmail.com', 'outlook.com', 'icloud.com', 'apple.com'];

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('url');
    }

    /**
     * Ana Denetleyici Fonksiyon
     * @param array $user_data (email, phone, user_agent, fingerprint vb.)
     * @return array
     */
    public function check_risk($user_data = [])
    {
        $ip = $this->CI->input->ip_address();
        $this->risk_score = 0;
        $this->logs = [];

        // 1. Email Kontrolü
        if (isset($user_data['email'])) {
            $this->_verify_email($user_data['email']);
        }

        // 2. IP Analizi (ProxyCheck & IP-API)
        $this->_verify_ip($ip);

        // 3. User Agent & Fingerprint Kontrolü
        if (isset($user_data['fingerprint'])) {
            $this->_verify_fingerprint($user_data['fingerprint']);
        }

        // 4. Hız Kontrolü (Velocity Check) - DB sorgusu gerektirir
        $this->_verify_velocity($ip, $user_data['email'] ?? null);

        return [
            'score' => $this->risk_score, // 0-100 arası (Yüksek = Tehlikeli)
            'status' => $this->risk_score > 70 ? 'REJECT' : ($this->risk_score > 35 ? 'REVIEW' : 'PASS'),
            'logs' => $this->logs,
            'ip' => $ip
        ];
    }

    private function _verify_email($email)
    {
        $parts = explode('@', $email);
        $domain = strtolower(end($parts));

        // Domain Kısıtlaması
        if (!in_array($domain, $this->allowed_email_domains)) {
            $this->risk_score += 40;
            $this->logs[] = "Email domain not in whitelist: $domain";
        }

        // Plus Tag Check (isim+test@gmail.com)
        if (strpos($parts[0], '+') !== false) {
            $this->risk_score += 20;
            $this->logs[] = "Email contains plus-alias (potential farmer)";
        }
    }

    private function _verify_ip($ip)
    {
        // ProxyCheck.io API (Performans için cURL kullanıyoruz)
        $url = "https://proxycheck.io/v3/{$ip}?vpn=1&asn=1";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($response[$ip])) {
            $data = $response[$ip];

            if (($data['proxy'] ?? 'no') === 'yes') {
                $this->risk_score += 50;
                $this->logs[] = "Proxy/VPN detected via ProxyCheck";
            }

            if (($data['type'] ?? '') === 'Business') {
                $this->risk_score += 30;
                $this->logs[] = "Datacenter/Hosting ASN detected";
            }
        }
    }

    private function _verify_fingerprint($fp)
    {
        // Veritabanında bu fingerprint ile kaç farklı email kayıtlı?
        $this->CI->db->where('fingerprint', $fp);
        $this->CI->db->where('created_at >', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $count = $this->CI->db->count_all_results('users');

        if ($count > 1) {
            $this->risk_score += ($count * 15);
            $this->logs[] = "Multiple accounts linked to this device: $count";
        }
    }

    private function _verify_velocity($ip, $email)
    {
        // Son 1 saat içinde aynı IP'den gelen kayıt denemesi
        $this->CI->db->where('ip_address', $ip);
        $this->CI->db->where('created_at >', date('Y-m-d H:i:s', strtotime('-1 hour')));
        $count = $this->CI->db->count_all_results('registration_logs');

        if ($count > 2) {
            $this->risk_score += 25;
            $this->logs[] = "High registration velocity from IP";
        }
    }
}
