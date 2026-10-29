<?php defined('BASEPATH') or exit('No direct script access allowed');

class Orders extends MY_Controller
{
	public $data = array();
	private $currencyRates = null;

	public function __construct()
	{
		userCheck();
		parent::__construct();
		$this->load->model('VtModel');
		$this->load->model('Orders_model');
	}

	public function currency($currency)
	{
		$trCurrency = tr_strtolower($currency);
		$currencyRow = $this->db->select('currencies_json')
			->from('currency')
			->where('currencies_date', date('Y-m-d'))
			->get()->row();

		if (empty($currencyRow) || empty($currencyRow->currencies_json)) {
			return 0;
		}

		$currencies = json_decode($currencyRow->currencies_json);
		if (!empty($currencies->$trCurrency)) {
			return $currencies->$trCurrency;
		}

		return 0;
	}

	public function index() //++
	{
		$data = $this->data;
		$data['adminActionCsrfToken'] = $this->adminActionCsrfToken();
		$data['cancellation_reasons'] = $this->db->table_exists('order_cancellation_reasons')
			? $this->db->where('reason_status', 1)->order_by('reason_sort', 'ASC')->get('order_cancellation_reasons')->result()
			: [];
		$data['categories'] = $this->db
			->select('category_id, category_title')
			->from('categories')
			->get()->result();

		$data['products'] = $this->db
			->select('product_id, product_name')
			->from('products')
			->get()->result();

		$data['suppiers'] = $this->db
			->select('user_id, user_name')
			->from('users')
			->where('user_type', 2)
			->get()->result();

		$data['resellers'] = $this->db
			->select('user_id, user_name')
			->from('users')
			->where('user_type', 3)
			->get()->result();

		$this->template->content->view('index', $data);
		$this->template->publish();
	}

	public function legacy()
	{
		$data = $this->data;
		$search = trim((string)$this->input->get('search'));
		$limit = min(200, max(1, (int)$this->input->get('limit') ?: 50));

		if ($this->db->query("SHOW TABLES LIKE 'legacy_orders'")->num_rows() === 0) {
			$data['legacy_orders'] = [];
			$data['legacy_order_rows'] = [];
			$data['legacy_payments'] = [];
			$data['legacy_cancellations'] = [];
			$data['legacy_error'] = 'Legacy tabloları henüz oluşturulmamış.';
		} else {
			$this->db->select('o.*, u.user_name, u.user_email');
			$this->db->from('legacy_orders AS o');
			$this->db->join('users AS u', 'u.user_id = o.target_user_id', 'left');
			if ($search !== '') {
				$this->db->group_start()
					->like('o.source_order_no', $search)
					->or_like('o.source_order_id', $search)
					->or_like('o.source_email', $search)
					->or_like('o.product_summary', $search)
					->or_like('u.user_email', $search)
					->group_end();
			}
			$data['legacy_orders'] = $this->db->order_by('o.order_date', 'DESC')->limit($limit)->get()->result();

			$orderKeys = array_map(function ($order) {
				return $order->legacy_order_key;
			}, $data['legacy_orders']);
			$data['legacy_order_rows'] = [];
			if (!empty($orderKeys)) {
				$this->db->where_in('legacy_order_key', $orderKeys);
				$data['legacy_order_rows'] = $this->db->order_by('source_order_no', 'DESC')->get('legacy_order_rows')->result();
			}

			$this->db->select('p.*, o.source_order_no');
			$this->db->from('legacy_payments AS p');
			$this->db->join('legacy_orders AS o', 'o.source_order_id = p.source_order_id', 'left');
			if ($search !== '') {
				$this->db->group_start()
					->like('p.source_payment_id', $search)
					->or_like('p.source_order_id', $search)
					->or_like('p.method_text', $search)
					->or_like('p.status_text', $search)
					->group_end();
			}
			$data['legacy_payments'] = $this->db->order_by('p.payment_date', 'DESC')->limit($limit)->get()->result();

			$this->db->from('legacy_order_cancellations AS c');
			if ($search !== '') {
				$this->db->group_start()
					->like('c.source_refund_id', $search)
					->or_like('c.source_order_no', $search)
					->or_like('c.description', $search)
					->or_like('c.admin_text', $search)
					->group_end();
			}
			$data['legacy_cancellations'] = $this->db->order_by('c.refund_date', 'DESC')->limit($limit)->get()->result();
			$data['legacy_error'] = null;
		}

		$data['legacy_search'] = $search;
		$data['legacy_limit'] = $limit;
		$this->template->content->view('legacy', $data);
		$this->template->publish();
	}

