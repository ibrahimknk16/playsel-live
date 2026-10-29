<?php defined('BASEPATH') or exit('No direct script access allowed');

class Cron2 extends MY_Controller
{
	public $data = array();

	private function orderDataWithMessage($orderData, $message)
	{
		$decoded = json_decode((string)$orderData, true);
		if (!is_array($decoded)) {
			$decoded = [];
		}
		$decoded['status_message'] = (string)$message;

		return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if (!empty($this->session->userdata('user'))) {
			redirect(topupcenter() . $this->data['uri']);
		}
		redirect(topupcenter() . $this->data['uri'] . '/login');
	}

	public function sendOrders() // Cron Job: Dijipin'e siparişleri gönder
	{
		$this->load->library('Dijipin');

		$dijipin = new Dijipin();

		$apiID = 1;

		$orders = $this->db
			->select('o.order_id, o.order_data, o.order_require_data, o.order_quantity, pp.provider_product_product_id')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('provider_products as pp', 'pp.provider_product_id = p.product_provider_id', 'left')
			->where('pp.provider_product_provider_id', $apiID)
			->where('o.order_status', 0)
			->order_by('o.order_id', 'ASC')
			->get()
			->result();

		if (empty($orders)) {
			return false;
		}

		$orderArr = [];
		foreach ($orders as $order) {
			$jsonData = json_decode($order->order_require_data, true);

			$orderArr[] = [
				'localOrderID' => $order->order_id,
				'productID' => (int) $order->provider_product_product_id,
				'orderID'   => (string) $order->order_id,
				'quantity'  => (int) $order->order_quantity,
				'user_id' => $jsonData['user_id'] ?? null,
				'server_id' => $jsonData['server_id'] ?? null,
			];
		}

		$responses = $dijipin->sendOrdersMulti($orderArr);
		if (empty($responses)) {
			return false;
		}

		$ordersByID = [];
		foreach ($orders as $order) {
			$ordersByID[(int)$order->order_id] = $order;
		}

		foreach ($responses as $response) {
			$localOrderID = (int)($response['order_id'] ?? 0);
			$apiResponse = $response['response'] ?? null;
			$httpCode = (int)($response['http_code'] ?? 0);
			$localOrder = $ordersByID[$localOrderID] ?? null;
			if (!$localOrder) {
				continue;
			}

			$isSuccess = (
				$httpCode == 200 &&
				is_array($apiResponse) &&
				isset($apiResponse['status']) &&
				$apiResponse['status'] === 'success' &&
				isset($apiResponse['data']['orderCreated']) &&
				(int) $apiResponse['data']['orderCreated'] === 1
			);

			if ($isSuccess) {
				$dijipinOrderID = $apiResponse['data']['orderID'] ?? null;

				$updateData = [
					'order_status' => 3,
					'order_sended_at' => date('Y-m-d H:i:s')
				];
				if ($dijipinOrderID) {
					$updateData['order_reseller_order_id'] = $dijipinOrderID;
				}

				$this->db->where('order_id', $localOrderID)->where('order_status', 0)->update('orders', $updateData);

				$this->db->insert('logs', [
					'log_controller' => 'sendOrders',
					'log_message' => json_encode($apiResponse, JSON_UNESCAPED_UNICODE),
					'log_error_code' => 'dijipin_order_success',
					'log_order_id' => $localOrderID
				]);
				continue;
			}

			$errorMessage = 'Bilinmeyen API hatası';
			if (is_array($apiResponse) && isset($apiResponse['message'])) {
				$errorMessage = $apiResponse['message'];
			}
			if (!empty($apiResponse['data']) && is_array($apiResponse['data'])) {
				$errorMessage .= ' (' . implode('; ', array_map('strval', array_filter($apiResponse['data'], 'is_scalar'))) . ')';
			}
			if (!empty($response['curl_error'])) {
				$errorMessage = 'cURL Error: ' . $response['curl_error'];
			}

			$this->db->insert('logs', [
				'log_controller' => 'sendOrders',
				'log_message' => 'Dijipin API Hatası - Order ID: ' . $localOrderID
					. ' - HTTP: ' . $httpCode
					. ' - Response: ' . json_encode($apiResponse, JSON_UNESCAPED_UNICODE)
					. ' - Error: ' . $errorMessage,
				'log_error_code' => 'dijipin_order_error',
				'log_order_id' => $localOrderID
			]);

			$alreadyExists = $httpCode === 400 && stripos((string)($apiResponse['message'] ?? ''), 'already exists') !== false;
			if ($alreadyExists) {
				// Tedarikçide kayıt var: yeniden gönderme, gerçek durumu sorgula.
				$remote = $dijipin->checkOrderStatus((string)$localOrderID);
				$remoteStatus = is_array($remote) ? strtolower((string)($remote['status'] ?? '')) : '';
				if ($remoteStatus !== '' && preg_match('/fail|cancel|reject/', $remoteStatus)) {
					$this->closeSupplierOrder($localOrder, 5, 'Tedarikçi siparişi iptal etti.');
				} elseif ($remoteStatus !== '' && preg_match('/complet|success|deliver/', $remoteStatus)) {
					$this->closeSupplierOrder($localOrder, 4, 'Tedarikçi siparişi tamamladı.');
				} else {
					$this->closeSupplierOrder($localOrder, 3, 'Sipariş tedarikçide mevcut; sonuç bekleniyor.');
				}
				continue;
			}

			// Kalıcı hatalar (doğrulama/yetki/ürün hatası): tedarikçide sipariş oluşmadı, iptal + iade akışına al.
			$isPermanent = in_array($httpCode, [400, 401, 403, 404, 422], true) && empty($response['curl_error']);
			$orderData = json_decode((string)$localOrder->order_data, true);
			$orderData = is_array($orderData) ? $orderData : [];
			$attempts = (int)($orderData['supplier_send_attempts'] ?? 0) + 1;
			if ($isPermanent || $attempts >= 5) {
				$this->closeSupplierOrder($localOrder, 5, 'Tedarikçiye iletilemedi: ' . $errorMessage);
			} else {
				// Geçici hata (ağ/5xx/429): sonraki çalışmada tekrar denenir, deneme sayısı sınırlıdır.
				$orderData['supplier_send_attempts'] = $attempts;
				$orderData['status_message'] = 'Tedarikçiye gönderim denemesi ' . $attempts . ': ' . $errorMessage;
				$this->db->where('order_id', $localOrderID)->where('order_status', 0)->update('orders', [
					'order_data' => json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				]);
			}
		}

		return true;
	}

