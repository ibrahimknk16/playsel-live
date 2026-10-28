<?php
function json($data, $code = 200)
{
	header('Content-Type: application/json');
	http_response_code($code);
	echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
	//echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	die;
}

function topup_url($url = '')
{
	return getenv('API_URL') . $url;
}

function generate_unique_nickname($name, $surname = '')
{
	$ci = &get_instance();
	$base = strtolower(trim($name . '_' . $surname));
	$base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);
	$base = preg_replace('/[^a-z0-9]+/', '_', $base);
	$base = trim($base, '_');
	$base = substr($base ?: 'user', 0, 35);
	if (!preg_match('/^[a-z]/', $base)) {
		$base = 'user_' . substr($base, 0, 30);
	}
	$nickname = $base;
	$counter = 2;

	while ($ci->db->where('LOWER(user_nickname) = ' . $ci->db->escape($nickname), null, false)->count_all_results('users') > 0) {
		$suffix = '_' . $counter;
		$nickname = substr($base, 0, 50 - strlen($suffix)) . $suffix;
		$counter++;
	}

	return $nickname;
}

function topupcenter()
{
	return getenv('SITE_URL'); // Topupcenter URL
}

function jsonE($data)
{
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

function gDate($format = 'Y-m-d H:i:s')
{
	return date($format);
}

function siteUrlClear($text = '')
{
	$text = str_replace('.html', '', $text);
	return str_replace(site_url(''), '', $text);
}

function postControl($post = '')
{
	if ($post !== NULL) {
		$post = trim($post);
		$post = strip_tags($post);
		$post = htmlspecialchars($post);
	}
	return $post;
}

function xssClean($data)
{
	if (is_array($data)) {
		foreach ($data as $key => $value) {
			$data[$key] = xssClean($value);
		}
	} elseif (is_string($data)) {
		$data = trim($data);
		$data = strip_tags($data);
		$data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
	}
	return $data;
}

function seoSlug($string)
{
	$turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü'];
	$english = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'O', 'S', 'U'];
	$string = str_replace($turkish, $english, $string);
	$string = mb_strtolower($string, 'UTF-8');
	$string = preg_replace('/[^a-z0-9-]/', '-', $string);
	$string = preg_replace('/-+/', '-', $string);
	$string = trim($string, '-');
	return $string;
}

function getIp()
{
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) and filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
		return $_SERVER['HTTP_X_REAL_IP'];
	} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	} else {
		return $_SERVER['REMOTE_ADDR'];
	}
}

function dateAdd($date = '', $type = '', $amount = 0, $returnFormat = 'Y-m-d H:i:s')
{
	$datetime = new DateTime($date);

	$datetime->modify($amount . ' ' . $type);

	return $datetime->format($returnFormat);
}

function convertDate($date, $returnFormat)
{
	$date = $date == '' ? date('Y-m-d H:i:s') : $date;
	return date($returnFormat, strtotime($date));
}

function nFormat($amount, $digit = 2, $parse1 = '.', $parse2 = '')
{
	return number_format($amount, $digit, $parse1, $parse2);
}

function validateDate($str, $format = 'Y-m-d H:i:s')
{
	$d = DateTime::createFromFormat($format, $str);
	return $d && $d->format($format) == $str;
}

function generateRandomString($length = '', $type = '')
{
	$schemes = array(
		'az09' => '0123456789abcdefghijklmnopqrstuvwxyz',
		'aZ09' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
		'AZ09' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
		'09' => '0123456789',
		'az' => 'abcdefghijklmnopqrstuvwxyz',
		'aZ' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
		'AZ' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
	);
	$characters = $schemes['AZ09'];
	if (isset($schemes[$type])) {
		$characters = $schemes[$type];
	}
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[random_int(0, $charactersLength - 1)];
	}
	return $randomString;
}

function tr_strtoupper($str)
{
	$search = array('ç', 'ğ', 'ı', 'i', 'ö', 'ş', 'ü');
	$replace = array('Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü');
	$str = str_replace($search, $replace, $str);
	return strtoupper($str);
}

function tr_strtolower($str)
{
	$search = array('Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü');
	$replace = array('ç', 'ğ', 'i', 'i', 'ö', 'ş', 'ü');
	$str = str_replace($search, $replace, $str);
	return strtolower($str);
}

