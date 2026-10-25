<?php defined('BASEPATH') or exit('No direct script access allowed');

class Homee extends MY_Controller
{
    public $data = array();
    public function __construct()
    {
        if ($_SERVER['SERVER_NAME'] == 'api.dijipin.com')
            die;
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
        $this->data['title'] = site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel');
    }

    public function index()
    {
        $data = $this->data;
        $country = $data['country'];

        // 1. Slider Kategorilerini Çek (slider_categories tablosu varsa)
        $sliderCategories = [];
        if ($this->db->table_exists('slider_categories')) {
            $catQuery = $this->db->select('*')
                ->from('slider_categories')
                ->where('status', 1);

            if ($this->db->field_exists('language', 'slider_categories')) {
                $catQuery->group_start()
                    ->where('language', strtolower($data['lang']))
                    ->or_where('language', strtoupper($data['lang']))
                    ->or_where('language', '')
                    ->or_where('language IS NULL', null, false)
                    ->group_end();
            }

            if ($this->db->field_exists('sort_order', 'slider_categories')) {
                $catQuery->order_by('sort_order', 'ASC');
            }
            $dbCategories = $catQuery->order_by('id', 'ASC')->get()->result_array();

            if (!empty($dbCategories)) {
                foreach ($dbCategories as $cat) {
                    $sliderCategories[$cat['id']] = [
                        'id' => $cat['id'],
                        'category_id' => $cat['id'],
                        'name' => $cat['name'],
                        'bullet_img' => $cat['bullet_icon'] ?? '',
                        'slug' => $cat['slug'] ?? '',
                        'items' => []
                    ];
                }
            }
        }

        // 2. Aktif slider'ları DB'den çek
        if ($this->db->table_exists('slider_categories')) {
            $slidersQuery = $this->db->select('sliders.*, slider_categories.name as category_title, slider_categories.slug as category_slug')
                ->from('sliders')
                ->join('slider_categories', 'slider_categories.id = sliders.slider_category_id', 'left')
                ->where('sliders.slider_status', 1);
        } else {
            $slidersQuery = $this->db->select('sliders.*, categories.category_title, categories.category_slug')
                ->from('sliders')
                ->join('categories', 'categories.category_id = sliders.slider_category_id', 'left')
                ->where('sliders.slider_status', 1);
        }

        // Dil filtresi
        $slidersQuery->group_start()
            ->where('sliders.slider_language', strtolower($data['lang']))
            ->or_where('sliders.slider_language', strtoupper($data['lang']))
            ->or_where('sliders.slider_language', '')
            ->or_where('sliders.slider_language IS NULL', null, false)
            ->group_end();

        if ($this->db->field_exists('slider_sort_order', 'sliders')) {
            $slidersQuery->order_by('sliders.slider_sort_order', 'ASC');
        }
        $rawSliders = $slidersQuery->order_by('sliders.slider_id', 'ASC')->get()->result();

        // 3. Slider'ları dinamik olarak kategorilere dağıt (Hiçbir kategori hardcoded değildir)
        if (!empty($sliderCategories)) {
            foreach ($rawSliders as $slider) {
                $catId = $slider->slider_category_id;
                if (isset($sliderCategories[$catId])) {
                    $sliderCategories[$catId]['items'][] = $slider;
                }
            }
        } elseif (!empty($rawSliders)) {
            // slider_categories tablosu boş veya yoksa, eklenen slider'lardan dinamik kategoriler oluştur
            foreach ($rawSliders as $slider) {
                $catId = $slider->slider_category_id ?: 0;
                if (!isset($sliderCategories[$catId])) {
                    $catName = !empty($slider->category_title) ? $slider->category_title : ($slider->slider_title ?: 'Kampanyalar');
                    $sliderCategories[$catId] = [
                        'id' => $catId,
                        'category_id' => $catId,
                        'name' => $catName,
                        'bullet_img' => $slider->slider_bullet_image ?? '',
                        'slug' => $slider->category_slug ?? '',
                        'items' => []
                    ];
                }
                $sliderCategories[$catId]['items'][] = $slider;
            }
        }

        // 4. Yalnızca içinde slider görseli olan kategorileri aktar (İçi boş kategoriler veya sahte slaytlar basılmaz)
        $sliders = array_filter($sliderCategories, function ($category) {
            return !empty($category['items']);
        });

        $data['sliders'] = $sliders;
        // pre($sliders);die;
        // 1. Ana Kategorileri Çek
        $mainCategories = $this->db
            ->select('category_id, category_title, category_slug, category_publisher, category_type, category_thumbnail, category_image')
            ->from('categories')
            ->where('category_status', 1)
            ->group_start()
            ->where('category_parent_category_id', 0)
            ->or_where('category_parent_category_id', null)
            ->or_where('category_parent_category_id', '')
            ->group_end()
            ->where("JSON_CONTAINS(category_countries, '\"$country\"')", null, false)
            ->where("category_is_popular", 1)
            ->order_by('category_sort_id', 'ASC')
            ->get()
            ->result();

        if (!empty($mainCategories)) {
            // Performans için sadece gelen ana kategorilerin ID'lerini topluyoruz
            $mainCategoryIds = array_column($mainCategories, 'category_id');

            // 2. Sadece bu ana kategorilere ait olan aktif alt kategorileri çek
            $allSubCategories = $this->db
                ->select('category_id, category_parent_category_id, category_title, category_slug')
                ->from('categories')
                ->where('category_status', 1)
                ->where_in('category_parent_category_id', $mainCategoryIds)
                ->order_by('category_sort_id', 'ASC')
                ->get()->result();
            // 3. Alt Kategorileri Ana Kategorilerle Eşleştir
            foreach ($mainCategories as $mainCat) {
                $mainCat->sub_categories = [];
                foreach ($allSubCategories as $subCat) {
                    if ($subCat->category_parent_category_id == $mainCat->category_id) {
                        $mainCat->sub_categories[] = $subCat;
                    }
                }
            }
        }

        $giftCardIds = [55, 83, 82, 80, 76, 80, 158, 79, 75, 72, 69, 68, 153, 154, 155, 156, 157];
        $data['giftCards'] = $this->db
            ->select('category_id, category_title, category_slug, category_thumbnail')
            ->from('categories')
            ->where('category_status', 1)
            ->where("JSON_CONTAINS(category_countries, '\"$country\"')", null, false)
            ->where_in('category_id', $giftCardIds)
            ->order_by('FIELD(category_id, ' . implode(',', $giftCardIds) . ')')
            ->get()
            ->result();


        // rastgele 8 ürün çekelim
        $data['randomProducts'] = $this->db
            ->select('p.product_id, p.product_name, p.product_icon, c.category_slug, c.category_title')
            ->from('products as p')
            ->join('categories as c', 'c.category_id = p.product_category_id', 'left')
            ->where('p.product_status', 1)
            ->where('p.product_api_status', 1)
            ->where('c.category_status', 1)
            ->where('c.category_api_status', 1)
            ->where("JSON_CONTAINS(p.product_countries, '\"$country\"')", null, false)
            ->order_by('RAND()')
            ->limit(30)
            ->get()->result();

        $data['randomProducts'] = prodctPricesChangeLocationCurrency2($data['randomProducts'], $data['currentLocale']->locale_currency, 1, $data['currentLocale']->locale_lang_code);
        // product_price_standart_price = 0 olanları kaldırıyoruz
        $data['randomProducts'] = array_filter($data['randomProducts'], function ($product) {
            return isset($product->user_price) && $product->user_price > 0 && isset($product->list_price) && $product->list_price > 0 && $product->list_price > $product->user_price;
        });
        // product_icon içerisinde topupcenter yazanları da kaldırıyoruz
        $data['randomProducts'] = array_filter($data['randomProducts'], function ($product) {
            return isset($product->product_icon) && strpos($product->product_icon, 'topupcenter') === false;
        });
        $data['randomProducts'] = array_slice($data['randomProducts'], 0, 8);


        $data['latestNews'] = $this->db->select('news.*, categories.category_slug as news_category_slug, categories.category_title as news_category_title')
            ->from('news')
            ->join('categories', 'categories.category_id = news.news_category_id', 'left')
            ->where('news_status', 1)
            ->where('news_published_at <=', date('Y-m-d H:i:s'))
            ->order_by('news_published_at', 'DESC')
            ->limit(9)
            ->get()
            ->result();

        $data['categories'] = $mainCategories;

        // Vitrin (Showcase) Öğelerini Çek
        $data['showcase_grid'] = [];
        $data['showcase_top'] = null;
        $data['showcase_bottom'] = [];

        if ($this->db->table_exists('showcase_items')) {
            $showcaseItems = $this->db->select('*')
                ->from('showcase_items')
                ->where('status', 1)
                ->order_by('sort_order', 'ASC')
                ->order_by('id', 'ASC')
                ->get()
                ->result();

            $curLang = $data['lang'] ?? 'tr';

            foreach ($showcaseItems as $scItem) {
                // Çoklu dil çevirisini aktif dile göre uygula
                if (!empty($scItem->translations)) {
                    $trans = is_string($scItem->translations) ? json_decode($scItem->translations, true) : (array)$scItem->translations;
                    if (!empty($trans[$curLang])) {
                        if (!empty($trans[$curLang]['title'])) {
                            $scItem->title = $trans[$curLang]['title'];
                        }
                        if (isset($trans[$curLang]['subtitle']) && $trans[$curLang]['subtitle'] !== '') {
                            $scItem->subtitle = $trans[$curLang]['subtitle'];
                        }
                        if (!empty($trans[$curLang]['badge_text'])) {
                            $scItem->badge_text = $trans[$curLang]['badge_text'];
                        }
                    }
                }

                if ($scItem->type === 'grid') {
                    $data['showcase_grid'][] = $scItem;
                } elseif ($scItem->type === 'campaign_top' && !$data['showcase_top']) {
                    $data['showcase_top'] = $scItem;
                } elseif ($scItem->type === 'campaign_bottom') {
                    $data['showcase_bottom'][] = $scItem;
                }
            }
        }

        $data['title'] = 'Playsel | Türkiye&#x27;nin En Hızlı Oyuncu Alışveriş Sitesi | Aradığın Tüm Oyun Pinleri, CD-Key ve Epinler Burada';
        $data['description'] = $this->lang->line('home_description');

        $this->template->content->view('index', $data);
        $this->template->publish();
    }
    public function error404()
    {
        $data = $this->data;
        $country = $data['country'];

        // Popüler 4 Ana Kategoriyi Çek
        $mainCategories = $this->db
            ->select('category_id, category_title, category_slug, category_publisher, category_type, category_thumbnail, category_image')
            ->from('categories')
            ->where('category_status', 1)
            ->group_start()
            ->where('category_parent_category_id', 0)
            ->or_where('category_parent_category_id', null)
            ->or_where('category_parent_category_id', '')
            ->group_end()
            ->where("JSON_CONTAINS(category_countries, '\"$country\"')", null, false)
            ->order_by('category_sort_id', 'ASC')
            ->limit(4)
            ->get()
            ->result();

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
        }
        $data['popular_categories'] = $mainCategories ?? [];

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
            'aml-kyc-policy' => 'AML ve KYC Politikası',
            'kvkk-policy' => 'KVKK Politikası',
            'distance-sales-agreement' => 'Mesafeli Satış Sözleşmesi',
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

    public function contactUs()
    {
        $data = $this->data;
        $data['title'] = 'İletişim ve Hukuki Bilgiler';
        $data['description'] = 'Playsel iletişim bilgileri, şirket bilgileri ve hukuki bildirim kanallarına ulaşın.';
        $this->template->content->view('contact', $data);
        $this->template->publish();
    }
}
