<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/add_to_cart', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($shopcode) || empty($shopid) || empty($product_id) || empty($quantity) )
	{
		$error='Please pass all the mandatory values';

	}else{
		$CartToken='';
		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
		$isPrelaunch =  (isset($isPrelaunch) ? $isPrelaunch : 0);

		$currency_name =  ((isset($currency_name) && $currency_name!='') ? $currency_name : '');
		$currency_code_session =  ((isset($currency_code_session) && $currency_code_session!='') ? $currency_code_session : '');
		$currency_conversion_rate =  ((isset($currency_conversion_rate) && $currency_conversion_rate!='') ? $currency_conversion_rate : '');
		$currency_symbol =  ((isset($currency_symbol) && $currency_symbol!='') ? $currency_symbol : '');
		$default_currency_flag =  ((isset($default_currency_flag) && $default_currency_flag!='') ? $default_currency_flag : '');

		$final_arr = array();
		$bundle_child_details = '';
		$webshop_obj = new DbProductFeature();
		$cart_obj = new DbCart();
		$gbl_feture = new DbGlobalFeature();
		$productData = $webshop_obj->getproductDetailsById($shopcode,$shopid,$product_id);

		if($productData == false)
		{
			$error='Product not available';
		}else{

			$product_type=$productData['product_type'];

			if($product_type=='configurable')
			{
				$ParentProductData=$webshop_obj->getproductDetailsById($shopcode,$shopid,$product_id);
				if($product_type=='configurable' && $conf_simple_pid==''){
					$error='Please select proper product variants';
				}else{
					if(isset($quote_id) && $quote_id!=''){
						$quote_id=$quote_id;
					}else{
						$quote_id='';
					}

					$ConfSimpleData = $webshop_obj->getproductDetailsById($shopcode,$shopid,$conf_simple_pid);

				 	$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$conf_simple_pid,$customer_type_id);

					if($ConfSimpleData['product_inv_type'] == 'buy'){

						$product_inv = $webshop_obj->getAvailableInventory($ConfSimpleData['id'],$shopcode);
						if(is_numeric($product_inv['available_qty'])){
							if($product_inv['available_qty'] > 0){
								$product_quantity = $product_inv['available_qty'];
							}else{
								$product_quantity = 0;
							}
						}

					}else if($ConfSimpleData['product_inv_type'] == 'virtual'){

						$seller_shopcode = 'shop'.$ConfSimpleData['shop_id'];
						$product_inv1 = $webshop_obj->getAvailableInventory($ConfSimpleData['id'],$shopcode,$seller_shopcode);

						if($product_inv1['available_qty'] > 0){
							$product_quantity = $product_inv1['available_qty'];
						}else{
							$product_quantity = 0;
						}

					}else if($ConfSimpleData['product_inv_type'] == 'dropship'){

						$seller_shopcode = 'shop'.$ConfSimpleData['shop_id'];
						$product_inv2 = $webshop_obj->getAvailableInventory($ConfSimpleData['id'],$shopcode,$seller_shopcode);

						if($product_inv2['available_qty'] > 0){
							$product_quantity = $product_inv2['available_qty'];
						}else{
							$product_quantity = 0;
						}
					}
				}

				$total_bundle_qty = 0;
				if(isset($quote_id) && $quote_id!=''){
					$total_bundle_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($shopcode,$shopid,$quote_id,'',$ConfSimpleData['id'],$product_id);
					$quantity_total_check = $quantity + $total_bundle_qty;
				}else{
					$quantity_total_check = $quantity;
				}

				if(isset($vat_percent_session) && $vat_percent_session !='') {

					if($specialPriceArr!=false){

						$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
						$price = $eu_special_price ;
						$total_price = $eu_special_price * $quantity;
					}else{

						$eu_price = ($ConfSimpleData['price'] * $vat_percent_session/100) + $ConfSimpleData['price'];
						$price = $eu_price;
						$total_price = $eu_price * $quantity;
					}
				}else{
					if($specialPriceArr!=false){
						$price = $specialPriceArr['special_price'];
						$total_price = $specialPriceArr['special_price'] * $quantity;
					}else{
						$price = $ConfSimpleData['webshop_price'];
						$total_price=$ConfSimpleData['webshop_price'] * $quantity;
					}
				}

				$product_code=$ParentProductData['product_code'];

				$product_type=$ConfSimpleData['product_type'];

				$product_name=$ConfSimpleData['name'];
				$sku=$ConfSimpleData['sku'];
				$barcode=$ConfSimpleData['barcode'];
				// $price=$ConfSimpleData['webshop_price'];   //webshop_price
				// $total_price=$ConfSimpleData['webshop_price'] * $quantity;
				$parent_product_id=$product_id;
				$cart_product_id=$conf_simple_pid;
				$product_inv_type=$ConfSimpleData['product_inv_type'];
				$pro_shop_id=$ConfSimpleData['shop_id'];
				if(isset($vat_percent_session) && $vat_percent_session !='') {
					$tax_percent=$vat_percent_session;
					$tax_amount=($ConfSimpleData['price'] * $vat_percent_session/100);
				}else{
					$tax_percent=$ConfSimpleData['tax_percent'];
					$tax_amount=$ConfSimpleData['tax_amount'];
				}


				$VariantInfo=$cart_obj->get_product_variant_details($shopcode,$product_id,$conf_simple_pid);

				$product_variants_arr=array();
				$product_variants_str='';
				//print_r($VariantInfo);exit;

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

				if(isset($product_variants_arr) && count($product_variants_arr)>0){
					$product_variants_str=json_encode($product_variants_arr);
				}else{
					$product_variants_str='';
				}

			}else{

				$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$productData['id'],$customer_type_id);

				if(isset($vat_percent_session) && $vat_percent_session !='') {

					if($specialPriceArr!=false){

						$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
						$price = $eu_special_price ;
						$total_price = $eu_special_price * $quantity;
					}else{

						$eu_price = ($productData['price'] * $vat_percent_session/100) + $productData['price'];
						$price = $eu_price;
						$total_price = $eu_price * $quantity;
					}
				}else{

					if($specialPriceArr!=false){
						$price = $specialPriceArr['special_price'];
						$total_price = $specialPriceArr['special_price'] * $quantity;
					}else{
						$price = $productData['webshop_price'];
						$total_price=$productData['webshop_price'] * $quantity;
					}

				}

				//$product_quantity=$quantity;

				$bundle_qty_flag = 1;
				if($productData['product_type']=='bundle'){
					$bundle_child_details = array();
					$bundleProducts = $webshop_obj->bundleProduct($shopcode,$shopid,$product_id);
					foreach ($bundleProducts as $value) {
						if($value['product_type'] == 'configurable'){

							$key = array_search($value['id'], $bundle_child_id);

							$bundleProductsdata =  $webshop_obj->bundleProductById($shopcode,$shopid,$value['id']);
							$product_inv = $webshop_obj->getAvailableInventory($conf_simple_pid[$key],$shopcode);

							$prd = $conf_simple_pid[$key];

							$total_qty = ($quantity * $bundleProductsdata['default_qty']);
							$total_other_qty = 0;
							if(isset($quote_id) && $quote_id!=''){
								$total_other_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($shopcode,$shopid,$quote_id,$product_id,$prd,$value['product_id']);
							}
							$total_qty = $total_qty + $total_other_qty;
							if($total_qty > $product_inv['available_qty']){
								$bundle_qty_flag = 0;
							}

							$bundle_child_details[] = array(
									'bundle_child_product_id' => $value['id'],
									'parent_id' => $value['product_id'],
									'product_id'=> intval($prd),
									'default_qty'=> $value['default_qty'],
									'tax_percent' => $value['tax_percent'],
									'price'=> $value['price'],
									'webshop_price' =>$value['webshop_price'],
							);

						}else{
							$bundleProductsdata = $webshop_obj->bundleProductItemByIdWithInventory($shopcode,$shopid,$product_id,$value['product_id']);

							$total_qty = ($quantity * $bundleProductsdata['default_qty']);
							$total_other_qty = 0;
							if(isset($quote_id) && $quote_id!=''){
								$total_other_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($shopcode,$shopid,$quote_id,$product_id,$value['product_id'],$value['product_parent_id']);
							}

							$total_qty = $total_qty + $total_other_qty;
							if($total_qty > $bundleProductsdata['available_qty']){
								$bundle_qty_flag = 0;
							}

							$bundle_child_details[] = array(
									'bundle_child_product_id' => $value['id'],
									'parent_id' => $value['product_parent_id'],
									'product_id'=> $value['product_id'],
									'default_qty'=> $value['default_qty'],
									'tax_percent' => $value['tax_percent'],
									'price'=> $value['price'],
									'webshop_price' =>$value['webshop_price'],
							);
						}
					}
					$bundle_child_details = json_encode($bundle_child_details);
				}

				$product_quantity = 0;
				if($productData['product_inv_type'] == 'buy'  && $productData['product_type']!='bundle'){

					$product_inv = $webshop_obj->getAvailableInventory($productData['id'],$shopcode);
					if(is_numeric($product_inv['available_qty'])){
						if($product_inv['available_qty'] > 0){
							$product_quantity = $product_inv['available_qty'];
						}else{
							$product_quantity = 0;
						}
					}

				}else if($productData['product_inv_type'] == 'virtual'  && $productData['product_type']!='bundle'){

					$seller_shopcode = 'shop'.$productData['shop_id'];
					$product_inv1 = $webshop_obj->getAvailableInventory($productData['id'],$shopcode,$seller_shopcode);

					if($product_inv1['available_qty'] > 0){
						$product_quantity = $product_inv1['available_qty'];
					}else{
						$product_quantity = 0;
					}

				}else if($productData['product_inv_type'] == 'dropship'  && $productData['product_type']!='bundle'){

					$seller_shopcode = 'shop'.$productData['shop_id'];
					$product_inv2 = $webshop_obj->getAvailableInventory($productData['id'],$shopcode,$seller_shopcode);

					if($product_inv2['available_qty'] > 0){
						$product_quantity = $product_inv2['available_qty'];
					}else{
						$product_quantity = 0;
					}
				}

				$total_bundle_qty = 0;
				if(isset($quote_id) && $quote_id!='' && $productData['product_type']!= 'bundle'){
					$total_bundle_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($shopcode,$shopid,$quote_id,'',$product_id,0);
					$quantity_total_check = $quantity + $total_bundle_qty;
				}else{
					$quantity_total_check = $quantity;
				}

				$cart_product_id=$product_id;
				$product_variants_str='';
				$product_name=$productData['name'];
				$product_code=$productData['product_code'];
				$sku=$productData['sku'];
				$barcode=$productData['barcode'];
				// $price=$productData['webshop_price'];
				// $total_price=$productData['webshop_price'] * $quantity;
				$parent_product_id='';
				$product_type=$productData['product_type'];
				$product_inv_type=$productData['product_inv_type'];
				$pro_shop_id=$productData['shop_id'];
				if(isset($vat_percent_session) && $vat_percent_session !='') {
					$tax_percent=$vat_percent_session;
					$tax_amount=($productData['price'] * $vat_percent_session/100);
				}else{
					$tax_percent=$productData['tax_percent'];
					$tax_amount=$productData['tax_amount'];
				}

			}

			if($customer_id!=''){
				$created_by=$customer_id;
			}else{
				$created_by='';
			}
			/*----------------------------------------------------------*/
			if((($product_quantity<$quantity_total_check ) && $isPrelaunch == 0 && $productData['product_type'] !='bundle')  || (isset($bundle_qty_flag) && $bundle_qty_flag == 0 && $isPrelaunch == 0 && $productData['product_type'] =='bundle' )){
					$error='Product quantity is not available';
				}else{

					if(isset($quote_id) && $quote_id!=''  && $quote_id>0){

						/*---------------Update into quote table-------------------------------------------------*/
						$QuoteId=$quote_id;

					}else{
						/*---------------Insert into quote table-------------------------------------------------*/
						$QuoteId=$cart_obj->add_to_sales_quote($shopcode,$shopid,$session_id,$customer_id);
					}



					if($QuoteId==false || $QuoteId==''){
						$error='Unable to process quote request';
					}else{

						$updateCurrency = $cart_obj->updateQuoteCurrency($shopcode,$QuoteId,$currency_name,$currency_code_session,$currency_conversion_rate,$default_currency_flag,$currency_symbol);

						$checkItemExist=$cart_obj->checkQuoteItemDataExistById($shopcode,$QuoteId, $cart_product_id,$parent_product_id);

						if($checkItemExist==false){
							$cart_item_added=$cart_obj->add_to_sales_quote_item($shopcode,$shopid,$QuoteId,$product_type,$product_inv_type,$cart_product_id,$product_name,$product_code,$quantity,$sku,$barcode,$price,$total_price,$pro_shop_id,$created_by,$parent_product_id,$product_variants_str,$tax_percent,$tax_amount,$isPrelaunch,$bundle_child_details);
						}else{

							$item_id=$checkItemExist['item_id'];
							if($checkItemExist['product_type'] == 'bundle'){
								$cart_obj->removeCartItem($shopcode,$QuoteId,$item_id);
								$cart_item_added=$cart_obj->add_to_sales_quote_item($shopcode,$shopid,$QuoteId,$product_type,$product_inv_type,$cart_product_id,$product_name,$product_code,$quantity,$sku,$barcode,$price,$total_price,$pro_shop_id,$created_by,$parent_product_id,$product_variants_str,$tax_percent,$tax_amount,$isPrelaunch,$bundle_child_details);
							}else{
							$quantity=$checkItemExist['qty_ordered']+$quantity;
							$quantity_total_check=$quantity_total_check+$checkItemExist['qty_ordered'];

							$identifier = 'product_detail_page_max_qty';
							$max_qty_product = $gbl_feture->get_custom_variable($shopcode,$identifier);

							if($product_quantity<$quantity_total_check && $isPrelaunch == 0){
								$error='The requested quantity exceeds the maximum quantity allowed in shopping cart.';
							}elseif($max_qty_product['value'] < $quantity && $isPrelaunch == 1){
								$error='The requested quantity exceeds the maximum quantity allowed in shopping cart.';
							}

							$total_price=$checkItemExist['price']*$quantity;


							if($error == ''){
								$cart_obj->updateCartItemQty($shopcode,$QuoteId,$item_id,$quantity,$total_price,$tax_percent,$tax_amount);
							}
						}
						}

						if($error == ''){
							$QuoteData = $cart_obj->getQuoteDataById($shopcode,$QuoteId);
							if($QuoteData['coupon_code'] !='' || $QuoteData['voucher_code'] !=''){
								$CpData = $cart_obj->addCouponDiscount($shopcode,$shopid,$QuoteData);
							}

							if(isset($quote_id) && $quote_id != '')
							{
								$ch_obj = new DbCheckout();
								$ch_obj->updateTaxAndShippingCharges($shopcode,$shopid,$quote_id);
							}

							$cart_obj->UpateQuoteTotal($shopcode,$QuoteId);  //update cart total after every  addtocart item
						}
					}



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
		$message['message'] = 'Product added to cart succssfully';
		$message['QuoteId'] = $QuoteId;
		exit(json_encode($message));
	}

});

