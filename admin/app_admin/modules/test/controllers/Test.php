<?php defined('BASEPATH') or exit('No direct script access allowed');

class Test extends MY_Controller
{
	public $data = array();
	public function __construct()
	{

		userCheck();
		parent::__construct();
		$this->load->model('VtModel');
		$this->data['locales'] = $this->locales();
	}

	public function mlbbQuantityReport()
	{
		$providerId = '9132'; // Moonton MLBB Provider ID
		$get = $this->input->get();
		if (!empty($get['startDate'])) {
			$reportStart = $get['startDate'] . ' 00:00:00';
			$reportEnd = $get['endDate'] . ' 23:59:59';
			$exchangeRate = isset($get['exchangeRate']) ? floatval($get['exchangeRate']) : 43.40;
		} else {
			$reportStart = '2025-11-11 00:00:00';
			$reportEnd = date('Y-m-d H:i:s');
			$exchangeRate = 43.40;
		}

		// 1. İlgili ürün ID’lerini al
		$productRows = $this->db->select('provider_product_id, p.product_id')
			->from('provider_products')
			->where('provider_product_provider_id', $providerId)
			->join('products as p', 'p.product_provider_id = provider_product_id', 'left')
			->get()
			->result();

		$productIDs = array_column($productRows, 'product_id');

		if (empty($productIDs)) {
			echo '<p>No products found for provider ' . htmlspecialchars($providerId) . '.</p>';
			return;
		}

		// 2. Siparişleri çek
		$orders = $this->db
			->select('
			o.order_id,
            o.order_cost_tl,
            o.order_require_data as requires,
            o.order_supplier_order_id as transactionID,
            o.order_created_at as orderDatetime,
            p.product_name as productName,
            pp.provider_product_product_id as providerProductID
        ')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('provider_products as pp', 'pp.provider_product_id = p.product_provider_id', 'left')
			->where_in('o.order_product_id', $productIDs)
			->where('o.order_status', 1)
			->where('o.order_created_at >=', $reportStart)
			->where('o.order_created_at <=', $reportEnd)
			//->order_by('o.order_created_at', 'ASC')
			->get()
			->result();

		if (empty($orders)) {
			echo '<p>No completed orders found in the given date range.</p>';
			return;
		}

		// first order transactionID
		$firstOrder = $orders[0];
		//pre($firstOrder);
		$lastOrder = end($orders);
		//pre($lastOrder);die;

		// 3. Rapor verilerini hazırla
		$quantityReport = [];
		$productCostTotal = [];
		$totalCost = 0;

		foreach ($orders as $order) {
			$productName = str_replace('Elmas', 'Diamonds', $order->productName);
			$productLabel = $productName . ' (' . $order->providerProductID . ')';

			if (!isset($quantityReport[$productLabel])) {
				$quantityReport[$productLabel] = 0;
				$productCostTotal[$productLabel] = 0;
			}

			$quantityReport[$productLabel]++;
			$productCostTotal[$productLabel] += $order->order_cost_tl;
			$totalCost += $order->order_cost_tl;
		}

		// 4. Satış miktarına göre sırala (azalan)
		//arsort($quantityReport);

		// date start-end select inputs and submit button
		echo '<form method="get" style="margin-bottom:20px;">';
		echo '<label for="date">Start Date: </label>';
		echo '<input type="date" id="date" name="startDate" value="' . htmlspecialchars($get['startDate'] ?? '') . '">';
		echo '<label for="endDate">End Date: </label>';
		echo '<input type="date" id="endDate" name="endDate" value="' . htmlspecialchars($get['endDate'] ?? '') . '">';
		echo '<label for="exchangeRate" style="margin-left:20px;">Ziraat USD/TRY Rate: </label>';
		echo '<input type="text" id="exchangeRate" name="exchangeRate" value="' . htmlspecialchars($get['exchangeRate'] ?? '43.40') . '">';
		echo '<button type="submit">Generate Report</button>';
		echo '</label>';
		// kur input

		echo '</form>';
		// 5. HTML çıktısı oluştur
		echo '<h2 style="font-family:Arial; color:#333;">Moonton MLBB Product Quantity Report</h2>';
		echo '<p><strong>Report Period:</strong> ' . htmlspecialchars($reportStart) . ' → ' . htmlspecialchars($reportEnd) . '</p>';
		echo '<p><strong>Total Orders:</strong> ' . count($orders) . ' | ';
		echo '<strong>Total Quantity Sold:</strong> ' . array_sum($quantityReport) . ' | ';
		echo '<strong>Total Cost:</strong> ' . number_format($totalCost, 2, ',', '.') . ' TL</p>';

		echo '<p><b>First Order Transaction ID:</b> ' . htmlspecialchars($firstOrder->transactionID) . ' | DateTime: ' . htmlspecialchars($firstOrder->orderDatetime) . '</p>';
		echo '<p><b>Last Order Transaction ID:</b> ' . htmlspecialchars($lastOrder->transactionID) . ' | DateTime: ' . htmlspecialchars($lastOrder->orderDatetime) . '</p>';
		echo '<a href="' . base_url('test/exportMoontonMlbbReport?' . http_build_query($this->input->get())) . '" class="btn btn-primary mb-3">Download Excel</a>';

		echo '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width:50%; font-family:Arial; font-size:14px;">';
		echo '<thead style="background:#f2f2f2; font-weight:bold; text-align:center;">';
		echo '<tr>';
		echo '<th style="width:50%; text-align:left;">Product Name</th>';
		echo '<th style="width:15%;">Quantity Sold</th>';
		echo '<th style="width:20%;">Total Cost (TL)</th>';
		echo '<th style="width:15%;">Discounted Cost (TL) -%18</th>';
		echo '</tr>';
		echo '</thead><tbody>';

		foreach ($quantityReport as $productName => $quantity) {
			$totalProductCost = $productCostTotal[$productName];
			$avgCost = $quantity > 0 ? ($totalProductCost / $quantity) : 0;

			echo '<tr>';
			echo '<td>' . htmlspecialchars($productName) . '</td>';
			echo '<td style="text-align:center;">' . $quantity . '</td>';
			echo '<td style="text-align:right;">' . number_format($totalProductCost, 2, ',', '.') . '</td>';
			echo '<td style="text-align:right;">' . number_format($totalProductCost * 0.82, 2, ',', '.') . '</td>';
			echo '</tr>';
		}

		// Toplam satırı
		echo '<tr style="background:#fafafa; font-weight:bold;">';
		echo '<td style="text-align:right;">TOTAL</td>';
		echo '<td style="text-align:center;">' . array_sum($quantityReport) . '</td>';
		echo '<td style="text-align:right;">' . number_format($totalCost, 2, ',', '.') . '</td>';
		echo '<td style="text-align:right;">' . number_format($totalCost * 0.82, 2, ',', '.') . '</td>';
		echo '</tr>';

		//kaç dolar tuttu
		echo '</tbody></table>';
		$odenecekDolar =  currencyCalculatorTo('USD', $totalCost);
		$odenecekDolarKurFarkli = $totalCost / $exchangeRate;
		// style ile yaz 
		echo '<h3 style="font-family:Arial; color:#333;">Total Payable in USD (using fixed rate ' . $exchangeRate . ' Ziraat): $' . number_format($odenecekDolarKurFarkli / 1.02 * 0.82, 4, ',', '.') . '</h3>';
		//echo '<h3 style="font-family:Arial; color:#333;">Total Payable in USD (after 18% discount): $' . number_format($odenecekDolar * 0.82, 2, ',', '.') . '</h3>';
	}

	public function exportMoontonMlbbReport()
	{

		$get = $this->input->get();
		if (!empty($get['startDate'])) {
			$reportStart = $get['startDate'] . ' 00:00:00';
			$reportEnd = $get['endDate'] . ' 23:59:59';
		} else {
			$reportStart = '2025-11-11 00:00:00';
			$reportEnd = date('Y-m-d H:i:s');
		}
		// Moonton MLBB ürünlerini çek
		$productIDS = $this->db->select('provider_product_id, p.product_id')
			->from('provider_products')
			->where('provider_product_provider_id', '9132') // Moonton MLBB
			->join('products as p', 'p.product_provider_id = provider_product_id', 'left')
			->get()
			->result();

		$productIDS = array_map(function ($item) {
			return $item->product_id;
		}, $productIDS);

		if (empty($productIDS)) {
			echo "Ürün bulunamadı.";
			return;
		}

		// İlgili siparişleri çek
		$orders = $this->db
			->select('o.order_require_data as requires, o.order_supplier_order_id as transactionID, o.order_created_at as orderDatetime, p.product_name as productName')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->where_in('o.order_product_id', $productIDS)
			->where('o.order_status', 1)
			->where('o.order_created_at >=', $reportStart)
			->where('o.order_created_at <=', $reportEnd)
			->order_by('o.order_created_at', 'ASC')
			->get()
			->result();

		if (empty($orders)) {
			echo "Raporlanacak sipariş bulunamadı.";
			return;
		}

		// Dosya adı
		$filename = 'moonton_mlbb_report_' . date('Y-m-d_H-i-s') . '.csv';

		// Header’lar (dosya indirilsin)
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename);

		// Output stream aç
		$output = fopen('php://output', 'w');

		// Başlık satırı
		fputcsv($output, ['Transaction ID', 'Product Name', 'Requires Data', 'Order Datetime']);

		// Verileri yaz
		foreach ($orders as $order) {
			fputcsv($output, [
				$order->transactionID,
				$order->productName,
				$order->requires,
				$order->orderDatetime
			]);
		}

		fclose($output);
		exit;
	}

