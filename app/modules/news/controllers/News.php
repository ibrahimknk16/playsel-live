<?php defined('BASEPATH') or exit('No direct script access allowed');

class News extends MY_Controller
{
    public $data = array();
    public function __construct()
    {
        if ($_SERVER['SERVER_NAME'] == 'api.dijipin.com') die;
        parent::__construct();
        $this->data['locales'] = $this->locales();
        $this->data['uri'] = $this->uri->segment(1) ? $this->uri->segment(1) : 'tr-TR';
        $uri = explode('-', $this->data['uri']);
        if (count($uri) < 2) {
            $lang = 'tr'; 
            $country = 'TR'; 
        } else {
            $country = preg_replace('/[^A-Za-z]/', '', (string)$uri[1]);
                        $lang = preg_replace('/[^A-Za-z]/', '', (string)$uri[0]);
        }
        $this->lang->load('custom', $lang);
        $this->data['currentLocale'] = $this->db->get_where('locales', ['locale_lang_code' => $lang, 'locale_code' => $country])->row();
        $this->data['user'] = $this->session->userdata('user');
        $this->data['lang'] = $lang;
        $this->data['country'] = $country;
        $this->data['title'] = site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel') . ' | Haberler';
    }

    public function index()
    {
        $data = $this->data;
        if (($data['lang'] ?? '') === 'tr') {
            $data['title'] = 'Oyun Haberleri ve Rehberleri';
        }
        $data['description'] = 'En güncel haberler ve duyurular.';

        // Kategorileri getir (Yalnızca haberi olan aktif kategoriler)
        $this->db->select('categories.category_id, categories.category_title');
        $this->db->from('categories');
        $this->db->join('news', 'news.news_category_id = categories.category_id');
        $this->db->where('categories.category_status', 1);
        $this->db->where('news.news_status', 1);
        $this->db->where('news.news_published_at <=', date('Y-m-d H:i:s'));
        $this->db->group_by('categories.category_id');
        $data['categories'] = $this->db->get()->result();

        // Tüm aktif haberleri getir (Kategori filtreleme ve sayfalama anlık JS ile sorunsuz çalışır)
        $this->db->select('news.*, categories.category_title');
        $this->db->join('categories', 'categories.category_id = news.news_category_id', 'left');
        $this->db->where('news.news_status', 1);
        $this->db->where('news.news_published_at <=', date('Y-m-d H:i:s'));
        $this->db->order_by('news.news_published_at', 'DESC');
        $data['newsList'] = $this->db->get('news')->result();

        $this->template->content->view('index', $data);
        $this->template->publish();
    }

    public function detail($slug = '')
    {
        if (empty($slug)) {
            show_404();
        }

        $data = $this->data;
        
        $this->db->select('news.*, categories.category_title, categories.category_slug, categories.category_type');
        $this->db->join('categories', 'categories.category_id = news.news_category_id', 'left');
        $this->db->where('news.news_slug', $slug);
        $this->db->where('news.news_status', 1);
        $this->db->where('news.news_published_at <=', date('Y-m-d H:i:s'));
        $news = $this->db->get('news')->row();

        if (empty($news)) {
            show_404();
        }

        // Görüntülenme sayısını artır
        $this->db->where('news_id', $news->news_id);
        $this->db->set('news_views', 'news_views+1', FALSE);
        $this->db->update('news');

        $data['news'] = $news;
        $data['title'] = !empty($news->news_seo_title)
            ? $news->news_seo_title
            : site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel') . ' | Haberler - ' . $news->news_title;
        $data['description'] = !empty($news->news_seo_description)
            ? $news->news_seo_description
            : (!empty($news->news_summary) ? $news->news_summary : $news->news_title);
        $data['keywords'] = !empty($news->news_seo_keywords) ? $news->news_seo_keywords : $news->news_tags;
        $data['meta_type'] = 'article';
        $data['og_title'] = !empty($news->news_seo_title) ? $news->news_seo_title : $news->news_title;
        $data['og_description'] = $data['description'];
        $newsImageBase = rtrim((string)getenv('CDN_URL'), '/');
        if ($newsImageBase === '') {
            $newsImageBase = rtrim(base_url(), '/');
        }
        $data['og_image'] = !empty($news->news_image)
            ? $newsImageBase . '/uploads/news/' . ltrim($news->news_image, '/')
            : '';
        $pubTime = !empty($news->news_published_at) ? $news->news_published_at : $news->news_created_at;
        $data['article_published_time'] = !empty($pubTime) ? date('c', strtotime($pubTime)) : '';
        $data['article_modified_time'] = !empty($news->news_updated_at)
            ? date('c', strtotime($news->news_updated_at))
            : $data['article_published_time'];
        $data['article_section'] = $news->category_title ?? '';
        $data['article_tags'] = $news->news_tags ?? '';

        // İlgili/Rastgele diğer haberler (önerilenler kısmı için)
        $this->db->select('news.*, categories.category_title');
        $this->db->join('categories', 'categories.category_id = news.news_category_id', 'left');
        $this->db->where('news.news_status', 1);
        $this->db->where('news.news_published_at <=', date('Y-m-d H:i:s'));
        $this->db->where('news.news_id !=', $news->news_id);
        $this->db->order_by('RAND()');
        $this->db->limit(3);
        $data['relatedNews'] = $this->db->get('news')->result();

        $this->template->content->view('detail', $data);
        $this->template->publish();
    }
}
