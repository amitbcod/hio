<?php

namespace App\Controllers;

use DbHomeFeature;
use DbProductFeature;
use DbProductReviewFeature;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

error_reporting(E_ERROR | E_PARSE);

class HomeListingController
{
	private $webshop_obj;
	private $product_obj;
	private $block_obj;
	private $shopcode;
	private $shopid;
	private $isEuropeShop = false;
	private $vat_percent_session = 0;
	private $customer_type_id = 0;
	private $specialPrices;
	private $config_products;
	private $config_product_ids;
	private $shopInventory;
	private $externalInventory;
	private $identifier;
	private $categoryid;

	public function __invoke(Request $request, Response $response, $args)
	{
	}

	public function newArrivals(Request $request, Response $response, $args)
	{
		$data = $request->getParsedBody();
		extract($data);
		$error = '';
		// print_R($data);die();
		$limit = $limit ?? '';
		if (isset($vat_percent_session) && $vat_percent_session !== '') {
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}


		$lang_code = $lang_code ?? '';

		$product_array = [];

		$this->webshop_obj = new DbHomeFeature();
		$this->product_obj = new DbProductFeature();
		$getNewArrivalList = $this->webshop_obj->getNewArrivalListNew();
		$getBestSelling = $this->webshop_obj->getBestSelling();

		if ($getNewArrivalList === false) {
			abort('New product not available');
		}
		
		$product_list  = $getNewArrivalList['data'];
		$product_ids   = array_column($product_list, 'id');


		$config_products = $this->product_obj->configurableProductForMultipleProducts($product_ids);
		
		$this->config_product_ids = array_column($config_products, 'id');

		$this->config_products = array_group($config_products, 'parent_id');

		$this->specialPrices = array_key_by(
			$this->product_obj->getSpecialPricesForMultipleProducts(array_merge($product_ids, $this->config_product_ids)),
			'product_id'
		);


		$this->shopInventory =
			array_key_by(
				$this->product_obj->getAvailableInventoryForMultipleProducts(array_merge($product_ids, $this->config_product_ids)),
				'product_id'
			);

		$product_array = [];
		foreach ($product_list as $value) {
			if ($value['product_type'] === 'simple') {
				// echo "h1";
				$value = $this->simpleProductStrategy($value);
			} elseif ($value['product_type'] === 'bundle') {
				// echo "h2";
				$value = $this->bundleProductStrategy($value);
			} else {
				// echo "h3";
				$value = $this->advancedProductStrategy($value);
			}
			$product_array[] = $value;
		}
		// echo "<pre>";
		// print_r($product_array);
		// die;

		if ($error != '') {
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			// exit(json_encode($message));
		} else {
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'New product available';
			$message['NewArrivalProduct'] = $product_array;
		}
		exit(json_encode($message));
	}

	public function newArrivals_page(Request $request, Response $response, $args)
	{
		$data = $request->getParsedBody();
		extract($data);
		$error = '';
		if (!isset($shopcode) || $shopcode === '') {
			abort('Please pass all the mandatory values');
		}

		$this->shopcode = $shopcode;
		$this->shopid = $shopid;
		$this->customer_type_id = $customer_type_id ?? 1;
		$customer_login_id = $customer_login_id ?? 0;

		$limit = $limit ?? '';
		$page = $page ?? '';
		if ($page > 0) {

			$page = ($page - 1) * $limit;
		}
		$options = (isset($options) ? $options : '');

		if (isset($vat_percent_session) && $vat_percent_session !== '') {
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}

		$lang_code = $lang_code ?? '';

		$product_array = [];

		$this->webshop_obj = new DbHomeFeature();
		$this->product_obj = new DbProductFeature();

		$getNewArrivalList_count = $this->webshop_obj->getNewArrivalList_product_count($this->shopcode, $this->shopid, $this->customer_type_id, '', $lang_code, 'new_arrivals', $options);
		// print_r($getNewArrivalList_count);exit;
		if ($getNewArrivalList_count != false) {
			$getNewArrivalList_count = $getNewArrivalList_count;
		} else {
			$getNewArrivalList_count = 0;
		}

		$getNewArrivalList = $this->webshop_obj->getNewArrivalList($this->shopcode, $this->shopid, $this->customer_type_id, $limit, '', $lang_code, 'new_arrivals', $page, $options);

		if ($getNewArrivalList === false) {
			abort('New product not available');
		}

		$product_ids = array_column($getNewArrivalList, 'id');
		$config_products = $this->product_obj->configurableProductForMultipleProducts($this->shopcode, $this->shopid, $product_ids);
		$this->config_product_ids = array_column($config_products, 'id');

		$this->config_products = array_group($config_products, 'parent_id');

		$this->specialPrices = array_key_by(
			$this->product_obj->getSpecialPricesForMultipleProducts($this->shopcode, array_merge($product_ids, $this->config_product_ids), $this->customer_type_id),
			'product_id'
		);


		$this->shopInventory =
			array_key_by(
				$this->product_obj->getAvailableInventoryForMultipleProducts(array_merge($product_ids, $this->config_product_ids), $this->shopcode),
				'product_id'
			);


		$external_products = array_reduce($config_products, function ($carry, $product) {
			if (!is_array($carry)) {
				$carry = [];
			}
			if ($product['product_inv_type'] === 'dropship' || $product['product_inv_type'] === 'virtual') {
				$carry[$product['shop_id']][] = $product['shop_product_id'];
			}
			return $carry;
		});
		//exit(json_encode($external_products));
		$this->externalInventory = [];
		if ($external_products) {
			foreach ($external_products as $shop_id => $shop_external_product_ids) {
				$this->externalInventory = array_merge(
					$this->externalInventory,
					$this->product_obj->getAvailableInventoryForMultipleProducts($shop_external_product_ids, 'shop' . $shop_id),
				);
			}
		}

		$this->externalInventory = array_key_by($this->externalInventory, 'product_id');


		$wishlistData = [];
		if (isset($customer_login_id) && $customer_login_id > 0) {
			$wishlistData = $this->product_obj->getWishlistCountForMultipleProducts($this->shopcode, $customer_login_id, $product_ids);
		}

		foreach ($getNewArrivalList as $value) {
			if (isset($customer_login_id) && $customer_login_id > 0 && in_array($value['id'], $wishlistData, true)) {
				$value['wishlist_status'] = 1;
			} else {
				$value['wishlist_status'] = 0;
			}
			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategy($value); // Not used in shop3? Did not refactor this function.
			} else {
				$value = $this->advancedProductStrategy($value);
			}
			$product_array[] = $value;
		}