function pre($array, $exit = false)
{
	echo '<pre>';
	print_r($array);
	echo '</pre>';
	if ($exit) {
		exit;
	}
}

function sendSms($phoneNumber, $message)
{
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.netgsm.com.tr/sms/send/get',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => array(
			'usercode' => '123123',
			'password' => '123213',
			'gsmno' => $phoneNumber,
			'message' => $message,
			'msgheader' => 'ASDASD',
			'filter' => '0',
			'startdate' => '',
			'stopdate' => ''
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	$responseCode = explode(' ', $response);
	if ($responseCode[0] == '00') {
		return true;
	} else {
		return false;
	}
}


function sendSms2($phoneNumber, $message)
{
	// 1. Telefon numarasını formatla
	$cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
	if (substr($cleanNumber, 0, 2) !== '90') {
		if (substr($cleanNumber, 0, 1) === '0') {
			$cleanNumber = '90' . substr($cleanNumber, 1);
		} else {
			$cleanNumber = '90' . $cleanNumber;
		}
	}

	// 2. API Bilgileri
	$username    = getenv('SMS_USERNAME');
	$password    = getenv('SMS_PASSWORD');
	$sourceAddr  = getenv('SMS_SOURCE') ?: 'PLAYSEL';

	$payload = [
		'username'    => $username,
		'password'    => $password,
		'source_addr' => $sourceAddr,
		'custom_id'   => uniqid('sms_'),
		'messages'    => [
			[
				'msg'  => $message,
				'dest' => $cleanNumber
			]
		]
	];

	$jsonData = json_encode($payload, JSON_UNESCAPED_UNICODE);

	// 3. Curl İstek Yapısı
	$ch = curl_init('https://sms.verimor.com.tr/v2/send.json');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Content-Length: ' . strlen($jsonData)
	]);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	// LOKALDE VEYA SSL DOĞRULAMA HATASI ALAN SUNUCULARDA BAĞLANTIYI KURTARMAK İÇİN:
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	// cURL düzeyinde hata var mı kontrol edelim
	$curlError = curl_error($ch);
	curl_close($ch);

	if ($httpCode === 200 && !empty($response)) {
		return [
			'status'   => true,
			'response' => $response
		];
	}

	return [
		'status'   => false,
		'response' => !empty($curlError) ? 'cURL Error: ' . $curlError : 'HTTP_CODE_' . $httpCode
	];
}

if (!function_exists('sendEmail')) {
	function sendEmail($to, $subject, $message, $from = 'noreply@playsel.com')
	{
		$CI = &get_instance();
		$CI->load->library('email');
		$CI->email->initialize([
			'protocol' => 'smtp',
			'smtp_host' => 'email-smtp.eu-north-1.amazonaws.com',
			'smtp_port' => 587,
			'smtp_user' => getenv('SMTP_USER'),
			'smtp_pass' => getenv('SMTP_PASSWORD'),
			'smtp_crypto' => 'tls',
			'mailtype' => 'html',
			'charset' => 'utf-8',
			'newline' => "\r\n",
			'crlf' => "\r\n",
		]);
		$CI->email->from($from ?: 'noreply@playsel.com', 'Playsel');
		$CI->email->to($to);
		$CI->email->subject($subject);
		$CI->email->message($message);
		return $CI->email->send();
	}
}