	public function mcggQuantityReport()
	{
		$data = $this->data;
		$providerId = '9156'; // Moonton MLBB Provider ID
		$get = $this->input->get();
		if (!empty($get['startDate'])) {
			$reportStart = $get['startDate'] . ' 00:00:00';
			$reportEnd = $get['endDate'] . ' 23:59:59';
			$exchangeRate = isset($get['exchangeRate']) ? floatval($get['exchangeRate']) : 43.40;
		} else {
			$reportStart = '2025-11-11 00:00:00';
			$reportEnd = date('Y-m-d H:i:s');
			$exchangeRate = 43.40;
		}

		// 1. İlgili ürün ID’lerini al
		$productRows = $this->db->select('provider_product_id, p.product_id')
			->from('provider_products')
			->where('provider_product_provider_id', $providerId)
			->join('products as p', 'p.product_provider_id = provider_product_id', 'left')
			->get()
			->result();

		$productIDs = array_column($productRows, 'product_id');

		if (empty($productIDs)) {
			echo '<p>No products found for provider ' . htmlspecialchars($providerId) . '.</p>';
			return;
		}

		// 2. Siparişleri çek
		$orders = $this->db
			->select('
			o.order_id,
            o.order_cost_tl,
            o.order_require_data as requires,
            o.order_supplier_order_id as transactionID,
            o.order_created_at as orderDatetime,
            p.product_name as productName,
            pp.provider_product_product_id as providerProductID
        ')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('provider_products as pp', 'pp.provider_product_id = p.product_provider_id', 'left')
			->where_in('o.order_product_id', $productIDs)
			->where('o.order_status', 1)
			->where('o.order_created_at >=', $reportStart)
			->where('o.order_created_at <=', $reportEnd)
			//->order_by('o.order_created_at', 'ASC')
			->get()
			->result();

		if (empty($orders)) {
			echo '<p>No completed orders found in the given date range.</p>';
			return;
		}

		// first order transactionID
		$firstOrder = $orders[0];
		//pre($firstOrder);
		$lastOrder = end($orders);
		//pre($lastOrder);die;

		// 3. Rapor verilerini hazırla
		$quantityReport = [];
		$productCostTotal = [];
		$totalCost = 0;

		foreach ($orders as $order) {
			$productName = str_replace('Elmas', 'Diamonds', $order->productName);
			$productLabel = $productName . ' (' . $order->providerProductID . ')';

			if (!isset($quantityReport[$productLabel])) {
				$quantityReport[$productLabel] = 0;
				$productCostTotal[$productLabel] = 0;
			}

			$quantityReport[$productLabel]++;
			$productCostTotal[$productLabel] += $order->order_cost_tl;
			$totalCost += $order->order_cost_tl;
		}

		// 4. Satış miktarına göre sırala (azalan)
		//arsort($quantityReport);

		// date start-end select inputs and submit button
		echo '<form method="get" style="margin-bottom:20px;">';
		echo '<label for="date">Start Date: </label>';
		echo '<input type="date" id="date" name="startDate" value="' . htmlspecialchars($get['startDate'] ?? '') . '">';
		echo '<label for="endDate">End Date: </label>';
		echo '<input type="date" id="endDate" name="endDate" value="' . htmlspecialchars($get['endDate'] ?? '') . '">';
		echo '<label for="exchangeRate" style="margin-left:20px;">Ziraat USD/TRY Rate: </label>';
		echo '<input type="text" id="exchangeRate" name="exchangeRate" value="' . htmlspecialchars($get['exchangeRate'] ?? '43.40') . '">';
		echo '<button type="submit">Generate Report</button>';
		echo '</label>';
		// kur input

		echo '</form>';
		// 5. HTML çıktısı oluştur
		echo '<h2 style="font-family:Arial; color:#333;">Moonton MLBB Product Quantity Report</h2>';
		echo '<p><strong>Report Period:</strong> ' . htmlspecialchars($reportStart) . ' → ' . htmlspecialchars($reportEnd) . '</p>';
		echo '<p><strong>Total Orders:</strong> ' . count($orders) . ' | ';
		echo '<strong>Total Quantity Sold:</strong> ' . array_sum($quantityReport) . ' | ';
		echo '<strong>Total Cost:</strong> ' . number_format($totalCost, 2, ',', '.') . ' TL</p>';

		echo '<p><b>First Order Transaction ID:</b> ' . htmlspecialchars($firstOrder->transactionID) . ' | DateTime: ' . htmlspecialchars($firstOrder->orderDatetime) . '</p>';
		echo '<p><b>Last Order Transaction ID:</b> ' . htmlspecialchars($lastOrder->transactionID) . ' | DateTime: ' . htmlspecialchars($lastOrder->orderDatetime) . '</p>';
		echo '<a href="' . base_url('test/exportMoontonMlbbReport?' . http_build_query($this->input->get())) . '" class="btn btn-primary mb-3">Download Excel</a>';

		echo '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width:50%; font-family:Arial; font-size:14px;">';
		echo '<thead style="background:#f2f2f2; font-weight:bold; text-align:center;">';
		echo '<tr>';
		echo '<th style="width:50%; text-align:left;">Product Name</th>';
		echo '<th style="width:15%;">Quantity Sold</th>';
		echo '<th style="width:20%;">Total Cost (TL)</th>';
		echo '<th style="width:15%;">Discounted Cost (TL) -%18</th>';
		echo '</tr>';
		echo '</thead><tbody>';

		foreach ($quantityReport as $productName => $quantity) {
			$totalProductCost = $productCostTotal[$productName];
			$avgCost = $quantity > 0 ? ($totalProductCost / $quantity) : 0;

			echo '<tr>';
			echo '<td>' . htmlspecialchars($productName) . '</td>';
			echo '<td style="text-align:center;">' . $quantity . '</td>';
			echo '<td style="text-align:right;">' . number_format($totalProductCost, 2, ',', '.') . '</td>';
			echo '<td style="text-align:right;">' . number_format($totalProductCost * 0.82, 2, ',', '.') . '</td>';
			echo '</tr>';
		}

		// Toplam satırı
		echo '<tr style="background:#fafafa; font-weight:bold;">';
		echo '<td style="text-align:right;">TOTAL</td>';
		echo '<td style="text-align:center;">' . array_sum($quantityReport) . '</td>';
		echo '<td style="text-align:right;">' . number_format($totalCost, 2, ',', '.') . '</td>';
		echo '<td style="text-align:right;">' . number_format($totalCost * 0.82, 2, ',', '.') . '</td>';
		echo '</tr>';

		//kaç dolar tuttu
		echo '</tbody></table>';
		$odenecekDolar =  currencyCalculatorTo('USD', $totalCost);
		$odenecekDolarKurFarkli = $totalCost / $exchangeRate;
		// style ile yaz 
		echo '<h3 style="font-family:Arial; color:#333;">Total Payable in USD (using fixed rate ' . $exchangeRate . ' Ziraat): $' . number_format($odenecekDolarKurFarkli / 1.02 * 0.82, 4, ',', '.') . '</h3>';
		//echo '<h3 style="font-family:Arial; color:#333;">Total Payable in USD (after 18% discount): $' . number_format($odenecekDolar * 0.82, 2, ',', '.') . '</h3>';

		//burada yayınla
		$this->template->content->view('report', $data);
		$this->template->publish();
	}

	public function vomsisAuth()
	{
		$curl = curl_init();

		$curl = curl_init();
		curl_setopt_array($curl, array(CURLOPT_URL => 'https://developers.vomsis.com/api/v2/authenticate', CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'POST', CURLOPT_POSTFIELDS => 'app_key=Q21WeldYbHl6bENKQWpPTDQvRnZIcTdwUVNoOTJWU0ZXWnQ2UytpYmQzaz06OrIYtnruajvyZJWdvQmQXDY%3D&app_secret=UXZmMFE3TzdDR2UvUjNFR1VsNDRaTTVMQlFJVzJENG9uNyttQlBQT1lwdz06OjX6tMHTGfqYLOPjA2adZgE%3D', CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),));

		$response = curl_exec($curl);
		if ($response === false) {
			$error = curl_error($curl);
			curl_close($curl);
			die('Curl error: ' . $error);
		}

		curl_close($curl);

		$decoded = json_decode($response, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			die('JSON decode error: ' . json_last_error_msg() . "\nRaw response: " . $response);
		}
		if ($decoded['status'] != 'success') {
			die('API error: ' . ($decoded['message'] ?? 'Unknown error') . "\nResponse: " . $response);
		}
		return $decoded['token'];
	}

