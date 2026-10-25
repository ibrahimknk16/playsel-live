<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Exceptions extends CI_Exceptions
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show_404($page = '', $log_error = TRUE)
    {
        // Log’layabilirsiniz
        if ($log_error) {
            log_message('error', '404 sayfası: ' . $page);
        }

        // 404 başlığı
        $CI = &get_instance();
        $CI->output->set_status_header(404);

        // Kendi view’inizi basın (ister helpers, ister template kütüphanenizle)
        // $cfLang = $_SERVER['HTTP_CF_IPCOUNTRY'];
        // $cfLang = strtolower($cfLang);

        $langSegment = $CI->uri->segment(1);
        // cron2, payment gibi locale olmayan segmentler /404 yönlendirmesinde döngü oluşturur
        if (!preg_match('/^[a-z]{2}-[A-Z]{2}$/', (string)$langSegment)) {
            $langSegment = 'tr-TR';
        }

        redirect(topupcenter() . $langSegment . '/404');
        exit;  // asla CI default error_404’a dönmesin
    }
}