	// Siparişi (yalnızca hâlâ 0 ise) yeni duruma geçirir; 5 durumu deliverOrder tarafından iptal + iade edilir.
	private function closeSupplierOrder($order, $newStatus, $message)
	{
		$orderData = json_decode((string)$order->order_data, true);
		$orderData = is_array($orderData) ? $orderData : [];
		$orderData['status_message'] = $message;
		if ($newStatus === 5) {
			$orderData['cancellation_reason'] = $message;
		}
		$update = [
			'order_status' => (int)$newStatus,
			'order_data' => json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		];
		if ($newStatus === 3) {
			$update['order_sended_at'] = date('Y-m-d H:i:s');
		}
		$this->db->where('order_id', (int)$order->order_id)->where('order_status', 0)->update('orders', $update);
	}

	public function currencies() // Cron Job: Döviz Kurlarını Güncelle
	{
		$fgc = file_get_contents('https://latest.currency-api.pages.dev/v1/currencies/try.json');
		$fgc = json_decode($fgc);
		$checkCurrencyByDate = $this->db->select('currencies_date')->from('currency')->where('currencies_date', date('Y-m-d'))->get()->row();
		if (!empty($checkCurrencyByDate)) {
			die;
		}
		$insertdata = [
			'currencies_json' => json_encode($fgc->try),
			'currencies_date' => date('Y-m-d'),
		];
		$this->db->insert('currency', $insertdata);
	}