//Cart listing API
$app->post('/webshop/cart_listing', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $session_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$cart_obj = new DbCart();
		$quote_id = (isset($quote_id) && $quote_id != '') ? $quote_id : '';
		$customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';
		$lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';

		$cartData = $cart_obj->getCartListing($shopcode,$session_id,$quote_id,$customer_id,$lang_code);

		if($cartData == false)
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
		$message['message'] = 'Data found';
		$message['cartData'] = $cartData;
		exit(json_encode($message));
	}

});



$app->post('/webshop/remove_cart_item', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $item_id =='' || $quote_id =='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$cart_obj = new DbCart();

		$SingleItemData=$cart_obj->getQuoteItemDataById($shopcode,$item_id);

		$applied_rule_ids=$SingleItemData['applied_rule_ids'];

		$reset_coupon='';


		$cartData = $cart_obj->removeCartItem($shopcode,$quote_id,$item_id);
		/*-----------------remove discount coupon if cart item related to it is removed-------------------------------*/
		if(isset($applied_rule_ids) && $applied_rule_ids!=''){

			$rule_id_found=0;

			$applied_rule_ids_arr=explode(',',$applied_rule_ids);
			$applied_rule_ids_arr=array_filter(array_unique($applied_rule_ids_arr));

			$OtherItems=$cart_obj->getQuoteItems($shopcode,$quote_id);

			if($OtherItems!=false){
				foreach($OtherItems as $Items){
					$oi_applied_rule_ids=$Items['applied_rule_ids'];

					if(isset($oi_applied_rule_ids) && $oi_applied_rule_ids!=''){

						$oir_arr=array_filter(explode(',',$oi_applied_rule_ids));
						$IsExist = !empty(array_intersect($oir_arr, $applied_rule_ids_arr));

						if($IsExist==1){
							$rule_id_found++;
						}
					}

				}
			}

			if($rule_id_found>0){
				$reset_coupon='';
			}else{
				$reset_coupon=1;
			}

		}




		$QuoteData = $cart_obj->getQuoteDataById($shopcode,$quote_id);
		if($QuoteData['coupon_code'] !='' || $QuoteData['voucher_code'] !=''){
			$CpData = $cart_obj->addCouponDiscount($shopcode,$shopid,$QuoteData);
		}


		if(isset($quote_id) && $quote_id != '')
		{
			$ch_obj = new DbCheckout();
			$OrderItems= $ch_obj->get_sales_quote_items($shopcode,$quote_id);

			if(!empty($OrderItems) )
			{
				$ss= $ch_obj->updateTaxAndShippingCharges($shopcode,$shopid,$quote_id);
			}

		}

		$cart_obj->UpateQuoteTotal($shopcode,$quote_id,$reset_coupon);  //update cart total after every  addtocart item

		if($cartData == false)
		{
			$error='Unable to remove';
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
		$message['message'] = 'Cart Item Removed.';
		exit(json_encode($message));
	}

});

