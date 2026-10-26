<?php defined('BASEPATH') or exit('No direct script access allowed');

class Payment extends MY_Controller
{
	private function normalizeGatewayStatus($status)
	{
		$status = strtoupper(trim((string)$status));
		if (in_array($status, ['SUCCESS', 'SUCCEEDED', 'COMPLETED', 'APPROVED'], true)) {
			return 'SUCCESS';
		}
		if (in_array($status, ['FAILURE', 'FAILED', 'DECLINED', 'REJECTED', 'CANCELED', 'CANCELLED', 'ERROR'], true)) {
			return 'FAILURE';
		}
		return $status;
	}

	private function extractGatewayStatus(array $response)
	{
		foreach ([
			$response['paymentStatus'] ?? null,
			$response['data']['paymentStatus'] ?? null,
			$response['result']['paymentStatus'] ?? null,
		] as $status) {
			if (is_string($status) && trim($status) !== '') {
				return $this->normalizeGatewayStatus($status);
			}
		}

		return '';
	}

	private function gatewayResponseHasError(array $response)
	{
		if (!empty($response['transport_error'])) {
			return false;
		}
		if (strtolower(trim((string)($response['status'] ?? ''))) === 'error'
			|| !empty($response['errors'])
			|| !empty($response['error'])) {
			return true;
		}

		foreach ([
			$response['errorCode'] ?? null,
			$response['data']['errorCode'] ?? null,
			$response['result']['errorCode'] ?? null,
		] as $errorCode) {
			if (is_scalar($errorCode) && trim((string)$errorCode) !== '') {
				return true;
			}
		}

		return false;
	}

	private function syncPendingCardOrder($row)
	{
		if ((int)$row->order_payment_type !== 4 || (int)$row->order_status !== 11) {
			return;
		}

		$paymentID = (int)($row->order_payment_token ?? 0);
		if ($paymentID <= 0) {
			return;
		}

		$this->load->library('Magicpay');
		$magicpay = new Magicpay();
		$response = $magicpay->query3dPayment($paymentID);
		$this->orderApiLog((int)$row->order_id, 'payment.query.result_page', 'outgoing', ['paymentId' => $paymentID], $response, ['provider' => 'digigate']);
		// A payment query can report FAILURE while 3D Secure is still in progress,
		// so it can never close an order. Callbacks are the only final signal.
	}

	private function syncPendingWalletTopup($row)
	{
		if ((int)$row->user_balance_status !== 0) {
			return;
		}

		$note = json_decode((string)$row->user_balance_note, true);
		if (!is_array($note) || strtolower((string)($note['payment_method'] ?? '')) !== 'cc') {
			return;
		}

		$paymentID = (int)($note['provider_payment_id'] ?? 0);
		if ($paymentID <= 0) {
			log_message('error', 'Pending wallet top-up has no provider payment ID. Balance: ' . (int)$row->user_balance_id);
			return;
		}

		$this->load->library('Magicpay');
		$magicpay = new Magicpay();
		$response = $magicpay->query3dPayment($paymentID);
		$paymentStatus = $this->extractGatewayStatus($response);
		$hasError = $this->gatewayResponseHasError($response);
		log_message('info', 'Wallet top-up provider status checked. Balance: ' . (int)$row->user_balance_id . ', Payment: ' . $paymentID . ', Status: ' . ($paymentStatus ?: 'UNKNOWN') . ', Error: ' . ($hasError ? 'yes' : 'no'));
		// A query result is never final (it can report FAILURE during 3D Secure).
		// Only the callback flow may credit or fail the wallet top-up.
	}

