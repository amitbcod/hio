<?php

namespace App\Controllers;

use DbProductFeature;
use DbGlobalFeature; // new added
use DbCommonFeature; // new added
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductListingController
{
	private $webshop_obj;
	private $common_obj;
	private $isEuropeShop = true;
	private $vat_percent_session = 0;
	private $specialPrices;
	private $config_products;
	private $config_product_ids;
	private $shopInventory;
	private $externalInventory;
	private $webshop_color_swatch_obj;
	private $multiple_variant_bind = [];
	private $base_color_status;
	private $base_color_listing;
	private $variant_options_ids;

	public function __invoke(Request $request, Response $response, $args)
	{
		// echo "asdsadasdad";die();
		$data = $request->getParsedBody();
		
		extract($data);
		$error = '';
		if (isset($vat_percent_session) && $vat_percent_session !== '') {
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}
		
		$final_arr = [];
		
		$page = $page ?? 0;
		$page_size = $page_size ?? 0;
		
		if ($page > 0) {
			$page = ($page - 1) * $page_size;
		}
		$this->common_obj = new DbCommonFeature();
		$options = $options ?? '';
		$gender = $gender ?? [];
		$categoryid = $categoryid ?? '';
		$search_term = (isset($search_term) ? $this->common_obj->custom_filter_input($search_term) :  '');
		$price_range = $price_range ?? [];
		$variant_id_arr = $variant_id_arr ?? [];
		$variant_attr_value_arr = $variant_attr_value_arr ?? [];
		$attribute_arr = $attribute_arr ?? [];
		$language_arr = $language_arr ?? [];
		
		$lang_code = $lang_code ?? '';
		$subscription = $subscription ?? [];
		$categoryIdsarr = $categoryIdsarr ?? [];
		
		$this->webshop_obj = new DbProductFeature();
		$product_listing_count = (int) $this->webshop_obj->productListingCount($categoryid, $options, $gender, $price_range, $variant_id_arr, $variant_attr_value_arr, $attribute_arr, $search_term, $categoryIdsarr,$language_arr);
		
		
		$product_listing = $this->webshop_obj->productListing($categoryid, $options, $gender, $subscription, $price_range, $variant_id_arr, $variant_attr_value_arr, $attribute_arr, $search_term, $page, $page_size, $categoryIdsarr);
		// echo "<pre>";
		// print_R($product_listing);die;

		if ($product_listing === false) {
			abort('New product not available');
		}

		$product_ids = array_column($product_listing, 'id');
		$config_products = $this->webshop_obj->configurableProductForMultipleProducts($product_ids);
		$this->config_product_ids = array_column($config_products, 'id');

		$this->config_products = array_group($config_products, 'parent_id');

		$wishlistData = [];
		if (isset($customer_login_id) && $customer_login_id > 0) {

			$wishlistData = $this->webshop_obj->getWishlistCountForMultipleProducts($customer_login_id, $product_ids);
		}

		$this->specialPrices = array_key_by(
			$this->webshop_obj->getSpecialPricesForMultipleProducts(array_merge($product_ids, $this->config_product_ids)),
			'product_id'
		);
		if ($this->webshop_obj->getAvailableInventoryForMultipleProducts(array_merge($product_ids, $this->config_product_ids))) {
			$this->shopInventory = array_key_by(
				$this->webshop_obj->getAvailableInventoryForMultipleProducts(array_merge($product_ids, $this->config_product_ids)),
				'product_id'
			);
		}

		// print_r($config_products);
		// die();
		// print_r($this->shopInventory);

		// print_r($this->base_color_listing);
		// exit();

		$external_products = array_reduce($config_products, function ($carry, $product) {
			if (!is_array($carry)) {
				$carry = [];
			}
			/* if($product['product_inv_type'] === 'dropship' || $product['product_inv_type'] === 'virtual'){
				$carry[$product['shop_id']][] = $product['shop_product_id'];
			} */
			return $carry;
		});
		//exit(json_encode($external_products));
		$this->externalInventory = [];
		if ($external_products) {
			foreach ($external_products as $shop_id => $shop_external_product_ids) {
				$this->externalInventory = array_merge(
					$this->externalInventory,
					$this->webshop_obj->getAvailableInventoryForMultipleProducts($shop_external_product_ids),
				);
			}
		}

		$this->externalInventory = array_key_by($this->externalInventory, 'product_id');

		//print_r($this->multiple_variant_bind);exit(); //working on
		// print_r($product_listing);die();
		
		if (isset($product_listing)) {
			// echo "hi1";die;
			foreach ($product_listing as $value) {
				if (isset($customer_login_id) && $customer_login_id > 0 && in_array($value['id'], $wishlistData, true)) {
					$value['wishlist_status'] = 1;
				} else {
					$value['wishlist_status'] = 0;
				}
				
				if ($value['product_type'] === 'simple') {
					// echo "h1";
					$value = $this->simpleProductStrategy($value); // Not used in shop3? Did not refactor
					// print_r($value);
				} else if ($value['product_type'] === 'bundle') {
					// echo "h2";
					$value = $this->bundleProductStrategy($value);
					// print_r($value);
				} else {
					// echo "h3";
					$value = $this->advancedProductStrategy($value);
					// print_r($value);
				}
				$final_arr[] = $value;
			}
			// echo "<pre>";
			// print_r($final_arr);
			// echo "<pre>";
			// print_r($product_listing_count);
			// exit;
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'New product available';
			$message['ProductList'] = $final_arr;
			$message['ProductListCount'] = $product_listing_count;
			exit(json_encode($message));
		} else {
			// echo "hi2";die;

			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'No product!';
			$message['ProductList'] = [];
			$message['ProductListCount'] = $product_listing_count;
			exit(json_encode($message));
		}
	}

	private function bundleProductStrategy($value): array
	{
		// var_dump($this->isEuropeShop); exit;
		if ($this->isEuropeShop) {
			$value['eu_webshop_price'] = calculate_price_with_vat($value['price'], $this->vat_percent_session);
		}
		$value['stock_status'] = 'Instock';
		$value['qty'] = 1;

		$specialPriceArr = false;
		return $value;
	}

	private function simpleProductStrategy($value): array
	{

		$product_inv = $this->webshop_obj->getAvailableInventory($value['id'],  $value, null);

		if ($product_inv['available_qty'] > 0) {
			$value['stock_status'] = 'Instock';
			$value['qty'] = $product_inv['available_qty'];
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

	private function advancedProductStrategy($value)
	{
		if ($this->isEuropeShop) {
			// echo "h1";die;
			[$value, $qty, $flag, $price_array] = $this->advancedProductStrategyEurope($value);
		} else {
			echo "h2";die;

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

	private function sortProductsByPrice(string $options, array $final_arr): array
	{
		if ($this->isEuropeShop) {
			if ($options === 'price_des') {
				$keys = array_column($final_arr, 'eu_webshop_price');
				array_multisort($keys, SORT_DESC, $final_arr);
			} elseif ($options === 'price_asc') {
				$keys = array_column($final_arr, 'eu_webshop_price');
				array_multisort($keys, SORT_ASC, $final_arr);
			}
		} else {
			if ($options === 'price_des') {
				$keys = array_column($final_arr, 'webshop_price');
				array_multisort($keys, SORT_DESC, $final_arr);
			} elseif ($options === 'price_asc') {
				$keys = array_column($final_arr, 'webshop_price');
				array_multisort($keys, SORT_ASC, $final_arr);
			}
		}
		return $final_arr;
	}

	private function advancedProductStrategyEurope($value): array
	{

		$eu_webshop_priceArray = [];
		$price_array = [];
		$flag = false;

		$configProduct = $this->config_products[$value['id']] ?? [];
		$this->base_color_status = 'yes';
		$qty = 0;
		$base_color_array = [];
		// echo count($configProduct);exit();
		if (count($configProduct) > 0) {
			foreach ($configProduct as $conf) {
				$eu_webshop_price = calculate_price_with_vat($conf['price'], $this->vat_percent_session);
				$eu_webshop_priceArray[] = $eu_webshop_price;

				$qty += $this->getAvailableQuantity($conf);
				
				if ($this->base_color_status == 'yes' && $this->getAvailableQuantity($conf) > 0) {
					 if (isset($this->multiple_variant_bind[$conf['parent_id']][$conf['id']])) {
						$getBaseValue = $this->multiple_variant_bind[$conf['parent_id']][$conf['id']];
						// print_r($getBaseValue); 
						// exit;
					} else {
						// Keys are missing, debug the structure
						continue;
					}
				} else {
					unset($this->multiple_variant_bind[$value['parent_id']][$value['id']]);
				}
				
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
			$value['base_color'] = $base_color_array;
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
		return max(
			0,
			(int) ($this->shopInventory[$product['id']]['available_qty'] ?? 0)
		);
	}


	public function regional_magazine(Request $request, Response $response, $args)
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
		if (isset($vat_percent_session) && $vat_percent_session !== '') {
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}
		if ($filter_type == '') {
			$error = 'Please pass all the mandatory values';
		} else {
			// exit($filter_type);

			$customer_type_id ??= 1;
			$customer_type_id = (int) $customer_type_id;

			$this->webshop_obj = new DbProductFeature();
			// $this->block_obj = new DbProductReviewFeature();

			// $this->home_obj = new DbHomeFeature();

			$featured_ids = '';
			if ($filter_type == 'regional_magazine') {
				$productBlock = $this->webshop_obj->getRegionalProductsBlock($filter_type);
				// echo "<pre>";
				// print_r($productBlock);
				// die;
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

		// echo "<pre>";
		// print_r($data);
		// die;
		foreach ($data as $new_data) {
			// echo "<pre>";
			// print_r($new_data);
			// die;
			// if ($new_data['attr_options_name'] != 'Hindi' || $new_data['attr_options_name'] != 'English' || $new_data['attr_options_name'] != 'English and Hindi') {
			// 	// echo "<pre>";
			// 	// print_r($new_data);
			// 	$variant_id = $new_data['variant_id'];
			// 	$attr_value = $new_data['attr_value'];
			// 	// echo "</pre>";
			// } else {

			// 	// echo "hii2<br>";
			// }
			$variant_id = $new_data['variant_id'];

			$attr_value = $new_data['attr_value'];

			$new_product_attribute_array = $this->webshop_obj->getRegionalProductAttributeArray($variant_id, $attr_value);

			// Check the type and contents of $new_product_attribute_array
			// var_dump($new_product_attribute_array);

			if (!isset($new_product_id) || !is_array($new_product_id)) {
				$new_product_id = [];
			}

			// Ensure $new_product_attribute_array is an array before iterating over it
			if (is_array($new_product_attribute_array)) {
				foreach ($new_product_attribute_array as $data) {
					$new_product_id[] = $data['product_id'];
				}
				$new_product_array = $this->webshop_obj->getRegionalProductArray($new_product_id);
			} else {
			}
		}

		// die;
		// die;
		// $new_product_id = [];
		$getHindiMagazineList = $this->webshop_obj->getRegionalMagazineList($limit, '', $lang_code, 'regional_magazine');
		// echo "<pre>";
		// print_r($getHindiMagazineList);
		// die;

		if ($getHindiMagazineList === false) {
			abort('New product not available');
		}
		foreach ($new_product_array as $keybest => $valBest) {
			$new_product_array[$keybest]['parent_id'] = $valBest['id'];
			$new_product_array_[] = $this->webshop_obj->getRegionalproductDetailsById($valBest['id']);
		}
		$newarrivalWithbestselling = $new_product_array_;
		// echo "<pre>";
		// print_r($newarrivalWithbestselling);
		// die;
		$config_products = $this->webshop_obj->configurableProductForMultipleProductsRegional($new_product_id);
		// echo "<pre>";
		// print_r($config_products);
		// die;

		$this->config_product_ids = array_column($config_products, 'id');
		// print_r($config_products);die;
		$this->config_products = array_group($config_products, 'parent_id');
		// print_r($this->config_products);die;
		$this->specialPrices = array_key_by(
			$this->webshop_obj->getSpecialPricesForMultipleRegionalProducts(array_merge($new_product_id, $this->config_product_ids)),
			'product_id'
		);
		// print_r($this->specialPrices);die;

		$this->shopInventory =
			array_key_by(
				$this->webshop_obj->getAvailableInventoryForMultipleRegionalProducts(array_merge($new_product_id, $this->config_product_ids)),
				'product_id'
			);

		$product_array_lastest = [];

		// echo "<pre>";
		// print_r($newarrivalWithbestselling);
		// die;
		foreach ($newarrivalWithbestselling as $value) {
			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategyRegionalMag($value); // Not used in shop3? Did not refactor this function.
				// echo "hi1";
			} else if ($value['product_type'] === 'bundle') {
				$value = $this->bundleProductStrategy($value);
				// echo "hi2";

			} else {
				$value = $this->advancedProductStrategyRegionalMags($value);
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
			$message['regional_magazine'] = $product_array_lastest;
			exit(json_encode($message));
		}
	}

	public function regional_magazine_test(Request $request, Response $response, $args)
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
		if (isset($vat_percent_session) && $vat_percent_session !== '') {
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}
		if ($filter_type == '') {
			$error = 'Please pass all the mandatory values';
		} else {
			// exit($filter_type);

			$customer_type_id ??= 1;
			$customer_type_id = (int) $customer_type_id;

			$this->webshop_obj = new DbProductFeature();
			// $this->block_obj = new DbProductReviewFeature();

			// $this->home_obj = new DbHomeFeature();

			$featured_ids = '';
			if ($filter_type == 'regional_magazine') {
				$productBlock = $this->webshop_obj->getRegionalProductsBlock($filter_type);
				// echo "<pre>";
				// print_r($productBlock);
				// die;
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

		// echo "<pre>";
		// print_r($data);
		// die;
		foreach ($data as $new_data) {
			// echo "<pre>";
			// print_r($new_data);
			// die;
			// if ($new_data['attr_options_name'] != 'Hindi' || $new_data['attr_options_name'] != 'English' || $new_data['attr_options_name'] != 'English and Hindi') {
			// 	// echo "<pre>";
			// 	// print_r($new_data);
			// 	$variant_id = $new_data['variant_id'];
			// 	$attr_value = $new_data['attr_value'];
			// 	// echo "</pre>";
			// } else {

			// 	// echo "hii2<br>";
			// }
			$variant_id = $new_data['variant_id'];

			$attr_value = $new_data['attr_value'];

			$new_product_attribute_array = $this->webshop_obj->getRegionalProductAttributeArray($variant_id, $attr_value);

			// Check the type and contents of $new_product_attribute_array
			// var_dump($new_product_attribute_array);

			if (!isset($new_product_id) || !is_array($new_product_id)) {
				$new_product_id = [];
			}

			// Ensure $new_product_attribute_array is an array before iterating over it
			if (is_array($new_product_attribute_array)) {
				foreach ($new_product_attribute_array as $data) {
					$new_product_id[] = $data['product_id'];
				}
				$new_product_array = $this->webshop_obj->getRegionalProductArray($new_product_id);
			} else {
			}
		}

		// die;
		// die;
		// $new_product_id = [];
		$getHindiMagazineList = $this->webshop_obj->getRegionalMagazineList($limit, '', $lang_code, 'regional_magazine');
		// echo "<pre>";
		// print_r($getHindiMagazineList);
		// die;

		if ($getHindiMagazineList === false) {
			abort('New product not available');
		}
		foreach ($new_product_array as $keybest => $valBest) {
			$new_product_array[$keybest]['parent_id'] = $valBest['id'];
			$new_product_array_[] = $this->webshop_obj->getRegionalproductDetailsById($valBest['id']);
		}
		$newarrivalWithbestselling = $new_product_array_;

		$config_products = $this->webshop_obj->configurableProductForMultipleProductsRegional($new_product_id);
		// echo "<pre>";
		// print_r($config_products);
		// die;

		$this->config_product_ids = array_column($config_products, 'id');
		// print_r($config_products);die;
		$this->config_products = array_group($config_products, 'parent_id');
		// print_r($this->config_products);die;
		$this->specialPrices = array_key_by(
			$this->webshop_obj->getSpecialPricesForMultipleRegionalProducts(array_merge($new_product_id, $this->config_product_ids)),
			'product_id'
		);
		// print_r($this->specialPrices);die;

		$this->shopInventory =
			array_key_by(
				$this->webshop_obj->getAvailableInventoryForMultipleRegionalProducts(array_merge($new_product_id, $this->config_product_ids)),
				'product_id'
			);

		$product_array_lastest = [];

		// echo "<pre>";
		// print_r($newarrivalWithbestselling);
		// die;
		foreach ($newarrivalWithbestselling as $value) {
			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategyRegionalMag($value); // Not used in shop3? Did not refactor this function.
				// echo "hi1";
			} else if ($value['product_type'] === 'bundle') {
				$value = $this->bundleProductStrategy($value);
				// echo "hi2";

			} else {
				$value = $this->advancedProductStrategyRegionalMags($value);
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
			$message['regional_magazine'] = $product_array_lastest;
			exit(json_encode($message));
		}
	}

	private function simpleProductStrategyRegionalMag($value): array
	{
		if ($this->isEuropeShop) {
			$value['eu_webshop_price'] = calculate_price_with_vat($value['price'], $this->vat_percent_session);
		}

		if ($value['product_inv_type'] === 'buy') {
			$product_inv = $this->webshop_obj->getRegionalAvailableInventory($value['id'], $value);
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

	private function advancedProductStrategyRegionalMags($value)
	{
		// echo "<pre>";

		// print_r($value);
		// die;
		[$value, $qty, $flag, $price_array] = $this->advancedProductStrategyStandardRegionalMags($value);
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
		}
		// else {
		// 	echo "hii2";
		// }
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

	private function advancedProductStrategyStandardRegionalMags($value): array
	{
		$flag = false;
		$price_array = [];
		// print_R($value['id']);die;
		// echo "<pre>";
		// print_R($this->config_products);die();
		// $this->config_products[$value['id']] = '';
		if (isset($this->config_products[$value['id']])) {
			// echo "hi1";

			$configProduct = $this->config_products[$value['id']];
			// echo "<pre>";
			// print_R($configProduct);
			// die();
			$value['min_price'] = min(array_column($configProduct, 'webshop_price'));
			$value['max_price'] = max(array_column($configProduct, 'webshop_price'));

			$value['webshop_price'] = $value['min_price'];
			// if ($qty > 0) {
			// 	$value['stock_status'] = 'Instock';
			// 	$value['qty'] = $qty;
			// }
			$qty = 0;

			if ($configProduct !== false) {
				// echo "hii1";

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
			$AttrOption = $this->webshop_obj->getOptionsByVariantIdForMultipleRegional($attr_ids);
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
