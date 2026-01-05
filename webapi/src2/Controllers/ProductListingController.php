<?php

namespace App\Controllers;

use DbProductFeature;
use DbGlobalFeature; // new added
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductListingController
{
	private $webshop_obj;
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
	private $webshop_color_swatch_obj;
	private $multiple_variant_bind;
	private $base_color_status;
	private $base_color_listing;
	private $variant_options_ids;

	public function __invoke(Request $request, Response $response, $args){
		$data = $request->getParsedBody();
		extract($data);

		$error = '';
		if (!isset($shopcode) || $shopcode === '' || !isset($shopid) || $shopid === '') {
			abort('Please pass all the mandatory values');
		}
		$this->shopcode = $shopcode;
		$this->shopid = $shopid;
		$this->customer_type_id = $customer_type_id ?? 1;

		if(isset($vat_percent_session) && $vat_percent_session !== ''){
			$this->isEuropeShop = true;
			$this->vat_percent_session = (int) $vat_percent_session;
		}

		

		$final_arr = [];

		$page = $page ?? 0;
		$page_size = $page_size ?? 0;

		if ($page > 0) {
			$page = ($page - 1) * $page_size;
		}

		$options = $options ?? '';
		$gender = $gender ?? [];
		$categoryid = $categoryid ?? '';
		$search_term = $search_term ?? '';
		$price_range = $price_range ?? [];
		$variant_id_arr = $variant_id_arr ?? [];
		$variant_attr_value_arr = $variant_attr_value_arr ?? [];
		$attribute_arr = $attribute_arr ?? [];
		$lang_code = $lang_code ?? '';


		$this->webshop_obj = new DbProductFeature();

		$product_listing_count = (int) $this->webshop_obj->productListingCount($this->shopcode, $this->shopid, $categoryid, $options, $this->customer_type_id, $gender, $price_range, $variant_id_arr, $variant_attr_value_arr, $attribute_arr, $search_term);
		// print_r($product_listing_count-2);die();

		$product_listing = $this->webshop_obj->productListing($this->shopcode, $this->shopid, $categoryid, $options, $this->customer_type_id, $gender, $price_range, $variant_id_arr, $variant_attr_value_arr, $attribute_arr, $search_term, $page, $page_size,$lang_code);

		// Inventory check code start
		// if(isset($options) && !empty($options)){
		// 	$product_listing_child_pid = $this->webshop_obj->productListingFilteredpid($this->shopcode, $this->shopid, $categoryid, $options, $this->customer_type_id, $gender, $price_range, $variant_id_arr, $variant_attr_value_arr, $attribute_arr, $search_term, $page, $page_size,$lang_code);
		// 	// print_r($product_listing);die();

		// 	foreach ($product_listing_child_pid as $key => $value) {
		// 		$product_listing_child[]=$value['child_product_id'];
		// 		$child_pid=$value['child_product_id'];
		// 		$shop_id= $value['shop_id']==0 ? '': $value['shop_id'];
		// 		$product_listing_child_pid_inv[] = $this->webshop_obj->getInventory($child_pid,$this->shopcode,$shop_id);

		// 	}
		// 	// find if its empty
		// 	foreach ($product_listing_child_pid_inv as $key => $value) {
		// 		if ($value['available_qty']<=0) {
		// 			// echo "its empty";
		// 			$parent_prod_ids = $this->webshop_obj->getParent_id($value['product_id'],$this->shopcode);
		// 			// echo $parent_prod_ids['parent_id'];
		// 			$product_listing_key[]=array_search($parent_prod_ids['parent_id'], array_column($product_listing, 'id'));
		// 			// echo $product_listing_key;
		// 			// unset($product_listing[$product_listing_key]);


		// 		}
		// 	}
		// 	foreach ($product_listing_key as $key => $value) {
		// 		// echo $value;
		// 		unset($product_listing[$value]);
		// 		$product_listing_count=$product_listing_count-1;
		// 	}
		// 	if (empty($product_listing)) {
		// 		$product_listing=false;
		// 	}
		// 	// print_r($product_listing_child);die();
		// 	// print_r($product_listing);die();


		
		// 	// die();
		// 	// unset($product_listing[0]);
		// 	// print_r($product_listing);die();

		// 	// print_r($product_listing_child_pid);die();
		// 	// $product_listing_child_pid_inv = $this->webshop_obj->getInventory(18,$this->shopcode);
		// 	// print_r($product_listing_child_pid_inv);die();
		// }
		
		if ($product_listing === false) {
			abort('New product not available');
		}

		$product_ids = array_column($product_listing, 'id');
		$config_products = $this->webshop_obj->configurableProductForMultipleProducts($this->shopcode, $this->shopid, $product_ids);
		$this->config_product_ids = array_column($config_products, 'id');

		$this->config_products = array_group($config_products, 'parent_id');


		// start color swatches new theme zumbawear if color base yes
			if($this->shopid==1){
				// base color status check
					$this->webshop_color_swatch_obj = new DbGlobalFeature();
					$webshop_base_color_status = $this->webshop_color_swatch_obj->get_custom_variable($this->shopcode,'use_base_colors');
					if(isset($webshop_base_color_status) && !empty($webshop_base_color_status)){
						$this->base_color_status=$webshop_base_color_status['value'];
						if(isset($this->base_color_status) && !empty($this->base_color_status) && $this->base_color_status=="yes"){

							// base color list actual list
							//$shop_base_color_listing = $this->webshop_obj->getBaseColorData($this->shopcode,$this->shopid);
							// end base color list
							// bipin

							 $multiple_variants_products = $this->webshop_obj->configProductVariantProduct($this->shopcode, $this->shopid, $product_ids);
							 $multiple_variant_id = array_column($multiple_variants_products, 'id');
							 $multiple_product_id = array_column($multiple_variants_products, 'product_id');
							 $variantOption = $this->webshop_obj->productMultipleVariantOptionsNew($this->shopcode, $this->shopid, $product_ids,$multiple_variant_id);
							 // $variantOption = $this->webshop_obj->productMultipleVariantOptions($this->shopcode, $this->shopid, $product_ids,$multiple_variant_id);
							 //$this->multiple_variant_bind = array_group($variantOption, 'parent_id');
							 //$multiple_variant_bind_new = Arrays::groupBy($variantOption, Functions::extract()->product_id);

							 $this->multiple_variant_bind = array();

							foreach ($variantOption as $key => $item) {
							   $this->multiple_variant_bind[$item['parent_id']][$item['product_id']] = $item;
							   //$this->multiple_variant_bind[][$item['product_id']] = $item;
							}

							 //print_r($this->multiple_variant_bind);exit();
							// end bipin


							//$variantArray = [];
							 //start base color list varient wise
							/*if(is_array($shop_base_color_listing) && count($shop_base_color_listing)>0  && $shop_base_color_listing!=false){

								$base_ids = array_column($shop_base_color_listing, 'id');
							 	$base_color_varient_listing=$this->webshop_obj->getOptionsByBaseColorIdForMultiple($this->shopcode,$this->shopid,$base_ids);
							 	$variant_match_options_ids = array_column($base_color_varient_listing, 'base_color_id');
								$this->variant_options_ids = array_group($base_color_varient_listing, 'base_color_id');
								$check_varient_id=array();

								$variant_id=6; // static varient id pending
								//$categoryid=10;
								// start

								// varient wise product list check and add varient if stock avaialible

									foreach($shop_base_color_listing as $variant){
											$color_name = $variant['color_name'];
											$square_color = $variant['square_color'];
										if(in_array($variant['id'], $variant_match_options_ids, true)){
												$variantOptionData=$this->variant_options_ids[$variant['id']];
												$varOptionsIds=array_column($variantOptionData, 'id');
												$varOptionsOptionIds=array_column($variantOptionData, 'variant_option_id');
												$attr_value='';
												$base_attr_value='';
												$varOptionsIds=array();
												foreach ($variantOptionData as $vkey => $vvalue) {
														$variant_id=$variant_id;
								                    	$attr_value != "" && $attr_value .= ",";
														$attr_value .= $vvalue['variant_option_id'];
														$base_attr_value != "" && $base_attr_value .= "_";
							    						$base_attr_value .= $vvalue['variant_option_id'];
														$arr['OptionIds'] = $attr_value;
														$arr['color_name'] = $color_name;
														$arr['square_color'] = $square_color;
														$arr['base_attr_value'] = $base_attr_value;
														array_push($check_varient_id,$vvalue['variant_option_id']);
														$arr['check_varient_id'] = $check_varient_id;
							                    }

							                    $ProductVariantCountMultiple=(int) $this->webshop_obj->checkProductCountByVariantMultipleBaseColor($this->shopcode,$this->shopid,$variant_id,$varOptionsOptionIds,$categoryid,$this->customer_type_id);
							                    if($ProductVariantCountMultiple!=false && $ProductVariantCountMultiple>0){
							                    	$variantArray[] = $arr;
							                    }
										}
									}

								// end
							}*/
							 	
							//$this->base_color_listing=$variantArray;
							
							 	// print_r($variantArray);
							
							 	// exit();
							 // end base color list varient wise

						}

					}
				// end base color status check
			}
		// end color swatches


		
		$wishlistData = [];
		if (isset($customer_login_id) && $customer_login_id > 0) {
			$wishlistData = $this->webshop_obj->getWishlistCountForMultipleProducts($this->shopcode, $customer_login_id, $product_ids);
		}

		$this->specialPrices = array_key_by(
				$this->webshop_obj->getSpecialPricesForMultipleProducts($this->shopcode, array_merge($product_ids, $this->config_product_ids), $this->customer_type_id),
				'product_id'
		);

		$this->shopInventory =array_key_by(
				$this->webshop_obj->getAvailableInventoryForMultipleProducts(array_merge($product_ids, $this->config_product_ids), $this->shopcode),
				'product_id'
			);

			//print_r($this->shopInventory);

		// print_r($this->base_color_listing);
		//exit();
		
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
					$this->webshop_obj->getAvailableInventoryForMultipleProducts($shop_external_product_ids, 'shop'.$shop_id),
				);
			}
		}

		$this->externalInventory = array_key_by($this->externalInventory, 'product_id');

		//print_r($this->multiple_variant_bind);exit(); //working on

		foreach ($product_listing as $value) {
			if (isset($customer_login_id) && $customer_login_id > 0 && in_array($value['id'], $wishlistData, true)){
				$value['wishlist_status'] = 1;
			} else {
				$value['wishlist_status'] = 0;
			}

			if ($value['product_type'] === 'simple') {
				$value = $this->simpleProductStrategy($value); // Not used in shop3? Did not refactor this function.
			} else {
				$value = $this->advancedProductStrategy($value);
			}
			$final_arr[] = $value;
		}
			//exit(json_encode($error));

		//$final_arr = $this->sortProductsByPrice($options, $final_arr);


		if (empty($final_arr)) {
			if ($page > 1) {
				$message['statusCode'] = '200';
				$message['is_success'] = 'true';
				$message['message'] = 'No product!';
				$message['ProductList'] = [];
				$message['ProductListCount'] = $product_listing_count;
				exit(json_encode($message));
			}

			abort($error);
		}

		// Remove unused fields for smaller payload (would be better to exclude in query)
		// Temporary disable, to compare results
