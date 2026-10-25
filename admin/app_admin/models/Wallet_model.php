<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wallet_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Kullanıcının güncel net bakiyelerini hesaplar.
     * Matematik: (1:Onaylananlar) - (3:Harcananlar)
     * * @param int $user_id
     * @return array
     */
    public function get_user_balances($user_id)
    {
        $user_id = (int)$user_id;

        $this->db->select('
            (SUM(CASE WHEN user_balance_type = 1 AND user_balance_status = 1 THEN user_balance_amount ELSE 0 END) -
             SUM(CASE WHEN user_balance_type = 1 AND user_balance_status = 3 THEN user_balance_amount ELSE 0 END)) AS withdrawable_balance,
            
            (SUM(CASE WHEN user_balance_type = 2 AND user_balance_status = 1 THEN user_balance_amount ELSE 0 END) -
             SUM(CASE WHEN user_balance_type = 2 AND user_balance_status = 3 THEN user_balance_amount ELSE 0 END)) AS spendable_balance
        ', FALSE);

        $this->db->where('user_balance_user_id', $user_id);
        $query = $this->db->get('user_balance');
        $row = $query->row();

        return [
            'withdrawable_balance' => (float)($row->withdrawable_balance ?? 0),
            'spendable_balance'    => (float)($row->spendable_balance ?? 0),
            'total_balance'        => (float)(($row->withdrawable_balance ?? 0) + ($row->spendable_balance ?? 0))
        ];
    }

    /**
     * Öncelikli harcama işlemi.
     * @param int $user_id
     * @param int $order_id
     * @param float $total_amount
     * @param string $order_note
     * @return array
     */
    public function process_payment($user_id, $order_id, $total_amount, $order_note = 'Sipariş Ödemesi')
    {
        $user_id      = (int)$user_id;
        $order_id     = (int)$order_id;
        $total_amount = (float)$total_amount;

        if ($total_amount <= 0) {
            return ['status' => false, 'message' => 'Geçersiz ödeme tutarı.'];
        }

        $this->db->trans_begin();

        $user_query = $this->db->query(
            'SELECT id FROM users WHERE id = ? FOR UPDATE',
            [$user_id]
        );

        if ($user_query->num_rows() === 0) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Kullanıcı bulunamadı.'];
        }

        $already_spent = $this->db
            ->select('COALESCE(SUM(user_balance_amount), 0) AS amount', false)
            ->where('user_balance_user_id', $user_id)
            ->where('user_balance_order_id', $order_id)
            ->where('user_balance_status', 3)
            ->get('user_balance')
            ->row();
        if ((float)($already_spent->amount ?? 0) >= $total_amount) {
            $this->db->trans_commit();
            return ['status' => true, 'message' => 'Ödeme daha önce işlendi.'];
        }

        $balances = $this->get_user_balances($user_id);
        $total_available = $balances['spendable_balance'] + $balances['withdrawable_balance'];

        if ($total_available < $total_amount) {
            $this->db->trans_rollback();
            log_message('info', "process_payment Red: Yetersiz bakiye. User: {$user_id}, Order: {$order_id}, İstenen: {$total_amount}, Mevcut: {$total_available}");
            return ['status' => false, 'message' => 'Yetersiz bakiye.'];
        }

        $remaining_amount = $total_amount;
        $insert_data = [];
        $current_time = date('Y-m-d H:i:s');

        if ($balances['spendable_balance'] > 0) {
            $spend = min($balances['spendable_balance'], $remaining_amount);

            $insert_data[] = [
                'user_balance_user_id'   => $user_id,
                'user_balance_type'      => 2,
                'user_balance_amount'    => (float)$spend,
                'user_balance_status'    => 3,
                'user_balance_order_id'  => $order_id,
                'user_balance_note'      => $order_note . ' (Harcanabilir)',
                'user_balance_created_at' => $current_time,
                'user_balance_admin_id' => $this->current_admin_id()
            ];

            $remaining_amount -= $spend;
        }

        if ($remaining_amount > 0 && $balances['withdrawable_balance'] >= $remaining_amount) {
            $insert_data[] = [
                'user_balance_user_id'   => $user_id,
                'user_balance_type'      => 1,
                'user_balance_amount'    => (float)$remaining_amount,
                'user_balance_status'    => 3,
                'user_balance_order_id'  => $order_id,
                'user_balance_note'      => $order_note . ' (Çekilebilir)',
                'user_balance_created_at' => $current_time,
                'user_balance_admin_id' => $this->current_admin_id()
            ];

            $remaining_amount = 0;
        }

        if ($remaining_amount > 0) {
            $this->db->trans_rollback();
            log_message('error', "process_payment Kritik Hata: Kalan tutar sıfırlanamadı (Matematiksel kaçak). User: {$user_id}, Order: {$order_id}");
            return ['status' => false, 'message' => 'Sistem hatası oluştu. (Log: Bakiye eşleşmezliği)'];
        }

        if (!empty($insert_data)) {
            $this->db->insert_batch('user_balance', $insert_data);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            log_message('error', "process_payment DB Hatası: Transaction başarılamadı. User: {$user_id}, Order: {$order_id}");
            return ['status' => false, 'message' => 'Veritabanı hatası oluştu. İşlem geri alındı.'];
        }

        $this->db->trans_commit();
        return ['status' => true, 'message' => 'Ödeme başarıyla alındı.'];
    }

    /**
     * Kredi limiti (eksi bakiye) destekli manuel bakiye düşümü / silme.
     * @param int $user_id
     * @param float $amounts
     * @param int $type
     * @param string $note
     * @return array
     */
    public function delete_balance($user_id, $amount, $type, $note)
    {
        $user_id = (int)$user_id;
        $amount  = (float)$amount;
        $type    = (int)$type;

        if ($amount <= 0) {
            return ['status' => false, 'message' => 'Düşülecek tutar sıfırdan büyük olmalıdır.'];
        }

        if (!in_array($type, [1, 2])) {
            return ['status' => false, 'message' => 'Geçersiz bakiye tipi.'];
        }

        $this->db->trans_begin();

        // Aynı kullanıcıya eşzamanlı bakiye düşümlerini sıraya almak için satır kilidi
        $user_query = $this->db->query(
            'SELECT user_id, user_credit_limit FROM users WHERE user_id = ? FOR UPDATE',
            [$user_id]
        );
        $user = $user_query ? $user_query->row() : null;

        if (!$user) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Kullanıcı bulunamadı.'];
        }

        $credit_limit = (float)($user->user_credit_limit ?? 0);

        $balances = $this->get_user_balances($user_id);
        $current_net_balance = ($type === 1) ? $balances['withdrawable_balance'] : $balances['spendable_balance'];

        $total_allowance = $current_net_balance + $credit_limit;

        if ($total_allowance < $amount) {
            $this->db->trans_rollback();
            log_message('info', "delete_balance Red: Kredi limiti aşıldı. User: {$user_id}, İstenen: {$amount}, İzin Verilen: {$total_allowance}");
            return [
                'status'  => false,
                'message' => 'Kredi limiti aşıldı. Mevcut: ' . number_format($current_net_balance, 2) . ' TL, Limit: ' . number_format($credit_limit, 2) . ' TL'
            ];
        }

        $insert_data = [
            'user_balance_user_id'   => $user_id,
            'user_balance_type'      => $type,
            'user_balance_amount'    => $amount,
            'user_balance_status'    => 3,
            'user_balance_note'      => $note,
            'user_balance_created_at' => date('Y-m-d H:i:s'),
            'user_balance_admin_id' => $this->current_admin_id()
        ];

        $this->db->insert('user_balance', $insert_data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            log_message('error', "delete_balance DB Hatası: Transaction başarılamadı. User: {$user_id}, Amount: {$amount}");
            return ['status' => false, 'message' => 'Veritabanı hatası oluştu, işlem geri alındı.'];
        }

        $this->db->trans_commit();
        return ['status' => true, 'message' => 'Bakiye başarıyla hesaptan düşüldü.'];
    }

    /**
     * Bakiye Ekleme
     * @param int $user_id
     * @param float $amount
     * @param int $type
     * @param string $reference_no
     * @param string $note
     * @return array
     */
    public function add_balance($user_id, $amount, $type, $reference_no = '', $note = '')
    {
        $user_id = (int)$user_id;
        $amount  = (float)$amount;
        $type    = (int)$type;

        if ($amount <= 0) {
            return ['status' => false, 'message' => 'Yüklenecek tutar sıfırdan büyük olmalıdır.'];
        }

        if (!in_array($type, [1, 2])) {
            return ['status' => false, 'message' => 'Geçersiz bakiye tipi.'];
        }

        $data = [
            'user_balance_user_id'       => $user_id,
            'user_balance_type'          => $type,
            'user_balance_amount'        => $amount,
            'user_balance_status'        => 1, // 1: Onay
            'user_balance_reference_no'  => $reference_no,
            'user_balance_note'          => $note,
            'user_balance_created_at'    => date('Y-m-d H:i:s'),
            'user_balance_payment_date'  => date('Y-m-d H:i:s'),
            'user_balance_admin_id'     => $this->current_admin_id()
        ];

        if ($this->db->insert('user_balance', $data)) {
            return ['status' => true, 'message' => 'Bakiye başarıyla eklendi.'];
        } else {
            log_message('error', "add_balance DB Hatası: Insert edilemedi. User: {$user_id}, Amount: {$amount}");
            return ['status' => false, 'message' => 'Veritabanı kayıt hatası.'];
        }
    }

    private function current_admin_id()
    {
        $admin = $this->session->userdata('admin');
        return is_array($admin) && !empty($admin['admin_id']) ? (int)$admin['admin_id'] : null;
    }
}