//Cart Update API
$app->post('/webshop/update_cart_item', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $session_id =='' || $quote_id =='' || $cart_item_id =='' || $qty =='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$cart_obj = new DbCart();
		$customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';
		$cartData = $cart_obj->updateCartItem($shopcode,$cart_item_id,$quote_id,$qty);

		$QuoteData = $cart_obj->getQuoteDataById($shopcode,$quote_id);
		if($QuoteData['coupon_code'] !='' || $QuoteData['voucher_code'] !=''){
			$CpData = $cart_obj->addCouponDiscount($shopcode,$shopid,$QuoteData);
		}

		if(isset($quote_id) && $quote_id != '')
		{
			$ch_obj = new DbCheckout();
			$ch_obj->updateTaxAndShippingCharges($shopcode,$shopid,$quote_id);
		}
		$cart_obj->UpateQuoteTotal($shopcode,$quote_id);  //update cart total after every  update item

		if($cartData == false)
		{
			$error='Unable to update';
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
		$message['message'] = 'Cart item updated.';
		exit(json_encode($message));
	}

});

// Update Whole Cart API
$app->post('/webshop/update_whole_cart', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $session_id =='' || $quote_id =='' || $cart_items == '')
	{
		$error='Please pass all the mandatory values';

	}else{
		$cart_obj = new DbCart();
		$ch_obj=new DbCheckout();

		$customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';
		$cartData = $cart_obj->updateWholeCartItems($shopcode,$quote_id,$cart_items);

		$QuoteData = $cart_obj->getQuoteDataById($shopcode,$quote_id);
		if($QuoteData['coupon_code'] !='' || $QuoteData['voucher_code'] !=''){
			$CpData = $cart_obj->addCouponDiscount($shopcode,$shopid,$QuoteData);
		}

		$ch_obj->updateTaxAndShippingCharges($shopcode,$shopid,$quote_id);


		$cart_obj->UpateQuoteTotal($shopcode,$quote_id);  //update cart total after every  update item

		if($cartData == false)
		{
			$error='Unable to update';
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
		$message['message'] = 'Cart items updated.';
		exit(json_encode($message));
	}

});

