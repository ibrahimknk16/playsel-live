<?php defined('BASEPATH') or exit('No direct script access allowed');

class Admins extends MY_Controller
{
	public $data = array();
	private $adminRoles = [
		2 => 'Super Admin',
		3 => 'Admin',
		4 => 'Operasyon',
	];

	public function __construct()
	{
		userCheck();
		parent::__construct();
		$this->load->model('VtModel');
	}

	public function index()
	{
		$data = $this->data;
		$users = $this->db->select('admin_id, admin_email, admin_name, admin_power, admin_status, admin_created_at')->get('admins')->result();
		$data['users'] = $users;
		$data['roles'] = $this->adminRoles;
		$this->template->content->view('index', $data);
		$this->template->publish();
	}
	public function addUser()
	{
		$admin = $this->session->userdata('admin');
		if (!is_array($admin) || (int) ($admin['admin_power'] ?? 0) !== 2) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Yetkisiz işlem', 'message' => 'Yalnızca Super Admin yeni yönetici oluşturabilir.']);
			redirect(base_url('admins'));
			return;
		}

		$post = $this->input->post();
		if (empty($post)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Hatalı istek.']);
			redirect(base_url('admins'));
			return;
		}

		$adminPower = isset($post['admin_power']) && $post['admin_power'] !== '' ? (int) $post['admin_power'] : 4;
		if (!in_array($adminPower, [2, 3, 4], true)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hatalı rol', 'message' => 'Geçerli bir yönetici rolü seçin.']);
			redirect(base_url('admins'));
			return;
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
			'admin_power' => $adminPower,
			'admin_permissions' => !empty($post['permissions']) ? json_encode($post['permissions']) : json_encode([]),
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
		$user = $this->db->where('admin_id', $userID)->get('admins')->row();
		if (empty($user)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Kullanici bulunamadi.']);
			redirect(base_url('admins'));
		}

		if (!$this->hasPermissionTables()) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Permission tablolari bulunamadi. Once sql_context.txt calistirin.']);
			redirect(base_url('admins'));
		}

		$categories = $this->db->order_by('permission_category_name', 'ASC')->get('permission_categories')->result_array();
		$permissions = $this->db
			->select('p.permission_id, p.permission_code, p.permission_name, p.permission_status, p.permission_category_id, c.permission_category_name')
			->from('permissions p')
			->join('permission_categories c', 'c.permission_category_id = p.permission_category_id', 'left')
			->where('p.permission_status', 1)
			->order_by('c.permission_category_name', 'ASC')
			->order_by('p.permission_code', 'ASC')
			->get()->result_array();

		$permissionsByCategory = [];
		foreach ($permissions as $permission) {
			$categoryId = (int) $permission['permission_category_id'];
			if (!isset($permissionsByCategory[$categoryId])) {
				$permissionsByCategory[$categoryId] = [];
			}
			$permissionsByCategory[$categoryId][] = $permission;
		}

		$roleAllowIds = $this->db->select('permission_id')
			->from('admin_role_permissions')
			->where('admin_power', (int) $user->admin_power)
			->where('admin_role_permission_effect', 'allow')
			->get()->result_array();
		$roleAllowIds = array_map('intval', array_column($roleAllowIds, 'permission_id'));

		$userOverrides = $this->db->select('permission_id, admin_user_permission_effect')
			->from('admin_user_permissions')
			->where('admin_id', (int) $user->admin_id)
			->get()->result_array();

		$userAllowIds = [];
		$userDenyIds = [];
		foreach ($userOverrides as $override) {
			$permissionId = (int) $override['permission_id'];
			if ($override['admin_user_permission_effect'] === 'deny') {
				$userDenyIds[] = $permissionId;
			} else {
				$userAllowIds[] = $permissionId;
			}
		}

