<?php defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->data['user'] = $this->session->userdata('user');
		if (empty($this->data['user']) && $this->router->fetch_method() !== 'changeRegion') {
			redirect('login');
		}
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
		$this->data['lang'] = $lang;
		$this->data['country'] = $country;
		$this->data['title'] = site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel') . ' | ';
		$this->load->model('Wallet_model');
		$this->data['userBalance'] = $this->Wallet_model->get_user_balances($this->data['user']['user_id'] ?? 0) ?? [
			'withdrawable_balance' => 0,
			'spendable_balance' => 0,
			'total_balance' => 0,
		];
	}

	public function updateProfile()
	{
		$post = $this->input->post(NULL, TRUE);
		$userID = $this->data['user']['user_id'];
		
		$updateData = [];
        if (isset($post['first_name']) || isset($post['user_name'])) {
            $firstName = strip_tags(trim($post['first_name'] ?? $post['user_name'] ?? ''));
            $lastName = strip_tags(trim($post['last_name'] ?? $post['user_surname'] ?? ''));
            $updateData['user_name'] = $firstName;
            if ($this->db->field_exists('user_surname', 'users')) {
                $updateData['user_surname'] = $lastName;
            }

        }

        if (isset($post['user_bill_tax_number'])) {
            $updateData['user_bill_tax_number'] = strip_tags(trim($post['user_bill_tax_number']));
        }
        if (isset($post['user_birthday']) && $this->db->field_exists('user_birthday', 'users')) {
            $updateData['user_birthday'] = strip_tags(trim($post['user_birthday']));
        }
		
		if (!empty($updateData)) {
			$this->db->where('user_id', $userID);
			$this->db->update('users', $updateData);
			
			$sessionData = $this->session->userdata('user');
            if (isset($updateData['user_name'])) {
			    $sessionData['user_name'] = $updateData['user_name'];
            }
            if (isset($updateData['user_surname'])) {
			    $sessionData['user_surname'] = $updateData['user_surname'];
            }
			$this->session->set_userdata('user', $sessionData);
			
			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Profil bilgileriniz güncellendi.']);
		}
		
		redirect(base_url($this->uri->segment(1) . '/profile/settings'));
	}

	public function requestNicknameChange()
	{
		$userID = (int) $this->data['user']['user_id'];
		$returnPage = $this->input->post('nickname_return_to') === 'referral' ? 'referral' : 'settings';
		$redirectTo = base_url($this->uri->segment(1) . '/profile/' . $returnPage);
		if (!$this->db->table_exists('nickname_change_requests')) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Nickname talep sistemi henüz etkinleştirilmedi.']);
			redirect($redirectTo);
		}
		$nickname = strtolower(trim((string) $this->input->post('user_nickname', true)));

		if (!preg_match('/^[a-z][a-z0-9_]{2,49}$/', $nickname)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Nickname 3-50 karakter olmalı, harfle başlamalı ve sadece harf, rakam veya alt çizgi içermelidir.']);
			redirect($redirectTo);
		}

		$currentUser = $this->db->select('user_nickname')->where('user_id', $userID)->get('users')->row();
		if (!$currentUser) {
			show_error('Kullanıcı bulunamadı.', 404);
		}
		if (strtolower((string) $currentUser->user_nickname) === $nickname) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Bu nickname zaten hesabınızda kullanılıyor.']);
			redirect($redirectTo);
		}

		$nicknameInUse = $this->db->where('LOWER(user_nickname) = ' . $this->db->escape($nickname), null, false)
			->where('user_id !=', $userID)
			->count_all_results('users');
		if ($nicknameInUse > 0) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Bu nickname zaten kullanılıyor.']);
			redirect($redirectTo);
		}
		$pendingNicknameInUse = $this->db->where('LOWER(nickname_request_value) = ' . $this->db->escape($nickname), null, false)
			->where('nickname_request_status', 2)
			->where('nickname_request_user_id !=', $userID)
			->count_all_results('nickname_change_requests');
		if ($pendingNicknameInUse > 0) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Bu nickname için bekleyen başka bir talep var.']);
			redirect($redirectTo);
		}
		if ($this->db->where(['nickname_request_user_id' => $userID, 'nickname_request_status' => 2])->count_all_results('nickname_change_requests') > 0) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Bekleyen bir nickname değişiklik talebiniz zaten var.']);
			redirect($redirectTo);
		}

		$inserted = $this->db->insert('nickname_change_requests', [
			'nickname_request_user_id' => $userID,
			'nickname_request_value' => $nickname,
			'nickname_request_status' => 2,
			'nickname_request_created_at' => date('Y-m-d H:i:s'),
		]);
		if (!$inserted) {
			log_message('error', 'Nickname change request insert failed for user ' . $userID . ': ' . json_encode($this->db->error()));
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Nickname talebiniz kaydedilemedi. Lütfen tekrar deneyin.']);
			redirect($redirectTo);
		}
		$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Nickname değişiklik talebiniz incelemeye gönderildi.']);
		redirect($redirectTo);
	}

	public function updateContact()
	{
		$post = $this->input->post(NULL, TRUE);
		$userID = $this->data['user']['user_id'];
		
		$updateData = [];
		if (isset($post['user_phone'])) $updateData['user_phone'] = normalizePhoneE164(strip_tags(trim($post['user_phone'])));
		if (isset($post['user_email'])) $updateData['user_email'] = strip_tags(trim($post['user_email']));
		
		if (!empty($updateData)) {
			$this->db->where('user_id', $userID);
			$this->db->update('users', $updateData);
			
			$sessionData = $this->session->userdata('user');
			if(isset($updateData['user_email'])) $sessionData['user_email'] = $updateData['user_email'];
			$this->session->set_userdata('user', $sessionData);
			
			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'İletişim bilgileriniz güncellendi.']);
		}
		
		redirect(base_url($this->uri->segment(1) . '/profile/settings'));
	}

	public function updateContactAjax()
	{
		$post = $this->input->post(NULL, TRUE);
		$userID = $this->data['user']['user_id'];
		
		$type = $post['type'] ?? '';
		if (!in_array($type, ['email', 'phone'], true)) {
			echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek.']);
			return;
		}
		if (!$this->throttleHit('contact_cd_' . $userID, 1, 60)
			|| !$this->throttleHit('contact_user_' . $userID, 8, 3600)
			|| !$this->throttleHit('contact_ip_' . getIp(), 20, 3600)) {
			echo json_encode(['status' => 'error', 'message' => 'Çok fazla kod istediniz. Lütfen daha sonra tekrar deneyin.']);
			return;
		}
		$userCheck = $this->db->get_where('users', ['user_id' => $userID])->row();

		if ($type === 'email') {
			$newEmail = strip_tags(trim($post['user_email'] ?? ''));
			if (empty($newEmail)) {
				echo json_encode(['status' => 'error', 'message' => 'Email adresi boş olamaz.']);
				return;
			}
			if ($newEmail === $userCheck->user_email && $userCheck->user_email_is_verified == 1) {
				echo json_encode(['status' => 'success', 'requires_verification' => false, 'message' => 'Email adresiniz zaten güncel ve onaylı.']);
				return;
			}
			
			// Update DB with temporary value
			$this->db->where('user_id', $userID);
			$this->db->update('users', ['user_email_temp' => $newEmail]);
			
			// Note: We no longer update session user_email here, only upon verification.
			
			// Cancel previous pending codes for email
			$this->db->where('2fa_user_id', $userID)->where('2fa_method', 1)->where('2fa_status', 0);
			$this->db->update('user_2fa_codes', ['2fa_status' => 2]);
			
			// Generate OTP
			$otpCode = random_int(100000, 999999);
			$this->db->insert('user_2fa_codes', [
				'2fa_user_id' => $userID,
				'2fa_otp_code' => $otpCode,
				'2fa_method' => 1,
				'2fa_status' => 0,
				'2fa_created_at' => date('Y-m-d H:i:s'),
				'2fa_expires_at' => date('Y-m-d H:i:s', strtotime('+3 minutes'))
			]);
			
			$subject = 'Playsel · E-Posta Doğrulama Kodun';
			$body = $this->load->view('emails/two_factor_auth', [
				'otp_code'       => $otpCode,
				'userName'       => $userCheck->user_name ?? '',
				'expiry_minutes' => 3,
				'badge_text'     => 'E-POSTA DOĞRULAMA',
				'title'          => 'E-posta Doğrulama Kodun',
				'intro_text'     => 'Yeni e-posta adresinizi onaylamak için aşağıdaki kodu doğrulama ekranına giriniz.'
			], true);
			sendEmail($newEmail, $subject, $body);
			
			echo json_encode(['status' => 'success', 'requires_verification' => true, 'message' => 'Doğrulama kodu e-posta adresinize gönderildi.']);
			return;
			
		} elseif ($type === 'phone') {
			$newPhone = normalizePhoneE164(strip_tags(trim($post['user_phone'] ?? '')));
			if (empty($newPhone)) {
				echo json_encode(['status' => 'error', 'message' => 'Telefon numarası boş olamaz.']);
				return;
			}
			if (!$this->throttleHit('contact_phone_' . $newPhone, 3, 3600)) {
				echo json_encode(['status' => 'error', 'message' => 'Bu numara için çok fazla kod istendi. Lütfen daha sonra tekrar deneyin.']);
				return;
			}
			if ($newPhone === $userCheck->user_phone && $userCheck->user_phone_is_verified == 1) {
				echo json_encode(['status' => 'success', 'requires_verification' => false, 'message' => 'Telefon numaranız zaten güncel ve onaylı.']);
				return;
			}
			
			// Update DB with temporary value
			$this->db->where('user_id', $userID);
			$this->db->update('users', ['user_phone_temp' => $newPhone]);
			
			// Cancel previous pending codes for phone
			$this->db->where('2fa_user_id', $userID)->where('2fa_method', 2)->where('2fa_status', 0);
			$this->db->update('user_2fa_codes', ['2fa_status' => 2]);
			
			// Generate OTP
			$otpCode = random_int(100000, 999999);
			$this->db->insert('user_2fa_codes', [
				'2fa_user_id' => $userID,
				'2fa_otp_code' => $otpCode,
				'2fa_method' => 2,
				'2fa_status' => 0,
				'2fa_created_at' => date('Y-m-d H:i:s'),
				'2fa_expires_at' => date('Y-m-d H:i:s', strtotime('+3 minutes'))
			]);
			
			sendSms2($newPhone, "Playsel Telefon Doğrulama Kodunuz: " . $otpCode);
			
			echo json_encode(['status' => 'success', 'requires_verification' => true, 'message' => 'Doğrulama kodu telefonunuza gönderildi.']);
			return;
		}

		echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek.']);
	}

	public function verifyContactOTP()
	{
		$post = $this->input->post(NULL, TRUE);
		$userID = $this->data['user']['user_id'];
		
		$otpType = $post['otp_type'] ?? '';
		$otpCode = trim($post['otp_code'] ?? '');
		
		if (empty($otpCode)) {
			echo json_encode(['status' => 'error', 'message' => 'Doğrulama kodu boş olamaz.']);
			return;
		}

		$where = [
			'2fa_user_id' => $userID,
			'2fa_otp_code'=> $otpCode,
			'2fa_status'  => 0,
			'2fa_expires_at >=' => date('Y-m-d H:i:s')
		];
		if ($otpType === 'email') $where['2fa_method'] = 1;
		else if ($otpType === 'phone') $where['2fa_method'] = 2;

		$verifyKey = 'contact_verify_' . (int)$userID;
		if (!$this->throttleHit($verifyKey, 5, 600)) {
			$this->db->where('2fa_user_id', $userID)->where('2fa_status', 0)->update('user_2fa_codes', ['2fa_status' => 2]);
			echo json_encode(['status' => 'error', 'message' => 'Çok fazla hatalı deneme. Lütfen yeni kod isteyin.']);
			return;
		}

		$checkCode = $this->db->get_where('user_2fa_codes', $where)->row();

		if (empty($checkCode)) {
			echo json_encode(['status' => 'error', 'message' => 'Girdiğiniz kod hatalı veya süresi dolmuş.']);
			return;
		}
		$this->throttleClear($verifyKey);

		// Update code as used
		$this->db->where('2fa_id', $checkCode->{'2fa_id'});
		$this->db->update('user_2fa_codes', ['2fa_status' => 1]);

		// Apply changes
		if ($otpType === 'email') {
			$u = $this->db->get_where('users', ['user_id' => $userID])->row();
			if (!empty($u->user_email_temp)) {
				$this->db->where('user_id', $userID);
				$this->db->update('users', [
					'user_email' => $u->user_email_temp, 
					'user_email_is_verified' => 1,
					'user_email_temp' => NULL
				]);
				
				// Update session
				$sessionData = $this->session->userdata('user');
				$sessionData['user_email'] = $u->user_email_temp;
				$this->session->set_userdata('user', $sessionData);
			}
		} elseif ($otpType === 'phone') {
			$u = $this->db->get_where('users', ['user_id' => $userID])->row();
			if (!empty($u->user_phone_temp)) {
				$this->db->where('user_id', $userID);
				$this->db->update('users', [
					'user_phone' => $u->user_phone_temp, 
					'user_phone_is_verified' => 1,
					'user_phone_temp' => NULL
				]);
				
				// Update session
				$sessionData = $this->session->userdata('user');
				$sessionData['user_phone'] = $u->user_phone_temp;
				$this->session->set_userdata('user', $sessionData);
			}
		} elseif ($otpType === 'security_2fa') {
			$pending = $this->session->userdata('pending_security_update');
			if (!empty($pending)) {
				$this->db->where('user_id', $userID);
				$this->db->update('users', $pending);
				$this->session->unset_userdata('pending_security_update');
			}
		}
		
		echo json_encode(['status' => 'success', 'message' => 'İşleminiz başarıyla onaylandı!']);
	}

	public function updateSecurity()
	{
		$post = $this->input->post(NULL, TRUE);
		$userID = $this->data['user']['user_id'];
		
		$updateData = [];
		if (isset($post['user_ip_whitelist'])) $updateData['user_ip_whitelist'] = $post['user_ip_whitelist'];
		if (isset($post['user_2fa_method'])) $updateData['user_2fa_method'] = (int)$post['user_2fa_method'];
		
		$this->db->where('user_id', $userID);
		$this->db->update('users', $updateData);
		
		$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Güvenlik ayarlarınız güncellendi.']);
		
		redirect(base_url($this->uri->segment(1) . '/profile/settings'));
	}

	public function updateSecurityRequest()
	{
		$post = $this->input->post(NULL, TRUE);
		$userID = $this->data['user']['user_id'];
		
		$newMethod = isset($post['user_2fa_method']) ? (int)$post['user_2fa_method'] : null;
		$whitelist = $post['user_ip_whitelist'] ?? null;
		
		$userCheck = $this->db->get_where('users', ['user_id' => $userID])->row();
		$currentMethod = (int)$userCheck->user_2fa_method;
		
		if ($newMethod === null || $newMethod === $currentMethod) {
			if ($whitelist !== null) {
				$this->db->where('user_id', $userID);
				$this->db->update('users', ['user_ip_whitelist' => $whitelist]);
			}
			echo json_encode(['status' => 'success', 'requires_verification' => false, 'message' => 'Güvenlik ayarlarınız güncellendi.']);
			return;
		}
		
		$verificationMethod = ($currentMethod !== 0) ? $currentMethod : $newMethod;
		
		if ($verificationMethod == 1 && empty($userCheck->user_email)) {
			echo json_encode(['status' => 'error', 'message' => 'Email adresiniz bulunmuyor. Lütfen önce email adresinizi ekleyin.']);
			return;
		}
		if ($verificationMethod == 2 && empty($userCheck->user_phone)) {
			echo json_encode(['status' => 'error', 'message' => 'Telefon numaranız bulunmuyor. Lütfen önce telefon numaranızı ekleyin.']);
			return;
		}
		
		$this->session->set_userdata('pending_security_update', [
			'user_2fa_method' => $newMethod,
			'user_ip_whitelist' => $whitelist
		]);
		
		$otpCode = rand(100000, 999999);
		$this->db->where('2fa_user_id', $userID)->where('2fa_method', $verificationMethod)->where('2fa_status', 0);
		$this->db->update('user_2fa_codes', ['2fa_status' => 2]);
		
		$this->db->insert('user_2fa_codes', [
			'2fa_user_id' => $userID,
			'2fa_otp_code' => $otpCode,
			'2fa_method' => $verificationMethod,
			'2fa_status' => 0,
			'2fa_created_at' => date('Y-m-d H:i:s'),
			'2fa_expires_at' => date('Y-m-d H:i:s', strtotime('+3 minutes'))
		]);
		
		if ($verificationMethod == 1) {
			$subject = 'Playsel · Güvenlik Doğrulama Kodun';
			$body = $this->load->view('emails/two_factor_auth', [
				'otp_code'       => $otpCode,
				'userName'       => $userCheck->user_name ?? '',
				'expiry_minutes' => 3,
				'badge_text'     => 'GÜVENLİK DOĞRULAMASI',
				'title'          => 'Güvenlik Doğrulama Kodun',
				'intro_text'     => 'Güvenlik ayarlarınızdaki değişikliği onaylamak için aşağıdaki kodu doğrulama ekranına giriniz.'
			], true);
			sendEmail($userCheck->user_email, $subject, $body);
		} else if ($verificationMethod == 2) {
			sendSms2($userCheck->user_phone, "Playsel Güvenlik Doğrulama Kodunuz: " . $otpCode);
		}
		
		echo json_encode(['status' => 'success', 'requires_verification' => true, 'otp_type' => 'security_2fa', 'message' => 'Değişikliği onaylamak için doğrulama kodu gönderildi.']);
		return;
	}

	public function updateBilling()
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		if (!$userID) {
			redirect(base_url($this->uri->segment(1) . '/login'));
			return;
		}

		$post = $this->input->post(NULL, TRUE);

		$addressID = (int)($post['user_addresses_id'] ?? 0);
		$addressTitle = strip_tags(trim($post['user_addresses_title'] ?? ''));

		$invoiceType = (($post['user_addresses_invoice_type'] ?? '') === '2' || ($post['invoice_type'] ?? '') === 'corporate') ? 2 : 1;
		$isTurkishCitizen = isset($post['user_addresses_is_turkish_citizen']) ? (int)$post['user_addresses_is_turkish_citizen'] : 1;
		$isTurkishCompany = isset($post['user_addresses_is_turkish_company']) ? (int)$post['user_addresses_is_turkish_company'] : 1;
		$isEInvoiceTaxpayer = isset($post['user_addresses_is_e_invoice_taxpayer']) ? (int)$post['user_addresses_is_e_invoice_taxpayer'] : 0;

		if (empty($addressTitle)) {
			$addressTitle = ($invoiceType === 2) ? 'Şirket Fatura Adresi' : 'Bireysel Fatura Adresi';
		}

		$fullName = strip_tags(trim($post['user_addresses_full_name'] ?? $post['user_bill_company_name'] ?? ''));
		$companyName = strip_tags(trim($post['user_addresses_company_name'] ?? $post['user_bill_company_name'] ?? ''));
		$taxOffice = strip_tags(trim($post['user_addresses_tax_office'] ?? $post['user_bill_tax_office'] ?? ''));
		$taxNumber = strip_tags(trim($post['user_addresses_tax_number'] ?? $post['user_bill_tax_number'] ?? ''));
		$addressPhone = strip_tags(trim($post['user_addresses_phone'] ?? ''));
		if ($addressPhone === '') {
			$addressPhone = $this->data['user']['user_phone'] ?? '';
		}

		$isTurkey = ($invoiceType === 1) ? ($isTurkishCitizen === 1) : ($isTurkishCompany === 1);

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

		// Doğrulamalar
		if ($invoiceType === 2) {
			if (empty($companyName)) {
				$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen firma unvanını giriniz.']);
				$this->session->set_flashdata('active_tab', 'billing');
				redirect(base_url($this->uri->segment(1) . '/profile/settings'));
				return;
			}
			if ($isTurkishCompany === 1) {
				if (empty($taxOffice)) {
					$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen vergi dairesini giriniz.']);
					$this->session->set_flashdata('active_tab', 'billing');
					redirect(base_url($this->uri->segment(1) . '/profile/settings'));
					return;
				}
				if (empty($taxNumber)) {
					$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen vergi numaranızı giriniz.']);
					$this->session->set_flashdata('active_tab', 'billing');
					redirect(base_url($this->uri->segment(1) . '/profile/settings'));
					return;
				}
			}
		} else {
			if (empty($fullName)) {
				$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen ad soyad bilginizi giriniz.']);
				$this->session->set_flashdata('active_tab', 'billing');
				redirect(base_url($this->uri->segment(1) . '/profile/settings'));
				return;
			}
			if ($isTurkishCitizen === 1 && empty($taxNumber)) {
				$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen T.C. kimlik numaranızı giriniz.']);
				$this->session->set_flashdata('active_tab', 'billing');
				redirect(base_url($this->uri->segment(1) . '/profile/settings'));
				return;
			}
		}

		if (empty($city) || empty($district)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen il ve ilçe alanlarını doldurunuz.']);
			$this->session->set_flashdata('active_tab', 'billing');
			redirect(base_url($this->uri->segment(1) . '/profile/settings'));
			return;
		}

		if (empty($address)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen açık adresinizi giriniz.']);
			$this->session->set_flashdata('active_tab', 'billing');
			redirect(base_url($this->uri->segment(1) . '/profile/settings'));
			return;
		}

		// Varsayılan adres durumu
		$activeCount = $this->db->where(['user_addresses_user_id' => $userID, 'user_addresses_is_deleted' => 0])->count_all_results('user_addresses');
		$isDefault = (!empty($post['user_addresses_is_default']) || $activeCount === 0) ? 1 : 0;

		if ($isDefault === 1) {
			$this->db->where('user_addresses_user_id', $userID)->update('user_addresses', ['user_addresses_is_default' => 0]);
		}

		// user_addresses tablosu için veri hazırla
		$addressData = [
			'user_addresses_user_id'               => $userID,
			'user_addresses_title'                 => $addressTitle,
			'user_addresses_invoice_type'          => $invoiceType,
			'user_addresses_full_name'             => ($invoiceType === 1) ? $fullName : '',
			'user_addresses_company_name'          => ($invoiceType === 2) ? $companyName : '',
			'user_addresses_phone'                 => $addressPhone,
			'user_addresses_tax_number'            => $taxNumber,
			'user_addresses_tax_office'            => ($invoiceType === 2) ? $taxOffice : '',
			'user_addresses_is_turkish_citizen'    => ($invoiceType === 1) ? $isTurkishCitizen : 1,
			'user_addresses_is_turkish_company'    => ($invoiceType === 2) ? $isTurkishCompany : 1,
			'user_addresses_is_e_invoice_taxpayer' => ($invoiceType === 2 && $isTurkishCompany === 1) ? $isEInvoiceTaxpayer : 0,
			'user_addresses_country'               => $country,
			'user_addresses_city'                  => $city,
			'user_addresses_district'              => $district,
			'user_addresses_postal_code'           => $postalCode,
			'user_addresses_address'               => $address,
			'user_addresses_is_default'            => $isDefault,
			'user_addresses_is_deleted'            => 0,
			'user_addresses_updated_at'            => date('Y-m-d H:i:s'),
		];

		if ($addressID > 0) {
			$existingAddress = $this->db->where([
				'user_addresses_id' => $addressID,
				'user_addresses_user_id' => $userID,
				'user_addresses_is_deleted' => 0
			])->get('user_addresses')->row();

			if ($existingAddress) {
				if ($isDefault === 0) {
					$addressData['user_addresses_is_default'] = $existingAddress->user_addresses_is_default;
				}
				$this->db->where('user_addresses_id', $addressID)->update('user_addresses', $addressData);
				$savedMessage = 'Fatura adresiniz güncellendi.';
			} else {
				$addressData['user_addresses_created_at'] = date('Y-m-d H:i:s');
				$this->db->insert('user_addresses', $addressData);
				$savedMessage = 'Yeni fatura adresi kaydedildi.';
			}
		} else {
			$addressData['user_addresses_created_at'] = date('Y-m-d H:i:s');
			$this->db->insert('user_addresses', $addressData);
			$savedMessage = 'Yeni fatura adresi eklendi.';
		}

		// Varsayılan ise users tablosunu senkronize tut
		if ($addressData['user_addresses_is_default'] == 1) {
			$userUpdate = [
				'user_bill_company_name'  => ($invoiceType === 2) ? $companyName : $fullName,
				'user_bill_tax_office'    => ($invoiceType === 2) ? $taxOffice : '',
				'user_bill_tax_number'    => $taxNumber,
				'user_bill_city'          => $city,
				'user_bill_district'      => $district,
				'user_bill_address'       => $address,
				'user_bill_e_invoice'     => ($invoiceType === 2 && $isTurkishCompany === 1) ? $isEInvoiceTaxpayer : 0,
				'user_is_turkish_company' => ($invoiceType === 2 && $isTurkishCompany === 1) ? 1 : 0
			];
			$this->db->where('user_id', $userID)->update('users', $userUpdate);
		}

		$this->session->set_flashdata('alert', ['type' => 'success', 'message' => $savedMessage]);
		$this->session->set_flashdata('active_tab', 'billing');

		$redirect = $this->input->post('redirect_url');
		if (!empty($redirect)) {
			redirect($redirect);
		} else {
			redirect(base_url($this->uri->segment(1) . '/profile/settings'));
		}
	}

	public function setDefaultAddress($addressID = 0)
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$addressID = (int)$addressID;
		if (!$userID || !$addressID) {
			redirect(base_url($this->uri->segment(1) . '/profile/settings'));
			return;
		}

		$targetAddress = $this->db->where([
			'user_addresses_id' => $addressID,
			'user_addresses_user_id' => $userID,
			'user_addresses_is_deleted' => 0
		])->get('user_addresses')->row();

		if ($targetAddress) {
			$this->db->where('user_addresses_user_id', $userID)->update('user_addresses', ['user_addresses_is_default' => 0]);
			$this->db->where('user_addresses_id', $addressID)->update('user_addresses', [
				'user_addresses_is_default' => 1,
				'user_addresses_updated_at' => date('Y-m-d H:i:s')
			]);

			// users tablosunu senkronize tut
			$userUpdate = [
				'user_bill_company_name'  => ($targetAddress->user_addresses_invoice_type == 2) ? $targetAddress->user_addresses_company_name : $targetAddress->user_addresses_full_name,
				'user_bill_tax_office'    => ($targetAddress->user_addresses_invoice_type == 2) ? $targetAddress->user_addresses_tax_office : '',
				'user_bill_tax_number'    => $targetAddress->user_addresses_tax_number,
				'user_bill_city'          => $targetAddress->user_addresses_city,
				'user_bill_district'      => $targetAddress->user_addresses_district,
				'user_bill_address'       => $targetAddress->user_addresses_address,
				'user_bill_e_invoice'     => $targetAddress->user_addresses_is_e_invoice_taxpayer,
				'user_is_turkish_company' => $targetAddress->user_addresses_is_turkish_company
			];
			$this->db->where('user_id', $userID)->update('users', $userUpdate);

			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Varsayılan fatura adresiniz güncellendi.']);
		} else {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Adres bulunamadı.']);
		}

		$this->session->set_flashdata('active_tab', 'billing');
		redirect(base_url($this->uri->segment(1) . '/profile/settings'));
	}

	public function deleteAddress($addressID = 0)
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$addressID = (int)$addressID;
		if (!$userID || !$addressID) {
			redirect(base_url($this->uri->segment(1) . '/profile/settings'));
			return;
		}

		$targetAddress = $this->db->where([
			'user_addresses_id' => $addressID,
			'user_addresses_user_id' => $userID,
			'user_addresses_is_deleted' => 0
		])->get('user_addresses')->row();

		if ($targetAddress) {
			$this->db->where('user_addresses_id', $addressID)->update('user_addresses', [
				'user_addresses_is_deleted' => 1,
				'user_addresses_is_default' => 0,
				'user_addresses_updated_at' => date('Y-m-d H:i:s')
			]);

			// Eğer silinen adres varsayılan idiyse, kalan en son adresi varsayılan yap
			if ($targetAddress->user_addresses_is_default == 1) {
				$nextAddress = $this->db->where([
					'user_addresses_user_id' => $userID,
					'user_addresses_is_deleted' => 0
				])->order_by('user_addresses_id', 'DESC')->get('user_addresses')->row();

				if ($nextAddress) {
					$this->db->where('user_addresses_id', $nextAddress->user_addresses_id)->update('user_addresses', ['user_addresses_is_default' => 1]);

					$userUpdate = [
						'user_bill_company_name'  => ($nextAddress->user_addresses_invoice_type == 2) ? $nextAddress->user_addresses_company_name : $nextAddress->user_addresses_full_name,
						'user_bill_tax_office'    => ($nextAddress->user_addresses_invoice_type == 2) ? $nextAddress->user_addresses_tax_office : '',
						'user_bill_tax_number'    => $nextAddress->user_addresses_tax_number,
						'user_bill_city'          => $nextAddress->user_addresses_city,
						'user_bill_district'      => $nextAddress->user_addresses_district,
						'user_bill_address'       => $nextAddress->user_addresses_address,
						'user_bill_e_invoice'     => $nextAddress->user_addresses_is_e_invoice_taxpayer,
						'user_is_turkish_company' => $nextAddress->user_addresses_is_turkish_company
					];
					$this->db->where('user_id', $userID)->update('users', $userUpdate);
				}
			}

			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Adres başarıyla silindi.']);
		} else {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Adres bulunamadı.']);
		}

		$this->session->set_flashdata('active_tab', 'billing');
		redirect(base_url($this->uri->segment(1) . '/profile/settings'));
	}

	public function requestReferenceChange()
	{
		$post = $this->input->post(NULL, TRUE);
		$userID = $this->data['user']['user_id'];
		
		$newRefNickname = strtolower(strip_tags(trim($post['new_reference_nickname'] ?? '')));
		if (!preg_match('/^[a-z][a-z0-9_]{2,49}$/', $newRefNickname)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Geçersiz nickname formatı.']);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$refUser = $this->db->where('LOWER(user_nickname) = ' . $this->db->escape($newRefNickname), null, false)
			->where('user_ref_permission', 1)
			->get('users')->row();
		
		if (!$refUser) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Geçersiz veya yetkisiz referans kodu.']);
			redirect($_SERVER['HTTP_REFERER']);
		}
		
		if ($refUser->user_id == $userID) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Kendi kendinize referans olamazsınız.']);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$currentUser = $this->db->get_where('users', ['user_id' => $userID])->row();
		$oldRefID = $currentUser->user_reference_id ?? 0;

		$this->db->insert('reference_change_requests', [
			'request_user_id' => $userID,
			'request_new_reference_id' => $refUser->user_id,
			'request_old_reference_id' => $oldRefID,
			'request_status' => 1,
			'request_created_at' => date('Y-m-d H:i:s')
		]);
		
		$this->db->where('user_id', $userID);
		$this->db->update('users', ['user_reference_id' => $refUser->user_id]);
		
		$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Referansınız başarıyla güncellendi.']);
		redirect($_SERVER['HTTP_REFERER']);
	}

	public function referral()
	{
		$data = $this->data;
		
		$data['user_details'] = $this->db->get_where('users', ['user_id' => $data['user']['user_id']])->row();
		$data['nickname_request'] = null;
		if ($this->db->table_exists('nickname_change_requests')) {
			$data['nickname_request'] = $this->db
				->where(['nickname_request_user_id' => $data['user']['user_id'], 'nickname_request_status' => 2])
				->order_by('nickname_request_id', 'DESC')
				->get('nickname_change_requests')->row();
		}
		if (!$data['user_details'] || $data['user_details']->user_ref_permission != 1) {
			show_404();
		}

		$data['pageTitle'] = $this->lang->line('profile_ref_menu');
		$data['title'] = $data['title'] . $this->lang->line('profile_ref_menu');
		$data['description'] = $this->lang->line('profile_ref_page_desc');

		$this->template->content->view('profile/referral', $data);
		$this->template->publish();
	}

	public function updateNickname()
	{
		$this->requestNicknameChange();
	}

	public function index()
	{
		$data = $this->data;
		$userID = $data['user']['user_id'] ?? 0;

		$data['pageTitle'] = $this->lang->line('profile_title');
		$data['title'] = $data['title'] . $this->lang->line('profile_title');
		$data['description'] = $this->lang->line('profile_description');

		// Son 5 sipariş (paket parçaları tek sipariş olarak gruplanır)
		$recentRows = $this->db->select('o.order_id, o.order_sale_price, o.order_status, o.order_created_at, o.order_payment_info, p.product_name, p.product_icon')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->where('o.order_reseller_id', $userID)
			->order_by('o.order_created_at', 'DESC')
			->limit(60)
			->get()
			->result();
		$recentGroups = [];
		$recentStatuses = [];
		$recentTotals = [];
		foreach ($recentRows as $recentRow) {
			$recentInfo = json_decode((string)$recentRow->order_payment_info, true);
			$recentSnapshot = is_array($recentInfo) ? ($recentInfo['pricing_snapshot'] ?? []) : [];
			$recentKey = trim((string)($recentSnapshot['bundle_reference'] ?? ''));
			$recentKey = $recentKey !== '' ? $recentKey : 'order_' . (int)$recentRow->order_id;
			if (!isset($recentGroups[$recentKey])) {
				if (!empty($recentSnapshot['bundle_product_name'])) {
					$recentRow->product_name = (string)$recentSnapshot['bundle_product_name'];
				}
				$recentGroups[$recentKey] = $recentRow;
				$recentStatuses[$recentKey] = [];
				$recentTotals[$recentKey] = 0.0;
			}
			$recentTotals[$recentKey] += (float)$recentRow->order_sale_price;
			$recentStatuses[$recentKey][] = (int)$recentRow->order_status;
		}
		foreach ($recentGroups as $recentKey => $recentGroup) {
			$recentGroup->order_sale_price = number_format($recentTotals[$recentKey], 2, '.', '');
			$recentGroup->order_status = $this->bundleAggregateStatus($recentStatuses[$recentKey]);
		}
		$data['recent_orders'] = array_slice(array_values($recentGroups), 0, 5);

		// Toplam sipariş sayısı
		$data['total_orders'] = $this->db->where('order_reseller_id', $userID)
		->count_all_results('orders');

		// Son 5 bakiye hareketi
		$data['recent_transactions'] = $this->db->select('
				user_balance.user_balance_id,
				user_balance.user_balance_amount,
				user_balance.user_balance_status,
				user_balance.user_balance_note,
				user_balance.user_balance_created_at,
				user_balance.user_balance_type,
				user_balance.user_balance_order_id,
				banks.bank_name
			')
			->from('user_balance')
			->join('banks', 'banks.bank_id = user_balance.user_balance_bank_id', 'left')
			->where('user_balance_user_id', $userID)
			->order_by('user_balance_created_at', 'DESC')
			->limit(5)
			->get()
			->result();

		// Objeleri view'ın beklediği formata dönüştür
		foreach ($data['recent_transactions'] as &$trx) {
			$trx->description = $this->balanceDescription($trx);
			$trx->created_at  = date('d.m.Y H:i', strtotime($trx->user_balance_created_at));
			$trx->amount      = number_format($trx->user_balance_amount, 2, ',', '.');
			$trx->type        = ((int)$trx->user_balance_status !== 3) ? 'in' : 'out';
		}
		unset($trx);

		$this->template->content->view('profile', $data);
		$this->template->publish();
	}

	private function balanceDescription($row)
	{
		$note = trim((string)($row->user_balance_note ?? ''));
		$noteData = json_decode($note, true);
		if (is_array($noteData)) {
			$note = trim((string)($noteData['message'] ?? ''));
		}
		$note = trim(preg_replace('/\s*\((Harcanabilir|Çekilebilir)\)\s*$/u', '', $note));
		// Yönetici tarafından Vomsis'ten doğrudan yüklenen havaleler: iç not ve gönderen bilgisi müşteriye gösterilmez.
		if (stripos($note, 'Vomsis') === 0) {
			$note = 'Havale ile bakiye yükleme';
		}
		$status = (int)$row->user_balance_status;
		$orderId = (int)($row->user_balance_order_id ?? 0);

		if ($status === 3 && $orderId > 0) {
			return 'Sipariş ödemesi #' . $orderId;
		}
		if ($status === 1 && $orderId > 0 && $note === 'Sipariş iadesi') {
			return 'Sipariş iadesi #' . $orderId;
		}
		if ($note === '' || $note === '-') {
			$note = $status === 3 ? 'Harcama' : 'Bakiye yükleme';
		}
		$suffix = [0 => ' (beklemede)', 2 => ' (iptal edildi)', 4 => ' (reddedildi)'];
		return $note . ($suffix[$status] ?? '');
	}

	public function settings()
	{
		$data = $this->data;

		$data['pageTitle'] = 'Ayarlarım';
		$data['title'] = $data['title'] . 'Ayarlarım';
		$data['description'] = 'Hesap bilgilerinizi güncelleyin ve güvenlik ayarlarınızı yönetin.';

		$data['user_details'] = $this->db->get_where('users', ['user_id' => $data['user']['user_id']])->row();

		// user_addresses tablosundan kullanıcının tüm aktif fatura adreslerini çek
		$data['user_addresses'] = $this->db->where([
			'user_addresses_user_id' => $data['user']['user_id'],
			'user_addresses_is_deleted' => 0
		])->order_by('user_addresses_is_default', 'DESC')->order_by('user_addresses_id', 'DESC')->get('user_addresses')->result();

		// Varsayılan veya ilk adres
		$data['user_address'] = !empty($data['user_addresses']) ? $data['user_addresses'][0] : null;

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

		$this->template->content->view('profile/settings', $data);
		$this->template->publish();
	}

	public function wallet()
	{
		$data = $this->data;
		// pre($data['user']);die;
		/*
			Array
			(
				[withdrawable_balance] => 0
				[spendable_balance] => 0
				[total_balance] => 0
			)
		*/
		$data['banks'] = $this->db->select('bank_id, bank_name, bank_owner, bank_iban, bank_account_number, bank_branch_code, bank_logo')
			->from('banks')
			->where(['bank_status' => 1])
			->order_by('bank_sort_id')
			->get()
			->result();
		$data['selectedBank'] = $data['banks'][0] ?? null;
		$data['pageTitle'] = 'Cüzdanım';
		$data['title'] = $data['title'] . 'Cüzdanım';
		$data['description'] = 'Cüzdanınızı yönetin ve bakiye işlemlerinizi takip edin.';
		$data['user_details'] = $this->db->get_where('users', ['user_id' => $data['user']['user_id']])->row();
		$bankReference = ensure_user_bank_reference($data['user']['user_id']);
		if ($bankReference !== '' && $data['user_details']) {
			$data['user_details']->user_bank_referance_number = $bankReference;
			$sessionUser = $this->session->userdata('user');
			if (is_array($sessionUser) && ($sessionUser['user_bank_referance_number'] ?? '') !== $bankReference) {
				$sessionUser['user_bank_referance_number'] = $bankReference;
				$this->session->set_userdata('user', $sessionUser);
			}
			$data['user']['user_bank_referance_number'] = $bankReference;
		}

		$this->template->content->view('profile/wallet', $data);
		$this->template->publish();
	}

	public function request_withdrawal()
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		if ($userID <= 0) {
			redirect('login');
			return;
		}

		$requestedAmount = (float)$this->input->post('amount');
		$bankAccountID = (int)$this->input->post('bank_account_id');
		if ($requestedAmount <= 0 || $bankAccountID <= 0) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Geçerli bir tutar ve banka hesabı seçiniz.']);
			redirect($this->uri->segment(1) . '/profile/withdrawals#tab-request');
			return;
		}

		$bankAccount = $this->db->get_where('user_bank_accounts', [
			'id' => $bankAccountID,
			'user_id' => $userID,
			'status' => 1,
		])->row();
		if (!$bankAccount) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Geçersiz banka hesabı.']);
			redirect($this->uri->segment(1) . '/profile/withdrawals#tab-accounts');
			return;
		}

		$lockName = $this->acquireBalanceLock($userID);
		if ($lockName === null) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'İşleminiz devam ediyor, lütfen tekrar deneyiniz.']);
			redirect($this->uri->segment(1) . '/profile/withdrawals#tab-request');
			return;
		}

		$this->load->model('Wallet_model');
		$balances = $this->Wallet_model->get_user_balances($userID);
		if ((float)($balances['withdrawable_balance'] ?? 0) < $requestedAmount) {
			$this->releaseBalanceLock($lockName);
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Çekilebilir bakiyeniz yetersiz.']);
			redirect($this->uri->segment(1) . '/profile/withdrawals#tab-request');
			return;
		}
		if (!$this->db->table_exists('withdrawals')) {
			$this->releaseBalanceLock($lockName);
			log_message('error', 'Withdrawal request table is missing.');
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Para çekim talebi şu anda oluşturulamadı.']);
			redirect($this->uri->segment(1) . '/profile/withdrawals#tab-request');
			return;
		}

		$this->db->trans_start();
		$this->db->insert('withdrawals', [
			'user_id' => $userID,
			'bank_account_id' => $bankAccountID,
			'amount' => round($requestedAmount, 2),
			'status' => 0,
			'created_at' => date('Y-m-d H:i:s'),
		]);
		$this->db->insert('user_balance', [
			'user_balance_user_id' => $userID,
			'user_balance_type' => 1,
			'user_balance_amount' => round($requestedAmount, 2),
			'user_balance_status' => 3,
			'user_balance_note' => 'Para Çekme Talebi',
			'user_balance_created_at' => date('Y-m-d H:i:s'),
		]);
		$this->db->trans_complete();
		$this->releaseBalanceLock($lockName);

		$this->session->set_flashdata('alert', [
			'type' => $this->db->trans_status() ? 'success' : 'error',
			'message' => $this->db->trans_status() ? 'Para çekim talebiniz oluşturuldu.' : 'Para çekim talebi oluşturulamadı.',
		]);
		redirect($this->uri->segment(1) . '/profile/withdrawals#tab-history');
	}

	// public function request_withdrawal()
	// {
	// 	$userID = $this->data['user']['user_id'] ?? 0;
	// 	if (!$userID) {
	// 		redirect('login');
	// 	}

	// 	$requestedAmount = (float)$this->input->post('amount');
	// 	$bankAccountID = (int)$this->input->post('bank_account_id');

	// 	if ($requestedAmount <= 0) {
	// 		$this->session->set_flashdata('error', 'Lütfen geçerli bir tutar giriniz.');
	// 		redirect('profile/withdrawals');
	// 		return;
	// 	}

	// 	if ($bankAccountID <= 0) {
	// 		$this->session->set_flashdata('error', 'Lütfen bir banka hesabı seçiniz.');
	// 		redirect('profile/withdrawals');
	// 		return;
	// 	}

	// 	$this->load->model('Wallet_model');
	// 	$balances = $this->Wallet_model->get_user_balances($userID);
	// 	$withdrawable = $balances['withdrawable_balance'] ?? 0;

	// 	if ($withdrawable >= $requestedAmount) {
	// 		$bankAccount = $this->db->get_where('user_bank_accounts', ['id' => $bankAccountID, 'user_id' => $userID])->row();
	// 		if (!$bankAccount) {
	// 			$this->session->set_flashdata('error', 'Geçersiz banka hesabı.');
	// 			redirect('profile/withdrawals');
	// 			return;
	// 		}

	// 		$this->db->trans_start();

	// 		if ($this->db->table_exists('withdrawals')) {
	// 			$this->db->insert('withdrawals', [
	// 				'user_id' => $userID,
	// 				'bank_account_id' => $bankAccountID,
	// 				'amount' => $requestedAmount,
	// 				'status' => 0, // Pending
	// 				'created_at' => date('Y-m-d H:i:s')
	// 			]);
				
	// 			// Bakiyeden düş
	// 			$this->db->insert('user_balance', [
	// 				'user_balance_user_id' => $userID,
	// 				'user_balance_type' => 1,
	// 				'user_balance_amount' => $requestedAmount,
	// 				'user_balance_status' => 3,
	// 				'user_balance_note' => 'Para Çekme Talebi',
	// 				'user_balance_created_at' => date('Y-m-d H:i:s')
	// 			]);
	// 		} else {
	// 			$this->db->insert('user_balance', [
	// 				'user_balance_user_id' => $userID,
	// 				'user_balance_type' => 1,
	// 				'user_balance_amount' => $requestedAmount,
	// 				'user_balance_status' => 3,
	// 				'user_balance_note' => 'Para Çekme Talebi (Banka ID: ' . $bankAccountID . ')',
	// 				'user_balance_created_at' => date('Y-m-d H:i:s')
	// 			]);
	// 		}

	// 		$this->db->trans_complete();

	// 		if ($this->db->trans_status() === FALSE) {
	// 			$this->session->set_flashdata('error', 'İşlem sırasında bir hata oluştu.');
	// 		} else {
	// 			$this->session->set_flashdata('success', 'Para çekme talebiniz başarıyla alındı.');
	// 		}
	// 	} else {
	// 		$this->session->set_flashdata('error', 'Yetersiz bakiye.');
	// 	}

	// 	redirect('profile/withdrawals');
	// }

	public function convertBalance()
	{
		$userID = $this->data['user']['user_id'] ?? 0;
		if (!$userID) {
			redirect('login');
		}

		$requestedAmount = (float)$this->input->post('amount');

		if ($requestedAmount <= 0) {
			$this->session->set_flashdata('error', 'Lütfen geçerli bir tutar giriniz.');
			redirect('profile/wallet');
			return;
		}

		$lockName = $this->acquireBalanceLock((int)$userID);
		if ($lockName === null) {
			$this->session->set_flashdata('error', 'İşleminiz devam ediyor, lütfen tekrar deneyiniz.');
			redirect('profile/wallet');
			return;
		}

		$this->load->model('Wallet_model');
		$balances = $this->Wallet_model->get_user_balances($userID);
		$withdrawable = $balances['withdrawable_balance'] ?? 0;

		if ($withdrawable >= $requestedAmount) {
			$this->db->trans_start();

			// 1. Çekilebilir bakiyeden düş
			$this->db->insert('user_balance', [
				'user_balance_user_id' => $userID,
				'user_balance_type' => 1,
				'user_balance_amount' => $requestedAmount,
				'user_balance_status' => 3,
				'user_balance_note' => 'Kullanılabilir bakiyeye aktarım (Dönüşüm)',
				'user_balance_created_at' => date('Y-m-d H:i:s')
			]);

			// 2. Harcanabilir bakiyeye ekle
			$this->db->insert('user_balance', [
				'user_balance_user_id' => $userID,
				'user_balance_type' => 2,
				'user_balance_amount' => $requestedAmount,
				'user_balance_status' => 1,
				'user_balance_note' => 'Çekilebilir bakiyeden aktarım (Dönüşüm)',
				'user_balance_created_at' => date('Y-m-d H:i:s')
			]);

			$this->db->trans_complete();
			$this->releaseBalanceLock($lockName);

			if ($this->db->trans_status() === FALSE) {
				$this->session->set_flashdata('error', 'Bakiye dönüştürme işlemi sırasında bir hata oluştu.');
			} else {
				$this->session->set_flashdata('success', 'Çekilebilir bakiyenizden ' . nFormat($requestedAmount) . ' ₺ başarıyla kullanılabilir bakiyeye dönüştürüldü.');
			}
		} else {
			$this->releaseBalanceLock($lockName);
			$this->session->set_flashdata('error', 'Yetersiz çekilebilir bakiye.');
		}

		redirect('profile/wallet');
	}

	private function acquireBalanceLock($userID)
	{
		$lockName = 'playsel_checkout_' . (int)$userID;
		$row = $this->db->query('SELECT GET_LOCK(?, 5) AS acquired', [$lockName])->row();
		return ($row && (int)$row->acquired === 1) ? $lockName : null;
	}

	private function releaseBalanceLock($lockName)
	{
		$this->db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
	}

	public function transactions()
	{
		$data = $this->data;
		$userID = $this->data['user']['user_id'];

		// 1. Filtreleme (GET parametreleri ile)
		$filter = $this->input->get('filter'); // 'week', 'month', 'year', 'all'
		$type = $this->input->get('type');     // 'in', 'out', 'all'

		$this->db->select('user_balance.*, banks.bank_name, banks.bank_iban, banks.bank_owner');
		$this->db->from('user_balance');
		$this->db->join('banks', 'banks.bank_id = user_balance.user_balance_bank_id', 'left');
		$this->db->where('user_balance_user_id', $userID);

		// Tip filtrelemesi (Yüklemeler / Harcamalar)
		if ($type == 'in') {
			$this->db->where('user_balance_status !=', 3); // Yüklemeler (0,1,2,4)
		} elseif ($type == 'out') {
			$this->db->where('user_balance_status', 3);    // Harcamalar (3)
		}

		// Filtreye göre tarih sorgusu ekle
		if ($filter == 'week') {
			$this->db->where('user_balance_created_at >=', date('Y-m-d H:i:s', strtotime('-1 week')));
		} elseif ($filter == 'month') {
			$this->db->where('user_balance_created_at >=', date('Y-m-d H:i:s', strtotime('-1 month')));
		} elseif ($filter == 'year') {
			$this->db->where('user_balance_created_at >=', date('Y-m-d H:i:s', strtotime('-1 year')));
		}

		// Clone db object to count rows for pagination
		$tempDb = clone $this->db;
		$total_rows = $tempDb->count_all_results();

		$this->load->library('pagination');
		$config['base_url'] = base_url($this->uri->segment(1) . '/profile/transactions');
		$config['total_rows'] = $total_rows;
		$config['per_page'] = 15;
		$config['page_query_string'] = TRUE;
		$config['reuse_query_string'] = TRUE;
		
		$config['full_tag_open'] = '<ul class="ps-pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li><a href="#" class="active">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['first_link'] = '&laquo;';
		$config['last_link'] = '&raquo;';
		$config['prev_link'] = '&lsaquo;';
		$config['next_link'] = '&rsaquo;';
		
		if (!isset($_GET['per_page'])) {
			$_GET['per_page'] = '0';
		}
		
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		$offset = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 0;

		// En yeni işlemler en üstte
		$this->db->order_by('user_balance_created_at', 'DESC');
		$this->db->limit($config['per_page'], $offset);
		$data['transactions'] = $this->db->get()->result();
		foreach ($data['transactions'] as &$transaction) {
			$note = trim((string)($transaction->user_balance_note ?? ''));
			$noteData = json_decode($note, true);
			$isStructuredNote = is_array($noteData);
			$transaction->display_note = $this->balanceDescription($transaction);
			$transaction->display_reference = '';
			$paymentMethod = strtolower(trim((string)($noteData['payment_method'] ?? '')));
			$transaction->display_method = $paymentMethod === 'cc'
				? 'Kredi kartı'
				: ($transaction->bank_name ?: 'Bakiye İşlemi');

			if ($isStructuredNote) {
				$providerPaymentId = (int)($noteData['provider_payment_id'] ?? 0);
				$providerReference = trim((string)($noteData['provider_reference'] ?? ''));
				if ($providerPaymentId > 0) {
					$transaction->display_reference = 'Ödeme No: ' . $providerPaymentId;
				} elseif ($providerReference !== '') {
					$transaction->display_reference = 'Referans: ' . $providerReference;
				}
			}
		}
		unset($transaction);

		// 2. İstatistikler (Üst kutular için)
		// Toplam Yüklenen Bakiye (type = 'in' veya 'deposit')
		// $data['totalDeposit'] = $this->db->select_sum('amount')->where(['user_id' => $userID, 'type' => 'in', 'status' => 'completed'])->get('transactions')->row()->amount ?? 0;

		// Toplam Harcanan Bakiye (type = 'out' veya 'withdraw' veya 'purchase')
		// $data['totalSpent'] = $this->db->select_sum('amount')->where(['user_id' => $userID, 'type' => 'out', 'status' => 'completed'])->get('transactions')->row()->amount ?? 0;

		// Sayfa başlıkları
		$data['active_filter'] = $filter ? $filter : 'all';
		$data['active_type'] = $type ? $type : 'all';
		$data['pageTitle'] = 'Bakiye Hareketlerim';
		$data['title'] = $data['title'] . 'Bakiye Hareketlerim';
		$data['description'] = 'Hesabınızdaki tüm bakiye yükleme ve harcama geçmişini görüntüleyin.';

		// View dosyasını yükle
		$this->template->content->view('profile/transactions', $data);
		$this->template->publish();
	}

	public function payments()
	{
		$data = $this->data;
		$userID = $this->data['user']['user_id'];

		$statusFilter = $this->input->get('status');
		$dateFilter = $this->input->get('date');

		$this->db->select('*');
		$this->db->from('user_balance'); // Ödemeleri tuttuğun tablonun adı 
		$this->db->where('user_balance_user_id', $userID);

		if ($statusFilter && $statusFilter != 'all') {
			if ($statusFilter == 'pending') {
				$this->db->where('user_balance_status', 0);
			} elseif ($statusFilter == 'completed') {
				$this->db->where('user_balance_status', 1);
			} elseif ($statusFilter == 'cancelled') {
				$this->db->where('user_balance_status', 2);
			} elseif ($statusFilter == 'rejected') {
				$this->db->where('user_balance_status', 4);
			}
		} else {
			$this->db->where_in('user_balance_status', [0, 1, 2, 4]);
		}
		$data['active_status'] = $statusFilter ? $statusFilter : 'all';

		if ($dateFilter) {
			if ($dateFilter == '1week') {
				$this->db->where('user_balance_created_at >=', date('Y-m-d H:i:s', strtotime('-1 week')));
			} elseif ($dateFilter == '1month') {
				$this->db->where('user_balance_created_at >=', date('Y-m-d H:i:s', strtotime('-1 month')));
			} elseif ($dateFilter == '3months') {
				$this->db->where('user_balance_created_at >=', date('Y-m-d H:i:s', strtotime('-3 months')));
			}
		}

		$this->db->join('banks', 'banks.bank_id = user_balance.user_balance_bank_id', 'left');

		$tempDb = clone $this->db;
		$total_rows = $tempDb->count_all_results();

		$this->load->library('pagination');
		$config['base_url'] = base_url($this->uri->segment(1) . '/profile/payments');
		$config['total_rows'] = $total_rows;
		$config['per_page'] = 15;
		$config['page_query_string'] = TRUE;
		$config['reuse_query_string'] = TRUE;
		
		$config['full_tag_open'] = '<ul class="ps-pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li><a href="#" class="active">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['first_link'] = '&laquo;';
		$config['last_link'] = '&raquo;';
		$config['prev_link'] = '&lsaquo;';
		$config['next_link'] = '&rsaquo;';
		
		if (!isset($_GET['per_page'])) {
			$_GET['per_page'] = '0';
		}

		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		$offset = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 0;

		$this->db->order_by('user_balance_created_at', 'DESC');
		$this->db->limit($config['per_page'], $offset);

		$data['payments'] = $this->db->get()->result();
		$data['legacy_payments'] = [];
		$data['legacy_cancellations'] = [];
		if ($this->db->query("SHOW TABLES LIKE 'legacy_payments'")->num_rows() > 0) {
			$data['legacy_payments'] = $this->db
				->where('target_user_id', $userID)
				->order_by('payment_date', 'DESC')
				->get('legacy_payments')
				->result();
		}
		if ($this->db->query("SHOW TABLES LIKE 'legacy_order_cancellations'")->num_rows() > 0) {
			$data['legacy_cancellations'] = $this->db
				->where('target_user_id', $userID)
				->order_by('refund_date', 'DESC')
				->get('legacy_order_cancellations')
				->result();
		}
		$data['pageTitle'] = 'Ödemelerim';
		$data['title'] = $data['title'] . 'Ödemelerim';
		$data['description'] = 'Ödeme işlemlerinizi görüntüleyin ve takip edin.';

		// View dosyasını yükle
		$this->template->content->view('profile/payments', $data);
		$this->template->publish();
	}

	public function add_bank_account()
	{
		$userID = $this->data['user']['user_id'];
		$post = $this->input->post(NULL, TRUE);

		// Tablo yoksa oluştur (sadece ilk sefer için)
		$sql = "CREATE TABLE IF NOT EXISTS `user_bank_accounts` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) NOT NULL,
		  `bank_name` varchar(255) NOT NULL,
		  `iban` varchar(50) NOT NULL,
		  `status` tinyint(4) NOT NULL DEFAULT 1,
		  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `user_id` (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		$this->db->query($sql);

		$raw_bank_name = trim($post['bank_name'] ?? '');

		// Bankayı veritabanındaki aktif bankalar tablosundan doğrula
		$matchedBank = $this->db
			->select('bank_name')
			->where('bank_status', 1)
			->where('bank_name', $raw_bank_name)
			->limit(1)
			->get('banks')
			->row();

		if (!$matchedBank) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen listeden geçerli bir banka seçin.']);
			redirect(base_url($this->uri->segment(1) . '/profile/withdrawals#tab-add-account'));
			return;
		}
		$bank_name = $matchedBank->bank_name;

		// IBAN sadece harf ve rakamlardan oluşabilir (tüm tırnak, boşluk ve özel karakterleri temizle)
		$iban = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($post['iban'] ?? '')));

		if (empty($iban)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen geçerli bir IBAN numarası girin.']);
			redirect(base_url($this->uri->segment(1) . '/profile/withdrawals#tab-add-account'));
			return;
		}

		$bank_account_id = (int)($post['bank_account_id'] ?? 0);

		if (!empty($bank_account_id)) {
			// Güncelleme işlemi
			$this->db->where('id', $bank_account_id);
			$this->db->where('user_id', $userID);
			$this->db->update('user_bank_accounts', [
				'bank_name' => $bank_name,
				'iban' => $iban
			]);
			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Banka hesabınız başarıyla güncellendi.']);
		} else {
			// Yeni ekleme
			$this->db->insert('user_bank_accounts', [
				'user_id' => $userID,
				'bank_name' => $bank_name,
				'iban' => $iban,
				'status' => 1,
				'created_at' => date('Y-m-d H:i:s')
			]);
			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Banka hesabınız başarıyla kaydedildi.']);
		}

		redirect(base_url($this->uri->segment(1) . '/profile/withdrawals#tab-accounts'));
	}

	public function delete_bank_account($id = null)
	{
		$userID = $this->data['user']['user_id'];
		
		if ($id) {
			$this->db->where('id', $id);
			$this->db->where('user_id', $userID);
			$this->db->delete('user_bank_accounts');
			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Banka hesabı silindi.']);
		}

		redirect($_SERVER['HTTP_REFERER'] ?? base_url($this->uri->segment(1) . '/profile/withdrawals#tab-accounts'));
	}

	public function withdrawals()
	{
		$data = $this->data;
		$userID = $this->data['user']['user_id'];

		// Tablo yoksa hata vermesin diye sessizce geç veya kontrol et
		$checkTable = $this->db->query("SHOW TABLES LIKE 'user_bank_accounts'")->num_rows();
		if ($checkTable > 0) {
			$data['bank_accounts'] = $this->db->get_where('user_bank_accounts', ['user_id' => $userID, 'status' => 1])->result();
		} else {
			$data['bank_accounts'] = [];
		}

		// Sistemde tanımlı aktif bankaları çek (banka hesabı ekleme/düzenleme listesi için)
		$data['system_banks'] = $this->db
			->where('bank_status', 1)
			->order_by('bank_sort_id', 'ASC')
			->order_by('bank_name', 'ASC')
			->get('banks')
			->result();

		// 1. Nakit Çekim Geçmişini Çek
		$this->db->order_by('user_balance_created_at', 'DESC');
		// $data['withdrawals'] = $this->db->get_where('withdrawals', ['user_id' => $userID])->result();
		$this->db->reset_query();
		$data['withdrawals'] = []; // Boş state görünmesi için şimdilik boş

		// Sayfa başlıkları
		$data['pageTitle'] = 'Nakit Çekimlerim';
		$data['title'] = $data['title'] . 'Nakit Çekimlerim';
		$data['description'] = 'Para çekme işlemlerinizi yönetin ve banka hesaplarınızı kontrol edin.';

		// View'ı yükle
		$this->template->content->view('profile/withdrawals', $data);
		$this->template->publish();
	}

	/**
	 * Siparişlerim Sayfasını Yükler (GET)
	 */
	public function orders()
	{
		$data = $this->data;
		$userID = $this->data['user']['user_id'];
		$data['cancellation_reasons'] = $this->db->table_exists('order_cancellation_reasons')
			? $this->db->where('reason_status', 1)->order_by('reason_sort', 'ASC')->get('order_cancellation_reasons')->result()
			: [];

		// Filtreleme Parametreleri
		$status_param = $this->input->get('status');
		$search_query = (string)$this->input->get('search');
		$dateFilter = $this->input->get('date');
		$dateRange = $this->input->get('date_range');

		// Whitelist kontrolü (XSS ve beklenmedik girdileri engellemek için)
		$allowed_statuses = ['all', 'pending', 'completed', 'cancelled'];
		if (!in_array($status_param, $allowed_statuses)) {
			$status_param = 'all';
		}

		$this->db->from('orders as o');
		$this->db->join('products as p', 'p.product_id = o.order_product_id', 'left');
		$this->db->where('o.order_reseller_id', $userID);

		// Durum Filtresi
		if ($status_param != 'all') {
			if ($status_param == 'pending') {
				$this->db->where_in('o.order_status', [0, 3, 4, 9, 11, 13, 14, 15]); // İşleniyor/Beklemede durumları
			} elseif ($status_param == 'completed') {
				$this->db->where('o.order_status', 1);
			} elseif ($status_param == 'cancelled') {
				$this->db->where_in('o.order_status', [2, 5, 12]);
			}
		}
		$data['active_status'] = $status_param;

		// Arama Filtresi (Sipariş ID veya Ürün Adı)
		if (!empty($search_query)) {
			$this->db->group_start();
			$this->db->like('p.product_name', $search_query);
			$this->db->or_like('o.order_id', $search_query);
			$this->db->group_end();
		}

		// Tarih Filtresi
		if ($dateFilter) {
			if ($dateFilter == '1week') {
				$this->db->where('o.order_created_at >=', date('Y-m-d H:i:s', strtotime('-1 week')));
			} elseif ($dateFilter == '1month') {
				$this->db->where('o.order_created_at >=', date('Y-m-d H:i:s', strtotime('-1 month')));
			} elseif ($dateFilter == '3months') {
				$this->db->where('o.order_created_at >=', date('Y-m-d H:i:s', strtotime('-3 months')));
			}
		} elseif (!empty($dateRange) && is_string($dateRange)) {
			$dates = explode(' to ', $dateRange);
			$dateValid = true;
			foreach ($dates as $dateItem) {
				$parsed = DateTime::createFromFormat('Y-m-d', $dateItem);
				if (!$parsed || $parsed->format('Y-m-d') !== $dateItem) {
					$dateValid = false;
				}
			}
			if (!$dateValid || count($dates) > 2) {
				$dates = [];
			}
			if (count($dates) == 2) {
				$this->db->where('o.order_created_at >=', $dates[0] . ' 00:00:00');
				$this->db->where('o.order_created_at <=', $dates[1] . ' 23:59:59');
			} elseif (count($dates) == 1) {
				$this->db->where('o.order_created_at >=', $dates[0] . ' 00:00:00');
				$this->db->where('o.order_created_at <=', $dates[0] . ' 23:59:59');
			}
		}

		// Bundle parçalarını tek bir kullanıcı siparişi olarak grupla.
		$this->db->select('o.*, p.product_name, p.product_image, p.product_icon');
		$this->db->order_by('o.order_created_at', 'DESC');
		$allOrders = $this->db->get()->result();
		$groupedOrders = [];
		$groupStatuses = [];
		foreach ($allOrders as $order) {
			$paymentInfo = json_decode((string)$order->order_payment_info, true);
			$snapshot = is_array($paymentInfo) ? ($paymentInfo['pricing_snapshot'] ?? []) : [];
			$groupKey = trim((string)($snapshot['bundle_reference'] ?? ''));
			$groupKey = $groupKey !== '' ? $groupKey : 'order_' . (int)$order->order_id;

			if (!isset($groupedOrders[$groupKey])) {
				$order->bundle_count = 0;
				$order->bundle_total = 0.0;
				$order->bundle_product_name = $snapshot['bundle_product_name'] ?? $order->product_name;
				$order->is_quantity_group = !empty($snapshot['is_quantity_group']);
				$groupedOrders[$groupKey] = $order;
			}

			$groupedOrders[$groupKey]->bundle_count++;
			$groupedOrders[$groupKey]->bundle_total += (float)$order->order_sale_price;
			$groupStatuses[$groupKey][] = (int)$order->order_status;
		}
		foreach ($groupedOrders as $groupKey => $groupHead) {
			if (($groupHead->bundle_count ?? 1) > 1) {
				$groupHead->order_status = $this->bundleAggregateStatus($groupStatuses[$groupKey]);
			}
		}
		$data['all_grouped_orders'] = array_values($groupedOrders);

		// Sayfalama grup bazında yapılır; parçalar ayrı satır olarak sayılmaz.
		$total_rows = count($data['all_grouped_orders']);

		$this->load->library('pagination');
		
		if (!isset($_GET['per_page'])) {
			$_GET['per_page'] = '0';
		}

		$config['base_url'] = base_url($this->uri->segment(1) . '/profile/orders');
		$config['total_rows'] = $total_rows;
		$config['per_page'] = 20;
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'per_page';
		$config['reuse_query_string'] = TRUE; // Mevcut GET parametrelerini korur

		// Pagination Stil Ayarları
		$config['full_tag_open'] = '<ul class="ps-pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li><a href="#" class="active">';
		$config['cur_tag_close'] = '</a></li>';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['first_link'] = '&laquo;';
		$config['last_link'] = '&raquo;';
		$config['prev_link'] = '&lsaquo;';
		$config['next_link'] = '&rsaquo;';
		
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		$offset = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 0;

		$data['orders'] = array_slice($data['all_grouped_orders'], $offset, $config['per_page']);
		unset($data['all_grouped_orders']);

		$data['legacy_orders'] = [];
		$legacyTableExists = $this->db->query("SHOW TABLES LIKE 'legacy_orders'")->num_rows() > 0;
		if ($legacyTableExists) {
			$this->db->select('legacy_order_key, source_order_no, source_order_id, order_date, payment_method, currency, status_text, total_amount, product_summary');
			$this->db->from('legacy_orders');
			$this->db->where('target_user_id', $userID);
			if (!empty($search_query)) {
				$this->db->group_start();
				$this->db->like('source_order_no', $search_query);
				$this->db->or_like('source_order_id', $search_query);
				$this->db->or_like('product_summary', $search_query);
				$this->db->group_end();
			}
			if ($dateFilter === '1week') {
				$this->db->where('order_date >=', date('Y-m-d H:i:s', strtotime('-1 week')));
			} elseif ($dateFilter === '1month') {
				$this->db->where('order_date >=', date('Y-m-d H:i:s', strtotime('-1 month')));
			} elseif ($dateFilter === '3months') {
				$this->db->where('order_date >=', date('Y-m-d H:i:s', strtotime('-3 months')));
			}
			$data['legacy_orders'] = $this->db->order_by('order_date', 'DESC')->get()->result();
			$legacyKeys = array_map(function ($order) {
				return $order->legacy_order_key;
			}, $data['legacy_orders']);
			$data['legacy_order_rows'] = [];
			if (!empty($legacyKeys)) {
				$this->db->where_in('legacy_order_key', $legacyKeys);
				$data['legacy_order_rows'] = $this->db->order_by('source_order_no', 'DESC')->get('legacy_order_rows')->result();
			}
		} else {
			$data['legacy_order_rows'] = [];
		}

		$order_ids = array_column($data['orders'], 'order_id');
		if (!empty($order_ids)) {
			$this->db->select('review_order_id');
			$this->db->from('reviews');
			$this->db->where_in('review_order_id', $order_ids);
			$this->db->where('review_user_id', $userID);
			$reviews = $this->db->get()->result_array();
			$data['reviewed_orders'] = array_column($reviews, 'review_order_id');
		} else {
			$data['reviewed_orders'] = [];
		}

		// Sayfa Başlıkları
		$data['pageTitle'] = 'Siparişlerim';
		$data['title'] = $data['title'] . 'Siparişlerim';
		$data['description'] = 'Tüm ürün satın alımlarınızı ve sipariş durumlarınızı buradan takip edin.';

		if ($this->input->is_ajax_request()) {
			echo $this->load->view('profile/orders_list_partial', $data, TRUE);
			exit;
		}

		$this->template->content->view('profile/orders', $data);
		$this->template->publish();
	}

	public function submit_review()
	{
		$userID = $this->data['user']['user_id'];
		$order_id = $this->input->post('order_id', TRUE);
		$rating = $this->input->post('rating', TRUE);
		$comment = $this->input->post('comment', TRUE);

		if (!$order_id || !$rating) {
			echo json_encode(['status' => false, 'message' => 'Lütfen yıldız verin.']);
			return;
		}

		$rating = (int) $rating;
		if ($rating < 1 || $rating > 5) {
			echo json_encode(['status' => false, 'message' => 'Geçersiz değerlendirme.']);
			return;
		}

		// Siparişi kontrol et
		$order = $this->db->get_where('orders', [
			'order_id' => $order_id,
			'order_reseller_id' => $userID,
			'order_status' => 1
		])->row();

		if (!$order) {
			echo json_encode(['status' => false, 'message' => 'Sipariş bulunamadı veya henüz tamamlanmamış.']);
			return;
		}

		// Zaten değerlendirilmiş mi?
		$existing_review = $this->db->get_where('reviews', [
			'review_order_id' => $order_id,
			'review_user_id' => $userID
		])->row();

		if ($existing_review) {
			echo json_encode(['status' => false, 'message' => 'Bu siparişi zaten değerlendirdiniz.']);
			return;
		}

		// Ürün ve Kategori bilgisini al (orders tablosunda category id yoksa diye product tablosundan alıyoruz)
		$product = $this->db->get_where('products', ['product_id' => $order->order_product_id])->row();
		$category_id = $product ? (isset($product->product_category_id) ? $product->product_category_id : (isset($product->category_id) ? $product->category_id : 0)) : 0;

		$data = [
			'review_user_id' => $userID,
			'review_order_id' => $order_id,
			'review_category_id' => $category_id,
			'review_product_id' => $order->order_product_id,
			'review_rating' => $rating,
			'review_comment' => $comment,
			'review_status' => 0, // Pending
			'review_created_at' => date('Y-m-d H:i:s'),
		];

		if ($this->db->insert('reviews', $data)) {
			echo json_encode(['status' => true, 'message' => 'Değerlendirmeniz başarıyla alındı ve onay için gönderildi.']);
		} else {
			echo json_encode(['status' => false, 'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.']);
		}
	}

	// Paket parçalarının durumlarını tek sipariş durumuna indirger; 100: kısmen tamamlandı / kısmen iptal.
	private function bundleAggregateStatus(array $statuses)
	{
		$statuses = array_values(array_unique(array_map('intval', $statuses)));
		if (count($statuses) === 1) {
			return $statuses[0];
		}
		$cancelled = [2, 5, 12];
		$hasDone = in_array(1, $statuses, true);
		$hasCancelled = (bool)array_intersect($cancelled, $statuses);
		$open = array_values(array_diff($statuses, [1], $cancelled));
		if ($hasCancelled && ($hasDone || !empty($open))) {
			return 100; // Kısmen iptal edildi (iade parça bazında)
		}
		if ($hasDone && !empty($open)) {
			return 102; // Kısmen teslim edildi, kalan parçalar işleniyor
		}
		if (!empty($open)) {
			return $open[0];
		}
		return $hasDone ? 1 : 2;
	}

	public function orderDetail($orderId = null)
	{
		$data = $this->data;
		$data['statusArray']['tr'] = [
			1 => ['text' => 'Başarılı', 'class' => 'badge text-success', 'icon' => 'fa-solid fa-circle-check text-success'],
			2 => ['text' => 'İptal Edildi', 'class' => 'badge text-danger', 'icon' => 'fa-solid fa-circle-xmark text-danger'],
			3 => ['text' => 'İşleniyor', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
			4 => ['text' => 'İşleniyor', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
		];
		$data['statusArray']['en'] = [
			1 => ['text' => 'Successful', 'class' => 'badge text-success', 'icon' => 'fa-solid fa-circle-check text-success'],
			2 => ['text' => 'Cancelled', 'class' => 'badge text-danger', 'icon' => 'fa-solid fa-circle-xmark text-danger'],
			3 => ['text' => 'Processing', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
			4 => ['text' => 'Processing', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
		];
		$data['statusArray']['de'] = [
			1 => ['text' => 'Erfolgreich', 'class' => 'badge text-success', 'icon' => 'fa-solid fa-circle-check text-success'],
			2 => ['text' => 'Storniert', 'class' => 'badge text-danger', 'icon' => 'fa-solid fa-circle-xmark text-danger'],
			3 => ['text' => 'In Bearbeitung', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
			4 => ['text' => 'In Bearbeitung', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
		];

		$data['statusArray']['ar'] = [
			1 => ['text' => 'ناجح', 'class' => 'badge text-success', 'icon' => 'fa-solid fa-circle-check text-success'],
			2 => ['text' => 'تم الإلغاء', 'class' => 'badge text-danger', 'icon' => 'fa-solid fa-circle-xmark text-danger'],
			3 => ['text' => 'قيد المعالجة', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
			4 => ['text' => 'قيد المعالجة', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
		];

		$data['statusArray']['ru'] = [
			1 => ['text' => 'Успешно', 'class' => 'badge text-success', 'icon' => 'fa-solid fa-circle-check text-success'],
			2 => ['text' => 'Отменено', 'class' => 'badge text-danger', 'icon' => 'fa-solid fa-circle-xmark text-danger'],
			3 => ['text' => 'В обработке', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
			4 => ['text' => 'В обработке', 'class' => 'badge text-warning', 'icon' => 'fa-solid fa-spinner fa-spin text-warning'],
		];
		$data['paymentArray']['tr'] = [
			2 => ['text' => 'Cüzdan', 'icon' => 'fa-solid fa-wallet text-white'],
			3 => ['text' => 'Havale', 'icon' => 'fa-solid fa-money-bill-transfer text-white'],
			4 => ['text' => 'Kredi Kartı', 'icon' => 'fa-regular fa-credit-card text-white'],

		];
		$data['paymentArray']['en'] = [
			2 => ['text' => 'Cüzdan', 'icon' => 'fa-solid fa-wallet text-white'],
			3 => ['text' => 'Bank Transfer', 'icon' => 'fa-solid fa-money-bill-transfer text-white'],
			4 => ['text' => 'CreditCard', 'icon' => 'fa-regular fa-credit-card text-white'],
		];
		$data['paymentArray']['de'] = [
			2 => ['text' => 'Cüzdan', 'icon' => 'fa-solid fa-wallet text-white'],
			3 => ['text' => 'Banküberweisung', 'icon' => 'fa-solid fa-money-bill-transfer text-white'],
			2 => ['text' => 'Kreditkarte', 'icon' => 'fa-regular fa-credit-card text-white'],
		];
		$data['paymentArray']['ar'] = [
			2 => ['text' => 'Cüzdan', 'icon' => 'fa-solid fa-wallet text-white'],
			3 => ['text' => 'تحويل بنكي', 'icon' => 'fa-solid fa-money-bill-transfer text-white'],
			4 => ['text' => 'بطاقة ائتمان', 'icon' => 'fa-regular fa-credit-card text-white'],
		];
		$data['paymentArray']['ru'] = [
			2 => ['text' => 'Cüzdan', 'icon' => 'fa-solid fa-wallet text-white'],
			3 => ['text' => 'Банковский перевод', 'icon' => 'fa-solid fa-money-bill-transfer text-white'],
			4 => ['text' => 'Кредитная карта', 'icon' => 'fa-regular fa-credit-card text-white'],
		];



		if (!$orderId) {
			show_404(); // ID yoksa 404 döndür
		}

		$order = $this->db->select('o.*, p.product_name, p.product_image, p.product_icon, b.bank_name, b.bank_iban, b.bank_owner')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('banks as b', 'b.bank_id = o.order_eft_bank_id', 'left')
			->where('o.order_id', $orderId)
			->where('o.order_reseller_id', $data['user']['user_id']) // sadece kendi siparişi
			->get()
			->row();

		if (!$order) {
			show_404(); // Sipariş bulunamadıysa yine 404
		}

		$order->bundle_parts = [];
		$order->bundle_count = 1;
		$order->bundle_total = (float)$order->order_sale_price;
		$paymentInfo = json_decode((string)$order->order_payment_info, true);
		$snapshot = is_array($paymentInfo) ? ($paymentInfo['pricing_snapshot'] ?? []) : [];
		$bundleReference = trim((string)($snapshot['bundle_reference'] ?? ''));

		if ($bundleReference !== '') {
			$bundleOrders = $this->db->select('o.*, p.product_name, p.product_image, p.product_icon, b.bank_name')
				->from('orders as o')
				->join('products as p', 'p.product_id = o.order_product_id', 'left')
				->join('banks as b', 'b.bank_id = o.order_eft_bank_id', 'left')
				->where('o.order_reseller_id', $data['user']['user_id'])
				->order_by('o.order_id', 'ASC')
				->get()->result();

			$order->bundle_total = 0.0;
			foreach ($bundleOrders as $bundleOrder) {
				$bundleInfo = json_decode((string)$bundleOrder->order_payment_info, true);
				$bundleSnapshot = is_array($bundleInfo) ? ($bundleInfo['pricing_snapshot'] ?? []) : [];
				if (($bundleSnapshot['bundle_reference'] ?? '') !== $bundleReference) {
					continue;
				}

				$order->bundle_parts[] = $bundleOrder;
				$order->bundle_total += (float)$bundleOrder->order_sale_price;
			}

			if (!empty($order->bundle_parts)) {
				$order->bundle_count = count($order->bundle_parts);
				$order->order_status = $this->bundleAggregateStatus(array_map(function ($part) {
					return (int)$part->order_status;
				}, $order->bundle_parts));
				$order->product_name = $snapshot['bundle_product_name'] ?? $order->product_name;
				$order->order_quantity = $order->bundle_count;
				$order->order_sale_price = $order->bundle_total;
				$order->is_quantity_group = !empty($snapshot['is_quantity_group']);
				if ($order->is_quantity_group) {
					$mergedVouchers = [];
					foreach ($order->bundle_parts as $part) {
						if (empty($part->order_voucher)) {
							continue;
						}
						$decodedVoucher = json_decode((string)$part->order_voucher, true);
						if (is_array($decodedVoucher)) {
							foreach ($decodedVoucher as $voucherKey => $voucherValue) {
								$mergedVouchers[] = is_numeric($voucherKey) ? $voucherValue : [$voucherKey => $voucherValue];
							}
						} else {
							$mergedVouchers[] = (string)$part->order_voucher;
						}
					}
					$order->order_voucher = !empty($mergedVouchers) ? json_encode($mergedVouchers, JSON_UNESCAPED_UNICODE) : null;
				}
			}
		} else {
			$order->bundle_parts[] = $order;
		}

		// Parça bazlı iade bilgisi: iptal olan parçaya bakiye iadesi yapıldı mı?
		$partIds = array_map(function ($part) {
			return (int)$part->order_id;
		}, $order->bundle_parts);
		$refundByOrder = [];
		if (!empty($partIds)) {
			$refundRows = $this->db->select('user_balance_order_id, SUM(user_balance_amount) AS refunded')
				->where_in('user_balance_order_id', $partIds)
				->where('user_balance_user_id', (int)$data['user']['user_id'])
				->where('user_balance_status', 1)
				->like('user_balance_note', 'iade')
				->group_by('user_balance_order_id')
				->get('user_balance')->result();
			foreach ($refundRows as $refundRow) {
				$refundByOrder[(int)$refundRow->user_balance_order_id] = (float)$refundRow->refunded;
			}
		}
		foreach ($order->bundle_parts as $part) {
			$part->refunded_amount = $refundByOrder[(int)$part->order_id] ?? 0.0;
		}

		// Burada decode işlemini ekle
		$order->order_require_data = !empty($order->order_require_data)
			? json_decode($order->order_require_data, true)
			: null;
		$orderData = !empty($order->order_data) ? json_decode($order->order_data, true) : [];
		$orderData = is_array($orderData) ? $orderData : [];
		$order->cancellation_reason = trim((string)($orderData['cancellation_reason'] ?? ''));
		$data['order'] = $order;

		$existingReview = $this->db->get_where('reviews', [
			'review_order_id' => $order->order_id,
			'review_user_id' => $data['user']['user_id']
		])->row();
		$data['has_reviewed'] = !empty($existingReview);

		$data['pageTitle'] = $this->lang->line('order_detail_title');
		$data['title'] = $data['title'] . $this->lang->line('order_detail_title');
		$data['description'] = $this->lang->line('order_detail_description');
		$this->template->content->view('order-detail', $data);
		$this->template->publish();
	}

	public function legacyOrderDetail($key = null)
	{
		$data = $this->data;
		$userID = (int)($data['user']['user_id'] ?? 0);
		$key = urldecode((string)$key);
		if ($userID <= 0 || $key === '' || $this->db->query("SHOW TABLES LIKE 'legacy_orders'")->num_rows() === 0) {
			show_404();
		}

		$order = $this->db->select('legacy_order_key, source_order_no, source_order_id, order_date, payment_method, currency, status_text, total_amount, product_summary')
			->get_where('legacy_orders', ['legacy_order_key' => $key, 'target_user_id' => $userID])
			->row();
		if (!$order) {
			show_404();
		}

		$data['order'] = $order;
		$data['rows'] = $this->db->select('product_name, quantity, unit_price, line_total, currency, status_text, completion_date')
			->where('legacy_order_key', $key)
			->get('legacy_order_rows')->result();
		$data['pageTitle'] = $this->lang->line('order_detail_title');
		$data['title'] = $data['title'] . $this->lang->line('order_detail_title');
		$data['description'] = $this->lang->line('order_detail_description');
		$this->template->content->view('legacy-order-detail', $data);
		$this->template->publish();
	}

	public function cancelOrder()
	{
		// Sipariş iptali yalnızca admin panelinden yapılır.
		return json(['status' => false, 'message' => 'Sipariş iptali müşteri tarafından yapılamaz.'], 403);
	}

	private function legacyCustomerCancelOrder()
	{
		$userID = (int)($this->data['user']['user_id'] ?? 0);
		$orderID = (int)$this->input->post('order_id');
		$reason = trim((string)$this->input->post('reason', true));
		$manualReason = trim((string)$this->input->post('manual_reason', true));
		if ($manualReason !== '') {
			$reason = $manualReason;
		}
		if ($reason === '' && $this->db->table_exists('order_cancellation_reasons')) {
			$selected = $this->db->get_where('order_cancellation_reasons', [
				'reason_id' => (int)$this->input->post('reason_id'),
				'reason_status' => 1,
			])->row();
			$reason = $selected ? $selected->reason_text : '';
		}
		if ($userID <= 0 || $orderID <= 0 || $reason === '') {
			return json(['status' => false, 'message' => 'Sipariş ve iptal sebebi zorunludur.'], 422);
		}

		$order = $this->db->get_where('orders', [
			'order_id' => $orderID,
			'order_reseller_id' => $userID,
		])->row();
		if (!$order) {
			return json(['status' => false, 'message' => 'Sipariş bulunamadı.'], 404);
		}
		if (!in_array((int)$order->order_status, [0, 11], true)) {
			return json(['status' => false, 'message' => 'Bu sipariş artık iptal edilemez.'], 422);
		}

		$orderData = json_decode((string)$order->order_data, true);
		$orderData = is_array($orderData) ? $orderData : [];
		$orderData['cancellation_reason'] = $reason;
		$snapshot = $orderData['pricing_snapshot'] ?? [];
		$refundAmount = (float)($snapshot['final_sale_price_tl'] ?? $order->order_sale_price_tl ?? 0);

		$this->db->trans_start();
		$this->db->where('order_id', $orderID)->where('order_status !=', 2)->update('orders', [
			'order_status' => 2,
			'order_data' => json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		]);
		$refundExists = $this->db->where('user_balance_order_id', $orderID)
			->where('user_balance_status', 1)
			->where('user_balance_note', 'Sipariş iadesi')
			->count_all_results('user_balance') > 0;
		// Yalnızca ödemesi alınmış (0) siparişe iade yapılır; 11: Ödeme Bekleniyor tahsilat içermez.
		if (!$refundExists && $refundAmount > 0 && (int)$order->order_status === 0) {
			$this->db->insert('user_balance', [
				'user_balance_user_id' => $userID,
				'user_balance_type' => 2,
				'user_balance_amount' => $refundAmount,
				'user_balance_status' => 1,
				'user_balance_order_id' => $orderID,
				'user_balance_note' => 'Sipariş iadesi',
				'user_balance_created_at' => date('Y-m-d H:i:s'),
				'user_balance_payment_date' => date('Y-m-d H:i:s'),
			]);
		}
		$this->db->trans_complete();

		return json([
			'status' => $this->db->trans_status(),
			'message' => $this->db->trans_status() ? 'Siparişiniz iptal edildi.' : 'İptal işlemi başarısız oldu.',
		]);
	}


	public function streamer()
	{
		$data = $this->data;
		$userID = $this->data['user']['user_id'];

		$dbUser = $this->db->select('user_streamer_nick, user_streamer_url, user_streamer_status')
						   ->from('users')
						   ->where('user_id', $userID)
						   ->get()
						   ->row_array();
		if ($dbUser) {
			$data['user']['user_streamer_nick'] = $dbUser['user_streamer_nick'];
			$data['user']['user_streamer_url'] = $dbUser['user_streamer_url'];
			$data['user']['user_streamer_status'] = $dbUser['user_streamer_status'];

			// Session güncellemesi yapalım ki sayfa yenilendiğinde veriler taze kalsın
			$sessionData = $this->session->userdata('user');
			$sessionData['user_streamer_nick'] = $dbUser['user_streamer_nick'];
			$sessionData['user_streamer_url'] = $dbUser['user_streamer_url'];
			$sessionData['user_streamer_status'] = $dbUser['user_streamer_status'];
			$this->session->set_userdata('user', $sessionData);
		}


		if ($this->input->server('REQUEST_METHOD') === 'POST') {
			$nick = strip_tags(trim($this->input->post('user_streamer_nick', TRUE)));
			$url = strip_tags(trim($this->input->post('user_streamer_url', TRUE)));

			if (!empty($nick) && !empty($url)) {
				$this->db->where('user_id', $userID);
				$this->db->update('users', [
					'user_streamer_nick' => $nick,
					'user_streamer_url' => $url,
					'user_streamer_status' => 2 // Pending
				]);
				
				// Update session
				$sessionData = $this->session->userdata('user');
				$sessionData['user_streamer_nick'] = $nick;
				$sessionData['user_streamer_url'] = $url;
				$sessionData['user_streamer_status'] = 2;
				$this->session->set_userdata('user', $sessionData);
				$data['user'] = $sessionData;

				$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Başvurunuz başarıyla alındı. Onay sürecini bekleyin.']);
			} else {
				$this->session->set_flashdata('alert', ['type' => 'error', 'message' => 'Lütfen tüm alanları doldurun.']);
			}
			redirect(base_url($this->uri->segment(1) . '/profile/streamer'));
		}

		$data['pageTitle'] = 'Yayıncı Başvurusu';
		$data['title'] = $data['title'] . 'Yayıncı Başvurusu';
		$data['description'] = 'Yayıncı programımıza başvurun.';

		$this->template->content->view('profile/streamer', $data);
		$this->template->publish();
	}

	public function changePassword()
	{
		$data = $this->data;
		$data['pageTitle'] = $this->lang->line('change_password_title');
		$data['title'] = $data['title'] . $this->lang->line('change_password_title');
		$data['description'] = $this->lang->line('change_password_description');
		$this->template->content->view('change-password', $data);
		$this->template->publish();
	}

	public function changePasswordRequest()
	{
		$post = $this->input->post();
		if (empty($post['current_password']) || empty($post['new_password']) || empty($post['confirm_password'])) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('fill_all_fields')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}
		$data = $this->data;
		$user = $this->db->get_where('users', ['user_id' => $data['user']['user_id']])->row();
		if (empty($user)) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => $this->lang->line('user_notfound')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}
		if (!password_verify($post['current_password'], $user->user_password)) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' =>  $this->lang->line('profile_change_password_error')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}
		if ($post['new_password'] != $post['confirm_password']) {
			$this->session->set_flashdata('alert', [
				'type' => 'error',
				'message' => ('password_unmatch')
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}
		// Update password
		$updateData = [
			'user_password' => password_hash($post['new_password'], PASSWORD_BCRYPT)
		];
		$this->db->where('user_id', $data['user']['user_id']);
		$this->db->update('users', $updateData);
		$this->session->set_flashdata('alert', [
			'type' => 'success',
			'message' => $this->lang->line('profile_change_password_success')
		]);
		redirect($_SERVER['HTTP_REFERER']);
	}

	public function changeRegion()
	{
		if ($this->input->method() !== 'post') {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => false, 'message' => 'Invalid request method.']));
			return;
		}
		$countryCode = strtoupper(trim($this->input->post('country', true)));
		$langCode    = strtolower(trim($this->input->post('lang', true)));

		if (empty($countryCode) || empty($langCode)) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => false, 'message' => 'Ülke ve dil seçimi zorunludur.']));
			return;
		}

		$locale = $this->db->get_where('locales', [
			'locale_code'      => $countryCode,
			'locale_lang_code' => $langCode
		])->row();

		if (empty($locale)) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => false, 'message' => 'Geçersiz bölge/dil seçimi.']));
			return;
		}

		$newUri = $langCode . '-' . $countryCode;

		$userData = $this->session->userdata('user');
		if (!empty($userData)) {
			$userId = $userData['user_id'];

			// DB güncelle
			$this->db->where('user_id', $userId);
			$this->db->update('users', [
				'user_country' => $countryCode,
				'user_lang'    => $langCode
			]);

			// Session güncelle
			$userData['user_country'] = $countryCode;
			$userData['user_lang']    = $langCode;
			$this->session->set_userdata('user', $userData);
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status'  => true,
				'message' => 'Bölge başarıyla güncellendi.',
				'newUri'  => $newUri
			]));
	}

	public function tickets()
	{
		$data = $this->data;
		$userID = $this->data['user']['user_id'];
		
		$this->db->where('tickets_user_id', $userID);
		$this->db->order_by('tickets_updated_at', 'DESC');
		$data['tickets'] = $this->db->get('tickets')->result();

		$data['pageTitle'] = 'Destek Taleplerim';
		$data['title'] = $data['title'] . 'Destek Taleplerim';

		$this->template->content->view('profile/tickets', $data);
		$this->template->publish();
	}

	private function _validateTicketAttachment($attachment)
	{
		if (empty($attachment) || !is_string($attachment)) {
			return null;
		}
		$attachment = trim($attachment);
		// Yalnizca raster resim formatlarina (png, jpeg, jpg, webp, gif) izin ver. SVG ve scriptler reddedilir.
		if (preg_match('#^data:image/(png|jpeg|jpg|webp|gif);base64,([A-Za-z0-9+/=]+)$#i', $attachment)) {
			// Maksimum 7MB base64 boyutu (~5MB resim dosyasi)
			if (strlen($attachment) <= 7 * 1024 * 1024) {
				return $attachment;
			}
		}
		return null;
	}

	public function createTicket()
	{
		$userID = $this->data['user']['user_id'];
		$topic = strip_tags(trim($this->input->post('topic', TRUE)));
		$subject = strip_tags(trim($this->input->post('subject', TRUE)));
		$message = strip_tags(trim($this->input->post('message', TRUE)));
		$order_id = $this->input->post('order_id', TRUE);
		$attachment = $this->_validateTicketAttachment($this->input->post('attachment', FALSE));

		if ($order_id) {
			$order = $this->db->get_where('orders', ['order_id' => $order_id, 'order_reseller_id' => $userID])->row();
			if ($order) {
				$subject = "Sipariş #" . $order->order_id . " Hakkında";
			} else {
				echo json_encode(['status' => false, 'message' => 'Geçersiz sipariş ID.']);
				return;
			}
		}

		if (!$topic || !$subject || !$message) {
			echo json_encode(['status' => false, 'message' => 'Lütfen tüm alanları doldurun.']);
			return;
		}

		$this->db->trans_start();

		$ticketData = [
			'tickets_user_id' => $userID,
			'tickets_topic' => $topic,
			'tickets_subject' => $subject,
			'tickets_status' => 0, // Open
			'tickets_created_at' => date('Y-m-d H:i:s'),
			'tickets_updated_at' => date('Y-m-d H:i:s')
		];
		if ($this->db->field_exists('tickets_order_id', 'tickets')) {
			$ticketData['tickets_order_id'] = $order_id ? (int)$order_id : null;
		}
		$this->db->insert('tickets', $ticketData);
		$ticket_id = $this->db->insert_id();

		$messageData = [
			'ticket_messages_ticket_id' => $ticket_id,
			'ticket_messages_user_id' => $userID,
			'ticket_messages_message' => $message,
			'ticket_messages_attachment' => $attachment,
			'ticket_messages_created_at' => date('Y-m-d H:i:s')
		];
		$this->db->insert('ticket_messages', $messageData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			echo json_encode(['status' => false, 'message' => 'Bir hata oluştu, lütfen tekrar deneyin.']);
		} else {
			echo json_encode(['status' => true, 'message' => 'Destek talebiniz başarıyla oluşturuldu.']);
		}
	}

	public function ticketDetail($id)
	{
		$data = $this->data;
		$userID = $this->data['user']['user_id'];

		$ticket = $this->db->get_where('tickets', ['tickets_id' => $id, 'tickets_user_id' => $userID])->row();
		if (!$ticket) {
			redirect(base_url($this->uri->segment(1) . '/profile/tickets'));
		}

		$this->db->where('ticket_messages_ticket_id', $id);
		$this->db->order_by('ticket_messages_created_at', 'ASC');
		$messages = $this->db->get('ticket_messages')->result();

		$data['ticket'] = $ticket;
		$data['messages'] = $messages;
		$data['pageTitle'] = 'Talep Detayı';
		$data['title'] = $data['title'] . 'Talep Detayı';

		$this->template->content->view('profile/ticket-detail', $data);
		$this->template->publish();
	}

	public function replyTicket()
	{
		$userID = $this->data['user']['user_id'];
		$ticket_id = $this->input->post('ticket_id', TRUE);
		$message = strip_tags(trim($this->input->post('message', TRUE)));
		$attachment = $this->_validateTicketAttachment($this->input->post('attachment', FALSE));

		if (!$message && !$attachment) {
			echo json_encode(['status' => false, 'message' => 'Lütfen bir mesaj yazın veya resim ekleyin.']);
			return;
		}

		$ticket = $this->db->get_where('tickets', ['tickets_id' => $ticket_id, 'tickets_user_id' => $userID])->row();
		if (!$ticket) {
			echo json_encode(['status' => false, 'message' => 'Talep bulunamadı.']);
			return;
		}

		if ($ticket->tickets_status == 2) {
			echo json_encode(['status' => false, 'message' => 'Bu talep kapatılmış, cevap yazılamaz.']);
			return;
		}

		$this->db->trans_start();

		$messageData = [
			'ticket_messages_ticket_id' => $ticket_id,
			'ticket_messages_user_id' => $userID,
			'ticket_messages_message' => $message,
			'ticket_messages_attachment' => $attachment,
			'ticket_messages_created_at' => date('Y-m-d H:i:s')
		];
		$this->db->insert('ticket_messages', $messageData);

		$this->db->where('tickets_id', $ticket_id);
		$this->db->update('tickets', [
			'tickets_status' => 0,
			'tickets_updated_at' => date('Y-m-d H:i:s')
		]);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			echo json_encode(['status' => false, 'message' => 'Bir hata oluştu.']);
		} else {
			echo json_encode(['status' => true, 'message' => 'Cevabınız gönderildi.']);
		}
	}
	
	public function orderContract($orderID, $type = 'distance-sales-agreement')
	{
		$orderID = (int)$orderID;
		$userID = $this->data['user']['user_id'] ?? 0;
		if (!$userID) {
			redirect('login');
		}

		$order = $this->db->select('o.*, p.product_name, c.category_title, u.user_name, u.user_surname, u.user_email, u.user_phone, u.user_bill_company_name, u.user_bill_address, u.user_bill_city, u.user_bill_district, u.user_bill_tax_office, u.user_bill_tax_number')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('categories as c', 'c.category_id = p.product_category_id', 'left')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
			->where('o.order_id', $orderID)
			->where('o.order_reseller_id', $userID)
			->get()->row();

		if (empty($order)) {
			show_404();
		}

		$viewPath = APPPATH . 'modules/homee/views/static_pages/' . $type . '.php';
		if (!file_exists($viewPath)) {
			show_404();
		}

		$content = file_get_contents($viewPath);

		$buyerFullName = trim(($order->user_name ?? '') . ' ' . ($order->user_surname ?? ''));
		if ($buyerFullName === '') {
			$buyerFullName = !empty($order->user_bill_company_name) ? trim($order->user_bill_company_name) : '-';
		}

		// Basic replacements
		$replacements = [
			'{{alici_ad_soyad}}' => $buyerFullName,
			'{{alici_tckn_vkn}}' => !empty($order->user_bill_tax_number) ? trim($order->user_bill_tax_number) : '-',
			'{{alici_fatura_adresi}}' => !empty($order->user_bill_address) ? trim($order->user_bill_address) : '-',
			'{{alici_telefon}}' => !empty($order->user_phone) ? $order->user_phone : '-',
			'{{alici_eposta}}' => !empty($order->user_email) ? $order->user_email : '-',
			'{{islem_ip_adresi}}' => !empty($order->order_user_ip) ? $order->order_user_ip : '-',
			'{{onay_tarih_saati}}' => date('d.m.Y H:i:s', strtotime($order->order_created_at))
		];

		$content = str_replace(array_keys($replacements), array_values($replacements), $content);

		// Format numbers
		$displayCurrency = $order->order_sale_price_currency === 'TRY' ? 'TL' : $order->order_sale_price_currency;

		$paymentInfo = json_decode((string)$order->order_payment_info, true);
		$snapshot = is_array($paymentInfo) ? ($paymentInfo['pricing_snapshot'] ?? []) : [];
		$bundleReference = trim((string)($snapshot['bundle_reference'] ?? ''));
		$contractRows = [$order];
		if ($bundleReference !== '') {
			$candidates = $this->db->select('o.*, p.product_name, c.category_title')
				->from('orders as o')
				->join('products as p', 'p.product_id = o.order_product_id', 'left')
				->join('categories as c', 'c.category_id = p.product_category_id', 'left')
				->where('o.order_reseller_id', $userID)
				->where('o.order_created_at', $order->order_created_at)
				->order_by('o.order_id', 'ASC')
				->get()->result();
			$matched = [];
			foreach ($candidates as $candidate) {
				$candidateInfo = json_decode((string)$candidate->order_payment_info, true);
				$candidateSnapshot = is_array($candidateInfo) ? ($candidateInfo['pricing_snapshot'] ?? []) : [];
				if (($candidateSnapshot['bundle_reference'] ?? '') === $bundleReference) {
					$matched[] = $candidate;
				}
			}
			if (!empty($matched)) {
				$contractRows = $matched;
			}
		}

		$trHtml = '';
		$totalSale = 0.0;
		foreach ($contractRows as $contractRow) {
			$rowPrice = is_numeric($contractRow->order_sale_price) ? (float)$contractRow->order_sale_price : 0;
			$rowQty = max(0, (int)$contractRow->order_quantity);
			$rowTotal = $rowPrice * $rowQty;
			$totalSale += $rowTotal;
			$trHtml .= '<tr>
			<td>' . html_escape($contractRow->product_name . ' - ' . $contractRow->category_title) . '</td>
			<td>' . $rowQty . '</td>
			<td>' . number_format($rowPrice, 2, ',', '.') . ' ' . $displayCurrency . '</td>
			<td>' . number_format($rowTotal, 2, ',', '.') . ' ' . $displayCurrency . '</td>
		</tr>';
		}
		$totalSaleFormatted = number_format($totalSale, 2, ',', '.') . ' ' . $displayCurrency;

		// Replace the tr loop HTML
		$patternTr = '/<!-- YAZILIMCI NOTU:.*?-->.*?<!-- DÖNGÜ SONU -->/s';
		$content = preg_replace_callback($patternTr, function () use ($trHtml) {
			return $trHtml;
		}, $content);

		// Replace genel toplam
		$content = preg_replace('/(<td colspan="3" style="text-align: right;" class="bold">Genel Toplam \(KDV Dahil\):<\/td>\s*<td class="bold">)(.*?)(<\/td>)/s', '${1}' . $totalSaleFormatted . '${3}', $content);

		// Replace ödeme yöntemi
		$paymentMethodLabels = [2 => 'Sistem Bakiyesi', 3 => 'Banka Havalesi', 4 => 'Kredi Kartı', 5 => 'Mobil Ödeme'];
		$paymentMethod = $paymentMethodLabels[(int)$order->order_payment_type]
			?? (!empty($order->order_payment_method) ? $order->order_payment_method : 'Sistem Bakiyesi');
		$content = preg_replace('/(<td colspan="3" style="text-align: right;" class="bold">Ödeme Yöntemi:<\/td>\s*<td class="bold">)(.*?)(<\/td>)/s', '${1}' . html_escape($paymentMethod) . '${3}', $content);

		$data['content'] = $content;
		$data['title'] = $type == 'distance-sales-agreement' ? 'Mesafeli Satış Sözleşmesi' : 'İade Politikası';
		
		$this->load->view('contract-view', $data);
	}
}
