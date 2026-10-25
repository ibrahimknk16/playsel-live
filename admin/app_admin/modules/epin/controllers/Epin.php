<?php defined('BASEPATH') or exit('No direct script access allowed');

class Epin extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		userCheck();
		parent::__construct();
		$this->load->model('VtModel');
	}

	public function index()
	{
		$data = $this->data;
		$data['categories'] = $this->db->select('category_id, category_title')->from('categories')->get()->result();
		$data['products'] = $this->db->select('product_id, product_name, product_image')->from('products')->get()->result();
		$data['epin_pools'] = $this->db->select('epin_pool_id, epin_pool_title')->from('epin_pool')->get()->result();
		$this->template->content->view('index', $data);
		$this->template->publish();
	}

	public function epinPoolAjax()
	{
		$draw = (int)$this->input->post('draw');
		$start = (int)$this->input->post('start');
		$length = (int)$this->input->post('length');
		$categoryID = $this->input->post('categoryID');
		$productID = $this->input->post('productID');

		// Sorgu Şablonu
		$this->db->start_cache();
		$this->db->from('epin_pool ep');
		$this->db->join('categories c', 'c.category_id = ep.epin_pool_category_id', 'left');
		$this->db->where('ep.epin_pool_status !=', 2);

		if (!empty($categoryID)) {
			$this->db->where('ep.epin_pool_category_id', (int)$categoryID);
		}
		if (!empty($productID)) {
			$this->db->where("ep.epin_pool_id", (int)$productID);
		}
		$this->db->stop_cache();

		$recordsTotal = $this->db->count_all_results();
		$recordsFiltered = $recordsTotal;

		// Sadece İstenen Sayfadaki Kayıtları Çek
		$this->db->select('ep.*, c.category_title');
		$this->db->order_by('ep.epin_pool_id', 'ASC');
		$this->db->limit($length, $start);
		$pools = $this->db->get()->result();

		// Global Filtrelenmiş Toplam Maliyeti Hesapla (Tek Sorgu ile)
		$costWhere = "ep.epin_pool_status != 2";
		if (!empty($categoryID)) $costWhere .= " AND ep.epin_pool_category_id = " . (int)$categoryID;

		$costQuery = $this->db->query("
            SELECT SUM(ep.epin_pool_cost * (
                SELECT COUNT(epin_id) FROM epins WHERE epin_pool_id = ep.epin_pool_id AND epin_status = 0
            )) as grand_total
            FROM epin_pool ep WHERE $costWhere
        ")->row();
		$globalTotalCost = $costQuery ? (float)$costQuery->grand_total : 0;

		$this->db->flush_cache();

		// Optimizasyon: Ürünleri döngü içinde sorgulamamak için bir kere çekip mapliyoruz
		$allProducts = $this->db->select('product_id, product_name, product_image')->from('products')->get()->result_array();
		$productMap = array_column($allProducts, null, 'product_id');

		$dt = [];
		foreach ($pools as $pool) {
			// Stok Sayısı
			$epinsCount = $this->db->where(['epin_pool_id' => $pool->epin_pool_id, 'epin_status' => 0])->count_all_results('epins');

			// Ortalama Satış
			$avgSaleData = $this->epinSaleAverage($pool->epin_pool_id);

			// Bağlı Ürünler HTML
			$productIds = json_decode($pool->epin_pool_products, true) ?: [];
			$productsHtml = '<div class="d-flex flex-wrap gap-2">';
			foreach ($productIds as $pid) {
				if (isset($productMap[$pid])) {
					$p = $productMap[$pid];
					$productsHtml .= '
                    <div class="epin-product-badge">
                        <img src="' . $p['product_image'] . '" alt="img">
                        <span>' . $p['product_name'] . '</span>
                    </div>';
				}
			}
			$productsHtml .= '</div>';

			// Renklendirme
			$stockBadgeClass = 'badge-light-success text-success';
			if ($epinsCount <= 0) $stockBadgeClass = 'badge-light-danger text-danger';
			elseif ($epinsCount <= 25) $stockBadgeClass = 'badge-light-warning text-warning';

			$rowTotal = $pool->epin_pool_cost * $epinsCount;

			// Düzenle Butonu (Verileri DOM yerine doğrudan data-* attribute'lara gömüyoruz)
			$editBtn = '
                <button class="btn btn-icon btn-light-warning btn-sm edit-pool" 
                    data-pool-id="' . $pool->epin_pool_id . '" 
                    data-pool-title="' . htmlspecialchars($pool->epin_pool_title, ENT_QUOTES) . '" 
                    data-pool-cost="' . htmlspecialchars((string)$pool->epin_pool_cost) . '" 
                    data-category-id="' . htmlspecialchars((string)$pool->epin_pool_category_id) . '"
                    data-products="' . htmlspecialchars(json_encode($productIds), ENT_QUOTES) . '"
                    data-bs-toggle="tooltip" title="Havuzu Düzenle">
                    <i class="ki-outline ki-pencil fs-4"></i>
                </button>';

			$addStockBtn = '
                <a href="' . base_url('epin/addStock/' . $pool->epin_pool_id) . '" class="btn btn-icon btn-light-success btn-sm" data-bs-toggle="tooltip" title="Stok İşlemleri">
                    <i class="ki-outline ki-plus-square fs-4"></i>
                </a>';

			$dt[] = [
				'<div class="text-center fw-bold">#' . $pool->epin_pool_id . '</div>',
				'<div class="fw-bold text-gray-800 text-hover-primary">' . $pool->epin_pool_title . '<small class="d-block text-muted">' . $pool->category_title . '</small></div>',
				$productsHtml,
				'<div class="text-center"><span class="badge fw-bold px-3 py-2 ' . $stockBadgeClass . '">' . $epinsCount . ' Adet</span></div>',
				'<div class="text-end"><span class="text-gray-800 fw-bold">' . number_format((float)$pool->epin_pool_cost, 2, ',', '.') . ' ₺</span></div>',
				'<div class="text-end"><span class="text-success fw-bold">' . number_format((float)$rowTotal, 2, ',', '.') . ' ₺</span></div>',
				'<div class="text-center"><span class="badge badge-light-info fw-bold px-3 py-2"><i class="ki-outline ki-chart-line-up fs-5 text-info me-1"></i> ' . $avgSaleData . '</span></div>',
				'<div class="d-flex justify-content-end gap-2">' . $editBtn . $addStockBtn . '</div>'
			];
		}

		$output = [
			"draw" => $draw,
			"recordsTotal" => $recordsTotal,
			"recordsFiltered" => $recordsFiltered,
			"data" => $dt,
			"globalTotalCost" => number_format($globalTotalCost, 2, ',', '.') . ' ₺'
		];

		return json($output);
	}

	public function addPoolItem()
	{
		$post = $this->input->post();
		if (!isset($post['epin_pool_products']) || empty($post['epin_pool_products'])) {
			$this->session->set_flashdata('error', 'Lütfen en az bir ürün seçiniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if (empty($post['epin_pool_title'])) {
			$this->session->set_flashdata('error', 'Lütfen havuz ismini giriniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if (empty($post['epin_pool_cost']) || $post['epin_pool_cost'] < 1) {
			$this->session->set_flashdata('error', 'Lütfen havuz fiyatını giriniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if (empty($post['epin_pool_category_id'])) {
			$this->session->set_flashdata('error', 'Lütfen havuz kategorisini seçiniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$epinPoolProductsInteger = array_map('intval', $post['epin_pool_products']);
		$post['epin_pool_products'] = json_encode($epinPoolProductsInteger);
		unset($post['epin_pool_id']);
		$this->db->insert('epin_pool', $post);
		$this->session->set_flashdata('success', 'Epin havuzu başarıyla eklendi.');
		redirect($_SERVER['HTTP_REFERER']);
	}

	public function updatePoolItem()
	{
		$post = $this->input->post();
		$epinPoolProductsInteger = array_map('intval', $post['epin_pool_products']);
		$post['epin_pool_products'] = json_encode($epinPoolProductsInteger);
		$this->db->where('epin_pool_id', $post['epin_pool_id']);
		$this->db->update('epin_pool', $post);
		$this->session->set_flashdata('success', 'Epin havuzu başarıyla güncellendi.');
		redirect($_SERVER['HTTP_REFERER']);
	}

	public function addStock()
	{
		$data = $this->data;
		$poolID = $this->uri->segment(3);
		$pool = $this->db->select('*')->from('epin_pool')->where('epin_pool_id', $poolID)->get()->row();
		if (empty($pool)) {
			$this->session->set_flashdata('error', 'Epin havuzu bulunamadı.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$products = json_decode($pool->epin_pool_products, true);
		$pool->epinProducts = $this->db->select('product_id, product_name, product_image')->from('products')
			->where_in('product_id', $products)
			->get()->result();
		$pool->epinsCount = $this->db->select('COUNT(*) as count')->from('epins')->where(['epin_pool_id' => $pool->epin_pool_id, 'epin_status' => 0])->get()->row()->count;
		$pool->epinLoads = $this->db
			->select('
				el.*,
				SUM(CASE WHEN e.epin_status = 0 THEN 1 ELSE 0 END) AS stokta,
				SUM(CASE WHEN e.epin_status = 1 THEN 1 ELSE 0 END) AS satilan,
				SUM(CASE WHEN e.epin_status = 2 THEN 1 ELSE 0 END) AS iptal,
				a.admin_name,
				epro.epin_provider_name
			')
			->from('epin_loads el')
			->join(
				'epins e',
				'e.epin_load_id = el.epin_load_code',
				'left'
			)
			->join('admins a', 'a.admin_id = el.epin_load_admin_id', 'left')
			->join('epin_providers epro', 'epro.epin_provider_id = el.epin_load_provider_id', 'left')
			->where('el.epin_load_pool_id', $pool->epin_pool_id)
			->group_by('el.epin_load_id')
			->order_by('el.epin_load_date', 'DESC')
			->get()
			->result();
		$data['totalInstalled'] = $this->db->select('COUNT(*) as count')->from('epins')->where(['epin_pool_id' => $pool->epin_pool_id])->get()->row()->count;
		$data['totalSelled'] = $this->db->select('COUNT(*) as count')->from('epins')->where(['epin_pool_id' => $pool->epin_pool_id, 'epin_status' => 1])->get()->row()->count;
		$data['totalCancelled'] = $this->db->select('COUNT(*) as count')->from('epins')->where(['epin_pool_id' => $pool->epin_pool_id, 'epin_status' => 2])->get()->row()->count;
		$data['pool'] = $pool;
		// pre($pool);die;
		$data['epin_providers'] = $this->db->select('*')->from('epin_providers')->get()->result();
		$this->template->content->view('addKeys', $data);
		$this->template->publish();
	}

	public function addKeys()
	{
		// ['pins'] ve ['epin_pool_id'] post edilmelidir.
		$post = $this->input->post();
		$purchaseCostRaw = trim((string)($post['epin_purchase_cost'] ?? ''));
		$purchaseRateRaw = trim((string)($post['epin_purchase_rate'] ?? '1'));
		$purchaseCurrency = strtoupper(trim((string)($post['epin_purchase_currency'] ?? 'TRY')));
		$purchaseCost = (float)str_replace(',', '.', $purchaseCostRaw);
		$purchaseRate = (float)str_replace(',', '.', $purchaseRateRaw);
		$post['pins'] = trim($post['pins']);
		$pins = explode("\n", $post['pins']);
		$pins = array_map('trim', $pins);
		$pins = array_filter($pins, function ($pin) {
			return !empty($pin);
		});
		// if epin_purchase_cost_type total or per_unit sent, calculate total cost accordingly
		if (isset($post['epin_purchase_cost_type']) && $post['epin_purchase_cost_type'] === 'per_unit') {
			$purchaseCost = $purchaseCost * count($pins);
		} else {
			$purchaseCost = $purchaseCost; // total olarak gönderilmiş
		}

		$count = count($pins);
		$randLoadID = generateRandomString(10, 'AZ09');
		if (empty($post['pins'])) {
			$this->session->set_flashdata('error', 'Lütfen en az bir adet pin giriniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if (empty($post['epin_pool_id'])) {
			$this->session->set_flashdata('error', 'Lütfen havuz ID\'sini kontrol ediniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if (empty($post['epin_load_provider_id'])) {
			$this->session->set_flashdata('error', 'Lütfen epin sağlayıcısı seçiniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if ($purchaseCost <= 0 || $purchaseRate <= 0) {
			$this->session->set_flashdata('error', 'Lütfen maliyet ve kur alanlarını doğru giriniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$allowedCurrencies = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];
		if (!in_array($purchaseCurrency, $allowedCurrencies, true)) {
			$this->session->set_flashdata('error', 'Geçersiz alış para birimi.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$epinPool = $this->db->select('*')->from('epin_pool')->where('epin_pool_id', $post['epin_pool_id'])->get()->row();
		if (empty($epinPool)) {
			$this->session->set_flashdata('error', 'Epin havuzu bulunamadı.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$loadCount = 0;
		$errorPinArray = [];
		$this->load->library('encryption');
		foreach ($pins as $key => $pin) {
			$pin = trim($pin);
			if (empty($pin)) {
				continue;
			}
			$pin = str_replace([" ", "\r", "\n", "\t", "\0"], '', $pin);

			$encrypted_pin = $this->encryption->encrypt($pin); // doğru kullanım
			$maskedPin = keyMask($pin);
			$insertArray = [
				'epin_load_id' => $randLoadID,
				'epin_pool_id' => $post['epin_pool_id'],
				'epin_code' => $encrypted_pin,
				'epin_code_masked' => $maskedPin,
				'epin_status' => 0,
			];
			$this->db->insert('epins', $insertArray);
			if ($this->db->affected_rows() > 0) {
				$loadCount++;
			} else {
				$errorPinArray[] = $pin;
			}
		}
		if ($loadCount > 0) {
			$this->session->set_flashdata('success', $loadCount . ' adet pin başarıyla yüklendi.');
		}
		if ($loadCount < $count) {
			$this->session->set_flashdata('error', 'Yüklenemeyen pin sayısı: ' . ($count - $loadCount));
		}
		if (!empty($errorPinArray)) {
			$this->session->set_flashdata('error', 'Aşağıdaki pinler yüklenemedi: ' . implode(', ', $errorPinArray));
		}
		// epin_loads tablosuna yükleme kaydı (maliyet/kur alanları dahil)
		$tryAmount = $purchaseCost * $purchaseRate;
		$loadInsertData = [
			'epin_load_code' => $randLoadID,
			'epin_load_pool_id' => (int)$post['epin_pool_id'],
			'epin_load_count' => (int)$loadCount,
			'epin_load_admin_id' => (int)$this->session->userdata('admin')['admin_id'],
			'epin_load_provider_id' => (int)$post['epin_load_provider_id'],
		];
		if ($this->db->field_exists('epin_load_status', 'epin_loads')) {
			$loadInsertData['epin_load_status'] = 1;
		}
		if ($this->db->field_exists('epin_load_date', 'epin_loads')) {
			$loadInsertData['epin_load_date'] = date('Y-m-d H:i:s');
		}
		if ($this->db->field_exists('epin_load_purchase_cost', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_cost'] = number_format($purchaseCost, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_currency', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_currency'] = $purchaseCurrency;
		}
		if ($this->db->field_exists('epin_load_purchase_rate', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_rate'] = number_format($purchaseRate, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_try_amount', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_try_amount'] = number_format($tryAmount, 6, '.', '');
		}
		$this->db->insert('epin_loads', $loadInsertData);
		$epinLoadId = (int)$this->db->insert_id();

		if ($loadCount > 0) {
			$providerId = (int)$post['epin_load_provider_id'];
			$adminId = (int)($this->session->userdata('admin')['admin_id'] ?? 0);

			$balanceLogged = $this->logProviderBalanceDebit(
				$providerId,
				$adminId,
				$purchaseCurrency,
				$purchaseCost,
				$purchaseRate,
				$tryAmount,
				$epinLoadId
			);
			if (!$balanceLogged) {
				$this->session->set_flashdata('error', 'Stok eklendi fakat provider_balances harcama kaydı yazılamadı. Lütfen muhasebe kontrolü yapın.');
			}
		}
		redirect($_SERVER['HTTP_REFERER']);
	}

	private function logProviderBalanceDebit(int $providerId, int $adminId, string $purchaseCurrency, float $purchaseCost, float $purchaseRate, float $tryAmount, int $epinLoadId = 0): bool
	{
		if ($providerId <= 0 || $adminId <= 0 || $purchaseCost <= 0 || $purchaseRate <= 0 || $tryAmount <= 0) {
			return false;
		}
		if (!$this->db->table_exists('provider_balances')) {
			return false;
		}

		$providerCol = $this->db->field_exists('provider_balance_provider_id', 'provider_balances') ? 'provider_balance_provider_id' : 'provider_id';
		$currencyCol = $this->db->field_exists('provider_balance_currency', 'provider_balances') ? 'provider_balance_currency' : 'currency';
		$amountCol = $this->db->field_exists('provider_balance_amount', 'provider_balances') ? 'provider_balance_amount' : 'balance_amount';
		$noteCol = $this->db->field_exists('provider_balance_note', 'provider_balances') ? 'provider_balance_note' : 'note';
		$createdCol = $this->db->field_exists('provider_balance_created_at', 'provider_balances') ? 'provider_balance_created_at' : 'created_at';
		$updatedCol = $this->db->field_exists('provider_balance_updated_at', 'provider_balances') ? 'provider_balance_updated_at' : 'updated_at';

		$adminCol = null;
		if ($this->db->field_exists('provider_balance_admin_id', 'provider_balances')) {
			$adminCol = 'provider_balance_admin_id';
		} elseif ($this->db->field_exists('admin_id', 'provider_balances')) {
			$adminCol = 'admin_id';
		} elseif ($this->db->field_exists('created_by', 'provider_balances')) {
			$adminCol = 'created_by';
		}

		$typeCol = null;
		if ($this->db->field_exists('provider_balance_type', 'provider_balances')) {
			$typeCol = 'provider_balance_type';
		} elseif ($this->db->field_exists('movement_type', 'provider_balances')) {
			$typeCol = 'movement_type';
		}

		$parallelCols = $this->resolveProviderBalanceParallelCols();

		$now = date('Y-m-d H:i:s');
		$insertData = [
			$providerCol => $providerId,
			$currencyCol => $purchaseCurrency,
			$amountCol => number_format($purchaseCost * -1, 6, '.', ''),
			$noteCol => $epinLoadId > 0 ? ('epin_load_id=' . $epinLoadId) : null,
			$createdCol => $now,
			$updatedCol => $now,
		];
		if ($parallelCols['load_id'] !== null && $epinLoadId > 0) {
			$insertData[$parallelCols['load_id']] = $epinLoadId;
		}
		if ($parallelCols['purchase_cost'] !== null) {
			$insertData[$parallelCols['purchase_cost']] = number_format($purchaseCost, 6, '.', '');
		}
		if ($parallelCols['purchase_currency'] !== null) {
			$insertData[$parallelCols['purchase_currency']] = $purchaseCurrency;
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
		if ($typeCol !== null) {
			$insertData[$typeCol] = 'debit';
		}

		return (bool)$this->db->insert('provider_balances', $insertData);
	}

	private function resolveProviderBalanceParallelCols()
	{
		$map = [
			'load_id' => ['provider_balance_epin_load_id', 'provider_balance_load_id', 'epin_load_id', 'load_id'],
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

	public function deleteLoad()
	{
		$loadID = $this->uri->segment(3);
		if (empty($loadID)) {
			$this->session->set_flashdata('error', 'Lütfen yükleme ID\'sini kontrol ediniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$epinLoad = $this->db->select('*')->from('epin_loads')->where('epin_load_id', $loadID)->get()->row();
		if (empty($epinLoad)) {
			$this->session->set_flashdata('error', 'Epin yükleme bulunamadı.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if ($epinLoad->epin_load_status == 2) {
			$this->session->set_flashdata('error', 'Epin yükleme zaten silinmiş.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		//pre($epinLoad);
		$loadCode = $epinLoad->epin_load_code;
		$epinsUpdate = $this->db->where('epin_load_id', $loadCode)
			->where('epin_status', 0)
			->update('epins', ['epin_status' => 2]);
		if ($epinsUpdate) {
			$this->session->set_flashdata('success', 'Epin yükleme başarıyla silindi.');
		} else {
			$this->session->set_flashdata('error', 'Epin yükleme silinemedi.');
		}
		$updateLoadStatus = $this->db->where('epin_load_id', $loadID)->update('epin_loads', ['epin_load_status' => 2]);

		if ($updateLoadStatus) {
			$this->session->set_flashdata('success', 'Epin yükleme başarıyla silindi.');
		} else {
			$this->session->set_flashdata('error', 'Epin yükleme silinemedi.');
		}
		redirect($_SERVER['HTTP_REFERER']);
	}

	public function returnLoad()
	{
		$loadID = $this->uri->segment(3);
		if (empty($loadID)) {
			$this->session->set_flashdata('error', 'Lütfen yükleme ID\'sini kontrol ediniz.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$epinLoad = $this->db->select('*')->from('epin_loads')->where('epin_load_id', $loadID)->get()->row();
		if (empty($epinLoad)) {
			$this->session->set_flashdata('error', 'Epin yükleme bulunamadı.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if ($epinLoad->epin_load_status == 1) {
			$this->session->set_flashdata('error', 'Epin yükleme zaten iade edilmiş.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$loadCode = $epinLoad->epin_load_code;
		$epinsUpdate = $this->db->where('epin_load_id', $loadCode)
			->where('epin_status', 2)
			->update('epins', ['epin_status' => 0]);
		if ($epinsUpdate) {
			$this->session->set_flashdata('success', 'Epin yükleme başarıyla iade edildi.');
		} else {
			$this->session->set_flashdata('error', 'Epin yükleme iade edilemedi.');
		}
		$updateLoadStatus = $this->db->where('epin_load_id', $loadID)->update('epin_loads', ['epin_load_status' => 1]);
		if ($updateLoadStatus) {
			$this->session->set_flashdata('success', 'Epin yükleme başarıyla iade edildi.');
		} else {
			$this->session->set_flashdata('error', 'Epin yükleme iade edilemedi.');
		}
		redirect($_SERVER['HTTP_REFERER']);
	}

	public function list()
	{
		$data = $this->data;
		$this->template->content->view('list', $data);
		$this->template->publish();
	}

	public function listPins()
	{
		// 1. Input Validation & Varsayılanlar
		$draw   = intval($this->input->post("draw"));
		$start  = intval($this->input->post("start"));
		$length = intval($this->input->post("length") ?? 10);

		$loadID  = $this->uri->segment(3);
		$orderID = $this->input->post('order_id');
		$epin    = $this->input->post('epin');

		$this->load->library('encryption');

		// 2. Base Query Builder (Sorguyu Tek Yerden Yönetelim)
		$this->db->from('epins');
		$this->db->join('epin_pool', 'epin_pool.epin_pool_id = epins.epin_pool_id', 'left'); // N+1 Çözümü
		$this->db->join('orders', 'orders.order_id = epins.epin_order_id', 'left');
		$this->db->join('users', 'users.user_id = orders.order_reseller_id', 'left');
		$this->db->join('epin_loads', 'epin_loads.epin_load_code = epins.epin_load_id', 'left');
		$this->db->join('epin_providers', 'epin_providers.epin_provider_id = epin_loads.epin_load_provider_id', 'left');

		// Filtreler
		if ($loadID)  $this->db->where('epins.epin_load_id', $loadID);
		if ($orderID) $this->db->where('epins.epin_order_id', $orderID);
		if ($epin) {
			$this->db->group_start()
				->like('epins.epin_code_masked', $epin, 'both')
				->or_like('epins.epin_code_decrypted', $epin, 'both')
				->group_end();
		}

		// 3. Count Filtered (Arama yapıldıktan sonraki toplam sayı)
		$temp_db = clone $this->db;
		$recordsFiltered = $temp_db->count_all_results();

		// 4. Get Data
		$this->db->select('epins.*, epin_pool.epin_pool_title, epin_pool.epin_pool_cost, users.user_name, users.user_id,epin_providers.epin_provider_name');
		$this->db->order_by('epins.epin_id', 'DESC');
		$this->db->limit($length, $start);
		$dataList = $this->db->get()->result();


		// Total Records (Filtresiz yalın toplam - Cache'lenebilir)
		$recordsTotal = $this->db->count_all('epins');

		$dt = [];
		foreach ($dataList as $row) {
			// PIN Çözme Mantığı
			$pinDisplay = '[Şifre Çözülemedi]';
			try {
				if ($row->epin_status == 1) {
					$decrypted = $this->encryption->decrypt($row->epin_code);
					$pinDisplay = $decrypted ?: '[Hatalı Veri]';
				} else {
					$pinDisplay = $row->epin_code_masked;
				}
			} catch (Exception $e) {
			}

			// Durum Badge Mantığı (Switch-case veya Array map daha performanslıdır)
			$statusMap = [
				0 => '<span class="badge badge-light-info">Stokta</span>',
				1 => '<span class="badge badge-light-success">Satıldı</span>',
				2 => '<span class="badge badge-light-danger">İptal Edildi</span>'
			];
			$statusHtml = $statusMap[$row->epin_status] ?? '-';

			$dt[] = [
				$row->epin_id,
				$row->epin_load_id,
				$row->epin_pool_title,
				$pinDisplay,
				$row->epin_pool_cost,
				$statusHtml,
				$row->user_name ? '<a href="' . site_url('users/detail/' . $row->user_id) . '" target="_blank">' . $row->user_name . '</a>' : '-',
				$row->epin_provider_name ?? '-',
				date('d-m-Y H:i', strtotime($row->epin_created_at)),
				$row->epin_selled_at ? date('d-m-Y H:i', strtotime($row->epin_selled_at)) : '-',
				$row->epin_order_id ? '<a href="' . site_url('orders/orderInvoice/' . $row->epin_order_id) . '" target="_blank">' . $row->epin_order_id . '</a>' : '-',
				'<button class="btn btn-xs btn-danger" onclick="cancelPin(' . $row->epin_id . ')">İptal Et</button>'
			];
		}

		return json([
			"draw"            => $draw,
			"recordsTotal"    => $recordsTotal,
			"recordsFiltered" => $recordsFiltered,
			"data"            => $dt
		]);
	}

	public function epinLoadReport()
	{
		// Selectbox verileri
		$data['admins']     = $this->db->select('admin_id, admin_name')->get('admins')->result_array();
		$data['epin_pools'] = $this->db->select('epin_pool_id, epin_pool_title')->get('epin_pool')->result_array();
		$data['providers']  = $this->db->select('epin_provider_id, epin_provider_name')->get('epin_providers')->result_array();

		// Filtreler için default tarihler
		$data['filters'] = [
			'startDate' => date('Y-m-d', strtotime('-30 days')),
			'endDate'   => date('Y-m-d')
		];

		$this->template->content->view('epin-report', $data);
		$this->template->publish();
	}

	public function epinLoadReportAjax()
	{
		// DataTables POST verileri
		$draw   = intval($this->input->post("draw"));
		$start  = intval($this->input->post("start"));
		$length = intval($this->input->post("length"));

		// Filtre Verileri
		$admin_id     = $this->input->post("admin_id");
		$epin_pool_id = $this->input->post("epin_pool_id");
		$provider_id  = $this->input->post("epin_provider_id");
		$startDate    = $this->input->post("startDate");
		$endDate      = $this->input->post("endDate");

		// Tarih formatlama
		$startDate = !empty($startDate) ? $startDate . ' 00:00:00' : date('Y-m-d 00:00:00', strtotime('-30 days'));
		$endDate   = !empty($endDate) ? $endDate . ' 23:59:59' : date('Y-m-d 23:59:59');

		// Dinamik kolon kontrollerini bir kere yap (Performans için)
		$hasStatus = $this->db->field_exists('epin_load_status', 'epin_loads');
		$hasCode = $this->db->field_exists('epin_load_code', 'epin_loads');
		$hasCost = $this->db->field_exists('epin_load_purchase_cost', 'epin_loads');
		$hasCurr = $this->db->field_exists('epin_load_purchase_currency', 'epin_loads');
		$hasRate = $this->db->field_exists('epin_load_purchase_rate', 'epin_loads');
		$hasTryAmt = $this->db->field_exists('epin_load_purchase_try_amount', 'epin_loads');

		$selectParts = [
			'el.epin_load_id',
			'el.epin_load_count',
			'el.epin_load_date',
			'ep.epin_pool_title',
			'ep.epin_pool_id',
			'prov.epin_provider_name',
			'prov.epin_provider_id',
			'adm.admin_name',
			'adm.admin_id'
		];

		if ($hasCode) $selectParts[] = 'el.epin_load_code';
		if ($hasStatus) $selectParts[] = 'el.epin_load_status';
		if ($hasCost) $selectParts[] = 'el.epin_load_purchase_cost';
		if ($hasCurr) $selectParts[] = 'el.epin_load_purchase_currency';
		if ($hasRate) $selectParts[] = 'el.epin_load_purchase_rate';
		if ($hasTryAmt) $selectParts[] = 'el.epin_load_purchase_try_amount';

		// --- ORTAK SORGU YAPISI (Veri Çekme ve Toplam Kayıt Sayısı İçin) ---
		$this->db->from('epin_loads el')
			->join('epin_pool ep', 'ep.epin_pool_id = el.epin_load_pool_id', 'left')
			->join('epin_providers prov', 'prov.epin_provider_id = el.epin_load_provider_id', 'left')
			->join('admins adm', 'adm.admin_id = el.epin_load_admin_id', 'left')
			->where('el.epin_load_date >=', $startDate)
			->where('el.epin_load_date <=', $endDate);

		if (!empty($admin_id)) $this->db->where('el.epin_load_admin_id', (int)$admin_id);
		if (!empty($epin_pool_id)) $this->db->where('el.epin_load_pool_id', (int)$epin_pool_id);
		if (!empty($provider_id)) $this->db->where('el.epin_load_provider_id', (int)$provider_id);

		// Toplam Kayıt Sayısı İçin Sorguyu Klonla
		$tempDb = clone $this->db;
		$recordsTotal = $tempDb->count_all_results();

		// Asıl Veriyi Çek (Limit ve Offset ile)
		$loads = $this->db->select(implode(', ', $selectParts), false)
			->order_by('el.epin_load_id', 'DESC')
			->limit($length, $start)
			->get()->result_array();

		// Tablo Satırlarını Oluştur
		$dt = [];
		$statusMap = [
			0 => 'Yeni',
			1 => 'Başarılı',
			2 => 'Hatalı',
			3 => 'Onay Bekliyor',
			4 => 'Onaylandı',
			5 => 'Supplier İptal'
		];

		foreach ($loads as $load) {
			// -- Veri Formatlamaları --
			$loadCode = $hasCode ? htmlspecialchars($load['epin_load_code'] ?? '-') : '-';
			$statusText = $hasStatus && isset($statusMap[(int)$load['epin_load_status']]) ? $statusMap[(int)$load['epin_load_status']] : '-';
			$providerName = !empty($load['epin_provider_name']) ? htmlspecialchars($load['epin_provider_name']) : '<span class="text-muted">-</span>';

			// Maliyet ve Birimler
			$purchaseCostVal = $hasCost ? $load['epin_load_purchase_cost'] : null;
			$purchaseCurrency = $hasCurr ? htmlspecialchars($load['epin_load_purchase_currency'] ?? '') : '';
			$formattedCost = ($purchaseCostVal !== null && is_numeric($purchaseCostVal))
				? number_format((float)$purchaseCostVal, 2, ',', '.') . ($purchaseCurrency ? ' ' . $purchaseCurrency : '')
				: htmlspecialchars((string)($purchaseCostVal ?? '-'));

			$purchaseRate = $hasRate ? $load['epin_load_purchase_rate'] : null;
			$formattedRate = (is_numeric($purchaseRate)) ? number_format((float)$purchaseRate, 4, ',', '.') : '-';
			$rawRate = (is_numeric($purchaseRate)) ? number_format((float)$purchaseRate, 4, '.', '') : '';

			$tryAmount = $hasTryAmt ? $load['epin_load_purchase_try_amount'] : null;
			$unitCostTry = (is_numeric($tryAmount) && $load['epin_load_count'] > 0) ? number_format((float)$tryAmount / $load['epin_load_count'], 2, ',', '.') . ' TL' : '-';
			$totalCostTry = is_numeric($tryAmount) ? number_format((float)$tryAmount, 2, ',', '.') . ' TL' : '-';

			// JS Edit butonu için gerekli raw inputları HTML içine gömüyoruz
			$hiddenInputs = '
                <input type="hidden" class="epin-load-purchase-cost" value="' . htmlspecialchars($purchaseCostVal ?? '') . '">
                <input type="hidden" class="epin-load-purchase-currency" value="' . $purchaseCurrency . '">
            ';

			$dt[] = [
				'<span class="badge badge-light fw-bold" data-epin-load-id="' . $load['epin_load_id'] . '">' . $load['epin_load_id'] . '</span>',
				'<span class="text-gray-800 fw-bold">' . $load['admin_name'] . '</span>',
				$load['epin_pool_title'],
				'<span data-epin-load-code="' . $loadCode . '">' . $loadCode . '</span>',
				'<div class="text-center"><span class="badge badge-light-success fw-bold px-3 py-2 fs-7">' . $load['epin_load_count'] . ' Adet</span></div>',
				$statusText,
				$providerName,
				$formattedCost . $hiddenInputs,
				'<span data-epin-load-purchase-rate="' . $rawRate . '">' . $formattedRate . '</span>',
				$unitCostTry,
				$totalCostTry,
				'<div class="text-end text-gray-700" data-epin-load-date="' . date('d.m.Y H:i', strtotime($load['epin_load_date'])) . '">' . date('d.m.Y H:i', strtotime($load['epin_load_date'])) . '</div>',
				'<div class="text-end"><button class="btn btn-icon btn-sm btn-light-primary fw-bold epin-load-edit" data-bs-toggle="tooltip" title="Yükleme Detaylarını Düzenle" data-epin-load-id="' . $load['epin_load_id'] . '"><i class="ki-outline ki-pencil fs-3"></i></button></div>'
			];
		}

		$output = [
			"draw" => $draw,
			"recordsTotal" => $recordsTotal,
			"recordsFiltered" => $recordsTotal,
			"data" => $dt
		];

		return json($output);
	}

	public function cancelPin()
	{
		$epinID = $this->input->post('epin_id');
		if (empty($epinID)) {
			return json(['status' => 'error', 'message' => 'Lütfen geçerli bir pin ID\'si sağlayınız.']);
		}
		$update = $this->db->where('epin_id', $epinID)->update('epins', ['epin_status' => 2]);
		if ($update) {
			return json(['status' => 'success', 'message' => 'Pin başarıyla iptal edildi.']);
		} else {
			return json(['status' => 'error', 'message' => 'Pin iptal edilirken bir hata oluştu.']);
		}
	}

	public function downloadEpins()
	{
		die;
		$poolID = $this->uri->segment(3);

		$epins = $this->db->select('*')
			->from('epins')
			->where('epin_pool_id', $poolID)
			->get()->result();

		$this->load->library('encryption');

		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="epins_' . $poolID . '_' . date('Y-m-d_H-i-s') . '.txt"');
		header('Pragma: no-cache');
		header('Expires: 0');

		foreach ($epins as $epin) {
			try {
				echo $this->encryption->decrypt($epin->epin_code) . "\n";
			} catch (Exception $e) {
				echo "[Şifre Çözülemedi]\n";
			}
		}

		exit;
	}

	public function epinSaleAverage($poolID)
	{
		$epinPoolID = $poolID;

		// 1. Tek query ile hem listeyi hem de genel ortalamayı (subquery olmadan) alalım.
		// Veritabanı seviyesinde hesaplama yapmak her zaman daha hızlıdır.
		$this->db->select("DATE(epin_selled_at) as sale_date, COUNT(*) as total_sold");
		$this->db->from('epins');
		$this->db->where([
			'epin_pool_id' => $epinPoolID,
			'epin_status'  => 1
		]);
		$this->db->group_by('sale_date');
		$this->db->order_by('sale_date', 'DESC'); // Genelde güncel veriyi önce görmek istersin

		$result = $this->db->get()->result_array();

		if (empty($result)) {
			return 0; // Satış yoksa ortalama da 0 olur, bölme hatası olmaması için erken dönüş yapıyoruz.
		}

		// 2. Output Buffer ve String birleştirme
		// Echo yerine tek seferde çıktı vermek veya buffer kullanmak daha profesyoneldir.
		$output = "Satış Tarihi | Satılan Epin Adedi<br>";
		$totalSold = 0;

		foreach ($result as $row) {
			$output .= "{$row['sale_date']} | {$row['total_sold']}<br>";
			$totalSold += $row['total_sold'];
		}

		$totalDays = count($result);
		$averageSales = $totalSold / $totalDays;

		//$output .= "<br><br>Ortalama Günlük Satış Adedi: " . (int)$averageSales;

		return (int)$averageSales;
	}

	public function turkpinProducts()
	{
		$data = $this->data;
		$this->load->library('turkpin');
		$turkpin = new Turkpin();
		$jsonPath = FCPATH . 'json/turkpin_categories.json';

		// 1. Yerel JSON'dan kategorileri oku
		if (!file_exists($jsonPath)) {
			die("Hata: JSON dosyası yok. Önce oluşturmalısın.");
		}
		$categories = json_decode(file_get_contents($jsonPath), true);
		$data['categories'] = $categories;

		// 2. Kategori Seçilmişse
		$categoryID = (int)$this->input->get('category_id');
		$data['selected_category_id'] = $categoryID;
		$data['products'] = array();

		if ($categoryID > 0) {
			$apiResponse = $turkpin->get_products($categoryID);
			// Hata kontrolü ve doğru array path'i (Epin listesi için)
			if (isset($apiResponse['epinUrunListesi']['urun'])) {
				$data['products'] = $apiResponse['epinUrunListesi']['urun'];
			}
			// Eğer top-up veya genel listeyse alternatif path:
			elseif (isset($apiResponse['urunListesi']['urun'])) {
				$data['products'] = $apiResponse['urunListesi']['urun'];
			}
		}
		// pre($data['products']);die;
		$data['epin_pools'] = $this->db->select('epin_pool_id, epin_pool_title')->where('epin_pool_status', 1)->get('epin_pool')->result_array();
		foreach ($data['epin_pools'] as &$pool) {
			$stockCount = $this->db->select('COUNT(*) as count')->from('epins')->where(['epin_pool_id' => $pool['epin_pool_id'], 'epin_status' => 0])->get()->row()->count;
			$pool['stock'] = $stockCount;
		}
		$this->template->content->view('turkpin.php', $data);
		$this->template->publish();
	}

	public function buyTurkpin()
	{
		$post = $this->input->post();
		$post['epin_provider_id'] = 3; // Turkpin provider_id'si
		// pre($post);die;
		// 1. Validasyon
		if (empty($post['catID']) || empty($post['productID']) || empty($post['qty']) || empty($post['epin_pool_id'])) {
			$this->session->set_flashdata('alert', [
				'status' => 'error',
				'message' => 'Lütfen tüm alanları (havuz, ürün, adet) doldurunuz.'
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$this->load->library('turkpin');
		$this->load->library('encryption');
		$turkpin = new Turkpin();

		$qty = (int)$post['qty'];

		// 2. API Siparişi
		$order = $turkpin->create_order($post['catID'], $post['productID'], $qty);

		if (empty($order) || !isset($order['HATA_NO']) || $order['HATA_NO'] !== '000' || $order['siparisSonuc'] !== 'Success') {
			$msg = isset($order['HATA_ACIKLAMA']) ? $order['HATA_ACIKLAMA'] : 'Turkpin siparişi başarısız.';
			$this->session->set_flashdata('alert', [
				'status' => 'error',
				'message' => 'Turkpin API Hatası: ' . $msg
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$rawEpins = isset($order['epin_list']['epin']) ? $order['epin_list']['epin'] : [];
		if (empty($rawEpins)) {
			$this->session->set_flashdata('alert', [
				'status' => 'error',
				'message' => 'Sipariş başarılı fakat dönen veride epin bulunamadı.'
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$epinList = isset($rawEpins['code']) ? [$rawEpins] : $rawEpins;

		$purchaseCost = (float)($order['siparisTutari'] ?? 0);
		$purchaseRate = 1.0;
		$purchaseCurrency = 'TRY';
		$tryAmount = $purchaseCost * $purchaseRate;

		$randLoadID = generateRandomString(10, 'AZ09');
		$loadCount = 0;
		$errorPinArray = [];

		// 3. E-Pin Insert Döngüsü (Kendi yapın)
		foreach ($epinList as $item) {
			$code = trim((string)($item['code'] ?? ''));

			if (empty($code)) continue;

			$code = trim($code);
			//$code = str_replace([" ", "\r", "\n", "\t", "\0"], '', $code);
			$encrypted_pin = $this->encryption->encrypt($code);
			$maskedPin = keyMask($code);

			$insertArray = [
				'epin_load_id' => $randLoadID,
				'epin_pool_id' => (int)$post['epin_pool_id'],
				'epin_code' => $encrypted_pin,
				'epin_code_masked' => $maskedPin,
				'epin_status' => 0,
			];

			$this->db->insert('epins', $insertArray);
			if ($this->db->affected_rows() > 0) {
				$loadCount++;
			} else {
				$errorPinArray[] = $code;
			}
		}

		// 4. Epin Load Kaydı
		$loadInsertData = [
			'epin_load_code' => $randLoadID,
			'epin_load_pool_id' => (int)$post['epin_pool_id'],
			'epin_load_count' => (int)$loadCount,
			'epin_load_admin_id' => (int)$this->session->userdata('admin')['admin_id'],
			'epin_load_provider_id' => (int)$post['epin_provider_id'],
		];

		if ($this->db->field_exists('epin_load_status', 'epin_loads')) {
			$loadInsertData['epin_load_status'] = 1;
		}
		if ($this->db->field_exists('epin_load_date', 'epin_loads')) {
			$loadInsertData['epin_load_date'] = date('Y-m-d H:i:s');
		}
		if ($this->db->field_exists('epin_load_purchase_cost', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_cost'] = number_format($purchaseCost, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_currency', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_currency'] = $purchaseCurrency;
		}
		if ($this->db->field_exists('epin_load_purchase_rate', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_rate'] = number_format($purchaseRate, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_try_amount', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_try_amount'] = number_format($tryAmount, 6, '.', '');
		}

		$this->db->insert('epin_loads', $loadInsertData);
		$epinLoadId = (int)$this->db->insert_id();

		// 5. Bakiye Logu
		$balanceError = false;
		if ($loadCount > 0) {
			$providerId = (int)$post['epin_provider_id'];
			$adminId = (int)($this->session->userdata('admin')['admin_id'] ?? 0);

			$balanceLogged = $this->logProviderBalanceDebit(
				$providerId,
				$adminId,
				$purchaseCurrency,
				$purchaseCost,
				$purchaseRate,
				$tryAmount,
				$epinLoadId
			);
			if (!$balanceLogged) {
				$balanceError = true;
			}
		}

		// 6. Mesajları Birleştirip Tek Toastr Gönderimi
		$alertMessages = [];
		$alertStatus = 'success'; // Default olarak success başlat

		if ($loadCount > 0) {
			$alertMessages[] = $loadCount . ' adet pin başarıyla satın alındı ve yüklendi.';
		}

		if ($balanceError) {
			$alertStatus = 'warning';
			$alertMessages[] = 'Stok eklendi fakat provider_balances harcama kaydı yazılamadı!';
		}

		if ($loadCount < $qty) {
			$alertStatus = $loadCount > 0 ? 'warning' : 'error';
			$alertMessages[] = 'Sipariş edilen: ' . $qty . ' / Yüklenemeyen: ' . ($qty - $loadCount);
		}

		if (!empty($errorPinArray)) {
			$alertStatus = 'error';
			$alertMessages[] = 'Veritabanına yazılamayan pinler: ' . implode(', ', $errorPinArray);
		}

		// Oluşan mesajları araya br atarak birleştir
		if (!empty($alertMessages)) {
			$this->session->set_flashdata('alert', [
				'status' => $alertStatus,
				'message' => implode(' | ', $alertMessages)
			]);
		}

		redirect($_SERVER['HTTP_REFERER']);
	}

	public function bambooProducts()
	{
		$data = $this->data;
		$this->load->library('bamboo');
		$bamboo = new Bamboo();
		$jsonPath = FCPATH . 'json/bamboo_categories.json';

		// 1. Yerel JSON'dan kategorileri oku
		if (!file_exists($jsonPath)) {
			die("Hata: JSON dosyası yok. Önce oluşturmalısın.");
		}
		$categories = json_decode(file_get_contents($jsonPath), true);
		$data['categories'] = $categories;
		// 2. Kategori Seçilmişse
		$categoryID = (int)$this->input->get('category_id');
		$data['selected_category_id'] = $categoryID;
		$data['products'] = array();

		if ($categoryID > 0) {
			// API'ye Canlı İstek (Turkpin mimarisi)
			// Not: Bamboo'nun filtre parametrelerine göre burayı optimize edebilirsin.
			$filters = [
				'PageSize'  => 100,
				'PageIndex' => 0
			];
			if ($categoryID > 0) {
				$filters['BrandId'] = $categoryID;
			}

			$apiResponse = $bamboo->getCatalog($filters);
			$data['currencyCode'] = isset($apiResponse->data->items[0]->currencyCode) ? $apiResponse->data->items[0]->currencyCode : 'USD';

			if ($apiResponse->status && !empty($apiResponse->data->items)) {
				foreach ($apiResponse->data->items as $brandItem) {

					// Seçilen Oyunun ID'si (1233), Bamboo'daki internalId ile eşleşiyor mu?
					if ((string)$brandItem->internalId === (string)$categoryID) {

						// Oyun eşleşti, alt varyasyonları (UC paketlerini) listeye ekle
						if (!empty($brandItem->products)) {
							foreach ($brandItem->products as $prod) {
								$data['products'][] = [
									'id'    => $prod->id, // Örn: 807477 (Satın alımda Bamboo'ya gidecek asıl ID)
									'name'  => $prod->name, // Örn: PUBG 16200 UC
									'price' => isset($prod->price->min) ? $prod->price->min : 0,
									'stock' => ($prod->count !== null && $prod->count !== "") ? $prod->count : 'Sınırsız',
									'faceValue' => isset($prod->minFaceValue) ? $prod->minFaceValue : 1,
								];
							}
						}

						// Aradığımız oyunu bulduk ve ürünlerini aldık. 
						// Kalan markaları boşuna dönmemek için döngüyü kırıp çıkıyoruz. (Performans)
						break;
					}
				}
			}
		}
		if (!empty($data['products'])) {
			usort($data['products'], function ($a, $b) {
				return $a['price'] <=> $b['price'];
				// Not: Büyükten küçüğe sıralamak istersen tam tersi yap: return $b['price'] <=> $a['price'];
			});
		}

		$data['epin_pools'] = $this->db->select('epin_pool_id, epin_pool_title')->where('epin_pool_status', 1)->get('epin_pool')->result_array();

		// epin pool stocks
		foreach ($data['epin_pools'] as &$pool) {
			$stockCount = $this->db->select('COUNT(*) as count')->from('epins')->where(['epin_pool_id' => $pool['epin_pool_id'], 'epin_status' => 0])->get()->row()->count;
			$pool['stock'] = $stockCount;
		}
		$this->template->content->view('bamboo.php', $data);
		$this->template->publish();
	}

	public function buyBamboo()
	{
		$post = $this->input->post();
		$post['epin_provider_id'] = 4; // Bamboo provider_id'si

		// 1. Validasyon
		if (empty($post['catID']) || empty($post['productID']) || empty($post['qty']) || empty($post['epin_pool_id'])) {
			$this->session->set_flashdata('alert', [
				'status' => 'error',
				'message' => 'Lütfen tüm alanları (havuz, ürün, adet) doldurunuz.'
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		$this->load->library('bamboo');
		$this->load->library('encryption');
		$bamboo = new Bamboo();

		$qty = (int)$post['qty'];
		$productId = $post['productID'];
		$faceValue = (float)$post['faceValue']; // Formdan gelen Face Value

		$transactionId = $this->generateUuidV4();
		$additionalData = [];

		// Library'ye yeni parametre ile gönderiyoruz
		$purchaseResponse = $bamboo->createOrder($productId, $qty, $transactionId, $faceValue, $additionalData);

		if (!$purchaseResponse->status || empty($purchaseResponse->data)) {
			$msg = isset($purchaseResponse->error) ? $purchaseResponse->error : 'Bilinmeyen hata';
			$this->session->set_flashdata('alert', [
				'status' => 'error',
				'message' => 'Bamboo Sipariş Hatası: ' . $msg
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		// Başarılıysa bize UUID dönüyor
		$orderUuid = $purchaseResponse->data;

		// 3. Sipariş Detayını ve E-Pinleri Çek (POLLING / DÖNGÜ)
		$maxAttempts = 10; // Maksimum deneme sayısı
		$sleepSeconds = 2; // Her deneme arası bekleme süresi
		$orderResponse = null;
		$orderSuccess = false;

		for ($i = 0; $i < $maxAttempts; $i++) {
			$orderResponse = $bamboo->getOrder($orderUuid);

			// Eğer API başarılı dönüş yaptıysa ve statü "Succeeded" ise
			if ($orderResponse->status && !empty($orderResponse->data) && $orderResponse->data->status === 'Succeeded') {
				$orderSuccess = true;
				break; // Döngüden çık, pinleri aldık!
			}

			// Eğer statü "Failed" veya benzeri bir kalıcı hata ise boşuna bekleme
			if ($orderResponse->status && !empty($orderResponse->data) && in_array($orderResponse->data->status, ['Failed', 'Cancelled'])) {
				break;
			}

			// Succeeded değilse biraz bekle ve tekrar dene
			sleep($sleepSeconds);
		}

		// 10 denemeye rağmen hala başarılı olmadıysa
		if (!$orderSuccess) {
			$apiStatus = $orderResponse->data->status ?? 'Bilinmiyor';
			$this->session->set_flashdata('alert', [
				'status' => 'warning',
				'message' => "Sipariş Bamboo'ya iletildi ancak zaman aşımı süresince pinler alınamadı (Statü: {$apiStatus}). Lütfen Bamboo paneli üzerinden sipariş UUID'sini ({$orderUuid}) kontrol ediniz."
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		// 4. Pin (Card) Listesini Çıkart
		$cards = [];
		if (!empty($orderResponse->data->items[0]->cards)) {
			$cards = $orderResponse->data->items[0]->cards;
		}

		if (empty($cards)) {
			$this->session->set_flashdata('alert', [
				'status' => 'error',
				'message' => 'Sipariş "Succeeded" olarak işaretlendi fakat dönen veride epin (kart) bulunamadı.'
			]);
			redirect($_SERVER['HTTP_REFERER']);
		}

		// Maliyet ve Kur Hesaplamaları
		$purchaseCost = (float)($orderResponse->data->total ?? 0);
		$purchaseCurrency = $orderResponse->data->currency ?? 'USD';

		$purchaseRate = currencyCalculator($purchaseCurrency, 1);
		$tryAmount = $purchaseCost * $purchaseRate;

		$randLoadID = generateRandomString(10, 'AZ09');
		$loadCount = 0;
		$errorPinArray = [];

		// 5. E-Pin Insert Döngüsü (Senin yapı)
		foreach ($cards as $card) {

			$code = trim((string)($card->cardCode ?? ''));
			$pinSecret = trim((string)($card->pin ?? ''));

			if (!empty($pinSecret)) {
				$code .= ' - ' . $pinSecret;
			}

			if (empty($code)) continue;

			$encrypted_pin = $this->encryption->encrypt($code);
			$maskedPin = keyMask($code);

			$insertArray = [
				'epin_load_id' => $randLoadID,
				'epin_pool_id' => (int)$post['epin_pool_id'],
				'epin_code' => $encrypted_pin,
				'epin_code_masked' => $maskedPin,
				'epin_status' => 0,
			];

			$this->db->insert('epins', $insertArray);
			if ($this->db->affected_rows() > 0) {
				$loadCount++;
			} else {
				$errorPinArray[] = $code;
			}
		}

		// 6. Epin Load Kaydı
		$loadInsertData = [
			'epin_load_code' => $randLoadID,
			'epin_load_pool_id' => (int)$post['epin_pool_id'],
			'epin_load_count' => (int)$loadCount,
			'epin_load_admin_id' => (int)$this->session->userdata('admin')['admin_id'],
			'epin_load_provider_id' => (int)$post['epin_provider_id'],
		];

		if ($this->db->field_exists('epin_load_status', 'epin_loads')) {
			$loadInsertData['epin_load_status'] = 1;
		}
		if ($this->db->field_exists('epin_load_date', 'epin_loads')) {
			$loadInsertData['epin_load_date'] = date('Y-m-d H:i:s');
		}
		if ($this->db->field_exists('epin_load_purchase_cost', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_cost'] = number_format($purchaseCost, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_currency', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_currency'] = $purchaseCurrency;
		}
		if ($this->db->field_exists('epin_load_purchase_rate', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_rate'] = number_format($purchaseRate, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_try_amount', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_try_amount'] = number_format($tryAmount, 6, '.', '');
		}

		$this->db->insert('epin_loads', $loadInsertData);
		$epinLoadId = (int)$this->db->insert_id();

		// 7. Bakiye Logu
		$balanceError = false;
		if ($loadCount > 0) {
			$providerId = (int)$post['epin_provider_id'];
			$adminId = (int)($this->session->userdata('admin')['admin_id'] ?? 0);

			$balanceLogged = $this->logProviderBalanceDebit(
				$providerId,
				$adminId,
				$purchaseCurrency,
				$purchaseCost,
				$purchaseRate,
				$tryAmount,
				$epinLoadId
			);
			if (!$balanceLogged) {
				$balanceError = true;
			}
		}

		// 8. Mesajları Birleştirip Tek Toastr Gönderimi
		$alertMessages = [];
		$alertStatus = 'success';

		if ($loadCount > 0) {
			$alertMessages[] = $loadCount . ' adet pin başarıyla Bamboo API\'den satın alındı ve yüklendi.';
		}

		if ($balanceError) {
			$alertStatus = 'warning';
			$alertMessages[] = 'Stok eklendi fakat provider_balances harcama kaydı yazılamadı!';
		}

		if ($loadCount < $qty) {
			$alertStatus = $loadCount > 0 ? 'warning' : 'error';
			$alertMessages[] = 'Sipariş edilen: ' . $qty . ' / Yüklenemeyen: ' . ($qty - $loadCount);
		}

		if (!empty($errorPinArray)) {
			$alertStatus = 'error';
			$alertMessages[] = 'Veritabanına yazılamayan pinler mevcut.';
		}

		if (!empty($alertMessages)) {
			$this->session->set_flashdata('alert', [
				'status' => $alertStatus,
				'message' => implode(' | ', $alertMessages)
			]);
		}

		redirect($_SERVER['HTTP_REFERER']);
	}

	private function generateUuidV4()
	{
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);
	}

	public function addPins()
	{
		$data = $this->data;

		// pre($pool);die;
		$data['pools'] = $this->db->select('epin_pool_id, epin_pool_title')->where('epin_pool_status', 1)->get('epin_pool')->result();
		$data['epin_providers'] = $this->db->select('*')->from('epin_providers')->get()->result();
		$this->template->content->view('addPins', $data);
		$this->template->publish();
	}

	public function updateEpinLoad()
	{
		// 1. Method & Input Validation
		if ($this->input->method() !== 'post') {
			show_404();
		}

		$post = $this->input->post(null, true);
		$loadId = (int)($post['epin_load_id'] ?? 0);

		if ($loadId <= 0) {
			$this->session->set_flashdata('error', 'Geçersiz yükleme kaydı.');
			redirect($_SERVER['HTTP_REFERER']);
		}

		// 2. Veri Sanitizasyonu ve Type Casting
		$costRaw = trim((string)($post['epin_pool_cost'] ?? '0'));
		$rateRaw = trim((string)($post['epin-load-purchase-rate'] ?? '1'));
		$currency = strtoupper(trim((string)($post['epin_load_cost_currency'] ?? 'TRY')));

		$purchaseCost = (float)str_replace(',', '.', $costRaw);
		$purchaseRate = (float)str_replace(',', '.', $rateRaw);

		// 3. Temel Validasyonlar
		if ($purchaseCost < 0 || $purchaseRate <= 0) {
			$this->session->set_flashdata('error', 'Maliyet ve kur değerleri hatalı girildi.');
			redirect($_SERVER['HTTP_REFERER']);
		}

		$allowedCurrencies = ['TRY', 'USD', 'EUR', 'AED', 'IDR'];
		if (!in_array($currency, $allowedCurrencies, true)) {
			$this->session->set_flashdata('error', 'Geçersiz para birimi.');
			redirect($_SERVER['HTTP_REFERER']);
		}

		// 4. Eski Yükleme Kaydını Çek
		$oldLoad = $this->db->select('epin_load_id, epin_load_provider_id')
			->from('epin_loads')
			->where('epin_load_id', $loadId)
			->get()
			->row();

		if (empty($oldLoad)) {
			$this->session->set_flashdata('error', 'Yükleme geçmişi bulunamadı.');
			redirect($_SERVER['HTTP_REFERER']);
		}

		// 5. Tablo kontrolleri
		if (!$this->db->table_exists('provider_balances')) {
			$this->session->set_flashdata('error', 'Provider bakiye tablosu bulunamadı.');
			redirect($_SERVER['HTTP_REFERER']);
		}
		$requiredBalanceCols = [
			'provider_balance_id',
			'provider_balance_provider_id',
			'provider_balance_currency',
			'provider_balance_amount',
			'provider_balance_amount_try',
			'provider_balance_type',
			'provider_balance_note',
			'provider_balance_updated_at',
		];
		foreach ($requiredBalanceCols as $requiredCol) {
			if (!$this->db->field_exists($requiredCol, 'provider_balances')) {
				$this->session->set_flashdata('error', 'Provider balances kolon eksik: ' . $requiredCol);
				redirect($_SERVER['HTTP_REFERER']);
			}
		}

		// 6. Yeni TRY Tutarını Hesapla
		$newTryAmount = $purchaseCost * $purchaseRate;

		// --- TRANSACTION BAŞLANGICI ---
		$this->db->trans_start();

		// 7. Epin Loads Tablosunu Güncelle
		$updateLoadData = [];
		if ($this->db->field_exists('epin_load_purchase_cost', 'epin_loads')) {
			$updateLoadData['epin_load_purchase_cost'] = number_format($purchaseCost, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_currency', 'epin_loads')) {
			$updateLoadData['epin_load_purchase_currency'] = $currency;
		}
		if ($this->db->field_exists('epin_load_purchase_rate', 'epin_loads')) {
			$updateLoadData['epin_load_purchase_rate'] = number_format($purchaseRate, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_try_amount', 'epin_loads')) {
			$updateLoadData['epin_load_purchase_try_amount'] = number_format($newTryAmount, 6, '.', '');
		}

		if (!empty($updateLoadData)) {
			$this->db->where('epin_load_id', $loadId)->update('epin_loads', $updateLoadData);
		}

		// 8. Provider balances kaydini UPDATE et (INSERT yok)
		$targetBalance = $this->db->select('provider_balance_id')
			->from('provider_balances')
			->where('provider_balance_provider_id', (int)$oldLoad->epin_load_provider_id)
			->where('provider_balance_type', 'debit')
			->group_start()
			->where('provider_balance_note', 'epin_load_id=' . $loadId);

		if ($this->db->field_exists('provider_balance_source_id', 'provider_balances')) {
			$this->db->or_where('provider_balance_source_id', $loadId);
		}

		$targetBalance = $this->db
			->group_end()
			->order_by('provider_balance_id', 'DESC')
			->limit(1)
			->get()
			->row();

		if (empty($targetBalance)) {
			$this->db->trans_rollback();
			$this->session->set_flashdata('error', 'Guncellenecek provider balance kaydi bulunamadi.');
			redirect($_SERVER['HTTP_REFERER']);
		}

		$balanceUpdateData = [
			'provider_balance_currency' => $currency,
			'provider_balance_amount' => number_format($purchaseCost * -1, 6, '.', ''),
			'provider_balance_amount_try' => number_format($newTryAmount, 6, '.', ''),
			'provider_balance_note' => 'epin_load_id=' . $loadId,
			'provider_balance_updated_at' => date('Y-m-d H:i:s')
		];

		$this->db->where('provider_balance_id', (int)$targetBalance->provider_balance_id)
			->update('provider_balances', $balanceUpdateData);

		// --- TRANSACTION BİTİŞİ ---
		$this->db->trans_complete();

		// 9. Sonuç Kontrolü ve Mesaj
		if ($this->db->trans_status() === FALSE) {
			$this->session->set_flashdata('error', 'Veritabanı güncellenirken kritik bir hata oluştu. İşlem geri alındı.');
		} else {
			$this->session->set_flashdata('success', 'Yükleme maliyetleri ve muhasebe kayıtları başarıyla güncellendi.');
		}

		redirect($_SERVER['HTTP_REFERER']);
	}

	public function stockTransfer()
	{
		$data = $this->data;

		// 1. Girdi kontrolü ve XSS/Injection güvenliği
		$loadCode = $this->security->xss_clean($this->uri->segment(3));

		if (empty($loadCode)) {
			show_error('Geçersiz yükleme kodu.', 400, 'Hatalı İstek');
		}

		// 2. select('*') yerine ihtiyaç olanları belirtmek MariaDB I/O yükünü hafifletir.
		$data['load'] = $this->db->select('el.*, ep.epin_provider_name, ep.epin_provider_currency')
			->from('epin_loads el')
			->join('epin_providers ep', 'ep.epin_provider_id = el.epin_load_provider_id', 'left')
			->where('el.epin_load_code', $loadCode)
			->get()
			->row();

		if (empty($data['load'])) {
			// MVC yapısını ve HTTP yanıtlarını bozmamak için show_error veya redirect kullanılmalı.
			show_error('Yükleme kaydı bulunamadı.', 404, 'Kayıt Bulunamadı');
		}

		// 3. KRİTİK OPTİMİZASYON: PHP memory yerine veritabanı motoruna saydırmak (O(1) veya indexe bağlı O(log n))
		$data['poolStockCount'] = $this->db->where('epin_load_id', $loadCode)
			->where('epin_status', 0)
			->count_all_results('epins');

		// 4. Havuzların çekilmesi (Zaten optimize edilmiş durumda)
		$data['pools'] = $this->db->select('epin_pool_id, epin_pool_title')
			->where('epin_pool_status', 1)
			->get('epin_pool')
			->result();

		$this->template->content->view('stock-transfer', $data);
		$this->template->publish();
	}

	public function stockTransferProcess()
	{
		if ($this->input->method() !== 'post') {
			show_404();
		}

		$this->load->library('user_agent');
		$redirectUrl = $this->agent->referrer() ?: base_url('epin/list');

		$post = $this->input->post();
		$loadCode = trim((string)($post['load_code'] ?? ''));
		$transferCount = (int)($post['transfer_count'] ?? 0);
		$targetPoolId = (int)($post['target_pool_id'] ?? 0);

		if ($loadCode === '' || $transferCount <= 0 || $targetPoolId <= 0) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Lütfen tüm transfer alanlarını doğru doldurunuz.']);
			redirect($redirectUrl);
		}

		$sourceLoad = $this->db->select('epin_load_pool_id, epin_load_provider_id, epin_load_purchase_cost, epin_load_purchase_currency, epin_load_purchase_rate, epin_load_purchase_try_amount')
			->from('epin_loads')
			->where('epin_load_code', $loadCode)
			->get()
			->row();

		if (empty($sourceLoad)) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Kaynak yükleme bulunamadı.']);
			redirect($redirectUrl);
		}

		$sourcePoolId = (int)$sourceLoad->epin_load_pool_id;
		$sourcePurchaseCost = (float)($sourceLoad->epin_load_purchase_cost ?? 0);
		$sourcePurchaseCurrency = strtoupper(trim((string)($sourceLoad->epin_load_purchase_currency ?? 'TRY')));
		$sourcePurchaseRate = (float)($sourceLoad->epin_load_purchase_rate ?? 1);
		$sourcePurchaseTryAmount = (float)($sourceLoad->epin_load_purchase_try_amount ?? ($sourcePurchaseCost * $sourcePurchaseRate));
		if ($sourcePurchaseCurrency === '') {
			$sourcePurchaseCurrency = 'TRY';
		}
		if ($sourcePurchaseRate <= 0) {
			$sourcePurchaseRate = 1;
		}
		if ($sourcePurchaseTryAmount <= 0 && $sourcePurchaseCost > 0) {
			$sourcePurchaseTryAmount = $sourcePurchaseCost * $sourcePurchaseRate;
		}

		if ($targetPoolId === $sourcePoolId) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Hedef havuz, mevcut havuzdan farklı olmalıdır.']);
			redirect($redirectUrl);
		}

		$targetPool = $this->db->select('epin_pool_id')
			->from('epin_pool')
			->where('epin_pool_id', $targetPoolId)
			->where('epin_pool_status', 1)
			->get()
			->row();

		if (empty($targetPool)) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Hedef havuz bulunamadı veya pasif durumda.']);
			redirect($redirectUrl);
		}

		$availableCount = $this->db->where('epin_load_id', $loadCode)
			->where('epin_pool_id', $sourcePoolId)
			->where('epin_status', 0)
			->count_all_results('epins');

		if ($availableCount < $transferCount) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Aktarılabilir stok yetersiz. Uygun stok: ' . (int)$availableCount]);
			redirect($redirectUrl);
		}

		$adminId = (int)($this->session->userdata('admin')['admin_id'] ?? 0);
		$newLoadCode = '';
		$attempts = 0;
		do {
			$candidateCode = generateRandomString(10, 'AZ09');
			$exists = $this->db->select('epin_load_id, epin_load_code, epin_load_count')
				->from('epin_loads')
				->where('epin_load_code', $candidateCode)
				->limit(1)
				->get()
				->row();

			if (empty($exists)) {
				$newLoadCode = $candidateCode;
			}
			$attempts++;
		} while ($newLoadCode === '' && $attempts < 5);

		if ($newLoadCode === '') {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Yeni yükleme kodu üretilemedi. Lütfen tekrar deneyiniz.']);
			redirect($redirectUrl);
		}

		$this->db->trans_start();

		$selectedPins = $this->db->select('epin_id')
			->from('epins')
			->where('epin_load_id', $loadCode)
			->where('epin_pool_id', $sourcePoolId)
			->where('epin_status', 0)
			->order_by('epin_id', 'ASC')
			->limit($transferCount)
			->get()
			->result_array();

		$selectedPinIds = array_column($selectedPins, 'epin_id');
		if (count($selectedPinIds) !== $transferCount) {
			$this->db->trans_rollback();
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Transfer sırasında stok değişti. Lütfen tekrar deneyiniz.']);
			redirect($redirectUrl);
		}

		$loadInsertData = [
			'epin_load_code' => $newLoadCode,
			'epin_load_pool_id' => $targetPoolId,
			'epin_load_count' => $transferCount,
			'epin_load_admin_id' => $adminId,
			'epin_load_provider_id' => (int)($sourceLoad->epin_load_provider_id ?? 0),
		];

		if ($this->db->field_exists('epin_load_status', 'epin_loads')) {
			$loadInsertData['epin_load_status'] = 1;
		}
		if ($this->db->field_exists('epin_load_date', 'epin_loads')) {
			$loadInsertData['epin_load_date'] = date('Y-m-d H:i:s');
		}
		if ($this->db->field_exists('epin_load_purchase_cost', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_cost'] = number_format($sourcePurchaseCost, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_currency', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_currency'] = $sourcePurchaseCurrency;
		}
		if ($this->db->field_exists('epin_load_purchase_rate', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_rate'] = number_format($sourcePurchaseRate, 6, '.', '');
		}
		if ($this->db->field_exists('epin_load_purchase_try_amount', 'epin_loads')) {
			$loadInsertData['epin_load_purchase_try_amount'] = number_format($sourcePurchaseTryAmount, 6, '.', '');
		}

		$this->db->insert('epin_loads', $loadInsertData);

		$this->db->where_in('epin_id', $selectedPinIds)
			->where('epin_status', 0)
			->update('epins', [
				'epin_pool_id' => $targetPoolId,
				'epin_load_id' => $newLoadCode,
			]);

		if ($this->db->affected_rows() !== $transferCount) {
			$this->db->trans_rollback();
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Transfer tamamlanamadı. Lütfen tekrar deneyiniz.']);
			redirect($redirectUrl);
		}

		$oldepinLoad = $this->db->select('epin_load_id, epin_load_count')
			->from('epin_loads')
			->where('epin_load_code', $loadCode)
			->get()
			->row();

		$this->db->trans_complete();

		// eski yüklemenin sayısını güncelle
		$newTotalCount = (int)($oldepinLoad->epin_load_count - $transferCount);
		$this->db->where('epin_load_code', $loadCode)
			->set('epin_load_count', $newTotalCount)
			->update('epin_loads');

		if ($this->db->trans_status() === false) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Transfer sırasında bir hata oluştu. İşlem geri alındı.']);
			redirect($redirectUrl);
		}

		$this->session->set_flashdata('alert', ['status' => 'success', 'message' => $transferCount . ' adet pin yeni bir yükleme kodu ile hedef havuza taşındı. Yeni kod: ' . $newLoadCode]);
		redirect($redirectUrl);
	}
}
