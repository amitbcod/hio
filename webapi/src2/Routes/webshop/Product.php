<?php

use App\Controllers\ProductListingController;
use App\Controllers\ProductListingControllerTest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/get_category_details', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $categoryslug =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbProductFeature();
		if(isset($lang_code) && $lang_code!=''){$lang_code=$lang_code;}else{$lang_code='';}
		$getCategoryDetails = $webshop_obj->getCategoryDetails($shopcode,$shopid,$categoryslug,$lang_code);

		if($getCategoryDetails == false)
		{
			$error='No category found';
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Category available';
		$message['CategoryDetails'] = $getCategoryDetails;
		exit(json_encode($message));
	}

});

$app->post('/webshop/get_product_category_by_level', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();

	extract($data);

	$error='';
	if($shopcode=='' || $product_id=='' || $cat_level=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbProductFeature();

		$getCategoryDetails = $webshop_obj->getProductCategoryByLevel($shopcode,$product_id,$cat_level);

		if($getCategoryDetails == false)
		{
			$error='No category found';
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Category available';
		$message['CategoryDetails'] = $getCategoryDetails;
		exit(json_encode($message));
	}

});

$app->post('/webshop/get_product_categorys', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();

	extract($data);

	$error='';
	if($shopcode=='' || $product_id=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbProductFeature();

		$getCategoryIds = $webshop_obj->getProductCategorys($shopcode,$product_id);

		if($getCategoryIds == false)
		{
			$error='No category found';
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Category available';
		$message['CategoryIds'] = $getCategoryIds;
		exit(json_encode($message));
	}

});


$app->post('/webshop/product_listing', ProductListingController::class);
$app->post('/webshop/product_listingTest', ProductListingControllerTest::class);

$app->post('/webshop/product_listing_count', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	$product_listing_count =0;
	if($shopcode =='' || $shopid=='')
	{
		$error='Please pass all the mandatory values';

	}else{

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
		$product_listing_count = $webshop_obj->productListingCount($shopcode,$shopid,$categoryid,$options,$customer_type_id,$gender,$price_range,$variant_id_arr,$variant_attr_value_arr,$attribute_arr,$search_term);

		if($product_listing_count!=false){
			$product_listing_count=$product_listing_count;
		}else{
			$product_listing_count=0;
		}



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
		$message['ProductListCount'] = $product_listing_count;
		exit(json_encode($message));
	}
});