function generateOrderID($length)
{
	$randomString = substr(str_shuffle(str_repeat($x = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
	return $randomString;
}

function stdToArray($data)
{
	return json_decode(json_encode($data), true);
}

function userCheck()
{
	$ci = &get_instance();
	$user = $ci->session->userdata('admin') ? $ci->session->userdata('admin') : null;

	if (!$user) {
		$refferer = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : null;
		if (!empty($refferer)) {
			$refferer = str_replace('/admin/', '', $refferer);
			redirect(base_url('login?ref=' . $refferer));
		} else {
			redirect(base_url('login'));
		}
	} else {
		enforceAdminPermission($ci, $user);
		return $user;
	}
}

function enforceAdminPermission($ci, array $user)
{
	$permissionKey = resolvePermissionKey($ci);
	if ($permissionKey === '' || isPermissionWhitelisted($permissionKey)) {
		return true;
	}

	$adminPower = isset($user['admin_power']) ? (int) $user['admin_power'] : 0;
	if ($adminPower === 2) {
		return true;
	}

	if (!hasPermissionTables($ci)) {
		$message = 'Admin yetki tabloları kullanılamıyor. Sistem yöneticinize başvurun.';
		log_message('error', 'Admin authorization denied because permission tables are unavailable: ' . $permissionKey);
		if (isAjaxRequest($ci)) {
			json(['status' => false, 'message' => $message], 503);
			return false;
		}

		$ci->session->set_flashdata('alert', [
			'type' => 'error',
			'title' => 'Yetki sistemi kullanılamıyor',
			'message' => $message,
		]);
		redirect(base_url('dashboard'));
		return false;
	}

	if (hasRolePermission($ci, $adminPower, $permissionKey)) {
		return true;
	}

	$message = 'Bu islem icin yetkiniz bulunmuyor. (' . $permissionKey . ')';
	if (isAjaxRequest($ci)) {
		json(['status' => false, 'message' => $message], 403);
	}

	$ci->session->set_flashdata('alert', [
		'type' => 'error',
		'title' => 'Yetkisiz erisim!',
		'message' => $message,
	]);

	$referrer = isset($_SERVER['HTTP_REFERER']) ? trim((string) $_SERVER['HTTP_REFERER']) : '';
	if ($referrer !== '') {
		redirect($referrer);
	}

	redirect(base_url('dashboard'));
}

function resolvePermissionKey($ci)
{
	$module = strtolower((string) $ci->uri->segment(1));
	$method = strtolower((string) $ci->uri->segment(2));

	if ($module === '') {
		$module = 'dashboard';
	}

	if ($method === '') {
		$method = 'index';
	}

	$controller = $module;

	return $module . '.' . $controller . '.' . $method;
}

function isPermissionWhitelisted($permissionKey)
{
	$whitelist = [
		'user.user.login',
		'user.user.loginrequest',
		'user.user.logout',
		'dashboard.dashboard.index',
		'admins.admins.detail',
		'admins.admins.updateuserpermissions',
		'admins.admins.permissions',
		'admins.admins.rolepermissions',
	];

	return in_array($permissionKey, $whitelist, true);
}

function resolveAdminPermissions($ci, array $user)
{
	$adminPower = isset($user['admin_power']) ? (int) $user['admin_power'] : 0;

	$roleRows = $ci->db
		->select('p.permission_code, arp.admin_role_permission_effect')
		->from('admin_role_permissions arp')
		->join('permissions p', 'p.permission_id = arp.permission_id', 'inner')
		->where('arp.admin_power', $adminPower)
		->where('p.permission_status', 1)
		->get()->result_array();

	$allowed = [];
	$denied = [];

	foreach ($roleRows as $row) {
		$code = strtolower((string) $row['permission_code']);
		if ($code === '') {
			continue;
		}

		if ($row['admin_role_permission_effect'] === 'deny') {
			$denied[$code] = true;
			unset($allowed[$code]);
		} else {
			if (!isset($denied[$code])) {
				$allowed[$code] = true;
			}
		}
	}

	$permissionCodes = array_keys($allowed);

	return $permissionCodes;
}

function hasRolePermission($ci, $adminPower, $permissionKey)
{
	$adminPower = (int) $adminPower;
	$permissionKey = strtolower((string) $permissionKey);

	if ($adminPower <= 0 || $permissionKey === '') {
		return false;
	}

	static $decisionCache = [];
	$cacheKey = $adminPower . '|' . $permissionKey;
	if (isset($decisionCache[$cacheKey])) {
		return $decisionCache[$cacheKey];
	}

	$row = $ci->db
		->select('arp.admin_role_permission_effect')
		->from('admin_role_permissions arp')
		->join('permissions p', 'p.permission_id = arp.permission_id', 'inner')
		->where('arp.admin_power', $adminPower)
		->where('p.permission_status', 1)
		->where('p.permission_code', $permissionKey)
		->limit(1)
		->get()->row_array();

	$isAllowed = !empty($row) && isset($row['admin_role_permission_effect']) && $row['admin_role_permission_effect'] === 'allow';
	$decisionCache[$cacheKey] = $isAllowed;

	return $isAllowed;
}

function hasPermissionTables($ci)
{
	static $hasAllTables = null;
	if ($hasAllTables !== null) {
		return $hasAllTables;
	}

	$requiredTables = [
		'permission_categories',
		'permissions',
		'admin_role_permissions',
	];

	foreach ($requiredTables as $table) {
		if (!$ci->db->table_exists($table)) {
			$hasAllTables = false;
			return false;
		}
	}

	$hasAllTables = true;
	return true;
}

function isAjaxRequest($ci)
{
	$isXhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	$isApi = (bool) $ci->input->get_request_header('Authorization', true);
	return $isXhr || $isApi;
}

function xhrCheck()
{
	$ci = &get_instance();
	$isAPIcall = $ci->input->get_request_header('Authorization', TRUE) ? $ci->input->get_request_header('Authorization', TRUE) : false;
	if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || $isAPIcall) {
		return true;
	} else {
		return json(array('status' => false, 'message' => 'Bu sayfaya erişim yetkiniz bulunmamaktadır.'));
	}
}

// Türkiye numaralarını +905XXXXXXXXX biçimine çevirir; yabancı (+ ile başlayan) numaralar +rakamlar olarak korunur.
// app/helpers/general_helper.php içindeki ile aynı davranışı korumalıdır.
if (!function_exists('normalizePhoneE164')) {
	function normalizePhoneE164($phoneNumber)
	{
		$raw = trim((string)$phoneNumber);
		$digits = preg_replace('/\D+/', '', $raw);
		if ($digits === '') {
			return '';
		}
		if (strpos($raw, '+') !== 0 && strpos($raw, '00') === 0 && strpos($digits, '00') === 0) {
			$digits = substr($digits, 2);
			$raw = '+' . $digits;
		}
		if (preg_match('/^(?:90|0)?(5\d{9})$/', $digits, $m)) {
			return '+90' . $m[1];
		}
		return strpos($raw, '+') === 0 ? '+' . $digits : $digits;
	}
}

// Normalize edilmiş numara geçerli uluslararası biçimdeyse wa.me bağlantısı döner, değilse boş string.
if (!function_exists('whatsappUrl')) {
	function whatsappUrl($phoneNumber)
	{
		$e164 = normalizePhoneE164($phoneNumber);
		return preg_match('/^\+[1-9]\d{7,14}$/', $e164) ? 'https://wa.me/' . substr($e164, 1) : '';
	}
}

function formatPhoneNumber($phoneNumber)
{
	$unformatted = $phoneNumber;
	if (strlen($phoneNumber) == 10) {
		$phoneNumber = "0" . $phoneNumber;
		$formattedPhoneNumber = substr($phoneNumber, 0, 4) . ' ' . substr($phoneNumber, 4, 3) . ' ' . substr($phoneNumber, 7, 2) . ' ' . substr($phoneNumber, 9);
		return $formattedPhoneNumber;
	} else {
		return $unformatted;
	}
}

function sendTelegramNotification($message)
{
	$botToken = '7489849888:AAF1a2kMVA44T-bRodzsSCSIVqL3oM_sLuA'; // @OneserverdreamBot
	// $chatID1 = 1661748892; //Benim chat id
	// $url = 'https://api.telegram.org/bot' . $botToken . '/sendMessage?chat_id=' . $chatID1 . '&text=' . $message;
	// $fgc = file_get_contents($url);
	$chatID2 = 5670611575; //Murat'un chat id
	$url = 'https://api.telegram.org/bot' . $botToken . '/sendMessage?chat_id=' . $chatID2 . '&text=' . $message;
	$fgc = file_get_contents($url);
}

function checkApiRequest()
{
	$ci = &get_instance();
	$api_key = $ci->input->get_request_header('Authorization', TRUE) ? $ci->input->get_request_header('Authorization', TRUE) : false;
	return $api_key;
}

function currency($currency)
{
	$ci = &get_instance();
	$trCurrency = tr_strtolower($currency);
	$currencies = $ci->db->select('currencies_json')
		->from('currency')
		->where('currencies_date', date('Y-m-d'))
		->get()->row()
		->currencies_json;
	$currencies = json_decode($currencies);
	if (!empty($currencies->$trCurrency)) {
		return $currencies->$trCurrency;
	} else {
		return 0;
	}
}

function currencyCalculator($currency, $cost)
{
	if (empty($cost) || $cost == 0 || $cost == null || empty($currency)) {
		return 0;
	}
	$a = currency($currency);
	if ($a == 0) {
		return 0;
	}
	$cost = $cost / $a;
	$cost = number_format($cost, 2, '.', '');
	return $cost;
}

function currencyCalculator222($currency, $cost)
{
	if (empty($cost) || empty($currency)) {
		return 0;
	}

	$a = currency($currency);

	// Kur 0 ise veya geçersizse işlemi durdur
	if ($a <= 0) {
		return 0;
	}

	$result = $cost / $a;

	// Eğer sonuç çok küçükse (IDR gibi), hassasiyeti 6 haneye kadar çıkart, 
	// değilse 2 hane ile sınırla.
	return ($result < 0.01) ? number_format($result, 8, '.', '') : number_format($result, 2, '.', '');
}


function currencyCalculatorTo($currency, $cost)
{
	$a = currency($currency);
	$cost = $cost * $a;
	$cost = number_format($cost, 2, '.', '');
	return $cost;
}

function keyMask($input)
{
	// Sadece harf ve rakamları ayıkla
	$cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $input);
	$length = strlen($cleaned);
	// Eğer uzunluk yeterli değilse maskeleme yapma
	if ($length <= 10) {
		return $input;
	}
	// İlk 6 ve son 6 karakteri koru
	$start = substr($cleaned, 0, 5);
	$end = substr($cleaned, -5);
	$maskedMiddle = str_repeat('*', $length - 10);
	// Maskeleme sonrası karakter dizisi
	$masked = $start . $maskedMiddle . $end;
	// Orijinal formatı korumak için karakterleri geri yerleştir
	$output = '';
	$j = 0;
	for ($i = 0; $i < strlen($input); $i++) {
		if (ctype_alnum($input[$i])) {
			$output .= $masked[$j++];
		} else {
			$output .= $input[$i]; // tire gibi karakterleri olduğu gibi ekle
		}
	}
	return $output;
}


function upload_image($field_name, $upload_path, $path, $prefix = 'img_')
{
	ini_set('memory_limit', '256M');

	$CI = &get_instance();
	$CI->load->library('R2_Storage');

	// ---------------------------------------------------------
	// 1) DOSYA KONTROLÜ
	// ---------------------------------------------------------

	if (
		!isset($_FILES[$field_name]) ||
		!is_array($_FILES[$field_name])
	) {
		return false;
	}

	$file = $_FILES[$field_name];

	if ($file['error'] !== UPLOAD_ERR_OK) {
		log_message(
			'error',
			'Image upload error: ' . $file['error']
		);

		return false;
	}

	if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
		log_message(
			'error',
			'Invalid uploaded file.'
		);

		return false;
	}

	// 10 MB
	if ($file['size'] > (10 * 1024 * 1024)) {
		log_message(
			'error',
			'Image exceeds 10MB limit.'
		);

		return false;
	}

	$source_path = $file['tmp_name'];

	// ---------------------------------------------------------
	// 2) GERÇEK IMAGE TİPİNİ BUL
	// ---------------------------------------------------------

	$image_info = @getimagesize($source_path);

	if ($image_info === false) {

		log_message(
			'error',
			'Uploaded file is not a valid image.'
		);

		return false;
	}

	$real_mime = $image_info['mime'];

	$allowed_mimes = [
		'image/jpeg',
		'image/png',
		'image/webp',
	];

	if (!in_array($real_mime, $allowed_mimes, true)) {

		log_message(
			'error',
			'Invalid image MIME: ' . $real_mime
		);

		return false;
	}

	// ---------------------------------------------------------
	// 3) UPLOAD DİZİNİ
	// ---------------------------------------------------------

	if (!is_dir($upload_path)) {

		if (!mkdir($upload_path, 0755, true)) {

			log_message(
				'error',
				'Could not create upload directory: ' . $upload_path
			);

			return false;
		}
	}

	// ---------------------------------------------------------
	// 4) DOSYA ADI
	// ---------------------------------------------------------

	$new_webp_name = uniqid($prefix, true) . '.webp';

	$webp_path = rtrim($upload_path, '/') . '/' . $new_webp_name;

	// ---------------------------------------------------------
	// 5) IMAGE RESOURCE OLUŞTUR
	// ---------------------------------------------------------

	$img = false;

	switch ($real_mime) {

		case 'image/jpeg':
			$img = @imagecreatefromjpeg($source_path);
			break;

		case 'image/png':
			$img = @imagecreatefrompng($source_path);

			if ($img) {
				imagepalettetotruecolor($img);
				imagealphablending($img, true);
				imagesavealpha($img, true);
			}

			break;

		case 'image/webp':
			$img = @imagecreatefromwebp($source_path);
			break;
	}

	if (!$img) {

		log_message(
			'error',
			'Could not create GD image resource. MIME: ' . $real_mime
		);

		return false;
	}

	// ---------------------------------------------------------
	// 6) WEBP OLUŞTUR
	// ---------------------------------------------------------

	if (!function_exists('imagewebp')) {

		imagedestroy($img);

		log_message(
			'error',
			'PHP GD imagewebp() function is not available.'
		);

		return false;
	}

	$result = @imagewebp(
		$img,
		$webp_path,
		80
	);

	imagedestroy($img);

	if (!$result || !file_exists($webp_path)) {

		@unlink($webp_path);

		log_message(
			'error',
			'WebP conversion failed.'
		);

		return false;
	}

	// ---------------------------------------------------------
	// 7) R2
	// ---------------------------------------------------------

	try {

		$r2_target_path =
			ltrim($path, '/') .
			$new_webp_name;

		$upload_status = $CI->r2_storage->upload(
			$webp_path,
			$r2_target_path,
			'image/webp'
		);

		if (!$upload_status) {

			log_message(
				'error',
				'R2 upload failed: ' . $r2_target_path
			);

			@unlink($webp_path);

			return false;
		}
	} catch (\Throwable $e) {

		log_message(
			'error',
			'R2 Exception: ' . $e->getMessage()
		);

		@unlink($webp_path);

		return false;
	}

	// ---------------------------------------------------------
	// 8) LOCAL DOSYAYI SİL
	// ---------------------------------------------------------

	@unlink($webp_path);

	return $new_webp_name;
}
function costToText($number)
{
	$birler = ["", "Bir", "İki", "Üç", "Dört", "Beş", "Altı", "Yedi", "Sekiz", "Dokuz"];
	$onlar  = ["", "On", "Yirmi", "Otuz", "Kırk", "Elli", "Altmış", "Yetmiş", "Seksen", "Doksan"];
	$basamak = ["", "Bin", "Milyon", "Milyar", "Trilyon"];

	$number = str_replace(",", ".", $number);
	$parts  = explode(".", $number);

	$tam   = preg_replace('/[^0-9]/', '', $parts[0]);
	$kurus = isset($parts[1]) ? substr(str_pad($parts[1], 2, "0"), 0, 2) : "00";

	if ($tam == "" || intval($tam) == 0) {
		$result = "Sıfır";
	} else {

		$tam = str_pad($tam, ceil(strlen($tam) / 3) * 3, "0", STR_PAD_LEFT);
		$groups = str_split($tam, 3);
		$groupCount = count($groups);

		$result = "";

		foreach ($groups as $index => $groupRaw) {

			$group = intval($groupRaw);
			if ($group == 0) continue;

			$yuz = intval($group / 100);
			$on  = intval(($group % 100) / 10);
			$bir = $group % 10;

			$basamakIndex = $groupCount - $index - 1;

			// 1000-1999 arası özel durum
			if ($basamakIndex == 1 && $group == 1) {
				$result .= "Bin";
				continue;
			}

			if ($yuz > 0)
				$result .= ($yuz == 1 ? "Yüz" : $birler[$yuz] . "Yüz");

			if ($on > 0)
				$result .= $onlar[$on];

			if ($bir > 0)
				$result .= $birler[$bir];

			$result .= $basamak[$basamakIndex];
		}
	}

	$result .= "TL";

	if (intval($kurus) > 0) {
		$k = intval($kurus);
		$on  = intval($k / 10);
		$bir = $k % 10;

		if ($on > 0)
			$result .= $onlar[$on];
		if ($bir > 0)
			$result .= $birler[$bir];

		$result .= "Krş";
	}

	return $result;
}
