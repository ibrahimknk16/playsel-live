<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Coupon_model extends CI_Model
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
    public function validate_and_calculate($coupon_code, $user_id, $cart_items, $localeCode)
    {
        if (empty($coupon_code) || empty($user_id) || empty($cart_items)) {
            return ['status' => 'error', 'message' => 'Eksik parametre gönderildi.', 'discount' => 0.00, 'coupon_id' => 0];
        }

        $current_time = date('Y-m-d H:i:s');

        // 1. Kuponu ana tablodan çekiyoruz (Aktiflik ve Tarih kontrolü tek sorguda)
        $coupon = $this->db->select('*')
            ->from('coupons')
            ->where('coupon_code', $coupon_code)
            ->where('coupon_status', 1)
            ->where('coupon_start_date <=', $current_time)
            ->where('coupon_end_date >=', $current_time)
            ->group_start()
                ->where('UPPER(coupon_country)', strtoupper((string)$localeCode))
                ->or_where('coupon_country IS NULL', null, false)
                ->or_where('coupon_country', '')
            ->group_end()
            ->get()
            ->row();

        if (!$coupon) {
            return ['status' => 'error', 'message' => 'Geçersiz veya süresi dolmuş kupon kodu.', 'discount' => 0.00, 'coupon_id' => 0];
        }

        $coupon_id = $coupon->coupon_id;

        // 2. Genel Toplam Kullanım Limiti Kontrolü (teslim edilenler + devam eden siparişler)
        if ($coupon->coupon_total_limit > 0) {
            $total_usages = $this->count_active_usages($coupon_id);

            if ($total_usages >= $coupon->coupon_total_limit) {
                return ['status' => 'error', 'message' => 'Bu kuponun toplam kullanım limiti dolmuştur.', 'discount' => 0.00, 'coupon_id' => $coupon_id];
            }
        }

        // 3. Kullanıcı Bazlı Kullanım Limiti Kontrolü
        if ($coupon->coupon_user_limit > 0) {
            $user_usages = $this->count_active_usages($coupon_id, $user_id);

            if ($user_usages >= $coupon->coupon_user_limit) {
                return ['status' => 'error', 'message' => 'Bu kuponu maksimum kullanım sınırınıza ulaştınız.', 'discount' => 0.00, 'coupon_id' => $coupon_id];
            }
        }

        // 4. Kategori ve Sepet Hesaplama Döngüsü
        $valid_categories = !empty($coupon->coupon_categories) ? explode(',', $coupon->coupon_categories) : [];

        $total_cart_amount = 0.00;      // Kupon şartı olan min_order_total için toplam sepet tutarı
        $eligible_items_total = 0.00;   // Kuponun indirim uygulayabileceği (kategoriye uyan) ürünlerin toplam tutarı
        $eligible_items_count = 0;      // Kuponun indirim uygulayabileceği toplam adet

        foreach ($cart_items as $item) {
            $item_total = $item['price'] * $item['qty'];
            $total_cart_amount += $item_total;

            // Eğer kupon kategori kısıtlamalıysa ve ürün bu kategorilerde değilse pas geç
            if (!empty($valid_categories) && !in_array($item['category_id'], $valid_categories)) {
                continue;
            }

            // Kategori kuralına uyan ürünleri indirim havuzuna dahil et
            $eligible_items_total += $item_total;
            $eligible_items_count += $item['qty'];
        }

        // 5. Minimum Sipariş Tutarı Kontrolü
        if ($total_cart_amount < $coupon->coupon_min_order_total) {
            return [
                'status' => 'error',
                'message' => 'Bu kuponu kullanabilmek için minimum sipariş tutarı: ' . nFormat($coupon->coupon_min_order_total) . ' TL olmalıdır.',
                'discount' => 0.00,
                'coupon_id' => $coupon_id
            ];
        }

        // Kupon kategorisine uyan ürün bulunamadıysa patlat
        if ($eligible_items_count === 0) {
            return ['status' => 'error', 'message' => 'Sepetinizdeki ürünler bu kuponun geçerli olduğu kategorilerle eşleşmiyor.', 'discount' => 0.00, 'coupon_id' => $coupon_id];
        }

        // 6. İndirim Tutarı Hesaplama Kurgusu
        $calculated_discount = 0.00;

        if ($coupon->coupon_type == 1) {
            // Yüzdelik (%) İndirim Kuponu
            if ($coupon->coupon_max_product_qty > 0 && $eligible_items_count > $coupon->coupon_max_product_qty) {
                // Eğer geçerli olacağı ürün adedine sınır konulmuşsa (Örn: Maks 5 adet ürün için geçerli)
                // En avantajlı hesaplama için ortalama birim fiyat üzerinden adet kadar indirim uyguluyoruz
                $average_item_price = $eligible_items_total / $eligible_items_count;
                $discount_pool = $average_item_price * $coupon->coupon_max_product_qty;
                $calculated_discount = $discount_pool * ($coupon->coupon_value / 100);
            } else {
                // Sınır yoksa veya sepet adedi sınırdan küçükse direkt toplam tutara uygula
                $calculated_discount = $eligible_items_total * ($coupon->coupon_value / 100);
            }
        } else if ($coupon->coupon_type == 2) {
            // Sabit Tutar (TL) İndirim Kuponu
            $calculated_discount = $coupon->coupon_value;
        }

        // 7. Maksimum İndirim Tutarı Üst Sınır Kontrolü (coupon_max_discount)
        if ($coupon->coupon_max_discount > 0 && $calculated_discount > $coupon->coupon_max_discount) {
            $calculated_discount = $coupon->coupon_max_discount;
        }

        // İndirim tutarı sepet tutarını aşamaz (Aşarsa sepet tutarına eşitlenir)
        if ($calculated_discount > $total_cart_amount) {
            $calculated_discount = $total_cart_amount;
        }

        return [
            'status' => 'success',
            'message' => 'Kupon başarıyla uygulandı.',
            'discount' => round($calculated_discount, 2),
            'coupon_id' => $coupon_id,
            'coupon_code' => $coupon->coupon_code,
            'coupon_value' => $coupon->coupon_value,
            'coupon_type' => $coupon->coupon_type,
        ];
    }

    /**
     * Teslim edilmiş (1) ve ödeme/işlem bekleyen (son 1 saat, sipariş 0 veya 11) kullanımları sayar.
     */
    public function count_active_usages($coupon_id, $user_id = null)
    {
        $this->db->from('coupon_usages cu')
            ->join('orders o', 'o.order_id = cu.coupon_usage_order_id', 'left')
            ->where('cu.coupon_usage_coupon_id', (int)$coupon_id)
            ->group_start()
                ->where('cu.coupon_usage_status', 1)
                ->or_group_start()
                    ->where('cu.coupon_usage_status', 0)
                    ->where_in('o.order_status', [0, 11])
                    ->where('cu.coupon_usage_created_at >=', date('Y-m-d H:i:s', strtotime('-1 hour')))
                ->group_end()
            ->group_end();
        if ($user_id !== null) {
            $this->db->where('cu.coupon_usage_user_id', (int)$user_id);
        }
        return (int)$this->db->count_all_results();
    }

    /**
     * Sipariş geçilirken kupon kullanım logunu '0' (Beklemede) statüsünde kaydeder.
     * Standart CodeIgniter 3 Transaction'ı içerisinde çalıştırılması önerilir.
     */
    public function apply_coupon_usage($coupon_id, $user_id, $order_id, $discount_amount)
    {
        $data = [
            'coupon_usage_coupon_id'       => $coupon_id,
            'coupon_usage_user_id'         => $user_id,
            'coupon_usage_order_id'        => $order_id,
            'coupon_usage_discount_amount' => $discount_amount,
            'coupon_usage_status'          => 0 // Sipariş verildi ancak henüz teslim edilmedi (Beklemede)
        ];

        return $this->db->insert('coupon_usages', $data);
    }
}
