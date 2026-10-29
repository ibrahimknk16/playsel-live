<?php
function site_setting($key, $default = '')
{
	static $settings = null;

	if ($settings === null) {
		$ci = &get_instance();
		$settings = [];

		if ($ci->db->table_exists('site_settings')) {
			$rows = $ci->db->select('setting_key, setting_value')->get('site_settings')->result();
			foreach ($rows as $row) {
				$settings[$row->setting_key] = $row->setting_value;
			}
		}
	}

	return array_key_exists($key, $settings) ? $settings[$key] : $default;
}

function generate_unique_nickname($name, $surname = '')
{
	$ci = &get_instance();
	$base = trim((string)$name . '_' . (string)$surname);
	$base = strtr($base, ['I' => 'ı', 'İ' => 'i']);
	$base = mb_strtolower($base, 'UTF-8');
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

function ensure_user_nickname($user)
{
	if (!$user || !empty($user->user_nickname)) {
		return $user;
	}

	$nickname = generate_unique_nickname($user->user_name ?? 'user', $user->user_surname ?? '');
	$ci = &get_instance();
	$ci->db->where('user_id', $user->user_id)->update('users', ['user_nickname' => $nickname]);
	$user->user_nickname = $nickname;

	return $user;
}

function ensure_user_bank_reference($userId)
{
	$ci = &get_instance();
	$userId = (int) $userId;
	if ($userId <= 0) {
		return '';
	}

	$current = $ci->db->select('user_bank_referance_number')->where('user_id', $userId)->get('users')->row();
	if (!$current) {
		return '';
	}
	$existing = trim((string) $current->user_bank_referance_number);
	if ($existing !== '') {
		return $existing;
	}

	for ($attempt = 0; $attempt < 10; $attempt++) {
		$ref = generateRandomString(16, '09');
		if ($ci->db->where('user_bank_referance_number', $ref)->count_all_results('users') > 0) {
			continue;
		}
		// Eşzamanlı isteklerde yalnızca ilk yazan kazanır; boş olmayan referans asla ezilmez.
		$ci->db->where('user_id', $userId)
			->group_start()->where('user_bank_referance_number IS NULL', null, false)->or_where('user_bank_referance_number', '')->group_end()
			->update('users', ['user_bank_referance_number' => $ref]);
		break;
	}

	$final = $ci->db->select('user_bank_referance_number')->where('user_id', $userId)->get('users')->row();
	return $final ? trim((string) $final->user_bank_referance_number) : '';
}

function json($data, $code = 200)
{
	header('Content-Type: application/json');
	http_response_code($code);
	//echo json_encode($data);
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	die;
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
	if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) and filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
		return $_SERVER['HTTP_CF_CONNECTING_IP'];
	} else if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
		return $_SERVER['HTTP_X_REAL_IP'];
	} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	} else {
		return $_SERVER['REMOTE_ADDR'];
	}
}

// İstemci portu: proxy/CDN arkasında gerçek kaynak portu başlıkla gelmiyorsa REMOTE_PORT son proxy'nin portudur.
function getClientPort()
{
	foreach (['HTTP_TRUE_CLIENT_PORT', 'HTTP_CF_CONNECTING_PORT', 'REMOTE_PORT'] as $key) {
		if (!empty($_SERVER[$key]) && ctype_digit((string) $_SERVER[$key])) {
			return (int) $_SERVER[$key];
		}
	}
	return 0;
}

