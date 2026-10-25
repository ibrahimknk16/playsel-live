<?php defined('BASEPATH') or exit('No direct script access allowed');

class Callback extends MY_Controller
{
	private function fraudReviewRequired(array $orders)
	{
		$this->load->model('FraudModel', 'fraudModel');
		foreach ($orders as $order) {
			$result = $this->fraudModel->fraudCheck((int)$order->order_id);
			if (empty($result['status'])) {
				return true;
			}
		}
		return false;
	}

	private function walletPaymentNote($note, $message)
	{
		$decoded = json_decode((string)$note, true);
		if (!is_array($decoded)) {
			$decoded = [];
		}

		$decoded['message'] = $message;
		return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	private function walletPaymentFailureNote($note, $message, array $response)
	{
		$decoded = json_decode((string)$note, true);
		if (!is_array($decoded)) {
			$decoded = [];
		}

		$error = $response['errors'] ?? $response['error'] ?? [];
		if (!is_array($error)) {
			$error = [];
		}
		$decoded['message'] = $message;
		$decoded['provider_complete_failure'] = true;
		$decoded['provider_error_code'] = (string)($error['errorCode'] ?? $error['error_code'] ?? $response['errorCode'] ?? '');
		$decoded['provider_error_description'] = (string)($error['errorDescription'] ?? $error['error_message'] ?? $error['providerMessage'] ?? '');
		$decoded['provider_error_group'] = (string)($error['errorGroup'] ?? $error['error_group'] ?? '');

		return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	private function walletPaymentProgressNote($note, $message, array $gatewayData)
	{
		$decoded = json_decode((string)$note, true);
		if (!is_array($decoded)) {
			$decoded = ['message' => $message];
		}
		$decoded['message'] = $message;
		$decoded['provider_event'] = (string)($gatewayData['eventType'] ?? $gatewayData['callbackStatus'] ?? '');
		$decoded['provider_payment_status'] = (string)($gatewayData['paymentStatus'] ?? '');
		$decoded['provider_payment_id'] = (string)($gatewayData['paymentId'] ?? $decoded['provider_payment_id'] ?? '');
		$decoded['provider_external_id'] = (string)($gatewayData['externalId'] ?? $gatewayData['conversationId'] ?? $decoded['provider_external_id'] ?? '');

		return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	private function normalizePaymentStatus($status)
	{
		$status = strtoupper(trim((string)$status));
		if (in_array($status, ['SUCCESS', 'SUCCEEDED', 'COMPLETED', 'APPROVED'], true)) {
			return 'SUCCESS';
		}
		if (in_array($status, ['FAILURE', 'FAILED', 'DECLINED', 'REJECTED', 'CANCELED', 'CANCELLED', 'ERROR'], true)) {
			return 'FAILURE';
		}
		return $status;
	}

	private function extractGatewayPaymentStatus(array $response)
	{
		foreach ([
			$response['paymentStatus'] ?? null,
			$response['data']['paymentStatus'] ?? null,
			$response['result']['paymentStatus'] ?? null,
		] as $status) {
			if (is_string($status) && trim($status) !== '') {
				return $this->normalizePaymentStatus($status);
			}
		}

		return '';
	}

	private function gatewayResponseHasError(array $response)
	{
		// Network/JSON failures say nothing about the provider's payment decision.
		if (!empty($response['transport_error'])) {
			return false;
		}
		if (strtolower(trim((string)($response['status'] ?? ''))) === 'error'
			|| !empty($response['errors'])
			|| !empty($response['error'])) {
			return true;
		}

		foreach ([
			$response['errorCode'] ?? null,
			$response['data']['errorCode'] ?? null,
			$response['result']['errorCode'] ?? null,
		] as $errorCode) {
			if (is_scalar($errorCode) && trim((string)$errorCode) !== '') {
				return true;
			}
		}

		return false;
	}

	private function gatewayField(array $response, $field)
	{
		foreach ([
			$response[$field] ?? null,
			$response['data'][$field] ?? null,
			$response['result'][$field] ?? null,
		] as $value) {
			if (is_scalar($value) && trim((string)$value) !== '') {
				return $value;
			}
		}

		return null;
	}

	// Digigate documents a 3ds-complete SUCCESS/AUTH response as the final card
	// result. It is accepted only for card payments (never mobile) and only when
	// payment id, provider reference, amount and currency match our own record.
	private function cardCompleteConfirmed(array $complete, $paymentID, array $orders, array $walletTopups)
	{
		if ($this->gatewayResponseHasError($complete)
			|| $this->extractGatewayPaymentStatus($complete) !== 'SUCCESS'
			|| strtoupper(trim((string)$this->gatewayField($complete, 'paymentPhase'))) !== 'AUTH'
			|| (int)$this->gatewayField($complete, 'id') !== (int)$paymentID) {
			return false;
		}

		if (!empty($walletTopups)) {
			$walletTopup = $walletTopups[0];
			$walletNote = json_decode((string)$walletTopup->user_balance_note, true);
			if (strtolower(trim((string)($walletNote['payment_method'] ?? 'cc'))) !== 'cc') {
				return false;
			}
			$expectedAmount = round((float)$walletTopup->user_balance_amount * 1.0270, 2);
		} elseif (!empty($orders)) {
			$saleTotal = 0.0;
			foreach ($orders as $order) {
				if ((int)$order->order_payment_type !== 4) {
					return false;
				}
				$saleTotal += (float)$order->order_sale_price_tl;
			}
			$normalize = function ($value) {
				return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$value));
			};
			$providerReferences = array_filter([
				$normalize($this->gatewayField($complete, 'externalId')),
				$normalize($this->gatewayField($complete, 'conversationId')),
			]);
			if (!in_array($normalize($orders[0]->order_reseller_order_id), $providerReferences, true)) {
				return false;
			}
			$expectedAmount = round($saleTotal * 1.0270, 2);
		} else {
			return false;
		}

		return $this->payoutAmountMatches($complete, $expectedAmount);
	}

	private function payoutAmountMatches(array $response, $expectedAmount, $expectedCurrency = 'TRY')
	{
		$paidPrice = $this->gatewayField($response, 'paidPrice');
		$currency = strtoupper(trim((string)$this->gatewayField($response, 'currency')));
		if (!is_numeric($paidPrice) || (float)$paidPrice <= 0 || $currency !== strtoupper($expectedCurrency)) {
			return false;
		}

		return abs((float)$paidPrice - (float)$expectedAmount) <= 0.01;
	}

	private function resolvePaymentId(array $callbackReferences, $paymentID)
	{
		$paymentID = (int)$paymentID;
		if ($paymentID > 0) {
			return $paymentID;
		}

		foreach ($callbackReferences as $reference) {
			$reference = trim((string)$reference);
			if ($reference === '') {
				continue;
			}

			$order = $this->db
				->select('order_payment_token')
				->group_start()
				->where('order_reseller_order_id', $reference)
				->or_where("REPLACE(order_reseller_order_id, '_', '') = " . $this->db->escape(str_replace('_', '', $reference)), null, false)
				->group_end()
				->where('order_payment_token IS NOT NULL', null, false)
				->where('order_payment_token <>', '')
				->order_by('order_id', 'ASC')
				->get('orders')
				->row();
			if ($order && (int)$order->order_payment_token > 0) {
				return (int)$order->order_payment_token;
			}

			$wallet = $this->db
				->select('user_balance_note')
				->like('user_balance_reference_no', $reference)
				->where_in('user_balance_status', [0, 1])
				->order_by('user_balance_id', 'DESC')
				->get('user_balance')
				->row();
			if ($wallet) {
				$note = json_decode((string)$wallet->user_balance_note, true);
				$walletPaymentID = (int)($note['provider_payment_id'] ?? 0);
				if ($walletPaymentID > 0) {
					return $walletPaymentID;
				}
			}
		}

		return 0;
	}

	private function findWalletTopups($paymentID, array $callbackData, $includeApproved = false)
	{
		if ($includeApproved) {
			$this->db->where_in('user_balance_status', [0, 1]);
		} else {
			$this->db->where('user_balance_status', 0);
		}
		if ((int)$paymentID > 0) {
			$this->db->group_start()
				->where_in('user_balance_reference_no', [
					'cc_' . (string)$paymentID,
					'mobile_' . (string)$paymentID,
				])
				->or_like('user_balance_note', '"provider_payment_id":"' . (string)$paymentID . '"')
				->or_like('user_balance_note', (string)$paymentID)
				->group_end();
			$matches = $this->db->get('user_balance')->result();
			if (!empty($matches)) {
				return $matches;
			}

			$this->db->reset_query();
			$this->db->where('user_balance_status', 0)
				->like('user_balance_note', (string)$paymentID);
			return $this->db->get('user_balance')->result();
		} else {
			$providerReferences = [];
			$collectReferences = function ($value) use (&$collectReferences, &$providerReferences) {
				if (!is_array($value)) {
					return;
				}
				foreach (['payloadId', 'orderId', 'externalId', 'conversationId', 'provider_payload_id', 'provider_order_id', 'provider_external_id', 'provider_conversation_id'] as $key) {
					if (isset($value[$key]) && is_scalar($value[$key])) {
						$reference = trim((string)$value[$key]);
						if ($reference !== '') {
							$providerReferences[] = $reference;
						}
					}
				}
				foreach ($value as $nestedValue) {
					if (is_array($nestedValue)) {
						$collectReferences($nestedValue);
					}
				}
			};
			$collectReferences($callbackData);
			$providerReferences = array_values(array_unique($providerReferences));
			if (empty($providerReferences)) {
				return [];
			}

			$this->db->group_start();
			foreach ($providerReferences as $index => $providerReference) {
				$normalizedReference = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $providerReference));
				$normalizedLike = '%' . $normalizedReference . '%';
				if ($index > 0) {
					$this->db->or_like('user_balance_reference_no', $providerReference)
						->or_like('user_balance_note', $providerReference)
						->or_where("LOWER(REPLACE(REPLACE(user_balance_reference_no, '_', ''), '-', '')) LIKE", $normalizedLike)
						->or_where("LOWER(REPLACE(REPLACE(user_balance_note, '_', ''), '-', '')) LIKE", $normalizedLike);
				} else {
					$this->db->group_start()
						->like('user_balance_reference_no', $providerReference)
						->or_like('user_balance_note', $providerReference)
						->or_where("LOWER(REPLACE(REPLACE(user_balance_reference_no, '_', ''), '-', '')) LIKE", $normalizedLike)
						->or_where("LOWER(REPLACE(REPLACE(user_balance_note, '_', ''), '-', '')) LIKE", $normalizedLike)
						->group_end();
				}
			}
			$this->db->group_end();
		}

