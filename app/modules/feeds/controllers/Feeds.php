<?php defined('BASEPATH') or exit('No direct script access allowed');

class Feeds extends MY_Controller
{
	public $data = array();

	public function __construct()
	{
		if ($_SERVER['SERVER_NAME'] == 'api.dijipin.com') {
			die;
		}
		parent::__construct();

		$this->data['locales'] = $this->locales();

		// Router artık tr-TR, en-US formatında çalıştığı için varsayılanı da en-US yapıyoruz.
		$segment = $this->uri->segment(1);
		$this->data['uri'] = $segment ? $segment : 'en-US';

		$uri = explode('-', $this->data['uri']);
		if (count($uri) < 2) {
			$lang = 'en';
			$country = 'US';
		} else {
			$lang = $uri[0];
			$country = $uri[1];
		}

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

	public function sitemap($lang_code = null)
	{
		if (ob_get_length()) ob_clean();

		$current_uri = $lang_code ?: $this->data['uri'];
		$uri_parts = explode('-', $current_uri, 2);
		$lang = strtolower((string)($uri_parts[0] ?? ''));
		$country = strtoupper((string)($uri_parts[1] ?? ''));

		$locale = null;
		if (!empty($lang) && !empty($country)) {
			$locale = $this->db->select('locale_code, locale_lang_code')
				->from('locales')
				->where('locale_status', 1)
				->where('locale_lang_code', $lang)
				->where('locale_code', $country)
				->get()
				->row();
		}

		if (empty($locale)) {
			// Varsayılan ilk aktif locale'e dön
			$locale = $this->db->select('locale_code, locale_lang_code')
				->from('locales')
				->where('locale_status', 1)
				->order_by('locale_sort', 'ASC')
				->limit(1)
				->get()
				->row();
		}

		if (empty($locale)) {
			show_404();
			return;
		}

		$current_uri = strtolower($locale->locale_lang_code) . '-' . strtoupper($locale->locale_code);
		$country = strtoupper($locale->locale_code);
		$baseUrl = rtrim((string)(getenv('SITE_URL') ?: topupcenter()), '/');

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
		$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		// 1. Statik ve kurumsal sayfalar
		$staticPages = [
			''                         => ['priority' => '1.0', 'changefreq' => 'daily'],
			'categories'               => ['priority' => '0.9', 'changefreq' => 'daily'],
			'news'                     => ['priority' => '0.8', 'changefreq' => 'daily'],
			//'topup'                    => ['priority' => '0.8', 'changefreq' => 'weekly'],
			//'voucher'                  => ['priority' => '0.8', 'changefreq' => 'weekly'],
			'about-us'                 => ['priority' => '0.7', 'changefreq' => 'monthly'],
			'contact'                  => ['priority' => '0.7', 'changefreq' => 'monthly'],
			'faq'                      => ['priority' => '0.6', 'changefreq' => 'monthly'],
			'terms-conditions'         => ['priority' => '0.5', 'changefreq' => 'monthly'],
			'user-agreement'           => ['priority' => '0.5', 'changefreq' => 'monthly'],
			'privacy-policy'           => ['priority' => '0.5', 'changefreq' => 'monthly'],
			'cookie-policy'            => ['priority' => '0.5', 'changefreq' => 'monthly'],
			'refund-policy'            => ['priority' => '0.5', 'changefreq' => 'monthly'],
			'distance-sales-agreement' => ['priority' => '0.5', 'changefreq' => 'monthly'],
			'kvkk-policy'              => ['priority' => '0.5', 'changefreq' => 'monthly'],
			'aml-kyc-policy'           => ['priority' => '0.5', 'changefreq' => 'monthly'],
		];

		foreach ($staticPages as $page => $meta) {
			$pageUrl = $baseUrl . '/' . $current_uri . ($page !== '' ? '/' . $page : '');
			$this->addUrlElement($xml, $pageUrl, date('Y-m-d'), $meta['changefreq'], $meta['priority']);
		}

		// 2. Aktif Kategoriler (Alt kategorisi olanların sadece alt kategorileri, alt kategorisi olmayanların kendisi)
		$mainCategories = $this->db
			->select('category_id, category_slug, category_updated_at, category_created_at')
			->from('categories')
			->where('category_status', 1)
			->group_start()
				->where('category_parent_category_id', 0)
				->or_where('category_parent_category_id', null)
				->or_where('category_parent_category_id', '')
			->group_end()
			->where("JSON_CONTAINS(category_countries, " . $this->db->escape('"' . $country . '"') . ")", null, false)
			->order_by('category_sort_id', 'ASC')
			->get()->result();

		if (!empty($mainCategories)) {
			$mainCategoryIds = array_column($mainCategories, 'category_id');

			$subCategories = $this->db
				->select('category_id, category_parent_category_id, category_slug, category_updated_at, category_created_at')
				->from('categories')
				->where('category_status', 1)
				->where_in('category_parent_category_id', $mainCategoryIds)
				->order_by('category_sort_id', 'ASC')
				->get()->result();

			$subsByParent = [];
			if (!empty($subCategories)) {
				foreach ($subCategories as $sub) {
					$subsByParent[$sub->category_parent_category_id][] = $sub;
				}
			}

			foreach ($mainCategories as $mainCat) {
				if (!empty($subsByParent[$mainCat->category_id])) {
					// Alt kategorisi var: Ana kategoriyi alma, sadece alt kategorilerini bastır
					foreach ($subsByParent[$mainCat->category_id] as $subCat) {
						if (empty($subCat->category_slug)) continue;
						$categoryUrl = $baseUrl . '/' . $current_uri . '/' . $subCat->category_slug;
						$lastmod = !empty($subCat->category_updated_at) ? $subCat->category_updated_at : (!empty($subCat->category_created_at) ? $subCat->category_created_at : date('Y-m-d'));
						$this->addUrlElement($xml, $categoryUrl, $lastmod, 'daily', '0.8');
					}
				} else {
					// Alt kategorisi yok: Doğrudan kendisini bastır
					if (empty($mainCat->category_slug)) continue;
					$categoryUrl = $baseUrl . '/' . $current_uri . '/' . $mainCat->category_slug;
					$lastmod = !empty($mainCat->category_updated_at) ? $mainCat->category_updated_at : (!empty($mainCat->category_created_at) ? $mainCat->category_created_at : date('Y-m-d'));
					$this->addUrlElement($xml, $categoryUrl, $lastmod, 'daily', '0.8');
				}
			}
		}

		// XML çıktısını düzgün girintili ve satır sonlu olarak formatlayıp basıyoruz
		$this->renderXml($xml);
	}

	/**
	 * Haberler için ayrıştırılmış sitemap (/{locale}/news.xml)
	 */
	public function newsSitemap($lang_code = null)
	{
		if (ob_get_length()) ob_clean();

		$current_uri = $lang_code ?: $this->data['uri'];
		$uri_parts = explode('-', $current_uri, 2);
		$lang = strtolower((string)($uri_parts[0] ?? ''));
		$country = strtoupper((string)($uri_parts[1] ?? ''));

		$locale = null;
		if (!empty($lang) && !empty($country)) {
			$locale = $this->db->select('locale_code, locale_lang_code')
				->from('locales')
				->where('locale_status', 1)
				->where('locale_lang_code', $lang)
				->where('locale_code', $country)
				->get()
				->row();
		}

		if (empty($locale)) {
			// Varsayılan ilk aktif locale'e dön
			$locale = $this->db->select('locale_code, locale_lang_code')
				->from('locales')
				->where('locale_status', 1)
				->order_by('locale_sort', 'ASC')
				->limit(1)
				->get()
				->row();
		}

		if (empty($locale)) {
			show_404();
			return;
		}

		$current_uri = strtolower($locale->locale_lang_code) . '-' . strtoupper($locale->locale_code);
		$baseUrl = rtrim((string)(getenv('SITE_URL') ?: topupcenter()), '/');

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
		$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		// 1. Haberler Anasayfası
		$this->addUrlElement($xml, $baseUrl . '/' . $current_uri . '/news', date('Y-m-d'), 'daily', '0.9');

		// 2. Aktif Haber Detayları
		$newsList = $this->db
			->select('news_slug, news_updated_at, news_created_at, news_published_at')
			->from('news')
			->where('news_status', 1)
			->where('news_published_at <=', date('Y-m-d H:i:s'))
			->order_by('news_published_at', 'DESC')
			->get()->result();

		if (!empty($newsList)) {
			foreach ($newsList as $item) {
				if (empty($item->news_slug)) continue;
				$newsUrl = $baseUrl . '/' . $current_uri . '/news/' . $item->news_slug;
				$lastmod = !empty($item->news_updated_at) ? $item->news_updated_at : (!empty($item->news_published_at) ? $item->news_published_at : (!empty($item->news_created_at) ? $item->news_created_at : date('Y-m-d')));
				$this->addUrlElement($xml, $newsUrl, $lastmod, 'weekly', '0.8');
			}
		}

		$this->renderXml($xml);
	}

	public function sitemaps($locale = null)
	{
		if (ob_get_length()) ob_clean();

		$baseUrl = rtrim((string)(getenv('SITE_URL') ?: topupcenter()), '/');

		$locales = $this->db->select('locale_code, locale_lang_code')
			->from('locales')
			->where('locale_status', 1)
			->order_by('locale_sort', 'ASC')
			->get()->result();

		// En başa xml tanımını zorunlu olarak gömüyoruz
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sitemapindex/>');
		$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		foreach ($locales as $loc) {
			$lang_slug = strtolower($loc->locale_lang_code) . '-' . strtoupper($loc->locale_code);

			// Ana Sitemap (Statik sayfalar ve kategoriler)
			$sitemap = $xml->addChild('sitemap');
			$sitemapUrl = $baseUrl . '/' . $lang_slug . '/sitemap.xml';
			$sitemap->addChild('loc', htmlspecialchars($sitemapUrl, ENT_XML1, 'UTF-8'));
			$sitemap->addChild('lastmod', date('Y-m-d'));

			// Haberler Sitemap'i (news.xml)
			$newsSitemap = $xml->addChild('sitemap');
			$newsSitemapUrl = $baseUrl . '/' . $lang_slug . '/news.xml';
			$newsSitemap->addChild('loc', htmlspecialchars($newsSitemapUrl, ENT_XML1, 'UTF-8'));
			$newsSitemap->addChild('lastmod', date('Y-m-d'));
		}

		$this->renderXml($xml);
	}

	/**
	 * XML'i düzgün satır ve girintilerle basar
	 */
	private function renderXml(SimpleXMLElement $xml)
	{
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml->asXML());

		$this->output
			->set_header('Cache-Control: public, max-age=900')
			->set_content_type('text/xml', 'UTF-8')
			->set_output($dom->saveXML());
	}

