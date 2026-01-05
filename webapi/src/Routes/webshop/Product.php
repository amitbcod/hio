<?php

use App\Controllers\ProductListingController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/get_subscription_variants', function (Request $request, Response $response, $args) {

	$data = $request->getParsedBody();
	extract($data);
	$error = '';

	$webshop_obj = new DbProductFeature();
	$getSubsVariants = $webshop_obj->getSubscriptionVariants();

	if ($getSubsVariants == false) {
		$error = 'No Subscription variants found';
	}


	if ($error != '') {
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = $error;
		exit(json_encode($message));
	} else {
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Subscription variants available';
		$message['SubsVariantsDetails'] = $getSubsVariants;
		exit(json_encode($message));
	}
});


$app->post('/webshop/get_category_details', function (Request $request, Response $response, $args) {

	$data = $request->getParsedBody();
	extract($data);
	$error = '';
	if ($categoryslug == '') {
		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();
		if (isset($lang_code) && $lang_code != '') {
			$lang_code = $lang_code;
		} else {
			$lang_code = '';
		}
		$getCategoryDetails = $webshop_obj->getCategoryDetails($categoryslug, $lang_code);

		if ($getCategoryDetails == false) {
			$error = 'No category found';
		}
	}

	if ($error != '') {
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	} else {
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Category available';
		$message['CategoryDetails'] = $getCategoryDetails;
		exit(json_encode($message));
	}
});


$app->post('/webshop/get_product_category_by_level', function (Request $request, Response $response, $args) {
	$data = $request->getParsedBody();
	extract($data);
	$error = '';
	if ($product_id == '' || $cat_level == '') {
		$error = 'Please pass all the mandatory values';
	} else {
		$webshop_obj = new DbProductFeature();
		$getCategoryDetails = $webshop_obj->getProductCategoryByLevel($product_id, $cat_level);
		if ($getCategoryDetails == false) {
			$error = 'No category found';
		}
	}

	if ($error != '') {
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	} else {
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Category available';
		$message['CategoryDetails'] = $getCategoryDetails;
		exit(json_encode($message));
	}
});



$app->post('/webshop/get_product_categorys', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();



	extract($data);



	$error = '';

	if ($product_id == '') {

		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();



		$getCategoryIds = $webshop_obj->getProductCategorys($product_id);



		if ($getCategoryIds == false) {

			$error = 'No category found';
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Category available';

		$message['CategoryIds'] = $getCategoryIds;

		exit(json_encode($message));
	}
});





$app->post('/webshop/product_listing', ProductListingController::class);



$app->post('/webshop/product_listing_count', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	$product_listing_count = 0;

	if ($shopcode == '' || $shopid == '') {

		$error = 'Please pass all the mandatory values';
	} else {
		$final_arr = array();
		$page = (isset($page) ? $page : 0);

		$page_size = (isset($page_size) ? $page_size : 0);

		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);

		$options = (isset($options) ? $options : '');

		$gender = (isset($gender) ? $gender : array());

		$categoryid = (isset($categoryid) ? $categoryid : '');

		$search_term = (isset($search_term) ? $search_term : '');

		$price_range = (isset($price_range) ? $price_range : array());

		$variant_id_arr = (isset($variant_id_arr) ? $variant_id_arr : array());

		$variant_attr_value_arr = (isset($variant_attr_value_arr) ? $variant_attr_value_arr : array());

		$attribute_arr = (isset($attribute_arr) ? $attribute_arr : array());

		$webshop_obj = new DbProductFeature();

		$product_listing_count = $webshop_obj->productListingCount($shopcode, $shopid, $categoryid, $options, $customer_type_id, $gender, $price_range, $variant_id_arr, $variant_attr_value_arr, $attribute_arr, $search_term);

		if ($product_listing_count != false) {

			$product_listing_count = $product_listing_count;
		} else {

			$product_listing_count = 0;
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {



		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Product available';

		$message['ProductListCount'] = $product_listing_count;

		exit(json_encode($message));
	}
});



$app->post('/webshop/browse_by_category', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($shopcode == '' || $shopid == '') {

		$error = 'Please pass all the mandatory values';
	} else {



		$final_arr = array();

		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);



		$webshop_obj = new DbProductFeature();

		$browseByCategory = $webshop_obj->mainCategory($shopcode, $shopid, $customer_type_id);



		if ($browseByCategory == false) {

			$error = 'No category found';
		} else {

			foreach ($browseByCategory as $cat) {

				$firstLevelCategory = $webshop_obj->firstLevelCategory($shopcode, $shopid, $cat['id']);



				if ($firstLevelCategory != false) {

					foreach ($firstLevelCategory as $cat1) {

						$secondLevelCategory = $webshop_obj->secondLevelCategory($shopcode, $shopid, $cat1['id'], $cat['id']);

						if ($secondLevelCategory != false) {

							foreach ($secondLevelCategory as $cat2) {

								$arr2['category_id'] = $cat2['category_id'];

								$arr2['id'] = $cat2['id'];

								$arr2['cat_name'] = $cat2['cat_name'];

								$arr2['slug'] = $cat2['slug'];

								$arr2['cat_description'] = $cat2['cat_description'];

								$arr2['parent_id'] = $cat2['parent_id'];

								$arr2['main_parent_id'] = $cat2['main_parent_id'];

								$arr2['cat_level'] = $cat2['cat_level'];

								$arr2['shop_id'] = $cat2['shop_id'];



								$cat1['cat_level_2'][] = $arr2;
							}
						}



						$cat['cat_level_1'][] = $cat1;
					}
				}



				$final_arr[] = $cat;
			}
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Category available';

		$message['AllCategoryLevels'] = $final_arr;

		exit(json_encode($message));
	}
});



