<?php defined('BASEPATH') OR exit('No direct script access allowed');

$hook['pre_controller'][] = [
  'class'    => '',
  'function' => 'lang_redirect',
  'filename' => 'lang_redirect.php',
  'filepath' => 'hooks'
];

$hook['pre_controller'][] = [
  'class'    => '',
  'function' => 'maintenance_mode',
  'filename' => 'maintenance_mode.php',
  'filepath' => 'hooks'
];
