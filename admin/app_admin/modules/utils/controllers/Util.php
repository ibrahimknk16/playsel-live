<?php defined('BASEPATH') or exit('No direct script access allowed');

class Util extends MY_Controller
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
		echo "asd";die;
		$data = $this->data;
		$epins = $this->db->select('*')->from('epin_pool')->get()->result();
		$this->template->content->view('index', $data);
		$this->template->publish();
	}


}
