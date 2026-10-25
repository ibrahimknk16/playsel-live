<?php defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends MY_Controller
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
		$categories = $this->db->select('*')->from('categories')->where('category_status !=', 2)->get()->result();
		$locales = $this->db->select('*')->from('locales')->get()->result();
		//->join('locales', 'categories.category_countries = locales.lang_code'); // diller dolunca buna geçilecek
		// Liste AJAX endpoint'i üzerinden yüklendiği için ilk HTML yanıtında satır taşımıyoruz.
		$data['categories'] = [];
		$data['locales'] = $locales;
		$this->template->content->view('index', $data);
		$this->template->publish();
	}

	public function ajaxList()
	{
		$draw = (int) $this->input->post('draw');
		$start = max(0, (int) $this->input->post('start'));
		$length = (int) $this->input->post('length');
		$length = $length > 0 && $length <= 500 ? $length : 15;
		$search = trim((string) ($this->input->post('search')['value'] ?? ''));

		$applyFilters = function () use ($search) {
			$query = $this->db->from('categories')->where('category_status !=', 2);
			if ($search !== '') {
				$query->group_start()
					->like('category_id', $search)
					->or_like('category_title', $search)
					->or_like('category_slug', $search)
					->or_like('category_publisher', $search)
					->group_end();
			}
			$status = $this->input->post('category_status');
			$popular = $this->input->post('category_is_popular');
			$type = $this->input->post('category_type');
			if ($status !== '' && is_numeric($status)) {
				$query->where('category_status', (int) $status);
			}
			if ($popular !== '' && is_numeric($popular)) {
				$query->where('category_is_popular', (int) $popular);
			}
			if ($type !== '' && is_numeric($type)) {
				$query->where('category_type', (int) $type);
			}
			return $query;
		};

		$total = (int) $this->db->from('categories')->where('category_status !=', 2)->count_all_results();
		$filtered = (int) $applyFilters()->count_all_results();
		$query = $applyFilters();
		$categories = $query->select('*')->order_by('category_id', 'DESC')->limit($length, $start)->get()->result();
		$data = [];
		foreach ($categories as $category) {
			$isActive = (int) $category->category_status === 1;
			$isPopular = (int) $category->category_is_popular === 1;
			$type = (int) ($category->category_type ?? 0);
			$typeText = $type === 2 ? 'Topup' : ($type === 3 ? 'Epin' : ($type === 4 ? 'CD Key' : '-'));
			$typeClass = $type === 2 ? 'badge-light-primary' : ($type === 3 ? 'badge-light-success' : ($type === 4 ? 'badge-light-warning' : 'badge-light'));
			$image = !empty($category->category_image)
				? '<img src="' . html_escape(getenv('CDN_URL') . 'uploads/categories/' . $category->category_image) . '" alt="">'
				: '<div class="symbol-label bg-light-info text-info fw-bold">' . html_escape(mb_strtoupper(mb_substr((string) $category->category_title, 0, 1))) . '</div>';

			$data[] = [
				'<span class="text-gray-800 fw-bold">#' . (int) $category->category_id . '</span>',
				'<div class="d-flex align-items-center"><div class="p-symbol m-2">' . $image . '</div><div class="p-name text-gray-900 fw-bold text-hover-primary"><a href="' . base_url('categories/edit/' . (int) $category->category_id) . '">' . html_escape($category->category_title ?: '-') . '</a></div></div>',
				html_escape($category->category_slug),
				'<span class="badge ' . ($isActive ? 'badge-light-success' : 'badge-light-danger') . ' fw-semibold js-cat-status-badge">' . ($isActive ? 'Aktif' : 'Pasif') . '</span><div class="form-check form-switch form-check-custom form-check-solid ms-2 d-inline-flex align-items-center"><input class="form-check-input js-category-status-switch" type="checkbox" data-category-id="' . (int) $category->category_id . '"' . ($isActive ? ' checked' : '') . '></div>',
				'<span class="badge ' . ($isPopular ? 'badge-light-success' : 'badge-light-danger') . ' fw-semibold js-cat-popular-badge">' . ($isPopular ? 'Evet' : 'Hayır') . '</span><div class="form-check form-switch form-check-custom form-check-solid ms-2 d-inline-flex align-items-center"><input class="form-check-input js-category-popular-switch" type="checkbox" data-category-id="' . (int) $category->category_id . '"' . ($isPopular ? ' checked' : '') . '></div>',
				'<span class="badge ' . $typeClass . ' fw-semibold">' . $typeText . '</span>',
				'<div class="actions-wrap"><a href="' . base_url('reports/categoryHourChart/' . (int) $category->category_id) . '" class="btn btn-sm btn-light-primary"><i class="ki-duotone ki-chart fs-5"><span class="path1"></span><span class="path2"></span></i></a><a href="' . base_url('categories/edit/' . (int) $category->category_id) . '" class="btn btn-sm btn-light-primary"><i class="ki-duotone ki-pencil fs-5"><span class="path1"></span><span class="path2"></span></i></a><button type="button" class="btn btn-sm btn-light-danger js-category-delete" data-category-id="' . (int) $category->category_id . '" data-category-title="' . html_escape($category->category_title) . '"><i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button></div>',
			];
		}

		return json(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
	}

	public function add()
	{
		$post = $this->input->post();
		if (empty($post)) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Kategori ekleme için gerekli veriler eksik.']);
			return redirect('categories');
		}

		$path = getenv('BASE_PATH') . 'uploads/categories/';
		$path2 = 'uploads/categories/';
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$imageName = upload_image('newCategoryImage', $path, $path2, 'img_');
		if (!$imageName) {
			$this->session->set_flashdata('alert', ["status" => "error", "message" => "Resim eklenirken bir hata oluştu"]);
			redirect('categories');
		}

		$thumbnailName = upload_image('newCategoryThumbnail', $path, $path2, 'thumb_');
		if (!$thumbnailName) {
			$this->session->set_flashdata('alert', ["status" => "error", "message" => "Thumbnail eklenirken bir hata oluştu"]);
			redirect('categories');
		}

		$category_slug = $this->slugify($post['newCategoryTitle']);
		$slugCheck = $this->db->select('category_slug')
			->from('categories')
			->where('category_slug', $category_slug)
			->where('category_status !=', 2)
			->get()
			->row();

		if ($slugCheck) {
			$this->session->set_flashdata('alert', ["status" => "error", "message" => "Aynı slug içeren kategori mevcut."]);
			redirect('categories');
		}
		$insertData = [
			'category_title'      => $post['newCategoryTitle'] ?? '',
			'category_slug'       => $category_slug,
			'category_publisher'  => $post['newCategoryPublisher'] ?? 'Dijipin',
			'category_image'      => $imageName ?? "",
			'category_thumbnail'  => $thumbnailName ?? "",
			'category_type'       => isset($post['category_type']) ? (int)$post['category_type'] : 2,
			'category_android_app_url'  => $post['newCategoryAndroidUrl'] ?? '',
			'category_ios_app_url'  => $post['newCategoryIosUrl'] ?? '',
			'category_status' => $post['newCategoryStatus'] ?? '1',
			'category_topupcenter_status' => $post['newCategoryTucStatus'] ?? '1',
			'category_is_main' => 1,
			'category_created_at' => date('Y-m-d H:i:s'),
			'category_updated_at' => date('Y-m-d H:i:s'),
		];

		$logData = [
			'log_controller' => 'new-category',
			'log_message'    => "Yeni kategori eklendi: {$post['newCategoryTitle']}. Ekleyen: " . $this->session->userdata('user_id'),
			'log_error_code' => '0',
			'log_created_at' => date('Y-m-d H:i:s'),
		];

		$this->db->trans_start();
		$this->db->insert('categories', $insertData);
		$this->db->insert('logs', $logData);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Kategori ekleme sırasında bir hata oluştu.']);
		} else {
			$this->session->set_flashdata('alert', ['status' => 'success', 'message' => 'Kategori başarıyla eklendi.']);
		}

		return redirect("categories");
	}

	public function edit($id)
	{
		$data = $this->data;

		$category = $this->db->select('*')
			->from('categories')
			->where('category_id', $id)
			->get()
			->row();

		if (!$category) {
			redirect('admin/categories');
		}

		// Yeni: category_lang JSON verisini al
		$categoryLangs = $this->db->select('*')
			->from('category_lang')
			->where('category_lang_category_id', $id)
			->get()
			->result();



		// locale_lang_code (örneğin: tr, en) bilgilerini al
		$langs = $this->db->select('locale_lang_code')
			->from('locales')
			->get()
			->result();

		$langs = array_map(function ($lang) {
			return $lang->locale_lang_code;
		}, $langs);
		$langs = array_unique($langs);

		// Yeni yapı: her dilin JSON verisini ayrıştır
		$categoryLangData = [];
		foreach ($categoryLangs as $lang) {
			$langCode = $lang->category_lang_lang;
			$jsonData = json_decode($lang->category_lang_json, true);
			$categoryLangData[$langCode] = $jsonData;
		}

		// Ülke bilgilerini al (değişmedi)
		$allLocales = $this->db->select('*')->from('locales')->get()->result();

		$selectedCountryCodes = !empty($category->category_countries)
			? json_decode($category->category_countries)
			: [];

		$selectedLocales = [];
		if (!empty($selectedCountryCodes)) {
			$selectedLocales = $this->db->select('*')
				->from('locales')
				->where_in('locale_code', $selectedCountryCodes)
				->get()
				->result();
		}

		$fraudRules = $this->db->select('*')
			->from('fraud_rules')
			->get()
			->result();
		$subCategories = $this->db->select('*')
			->from('categories')
			->where('category_parent_category_id', $id)
			->where('category_status !=', 2)
			->order_by('category_id', 'DESC')
			->get()
			->result();
		$availableMainCategories = $this->db->select('category_id, category_title')
			->from('categories')
			->where('category_is_main', 1)
			->where('category_id !=', $id)
			->where('category_status !=', 2)
			->order_by('category_title', 'ASC')
			->get()
			->result();

		$faqCategoryId = ($category->category_parent_category_id > 0) ? $category->category_parent_category_id : $id;
		$faqs = $this->db->select('*')
			->from('category_faqs')
			->where('faq_category_id', $faqCategoryId)
			->order_by('faq_order', 'ASC')
			->get()
			->result();

		// View'e aktarılacak veriler
		$data['langs'] = $langs;
		$data['category'] = $category;
		$data['category_faqs'] = $faqs;
		$data['subCategories'] = $subCategories;
		$data['locales'] = $allLocales;
		$data['selectedCountries'] = $selectedCountryCodes;
		$data['selectedLocales'] = $selectedLocales;
		$data['categoryLangData'] = $categoryLangData;
		$data['fraudRules'] = $fraudRules;
		$serverRecord = $this->db->get_where('servers', ['servers_category_id' => $id])->row();
		$serverList = !empty($serverRecord->servers_serversJson) ? json_decode($serverRecord->servers_serversJson, true) : [];
		$data['serverList'] = $serverList;
		$data['availableMainCategories'] = $availableMainCategories;
		$this->template->content->view('edit-category', $data);
		$this->template->publish();
	}

	public function addSubCategory()
	{
		$post = $this->input->post();
		if (empty($post) || empty($post['category_parent_category_id'])) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Eksik veri gönderildi.']);
			redirect($this->agent->referrer());
		}

		$parent_cat_id = (int)$post['category_parent_category_id'];
		$title = $post['sub_category_title'];
		$slug = $this->slugify($title);

		// Slug Kontrolü
		$slugCheck = $this->db->get_where('categories', ['category_slug' => $slug, 'category_status !=' => 2])->row();
		if ($slugCheck) {
			$slug = $slug . '-' . rand(1000, 9999); // Çakışma varsa rastgele sayı ekle
		}

		// Resim Yükleme (Opsiyonel)
		$imageName = "";
		if (!empty($_FILES['sub_category_image']['name'])) {
			$path = getenv('BASE_PATH') . 'uploads/categories/';
			$path2 = 'uploads/categories/';
			$imageName = upload_image('sub_category_image', $path, $path2, 'sub_');
		}

		$insertData = [
			'category_title'       => $title,
			'category_slug'        => $slug,
			'category_parent_category_id' => $parent_cat_id,
			'category_is_main'     => 0, // Alt kategori bayrağı
			'category_status'      => $post['sub_category_status'] ?? 1,
			'category_type'        => $post['category_type'] ?? 2, // Ana kategorinin tipiyle aynı yapılabilir
			'category_image'       => $imageName,
			'category_created_at'  => date('Y-m-d H:i:s'),
			'category_updated_at'  => date('Y-m-d H:i:s'),
		];

		if ($this->db->insert('categories', $insertData)) {
			$this->session->set_flashdata('alert', ['status' => 'success', 'message' => 'Alt kategori başarıyla eklendi.']);
		} else {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Alt kategori eklenirken hata oluştu.']);
		}

		redirect('categories/edit/' . $parent_cat_id . '#tab_subcategories');
	}

	public function updateSubCategory()
	{
		$post = $this->input->post();
		if (empty($post) || empty($post['sub_category_id'])) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Eksik veri gönderildi.']);
			redirect($this->agent->referrer());
		}

		$sub_cat_id = (int)$post['sub_category_id'];
		$parent_cat_id = (int)$post['category_parent_category_id'];

		$updateData = [
			'category_title'      => $post['sub_category_title'],
			'category_status'     => $post['sub_category_status'],
			'category_updated_at' => date('Y-m-d H:i:s'),
		];

		// Yeni Resim Yüklendiyse
		if (!empty($_FILES['sub_category_image']['name'])) {
			$path = getenv('BASE_PATH') . 'uploads/categories/';
			$path2 = 'uploads/categories/';
			$imageName = upload_image('sub_category_image', $path, $path2, 'sub_');
			if ($imageName) {
				$updateData['category_image'] = $imageName;
			}
		}

		$this->db->where('category_id', $sub_cat_id)->update('categories', $updateData);
		$this->session->set_flashdata('alert', ['status' => 'success', 'message' => 'Alt kategori başarıyla güncellendi.']);

		redirect('categories/edit/' . $parent_cat_id . '#tab_subcategories');
	}

	public function update()
	{
		$post = $this->input->post();

		if (empty($post) || empty($post['category_id'])) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Güncelleme için gerekli veriler eksik.']);
			return redirect('categories');
		}

		$category_id = $post['category_id'];
		$langs = $post['category_lang'];

		foreach ($langs as $langCode => $langContent) {
			$langContentFiltered = array_filter($langContent, function ($value) {
				return $value !== '';
			});

			if (empty($langContentFiltered)) {
				continue;
			}

			$jsonData = json_encode($langContentFiltered, JSON_UNESCAPED_UNICODE);

			$existing = $this->db
				->where('category_lang_category_id', $category_id)
				->where('category_lang_lang', $langCode)
				->get('category_lang')
				->row();

			if ($existing) {
				$this->db
					->where('category_lang_id', $existing->category_lang_id)
					->update('category_lang', [
						'category_lang_json' => $jsonData
					]);
			} else {
				$this->db->insert('category_lang', [
					'category_lang_category_id' => $category_id,
					'category_lang_lang' => $langCode,
					'category_lang_json' => $jsonData
				]);
			}
		}
		$cid  = (int)$post['category_id'];
		$path = getenv('BASE_PATH') . 'uploads/categories/';
		$path2 = 'uploads/categories/';

		// Klasör yoksa oluştur
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		// Mevcut dosya isimlerini al
		$row = $this->db
			->select('category_image, category_thumbnail,category_slug,category_parent_category_id')
			->from('categories')
			->where('category_id', $cid)
			->get()
			->row();

		// 2) Görsel Yükleme (Helper Kullanımı)
		$imageName = $row->category_image;
		if (!empty($_FILES['category_image']['name'])) {
			$uploaded = upload_image('category_image', $path, $path2, 'img_');
			if ($uploaded) {
				$imageName = $uploaded;
				if (!empty($row->category_image) && file_exists($path . $row->category_image)) {
					@unlink($path . $row->category_image);
				}
			} else {
				$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Görsel yüklenemedi.']);
				return redirect("categories/edit/{$cid}");
			}
		}

		$thumbName = $row->category_thumbnail;
		if (!empty($_FILES['category_thumbnail']['name'])) {
			$uploadedThumb = upload_image('category_thumbnail', $path, $path2, 'thumb_');
			if ($uploadedThumb) {
				$thumbName = $uploadedThumb;
				if (!empty($row->category_thumbnail) && file_exists($path . $row->category_thumbnail)) {
					@unlink($path . $row->category_thumbnail);
				}
			} else {
				$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Thumbnail yüklenemedi.']);
				return redirect("categories/edit/{$cid}");
			}
		}

		$categoryRequires = [];
		if (isset($post['category_requires']) && is_array($post['category_requires'])) {
			foreach ($post['category_requires'] as $req) {
				if (!empty($req['inputName'])) {
					$categoryRequires[] = [
						'inputName'         => $req['inputName'],
						'inputType'         => $req['inputType'] ?? 'text',
						'inputMaxLength'    => (isset($req['inputMaxLength']) && $req['inputMaxLength'] !== '') ? (int)$req['inputMaxLength'] : null,
						'inputMinLength'    => (isset($req['inputMinLength']) && $req['inputMinLength'] !== '') ? (int)$req['inputMinLength'] : null,
						'inputPattern'      => $req['inputPattern'] ?? '',
						'inputPatternError' => $req['inputPatternError'] ?? '',
						'inputPlaceholder'  => $req['inputPlaceholder'] ?? '',
						'inputRequired'     => (isset($req['inputRequired']) && $req['inputRequired'] == '1') ? true : true, //TBI şimdilik hep true servers bug var
					];
				}
			}
		}

		$update = [
			'category_title'      => $post['category_title']     ?? '',
			'category_publisher'  => $post['category_publisher'] ?? 'Dijipin',
			'category_image'      => $imageName,
			'category_thumbnail'  => $thumbName,
			'category_content'    => $post['category_content']   ?? '',
			'category_type'       => isset($post['category_type']) ? (int)$post['category_type'] : 1,
			'category_android_app_url'  => $post['category_android_app_url']     ?? '',
			'category_ios_app_url'  => $post['category_ios_app_url']     ?? '',
			'category_countries'  => isset($post['locales']) ? json_encode($post['locales']) : json_encode([]),
			'category_status'     => isset($post['category_status']) ? (int)$post['category_status'] : 0,
			'category_topupcenter_status'     => isset($post['category_topupcenter_status']) ? (int)$post['category_topupcenter_status'] : 0,
			'category_requires'           => json_encode($categoryRequires, JSON_UNESCAPED_UNICODE),
			'category_fraud_rule_id'      => isset($post['category_fraud_rule_id']) ? (int)$post['category_fraud_rule_id'] : 0,
			'category_updated_at' => date('Y-m-d H:i:s'),
		];

		// $post['category_slug] eğer kategorinin slug'ı değiştirildiyse, slug'ı başka category ile çakışmadığını kontrol et
		$mainSlug = $row->category_slug;
		if (isset($post['category_slug']) && $post['category_slug'] !== '') {
			$newSlug = $post['category_slug'];
			if ($newSlug !== $mainSlug) {
				$slugCheck = $this->db->select('category_id')
					->from('categories')
					->where('category_slug', $newSlug)
					->where('category_status !=', 2)
					->get()
					->row();

				if ($slugCheck) {
					$this->session->set_flashdata('alert', ['status' => 'error', 'message' => "Slug çakışması: {$newSlug} slug'ı başka bir kategori tarafından kullanılıyor."]);
					return redirect("categories/edit/{$cid}");
				} else {
					$update['category_slug'] = $newSlug;
				}
			}
		}


		$log = [
			'log_controller' => 'update-category',
			'log_message'    => "{$cid} ID'li kategori güncellendi. Güncelleyen: " . $this->session->userdata('admin')['admin_id'] . " - " . $this->session->userdata('admin')['admin_name'],
			'log_error_code' => '0',
			'log_created_at' => date('Y-m-d H:i:s'),
		];

		$serverListInput = [];
		if (!empty($post['servers_bulk'])) {
			$lines = explode("\n", trim($post['servers_bulk']));
			foreach ($lines as $line) {
				$line = trim($line);
				if (empty($line)) continue;

				// Satırı " : " işaretinden ikiye bölüyoruz
				$parts = explode(':', $line);
				if (count($parts) >= 2) {
					$title = trim($parts[0]);

					// ID kısmının içinde de kazara ":" varsa bozulmaması için geri kalanını birleştiriyoruz
					array_shift($parts);
					$value = trim(implode(':', $parts));

					if ($title !== '' && $value !== '') {
						$serverListInput[] = [
							'title' => $title,
							'value' => $value
						];
					}
				}
			}
		}
		$serversJsonData = json_encode($serverListInput, JSON_UNESCAPED_UNICODE);

		// Category ID'ye ait kayıt var mı kontrol et
		$serverExist = $this->db->get_where('servers', ['servers_category_id' => $cid])->row();

		if ($serverExist) {
			$this->db->where('servers_category_id', $cid)
				->update('servers', ['servers_serversJson' => $serversJsonData]);
		} else {
			if (!empty($serverListInput)) {
				$this->db->insert('servers', [
					'servers_category_id' => $cid,
					'servers_serversJson' => $serversJsonData
				]);
			}
		}

		$this->db->trans_start();
		$this->db->update('categories', $update, ['category_id' => $cid]);
		$this->db->insert('logs', $log);

		// SSS (FAQs) İşlemleri
		$faqCategoryId = ($row->category_parent_category_id > 0) ? $row->category_parent_category_id : $cid;
		if (isset($post['faqs']) && is_array($post['faqs'])) {
			$faq_ids_to_keep = [];
			foreach ($post['faqs'] as $faq) {
				$faq_id = isset($faq['faq_id']) ? (int)$faq['faq_id'] : 0;
				$faq_data = [
					'faq_category_id' => $faqCategoryId,
					'faq_question'    => $faq['faq_question'] ?? '',
					'faq_answer'      => $faq['faq_answer'] ?? '',
					'faq_order'       => isset($faq['faq_order']) ? (int)$faq['faq_order'] : 0,
					'faq_status'      => isset($faq['faq_status']) ? 1 : 0
				];
				
				if ($faq_id > 0) {
					$this->db->where('faq_id', $faq_id)->update('category_faqs', $faq_data);
					$faq_ids_to_keep[] = $faq_id;
				} else {
					$this->db->insert('category_faqs', $faq_data);
					$faq_ids_to_keep[] = $this->db->insert_id();
				}
			}
			// Silinenleri temizle
			if (!empty($faq_ids_to_keep)) {
				$this->db->where('faq_category_id', $faqCategoryId)->where_not_in('faq_id', $faq_ids_to_keep)->delete('category_faqs');
			} else {
				$this->db->where('faq_category_id', $faqCategoryId)->delete('category_faqs');
			}
		} else {
			$this->db->where('faq_category_id', $faqCategoryId)->delete('category_faqs');
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Kategori güncelleme sırasında bir hata oluştu.']);
		} else {
			$this->session->set_flashdata('alert', ['status' => 'success', 'message' => 'Kategori başarıyla güncellendi.']);
		}

		return redirect("categories/edit/{$cid}");
	}

	public function linkSubCategory()
	{
		$post = $this->input->post();
		if (empty($post['linked_category_id']) || empty($post['category_parent_category_id'])) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Eksik veri gönderildi.']);
			redirect($this->agent->referrer());
		}

		$sub_cat_id = (int)$post['linked_category_id'];
		$parent_cat_id = (int)$post['category_parent_category_id'];

		$updateData = [
			'category_parent_category_id' => $parent_cat_id,
			'category_is_main'       => 0,
			'category_updated_at'    => date('Y-m-d H:i:s'),
		];

		if ($this->db->where('category_id', $sub_cat_id)->update('categories', $updateData)) {
			$this->session->set_flashdata('alert', ['status' => 'success', 'message' => 'Mevcut kategori başarıyla alt kategori olarak bağlandı.']);
		} else {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Kategori bağlanırken hata oluştu.']);
		}

		redirect('categories/edit/' . $parent_cat_id . '#tab_subcategories');
	}

	public function unlinkSubCategory()
	{
		$post = $this->input->post();
		if (empty($post['sub_category_id']) || empty($post['category_parent_category_id'])) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Eksik veri gönderildi.']);
			redirect($this->agent->referrer());
		}

		$sub_cat_id = (int)$post['sub_category_id'];
		$parent_cat_id = (int)$post['category_parent_category_id'];

		$updateData = [
			'category_parent_category_id' => 0,
			'category_is_main'       => 1,
			'category_updated_at'    => date('Y-m-d H:i:s'),
		];

		if ($this->db->where('category_id', $sub_cat_id)->update('categories', $updateData)) {
			$this->session->set_flashdata('alert', ['status' => 'success', 'message' => 'Alt kategori bağlantısı koparıldı. Artık ana kategori olarak listelenecek.']);
		} else {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Bağlantı koparılırken hata oluştu.']);
		}

		redirect('categories/edit/' . $parent_cat_id . '#tab_subcategories');
	}

	public function delete($id = null)
	{
		$this->load->library('session');
		$this->load->helper(['url', 'file']);

		$post  = $this->input->post();
		$cid   = (int)($id ?? ($post['category_id'] ?? 0));
		$force = (int)($post['force'] ?? 0);

		if (!$cid) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status' => 'error',
					'message' => 'Kategori ID eksik.'
				]));
		}

		$row = $this->db->select('*')
			->from('categories')
			->where('category_id', $cid)
			->get()
			->row();

		if (!$row) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status' => 'error',
					'message' => 'Kategori bulunamadı.'
				]));
		}

		$activeProducts = $this->db->select('product_id, product_name')
			->from('products')
			->where('product_category_id', $cid)
			->where('product_status', 1)
			->order_by('product_id', 'DESC')
			->limit(15)
			->get()
			->result();

		$totalActiveCount = (int)$this->db->from('products')
			->where('product_category_id', $cid)
			->where('product_status', 1)
			->count_all_results();

		if ($totalActiveCount > 0 && $force !== 1) {
			$items = array_map(function ($p) {
				return [
					'product_id' => (int)$p->product_id,
					'product_name' => (string)$p->product_name,
				];
			}, $activeProducts);

			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status' => 'error',
					'code' => 'HAS_ACTIVE_PRODUCTS',
					'message' => "Bu kategoriye bağlı aktif ürünler var ({$totalActiveCount}).",
					'data' => [
						'total' => $totalActiveCount,
						'shown' => count($items),
						'items' => $items,
					]
				]));
		}

		$log = [
			'log_controller' => 'delete-category',
			'log_message'    => "{$cid} ID'li kategori silindi (soft). Silen: " . $this->session->userdata('user_id'),
			'log_error_code' => '0',
			'log_created_at' => date('Y-m-d H:i:s'),
		];

		$this->db->trans_start();

		$this->db->where('category_id', $cid)
			->update('categories', [
				'category_status' => 2,
				'category_updated_at' => date('Y-m-d H:i:s'),
			]);

		if ($totalActiveCount > 0) {
			$this->db->where('product_category_id', $cid)
				->update('products', [
					'product_status' => 2,
				]);
		}

		$this->db->insert('logs', $log);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status' => 'error',
					'message' => 'Kategori silinirken bir hata oluştu.'
				]));
		}

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => 'success',
				'message' => ($totalActiveCount > 0)
					? 'Kategori ve bağlı aktif ürünler silindi.'
					: 'Kategori silindi.'
			]));
	}


	public function changeStatus()
	{
		$post = $this->input->post();

		$categoryId = (int)($post['category_id'] ?? 0);
		$status     = isset($post['category_status']) ? (int)$post['category_status'] : null;
		$force      = (int)($post['force'] ?? 0);

		if ($categoryId <= 0 || ($status !== 0 && $status !== 1)) {
			return $this->output
				->set_status_header(400)
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Geçersiz istek']));
		}

		$exists = $this->db->select('category_id')
			->from('categories')
			->where('category_id', $categoryId)
			->get()->row();

		if (!$exists) {
			return $this->output
				->set_status_header(404)
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Kategori bulunamadı']));
		}

		// Sadece PASİF'e çekilirken bağlı AKTİF ürün kontrolü yap
		if ($status === 0 && $force !== 1) {

			$activeProducts = $this->db->select('product_id, product_name')
				->from('products')
				->where('product_category_id', $categoryId)
				->where('product_status', 1)
				->order_by('product_id', 'DESC')
				->limit(15)
				->get()
				->result();

			$totalActiveCount = (int)$this->db->from('products')
				->where('product_category_id', $categoryId)
				->where('product_status', 1)
				->count_all_results();

			if ($totalActiveCount > 0) {
				$items = array_map(function ($p) {
					return [
						'product_id' => (int)$p->product_id,
						'product_name' => (string)$p->product_name,
					];
				}, $activeProducts);

				return $this->output
					->set_status_header(200)
					->set_content_type('application/json')
					->set_output(json_encode([
						'status' => 'error',
						'code' => 'HAS_ACTIVE_PRODUCTS',
						'message' => "Bu kategoriye bağlı aktif ürünler var ({$totalActiveCount}).",
						'data' => [
							'total' => $totalActiveCount,
							'shown' => count($items),
							'items' => $items,
						]
					]));
			}
		}

		$updatedProducts = 0;

		$this->db->trans_start();

		// Kategori status güncelle
		$this->db->update('categories', [
			'category_status' => $status
		], ['category_id' => $categoryId]);

		// Kategori PASİF olursa bağlı aktif ürünleri de PASİF yap
		if ($status === 0) {
			$this->db->where('product_category_id', $categoryId)
				->where('product_status', 1)
				->update('products', [
					'product_status' => 0
				]);

			$updatedProducts = $this->db->affected_rows();
		}

		$log = [
			'log_controller' => 'change-category-status',
			'log_message'    => "{$categoryId} ID'li kategori durumu " . ($status ? 'aktif' : 'pasif') . " yapıldı. Güncelleyen: " .
				($this->session->userdata('admin')['admin_id'] ?? '-') . " - " .
				($this->session->userdata('admin')['admin_name'] ?? '') .
				($status === 0 ? " | Bağlı aktif ürünler 0 yapıldı: {$updatedProducts}" : ""),
			'log_error_code' => 'CAT_STATUS',
			'log_created_at' => date('Y-m-d H:i:s'),
		];
		$this->db->insert('logs', $log);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->output
				->set_status_header(500)
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Güncelleme sırasında hata oluştu']));
		}

		return $this->output
			->set_status_header(200)
			->set_content_type('application/json')
			->set_output(json_encode([
				'status'  => 'success',
				'message' => 'Kategori durumu güncellendi' . ($status === 0 ? " (Bağlı aktif ürünler pasife alındı: {$updatedProducts})" : ''),
				'data'    => [
					'category_id' => $categoryId,
					'category_status' => $status,
					'updated_products' => $updatedProducts
				]
			]));
	}


	public function changePopular()
	{
		$post = $this->input->post();

		$categoryId = (int)($post['category_id'] ?? 0);
		$status     = isset($post['category_is_popular']) ? (int)$post['category_is_popular'] : null;

		if ($categoryId <= 0 || ($status !== 0 && $status !== 1)) {
			return $this->output
				->set_status_header(400)
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Geçersiz istek']));
		}

		$exists = $this->db->select('category_id')
			->from('categories')
			->where('category_id', $categoryId)
			->get()->row();

		if (!$exists) {
			return $this->output
				->set_status_header(404)
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Kategori bulunamadı']));
		}

		$this->db->trans_start();

		$this->db->update('categories', [
			'category_is_popular' => $status
		], ['category_id' => $categoryId]);

		$log = [
			'log_controller' => 'change-category-popular',
			'log_message'    => "{$categoryId} ID'li kategori popüler durumu " . ($status ? 'evet' : 'hayır') . " yapıldı. Güncelleyen: " .
				($this->session->userdata('admin')['admin_id'] ?? '-') . " - " .
				($this->session->userdata('admin')['admin_name'] ?? ''),
			'log_error_code' => 'CAT_POPULAR',
			'log_created_at' => date('Y-m-d H:i:s'),
		];
		$this->db->insert('logs', $log);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->output
				->set_status_header(500)
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Güncelleme sırasında hata oluştu']));
		}

		return $this->output
			->set_status_header(200)
			->set_content_type('application/json')
			->set_output(json_encode([
				'status'  => 'success',
				'message' => 'Kategori popüler durumu güncellendi',
				'data'    => [
					'category_id' => $categoryId,
					'category_is_popular' => $status
				]
			]));
	}

	public function addFraudRule()
	{
		$post = $this->input->post();

		if (empty($post['fraud_rule_name'])) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Kural adı boş bırakılamaz.']);
			return redirect($_SERVER['HTTP_REFERER']);
		}

		$insertData = [
			'fraud_rule_name'                => trim($post['fraud_rule_name']),
			'fraud_rule_single_amount_limit' => isset($post['fraud_rule_single_amount_limit']) ? (float)$post['fraud_rule_single_amount_limit'] : 0,
			'fraud_rule_daily_total_limit'   => isset($post['fraud_rule_daily_total_limit']) ? (float)$post['fraud_rule_daily_total_limit'] : 0,
			'fraud_rule_daily_order_limit'   => isset($post['fraud_rule_daily_order_limit']) ? (int)$post['fraud_rule_daily_order_limit'] : 0,
			'fraud_rule_status'              => isset($post['fraud_rule_status']) ? (int)$post['fraud_rule_status'] : 1,
			'fraud_rule_created_at'          => date('Y-m-d H:i:s')
		];

		$this->db->insert('fraud_rules', $insertData);

		if ($this->db->insert_id()) {
			$this->session->set_flashdata('alert', ['status' => 'success', 'message' => 'Yeni fraud kuralı başarıyla eklendi.']);
		} else {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Kural eklenirken bir hata oluştu.']);
		}

		return redirect($_SERVER['HTTP_REFERER']);
	}

	public function updateFraudRule()
	{
		$post = $this->input->post();

		if (empty($post['fraud_rule_id'])) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Güncellenecek kural bulunamadı veya eksik veri gönderildi.']);
			return redirect($_SERVER['HTTP_REFERER']);
		}

		$fraud_rule_id = (int)$post['fraud_rule_id'];

		$updateData = [
			'fraud_rule_name'                => trim($post['fraud_rule_name']),
			'fraud_rule_single_amount_limit' => isset($post['fraud_rule_single_amount_limit']) ? (float)$post['fraud_rule_single_amount_limit'] : 0,
			'fraud_rule_daily_total_limit'   => isset($post['fraud_rule_daily_total_limit']) ? (float)$post['fraud_rule_daily_total_limit'] : 0,
			'fraud_rule_daily_order_limit'   => isset($post['fraud_rule_daily_order_limit']) ? (int)$post['fraud_rule_daily_order_limit'] : 0,
			'fraud_rule_status'              => isset($post['fraud_rule_status']) ? (int)$post['fraud_rule_status'] : 0,
			'fraud_rule_updated_at'          => date('Y-m-d H:i:s')
		];

		$this->db->trans_start();
		$this->db->where('fraud_rule_id', $fraud_rule_id);
		$this->db->update('fraud_rules', $updateData);

		$userId = $this->session->userdata('user_id') ?? $this->session->userdata('user')['user_id'] ?? 0;
		$this->db->insert('logs', [
			'log_controller' => 'update-fraud-rule',
			'log_message'    => "{$fraud_rule_id} ID'li Fraud Kuralı güncellendi. İşlem yapan: {$userId}",
			'log_error_code' => '0',
			'log_created_at' => date('Y-m-d H:i:s')
		]);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->session->set_flashdata('alert', ['status' => 'error', 'message' => 'Kural güncellenirken veritabanı tarafında bir hata oluştu.']);
		} else {
			$this->session->set_flashdata('alert', ['status' => 'success', 'message' => 'Fraud kuralı başarıyla güncellendi.']);
		}

		return redirect($_SERVER['HTTP_REFERER']);
	}

	public function categorySorts()
	{
		$data = $this->data;

		// Ana kategorileri çek (category_is_main = 1 ve aktif olanlar)
		$mainCategories = $this->db->select('category_id, category_title, category_image, category_sort_id, category_status')
			->from('categories')
			->where('category_is_main', 1)
			->where('category_status !=', 2)
			->order_by('category_sort_id', 'ASC')
			->order_by('category_id', 'ASC')
			->get()
			->result();

		$data['mainCategories'] = $mainCategories;
		$this->template->content->view('sort-category', $data);
		$this->template->publish();
	}

	public function getSubCategories()
	{
		$parentId = (int)$this->input->post('parent_id');

		if ($parentId <= 0) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Geçersiz kategori ID']));
		}

		$subCategories = $this->db->select('category_id, category_title, category_image, category_sort_id, category_status')
			->from('categories')
			->where('category_parent_category_id', $parentId)
			->where('category_status !=', 2)
			->order_by('category_sort_id', 'ASC')
			->order_by('category_id', 'ASC')
			->get()
			->result();

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(['status' => 'success', 'data' => $subCategories]));
	}

	public function updateCategorySort()
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
			$categoryId = (int)($item['category_id'] ?? 0);
			$sortOrder  = (int)($item['sort_order'] ?? 0);

			if ($categoryId > 0) {
				$this->db->where('category_id', $categoryId)
					->update('categories', [
						'category_sort_id'  => $sortOrder,
						'category_updated_at' => date('Y-m-d H:i:s')
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
			->set_output(json_encode(['status' => 'success', 'message' => 'Sıralama başarıyla güncellendi']));
	}

	private function slugify($text, string $divider = '-')
	{
		$text = preg_replace('~[^\pL\d]+~u', $divider, $text);
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = trim($text, $divider);
		$text = preg_replace('~-+~', $divider, $text);
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}
}