$app->post('/webshop/browse_by_category', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$final_arr = array();
		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);

		$webshop_obj = new DbProductFeature();
		$browseByCategory = $webshop_obj->mainCategory($shopcode,$shopid,$customer_type_id);

		if($browseByCategory == false)
		{
			$error='No category found';
		}else{
			foreach ($browseByCategory as $cat) {
				$firstLevelCategory = $webshop_obj->firstLevelCategory($shopcode,$shopid,$cat['id']);

				if($firstLevelCategory != false)
				{
					foreach ($firstLevelCategory as $cat1) {
						$secondLevelCategory = $webshop_obj->secondLevelCategory($shopcode,$shopid,$cat1['id'],$cat['id']);
						if($secondLevelCategory != false)
						{
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

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Category available';
		$message['AllCategoryLevels'] = $final_arr;
		exit(json_encode($message));
	}

});

$app->post('/webshop/product_detail', function (Request $request, Response $response, $args){
	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($shopcode) || empty($shopid) || empty($product_url_key) )
	{
		$error='Please pass all the mandatory values';

	}else{


		$final_arr = array();
		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
		$prelaunch =  (isset($prelaunch) ? $prelaunch : 'no');
		$lang_code =  (isset($lang_code) ? $lang_code : '');

		$webshop_obj = new DbProductFeature();
		$rating_obj = new DbProductReviewFeature();
		$comn_obj = new DbCommonFeature();
		$productData = $webshop_obj->productDetails($shopcode,$shopid,$product_url_key,$customer_type_id,$prelaunch,$lang_code);

		if($productData == false)
		{
			$error='Product not available';
		}else{
			/* if($productData['product_inv_type'] == 'virtual'){

				$inventory = $webshop_obj->getInventoryForVertual($productData['id'],$shopcode);
				if($inventory!=false && $inventory['available_qty']<=0){

					$delivery_time = $webshop_obj->getEstimateDeliveryTime($shopcode,$productData['imported_from'],$shopid);

					$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0;
					$ProDelTime2 = ($delivery_time['buyin_del_time'] != '') ? $delivery_time['buyin_del_time'] : 0;

					$estimate_delivery_time = $ProDelTime1 + $ProDelTime2;
					$productData['estimate_delivery_time'] = $estimate_delivery_time;
				}
			}else if($productData['product_inv_type'] == 'dropship'){

				$delivery_time = $webshop_obj->getEstimateDeliveryTime($shopcode,$productData['imported_from'],$shopid);
				$productData['estimate_delivery_time'] = $delivery_time['dropship_del_time'];
			} */

			if($productData['product_type'] == 'simple'){

				if($productData['product_inv_type'] == 'buy'){
					$product_inv = $webshop_obj->getAvailableInventory($productData['id'],$shopcode);
					if($product_inv['available_qty'] > 0){
						$productData['stock_status'] = 'Instock';
						$productData['total_qty'] = $product_inv['available_qty'];
					}else{
						$productData['stock_status'] = 'OutofStock';
						$productData['total_qty'] = $product_inv['available_qty'];
					}

					$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0;
					$delay_warehouse_time = $comn_obj->getDelayWarehouseTime($shopcode);
					$delay_warehouse_timing = (isset($delay_warehouse_time['value']) && $delay_warehouse_time['value'] != '') ? $delay_warehouse_time['value'] : 0;
					$estimate_delivery_time = $ProDelTime1 + $delay_warehouse_timing;
					$productData['estimate_delivery_time'] = $estimate_delivery_time;

				}else if($productData['product_inv_type'] == 'virtual') {
					$seller_shopcode = 'shop'.$productData['imported_from']; // changed by al  shop_id to imported_from
					$product_inv1 = $webshop_obj->getAvailableInventory($productData['id'],$shopcode,$seller_shopcode);

					if($product_inv1['available_qty'] > 0){
						$productData['stock_status'] = 'Instock';
						$productData['total_qty'] = $product_inv1['available_qty'];
					}else{
						$productData['stock_status'] = 'Outofstock';
						$productData['total_qty'] = $product_inv1['available_qty'];
					}

					//Estimated Delivery for Virtual (Simple)
					$inventory = $webshop_obj->getInventoryForVertual($productData['id'],$shopcode);
					if($inventory!=false && $inventory['available_qty']<=0){

						$delivery_time = $webshop_obj->getEstimateDeliveryTime($shopcode,$productData['imported_from'],$shopid);

						$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0;
						$ProDelTime2 = ($delivery_time['buyin_del_time'] != '') ? $delivery_time['buyin_del_time'] : 0;

						$delay_warehouse_time = $comn_obj->getDelayWarehouseTime($shopcode);
						$delay_warehouse_timing = (isset($delay_warehouse_time['value']) && $delay_warehouse_time['value'] != '') ? $delay_warehouse_time['value'] : 0;

						$delay_warehouse_time2 = $comn_obj->getDelayWarehouseTime($seller_shopcode);
						$delay_warehouse_timing2= (isset($delay_warehouse_time2['value']) && $delay_warehouse_time2['value'] != '') ? $delay_warehouse_time2['value'] : 0;

						$estimate_delivery_time = $ProDelTime1 + $ProDelTime2 + $delay_warehouse_timing + $delay_warehouse_timing2;
						$productData['estimate_delivery_time'] = $estimate_delivery_time;
					}else{

						$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0;

						$delay_warehouse_time = $comn_obj->getDelayWarehouseTime($shopcode);
						$delay_warehouse_timing = (isset($delay_warehouse_time['value']) && $delay_warehouse_time['value'] != '') ? $delay_warehouse_time['value'] : 0;

						$productData['estimate_delivery_time'] = $ProDelTime1 + $delay_warehouse_timing;
					}

				}else if($productData['product_inv_type'] == 'dropship'){
					$seller_shopcode = 'shop'.$productData['imported_from']; // changed by al  shop_id to imported_from
					//$product_inv2 = $webshop_obj->getAvailableInventory($productData['shop_product_id'],$seller_shopcode);
					$product_inv2 = $webshop_obj->getAvailableInventory($productData['id'],$shopcode,$seller_shopcode);
					if($product_inv2['available_qty'] > 0){
						$productData['stock_status'] = 'Instock';
						$productData['total_qty'] = $product_inv2['available_qty'];
					}else{
						$productData['stock_status'] = 'Outofstock';
						$productData['total_qty'] = $product_inv2['available_qty'];
					}

					//Estimated Delivery for Dropship (Simple)
					$delivery_time = $webshop_obj->getEstimateDeliveryTime($shopcode,$productData['imported_from'],$shopid);

					$delay_warehouse_time = $comn_obj->getDelayWarehouseTime($seller_shopcode);
					$delay_warehouse_timing= (isset($delay_warehouse_time['value']) && $delay_warehouse_time['value'] != '') ? $delay_warehouse_time['value'] : 0;

					$productData['estimate_delivery_time'] = $delivery_time['dropship_del_time']+$delay_warehouse_timing;
				}else{
					$productData['stock_status'] = 'Notavailable';
					$productData['total_qty'] = 0;
				}

				$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$productData['id'],$customer_type_id);
				if($specialPriceArr!=false){

					if(isset($vat_percent_session) && $vat_percent_session !='') {
						$productData['special_price'] = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
					}else{
						$productData['special_price'] = $specialPriceArr['special_price'];
					}
					$productData['special_price_from'] = $specialPriceArr['special_price_from'];
					$productData['special_price_to'] = $specialPriceArr['special_price_to'];
					$productData['display_original'] = $specialPriceArr['display_original'];
				}

				if(isset($vat_percent_session) && $vat_percent_session !='') {
					$productData['least_price'] = ($productData['price'] * $vat_percent_session/100) + $productData['price'];
				}else{
					$productData['least_price'] = $productData['webshop_price'];
				}
			}else{

				$specialPriceArray = array();
				$eu_webshop_priceArray = array();
				$flag = false;

				$variantMaster=$webshop_obj->productVariantMaster($shopcode,$productData['id']);

				$productData['variant_master']=(isset($variantMaster) && $variantMaster!=false) ? $variantMaster : array();

				$configProduct = $webshop_obj->configurableProduct($shopcode,$shopid,$productData['id']);

				$qty = '0';
				$quantity = 0;
				$configChildProduct = array();
				$configChildProductNotInv = array();// inv
				$estcount = 0;
				foreach ($configProduct as $value) {

					if(isset($vat_percent_session) && $vat_percent_session !='') {

						$eu_webshop_price = ($value['price'] * $vat_percent_session/100) + $value['price'];
						$eu_webshop_priceArray[] = $eu_webshop_price;
					}

					if($value['product_inv_type'] == 'buy') {
						$product_inv = $webshop_obj->getAvailableInventory($value['id'],$shopcode);
						if(is_numeric($product_inv['available_qty'])){
							if($product_inv['available_qty'] > 0){
								$qty += $product_inv['available_qty'];
								$quantity = $product_inv['available_qty'];

								//Estimated Delivery for Simple (Configurable)
								if($estcount == 0) {
									// $productData['estimate_delivery_time'] remains as it is

									$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0;
									$delay_warehouse_time = $comn_obj->getDelayWarehouseTime($shopcode);
									$delay_warehouse_timing = (isset($delay_warehouse_time['value']) && $delay_warehouse_time['value'] != '') ? $delay_warehouse_time['value'] : 0;
									$estimate_delivery_time = $ProDelTime1 + $delay_warehouse_timing;
									$productData['estimate_delivery_time'] = $estimate_delivery_time;

									$estcount = $estcount + 1;
								}

							}else{ // stock check
								$configChildProductNotInv[]=$value['id'];
							}
						}else{ // stock check
							$configChildProductNotInv[]=$value['id'];
						}

					}else if($value['product_inv_type'] == 'virtual'){
						$seller_shopcode = 'shop'.$value['imported_from'];  // changed by al  shop_id to imported_from
						$product_inv1 = $webshop_obj->getAvailableInventory($value['id'],$shopcode,$seller_shopcode);

						if($product_inv1['qty'] > 0){
							$qty += $product_inv1['qty'];
							$quantity = $product_inv1['qty'];

							//Estimated Delivery for Virtual (Configurable)
							if($estcount == 0) {
								$inventory = $webshop_obj->getInventoryForVertual($value['id'],$shopcode);
								if($inventory!=false && $inventory['available_qty']<=0) {
									$delivery_time = $webshop_obj->getEstimateDeliveryTime($shopcode,$value['imported_from'],$shopid);

									$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0; //Need to take parent estimate_delivery_time
									$ProDelTime2 = ($delivery_time['buyin_del_time'] != '') ? $delivery_time['buyin_del_time'] : 0;

									$delay_warehouse_time = $comn_obj->getDelayWarehouseTime($shopcode);
									$delay_warehouse_timing = (isset($delay_warehouse_time['value']) && $delay_warehouse_time['value'] != '') ? $delay_warehouse_time['value'] : 0;

									$delay_warehouse_time2 = $comn_obj->getDelayWarehouseTime($seller_shopcode);
									$delay_warehouse_timing2= (isset($delay_warehouse_time2['value']) && $delay_warehouse_time2['value'] != '') ? $delay_warehouse_time2['value'] : 0;

									$estimate_delivery_time = $ProDelTime1 + $ProDelTime2 + $delay_warehouse_timing + $delay_warehouse_timing2;
									$productData['estimate_delivery_time'] = $estimate_delivery_time;
								}else{

									$ProDelTime1 = ($productData['estimate_delivery_time'] != '') ? $productData['estimate_delivery_time'] : 0;

									$delay_warehouse_time = $comn_obj->getDelayWarehouseTime($shopcode);
									$delay_warehouse_timing = (isset($delay_warehouse_time['value']) && $delay_warehouse_time['value'] != '') ? $delay_warehouse_time['value'] : 0;

									$productData['estimate_delivery_time'] = $ProDelTime1 + $delay_warehouse_timing;

								}
								//else { // $productData['estimate_delivery_time'] remains as it is }

								$estcount = $estcount + 1;
							}

						}else{ // stock check
								$configChildProductNotInv[]=$value['id'];
						}

					}else if($value['product_inv_type'] == 'dropship'){
						$seller_shopcode = 'shop'.$value['imported_from']; // changed by al  shop_id to imported_from
						//$product_inv2 = $webshop_obj->getAvailableInventory($value['shop_product_id'],$seller_shopcode);
						$product_inv2 = $webshop_obj->getAvailableInventory($value['id'],$shopcode,$seller_shopcode);

						if($product_inv2['available_qty'] > 0){
							$qty += $product_inv2['available_qty'];
							$quantity = $product_inv2['available_qty'];

							//Estimated Delivery for Dropship (Configurable)
							if($estcount == 0) {
								$delivery_time = $webshop_obj->getEstimateDeliveryTime($shopcode,$value['imported_from'],$shopid);

								$delay_warehouse_time = $comn_obj->getDelayWarehouseTime($seller_shopcode);
								$delay_warehouse_timing= (isset($delay_warehouse_time['value']) && $delay_warehouse_time['value'] != '') ? $delay_warehouse_time['value'] : 0;

								$productData['estimate_delivery_time'] = $delivery_time['dropship_del_time']+ $delay_warehouse_timing;
								$estcount = $estcount + 1;
							}

						}else{ // stock check
								$configChildProductNotInv[]=$value['id'];
						}
					}

					$specialPrice = $webshop_obj->getSpecialPrices($shopcode,$value['id'],$customer_type_id);

					if($specialPrice!=false){
						$flag = true;
						$arr1['display_original'] = $specialPrice['display_original'];
						if(isset($vat_percent_session) && $vat_percent_session !='') {
							$arr1['different_price'] = ($specialPrice['special_price'] * $vat_percent_session/100)+$specialPrice['special_price'];
						}else{
							$arr1['different_price'] = $specialPrice['special_price'];
						}
					}else{
						if(isset($vat_percent_session) && $vat_percent_session !='') {
							$arr1['different_price'] = $eu_webshop_price;
						}else{
							$arr1['different_price'] = $value['webshop_price'];
						}
						$arr1['display_original'] = '';
					}

					$specialPriceArray[] = $arr1;

					$arr['id'] = $value['id'];
					$arr['sku'] = $value['sku'];
					$arr['barcode'] = $value['barcode'];
					$arr['price'] = $value['webshop_price'];
					$arr['cost_price'] = $value['cost_price'];
					$arr['parent_id'] = $value['parent_id'];
					$arr['fbc_user_id'] = $value['fbc_user_id'];
					$arr['display_original'] = $arr1['display_original'];
					$arr['special_price'] = $arr1['different_price'];
					$arr['qty'] = $quantity;
					$configChildProduct[] = $arr;

				}

				// define variable
				$variants_arr = array();

				if(isset($vat_percent_session) && $vat_percent_session !='') {

					$productData['least_price'] =min($eu_webshop_priceArray);
					$productData['webshop_price'] = min($eu_webshop_priceArray);

				}else{
					$minPrice = $webshop_obj->getMinPrice($shopcode,$shopid,$productData['id']);
					$productData['least_price'] = $minPrice['min_price'];
					$productData['webshop_price'] = $minPrice['min_price'];
				}

				if($qty > 0 || $prelaunch == 'yes'){ // check stock
					$productData['stock_status'] = 'Instock';
					$productData['total_qty'] = $qty;

				if($flag == true){
					$prices = array_column($specialPriceArray, 'different_price');
					$display_original = array_values(array_filter(array_column($specialPriceArray, 'display_original')));

					$productData['special_price'] = min($prices);
					$productData['special_min_price'] = min($prices);
					$productData['special_max_price'] = max($prices);
					$productData['display_original'] = $display_original[0];
				}

				$variantProduct = $webshop_obj->configProductVariant($shopcode,$shopid,$productData['id']);

				if($variantProduct!=false){
					$childProductsNotStock='';
					if($configChildProductNotInv){
						$childProductsNotStock=implode(",",$configChildProductNotInv);
					}
					foreach ($variantProduct as $variant) {
						$variantOption = $webshop_obj->productVariantOptionsInstockNewQuery($shopcode,$shopid,$productData['id'],$variant['id'],$childProductsNotStock,$prelaunch);
						$varr['variant_id'] = $variant['id'];
						$varr['variant_name'] = $variant['attr_name'];
						$varr['variant_code'] = $variant['attr_code'];
						$varr['variant_options'] = $variantOption;

						$variants_arr[] = $varr;
					}
				}

				}else{ // else check stock

					$productData['stock_status'] = 'Notavailable';
					$productData['total_qty'] = 0;



				}

				$productData['childProducts'] = $configChildProduct;  // Store child product
				$productData['product_variants'] = $variants_arr;  // store variants  // store variants

			}

			$rating_arr=array();
			if($productData['product_reviews_code'] == ''){
				$ratingData = $rating_obj->getProductRatings($shopcode,$shopid,$productData['id']);
				if($ratingData != false){
					$average = ($ratingData['ratings'] / $ratingData['total_rating_count']);
					$average_rating = round($average, 1);
					$rat_arr['average_rating_start'] = $average_rating;
					$rat_arr['total_rating_count'] = $ratingData['total_rating_count'];
				}else{
					$rat_arr['average_rating_start'] = 0;
					$rat_arr['total_rating_count'] = 0;
				}

				$rating_arr[] = $rat_arr;
				//echo $average_rating;exit;
			}else{
				$rating_star = 0;
				$rating_count = 0;
				$productIds = $rating_obj->productidsByReviewCode($shopcode,$shopid,$productData['id']);

				if($productIds != false){
					foreach($productIds as $ids){
						$ratingData = $rating_obj->getProductRatings($shopcode,$shopid,$ids['id']);

						if($ratingData != false){
							$rating_star += $ratingData['ratings'];
							$rating_count += $ratingData['total_rating_count'];
						}
					}
					if($rating_star > 0 && $rating_count > 0){
						$average = ($rating_star / $rating_count);
						$average_rating = round($average, 1);
						$rat_arr['average_rating_start'] = $average_rating;
						$rat_arr['total_rating_count'] = $rating_count;
					}else{
						$rat_arr['average_rating_start'] = 0;
						$rat_arr['total_rating_count'] = $rating_count;
					}
					$rating_arr[] = $rat_arr;
				}
			}
			$attributData = $webshop_obj->attributDetails($shopcode,$shopid,$productData['id'],$lang_code);
		
			$mediaGalleryData = $webshop_obj->mediaGallery($shopcode,$shopid,$productData['id']);

			$AttributesWithOptions=$webshop_obj->attributeDropdownForProductDetail($shopcode,$shopid,$productData['id'],$lang_code);
			$productData['specification'] = $attributData;
			$productData['mediaGallery'] = (isset($mediaGalleryData) && $mediaGalleryData!=false ) ? $mediaGalleryData : array() ;
			$productData['ratingDetails'] = $rating_arr;
			$productData['AttributesWithOptions'] = $AttributesWithOptions;

			//echo "<pre>";print_r($productData);exit;
		}

	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Product Details Available';
		$message['ProductData'] = $productData;
		exit(json_encode($message));
	}

});


