<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['socket_type'] = 'tcp'; // Redis sunucusuna bağlantı türü (tcp veya unix)
$config['socket'] = '/var/run/redis/redis.sock'; // Unix soket yolunu kullanıyorsanız
$config['host'] = '127.0.0.1'; // Redis sunucusunun IP adresi veya host adı
$config['password'] = NULL; // Redis sunucusuna erişim parolası (gerekiyorsa)
$config['port'] = 6379; // Redis sunucusunun port numarası
$config['timeout'] = 0;
