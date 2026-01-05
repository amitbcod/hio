<?php
namespace App\Controllers;

use DbHomeFeature;
use DbProductFeature;
use DbProductReviewFeature;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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

	public function __invoke(Request $request, Response $response, $args){		
	}

	public function newArrivals(Request $request, Response $response, $args){
		$data = $request->getParsedBody();
		extract($data);
		$error = '';
		if (!isset($shopcode) || $shopcode === '') {
			abort('Please pass all the mandatory values');
		}

		$this->shopcode = $shopcode;
		$this->shopid = $shopid;
		$this->customer_type_id = $customer_type_id ?? 1;
		$limit = $limit ?? '';
		if(isset($vat_percent_session) && $vat_percent_session !== ''){
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}

		$lang_code = $lang_code ?? '';

		$product_array = [];	

		$this->webshop_obj = new DbHomeFeature();
		$this->product_obj = new DbProductFeature(); 
		$getNewArrivalList = $this->webshop_obj->getNewArrivalList($this->shopcode,$this->shopid,$this->customer_type_id,$limit,'',$lang_code,'new_arrivals');

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
	
		$external_products = array_reduce($config_products, function($carry, $product){
			if(!is_array($carry)){
				$carry = [];
			}
			if($product['product_inv_type'] === 'dropship' || $product['product_inv_type'] === 'virtual'){
				$carry[$product['shop_id']][] = $product['shop_product_id'];
			}
			return $carry;
		});
		//exit(json_encode($external_products));
		$this->externalInventory = [];
		if($external_products){
			foreach($external_products as $shop_id => $shop_external_product_ids){
				$this->externalInventory = array_merge(
					$this->externalInventory,
					$this->product_obj->getAvailableInventoryForMultipleProducts($shop_external_product_ids, 'shop'.$shop_id),
				);
			}
		}

		$this->externalInventory = array_key_by($this->externalInventory, 'product_id');


		foreach ($getNewArrivalList as $value) {
			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategy($value); // Not used in shop3? Did not refactor this function.
			} else {
				$value = $this->advancedProductStrategy($value);
			}
			$product_array[] = $value;
		}
		
		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;      
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'New product available';
			$message['NewArrivalProduct'] = $product_array;
			exit(json_encode($message));
		}	

	}

	public function newArrivals_page(Request $request, Response $response, $args){
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
		if($page>0){
			
			$page = ($page - 1) * $limit;
		}
		$options = (isset($options) ? $options : '');

		if(isset($vat_percent_session) && $vat_percent_session !== ''){
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}

		$lang_code = $lang_code ?? '';

		$product_array = [];	

		$this->webshop_obj = new DbHomeFeature();
		$this->product_obj = new DbProductFeature(); 

		$getNewArrivalList_count = $this->webshop_obj->getNewArrivalList_product_count($this->shopcode,$this->shopid,$this->customer_type_id,'',$lang_code,'new_arrivals',$options); 
		// print_r($getNewArrivalList_count);exit;
		if($getNewArrivalList_count!=false){
			$getNewArrivalList_count=$getNewArrivalList_count;
		}else{
			$getNewArrivalList_count=0;
		}

		$getNewArrivalList = $this->webshop_obj->getNewArrivalList($this->shopcode,$this->shopid,$this->customer_type_id,$limit,'',$lang_code,'new_arrivals',$page,$options);

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

		
		$external_products = array_reduce($config_products, function($carry, $product){
			if(!is_array($carry)){
				$carry = [];
			}
			if($product['product_inv_type'] === 'dropship' || $product['product_inv_type'] === 'virtual'){
				$carry[$product['shop_id']][] = $product['shop_product_id'];
			}
			return $carry;
		});
		//exit(json_encode($external_products));
		$this->externalInventory = [];
		if($external_products){
			foreach($external_products as $shop_id => $shop_external_product_ids){
				$this->externalInventory = array_merge(
					$this->externalInventory,
					$this->product_obj->getAvailableInventoryForMultipleProducts($shop_external_product_ids, 'shop'.$shop_id),
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
		
		if(empty($product_array))
		{
			if($page > 1)
			{
				$message['statusCode'] = '200';
				$message['is_success'] = 'true';
				$message['message'] = 'No product!';
				$message['ProductList'] = $product_array;
				$message['ProductListCount'] = $getNewArrivalList_count;
				exit(json_encode($message));
			}else{
				$message['statusCode'] = '500';
				$message['is_success'] = 'false';
				$message['message'] = $error;
				exit(json_encode($message));
			}
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'New product available';
			$message['ProductList'] = $product_array;
			$message['ProductListCount'] = $getNewArrivalList_count;
			exit(json_encode($message));
		}	

	}

	public function featuredProducts(Request $request, Response $response, $args){
		$data = $request->getParsedBody();
		extract($data);
		$error = '';
		if (!isset($shopcode) || $shopcode === '' || $identifier=='') {
			abort('Please pass all the mandatory values');
		}

		$this->shopcode = $shopcode;
		$this->shopid = $shopid;
		$this->identifier = $identifier;
		$this->customer_type_id = $customer_type_id ?? 1;
		$limit = $limit ?? '';

		if(isset($vat_percent_session) && $vat_percent_session !== ''){
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}

		$lang_code = $lang_code ?? '';

		$product_array = [];
		
			
		$this->webshop_obj = new DbHomeFeature();
		$this->product_obj = new DbProductFeature();
		$this->block_obj = new DbProductReviewFeature();	
		
		$productBlock = $this->block_obj->getProductsBlock($this->shopcode,$this->shopid,$this->identifier);

		if ($productBlock === false) {
			abort('Data not available for the '.$identifier.' identifier');
		}		
		
		$id_string = trim($productBlock['assigned_products'],","); //remove comma from start and end of the string.
		$id_arr = explode(",",$id_string); // convert string to array
		$ids = "'".implode("','", $id_arr)."'";  // Convert array to string with quote and comma.

		
		$getNewArrivalList = $this->webshop_obj->getNewArrivalList($this->shopcode,$this->shopid,$this->customer_type_id,$limit,$ids,$lang_code);

		
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

		
		$external_products = array_reduce($config_products, function($carry, $product){
			if(!is_array($carry)){
				$carry = [];
			}
			if($product['product_inv_type'] === 'dropship' || $product['product_inv_type'] === 'virtual'){
				$carry[$product['shop_id']][] = $product['shop_product_id'];
			}
			return $carry;
		});
		//exit(json_encode($external_products));
		$this->externalInventory = [];
		if($external_products){
			foreach($external_products as $shop_id => $shop_external_product_ids){
				$this->externalInventory = array_merge(
					$this->externalInventory,
					$this->product_obj->getAvailableInventoryForMultipleProducts($shop_external_product_ids, 'shop'.$shop_id),
				);
			}
		}

		$this->externalInventory = array_key_by($this->externalInventory, 'product_id');


		foreach ($getNewArrivalList as $value) {
			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategy($value); // Not used in shop3? Did not refactor this function.
			} else {
				$value = $this->advancedProductStrategy($value);
			}
			$product_array[] = $value;
		}	
		
		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;      
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Product available';
			$message['productBlockList'] = $product_array;
			exit(json_encode($message));
		}

	}


	public function featuredProducts_page(Request $request, Response $response, $args){
		$data = $request->getParsedBody();

		extract($data);
		$error = '';
		if (!isset($shopcode) || $shopcode === '' || $identifier=='') {
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
		if($page>0){
			
			$page = ($page - 1) * $limit;
		}
		$options = (isset($options) ? $options : '');

		if(isset($vat_percent_session) && $vat_percent_session !== ''){
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}

		$lang_code = $lang_code ?? '';

		$product_array = [];
		
			
		$this->webshop_obj = new DbHomeFeature();
		$this->product_obj = new DbProductFeature();
		$this->block_obj = new DbProductReviewFeature();	
		
		$productBlock = $this->block_obj->getProductsBlock($this->shopcode,$this->shopid,$this->identifier);

		if ($productBlock === false) {
			abort('Data not available for the '.$identifier.' identifier');
		}		
		
		$id_string = trim($productBlock['assigned_products'],","); //remove comma from start and end of the string.
		$id_arr = explode(",",$id_string); // convert string to array
		$ids = "'".implode("','", $id_arr)."'";  // Convert array to string with quote and comma.

		$getNewArrivalList_count = $this->webshop_obj->getNewArrivalList_product_count($this->shopcode,$this->shopid,$this->customer_type_id,$ids,$lang_code,'',$options); 
		// print_r($getNewArrivalList_count);exit;
		if($getNewArrivalList_count!=false){
			$getNewArrivalList_count=$getNewArrivalList_count;
		}else{
			$getNewArrivalList_count=0;
		}
		
		$getNewArrivalList = $this->webshop_obj->getNewArrivalList($this->shopcode,$this->shopid,$this->customer_type_id,$limit,$ids,$lang_code,'',$page,$options);	
		
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

		
		$external_products = array_reduce($config_products, function($carry, $product){
			if(!is_array($carry)){
				$carry = [];
			}
			if($product['product_inv_type'] === 'dropship' || $product['product_inv_type'] === 'virtual'){
				$carry[$product['shop_id']][] = $product['shop_product_id'];
			}
			return $carry;
		});
		//exit(json_encode($external_products));
		$this->externalInventory = [];
		if($external_products){
			foreach($external_products as $shop_id => $shop_external_product_ids){
				$this->externalInventory = array_merge(
					$this->externalInventory,
					$this->product_obj->getAvailableInventoryForMultipleProducts($shop_external_product_ids, 'shop'.$shop_id),
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
		

		if(empty($product_array))
		{
			if($page > 1)
			{
				$message['statusCode'] = '200';
				$message['is_success'] = 'true';
				$message['message'] = 'No product!';
				$message['ProductList'] = $product_array;
				$message['ProductListCount'] = $getNewArrivalList_count;
				exit(json_encode($message));
			}else{
				$message['statusCode'] = '500';
				$message['is_success'] = 'false';
				$message['message'] = $error;
				exit(json_encode($message));
			}
		}else{
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
		if($this->isEuropeShop) {
			[$value, $qty, $flag, $price_array] = $this->advancedProductStrategyEurope($value);
		} else {
			[$value, $qty, $flag, $price_array] = $this->advancedProductStrategyStandard($value);
		}

		if ($flag === true) {
			$prices = array_column($price_array, 'different_price');
			$display_original = array_values(array_filter(array_column($price_array, 'display_original')));

			$value['special_price'] = min($prices);
			$value['special_min_price'] = min($prices);
			$value['special_max_price'] = max($prices);
			$value['display_original'] = $display_original[0];
		}

		if ($qty > 0) {
			$value['stock_status'] = 'Instock';
			$value['qty'] = $qty;
		}
		return $value;
	}

	private function simpleProductStrategy($value): array
	{
		if($this->isEuropeShop) {
			$value['eu_webshop_price'] = calculate_price_with_vat($value['price'], $this->vat_percent_session);
		}


		if ($value['product_inv_type'] === 'buy') {
			$product_inv = $this->product_obj->getAvailableInventory($value['id'], $this->shopcode, '', $value);
			if ($product_inv['available_qty'] > 0) {
				$value['stock_status'] = 'Instock';
				$value['qty'] = $product_inv['available_qty'];
			}
		} elseif ($value['product_inv_type'] === 'virtual' || $value['product_inv_type'] === 'dropship') {
			$seller_shopcode = 'shop' . $value['shop_id'];
			$product_inv1 = $this->product_obj->getAvailableInventory($value['id'], $this->shopcode, $seller_shopcode, $value);
			if ($product_inv1['available_qty'] > 0) {
				$value['stock_status'] = 'Instock';
				$value['qty'] = $product_inv1['available_qty'];
			}
		}


		$specialPriceArr = $this->specialPrices[$value['id']] ?? false;

		if ($specialPriceArr !== false) {
			if($this->isEuropeShop) {
				$value['special_price'] = calculate_price_with_vat($specialPriceArr['special_price'], $this->vat_percent_session);
			} else {
				$value['special_price'] = $specialPriceArr['special_price'];
			}
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

		$configProduct = $this->config_products[$value['id']] ?? [];

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
		$flag = false;
		$price_array = [];

		$configProduct = $this->config_products[$value['id']];

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

	private function getAvailableQuantity($product)
	{
		if ($product['product_inv_type'] === 'buy') {
			return max(
				0,
				(int) ($this->shopInventory[$product['id']]['available_qty'] ?? 0)
			);
		}

		if ($product['product_inv_type'] === 'virtual') {
			return max(
				0,
				(int) ($this->externalInventory[$product['shop_product_id']]['available_qty'] ?? 0),
				(int) ($this->shopInventory[$product['id']]['available_qty'] ?? 0)
			);
		}

		if($product['product_inv_type'] === 'dropship') {
			return max(
				0,
				(int) ($this->externalInventory[$product['shop_product_id']]['available_qty'] ?? 0)
			);
		}

		return 0;
	}
}
