<?php defined('BASEPATH') or exit('No direct script access allowed');

class Home extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		if ($_SERVER['SERVER_NAME'] == 'api.dijipin.com') die;
		parent::__construct();
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
		$this->data['user'] = $this->session->userdata('user');
		$this->data['lang'] = $lang;
		$this->data['country'] = $country;
		$this->data['title'] = site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel') . ' | ';
	}

	public function index()
	{
		$data = $this->data;
		$country = $data['country'];
		// Fetch sliders for current language, fallback to default language ('en') if none found
		$sliderCategories = [1, 2, 5, 13]; // Example category IDs for sliders
		$rawSliders = $this->db->select('*')
			->from('sliders')
			->where('slider_language', $data['lang'])
			->where('slider_status', 1)
			->where_in('slider_category_id', $sliderCategories)
			->order_by('slider_category_id', 'ASC') // PHP tarafında düzenli işlemek için kategoriye göre sıralıyoruz
			->order_by('slider_id', 'DESC')         // Kategori içindeki sıralama
			->get()
			->result();

		// 2. Çekilen veriyi PHP tarafında kategori ID'sine göre grupluyoruz.
		$sliders = [];

		// Kategorileri boş array olarak initialize edebilirsin (eğer ilgili kategoride slider yoksa hata almamak için)
		foreach ($sliderCategories as $categoryId) {
			$sliders[$categoryId] = [];
		}

		// Veriyi ilgili kategori anahtarına atıyoruz
		if (!empty($rawSliders)) {
			foreach ($rawSliders as $slider) {
				$sliders[$slider->slider_category_id][] = $slider;
			}
		}
		pre($sliders); // Veriyi kontrol etmek için ekleyebilirsin
		die;
		$data['sliders'] = $sliders;
		$data['categories'] = $this->db
			->select('category_title, category_slug,category_publisher, category_thumbnail, category_image')
			->from('categories')
			// ->where('category_topupcenter_status', 1)
			->where('category_status', 1)
			->where("JSON_CONTAINS(category_countries, '\"$country\"')", null, false)
			->get()->result();
		$data['title'] = $data['title'] . $this->lang->line('home_title');
		$data['description'] = $this->lang->line('home_description');
		// pre($data["categories"]);die;
		$this->template->content->view('index', $data);
		$this->template->publish();
	}

	public function error404()
	{
		$data = $this->data;
		$this->output->set_status_header(404);
		$data['title'] = $data['title'] . '404';
		$data['description'] = '404';
		$this->template->content->view('error404', $data);
		$this->template->publish();
	}

	public function staticPages()
	{
		$data = $this->data;
		$lang = $this->uri->segment(1);
		$uriSegment = $this->uri->segment(2);
		if (empty($lang) || empty($uriSegment)) {
			show_404();
		}
		$data['staticPage'] = $this->db->get_where('static_pages', ['static_page_slug' => $uriSegment, 'static_page_lang' => $data['lang']])->row();
		$seoTitles = [
			'about-us' => 'Hakkımızda',
			'user-agreement' => 'Kullanıcı Sözleşmesi',
			'terms-conditions' => 'Kullanım Şartları',
			'cookie-policy' => 'Çerez Politikası',
			'privacy-policy' => 'Gizlilik Politikası',
			'refund-policy' => 'İptal ve İade Politikası',
			'faq' => 'Sıkça Sorulan Sorular',
		];
		$pageTitle = !empty($data['staticPage']->static_page_title)
			? trim(strip_tags($data['staticPage']->static_page_title))
			: ($seoTitles[$uriSegment] ?? ucwords(str_replace('-', ' ', $uriSegment)));
		$data['title'] = $pageTitle;
		$pageContent = !empty($data['staticPage']->static_page_content)
			? trim(preg_replace('/\s+/u', ' ', strip_tags($data['staticPage']->static_page_content)))
			: '';
		$data['description'] = $pageContent !== ''
			? function_exists('mb_substr') ? mb_substr($pageContent, 0, 155) : substr($pageContent, 0, 155)
			: $pageTitle . ' hakkında Playsel bilgilendirme ve resmi sayfa içeriği.';
		$this->template->content->view('static-page', $data);
		$this->template->publish();
	}
}
