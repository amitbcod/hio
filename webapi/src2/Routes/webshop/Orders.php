<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/get_customer_address_by_order_id', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $order_id  =='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$order_obj = new DbEmailFeature();
		$ch_obj = new DbCheckout();

		$responseData[]='';

		$billing_address=$ch_obj->getFormattedOrderAddressById($shopcode,$order_id,1);
	    	 $responseData[]=$billing_address;

		$shipping_address=$ch_obj->getFormattedOrderAddressById($shopcode,$order_id,2);
	       $responseData[]=$shipping_address;

      $PaymentInfo = $ch_obj->getOrderPaymentDataById($shopcode,$order_id);


          	
      $WebShopPaymentDetailsById = $ch_obj->getWebShopPaymentDetailsById($shopcode,$PaymentInfo['payment_method_id']);
		$WebshopFbcData=$ch_obj->getFbcUserShopDataByShopId($shopid);
	
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
				
			}else{
				$payment_method_name='';
				$payment_method='';
			}

		 $responseData[]=$payment_method_name;
		if($responseData == false)
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
		$message['tableData'] = $responseData;
		exit(json_encode($message));
	}

});

$app->get('/webshop/my_orders_listing_old/{shopcode}/{shopid}/{customer_id}/{limit}/{offset}', function (Request $request, Response $response, $args){

	$shopcode 	= $args['shopcode'];

	$shopid 	= 	$args['shopid'];

	$customer_id = $args['customer_id'];

	$limit = $args['limit'];

	$offset = $args['offset'];



	$error='';

	if($shopcode =='' || $shopid=='' ||  $customer_id =='')

	{

		$error='Please pass all the mandatory values';



	}else{

		$OrderCollection = array();

		$order_obj = new DbOrders();

		$ch_obj= new  DbCheckout();







		$OrderData = $order_obj->getOrdersData($shopcode,$customer_id,$limit,$offset);

		$OrderDataCount = $order_obj->countgetOrdersData($shopcode,$customer_id);



	//	print_r($OrderData);exit;

	if($OrderData!=false){



		if(isset($OrderData) && count($OrderData)>0){

			foreach($OrderData as $order){

				$order_id=$order['order_id'];

				$order_items=$order_obj->getOrderItems($shopcode,$shopid,$order_id);

				$order['order_items']=$order_items;

				$b2b_orders=array();

				$return_orders=array();





				$b2bShops=$ch_obj->getShopsForBTwoBOrders($shopcode,$shopid,$order_id);

				if($b2bShops!=false){

					if(isset($b2bShops) && count($b2bShops)>0){

						foreach($b2bShops as $key=>$shop){

							$b2b_shop_id=$shop['shop_id'];



							$seller_shopcode='shop'.$b2b_shop_id;



							$b2b_order_list=$order_obj->getB2BOrdersForWebshop($seller_shopcode,$order_id);

							if($b2b_order_list!=false){

								if(isset($b2b_order_list) && count($b2b_order_list)>0){

									foreach($b2b_order_list as $value){

										$b2b_orders[$key]['increment_id']=$value['increment_id'];

										$b2b_orders[$key]['order_id']=$value['order_id'];

										$b2b_orders[$key]['shop_id']=$b2b_shop_id;

										$b2b_orders[$key]['is_split']=$value['is_split'];

									}

								}

							}

						}

					}



				}





				$order['b2b_orders']=$b2b_orders;







				$OrderCollection[]=$order;

			}

		}



		}else{

			$error='No Order found';

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

		$message['Total_Order'] = $OrderDataCount;

		$message['OrderData']=$OrderCollection;

		$message['message'] = 'My order  lisintg..';

		exit(json_encode($message));

	}



});

$app->get('/webshop/my_orders_listing_new/{shopcode}/{shopid}/{customer_id}/{limit}/{offset}', function (Request $request, Response $response, $args){
		$shopcode 	= $args['shopcode'];
		$shopid 	= 	$args['shopid'];
		$customer_id = $args['customer_id'];
		$limit = $args['limit'];
		$offset = $args['offset'];

		$error='';

		if($shopcode =='' || $shopid=='' ||  $customer_id =='')

		{
			$error='Please pass all the mandatory values';

		}else{

			$OrderCollection = array();
			$order_obj = new DbOrders();
			$ch_obj= new  DbCheckout();

			$OrderData = $order_obj->getOrdersData($shopcode,$customer_id,$limit,$offset);
			// print_r($OrderData);exit();
			$OrderDataCount = $order_obj->countgetOrdersData($shopcode,$customer_id);	
			
			if($OrderData!=false){
				if(isset($OrderData) && count($OrderData)>0){
					// echo json_encode($OrderData);

					// got all order_id
					$order_arry=array_column($OrderData,'order_id');
					$order_ids = json_encode($order_arry);
					$order_ids = substr($order_ids, 1,-1);

					// got all order_items
					$order_items=$order_obj->getOrderItems_opt_2($shopcode,$shopid,$order_ids);
					// echo json_encode($order_items);die();


					foreach ($order_arry as $key => $order_id) {
						$order_data_key=array_search($order_id, array_column($OrderData, 'order_id'));
					

						$b2b_orders=array();
						$return_orders=array();
						$b2bShops=$ch_obj->getShopsForBTwoBOrders($shopcode,$shopid,$order_id);

						if($b2bShops!=false){

							if(isset($b2bShops) && count($b2bShops)>0){

								foreach($b2bShops as $key=>$shop){

									$b2b_shop_id=$shop['shop_id'];



									$seller_shopcode='shop'.$b2b_shop_id;



									$b2b_order_list=$order_obj->getB2BOrdersForWebshop($seller_shopcode,$order_id);

									if($b2b_order_list!=false){

										if(isset($b2b_order_list) && count($b2b_order_list)>0){

											foreach($b2b_order_list as $value){

												$b2b_orders[$key]['increment_id']=$value['increment_id'];

												$b2b_orders[$key]['order_id']=$value['order_id'];

												$b2b_orders[$key]['shop_id']=$b2b_shop_id;

												$b2b_orders[$key]['is_split']=$value['is_split'];

											}

										}

									}

								}

							}

						}

						$order_data_with_item=$OrderData[$order_data_key];
						// print_r($order_data_with_item['invoice_id']);exit();
						$got_item_arry=array();
						foreach ($order_items as $key => $val) {
						       if ($val['order_id'] === $order_id) {
						       		array_push($got_item_arry,$order_items[$key]);

						       }
						}
						$order_data_with_item['order_items']=$got_item_arry;
						
						$order_data_with_item['b2b_orders']=$b2b_orders;


						$OrderCollection[]=$order_data_with_item;
					}
				


				}else{

					$error='No Order found';

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

				$message['Total_Order'] = $OrderDataCount;

				$message['OrderData']=$OrderCollection;

				$message['message'] = 'My order  lisintg..';

				exit(json_encode($message));

			}

		}	

});


$app->get('/webshop/my_return_orders_listing/{shopcode}/{shopid}/{order_id}', function (Request $request, Response $response, $args){





	$shopcode 	= $args['shopcode'];

	$shopid 	= 	$args['shopid'];

	$order_id = $args['order_id'];



	$error='';

	if($shopcode =='' || $shopid=='' ||  $order_id =='')

	{

		$error='Please pass all the mandatory values';



	}else{

		$ReturnOrderCollection = array();

		$order_obj = new DbOrders();



		$OrderData = $order_obj->getReturnOrdersData($shopcode,$shopid,$order_id);





		if($OrderData!=false){



			if(isset($OrderData) && count($OrderData)>0){

				foreach($OrderData as $order){

					$order_id=$order['order_id'];

					$return_order_id=$order['return_order_id'];

					$order_items=$order_obj->getReturnOrdersItemsData($shopcode,$shopid,$order_id,$return_order_id);

					$order['order_items']=$order_items;



					$ReturnOrderCollection[]=$order;

				}

			}else{

				$error='No Order found';

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

		$message['ReturnOrderCollection']=$ReturnOrderCollection;

		$message['message'] = 'My order  lisintg..';

		exit(json_encode($message));

	}



});


$app->post('/webshop/tracking_details_request', function (Request $request, Response $response, $args){

	$data = $request->getParsedBody();

	extract($data);

	// print_R($data);exit();


	$error='';

	// print_r($shopcode);exit();

	if($shopcode =='' || $shopid=='' ||  $order_id =='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$tracking_data_collection = array();
		$order_obj = new DbOrders();

		$OrderData = $order_obj->get_single_OrderData($shopcode,$order_id);
		
		$show_order_number_col = 0;

		// print_r($OrderData['is_split']);exit();

		if($OrderData!=false){

			if(isset($OrderData) && count($OrderData)>0){

					$order['order_id']=$OrderData['order_id'];
					// check if order is split or not
					if ($OrderData['is_split']==0) {

						// code for non split order
						$order_tracking_data = $order_obj->get_tracking_data($shopcode,$order_id);
						if (isset($order_tracking_data) && $order_tracking_data != '') {
							foreach ($order_tracking_data as $key => $value) {
								$order_increment_id = $order_obj->get_order_increment_id($shopcode,$value['order_id']);
								$order['tracking_data'][$key]['order_id']=$order_increment_id['increment_id'];
								$order['tracking_data'][$key]['box_number']=$value['box_number'];
								$order['tracking_data'][$key]['tracking_url']=$value['tracking_url'];
								$order['tracking_data'][$key]['tracking_id']=$value['tracking_id'];	
							}
						}
						

						
					}else{
						// code for split order
						$show_order_number_col = 1;

						$order_ids=[];
						$order_tracking_data = $order_obj->get_order_id_for_split($shopcode,$order_id);
						$order_ids = $order_tracking_data['order_ids'];
						// echo $order_ids;exit();
						// print_r($order_tracking_data);exit();

						$order_tracking_data = $order_obj->get_tracking_data($shopcode,$order_ids);
						if (isset($order_tracking_data) && $order_tracking_data != '') {
							foreach ($order_tracking_data as $key => $value) {
								$order_increment_id = $order_obj->get_order_increment_id($shopcode,$value['order_id']);
								$order['tracking_data'][$key]['order_id']=$order_increment_id['increment_id'];
								$order['tracking_data'][$key]['box_number']=$value['box_number'];
								$order['tracking_data'][$key]['tracking_url']=$value['tracking_url'];
								$order['tracking_data'][$key]['tracking_id']=$value['tracking_id'];	
							}
						}
						
						// print_r($order['tracking_data']);exit();

					}

					//Get B2B order tracking data 
					$Order_shop_ids = $order_obj->get_single_Order_items_Data($shopcode,$order_id);
					// print_r($Order_shop_ids);exit();

					if (isset($Order_shop_ids) && $Order_shop_ids != '' ) {
						foreach ($Order_shop_ids as $key => $value) {
							if ($value['shop_id'] != 0) {

								$b2b_shop_id= 'shop'.$value['shop_id'];
								$order_id_data = $order_obj->get_order_id_b2b($b2b_shop_id,$order_id,$shopid);
								if (count($Order_shop_ids) > 0 || (count($Order_shop_ids) == 1 && strpos($order_id_data, ',') !== false) ) {
									$show_order_number_col=1;
								}
								// print_r($order_id_data['order_ids']);exit();
								$order_tracking_data = $order_obj->get_tracking_data_for_b2b($b2b_shop_id,$order_id_data['order_ids']);
								if (isset($order_tracking_data) && $order_tracking_data != '') {
									foreach ($order_tracking_data as $key => $value) {
										$order_increment_id = $order_obj->get_order_increment_id_b2b($b2b_shop_id,$value['order_id']);
										$order['b2b_tracking_data'][$key]['order_id']=$order_increment_id['increment_id'];
										$order['b2b_tracking_data'][$key]['box_number']=$value['box_number'];
										$order['b2b_tracking_data'][$key]['tracking_url']=$value['tracking_url'];
										$order['b2b_tracking_data'][$key]['tracking_id']=$value['tracking_id'];	
									}	
								}
								// print_r($order['tracking_data']);exit();
								// exit();							

							}
						}
					}

					if ($show_order_number_col == 1) {
						$order['order_col_show']=1;
					}else{
						$order['order_col_show']=0;
					}


					$tracking_data_collection[]=$order;

			}else{

				$error='No Order found';

			}

		}else{
			$error='No Order found';
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

		$message['tracking_data']=$tracking_data_collection[0];

		$message['message'] = 'Order tracking data..';

		exit(json_encode($message));

	}



});


$app->post('/webshop/order_operation_checks', function (Request $request, Response $response, $args){





	$data = $request->getParsedBody();

	extract($data);

	// print_R($data );

	$error='';

	if(empty($shopcode) || empty($shopid) ||  empty($order_id))

	{

		$error='Please pass all the mandatory values';



	}else{



		$OrderCollection = array();

		$order_obj = new DbOrders();

		$ch_obj= new  DbCheckout();



		$flag='';



		$order = $order_obj->getOrderDataById($shopcode,$order_id);



		if($operation_name=='can_able_to_cancel'){



			if($order!=false){

				$status=$order['status'];

				$order_id=$order['order_id'];



				$b2b_orders=array();



				$b2b_order_status_zero=0;



				$b2bShops=$ch_obj->getShopsForBTwoBOrders($shopcode,$shopid,$order_id);

				if($b2bShops!=false){

					if(isset($b2bShops) && count($b2bShops)>0){

						foreach($b2bShops as $key=>$shop){

							$b2b_shop_id=$shop['shop_id'];



							$seller_shopcode='shop'.$b2b_shop_id;



							$b2b_order_list=$order_obj->getB2BOrdersForWebshop($seller_shopcode,$order_id);

							$b2b_order_count=count($b2b_order_list);

							if($b2b_order_list!=false){

								if(isset($b2b_order_list) && count($b2b_order_list)>0){

									foreach($b2b_order_list as $value){

										if($value['status']==0){

											$b2b_order_status_zero++;

										}

									}

								}

							}

						}

					}



					if($status==0 && ($b2b_order_count==$b2b_order_status_zero)){

						$flag='able_to_cancel';

					}else{

						$flag=false;

					}



				}else{



					if($status==0){

						$flag='able_to_cancel';

					}else{

						$flag=false;

					}

				}



			}else{

				$error='No Order found';

				$flag=false;

			}



		}else if($operation_name=='can_able_to_return'){



				if($order!=false){

					$status=$order['status'];

					$order_id=$order['order_id'];

					$tracking_complete_date=$order['tracking_complete_date'];





					$b2b_orders=array();



					$b2b_order_status_zero=0;



					$b2bShops=$ch_obj->getShopsForBTwoBOrders($shopcode,$shopid,$order_id);

					if($b2bShops!=false){

						if(isset($b2bShops) && count($b2bShops)>0){

							foreach($b2bShops as $key=>$shop){

								$b2b_shop_id=$shop['shop_id'];



								$seller_shopcode='shop'.$b2b_shop_id;



								$b2b_order_list=$order_obj->getB2BOrdersForWebshop($seller_shopcode,$order_id);

								$b2b_order_count=count($b2b_order_list);

								if($b2b_order_list!=false){

									if(isset($b2b_order_list) && count($b2b_order_list)>0){

										foreach($b2b_order_list as $value){

											if($value['status']==6){

												$b2b_order_status_zero++;

											}

										}

									}

								}

							}

						}



						if($status==6 && ($b2b_order_count==$b2b_order_status_zero)){



							$now = time(); //

							$your_date = $tracking_complete_date;

							$datediff = $now - $your_date;



							$no_of_days= round($datediff / (60 * 60 * 24));



							if($no_of_days<=$product_return_duration){

								$flag='able_to_return';

							}else{

								$flag=false;

							}



						}else{

							$flag=false;

						}



					}else{



						if($status==6){

							$now = time(); //

							$your_date = $tracking_complete_date;

							$datediff = $now - $your_date;





							$no_of_days= round($datediff / (60 * 60 * 24));



							if($no_of_days<=$product_return_duration){

								$flag='able_to_return';

							}else{

								$flag=false;

							}

						}else{

							$flag=false;

						}

					}





			}else{

				$flag=false;

			}

		}



	}





	if($error != '' ){

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		$message['flag']=$flag;

		exit(json_encode($message));

	}else{

		$message['statusCode'] = '200';

		$message['is_success'] = 'true';

		$message['flag']=$flag;

		$message['message'] = '';

		exit(json_encode($message));

	}



});





$app->get('/webshop/my_order_detail/{shopcode}/{shopid}/{order_id}', function (Request $request, Response $response, $args){





	$shopcode 	= $args['shopcode'];

	$shopid 	= 	$args['shopid'];

	$order_id = $args['order_id'];



	$error='';

	if($shopcode =='' || $shopid=='' ||  $order_id =='')

	{

		$error='Please pass all the mandatory values';



	}else{

		$OrderData = array();

		$order_obj = new DbOrders();



		$OrderData = $order_obj->getOrderDataById($shopcode,$order_id);





		if($OrderData!=false){



			if(isset($OrderData) && $OrderData['order_id']>0){



					$order_id=$OrderData['order_id'];



					$order_items=$order_obj->getOrderItems($shopcode,$shopid,$order_id);

					$OrderData['order_items']=$order_items;

			}else{

				$error='No Order found';

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

		$message['OrderData']=$OrderData;

		$message['message'] = 'My order  detail.';

		exit(json_encode($message));

	}



});





$app->get('/webshop/my_return_order_detail/{shopcode}/{shopid}/{return_order_id}', function (Request $request, Response $response, $args){





	$shopcode 	= $args['shopcode'];

	$shopid 	= 	$args['shopid'];

	$return_order_id = $args['return_order_id'];



	$error='';

	if($shopcode =='' || $shopid=='' ||  $return_order_id =='')

	{

		$error='Please pass all the mandatory values';



	}else{

		$OrderData = array();

		$order_obj = new DbOrders();



		$OrderData = $order_obj->getReturnOrderDataById($shopcode,$shopid,$return_order_id);







		if($OrderData!=false){



			$ShopDetail=$order_obj->get_fbc_user_shop_details($shopid);

			$OrderData['webshop_name']=$ShopDetail['org_shop_name'];



			$return_order_id=$OrderData['return_order_id'];

			$order_id=$OrderData['order_id'];

			$OrderData['payment_method_data'] = $order_obj->getOrderPaymentDataById($shopcode,$order_id);

			$MainOrderData = $order_obj->getOrderDataById($shopcode,$order_id);

			$OrderData['customer_name']=$MainOrderData['customer_firstname'].' '.$MainOrderData['customer_lastname'];

			$OrderData['customer_address']=$order_obj->getFormattedOrderAddressById($shopcode,$order_id,2);





			$OrderData['order_items']=$order_obj->getReturnOrdersItemsData($shopcode,$shopid,$order_id,$return_order_id);



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

		$message['OrderData']=$OrderData;

		$message['message'] = 'Return order Details';

		exit(json_encode($message));

	}



});







$app->post('/webshop/return_order_request', function (Request $request, Response $response, $args){





	$data = $request->getParsedBody();

	extract($data);

	$error='';

	if(empty($shopcode) || empty($shopid) ||  empty($order_id)  || empty($flag) || empty($selected_item) || empty($customer_id) ||  empty($increment_id))

	{

		$error='Please pass all the mandatory values';



	}else{



	//	var_dump($selected_item);exit;



		$return_order_id='';

		$product_return_duration=0;

		$order_obj = new DbOrders();

		$ch_obj= new  DbCheckout();
		$order_amount=0;
		$order_discount=0;
		$order_grandtotal=0;



		$glb_obj = new DbGlobalFeature();



		$CustomerVariable=$glb_obj->get_custom_variable($shopcode,'product_return_duration');



		if($CustomerVariable!=false){

			$product_return_duration=$CustomerVariable['value'];

		}else{

			$product_return_duration=0;

		}



		if($product_return_duration>0){

				$MainOrderData = $order_obj->getOrderDataById($shopcode,$order_id);

				if($MainOrderData!=false){



					$tracking_complete_date=date('Y-m-d',$MainOrderData['tracking_complete_date']);

					$return_request_due_date= date('Y-m-d', strtotime($tracking_complete_date. ' + '.$product_return_duration.' days'));

					$return_request_due_date=strtotime($return_request_due_date);



				}else{

					$return_request_due_date='';

				}

		}else{

			$return_request_due_date='';

		}



		$return_order_id = $order_obj->add_to_sales_order_return($shopcode,$shopid,$order_id,$increment_id,$customer_id,$return_request_due_date);



		if($return_order_id!=false){



			if(isset($selected_item) && count($selected_item)>0){



				foreach($selected_item as $item){

					$order_item_id=$item['item_id'];

					$qty_ordered=$item['qty_ordered'];

					$qty_return=$item['qty_return'];



					$order_item_info=$order_obj->getSingleOrderItemDetail($shopcode,$order_item_id);

					$price=$order_item_info['price'];

					$total_price=$order_item_info['total_price'];

					$discount_amount=$order_item_info['discount_amount'];

					$barcode=$order_item_info['barcode'];



					$total_discount_amount=$qty_return * $discount_amount;



					$return_order_item_id = $order_obj->add_to_sales_order_return_items($shopcode,$shopid,$order_id,$return_order_id,$order_item_id,$qty_ordered,$qty_return,$price,$total_price,$barcode,$discount_amount,$total_discount_amount);

					//start changes
					$order_amount += $price * $qty_return;
					$order_discount += $discount_amount * $qty_return;
					if(isset($order_discount) && $order_discount>0){
					$order_grandtotal=$order_amount-$order_discount;
					}else{
						$order_grandtotal=$order_amount;
					}
					//end changes

				}

			}



			// $order_amount=$order_obj->get_order_items_column_data_for_sum($shopcode,$shopid,$order_id,$return_order_id,'total_price');



			$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'order_amount',$order_amount);


			// $order_discount=$order_obj->get_order_items_column_data_for_sum($shopcode,$shopid,$order_id,$return_order_id,'total_discount_amount');



			// if(isset($order_discount) && $order_discount>0){

			// 	$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'order_discount',$order_discount);

			// 	$order_grandtotal=$order_amount-$order_discount;

			// }else{

			// 	$order_grandtotal=$order_amount;

			// }

			$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'order_discount',$order_discount);

			$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'order_grandtotal',$order_grandtotal);





		}else{

			$error='Unable to create order';

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

		$message['return_order_id']=$return_order_id;

		$message['message'] = 'Return request send successfully.';

		exit(json_encode($message));

	}



});





$app->post('/webshop/return_order_confirm', function (Request $request, Response $response, $args){





	$data = $request->getParsedBody();

	extract($data);



	$error='';

	if(empty($shopcode) || empty($shopid) ||  empty($order_id) ||  empty($return_order_id)  || empty($item_qty) )

	{

		$error='Please pass all the mandatory values';



	}else{



		$order_obj = new DbOrders();

		$ch_obj= new  DbCheckout();
		$order_amount=0;
		$order_discount=0;
		$order_grandtotal=0;


		$OrderData = $order_obj->getReturnOrderDataById($shopcode,$shopid,$return_order_id);





		if($OrderData!=false){



			$return_order_id=$OrderData['return_order_id'];

			$order_id=$OrderData['order_id'];



			//$MainOrderData = $order_obj->getOrderDataById($shopcode,$order_id);



			if(isset($item_qty) && count($item_qty)>0){

				foreach($item_qty as $order_item_id=>$item_qty_val){



					$RetunItemRow=$order_obj->getReturnOrderItemDetailById($shopcode,$shopid,$return_order_id,$order_item_id);



					$qty_return=$RetunItemRow['qty_return'];



					if($qty_return!=$item_qty_val){

						$SingleRow=$order_obj->getSingleOrderItemDetail($shopcode,$order_item_id);

						$discount_amount=$SingleRow['discount_amount'];

						$new_total_price=$SingleRow['price'] * $item_qty_val;



						$total_discount_amount=$discount_amount * $item_qty_val;



						$order_obj->update_return_order_item_column($shopcode,$shopid,$return_order_id,$order_item_id,'qty_return',$item_qty_val);



						$order_obj->update_return_order_item_column($shopcode,$shopid,$return_order_id,$order_item_id,'total_price',$new_total_price);



						$order_obj->update_return_order_item_column($shopcode,$shopid,$return_order_id,$order_item_id,'total_discount_amount',$total_discount_amount);



					}else{
						//start changes
						$SingleRow=$order_obj->getSingleOrderItemDetail($shopcode,$order_item_id);

						$order_amount += $SingleRow['price'] * $qty_return; //$SingleRow['qty_scanned'] //qty_ordered
						// $order_amount += $SingleRow['price'];
						$order_discount += $SingleRow['discount_amount'] * $qty_return;
						if(isset($order_discount) && $order_discount>0){
							$order_grandtotal=$order_amount-$order_discount;
						}else{
							$order_grandtotal=$order_amount;
						}
						//end changes
					}

				}

			}

			$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'order_amount',$order_amount);

			$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'order_discount',$order_discount);

			$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'order_grandtotal',$order_grandtotal);


			// $order_amount=$order_obj->get_order_items_column_data_for_sum($shopcode,$shopid,$order_id,$return_order_id,'total_price');



			// $order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'order_amount',$order_amount);



			// $order_discount=$order_obj->get_order_items_column_data_for_sum($shopcode,$shopid,$order_id,$return_order_id,'total_discount_amount');



			// if(isset($order_discount) && $order_discount>0){

			// 	$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'order_discount',$order_discount);

			// 	$order_grandtotal=$order_amount-$order_discount;

			// }else{

			// 	$order_grandtotal=$order_amount;

			// }



			$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'reason_for_return',$reason_for_return);

			$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'refund_payment_mode',$refund_payment_mode);

			$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'status',2);   //update order status to 2 - confirm from customer



			if($refund_payment_mode==2){

				$order_obj->update_return_order_bank_detail($shopcode,$shopid,$return_order_id,$bank_name,$bank_branch,$ifsc_iban,$bic_swift,$bank_acc_no,$acc_holder_name);

			}





		}else{

			$error='Unable to confirm order';

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

		$message['return_order_id']=$return_order_id;

		$message['message'] = 'Return confirmed  successfully.';

		exit(json_encode($message));

	}



});



$app->post('/webshop/return_order_print', function (Request $request, Response $response){



	$data = $request->getParsedBody();

	extract($data);

	$error='';

	if(empty($shopcode) || empty($shopid) ||  empty($return_order_id) )

	{

		$error='Please pass all the mandatory values';



	}

	else

	{

		$order_obj = new DbOrders();

		$getReturnData=$order_obj->getReturnOrderDataById($shopcode,$shopid,$return_order_id);
		if(isset($getReturnData) && $getReturnData!=false){
			if($getReturnData['status']==0){
				$order_obj->update_return_order_column($shopcode,$shopid,$return_order_id,'status',1);   //update order status to 1- Print
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

		$message['return_order_id']=$return_order_id;

		$message['message'] = 'Return print  successfully.';

		exit(json_encode($message));

	}

});







$app->post('/webshop/cancel_order_request', function (Request $request, Response $response, $args){


	$data = $request->getParsedBody();
	extract($data);
	$error='';

	if(empty($shopcode) || empty($shopid) ||  empty($order_id) ||  empty($reason_for_cancel)){

		$error='Please pass all the mandatory values';

	}else{

		$lang_code =  (isset($lang_code) ? $lang_code : '');

		$order_obj = new DbOrders();
		$webshop_obj = new DbCommonFeature();
		$webmail_obj = new DbEmailFeature();
		$webcheck_obj = new DbCheckout();

		$glb_feture_obj = new DbGlobalFeature();
		$identifier = 'online_stripe_payment_refund';	
		$online_stripe_payment_refund = $glb_feture_obj->get_custom_variable($shopcode,$identifier);

		$OrderDetails = $order_obj->getOrdersDetailsByID($shopcode,$order_id);

		$payment_method = $order_obj->getOrderPaymentDataById($shopcode,$order_id);

		$OrderItemsDetails = $order_obj->getOrdersItemsDetailsByID($shopcode,$order_id);
		$customer_type_master = $order_obj->getCustomerTypeMaster($shopcode);

	
		//exit;

		$email = $OrderDetails['customer_email'];
		$first_name = $OrderDetails['customer_firstname'];
		$last_name = $OrderDetails['customer_lastname'];
		$name = $first_name.' '.$last_name;
		$increment_id = $OrderDetails['increment_id'];
		// $coupon_amount = $OrderDetails['base_subtotal'] - $OrderDetails['discount_amount'];
		$coupon_amount = $OrderDetails['subtotal'] + $OrderDetails['payment_final_charge']; // new added
		// $coupon_amount = $OrderDetails['base_subtotal'] - $OrderDetails['discount_amount'];//old
		// new code cancel order copuon cod || via transfer
		if($payment_method['payment_method'] == 'via_transfer' || $payment_method['payment_method'] == 'cod' || ($payment_method['payment_method'] == 'stripe_payment' && $online_stripe_payment_refund['value'] == 'yes') || ($payment_method['payment_method'] == 'paypal_express' && $online_stripe_payment_refund['value'] == 'yes')) {
			if($OrderDetails['voucher_code'] != '' && $OrderDetails['voucher_amount'] > 0.00 ) {
				$coupon_amount = $OrderDetails['voucher_amount'];
			}else{
				$coupon_amount = 0;
			}
		}
		// end new code

		$Mail_Coupun_amount = $currency_code.$coupon_amount;
		$start_date = date('Y-m-d');
		$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
		$expiry_date = date('j F Y', strtotime($end_date));


		$customer_id_arr = array();
		foreach ($customer_type_master as $value) {
	 		$customer_id_arr[] = $value['id'];
		}
		$apply_to = implode(",", $customer_id_arr);



		$OrderData = $order_obj->cancel_order_request($shopcode,$shopid,$order_id,$reason_for_cancel);
		if($OrderData)
		{
			if($coupon_amount > 0 ) {
			// if($payment_method['payment_method'] != 'via_transfer' && $payment_method['payment_method'] != 'cod' ) {

				$rules_id = $order_obj->createCancelCoupon($shopcode,$order_id,$coupon_amount,$apply_to);
				if($rules_id > 0){
					$cancel_coupon_code = 'CAN-'.$increment_id.'-'.time();
					$coupon = $order_obj->insert_salesrule_coupon($shopcode,$rules_id,$cancel_coupon_code);

				}

				$webshopName = $webshop_obj->getWebShopName($shopcode,$shopid);
				if($webshopName!=false){
					$webshop_name = $webshopName['org_shop_name'];
				}else{
					$webshop_name = '';
				}



				$email_code = "storecredit-voucher-cancelnorder";
				$TempVars = array('##CUSTOMERNAME##','##ORDERID##','##VOUCHERCODE##','##VOUCHERAMOUNT##','##VOUCHEREXPIRYDATE##','##WEBSHOPNAME##');
				$DynamicVars=array($name, $increment_id, $cancel_coupon_code, $Mail_Coupun_amount, $expiry_date, $webshop_name);
				$CommonVars=array($site_logo, $webshop_name);
				$emailSendStatusFlag=$webmail_obj->get_email_code_status($shopcode,$email_code);
				if($emailSendStatusFlag==1){
					$send_email=$webmail_obj->sendCommonHTMLEmail($shopcode,$email,$email_code,$TempVars,$DynamicVars,$increment_id,'',$CommonVars,$lang_code);

					if($send_email == false)
					{

					}
				}
			}

			if($payment_method['payment_method'] == 'stripe_payment' && $online_stripe_payment_refund['value'] == 'yes'){		

				$stripe_dir = $_SERVER["DOCUMENT_ROOT"]."/stripe_lib/init.php";
			
				$WebShopPaymentDetailsById =  $webcheck_obj->getWebShopPaymentDetailsById($shopcode,$payment_method['payment_method_id']);
				$keyMainData=json_decode($WebShopPaymentDetailsById['gateway_details']);
				$keyData=$keyMainData->key;

				$payment_intent = $payment_method['payment_intent_id'];
				require_once($stripe_dir);

				$stripe = new \Stripe\StripeClient($keyData);
				$stripe->refunds->create([
					'payment_intent' => $payment_intent,
				]); 
			}


			if($payment_method['payment_method'] == 'paypal_express' && $online_stripe_payment_refund['value'] == 'yes'){		

				$WebShopPaymentDetailsById =  $webcheck_obj->getWebShopPaymentDetailsById($shopcode,$payment_method['payment_method_id']);
				$keyMainData=json_decode($WebShopPaymentDetailsById['gateway_details']);
				$client_id=$keyMainData->client_id;
				$secret_key=$keyMainData->secret_key;
	
				/***********************PayPal Auth Start****************************************/
				$curl = curl_init();
	
				curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v1/oauth2/token',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_USERPWD => $client_id.":".$secret_key,
				CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
				//CURLOPT_HTTPHEADER => $headers,
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/x-www-form-urlencoded'
				  ),
				));
	
				$response = curl_exec($curl);
				curl_close($curl);
				$result = json_decode($response, true);
	
				$access_token = $result['access_token'];
				//echo $access_token;
	
			/***********************PayPal Auth End****************************************/
	
			/***********************PayPal Refund Start****************************************/
	
				$paypal_refund_url = "https://api-m.sandbox.paypal.com/v1/payments/capture/".$payment_method['transaction_id']."/refund";
				$headers = array(
					'Content-Type:application/json',
					'Authorization: Bearer '. $access_token
				);
				$curl = curl_init();
	
				curl_setopt_array($curl, array(
				CURLOPT_URL => $paypal_refund_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST => 'POST',			
				CURLOPT_HTTPHEADER => $headers,
				));
	
				$response = curl_exec($curl);
	
				curl_close($curl);
				//$result = json_decode($response, true);

				//print_r($result);exit;
				
			/***********************PayPal Refund Start****************************************/	
			
				$table='paypal_refund_details';
				$columns = 'resource_id, type, output';
				$values = '?, ?, ?';
				$params = array($order_id, 0 , $response);
				$Row = $webshop_obj->add_row($shopcode, $table, $columns, $values, $params);

			}

			
			foreach($OrderItemsDetails as $item) {
				if($item['product_inv_type'] != 'dropship'){
					$increase_available_qty = $webcheck_obj->incrementAvailableQty($shopcode,$item['product_id'],$item['qty_ordered']);
				}
			}



			/*---------------------cancel b2b order as well------------------------*/

				$shop_order_items=array();
				$OtherShops=$webcheck_obj->getShopsForBTwoBOrders($shopcode,$shopid,$order_id);
				if($OtherShops!=false){


				if(isset($OtherShops) && count($OtherShops)>0){

					foreach($OtherShops as $shop){

						$seller_shop_id=$shop['shop_id'];
						$Products=$webcheck_obj->getProductsDropShipAndVirtualWithQtyZero($shopcode,$shopid,$order_id,$seller_shop_id);
						$total_qty_ordered=0;
						$total_price=0;

						if(isset($Products) && count($Products)>0){
							foreach($Products as $value){
								//b2b product increase
								$dropship_shop_code='shop'.$value['shop_id'];
								$dropship_shop_product_id=$value['shop_product_id'];
								$qty_ordered_shop_product_qty_order=$value['qty_ordered'];

								// update dropshihp database product inventory table
								$order_obj->cancel_b2b_order_update_qty($dropship_shop_code,$dropship_shop_product_id,$qty_ordered_shop_product_qty_order);
								//end
								$shop_order_items[$seller_shop_id]['item_ids'][]=$value['item_id'];
								$total_qty_ordered+=$value['qty_ordered'];
								$total_price+=$value['total_price'];
							}

						}

						$shop_order_items[$seller_shop_id]['total_qty_ordered']=$total_qty_ordered;
						$shop_order_items[$seller_shop_id]['total_price']=$total_price;

					}


					$SellerFbcData=$webcheck_obj->getFbcUserIdByShopId($seller_shop_id);
					$SellerShopData=$webcheck_obj->getFbcUserIdByShopId($seller_shop_id);

					//$WebshopFbcData=$webcheck_obj->getFbcUserIdByShopId($shopid);

					//$seller_fbc_user_id=$SellerFbcData['fbc_user_id'];

					//$webshop_name=$SellerShopData['org_shop_name'];

				if($SellerFbcData!=false){

						if(isset($shop_order_items) && count($shop_order_items)>0){

							foreach($shop_order_items as $seller_shop_id=>$value){

								$seller_shopcode='shop'.$seller_shop_id;
								$B2BOrder=$webcheck_obj->getB2BOrderByWebshopOrderId($seller_shopcode,$seller_shop_id,$order_id);

								if($B2BOrder!=false){

									$b2b_order_id=$B2BOrder['order_id'];

									$order_obj->cancel_b2b_order_request($seller_shopcode,$seller_shop_id,$b2b_order_id);

								}

							}

						}



					}



				}



		}


			/*--------------------------------------------------------------------*/

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
		$message['message'] = 'Order Cancelled  successfully.';
		exit(json_encode($message));

	}

});