$app->post('/webshop/product_detail', function (Request $request, Response $response, $args) {
	$data = $request->getParsedBody();
	extract($data);
	$error = '';
	if (empty($product_url_key)) {
		abort('Please pass all the mandatory values');
	}
	$webshop_obj = new DbProductFeature();
	$rating_obj = new DbProductReviewFeature();
	$comn_obj = new DbCommonFeature();
	$wishlist_obj = new DbWishlistFeature();
	$cart_obj = new DbCart();
	$productData = $webshop_obj->productDetails($product_url_key);
	
	if ($productData == false) {
		abort('Product not available');
	}

	if ($productData['product_type'] === 'simple') {

		$product_inv = $webshop_obj->getAvailableInventory($productData['id']);
		if ($product_inv['available_qty'] > 0) {
			$productData['stock_status'] = 'Instock';
			$productData['total_qty'] = $product_inv['available_qty'];
		} else {
			$productData['stock_status'] = 'OutofStock';
			$productData['total_qty'] = $product_inv['available_qty'];
		}
		$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0;
		$estimate_delivery_time = $ProDelTime1;

		$productData['estimate_delivery_time'] = $estimate_delivery_time;
		$specialPriceArr = $webshop_obj->getSpecialPrices($productData['id']);

		$percent_off =  0;

		
		if ($specialPriceArr != false) {
			$productData['special_price'] = $specialPriceArr['special_price'];
			$productData['special_price_from'] = $specialPriceArr['special_price_from'];
			$productData['special_price_to'] = $specialPriceArr['special_price_to'];
			$productData['display_original'] = $specialPriceArr['display_original'];
			$cal1 = ($productData['webshop_price'] - $productData['special_price']) / $productData['webshop_price'];
			$percent_off = round($cal1 * 100);
		}


		$productData['least_price'] = $productData['webshop_price'];
		$product_gift_master = $webshop_obj->getGiftMaster($productData['gift_id']);
	
		if($product_gift_master){
			$productData['gift_master_name'] = $product_gift_master['name'];
		}
		else{
			$productData['gift_master_name'] = "NA";
		}
		
		$productData['off_percent_price'] = $percent_off;
		

	} else if ($productData['product_type'] === 'bundle') {
		$notAvailable = '';

		$bundleProducts = $webshop_obj->bundleProduct($productData['id']);
		$productData['least_price'] = $productData['webshop_price'];
		$qty = '0';
		$quantity = 0;
		$configChildProduct = array();
		$configChildProductNotInv = array(); // inv
		$estcount = 0;
		$bundle_child_NotInv = 0;
		$bundle_childProductNotInv = array();
		$bundle_childName = array();
		$bundle_child_ids = array_column($bundleProducts, 'product_id');
		$BundleProductChildDetails = $webshop_obj->getproductStatusById($bundle_child_ids);


		foreach ($bundleProducts as $value) {
			$arr_filter_childs = array_values(array_filter($BundleProductChildDetails, function ($child) use ($value) {
				return $child['id'] === $value['product_id'];
			}));

			$bundle_childName[] = $arr_filter_childs[0]['name'];
			if ($arr_filter_childs[0]['status'] == 1 && $arr_filter_childs[0]['remove_flag'] == 0) {
				$main_prd_id = ($value['product_parent_id'] > 0) ? $value['product_parent_id'] : $value['product_id'];



				if ($value['product_type'] == 'configurable') {
					$configChildNotInv = array();
					$configProduct = $webshop_obj->configurableProduct($value['product_id']);
					$buyInventoryIds = array_column(array_filter($configProduct, function ($sku) {
						return $sku['product_inv_type'] === 'buy';
					}), 'id');


					$buyInventory = [];
					if (!empty($buyInventoryIds)) {


						$buyInventory = $webshop_obj->getAvailableInventoryForMultipleProducts($buyInventoryIds);
						$buyInventory = array_combine(array_column($buyInventory, 'product_id'), $buyInventory);
					}



					foreach ($configProduct as $confsimple) {

						$product_inv = $buyInventory[$confsimple['id']];
						if (is_numeric($product_inv['available_qty']) && $product_inv['available_qty'] > 0 && $product_inv['available_qty'] >= $value['default_qty']) {
							$qty += $product_inv['available_qty'];
							$quantity = $product_inv['available_qty'];
						} else {
							$configChildNotInv[] = $confsimple['id'];
						}
					}

					$product_variants = ((isset($value['variant_options']) && $value['variant_options'] != '') ? json_decode($value['variant_options']) : "");

					$attr_id = array();
					$attr_value = array();
					$prod_selected_ids = array();
					if ($product_variants != '') {

						foreach ($product_variants as $key => $val) {
							$attr_id[] = $key;
							$attr_value[] = $val;
						}
						//print_r($attr_id);'   '.print_r($attr_value);exit;
						if (count($attr_id) > 0 && count($attr_value) > 0) {

							$selected_variant_count = count((array)$product_variants);
							$VariantOptionsSelected = $webshop_obj->getVariantOptionsSelected($main_prd_id, $attr_id, $attr_value, $selected_variant_count);

							if (!empty($VariantOptionsSelected) && count($VariantOptionsSelected) > 0) {
								foreach ($VariantOptionsSelected as $id) {
									$prod_selected_ids[] = $id['product_id'];
								}
							}
						}
					}


					$productDetails = $wishlist_obj->getproductDetailsById($main_prd_id);
					$arr['bundle_child_id'] = $value['id'];
					$arr['id'] = $value['product_id'];
					$arr['product_type'] = $value['product_type'];
					$arr['name'] = $productDetails['name'];
					$arr['webshop_price'] = $value['webshop_price'];
					$arr['parent_id'] = $value['product_parent_id'];
					$arr['qty'] = $quantity;
					$arr['default_qty'] = $value['default_qty'];
					$variants_arr = array();
					$variantProduct = $webshop_obj->configProductVariant($main_prd_id);
					if ($variantProduct != false) {
						$childProductsNotStock = '';
						if ($configChildNotInv) {
							$childProductsNotStock = implode(",", $configChildNotInv);
						}

						$int = 0;
						foreach ($variantProduct as $variant) {

							$variantOption = array();

							if (empty($prod_selected_ids)) {

								$variantOption = $webshop_obj->productVariantOptionsInstockNewQuery($main_prd_id, $variant['id'], '');
								$variantOption['check_aaa'] = $int;
							} else {

								$variantOption = $webshop_obj->productVariantOptionsFilterByProductIds($main_prd_id, $prod_selected_ids, $variant['id'], $childProductsNotStock);
								$variantOption['check_aaa'] = $int;
							}
							$varr['variant_id_'] = $variant['id'];
							$varr['variant_name'] = $variant['attr_name'];
							$varr['variant_code'] = $variant['attr_code'];
							$varr['variant_options'] = $variantOption;
							$variants_arr[] = $varr;


							if (empty($variantOption)) {

								$notAvailable = "Not Available";
							}
							$int++;
						}
					}



					$arr['product_variants'] = $variants_arr;
					$configChildProduct[] = $arr;
				} else {
					$quantity = 0;
					$arrSim = [];
					$product_inv =  $webshop_obj->getAvailableInventory($value['product_id']);
					if (is_numeric($product_inv['available_qty']) && $product_inv['available_qty'] > 0) {
						$qty += $product_inv['available_qty'];
						$quantity = $product_inv['available_qty'];
						if ($estcount == 0) {

							$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0;
							$estimate_delivery_time = $ProDelTime1;
							$arrSim['estimate_delivery_time'] = $estimate_delivery_time;
							$estcount = $estcount + 1;
						}
					} else { // stock check

						$configChildProductNotInv[] = $value['product_id'];
					}



					// if ($product_inv['available_qty'] >= $value['default_qty']) {
					// 	$notAvailable = "Not Available";
					// }

					$productDetails = $wishlist_obj->getproductDetailsById($main_prd_id);

					$arrSim['bundle_child_id'] = $value['id'];
					$arrSim['id'] = $value['product_id'];
					$arrSim['name'] = $productDetails['name'];
					$arrSim['product_type'] = $value['product_type'];
					$arrSim['webshop_price'] = $value['webshop_price'];
					$arrSim['parent_id'] = $value['product_parent_id'];
					$arrSim['qty'] = $quantity;
					if ($quantity < $value['default_qty']) {
						$notAvailable = "Not Available";
					}
					$arrSim['default_qty'] = $value['default_qty'];
					if ($productDetails['product_type'] == 'configurable') {

						$VariantInfo = $cart_obj->get_product_variant_details($value['product_parent_id'], $value['product_id']);
						$product_variants_arr = array();
						$product_variants_str = '';
						if (is_array($VariantInfo) && count($VariantInfo) > 0  && $VariantInfo != false) {
							foreach ($VariantInfo  as $value) {
								$attr_id = $value['attr_id'];
								$attr_value = $value['attr_value'];
								$AttrData = $cart_obj->getAttributeDetails($attr_id);
								if ($AttrData == false) {

									$attr_name = '';
								} else {
									$attr_name = $AttrData['attr_name'];
								}
								$AttrOptionData = $cart_obj->getAttributeOptionDetails($attr_value);
								if ($AttrOptionData == false) {
									$attr_option_name = '';
								} else {

									$attr_option_name = $AttrOptionData['attr_options_name'];
								}
								if ($attr_name != '' && $attr_option_name != '') {
									$product_variants_arr[] = array($attr_name => $attr_option_name);
								}
							}
						}
						if (isset($product_variants_arr) && count($product_variants_arr) > 0) {
							$product_variants_str = json_encode($product_variants_arr);
						} else {
							$product_variants_str = '';
						}


						$config_simple_details = $wishlist_obj->getproductDetailsById($value['product_id']);
						//print_r($config_simple_details);exit;
						$specialPriceArr1 = $webshop_obj->getSpecialPrices($config_simple_details['id']);
						if ($specialPriceArr1 != false) {
							$arrSim['special_price'] = $specialPriceArr1['special_price'];
							$arrSim['special_price_from'] = $specialPriceArr1['special_price_from'];
							$arrSim['special_price_to'] = $specialPriceArr1['special_price_to'];
							$arrSim['display_original'] = $specialPriceArr1['display_original'];
						}
						$arrSim['sub_issues'] = $config_simple_details['sub_issues'];
						$product_gift_master = $webshop_obj->getGiftMaster($config_simple_details['gift_id']);
						$arrSim['gift_master_name'] = $product_gift_master['name'];
						$arrSim['cost_price'] = $config_simple_details['cost_price'];
						$arrSim['variants'] = $product_variants_str;
					} else {
						$specialPriceArr1 = $webshop_obj->getSpecialPrices($productDetails['id']);
						if ($specialPriceArr1 != false) {
							$arrSim['special_price'] = $specialPriceArr1['special_price'];
							$arrSim['special_price_from'] = $specialPriceArr1['special_price_from'];
							$arrSim['special_price_to'] = $specialPriceArr1['special_price_to'];
							$arrSim['display_original'] = $specialPriceArr1['display_original'];
						}
						$arrSim['sub_issues'] = $productDetails['sub_issues'];
						$product_gift_master = $webshop_obj->getGiftMaster($productDetails['gift_id']);
						$arrSim['gift_master_name'] = $product_gift_master['name'];
						$arrSim['cost_price'] = $productDetails['cost_price'];

						// if($productDetails['product_type'] == 'simple'){

						// }
						$arrSim['variants'] = '';
					}
					$configChildProduct[] = $arrSim;
				}
			} else {
				$bundle_child_NotInv = 1;
				$bundle_childProductNotInv[] = $arr_filter_childs[0]['name'];
			}
		}


		if (($qty > 0) && $bundle_child_NotInv == 0 && $notAvailable == '') { // check stock

			$productData['stock_status'] = 'Instock';

			$productData['total_qty'] = $qty;
		} else { // else check stock

			$productData['stock_status'] = 'Notavailable';

			$productData['total_qty'] = 0;
		}

		$productData['childProducts'] = $configChildProduct;  // Store child product

		$productData['bundle_childProduct_out_of_stock'] = $bundle_childProductNotInv;
		$productData['bundle_childProduct_all'] = $bundle_childName;
	} else {


		$specialPriceArray = array();

		$eu_webshop_priceArray = array();

		$flag = false;
		$configProduct = $webshop_obj->configurableProduct($productData['id']);
		
		$qty = '0';

		$quantity = 0;

		$configChildProduct = array();

		$configChildProductNotInv = array(); // inv
		
		$estcount = 0;
		
		$buyInventoryIds = array_column(array_filter($configProduct, function ($sku) {
			
			return $sku['product_inv_type'] === 'buy';
		}), 'id');
		
		
		$buyInventory = [];
		
		if (!empty($buyInventoryIds)) {
			
			$buyInventory = $webshop_obj->getAvailableInventoryForMultipleProducts($buyInventoryIds);
			
			$buyInventory = array_combine(array_column($buyInventory, 'product_id'), $buyInventory);
		}
		
		
		
		$allSpecialPrices = $webshop_obj->getSpecialPricesForMultipleProducts(array_column($configProduct, 'id'));
		
		$allSpecialPrices = array_combine(array_column($allSpecialPrices, 'product_id'), $allSpecialPrices);
		
		foreach ($configProduct as $value) {
			
			
			$gift_master = $webshop_obj->getGiftMaster($value['gift_id']);
		
			

			$product_inv = $buyInventory[$value['id']];
			if (is_numeric($product_inv['available_qty']) && $product_inv['available_qty'] > 0) {

				$qty += $product_inv['available_qty'];

				$quantity = $product_inv['available_qty'];

				//Estimated Delivery for Simple (Configurable)

				if ($estcount == 0) {
					$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0;

					$estimate_delivery_time = $ProDelTime1;

					$productData['estimate_delivery_time'] = $estimate_delivery_time;
					$estcount = $estcount + 1;
				}
			} else { // stock check

				$configChildProductNotInv[] = $value['id'];
			}



			$specialPrice = $allSpecialPrices[$value['id']] ?? null;
	

			$percent_off =  0;

			if (!empty($specialPrice)) {

				$flag = true;

				$arr1['display_original'] = $specialPrice['display_original'];

				$arr1['different_price'] = $specialPrice['special_price'];

				$cal1 = ($value['webshop_price'] - $arr1['different_price']) / $value['webshop_price'];
				$percent_off = round($cal1 * 100);
			} else {

				$arr1['different_price'] = "";
				$arr1['display_original'] = '';
			}

			$specialPriceArray[] = $arr1;

			$arr['id'] = $value['id'];

			$arr['sku'] = $value['sku'];

			$arr['barcode'] = $value['barcode'];

			$arr['webshop_price'] = $value['webshop_price'];

			$arr['cost_price'] = $value['cost_price'];

			$arr['parent_id'] = $value['parent_id'];

			$arr['display_original'] = $arr1['display_original'];

			$arr['special_price'] = $arr1['different_price'];

	
			$arr['qty'] = $quantity;

			if ($gift_master !== false && isset($gift_master['name'])) {
				$arr['gift_master_name'] = $gift_master['name'];
			} else {
				$arr['gift_master_name'] = ''; // or null or a default message
			}
			$arr['sub_issues'] = $value['sub_issues'];
			
			$arr['off_percent_price'] = $percent_off;
			
			$variantConfigProduct = $webshop_obj->variantConfigProduct($value['id']);
			


			if ($variantConfigProduct != false) {

				$childProductsNotStock = '';

				if ($configChildProductNotInv) {

					$childProductsNotStock = implode(",", $configChildProductNotInv);
				}
				// print_R($variantConfigProduct);
				$arr['variant_options'] = array();
				$variantOption = array();
				foreach ($variantConfigProduct as $variant) {


					$variantOption = $webshop_obj->ConfigproductVariantOptionsInstockNewQuery($productData['id'], $variant['id'], $value['id'], $childProductsNotStock);
					// print_R($variant);
					if (is_array($variantOption)) {
						foreach ($variantOption as $var_child) {
							// $variantOption_con =
							// $arrayVariant_con = array();
							$specialPrice = $allSpecialPrices[$var_child['product_id']] ?? null;

							$percent_off =  0;

							if (!empty($specialPrice)) {

								$var_child['display_original'] = $specialPrice['display_original'];

								$var_child['special_price'] = $specialPrice['special_price'];

								$cal1 = ($var_child['webshop_price'] - $var_child['special_price']) / $var_child['webshop_price'];
								$percent_off = round($cal1 * 100);
							} else {

								$var_child['special_price'] = "";
								$var_child['display_original'] = '';
							}


							$var_child['off_percent_price'] =  $percent_off;

							$arrayVariant_con[$value['id']][$variant['id']][] = $var_child;
						}
					}
					$arr['variant_options'][] =  $variantOption;
				}
			}
			// print_R($arrayVariant_con);
			// $arr['variant_option'] = $arrayVariant_con;

			$configChildProduct[] = $arr;
		}
		// echo "<pre>";
		// print_r($configChildProduct);die;

		// define variable
		$variants_arr = array();
		$minPrice = $webshop_obj->getMinPrice($productData['id']);

		$productData['least_price'] = $minPrice['min_price'];

		$productData['webshop_price'] = $minPrice['min_price'];


		


		if ($qty > 0) { // check stock




			if ($flag == true) {

				$prices = array_column($specialPriceArray, 'different_price');

				$display_original = array_values(array_filter(array_column($specialPriceArray, 'display_original')));


				$productData['special_price'] = min($prices);

				$productData['special_min_price'] = min($prices);

				$productData['special_max_price'] = max($prices);

				$productData['display_original'] = $display_original[0];
			}

			$productData['stock_status'] = 'Instock';

			$productData['total_qty'] = $qty;

			// print_r($configProduct);
			// die();

			$variantProduct = $webshop_obj->configProductVariant($productData['id']);
			// print_R($variantProduct);
			// die();

			
			if ($variantProduct != false) {
				
				$childProductsNotStock = '';
				
				if ($configChildProductNotInv) {
					
					$childProductsNotStock = implode(",", $configChildProductNotInv);
				}
				
				foreach ($variantProduct as $variant) {
					$varr['variant_name'] = $variant['attr_name'];
					$varr['attr_id'] = $variant['id'];
					$varr['variant_code'] = $variant['attr_code'];
					// $variantOption = array();
					$variantOption = $webshop_obj->productVariantOptionsInstockNewQuery($productData['id'], $variant['id'], $childProductsNotStock);
					

					// print_r($variantOption);
					// die();

					if (is_array($variantOption)) {
						foreach ($variantOption as $var_child) {


							// $arrayVariant = array();
							$specialPrice = $allSpecialPrices[$var_child['product_id']] ?? null;

							$percent_off =  0;

							if (!empty($specialPrice)) {

								$var_child['display_original'] = $specialPrice['display_original'];

								$var_child['special_price'] = $specialPrice['special_price'];

								$cal1 = ($var_child['webshop_price'] - $var_child['special_price']) / $var_child['webshop_price'];
								$percent_off = round($cal1 * 100);
							} else {

								$var_child['special_price'] = "";
								$var_child['display_original'] = '';
							}


							$var_child['off_percent_price'] =  $percent_off;

							$arrayVariant[] = $var_child;
						}
					}




					// $varr['variant_options'] = $arrayVariant;
					$varr['variant_options'] = (isset($arrayVariant) && is_array($arrayVariant) ? $arrayVariant : '');
					
					$variants_arr[] = $varr;
				}
			}
		} else { // else check stock

			$productData['stock_status'] = 'Notavailable';

			$productData['total_qty'] = 0;
		}

		$productData['childProducts'] = $configChildProduct;  // Store child product

		$productData['product_variants'] = $variants_arr;  // store variants  // store variants

	}

	

	$rating_arr = array();

	if ($productData['product_reviews_code'] == '') {

		$ratingData = $rating_obj->getProductRatings($productData['id']);

		if ($ratingData != false) {

			$average = ($ratingData['ratings'] / $ratingData['total_rating_count']);

			$average_rating = round($average, 1);

			$rat_arr['average_rating_start'] = $average_rating;

			$rat_arr['total_rating_count'] = $ratingData['total_rating_count'];
		} else {

			$rat_arr['average_rating_start'] = 0;

			$rat_arr['total_rating_count'] = 0;
		}
		$rating_arr[] = $rat_arr;
	} else {

		$rating_star = 0;

		$rating_count = 0;

		$productIds = $rating_obj->productidsByReviewCode($productData['id']);

		if ($productIds != false) {

			foreach ($productIds as $ids) {

				$ratingData = $rating_obj->getProductRatings($ids['id']);
				if ($ratingData != false) {

					$rating_star += $ratingData['ratings'];

					$rating_count += $ratingData['total_rating_count'];
				}
			}

			if ($rating_star > 0 && $rating_count > 0) {

				$average = ($rating_star / $rating_count);

				$average_rating = round($average, 1);

				$rat_arr['average_rating_start'] = $average_rating;

				$rat_arr['total_rating_count'] = $rating_count;
			} else {

				$rat_arr['average_rating_start'] = 0;

				$rat_arr['total_rating_count'] = $rating_count;
			}

			$rating_arr[] = $rat_arr;
		}
	}
	



	$attributData = $webshop_obj->attributDetails($productData['id']);
	$mediaGalleryData = $webshop_obj->mediaGallery($productData['id']);
	$AttributesWithOptions = $webshop_obj->attributeDropdownForProductDetail($productData['id']);

	$productData['specification'] = $attributData;

	$productData['mediaGallery'] = (isset($mediaGalleryData) && $mediaGalleryData != false) ? $mediaGalleryData : array();

	$productData['ratingDetails'] = $rating_arr;

	$productData['AttributesWithOptions'] = $AttributesWithOptions;

	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Product Details Available';

		$message['ProductData'] = $productData;

		exit(json_encode($message));
	}
});





