<?php defined('BASEPATH') or exit('No direct script access allowed');

class Fatura extends MY_Controller
{
	public $data = array();
	private $invoiceBundleValidationCache = [];

	public function __construct()
	{
		userCheck();
		parent::__construct();
		$this->load->model('VtModel');
		$this->data['locales'] = $this->locales();
	}

	public function fatura_kesim()
	{
		$data = $this->data;
		$data['title'] = 'Fatura Adayları';
		$data['description'] = 'Günlük fatura adaylarını kontrol edin.';
		$timezone = new DateTimeZone('Europe/Istanbul');
		$yesterday = (new DateTimeImmutable('yesterday', $timezone))->format('Y-m-d');
		$today = (new DateTimeImmutable('today', $timezone))->format('Y-m-d');
		$data['automation_date'] = (string)$this->input->post('automation_date') ?: $yesterday;
		$data['daily_preview'] = null;
		$data['error'] = null;
		$data['adminActionCsrfToken'] = $this->adminActionCsrfToken();

		if ($this->input->method(true) === 'POST') {
			$action = (string)$this->input->post('invoice_action');
			$targetDate = (string)$this->input->post('automation_date');
			$data['automation_date'] = $targetDate;
			if ($action !== 'daily_preview') {
				$data['error'] = 'Bu ekranda yalnızca önizleme yapılabilir. Kesim müşteri listesinden başlatılmalıdır.';
			}
		}

		if ($data['error'] === null) {
			$targetDate = $data['automation_date'];
			if (!$this->is_valid_invoice_date($targetDate) || $targetDate >= $today) {
				$data['error'] = 'Test tarihi geçerli olmalı ve bugünden önceki bir gün seçilmelidir.';
			} else {
				try {
					$data['daily_preview'] = $this->build_daily_invoice_preview($targetDate);
				} catch (RuntimeException $e) {
					$data['error'] = $e->getMessage();
				} catch (Exception $e) {
					log_message('error', 'Daily invoice preview failed. Date: ' . $targetDate . ', Error: ' . $e->getMessage());
					$data['error'] = 'Fatura adayları kontrol edilirken bir hata oluştu.';
				}
			}
		}

		$this->template->content->view('fatura_kesim', $data);
		$this->template->publish();
	}