	public function deliverOrder() // bekleyen siparişleri tamamla
	{
		$orders = $this->db->select('*')
			->from('orders')
			->where_in('order_status', [4, 5])
			->where('order_reseller_id !=', 9001)
			->limit(100)
			->get()
			->result();

		foreach ($orders as $order) {
			// eğer order_status 4 ise 1 yap
			if ($order->order_status == 4) {
				$this->db->where('order_id', (int)$order->order_id)
					->where('order_status', 4)
					->update('orders', [
					'order_status' => 1,
					'order_data' => $this->orderDataWithMessage($order->order_data, 'Order Completed Successfully')
					]);
				$this->db->where('coupon_usage_order_id', $order->order_id)->update('coupon_usages', [
					'coupon_usage_status' => 1,
					'coupon_usage_updated_at' => date('Y-m-d H:i:s'),
				]);
			}
			if ($order->order_status == 5) {
				$this->db->trans_start();
				$orderData = json_decode((string)$order->order_data, true);
				$orderData = is_array($orderData) ? $orderData : [];
				$orderData['cancellation_reason'] = $orderData['cancellation_reason'] ?? 'Tedarikçi siparişi iptal etti.';

				// Sadece bu sipariş satırını kapat; bundle içindeki diğer satıra dokunma.
				$this->db->where('order_id', (int)$order->order_id)
					->where('order_status', 5)
					->update('orders', [
						'order_status' => 2,
					]);

				if ($this->db->affected_rows() > 0) {
					// İade tutarı, bu sipariş satırının ödeme anındaki snapshot'ından
					// alınır. user_balance hareketlerini toplamak aynı satırda birden
					// fazla harcama kaydı varsa 2x iade oluşturabilir.
					$pricingSnapshot = is_array($orderData)
						? ($orderData['pricing_snapshot'] ?? [])
						: [];
					$refundAmount = (float)($pricingSnapshot['final_sale_price_tl'] ?? 0);
					if ($refundAmount <= 0) {
						$refundAmount = (float)$order->order_sale_price_tl;
					}

					$refundExists = $this->db->where('user_balance_order_id', (int)$order->order_id)
						->where('user_balance_user_id', (int)$order->order_reseller_id)
						->where('user_balance_type', 2)
						->where('user_balance_status', 1)
						->where('user_balance_note', 'Sipariş iadesi')
						->limit(1)
						->get('user_balance')
						->num_rows() > 0;

					if (!$refundExists && $refundAmount > 0) {
						$this->db->insert('user_balance', [
							'user_balance_user_id' => (int)$order->order_reseller_id,
							'user_balance_type' => 2,
							'user_balance_amount' => $refundAmount,
							'user_balance_status' => 1,
							'user_balance_order_id' => (int)$order->order_id,
							'user_balance_note' => 'Sipariş iadesi',
							'user_balance_created_at' => date('Y-m-d H:i:s'),
						]);
					}
				}

				$this->db->trans_complete();
				if ($this->db->trans_status() === false) {
					log_message('error', 'Order cancellation refund transaction failed. Order: ' . (int)$order->order_id);
					continue;
				}
				$this->db->where('order_id', (int)$order->order_id)->update('orders', [
					'order_data' => json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				]);

				$this->db->where('coupon_usage_order_id', $order->order_id)->update('coupon_usages', [
					'coupon_usage_status' => 0,
					'coupon_usage_updated_at' => date('Y-m-d H:i:s'),
				]);
			}
		}
	}

	// Cron Job: teslim/iptal edilen siparişlerin GA4 + Meta olaylarını gönderir (her 5 dakikada bir önerilir).
	// Idempotent: gönderilen olay analytics_events_sent kaydıyla tekrar gönderilmez; çakışan çalışmalar kilitle engellenir.
	public function analyticsEvents()
	{
		// $this->load->library('analytics_tracker');
		// $processed = $this->analytics_tracker->runLocked(50);
		// echo $processed === null ? 'Başka bir çalışma sürüyor.' : 'Analytics olayı işlenen sipariş: ' . $processed;
	}