$app->post('/webshop/get_conf_simprod_by_variants', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$ConfigSimpleResponse = array();



	$error = '';

	if ($product_id == '' || $total_variant == '' || $selected_variant == '') {

		$error = 'Please pass all the mandatory values';
	} else {



		$webshop_obj = new DbProductFeature();



		$customer_type_id = $customer_type_id ?? 1;


		$product_exists = $webshop_obj->productExists($product_id);



		if (!$product_exists) {

			abort('No Product found');
		}



		if ($total_variant > 0) {

			foreach ($selected_variant as $values) {

				if (!isset($values['attr_value'])) {

					abort('Not all values selected');
				}
			}



			$conf_simple_product_id = "";

			$parent_id = $product_id;



			if ($total_variant == 1) {

				$attr_id = $selected_variant[0]['attr_id'];

				$attr_value = $selected_variant[0]['attr_value'];



				$cof_simple = $webshop_obj->getsimplevariantproducts($parent_id, $attr_id, $attr_value);



				$cof_simple = $cof_simple[0];

				if (isset($cof_simple) && $cof_simple['id'] != '') {

					$conf_simple_product_id = $cof_simple['product_id'];
				}
			} else if ($total_variant > 1) {

				$attr_id = $selected_variant[0]['attr_id'];

				$attr_value = $selected_variant[0]['attr_value'];



				$second_attr_id = $selected_variant[1]['attr_id'];

				$second_attr_value = $selected_variant[1]['attr_value'];



				if ($total_variant > 2) {

					$third_attr_id = $selected_variant[2]['attr_id'];

					$third_attr_value = $selected_variant[2]['attr_value'];
				}



				if ($total_variant > 3) {

					$fourth_attr_id = $selected_variant[3]['attr_id'];

					$fourth_attr_value = $selected_variant[3]['attr_value'];
				}



				$conf_simple_products  = $webshop_obj->getsimplevariantproducts($parent_id, $attr_id, $attr_value);



				if (isset($conf_simple_products) && count($conf_simple_products) > 0) {

					foreach ($conf_simple_products as $conf_simple) {

						$temp_product_id = $conf_simple['product_id'];

						if ($total_variant == 2) {

							$second_conf_simple = $webshop_obj->simplevariantproductexistbyattrid($parent_id, $second_attr_id, $second_attr_value, $temp_product_id);



							if (isset($second_conf_simple) && $second_conf_simple['count'] > 0) {

								$conf_simple_product_id = $temp_product_id;

								break;
							}
						} else if ($total_variant == 3) {

							$third_conf_simple = $webshop_obj->simplevariantproductexistbyattrid($parent_id, $third_attr_id, $third_attr_value, $temp_product_id);



							if (isset($third_conf_simple) && $third_conf_simple['count'] > 0) {

								$conf_simple_product_id = $temp_product_id;

								break;
							}
						} else if ($total_variant == 4) {

							$fourth_conf_simple = $webshop_obj->simplevariantproductexistbyattrid($parent_id, $fourth_attr_id, $fourth_attr_value, $temp_product_id);



							if (isset($fourth_conf_simple) && $fourth_conf_simple['count'] > 0) {

								$conf_simple_product_id = $temp_product_id;

								break;
							}
						}
					}
				} else {

					$conf_simple_product_id = '';
				}
			}




			if (isset($conf_simple_product_id)  && $conf_simple_product_id != '' && $conf_simple_product_id > 0) {
				$quantity = 0;
				$ConfSimpleData = $webshop_obj->getproductDetailsById($conf_simple_product_id);


				$specialPriceArr = $webshop_obj->getSpecialPrices($conf_simple_product_id);



				$ConfigSimpleResponse['conf_simple_pro_id'] = $ConfSimpleData['id'];

				$ConfigSimpleResponse['conf_simple_pro_pice'] = $ConfSimpleData['webshop_price'];


				$ConfigSimpleResponse['conf_simple_pro_sku'] = $ConfSimpleData['sku'];

				$ConfigSimpleResponse['conf_simple_pro_barcode'] = $ConfSimpleData['barcode'];



				if ($specialPriceArr != false) {

					$ConfigSimpleResponse['special_price'] = $specialPriceArr['special_price'];


					$ConfigSimpleResponse['special_price_from'] = $specialPriceArr['special_price_from'];

					$ConfigSimpleResponse['special_price_to'] = $specialPriceArr['special_price_to'];

					$ConfigSimpleResponse['display_original'] = $specialPriceArr['display_original'];
				} else {

					$ConfigSimpleResponse['special_price'] = '';

					$ConfigSimpleResponse['special_price_from'] = '';

					$ConfigSimpleResponse['special_price_to'] = '';

					$ConfigSimpleResponse['display_original'] = '';
				}



				$product_inv = $webshop_obj->getAvailableInventory($ConfSimpleData['id']);

				if (is_numeric($product_inv['available_qty']) && $product_inv['available_qty'] > 0) {

					$quantity = $product_inv['available_qty'];
				}





				// if($prelaunch === 'yes' && $quantity== 0){

				// 	$qty_identifier='product_detail_page_max_qty';

				// 	$webshop_obj = new DbGlobalFeature();

				// 	$get_custom_variable = $webshop_obj->get_custom_variable($shopcode,$qty_identifier);



				// 	if(!empty($get_custom_variable)){

				// 		$ConfigSimpleResponse['conf_simple_pro_inventory']['qty']=$get_custom_variable['value'];

				// 	}else{

				// 		$ConfigSimpleResponse['conf_simple_pro_inventory']['qty'] = 50;

				// 	}

				// }else{
				// $ConfigSimpleResponse['conf_simple_pro_inventory']['qty']=$quantity;


				// }

				$ConfigSimpleResponse['conf_simple_pro_inventory']['qty'] = $quantity;


				if ($quantity > 0) {

					$ConfigSimpleResponse['conf_simple_pro_inventory']['status'] = 'instock';
				} else {

					$ConfigSimpleResponse['conf_simple_pro_inventory']['status'] = 'outofstock';
				}
			} else {
				$error = 'Product not available';
				$ConfigSimpleResponse['conf_simple_pro_inventory']['status'] = 'notavailable';
			}
		}
	}



	if (!empty($error)) {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		$message['ConfigSimpleDetails'] = $ConfigSimpleResponse;

		exit(json_encode($message));
	}



	$message['statusCode'] = '200';

	$message['is_success'] = 'true';

	$message['message'] = 'Conf Simple Product available';

	$message['ConfigSimpleDetails'] = $ConfigSimpleResponse;

	exit(json_encode($message));
});