		unset($user->admin_pass);
		$data['user'] = $user;
		$data['roles'] = $this->adminRoles;
		$data['categories'] = $categories;
		$data['permissionsByCategory'] = $permissionsByCategory;
		$data['roleAllowIds'] = $roleAllowIds;
		$data['userAllowIds'] = $userAllowIds;
		$data['userDenyIds'] = $userDenyIds;
		$this->template->content->view('detail', $data);
		$this->template->publish();
	}

	public function updateUserPermissions($userID = null)
	{
		if (!$this->hasPermissionTables()) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Permission tablolari bulunamadi.']);
			redirect(base_url('admins'));
		}

		$userID = !empty($userID) ? (int) $userID : (int) $this->input->post('admin_id');
		if (empty($userID)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Gecersiz kullanici.']);
			redirect(base_url('admins'));
		}

		$user = $this->db->where('admin_id', $userID)->get('admins')->row();
		if (empty($user)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Kullanici bulunamadi.']);
			redirect(base_url('admins'));
		}

		$allowIds = $this->input->post('user_allow_ids');
		$denyIds = $this->input->post('user_deny_ids');
		$allowIds = is_array($allowIds) ? array_map('intval', $allowIds) : [];
		$denyIds = is_array($denyIds) ? array_map('intval', $denyIds) : [];

		$allowIds = array_values(array_unique(array_filter($allowIds)));
		$denyIds = array_values(array_unique(array_filter($denyIds)));

		if (!empty($allowIds) && !empty($denyIds)) {
			$allowIds = array_values(array_diff($allowIds, $denyIds));
		}

		$saveRows = [];
		foreach ($allowIds as $permissionId) {
			$saveRows[] = [
				'admin_id' => $userID,
				'permission_id' => $permissionId,
				'admin_user_permission_effect' => 'allow',
			];
		}

		foreach ($denyIds as $permissionId) {
			$saveRows[] = [
				'admin_id' => $userID,
				'permission_id' => $permissionId,
				'admin_user_permission_effect' => 'deny',
			];
		}

		$this->db->trans_start();
		$this->db->where('admin_id', $userID)->delete('admin_user_permissions');
		if (!empty($saveRows)) {
			$this->db->insert_batch('admin_user_permissions', $saveRows);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status()) {
			$this->clearPermissionCache();
			$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Basarili!', 'message' => 'Kullanici yetki override kayitlari guncellendi.']);
		} else {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Yetki kayitlari guncellenemedi.']);
		}

		redirect(base_url('admins/detail/' . $userID));
	}

	public function permissions()
	{
		if (!$this->hasPermissionTables()) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Permission tablolari bulunamadi. Once sql_context.txt calistirin.']);
			redirect(base_url('admins'));
		}

		$data = $this->data;
		$data['roles'] = $this->adminRoles;
		$data['categories'] = $this->db->order_by('permission_category_name', 'ASC')->get('permission_categories')->result_array();

		$data['permissions'] = $this->db
			->select('p.*, c.permission_category_name')
			->from('permissions p')
			->join('permission_categories c', 'c.permission_category_id = p.permission_category_id', 'left')
			->order_by('p.permission_id', 'DESC')
			->get()->result_array();

		$this->template->content->view('permissions', $data);
		$this->template->publish();
	}

	public function addCategory()
	{
		if (!$this->hasPermissionTables()) {
			redirect(base_url('admins'));
		}

		$post = $this->input->post();
		$key = !empty($post['permission_category_key']) ? trim(strtolower($post['permission_category_key'])) : '';
		$name = !empty($post['permission_category_name']) ? trim($post['permission_category_name']) : '';
		$status = isset($post['permission_category_status']) ? (int) $post['permission_category_status'] : 1;

		if ($key === '' || $name === '') {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Kategori key ve ad zorunludur.']);
			redirect(base_url('admins/permissions'));
		}

		$exists = $this->db->where('permission_category_key', $key)->get('permission_categories')->row();
		if (!empty($exists)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Bu kategori key zaten mevcut.']);
			redirect(base_url('admins/permissions'));
		}

		$this->db->insert('permission_categories', [
			'permission_category_key' => $key,
			'permission_category_name' => $name,
			'permission_category_status' => $status,
		]);

		$this->clearPermissionCache();
		$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Basarili!', 'message' => 'Kategori eklendi.']);
		redirect(base_url('admins/permissions'));
	}

	public function updateCategory($categoryID = null)
	{
		if (!$this->hasPermissionTables()) {
			redirect(base_url('admins'));
		}

		$categoryID = (int) $categoryID;
		$post = $this->input->post();
		$key = !empty($post['permission_category_key']) ? trim(strtolower($post['permission_category_key'])) : '';
		$name = !empty($post['permission_category_name']) ? trim($post['permission_category_name']) : '';
		$status = isset($post['permission_category_status']) ? (int) $post['permission_category_status'] : 1;

		if (empty($categoryID) || $key === '' || $name === '') {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Gecersiz kategori verisi.']);
			redirect(base_url('admins/permissions'));
		}

		$exists = $this->db->where('permission_category_key', $key)->where('permission_category_id !=', $categoryID)->get('permission_categories')->row();
		if (!empty($exists)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Bu kategori key zaten kullaniliyor.']);
			redirect(base_url('admins/permissions'));
		}

		$this->db->where('permission_category_id', $categoryID)->update('permission_categories', [
			'permission_category_key' => $key,
			'permission_category_name' => $name,
			'permission_category_status' => $status,
		]);

		$this->clearPermissionCache();
		$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Basarili!', 'message' => 'Kategori guncellendi.']);
		redirect(base_url('admins/permissions'));
	}

	public function deleteCategory($categoryID = null)
	{
		if (!$this->hasPermissionTables()) {
			redirect(base_url('admins'));
		}

		$categoryID = (int) $categoryID;
		if (empty($categoryID)) {
			redirect(base_url('admins/permissions'));
		}

		$permissionCount = $this->db->where('permission_category_id', $categoryID)->count_all_results('permissions');
		if ($permissionCount > 0) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Kategoriye bagli permission kayitlari var. Once permissionlari tasiyin veya silin.']);
			redirect(base_url('admins/permissions'));
		}

		$this->db->where('permission_category_id', $categoryID)->delete('permission_categories');
		$this->clearPermissionCache();
		$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Basarili!', 'message' => 'Kategori silindi.']);
		redirect(base_url('admins/permissions'));
	}

	public function addPermission()
	{
		if (!$this->hasPermissionTables()) {
			redirect(base_url('admins'));
		}

		$post = $this->input->post();
		$categoryID = !empty($post['permission_category_id']) ? (int) $post['permission_category_id'] : 0;
		$module = !empty($post['permission_module']) ? trim(strtolower($post['permission_module'])) : '';
		$controller = !empty($post['permission_controller']) ? trim(strtolower($post['permission_controller'])) : '';
		$method = !empty($post['permission_method']) ? trim(strtolower($post['permission_method'])) : '';
		$code = !empty($post['permission_code']) ? trim(strtolower($post['permission_code'])) : '';
		$name = !empty($post['permission_name']) ? trim($post['permission_name']) : '';
		$description = isset($post['permission_description']) ? trim($post['permission_description']) : null;
		$status = isset($post['permission_status']) ? (int) $post['permission_status'] : 1;

		if (empty($code) && $module !== '' && $controller !== '' && $method !== '') {
			$code = $module . '.' . $controller . '.' . $method;
		}

		if (empty($categoryID) || $module === '' || $controller === '' || $method === '' || $code === '' || $name === '') {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Permission alanlari eksik.']);
			redirect(base_url('admins/permissions'));
		}

		$exists = $this->db->where('permission_code', $code)->get('permissions')->row();
		if (!empty($exists)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Bu permission_code zaten mevcut.']);
			redirect(base_url('admins/permissions'));
		}

		$this->db->insert('permissions', [
			'permission_category_id' => $categoryID,
			'permission_code' => $code,
			'permission_module' => $module,
			'permission_controller' => $controller,
			'permission_method' => $method,
			'permission_name' => $name,
			'permission_description' => $description,
			'permission_status' => $status,
		]);

		$this->clearPermissionCache();
		$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Basarili!', 'message' => 'Permission eklendi.']);
		redirect(base_url('admins/permissions'));
	}

	public function updatePermission($permissionID = null)
	{
		if (!$this->hasPermissionTables()) {
			redirect(base_url('admins'));
		}

		$permissionID = (int) $permissionID;
		$post = $this->input->post();
		$categoryID = !empty($post['permission_category_id']) ? (int) $post['permission_category_id'] : 0;
		$module = !empty($post['permission_module']) ? trim(strtolower($post['permission_module'])) : '';
		$controller = !empty($post['permission_controller']) ? trim(strtolower($post['permission_controller'])) : '';
		$method = !empty($post['permission_method']) ? trim(strtolower($post['permission_method'])) : '';
		$code = !empty($post['permission_code']) ? trim(strtolower($post['permission_code'])) : '';
		$name = !empty($post['permission_name']) ? trim($post['permission_name']) : '';
		$description = isset($post['permission_description']) ? trim($post['permission_description']) : null;
		$status = isset($post['permission_status']) ? (int) $post['permission_status'] : 1;

		if (empty($permissionID) || empty($categoryID) || $module === '' || $controller === '' || $method === '' || $code === '' || $name === '') {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Permission verisi gecersiz.']);
			redirect(base_url('admins/permissions'));
		}

		$exists = $this->db->where('permission_code', $code)->where('permission_id !=', $permissionID)->get('permissions')->row();
		if (!empty($exists)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Bu permission_code zaten kullaniliyor.']);
			redirect(base_url('admins/permissions'));
		}

		$this->db->where('permission_id', $permissionID)->update('permissions', [
			'permission_category_id' => $categoryID,
			'permission_code' => $code,
			'permission_module' => $module,
			'permission_controller' => $controller,
			'permission_method' => $method,
			'permission_name' => $name,
			'permission_description' => $description,
			'permission_status' => $status,
		]);

		$this->clearPermissionCache();
		$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Basarili!', 'message' => 'Permission guncellendi.']);
		redirect(base_url('admins/permissions'));
	}

	public function deletePermission($permissionID = null)
	{
		if (!$this->hasPermissionTables()) {
			redirect(base_url('admins'));
		}

		$permissionID = (int) $permissionID;
		if (empty($permissionID)) {
			redirect(base_url('admins/permissions'));
		}

		$this->db->trans_start();
		$this->db->where('permission_id', $permissionID)->delete('admin_role_permissions');
		$this->db->where('permission_id', $permissionID)->delete('admin_user_permissions');
		$this->db->where('permission_id', $permissionID)->delete('permissions');
		$this->db->trans_complete();

		if ($this->db->trans_status()) {
			$this->clearPermissionCache();
			$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Basarili!', 'message' => 'Permission silindi.']);
		} else {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Permission silinemedi.']);
		}

		redirect(base_url('admins/permissions'));
	}

	public function rolePermissions($adminPower = null)
	{
		if (!$this->hasPermissionTables()) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Permission tablolari bulunamadi.']);
			redirect(base_url('admins'));
		}

		$adminPower = !empty($adminPower) ? (int) $adminPower : 3;
		if (!isset($this->adminRoles[$adminPower])) {
			$adminPower = 3;
		}

		$categories = $this->db->order_by('permission_category_name', 'ASC')->get('permission_categories')->result_array();
		$permissions = $this->db->where('permission_status', 1)->order_by('permission_code', 'ASC')->get('permissions')->result_array();

		$permissionsByCategory = [];
		foreach ($permissions as $permission) {
			$categoryId = (int) $permission['permission_category_id'];
			if (!isset($permissionsByCategory[$categoryId])) {
				$permissionsByCategory[$categoryId] = [];
			}
			$permissionsByCategory[$categoryId][] = $permission;
		}

		$roleRows = $this->db->select('permission_id, admin_role_permission_effect')
			->from('admin_role_permissions')
			->where('admin_power', $adminPower)
			->get()->result_array();

		$allowIds = [];
		$denyIds = [];
		foreach ($roleRows as $row) {
			$permissionId = (int) $row['permission_id'];
			if ($row['admin_role_permission_effect'] === 'deny') {
				$denyIds[] = $permissionId;
			} else {
				$allowIds[] = $permissionId;
			}
		}

		$data = $this->data;
		$data['roles'] = $this->adminRoles;
		$data['selectedRole'] = $adminPower;
		$data['categories'] = $categories;
		$data['permissionsByCategory'] = $permissionsByCategory;
		$data['roleAllowIds'] = $allowIds;
		$data['roleDenyIds'] = $denyIds;

		$this->template->content->view('role_permissions', $data);
		$this->template->publish();
	}

	public function updateRolePermissions($adminPower = null)
	{
		if (!$this->hasPermissionTables()) {
			redirect(base_url('admins'));
		}

		$adminPower = !empty($adminPower) ? (int) $adminPower : (int) $this->input->post('admin_power');
		if (!isset($this->adminRoles[$adminPower])) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Gecersiz rol.']);
			redirect(base_url('admins/rolePermissions/3'));
		}

		$allowIds = $this->input->post('role_allow_ids');
		$denyIds = $this->input->post('role_deny_ids');
		$allowIds = is_array($allowIds) ? array_map('intval', $allowIds) : [];
		$denyIds = is_array($denyIds) ? array_map('intval', $denyIds) : [];

		$allowIds = array_values(array_unique(array_filter($allowIds)));
		$denyIds = array_values(array_unique(array_filter($denyIds)));
		if (!empty($allowIds) && !empty($denyIds)) {
			$allowIds = array_values(array_diff($allowIds, $denyIds));
		}

		$saveRows = [];
		foreach ($allowIds as $permissionId) {
			$saveRows[] = [
				'admin_power' => $adminPower,
				'permission_id' => $permissionId,
				'admin_role_permission_effect' => 'allow',
			];
		}

		foreach ($denyIds as $permissionId) {
			$saveRows[] = [
				'admin_power' => $adminPower,
				'permission_id' => $permissionId,
				'admin_role_permission_effect' => 'deny',
			];
		}

		$this->db->trans_start();
		$this->db->where('admin_power', $adminPower)->delete('admin_role_permissions');
		if (!empty($saveRows)) {
			$this->db->insert_batch('admin_role_permissions', $saveRows);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status()) {
			$this->clearPermissionCache();
			$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Basarili!', 'message' => 'Rol yetkileri guncellendi.']);
		} else {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Rol yetkileri guncellenemedi.']);
		}

		redirect(base_url('admins/rolePermissions/' . $adminPower));
	}

	private function hasPermissionTables()
	{
		$requiredTables = [
			'permission_categories',
			'permissions',
			'admin_role_permissions',
		];

		foreach ($requiredTables as $table) {
			if (!$this->db->table_exists($table)) {
				return false;
			}
		}

		return true;
	}

	private function clearPermissionCache()
	{
		$this->session->unset_userdata('admin_permission_cache');
	}

	public function deneme()
	{
		$this->load->view('deneme');
	}
}
