<?php defined('BASEPATH') or exit('No direct script access allowed');

class Products extends MY_Controller
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
		$products = $this->db->select('p.*, c.*, sp.*, pp.*, pr.*, sub_c.category_title as sub_category_title')
			->from('products as p')
			// Ana Kategori Bağlantısı
			->join('categories as c', 'c.category_id = p.product_category_id')
			// Alt Kategori Bağlantısı (LEFT JOIN: Çünkü alt kategori olmayabilir)
			->join('categories as sub_c', 'sub_c.category_id = p.product_sub_category_id', 'left')
			->join('seller_products as sp', 'sp.seller_products_product_id = p.product_id', 'left')
			->join('provider_products as pp', 'pp.provider_product_id = p.product_provider_id', 'left')
			->join('product_prices as pr', 'pr.product_price_product_id = p.product_id AND pr.product_price_country = "tr"', 'left')
			->where('p.product_status !=', 2)
			->get()->result();
		// pre($products);die;
		$suppliers = $this->db->select('*')->from('users')->where('user_type', 2)->get()->result();

		$supplierMap = [];
		foreach ($suppliers as $supplier) {
			$supplierMap[(int) $supplier->user_id] = $supplier->user_name;
		}

		$decodeJsonArray = static function ($value): array {
			if (empty($value)) {
				return [];
			}

			$decoded = is_array($value) ? $value : json_decode((string) $value, true);
			if (!is_array($decoded)) {
				return [];
			}

			$normalized = [];
			foreach ($decoded as $item) {
				if (!is_scalar($item)) {
					continue;
				}

				$text = trim((string) $item);
				if ($text === '') {
					continue;
				}

				$normalized[] = $text;
			}

			return array_values(array_unique($normalized));
		};

		$normalizeSearchText = static function ($value): string {
			if (is_array($value)) {
				$value = implode(' ', $value);
			}

			if (!is_scalar($value)) {
				return '';
			}

			$text = mb_strtolower(trim((string) $value), 'UTF-8');
			$text = preg_replace('/\s+/', ' ', $text);

			return is_string($text) ? $text : '';
		};

		$typeLabelMap = [
			1 => 'epin',
			2 => 'topup',
			3 => 'pin',
		];

		$typeAliasMap = [
			'epin' => ['epin', 'pin'],
			'pin' => ['pin', 'epin'],
			'topup' => ['topup'],
		];

		$filterOptions = [
			'categories' => [],
			'suppliers' => [],
			'product_types' => [],
			'category_types' => [],
			'countries' => [],
			'cost_currencies' => [],
			'price_currencies' => [],
			'product_statuses' => [],
			'product_api_statuses' => [],
			'category_api_statuses' => [],
		];

		$productCounts = [
			'total' => count($products),
			'active' => 0,
			'passive' => 0,
		];

		foreach ($products as $product) {
			$categoryId = (int) ($product->category_id ?? 0);
			$categoryTitle = trim((string) ($product->category_title ?? ''));

			if ($categoryId > 0) {
				$filterOptions['categories'][(string) $categoryId] = [
					'id' => $categoryId,
					'title' => $categoryTitle !== '' ? $categoryTitle : ('Kategori #' . $categoryId),
				];
			}

			$supplierId = (int) ($product->product_supplier_id ?? 0);
			$product->supplier_name = $supplierMap[$supplierId] ?? '';

			if ($supplierId > 0) {
				$filterOptions['suppliers'][(string) $supplierId] = [
					'id' => $supplierId,
					'name' => $product->supplier_name !== '' ? $product->supplier_name : ('Tedarikçi #' . $supplierId),
				];
			}

			$productType = isset($product->product_type) ? (int) $product->product_type : null;
			$categoryType = isset($product->category_type) ? (int) $product->category_type : null;

			$product->product_type_text = ($productType !== null)
				? ($typeLabelMap[$productType] ?? ('type-' . $productType))
				: '';
			$product->category_type_text = ($categoryType !== null)
				? ($typeLabelMap[$categoryType] ?? ('type-' . $categoryType))
				: '';

			if ($productType !== null) {
				$filterOptions['product_types'][(string) $productType] = [
					'value' => (string) $productType,
					'label' => $product->product_type_text,
				];
			}

			if ($categoryType !== null) {
				$filterOptions['category_types'][(string) $categoryType] = [
					'value' => (string) $categoryType,
					'label' => $product->category_type_text,
				];
			}

			$productCountries = array_map('strtoupper', $decodeJsonArray($product->product_countries ?? ''));
			$categoryCountries = array_map('strtoupper', $decodeJsonArray($product->category_countries ?? ''));
			$countries = array_values(array_unique(array_merge($productCountries, $categoryCountries)));
			$product->search_countries = $countries;

			foreach ($countries as $countryCode) {
				if ($countryCode === '') {
					continue;
				}
				$filterOptions['countries'][$countryCode] = $countryCode;
			}

			$requires = $decodeJsonArray($product->product_is_require_spesific ?? '');
			if ((int) ($product->product_is_require_user_id ?? 0) === 1) {
				$requires[] = 'user_id';
			}
			if ((int) ($product->product_is_require_server_id ?? 0) === 1) {
				$requires[] = 'server_id';
			}
			if ((int) ($product->product_is_require_zone_id ?? 0) === 1) {
				$requires[] = 'zone_id';
			}
			$requires = array_values(array_unique($requires));
			$product->requires_array = $requires;

			$productCostCurrency = strtoupper(trim((string) ($product->product_cost_currency ?? '')));
			$providerCostCurrency = strtoupper(trim((string) ($product->provider_product_product_cost_currency ?? '')));
			$productPriceCurrency = strtoupper(trim((string) ($product->product_price_currency ?? '')));

			$product->search_cost_currency = $productCostCurrency !== '' ? $productCostCurrency : $providerCostCurrency;
			$product->search_price_currency = $productPriceCurrency;

			if ($productCostCurrency !== '') {
				$filterOptions['cost_currencies'][$productCostCurrency] = $productCostCurrency;
			}
			if ($providerCostCurrency !== '') {
				$filterOptions['cost_currencies'][$providerCostCurrency] = $providerCostCurrency;
			}
			if ($productPriceCurrency !== '') {
				$filterOptions['price_currencies'][$productPriceCurrency] = $productPriceCurrency;
			}

			$productStatus = (int) ($product->product_status ?? 0);
			$productApiStatus = (int) ($product->product_api_status ?? 0);
			$categoryApiStatus = (int) ($product->category_api_status ?? 0);

			if ($productStatus === 1) {
				$product->product_status_text = 'aktif';
			} elseif ($productStatus === 2) {
				$product->product_status_text = 'silinmiş';
			} else {
				$product->product_status_text = 'pasif';
			}
			$product->product_api_status_text = $productApiStatus === 1 ? 'api-aktif' : 'api-pasif';
			$product->category_api_status_text = $categoryApiStatus === 1 ? 'api-aktif' : 'api-pasif';

			$filterOptions['product_statuses'][(string) $productStatus] = $product->product_status_text;
			$filterOptions['product_api_statuses'][(string) $productApiStatus] = $product->product_api_status_text;
			$filterOptions['category_api_statuses'][(string) $categoryApiStatus] = $product->category_api_status_text;

			if ($productStatus === 1) {
				$productCounts['active']++;
			} else {
				$productCounts['passive']++;
			}

			$typeTokens = [];
			if ($product->product_type_text !== '') {
				$typeTokens[] = $product->product_type_text;
			}
			if ($product->category_type_text !== '' && $product->category_type_text !== $product->product_type_text) {
				$typeTokens[] = $product->category_type_text;
			}
			$expandedTypeTokens = [];
			foreach ($typeTokens as $typeToken) {
				$normalizedTypeToken = strtolower(trim((string) $typeToken));
				if ($normalizedTypeToken === '') {
					continue;
				}

				$expandedTypeTokens[] = $normalizedTypeToken;
				if (isset($typeAliasMap[$normalizedTypeToken])) {
					$expandedTypeTokens = array_merge($expandedTypeTokens, $typeAliasMap[$normalizedTypeToken]);
				}
			}

			if (
				in_array('epin', $expandedTypeTokens, true)
				|| in_array('pin', $expandedTypeTokens, true)
				|| in_array('topup', $expandedTypeTokens, true)
			) {
				$expandedTypeTokens[] = 'epin-topup';
				$expandedTypeTokens[] = 'epin topup';
			}

			$typeTokens = array_values(array_unique($expandedTypeTokens));
			$product->type_search_text = implode(',', $typeTokens);

			$searchTokens = [
				$product->product_id ?? '',
				$product->product_name ?? '',
				$product->product_category_id ?? '',
				$product->category_id ?? '',
				$product->category_title ?? '',
				$product->category_publisher ?? '',
				$product->product_dijipin_id ?? '',
				$product->product_supplier_id ?? '',
				$product->supplier_name,
				$product->product_supplier_code ?? '',
				$product->product_supplier_variant_code ?? '',
				$product->product_provider_id ?? '',
				$product->provider_product_id ?? '',
				$product->provider_product_provider_id ?? '',
				$product->provider_product_category_id ?? '',
				$product->provider_product_category_name ?? '',
				$product->provider_product_product_id ?? '',
				$product->provider_product_product_name ?? '',
				$product->product_cost ?? '',
				$product->product_price ?? '',
				$product->provider_product_product_cost ?? '',
				$productCostCurrency,
				$providerCostCurrency,
				$productPriceCurrency,
				implode(' ', $countries),
				implode(' ', $requires),
				implode(' ', $typeTokens),
				$product->product_status_text,
				$product->product_api_status_text,
				$product->category_api_status_text,
			];

			$product->search_blob = $normalizeSearchText($searchTokens);
		}

		uasort($filterOptions['categories'], static function ($a, $b) {
			return strcasecmp($a['title'], $b['title']);
		});
		uasort($filterOptions['suppliers'], static function ($a, $b) {
			return strcasecmp($a['name'], $b['name']);
		});
		uasort($filterOptions['product_types'], static function ($a, $b) {
			return strcasecmp($a['label'], $b['label']);
		});
		uasort($filterOptions['category_types'], static function ($a, $b) {
			return strcasecmp($a['label'], $b['label']);
		});
		ksort($filterOptions['countries']);
		ksort($filterOptions['cost_currencies']);
		ksort($filterOptions['price_currencies']);
		ksort($filterOptions['product_statuses']);
		ksort($filterOptions['product_api_statuses']);
		ksort($filterOptions['category_api_statuses']);

		// Liste AJAX endpoint'i üzerinden yüklendiği için ilk HTML yanıtında satır taşımıyoruz.
		$data['products'] = [];
		$data['suppliers'] = $suppliers;
		$data['filter_options'] = $filterOptions;
		$data['product_counts'] = $productCounts;
		$this->template->content->view('index', $data);
		$this->template->publish();
	}

	public function ajaxList()
	{
		$draw = (int) $this->input->post('draw');
		$start = max(0, (int) $this->input->post('start'));
		$length = (int) $this->input->post('length');
		$length = $length > 0 && $length <= 500 ? $length : 25;
		$post = $this->input->post();
		$search = trim((string) ($post['search']['value'] ?? ''));

		$buildQuery = function ($withFilters) use ($post, $search) {
			$query = $this->db->select('p.*, c.category_title, c.category_type, c.category_api_status, sub_c.category_title as sub_category_title, sp.seller_products_seller_id as product_supplier_id, u.user_name as supplier_name, pp.provider_product_product_name')
				->from('products p')
				->join('categories c', 'c.category_id = p.product_category_id')
				->join('categories sub_c', 'sub_c.category_id = p.product_sub_category_id', 'left')
				->join('seller_products sp', 'sp.seller_products_product_id = p.product_id', 'left')
				->join('users u', 'u.user_id = sp.seller_products_seller_id', 'left')
				->join('provider_products pp', 'pp.provider_product_id = p.product_provider_id', 'left')
				->where('p.product_status !=', 2);
			if (!$withFilters) {
				return $query;
			}
			if ($search !== '') {
				$query->group_start()->like('p.product_id', $search)->or_like('p.product_name', $search)->or_like('c.category_title', $search)->or_like('sub_c.category_title', $search)->or_like('pp.provider_product_product_name', $search)->or_like('u.user_name', $search)->group_end();
			}
			foreach (['filterCategory' => 'p.product_category_id', 'filterProductType' => 'p.product_type', 'filterSupplier' => 'sp.seller_products_seller_id', 'filterStatus' => 'p.product_status', 'filterProductApi' => 'p.product_api_status', 'filterCategoryApi' => 'c.category_api_status', 'filterCategoryType' => 'c.category_type'] as $key => $field) {
				if (($post[$key] ?? '') !== '' && is_numeric($post[$key])) {
					$query->where($field, (int) $post[$key]);
				}
			}
			if (($post['filterCountry'] ?? '') !== '') {
				$country = strtoupper(trim((string) $post['filterCountry']));
				$query->group_start()->like('p.product_countries', '"' . $country . '"')->or_like('c.category_countries', '"' . $country . '"')->group_end();
			}
			if (($post['filterCurrency'] ?? '') !== '') {
				$currency = strtoupper(trim((string) $post['filterCurrency']));
				$query->group_start()->like('p.product_cost_currency', $currency)->or_like('pp.provider_product_product_cost_currency', $currency)->or_like('p.product_price_currency', $currency)->group_end();
			}
			return $query;
		};
		$total = (int) $buildQuery(false)->count_all_results();
		$filtered = (int) $buildQuery(true)->count_all_results();
		$query = $buildQuery(true);
		$products = $query->order_by('p.product_id', 'DESC')->limit($length, $start)->get()->result();
		$data = [];
		foreach ($products as $product) {
			$active = (int) $product->product_status === 1;
			$featured = (int) ($product->product_is_featured ?? 0) === 1;
			$apiActive = (int) $product->product_api_status === 1;
			$name = html_escape($product->product_name ?: '-');
			$category = html_escape($product->category_title ?: '-');
			$sub = html_escape($product->sub_category_title ?: 'Alt Kategori Yok');
			$data[] = [
				'<span class="text-gray-800 fw-bold">#' . (int) $product->product_id . '</span>',
				'<div class="p-row"><div class="p-symbol">' . (!empty($product->product_image) ? '<img src="' . html_escape(getenv('CDN_URL') . $product->product_image) . '" alt="">' : '<div class="symbol-label bg-light-info text-info fw-bold">' . html_escape(mb_strtoupper(mb_substr($name, 0, 1))) . '</div>') . '</div><div class="p-info"><div class="p-name text-gray-900 fw-bold text-hover-primary"><a href="' . base_url('products/edit/' . (int) $product->product_id) . '">' . $name . '</a> <span class="text-muted fs-7">#' . (int) $product->product_id . '</span></div><div class="p-sub">' . ($product->supplier_name ? '<span class="badge badge-light-info">' . html_escape($product->supplier_name) . '</span>' : '<span class="text-muted">Tedarikçi yok</span>') . '</div></div></div>',
				'<div class="text-gray-800 fw-bold">' . $category . ' <span class="text-muted">#' . (int) $product->product_category_id . '</span></div>',
				'<div class="text-gray-800 fw-bold">' . $sub . '</div>',
				'<div class="status-grid"><div class="status-item"><span class="badge ' . ($active ? 'badge-light-success' : 'badge-light-danger') . ' fw-semibold js-product-status-badge">' . ($active ? 'Aktif' : 'Pasif') . '</span><div class="form-check form-switch form-check-custom form-check-solid m-0"><input class="form-check-input js-product-status-switch" type="checkbox" data-product-id="' . (int) $product->product_id . '"' . ($active ? ' checked' : '') . '></div></div><div class="status-item"><span class="badge ' . ($featured ? 'badge-light-warning' : 'badge-light-secondary') . ' fw-semibold js-product-featured-badge">' . ($featured ? 'Öne Çıkan' : 'Standart') . '</span><div class="form-check form-switch form-check-custom form-check-solid m-0"><input class="form-check-input js-product-featured-switch" type="checkbox" data-product-id="' . (int) $product->product_id . '"' . ($featured ? ' checked' : '') . '></div></div><div class="status-item"><span class="badge ' . ($apiActive ? 'badge-light-success' : 'badge-light-danger') . ' fw-semibold js-product-api-status-badge">' . ($apiActive ? 'API Aktif' : 'API Pasif') . '</span><div class="form-check form-switch form-check-custom form-check-solid m-0"><input class="form-check-input js-product-api-status-switch" type="checkbox" data-product-id="' . (int) $product->product_id . '"' . ($apiActive ? ' checked' : '') . '></div></div><div class="status-sub-info">Kat. API: ' . ((int) $product->category_api_status === 1 ? '<span class="text-success fw-bold">Aktif</span>' : '<span class="text-danger fw-bold">Pasif</span>') . '</div></div>',
				'<div class="actions-wrap"><a href="' . base_url('products/edit/' . (int) $product->product_id) . '" class="btn btn-sm btn-light-primary">Düzenle</a><a href="' . base_url('products/delete/' . (int) $product->product_id) . '" class="btn btn-sm btn-light-danger">Sil</a></div>',
			];
		}
		return json(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
	}

	public function add()
	{
		$data = $this->data;
		$data['categories'] = $this->db
			->from('categories')
			->where('category_is_main', 1)
			->where('category_status', 1)
			->get()
			->result();
		$data['locales'] = $this->db->get('locales')->result();
		$data['types'] = $this->db->get('product_type')->result();
		$this->template->content->view('products/add-product', $data);
		$this->template->publish();
	}

	public function addProduct()
	{
		$post = $this->input->post();

		if (empty($post)) {
			$this->session->set_flashdata('error', 'Ürün ekleme için gerekli veriler eksik.');
			return redirect('products');
		}

		$productName = trim((string) ($post['product_name'] ?? ''));
		$categoryId = (int) ($post['product_category_id'] ?? 0);
		$productType = (int) ($post['product_type']);

		if ($productName === '' || $categoryId <= 0) {
			$this->session->set_flashdata('error', 'Ürün adı ve kategori zorunludur.');
			return redirect('products/addProduct');
		}

		if ($productType === '' || $categoryId <= 0) {
			$this->session->set_flashdata('error', 'Ürün tipi zorunludur.');
			return redirect('products/addProduct');
		}


		// Upload path
		$path = getenv('BASE_PATH') . 'uploads/products/';
		$path2 = 'uploads/products/';
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		// Resimler
		$imageUrl = null;
		$iconUrl = null;

		if (!empty($_FILES['product_image']['name'])) {
			$imageName = upload_image('product_image', $path, $path2, 'img_');
			if (!$imageName) {
				$this->session->set_flashdata('error', 'Ürün resmi yüklenirken hata oluştu.');
				return redirect('products/addProduct');
			}
			$imageUrl = 'uploads/products/' . $imageName;
		}

		if (!empty($_FILES['product_icon']['name'])) {
			$iconName = upload_image('product_icon', $path, $path2, 'icon_');
			if (!$iconName) {
				$this->session->set_flashdata('error', 'Ürün ikonu yüklenirken hata oluştu.');
				return redirect('products/addProduct');
			}
			$iconUrl = 'uploads/products/' . $iconName;
		}

		// requires[] => update()
		$requires = isset($post['requires']) ? (array) $post['requires'] : [];

		$updateArray = [
			'product_name' => $productName,
			'product_category_id' => $categoryId,
			'product_sub_category_id' => !empty($post['product_sub_category_id']) ? (int) $post['product_sub_category_id'] : 0,

			// resimler
			'product_image' => $imageUrl,
			'product_icon' => $iconUrl,
			'product_is_bundle' => isset($post['product_is_bundle']) ? (int) $post['product_is_bundle'] : 0,

			// requires json
			'product_is_require_user_id' => (!empty($requires) && in_array('user_id', $requires)) ? 1 : 0,
			'product_is_require_server_id' => (!empty($requires) && in_array('server_id', $requires)) ? 1 : 0,

			// countries
			'product_countries' => isset($post['locales']) ? json_encode((array) $post['locales']) : json_encode(["TR"]),
			'product_type' => $productType,
			'product_is_featured' => isset($post['product_is_featured']) ? (int) $post['product_is_featured'] : 0,
			'product_badge' => !empty($post['product_badge']) ? mb_substr(trim((string) $post['product_badge']), 0, 50) : null,
			'product_short_desc' => !empty($post['product_short_desc']) ? mb_substr(trim((string) $post['product_short_desc']), 0, 150) : null,
		];

		$log = [
			'log_controller' => 'add-product',
			'log_message' => "Yeni ürün eklendi: {$productName}. Ekleyen: " . $this->session->userdata('user_id'),
			'log_error_code' => 'ADD_PRODUCT',
			'log_created_at' => date('Y-m-d H:i:s'),
		];

		$this->db->trans_start();
		$this->db->insert('products', $updateArray);
		$newId = $this->db->insert_id();
		$this->db->insert('logs', $log);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE || !$newId) {
			$this->session->set_flashdata('error', 'Ürün ekleme sırasında bir hata oluştu.');
			return redirect('products/addProduct');
		}

		$this->session->set_flashdata('success', 'Ürün başarıyla eklendi.');
		return redirect("products/edit/{$newId}");
	}

	//Modal için
	public function productsDetail()
	{
		$pid = $this->uri->segment(3);
		$product = $this->db->select('*')->from('products')
			->join('categories', 'categories.category_id = products.product_category_id')
			->join('seller_products', 'seller_products.seller_products_product_id = products.product_id', 'left')
			->join('product_prices', 'product_prices.product_price_product_id = products.product_id', 'left')
			->join('users', 'users.user_id = seller_products.seller_products_seller_id', 'left')
			->join('provider_products as pp', 'pp.provider_product_id = products.product_provider_id', 'left')
			->where('product_id', $pid)
			->get()->row();
		// pre($product);
		// die;
		//$categories = $this->db->get('categories')->result();
		//$suppliers = $this->db->select('*')->from('users')->where('user_type', 2)->get()->result();
		return json($product, 200);
	}

	public function edit($id)
	{
		$product = $this->db->select('*')->from('products')
			->join('categories', 'categories.category_id = products.product_category_id')
			->join('seller_products', 'seller_products.seller_products_product_id = products.product_id', 'left')
			->join('product_prices', 'product_prices.product_price_product_id = products.product_id', 'left')
			->join('users', 'users.user_id = seller_products.seller_products_seller_id', 'left')
			->join('provider_products as pp', 'pp.provider_product_id = products.product_provider_id', 'left')
			->where('product_id', $id)
			//->where('product_prices.product_price_country', 'tr')
			->get()->row();
		// pre($product);die;

		$productID = $product->product_id;

		$providerProducts = $this->db->select('*')->from('product_provider_alternatives as ppa')
			->where('ppa.product_provider_alternative_product_id', $productID)
			->where('product_provider_alternative_status', 1)
			->join('provider_products as pp', 'pp.provider_product_id = ppa.product_provider_alternative_provider_product_id', 'left')
			->join('users as u', 'u.user_id = pp.provider_product_provider_id', 'left')
			->order_by('ppa.product_provider_alternative_order_by', 'ASC')
			->get()->result();
		// pre($providerProducts);
		// die;

		// locale_lang_code (örneğin: tr, en) bilgilerini al
		$langs = $this->db->select('locale_lang_code')
			->from('locales')
			->get()
			->result();

		$langs = array_map(function ($lang) {
			return $lang->locale_lang_code;
		}, $langs);
		$langs = array_unique($langs);

		// Her dilin JSON verisini ayrıştır
		$productLangData = [];

		// Ülke bilgilerini al
		$allLocales = $this->db->select('*')->from('locales')->get()->result();

		$selectedProductCodes = !empty($product->product_countries)
			? json_decode($product->product_countries)
			: [];

		$selectedLocales = [];
		if (!empty($selectedProductCodes)) {
			$selectedLocales = $this->db->select('*')
				->from('locales')
				->where_in('locale_code', $selectedProductCodes)
				->get()
				->result();
		}
		$subCategories = $this->db->select('*')
			->from('categories')
			->where('category_parent_category_id', $product->product_category_id)
			->where('category_status !=', 2)
			->get()
			->result();

		$data['allProducts'] = $this->db->select('product_id, product_name')->where('product_status !=', 2)->get('products')->result();
		// pre($data['allProducts']);die;
		$data['langs'] = $langs;
		$data['locales'] = $allLocales;
		$data['selectedCountries'] = $selectedProductCodes;
		$data['selectedLocales'] = $selectedLocales;
		$data['productLangData'] = $productLangData;
		$data['provider_product'] = $this->db->select('*')
			->from('provider_products as pp')
			->where('pp.provider_product_id', $product->product_provider_id)
			->get()->row();
		$data['provider_products'] = $providerProducts;
		$data['product'] = $product;
		$data['categories'] = $this->db
			->where('category_is_main', 1)
			->get('categories')
			->result();
		$data['subCategories'] = $subCategories;

		$this->template->content->view('edit-product', $data);
		$this->template->publish();
	}

	public function getProviderProducts()
	{
		$post = $this->input->post();
		if (empty($post['q'])) {
			return json(["status" => "error", "message" => "Search query is empty"], 400);
		}
		// eğer 3 karakterden kısaysa cevap verme
		if (strlen($post['q']) < 3) {
			return json(["status" => "success", "message" => "Search query must be at least 3 characters", "data" => []], 400);
		}
		$q = $post['q'];
		$providerProducts = $this->db->select('*')
			->from('provider_products as pp')
			->like('pp.provider_product_category_name', $q, 'both', false)
			->or_like('pp.provider_product_product_name', $q, 'both', false)
			->or_like('pp.provider_product_product_id', $q, 'both', false)
			->join('users as u', 'u.user_id = pp.provider_product_provider_id', 'left')
			->get()
			->result();
		return json(['status' => 'success', 'data' => $providerProducts]);
	}

	public function changeProvider()
	{
		$pid = $this->uri->segment(4);
		$provid = !empty($this->uri->segment(3)) ? $this->uri->segment(3) : null;
		$updateArray = [
			'product_provider_id' => $provid,
		];
		$this->db->update('products', $updateArray, ['product_id' => $pid]);
		$affectedRows = $this->db->affected_rows();
		if ($affectedRows > 0) {
			$this->session->set_flashdata('success', 'Provider changed successfully.');
		} else {
			$this->session->set_flashdata('error', 'Failed to change provider.');
		}
		return redirect("products/edit/{$pid}");
	}

	public function update()
	{
		$post = $this->input->post();

		$pid = $post['product_id'];

		$path = getenv('BASE_PATH') . 'uploads/products/';
		$path2 = 'uploads/products/';
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		// Eski kayıtları çek
		$old = $this->db->select('product_image, product_icon')
			->from('products')
			->where('product_id', $pid)
			->get()->row();

		$oldImageUrl = $old->product_image ?? null;
		$oldIconUrl = $old->product_icon ?? null;

		$bundleItems = isset($post['bundle_items']) ? (array) $post['bundle_items'] : [];

		$sanitizedBundle = [];

		foreach ($bundleItems as $item) {
			if (!empty($item)) {
				$sanitizedBundle[] = (int) $item;
			}
		}

		// Veritabanına doğrudan eklenecek düz saf JSON formatı
		$product_bundle_items_json = !empty($sanitizedBundle) ? json_encode($sanitizedBundle, JSON_UNESCAPED_UNICODE) : null;

		// DB array'ine: 'product_bundle_items' => $product_bundle_items_json

		// Indexleri sıfırla ve JSON haline getir
		$product_bundle_items_json = !empty($sanitizedBundle) ? json_encode(array_values($sanitizedBundle), JSON_UNESCAPED_UNICODE) : null;

		// DB güncelleme array'ine direkt: 'product_bundle_items' => $product_bundle_items_json

		// Yeni URL’ler (güncellenmezse eskiler kalır)
		$imageUrl = $oldImageUrl;
		$iconUrl = $oldIconUrl;

		// ===== ÜRÜN RESMİ =====
		if (!empty($_FILES['product_image']['name'])) {
			$imageName = upload_image('product_image', $path, $path2, 'img_');

			if (!$imageName) {
				$this->session->set_flashdata('error', 'Ürün resmi yüklenirken hata oluştu.');
				return redirect('products/edit/' . $pid);
			}

			$imageUrl = 'uploads/products/' . $imageName;

			$this->delete_uploaded_product_file($oldImageUrl, $path);
		}

		// ===== ÜRÜN İKONU =====
		if (!empty($_FILES['product_icon']['name'])) {
			$iconName = upload_image('product_icon', $path, $path2, 'icon_');

			if (!$iconName) {
				$this->session->set_flashdata('error', 'Ürün ikonu yüklenirken hata oluştu.');
				return redirect('products/edit/' . $pid);
			}

			$iconUrl = 'uploads/products/' . $iconName;

			$this->delete_uploaded_product_file($oldIconUrl, $path);
		}

		$updateArray = [
			'product_image' => $imageUrl,
			'product_icon' => $iconUrl,
			'product_name' => $post['product_name'],
			'product_category_id' => $post['product_category_id'],
			'product_sub_category_id' => !empty($post['product_sub_category_id']) ? (int) $post['product_sub_category_id'] : 0,
			'product_status' => !empty($post['product_status']) ? $post['product_status'] : null,
			'product_is_require_user_id' => !empty($post['requires']) && in_array('user_id', $post['requires']) ? 1 : 0,
			'product_is_require_server_id' => !empty($post['requires']) && in_array('server_id', $post['requires']) ? 1 : 0,
			'product_countries' => isset($post['locales']) ? json_encode($post['locales']) : json_encode([]),
			'product_type' => $post['product_type'],
			'product_bundle_items' => $product_bundle_items_json,
			'product_is_featured' => isset($post['product_is_featured']) ? (int) $post['product_is_featured'] : 0,
			'product_badge' => !empty($post['product_badge']) ? mb_substr(trim((string) $post['product_badge']), 0, 50) : null,
			'product_short_desc' => !empty($post['product_short_desc']) ? mb_substr(trim((string) $post['product_short_desc']), 0, 150) : null,
		];
		$log = [
			'log_controller' => 'update-product',
			'log_message' => "{$pid} ID'li ürün güncellendi. Güncelleyen: " . $this->session->userdata('admin')['admin_id'],
			'log_error_code' => '543435',
			'log_created_at' => date('Y-m-d H:i:s'),
		];

		$this->db->trans_start();
		$this->db->update('products', $updateArray, ['product_id' => $pid]);
		$this->db->insert('logs', $log);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->session->set_flashdata('error', 'Ürün güncelleme sırasında bir hata oluştu.');
		} else {
			$this->session->set_flashdata('success', 'Ürün başarıyla güncellendi.');
		}
		return redirect("products/edit/{$pid}");
	}

	public function updateSupplier()
	{
		$post = $this->input->post();
		$checkSellerProduct = $this->db->get_where('seller_products', ['seller_products_product_id' => $post['pid']])->row();
		if ($checkSellerProduct) {
			$this->db->update('seller_products', ['seller_products_seller_id' => $post['sp']], ['seller_products_product_id' => $post['pid']]);
		} else {
			$insertData = [
				'seller_products_seller_id' => $post['sp'],
				'seller_products_product_id' => $post['pid'],
			];
			$this->db->insert('seller_products', $insertData);
		}
		return json(["status" => "success", "message" => "Product Supplier updated successfully"], 200);
	}

	public function updateProductPrices()
	{
		$post = $this->input->post();
		$pid = $post['product_price_product_id'];
		$currency = $post['product_price_currency'];
		$country = $post['product_price_country'];

		$updateArray = [
			'product_price_standart_price' => isset($post['product_price_standart_price'])
				? number_format($post['product_price_standart_price'], 4, '.', '')
				: null,
			'product_price_vip_user' => isset($post['product_price_vip_user'])
				? number_format($post['product_price_vip_user'], 4, '.', '')
				: null,
			'product_price_gold_user' => isset($post['product_price_gold_user'])
				? number_format($post['product_price_gold_user'], 4, '.', '')
				: null,
			'product_price_silver_user' => isset($post['product_price_silver_user'])
				? number_format($post['product_price_silver_user'], 4, '.', '')
				: null,
			'product_price_currency' => $currency,
			'product_price_country' => $country,
			'product_price_updated_at' => date('Y-m-d H:i:s'),
		];

		$where = [
			'product_price_product_id' => $pid,
			'product_price_country' => $country,
			'product_price_currency' => $currency
		];

		if ($this->db->get_where('product_prices', $where)->num_rows() > 0) {
			$this->db->update('product_prices', $updateArray, $where);
		} else {
			$updateArray['product_price_product_id'] = $pid;
			$this->db->insert('product_prices', $updateArray);
		}

		return json(["status" => "success", "message" => "Ürün fiyatları güncellendi"], 200);
	}

	public function getSubCategories()
	{
		$parent_id = (int) $this->input->post('parent_id');
		$subCats = $this->db->select('category_id, category_title')
			->from('categories')
			->where('category_parent_category_id', $parent_id)
			->where('category_status !=', 2)
			->order_by('category_title', 'ASC')
			->get()
			->result();

		echo json_encode(['status' => 'success', 'data' => $subCats]);
	}

	public function productPrices()
	{
		$categoryID = $this->uri->segment(3);
		$localeID = $this->uri->segment(4) ? $this->uri->segment(4) : 'tr';
		$data = $this->data;
		$products = $this->db->select('pr.*, pp.*, p.product_id, p.product_name, p.product_image, p.product_status, p.product_api_status, c.category_title, u.user_name, ep.epin_pool_cost, p.product_is_bundle, p.product_bundle_items')
			->from('products as p')
			->join('categories as c', 'c.category_id = p.product_category_id')
			->join('provider_products as pp', 'pp.provider_product_id = p.product_provider_id', 'left')
			->join('product_prices as pr', 'pr.product_price_product_id = p.product_id AND pr.product_price_country = "' . $localeID . '"', 'left')
			->join('users as u', 'u.user_id = pp.provider_product_provider_id', 'left')
			->join('epin_pool as ep', 'ep.epin_pool_category_id = p.product_category_id', 'left')
			->group_start()
			// Durum 1: Ürün epin havuzundaki listede var mı? (Köşeli parantezleri temizleyip bakıyoruz)
			->where("FIND_IN_SET(p.product_id, REPLACE(REPLACE(IFNULL(ep.epin_pool_products, ''), '[', ''), ']', '')) >", 0)
			// Durum 2: Ürün herhangi bir havuzda tanımlı değilse (epin_pool kaydı yoksa veya ürün listesi boşsa)
			->or_where('ep.epin_pool_products IS NULL')
			->or_where('ep.epin_pool_products', '')
			->or_where('ep.epin_pool_products', '[]')
			->group_end()
			->group_start()
			->where('p.product_category_id', $categoryID)
			->or_where('p.product_sub_category_id', $categoryID)
			->group_end()
			->where('p.product_status !=', 2)
			->get()
			->result();

		foreach ($products as $product) {
			// Her ürün için varsayılan değerleri tanımlayalım
			$product->bundle_total_cost = 0;
			$product->bundle_cost_currency = null;

			if ($product->product_is_bundle == 1 && !empty($product->product_bundle_items)) {
				$bundleItems = json_decode($product->product_bundle_items, true);
				if (is_array($bundleItems) && !empty($bundleItems)) {

					$uniqueIds = array_unique($bundleItems);

					// Alt ürünleri ve tam istediğin alan adlarını çekiyoruz
					$dbProducts = $this->db->select('p.product_id, p.product_name, pp.provider_product_product_cost, pp.provider_product_product_cost_currency')
						->from('products as p')
						->join('provider_products as pp', 'pp.provider_product_id = p.product_provider_id', 'left')
						->where_in('p.product_id', $uniqueIds)
						->get()
						->result();

					// ID bazlı map'leme
					$productMap = [];
					foreach ($dbProducts as $dbP) {
						$productMap[$dbP->product_id] = $dbP;
					}

					$finalBundledProducts = [];
					$totalCost = 0;
					$currency = null;

					// Orijinal array dizilimine göre ([15184, 15184]) dönüyoruz
					foreach ($bundleItems as $itemId) {
						if (isset($productMap[$itemId])) {
							$clonedItem = clone $productMap[$itemId];
							$finalBundledProducts[] = $clonedItem;

							// Maliyetleri üst üste ekle (2 adet varsa 2 kez eklenir)
							$totalCost += (float) $clonedItem->provider_product_product_cost;

							// Para birimini al (hepsi aynı tedarikçi/para birimi varsayımıyla en sonuncuyu set eder)
							if (!empty($clonedItem->provider_product_product_cost_currency)) {
								$currency = $clonedItem->provider_product_product_cost_currency;
							}
						}
					}

					$product->bundled_products = $finalBundledProducts;
					$product->bundle_total_cost = $totalCost;
					$product->bundle_cost_currency = $currency; // Para birimini nesneye bastık
				} else {
					$product->bundled_products = [];
				}
			} else {
				$product->bundled_products = [];
			}
		}
		// pre($products);die;

		$data['categories'] = $this->db->select('category_id, category_title')->from('categories')->where('category_status', 1)->get()->result();
		$data['countries'] = $this->db->get('locales')->result();
		$data['category'] = $this->db->get_where('categories', ['category_id' => $categoryID])->row();
		$data['categoryID'] = $categoryID;
		$data['localeID'] = $localeID;
		$data['products'] = $products;
		$localeCurrency = $this->db->get_where('locales', ['locale_lang_code' => $localeID])->row();
		$data['currency'] = $localeCurrency->locale_currency;

		$this->template->content->view('price-update', $data);
		$this->template->publish();
	}

	public function productPricesReport()
	{
		$categoryID = $this->uri->segment(3);
		$localeID = $this->uri->segment(4) ? $this->uri->segment(4) : 'tr';
		$data = $this->data;
		$productsQuery = $this->db->select('pr.*, pp.*, p.product_id, p.product_name, p.product_image, p.product_status, p.product_api_status, p.product_category_id, c.category_title, u.user_name, ep.epin_pool_cost')
			->from('products as p')
			->join('categories as c', 'c.category_id = p.product_category_id')
			->join('provider_products as pp', 'pp.provider_product_id = p.product_provider_id', 'left')
			->join('product_prices as pr', 'pr.product_price_product_id = p.product_id AND pr.product_price_country = "' . $localeID . '"', 'left')
			->join('users as u', 'u.user_id = pp.provider_product_provider_id', 'left')
			->join('epin_pool as ep', 'ep.epin_pool_category_id = p.product_category_id', 'left')
			->group_start()
			// Durum 1: Ürün epin havuzundaki listede var mı? (Köşeli parantezleri temizleyip bakıyoruz)
			->where("FIND_IN_SET(p.product_id, REPLACE(REPLACE(IFNULL(ep.epin_pool_products, ''), '[', ''), ']', '')) >", 0)
			// Durum 2: Ürün herhangi bir havuzda tanımlı değilse (epin_pool kaydı yoksa veya ürün listesi boşsa)
			->or_where('ep.epin_pool_products IS NULL')
			->or_where('ep.epin_pool_products', '')
			->or_where('ep.epin_pool_products', '[]')
			->group_end();
		if (ctype_digit((string) $categoryID) && (int) $categoryID > 0) {
			$productsQuery->group_start()
				->where('p.product_category_id', (int) $categoryID)
				->or_where('p.product_sub_category_id', (int) $categoryID)
				->group_end();
		}
		$products = $productsQuery
			->where('p.product_status !=', 2)
			->get()
			->result();
		// pre($products);die;
		$data['categories'] = $this->db->get_where('categories', ['category_status' => 1])->result();
		$data['countries'] = $this->db->get('locales')->result();
		// pre($data['countries']);die;
		// //die;
		$data['category'] = $this->db->get_where('categories', ['category_id' => $categoryID])->row();
		$data['categoryID'] = $categoryID;
		$data['localeID'] = $localeID;
		$data['products'] = $products;
		$localeCurrency = $this->db->get_where('locales', ['locale_lang_code' => $localeID])->row();
		$data['currency'] = $localeCurrency->locale_currency;
		//$suppliers = $this->db->select('*')->from('users')->where('user_type', 2)->get()->result();
		//$data['suppliers'] = $suppliers;
		// pre($data['products']);
		// die;
		$this->template->content->view('price-update2', $data);
		$this->template->publish();
	}

	public function savePrice()
	{
		$pid = $this->uri->segment(3);
		$post = $this->input->post();
		// get price
		$productPrice = $this->db->get_where(
			'product_prices',
			[
				'product_price_product_id' => $post['productID'],
				'product_price_country' => $post['product_price_country']
			]
		)->row();

		if ($post['is_multiplier'] == 0):
			$insertOrUpdateData = [
				'product_price_currency' => $post['product_price_currency'],
				'product_price_product_id' => $post['productID'],
				'product_price_cost' => $post['cost'],
				'product_price_country' => $post['product_price_country'],
				'product_price_is_multiplier' => $post['is_multiplier'],

				'product_price_standart_price' => $post['standart_price'],
				'product_price_silver_user' => $post['silver_user'],
				'product_price_gold_user' => $post['gold_user'],
				'product_price_vip_user' => $post['vip_user'],
				'product_price_playsel' => $post['playsel'],
				'product_price_payment' => $post['payment'],

				'product_price_list_price' => $post['list_price'],
				'product_price_created_at' => date('Y-m-d H:i:s'),
			];
		else: // eğer çarpanlı ise
			$insertOrUpdateData = [
				'product_price_currency' => $post['product_price_currency'],
				'product_price_product_id' => $post['productID'],
				'product_price_cost' => $post['cost'],
				'product_price_country' => $post['product_price_country'],
				'product_price_is_multiplier' => $post['is_multiplier'],

				'product_price_standart_price_multiplier' => $post['standart_price'],
				'product_price_silver_user_multiplier' => $post['silver_user'],
				'product_price_gold_user_multiplier' => $post['gold_user'],
				'product_price_vip_user_multiplier' => $post['vip_user'],
				'product_price_playsel_multiplier' => $post['playsel'],
				'product_price_payment_multiplier' => $post['payment'],

				'product_price_list_price' => $post['list_price'],
				'product_price_created_at' => date('Y-m-d H:i:s'),
			];
		endif;
		//$this->db->insert('product_prices', $insertData);
		if (!$productPrice) {
			$this->db->insert('product_prices', $insertOrUpdateData);
			return json(["status" => "success", "message" => "Fiyat eklendi."], 200);
		} elseif ($productPrice) {
			$this->db->update(
				'product_prices',
				$insertOrUpdateData,
				[
					'product_price_id' => $productPrice->product_price_id
				]
			);
			return json(["status" => "success", "message" => "Fiyat güncellendi."], 200);
		} else {
			return json(["status" => "error", "message" => "Fiyat eklenemedi/güncellenemedi."], 200);
		}
	}

	public function updateProductStatus()
	{
		$post = $this->input->post();
		$pid = $this->uri->segment(3);
		$status = $post['status'];
		$apiStatus = $post['apiStatus'] ?? null;
		$this->db->update('products', ['product_status' => $status, 'product_api_status' => $apiStatus], ['product_id' => $pid]);
		return json(["status" => "success", "message" => "Product status updated successfully"], 200);
	}

	private function delete_uploaded_product_file($fileUrlOrName, $basePath)
	{
		if (empty($fileUrlOrName))
			return;

		// URL geldiyse sadece dosya adını al
		$filename = basename(parse_url($fileUrlOrName, PHP_URL_PATH));

		if (!$filename)
			return;

		// Güvenlik: path traversal engelle
		$filename = str_replace(['..', '/', '\\'], '', $filename);

		$fullPath = rtrim($basePath, '/') . '/' . $filename;

		if (is_file($fullPath)) {
			@unlink($fullPath);
		}
	}

	public function changeStatus()
	{
		$post = $this->input->post();

		$productId = (int) ($post['product_id'] ?? 0);
		$status = isset($post['product_status']) ? (int) $post['product_status'] : null;

		if ($productId <= 0 || ($status !== 0 && $status !== 1)) {
			return json(['status' => 'error', 'message' => 'Geçersiz istek'], 400);
		}

		$exists = $this->db->select('product_id')
			->from('products')
			->where('product_id', $productId)
			->get()->row();

		if (!$exists) {
			return json(['status' => 'error', 'message' => 'Ürün bulunamadı'], 404);
		}

		$this->db->trans_start();
		$this->db->update('products', [
			'product_status' => $status
		], ['product_id' => $productId]);

		$log = [
			'log_controller' => 'change-product-status',
			'log_message' => "{$productId} ID'li ürün durumu " . ($status ? 'aktif' : 'pasif') . " yapıldı. Güncelleyen: " . $this->session->userdata('user_id'),
			'log_error_code' => 'PRO_STATUS',
			'log_created_at' => date('Y-m-d H:i:s'),
		];
		$this->db->insert('logs', $log);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return json(['status' => 'error', 'message' => 'Güncelleme sırasında hata oluştu'], 500);
		}
		return json([
			'status' => 'success',
			'message' => 'Ürün durumu güncellendi',
			'data' => [
				'product_id' => $productId,
				'product_status' => $status
			]
		], 200);
	}

	public function changeApiStatus()
	{
		$post = $this->input->post();

		$productId = (int) ($post['product_id'] ?? 0);
		$apiStatus = isset($post['product_api_status']) ? (int) $post['product_api_status'] : null;

		if ($productId <= 0 || ($apiStatus !== 0 && $apiStatus !== 1)) {
			return json(['status' => 'error', 'message' => 'Geçersiz istek'], 400);
		}

		$exists = $this->db->select('product_id')
			->from('products')
			->where('product_id', $productId)
			->get()->row();

		if (!$exists) {
			return json(['status' => 'error', 'message' => 'Ürün bulunamadı'], 404);
		}

		$this->db->trans_start();
		$this->db->update('products', [
			'product_api_status' => $apiStatus
		], ['product_id' => $productId]);

		$log = [
			'log_controller' => 'change-product-api-status',
			'log_message' => "{$productId} ID'li ürün API durumu " . ($apiStatus ? 'aktif' : 'pasif') . " yapıldı. Güncelleyen: " . $this->session->userdata('user_id'),
			'log_error_code' => 'PRO_API_STATUS',
			'log_created_at' => date('Y-m-d H:i:s'),
		];
		$this->db->insert('logs', $log);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return json(['status' => 'error', 'message' => 'Güncelleme sırasında hata oluştu'], 500);
		}

		return json([
			'status' => 'success',
			'message' => 'Ürün API durumu güncellendi',
			'data' => [
				'product_id' => $productId,
				'product_api_status' => $apiStatus
			]
		], 200);
	}

	public function changeFeaturedStatus()
	{
		$post = $this->input->post();

		$productId = (int) ($post['product_id'] ?? 0);
		$featuredStatus = isset($post['product_is_featured']) ? (int) $post['product_is_featured'] : null;

		if ($productId <= 0 || ($featuredStatus !== 0 && $featuredStatus !== 1)) {
			return json(['status' => 'error', 'message' => 'Geçersiz istek'], 400);
		}

		$exists = $this->db->select('product_id')
			->from('products')
			->where('product_id', $productId)
			->get()->row();

		if (!$exists) {
			return json(['status' => 'error', 'message' => 'Ürün bulunamadı'], 404);
		}

		$this->db->trans_start();
		$this->db->update('products', [
			'product_is_featured' => $featuredStatus
		], ['product_id' => $productId]);

		$log = [
			'log_controller' => 'change-product-featured',
			'log_message' => "{$productId} ID'li ürün öne çıkarılma durumu " . ($featuredStatus ? 'öne çıkan' : 'standart') . " yapıldı. Güncelleyen: " . $this->session->userdata('user_id'),
			'log_error_code' => 'PRO_FEATURED',
			'log_created_at' => date('Y-m-d H:i:s'),
		];
		$this->db->insert('logs', $log);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return json(['status' => 'error', 'message' => 'Güncelleme sırasında hata oluştu'], 500);
		}
		return json([
			'status' => 'success',
			'message' => 'Öne çıkarılma durumu güncellendi',
			'data' => [
				'product_id' => $productId,
				'product_is_featured' => $featuredStatus
			]
		], 200);
	}

	public function updateProviderOrder()
	{
		$data = json_decode(file_get_contents("php://input"), true);
		foreach ($data as $row) {
			$this->db->where(
				'product_provider_alternative_provider_product_id',
				$row['id'],
				'product_provider_alternative_product_id',
				$this->uri->segment(3)
			)
				->update('product_provider_alternatives', [
					'product_provider_alternative_order_by' => $row['sort']
				]);
		}

		echo json_encode(['status' => true]);
	}

	public function delete()
	{
		$pid = $this->uri->segment(3);
		$this->db->update('products', ['product_status' => 2], ['product_id' => $pid]);
		$this->session->set_flashdata('alert', ['status' => 'success', 'message' => 'Ürün başarıyla silindi.']);
		return redirect('products');
	}

	public function productSorts()
	{
		$data = $this->data;

		// Tüm aktif kategorileri çek (ana + alt)
		$categories = $this->db->select('category_id, category_title, category_is_main, category_parent_category_id')
			->from('categories')
			->where('category_status !=', 2)
			->order_by('category_title', 'ASC')
			->get()
			->result();

		$data['categories'] = $categories;
		$this->template->content->view('sort-product', $data);
		$this->template->publish();
	}

	public function getProductsByCategory()
	{
		$categoryId = (int) $this->input->post('category_id');

		if ($categoryId <= 0) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Geçersiz kategori ID']));
		}

		$products = $this->db->select('product_id, product_name, product_image, product_sort_id, product_status')
			->from('products')
			->where('product_category_id', $categoryId)
			->where('product_status !=', 2)
			->order_by('product_sort_id', 'ASC')
			->order_by('product_id', 'ASC')
			->get()
			->result();

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(['status' => 'success', 'data' => $products]));
	}

	public function updateProductSort()
	{
		$post = $this->input->post();
		$sortData = $post['sort_data'] ?? null;

		if (empty($sortData) || !is_array($sortData)) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Sıralama verisi eksik']));
		}

		$this->db->trans_start();

		foreach ($sortData as $item) {
			$productId = (int) ($item['product_id'] ?? 0);
			$sortOrder = (int) ($item['sort_order'] ?? 0);

			if ($productId > 0) {
				$this->db->where('product_id', $productId)
					->update('products', [
						'product_sort_id' => $sortOrder,
					]);
			}
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Sıralama güncellenirken hata oluştu']));
		}

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(['status' => 'success', 'message' => 'Ürün sıralaması başarıyla güncellendi']));
	}

	public function reviews()
	{
		$data = $this->data;
		
		$this->db->select('r.*, u.user_name, u.user_email, p.product_name');
		$this->db->from('reviews r');
		$this->db->join('users u', 'u.user_id = r.review_user_id', 'left');
		$this->db->join('products p', 'p.product_id = r.review_product_id', 'left');
		$this->db->order_by('r.review_created_at', 'DESC');
		
		$data['reviews'] = $this->db->get()->result();

		$this->template->content->view('products/reviews', $data);
		$this->template->publish();
	}

	public function updateReviewStatus()
	{
		$review_id = $this->input->post('review_id');
		$status = $this->input->post('status');

		if (!in_array($status, [1, 2, 3])) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Geçersiz durum.']));
		}

		// If status is 3, delete the review
		if ($status == 3) {
			$this->db->where('review_id', $review_id);
			$this->db->delete('reviews');
			$message = 'Yorum başarıyla silindi.';
		} else {
			$this->db->where('review_id', $review_id);
			$this->db->update('reviews', ['review_status' => $status]);
			$message = $status == 1 ? 'Yorum başarıyla onaylandı.' : 'Yorum reddedildi.';
		}

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(['status' => 'success', 'message' => $message]));
	}
}