	private function walletPaymentNote($note, $message)
	{
		$decoded = json_decode((string)$note, true);
		if (!is_array($decoded)) {
			return $message;
		}

		$decoded['message'] = $message;
		return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	public $data = array();
	private const CREDIT_CARD_COMMISSION_RATE = 2.7;
	private const MOBILE_COMMISSION_RATE = 60;

	private function safeCardInfo($cardInfo)
	{
		$number = preg_replace('/\D+/', '', (string)($cardInfo['cc_number'] ?? ''));
		$expiry = trim((string)($cardInfo['cc_expiry'] ?? ''));

		return [
			'cardBin' => strlen($number) >= 6 ? substr($number, 0, 6) : '',
			'cardLast4' => strlen($number) >= 4 ? substr($number, -4) : '',
			'cardHolderName' => trim((string)($cardInfo['cc_name'] ?? '')),
			'cardExpiry' => $expiry,
		];
	}

	public function __construct()
	{
		parent::__construct();
		$this->data['locales'] = $this->locales();
		$this->data['uri'] = $this->uri->segment(1) ? $this->uri->segment(1) : 'US-en';
		$uri = explode('-', $this->data['uri']);
		if (count($uri) < 2) {
			$lang = 'en'; // Default language if not specified
			$country = 'US'; // Default country if not specified
		} else {
			$country = preg_replace('/[^A-Za-z]/', '', (string)$uri[1]);
						$lang = preg_replace('/[^A-Za-z]/', '', (string)$uri[0]);
		}
		$this->lang->load('custom', $lang);
		$this->data['currentLocale'] = $this->db->get_where('locales', ['locale_lang_code' => $lang, 'locale_code' => $country])->row();
		$this->data['user'] = $this->session->userdata('user');
		$this->data['lang'] = $lang;
		$this->data['country'] = $country;
		$this->load->model('Wallet_model');
		$this->data['userBalance'] = $this->Wallet_model->get_user_balances($this->data['user']['user_id'] ?? 0)['spendable_balance'] ?? 0;
	}

	public function index()
	{
		if (!empty($this->session->userdata('user'))) {
			redirect(topupcenter() . $this->data['uri']);
		}
		redirect(topupcenter() . $this->data['uri'] . '/login');
	}

	public function checkout()
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$restriction = customerRestriction($userID, 'purchase');
		if ($restriction) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => $restriction->restriction_customer_reason]);
			redirect(topupcenter() . $this->data['uri']);
		}
		$cartData = $this->session->userdata('checkout_cart');
		if (empty($cartData)) {
			redirect($_SERVER['HTTP_REFERER'] ?? topupcenter() . $this->data['uri']);
		}

		$data = $this->data;

		// Checkout'a her girişte oturum kilidi sıfırlanır; eşzamanlı gönderimi finalize'daki DB kilidi engeller.
		$this->session->unset_userdata('checkout_submission_lock');

		// Fiyat ve indirim değişkenlerini sağlama alalım
		$cartPrice = $cartData['price'] ?? 0;
		$couponDiscount = $this->session->userdata('applied_coupon')['discount'] ?? 0;
		$netTotal = $cartPrice - $couponDiscount;
		if ($netTotal < 0) {
			$netTotal = 0;
		}

		// Kredi kartı komisyonu indirim uygulanmış net tutar üzerinden hesaplanır.
		$creditCardCommissionRate = self::CREDIT_CARD_COMMISSION_RATE;
		$creditCardTotal = round($netTotal * (1 + ($creditCardCommissionRate / 100)), 2);
		$creditCardFee = round($creditCardTotal - $netTotal, 2);

		// View katmanına saf, hesaplanmış dataları gönderiyoruz
		$data['cartPrice']       = $cartPrice;
		$data['couponDiscount']  = $couponDiscount;
		$data['netTotal']        = $netTotal;
		$data['creditCardTotal'] = $creditCardTotal;
		$data['creditCardCommissionRate'] = $creditCardCommissionRate;
		$data['creditCardFee']   = $creditCardFee;
		$data['mobileCommissionRate'] = self::MOBILE_COMMISSION_RATE;
		$data['mobileTotal'] = round($netTotal * (1 + (self::MOBILE_COMMISSION_RATE / 100)), 2);
		$data['mobileFee'] = round($data['mobileTotal'] - $netTotal, 2);
		$data['cartData']        = $cartData;
		$data['banks']           = $this->db->select()->from('banks')->where(['bank_status' => 1])->order_by('bank_sort_id')->get()->result();
		$data['user_details']    = !empty($this->data['user']['user_id']) ? $this->db->get_where('users', ['user_id' => $this->data['user']['user_id']])->row() : null;

		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$userAddresses = [];
		if ($userID > 0) {
			$userAddresses = $this->db->where([
				'user_addresses_user_id' => $userID,
				'user_addresses_is_deleted' => 0
			])->order_by('user_addresses_is_default', 'DESC')->order_by('user_addresses_id', 'DESC')->get('user_addresses')->result();
		}
		$data['user_addresses'] = $userAddresses;

		$citiesFilePath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'cities.json';
		$citiesData = [];
		if (file_exists($citiesFilePath)) {
			$citiesData = json_decode(file_get_contents($citiesFilePath), true);
		}

		$groupedCities = [];
		if (is_array($citiesData)) {
			foreach ($citiesData as $item) {
				if (isset($item['il']) && isset($item['ilce'])) {
					$groupedCities[$item['il']][] = $item['ilce'];
				}
			}
		}

		$data['cities_json'] = json_encode($groupedCities);
		// pre($data['banks']);die;

		$this->template->content->view('checkout', $data);
		$this->template->publish();
	}

	public function invoice($orderID = null)
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$orderID = (int)$orderID;
		if (!$userID || !$orderID) {
			show_404();
		}

		$order = $this->db->select('o.*, u.user_name, u.user_surname, u.user_email, p.product_name')
			->from('orders as o')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->where('o.order_id', $orderID)
			->where('o.order_reseller_id', $userID)
			->limit(1)
			->get()->row();

		if (!$order) {
			show_404();
		}

		$this->load->view('payment/invoice', ['order' => $order]);
	}

	public function updateBillingCheckout()
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		if (!$userID) {
			return json(['status' => 'error', 'message' => 'Fatura bilgilerini güncellemek için giriş yapmalısınız.'], 401);
		}

		$post = $this->input->post(NULL, TRUE);

		$addressID = (int)($post['user_addresses_id'] ?? $post['address_id'] ?? 0);
		$addressTitle = strip_tags(trim($post['user_addresses_title'] ?? ''));

		$invoiceType = (($post['user_addresses_invoice_type'] ?? '') === '2' || ($post['invoice_type'] ?? '') === 'corporate') ? 2 : 1;
		$isCorporate = ($invoiceType === 2);
		$isTurkishCitizen = isset($post['user_addresses_is_turkish_citizen']) ? ((int)$post['user_addresses_is_turkish_citizen'] === 1) : (($post['is_turkish_citizen'] ?? '1') === '1');
		$isTurkishCompany = isset($post['user_addresses_is_turkish_company']) ? ((int)$post['user_addresses_is_turkish_company'] === 1) : (($post['is_turkish_company'] ?? '1') === '1');
		$eInvoice = isset($post['user_addresses_is_e_invoice_taxpayer']) ? (int)$post['user_addresses_is_e_invoice_taxpayer'] : ((($post['user_bill_e_invoice'] ?? '') === '1') ? 1 : 0);

		if (empty($addressTitle)) {
			$addressTitle = $isCorporate ? 'Şirket Fatura Adresi' : 'Bireysel Fatura Adresi';
		}

		$fullName = strip_tags(trim($post['user_addresses_full_name'] ?? $post['user_bill_company_name'] ?? ''));
		$companyName = strip_tags(trim($post['user_addresses_company_name'] ?? $post['user_bill_company_name'] ?? ''));
		$taxOffice = $isCorporate ? strip_tags(trim($post['user_addresses_tax_office'] ?? $post['user_bill_tax_office'] ?? '')) : '';
		$taxNumber = strip_tags(trim($post['user_addresses_tax_number'] ?? $post['user_bill_tax_number'] ?? ''));
		$addressPhone = strip_tags(trim($post['user_addresses_phone'] ?? $post['user_bill_phone'] ?? ''));
		if ($addressPhone === '') {
			$addressPhone = $this->data['user']['user_phone'] ?? '';
		}

		$isTurkey = $isCorporate ? $isTurkishCompany : $isTurkishCitizen;

		if ($isTurkey) {
			$country = 'Türkiye';
			$city = strip_tags(trim($post['user_addresses_city'] ?? $post['user_bill_city'] ?? ''));
			$district = strip_tags(trim($post['user_addresses_district'] ?? $post['user_bill_district'] ?? ''));
		} else {
			$country = strip_tags(trim($post['user_addresses_country'] ?? 'Yurtdışı'));
			if ($country === '') $country = 'Yurtdışı';
			$city = strip_tags(trim($post['user_addresses_foreign_city'] ?? $post['user_addresses_city'] ?? $post['user_bill_city'] ?? ''));
			$district = strip_tags(trim($post['user_addresses_foreign_district'] ?? $post['user_addresses_district'] ?? $post['user_bill_district'] ?? ''));
		}

		$address = strip_tags(trim($post['user_addresses_address'] ?? $post['user_bill_address'] ?? ''));
		$postalCode = strip_tags(trim($post['user_addresses_postal_code'] ?? ''));

		if ($isCorporate) {
			if (empty($companyName)) return json(['status' => 'error', 'message' => 'Lütfen firma unvanını giriniz.'], 422);
			if ($isTurkishCompany) {
				if (empty($taxOffice)) return json(['status' => 'error', 'message' => 'Lütfen vergi dairesini giriniz.'], 422);
				if (empty($taxNumber)) return json(['status' => 'error', 'message' => 'Lütfen vergi numaranızı giriniz.'], 422);
			}
		} else {
			if (empty($fullName)) return json(['status' => 'error', 'message' => 'Lütfen ad soyad giriniz.'], 422);
			if ($isTurkishCitizen && empty($taxNumber)) return json(['status' => 'error', 'message' => 'Lütfen T.C. kimlik numaranızı giriniz.'], 422);
		}

		if (empty($city)) return json(['status' => 'error', 'message' => 'Lütfen şehir seçiniz.'], 422);
		if ($isTurkey && empty($district)) return json(['status' => 'error', 'message' => 'Lütfen ilçe seçiniz.'], 422);
		if (empty($address)) return json(['status' => 'error', 'message' => 'Lütfen açık adres giriniz.'], 422);

		// user_addresses tablosuna kaydet / güncelle
		$addressData = [
			'user_addresses_user_id' => $userID,
			'user_addresses_title' => $addressTitle,
			'user_addresses_invoice_type' => $isCorporate ? 2 : 1,
			'user_addresses_is_turkish_citizen' => $isTurkishCitizen ? 1 : 0,
			'user_addresses_is_turkish_company' => $isTurkishCompany ? 1 : 0,
			'user_addresses_full_name' => $fullName,
			'user_addresses_company_name' => $companyName,
			'user_addresses_phone' => $addressPhone,
			'user_addresses_tax_office' => $taxOffice,
			'user_addresses_tax_number' => $taxNumber,
			'user_addresses_is_e_invoice_taxpayer' => $eInvoice,
			'user_addresses_country' => $country,
			'user_addresses_city' => $city,
			'user_addresses_district' => $district,
			'user_addresses_postal_code' => $postalCode,
			'user_addresses_address' => $address,
			'user_addresses_is_default' => 1,
			'user_addresses_is_deleted' => 0,
			'user_addresses_updated_at' => date('Y-m-d H:i:s'),
		];

		// Önceki varsayılanları sıfırla
		$this->db->where('user_addresses_user_id', $userID)->update('user_addresses', ['user_addresses_is_default' => 0]);

		if ($addressID > 0) {
			$this->db->where('user_addresses_id', $addressID)->where('user_addresses_user_id', $userID)->update('user_addresses', $addressData);
		} else {
			$addressData['user_addresses_created_at'] = date('Y-m-d H:i:s');
			$this->db->insert('user_addresses', $addressData);
			$addressID = (int)$this->db->insert_id();
		}

		// users tablosunu senkronize et
		$userUpdate = [
			'user_bill_company_name'  => $isCorporate ? $companyName : $fullName,
			'user_bill_tax_office'    => $taxOffice,
			'user_bill_tax_number'    => $taxNumber,
			'user_bill_city'          => $city,
			'user_bill_district'      => $district,
			'user_bill_address'       => $address,
			'user_bill_e_invoice'     => $eInvoice,
			'user_is_turkish_company' => $isCorporate && $isTurkishCompany ? 1 : 0,
		];
		$this->db->where('user_id', $userID)->update('users', $userUpdate);

		return json(['status' => 'success', 'message' => 'Fatura bilgileriniz güncellendi.', 'address_id' => $addressID]);
	}

	public function selectAddressCheckout()
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		if (!$userID) {
			return json(['status' => 'error', 'message' => 'Fatura adresi seçmek için giriş yapmalısınız.'], 401);
		}

		$addressID = (int)$this->input->post('address_id', TRUE);
		if (!$addressID) {
			return json(['status' => 'error', 'message' => 'Geçersiz adres seçimi.'], 422);
		}

		$addr = $this->db->where([
			'user_addresses_id' => $addressID,
			'user_addresses_user_id' => $userID,
			'user_addresses_is_deleted' => 0
		])->get('user_addresses')->row();

		if (!$addr) {
			return json(['status' => 'error', 'message' => 'Adres bulunamadı.'], 404);
		}

		$this->db->where('user_addresses_user_id', $userID)->update('user_addresses', ['user_addresses_is_default' => 0]);
		$this->db->where('user_addresses_id', $addressID)->update('user_addresses', [
			'user_addresses_is_default' => 1,
			'user_addresses_updated_at' => date('Y-m-d H:i:s')
		]);

		$userUpdate = [
			'user_bill_company_name'  => ($addr->user_addresses_invoice_type == 2) ? $addr->user_addresses_company_name : $addr->user_addresses_full_name,
			'user_bill_tax_office'    => ($addr->user_addresses_invoice_type == 2) ? $addr->user_addresses_tax_office : '',
			'user_bill_tax_number'    => $addr->user_addresses_tax_number,
			'user_bill_city'          => $addr->user_addresses_city,
			'user_bill_district'      => $addr->user_addresses_district,
			'user_bill_address'       => $addr->user_addresses_address,
			'user_bill_e_invoice'     => $addr->user_addresses_is_e_invoice_taxpayer,
			'user_is_turkish_company' => $addr->user_addresses_is_turkish_company
		];
		$this->db->where('user_id', $userID)->update('users', $userUpdate);

		return json(['status' => 'success', 'message' => 'Fatura adresi seçildi.']);
	}

	private function checkoutPhoneUser($userID)
	{
		return $this->db->select('user_phone, user_phone_is_verified')
			->where('user_id', (int)$userID)->limit(1)->get('users')->row();
	}

	private function verifiedMobilePhone($userID): string
	{
		$row = $this->checkoutPhoneUser($userID);
		if (!$row || (int)$row->user_phone_is_verified !== 1) {
			return '';
		}
		$phone = $this->normalizeMobilePhoneNumber((string)$row->user_phone);
		return $this->isValidMobilePhoneNumber($phone) ? $phone : '';
	}

	public function requestCheckoutPhoneOtp()
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$phone = trim((string)$this->input->post('phone_number', TRUE));
		$phone = $this->normalizeMobilePhoneNumber($phone);
		if (!$userID || !$this->isValidMobilePhoneNumber($phone)) {
			return json(['status' => 'error', 'message' => 'Lütfen geçerli bir telefon numarası girin.'], 422);
		}

		$otpCode = (string)random_int(100000, 999999);
		$this->db->where('2fa_user_id', $userID)->where('2fa_method', 2)->where('2fa_status', 0)
			->update('user_2fa_codes', ['2fa_status' => 2]);
		$this->db->insert('user_2fa_codes', [
			'2fa_user_id' => $userID, '2fa_otp_code' => $otpCode, '2fa_method' => 2,
			'2fa_status' => 0, '2fa_created_at' => date('Y-m-d H:i:s'),
			'2fa_expires_at' => date('Y-m-d H:i:s', strtotime('+3 minutes')),
		]);
		$this->session->set_userdata('checkout_phone_pending', $phone);
		sendSms2($phone, 'Playsel Checkout telefon doğrulama kodunuz: ' . $otpCode);
		return json(['status' => 'success', 'message' => 'Doğrulama kodu telefonunuza gönderildi.']);
	}

	public function verifyCheckoutPhoneOtp()
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$otpCode = trim((string)$this->input->post('otp_code', TRUE));
		$code = $this->db->where([
			'2fa_user_id' => $userID, '2fa_otp_code' => $otpCode, '2fa_method' => 2,
			'2fa_status' => 0,
		])->where('2fa_expires_at >=', date('Y-m-d H:i:s'))->limit(1)->get('user_2fa_codes')->row();
		$phone = $this->session->userdata('checkout_phone_pending');
		if (!$userID || !$code || !$phone) {
			return json(['status' => 'error', 'message' => 'Doğrulama kodu hatalı veya süresi dolmuş.'], 422);
		}

		$this->db->where('2fa_id', $code->{'2fa_id'})->update('user_2fa_codes', ['2fa_status' => 1]);
		$this->db->where('user_id', $userID)->update('users', [
			'user_phone' => normalizePhoneE164($phone), 'user_phone_is_verified' => 1, 'user_phone_temp' => NULL,
		]);
		$sessionUser = $this->session->userdata('user');
		$sessionUser['user_phone'] = $phone;
		$this->session->set_userdata('user', $sessionUser);
		$this->session->unset_userdata('checkout_phone_pending');
		return json(['status' => 'success', 'message' => 'Telefon numaranız doğrulandı.']);
	}

	private function resultTokenKey()
	{
		$key = getenv('ENCRYPTION_KEY') ?: getenv('APP_KEY') ?: config_item('encryption_key');
		if (!$key) {
			$key = hash('sha256', (string)config_item('base_url') . '|playsel-payment-result', true);
		}

		return hash('sha256', (string)$key, true);
	}

	private function createResultToken($type, $id, $userID)
	{
		$payload = json_encode([
			'type' => $type,
			'id' => (int)$id,
			'user_id' => (int)$userID,
			'expires_at' => time() + 86400,
		], JSON_UNESCAPED_SLASHES);
		$iv = random_bytes(12);
		$tag = '';
		$ciphertext = openssl_encrypt($payload, 'aes-256-gcm', $this->resultTokenKey(), OPENSSL_RAW_DATA, $iv, $tag);

		if ($ciphertext === false) {
			return null;
		}

		return rtrim(strtr(base64_encode($iv . $tag . $ciphertext), '+/', '-_'), '=');
	}

	private function readResultToken($token)
	{
		$encoded = strtr((string)$token, '-_', '+/');
		$padding = strlen($encoded) % 4;
		if ($padding > 0) {
			$encoded .= str_repeat('=', 4 - $padding);
		}
		$raw = base64_decode($encoded, true);

		if ($raw === false || strlen($raw) <= 28) {
			return null;
		}

		$iv = substr($raw, 0, 12);
		$tag = substr($raw, 12, 16);
		$ciphertext = substr($raw, 28);
		$payload = openssl_decrypt($ciphertext, 'aes-256-gcm', $this->resultTokenKey(), OPENSSL_RAW_DATA, $iv, $tag);
		$data = $payload !== false ? json_decode($payload, true) : null;

		if (!is_array($data) || !in_array($data['type'] ?? '', ['order', 'balance'], true) || (int)($data['id'] ?? 0) <= 0 || (int)($data['user_id'] ?? 0) <= 0 || (int)($data['expires_at'] ?? 0) < time()) {
			return null;
		}

		return $data;
	}

	private function resultUrl($type, $id, $userID)
	{
		$token = $this->createResultToken($type, $id, $userID);
		return $token ? topupcenter() . $this->data['uri'] . '/payment/result?token=' . rawurlencode($token) : null;
	}

	public function result()
	{
		$tokenData = $this->readResultToken($this->input->get('token'));
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$type = $tokenData['type'] ?? null;
		$recordID = (int)($tokenData['id'] ?? 0);

		if (!$tokenData || !$userID || (int)$tokenData['user_id'] !== $userID || !$recordID) {
			show_404();
		}

		if ($type === 'order') {
			$ownedOrder = $this->db->select('order_id')
				->from('orders')
				->where('order_id', $recordID)
				->where('order_reseller_id', $userID)
				->limit(1)
				->get()->row();

			if (!$ownedOrder) {
				show_404();
			}
		} else {
			$ownedBalance = $this->db->select('user_balance_id')
				->from('user_balance')
				->where('user_balance_id', $recordID)
				->where('user_balance_user_id', $userID)
				->limit(1)
				->get()->row();

			if (!$ownedBalance) {
				show_404();
			}
		}

		$data = $this->data;
		$data['result_type'] = $type;
		$data['result_id'] = $recordID;
		$data['result_token'] = $this->input->get('token');
		$data['pageTitle'] = $type === 'order' ? 'Ödeme Sonucu' : 'Bakiye Yükleme Sonucu';
		$data['title'] = ($data['title'] ?? '') . $data['pageTitle'];
		$data['description'] = 'Ödeme işleminizin güncel durumunu görüntüleyin.';

		$this->template->content->view('result', $data);
		$this->template->publish();
	}

	public function resultStatus()
	{
		$tokenData = $this->readResultToken($this->input->get('token'));
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$type = $tokenData['type'] ?? null;
		$id = (int)($tokenData['id'] ?? 0);

		if (!$tokenData || !$userID || (int)$tokenData['user_id'] !== $userID || !$id) {
			$this->db->insert('logs', [
				'log_controller' => 'payment.resultStatus',
				'log_message' => json_encode([
					'message' => 'Result token doğrulanamadı.',
					'has_token' => !empty($tokenData),
					'user_id' => $userID,
					'id' => $id,
				], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				'log_error_code' => 'result_token_invalid',
			]);
			return json(['status' => 'error', 'message' => 'Geçersiz sonuç sorgusu.'], 400);
		}

		if ($type === 'order') {
			$row = $this->db->select('o.order_id, o.order_status, o.order_payment_type, o.order_eft_id, o.order_eft_bank_id, o.order_payment_token, o.order_reseller_order_id, o.order_sale_price, o.order_sale_price_currency, o.order_voucher, o.order_data, o.order_payment_info, o.order_require_data, o.order_created_at, o.order_sended_at, o.order_answered_at, o.order_supplier_answer, p.product_name, p.product_icon')
				->from('orders as o')
				->join('products as p', 'p.product_id = o.order_product_id', 'left')
				->where('o.order_id', $id)
				->where('o.order_reseller_id', $userID)
				->get()->row();

			if (!$row) {
				$this->db->insert('logs', [
					'log_controller' => 'payment.resultStatus',
					'log_message' => json_encode([
						'message' => 'Result siparişi bulunamadı.',
						'order_id' => $id,
						'user_id' => $userID,
					], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
					'log_error_code' => 'result_order_not_found',
					'log_order_id' => $id,
				]);
				return json(['status' => 'error', 'message' => 'Sipariş bulunamadı.'], 404);
			}

			// Status 1 is reserved for supplier delivery completion. Repair
			// legacy records that were marked completed before supplier handoff.
			if (
				(int)$row->order_status === 1
				&& empty($row->order_sended_at)
				&& empty($row->order_answered_at)
				&& (int)$row->order_supplier_answer !== 4
				&& empty($row->order_voucher)
			) {
				$this->db->where('order_id', (int)$row->order_id)
					->where('order_status', 1)
					->update('orders', ['order_status' => 11]);
				$row->order_status = 11;
				$this->db->insert('logs', [
					'log_controller' => 'payment.resultStatus.repair',
					'log_message' => json_encode([
						'message' => 'Tedarikçi teslimi olmadan tamamlanan sipariş beklemeye alındı.',
						'order_id' => (int)$row->order_id,
					], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
					'log_error_code' => 'invalid_order_completed_status',
					'log_order_id' => (int)$row->order_id,
				]);
			}

			$this->syncPendingCardOrder($row);

			$orderGroup = [$row];
			$firstPaymentInfo = json_decode((string)$row->order_payment_info, true);
			$firstSnapshot = is_array($firstPaymentInfo) ? ($firstPaymentInfo['pricing_snapshot'] ?? []) : [];
			$bundleReference = trim((string)($firstSnapshot['bundle_reference'] ?? ''));
			if ($bundleReference !== '') {
				$bundleOrders = $this->db->select('o.order_id, o.order_product_id, o.order_status, o.order_sale_price, o.order_sale_price_currency, o.order_voucher, o.order_payment_info, o.order_data, o.order_created_at, p.product_name')
					->from('orders as o')
					->join('products as p', 'p.product_id = o.order_product_id', 'left')
					->where('o.order_reseller_id', $userID)
					->where('o.order_created_at', $row->order_created_at)
					->get()->result();
				$matchedOrders = [];
				foreach ($bundleOrders as $bundleOrder) {
					$bundleOrderInfo = json_decode((string)$bundleOrder->order_payment_info, true);
					$bundleOrderSnapshot = is_array($bundleOrderInfo)
						? ($bundleOrderInfo['pricing_snapshot'] ?? [])
						: [];
					if (($bundleOrderSnapshot['bundle_reference'] ?? '') === $bundleReference) {
						$matchedOrders[] = $bundleOrder;
					}
				}
				if (count($matchedOrders) === 1 && !empty($firstSnapshot['bundle_product_id'])) {
					foreach ($bundleOrders as $bundleOrder) {
						if ((int)$bundleOrder->order_id === (int)$row->order_id) {
							continue;
						}
						$bundleOrderInfo = json_decode((string)$bundleOrder->order_payment_info, true);
						$bundleOrderSnapshot = is_array($bundleOrderInfo)
							? ($bundleOrderInfo['pricing_snapshot'] ?? [])
							: [];
						if ((int)($bundleOrderSnapshot['bundle_product_id'] ?? 0) === (int)$firstSnapshot['bundle_product_id']) {
							$matchedOrders[] = $bundleOrder;
						}
					}
				}
				if (!empty($matchedOrders)) {
					$orderGroup = $matchedOrders;
				}
			}

			$groupSalePrice = array_sum(array_map(function ($order) {
				return (float)$order->order_sale_price;
			}, $orderGroup));
			$items = array_map(function ($order) {
				return [
					'name' => (string)($order->product_name ?? ''),
					'amount' => number_format((float)$order->order_sale_price, 2, ',', '.'),
					'currency' => $order->order_sale_price_currency ?: 'TRY',
					'status' => (int)$order->order_status,
				];
			}, $orderGroup);
			$displayName = $row->product_name ?: 'Dijital Ürün';
			$displayIcon = $row->product_icon ?: '';
			if (!empty($firstSnapshot['bundle_product_name'])) {
				$displayName = (string)$firstSnapshot['bundle_product_name'];
				$bundleProductID = (int)($firstSnapshot['bundle_product_id'] ?? 0);
				if ($bundleProductID > 0) {
					$bundleProduct = $this->db->select('product_name, product_icon')
						->where('product_id', $bundleProductID)
						->get('products')->row();
					if ($bundleProduct) {
						$displayName = $bundleProduct->product_name ?: $displayName;
						$displayIcon = $bundleProduct->product_icon ?: $displayIcon;
					}
				}
			}

			$orderData = json_decode((string)$row->order_data, true);
			$orderData = is_array($orderData) ? $orderData : [];
			$requireData = json_decode((string)$row->order_require_data, true);
			$requireData = is_array($requireData) ? $requireData : [];
			$cancellationReason = trim((string)($orderData['cancellation_reason'] ?? $requireData['cancellation_reason'] ?? ''));
			$orderPaymentMethodLabels = [
				2 => 'Cüzdan',
				3 => 'Banka havalesi',
				4 => 'Kredi kartı',
				5 => 'Mobil ödeme',
			];

			$transferInfo = [];
			if ((int)$row->order_payment_type === 3) {
				$transferInfo = $this->transferResultInfo((int)$row->order_eft_bank_id, (string)$row->order_eft_id);
			}

			return json([
				'status' => 'success',
				'entity' => 'order',
				'data' => array_merge([
					'id' => (int)$row->order_id,
					'order_status' => (int)$row->order_status,
					'payment_type' => (int)$row->order_payment_type,
					'amount' => number_format($bundleReference !== '' ? $groupSalePrice : (float)$row->order_sale_price, 2, ',', '.'),
					'currency' => $row->order_sale_price_currency ?: 'TRY',
					'part_count' => count($orderGroup),
					'items' => $items,
					'cancellation_reason' => $cancellationReason,
					'require_data' => $requireData,
					'product_name' => $displayName,
					'product_icon' => $displayIcon,
					'has_voucher' => !empty($row->order_voucher),
					'payment_method_label' => $orderPaymentMethodLabels[(int)$row->order_payment_type] ?? 'Ödeme',
				], $transferInfo),
			]);
		}

		$row = $this->db->select('user_balance_id, user_balance_status, user_balance_amount, user_balance_bank_id, user_balance_reference_no, user_balance_note')
			->from('user_balance')
			->where('user_balance_id', $id)
			->where('user_balance_user_id', $userID)
			->get()->row();

		if (!$row) {
			log_message('warning', 'Result balance not found. Balance: ' . $id . ', User: ' . $userID);
			return json(['status' => 'error', 'message' => 'Bakiye işlemi bulunamadı.'], 404);
		}

		log_message('info', 'Result balance status requested. Balance: ' . (int)$row->user_balance_id . ', User: ' . $userID . ', Status: ' . (int)$row->user_balance_status);
		$this->syncPendingWalletTopup($row);
		if ((int)$row->user_balance_status === 0) {
			$latestRow = $this->db
				->select('user_balance_status, user_balance_note')
				->where('user_balance_id', (int)$row->user_balance_id)
				->get('user_balance')
				->row();
			if ($latestRow) {
				$row->user_balance_status = (int)$latestRow->user_balance_status;
				$row->user_balance_note = $latestRow->user_balance_note;
			}
		}

		$paymentMethod = '';
		$paymentMethodLabel = 'Bakiye yükleme';
		$bankName = '';
		$transferReference = '';
		$bankIban = '';
		$bankOwner = '';
		$paymentNote = json_decode((string)$row->user_balance_note, true);
		if (is_array($paymentNote)) {
			$paymentMethod = strtolower(trim((string)($paymentNote['payment_method'] ?? '')));
		}
		if ((int)($row->user_balance_bank_id ?? 0) > 0) {
			$paymentMethod = 'eft';
			$transferReference = (string)$row->user_balance_reference_no;
			$bank = $this->db->select('bank_name, bank_iban, bank_owner')
				->where('bank_id', (int)$row->user_balance_bank_id)
				->get('banks')->row();
			$bankName = $bank ? (string)$bank->bank_name : '';
			$bankIban = $bank ? (string)$bank->bank_iban : '';
			$bankOwner = $bank ? (string)$bank->bank_owner : '';
		} elseif ($paymentMethod === 'cc') {
			$paymentMethodLabel = 'Kredi kartı';
		} elseif ($paymentMethod === 'mobile') {
			$paymentMethodLabel = 'Mobil ödeme';
		} else {
			$paymentMethod = '';
		}
		if ($paymentMethod === 'eft') {
			$paymentMethodLabel = 'Banka havalesi';
		}

		return json([
			'status' => 'success',
			'entity' => 'balance',
			'data' => [
				'id' => (int)$row->user_balance_id,
				'balance_status' => (int)$row->user_balance_status,
				'amount' => number_format((float)$row->user_balance_amount, 2, ',', '.'),
				'reference_no' => $row->user_balance_reference_no ?: '',
				'note' => $row->user_balance_note ?: '',
				'payment_method' => $paymentMethod,
				'payment_method_label' => $paymentMethodLabel,
				'bank_name' => $bankName,
				'bank_iban' => $bankIban,
				'bank_owner' => $bankOwner,
				'transfer_reference' => $transferReference,
			],
		]);
	}

	private function transferResultInfo(int $bankID, string $reference): array
	{
		$bank = $bankID > 0
			? $this->db->select('bank_name, bank_iban, bank_owner')->where('bank_id', $bankID)->get('banks')->row()
			: null;
		return [
			'payment_method' => 'eft',
			'bank_name' => $bank ? (string)$bank->bank_name : '',
			'bank_iban' => $bank ? (string)$bank->bank_iban : '',
			'bank_owner' => $bank ? (string)$bank->bank_owner : '',
			'transfer_reference' => $reference,
		];
	}

	public function startPayment()
	{
		$data = $this->data;
		$post = $this->input->post();
		$pid  = !empty($post['pid']) ? (int)$post['pid'] : null;

		if (empty($pid)) {
			return json(['status' => 'error', 'message' => 'Ürün ID bilgiisi eksik.']);
		}

		$getProduct = $this->db->get_where('products', ['product_id' => $pid, 'product_status' => 1])->row();
		if (!$getProduct) {
			return json(['status' => 'error', 'message' => 'Geçersiz veya yayından kaldırılmış ürün.']);
		}

		$userTier = (int)($data['user']['user_tier'] ?? 1);
		$userPrice = userPrice($pid, $data['currentLocale']->locale_code, $userTier);
		if ((int)$getProduct->product_is_bundle !== 1 && (float)$userPrice <= 0) {
			return json(['status' => 'error', 'message' => 'Bu ürün şu anda stokta bulunmuyor.']);
		}

		$uid = !empty($post['uid']) ? trim((string)$post['uid']) : null;
		$sid = !empty($post['sid']) ? trim((string)$post['sid']) : null;
		$gameIdPattern = '/^[\p{L}\p{N} _.\-#@:]{1,100}$/u';
		if (($uid !== null && !preg_match($gameIdPattern, $uid)) || ($sid !== null && !preg_match($gameIdPattern, $sid))) {
			return json(['status' => 'error', 'message' => 'Oyuncu ID veya Sunucu ID geçersiz karakterler içeriyor.']);
		}

		if ((int)$getProduct->product_is_require_user_id === 1 && empty($uid)) {
			return json(['status' => 'error', 'message' => 'Bu ürün için Oyuncu ID (User ID) girilmesi zorunludur.']);
		}
		if ((int)$getProduct->product_is_require_server_id === 1 && empty($sid)) {
			return json(['status' => 'error', 'message' => 'Bu ürün için Sunucu ID (Server ID) seçilmesi zorunludur.']);
		}

		$qty = ((int)$getProduct->product_is_bundle === 1) ? 1 : max(1, min(10, (int)($post['qty'] ?? 1)));

		$cartData = [
			'product_id'        => $getProduct->product_id,
			'product_name'      => $getProduct->product_name,
			'category_id'       => $getProduct->product_category_id,
			'sub_category_id'   => $getProduct->product_sub_category_id,
			'product_image'     => $getProduct->product_image,
			'product_icon'      => $getProduct->product_icon,
			'price'             => round((float)$userPrice * $qty, 2),
			'unit_price'        => $userPrice,
			'user_id'           => $uid,
			'server_id'         => $sid,
			'provider_id'       => $getProduct->product_provider_id,
			'product_type'      => $getProduct->product_type,
			'is_bundle'         => $getProduct->product_is_bundle,
			'qty'               => $qty,
			'quantity'          => $qty,
			'created_at'        => date('Y-m-d H:i:s'),
		];

		$this->session->set_userdata('checkout_cart', $cartData);
		$this->session->unset_userdata('applied_coupon');
		$this->session->unset_userdata('checkout_submission_lock');

		// İşte seni doğrudan checkout sayfasına fırlatan o redirect_url yapısı
		return json([
			'status'       => 'success',
			'message'      => 'Sepet işlemleri başarıyla tamamlandı, ödeme sayfasına aktarılıyorsunuz.',
			'redirect_url' => topupcenter() . $this->data['uri'] . '/payment/checkout'
		]);
	}

	public function useCoupon()
	{
		$data = $this->data;
		$post = $this->input->post();
		$couponCode = !empty($post['coupon_code']) ? trim($post['coupon_code']) : null;

		if (empty($couponCode)) {
			if ($this->input->is_ajax_request()) {
				return json(['status' => 'error', 'message' => 'Kupon kodu boş olamaz.']);
			}
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Kupon kodu boş olamaz.']);
			redirect($this->data['uri'] . '/payment/checkout');
		}

		$this->load->model('coupon_model');
		$userID = $this->data['user']['user_id'] ?? null;
		$cartItems = $this->session->userdata('checkout_cart') ? [$this->session->userdata('checkout_cart')] : [];
		$coupon = $this->coupon_model->validate_and_calculate($couponCode, $userID, $cartItems, $data['currentLocale']->locale_code);

		if ($coupon['status'] === 'error') {
			if ($this->input->is_ajax_request()) {
				return json(['status' => 'error', 'message' => $coupon['message']]);
			}
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => $coupon['message']]);
			redirect($this->data['uri'] . '/payment/checkout');
		} else {
			$this->session->set_userdata('applied_coupon', [
				'discount'     => $coupon['discount'],
				'coupon_code'  => $couponCode,
				'coupon_id'    => $coupon['coupon_id'],
				'coupon_type'  => $coupon['coupon_type'],
				'coupon_value' => $coupon['coupon_value'],
			]);

			if ($this->input->is_ajax_request()) {
				$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Kupon başarıyla uygulandı. İndirim: ' . nFormat($coupon['discount']) . ' TL']);
				return json([
					'status' => 'success',
					'message' => 'Kupon başarıyla uygulandı.',
					'redirect_url' => topupcenter() . $this->data['uri'] . '/payment/checkout'
				]);
			}

			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Kupon başarıyla uygulandı. İndirim: ' . nFormat($coupon['discount']) . ' TL']);
			redirect($this->data['uri'] . '/payment/checkout');
		}
	}

	public function removeCoupon()
	{
		$this->session->unset_userdata('applied_coupon');

		if ($this->input->is_ajax_request()) {
			return json([
				'status' => 'success',
				'message' => 'Kupon kaldırıldı.',
				'redirect_url' => topupcenter() . $this->data['uri'] . '/payment/checkout'
			]);
		}

		$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Kupon kaldırıldı.']);
		redirect($this->data['uri'] . '/payment/checkout');
	}

	public function finalize()
	{
		// PHP uyarıları/DB hata sayfaları JSON yanıtını bozup "Sistem hatası" gösteriyordu.
		ini_set('display_errors', '0');
		$this->db->db_debug = false;
		$finalizeDb = $this->db;
		register_shutdown_function(function () use ($finalizeDb) {
			$error = error_get_last();
			if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR], true)) {
				@$finalizeDb->insert('logs', [
					'log_controller' => 'payment.finalize.fatal',
					'log_message' => json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
					'log_error_code' => 'finalize_fatal',
				]);
			}
		});
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$restriction = customerRestriction($userID, 'purchase');
		if ($restriction) {
			return json(['status' => 'error', 'message' => $restriction->restriction_customer_reason], 403);
		}
		$post          = $this->input->post();
		$paymentMethod = !empty($post['payment_method']) && is_string($post['payment_method']) ? trim($post['payment_method']) : null;
		$paymentMethod = strtolower((string)$paymentMethod);
		$paymentMethodAliases = [
			'card' => 'credit',
			'cc' => 'credit',
			'eft' => 'transfer',
		];
		$paymentMethod = $paymentMethodAliases[$paymentMethod] ?? $paymentMethod;
		$post['payment_method'] = $paymentMethod;
		if (empty($post['cc_expire']) && isset($post['cc_expiry'])) {
			$post['cc_expire'] = $post['cc_expiry'];
		}
		$cartData      = $this->session->userdata('checkout_cart');
		$userID        = $this->data['user']['user_id'] ?? null;

		if (empty($cartData)) {
			return json(['status' => 'error', 'message' => 'Sepetinizde ürün bulunamadı veya oturum süreniz doldu.']);
		}

		if (empty($userID)) {
			return json(['status' => 'error', 'message' => 'Lütfen bu işlem için önce giriş yapın.']);
		}

		// Eşzamanlı çift/flood gönderimleri DB seviyesinde engelle (oturum kilidi yarış durumuna açıktır).
		$finalizeLockName = 'playsel_checkout_' . (int)$userID;
		$finalizeLock = $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$finalizeLockName])->row();
		if (!$finalizeLock || (int)$finalizeLock->acquired !== 1) {
			return json(['status' => 'error', 'message' => 'Önceki ödeme isteğiniz işleniyor, lütfen bekleyin.'], 429);
		}
		$db = $this->db;
		register_shutdown_function(function () use ($db, $finalizeLockName) {
			@$db->query('SELECT RELEASE_LOCK(?)', [$finalizeLockName]);
		});

		$checkoutUser = $this->checkoutPhoneUser($userID);
		if (!$checkoutUser || (int)$checkoutUser->user_phone_is_verified !== 1) {
			return json(['status' => 'error', 'message' => 'Ödemeye devam etmek için telefon numaranızı doğrulayın.'], 403);
		}

		if (!in_array($paymentMethod, ['wallet', 'transfer', 'credit', 'mobile'])) {
			return json(['status' => 'error', 'message' => 'Geçersiz ödeme yöntemi seçildi.']);
		}

		$getProduct = $this->db->get_where('products', ['product_id' => (int)$cartData['product_id'], 'product_status' => 1])->row();
		if (!$getProduct) {
			return json(['status' => 'error', 'message' => 'Sepetteki ürün artık satışta değil veya güncellendi.']);
		}
		$currentPrice = userPrice((int)$getProduct->product_id, $this->data['currentLocale']->locale_code, (int)($this->data['user']['user_tier'] ?? 1));
		if ((int)$getProduct->product_is_bundle !== 1 && (float)$currentPrice <= 0) {
			return json(['status' => 'error', 'message' => 'Bu ürün şu anda stokta bulunmuyor.']);
		}

		$cartPrice     = (float)$cartData['price'];
		$appliedCoupon = $this->session->userdata('applied_coupon');
		$discount      = !empty($appliedCoupon['discount']) ? (float)$appliedCoupon['discount'] : 0.0;

		$netTotal = $cartPrice - $discount;
		if ($netTotal < 0) {
			$netTotal = 0.0;
		}
		$post['_coupon_discount'] = $discount;
		$post['_coupon_id'] = !empty($appliedCoupon['coupon_id']) ? (int)$appliedCoupon['coupon_id'] : null;

		$submissionKey = sha1(implode('|', [
			(int)$userID,
			(int)($cartData['product_id'] ?? 0),
			(string)($cartData['user_id'] ?? ''),
			(string)($cartData['server_id'] ?? ''),
			(int)($appliedCoupon['coupon_id'] ?? 0),
			$paymentMethod,
		]));
		$submissionLock = $this->session->userdata('checkout_submission_lock');
		if (is_array($submissionLock)
			&& ($submissionLock['key'] ?? '') === $submissionKey
			&& (int)($submissionLock['created_at'] ?? 0) > time() - 120) {
			return json(['status' => 'error', 'message' => 'Bu sipariş için ödeme işlemi zaten başlatıldı.'], 409);
		}
		$this->session->set_userdata('checkout_submission_lock', [
			'key' => $submissionKey,
			'created_at' => time(),
		]);

		switch ($paymentMethod) {
			case 'wallet':
				return $this->payWallet($userID, $netTotal, $cartData, $post);
			case 'transfer':
				return $this->payEFT($userID, $netTotal, $cartData, $post);
			case 'credit':
				return $this->payCredit($userID, $post, $netTotal, $cartData);
			case 'mobile':
				return $this->payMobile($userID, $post, $netTotal, $cartData);
			default:
				return json(['status' => 'error', 'message' => 'Ödeme yöntemi işlenemedi.']);
		}
	}

	// A failing fraud check must never break the checkout response; fail closed to manual review.
	private function orderNeedsFraudReview($orderID)
	{
		$previousDbDebug = $this->db->db_debug;
		$this->db->db_debug = false;
		try {
			$this->load->model('FraudModel', 'fraudModel');
			$result = $this->fraudModel->fraudCheck((int)$orderID);
			$dbError = $this->db->error();
			if (!empty($dbError['code'])) {
				throw new RuntimeException('DB error: ' . json_encode($dbError, JSON_UNESCAPED_UNICODE));
			}
			return empty($result['status']);
		} catch (Throwable $e) {
			$this->db->insert('logs', [
				'log_controller' => 'payment.fraudCheck',
				'log_message' => json_encode(['order_id' => (int)$orderID, 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				'log_error_code' => 'fraud_check_failed',
				'log_order_id' => (int)$orderID,
			]);
			return true;
		} finally {
			$this->db->db_debug = $previousDbDebug;
		}
	}

	private function payWallet($userID, $netTotal, $cartData, $post)
	{
		$data = $this->data;
		$post['order_payment_type'] = 2; // Cüzdan ödemesi

		if ($netTotal <= 0) {
			return json([
				'status'  => 'error',
				'message' => 'Geçersiz ödeme tutarı.'
			]);
		}
		$order = $this->createOrder($userID, $cartData, $post);
		if (empty($order) || ($order['status'] ?? '') !== 'success') {
			return json(['status' => 'error', 'message' => $order['message'] ?? 'Sipariş oluşturulamadı. Lütfen tekrar deneyin.']);
		}

		$orderIDs = array_column($order['orders'], 'order_id');
		$fraudOrderIDs = [];
		foreach ($order['orders'] as $createdOrder) {
			if ($this->orderNeedsFraudReview((int)$createdOrder['order_id'])) {
				$fraudOrderIDs[] = (int)$createdOrder['order_id'];
			}
		}

		$walletCharge = round(array_sum(array_map(function ($createdOrder) {
			return (float)$createdOrder['wallet_price'];
		}, $order['orders'])), 2);
		$this->db->trans_start();
		$balanceRows = $this->db->query(
			'SELECT user_balance_amount, user_balance_status
			 FROM user_balance
			 WHERE user_balance_user_id = ? AND user_balance_type = 2
			 AND user_balance_status IN (1, 3)
			 FOR UPDATE',
			[(int)$userID]
		)->result();
		$spendableBalance = 0.0;
		foreach ($balanceRows as $balanceRow) {
			$amount = (float)$balanceRow->user_balance_amount;
			$spendableBalance += (int)$balanceRow->user_balance_status === 1 ? $amount : -$amount;
		}
		if ($spendableBalance + 0.001 < $walletCharge) {
			$this->db->trans_rollback();
			$this->db->where_in('order_id', $orderIDs)->update('orders', ['order_status' => 12]);
			return json([
				'status' => 'error',
				'message' => 'Cüzdan bakiyeniz bu işlem için yetersizdir. Gerekli Tutar: ' . nFormat($walletCharge) . ' TL, Mevcut Bakiyeniz: ' . nFormat($spendableBalance) . ' TL'
			]);
		}
		foreach ($order['orders'] as $createdOrder) {
			$this->db->insert('user_balance', [
				'user_balance_user_id' => (int)$userID,
				'user_balance_amount' => (float)$createdOrder['wallet_price'],
				'user_balance_order_id' => (int)$createdOrder['order_id'],
				'user_balance_status' => 3,
				'user_balance_type' => 2,
				'user_balance_created_at' => date('Y-m-d H:i:s'),
			]);
		}
		$approvedOrderIDs = array_values(array_diff($orderIDs, $fraudOrderIDs));
		if (!empty($approvedOrderIDs)) {
			$this->db->where_in('order_id', $approvedOrderIDs)->update('orders', ['order_status' => 0]);
		}
		if (!empty($fraudOrderIDs)) {
			$this->db->where_in('order_id', $fraudOrderIDs)->update('orders', ['order_status' => 13]);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === false) {
			$dbError = $this->db->error();
			log_message('error', 'Wallet payment transaction failed. User: ' . (int)$userID . ', Orders: ' . implode(',', $orderIDs) . ', DB error: ' . json_encode($dbError, JSON_UNESCAPED_UNICODE));
			$this->db->where_in('order_id', $orderIDs)->update('orders', ['order_status' => 2]);
			return json(['status' => 'error', 'message' => $this->lang->line('wallet_transaction_error') ?: 'Cüzdan işlemi oluşturulamadı.']);
		}

		foreach ($fraudOrderIDs as $fraudOrderID) {
			log_message('warning', 'Paid wallet order moved to fraud review. Order: ' . $fraudOrderID);
		}

		$this->session->unset_userdata('checkout_cart');
		$this->session->unset_userdata('applied_coupon');
		return json([
			'status'       => 'success',
			'message'      => 'Ödeme cüzdanınızdan tahsil edildi ve siparişiniz başarıyla alındı.',
			'redirect_url' => $this->resultUrl('order', (int)$orderIDs[0], $userID)
		]);
	}

	private function payEFT($userID, $netTotal, $cartData, $post)
	{
		$eftBankID  = !empty($post['eft_bank_id']) ? trim((string)$post['eft_bank_id']) : null;
		$eftRefCode = !empty($post['eft_ref_code']) ? trim((string)$post['eft_ref_code']) : null;

		if (empty($eftBankID)) {
			return json(['status' => 'error', 'message' => 'Lütfen havale yapacağınız bankayı seçin.']);
		}
		if (!ctype_digit($eftBankID)) {
			return json(['status' => 'error', 'message' => 'Seçilen banka geçersiz.']);
		}
		if ($eftRefCode !== null && !preg_match('/^[A-Za-z0-9]{4,16}$/', $eftRefCode)) {
			return json(['status' => 'error', 'message' => 'Havale referans kodu geçersiz, lütfen sayfayı yenileyip tekrar deneyin.']);
		}
		$bank = $this->db->select('bank_id')->where('bank_id', (int)$eftBankID)->where('bank_status', 1)->get('banks')->row();
		if (!$bank) {
			return json(['status' => 'error', 'message' => 'Seçilen banka kullanılamıyor.']);
		}
		if (empty($eftRefCode)) {
			return json(['status' => 'error', 'message' => 'Havale referans kodu eksik, lütfen sayfayı yenileyip tekrar deneyin.']);
		}

		$post['order_payment_type'] = 3;
		$order = $this->createOrder($userID, $cartData, $post);
		if (($order['status'] ?? '') !== 'success') {
			return json(['status' => 'error', 'message' => $order['message'] ?? 'Sipariş oluşturulamadı. Lütfen tekrar deneyin.']);
		}
		$this->session->unset_userdata('checkout_cart');
		$this->session->unset_userdata('applied_coupon');

		return json([
			'status'       => 'success',
			'message'      => 'Havale bildiriminiz alındı. Ödemeniz onaylandıktan sonra siparişiniz teslim edilecektir.',
			'redirect_url' => $this->resultUrl('order', (int)array_column($order['orders'], 'order_id')[0], $userID)
		]);
	}

	private function payCredit($userID, $post, $netTotal, $cartData)
	{
		$ccName   = !empty($post['cc_name']) ? trim($post['cc_name']) : null;
		$ccNumber = !empty($post['cc_number']) ? str_replace(' ', '', trim($post['cc_number'])) : null;
		$ccExpire = !empty($post['cc_expire']) ? trim($post['cc_expire']) : null;
		$ccCvv    = !empty($post['cc_cvv']) ? trim($post['cc_cvv']) : null;

		if (empty($ccName) || empty($ccNumber) || empty($ccExpire) || empty($ccCvv)) {
			return json(['status' => 'error', 'message' => 'Kredi kartı bilgileri eksik gönderildi.']);
		}
		$ccNumber = preg_replace('/\D+/', '', $ccNumber);
		if (strlen($ccNumber) < 12 || strlen($ccNumber) > 19 || !preg_match('/^\d{2}\/\d{2}$/', $ccExpire) || !preg_match('/^\d{3,4}$/', $ccCvv)) {
			return json(['status' => 'error', 'message' => 'Kredi kartı bilgileri geçersiz.']);
		}

		// $ccExpire format 11 / 11 we need clean
		$ccExpireParts = explode('/', $post['cc_expire']);
		$ccExpireMonth = isset($ccExpireParts[0]) ? trim($ccExpireParts[0]) : null;
		$ccExpireYear = isset($ccExpireParts[1]) ? trim($ccExpireParts[1]) : null;
		if ((int)$ccExpireMonth < 1 || (int)$ccExpireMonth > 12) {
			return json(['status' => 'error', 'message' => 'Kart son kullanma tarihi geçersiz.']);
		}

		$totalAmount = round($netTotal * (1 + (self::CREDIT_CARD_COMMISSION_RATE / 100)), 2);

		$cardInfo = [
			'cc_name'   => $ccName,
			'cc_number' => $ccNumber,
			'cc_expiry' => $ccExpireMonth . '/' . $ccExpireYear,
			'cc_cvv'    => $ccCvv
		];

		$randomString = generateRandomString(12, 'AZ09');
		$post['randomString'] = $randomString;
		$post['order_payment_type'] = 4;
		$order = $this->createOrder($userID, $cartData, $post);

		if (empty($order) || !isset($order['status']) || $order['status'] !== 'success') {
			return json(['status' => 'error', 'message' => $order['message'] ?? 'Sipariş oluşturulamadı. Lütfen tekrar deneyin.']);
		}
		$totalAmount = round((float)$order['total_wallet_price'] * (1 + (self::CREDIT_CARD_COMMISSION_RATE / 100)), 2);

		$orderID = $order['order_id'] ?? null;
		$paymentReference = 'order_' . $randomString . '_' . $orderID;

		$orderIDs = array_column($order['orders'], 'order_id');
		$cardPaymentInfo = json_encode($this->safeCardInfo($cardInfo), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$this->db->where_in('order_id', $orderIDs)->update('orders', [
			'order_payment_info' => $cardPaymentInfo,
		]);
		$response = $this->dijigate3dInit($paymentReference, $totalAmount, $cardInfo, 'order', $order['orders'], $this->resultUrl('order', (int)$orderIDs[0], $userID));
		$providerPaymentID = isset($response['paymentId']) ? (int)$response['paymentId'] : 0;
		if ($providerPaymentID <= 0 || empty($response['htmlContent'])) {
			$this->db->where_in('order_id', $orderIDs)->update('orders', ['order_status' => 12]);
			return json([
				'status' => 'error',
				'message' => $response['message'] ?? $response['errorMessage'] ?? '3D Secure ödeme başlatılamadı.'
			]);
		}

		$this->db->where_in('order_id', $orderIDs)->update('orders', [
			'order_payment_token' => (string)$providerPaymentID,
			'order_reseller_order_id' => $paymentReference,
		]);
		$this->session->unset_userdata('checkout_cart');
		$this->session->unset_userdata('applied_coupon');

		return json([
			'status' => '3d_redirect',
			'html_content' => $response['htmlContent']
		]);
	}

	private function payMobile($userID, $post, $netTotal, $cartData)
	{
		$phoneNumber = $this->verifiedMobilePhone($userID);
		if ($phoneNumber === '') {
			return json(['status' => 'error', 'message' => 'Mobil ödeme için hesabınızda onaylı bir telefon numarası gereklidir.']);
		}

		$randomString = generateRandomString(12, 'AZ09');
		$post['randomString'] = $randomString;
		$post['order_payment_type'] = 5;
		$order = $this->createOrder($userID, $cartData, $post);

		if (empty($order) || !isset($order['status']) || $order['status'] !== 'success') {
			return json(['status' => 'error', 'message' => $order['message'] ?? 'Sipariş oluşturulamadı. Lütfen tekrar deneyin.']);
		}

		$totalAmount = round((float)$order['total_wallet_price'] * 1.60, 2);
		$orderID = $order['order_id'] ?? null;
		$paymentReference = 'M' . strtoupper(bin2hex(random_bytes(9)));
		$orderIDs = array_column($order['orders'], 'order_id');
		$response = $this->dijigateMobileInit($paymentReference, $totalAmount, [
			'phoneNumber' => $phoneNumber,
			'price' => $totalAmount,
			'items' => array_map(function ($item) {
				return ['name' => 'Dijital Ürün Siparişi #' . ($item['order_id'] ?? 0), 'price' => round((float)($item['wallet_price'] ?? 0) * 1.60, 2)];
			}, $order['orders']),
		], 'order', $order['orders'], $this->resultUrl('order', (int)$orderIDs[0], $userID));
		$providerPaymentID = isset($response['paymentId']) ? (int)$response['paymentId'] : 0;
		$providerRedirectUrl = trim((string)($response['redirectUrl'] ?? ''));
		if ($providerPaymentID <= 0 || (empty($providerRedirectUrl) && empty($response['htmlContent']))) {
			$this->db->where_in('order_id', $orderIDs)->update('orders', ['order_status' => 12]);
			return json([
				'status' => 'error',
				'message' => $response['message'] ?? $response['errorMessage'] ?? 'Mobil ödeme başlatılamadı.'
			]);
		}

		$this->db->where_in('order_id', $orderIDs)->update('orders', [
			'order_payment_token' => (string)$providerPaymentID,
			'order_reseller_order_id' => $paymentReference,
		]);
		$this->session->unset_userdata('checkout_cart');
		$this->session->unset_userdata('applied_coupon');

		return json([
			'status' => '3d_redirect',
			'html_content' => $response['htmlContent'] ?? '',
			'redirect_url' => $providerRedirectUrl,
			'payment_id' => $providerPaymentID,
		]);
	}

	public function webhook()
	{
		$webhookKey = getenv('CRAFTGATE_WEBHOOK_KEY');
		if (empty($webhookKey)) {
			log_message('error', 'Craftgate webhook secret is not configured.');
			show_error('Webhook yapılandırması eksik', 503);
			return;
		}
		// 1. Ham JSON içeriğini al
		$payload = file_get_contents('php://input');
		$data = json_decode($payload, true);
		// 2. Signature header'ını al
		$headers = getallheaders();
		$signatureHeader = isset($headers['x-cg-signature-v1']) ? $headers['x-cg-signature-v1'] : null;

		if (!$signatureHeader || !$data) {
			log_message('error', 'Eksik veri veya header');
			show_error('Eksik veri', 400);
			return;
		}
		// 3. İmza oluşturulacak alanları sırayla birleştir
		$stringToSign = $data['eventType'] . $data['eventTimestamp'] . $data['status'] . $data['payloadId'];
		// 4. HMAC-SHA256 ile hashle ve Base64 ile encode et
		$calculatedSignature = base64_encode(hash_hmac('sha256', $stringToSign, $webhookKey, true));
		// 5. Doğrulama
		if (!hash_equals($signatureHeader, $calculatedSignature)) {
			log_message('error', 'Webhook imza doğrulaması başarısız!');
			show_error('Geçersiz imza', 403);
			$logArray = [
				'log_controller' => 'payment',
				'log_message' => 'Craftgate webhook signature rejected',
				'log_error_code' => '98761',
			];
			$this->db->insert('logs', $logArray);
			return;
		}
		// ✅ Geçerli istek
		log_message('info', 'Craftgate webhook doğrulandı. Event: ' . $data['eventType']);
		// Gerekli işlemleri burada yapabilirsiniz (siparişi işaretle, veritabanına yaz, vs.)
		http_response_code(200); // Craftgate "200 OK" bekler
		$logArray2 = [
			'log_controller' => 'payment',
			'log_message' => json_encode(['eventType' => $data['eventType'] ?? null, 'payloadId' => $data['payloadId'] ?? null]),
			'log_error_code' => '98762',
		];
		$this->db->insert('logs', $logArray2);
		// şimdi siparişi kontrol edelim
		$this->load->library('craftgate');
		$craftgate = new Craftgate();
		$token = $data['payloadId'] ?? '';
		if (empty($token)) {
			log_message('error', 'Craftgate webhook payloadId eksik!');
			show_error('Eksik payloadId', 400);
			return;
		}
		$response = $craftgate->orderCheck($token);
		$logArray3 = [
			'log_controller' => 'payment',
			'log_message' => json_encode($response),
			'log_error_code' => '98763',
		];
		$this->db->insert('logs', $logArray3);
		if (!empty($response->data->paymentStatus) && $response->data->paymentStatus == 'SUCCESS') {
			$status = 'Başarılı';
			$data = $response->data ?? [];
			$conversationID = explode('-', $data->conversationId); // order_id
			$orderID = end($conversationID);
			$paymentInfo = [
				'cardHolderName' => $data->cardHolderName ?? '',
				'cardBrand' => $data->cardBrand ?? '',
				'cardType' => $data->cardType ?? '',
				'binNumber' => $data->binNumber ?? '',
				'lastFourDigits' => $data->lastFourDigits ?? '',
				'pos'  => $data->pos ?? [],
				'isThreeDS' => $data->isThreeDS ?? false,
				'paymentID' => $data->id ?? '',
			];
			$paymentInfoJson = json_encode($paymentInfo); // order_payment_info
			$paymentPrice = $data->price ?? 0; //order_sale_price
			$paymentCurrency = $data->currency ?? 'TRY'; // order_sale_price_currency
			//$tryPrice = currencyCalculatorTo('TRY', $paymentPrice);
			$this->db->where('order_id', $orderID);
			$this->db->update('orders', [
				'order_status' => 0,
				'order_payment_info' => $paymentInfoJson,
				'order_sale_price' => $paymentPrice,
				'order_sale_price_currency' => $paymentCurrency,
			]);
		}
		if (!empty($response->data->paymentStatus) && $response->data->paymentStatus == 'FAILURE') {
			$status = 'Başarısız';
			$this->db->where('order_id', $orderID);
			$this->db->update('orders', ['order_status' => 12]);
		}
		echo $orderID . ' numaralı Sipariş ' . $status . ' olarak işleme tamamlandı.';
	}

	public function dijigate3dInit($paymentReference, $totalAmount, $cardInfo = [], $type = 'order', $orderItems = [], $redirectUrl = null)
	{
		$ccExplode = explode('/', $cardInfo['cc_expiry'] ?? '');
		$cardInfo['cc_month'] = $ccExplode[0] ?? null;
		$cardInfo['cc_year'] = $ccExplode[1] ?? null;

		$magicPay = new Magicpay();
		$paymentData = $this->buildDijigatePaymentData($paymentReference, $totalAmount, $cardInfo, $type, $orderItems, $redirectUrl);
		$response = $magicPay->init3dPayment($paymentData);
		return $response;
	}

	public function dijigateMobileInit($paymentReference, $totalAmount, $mobileInfo = [], $type = 'order', $orderItems = [], $redirectUrl = null)
	{
		$magicPay = new Magicpay();
		$paymentData = $this->buildDijigateMobilePaymentData($paymentReference, $totalAmount, $mobileInfo, $type, $orderItems, $redirectUrl);
		$response = $magicPay->initMobilePayment($paymentData);
		return $response;
	}

	private function buildDijigatePaymentData($paymentReference, $totalAmount, $cardInfo, $type = 'order', $orderItems = [], $redirectUrl = null)
	{
		$items = [];
		foreach ($orderItems as $orderItem) {
			$orderID = (int)($orderItem['order_id'] ?? 0);
			$items[] = [
				'id' => 'ORDER-' . (int)$orderID,
				'name' => 'Dijital Ürün Siparişi #' . (int)$orderID,
				'category' => 'Dijital Ürün',
				'price' => (float)($orderItem['wallet_price'] ?? 0),
			];
		}
		if (empty($items)) {
			$items[] = [
				'name' => 'Dijital Ürün Siparişi',
				'category' => 'Dijital Ürün',
				'price' => $totalAmount,
			];
		}

		$paymentData = [
			'conversationId'   => $paymentReference,
			'externalId'       => $paymentReference,
			'price'            => $totalAmount,
			'paidPrice'        => $totalAmount,
			'installment'      => 1,
			'currency'         => 'TRY',
			'bankOrderId'      => $paymentReference,
			'redirectUrl'      => $redirectUrl ?: topupcenter() . 'profile/orders',
			'callbackUrl'      => topupcenter() . 'callback/magicpayCallback',
			'paymentGroup'     => 'PRODUCT',
			'paymentChannel'   => 'WEB',
			'clientIp'         => getIp(),
			'additionalParams' => 'optional_metadata',
			'items'            => $items,
			'card' => [
				'cardAlias'      => 'New Card' ?? null,
				'cardHolderName' => $cardInfo['cc_name'] ?? null,
				'cardNumber'     => $cardInfo['cc_number'] ?? null,
				'expireYear'     => $cardInfo['cc_year'] ?? null,
				'expireMonth'    => $cardInfo['cc_month'] ?? null,
				'cvc'            => $cardInfo['cc_cvv'] ?? null,
			]
		];
		//pre($paymentData);die;

		return $paymentData;
	}

	private function buildDijigateMobilePaymentData($paymentReference, $totalAmount, $mobileInfo = [], $type = 'order', $orderItems = [], $redirectUrl = null)
	{
		$priceAmount = round((float)$totalAmount, 2);
		$items = [];
		if (!empty($mobileInfo['items']) && is_array($mobileInfo['items'])) {
			$items = $mobileInfo['items'];
		} elseif (!empty($orderItems)) {
			foreach ($orderItems as $orderItem) {
				$orderID = (int)($orderItem['order_id'] ?? 0);
				$items[] = [
					'name' => 'Dijital Ürün Siparişi #' . $orderID,
					'price' => round((float)($orderItem['wallet_price'] ?? 0) * 1.60, 2),
				];
			}
		} else {
			$items[] = ['name' => 'Bakiye Yükleme', 'price' => $priceAmount];
		}

		$phoneNumber = $this->normalizeMobilePhoneNumber((string)($mobileInfo['phoneNumber'] ?? ($mobileInfo['phone_number'] ?? '')));
		$payload = [
			'conversationId' => $paymentReference,
			'externalId' => $paymentReference,
			'price' => $priceAmount,
			'paidPrice' => (float)$totalAmount,
			'walletPrice' => 0.0,
			'installment' => 1,
			'callbackUrl' => topupcenter() . 'callback/magicpayCallback',
			'currency' => 'TRY',
			'paymentGroup' => 'PRODUCT',
			'paymentPhase' => 'AUTH',
			'paymentChannel' => 'MOBILE',
			'bankOrderId' => $paymentReference,
			'clientIp' => getIp(),
			'items' => $items,
			'redirectUrl' => $redirectUrl ?: topupcenter() . 'profile/orders',
			'description' => 'Mobile payment',
		];

		if (!empty($phoneNumber)) {
			$payload['phoneNumber'] = normalizePhoneE164($phoneNumber);
		}
		return $payload;
	}

	public function testMagicpay()
	{
		$post = $this->input->post();
		if (empty($post)) {
			// kk formu aç
			echo '<form method="post">
				Tutar: <input type="text" name="Amount" placeholder="Amount" value="1.00" required><br>
				Kart Sahibi: <input type="text" name="cc_name" placeholder="Card Holder Name" value="İsim Soyisim" required><br>
				Kart Numarası: <input type="text" name="cc_number" placeholder="Card Number" value="Kart Numarası" required><br>
				Son Kullanma Tarihi: <input type="text" name="cc_expiry" placeholder="MM/YY" value="00/00" required><br>
				CVV: <input type="text" name="cc_cvv" placeholder="CVV" value="000" required><br>
				<button type="submit">Pay</button>
			</form>';
			return;
		}
		$orderID = md5(time() . rand(1000, 9999));
		$totalAmount = 1;
		$cardInfo = [
			'cc_name' => $this->input->post('cc_name'),
			'cc_number' => $this->input->post('cc_number'),
			'cc_expiry' => $this->input->post('cc_expiry'),
			'cc_cvv' => $this->input->post('cc_cvv')
		];
		$response = $this->dijigate3dInit($orderID, $totalAmount, $cardInfo, $type = 'order');
		$logArray = [
			'log_controller' => 'magicpayResponse',
			'log_message' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'log_error_code' => '987644875',
		];
		$this->db->insert('logs', $logArray);

		if (in_array($response['paymentStatus'], ['WAITING', 'OTP_WAITING'])) {
			if (!empty($response['htmlContent'])) {
				$paymentID = $response['paymentId'] ?? null;
				echo $response['htmlContent']; // 3D Secure formu
			}
		} else {
			return [
				'status'  => 'error',
				'message' => $response['errorMessage'] ?? 'Ödeme başlatılamadı.'
			];
		}
	}

	public function addBalance()
	{
		$user = $this->session->userdata('user');
		$restriction = customerRestriction((int)($user['user_id'] ?? 0), 'topup');
		if ($restriction) {
			return json(['status' => 'error', 'message' => $restriction->restriction_customer_reason], 403);
		}
		$data = $this->data;
		$user = $this->data['user'];
		$post = $this->input->post();

		if (empty($user['user_id']) || empty($post) || !isset($post['method'])) {
			return json(['status' => 'error', 'message' => 'Geçersiz istek parametreleri.']);
		}

		$method = strtolower(trim((string)$post['method']));
		$methodAliases = [
			'card' => 'cc',
			'credit' => 'cc',
			'transfer' => 'eft',
		];
		$method = $methodAliases[$method] ?? $method;
		$post['method'] = $method;
		if ($method === 'cc') {
			$post['cc_number'] = preg_replace('/\D+/', '', (string)($post['cc_number'] ?? ''));
			$post['cc_expiry'] = preg_replace('/\s+/', '', trim((string)($post['cc_expiry'] ?? '')));
		}

		// --- 1. FORM VALIDATION KURALLARI ---
		$this->load->library('form_validation');
		$this->form_validation->set_data($post);

		// Ortak Kurallar
		$this->form_validation->set_rules('amount', 'Tutar', 'required|trim|numeric|greater_than[0]');
		$this->form_validation->set_rules('method', 'Ödeme Yöntemi', 'required|trim|in_list[cc,eft,mobile]');

		if ($method === 'eft') {
			$this->form_validation->set_rules('bank_id', 'Banka', 'required|trim|integer');
		} elseif ($method === 'cc') {
			$this->form_validation->set_rules('cc_name', 'Kart Sahibi', 'required|trim|min_length[5]');
			$this->form_validation->set_rules('cc_number', 'Kart Numarası', 'required|trim|exact_length[16]|numeric');
			$this->form_validation->set_rules('cc_expiry', 'Son Kullanma Tarihi', 'required|trim|exact_length[5]');
			$this->form_validation->set_rules('cc_cvv', 'CVV', 'required|trim|numeric|min_length[3]|max_length[4]');
		} elseif ($method === 'mobile') {
			$this->form_validation->set_rules('phone_number', 'Telefon Numarası', 'required|trim');
		}

		if ($this->form_validation->run() == FALSE) {
			// İlk hatayı çekip temiz mesaj basıyoruz
			$errors = $this->form_validation->error_array();
			$first_error = reset($errors);
			return json(['status' => 'error', 'message' => $first_error]);
		}

		// Tutar Formatlama (Stabil decimal dönüşüm)
		$clean_amount = number_format((float) $post['amount'], 2, '.', '');

		// --- 2. AKIŞ YÖNETİMİ ---
		if ($method === 'eft') {
			$bank = $this->db->select('bank_id')->where('bank_id', (int)$post['bank_id'])->where('bank_status', 1)->get('banks')->row();
			if (!$bank) {
				return json(['status' => 'error', 'message' => 'Seçilen banka kullanılamıyor.']);
			}

			// --- BANKA HAVALESİ / EFT AKIŞI ---
			$referenceNumber = ensure_user_bank_reference((int)$user['user_id']);
			if ($referenceNumber === '') {
				return json(['status' => 'error', 'message' => 'Hesabınıza ait referans numarası bulunamadı.']);
			}

			$userFullName = trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''));
			$insertArray = [
				'user_balance_bank_id'      => (int) $post['bank_id'],
				'user_balance_user_id'      => (int) $user['user_id'],
				'user_balance_user_name'    => $userFullName !== '' ? $userFullName : ($user['user_name'] ?? ''),
				'user_balance_amount'       => $clean_amount,
				'user_balance_type'         => 2, // Harcanabilir bakiye ekleme (1: Çekilebilir, 2: Harcanabilr)
				'user_balance_reference_no' => $referenceNumber,
				'user_balance_payment_date' => date('Y-m-d H:i:s'), // Şu anki zamanı otomatik basıyoruz
				'user_balance_status'       => 0, // 0: Beklemede
			];
			if ($this->db->field_exists('user_balance_request_ip', 'user_balance')) {
				$insertArray['user_balance_request_ip'] = getIp();
			}
			if ($this->db->field_exists('user_balance_request_port', 'user_balance')) {
				$insertArray['user_balance_request_port'] = getClientPort();
			}

			$this->db->insert('user_balance', $insertArray);

			if ($this->db->affected_rows() > 0) {
				$balanceID = (int)$this->db->insert_id();
				return json([
					'status'       => 'success',
					'message'      => $this->lang->line('payment_notification_success') ?? 'Havale bildiriminiz başarıyla alındı, kontrol edildikten sonra onaylanacaktır.',
					'redirect_url' => $this->resultUrl('balance', $balanceID, (int)$user['user_id'])
				]);
			} else {
				return json([
					'status'  => 'error',
					'message' => $this->lang->line('payment_notification_error') ?? 'Havale bildirimi kaydedilirken bir hata oluştu.'
				]);
			}
		} elseif ($method === 'cc') {

			// --- KREDİ KARTI / 3D SECURE AKIŞI ---
			try {
				$commission_rate = (float) ($post['commission_rate'] ?? 2.7);
				$commission_amount = number_format(($clean_amount * $commission_rate) / 100, 2, '.', '');
				$total_amount = number_format($clean_amount + $commission_amount, 2, '.', '');
				$paymentReference = 'wallet_cc_' . (int)$user['user_id'] . '_' . time() . '_' . bin2hex(random_bytes(4));
				$userFullName = trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''));

				$pendingBalance = [
					'user_balance_user_id' => (int)$user['user_id'],
					'user_balance_user_name' => $userFullName !== '' ? $userFullName : ($user['user_name'] ?? ''),
					'user_balance_amount' => (float)$clean_amount,
					'user_balance_type' => 2,
					'user_balance_reference_no' => $paymentReference,
					'user_balance_payment_date' => date('Y-m-d H:i:s'),
					'user_balance_status' => 0,
					'user_balance_note' => 'Kredi kartı ile bakiye yükleme',
					'user_balance_created_at' => date('Y-m-d H:i:s'),
				];
					if ($this->db->field_exists('user_balance_request_ip', 'user_balance')) {
						$pendingBalance['user_balance_request_ip'] = getIp();
					}
					if ($this->db->field_exists('user_balance_request_port', 'user_balance')) {
						$pendingBalance['user_balance_request_port'] = getClientPort();
					}

				$this->db->insert('user_balance', $pendingBalance);
				if ($this->db->affected_rows() < 1) {
					return json(['status' => 'error', 'message' => 'Bakiye yükleme kaydı oluşturulamadı.']);
				}
				$balanceID = (int)$this->db->insert_id();

				$cardInfo = [
					'cc_name' => trim($post['cc_name']),
					'cc_number' => str_replace(' ', '', trim($post['cc_number'])),
					'cc_expiry' => trim($post['cc_expiry']),
					'cc_cvv' => trim($post['cc_cvv']),
				];
				$pendingBalance['user_balance_note'] = json_encode([
					'message' => 'Kredi kartı ile bakiye yükleme',
					'card' => $this->safeCardInfo($cardInfo),
				], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				$this->db->where('user_balance_id', $balanceID)->update('user_balance', [
					'user_balance_note' => $pendingBalance['user_balance_note'],
				]);

				$paymentResponse = $this->dijigate3dInit($paymentReference, (float)$total_amount, $cardInfo, 'wallet', [], $this->resultUrl('balance', $balanceID, (int)$user['user_id']));
				$providerPaymentID = isset($paymentResponse['paymentId']) ? (int)$paymentResponse['paymentId'] : 0;
				$html_content = $paymentResponse['htmlContent'] ?? '';

				if ($providerPaymentID <= 0 || empty($html_content)) {
					$this->db->where('user_balance_reference_no', $paymentReference)->update('user_balance', ['user_balance_status' => 2]);
					return json([
						'status' => 'error',
						'message' => $paymentResponse['message'] ?? $paymentResponse['errorMessage'] ?? '3D Secure ödeme başlatılamadı.'
					]);
				}

				$this->db->where('user_balance_reference_no', $paymentReference)->update('user_balance', [
					'user_balance_note' => json_encode([
						'message' => 'Kredi kartı ile bakiye yükleme - ödeme başlatıldı',
						'provider_payment_id' => (string)$providerPaymentID,
						'provider_reference' => $paymentReference,
						'provider_payload_id' => (string)($paymentResponse['payloadId'] ?? ''),
						'provider_order_id' => (string)($paymentResponse['orderId'] ?? ''),
						'provider_external_id' => (string)($paymentResponse['externalId'] ?? ''),
						'provider_conversation_id' => (string)($paymentResponse['conversationId'] ?? ''),
						'payment_method' => 'cc',
						'card' => $this->safeCardInfo($cardInfo),
					], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				]);

				return json([
					'status'       => '3d_redirect',
					'html_content' => $html_content
				]);
			} catch (Exception $e) {
				return json([
					'status'  => 'error',
					'message' => 'Ödeme sistemi başlatılırken bir hata oluştu: ' . $e->getMessage()
				]);
			}
		} elseif ($method === 'mobile') {
			try {
				$phoneNumber = $this->verifiedMobilePhone((int)$user['user_id']);
				if ($phoneNumber === '') {
					return json(['status' => 'error', 'message' => 'Mobil ödeme için hesabınızda onaylı bir telefon numarası gereklidir.']);
				}

				$paymentReference = 'M' . strtoupper(bin2hex(random_bytes(9)));
				$userFullName = trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''));
				$pendingBalance = [
					'user_balance_user_id' => (int)$user['user_id'],
					'user_balance_user_name' => $userFullName !== '' ? $userFullName : ($user['user_name'] ?? ''),
					'user_balance_amount' => (float)$clean_amount,
					'user_balance_type' => 2,
					'user_balance_reference_no' => $paymentReference,
					'user_balance_payment_date' => date('Y-m-d H:i:s'),
					'user_balance_status' => 0,
					'user_balance_note' => 'Mobil ödeme ile bakiye yükleme',
					'user_balance_created_at' => date('Y-m-d H:i:s'),
				];
				if ($this->db->field_exists('user_balance_request_ip', 'user_balance')) {
					$pendingBalance['user_balance_request_ip'] = getIp();
				}
				if ($this->db->field_exists('user_balance_request_port', 'user_balance')) {
					$pendingBalance['user_balance_request_port'] = getClientPort();
				}

				$this->db->insert('user_balance', $pendingBalance);
				if ($this->db->affected_rows() < 1) {
					return json(['status' => 'error', 'message' => 'Mobil bakiye yükleme kaydı oluşturulamadı.']);
				}
				$balanceID = (int)$this->db->insert_id();

				$mobileInfo = [
					'phoneNumber' => $phoneNumber,
					'price' => round((float)$clean_amount * 1.60, 2),
					'items' => [[
						'name' => 'Mobil bakiye yükleme',
						'price' => round((float)$clean_amount * 1.60, 2),
					]],
				];


				$mobileGatewayAmount = round((float)$clean_amount * 1.60, 2);
				$paymentResponse = $this->dijigateMobileInit($paymentReference, $mobileGatewayAmount, $mobileInfo, 'wallet', [], $this->resultUrl('balance', $balanceID, (int)$user['user_id']));
				$providerPaymentID = isset($paymentResponse['paymentId']) ? (int)$paymentResponse['paymentId'] : 0;
				$html_content = $paymentResponse['htmlContent'] ?? '';
				$providerRedirectUrl = trim((string)($paymentResponse['redirectUrl'] ?? ''));
				if ($providerPaymentID <= 0 || (empty($providerRedirectUrl) && empty($html_content))) {
					$this->db->where('user_balance_reference_no', $paymentReference)->update('user_balance', ['user_balance_status' => 2]);
					return json([
						'status' => 'error',
						'message' => $paymentResponse['message'] ?? $paymentResponse['errorMessage'] ?? 'Mobil ödeme başlatılamadı.'
					]);
				}

				$this->db->where('user_balance_reference_no', $paymentReference)->update('user_balance', [
					'user_balance_note' => json_encode([
						'message' => 'Mobil ödeme ile bakiye yükleme - ödeme başlatıldı',
						'provider_payment_id' => (string)$providerPaymentID,
						'provider_reference' => $paymentReference,
						'provider_payload_id' => (string)($paymentResponse['payloadId'] ?? ''),
						'provider_order_id' => (string)($paymentResponse['orderId'] ?? ''),
						'provider_external_id' => (string)($paymentResponse['externalId'] ?? ''),
						'provider_conversation_id' => (string)($paymentResponse['conversationId'] ?? ''),
						'payment_method' => 'mobile',
					], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				]);

				return json([
					'status' => '3d_redirect',
					'html_content' => $html_content,
					'redirect_url' => $providerRedirectUrl,
					'payment_id' => $providerPaymentID,
				]);
			} catch (Exception $e) {
				return json([
					'status' => 'error',
					'message' => 'Mobil ödeme sistemi başlatılırken bir hata oluştu: ' . $e->getMessage(),
				]);
			}
		}

		return json(['status' => 'error', 'message' => 'Bilinmeyen ödeme yöntemi.']);
	}

	private function normalizeMobilePhoneNumber(?string $phoneNumber): string
	{
		if ($phoneNumber === null) {
			return '';
		}

		$digits = preg_replace('/\D+/', '', trim((string)$phoneNumber));
		if ($digits === '') {
			return '';
		}

		if (str_starts_with($digits, '90')) {
			return $digits;
		}

		if (str_starts_with($digits, '0')) {
			return '9' . substr($digits, 1);
		}

		if (strlen($digits) === 10 && str_starts_with($digits, '5')) {
			return '9' . $digits;
		}

		if (strlen($digits) === 11 && str_starts_with($digits, '5')) {
			return '9' . substr($digits, 1);
		}

		return $digits;
	}

	private function isValidMobilePhoneNumber(string $phoneNumber): bool
	{
		return (bool)preg_match('/^905\d{9}$/', $phoneNumber);
	}

	public function createOrder($userID, $cartData, $post)
	{
		$data = $this->data;
		$appliedCoupon = $this->session->userdata('applied_coupon') ?: [];
		$couponID = isset($post['_coupon_id']) ? (int)$post['_coupon_id'] : (!empty($appliedCoupon['coupon_id']) ? (int)$appliedCoupon['coupon_id'] : null);

		$pid = (int)$cartData['product_id'];
		$parentProduct = $this->db->select('product_is_bundle, product_bundle_items')
			->where('product_id', $pid)->where('product_status', 1)->get('products')->row();
		if (!$parentProduct) {
			return ['status' => 'error', 'message' => 'Ürün bulunamadı veya satıştan kaldırıldı.'];
		}

		$productIDs = [$pid];
		if ((int)$parentProduct->product_is_bundle === 1) {
			$bundleItems = json_decode($parentProduct->product_bundle_items ?: '[]', true);
			$productIDs = array_values(array_filter(array_map('intval', is_array($bundleItems) ? $bundleItems : [])));
			if (empty($productIDs)) {
				return ['status' => 'error', 'message' => 'Paket üründe geçerli alt ürün bulunamadı.'];
			}
		}

		$originalPrices = [];
		$totalListPrice = 0.0;
		foreach ($productIDs as $productID) {
			$product = $this->db->select('product_id')->where('product_id', $productID)->where('product_status', 1)->get('products')->row();
			if (!$product) {
				return ['status' => 'error', 'message' => 'Paket alt ürünlerinden biri satışta değil.'];
			}
			$price = (float)userPrice($productID, $data['currentLocale']->locale_code, $data['user']['user_tier']);
			if ($price <= 0) {
				return ['status' => 'error', 'message' => 'Paket alt ürünlerinden biri şu anda stokta bulunmuyor.'];
			}
			$originalPrices[$productID] = $price;
			$totalListPrice += $price;
		}

		// Bundle dışı ürünlerde her adet için ayrı sipariş satırı oluşturulur (her biri kendi teslimatını alır).
		$orderQty = ((int)$parentProduct->product_is_bundle === 1) ? 1 : max(1, min(10, (int)($cartData['qty'] ?? 1)));
		if ($orderQty > 1) {
			$productIDs = array_fill(0, $orderQty, $pid);
			$totalListPrice *= $orderQty;
		}

		$couponDiscount = max(0.0, (float)(isset($post['_coupon_discount']) ? $post['_coupon_discount'] : ($appliedCoupon['discount'] ?? 0)));
		$bundleSalePrice = isset($cartData['price']) ? (float)$cartData['price'] : $totalListPrice;
		$couponDiscount = min($couponDiscount, $bundleSalePrice);
		$bundlePriceAdjustment = $totalListPrice - $bundleSalePrice;
		$totalAdjustment = $bundlePriceAdjustment + $couponDiscount;
		$netSalePrice = max(0.0, $totalListPrice - $totalAdjustment);
		$uid = !empty($cartData['user_id']) ? trim($cartData['user_id']) : null;
		$sid = !empty($cartData['server_id']) ? trim($cartData['server_id']) : null;
		$orderRequireData = [];
		if (!empty($uid)) {
			$orderRequireData['user_id'] = trim($uid);
		}
		if (!empty($sid)) {
			$orderRequireData['server_id'] = trim($sid);
		}
		$this->db->trans_start();
		$orders = [];
		$allocatedSalePrice = 0.0;
		$allocatedBundleSalePrice = 0.0;
		$allocatedCouponDiscount = 0.0;
		$bundleReference = (int)$parentProduct->product_is_bundle === 1
			? 'bundle_' . date('YmdHis') . '_' . bin2hex(random_bytes(6))
			: null;
		$pricingSnapshot = [
			'is_bundle' => $bundleReference !== null,
			'bundle_reference' => $bundleReference,
			'bundle_product_id' => $bundleReference !== null ? $pid : null,
			'bundle_product_name' => $bundleReference !== null ? (string)$cartData['product_name'] : null,
			'bundle_part_count' => count($productIDs),
			'bundle_total_price' => round($netSalePrice, 2),
		];
		// Sipariş anındaki fatura adresini snapshot olarak al
		$billingAddress = $this->db->where([
			'user_addresses_user_id' => $userID,
			'user_addresses_is_default' => 1,
			'user_addresses_is_deleted' => 0
		])->get('user_addresses')->row();

		if (!$billingAddress) {
			$billingAddress = $this->db->where([
				'user_addresses_user_id' => $userID,
				'user_addresses_is_deleted' => 0
			])->order_by('user_addresses_id', 'DESC')->get('user_addresses')->row();
		}

		if ($billingAddress) {
			$billingSnapshot = [
				'address_id'            => (int)$billingAddress->user_addresses_id,
				'title'                 => $billingAddress->user_addresses_title,
				'invoice_type'          => (int)$billingAddress->user_addresses_invoice_type,
				'is_turkish_citizen'    => (int)$billingAddress->user_addresses_is_turkish_citizen,
				'is_turkish_company'    => (int)$billingAddress->user_addresses_is_turkish_company,
				'full_name'             => $billingAddress->user_addresses_full_name,
				'company_name'          => $billingAddress->user_addresses_company_name,
				'tax_office'            => $billingAddress->user_addresses_tax_office,
				'tax_number'            => $billingAddress->user_addresses_tax_number,
				'is_e_invoice_taxpayer' => (int)$billingAddress->user_addresses_is_e_invoice_taxpayer,
				'country'               => $billingAddress->user_addresses_country,
				'city'                  => $billingAddress->user_addresses_city,
				'district'              => $billingAddress->user_addresses_district,
				'postal_code'           => $billingAddress->user_addresses_postal_code,
				'address'               => $billingAddress->user_addresses_address,
				'snapshot_at'           => date('Y-m-d H:i:s'),
			];
		} else {
			$userRow = $this->db->get_where('users', ['user_id' => $userID])->row();
			$billingSnapshot = [
				'address_id'            => 0,
				'title'                 => 'Fatura Adresi',
				'invoice_type'          => !empty($userRow->user_bill_tax_office) ? 2 : 1,
				'is_turkish_citizen'    => 1,
				'is_turkish_company'    => (int)($userRow->user_is_turkish_company ?? 1),
				'full_name'             => trim(($userRow->user_name ?? '') . ' ' . ($userRow->user_surname ?? '')),
				'company_name'          => $userRow->user_bill_company_name ?? '',
				'tax_office'            => $userRow->user_bill_tax_office ?? '',
				'tax_number'            => $userRow->user_bill_tax_number ?? '',
				'is_e_invoice_taxpayer' => (int)($userRow->user_bill_e_invoice ?? 0),
				'country'               => 'Türkiye',
				'city'                  => $userRow->user_bill_city ?? '',
				'district'              => $userRow->user_bill_district ?? '',
				'postal_code'           => '',
				'address'               => $userRow->user_bill_address ?? '',
				'snapshot_at'           => date('Y-m-d H:i:s'),
			];
		}

		foreach ($productIDs as $index => $productID) {
			$childCouponDiscount = ($index === count($productIDs) - 1)
				? round($couponDiscount - $allocatedCouponDiscount, 2)
				: round($couponDiscount * ($originalPrices[$productID] / $totalListPrice), 2);
			$allocatedCouponDiscount += $childCouponDiscount;
			$childBundleSalePrice = ($index === count($productIDs) - 1)
				? round($bundleSalePrice - $allocatedBundleSalePrice, 2)
				: round($bundleSalePrice * ($originalPrices[$productID] / $totalListPrice), 2);
			$allocatedBundleSalePrice += $childBundleSalePrice;
			$salePrice = ($index === count($productIDs) - 1)
				? round($netSalePrice - $allocatedSalePrice, 2)
				: round($netSalePrice * ($originalPrices[$productID] / $totalListPrice), 2);
			$allocatedSalePrice += $salePrice;
			$insertArray = [
				'order_reseller_id' => $userID,
				'order_product_id' => $productID,
				'order_quantity' => 1,
				'order_pay_price_currency' => 'TRY', // POS tarafında ödenen toplam tutar para birimi
				'order_coupon_id' => $couponID,
				'order_require_data' => json_encode($orderRequireData),
				'order_billing_address' => json_encode($billingSnapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				'order_status' => 11, // 0: Pending
				'order_type' => 3, // Siteden
				'order_user_ip' => getIp(),
				'order_payment_token' =>  null, // Kredi Kartı işlemlerinde kullanılacak
				'order_eft_id' => !empty($post['eft_ref_code']) ? $post['eft_ref_code'] : null, // EFT İşlemlerinde Kullanılacak
				'order_eft_bank_id' => !empty($post['eft_bank_id']) ? $post['eft_bank_id'] : null, // EFT İşlemlerinde Kullanılacak
				'order_payment_type' => !empty($post['order_payment_type']) ? (int)$post['order_payment_type'] : null, // 2: Cüzdan, 3: Havale, 4: Kredi Kartı
				'order_payment_token' => !empty($post['order_payment_token']) ? trim($post['order_payment_token']) : null,
			];
			$orderPrices = $this->orderPrices($productID, $post, null, $salePrice);
			if (empty($orderPrices)) {
				$this->db->trans_rollback();
				return [
					'status' => 'error',
					'message' => 'Ürün maliyeti hesaplanamadığı için sipariş oluşturulamadı.',
				];
			}
			// Keep local grouping metadata separate from supplier response data.
			$insertArray['order_data'] = null;
			$this->load->library('analytics_tracker');
			$insertArray['order_tracking'] = $this->analytics_tracker->captureTracking();
			$insertArray['order_payment_info'] = json_encode([
				'pricing_snapshot' => $pricingSnapshot,
				'client_ip' => getIp(),
				'client_port' => getClientPort(),
			], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			$insertArray = array_merge($insertArray, $orderPrices);
			$previousDbDebug = $this->db->db_debug;
			$this->db->db_debug = false;
			$inserted = $this->db->insert('orders', $insertArray);
			$dbError = $this->db->error();
			$this->db->db_debug = $previousDbDebug;
			if ($inserted === false || $this->db->affected_rows() < 1) {
				log_message('error', 'Order insert failed. User: ' . (int)$userID . ', Product: ' . (int)$productID . ', DB error: ' . json_encode($dbError, JSON_UNESCAPED_UNICODE));
				$this->db->trans_rollback();
				return ['status' => 'error', 'message' => !empty($dbError['message']) ? $dbError['message'] : 'Sipariş oluşturulurken veritabanı hatası oluştu.'];
			}
			$orderID = (int)$this->db->insert_id();
			if ($couponID !== null) {
				$couponUsageInserted = $this->db->insert('coupon_usages', [
					'coupon_usage_coupon_id' => $couponID,
					'coupon_usage_user_id' => (int)$userID,
					'coupon_usage_order_id' => $orderID,
					'coupon_usage_discount_amount' => $childCouponDiscount,
					'coupon_usage_status' => 0,
					'coupon_usage_created_at' => date('Y-m-d H:i:s'),
				]);
				if ($couponUsageInserted === false) {
					$dbError = $this->db->error();
					log_message('error', 'Coupon usage insert failed. Coupon: ' . $couponID . ', User: ' . (int)$userID . ', Order: ' . $orderID . ', DB error: ' . json_encode($dbError, JSON_UNESCAPED_UNICODE));
					$this->db->trans_rollback();
					return ['status' => 'error', 'message' => 'Kupon kullanım kaydı oluşturulamadı.'];
				}
			}
			$orders[] = ['order_id' => $orderID, 'wallet_price' => (float)$insertArray['order_sale_price_tl']];
		}

		$this->db->trans_complete();
		if ($this->db->trans_status() === false) {
			$dbError = $this->db->error();
			log_message('error', 'Order transaction failed. User: ' . (int)$userID . ', Products: ' . implode(',', $productIDs) . ', DB error: ' . json_encode($dbError, JSON_UNESCAPED_UNICODE));
			return ['status' => 'error', 'message' => 'Sipariş oluşturulurken veritabanı hatası oluştu.'];
		}
		$totalWalletPrice = array_sum(array_column($orders, 'wallet_price'));
		return [
			'status' => 'success',
			'message' => 'Sipariş başarıyla oluşturuldu.',
			'orders' => $orders,
			'order_id' => $orders[0]['order_id'],
			'wallet_price' => $totalWalletPrice,
			'total_wallet_price' => $totalWalletPrice,
		];
	}

	public function orderPrices($productID, $post, $discount = null, $salePriceOverride = null)
	{
		$data = $this->data;
		$coupon = $this->session->userdata('applied_coupon') ?? null;
		$coupon_value = $discount === null ? (!empty($coupon['discount']) ? (float)$coupon['discount'] : 0) : (float)$discount;

		$userPrice = userPrice($productID, $data['currentLocale']->locale_code, $data['user']['user_tier']);
		$userCost = userPriceCost($productID, $data['currentLocale']->locale_code, $data['user']['user_tier']);
		if (!is_array($userCost) || !isset($userCost['cost'], $userCost['currency'])) {
			log_message('error', 'Unable to calculate order cost for product: ' . (int)$productID);
			return [];
		}
		$costTL = currencyCalculatorTo($userCost['currency'], $userCost['cost']);
		$salePrice = $salePriceOverride === null ? $userPrice - $coupon_value : max(0.0, (float)$salePriceOverride);
		$salePriceCurrency = $data['currentLocale']->locale_currency;
		$salePriceTL = currencyCalculatorTo($salePriceCurrency, $salePrice);
		$payment_method = !empty($post['payment_method']) ? trim($post['payment_method']) : null;

		$orderPosPrice = $salePrice;
		$orderPosPriceCurrency = $salePriceCurrency;
		if ($payment_method == 'credit') {
			$commission_rate = self::CREDIT_CARD_COMMISSION_RATE;
			$orderPosPrice = $salePrice + ($salePrice * $commission_rate / 100);
			$orderPosPrice = round($orderPosPrice, 2);
			$orderPosPriceCurrency = $salePriceCurrency;
		}



		$returnArray = [
			'order_cost_tl' => $costTL,
			'order_cost' => $userCost['cost'],
			'order_cost_currency' => $userCost['currency'],
			'order_sale_price' => $salePrice,
			'order_sale_price_currency' => $salePriceCurrency,
			'order_sale_price_tl' => $salePriceTL,
			'order_pay_price' => $orderPosPrice,
			'order_pay_price_currency' => $orderPosPriceCurrency,
		];
		return $returnArray;
	}
}