$app->post('/webshop/get_conf_simprod_by_variants_new', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$ConfigSimpleResponse = array();



	$error = '';

	if ($product_id == '' || $parent_id == '') {

		$error = 'Please pass all the mandatory values';
	} else {



		$webshop_obj = new DbProductFeature();



		$customer_type_id = $customer_type_id ?? 1;


		$product_exists = $webshop_obj->productExists($parent_id);


		if (!$product_exists) {

			abort('No Product found');
		}
		$conf_simple_product_id = $product_id;

		if (isset($conf_simple_product_id)  && $conf_simple_product_id != '' && $conf_simple_product_id > 0) {
			$quantity = 0;
			$ConfSimpleData = $webshop_obj->getproductDetailsById($conf_simple_product_id);


			$specialPriceArr = $webshop_obj->getSpecialPrices($conf_simple_product_id);



			$ConfigSimpleResponse['conf_simple_pro_id'] = $ConfSimpleData['id'];

			$ConfigSimpleResponse['conf_simple_pro_pice'] = $ConfSimpleData['webshop_price'];


			$ConfigSimpleResponse['conf_simple_pro_sku'] = $ConfSimpleData['sku'];

			$ConfigSimpleResponse['conf_simple_pro_barcode'] = $ConfSimpleData['barcode'];



			if ($specialPriceArr != false) {

				$ConfigSimpleResponse['special_price'] = $specialPriceArr['special_price'];


				$ConfigSimpleResponse['special_price_from'] = $specialPriceArr['special_price_from'];

				$ConfigSimpleResponse['special_price_to'] = $specialPriceArr['special_price_to'];

				$ConfigSimpleResponse['display_original'] = $specialPriceArr['display_original'];
			} else {

				$ConfigSimpleResponse['special_price'] = '';

				$ConfigSimpleResponse['special_price_from'] = '';

				$ConfigSimpleResponse['special_price_to'] = '';

				$ConfigSimpleResponse['display_original'] = '';
			}



			$product_inv = $webshop_obj->getAvailableInventory($ConfSimpleData['id']);

			if (is_numeric($product_inv['available_qty']) && $product_inv['available_qty'] > 0) {

				$quantity = $product_inv['available_qty'];
			}





			// if($prelaunch === 'yes' && $quantity== 0){

			// 	$qty_identifier='product_detail_page_max_qty';

			// 	$webshop_obj = new DbGlobalFeature();

			// 	$get_custom_variable = $webshop_obj->get_custom_variable($shopcode,$qty_identifier);



			// 	if(!empty($get_custom_variable)){

			// 		$ConfigSimpleResponse['conf_simple_pro_inventory']['qty']=$get_custom_variable['value'];

			// 	}else{

			// 		$ConfigSimpleResponse['conf_simple_pro_inventory']['qty'] = 50;

			// 	}

			// }else{
			// $ConfigSimpleResponse['conf_simple_pro_inventory']['qty']=$quantity;


			// }

			$ConfigSimpleResponse['conf_simple_pro_inventory']['qty'] = $quantity;


			if ($quantity > 0) {

				$ConfigSimpleResponse['conf_simple_pro_inventory']['status'] = 'instock';
			} else {

				$ConfigSimpleResponse['conf_simple_pro_inventory']['status'] = 'outofstock';
			}
		} else {
			$error = 'Product not available';
			$ConfigSimpleResponse['conf_simple_pro_inventory']['status'] = 'notavailable';
		}
	}

	if (!empty($error)) {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		$message['ConfigSimpleDetails'] = $ConfigSimpleResponse;

		exit(json_encode($message));
	}



	$message['statusCode'] = '200';

	$message['is_success'] = 'true';

	$message['message'] = 'Conf Simple Product available';

	$message['ConfigSimpleDetails'] = $ConfigSimpleResponse;

	exit(json_encode($message));
});

