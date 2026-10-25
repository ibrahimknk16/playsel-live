<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['birfatura_api_key']         = '1c5dc2ba-a745-4fe0-aad5-1ca362eb7025';
$config['birfatura_secret_key']      = 'a8c55f09-3a74-412a-880e-24a002c1a248';
$config['birfatura_integration_key'] = '3f0959c3-ee9d-4d97-8975-a56a3223575f';
$config['birfatura_base_url']        = 'https://test1.edonustur.com'; // Canlı: https://api.edonustur.com/

// ESKİ: http://api.edonustur.com/
// YENİ (STABİL):

$config['birfatura_supplier'] = [
    'name'        => 'Playsel Elektronik Ticaret Limited Şirketi',
    'address'     => 'Adalet Mh, Anadolu Cd. No 41/1 İç Kapı No: 1402 My Plaza',
    'city'        => 'İzmir',
    'district'    => 'Bayraklı',
    'phone'       => '+905353454746',
    'email'       => 'muhasebe@playsel.com',
    'tax_office'  => 'KARŞIYAKA',
    'vkn'         => '7300761424'
];
