<?php

use App\Controllers\Webshop\SaveQuoteAddressController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;




$app->post('/webshop/remove_quote', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' ||  $quote_id =='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$ch_obj = new DbCheckout();

		$cartData = $ch_obj->removeQuote($shopcode,$quote_id,$customer_id);

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
		$message['message'] = 'Quote Removed.';
		exit(json_encode($message));
	}

});


$app->post('/webshop/update_quote_customer_id', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' ||  $quote_id =='' || $customer_id =='' || $session_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$ch_obj = new DbCheckout();

		$cartData = $ch_obj->updateQuoteCustomer($shopcode,$quote_id,$session_id,$customer_id,$checkout_method);

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
		$message['message'] = 'Quote Updated.';
		exit(json_encode($message));
	}

});


$app->post('/webshop/place_order', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($shopcode) || empty($shopid) || empty($quote_id) || empty($address_options) || empty($payment_method))
	{
		$error='Please pass all the mandatory values';

	}else{

		$lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';
		$lis_default_language = (isset($lis_default_language) && $lis_default_language != '') ? $lis_default_language : '';

		$ch_obj = new DbCheckout();

		if($address_options!='new' ){
			$AddressInfo=$ch_obj->get_customer_address_by_id($shopcode,$customer_id,$address_options);
		}else{
			$AddressInfo=false;
		}

		if($billing_address_options!='new' ){
			$BillingAddressInfo=$ch_obj->get_customer_address_by_id($shopcode,$customer_id,$billing_address_options);
		}else{
			$BillingAddressInfo=false;
		}

		if($address_options=='new' && (empty($shipping_first_name) || empty($shipping_last_name) || empty($shipping_mobile_no) || empty($shipping_address)  || empty($shipping_country) || empty($shipping_pincode) || empty($shipping_city))){
				$error='Please pass all the shipping address';
		}else if($address_options!='new' && $AddressInfo==false){
			$error='Please select proper shipping address';

		}else{
			$increment_id='';
			/*vat*/
			$company_name='';
			$vat_no='';
			$consulation_no='';
			$res_company_name='';
			$res_company_address='';
			$vat_vies_valid_flag ='';
			/*end vat*/
			$QuoteData=$ch_obj->getQuoteDataById($shopcode,$quote_id);

			$save_in_addressbook=(isset($save_in_addressbook) && $save_in_addressbook=='1')?1:'';
			$same_as_billing=(isset($same_as_billing) && $same_as_billing !='')?$same_as_billing:0;

			if($QuoteData == false){
				$error='Quote not found';
			}else{

				$QuotePayment=$ch_obj->getQuotePaymentDataById($shopcode,$quote_id);

				$checkout_method=$QuoteData['checkout_method'];
				$customer_id=$QuoteData['customer_id'];

				if($checkout_method == ''){
					$checkout_method='guest';
					if($customer_id>0){
						$checkout_method='login';
					}
				}
				$customer_group_id=$QuoteData['customer_group_id'];
				$customer_email=$QuoteData['customer_email'];
				$customer_firstname=$QuoteData['customer_firstname'];
				$customer_lastname=$QuoteData['customer_lastname'];
				$applied_rule_ids=$QuoteData['applied_rule_ids'];
				$coupon_code=$QuoteData['coupon_code'];
				$base_discount_amount=$QuoteData['base_discount_amount'];
				$base_grand_total=$QuoteData['base_grand_total'];
				$base_shipping_amount=$QuoteData['base_shipping_amount'];
				$base_shipping_tax_amount=$QuoteData['base_shipping_tax_amount'];
				$base_subtotal=$QuoteData['base_subtotal'];
				$base_tax_amount=$QuoteData['base_tax_amount'];
				$discount_amount=$QuoteData['discount_amount'];
				$grand_total=$QuoteData['grand_total'];
				$shipping_amount=$QuoteData['shipping_amount'];
				$shipping_tax_amount=$QuoteData['shipping_tax_amount'];
				$shipping_charge=$QuoteData['shipping_charge'];
				$shipping_tax_percent=$QuoteData['shipping_tax_percent'];
				$ship_method_id=$QuoteData['ship_method_id'];
				$ship_method_name=$QuoteData['ship_method_name'];
				$subtotal=$QuoteData['subtotal'];
				$tax_amount=$QuoteData['tax_amount'];
				$total_qty_ordered=$QuoteData['total_qty_ordered'];
				$voucher_code=$QuoteData['voucher_code'];
				$voucher_amount=$QuoteData['voucher_amount'];
				$customer_is_guest=$QuoteData['customer_is_guest'];
				$payment_tax_percent= $QuoteData['payment_tax_percent'];
				$payment_charge = $QuoteData['payment_charge'];
				$payment_tax_amount= $QuoteData['payment_tax_amount'];
				$payment_final_charge= $QuoteData['payment_final_charge'];

				$currency_name=$QuoteData['currency_name'];
				$currency_code_session=$QuoteData['currency_code_session'];
				$currency_conversion_rate=$QuoteData['currency_conversion_rate'];
				$currency_symbol=$QuoteData['currency_symbol'];
				$default_currency_flag=$QuoteData['default_currency_flag'];

				$WebshopFbcData=$ch_obj->getFbcUserShopDataByShopId($shopid);

				if($WebshopFbcData!=false){
					$zumba_shop_flag=$WebshopFbcData['shop_flag'];
				}else{
					$zumba_shop_flag='';
				}

				if (isset($subscribe_newsletter)) {
					if ($subscribe_newsletter == 1) {
						$email = $customer_email;
						// $shopcode;
						// $shopid;
						$webshop_obj = new DbHomeFeature();
						$Record = $webshop_obj->getDataByEmail($shopcode,$email);
						if($Record!=false){
							if($Record['status']==2){
								$updateData = $webshop_obj->updateDataByEmail($shopcode,$email);
							}else{
								$updateData = $webshop_obj->updateDataByEmail($shopcode,$email);
							}

						}else{
							$insertData = $webshop_obj->insertData($shopcode,$email);
						}
					}
				}
				$order_id=$ch_obj->add_to_sales_order($shopcode,$shopid,$checkout_method,$customer_id,$customer_group_id,$customer_email,$customer_firstname,$customer_lastname,$applied_rule_ids,$coupon_code,$base_discount_amount,$base_grand_total,$base_shipping_amount,$base_shipping_tax_amount,$base_subtotal,$base_tax_amount,$discount_amount,$grand_total,$shipping_amount,$shipping_tax_amount,$shipping_charge,$shipping_tax_percent,$subtotal,$tax_amount,$total_qty_ordered,$voucher_code,$voucher_amount,$customer_is_guest,$invoice_self,$payment_tax_percent,$payment_charge,$payment_tax_amount,$payment_final_charge,$ship_method_id,$ship_method_name,$currency_name,$currency_code_session,$currency_conversion_rate,$default_currency_flag,$currency_symbol,'',$lang_code,$lis_default_language);



				if($order_id == false){

				}else{
					$QuoteItems=$ch_obj->get_sales_quote_items($shopcode,$quote_id);
					if(isset($QuoteItems) && count($QuoteItems)>0){

						foreach($QuoteItems as $item){
							$ch_obj->add_to_sales_order_item($shopcode,$shopid,$order_id,$item['product_type'],$item['product_inv_type'],$item['product_id'],$item['product_name'],$item['product_code'],$item['qty_ordered'],$item['sku'],$item['barcode'],$item['price'],$item['total_price'],$item['shop_id'],$item['created_by'],$item['parent_product_id'],$item['product_variants'],$item['estimate_delivery_time'],$item['applied_rule_ids'],$item['tax_percent'],$item['tax_amount'],$item['discount_amount'],$item['discount_percent'],$item['total_discount_amount'],$item['prelaunch'],$coupon_code,$zumba_shop_flag,$item['bundle_child_details'],$ship_method_id,$shipping_tax_percent);

						}

					}

					if($address_options=='new'){
						$save_in_address_book=(isset($save_in_address_book)  && $save_in_address_book==1)?1:0;
						if($shipping_country == 'IN') {
							$shipping_state= $s_state_dp;
						}
					}else{

						if($AddressInfo!=false){
							$save_in_address_book='0';
							$shipping_first_name=$AddressInfo['first_name'];
							$shipping_last_name=$AddressInfo['last_name'];
							$shipping_mobile_no=$AddressInfo['mobile_no'];
							$shipping_city=$AddressInfo['city'];
							$shipping_state=$AddressInfo['state'];
							$shipping_address=$AddressInfo['address_line1'];
							$shipping_address_1=$AddressInfo['address_line2'];
							$shipping_country=$AddressInfo['country'];
							$shipping_pincode=$AddressInfo['pincode'];
						}
					}

					if($billing_address_options=='new'){
						$billing_save_in_address_book=(isset($billing_save_in_address_book)  && $billing_save_in_address_book==1)?1:0;
						if($billing_country == 'IN') {
							$billing_state= $b_state_dp;
						}
					}else{

						if($BillingAddressInfo!=false){
							$billing_save_in_address_book='0';
							$billing_first_name=$BillingAddressInfo['first_name'];
							$billing_last_name=$BillingAddressInfo['last_name'];
							$billing_mobile_no=$BillingAddressInfo['mobile_no'];
							$billing_city=$BillingAddressInfo['city'];
							$billing_state=$BillingAddressInfo['state'];
							$billing_address=$BillingAddressInfo['address_line1'];
							$billing_address_1=$BillingAddressInfo['address_line2'];
							$billing_country=$BillingAddressInfo['country'];
							$billing_pincode=$BillingAddressInfo['pincode'];

							$company_name = ((isset($BillingAddressInfo['company_name']) && $BillingAddressInfo['company_name'] !='')? $BillingAddressInfo['company_name']:'');
							$vat_no = ((isset($BillingAddressInfo['vat_no']) && $BillingAddressInfo['vat_no'] !='')?$BillingAddressInfo['vat_no']:'');
							$consulation_no = ((isset($BillingAddressInfo['consulation_no']) && $BillingAddressInfo['consulation_no'] !='')?$BillingAddressInfo['consulation_no']:'');
							$res_company_name = ((isset($BillingAddressInfo['res_company_name']) && $BillingAddressInfo['res_company_name'] !='')?$BillingAddressInfo['res_company_name']:'');
							$res_company_address = ((isset($BillingAddressInfo['res_company_address']) && $BillingAddressInfo['res_company_address'] !='')?$BillingAddressInfo['res_company_address']:'');
							$vat_vies_valid_flag = ((isset($BillingAddressInfo['vat_vies_valid_flag']) && $BillingAddressInfo['vat_vies_valid_flag'] !='')?$BillingAddressInfo['vat_vies_valid_flag']:'');

							// $billing_email_id=$shipping_email_id;// old 30-10-2012
							$billing_email_id=$customer_email;
						}
					}

					$ch_obj->add_to_sales_order_address($shopcode,$shopid,$order_id,1,$billing_first_name,$billing_last_name,$billing_address_options,$billing_mobile_no,$billing_address,$billing_address_1,$billing_city,$billing_state,$billing_country,$billing_pincode,$billing_save_in_address_book,$company_name,$vat_no,$consulation_no,$res_company_name,$res_company_address,$vat_vies_valid_flag,$same_as_billing);


					$ch_obj->add_to_sales_order_address($shopcode,$shopid,$order_id,2,$shipping_first_name,$shipping_last_name,$address_options,$shipping_mobile_no,$shipping_address,$shipping_address_1,$shipping_city,$shipping_state,$shipping_country,$shipping_pincode,$save_in_address_book,'','','','','','', $same_as_billing);

					$ch_obj->update_vatDetails_in_sales_order($shopcode,$shopid,$order_id,$company_name,$vat_no,$consulation_no,$res_company_name,$res_company_address);

					if($QuotePayment==false){

					}else{
						$payment_method_id=$QuotePayment['payment_method_id'];
						$payment_method=$QuotePayment['payment_method'];
						$payment_method_name=$QuotePayment['payment_method_name'];
						$payment_type=$QuotePayment['payment_type'];
						$currency_code=$currency_code;
						$ch_obj->add_to_sales_order_payment($shopcode,$shopid,$order_id,$payment_method_id,$payment_method,$payment_method_name,$payment_type,$currency_code);
					}


					$FbcData=$ch_obj->getFbcUserIdByShopId($shopid);

					if($FbcData==false){

					}else{
						$fbc_user_id=$FbcData['fbc_user_id'];
						$type=1;  // Webshop
						$ch_obj->add_to_order_log($type,$order_id,$shopid,$fbc_user_id);
					}


					//$DropShipProducts=$ch_obj->getProductsDropShipAndVirtualWithQtyZero($shopcode,$shopid,$order_id);


					$orderData=$ch_obj->getOrderDataById($shopcode,$order_id);
					$increment_id=$orderData['increment_id'];


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
		$message['order_id']=$order_id;
		$message['increment_id']=$increment_id;
		$message['grand_total']=$grand_total;
		$message['message'] = 'Order created successfully.';
		exit(json_encode($message));
	}

});


$app->post('/webshop/check_quote_item_available', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' ||  $quote_id =='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$ch_obj = new DbCheckout();
		$webshop_obj = new DbProductFeature();
		$gbl_feture = new DbGlobalFeature();

		$QuoteItems=$ch_obj->get_sales_quote_items($shopcode,$quote_id);

		$item_qty_exceed_flag = 0;

		if(!empty($QuoteItems) && count($QuoteItems)>0){

			foreach($QuoteItems as $item){

				$identifier = 'product_detail_page_max_qty';
				$max_qty_product = $gbl_feture->get_custom_variable($shopcode,$identifier);


				if($item['product_type'] == 'conf-simple'){

					if($item['prelaunch'] == 1){

						if($item['qty_ordered'] > $max_qty_product){
							$item_qty_exceed_flag = 1;
							break;
						}

					}else{

						$productData = $webshop_obj->getproductDetailsById($shopcode,$shopid,$item['product_id']);

						if($productData['product_inv_type'] == 'buy'){

							$product_inv = $webshop_obj->getAvailableInventory($productData['id'],$shopcode);

								if($item['qty_ordered'] > $product_inv['available_qty']){
									$item_qty_exceed_flag = 1;
									break;
								}


						}else if($productData['product_inv_type'] == 'virtual'){

							$seller_shopcode = 'shop'.$productData['shop_id'];
							$product_inv1 = $webshop_obj->getAvailableInventory($productData['id'],$shopcode,$seller_shopcode);

							if($item['qty_ordered'] > $product_inv1['available_qty']){
								$item_qty_exceed_flag = 1;
								break;
							}

						}else if($productData['product_inv_type'] == 'dropship'){

							$seller_shopcode = 'shop'.$productData['shop_id'];
							$product_inv2 = $webshop_obj->getAvailableInventory($productData['id'],$shopcode,$seller_shopcode);

							if($item['qty_ordered'] > $product_inv2['available_qty']){
								$item_qty_exceed_flag = 1;
								break;
							}
						}



					}

				}else{

					if($item['prelaunch'] == 1){

						if($item['qty_ordered'] > $max_qty_product){
							$item_qty_exceed_flag = 1;
							break;
						}

					}else{

						$productData = $webshop_obj->getproductDetailsById($shopcode,$shopid,$item['product_id']);

						if($productData['product_inv_type'] == 'buy'){

							$product_inv = $webshop_obj->getAvailableInventory($productData['id'],$shopcode);

								if($item['qty_ordered'] > $product_inv['available_qty']){
									$item_qty_exceed_flag = 1;
									break;
								}


						}else if($productData['product_inv_type'] == 'virtual'){

							$seller_shopcode = 'shop'.$productData['shop_id'];
							$product_inv1 = $webshop_obj->getAvailableInventory($productData['id'],$shopcode,$seller_shopcode);

							if($item['qty_ordered'] > $product_inv1['available_qty']){
								$item_qty_exceed_flag = 1;
								break;
							}

						}else if($productData['product_inv_type'] == 'dropship'){

							$seller_shopcode = 'shop'.$productData['shop_id'];
							$product_inv2 = $webshop_obj->getAvailableInventory($productData['id'],$shopcode,$seller_shopcode);

							if($item['qty_ordered'] > $product_inv2['available_qty']){
								$item_qty_exceed_flag = 1;
								break;
							}
						}


					}

				}

			}
		}

	}

	if($item_qty_exceed_flag == 1){
		$error = 'The requested quantity is not available.';
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'All products quantity is available..';
		exit(json_encode($message));
	}


});