//Cart listing API
$app->post('/webshop/apply_coupon_code', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $coupon_code == '')
	{
		$error='Please pass all the mandatory values';

	}else if($shopid != 1 && $coupon_type ==''){
		$error='Please pass all the mandatory values';
	}else{
		$cart_obj = new DbCart();

		$coupon_code=trim($coupon_code);

		$quote_id = (isset($quote_id) && $quote_id != '') ? $quote_id : '';
		$customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';
		$session_id = (isset($session_id) && $session_id != '') ? $session_id : '';
		$coupon_type = (isset($coupon_type) && $coupon_type != '') ? $coupon_type : '';
		$couponData = $cart_obj->getCouponData($shopcode,$coupon_code,$coupon_type,$customer_id);

		if($shopid == 1){
			if($coupon_type !=""){
				if($coupon_type == 0){
					$coupon_label='Coupon';
				}else if($coupon_type ==1){
					$coupon_label='Voucher';
				}
			}else{
				$coupon_label='Coupon/Voucher';
			}
		}else{
			$coupon_label='Discount';

			if($coupon_type ==0){
				$coupon_label='Discount';
			}else if($coupon_type ==1){
				$coupon_label='Voucher';
			}

		}



		if($couponData == false)
		{
			$error='Invalid '.$coupon_label.' code.';
		}else if(isset($couponData) && $couponData!='' && (strtotime($couponData['end_date'])<strtotime('today midnight'))){
			$error='This '.$coupon_label.' code is expired.';
		}else if($couponData['type']==1 && ($couponData['apply_on_categories']=='' || empty($couponData['apply_on_categories']))) {
			$error='Unable to apply this catalog '.$coupon_label.' code.';
		} else if($couponData['type']==2 && ($couponData['apply_on_products']=='' || empty($couponData['apply_on_products']))) {
			$error='Unable to apply this product '.$coupon_label.' code.';
		}
		else{


			$customer_group=$couponData['apply_to'];

			if( strpos($customer_group, ',') !== false ) {
				$customer_group_arr=explode(',',$customer_group);
			}else{
				$customer_group_arr[]=$customer_group;
			}

			$customer_group_arr=array_filter($customer_group_arr);



			$customer_type_id='';
			if(isset($customer_id) && $customer_id!=0 && $customer_id>0){
				$CustomerInfo=$cart_obj->getCustomerInfo($shopcode,$shopid,$customer_id);
				if($CustomerInfo!=false){
					$customer_type_id=$CustomerInfo['customer_type_id'];
				}
			}


			if(($customer_id==0) && (!in_array(1,$customer_group_arr))){
				$error='This '.$coupon_label.' code not applicable for you';
			}else if(($couponData['type']!=4) && (isset($customer_type_id) && ($customer_type_id>0) && (!in_array($customer_type_id,$customer_group_arr)))){
				$error='This '.$coupon_label.' code not applicable for you.';
			}
			else {
				$cartData = $cart_obj->getCartListing($shopcode,$session_id,$quote_id,$customer_id);

				$result = $cart_obj->updateCouponDiscount($shopcode,$shopid,$quote_id,$couponData,$cartData,'apply_btn');

				if($result == false){
					$error='Unable to apply this '.$coupon_label.' code.';
				}else{
					if(isset($quote_id) && $quote_id != '')
						{
							$ch_obj = new DbCheckout();
							$ch_obj->updateTaxAndShippingCharges($shopcode,$shopid,$quote_id);
						}
					$cart_obj->UpateQuoteTotal($shopcode,$quote_id);
				}

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
		$message['message'] = ''.$coupon_label.' code applied successfully';
		exit(json_encode($message));
	}

});

