<?php defined('BASEPATH') or exit('No direct script access allowed');

use Predis\Client;

class User extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model('VtModel');
	}

	public function login()
	{
		$this->load->view('login');
	}

	public function logout()
	{
		$this->session->unset_userdata('admin');
		redirect(base_url('login'));
	}

	public function addUser()
	{
		die;
		//$post = $this->input->post();
		$post = [
			'name' => 'Dijipin',
			'email' => 'demo@dijipin.com',
			'password' => 'diji1q2w3e4r',
		];
		if (empty($post)) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Hatalı istek.']);
			redirect(base_url('users'));
		}
		$insertData = [
			'admin_name' => !empty($post['name']) ? $post['name'] : '',
			'admin_email' => !empty($post['email']) ? $post['email'] : '',
			'admin_pass' => password_hash($post['password'], PASSWORD_DEFAULT),
			'admin_power' => 1,
		];
		if (empty($insertData['admin_name']) || empty($insertData['admin_email']) || empty($insertData['admin_pass'])) {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Lütfen tüm alanları doldurun.']);
			redirect(base_url('users'));
		}
		$insert = $this->db->insert('admins', $insertData);
		if ($insert) {
			$this->session->set_flashdata('alert', ['type' => 'success', 'title' => 'Başarılı!', 'message' => 'Kullanıcı başarıyla eklendi.']);
			redirect(base_url('users'));
		} else {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Kullanıcı eklenirken bir hata oluştu.']);
			redirect(base_url('users'));
		}
	}

	public function loginRequest()
	{
		//pre($_SERVER);die;
		$post = $this->input->post();
		$refferer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('dashboard');
		$checkRef = explode('?ref=', $refferer);
		$checkRef = !empty($checkRef[1]) ? $checkRef[1] : 'dashboard';
		if (!empty($post)) {
			$checkUser = $this->db->from('admins')->where('admin_email', $post['email'])->get()->row_array();
			if (!empty($checkUser) && password_verify($post['password'], $checkUser['admin_pass'])) {
				$userData = [
					'admin_id' => $checkUser['admin_id'],
					'admin_name' => $checkUser['admin_name'],
					'admin_email' => $checkUser['admin_email'],
					'admin_power' => $checkUser['admin_power'],
				];
				$this->session->set_userdata('admin', $userData);
				redirect(base_url($checkRef));
			} else {
				$this->session->set_flashdata('alert', [
					'type' => 'error',
					'title' => 'Hata!',
					'message' => 'Kullanıcı adı veya şifre hatalı.'
				]);
				redirect(base_url('login?ref=' . $checkRef));
			}
		} else {
			$this->session->set_flashdata('alert', ['type' => 'error', 'title' => 'Hata!', 'message' => 'Hatalı istek.']);
			redirect(base_url('login?ref=' . $checkRef));
		}
	}
}