	public function getOrders()
	{
		$uriSegment = (int)$this->uri->segment(3);
		$allowStatusChange = $uriSegment === 2;
		$statusArray = $this->resolveStatusArray($uriSegment);

		$draw = (int)$this->input->post('draw');
		$start = max(0, (int)$this->input->post('start'));
		$length = (int)$this->input->post('length');
		$length = $length > 0 ? min($length, 500) : 20;

		$searchOrderID = (int)$this->input->post('order_id');
		$resellerOrderID = trim((string)$this->input->post('reseller_order_id'));
		$searchProductID = (int)$this->input->post('product_id');
		$searchCategory = (int)$this->input->post('category');
		$searchStatus = $this->input->post('status');
		$searchStatus = $searchStatus === '' ? null : (int)$searchStatus;
		$startDate = $this->normalizeDate($this->input->post('startDate'));
		$endDate = $this->normalizeDate($this->input->post('endDate'));
		$reseller = (int)$this->input->post('reseller');
		$supplier = (int)$this->input->post('supplier');
		$siteID = (int)$this->input->post('site_id');
		$searchUserIDorServer = trim((string)$this->input->post('searchUserIDorServer'));

		if (!empty($startDate) && !empty($endDate) && strtotime($startDate) > strtotime($endDate)) {
			$temp = $startDate;
			$startDate = $endDate;
			$endDate = $temp;
		}

		if ($searchStatus !== null && !in_array((int)$searchStatus, $statusArray, true)) {
			$searchStatus = null;
		}

		$orders = $this->Orders_model->get_orders($start, $length, $searchOrderID, $searchProductID, $searchCategory, $searchStatus, $resellerOrderID, $startDate, $endDate, $reseller, $supplier, $statusArray, $searchUserIDorServer, $siteID);
		$allOrders = $this->Orders_model->get_total_orders($searchOrderID, $searchProductID, $searchCategory, $searchStatus, $resellerOrderID, $startDate, $endDate, $reseller, $supplier, $statusArray, $searchUserIDorServer, $siteID);

		$totalOrders = !empty($allOrders) && isset($allOrders->total_orders) ? (int)$allOrders->total_orders : 0;

		$currentAdminId = (int)($this->session->userdata('admin')['admin_id'] ?? 0);
		$adminMap = $this->getAdminMap($this->extractOrderIntValues($orders, 'order_operator_admin'));
		$statusMap = $this->getOrderStatusMap($this->extractOrderIntValues($orders, 'order_status'));
		$dt = array();

		foreach ($orders as $order) {
			$orderId = (int)$order->order_id;
			$orderStatusCode = (int)$order->order_status;
			$operatorAdminId = (int)$order->order_operator_admin;
			$resellerOrderMain = (string)$order->order_reseller_order_id;
			$resellerOrderDetail = (string)$order->order_reseller_order_detail_id;
			$requireData = !empty($order->order_require_data) ? json_decode($order->order_require_data, true) : [];
			if (!is_array($requireData)) {
				$requireData = [];
			}

			if ($operatorAdminId > 0 && isset($adminMap[$operatorAdminId])) {
				$order->operator_admin_name = $adminMap[$operatorAdminId]->admin_name;
			}

			$cost = $this->resolveOrderCostTry($order);
			$statusMeta = isset($statusMap[$orderStatusCode]) ? $statusMap[$orderStatusCode] : null;

			$paymentFallback = [11 => ['Ödeme Bekleniyor', 'warning'], 12 => ['Ödeme Başarısız', 'danger']];
			$statusColor = !empty($statusMeta->order_status_color) ? preg_replace('/[^a-z0-9_-]/i', '', $statusMeta->order_status_color) : ($paymentFallback[$orderStatusCode][1] ?? 'secondary');
			$statusName = !empty($statusMeta->order_status_name) ? html_escape($statusMeta->order_status_name) : ($paymentFallback[$orderStatusCode][0] ?? 'Bilinmiyor');
			$productLink = base_url('products/edit/' . (int)$order->order_product_id);
			$resellerLink = base_url('users/detail/' . (int)$order->order_reseller_id);
			$supplierLink = base_url('users/detail/' . (int)$order->order_supplier_id);
			$costText = number_format($cost, 2, ',', '.') . ' TL';
			$salePriceText = !empty($order->order_sale_price)
				? number_format((float)$order->order_sale_price, 2, ',', '.') . ' TL'
				: '-';

			$dt[] = array(
				$this->buildCopyIcon($orderId) . ' ' . $orderId . ($orderStatusCode === 13 ? '<br><span class="order-flag order-flag-fraud">FRAUD</span>' : ''),
				$this->buildCopyIcon($resellerOrderMain) . ' ' . html_escape($this->truncateText($resellerOrderMain, 8)) . '/' . html_escape($this->truncateText($resellerOrderDetail, 8)),
				'<div class="order-product">'
					. '<img class="order-category-img" src="' . html_escape(!empty($order->product_image) ? getenv('CDN_URL') . $order->product_image : topup_url('assets/img/no-image.jpg')) . '" width="33" height="33" loading="lazy" />'
					. '<div class="order-product-info">'
					. '<span class="order-product-cat">' . html_escape((string)$order->category_title) . '</span>'
					. '<a href="' . $productLink . '" target="_blank" rel="noopener"><strong>' . html_escape((string)$order->product_name) . '#' . (int)$order->order_product_id . '</strong></a>'
					. '<span class="order-product-qty">' . (int)$order->order_quantity . ' Adet</span>'
					. $this->buildQuantityGroupBadge($order)
					. '</div>'
					. '</div>',
				'<small>' . $this->buildRequireDataHtml($requireData) . '</small>',
				$costText,
				$salePriceText,
				'<small><a href="' . $resellerLink . '" target="_blank" rel="noopener">' . html_escape((string)$order->reseller_name) . '</a></small>',
				'<small><a href="' . $supplierLink . '" target="_blank" rel="noopener">' . html_escape((string)$order->supplier_name) . '</a></small>',
				(int)$order->order_site_id === 3 ? '<span class="badge badge-success order-channel order-channel-dijipin">Dijipin</span>' : '<span class="badge badge-info order-channel order-channel-api">API</span>',
				'<small>' . $this->formatDateTime($order->order_created_at) . '</small>',
				$this->buildAdminActionBadge($order, $currentAdminId),
				'<span ' . ($allowStatusChange && !in_array($orderStatusCode, [1, 2], true) ? 'onClick="changeStatus(' . $orderId . ')" style="cursor:pointer" ' : '') . 'class="change-status badge badge-' . $statusColor . '" data-order-status="' . $orderStatusCode . '">' . $statusName . '</span>',
				'<button class="btn btn-light btn-xs btn-rounded m-1 p-1 fs-14" onclick="orderDetail(' . $orderId . ')"><i class="fa-solid fa-magnifying-glass"></i> Detay</button> '
					. (!in_array($orderStatusCode, [1, 2], true) ? '<button class="btn btn-danger btn-xs btn-rounded m-1 p-1 fs-14" onclick="openOrderCancelModal(' . $orderId . ')"><i class="fa-solid fa-ban"></i> İptal / İade</button> ' : '')
					. ($orderStatusCode === 13 ? '<button class="btn btn-success btn-xs btn-rounded m-1 p-1 fs-14" onclick="approveFraudOrder(' . $orderId . ')"><i class="fa-solid fa-shield-check"></i> Fraud Onayla</button> ' : '')
					. '<a href="' . base_url('orders/orderInvoice/' . $orderId) . '" class="btn btn-light btn-xs btn-rounded m-1 p-1 fs-14" target="_blank" rel="noopener"><i class="fa-solid fa-receipt"></i> Dekont</a>'
					. ((int)$order->order_status === 14 ? '<button class="btn btn-success btn-xs btn-rounded m-1 p-1 fs-14" onclick="deliverOrder(' . $orderId . ')"><i class="fa-solid fa-truck-fast"></i> Teslim Et</button>' : ''),
			);
		}

		$output = array(
			'draw' => $draw,
			'recordsTotal' => $totalOrders,
			'recordsFiltered' => $totalOrders,
			'data' => !empty($dt) ? $dt : [],
		);

		return json($output);
	}

	private function orderGroupSnapshot($paymentInfo)
	{
		$info = is_string($paymentInfo) ? json_decode($paymentInfo, true) : (is_array($paymentInfo) ? $paymentInfo : null);
		$snapshot = is_array($info) && isset($info['pricing_snapshot']) && is_array($info['pricing_snapshot']) ? $info['pricing_snapshot'] : [];
		return !empty($snapshot['bundle_reference']) ? $snapshot : [];
	}

	private function buildQuantityGroupBadge($order)
	{
		$snapshot = $this->orderGroupSnapshot($order->order_payment_info ?? null);
		if (empty($snapshot['is_quantity_group'])) {
			return '';
		}
		return '<span class="badge badge-warning" title="Adetli siparişin alt siparişi">' . (int)($snapshot['quantity'] ?? 0) . ' adetli sipariş</span>';
	}

	private function buildOrderGroup($order, $paymentInfo)
	{
		$snapshot = $this->orderGroupSnapshot($paymentInfo);
		if (empty($snapshot)) {
			return null;
		}
		$reference = (string)$snapshot['bundle_reference'];
		$rows = $this->db->select('o.order_id, o.order_status, o.order_sale_price, o.order_payment_info, os.order_status_name')
			->from('orders as o')
			->join('order_status as os', 'os.order_status_code = o.order_status', 'left')
			->where('o.order_reseller_id', (int)$order->order_reseller_id)
			->like('o.order_payment_info', $reference)
			->order_by('o.order_id', 'ASC')
			->get()->result();
		$items = [];
		$total = 0.0;
		foreach ($rows as $row) {
			$rowSnapshot = $this->orderGroupSnapshot($row->order_payment_info);
			if (($rowSnapshot['bundle_reference'] ?? '') !== $reference) {
				continue;
			}
			$total += (float)$row->order_sale_price;
			$items[] = [
				'order_id' => (int)$row->order_id,
				'order_status' => (int)$row->order_status,
				'status_name' => (string)($row->order_status_name ?: 'Bilinmiyor'),
				'sale_price' => (float)$row->order_sale_price,
			];
		}
		if (count($items) < 2) {
			return null;
		}
		return [
			'is_quantity_group' => !empty($snapshot['is_quantity_group']),
			'product_name' => (string)($snapshot['bundle_product_name'] ?? ''),
			'count' => count($items),
			'total_sale_price' => round($total, 2),
			'items' => $items,
		];
	}

