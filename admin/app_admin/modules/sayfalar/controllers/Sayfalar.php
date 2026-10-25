<?php defined('BASEPATH') or exit('No direct script access allowed');

class Sayfalar extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		//$this->data['user'] = userCheck();
		parent::__construct();
		$this->load->model('VtModel');
		$this->load->model('Sayfalar_model');
	}

	public function index()
	{
		$data = $this->data;
		
		$this->template->content->view('index', $data);
		$this->template->publish();
	}

	public function siteNews()
	{
		$data = $this->data;
		$siteID = $this->input->get('site');
		$site = $this->db->from('sites')->where('site_id', $siteID)->get()->row_array();
		if (!$site) {
			$this->session->set_flashdata('alert', ['title' => 'Hata', 'message' => 'Site bulunamadı.', 'type' => 'error']);
			redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('sayfalar'));
		}
		$news = $this->db->select('news.*, categories.title as category')
			->from('news')
			->join('categories', 'categories.id = news.category_id')
			->where('news.site_id', $siteID)
			->order_by('news.created_at', 'desc')
			->get()
			->result();
		$data['site'] = $site;
		$data['news'] = $news;
		//pre($data);die;
		$this->template->content->view('site-news', $data);
		$this->template->publish();
	}

	public function addSite()
	{
		$data = $this->data;
		$post = $this->input->post();
		$data = array(
			'site_url' => $post['site_url'],
		);
		$this->db->insert('sites', $data);
		$this->session->set_flashdata('alert', ['title' => 'Başarılı', 'message' => 'Site eklendi.', 'type' => 'success']);
		redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('sayfalar'));
	}

	public function saveNews()
	{
		$data = $this->data;
		$post = $this->input->post();
		$siteURL = trim($post['site_id']);
		$url = "https://" . $siteURL . "/cron/addNews";
		$newsAdd = [
			'category' => $post['category_id'],
			'title' => $post['title'],
			'description' => $post['description'],
			'content' => $post['content'],
			'image' => $post['image'],
			'kaynak' => $post['source'],
		];
		$postFields = http_build_query($newsAdd);
		$url = $url . '?' . $postFields;
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
		]);
		$response = curl_exec($curl);
		$curl_error = curl_error($curl);
		curl_close($curl);
		if ($curl_error) {
			$this->session->set_flashdata('alert', ['title' => 'Hata', 'message' => 'Haber eklenemedi.', 'type' => 'error']);
			redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('sayfalar'));
		} else {
			$message = 'Biri ' . $siteURL . 'e haber ekledi.';
			sendTelegramNotification($message);
			$category = $this->db->from('categories')->where('title', $post['category_id'])->get()->row_array();
			$categoryID = !empty($category['id']) ? $category['id'] : 0;
			$site = $this->db->from('sites')->where('site_url', $siteURL)->get()->row_array();
			$siteID = !empty($site['site_id']) ? $site['site_id'] : 0;
			$newsAdd = [
				'category_id' => $categoryID,
				'site_id' => $siteID,
				'title' => $post['title'],
				'description' => $post['description'],
				'content' => $post['content'],
				'image' => $post['image'],
				'kaynak' => $post['source'],
				'is_active' => $post['is_active'],
			];
			$insertID = $this->VtModel->insert('news', $newsAdd);
			if (!$insertID) {
				$this->session->set_flashdata('alert', ['title' => 'Hata', 'message' => 'Haber database eklenemedi.', 'type' => 'error']);
				redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('sayfalar'));
			}
			$this->session->set_flashdata('alert', ['title' => 'Başarılı', 'message' => 'Haber eklendi.', 'type' => 'success']);
			redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('sayfalar'));
		}
	}


	public function addNews()
	{
		$data = $this->data;
		$data['categories'] = $this->VtModel->getAll('categories');
		$data['sites'] = $this->VtModel->getAll('sites');
		$this->template->content->view('add-news', $data);
		$this->template->publish();
	}

	public function uploadImagesWithDropzone()
	{
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type');
		if (!empty($_FILES['file'])) {
			$file = $_FILES['file'];

			// Dosya Yolu
			$uploadPath = './uploads/';
			if (!is_dir($uploadPath)) {
				mkdir($uploadPath, 0777, true); // Klasör oluştur
			}

			// Dosya İsmini Temizle
			$fileName = seoSlug($file['name']); // Yukarıda verdiğim slug fonksiyonunu kullanabilirsiniz

			// Dosyayı Taşı
			$filePath = $uploadPath . $fileName;
			if (move_uploaded_file($file['tmp_name'], $filePath)) {
				echo json_encode(['status' => 'success', 'filePath' => base_url('uploads/' . $fileName)]);
			} else {
				echo json_encode(['status' => 'error', 'message' => 'Dosya yükleme başarısız.']);
			}
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Dosya bulunamadı.']);
		}
	}

	public function addSitesForTxt()
	{
		// $txt = file_get_contents('sites.txt');
		// $lines = explode("\n", $txt);
		// // pre($lines);
		// // die;
		// // add sites table site_url
		// foreach ($lines as $line) {
		// 	$line = trim($line);
		// 	$data = array(
		// 		'site_url' => $line,
		// 	);
		// 	$this->db->insert('sites', $data);
		// }
	}

	public function readNews()
	{
		$data = $this->data;
		$post = $this->input->post();
		$site = $this->db->from('sites')->where('site_id', $post['site_id'])->get()->row_array();
		if (!$site) {
			return json(['status' => 'error', 'message' => 'Site bulunamadı.']);
		}
		$subQuery = $this->db->select('readed_news_id')
			->from('site_is_readed')
			->where('readed_site_id', $site['site_id'])
			->get_compiled_select();

		$unreadNews = $this->db->select('news.*, categories.title as category')
			->from('news')
			->join('categories', 'categories.id = news.category_id')
			->where("news.id NOT IN ($subQuery)", null, false)
			->order_by('news.created_at', 'desc')
			->limit(100)
			->get()
			->result();
		if (empty($unreadNews)) {
			return json(['status' => 'error', 'message' => 'Okunacak haber bulunamadı.']);
		} else {
			return json(['status' => 'success', 'news' => $unreadNews]);
		}
	}

	public function newsReaded()
	{
		$data = $this->data;
		$post = $this->input->post();
		$siteID = $post['site_id'];
		$newsID = $post['news_id'];
		$site = $this->db->from('sites')->where('site_id', $siteID)->get()->row_array();
		if (!$site) {
			return json(['status' => 'error', 'message' => 'Site bulunamadı.']);
		}
		$news = $this->db->from('news')->where('id', $newsID)->get()->row_array();
		if (!$news) {
			return json(['status' => 'error', 'message' => 'Haber bulunamadı.']);
		}
		$check = $this->db->from('site_is_readed')->where(['readed_site_id' => $siteID, 'readed_news_id' => $newsID])->get()->row_array();
		if ($check) {
			return json(['status' => 'error', 'message' => 'Haber zaten okundu.']);
		}
		$this->db->insert('site_is_readed', ['readed_site_id' => $siteID, 'readed_news_id' => $newsID]);
		return json(['status' => 'success', 'message' => 'Haber okundu.']);
	}

	public function addSiteLink()
	{
		$data = $this->data;
		$data['links'] = $this->db->from('links')->get()->result();
		$this->template->content->view('add-link', $data);
		$this->template->publish();
	}

	public function addSiteLinkAction()
	{
		$post = $this->input->post();
		$linkType = $post['link_type'];
		$update = $this->db->where('link_type', $linkType)->update('links', ['link_content' => $post['link_content']]);

		if ($update) {
			$message = 'Biri footer url lerini değiştirdi.';
			sendTelegramNotification($message);
			$this->session->set_flashdata('alert', ['title' => 'Başarılı', 'message' => 'Link eklendi.', 'type' => 'success']);
			redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('sayfalar'));
		} else {
			$this->session->set_flashdata('alert', ['title' => 'Hata', 'message' => 'Link eklenemedi.', 'type' => 'error']);
			redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('sayfalar'));
		}
	}

	public function siteLinks()
	{
		$get = $this->input->get();
		$siteID = !empty($get['site']) ? $get['site'] : 0;
		$links = $this->db->from('links')
			->join('sites', 'sites.site_id = links.link_site_id')
			->where('link_site_id', $siteID)
			->get()->result();
		//pre($links);die;
		$data['links'] = $links;
		$this->template->content->view('site-links', $data);
		$this->template->publish();
	}

	public function deleteLink()
	{
		$id = $this->input->get('id');
		$link = $this->db->from('links')->where('link_id', $id)->get()->row_array();
		$update = $this->db->where('link_id', $id)->update('links', ['link_end_date' => '2025-01-01 00:00:00']);
		if ($update) {
			$this->session->set_flashdata('alert', ['title' => 'Başarılı', 'message' => 'Link silindi.', 'type' => 'success']);
			redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('sayfalar'));
		} else {
			$this->session->set_flashdata('alert', ['title' => 'Hata', 'message' => 'Link silinemedi.', 'type' => 'error']);
			redirect(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('sayfalar'));
		}
	}
}
