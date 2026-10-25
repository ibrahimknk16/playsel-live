<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['checkout_secret_key'] = getenv('CHECKOUT_SECRET_KEY') ?: '';
$config['checkout_base_url']   = getenv('CHECKOUT_BASE_URL') ?: 'https://api.sandbox.checkout.com';