	public function orderDetail()
	{
		$orderID = (int)$this->uri->segment(3);
		if ($orderID <= 0) {
			return json(['status' => 'error', 'message' => 'Geçersiz sipariş kimliği']);
		}

		$order = $this->db->select("o.*, products.product_name, products.product_image, products.product_category_id, categories.category_title, u.user_name as reseller_name, uu.user_name as supplier_name, a.admin_name as operator_admin_name, os.order_status_name")
			->from('orders as o')
			->where('order_id', $orderID)
			->join('products', 'products.product_id = o.order_product_id', 'left')
			->join('categories', 'categories.category_id = products.product_category_id', 'left')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
			->join('users as uu', 'uu.user_id = o.order_supplier_id', 'left')
			->join('admins as a', 'a.admin_id = o.order_operator_admin', 'left')
			->join('order_status as os', 'os.order_status_code = o.order_status', 'left')
			->get()->row();

		if (empty($order)) {
			return json(['status' => 'error', 'message' => 'Sipariş bulunamadı']);
		}

		$couponUsage = $this->db->select('cu.coupon_usage_discount_amount, c.coupon_code')
			->from('coupon_usages as cu')
			->join('coupons as c', 'c.coupon_id = cu.coupon_usage_coupon_id', 'left')
			->where('cu.coupon_usage_order_id', $orderID)
			->order_by('cu.coupon_usage_id', 'DESC')
			->get()->row();
		$order->coupon_code = $couponUsage ? (string)($couponUsage->coupon_code ?: '-') : '-';
		$order->coupon_discount = $couponUsage ? (float)$couponUsage->coupon_usage_discount_amount : 0.0;
		$order->order_user_ip = !empty($order->order_user_ip) ? (string)$order->order_user_ip : '-';

		$orderCustomer = !empty($order->order_reseller_id)
			? $this->db->select('user_name, user_surname, user_email, user_phone')->where('user_id', (int)$order->order_reseller_id)->get('users')->row()
			: null;
		$order->customer_name = $orderCustomer ? trim($orderCustomer->user_name . ' ' . $orderCustomer->user_surname) : '';
		$order->customer_email = $orderCustomer ? (string)$orderCustomer->user_email : '';
		$order->customer_phone = $orderCustomer ? (string)$orderCustomer->user_phone : '';

		$currentAdminId = (int)($this->session->userdata('admin')['admin_id'] ?? 0);
		$islemeAl = $this->buildAdminActionButton($order, $currentAdminId);

		$pinTeslim = '';
		if ((int)$order->product_category_id === 1 && empty($order->order_voucher)) {
			$pinTeslim = '<button onclick="orderDetail(' . $order->order_id . '); orderPin(' . $order->order_id . ');" class="change-status m-3 btn btn-success" style="cursor:pointer"> Pin Tanımla</button>';
		}

		$order->order_cost_tl = !empty($order->order_cost_tl) && $order->order_cost_tl > 0 ? nFormat($order->order_cost_tl) : 'Maliyet Yok';
		$order->order_require_data = !empty($order->order_require_data) ? json_decode($order->order_require_data) : null;
		$orderData = !empty($order->order_data) ? json_decode($order->order_data, true) : [];
		$order->pricing_snapshot = is_array($orderData) && isset($orderData['pricing_snapshot'])
			? $orderData['pricing_snapshot']
			: null;
		$order->order_created_at = $this->formatDateTime($order->order_created_at);
		$order->order_readed_at = $this->formatDateTime($order->order_readed_at);
		$order->order_answered_at = $this->formatDateTime($order->order_answered_at);
		$order->order_sended_at = $this->formatDateTime($order->order_sended_at);
		$order->order_group = $this->buildOrderGroup($order, $order->order_payment_info);
		$order->order_payment_info = !empty($order->order_payment_info) ? json_decode($order->order_payment_info) : null;
		$order->api_logs = $this->db
			->select('log_id, log_controller, log_message, log_error_code, log_created_at')
			->where('log_order_id', $orderID)
			->order_by('log_id', 'ASC')
			->get('logs')
			->result_array();
		$order->order_status_name = !empty($order->order_status_name) ? $order->order_status_name : 'Bilinmiyor';
		$order->islemeAl = $islemeAl;
		$order->pinTeslim = $pinTeslim;

		return json($order);
	}

	public function lookupOrderIp()
	{
		if (!$this->requirePostRequest()) {
			return;
		}
		if (!$this->requireAdminActionCsrf()) {
			return;
		}

		$orderID = (int)$this->input->post('order_id');
		if ($orderID <= 0) {
			return json(['status' => 'error', 'message' => 'Geçersiz sipariş kimliği.'], 422);
		}

		$order = $this->db->select('order_user_ip')
			->where('order_id', $orderID)
			->get('orders')
			->row();
		if (!$order) {
			return json(['status' => 'error', 'message' => 'Sipariş bulunamadı.'], 404);
		}

		$ip = trim((string)$order->order_user_ip);
		if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			return json(['status' => 'error', 'message' => 'Bu sipariş için sorgulanabilir bir genel IP adresi bulunamadı.'], 422);
		}

		$adminID = (int)($this->session->userdata('admin')['admin_id'] ?? 0);
		if ($adminID <= 0) {
			return json(['status' => 'error', 'message' => 'Admin oturumu doğrulanamadı.'], 401);
		}

		$adminThrottle = $this->throttleHit('ipwhois_admin_' . $adminID, 50, 86400);
		if ($adminThrottle === null) {
			return json(['status' => 'error', 'message' => 'IP sorgu limiti kontrol edilemiyor. Lütfen daha sonra tekrar deneyin.'], 503);
		}
		if (!$adminThrottle) {
			return json(['status' => 'error', 'message' => 'IP sorgu limitiniz doldu. Daha sonra tekrar deneyin.'], 429);
		}

		$dailyThrottle = $this->throttleHit('ipwhois_admin_daily', 900, 86400);
		if ($dailyThrottle === null) {
			return json(['status' => 'error', 'message' => 'IP sorgu limiti kontrol edilemiyor. Lütfen daha sonra tekrar deneyin.'], 503);
		}
		if (!$dailyThrottle) {
			return json(['status' => 'error', 'message' => 'IP sorgu limiti doldu. Daha sonra tekrar deneyin.'], 429);
		}