$app->post('/webshop/set_checkout_method', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' ||  $quote_id =='' || $checkout_method=='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$ch_obj = new DbCheckout();

		$cartData = $ch_obj->updateCheckoutMethod($shopcode,$quote_id,$checkout_method);

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
		$message['message'] = 'Checkout Method Updated.';
		exit(json_encode($message));
	}

});



$app->post('/webshop/save_quote_address', SaveQuoteAddressController::class);

$app->post('/webshop/payment_methods_listing', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $country_code=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$ch_obj = new DbCheckout();

		$Result = $ch_obj->getPaymentMethods($shopcode,$country_code);

		$payment_methods=array();
		if($Result == false)
		{
			$error='Unable to find payment method';
		}else{
			$count=0;

			foreach($Result as $value){
				$payment_methods[$count]['main_payment_id']=$value['main_payment_id'];
				$payment_methods[$count]['payment_id']=$value['payment_id'];
				$payment_methods[$count]['payment_type']=$value['payment_type'];
				$payment_methods[$count]['integrate_with_ws']=$value['integrate_with_ws'];
				$payment_methods[$count]['display_name']=$value['display_name'];
				$payment_methods[$count]['message']=$value['message'];
				$payment_methods[$count]['payment_gateway_key']=$value['payment_gateway_key'];
				$payment_methods[$count]['payment_gateway']=$value['payment_gateway'];
				if($value['payment_type']==1){
					$MainPayInfo=$ch_obj->getMainShopPaymentDetailsById($shopcode,$value['main_payment_id']);
					if($MainPayInfo!=false){
						$payment_methods[$count]['payment_type_details']=$value['payment_type_details'];
						$payment_methods[$count]['gateway_details']=$value['gateway_details'];
					}else{
						$payment_methods[$count]['payment_type_details']='';
						$payment_methods[$count]['gateway_details']='';
					}

				}else{
					$payment_methods[$count]['payment_type_details']=$value['payment_type_details'];
					$payment_methods[$count]['gateway_details']=$value['gateway_details'];

				}
				$count++;
			}
		}


		//print_r($payment_methods);exit;
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = '';
		$message['PaymentMethods'] = $payment_methods;
		exit(json_encode($message));
	}

});


