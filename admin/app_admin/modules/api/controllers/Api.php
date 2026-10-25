<?php defined('BASEPATH') or exit('No direct script access allowed');

class Api extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		userCheck();
		parent::__construct();
	}

	public function dijiActiveProducts()
	{
		$products = $this->db->select('product_dijipin_id')
			->where('product_api_status', 1)
			->where('product_dijipin_id IS NOT NULL')
			->get('products')->result_array();

		$products = array_unique(array_map('intval', array_column($products, 'product_dijipin_id')));
		$params = [
			'status' => 0,
			'productIDs' => $products
		];
		return json_encode($params);
	}

	public function index()
	{
		$data = $this->data;
		$data['categories'] = $this->db->get('categories')->result();
		//pre($data['categories']);die;
		foreach ($data['categories'] as $key => $cat) {
			$data['categories'][$key]->products = $this->db->select('p.product_id, p.product_name, p.product_api_status, u.user_name, u.user_id')->from('products as p')
				->join('seller_products as sp', 'p.product_id = sp.seller_products_product_id', 'left')
				->join('users as u', 'sp.seller_products_seller_id = u.user_id', 'left')
				->where('p.product_category_id', $cat->category_id)
				->get()
				->result();
		}
		//pre($data['categories']);die;
		$this->template->content->view('index', $data);
		$this->template->publish();
	}

	public function changeProductStatus()
	{
		$post = $this->input->post();
		$pid = $post['pid'];
		$update = $this->db->where('product_id', $pid)->update('products', ['product_api_status' => $post['status']]);
		return json(['status' => 'success', 'message' => 'Durum başarıyla güncellendi']);
	}

	public function changeCategoryStatus()
	{
		$post = $this->input->post();
		$cid = $post['cid'];
		$update = $this->db->where('category_id', $cid)->update('categories', ['category_api_status' => $post['status']]);
		return json(['status' => 'success', 'message' => 'Durum başarıyla güncellendi']);
	}

	public function startCron()
	{
		$pidFile = '/home/dijipin-api/tmp/crons/listen_cron_process.pid';
		$commands = [
			"index.php cron2 listenDijipinOrders",
		];
		$phpPath = "/usr/local/bin/php";
		$basePath = getenv('BASE_PATH');
		if (file_exists($pidFile)) {
			//echo "Cron işlemi zaten çalışıyor. Çakışma önlendi.<br>";
			echo json_encode(['status' => 'error', 'message' => 'Cron işlemi zaten çalışıyor. Çakışma önlendi.']);
			exit;
		}
		file_put_contents($pidFile, getmypid());
		while (true) {
			foreach ($commands as $command) {
				$cmd = "pgrep -f \"$command\"";
				$runningInstances = trim(shell_exec($cmd));
				if (empty($runningInstances)) {
					//echo "$command çalışmıyor, başlatılıyor...<br>";
					echo json_encode(['status' => 'success', 'message' => 'Cron işlemi başlatıldı']);
					$exec = shell_exec("nohup $phpPath $basePath$command > /dev/null 2>&1 &");
				} else {
					echo "$command zaten çalışıyor.<br>";
					echo json_encode(['status' => 'error', 'message' => $command . ' işlemi zaten çalışıyor.']);
				}
			}
			sleep(2);
		}
	}

	public function stopCron()
	{
		$pidFile = '/home/dijipin-api/tmp/crons/listen_cron_process.pid';
		if (file_exists($pidFile)) {
			$pid = trim(file_get_contents($pidFile));
			shell_exec("kill $pid");
			unlink($pidFile);
			//echo "cron.php işlemi sonlandırıldı.";
			echo json_encode(['status' => 'success', 'message' => 'Cron işlemi durduruldu.']);
		} else {
			//echo "Çalışan bir cron.php işlemi bulunamadı.";
			echo json_encode(['status' => 'error', 'message' => 'Çalışan bir cron işlemi bulunamadı.']);
		}
		return json(['status' => 'success', 'message' => 'Cron işlemi durduruldu.']);
	}
}
