<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mailer
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function send_otp($to_email, $otp_code, $lang_code = 'tr', $user_name = '')
    {
        $this->CI->lang->load('email', $lang_code);
        $body = $this->CI->load->view('emails/two_factor_auth', [
            'otp_code'       => $otp_code,
            'userName'       => $user_name,
            'expiry_minutes' => 3
        ], true);
        $subject = $this->CI->lang->line('otp_subject') ?: 'Playsel · Doğrulama kodun';
        return sendEmail($to_email, $subject, $body);
    }

    public function send_email_verification($to_email, $verify_url, $user_name = '', $expiry = '24 saat')
    {
        $body = $this->CI->load->view('emails/email_verification', [
            'verification_url' => $verify_url,
            'userName'         => $user_name,
            'expiry_minutes'   => $expiry
        ], true);
        $subject = 'Playsel - E-posta Adresini Doğrula';
        return sendEmail($to_email, $subject, $body);
    }

    public function send_password_reset($to_email, $reset_url, $user_name = '', $expiry_minutes = 5)
    {
        $body = $this->CI->load->view('emails/password_reset', [
            'reset_url'      => $reset_url,
            'userName'       => $user_name,
            'expiry_minutes' => $expiry_minutes
        ], true);
        $subject = 'Playsel - Şifre Sıfırlama Talebi';
        return sendEmail($to_email, $subject, $body);
    }
}
