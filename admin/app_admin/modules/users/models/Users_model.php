<?php defined('BASEPATH') or exit('No direct script access allowed');
class Users_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	private function applyOrderFilters($userID, $order_id, $product_id, $category, $status, $statusArray, $searchUserIDorServer, $startDate = '', $endDate = '', $statusGroup = '')
	{
		$groups = ['success' => [1], 'failed' => [2, 5, 12], 'pending' => [0, 3, 4, 11, 13, 14]];

		$this->db->join("products", "products.product_id = o.order_product_id", "left");
		$this->db->join("categories", "categories.category_id = products.product_category_id", "left");
		$this->db->where('o.order_reseller_id', (int)$userID);
		$this->db->where_in("o.order_status", $statusArray);
		if (!empty($order_id)) {
			$this->db->where("o.order_id", (int)$order_id);
		}
		if (!empty($product_id)) {
			$this->db->where("o.order_product_id", (int)$product_id);
		}
		if (!empty($category)) {
			$this->db->where("products.product_category_id", (int)$category);
		}
		if ($status !== "" && $status !== null) {
			$this->db->where("o.order_status", (int)$status);
		} elseif (isset($groups[$statusGroup])) {
			$this->db->where_in("o.order_status", $groups[$statusGroup]);
		}
		$sd = DateTime::createFromFormat('Y-m-d', (string)$startDate);
		if ($sd && $sd->format('Y-m-d') === $startDate) {
			$this->db->where("o.order_created_at >=", $startDate . ' 00:00:00');
		}
		$ed = DateTime::createFromFormat('Y-m-d', (string)$endDate);
		if ($ed && $ed->format('Y-m-d') === $endDate) {
			$this->db->where("o.order_created_at <", date('Y-m-d', strtotime($endDate . ' +1 day')) . ' 00:00:00');
		}
		if (!empty($searchUserIDorServer)) {
			$this->db->group_start();
			$this->db->where("JSON_UNQUOTE(JSON_EXTRACT(o.order_require_data, '$.user_id')) =", $searchUserIDorServer);
			$this->db->or_where("JSON_UNQUOTE(JSON_EXTRACT(o.order_require_data, '$.server_id')) =", $searchUserIDorServer);
			$this->db->group_end();
		}
	}

	public function get_orders($userID, $start, $length, $order_id, $product_id, $category, $status, $statusArray, $searchUserIDorServer, $startDate = '', $endDate = '', $statusGroup = '')
	{
		$this->db->select("
        o.order_id,
        o.order_product_id,
        o.order_cost,
        o.order_cost_currency,
        o.order_cost_tl,
		o.order_sale_price,
		o.order_sale_price_currency,
        o.order_quantity,
        o.order_reseller_order_id,
        o.order_reseller_order_detail_id,
		o.order_site_id,
		o.order_created_at,
        o.order_status,
		o.order_require_data,
		o.order_supplier_order_id,
        products.product_name,
        products.product_image,
        categories.category_title
    	");
		$this->db->from("orders as o");
		$this->applyOrderFilters($userID, $order_id, $product_id, $category, $status, $statusArray, $searchUserIDorServer, $startDate, $endDate, $statusGroup);
		$this->db->order_by("o.order_id", "DESC");
		$this->db->limit((int)$length, (int)$start);
		return $this->db->get()->result();
	}

	public function get_total_orders($userID, $order_id, $product_id, $category, $status, $statusArray, $searchUserIDorServer, $startDate = '', $endDate = '', $statusGroup = '')
	{
		$this->db->select("
			COUNT(o.order_id) as total_orders,
			SUM(CASE WHEN o.order_status = 1 THEN o.order_cost ELSE 0 END) as total_cost,
			SUM(CASE WHEN o.order_status = 1 THEN o.order_sale_price ELSE 0 END) as total_sale_price
		");
		$this->db->from("orders as o");
		$this->applyOrderFilters($userID, $order_id, $product_id, $category, $status, $statusArray, $searchUserIDorServer, $startDate, $endDate, $statusGroup);
		return $this->db->get()->row();
	}

	// Müşterinin tüm siparişleri için filtresiz özet (kartlar için)
	public function get_order_stats($userID)
	{
		return $this->db->select("
			COUNT(order_id) AS total,
			COALESCE(SUM(order_status = 1), 0) AS ok,
			COALESCE(SUM(order_status IN (2, 5, 12)), 0) AS failed,
			COALESCE(SUM(CASE WHEN order_status = 1 THEN order_sale_price ELSE 0 END), 0) AS amount
		", false)->where('order_reseller_id', (int)$userID)->get('orders')->row();
	}

	    public function update_user_details($userID, array $update)
    {
        $userID = (int)$userID;
        if ($userID <= 0) return false;
        if (empty($update)) return false;

        $allowed = [
            'user_name',
            'user_surname',
            'user_email',
            'user_phone',
            'user_country',
            'user_lang',
            'user_type',
            'user_tier',
			'user_credit_limit',
            'user_email_is_verified',
            'user_phone_is_verified',
            'user_notes',
            'user_2fa_method',
            'user_status',
            'user_birthday',
        ];

        $data = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $update)) {
                $data[$key] = $update[$key];
            }
        }

        if (empty($data)) return false;

        $this->db->where('user_id', $userID);
        $ok = $this->db->update('users', $data);

        if (!$ok) {
            // DB hatasını logla (çok işe yarar)
            $err = $this->db->error();
            log_message('error', 'Users_model::update_user_details DB error: ' . ($err['message'] ?? 'unknown'));
            return false;
        }

        // affected_rows 0 ise: değişiklik yok ama query başarılı.
        // Controller'da "değişiklik yok"u hata sayıyorsun. İstersen true döndür.
        return ($this->db->affected_rows() >= 0);
    }
}
