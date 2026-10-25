<?php defined('BASEPATH') or exit('No direct script access allowed');

class Users extends MY_Controller
{
	public $data = array();

	public function __construct()
	{
		userCheck();
		parent::__construct();
		$this->load->model('Users_model');
		$this->load->library('R2_Storage');
	}

	public function index()
	{
		$data = $this->data;
		$data['countries'] = array_column(
			$this->db->distinct()->select('user_country')->where('user_type', 4)->where("user_country <> ''", null, false)->order_by('user_country')->get('users')->result_array(),
			'user_country'
		);
		$this->template->content->view('users', $data);
		$this->template->publish();
	}

	public function restrictions()
	{
		if (!$this->db->table_exists('customer_restrictions')) {
			show_error('customer_restrictions tablosu bulunamadı. migration/operations_requests.sql dosyasını çalıştırın.', 500);
		}
		$data = $this->data;
		$data['restrictions'] = $this->db
			->select('r.*, u.user_name, u.user_surname, u.user_email')
			->from('customer_restrictions r')
			->join('users u', 'u.user_id = r.restriction_user_id', 'left')
			->order_by('r.restriction_status', 'DESC')
			->order_by('r.restriction_id', 'DESC')->get()->result();
		$this->template->content->view('restrictions', $data);
		$this->template->publish();
	}

	public function searchUsersAjax()
	{
		$term = trim((string)$this->input->get('q', true));
		if ($term === '') {
			return json(['results' => []]);
		}

		$users = $this->db->select('user_id, user_name, user_surname, user_email, user_phone')
			->group_start()
				->like('user_id', $term)
				->or_like('user_name', $term)
				->or_like('user_surname', $term)
				->or_like('user_email', $term)
				->or_like('user_phone', $term)
			->group_end()
			->limit(30)
			->get('users')
			->result();

		$results = [];
		foreach ($users as $user) {
			$fullName = trim($user->user_name . ' ' . $user->user_surname);
			$text = "#" . $user->user_id . ' - ' . ($fullName !== '' ? $fullName . ' - ' : '') . $user->user_email;
			if (!empty($user->user_phone)) {
				$text .= ' (' . $user->user_phone . ')';
			}
			$results[] = [
				'id' => (int)$user->user_id,
				'text' => $text
			];
		}

		return json(['results' => $results]);
	}

	public function createRestriction()
	{
		$userId = (int)$this->input->post('user_id');
		$customerReason = trim((string)$this->input->post('customer_reason', true));
		$adminReason = trim((string)$this->input->post('admin_reason', true));
		$minutes = max(0, (int)$this->input->post('duration_minutes'));
		if (!$this->db->table_exists('customer_restrictions') || $userId <= 0 || $customerReason === '' || $adminReason === '') {
			return json(['status' => 'error', 'message' => 'Müşteri ve iki açıklama alanı zorunludur.'], 422);
		}
		$now = date('Y-m-d H:i:s');
		$this->db->trans_start();
		$this->db->insert('customer_restrictions', [
			'restriction_user_id' => $userId,
			'restriction_scope' => 'all',
			'restriction_customer_reason' => $customerReason,
			'restriction_admin_reason' => $adminReason,
			'restriction_starts_at' => $now,
			'restriction_expires_at' => $minutes > 0 ? date('Y-m-d H:i:s', strtotime('+' . $minutes . ' minutes')) : null,
			'restriction_created_by' => (int)($this->data['user']['admin_id'] ?? 0),
		]);
		$this->db->set('user_session_version', 'user_session_version + 1', false)
			->where('user_id', $userId)->update('users');
		$this->db->trans_complete();
		return json(['status' => $this->db->trans_status() ? 'success' : 'error', 'message' => 'Kısıtlama kaydedildi.']);
	}

	public function removeRestriction()
	{
		$id = (int)$this->input->post('restriction_id');
		if (!$this->db->table_exists('customer_restrictions') || $id <= 0) {
			return json(['status' => 'error', 'message' => 'Geçersiz kısıtlama.'], 422);
		}
		$restriction = $this->db->where('restriction_id', $id)->where('restriction_status', 1)
			->get('customer_restrictions')->row();
		if (!$restriction) {
			return json(['status' => 'error', 'message' => 'Kısıtlama zaten kaldırılmış veya süresi dolmuş.'], 409);
		}
		$this->db->trans_start();
		$this->db->where('restriction_id', $id)->update('customer_restrictions', [
			'restriction_status' => 0,
			'restriction_removed_by' => (int)($this->data['user']['admin_id'] ?? 0),
			'restriction_removed_at' => date('Y-m-d H:i:s'),
			'restriction_removal_reason' => trim((string)$this->input->post('removal_reason', true)),
		]);
		$this->db->set('user_session_version', 'user_session_version + 1', false)
			->where('user_id', (int)$restriction->restriction_user_id)->update('users');
		$this->db->trans_complete();
		return json(['status' => $this->db->trans_status() ? 'success' : 'error', 'message' => 'Kısıtlama kaldırıldı.']);
	}

	public function referenceRequests()
	{
		$data = $this->data;
		$this->template->content->view('reference_requests', $data);
		$this->template->publish();
	}

	public function getReferenceRequestsList()
	{
		$postData = $this->input->post();
		$start = isset($postData['start']) ? (int)$postData['start'] : 0;
		$length = isset($postData['length']) ? (int)$postData['length'] : 10;

		$this->db->select('r.*, u.user_name, u.user_email as user_email, u.user_reference_id, ref_new.user_nickname as new_ref_nickname, ref_new.user_email as new_ref_email, ref_old.user_nickname as old_ref_nickname, ref_old.user_email as old_ref_email');
		$this->db->from('reference_change_requests r');
		$this->db->join('users u', 'u.user_id = r.request_user_id', 'left');
		$this->db->join('users ref_new', 'ref_new.user_id = r.request_new_reference_id', 'left');
		$this->db->join('users ref_old', 'ref_old.user_id = r.request_old_reference_id', 'left');

		$tempDb = clone $this->db;
		$totalRecords = $tempDb->count_all_results();

		$this->db->order_by('r.request_status', 'DESC'); // 2 (pending) at the top
		$this->db->order_by('r.request_created_at', 'DESC');
		
		if ($length != -1) {
			$this->db->limit($length, $start);
		}

		$requests = $this->db->get()->result();
		$data = [];

		foreach ($requests as $req) {
			$statusBadge = '';
			$actions = '';
			
			if ($req->request_status == 2) {
				$statusBadge = '<span class="badge badge-light-warning">Bekliyor</span>';
				$actions = '
					<button class="btn btn-sm btn-icon btn-light-success js-approve-request" data-id="'.$req->request_id.'" title="Onayla"><i class="fa-solid fa-check"></i></button>
					<button class="btn btn-sm btn-icon btn-light-danger js-reject-request" data-id="'.$req->request_id.'" title="Reddet"><i class="fa-solid fa-xmark"></i></button>
				';
			} elseif ($req->request_status == 1) {
				$statusBadge = '<span class="badge badge-light-success">Onaylandı</span>';
			} else {
				$statusBadge = '<span class="badge badge-light-danger">Reddedildi</span>';
			}

			$userNameDisplay = $req->user_name . '<br><small class="text-muted">' . $req->user_email . '</small>';
			$oldRefDisplay = ($req->old_ref_nickname ?? 'Yok') . ($req->old_ref_email ? '<br><small class="text-muted">' . $req->old_ref_email . '</small>' : '');
			$newRefDisplay = ($req->new_ref_nickname ?? 'Yok') . ($req->new_ref_email ? '<br><small class="text-muted">' . $req->new_ref_email . '</small>' : '');

			$data[] = [
				$req->request_id,
				$userNameDisplay,
				$oldRefDisplay,
				$newRefDisplay,
				date('d.m.Y H:i', strtotime($req->request_created_at)),
				$statusBadge,
				$actions
			];
		}

		echo json_encode([
			"draw" => isset($postData['draw']) ? (int)$postData['draw'] : 1,
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalRecords,
			"data" => $data
		]);
	}

	public function approveReferenceRequest()
	{
		$request_id = $this->input->post('request_id');
		$req = $this->db->get_where('reference_change_requests', ['request_id' => $request_id])->row();
		
		if ($req && $req->request_status == 2) {
			$this->db->where('user_id', $req->request_user_id);
			$this->db->update('users', ['user_reference_id' => $req->request_new_reference_id]);

			$this->db->where('request_id', $request_id);
			$this->db->update('reference_change_requests', ['request_status' => 1]);
			echo json_encode(['status' => 'success', 'message' => 'Talebi onayladınız. Referans güncellendi.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem veya zaten onaylanmış/reddedilmiş.']);
		}
	}

	public function rejectReferenceRequest()
	{
		$request_id = $this->input->post('request_id');
		$req = $this->db->get_where('reference_change_requests', ['request_id' => $request_id])->row();
		
		if ($req && $req->request_status == 2) {
			$this->db->where('request_id', $request_id);
			$this->db->update('reference_change_requests', ['request_status' => 0]);
			echo json_encode(['status' => 'success', 'message' => 'Talebi reddettiniz.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem veya zaten onaylanmış/reddedilmiş.']);
		}
	}

		public function nicknameRequests()
		{
			$data['requests'] = $this->db
				->select('r.*, u.user_name, u.user_surname, u.user_email, u.user_nickname')
				->from('nickname_change_requests r')
				->join('users u', 'u.user_id = r.nickname_request_user_id', 'left')
				->order_by('r.nickname_request_status', 'DESC')
				->order_by('r.nickname_request_created_at', 'DESC')
				->get()->result();
			$this->template->content->view('nickname_requests', $data);
			$this->template->publish();
		}

		public function approveNicknameRequest()
		{
			$id = (int) $this->input->post('request_id');
			$request = $this->db->get_where('nickname_change_requests', ['nickname_request_id' => $id, 'nickname_request_status' => 2])->row();
			if (!$request || $this->db->where('user_nickname', $request->nickname_request_value)->count_all_results('users') > 0) {
				return json(['status' => 'error', 'message' => 'Talep geçersiz veya nickname artık kullanılıyor.'], 400);
			}
			$this->db->trans_start();
			$this->db->where('user_id', $request->nickname_request_user_id)->update('users', ['user_nickname' => $request->nickname_request_value]);
			$this->db->where('nickname_request_id', $id)->update('nickname_change_requests', ['nickname_request_status' => 1, 'nickname_request_updated_at' => date('Y-m-d H:i:s')]);
			$this->db->trans_complete();
			return json(['status' => 'success', 'message' => 'Nickname güncellendi.']);
		}

		public function rejectNicknameRequest()
		{
			$id = (int) $this->input->post('request_id');
			$updated = $this->db->where(['nickname_request_id' => $id, 'nickname_request_status' => 2])
				->update('nickname_change_requests', ['nickname_request_status' => 0, 'nickname_request_updated_at' => date('Y-m-d H:i:s')]);
			return json(['status' => $updated ? 'success' : 'error', 'message' => $updated ? 'Talep reddedildi.' : 'Talep bulunamadı.']);
		}


	public function addBalance($id)
	{
		$id = (int)$id;
		$user = $this->db->get_where('users', ['user_id' => $id])->row();
		if (!$user) {
			$this->session->set_flashdata('error', 'Kullanıcı bulunamadı.');
			redirect('users');
		}

		$amount = (float)$this->input->post('amount');
		$type = (int)$this->input->post('type');
		$note = trim($this->input->post('note'));

		if ($amount <= 0) {
			$this->session->set_flashdata('error', 'Lütfen geçerli bir tutar girin.');
			redirect('users/detail/' . $id);
			return;
		}

		if (!in_array($type, [1, 2])) {
			$type = 2; // Default harcanabilir
		}

		if (empty($note)) {
			$note = 'Yönetici tarafından bakiye eklendi';
		}

		$this->load->model('Wallet_model');
		$result = $this->Wallet_model->add_balance($id, $amount, $type, '', $note);

		if ($result['status']) {
			$this->session->set_flashdata('success', $result['message']);
			$this->session->set_flashdata('balance_ok', 1);
		} else {
			$this->session->set_flashdata('error', $result['message']);
		}

		redirect('users/detail/' . $id);
	}

	public function removeBalance($id)
	{
		$id = (int)$id;
		$amount = (float)$this->input->post('amount');
		$type = (int)$this->input->post('type');
		$note = trim((string)$this->input->post('note'));
		if ($note === '') {
			$note = 'Yönetici tarafından bakiye çıkarıldı';
		}

		$this->load->model('Wallet_model');
		$result = $this->Wallet_model->delete_balance($id, $amount, $type, $note);
		$this->session->set_flashdata($result['status'] ? 'success' : 'error', $result['message']);
		if ($result['status']) {
			$this->session->set_flashdata('balance_ok', 1);
		}
		redirect('users/detail/' . $id);
	}

