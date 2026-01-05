<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/addtowishlist', function (Request $request, Response $response){

$posted_data = $request->getParsedBody();
extract($posted_data);
$error='';

	if( empty($shopcode) || empty($shopid) || empty($customer_id) ){
		$error='Please enter all mandatory / compulsory fields.';
	}
	else
	{
		if(isset($product_id) && !empty($product_id)){
			$prod_id = $product_id;
		}
		else{
			$prod_id = '';
		}

		$webshop_obj = new DbWishlistFeature();

		$Record = $webshop_obj->getProductExistWishlist($shopcode,$customer_id,$prod_id);
		if($Record > 0){
			$error = "Already exist this product in wishlist.";
		}
		else{
			$save_wishlist = $webshop_obj->addtowishlist($shopcode,$customer_id,$prod_id);
		}

	}

	if($error != ''){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}
	else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Added to wishlist product';
		exit(json_encode($message));
	}

});


$app->get('/webshop/wishlist_getproduct/{shopcode}/{shopid}/{customer_id}[/{product_id}]', function (Request $request, Response $response, $args){
	 $shopcode 	= $args['shopcode'];
	 $shop_id 	= 	$args['shopid'];
	 $customer_id = $args['customer_id'];
	 $product_id = $args['product_id'] ?? '';
	 $error='';

	if($shopcode == '' || $shop_id =='' || $customer_id == '')
	{
		$error='Please pass all the mandatory values';
	}
	else{

		if(isset($product_id) && !empty($product_id)){
			$prod_id = $product_id;
		}
		else{
			$prod_id = '';
		}

		$webshop_obj = new DbWishlistFeature();
		$wishlist_getproduct = $webshop_obj->getProductExistWishlist($shopcode,$customer_id,$prod_id);
		if($wishlist_getproduct)
		{
			$WishlistProduct = $wishlist_getproduct;
		}
		else
		{
			$error = 'No result found';
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
		$message['message'] = 'Wishlist product available';
		$message['search_result'] = $WishlistProduct;
		exit(json_encode($message));
	}
});


$app->get('/webshop/wishlist_deleteproduct/{shopcode}/{shopid}/{wishlist_id}', function (Request $request, Response $response, $args){
	 $shopcode 	= $args['shopcode'];
	 $shop_id 	= 	$args['shopid'];
	 $wishlist_id = $args['wishlist_id'];
	 $error='';

	if($shopcode == '' || $shop_id =='' || $wishlist_id == '')
	{
		$error='Please pass all the mandatory values';
	}
	else{

		$webshop_obj = new DbWishlistFeature();
		$wishlist_existproduct = $webshop_obj->getWishlistProductById($shopcode,$wishlist_id);
		if($wishlist_existproduct)
		{
			$delete_wishlist = $webshop_obj->wishlist_deleteproduct($shopcode,$wishlist_id);
		}
		else
		{
			$error = 'No result found';
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
		$message['message'] = 'Delete record sucessfully.';
		exit(json_encode($message));
	}
});

$app->post('/webshop/mywishlists', function (Request $request, Response $response){

	$data = $request->getParsedBody();
	extract($data);

	$error='';

	if( empty($shopcode) || empty($shopid) || empty($customer_id) ){
		$error='Please enter all mandatory / compulsory fields.';
	}else{
		$lang_code =  (isset($lang_code) ? $lang_code : '');
		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
		$vat_percent_session =  (isset($vat_percent_session) ? $vat_percent_session : '');

		$webshop_obj = new DbWishlistFeature();
		$product_obj = new DbProductFeature();
		$final_arr = array();

		$records = $webshop_obj->getProductExistWishlistByCustomerId($shopcode,$customer_id);
		if($records !=false)
		{

			foreach ($records as $value)
			{
				$productData = $webshop_obj->getproductDetailsById($shopcode,$shopid,$value['product_id'],$lang_code);

				if($productData != false)
				{
					$productData['wishlist_id'] = $value['wishlist_id'];
					$quantity = 0;
					if($productData['product_type'] == 'simple'){
						if($productData['product_inv_type'] == 'buy'){
							$product_inv = $product_obj->getInventory($productData['id'],$shopcode);
							if(is_numeric($product_inv['qty'])) {
								if($product_inv['qty'] > 0){
									$quantity = $product_inv['qty'];
								}
							}
						}else if($productData['product_inv_type'] == 'virtual'){
							$seller_shopcode = 'shop'.$productData['shop_id'];
							$product_inv1 = $product_obj->getInventory($productData['id'],$shopcode,$seller_shopcode);
							if($product_inv1['qty'] > 0){
								$quantity = $product_inv1['qty'];
							}
						}else if($productData['product_inv_type'] == 'dropship'){
							$seller_shopcode = 'shop'.$productData['shop_id'];
							$product_inv2 = $product_obj->getInventory($productData['id'],$shopcode,$seller_shopcode);
							if($product_inv2['qty'] > 0){
								$quantity = $product_inv2['qty'];
							}
						}

						$specialPriceArr = $product_obj->getSpecialPrices($shopcode,$productData['id'],$customer_type_id);
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
							$productData['eu_webshop_price'] = ($productData['price'] * $vat_percent_session/100) + $productData['price'];
						}

					}else if($productData['product_type'] == 'bundle'){
						if(isset($vat_percent_session) && $vat_percent_session !='') {
							$productData['eu_webshop_price'] = ($productData['price'] * $vat_percent_session/100) + $productData['price'];
						}
						$quantity = 1;

					}else{

						$eu_webshop_priceArray = array();
						$specialPriceArray = array();
						$flag = false;


						$configProduct = $product_obj->configurableProduct($shopcode,$shopid,$productData['id']);
						foreach ($configProduct as $conf)
						{

							if(isset($vat_percent_session) && $vat_percent_session !='') {
								$eu_webshop_price = ($conf['price'] * $vat_percent_session/100) + $conf['price'];
								$eu_webshop_priceArray[] = $eu_webshop_price;
							}

							if($conf['product_inv_type'] == 'buy'){
								$product_inv = $product_obj->getInventory($conf['id'],$shopcode);
								if(is_numeric($product_inv['qty'])){
									if($product_inv['qty'] > 0){
										$quantity += $product_inv['qty'];
									}
								}
							}else if($conf['product_inv_type'] == 'virtual'){
								$seller_shopcode = 'shop'.$conf['shop_id'];
								$product_inv1 = $product_obj->getInventory($conf['id'],$shopcode,$seller_shopcode);
								if($product_inv1['qty'] > 0){
									$quantity += $product_inv1['qty'];
								}

							}else if($conf['product_inv_type'] == 'dropship'){
								$seller_shopcode = 'shop'.$conf['shop_id'];
								$product_inv2 = $product_obj->getInventory($conf['id'],$shopcode,$seller_shopcode);
								if($product_inv2['qty'] > 0){
									$quantity += $product_inv2['qty'];
								}
							}

							$specialPrice = $product_obj->getSpecialPrices($shopcode,$conf['id'],$customer_type_id);
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
									$arr1['different_price'] = $conf['webshop_price'];
								}
								$arr1['display_original'] = '';
							}

							$specialPriceArray[] = $arr1;

						}

						if(isset($vat_percent_session) && $vat_percent_session !='') {
							$productData['min_price'] = min($eu_webshop_priceArray);
							$productData['max_price'] = max($eu_webshop_priceArray);
						}else{
							$minPrice = $product_obj->getMinPrice($shopcode,$shopid,$productData['id']);
							$productData['min_price'] = $minPrice['min_price'];
							$productData['max_price'] = $minPrice['max_price'];
						}

						if($flag == true){
							$prices = array_column($specialPriceArray, 'different_price');
							$display_original = array_values(array_filter(array_column($specialPriceArray, 'display_original')));

							$productData['special_price'] = min($prices);
							$productData['special_min_price'] = min($prices);
							$productData['special_max_price'] = max($prices);
							$productData['display_original'] = $display_original[0];
						}
					}

					$productData['product_inventory']['qty']=$quantity;
					if($quantity>0){
						$productData['product_inventory']['status']='instock';
					}else{
						$productData['product_inventory']['status']='outofstock';
					}

					$final_arr[] = $productData;
				}
			}
		}else{
			$error='Wishlist Empty!';
		}

		if(empty($final_arr)) {
			$error='Wishlist Empty!';
		}
	}

	if($error != ''){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'My wishlist available';
		$message['myWishlist'] = $final_arr;
		exit(json_encode($message));
	}

});