// IP ve port birlikte
function getClientConnection()
{
	return ['ip' => getIp(), 'port' => getClientPort()];
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

function nFormat($amount, $digit = 2, $parse1 = ',', $parse2 = '.')
{
	return number_format($amount, $digit, $parse1, $parse2);
}

function displayCurrency($currency)
{
	return strtoupper((string) $currency) === 'TRY' ? 'TL' : (string) $currency;
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
		'REF' => '23456789ABCDEFGHKLMNPRSTVYZ',
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


function createRefCode($length = 6)
{
	// CI instance'ını alıyoruz
	$CI = &get_instance();

	// Karıştırılabilecek karakterler elenmiş (0, 1, I, O vb.) 27 karakterlik güvenli havuz
	$characters = '23456789ABCDEFGHKLMNPRSTVYZ';
	$charactersLength = strlen($characters);

	$isUnique = false;
	$refCode = '';
	$maxAttempts = 10; // Sonsuz döngüye girmemesi için safe-guard
	$attempt = 0;

	while (!$isUnique && $attempt < $maxAttempts) {
		$refCode = '';
		for ($i = 0; $i < $length; $i++) {
			$refCode .= $characters[random_int(0, $charactersLength - 1)];
		}

		// Performans için sadece select 1 ve limit 1 ile index üzerinden hızlıca sorguluyoruz
		$CI->db->select('1', FALSE);
		$CI->db->from('orders');
		$CI->db->where('order_eft_id', $refCode);
		$CI->db->limit(1);
		$query = $CI->db->get();

		if ($query->num_rows() === 0) {
			$isUnique = true;
		}

		$attempt++;
	}

	// Eğer 10 denemede de çakışma çözülmediyse (teorik olarak neredeyse imkansız) uzunluğu 1 artırıp kurtarıyoruz
	if (!$isUnique) {
		return createRefCode($length + 1);
	}

	return $refCode;
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

function isAjaxRequest()
{
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
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
			'usercode' => '8503088548',
			'password' => 'P9T935DK',
			'gsmno' => $phoneNumber,
			'message' => $message,
			'msgheader' => 'PLAYSEL',
			'filter' => '0',
			'startdate' => '',
			'stopdate' => ''
		),
	));
	$response = curl_exec($curl);
	pre($response);
	curl_close($curl);
	$responseCode = explode(' ', $response);
	pre($responseCode);
	if ($responseCode[0] == '00') {
		return true;
	} else {
		return false;
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
	$user = $ci->session->userdata('user') ? $ci->session->userdata('user') : null;

	if (!$user) {
		$refferer = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : null;
		if (!empty($refferer)) {
			$refferer = str_replace('/admin/', '', $refferer);
			redirect(topupcenter() . 'login?ref=' . $refferer);
		} else {
			redirect(topupcenter() . 'login');
		}

	} else {
		return $user;
	}
}

/**
 * Returns the active restriction for a user and capability.
 * Expired rows are treated as inactive without a background job.
 */
function customerRestriction($userId, $scope = 'all')
{
	$ci = &get_instance();
	$userId = (int) $userId;
	if ($userId <= 0 || !$ci->db->table_exists('customer_restrictions')) {
		return null;
	}

	$now = date('Y-m-d H:i:s');
	$ci->db->where('restriction_user_id', $userId)
		->where('restriction_status', 1)
		->where('restriction_starts_at <=', $now)
		->group_start()
		->where('restriction_expires_at IS NULL', null, false)
		->or_where('restriction_expires_at >', $now)
		->group_end()
		->group_start()
		->where('restriction_scope', 'all')
		->or_where('restriction_scope', $scope)
		->group_end()
		->order_by('restriction_id', 'DESC');

	return $ci->db->get('customer_restrictions')->row();
}

function customerRestrictionMessage($userId, $scope = 'all')
{
	$restriction = customerRestriction($userId, $scope);
	return $restriction ? trim((string) $restriction->restriction_customer_reason) : '';
}

// Kısıtlı üyeye gösterilecek bildirim (toast HTML basıldığı için sebep escape edilir).
function restrictedAccountMessage($restriction = null)
{
	$message = 'Üyeliğiniz kısıtlanmıştır. Canlı destek ile iletişime geçebilirsiniz.';
	$reason = $restriction ? trim((string) ($restriction->restriction_customer_reason ?? '')) : '';
	if ($reason !== '') {
		$message .= '<br><small>' . html_escape($reason) . '</small>';
	}
	return $message;
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
function normalizePhoneE164($phoneNumber)
{
	$raw = trim((string) $phoneNumber);
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

function sendTelegramNotification($message = '')
{
	$botToken = '7844018821:AAGNJI02OStem2eeWGNiPqmkSiUfSZ7rKdQ'; // @OneserverdreamBot
	// $url = 'https://api.telegram.org/bot' . $botToken . '/getUpdates';
	// $fgc = file_get_contents($url);
	// pre($fgc);
	// die;
	$chatID1 = 1661748892; //Benim chat id
	$url = 'https://api.telegram.org/bot' . $botToken . '/sendMessage?chat_id=' . $chatID1 . '&text=' . $message;
	$fgc = file_get_contents($url);
	$chatID2 = 5264087591; //Tayfun abi chat id
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

	$row = $ci->db->select('currencies_json')
		->from('currency')
		->where('currencies_date', date('Y-m-d'))
		->get()->row();

	if ($row && !empty($row->currencies_json)) {
		$currencies = json_decode($row->currencies_json);

		if (!empty($currencies->$trCurrency)) {
			return $currencies->$trCurrency;
		}
	}
	return 1;
}

function currencyCalculator($currency, $cost)
{
	$a = currency($currency);
	$cost = $cost / $a;
	$cost = number_format($cost, 2, '.', '');
	return $cost;
}

function currencyCalculatorTo($currency, $cost)
{
	$a = currency($currency);
	$cost = $cost * $a;
	$cost = number_format($cost, 2, '.', '');
	return $cost;
}

function prodctPricesChangeLocationCurrency($products, $currency = 'TRY')
{
	$CI = &get_instance();
	if (empty($products)) {
		return [];
	}

	// ürün id listesini güvenli şekilde çıkar
	$productIds = [];
	foreach ($products as $p) {
		if (is_array($p) && isset($p['product_id'])) {
			$productIds[] = $p['product_id'];
		} elseif (is_object($p) && isset($p->product_id)) {
			$productIds[] = $p->product_id;
		}
	}

	if (empty($productIds)) {
		return $products;
	}

	$productPrices = $CI->db
		->select('product_price_product_id, product_price_standart_price, product_price_silver_user, product_price_gold_user, product_price_vip_user')
		->from('product_prices')
		->where('product_price_country', 'TR')
		->where_in('product_price_product_id', $productIds)
		->get()
		->result();

	if (empty($productPrices)) {
		return $products;
	}

	// product_id => priceRow map'i oluştur
	$priceMap = [];
	foreach ($productPrices as $pp) {
		$priceMap[$pp->product_price_product_id] = $pp;
	}

	// Ürün listesi üzerinde dolaş, product_id ile eşleşen fiyatları ata
	foreach ($products as &$prod) {
		$prodId = null;
		$isObject = false;

		if (is_array($prod) && isset($prod['product_id'])) {
			$prodId = $prod['product_id'];
		} elseif (is_object($prod) && isset($prod->product_id)) {
			$prodId = $prod->product_id;
			$isObject = true;
		}

		if ($prodId !== null && isset($priceMap[$prodId])) {
			$row = $priceMap[$prodId];

			// güvenli numeric dönüşüm — eğer değer null veya boşsa 0 olarak kabul et
			$std = is_numeric($row->product_price_standart_price) ? $row->product_price_standart_price : 0;
			$silver = is_numeric($row->product_price_silver_user) ? $row->product_price_silver_user : 0;
			$gold = is_numeric($row->product_price_gold_user) ? $row->product_price_gold_user : 0;
			$vip = is_numeric($row->product_price_vip_user) ? $row->product_price_vip_user : 0;

			// currencyCalculatorTo fonksiyonunun dönüşünü doğrudan kullan
			$stdConv = currencyCalculatorTo($currency, $std);
			$silverConv = currencyCalculatorTo($currency, $silver);
			$goldConv = currencyCalculatorTo($currency, $gold);
			$vipConv = currencyCalculatorTo($currency, $vip);

			if ($isObject) {
				$prod->product_price_standart_price = $stdConv;
				$prod->product_price_silver_user = $silverConv;
				$prod->product_price_gold_user = $goldConv;
				$prod->product_price_vip_user = $vipConv;
			} else {
				$prod['product_price_standart_price'] = $stdConv;
				$prod['product_price_silver_user'] = $silverConv;
				$prod['product_price_gold_user'] = $goldConv;
				$prod['product_price_vip_user'] = $vipConv;
			}
		}
		// eğer eşleşme yoksa mevcut değerleri dokunmadan bırak (isteğe göre 0 atayabilirsin)
	}
	unset($prod); // reference temizle

	return $products;
}

function prodctPricesChangeLocationCurrency2($products, $currency = 'TRY', $userTier = 1, $userLocale = 'TR')
{
	$CI = &get_instance();
	if (empty($products))
		return [];

	$productIds = [];
	foreach ($products as $p) {
		$productIds[] = is_object($p) ? $p->product_id : $p['product_id'];
	}

	$prices = $CI->db->where_in('product_price_product_id', $productIds)
		->select('*')
		->where('product_price_country', $userLocale)
		->get('product_prices')
		->result();

	$map = [];
	foreach ($prices as $p) {
		$map[$p->product_price_product_id] = $p;
	}

	$fields = [
		1 => ['price' => 'product_price_standart_price', 'mult' => 'product_price_standart_price_multiplier'],
		2 => ['price' => 'product_price_silver_user', 'mult' => 'product_price_silver_user_multiplier'],
		3 => ['price' => 'product_price_gold_user', 'mult' => 'product_price_gold_user_multiplier'],
		4 => ['price' => 'product_price_vip_user', 'mult' => 'product_price_vip_user_multiplier'],
		5 => ['price' => 'product_price_playsel', 'mult' => 'product_price_playsel_multiplier'],
		6 => ['price' => 'product_price_payment', 'mult' => 'product_price_payment_multiplier'],
	];

	foreach ($products as &$prod) {
		$id = is_object($prod) ? $prod->product_id : $prod['product_id'];

		// Set default price to 0 to prevent Undefined property errors
		if (is_object($prod)) {
			$prod->user_price = 0;
		} else {
			$prod['user_price'] = 0;
		}

		if (!isset($map[$id]))
			continue;

		$row = $map[$id];

		if ($row->product_price_is_multiplier == 1) {
			$price = $row->product_price_cost * $row->{$fields[$userTier]['mult']};
		} else {
			$price = $row->{$fields[$userTier]['price']};
		}

		if (is_object($prod)) {
			$prod->user_price = $price;
			$prod->list_price = $row->product_price_list_price ?? 0; // Eğer list_price yoksa 0 ata
			$prod->product_price_currency = $row->product_price_currency ?? 'TRY'; // Eğer currency yoksa 'TRY' ata
		} else {
			$prod['user_price'] = $price;
			$prod['list_price'] = $row->product_price_list_price ?? 0; // Eğer list_price yoksa 0 ata
			$prod['product_price_currency'] = $row->product_price_currency ?? 'TRY'; // Eğer currency yoksa 'TRY' ata
		}
	}
	unset($prod);
	return $products;
}

function userPrice($productID, $userLocale, $userTier)
{
	$ci = &get_instance();
	$productID = (int) $productID;
	$userLocale = strtoupper(trim((string) $userLocale));
	$userLocale = $userLocale !== '' ? $userLocale : 'TR';
	$userTier = (int) $userTier; // 1: standart, 2: silver, 3: gold, 4: vip
	if ($userTier < 1 || $userTier > 6) {
		$userTier = 1;
	}
	$price = $ci->db->select('*')
		->from('product_prices')
		->where('product_price_product_id', $productID)
		->where('product_price_country', $userLocale)
		->get()
		->row();
	if (!$price && $userLocale !== 'TR') {
		$price = $ci->db->select('*')
			->from('product_prices')
			->where('product_price_product_id', $productID)
			->where('product_price_country', 'TR')
			->get()
			->row();
	}
	if (!$price) {
		return null;
	}
	if ($productID <= 0) {
		return 0;
	}
	$priceFields = [
		1 => [
			'price' => 'product_price_standart_price',
			'multiplier' => 'product_price_standart_price_multiplier',
		],
		2 => [
			'price' => 'product_price_silver_user',
			'multiplier' => 'product_price_silver_user_multiplier',
		],
		3 => [
			'price' => 'product_price_gold_user',
			'multiplier' => 'product_price_gold_user_multiplier',
		],
		4 => [
			'price' => 'product_price_vip_user',
			'multiplier' => 'product_price_vip_user_multiplier',
		],
		5 => [
			'price' => 'product_price_playsel',
			'multiplier' => 'product_price_playsel_multiplier',
		],
		6 => [
			'price' => 'product_price_payment',
			'multiplier' => 'product_price_payment_multiplier',
		],
	];
	if ($price->product_price_is_multiplier == 1) {
		$userPrice = $price->product_price_cost * $price->{$priceFields[$userTier]['multiplier']};
	} else {
		$userPrice = $price->{$priceFields[$userTier]['price']};
	}
	return $userPrice;
}

function userPriceCost($productID, $userLocale, $userTier)
{
	$ci = &get_instance();

	if (empty($userLocale))
		$userLocale = 'TR';
	if (empty($userTier) || $userTier < 1 || $userTier > 6)
		$userTier = 1;
	if (empty($productID))
		return 0;

	$price = $ci->db->select('
			pp.product_price_cost,
			pp.product_price_currency,
			provider.provider_product_product_cost,
			provider.provider_product_product_cost_currency
		')
		->from('product_prices as pp')
		->join('products as product', 'product.product_id = pp.product_price_product_id', 'left')
		->join('provider_products as provider', 'provider.provider_product_id = product.product_provider_id', 'left')
		->where('pp.product_price_product_id', (int) $productID)
		->where('pp.product_price_country', strtolower(trim((string) $userLocale)))
		->get()
		->row();

	if (!$price && strtolower(trim((string) $userLocale)) !== 'tr') {
		$price = $ci->db->select('
				pp.product_price_cost,
				pp.product_price_currency,
				provider.provider_product_product_cost,
				provider.provider_product_product_cost_currency
			')
			->from('product_prices as pp')
			->join('products as product', 'product.product_id = pp.product_price_product_id', 'left')
			->join('provider_products as provider', 'provider.provider_product_id = product.product_provider_id', 'left')
			->where('pp.product_price_product_id', (int) $productID)
			->where('pp.product_price_country', 'tr')
			->get()
			->row();
	}

	if (!$price)
		return null;

	$cost = $price->product_price_cost;
	$currency = $price->product_price_currency;
	if (!is_numeric($cost) || (float) $cost <= 0) {
		$cost = $price->provider_product_product_cost;
		$currency = $price->provider_product_product_cost_currency ?: $currency;
	}

	if (!is_numeric($cost) || (float) $cost <= 0) {
		log_message('error', 'Order cost is missing for product: ' . (int) $productID);
		return null;
	}

	return [
		'cost' => (float) $cost,
		'currency' => $currency
	];
}




function topupcenter()
{
	$url = getenv('SITE_URL');
	if (!empty($url)) {
		return rtrim($url, '/') . '/';
	}
	return function_exists('base_url') ? rtrim(base_url(), '/') . '/' : '/';
}

function userTier($tier)
{
	$tiers = [
		1 => 'product_price_standart_price',
		2 => 'product_price_silver_user',
		3 => 'product_price_gold_user',
		4 => 'product_price_vip_user',
		5 => 'product_price_playsel',
		6 => 'product_price_payment',
	];
	return isset($tiers[$tier]) ? $tiers[$tier] : 'product_price_standart_price';
}

function sendEmail($to, $subject, $message, $from = 'noreply@playsel.com')
{
	$CI = &get_instance();
	$CI->load->library('email');
	$config = array(
		'protocol' => 'smtp', // SMTP kullanıyorsanız
		'smtp_host' => 'email-smtp.eu-north-1.amazonaws.com', // Örnek: smtp.gmail.com
		'smtp_port' => 587, // Genelde 587 veya 465
		'smtp_user' => getenv('SMTP_USER'),
		'smtp_pass' => getenv('SMTP_PASSWORD'),
		'smtp_crypto' => 'tls',
		'mailtype' => 'html', // HTML formatında mail
		'charset' => 'utf-8', // Türkçe karakterler için
		'newline' => "\r\n",
		'crlf' => "\r\n",
	);
	$CI->email->initialize($config);
	if (empty($from)) {
		$from = 'noreply@playsel.com';
	}
	$CI->email->from($from, 'Playsel'); // Gönderen e-posta adresi ve adı
	$CI->email->to($to);
	$CI->email->subject($subject);
	$CI->email->message($message);
	if ($CI->email->send()) {
		return true;
	} else {
		//log_message('error', 'Mail gönderilemedi: ' . $CI->email->print_debugger(array('headers')));
		pre($CI->email->print_debugger(array('headers')), true);
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
	$username = getenv('SMS_USERNAME');
	$password = getenv('SMS_PASSWORD');
	$sourceAddr = getenv('SMS_SOURCE') ?: 'PLAYSEL';

	$payload = [
		'username' => $username,
		'password' => $password,
		'source_addr' => $sourceAddr,
		'custom_id' => uniqid('sms_'),
		'messages' => [
			[
				'msg' => $message,
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

	// DEBUGLOG: sms_debug.log dosyasına tüm adımları yazalım
	// $logData = [
	// 	'time' => date('Y-m-d H:i:s'),
	// 	'original_phone' => $phoneNumber,
	// 	'clean_phone' => $cleanNumber,
	// 	'payload' => $payload,
	// 	'http_code' => $httpCode,
	// 	'curl_error' => $curlError,
	// 	'response_body' => $response
	// ];
	// file_put_contents(FCPATH . 'sms_debug.log', print_r($logData, true) . "\n-------------------------\n", FILE_APPEND);

	if ($httpCode === 200 && !empty($response)) {
		return [
			'status' => true,
			'response' => $response
		];
	}

	return [
		'status' => false,
		'response' => !empty($curlError) ? 'cURL Error: ' . $curlError : 'HTTP_CODE_' . $httpCode
	];
}

function errorMessages($message)
{
	$errorArray = [
		'Error_IAP_FirstChargeDoubbleBuyed' => 'Bu ürünün ilk yükleme bonus elmas kampanyasından daha önce yararlandığınız için siparişiniz iptal edilmiştir.',
		'no valid price for pay: bad price key' => 'Hesabınıza TR bölgesinde yükleme yapılamamaktadır. Lütfen sunucunuzun bölgesinden veya global kategori kısmından satın alım yapmayı deneyiniz.',
		'game user product not available' => 'Satın alma limitine ulaşıldı. Bu ürün için şuan daha fazla satın alma yapamazsınız.',
		'Error_InvalidZoneId' => 'Oyuncu bilgileriniz hatalı. Lütfen kontrol edip tekrar sipariş oluşturunuz.',
		'Error_Role_Null' => 'Oyuncu bilgileriniz hatalı. Lütfen kontrol edip tekrar sipariş oluşturunuz.',
		'User region not support recharging' => 'Hesabınıza seçtiğiniz bölgeden yükleme yapılamamaktadır. Lütfen sunucunuzun bölgesinden veya global kategori kısmından satın alım yapmayı deneyiniz.',
		'Error_Act_Subscrip_OneTimeProd_LeftTimeLimit' => 'Mobile Legends haftalık elmas satın alım sınırına ulaştınız. Haftalık elmas, Mobile Legends hesabınız için en fazla 10 kez satın alınabilir.',
		'game api request err, with err context deadline exceeded' => 'Lütfen tekrar sipariş oluşturunuz.',
		'Invalid ID' => 'Kullanıcı bilgileri hatalıdır. Lütfen bilgilerinizi kontrol ediniz.',
	];

	// birebir eşleşme
	if (isset($errorArray[$message])) {
		return $errorArray[$message];
	}

	// değişkenli hatalar
	if (strpos($message, 'bad server id') !== false || strpos($message, 'bad user id') !== false) {
		return 'Oyuncu bilgileriniz hatalıdır. Oyuncu ID ve sunucu ID bilgilerinizde harf ve özel karakter kullanmayınız. Sadece rakam içermelidir. (Oyuncu bilgilerinizi oyun içerisinde karakterinizin detaylarından kontrol edebilirsiniz)';
	}

	// Eğer mesaj yukarıdaki hatalardan biriyle eşleşmiyorsa, varsayılan mesajı döndür
	return 'Siparişiniz oluşturulurken bir hata oluştu. Lütfen bilgilerinizi kontrol edip tekrar deneyiniz. Eğer sorun devam ederse destek ekibimizle iletişime geçiniz.';

	// Şuanlık alt kısım iptal. Eğer ileride yeni hatalar eklenirse, bu kısım açılabilir.
	return $message;
}

function costToText($number)
{
	$birler = ["", "Bir", "İki", "Üç", "Dört", "Beş", "Altı", "Yedi", "Sekiz", "Dokuz"];
	$onlar = ["", "On", "Yirmi", "Otuz", "Kırk", "Elli", "Altmış", "Yetmiş", "Seksen", "Doksan"];
	$basamak = ["", "Bin", "Milyon", "Milyar", "Trilyon"];

	$number = str_replace(",", ".", $number);
	$parts = explode(".", $number);

	$tam = preg_replace('/[^0-9]/', '', $parts[0]);
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
			if ($group == 0)
				continue;

			$yuz = intval($group / 100);
			$on = intval(($group % 100) / 10);
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
		$on = intval($k / 10);
		$bir = $k % 10;

		if ($on > 0)
			$result .= $onlar[$on];
		if ($bir > 0)
			$result .= $birler[$bir];

		$result .= "Krş";
	}

	return $result;
}
