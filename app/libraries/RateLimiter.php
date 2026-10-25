<?php
class RateLimiter
{
    protected $CI;
    private $limit = 100; // Maksimum istek sayısı
    private $timeWindow = 60; // Saniye cinsinden zaman penceresi

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
    }

    public function checkLimit($endpoint)
    {
        $ip = $this->CI->input->ip_address();

        // Veritabanında kaydı al
        $query = $this->CI->db->get_where('rate_limits', [
            'ip_address' => $ip,
            'endpoint' => $endpoint
        ]);

        $row = $query->row();

        if ($row) {
            // Son isteğin zaman farkını hesapla
            $timeDiff = time() - strtotime($row->last_request);

            if ($timeDiff > $this->timeWindow) {
                // Süre dolduysa sayacı sıfırla
                $this->CI->db->where(['ip_address' => $ip, 'endpoint' => $endpoint])
                    ->update('rate_limits', ['request_count' => 1, 'last_request' => date('Y-m-d H:i:s')]);
                return true;
            }

            if ($row->request_count >= $this->limit) {
                return false; // Limit aşıldı
            }

            // Sayaç artır
            $this->CI->db->where(['ip_address' => $ip, 'endpoint' => $endpoint])
                ->update('rate_limits', ['request_count' => $row->request_count + 1, 'last_request' => date('Y-m-d H:i:s')]);
        } else {
            // İlk defa istekte bulunuyorsa kaydet
            $this->CI->db->insert('rate_limits', [
                'ip_address' => $ip,
                'endpoint' => $endpoint,
                'request_count' => 1,
                'last_request' => date('Y-m-d H:i:s')
            ]);
        }
        return true;
    }
}