$app->post('/webshop/get_gallery_images_by_variants', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if (empty($shopcode) || empty($shopid) || empty($product_id)) {

		$error = 'Please pass all the mandatory values';
	} else {



		$final_arr = array();

		$attr_option_value = $attr_option_value ?? '';

		$media_variant_id = $media_variant_id ?? '';

		$child_product_id  = $child_product_id ?? '';



		$webshop_obj = new DbProductFeature();

		$mediaGalleryData = $webshop_obj->getmediaGalleryByVariants($shopcode, $shopid, $product_id, $child_product_id, $attr_option_value, $media_variant_id);



		$productData = $webshop_obj->getproductDetailsById($product_id);



		$final_arr['mediaGallery'] = $mediaGallery = (isset($mediaGalleryData) && $mediaGalleryData != false) ? $mediaGalleryData : array();

		$final_arr['name'] = $productData['name'];

		if ($media_variant_id > 0) {

			$mediaGalleryBaseImg = $webshop_obj->getmediaGalleryVariantsBaseImage($shopcode, $product_id, $attr_option_value);

			$final_arr['base_image'] = $mediaGalleryBaseImg;
		} else {

			$final_arr['base_image'] = $productData['base_image'];
		}

		$final_arr['media_count'] = count($mediaGallery);
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Product Images Available';

		$message['MediaGalleryData'] = $final_arr;

		exit(json_encode($message));
	}
});



