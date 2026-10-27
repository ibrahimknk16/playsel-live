<?php defined('BASEPATH') or exit('No direct script access allowed');
class Orders_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_orders($start, $length, $order_id, $product_id, $category, $status, $resellerOrderID, $startDate, $endDate, $reseller, $supplier, $statusArray, $searchUserIDorServer, $siteID)
	{
		$filters = $this->build_filters($order_id, $product_id, $category, $status, $resellerOrderID, $startDate, $endDate, $reseller, $supplier, $statusArray, $searchUserIDorServer, $siteID);
		$orderIds = $this->get_filtered_order_ids($start, $length, $filters);

		if (empty($orderIds)) {
			return [];
		}

		return $this->get_order_details_by_ids($orderIds);
	}

	private function get_filtered_order_ids($start, $length, $filters)
	{
		$this->db->select('o.order_id');
		$this->db->from('orders as o');

		if (!empty($filters['category'])) {
			$this->db->join('products', 'products.product_id = o.order_product_id', 'inner');
		}

		$this->apply_order_filters($filters);
		$this->db->order_by('o.order_created_at', 'DESC');
		$this->db->limit((int)$length, (int)$start);

		$rows = $this->db->get()->result_array();
		if (empty($rows)) {
			return [];
		}
		

		return array_map('intval', array_column($rows, 'order_id'));
	}

	private function get_order_details_by_ids(array $orderIds)
	{
		$this->db->select("
			o.order_id,
			o.order_product_id,
			o.order_cost,
			o.order_reseller_id,
			o.order_supplier_id,
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
			o.order_operator_admin,
			products.product_name,
			products.product_image,
			categories.category_title,
			CONCAT_WS(' ', NULLIF(TRIM(u.user_name), ''), NULLIF(TRIM(u.user_surname), '')) as reseller_name,
			uu.user_name as supplier_name
		");
		$this->db->from('orders as o');
		$this->db->join('products', 'products.product_id = o.order_product_id', 'left');
		$this->db->join('categories', 'categories.category_id = products.product_category_id', 'left');
		$this->db->join('users as u', 'u.user_id = o.order_reseller_id', 'left');
		$this->db->join('users as uu', 'uu.user_id = o.order_supplier_id', 'left');
		$this->db->where_in('o.order_id', $orderIds);

		$orderIds = array_values(array_unique(array_map('intval', $orderIds)));
		$this->db->order_by('FIELD(o.order_id,' . implode(',', $orderIds) . ')', '', false);

		return $this->db->get()->result();
	}

	public function get_total_orders($order_id, $product_id, $category, $status, $resellerOrderID, $startDate, $endDate, $reseller, $supplier, $statusArray, $searchUserIDorServer, $siteID)
	{
		$filters = $this->build_filters($order_id, $product_id, $category, $status, $resellerOrderID, $startDate, $endDate, $reseller, $supplier, $statusArray, $searchUserIDorServer, $siteID);

		$this->db->select("
			COUNT(o.order_id) as total_orders
		");
		$this->db->from("orders as o");

		if (!empty($filters['category'])) {
			$this->db->join("products", "products.product_id = o.order_product_id", "inner");
		}

		$this->apply_order_filters($filters);

		$query = $this->db->get();
		return $query->row(); // Tek satır döndüğünden `result()` yerine `row()` kullanıldı
	}

	private function build_filters($order_id, $product_id, $category, $status, $resellerOrderID, $startDate, $endDate, $reseller, $supplier, $statusArray, $searchUserIDorServer, $siteID)
	{
		return [
			'order_id' => (int)$order_id,
			'product_id' => (int)$product_id,
			'category' => (int)$category,
			'status' => $status,
			'resellerOrderID' => trim((string)$resellerOrderID),
			'startDate' => $startDate,
			'endDate' => $endDate,
			'reseller' => (int)$reseller,
			'supplier' => (int)$supplier,
			'statusArray' => is_array($statusArray) ? $statusArray : [],
			'searchUserIDorServer' => trim((string)$searchUserIDorServer),
			'siteID' => (int)$siteID,
		];
	}

	private function apply_order_filters($filters)
	{
		if (!empty($filters['statusArray'])) {
			$this->db->where_in("o.order_status", $filters['statusArray']);
		}

		if (!empty($filters['order_id'])) {
			$this->db->where("o.order_id", $filters['order_id']);
		}

		if (!empty($filters['product_id'])) {
			$this->db->where("o.order_product_id", $filters['product_id']);
		}

		if (!empty($filters['category'])) {
			$this->db->where("products.product_category_id", $filters['category']);
		}

		if ($filters['status'] !== "" && $filters['status'] !== null) {
			$this->db->where("o.order_status", (int)$filters['status']);
		}

		if (!empty($filters['resellerOrderID'])) {
			$this->db->group_start();
			$this->db->where("o.order_reseller_order_id", $filters['resellerOrderID']);
			$this->db->or_where("o.order_reseller_order_detail_id", $filters['resellerOrderID']);
			$this->db->group_end();
		}

		if (!empty($filters['siteID'])) {
			$this->db->where("o.order_site_id", $filters['siteID']);
		}

		if (!empty($filters['startDate'])) {
			$this->db->where("o.order_created_at >=", $filters['startDate'] . " 00:00:00");
		}

		if (!empty($filters['endDate'])) {
			$this->db->where("o.order_created_at <=", $filters['endDate'] . " 23:59:59");
		}

		if (!empty($filters['reseller'])) {
			$this->db->where("o.order_reseller_id", $filters['reseller']);
		}

		if (!empty($filters['supplier'])) {
			$this->db->where("o.order_supplier_id", $filters['supplier']);
		}

		if (!empty($filters['searchUserIDorServer'])) {
			$this->db->group_start();
			$this->db->where("JSON_UNQUOTE(JSON_EXTRACT(o.order_require_data, '$.user_id')) =", $filters['searchUserIDorServer']);
			$this->db->or_where("JSON_UNQUOTE(JSON_EXTRACT(o.order_require_data, '$.server_id')) =", $filters['searchUserIDorServer']);
			$this->db->group_end();
		}
	}
}