		if (empty($product_array)) {
			if ($page > 1) {
				$message['statusCode'] = '200';
				$message['is_success'] = 'true';
				$message['message'] = 'No product!';
				$message['ProductList'] = $product_array;
				$message['ProductListCount'] = $getNewArrivalList_count;
				exit(json_encode($message));
			} else {
				$message['statusCode'] = '500';
				$message['is_success'] = 'false';
				$message['message'] = $error;
				exit(json_encode($message));
			}
		} else {
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'New product available';
			$message['ProductList'] = $product_array;
			$message['ProductListCount'] = $getNewArrivalList_count;
			exit(json_encode($message));
		}
	}

	public function featuredProducts(Request $request, Response $response, $args)
	{
		$data = $request->getParsedBody();
		extract($data);
		$error = '';
		if (empty($identifier)) {
			abort('Please pass all the mandatory values');
		}

		$this->identifier = $identifier;
		$limit = $limit ?? '';

		$product_array = [];
		$this->webshop_obj = new DbHomeFeature();
		$this->product_obj = new DbProductFeature();
		$this->block_obj = new DbProductReviewFeature();

		$ids = ''; // ✅ Initialize to avoid "undefined variable"

		if ($this->identifier != "recent_popular") {
			$productBlock = $this->block_obj->getProductsBlock($this->identifier);
			if ($productBlock === false) {
				abort('Data not available for the ' . $identifier . ' identifier');
			}

			$id_string = trim($productBlock['assigned_products'], ","); 
			$id_arr = explode(",", $id_string); 
			$ids = "'" . implode("','", $id_arr) . "'";  
		}

		$getNewArrivalList = $this->webshop_obj->getNewArrivalList(
			$limit,
			$ids,
			$lang_code = '',
			$flag_rr = '',
			$page = '',
			$options = 'recent_popular'
		);

		if ($getNewArrivalList === false) {
			abort('Featured product not available');
		}

		$product_list = $getNewArrivalList['data'];
		$product_ids = array_column($product_list, 'id');

		$config_products = $this->product_obj->configurableProductForMultipleProducts($product_ids);
		$this->config_product_ids = array_column($config_products, 'id');

		$this->config_products = array_group($config_products, 'parent_id');

		$this->specialPrices = array_key_by(
			$this->product_obj->getSpecialPricesForMultipleProducts(array_merge($product_ids, $this->config_product_ids)),
			'product_id'
		);

		$this->shopInventory = array_key_by(
			$this->product_obj->getAvailableInventoryForMultipleProducts(array_merge($product_ids, $this->config_product_ids)),
			'product_id'
		);

		$product_array = [];
		foreach ($product_list as $value) {
			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategy($value);
			} elseif ($value['product_type'] === 'bundle') {
				$value = $this->bundleProductStrategy($value);
			} else {
				$value = $this->advancedProductStrategy($value);
			}
			$product_array[] = $value;
		}

		if (!empty($error)) {
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
		} else {
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Product available';
			$message['productBlockList'] = $product_array;
		}

		exit(json_encode($message));
	}


	private function bundleProductStrategy($value): array
	{
		// echo "h4";die;
		if ($this->isEuropeShop) {
			$value['eu_webshop_price'] = calculate_price_with_vat($value['price'], $this->vat_percent_session);
		}

		$value['stock_status'] = 'Instock';
		$value['qty'] = 1;

		$specialPriceArr = false;
		
		return $value;
	}


	public function featuredProducts_page(Request $request, Response $response, $args)
	{
		$data = $request->getParsedBody();

		extract($data);
		$error = '';
		if (!isset($shopcode) || $shopcode === '' || $identifier == '') {
			abort('Please pass all the mandatory values');
		}
		// echo $shopcode;exit();
		$this->shopcode = $shopcode;
		$this->shopid = $shopid;
		$this->identifier = $identifier;
		$this->customer_type_id = $customer_type_id ?? 1;
		$customer_login_id = $customer_login_id ?? 0;
		$limit = $limit ?? '';
		$page = $page ?? '';
		if ($page > 0) {

			$page = ($page - 1) * $limit;
		}
		$options = (isset($options) ? $options : '');

		if (isset($vat_percent_session) && $vat_percent_session !== '') {
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}

		$lang_code = $lang_code ?? '';

		$product_array = [];


		$this->webshop_obj = new DbHomeFeature();
		$this->product_obj = new DbProductFeature();
		$this->block_obj = new DbProductReviewFeature();

		$productBlock = $this->block_obj->getProductsBlock($this->shopcode, $this->shopid, $this->identifier);

		if ($productBlock === false) {
			abort('Data not available for the ' . $identifier . ' identifier');
		}

		$id_string = trim($productBlock['assigned_products'], ","); //remove comma from start and end of the string.
		$id_arr = explode(",", $id_string); // convert string to array
		$ids = "'" . implode("','", $id_arr) . "'";  // Convert array to string with quote and comma.

		$getNewArrivalList_count = $this->webshop_obj->getNewArrivalList_product_count($this->shopcode, $this->shopid, $this->customer_type_id, $ids, $lang_code, '', $options);
		// print_r($getNewArrivalList_count);exit;
		if ($getNewArrivalList_count != false) {
			$getNewArrivalList_count = $getNewArrivalList_count;
		} else {
			$getNewArrivalList_count = 0;
		}

		$getNewArrivalList = $this->webshop_obj->getNewArrivalList($this->shopcode, $this->shopid, $this->customer_type_id, $limit, $ids, $lang_code, '', $page, $options);

		if ($getNewArrivalList === false) {
			abort('Featured product not available');
		}

		$product_ids = array_column($getNewArrivalList, 'id');
		$config_products = $this->product_obj->configurableProductForMultipleProducts($this->shopcode, $this->shopid, $product_ids);
		$this->config_product_ids = array_column($config_products, 'id');

		$this->config_products = array_group($config_products, 'parent_id');

		$this->specialPrices = array_key_by(
			$this->product_obj->getSpecialPricesForMultipleProducts($this->shopcode, array_merge($product_ids, $this->config_product_ids), $this->customer_type_id),
			'product_id'
		);



		$this->shopInventory =
			array_key_by(
				$this->product_obj->getAvailableInventoryForMultipleProducts(array_merge($product_ids, $this->config_product_ids), $this->shopcode),
				'product_id'
			);


		$external_products = array_reduce($config_products, function ($carry, $product) {
			if (!is_array($carry)) {
				$carry = [];
			}
			if ($product['product_inv_type'] === 'dropship' || $product['product_inv_type'] === 'virtual') {
				$carry[$product['shop_id']][] = $product['shop_product_id'];
			}
			return $carry;
		});
		//exit(json_encode($external_products));
		$this->externalInventory = [];
		if ($external_products) {
			foreach ($external_products as $shop_id => $shop_external_product_ids) {
				$this->externalInventory = array_merge(
					$this->externalInventory,
					$this->product_obj->getAvailableInventoryForMultipleProducts($shop_external_product_ids, 'shop' . $shop_id),
				);
			}
		}

		$this->externalInventory = array_key_by($this->externalInventory, 'product_id');


		$wishlistData = [];
		if (isset($customer_login_id) && $customer_login_id > 0) {
			$wishlistData = $this->product_obj->getWishlistCountForMultipleProducts($this->shopcode, $customer_login_id, $product_ids);
		}

		foreach ($getNewArrivalList as $value) {
			if (isset($customer_login_id) && $customer_login_id > 0 && in_array($value['id'], $wishlistData, true)) {
				$value['wishlist_status'] = 1;
			} else {
				$value['wishlist_status'] = 0;
			}
			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategy($value); // Not used in shop3? Did not refactor this function.
			} else {
				$value = $this->advancedProductStrategy($value);
			}
			$product_array[] = $value;
		}


		if (empty($product_array)) {
			if ($page > 1) {
				$message['statusCode'] = '200';
				$message['is_success'] = 'true';
				$message['message'] = 'No product!';
				$message['ProductList'] = $product_array;
				$message['ProductListCount'] = $getNewArrivalList_count;
				exit(json_encode($message));
			} else {
				$message['statusCode'] = '500';
				$message['is_success'] = 'false';
				$message['message'] = $error;
				exit(json_encode($message));
			}
		} else {
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'New product available';
			$message['ProductList'] = $product_array;
			$message['ProductListCount'] = $getNewArrivalList_count;
			exit(json_encode($message));
		}
	}

	private function advancedProductStrategy($value)
	{
		// Capture result array
		// echo "h4";die;

		$result = $this->advancedProductStrategyStandard($value);
		
		// Destructure safely
		[$value, $qty, $flag, $price_array] = $result;

		// Default prices
		$value['special_price']     = $value['special_price'] ?? null;
		$value['special_min_price'] = $value['special_min_price'] ?? null;
		$value['special_max_price'] = $value['special_max_price'] ?? null;
		$value['display_original']  = $value['display_original'] ?? null;

		// If flag true, process prices
		if ($flag === true && !empty($price_array)) {
			$prices           = array_column($price_array, 'different_price');
			$display_original = array_values(array_filter(array_column($price_array, 'display_original')));

			$value['special_price']     = min($prices);
			$value['special_min_price'] = min($prices);
			$value['special_max_price'] = max($prices);
			$value['display_original']  = $display_original[0] ?? null;
		}

		// Stock status
		if ($qty > 0) {
			$value['stock_status'] = 'Instock';
			$value['qty']          = $qty;
		} else {
			$value['stock_status'] = 'Out of stock';
			$value['qty']          = 0;
		}
		// print_r($result);
		// die;

		return $value;
	}

	private function simpleProductStrategy($value): array
	{
		if ($this->isEuropeShop) {
			$value['eu_webshop_price'] = calculate_price_with_vat($value['price'], $this->vat_percent_session);
		}

		if ($value['product_inv_type'] === 'buy') {
			$product_inv = $this->product_obj->getAvailableInventory($value['id'], $value);
			if ($product_inv['available_qty'] > 0) {
				$value['stock_status'] = 'Instock';
				$value['qty'] = $product_inv['available_qty'];
			}
		}
		$specialPriceArr = $this->specialPrices[$value['id']] ?? false;
		if ($specialPriceArr !== false) {
			$value['special_price'] = $specialPriceArr['special_price'];
			$value['special_price_from'] = $specialPriceArr['special_price_from'];
			$value['special_price_to'] = $specialPriceArr['special_price_to'];
			$value['display_original'] = $specialPriceArr['display_original'];
		}
		return $value;
	}

	private function advancedProductStrategyEurope($value): array
	{
		$eu_webshop_priceArray = [];
		$price_array = [];
		$flag = false;

		$configProduct = [$value['id']] ?? [];

		$qty = 0;
		if (count($configProduct) > 0) {
			foreach ($configProduct as $conf) {

				$eu_webshop_price = calculate_price_with_vat($conf['price'], $this->vat_percent_session);
				$eu_webshop_priceArray[] = $eu_webshop_price;

				$qty += $this->getAvailableQuantity($conf);

				$specialPriceArr = $this->specialPrices[$conf['id']] ?? false;

				if ($specialPriceArr !== false) {
					$flag = true;
					$arr['display_original'] = $specialPriceArr['display_original'];
					$arr['different_price'] = calculate_price_with_vat($specialPriceArr['special_price'], $this->vat_percent_session);
				} else {
					$arr['different_price'] = $eu_webshop_price;
					$arr['display_original'] = '';
				}

				$price_array[] = $arr;
			}

			$value['min_price'] = min($eu_webshop_priceArray);
			$value['max_price'] = max($eu_webshop_priceArray);
			$value['eu_webshop_price'] = min($eu_webshop_priceArray);
		}
		return [$value, $qty, $flag, $price_array];
	}

	private function advancedProductStrategyStandard($value): array
	{
		// echo "hiiiiiiiiiiiiiiii4";die;

		$flag = false;
		$price_array = [];
		// print_R($value['id']);die;
		if (isset($this->config_products[$value['id']])) {
    	// Case 1: current product is a parent (has children)
			$configProduct = $this->config_products[$value['id']];
		} elseif (!empty($value['parent_id']) && isset($this->config_products[$value['parent_id']])) {
			// Case 2: current product is a child → get its parent’s children
			$configProduct = $this->config_products[$value['parent_id']];
		} else {
			// Case 3: nothing found → prevent error
			$configProduct = [];
		}

		if (!empty($configProduct)) {
			$value['min_price'] = min(array_column($configProduct, 'webshop_price'));
			$value['max_price'] = max(array_column($configProduct, 'webshop_price'));
			$value['webshop_price'] = $value['min_price'];

			$qty = 0;
			$flag = false;
			$price_array = [];

			foreach ($configProduct as $conf) {
				$specialPriceArr = $this->specialPrices[$conf['id']] ?? [];
				$qty += $this->getAvailableQuantity($conf);

				if (!empty($specialPriceArr)) {
					$flag = true;
					$arr['display_original'] = $specialPriceArr['display_original'];
					$arr['different_price'] = $specialPriceArr['special_price'];
				} else {
					$arr['different_price'] = $conf['webshop_price'];
					$arr['display_original'] = '';
				}

				$price_array[] = $arr;
			}

			return [$value, $qty, $flag, $price_array];
		}
		// print_r($value);die;
		return [$value, 0, false, []]; // fallback when no configProduct

		
	}

	private function getAvailableQuantity($product)
	{
		return max(
			0,
			(int) ($this->shopInventory[$product['id']]['available_qty'] ?? 0)
		);


		// if ($product['product_inv_type'] === 'buy') {
		// 	return max(
		// 		0,
		// 		(int) ($this->shopInventory[$product['id']]['available_qty'] ?? 0)
		// 	);
		// }

		// if ($product['product_inv_type'] === 'virtual') {
		// 	return max(
		// 		0,
		// 		(int) ($this->externalInventory[$product['shop_product_id']]['available_qty'] ?? 0),
		// 		(int) ($this->shopInventory[$product['id']]['available_qty'] ?? 0)
		// 	);
		// }

		// if($product['product_inv_type'] === 'dropship') {
		// 	return max(
		// 		0,
		// 		(int) ($this->externalInventory[$product['shop_product_id']]['available_qty'] ?? 0)
		// 	);
		// }

		// return 0;
	}


	public function ProductWithGifts(Request $request, Response $response, $args)
	{
		$data = $request->getParsedBody();
		extract($data);
		$error = '';
		// print_R($data);die();
		// exit(json_encode($data));

		$limit = $limit ?? '';
		if (isset($vat_percent_session) && $vat_percent_session !== '') {
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}


		$lang_code = $lang_code ?? '';

		$product_array = [];

		$this->webshop_obj = new DbHomeFeature();
		$this->product_obj = new DbProductFeature();
		// $product_id =	'226,3961';
		// $product_id =	'226,189';
		$product_id =	'3961,251';



		$getNewArrivalList = $this->webshop_obj->getProductWithGiftsList($limit, $product_id, $lang_code, 'product_with_gifts');
		// echo "<pre>";
		// print_r($getNewArrivalList);
		// die;
		if ($getNewArrivalList === false) {
			abort('Products with gifts not available');
		}

		$product_ids = array_column($getNewArrivalList, 'parent_id');
		if (($key = array_search(0, $product_ids)) !== false) {
			unset($product_ids[$key]);
		}

		$config_products = $this->product_obj->configurableProductForMultipleProducts($product_ids);
		// echo "<pre>";
		// print_r($config_products);
		// die;
		$this->config_product_ids = array_column($config_products, 'id');

		$this->config_products = array_group($config_products, 'parent_id');

		$this->specialPrices = array_key_by(
			$this->webshop_obj->getSpecialPricesForMultipleProducts(array_merge($product_ids, $this->config_product_ids)),
			'product_id'
		);
		$new_data = ['226,3961'];
		// print_R($product_ids);die();
		$this->shopInventory =
			array_key_by(
				$this->product_obj->getAvailableInventoryForMultipleProducts($new_data),
				'product_id'
			);

		// $external_products = array_reduce($config_products, function ($carry, $product) {
		// 	if (!is_array($carry)) {
		// 		$carry = [];
		// 	}
		// 	/* if($product['product_inv_type'] === 'dropship' || $product['product_inv_type'] === 'virtual'){
		// 		$carry[$product['shop_id']][] = $product['shop_product_id'];
		// 	} */
		// 	return $carry;
		// });

		// //exit(json_encode($external_products));
		// $this->externalInventory = [];
		// if ($external_products) {
		// 	foreach ($external_products as $shop_id => $shop_external_product_ids) {
		// 		$this->externalInventory = array_merge(
		// 			$this->externalInventory,
		// 			$this->product_obj->getAvailableInventoryForMultipleProducts($shop_external_product_ids),
		// 		);
		// 	}
		// }

		// $this->externalInventory = array_key_by($this->externalInventory, 'product_id');

		// print_r($getNewArrivalList );die();
		foreach ($getNewArrivalList as $value) {
			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategy($value); // Not used in shop3? Did not refactor this function.
			} else if ($value['product_type'] === 'bundle') {
				$value = $this->bundleProductStrategy($value);
			} else if ($value['product_type'] === 'conf-simple') {
				$base_image = $this->product_obj->mediaGallery($value['parent_id']);
				$url_key = $this->product_obj->getproductDetailsById($value['parent_id']);
				$value['base_image'] = $base_image[0]['image'];
				$value['url_key'] = $url_key['url_key'];
				$value = $this->advancedProductStrategy($value);
			} else {
				$value = $this->advancedProductStrategy($value);
			}
			$product_array[] = $value;
		}

		if ($error != '') {
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		} else {
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'New product available';
			$message['ProductWithGifts'] = $product_array;
			exit(json_encode($message));
		}
	}


	public function hindi_magazines(Request $request, Response $response, $args)
	{
		$data = $request->getParsedBody();
		extract($data);
		// exit(json_encode($data));
		$lang_code ??= '';
		$filter_type ??= '';
		$categoryid ??= '';
		$search_term ??= '';
		// $category_id = '';
		$limit = $limit ?? '';
		$error = '';
		// if ($category_id == '') {
		// 	$error = 'Please pass all the mandatory values';
		// } else {
		// 	$this->webshop_obj = new DbProductFeature();
		// 	$this->block_obj = new DbProductReviewFeature();
		// 	$product_array = $this->webshop_obj->getAttributeArray($category_id);
		// }
		if ($filter_type == '') {
			$error = 'Please pass all the mandatory values';
		} else {
			// exit($filter_type);

			$customer_type_id ??= 1;
			$customer_type_id = (int) $customer_type_id;

			$this->webshop_obj = new DbProductFeature();
			$this->block_obj = new DbProductReviewFeature();

			$this->home_obj = new DbHomeFeature();

			$featured_ids = '';
			if ($filter_type == 'hindi_magazines') {
				$productBlock = $this->block_obj->getProductsBlock($filter_type);
				$id_string = trim($productBlock['assigned_products'], ",");
				$id_arr = explode(",", $id_string);
				$featured_ids = "'" . implode("','", $id_arr) . "'";
			}

			$product_array = [];

			$attribut_array = $this->getAttributeArray($lang_code, $categoryid, $customer_type_id, $search_term, $filter_type, $featured_ids);
			// echo "<pre>";
			// print_r($attribut_array);
			// die;
			$product_array['price_range']['min_price_range'] = 0;
			$product_array['price_range']['max_price_range'] = 0;

			if (!empty($attribut_array) && count($attribut_array) > 0) {

				foreach ($attribut_array as $attr_key => $attr_val) {
					$attr_unique = array_values(array_unique($attr_val, SORT_REGULAR));
					$attr_unique_arr[$attr_key] = $attr_unique;
				}
				$product_array['attribute_listing'] = (is_array($attr_unique_arr) && $attr_unique_arr != '[]') ? $attr_unique_arr : NULL;
			} else {

				$product_array['attribute_listing'] = (is_array($attribut_array) && $attribut_array != '[]') ? $attribut_array : NULL;
			}
		}

		// $product_array = null;
		$data = $product_array['attribute_listing']['language__Language'];
		// $variant_data = false; // Initialize $variant_data variable outside the loop
		foreach ($data as $new_data) {
			if ($new_data['attr_options_name'] == 'Hindi') {
				$variant_id = $new_data['variant_id'];
				$attr_value = $new_data['attr_value'];
				// $variant_id = $new_data['variant_id'];

				// $variant_data = true; 
				break;
			} else {
				// echo "hii2<br>";
			}
		}

		$new_product_attribute_array = $this->webshop_obj->getProductAttributeArray($variant_id, $attr_value);
		foreach ($new_product_attribute_array as $data) {
			$new_product_id[] = $data['product_id'];

			// print_r($new_product_id);
			$new_product_array = $this->webshop_obj->getProductArray($new_product_id);
		}
		// echo "<pre>";
		// print_r($new_product_array);
		// die;
		$getHindiMagazineList = $this->webshop_obj->getHindiMagazineList($limit, '', $lang_code, 'hindi_magazines');

		if ($getHindiMagazineList === false) {
			abort('New product not available');
		}
		foreach ($new_product_array as $keybest => $valBest) {
			$new_product_array[$keybest]['parent_id'] = $valBest['id'];
			$new_product_array_[] = $this->webshop_obj->getproductDetailsById($valBest['id']);
		}
		$newarrivalWithbestselling = $new_product_array_;

		$config_products = $this->webshop_obj->configurableProductForMultipleProductsHindi($new_product_id);
		// echo "<pre>";
		// print_r($config_products);
		// die;

		$this->config_product_ids = array_column($config_products, 'id');
		// print_r($config_products);die;
		$this->config_products = array_group($config_products, 'parent_id');
		// print_r($this->config_products);die;
		$this->specialPrices = array_key_by(
			$this->webshop_obj->getSpecialPricesForMultipleHindiProducts(array_merge($new_product_id, $this->config_product_ids)),
			'product_id'
		);
		// echo "<pre>";
		// print_r($this->specialPrices);die;

		$this->shopInventory =
			array_key_by(
				$this->webshop_obj->getAvailableInventoryForMultipleProducts(array_merge($new_product_id, $this->config_product_ids)),
				'product_id'
			);

		$product_array_lastest = [];

		foreach ($newarrivalWithbestselling as $value) {
			// echo "<pre>";
			// print_r($newarrivalWithbestselling);
			// die;
			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategyNew($value); // Not used in shop3? Did not refactor this function.
				// echo "hi1";
			} else if ($value['product_type'] === 'bundle') {
				$value = $this->bundleProductStrategy($value);
				// echo "hi2";

			} else {
				$value = $this->advancedProductStrategyNew($value);
				// echo "hi3";

			}
			// print_r($value);die;
			$product_array_lastest[] = $value;
		}
		// echo "<pre>";
		// print_r($product_array_lastest);
		// die;

		if ($error != '') {
			// exit($filter_type);
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		} else {
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'New product available';
			$message['hindi_magazines'] = $product_array_lastest;
			exit(json_encode($message));
		}
	}


	private function simpleProductStrategyNew($value): array
	{
		if ($this->isEuropeShop) {
			$value['eu_webshop_price'] = calculate_price_with_vat($value['price'], $this->vat_percent_session);
		}

		if ($value['product_inv_type'] === 'buy') {
			$product_inv = $this->webshop_obj->getAvailableInventory($value['id'], $value);
			if ($product_inv['available_qty'] > 0) {
				$value['stock_status'] = 'Instock';
				$value['qty'] = $product_inv['available_qty'];
			}
		}
		$specialPriceArr = $this->specialPrices[$value['id']] ?? false;
		if ($specialPriceArr !== false) {
			$value['special_price'] = $specialPriceArr['special_price'];
			$value['special_price_from'] = $specialPriceArr['special_price_from'];
			$value['special_price_to'] = $specialPriceArr['special_price_to'];
			$value['display_original'] = $specialPriceArr['display_original'];
		}
		return $value;
	}

	private function advancedProductStrategyNew($value)
	{
		// echo "<pre>";

		// print_r($value);
		// die;
		[$value, $qty, $flag, $price_array] = $this->advancedProductStrategyStandardNew($value);
		if ($flag === true) {
			// echo "hii";

			$prices = array_column($price_array, 'different_price');
			$display_original = array_values(array_filter(array_column($price_array, 'display_original')));
			// echo "<pre>";
			// print_r($display_original);
			// die;

			$value['special_price'] = min($prices);
			$value['special_min_price'] = min($prices);
			$value['special_max_price'] = max($prices);
			$value['display_original'] = $display_original[0];
		} else {
			// echo "hii2";
		}
		// echo "<pre>";
		// print_r($value);
		// die;
		if ($qty > 0) {
			$value['stock_status'] = 'Instock';
			$value['qty'] = $qty;
		}
		// print_r($value);die;
		return $value;
	}

	private function advancedProductStrategyStandardNew($value): array
	{
		$flag = false;
		$price_array = [];
		// print_R($value['id']);die;
		// echo "<pre>";
		// print_R($this->config_products);die();
		// $this->config_products[$value['id']] = '';
		// var_dump($value['id']); // Check the value of $value['id']
		// var_dump($this->config_products); // Inspect the contents of $this->config_products
		// var_dump(isset($value['id'])); // Check if $value['id'] is set
		// var_dump(array_key_exists($value['id'], $this->config_products));
		if (isset($this->config_products[$value['id']])) {
			// echo "hi1";

			$configProduct = $this->config_products[$value['id']];
			$value['min_price'] = min(array_column($configProduct, 'webshop_price'));
			$value['max_price'] = max(array_column($configProduct, 'webshop_price'));

			$value['webshop_price'] = $value['min_price'];
			// if ($qty > 0) {
			// 	$value['stock_status'] = 'Instock';
			// 	$value['qty'] = $qty;
			// }
			$qty = 0;

			if ($configProduct !== false) {

				foreach ($configProduct as $conf) {
					// echo "<pre>";
					// print_R($this->specialPrices);
					// die();
					$specialPriceArr = $this->specialPrices[$conf['id']] ?? [];
					// echo "<pre>";
					// print_R($specialPriceArr);
					// die();
					$qty += $this->getAvailableQuantity($conf);
					if (count($specialPriceArr) > 0) {
						// echo "hii1";

						$flag = true;
						$arr['display_original'] = $specialPriceArr['display_original'];
						$arr['different_price'] = $specialPriceArr['special_price'];
						// print_r($flag);
					} else {
						// echo "hii2";
						$arr['different_price'] = $conf['webshop_price'];
						$arr['display_original'] = '';
					}

					$price_array[] = $arr;
				}
			}
			// echoecho $flag;die;

			return [$value, $qty, $flag, $price_array];
		} else {
			// echo "hi2";
			$configProduct = $this->config_products[$value['parent_id']];

			$value['min_price'] = min(array_column($configProduct, 'webshop_price'));
			$value['max_price'] = max(array_column($configProduct, 'webshop_price'));

			$value['webshop_price'] = $value['min_price'];

			$qty = 0;

			if ($configProduct !== false) {
				foreach ($configProduct as $conf) {

					$specialPriceArr = $this->specialPrices[$conf['id']] ?? [];
					$qty += $this->getAvailableQuantity($conf);
					if (count($specialPriceArr) > 0) {
						$flag = true;
						$arr['display_original'] = $specialPriceArr['display_original'];
						$arr['different_price'] = $specialPriceArr['special_price'];
					} else {
						$arr['different_price'] = $conf['webshop_price'];
						$arr['display_original'] = '';
					}

					$price_array[] = $arr;
				}
			}
			return [$value, $qty, $flag, $price_array];
		}
	}

	private function getAttributeArray(string $lang_code, string $categoryid, int $customer_type_id, string $search_term, string $filter_type, string $featured_ids): array
	{
		$attribut_array = [];
		$attributData = $this->webshop_obj->getFiltersAttributeMasterOfShop($lang_code);
		// echo "<pre>";
		// print_r($attributData);
		// die;
		if (is_array($attributData) && count($attributData) > 0 && $attributData != false) {
			$attr_ids = array_column($attributData, 'id');
			$AttrOption = $this->webshop_obj->getOptionsByVariantIdForMultiple($attr_ids);
			// echo "<pre>";
			// print_r($AttrOption);
			// die;
			$attr_match_options_ids = array_column($AttrOption, 'attr_id');
			$attr_options_ids = array_group($AttrOption, 'attr_id');

			foreach ($attributData as $attribute) {
				// echo "<pre>";
				// print_r($attribute);
				// die;
				if (isset($attribute['multi_attr_name']) && $attribute['multi_attr_name'] != '') {
					$attr_name = $attribute['multi_attr_name'];
				} else {
					$attr_name = $attribute['attr_name'];
				}
				$attr_code = $attribute['attr_code'];
				$attr_id = $attribute['id'];
				if (in_array($attribute['id'], $attr_match_options_ids, true)) {
					$attrOptionValueData = $attr_options_ids[$attr_id];
					$attrOptionsIds = array_column($attrOptionValueData, 'id');
					if (isset($attrOptionValueData) && count($attrOptionValueData) > 0) {
						$ProductCountMultipleData = [];
						if (isset($attribute['attr_properties']) && $attribute['attr_properties'] != 6) {
							// print_r($attr_id);
							// print_r($attrOptionsIds);
							// print_r($customer_type_id);
							// die;
							$ProductCountMultiple = $this->webshop_obj->checkProductCountByAttributeMultiple($attr_id, $attrOptionsIds, $customer_type_id);

							// echo "<pre>";
							// print_r($ProductCountMultiple);
							// die;


							if (isset($ProductCountMultiple) && is_array($ProductCountMultiple)) {

								$ProductCountMultipleData = array_column($ProductCountMultiple, 'attr_value');
							}
							$multiSelectFlag = 0;
						} else {
							$multiSelectFlag = 1;
						}
						foreach ($attrOptionValueData as $attr_val) {

							// die;
							if ($multiSelectFlag === 1) {
								// echo "hiii1";

								$ProductCount = (int)$this->webshop_obj->checkProductCountByAttributeOption($attribute['id'], $attr_val['id'], $customer_type_id);
							} else {
								// print_r($attr_id);
								// print_r($attrOptionsIds);
								// print_r($customer_type_id);
								// die;
								$ProductCount = 0;
								if (isset($ProductCountMultipleData) && !empty($ProductCountMultipleData) && is_array($ProductCountMultiple)) {
									if (in_array($attr_val['id'], $ProductCountMultipleData)) {
										$ProductCount = 1;
									}
								}

								// $ProductCount = $this->webshop_obj->checkProductCountByAttributeOption($attribute['id'], $attr_val['id'], $customer_type_id);
								// echo "<pre>";
								// print_r($attr_val);
							}
							// if ($ProductCount != false && $ProductCount > 0) {
							$arr['variant_id'] = $attribute['id'];
							$arr['attr_value'] = $attr_val['id'];
							$arr['attr_options_name'] = $attr_val['attr_options_name'];
							$attribut_array[$attr_code . "__" . $attr_name][] = $arr;
							// }
						}
					}
				}
			}
		}
		return $attribut_array;
	}
}
