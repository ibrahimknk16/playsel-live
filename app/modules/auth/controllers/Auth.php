<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->data['locales'] = $this->locales();
		$this->data['uri'] = $this->uri->segment(1) ? $this->uri->segment(1) : 'tr-TR';

		$uri = explode('-', $this->data['uri']);

		if (count($uri) < 2) {
			$lang = 'tr';
			$country = 'TR';
		} else {
			$lang = preg_replace('/[^A-Za-z]/', '', (string)$uri[0]);
						$country = preg_replace('/[^A-Za-z]/', '', (string)$uri[1]);
		}

		$langClean = strtolower($lang); // Örn: 'tr'
		$countryClean = strtoupper($country); // Örn: 'TR'

		$this->lang->load('custom', $langClean);

		$this->data['currentLocale'] = $this->db->get_where('locales', [
			'locale_lang_code' => $langClean,
			'locale_code' => $countryClean
		])->row();

		$this->data['user'] = $this->session->userdata('user');
		$this->data['lang'] = $langClean;
		$this->data['country'] = $countryClean;
		$this->data['title'] = 'Playsel';
	}

	public function index()
	{
		if (!empty($this->session->userdata('user'))) {
			redirect(topupcenter() . $this->data['uri']);
		}
		redirect(topupcenter() . $this->data['uri'] . '/login');
	}

	public function login()
	{
		if (!empty($this->session->userdata('user'))) {
			redirect(topupcenter() . $this->data['uri']);
		}
		$data = $this->data;
		$data['title'] = ($data['lang'] ?? '') === 'tr' ? 'Giriş Yap' : $data['title'] . $this->lang->line('login_register_title');
		$data['description'] = $this->lang->line('login_register_description');
		$this->template->content->view('login', $data);
		$this->template->publish();
	}

	public function register()
	{
		if (!empty($this->session->userdata('user'))) {
			redirect(topupcenter() . $this->data['uri']);
		}

		$data = $this->data;
		$data['title'] = ($data['lang'] ?? '') === 'tr' ? 'Kayıt Ol' : $data['title'] . $this->lang->line('login_register_title');
		$data['description'] = $this->lang->line('login_register_description');
		
		$ref = trim((string)$this->input->get('ref', TRUE));
		if (!empty($ref)) {
			$this->session->set_userdata('referral_nickname', $ref);
		}
		$data['referral_user'] = $this->getReferralUser();

		$countriesJsonPath = FCPATH . 'countries.json';
		$data['countries'] = [];
		// pre($countriesJsonPath);die;
		if (file_exists($countriesJsonPath)) {
			$jsonContent = file_get_contents($countriesJsonPath);
			$data['countries'] = json_decode($jsonContent, true) ?? [];
		}

		$this->template->content->view('register', $data);
		$this->template->publish();
	}

	public function loginRequest()
	{
		$data = $this->data;
		$post = $this->input->post();
		$token = $this->input->post('cf-turnstile-response');

		if (! $this->verify_turnstile($token)) {
			$data['turnstile_error'] = $this->lang->line('turnstile_error');
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('turnstile_error')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$loginEmail = strtolower(trim((string)($post['email'] ?? '')));
		$ipKey = 'login_ip_' . getIp();
		$emailKey = 'login_email_' . $loginEmail;
		if (!$this->throttleHit($ipKey, 30, 900) || !$this->throttleHit($emailKey, 10, 900)) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => 'Çok fazla deneme yaptınız. Lütfen 15 dakika sonra tekrar deneyin.'
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$userCheck = $this->db->get_where('users', ['user_email' => $post['email']])->row();


		if (empty($userCheck)) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('check_infos')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}
		$userCheck = ensure_user_nickname($userCheck);
		//password check
		if (!password_verify($post['password'], $userCheck->user_password)) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' =>  $this->lang->line('check_infos')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}
		$this->throttleClear($emailKey);

		$restriction = customerRestriction($userCheck->user_id, 'login');
		if ($restriction || (int)$userCheck->user_status !== 1) {
			$this->session->set_flashdata('alert', [
				'type' => 'warning',
				'message' => restrictedAccountMessage($restriction)
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		// 2FA CHECK
		if (isset($userCheck->user_2fa_method) && in_array($userCheck->user_2fa_method, [1, 2])) {
			$otpCode = random_int(100000, 999999);
			$method = $userCheck->user_2fa_method; // 1: Email, 2: SMS
			
			$this->db->insert('user_2fa_codes', [
				'2fa_user_id' => $userCheck->user_id,
				'2fa_otp_code' => $otpCode,
				'2fa_method' => $method,
				'2fa_status' => 0,
				'2fa_created_at' => date('Y-m-d H:i:s'),
				'2fa_expires_at' => date('Y-m-d H:i:s', strtotime('+3 minutes'))
			]);

			if ($method == 1) {
				$subject = 'Playsel · Doğrulama kodun';
				$body = $this->load->view('emails/two_factor_auth', [
					'otp_code'       => $otpCode,
					'userName'       => $userCheck->user_name ?? '',
					'expiry_minutes' => 3
				], true);
				sendEmail($userCheck->user_email, $subject, $body);
			} elseif ($method == 2) {
				if (empty($userCheck->user_phone)) {
					$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Telefon numaranız kayıtlı değil, yöneticinizle iletişime geçin.']);
					redirect($_SERVER['HTTP_REFERER']);
				}
				sendSms2($userCheck->user_phone, "Playsel Giriş Doğrulama Kodunuz: " . $otpCode);
			}
			// Save temp session
			$this->session->set_userdata('temp_2fa_user_id', $userCheck->user_id);
			if (!empty($post['redirect'])) {
				$this->session->set_userdata('temp_2fa_redirect', $post['redirect']);
			}

			$langPrefix = count(explode('-', $this->data['uri'])) < 2 ? 'tr-TR' : $this->data['uri'];
			redirect(topupcenter() . $langPrefix . '/auth/verify_2fa');
		}

		if (empty($userCheck->user_terms_accepted)) {
			$this->session->set_userdata('temp_terms_user_id', $userCheck->user_id);
			if (!empty($post['redirect'])) {
				$this->session->set_userdata('temp_terms_redirect', $post['redirect']);
			}
			$langPrefix = (strlen($this->uri->segment(1)) == 5 && strpos($this->uri->segment(1), '-') !== false) ? $this->uri->segment(1) : 'tr-TR';
			redirect(topupcenter() . $langPrefix . '/login?terms=1');
		}

		$userCheck = $this->ensure_default_billing_address($userCheck);

		$this->session->set_userdata('user', [
			'user_id' => $userCheck->user_id,
			'user_name' => $userCheck->user_name,
			'user_surname' => $userCheck->user_surname ?? '',
			'user_nickname' => $userCheck->user_nickname ?? '',
			'user_email' => $userCheck->user_email,
			'user_bill_address' => $userCheck->user_bill_address ?? '',
			'user_bill_tax_number' => $userCheck->user_bill_tax_number ?? '',	
			'user_phone' => $userCheck->user_phone ?? '',
			'user_status' => $userCheck->user_status,
			'user_session_version' => (int)($userCheck->user_session_version ?? 1),
			'user_type' => $userCheck->user_type,
			'user_tier' => $userCheck->user_tier,
			'user_lang' => $userCheck->user_lang,
			'user_country' => $userCheck->user_country,
			'user_email_is_verified' => $userCheck->user_email_is_verified,
			'user_phone_is_verified' => $userCheck->user_phone_is_verified,
			'user_bank_referance_number' => $userCheck->user_bank_referance_number,
			'user_created_at' => $userCheck->user_created_at,
			'user_terms_accepted' => $userCheck->user_terms_accepted,
			'user_last_login_ip' => getIp()
		]);

		$this->db->where('user_id', $userCheck->user_id);
		$this->db->update('users', [
			'user_last_login_ip' => getIp()
		]);

		$this->userLogInsert('LOGIN');
		$this->session->set_flashdata('alert', [
			'type' => 'success',
			'message' => $this->lang->line('login_success')
		]);

		if (!empty($post['redirect'])) {
			redirect(topupcenter() . $post['redirect']);
		}
		redirect(topupcenter());
	}

	public function verify_2fa()
	{
		$langPrefix = count(explode('-', $this->data['uri'])) < 2 ? 'tr-TR' : $this->data['uri'];
		$tempUserId = $this->session->userdata('temp_2fa_user_id');
		if (!$tempUserId) {
			redirect(topupcenter() . $langPrefix . '/login');
		}

		$userCheck = $this->db->get_where('users', ['user_id' => $tempUserId])->row();
		$restriction = $userCheck ? customerRestriction($userCheck->user_id, 'login') : null;
		if (!$userCheck || $restriction || (int)$userCheck->user_status !== 1) {
			$this->session->unset_userdata('temp_2fa_user_id');
			$this->session->set_flashdata('alert', [
				'type' => 'warning',
				'message' => restrictedAccountMessage($restriction)
			]);
			redirect(topupcenter() . $langPrefix . '/login');
		}

		$data = $this->data;
		$data['title'] = $data['title'] . ' 2FA Doğrulama';
		$data['description'] = 'İki aşamalı doğrulama kodunuzu giriniz.';
		$data['two_fa_method'] = isset($userCheck->user_2fa_method) ? $userCheck->user_2fa_method : 1;
		
		$this->template->content->view('verify-2fa', $data);
		$this->template->publish();
	}

	public function resend_2fa()
	{
		$langPrefix = count(explode('-', $this->data['uri'])) < 2 ? 'tr-TR' : $this->data['uri'];
		$tempUserId = $this->session->userdata('temp_2fa_user_id');

		if (!$tempUserId) {
			redirect(topupcenter() . $langPrefix . '/login');
		}

		$userCheck = $this->db->get_where('users', ['user_id' => $tempUserId])->row();

		if (isset($userCheck->user_2fa_method) && in_array($userCheck->user_2fa_method, [1, 2])) {
			if (!$this->throttleHit('resend2fa_cd_' . (int)$userCheck->user_id, 1, 60)
				|| !$this->throttleHit('resend2fa_hr_' . (int)$userCheck->user_id, 5, 3600)) {
				$this->session->set_flashdata('alert', [
					'type' => 'error',
					'message' => 'Yeni kod istemek için lütfen biraz bekleyin.'
				]);
				redirect(topupcenter() . $langPrefix . '/auth/verify_2fa');
			}
			// Önceki aktif kodları iptal edelim
			$this->db->where('2fa_user_id', $userCheck->user_id);
			$this->db->where('2fa_status', 0);
			$this->db->update('user_2fa_codes', ['2fa_status' => 2]); // 2: expired/cancelled

			$otpCode = random_int(100000, 999999);
			$method = $userCheck->user_2fa_method; // 1: Email, 2: SMS
			
			$this->db->insert('user_2fa_codes', [
				'2fa_user_id' => $userCheck->user_id,
				'2fa_otp_code' => $otpCode,
				'2fa_method' => $method,
				'2fa_status' => 0,
				'2fa_created_at' => date('Y-m-d H:i:s'),
				'2fa_expires_at' => date('Y-m-d H:i:s', strtotime('+3 minutes'))
			]);

			if ($method == 1) {
				$subject = 'Playsel · Doğrulama kodun';
				$body = $this->load->view('emails/two_factor_auth', [
					'otp_code'       => $otpCode,
					'userName'       => $userCheck->user_name ?? '',
					'expiry_minutes' => 3
				], true);
				sendEmail($userCheck->user_email, $subject, $body);
			} elseif ($method == 2) {
				if (!empty($userCheck->user_phone)) {
					sendSms2($userCheck->user_phone, "Playsel Giris Dogrulama Kodunuz: " . $otpCode);
				}
			}

			$this->session->set_flashdata('alert', [
				'type' => 'success',
				'message' => 'Yeni doğrulama kodunuz gönderildi.'
			]);
		}

		redirect(topupcenter() . $langPrefix . '/auth/verify_2fa');
	}

	public function verify2faRequest()
	{
		$langPrefix = count(explode('-', $this->data['uri'])) < 2 ? 'tr-TR' : $this->data['uri'];
		$post = $this->input->post(NULL, TRUE);
		$tempUserId = $this->session->userdata('temp_2fa_user_id');

		if (!$tempUserId) {
			redirect(topupcenter() . $langPrefix . '/login');
		}

		$otpCode = isset($post['otp_code']) ? trim($post['otp_code']) : '';

		$attemptKey = 'verify2fa_' . (int)$tempUserId;
		if (!$this->throttleHit($attemptKey, 5, 600)) {
			$this->db->where('2fa_user_id', $tempUserId)->where('2fa_status', 0)
				->update('user_2fa_codes', ['2fa_status' => 2]);
			$this->session->unset_userdata('temp_2fa_user_id');
			$this->session->unset_userdata('temp_2fa_redirect');
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => 'Çok fazla hatalı deneme. Lütfen yeniden giriş yapın.'
			]);
			redirect(topupcenter() . $langPrefix . '/login');
		}

		$checkCode = $this->db->get_where('user_2fa_codes', [
			'2fa_user_id' => $tempUserId,
			'2fa_otp_code' => $otpCode,
			'2fa_status' => 0,
			'2fa_expires_at >=' => date('Y-m-d H:i:s')
		])->row();

		if (empty($checkCode)) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => 'Girdiğiniz kod hatalı veya süresi dolmuş.'
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}
		$this->throttleClear($attemptKey);

		// Update code as used
		$this->db->where('2fa_id', $checkCode->{'2fa_id'});
		$this->db->update('user_2fa_codes', ['2fa_status' => 1]);

		// Login user
		$userCheck = $this->db->get_where('users', ['user_id' => $tempUserId])->row();
		if (empty($userCheck->user_terms_accepted)) {
			$this->session->set_userdata('temp_terms_user_id', $userCheck->user_id);
			$redirectUrl = $this->session->userdata('temp_2fa_redirect');
			if (!empty($redirectUrl)) {
				$this->session->set_userdata('temp_terms_redirect', $redirectUrl);
			}
			$this->session->unset_userdata('temp_2fa_user_id');
			$this->session->unset_userdata('temp_2fa_redirect');
			$langPrefix = (strlen($this->uri->segment(1)) == 5 && strpos($this->uri->segment(1), '-') !== false) ? $this->uri->segment(1) : 'tr-TR';
			redirect(topupcenter() . $langPrefix . '/login?terms=1');
		}

		$userCheck = $this->ensure_default_billing_address($userCheck);

		$this->session->set_userdata('user', [
			'user_id' => $userCheck->user_id,
			'user_name' => $userCheck->user_name,
			'user_surname' => $userCheck->user_surname ?? '',
			'user_nickname' => $userCheck->user_nickname ?? '',
			'user_email' => $userCheck->user_email,
			'user_bill_address' => $userCheck->user_bill_address ?? '',
			'user_bill_tax_number' => $userCheck->user_bill_tax_number ?? '',	
			'user_phone' => $userCheck->user_phone ?? '',
			'user_status' => $userCheck->user_status,
			'user_session_version' => (int)($userCheck->user_session_version ?? 1),
			'user_type' => $userCheck->user_type,
			'user_tier' => $userCheck->user_tier,
			'user_lang' => $userCheck->user_lang,
			'user_country' => $userCheck->user_country,
			'user_email_is_verified' => $userCheck->user_email_is_verified,
			'user_phone_is_verified' => $userCheck->user_phone_is_verified,
			'user_bank_referance_number' => $userCheck->user_bank_referance_number,
			'user_created_at' => $userCheck->user_created_at ?? '',
			'user_terms_accepted' => $userCheck->user_terms_accepted,
			'user_last_login_ip' => getIp()
		]);
		$this->db->where('user_id', $userCheck->user_id);
		$this->db->update('users', [
			'user_last_login_ip' => getIp()
		]);

		$this->userLogInsert('LOGIN_2FA');
		
		$redirectUrl = $this->session->userdata('temp_2fa_redirect');
		
		$this->session->unset_userdata('temp_2fa_user_id');
		$this->session->unset_userdata('temp_2fa_redirect');

		$this->session->set_flashdata('alert', [
			'type' => 'success',
			'message' => $this->lang->line('login_success') ?? 'Başarıyla giriş yapıldı.'
		]);

		if (!empty($redirectUrl)) {
			redirect(topupcenter() . $redirectUrl);
		}
		redirect(topupcenter());
	}

	public function registerRequest()
	{
		$data = $this->data;
		$post = $this->input->post();
		$token = $this->input->post('cf-turnstile-response');
		if (!$this->verify_turnstile($token)) {
			$data['turnstile_error'] = $this->lang->line('turnstile_error');
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('turnstile_error')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$post['email'] = strtolower(trim((string)($post['email'] ?? '')));
		if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL) || strlen($post['email']) > 190) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('wrong_email')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$userCheck = $this->db->get_where('users', ['user_email' => $post['email']])->row();
		if (!empty($userCheck)) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('email_already')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$post['name'] = trim($post['name'] ?? '');
		$post['surname'] = trim($post['surname'] ?? $post['last_name'] ?? '');

		// Fallback: Ad ve soyad tek alandan geldiyse ayrıştır
		if ($post['surname'] === '' && $post['name'] !== '') {
			$nameParts = explode(' ', $post['name']);
			if (count($nameParts) > 1) {
				$post['surname'] = array_pop($nameParts);
				$post['name'] = implode(' ', $nameParts);
			}
		}

		$validName = preg_match('/^[a-zA-ZğüşıöçĞÜŞİÖÇ\s]{2,}$/u', $post['name']);
		$validSurname = ($post['surname'] !== '') ? preg_match('/^[a-zA-ZğüşıöçĞÜŞİÖÇ\s]{2,}$/u', $post['surname']) : true;

		if (!$validName || !$validSurname || $post['name'] === '') {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('name_rule')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		// Telefon Numarası
		$rawPhone = trim($post['phone'] ?? $post['user_phone'] ?? '');
		$cleanPhone = preg_replace('/\s+/', '', $rawPhone);
		if ($cleanPhone !== '') {
			if (str_starts_with($cleanPhone, '00')) {
				$cleanPhone = '+' . substr($cleanPhone, 2);
			} elseif (str_starts_with($cleanPhone, '+')) {
				// uluslararası format
			} elseif (str_starts_with($cleanPhone, '90') && strlen($cleanPhone) === 12) {
				$cleanPhone = '+' . $cleanPhone;
			} elseif (str_starts_with($cleanPhone, '0') && strlen($cleanPhone) === 11) {
				$cleanPhone = '+9' . $cleanPhone;
			} elseif (strlen($cleanPhone) === 10 && str_starts_with($cleanPhone, '5')) {
				$cleanPhone = '+90' . $cleanPhone;
			}
		}

		if ($cleanPhone === '') {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => 'Lütfen geçerli bir telefon numarası giriniz.'
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$passwordRegex = '/^(?=.{8,}$)(?=.*[A-ZÇĞİÖŞÜ])(?=.*[a-zçğıöşü])(?=.*\d)(?=.*[^A-Za-zçğıöşüÇĞİÖŞÜ\d]).+$/u';
		$post['password'] = trim($post['password']);
		if (!preg_match($passwordRegex, $post['password'])) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('password_rule')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$post['confirm_password'] = trim($post['confirm_password']);
		if ($post['password'] != $post['confirm_password']) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('password_unmatch')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$ref = generateRandomString(16, '09');
		$checkRef = $this->db->get_where('users', ['user_bank_referance_number' => $ref])->row();
		while ($checkRef) {
			$ref = generateRandomString(16, '09');
			$checkRef = $this->db->get_where('users', ['user_bank_referance_number' => $ref])->row();
		}

		$refUser = $this->getReferralUser();
		$user_reference_id = $refUser ? (int)$refUser->user_id : null;

		$fullName = trim($post['name'] . ' ' . $post['surname']);
		$insertArray = [
			'user_name' => $post['name'],
			'user_surname' => $post['surname'],
			'user_nickname' => generate_unique_nickname($post['name'], $post['surname']),
			'user_email' => $post['email'],
			'user_password' => password_hash($post['password'], PASSWORD_BCRYPT),
			'user_phone' => $cleanPhone,
			'user_phone_is_verified' => 0,
			'user_status' => 1,
			'user_type' => 4,
			'user_tier' => 1,
			'user_lang' => $data['lang'],
			'user_country' => $data['country'],
			'user_bank_referance_number' => $ref,
			'user_reference_id' => $user_reference_id,
			'user_email_is_verified' => 0,
			'user_bill_company_name' => $fullName ?: 'Kullanıcı',
			'user_bill_tax_number' => '11111111111',
			'user_bill_city' => 'İstanbul',
			'user_bill_district' => 'Kadıköy',
			'user_bill_address' => 'Merkez Mah.',
		];

		$this->db->insert('users', $insertArray);
		$id = $this->db->insert_id();

		if ($id) {
			$newUser = $this->db->get_where('users', ['user_id' => $id])->row();
			if ($newUser) {
				$this->ensure_default_billing_address($newUser);
			}
			$this->session->unset_userdata('referral_nickname');

			// Generate Email Verification Token
			$token = generateRandomString(64, 'aZ09');
			$this->db->insert('verify_email', [
				'verify_email_email' => $post['email'],
				'verify_email_token' => $token,
				'verify_email_used' => 0,
				'verify_email_expires_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
				'verify_email_created_at' => date('Y-m-d H:i:s')
			]);

			// Send Verification Email
			$verifyUrl = base_url('auth/verifyEmail/' . $token);
			$emailSubject = 'Playsel - E-Posta Doğrulama';
			$fullName = trim($post['name'] . ' ' . $post['surname']);
			$emailMessage = $this->getVerificationEmailTemplate($fullName ?: $post['name'], $verifyUrl);
			sendEmail($post['email'], $emailSubject, $emailMessage);

			$this->session->set_flashdata('alert', [
				'type' => 'success',
				'message' => 'Kayıt başarılı! Lütfen giriş yapmadan önce e-posta adresinize gönderilen doğrulama linkine tıklayarak hesabınızı doğrulayın.'
			]);
			
			redirect(base_url('login'));
		} else {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('register_unsuccess')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}
	}

	public function forgotPassword()
	{
		if (!empty($this->session->userdata('user'))) {
			redirect(base_url($this->uri->segment(1)));
		}

		if ($this->input->method() === 'post') {
			$post  = $this->input->post();
			$token = $this->input->post('cf-turnstile-response');

			// Captcha doğrulama
			if (!$this->verify_turnstile($token)) {
				$this->session->set_flashdata('alert', [
					'type'    => 'error',
					'message' => $this->lang->line('turnstile_error')
				]);
				redirect(topupcenter() . $this->uri->segment(1) . '/forgot-password');
			}

			// Kullanıcı kontrolü
			$forgotEmail = strtolower(trim((string)($post['email'] ?? '')));
			if (!$this->throttleHit('forgot_ip_' . getIp(), 10, 3600) || !$this->throttleHit('forgot_email_' . $forgotEmail, 3, 3600)) {
				$this->session->set_flashdata('alert', [
					'type'    => 'error',
					'message' => 'Çok fazla istek gönderdiniz. Lütfen daha sonra tekrar deneyin.'
				]);
				redirect(topupcenter() . $this->uri->segment(1) . '/forgot-password');
			}
			$userCheck = $this->db->get_where('users', ['user_email' => $post['email']])->row();
			if (empty($userCheck)) {
				// Hesap numaralandırmayı önlemek için başarılı gibi yanıtla
				$this->session->set_flashdata('alert', [
					'type'    => 'success',
					'message' => $this->lang->line('email_reset_password')
				]);
				redirect(topupcenter() . $this->uri->segment(1) . '/forgot-password');
			}
			// Son 5 dakika içinde istek yapılmış mı kontrolü
			$recentRequest = $this->db->get_where('forgot_password', [
				'forgot_password_email' => $post['email'],
				'forgot_password_created_at >=' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
			])->row();

			if (!empty($recentRequest)) {
				$this->session->set_flashdata('alert', [
					'type'    => 'success',
					'message' => $this->lang->line('email_reset_password')
				]);
				redirect(topupcenter() . $this->uri->segment(1) . '/forgot-password');
			}

			// Reset token üret
			$resetToken = bin2hex(random_bytes(32));
			$expiresAt  = date('Y-m-d H:i:s', strtotime('+5 minutes'));
			$createdAt  = date('Y-m-d H:i:s');

			// forgot_password tablosuna kaydet
			$this->db->insert('forgot_password', [
				'forgot_password_email'       => $post['email'],
				'forgot_password_token'       => $resetToken,
				'forgot_password_is_clicked'  => 0,
				'forgot_password_expires_at'  => $expiresAt,
				'forgot_password_created_at'  => $createdAt
			]);

			$resetLink = topupcenter() . $this->uri->segment(1) . '/reset-password?token=' . $resetToken;
			$subject = "Playsel - Şifre Sıfırlama Talebi";
			$fullName = trim(($userCheck->user_name ?? '') . ' ' . ($userCheck->user_surname ?? ''));
			$message = $this->load->view('emails/password_reset', [
				'userName'          => $fullName ?: ($userCheck->user_name ?? ''),
				'reset_url'         => $resetLink,
				'expiry_minutes'    => 5,
				'reset_request_url' => topupcenter() . $this->uri->segment(1) . '/forgot-password'
			], true);
			sendEmail($post['email'], $subject, $message);

			// Alert
			$this->session->set_flashdata('alert', [
				'type'    => 'success',
				'message' => $this->lang->line('email_reset_password')
			]);
			redirect(topupcenter() . $this->uri->segment(1) . '/forgot-password');
		}

		$data = $this->data;
		$data['title']       = $data['title'] . " | " . $this->lang->line('change_password_title');
		$data['description'] = $this->lang->line('login_register_description');

		$this->template->content->view('forgot-password', $data);
		$this->template->publish();
	}


	public function resetPassword()
	{
		if (!empty($this->session->userdata('user'))) {
			redirect(base_url($this->uri->segment(1)));
		}

		$token = $this->input->get('token');
		if (!$token) {
			show_404();
		}
		// Token kontrol
		$tokenRow = $this->db->get_where('forgot_password', [
			'forgot_password_token' => $token
		])->row();

		if (empty($tokenRow)) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('invalid_token')
			]);
			redirect(topupcenter() . $this->uri->segment(1) . '/login');
		}

		// Token expire / daha önce kullanılmış kontrolü
		if ($tokenRow->forgot_password_is_clicked == 1 || strtotime($tokenRow->forgot_password_expires_at) < time()) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('invalid_token')
			]);
			redirect(topupcenter() . $this->uri->segment(1) . '/login');
		}

		if ($this->input->method() === 'post') {
			$post = $this->input->post();

			$password = trim($post['password']);
			$confirm  = trim($post['confirm_password']);

			// Şifre kuralı
			$passwordRegex = '/^(?=.{8,}$)(?=.*[A-ZÇĞİÖŞÜ])(?=.*[a-zçğıöşü])(?=.*\d)(?=.*[^A-Za-zçğıöşüÇĞİÖŞÜ\d]).+$/u';

			if (!preg_match($passwordRegex, $password)) {
				$this->session->set_flashdata('alert', [
					'type' => 'error',
					'message' => $this->lang->line('password_rule')
				]);
				redirect($_SERVER['HTTP_REFERER']);
			}

			if ($password !== $confirm) {
				$this->session->set_flashdata('alert', [
					'type' => 'error',
					'message' => $this->lang->line('password_unmatch')
				]);
				redirect($_SERVER['HTTP_REFERER']);
			}

			// Şifreyi güncelle
			$this->db->update('users', [
				'user_password' => password_hash($password, PASSWORD_BCRYPT)
			], ['user_email' => $tokenRow->forgot_password_email]);

			// Token'i kullanılmış yap
			$this->db->update('forgot_password', [
				'forgot_password_is_clicked' => 1
			], ['forgot_password_token' => $token]);

			$this->session->set_flashdata('alert', [
				'type' => 'success',
				'message' => $this->lang->line('reset_password_success')
			]);
			redirect(topupcenter() . $this->uri->segment(1) . '/login');
		}

		// GET ise formu aç
		$data = $this->data;

		$data['title']       = $data['title'] . " | " . $this->lang->line('change_password_title');
		$data['description'] = $this->lang->line('login_register_description');
		$data['token']       = $token;

		$this->template->content->view('reset-password', $data);

		$this->template->publish();
	}

	public function logout()
	{
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('temp_terms_user_id');
		$this->session->unset_userdata('temp_terms_redirect');
		$this->session->unset_userdata('temp_2fa_user_id');
		$this->session->unset_userdata('temp_2fa_redirect');
		
		$this->session->set_flashdata('alert', [
			'type' => 'success',
			'message' => $this->lang->line('logout_success')
		]);
		$langPrefix = (strlen($this->uri->segment(1)) == 5 && strpos($this->uri->segment(1), '-') !== false) ? $this->uri->segment(1) : 'tr-TR';
		redirect(topupcenter() . $langPrefix . '/');
	}

	public function googleLogin()
	{
		$clientId = trim((string)getenv('GOOGLE_CLIENT_ID'));
		if ($clientId === '') {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Google ile giriş şu anda kullanılamıyor.']);
			redirect(base_url($this->data['uri'] . '/login'));
		}

		$redirect = trim((string)$this->input->get('redirect', TRUE));
		if ($redirect !== '' && (strpos($redirect, '/') !== 0 || strpos($redirect, '//') === 0)) {
			$redirect = '';
		}
		$langPrefix = trim((string)$this->input->get('locale', TRUE));
		$validLocales = ['tr-TR', 'en-US', 'ar-AE', 'en-AE', 'de-DE', 'ru-RU', 'en-RU', 'id-ID'];
		if (!in_array($langPrefix, $validLocales, true)) {
			$langPrefix = 'tr-TR';
		}

		$state = generateRandomString(64, 'az09');
		$this->session->set_userdata('google_oauth_state', $state);
		$this->session->set_userdata('google_oauth_redirect', $redirect);
		$this->session->set_userdata('google_oauth_locale', $langPrefix);

		$query = http_build_query([
			'client_id' => $clientId,
			'redirect_uri' => base_url('auth/googleCallback'),
			'response_type' => 'code',
			'scope' => 'openid email profile',
			'state' => $state,
			'prompt' => 'select_account',
		]);
		redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
	}

	public function googleCallback()
	{
		$state = (string)$this->input->get('state', TRUE);
		$expectedState = (string)$this->session->userdata('google_oauth_state');
		$redirect = (string)$this->session->userdata('google_oauth_redirect');
		$langPrefix = (string)$this->session->userdata('google_oauth_locale');
		$validLocales = ['tr-TR', 'en-US', 'ar-AE', 'en-AE', 'de-DE', 'ru-RU', 'en-RU', 'id-ID'];
		if (!in_array($langPrefix, $validLocales, true)) {
			$langPrefix = 'tr-TR';
		}
		$this->session->unset_userdata(['google_oauth_state', 'google_oauth_redirect', 'google_oauth_locale']);

		if ($expectedState === '' || $state === '' || !hash_equals($expectedState, $state)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Google giriş doğrulaması geçersiz.']);
			redirect(topupcenter() . $langPrefix . '/login');
		}
		if ($this->input->get('error', TRUE)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Google ile giriş iptal edildi.']);
			redirect(topupcenter() . $langPrefix . '/login');
		}

		$clientId = trim((string)getenv('GOOGLE_CLIENT_ID'));
		$clientSecret = trim((string)getenv('GOOGLE_CLIENT_SECRET'));
		$code = trim((string)$this->input->get('code', TRUE));
		if ($clientId === '' || $clientSecret === '' || $code === '') {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Google giriş bilgileri eksik.']);
			redirect(topupcenter() . $langPrefix . '/login');
		}

		$token = $this->http_post('https://oauth2.googleapis.com/token', [
			'code' => $code,
			'client_id' => $clientId,
			'client_secret' => $clientSecret,
			'redirect_uri' => base_url('auth/googleCallback'),
			'grant_type' => 'authorization_code',
		]);
		$idToken = is_array($token) ? (string)($token['id_token'] ?? '') : '';
		if ($idToken === '') {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Google hesabı doğrulanamadı.']);
			redirect(topupcenter() . $langPrefix . '/login');
		}

		$identity = $this->http_get('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($idToken));
		$issuer = is_array($identity) ? (string)($identity['iss'] ?? '') : '';
		$audience = is_array($identity) ? (string)($identity['aud'] ?? '') : '';
		$expiresAt = is_array($identity) ? (int)($identity['exp'] ?? 0) : 0;
		$email = is_array($identity) ? strtolower(trim((string)($identity['email'] ?? ''))) : '';
		if (!is_array($identity) || !in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)
			|| $audience !== $clientId || $expiresAt < time()
			|| empty($identity['email_verified']) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Google hesabı doğrulanamadı.']);
			redirect(topupcenter() . $langPrefix . '/login');
		}

		$user = $this->db->get_where('users', ['user_email' => $email])->row();
		if (!$user) {
			$ref = generateRandomString(16, '09');
			while ($this->db->get_where('users', ['user_bank_referance_number' => $ref])->row()) {
				$ref = generateRandomString(16, '09');
			}
			$refUser = $this->getReferralUser();
			$givenName = trim((string)($identity['given_name'] ?? ''));
			$familyName = trim((string)($identity['family_name'] ?? ''));
			$name = trim($givenName . ' ' . $familyName);
			if ($name === '') {
				$name = trim((string)($identity['name'] ?? ''));
			}
			if ($name === '') {
				$name = strstr($email, '@', true);
			}
			$defaultFullName = !empty($name) ? $name : 'Kullanıcı';
			$this->db->insert('users', [
				'user_name' => !empty($givenName) ? $givenName : $name,
				'user_surname' => $familyName,
				'user_nickname' => generate_unique_nickname(!empty($givenName) ? $givenName : $name, $familyName),
				'user_email' => $email,
				'user_password' => password_hash(generateRandomString(40, 'az09'), PASSWORD_BCRYPT),
				'user_status' => 1,
				'user_type' => 4,
				'user_tier' => 1,
				'user_lang' => $this->data['lang'],
				'user_country' => $this->data['country'],
				'user_bank_referance_number' => $ref,
				'user_reference_id' => $refUser ? (int)$refUser->user_id : null,
				'user_email_is_verified' => 1,
				'user_bill_company_name' => $defaultFullName,
				'user_bill_tax_number' => '11111111111',
				'user_bill_city' => 'İstanbul',
				'user_bill_district' => 'Kadıköy',
				'user_bill_address' => 'Merkez Mah.',
			]);
			$user = $this->db->get_where('users', ['user_id' => $this->db->insert_id()])->row();
		}
		if ($user) {
			$this->session->unset_userdata('referral_nickname');
		}
		$user = ensure_user_nickname($user);
		$user = $this->ensure_default_billing_address($user);

		$googleRestriction = $user ? customerRestriction($user->user_id, 'login') : null;
		if (!$user || $googleRestriction || (int)$user->user_status !== 1) {
			$this->session->set_flashdata('alert', ['type' => 'warning', 'message' => restrictedAccountMessage($googleRestriction)]);
			redirect(topupcenter() . $langPrefix . '/login');
		}
		if (empty($user->user_terms_accepted)) {
			$this->session->set_userdata('temp_terms_user_id', $user->user_id);
			if ($redirect !== '') {
				$this->session->set_userdata('temp_terms_redirect', $redirect);
			}
			redirect(topupcenter() . $langPrefix . '/login?terms=1');
		}

		$this->setUserSession($user);
		$this->userLogInsert('LOGIN');
		redirect($redirect !== '' ? topupcenter() . ltrim($redirect, '/') : topupcenter() . $langPrefix);
	}

	private function getReferralUser()
	{
		$nickname = strtolower(trim((string)$this->session->userdata('referral_nickname')));
		if ($nickname === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $nickname)) {
			return null;
		}

		return $this->db->where('LOWER(user_nickname) = ' . $this->db->escape($nickname), null, false)
			->where('user_ref_permission', 1)
			->get('users')->row();
	}

	private function ensure_default_billing_address($user)
	{
		if (!$user || empty($user->user_id)) {
			return $user;
		}

		$userID = (int)$user->user_id;
		$fullName = trim(($user->user_name ?? '') . ' ' . ($user->user_surname ?? ''));
		if ($fullName === '') {
			$fullName = 'Kullanıcı';
		}
		$defaultTckn = '11111111111';
		$defaultCity = 'İstanbul';
		$defaultDistrict = 'Kadıköy';
		$defaultAddress = 'Merkez Mah.';

		// 1. user_addresses tablosunda aktif adres var mı?
		if ($this->db->table_exists('user_addresses')) {
			$existingAddress = $this->db->where('user_addresses_user_id', $userID)
				->where('user_addresses_is_deleted', 0)
				->get('user_addresses')
				->row();

			if (!$existingAddress) {
				$this->db->insert('user_addresses', [
					'user_addresses_user_id'               => $userID,
					'user_addresses_title'                 => 'Fatura Adresi',
					'user_addresses_invoice_type'          => 1, // 1 = Bireysel
					'user_addresses_full_name'             => $fullName,
					'user_addresses_company_name'          => '',
					'user_addresses_tax_office'            => '',
					'user_addresses_tax_number'            => $defaultTckn,
					'user_addresses_is_turkish_citizen'    => 1,
					'user_addresses_is_turkish_company'    => 0,
					'user_addresses_is_e_invoice_taxpayer' => 0,
					'user_addresses_country'               => 'Türkiye',
					'user_addresses_city'                  => $defaultCity,
					'user_addresses_district'              => $defaultDistrict,
					'user_addresses_postal_code'           => '34000',
					'user_addresses_address'               => $defaultAddress,
					'user_addresses_phone'                 => $user->user_phone ?? '',
					'user_addresses_is_default'            => 1,
					'user_addresses_is_deleted'            => 0,
					'user_addresses_created_at'            => date('Y-m-d H:i:s'),
					'user_addresses_updated_at'            => date('Y-m-d H:i:s'),
				]);
			} elseif (empty($existingAddress->user_addresses_phone) && !empty($user->user_phone)) {
				$this->db->where('user_addresses_id', $existingAddress->user_addresses_id)->update('user_addresses', [
					'user_addresses_phone' => $user->user_phone,
					'user_addresses_updated_at' => date('Y-m-d H:i:s')
				]);
			}
		}

		// 2. users tablosundaki fatura alanları eksikse tamamla
		$userUpdates = [];
		if (empty($user->user_bill_tax_number)) {
			$userUpdates['user_bill_tax_number'] = $defaultTckn;
		}
		if (empty($user->user_bill_company_name)) {
			$userUpdates['user_bill_company_name'] = $fullName;
		}
		if (empty($user->user_bill_city)) {
			$userUpdates['user_bill_city'] = $defaultCity;
		}
		if (empty($user->user_bill_district)) {
			$userUpdates['user_bill_district'] = $defaultDistrict;
		}
		if (empty($user->user_bill_address)) {
			$userUpdates['user_bill_address'] = $defaultAddress;
		}

		if (!empty($userUpdates)) {
			$this->db->where('user_id', $userID)->update('users', $userUpdates);
			foreach ($userUpdates as $field => $val) {
				$user->$field = $val;
			}
		}

		return $user;
	}

	private function setUserSession($user)
	{
		$this->session->set_userdata('user', [
			'user_id' => $user->user_id,
			'user_name' => $user->user_name,
			'user_surname' => $user->user_surname ?? '',
			'user_nickname' => $user->user_nickname ?? '',
			'user_email' => $user->user_email,
			'user_bill_address' => $user->user_bill_address ?? '',
			'user_bill_tax_number' => $user->user_bill_tax_number ?? '',
			'user_phone' => $user->user_phone ?? '',
			'user_status' => $user->user_status,
			'user_type' => $user->user_type,
			'user_tier' => $user->user_tier,
			'user_lang' => $user->user_lang,
			'user_country' => $user->user_country,
			'user_email_is_verified' => $user->user_email_is_verified,
			'user_phone_is_verified' => $user->user_phone_is_verified,
			'user_bank_referance_number' => $user->user_bank_referance_number,
			'user_created_at' => $user->user_created_at ?? '',
			'user_terms_accepted' => $user->user_terms_accepted,
			'user_last_login_ip' => getIp(),
		]);
		$this->db->where('user_id', $user->user_id)->update('users', ['user_last_login_ip' => getIp()]);
	}

	protected function verify_turnstile($token)
	{
		if (empty($token)) {
			return FALSE;
		}

		$secret = '0x4AAAAAADLcnY5DhZbSAyFr_xjvURzPe5s';
		$remoteip = $this->input->ip_address();

		$response = $this->http_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
			'secret'   => $secret,
			'response' => $token,
			'remoteip' => $remoteip,
		]);

		if (! $response || ! isset($response['success'])) {
			return FALSE;
		}

		return $response['success'] === true;
	}

	public function mailVerify()
	{
		$data = $this->data;
		$data['title'] = $data['title'] . $this->lang->line('login_register_title');
		$data['description'] = $this->lang->line('login_register_description');
		$this->template->content->view('mail-verify', $data);
		$this->template->publish();
	}

	/**
	 * Basit POST helper: cURL ile JSON parse
	 */
	protected function http_post($url, array $data)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
		$result = curl_exec($ch);
		curl_close($ch);

		return json_decode($result, true);
	}

	protected function http_get($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$result = curl_exec($ch);
		curl_close($ch);
		return json_decode($result, true);
	}

	public function send_otp($to_email, $otp_code, $lang = 'tr')
	{
		// 1) Language yükle
		$this->lang->load('email', $lang);

		// 2) Verify link oluştur
		$verify_url = topupcenter() . "verify?code={$otp_code}";

		// 3) E‐posta içeriği
		$body = $this->load->view('emails/otp', [
			'otp_code'   => $otp_code,
			'verify_url' => $verify_url
		], true);

		// 4) E‐posta başlığı
		$subject = $this->lang->line('otp_subject');

		// 5) Email kütüphanesi ile gönder
		$this->load->library('email');
		$this->email->from('no-reply@topupcenter.com', 'TopUpCenter');
		$this->email->to($to_email);
		$this->email->subject($subject);
		$this->email->message($body);
		$this->email->set_mailtype('html');

		return $this->email->send();
	}
	public function verifyEmail($token = null)
	{
		if (empty($token)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Geçersiz doğrulama bağlantısı.']);
			redirect(base_url('login'));
		}

		$verifyRecord = $this->db->get_where('verify_email', [
			'verify_email_token' => $token,
			'verify_email_used' => 0,
			'verify_email_expires_at >' => date('Y-m-d H:i:s')
		])->row();

		if ($verifyRecord) {
			$this->db->where('user_email', $verifyRecord->verify_email_email);
			$this->db->update('users', ['user_email_is_verified' => 1]);

			$this->db->where('verify_email_id', $verifyRecord->verify_email_id);
			$this->db->update('verify_email', ['verify_email_used' => 1]);

			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'E-posta adresiniz başarıyla doğrulandı. Şimdi giriş yapabilirsiniz.']);
			redirect(base_url('login'));
		} else {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Doğrulama bağlantısı geçersiz veya süresi dolmuş.']);
			redirect(base_url('login'));
		}
	}

	public function resendVerification()
	{
		$email = trim($this->input->post('email', TRUE));

		if (empty($email)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen e-posta adresinizi girin.']);
			redirect(base_url('login'));
		}

		$user = $this->db->get_where('users', ['user_email' => $email])->row();

		if (!$user) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Bu e-posta adresine kayıtlı bir hesap bulunamadı.']);
			redirect(base_url('login'));
		}

		if ($user->user_email_is_verified == 1) {
			$this->session->set_flashdata('alert', ['type' => 'info', 'message' => 'Bu hesabın e-posta adresi zaten doğrulanmış.']);
			redirect(base_url('login'));
		}

		// Check if there is already an unused token generated recently (e.g. last 5 minutes to prevent spam)
		$recentRequest = $this->db->get_where('verify_email', [
			'verify_email_email' => $email,
			'verify_email_used' => 0,
			'verify_email_created_at >' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
		])->row();

		if ($recentRequest) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Kısa süre önce bir doğrulama maili gönderildi. Lütfen tekrar istemeden önce birkaç dakika bekleyin.']);
			redirect(base_url('login'));
		}

		// Generate Email Verification Token
		$token = generateRandomString(64, 'aZ09');
		$this->db->insert('verify_email', [
			'verify_email_email' => $email,
			'verify_email_token' => $token,
			'verify_email_used' => 0,
			'verify_email_expires_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
			'verify_email_created_at' => date('Y-m-d H:i:s')
		]);

		// Send Verification Email
		$verifyUrl = base_url('auth/verifyEmail/' . $token);
		$emailSubject = 'Playsel - E-Posta Doğrulama (Tekrar Gönderildi)';
		$fullName = trim(($user->user_name ?? '') . ' ' . ($user->user_surname ?? ''));
		$emailMessage = $this->getVerificationEmailTemplate($fullName ?: $user->user_name, $verifyUrl);
		sendEmail($email, $emailSubject, $emailMessage);

		$this->session->set_flashdata('alert', [
			'type' => 'success',
			'message' => 'Doğrulama maili tekrar gönderildi. Lütfen e-posta adresinizi kontrol edin.'
		]);
		
		redirect(base_url('login'));
	}

	private function getVerificationEmailTemplate($userName, $verifyUrl)
	{
		return $this->load->view('emails/email_verification', [
			'userName'         => $userName,
			'verification_url' => $verifyUrl,
			'expiry_minutes'   => '24 saat'
		], true);
	}

	public function acceptUserAgreement()
	{
		$user = $this->session->userdata('user');
		$tempUserId = $this->session->userdata('temp_terms_user_id');
		
		if ($tempUserId) {
			$this->db->where('user_id', $tempUserId)->update('users', ['user_terms_accepted' => 1]);
			$userCheck = $this->db->get_where('users', ['user_id' => $tempUserId])->row();
			if ($userCheck) {
				$userCheck = $this->ensure_default_billing_address($userCheck);
			}

			$this->session->set_userdata('user', [
				'user_id' => $userCheck->user_id,
				'user_name' => $userCheck->user_name,
				'user_surname' => $userCheck->user_surname ?? '',
				'user_nickname' => $userCheck->user_nickname ?? '',
				'user_email' => $userCheck->user_email,
				'user_bill_address' => $userCheck->user_bill_address ?? '',
				'user_bill_tax_number' => $userCheck->user_bill_tax_number ?? '',	
				'user_phone' => $userCheck->user_phone ?? '',
				'user_status' => $userCheck->user_status,
				'user_type' => $userCheck->user_type,
				'user_tier' => $userCheck->user_tier,
				'user_lang' => $userCheck->user_lang,
				'user_country' => $userCheck->user_country,
				'user_email_is_verified' => $userCheck->user_email_is_verified,
				'user_phone_is_verified' => $userCheck->user_phone_is_verified,
				'user_bank_referance_number' => $userCheck->user_bank_referance_number,
				'user_created_at' => $userCheck->user_created_at ?? '',
				'user_terms_accepted' => 1
			]);
			
			$this->userLogInsert('USER-AGREEMENT');
			
			$redirectUrl = $this->session->userdata('temp_terms_redirect');
			$this->session->unset_userdata('temp_terms_user_id');
			$this->session->unset_userdata('temp_terms_redirect');
			
			$this->session->set_flashdata('alert', [
				'type' => 'success',
				'message' => 'Kullanıcı sözleşmesi onaylandı. Başarıyla giriş yapıldı.'
			]);
			
			if (!empty($redirectUrl)) {
				redirect(topupcenter() . $redirectUrl);
			}
			$langPrefix = (strlen($this->uri->segment(1)) == 5 && strpos($this->uri->segment(1), '-') !== false) ? $this->uri->segment(1) : 'tr-TR';
			redirect(topupcenter() . $langPrefix . '/');
		} elseif ($user && isset($user['user_id'])) {
			$this->db->where('user_id', $user['user_id'])->update('users', ['user_terms_accepted' => 1]);
			$user['user_terms_accepted'] = 1;
			$this->session->set_userdata('user', $user);
			
			$this->userLogInsert('USER-AGREEMENT');
			
			$this->session->set_flashdata('alert', [
				'type' => 'success',
				'message' => 'Kullanıcı sözleşmesi onaylandı.'
			]);
			
			$langPrefix = (strlen($this->uri->segment(1)) == 5 && strpos($this->uri->segment(1), '-') !== false) ? $this->uri->segment(1) : 'tr-TR';
			redirect(topupcenter() . $langPrefix . '/');
		} else {
			$langPrefix = (strlen($this->uri->segment(1)) == 5 && strpos($this->uri->segment(1), '-') !== false) ? $this->uri->segment(1) : 'tr-TR';
			redirect(topupcenter() . $langPrefix . '/login');
		}
	}
}
