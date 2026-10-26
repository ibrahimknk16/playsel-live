<?php defined('BASEPATH') or exit('No direct script access allowed');

class Categoriess extends MY_Controller
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
		$this->data['currentLocale'] = $this->db
			->where('locale_lang_code', $lang)
			->where('locale_code', $country)
			->where('locale_status', 1)
			->get('locales')
			->row();
		if (empty($this->data['currentLocale'])) {
			show_404();
		}
		$this->data['user'] = $this->session->userdata('user');
		$this->data['lang'] = $lang;
		$this->data['country'] = $country;
		$this->data['title'] = 'Playsel ';
	}

	public function index($type)
	{
		$pageTitle = tr_strtoupper($type);
		$type = $type == 'topup' ? 2 : ($type == 'voucher' ? 3 : 2);

		$data = $this->data;
		$country = $data['country'];

		$mainCategories = $this->db
			->select('category_id, category_title, category_slug, category_publisher, category_type, category_thumbnail, category_image')
			->from('categories')
			->where_not_in('category_id', [4])
			->where('category_status', 1)
			->group_start()
			->where('category_parent_category_id', 0)
			->or_where('category_parent_category_id', null)
			->or_where('category_parent_category_id', '')
			->group_end()
			->where("JSON_CONTAINS(category_countries, '\"$country\"')", null, false)
			->get()->result();

		if (!empty($mainCategories)) {
			$mainCategoryIds = array_column($mainCategories, 'category_id');

			$allSubCategories = $this->db
				->select('category_id, category_parent_category_id, category_title, category_slug')
				->from('categories')
				->where('category_status', 1)
				->where_in('category_parent_category_id', $mainCategoryIds)
				->get()->result();

			foreach ($mainCategories as $mainCat) {
				$mainCat->sub_categories = [];
				foreach ($allSubCategories as $subCat) {
					if ($subCat->category_parent_category_id == $mainCat->category_id) {
						$mainCat->sub_categories[] = $subCat;
					}
				}
			}
		} else {
			$mainCategories = [];
		}

		$data['categories'] = $mainCategories;
		$data['pageTitle'] = $pageTitle;
		$data['title'] = ($data['lang'] ?? '') === 'tr' ? 'E-Pin, Top-Up ve Hediye Kartı Kategorileri' : $data['title'] . $this->lang->line('category_title');
		$data['description'] = $this->lang->line('category_description');
		$this->template->content->view('index', $data);
		$this->template->publish();
	}

	public function categoryDetail($slug)
	{
		$data = $this->data;
		$country = $data['country'];

		// 1. Gelen slug'a göre mevcut kategoriyi bul (Ana veya Alt kategori olabilir)
		$currentCategory = $this->db->get_where('categories', ['category_slug' => $slug, 'category_status' => 1])->row();
		if (empty($currentCategory)) {
			show_404();
		}

		$mainCategory = null;
		$activeSubCatId = null;

		// Eğer bu kategorinin bir üst kategorisi varsa (Yani URL'deki slug alt kategori ise)
		if (!empty($currentCategory->category_parent_category_id) && $currentCategory->category_parent_category_id > 0) {
			$activeSubCatId = $currentCategory->category_id;
			$mainCategory = $this->db->get_where('categories', ['category_id' => $currentCategory->category_parent_category_id, 'category_status' => 1])->row();
			if (empty($mainCategory))
				show_404();

			// Kardeş alt kategorileri menüde göstermek için çek (sort_id'ye göre sıralı)
			$subCategories = $this->db->where('category_parent_category_id', $mainCategory->category_id)
				->where('category_status', 1)
				->order_by('category_sort_id', 'ASC')
				->get('categories')
				->result();
		} else {
			// Ana kategorideyiz
			$mainCategory = $currentCategory;

			// Alt kategorileri sort_id'ye göre küçükten büyüğe (ASC) sıralayarak çek
			$subCategories = $this->db->where('category_parent_category_id', $mainCategory->category_id)
				->where('category_status', 1)
				->order_by('category_sort_id', 'ASC')
				->get('categories')
				->result();

			if (!empty($subCategories)) {
				// ASC sıralandığı için ilk sıradaki eleman en küçük category_sort_id'ye sahiptir
				$activeSubCatId = $subCategories[0]->category_id;

				// SEO için ana kategoriye girildiğinde doğrudan default alt kategori URL'ine yönlendiriyoruz
				redirect(topupcenter() . $this->data['uri'] . '/' . $subCategories[0]->category_slug, 'location', 301);
			} else {
				// Alt kategori hiç tanımlanmamışsa mecburen ana kategoriyi baz al
				$activeSubCatId = $mainCategory->category_id;
			}
		}

		$data['category'] = $mainCategory;
		$data['activeSubCatId'] = $activeSubCatId;
		$data['subCategories'] = $subCategories;

		// 3. Ürünleri Çek
		$data['categoryProducts'] = [];
		if (!empty($mainCategory)) {
			$products = $this->db->select('products.*, sub_c.category_title as sub_category_title, pr.product_price_standart_price')
				->from('products')
				->join('categories as sub_c', 'sub_c.category_id = products.product_sub_category_id', 'left')
				->join('product_prices as pr', 'pr.product_price_product_id = products.product_id AND pr.product_price_country = ' . $this->db->escape($data['currentLocale']->locale_code), 'left')
				->group_start();
			if (!empty($subCategories)) {
				$this->db
					->where('products.product_sub_category_id', (int) $activeSubCatId)
					->or_where('products.product_category_id', (int) $activeSubCatId);
			} else {
				$this->db
					->where('products.product_category_id', (int) $mainCategory->category_id)
					->where('products.product_sub_category_id', 0);
			}
			$products = $this->db
				->group_end()
				->where('products.product_status', 1)
				->where("JSON_CONTAINS(products.product_countries, " . $this->db->escape('"' . $country . '"') . ")", null, false)
				->order_by('products.product_sort_id', 'ASC')
				->get()
				->result();

			// Fiyat Formatlamaları
			$products = prodctPricesChangeLocationCurrency2($products, 'TRY', !empty($data['user']) ? $data['user']['user_tier'] : 1, $data['currentLocale']->locale_code);

			$groupedProducts = [];
			foreach ($products as $prod) {
				$subId = $prod->product_sub_category_id
					? $prod->product_sub_category_id
					: (!empty($subCategories) && (int) $prod->product_category_id === (int) $activeSubCatId ? $activeSubCatId : 0);
				$groupedProducts[$subId][] = $prod;
			}
			$data['groupedProducts'] = $groupedProducts;
			$data['seoProducts'] = !empty($subCategories)
				? ($groupedProducts[$activeSubCatId] ?? [])
				: ($groupedProducts[0] ?? []);
			// $data['pageRobots'] = !empty($data['seoProducts']) ? 'index,follow' : 'noindex,follow';
			$data['pageRobots'] = 'index,follow';
		}

		// 4. Sunucuları ve Gereksinimleri Çek
		$servers = $this->db->select('*')->from('servers')
			->where('servers_category_id', $mainCategory->category_id)
			->get()->row();
		$data['category']->servers = !empty($servers->servers_serversJson) ? json_decode($servers->servers_serversJson) : [];
		$data['category']->category_requires = !empty($currentCategory->category_requires) ? json_decode($currentCategory->category_requires) : [];

		// 5. SEO Ayarları (Sayfanın başlığı URL'deki aktif kategoriye göre belirlenir)
		$categorySeo = $this->db->select('category_lang_json')
			->from('category_lang')
			->where('category_lang_category_id', $currentCategory->category_id)
			->where('category_lang_lang', $data['lang'])
			->get()
			->row();

		if (!empty($categorySeo)) {
			$categorySeo = json_decode($categorySeo->category_lang_json, true);
			$data['title'] = !empty($categorySeo['meta_title']) ? $categorySeo['meta_title'] : $currentCategory->category_title;
			$data['description'] = !empty($categorySeo['meta_desc']) ? $categorySeo['meta_desc'] : $this->lang->line('category_description');
			$data['meta_keywords'] = !empty($categorySeo['meta_keywords']) ? $categorySeo['meta_keywords'] : '';
			$data['h1_tag'] = !empty($categorySeo['h1_tag']) ? $categorySeo['h1_tag'] : $currentCategory->category_title;

			$data['category_description'] = !empty($categorySeo['description']) ? $categorySeo['description'] : '';
			$data['how_to_install'] = !empty($categorySeo['how_to_install']) ? $categorySeo['how_to_install'] : '';
		} else {
			$data['title'] = $currentCategory->category_title;
			$data['description'] = $this->lang->line('category_description');
			$data['meta_keywords'] = '';
			$data['h1_tag'] = $currentCategory->category_title;

			$data['category_description'] = '';
			$data['how_to_install'] = '';
		}
		$data['uniqueUserIds'] = !empty($uniqueUserIds) ? $uniqueUserIds : [];

		$data['reviews'] = $this->db->select('r.*, u.user_nickname, u.user_name, p.product_name, p.product_icon, s.category_title as sub_category_title')
			->from('reviews r')
			->join('users u', 'r.review_user_id = u.user_id', 'left')
			->join('products p', 'p.product_id = r.review_product_id', 'left')
			->join('categories s', 's.category_id = p.product_sub_category_id', 'left')
			->where('r.review_category_id', $mainCategory->category_id)
			->where('r.review_status', 1)
			->order_by('r.review_created_at', 'DESC')
			->get()
			->result();

		$data['category_faqs'] = $this->db->select('*')
			->from('category_faqs')
			->where('faq_category_id', $mainCategory->category_id)
			->where('faq_status', 1)
			->order_by('faq_order', 'ASC')
			->get()
			->result();

		$this->template->content->view('category-detail', $data);
		$this->template->publish();
	}


	public function deneme()
	{
		die;
		// <?= nFormat(currencyCalculatorTo('USD', $costTL) * 1.015)
		$data = $this->data;
		//pre($data);die;
		$products = $this->db->get('products')->result();
		foreach ($data['locales'] as $locale) {
			pre($locale);
			foreach ($products as $product) {
				$productCost = currencyCalculator($product->product_cost_currency, $product->product_cost) * 1.015;
				echo $product->product_name;
				echo '<br>';
				echo 'Maliyet: ' . nFormat(currencyCalculatorTo($locale->locale_currency, $productCost)) . ' %1.5 Kur Farkı';
				echo '<br>';
				echo 'Normal Fiyat: ' . nFormat(currencyCalculatorTo($locale->locale_currency, $productCost) * 1.08) . ' TL - +%8 Kar';
				echo '<br>';
				echo 'Silver Fiyat: ' . nFormat(currencyCalculatorTo($locale->locale_currency, $productCost) * 1.06) . ' TL - +%6 Kar';
				echo '<br>';
				echo 'Gold Fiyat: ' . nFormat(currencyCalculatorTo($locale->locale_currency, $productCost) * 1.04) . ' TL - +%4 Kar';
				echo '<br>';
				echo 'VIP Fiyat: ' . nFormat(currencyCalculatorTo($locale->locale_currency, $productCost) * 1.02) . ' TL - +%2 Kar';
				echo '<hr>';
				pre($product);
				//die;
				$productPricesInsertArray = [
					'product_price_product_id' => $product->product_id,
					'product_price_currency' => $locale->locale_currency,
					'product_price_country' => $locale->locale_lang_code,
					'product_price_standart_price' => number_format(currencyCalculatorTo($locale->locale_currency, $productCost) * 1.06, 2, '.', ''),
					'product_price_silver_user' => number_format(currencyCalculatorTo($locale->locale_currency, $productCost) * 1.05, 2, '.', ''),
					'product_price_gold_user' => number_format(currencyCalculatorTo($locale->locale_currency, $productCost) * 1.04, 2, '.', ''),
					'product_price_vip_user' => number_format(currencyCalculatorTo($locale->locale_currency, $productCost) * 1.02, 2, '.', ''),
				];
				$this->db->insert('product_prices', $productPricesInsertArray);
			}
		}
	}

	public function checkUser()
	{
		// Bu istek sadece ana domainden gelsin
		$mainDomain = strtolower((string) parse_url(topupcenter(), PHP_URL_HOST));
		$currentDomain = strtolower((string) parse_url(base_url(), PHP_URL_HOST));
		$source = (string) $this->input->server('HTTP_ORIGIN');
		if ($source === '') {
			$source = (string) $this->input->server('HTTP_REFERER');
		}
		$referrer = strtolower((string) parse_url($source, PHP_URL_HOST));
		if ($referrer === '' || ($mainDomain !== $referrer && $currentDomain !== $referrer)) {
			return json(['status' => 'error', 'message' => 'Unauthorized request'], 403);
		}
		$userId = trim((string) $this->input->post('user_id'));
		$serverId = trim((string) $this->input->post('server_id'));

		if ($userId === '' || $serverId === '') {
			return json(['status' => 'error', 'message' => 'Lütfen Oyuncu ID ve Sunucu ID giriniz.'], 400);
		}
		if (!preg_match('/^\d{4,20}$/', $userId) || !preg_match('/^\d{1,10}$/', $serverId)) {
			return json(['status' => 'error', 'message' => 'Oyuncu ID ve Sunucu ID yalnızca rakamlardan oluşmalıdır.'], 422);
		}

		$this->load->library('Dijipin');
		$dijipinAPI = new Dijipin();
		$request = $dijipinAPI->mlbbUserCheck($userId, $serverId);

		if ($request === false) {
			return json([
				'status' => 'error',
				'message' => 'Sorgulama servisine şu anda ulaşılamıyor. Lütfen bilgilerinizi kontrol edip tekrar deneyin.'
			], 500);
		}

		$userData = $request['data']['user_data'] ?? [];
		if (!empty($request['status']) && !empty($userData['user_name'])) {
			$safeName = trim(strip_tags((string) $userData['user_name']));
			$safeName = mb_substr($safeName, 0, 64);
			$safeCountry = preg_match('/^[A-Za-z]{2}$/', (string) ($userData['register_country'] ?? '')) ? (string) $userData['register_country'] : '';
			return json([
				'status' => 'success',
				'name' => $safeName,
				'user_name' => $safeName,
				'register_country' => $safeCountry,
				'data' => ['user_name' => $safeName, 'register_country' => $safeCountry]
			]);
		}

		return json([
			'status' => 'error',
			'message' => 'Hesap bulunamadı veya ID/Sunucu bilgileri hatalı.'
		], 422);
	}
}
