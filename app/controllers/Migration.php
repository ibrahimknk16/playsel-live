<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		if (!is_cli()) {
			show_error('Migration yalnızca CLI üzerinden çalıştırılabilir.', 403);
		}

		$this->load->library('migration');
	}

	public function latest()
	{
		$result = $this->migration->latest();
		if ($result === false) {
			$error = $this->migration->error_string();
			log_message('error', 'Migration latest failed: ' . $error);
			echo trim($error) . PHP_EOL;
			exit(1);
		}

		echo 'Migration tamamlandı.' . PHP_EOL;
	}
}