	public function vomsisAutoApproveProcess() // Cron Job: Vomsis ile Bekleyen Cüzdan Yüklemelerini ve Siparişleri Tek Elden Yönet
	{
		$this->expirePendingTransferRecords();

		$this->load->library('Vomsis');
		$vomsis = new Vomsis();

		$vomsisResponse = $vomsis->vomsisTransactions()['transactions'] ?? [];
		if (empty($vomsisResponse)) {
			echo "Vomsis üzerinden işlem listesi alınamadı veya liste boş.\n";
			return;
		}

		$vomsisResponse = array_reverse($vomsisResponse); // Son işlemler önce gelsin

		// 1. Bekleyen Cüzdan Yüklemelerini Çek (Son 24 saat)
		$pendingBalances = $this->db->select('*')
			->from('user_balance')
			->where('user_balance_status', 0)
			->where('user_balance_vomsis_payment_id IS NULL', null, false)
			->where("user_balance_reference_no NOT LIKE 'cc!_%' ESCAPE '!'", null, false)
			->where("user_balance_reference_no NOT LIKE 'mobile!_%' ESCAPE '!'", null, false)
			->where("user_balance_reference_no NOT LIKE 'wallet!_cc!_%' ESCAPE '!'", null, false)
			->where("user_balance_reference_no NOT LIKE 'wallet!_mobile!_%' ESCAPE '!'", null, false)
			->where_not_in('user_balance_bank_id', [4, 5])
			->where('user_balance_created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
			->get()
			->result();

		// 2. Bekleyen Siparişleri Çek (Son 24 saat)
		$pendingOrders = $this->db->select('*')
			->from('orders')
			->where('order_status', 11)
			->where('order_eft_id IS NOT NULL', null, false)
			->where('order_eft_bank_id IS NOT NULL', null, false)
			->join('banks', 'banks.bank_id = orders.order_eft_bank_id', 'left')
			->where('order_created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
			->get()
			->result();

		if (empty($pendingBalances) && empty($pendingOrders)) {
			echo "İşlenecek bekleyen bakiye veya sipariş bulunamadı.\n";
			return;
		}

		// AYNİ HAREKETİ İKİ KERE KULLANMAMAK İÇİN GEÇİCİ BLOKE DİZİSİ
		$processedPaymentIDs = [];

		// Açıklamasında birden fazla FARKLI bekleyen referans geçen hareketler otomatik onaylanmaz;
		// yanlış kişiye eşleşmeyi önlemek için manuel eşleştirmeye bırakılır.
		foreach ($vomsisResponse as $transaction) {
			$txID = $transaction['id'] ?? null;
			if (!$txID) {
				continue;
			}
			$txDescription = (string)($transaction['description'] ?? '');
			$hitRefs = [];
			foreach ($pendingOrders as $pendingOrder) {
				$candidateRef = trim((string)$pendingOrder->order_eft_id);
				if ($candidateRef !== '' && preg_match('/\b' . preg_quote($candidateRef, '/') . '\b/i', $txDescription)) {
					$hitRefs[strtoupper($candidateRef)] = true;
				}
			}
			foreach ($pendingBalances as $pendingBalance) {
				$candidateRef = trim((string)$pendingBalance->user_balance_reference_no);
				if ($candidateRef !== '' && preg_match('/\b' . preg_quote($candidateRef, '/') . '\b/i', $txDescription)) {
					$hitRefs[strtoupper($candidateRef)] = true;
				}
			}
			if (count($hitRefs) > 1) {
				$processedPaymentIDs[] = $txID;
				$logMarker = 'Vomsis #' . $txID . ' ';
				$alreadyLogged = $this->db->where('log_error_code', 'VomsisAmbiguousReference')
					->like('log_message', $logMarker, 'after')->limit(1)->get('logs')->num_rows() > 0;
				if (!$alreadyLogged) {
					$this->db->insert('logs', [
						'log_controller' => 'vomsisAutoApproveProcess',
						'log_message' => $logMarker . 'açıklamasında birden fazla bekleyen referans bulundu: ' . implode(', ', array_keys($hitRefs)) . '. Otomatik onay yapılmadı, manuel eşleştirme gerekli.',
						'log_error_code' => 'VomsisAmbiguousReference',
						'log_order_id' => null,
					]);
				}
				echo "Payment ID: $txID has multiple pending references, skipped for manual matching.\n";
			}
		}

		// ÖNCELİK SIRASI: Önce Siparişler (İstersen buradaki döngü sırasını değiştirebilirsin)

		// --- 1. ADIM: SİPARİŞLERİ İŞLE ---
		foreach ($pendingOrders as $order) {
			$refNo = trim($order->order_eft_id);
			$expectedBankTitle = $order->bank_title;

			$found = false;
			$matchedTransaction = null;

			foreach ($vomsisResponse as $transaction) {
				$paymentID = $transaction['id'] ?? null;
				if (!$paymentID || in_array($paymentID, $processedPaymentIDs)) {
					continue; // Zaten işlendiyse atla
				}

				$description = $transaction['description'] ?? '';
				if (!empty($refNo) && preg_match('/\b' . preg_quote($refNo, '/') . '\b/i', $description)) {
					$vomsisBankName = $transaction['account']['bank']['bank_name'] ?? null;
					if ($vomsisBankName == $expectedBankTitle) {
						$found = true;
						$matchedTransaction = $transaction;
						break;
					}
				}
			}

			if ($found && $matchedTransaction) {
				$paymentID = $matchedTransaction['id'];
				$amount = $matchedTransaction['amount'] ?? null;

				// Aynı Vomsis işlemi için cron/admin eşleştirmeleri birbirini bekletmeden dışlar
				$vomsisLockName = 'vomsis_payment_' . $paymentID;
				$vomsisLock = $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$vomsisLockName])->row();
				if (!$vomsisLock || (int)$vomsisLock->acquired !== 1) {
					echo "Payment ID: $paymentID is being processed elsewhere. Skipping Order ID: {$order->order_id}\n";
					continue;
				}

				// Veritabanı çift işlem (duplicate) kontrolü
				$existingOrder = $this->db->select('order_id')->from('orders')
					->where('order_vomsis_id', $paymentID)->get()->row();
				$existingBalance = $this->db->select('user_balance_id')->from('user_balance')
					->where('user_balance_vomsis_payment_id', $paymentID)->get()->row();

				if ($existingOrder || $existingBalance) {
					$this->db->query('SELECT RELEASE_LOCK(?)', [$vomsisLockName]);
					echo "Payment ID: $paymentID already processed. Skipping Order ID: {$order->order_id}\n";
					continue;
				}

				$paymentGroupOrders = $this->db->select('order_id, order_pay_price')
					->from('orders')
					->where('order_eft_id', $refNo)
					->where('order_eft_bank_id', $order->order_eft_bank_id)
					->where('order_status', 11)
					->get()->result();
				$expectedGroupAmount = round(array_sum(array_map(function ($groupOrder) {
					return (float)$groupOrder->order_pay_price;
				}, $paymentGroupOrders)), 2);

				if (abs($expectedGroupAmount - (float)$amount) > 0.01) {
					$this->db->query('SELECT RELEASE_LOCK(?)', [$vomsisLockName]);
					echo "Amount mismatch for payment group {$refNo}. Expected: {$expectedGroupAmount} - Vomsis: $amount\n";
					$this->db->insert('logs', [
						'log_controller' => 'vomsisAutoApproveProcess',
						'log_message' => "Order group amount mismatch: Ref {$refNo}. Expected: {$expectedGroupAmount} - Vomsis: $amount",
						'log_error_code' => 'OrderAmountMismatch',
						'log_order_id' => $order->order_id,
					]);
					continue;
				}

				$this->load->model('FraudModel', 'fraudModel');
				$fraudGroupIDs = [];
				foreach ($paymentGroupOrders as $groupOrder) {
					if ($this->fraudModel->needsReview((int)$groupOrder->order_id)) {
						$fraudGroupIDs[] = (int)$groupOrder->order_id;
					}
				}

				$this->db->trans_begin();
				$this->db->where('order_eft_id', $refNo)
					->where('order_eft_bank_id', $order->order_eft_bank_id)
					->where('order_status', 11)
					->where('order_vomsis_id IS NULL', null, false)
					->update('orders', [
						'order_status' => empty($fraudGroupIDs) ? 0 : 13,
						'order_vomsis_id' => $paymentID,
					]);
				$claimedOrders = $this->db->affected_rows();
				if ($this->db->trans_status() === false || $claimedOrders !== count($paymentGroupOrders)) {
					$this->db->trans_rollback();
					$this->db->query('SELECT RELEASE_LOCK(?)', [$vomsisLockName]);
					log_message('error', 'EFT payment group update failed. Ref: ' . $refNo . ', Payment: ' . $paymentID . ', Claimed: ' . $claimedOrders);
					continue;
				}
				$this->db->trans_commit();
				$this->db->query('SELECT RELEASE_LOCK(?)', [$vomsisLockName]);

				$processedPaymentIDs[] = $paymentID;
				echo "Order ID: {$order->order_id} approved with Vomsis Payment ID: $paymentID\n";
			}
		}

		// --- 2. ADIM: CÜZDAN YÜKLEMELERİNİ İŞLE ---
		foreach ($pendingBalances as $balance) {
			$refNo = trim($balance->user_balance_reference_no);

			$found = false;
			$matchedTransaction = null;

			foreach ($vomsisResponse as $transaction) {
				$paymentID = $transaction['id'] ?? null;
				if (!$paymentID || in_array($paymentID, $processedPaymentIDs)) {
					continue; // Zaten işlendiyse atla
				}

				$description = $transaction['description'] ?? '';
				if (!empty($refNo) && preg_match('/\b' . preg_quote($refNo, '/') . '\b/i', $description)) {
					$found = true;
					$matchedTransaction = $transaction;
					break;
				}
			}

			if ($found && $matchedTransaction) {
				$paymentID = $matchedTransaction['id'];
				$amount = $matchedTransaction['amount'] ?? null;
				$bankName = $matchedTransaction['account']['bank']['bank_name'] ?? null;

				$bank = $this->db->select('bank_id')->from('banks')
					->where('bank_title', $bankName)->get()->row();
				$bankID = $bank->bank_id ?? 0;

				if (!$bankID) {
					echo "Bank not found for title: $bankName, skipping Balance ID: {$balance->user_balance_id}\n";
					continue;
				}

				// Aynı Vomsis işlemi için cron/admin eşleştirmeleri birbirini dışlar
				$vomsisLockName = 'vomsis_payment_' . $paymentID;
				$vomsisLock = $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$vomsisLockName])->row();
				if (!$vomsisLock || (int)$vomsisLock->acquired !== 1) {
					echo "Payment ID: $paymentID is being processed elsewhere. Skipping Balance ID: {$balance->user_balance_id}\n";
					continue;
				}

				// Veritabanı çift işlem kontrolü
				$existingBalance = $this->db->select('user_balance_id')->from('user_balance')
					->where('user_balance_vomsis_payment_id', $paymentID)->get()->row();
				$existingOrder = $this->db->select('order_id')->from('orders')
					->where('order_vomsis_id', $paymentID)->get()->row();

				if ($existingBalance || $existingOrder) {
					$this->db->query('SELECT RELEASE_LOCK(?)', [$vomsisLockName]);
					echo "Payment ID: $paymentID already processed. Skipping Balance ID: {$balance->user_balance_id}\n";
					continue;
				}

				if ($balance->user_balance_amount != $amount) {
					$this->db->query('SELECT RELEASE_LOCK(?)', [$vomsisLockName]);
					echo "Amount mismatch for Balance ID: {$balance->user_balance_id}. Expected: {$balance->user_balance_amount} - Vomsis: $amount\n";
					$this->db->insert('logs', [
						'log_controller' => 'vomsisAutoApproveProcess',
						'log_message' => "Balance Amount mismatch: ID {$balance->user_balance_id}. Expected: {$balance->user_balance_amount} - Vomsis: $amount",
						'log_error_code' => 'BalanceAmountMismatch',
						'log_order_id' => null,
					]);
					continue;
				}

				// Onayla (yalnızca hâlâ bekleyen ve eşleşmemiş talep)
				$this->db->where('user_balance_id', $balance->user_balance_id)
					->where('user_balance_status', 0)
					->where('user_balance_vomsis_payment_id IS NULL', null, false)
					->update('user_balance', [
						'user_balance_status' => 1,
						'user_balance_vomsis_payment_id' => $paymentID,
						'user_balance_bank_id' => $bankID,
						'user_balance_change_date' => date('Y-m-d H:i:s'),
						'user_balance_admin_id' => 0,
					]);
				$balanceClaimed = $this->db->affected_rows();
				$this->db->query('SELECT RELEASE_LOCK(?)', [$vomsisLockName]);
				if ($balanceClaimed !== 1) {
					echo "Balance ID: {$balance->user_balance_id} was already processed elsewhere, skipping.\n";
					continue;
				}

				$processedPaymentIDs[] = $paymentID;
				echo "Balance ID: {$balance->user_balance_id} approved with Vomsis Payment ID: $paymentID\n";
			}
		}
	}

	/**
	 * Havale bildirimi ve havale ile oluşturulan siparişler 24 saat içinde
	 * eşleşmezse bekleyen durumdan iptal durumuna alınır.
	 */
	public function expirePendingTransferRecords()
	{
		$expiry = date('Y-m-d H:i:s', strtotime('-24 hours'));
		$now = date('Y-m-d H:i:s');

		$this->db->trans_start();
		$this->db->where('user_balance_status', 0)
			->where('user_balance_created_at <', $expiry)
			->where('user_balance_order_id IS NULL', null, false)
			->where('user_balance_bank_id IS NOT NULL', null, false)
			->update('user_balance', [
				'user_balance_status' => 2,
				'user_balance_change_date' => $now,
				'user_balance_note' => 'Havale bildirimi 24 saat içinde doğrulanmadı.',
			]);

		$orders = $this->db->where('order_status', 11)
			->where('order_payment_type', 3)
			->where('order_created_at <', $expiry)
			->get('orders')
			->result();
		foreach ($orders as $order) {
			$this->db->where('order_id', (int)$order->order_id)
				->where('order_status', 11)
				->update('orders', [
					'order_status' => 2,
					'order_data' => $this->appendCancellationReasonToOrderData($order->order_data),
				]);
		}

		$this->db->trans_complete();
		if ($this->db->trans_status() === false) {
			log_message('error', 'Pending transfer expiry transaction failed.');
			return false;
		}

		return true;
	}

	private function appendCancellationReasonToOrderData($orderData)
	{
		$decoded = json_decode((string)$orderData, true);
		$decoded = is_array($decoded) ? $decoded : [];
		$decoded['cancellation_reason'] = 'Havale ödemesi 24 saat içinde doğrulanmadı.';

		return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	public function dijipinAPIProductsUpdate() // Cron Job: Dijipin API Ürünlerini Güncelle
	{
		$this->load->library('Dijipin');
		$dijipinAPI = new Dijipin();
		$allProducts = $dijipinAPI->getProducts();
		$providerID = 1; // Assuming provider_id is 1 for Dijipin
		// update or insert products from provider_products table
		foreach ($allProducts as $key => $category) {
			foreach ($category['products'] as $product) {
				$existingProduct = $this->db->get_where('provider_products', ['provider_product_product_id' => $product['product_id'], 'provider_product_provider_id' => $providerID])->row();
				$requiresMerge = array_merge($category['servers'], $category['categoryRequires']);
				$reqsMerge = json_encode($requiresMerge, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				$data = [
					'provider_product_provider_id' => $providerID,
					'provider_product_type' => !empty($category['categoryType']) && $category['categoryType'] == 'topup' ? 2 : 3,
					'provider_product_category_id' => $category['categoryID'],
					'provider_product_category_name' => $category['categoryTitle'],
					'provider_product_product_id' => $product['product_id'],
					'provider_product_product_name' => $product['product_name'],
					'provider_product_product_cost' => $product['price']['userPrice'],
					'provider_product_product_cost_currency' => $product['price']['currency'],
					'provider_product_requires' => $reqsMerge,
				];

				if ($existingProduct) {
					$this->db->where('provider_product_product_id', $product['product_id']);
					$this->db->update('provider_products', $data);
				} else {
					$this->db->insert('provider_products', $data);
				}
			}
		}
	}

	public function buildSearchIndex() // Cron Job: Arama İndeksini Oluştur
	{
		$this->load->model('search/Search_model');
		echo $this->Search_model->buildIndex() ? "Search index built successfully\n" : "Failed to write search index.\n";
	}

	public function listenDijipinOrders() // Cron Job: Test
	{
		die;
		$logArray = [
			'log_controller' => 'payment',
			'log_message' => 'supervisor test log',
			'log_error_code' => '123456',
		];
		$this->db->insert('logs', $logArray);
	}
}