		$curl = curl_init('https://ipwho.is/' . $ip);
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_HTTPHEADER => ['Accept: application/json'],
		]);
		$body = curl_exec($curl);
		$httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$curlErrorNumber = curl_errno($curl);
		curl_close($curl);

		if ($body === false) {
			log_message('error', 'Admin IP geolocation request failed. cURL error number: ' . $curlErrorNumber);
			return json(['status' => 'error', 'message' => 'IP sorgu servisine şu anda ulaşılamıyor.'], 502);
		}
		if ($httpCode === 429) {
			return json(['status' => 'error', 'message' => 'IP sorgu servisinin günlük limiti doldu. Daha sonra tekrar deneyin.'], 429);
		}

		$lookup = json_decode($body, true);
		if ($httpCode !== 200 || !is_array($lookup) || empty($lookup['success'])) {
			log_message('error', 'Admin IP geolocation service returned an unsuccessful response. HTTP status: ' . $httpCode);
			return json(['status' => 'error', 'message' => 'IP adresi için konum bilgisi alınamadı.'], 502);
		}

		$connection = is_array($lookup['connection'] ?? null) ? $lookup['connection'] : [];
		$timezone = is_array($lookup['timezone'] ?? null) ? $lookup['timezone'] : [];

		return json([
			'status' => 'success',
			'data' => [
				'ip' => $ip,
				'type' => (string)($lookup['type'] ?? ''),
				'country' => (string)($lookup['country'] ?? ''),
				'region' => (string)($lookup['region'] ?? ''),
				'city' => (string)($lookup['city'] ?? ''),
				'postal' => (string)($lookup['postal'] ?? ''),
				'latitude' => $lookup['latitude'] ?? null,
				'longitude' => $lookup['longitude'] ?? null,
				'isp' => (string)($connection['isp'] ?? ''),
				'organization' => (string)($connection['org'] ?? ''),
				'timezone' => (string)($timezone['id'] ?? ''),
			],
		]);
	}

	public function approveFraud()
	{
		$orderID = (int)$this->input->post('order_id');
		$decision = (string)$this->input->post('decision');
		$note = trim((string)$this->input->post('note', true));
		if ($orderID <= 0 || !in_array($decision, ['approve', 'reject'], true)) {
			return json(['status' => 'error', 'message' => 'Geçersiz istek.'], 422);
		}
		if ($note === '' || mb_strlen($note) > 1000) {
			return json(['status' => 'error', 'message' => 'Açıklama zorunludur (en fazla 1000 karakter).'], 422);
		}
		$adminID = (int)($this->session->userdata('admin')['admin_id'] ?? 0);

		if ($decision === 'reject') {
			$cur = $this->db->select('order_status')->where('order_id', $orderID)->get('orders')->row();
			if (!$cur || (int)$cur->order_status !== 13) {
				return json(['status' => 'error', 'message' => 'Sipariş fraud kontrolünde değil; durum başka bir işlemle değişmiş olabilir.'], 409);
			}
			$this->db->insert('logs', [
				'log_controller' => 'orderFraudReject',
				'log_message' => json_encode(['order_id' => $orderID, 'admin_id' => $adminID, 'note' => $note, 'ip' => getIp()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				'log_error_code' => $adminID,
				'log_order_id' => $orderID,
			]);
			// İptal + iade mevcut akışla yapılır (satır kilidi ve çift iade koruması orada)
			$_POST['order_id'] = $orderID;
			$_POST['manual_reason'] = 'Fraud reddi: ' . $note;
			return $this->cancelOrderAndRefund();
		}

		$this->db->trans_begin();
		$row = $this->db->query('SELECT order_id, order_status FROM orders WHERE order_id = ? FOR UPDATE', [$orderID])->row();
		if (!$row) {
			$this->db->trans_rollback();
			return json(['status' => 'error', 'message' => 'Sipariş bulunamadı.'], 404);
		}
		if ((int)$row->order_status !== 13) {
			$this->db->trans_rollback();
			return json(['status' => 'error', 'message' => 'Sipariş fraud kontrolünde değil; durum başka bir işlemle değişmiş olabilir.'], 409);
		}
		$this->db->where('order_id', $orderID)->where('order_status', 13)->update('orders', ['order_status' => 0]);
		$this->db->insert('logs', [
			'log_controller' => 'orderFraudApprove',
			'log_message' => json_encode(['order_id' => $orderID, 'admin_id' => $adminID, 'from' => 13, 'to' => 0, 'note' => $note, 'ip' => getIp()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'log_error_code' => $adminID,
			'log_order_id' => $orderID,
		]);
		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return json(['status' => 'error', 'message' => 'Onay işlemi kaydedilemedi.'], 500);
		}
		$this->db->trans_commit();
		return json(['status' => 'success', 'message' => 'Fraud kontrolü onaylandı; sipariş işleme alınabilir.']);
	}

	public function changeStatus() //++
	{
		$orderID = (int)$this->uri->segment(3);
		$status = (int)$this->input->post('orderStatus');
		$allowedStatuses = [0, 1, 13, 14];
		if ($orderID <= 0 || !in_array($status, $allowedStatuses, true)) {
			return json(['status' => 'error', 'message' => 'Geçersiz sipariş veya durum. İptal için "İptal / İade" kullanılmalıdır.'], 422);
		}
		$order = $this->db->select('order_id, order_status, order_operator_admin')->where('order_id', $orderID)->get('orders')->row();
		if (!$order) {
			return json(['status' => 'error', 'message' => 'Sipariş bulunamadı.'], 404);
		}
		$currentAdminID = (int)($this->session->userdata('admin')['admin_id'] ?? 0);
		if ((int)$order->order_operator_admin > 0 && (int)$order->order_operator_admin !== $currentAdminID) {
			return json(['status' => 'error', 'message' => 'Bu sipariş başka bir admin tarafından işleme alındı.'], 403);
		}
		// İptal edilmiş sipariş iade/serbest bırakma yapılmış olabilir; geri açılamaz
		if ((int)$order->order_status === 2) {
			return json(['status' => 'error', 'message' => 'İptal edilmiş sipariş yeniden açılamaz.'], 409);
		}
		$admin = $this->session->userdata('admin');
		$adminID = (int)($admin['admin_id'] ?? 0);

		$this->db->trans_start();
		$this->db->insert('logs', [
			'log_controller' => 'orderChangeStatus',
			'log_message' => json_encode([
				'status' => $status,
				'order_id' => $orderID,
				'admin_id' => $adminID,
				'ip' => getIp(),
				'port' => (int)($_SERVER['REMOTE_PORT'] ?? 0),
			], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'log_error_code' => $adminID,
			'log_order_id' => $orderID,
		]);

		if ($status === 2) {
			$userBalance = $this->db->select('*')->from('user_balance')->where('user_balance_order_id', $orderID)->get()->row();
			if (!empty($userBalance)) {
				$this->db->where('user_balance_order_id', $orderID)->update('user_balance', [
					'user_balance_status' => 4,
					'user_balance_change_date' => date('Y-m-d H:i:s')
				]);
			}

			// üründe epin varsa ve sipariş iptal ediliyorsa epinleri serbest bırak
			$orderPins = $this->db->select('*')->from('epins')->where('epin_order_id', $orderID)->get()->result();
			foreach ($orderPins as $pin) {
				$this->db->where('epin_id', $pin->epin_id)
				->update('epins', ['epin_status' => 0, 'epin_order_id' => null, 'epin_selled_at' => null, 'epin_code_decrypted' => null]);
			}
			// orderdan kaldıralım
			$this->db->where('order_id', $orderID)->update('orders', ['order_voucher' => null]);
		}
		if ($status === 1) {
			$this->db->where('coupon_usage_order_id', $orderID)->update('coupon_usages', [
				'coupon_usage_status' => 1,
				'coupon_usage_updated_at' => date('Y-m-d H:i:s'),
			]);
		}
		if ($status === 2) {
			$this->db->where('coupon_usage_order_id', $orderID)->update('coupon_usages', [
				'coupon_usage_status' => 0,
				'coupon_usage_updated_at' => date('Y-m-d H:i:s'),
			]);
		}

		$this->db->where('order_id', $orderID)->update('orders', ['order_status' => $status]);
		$this->db->trans_complete();

		if ($this->db->trans_status()) {
			return json(['status' => 'success', 'message' => 'Durum başarıyla güncellendi']);
		}

		return json(['status' => 'error', 'message' => 'Durum güncellenirken bir hata oluştu']);
	}

	public function cancelOrderAndRefund()
	{
		$orderID = (int)$this->input->post('order_id');
		$reason = trim((string)$this->input->post('reason', true));
		$manualReason = trim((string)$this->input->post('manual_reason', true));
		if ($manualReason !== '') {
			$reason = $manualReason;
		}
		if ($reason === '' && $this->db->table_exists('order_cancellation_reasons')) {
			$selected = $this->db->get_where('order_cancellation_reasons', [
				'reason_id' => (int)$this->input->post('reason_id'),
				'reason_status' => 1,
			])->row();
			$reason = $selected ? $selected->reason_text : '';
		}
		if ($orderID <= 0 || $reason === '') {
			return json(['status' => 'error', 'message' => 'Sipariş ve iptal sebebi zorunludur.'], 422);
		}

		$order = $this->db->get_where('orders', ['order_id' => $orderID])->row();
		if (!$order) {
			return json(['status' => 'error', 'message' => 'Sipariş bulunamadı.'], 404);
		}
		if ((int)$order->order_status === 2) {
			return json(['status' => 'error', 'message' => 'Sipariş zaten iptal edilmiş.'], 422);
		}
		$currentAdminID = (int)($this->session->userdata('admin')['admin_id'] ?? 0);
		if ((int)$order->order_operator_admin > 0 && (int)$order->order_operator_admin !== $currentAdminID) {
			return json(['status' => 'error', 'message' => 'Bu sipariş başka bir admin tarafından işleme alındı.'], 403);
		}

		$orderData = json_decode((string)$order->order_data, true);
		$orderData = is_array($orderData) ? $orderData : [];
		$snapshot = $orderData['pricing_snapshot'] ?? [];
		$refundAmount = (float)($snapshot['final_sale_price_tl'] ?? $order->order_sale_price_tl ?? 0);
		$adminID = (int)($this->session->userdata('admin')['admin_id'] ?? 0);
		$orderData['cancellation_reason'] = $reason;

		$this->db->trans_begin();
		// Eşzamanlı çift iptal/iadeyi engellemek için sipariş satırı kilitlenir ve durum yeniden okunur
		$locked = $this->db->query('SELECT order_status FROM orders WHERE order_id = ? FOR UPDATE', [$orderID])->row();
		if (!$locked || (int)$locked->order_status === 2) {
			$this->db->trans_rollback();
			return json(['status' => 'error', 'message' => 'Sipariş zaten iptal edilmiş.'], 409);
		}
		// 11: Ödeme Bekleniyor, 12: Ödeme Başarısız -> tahsilat yok, bakiye iadesi yapılmaz
		$isPaid = !in_array((int)$locked->order_status, [11, 12], true);
		$this->db->where('order_id', $orderID)->update('orders', [
			'order_status' => 2,
			'order_data' => json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		]);
		$refundExists = $this->db->where('user_balance_order_id', $orderID)
			->where('user_balance_status', 1)
			->where('user_balance_note', 'Sipariş iadesi')
			->count_all_results('user_balance') > 0;
		if (!$refundExists && $refundAmount > 0 && $isPaid) {
			$this->db->insert('user_balance', [
				'user_balance_user_id' => (int)$order->order_reseller_id,
				'user_balance_type' => 2,
				'user_balance_amount' => $refundAmount,
				'user_balance_status' => 1,
				'user_balance_order_id' => $orderID,
				'user_balance_note' => 'Sipariş iadesi',
				'user_balance_admin_id' => $adminID,
				'user_balance_created_at' => date('Y-m-d H:i:s'),
				'user_balance_payment_date' => date('Y-m-d H:i:s'),
			]);
		}
		$this->db->where('coupon_usage_order_id', $orderID)->update('coupon_usages', [
			'coupon_usage_status' => 0,
			'coupon_usage_updated_at' => date('Y-m-d H:i:s'),
		]);
		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return json(['status' => 'error', 'message' => 'İptal işlemi başarısız oldu.'], 500);
		}
		$this->db->trans_commit();

		return json(['status' => 'success', 'message' => 'Sipariş iptal edildi ve iade işlemi tamamlandı.']);
	}

	public function waitingOrders() //++
	{
		$data = $this->data;
		$data['title'] = 'Ödemesi Bekleyen Siparişler';
		$data['orders'] = $this->db->select('o.*, products.product_name, products.product_image, categories.category_title, u.user_name as reseller_name, uu.user_name as supplier_name')
			->from('orders as o')
			->where_in('order_status', [11, 12])
			->join('products', 'products.product_id = o.order_product_id', 'left')
			->join('categories', 'categories.category_id = products.product_category_id', 'left')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
			->join('users as uu', 'uu.user_id = o.order_supplier_id', 'left')
			->order_by('o.order_id', 'DESC')
			->limit(500)
			->get()->result();

		foreach ($data['orders'] as $order) {
			$orderData = json_decode((string)$order->order_data, true);
			$snapshot = is_array($orderData) ? ($orderData['pricing_snapshot'] ?? []) : [];
			if (!empty($snapshot['is_bundle']) && !empty($snapshot['bundle_product_name'])) {
				$order->product_name = (string)$snapshot['bundle_product_name'];
			}
		}

		$this->template->content->view('waiting-payment', $data);
		$this->template->publish();
	}

	public function orderInvoice() //++
	{
		$orderID = (int)$this->uri->segment(3);
		if ($orderID <= 0) {
			redirect(base_url('orders'));
		}

		$order = $this->db->select('o.*, products.product_name, products.product_image, categories.category_title, u.user_name as reseller_name, uu.user_name as supplier_name')
			->from('orders as o')
			->where('order_id', $orderID)
			->join('products', 'products.product_id = o.order_product_id', 'left')
			->join('categories', 'categories.category_id = products.product_category_id', 'left')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
			->join('users as uu', 'uu.user_id = o.order_supplier_id', 'left')
			->get()->row();

		if (empty($order)) {
			redirect(base_url('orders'));
		}

		$statusMap = [
			0 => ['Yeni Sipariş', 'warning'],
			1 => ['Teslim Edildi', 'success'],
			2 => ['İptal Edildi', 'danger'],
			3 => ['İşleme Alındı', 'info'],
			4 => ['Satıcı Onayladı', 'primary'],
			5 => ['Satıcı Onaylamadı', 'danger'],
			9 => ['Cron İşlemi', 'dark'],
			13 => ['Fraud Kontrolü', 'warning'],
			14 => ['Tedarikçi Değiştir', 'danger'],
			15 => ['Teslimat Bekleniyor', 'info'],
		];

		$statusMeta = isset($statusMap[(int)$order->order_status]) ? $statusMap[(int)$order->order_status] : ['Bilinmiyor', 'secondary'];
		$order->order_status_label = $statusMeta[0];
		$order->order_status_badge_class = 'badge badge-' . $statusMeta[1];

		$order->order_created_at_formatted = $this->formatDateTime($order->order_created_at) ?: '-';
		$order->order_sended_at_formatted = $this->formatDateTime($order->order_sended_at) ?: '-';

		$saleCurrency = 'TL';
		$salePrice = is_numeric($order->order_sale_price ?? null) ? (float)$order->order_sale_price : null;
		$order->sale_price_text = $salePrice !== null
			? number_format($salePrice, 2, ',', '.') . ' ' . $saleCurrency
			: '-';

		$costCurrency = 'TL';
		$costPrice = is_numeric($order->order_cost ?? null) ? (float)$order->order_cost : null;
		$order->cost_price_text = $costPrice !== null
			? number_format($costPrice, 2, ',', '.') . ' ' . $costCurrency
			: '-';

		$costTry = $this->resolveOrderCostTry($order);
		$order->cost_try_text = $costTry > 0 ? number_format($costTry, 2, ',', '.') . ' TL' : '-';
		$order->order_site_name = (int)$order->order_site_id === 3 ? 'Dijipin' : 'API';
		$order->order_quantity = max(0, (int)$order->order_quantity);

		$order->total_sale_text = '-';
		if ($salePrice !== null && $order->order_quantity > 0) {
			$order->total_sale_text = number_format($salePrice * $order->order_quantity, 2, ',', '.') . ' ' . $saleCurrency;
		}

		$order->total_cost_try_text = '-';
		if ($costTry > 0 && $order->order_quantity > 0) {
			$order->total_cost_try_text = number_format($costTry * $order->order_quantity, 2, ',', '.') . ' TL';
		}

		$order->estimated_profit_text = '-';
		if ($salePrice !== null && $order->order_quantity > 0 && $costTry > 0) {
			$estimatedProfit = ($salePrice * $order->order_quantity) - ($costTry * $order->order_quantity);
			$order->estimated_profit_text = number_format($estimatedProfit, 2, ',', '.') . ' TL';
		}

		$decodedRequireData = !empty($order->order_require_data) ? json_decode($order->order_require_data, true) : [];
		$order->order_require_data = is_array($decodedRequireData) ? $decodedRequireData : [];

		$keyMap = [
			'user_id' => 'User ID',
			'server_id' => 'Server ID',
			'zone_id' => 'Zone ID',
		];

		$order->require_data_lines = [];
		foreach ($order->order_require_data as $key => $value) {
			$label = isset($keyMap[$key]) ? $keyMap[$key] : (string)$key;

			if (is_array($value) || is_object($value)) {
				$value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}

			$normalizedValue = trim((string)$value);
			if ($normalizedValue === '') {
				$normalizedValue = '-';
			}

			$order->require_data_lines[] = [
				'label' => $label,
				'value' => $normalizedValue,
			];
		}

		$voucherData = [];
		if (!empty($order->order_voucher)) {
			$decodedVoucher = json_decode($order->order_voucher, true);
			if (is_array($decodedVoucher)) {
				$voucherData = $decodedVoucher;
			} else {
				$voucherData = preg_split('/\r\n|\r|\n/', (string)$order->order_voucher);
			}
		}

		$order->voucher_data = array_values(array_filter(array_map(static function ($voucher) {
			return trim((string)$voucher);
		}, $voucherData), static function ($voucher) {
			return $voucher !== '';
		}));
		$order->voucher_text = implode("\n", $order->voucher_data);

		$decodedPaymentInfo = !empty($order->order_payment_info) ? json_decode($order->order_payment_info, true) : [];
		$order->order_payment_info = is_array($decodedPaymentInfo) ? $decodedPaymentInfo : [];
		$order->payment_info_text = '';
		if (!empty($order->order_payment_info)) {
			$paymentInfoText = json_encode($order->order_payment_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			$order->payment_info_text = $paymentInfoText !== false ? $paymentInfoText : '';
		}

		$order->api_logs = $this->db
			->select('log_id, log_controller, log_message, log_created_at, log_error_code')
			->where('log_order_id', $orderID)
			->where('log_controller', 'order.api')
			->order_by('log_id', 'ASC')
			->limit(500)
			->get('logs')->result();

		$resellerOrderId = trim((string)$order->order_reseller_order_id);
		$resellerDetailId = trim((string)$order->order_reseller_order_detail_id);
		$order->reseller_order_ref = $resellerOrderId !== '' ? $resellerOrderId : '-';
		if ($resellerOrderId !== '' && $resellerDetailId !== '') {
			$order->reseller_order_ref .= ' / ' . $resellerDetailId;
		}

		$supplierOrderId = trim((string)$order->order_supplier_order_id);
		$order->supplier_order_ref = $supplierOrderId !== '' ? $supplierOrderId : '-';

		$resellerName = trim((string)$order->reseller_name);
		$order->reseller_name_text = $resellerName !== '' ? $resellerName : '-';

		$supplierName = trim((string)$order->supplier_name);
		$order->supplier_name_text = $supplierName !== '' ? $supplierName : '-';

		$data = $this->data;
		$data['order'] = $order;
		$this->template->content->view('invoice', $data);
		$this->template->publish();
	}

	public function stokFails() //++
	{
		$pendingStatuses = [0, 3, 4, 5, 9, 11, 12, 13, 14];

		$pools = $this->db
			->select('epin_pool_id, epin_pool_title, epin_pool_products')
			->from('epin_pool')
			->where('epin_pool_status !=', 2)
			->get()
			->result();

		if (empty($pools)) {
			return json([]);
		}

		$productPoolMap = [];
		$poolAlerts = [];

		foreach ($pools as $pool) {
			$poolId = (int)$pool->epin_pool_id;
			$productIds = json_decode($pool->epin_pool_products, true);

			if (!is_array($productIds)) {
				// Legacy/dirty rows may store pool products as comma-separated text.
				$rawProducts = trim((string)$pool->epin_pool_products);
				$rawProducts = trim($rawProducts, "[] ");
				$rawProducts = str_replace(['"', "'", "\r", "\n", "\t"], '', $rawProducts);
				$productIds = $rawProducts !== '' ? explode(',', $rawProducts) : [];
			}

			if (!is_array($productIds) || empty($productIds)) {
				continue;
			}

			$poolAlerts[$poolId] = [
				'epin_pool_id' => $poolId,
				'epin_pool_title' => (string)$pool->epin_pool_title,
				'product_name' => (string)$pool->epin_pool_title,
				'product_subtitle' => '',
				'product_image' => '',
				'waiting_order_quantity' => 0,
				'available_stock' => 0,
				'fail_count' => 0,
				'product_names' => [],
			];

			foreach ($productIds as $productId) {
				$productId = (int)trim((string)$productId);
				if ($productId <= 0 || isset($productPoolMap[$productId])) {
					continue;
				}

				$productPoolMap[$productId] = $poolId;
			}
		}

		if (empty($productPoolMap)) {
			return json([]);
		}

		$poolIds = array_values(array_unique(array_values($productPoolMap)));
		$productIds = array_keys($productPoolMap);

		$pendingOrders = $this->db
			->select('o.order_product_id, SUM(o.order_quantity) as waiting_order_quantity, MIN(products.product_name) as product_name, MIN(products.product_image) as product_image', false)
			->from('orders as o')
			->join('products', 'products.product_id = o.order_product_id', 'inner')
			->where_in('o.order_product_id', $productIds)
			->where_in('o.order_status', $pendingStatuses)
			->group_start()
			->where('o.order_voucher IS NULL', null, false)
			->or_where("TRIM(o.order_voucher) = ''", null, false)
			->or_where("TRIM(o.order_voucher) = '[]'", null, false)
			->or_where("TRIM(o.order_voucher) = '[ ]'", null, false)
			->or_where("TRIM(o.order_voucher) = 'null'", null, false)
			->or_where("TRIM(o.order_voucher) = '[null]'", null, false)
			->or_where("TRIM(o.order_voucher) = '[\"\"]'", null, false)
			->group_end()
			->group_by('o.order_product_id')
			->get()
			->result();

		$availableStocks = $this->db
			->select('epin_pool_id, COUNT(*) as available_stock', false)
			->from('epins')
			->where('epin_status', 0)
			->where_in('epin_pool_id', $poolIds)
			->group_by('epin_pool_id')
			->get()
			->result();

		foreach ($availableStocks as $stock) {
			$poolId = (int)$stock->epin_pool_id;
			if (isset($poolAlerts[$poolId])) {
				$poolAlerts[$poolId]['available_stock'] = (int)$stock->available_stock;
			}
		}

		foreach ($pendingOrders as $pendingOrder) {
			$productId = (int)$pendingOrder->order_product_id;
			if (!isset($productPoolMap[$productId])) {
				continue;
			}

			$poolId = (int)$productPoolMap[$productId];
			if (!isset($poolAlerts[$poolId])) {
				continue;
			}

			$poolAlerts[$poolId]['waiting_order_quantity'] += (int)$pendingOrder->waiting_order_quantity;

			if (empty($poolAlerts[$poolId]['product_image']) && !empty($pendingOrder->product_image)) {
				$poolAlerts[$poolId]['product_image'] = (string)$pendingOrder->product_image;
			}

			$productName = trim((string)$pendingOrder->product_name);
			if ($productName !== '' && !in_array($productName, $poolAlerts[$poolId]['product_names'], true)) {
				$poolAlerts[$poolId]['product_names'][] = $productName;
			}
		}

		$stokFails = [];
		foreach ($poolAlerts as $alert) {
			$alert['fail_count'] = max(0, (int)$alert['waiting_order_quantity'] - (int)$alert['available_stock']);
			if ($alert['fail_count'] <= 0) {
				continue;
			}

			$alert['product_subtitle'] = implode(', ', $alert['product_names']);
			unset($alert['product_names']);
			$stokFails[] = (object)$alert;
		}

		usort($stokFails, function ($left, $right) {
			$failCompare = ((int)$right->fail_count <=> (int)$left->fail_count);
			if ($failCompare !== 0) {
				return $failCompare;
			}

			return ((int)$right->waiting_order_quantity <=> (int)$left->waiting_order_quantity);
		});

		return json($stokFails);
	}

	public function orderDeliveryAlternatives() //++
	{
		$orderID = (int)$this->uri->segment(3);
		if ($orderID <= 0) {
			return json([
				'status' => 'error',
				'message' => 'Geçersiz sipariş kimliği.'
			]);
		}

		$order = $this->db->where('order_id', $orderID)->get('orders')->row();
		if (!$order) {
			return json([
				'status' => 'error',
				'message' => 'Sipariş bulunamadı.'
			]);
		}

		$alternatives = $this->db->select('*')
			->from('product_provider_alternatives')
			->where('product_provider_alternative_product_id', $order->order_product_id)
			->where('product_provider_alternative_status', 1)
			->join('provider_products', 'provider_products.provider_product_id = product_provider_alternatives.product_provider_alternative_provider_product_id', 'left')
			->join('users', 'users.user_id = provider_products.provider_product_provider_id', 'left')
			->order_by('product_provider_alternative_order_by', 'ASC')
			->get()->result();

		return json([
			'status' => 'success',
			'data' => $alternatives
		]);
	}

	public function orderProviderChange() //++
	{
		$post = $this->input->post();
		$orderID = (int)($post['orderID'] ?? 0);
		$newProviderProductID = (int)($post['newProviderProductID'] ?? 0);

		if ($orderID <= 0 || $newProviderProductID <= 0) {
			return json([
				'status' => 'error',
				'message' => 'Geçersiz sipariş veya tedarikçi ürünü.'
			]);
		}

		$order = $this->db->select('order_id')->from('orders')->where('order_id', $orderID)->get()->row();
		if (!$order) {
			return json([
				'status' => 'error',
				'message' => 'Sipariş bulunamadı.'
			]);
		}

		$providerProduct = $this->db->where('provider_product_id', $newProviderProductID)->get('provider_products')->row();
		if (!$providerProduct) {
			return json([
				'status' => 'error',
				'message' => 'Seçilen tedarikçi ürünü bulunamadı.'
			]);
		}

		$this->db->trans_start();
		$this->db->where('order_id', $orderID)->update('orders', [
			'order_supplier_id' => $providerProduct->provider_product_provider_id,
			'order_supplier_product_id' => $providerProduct->provider_product_product_id,
			'order_operator_admin' => 0,
			'order_status' => 0,
		]);
		$this->db->trans_complete();

		if ($this->db->trans_status()) {
			return json([
				'status' => 'success',
				'message' => 'Sipariş tedarikçisi başarıyla değiştirildi.'
			]);
		}

		return json([
			'status' => 'error',
			'message' => 'Sipariş tedarikçisi değiştirilirken bir hata oluştu.'
		]);
	}

	public function orderAdmin() //++
	{
		$orderID = (int)$this->uri->segment(3);
		$requestedAdminID = (int)$this->uri->segment(4);
		$currentAdminID = (int)($this->session->userdata('admin')['admin_id'] ?? 0);

		if ($orderID <= 0 || $currentAdminID <= 0) {
			return json([
				'status' => 'error',
				'message' => 'Geçersiz işlem.'
			]);
		}

		$order = $this->db->select('order_id, order_status, order_operator_admin')->from('orders')->where('order_id', $orderID)->get()->row();
		if (!$order) {
			return json([
				'status' => 'error',
				'message' => 'Sipariş bulunamadı.'
			]);
		}

		// Her parça/sipariş satırı bağımsızdır; teslim, iptal veya API'de işlenen satır manuel alınamaz
		$isManuallyHeld = (int)$order->order_status === 3 && (int)$order->order_operator_admin > 0;
		if (!((int)$order->order_status === 0 || $isManuallyHeld)) {
			return json([
				'status' => 'error',
				'message' => 'Bu sipariş satırı şu an işleme alınamaz veya bırakılamaz.'
			], 409);
		}

		$adminID = $requestedAdminID === 0 ? 0 : $currentAdminID;
		if ($adminID === 0 && !empty($order->order_operator_admin) && (int)$order->order_operator_admin !== $currentAdminID) {
			return json([
				'status' => 'error',
				'message' => 'Bu siparişi sadece işleme alan admin bırakabilir.'
			]);
		}

		$orderStatus = $adminID === 0 ? 0 : 3;
		$this->db->where('order_id', $orderID)
			->where('order_status', (int)$order->order_status)
			->where('order_operator_admin', (int)$order->order_operator_admin)
			->update('orders', [
				'order_operator_admin' => $adminID,
				'order_status' => $orderStatus,
			]);
		if ($this->db->affected_rows() < 1) {
			return json([
				'status' => 'error',
				'message' => 'Sipariş durumu değişti, sayfayı yenileyip tekrar deneyin.'
			], 409);
		}

		$message = $adminID === 0 ? 'İşlem bırakıldı.' : 'Sipariş işleme alındı.';

		return json([
			'status' => 'success',
			'message' => $message
		]);
	}

	private function resolveStatusArray($uriSegment)  //--
	{
		if ($uriSegment === 2) {
			return [0, 3, 4, 5, 9, 13, 14, 15];
		}

		return [0, 1, 2, 3, 4, 5, 9, 11, 12, 13, 14, 15];
	}

	private function normalizeDate($date)  //--
	{
		$date = trim((string)$date);
		if ($date === '') {
			return null;
		}

		$timestamp = strtotime($date);
		if ($timestamp === false) {
			return null;
		}

		return date('Y-m-d', $timestamp);
	}

	private function formatDateTime($dateTime) //--
	{
		if (empty($dateTime)) {
			return null;
		}

		$timestamp = strtotime($dateTime);
		if ($timestamp === false) {
			return null;
		}

		return date('d.m.Y H:i:s', $timestamp);
	}

	private function truncateText($text, $maxLength = 30) //--
	{
		$text = (string)$text;
		if (function_exists('mb_strlen') && function_exists('mb_substr')) {
			if (mb_strlen($text, 'UTF-8') > $maxLength) {
				return mb_substr($text, 0, $maxLength, 'UTF-8') . '...';
			}
			return $text;
		}

		if (strlen($text) > $maxLength) {
			return substr($text, 0, $maxLength) . '...';
		}

		return $text;
	}

	private function buildCopyIcon($value) //--
	{
		$encoded = json_encode((string)$value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
		return '<i class="fa-solid fa-copy fs-12 copy-value" onClick=\'copy(' . $encoded . ')\' title="Kopyala"></i>';
	}

	private function buildRequireDataHtml(array $requireData)
	{
		if (empty($requireData)) {
			return '-';
		}

		$requireText = '';
		foreach ($requireData as $key => $value) {
			$normalizedKey = $this->normalizeRequireDataKey((string)$key);
			$formattedValue = is_array($value) || is_object($value)
				? json_encode($value, JSON_UNESCAPED_UNICODE)
				: (string)$value;

			if ($formattedValue === false || $formattedValue === null) {
				$formattedValue = '-';
			}

			$requireText .= '<span class="fs-12">' . html_escape($normalizedKey) . ' : ' . html_escape($formattedValue) . '</span> '
				. $this->buildCopyIcon($formattedValue)
				. '<br>';
		}

		return $requireText;
	}

	private function normalizeRequireDataKey($key)
	{
		$keyMap = [
			'user_id' => 'UserID',
			'server_id' => 'Server',
			'zone_id' => 'Zone',
		];

		return isset($keyMap[$key]) ? $keyMap[$key] : $key;
	}

	private function buildAdminActionBadge($order, $currentAdminId)
	{
		if ($currentAdminId <= 0) {
			return '';
		}

		if (!in_array((int)$order->order_status, [0, 3], true)) {
			return '';
		}

		if ((int)$order->order_status === 3 && empty($order->order_operator_admin)) {
			return '';
		}

		$orderID = (int)$order->order_id;
		$action = '<span onClick="orderAdmin(' . $orderID . ',' . $currentAdminId . ')" class="change-status badge badge-success" style="cursor:pointer"> İşleme Al</span>';

		if (!empty($order->order_operator_admin)) {
			$operatorAdminId = (int)$order->order_operator_admin;
			$operatorAdminName = !empty($order->operator_admin_name) ? html_escape($order->operator_admin_name) : 'Başka Bir Admin';

			if ($operatorAdminId === $currentAdminId) {
				$action = '<span onClick="orderAdmin(' . $orderID . ',0)" class="change-status badge badge-danger" style="cursor:pointer"> İşlemi Bırak</span>';
				$action .= '<br><small>Senin tarafından işleme alındı</small>';
			} else {
				$action = '<span onClick="orderAdmin(' . $orderID . ',' . $currentAdminId . ')" class="change-status badge badge-info" style="cursor:pointer"> İşlemi Kendine Al</span>';
				$action .= '<br><small>' . $operatorAdminName . ' tarafından işleme alındı</small>';
			}
		}

		return $action;
	}

	private function buildAdminActionButton($order, $currentAdminId)
	{
		if ($currentAdminId <= 0) {
			return '';
		}

		if (!in_array((int)$order->order_status, [0, 3], true)) {
			return '';
		}

		if ((int)$order->order_status === 3 && empty($order->order_operator_admin)) {
			return '';
		}

		$orderID = (int)$order->order_id;
		$action = '<button onclick="orderDetail(' . $orderID . '); orderAdmin(' . $orderID . ',' . $currentAdminId . ');" class="change-status m-3 btn btn-success" style="cursor:pointer"> İşleme Al</button>';

		if (!empty($order->order_operator_admin)) {
			$operatorAdminId = (int)$order->order_operator_admin;
			$operatorAdminName = !empty($order->operator_admin_name) ? html_escape($order->operator_admin_name) : 'Başka Bir Admin';

			if ($operatorAdminId === $currentAdminId) {
				$action = '<button onclick="orderDetail(' . $orderID . '); orderAdmin(' . $orderID . ',0);" class="change-status m-3 btn btn-danger" style="cursor:pointer"> İşlemi Bırak</button>';
				$action .= '<br><small>Senin tarafından işleme alındı</small>';
			} else {
				$action = '<button onclick="orderDetail(' . $orderID . '); orderAdmin(' . $orderID . ',' . $currentAdminId . ');" class="change-status m-3 btn btn-info" style="cursor:pointer"> İşlemi Kendine Al</button>';
				$action .= '<br><small>' . $operatorAdminName . ' tarafından işleme alındı</small>';
			}
		}

		return $action;
	}

	private function resolveOrderCostTry($order)
	{
		if (isset($order->order_cost_tl) && is_numeric($order->order_cost_tl) && (float)$order->order_cost_tl > 0) {
			return (float)$order->order_cost_tl;
		}

		$cost = is_numeric($order->order_cost ?? null) ? (float)$order->order_cost : 0.0;
		if ($cost <= 0) {
			return 0.0;
		}

		$currency = trim((string)($order->order_cost_currency ?? ''));
		if ($currency === '' || strcasecmp($currency, 'TRY') === 0 || strcasecmp($currency, 'TL') === 0) {
			return $cost;
		}

		$rateKey = function_exists('tr_strtolower') ? tr_strtolower($currency) : strtolower($currency);
		$rates = $this->getCurrencyRates();
		$rate = isset($rates[$rateKey]) && is_numeric($rates[$rateKey]) ? (float)$rates[$rateKey] : 0.0;

		if ($rate <= 0) {
			return 0.0;
		}

		return (float)number_format($cost * $rate, 2, '.', '');
	}

	private function getCurrencyRates()
	{
		if (is_array($this->currencyRates)) {
			return $this->currencyRates;
		}

		$currencyRow = $this->db->select('currencies_json')
			->from('currency')
			->where('currencies_date', date('Y-m-d'))
			->get()
			->row();

		if (empty($currencyRow) || empty($currencyRow->currencies_json)) {
			$this->currencyRates = [];
			return $this->currencyRates;
		}

		$decoded = json_decode($currencyRow->currencies_json, true);
		$this->currencyRates = is_array($decoded) ? array_change_key_case($decoded, CASE_LOWER) : [];

		return $this->currencyRates;
	}

	private function getAdminMap(array $adminIds)
	{
		$adminIds = array_values(array_unique(array_filter(array_map('intval', $adminIds))));
		if (empty($adminIds)) {
			return [];
		}

		$rows = $this->db->select('admin_id, admin_name')
			->from('admins')
			->where_in('admin_id', $adminIds)
			->get()
			->result();

		$map = [];
		foreach ($rows as $row) {
			$map[(int)$row->admin_id] = $row;
		}

		return $map;
	}

	private function getOrderStatusMap(array $statusCodes)
	{
		$statusCodes = array_values(array_unique(array_map('intval', $statusCodes)));
		if (empty($statusCodes)) {
			return [];
		}

		$rows = $this->db->select('order_status_code, order_status_name, order_status_color')
			->from('order_status')
			->where_in('order_status_code', $statusCodes)
			->get()
			->result();

		$map = [];
		foreach ($rows as $row) {
			$map[(int)$row->order_status_code] = $row;
		}

		return $map;
	}

	private function extractOrderIntValues(array $orders, $property)
	{
		$values = [];
		foreach ($orders as $order) {
			if (is_object($order) && isset($order->$property)) {
				$values[] = (int)$order->$property;
			}
		}
		return $values;
	}

	public function orderContract($orderID, $type = 'distance-sales-agreement')
	{
		$orderID = (int)$orderID;
		$order = $this->db->select('o.*, p.product_name, c.category_title, u.user_name, u.user_email, u.user_phone, u.user_bill_company_name, u.user_bill_address, u.user_bill_city, u.user_bill_district, u.user_bill_tax_office, u.user_bill_tax_number')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('categories as c', 'c.category_id = p.product_category_id', 'left')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'left')
			->where('o.order_id', $orderID)
			->get()->row();

		if (empty($order)) {
			die("Order not found or empty");
		}

		$viewPath = APPPATH . '../../app/modules/homee/views/static_pages/' . $type . '.php';
		if (!file_exists($viewPath)) {
			die("File not found: " . $viewPath);
		}

		$content = file_get_contents($viewPath);

		// Basic replacements
		$replacements = [
			'{{alici_ad_soyad}}' => !empty($order->user_name) ? trim($order->user_name) : '-',
			'{{alici_tckn_vkn}}' => !empty($order->user_bill_tax_number) ? trim($order->user_bill_tax_number) : '-',
			'{{alici_fatura_adresi}}' => !empty($order->user_bill_address) ? trim($order->user_bill_address) : '-',
			'{{alici_telefon}}' => !empty($order->user_phone) ? $order->user_phone : '-',
			'{{alici_eposta}}' => !empty($order->user_email) ? $order->user_email : '-',
			'{{islem_ip_adresi}}' => !empty($order->order_user_ip) ? $order->order_user_ip : '-',
			'{{onay_tarih_saati}}' => date('d.m.Y H:i:s', strtotime($order->order_created_at))
		];

		$content = str_replace(array_keys($replacements), array_values($replacements), $content);

		// Format numbers
		$salePrice = is_numeric($order->order_sale_price) ? (float)$order->order_sale_price : 0;
		$qty = max(0, (int)$order->order_quantity);
		$totalSale = $salePrice * $qty;
		
		$salePriceFormatted = number_format($salePrice, 2, ',', '.') . ' ' . $order->order_sale_price_currency;
		$totalSaleFormatted = number_format($totalSale, 2, ',', '.') . ' ' . $order->order_sale_price_currency;

		$trHtml = '<tr>
			<td>' . html_escape($order->product_name . ' - ' . $order->category_title) . '</td>
			<td>' . $qty . '</td>
			<td>' . $salePriceFormatted . '</td>
			<td>' . $totalSaleFormatted . '</td>
		</tr>';

		// Replace the tr loop HTML
		$patternTr = '/<!-- YAZILIMCI NOTU:.*?-->.*?<!-- DÖNGÜ SONU -->/s';
		$content = preg_replace($patternTr, $trHtml, $content);

		// Replace genel toplam
		$content = preg_replace('/(<td colspan="3" style="text-align: right;" class="bold">Genel Toplam \(KDV Dahil\):<\/td>\s*<td class="bold">)(.*?)(<\/td>)/s', '${1}' . $totalSaleFormatted . '${3}', $content);

		// Replace ödeme yöntemi
		$paymentMethod = !empty($order->order_payment_method) ? $order->order_payment_method : 'Sistem Bakiyesi';
		$content = preg_replace('/(<td colspan="3" style="text-align: right;" class="bold">Ödeme Yöntemi:<\/td>\s*<td class="bold">)(.*?)(<\/td>)/s', '${1}' . html_escape($paymentMethod) . '${3}', $content);

		$data['content'] = $content;
		$data['title'] = $type == 'distance-sales-agreement' ? 'Mesafeli Satış Sözleşmesi' : 'İade Politikası';
		
		$this->load->view('contract-view', $data);
	}
}
