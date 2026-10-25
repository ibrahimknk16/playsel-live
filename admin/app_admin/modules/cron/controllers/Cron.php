<?php defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$data = $this->data;
		$testJson = $this->testJson();
		pre($testJson);
		die;

		if (!empty($testJson->data)) {
			$insertData = [];
			foreach ($testJson->data as $orderDetail) {
				$insertData = [
					'orderDetailID' => $orderDetail->orderDetailID,
					'orderID' => $orderDetail->orderID,
					'productID' => $orderDetail->productID,
					'externalProductID' => $orderDetail->externalProductID,
					'customerStoreProductID' => $orderDetail->customerStoreProductID,
					'quantity' => $orderDetail->quantity,
					'productCategoryID' => $orderDetail->productCategoryID,
					'externalProductCategoryID' => $orderDetail->externalProductCategoryID,
					'listPrice' => $orderDetail->costPrice,
					'salePrice' => $orderDetail->salePrice,
					'requireData' => json_encode($orderDetail->requireData),
					'status' => 0,
				];
				$insert = $this->db->insert('orders', $insertData);
				$insertID = $this->db->insert_id();
				$insertOrderDetail = [];
				if (!empty($orderDetail->externalProductIDs)) {
					foreach ($orderDetail->externalProductIDs as $key => $item) {
						$insertOrderDetail = [
							'order_id' => $insertID,
							'product_id' => $key,
							'order_item_status' => 0,
						];
						$this->db->insert('order_items', $insertOrderDetail);
					}
				} else {
					$insertOrderDetail = [
						'order_id' => $insertID,
						'product_id' => $orderDetail->externalProductID,
						'order_item_status' => 0,
					];
					$this->db->insert('order_items', $insertOrderDetail);
				}
			}
		}
	}

	public function listenHyperOrders()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://dijipinapi.dijipin.com/order/list');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "{}");
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = 'Apikey: eec7d5323507b09dcbbb77daf68e5257'; //4efe7778dfc3ab7d2b8f2d5c93b9070d
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
		echo $result;
		$result = json_decode($result);
		pre($result);
	}

	public function getAllCategories()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://dijipinapi.dijipin.com/Categories');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = 'Apikey: 4efe7778dfc3ab7d2b8f2d5c93b9070d';
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
		$result = json_decode($result);
		pre($result);
	}

	public function testJson($jsonNumber = 1)
	{
		$json = '{"data":[{"orderDetailID":1903876,"orderID":1756240,"productID":711,"deliveryDetail":null,"externalProductID":1720,"customerStoreProductID":1720,"externalProductIDs":{"60":1,"600":1},"externalProductIDsData":{"60":{"ProductID":265,"ProductName":"Top-Up PubG Mobile 60 UC - TR","HyperProductID":60,"ProductCategoryID":165,"CustomerStoreProductID":60},"600":{"ProductID":22351,"ProductName":"660 UC Unipin 310 TL","HyperProductID":600,"ProductCategoryID":228,"CustomerStoreProductID":22351}},"quantity":1,"productMainImage":"https://assets.hyperteknoloji.com/cdn/dijipin/product/doc720_1ca96e9f4116a939f78443633274ca41.png","productName":"Top-Up PubG Mobile 720 UC - TR","productSlug":"top-up-pubg-mobile-720-uc-tr","productCategoryID":165,"externalProductCategoryID":165,"marketPrice":341.00,"salePrice":324.99,"costPrice":340.99,"requireData":[{"productRequireID":1,"identifier":"user_id","title":"Oyuncu ID\'nizi Yazınız","value":"52162559068"}],"createDate":"2025-02-13T13:48:36"},{"orderDetailID":1903879,"orderID":1756243,"productID":266,"deliveryDetail":null,"externalProductID":300,"customerStoreProductID":300,"externalProductIDs":null,"externalProductIDsData":null,"quantity":1,"productMainImage":"https://assets.hyperteknoloji.com/cdn/dijipin/product/doc325_4a5250b3097827ffb8df84a21226186f.png","productName":"Top-Up PubG Mobile 325 UC - TR","productSlug":"top-up-pubg-mobile-325-uc-tr","productCategoryID":165,"externalProductCategoryID":165,"marketPrice":155.00,"salePrice":149.99,"costPrice":154.99,"requireData":[{"productRequireID":1,"identifier":"user_id","title":"Oyuncu ID\'nizi Yazınız","value":"5512131684"}],"createDate":"2025-02-13T13:50:44"},{"orderDetailID":1903880,"orderID":1756244,"productID":22156,"deliveryDetail":null,"externalProductID":1325,"customerStoreProductID":22156,"externalProductIDs":null,"externalProductIDsData":null,"quantity":1,"productMainImage":"https://assets.hyperteknoloji.com/cdn/dijipin/product/doc325_16f0ba26694ef02dcf63eccea068a3f0.png","productName":"Top-Up PubG Mobile 325 UC- Global","productSlug":"top-up-pubg-mobile-325-uc-global","productCategoryID":208,"externalProductCategoryID":208,"marketPrice":199.99,"salePrice":147.46,"costPrice":199.99,"requireData":[{"productRequireID":1,"identifier":null,"title":"Oyuncu ID\'nizi Yazınız","value":"51312910223"}],"createDate":"2025-02-13T13:50:56"},{"orderDetailID":1903882,"orderID":1756246,"productID":22351,"deliveryDetail":null,"externalProductID":600,"customerStoreProductID":22351,"externalProductIDs":null,"externalProductIDsData":null,"quantity":1,"productMainImage":"https://assets.hyperteknoloji.com/cdn/dijipin/product/doc660_a75829a1fc3f1aac36ffa316507e4b71.png","productName":"660 UC Unipin 310 TL","productSlug":"660-uc-unipin-310-tl","productCategoryID":228,"externalProductCategoryID":228,"marketPrice":350.00,"salePrice":267.30,"costPrice":259.99,"requireData":[{"productRequireID":1,"identifier":"user_id","title":"Oyuncu ID\'nizi Yazınız","value":"5321603508"}],"createDate":"2025-02-13T13:51:46"}],"success":true,"message":"success","errorCode":null}';
		$json2 = '{"data":[{"orderDetailID":1903973,"orderID":1756331,"productID":265,"deliveryDetail":null,"externalProductID":60,"customerStoreProductID":60,"externalProductIDs":null,"externalProductIDsData":null,"quantity":1,"productMainImage":"https://assets.hyperteknoloji.com/cdn/dijipin/product/doc60_3a4e74026f2c426efe0c542c6bc24b01.png","productName":"Top-Up PubG Mobile 60 UC - TR","productSlug":"top-up-pubg-mobile-60-uc-tr","productCategoryID":165,"externalProductCategoryID":165,"marketPrice":31.00,"salePrice":26.57,"costPrice":30.99,"requireData":[{"productRequireID":1,"identifier":"user_id","title":"Oyuncu ID\'nizi Yazınız","value":"232323"}],"createDate":"2025-02-13T14:40:20"},{"orderDetailID":1903974,"orderID":1756331,"productID":265,"deliveryDetail":null,"externalProductID":60,"customerStoreProductID":60,"externalProductIDs":null,"externalProductIDsData":null,"quantity":1,"productMainImage":"https://assets.hyperteknoloji.com/cdn/dijipin/product/doc60_3a4e74026f2c426efe0c542c6bc24b01.png","productName":"Top-Up PubG Mobile 60 UC - TR","productSlug":"top-up-pubg-mobile-60-uc-tr","productCategoryID":165,"externalProductCategoryID":165,"marketPrice":31.00,"salePrice":26.57,"costPrice":30.99,"requireData":[{"productRequireID":1,"identifier":"user_id","title":"Oyuncu ID\'nizi Yazınız","value":"123123"}],"createDate":"2025-02-13T14:40:20"},{"orderDetailID":1903975,"orderID":1756331,"productID":22537,"deliveryDetail":null,"externalProductID":1,"customerStoreProductID":22537,"externalProductIDs":null,"externalProductIDsData":null,"quantity":1,"productMainImage":"https://assets.hyperteknoloji.com/cdn/dijipin/product/docageofdoruk-d%C4%B1j%C4%B1-99_60f5f0347f9fc93d8cdf6aade4714cfe.jpg","productName":"AoE 99 Doruk Parası (DP)","productSlug":"99-doruk-parasi","productCategoryID":367,"externalProductCategoryID":367,"marketPrice":41.99,"salePrice":31.20,"costPrice":39.99,"requireData":[{"productRequireID":1,"identifier":"","title":"ID Numaranız","value":"123123"},{"productRequireID":2,"identifier":"","title":"Server","value":"123"}],"createDate":"2025-02-13T14:40:20"},{"orderDetailID":1903976,"orderID":1756331,"productID":639,"deliveryDetail":null,"externalProductID":99,"customerStoreProductID":639,"externalProductIDs":null,"externalProductIDsData":null,"quantity":1,"productMainImage":"https://assets.hyperteknoloji.com/cdn/dijipin/product/doc99_f9563f9835408fbf8457f6d65dc32547.png","productName":"Whiteout Survival 99 Frost Star","productSlug":"whiteout-survival-99-frost-star","productCategoryID":253,"externalProductCategoryID":253,"marketPrice":41.99,"salePrice":38.14,"costPrice":29.99,"requireData":[{"productRequireID":1,"identifier":null,"title":"Lütfen Oyuncu Kimliğinizi Girin","value":"2323"}],"createDate":"2025-02-13T14:40:20"}],"success":true,"message":"success","errorCode":null}';
		return json_decode($json2);
	}

	public function getProductByID($id)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://dijipinapi.dijipin.com/Products/Detail/' . $id . '/1');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		$headers = array();
		$headers[] = 'Accept: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
		return json_decode($result);
	}

	public function getProductByCategoryID($categoryID)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://dijipinapi.dijipin.com/Products/List/' . $categoryID);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		$headers = array();
		$headers[] = 'Accept: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
		return json_decode($result);
	}

	public function productsInsert()
	{
		$productIDS = $this->db->select('productID')->from('orders')->get()->result();
		$productIDS = array_column($productIDS, 'productID');
		$productIDS = array_unique($productIDS);
		foreach ($productIDS as $pid) {
			$product = $this->getProductByID($pid);
			pre($product->data);
			continue;
			$product = $product->data;
			$checkProduct = $this->db->from('products')->where('productID', $product->productID)->get()->row();
			if (!empty($checkProduct)) {
				continue;
			}
			$insertData = [
				'productID' => $product->productID,
				'productName' => $product->productName,
				'brandID' => $product->brandID,
				'brandName' => $product->brandName,
				'productCategoryID' => $product->productCategoryID,
				'categoryName' => $product->categoryName,
				'productMainImage' => $product->productData->productMainImage,
				'productRequire' => json_encode($product->productRequire),
				'productCost' => 0,
			];
			$this->db->insert('products', $insertData);
		}
	}
}