$app->post('/webshop/getProductSku', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($term == '') {

		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();

		$products = $webshop_obj->getProductSku($term);



		if ($products == false) {

			$error = 'No Products found';
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Products available';

		$message['products'] = $products;

		exit(json_encode($message));
	}
});



$app->post('/webshop/getAvailableProducts', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);

	$error = '';

	if (($barcode == '' && $sku == '')  || ($qty == '' || $qty == 0)) {

		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();

		$AvailableProducts = $webshop_obj->CheckProductsAvailable($barcode, $sku);



		if ($AvailableProducts == false) {

			$error = 'No products found';
		}
	}

	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Products available';

		$message['AvailableProducts'] = $AvailableProducts;

		exit(json_encode($message));
	}
});



$app->post('/webshop/insert_scanned_products', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($shopcode == '' || $shopid == '' || $customer_id == '' || $customer_id == 0 || $parent_id == '' || $product_id == '') {

		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();

		$cart_obj = new DbCart();



		$VariantInfo = $cart_obj->get_product_variant_details($shopcode, $parent_id, $product_id);



		$product_variants_arr = array();

		$product_variants_str = '';


		if (is_array($VariantInfo) && count($VariantInfo) > 0  && $VariantInfo != false) {

			foreach ($VariantInfo  as $value) {

				$attr_id = $value['attr_id'];

				$attr_value = $value['attr_value'];



				$AttrData = $cart_obj->getAttributeDetails($attr_id);



				if ($AttrData == false) {

					$attr_name = '';
				} else {

					$attr_name = $AttrData['attr_name'];
				}





				$AttrOptionData = $cart_obj->getAttributeOptionDetails($attr_value);

				if ($AttrOptionData == false) {

					$attr_option_name = '';
				} else {

					$attr_option_name = $AttrOptionData['attr_options_name'];
				}





				if ($attr_name != '' && $attr_option_name != '') {

					//$product_variants_arr[$attr_name]=$attr_option_name;  //comment by al

					$product_variants_arr[] = array($attr_name => $attr_option_name);
				}
			}
		}

		// if(isset($product_variants_arr) && count($product_variants_arr)>0){

		// 	$product_variants_str=json_encode($product_variants_arr);

		// }else{

		// 	$product_variants_str='';

		// }



		$product_variants = '';

		if (isset($product_variants_arr)  && count($product_variants_arr) > 0) {

			$product_variants = $product_variants_arr;
		}



		$variants = array();

		if (isset($product_variants) && $product_variants != '') {



			foreach ($product_variants as $pk => $single_variant) {

				foreach ($single_variant as $key => $val) {

					$variants[] = $key . ' : ' . $val . ' ';
				}
			}
		} else {

			$variants[] = '';
		}

		if (isset($variants) && $variants != '' && !empty($variants)) {

			$variants =  implode(', ', $variants);
		}



		$insert_productdata_arr = array(

			"customer_id" => "'" . $customer_id . "'",

			"product_name" => '"' . $product_name . '"',

			"barcode" => "'" . $barcode . "'",

			"sku" => "'" . $sku . "'",

			"variants" => '"' . $variants . '"',

			"launch_date" => "'" . $launch_date . "'",

			"qty_scanned" => "'" . $qty_scanned . "'",

			"webshop_price" => "'" . $webshop_price . "'",

			"created_at" => time(),

			'ip' => "'" . $_SERVER['REMOTE_ADDR'] . "'"



		);



		$insert_product = $webshop_obj->insert_productdata($shopcode, $insert_productdata_arr);



		if ($insert_product == false) {

			$error = 'Something went wrong';
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Products Inserted';

		// $message['products'] = $products;

		exit(json_encode($message));
	}
});