$app->post('/webshop/set_checkout_payment_method', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' ||  $quote_id =='' || $payment_id=='' || $payment_type==''  )
	{
		$error='Please pass all the mandatory values';

	}else{
		$ch_obj = new DbCheckout();
		$webshop_obj = new DbCommonFeature();

		if($payment_type==1){
			$PayInfo=$ch_obj->getMainShopPaymentDetailsById($payment_id);
		}else{
			$PayInfo=$ch_obj->getWebShopPaymentDetailsById($shopcode,$payment_id);
			$PayInfo2=$ch_obj->getMainShopPaymentDetailsById($payment_id);
		}

		if($PayInfo==false){

		}else{
			$payment_method_id = $payment_id;

			if($payment_type==1){
				$payment_method=$PayInfo['payment_gateway_key'];
				$payment_method_name=$PayInfo['payment_gateway'];

			}else{
				$payment_method_name=$PayInfo2['payment_gateway'];
				$payment_method=$PayInfo2['payment_gateway_key'];
			}

			$gateway_details=$PayInfo['gateway_details'];

		}

		$QuotePayExist=$ch_obj->getQuotePaymentById($shopcode,$quote_id);
		// if payment cod code
		$WebshopFbcData=$ch_obj->getFbcUserShopDataByShopId($shopid);
		$QuoteData=$ch_obj->getQuoteDataById($shopcode,$quote_id);
		if($payment_method=='cod' && $WebshopFbcData['shop_flag']==2){
			if($QuoteData['payment_final_charge'] > 0.00){
				$baseGrandTotal=$QuoteData['base_grand_total'] - $QuoteData['payment_final_charge'];
				$grandTotal=$QuoteData['grand_total'] - $QuoteData['payment_final_charge'];
			}else{
				$baseGrandTotal=$QuoteData['base_grand_total'];
				$grandTotal=$QuoteData['grand_total'];

			}
			$qtyCharge=25;//fixed
			$payment_tax_percent= 18;//tax percentage
			$payment_charge = $qtyCharge * $QuoteData['total_qty_ordered'];
			$payment_tax_amount= ($payment_charge * $payment_tax_percent) / 100;
			$payment_final_charge= $payment_charge + $payment_tax_amount;
			$base_grand_total = $baseGrandTotal + $payment_final_charge;
			$grand_total = $grandTotal + $payment_final_charge;

			//updated quote table
			$common_obj=new DbCommonFeature();
			$table='sales_quote';
			$columns = ' base_grand_total = ?, grand_total = ?, payment_charge = ?, payment_tax_percent = ?, payment_tax_amount = ?, payment_final_charge = ?';
			$where = ' quote_id = ? ';
			$params = array($base_grand_total,$grand_total,$payment_charge,$payment_tax_percent,$payment_tax_amount,$payment_final_charge,$quote_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);


		}else{
			if($QuoteData['payment_final_charge'] > 0.00){
				$baseGrandTotal=$QuoteData['base_grand_total'] - $QuoteData['payment_final_charge'];
				$grandTotal=$QuoteData['grand_total'] - $QuoteData['payment_final_charge'];
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
				$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);

			}
		}

		if($QuotePayExist==false){

			$table='sales_quote_payment';
			$columns = 'quote_id, payment_method_id, payment_method,payment_method_name, payment_type, gateway_details,created_at,ip';
			$values = '?, ?, ?,?, ?, ?, ?, ?';
			$params = array($quote_id, $payment_method_id, $payment_method, $payment_method_name, $payment_type, $gateway_details, time(), $_SERVER['REMOTE_ADDR']);
			$Row = $webshop_obj->add_row($shopcode, $table, $columns, $values, $params);

		}else{
			$Row =$ch_obj->updateQuotePaymentMethod($shopcode,$quote_id,$payment_method_id,$payment_method,$payment_method_name,$payment_type,$gateway_details);

		}


		if($Row == false)
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
		$message['message'] = 'Checkout Payment Method Updated.';
		exit(json_encode($message));
	}

});



