<?php defined('BASEPATH') or exit('No direct script access allowed');

class Utils extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		userCheck();
		parent::__construct();
		$this->load->model('VtModel');
	}

	public function otherPrices()
	{
		$data = $this->data;
		$this->template->content->view('index', $data);
		$this->template->publish();
	}

}
