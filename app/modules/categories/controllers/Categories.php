<?php defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends MY_Controller
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
		$this->data['title'] = 'Topup Center ';
	}

	public function index($type)
	{
		$pageTitle = tr_strtoupper($type);
		$type = $type == 'topup' ? 2 : ($type == 'voucher' ? 3 : 2);
		$data = $this->data;
		$country = $data['country'];

		$data['categories'] = $this->db
			->select('category_title, category_slug,category_publisher, category_thumbnail, category_image')
			->where_not_in('category_id', [4])->from('categories')
			->where('category_topupcenter_status', 1)
			->where('category_type', $type)
			->where("JSON_CONTAINS(category_countries, '\"$country\"')", null, false)
			->get()->result();
		if (empty($data['categories'])) {
			$data['categories'] = [];
		}
		$data['pageTitle'] = $pageTitle;
		$data['title'] = $data['title'] . $this->lang->line('category_title');
		$data['description'] = $this->lang->line('category_description');
		$this->template->content->view('index', $data);
		$this->template->publish();
	}

public function categoryDetail($slug)
    {
        $data = $this->data;
        $country = $data['country'];
        // 1. İstenen Kategoriyi Bul (Genelde Ana Kategori olacaktır)
        $data['category'] = $this->db->get_where('categories', ['category_slug' => $slug, 'category_status' => 1])->row();
        if (empty($data['category'])) {
            show_404();
        }
        // 2. Bu kategoriye bağlı Alt Kategorileri Çek (Eğer kendisi alt kategoriyse boş döner)
        $subCategories = $this->db->get_where('categories', [
            'category_main_cat_id' => $data['category']->category_id,
            'category_status' => 1
        ])->result();
        
        $data['subCategories'] = $subCategories;
        // Ürünleri Çekeceğimiz Kategori ID'lerini belirle
        // Eğer alt kategori varsa, ürünleri alt kategorilerden çekeceğiz.
        // Yoksa doğrudan mevcut kategoriden çekeceğiz.

        $categoryIdsToFetch = [];
        if (!empty($subCategories)) {
            $categoryIdsToFetch = array_column($subCategories, 'category_id');


        } else {
            $categoryIdsToFetch[] = $data['category']->category_id;
        }
        // 3. Ürünleri Çek
        $data['categoryProducts'] = [];
        if (!empty($categoryIdsToFetch)) {
            $data['categoryProducts'] = $this->db->select('*')
                ->from('products')
                ->where_in('product_category_id', $categoryIdsToFetch)
                ->where('product_status', 1)
                ->where("JSON_CONTAINS(product_countries, '\"$country\"')", null, false)
                ->get()
                ->result();

            // Fiyat Formatlamaları
            $data['categoryProducts'] = prodctPricesChangeLocationCurrency2($data['categoryProducts'], 'TRY', !empty($data['user']) ? $data['user']['user_tier'] : 1, 'TR');
        }
        // 4. Sunucuları ve Gereksinimleri Çek
        $servers = $this->db->select('*')->from('servers')
            ->where('servers_category_id', $data['category']->category_id)
            ->get()->row();
        $data['category']->servers = !empty($servers->servers_serversJson) ? json_decode($servers->servers_serversJson) : [];
        $data['category']->category_requires = !empty($data['category']->category_requires) ? json_decode($data['category']->category_requires) : [];
        
        // 5. SEO Ayarları
        $categorySeo = $this->db->select('category_lang_json')
            ->from('category_lang')
            ->where('category_lang_category_id', $data['category']->category_id)
            ->where('category_lang_lang', $data['lang'])
            ->get()
            ->row();
            
        if (!empty($categorySeo)) {
            $categorySeo = json_decode($categorySeo->category_lang_json, true);
            $data['title'] = !empty($categorySeo['meta_title']) ? $categorySeo['meta_title'] : $data['category']->category_title;
            $data['description'] = !empty($categorySeo['meta_desc']) ? $categorySeo['meta_desc'] : $this->lang->line('category_description');
            $data['meta_keywords'] = !empty($categorySeo['meta_keywords']) ? $categorySeo['meta_keywords'] : '';
            $data['h1_tag'] = !empty($categorySeo['h1_tag']) ? $categorySeo['h1_tag'] : $data['category']->category_title;
            $data['category_description'] = !empty($categorySeo['description']) ? $categorySeo['description'] : $data['category']->category_description;
            $data['how_to_install'] = !empty($categorySeo['how_to_install']) ? $categorySeo['how_to_install'] : '';
        } else {
            $data['title'] = $data['category']->category_title;
            $data['description'] = $this->lang->line('category_description');
            $data['meta_keywords'] = '';
            $data['h1_tag'] = $data['category']->category_title;
            $data['category_description'] = '';
            $data['how_to_install'] = '';
        }

        $data['uniqueUserIds'] = !empty($uniqueUserIds) ? $uniqueUserIds : [];
        
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
		// bu istek sadece ana domainden gelsin 
		$mainDomain = parse_url(topupcenter(), PHP_URL_HOST);
		$referrer = parse_url($this->input->server('HTTP_REFERER'), PHP_URL_HOST);
		if ($mainDomain !== $referrer) {
			return json(['error' => 'Unauthorized request'], 403);
		}

		$post = $this->input->post();
		$config = [
			'default_country'  => 'TR',
			'default_language' => 'TR',
		];
		$this->load->library('Moontoon', $config);
		$moonton = new Moontoon($config);
		$check = $moonton->gameUser([
			'game_user_key'   => $post['user_id'],
			'game_server_key' => $post['server_id'],
		]);
		//pre($check);die;
		return json($check['response']['data']);
	}
}
