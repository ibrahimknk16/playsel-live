<?php
require_once FCPATH . 'vendor/autoload.php';
use PragmaRX\Google2FA\Google2FA;

class Google2fa_lib
{

    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret()
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQrUrl($email, $secret)
    {
        return $this->google2fa->getQRCodeUrl(
            'API Dijipin',
            $email,
            $secret
        );
    }

    public function verify($secret, $code)
    {
        return $this->google2fa->verifyKey($secret, $code);
    }
}