$app->post('/webshop/send_order_confirmation_email', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($shopcode) || empty($shopid) ||  empty($order_id) || empty($currency_code) )
	{
		$error='Please pass all the mandatory values';

	}else{
		$lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';

		$ch_obj = new DbCheckout();
		$webshop_obj = new DbCommonFeature();
		$email_obj=new DbEmailFeature();

		$identifier='new-order-confirmation';

		$OrderDetail=$ch_obj->getOrderDataById($shopcode,$order_id);
		$OrderItems=$ch_obj->getOrderItems($shopcode,$order_id);

		if($OrderDetail!=false){
			$webshopName = $webshop_obj->getWebShopName($shopcode,$shopid);
			if($webshopName!=false){
				$webshop_name = $webshopName['org_shop_name'];
			}else{
				$webshop_name = '';
			}

			$ch_obj->updateOrderStatus($shopcode,$order_id,0);  // set order status to 0 = Processing


			$increment_id=$OrderDetail['increment_id'];

			$EmailTo=$OrderDetail['customer_email'];
			$customer_firstname=$OrderDetail['customer_firstname'];
			$subtotal=number_format($OrderDetail['subtotal'],2);
			$coupon_code=$OrderDetail['coupon_code'];
			$base_discount_amount=number_format($OrderDetail['base_discount_amount'],2);
			$voucher_code=$OrderDetail['voucher_code'];
			$voucher_amount=number_format($OrderDetail['voucher_amount'],2);
			$tax_amount=number_format($OrderDetail['tax_amount'],2);
			$grand_total=number_format($OrderDetail['grand_total'],2);
			$checkout_method=$OrderDetail['checkout_method'];
			$shipping_amount=number_format($OrderDetail['shipping_amount'],2);
			$payment_final_charge=number_format($OrderDetail['payment_final_charge'],2);//cod

			$currency_name =  $OrderDetail['currency_name'];
			$currency_code_session = $OrderDetail['currency_code_session'];
			$currency_conversion_rate = $OrderDetail['currency_conversion_rate'];
			$currency_symbol = $OrderDetail['currency_symbol'];
			$default_currency_flag = $OrderDetail['default_currency_flag'];

			$order_date=date('d-M-Y h:i A',$OrderDetail['created_at']);

			$billing_address=$ch_obj->getFormattedOrderAddressById($shopcode,$order_id,1);

			$shipping_address=$ch_obj->getFormattedOrderAddressById($shopcode,$order_id,2);

			$PaymentInfo=$ch_obj->getOrderPaymentDataById($shopcode,$order_id);
			$WebShopPaymentDetailsById = $ch_obj->getWebShopPaymentDetailsById($shopcode,$PaymentInfo['payment_method_id']);

			$WebshopFbcData=$ch_obj->getFbcUserShopDataByShopId($shopid);

			$customer_id=$OrderDetail['customer_id'];

			if($customer_id == 0){
				$customer_group_name = "Not Logged In";
			}else{
				$customer_details = $webshop_obj->getCustomerDetailById($shopcode,$customer_id);
				$Customer_Type =  $webshop_obj->getCustomerType($shopcode,$customer_details['customer_type_id']);
				$customer_group_name = $Customer_Type['name'];
			}

			if($PaymentInfo!=false){

				if($WebshopFbcData['shop_flag']==2){
					$payment_method=$PaymentInfo['payment_method'];
					$payment_method_name=$PaymentInfo['payment_method_name'];

				}else{

					$payment_method=$PaymentInfo['payment_method'];
					if(isset($WebShopPaymentDetailsById['display_name']) && $WebShopPaymentDetailsById['display_name'] != null) {
						$payment_method_name=$WebShopPaymentDetailsById['display_name'];
					}else{
						$payment_method_name=$PaymentInfo['payment_method_name'];
					}
				}

				if(isset($WebShopPaymentDetailsById['message']) && $WebShopPaymentDetailsById['message'] != null){
					$payment_method_name .= $WebShopPaymentDetailsById['message'];
				}

			}else{
				$payment_method_name='';
				$payment_method='';
			}


			if($OrderDetail['checkout_method'] == 'login')
			{

				if($lang_code == 'fr'){
					$login_url= '<p>Vous pouvez vérifier votre commande dans Mes Commandes en <a href="'.$site_url.'customer/my-orders">vous connectant à votre compte</a>.</p>';
				}else if($lang_code == 'it'){
					$login_url= '<p>Puoi controllare il tuo ordine in I miei ordini <a href="'.$site_url.'customer/my-orders">accedendo al tuo account</a>.</p>';
				}else if($lang_code == 'pt'){
					$login_url= '<p>Você pode verificar seu pedido em Meus Pedidos <a href="'.$site_url.'customer/my-orders">fazendo login em sua conta</a>.</p>';
				}else if($lang_code == 'nl'){
					$login_url= '<p>U kunt uw bestelling controleren in Mijn Bestellingen door <a href="'.$site_url.'customer/my-orders">in te loggen op uw account</a>.</p>';
				}else if($lang_code == 'de'){
					$login_url= '<p>Sie können Ihre Bestellung unter „Meine Bestellungen“ überprüfen, <a href="'.$site_url.'customer/my-orders"> indem Sie sich bei Ihrem Konto anmelden</a>.</p>';
				}else if($lang_code == 'es'){
					$login_url= '<p>Puede consultar su pedido en Mis pedidos <a href="'.$site_url.'customer/my-orders">iniciando sesión en su cuenta</a>.</p>';
				}else{
					$login_url= '<p>You can check your order in My Orders by <a href="'.$site_url.'customer/my-orders">logging into your account</a>.</p>';
				}
			}
			else if($OrderDetail['checkout_method'] == 'guest')
			{
				$encoded_id = base64_encode($OrderDetail['order_id']);
				$encoded_id = urlencode($encoded_id );

				if($lang_code == 'fr'){
					$login_url='<a href="'.$site_url.'guest-order/detail/'.$encoded_id.'">Cliquez ici</a> pour v&#233;rifier l&#233;tat de votre commande.</p>';
				}else if($lang_code == 'it'){
					$login_url='<a href="'.$site_url.'guest-order/detail/'.$encoded_id.'">Clicca qui</a> per verificare lo stato del tuo ordine.</p>';
				}else if($lang_code == 'pt'){
					$login_url='<a href="'.$site_url.'guest-order/detail/'.$encoded_id.'">Clique aqui</a> para verificar o status do seu pedido.</p>';
				}else if($lang_code == 'nl'){
					$login_url='<a href="'.$site_url.'guest-order/detail/'.$encoded_id.'">Klik hier</a> om de status van uw bestelling te controleren.</p>';
				}else if($lang_code == 'de'){
					$login_url='<a href="'.$site_url.'guest-order/detail/'.$encoded_id.'">Klicken Sie hier,</a> um den Status Ihrer Bestellung zu &#252;berpr&#252;fen.</p>';
				}else if($lang_code == 'es'){
					$login_url='<a href="'.$site_url.'guest-order/detail/'.$encoded_id.'">Haga clic aqu&#237;</a> para comprobar el estado de su pedido.</p>';
				}else{
					$login_url='<a href="'.$site_url.'guest-order/detail/'.$encoded_id.'">Click Here</a> to check the status of your order.</p>';
				}
			}

			$TemplateContentData = $email_obj->getWebShopEmailTemplateByCode($shopcode,$identifier,$lang_code);

			if($TemplateContentData == false)
			{
				$error='Template not available';
			}else{

				if(isset($TemplateContentData['other_lang_content']) && $emailTemplate['other_lang_content'] !=''){
					$TemplateData=$TemplateContentData['other_lang_content'];
				}else{
					$TemplateData=$TemplateContentData['content'];
				}

				$FromEmail=$email_obj->get_custom_variable($shopcode,'admin_email');

				$Sales_Email_Details=$email_obj->get_custom_variable($shopcode,'sales_admin_email');
				if($Sales_Email_Details==false){
					$store_sales_admin_email='';
				}else{
					$store_sales_admin_email=$Sales_Email_Details['value'];
				}

				if($FromEmail==false){
					$store_admin_email='no-reply@shopinshop.com';
				}else{
					$store_admin_email=$FromEmail['value'];
				}

				$StoreMobile=$email_obj->get_custom_variable($shopcode,'store_mobile');

				if($FromEmail==false){
					$store_phone='no-reply@shopinshop.com';
				}else{
					$store_phone=$StoreMobile['value'];
				}

				$order_item_list='';
				$discount_html='';
				$voucher_html='';
				$payment_html='';
				if($coupon_code!=''){

					if($currency_name !='' && $currency_code_session !=''  && $default_currency_flag != 1){

						$convertedAmount=  $currency_conversion_rate*$OrderDetail['base_discount_amount'];
    					$discount_amt =  $currency_code_session.number_format($convertedAmount,2);

					}else{
						$discount_amt = $currency_code.$base_discount_amount;
					}

					if($lang_code == 'fr'){
						$discount_txt ='Rabais (-)';
					}else if($lang_code == 'it'){
						$discount_txt ='Sconto (-)';
					}else if($lang_code == 'pt'){
						$discount_txt ='Desconto (-)';
					}else if($lang_code == 'nl'){
						$discount_txt ='Korting (-)';
					}else if($lang_code == 'de'){
						$discount_txt ='Rabatt (-)';
					}else if($lang_code == 'es'){
						$discount_txt ='Descuento (-)';
					}else{
						$discount_txt ='Discount (-)';
					}

					$discount_html='<tr>
									<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
										'.$discount_txt.'
									</td>
									<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
										<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$discount_amt.'</span>
									</td>
								</tr>';
				}
				if($voucher_code!=''){

					if($currency_name !='' && $currency_code_session !=''  && $default_currency_flag != 1){

						$convertedAmount=  $currency_conversion_rate*$OrderDetail['voucher_amount'];
    					$voucher_amt =  $currency_code_session.number_format($convertedAmount,2);

					}else{
						$voucher_amt = $currency_code.$voucher_amount;
					}

					if($lang_code == 'fr'){
						$voucher_txt ='Bon (mode de paiement)';
					}else if($lang_code == 'it'){
						$voucher_txt ='Voucher (Metodo di pagamento)';
					}else if($lang_code == 'pt'){
						$voucher_txt ='Voucher (forma de pagamento)';
					}else if($lang_code == 'nl'){
						$voucher_txt ='Voucher (Betaalmethode)';
					}else if($lang_code == 'de'){
						$voucher_txt ='Gutschein (Zahlungsart)';
					}else if($lang_code == 'es'){
						$voucher_txt ='Vale (M&#233;todo de pago)';
					}else{
						$voucher_txt ='Voucher (Payment method)';
					}

					$voucher_html='<tr>
									<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
										'.$voucher_txt.'
									</td>
									<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
										<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$voucher_amt.'</span>
									</td>
								</tr>';
				}

				if($payment_method!='' && $payment_method=='cod' && $payment_final_charge > 0.00){

					if($currency_name !='' && $currency_code_session !=''  && $default_currency_flag != 1){

						$convertedAmount=  $currency_conversion_rate*$OrderDetail['payment_final_charge'];
    					$payment_final_charge_amt =  $currency_code_session.number_format($convertedAmount,2);

					}else{
						$payment_final_charge_amt = $currency_code.$payment_final_charge;
					}

					if($lang_code == 'fr'){
						$Payment_Charge_txt ='Frais de paiement';
					}else if($lang_code == 'it'){
						$Payment_Charge_txt ='Addebito di pagamento';
					}else if($lang_code == 'pt'){
						$Payment_Charge_txt ='Taxa de pagamento';
					}else if($lang_code == 'nl'){
						$Payment_Charge_txt ='Betalingskosten:';
					}else if($lang_code == 'de'){
						$Payment_Charge_txt ='Zahlungsgeb&#220;hr';
					}else if($lang_code == 'es'){
						$Payment_Charge_txt ='cargo de pago';
					}else{
						$Payment_Charge_txt ='Payment Charge';
					}

					$payment_html='<tr>
									<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
									'.$Payment_Charge_txt.'
									</td>
									<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
										<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$payment_final_charge_amt.'</span>
									</td>
								</tr>';
				}

				if(isset($OrderItems) && count($OrderItems)>0){
					// print_r($OrderItems);die();
					$total_itmes = count($OrderItems);
					$total_base_amount = 0;
					foreach($OrderItems as $item){
						$total_base_amount += $item['total_price'];
						$product_variants = '';
						if(isset($item['product_variants'] ) && $item['product_variants']  != ''){
							$product_variants = json_decode($item['product_variants']);
						}
						$variants =array();
						if(isset($product_variants) && $product_variants != ''){

							foreach($product_variants as $pk=>$single_variant){
								foreach($single_variant as $key=>$val){
									$variants[]=$key.' : '.$val.' ';
								}
							}
						}else{
							$variants[]=' ';
						}
						$variant_type = '';
						if(isset($variants) && $variants != '')
						{
							$variant_type = '<p style="font-weight: 500;font-size: 13px;line-height: 15px;color: #787878;">'.implode(', ',$variants).'</p>';
						}

						if($item['product_inv_type'] != 'dropship'){
							$ch_obj->decrementAvailableQty($shopcode,$item['product_id'],$item['qty_ordered']);
						}

						if($currency_name !='' && $currency_code_session !=''  && $default_currency_flag != 1){


							$convertedAmount=  $currency_conversion_rate*$item['price'];
							$price_final =  number_format($convertedAmount,2);

							$convertedAmount2=  $currency_conversion_rate*$item['total_price'];
							$total_price_final =  number_format($convertedAmount2,2);

						}else{

							$price_final = number_format($item['price'],2);
							$total_price_final  = number_format($item['total_price'],2);

						}

						$order_item_list.='	<tr>

									<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb;text-align:left">
										<p style="font-family:Verdana,Arial;font-weight:bold;margin:0 0 5px 0;color:#636363;font-style:normal;text-transform:uppercase;line-height:1.4;font-size:14px;float:left;width:100%;display:block">'.$item['product_name'].'</p> '.$variant_type.'
									</td>
									<td style="text-align:center;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">'.$item['qty_ordered'].'</td>
									<td style="text-align:right;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">
										<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$price_final.'</span>
									</td>
									<td style="text-align:right;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">
										<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$total_price_final.'</span>
									</td>
								</tr>';
					}

				}

				if($currency_name !='' && $currency_code_session !=''  && $default_currency_flag != 1){

					$convertedAmount=  $currency_conversion_rate*$total_base_amount;
					$total_base_amount_final =  $currency_code_session.number_format($convertedAmount,2);

					$convertedAmount2=  $currency_conversion_rate*$OrderDetail['tax_amount'];
					$tax_amount_final =  $currency_code_session.number_format($convertedAmount2,2);

					$convertedAmount3=  $currency_conversion_rate*$OrderDetail['shipping_amount'];
					$shipping_amount_final =  $currency_code_session.number_format($convertedAmount3,2);

					$convertedAmount4=  $currency_conversion_rate*$OrderDetail['subtotal'];
					$subtotal_final =  $currency_code_session.number_format($convertedAmount4,2);

					$convertedAmount5=  $currency_conversion_rate*$OrderDetail['grand_total'];
					$grand_total_final =  $currency_code_session.number_format($convertedAmount5,2);


				}else{
					$total_base_amount_final = $currency_code.$total_base_amount;
					$tax_amount_final = $currency_code.$tax_amount;
					$shipping_amount_final = $currency_code.$shipping_amount;
					$subtotal_final = $currency_code.$subtotal;
					$grand_total_final =$currency_code.$grand_total;

				}


				if($lang_code == 'fr'){
					$item_price_txt ='Prix('.$total_itmes.' articles) (Taxes incluses)';
					$item_txt = 'Articles de votre commande';
					$Qty_txt ='Quantit&#237;';
					$Price_txt = 'Prix';
					$TotalPrice_txt  ='Prix total';
					$Taxes_txt = 'Imp&ograve;ts';
					$shipping_txt = 'Exp&#237;dition et manutention';
					$Subtotal_txt = 'Total';
					$grand_total_txt = 'Total';

				}else if($lang_code == 'it'){
					$item_price_txt = 'Prezzo('.$total_itmes.' articoli) <br> (Compreso di tasse)';
					$item_txt = 'Articoli nel tuo ordine';
					$Qty_txt ='Quantit&#224;';
					$Price_txt = 'Prezzo';
					$TotalPrice_txt  ='Prezzo totale';
					$Taxes_txt = 'Le tasse';
					$shipping_txt = 'Spedizione &amp; Gestione';
					$Subtotal_txt = 'totale parziale';
					$grand_total_txt = 'Somma totale';

				}else if($lang_code == 'pt'){
					$item_price_txt ='Pre&ccedil;o('.$total_itmes.' itens) <br> (incluindo impostos)';
					$item_txt = 'Itens em seu pedido';
					$Qty_txt ='Quantidade';
					$Price_txt = 'Pre&ccedil;o';
					$TotalPrice_txt  ='Pre&ccedil;o total';
					$Taxes_txt = 'Impostos';
					$shipping_txt = 'Envio e manuseio';
					$Subtotal_txt = 'Subtotal';
					$grand_total_txt = 'Total geral';

				}else if($lang_code == 'nl'){
					$item_price_txt ='Prijs('.$total_itmes.' stuks) <br> (Inclusief belastingen)';
					$item_txt = 'Artikelen in je bestelling';
					$Qty_txt ='Hoeveelheid';
					$Price_txt = 'Prijs';
					$TotalPrice_txt  ='Totale prijs';
					$Taxes_txt = 'Belastingen';
					$shipping_txt = 'Verzending &amp; Behandeling';
					$Subtotal_txt = 'Subtotaal';
					$grand_total_txt = 'Eindtotaal';

				}else if($lang_code == 'de'){
					$item_price_txt = 'Preis('.$total_itmes.' Artikel) <br> (Inklusive Steuern)';
					$item_txt = 'Artikel in Ihrer Bestellung';
					$Qty_txt ='Menge';
					$Price_txt = 'Preis';
					$TotalPrice_txt  ='Gesamtpreis';
					$Taxes_txt = 'Steuern';
					$shipping_txt = 'Versand &amp; Bearbeitung';
					$Subtotal_txt = 'Zwischensumme';
					$grand_total_txt = 'Gesamtsumme';

				}else if($lang_code == 'es'){
					$item_price_txt = 'Precio('.$total_itmes.' art&#237;culos) <br> (Impuestos incluidos)';
					$item_txt = 'Art&#237;culos en tu pedido';
					$Qty_txt ='Cantidad';
					$Price_txt = 'Precio';
					$TotalPrice_txt  ='Precio total';
					$Taxes_txt = 'Impuestos';
					$shipping_txt = 'Env&#237;o y manejo';
					$Subtotal_txt = 'Total parcial';
					$grand_total_txt = 'Gran total';
				}else{
					$item_price_txt = 'Price('.$total_itmes.' items) <br> (Inclusive of taxes)';
					$item_txt = 'Items <span class="il">in</span> your <span class="il">order</span>';
					$Qty_txt ='Qty';
					$Price_txt = 'Price';
					$TotalPrice_txt  ='Total Price';
					$Taxes_txt = 'Taxes';
					$shipping_txt = 'Shipping &amp; Handling';
					$Subtotal_txt = 'Subtotal';
					$grand_total_txt = 'Grand Total';
				}

				$order_items='<tr>
					<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:0;margin:0">
						<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:10px 15px;margin:0">
							<thead>
								<tr>

									<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:left;font-size:11px">
									'.$item_txt.'
									</th>
									<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
									'.$Qty_txt.'
									</th>
									<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">
									'.$Price_txt.'
									</th>
									<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">
									'.$TotalPrice_txt.'
									</th>
								</tr>
							</thead>
							<tbody>
							'.$order_item_list.'
							</tbody>

						</table>
					</td>
				</tr>
				<tr>
					<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:0;margin:0">
						<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0;border-top:1px dashed #c3ced4;border-bottom:1px dashed #c3ced4">
							<tbody>
								<tr>
									<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:20px 15px;margin:0;text-align:right;line-height:20px">
										<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0">
											<tbody>
											<tr style="padding-bottom:5px">
													<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
													'.$item_price_txt.'
													</td>
													<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$total_base_amount_final.'</span>
													</td>
												</tr>
												<tr style="padding-bottom:5px">
													<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
													'.$Taxes_txt.'
													</td>
													<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$tax_amount_final.'</span>
													</td>
												</tr>
												<tr style="padding-bottom:5px">
													<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
													'.$shipping_txt.'
													</td>
													<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$shipping_amount_final.'</span>
													</td>
												</tr>
												'.$discount_html.'
												<tr>
													<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
													'.$Subtotal_txt.'
													</td>
													<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$subtotal_final.'</span>
													</td>
												</tr>
												'.$voucher_html.'
												'.$payment_html.'
												<tr>
													<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														<strong>'.$grand_total_txt.'</strong>
													</td>
													<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														<strong><span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$grand_total_final.'</span></strong>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>';

				if($checkout_method=='guest'){
					$oid= base64_encode($OrderDetail['order_id']);
					$encoded_oid = urlencode($oid);
					$guest_order_url=$site_url.'guest-order/detail/'.$encoded_oid;

					if($lang_code == 'fr'){
						$customer_note="<p>Si vous souhaitez annuler ou retourner votre commande, veuillez <a href='".$guest_order_url."' target='_blank'>Cliquez ici.</a></p>";
					}else if($lang_code == 'it'){
						$customer_note="<p>Se desideri annullare o restituire il tuo ordine, per favore <a href='".$guest_order_url."' target='_blank'>clicca qui.</a></p>";
					}else if($lang_code == 'pt'){
						$customer_note="<p>Se pretender cancelar ou devolver a sua encomenda, por favor please <a href='".$guest_order_url."' target='_blank'>Clique aqui.</a></p>";
					}else if($lang_code == 'nl'){
						$customer_note="<p>Als u uw bestelling wilt annuleren of retourneren, alstublieft <a href='".$guest_order_url."' target='_blank'>Klik hier.</a></p>";
					}else if($lang_code == 'de'){
						$customer_note="<p>Wenn Sie Ihre Bestellung stornieren oder zur&#252;cksenden m&ouml;chten, bitte <a href='".$guest_order_url."' target='_blank'>Klick hier.</a></p>";
					}else if($lang_code == 'es'){
						$customer_note="<p>Si desea cancelar o devolver su pedido, por favor <a href='".$guest_order_url."' target='_blank'>haga clic aqu&#237;.</a></p>";
					}else{
						$customer_note="<p>If you want to cancel or return you order, please <a href='".$guest_order_url."' target='_blank'>click here.</a></p>";
					}

				}else{
					$customer_note='';
				}
				$websopname = 	strtoupper($webshop_name);
				$TempVars=array('##LOGIN_URL##','##STORE_EMAIL##','##STORE_PHONE##','##INCREMENT_ID##','##ORDER_DATE##','##CUSTOMER_NOTE##','##ORDER_ITEMS##','##BILLING_ADDRESS##','##SHIPPING_ADDRESS##','##PAYMENT_METHOD##','##WEBSHOPNAME##','##CUSTOMER_GROUP##');

				$DynamicVars=array($login_url,$store_sales_admin_email,$store_phone,$increment_id,$order_date,$customer_note,$order_items,$billing_address,$shipping_address,$payment_method_name, $websopname,$customer_group_name);

				//print_R($DynamicVars);exit;

				$CommonVars=array($site_logo, $webshop_name);

				$emailSendStatusFlag=$email_obj->get_email_code_status($shopcode,$identifier);
				if($emailSendStatusFlag==1){
					$email_send =  $email_obj->sendCommonHTMLEmail($shopcode,$EmailTo,$identifier,$TempVars,$DynamicVars,$increment_id,'',$CommonVars,$lang_code);

					if($email_send == false){
						$error = 'Unable to send email' ;
					}else{
						$ch_obj->updateOrderEmailSentNotification($shopcode,$order_id);
					}
				}

				if($WebshopFbcData['shop_flag']==2){
					if($emailSendStatusFlag==1){
						$admin_email_send1 = $email_obj->sendCommonHTMLEmail($shopcode,'anu@bcod.co.in',$identifier,$TempVars,$DynamicVars,$increment_id,'',$CommonVars,$lang_code);
						$admin_email_send2 = $email_obj->sendCommonHTMLEmail($shopcode,'accounts@whuso.in',$identifier,$TempVars,$DynamicVars,$increment_id,'',$CommonVars,$lang_code);
						$admin_email_send3 = $email_obj->sendCommonHTMLEmail($shopcode,'heeral@whuso.in',$identifier,$TempVars,$DynamicVars,$increment_id,'',$CommonVars,$lang_code);
						$admin_email_send4 = $email_obj->sendCommonHTMLEmail($shopcode,'usha@bcod.co.in',$identifier,$TempVars,$DynamicVars,$increment_id,'',$CommonVars,$lang_code);
					}
				}
				//else{
					//send email to sales_admin_email

					$SalesAdminEmail=$email_obj->get_custom_variable($shopcode,'sales_admin_email');

					if($SalesAdminEmail==false){
						$sales_admin_email='';
						$admin_email_send='';
					}else{
						$sales_admin_email=$SalesAdminEmail['value'];
						if($emailSendStatusFlag==1){
							$admin_email_send =  $email_obj->sendCommonHTMLEmail($shopcode,$sales_admin_email,$identifier,$TempVars,$DynamicVars,$increment_id,'',$CommonVars,$lang_code);
						}
					}

				//}

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
		$message['message'] = 'Email sent successfully';

		exit(json_encode($message));
	}

});


