<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class RequestPaymentController extends CI_Controller {
	function  __construct(){
        parent::__construct();
        $this->load->library('paypal_lib');
    }

	public function index(){
		$shopcode = SHOPCODE;
		$shop_id = SHOP_ID;
		$error='';
		$urlData=$this->uri->segment('2');
		// echo 'test';
		// $decoded_data = json_decode(base64_decode($urlData), true);
		// $decoded_data['order_id']='374';
		// $data_o['order_id'] = $decoded_data['order_id'];
		// $encrypted_order_id = rtrim(base64_encode(json_encode($data_o)), '=');

		// print_r($encrypted_order_id);exit();
		if(isset($urlData) && !empty($urlData)){
			$decoded_data = json_decode(base64_decode($urlData), true);
			$order_id = $decoded_data['order_id'];
			$data['order_id']=$order_id;

			// order id exit
			$table1 = 'sales_order';
			$flag1 = 'own';
			$where1 = 'order_id = ?';
			$params1=array($order_id);
			$postArr1 = array('table_name'=>$table1,'database_flag'=>$flag1,'where'=>$where1,'params'=>$params1);
			$response1 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr1);
			if(!empty($response1)&& isset($response1) && $response1->statusCode==200){
				// check if payment method
				$redirect='';
				$table3 = 'sales_order_payment';
				$flag3 = 'own';
				$where3 = 'order_id = ?';
				$params3=array($order_id);
				$postArr3 = array('table_name'=>$table3,'database_flag'=>$flag3,'where'=>$where3,'params'=>$params3);
				$response3 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr3);

				if(!empty($response3) && isset($response3)){
					if(!empty($response3) && isset($response3) && $response3->statusCode==200 && isset($response3->tableData[0]->stripe_success_page_flag) && $response3->tableData[0]->stripe_success_page_flag==0){
							$order_payment_method=$response3->tableData[0]->payment_method_id;
							$order_payment_status=$response3->tableData[0]->status;
							if(($order_payment_method==1 || $order_payment_method==6 || $order_payment_method==5 || $order_payment_method==4) && $order_payment_status!='completed'){

								$redirect='no';
							}else{
								redirect(base_url().'paymentfailed');
							}

					}else{
						redirect(base_url().'paymentfailed');
					}
				}else{
					$redirect='no';
				}

				if($redirect=='no'){
					$payArr = array('country_code'=>COUNTRY_CODE);
					$MethodResult = CheckoutRepository::payment_methods_listing($shopcode,$shop_id,$payArr);
					if(!empty($MethodResult) && isset($MethodResult) && ($MethodResult->is_success=='true')){
						$data['PaymentMethods'] = $MethodResult->PaymentMethods;
					}else{
						redirect(base_url().'paymentfailed');
					}
					$this->template->load('requestpayment/request_payment', $data);
				}else{
					redirect(base_url().'paymentfailed');
				}
			}else{
				redirect(base_url().'paymentfailed');
			}
		}else{
			redirect(base_url().'paymentfailed');
		}
	}

	public function requestPayment(){
		$shopcode = SHOPCODE;
		$shop_id = SHOP_ID;
		if(isset($_POST)){
			if($_POST['orderId'] && $_POST['payment_method']){
				$order_id=$_POST['orderId'];
				$customer_id=0;
				$payment_method_id=$_POST['payment_method'];
				$table1 = 'sales_order';
				$flag1 = 'own';
				$where1 = 'order_id = ?';
				$params1=array($order_id);
				$postArr1 = array('table_name'=>$table1,'database_flag'=>$flag1,'where'=>$where1,'params'=>$params1);
				$response1 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr1);
				if(!empty($response1)&& isset($response1) && $response1->statusCode==200){
					$order_response=$response1->tableData[0];
					$customer_id=$order_response->customer_id;
					$encode_oid=base64_encode($order_response->increment_id);
					if($order_response->currency_code_session != '' && $order_response->default_currency_flag == 0){
						$currency_code = $order_response->currency_code_session;
					}else{
						$currency_code = CURRENCY_CODE;
					}
				}
				$table3 = 'sales_order_payment';
				$flag3 = 'own';
				$where3 = 'order_id = ?';
				$params3=array($order_id);
				$postArr3 = array('table_name'=>$table3,'database_flag'=>$flag3,'where'=>$where3,'params'=>$params3);
				$response3 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr3);
				if(!empty($response3) && isset($response3) && $response3->statusCode==200 && isset($response3->tableData[0]->stripe_success_page_flag) && $response3->tableData[0]->stripe_success_page_flag==0){

				}else{
					if($payment_method_id==1){
						$payment_method_id=$payment_method_id;
						$payment_method='paypal_express';
						$payment_method_name='Paypal Express Checkout';
						$payment_type=1;
						$currency_code='';
					}elseif($payment_method_id==6){
						$payment_method_id=$payment_method_id;
						$payment_method='stripe_payment';
						$payment_method_name='Stripe';
						$payment_type=2;
						$currency_code='';
					}
					$order_payment_array = array('order_id'=>$order_id,'payment_method_id'=>$payment_method_id,'payment_method'=>$payment_method,'payment_method_name'=>$payment_method_name,'payment_type'=>$payment_type,'currency_code'=>$currency_code);
					CheckoutRepository::insert_payment_method($shopcode,$shop_id,$order_payment_array);
				}

				if($payment_method_id==1){
					$cancel_url=base_url().'paymentfailed';
					$success_url=base_url().'paymentsuccess/?key='.$encode_oid;
					$notifyURL=base_url().'order/ipn/?request_id='.$encode_oid;

					if($order_response->currency_code_session != '' && $order_response->default_currency_flag == 0){
						$total_final_amount = $order_response->grand_total*$order_response->currency_conversion_rate;
						$amountPaypalConvert=number_format($total_final_amount, 2, '.', '');
						$CURRENCY_CODE = $order_response->currency_code_session;
					}else{
						$total_final_amount = $order_response->grand_total;
                        $amountPaypalConvert=number_format($order_response->grand_total, 2, '.', '');
                        $CURRENCY_CODE = CURRENCY_CODE;
					}

					$orderPaymentArr = array('order_id'=>$order_id,'amount'=>$order_response->grand_total,'payment_amount'=>number_format($total_final_amount, 2, '.', ''),'payment_currency'=>$CURRENCY_CODE);
					$ResponseArr = CheckoutRepository::update_order_payment_status_info($shopcode,$shop_id,$orderPaymentArr);
					// Add fields to paypal form
					$this->paypal_lib->add_field('return', $success_url);
					$this->paypal_lib->add_field('cancel_return', $cancel_url);
					$this->paypal_lib->add_field('notify_url', $notifyURL);
					$this->paypal_lib->add_field('item_name', 'ZumbaWear ');
					$this->paypal_lib->add_field('custom', $customer_id);
					$this->paypal_lib->add_field('item_number',  $order_id);
					$this->paypal_lib->add_field('amount',  $amountPaypalConvert);
					$this->paypal_lib->add_field('currency_code', $CURRENCY_CODE);
					// Render paypal form
					$paypalReturn=$this->paypal_lib->paypal_auto_form();
				}elseif($payment_method_id==6){ // stripe
					$this->requestStripePayment($order_id,$payment_method_id);
				}
			}else{

			}
		}else{

		}
	}

	public function stripePaymnet()
	{
		// encryt 35

		/*$data['order_id'] = 35;
		$encrypted_order_id = rtrim(base64_encode(json_encode($data)), '=');
		print_r($encrypted_order_id);
		exit();*/

		$shopcode = SHOPCODE;
		$shop_id = SHOP_ID;
		$error='';
		$urlData=$this->uri->segment('2');
		$decoded_data = json_decode(base64_decode($urlData), true);
		$order_id = $decoded_data['order_id'];

		// check if payment method
		$table3 = 'sales_order_payment';
		$flag3 = 'own';
		$where3 = 'order_id = ?';
		$params3=array($order_id);
		$postArr3 = array('table_name'=>$table3,'database_flag'=>$flag3,'where'=>$where3,'params'=>$params3);
		$response3 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr3);
		if(!empty($response3) && isset($response3) && $response3->statusCode==200 && isset($response3->tableData[0]->stripe_success_page_flag) && $response3->tableData[0]->stripe_success_page_flag==0){
			$order_payment_method=$response3->tableData[0]->payment_method_id;
			if($order_payment_method==6 || $order_payment_method==5 || $order_payment_method==4){ // stripe-6, cod-5, viatransfer-4
				//$order_payment_method //old method
				$payment_method_id=6;
				$table2 = 'webshop_payments';
				$flag = 'own';
				$where = 'payment_id = ?';
				$params=array($payment_method_id);
				$postArr2 = array('table_name'=>$table2,'database_flag'=>$flag,'where'=>$where,'params'=>$params);
				$response2 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr2, 3600);


				if(!empty($response2) && isset($response2) && $response2->is_success==true){

					if($response2->tableData[0]->status==1 && $response2->tableData[0]->integrate_with_ws==1){

						// check payment request valid or invalid
						//
						//end
						//$data='';
						//$this->load->view('requestpayment/request_payment', $data);
						$this->requestStripePayment($order_id,$payment_method_id);
					}else{
						redirect(base_url().'paymentfailed');
					}
				}else{
					redirect(base_url().'paymentfailed');
				}

			}else{
				redirect(base_url().'paymentfailed');
			}
		}elseif(empty($response3)){

			$payment_method_id=6;
			// insert payment method

			// $payment_method_id=$payment_method_id;
			$payment_method='stripe_payment';
			$payment_method_name='Stripe';
			$payment_type=2;
			$currency_code='';

			$table1 = 'sales_order';
			$flag1 = 'own';
			$where1 = 'order_id = ?';
			$params1=array($order_id);
			$postArr1 = array('table_name'=>$table1,'database_flag'=>$flag1,'where'=>$where1,'params'=>$params1);
			$response1 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr1);
			if(!empty($response1)&& isset($response1) && $response1->statusCode==200){
				if($order_response->currency_code_session != '' && $order_response->default_currency_flag == 0){
					$currency_code = $order_response->currency_code_session;
				}else{
					$currency_code = CURRENCY_CODE;
				}
			}
			$order_payment_array = array('order_id'=>$order_id,'payment_method_id'=>$payment_method_id,'payment_method'=>$payment_method,'payment_method_name'=>$payment_method_name,'payment_type'=>$payment_type,'currency_code'=>$currency_code);
			CheckoutRepository::insert_payment_method($shopcode,$shop_id,$order_payment_array);

			$table2 = 'webshop_payments';
			$flag = 'own';
			$where = 'payment_id = ?';
			$params=array($payment_method_id);
			$postArr2 = array('table_name'=>$table2,'database_flag'=>$flag,'where'=>$where,'params'=>$params);
			$response2 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr2, 3600);
			if(!empty($response2) && isset($response2) && $response2->is_success==true){

				if($response2->tableData[0]->status==1 && $response2->tableData[0]->integrate_with_ws==1){
					$this->requestStripePayment($order_id,$payment_method_id);
				}else{
					redirect(base_url().'paymentfailed');
				}
			}else{
				redirect(base_url().'paymentfailed');
			}

		}else{
			redirect(base_url().'paymentfailed');
		}
	}

	// payment click
	public function requestStripePayment($order_id,$payment_method){
		$shopcode = SHOPCODE;
		$shop_id = SHOP_ID;
		$payment_method=$payment_method;  // 6 = stripe payment  $_POST['payment_method_id']
		$order_id=$order_id; //$_POST['payment_method']

		// order data
		$table1 = 'sales_order';
		$flag1 = 'own';
		$where1 = 'order_id = ?';
		$params1=array($order_id);
		$postArr1 = array('table_name'=>$table1,'database_flag'=>$flag1,'where'=>$where1,'params'=>$params1);
		$response1 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr1);
		if(!empty($response1)&& isset($response1) && $response1->statusCode==200){
			$order_response=$response1->tableData[0];
			$increment_id=$order_response->increment_id;
			$order_id=$order_response->order_id;
			$encode_oid=base64_encode($increment_id);
			$grand_total=$order_response->grand_total;
			if($order_response->currency_code_session != '' && $order_response->default_currency_flag == 0){
				$total_final_convert = $order_response->grand_total*$order_response->currency_conversion_rate;
				$amountStripeConvert=number_format($total_final_convert, 2, '.', '') * 100;
				$CURRENCY_CODE = $order_response->currency_code_session;
			}else{
				$total_final_convert = $order_response->grand_total;
				$amountStripeConvert=number_format($order_response->grand_total, 2, '.', '') * 100;
				$CURRENCY_CODE = CURRENCY_CODE;
			}

			$orderPaymentArr = array('order_id'=>$order_id,'amount'=>$order_response->grand_total,'payment_amount'=>number_format($total_final_convert, 2, '.', ''),'payment_currency'=>$CURRENCY_CODE);
			$ResponseArr = CheckoutRepository::update_order_payment_status_info($shopcode,$shop_id,$orderPaymentArr);

			//start main database
			$table_main = 'payment_master';
			$flag_main = 'main';
			$where_main = 'id  = ?';
			$params_main = array($payment_method);
			$post_main_array = array('table_name'=>$table_main,'database_flag'=>$flag_main,'where'=>$where_main,'params'=>$params_main);
			$main_payment_master_data= CommonRepository::get_table_data($shopcode,$shop_id,$post_main_array, 3600);
			if(!empty($main_payment_master_data) && isset($main_payment_master_data) && $main_payment_master_data->is_success==true ){
				$keyMainData=json_decode($main_payment_master_data->tableData[0]->gateway_details);
				$keyData=$keyMainData->key;
			}else{
				$keyData='';
			}

			// payment commision
			$table_com = 'payment_com_master';
			$flag_com = 'main';
			$where_com = 'payment_id  = ? AND shop_id = ?';
			$params_com =array($payment_method,$shop_id);
			$post_com_array = array('table_name'=>$table_com,'database_flag'=>$flag_com,'where'=>$where_com,'params'=>$params_com);
			$com_payment_master_data= CommonRepository::get_table_data($shopcode,$shop_id,$post_com_array, 3600);

			$stripe_line_item_heading = GlobalRepository::get_fbc_users_shop()?->result?->org_shop_name ?? '';

			if(!empty($com_payment_master_data) && isset($com_payment_master_data) && $com_payment_master_data->is_success==true ){
				$stripe_line_item_heading=$stripe_line_item_heading;

				if($order_response->currency_code_session != '' && $order_response->default_currency_flag == 0){
					$table_com = 'payment_com_master_currency';
					$flag_com = 'main';
					$where_com = 'payment_com_master_id = ? AND currency_code = ?';
					$params_com =array($com_payment_master_data->tableData[0]->id,$CURRENCY_CODE);
					$post_com_array = array('table_name'=>$table_com,'database_flag'=>$flag_com,'where'=>$where_com,'params'=>$params_com);
					$payment_com_master_currency_data = CommonRepository::get_table_data($shopcode,$shop_id,$post_com_array, 3600);

					if(!empty($com_payment_master_data) && isset($com_payment_master_data) && $com_payment_master_data->is_success==true ){
						$stripe_payment_method=$payment_com_master_currency_data->tableData[0]->payment_method;
					}else{
						$stripe_payment_method=$com_payment_master_data->tableData[0]->payment_method;
					}

				}else{
					$stripe_payment_method=$com_payment_master_data->tableData[0]->payment_method;
				}


				$commison_fee_percent=$com_payment_master_data->tableData[0]->commison_fee_percent;
				$transaction_fee_fixed=$com_payment_master_data->tableData[0]->transaction_fee_fixed;

				if(isset($commison_fee_percent)){
					$commison_fee_percent=$commison_fee_percent;
				}else{$commison_fee_percent=0;}
				if(isset($transaction_fee_fixed)){
					$transaction_fee_fixed=$transaction_fee_fixed;
				}else{$transaction_fee_fixed=0;}

				$split_fbc_percentage = $commison_fee_percent; //percentage main
				$split_fbc_percentage_amount = ($order_response->grand_total * $commison_fee_percent) / 100;
				$split_fbc_fixed = $transaction_fee_fixed;
				$fbc_payment_amount = (number_format($split_fbc_percentage_amount, 2, '.', '') + number_format($split_fbc_fixed, 2, '.', '') ) ;
				// $fbc_payment_amount = (number_format($split_fbc_percentage_amount,2) + $split_fbc_fixed ) ;
				$webshop_payment_amount = $order_response->grand_total - $fbc_payment_amount;

			}else{
				$stripe_line_item_heading='';
				$stripe_payment_method='';
				$commison_fee_percent='';
				$transaction_fee_fixed='';
				$split_fbc_percentage = 0; //percentage main
				$split_fbc_percentage_amount = 0; //amount percentage
				$split_fbc_fixed = 0;
				$fbc_payment_amount = 0;
				$webshop_payment_amount = $order_response->grand_total - $fbc_payment_amount;
			}
			// end comission main

			if($order_response->currency_code_session != '' && $order_response->default_currency_flag == 0){
				//echo $fbc_payment_amount."<br>";
				$fbc_payment_amount_final = $fbc_payment_amount*$order_response->currency_conversion_rate;
				$fbc_payment_amount_final = (number_format($fbc_payment_amount_final, 2, '.', '')) ;
				//echo $fbc_payment_amount_final;
			}else{
				$fbc_payment_amount_final = $fbc_payment_amount;
				//echo $fbc_payment_amount_final;
			}

			if($this->session->userdata('lcode') != ''){
				$locale = $this->session->userdata('lcode');
			}else{
				$locale = 'en';
			}

			$data = array(
				'amount' => $amountStripeConvert,
				'currency' => $CURRENCY_CODE,
				'receipt' => $increment_id,
				'fbc_payment_amount' => $fbc_payment_amount_final * 100,
				'order_id' => $order_id,
				'increment_id' => $increment_id,
				'encode_oid' => $encode_oid,
				'stripe_payment_method' => $stripe_payment_method,
				'stripe_line_item_heading' => $stripe_line_item_heading,
				'locale'=>$locale,
				'order_type'=>'request_order'
			);
			// updated data
			$dataStripe=array('split_fbc_percentage' => $split_fbc_percentage,'split_fbc_percentage_amount' => $split_fbc_percentage_amount,'split_fbc_fixed' => $split_fbc_fixed,'fbc_payment_amount' => $fbc_payment_amount,'webshop_payment_amount' => $webshop_payment_amount, );
			$orderArrStripe = array('order_id'=>$order_id,'stripUpdateData'=>$dataStripe);
			$ResponseArrStripe = CheckoutRepository::update_order_payment_status_info($shopcode,$shop_id,$orderArrStripe);

		}
		// end order data

		$table2 = 'webshop_payments';
		$flag = 'own';
		$where = 'payment_id = ?';
		$params=array($payment_method);
		$postArr2 = array('table_name'=>$table2,'database_flag'=>$flag,'where'=>$where,'params'=>$params);
		$response2 =  CommonRepository::get_table_data($shopcode,$shop_id,$postArr2, 3600);

		if(!empty($response2) && isset($response2) && $response2->is_success==true){

			$Row=$response2->tableData[0];
			$gateway_details=json_decode($Row->gateway_details);
			$connected_stripe_account_id=(isset($gateway_details->connected_stripe_account_id) && $gateway_details->connected_stripe_account_id!='')?$gateway_details->connected_stripe_account_id:'';
			$key_secret=$keyData;

			$cancel_url=base_url().'paymentfailed'; //new payment url
			$success_url=base_url();
			$url=array('cancel_url' => $cancel_url,'success_url' => $success_url );

			/*start request*/
            $pay_request=json_encode($data);
            $orderArr = array('order_id'=>$order_id,'pay_request'=>$pay_request);
            $ResponseArr = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderArr);
            /*end request*/

			$stripe_create_order_data=$this->payment_create_order_stripe($data,$connected_stripe_account_id,$key_secret,$url);
			if(isset($stripe_create_order_data)){
				if(isset($stripe_create_order_data->url)){
					/*$script = "<script>
					window.location = '".$stripe_create_order_data->url."';</script>";
					echo $script;*/
					header('Location:'.$stripe_create_order_data->url);
				}else{
					redirect(base_url().'paymentfailed');
				}
			}else{
				redirect(base_url().'paymentfailed');
			}
		}else{
			redirect(base_url().'paymentfailed');
		}
	}
	// end payment click


	function stripe_success(){
		$data['PageTitle']= 'Thank You';
		$shopcode = SHOPCODE;
		$shop_id = SHOP_ID;

		if($this->session->userdata('LoginID')){
			$customer_id=$this->session->userdata('LoginID');
		}else{
			$customer_id='0';
		}
		$site_url=base_url();
		if(isset($_GET['key'])) {
			$key=$_GET['key'];
			$sessionId='';
		}else{
			$key='';
			$sessionId='';
			// $sessionId=$_GET['sessionId'];
		}


		if(isset($_GET['PayerID'])) {
			$PayerID=$_GET['PayerID'];
		}else{
			$PayerID='';
		}

		if(isset($_GET['key']) && $PayerID != '' ){

			// paypal call
			$increment_id=base64_decode($key);
			$table2 = 'sales_order';
			$flag = 'own';
			$where = 'increment_id = ?';
			$params=array($increment_id);
			$postArr2 = array('table_name'=>$table2,'database_flag'=>$flag,'where'=>$where,'params'=>$params);
			$response2 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr2);

			if(!empty($response2)&& isset($response2) && $response2->statusCode==200){
				// && stripe_success_page_flag=0
				$OrderInfo = $response2->tableData[0];
				$table3 = 'sales_order_payment';
				$flag3 = 'own';
				$where3 = 'order_id = ?';
				$params3=array($OrderInfo->order_id);
				$postArr3 = array('table_name'=>$table3,'database_flag'=>$flag3,'where'=>$where3,'params'=>$params3);
				$response3 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr3);

				if(!empty($response3) && isset($response3) && $response3->statusCode==200 && isset($response3->tableData[0]->stripe_success_page_flag) && $response3->tableData[0]->stripe_success_page_flag==0){

					$orderArr = array('order_id'=>$OrderInfo->order_id,'stripe_success_page_flag'=>1);
					$ResponseArr = CheckoutRepository::update_order_payment_status_info($shopcode,$shop_id,$orderArr);

			   }else{
					$this->template->load('requestpayment/success', $data);
			   }
			}else{
				redirect(base_url().'paymentfailed');
			}

		}elseif(isset($_GET['keys'])) {
			$sessionId;
			$key=$_GET['keys'];
			$increment_id=base64_decode($key);

			$table2 = 'sales_order';
			$flag = 'own';
			$where = 'increment_id = ?';
			$params=array($increment_id);
			$postArr2 = array('table_name'=>$table2,'database_flag'=>$flag,'where'=>$where,'params'=>$params);
			$response2 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr2);

			if(!empty($response2)&& isset($response2) && $response2->statusCode==200){
				// && stripe_success_page_flag=0
				$OrderInfo = $response2->tableData[0];
				$table3 = 'sales_order_payment';
				$flag3 = 'own';
				$where3 = 'order_id = ?';
				$params3=array($OrderInfo->order_id);
				$postArr3 = array('table_name'=>$table3,'database_flag'=>$flag3,'where'=>$where3,'params'=>$params3);
				$response3 = CommonRepository::get_table_data($shopcode,$shop_id,$postArr3);

				if(!empty($response3) && isset($response3) && $response3->statusCode==200 && isset($response3->tableData[0]->stripe_success_page_flag) && $response3->tableData[0]->stripe_success_page_flag==0){

					$this->session->set_userdata('repayment_message','Request repayment successfully');
					$orderArr = array('order_id'=>$OrderInfo->order_id,'stripe_success_page_flag'=>1);
					$ResponseArr = CheckoutRepository::update_order_payment_status_info($shopcode,$shop_id,$orderArr);

			   }else{
					$this->template->load('requestpayment/success', $data);
			   }
			}else{
				redirect(base_url().'paymentfailed');
			}
		}else{
			$increment_id='';
			redirect(base_url().'paymentfailed');
		}
		$this->template->load('requestpayment/success', $data);
	}

	function stripe_failed(){


		$data['PageTitle']= 'Order Failed';

		$shopcode = SHOPCODE;
		$shop_id = SHOP_ID;

		$site_url=base_url();

		/*$key=$_GET['key'];
		if($key!=''){
			$increment_id=base64_decode($key);

			$apiUrl2 = '/webshop/get_table_data'; //get_table_data
			$table2 = 'sales_order';
			$flag = 'own';
			$where = 'increment_id = ?';
			$params=array($increment_id);
			$postArr2 = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'table_name'=>$table2,'database_flag'=>$flag,'where'=>$where,'params'=>$params);
			$response2 = $this->restapi->post_method($apiUrl2,$postArr2);

			if($response2->statusCode==200){
				$OrderInfo = $response2->tableData[0];
				$order_id=$OrderInfo->order_id;

				$orderApiUrl = '/webshop/update_order_status'; //update order status
				$orderArr = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'order_id'=>$order_id,'statue'=>3);  // 3-Cancelled

				$ResponseArr = $this->restapi->post_method($orderApiUrl,$orderArr);


			}else{
				$order_id= '';
			}

		}else{
			$increment_id='';
		}*/

		$this->template->load('requestpayment/failed', $data);
	}

	/*stripe start*/
	private function payment_create_order_stripe($data,$account_id,$key_secret,$url){

		$stripePaymentMethod='';

		if(isset($data)){
			if($data['encode_oid']){
				$this->session->set_userdata('sKey',$data['encode_oid']);
			}
			if(isset($data['stripe_payment_method']) && !empty($data['stripe_payment_method'])){
				$stripePaymentMethod=json_decode($data['stripe_payment_method'], true);
			}
		}
		//$account_id='acct_1JucaVRhcptcc6X3'; //tempary
		//$currency='eur';
		$currency=$data['currency'];
		$item_name=$data['stripe_line_item_heading']; //Zumbawear Order

		//$locale = 'id';
		$locale = $data['locale'];

		$line_items=array('name' =>$item_name,'amount' =>$data['amount'],'currency' =>$currency,'quantity' =>1);
		$dataStripe=array('application_fee_amount' => $data['fbc_payment_amount'], 'line_items'=>$line_items);

         \Stripe\Stripe::setApiKey($key_secret);//

		try {
		$session = \Stripe\Checkout\Session::create([
		 'payment_method_types' => [$stripePaymentMethod],
		 // 'payment_method_types' => ['card','alipay','bancontact','eps','giropay','ideal','klarna','p24','sepa_debit','sofort'],
		  'line_items' => [[$dataStripe['line_items']]],
		  "metadata" => ["order_id" => $data['encode_oid'],"order_type" => $data['order_type']],
          'payment_intent_data' => mb_strpos($key_secret, 'test') === false ? [
		    'application_fee_amount' => $dataStripe['application_fee_amount'],
		  ] : [],
		  'mode' => 'payment',
		  'locale'=>$locale,
		   'success_url' => $url['success_url'].'paymentsuccess/?sessionId={CHECKOUT_SESSION_ID}&keys='.$data['encode_oid'],
		  'cancel_url' => $url['cancel_url'].'?key='.$data['encode_oid'],
		], ['stripe_account' => $account_id]);


		  // Use Stripe's library to make requests...
		} catch(\Stripe\Exception\CardException $e) {
			$this->session->set_userdata('checkout_error','Something went wrong with stripe, please try again.');
			redirect(base_url().'checkout/');
			/*$session=2;
		  // Since it's a decline, \Stripe\Exception\CardException will be caught
		  echo 'Status is:' . $e->getHttpStatus() . '\n';
		  echo 'Type is:' . $e->getError()->type . '\n';
		  echo 'Code is:' . $e->getError()->code . '\n';
		  // param is '' in this case
		  echo 'Param is:' . $e->getError()->param . '\n';
		  echo 'Message is:' . $e->getError()->message . '\n';*/
		} catch (\Stripe\Exception\RateLimitException $e) {
		  // Too many requests made to the API too quickly
			$this->session->set_userdata('checkout_error','Something went wrong with stripe 1, please try again.');
			redirect(base_url().'paymentfailed');
		} catch (\Stripe\Exception\InvalidRequestException $e) {
		  // Invalid parameters were supplied to Stripe's API
			 //print_r($e);exit();
			$this->session->set_userdata('checkout_error','Something went wrong with stripe 2, please try again.');
			redirect(base_url().'paymentfailed');
		} catch (\Stripe\Exception\AuthenticationException $e) {
			$this->session->set_userdata('checkout_error','Something went wrong with stripe 3, please try again.');
			redirect(base_url().'paymentfailed');
		} catch (\Stripe\Exception\ApiConnectionException $e) {
			$this->session->set_userdata('checkout_error','Something went wrong with stripe 4, please try again.');
			redirect(base_url().'paymentfailed');
		} catch (\Stripe\Exception\ApiErrorException $e) {
			$session=6;
			$this->session->set_userdata('checkout_error','Something went wrong with stripe 5, please try again.');
			redirect(base_url().'paymentfailed');
		} catch (Exception $e) {
			$this->session->set_userdata('checkout_error','Something went wrong with stripe 6, please try again.');
			redirect(base_url().'paymentfailed');
		}
		return $session;
    }


	/*stripe end*/

}