//		foreach($final_arr as &$product) {
//			unset($product['highlights']);
//			unset($product['description']);
//		}

		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'New product available';
		$message['ProductList'] = $final_arr;
		$message['ProductListCount'] = $product_listing_count;
		exit(json_encode($message));
	}

	private function simpleProductStrategy($value): array
	{
		if($this->isEuropeShop) {
			$value['eu_webshop_price'] = calculate_price_with_vat($value['price'], $this->vat_percent_session);
		}



		if ($value['product_inv_type'] === 'buy') {
			$product_inv = $this->webshop_obj->getAvailableInventory($value['id'], $this->shopcode, '', $value);
			if ($product_inv['available_qty'] > 0) {
				$value['stock_status'] = 'Instock';
				$value['qty'] = $product_inv['available_qty'];
			}
		} elseif ($value['product_inv_type'] === 'virtual' || $value['product_inv_type'] === 'dropship') {
			$seller_shopcode = 'shop' . $value['shop_id'];
			$product_inv1 = $this->webshop_obj->getAvailableInventory($value['id'], $this->shopcode, $seller_shopcode, $value);
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

		$qty = 0;
		$base_color_array=[];
		//echo count($configProduct);exit();
		if (count($configProduct) > 0) {
			foreach ($configProduct as $conf) {
				$eu_webshop_price = calculate_price_with_vat($conf['price'], $this->vat_percent_session);
				$eu_webshop_priceArray[] = $eu_webshop_price;

				$qty += $this->getAvailableQuantity($conf);
				if($this->base_color_status=='yes' && $this->getAvailableQuantity($conf) > 0){
					$getBaseValue=$this->multiple_variant_bind[$conf['parent_id']][$conf['id']];
					$base_color_array[$getBaseValue['color_name']]=$getBaseValue['square_color'];
				}else{
					unset($this->multiple_variant_bind[$value['parent_id']][$value['id']]);
				}
				
				$specialPriceArr = $this->specialPrices[$conf['id']] ?? false;
				if($specialPriceArr !== false){
					$flag = true;
					$arr['display_original'] = $specialPriceArr['display_original'];
					$arr['different_price'] = calculate_price_with_vat($specialPriceArr['special_price'], $this->vat_percent_session);
				}else{
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

		//print_r($value);exit();
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
