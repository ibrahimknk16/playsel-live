<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Render the static maintenance page before any application controller runs.
 */
function maintenance_mode()
{
    $ci = get_instance();

    if (!$ci->db->table_exists('site_settings')) {
        return;
    }

    $setting = $ci->db
        ->select('setting_value')
        ->where('setting_key', 'maintenance_mode')
        ->get('site_settings')
        ->row();

    if (!$setting || (int) $setting->setting_value !== 1) {
        return;
    }

    $content = $ci->load->view('maintenance', [], true);

    $ci->output
        ->set_status_header(503)
        ->set_header('Retry-After: 3600')
        ->set_content_type('text/html', 'UTF-8')
        ->set_output($content);
    $ci->output->_display();
    exit;
}