$app->post('/webshop/scanned_products_listing_new', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($shopcode == '' || $shopid == '' || $customer_id == '') {

		$error = 'Please pass all the mandatory values';
	} else {

		$scanned_products = array();

		// $order_obj = new DbOrders();

		// $ch_obj= new  DbCheckout();

		$webshop_obj = new DbProductFeature();





		$scanned_productsData = $webshop_obj->getScannedProductsData($shopcode, $customer_id);

		$scanned_products_count = $webshop_obj->countgetScannedProductsData($shopcode, $customer_id);

		if ($scanned_productsData != false) {



			if (isset($scanned_productsData) && count($scanned_productsData) > 0) {

				foreach ($scanned_productsData as $scanned_product) {



					$scanned_products[] = $scanned_product;
				}
			}
		} else {

			$error = 'No Scanned Product available in table';
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['scanned_products_count'] = $scanned_products_count;

		$message['scanned_productsData'] = $scanned_products;

		$message['message'] = 'My Scanned products  lisintg..';

		exit(json_encode($message));
	}
});

/*endtesting new*/

$app->post('/webshop/update_scanned_qty', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($shopcode == '' || $shopid == '' || $customer_id == '' || $id == '' || $qty_scanned == '') {

		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbCommonFeature();

		$time = time();

		$ip = $_SERVER['REMOTE_ADDR'];

		$qty_scanned = (isset($qty_scanned) && $qty_scanned != '') ? $qty_scanned : '';

		// $customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';

		$table = 'catlog_builder_scanning';



		$update_column = 'qty_scanned = ?,updated_at = ?, ip = ?';

		$where = 'id = ? '; //and customer_id = ?

		$params = array($qty_scanned, $time, $ip, $id); //, $qty_scanned, $customer_id

		$update_catlog_builder_scanning = $webshop_obj->update_row($shopcode, $table, $update_column, $where, $params);


		if ($update_catlog_builder_scanning == 1) {

			$error = 'No data found';
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Data updated successfully';

		exit(json_encode($message));
	}
});



$app->post('/webshop/deleteScannedProduct', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($shopcode == '' || $shopid == '' || $id == '') {

		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();

		// $customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';

		$deleteScannedProduct = $webshop_obj->deleteScannedProduct($shopcode, $id);



		if ($deleteScannedProduct == 1) {

			$error = 'No data found';
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Data deleted successfully';

		exit(json_encode($message));
	}
});



$app->post('/webshop/deleteAllScannedProduct', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($shopcode == '' || $shopid == '' || $customer_id == '') {

		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();

		// $customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';

		$deleteScannedProduct = $webshop_obj->deleteAllScannedProduct($shopcode, $customer_id);



		if ($deleteScannedProduct == 1) {

			$error = 'No data found';
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Data deleted successfully';

		exit(json_encode($message));
	}
});



$app->post('/webshop/add_to_catlog_builder', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($shopcode == '' || $shopid == '' || $customer_id == '' || $csv_file == '') {

		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();



		$insert_catlog_Builder_arr = array(

			"customer_id" => "'" . $customer_id . "'",

			"csv_file" => "'" . $csv_file . "'",

			"catalog_name" => "'" . $catalog_name . "'",

			"customer_name" => "'" . $customer_name . "'",

			"email" => "'" . $email . "'",

			"phone_no" => "'" . $phone_no . "'",

			"show_qtys" => "'" . $show_qtys . "'",

			"show_retail_price" => "'" . $show_retail_price . "'",

			"show_coll_name" => "'" . $show_coll_name . "'",

			"show_style_code" => "'" . $show_style_code . "'",

			"show_upc" => "'" . $show_upc . "'",

			"show_csv_qtys" => "'" . $show_csv_qtys . "'",

			"show_csv_price" => "'" . $show_csv_price . "'",

			"sort_by" => "'" . $sort_by . "'",

			"display_currency" => "'" . $display_currency . "'",

			"created_by" => "'" . $customer_id . "'",

			"created_by_type" => 1,

			"created_at" => time(),

			'ip' => "'" . $_SERVER['REMOTE_ADDR'] . "'"



		);



		$insert_catlog_id = $webshop_obj->insert_catlog_Builder_data($shopcode, $insert_catlog_Builder_arr);



		if ($insert_catlog_id == false) {

			$error = 'Something went wrong';
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Data updated successfully';

		$message['lastInsertId'] = $insert_catlog_id;

		exit(json_encode($message));
	}
});



$app->post('/webshop/add_to_catlog_builder_items', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($shopcode == '' || $shopid == '' || $catalog_builder_id == '' || $upc == '') {

		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();



		$insert_catlog_Builder_items_arr = array(

			"catalog_builder_id" => "'" . $catalog_builder_id . "'",

			"upc" => "'" . $upc . "'",

			"product_id" => "'" . $product_id . "'",

			"parent_id" => "'" . $parent_id . "'",

			"product_name" => '"' . $product_name . '"',

			"sku" => "'" . $sku . "'",

			"variants" => '"' . $variants . '"',

			"launch_date" => "'" . $launch_date . "'",

			"quantity" => "'" . $quantity . "'",

			"retail_price" => "'" . $retail_price . "'",

			"price" => "'" . $price . "'"



		);



		$insert_catlog_item = $webshop_obj->insert_catlog_Builder_items_data($shopcode, $insert_catlog_Builder_items_arr);



		if ($insert_catlog_item == false) {

			$error = 'Something went wrong';
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Data updated successfully';

		$message['lastInsertId'] = $insert_catlog_item;

		exit(json_encode($message));
	}
});



