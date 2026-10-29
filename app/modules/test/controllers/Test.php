<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Test extends MY_Controller
{
	public $data = array();
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
			$country = preg_replace('/[^A-Za-z]/', '', (string) $uri[1]);
			$lang = preg_replace('/[^A-Za-z]/', '', (string) $uri[0]);
		}
		$this->lang->load('custom', $lang);
		$this->data['currentLocale'] = $this->db->get_where('locales', ['locale_lang_code' => $lang, 'locale_code' => $country])->row();
		$this->data['user'] = $this->session->userdata('user');
		$this->data['lang'] = $lang;
		$this->data['country'] = $country;

		// analyticsReplay kendi ANALYTICS_REPLAY_KEY kontrolunu yapar; search uclari herkese aciktir.
		// if (!in_array($this->router->fetch_method(), ['analyticsReplay', 'search', 'buildSearchIndex'], true)) {
		// 	$this->requireInternalAccessKey('TOOLS_ACCESS_KEY', 'X-Tools-Key', 'tools_key');
		// }
	}

	public function index()
	{
		if (!empty($this->session->userdata('user'))) {
			redirect(topupcenter() . $this->data['uri']);
		}
		redirect(topupcenter() . $this->data['uri'] . '/login');
	}

	// magicpayComplete
	public function magicpayComplete()
	{
		$this->load->library('Magicpay');
		$magicpay = new Magicpay();
		$complete = $magicpay->complete3dPayment(100153);
		pre($complete);
		die;
	}

	public function buildSearchIndex()
	{
		$indexFilePath = FCPATH . 'uploads/search_index.json';
		$tmpFilePath = FCPATH . 'uploads/search_index.tmp.json';

		if (!is_dir(FCPATH . 'uploads')) {
			@mkdir(FCPATH . 'uploads', 0755, true);
		}

		// 1. Kategorileri önce alalım (Mapping için)
		$dbCategories = $this->db->select('category_id, category_title, category_slug, category_image, category_thumbnail, category_countries')
			->where('category_status', 1)
			->where('category_api_status', 1)
			->order_by('category_sort_id', 'ASC')
			->get('categories')->result_array();

		$categoriesMap = [];
		foreach ($dbCategories as $cat) {
			if (empty($cat['category_image']) && !empty($cat['category_thumbnail'])) {
				$cat['category_image'] = $cat['category_thumbnail'];
			}
			$categoriesMap[$cat['category_id']] = $cat;
		}

		// 2. Ürünleri çek
		$products = $this->db->select('product_id, product_name, product_category_id, product_image, product_countries')
			->where('product_status', 1)
			->where('product_api_status', 1)
			->order_by('product_sort_id', 'ASC')
			->get('products')->result_array();

		// 3. İndeks ve Veri yapısını hazırla
		$index = [];
		$productsData = [];

		foreach ($products as $p) {
			$catId = $p['product_category_id'];
			$p['category_slug'] = isset($categoriesMap[$catId]) ? $categoriesMap[$catId]['category_slug'] : '#';
			$p['category_image'] = isset($categoriesMap[$catId]) ? $categoriesMap[$catId]['category_image'] : '';

			$productsData[$p['product_id']] = $p;

			// Ürünü indexle (Prefix p_)
			$this->_add_to_index($index, $p['product_name'], 'p_' . $p['product_id']);
		}

		// Kategorileri indexle (Prefix c_)
		foreach ($categoriesMap as $c) {
			$this->_add_to_index($index, $c['category_title'], 'c_' . $c['category_id']);
		}

		$payload = json_encode([
			'timestamp' => time(),
			'index' => $index,
			'products' => $productsData,
			'categories' => $categoriesMap
		], JSON_UNESCAPED_UNICODE);

		// 4. Atomik Yazma
		$fp = @fopen($tmpFilePath, 'w');
		if ($fp && flock($fp, LOCK_EX)) {
			fwrite($fp, $payload);
			fflush($fp);
			flock($fp, LOCK_UN);
			fclose($fp);
			@rename($tmpFilePath, $indexFilePath);
		} elseif ($fp) {
			fclose($fp);
		} else {
			@file_put_contents($indexFilePath, $payload, LOCK_EX);
		}

		return true;
	}

	private function _add_to_index(array|object &$index, string $title, string $idWithPrefix)
	{
		$name = $this->_normalize($title);
		$words = explode(' ', $name);
		foreach ($words as $word) {
			if (strlen($word) < 2)
				continue;
			for ($len = 2; $len <= strlen($word); $len++) {
				$sub = substr($word, 0, $len);
				$index[$sub][] = $idWithPrefix;
			}
		}
	}

	private function _normalize(string $string): string
	{
		$string = mb_strtolower($string, 'UTF-8');
		$tr = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'i̇'];
		$en = ['i', 'g', 'u', 's', 'o', 'c', 'i'];
		$string = str_replace($tr, $en, $string);
		return trim(preg_replace('/[^a-z0-9 ]+/', '', $string));
	}

	public function search()
	{
		$rawQ = (string) $this->input->get('q', true);
		$q = $this->_normalize($rawQ);
		$country = strtoupper(trim((string) ($this->input->get('country', true) ?: ($this->data['country'] ?? 'TR'))));

		$results = [
			'categories' => [],
			'products' => []
		];

		if (empty($q)) {
			return json($results);
		}

		$indexFilePath = FCPATH . 'uploads/search_index.json';
		if (!file_exists($indexFilePath) || filesize($indexFilePath) < 10) {
			$this->buildSearchIndex();
		}

		$data = null;
		if (file_exists($indexFilePath)) {
			$content = @file_get_contents($indexFilePath);
			if (!empty($content)) {
				$data = json_decode($content, true);
			}
		}

		// Fallback to database search if index could not be loaded or parsed
		if (empty($data) || empty($data['index'])) {
			return $this->_dbSearchFallback($rawQ, $country);
		}

		$words = array_filter(explode(' ', $q));
		if (empty($words)) {
			return json($results);
		}

		$matchSets = [];
		foreach ($words as $word) {
			if (strlen($word) < 2) {
				continue;
			}
			$matches = $data['index'][$word] ?? [];
			if (empty($matches)) {
				return $this->_dbSearchFallback($rawQ, $country);
			}
			$matchSets[] = array_unique($matches);
		}

		if (empty($matchSets)) {
			return $this->_dbSearchFallback($rawQ, $country);
		}

		usort($matchSets, function ($a, $b) {
			return count($a) <=> count($b);
		});

		$matches = array_shift($matchSets);
		foreach ($matchSets as $set) {
			$matches = array_intersect($matches, $set);
			if (empty($matches)) {
				break;
			}
		}

		$matches = array_unique($matches);

		$countryUpper = '"' . $country . '"';
		$countryLower = '"' . strtolower($country) . '"';

		foreach ($matches as $match) {
			if (strpos($match, 'c_') === 0) {
				$id = substr($match, 2);
				if (isset($data['categories'][$id])) {
					$cat = $data['categories'][$id];
					$catCountries = (string) ($cat['category_countries'] ?? '');
					if (empty($catCountries) || $catCountries === '[]' || strpos($catCountries, $countryUpper) !== false || strpos($catCountries, $countryLower) !== false) {
						$results['categories'][] = $cat;
					}
				}
			} elseif (strpos($match, 'p_') === 0) {
				$id = substr($match, 2);
				if (isset($data['products'][$id])) {
					$prod = $data['products'][$id];
					$prodCountries = (string) ($prod['product_countries'] ?? '');
					if (empty($prodCountries) || $prodCountries === '[]' || strpos($prodCountries, $countryUpper) !== false || strpos($prodCountries, $countryLower) !== false) {
						$results['products'][] = $prod;
					}
				}
			}
		}

		if (empty($results['categories']) && empty($results['products'])) {
			return $this->_dbSearchFallback($rawQ, $country);
		}

		$results['categories'] = array_slice($results['categories'], 0, 10);
		$results['products'] = array_slice($results['products'], 0, 20);

		return json($results);
	}

	private function _dbSearchFallback($rawQ, $country)
	{
		$results = [
			'categories' => [],
			'products' => []
		];
		$term = trim($rawQ);
		if (strlen($term) < 2) {
			return json($results);
		}

		$countryUpper = '"' . $country . '"';
		$countryLower = '"' . strtolower($country) . '"';

		$catQuery = $this->db->select('category_id, category_title, category_slug, category_image, category_thumbnail, category_countries')
			->where('category_status', 1)
			->where('category_api_status', 1)
			->like('category_title', $term)
			->order_by('category_sort_id', 'ASC')
			->limit(10)
			->get('categories')->result_array();

		foreach ($catQuery as $cat) {
			$catCountries = (string) ($cat['category_countries'] ?? '');
			if (empty($catCountries) || $catCountries === '[]' || strpos($catCountries, $countryUpper) !== false || strpos($catCountries, $countryLower) !== false) {
				$cat['category_image'] = !empty($cat['category_image']) ? $cat['category_image'] : ($cat['category_thumbnail'] ?? '');
				$results['categories'][] = $cat;
			}
		}

		$prodQuery = $this->db->select('products.product_id, products.product_name, products.product_category_id, products.product_image, products.product_countries, categories.category_slug')
			->join('categories', 'categories.category_id = products.product_category_id', 'left')
			->where('products.product_status', 1)
			->where('products.product_api_status', 1)
			->like('products.product_name', $term)
			->order_by('products.product_sort_id', 'ASC')
			->limit(20)
			->get('products')->result_array();

		foreach ($prodQuery as $prod) {
			$prodCountries = (string) ($prod['product_countries'] ?? '');
			if (empty($prodCountries) || $prodCountries === '[]' || strpos($prodCountries, $countryUpper) !== false || strpos($prodCountries, $countryLower) !== false) {
				$results['products'][] = $prod;
			}
		}

		return json($results);
	}

	public function testEmail()
	{
		 $toPhone = '05437462688';
		 $toPhone2 = '+905466987264';
		$a = sendSMS($toPhone2, 'Verification code: ' . rand(100000, 999999));
		pre($a);
		die;
		$to = 'ibrahim@dijipin.com';
		$subject = 'Test Email';
		$message = '<p>This is a test email sent from CodeIgniter.</p>';
		if (sendEmail($to, $subject, $message)) {
			echo 'Email sent successfully.';
		} else {
			echo 'Failed to send email.';
		}
	}

	public function sendOrders()
	{
		// Taşındı: cron2/sendOrders. Test modülünde sipariş gönderilmez.
		return false;
	}

	// Analytics/Meta olaylarını yeniden gönderir. Varsayılan prova (dry run); ?apply=1 ile uygular.
	// Erişim: ?key= değeri ANALYTICS_REPLAY_KEY ortam değişkeniyle eşleşmeli (tanımlı değilse endpoint kapalıdır).
	// ?since=YYYY-MM-DD: bu tarihten beri kaydedilen gönderim kayıtları sıfırlanır (varsayılan 2026-10-26).
	public function analyticsReplay()
	{
		$expected = (string) getenv('ANALYTICS_REPLAY_KEY');
		$given = (string) $this->input->get('key');
		if ($expected === '' || !hash_equals($expected, $given)) {
			return $this->legacyBanRespond(['status' => 'error', 'message' => 'Yetkisiz.'], 403);
		}
		if (!$this->db->table_exists('analytics_events_sent') || !$this->db->field_exists('order_tracking', 'orders')) {
			return $this->legacyBanRespond(['status' => 'error', 'message' => 'analytics_events_sent tablosu veya orders.order_tracking sütunu yok; önce SQL çalıştırılmalı.'], 409);
		}

		$since = (string) $this->input->get('since');
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $since)) {
			$since = '2026-10-26';
		}
		$apply = (string) $this->input->get('apply') === '1';
		$sinceStart = $since . ' 00:00:00';

		$sent = $this->db->select('event_key, COUNT(*) AS total', false)
			->where('created_at >=', $sinceStart)
			->group_by('event_key')->get('analytics_events_sent')->result_array();
		$deliverable = (int) $this->db->where('order_status', 1)->where('order_tracking IS NOT NULL', null, false)->count_all_results('orders');
		$cancelled = (int) $this->db->where('order_status', 2)->where('order_tracking IS NOT NULL', null, false)->count_all_results('orders');

		$result = [
			'status' => 'success',
			'mode' => $apply ? 'apply' : 'dry_run',
			'since' => $since,
			'records_to_reset' => $sent,
			'orders_with_tracking' => ['delivered' => $deliverable, 'cancelled' => $cancelled],
		];
		$this->load->library('analytics_tracker');
		$result['channels_configured'] = $this->analytics_tracker->channelStatus();

		if ((string) $this->input->get('status') === '1') {
			$result['mode'] = 'status';
			$result['status_report'] = $this->analytics_tracker->status();
			return $this->legacyBanRespond($result, 200);
		}

		if ((string) $this->input->get('live') === '1') {
			$result['mode'] = 'live_ping';
			$result['live_ping'] = $this->analytics_tracker->livePing();
			return $this->legacyBanRespond($result, 200);
		}

		// ?debug=1: kayıt yazmadan GA4 doğrulama ucuna ve (varsa ?meta_test=TEST123) Meta'ya örnek gönderir.
		if ((string) $this->input->get('debug') === '1') {
			$this->analytics_tracker->ga4Debug = true;
			$this->analytics_tracker->metaTestCode = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $this->input->get('meta_test'));
			$result['mode'] = 'debug';
			$result['diagnose'] = $this->analytics_tracker->diagnose(2);
			return $this->legacyBanRespond($result, 200);
		}

		if ($apply) {
			$this->db->where('created_at >=', $sinceStart)->delete('analytics_events_sent');
			$result['records_deleted'] = (int) $this->db->affected_rows();

			$this->analytics_tracker->useOrderTime = true;
			$processed = 0;
			// Her tur en fazla 50 kayıt işler; başarısız gönderimler bir sonraki turda yeniden denenir.
			for ($i = 0; $i < 10; $i++) {
				$count = (int) $this->analytics_tracker->processPending(50);
				$processed += $count;
				if ($count === 0) {
					break;
				}
			}
			$result['orders_processed'] = $processed;
			$result['responses'] = $this->analytics_tracker->responseLog;
			$result['note'] = 'Kalan kayıt varsa endpoint tekrar çağrılabilir; gönderilenler tekrar gönderilmez.';
		}

		return $this->legacyBanRespond($result, 200);
	}

	public function legacyBan()
	{
		$apply = (string) $this->input->get('apply') === '1';

		$missing = [];
		foreach (['user_eski_ban', 'customer_restrictions', 'users'] as $table) {
			if (!$this->db->table_exists($table)) {
				$missing[] = $table;
			}
		}
		if ($missing) {
			return $this->legacyBanRespond(['status' => 'error', 'message' => 'Eksik tablo', 'missing_tables' => $missing], 409);
		}

		$counts = ['total' => 0, 'inserted' => 0, 'already' => 0, 'expired' => 0, 'no_match' => 0, 'ambiguous' => 0, 'error' => 0];
		$problemIds = ['no_match' => [], 'ambiguous' => [], 'error' => []];
		$rows = $this->db->where_in('status', ['pending', 'no_match', 'ambiguous', 'error'])
			->order_by('id', 'ASC')->get('user_eski_ban')->result();

		foreach ($rows as $row) {
			$counts['total']++;
			$now = date('Y-m-d H:i:s');

			if (!empty($row->expires_at) && strtotime($row->expires_at) <= time()) {
				$counts['expired']++;
				$this->legacyBanMark($apply, $row->id, 'expired', null, null);
				continue;
			}

			// BINARY: buyuk/kucuk harf ve noktali/noktasiz i farkli musterileri ayirir.
			$users = $this->db->select('user_id')
				->where('BINARY user_email = BINARY ' . $this->db->escape($row->user_email), null, false)
				->get('users')->result();
			if (count($users) === 0) {
				$counts['no_match']++;
				$problemIds['no_match'][] = (int) $row->id;
				$this->legacyBanMark($apply, $row->id, 'no_match', null, null);
				continue;
			}
			if (count($users) > 1) {
				$counts['ambiguous']++;
				$problemIds['ambiguous'][] = (int) $row->id;
				$this->legacyBanMark($apply, $row->id, 'ambiguous', null, null);
				continue;
			}
			$userId = (int) $users[0]->user_id;

			$existing = $this->db->select('restriction_id')
				->where('restriction_user_id', $userId)
				->where('restriction_status', 1)
				->like('restriction_admin_reason', 'Legacy k', 'after')
				->get('customer_restrictions')->row();
			if ($existing) {
				$counts['already']++;
				$this->legacyBanMark($apply, $row->id, 'done', $userId, (int) $existing->restriction_id);
				continue;
			}

			if (!$apply) {
				$counts['inserted']++;
				continue;
			}

			$this->db->trans_start();
			$this->db->insert('customer_restrictions', [
				'restriction_user_id' => $userId,
				'restriction_scope' => 'all',
				'restriction_customer_reason' => $row->customer_reason,
				'restriction_admin_reason' => $row->admin_reason,
				'restriction_starts_at' => $now,
				'restriction_expires_at' => empty($row->expires_at) ? null : $row->expires_at,
				'restriction_status' => 1,
			]);
			$restrictionId = (int) $this->db->insert_id();
			$this->db->set('user_session_version', 'user_session_version + 1', false)
				->where('user_id', $userId)->update('users');
			$this->db->where('id', $row->id)->update('user_eski_ban', [
				'status' => 'done',
				'target_user_id' => $userId,
				'restriction_id' => $restrictionId,
				'processed_at' => $now,
			]);
			$this->db->trans_complete();

			if ($this->db->trans_status() === false || $restrictionId <= 0) {
				$counts['error']++;
				$problemIds['error'][] = (int) $row->id;
				continue;
			}
			$counts['inserted']++;
		}

		return $this->legacyBanRespond([
			'status' => $counts['error'] > 0 ? 'partial' : 'success',
			'mode' => $apply ? 'apply' : 'dry_run',
			'counts' => $counts,
			'problem_row_ids' => $problemIds,
		], 200);
	}

	private function legacyBanMark($apply, $id, $status, $userId, $restrictionId)
	{
		if (!$apply) {
			return;
		}
		$this->db->where('id', $id)->update('user_eski_ban', [
			'status' => $status,
			'target_user_id' => $userId,
			'restriction_id' => $restrictionId,
			'processed_at' => date('Y-m-d H:i:s'),
		]);
	}

	private function legacyBanRespond($payload, $httpCode)
	{
		$this->output->set_status_header($httpCode)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
	}

	// 2025 başından beri legacy_orders'ta alışverişi olan müşteriler. ?since=YYYY-MM-DD ile değiştirilebilir.
	public function legacyCustomers()
	{
		if (!$this->db->table_exists('legacy_orders')) {
			return $this->legacyBanRespond(['status' => 'error', 'message' => 'legacy_orders tablosu yok'], 409);
		}

		$since = (string) $this->input->get('since');
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $since)) {
			$since = '2025-01-01';
		}

		$rows = $this->db->select('o.target_user_id, u.user_email, u.user_phone, COUNT(*) AS order_count, SUM(o.total_amount) AS total_spent, MIN(o.order_date) AS first_order_date, MAX(o.order_date) AS last_order_date, MAX(o.currency) AS currency', false)
			->from('legacy_orders o')
			->join('users u', 'u.user_id = o.target_user_id', 'left')
			->where('o.order_date >=', $since . ' 00:00:00')
			->where('o.target_user_id IS NOT NULL', null, false)
			->where("TRIM(IFNULL(u.user_phone, '')) <>", '')
			->group_by('o.target_user_id, u.user_email, u.user_phone')
			->order_by('last_order_date', 'DESC')
			->get()->result();

		$customers = [];
		$invalidPhones = 0;
		foreach ($rows as $r) {
			// Geçerli: +905XXXXXXXXX, 905XXXXXXXXX veya 05XXXXXXXXX (boşluk/tire vb. temizlenir).
			$phone = trim((string) $r->user_phone);
			$digits = preg_replace('/\D+/', '', $phone);
			$valid = (bool) preg_match('/^(?:90|0)5\d{9}$/', $digits);
			if (!$valid) {
				$invalidPhones++;
				continue;
			}
			$customers[] = [
				'user_id' => (int) $r->target_user_id,
				'email' => $r->user_email,
				'phone' => $phone,
				'order_count' => (int) $r->order_count,
				'total_spent' => round((float) $r->total_spent, 2),
				'currency' => $r->currency,
				'first_order_date' => $r->first_order_date,
				'last_order_date' => $r->last_order_date,
			];
		}
		echo count($customers);
		die;
		return $this->legacyBanRespond([
			'status' => 'success',
			'since' => $since,
			'count' => count($customers),
			'skipped_invalid_phone' => $invalidPhones,
			'customers' => $customers,
		], 200);
	}

	public function userCheck()
	{
		$userID = trim((string) $this->input->get('user_id'));
		$serverID = trim((string) $this->input->get('server_id'));

		if ($userID === '' || $serverID === '') {
			return json(['status' => 'error', 'message' => 'Lütfen Oyuncu ID ve Sunucu ID giriniz.'], 400);
		}
		if (!preg_match('/^\d{4,20}$/', $userID) || !preg_match('/^\d{1,10}$/', $serverID)) {
			return json(['status' => 'error', 'message' => 'Oyuncu ID ve Sunucu ID yalnızca rakamlardan oluşmalıdır.'], 422);
		}

		$this->load->library('Dijipin');
		$djpn = new Dijipin();
		$userCheck = $djpn->mlbbUserCheck($userID, $serverID);

		if ($userCheck === false) {
			return json(['status' => 'error', 'message' => 'Sorgulama servisine şu anda ulaşılamıyor.'], 500);
		}

		return json($userCheck);
	}
}
