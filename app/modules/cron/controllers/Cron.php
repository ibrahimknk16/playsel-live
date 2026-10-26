<?php defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends MY_Controller
{
	public $data = array();
	private const PAYMENT_QUERY_COOLDOWN_SECONDS = 60;
	private const PENDING_PAYMENT_QUERY_MAX_AGE_MINUTES = 15;

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

	public function indexx()
	{
		die;
		// $this->load->library('Biotekno');
		// $bio = new Biotekno();
		// $products = $bio::getProducts();
		// //pre($products);die;
		// foreach ($products->products as $key => $product) {
		// 	echo $key + 1 . ': ' . $product->description . ' ' . nFormat($product->unitAmount) . ' TL<br>';
		// }
		// $servers = $this->db->select('*')->from('servers')
		// 	->where('servers_category_id', 15)
		// 	->get()->row()->servers_serversJson;
		// //pre($servers);
		// $servers = json_decode($servers, true);
		// $serverTitles = array_column($servers, 'title');
		// echo implode('<br>', $serverTitles);
	}

	private function paymentStatusFromGateway(array $response)
	{
		foreach ([
			$response['paymentStatus'] ?? null,
			$response['data']['paymentStatus'] ?? null,
			$response['result']['paymentStatus'] ?? null,
		] as $status) {
			if (is_string($status) && trim($status) !== '') {
				return strtoupper(trim($status));
			}
		}

		return '';
	}

	private function paymentGatewayHasError(array $response)
	{
		if (!empty($response['transport_error'])) {
			return false;
		}
		if (strtolower(trim((string)($response['status'] ?? ''))) === 'error'
			|| !empty($response['errors'])
			|| !empty($response['error'])
			|| !empty($response['errorCode'])
			|| !empty($response['data']['errorCode'])
			|| !empty($response['result']['errorCode'])) {
			return true;
		}

		return false;
	}

	private function addWalletFailureNote($note, $response)
	{
		$decoded = json_decode((string)$note, true);
		$decoded = is_array($decoded) ? $decoded : [];
		$decoded['message'] = 'Kredi kartı ödemesi başarısız oldu';
		$decoded['provider_query_failure'] = true;
		$decoded['provider_response'] = $response;

		return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	public function syncPendingPayments()
	{
		$lockName = 'playsel_sync_pending_payments';
		$lock = $this->db->query('SELECT GET_LOCK(?, 0) AS acquired', [$lockName])->row();
		if (!$lock || (int)$lock->acquired !== 1) {
			return json(['status' => 'success', 'checked' => 0, 'failed' => 0, 'skipped' => 'already_running']);
		}
		$db = $this->db;
		register_shutdown_function(static function () use ($db, $lockName) {
			$db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
		});

		try {
			$recentPaymentCutoff = date('Y-m-d H:i:s', time() - self::PENDING_PAYMENT_QUERY_MAX_AGE_MINUTES * 60);
			$orders = $this->db->select('order_id, order_payment_token')
				->where('order_payment_type', 4)
				->where('order_status', 11)
				->where('order_payment_token >', 0)
				->where('order_created_at >=', $recentPaymentCutoff)
				->get('orders')->result();
			$balances = $this->db->select('user_balance_id, user_balance_note')
				->where('user_balance_status', 0)
				->where('user_balance_created_at >=', $recentPaymentCutoff)
				->get('user_balance')->result();

			$paymentReferences = [];
			foreach ($orders as $order) {
				$paymentReferences[(int)$order->order_payment_token] = [
					'type' => 'order',
					'id' => (int)$order->order_id,
				];
			}
			foreach ($balances as $balance) {
				$note = json_decode((string)$balance->user_balance_note, true);
				if (is_array($note) && strtolower((string)($note['payment_method'] ?? '')) === 'cc') {
					$paymentId = (int)($note['provider_payment_id'] ?? 0);
					if ($paymentId > 0) {
						$paymentReferences[$paymentId] = [
							'type' => 'wallet',
							'id' => (int)$balance->user_balance_id,
						];
					}
				}
			}

			if (empty($paymentReferences)) {
				return json(['status' => 'success', 'checked' => 0, 'failed' => 0]);
			}

			$recentCutoff = date('Y-m-d H:i:s', time() - self::PAYMENT_QUERY_COOLDOWN_SECONDS);
			$recentLogs = $this->db->select('log_message')
				->from('logs')
				->where('log_controller', 'order.api')
				->where('log_created_at >=', $recentCutoff)
				->like('log_message', '"phase":"payment.query.cron"')
				->get()->result();
			$recentlyQueriedPaymentIds = [];
			foreach ($recentLogs as $log) {
				$payload = json_decode((string)$log->log_message, true);
				$paymentId = (int)($payload['request']['paymentId'] ?? 0);
				if ($paymentId > 0) {
					$recentlyQueriedPaymentIds[$paymentId] = true;
				}
			}
			$paymentReferences = array_diff_key($paymentReferences, $recentlyQueriedPaymentIds);
			if (empty($paymentReferences)) {
				return json(['status' => 'success', 'checked' => 0, 'failed' => 0, 'skipped' => 'cooldown']);
			}

			$this->load->library('Magicpay');
			$magicpay = new Magicpay();
			$responses = $magicpay->query3dPaymentsMulti(array_keys($paymentReferences));
			$failed = 0;
			foreach ($paymentReferences as $paymentId => $reference) {
				$response = is_array($responses[$paymentId] ?? null) ? $responses[$paymentId] : [];
				$this->orderApiLog(
					$reference['type'] === 'order' ? $reference['id'] : 0,
					'payment.query.cron',
					'outgoing',
					['paymentId' => (int)$paymentId],
					$response,
					['provider' => 'digigate', 'reference_type' => $reference['type'], 'reference_id' => $reference['id']]
				);
				// The provider reports FAILURE (isFinal=true) while the customer is still
				// in 3D Secure, so a failed/unknown query never decides the payment.
				// Only a strict SUCCESS/AUTH for a still-open card order is recovered
				// here, for the case where the OTP_SUCCESS callback never reached us.
				if ($reference['type'] === 'order') {
					$this->recoverCardOrder((int)$reference['id'], (int)$paymentId, $response, $magicpay);
				}
			}
			return json(['status' => 'success', 'checked' => count($paymentReferences), 'failed' => $failed]);
		} finally {
			$this->db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
		}
	}

	private function recoverCardOrder($orderId, $paymentId, array $query, $magicpay)
	{
		$status = strtoupper((string)($query['paymentStatus'] ?? ''));
		$phase = strtoupper((string)($query['paymentPhase'] ?? ''));
		if ($status !== 'SUCCESS' || $phase !== 'AUTH' || (int)($query['id'] ?? 0) !== $paymentId) {
			return false;
		}
		$orders = $this->db->where('order_payment_token', (string)$paymentId)
			->where('order_payment_type', 4)
			->where('order_status', 11)
			->get('orders')->result();
		if (empty($orders)) {
			return false;
		}
		$normalize = function ($value) {
			return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$value));
		};
		$reference = $normalize($orders[0]->order_reseller_order_id);
		if ($reference === ''
			|| !in_array($reference, [$normalize($query['externalId'] ?? ''), $normalize($query['conversationId'] ?? '')], true)) {
			return false;
		}
		$saleTotal = array_sum(array_map(function ($order) {
			return (float)$order->order_sale_price_tl;
		}, $orders));
		if (strtoupper((string)($query['currency'] ?? '')) !== 'TRY'
			|| abs((float)($query['paidPrice'] ?? 0) - round($saleTotal * 1.0270, 2)) > 0.01) {
			return false;
		}

		// Digigate needs 3ds-complete to finalize the payment; run it once per sweep.
		$complete = $magicpay->complete3dPayment($paymentId);
		$this->orderApiLog($orderId, 'payment.3ds_complete.cron', 'outgoing', ['paymentId' => $paymentId], $complete, ['provider' => 'digigate']);
		if (!empty($complete['transport_error']) || !empty($complete['errors']) || !empty($complete['error'])
			|| strtoupper((string)($complete['paymentStatus'] ?? '')) !== 'SUCCESS'
			|| strtoupper((string)($complete['paymentPhase'] ?? '')) !== 'AUTH') {
			return false;
		}

		$orderIDs = array_map('intval', array_column($orders, 'order_id'));
		$existingInfo = json_decode((string)$orders[0]->order_payment_info, true);
		$paymentInfo = $complete;
		foreach (['pricing_snapshot', 'cardBin', 'cardLast4', 'cardHolderName', 'cardExpiry'] as $field) {
			if (is_array($existingInfo) && !empty($existingInfo[$field])) {
				$paymentInfo[$field] = $existingInfo[$field];
			}
		}
		$this->db->trans_start();
		$this->db->where_in('order_id', $orderIDs)->where('order_status', 11)->update('orders', [
			'order_status' => 0,
			'order_payment_info' => json_encode($paymentInfo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		]);
		$this->db->where_in('coupon_usage_order_id', $orderIDs)->update('coupon_usages', [
			'coupon_usage_status' => 1,
			'coupon_usage_updated_at' => date('Y-m-d H:i:s'),
		]);
		$this->db->trans_complete();

		return $this->db->trans_status() !== false;
	}

}