$app->post('/webshop/generate_b2b_order_for_webshop', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($shopcode) || empty($shopid) ||  empty($order_id))
	{
		$error='Please pass all the mandatory values';

	}else{
		$lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';

		$ch_obj=new DbCheckout();
		$product_obj=new DbProductFeature();

		$shop_order_items=array();
		$OtherShops=$ch_obj->getShopsForBTwoBOrders($shopcode,$shopid,$order_id);
		if($OtherShops!=false){
			foreach($OtherShops as $shop){
				$seller_shop_id=$shop['shop_id'];

				$Products=$ch_obj->getProductsDropShipAndVirtualWithQtyZero($shopcode,$shopid,$order_id,$seller_shop_id);

				$total_qty_ordered=0;
				$total_price=0;
				$total_price_excl_special = 0;
				$discount_amout_special_price  = 0;
				if($Products!=false){

					foreach($Products as $value){
						$shop_order_items[$seller_shop_id]['item_ids'][]=$value['item_id'];
						$total_qty_ordered+=$value['qty_ordered'];

						$shop_product_id=$value['shop_product_id'];
						$seller_shopcode='shop'.$seller_shop_id;

						$checkSpecialPrice = $ch_obj->getSpecialPricingB2b($seller_shopcode,$shopid,$value['shop_product_id']);

						$SellerProductData=$product_obj->getproductDetailsByShopCode($seller_shopcode,$shop_product_id);
						if($SellerProductData!=false) {
							if($checkSpecialPrice != false){
								$total_price+=($value['qty_ordered'] * $checkSpecialPrice['special_price']);

								if($SellerProductData['price'] >= $checkSpecialPrice['special_price']){
									$special_dicount_amt = $SellerProductData['price'] - $checkSpecialPrice['special_price'];
								}else{
									$special_dicount_amt = 0;
								}
								$discount_amout_special_price +=($value['qty_ordered'] * $special_dicount_amt);
							}else{
								$total_price+=($value['qty_ordered'] * $SellerProductData['price']);
								$total_price_excl_special+=($value['qty_ordered'] * $SellerProductData['price']);

							}
						}
						//$total_price+=$value['total_price'];
					}
				}

				$shop_order_items[$seller_shop_id]['total_qty_ordered']=$total_qty_ordered;
				$shop_order_items[$seller_shop_id]['total_price']=$total_price;
				$shop_order_items[$seller_shop_id]['total_price_excl_special']=$total_price_excl_special;
				$shop_order_items[$seller_shop_id]['discount_amout_special_price']=$discount_amout_special_price;

			}

			$OrderDetail=$ch_obj->getOrderDataById($shopcode,$order_id);

			$SellerFbcUserData=$ch_obj->getFbcUserIdByShopId($seller_shop_id);
			//$SellerShopData=$ch_obj->getFbcUserIdByShopId($seller_shop_id);

			$WebshopFbcData=$ch_obj->getFbcUserShopDataByShopId($shopid);
			$SellerFbcData=$ch_obj->getFbcUserShopDataByShopId($seller_shop_id);

			$seller_shop_currency_code=$SellerFbcData['currency_code'];
			$buyer_shop_currency_code=$WebshopFbcData['currency_code'];


			$customer_firstname=$OrderDetail['customer_firstname'];
			$customer_lastname=$OrderDetail['customer_lastname'];

			$customer_is_guest=$OrderDetail['customer_is_guest'];

			$seller_fbc_user_id=$SellerFbcData['fbc_user_id'];

			$webshop_name=$SellerFbcData['org_shop_name'];

			$seller_email=$SellerFbcUserData['email'];



			if($SellerFbcData!=false){
				$seller_shopcode='shop'.$seller_shop_id;

				if(isset($shop_order_items) && count($shop_order_items)>0){
					foreach($shop_order_items as $seller_shop_id=>$value){

						$order_item_ids=$value['item_ids'];
						$item_ids=implode(',',$order_item_ids);


						$b2b_customers_details = $ch_obj->get_b2b_customers_details($seller_shopcode,$seller_shop_id,$shopid);

						//echo '<pre>'.print_r($b2b_customers_details, '\n').'</pre>';

						/*if($buyer_shop_currency_code!=$seller_shop_currency_code){
							$total_qty_ordered=$value['total_qty_ordered'];
							$total_price=$ch_obj->sis_convert_currency($buyer_shop_currency_code,$seller_shop_currency_code,$value['total_price']);
							$base_grand_total=$total_price;
							$base_subtotal=$total_price;
							$subtotal=$total_price;
							$grand_total=$total_price;
						}else{*/
							$total_qty_ordered=$value['total_qty_ordered'];
							$total_price=$value['total_price'];
							$base_grand_total=$total_price;
							$base_subtotal=$total_price;
							$subtotal=$total_price;
							$grand_total=$total_price;
						//}

						$discount_amount=0;
						$discount_percent=0;
						if((isset($b2b_customers_details['dropship_discount']) && $b2b_customers_details['dropship_discount']>0) && $subtotal>0) {
							$discount_amount = ($b2b_customers_details['dropship_discount'] / 100) * $total_price_excl_special;
							$grand_total=$subtotal-$discount_amount;

							$base_grand_total=$grand_total;
							$discount_percent=$b2b_customers_details['dropship_discount'];
						}
						//echo $total_qty_ordered."---".$subtotal."-----".$grand_total."----".$discount_percent."---".$discount_amount; exit;

					$b2b_order_id=$ch_obj->create_b2b_order_for_webshop($shopid,$order_id,$seller_shopcode,$seller_shop_id,$customer_firstname,$customer_lastname,$customer_is_guest,$subtotal,$grand_total,$base_subtotal,$base_grand_total,$total_qty_ordered,$discount_percent,$discount_amount,$discount_amout_special_price);

					$total_tax_amount = 0;

						if($b2b_order_id != false) {


							$type=2;  // Webshop to B2b
							$ch_obj->add_to_order_log($type,$b2b_order_id,$seller_shop_id,$seller_fbc_user_id);

							$OrderItems=$ch_obj->getB2BOrderItemsByIds($shopcode,$shopid,$order_id,$item_ids);

							if($OrderItems!=false){

								foreach($OrderItems as $item){
									$tax_percent = 0;
									$tax_amount = 0;

									$special_price_flag = 0;
									$sp_original_price = 0.00;

									$BuyerProductData=$product_obj->getproductDetailsByShopCode($shopcode,$item['product_id']);
									if($BuyerProductData!=false) {
										$shop_product_id=$BuyerProductData['shop_product_id'];
										$SellerProductData=$product_obj->getproductDetailsByShopCode($seller_shopcode,$shop_product_id);
										if($SellerProductData!=false){

											$checkSpecialPrice = $ch_obj->getSpecialPricingB2b($seller_shopcode,$shopid,$shop_product_id);
											if($checkSpecialPrice != false){
												$special_price_flag = 1;
											}

											$product_id=$shop_product_id;
											$parent_product_id=$SellerProductData['parent_id'];
											$product_code=$SellerProductData['product_code'];
											$sku=$SellerProductData['sku'];
											$barcode=$SellerProductData['barcode'];
											$product_name = $SellerProductData['name'];
											//$item_price = $SellerProductData['price'];
											//$item_total_price = ($SellerProductData['price'] * $item['qty_ordered']);

											if($special_price_flag == 1){
												$item_price = $checkSpecialPrice['special_price'];
												$item_total_price = ($checkSpecialPrice['special_price'] * $item['qty_ordered']);

											}else{
												$item_price = $SellerProductData['price'];
												$item_total_price = ($SellerProductData['price'] * $item['qty_ordered']);
											}

											$tax_percent = $SellerProductData['tax_percent'];
											$sp_original_price = $SellerProductData['price'];

										}else{
											$parent_product_id=$item['parent_product_id'];
											$product_id=$item['product_id'];
											$product_code=$item['product_code'];
											$sku=$item['sku'];
											$barcode=$item['barcode'];
											$product_name = $item['product_name'];
											$item_price = $item['price'];
											$item_total_price = $item['total_price'];
											$tax_percent = $item['tax_percent'];
										}

									}else {
										$parent_product_id=$item['parent_product_id'];
										$product_id=$item['product_id'];
										$product_code=$item['product_code'];
										$sku=$item['sku'];
										$barcode=$item['barcode'];
										$product_name = $item['product_name'];
										$item_price = $item['price'];
										$item_total_price = $item['total_price'];
										$tax_percent = $item['tax_percent'];
									}

									if($b2b_customers_details['tax_exampted'] == 2 ) {
										if($item_price > 0.00 && $discount_percent > 0.00 && $special_price_flag == 0) {
											$pro_price_excl_tax = $item_price - ($item_price * $discount_percent)/100;
										} else {
											$pro_price_excl_tax = $item_price;
										}

										if($tax_percent > 0.00 && $pro_price_excl_tax > 0.00) {
											$tax_amount = ($pro_price_excl_tax*$tax_percent)/100;

											$total_tax_amount = $total_tax_amount+($tax_amount*$item['qty_ordered']);
										}
									}

									$special_price_original_price = ($special_price_flag == 1)?$sp_original_price:'';

									$ch_obj->add_to_b2b_order_item($seller_shopcode,$seller_shop_id,$b2b_order_id,$item['product_type'],$product_id,$product_name,$product_code,$item['qty_ordered'],$sku,$barcode,$item_price,$item_total_price,$item['created_by'],$parent_product_id,$item['product_variants'],'',$tax_percent,$tax_amount,0,$shopid,$special_price_flag,$special_price_original_price);



									$CheckProduct=$product_obj->checkInventorySource($item['product_id'],$shopcode,$seller_shopcode);
									if($CheckProduct==false){

									}else{
										$inv_db_source=$CheckProduct['db'];
										$ch_obj->decrementAvailableQty($inv_db_source,$product_id,$item['qty_ordered']);
									}

								}


								if($total_tax_amount > 0) {
									$grandtotal_updated = $grand_total+$total_tax_amount;
									$ch_obj->update_b2b_order_for_webshop($shopid,$b2b_order_id,$seller_shopcode,$seller_shop_id,$total_tax_amount,$total_tax_amount,$grandtotal_updated,$grandtotal_updated);
								}

							}



								/*************************Send email to b2b  seller admin***********************************************************/

								$email_obj=new DbEmailFeature();



								$identifier='new-dropship-order-confirmation';

								$OrderDetail=$ch_obj->getB2BOrderDataByOrderId($seller_shopcode,$b2b_order_id);
								$OrderItems=$ch_obj->getB2BOrderItemsByOrderId($seller_shopcode,$seller_shop_id,$b2b_order_id);

								if($OrderDetail!=false){

									$increment_id=$OrderDetail['increment_id'];

									$EmailTo=$seller_email;
									$customer_firstname=$OrderDetail['customer_firstname'];
									$subtotal=$OrderDetail['subtotal'];

									$grand_total=$OrderDetail['grand_total'];

									$order_date=date('d-M-Y h:i A',$OrderDetail['created_at']);

									$billing_address=$ch_obj->getFormattedOrderAddressById($shopcode,$order_id,1);

									$shipping_address=$ch_obj->getFormattedOrderAddressById($shopcode,$order_id,2);



									$TemplateContentData = $email_obj->getWebShopEmailTemplateByCode($shopcode,$identifier,$lang_code);

									if($TemplateContentData == false)
									{
										$error='Template not available';
									}else{
										if(isset($TemplateContentData['other_lang_content']) && $emailTemplate['other_lang_content'] !=''){
											$TemplateData=$TemplateContentData['other_lang_content'];
										}else{
											$TemplateData=$TemplateContentData['content'];
										}

										$FromEmail=$email_obj->get_custom_variable($shopcode,'admin_email');

										if($FromEmail==false){
											$store_admin_email='no-reply@shopinshop.com';
										}else{
											$store_admin_email=$FromEmail['value'];
										}



										$order_item_list='';
										$discount_html='';
										$voucher_html='';
										if($coupon_code!=''){
											$discount_html='';
										}
										if($voucher_code!=''){
											$voucher_html='';
										}


										if(isset($OrderItems) && count($OrderItems)>0){
											foreach($OrderItems as $item){

												if($item['shop_id']==0){
													//$ch_obj->decrementAvailableQty($shopcode,$item['product_id'],$item['qty_ordered']);
												}

												$order_item_list.='	<tr>
															<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">
																-
															</td>
															<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb;text-align:left">
																<p style="font-family:Verdana,Arial;font-weight:bold;margin:0 0 5px 0;color:#636363;font-style:normal;text-transform:uppercase;line-height:1.4;font-size:14px;float:left;width:100%;display:block">'.$item['product_name'].'</p>
															</td>
															<td style="text-align:center;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">'.$item['qty_ordered'].'</td>
															<td style="text-align:right;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">
																<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$item['price'].'</span>
															</td>
															<td style="text-align:right;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">
																<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$item['total_price'].'</span>
															</td>
														</tr>';
											}

										}

										if($lang_code == 'fr'){
											$item_price_txt ='Prix('.$total_itmes.' articles) (Taxes incluses)';
											$item_txt = 'Articles de votre commande';
											$Qty_txt ='Quantit&#237;';
											$Price_txt = 'Prix';
											$TotalPrice_txt  ='Prix total';
											$Taxes_txt = 'Imp&ograve;ts';
											$shipping_txt = 'Exp&#237;dition et manutention';
											$Subtotal_txt = 'Total';
											$grand_total_txt = 'Total';
										}else if($lang_code == 'it'){
											$item_price_txt = 'Prezzo('.$total_itmes.' articoli) <br> (Compreso di tasse)';
											$item_txt = 'Articoli nel tuo ordine';
											$Qty_txt ='Quantit&#224;';
											$Price_txt = 'Prezzo';
											$TotalPrice_txt  ='Prezzo totale';
											$Taxes_txt = 'Le tasse';
											$shipping_txt = 'Spedizione &amp; Gestione';
											$Subtotal_txt = 'totale parziale';
											$grand_total_txt = 'Somma totale';
										}else if($lang_code == 'pt'){
											$item_price_txt ='Pre&ccedil;o('.$total_itmes.' itens) <br> (incluindo impostos)';
											$item_txt = 'Itens em seu pedido';
											$Qty_txt ='Quantidade';
											$Price_txt = 'Pre&ccedil;o';
											$TotalPrice_txt  ='Pre&ccedil;o total';
											$Taxes_txt = 'Impostos';
											$shipping_txt = 'Envio e manuseio';
											$Subtotal_txt = 'Subtotal';
											$grand_total_txt = 'Total geral';
										}else if($lang_code == 'nl'){
											$item_price_txt ='Prijs('.$total_itmes.' stuks) <br> (Inclusief belastingen)';
											$item_txt = 'Artikelen in je bestelling';
											$Qty_txt ='Hoeveelheid';
											$Price_txt = 'Prijs';
											$TotalPrice_txt  ='Totale prijs';
											$Taxes_txt = 'Belastingen';
											$shipping_txt = 'Verzending &amp; Behandeling';
											$Subtotal_txt = 'Subtotaal';
											$grand_total_txt = 'Eindtotaal';
										}else if($lang_code == 'de'){
											$item_price_txt = 'Preis('.$total_itmes.' Artikel) <br> (Inklusive Steuern)';
											$item_txt = 'Artikel in Ihrer Bestellung';
											$Qty_txt ='Menge';
											$Price_txt = 'Preis';
											$TotalPrice_txt  ='Gesamtpreis';
											$Taxes_txt = 'Steuern';
											$shipping_txt = 'Versand &amp; Bearbeitung';
											$Subtotal_txt = 'Zwischensumme';
											$grand_total_txt = 'Gesamtsumme';
										}else if($lang_code == 'es'){
											$item_price_txt = 'Precio('.$total_itmes.' art&#237;culos) <br> (Impuestos incluidos)';
											$item_txt = 'Art&#237;culos en tu pedido';
											$Qty_txt ='Cantidad';
											$Price_txt = 'Precio';
											$TotalPrice_txt  ='Precio total';
											$Taxes_txt = 'Impuestos';
											$shipping_txt = 'Env&#237;o y manejo';
											$Subtotal_txt = 'Total parcial';
											$grand_total_txt = 'Gran total';
										}else{
											$item_price_txt = 'Price('.$total_itmes.' items) <br> (Inclusive of taxes)';
											$item_txt = 'Items <span class="il">in</span> your <span class="il">order</span>';
											$Qty_txt ='Qty';
											$Price_txt = 'Price';
											$TotalPrice_txt  ='Total Price';
											$Taxes_txt = 'Taxes';
											$shipping_txt = 'Shipping &amp; Handling';
											$Subtotal_txt = 'Subtotal';
											$grand_total_txt = 'Grand Total';
										}

										$order_items='<tr>
											<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:0;margin:0">
												<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:10px 15px;margin:0">
													<thead>
														<tr>
															<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
																Image
															</th>
															<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:left;font-size:11px">
															'.$item_txt.'
															</th>
															<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
															'.$Qty_txt.'
															</th>
															<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">
															'.$Price_txt.'
															</th>
															<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">
															'.$TotalPrice_txt.'
															</th>
														</tr>
													</thead>
													<tbody>
													'.$order_item_list.'
													</tbody>

												</table>
											</td>
										</tr>
										<tr>
											<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:0;margin:0">
												<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0;border-top:1px dashed #c3ced4;border-bottom:1px dashed #c3ced4">
													<tbody>
														<tr>
															<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:20px 15px;margin:0;text-align:right;line-height:20px">
																<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0">
																	<tbody>

																		<!--------------- // $discount_html ---------------------->
																		<tr>
																			<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
																			'.$Subtotal_txt.'
																			</td>
																			<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
																				<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$seller_shop_currency_code.$subtotal.'</span>
																			</td>
																		</tr>
																		<!--------------- // $voucher_html ---------------------->

																		<tr>
																			<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
																				<strong>'.$grand_total_txt.'</strong>
																			</td>
																			<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
																				<strong><span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">'.$seller_shop_currency_code.$grand_total.'</span></strong>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>';

										$TempVars=array('##WEBSHOPNAME##','##B2BORDERID##','##ORDER_DATE##','##ORDER_ITEMS##','##SHIPPING_ADDRESS##');

										$DynamicVars=array($webshop_name,$increment_id,$order_date,$order_items,$shipping_address);

										$CommonVars=array($site_logo, $webshop_name);
										$emailSendStatusFlag=$email_obj->get_email_code_status($shopcode,$identifier);
										if($emailSendStatusFlag==1){
											$email_send =  $email_obj->sendCommonHTMLEmail($shopcode,$EmailTo,$identifier,$TempVars,$DynamicVars,$increment_id,$webshop_name,$CommonVars,$lang_code);
											if($email_send == false)
											{

												$error = 'Unable to send email' ;
											}
											else
											{
												$ch_obj->updateB2BOrderEmailSentNotification($seller_shopcode,$order_id);
											}
										}

									}
								}

									/*************************Send email to b2b  seller admin**END*********************************************************/



						}else{
							$error='Unable to create b2b order ';
						}
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
		$message['message'] = 'B2b Order created successfully';

		exit(json_encode($message));
	}

});



$app->post('/webshop/update_order_status', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' ||  $order_id =='' || $status=='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$ch_obj = new DbCheckout();

		$cartData = $ch_obj->updateOrderStatus($shopcode,$order_id,$status);

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
		$message['message'] = 'Order Status Updated.';
		exit(json_encode($message));
	}

});