	/**
	 * Sitemap XML url elemanı ekleme yardımcısı
	 */
	private function addUrlElement(SimpleXMLElement $xml, $loc, $lastmod = null, $changefreq = 'daily', $priority = '0.8')
	{
		$url = $xml->addChild('url');
		$url->addChild('loc', htmlspecialchars($loc, ENT_XML1, 'UTF-8'));

		if (!empty($lastmod)) {
			$time = is_numeric($lastmod) ? (int)$lastmod : strtotime($lastmod);
			$date = $time ? date('Y-m-d', $time) : date('Y-m-d');
			$url->addChild('lastmod', $date);
		} else {
			$url->addChild('lastmod', date('Y-m-d'));
		}

		if (!empty($changefreq)) {
			$url->addChild('changefreq', $changefreq);
		}

		if ($priority !== null && $priority !== '') {
			$url->addChild('priority', (string)$priority);
		}
	}

	public function googleMerchantFeed()
	{
		if (ob_get_length()) ob_clean();

		$locale = $this->db->select('locale_code, locale_lang_code, locale_currency')
			->from('locales')
			->where('locale_status', 1)
			->where('locale_lang_code', 'tr')
			->where('locale_code', 'TR')
			->get()
			->row();

		if (empty($locale)) {
			log_message('error', 'Google Merchant feed could not find the active tr-TR locale.');
			$this->output->set_status_header(503)->set_output('Feed configuration is unavailable.');
			return;
		}

		$products = $this->db
			->select('
				p.product_id,
				p.product_name,
				p.product_icon,
				p.product_countries,
				p.product_category_id,
				p.product_sub_category_id,
				p.product_sort_id,
				c.category_title,
				c.category_slug,
				c.category_publisher,
				c.category_sort_id,
				sc.category_slug AS sub_category_slug,
				sc.category_sort_id AS sub_category_sort_id,
				pp.product_price_standart_price,
				pp.product_price_currency
			')
			->from('products AS p')
			->join('categories AS c', 'c.category_id = p.product_category_id AND c.category_status = 1', 'inner')
			->join('categories AS sc', 'sc.category_id = p.product_sub_category_id AND sc.category_status = 1', 'left')
			->join(
				'product_prices AS pp',
				'pp.product_price_product_id = p.product_id AND pp.product_price_country = ' . $this->db->escape('TR'),
				'inner'
			)
			->where('p.product_status', 1)
			->where("JSON_CONTAINS(p.product_countries, " . $this->db->escape('"TR"') . ")", null, false)
			->where('pp.product_price_standart_price >=', 2)
			->order_by('c.category_sort_id', 'ASC')
			->order_by('sc.category_sort_id', 'ASC')
			->order_by('p.product_sort_id', 'ASC')
			->order_by('p.product_id', 'ASC')
			->get()
			->result();

		$baseUrl = rtrim((string) getenv('SITE_URL'), '/');
		if ($baseUrl === '') {
			$baseUrl = rtrim(topupcenter(), '/');
		}
		$localePath = 'tr-TR';
		$cdnUrl = rtrim((string) getenv('CDN_URL'), '/');
		$currency = !empty($locale->locale_currency) ? strtoupper($locale->locale_currency) : 'TRY';

		$xml = new SimpleXMLElement(
			'<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel/></rss>'
		);
		$channel = $xml->channel;
		$selfLink = $channel->addChild('atom:link', null, 'http://www.w3.org/2005/Atom');
		$selfLink->addAttribute('href', $baseUrl . '/feeds/googleMerchantFeed.xml');
		$selfLink->addAttribute('rel', 'self');
		$selfLink->addAttribute('type', 'application/rss+xml');
		$this->addCdataElement($channel, 'title', 'Playsel');
		$channel->addChild('link', $baseUrl);
		$this->addCdataElement($channel, 'description', 'Playsel dijital ürün kataloğu');

		foreach ($products as $product) {
			if (preg_match('/(^|[\s\-_])test([\s\-_]|$)/i', (string) $product->product_name)) {
				continue;
			}

			$categorySlug = !empty($product->sub_category_slug)
				? $product->sub_category_slug
				: $product->category_slug;
			if (empty($categorySlug) || empty($product->product_icon)) {
				continue;
			}

			$title = $this->cleanFeedText($product->product_name, 150);
			if ($title === '') {
				continue;
			}
			$description = $this->cleanFeedText(
				$product->product_name
					. ' - '
					. ($product->category_title ?: 'Dijital ürün')
					. '. Dijital ürün; kullanım ve bölge koşulları ürün detay sayfasında belirtilmiştir.',
				5000
			);
			$imagePath = ltrim((string) $product->product_icon, '/');
			$imageUrl = preg_match('#^https?://#i', $imagePath)
				? $imagePath
				: ($cdnUrl !== '' ? $cdnUrl : $baseUrl) . '/' . $imagePath;
			$brand = $this->cleanFeedText($product->category_publisher ?: 'Playsel', 70);

			$item = $channel->addChild('item');
			$this->addCdataElement($item, 'title', $title);
			$this->addPlainElement($item, 'link', $baseUrl . '/' . $localePath . '/' . rawurlencode($categorySlug) . '?p=' . (int) $product->product_id);
			$this->addCdataElement($item, 'description', $description);
			$guid = $item->addChild('guid');
			$guid[0] = 'playsel-' . (int) $product->product_id;
			$guid->addAttribute('isPermaLink', 'false');
			$this->addMerchantElement($item, 'id', (string) $product->product_id);
			$this->addMerchantElement($item, 'image_link', $imageUrl);
			$this->addMerchantElement($item, 'availability', 'in stock');
			$this->addMerchantElement(
				$item,
				'price',
				number_format((float) $product->product_price_standart_price, 2, '.', '') . ' ' . $currency
			);
			$this->addMerchantElement($item, 'brand', $brand);
			$this->addMerchantElement($item, 'condition', 'new');
			$this->addMerchantElement($item, 'identifier_exists', 'no');
			$this->addMerchantElement($item, 'google_product_category', '4097');
			if (!empty($product->category_title)) {
				$this->addMerchantElement($item, 'product_type', $this->cleanFeedText($product->category_title, 750));
			}
		}

		$this->renderXml($xml);
	}

	private function cleanFeedText($value, $max)
	{
		$text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
		$text = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $text);
		$text = trim(preg_replace('/\s+/u', ' ', (string) $text));
		return function_exists('mb_substr') ? mb_substr($text, 0, $max, 'UTF-8') : substr($text, 0, $max);
	}

	private function addPlainElement(SimpleXMLElement $item, $name, $value)
	{
		$child = $item->addChild($name);
		$child[0] = (string) $value;
	}

	private function addCdataElement(SimpleXMLElement $parent, $name, $value)
	{
		$parentNode = dom_import_simplexml($parent);
		$document = $parentNode->ownerDocument;
		$child = $document->createElement($name);
		$child->appendChild($document->createCDATASection((string) $value));
		$parentNode->appendChild($child);
	}

	private function addMerchantElement(SimpleXMLElement $item, $name, $value)
	{
		$namespaced = $item->addChild('g:' . $name, null, 'http://base.google.com/ns/1.0');
		$namespaced[0] = (string) $value;
	}
}
