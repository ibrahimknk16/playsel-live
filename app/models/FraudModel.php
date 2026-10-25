<?php defined('BASEPATH') or exit('No direct script access allowed');

class FraudModel extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Tüm ödeme yöntemlerinin ortak girişi: true dönerse sipariş fraud incelemesine (13) alınmalıdır.
     * Kontrol hata verirse akış bozulmaz; güvenli tarafta kalıp incelemeye alır ve logs'a yazar.
     */
    public function needsReview($order_id)
    {
        $previousDbDebug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            $result = $this->fraudCheck((int)$order_id);
            $dbError = $this->db->error();
            if (!empty($dbError['code'])) {
                throw new RuntimeException('DB error: ' . json_encode($dbError, JSON_UNESCAPED_UNICODE));
            }
            return empty($result['status']);
        } catch (Throwable $e) {
            $this->db->insert('logs', [
                'log_controller' => 'fraudCheck',
                'log_message' => json_encode(['order_id' => (int)$order_id, 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'log_error_code' => 'fraud_check_failed',
                'log_order_id' => (int)$order_id,
            ]);
            return true;
        } finally {
            $this->db->db_debug = $previousDbDebug;
        }
    }

    /**
     * Sipariş için Şablon Kural Setlerine Bağlı, Kategori İzoleli Fraud Kontrolü Yapar
     *
     * @param int $order_id
     * @return array Fraud sonucu (status: boolean, message: string)
     */
    public function fraudCheck($order_id)
    {
        // 1. Aşama: Sipariş, Ürün, Kategori ve Fraud Kural Şablonunu getir.
        $this->db->select('
        o.order_id, 
        o.order_reseller_id, 
        o.order_sale_price, 
        o.order_product_id, 
        p.product_category_id, 
        fr.fraud_rule_id, 
        fr.fraud_rule_daily_order_limit, 
        fr.fraud_rule_single_amount_limit, 
        fr.fraud_rule_daily_total_limit');
        
        $this->db->from('orders o');
        $this->db->join('products p', 'p.product_id = o.order_product_id', 'inner');
        $this->db->join('categories c', 'c.category_id = p.product_category_id', 'inner');
        // Sadece aktif (1) olan kural setini dahil et
        $this->db->join('fraud_rules fr', 'fr.fraud_rule_id = c.category_fraud_rule_id AND fr.fraud_rule_status = 1', 'left');
        $this->db->where('o.order_id', (int)$order_id);

        $order_data = $this->db->get()->row();

        // Sipariş yoksa
        if (!$order_data) {
            return ['status' => false, 'message' => 'Sipariş bulunamadı.'];
        }

        // Bu kategoriye atanmış aktif bir fraud şablonu yoksa, direkt onay ver.
        if (empty($order_data->fraud_rule_id)) {
            return ['status' => true, 'message' => 'Kategoriye ait fraud kuralı yok, onaylandı.'];
        }

        // --- KONTROL 1: Tekil Sipariş Tutarı Limiti ---
        if ($order_data->fraud_rule_single_amount_limit > 0 && $order_data->order_sale_price > $order_data->fraud_rule_single_amount_limit) {
            return ['status' => false, 'message' => 'Tekil sipariş tutarı limiti aşıldı.'];
        }

        // --- KONTROL 2 ve 3 İçin SADECE BU KATEGORİYE AİT Geçmişi Çek ---
        if ($order_data->fraud_rule_daily_order_limit > 0 || $order_data->fraud_rule_daily_total_limit > 0) {

            $time_threshold = date('Y-m-d H:i:s', strtotime('-24 hours'));

            $this->db->select('COUNT(o.order_id) as total_orders, SUM(o.order_sale_price) as total_amount');
            $this->db->from('orders o');
            $this->db->join('products p', 'p.product_id = o.order_product_id', 'inner');

            $this->db->where('o.order_reseller_id', $order_data->order_reseller_id);

            // Sadece bu siparişin ait olduğu kategori içindeki geçmiş işlemleri baz alıyoruz.
            $this->db->where('p.product_category_id', $order_data->product_category_id);

            $this->db->where('o.order_created_at >=', $time_threshold);

            // İptal edilenler limite dahil edilmesin. Başarılı/Bekleyen sipariş statülerini açmalısın.
            // Pending, approved and already reviewed orders consume the
            // velocity limit; failed/cancelled orders do not.
            $this->db->where_in('o.order_status', [0, 1, 11, 13, 14, 15]);

            $history = $this->db->get()->row();

			//pre($history);die;

            $total_orders = (int)$history->total_orders;
            $total_amount = (float)$history->total_amount;

            // --- KONTROL 2: Günlük (24 Saatlik) Adet Limiti (Bu Kategori İçin) ---
            if ($order_data->fraud_rule_daily_order_limit > 0 && $total_orders >= $order_data->fraud_rule_daily_order_limit) {
                return ['status' => false, 'message' => 'Bu kategori için günlük maksimum sipariş adedi limiti aşıldı.'];
            }

            // --- KONTROL 3: Günlük (24 Saatlik) Toplam Tutar Limiti (Bu Kategori İçin) ---
            if ($order_data->fraud_rule_daily_total_limit > 0 && $total_amount >= $order_data->fraud_rule_daily_total_limit) {
                return ['status' => false, 'message' => 'Bu kategori için günlük toplam sipariş tutarı limiti aşıldı.'];
            }
        }

        return ['status' => true, 'message' => 'Temiz'];
    }
}