		return $this->db->get('user_balance')->result();
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

	public function magicpayCallback()
	{
		return $this->handleMagicpayCallback(null);
	}

	public function magicpayOrderCallback()
	{
		return $this->handleMagicpayCallback('order');
	}

	public function magicpayWalletCallback()
	{
		return $this->handleMagicpayCallback('wallet');
	}

	private function handleRefundCallback(array $callbackOrders, $callbackStatus, $forcedType, $paymentID, array $data)
	{
		// The refund webhook is unsigned and carries no amount, so it is only
		// recorded (already logged above) and never changes order/wallet state.
		return json(['status' => 'success', 'message' => 'İade bildirimi kaydedildi.'], 200);
	}

	private function handleMagicpayCallback($forcedType = null)
	{
		$rawPayload = file_get_contents('php://input');
		$jsonPayload = json_decode($rawPayload, true);
		$receivedLogId = null;
		$this->db->insert('logs', [
			'log_controller' => 'magicpayCallback.received',
			'log_message' => json_encode([
				'event' => is_array($jsonPayload) ? ($jsonPayload['eventType'] ?? $jsonPayload['type'] ?? null) : null,
				'status' => is_array($jsonPayload) ? ($jsonPayload['status'] ?? null) : null,
				'payloadId' => is_array($jsonPayload) ? ($jsonPayload['payloadId'] ?? null) : null,
				'orderId' => is_array($jsonPayload) ? ($jsonPayload['orderId'] ?? null) : null,
				'paymentId' => is_array($jsonPayload) ? ($jsonPayload['paymentId'] ?? null) : null,
				'method' => $_SERVER['REQUEST_METHOD'] ?? '',
				'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
				'payload_length' => strlen((string)$rawPayload),
				'payload_hash' => hash('sha256', (string)$rawPayload),
				'json_error' => json_last_error_msg(),
				'forced_type' => $forcedType,
			], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'log_error_code' => 54541568,
		]);
		$receivedLogId = $this->db->insert_id();
		register_shutdown_function(function () use ($receivedLogId) {
			$error = error_get_last();
			if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
				$this->db->insert('logs', [
					'log_controller' => 'magicpayCallback.fatal',
					'log_message' => json_encode([
						'received_log_id' => $receivedLogId,
						'message' => $error['message'],
						'file' => basename((string)$error['file']),
						'line' => $error['line'],
					], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
					'log_error_code' => 54541570,
				]);
			}
		});
		$data = is_array($jsonPayload) ? $jsonPayload : array_merge($_GET, $_POST);

		$callbackEvent = strtoupper(trim((string)($data['eventType'] ?? $data['callbackStatus'] ?? '')));
		$callbackStatus = $this->normalizePaymentStatus(
			$data['paymentStatus']
				?? $data['data']['paymentStatus']
				?? ($callbackEvent === 'PAYOUT_COMPLETED' ? ($data['status'] ?? '') : '')
		);
		$paymentID = (int)($data['paymentId'] ?? $data['payment_id'] ?? $data['id'] ?? 0);
		$callbackReferences = array_values(array_unique(array_filter([
			(string)($data['payloadId'] ?? ''),
			(string)($data['orderId'] ?? ''),
			(string)($data['conversationId'] ?? ''),
			(string)($data['externalId'] ?? ''),
		])));
		$paymentID = $this->resolvePaymentId($callbackReferences, $paymentID);
		$callbackOrders = [];
		if ($paymentID > 0) {
			$callbackOrders = $this->db
				->where('order_payment_token', (string)$paymentID)
				->order_by('order_id', 'ASC')
				->get('orders')->result();
		}
		if ($paymentID <= 0 && empty($callbackOrders) && !empty($callbackReferences)) {
			$this->db->group_start();
			foreach ($callbackReferences as $index => $callbackReference) {
				if ($index > 0) {
					$this->db->or_where('order_reseller_order_id', $callbackReference);
				} else {
					$this->db->where('order_reseller_order_id', $callbackReference);
				}
				$this->db->or_where(
					"REPLACE(order_reseller_order_id, '_', '') = " . $this->db->escape(str_replace('_', '', $callbackReference)),
					null,
					false
				);
			}
			$this->db->group_end()
				->order_by('order_id', 'ASC');
			$orderQuery = $this->db->get('orders');
			if ($orderQuery !== false) {
				$callbackOrders = $orderQuery->result();
			} else {
				log_message('error', 'Digigate callback order reference lookup failed: ' . json_encode([
					'paymentId' => $paymentID,
					'references' => $callbackReferences,
					'dbError' => $this->db->error(),
				], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
			}
		}
		$callbackOrder = $callbackOrders[0] ?? null;
		$callbackOrderID = $callbackOrder ? (int)$callbackOrder->order_id : null;
		$this->db->insert('logs', [
			'log_controller' => 'magicpayCallback',
			'log_message' => json_encode($data, JSON_UNESCAPED_UNICODE),
			'log_error_code' => 54541569,
			'log_order_id' => $callbackOrderID,
		]);
		$this->orderApiLog($callbackOrderID, 'payment.callback', 'incoming', $data, null, [
			'provider' => 'digigate',
			'event' => $callbackEvent,
			'payment_id' => $paymentID,
		]);
		$isFailureCallback = $callbackStatus === 'FAILURE'
			|| strtoupper(trim((string)($data['status'] ?? ''))) === 'FAILURE';
		if ($callbackEvent === 'REFUND') {
			return $this->handleRefundCallback($callbackOrders, $callbackStatus, $forcedType, $paymentID, $data);
		}
		$isPayoutCallback = $callbackEvent === 'PAYOUT_COMPLETED';
		$isPaymentCallback = in_array($callbackEvent, [
			'API_AUTH',
			'API_VERIFY_AND_AUTH',
			'CHECKOUTFORM_AUTH',
			'THREEDS_VERIFY',
			'OTP_SUCCESS',
		], true);
		$pendingWalletTopups = $this->findWalletTopups(
			$paymentID,
			$data,
			$isFailureCallback || $isPayoutCallback
		);
		if ($forcedType === 'order' || ($forcedType === null && !empty($callbackOrders))) {
			$pendingWalletTopups = [];
		} elseif ($forcedType === 'wallet') {
			$callbackOrders = [];
			$callbackOrder = null;
			$callbackOrderID = null;
		}
		if ($forcedType === 'wallet' && empty($pendingWalletTopups)) {
			return json(['status' => 'success', 'message' => 'Bakiye ödemesi eşleşmedi, bildirim alındı.'], 200);
		}
		if ($forcedType === 'order' && empty($callbackOrders)) {
			return json(['status' => 'error', 'message' => 'Sipariş ödemesi eşleşmedi.'], 404);
		}
		if ($paymentID <= 0 && !empty($pendingWalletTopups)) {
			$walletNote = json_decode((string)$pendingWalletTopups[0]->user_balance_note, true);
			$paymentID = (int)($walletNote['provider_payment_id'] ?? 0);
		}
		if ($paymentID <= 0 && !empty($callbackOrders)) {
			$paymentID = (int)($callbackOrders[0]->order_payment_token ?? 0);
		}
		$storedCompleteFailure = false;
		foreach ($pendingWalletTopups as $walletTopup) {
			$walletNote = json_decode((string)$walletTopup->user_balance_note, true);
			if (is_array($walletNote) && !empty($walletNote['provider_complete_failure'])) {
				$storedCompleteFailure = true;
				break;
			}
		}
		if (!$storedCompleteFailure) {
			foreach ($callbackOrders as $callbackOrder) {
				$orderPaymentInfo = json_decode((string)$callbackOrder->order_payment_info, true);
				if (is_array($orderPaymentInfo) && !empty($orderPaymentInfo['provider_complete_failure'])) {
					$storedCompleteFailure = true;
					break;
				}
			}
		}
		if (!$storedCompleteFailure && $paymentID > 0 && $forcedType !== 'order') {
			$failedWallet = $this->db
				->where('user_balance_status', 2)
				->like('user_balance_note', '"provider_payment_id":"' . (string)$paymentID . '"')
				->group_start()
				->like('user_balance_note', '"provider_complete_failure":true')
				->or_like('user_balance_note', '"provider_error_code":"')
				->group_end()
				->get('user_balance')
				->row();
			$storedCompleteFailure = $failedWallet !== null;
		}
		$isWalletPayout = $isPayoutCallback && !empty($pendingWalletTopups);
		if ($isPayoutCallback) {
			log_message('info', 'Digigate payout callback: ' . json_encode([
				'status' => $callbackStatus,
				'paymentId' => $paymentID,
				'orderId' => $callbackOrderID,
				'payloadId' => $data['payloadId'] ?? null,
				'matchCount' => count($pendingWalletTopups),
			], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		}

		if ($isPaymentCallback && empty($paymentID)) {
			return json(['status' => 'error', 'message' => 'Digigate paymentId eksik.'], 400);
		}
		if ($storedCompleteFailure && $isPaymentCallback && empty($callbackOrders)) {
			return json(['status' => 'error', 'message' => 'Ödeme daha önce başarısız olarak kaydedildi.'], 200);
		}

		if (!$isPaymentCallback && !$isWalletPayout && $callbackStatus === '') {
			return json(['status' => 'success', 'message' => 'Bildirim alındı.'], 200);
		}

		if ($isPayoutCallback) {
			if (!in_array($callbackStatus, ['SUCCESS', 'FAILURE'], true)) {
				return json(['status' => 'pending', 'message' => 'Payout sonucu bekleniyor.'], 200);
			}

			$this->load->library('Magicpay');
			$magicpay = new Magicpay();
			$queryResponse = $paymentID > 0
				? $magicpay->query3dPayment($paymentID)
				: [];
			$this->orderApiLog($callbackOrderID, 'payment.query', 'outgoing', [
				'paymentId' => $paymentID,
			], $queryResponse, ['provider' => 'digigate', 'event' => $callbackEvent]);
			$queryStatus = $this->extractGatewayPaymentStatus($queryResponse);
			log_message('info', 'Digigate payout callback verified. Payment: ' . $paymentID . ', Callback status: ' . $callbackStatus . ', Payment status: ' . ($queryStatus ?: 'UNKNOWN'));
			if ($callbackStatus === 'FAILURE' || $queryStatus === 'FAILURE' || $this->gatewayResponseHasError($queryResponse)) {
				$callbackStatus = 'FAILURE';
			} elseif ($queryStatus !== 'SUCCESS') {
				return json(['status' => 'pending', 'message' => 'Kart ödeme sonucu henüz kesinleşmedi.'], 200);
			} elseif (!empty($pendingWalletTopups)) {
				$walletTopup = $pendingWalletTopups[0];
				$walletNote = json_decode((string)$walletTopup->user_balance_note, true);
				$paymentMethod = strtolower(trim((string)($walletNote['payment_method'] ?? 'cc')));
				$rate = $paymentMethod === 'mobile' ? 1.60 : 1.0270;
				$expectedAmount = round((float)$walletTopup->user_balance_amount * $rate, 2);
				if (!$this->payoutAmountMatches($queryResponse, $expectedAmount)) {
					log_message('error', 'Digigate payout amount mismatch for wallet balance ' . (int)$walletTopup->user_balance_id);
					return json(['status' => 'pending', 'message' => 'Ödeme tutarı doğrulanamadı, işlem incelemede.'], 200);
				}
			} else {
				$orderSaleTotal = array_sum(array_map(function ($order) {
					return (float)$order->order_sale_price_tl;
				}, $callbackOrders));
				$isMobileOrder = !empty(array_filter($callbackOrders, function ($order) {
					return (int)$order->order_payment_type === 5;
				}));
				$expectedAmount = round($orderSaleTotal * ($isMobileOrder ? 1.60 : 1.0270), 2);
				if (!$this->payoutAmountMatches($queryResponse, $expectedAmount)) {
					log_message('error', 'Digigate payout amount mismatch for order payment ' . $paymentID);
					return json(['status' => 'pending', 'message' => 'Ödeme tutarı doğrulanamadı, işlem incelemede.'], 200);
				}
			}

			$this->db->trans_start();
			if (!empty($pendingWalletTopups)) {
				foreach ($pendingWalletTopups as $walletTopup) {
					$this->db->where('user_balance_id', (int)$walletTopup->user_balance_id)
						->where_in('user_balance_status', [0, 1])
						->update('user_balance', [
							'user_balance_status' => $callbackStatus === 'SUCCESS' ? 1 : 2,
							'user_balance_payment_date' => $callbackStatus === 'SUCCESS' ? date('Y-m-d H:i:s') : null,
							'user_balance_note' => $this->walletPaymentNote(
								$walletTopup->user_balance_note,
								$callbackStatus === 'SUCCESS' ? 'Bakiye yükleme onaylandı' : 'Bakiye yükleme başarısız'
							),
						]);
				}
			}
			if (!empty($callbackOrders)) {
				$orderIDs = array_map('intval', array_column($callbackOrders, 'order_id'));
				// A verified PAYOUT_COMPLETED success (query + amount checked above)
				// also recovers an order wrongly closed as payment-failed (12).
				$fraudReview = $callbackStatus === 'SUCCESS' && $this->fraudReviewRequired($callbackOrders);
				$this->db->where_in('order_id', $orderIDs)
					->where_in('order_status', $callbackStatus === 'SUCCESS' ? [0, 11, 12, 14, 15] : [0, 11, 14, 15])
					->update('orders', [
						// 0: payment approved, waiting for supplier delivery.
						// 1 is reserved for supplier delivery completion.
						'order_status' => $callbackStatus === 'SUCCESS' ? ($fraudReview ? 13 : 0) : 12,
						'order_payment_info' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
					]);
				if ($callbackStatus === 'FAILURE') {
					$this->db->where_in('coupon_usage_order_id', $orderIDs)->update('coupon_usages', [
						'coupon_usage_status' => 0,
						'coupon_usage_updated_at' => date('Y-m-d H:i:s'),
					]);
				}
			}
			$this->db->trans_complete();
			if ($this->db->trans_status() === false) {
				return json(['status' => 'error', 'message' => 'Payout sonucu kaydedilemedi.'], 500);
			}
			return json([
				'status' => $callbackStatus === 'SUCCESS' ? 'success' : 'error',
				'message' => $callbackStatus === 'SUCCESS'
					? 'Payout başarılı, ödeme sonucu işlendi.'
					: 'Payout başarısız, ödeme reddedildi.',
			], 200);
		}

		if ($paymentID <= 0) {
			if ($callbackStatus === 'FAILURE') {
				log_message('error', 'Digigate failure callback missing paymentId: ' . json_encode([
					'payloadId' => $data['payloadId'] ?? null,
					'orderId' => $data['orderId'] ?? null,
				], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
			}
			return json(['status' => 'error', 'message' => 'Digigate paymentId eksik.'], 400);
		}

		$complete = [];
		$paymentStatus = $callbackStatus;
		if ($isWalletPayout) {
			$complete = $data;
			$paymentStatus = $callbackStatus;
			if ($paymentID > 0) {
				$this->load->library('Magicpay');
				$magicpay = new Magicpay();
				$queryResponse = $magicpay->query3dPayment($paymentID);
				$queryStatus = $this->extractGatewayPaymentStatus($queryResponse);
				if (in_array($queryStatus, ['SUCCESS', 'FAILURE'], true)) {
					$complete = $queryResponse;
					$paymentStatus = $queryStatus;
				}
			}
		} elseif ($callbackEvent === 'OTP_SUCCESS' || $callbackEvent === 'THREEDS_VERIFY') {
			$openOrders = array_filter($callbackOrders, function ($order) {
				return in_array((int)$order->order_status, [11, 14, 15], true);
			});
			if (!$storedCompleteFailure && empty($openOrders) && empty($pendingWalletTopups)) {
				// Repeated provider callback for an already settled payment.
				return json(['status' => 'success', 'message' => 'Ödeme daha önce işlendi.'], 200);
			}
			if ($storedCompleteFailure) {
				$complete = [];
				$paymentStatus = 'FAILURE';
			} else {
			$this->load->library('Magicpay');
			$magicpay = new Magicpay();
			$complete = $magicpay->complete3dPayment($paymentID);
			$this->orderApiLog($callbackOrderID, 'payment.3ds_complete', 'outgoing', [
				'paymentId' => $paymentID,
			], $complete, ['provider' => 'digigate', 'event' => $callbackEvent]);
			$this->db->insert('logs', [
				'log_controller' => 'magicpayCallback.verify',
				'log_message' => json_encode([
					'paymentId' => $paymentID,
					'callbackPaymentStatus' => $callbackStatus,
					'completeResponseStatus' => $complete['status'] ?? null,
					'completePaymentStatus' => $this->extractGatewayPaymentStatus($complete),
					'status' => $complete['status'] ?? null,
					'error' => $complete['errors'] ?? null,
				], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				'log_error_code' => 54541570,
				'log_order_id' => $callbackOrderID,
			]);
			// A provider-declared rejection is persisted as a failure. A card
			// SUCCESS/AUTH is final only after the strict checks below; anything
			// else (including mobile) keeps waiting for the provider callbacks.
			if ($this->gatewayResponseHasError($complete)) {
				$paymentStatus = 'FAILURE';
			} elseif ($this->cardCompleteConfirmed($complete, $paymentID, $callbackOrders, $pendingWalletTopups)) {
				$paymentStatus = 'SUCCESS';
			} else {
				$paymentStatus = 'WAITING';
			}
			}
		} elseif ($callbackStatus !== 'FAILURE' && empty($pendingWalletTopups)) {
			$this->load->library('Magicpay');
			$magicpay = new Magicpay();
			$complete = $magicpay->query3dPayment($paymentID);
			$paymentStatus = $this->extractGatewayPaymentStatus($complete);
			$paymentStatus = $paymentStatus ?: $callbackStatus;
		}

		// Only PAYOUT_COMPLETED can finalize a successful payment. A provider
		// failure must still be persisted immediately so the order cannot
		// remain eligible for supplier delivery.
		if (!in_array($paymentStatus, ['SUCCESS', 'FAILURE'], true)) {
			$paymentStatus = 'WAITING';
		}

		if (!empty($pendingWalletTopups)) {
			$this->db->trans_start();
			if ($paymentStatus === 'SUCCESS') {
				foreach ($pendingWalletTopups as $walletTopup) {
					$this->db->where('user_balance_id', (int)$walletTopup->user_balance_id)->update('user_balance', [
						'user_balance_status' => 1,
						'user_balance_payment_date' => date('Y-m-d H:i:s'),
						'user_balance_note' => $this->walletPaymentNote($walletTopup->user_balance_note, 'Bakiye yükleme onaylandı'),
					]);
				}
			} elseif ($paymentStatus === 'FAILURE') {
				foreach ($pendingWalletTopups as $walletTopup) {
					$this->db->where('user_balance_id', (int)$walletTopup->user_balance_id)->update('user_balance', [
						'user_balance_status' => 2,
						'user_balance_note' => !empty($complete)
							? $this->walletPaymentFailureNote($walletTopup->user_balance_note, 'Bakiye yükleme başarısız', $complete)
							: $this->walletPaymentNote($walletTopup->user_balance_note, 'Bakiye yükleme başarısız'),
					]);
				}
			} elseif (in_array($paymentStatus, ['WAITING', 'OTP_WAITING', 'PENDING', 'AUTH'], true) || $callbackEvent === 'OTP_SUCCESS') {
				foreach ($pendingWalletTopups as $walletTopup) {
					$this->db->where('user_balance_id', (int)$walletTopup->user_balance_id)
						->where('user_balance_status', 0)
						->update('user_balance', [
							'user_balance_note' => $this->walletPaymentProgressNote($walletTopup->user_balance_note, 'OTP doğrulandı, ödeme sonucu bekleniyor', $data),
						]);
				}
			}
			$this->db->trans_complete();
			if ($this->db->trans_status() === false) {
				return json(['status' => 'error', 'message' => 'Bakiye yükleme kaydı güncellenemedi.'], 500);
			}
			if ($paymentStatus === 'SUCCESS') {
				return json(['status' => 'success', 'message' => 'Bakiye yükleme başarılı.']);
			}
			if (in_array($paymentStatus, ['WAITING', 'OTP_WAITING', 'PENDING', 'AUTH'], true) || $callbackEvent === 'OTP_SUCCESS') {
				return json(['status' => 'pending', 'message' => 'OTP doğrulandı, ödeme sonucu bekleniyor.'], 200);
			}
			return json(['status' => 'error', 'message' => 'Ödeme başarısız oldu.']);
		}

		$orders = $this->db->where('order_payment_token', (string)$paymentID)->get('orders')->result();
		if (empty($orders)) {
			return json(['status' => 'error', 'message' => 'Ödeme grubuna ait sipariş bulunamadı.'], 404);
		}
		$orderIDs = array_map('intval', array_column($orders, 'order_id'));
		$allOrdersFailed = !empty($orders) && !array_filter($orders, function ($order) {
			return (int)$order->order_status !== 12;
		});
		if ($allOrdersFailed && $paymentStatus === 'FAILURE') {
			return json(['status' => 'error', 'message' => 'Ödeme sonucu daha önce işlendi.'], 200);
		}
		if ($paymentStatus === 'FAILURE' && !empty($complete)) {
			$complete['provider_complete_failure'] = true;
		}
		$paymentInfoJson = json_encode($complete, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$existingOrderInfo = json_decode((string)$orders[0]->order_payment_info, true);
		if (is_array($existingOrderInfo)) {
			$paymentInfo = is_array($complete) ? $complete : [];
			if (isset($existingOrderInfo['pricing_snapshot'])) {
				$paymentInfo['pricing_snapshot'] = $existingOrderInfo['pricing_snapshot'];
			}
			foreach (['cardBin', 'cardLast4', 'cardHolderName', 'cardExpiry'] as $cardField) {
				if (!empty($existingOrderInfo[$cardField])) {
					$paymentInfo[$cardField] = $existingOrderInfo[$cardField];
				}
			}
			$paymentInfoJson = json_encode($paymentInfo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}

		if ($paymentStatus === 'SUCCESS') {
			$orderSaleTotal = array_sum(array_map(function ($order) {
				return (float)$order->order_sale_price_tl;
			}, $orders));
			$isMobileOrder = !empty(array_filter($orders, function ($order) {
				return (int)$order->order_payment_type === 5;
			}));
			$expectedPaidPrice = $isMobileOrder ? round($orderSaleTotal * 1.60, 2) : round($orderSaleTotal * 1.0270, 2);
			$paidPrice = (float)($complete['paidPrice'] ?? 0);
			if (abs($paidPrice - $expectedPaidPrice) > 0.01) {
				return json(['status' => 'error', 'message' => 'Ödeme tutarı sipariş toplamıyla eşleşmiyor.'], 400);
			}
			// Payment approval is not supplier delivery completion.
			$fraudReview = $this->fraudReviewRequired($orders);
			$orderStatus = $fraudReview ? 13 : 0;
			$this->db->trans_start();
			$this->db->where_in('order_id', $orderIDs)
				->where_in('order_status', [0, 11, 14, 15])
				->update('orders', [
				'order_status' => $orderStatus,
				'order_payment_info' => $paymentInfoJson,
			]);
			$this->db->where_in('coupon_usage_order_id', $orderIDs)->update('coupon_usages', [
				'coupon_usage_status' => 1,
				'coupon_usage_updated_at' => date('Y-m-d H:i:s'),
			]);
			$this->db->trans_complete();
			if ($this->db->trans_status() === false) {
				return json(['status' => 'error', 'message' => 'Ödeme sonucu siparişlere işlenemedi.'], 500);
			}
			$this->send_ga4_purchase_event((string)$paymentID, 'SUCCESS', $orderSaleTotal);
			return json(['status' => 'success', 'message' => 'Ödeme başarılı, tüm siparişler işleme alındı.']);
		}

		if ($paymentStatus === 'FAILURE') {
			$this->db->trans_start();
			$this->db->where_in('order_id', $orderIDs)
				->where_in('order_status', [0, 11, 14, 15])
				->update('orders', [
				'order_status' => 12,
				'order_payment_info' => $paymentInfoJson,
			]);
			$this->db->where_in('coupon_usage_order_id', $orderIDs)->update('coupon_usages', [
				'coupon_usage_status' => 0,
				'coupon_usage_updated_at' => date('Y-m-d H:i:s'),
			]);
			$this->db->trans_complete();
			return json(['status' => 'error', 'message' => 'Kredi kartı ödemesi başarısız oldu.']);
		}

		return json(['status' => 'pending', 'message' => 'Ödeme sonucu bekleniyor.'], 200);
	}

	public function send_ga4_purchase_event($orderId, $status, $amount)
	{
		// GA4 Measurement Protocol bilgilerinizi buraya girin:
		$measurementId = getenv('GA4_MEASUREMENT_ID');
		$apiSecret     = getenv('GA4_API_SECRET');
		if (empty($measurementId) || empty($apiSecret)) {
			log_message('warning', 'GA4 credentials are not configured; purchase event skipped.');
			return;
		}

		$url = "https://www.google-analytics.com/mp/collect"
			. "?measurement_id={$measurementId}"
			. "&api_secret={$apiSecret}";

		// Benzersiz client_id (sunucu-taraflı tracking)
		$clientId = bin2hex(random_bytes(8)) . '.' . time();

		$payload = [
			'client_id' => $clientId,
			'events'    => [[
				'name'   => 'purchase',
				'params' => [
					'transaction_id' => (string)$orderId,
					'value'          => (float)$amount,
					'currency'       => 'TRY',
					'payment_status' => strtolower($status),
					'engagement_time_msec' => 1, // Zorunlu alan, 1 milisaniye olarak ayarlandı
				],
			]],
		];
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => true,            // include headers in output
			CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=UTF-8'],
			CURLOPT_POSTFIELDS     => json_encode($payload),
			CURLOPT_TIMEOUT        => 10,
		]);

		$response = curl_exec($ch);
		if ($response === false) {
			$err = curl_error($ch);
			log_message('error', 'GA4 cURL error: ' . $err);
		} else {
			// separate header and body
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header      = substr($response, 0, $header_size);
			$body        = substr($response, $header_size);
			$httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			log_message('info', "GA4 HTTP code: {$httpCode}");
			log_message('info', "GA4 response headers:\n{$header}");
			log_message('info', "GA4 response body:\n{$body}");

			if ($httpCode !== 204) {
				// 204 No Content is expected success
				log_message('error', "GA4 unexpected HTTP code {$httpCode}, body: {$body}");
			}
		}
		curl_close($ch);
	}

	public function dijipinAPI()
	{
		echo "Dijipin API Callback Endpoint";
	}

	public function dijipinCallback()
	{
		try {
			$postData = file_get_contents('php://input');

			$this->db->insert('logs', [
				'log_controller' => 'dijipinCallback',
				'log_message' => $postData,
				'log_error_code' => 'dijipin_callback_received'
			]);

			$data = json_decode($postData, true);

			if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
				$this->db->insert('logs', [
					'log_controller' => 'dijipinCallback',
					'log_message' => 'Invalid JSON: ' . $postData,
					'log_error_code' => 'dijipin_callback_invalid_json'
				]);
				http_response_code(400);
				echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
				return;
			}

			$callbackData = $data['data'] ?? [];

			if (empty($callbackData)) {
				$this->db->insert('logs', [
					'log_controller' => 'dijipinCallback',
					'log_message' => 'Callback data boş: ' . $postData,
					'log_error_code' => 'dijipin_callback_empty_data'
				]);
				http_response_code(400);
				echo json_encode(['status' => 'error', 'message' => 'Callback data is empty']);
				return;
			}

			// transactionID: Dijipin tarafındaki orderID, referenceNumber: bizim orders.order_id
			$transactionID = trim((string) ($callbackData['transactionID'] ?? ''));
			$this->load->library('Dijipin');
			$referenceNumber = (new Dijipin())->localOrderID($callbackData['referanceNumber'] ?? '');
			$status = isset($callbackData['status']) ? (int) $callbackData['status'] : null;
			$statusText = trim((string) ($callbackData['statusText'] ?? ''));
			$voucher = $callbackData['voucher'] ?? [];

			if ($transactionID === '' || $referenceNumber === '') {
				$this->db->insert('logs', [
					'log_controller' => 'dijipinCallback',
					'log_message' => 'Eksik transactionID/referenceNumber: ' . $postData,
					'log_error_code' => 'dijipin_callback_missing_order'
				]);
				http_response_code(400);
				echo json_encode(['status' => 'error', 'message' => 'Missing transactionID or referanceNumber']);
				return;
			}

			$order = $this->db
				->select('*')
				->from('orders')
				->where('order_id', $referenceNumber)
				->where('order_reseller_order_id', $transactionID)
				->limit(1)
				->get()
				->row();

			if (empty($order)) {
				$this->db->insert('logs', [
					'log_controller' => 'dijipinCallback',
					'log_message' => 'Sipariş bulunamadı - Local Order: ' . $referenceNumber
						. ' - Transaction ID: ' . $transactionID . ' - Response: ' . $postData,
					'log_error_code' => 'dijipin_callback_order_not_found',
					'log_order_id' => $referenceNumber
				]);
				http_response_code(404);
				echo json_encode(['status' => 'error', 'message' => 'Order not found']);
				return;
			}

			// Dijipin: 0 Pending, 1 Completed, 2 Cancelled, 3 Processing
			switch ($status) {
				case 1:
					$orderStatus = 4;
					break;
				case 2:
					$orderStatus = 5;
					break;
				case 3:
				case 0:
				default:
					$orderStatus = 3;
					break;
			}

			// Kesinleşmiş (teslim/iptal/iade akışındaki) siparişler geç gelen callback ile geri açılamaz.
			if (in_array((int) $order->order_status, [1, 2, 4, 5], true)) {
				$this->db->insert('logs', [
					'log_controller' => 'dijipinCallback',
					'log_message' => 'Kesinleşmiş sipariş için callback yok sayıldı. Mevcut durum: ' . (int) $order->order_status . ' - Gelen: ' . $postData,
					'log_error_code' => 'dijipin_callback_ignored_final_status',
					'log_order_id' => $order->order_id
				]);
				http_response_code(200);
				echo json_encode(['status' => 'success', 'message' => 'Callback already processed']);
				return;
			}

			$updateData = [
				'order_status' => $orderStatus,
				'order_sended_at' => date('Y-m-d H:i:s')
			];

			if (!empty($voucher)) {
				$updateData['order_voucher'] = json_encode($voucher, JSON_UNESCAPED_UNICODE);
			}

			// Mevcut order_data (pricing_snapshot vb.) korunur; callback bilgisi üzerine eklenir.
			if ($statusText !== '') {
				$existingData = json_decode((string) $order->order_data, true);
				$existingData = is_array($existingData) ? $existingData : [];
				$existingData['status'] = $status;
				$existingData['statusText'] = $statusText;
				$existingData['transactionID'] = $transactionID;
				$existingData['referanceNumber'] = $referenceNumber;
				if ($status === 2) {
					$existingData['cancellation_reason'] = $statusText;
				}
				$updateData['order_data'] = json_encode($existingData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}

			$this->db
				->where('order_id', $order->order_id)
				->where('order_reseller_order_id', $transactionID)
				->where_in('order_status', [0, 3])
				->update('orders', $updateData);

			if ($status === 1) {
				$this->db->insert('logs', [
					'log_controller' => 'dijipinCallback',
					'log_message' => $postData,
					'log_error_code' => 'dijipin_callback_success',
					'log_order_id' => $order->order_id
				]);
			} elseif ($status === 2) {
				$this->db->insert('logs', [
					'log_controller' => 'dijipinCallback',
					'log_message' => 'Dijipin sipariş iptal edildi. Order ID: ' . $order->order_id
						. ' - Transaction ID: ' . $transactionID . ' - Reason: ' . $statusText,
					'log_error_code' => 'dijipin_callback_cancelled',
					'log_order_id' => $order->order_id
				]);
			} else {
				$this->db->insert('logs', [
					'log_controller' => 'dijipinCallback',
					'log_message' => $postData,
					'log_error_code' => 'dijipin_callback_status_' . $status,
					'log_order_id' => $order->order_id
				]);
			}

			http_response_code(200);
			echo json_encode(['status' => 'success', 'message' => 'Callback received']);
		} catch (Exception $e) {
			$this->db->insert('logs', [
				'log_controller' => 'dijipinCallback',
				'log_message' => $e->getMessage(),
				'log_error_code' => 'dijipin_callback_exception'
			]);
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
		}
	}

	public function checkoutWebhook()
	{
		// 1. Checkout.com Webhook Secret Anahtarı
		$webhookSecret = getenv('CHECKOUT_WEBHOOK_SECRET');
		if (empty($webhookSecret)) {
			log_message('error', 'Checkout webhook secret is not configured.');
			return json(['status' => 'error', 'message' => 'Webhook yapılandırması eksik.'], 503);
		}

		// 2. Raw Payload ve JSON Decode işlemleri
		$payload = file_get_contents('php://input');

		// Ham veriyi hemen logla (Eğer süreç yarıda kesilirse elimizde veri kalsın)
		$this->db->insert('logs', [
			'log_controller' => 'payment',
			'log_message'    => json_encode(['type' => $data['type'] ?? null, 'reference' => $data['data']['reference'] ?? null]),
			'log_error_code' => 'chk0001',
		]);

		$data = json_decode($payload, true);
		if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
			return json(['status' => 'error', 'message' => 'Geçersiz JSON verisi'], 400);
		}

		// 3. Webhook İmza Doğrulaması (Cko-Signature)
		$headers = array_change_key_case(getallheaders(), CASE_LOWER);
		$signatureHeader = $headers['cko-signature'] ?? null;

		if (!$signatureHeader) {
			return json(['status' => 'error', 'message' => 'İmza header eksik'], 400);
		}

		$calculatedSignature = hash_hmac('sha256', $payload, $webhookSecret);
		if (!hash_equals($calculatedSignature, $signatureHeader)) {
			$this->db->insert('logs', [
				'log_controller' => 'payment',
				'log_message'    => 'Hatalı İmza Girişimi: ' . $signatureHeader,
				'log_error_code' => 'chk_sig_error',
			]);
			return json(['status' => 'error', 'message' => 'Geçersiz imza doğrulanamadı'], 401);
		}

		// Checkout.com imza doğrulandığında "200 OK" bekler, response kodunu set edelim

		// 4. Veri Ayrıştırma
		$eventType   = $data['type'] ?? '';
		$paymentData = $data['data'] ?? [];
		$reference   = $paymentData['reference'] ?? '';

		if (empty($reference)) {
			return json(['status' => 'error', 'message' => 'Eksik reference değeri'], 400);
		}

		$orderID = $reference;
		$status  = 'İşlenmedi';

		// Siparişin veritabanındaki varlığını kontrol et
		$orders = $this->db->where('order_payment_token', $orderID)->get('orders')->result();
		$order = $orders[0] ?? null;
		if (!$order) {
			return json(['status' => 'error', 'message' => 'Sipariş bulunamadı'], 404);
		}
		$incomingAmount = isset($paymentData['amount']) ? ((float)$paymentData['amount'] / 100) : 0.0;
		$expectedAmount = round(array_sum(array_map(function ($row) {
			return (float)($row->order_pay_price ?? $row->order_sale_price ?? 0);
		}, $orders)), 2);
		if ($incomingAmount <= 0 || abs($incomingAmount - $expectedAmount) > 0.01) {
			log_message('error', 'Checkout webhook amount mismatch for reference ' . $orderID);
			return json(['status' => 'error', 'message' => 'Ödeme tutarı doğrulanamadı.'], 400);
		}
		$eventId = trim((string)($data['id'] ?? $data['event_id'] ?? ''));
		if ($eventId !== '' && $this->db->table_exists('payment_webhook_events')) {
			$inserted = $this->db->insert('payment_webhook_events', [
				'provider' => 'checkout',
				'event_id' => $eventId,
				'reference' => $orderID,
				'created_at' => date('Y-m-d H:i:s'),
			]);
			if (!$inserted && $this->db->error()['code'] === 1062) {
				return json(['status' => 'success', 'message' => 'Webhook daha önce işlendi.']);
			}
		}

		// 5. Event Tiplerine Göre Süreç Yönetimi
		if ($eventType === 'payment_approved') {
			$status = 'Başarılı';

			// Kart ve POS detaylarını içeren metadata nesnesi
			$paymentInfo = [
				'cardHolderName' => $paymentData['source']['name'] ?? '',
				'cardBrand'      => $paymentData['source']['scheme'] ?? '',
				'cardType'       => $paymentData['source']['card_type'] ?? '',
				'binNumber'      => $paymentData['source']['bin'] ?? '',
				'lastFourDigits' => $paymentData['source']['last_4'] ?? '',
				'pos'            => $paymentData['processing'] ?? [],
				'isThreeDS'      => !empty($paymentData['3ds']['enrolled']) && ($paymentData['3ds']['authentication_response'] ?? '') === 'Y',
				'paymentID'      => $paymentData['id'] ?? '',
			];

			$paymentInfoJson = json_encode($paymentInfo);

			// Checkout API cent/kuruş cinsinden gönderir (Örn: 30$ için 3000 gelir). Onu birime çeviriyoruz.
			$orderStatus = 0; // Standart başarılı sipariş durumu

			// Siparişi başarıya çek
			$this->db->where('order_payment_token', $orderID)->update('orders', [
				'order_status'       => $orderStatus,
				'order_payment_info' => $paymentInfoJson,
			]);

			// GA4 Event Tetikleme
			$this->send_ga4_purchase_event(
				$orderID,
				$paymentData['response_summary'] ?? 'Approved',
				$order->order_sale_price ?? $incomingAmount
			);
		} elseif ($eventType === 'payment_declined') {
			$status = 'Başarısız';

			// Siparişi başarısız durumuna çek
			$this->db->where('order_payment_token', $orderID)->update('orders', [
				'order_status' => 12
			]);
		}

		// 6. Nihai Sonuç Logu
		$this->db->insert('logs', [
			'log_controller' => 'payment',
			'log_message'    => json_encode([
				'eventType' => $eventType,
				'orderID'   => $orderID,
				'status'    => $status
			]),
			'log_error_code' => 'chk0002',
		]);

		return json([
			'status'  => 'success',
			'message' => "{$orderID} numaralı Sipariş {$status} olarak tamamlandı."
		]);
	}

}
