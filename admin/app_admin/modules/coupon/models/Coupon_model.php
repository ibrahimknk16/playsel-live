<?php defined('BASEPATH') or exit('No direct script access allowed');

class Coupon_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Kupon kodunun geçerliliğini doğrular ve sepete uygulanacak net indirim tutarını hesaplar.
     * * @param string $coupon_code Kontrol edilecek kupon kodu
     * @param int $user_id İşlemi yapan kullanıcı ID'si
     * @param array $cart_items Sepetteki ürünler dizisi. Format: [['product_id' => 1, 'category_id' => 2, 'price' => 100.00, 'qty' => 2], ...]
     * @return array ['status' => bool, 'message' => string, 'discount' => float, 'coupon_id' => int]
     */
    public function validate_and_calculate($coupon_code, $user_id, $cart_items)
    {
        if (empty($coupon_code) || empty($user_id) || empty($cart_items)) {
            return ['status' => false, 'message' => 'Eksik parametre gönderildi.', 'discount' => 0.00, 'coupon_id' => 0];
        }

        $current_time = date('Y-m-d H:i:s');

        // 1. Kuponu ana tablodan çekiyoruz (Sadece coupon_status = 1 olan yani aktif kuponlar gelebilir, silinmiş eller elenir)
        $coupon = $this->db->select('*')
            ->from('coupons')
            ->where('coupon_code', $coupon_code)
            ->where('coupon_status', 1)
            ->where('coupon_start_date <=', $current_time)
            ->where('coupon_end_date >=', $current_time)
            ->get()
            ->row();

        if (!$coupon) {
            return ['status' => false, 'message' => 'Geçersiz veya süresi dolmuş kupon kodu.', 'discount' => 0.00, 'coupon_id' => 0];
        }

        $coupon_id = $coupon->coupon_id;

        // 2. Genel Toplam Kullanım Limiti Kontrolü (Sadece '1' yani teslim edilenleri sayıyoruz)
        if ($coupon->coupon_total_limit > 0) {
            $total_usages = $this->db->where('coupon_usage_coupon_id', $coupon_id)
                ->where('coupon_usage_status', 1)
                ->count_all_results('coupon_usages');

            if ($total_usages >= $coupon->coupon_total_limit) {
                return ['status' => false, 'message' => 'Bu kuponun toplam kullanım limiti dolmuştur.', 'discount' => 0.00, 'coupon_id' => $coupon_id];
            }
        }

        // 3. Kullanıcı Bazlı Kullanım Limiti Kontrolü (Sadece '1' yani teslim edilenleri sayıyoruz)
        if ($coupon->coupon_user_limit > 0) {
            $user_usages = $this->db->where('coupon_usage_coupon_id', $coupon_id)
                ->where('coupon_usage_user_id', $user_id)
                ->where('coupon_usage_status', 1)
                ->count_all_results('coupon_usages');

            if ($user_usages >= $coupon->coupon_user_limit) {
                return ['status' => false, 'message' => 'Bu kuponu maksimum kullanım sınırınıza ulaştınız.', 'discount' => 0.00, 'coupon_id' => $coupon_id];
            }
        }

        // 4. Kategori ve Sepet Hesaplama Döngüsü
        $valid_categories = !empty($coupon->coupon_categories) ? explode(',', $coupon->coupon_categories) : [];

        $total_cart_amount = 0.00;
        $eligible_items_total = 0.00;
        $eligible_items_count = 0;

        foreach ($cart_items as $item) {
            $item_total = $item['price'] * $item['qty'];
            $total_cart_amount += $item_total;

            if (!empty($valid_categories) && !in_array($item['category_id'], $valid_categories)) {
                continue;
            }

            $eligible_items_total += $item_total;
            $eligible_items_count += $item['qty'];
        }

        // 5. Minimum Sipariş Tutarı Kontrolü
        if ($total_cart_amount < $coupon->coupon_min_order_total) {
            return [
                'status' => false,
                'message' => 'Bu kuponu kullanabilmek için minimum sipariş tutarı: ' . number_format($coupon->coupon_min_order_total, 2, ',', '.') . ' TL olmalıdır.',
                'discount' => 0.00,
                'coupon_id' => $coupon_id
            ];
        }

        if ($eligible_items_count === 0) {
            return ['status' => false, 'message' => 'Sepetinizdeki ürünler bu kuponun geçerli olduğu kategorilerle eşleşmiyor.', 'discount' => 0.00, 'coupon_id' => $coupon_id];
        }

        // 6. İndirim Tutarı Hesaplama Kurgusu
        $calculated_discount = 0.00;

        if ($coupon->coupon_type == 1) {
            if ($coupon->coupon_max_product_qty > 0 && $eligible_items_count > $coupon->coupon_max_product_qty) {
                $average_item_price = $eligible_items_total / $eligible_items_count;
                $discount_pool = $average_item_price * $coupon->coupon_max_product_qty;
                $calculated_discount = $discount_pool * ($coupon->coupon_value / 100);
            } else {
                $calculated_discount = $eligible_items_total * ($coupon->coupon_value / 100);
            }
        } else if ($coupon->coupon_type == 2) {
            $calculated_discount = $coupon->coupon_value;
        }

        // 7. Maksimum İndirim Tutarı Üst Sınır Kontrolü
        if ($coupon->coupon_max_discount > 0 && $calculated_discount > $coupon->coupon_max_discount) {
            $calculated_discount = $coupon->coupon_max_discount;
        }

        if ($calculated_discount > $total_cart_amount) {
            $calculated_discount = $total_cart_amount;
        }

        return [
            'status' => true,
            'message' => 'Kupon başarıyla uygulandı.',
            'discount' => round($calculated_discount, 2),
            'coupon_id' => $coupon_id
        ];
    }

    /**
     * Sipariş geçilirken kupon kullanım logunu '0' (Beklemede) statüsünde kaydeder.
     */
    public function apply_coupon_usage($coupon_id, $user_id, $order_id, $discount_amount)
    {
        $data = [
            'coupon_usage_coupon_id'       => $coupon_id,
            'coupon_usage_user_id'         => $user_id,
            'coupon_usage_order_id'        => $order_id,
            'coupon_usage_discount_amount' => $discount_amount,
            'coupon_usage_status'          => 0
        ];

        return $this->db->insert('coupon_usages', $data);
    }

    /* =========================================================================
     * ADMIN CRUD METOTLARI (STATUS = 2 VE UNIQUE PROVİZYON ENTEGRELİ)
     * ========================================================================= */

    /**
     * Silinmemiş (Statüsü 2 olmayan) tüm kuponları listeler
     */
    public function get_all_coupons()
    {
        return $this->db->select('*')
            ->from('coupons')
            ->where_in('coupon_status', [0, 1]) // Sadece Pasif (0) ve Aktif (1) olanlar gelsin, silinmişler (2) elensin
            ->order_by('coupon_id', 'DESC')
            ->get()
            ->result();
    }

    /**
     * ID bazlı tekil kupon getirir (Silinmemiş olması şartıyla)
     */
    public function get_coupon($coupon_id)
    {
        return $this->db->select('*')
            ->from('coupons')
            ->where('coupon_id', $coupon_id)
            ->where_in('coupon_status', [0, 1])
            ->get()
            ->row();
    }

    /**
     * Aktif veya pasif kuponlar arasında kodun benzersizliğini kontrol eder.
     * Silinmiş olanları (Status 2) hesaba katmaz, böylece aynı kod yeniden açılabilir.
     * * @param string $coupon_code Kontrol edilecek ham kod
     * @param int $exclude_id Düzenleme esnasında çakışma olmaması için hariç tutulacak ID
     * @return bool Eğer kod kullanılabilir ise TRUE, çakışma varsa FALSE döner
     */
    public function check_coupon_code_unique($coupon_code, $exclude_id = 0)
    {
        $this->db->select('coupon_id')
            ->from('coupons')
            ->where('coupon_code', $coupon_code)
            ->where_in('coupon_status', [0, 1]);

        if ($exclude_id > 0) {
            $this->db->where('coupon_id !=', $exclude_id);
        }

        $query = $this->db->get();
        return $query->num_rows() === 0;
    }

    /**
     * Yeni kupon ekler
     */
    public function insert_coupon($data)
    {
        return $this->db->insert('coupons', $data);
    }

    /**
     * Kupon günceller
     */
    public function update_coupon($coupon_id, $data)
    {
        return $this->db->where('coupon_id', $coupon_id)
            ->where_in('coupon_status', [0, 1])
            ->update('coupons', $data);
    }

    /**
     * SOFT DELETE: Kuponu silerken veritabanındaki UNIQUE KEY çakışmasını engellemek için
     * mevcut kodu '__del__[timestamp]' formatına günceller ve boşa çıkarır.
     * Standart CI3 transaction blokları arasında çalıştırılması kararlılığı artırır.
     */
    public function delete_coupon($coupon_id)
    {
        if (empty($coupon_id)) {
            return false;
        }

        // Önce silinecek kuponun mevcut kodunu çekiyoruz
        $coupon = $this->db->select('coupon_code')
            ->from('coupons')
            ->where('coupon_id', $coupon_id)
            ->get()
            ->row();

        if (!$coupon) {
            return false;
        }

        // Aynı kodun tekrar açılabilmesi için veritabanındaki kodu bozup serbest bırakıyoruz
        $released_code = $coupon->coupon_code . '__del__' . time();

        $this->db->trans_start();

        $this->db->where('coupon_id', $coupon_id);
        $this->db->update('coupons', [
            'coupon_code'   => $released_code,
            'coupon_status' => 2 // Silinmiş statüsü
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
}