$app->post('/webshop/update_order_payment_status_info', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' ||  $order_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$ch_obj = new DbCheckout();
		$common_obj=new DbCommonFeature();

		if(isset($pay_request) && $pay_request!=''){
			$table='sales_order_payment';
			$columns = ' request_data = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($pay_request,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		if(isset($pay_response) && $pay_response!=''){
			$table='sales_order_payment';
			$columns = ' response_data = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($pay_response,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		if(isset($status) && $status!=''){


			$table='sales_order_payment';
			$columns = ' status = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($status, time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);

		}

		if(isset($transaction_id) && $transaction_id!=''){
			$table='sales_order_payment';
			$columns = ' transaction_id = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($transaction_id,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		// new add code updated
		if(isset($stripUpdateData) && $stripUpdateData!=''){
			$table='sales_order_payment';
			$columns = ' split_fbc_percentage = ?, split_fbc_percentage_amount = ?, split_fbc_fixed = ?, fbc_payment_amount = ?, webshop_payment_amount = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($stripUpdateData['split_fbc_percentage'],$stripUpdateData['split_fbc_percentage_amount'],$stripUpdateData['split_fbc_fixed'],$stripUpdateData['fbc_payment_amount'],$stripUpdateData['webshop_payment_amount'],time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		if(isset($stripe_success_page_flag) && $stripe_success_page_flag!=''){
			$table='sales_order_payment';
			$columns = 'stripe_success_page_flag = ?, updated_at = ?';
			$where = 'order_id = ? ';
			$params = array($stripe_success_page_flag,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}
		/*new code*/

		if(isset($payment_intent) && $payment_intent!=''){
			$table='sales_order_payment';
			$columns = 'payment_intent_id = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($payment_intent,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		if(isset($payment_method_id) && $payment_method_id!=''){
			$table='sales_order_payment';
			$columns = 'payment_method_id = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($payment_method_id,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		if(isset($payment_method) && $payment_method!=''){
			$table='sales_order_payment';
			$columns = 'payment_method = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($payment_method,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		if(isset($payment_method_name) && $payment_method_name!=''){
			$table='sales_order_payment';
			$columns = 'payment_method_name = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($payment_method_name,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		if(isset($amount) && $amount!=''){
			$table='sales_order_payment';
			$columns = 'amount = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($amount,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		if(isset($payment_amount) && $payment_amount!=''){
			$table='sales_order_payment';
			$columns = 'payment_amount = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($payment_amount,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
		}

		if(isset($payment_currency) && $payment_currency!=''){
			$table='sales_order_payment';
			$columns = 'payment_currency = ?, updated_at = ?';
			$where = ' order_id = ? ';
			$params = array($payment_currency,time(),$order_id);
			$Row = $common_obj->update_row($shopcode, $table, $columns, $where, $params);
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
		$message['message'] = 'Order payment Updated.';
		exit(json_encode($message));
	}

});


//cod otp send
$app->post('/webshop/send_cod_otp', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	// exit();

	$error='';
	if($shopcode =='' || $shopid=='' ||  $order_id =='' || $mobile_no=='')
	{
		$error='Please pass all the mandatory values';

	}else{

		// new mobile number
		$remove_space = str_replace(' ', '', $mobile_no);
		$remove_dash = str_replace('-', '', $remove_space);
		$final_mobile_no = $remove_dash;

		if(substr($final_mobile_no, 0, 3) === "+91" || substr($final_mobile_no, 0, 4) === "+91 "){
			$new_number = str_replace(' ', '', $final_mobile_no);
		}else if(strlen($final_mobile_no) > 10 && substr($final_mobile_no, 0, 2) === "91"){
			$new_number = "+";
			$new_number .= $final_mobile_no;
		}else if(substr($final_mobile_no, 0, 1) === "0"){
			$new_number = "+91";
			$new_number .= ltrim($final_mobile_no, '0');
		}else {
			$new_number = "+91";
			$new_number .= $final_mobile_no;
		}
	    //end new mobile number

		$new_number = str_replace('+91', '', $new_number);

	    //otp generate
	    $string = '0123456789';
		$string_shuffled = str_shuffle($string);
		$password = substr($string_shuffled, 1, 4);

		$msgtxt ='{{OTP}}%20is%20your%20order%20confirmation%20OTP%20for%20ZumbaShop.in%20account.%20Happy%20Shopping!%20Thanks%20WHUSO%20ECOMMERCE%20SOLUTIONS%20PRIVATE%20LIMITED';

		$msgtxt = str_replace("{{OTP}}",$password,$msgtxt);

		// curl start
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => "http://dltsms.bhavnacommunications.com/sendurlcomma.aspx?user=WHUCOM&pwd=jjpmu6__&senderid=WHUCOM&CountryCode=91&mobileno=".$new_number."&msgtext=".$msgtxt,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",

		));

		$response = curl_exec($curl);
		//$err = curl_error($curl);

		curl_close($curl);
		// end curl start

		$webshop_obj = new DbCommonFeature();
		$generatedOtp=$password;//custom otp
		$table='sales_order_cod_otp';
		$columns = 'order_id, mobile_no, otp,created_at,ip';
		$values = '?, ?, ?, ?, ?';
		$params = array($order_id, $new_number, $generatedOtp, time(), $_SERVER['REMOTE_ADDR']);
		$Row = $webshop_obj->add_row($shopcode, $table, $columns, $values, $params);


		if($Row == false)
		{
			$error='Unable to send otp';
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
		$message['message'] = 'Send otp successfully.';
		exit(json_encode($message));
	}

});
//remove otp
$app->post('/webshop/remove_otp', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' ||  $order_id =='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$ch_obj = new DbCheckout();

		$cartData = $ch_obj->removeOtp($shopcode,$order_id);

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
		$message['message'] = 'Otp Removed.';
		exit(json_encode($message));
	}

});