$app->post('/webshop/base_color_data', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	$product_listing_count = 0;

	$variantArray = [];

	$color_filtertype = (isset($color_filtertype) ? $color_filtertype : '');

	$categoryid = (isset($categoryid) ? $categoryid : '');

	if ($shopcode == '' || $shopid == '' || ($categoryid == '' && $color_filtertype == '')) {

		$error = 'Please pass all the mandatory values';
	} else {



		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);

		$variant_id = $variant_id;

		$this->shopcode = $shopcode;

		$this->shopid = $shopid;

		//$this->categoryid = $categoryid;

		$this->categoryid = $categoryid ?? '';

		$this->customer_type_id = $customer_type_id ?? 1;

		$search_term = (isset($search_term) ? $search_term : '');



		$webshop_obj = new DbProductFeature();

		$base_color_listing = $webshop_obj->getBaseColorData($shopcode, $shopid);

		//$base_color_listing = $webshop_obj->getBaseColorDatatest($shopcode,$shopid);





		//$attr_ids = array_column($base_color_listing, 'id');

		if (is_array($base_color_listing) && count($base_color_listing) > 0  && $base_color_listing != false) {


			$base_ids = array_column($base_color_listing, 'id');

			$base_color_varient_listing = $webshop_obj->getOptionsByBaseColorIdForMultiple($shopcode, $shopid, $base_ids);

			$variant_match_options_ids = array_column($base_color_varient_listing, 'base_color_id');

			$this->variant_options_ids = array_group($base_color_varient_listing, 'base_color_id');

			$check_varient_id = array();

			foreach ($base_color_listing as $variant) {

				$color_name = $variant['color_name'];

				$square_color = $variant['square_color'];



				if (in_array($variant['id'], $variant_match_options_ids, true)) {

					$variantOptionData = $this->variant_options_ids[$variant['id']];

					$varOptionsIds = array_column($variantOptionData, 'id');

					$varOptionsOptionIds = array_column($variantOptionData, 'variant_option_id');

					$attr_value = '';

					$base_attr_value = '';

					//$varOptionsIds=array();

					foreach ($variantOptionData as $vkey => $vvalue) {



						$variant_id = $variant_id;



						$attr_value != "" && $attr_value .= ",";

						$attr_value .= $vvalue['variant_option_id'];

						$base_attr_value != "" && $base_attr_value .= "_";

						$base_attr_value .= $vvalue['variant_option_id'];



						$arr['OptionIds'] = $attr_value;

						$arr['color_name'] = $color_name;

						$arr['square_color'] = $square_color;

						$arr['base_attr_value'] = $base_attr_value;

						array_push($check_varient_id, $vvalue['variant_option_id']);

						$arr['check_varient_id'] = $check_varient_id;

						// }

					}



					$block_obj = new DbProductReviewFeature();



					$featured_ids = '';

					if ($color_filtertype == 'featured') {

						$productBlock = $block_obj->getProductsBlock($shopcode, $shopid, $color_filtertype);

						$id_string = trim($productBlock['assigned_products'], ",");

						$id_arr = explode(",", $id_string);

						$featured_ids = "'" . implode("','", $id_arr) . "'";
					}

					$ProductVariantCountMultiple = (int) $webshop_obj->checkProductCountByVariantMultipleBaseColor($this->shopcode, $this->shopid, $variant_id, $varOptionsOptionIds, $this->categoryid, $this->customer_type_id, $search_term, $color_filtertype, $featured_ids);


					if ($ProductVariantCountMultiple != false && $ProductVariantCountMultiple > 0) {

						$variantArray[] = $arr;
					}
				}
			}



			//$variantArray[] = $arr;

		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Base color available';

		$message['BaseColorList'] = $variantArray;

		exit(json_encode($message));
	}
});





$app->post('/webshop/getBundleChildValidateQty', function (Request $request, Response $response, $args) {



	$data = $request->getParsedBody();

	extract($data);



	$error = '';

	if ($qty == '') {

		$error = 'This Product qty is not available.';
	} else {

		$webshop_obj = new DbProductFeature();



		$flag = 0;



		if (isset($conf_simple_array) && !empty($conf_simple_array)) {

			foreach ($conf_simple_array as $confSimple) {



				$bundleProductsdata =  $webshop_obj->bundleProductById($confSimple['bundle_child_id']);

				$parent_id = ($bundleProductsdata['product_type'] == 'configurable') ? $bundleProductsdata['product_id'] : $bundleProductsdata['product_parent_id'];

				$product_inv = $webshop_obj->getAvailableInventory($confSimple['conf_simple_pid']);



				$total_qty = ($qty * $bundleProductsdata['default_qty']);

				$total_other_qty = 0;

				if (isset($quote_id) && $quote_id != '') {

					$total_other_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($quote_id, $bundleProductsdata['bundle_product_id'], $confSimple['conf_simple_pid'], $parent_id);
				}

				$total_qty = $total_qty + $total_other_qty;

				if ($total_qty > $product_inv['available_qty']) {

					$flag = 1;



					$message['statusCode'] = '500';

					$message['is_success'] = 'false';

					$message['message'] = 'This Product qty is not available.';

					exit(json_encode($message));
				}
			}
		}



		if (isset($bundle_products_ids) && $bundle_products_ids != '') {

			$bundle_products_ids = explode(',', $bundle_products_ids);

			foreach ($bundle_products_ids as $id) {

				$bundleProductsdata = $webshop_obj->bundleProductItemByIdWithInventory($main_bundle_id, $id);



				$total_qty = ($qty * $bundleProductsdata['default_qty']);

				$total_other_qty = 0;

				if (isset($quote_id) && $quote_id != '') {

					$total_other_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($quote_id, $bundleProductsdata['bundle_product_id'], $bundleProductsdata['product_id'], $bundleProductsdata['product_parent_id']);
				}

				$total_qty = $total_qty + $total_other_qty;

				if ($total_qty > $bundleProductsdata['available_qty']) {

					$flag = 1;



					$message['statusCode'] = '500';

					$message['is_success'] = 'false';

					$message['message'] = 'This Product qty is not available.';

					exit(json_encode($message));
				}
			}
		}
	}



	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Product qty is available.';

		exit(json_encode($message));
	}
});





$app->post('/webshop/getSimpleValidateQty', function (Request $request, Response $response, $args) {
	$data = $request->getParsedBody();
	extract($data);
	$error = '';

	if ($qty == '') {
		$error = 'This Product qty is not available.';
	} else {

		$webshop_obj = new DbProductFeature();
		$cart_obj = new DbCart();
		$productData = $webshop_obj->getproductDetailsById($product_id);

		if ($productData['product_inv_type'] == 'buy') {
			$product_inv = $webshop_obj->getAvailableInventory($productData['id']);
			if (is_numeric($product_inv['available_qty'])) {
				if ($product_inv['available_qty'] > 0) {
					$product_quantity = $product_inv['available_qty'];
				} else {

					$product_quantity = 0;
				}
			}
		}

		$total_bundle_qty = 0;
		if (isset($quote_id) && $quote_id != '') {
			$total_bundle_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($quote_id, '', $product_id, $parent_product_id);

			$quantity_total_check = $qty + $total_bundle_qty;
		} else {

			$quantity_total_check = $qty;
		}

		if ($product_quantity < $quantity_total_check) {

			$error = 'Product quantity is not available';
		}
	}

	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Product qty is available.';

		exit(json_encode($message));
	}
});

$app->post('/webshop/geSearchtCategoryIds', function (Request $request, Response $response, $args) {
	$data = $request->getParsedBody();
	extract($data);
	$error = '';

	if ($search_term == '') {
		$error = 'Please pass all the mandatory values';
	} else {

		$webshop_obj = new DbProductFeature();

		$categoryIds = $webshop_obj->getCategoryIds($search_term);

		if (!$categoryIds) {
			$error = 'No ids Found';
		}
	}

	if ($error != '') {

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));
	} else {

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['message'] = 'Product Category is available.';

		$message['categoryIds'] = $categoryIds;

		exit(json_encode($message));
	}
});