$app->post('/webshop/get_conf_simprod_by_variants', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$ConfigSimpleResponse=array();

	$error='';
	if($shopcode=='' || $shopid=='' || $product_id=='' || $total_variant=='' || $selected_variant=='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$webshop_obj = new DbProductFeature();

		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
		$prelaunch =  (isset($prelaunch) ? $prelaunch : 'no');

		$productData = $webshop_obj->getproductDetailsById($shopcode,$shopid,$product_id);

		if($productData == false)
		{
			$error='No Product found';
		}else{
			if($total_variant>0){

				$conf_simple_product_id="";

				$parent_id=$product_id;

				$variantMaster=$webshop_obj->productVariantMaster($shopcode,$parent_id);

				if($total_variant==1){
					$attr_id=$selected_variant[0]['attr_id'];
					$attr_value=$selected_variant[0]['attr_value'];

					$cof_simple=$webshop_obj->getsimplevariantproducts($shopcode,$parent_id,$attr_id,$attr_value);

					$cof_simple=$cof_simple[0];
					if(isset($cof_simple) && $cof_simple['id']!=''){
						$conf_simple_product_id=$cof_simple['product_id'];
					}

				}else if($total_variant>1){


					//print_r($selected_variant);exit;

					$attr_id=$selected_variant[0]['attr_id'];
					$attr_value=$selected_variant[0]['attr_value'];

					$second_attr_id=$selected_variant[1]['attr_id'];
					$second_attr_value=$selected_variant[1]['attr_value'];

					if($total_variant>2){
						$third_attr_id=$selected_variant[2]['attr_id'];
						$third_attr_value=$selected_variant[2]['attr_value'];
					}

					if($total_variant>3){
						$fourth_attr_id=$selected_variant[3]['attr_id'];
						$fourth_attr_value=$selected_variant[3]['attr_value'];
					}

					// simple products of variant[0]
					$conf_simple_products  =$webshop_obj->getsimplevariantproducts($shopcode,$parent_id,$attr_id,$attr_value);

					//print($conf_simple_products);exit;

					if(isset($conf_simple_products) && count($conf_simple_products)>0){
						foreach($conf_simple_products as $conf_simple){
							$temp_product_id=$conf_simple['product_id'];
							if($total_variant==2)
							{

								$second_conf_simple=$webshop_obj->simplevariantproductexistbyattrid($shopcode,$parent_id,$second_attr_id,$second_attr_value,$temp_product_id);

								if(isset($second_conf_simple) && $second_conf_simple['count']>0){
									$conf_simple_product_id=$temp_product_id;
									break;
								}

							}else if($total_variant==3){

								$third_conf_simple=$webshop_obj->simplevariantproductexistbyattrid($shopcode,$parent_id,$third_attr_id,$third_attr_value,$temp_product_id);

								if(isset($third_conf_simple) && $third_conf_simple['count']>0){
									$conf_simple_product_id=$temp_product_id;
									break;
								}

							}else if($total_variant==4){
								$fourth_conf_simple=$webshop_obj->simplevariantproductexistbyattrid($shopcode,$parent_id,$fourth_attr_id,$fourth_attr_value,$temp_product_id);

								if(isset($fourth_conf_simple) && $fourth_conf_simple['count']>0){
									$conf_simple_product_id=$temp_product_id;
									break;
								}

							}

						}
					}else{
						$conf_simple_product_id='';
					}

				}



				if(isset($conf_simple_product_id)  && $conf_simple_product_id!='' && $conf_simple_product_id>0){


					$quantity = 0;
					$ConfSimpleData=$webshop_obj->getproductDetailsById($shopcode,$shopid,$conf_simple_product_id);

					$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$conf_simple_product_id,$customer_type_id);

					$ConfigSimpleResponse['conf_simple_pro_id']=$ConfSimpleData['id'];
					if(isset($vat_percent_session) && $vat_percent_session !='') {
						$ConfigSimpleResponse['conf_simple_pro_pice']=($ConfSimpleData['price'] * $vat_percent_session/100)+$ConfSimpleData['price'];
					}else{
						$ConfigSimpleResponse['conf_simple_pro_pice']=$ConfSimpleData['webshop_price'];
					}
					$ConfigSimpleResponse['conf_simple_pro_sku']=$ConfSimpleData['sku'];
					$ConfigSimpleResponse['conf_simple_pro_barcode']=$ConfSimpleData['barcode'];

					if($specialPriceArr!=false){
						if(isset($vat_percent_session) && $vat_percent_session !='') {
							$ConfigSimpleResponse['special_price'] = ($specialPriceArr['special_price'] * $vat_percent_session/100)+$specialPriceArr['special_price'];
						}else{
							$ConfigSimpleResponse['special_price'] = $specialPriceArr['special_price'];
						}
						$ConfigSimpleResponse['special_price_from'] = $specialPriceArr['special_price_from'];
						$ConfigSimpleResponse['special_price_to'] = $specialPriceArr['special_price_to'];
						$ConfigSimpleResponse['display_original'] = $specialPriceArr['display_original'];
					}else{
						$ConfigSimpleResponse['special_price'] = '';
						$ConfigSimpleResponse['special_price_from'] = '';
						$ConfigSimpleResponse['special_price_to'] = '';
						$ConfigSimpleResponse['display_original'] = '';
					}

					if($ConfSimpleData['product_inv_type'] == 'buy'){

						$product_inv = $webshop_obj->getAvailableInventory($ConfSimpleData['id'],$shopcode);
						if(is_numeric($product_inv['available_qty'])){
							if($product_inv['available_qty'] > 0){
								$quantity = $product_inv['available_qty'];
							}else{
								$quantity = 0;
							}
						}

					}else if($ConfSimpleData['product_inv_type'] == 'virtual'){

						$seller_shopcode = 'shop'.$ConfSimpleData['imported_from']; // changed by al  shop_id to imported_from
						$product_inv1 = $webshop_obj->getAvailableInventory($ConfSimpleData['id'],$shopcode,$seller_shopcode);

						if($product_inv1['available_qty'] > 0){
							$quantity = $product_inv1['available_qty'];
						}else{
							$quantity = 0;
						}

					}else if($ConfSimpleData['product_inv_type'] == 'dropship'){

						$seller_shopcode = 'shop'.$ConfSimpleData['imported_from'];  // changed by al  shop_id to imported_from
						//$product_inv2 = $webshop_obj->getAvailableInventory($ConfSimpleData['shop_product_id'],$seller_shopcode);
						$product_inv2 = $webshop_obj->getAvailableInventory($ConfSimpleData['id'],$shopcode,$seller_shopcode);

						if($product_inv2['available_qty'] > 0){
							$quantity = $product_inv2['available_qty'];
						}else{
							$quantity = 0;
						}
					}

					//$ConfigSimpleResponse['conf_simple_pro_inventory']['qty']=$quantity;
					if($prelaunch == 'yes' && $quantity== 0){

						$qty_identifier='product_detail_page_max_qty';
						$webshop_obj = new DbGlobalFeature();
						$get_custom_variable = $webshop_obj->get_custom_variable($shopcode,$qty_identifier);

						if(!empty($get_custom_variable)){
							$ConfigSimpleResponse['conf_simple_pro_inventory']['qty']=$get_custom_variable['value'];
						}else{
							$ConfigSimpleResponse['conf_simple_pro_inventory']['qty'] = 50;

						}


					}else{
						$ConfigSimpleResponse['conf_simple_pro_inventory']['qty']=$quantity;
					}

					if($quantity>0){
						$ConfigSimpleResponse['conf_simple_pro_inventory']['status']='instock';
					}elseif($prelaunch == 'yes' && $quantity== 0){
						$ConfigSimpleResponse['conf_simple_pro_inventory']['status']='instock';
					}else{
						$ConfigSimpleResponse['conf_simple_pro_inventory']['status']='outofstock';
					}

				}else{
					$error='Product not available';
					$ConfigSimpleResponse['conf_simple_pro_inventory']['status']='notavailable';
				}

			}
		}

	}

	if($error != '' ){

		//echo "1111111111111";exit;
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		$message['ConfigSimpleDetails'] = $ConfigSimpleResponse;
		exit(json_encode($message));
	}else{
		//echo "2222222222";exit;
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Conf Simple Product available';
		$message['ConfigSimpleDetails'] = $ConfigSimpleResponse;
		exit(json_encode($message));
	}

});