$app->post('/webshop/insert_razorpay_data/{shopcode}/{shopid}/{order_id}', function (Request $request, Response $response, $args){
	$data = $request->getParsedBody();
	extract($data);

	$shopcode = $args['shopcode'];
	$shopid  = $args['shopid'];
	$order_id = $args['order_id'];


	$error='';
	if($shopcode =='' || $shopid=='' ||  $order_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{

		$common_obj=new DbCommonFeature();

		$table ='razorpay_request_response';
		$columns = 'order_id, razorpay_request, razorpay_response';
		$values = '?, ?, ?';
		$params = array($order_id,$razorpay_request,$razorpay_response);
		$Row = $common_obj->add_row($shopcode, $table, $columns, $values, $params);

		if($Row == false)
		{
			$error='Unable to add razor data.';
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
		$message['message'] = 'Razorpay Data Updated.';
		exit(json_encode($message));
	}



});


$app->post('/webshop/save_eu_shippping_method', function (Request $request, Response $response, $args){
	$data = $request->getParsedBody();
	extract($data);


	$error='';
	if($shopcode =='' || $shop_id=='' ||  $quote_id =='' || $shipping_charge_id == '')
	{
		$error='Please pass all the mandatory values';

	}else{

		$common_obj=new DbCommonFeature();
		$ch_obj = new DbCheckout();
		$cart_obj = new DbCart();

		$table_name = 'shipping_methods_charges';
		$database_flag = 'own';
		$where = 'id = '.$shipping_charge_id.'' ;
		$tableData = $common_obj->getTableData($shopcode, $table_name, $database_flag, $where);
        $tableData[0]['cart_value_2'] = $tableData[0]['cart_value_2'] ?? null;
        $tableData[0]['cart_value_3'] = $tableData[0]['cart_value_3'] ?? null;

		//print_r($tableData);//exit;
		$table_name2 = 'shipping_methods';
		$database_flag2 = 'own';
		$where2 = 'id = '.$tableData[0]['ship_method_id'].'' ;
		$tableData2 = $common_obj->getTableData($shopcode, $table_name2, $database_flag2, $where2);

        $shipping_charge = $tableData[0]['ship_rate'];

        if($tableData[0]['cart_value_2'] !== null ||  $tableData[0]['cart_value_3'] !== null){
            $OrderItems= $ch_obj->get_sales_quote_items($shopcode,$quote_id);
            $total_price = 0;
            foreach($OrderItems as $item){
                $total_price += $item['total_price'];
            }

            if(($tableData[0]['cart_value_2'] ?? null) !== null && $total_price > $tableData[0]['cart_value_2']){
                $shipping_charge = $tableData[0]['rate_2'];
            }
            if(($tableData[0]['cart_value_3'] ?? null) !== null && $total_price > $tableData[0]['cart_value_3']){
                $shipping_charge = $tableData[0]['rate_3'];
            }
        }

		$shipping_tax_percent = $vat_percent;
		$shipping_tax_amount=($shipping_charge * $shipping_tax_percent)/100;
		$shipping_amount=$shipping_charge+$shipping_tax_amount;

		$shipping_method_id =$tableData2[0]['id'];
		$shipping_method_name =$tableData2[0]['ship_method_name'];


		$ch_obj->finalupdateQuoteShippingChargeAndTax($shopcode,$shop_id,$quote_id,$shipping_charge,$shipping_amount,$shipping_tax_percent,$shipping_tax_amount);

		$cart_obj->UpateQuoteTotal($shopcode,$quote_id);

		$ch_obj->UpdateQuoteShippingIdName($shopcode,$shop_id,$quote_id,$shipping_method_id,$shipping_method_name);


	}


	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Shipping Rates Updated';
		exit(json_encode($message));
	}
});

$app->post('/webshop/insert_payment_method', function (Request $request, Response $response, $args){
	$data = $request->getParsedBody();
	extract($data);
	$error='';
	if($shopcode =='' || $shop_id=='' ||  $order_id =='' || $payment_method_id == '')
	{
		$error='Please pass all the mandatory values';
	}else{
		$ch_obj = new DbCheckout();
		$payment_method_id=$payment_method_id;
		$payment_method=$payment_method;
		$payment_method_name=$payment_method_name;
		$payment_type=$payment_type;
		$currency_code=$currency_code;
		$ch_obj->add_to_sales_order_payment($shopcode,$shop_id,$order_id,$payment_method_id,$payment_method,$payment_method_name,$payment_type,$currency_code);
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Insert payment method';
		exit(json_encode($message));
	}
});

$app->post('/webshop/insert_sales_order_payment_history', function (Request $request, Response $response, $args){
	$data = $request->getParsedBody();
	extract($data);
	$error='';
	if($shopcode =='' || $shop_id=='' ||  $order_id =='' || $payment_method_id == '')
	{
		$error='Please pass all the mandatory values';
	}else{
		$ch_obj = new DbCheckout();
		$order_payment_id=$order_payment_id;
		$payment_method_id=$payment_method_id;
		$payment_method=$payment_method;
		$payment_method_name=$payment_method_name;
		$payment_type=$payment_type;
		// $currency_code=$currency_code;
		$ch_obj->add_to_sales_order_payment_history($shopcode,$shop_id,$order_id,$payment_method_id,$payment_method,$payment_method_name,$payment_type,$order_payment_id);
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Insert payment method';
		exit(json_encode($message));
	}
});

$app->post('/webshop/get_shipping_charges', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' ||  $quote_id =='' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$ch_obj = new DbCheckout();
		$tax_shipping_response = $ch_obj->getShippingChargesByQuoteWeight($shopcode,$shopid,$quote_id);
		$quote_expected_date = $ch_obj->get_sales_quote_items($shopcode,$quote_id);
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['eu_shipping_response']=$tax_shipping_response;
		$message['quote_estimate_delivery']=$quote_expected_date[0]['estimate_delivery_time'];
		$message['message'] = 'Data Available';
		exit(json_encode($message));
	}

});
