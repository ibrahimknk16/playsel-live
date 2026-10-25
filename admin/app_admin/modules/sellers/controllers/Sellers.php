<?php defined('BASEPATH') or exit('No direct script access allowed');

class Sellers extends MY_Controller
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
        // Kullanıcı listesini artık AJAX ile çekeceğiz
        $products = $this->db->from('products')->get()->result();
        $data['products'] = $products;
        
        $this->template->content->view('index', $data);
        $this->template->publish();
    }

    // ==================================================
    // DATATABLE SERVER-SIDE AJAX METODU
    // ==================================================
    public function getSellersList()
    {
        $postData = $this->input->post();
        
        // DataTables Parametreleri
        $draw = isset($postData['draw']) ? (int)$postData['draw'] : 1;
        $start = isset($postData['start']) ? (int)$postData['start'] : 0;
        $length = isset($postData['length']) ? (int)$postData['length'] : 10;
        $searchValue = isset($postData['search']['value']) ? $postData['search']['value'] : '';
        
        // Filtre Parametreleri
        $filterEmail = isset($postData['email']) ? trim($postData['email']) : '';
        $filterStatus = isset($postData['status']) ? $postData['status'] : '';
        $filterBalance = isset($postData['balance_type']) ? $postData['balance_type'] : '';

        // 1. Toplam Kayıt Sayısı (Filtresiz)
        $totalRecords = $this->db->where('user_type', 2)->count_all_results('users');

        // 2. Sorgu Oluşturma (Filtreli)
        $this->db->select('*')->from('users')->where('user_type', 2);

        // Arama (Search) Mantığı
        if (!empty($searchValue)) {
            $this->db->group_start()
                     ->like('user_name', $searchValue)
                     ->or_like('user_email', $searchValue)
                     ->or_like('user_id', $searchValue)
                     ->group_end();
        }

        // Email Filtresi
        if (!empty($filterEmail)) {
            $this->db->like('user_email', $filterEmail);
        }

        // Durum Filtresi (Aktif/Pasif vs.) - Eğer user_status kullanıyorsan
        if ($filterStatus !== '') {
            $this->db->where('user_status', (int)$filterStatus);
        }

        // Bakiye (Artı / Eksi) Filtresi - Senin kodunda doğrudan user_balance kolonu var
        if ($filterBalance !== '') {
            if ($filterBalance === 'positive') {
                $this->db->where("user_balance >", 0);
            } elseif ($filterBalance === 'negative') {
                $this->db->where("user_balance <", 0);
            } elseif ($filterBalance === 'zero') {
                $this->db->where("user_balance =", 0);
            }
        }

        // Filtrelenmiş Kayıt Sayısı
        $tempDb = clone $this->db;
        $filteredRecords = $tempDb->count_all_results();

        // Sıralama (Order)
        $this->db->order_by('user_id', 'ASC');

        // Sayfalama (Pagination)
        if ($length != -1) {
            $this->db->limit($length, $start);
        }

        $users = $this->db->get()->result();
        $data = [];

        // 3. Verileri DataTables Formatına Çevir
        foreach ($users as $user) {
            
            $balance = isset($user->user_balance) ? (float)$user->user_balance : 0;
            $balClass = $balance > 0 ? 'badge-light-success' : 'badge-light-danger';
            $created = !empty($user->user_created_at) ? date('d.m.Y H:i', strtotime($user->user_created_at)) : '-';
            
            $initial = $user->user_name !== '' ? mb_strtoupper(mb_substr($user->user_name, 0, 1)) : 'S';
            $tokenShort = !empty($user->user_token) ? html_escape(substr($user->user_token, 0, 3) . '...' . substr($user->user_token, -3)) : '-';

            // HTML Blokları
            $supplierCol = '
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px symbol-circle me-3">
                        <div class="symbol-label bg-light-primary text-primary fw-bold">'.$initial.'</div>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="text-gray-900 fw-bold text-hover-primary">
                            <a href="'.base_url('sellers/sellerDetail/' . $user->user_id).'" class="text-gray-900 text-hover-primary fw-bold">'.($user->user_name ?: '-').'</a>
                        </span>
                        <span class="text-muted fs-7">Supplier</span>
                    </div>
                </div>';

            $emailCol = $user->user_email !== '' ? '
                <a href="mailto:'.$user->user_email.'" class="text-gray-800 text-hover-primary">
                    <i class="ki-duotone ki-sms fs-6 me-2"><span class="path1"></span><span class="path2"></span></i>'.$user->user_email.'
                </a>' : '<span class="text-muted">-</span>';

            $tokenCol = $user->user_token !== '' ? '
                <div class="d-flex align-items-center gap-2">
                    <code class="text-gray-700">'.$tokenShort.'</code>
                    <button type="button" class="btn btn-icon btn-sm btn-light" title="Token kopyala" onclick="copyText(\''.$user->user_token.'\')">
                        <i class="ki-duotone ki-copy fs-5"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                </div>' : '<span class="text-muted">-</span>';

            $actionCol = '
                <a href="'.base_url('sellers/sellerDetail/' . $user->user_id).'" class="btn btn-sm btn-light-primary">
                    <i class="ki-duotone ki-eye fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>Detay
                </a>';

            // Satır dizisini oluştur (7 Sütun)
            $data[] = [
                '<span class="text-gray-800 fw-bold">#'.$user->user_id.'</span>',
                $supplierCol,
                $emailCol,
                $tokenCol,
                '<span class="badge '.$balClass.' fw-semibold">'.number_format($balance, 2, ',', '.').' TL</span>',
                '<span class="text-muted">'.$created.'</span>',
                $actionCol
            ];
        }

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);
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
				'user_type' => 2,
				'user_email' => $email,
				'user_token' => generateRandomString('48', 'az09'),
				'user_password' => $password,
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
				redirect('sellers');
			} else {
				$this->session->set_flashdata('error', 'Satıcı eklenirken bir hata oluştu.');
				redirect('sellers');
			}
		}
	}

	public function sellerDetail()
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
		$this->template->content->view('seller-detail', $data);
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
}