	public function manualCutUserPreviousDay()
	{
		if (!$this->requirePostRequest() || !$this->requireAdminActionCsrf()) {
			return;
		}

		$userId = (int)$this->input->post('user_id');
		if ($userId <= 0) {
			return json(['status' => 'error', 'message' => 'Geçersiz kullanıcı.'], 422);
		}
		if (!$this->is_invoice_customer($userId)) {
			return json(['status' => 'error', 'message' => 'Müşteri bulunamadı.'], 404);
		}

		$timezone = new DateTimeZone('Europe/Istanbul');
		$targetDate = (new DateTimeImmutable('yesterday', $timezone))->format('Y-m-d');
		$lockName = 'playsel_invoice_' . $userId . '_' . str_replace('-', '', $targetDate);
		$lock = $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$lockName])->row();
		if (!$lock || (int)$lock->acquired !== 1) {
			return json(['status' => 'error', 'message' => 'Bu müşterinin günlük fatura işlemi zaten çalışıyor.'], 409);
		}

		$response = null;
		try {
			$this->invoiceBundleValidationCache = [];
			$preview = $this->build_daily_invoice_preview($targetDate, $userId);
			$userGroups = array_values(array_filter($preview['groups'], function ($group) use ($userId) {
				return (int)$group['user_id'] === $userId;
			}));
			$readyGroups = array_values(array_filter($userGroups, function ($group) {
				return $group['ready'];
			}));
			$heldGroups = array_values(array_filter($userGroups, function ($group) {
				return !$group['ready'];
			}));

			if (empty($readyGroups)) {
				$reasons = [];
				foreach ($userGroups as $group) {
					$reasons = array_merge($reasons, $group['hold_reasons']);
				}
				$response = [
					'status' => 'error',
					'message' => empty($userGroups)
						? 'Dün için faturalanmaya uygun tamamlanmış sipariş bulunamadı.'
						: implode(' ', array_values(array_unique($reasons))),
				];
			} else {
				$results = [];
				foreach ($readyGroups as $group) {
					$this->invoiceBundleValidationCache = [];
					try {
						$payload = $this->build_toplu_fatura_payload(
							$userId,
							$targetDate . ' 00:00:00',
							$targetDate . ' 23:59:59',
							$targetDate,
							$group['order_ids'],
							$group['snapshot']
						);
						$results[] = $this->send_and_record_invoice($payload);
						$lastResult = end($results);
						if (!empty($lastResult['sent_not_recorded']) || !empty($lastResult['needs_reconciliation'])) {
							break;
						}
					} catch (Exception $e) {
						log_message('error', 'Manual invoice group failed. User: ' . $userId . ', Date: ' . $targetDate . ', Error: ' . $e->getMessage());
						$results[] = ['success' => false, 'message' => 'Bir fatura grubu hazırlanamadı; diğer gruplar ayrıca kontrol edildi.'];
					}
				}

				$successful = count(array_filter($results, function ($result) {
					return !empty($result['success']);
				}));
				$failed = count($results) - $successful;
				$failureMessages = array_values(array_unique(array_filter(array_map(function ($result) {
					return empty($result['success']) ? trim((string)($result['message'] ?? '')) : '';
				}, $results))));
				$heldReasons = [];
				foreach ($heldGroups as $group) {
					$heldReasons = array_merge($heldReasons, $group['hold_reasons']);
				}
				$heldMessage = count($heldGroups) > 0
					? ' ' . count($heldGroups) . ' grup alıcı/bundle kontrolü nedeniyle bekletildi. ' . implode(' ', array_values(array_unique($heldReasons)))
					: '';
				$response = [
					'status' => $failed === 0 && empty($heldGroups) ? 'success' : ($successful > 0 ? 'partial' : 'error'),
					'message' => $successful . ' fatura kesildi.' . ($failed > 0 ? ' ' . $failed . ' grup kesilemedi. ' . implode(' ', $failureMessages) : '') . $heldMessage,
					'results' => $results,
				];
			}
		} catch (RuntimeException $e) {
			$response = ['status' => 'error', 'message' => $e->getMessage()];
		} catch (Exception $e) {
			log_message('error', 'Manual invoice failed. User: ' . $userId . ', Date: ' . $targetDate . ', Error: ' . $e->getMessage());
			$response = ['status' => 'error', 'message' => 'Fatura kesimi sırasında bir hata oluştu.'];
		} finally {
			$this->db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
		}

		return json($response, in_array($response['status'], ['success', 'partial'], true) ? 200 : 422);
	}

	public function previewInvoiceGroup()
	{
		if (!$this->requirePostRequest()) {
			return;
		}

		$userId = (int)$this->input->post('user_id');
		$targetDate = trim((string)$this->input->post('target_date'));
		$orderId = (int)$this->input->post('order_id');
		if ($userId <= 0 || $orderId <= 0 || !$this->is_valid_invoice_date($targetDate)) {
			return json(['status' => 'error', 'message' => 'Geçersiz kullanıcı, sipariş veya tarih.'], 422);
		}
		if (!$this->is_invoice_customer($userId)) {
			return json(['status' => 'error', 'message' => 'Müşteri bulunamadı.'], 404);
		}
		if (!$this->is_invoice_date_before_today($targetDate)) {
			return json(['status' => 'error', 'message' => 'Yalnız bugünden önceki sipariş günleri önizlenebilir.'], 422);
		}

		try {
			$group = $this->find_invoice_group($userId, $targetDate, $orderId);
			if (!$group) {
				return json(['status' => 'error', 'message' => 'Seçilen sipariş için faturalanmamış aday bulunamadı.'], 404);
			}
			if (!$group['ready']) {
				return json(['status' => 'error', 'message' => implode(' ', $group['hold_reasons'])], 422);
			}

			$payload = $this->build_invoice_group_payload($userId, $targetDate, $group);
			return json([
				'status' => 'success',
				'target_date' => $targetDate,
				'group' => $this->invoice_preview_data($group, $payload),
			]);
		} catch (RuntimeException $e) {
			return json(['status' => 'error', 'message' => $e->getMessage()], 422);
		} catch (Exception $e) {
			log_message('error', 'Invoice group preview failed. User: ' . $userId . ', Order: ' . $orderId . ', Error: ' . $e->getMessage());
			return json(['status' => 'error', 'message' => 'Fatura önizlemesi hazırlanırken bir hata oluştu.'], 500);
		}
	}

	public function manualCutInvoiceGroup()
	{
		if (!$this->requirePostRequest() || !$this->requireAdminActionCsrf()) {
			return;
		}

		$userId = (int)$this->input->post('user_id');
		$targetDate = trim((string)$this->input->post('target_date'));
		$orderId = (int)$this->input->post('order_id');
		if ($userId <= 0 || $orderId <= 0 || !$this->is_valid_invoice_date($targetDate)) {
			return json(['status' => 'error', 'message' => 'Geçersiz kullanıcı, sipariş veya tarih.'], 422);
		}
		if (!$this->is_invoice_customer($userId)) {
			return json(['status' => 'error', 'message' => 'Müşteri bulunamadı.'], 404);
		}
		if (!$this->is_invoice_date_before_today($targetDate)) {
			return json(['status' => 'error', 'message' => 'Yalnız bugünden önceki sipariş günleri için manuel kesim yapılabilir.'], 422);
		}

		$lockName = 'playsel_invoice_' . $userId . '_' . str_replace('-', '', $targetDate);
		$lock = $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$lockName])->row();
		if (!$lock || (int)$lock->acquired !== 1) {
			return json(['status' => 'error', 'message' => 'Bu müşterinin seçilen gün için fatura işlemi zaten çalışıyor.'], 409);
		}

		$response = null;
		try {
			$group = $this->find_invoice_group($userId, $targetDate, $orderId);
			if (!$group) {
				$response = ['status' => 'error', 'message' => 'Seçilen sipariş artık faturalanmaya uygun değil. Listeyi yenileyip tekrar kontrol edin.'];
			} elseif (!$group['ready']) {
				$response = ['status' => 'error', 'message' => implode(' ', $group['hold_reasons'])];
			} else {
				$payload = $this->build_invoice_group_payload($userId, $targetDate, $group);
				$result = $this->send_and_record_invoice($payload);
				$response = [
					'status' => !empty($result['success']) ? 'success' : 'error',
					'message' => !empty($result['success'])
						? 'Fatura kesildi. Fatura No: ' . $result['invoice_no']
						: ($result['message'] ?? 'Fatura kesimi başarısız oldu.'),
					'result' => $result,
				];
			}
		} catch (RuntimeException $e) {
			$response = ['status' => 'error', 'message' => $e->getMessage()];
		} catch (Exception $e) {
			log_message('error', 'Manual invoice group failed. User: ' . $userId . ', Order: ' . $orderId . ', Error: ' . $e->getMessage());
			$response = ['status' => 'error', 'message' => 'Fatura kesimi sırasında bir hata oluştu.'];
		} finally {
			$this->db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
		}

		return json($response, $response['status'] === 'success' ? 200 : 422);
	}

	public function previewUserPreviousDay()
	{
		if (!$this->requirePostRequest()) {
			return;
		}

		$userId = (int)$this->input->post('user_id');
		if ($userId <= 0) {
			return json(['status' => 'error', 'message' => 'Geçersiz kullanıcı.'], 422);
		}
		if (!$this->is_invoice_customer($userId)) {
			return json(['status' => 'error', 'message' => 'Müşteri bulunamadı.'], 404);
		}

		$timezone = new DateTimeZone('Europe/Istanbul');
		$targetDate = (new DateTimeImmutable('yesterday', $timezone))->format('Y-m-d');
		$lockName = 'playsel_invoice_' . $userId . '_' . str_replace('-', '', $targetDate);
		$lock = $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$lockName])->row();
		if (!$lock || (int)$lock->acquired !== 1) {
			return json(['status' => 'error', 'message' => 'Bu müşterinin günlük fatura işlemi zaten çalışıyor.'], 409);
		}

		$response = null;
		try {
			$this->invoiceBundleValidationCache = [];
			$preview = $this->build_daily_invoice_preview($targetDate, $userId);
			$userGroups = array_values(array_filter($preview['groups'], function ($group) use ($userId) {
				return (int)$group['user_id'] === $userId;
			}));
			$groups = [];
			foreach ($userGroups as $group) {
				if (!$group['ready']) {
					$groups[] = [
						'ready' => false,
						'customer_name' => $group['customer_name'],
						'order_ids' => $group['order_ids'],
						'hold_reasons' => $group['hold_reasons'],
					];
					continue;
				}

				$this->invoiceBundleValidationCache = [];
				$payload = $this->build_toplu_fatura_payload(
					$userId,
					$targetDate . ' 00:00:00',
					$targetDate . ' 23:59:59',
					$targetDate,
					$group['order_ids'],
					$group['snapshot']
				);
				$customer = $payload['invoice_data']['customer'];
				$groups[] = [
					'ready' => true,
					'customer_name' => $group['customer_name'],
					'invoice_date' => $targetDate,
					'invoice_type' => $group['invoice_type'],
					'is_efatura' => $payload['invoice_data']['is_efatura'],
					'order_ids' => $payload['order_ids'],
					'order_count' => $payload['total_order_count'],
					'recipient' => [
						'name' => $customer['user_bill_company_name'],
						'tax_number' => $customer['user_bill_tax_number'],
						'tax_office' => $customer['user_bill_tax_office'],
						'address' => $customer['user_bill_address'],
						'district' => $customer['user_bill_district'],
						'city' => $customer['user_bill_city'],
						'phone' => $customer['user_phone'] ?? '',
						'email' => $customer['user_email'] ?? '',
					],
					'items' => $payload['items'],
					'net_total' => $payload['net_total'],
					'tax_total' => $payload['tax_total'],
					'grand_total' => $payload['grand_total'],
					'toplam_odenecek' => round($payload['toplam_odenecek'], 2),
				];
			}

			$response = [
				'status' => 'success',
				'target_date' => $targetDate,
				'groups' => $groups,
				'message' => empty($groups) ? 'Dün için faturalanmaya uygun tamamlanmış sipariş bulunamadı.' : '',
			];
		} catch (RuntimeException $e) {
			$response = ['status' => 'error', 'message' => $e->getMessage()];
		} catch (Exception $e) {
			log_message('error', 'Manual invoice preview failed. User: ' . $userId . ', Date: ' . $targetDate . ', Error: ' . $e->getMessage());
			$response = ['status' => 'error', 'message' => 'Fatura önizlemesi hazırlanırken bir hata oluştu.'];
		} finally {
			$this->db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
		}

		return json($response, $response['status'] === 'success' ? 200 : 422);
	}

	private function is_valid_invoice_date($date)
	{
		$parsed = DateTime::createFromFormat('!Y-m-d', $date, new DateTimeZone('Europe/Istanbul'));
		return $parsed && $parsed->format('Y-m-d') === $date;
	}

	private function is_invoice_date_before_today($date)
	{
		return $this->is_valid_invoice_date($date)
			&& $date < (new DateTimeImmutable('today', new DateTimeZone('Europe/Istanbul')))->format('Y-m-d');
	}

	private function find_invoice_group($userId, $targetDate, $orderId)
	{
		$this->invoiceBundleValidationCache = [];
		$preview = $this->build_daily_invoice_preview($targetDate, $userId);
		foreach ($preview['groups'] as $group) {
			if (in_array($orderId, $group['order_ids'], true)) {
				return $group;
			}
		}
		return null;
	}

	private function build_invoice_group_payload($userId, $targetDate, array $group)
	{
		$this->invoiceBundleValidationCache = [];
		return $this->build_toplu_fatura_payload(
			$userId,
			$targetDate . ' 00:00:00',
			$targetDate . ' 23:59:59',
			$targetDate,
			$group['order_ids'],
			$group['snapshot']
		);
	}

	private function invoice_preview_data(array $group, array $payload)
	{
		$customer = $payload['invoice_data']['customer'];
		return [
			'ready' => true,
			'user_id' => $group['user_id'],
			'user_name' => $group['user_name'],
			'customer_name' => $group['customer_name'],
			'invoice_date' => $payload['invoice_date'],
			'invoice_type' => $group['invoice_type'],
			'is_efatura' => $payload['invoice_data']['is_efatura'],
			'order_ids' => $payload['order_ids'],
			'order_count' => $payload['total_order_count'],
			'recipient' => [
				'name' => $customer['user_bill_company_name'],
				'tax_number' => $customer['user_bill_tax_number'],
				'tax_office' => $customer['user_bill_tax_office'],
				'address' => $customer['user_bill_address'],
				'district' => $customer['user_bill_district'],
				'city' => $customer['user_bill_city'],
				'phone' => $customer['user_phone'] ?? '',
				'email' => $customer['user_email'] ?? '',
			],
			'items' => $payload['items'],
			'net_total' => $payload['net_total'],
			'tax_total' => $payload['tax_total'],
			'grand_total' => $payload['grand_total'],
			'toplam_odenecek' => round($payload['toplam_odenecek'], 2),
		];
	}

	private function is_invoice_customer($userId)
	{
		return $this->db->select('user_id')
			->where('user_id', (int)$userId)
			->where('user_type', 4)
			->limit(1)
			->get('users')
			->row() !== null;
	}

	private function build_daily_invoice_preview($targetDate, $userId = null)
	{
		$start = $targetDate . ' 00:00:00';
		$end = (new DateTimeImmutable($targetDate, new DateTimeZone('Europe/Istanbul')))
			->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
		$orders = $this->db->select('o.order_id, o.order_reseller_id, o.order_type, o.order_product_id, o.order_quantity, o.order_created_at, o.order_status, o.order_invoice_status, o.order_billing_address, o.order_payment_info, u.user_name, u.user_surname, p.product_name')
			->from('orders as o')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->where('o.order_created_at >=', $start)
			->where('o.order_created_at <', $end)
			->where('o.order_type', 3)
			->where('o.order_status', 1)
			->where('o.order_invoice_status', 0)
			->order_by('o.order_id', 'ASC');
		if ($userId !== null) {
			$this->db->where('o.order_reseller_id', (int)$userId);
		}
		$query = $this->db->limit(1001)->get();
		$orders = $query->result();
		if (count($orders) > 1000) {
			throw new RuntimeException('Bu gün 1000’den fazla sipariş içeriyor; güvenli önizleme için sayfalama gerekiyor.');
		}

		$groups = [];
		foreach ($orders as $order) {
			$snapshot = $this->invoice_snapshot_for_user(
				json_decode((string)$order->order_billing_address, true),
				$order->user_name,
				$order->user_surname
			);
			$validationErrors = $this->validate_invoice_snapshot($snapshot);
			if (trim((string)$order->product_name) === '') {
				$validationErrors[] = 'Sipariş ürün bilgisi bulunamadı.';
			}
			$identity = $this->invoice_snapshot_identity($snapshot);
			$identityKey = !empty($validationErrors)
				? 'invalid_order_' . (int)$order->order_id
				: (int)$order->order_reseller_id . '_' . hash('sha256', json_encode($identity, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

			if (!isset($groups[$identityKey])) {
				$groups[$identityKey] = [
					'user_id' => (int)$order->order_reseller_id,
					'user_name' => trim((string)$order->user_name),
					'invoice_date' => substr((string)$order->order_created_at, 0, 10),
					'customer_name' => $this->invoice_snapshot_name($snapshot),
					'invoice_type' => (int)($snapshot['invoice_type'] ?? 0),
					'order_ids' => [],
					'orders' => [],
					'hold_reasons' => $validationErrors,
				];
			}

			$groups[$identityKey]['order_ids'][] = (int)$order->order_id;
			$groups[$identityKey]['orders'][] = $order;
			$groups[$identityKey]['hold_reasons'] = array_values(array_unique(array_merge(
				$groups[$identityKey]['hold_reasons'],
				$this->validate_invoice_bundle($order)
			)));
		}

		$previewGroups = [];
		foreach ($groups as $group) {
			$previewGroups[] = [
				'user_id' => $group['user_id'],
				'user_name' => $group['user_name'],
				'invoice_date' => $group['invoice_date'],
				'customer_name' => $group['customer_name'] ?: 'Alıcı bilgisi eksik',
				'invoice_type' => $group['invoice_type'],
				'order_ids' => $group['order_ids'],
				'snapshot' => $this->invoice_snapshot_for_user(
					json_decode((string)$group['orders'][0]->order_billing_address, true),
					$group['orders'][0]->user_name,
					$group['orders'][0]->user_surname
				),
				'order_count' => count($group['orders']),
				'hold_reasons' => $group['hold_reasons'],
				'ready' => empty($group['hold_reasons']),
			];
		}

		$invoicedQuery = $this->db->select('o.order_reseller_id AS user_id, u.user_name, o.order_invoice_number, o.order_invoice_uuid, o.order_invoice_date, COUNT(o.order_id) AS order_count', false)
			->from('orders AS o')
			->join('users AS u', 'u.user_id = o.order_reseller_id', 'left')
			->where('o.order_created_at >=', $start)
			->where('o.order_created_at <', $end)
			->where('o.order_type', 3)
			->where('o.order_invoice_status', 1)
			->where('o.order_invoice_number !=', '');
		if ($userId !== null) {
			$invoicedQuery->where('o.order_reseller_id', (int)$userId);
		}
		$invoices = $invoicedQuery
			->group_by(['o.order_invoice_uuid', 'o.order_invoice_number', 'o.order_reseller_id', 'u.user_name', 'o.order_invoice_date'])
			->order_by('o.order_invoice_date', 'DESC')
			->limit(1001)
			->get()->result_array();

		return [
			'target_date' => $targetDate,
			'group_count' => count($previewGroups),
			'ready_count' => count(array_filter($previewGroups, function ($group) {
				return $group['ready'];
			})),
			'held_count' => count(array_filter($previewGroups, function ($group) {
				return !$group['ready'];
			})),
			'groups' => $previewGroups,
			'invoices' => $invoices,
		];
	}

	private function validate_invoice_snapshot($snapshot)
	{
		$errors = [];
		if (!is_array($snapshot)) {
			return ['Sipariş anında kaydedilmiş fatura adresi bulunamadı.'];
		}

		$invoiceType = (int)($snapshot['invoice_type'] ?? 0);
		if (!in_array($invoiceType, [1, 2], true)) {
			return ['Fatura türü tanınmıyor.'];
		}

		$isTurkish = $invoiceType === 1
			? (int)($snapshot['is_turkish_citizen'] ?? 0) === 1
			: (int)($snapshot['is_turkish_company'] ?? 0) === 1;
		if (!$isTurkish) {
			return ['Yurt dışı alıcı için muhasebe/entegratör kuralı henüz onaylanmadı.'];
		}
		if (trim((string)($snapshot['country'] ?? '')) !== 'Türkiye') {
			$errors[] = 'Alıcı ülke bilgisi ile Türkiye müşteri türü uyumsuz.';
		}

		$name = $invoiceType === 2
			? trim((string)($snapshot['company_name'] ?? ''))
			: trim((string)($snapshot['full_name'] ?? ''));
		if ($name === '') {
			$errors[] = $invoiceType === 2 ? 'Firma unvanı eksik.' : 'Ad soyad eksik.';
		}

		$taxNumber = preg_replace('/\s+/', '', (string)($snapshot['tax_number'] ?? ''));
		$expectedTaxLength = $invoiceType === 2 ? 10 : 11;
		if (!preg_match('/^\d{' . $expectedTaxLength . '}$/', $taxNumber)) {
			$errors[] = $invoiceType === 2 ? '10 haneli vergi numarası eksik veya geçersiz.' : '11 haneli T.C. kimlik numarası eksik veya geçersiz.';
		}
		if ($invoiceType === 2 && trim((string)($snapshot['tax_office'] ?? '')) === '') {
			$errors[] = 'Vergi dairesi eksik.';
		}
		if (trim((string)($snapshot['address'] ?? '')) === '') {
			$errors[] = 'Açık adres eksik.';
		}
		if (trim((string)($snapshot['city'] ?? '')) === '') {
			$errors[] = 'İl bilgisi eksik.';
		}
		if (trim((string)($snapshot['district'] ?? '')) === '') {
			$errors[] = 'İlçe bilgisi eksik.';
		}

		return $errors;
	}

	private function invoice_snapshot_for_user($snapshot, $userName, $userSurname)
	{
		$fullName = trim((string)$userName . ' ' . (string)$userSurname);
		$address = is_array($snapshot) ? trim((string)($snapshot['address'] ?? '')) : '';
		$taxNumber = is_array($snapshot)
			? preg_replace('/\s+/', '', (string)($snapshot['tax_number'] ?? ''))
			: '';
		$isAuthDefaultAddress = is_array($snapshot)
			&& (int)($snapshot['invoice_type'] ?? 0) === 1
			&& trim((string)($snapshot['title'] ?? '')) === 'Fatura Adresi'
			&& $taxNumber === '11111111111'
			&& trim((string)($snapshot['city'] ?? '')) === 'İstanbul'
			&& trim((string)($snapshot['district'] ?? '')) === 'Kadıköy'
			&& in_array($address, ['', 'Merkez Mah.'], true);

		$hasNoInvoiceData = !is_array($snapshot) || empty($snapshot);
		if (is_array($snapshot) && (int)($snapshot['address_id'] ?? 0) <= 0) {
			$hasNoInvoiceData = true;
			foreach (['company_name', 'tax_office', 'tax_number', 'country', 'city', 'district', 'postal_code', 'address'] as $field) {
				if (trim((string)($snapshot[$field] ?? '')) !== '') {
					$hasNoInvoiceData = false;
					break;
				}
			}
		}

		if (!$hasNoInvoiceData && !$isAuthDefaultAddress) {
			return is_array($snapshot) ? $snapshot : [];
		}

		return [
			'address_id' => is_array($snapshot) ? (int)($snapshot['address_id'] ?? 0) : 0,
			'title' => 'Fatura Adresi',
			'invoice_type' => 1,
			'is_turkish_citizen' => 1,
			'is_turkish_company' => 0,
			'full_name' => $fullName,
			'company_name' => '',
			'tax_office' => '',
			'tax_number' => '11111111111',
			'is_e_invoice_taxpayer' => 0,
			'country' => 'Türkiye',
			'city' => 'İstanbul',
			'district' => 'Kadıköy',
			'postal_code' => '',
			'address' => ' ',
			'snapshot_at' => is_array($snapshot) ? ($snapshot['snapshot_at'] ?? null) : null,
		];
	}

	private function invoice_snapshot_identity($snapshot)
	{
		if (!is_array($snapshot)) {
			return [];
		}

		$fields = [
			'invoice_type',
			'is_turkish_citizen',
			'is_turkish_company',
			'full_name',
			'company_name',
			'tax_office',
			'tax_number',
			'country',
			'city',
			'district',
			'postal_code',
			'address',
		];
		$identity = [];
		foreach ($fields as $field) {
			$identity[$field] = trim((string)($snapshot[$field] ?? ''));
		}

		return $identity;
	}

	private function invoice_snapshot_name($snapshot)
	{
		if (!is_array($snapshot)) {
			return '';
		}

		$name = (int)($snapshot['invoice_type'] ?? 0) === 2
			? ($snapshot['company_name'] ?? '')
			: ($snapshot['full_name'] ?? '');
		return trim((string)$name);
	}

	private function validate_invoice_bundle($order)
	{
		$paymentInfo = json_decode((string)$order->order_payment_info, true);
		$pricingSnapshot = is_array($paymentInfo) ? ($paymentInfo['pricing_snapshot'] ?? []) : [];
		$bundleReference = trim((string)($pricingSnapshot['bundle_reference'] ?? ''));
		$expectedParts = (int)($pricingSnapshot['bundle_part_count'] ?? 0);
		if ($bundleReference === '' || $expectedParts <= 1) {
			return [];
		}
		if (isset($this->invoiceBundleValidationCache[$bundleReference])) {
			return $this->invoiceBundleValidationCache[$bundleReference];
		}

		$matches = $this->db->select('o.order_id, o.order_reseller_id, o.order_type, o.order_created_at, o.order_status, o.order_invoice_status, o.order_payment_info, o.order_billing_address, u.user_name, u.user_surname')
			->from('orders AS o')
			->join('users AS u', 'u.user_id = o.order_reseller_id', 'left')
			->like('o.order_payment_info', $bundleReference)
			->limit(1001)
			->get()->result();
		if (count($matches) > 1000) {
			$this->invoiceBundleValidationCache[$bundleReference] = ['Bundle satır sayısı kontrol sınırını aştı; grup elle kontrol edilmeli.'];
			return $this->invoiceBundleValidationCache[$bundleReference];
		}
		$bundleOrders = [];
		foreach ($matches as $bundleOrder) {
			$bundleInfo = json_decode((string)$bundleOrder->order_payment_info, true);
			$bundlePricing = is_array($bundleInfo) ? ($bundleInfo['pricing_snapshot'] ?? []) : [];
			if (($bundlePricing['bundle_reference'] ?? '') === $bundleReference) {
				$bundleOrders[] = $bundleOrder;
			}
		}

		if (count($bundleOrders) !== $expectedParts) {
			$this->invoiceBundleValidationCache[$bundleReference] = ['Bundle sipariş satırlarının tamamı bulunamadı; grup bekletildi.'];
			return $this->invoiceBundleValidationCache[$bundleReference];
		}
		foreach ($bundleOrders as $bundleOrder) {
			if ((int)$bundleOrder->order_reseller_id !== (int)$order->order_reseller_id || (int)$bundleOrder->order_type !== 3) {
				$this->invoiceBundleValidationCache[$bundleReference] = ['Bundle satırları aynı site kullanıcısına ait değil; grup elle kontrol edilmeli.'];
				return $this->invoiceBundleValidationCache[$bundleReference];
			}
			if ((int)$bundleOrder->order_status !== 1 || (int)$bundleOrder->order_invoice_status !== 0) {
				$this->invoiceBundleValidationCache[$bundleReference] = ['Bundle içindeki tüm satırlar tamamlanmadı veya aynı faturada değil; grup bekletildi.'];
				return $this->invoiceBundleValidationCache[$bundleReference];
			}
			if (substr((string)$bundleOrder->order_created_at, 0, 10) !== substr((string)$order->order_created_at, 0, 10)) {
				$this->invoiceBundleValidationCache[$bundleReference] = ['Bundle satırlarının sipariş tarihi farklı; grup elle kontrol edilmeli.'];
				return $this->invoiceBundleValidationCache[$bundleReference];
			}
			$bundleSnapshot = $this->invoice_snapshot_for_user(
				json_decode((string)$bundleOrder->order_billing_address, true),
				$bundleOrder->user_name,
				$bundleOrder->user_surname
			);
			$orderSnapshot = $this->invoice_snapshot_for_user(
				json_decode((string)$order->order_billing_address, true),
				$order->user_name,
				$order->user_surname
			);
			if ($this->invoice_snapshot_identity($bundleSnapshot) !== $this->invoice_snapshot_identity($orderSnapshot)) {
				$this->invoiceBundleValidationCache[$bundleReference] = ['Bundle içindeki fatura alıcı bilgileri farklı; grup elle kontrol edilmeli.'];
				return $this->invoiceBundleValidationCache[$bundleReference];
			}
		}

		$this->invoiceBundleValidationCache[$bundleReference] = [];
		return $this->invoiceBundleValidationCache[$bundleReference];
	}

	private function build_toplu_fatura_payload($reseller_id, $start_date, $end_date, $invoice_date = null, array $orderIds = [], array $billingSnapshot = [])
	{
		$orderIds = array_values(array_unique(array_map('intval', $orderIds)));
		if (empty($orderIds)) {
			throw new RuntimeException('Faturalanacak sipariş bulunamadı.');
		}

		$orders = $this->db->select('o.order_id, o.order_product_id, o.order_quantity, o.order_sale_price, o.order_cost, o.order_created_at, o.order_payment_info, o.order_billing_address, p.product_name, p.product_is_full_vat, u.*')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'inner')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'inner')
			->where('o.order_reseller_id', $reseller_id)
			->where('o.order_type', 3)
			->where('o.order_created_at >=', $start_date)
			->where('o.order_created_at <=', $end_date)
			->where('o.order_invoice_status', 0)
			->where('o.order_status', 1)
			->where_in('o.order_id', $orderIds)
			->order_by('o.order_id', 'ASC')
			->get()->result();

		if (count($orders) !== count($orderIds)) {
			throw new RuntimeException('Siparişler değişti veya artık faturalanmaya uygun değil; işlem durduruldu.');
		}
		foreach ($orders as $order) {
			$orderSnapshot = $this->invoice_snapshot_for_user(
				json_decode((string)$order->order_billing_address, true),
				$order->user_name,
				$order->user_surname
			);
			if (substr((string)$order->order_created_at, 0, 10) !== $invoice_date
				|| $this->invoice_snapshot_identity($orderSnapshot) !== $this->invoice_snapshot_identity($billingSnapshot)
				|| !empty($this->validate_invoice_snapshot($orderSnapshot))
				|| !empty($this->validate_invoice_bundle($order))) {
				throw new RuntimeException('Sipariş alıcı/bundle kontrolünden geçemedi; işlem durduruldu.');
			}
		}

		$this->load->library('birfatura');
		$user = $orders[0];
		$user->user_bill_company_name = (int)$billingSnapshot['invoice_type'] === 2
			? trim((string)$billingSnapshot['company_name'])
			: trim((string)$billingSnapshot['full_name']);
		$user->user_bill_address = trim((string)$billingSnapshot['address']);
		$user->user_bill_city = trim((string)$billingSnapshot['city']);
		$user->user_bill_district = trim((string)$billingSnapshot['district']);
		$user->user_bill_tax_office = trim((string)($billingSnapshot['tax_office'] ?? ''));
		$user->user_bill_tax_number = preg_replace('/\s+/', '', (string)$billingSnapshot['tax_number']);
		$user->user_bill_e_invoice = (int)($billingSnapshot['is_e_invoice_taxpayer'] ?? 0);
		$user->user_is_turkish_company = 1;
		$user->user_phone = trim((string)($billingSnapshot['phone'] ?? $user->user_phone ?? ''));
		$user->user_email = trim((string)($billingSnapshot['email'] ?? $user->user_email ?? ''));

		$status = $this->birfatura->check_user_status($user->user_bill_tax_number);
		$is_efatura = !empty($status['is_efatura']) ? true : ($user->user_bill_e_invoice == 1);

		$endpoint = $status['endpoint'] ?? null;
		if (empty($endpoint) && $is_efatura) {
			$endpoint = 'urn:mail:defaultpk';
		}

		$uuid = $this->birfatura->generate_uuid();
		$is_foreign = ($user->user_is_turkish_company == 0);

		$type_code = $is_foreign ? 'ISTISNA' : 'SATIS';
		$exemption_code = $is_foreign ? '302' : '351';
		$exemption_text = $is_foreign ? '11/1-a Hizmet ihracatı' : 'İstisna Olmayan Diğer';

		// --- MAP & REDUCE (GRUPLAMA) KATMANI ---
		$grouped_products = [];
		$hizmet_bedeli_raw = [
			'stock_code' => '0-0',
			'name'       => 'Hizmet Bedeli',
			'total_profit_kdv_dahil' => 0.0 // Ham kâr kümülasyonu için
		];
		$has_hizmet = false;

		$order_ids = [];
		$toplam_odenecek = 0.0;

		// 1. FAZ: DÖNGÜ İÇİNDE SIFIR YUVARLAMA, SADECE HAM VERİ BİRİKTİRME
		foreach ($orders as $row) {
			$order_ids[] = $row->order_id;

			// Satır bazlı tutarlar (Veritabanından KDV dahil geldiğini / birim*adet olarak satır toplamı olduğunu varsayıyoruz)
			$satis_fiyati = (float)$row->order_sale_price;
			$maliyet_fiyati = (float)$row->order_cost;
			$adet = (int)$row->order_quantity;
			$safe_adet = $adet > 0 ? $adet : 1;

			$stok_kodu = $row->order_product_id ?? '0-0';
			// DİKKAT: Ürün adından sipariş numarasını (#12345) siliyoruz.
			$urun_adi = trim($row->product_name);

			// Unique Key (Stok Kodu + İsim kombinasyonu)
			$key = $stok_kodu . '_' . md5($urun_adi);

			if (!isset($grouped_products[$key])) {
				$grouped_products[$key] = [
					'stock_code'      => $stok_kodu,
					'name'            => $urun_adi,
					'qty'             => 0,
					'total_kdv_dahil' => 0.0, // Fiziki / Yurtdışı siparişlerin yekûnü
					'total_maliyet'   => 0.0, // E-Pinler için yekûn maliyet (0% KDV)
					'tax_rate'        => 0,
					'is_full_vat'     => false
				];
			}

			if ($is_foreign) {
				// Yurtdışı tamamen istisna
				$grouped_products[$key]['qty'] += $safe_adet;
				$grouped_products[$key]['total_kdv_dahil'] += $satis_fiyati;
				$toplam_odenecek += $satis_fiyati;
			} else {
				$is_full_vat_product = isset($row->product_is_full_vat) && $row->product_is_full_vat == 1;

				if ($is_full_vat_product) {
					// Tamamı %20 KDV'li (Fiziki/Genel ürün)
					$grouped_products[$key]['qty'] += $safe_adet;
					$grouped_products[$key]['total_kdv_dahil'] += $satis_fiyati;
					$grouped_products[$key]['tax_rate'] = 20;
					$grouped_products[$key]['is_full_vat'] = true;

					$toplam_odenecek += $satis_fiyati;
				} else {
					// E-PİN Kâr Marjlı Mantık (Maliyet %0, Kâr %20)
					$grouped_products[$key]['qty'] += $safe_adet;
					$grouped_products[$key]['total_maliyet'] += $maliyet_fiyati;

					// Hizmet bedeli (Kâr) için ham matrahı biriktir
					$profit_kdv_dahil = $satis_fiyati - $maliyet_fiyati;
					$hizmet_bedeli_raw['total_profit_kdv_dahil'] += $profit_kdv_dahil;
					$has_hizmet = true;

					$toplam_odenecek += $satis_fiyati;
				}
			}
		}

		// 2. FAZ: GRUPLANAN VERİLERİN MATRAH AYRIŞTIRMASI VE YUVARLANMASI
		$items = [];
		foreach ($grouped_products as $gp) {
			if ($gp['qty'] > 0) {
				if ($gp['is_full_vat']) {
					// Tamamı %20 KDV'li olan grupta tek seferlik tersine mühendislik
					$fiyat_kdv_haric = $gp['total_kdv_dahil'] / 1.20;
					$kdv_tutari = $gp['total_kdv_dahil'] - $fiyat_kdv_haric;

					$items[] = [
						'stock_code' => $gp['stock_code'],
						'name'       => $gp['name'],
						'qty'        => $gp['qty'],
						// GİB birim fiyatta 8 haneye kadar destekler, kümülatif miktar kaymalarını önler
						'price'      => round($fiyat_kdv_haric / $gp['qty'], 8),
						'tax_rate'   => 20,
						'tax_amount' => round($kdv_tutari, 2)
					];
				} else {
					// Yurtdışı (%0) veya Yerli E-Pin Maliyet (%0)
					$amount = $is_foreign ? $gp['total_kdv_dahil'] : $gp['total_maliyet'];

					$items[] = [
						'stock_code' => $gp['stock_code'],
						'name'       => $gp['name'],
						'qty'        => $gp['qty'],
						'price'      => round($amount / $gp['qty'], 8),
						'tax_rate'   => 0,
						'tax_amount' => 0.0
					];
				}
			}
		}

		// 3. FAZ: HİZMET BEDELİNİ (KÜMÜLATİF KÂR) TEK SATIR OLARAK FATURAYA EKLE
		if ($has_hizmet && $hizmet_bedeli_raw['total_profit_kdv_dahil'] > 0) {
			$total_profit = $hizmet_bedeli_raw['total_profit_kdv_dahil'];

			// Tek seferlik %20 matrah ayrıştırma işlemi
			$hizmet_kdv_haric = $total_profit / 1.20;
			$kdv_tutari = $total_profit - $hizmet_kdv_haric;

			$items[] = [
				'stock_code' => $hizmet_bedeli_raw['stock_code'],
				'name'       => $hizmet_bedeli_raw['name'],
				'qty'        => 1,
				'price'      => round($hizmet_kdv_haric, 8),
				'tax_rate'   => 20,
				'tax_amount' => round($kdv_tutari, 2)
			];
		}
		// --- GRUPLAMA KATMANI SONU ---

		$ilk_siparis = $order_ids[0];
		$son_siparis = end($order_ids);
		$gosterilecek_siparis_no = ($ilk_siparis == $son_siparis) ? $ilk_siparis : $ilk_siparis . '-' . $son_siparis;

		$notes = [
			'Fatura Kalem Sayısı: ' . count($items),
			'Yalnız ' . costToText(round($toplam_odenecek, 2)),
			'Bu fatura ' . getenv('API_URL') . ' üzerinden satışı yapılan dijital ürün/hizmet (e-pin) teslimat(lar)ını içerir.',
			'İlgili Sipariş Numaraları: ' . $gosterilecek_siparis_no
		];

		if (!empty($exemption_code)) {
			$notes[] = 'Vergi İstisna Muafiyet Sebebi: ' . $exemption_code . ' - ' . $exemption_text;
		}

		$fatura_tarihi = !empty($invoice_date) ? date('Y-m-d', strtotime($invoice_date)) : date('Y-m-d');
		$fatura_datetime = $fatura_tarihi . ' 23:59:00';

		$invoice_data = [
			'uuid'           => $uuid,
			'invoice_no'     => 'PLYSL-' . strtoupper(substr(str_replace('-', '', $uuid), 0, 8)),
			'date'           => $fatura_datetime,
			'is_foreign'     => $is_foreign,
			'is_efatura'     => $is_efatura,
			'endpoint'       => $endpoint,
			'type_code'      => $type_code,
			'currency'       => 'TRY',
			'customer'       => (array)$user,
			'exemption_code' => $exemption_code,
			'exemption_text' => $exemption_text,
			'notes'          => $notes,
			'order_no'       => $gosterilecek_siparis_no,
			'order_date'     => $fatura_tarihi,
			'sales_channel'  => 'Playsel',
		];

		// GENEL TOPLAMLARIN HESAPLANMASI (GİB standartlarına %100 uyumlu satır çarpanıyla)
		$net_total = 0.0;
		$tax_total = 0.0;
		foreach ($items as $item) {
			$line_qty = (float)($item['qty'] ?? 0);
			$line_price = (float)($item['price'] ?? 0);
			$line_net_total = round($line_qty * $line_price, 2);
			$line_tax_total = round((float)($item['tax_amount'] ?? 0), 2);
			$net_total += $line_net_total;
			$tax_total += $line_tax_total;
		}

		return [
			'orders'            => $orders,
			'user'              => $user,
			'items'             => $items,
			'order_ids'         => $order_ids,
			'invoice_data'      => $invoice_data,
			'toplam_odenecek'   => $toplam_odenecek,
			'order_no'          => $gosterilecek_siparis_no,
			'order_date'        => $fatura_tarihi,
			'invoice_date'      => $fatura_tarihi,
			'total_order_count' => count($orders),
			'total_item_count'  => count($items),
			'net_total'         => round($net_total, 2),
			'tax_total'         => round($tax_total, 2),
			'grand_total'       => round($net_total + $tax_total, 2),
			'date_start'        => $start_date,
			'date_end'          => $end_date,
		];
	}

	private function send_and_record_invoice(array $payload)
	{
		$this->load->library('birfatura');
		$result = $this->birfatura->send_invoice($payload['invoice_data'], $payload['items']);

		if (is_string($result)) {
			$result = json_decode($result, true);
		}

		if (!is_array($result) || !array_key_exists('Success', $result)) {
			$uuid = $payload['invoice_data']['uuid'];
			log_message('error', 'Birfatura returned an invalid invoice response. UUID: ' . $uuid);
			return [
				'success' => false,
				'needs_reconciliation' => true,
				'invoice_uuid' => $uuid,
				'message' => 'Birfatura yanıtı doğrulanamadı. UUID ' . $uuid . ' kontrol edilmeden tekrar kesmeyin.',
			];
		}

		if (!empty($result['Success'])) {
			$invoiceResult = $result['Result'] ?? [];
			$invoice_no = is_array($invoiceResult) ? trim((string)($invoiceResult['invoiceNo'] ?? '')) : '';
			if ($invoice_no === '') {
				$uuid = $payload['invoice_data']['uuid'];
				log_message('error', 'Birfatura accepted invoice without returning invoice number. UUID: ' . $uuid);
				return [
					'success' => false,
					'needs_reconciliation' => true,
					'invoice_uuid' => $uuid,
					'message' => 'Birfatura belgeyi kabul etti ancak fatura numarası dönmedi. Fatura UUID ' . $uuid . ' ile kontrol edilmeden tekrar kesmeyin.',
				];
			}
			$updateData = [
				'order_invoice_number' => $invoice_no,
				'order_invoice_status' => 1,
				'order_invoice_uuid'   => $payload['invoice_data']['uuid'],
				'order_invoice_date'   => $payload['invoice_data']['date'],
			];

			$orderIds = array_values(array_unique(array_map('intval', (array)$payload['order_ids'])));
			if (!$this->db->trans_begin()) {
				$uuid = $payload['invoice_data']['uuid'];
				log_message('error', 'Invoice was accepted by Birfatura but DB transaction could not start. UUID: ' . $uuid);
				return [
					'success' => false,
					'sent_not_recorded' => true,
					'invoice_uuid' => $uuid,
					'message' => 'Birfatura belgeyi kabul etti ancak sipariş kaydı başlatılamadı. UUID ' . $uuid . ' kontrol edilmeden tekrar kesmeyin.',
				];
			}
			$this->db->where_in('order_id', $orderIds)
				->where('order_reseller_id', (int)$payload['user']->user_id)
				->where('order_type', 3)
				->where('order_status', 1)
				->where('order_invoice_status', 0)
				->update('orders', $updateData);
			$updatedRows = $this->db->affected_rows();
			if ($this->db->trans_status() === false || $updatedRows !== count($orderIds)) {
				$this->db->trans_rollback();
				$uuid = $payload['invoice_data']['uuid'];
				log_message('error', 'Invoice was accepted by Birfatura but order records were not updated. UUID: ' . $uuid);
				return [
					'success' => false,
					'sent_not_recorded' => true,
					'invoice_uuid' => $uuid,
					'message' => 'Birfatura belgeyi kabul etti ancak sipariş kayıtları güncellenemedi. UUID ' . $uuid . ' kontrol edilmeden tekrar kesmeyin.',
				];
			}
			if (!$this->db->trans_commit()) {
				$uuid = $payload['invoice_data']['uuid'];
				log_message('error', 'Invoice was accepted by Birfatura but order transaction commit failed. UUID: ' . $uuid);
				return [
					'success' => false,
					'sent_not_recorded' => true,
					'invoice_uuid' => $uuid,
					'message' => 'Birfatura belgeyi kabul etti ancak sipariş kaydı kesinleştirilemedi. UUID ' . $uuid . ' kontrol edilmeden tekrar kesmeyin.',
				];
			}

			return ['success' => true, 'invoice_no' => $invoice_no, 'invoice_uuid' => $payload['invoice_data']['uuid']];
		}

		$message = trim((string)($result['Message'] ?? ''));
		if ($message === '') {
			$message = 'Birfatura belgeyi oluşturmadı.';
		}
		log_message('error', 'Birfatura rejected invoice. UUID: ' . $payload['invoice_data']['uuid'] . ', Message: ' . $message);
		return ['success' => false, 'message' => 'Birfatura belgeyi kabul etmedi. Tekrar kesmeden önce fatura durumu kontrol edilmelidir.'];
	}
}
