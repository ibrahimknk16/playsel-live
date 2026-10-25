<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends MX_Controller
{

	public function __construct()
	{
		userCheck();
		parent::__construct();
	}

	public function paymentReports()
	{
		$validDate = static function ($value, $fallback) {
			$value = trim((string)$value);
			$time = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? strtotime($value) : false;
			return $time ? date('Y-m-d', $time) : $fallback;
		};
		$start = $validDate($this->input->get('start'), date('Y-m-d', strtotime('-29 days')));
		$end = $validDate($this->input->get('end'), date('Y-m-d'));
		if ($start > $end) {
			list($start, $end) = [$end, $start];
		}
		$typeLabels = [
			'payment' => 'Ödeme Yapıldı', 'admin_add' => 'Admin Bakiye Ekleme', 'order' => 'Sipariş Verildi',
			'admin_deduct' => 'Admin Bakiye Düşme', 'withdraw' => 'Çekim / Düşüm', 'pending' => 'Bekleyen Ödeme',
			'cancelled' => 'İptal / Red / İade',
		];
		$type = trim((string)$this->input->get('type'));
		$type = isset($typeLabels[$type]) ? $type : '';
		$query = mb_substr(trim((string)$this->input->get('query')), 0, 80);
		$page = max(1, (int)$this->input->get('page'));
		$limit = 25;

		$dbRows = $this->db->select('ub.user_balance_id, ub.user_balance_user_id, ub.user_balance_amount, ub.user_balance_status, ub.user_balance_order_id, ub.user_balance_admin_id, ub.user_balance_reference_no, ub.user_balance_created_at, u.user_name, u.user_surname, u.user_email, a.admin_name')
			->from('user_balance ub')
			->join('users u', 'u.user_id = ub.user_balance_user_id', 'left')
			->join('admins a', 'a.admin_id = ub.user_balance_admin_id', 'left')
			->where('ub.user_balance_created_at >=', $start . ' 00:00:00')
			->where('ub.user_balance_created_at <=', $end . ' 23:59:59')
			->order_by('ub.user_balance_created_at', 'ASC')->order_by('ub.user_balance_id', 'ASC')
			->limit(20000)->get()->result();

		// Dönem öncesi bakiye: onaylı girişler (1) eksi harcama/düşümler (3).
		$balances = [];
		$userIds = array_values(array_unique(array_map(static function ($row) { return (int)$row->user_balance_user_id; }, $dbRows)));
		if ($userIds) {
			$opening = $this->db->select('user_balance_user_id, SUM(CASE WHEN user_balance_status = 1 THEN user_balance_amount WHEN user_balance_status = 3 THEN -user_balance_amount ELSE 0 END) AS total', false)
				->from('user_balance')->where_in('user_balance_user_id', $userIds)
				->where('user_balance_created_at <', $start . ' 00:00:00')
				->group_by('user_balance_user_id')->get()->result();
			foreach ($opening as $row) {
				$balances[(int)$row->user_balance_user_id] = (float)$row->total;
			}
		}

		$rows = [];
		foreach ($dbRows as $row) {
			$userId = (int)$row->user_balance_user_id;
			$status = (int)$row->user_balance_status;
			$amount = (float)$row->user_balance_amount;
			$adminId = (int)$row->user_balance_admin_id;
			$orderId = (int)$row->user_balance_order_id;
			$before = $balances[$userId] ?? 0.0;
			$delta = $status === 1 ? $amount : ($status === 3 ? -$amount : 0.0);
			$after = $before + $delta;
			$balances[$userId] = $after;

			if ($status === 1) {
				$key = $adminId > 0 ? 'admin_add' : 'payment';
			} elseif ($status === 3) {
				$key = $orderId > 0 ? 'order' : ($adminId > 0 ? 'admin_deduct' : 'withdraw');
			} else {
				$key = $status === 0 ? 'pending' : 'cancelled';
			}
			$name = trim($row->user_name . ' ' . $row->user_surname);
			$reference = $orderId > 0 ? 'Sipariş #' . $orderId : (trim((string)$row->user_balance_reference_no) !== '' ? 'Ödeme #' . (int)$row->user_balance_id . ' · ' . $row->user_balance_reference_no : 'Ödeme #' . (int)$row->user_balance_id);
			$entry = [
				'id' => (int)$row->user_balance_id, 'user_id' => $userId, 'user_name' => $name !== '' ? $name : '-',
				'user_email' => (string)$row->user_email, 'type_key' => $key, 'type' => $typeLabels[$key],
				'reference' => $reference, 'order_id' => $orderId, 'amount' => $amount, 'delta' => $delta,
				'before' => $before, 'after' => $after, 'status' => $status,
				'actor' => $adminId > 0 ? ((string)$row->admin_name !== '' ? (string)$row->admin_name : 'Admin #' . $adminId) : ($orderId > 0 || $status === 3 ? 'Kullanıcı' : 'Sistem'),
				'created_at' => (string)$row->user_balance_created_at,
			];
			if ($type !== '' && $entry['type_key'] !== $type) {
				continue;
			}
			if ($query !== '') {
				$haystack = mb_strtolower(implode(' ', [$entry['id'], $entry['user_id'], $entry['user_name'], $entry['user_email'], $entry['reference'], $entry['order_id']]), 'UTF-8');
				if (mb_strpos($haystack, mb_strtolower($query, 'UTF-8'), 0, 'UTF-8') === false) {
					continue;
				}
			}
			$rows[] = $entry;
		}

		$stats = ['count' => count($rows), 'incoming' => 0.0, 'outgoing' => 0.0, 'users' => count(array_unique(array_column($rows, 'user_id')))];
		foreach ($rows as $entry) {
			if ($entry['delta'] > 0) {
				$stats['incoming'] += $entry['delta'];
			} elseif ($entry['delta'] < 0) {
				$stats['outgoing'] -= $entry['delta'];
			}
		}
		$rows = array_reverse($rows);
		$pages = max(1, (int)ceil(count($rows) / $limit));
		$page = min($page, $pages);

		$data = $this->data ?? [];
		$data['paymentReport'] = [
			'rows' => array_slice($rows, ($page - 1) * $limit, $limit), 'stats' => $stats, 'typeLabels' => $typeLabels,
			'start' => $start, 'end' => $end, 'type' => $type, 'query' => $query, 'page' => $page, 'pages' => $pages,
			'capped' => count($dbRows) >= 20000,
		];
		$this->template->content->view('payment-reports', $data);
		$this->template->publish();
	}

	/**
	 * Provider management list with active balance summary.
	 */
	public function providerbalances()
	{
		$data = $this->data ?? [];

		// Compatibility: if URL is /admin/reports/providerbalances/edit/{id} forward to edit handler
		$seg4 = $this->uri->segment(4);
		$seg5 = $this->uri->segment(5);
		if ($seg4 === 'edit') {
			return $this->providerbalances_edit($seg5 ?? null);
		}

		// Providers
		$data['providers'] = $this->db->select('epin_provider_id, epin_provider_name, epin_provider_currency, epin_provider_created_at')
			->from('epin_providers')
			->get()
			->result_array();

		// Provider-based active balance summary
		$data['providerSummaries'] = [];
		if ($this->db->table_exists('provider_balances')) {
			$providerCol = $this->db->field_exists('provider_balance_provider_id', 'provider_balances') ? 'provider_balance_provider_id' : 'provider_id';
			$currencyCol = $this->db->field_exists('provider_balance_currency', 'provider_balances') ? 'provider_balance_currency' : 'currency';
			$amountCol = $this->db->field_exists('provider_balance_amount', 'provider_balances') ? 'provider_balance_amount' : 'balance_amount';
			$updatedCol = $this->db->field_exists('provider_balance_updated_at', 'provider_balances') ? 'provider_balance_updated_at' : 'updated_at';

			$activeRows = $this->db
				->select($providerCol . ' as provider_id, ' . $currencyCol . ' as currency, SUM(' . $amountCol . ') as net_amount, MAX(' . $updatedCol . ') as last_updated', false)
				->from('provider_balances')
				->group_by([$providerCol, $currencyCol])
				->order_by($providerCol, 'ASC')
				->order_by($currencyCol, 'ASC')
				->get()
				->result_array();

			$summaryMap = [];
			foreach ($activeRows as $row) {
				$providerId = (int)($row['provider_id'] ?? 0);
				if ($providerId <= 0) {
					continue;
				}

				if (!isset($summaryMap[$providerId])) {
					$summaryMap[$providerId] = [
						'currencies' => [],
						'last_updated' => null,
					];
				}

				$summaryMap[$providerId]['currencies'][] = [
					'currency' => $row['currency'] ?? '',
					'net_amount' => (float)($row['net_amount'] ?? 0),
				];

				$currentLast = $summaryMap[$providerId]['last_updated'];
				if ($currentLast === null || (($row['last_updated'] ?? '') > $currentLast)) {
					$summaryMap[$providerId]['last_updated'] = $row['last_updated'] ?? null;
				}
			}

			foreach ($data['providers'] as $provider) {
				$providerId = (int)$provider['epin_provider_id'];
				$data['providerSummaries'][] = [
					'provider_id' => $providerId,
					'provider_name' => $provider['epin_provider_name'],
					'provider_currency' => $provider['epin_provider_currency'] ?? '',
					'provider_created_at' => $provider['epin_provider_created_at'] ?? '',
					'currencies' => $summaryMap[$providerId]['currencies'] ?? [],
					'last_updated' => $summaryMap[$providerId]['last_updated'] ?? null,
				];
			}
		} else {
			foreach ($data['providers'] as $provider) {
				$data['providerSummaries'][] = [
					'provider_id' => (int)$provider['epin_provider_id'],
					'provider_name' => $provider['epin_provider_name'],
					'provider_currency' => $provider['epin_provider_currency'] ?? '',
					'provider_created_at' => $provider['epin_provider_created_at'] ?? '',
					'currencies' => [],
					'last_updated' => null,
				];
			}
		}

		// Lookup map
		$providerMap = [];
		foreach ($data['providers'] as $p) {
			$providerMap[(int)$p['epin_provider_id']] = $p['epin_provider_name'];
		}
		$data['providerMap'] = $providerMap;

		$data['currencies'] = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];

		$this->template->content->view('providers_accounting', $data);
		$this->template->publish();
	}

	public function provider_create()
	{
		$data = $this->data ?? [];
		$data['provider'] = null;
		$data['currencies'] = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];
		$this->template->content->view('provider_management_form', $data);
		$this->template->publish();
	}

	public function provider_store()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			redirect(base_url('reports/providerbalances'));
			return;
		}

		$name = trim((string)$this->input->post('epin_provider_name'));
		$currency = trim((string)$this->input->post('epin_provider_currency'));
		$createdAt = trim((string)$this->input->post('epin_provider_created_at'));
		$currencies = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];

		if ($name === '' || !in_array($currency, $currencies, true)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Tedarikçi adı ve para birimi zorunludur.']);
			redirect(base_url('reports/provider_create'));
			return;
		}

		$insertData = [
			'epin_provider_name' => $name,
			'epin_provider_currency' => $currency,
		];
		if ($this->db->field_exists('epin_provider_created_at', 'epin_providers')) {
			$insertData['epin_provider_created_at'] = $this->normalizeProviderCreatedAt($createdAt);
		}

		if ($this->db->insert('epin_providers', $insertData)) {
			$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Başarılı!', 'message' => 'Tedarikçi eklendi.']);
			redirect(base_url('reports/providerbalances'));
			return;
		}

		$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Tedarikçi eklenemedi.']);
		redirect(base_url('reports/provider_create'));
	}

	public function provider_edit($providerId = null)
	{
		$providerId = (int)$providerId;
		if ($providerId <= 0) {
			redirect(base_url('reports/providerbalances'));
			return;
		}

		$data = $this->data ?? [];
		$data['provider'] = $this->db->select('epin_provider_id, epin_provider_name, epin_provider_currency, epin_provider_created_at')
			->from('epin_providers')
			->where('epin_provider_id', $providerId)
			->get()
			->row_array();
		if (empty($data['provider'])) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Tedarikçi bulunamadı.']);
			redirect(base_url('reports/providerbalances'));
			return;
		}
		$data['currencies'] = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];
		$this->template->content->view('provider_management_form', $data);
		$this->template->publish();
	}

	public function provider_update($providerId = null)
	{
		$providerId = (int)$providerId;
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $providerId <= 0) {
			redirect(base_url('reports/providerbalances'));
			return;
		}

		$name = trim((string)$this->input->post('epin_provider_name'));
		$currency = trim((string)$this->input->post('epin_provider_currency'));
		$currencies = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];

		if ($name === '' || !in_array($currency, $currencies, true)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Tedarikçi adı ve para birimi zorunludur.']);
			redirect(base_url('reports/provider_edit/' . $providerId));
			return;
		}

		$updateData = [
			'epin_provider_name' => $name,
			'epin_provider_currency' => $currency,
		];

		if ($this->db->where('epin_provider_id', $providerId)->update('epin_providers', $updateData)) {
			$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Başarılı!', 'message' => 'Tedarikçi güncellendi.']);
			redirect(base_url('reports/providerbalances'));
			return;
		}

		$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Tedarikçi güncellenemedi.']);
		redirect(base_url('reports/provider_edit/' . $providerId));
	}

	private function normalizeProviderCreatedAt(string $createdAt): string
	{
		if ($createdAt === '') {
			return date('Y-m-d H:i:s');
		}

		$ts = strtotime($createdAt);
		if ($ts === false) {
			return date('Y-m-d H:i:s');
		}

		return date('Y-m-d H:i:s', $ts);
	}

	/**
	 * Provider balance detail page (separate credit/debit lists).
	 * @param int|null $providerId
	 */
	public function providerbalances_provider($providerId = null)
	{
		$providerId = (int)$providerId;
		if ($providerId <= 0) {
			redirect(base_url('reports/providerbalances'));
			return;
		}

		$data = $this->data ?? [];
		$data['provider'] = $this->db->select('epin_provider_id, epin_provider_name')
			->from('epin_providers')
			->where('epin_provider_id', $providerId)
			->get()
			->row_array();

		if (empty($data['provider'])) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Tedarikçi bulunamadı.']);
			redirect(base_url('reports/providerbalances'));
			return;
		}

		$data['credits'] = [];
		$data['debits'] = [];
		$data['netBalances'] = [];
		$data['debitLoadMap'] = [];

		if ($this->db->table_exists('provider_balances')) {
			$providerCol = $this->db->field_exists('provider_balance_provider_id', 'provider_balances') ? 'provider_balance_provider_id' : 'provider_id';
			$currencyCol = $this->db->field_exists('provider_balance_currency', 'provider_balances') ? 'provider_balance_currency' : 'currency';
			$amountCol = $this->db->field_exists('provider_balance_amount', 'provider_balances') ? 'provider_balance_amount' : 'balance_amount';
			$noteCol = $this->db->field_exists('provider_balance_note', 'provider_balances') ? 'provider_balance_note' : 'note';
			$updatedCol = $this->db->field_exists('provider_balance_updated_at', 'provider_balances') ? 'provider_balance_updated_at' : 'updated_at';
			$typeCol = null;
			if ($this->db->field_exists('provider_balance_type', 'provider_balances')) {
				$typeCol = 'provider_balance_type';
			} elseif ($this->db->field_exists('movement_type', 'provider_balances')) {
				$typeCol = 'movement_type';
			}

			$selectCols = 'provider_balance_id, ' . $currencyCol . ' as currency, ' . $amountCol . ' as amount, ' . $noteCol . ' as note, ' . $updatedCol . ' as updated_at';
			if ($typeCol !== null) {
				$selectCols .= ', ' . $typeCol . ' as movement_type';
			}

			$data['netBalances'] = $this->db
				->select($currencyCol . ' as currency, SUM(' . $amountCol . ') as net_amount', false)
				->from('provider_balances')
				->where($providerCol, $providerId)
				->group_by($currencyCol)
				->order_by($currencyCol, 'ASC')
				->get()
				->result_array();

			$this->db->select($selectCols, false)
				->from('provider_balances')
				->where($providerCol, $providerId);
			if ($typeCol !== null) {
				$this->db->where($typeCol, 'credit');
			} else {
				$this->db->where($amountCol . ' >', 0);
			}
			$data['credits'] = $this->db
				->order_by('provider_balance_id', 'DESC')
				->get()
				->result_array();

			$this->db->select($selectCols, false)
				->from('provider_balances')
				->where($providerCol, $providerId);
			if ($typeCol !== null) {
				$this->db->where($typeCol, 'debit');
			} else {
				$this->db->where($amountCol . ' <', 0);
			}
			$data['debits'] = $this->db
				->order_by('provider_balance_id', 'DESC')
				->get()
				->result_array();

			$loadIds = [];
			foreach ($data['debits'] as $row) {
				$note = (string)($row['note'] ?? '');
				if (preg_match('/epin_load_id\s*=\s*(\d+)/i', $note, $matches)) {
					$loadIds[] = (int)$matches[1];
				}
			}
			$loadIds = array_values(array_unique(array_filter($loadIds)));

			if (!empty($loadIds) && $this->db->table_exists('epin_loads')) {
				$loadSelectParts = ['el.epin_load_id'];
				$optionalLoadFields = [
					'epin_load_code',
					'epin_load_pool_id',
					'epin_load_date',
					'epin_load_count',
					'epin_load_admin_id',
					'epin_load_provider_id',
					'epin_load_status',
					'epin_load_purchase_cost',
					'epin_load_purchase_currency',
					'epin_load_purchase_rate',
					'epin_load_purchase_try_amount',
				];
				foreach ($optionalLoadFields as $fieldName) {
					if ($this->db->field_exists($fieldName, 'epin_loads')) {
						$loadSelectParts[] = 'el.' . $fieldName;
					}
				}
				if ($this->db->field_exists('epin_pool_title', 'epin_pool')) {
					$loadSelectParts[] = 'ep.epin_pool_title';
				}

				$loadRows = $this->db
					->select(implode(', ', $loadSelectParts), false)
					->from('epin_loads el')
					->join('epin_pool ep', 'ep.epin_pool_id = el.epin_load_pool_id', 'left')
					->where_in('el.epin_load_id', $loadIds)
					->get()
					->result_array();

				$map = [];
				foreach ($loadRows as $loadRow) {
					$map[(int)$loadRow['epin_load_id']] = $loadRow;
				}
				// Enrich map with admin and provider names when possible (avoid schema changes)
				$adminIds = [];
				$providerIds = [];
				foreach ($map as $id => $lr) {
					if (!empty($lr['epin_load_admin_id'])) $adminIds[] = (int)$lr['epin_load_admin_id'];
					if (!empty($lr['epin_load_provider_id'])) $providerIds[] = (int)$lr['epin_load_provider_id'];
				}
				$adminIds = array_values(array_unique(array_filter($adminIds)));
				$providerIds = array_values(array_unique(array_filter($providerIds)));
				$adminMap = [];
				$providerMap = [];
				if (!empty($adminIds) && $this->db->table_exists('admins')) {
					$rows = $this->db->select('admin_id, admin_name')
						->from('admins')
						->where_in('admin_id', $adminIds)
						->get()
						->result_array();
					foreach ($rows as $r) $adminMap[(int)$r['admin_id']] = $r['admin_name'];
				}
				if (!empty($providerIds) && $this->db->table_exists('epin_providers')) {
					$rows = $this->db->select('epin_provider_id, epin_provider_name')
						->from('epin_providers')
						->where_in('epin_provider_id', $providerIds)
						->get()
						->result_array();
					foreach ($rows as $r) $providerMap[(int)$r['epin_provider_id']] = $r['epin_provider_name'];
				}
				// attach names to entries
				foreach ($map as $id => &$entry) {
					$aid = (int)($entry['epin_load_admin_id'] ?? 0);
					$pid = (int)($entry['epin_load_provider_id'] ?? 0);
					$entry['epin_admin_name'] = $adminMap[$aid] ?? null;
					$entry['epin_provider_name'] = $providerMap[$pid] ?? null;
				}
				unset($entry);
				$data['debitLoadMap'] = $map;
			}
		}

		$this->template->content->view('providers_accounting_provider', $data);
		$this->template->publish();
	}

	/**
	 * New provider balance create screen (manual hold/release flow).
	 */
	public function providerbalances_create()
	{
		$data = $this->data ?? [];
		$data['user'] = $this->session->userdata('admin') ?? [];
		$data['providers'] = $this->db->select('*')
			->from('epin_providers')
			->order_by('epin_provider_name', 'ASC')
			->get()
			->result_array();

		$data['currencies'] = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];

		$this->template->content->view('providers_accounting_create', $data);
		$this->template->publish();
	}

	/**
	 * Store provider balance record (real write operation).
	 * Uses CodeIgniter Query Builder only.
	 */
	public function providerbalances_store()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Hatalı istek.']);
			redirect(base_url('reports/providerbalances_create'));
			return;
		}

		if (!$this->db->table_exists('provider_balances')) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'provider_balances tablosu bulunamadı.']);
			redirect(base_url('reports/providerbalances_create'));
			return;
		}

		$providerId = (int)$this->input->post('provider_id');
		$currency = trim((string)$this->input->post('currency'));
		$amountRaw = trim((string)$this->input->post('amount'));
		$purchaseRateRaw = trim((string)$this->input->post('epin_purchase_rate'));
		$movementType = trim((string)$this->input->post('movement_type'));
		$note = trim((string)$this->input->post('note'));
		$adminSession = $this->session->userdata('admin');
		$adminId = (int)($adminSession['admin_id'] ?? 0);

		$allowedCurrencies = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];
		$allowedMovementTypes = ['credit', 'debit'];
		$amount = str_replace(',', '.', $amountRaw);
		$purchaseRate = (float)str_replace(',', '.', $purchaseRateRaw);

		if ($providerId <= 0 || !in_array($currency, $allowedCurrencies, true) || !in_array($movementType, $allowedMovementTypes, true) || !is_numeric($amount) || $adminId <= 0) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Lütfen zorunlu alanları doğru doldurun.']);
			redirect(base_url('reports/providerbalances_create'));
			return;
		}

		$amountValue = (float)$amount;
		if ($amountValue <= 0) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Tutar 0\'dan büyük olmalıdır.']);
			redirect(base_url('reports/providerbalances_create'));
			return;
		}
		if ($currency === 'TRY') {
			$purchaseRate = 1.0;
		}
		if ($purchaseRate <= 0) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Kur bilgisi 0\'dan büyük olmalıdır.']);
			redirect(base_url('reports/providerbalances_create'));
			return;
		}
		$signedAmount = $movementType === 'debit' ? ($amountValue * -1) : $amountValue;
		$tryAmount = $amountValue * $purchaseRate;

		$providerCol = $this->db->field_exists('provider_balance_provider_id', 'provider_balances') ? 'provider_balance_provider_id' : 'provider_id';
		$currencyCol = $this->db->field_exists('provider_balance_currency', 'provider_balances') ? 'provider_balance_currency' : 'currency';
		$amountCol = $this->db->field_exists('provider_balance_amount', 'provider_balances') ? 'provider_balance_amount' : 'balance_amount';
		$noteCol = $this->db->field_exists('provider_balance_note', 'provider_balances') ? 'provider_balance_note' : 'note';
		$createdCol = $this->db->field_exists('provider_balance_created_at', 'provider_balances') ? 'provider_balance_created_at' : 'created_at';
		$updatedCol = $this->db->field_exists('provider_balance_updated_at', 'provider_balances') ? 'provider_balance_updated_at' : 'updated_at';
		$typeCol = null;
		if ($this->db->field_exists('provider_balance_type', 'provider_balances')) {
			$typeCol = 'provider_balance_type';
		} elseif ($this->db->field_exists('movement_type', 'provider_balances')) {
			$typeCol = 'movement_type';
		}
		$parallelCols = $this->resolveProviderBalanceParallelCols();

		$adminCol = null;
		if ($this->db->field_exists('provider_balance_admin_id', 'provider_balances')) {
			$adminCol = 'provider_balance_admin_id';
		} elseif ($this->db->field_exists('admin_id', 'provider_balances')) {
			$adminCol = 'admin_id';
		} elseif ($this->db->field_exists('created_by', 'provider_balances')) {
			$adminCol = 'created_by';
		}

		$now = date('Y-m-d H:i:s');
		$insertData = [
			$providerCol => $providerId,
			$currencyCol => $currency,
			$amountCol => number_format($signedAmount, 6, '.', ''),
			$noteCol => $note !== '' ? $note : null,
			$createdCol => $now,
			$updatedCol => $now,
		];
		if ($typeCol !== null) {
			$insertData[$typeCol] = $movementType;
		}
		if ($parallelCols['purchase_cost'] !== null) {
			$insertData[$parallelCols['purchase_cost']] = number_format($amountValue, 6, '.', '');
		}
		if ($parallelCols['purchase_currency'] !== null) {
			$insertData[$parallelCols['purchase_currency']] = $currency;
		}
		if ($parallelCols['purchase_rate'] !== null) {
			$insertData[$parallelCols['purchase_rate']] = number_format($purchaseRate, 6, '.', '');
		}
		if ($parallelCols['purchase_try_amount'] !== null) {
			$insertData[$parallelCols['purchase_try_amount']] = number_format($tryAmount, 6, '.', '');
		}
		if ($adminCol !== null) {
			$insertData[$adminCol] = $adminId;
		}

		$ok = $this->db->insert('provider_balances', $insertData);
		if ($ok) {
			$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Başarılı!', 'message' => 'Provider bakiye kaydı eklendi.']);
			redirect(base_url('reports/providerbalances'));
			return;
		}

		$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Kayıt eklenemedi.']);
		redirect(base_url('reports/providerbalances_create'));
	}

	private function resolveProviderBalanceParallelCols(): array
	{
		$map = [
			'load_id' => ['provider_balance_epin_load_id', 'epin_load_id', 'load_id'],
			'purchase_cost' => ['provider_balance_purchase_cost', 'purchase_cost', 'provider_balance_original_amount', 'original_amount'],
			'purchase_currency' => ['provider_balance_purchase_currency', 'purchase_currency', 'provider_balance_original_currency', 'original_currency'],
			'purchase_rate' => ['provider_balance_purchase_rate', 'purchase_rate', 'epin_purchase_rate', 'provider_balance_rate', 'rate'],
			'purchase_try_amount' => ['provider_balance_amount_try', 'provider_balance_purchase_try_amount', 'purchase_try_amount', 'provider_balance_try_amount', 'try_amount'],
		];

		$result = [];
		foreach ($map as $key => $candidates) {
			$result[$key] = null;
			foreach ($candidates as $column) {
				if ($this->db->field_exists($column, 'provider_balances')) {
					$result[$key] = $column;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Provider balance edit view (read-only form for review).
	 * @param int|null $id
	 */
	public function providerbalances_edit($id = null)
	{
		if (empty($id)) {
			redirect(base_url('reports/providerbalances_create'));
			return;
		}

		$data = $this->data ?? [];

		$data['entry'] = null;
		if ($id && $this->db->table_exists('provider_balances')) {
			$data['entry'] = $this->db->select('*')
				->from('provider_balances')
				->where('provider_balance_id', (int)$id)
				->get()
				->row_array();
		}

		// If entry exists and table has an admin column, resolve admin info for display
		$data['entry_admin'] = null;
		if (!empty($data['entry']) && $this->db->table_exists('provider_balances')) {
			// possible admin columns
			$adminCol = null;
			if ($this->db->field_exists('admin_id', 'provider_balances')) $adminCol = 'admin_id';
			elseif ($this->db->field_exists('provider_balance_admin_id', 'provider_balances')) $adminCol = 'provider_balance_admin_id';
			elseif ($this->db->field_exists('created_by', 'provider_balances')) $adminCol = 'created_by';
			if ($adminCol && !empty($data['entry'][$adminCol])) {
				$adminId = (int)$data['entry'][$adminCol];
				$admin = $this->db->select('admin_id, admin_name, admin_email')
					->from('admins')
					->where('admin_id', $adminId)
					->get()
					->row_array();
				if (!empty($admin)) {
					$data['entry_admin'] = $admin;
				}
			}
		}

		$data['providers'] = $this->db->select('epin_provider_id, epin_provider_name')
			->from('epin_providers')
			->order_by('epin_provider_name', 'ASC')
			->get()
			->result_array();

		$data['currencies'] = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];

		$this->template->content->view('providers_accounting_edit', $data);
		$this->template->publish();
	}

	/**
	 * Generate a CodeIgniter Query Builder insert snippet for provider_balances (read-only; does not execute).
	 * Expects POST or GET params: provider_id, currency, amount, note
	 * Admin id is resolved automatically from current logged-in admin session.
	 */
	public function providerbalances_generate_sql()
	{
		$providerId = (int)($this->input->post('provider_id') ?? $this->input->get('provider_id'));
		$currency = $this->input->post('currency') ?? $this->input->get('currency');
		$amount = $this->input->post('amount') ?? $this->input->get('amount');
		$purchaseRate = $this->input->post('epin_purchase_rate') ?? $this->input->get('epin_purchase_rate');
		$note = $this->input->post('note') ?? $this->input->get('note');
		$adminSession = $this->session->userdata('admin');
		$adminId = (int)($adminSession['admin_id'] ?? 0);

		// Basic validation
		$errors = [];
		if ($providerId <= 0) $errors[] = 'Invalid provider_id';
		if (empty($currency)) $errors[] = 'Missing currency';
		if (!is_numeric($amount)) $errors[] = 'Amount must be numeric';
		if ($currency !== 'TRY' && !is_numeric($purchaseRate)) $errors[] = 'Rate must be numeric';
		if ($adminId <= 0) $errors[] = 'Admin session not found';

		if (!empty($errors)) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['ok' => false, 'errors' => $errors]));
		}

				// Build a CodeIgniter Query Builder PHP snippet (no raw SQL, no execution).
				$now = date('Y-m-d H:i:s');
				$rateValue = $currency === 'TRY' ? 1 : (float)$purchaseRate;
				$amountValue = (float)$amount;
				$tryAmount = $amountValue * $rateValue;
				$dataArr = [
						'provider_balance_provider_id' => (int)$providerId,
						'provider_balance_currency' => $currency,
						'provider_balance_amount' => number_format($amountValue, 6, '.', ''),
						'provider_balance_purchase_cost' => number_format($amountValue, 6, '.', ''),
						'provider_balance_purchase_currency' => $currency,
						'provider_balance_purchase_rate' => number_format($rateValue, 6, '.', ''),
						'provider_balance_amount_try' => number_format($tryAmount, 6, '.', ''),
						'provider_balance_note' => $note === null ? null : $note,
						'provider_balance_admin_id' => $adminId,
						'provider_balance_created_at' => $now,
						'provider_balance_updated_at' => $now,
				];

				// Create a readable PHP snippet using var_export for safe values
				$exportLines = [];
				foreach ($dataArr as $k => $v) {
						$exportLines[] = "    '" . $k . "' => " . var_export($v, true) . ",";
				}
				$ciSnippet = "<?php\n";
				$ciSnippet .= "// CodeIgniter Query Builder snippet - run this in a controller/model with proper approval.\n";
				$ciSnippet .= "\$data = [\n" . implode("\n", $exportLines) . "\n];\n";
				$ciSnippet .= "\$this->db->insert('provider_balances', \$data);\n";

				return $this->output
					->set_content_type('application/json')
					->set_output(json_encode(['ok' => true, 'ci_snippet' => $ciSnippet]));
	}

	// Kullanacağımız status seti
	private $statusMap = [
		0  => 'Yeni',
		1  => 'Başarılı',
		2  => 'Başarısız',
		3  => 'Onay Bekliyor',
		4  => 'Onaylandı Bildirim',
		5  => 'Supplier İptal',
		11 => 'Ödeme Bekliyor',
		12 => 'Ödeme Başarısız',
	];

	// Ürün adını çekmek için (orders.order_external_product_id -> provider_products.provider_product_product_id)
	// farklı provider için değiştir.

	public $data;

	/**
	 * Payment method summary report.
	 */
	public function paymentMethods()
	{
		$data = $this->data ?? [];
		$validDate = static function ($value, $fallback) {
			$value = trim((string)$value);
			$time = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? strtotime($value) : false;
			return $time ? date('Y-m-d', $time) : $fallback;
		};
		$start = $validDate($this->input->get('start'), date('Y-m-d', strtotime('-29 days')));
		$end = $validDate($this->input->get('end'), date('Y-m-d'));
		if ($start > $end) {
			list($start, $end) = [$end, $start];
		}
		// Sorgu yükünü sınırlamak için en fazla 366 günlük aralık.
		if (strtotime($end) - strtotime($start) > 366 * 86400) {
			$start = date('Y-m-d', strtotime($end . ' -366 days'));
		}

		$method = (string)$this->input->get('method');
		$method = in_array($method, ['card', 'bank', 'mobile'], true) ? $method : 'all';
		$status = (string)$this->input->get('status');
		$status = in_array($status, ['success', 'failed', 'pending'], true) ? $status : '';
		$bank = trim((string)$this->input->get('bank'));
		$query = mb_strtolower(trim((string)$this->input->get('query')), 'UTF-8');

		$all = $this->paymentMethodRows($start . ' 00:00:00', $end . ' 23:59:59');

		$banks = [];
		foreach ($all as $row) {
			if ($row['method'] === 'bank' && $row['bank'] !== '') {
				$banks[$row['bank']] = true;
			}
		}
		ksort($banks);

		$methodCounts = ['all' => 0, 'card' => 0, 'bank' => 0, 'mobile' => 0];
		$rows = [];
		foreach ($all as $row) {
			if ($status !== '' && $row['status'] !== $status) {
				continue;
			}
			if ($bank !== '' && $row['bank'] !== $bank) {
				continue;
			}
			if ($query !== '' && mb_strpos(mb_strtolower($row['id'] . ' ' . $row['email'], 'UTF-8'), $query) === false) {
				continue;
			}
			$methodCounts['all']++;
			$methodCounts[$row['method']]++;
			if ($method === 'all' || $row['method'] === $method) {
				$rows[] = $row;
			}
		}

		$summary = ['count' => count($rows), 'success' => 0, 'failed' => 0, 'pending' => 0, 'success_amount' => 0.0, 'failed_amount' => 0.0, 'pending_amount' => 0.0];
		$daily = [];
		$bankSummary = [];
		foreach ($rows as $row) {
			$summary[$row['status']]++;
			$summary[$row['status'] . '_amount'] += $row['amount'];
			$day = substr($row['date'], 0, 10);
			if (!isset($daily[$day])) {
				$daily[$day] = ['success' => 0.0, 'failed' => 0.0];
			}
			if ($row['status'] === 'success' || $row['status'] === 'failed') {
				$daily[$day][$row['status']] += $row['amount'];
			}
			if ($row['method'] === 'bank' && $row['status'] === 'success') {
				$name = $row['bank'] !== '' ? $row['bank'] : 'Banka belirtilmemiş';
				$bankSummary[$name] = $bankSummary[$name] ?? ['count' => 0, 'amount' => 0.0];
				$bankSummary[$name]['count']++;
				$bankSummary[$name]['amount'] += $row['amount'];
			}
		}
		ksort($daily);
		uasort($bankSummary, static function ($a, $b) {
			return $b['amount'] <=> $a['amount'];
		});

		$data['filters'] = ['start' => $start, 'end' => $end, 'method' => $method, 'status' => $status, 'bank' => $bank, 'query' => (string)$this->input->get('query')];
		$data['summary'] = $summary;
		$data['daily'] = $daily;
		$data['bankSummary'] = $bankSummary;
		$data['banks'] = array_keys($banks);
		$data['methodCounts'] = $methodCounts;

		$this->template->content->view('payment-methods', $data);
		$this->template->publish();
	}

	/**
	 * Sipariş ödemeleri (kart/havale/mobil) ve bakiye yüklemelerini ortak formatta döndürür.
	 * Durum: success | failed | pending. Cüzdan ile yapılan sipariş ve harcama/iade hareketleri dahil değildir.
	 */
	private function paymentMethodRows($from, $to)
	{
		$rows = [];
		$orderMethods = [3 => 'bank', 4 => 'card', 5 => 'mobile'];
		$orders = $this->db
			->select('o.order_id, o.order_payment_type, o.order_status, o.order_sale_price, o.order_created_at, u.user_email')
			->from('orders o')
			->join('users u', 'u.user_id = o.order_reseller_id', 'left')
			->where_in('o.order_payment_type', array_keys($orderMethods))
			->where('o.order_created_at >=', $from)
			->where('o.order_created_at <=', $to)
			->get()->result();
		foreach ($orders as $o) {
			$st = (int)$o->order_status;
			$rows[] = [
				'id' => 'S' . (int)$o->order_id,
				'method' => $orderMethods[(int)$o->order_payment_type],
				'bank' => '',
				'email' => (string)$o->user_email,
				'amount' => (float)$o->order_sale_price,
				'status' => in_array($st, [2, 12], true) ? 'failed' : (in_array($st, [0, 11, 13], true) ? 'pending' : 'success'),
				'date' => (string)$o->order_created_at,
			];
		}

		$balances = $this->db
			->select('ub.user_balance_id, ub.user_balance_bank_id, ub.user_balance_amount, ub.user_balance_status, ub.user_balance_note, COALESCE(ub.user_balance_created_at, ub.user_balance_payment_date) AS paid_at, u.user_email, b.bank_name', false)
			->from('user_balance ub')
			->join('users u', 'u.user_id = ub.user_balance_user_id', 'left')
			->join('banks b', 'b.bank_id = ub.user_balance_bank_id', 'left')
			->where('ub.user_balance_order_id IS NULL', null, false)
			->where_in('ub.user_balance_status', [0, 1, 2])
			->where('COALESCE(ub.user_balance_created_at, ub.user_balance_payment_date) >=', $from)
			->where('COALESCE(ub.user_balance_created_at, ub.user_balance_payment_date) <=', $to)
			->get()->result();
		$statusMap = [0 => 'pending', 1 => 'success', 2 => 'failed'];
		foreach ($balances as $b) {
			$note = (string)$b->user_balance_note;
			if ((int)$b->user_balance_bank_id > 0) {
				$method = 'bank';
			} elseif (stripos($note, 'Mobil') !== false || strpos($note, '"payment_method":"mobile"') !== false) {
				$method = 'mobile';
			} elseif (stripos($note, 'Kredi') !== false || strpos($note, '"payment_method":"cc"') !== false) {
				$method = 'card';
			} else {
				$method = 'bank';
			}
			$rows[] = [
				'id' => 'B' . (int)$b->user_balance_id,
				'method' => $method,
				'bank' => $method === 'bank' ? trim((string)$b->bank_name) : '',
				'email' => (string)$b->user_email,
				'amount' => (float)$b->user_balance_amount,
				'status' => $statusMap[(int)$b->user_balance_status],
				'date' => (string)$b->paid_at,
			];
		}

		return $rows;
	}

	public function index()
	{
		$data = $this->data ?? [];
		$filters = $this->getFilters();

		// Toplam özet
		$data['summary'] = $this->getTotals($filters);

		// Ürün bazlı özet
		$data['productRows'] = $this->getProductReport($filters, 500);
		//son sql sorgusunu yaz
		//echo $this->db->last_query();die;
		//productID ve productName filtrele
		$reportedProducts = array_filter($data['productRows'], function ($item) use ($filters) {
			if (!empty($filters['product_id']) && $item['order_product_id'] != $filters['product_id']) {
				return false;
			}
			if (!empty($filters['external_product_id']) && $item['order_external_product_id'] != $filters['external_product_id']) {
				return false;
			}
			return true;
		});
		$reportedProducts = array_map(function ($item) {
			return [
				'order_product_id'   => $item['order_product_id'],
				'product_name'       => $item['product_name'],
			];
		}, $reportedProducts);
		$reportedProducts = array_unique($reportedProducts, SORT_REGULAR);
		$data['reportedProducts'] = $reportedProducts;

		// UI için
		$data['filters']   = $filters;
		$data['statusMap'] = $this->statusMap;
		$data['resellers'] = $this->db->select('user_id, user_name')
			->from('users')
			->where('user_type', 3)
			->get()->result_array();
		$data['suppliers'] = $this->db->select('user_id, user_name')
			->from('users')
			->where('user_type', 2)
			->get()->result_array();
		$data['categories'] = $this->db->select('category_id, category_title')
			->from('categories')
			->get()->result_array();
		// pre($data['resellers']);
		// pre($data['suppliers']);
		// die;
		// View
		$this->template->content->view('reports', $data);
		$this->template->publish();
	}

	public function index2()
	{
		$data = $this->data ?? [];
		$filters = $this->getFilters();

		// Toplam özet
		$data['summary'] = $this->getTotals($filters);

		// Ürün bazlı özet
		$data['productRows'] = $this->getProductReport($filters, 500);
		//son sql sorgusunu yaz
		//echo $this->db->last_query();die;
		//productID ve productName filtrele
		$reportedProducts = array_filter($data['productRows'], function ($item) use ($filters) {
			if (!empty($filters['product_id']) && $item['order_product_id'] != $filters['product_id']) {
				return false;
			}
			if (!empty($filters['external_product_id']) && $item['order_external_product_id'] != $filters['external_product_id']) {
				return false;
			}
			return true;
		});
		$reportedProducts = array_map(function ($item) {
			return [
				'order_product_id'   => $item['order_product_id'],
				'product_name'       => $item['product_name'],
			];
		}, $reportedProducts);
		$reportedProducts = array_unique($reportedProducts, SORT_REGULAR);
		$data['reportedProducts'] = $reportedProducts;

		// UI için
		$data['filters']   = $filters;
		$data['statusMap'] = $this->statusMap;
		$data['resellers'] = $this->db->select('user_id, user_name')
			->from('users')
			->where('user_type', 3)
			->get()->result_array();
		$data['suppliers'] = $this->db->select('user_id, user_name')
			->from('users')
			->where('user_type', 2)
			->get()->result_array();
		$data['categories'] = $this->db->select('category_id, category_title')
			->from('categories')
			->get()->result_array();

		// pre($data['productRows']);
		// die;
		/*
		[0] => Array
			(
				[category_title] => Mobile Legends (TR) Top-Up
				[product_category_id] => 36
				[product_name] => Mobile Legends TR Weekly Pass
				[order_product_id] => 15325
				[order_external_product_id] => 
				[order_count] => 6047
				[total_qty] => 6047
				[total_cost_tl] => 439556.43
				[total_sale] => 441733.35
				[profit] => 2176.92
			)
			ÜrunAdi	Varyant	KDVOranı	Miktar	Birim	İskonto/İndirim Tipi	Fatura bazlı/Ürün bazlı	Yüzde/Tutar	iskonto/indirim miktarı	KDVDahilBirimFiyatı // buna göre gereken yerleri boş bırakarak tablo oluşturaralım
			
		*/
		echo '<table border="1" cellpadding="5" cellspacing="0">';
		echo '
		<tr>
		<th>ÜrunAdi</th>
		<th>Varyant</th>
		<th>KDVOranı</th>
		<th>Miktar</th>
		<th>Birim</th>
		<th>İskonto/İndirim Tipi</th>
		<th>İskonto/İndirim Tipi</th>
		<th>İskonto/İndirim Tipi</th>
		<th>Profit</th>
		<th>Para Birimi</th>
		</tr>';
		foreach ($data['productRows'] as $row) {
			echo '<tr>';
			echo '<td>' . $row['product_name'] . '</td>';
			echo '<td></td>';
			echo '<td>0</td>';
			echo '<td>' . $row['total_qty'] . '</td>';
			echo '<td>Adet</td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td>' . number_format($row['profit'], 2) . '</td>';
			echo '<td>TRY</td>';
			echo '</tr>';
		}
		echo '</table>';
	}

	// ---------------- Export CSV ----------------
	public function export_csv()
	{
		die;
		$filters = $this->getFilters();
		$rows    = $this->getProductReport($filters, 0); // limitsiz

		$filename = 'product_report_' . date('Ymd_His') . '.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header("Content-Disposition: attachment; filename=\"$filename\"");
		$out = fopen('php://output', 'w');

		// header
		fputcsv($out, [
			'Product ID',
			'External Product ID',
			'Product Name',
			'Category',
			'Order Count',
			'Total Quantity',
			'Total Cost TL',
			'Total Sale',
			'Profit'
		]);

		foreach ($rows as $r) {
			fputcsv($out, [
				$r['order_product_id'],
				$r['order_external_product_id'],
				$r['product_name'],
				$r['category_name'],
				$r['order_count'],
				$r['total_qty'],
				number_format($r['total_cost_tl'], 2, '.', ''),
				number_format($r['total_sale'],  2, '.', ''),
				number_format($r['profit'],      2, '.', ''),
			]);
		}
		fclose($out);
		exit;
	}

	// -------------- Helpers --------------

	// GET filtrelerini topla (default: son 7 gün)
	private function getFilters(): array
	{
		$g = $this->input->get();

		// Tarih aralığı
		$start_date = !empty($g['start_date']) ? date('Y-m-d', strtotime($g['start_date'])) : date('Y-m-d');
		$end_date   = !empty($g['end_date'])   ? date('Y-m-d', strtotime($g['end_date']))   : date('Y-m-d');

		// Saat ekle
		$start_dt = $start_date . ' 00:00:00';
		$end_dt   = $end_date   . ' 23:59:59';

		// Status
		$statuses = [];
		if (!empty($g['status']) && is_array($g['status'])) {
			foreach ($g['status'] as $s) {
				$s = (int)$s;
				if (isset($this->statusMap[$s])) $statuses[] = $s;
			}
		}
		if (empty($statuses)) {
			$statuses = array_keys($this->statusMap); // hepsi
		}

		// Diğerleri
		$filters = [
			'start'   => $start_dt,
			'end'     => $end_dt,
			'status'  => $statuses,
			'reseller_id'          => isset($g['reseller_id']) ? (int)$g['reseller_id'] : null,
			'supplier_id'          => isset($g['supplier_id']) ? (int)$g['supplier_id'] : null,
			'site_id'              => isset($g['site_id'])     ? (int)$g['site_id']     : null,
			'product_id'           => isset($g['product_id'])  ? (int)$g['product_id']  : null,
			'category_id'  		   => isset($g['category_id']) ? (int)$g['category_id'] : null,
		];

		return $filters;
	}

	// Query builder'a filtreleri uygula
	private function applyFilters(CI_DB_query_builder $qb, array $f): CI_DB_query_builder
	{
		$qb->where('o.order_created_at >=', $f['start'])
			->where('o.order_created_at <=', $f['end'])
			->where_in('o.order_status', $f['status']);

		if (!empty($f['reseller_id']))         $qb->where('o.order_reseller_id',        $f['reseller_id']);
		if (!empty($f['supplier_id']))         $qb->where('o.order_supplier_id',        $f['supplier_id']);
		if (!empty($f['site_id']))             $qb->where('o.order_site_id',            $f['site_id']);
		if (!empty($f['product_id']))          $qb->where('o.order_product_id',         $f['product_id']);
		if (!empty($f['category_id'])) 		   $qb->where('p.product_category_id', $f['category_id']);

		return $qb;
	}

	// Toplam özet
	private function getTotals(array $f): array
	{
		$row = $this->applyFilters(
			$this->db->select("
				COUNT(DISTINCT o.order_id) AS order_count,
				COALESCE(SUM(o.order_quantity), 0) AS total_qty,
				COALESCE(SUM(COALESCE(o.order_cost,0)),0) AS total_cost_tl,
				COALESCE(SUM(COALESCE(o.order_sale_price,0)),0) AS total_sale
			", false)
				->join('products p', 'p.product_id = o.order_product_id', 'left')
				->from('orders o'),
			$f
		)->get()->row_array();


		$orders   = (int)   ($row['order_count'] ?? 0);
		$qty      = (int)   ($row['total_qty']    ?? 0);
		$cost_tl  = (float) ($row['total_cost_tl'] ?? 0);
		$sale     = (float) ($row['total_sale']   ?? 0);
		$profit   = $sale - $cost_tl;

		return [
			'order_count' => $orders,
			'total_qty'   => $qty,
			'total_cost_tl' => $cost_tl,
			'total_sale'    => $sale,
			'profit'        => $profit,
		];
	}

	// Ürün bazlı özet
	private function getProductReport(array $f, int $limit = 500): array
	{
		$qb = $this->db->select("
			c.category_title,
			p.product_category_id,
			p.product_name,
			o.order_product_id,
			o.order_external_product_id,
			COUNT(*) AS order_count,
			COALESCE(SUM(o.order_quantity), 0) AS total_qty,
			COALESCE(SUM(o.order_cost), 0) AS total_cost_tl,
			COALESCE(SUM(o.order_sale_price), 0) AS total_sale
		", false)
			->where('o.order_status', 1)
			->from('orders o')
			->join('products p', 'p.product_id = o.order_product_id', 'left')
			->join('categories c', 'c.category_id = p.product_category_id', 'left');

		$qb = $this->applyFilters($qb, $f);

		$qb->group_by([
			'o.order_product_id'
		])
			->order_by('total_sale', 'DESC');

		if ($limit > 0) $qb->limit($limit);

		$rows = $qb->get()->result_array();
		// pre($rows);
		// die;

		// Profit hesapla
		foreach ($rows as &$r) {
			$r['total_cost_tl'] = (float)$r['total_cost_tl'];
			$r['total_sale']    = (float)$r['total_sale'];
			$r['profit']        = $r['total_sale'] - $r['total_cost_tl'];
		}
		unset($r);
		// sipariş adedine göre en fazla satıştan en aza sırala
		usort($rows, function ($a, $b) {
			return $b['order_count'] <=> $a['order_count'];
		});

		return $rows;
	}

	public function checkoutOrders()
	{
		$orders = $this->db->select('*')
			//$orders = $this->db->select('orders.* , SUM(order_sale_price) as total_sale_price')
			->from('orders')
			->where('order_status', 1)
			->where('order_site_id', 2)
			// 1 september - 8 september 2025
			->join('products', 'products.product_id = orders.order_product_id', 'left')
			->where('order_created_at >=', '2025-09-01 00:00:00')
			->where('order_created_at <=', '2025-09-08 23:59:59')
			->get()->result();
		// pre($orders);
		// die;
		$total = 0;
		// foreach ($orders as $order) {
		// 	// echo $order->order_id . '<br>';
		// 	// echo $order->order_sale_price . '<br>';
		// 	// echo $order->order_sale_price_currency . '<br>';
		// 	// echo '<hr>';
		// 	$total += $order->order_sale_price;
		// }
		$total = array_sum(array_column($orders, 'order_sale_price'));
		// tarih aralığı tr formatında
		echo 'Tarih Aralığı: <br>01 Eylül 2025 - 08 Eylül 2025<br>';
		echo 'Adet: ' . count($orders) . '<br>';
		echo 'USD: ' . $total . '<br>';
		$try = currencyCalculator('USD', $total);
		echo 'TRY: ' . currencyCalculator('USD', $total) . '<br>';
		echo 'AED: ' . currencyCalculatorTo('AED', $try) . '<br>';

		// orders aynı ürünleri grupla ve tablo oluştur
		$grouped = [];
		foreach ($orders as $order) {
			$key = $order->order_product_id . '|' . $order->order_external_product_id;
			if (!isset($grouped[$key])) {
				$grouped[$key] = [
					'product_id' => $order->order_product_id,
					'product_name' => $order->product_name,
					'count' => 0,
					'total_sale_price' => 0,
				];
			}
			$grouped[$key]['count'] += 1;
			$grouped[$key]['total_sale_price'] += $order->order_sale_price;
		}
		echo '<hr>';
		echo '<table border="1" cellpadding="5" cellspacing="0">';
		echo '<tr><th>Product Name</th><th>Adet</th><th>Total Sale Price (USD)</th></tr>';
		foreach ($grouped as $item) {
			echo '<tr>';
			echo '<td>' . $item['product_name'] . '</td>';
			echo '<td>' . $item['count'] . '</td>';
			echo '<td>' . number_format($item['total_sale_price'], 2) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}

	public function exportMoontonReport()
	{

		$get = $this->input->get();
		if (!empty($get['startDate'])) {
			$reportStart = $get['startDate'] . ' 00:00:00';
			$reportEnd = $get['endDate'] . ' 23:59:59';
		} else {
			$reportStart = '2025-11-11 00:00:00';
			$reportEnd = date('Y-m-d H:i:s');
		}

		$providerId = $get['providerID'];

		// Moonton MLBB ürünlerini çek
		$productIDS = $this->db->select('provider_product_id, p.product_id')
			->from('provider_products')
			->where('provider_product_provider_id', $providerId)
			->join('products as p', 'p.product_provider_id = provider_product_id', 'left')
			->get()
			->result();

		$productIDS = array_map(function ($item) {
			return $item->product_id;
		}, $productIDS);

		if (empty($productIDS)) {
			echo "Ürün bulunamadı.";
			return;
		}

		// İlgili siparişleri çek
		$orders = $this->db
			->select('o.order_require_data as requires, o.order_supplier_order_id as transactionID, o.order_created_at as orderDatetime, p.product_name as productName')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->where_in('o.order_product_id', $productIDS)
			->where('o.order_status', 1)
			->where('o.order_created_at >=', $reportStart)
			->where('o.order_created_at <=', $reportEnd)
			->order_by('o.order_created_at', 'ASC')
			->get()
			->result();

		if (empty($orders)) {
			echo "Raporlanacak sipariş bulunamadı.";
			return;
		}

		// Dosya adı
		$filename = 'moonton_mlbb_report_' . date('Y-m-d_H-i-s') . '.csv';

		// Header’lar (dosya indirilsin)
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename);

		// Output stream aç
		$output = fopen('php://output', 'w');

		// Başlık satırı
		fputcsv($output, ['Transaction ID', 'Product Name', 'Requires Data', 'Order Datetime']);

		// Verileri yaz
		foreach ($orders as $order) {
			fputcsv($output, [
				$order->transactionID,
				$order->productName,
				$order->requires,
				$order->orderDatetime
			]);
		}

		fclose($output);
		exit;
	}

	public function moontonReport()
	{
		$data = $this->data;

		$get = $this->input->get();

		if (!empty($get['startDate'])) {
			$reportStart = $get['startDate'] . ' 00:00:00';
			$reportEnd   = $get['endDate'] . ' 23:59:59';
			$exchangeRate = isset($get['exchangeRate']) ? floatval($get['exchangeRate']) : 43.40;
		} else {
			$reportStart = date('Y-m-d') . ' 00:00:00';
			$reportEnd   = date('Y-m-d H:i:s');
			$exchangeRate = 43.40;
		}


		if (!empty($get['providerID'])) {
			$providerId = $get['providerID'];
		} else {
			$providerId = '9132'; // 9156 Moonton Magic Chess Go GO / 9132 Moonton MLBB / 9002 Bangjeff
		}

		// resellerID filtre
		if (!empty($get['resellerID'])) {
			$resellerId = $get['resellerID'];
		} else {
			$resellerId = null;
		}

		if ($providerId == '9132') {
			$data['providerName'] = 'Moonton MLBB';
		} elseif ($providerId == '9156') {
			$data['providerName'] = 'Moonton Magic Chess Go GO';
		} elseif ($providerId == '9002') {
			$data['providerName'] = 'Bangjeff';
		} elseif ($providerId == '9054') {
			$data['providerName'] = 'Pinabi';
		} else {
			$data['providerName'] = 'Unknown Provider';
		}

		$data['providerId'] = $providerId;

		// 1. ürün id'leri
		$productRows = $this->db->select('provider_product_id, p.product_id')
			->from('provider_products')
			->where('provider_product_provider_id', $providerId)
			->join('products as p', 'p.product_provider_id = provider_product_id', 'left')
			->order_by('p.product_id', 'ASC')
			->get()->result();

		$productIDs = array_column($productRows, 'product_id');

		if (empty($productIDs)) {
			$data['error'] = "No products found for provider $providerId";
			$this->template->content->view('report', $data);
			$this->template->publish();
			return;
		}

		// 2. Siparişler
		$orders = $this->db->select('
        o.order_id,
        o.order_cost_tl,
        o.order_require_data as requires,
        o.order_supplier_order_id as transactionID,
        o.order_created_at as orderDatetime,
        p.product_name as productName,
		p.product_id as product_id,
        pp.provider_product_product_id as providerProductID,
		pp.provider_product_product_cost
    	')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('provider_products as pp', 'pp.provider_product_id = p.product_provider_id', 'left')
			->where('pp.provider_product_provider_id', $providerId);
		if (!empty($resellerId)) {
			$this->db->where('o.order_reseller_id', $resellerId);
		}
		$orders = $this->db
			->where('o.order_status', 1)
			->where('o.order_created_at >=', $reportStart)
			->where('o.order_created_at <=', $reportEnd)
			//->order_by('p.product_id', 'ASC')
			->order_by('o.order_id', 'ASC')
			->get()->result();

		if (empty($orders)) {
			$data['error'] = "No completed orders found in the selected period.";
			$this->template->content->view('report', $data);
			$this->template->publish();
			return;
		}

		$firstOrder = $orders[0];
		$lastOrder  = end($orders);

		$quantityReport = [];
		$productCostTotal = [];
		$productSortIndex = [];
		$totalCost = 0;
		//pre($orders);die;
		foreach ($orders as $order) {
			$productName  = str_replace('Elmas', 'Diamonds', $order->productName);
			$productLabel = $productName . ' (' . $order->providerProductID . ')';

			if (!isset($quantityReport[$productLabel])) {
				$quantityReport[$productLabel] = 0;
				$productCostTotal[$productLabel] = 0;

				// 🔥 ürün sırasını kaydet (ilk karşılaşılan olarak)
				$productSortIndex[$productLabel] = $order->product_id;
			}

			$quantityReport[$productLabel]++;
			$productCostTotal[$productLabel] += $order->provider_product_product_cost;
			$totalCost += $order->provider_product_product_cost;
		}

		uksort($quantityReport, function ($a, $b) use ($productSortIndex) {
			return $productSortIndex[$a] <=> $productSortIndex[$b];
		});

		$data['resellers'] = $this->db->select('user_id, user_name')
			->from('users')
			->where('user_type', 3)
			->get()->result_array();
		// 4. view'e aktarılacak tüm veriler
		$data['resellerID']        = $resellerId;
		$data['orders']              = $orders;
		$data['quantityReport']      = $quantityReport;
		$data['productCostTotal']    = $productCostTotal;
		$data['totalCost']           = $totalCost;
		$data['totalQuantity']       = array_sum($quantityReport);
		$data['reportStart']         = $reportStart;
		$data['reportEnd']           = $reportEnd;
		$data['firstOrder']          = $firstOrder;
		$data['lastOrder']           = $lastOrder;
		$data['exchangeRate']        = $exchangeRate;

		$data['totalUSD_fixedRate'] = ($totalCost / $exchangeRate) / 1.02 * 0.82;

		$data['get'] = $get;

		// 5. view
		$this->template->content->view('report', $data);
		$this->template->publish();
	}

	public function categoryHourChart()
	{
		$categoryID = $this->uri->segment(3);
		$post = $this->input->post();

		$startDate = date('Y-m-d');
		$endDate = date('Y-m-d');

		if (!empty($post['startDate'])) {
			$startTimestamp = strtotime($post['startDate']);
			if ($startTimestamp !== false) {
				$startDate = date('Y-m-d', $startTimestamp);
			}
		}

		if (!empty($post['endDate'])) {
			$endTimestamp = strtotime($post['endDate']);
			if ($endTimestamp !== false) {
				$endDate = date('Y-m-d', $endTimestamp);
			}
		}

		if (strtotime($startDate) > strtotime($endDate)) {
			$temp = $startDate;
			$startDate = $endDate;
			$endDate = $temp;
		}

		$reportStart = $startDate . ' 00:00:00';
		$reportEnd = $endDate . ' 23:59:59';

		// 1. Join ile sorguyu tek bir SQL cümlesine indirgiyoruz. 
		// PHP'de array_column ve döngü ile uğraşmak, 10.000+ ürün varsa sunucuyu yorar.
		$rows = $this->db->select("HOUR(o.order_created_at) as hour, COUNT(o.order_id) as total_sales")
			->from('orders o')
			->join('products p', 'p.product_id = o.order_product_id', 'inner')
			->where('p.product_category_id', $categoryID)
			->where('o.order_status', 1)
			->where('o.order_created_at >=', $reportStart)
			->where('o.order_created_at <=', $reportEnd)
			->group_by('HOUR(o.order_created_at)')
			->get()
			->result_array();
		
		

		// 2. Grafiğin boş çıkmaması için 0-23 arası tüm saatleri dolduruyoruz.
		// Bu, frontend tarafında (Chart.js vs.) boşluksuz bir grafik çizilmesini sağlar.
		$chartData = array_fill(0, 24, 0);
		foreach ($rows as $row) {
			$chartData[(int)$row['hour']] = (int)$row['total_sales'];
		}

		$totalSales = (int)array_sum($chartData);
		$peakSales = (int)max($chartData);
		$peakHour = (int)array_search($peakSales, $chartData, true);

		$activeHours = 0;
		foreach ($chartData as $hourSales) {
			if ((int)$hourSales > 0) {
				$activeHours++;
			}
		}

		$averagePerHour = $totalSales / 24;
		$averageActiveHour = $activeHours > 0 ? ($totalSales / $activeHours) : 0;

		$productHourlyRows = $this->db->select("p.product_id, p.product_name, HOUR(o.order_created_at) as hour, COUNT(o.order_id) as total_sales")
			->from('orders o')
			->join('products p', 'p.product_id = o.order_product_id', 'inner')
			->where('p.product_category_id', $categoryID)
			->where('o.order_status', 1)
			->where('o.order_created_at >=', $reportStart)
			->where('o.order_created_at <=', $reportEnd)
			->group_by(['p.product_id', 'p.product_name', 'HOUR(o.order_created_at)'])
			->order_by('p.product_name', 'ASC')
			->get()
			->result_array();

		$productHourlyReports = [];
		foreach ($productHourlyRows as $row) {
			$productId = (int)($row['product_id'] ?? 0);
			$hour = (int)($row['hour'] ?? 0);
			$sales = (int)($row['total_sales'] ?? 0);

			if (!isset($productHourlyReports[$productId])) {
				$productHourlyReports[$productId] = [
					'product_id' => $productId,
					'product_name' => $row['product_name'] ?? '-',
					'hours' => array_fill(0, 24, 0),
					'total_sales' => 0,
					'peak_hour' => 0,
					'peak_sales' => 0,
					'active_hour_count' => 0,
					'average_per_active_hour' => 0,
					'sales_rate' => 0,
				];
			}

			if ($hour >= 0 && $hour < 24) {
				$productHourlyReports[$productId]['hours'][$hour] = $sales;
				$productHourlyReports[$productId]['total_sales'] += $sales;

				if ($sales > $productHourlyReports[$productId]['peak_sales']) {
					$productHourlyReports[$productId]['peak_sales'] = $sales;
					$productHourlyReports[$productId]['peak_hour'] = $hour;
				}
			}
		}

		$productHourlyReports = array_values($productHourlyReports);
		foreach ($productHourlyReports as &$productReport) {
			$activeHourCount = 0;
			foreach ($productReport['hours'] as $hourSales) {
				if ((int)$hourSales > 0) {
					$activeHourCount++;
				}
			}

			$productReport['active_hour_count'] = $activeHourCount;
			$productReport['average_per_active_hour'] = $activeHourCount > 0
				? ($productReport['total_sales'] / $activeHourCount)
				: 0;
			$productReport['sales_rate'] = $totalSales > 0
				? (($productReport['total_sales'] / $totalSales) * 100)
				: 0;
		}
		unset($productReport);

		usort($productHourlyReports, function ($a, $b) {
			return ($b['total_sales'] ?? 0) <=> ($a['total_sales'] ?? 0);
		});

		$topProducts = array_slice($productHourlyReports, 0, 8);
		$top3Sales = 0;
		for ($i = 0; $i < 3; $i++) {
			if (!isset($productHourlyReports[$i])) {
				break;
			}
			$top3Sales += (int)$productHourlyReports[$i]['total_sales'];
		}

		$topProduct = $productHourlyReports[0] ?? null;
		$topProductName = $topProduct['product_name'] ?? '-';
		$topProductSales = (int)($topProduct['total_sales'] ?? 0);
		$topProductShare = (float)($topProduct['sales_rate'] ?? 0);
		$totalProducts = count($productHourlyReports);
		$top3Share = $totalSales > 0 ? (($top3Sales / $totalSales) * 100) : 0;

		$hourLabels = [];
		$hourLabelsChart = [];
		for ($i = 0; $i < 24; $i++) {
			$label = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
			$hourLabels[] = $label;
			$hourLabelsChart[] = $label . ':00';
		}

		$topProductStatCards = [];
		foreach ($topProducts as $index => $stat) {
			$statRate = (float)($stat['sales_rate'] ?? 0);
			$topProductStatCards[] = [
				'rank' => $index + 1,
				'product_name' => $stat['product_name'] ?? '-',
				'product_id_text' => (string)((int)($stat['product_id'] ?? 0)),
				'sales_text' => number_format((int)($stat['total_sales'] ?? 0), 0, ',', '.'),
				'rate_text' => number_format($statRate, 1, ',', '.'),
				'rate_width_text' => number_format(min(100, max(0, $statRate)), 2, '.', ''),
			];
		}

		$productHourlyChartCards = [];
		$productHourlyChartSeries = [];
		foreach ($productHourlyReports as $index => $report) {
			$canvasId = 'productHourlyChart_' . $index;
			$productHourlyChartCards[] = [
				'canvas_id' => $canvasId,
				'product_name' => $report['product_name'] ?? '-',
				'product_id_text' => (string)((int)($report['product_id'] ?? 0)),
				'total_sales_text' => number_format((int)($report['total_sales'] ?? 0), 0, ',', '.'),
				'sales_rate_text' => number_format((float)($report['sales_rate'] ?? 0), 1, ',', '.'),
				'peak_hour_text' => str_pad((string)((int)($report['peak_hour'] ?? 0)), 2, '0', STR_PAD_LEFT) . ':00',
				'peak_sales_text' => number_format((int)($report['peak_sales'] ?? 0), 0, ',', '.'),
				'active_hour_count_text' => (string)((int)($report['active_hour_count'] ?? 0)),
				'average_active_hour_text' => number_format((float)($report['average_per_active_hour'] ?? 0), 1, ',', '.'),
			];

			$productHourlyChartSeries[] = [
				'canvas_id' => $canvasId,
				'label' => $report['product_name'] ?? '-',
				'data' => array_values(array_map('intval', $report['hours'] ?? [])),
			];
		}

		$category = $this->db->select('*')->from('categories')->where('category_id', $categoryID)->get()->row();
		$categoryTitle = (is_object($category) && isset($category->category_title)) ? $category->category_title : 'Kategori';

		$data = $this->data ?? [];
		$data['categoryID'] = (int)$categoryID;
		$data['category'] = $category;
		$data['categoryTitle'] = $categoryTitle;
		$data['chartData'] = json_encode($chartData);
		$data['chartDataJson'] = json_encode($chartData);
		$data['startDate'] = $startDate;
		$data['endDate'] = $endDate;
		$data['reportStart'] = $reportStart;
		$data['reportEnd'] = $reportEnd;
		$data['reportRangeLabel'] = $reportStart . ' - ' . $reportEnd;
		$data['hourLabels'] = $hourLabels;
		$data['hourLabelsChartJson'] = json_encode($hourLabelsChart);
		$data['maxDateValue'] = date('Y-m-d');
		$data['startDateValue'] = $startDate;
		$data['endDateValue'] = $endDate;
		$data['reportRangeLabelText'] = $reportStart . ' - ' . $reportEnd;
		$data['totalSales'] = $totalSales;
		$data['totalSalesText'] = number_format($totalSales, 0, ',', '.');
		$data['peakHourText'] = str_pad((string)$peakHour, 2, '0', STR_PAD_LEFT) . ':00';
		$data['peakSalesText'] = number_format($peakSales, 0, ',', '.');
		$data['averagePerHourText'] = number_format($averagePerHour, 1, ',', '.');
		$data['averageActiveHourText'] = number_format($averageActiveHour, 1, ',', '.');
		$data['activeHoursText'] = (string)$activeHours;
		$data['totalProductsText'] = number_format($totalProducts, 0, ',', '.');
		$data['topProductNameText'] = $topProductName;
		$data['topProductSalesText'] = number_format($topProductSales, 0, ',', '.');
		$data['topProductShareText'] = number_format($topProductShare, 1, ',', '.');
		$data['top3ShareText'] = number_format($top3Share, 1, ',', '.');
		$data['topProductStatCards'] = $topProductStatCards;
		$data['productHourlyChartCards'] = $productHourlyChartCards;
		$data['productHourlyChartsJson'] = json_encode($productHourlyChartSeries);
		$data['hasSales'] = $totalSales > 0;
		$data['hasProductStats'] = !empty($topProductStatCards);
		$data['hasProductHourlyReports'] = !empty($productHourlyChartCards);
		$data['categorySummary'] = [
			'total_sales' => $totalSales,
			'peak_sales' => $peakSales,
			'peak_hour' => $peakHour,
			'active_hours' => $activeHours,
			'average_per_hour' => $averagePerHour,
			'average_active_hour' => $averageActiveHour,
		];
		$data['productStatsCard'] = [
			'total_products' => $totalProducts,
			'top3_share' => $top3Share,
			'top_product_name' => $topProductName,
			'top_product_sales' => $topProductSales,
			'top_product_share' => $topProductShare,
		];
		$data['productStatsTopList'] = $topProducts;
		$data['productHourlyReports'] = $productHourlyReports;

		// 3. JSON çıktı (API veya frontend tüketimi için)
		$this->template->content->view('category-sale-chart', $data);
		$this->template->publish();
		// die;
		// header('Content-Type: application/json');
		// echo json_encode([
		// 	'status' => 'success',
		// 	'category' => $categoryID,
		// 	'data' => $chartData
		// ]);
	}
}