//Cart listing API
$app->post('/webshop/remove_coupon_code', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $coupon_code == '' || $coupon_type == '' || $quote_id == '')
	{
		$error='Please pass all the mandatory values';

	}else{
		$cart_obj = new DbCart();
		$coupon_label='Discount';
		if($coupon_type ==0){
			$coupon_label='Discount';
		}else if($coupon_type ==1){
			$coupon_label='Voucher';
		}
		$removeData = $cart_obj->removeCouponCode($shopcode,$quote_id,$coupon_code,$coupon_type);
		if(isset($quote_id) && $quote_id != '')
		{
			$ch_obj = new DbCheckout();
			$ch_obj->updateTaxAndShippingCharges($shopcode,$shopid,$quote_id);
		}
		$cart_obj->UpateQuoteTotal($shopcode,$quote_id);  //update cart total after every  addtocart item

		if($removeData == false)
		{
			$error='Unable to remove';
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
		$message['message'] = ''.$coupon_label.' code removed.';
		exit(json_encode($message));
	}

});

//cod api check cod
$app->post('/webshop/cart_listing_check_cod', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $session_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$cart_obj = new DbCart();
		$quote_id = (isset($quote_id) && $quote_id != '') ? $quote_id : '';
		$customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';
		$lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';

		$cartData1 = $cart_obj->getCartListing($shopcode,$session_id,$quote_id,$customer_id,$lang_code);
		//cod
		if($cartData1['cartDetails']['payment_final_charge'] > 0.00){
		// if($cartData->payment_final_charge > 0.00){
			$baseGrandTotal=$cartData1['cartDetails']['base_grand_total'] - $cartData1['cartDetails']['payment_final_charge'];
			$grandTotal=$cartData1['cartDetails']['grand_total'] - $cartData1['cartDetails']['payment_final_charge'];
			$payment_charge=0.00;
			$payment_tax_percent=0.00;
			$payment_tax_amount=0.00;
			$payment_final_charge=0.00;
			//updated quote table
			$common_obj= new DbCommonFeature();
			$table='sales_quote';
			$columns = ' base_grand_total = ?, grand_total = ?, payment_charge = ?, payment_tax_percent = ?, payment_tax_amount = ?, payment_final_charge = ?';
			$where = ' quote_id = ? ';
			$params = array($baseGrandTotal,$grandTotal,$payment_charge,$payment_tax_percent,$payment_tax_amount,$payment_final_charge,$quote_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);

			$cartData = $cart_obj->getCartListing($shopcode,$session_id,$quote_id,$customer_id,$lang_code);
		}else{
			$cartData = $cart_obj->getCartListing($shopcode,$session_id,$quote_id,$customer_id,$lang_code);
		}

		if($cartData == false)
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
		$message['message'] = 'Data found';
		$message['cartData'] = $cartData;
		exit(json_encode($message));
	}

});


