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
			$country = $uri[1];
			$lang = $uri[0];
		}
		$this->lang->load('custom', $lang);
		$this->data['currentLocale'] = $this->db->get_where('locales', ['locale_lang_code' => $lang, 'locale_code' => $country])->row();
		$this->data['user'] = $this->session->userdata('user');
		$this->data['lang'] = $lang;
		$this->data['country'] = $country;
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

		// 1. Kategorileri önce alalım (Mapping için)
		$dbCategories = $this->db->select('category_id, category_title, category_slug, category_image, category_countries')
			->where('category_status', 1)
			->where('category_api_status', 1)
			->order_by('category_sort_id', 'ASC')
			->get('categories')->result_array();

		$categoriesMap = [];
		foreach ($dbCategories as $cat) {
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
			// Ürüne kategori bilgilerini ekle (Slug ve image)
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
		]);

		// 4. Atomik Yazma
		$fp = fopen($tmpFilePath, 'w');
		if (flock($fp, LOCK_EX)) {
			fwrite($fp, $payload);
			fflush($fp);
			flock($fp, LOCK_UN);
			fclose($fp);
			rename($tmpFilePath, $indexFilePath);
		} else {
			fclose($fp);
		}
	}

	private function _add_to_index(array|object &$index, string $title, string $idWithPrefix)
	{
		$name = $this->_normalize($title);
		$words = explode(' ', $name);
		foreach ($words as $word) {
			if (strlen($word) < 2) continue;
			for ($len = 3; $len <= strlen($word); $len++) {
				$sub = substr($word, 0, $len);
				$index[$sub][] = $idWithPrefix;
			}
		}
	}

	private function _normalize(string $string): string
	{
		$string = mb_strtolower($string, 'UTF-8');
		$tr = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç'];
		$en = ['i', 'g', 'u', 's', 'o', 'c'];
		$string = str_replace($tr, $en, $string);
		return preg_replace('/[^a-z0-9 ]+/', '', $string);
	}

	public function search()
	{
		$q = $this->_normalize($this->input->get('q', true));
		$data = json_decode(
			file_get_contents(FCPATH . 'uploads/search_index.json'),
			true
		);

		$results = [
			'categories' => [],
			'products' => []
		];

		$words = array_filter(explode(' ', $q));

		if (empty($words)) {
			return json($results);
		}

		$matchSets = [];

		foreach ($words as $word) {

			if (strlen($word) < 3) {
				continue;
			}

			// Kelimenin prefix indexindeki sonuçları al
			$matches = $data['index'][$word] ?? [];

			// Hiç sonuç yoksa direkt boş dön
			if (empty($matches)) {
				return json($results);
			}

			$matchSets[] = array_unique($matches);
		}

		if (empty($matchSets)) {
			return json($results);
		}

		// En küçük kümeyi önce kullanmak performans açısından daha iyi
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

		$country = $this->data['country'] ?? 'tr';
		$countrySearchStr = '"' . $country . '"';

		foreach ($matches as $match) {

			if (strpos($match, 'c_') === 0) {

				$id = substr($match, 2);

				if (isset($data['categories'][$id])) {
					$cat = $data['categories'][$id];
					if (empty($cat['category_countries']) || strpos($cat['category_countries'], $countrySearchStr) !== false) {
						$results['categories'][] = $cat;
					}
				}
			} elseif (strpos($match, 'p_') === 0) {

				$id = substr($match, 2);

				if (isset($data['products'][$id])) {
					$prod = $data['products'][$id];
					if (empty($prod['product_countries']) || strpos($prod['product_countries'], $countrySearchStr) !== false) {
						$results['products'][] = $prod;
					}
				}
			}
		}

		return json($results);
	}

	public function testEmail()
	{
		// $toPhone = '05437462688';
		// $a = sendSMS2($toPhone, 'Verification code: ' . rand(100000, 999999));
		// pre($a);
		// die;
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
  
  public function legacyBan()
	{
		$apply = (string)$this->input->get('apply') === '1';

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
				$problemIds['no_match'][] = (int)$row->id;
				$this->legacyBanMark($apply, $row->id, 'no_match', null, null);
				continue;
			}
			if (count($users) > 1) {
				$counts['ambiguous']++;
				$problemIds['ambiguous'][] = (int)$row->id;
				$this->legacyBanMark($apply, $row->id, 'ambiguous', null, null);
				continue;
			}
			$userId = (int)$users[0]->user_id;

			$existing = $this->db->select('restriction_id')
				->where('restriction_user_id', $userId)
				->where('restriction_status', 1)
				->like('restriction_admin_reason', 'Legacy k', 'after')
				->get('customer_restrictions')->row();
			if ($existing) {
				$counts['already']++;
				$this->legacyBanMark($apply, $row->id, 'done', $userId, (int)$existing->restriction_id);
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
			$restrictionId = (int)$this->db->insert_id();
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
				$problemIds['error'][] = (int)$row->id;
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
}
