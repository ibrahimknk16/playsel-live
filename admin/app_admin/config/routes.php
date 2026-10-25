<?php
defined('BASEPATH') or exit('No direct script access allowed');
$route['default_controller'] = 'dashboard';
$route['login'] = 'user/login';
$route['logout'] = 'user/logout';
$route['loginRequest'] = 'user/loginRequest';
$route['settings'] = 'settings/index';
$route['settings/save'] = 'settings/save';
$route['users/nicknameRequests'] = 'users/nicknameRequests';
$route['users/approveNicknameRequest'] = 'users/approveNicknameRequest';
$route['users/rejectNicknameRequest'] = 'users/rejectNicknameRequest';


$route['(:any)-(c|p)-(:num)'] = 'products/$1/$2/$3';
$route['orders/orderContract/(:num)/(:any)'] = 'orders/orders/orderContract/$1/$2';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;