$app->post('/webshop/get_gallery_images_by_variants', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($shopcode) || empty($shopid) || empty($product_id) )
	{
		$error='Please pass all the mandatory values';

	}else{

		$final_arr = array();

		$webshop_obj = new DbProductFeature();
		$mediaGalleryData = $webshop_obj->getmediaGalleryByVariants($shopcode,$shopid,$product_id,$child_product_id);

		$productData = $webshop_obj->getproductDetailsById($shopcode,$shopid,$product_id);

		$final_arr['mediaGallery']=$mediaGallery=(isset($mediaGalleryData) && $mediaGalleryData!=false ) ? $mediaGalleryData : array() ;
		$final_arr['name']=$productData['name'];
		$final_arr['base_image']=$productData['base_image'];
		$final_arr['media_count']=count($mediaGallery);


	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Product Images Available';
		$message['MediaGalleryData'] = $final_arr;
		exit(json_encode($message));
	}

});

$app->post('/webshop/getProductSku', function (Request $request, Response $response, $args){

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		if($shopcode =='' || $shopid=='' || $term =='' )
		{
			$error='Please pass all the mandatory values';

		}else{
			$webshop_obj = new DbProductFeature();
			$products = $webshop_obj->getProductSku($shopcode,$shopid,$term);

			if($products == false)
			{
				$error='No Products found';
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Products available';
			$message['products'] = $products;
			exit(json_encode($message));
		}

	});

	$app->post('/webshop/getAvailableProducts', function (Request $request, Response $response, $args){

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		if($shopcode =='' || $shopid=='' || ($barcode =='' && $sku =='')  || ($qty =='' || $qty == 0))
		{
			$error='Please pass all the mandatory values';

		}else{
			$webshop_obj = new DbProductFeature();
			$AvailableProducts = $webshop_obj->CheckProductsAvailable($shopcode,$shopid,$barcode,$sku);

			if($AvailableProducts == false)
			{
				$error='No products found';
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Products available';
			$message['AvailableProducts'] = $AvailableProducts;
			exit(json_encode($message));
		}

	});

	$app->post('/webshop/insert_scanned_products', function (Request $request, Response $response, $args){

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		if($shopcode =='' || $shopid=='' || $customer_id =='' || $customer_id == 0 || $parent_id == '' || $product_id == '' )
		{
			$error='Please pass all the mandatory values';

		}else{

			$webshop_obj = new DbProductFeature();
			$cart_obj = new DbCart();
			 // print_r($shopcode);
			 // print_r($parent_id);
			 // print_r($product_id);exit;

			 $VariantInfo=$cart_obj->get_product_variant_details($shopcode,$parent_id,$product_id);
			 // print_r($VariantInfo);exit;

			$product_variants_arr=array();
			$product_variants_str='';

			//print_r($VariantInfo);exit;
			if(is_array($VariantInfo) && count($VariantInfo)>0  && $VariantInfo!=false){
				foreach($VariantInfo  as $value){
					$attr_id=$value['attr_id'];
					$attr_value=$value['attr_value'];

					$AttrData=$cart_obj->getAttributeDetails($attr_id);

					if($AttrData==false){
						$attr_name='';
					}else{
						$attr_name=$AttrData['attr_name'];
					}
			 // print_r($VariantInfo);exit;



					$AttrOptionData=$cart_obj->getAttributeOptionDetails($attr_value);
					if($AttrOptionData==false){
						$attr_option_name='';
					}else{
						$attr_option_name=$AttrOptionData['attr_options_name'];
					}


					if($attr_name!='' && $attr_option_name!=''){
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
			if(isset($product_variants_arr)  && count($product_variants_arr)>0){
				$product_variants = $product_variants_arr;
			}

			$variants =array();
			if(isset($product_variants) && $product_variants != ''){

				foreach($product_variants as $pk=>$single_variant){
					foreach($single_variant as $key=>$val){
						$variants[]=$key.' : '.$val.' ';
					}
				}
			}else{
				$variants[]='';
			}
			if(isset($variants) && $variants !='' && !empty($variants))
			{
				$variants=  implode(', ',$variants);
			}

			$insert_productdata_arr= array(
				"customer_id" => "'".$customer_id."'",
				"product_name" => "'".$product_name."'",
				"barcode" => "'".$barcode."'",
				"sku" => "'".$sku."'",
				"variants" => '"'.$variants.'"',
				"launch_date" => "'".$launch_date."'",
				"qty_scanned" => "'".$qty_scanned."'",
				"webshop_price" => "'".$webshop_price."'",
				"created_at" => time(),
				'ip' => "'".$_SERVER['REMOTE_ADDR']."'"

				);

						// print_r($insert_productdata_arr);exit;


				$insert_product = $webshop_obj->insert_productdata($shopcode,$insert_productdata_arr);

			if($insert_product == false)
			{
				$error='Something went wrong';
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Products Inserted';
			// $message['products'] = $products;
			exit(json_encode($message));
		}

	});


	$app->post('/webshop/scanned_products_listing_new', function (Request $request, Response $response, $args)
	{

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		if($shopcode =='' || $shopid=='' || $customer_id =='')
		{
			$error='Please pass all the mandatory values';

		}else{
			$scanned_products = array();
			// $order_obj = new DbOrders();
			// $ch_obj= new  DbCheckout();
			$webshop_obj = new DbProductFeature();


			$scanned_productsData = $webshop_obj->getScannedProductsData($shopcode,$customer_id);
			$scanned_products_count = $webshop_obj->countgetScannedProductsData($shopcode,$customer_id);

			// print_r($scanned_productsData);exit;die();
			if($scanned_productsData!=false){

				if(isset($scanned_productsData) && count($scanned_productsData)>0){
					foreach($scanned_productsData as $scanned_product){

						$scanned_products[]=$scanned_product;
					}
				}

			}else{
				$error='No Scanned Product available in table';
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['scanned_products_count'] = $scanned_products_count;
			$message['scanned_productsData']=$scanned_products;
			$message['message'] = 'My Scanned products  lisintg..';
			exit(json_encode($message));
		}

	});
	/*endtesting new*/
	$app->post('/webshop/update_scanned_qty', function (Request $request, Response $response, $args)
	{

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		if($shopcode =='' || $shopid=='' || $customer_id =='' || $id =='' || $qty_scanned =='')
		{
			$error='Please pass all the mandatory values';

		}else{
			$webshop_obj = new DbCommonFeature();
			$time = time();
			$ip=$_SERVER['REMOTE_ADDR'];
			$qty_scanned = (isset($qty_scanned) && $qty_scanned != '') ? $qty_scanned : '';
			// $customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';
			$table = 'catlog_builder_scanning';

			$update_column = 'qty_scanned = ?,updated_at = ?, ip = ?';
			$where = 'id = ? ';//and customer_id = ?
			$params = array($qty_scanned, $time, $ip,$id); //, $qty_scanned, $customer_id
			$update_catlog_builder_scanning = $webshop_obj->update_row($shopcode, $table,$update_column,$where,$params);
			// print_r($update_catlog_builder_scanning);die();
			if($update_catlog_builder_scanning == 1)
			{
				$error='No data found';
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Data updated successfully';
			exit(json_encode($message));
		}

	});

	$app->post('/webshop/deleteScannedProduct', function (Request $request, Response $response, $args)
	{

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		if($shopcode =='' || $shopid=='' || $id =='')
		{
			$error='Please pass all the mandatory values';

		}else{
			$webshop_obj = new DbProductFeature();
			// $customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';
			$deleteScannedProduct = $webshop_obj->deleteScannedProduct($shopcode, $id);

			if($deleteScannedProduct == 1)
			{
				$error='No data found';
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Data deleted successfully';
			exit(json_encode($message));
		}

	});

	$app->post('/webshop/deleteAllScannedProduct', function (Request $request, Response $response, $args)
	{

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		if($shopcode =='' || $shopid=='' || $customer_id =='')
		{
			$error='Please pass all the mandatory values';

		}else{
			$webshop_obj = new DbProductFeature();
			// $customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';
			$deleteScannedProduct = $webshop_obj->deleteAllScannedProduct($shopcode, $customer_id);

			if($deleteScannedProduct == 1)
			{
				$error='No data found';
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Data deleted successfully';
			exit(json_encode($message));
		}

	});

	$app->post('/webshop/add_to_catlog_builder', function (Request $request, Response $response, $args)
	{

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		if($shopcode =='' || $shopid=='' || $customer_id =='' || $csv_file =='' )
		{
			$error='Please pass all the mandatory values';

		}else{
			$webshop_obj = new DbProductFeature();

			$insert_catlog_Builder_arr= array(
				"customer_id" => "'".$customer_id."'",
				"csv_file" => "'".$csv_file."'",
				"catalog_name" => "'".$catalog_name."'",
				"customer_name" => "'".$customer_name."'",
				"email" => "'".$email."'",
				"phone_no" => "'".$phone_no."'",
				"show_qtys" => "'".$show_qtys."'",
				"show_retail_price" => "'".$show_retail_price."'",
				"show_coll_name" => "'".$show_coll_name."'",
				"show_style_code" => "'".$show_style_code."'",
				"show_upc" => "'".$show_upc."'",
				"show_csv_qtys" => "'".$show_csv_qtys."'",
				"show_csv_price" => "'".$show_csv_price."'",
				"sort_by" => "'".$sort_by."'",
				"display_currency" => "'".$display_currency."'",
				"created_by" => "'".$customer_id."'",
				"created_by_type" => 1,
				"created_at" => time(),
				'ip' => "'".$_SERVER['REMOTE_ADDR']."'"

				);

			$insert_catlog_id = $webshop_obj->insert_catlog_Builder_data($shopcode,$insert_catlog_Builder_arr);

			if($insert_catlog_id == false)
			{
				$error='Something went wrong';
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Data updated successfully';
			$message['lastInsertId'] = $insert_catlog_id;
			exit(json_encode($message));
		}

	});

	$app->post('/webshop/add_to_catlog_builder_items', function (Request $request, Response $response, $args)
	{

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		if($shopcode =='' || $shopid=='' || $catalog_builder_id =='' || $upc =='' )
		{
			$error='Please pass all the mandatory values';

		}else{
			$webshop_obj = new DbProductFeature();

			$insert_catlog_Builder_items_arr= array(
				"catalog_builder_id" => "'".$catalog_builder_id."'",
				"upc" => "'".$upc."'",
				"product_id" => "'".$product_id."'",
				"parent_id" => "'".$parent_id."'",
				"product_name" => "'".$product_name."'",
				"sku" => "'".$sku."'",
				"variants" => '"'.$variants.'"',
				"launch_date" => "'".$launch_date."'",
				"quantity" => "'".$quantity."'",
				"retail_price" => "'".$retail_price."'",
				"price" => "'".$price."'"

				);

			$insert_catlog_item = $webshop_obj->insert_catlog_Builder_items_data($shopcode,$insert_catlog_Builder_items_arr);

			if($insert_catlog_item == false)
			{
				$error='Something went wrong';
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Data updated successfully';
			$message['lastInsertId'] = $insert_catlog_item;
			exit(json_encode($message));
		}

	});


	// base color

	/*$app->post('/webshop/base_color_data', function (Request $request, Response $response, $args){

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		$product_listing_count =0;
		if($shopcode =='' || $shopid=='')
		{
			$error='Please pass all the mandatory values';
		}else{
			$webshop_obj = new DbProductFeature();
			$base_color_listing = $webshop_obj->getBaseColorData($shopcode,$shopid);
			if($base_color_listing!=false){
				$base_color_listing=$base_color_listing;
			}else{
				$base_color_listing=0;
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Base color available';
			$message['BaseColorList'] = $base_color_listing;
			exit(json_encode($message));
		}
	});*/

	$app->post('/webshop/base_color_data', function (Request $request, Response $response, $args){

		$data = $request->getParsedBody();
		extract($data);

		$error='';
		$product_listing_count =0;
		$variantArray = [];
		if($shopcode =='' || $shopid=='' || $categoryid=='')
		{
			$error='Please pass all the mandatory values';
		}else{

			$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
			$variant_id = $variant_id;
			$this->shopcode = $shopcode;
			$this->shopid = $shopid;
			$this->categoryid = $categoryid;
			$this->customer_type_id = $customer_type_id ?? 1;

			$webshop_obj = new DbProductFeature();
			 $base_color_listing = $webshop_obj->getBaseColorData($shopcode,$shopid);
			//$base_color_listing = $webshop_obj->getBaseColorDatatest($shopcode,$shopid);
			

			//$attr_ids = array_column($base_color_listing, 'id');
			if(is_array($base_color_listing) && count($base_color_listing)>0  && $base_color_listing!=false){
				//print_r($base_color_listing);
				$base_ids = array_column($base_color_listing, 'id');
				$base_color_varient_listing = $webshop_obj->getOptionsByBaseColorIdForMultiple($shopcode,$shopid,$base_ids);
				$variant_match_options_ids = array_column($base_color_varient_listing, 'base_color_id');
				$this->variant_options_ids = array_group($base_color_varient_listing, 'base_color_id');
				$check_varient_id=array();
				foreach($base_color_listing as $variant){
					$color_name = $variant['color_name'];
					$square_color = $variant['square_color'];
					
					if(in_array($variant['id'], $variant_match_options_ids, true)){
						$variantOptionData=$this->variant_options_ids[$variant['id']];
						$varOptionsIds=array_column($variantOptionData, 'id');
						$varOptionsOptionIds=array_column($variantOptionData, 'variant_option_id');
						$attr_value='';
						$base_attr_value='';
						//$varOptionsIds=array();
						foreach ($variantOptionData as $vkey => $vvalue) {
							
							$variant_id=$variant_id;
							/*$ProductCountVar=(int) $webshop_obj->checkProductCountByVariantOption($this->shopcode,$this->shopid,$variant_id,$vvalue['variant_option_id'],$this->categoryid,$customer_type_id);
    						
							if($ProductCountVar!=false && $ProductCountVar>0){*/
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
							// }
	                    }
	                    
	                    $ProductVariantCountMultiple=(int) $webshop_obj->checkProductCountByVariantMultipleBaseColor($this->shopcode,$this->shopid,$variant_id,$varOptionsOptionIds,$this->categoryid,$this->customer_type_id);
	                    // print_r($ProductVariantCountMultiple);
	                    if($ProductVariantCountMultiple!=false && $ProductVariantCountMultiple>0){
	                    	$variantArray[] = $arr;
	                    }
					}

				}
				
				//$variantArray[] = $arr;
			}
		}

		if($error != '' ){
			$message['statusCode'] = '500';
			$message['is_success'] = 'false';
			$message['message'] = $error;
			exit(json_encode($message));
		}else{
			$message['statusCode'] = '200';
			$message['is_success'] = 'true';
			$message['message'] = 'Base color available';
			$message['BaseColorList'] = $variantArray;
			exit(json_encode($message));
		}
	});

	// end base color


?>
