<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->post('/webshop/prelauch_product_listing', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);


	$error='';
	if($shopcode =='' || $shopid=='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$final_arr = array();

		$page = (isset($page) ? $page : 0);
		$page_size = (isset($page_size) ? $page_size : 0);

		if($page>0){

			$page = ($page - 1) * $page_size;
		}


		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
        $options = (isset($options) ? $options : '');
		$lang_code =  (isset($lang_code) ? $lang_code : '');

		$webshop_obj = new DbProductFeature();
        $products_obj = new DbProductPreLauch();

        $block_obj = new DbProductReviewFeature();
        $identifier = 'prelauch';

		$productBlock = $block_obj->getProductsBlock($shopcode,$shopid,$identifier);
        $id_string = trim($productBlock['assigned_products'],","); //remove comma from start and end of the string.
		$id_arr = explode(",",$id_string); // convert string to array
		$ids = "'".implode("','", $id_arr)."'";  // Convert array to string with quote and comma.

		$product_listing_count = $products_obj->productListingCount($shopcode,$shopid,$ids,$customer_type_id,$options);
		//print_r($product_listing_count);exit;
		if($product_listing_count!=false){
			$product_listing_count=$product_listing_count;
		}else{
			$product_listing_count=0;
		}



		$product_listing = $products_obj->productListing($shopcode,$shopid,$ids,$customer_type_id,$options,$page,$page_size,$lang_code);
		//print_r($product_listing);exit;

		if($product_listing == false)
		{
			$error='New product not available';
		}else{
			foreach($product_listing as $value){
				if($value['product_type'] == 'simple'){

					if(isset($vat_percent_session) && $vat_percent_session !='') {
						$value['eu_webshop_price'] = ($value['price'] * $vat_percent_session/100) + $value['price'];
					}

					$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$value['id'],$customer_type_id);

					if($value['product_inv_type'] == 'buy'){
						$product_inv = $webshop_obj->getAvailableInventory($value['id'],$shopcode);
						if($product_inv['available_qty'] > 0){
							$value['stock_status'] = 'Instock';
							$value['qty'] = $product_inv['available_qty'];
						}
					}else if($value['product_inv_type'] == 'virtual'){
						$seller_shopcode = 'shop'.$value['shop_id'];
						$product_inv1 = $webshop_obj->getAvailableInventory($value['id'],$shopcode,$seller_shopcode);
						if($product_inv1['available_qty'] > 0){
							$value['stock_status'] = 'Instock';
							$value['qty'] = $product_inv1['available_qty'];
						}
					}else if($value['product_inv_type'] == 'dropship'){
						$seller_shopcode = 'shop'.$value['shop_id'];
						//$product_inv2 = $webshop_obj->getAvailableInventory($value['shop_product_id'],$seller_shopcode);
						$product_inv2 = $webshop_obj->getAvailableInventory($value['id'],$shopcode,$seller_shopcode);
						if($product_inv2['available_qty'] > 0){
							$value['stock_status'] = 'Instock';
							$value['qty'] = $product_inv2['available_qty'];
						}
					}

					if($specialPriceArr!=false){
						if(isset($vat_percent_session) && $vat_percent_session !='') {
							$value['special_price'] = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
						}else{
							$value['special_price'] = $specialPriceArr['special_price'];
						}
						$value['special_price_from'] = $specialPriceArr['special_price_from'];
						$value['special_price_to'] = $specialPriceArr['special_price_to'];
						$value['display_original'] = $specialPriceArr['display_original'];
					}

				}
				else if($value['product_type'] == 'bundle'){

					// echo($value['id']); exit();
					if(isset($vat_percent_session) && $vat_percent_session !='') {
						$value['eu_webshop_price'] = ($value['price'] * $vat_percent_session/100) + $value['price'];
					}

					$value['stock_status'] = 'Instock';
					$value['qty'] = 1;

					$specialPriceArr  = false;

				}else{
					$price_array = array();
					$eu_webshop_priceArray = array();
					$flag = false;

					if(isset($vat_percent_session) && $vat_percent_session !='') {

						$configProduct = $webshop_obj->configurableProduct($shopcode,$shopid,$value['id']);

						$qty = '0';
						if($configProduct!=false){
							foreach ($configProduct as $conf) {

								$eu_webshop_price = ($conf['price'] * $vat_percent_session/100) + $conf['price'];
								$eu_webshop_priceArray[] = $eu_webshop_price;


								$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$conf['id'],$customer_type_id);

								if($conf['product_inv_type'] == 'buy'){
									$product_inv = $webshop_obj->getAvailableInventory($conf['id'],$shopcode);
									if(is_numeric($product_inv['available_qty'])){
										if($product_inv['available_qty'] > 0){
											$qty += $product_inv['available_qty'];
										}
									}
								}else if($conf['product_inv_type'] == 'virtual'){
									$seller_shopcode = 'shop'.$conf['shop_id'];
									$product_inv1 = $webshop_obj->getAvailableInventory($conf['id'],$shopcode,$seller_shopcode);

									if($product_inv1['available_qty'] > 0){
										$qty += $product_inv1['available_qty'];
									}

								}else if($conf['product_inv_type'] == 'dropship'){
									$seller_shopcode = 'shop'.$conf['shop_id'];
									//$product_inv2 = $webshop_obj->getAvailableInventory($conf['shop_product_id'],$seller_shopcode);
									$product_inv2 = $webshop_obj->getAvailableInventory($conf['id'],$shopcode,$seller_shopcode);

									if($product_inv2['available_qty'] > 0){
										$qty += $product_inv2['available_qty'];
									}
								}

								if($specialPriceArr!=false){
									$flag = true;
									$arr['display_original'] = $specialPriceArr['display_original'];
									$arr['different_price'] = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
								}else{
									$arr['different_price'] = $eu_webshop_price;
									$arr['display_original'] = '';
								}

								$price_array[] = $arr;

							}

							$value['min_price'] = min($eu_webshop_priceArray);
							$value['max_price'] = max($eu_webshop_priceArray);
							$value['eu_webshop_price'] = min($eu_webshop_priceArray);


						}

					}else{

						$minPrice = $webshop_obj->getMinPrice($shopcode,$shopid,$value['id']);
						$value['min_price'] = $minPrice['min_price'];
						$value['max_price'] = $minPrice['max_price'];

						$value['webshop_price'] = $minPrice['min_price'];

						$configProduct = $webshop_obj->configurableProduct($shopcode,$shopid,$value['id']);

						$qty = '0';
						if($configProduct!=false){
						foreach ($configProduct as $conf) {

							$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$conf['id'],$customer_type_id);

							if($conf['product_inv_type'] == 'buy'){
								$product_inv = $webshop_obj->getAvailableInventory($conf['id'],$shopcode);
								if(is_numeric($product_inv['available_qty'])){
									if($product_inv['available_qty'] > 0){
										$qty += $product_inv['available_qty'];
									}
								}
							}else if($conf['product_inv_type'] == 'virtual'){
								$seller_shopcode = 'shop'.$conf['shop_id'];
								$product_inv1 = $webshop_obj->getAvailableInventory($conf['id'],$shopcode,$seller_shopcode);

								if($product_inv1['available_qty'] > 0){
									$qty += $product_inv1['available_qty'];
								}

							}else if($conf['product_inv_type'] == 'dropship'){
								$seller_shopcode = 'shop'.$conf['shop_id'];
								//$product_inv2 = $webshop_obj->getAvailableInventory($conf['shop_product_id'],$seller_shopcode);
								$product_inv2 = $webshop_obj->getAvailableInventory($conf['id'],$shopcode,$seller_shopcode);

								if($product_inv2['available_qty'] > 0){
									$qty += $product_inv2['available_qty'];
								}
							}

							if($specialPriceArr!=false){
								$flag = true;
								$arr['display_original'] = $specialPriceArr['display_original'];
								$arr['different_price'] = $specialPriceArr['special_price'];
							}else{
								$arr['different_price'] = $conf['webshop_price'];
								$arr['display_original'] = '';
							}

							$price_array[] = $arr;

						}

					}


				}



					if($flag == true){
						$prices = array_column($price_array, 'different_price');
						$display_original = array_values(array_filter(array_column($price_array, 'display_original')));

						$value['special_price'] = min($prices);
						$value['special_min_price'] = min($prices);
						$value['special_max_price'] = max($prices);
						$value['display_original'] = $display_original[0];
					}

					if($qty > 0){

						$value['stock_status'] = 'Instock';
						$value['qty'] = $qty;
					}
				}

				$final_arr[] = $value;

			}
		}

		if(isset($vat_percent_session) && $vat_percent_session !='') {

			if($options=='price_des'){
				$keys = array_column($final_arr, 'eu_webshop_price');
				array_multisort($keys, SORT_DESC, $final_arr);
			}else if($options=='price_asc'){
				$keys = array_column($final_arr, 'eu_webshop_price');
				array_multisort($keys, SORT_ASC, $final_arr);
			}

		}else{

			if($options=='price_des'){
				$keys = array_column($final_arr, 'webshop_price');
				array_multisort($keys, SORT_DESC, $final_arr);
			}else if($options=='price_asc'){
				$keys = array_column($final_arr, 'webshop_price');
				array_multisort($keys, SORT_ASC, $final_arr);
			}

		}



		if(empty($final_arr))
		{
			if($page > 1)
			{
				$message['statusCode'] = '200';
				$message['is_success'] = 'true';
				$message['message'] = 'No product!';
				$message['ProductList'] = $final_arr;
				$message['ProductListCount'] = $product_listing_count;
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
			$message['ProductList'] = $final_arr;
			$message['ProductListCount'] = $product_listing_count;
			exit(json_encode($message));
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}
});



$app->post('/webshop/prelauch_product_listing_all', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);


	$error='';
	if($shopcode =='' || $shopid=='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$final_arr = array();


		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
        $options = (isset($options) ? $options : '');


		$webshop_obj = new DbProductFeature();
        $products_obj = new DbProductPreLauch();

        $block_obj = new DbProductReviewFeature();
        $identifier = 'prelauch';

		$productBlock = $block_obj->getProductsBlock($shopcode,$shopid,$identifier);
        $id_string = trim($productBlock['assigned_products'],","); //remove comma from start and end of the string.
		$id_arr = explode(",",$id_string); // convert string to array
		$ids = "'".implode("','", $id_arr)."'";  // Convert array to string with quote and comma.

		$product_listing = $products_obj->productListing($shopcode,$shopid,$ids,$customer_type_id,$options);

		if($product_listing == false)
		{
			$error='New product not available';
		}else{

			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'New product available';
			$message['ProductList'] = $product_listing;
			exit(json_encode($message));


		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		$message['ProductList'] = '';
		exit(json_encode($message));
	}

});
