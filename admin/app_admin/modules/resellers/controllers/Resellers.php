<?php defined('BASEPATH') or exit('No direct script access allowed');

class Resellers extends MY_Controller
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
		$data['products'] = $this->db->from('products')->get()->result();

		$this->template->content->view('index', $data);
		$this->template->publish();
	}

	public function getResellersList()
	{
		$postData = $this->input->post();

		// DataTables Parametreleri
		$draw = isset($postData['draw']) ? (int)$postData['draw'] : 1;
		$start = isset($postData['start']) ? (int)$postData['start'] : 0;
		$length = isset($postData['length']) ? (int)$postData['length'] : 10;
		$searchValue = isset($postData['search']['value']) ? $postData['search']['value'] : '';

		// Sıralama (Order) Parametreleri (YENİ EKLENDİ)
		$orderColumnIdx = isset($postData['order'][0]['column']) ? (int)$postData['order'][0]['column'] : null;
		$orderDir = isset($postData['order'][0]['dir']) ? $postData['order'][0]['dir'] : 'DESC';

		// Özel Filtre Parametreleri
		$filterEmail = isset($postData['email']) ? trim($postData['email']) : '';
		$filterTier = isset($postData['tier']) ? $postData['tier'] : '';
		$filterStatus = isset($postData['status']) ? $postData['status'] : '';
		$filterBalance = isset($postData['balance_type']) ? $postData['balance_type'] : '';

		// 1. Toplam Kayıt Sayısı (Filtresiz)
		$totalRecords = $this->db->where('user_type', 3)->count_all_results('users');

		// Bakiye Alt Sorgusu (SQL'in Kalbi)
		$balanceSubquery = "(
            SELECT 
                COALESCE(SUM(CASE WHEN user_balance_status = 1 THEN user_balance_amount ELSE 0 END), 0) - 
                COALESCE(SUM(CASE WHEN user_balance_status = 3 THEN user_balance_amount ELSE 0 END), 0)
            FROM user_balance 
            WHERE user_balance_user_id = u.user_id
        )";

		// 2. Sorgu Oluşturma (Filtreli ve Bakiye Sütunu Dahil)
		// DİKKAT: Artık bakiye hesaplanarak current_balance adıyla SQL'den geliyor!
		$this->db->select("u.*, $balanceSubquery AS current_balance", FALSE)
			->from('users u')
			->where('u.user_type', 3);

		// Arama Mantığı
		if (!empty($searchValue)) {
			$this->db->group_start()
				->like('u.user_name', $searchValue)
				->or_like('u.user_email', $searchValue)
				->or_like('u.user_id', $searchValue)
				->group_end();
		}

		if (!empty($filterEmail)) $this->db->like('u.user_email', $filterEmail);
		if ($filterTier !== '') $this->db->where('u.user_tier', (int)$filterTier);
		if ($filterStatus !== '') $this->db->where('u.user_status', (int)$filterStatus);

		// Bakiye (Artı / Eksi) Filtresi
		if ($filterBalance !== '') {
			if ($filterBalance === 'positive') {
				$this->db->where("$balanceSubquery > 0", NULL, FALSE);
			} elseif ($filterBalance === 'negative') {
				$this->db->where("$balanceSubquery < 0", NULL, FALSE);
			} elseif ($filterBalance === 'zero') {
				$this->db->where("$balanceSubquery = 0", NULL, FALSE);
			}
		}

		// Filtrelenmiş Kayıt Sayısı
		$tempDb = clone $this->db;
		$filteredRecords = $tempDb->count_all_results();

		// 3. SIRALAMA (ORDERING) İŞLEMİ (YENİ EKLENDİ)
		// DataTables'dan gelen sütun indexini, veritabanı sütunlarıyla eşleştiriyoruz.
		$columnsMap = [
			0 => 'u.user_id',
			1 => 'u.user_name',
			2 => 'u.user_email',
			// 3. sütun API Token (Sıralanamaz)
			4 => 'u.user_tier',
			5 => 'u.user_status',
			6 => 'current_balance', // İşte sihri yapan kısım! Bakiye sıralaması
			7 => 'u.user_created_at'
		];

		if ($orderColumnIdx !== null && isset($columnsMap[$orderColumnIdx])) {
			$this->db->order_by($columnsMap[$orderColumnIdx], $orderDir);
		} else {
			$this->db->order_by('u.user_id', 'DESC'); // Varsayılan sıralama
		}

		// Sayfalama
		if ($length != -1) {
			$this->db->limit($length, $start);
		}

		$users = $this->db->get()->result();
		$data = [];

		// Tipler ve Renkler
		$userTiers = [1 => 'Son Kullanıcı', 2 => 'Dijipin', 3 => 'API Kullanıcısı', 4 => 'API Özel', 5 => 'Playsel', 6 => 'Ödeme Kuruluşu'];
		$tierColorMap = [1 => 'light-primary', 2 => 'light-success', 3 => 'light-warning', 4 => 'light-danger', 5 => 'light-info', 6 => 'light-success'];

		foreach ($users as $user) {
			// MUAZZAM PERFORMANS: Artık PHP fonksiyonu çağırmıyoruz! SQL'den hesaplanmış geliyor.
			$balanceVal = (float)$user->current_balance;
			$balanceClass = $balanceVal >= 0 ? 'text-success' : 'text-danger';
			$balance = number_format($balanceVal, 2, ',', '.');

			$tierText = $userTiers[$user->user_tier] ?? '—';
			$tierBadge = $tierColorMap[(int)$user->user_tier] ?? 'light-secondary';
			$isActive = ((int)$user->user_status === 1);
			$initial = mb_strtoupper(mb_substr($user->user_name ?: 'R', 0, 1));
			$tokenShort = !empty($user->user_token) ? html_escape(substr($user->user_token, 0, 3) . '...' . substr($user->user_token, -3)) : '-';

			$resellerCol = '
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px symbol-circle me-3">
                        <div class="symbol-label bg-light-primary text-primary fw-bold">' . $initial . '</div>
                    </div>
                    <div class="d-flex flex-column">
                        <a href="' . base_url('users/detail/' . $user->user_id) . '" class="text-gray-900 text-hover-primary fw-bold">' . ($user->user_name ?: '-') . '</a>
                    </div>
                </div>';

			$emailCol = '<a href="mailto:' . $user->user_email . '" class="text-gray-800 text-hover-primary"><i class="ki-duotone ki-sms fs-6 me-2"><span class="path1"></span><span class="path2"></span></i>' . $user->user_email . '</a>';
			$tokenCol = !empty($user->user_token) ? '<div class="d-flex align-items-center gap-2"><code class="text-gray-700">' . $tokenShort . '</code><button type="button" class="btn btn-icon btn-sm btn-light" onclick="copyText(\'' . $user->user_token . '\')"><i class="ki-duotone ki-copy fs-5"><span class="path1"></span><span class="path2"></span></i></button></div>' : '-';

			$statusCol = '
                <div class="d-flex align-items-center">
                    <span class="badge ' . ($isActive ? 'badge-light-success' : 'badge-light-danger') . ' fw-semibold me-2 js-status-badge">' . ($isActive ? 'Aktif' : 'Pasif') . '</span>
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input js-user-status-switch" type="checkbox" value="1" data-user-id="' . $user->user_id . '" ' . ($isActive ? 'checked' : '') . ' />
                    </div>
                </div>';

			$actionCol = '<a href="' . base_url('users/detail/' . $user->user_id) . '" class="btn btn-sm btn-light-primary"><i class="ki-duotone ki-eye fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>Detay</a>';

			$data[] = [
				'<span class="text-gray-800 fw-bold">#' . $user->user_id . '</span>',
				$resellerCol,
				$emailCol,
				$tokenCol,
				'<span class="badge badge-' . $tierBadge . ' fw-semibold">' . html_escape($tierText) . '</span>',
				$statusCol,
				'<span class="fw-bold ' . $balanceClass . '">' . $balance . ' ₺</span>',
				'<span class="text-muted">' . $user->user_created_at . '</span>',
				$actionCol
			];
		}

		echo json_encode(["draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $filteredRecords, "data" => $data]);
	}


	public function add()
	{
		$post = $this->input->post();
		if (!empty($post)) {
			$email = $post['email'] ?? '';
			$password = password_hash(
				!empty($post['password']) ? $post['password'] : 'password',
				PASSWORD_DEFAULT
			);
			$ref = generateRandomString(16, '09');
			$checkRef = $this->db->get_where('users', ['user_bank_referance_number' => $ref])->row();
			while ($checkRef) {
				$ref = generateRandomString(16, '09');
				$checkRef = $this->db->get_where('users', ['user_bank_referance_number' => $ref])->row();
			}
			$insertData = [
				'user_name' => !empty($post['name']) ? $post['name'] : '',
				'user_nickname' => generate_unique_nickname($post['name'] ?? ''),
				'user_type' => 3,
				'user_email' => $email,
				'user_password' => $password,
				'user_token' => generateRandomString('48', 'az09'),
				'user_country' => 'TR',
				'user_lang' => 'tr',
				'user_bank_referance_number' => $ref
			];
			$insert = $this->db->insert('users', $insertData);
			$insertID = $this->db->insert_id();
			if ($insert) {
				$products = $post['products'] ?? [];
				if (!empty($products)) {
					foreach ($products as $productID) {
						$insertData = [
							'seller_products_seller_id' => $insertID,
							'seller_products_product_id' => $productID,
						];
						$this->db->insert('seller_products', $insertData);
					}
				}
				$this->session->set_flashdata('success', 'Satıcı başarıyla eklendi.');
				redirect('resellers');
			} else {
				$this->session->set_flashdata('error', 'Satıcı eklenirken bir hata oluştu.');
				redirect('resellers');
			}
		}
	}

	public function resellerDetail()
	{
		$data = $this->data;
		$sellerID = $this->uri->segment(3);
		$seller = $this->db->from('users')->where('user_id', $sellerID)->get()->row();
		$sellerProducts = $this->db
			->select('s.*, p.*, p.product_id as product_id, c.*')
			->from('users s')
			->join('seller_products sp', 's.user_id = sp.seller_products_seller_id')
			->join('products p', 'p.product_id = sp.seller_products_product_id', 'left')
			->join('categories c', 'c.category_id = p.product_category_id', 'left')
			->where('s.user_id', $sellerID)
			->where('sp.seller_products_status', 1)
			->get()
			->result();
		$sellerProductIDS = array_column($sellerProducts, 'product_id');
		$products = $this->db->from('products')->get()->result();
		$data['seller'] = $seller;
		$data['sellerProductIDS'] = $sellerProductIDS;
		$data['products'] = $products;
		$data['sellerProducts'] = $sellerProducts;
		$this->template->content->view('reseller-detail', $data);
		$this->template->publish();
	}

	public function updateSellerProducts()
	{
		$userID = $this->uri->segment(3);
		$post = $this->input->post();
		$products = $post['products'] ?? [];
		$this->db->where('seller_products_seller_id', $userID)->delete('seller_products');
		foreach ($products as $productID) {
			$insertData = [
				'seller_products_seller_id' => $userID,
				'seller_products_product_id' => $productID,
			];
			$this->db->insert('seller_products', $insertData);
		}
		$this->session->set_flashdata('success', 'Satıcının yetkili ürünleri başarıyla güncellendi.');
		redirect('sellers/sellerDetail/' . $userID);
	}

	public function changeUserStatus($userID = null)
	{
		$userID = (int)($userID ?: $this->uri->segment(3));
		if ($userID <= 0) {
			return json(['status' => 'error', 'message' => 'Geçersiz kullanıcı.']);
		}

		$user = $this->db->select('user_id,user_status')->from('users')->where('user_id', $userID)->get()->row();
		if (empty($user)) {
			return json(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
		}

		$status = $this->input->post('status', true);
		$status = (int)$status;

		if (!in_array($status, [0, 1], true)) {
			return json(['status' => 'error', 'message' => 'Geçersiz durum.']);
		}

		$ok = $this->db->where('user_id', $userID)->update('users', [
			'user_status' => $status,
		]);

		if ($ok) {
			return json([
				'status'  => 'success',
				'message' => $status === 1 ? 'Kullanıcı aktif edildi.' : 'Kullanıcı pasif edildi.',
				'data'    => ['user_id' => $userID, 'user_status' => $status]
			]);
		}

		return json(['status' => 'error', 'message' => 'Güncelleme başarısız.']);
	}
}
