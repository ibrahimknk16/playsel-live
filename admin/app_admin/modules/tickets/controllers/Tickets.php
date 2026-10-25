<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tickets extends MY_Controller
{
	public $data = array();

	public function __construct()
	{
		userCheck();
		parent::__construct();
	}

	public function index()
	{
		$data = $this->data;

		$this->db->select('t.*, u.user_name, u.user_email');
		$this->db->from('tickets t');
		$this->db->join('users u', 'u.user_id = t.tickets_user_id', 'left');
		$this->db->order_by('t.tickets_updated_at', 'DESC');
		$data['tickets'] = $this->db->get()->result();
		$data['cancellation_reasons'] = [];
		if ($this->db->table_exists('order_cancellation_reasons')) {
			$data['cancellation_reasons'] = $this->db->order_by('reason_sort', 'ASC')->get('order_cancellation_reasons')->result();
		}

		$data['title'] = (isset($data['title']) ? $data['title'] . ' - ' : '') . 'Destek Talepleri';

		$this->template->content->view('tickets/index', $data);
		$this->template->publish();
	}

	public function detail($id)
	{
		$data = $this->data;

		$this->db->select('t.*, u.user_name, u.user_email');
		$this->db->from('tickets t');
		$this->db->join('users u', 'u.user_id = t.tickets_user_id', 'left');
		$this->db->where('t.tickets_id', $id);
		$ticket = $this->db->get()->row();

		if (!$ticket) {
			redirect(base_url('tickets'));
		}

		$this->db->where('ticket_messages_ticket_id', $id);
		$this->db->order_by('ticket_messages_created_at', 'ASC');
		$data['messages'] = $this->db->get('ticket_messages')->result();

		$data['ticket'] = $ticket;
		$data['cancellation_reasons'] = [];
		if ($this->db->table_exists('order_cancellation_reasons')) {
			$data['cancellation_reasons'] = $this->db
				->where('reason_status', 1)
				->order_by('reason_sort', 'ASC')
				->get('order_cancellation_reasons')
				->result();
		}
		$data['title'] = (isset($data['title']) ? $data['title'] . ' - ' : '') . 'Talep Detayı #' . $ticket->tickets_id;

		$this->template->content->view('tickets/detail', $data);
		$this->template->publish();
	}

	private function _validateAttachment($attachment)
	{
		if (empty($attachment) || !is_string($attachment)) {
			return null;
		}
		$attachment = trim($attachment);
		if (preg_match('#^data:image/(png|jpeg|jpg|webp|gif);base64,([A-Za-z0-9+/=]+)$#i', $attachment)) {
			if (strlen($attachment) <= 7 * 1024 * 1024) {
				return $attachment;
			}
		}
		return null;
	}

	public function reply()
	{
		$ticket_id = $this->input->post('ticket_id', TRUE);
		$message = $this->input->post('message', TRUE);
		$attachment = $this->_validateAttachment($this->input->post('attachment', FALSE));
		$admin_id = (int) ($this->session->userdata('admin')['admin_id'] ?? 0);

		if (!$message && !$attachment) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Lütfen bir mesaj yazın.']));
		}

		$ticket = $this->db->get_where('tickets', ['tickets_id' => $ticket_id])->row();
		if (!$ticket || $ticket->tickets_status == 2) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Geçersiz talep veya kapatılmış.']));
		}

		$admin_id = (int)($this->session->userdata('admin')['admin_id'] ?? 0);

		$this->db->trans_start();
		
		$messageData = [
			'ticket_messages_ticket_id' => $ticket_id,
			'ticket_messages_user_id' => 0,
			'ticket_messages_admin_id' => $admin_id, 
			'ticket_messages_message' => $message,
			'ticket_messages_attachment' => $attachment,
			'ticket_messages_created_at' => date('Y-m-d H:i:s')
		];
		$this->db->insert('ticket_messages', $messageData);

		$this->db->where('tickets_id', $ticket_id);
		$this->db->update('tickets', [
			'tickets_status' => 1,
			'tickets_updated_at' => date('Y-m-d H:i:s')
		]);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Hata oluştu.']));
		} else {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 'success', 'message' => 'Cevabınız başarıyla gönderildi!']));
		}
	}

	public function createCancellationReason()
		{
			if (!$this->db->table_exists('order_cancellation_reasons')) {
				return $this->ticketJson('error', 'Önce veritabanı migration çalıştırılmalıdır.');
			}

			$reason = trim((string)$this->input->post('reason', true));
			if ($reason === '') {
				return $this->ticketJson('error', 'İptal sebebi boş bırakılamaz.');
			}

			$this->db->insert('order_cancellation_reasons', [
				'reason_text' => $reason,
				'reason_sort' => 0,
				'reason_status' => 1,
				'reason_created_at' => date('Y-m-d H:i:s'),
			]);

			return $this->ticketJson(
				$this->db->affected_rows() > 0 ? 'success' : 'error',
				$this->db->affected_rows() > 0 ? 'İptal sebebi eklendi.' : 'İptal sebebi eklenemedi.'
			);
		}

	public function cancelOrderAndRefund()
		{
			$ticketId = (int)$this->input->post('ticket_id');
			$reason = trim((string)$this->input->post('reason', true));
			$manualReason = trim((string)$this->input->post('manual_reason', true));

			$ticket = $this->db->get_where('tickets', ['tickets_id' => $ticketId])->row();
			if (!$ticket || empty($ticket->tickets_order_id)) {
				return $this->ticketJson('error', 'Bu destek talebi bir siparişe bağlı değil.');
			}

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
			if ($reason === '') {
				return $this->ticketJson('error', 'İptal sebebi seçin veya manuel sebep yazın.');
			}

			$order = $this->db->get_where('orders', ['order_id' => (int)$ticket->tickets_order_id])->row();
			if (!$order) {
				return $this->ticketJson('error', 'Sipariş bulunamadı.');
			}
			if ((int)$order->order_status === 2) {
				return $this->ticketJson('error', 'Sipariş zaten iptal edilmiş.');
			}

			$orderData = json_decode((string)$order->order_data, true);
			$orderData = is_array($orderData) ? $orderData : [];
			$snapshot = $orderData['pricing_snapshot'] ?? [];
			$refundAmount = (float)($snapshot['final_sale_price_tl'] ?? $order->order_sale_price_tl ?? 0);
			if ($refundAmount <= 0) {
				return $this->ticketJson('error', 'İade tutarı hesaplanamadı; işlem yapılmadı.');
			}
			$orderData['cancellation_reason'] = $reason;
			$adminId = (int)($this->session->userdata('admin')['admin_id'] ?? 0);

			$this->db->trans_begin();
			// Eşzamanlı işlem ve ödeme durumu değişimine karşı satır kilitlenir; iade kararı kilitli durumdan verilir
			$locked = $this->db->query('SELECT order_status FROM orders WHERE order_id = ? FOR UPDATE', [(int)$order->order_id])->row();
			if (!$locked || (int)$locked->order_status === 2) {
				$this->db->trans_rollback();
				return $this->ticketJson('error', 'Sipariş zaten iptal edilmiş.');
			}
			// 11: Ödeme Bekleniyor, 12: Ödeme Başarısız -> tahsilat yok, iade edilemez
			$isPaid = !in_array((int)$locked->order_status, [11, 12], true);
			$this->db->where('order_id', (int)$order->order_id)
				->update('orders', [
					'order_status' => 2,
					'order_data' => json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				]);

			$refundExists = $this->db->where('user_balance_order_id', (int)$order->order_id)
				->where('user_balance_status', 1)
				->where('user_balance_note', 'Sipariş iadesi')
				->count_all_results('user_balance') > 0;
			// Kalem bazlı daha önce iade edilen tutar tekrar iade edilmez
			$alreadyRefunded = (float)($this->db->select_sum('refund_amount', 'total')
				->where('refund_order_id', (int)$order->order_id)
				->get('order_refund_items')->row()->total ?? 0);
			$refundAmount = round($refundAmount - $alreadyRefunded, 2);
			if ($isPaid && !$refundExists && $refundAmount > 0) {
				$this->db->insert('user_balance', [
					'user_balance_user_id' => (int)$order->order_reseller_id,
					'user_balance_type' => 2,
					'user_balance_amount' => $refundAmount,
					'user_balance_status' => 1,
					'user_balance_order_id' => (int)$order->order_id,
					'user_balance_note' => 'Sipariş iadesi',
					'user_balance_admin_id' => $adminId,
					'user_balance_created_at' => date('Y-m-d H:i:s'),
					'user_balance_payment_date' => date('Y-m-d H:i:s'),
				]);
			}
			$this->db->where('tickets_id', $ticketId)->update('tickets', [
				'tickets_status' => 2,
				'tickets_updated_at' => date('Y-m-d H:i:s'),
			]);
			$this->db->trans_complete();

			if (!$this->db->trans_status()) {
				return $this->ticketJson('error', 'İptal/iade işlemi başarısız oldu.');
			}
			return $this->ticketJson(
				'success',
				$isPaid ? 'Sipariş iptal edildi ve tutar kullanıcı bakiyesine iade edildi.' : 'Ödemesi alınmamış sipariş iptal edildi; tahsilat olmadığı için bakiye iadesi yapılmadı.'
			);
		}

	public function getRefundItems()
	{
		$ticketId = (int)$this->input->get_post('ticket_id');
		$ticket = $this->db->get_where('tickets', ['tickets_id' => $ticketId])->row();
		$order = $ticket ? $this->db->get_where('orders', ['order_id' => (int)$ticket->tickets_order_id])->row() : null;
		if (!$order) {
			return $this->ticketJson('error', 'Sipariş bulunamadı.');
		}
		$quantity = max(1, (int)$order->order_quantity);
		$vouchers = json_decode((string)$order->order_voucher, true);
		$vouchers = is_array($vouchers) ? array_values($vouchers) : [];
		$count = max($quantity, count($vouchers));
		$total = (float)($order->order_sale_price_tl ?: $order->order_sale_price ?: 0);
		$unit = round($total / $count, 2);
		$items = [];
		for ($i = 0; $i < $count; $i++) {
			$amount = $i === $count - 1 ? round($total - ($unit * ($count - 1)), 2) : $unit;
			$refunded = $this->db->where('refund_order_id', (int)$order->order_id)
				->where('refund_item_key', (string)$i)->count_all_results('order_refund_items') > 0;
			$items[] = ['key' => (string)$i, 'label' => $vouchers[$i] ?? ('Kalem #' . ($i + 1)), 'amount' => $amount, 'refunded' => $refunded];
		}
		return $this->output->set_content_type('application/json')
			->set_output(json_encode(['status' => 'success', 'items' => $items]));
	}

	public function refundSelectedItems()
	{
		$ticketId = (int)$this->input->post('ticket_id');
		$keys = $this->input->post('item_keys');
		$keys = is_array($keys) ? array_values(array_unique(array_map('strval', $keys))) : [];
		$ticket = $this->db->get_where('tickets', ['tickets_id' => $ticketId])->row();
		$order = $ticket ? $this->db->get_where('orders', ['order_id' => (int)$ticket->tickets_order_id])->row() : null;
		if (!$order || !$keys) {
			return $this->ticketJson('error', 'Sipariş ve en az bir kalem seçilmelidir.');
		}
		$quantity = max(1, (int)$order->order_quantity);
		$vouchers = json_decode((string)$order->order_voucher, true);
		$count = max($quantity, is_array($vouchers) ? count($vouchers) : 0);
		$total = (float)($order->order_sale_price_tl ?: $order->order_sale_price ?: 0);
		$unit = round($total / $count, 2);
		$adminId = (int)($this->session->userdata('admin')['admin_id'] ?? 0);
		$this->db->trans_begin();
		$locked = $this->db->query('SELECT order_status FROM orders WHERE order_id = ? FOR UPDATE', [(int)$order->order_id])->row();
		if (!$locked || (int)$locked->order_status === 2 || in_array((int)$locked->order_status, [11, 12], true)) {
			$this->db->trans_rollback();
			return $this->ticketJson('error', 'Sipariş iptal edilmiş veya ödemesi alınmamış; kalem iadesi yapılamaz.');
		}
		foreach ($keys as $key) {
			$index = (int)$key;
			if ($index < 0 || $index >= $count || $this->db->query(
				'SELECT refund_item_id FROM order_refund_items WHERE refund_order_id = ? AND refund_item_key = ? FOR UPDATE',
				[(int)$order->order_id, $key]
			)->row()) {
				$this->db->trans_rollback();
				return $this->ticketJson('error', 'Geçersiz veya daha önce iade edilmiş kalem seçildi.');
			}
			$amount = $index === $count - 1 ? round($total - ($unit * ($count - 1)), 2) : $unit;
			$this->db->insert('user_balance', [
				'user_balance_user_id' => (int)$order->order_reseller_id,
				'user_balance_type' => 2,
				'user_balance_amount' => $amount,
				'user_balance_status' => 1,
				'user_balance_order_id' => (int)$order->order_id,
				'user_balance_note' => 'Sipariş kalemi iadesi #' . ($index + 1),
				'user_balance_admin_id' => $adminId,
				'user_balance_created_at' => date('Y-m-d H:i:s'),
				'user_balance_payment_date' => date('Y-m-d H:i:s'),
			]);
			$this->db->insert('order_refund_items', [
				'refund_order_id' => (int)$order->order_id,
				'refund_item_key' => $key,
				'refund_amount' => $amount,
				'refund_user_balance_id' => (int)$this->db->insert_id(),
				'refund_created_by' => $adminId,
			]);
		}
		$refundedCount = (int)$this->db->where('refund_order_id', (int)$order->order_id)->count_all_results('order_refund_items');
		if ($refundedCount >= $count) {
			$orderData = json_decode((string)$order->order_data, true);
			$orderData = is_array($orderData) ? $orderData : [];
			$orderData['cancellation_reason'] = $orderData['cancellation_reason'] ?? 'Destek talebi üzerinden tüm kalemler iade edildi';
			$this->db->where('order_id', (int)$order->order_id)->update('orders', [
				'order_status' => 2,
				'order_data' => json_encode($orderData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			]);
		}
		$this->db->where('tickets_id', $ticketId)->update('tickets', [
			'tickets_status' => 2,
			'tickets_updated_at' => date('Y-m-d H:i:s'),
		]);
		$this->db->trans_complete();
		return $this->ticketJson($this->db->trans_status() ? 'success' : 'error', 'Seçilen kalemler iade edildi.');
	}

	private function ticketJson($status, $message)
		{
			return $this->output->set_content_type('application/json')->set_output(json_encode([
				'status' => $status,
				'message' => $message,
			], JSON_UNESCAPED_UNICODE));
		}

	public function updateStatus()
	{
		$ticket_id = $this->input->post('ticket_id', TRUE);
		$status = $this->input->post('status', TRUE); // e.g. 2 for close

		$this->db->where('tickets_id', $ticket_id);
		$this->db->update('tickets', [
			'tickets_status' => $status,
			'tickets_updated_at' => date('Y-m-d H:i:s')
		]);

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(['status' => 'success', 'message' => 'Talep durumu güncellendi.']));
	}
}