//cod api check cod refresh payment charge
$app->post('/webshop/payment_charge_updated', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	if($shopcode =='' || $shopid=='' || $session_id =='' )
	{
		abort('Please pass all the mandatory values');
	}

	$cart_obj = new DbCart();
	$quote_id = (isset($quote_id) && $quote_id != '') ? $quote_id : '';
	$customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';

	$cartData1 = $cart_obj->getCartListing($shopcode,$session_id,$quote_id,$customer_id);
	//cod
	if($cartData1['cartDetails']['payment_final_charge'] > 0.00){
	// if($cartData->payment_final_charge > 0.00){
		$baseGrandTotal=$cartData1['cartDetails']['base_grand_total'] - $cartData1['cartDetails']['payment_final_charge'];
		$grandTotal=$cartData1['cartDetails']['grand_total'] - $cartData1['cartDetails']['payment_final_charge'];
		$payment_charge=0.00;
		$payment_tax_percent=0.00;
		$payment_tax_amount=0.00;
		$payment_final_charge=0.00;
		//updated quote table
		$common_obj=new DbCommonFeature();
		$table='sales_quote';
		$columns = ' base_grand_total = ?, grand_total = ?, payment_charge = ?, payment_tax_percent = ?, payment_tax_amount = ?, payment_final_charge = ?';
		$where = ' quote_id = ? ';
		$params = array($baseGrandTotal,$grandTotal,$payment_charge,$payment_tax_percent,$payment_tax_amount,$payment_final_charge,$quote_id);
		$common_obj->update_row($shopcode, $table, $columns, $where, $params);
	}

	$message['statusCode'] = '200';
	$message['is_success'] = 'true';
	$message['message'] = 'Data found';
	exit(json_encode($message));
});