	public function vomsisTransactions()
	{
		$curl = curl_init();
		// şu andan önceki 1 dakika ile şu andan sonraki 1 dakika arasındaki işlemleri çekelim
		$begindate = date('d-m-Y H:i:s', strtotime('-6000 minute'));
		$enddate = date('d-m-Y H:i:s');

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://developers.vomsis.com/api/v2/transactions?beginDate=' . urlencode($begindate) . '&endDate=' . urlencode($enddate),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Accept: application/json',
				'Authorization: Bearer ' . $this->vomsisAuth()
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		//echo $response;
		$response = json_decode($response, true);
		return $response;
	}

	public function vomsisAccounts()
	{
		$this->load->library('Vomsis');
		$vomsis = new Vomsis();
		$token = $vomsis->vomsisAuth();
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => 'https://developers.vomsis.com/api/v2/accounts',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				'Accept: application/json',
				'Authorization: Bearer ' . $token
			],
		]);

		$response = curl_exec($curl);
		curl_close($curl);

		$response = json_decode($response, true);

		$data = [];
		$totalBalance = 0;

		foreach ($response['accounts'] as $acc) {

			$balance = (float) $acc['balance'];
			$totalBalance += $balance;

			$data[] = [
				'bank'     => $acc['bank']['bank_title'] ?? '-',
				'branch'   => $acc['branch_name'],
				'currency' => $acc['fec_name'],
				'account'  => $acc['account_number'],
				'iban'     => $acc['iban'] ?: $acc['custom_iban'],
				'balance'  => number_format($balance, 2, ',', '.'),
				'status'   => $acc['status'] == 1 ? 'Aktif' : 'Pasif',
				'created'  => $acc['created_at'],
			];
		}

		echo json_encode([
			"data" => $data,
			"total_balance" => number_format($totalBalance, 2, ',', '.')
		]);
	}

	public function vomsisAcc()
	{
		$data = $this->data;
		$data['title'] = 'Vomsis Banka Hesaplarım';
		$data['description'] = 'Vomsis API ile bağlı banka hesaplarınızın listesini ve toplam bakiyenizi görebilirsiniz.';
		$this->template->content->view('vomsisAccounts', $data);
		$this->template->publish();
	}

	public function balanceAutoApproveWithVomsis()
	{
		// get user balance requests that are pending
		$pendingBalances = $this->db->select('*')
			->from('user_balance')
			->where('user_balance_status', 0)
			->where('user_balance_vomsis_payment_id IS NULL', null, false)
			->get()
			->result();
		if (!empty($pendingBalances)) {
			foreach ($pendingBalances as $balance) {
				$refNo = $balance->user_balance_reference_no;
				$vomsisResponse = $this->vomsisTransactions($refNo)['transactions'] ?? [];

				// array search $vomsisResponse in [description] for $refNo inside strPos
				$found = false;
				$transactionData = [];
				foreach ($vomsisResponse as $transaction) {
					if (strpos($transaction['description'], $refNo) !== false) {
						$found = true;
						$transactionData[] = $transaction;
						break;
					}
				}
				if ($found) {
					pre($balance);
					pre($transactionData);
					$paymentID = $transactionData[0]['id'] ?? null;
					$bankName = $transactionData[0]['account']['bank']['bank_name'] ?? null;
					echo "Reference No: $refNo - Vomsis Payment ID: $paymentID - Bank Name: $bankName	\n";
					$bank = $this->db->select('bank_id, bank_name')->from('banks')
						->where('bank_title', $bankName)->get()->row();
					$bankID = $bank->bank_id ?? 0;
					if ($paymentID && $bankID) {
						// paymentID ile daha önce sipariş işlenmiş mi kontrol et
						$existing = $this->db->select('*')->from('user_balance')
							->where('user_balance_vomsis_payment_id', $paymentID)
							->get()->row();
						if ($existing) {
							echo "Payment ID: $paymentID has already been processed for Balance ID: {$existing->user_balance_id}\n";
							continue;
						}
						// update user_balance with vomsis payment id and bank id and set status to approved (1)
						$this->db->where('user_balance_id', $balance->user_balance_id)
							->update('user_balance', [
								'user_balance_status' => 1,
								'user_balance_vomsis_payment_id' => $paymentID,
								'user_balance_bank_id' => $bankID,
								'user_balance_change_date' => date('Y-m-d H:i:s'),
								'user_balance_admin_id' => 0, // admin id 1 for auto approval
							]);
						echo "Balance ID: {$balance->user_balance_id} approved with Vomsis Payment ID: $paymentID and Bank ID: $bankID\n";
					} else {
						echo "Could not find matching bank or payment ID for Reference No: $refNo\n";
					}
				}
			}
		}
	}
	// product_provider_alternative_id
	// product_provider_alternative_product_id
	// product_provider_alternative_provider_product_id
	// product_provider_alternative_created_at

	public function fatura_kesim()
	{
		$data = $this->data;
		$data['title'] = 'Toplu Fatura Kesim';
		$data['description'] = 'Reseller ve tarih aralığı seçerek fatura önizleyin veya kesin.';
		$data['resellers'] = $this->db->select('user_id, user_name')
			->from('users')
			->where('user_type', 3)
			->order_by('user_name', 'ASC')
			->get()
			->result_array();

		$today = date('Y-m-d');
		$data['filters'] = [
			'reseller_id' => (int)$this->input->post('reseller_id'),
			'start_date' => (string)$this->input->post('start_date') ?: $today,
			'end_date' => (string)$this->input->post('end_date') ?: $today,
			'invoice_date' => (string)$this->input->post('invoice_date') ?: $today,
		];
		$data['preview'] = null;
		$data['success'] = null;
		$data['error'] = null;

		if ($this->input->method(true) === 'POST') {
			$action = (string)$this->input->post('invoice_action');
			$reseller_id = (int)$this->input->post('reseller_id');
			$start_input = (string)$this->input->post('start_date');
			$end_input = (string)$this->input->post('end_date');
			$invoice_date_input = (string)$this->input->post('invoice_date');
			$confirm_cut = (string)$this->input->post('confirm_cut');

			if ($reseller_id <= 0 || empty($start_input) || empty($end_input)) {
				$data['error'] = 'Lütfen reseller ve başlangıç-bitiş tarihlerini seçiniz.';
			} elseif (strtotime($start_input) === false || strtotime($end_input) === false) {
				$data['error'] = 'Geçersiz tarih formatı.';
			} elseif (!empty($invoice_date_input) && strtotime($invoice_date_input) === false) {
				$data['error'] = 'Geçersiz fatura tarihi.';
			} elseif (strtotime($start_input) > strtotime($end_input)) {
				$data['error'] = 'Başlangıç tarihi bitiş tarihinden büyük olamaz.';
			} else {
				$start_date = $start_input . ' 00:00:00';
				$end_date = $end_input . ' 23:59:59';
				$invoice_date = !empty($invoice_date_input) ? $invoice_date_input : date('Y-m-d');

				if ($action === 'preview') {
					try {
						$data['preview'] = $this->build_toplu_fatura_payload($reseller_id, $start_date, $end_date, $invoice_date);
					} catch (Exception $e) {
						$data['error'] = $e->getMessage();
					}
				} elseif ($action === 'cut') {
					if ($confirm_cut !== 'yes') {
						$data['error'] = 'Fatura kesimi için onay bilgisi eksik.';
					} else {
						$result = $this->toplu_fatura_kes($reseller_id, $start_date, $end_date, true, $invoice_date);
						if (!empty($result['success'])) {
							$data['success'] = 'Fatura kesimi başarılı. Fatura No: ' . ($result['invoice_no'] ?? '-');
							$data['preview'] = $result['payload'] ?? null;
						} else {
							$data['error'] = $result['message'] ?? 'Fatura kesimi sırasında bir hata oluştu.';
							$data['preview'] = $result['payload'] ?? null;
						}
					}
				} else {
					$data['error'] = 'Geçersiz işlem tipi.';
				}
			}
		}

		$this->template->content->view('fatura_kesim', $data);
		$this->template->publish();
	}

	private function build_toplu_fatura_payload($reseller_id, $start_date, $end_date, $invoice_date = null)
	{
		$orders = $this->db->select('o.order_id, o.order_product_id, o.order_quantity, o.order_sale_price, o.order_cost, o.order_created_at, p.product_name, p.product_is_full_vat, u.*')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'inner')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'inner')
			->where('o.order_reseller_id', $reseller_id)
			->where('o.order_created_at >=', $start_date)
			->where('o.order_created_at <=', $end_date)
			->where('o.order_invoice_status', 0)
			->where('o.order_status', 1)
			->order_by('o.order_id', 'ASC')
			->get()->result();

		if (empty($orders)) {
			throw new RuntimeException('Kesilecek veri bulunamadı.');
		}

		$this->load->library('birfatura');
		$user = $orders[0];

		$status = $this->birfatura->check_user_status($user->user_bill_tax_number);
		$is_efatura = !empty($status['is_efatura']) ? true : ($user->user_bill_e_invoice == 1);

		$endpoint = $status['endpoint'] ?? null;
		if (empty($endpoint) && $is_efatura) {
			$endpoint = 'urn:mail:defaultpk';
		}

		$uuid = $this->birfatura->generate_uuid();
		$is_foreign = ($user->user_country != 'TR');

		$type_code = $is_foreign ? 'ISTISNA' : 'SATIS';
		$exemption_code = $is_foreign ? '302' : '351';
		$exemption_text = $is_foreign ? '11/1-a Hizmet ihracatı' : 'İstisna Olmayan Diğer';

		$items = [];
		$order_ids = [];
		$toplam_odenecek = 0;

		foreach ($orders as $row) {
			$order_ids[] = $row->order_id;
			$satis_fiyati = (float)$row->order_sale_price;
			$maliyet_fiyati = (float)$row->order_cost;
			$adet = (int)$row->order_quantity;
			$safe_adet = $adet > 0 ? $adet : 1;
			$stok_kodu = $row->order_product_id ?? '0-0';

			if ($is_foreign) {
				$items[] = [
					'stock_code' => $stok_kodu,
					'name'       => $row->product_name . " (#{$row->order_id})",
					'qty'        => $safe_adet,
					'price'      => number_format($satis_fiyati, 4, '.', '') / $safe_adet,
					'tax_rate'   => 0,
					'tax_amount' => 0,
				];
				$toplam_odenecek += $satis_fiyati;
			} else {
				$is_full_vat_product = isset($row->product_is_full_vat) && $row->product_is_full_vat == 1;

				if ($is_full_vat_product) {
					$fiyat_kdv_haric = round($satis_fiyati / 1.20, 2);
					$kdv_tutari = round(($satis_fiyati - $fiyat_kdv_haric), 2);

					$items[] = [
						'stock_code' => $stok_kodu,
						'name'       => $row->product_name . " (#{$row->order_id})",
						'qty'        => $safe_adet,
						'price'      => number_format($fiyat_kdv_haric, 4, '.', '') / $safe_adet,
						'tax_rate'   => 20,
						'tax_amount' => $kdv_tutari,
					];
					$toplam_odenecek += $satis_fiyati;
				} else {
					$profit_kdv_dahil = round($satis_fiyati - $maliyet_fiyati, 2);
					$hizmet_kdv_haric = round($profit_kdv_dahil / 1.20, 2);
					$kdv_tutari = round(($profit_kdv_dahil - $hizmet_kdv_haric), 2);

					$items[] = [
						'stock_code' => $stok_kodu,
						'name'       => $row->product_name . " (#{$row->order_id})",
						'qty'        => $safe_adet,
						'price'      => number_format($maliyet_fiyati, 4, '.', '') / $safe_adet,
						'tax_rate'   => 0,
						'tax_amount' => 0,
					];

					$items[] = [
						'stock_code' => '0-0',
						'name'       => "Hizmet Bedeli (#{$row->order_id})",
						'qty'        => $safe_adet,
						'price'      => number_format($hizmet_kdv_haric, 4, '.', '') / $safe_adet,
						'tax_rate'   => 20,
						'tax_amount' => $kdv_tutari,
					];
					$toplam_odenecek += $satis_fiyati;
				}
			}
		}

		$ilk_siparis = $order_ids[0];
		$son_siparis = end($order_ids);
		$gosterilecek_siparis_no = ($ilk_siparis == $son_siparis) ? $ilk_siparis : $ilk_siparis . '-' . $son_siparis;

		$notes = [
			'Fatura Kalem Sayısı: ' . count($items),
			'Yalnız ' . costToText(round($toplam_odenecek, 2)),
			'Bu fatura ' . getenv('API_URL') . ' üzerinden satışı yapılan dijital ürün/hizmet (e-pin) teslimat(lar)ını içerir.',
		];

		if (!empty($exemption_code)) {
			$notes[] = 'Vergi İstisna Muafiyet Sebebi: ' . $exemption_code . ' - ' . $exemption_text;
		}

		$fatura_tarihi = !empty($invoice_date) ? date('Y-m-d', strtotime($invoice_date)) : date('Y-m-d');
		$fatura_datetime = $fatura_tarihi . ' ' . date('H:i:s');

		$invoice_data = [
			'uuid'           => $uuid,
			'invoice_no'     => 'APIDJPN-' . strtoupper(substr(str_replace('-', '', $uuid), 0, 8)),
			'date'           => $fatura_datetime,
			'is_foreign'     => $is_foreign,
			'is_efatura'     => $is_efatura,
			'endpoint'       => $endpoint,
			'type_code'      => $type_code,
			'currency'       => 'TRY',
			'customer'       => (array)$user,
			'exemption_code' => $exemption_code,
			'exemption_text' => $exemption_text,
			'notes'          => $notes,
			'order_no'       => $gosterilecek_siparis_no,
			'order_date'     => $fatura_tarihi,
			'sales_channel'  => 'Dijipin API',
		];

		$net_total = 0.0;
		$tax_total = 0.0;
		foreach ($items as $item) {
			$line_qty = (float)($item['qty'] ?? 0);
			$line_price = (float)($item['price'] ?? 0);
			$line_net_total = round($line_qty * $line_price, 2);
			$line_tax_total = round((float)($item['tax_amount'] ?? 0), 2);
			$net_total += $line_net_total;
			$tax_total += $line_tax_total;
		}

		return [
			'orders' => $orders,
			'user' => $user,
			'items' => $items,
			'order_ids' => $order_ids,
			'invoice_data' => $invoice_data,
			'toplam_odenecek' => $toplam_odenecek,
			'order_no' => $gosterilecek_siparis_no,
			'order_date' => $fatura_tarihi,
			'invoice_date' => $fatura_tarihi,
			'total_order_count' => count($orders),
			'total_item_count' => count($items),
			'net_total' => round($net_total, 2),
			'tax_total' => round($tax_total, 2),
			'grand_total' => round($net_total + $tax_total, 2),
			'date_start' => $start_date,
			'date_end' => $end_date,
		];
	}

	public function toplu_fatura_kes($reseller_id, $start_date, $end_date, $return_result = false, $invoice_date = null)
	{
		try {
			$payload = $this->build_toplu_fatura_payload($reseller_id, $start_date, $end_date, $invoice_date);
		} catch (Exception $e) {
			if ($return_result) {
				return [
					'success' => false,
					'message' => $e->getMessage(),
				];
			}
			die($e->getMessage());
		}

		$this->load->library('birfatura');
		$result = $this->birfatura->send_invoice($payload['invoice_data'], $payload['items']);

		if (is_string($result)) {
			$result = json_decode($result, true);
		}

		if (!is_array($result)) {
			$result = [
				'Success' => false,
				'Message' => 'Birfatura cevabı parse edilemedi.',
			];
		}

		if (!empty($result['Success'])) {
			$invoice_no = $result['Result']['invoiceNo'] ?? 'OTOMATIK';

			$this->db->where_in('order_id', $payload['order_ids'])->update('orders', [
				'order_invoice_number' => $invoice_no,
				'order_invoice_status' => 1,
				'order_invoice_uuid'   => $payload['invoice_data']['uuid'],
				'order_invoice_date'   => $payload['invoice_data']['date'],
			]);

			if ($return_result) {
				return [
					'success' => true,
					'invoice_no' => $invoice_no,
					'result' => $result,
					'payload' => $payload,
				];
			}

			echo 'Islem Basarili. Fatura No: ' . $invoice_no;
			return;
		}

		if ($return_result) {
			return [
				'success' => false,
				'message' => $result['Message'] ?? 'Fatura oluşturma başarısız oldu.',
				'result' => $result,
				'payload' => $payload,
			];
		}

		header('Content-Type: application/json');
		die(json_encode($result, JSON_PRETTY_PRINT));
	}

	public function kesDeneme()
	{

		// Test sipariş tarihi 17.03.2026 00:05:31
		$resellerID = 9231; // Örnek reseller ID
		// $startDateTime = date('Y-m-d 00:00:00', strtotime('-5 minutes')); // 10 dakika öncesi
		// $endDateTime = date('Y-m-d 23:59:59');
		$startDateTime = date('2026-03-17 00:00:00');
		$endDateTime = date('2026-03-17 23:59:59');
		$this->toplu_fatura_kes($resellerID, $startDateTime, $endDateTime);
	}

	public function r2Test()
	{
		// Toplam süreyi ölçmek için zamanlayıcıyı başlat
		$start_time = microtime(true);

		// Kütüphaneyi yükle (Config değerleri artık R2_Storage'ın kendi içinden veya env'den alınıyor)
		$this->load->library('R2_Storage');

		$results = [];

		// 1. Aşama: Gerçek Dosya Yolunu Belirle
		$local_file_path = getenv('BASE_PATH') . 'uploads/sliders/img_685bfcf24f4a7.jpg';
		$r2_target_path  = 'uploads/sliders/img_685bfcf24f4a7.jpg'; // R2 üzerindeki mantıksal yol

		// Dosyanın diskte gerçekten var olup olmadığını kontrol edelim
		if (!file_exists($local_file_path)) {
			die("<pre style='background:#1e1e1e; color:#ff0000; padding:15px; border-radius:5px; font-family: monospace;'>HATA: Test edilecek yerel dosya bulunamadı!\nYol: {$local_file_path}</pre>");
		}

		$results[] = "Yerel test dosyası bulundu: " . basename($local_file_path);

		// 2. Aşama: Yükleme (PUT) Testi
		$upload_start = microtime(true);
		// Resim dosyası olduğu için content_type'ı image/jpeg olarak gönderiyoruz
		$upload_status = $this->r2_storage->upload($local_file_path, $r2_target_path, 'image/jpeg');
		$upload_time = microtime(true) - $upload_start;

		$results[] = "Upload İşlemi: " . ($upload_status ? "BAŞARILI" : "BAŞARISIZ") . " (" . number_format($upload_time, 4) . " sn)";

		// 3. Aşama: Silme (DELETE) Testi
		$delete_start = microtime(true);
		$delete_status = false;

		// Mantıken sadece upload başarılı olduysa silmeyi deneriz
		if ($upload_status) {
			$delete_status = $this->r2_storage->delete($r2_target_path);
			$delete_time = microtime(true) - $delete_start;
			$results[] = "Delete İşlemi: " . ($delete_status ? "BAŞARILI" : "BAŞARISIZ") . " (" . number_format($delete_time, 4) . " sn)";
		} else {
			$results[] = "Delete İşlemi: ATLANDI (Upload işlemi başarısız olduğu için silme denenmedi)";
		}

		// DİKKAT: Bu gerçek bir proje dosyası olduğu için unlink() ile SİLMİYORUZ.
		$results[] = "Yerel dosya (slider resmi) olduğu için unlink() edilmedi, yerinde bırakıldı.";

		$total_time = microtime(true) - $start_time;

		// Tarayıcı üzerinde rahat okunabilmesi için çıktıyı terminal formatında bas
		echo '<pre style="background:#1e1e1e; color:#00ff00; padding:15px; border-radius:5px; font-family: monospace; font-size: 14px;">';
		echo "=== CLOUDFLARE R2 GERÇEK DOSYA TEST RAPORU ===\n\n";
		echo "Yerel Yol : " . $local_file_path . "\n";
		echo "Hedef R2  : " . $r2_target_path . "\n\n";

		echo "--- İŞLEM ADIMLARI ---\n";
		foreach ($results as $res) {
			echo "- " . $res . "\n";
		}

		echo "\n==============================================\n";
		echo "Genel Statü    : " . ($upload_status && $delete_status ? "KUSURSUZ (Tüm testler başarıyla geçti)" : "HATALI (Detayları ve API keyleri kontrol et)") . "\n";
		echo "Toplam Süre    : " . number_format($total_time, 4) . " saniye\n";
		echo '</pre>';
	}

	public function migrate_to_cdn()
	{
		// Performans ayarları
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		ignore_user_abort(true);

		$this->load->library('R2_Storage');

		// Kaynak dizin (Sondaki * yerine klasörün kendisinden başlıyoruz)
		$source_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads';

		if (!is_dir($source_dir)) {
			die("Kaynak dizin bulunamadı: $source_dir");
		}

		// Recursive Tarama Başlat
		$directory = new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);

		$success_count = 0;
		$error_count = 0;

		foreach ($iterator as $file) {
			if ($file->isFile()) {
				$local_path = $file->getRealPath();

				// CDN üzerindeki mantıksal yol (uploads/kategori/resim.webp gibi)
				// DOCUMENT_ROOT kısmını temizleyip başına slash koymadan yolu alıyoruz
				$relative_path = str_replace($_SERVER['DOCUMENT_ROOT'] . '/', '', $local_path);

				// Mime type belirleme (WebP öncelikli)
				$mime_type = $this->get_mime_type($local_path);

				// R2 Upload
				$upload_status = $this->r2_storage->upload($local_path, $relative_path, $mime_type);

				if ($upload_status) {
					$success_count++;
					// echo "Başarılı: $relative_path \n"; // Debug için CLI'da açabilirsin

					// OPSİYONEL: CDN'e taşınan dosyayı yerelden silmek istersen:
					// @unlink($local_path); 
				} else {
					$error_count++;
					log_message('error', "CDN Taşıma Hatası: " . $local_path);
				}
			}
		}

		echo "İşlem Tamamlandı.\nBaşarılı: $success_count\nHatalı: $error_count\n";
	}

	/**
	 * Dosya uzantısına göre mime-type döndürür
	 */
	private function get_mime_type($file_path)
	{
		$extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
		$mimes = [
			'webp' => 'image/webp',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'gif'  => 'image/gif'
		];
		return $mimes[$extension] ?? 'application/octet-stream';
	}

	public function balanceTest()
	{
		$this->load->model('Wallet_model');
		$balance = $this->Wallet_model->get_user_balances(9217); //
		pre($balance);
		die;
	}

	public function testSmsVerimor()
	{
		$phone = '+90 546 698 72 64';
		$message = "Umutcum selam. IıĞğÜüŞşÖöÇç test mesajıdır. 1234567890";
		$asd = sendSms2($phone, $message);
		pre($asd);
		die;
	}

	/**
	 * Gerçek Coupon_model Üzerinden Entegrasyon Test Senaryosu
	 * URL: domain.com/coupon/couponTest
	 */
	public function couponTest()
	{
		// 1. ADIM: Bağımlılıkları Yükle
		// Eğer autoload içinde değilse modeli controller'a bağlıyoruz
		$this->load->model('coupon_model');

		// 2. ADIM: Test Parametrelerini Hazırla
		$test_coupon_code = 'TEST10';
		$test_user_id     = 1; // Sistemdeki aktif bir kullanıcı ID'si

		// Sistemindeki sepet array formatına uygun simülasyon verisi
		$mock_cart_items  = [
			[
				'product_id'  => 101,
				'category_id' => 5,    // Örn: Kuponun geçerli olduğu varsayılan kategori
				'price'       => 550.00,
				'qty'         => 2     // 550 * 2 = 1100 TL
			],
			[
				'product_id'  => 102,
				'category_id' => 3,    // Örn: Kupona dahil olmayan kategori
				'price'       => 100.00,
				'qty'         => 1     // 100 * 1 = 100 TL
			]
		];
		// Toplam ham sepet tutarı: 1200.00 TL

		echo "<h3>--- KUPON MODEL ENTEGRASYON TESTİ ---</h3>";
		echo "<b>Girdi Kupon Kodu:</b> " . $test_coupon_code . "<br>";
		echo "<b>Girdi Kullanıcı ID:</b> " . $test_user_id . "<br>";
		echo "<b>Simüle Edilen Sepet:</b><br>";
		echo "- Ürün A (Kat 5): 250 TL x 2 Adet = 500 TL<br>";
		echo "- Ürün B (Kat 3): 100 TL x 1 Adet = 100 TL<br>";
		echo "<b>Toplam Sepet Değeri:</b> 600.00 TL<br><br>";

		// 3. ADIM: Senin Model Fonksiyonunu Doğrudan Tetikliyoruz
		echo "<b>Model Çalıştırılıyor...</b><br>";

		$result = $this->coupon_model->validate_and_calculate(
			$test_coupon_code,
			$test_user_id,
			$mock_cart_items
		);

		// 4. ADIM: Dönen Çıktıyı Ekrana Bas ve Analiz Et
		echo "<h4>Modelden Dönen Response Dizisi:</h4>";
		echo "<pre>";
		print_r($result);
		echo "</pre>";

		echo "<hr>";

		// 5. ADIM: Durum Değerlendirmesi
		if ($result['status'] === true) {
			echo "<span style='color:green; font-weight:bold; font-size:14px;'>✓ BAŞARILI:</span> Model kuralları işletti, hata fırlatmadı.<br>";
			echo "<b>Uygulanan Net İndirim:</b> " . $result['discount'] . " TL<br>";
			echo "<b>Kupon ID:</b> " . $result['coupon_id'] . "<br>";
		} else {
			echo "<span style='color:red; font-weight:bold; font-size:14px;'>✗ MODEL REDDETTİ:</span> " . $result['message'] . "<br>";
			echo "<i>Not: Eğer 'TEST10' kodu DB'de yoksa, süresi geçmişse, limitleri dolmuşsa veya kategoriler uyuşmadıysa modelin bu cevabı vermesi normaldir.</i><br>";
		}
	}
}
