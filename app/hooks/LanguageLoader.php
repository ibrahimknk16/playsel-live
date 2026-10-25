<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LanguageLoader
{
    public function initialize()
    {
        $CI =& get_instance();
        $CI->load->helper('url');

        // 0) Sadece topupcenter.com domain’ine uygula
        $host     = $CI->input->server('HTTP_HOST');
        $baseHost = parse_url(rtrim(topupcenter(), '/'), PHP_URL_HOST);
        if ($host !== $baseHost) {
            return;
        }

        // 1) Ham REQUEST_URI’i al ve query string’den ayır
        $reqPath = parse_url($CI->input->server('REQUEST_URI'), PHP_URL_PATH);

        // 2) Eğer index.php kullanıyorsan baştan çıkar
        $index = $CI->config->item('index_page') ?: 'index.php';
        if ($index !== '') {
            $reqPath = preg_replace('#^/'.$index.'#', '', $reqPath);
        }

        // 3) Ön ve son slash’ları kırp, segment’lere böl
        $uri      = trim($reqPath, '/');
        $segments = $uri === '' ? [] : explode('/', $uri);

        // 4) Diller ve exempt controller’lar
        $default   = $CI->config->item('default_language') ?: 'en';
        $langs     = ['tr','en','de'];
        $exempt    = ['v1','callback','cron','cron2'];

        // 5) Exempt controller mı? → hemen default ile dön
        if (isset($segments[0]) && in_array($segments[0], $exempt, true)) {
            define('CURRENT_LANGUAGE', $default);
            $CI->config->set_item('language', $default);
            return;
        }

        // 6) Zaten prefix’li bir dil kodu mu? → onu kullan, dön
        if (isset($segments[0]) && in_array($segments[0], $langs, true)) {
            define('CURRENT_LANGUAGE', $segments[0]);
            $CI->config->set_item('language', $segments[0]);
            return;
        }

        // 7) Hiçbiri değil → CF-IPCOUNTRY’den al, yoksa default
        $cf   = strtolower($CI->input->server('HTTP_CF_IPCOUNTRY') ?: '');
        $lang = in_array($cf, $langs, true) ? $cf : $default;

        // 8) Yönlendir: orijinal path + query string’i koru
        $rest = $uri ? '/'.$uri : '';
        $qs   = $_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '';
        $target = rtrim(topupcenter(), '/').'/'.$lang.$rest.$qs;

        redirect($target,'location',302);
        exit;
    }
}