$app->post('/webshop/update_quote_as_vat_change', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shop_id=='' || $quote_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{


		$webshop_obj = new DbProductFeature();
		$cart_obj = new DbCart();
		$common_obj = new DbCommonFeature();

		$QuoteData = $cart_obj->getQuoteDataById($shopcode,$quote_id);
		$QuoteItemData =$cart_obj->getQuoteItems($shopcode,$quote_id);

		$customer_id = (isset($customer_id) && $customer_id != '') ? $customer_id : '';
		$session_id = (isset($session_id) && $session_id != '') ? $session_id : '';
		$customer_type_id = (isset($customer_type_id )  && $customer_type_id != '' ) ? $customer_type_id : 1;

		$coupon_type = '';
		if($QuoteData['coupon_code'] !=''){
			$coupon_type = 0;
			$coupon_code= $QuoteData['coupon_code'];
		}

		if($QuoteData['voucher_code'] !=''){
			$coupon_type = 1;
			$coupon_code= $QuoteData['voucher_code'];
		}

		$coupon_code=trim($coupon_code);

		if($QuoteData['coupon_code'] !='' || $QuoteData['voucher_code'] !=''){
			$couponData = $cart_obj->getCouponData($shopcode,$coupon_code,$coupon_type,$customer_id);
		}

		if($QuoteItemData!=false){
			foreach($QuoteItemData as $Items){

				$item_id = $Items['item_id'];

				$productData = $webshop_obj->getproductDetailsById($shopcode,$shop_id,$Items['product_id']);
				$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$Items['product_id'],$customer_type_id);

				if(isset($vat_percent) && $vat_percent !='') {
					$tax_percent=$vat_percent;
					$tax_amount=($productData['price'] * $vat_percent/100);
				}else{
					$tax_percent=$productData['tax_percent'];
					$tax_amount=$productData['tax_amount'];
				}

				if(isset($vat_percent) && $vat_percent !='') {

					if($specialPriceArr!=false){
						$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent/100) + $specialPriceArr['special_price'];
						$price = $eu_special_price ;
						$total_price = $eu_special_price * $Items['qty_ordered'];
					}else{
						$eu_price = ($productData['price'] * $vat_percent/100) + $productData['price'];
						$price = $eu_price;
						$total_price = $eu_price * $Items['qty_ordered'];
					}

				}else{
					if($specialPriceArr!=false){
						$price = $specialPriceArr['special_price'];
						$total_price = $specialPriceArr['special_price'] * $Items['qty_ordered'];
					}else{
						$price = $productData['webshop_price'];
						$total_price=$productData['webshop_price'] * $Items['qty_ordered'];
					}

				}

				$common_obj->updateCartItemQty($shopcode,$quote_id,$item_id,$Items['qty_ordered'],$price,$total_price,$tax_percent,$tax_amount);


			}

			$common_obj->updateCartDataBasedOnCustomerType($shopcode,$shop_id,$customer_type_id,$quote_id,$vat_percent);


			if($QuoteData['coupon_code'] !='' || $QuoteData['voucher_code'] !=''){

				$cartData = $cart_obj->getCartListing($shopcode,$session_id,$quote_id,$customer_id);
				$result = $cart_obj->updateCouponDiscount($shopcode,$shop_id,$quote_id,$couponData,$cartData,'apply_btn');

				/* if(isset($quote_id) && $quote_id != '')
				{
					$ch_obj = new DbCheckout();
					$ch_obj->updateTaxAndShippingCharges($shopcode,$shop_id,$quote_id);

					if($result == false){
						$reset_coupon=1;
						$cart_obj->UpateQuoteTotal($shopcode,$quote_id,$reset_coupon);
					}else{
						$cart_obj->UpateQuoteTotal($shopcode,$quote_id);
					}

				} */
			}

		}

		if(isset($quote_id) && $quote_id != '')
		{
			$ch_obj = new DbCheckout();
			$ch_obj->updateTaxAndShippingCharges($shopcode,$shop_id,$quote_id);

			if($result == false){
				$reset_coupon=1;
				$cart_obj->UpateQuoteTotal($shopcode,$quote_id,$reset_coupon);
			}else{
				$cart_obj->UpateQuoteTotal($shopcode,$quote_id);
			}

		}


	}


});
