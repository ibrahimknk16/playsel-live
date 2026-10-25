<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['birfatura_api_key']         = getenv('BIRFATURA_API_KEY') ?: '';
$config['birfatura_secret_key']      = getenv('BIRFATURA_SECRET_KEY') ?: '';
$config['birfatura_integration_key'] = getenv('BIRFATURA_INTEGRATION_KEY') ?: '';
$config['birfatura_base_url']        = getenv('BIRFATURA_BASE_URL') ?: 'https://test1.edonustur.com';

$config['birfatura_supplier'] = [
    'name'        => 'DİJİPİN ELEKTRONİK HİZMETLER ANONİM ŞİRKETİ',
    'address'     => 'ADALET MAH. ANADOLU CAD. NO: 41/1 İÇ KAPI NO: 1401 MY PLAZA',
    'city'        => 'İzmir',
    'district'    => 'Bayraklı',
    'phone'       => '+905353454746',
    'email'       => 'muhasebe@dijipin.com',
    'tax_office'  => 'KARŞIYAKA',
    'vkn'         => '2951058328'
];