	public function getUsersList()
	{
		$postData = $this->input->post();

		// DataTables Parametreleri
		$draw = isset($postData['draw']) ? (int)$postData['draw'] : 1;
		$start = isset($postData['start']) ? (int)$postData['start'] : 0;
		$length = isset($postData['length']) ? (int)$postData['length'] : 10;
		$searchValue = isset($postData['search']['value']) ? $postData['search']['value'] : '';

		// Özel Filtre Parametreleri
		$filterEmail = isset($postData['email']) ? trim($postData['email']) : '';
		$filterPhone = isset($postData['phone']) ? trim($postData['phone']) : '';
		$filterTier = isset($postData['tier']) ? $postData['tier'] : '';
		$filterStatus = isset($postData['status']) ? $postData['status'] : '';
		$filterBalance = isset($postData['balance']) ? (string)$postData['balance'] : '';
		$filterCountry = isset($postData['country']) ? strtoupper(trim((string)$postData['country'])) : '';
		$filterMail = isset($postData['mail_verified']) ? (string)$postData['mail_verified'] : '';
		$filterPhoneVerified = isset($postData['phone_verified']) ? (string)$postData['phone_verified'] : '';
		$filterIp = isset($postData['ip']) ? trim((string)$postData['ip']) : '';
		$sortKey = isset($postData['sort']) ? (string)$postData['sort'] : 'id';
		$orderDirection = strtolower((string)($postData['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';

		// 1. Toplam Kayıt Sayısı (Filtresiz) - type 4 (Son Kullanıcı)
		$totalRecords = $this->db->where('user_type', 4)->count_all_results('users');

		// 2. Sorgu Oluşturma (Filtreli)
		$withdrawableSql = '(COALESCE(SUM(CASE WHEN user_balance.user_balance_type = 1 AND user_balance.user_balance_status = 1 THEN user_balance.user_balance_amount ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN user_balance.user_balance_type = 1 AND user_balance.user_balance_status = 3 THEN user_balance.user_balance_amount ELSE 0 END), 0))';
		$this->db->select('users.*, (COALESCE(SUM(CASE WHEN user_balance.user_balance_status = 1 THEN user_balance.user_balance_amount ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN user_balance.user_balance_status IN (3, 4) THEN user_balance.user_balance_amount ELSE 0 END), 0)) AS current_balance, ' . $withdrawableSql . ' AS withdrawable_balance', false)
			->from('users')
			->join('user_balance', 'user_balance.user_balance_user_id = users.user_id', 'left')
			->where('users.user_type', 4)
			->group_by('users.user_id');

		// Arama (Search) Mantığı
		if (!empty($searchValue)) {
			$this->db->group_start()
				->like('user_name', $searchValue)
				->or_like('user_surname', $searchValue)
				->or_like('user_email', $searchValue)
				->or_like('user_phone', $searchValue)
				->or_like('user_id', $searchValue)
				->group_end();
		}

		// Email Filtresi
		if (!empty($filterEmail)) {
			$this->db->like('user_email', $filterEmail);
		}

		// Telefon Filtresi
		if (!empty($filterPhone)) {
			$this->db->like('user_phone', $filterPhone);
		}

		// Tier Filtresi
		if ($filterTier !== '') {
			$this->db->where('user_tier', (int)$filterTier);
		}

		// Durum Filtresi
		if ($filterStatus !== '') {
			$this->db->where('user_status', (int)$filterStatus);
		}

		if ($filterCountry !== '') {
			$this->db->where('UPPER(users.user_country)', $filterCountry);
		}
		if (in_array($filterMail, ['yes', 'no'], true)) {
			$this->db->where('users.user_email_is_verified', $filterMail === 'yes' ? 1 : 0);
		}
		if (in_array($filterPhoneVerified, ['yes', 'no'], true)) {
			$this->db->where('users.user_phone_is_verified', $filterPhoneVerified === 'yes' ? 1 : 0);
		}
		if ($filterIp !== '') {
			$likeIp = $this->db->escape('%' . $this->db->escape_like_str($filterIp) . '%');
			$this->db->where("users.user_id IN (SELECT user_logs_user_id FROM user_logs WHERE user_logs_user_event = 'REGISTER' AND user_logs_user_ip LIKE " . $likeIp . ")", null, false);
		}
		if ($filterBalance === 'positive') {
			$this->db->having('current_balance >', 0);
		} elseif ($filterBalance === 'zero') {
			$this->db->having('current_balance <=', 0);
		}

		// Filtrelenmiş Kayıt Sayısı
		$tempDb = clone $this->db;
		$filteredRecords = $tempDb->count_all_results();

		// Sıralama (Order)
		$orderColumns = [
			'id' => 'users.user_id',
			'name' => 'users.user_name',
			'email' => 'users.user_email',
			'country' => 'users.user_country',
			'balance' => 'current_balance',
			'withdrawable' => 'withdrawable_balance',
			'type' => 'users.user_tier',
			'active' => 'users.user_status',
			'date' => 'users.user_created_at',
		];
		$this->db->order_by($orderColumns[$sortKey] ?? 'users.user_id', $orderDirection);

		// Sayfalama (Pagination)
		if ($length != -1) {
			$this->db->limit($length, $start);
		}

		$users = $this->db->get()->result();
		$data = [];

		// Kayıt IP'leri yalnızca sayfadaki kullanıcılar için tek sorguda alınır.
		$registerIps = [];
		$pageIds = array_map('intval', array_column($users, 'user_id'));
		if (!empty($pageIds)) {
			$ipRows = $this->db->select('user_logs_user_id, user_logs_user_ip')
				->where_in('user_logs_user_id', $pageIds)
				->where('user_logs_user_event', 'REGISTER')
				->order_by('user_logs_id', 'ASC')
				->get('user_logs')->result();
			foreach ($ipRows as $ipRow) {
				if (!isset($registerIps[$ipRow->user_logs_user_id])) {
					$registerIps[$ipRow->user_logs_user_id] = (string)$ipRow->user_logs_user_ip;
				}
			}
		}

		$userTiers = [
			1 => 'Son Kullanıcı',
			2 => 'Dijipin',
			3 => 'API Kullanıcısı',
			4 => 'API Özel Kullanıcısı',
			5 => 'Playsel',
			6 => 'Ödeme Kuruluşu'
		];

		// Ham değerler döner; HTML kaçışı istemci tarafında yapılır.
		foreach ($users as $user) {
			$data[] = [
				'id' => (int)$user->user_id,
				'name' => trim(($user->user_name ?? '') . ' ' . ($user->user_surname ?? '')) ?: ($user->user_name ?: '-'),
				'email' => (string)$user->user_email,
				'email_verified' => (int)$user->user_email_is_verified === 1,
				'phone' => (string)$user->user_phone,
				'phone_verified' => (int)$user->user_phone_is_verified === 1,
				'country' => (string)$user->user_country,
				'register_ip' => $registerIps[$user->user_id] ?? '',
				'balance' => (float)$user->current_balance,
				'withdrawable' => (float)$user->withdrawable_balance,
				'type' => $userTiers[(int)$user->user_tier] ?? '—',
				'active' => (int)$user->user_status === 1,
				'created' => !empty($user->user_created_at) ? date('d.m.Y', strtotime($user->user_created_at)) : '-',
				'url' => base_url('users/detail/' . (int)$user->user_id),
			];
		}

		echo json_encode([
			"draw" => $draw,
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $filteredRecords,
			"data" => $data
		]);
	}

	public function addUser()
	{
		$post = $this->input->post();
		if (empty($post)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Hatalı istek.']);
			redirect(base_url('admins'));
		}

		$checkUserEmail = $this->db->where('admin_email', $post['email'])->get('admins')->row();
		if (!empty($checkUserEmail)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Bu e-posta adresi ile kayıtlı bir kullanıcı bulunmaktadır.']);
			redirect(base_url('admins'));
		}
		$insertData = [
			'admin_name' => !empty($post['name']) ? $post['name'] : '',
			'admin_email' => !empty($post['email']) ? $post['email'] : '',
			'admin_pass' => password_hash($post['password'], PASSWORD_DEFAULT),
			'admin_power' => 2,
		];
		if (empty($insertData['admin_name']) || empty($insertData['admin_email']) || empty($insertData['admin_pass'])) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Lütfen tüm alanları doldurun.']);
			redirect(base_url('admins'));
		}
		$insert = $this->db->insert('admins', $insertData);
		if ($insert) {
			$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Başarılı!', 'message' => 'Kullanıcı başarıyla eklendi.']);
			redirect(base_url('admins'));
		} else {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Kullanıcı eklenirken bir hata oluştu.']);
			redirect(base_url('admins'));
		}
	}

	public function detail()
	{
		$userID = $this->uri->segment(3);
		$user = $this->db->where('user_id', $userID)->get('users')->row();
		if (!$user) {
			show_404();
		}
		unset($user->user_password);
		$data['user'] = $user;
		$data['fraudDocuments'] = $this->getFraudDocuments((int) $userID);
		$data['userBalance'] = $this->userBalance($userID);

		$data['activeRestriction'] = null;
		if ($this->db->table_exists('customer_restrictions')) {
			$data['activeRestriction'] = $this->db->where('restriction_user_id', (int)$userID)
				->where('restriction_status', 1)
				->group_start()
					->where('restriction_expires_at IS NULL', null, false)
					->or_where('restriction_expires_at >', date('Y-m-d H:i:s'))
				->group_end()
				->order_by('restriction_id', 'DESC')->get('customer_restrictions')->row();
		}

		// Hesap hareketleri: tüm durumlar (0 bekleyen, 1 giriş, 2 iptal, 3 harcama/düşüm, 4 iade/red)
		$data['movements'] = $this->db->select('ub.*, a.admin_name, b.bank_name')
			->from('user_balance ub')
			->join('admins a', 'a.admin_id = ub.user_balance_admin_id', 'left')
			->join('banks b', 'b.bank_id = ub.user_balance_bank_id', 'left')
			->where('ub.user_balance_user_id', (int)$userID)
			->order_by('ub.user_balance_id', 'DESC')->limit(300)->get()->result();
		$data['spendingMovements'] = $this->db->select('ub.*, a.admin_name')
			->from('user_balance ub')
			->join('admins a', 'a.admin_id = ub.user_balance_admin_id', 'left')
			->where('ub.user_balance_user_id', (int)$userID)
			->where('ub.user_balance_status', 3)
			->order_by('ub.user_balance_id', 'DESC')->limit(300)->get()->result();
		$data['pendingPaymentOrders'] = $this->db
			->select('o.order_id, o.order_created_at, o.order_payment_type, o.order_sale_price, o.order_sale_price_currency, o.order_quantity, p.product_name')
			->from('orders o')
			->join('products p', 'p.product_id = o.order_product_id', 'left')
			->where('o.order_reseller_id', (int)$userID)
			->where('o.order_status', 11)
			->order_by('o.order_id', 'DESC')
			->limit(300)
			->get()->result();
		$data['movementSums'] = $this->db->select("COALESCE(SUM(CASE WHEN user_balance_status = 1 THEN user_balance_amount ELSE 0 END), 0) AS inflow, COALESCE(SUM(CASE WHEN user_balance_status = 3 THEN user_balance_amount ELSE 0 END), 0) AS outflow, COUNT(*) AS cnt", false)
			->where('user_balance_user_id', (int)$userID)->get('user_balance')->row();
		$registerLog = $this->db->select('user_logs_user_ip')
			->where('user_logs_user_id', (int)$userID)
			->where('user_logs_user_event', 'REGISTER')
			->order_by('user_logs_id', 'ASC')
			->limit(1)
			->get('user_logs')->row();
		$data['registerIp'] = !empty($registerLog->user_logs_user_ip) ? $registerLog->user_logs_user_ip : '—';
		$data['lastPayments'] = $this->lastPayments($userID);
		$data['summaryStats'] = $this->getUserSummaryStats($userID);
		$legacyHistory = $this->getLegacyHistory($userID);
		$data['legacy_orders'] = $legacyHistory['orders'];
		$data['legacy_order_rows'] = $legacyHistory['order_rows'];
		$data['legacy_payments'] = $legacyHistory['payments'];
		$data['legacy_cancellations'] = $legacyHistory['cancellations'];
		$data['legacy_balance_movements'] = $legacyHistory['balance_movements'];
		$data['legacyFilterOptions'] = $this->getLegacyFilterOptions($userID);
		$data['categories'] = $this->db->select('category_id, category_title')->get('categories')->result();
		$data['userCategories'] = $this->db->select('user_categories_category_id, user_categories_credit_enable')->where('user_categories_user_id', $userID)->get('user_categories')->result_array();
		$data['userCreditCategories'] = $data['userCategories'];
		$data['userCategories'] = !empty($data['userCategories']) ? array_column($data['userCategories'], 'user_categories_category_id') : [];
		//pre($data['lastPayments']);die;

		$this->load->model('Wallet_model');
		$data['userBalance2'] = $this->Wallet_model->get_user_balances($userID);

		$data['locales'] = $this->db->get('locales')->result();
		$citiesFilePath = dirname(FCPATH) . '/uploads/cities.json';
		$uniqueCities = [];

		if (file_exists($citiesFilePath)) {
			$citiesJson = file_get_contents($citiesFilePath);
			$citiesArray = json_decode($citiesJson, true);

			if (!empty($citiesArray)) {
				foreach ($citiesArray as $item) {
					if (!isset($uniqueCities[$item['plaka']])) {
						$uniqueCities[$item['plaka']] = $item['il'];
					}
				}
			}
			ksort($uniqueCities);
		}
		$data['cities'] = $uniqueCities;
		$data['invoices'] = $this->userInvoices($userID);
		$data['referredUsers'] = $this->db->select('user_id, user_name, user_nickname, user_email, user_created_at')
										  ->where('user_reference_id', $userID)
										  ->order_by('user_created_at', 'DESC')
										  ->get('users')->result();
		$data['bank_accounts'] = $this->db->get_where('user_bank_accounts', ['user_id' => $userID])->result();
		$data['user_addresses'] = [];
		if ($this->db->table_exists('user_addresses')) {
			$data['user_addresses'] = $this->db->where([
				'user_addresses_user_id' => (int)$userID,
				'user_addresses_is_deleted' => 0
			])->order_by('user_addresses_is_default', 'DESC')
			  ->order_by('user_addresses_id', 'DESC')
			  ->get('user_addresses')->result();
		}
		$this->template->content->view('detail', $data);
		$this->template->publish();
	}

	public function uploadFraudDocument()
	{
		$userId = (int) $this->input->post('user_id');
		$documentType = trim((string) $this->input->post('document_type', true));
		$note = trim((string) $this->input->post('admin_note', true));
		$allowedTypes = ['identity_front', 'identity_back', 'selfie', 'address_proof', 'payment_proof', 'other'];
		$user = $this->db->where('user_id', $userId)->get('users')->row();

		if (!$user || !in_array($documentType, $allowedTypes, true)) {
			return $this->fraudDocumentError('Geçersiz kullanıcı veya belge türü.', 422);
		}
		if (empty($_FILES['fraud_document']) || $_FILES['fraud_document']['error'] !== UPLOAD_ERR_OK) {
			return $this->fraudDocumentError('Geçerli bir belge seçin.', 422);
		}

		$file = $_FILES['fraud_document'];
		if ((int) $file['size'] > 10 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
			return $this->fraudDocumentError('Belge 10 MB sınırını aşmamalı.', 422);
		}

		$dimensions = @getimagesize($file['tmp_name']);
		if ($dimensions === false) {
			return $this->fraudDocumentError('Geçerli bir görsel yükleyin.', 422);
		}

		$relativePath = 'uploads/fraud-documents/' . $userId . '/';
		$localPath = rtrim((string) getenv('BASE_PATH'), '/\\') . '/' . $relativePath;
		$fileName = upload_image('fraud_document', $localPath, $relativePath, 'fraud_');
		if (!$fileName) {
			return $this->fraudDocumentError('Belge CDN’e yüklenemedi. R2 ayarlarınızı kontrol edin.', 500);
		}
		$objectKey = $relativePath . $fileName;

		$inserted = $this->db->insert('fraud_documents', [
			'user_id' => $userId,
			'document_type' => $documentType,
			'object_key' => $objectKey,
			'original_file_name' => substr(basename((string) $file['name']), 0, 255),
			'mime_type' => 'image/webp',
			'file_size' => (int) $file['size'],
			'sha256_hash' => hash_file('sha256', $file['tmp_name']),
			'status' => 'active',
			'admin_note' => $note !== '' ? $note : null,
			'uploaded_by' => (int) ($this->data['user']['admin_id'] ?? 0),
			'uploaded_at' => date('Y-m-d H:i:s'),
		]);

		if (!$inserted) {
			$this->r2_storage->delete($objectKey);
			return $this->fraudDocumentError('Belge yüklendi ancak kaydı oluşturulamadı.', 500);
		}

		$this->session->set_flashdata('success', 'Fraud belgesi kullanıcıya kaydedildi.');
		redirect('users/detail/' . $userId);
	}

	private function getFraudDocuments($userId)
	{
		if (!$this->db->table_exists('fraud_documents')) {
			return [];
		}

		return $this->db->select('d.*, a.admin_name')
			->from('fraud_documents d')
			->join('admins a', 'a.admin_id = d.uploaded_by', 'left')
			->where('d.user_id', $userId)
			->order_by('d.uploaded_at', 'DESC')
			->get()->result();
	}

	private function fraudDocumentError($message, $status)
	{
		if ($this->input->is_ajax_request()) {
			return json(['status' => 'error', 'message' => $message], $status);
		}
		$this->session->set_flashdata('error', $message);
		redirect('users/detail/' . (int) $this->input->post('user_id'));
	}

	private function getLegacyHistory($userID)
	{
		$userID = (int)$userID;
		$history = [
			'orders' => [],
			'order_rows' => [],
			'payments' => [],
			'cancellations' => [],
			'balance_movements' => [],
		];

		if ($userID <= 0) {
			return $history;
		}

		if ($this->db->query("SHOW TABLES LIKE 'legacy_orders'")->num_rows() > 0) {
			$history['orders'] = $this->db
				->where('target_user_id', $userID)
				->order_by('order_date', 'DESC')
				->get('legacy_orders')
				->result();

			$legacyKeys = array_map(function ($order) {
				return $order->legacy_order_key;
			}, $history['orders']);
			if (!empty($legacyKeys) && $this->db->query("SHOW TABLES LIKE 'legacy_order_rows'")->num_rows() > 0) {
				// Büyük IN listeleri CI query builder'da PCRE "regular expression is too large" hatası verir
				foreach (array_chunk($legacyKeys, 200) as $keyChunk) {
					$chunkRows = $this->db
						->where_in('legacy_order_key', $keyChunk)
						->get('legacy_order_rows')
						->result();
					$history['order_rows'] = array_merge($history['order_rows'], $chunkRows);
				}
				usort($history['order_rows'], function ($a, $b) {
					return strcmp((string)$b->source_order_no, (string)$a->source_order_no);
				});
			}
		}

		if ($this->db->query("SHOW TABLES LIKE 'legacy_payments'")->num_rows() > 0) {
			$history['payments'] = $this->db
				->where('target_user_id', $userID)
				->order_by('payment_date', 'DESC')
				->get('legacy_payments')
				->result();
		}

		if ($this->db->query("SHOW TABLES LIKE 'legacy_order_cancellations'")->num_rows() > 0) {
			$history['cancellations'] = $this->db
				->where('target_user_id', $userID)
				->order_by('refund_date', 'DESC')
				->get('legacy_order_cancellations')
				->result();
		}

		if ($this->db->query("SHOW TABLES LIKE 'legacy_balance_movements'")->num_rows() > 0) {
			$history['balance_movements'] = $this->db
				->where('target_user_id', $userID)
				->order_by('operation_date', 'DESC')
				->get('legacy_balance_movements')
				->result();
		}

		return $history;
	}

	private function legacyDistinctValues($table, $column, $userID)
	{
		if ($this->db->query("SHOW TABLES LIKE " . $this->db->escape($table))->num_rows() === 0) {
			return [];
		}
		$rows = $this->db->distinct()->select($column)
			->where('target_user_id', (int)$userID)
			->where($column . ' IS NOT NULL', null, false)
			->where($column . ' !=', '')
			->order_by($column, 'ASC')
			->get($table)
			->result_array();
		return array_column($rows, $column);
	}

	private function getLegacyFilterOptions($userID)
	{
		return [
			'payments' => [
				'methods' => $this->legacyDistinctValues('legacy_payments', 'method_text', $userID),
				'statuses' => $this->legacyDistinctValues('legacy_payments', 'status_text', $userID),
				'currencies' => $this->legacyDistinctValues('legacy_payments', 'currency', $userID),
				'purposes' => $this->legacyDistinctValues('legacy_payments', 'purpose_text', $userID),
			],
			'movements' => [
				'types' => $this->legacyDistinctValues('legacy_balance_movements', 'operation_type', $userID),
				'balance_types' => $this->legacyDistinctValues('legacy_balance_movements', 'balance_type', $userID),
				'currencies' => $this->legacyDistinctValues('legacy_balance_movements', 'currency', $userID),
			],
		];
	}

	// Ortak filtreler: tarih aralığı (Y-m-d) ve tutar aralığı. Değerler doğrulanır, sorguya parametreli girer.
	private function applyLegacyCommonFilters($dateColumn, $amountColumn)
	{
		$from = trim((string)$this->input->post('date_from'));
		$to = trim((string)$this->input->post('date_to'));
		if ($from !== '' && validateDate($from, 'Y-m-d')) {
			$this->db->where($dateColumn . ' >=', $from . ' 00:00:00');
		}
		if ($to !== '' && validateDate($to, 'Y-m-d')) {
			$this->db->where($dateColumn . ' <=', $to . ' 23:59:59');
		}

		$min = trim((string)$this->input->post('amount_min'));
		$max = trim((string)$this->input->post('amount_max'));
		if ($min !== '' && is_numeric($min)) {
			$this->db->where($amountColumn . ' >=', (float)$min);
		}
		if ($max !== '' && is_numeric($max)) {
			$this->db->where($amountColumn . ' <=', (float)$max);
		}
	}

	private function applyLegacyExactFilter($postKey, $column)
	{
		$value = trim((string)$this->input->post($postKey));
		if ($value !== '' && mb_strlen($value) <= 255) {
			$this->db->where($column, $value);
		}
	}

	private function applyLegacySearch($columns)
	{
		$q = trim((string)$this->input->post('q'));
		if ($q === '' || mb_strlen($q) > 255) {
			return;
		}
		$this->db->group_start();
		foreach ($columns as $i => $column) {
			if ($i === 0) {
				$this->db->like($column, $q);
			} else {
				$this->db->or_like($column, $q);
			}
		}
		$this->db->group_end();
	}

	private function legacyListResponse($table, $userID, $orderMap, $defaultOrder, $tieBreaker, $filterCallback, $rowCallback)
	{
		$draw = (int)$this->input->post('draw');
		$start = max(0, (int)$this->input->post('start'));
		$length = (int)$this->input->post('length');
		$length = ($length < 1) ? 20 : min($length, 250);

		$empty = ['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []];
		if ($userID <= 0 || $this->db->query("SHOW TABLES LIKE " . $this->db->escape($table))->num_rows() === 0) {
			return json($empty);
		}

		$total = (int)$this->db->from($table)->where('target_user_id', $userID)->count_all_results();

		$this->db->from($table)->where('target_user_id', $userID);
		call_user_func($filterCallback);
		$filtered = (int)$this->db->count_all_results('', false);

		$orderColumn = $defaultOrder[0];
		$orderDir = $defaultOrder[1];
		$order = $this->input->post('order');
		if (is_array($order) && isset($order[0]['column'], $order[0]['dir'])) {
			$idx = (int)$order[0]['column'];
			if (isset($orderMap[$idx])) {
				$orderColumn = $orderMap[$idx];
				$orderDir = strtolower($order[0]['dir']) === 'asc' ? 'ASC' : 'DESC';
			}
		}

		$rows = $this->db
			->order_by($orderColumn, $orderDir)
			->order_by($tieBreaker, 'ASC')
			->limit($length, $start)
			->get()
			->result();

		$data = [];
		foreach ($rows as $row) {
			$data[] = call_user_func($rowCallback, $row);
		}

		return json([
			'draw' => $draw,
			'recordsTotal' => $total,
			'recordsFiltered' => $filtered,
			'data' => $data,
		]);
	}

	public function legacyPaymentsList()
	{
		$userID = (int)$this->uri->segment(3);

		return $this->legacyListResponse(
			'legacy_payments',
			$userID,
			[
				0 => 'source_payment_id', 1 => 'source_order_id', 2 => 'source_customer_id', 3 => 'payment_date',
				4 => 'method_text', 5 => 'masked_card', 6 => 'cardholder_name', 7 => 'payment_ip', 8 => 'purpose_text', 9 => 'amount', 10 => 'net_amount', 11 => 'status_text',
			],
			['payment_date', 'DESC'],
			'legacy_payment_key',
			function () {
				$this->applyLegacySearch(['source_payment_id', 'source_order_id', 'source_customer_id', 'masked_card', 'cardholder_name', 'payment_ip', 'purpose_text', 'method_text', 'status_text']);
				$this->applyLegacyExactFilter('method', 'method_text');
				$this->applyLegacyExactFilter('status', 'status_text');
				$this->applyLegacyExactFilter('currency', 'currency');
				$this->applyLegacyExactFilter('purpose', 'purpose_text');
				$card = trim((string)$this->input->post('card'));
				if ($card !== '' && mb_strlen($card) <= 64) {
					$this->db->like('masked_card', $card);
				}
				$this->applyLegacyCommonFilters('payment_date', 'amount');
			},
			function ($p) {
				$currency = html_escape($p->currency ?: 'TRY');
				return [
					html_escape($p->source_payment_id ?: '-'),
					html_escape($p->source_order_id ?: '-'),
					html_escape($p->source_customer_id ?: '-'),
					html_escape($p->payment_date ?: '-'),
					'<span class="badge badge-light-info">' . html_escape($p->method_text ?: '-') . '</span>',
					html_escape($p->masked_card ?: '-'),
					html_escape($p->cardholder_name ?: '-'),
					html_escape($p->payment_ip ?: '-'),
					html_escape($p->purpose_text ?: '-'),
					$p->amount !== null ? nFormat($p->amount) . ' ' . $currency : '-',
					$p->net_amount !== null ? nFormat($p->net_amount) . ' ' . $currency : '-',
					'<span class="badge badge-light-warning">' . html_escape($p->status_text ?: 'Legacy') . '</span>',
					'<button type="button" class="cd-table-btn" data-cd-legacy-payment="' . html_escape($p->legacy_payment_key) . '">Ödeme Detayı</button>',
				];
			}
		);
	}

	public function legacyBalanceMovementsList()
	{
		$userID = (int)$this->uri->segment(3);

		return $this->legacyListResponse(
			'legacy_balance_movements',
			$userID,
			[
				0 => 'operation_date', 1 => 'operation_type', 2 => 'balance_type', 3 => 'amount', 4 => 'product',
				5 => 'description', 6 => 'customer_description', 7 => 'source_order_line_id', 8 => 'source_payment_id',
				9 => 'source_admin', 10 => 'target_balance_id',
			],
			['operation_date', 'DESC'],
			'source_movement_key',
			function () {
				$this->applyLegacySearch(['product', 'description', 'customer_description', 'source_order_line_id', 'source_payment_id', 'source_admin', 'operation_type', 'balance_type']);
				$this->applyLegacyExactFilter('type', 'operation_type');
				$this->applyLegacyExactFilter('balance_type', 'balance_type');
				$this->applyLegacyExactFilter('currency', 'currency');

				$direction = (string)$this->input->post('direction');
				if ($direction === 'in') {
					$this->db->where('amount >', 0);
				} elseif ($direction === 'out') {
					$this->db->where('amount <', 0);
				}

				$linked = (string)$this->input->post('linked');
				if ($linked === 'yes') {
					$this->db->where('target_balance_id IS NOT NULL', null, false);
				} elseif ($linked === 'no') {
					$this->db->where('target_balance_id IS NULL', null, false);
				}

				$this->applyLegacyCommonFilters('operation_date', 'amount');
			},
			function ($m) {
				$amountText = '-';
				if ($m->amount !== null) {
					$cls = ((float)$m->amount < 0) ? 'text-danger' : 'text-success';
					$amountText = '<span class="' . $cls . '">' . nFormat($m->amount) . ' ' . html_escape($m->currency ?: 'TRY') . '</span>';
				}
				return [
					html_escape($m->operation_date ?: '-'),
					'<span class="badge badge-light-info">' . html_escape($m->operation_type ?: '-') . '</span>',
					html_escape($m->balance_type ?: '-'),
					$amountText,
					html_escape($m->product ?: '-'),
					html_escape($m->description ?: '-'),
					html_escape($m->customer_description ?: '-'),
					html_escape($m->source_order_line_id ?: '-'),
					html_escape($m->source_payment_id ?: '-'),
					html_escape($m->source_admin ?: '-'),
					!empty($m->target_balance_id) ? '#' . (int)$m->target_balance_id : '-',
				];
			}
		);
	}

	public function userInvoices($userID)
	{
		// Veritabanında faturaya göre grupla ve toplamları al
		$invoices = $this->db->select('
            order_invoice_number,
            MAX(order_invoice_uuid) as order_invoice_uuid,
            MAX(order_invoice_date) as order_invoice_date,
            MIN(order_id) as first_order_id,
            MAX(order_id) as last_order_id,
            MIN(order_created_at) as first_order_date, /* YENİ EKLENEN */
            MAX(order_created_at) as last_order_date,  /* YENİ EKLENEN */
            COUNT(order_id) as total_orders,
            SUM(order_cost) as total_cost,
            SUM(order_sale_price) as total_sale_price
        ')
			->from('orders')
			->where('order_invoice_status', 1)
			->where('order_reseller_id', (int)$userID)
			->where('order_invoice_number !=', '')
			->group_by('order_invoice_number')
			->order_by('order_invoice_date', 'DESC')
			->get()
			->result_array();
		return $invoices;
	}

	public function deneme()
	{
		$this->load->view('deneme');
	}

	public function updateDetails($userID = null)
	{
		$userID = (int)($userID ?: $this->uri->segment(3));
		if ($userID <= 0) {
			return json(['status' => 'error', 'message' => 'Geçersiz kullanıcı.']);
		}

		$user = $this->db->where('user_id', $userID)->get('users')->row();
		if (empty($user)) {
			return json(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
		}

		$post = $this->input->post();
		if (empty($post)) {
			return json(['status' => 'error', 'message' => 'Hatalı istek.']);
		}

		$userLang = array_key_exists('user_lang', $post)
			? strtolower(trim((string)$post['user_lang']))
			: strtolower(trim((string)$user->user_lang));
		if ($userLang === '') {
			$userLang = 'tr';
		}

		// Basic sanitize
		$userName = array_key_exists('user_name', $post) ? trim((string)$post['user_name']) : trim((string)$user->user_name);
		$userSurname = array_key_exists('user_surname', $post) ? trim((string)$post['user_surname']) : trim((string)($user->user_surname ?? ''));
		if ($userSurname === '' && array_key_exists('user_name', $post)) {
			$parts = explode(' ', $userName);
			if (count($parts) > 1) {
				$userSurname = array_pop($parts);
				$userName = implode(' ', $parts);
			}
		}

		$update = [
			'user_name'              => $userName,
			'user_surname'           => $userSurname,
			'user_email'             => array_key_exists('user_email', $post) ? trim((string)$post['user_email']) : trim((string)$user->user_email),
			'user_phone'             => array_key_exists('user_phone', $post) ? trim((string)$post['user_phone']) : trim((string)$user->user_phone),
			'user_country'           => array_key_exists('user_country', $post) ? trim((string)$post['user_country']) : trim((string)$user->user_country),
			'user_lang'              => $userLang,
			'user_type'              => (int)($post['user_type'] ?? $user->user_type),
			'user_tier'              => (int)($post['user_tier'] ?? $user->user_tier),
			'user_email_is_verified' => array_key_exists('user_email_is_verified', $post) ? (int)$post['user_email_is_verified'] : (int)$user->user_email_is_verified,
			'user_phone_is_verified' => array_key_exists('user_phone_is_verified', $post) ? (int)$post['user_phone_is_verified'] : (int)$user->user_phone_is_verified,
			'user_notes'             => array_key_exists('user_notes', $post) ? trim((string)$post['user_notes']) : (string)$user->user_notes,
			'user_reference_id'      => array_key_exists('user_reference_id', $post) ? (int)$post['user_reference_id'] : (int)$user->user_reference_id,
			'user_ref_permission'    => array_key_exists('user_ref_permission', $post) ? (int)$post['user_ref_permission'] : (int)$user->user_ref_permission,
		];

		if (array_key_exists('user_credit_limit', $post)) {
			$creditLimit = $this->normalizeDecimalInput($post['user_credit_limit']);
			if ($creditLimit === null || $creditLimit < 0) {
				return json(['status' => 'error', 'message' => 'Kredi limiti gecerli bir sayi olmalidir.']);
			}

			$update['user_credit_limit'] = $creditLimit;
		}

		if (array_key_exists('user_birthday', $post)) {
			$birthday = trim((string)$post['user_birthday']);
			if ($birthday === '') {
				$update['user_birthday'] = null;
			} else {
				$bd = DateTime::createFromFormat('Y-m-d', $birthday);
				if (!$bd || $bd->format('Y-m-d') !== $birthday || $birthday > date('Y-m-d') || $birthday < '1900-01-01') {
					return json(['status' => 'error', 'message' => 'Geçerli bir doğum tarihi girin.']);
				}
				$update['user_birthday'] = $birthday;
			}
		}

		if (array_key_exists('user_status', $post)) {
			$newStatus = (int)$post['user_status'];
			if (!in_array($newStatus, [0, 1], true)) {
				return json(['status' => 'error', 'message' => 'Geçersiz hesap durumu.']);
			}
			$update['user_status'] = $newStatus;
		}

		if (array_key_exists('user_2fa_method', $post)) {
			$otpMethod = (int)$post['user_2fa_method'];
			if (!in_array($otpMethod, [0, 1, 2], true)) {
				return json(['status' => 'error', 'message' => 'Geçersiz OTP yöntemi.']);
			}
			if ($otpMethod === 1 && !filter_var($update['user_email'], FILTER_VALIDATE_EMAIL)) {
				return json(['status' => 'error', 'message' => 'E-posta ile OTP için geçerli bir e-posta gerekir.']);
			}
			if ($otpMethod === 2 && $update['user_phone'] === '') {
				return json(['status' => 'error', 'message' => 'SMS ile OTP için telefon numarası gerekir.']);
			}
			if ($this->db->field_exists('user_2fa_method', 'users')) {
				$update['user_2fa_method'] = $otpMethod;
				if ($otpMethod !== (int)($user->user_2fa_method ?? 0)) {
					log_message('info', 'Admin OTP method change: user_id=' . $userID . ', admin_id=' . (int)($this->data['user']['admin_id'] ?? 0) . ', ' . (int)($user->user_2fa_method ?? 0) . ' -> ' . $otpMethod);
				}
			}
		}

		if ($update['user_name'] === '' || $update['user_email'] === '') {
			return json(['status' => 'error', 'message' => 'Ad soyad ve email zorunludur.']);
		}

		if (!filter_var($update['user_email'], FILTER_VALIDATE_EMAIL)) {
			return json(['status' => 'error', 'message' => 'Geçerli bir email girin.']);
		}

		$exists = $this->db
			->where('user_email', $update['user_email'])
			->where('user_id !=', $userID)
			->get('users')
			->row();

		if (!empty($exists)) {
			return json(['status' => 'error', 'message' => 'Bu email başka bir kullanıcıda kayıtlı.']);
		}

		// allowed values
		if (!in_array($update['user_type'], [2, 3, 4], true)) $update['user_type'] = (int)$user->user_type;
		if (!in_array($update['user_tier'], [1, 2, 3, 4, 5, 6], true)) $update['user_tier'] = (int)$user->user_tier;
		if (!in_array($update['user_email_is_verified'], [0, 1], true)) $update['user_email_is_verified'] = (int)$user->user_email_is_verified;
		if (!in_array($update['user_phone_is_verified'], [0, 1], true)) $update['user_phone_is_verified'] = (int)$user->user_phone_is_verified;

		// Update
		$ok = $this->Users_model->update_user_details($userID, $update);

		if ($ok) {
			return json(['status' => 'success', 'message' => 'Kullanıcı bilgileri güncellendi.']);
		}

		return json(['status' => 'error', 'message' => 'Güncelleme başarısız veya değişiklik yok.']);
	}

	public function resetPassword($userID = null)
	{
		$userID = (int)$userID;
		$sendEmail = (int)$this->input->post('send_email') === 1;
		$sendSms = (int)$this->input->post('send_sms') === 1;
		$user = $this->db->where('user_id', $userID)->get('users')->row();

		if (!$user) {
			return json(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.'], 404);
		}
		if (!$sendEmail && !$sendSms) {
			return json(['status' => 'error', 'message' => 'En az bir bildirim yöntemi seçin.'], 422);
		}
		if ($sendEmail && !filter_var($user->user_email, FILTER_VALIDATE_EMAIL)) {
			return json(['status' => 'error', 'message' => 'Kullanıcının geçerli bir e-posta adresi yok.'], 422);
		}
		if ($sendSms && trim((string)$user->user_phone) === '') {
			return json(['status' => 'error', 'message' => 'Kullanıcının telefon numarası yok.'], 422);
		}

		$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
		$newPassword = '';
		for ($i = 0; $i < 16; $i++) {
			$newPassword .= $characters[random_int(0, strlen($characters) - 1)];
		}

		$this->db->trans_start();
		$this->db->where('user_id', $userID)->update('users', [
			'user_password' => password_hash($newPassword, PASSWORD_DEFAULT),
		]);
		if ($this->db->field_exists('user_session_version', 'users')) {
			$this->db->set('user_session_version', 'user_session_version + 1', false)
				->where('user_id', $userID)->update('users');
		}
		$this->db->trans_complete();
		if (!$this->db->trans_status()) {
			return json(['status' => 'error', 'message' => 'Şifre değiştirilemedi.'], 500);
		}

		$message = "Playsel giriş bilgileriniz değiştirilmiştir.\nE-posta adresiniz: "
			. $user->user_email . "\nYeni şifreniz: " . $newPassword;
		$notifications = [];
		if ($sendEmail) {
			$emailBody = nl2br(html_escape($message));
			$notifications['email'] = sendEmail($user->user_email, 'Playsel giriş bilgileriniz değiştirildi', $emailBody);
		}
		if ($sendSms) {
			$smsResult = sendSms2($user->user_phone, $message);
			$notifications['sms'] = !empty($smsResult['status']);
		}
		foreach ($notifications as $channel => $sent) {
			if (!$sent) {
				log_message('error', 'Admin password reset notification failed: ' . $channel . ', user_id=' . $userID);
			}
		}
		if (in_array(false, $notifications, true)) {
			return json(['status' => 'error', 'message' => 'Şifre değiştirildi ancak seçilen bildirimlerden biri gönderilemedi.'], 502);
		}
		return json(['status' => 'success', 'message' => 'Şifre oluşturuldu ve seçilen bildirimler gönderildi.']);
	}

	private function normalizeDecimalInput($value)
	{
		$value = trim((string)$value);
		if ($value === '') {
			return null;
		}

		$value = str_replace(' ', '', $value);

		if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
			if (strrpos($value, ',') > strrpos($value, '.')) {
				$value = str_replace('.', '', $value);
				$value = str_replace(',', '.', $value);
			} else {
				$value = str_replace(',', '', $value);
			}
		} else {
			$value = str_replace(',', '.', $value);
		}

		if (!is_numeric($value)) {
			return null;
		}

		return round((float)$value, 2);
	}


	public function getOrders()
	{
		$userID = (int)$this->uri->segment(3);
		$statusArray = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];

		$draw = intval($this->input->post("draw"));
		$start = max(0, intval($this->input->post("start")));
		$length = intval($this->input->post("length"));
		$length = ($length > 0 && $length <= 500) ? $length : 20;

		$searchOrderID = intval($this->input->post("order_id"));
		$searchProductID = intval($this->input->post("product_id"));
		$searchCategory = intval($this->input->post("category"));
		$searchStatus = $this->input->post("status");
		$searchStatus = ($searchStatus !== null && $searchStatus !== '' && ctype_digit((string)$searchStatus)) ? (int)$searchStatus : '';
		$statusGroup = (string)$this->input->post("status_group");
		$statusGroup = in_array($statusGroup, ['success', 'failed', 'pending'], true) ? $statusGroup : '';
		$startDate = trim((string)$this->input->post("startDate"));
		$endDate = trim((string)$this->input->post("endDate"));
		$searchUserIDorServer = $this->input->post("searchUserIDorServer");

		$orders = $this->Users_model->get_orders($userID, $start, $length, $searchOrderID, $searchProductID, $searchCategory, $searchStatus, $statusArray, $searchUserIDorServer, $startDate, $endDate, $statusGroup);
		$allOrders = $this->Users_model->get_total_orders($userID, $searchOrderID, $searchProductID, $searchCategory, $searchStatus, $statusArray, $searchUserIDorServer, $startDate, $endDate, $statusGroup);
		$totalOrders = (int)$allOrders->total_orders;

		$statusLabels = [0 => 'Beklemede', 1 => 'Teslim Edildi', 2 => 'İptal Edildi', 3 => 'İşleme Alındı', 4 => 'Satıcı Onayladı', 5 => 'Satıcı Onaylamadı', 9 => 'Cron İşlemi', 11 => 'Ödeme Bekleniyor', 12 => 'Ödeme Reddedildi', 13 => 'Fraud Onayı', 14 => 'Yeni Tedarikçi Bekleniyor'];
		$statusKinds = [1 => 'in', 2 => 'cancel', 5 => 'cancel', 12 => 'cancel'];

		$dt = array();
		foreach ($orders as $order) {
			$requireData = !empty($order->order_require_data) ? json_decode($order->order_require_data, true) : null;
			$cost = currencyCalculator($order->order_cost_currency, $order->order_cost) ?? 0;
			$requireText = '';
			if (is_array($requireData)) {
				$keyMap = ['user_id' => 'UserID', 'server_id' => 'Server', 'zone_id' => 'Zone'];
				foreach ($requireData as $key => $value) {
					if (is_array($value)) {
						$value = json_encode($value, JSON_UNESCAPED_UNICODE);
					}
					$label = $keyMap[$key] ?? (string)$key;
					$requireText .= '<div class="cd-req-line"><span>' . html_escape($label) . '</span><b>' . html_escape((string)$value) . '</b><button type="button" class="cd-copy-btn" data-copy="' . html_escape((string)$value) . '" title="Kopyala" aria-label="Kopyala">⧉</button></div>';
				}
			}
			$productImage = trim((string)$order->product_image);
			if ($productImage !== '' && !preg_match('#^https?://#i', $productImage)) {
				$productImage = rtrim((string)getenv('CDN_URL'), '/') . '/' . ltrim($productImage, '/');
			}
			$thumb = $productImage !== ''
				? '<img src="' . html_escape($productImage) . '" class="cd-order-thumb" loading="lazy" alt="">'
				: '<span class="cd-order-thumb ph">' . html_escape(mb_strtoupper(mb_substr((string)$order->product_name ?: 'S', 0, 1, 'UTF-8'), 'UTF-8')) . '</span>';
			$st = (int)$order->order_status;
			$kind = $statusKinds[$st] ?? 'wait';
			$dt[] = array(
				'<span class="cd-ref">#' . (int)$order->order_id . '</span>',
				'<div class="cd-order-prod">' . $thumb . '<div><small>' . html_escape($order->category_title ?: '—') . '</small><strong>' . html_escape($order->product_name ?: 'Ürün') . '</strong><span>' . (int)$order->order_quantity . ' adet · #' . (int)$order->order_product_id . '</span></div></div>',
				$requireText !== '' ? $requireText : '<span class="cd-muted">—</span>',
				!empty($order->order_sale_price) ? '<span class="cd-money">' . number_format((float)$order->order_sale_price, 2, ',', '.') . ' ' . html_escape($order->order_sale_price_currency ?: 'TL') . '</span>' : '<span class="cd-muted">—</span>',
				'<span class="cd-muted-num">' . number_format((float)$cost, 2, ',', '.') . ' TL</span>',
				html_escape(date("d.m.Y H:i", strtotime($order->order_created_at))),
				'<span class="cd-mv-pill ' . $kind . '">' . html_escape($statusLabels[$st] ?? 'Bilinmiyor') . '</span>',
				'<button type="button" class="cd-table-btn" data-cd-order="' . (int)$order->order_id . '">Sipariş Detayı</button>'
			);
		}

		$legacyHistory = $this->getLegacyHistory($userID);
		$legacyAll = $legacyHistory['orders'];
		$legacyStats = ['total' => count($legacyAll), 'ok' => 0, 'failed' => 0, 'amount' => 0.0];
		foreach ($legacyAll as $lo) {
			$grp = $this->legacyStatusGroup($lo->status_text);
			if ($grp === 'success') {
				$legacyStats['ok']++;
				$legacyStats['amount'] += (float)$lo->total_amount;
			} elseif ($grp === 'failed') {
				$legacyStats['failed']++;
			}
		}

		// Legacy kayıtlar da aynı filtrelerle daraltılır; kategori/ürün filtresi legacy'de uygulanamaz
		$legacyVisible = [];
		if ($start === 0 && empty($searchCategory) && empty($searchProductID) && empty($searchUserIDorServer)) {
			foreach ($legacyAll as $lo) {
				$grp = $this->legacyStatusGroup($lo->status_text);
				if ($statusGroup !== '' && $grp !== $statusGroup) continue;
				if ($searchStatus !== '' && $searchStatus !== 1 && $searchStatus !== 2) continue;
				if ($searchStatus === 1 && $grp !== 'success') continue;
				if ($searchStatus === 2 && $grp !== 'failed') continue;
				if ($searchOrderID > 0 && (string)$lo->source_order_no !== (string)$searchOrderID && (string)$lo->source_order_id !== (string)$searchOrderID) continue;
				$d = substr((string)$lo->order_date, 0, 10);
				if ($startDate !== '' && $d !== '' && $d < $startDate) continue;
				if ($endDate !== '' && $d !== '' && $d > $endDate) continue;
				$legacyVisible[] = $lo;
			}
		}

		if (!empty($legacyVisible)) {
			$legacyRowsByKey = [];
			foreach ($legacyHistory['order_rows'] as $legacyRow) {
				$legacyRowsByKey[$legacyRow->legacy_order_key][] = $legacyRow;
			}
			$legacyData = [];
			foreach ($legacyVisible as $legacyOrder) {
				$rows = $legacyRowsByKey[$legacyOrder->legacy_order_key] ?? [];
				$first = $rows ? ($rows[0]->product_name ?: 'Eski ürün') : ($legacyOrder->product_summary ?: 'Eski ürün bilgisi');
				$more = count($rows) > 1 ? ' +' . (count($rows) - 1) : '';
				$qty = 0;
				foreach ($rows as $r) { $qty += (float)$r->quantity; }
				$grp = $this->legacyStatusGroup($legacyOrder->status_text);
				$legacyData[] = [
					'<span class="cd-ref">#' . html_escape($legacyOrder->source_order_no ?: $legacyOrder->source_order_id) . '</span> <span class="cd-legacy-tag">Legacy</span>',
					'<div class="cd-order-prod"><span class="cd-order-thumb ph">' . html_escape(mb_strtoupper(mb_substr((string)$first, 0, 1, 'UTF-8'), 'UTF-8')) . '</span><div><small>Legacy (eski sistem)</small><strong>' . html_escape($first . $more) . '</strong><span>' . ($qty > 0 ? (0 + $qty) . ' adet' : '—') . '</span></div></div>',
					'<span class="cd-muted">—</span>',
					'<span class="cd-money">' . number_format((float)$legacyOrder->total_amount, 2, ',', '.') . ' ' . html_escape($legacyOrder->currency ?: 'TRY') . '</span>',
					'<span class="cd-muted-num">' . html_escape($legacyOrder->payment_method ?: '—') . '</span>',
					html_escape($legacyOrder->order_date ?: '—'),
					'<span class="cd-mv-pill ' . ($grp === 'success' ? 'in' : ($grp === 'failed' ? 'cancel' : 'wait')) . '">' . html_escape($legacyOrder->status_text ?: 'Legacy') . '</span>',
					'<button type="button" class="cd-table-btn" data-cd-legacy-order="' . html_escape($legacyOrder->legacy_order_key) . '">Sipariş Detayı</button>',
				];
			}
			$dt = array_merge($legacyData, $dt);
			$totalOrders += count($legacyData);
		}

		$totalSalePrice = $allOrders->total_sale_price;
		$stats = $this->Users_model->get_order_stats($userID);
		$output = array(
			"draw" => $draw,
			"recordsTotal" => $totalOrders,
			"recordsFiltered" => $totalOrders,
			"data" => $dt,
			"totalSalePrice" => !empty($totalSalePrice) ? number_format($totalSalePrice, 2, ',', '.') : 0,
			"stats" => [
				'total' => (int)$stats->total + $legacyStats['total'],
				'ok' => (int)$stats->ok + $legacyStats['ok'],
				'failed' => (int)$stats->failed + $legacyStats['failed'],
				'amount' => number_format((float)$stats->amount + $legacyStats['amount'], 2, ',', '.'),
			],
		);
		return json($output);
	}

	private function legacyStatusGroup($text)
	{
		$t = mb_strtolower((string)$text, 'UTF-8');
		if (preg_match('/iptal|red|başarısız|basarisiz|iade|hata/u', $t)) return 'failed';
		if (preg_match('/tamam|teslim|başarılı|basarili|onay/u', $t)) return 'success';
		return 'pending';
	}

	public function legacyOrderDetail()
	{
		$userID = (int)$this->uri->segment(3);
		$key = trim((string)$this->input->get('key', true));
		if ($userID <= 0 || $key === '' || !preg_match('/^[A-Za-z0-9_\-:]{1,100}$/', $key)) {
			return json(['status' => 'error', 'message' => 'Geçersiz istek.'], 422);
		}
		if (!$this->db->table_exists('legacy_orders')) {
			return json(['status' => 'error', 'message' => 'Legacy kayıt bulunamadı.'], 404);
		}

		// Sahiplik: kayıt yalnızca eşleşmiş müşteriye ait olabilir
		$order = $this->db->where('legacy_order_key', $key)->where('target_user_id', $userID)->get('legacy_orders')->row();
		if (!$order) {
			return json(['status' => 'error', 'message' => 'Sipariş bulunamadı.'], 404);
		}

		$rows = $this->db->table_exists('legacy_order_rows')
			? $this->db->where('legacy_order_key', $key)->get('legacy_order_rows')->result()
			: [];

		$payments = [];
		if ($this->db->table_exists('legacy_payments') && !empty($order->source_order_id)) {
			$payments = $this->db->where('target_user_id', $userID)
				->where('source_order_id', $order->source_order_id)
				->order_by('payment_date', 'DESC')->get('legacy_payments')->result();
		}

		$cancellations = [];
		if ($this->db->table_exists('legacy_order_cancellations')) {
			$this->db->where('target_user_id', $userID)->group_start();
			if (!empty($order->source_order_no)) {
				$this->db->where('source_order_no', $order->source_order_no);
			}
			if (!empty($order->source_order_id)) {
				$this->db->or_where('source_order_id', $order->source_order_id);
			}
			$this->db->group_end();
			if (!empty($order->source_order_no) || !empty($order->source_order_id)) {
				$cancellations = $this->db->order_by('refund_date', 'DESC')->get('legacy_order_cancellations')->result();
			}
		}

		$user = $this->db->select('user_name, user_surname, user_email')->where('user_id', $userID)->get('users')->row();

		// Kart ve IP gibi hassas alanlar zaten maskeli saklanır; kart sahibi adı dışarı verilmez.
		$paymentOut = [];
		foreach ($payments as $p) {
			$paymentOut[] = [
				'payment_no' => $p->source_payment_id,
				'date' => $p->payment_date,
				'method' => $p->method_text,
				'masked_card' => $p->masked_card,
				'purpose' => $p->purpose_text,
				'amount' => $p->amount,
				'net_amount' => $p->net_amount,
				'currency' => $p->currency,
				'status' => $p->status_text,
			];
		}
		$rowOut = [];
		foreach ($rows as $r) {
			$rowOut[] = [
				'product' => $r->product_name,
				'type' => $r->product_type,
				'quantity' => $r->quantity,
				'unit_price' => $r->unit_price,
				'line_total' => $r->line_total,
				'currency' => $r->currency,
				'status' => $r->status_text,
				'completion_date' => $r->completion_date,
			];
		}
		$cancelOut = [];
		foreach ($cancellations as $c) {
			$cancelOut[] = [
				'date' => $c->refund_date,
				'amount' => $c->amount,
				'currency' => $c->currency,
				'status' => $c->status_text,
				'description' => $c->description,
			];
		}

		return json([
			'status' => 'success',
			'order' => [
				'order_no' => $order->source_order_no ?: $order->source_order_id,
				'date' => $order->order_date,
				'payment_method' => $order->payment_method,
				'currency' => $order->currency,
				'status' => $order->status_text,
				'total_amount' => $order->total_amount,
				'product_summary' => $order->product_summary,
				'order_ip' => $order->order_ip,
			],
			'rows' => $rowOut,
			'payments' => $paymentOut,
			'cancellations' => $cancelOut,
			'customer' => [
				'name' => $user ? trim($user->user_name . ' ' . $user->user_surname) : '',
				'email' => $user ? $user->user_email : '',
			],
		]);
	}

	public function paymentDetail()
	{
		$userID = (int)$this->uri->segment(3);
		$id = (int)$this->uri->segment(4);
		if ($userID <= 0 || $id <= 0) {
			return json(['status' => 'error', 'message' => 'Geçersiz istek.'], 422);
		}

		// Sahiplik: ödeme yalnızca bu müşterinin kaydıysa döner
		$p = $this->db->select('ub.*, b.bank_name, a.admin_name')
			->from('user_balance ub')
			->join('banks b', 'b.bank_id = ub.user_balance_bank_id', 'left')
			->join('admins a', 'a.admin_id = ub.user_balance_admin_id', 'left')
			->where('ub.user_balance_id', $id)
			->where('ub.user_balance_user_id', $userID)
			->get()->row();
		if (!$p) {
			return json(['status' => 'error', 'message' => 'Ödeme bulunamadı.'], 404);
		}

		$note = json_decode((string)$p->user_balance_note, true);
		$card = (is_array($note) && is_array($note['card'] ?? null)) ? $note['card'] : [];
		$maskedCard = (!empty($card['cardBin']) || !empty($card['cardLast4']))
			? (($card['cardBin'] ?? '') ?: '******') . ' **** ' . (($card['cardLast4'] ?? '') ?: '****')
			: '';
		$noteText = is_array($note) ? (string)($note['message'] ?? '') : (string)$p->user_balance_note;

		$refunded = 0.0;
		$pendingRefund = 0.0;
		if ($this->db->table_exists('payment_refunds')) {
			$refundRows = $this->db->select('refund_status, SUM(refund_amount) AS total', false)
				->where('refund_balance_id', $id)
				->group_by('refund_status')->get('payment_refunds')->result();
			foreach ($refundRows as $r) {
				if ($r->refund_status === 'completed') $refunded = (float)$r->total;
				if ($r->refund_status === 'pending') $pendingRefund = (float)$r->total;
			}
		}

		$statusMap = [0 => 'Beklemede', 1 => 'Onaylandı', 2 => 'Reddedildi', 3 => 'Harcama', 4 => 'İade edildi'];
		$status = (int)$p->user_balance_status;
		$statusText = $statusMap[$status] ?? 'Bilinmiyor';
		if ($status === 1 && $refunded > 0) {
			$statusText = $refunded >= (float)$p->user_balance_amount ? 'İade edildi' : 'Kısmi iade';
		}
		$user = $this->db->select('user_name, user_surname, user_email, user_phone')->where('user_id', $userID)->get('users')->row();

		return json([
			'status' => 'success',
			'payment' => [
				'id' => (int)$p->user_balance_id,
				'method' => $p->bank_name ?: ($maskedCard !== '' ? 'Kart' : 'Manuel (Yönetici)'),
				'status' => $statusText,
				'status_code' => $status,
				'amount' => (float)$p->user_balance_amount,
				'paid_amount' => $status === 1 ? (float)$p->user_balance_amount : 0.0,
				'refunded' => $refunded,
				'pending_refund' => $pendingRefund,
				'reference_no' => $p->user_balance_reference_no,
				'transaction_id' => isset($p->user_balance_vomsis_payment_id) ? $p->user_balance_vomsis_payment_id : '',
				'created_at' => $p->user_balance_created_at,
				'payment_date' => $p->user_balance_payment_date,
				'change_date' => $p->user_balance_change_date,
				'masked_card' => $maskedCard,
				'request_ip' => isset($p->user_balance_request_ip) ? $p->user_balance_request_ip : '',
				'admin' => $p->admin_name,
				'note' => $noteText,
				'refundable' => $status === 1 && ($refunded + $pendingRefund) < (float)$p->user_balance_amount,
				'detail_url' => base_url('balance/paymentDetail/balance/' . (int)$p->user_balance_id),
			],
			'customer' => [
				'name' => $user ? trim($user->user_name . ' ' . $user->user_surname) : '',
				'email' => $user ? $user->user_email : '',
				'phone' => $user ? $user->user_phone : '',
			],
		]);
	}

	public function legacyPaymentDetail()
	{
		$userID = (int)$this->uri->segment(3);
		$key = trim((string)$this->input->get('key', true));
		if ($userID <= 0 || $key === '' || !preg_match('/^[A-Za-z0-9_\-:]{1,100}$/', $key) || !$this->db->table_exists('legacy_payments')) {
			return json(['status' => 'error', 'message' => 'Geçersiz istek.'], 422);
		}

		$p = $this->db->where('legacy_payment_key', $key)->where('target_user_id', $userID)->get('legacy_payments')->row();
		if (!$p) {
			return json(['status' => 'error', 'message' => 'Ödeme bulunamadı.'], 404);
		}

		$refunded = 0.0;
		if ($this->db->table_exists('legacy_order_cancellations') && !empty($p->source_payment_id)) {
			$sum = $this->db->select_sum('amount')
				->where('target_user_id', $userID)
				->where('source_payment_id', $p->source_payment_id)
				->get('legacy_order_cancellations')->row();
			$refunded = (float)($sum->amount ?? 0);
		}

		$user = $this->db->select('user_name, user_surname, user_email, user_phone')->where('user_id', $userID)->get('users')->row();

		return json([
			'status' => 'success',
			'payment' => [
				'payment_no' => $p->source_payment_id,
				'order_no' => $p->source_order_id,
				'date' => $p->payment_date,
				'method' => $p->method_text,
				'masked_card' => $p->masked_card,
				'purpose' => $p->purpose_text,
				'amount' => $p->amount !== null ? (float)$p->amount : null,
				'net_amount' => $p->net_amount !== null ? (float)$p->net_amount : null,
				'currency' => $p->currency ?: 'TRY',
				'status' => $p->status_text,
				'ip' => $p->payment_ip,
				'refunded' => $refunded,
			],
			'customer' => [
				'name' => $user ? trim($user->user_name . ' ' . $user->user_surname) : '',
				'email' => $user ? $user->user_email : '',
				'phone' => $user ? $user->user_phone : '',
			],
		]);
	}

	public function currency($currency)
	{
		$currency = $currency;
		$trCurrency = tr_strtolower($currency);
		$currencies = $this->db->select('currencies_json')
			->from('currency')
			->where('currencies_date', date('Y-m-d'))
			->get()->row()
			->currencies_json;
		$currencies = json_decode($currencies);
		if (!empty($currencies->$trCurrency)) {
			return $currencies->$trCurrency;
		} else {
			return 0;
		}
		// $cost = 12123; // Ürün fiyatı gibi bir değer
		// $cost = $cost / $currencies->$trCurrency;
		// $cost = number_format($cost, 2, '.', '');
		// return $cost;
	}

	public function userBalance($userID)
	{
		// 0: Beklemede, 1: Onay, 2: İptal, 3: Harcama, 4: Harcama İptal
		$result = $this->db
			->select("
            SUM(CASE WHEN user_balance_status = 1 THEN user_balance_amount ELSE 0 END) AS added,
            SUM(CASE WHEN user_balance_status = 3 THEN user_balance_amount ELSE 0 END) AS spent,
            SUM(CASE WHEN user_balance_status = 4 THEN user_balance_amount ELSE 0 END) AS declined
        ")
			->from('user_balance')
			->where('user_balance_user_id', $userID)
			->get()
			->row();

		$added   = (float) $result->added;
		$spent   = (float) $result->spent;
		$declined = (float) $result->declined;

		return [
			'balanceTotal'      => $added,
			'spent'        => $spent,
			'userBalance' => $added - $spent,
			'declined'     => $declined,
		];
	}

	public function lastPayments($userID)
	{
		$banks = $this->db->from('banks')->get()->result();
		$refno = generateRandomString(16, 'ref');
		$bankInfo = [];
		foreach ($banks as $bank) {
			$bankInfo[$bank->bank_id] = [
				'name' => $bank->bank_name,
				'iban' => $bank->bank_iban,
				'account' => $bank->bank_account_number,
				'branch' => $bank->bank_branch_code,
				'holder' => $bank->bank_owner,
				'ref' => $refno,
				'qr' => ''
			];
		}
		$array['bankInfo'] = json_encode($bankInfo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$statusArray = [
			0 => ['text' => 'Beklemede', 'color' => 'warning'],
			1 => ['text' => 'Onaylandı', 'color' => 'success'],
			2 => ['text' => 'Reddedildi', 'color' => 'danger'],
		];
		$bankArray = [
			1 => ['text' => 'Kuveyt Türk', 'color' => 'success'],
			2 => ['text' => 'Ziraat Bankası', 'color' => 'success'],
			3 => ['text' => 'Albaraka', 'color' => 'success'],
			4 => ['text' => 'Kredi', 'color' => 'info'],
			5 => ['text' => 'Ödenmiş Kredi', 'color' => 'success'],
			6 => ['text' => 'Akbank', 'color' => 'success'],
			7 => ['text' => 'İş Bankası', 'color' => 'success'],
			8 => ['text' => 'Türkiye Finans', 'color' => 'success'],
			9 => ['text' => 'Vakıfbank', 'color' => 'success'],
		];

		$array['lastPayments'] = array_map(
			function ($item) use ($statusArray, $bankArray) {
				return (object)[
					...get_object_vars($item),
					'user_balance_amount'        => number_format((float)$item->user_balance_amount, 2, '.', ''),
					'user_balance_payment_date'  => !empty($item->user_balance_payment_date) ? date('d.m.Y', strtotime($item->user_balance_payment_date)) : '-',
					'statusText'                 => $statusArray[$item->user_balance_status]['text'] ?? 'Bilinmiyor',
					'statusColor'                => $statusArray[$item->user_balance_status]['color'] ?? 'secondary',
					'bankText'                   => $bankArray[$item->user_balance_bank_id]['text'] ?? null,
					'bankColor'                  => $bankArray[$item->user_balance_bank_id]['color'] ?? null,
				];
			},
			$this->db
				->select('
					user_balance.*,
					a1.admin_name  as create_admin_name,
					a1.admin_email as create_admin_email,
					a2.admin_name  as approve_admin_name,
					a2.admin_email as approve_admin_email
				')
				->from('user_balance')
				->join('admins as a1', 'a1.admin_id = user_balance.user_balance_admin_id', 'left')
				->join('admins as a2', 'a2.admin_id = user_balance.user_balance_credit_approve_admin_id', 'left')
				->where('user_balance.user_balance_user_id', $userID)
				->where_in('user_balance.user_balance_status', [0, 1, 2])
				->order_by('user_balance.user_balance_id', 'desc')
				->limit(200)
				->get()
				->result()
		);

		return $array['lastPayments'];
	}

	public function getUserSummaryStats($userID)
	{
		$userID = (int)$userID;

		$orderCount = (int)$this->db->from('orders')
			->where('order_reseller_id', $userID)
			->count_all_results();

		$paidCount = (int)$this->db->from('balance')
			->where('balance_user_id', $userID)
			->where('balance_status', 1)
			->count_all_results();

		// --- Notes count: each line = 1 note ---
		$notesRow = $this->db->select('user_notes')
			->from('users')
			->where('user_id', $userID)
			->get()
			->row();

		$notesRaw = $notesRow ? (string)$notesRow->user_notes : '';
		$notesRaw = trim($notesRaw);

		if ($notesRaw === '') {
			$noteCount = 0;
		} else {
			$lines = preg_split("/\r\n|\n|\r/", $notesRaw);
			$lines = array_map('trim', $lines);
			$lines = array_filter($lines, function ($l) {
				return $l !== '';
			});
			$noteCount = count($lines);
		}

		return [
			'orderCount' => $orderCount,
			'paidCount'  => $paidCount,
			'noteCount'  => $noteCount,
		];
	}

	public function getUserLogs()
	{
		$userID = (int)$this->uri->segment(3);

		$draw = intval($this->input->post("draw"));
		$start = intval($this->input->post("start"));
		$length = intval($this->input->post("length"));

		$event = trim((string)$this->input->post("event")); // LGN / LGOUT / VIEW
		$ip    = trim((string)$this->input->post("ip"));
		$page  = trim((string)$this->input->post("page"));

		// total
		$total = (int)$this->db->from('user_logs')
			->where('user_logs_user_id', $userID)
			->count_all_results();

		// filtered query
		$this->db->from('user_logs')
			->where('user_logs_user_id', $userID);

		if ($event !== '') {
			$this->db->where('user_logs_user_event', $event);
		}
		if ($ip !== '') {
			$this->db->like('user_logs_user_ip', $ip);
		}
		if ($page !== '') {
			$this->db->like('user_logs_user_page', $page);
		}

		// filtered count
		$filtered = (int)$this->db->count_all_results('', false); // aynı query'yi bozmadan count

		// data
		$logs = $this->db
			->order_by('user_logs_id', 'desc')
			->limit($length, $start)
			->get()
			->result();

		$dt = [];
		foreach ($logs as $i => $log) {
			$eventBadge = 'badge badge-light-secondary';
			$eventText = $log->user_logs_user_event;

			if ($log->user_logs_user_event === 'LOGIN') {
				$eventBadge = 'badge badge-light-success';
				$eventText = 'LOGIN';
			} elseif ($log->user_logs_user_event === 'LOGOUT') {
				$eventBadge = 'badge badge-light-danger';
				$eventText = 'LOGOUT';
			} elseif ($log->user_logs_user_event === 'VIEW') {
				$eventBadge = 'badge badge-light-primary';
				$eventText = 'VIEW';
			} else if ($log->user_logs_user_event === 'PASS-CHANGE') {
				$eventBadge = 'badge badge-light-info';
				$eventText = 'PASS-CHANGE';
			} else if ($log->user_logs_user_event === 'REGISTER') {
				$eventBadge = 'badge badge-light-danger';
				$eventText = 'REGISTER';
			} else if ($log->user_logs_user_event === 'FORGOT-PASS') {
				$eventBadge = 'badge badge-light-warning';
				$eventText = 'FORGOT-PASS';
			} else if ($log->user_logs_user_event === 'FAILED-LOGIN') {
				$eventBadge = 'badge badge-light-danger';
				$eventText = 'FAILED-LOGIN';
			} else if ($log->user_logs_user_event === 'ORDER') {
				$eventBadge = 'badge badge-light-info';
				$eventText = 'ORDER';
			}

			$dt[] = [
				($start + $i + 1),
				date("d.m.Y H:i:s", strtotime($log->user_logs_created_at)),
				'<span class="' . $eventBadge . '">' . $eventText . '</span>',
				!empty($log->user_logs_user_page) ? html_escape($log->user_logs_user_page) : '-',
				!empty($log->user_logs_user_country) ? html_escape($log->user_logs_user_country) : '-',
				!empty($log->user_logs_user_ip) ? html_escape($log->user_logs_user_ip) : '-',
				!empty($log->user_logs_user_agent) ? '<span class="text-gray-600">' . html_escape($log->user_logs_user_agent) . '</span>' : '-',
			];
		}

		return json([
			"draw" => $draw,
			"recordsTotal" => $total,
			"recordsFiltered" => $filtered,
			"data" => $dt,
		]);
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
			// 'user_updated_at' => date('Y-m-d H:i:s'),
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

	public function approve_credit()
	{
		$adminID = $this->session->userdata('admin')['admin_id'];
		$balanceID = (int)$this->uri->segment(3);
		$getBalance = $this->db->where('user_balance_id', $balanceID)->get('user_balance')->row();

		// user_balance_bank_id 4 kredi, user_balance_bank_id =5 ödenmiş kredi
		//
		if ($getBalance->user_balance_bank_id != 4) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Bu işlem sadece kredi talepleri için geçerlidir.']);
			redirect($_SERVER['HTTP_REFERER']);
		} else {
			$this->db->where('user_balance_id', $balanceID)->update('user_balance', [
				'user_balance_bank_id' => 5,
				'user_balance_credit_approve_admin_id' => $adminID,
				'user_balance_credit_approve_date' => date('Y-m-d H:i:s'),
			]);
			$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Başarılı!', 'message' => 'Kredi ödemesi onaylandı.']);
			redirect($_SERVER['HTTP_REFERER']);
		}
	}

	public function updateUserCategory()
	{
		$post = $this->input->post();
		$userID = (int)$post['user_id'];
		$categoryID = (int)$post['category_id'];
		$assigned = (int)$post['access']; // 1 ise ekle, 0 ise çıkar
		// Önce kullanıcı ve kategori var mı kontrolü
		$user = $this->db->where('user_id', $userID)->get('users')->row();
		$category = $this->db->where('category_id', $categoryID)->get('categories')->row();
		if (empty($user) || empty($category)) {
			return json(['status' => 'error', 'message' => 'Kullanıcı veya kategori bulunamadı.']);
		}
		if ($assigned === 1) {
			// Ekle
			$exists = $this->db->where('user_categories_user_id', $userID)
				->where('user_categories_category_id', $categoryID)
				->get('user_categories')
				->row();
			if (empty($exists)) {
				$this->db->insert('user_categories', [
					'user_categories_user_id' => $userID,
					'user_categories_category_id' => $categoryID,
				]);
			}
		} else {
			// Çıkar
			$this->db->where('user_categories_user_id', $userID)
				->where('user_categories_category_id', $categoryID)
				->delete('user_categories');
		}
		return json(['status' => 'success', 'message' => 'Kullanıcı kategorisi güncellendi.']);
	}

	public function updateUserCategoryCredit()
	{
		$post = $this->input->post();
		$uid = (int)$post['user_id'];
		$catid = (int)$post['category_id'];
		$credit_access = (int)$post['credit_access']; // 1 ise ekle, 0 ise çıkar
		$row = $this->db->where('user_categories_user_id', $uid)
			->where('user_categories_category_id', $catid)
			->get('user_categories')
			->row();
		if (!empty($row)) {
			$this->db->where('user_categories_id', $row->user_categories_id)
				->update('user_categories', [
					'user_categories_credit_enable' => $credit_access,
				]);
		} else {
			return json(['status' => 'error', 'message' => 'Kullanıcı kategorisini önce aktif edin.']);
		}
		return json([
			'status' => 'success',
			'message' => 'Kullanıcı kategori/kredi durumu güncellendi.',
			'data' => $post
		]);
	}

	public function invoiceDetail($uuid)
	{
		// Segment'ten alamazsa parametre olarak geleni kullanması için ufak bir fallback


		$uuid = $this->uri->segment(3);
		if (empty($uuid)) {
			show_error('Geçersiz fatura numarası.');
			return;
		}
		$this->load->library('birfatura');
		$birFatura = new Birfatura();
		// API'den PDF linkini çek
		$result = $birFatura->get_pdf_link_by_uuid($uuid, 'EFATURA');
		// Eğer sonuç geçerli bir URL ise doğrudan o adrese yönlendir
		if (!empty($result) && filter_var($result, FILTER_VALIDATE_URL)) {
			redirect($result);
		} else {
			// Eğer link gelmediyse veya API hata verdiyse kullanıcıya bilgi ver
			//sekmeyi kapat
			echo '<script>window.close();</script>';
		}
	}

	public function updateBillingInfo($id)
	{
		// Yalnızca yetkili (Admin) kontrolü burada varsa ekleyin

		$post = $this->input->post();

		if (empty($post)) {
			echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek.']);
			return;
		}

		// Verileri temizleyip alıyoruz
		$updateData = [
			'user_bill_company_name' => trim($post['user_bill_company_name'] ?? ''),
			'user_bill_city'         => trim($post['user_bill_city'] ?? ''),
			'user_bill_district'     => trim($post['user_bill_district'] ?? ''),
			'user_bill_address'      => trim($post['user_bill_address'] ?? ''),
			'user_bill_tax_office'   => trim($post['user_bill_tax_office'] ?? ''),
			'user_bill_tax_number'   => trim($post['user_bill_tax_number'] ?? ''),
			'user_bill_e_invoice'    => isset($post['user_bill_e_invoice']) ? (int)$post['user_bill_e_invoice'] : 0,
			'user_is_turkish_company' => isset($post['user_is_turkish_company']) ? (int)$post['user_is_turkish_company'] : 0,

		];

		// Veritabanını güncelliyoruz
		$this->db->where('user_id', (int)$id)->update('users', $updateData);

		// always returns true if query succeeds, affected_rows() is 0 if no changes made
		if ($this->db->affected_rows() >= 0) {
			echo json_encode(['status' => 'success', 'message' => 'Fatura bilgileri başarıyla güncellendi.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Veritabanı güncellenirken bir hata oluştu.']);
		}
	}

	public function getDistricts()
	{
		$city = $this->input->post('city', true);
		$districts = [];

		if ($city) {
			$citiesFilePath = dirname(FCPATH) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cities.json';

			if (file_exists($citiesFilePath)) {
				$citiesJson = file_get_contents($citiesFilePath);
				$citiesArray = json_decode($citiesJson, true);

				if (!empty($citiesArray)) {
					foreach ($citiesArray as $item) {
						if ($item['il'] === $city) {
							$districts[] = $item['ilce'];
						}
					}
				}
			}
		}

		sort($districts);

		echo json_encode(['status' => 'success', 'data' => $districts]);
		exit;
	}

	public function saveUserAddress($userID)
	{
		$userID = (int)$userID;
		$user = $this->db->where('user_id', $userID)->get('users')->row();
		if (!$user) {
			json(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.'], 404);
		}

		$post = $this->input->post();
		if (empty($post)) {
			json(['status' => 'error', 'message' => 'Geçersiz veri.'], 400);
		}

		$addressID = (int)($post['user_addresses_id'] ?? 0);
		$addressTitle = strip_tags(trim($post['user_addresses_title'] ?? ''));
		$invoiceType = (int)($post['user_addresses_invoice_type'] ?? 1) === 2 ? 2 : 1;
		$isTurkishCitizen = isset($post['user_addresses_is_turkish_citizen']) ? (int)$post['user_addresses_is_turkish_citizen'] : 1;
		$isTurkishCompany = isset($post['user_addresses_is_turkish_company']) ? (int)$post['user_addresses_is_turkish_company'] : 1;
		$isEInvoiceTaxpayer = isset($post['user_addresses_is_e_invoice_taxpayer']) ? (int)$post['user_addresses_is_e_invoice_taxpayer'] : 0;
		$fullName = strip_tags(trim($post['user_addresses_full_name'] ?? ''));
		$companyName = strip_tags(trim($post['user_addresses_company_name'] ?? ''));
		$taxOffice = strip_tags(trim($post['user_addresses_tax_office'] ?? ''));
		$taxNumber = strip_tags(trim($post['user_addresses_tax_number'] ?? ''));
		$phone = strip_tags(trim($post['user_addresses_phone'] ?? $user->user_phone ?? ''));
		$country = strip_tags(trim($post['user_addresses_country'] ?? 'Türkiye'));
		$city = strip_tags(trim($post['user_addresses_city'] ?? ''));
		$district = strip_tags(trim($post['user_addresses_district'] ?? ''));
		$postalCode = strip_tags(trim($post['user_addresses_postal_code'] ?? ''));
		$address = strip_tags(trim($post['user_addresses_address'] ?? ''));
		$isDefault = !empty($post['user_addresses_is_default']) ? 1 : 0;

		if (empty($addressTitle)) {
			$addressTitle = ($invoiceType === 2) ? 'Şirket Fatura Adresi' : 'Bireysel Fatura Adresi';
		}

		$activeCount = $this->db->where([
			'user_addresses_user_id' => $userID,
			'user_addresses_is_deleted' => 0
		])->count_all_results('user_addresses');

		if ($activeCount === 0) {
			$isDefault = 1;
		}

		if ($isDefault === 1) {
			$this->db->where('user_addresses_user_id', $userID)->update('user_addresses', ['user_addresses_is_default' => 0]);
		}

		$addressData = [
			'user_addresses_user_id'               => $userID,
			'user_addresses_title'                 => $addressTitle,
			'user_addresses_invoice_type'          => $invoiceType,
			'user_addresses_full_name'             => ($invoiceType === 1) ? $fullName : '',
			'user_addresses_company_name'          => ($invoiceType === 2) ? $companyName : '',
			'user_addresses_phone'                 => $phone,
			'user_addresses_tax_number'            => $taxNumber,
			'user_addresses_tax_office'            => ($invoiceType === 2) ? $taxOffice : '',
			'user_addresses_is_turkish_citizen'    => ($invoiceType === 1) ? $isTurkishCitizen : 1,
			'user_addresses_is_turkish_company'    => ($invoiceType === 2) ? $isTurkishCompany : 1,
			'user_addresses_is_e_invoice_taxpayer' => ($invoiceType === 2 && $isTurkishCompany === 1) ? $isEInvoiceTaxpayer : 0,
			'user_addresses_country'               => $country,
			'user_addresses_city'                  => $city,
			'user_addresses_district'              => $district,
			'user_addresses_postal_code'           => $postalCode,
			'user_addresses_address'               => $address,
			'user_addresses_is_default'            => $isDefault,
			'user_addresses_is_deleted'            => 0,
			'user_addresses_updated_at'            => date('Y-m-d H:i:s'),
		];

		if ($addressID > 0) {
			$existing = $this->db->where([
				'user_addresses_id' => $addressID,
				'user_addresses_user_id' => $userID,
				'user_addresses_is_deleted' => 0
			])->get('user_addresses')->row();

			if ($existing) {
				if ($isDefault === 0) {
					$addressData['user_addresses_is_default'] = $existing->user_addresses_is_default;
				}
				$this->db->where('user_addresses_id', $addressID)->update('user_addresses', $addressData);
				$savedMessage = 'Adres başarıyla güncellendi.';
			} else {
				$addressData['user_addresses_created_at'] = date('Y-m-d H:i:s');
				$this->db->insert('user_addresses', $addressData);
				$savedMessage = 'Yeni adres kaydedildi.';
			}
		} else {
			$addressData['user_addresses_created_at'] = date('Y-m-d H:i:s');
			$this->db->insert('user_addresses', $addressData);
			$savedMessage = 'Yeni adres başarıyla eklendi.';
		}

		if ($addressData['user_addresses_is_default'] == 1) {
			$userUpdate = [
				'user_bill_company_name'  => ($invoiceType === 2) ? $companyName : $fullName,
				'user_bill_tax_office'    => ($invoiceType === 2) ? $taxOffice : '',
				'user_bill_tax_number'    => $taxNumber,
				'user_bill_city'          => $city,
				'user_bill_district'      => $district,
				'user_bill_address'       => $address,
				'user_bill_e_invoice'     => ($invoiceType === 2 && $isTurkishCompany === 1) ? $isEInvoiceTaxpayer : 0,
				'user_is_turkish_company' => ($invoiceType === 2 && $isTurkishCompany === 1) ? 1 : 0
			];
			$this->db->where('user_id', $userID)->update('users', $userUpdate);
		}

		json(['status' => 'success', 'message' => $savedMessage]);
	}

	public function setDefaultUserAddress($userID, $addressID)
	{
		$userID = (int)$userID;
		$addressID = (int)$addressID;
		$target = $this->db->where([
			'user_addresses_id' => $addressID,
			'user_addresses_user_id' => $userID,
			'user_addresses_is_deleted' => 0
		])->get('user_addresses')->row();

		if ($target) {
			$this->db->where('user_addresses_user_id', $userID)->update('user_addresses', ['user_addresses_is_default' => 0]);
			$this->db->where('user_addresses_id', $addressID)->update('user_addresses', [
				'user_addresses_is_default' => 1,
				'user_addresses_updated_at' => date('Y-m-d H:i:s')
			]);

			$userUpdate = [
				'user_bill_company_name'  => ($target->user_addresses_invoice_type == 2) ? $target->user_addresses_company_name : $target->user_addresses_full_name,
				'user_bill_tax_office'    => ($target->user_addresses_invoice_type == 2) ? $target->user_addresses_tax_office : '',
				'user_bill_tax_number'    => $target->user_addresses_tax_number,
				'user_bill_city'          => $target->user_addresses_city,
				'user_bill_district'      => $target->user_addresses_district,
				'user_bill_address'       => $target->user_addresses_address,
				'user_bill_e_invoice'     => $target->user_addresses_is_e_invoice_taxpayer,
				'user_is_turkish_company' => $target->user_addresses_is_turkish_company
			];
			$this->db->where('user_id', $userID)->update('users', $userUpdate);

			json(['status' => 'success', 'message' => 'Varsayılan fatura adresi güncellendi.']);
		} else {
			json(['status' => 'error', 'message' => 'Adres bulunamadı.'], 404);
		}
	}

	public function deleteUserAddress($userID, $addressID)
	{
		$userID = (int)$userID;
		$addressID = (int)$addressID;
		$target = $this->db->where([
			'user_addresses_id' => $addressID,
			'user_addresses_user_id' => $userID,
			'user_addresses_is_deleted' => 0
		])->get('user_addresses')->row();

		if ($target) {
			$this->db->where('user_addresses_id', $addressID)->update('user_addresses', [
				'user_addresses_is_deleted' => 1,
				'user_addresses_is_default' => 0,
				'user_addresses_updated_at' => date('Y-m-d H:i:s')
			]);

			if ($target->user_addresses_is_default == 1) {
				$next = $this->db->where([
					'user_addresses_user_id' => $userID,
					'user_addresses_is_deleted' => 0
				])->order_by('user_addresses_id', 'DESC')->get('user_addresses')->row();

				if ($next) {
					$this->db->where('user_addresses_id', $next->user_addresses_id)->update('user_addresses', ['user_addresses_is_default' => 1]);
					$userUpdate = [
						'user_bill_company_name'  => ($next->user_addresses_invoice_type == 2) ? $next->user_addresses_company_name : $next->user_addresses_full_name,
						'user_bill_tax_office'    => ($next->user_addresses_invoice_type == 2) ? $next->user_addresses_tax_office : '',
						'user_bill_tax_number'    => $next->user_addresses_tax_number,
						'user_bill_city'          => $next->user_addresses_city,
						'user_bill_district'      => $next->user_addresses_district,
						'user_bill_address'       => $next->user_addresses_address,
						'user_bill_e_invoice'     => $next->user_addresses_is_e_invoice_taxpayer,
						'user_is_turkish_company' => $next->user_addresses_is_turkish_company
					];
					$this->db->where('user_id', $userID)->update('users', $userUpdate);
				}
			}

			json(['status' => 'success', 'message' => 'Adres silindi.']);
		} else {
			json(['status' => 'error', 'message' => 'Adres bulunamadı.'], 404);
		}
	}

	public function streamers()
	{
		$data['title'] = "Yayıncı Yönetimi";
		$data['pageName'] = "Yayıncı Yönetimi";
		$this->template->content->view('streamers', $data);
		$this->template->publish();
	}

	public function streamersList()
	{
		$postData = $this->input->post();
		$start = isset($postData['start']) ? (int)$postData['start'] : 0;
		$length = isset($postData['length']) ? (int)$postData['length'] : 10;

		$this->db->select('user_id, user_nickname, user_streamer_nick, user_streamer_url, user_streamer_status, user_email');
		$this->db->from('users');
		$this->db->where('user_streamer_status IS NOT NULL', null, false);
		$this->db->where('user_streamer_status !=', null); // Fallback to avoid query error

		$tempDb = clone $this->db;
		$totalRecords = $tempDb->count_all_results();

		$this->db->order_by('user_streamer_status', 'DESC'); // 2 pending, 1 active, 0 rejected
		$this->db->order_by('user_id', 'DESC');

		if ($length != -1) {
			$this->db->limit($length, $start);
		}

		$streamers = $this->db->get()->result();

		echo json_encode([
			"draw" => isset($postData['draw']) ? (int)$postData['draw'] : 1,
			"recordsTotal" => $totalRecords,
			"recordsFiltered" => $totalRecords,
			"data" => $streamers
		]);
	}

	public function updateStreamerStatus()
	{
		$user_id = $this->input->post('user_id');
		$status = $this->input->post('status');

		if (!$user_id || $status === null) {
			echo json_encode(['status' => false, 'message' => 'Geçersiz parametreler.']);
			return;
		}

		$this->db->where('user_id', $user_id);
		$this->db->update('users', ['user_streamer_status' => $status]);

		echo json_encode(['status' => true, 'message' => 'Durum başarıyla güncellendi.']);
	}

	public function deleteBankAcc($id)
	{
		$bankAcc = $this->db->get_where('user_bank_accounts', ['id' => $id])->row();
		if ($bankAcc) {
			$this->db->where('id', $id);
			$this->db->delete('user_bank_accounts');
			$this->session->set_flashdata('alert', ['type' => 'success', 'message' => 'Banka hesabı silindi.']);
			redirect($_SERVER['HTTP_REFERER'] ?? base_url('users/detail/' . $bankAcc->user_id . '#kt_user_billing_info'));
		} else {
			redirect($_SERVER['HTTP_REFERER'] ?? base_url('users'));
		}
	}

	/* ---------- Referans raporu ---------- */

	public function referralReport()
	{
		$data = $this->data;
		$data['referralDefaults'] = ['date_from' => '', 'date_to' => ''];
		$this->template->content->view('referral_report', $data);
		$this->template->publish();
	}

	public function referralReportDetail($referrerID = 0)
	{
		$referrerID = (int)$referrerID;
		$referrer = $referrerID > 0 ? $this->db->select('user_id, user_name, user_surname, user_nickname, user_email, user_created_at')
			->where('user_id', $referrerID)->get('users')->row() : null;
		if (!$referrer) {
			show_404();
		}
		$data = $this->data;
		$data['referrer'] = $referrer;
		$this->template->content->view('referral_report_detail', $data);
		$this->template->publish();
	}

	private function referralFilters()
	{
		$from = trim((string)$this->input->post_get('date_from'));
		$to = trim((string)$this->input->post_get('date_to'));
		return [
			'date_from' => validateDate($from, 'Y-m-d') ? $from : '',
			'date_to' => validateDate($to, 'Y-m-d') ? $to : '',
			'search' => mb_substr(trim((string)$this->input->post_get('search_text')), 0, 100),
			'min_referred' => max(0, (int)$this->input->post_get('min_referred')),
			'only_active' => $this->input->post_get('only_active') === '1',
		];
	}

	private function referralTryCurrencies()
	{
		return "('', 'TRY', 'TL')";
	}

	// Müşteri başına canlı (yeni sistem) ve legacy metrikleri; legacy toplamlar tarih filtresinden bağımsızdır.
	private function referralCustomerSql($f, $referrerID = 0)
	{
		$orderDate = '';
		$balanceDate = '';
		if ($f['date_from'] !== '') {
			$orderDate .= ' AND order_created_at >= ' . $this->db->escape($f['date_from'] . ' 00:00:00');
			$balanceDate .= ' AND user_balance_created_at >= ' . $this->db->escape($f['date_from'] . ' 00:00:00');
		}
		if ($f['date_to'] !== '') {
			$orderDate .= ' AND order_created_at <= ' . $this->db->escape($f['date_to'] . ' 23:59:59');
			$balanceDate .= ' AND user_balance_created_at <= ' . $this->db->escape($f['date_to'] . ' 23:59:59');
		}
		$referred = 'SELECT user_id FROM users WHERE user_reference_id > 0';
		$try = $this->referralTryCurrencies();

		$orders = "SELECT uid, COUNT(*) AS orders, SUM(rev) AS revenue,
				SUM(CASE WHEN cst > 0 THEN cst ELSE 0 END) AS cost,
				SUM(CASE WHEN cst > 0 THEN rev ELSE 0 END) AS costed_revenue,
				SUM(CASE WHEN cst > 0 THEN 0 ELSE 1 END) AS missing_cost,
				MAX(order_created_at) AS last_order
			FROM (
				SELECT order_reseller_id AS uid, order_created_at,
					COALESCE(NULLIF(order_sale_price_tl, 0), CASE WHEN UPPER(COALESCE(order_sale_price_currency, '')) IN $try THEN order_sale_price END, 0) AS rev,
					COALESCE(NULLIF(order_cost_tl, 0), CASE WHEN UPPER(COALESCE(order_cost_currency, '')) IN $try THEN order_cost END, 0) AS cst
				FROM orders
				WHERE order_status = 1 AND order_reseller_id IN ($referred) $orderDate
			) t GROUP BY uid";

		$balance = "SELECT user_balance_user_id AS uid,
				SUM(CASE WHEN user_balance_status = 1 THEN user_balance_amount ELSE 0 END) AS deposits,
				SUM(CASE WHEN user_balance_status = 3 THEN user_balance_amount ELSE 0 END) AS spend
			FROM user_balance
			WHERE user_balance_status IN (1, 3) AND user_balance_user_id IN ($referred) $balanceDate
			GROUP BY user_balance_user_id";

		$legacyOrders = $this->db->table_exists('legacy_orders')
			? "SELECT target_user_id AS uid, COUNT(*) AS cnt,
					SUM(CASE WHEN UPPER(COALESCE(currency, '')) IN $try THEN total_amount ELSE 0 END) AS amt
				FROM legacy_orders WHERE target_user_id IN ($referred) GROUP BY target_user_id"
			: 'SELECT NULL AS uid, 0 AS cnt, 0 AS amt FROM DUAL WHERE 1 = 0';
		$legacyPayments = $this->db->table_exists('legacy_payments')
			? "SELECT target_user_id AS uid, COUNT(*) AS cnt,
					SUM(CASE WHEN UPPER(COALESCE(currency, '')) IN $try THEN amount ELSE 0 END) AS amt
				FROM legacy_payments WHERE target_user_id IN ($referred) GROUP BY target_user_id"
			: 'SELECT NULL AS uid, 0 AS cnt, 0 AS amt FROM DUAL WHERE 1 = 0';

		$where = 'c.user_reference_id > 0';
		if ($referrerID > 0) {
			$where .= ' AND c.user_reference_id = ' . (int)$referrerID;
		}

		return "SELECT c.user_id, c.user_reference_id AS referrer_id, c.user_name, c.user_surname, c.user_nickname,
				c.user_email, c.user_created_at,
				COALESCE(o.orders, 0) AS orders, COALESCE(o.revenue, 0) AS revenue, COALESCE(o.cost, 0) AS cost,
				COALESCE(o.costed_revenue, 0) AS costed_revenue, COALESCE(o.missing_cost, 0) AS missing_cost, o.last_order,
				COALESCE(b.deposits, 0) AS deposits, COALESCE(b.spend, 0) AS spend,
				COALESCE(lo.cnt, 0) AS legacy_orders, COALESCE(lo.amt, 0) AS legacy_order_amount,
				COALESCE(lp.cnt, 0) AS legacy_payments, COALESCE(lp.amt, 0) AS legacy_payment_amount
			FROM users c
			LEFT JOIN ($orders) o ON o.uid = c.user_id
			LEFT JOIN ($balance) b ON b.uid = c.user_id
			LEFT JOIN ($legacyOrders) lo ON lo.uid = c.user_id
			LEFT JOIN ($legacyPayments) lp ON lp.uid = c.user_id
			WHERE $where";
	}

	private function referralReferrerSql($f)
	{
		$where = '1 = 1';
		if ($f['search'] !== '') {
			$like = $this->db->escape('%' . $this->db->escape_like_str($f['search']) . '%');
			$where .= " AND (r.user_name LIKE $like OR r.user_surname LIKE $like OR r.user_nickname LIKE $like OR r.user_email LIKE $like OR CONCAT_WS(' ', r.user_name, r.user_surname) LIKE $like)";
		}
		$having = [];
		if ($f['min_referred'] > 0) {
			$having[] = 'referred >= ' . (int)$f['min_referred'];
		}
		if ($f['only_active']) {
			$having[] = 'orders > 0';
		}

		return 'SELECT r.user_id, r.user_name, r.user_surname, r.user_nickname, r.user_email,
				COUNT(m.user_id) AS referred, SUM(CASE WHEN m.orders > 0 THEN 1 ELSE 0 END) AS active_customers,
				SUM(m.orders) AS orders, SUM(m.revenue) AS revenue, SUM(m.cost) AS cost,
				SUM(m.costed_revenue) - SUM(m.cost) AS profit, SUM(m.costed_revenue) AS costed_revenue,
				SUM(m.missing_cost) AS missing_cost, SUM(m.deposits) AS deposits, SUM(m.spend) AS spend,
				SUM(m.legacy_orders) AS legacy_orders, SUM(m.legacy_order_amount) AS legacy_order_amount,
				SUM(m.legacy_payments) AS legacy_payments, SUM(m.legacy_payment_amount) AS legacy_payment_amount
			FROM (' . $this->referralCustomerSql($f) . ') m
			JOIN users r ON r.user_id = m.referrer_id
			WHERE ' . $where . '
			GROUP BY r.user_id, r.user_name, r.user_surname, r.user_nickname, r.user_email'
			. ($having ? ' HAVING ' . implode(' AND ', $having) : '');
	}

	private function referralMoney($value)
	{
		return number_format((float)$value, 2, ',', '.') . ' ₺';
	}

	private function referralPerson($row, $idKey = 'user_id')
	{
		$name = trim(($row->user_name ?? '') . ' ' . ($row->user_surname ?? ''));
		if ($name === '') {
			$name = $row->user_nickname ?: ('#' . $row->{$idKey});
		}
		return html_escape($name);
	}

	private function referralMargin($profit, $costedRevenue)
	{
		return (float)$costedRevenue > 0 ? number_format(((float)$profit / (float)$costedRevenue) * 100, 1, ',', '.') . '%' : '-';
	}

	private function referralOrder($map, $defaultKey)
	{
		$column = $map[$defaultKey];
		$dir = 'DESC';
		$order = $this->input->post('order');
		if (is_array($order) && isset($order[0]['column'], $order[0]['dir']) && isset($map[(int)$order[0]['column']])) {
			$column = $map[(int)$order[0]['column']];
			$dir = strtolower($order[0]['dir']) === 'asc' ? 'ASC' : 'DESC';
		}
		return [$column, $dir];
	}

	public function referralReportList()
	{
		$f = $this->referralFilters();
		$draw = (int)$this->input->post('draw');
		$start = max(0, (int)$this->input->post('start'));
		$length = (int)$this->input->post('length');
		$length = ($length < 1) ? 25 : min($length, 250);

		$sql = $this->referralReferrerSql($f);
		$total = (int)$this->db->query('SELECT COUNT(DISTINCT user_reference_id) AS c FROM users WHERE user_reference_id > 0')->row()->c;
		$filtered = (int)$this->db->query("SELECT COUNT(*) AS c FROM ($sql) x")->row()->c;

		$map = [0 => 'user_name', 1 => 'referred', 2 => 'active_customers', 3 => 'orders', 4 => 'revenue', 5 => 'cost',
			6 => 'profit', 7 => 'profit', 8 => 'deposits', 9 => 'spend', 10 => 'legacy_order_amount', 11 => 'legacy_payment_amount'];
		list($orderColumn, $orderDir) = $this->referralOrder($map, 6);
		$rows = $this->db->query("SELECT * FROM ($sql) x ORDER BY $orderColumn $orderDir, user_id ASC LIMIT " . (int)$length . ' OFFSET ' . (int)$start)->result();

		$data = [];
		foreach ($rows as $row) {
			$id = (int)$row->user_id;
			$data[] = [
				'referrer' => '<a href="' . base_url('users/referralReportDetail/' . $id) . '" class="fw-bold text-gray-800 text-hover-primary">' . $this->referralPerson($row) . '</a><br><small class="text-muted">' . html_escape($row->user_email) . '</small>',
				'referred' => number_format((int)$row->referred, 0, ',', '.'),
				'active' => number_format((int)$row->active_customers, 0, ',', '.'),
				'orders' => number_format((int)$row->orders, 0, ',', '.'),
				'revenue' => $this->referralMoney($row->revenue),
				'cost' => $this->referralMoney($row->cost),
				'profit' => $this->referralMoney($row->profit) . ((int)$row->missing_cost > 0 ? ' <i class="ki-outline ki-information-5 text-warning" title="' . (int)$row->missing_cost . ' siparişte maliyet yok"></i>' : ''),
				'margin' => $this->referralMargin($row->profit, $row->costed_revenue),
				'deposits' => $this->referralMoney($row->deposits),
				'spend' => $this->referralMoney($row->spend),
				'legacy_orders' => number_format((int)$row->legacy_orders, 0, ',', '.') . ' / ' . $this->referralMoney($row->legacy_order_amount),
				'legacy_payments' => number_format((int)$row->legacy_payments, 0, ',', '.') . ' / ' . $this->referralMoney($row->legacy_payment_amount),
				'profit_raw' => (float)$row->profit,
				'actions' => '<a href="' . base_url('users/referralReportDetail/' . $id) . '" class="btn btn-sm btn-light-primary">Detay</a>',
			];
		}

		return json(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
	}

	public function referralReportSummary()
	{
		$f = $this->referralFilters();
		$referrerID = max(0, (int)$this->input->post_get('referrer_id'));

		if ($referrerID > 0) {
			$custSql = $this->referralCustomerSql($f, $referrerID);
			$referrerFilter = ' AND c.user_reference_id = ' . $referrerID;
		} elseif ($f['search'] !== '' || $f['min_referred'] > 0 || $f['only_active']) {
			$custSql = 'SELECT m.* FROM (' . $this->referralCustomerSql($f) . ') m WHERE m.referrer_id IN (SELECT user_id FROM (' . $this->referralReferrerSql($f) . ') rr)';
			$referrerFilter = ' AND c.user_reference_id IN (SELECT user_id FROM (' . $this->referralReferrerSql($f) . ') rr2)';
		} else {
			$custSql = $this->referralCustomerSql($f);
			$referrerFilter = '';
		}

		$t = $this->db->query("SELECT COUNT(DISTINCT referrer_id) AS referrers, COUNT(*) AS referred,
				SUM(CASE WHEN orders > 0 THEN 1 ELSE 0 END) AS active_customers, COALESCE(SUM(orders), 0) AS orders,
				COALESCE(SUM(revenue), 0) AS revenue, COALESCE(SUM(cost), 0) AS cost,
				COALESCE(SUM(costed_revenue), 0) AS costed_revenue, COALESCE(SUM(missing_cost), 0) AS missing_cost,
				COALESCE(SUM(deposits), 0) AS deposits, COALESCE(SUM(spend), 0) AS spend,
				COALESCE(SUM(legacy_orders), 0) AS legacy_orders, COALESCE(SUM(legacy_order_amount), 0) AS legacy_order_amount,
				COALESCE(SUM(legacy_payments), 0) AS legacy_payments, COALESCE(SUM(legacy_payment_amount), 0) AS legacy_payment_amount
			FROM ($custSql) z")->row();

		$trendFrom = $f['date_from'] !== '' ? $f['date_from'] : date('Y-m-01', strtotime('-11 months'));
		$trendWhere = 'o.order_status = 1 AND c.user_reference_id > 0' . $referrerFilter
			. ' AND o.order_created_at >= ' . $this->db->escape($trendFrom . ' 00:00:00');
		if ($f['date_to'] !== '') {
			$trendWhere .= ' AND o.order_created_at <= ' . $this->db->escape($f['date_to'] . ' 23:59:59');
		}
		$try = $this->referralTryCurrencies();
		$trend = $this->db->query("SELECT ym, COUNT(*) AS orders, SUM(rev) AS revenue, SUM(CASE WHEN cst > 0 THEN rev - cst ELSE 0 END) AS profit FROM (
				SELECT DATE_FORMAT(o.order_created_at, '%Y-%m') AS ym,
					COALESCE(NULLIF(o.order_sale_price_tl, 0), CASE WHEN UPPER(COALESCE(o.order_sale_price_currency, '')) IN $try THEN o.order_sale_price END, 0) AS rev,
					COALESCE(NULLIF(o.order_cost_tl, 0), CASE WHEN UPPER(COALESCE(o.order_cost_currency, '')) IN $try THEN o.order_cost END, 0) AS cst
				FROM orders o JOIN users c ON c.user_id = o.order_reseller_id WHERE $trendWhere
			) q GROUP BY ym ORDER BY ym")->result();

		$profit = (float)$t->costed_revenue - (float)$t->cost;
		return json([
			'status' => 'success',
			'totals' => [
				'referrers' => (int)$t->referrers,
				'referred' => (int)$t->referred,
				'active_customers' => (int)$t->active_customers,
				'orders' => (int)$t->orders,
				'revenue' => (float)$t->revenue,
				'cost' => (float)$t->cost,
				'profit' => $profit,
				'margin' => (float)$t->costed_revenue > 0 ? round($profit / (float)$t->costed_revenue * 100, 2) : null,
				'missing_cost' => (int)$t->missing_cost,
				'deposits' => (float)$t->deposits,
				'spend' => (float)$t->spend,
				'legacy_orders' => (int)$t->legacy_orders,
				'legacy_order_amount' => (float)$t->legacy_order_amount,
				'legacy_payments' => (int)$t->legacy_payments,
				'legacy_payment_amount' => (float)$t->legacy_payment_amount,
			],
			'trend' => array_map(function ($r) {
				return ['month' => $r->ym, 'orders' => (int)$r->orders, 'revenue' => (float)$r->revenue, 'profit' => (float)$r->profit];
			}, $trend),
		]);
	}

	public function referralReportDetailList()
	{
		$f = $this->referralFilters();
		$referrerID = (int)$this->uri->segment(3);
		$draw = (int)$this->input->post('draw');
		$start = max(0, (int)$this->input->post('start'));
		$length = (int)$this->input->post('length');
		$length = ($length < 1) ? 25 : min($length, 250);

		$sql = 'SELECT m.*, (m.costed_revenue - m.cost) AS profit FROM (' . $this->referralCustomerSql($f, $referrerID) . ') m WHERE 1 = 1';
		$total = (int)$this->db->where('user_reference_id', $referrerID)->count_all_results('users');
		if ($f['search'] !== '') {
			$like = $this->db->escape('%' . $this->db->escape_like_str($f['search']) . '%');
			$sql .= " AND (m.user_name LIKE $like OR m.user_surname LIKE $like OR m.user_nickname LIKE $like OR m.user_email LIKE $like)";
		}
		if ($f['only_active']) {
			$sql .= ' AND m.orders > 0';
		}
		$filtered = (int)$this->db->query("SELECT COUNT(*) AS c FROM ($sql) x")->row()->c;

		$map = [0 => 'user_name', 1 => 'user_created_at', 2 => 'orders', 3 => 'revenue', 4 => 'cost', 5 => 'profit', 6 => 'profit',
			7 => 'deposits', 8 => 'spend', 9 => 'legacy_order_amount', 10 => 'legacy_payment_amount', 11 => 'last_order'];
		list($orderColumn, $orderDir) = $this->referralOrder($map, 3);
		$rows = $this->db->query("SELECT * FROM ($sql) x ORDER BY $orderColumn $orderDir, user_id ASC LIMIT " . (int)$length . ' OFFSET ' . (int)$start)->result();

		$data = [];
		foreach ($rows as $row) {
			$data[] = [
				'customer' => '<a href="' . base_url('users/detail/' . (int)$row->user_id) . '" class="fw-bold text-gray-800 text-hover-primary">' . $this->referralPerson($row) . '</a><br><small class="text-muted">' . html_escape($row->user_email) . '</small>',
				'registered' => !empty($row->user_created_at) ? date('d.m.Y', strtotime($row->user_created_at)) : '-',
				'orders' => number_format((int)$row->orders, 0, ',', '.'),
				'revenue' => $this->referralMoney($row->revenue),
				'cost' => $this->referralMoney($row->cost),
				'profit' => $this->referralMoney($row->profit) . ((int)$row->missing_cost > 0 ? ' <i class="ki-outline ki-information-5 text-warning" title="' . (int)$row->missing_cost . ' siparişte maliyet yok"></i>' : ''),
				'margin' => $this->referralMargin($row->profit, $row->costed_revenue),
				'deposits' => $this->referralMoney($row->deposits),
				'spend' => $this->referralMoney($row->spend),
				'legacy_orders' => number_format((int)$row->legacy_orders, 0, ',', '.') . ' / ' . $this->referralMoney($row->legacy_order_amount),
				'legacy_payments' => number_format((int)$row->legacy_payments, 0, ',', '.') . ' / ' . $this->referralMoney($row->legacy_payment_amount),
				'last_order' => !empty($row->last_order) ? date('d.m.Y', strtotime($row->last_order)) : '-',
				'profit_raw' => (float)$row->profit,
			];
		}

		return json(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
	}

	public function referralReportExport()
	{
		$f = $this->referralFilters();
		$rows = $this->db->query($this->referralReferrerSql($f) . ' ORDER BY profit DESC LIMIT 10000')->result();

		// Excel formül enjeksiyonuna karşı hücre başlangıcı nötrlenir.
		$cell = function ($value) {
			$value = (string)$value;
			return (preg_match('/^[=+\-@\t\r]/', $value) ? "'" : '') . $value;
		};

		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="referans-raporu-' . date('Ymd-His') . '.csv"');
		$out = fopen('php://output', 'w');
		fwrite($out, "\xEF\xBB\xBF");
		fputcsv($out, ['Referans', 'E-posta', 'Davet edilen', 'Aktif müşteri', 'Tamamlanan sipariş', 'Ciro (TL)', 'Maliyet (TL)', 'Platform kârı (TL)', 'Marj (%)', 'Maliyeti olmayan sipariş', 'Bakiye yükleme (TL)', 'Bakiye harcama (TL)', 'Legacy sipariş adedi', 'Legacy sipariş tutarı (TL)', 'Legacy ödeme adedi', 'Legacy ödeme tutarı (TL)'], ';');
		foreach ($rows as $row) {
			$margin = (float)$row->costed_revenue > 0 ? round((float)$row->profit / (float)$row->costed_revenue * 100, 2) : '';
			fputcsv($out, [
				$cell(trim($row->user_name . ' ' . $row->user_surname) ?: $row->user_nickname), $cell($row->user_email),
				(int)$row->referred, (int)$row->active_customers, (int)$row->orders,
				number_format((float)$row->revenue, 2, '.', ''), number_format((float)$row->cost, 2, '.', ''),
				number_format((float)$row->profit, 2, '.', ''), $margin, (int)$row->missing_cost,
				number_format((float)$row->deposits, 2, '.', ''), number_format((float)$row->spend, 2, '.', ''),
				(int)$row->legacy_orders, number_format((float)$row->legacy_order_amount, 2, '.', ''),
				(int)$row->legacy_payments, number_format((float)$row->legacy_payment_amount, 2, '.', ''),
			], ';');
		}
		fclose($out);
		exit;
	}
}
