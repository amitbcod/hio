<?php

use App\Actions\Emails\SendShipmentConfirmationEmail;
use App\Actions\Emails\SendTrackingEmail;
use App\Actions\Orders\ProcessOrderWithMissingItems;
use App\Actions\Orders\ProcessPaymentRefund;
use App\Actions\Orders\RecalculateOrderTotals;

use App\Services\Trackers\ShipmentStatusEnum;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property SalesOrderPaymentModel $SalesOrderPaymentModel
 */
class WebshopOrdersController extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		if ($this->session->userdata('LoginID') == '') {
			redirect(base_url());
		}
		if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/orders', $this->session->userdata('userPermission'))) {
			redirect(base_url('dashboard'));
		}

		$this->load->model('UserModel');
		$this->load->model('WebshopOrdersModel');
		$this->load->model('WebshopModel');
		$this->load->model('CommonModel');
		$this->load->model('SalesOrderPaymentModel');
		// $this->load->library('encryption');
	}

	public function index()
	{

		$current_tab = $this->uri->segment(2);
		$data['PageTitle'] = 'Webshop - Orders';
		$data['side_menu'] = 'webshop';
		$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
		//echo $this->WebshopOrdersModel->generate_new_transaction_id().'=====';

		$this->load->view('webshop/order/orderlist', $data);
	}

	public function openPaymentReceivePopup()
	{
		if (isset($_POST['order_id'])) {
			$data['order_id'] = $order_id = $_POST['order_id'];
			$data['salesPaymentData'] = $this->CommonModel->getSingleShopDataByID('sales_order_payment', array('order_id' => $order_id), 'payment_received_note');
			$View = $this->load->view('webshop/order/payment-notes-display-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function GetOrderByIncrementId()
	{

		$increment_id = $_POST['increment_id'];
		$orderdata = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('increment_id' => $increment_id), 'order_id,increment_id,parent_id,main_parent_id,status');
		if (isset($orderdata)) {
			if ($orderdata->main_parent_id != 0) {
				$redirect_url = base_url() . 'webshop/split-order/detail/' . $orderdata->order_id;
			} else if ($orderdata->status == 4 || $orderdata->status == 5 || $orderdata->status == 6) {
				$redirect_url = base_url() . 'webshop/shipped-order/detail/' . $orderdata->order_id;
			} else {
				$redirect_url = base_url() . 'webshop/order/detail/' . $orderdata->order_id;
			}
			$arrResponse  = array('flag' => 1, 'redirect' => $redirect_url);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('flag' => 0, 'message' => 'No order found.');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function loadordersajax()
	{

		$price = $this->input->post('price');
		$order_status = $this->input->post('order_status');
		$shipment_type = $this->input->post('shipment_type');
		$fromDate = $this->input->post('from_date');
		$toDate = $this->input->post('to_date');
		$payment_method = $this->input->post('payment_method');

		$current_tab = $this->input->post('current_tab');


		$shop_id		=	$this->session->userdata('ShopID');



		$ProductData = $this->WebshopOrdersModel->get_datatables_orders($price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method);

		$data = array();
		$no = $_POST['start'];
		foreach ($ProductData as $readData) {
			$no++;
			$row = array();
			$qty = '';

			$order_url = '';

			$payment_type = $this->CommonModel->getPaymentTypeLabel($readData->payment_type);

			if ($current_tab == 'orders') {
				$order_url = base_url() . 'webshop/order/detail/' . $readData->order_id;
				$print_url = base_url() . 'webshop/order/print/' . $readData->order_id;
				$payment_method_name = $readData->payment_method_name;
				$coupon_code = (isset($readData->coupon_code)) ? $readData->coupon_code : '-';
				$voucher_code = (isset($readData->voucher_code)) ? $readData->voucher_code : '-';

				if ($readData->voucher_code != '' && $readData->grand_total <= 0) {
					$payment_method_name = 'Voucher Payment';
				}
			} else if ($current_tab == 'split-orders') {

				$order_url = base_url() . 'webshop/split-order/detail/' . $readData->order_id;
				$print_url = base_url() . 'webshop/order/print/' . $readData->order_id;


				$SalesPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $readData->main_parent_id), 'payment_method_name,payment_type');
				$payment_method_name = (isset($SalesPayment->payment_method_name) && $SalesPayment->payment_method_name != '') ? $SalesPayment->payment_method_name : '-';
				$coupon_code = '-';
				$voucher_code = '-';
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $readData->main_parent_id), '');


				$data['shop_gateway_credentials'] = $shop_gateway_credentials = $this->WebshopModel->shop_gateway_credentials($OrderPayment->payment_method_id);
				if (isset($shop_gateway_credentials['display_name']) && $shop_gateway_credentials['display_name'] != '') {
					$payment_method_name = $shop_gateway_credentials['display_name'];
				} else if (isset($OrderPayment) && $OrderPayment->payment_method_name != '') {
					$payment_method_name = $OrderPayment->payment_method_name;
				} else {
					$payment_method_name = '-';
				}
				if ($readData->voucher_code != '' && $readData->grand_total <= 0) {
					$payment_method_name = 'Voucher Payment';
				}

				$payment_type_id = (isset($SalesPayment->payment_type) && $SalesPayment->payment_type != '') ? $SalesPayment->payment_type : '-';
				$payment_type = $this->CommonModel->getPaymentTypeLabel($payment_type_id);
			} else if ($current_tab == 'b2b-orders') {

				$order_url = base_url() . 'webshop/b2b-order/detail/' . $readData->order_id;
				$print_url = base_url() . 'webshop/order/print/' . $readData->order_id;


				$SalesPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $readData->main_parent_id), 'payment_method_name,payment_type');


				$payment_method_name = (isset($SalesPayment->payment_method_name) && $SalesPayment->payment_method_name != '') ? $SalesPayment->payment_method_name : '-';
				$coupon_code = '-';
				$voucher_code = '-';

				if ($readData->voucher_code != '' && $readData->grand_total <= 0) {
					$payment_method_name = 'Voucher Payment';
				}

				$payment_type_id = (isset($SalesPayment->payment_type) && $SalesPayment->payment_type != '') ? $SalesPayment->payment_type : '-';
				$payment_type = $this->CommonModel->getPaymentTypeLabel($payment_type_id);
			} else if ($current_tab == 'shipped-orders') {
				$order_url = base_url() . 'webshop/shipped-order/detail/' . $readData->order_id;
				$print_url = base_url() . 'webshop/shipped-order/print/' . $readData->order_id;

				$payment_method_name = $readData->payment_method_name;
				$coupon_code = (isset($readData->coupon_code)) ? $readData->coupon_code : '-';
				$voucher_code = (isset($readData->voucher_code)) ? $readData->voucher_code : '-';

				if ($readData->voucher_code != '' && $readData->grand_total <= 0) {
					$payment_method_name = 'Voucher Payment';
				}
			} else if ($current_tab == 'cancel-orders') {
				/*$order_url=base_url().'webshop/order/detail/'.$readData->order_id;
				$print_url=base_url().'webshop/order/print/'.$readData->order_id;
				$payment_method_name=$readData->payment_method_name;
				$coupon_code = (isset($readData->coupon_code))? $readData->coupon_code : '-';
				$voucher_code = (isset($readData->voucher_code))? $readData->voucher_code : '-';

				if($readData->voucher_code!='' && $readData->grand_total<=0){
					$payment_method_name='Voucher Payment';
				}*/

				if ($readData->main_parent_id > 0) {
					$order_url = base_url() . 'webshop/split-order/detail/' . $readData->order_id;
				} else {
					$order_url = base_url() . 'webshop/order/detail/' . $readData->order_id;
				}

				$print_url = base_url() . 'webshop/order/print/' . $readData->order_id;
				$payment_method_name = $readData->payment_method_name;
				$coupon_code = (isset($readData->coupon_code)) ? $readData->coupon_code : '-';
				$voucher_code = (isset($readData->voucher_code)) ? $readData->voucher_code : '-';

				if ($readData->voucher_code != '' && $readData->grand_total <= 0) {
					$payment_method_name = 'Voucher Payment';
				}
			} else {
				$print_url = base_url();
				$order_url = base_url();
				$payment_method_name = '-';
			}



			$orderStatus = $this->CommonModel->getOrderStatusLabel($readData->status);
			//$shipment_type_label=$payment_method_name;
			if ($orderStatus == 'Cancelled' && $readData->cancel_by_customer == 1) {
				$orderStatus = "Cancelled <span class='glyphicon glyphicon-question-sign tooltip'><i class='fa fa-info'></i><span class='tooltiptext'>" . $readData->cancel_reason . "</span></span>";
			} elseif ($orderStatus == 'Cancelled' && $readData->cancel_by_customer == 0 && ($readData->cancel_by_admin == 0 || $readData->cancel_by_admin == '')) {
				$orderStatus = "Cancelled <span class='glyphicon glyphicon-question-sign tooltip'><i class='fa fa-info'></i><span class='tooltiptext'>Payment Gateway failure</span></span>";
			} elseif ($readData->status == '3') {
				$orderStatus = "Cancelled <span class='glyphicon glyphicon-question-sign tooltip'><i class='fa fa-info'></i><span class='tooltiptext'>" . $readData->cancel_reason . "</span></span>";
			}

			$row[] = $readData->increment_id;
			$row[] = date(SIS_DATE_FM_WT, $readData->created_at);
			$row[] = ucwords($readData->customer_name) . ' ' . (isset($readData->company_name) && $readData->company_name != '' ? '- ' . $readData->company_name : '');
			//$row[]= '-';

			$row[] = ($payment_method_name == "Cheque/Funds Transfer") 
			? 'Pending' 
			: (($payment_method_name == "Cc Avenue") 
				? $orderStatus 
				: '');

			$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');

			if ($use_advanced_warehouse->value == "yes") {
				$warehouse_status = $readData->warehouse_status;
				$row[] = $this->CommonModel->getWarehouse_status_name($warehouse_status);
			}
			$row[] =  $payment_method_name;
			$row[] = $coupon_code;
			$row[] = $voucher_code;

			$refrundStatus = '';
			if (empty($readData->cancel_refund_type) && $readData->status == 3) {
				$refrundStatus = '<button name="refund_order_btn" class="purple-btn refund_order_btn" data-toggle="modal" id="refund_order_btn" data-id="'.$readData->order_id.'" value="'.$readData->order_id.'" data-target="#refund-order-modal" onClick="refundPayment('.$readData->order_id.')">Refund Initiate</button>';
			} elseif ($readData->cancel_refund_type==2) {
				$refrundStatus = 'Refund done';
			}
			$row[] = $refrundStatus;

			$row[] = '<a class="link-purple " target="_blank" href="' . $print_url . '">Print</a>';
			$row[] = '<a class="link-purple" href="' . $order_url . '">View</a>';

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->WebshopOrdersModel->count_all_orders($price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method),
			"recordsFiltered" => $this->WebshopOrdersModel->count_filtered_orders($price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method),
			"data" => $data,
		);

		//output to json format
		echo json_encode($output);
		exit;
	}

	/*function detail(){
		$current_tab=$this->uri->segment(2);
		$order_id=$this->uri->segment(4);
		if(isset($order_id) && $order_id>0){
			$data['PageTitle']='Webshop - Orders';
			$data['side_menu']='webshop';
			$data['current_tab']=(isset($current_tab) && $current_tab!='')?$current_tab:'';

			$shop_id		=	$this->session->userdata('ShopID');
			$data['product_delivery_duration'] = $product_delivery_duration = $this->CommonModel->getSingleShopDataByID('custom_variables as cv',array('identifier'=>'product_delivery_duration'),'cv.*');
			// print_r($data['product_delivery_duration']);
			//echo $this->WebshopOrdersModel->generate_new_transaction_id().'=====';
			$data['OrderData']=$OrderData=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');

			if(empty($OrderData)){
				redirect('/webshop/orders');
			}

			$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);

			$data['OrderItems']=$OrderItems=$this->WebshopOrdersModel->getOrderItems($order_id);

			$QtyScanItem=$this->WebshopOrdersModel->getQtyFullyScannedOrderItems($order_id);
			$data['scanned_qty']=count($QtyScanItem);
			$PendingScanQty=$this->WebshopOrdersModel->getQtyPendingScannedOrderItems($order_id);
			$data['pending_scanned_qty']=count($PendingScanQty);

			$b2b_orders=array();

			$b2bShops=$this->WebshopOrdersModel->getWebshopB2BShops($order_id);

			$b2b_subtotal=0;
			$b2b_discount_total=0;
			$b2b_grand_total=0;
			if(isset($b2bShops) && count($b2bShops)>0){

				foreach($b2bShops as $key=>$shop){
					$b2b_shop_id=$shop->shop_id;
					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$b2b_shop_id),'');
					$b2b_fbc_user_id=$FbcUser->fbc_user_id;

					$args['shop_id']	=	$b2b_shop_id;
					$args['fbc_user_id']	=	$b2b_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);

					$b2b_order_list=$this->ShopProductModel->getMultiDataById('b2b_orders',array('webshop_order_id'=>$order_id),'order_id,increment_id,is_split,subtotal,grand_total,discount_amount,discount_percent','order_id','ASC');
					if(isset($b2b_order_list) && count($b2b_order_list)>0){
						foreach($b2b_order_list as $value){
							$b2b_orders[$key]['increment_id']=$value->increment_id;
							$b2b_orders[$key]['order_id']=$value->order_id;
							$b2b_orders[$key]['shop_id']=$b2b_shop_id;
							$b2b_orders[$key]['is_split']=$value->is_split;
							$b2b_subtotal=$b2b_subtotal+$value->subtotal;
							$b2b_discount_total=$b2b_discount_total+$value->discount_amount;
							$b2b_grand_total=$b2b_grand_total+$value->grand_total;
						}
					}
				}
			}

			$data['b2b_orders']=$b2b_orders;

			$data['b2b_subtotal']=$b2b_subtotal;
			$data['b2b_grand_total']=$b2b_grand_total;
			$data['b2b_discount_total']=$b2b_discount_total;


			if($current_tab=='order')
			{
				if($OrderData->status==1){
					redirect(base_url().'webshop/order/create-shipment/'.$OrderData->order_id);
				}
				$data['ShippingAddress']=$ShippingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>2),'');
				$data['BillingAddress']=$BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>1),'');
				$data['OrderPaymentDetail']=$OrderPayment=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->order_id),'');

				if(isset($OrderPayment)) {
					$data['shop_gateway_credentials'] = $this->WebshopModel->shop_gateway_credentials($OrderPayment->payment_method_id);
				}

				$this->load->view('webshop/order/main-order-detail',$data);

			}else if($current_tab=='split-order'){
				if($OrderData->status==1){
					redirect(base_url().'webshop/order/create-shipment/'.$OrderData->order_id);
				}
				$data['ShippingAddress']=$ShippingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->main_parent_id,'address_type'=>2),'');
				$data['BillingAddress']=$BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->main_parent_id,'address_type'=>1),'');
				$data['ParentOrder']=$ParentOrder=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$OrderData->main_parent_id),'');
				$data['SplitOrderIds']=$this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->main_parent_id);

				$data['OrderPaymentDetail']=$OrderPayment=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->main_parent_id),'');

				if(isset($OrderPayment)) {
					$data['shop_gateway_credentials'] = $this->WebshopModel->shop_gateway_credentials($OrderPayment->payment_method_id);
				}

				$this->load->view('webshop/order/split-order-detail',$data);
			}else if($current_tab=='shipped-order'){
				$data['ShippingAddress']=$ShippingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>2),'');
				$data['BillingAddress']=$BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>1),'');
				$data['SplitOrderIds']=$this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->order_id);
				$data['ShippedItem']=$ShippedItem=$this->WebshopOrdersModel->getShippedOrderItems($OrderData->order_id,$OrderData->is_split);
				$data['OrderPaymentDetail']=$OrderPayment=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->order_id),'');

				if(isset($OrderPayment)) {
					$data['shop_gateway_credentials'] = $this->WebshopModel->shop_gateway_credentials($OrderPayment->payment_method_id);
				}

				$this->load->view('webshop/order/shipped-order-detail',$data);
			}else{
				redirect('/webshop/orders');
			}
		}else{
			redirect('/webshop/orders');
		}
	}*/

	function detail()
	{
		$current_tab = $this->uri->segment(2);

		$order_id = $this->uri->segment(4);
		if (isset($order_id) && $order_id > 0) {
			$data['PageTitle'] = 'Webshop - Orders';
			$data['side_menu'] = 'webshop';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';



			//$shop_id		=	$this->session->userdata('ShopID');

			$data['product_delivery_duration'] = $product_delivery_duration = $this->CommonModel->getSingleShopDataByID('custom_variables as cv', array('identifier' => 'product_delivery_duration'), 'cv.*');
			//echo $this->WebshopOrdersModel->generate_new_transaction_id().'=====';
			$data['OrderData'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			if (empty($OrderData)) {
				redirect('/webshop/orders');
			}


			$data['orderReturnData'] = $orderReturnData = $this->WebshopOrdersModel->getReturnDataById($order_id);
			// $data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);
			$data['currency_code'] = '';
			$data['OrderItems'] = $OrderItems = $this->WebshopOrdersModel->getOrderItems($order_id);
			$QtyScanItem = $this->WebshopOrdersModel->getQtyFullyScannedOrderItems($order_id);
			$data['scanned_qty'] = count($QtyScanItem);
			$PendingScanQty = $this->WebshopOrdersModel->getQtyPendingScannedOrderItems($order_id);
			$data['pending_scanned_qty'] = count($PendingScanQty);
			$b2b_orders = array();
			$b2bShops = $this->WebshopOrdersModel->getWebshopB2BShops($order_id);
			$b2b_subtotal = 0;
			$b2b_discount_total = 0;
			$b2b_grand_total = 0;

			/*order cancel*/
			$b2b_order_status_zero = 0;
			$data['cancel_order'] = '';
			/*end order cancel*/

			// echo count($b2bShops);exit();
			if (isset($b2bShops) && count($b2bShops) > 0) {

				foreach ($b2bShops as $key => $shop) {
					$b2b_shop_id = $shop->shop_id;
					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $b2b_shop_id), '');
					$b2b_fbc_user_id = $FbcUser->fbc_user_id;

					$args['shop_id']	=	$b2b_shop_id;
					$args['fbc_user_id']	=	$b2b_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);

					//$b2b_order_list=$this->ShopProductModel->getMultiDataById('b2b_orders',array('webshop_order_id'=>$order_id),'order_id,increment_id,is_split,subtotal,grand_total,discount_amount,discount_percent','order_id','ASC'); //old
					$b2b_order_list = $this->ShopProductModel->getMultiDataById('b2b_orders', array('webshop_order_id' => $order_id, 'shop_id' => $shop_id), 'status,order_id,increment_id,is_split,subtotal,grand_total,discount_amount,discount_percent,main_parent_id', 'order_id', 'ASC');
					// order cancel add status
					if (isset($b2b_order_list) && count($b2b_order_list) > 0) {
						foreach ($b2b_order_list as $key => $value) {
							if (count($b2b_order_list) == 1) {
								// foreach($b2b_order_list as $value){ //old issue $key
								$b2b_orders[$key]['increment_id'] = $value->increment_id;
								$b2b_orders[$key]['order_id'] = $value->order_id;
								$b2b_orders[$key]['shop_id'] = $b2b_shop_id;
								$b2b_orders[$key]['is_split'] = $value->is_split;
								$b2b_subtotal = $b2b_subtotal + $value->subtotal;
								$b2b_discount_total = $b2b_discount_total + $value->discount_amount;
								$b2b_grand_total = $b2b_grand_total + $value->grand_total;
							} else {
								if ($this->uri->segment(2) == 'shipped-order') {
									if ($value->main_parent_id != 0) {
										// if($value->main_parent_id!=0 && $this->uri->segment(2)=='shipped-order'){
										$b2b_orders[$key]['increment_id'] = $value->increment_id;
										$b2b_orders[$key]['order_id'] = $value->order_id;
										$b2b_orders[$key]['shop_id'] = $b2b_shop_id;
										$b2b_orders[$key]['is_split'] = $value->is_split;
										$b2b_subtotal = $b2b_subtotal + $value->subtotal;
										$b2b_discount_total = $b2b_discount_total + $value->discount_amount;
										$b2b_grand_total = $b2b_grand_total + $value->grand_total;
									}
								} else {
									$b2b_orders[$key]['increment_id'] = $value->increment_id;
									$b2b_orders[$key]['order_id'] = $value->order_id;
									$b2b_orders[$key]['shop_id'] = $b2b_shop_id;
									$b2b_orders[$key]['is_split'] = $value->is_split;
									$b2b_subtotal = $b2b_subtotal + $value->subtotal;
									$b2b_discount_total = $b2b_discount_total + $value->discount_amount;
									$b2b_grand_total = $b2b_grand_total + $value->grand_total;
								}
							} //if end

							/*order cancel*/
							if ($value->status == 0) {
								$b2b_order_status_zero++;
							}
							/*end order cancel*/
						}
					} // end if
				}
			}
			/*echo $b2b_order_status_zero;
			echo $b2b_order_status_zero;
			exit();*/
			$data['b2b_orders'] = $b2b_orders;

			$data['b2b_subtotal'] = $b2b_subtotal;
			$data['b2b_grand_total'] = $b2b_grand_total;
			$data['b2b_discount_total'] = $b2b_discount_total;
			/*order cancel*/
			/*order cancel*/
			if (isset($b2b_order_list) && count($b2b_order_list) > 0) {
				if ($OrderData->status == 0 && (count($b2b_order_list) == $b2b_order_status_zero)) {
					$data['cancel_order'] = 'able_to_cancel';
				}
			} else {
				if ($OrderData->status == 0) {
					$data['cancel_order'] = 'able_to_cancel';
				}
			}

			/*end order cancel*/
			/*if($OrderData->status==0 ){
				$data['cancel_order']='able_to_cancel';
			}*/
			// $data['cancel_order']='able_to_cancel';
			// $OrderData->status;
			// $OrderData->order_id;

			/*end order cancel*/
			$data['use_advanced_warehouse'] = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');



			if ($current_tab == 'order') {
				// print_r($OrderData);
				// die();
				// if ($OrderData->status == 1) {
				// 	redirect(base_url() . 'webshop/order/create-shipment/' . $OrderData->order_id);
				// }
				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 1), '');
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->order_id), '');

				//$data['OrderRefundDetail'] = $this->SalesOrderPaymentModel->get_order_refunds($OrderData->order_id);
				$data['OrderRefundDetail'] =  $OrderRefundDetail = $this->WebshopOrdersModel->getSingleDataByID('refund_payment', array('order_id' => $OrderData->order_id), '');

				if (isset($OrderPayment)) {
					$data['shop_gateway_credentials'] = $this->WebshopModel->shop_gateway_credentials($OrderPayment->payment_method_id);
				}
				$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->order_id), '');

				// echo "<pre style='display:hidden'>";
				// print_r($OrderData);

				$this->load->view('webshop/order/main-order-detail', $data);
			} else if ($current_tab == 'split-order') {
				if ($OrderData->status == 1) {
					redirect(base_url() . 'webshop/order/create-shipment/' . $OrderData->order_id);
				}
				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 1), '');
				$data['ParentOrder'] = $ParentOrder = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->main_parent_id), '');
				$data['SplitOrderIds'] = $this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->main_parent_id);

				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->main_parent_id), '');
				//$data['OrderRefundDetail'] = $this->SalesOrderPaymentModel->get_order_refunds($OrderData->main_parent_id);

				if (isset($OrderPayment)) {
					$data['shop_gateway_credentials'] = $this->WebshopModel->shop_gateway_credentials($OrderPayment->payment_method_id);
				}

				$this->load->view('webshop/order/split-order-detail', $data);
			} else if ($current_tab == 'shipped-order') {

				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 1), '');
				$data['SplitOrderIds'] = $this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->order_id);
				$data['ShippedItem'] = $ShippedItem = $this->WebshopOrdersModel->getShippedOrderItems($OrderData->order_id, $OrderData->is_split);
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->order_id), '');
				//$data['OrderRefundDetail'] = $this->SalesOrderPaymentModel->get_order_refunds($OrderData->order_id);

				$data['CancelItem'] = $ShippedItem = $this->WebshopOrdersModel->getCancelOrderItems($OrderData->order_id, $OrderData->is_split); //new added delete order 16-11-2021
				if (isset($OrderPayment)) {
					$data['shop_gateway_credentials'] = $this->WebshopModel->shop_gateway_credentials($OrderPayment->payment_method_id);
				}
				$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->order_id), '');
				$this->load->view('webshop/order/shipped-order-detail', $data);
			} else {
				redirect('/webshop/orders');
			}
		} else {
			redirect('/webshop/orders');
		}
	}

	function scanbarcodemanually()
	{
		if (isset($_POST['order_id']) && isset($_POST['barcode_item'])) {
			$order_id = $_POST['order_id'];
			$barcode = $_POST['barcode_item'];
			$current_tab = $_POST['current_tab'];
			$ItemExist = $this->WebshopOrdersModel->checkOrderItemsExist($order_id, $barcode);
			if (isset($ItemExist) && $ItemExist->item_id != '') {

				$item_id = $ItemExist->item_id;
				$qty_ordered = $ItemExist->qty_ordered;
				$old_qty_scanned = $ItemExist->qty_scanned;

				$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,is_split');

				if ($current_tab == 'split-order') {
					$main_oi_qty = $this->WebshopOrdersModel->getMainOrderItemQty($OrderData->main_parent_id, $ItemExist->product_id);
					$main_qty_ordered = $main_oi_qty;
				} else {

					$main_qty_ordered = $ItemExist->qty_ordered;
				}


				if (($current_tab == 'order') && ($old_qty_scanned == $qty_ordered)) {
					$arrResponse  = array('status' => 400, 'message' => 'Barcode <b>' . $barcode . '</b> already scanned all quantity.');
					echo json_encode($arrResponse);
					exit;
				}
				/*
				else if(($current_tab=='split-order') && ($old_qty_scanned==$qty_ordered)){
					$arrResponse  = array('status' =>400 ,'message'=>'Barcode already scanned all quantity.');
					echo json_encode($arrResponse);exit;

				}
				*/ else {

					if ($old_qty_scanned < $main_qty_ordered) {
						$this->WebshopOrdersModel->incrementOrderItemQtyScanned($item_id);  //increament qty_scanned
					}

					$item_class = $this->WebshopOrdersModel->getOrderItemRowClass($item_id);


					$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,is_split');
					if ($OrderData->is_split == 0) {
						$QtyScanItem = $this->WebshopOrdersModel->getQtyFullyScannedOrderItems($order_id);
						$AllItems = $this->WebshopOrdersModel->getOrderItems($order_id);
						if (count($QtyScanItem) == count($AllItems)) {
							$odr_update = array('system_generated_split_order' => 0, 'updated_at' => time());
							$where_arr = array('order_id' => $order_id);
							$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $odr_update);
						}
					}


					$arrResponse  = array('status' => 200, 'message' => 'Barcode scanned successfully.', 'item_id' => $item_id, 'item_class' => $item_class);
					echo json_encode($arrResponse);
					exit;
				}
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Barcode <b>' . $barcode . '</b> does not belongs to this order!');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function refreshOrderItems()
	{
		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$current_tab = $_POST['current_tab'];
			$data['current_tab'] = $current_tab;
			$data['use_advanced_warehouse'] = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');
			$data['OrderData'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,status');
			$shop_id		=	$this->session->userdata('ShopID');
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);
			$data['OrderItems'] = $OrderItems = $this->WebshopOrdersModel->getOrderItems($order_id);
			$QtyScanItem = $this->WebshopOrdersModel->getQtyFullyScannedOrderItems($order_id);
			$data['scanned_qty'] = count($QtyScanItem);
			$PendingScanQty = $this->WebshopOrdersModel->getQtyPendingScannedOrderItems($order_id);
			$data['pending_scanned_qty'] = count($PendingScanQty);
			$View = $this->load->view('webshop/order/order-items', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function openSplitOrderPopup()
	{
		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$shop_id		=	$this->session->userdata('ShopID');

			$data['order_id'] = $order_id;
			$View = $this->load->view('webshop/order/split-confirmation-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function splitorderconfirm()
	{
		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$split_message = $_POST['split_message'];

			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$shop_upload_path = 'shop' . $shop_id;

			$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');

			//$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$OrderData->shop_id),'');
			$discount_percent = 0;


			$QtyScanItem = $this->WebshopOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);

			if (count($QtyScanItem) <= 0) {
				$arrResponse  = array('status' => 400, 'message' => 'Order can not split, Please scan completely atleast one item.');
				echo json_encode($arrResponse);
				exit;
			}

			$parent_id = $OrderData->parent_id;

			$increment_id = $OrderData->increment_id;
			/*if( strpos($increment_id, '-') !== false ) {
				$temp_inc_id=$increment_id;
				$inc_id_arr=explode('-',$increment_id);
				$middle_inc_id=$inc_id_arr[0];
			}else{
				$temp_inc_id=$increment_id;
				$inc_id_arr=array();
				$middle_inc_id=$temp_inc_id;

			}*/
			$split_Data = is_split_order($increment_id);

			$next_letter = '';
			$inc_prefix = '';

			//$order_shop_id=$OrderData->shop_id;

			$split_order_one = '';
			$split_flag = '';


			if ($OrderData->is_split == 0) {

				//if( strpos($temp_inc_id, '-') !== false ) {
				if ($split_Data['split_flag'] == 1) {

					$next_letter	=	$split_Data['last_splited'];
					//$next_letter 	=	get_next_letter($next_letter);
					$order_one		=	$inc_prefix . $split_Data['order_id'] . '-' . $next_letter;
					$new_next_letter 	=	get_next_letter($next_letter);
					$order_two		=	$inc_prefix . $split_Data['order_id'] . '-' . $new_next_letter;
					$main_parent_id	=	$OrderData->main_parent_id;
					$split_flag = 2;
				} else {

					$next_letter = 'A';
					$order_one		=	$inc_prefix . $split_Data['order_id'] . '-' . $next_letter;
					$new_next_letter 	=	get_next_letter($next_letter);
					$order_two		=	$inc_prefix . $split_Data['order_id'] . '-' . $new_next_letter;
					$main_parent_id	=	$OrderData->order_id;
					$split_flag = 1;
				}

				//echo $temp_inc_id.'-----'.$order_one.'-----'.$order_two.'-----'.$split_flag.'-----';exit;
				$QtyScanItem = $this->WebshopOrdersModel->getQtyFullyScannedOrderItems($order_id);


				//$QtyScanItem=$this->WebshopOrdersModel->getQtyFullyScannedOrderItems($order_id);
				$QtyPartialScanItem = $this->WebshopOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);
				$QtyPendingItem = $this->WebshopOrdersModel->getQtyPendingScannedOrderItems($order_id);

				//echo '------'.count($QtyPartialScanItem).'=============='.count($QtyPendingItem);


				if (isset($split_flag) && $split_flag == 2) {

					$this->WebshopOrdersModel->removeOldOrderItems($order_id);

					if (isset($QtyPartialScanItem) && count($QtyPartialScanItem) > 0) {
						$sutotal_one = 0;
						$totalqty_one = 0;
						foreach ($QtyPartialScanItem as $item) {
							$total_price = 0;
							$total_price = $item->price * $item->qty_scanned;
							$sutotal_one = $total_price + $sutotal_one;
							$totalqty_one = $totalqty_one + $item->qty_scanned;
						}

						/*****---------calculate net pay-----------------------****/

						$percent_amount = 0;
						$grandtotal_one = $sutotal_one;

						$split_update = array('grand_total' => $grandtotal_one, 'subtotal' => $sutotal_one, 'base_grand_total' => $grandtotal_one, 'base_subtotal' => $sutotal_one, 'total_qty_ordered' => $totalqty_one, 'system_generated_split_order' => 0, 'updated_at' => time());
						$where_arr = array('order_id' => $order_id);

						$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $split_update);

						$split_order_one = $order_id;
						if ($split_order_one) {

							foreach ($QtyPartialScanItem as $item) {

								$insertdataitem = array(
									'order_id' => $split_order_one,
									'product_id' => $item->product_id,
									'parent_product_id' => $item->parent_product_id,
									'product_type' => $item->product_type,
									'product_inv_type' => $item->product_inv_type,
									'product_name' => $item->product_name,
									'product_code' => $item->product_code,
									'sku' => $item->sku,
									'barcode' => $item->barcode,
									'product_variants' => $item->product_variants,
									'qty_ordered' => $item->qty_ordered,
									'qty_scanned' => $item->qty_scanned,
									'price' => $item->price,
									'total_price' => $item->total_price,
									'tax_percent' => $item->tax_percent,
									'tax_amount' => $item->tax_amount,
									'discount_amount' => $item->discount_amount,
									'applied_rule_ids' => $item->applied_rule_ids,
									'created_by' => $fbc_user_id,
									'created_by_type' => 1,
									'created_at' => time(),
									'ip' => $_SERVER['REMOTE_ADDR']
								);

								$this->WebshopOrdersModel->insertData('sales_order_items', $insertdataitem);
							}
						}
					}
				} else {


					if (isset($QtyPartialScanItem) && count($QtyPartialScanItem) > 0) {
						$sutotal_one = 0;
						$totalqty_one = 0;

						if (isset($QtyPartialScanItem) && count($QtyPartialScanItem) > 0) {
							foreach ($QtyPartialScanItem as $item) {

								$total_price = 0;
								$total_price = $item->price * $item->qty_scanned;
								$sutotal_one = $total_price + $sutotal_one;
								$totalqty_one = $totalqty_one + $item->qty_scanned;
							}
						}


						/*****---------calculate net pay-----------------------****/

						$percent_amount = 0;
						$grandtotal_one = $sutotal_one;



						$insertdataone = array(
							'increment_id' => $order_one,
							'order_barcode' => $order_one,
							'shipment_type' => $OrderData->shipment_type,
							'customer_email' => $OrderData->customer_email,
							'customer_firstname' => $OrderData->customer_firstname,
							'customer_lastname' => $OrderData->customer_lastname,
							'checkout_method' => $OrderData->checkout_method,
							'payment_method' => $OrderData->payment_method,
							'status' => 1,
							'parent_id' => $OrderData->order_id,
							'main_parent_id' => $main_parent_id,

							'base_grand_total' => $grandtotal_one,
							'base_subtotal' => $sutotal_one,
							'grand_total' => $grandtotal_one,

							'discount_amount'	=> $percent_amount,
							'subtotal' => $sutotal_one,
							'total_qty_ordered' => $totalqty_one,
							'system_generated_split_order' => 0,
							'created_by' => $fbc_user_id,

							'created_at' => time(),
							'ip' => $_SERVER['REMOTE_ADDR']
						);

						$split_order_one = $this->WebshopOrdersModel->insertData('sales_order', $insertdataone);

						if ($split_order_one) {


							foreach ($QtyPartialScanItem as $item) {



								$insertdataitem = array(
									'order_id' => $split_order_one,
									'product_id' => $item->product_id,
									'parent_product_id' => $item->parent_product_id,
									'product_type' => $item->product_type,
									'product_inv_type' => $item->product_inv_type,
									'product_name' => $item->product_name,
									'product_code' => $item->product_code,
									'sku' => $item->sku,
									'barcode' => $item->barcode,
									'product_variants' => $item->product_variants,
									'qty_ordered' => $item->qty_ordered,
									'qty_scanned' => $item->qty_scanned,
									'price' => $item->price,
									'total_price' => $item->total_price,
									'tax_percent' => $item->tax_percent,
									'tax_amount' => $item->tax_amount,
									'discount_amount' => $item->discount_amount,
									'applied_rule_ids' => $item->applied_rule_ids,
									'created_by' => $fbc_user_id,
									'created_by_type' => 1,
									'created_at' => time(),
									'ip' => $_SERVER['REMOTE_ADDR']
								);

								$this->WebshopOrdersModel->insertData('sales_order_items', $insertdataitem);
							}
						}
					}
				}


				/*-------------------Pending item------------------------------*/



				if (isset($QtyPendingItem) && count($QtyPendingItem) > 0) {
					$sutotal_two = 0;
					$totalqty_two = 0;
					foreach ($QtyPendingItem as $item) {
						$total_price = 0;
						$total_price = $item->price * $item->qty_ordered;
						$sutotal_two = $total_price + $sutotal_two;
						$totalqty_two = $totalqty_two + $item->qty_ordered;
					}

					/*****---------calculate net pay-----------------------****/

					$percent_amount = 0;
					$grandtotal_two = $sutotal_two;

					$insertdatatwo = array(
						'increment_id' => $order_two,
						'order_barcode' => $order_two,
						'shipment_type' => $OrderData->shipment_type,
						'customer_email' => $OrderData->customer_email,
						'customer_firstname' => $OrderData->customer_firstname,
						'customer_lastname' => $OrderData->customer_lastname,
						'checkout_method' => $OrderData->checkout_method,
						'payment_method' => $OrderData->payment_method,
						'status' => 0,
						'parent_id' => $OrderData->order_id,
						'main_parent_id' => $main_parent_id,

						'base_grand_total' => $grandtotal_two,
						'base_subtotal' => $sutotal_two,
						'grand_total' => $grandtotal_two,

						'discount_amount'	=> $percent_amount,
						'subtotal' => $sutotal_two,
						'total_qty_ordered' => $totalqty_two,
						'system_generated_split_order' => 1,
						'created_by' => $fbc_user_id,

						'created_at' => time(),
						'ip' => $_SERVER['REMOTE_ADDR']
					);

					$split_order_two = $this->WebshopOrdersModel->insertData('sales_order', $insertdatatwo);

					if ($split_order_two) {

						foreach ($QtyPendingItem as $item2) {

							$CheckPartialScanned = $this->WebshopOrdersModel->getPartialScannedSingleOrderItems($order_id, $item2->product_id);
							if (isset($CheckPartialScanned) && $CheckPartialScanned->item_id != '') {
								$qty_ordered = $CheckPartialScanned->qty_ordered - $CheckPartialScanned->qty_scanned;
							} else {
								$qty_ordered = $item2->qty_ordered;
							}


							$insertdataitem = array(
								'order_id' => $split_order_two,
								'product_id' => $item2->product_id,
								'parent_product_id' => $item2->parent_product_id,
								'product_type' => $item2->product_type,
								'product_inv_type' => $item->product_inv_type,
								'product_name' => $item2->product_name,
								'product_code' => $item2->product_code,
								'sku' => $item2->sku,
								'barcode' => $item2->barcode,
								'product_variants' => $item2->product_variants,
								'qty_ordered' => $qty_ordered,
								'qty_scanned' => 0,
								'price' => $item2->price,
								'total_price' => $item2->total_price,
								'tax_percent' => $item2->tax_percent,
								'tax_amount' => $item2->tax_amount,
								'discount_amount' => $item2->discount_amount,
								'applied_rule_ids' => $item2->applied_rule_ids,
								'created_by' => $fbc_user_id,
								'created_by_type' => 1,
								'created_at' => time(),
								'ip' => $_SERVER['REMOTE_ADDR']
							);

							$this->WebshopOrdersModel->insertData('sales_order_items', $insertdataitem);
						}
					}
				}

				/*------------update main order-----------------------------*/
				$split_update = array('is_split' => 1, 'status' => 1, 'updated_at' => time());
				$where_arr = array('order_id' => $order_id);

				$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $split_update);

				$SplitOrderIdsName = $order_one . ' & ' . $order_two;

				/*----------------Send Email to shop owner--------------------*/
				$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
				$webshop_details = $this->CommonModel->get_webshop_details($shop_id);
				$owner_email = $shop_owner->email;
				$templateId = 'fbcuser-split-order';
				// $to = $owner_email;//old 16-11-2021 updated
				$to = $OrderData->customer_email;
				$shop_name = $shop_owner->org_shop_name;
				// $username = $shop_owner->owner_name;//old 16-11-2021 updated
				$username = $OrderData->customer_firstname . ' ' . $OrderData->customer_lastname;
				$site_logo = '';
				if (isset($webshop_details)) {
					$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				}
				$burl = base_url();
				$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
				$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
						<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
					</a>';
				$TempVars = array("##OWNER##", "##ORDERID##", "##ORDER_NOTE##", "##SPLITORDERID##", '##WEBSHOPNAME##');
				$DynamicVars   = array($username, $increment_id, $split_message, $SplitOrderIdsName, $shop_name);
				$CommonVars = array($site_logo, $shop_name);
				if ($OrderData->main_parent_id > 0) {
					$ParentOrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->main_parent_id), '');
					$is_default_language = $ParentOrderData->is_default_language;
					$language_code = $ParentOrderData->language_code;
				} else {
					$is_default_language = $OrderData->is_default_language;
					$language_code = $OrderData->language_code;
				}
				if ($is_default_language != 1 && $language_code != '') {
					$lang_code = $language_code;
				} else {
					$lang_code = '';
				}
				if (isset($templateId)) {
					$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
					if ($emailSendStatusFlag == 1) {
						$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars, $lang_code);
					}
				}


				$arrResponse  = array('status' => 200, 'message' => 'Order split successfully.', 'split_order_id' => $split_order_one);
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Order can not split.');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
	}


	function confirmOrder()
	{

		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$current_tab = $_POST['current_tab'];

			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$shop_upload_path = 'shop' . $shop_id;

			$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');


			if ($current_tab == 'order') {
				$Parent_order_id = $OrderData->order_id;
			} else if ($current_tab == 'split-order') {
				$Parent_order_id = $OrderData->main_parent_id;
			}

			if ($order_id != $Parent_order_id) {
			}

			$NewOrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $Parent_order_id), '');

			$status_arr = array('0', '1', '2', '3');
			/*
			if(in_array($NewOrderData->status,$status_arr)){
				$split_update=array('status'=>4,'updated_at'=>time());
				$where_arr=array('order_id'=>$Parent_order_id);
				$this->WebshopOrdersModel->updateData('sales_order',$where_arr,$split_update);
			}
			*/

			$st_update = array('status' => 1, 'updated_at' => time());
			$where_arr = array('order_id' => $order_id);
			$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $st_update);

			$arrResponse  = array('status' => 200, 'message' => 'Order confirmed successfully.');
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function createShipmentPage()
	{
		$order_id = $this->uri->segment(4);
		if (isset($order_id) && $order_id > 0) {
			$data['PageTitle'] = 'Webshop - Orders';
			$data['side_menu'] = 'webshop';
			$data['current_tab'] = 'create-shipment';

			$shop_id		=	$this->session->userdata('ShopID');
			$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');

			/*
			$CheckShipmentExist=$this->WebshopOrdersModel->CheckShipmentExist($order_id);

			if(isset($CheckShipmentExist) && $CheckShipmentExist->id!='' ){
				redirect('webshop/orders/');
			}
			*/

			$data['ShipmentService'] = $ShipmentService = $this->WebshopOrdersModel->getMultiDataById('shipment_master', array('status' => 1), '', 'id', 'ASC');
			$data['use_advanced_warehouse'] = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');


			$data['OrderData'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');

			if ($OrderData->parent_id != ''  &&  $OrderData->parent_id > 0) {

				$data['ParentOrder'] = $ParentOrder = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->main_parent_id), '');
				$data['SplitOrderIds'] = $this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->main_parent_id);
				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 1), '');
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->main_parent_id), '');
			} else {

				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 1), '');
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->order_id), '');
			}

			// $data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);
			$data['currency_code'] = '';
			$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');
			if ($use_advanced_warehouse->value === 'yes') {
				$data['OrderItems'] = $this->WebshopOrdersModel->getOrderItems($order_id);
			} else {
				$data['OrderItems'] = $this->WebshopOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);
			}

			// delivery api intigration
			// $OwnShop=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'');
			// $data['webshop_order_shop_flag']='';
			// if(isset($OwnShop->shop_flag)){
			// 	$data['webshop_order_shop_flag']=$OwnShop->shop_flag;
			// }
			// end delivery api intigration

			$this->load->view('webshop/order/create_shipment', $data);
		} else {
			redirect('/webshop/orders');
		}
	}

	function createShipment()
	{
		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$box_weight = $_POST['box_weight'];
			$shipment_id = $_POST['shipment_id'];
			$additional_message = $_POST['additional_message'];



			if ($shipment_id == '') {

				$arrResponse  = array('status' => 400, 'message' => 'Please select shipment service.');
				echo json_encode($arrResponse);
				exit;
			} else {

				$fbc_user_id	=	$this->session->userdata('LoginID');
				$shop_id		=	$this->session->userdata('ShopID');

				$count = 1;
				$apiStatus = '';
				$order_shop_flag = '';
				$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
				if (isset($box_weight) && count($box_weight) > 0) {

					//  start code
					if (isset($OrderData->order_id)  && $OrderData->order_id > 0) {
						$webshop_order_id = $OrderData->order_id;
						//$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'');
						//$order_shop_flag=$FbcUser->shop_flag;
						if ($OrderData->main_parent_id > 0) {
							$order_main_parent_id = $OrderData->main_parent_id;
						} else {
							$order_main_parent_id = $webshop_order_id;
						}
						$paymentMethodShopflag2 = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $order_main_parent_id), 'payment_method');
						if (isset($paymentMethodShopflag2) && $paymentMethodShopflag2->payment_method == 'cod' && $shipment_id == 3) {
							// if(isset($FbcUser->shop_flag) && $FbcUser->shop_flag==4){
							// 	$apiResponse=$this->deliveryAPiProcessData($FbcUser,$OrderData,$shipment_id,$webshop_order_id,$box_weight,'COD'); //COD Prepaid
							// 	$apiResponseData=$apiResponse['apiResponseData'];
							// }
						} elseif (isset($paymentMethodShopflag2) && $paymentMethodShopflag2->payment_method != 'cod' && $shipment_id == 3) {
							// if(isset($FbcUser->shop_flag) && $FbcUser->shop_flag==4){
							// 	$apiResponse=$this->deliveryAPiProcessData($FbcUser,$OrderData,$shipment_id,$webshop_order_id,$box_weight,'Prepaid'); //COD Prepaid
							// 	$apiResponseData=$apiResponse['apiResponseData'];
							// }
						} //end else if
					}
					//end code

					// api status delivery
					if (isset($order_shop_flag) && $order_shop_flag == 4 && isset($apiResponseData->packages[0]->status) && $apiResponseData->packages[0]->status != 'Success' && $shipment_id == 3) {
						if (isset($apiResponseData->packages[0]->status) && isset($apiResponseData->packages[0]->remarks)) {
							$apiMsg = $apiResponseData->packages[0]->remarks;
						} else {
							$apiMsg = $apiResponseData->rmk;
						}
						$arrResponse  = array('status' => 400, 'message' => $apiMsg);
						echo json_encode($arrResponse);
						exit;
					}

					if (isset($apiResponse) && $apiResponse != '') {
						$tracking_id = $apiResponse['tracking_id']; //new
						$tracking_url = $apiResponse['tracking_url']; //new
						$apiReturnData = $apiResponse['apiReturnData']; //new
					} else {
						$tracking_id = ''; //new
						$tracking_url = ''; //new
						$apiReturnData = ''; //new
					}
					// end api status delivery

					$insertData = array(
						'order_id' => $order_id,
						'shipment_id' => $shipment_id,
						'message' => $additional_message,
						'created_by' => $fbc_user_id,
						'created_at' => time(),
						'ip' => $_SERVER['REMOTE_ADDR']
					);

					$order_shipment_id =		$this->WebshopOrdersModel->insertData('sales_order_shipment', $insertData);

					foreach ($box_weight as $box_val) {
						$insertData = array(
							'order_id' => $order_id,
							'order_shipment_id' => $order_shipment_id,
							'box_number' => $count,
							'weight' => $box_val,
							'tracking_id' => $tracking_id, //new
							'tracking_url' => $tracking_url, //new
							'api_response' => $apiReturnData, //new
							'created_by' => $fbc_user_id,
							'created_at' => time(),

						);

						$this->WebshopOrdersModel->insertData('sales_order_shipment_details', $insertData);

						$count++;
					}
				}

				if ($OrderData->parent_id > 0) {
					//$ParentOrder=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$OrderData->main_parent_id),'');
					$Parent_order_id = $OrderData->main_parent_id;
				} else {
					$Parent_order_id = $order_id;
				}

				/*----------------Decrement Order Qty---------------------------*/
				$this->WebshopOrdersModel->decrementOrderItemStock($order_id);

				/*---------------- Order Status update---------------------------*/

				$order_status = 4;

				if ($OrderData->parent_id != '') {
					$SplitOrderIds = $this->WebshopOrdersModel->getSplitChildOrderIds($Parent_order_id);
					$count_new = 0;
					if (isset($SplitOrderIds) && count($SplitOrderIds)) {
						foreach ($SplitOrderIds as $value) {
							$trk_order_id = $value->order_id;

							$Row = $this->WebshopOrdersModel->getSingleDataByID('sales_order_shipment_details', array('tracking_id <>' => '-', 'order_id' => $trk_order_id), 'id,tracking_id');
							if (isset($Row) && $Row->tracking_id != '-') {
								$count_new++;
							}
						}
					}
					if ($count_new > 0) {
						$order_status = 5;
					} else {
						$order_status = 4;
					}
				} else {
					$order_status = 4;
				}

				//echo $order_status.'================'.$Parent_order_id;exit;


				$os_update = array('status' => $order_status, 'updated_at' => time()); 		// Tracking Missing  OR Tracking Incomplete
				$where_arr = array('order_id' => $Parent_order_id);
				$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $os_update);

				$increment_id = $OrderData->increment_id;
				$owner_email = $OrderData->customer_email;
				$username = $OrderData->customer_firstname . ' ' . $OrderData->customer_lastname;

				(new SendShipmentConfirmationEmail())->execute($OrderData, $shop_id, $owner_email, $username, $increment_id, $additional_message);

				$arrResponse  = array('status' => 200, 'message' => 'Shipment created successfully.', 'order_id' => $Parent_order_id);
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
	}


	function saveTrackingId()
	{

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$tracking_id = $_POST['tracking_id'];


			if (isset($tracking_id) &&  count($tracking_id) > 0) {
				foreach ($tracking_id as $key => $value) {
					$value = isset($value) ? $value : '-';
					$tracking_url = $_POST['tracking_url'][$key];
					$_updatedata = array('tracking_id' => $value, 'tracking_url' => $tracking_url, 'updated_at' => time());
					$where_arr = array('id' => $key);
					$this->WebshopOrdersModel->updateData('sales_order_shipment_details', $where_arr, $_updatedata);
				}
			}

			$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');

			$DurationData = $this->WebshopOrdersModel->getSingleDataByID('custom_variables', array('identifier' => 'product_return_duration'), '');

			if (isset($DurationData) && $DurationData->value != '') {
				$product_return_duration = $DurationData->value;
			} else {
				$product_return_duration = 0;
			}



			if ($OrderData->is_split == 0) {
				/*
				$QtyScanItem=$this->WebshopOrdersModel->getQtyFullyScannedOrderItems($order_id);
				$AllItems=$this->WebshopOrdersModel->getOrderItems($order_id);
				if(count($QtyScanItem)==count($AllItems))
				{
					$odr_update=array('status'=>6,'updated_at'=>time());  	// Tracking Complete
					$where_arr=array('order_id'=>$order_id);
					$this->WebshopOrdersModel->updateData('sales_order',$where_arr,$odr_update);
				}else{

				}*/
				$op_ct = 0;
				$Result = $this->WebshopOrdersModel->getMultiDataById('sales_order_shipment_details', array('order_id' => $order_id), 'id,tracking_id');
				if (isset($Result) && Count($Result) > 0) {
					foreach ($Result as $Row) {
						if (isset($Row) && ($Row->tracking_id == '-' || $Row->tracking_id == '')) {
							$op_ct++;
						}
					}
				}


				if ($op_ct > 0) {
					$order_status = 5;
				} else {
					$order_status = 6;
				}

				$odr_update = array('status' => $order_status, 'updated_at' => time());  	// Tracking Complete
				$where_arr = array('order_id' => $order_id);
				$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $odr_update);



				if ($order_status == 6) {

					/*-------------update webshop b2b related order to  tracking complete------------------*/

					$odr_update = array('tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Complete Date
					$where_arr = array('order_id' => $order_id);
					$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $odr_update);


					$increment_id = $OrderData->increment_id;



					$site_url = str_replace('admin', '', base_url());
					$order_id = base64_encode($OrderData->order_id);
					$encoded_oid = urlencode($order_id);
					$burl = base_url();
					if ($OrderData->checkout_method == 'guest') {
						// $website_url = getWebsiteUrl($shop_id,$burl);
						// $return_link = $website_url.'/guest-order/detail/'.$encoded_oid;
					} else {
						// $website_url = getWebsiteUrl($shop_id,$burl);
						// $return_link= $website_url.'/customer/my-orders/';
					}
					/*----------------Send Email to shop owner--------------------*/
					// $shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
					// $webshop_details=$this->CommonModel->get_webshop_details($shop_id);
					// $owner_email=$shop_owner->email;
					// $templateId ='fbcuser-order-tracking-completed';
					// $to = $OrderData->customer_email;
					// $shop_name=$shop_owner->org_shop_name;
					// $username = $OrderData->customer_firstname.' '.$OrderData->customer_lastname;
					// $site_logo = '';
					// if(isset($webshop_details)){
					//  $shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
					// }

					// $shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
					// $site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">
					// 	<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
					// </a>';
					// $TempVars = array("##OWNER##" ,"##ORDERID##","##RETURNDURATION##","##RETURNLINK##",'##WEBSHOPNAME##');
					// $DynamicVars   = array($username,$increment_id,$product_return_duration,$return_link,$shop_name);
					// $CommonVars=array($site_logo, $shop_name);
					if ($OrderData->main_parent_id > 0) {
						$ParentOrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->main_parent_id), '');
						$is_default_language = $ParentOrderData->is_default_language;
						$language_code = $ParentOrderData->language_code;
					} else {
						$is_default_language = $OrderData->is_default_language;
						$language_code = $OrderData->language_code;
					}
					if ($is_default_language != 1 && $language_code != '') {
						$lang_code = $language_code;
					} else {
						$lang_code = '';
					}

					if (isset($templateId)) {
						$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
						if ($emailSendStatusFlag == 1) {
							$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars, $lang_code);
						}
					}



					//$this->updateWebshopB2BOrderStatus($order_id);
					// invoice generate
					// if(isset($this->CommonModel->page_access()->acc_inv_flag) && $this->CommonModel->page_access()->acc_inv_flag==1 && $this->CommonModel->page_access()->semi_invoice==0){
					// 	$this->invoiceGenerate($OrderData->order_id);
					// }
				}
			} else {

				$SplitOrderIds = $this->WebshopOrdersModel->getSplitChildOrderIds($order_id);
				$count = 0;
				$tracking_not_gen = false;
				if (isset($SplitOrderIds) && count($SplitOrderIds)) {
					foreach ($SplitOrderIds as $value) {
						if ($value->status != 3) {
							$trk_order_id = $value->order_id;
							$Result = $this->WebshopOrdersModel->getMultiDataById('sales_order_shipment_details', array('order_id' => $trk_order_id), 'id,tracking_id');
							if (isset($Result) && count($Result) > 0) {
								foreach ($Result as $Row) {
									if (isset($Row) && ($Row->tracking_id == '-' || $Row->tracking_id == '')) {
										$count++;
									}
								}
							} else {
								$tracking_not_gen = true;
							}
						}
					}
				}

				if ($count > 0 || $tracking_not_gen == true) {
					$order_status = 5;
				} else {
					$order_status = 6;
				}

				$odr_update = array('status' => $order_status, 'updated_at' => time());  	// Tracking Complete
				$where_arr = array('order_id' => $order_id);
				$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $odr_update);

				if ($order_status == 6) {

					$odr_update = array('tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Complete Date
					$where_arr = array('order_id' => $order_id);
					$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $odr_update);

					/*-------------update webshop b2b related order to  tracking complete------------------*/

					$increment_id = $OrderData->increment_id;


					$site_url = str_replace('admin', '', base_url());
					$order_id = base64_encode($OrderData->order_id);
					$encoded_oid = urlencode($order_id);
					$burl = base_url();
					if ($OrderData->checkout_method == 'guest') {
						$website_url = getWebsiteUrl($shop_id, $burl);
						$return_link = $website_url . '/guest-order/detail/' . $encoded_oid;
					} else {
						$website_url = getWebsiteUrl($shop_id, $burl);
						$return_link = $website_url . '/customer/my-orders/';
					}
					/*----------------Send Email to shop owner--------------------*/
					$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
					$webshop_details = $this->CommonModel->get_webshop_details($shop_id);
					$owner_email = $shop_owner->email;
					$templateId = 'fbcuser-order-tracking-completed';
					$to = $OrderData->customer_email;
					$shop_name = $shop_owner->org_shop_name;
					$username = $OrderData->customer_firstname . ' ' . $OrderData->customer_lastname;
					$site_logo = '';
					if (isset($webshop_details)) {
						$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
					}

					$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
					$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
						<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
					</a>';
					$TempVars = array("##OWNER##", "##ORDERID##", "##RETURNDURATION##", "##RETURNLINK##", '##WEBSHOPNAME##');
					$DynamicVars   = array($username, $increment_id, $product_return_duration, $return_link, $shop_name);
					$CommonVars = array($site_logo, $shop_name);
					if ($OrderData->main_parent_id > 0) {
						$ParentOrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->main_parent_id), '');
						$is_default_language = $ParentOrderData->is_default_language;
						$language_code = $ParentOrderData->language_code;
					} else {
						$is_default_language = $OrderData->is_default_language;
						$language_code = $OrderData->language_code;
					}
					if ($is_default_language != 1 && $language_code != '') {
						$lang_code = $language_code;
					} else {
						$lang_code = '';
					}

					if (isset($templateId)) {
						$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
						if ($emailSendStatusFlag == 1) {
							$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars, $lang_code);
						}
					}


					$this->updateWebshopB2BOrderStatus($order_id);

					// invoice generate webshop b2b order related

					// invoice generate
					if (isset($this->CommonModel->page_access()->acc_inv_flag) && $this->CommonModel->page_access()->acc_inv_flag == 1 && $this->CommonModel->page_access()->semi_invoice == 0) {
						$this->invoiceGenerate($OrderData->order_id);
					}
				}
			}

			$arrResponse  = array('status' => 200, 'message' => 'Tracking data saved successfully.');
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
	}

	public function webshopPrintdetails()
	{
		$data['PageTitle'] = 'Webshop - Print Details';
		$order_id = $this->uri->segment(4);
		if (isset($order_id) && $order_id > 0) {

			$data['OrderData'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');

			if (empty($OrderData)) {
				redirect('/webshop/orders');
			}

			$data['order_id'] = $order_id;

			$data['OrderItems'] = $OrderItems = $this->WebshopOrdersModel->getOrderItems($order_id);

			$show_cust_addr_check = $this->WebshopOrdersModel->getSingleDataByID('custom_variables', array('identifier' => 'pickinglist_show_cust_addr'), '');
			// print_r($show_cust_addr_check->value);die();

			if (isset($show_cust_addr_check) && $show_cust_addr_check->value == 'yes') {
				if ($OrderData->parent_id > 0) {
					$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->parent_id, 'address_type' => 2), '');
					$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->parent_id, 'address_type' => 1), '');
				} else {
					$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $order_id, 'address_type' => 2), '');
					$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $order_id, 'address_type' => 1), '');
				}

				$data['FormattedAddress_ship'] = $this->WebshopOrdersModel->getFormattedAddress($ShippingAddress);
				$data['FormattedAddress_bill'] = $this->WebshopOrdersModel->getFormattedAddress($BillingAddress);
			}

			$this->load->view('webshop/order/webshopOrderDetailPrint', $data);
		} else {
			redirect('/webshop/orders');
		}
	}

	function shippedorderprint()
	{
		$current_tab = $this->uri->segment(2);
		$order_id = $this->uri->segment(4);
		if (isset($order_id) && $order_id > 0) {
			$data['PageTitle'] = ' Shipped Order Print';
			$data['side_menu'] = 'webshop';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';

			$shop_id = $this->session->userdata('ShopID');

			//echo $this->B2BOrdersModel->generate_new_transaction_id().'=====';
			$data['OrderData'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');

			if (empty($OrderData)) {
				redirect('/webshop/orders');
			}

			//$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);
			$data['currency_code'] = 'RS';

			if ($current_tab == 'shipped-order') {
				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 1), '');
				$data['SplitOrderIds'] = $this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->order_id);
				$data['ShippedItem'] = $ShippedItem = $this->WebshopOrdersModel->getShippedOrderItems($OrderData->order_id, $OrderData->is_split);
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->order_id), '');



				$this->load->view('webshop/order/shipped-order-print', $data);
			} else {
				redirect('/webshop/orders');
			}
		} else {
			redirect('/webshop/orders');
		}
	}

	function printshipmentlabel()
	{

		$order_id = $_POST['order_id'];
		if (isset($order_id) && $order_id > 0) {
			$_data['temp_box_weight'] = $box_weight = $_POST['box_weight'];
			$_data['temp_order_id'] = $order_id = $_POST['order_id'];
			$_data['temp_additional_message'] = $additional_message = $_POST['additional_message'];

			$this->session->set_userdata($_data);

			$arrResponse  = array('status' => 200, 'message' => '', 'order_id' => $order_id);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong1082');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function orderprintlabel()
	{
		$order_id = $this->uri->segment(4);

		if (isset($order_id) && $order_id > 0) {
			$shop_id		=	$this->session->userdata('ShopID');
			$data['OrderData'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			if (empty($OrderData)) {
				redirect('/webshop/orders');
			}

			$data['temp_box_weight'] = $this->session->userdata('temp_box_weight');
			$data['temp_order_id'] = $this->session->userdata('temp_order_id');
			$data['temp_additional_message'] = $this->session->userdata('temp_additional_message');

			$data['OrderItems'] = $OrderItems = $this->WebshopOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);


			if ($OrderData->parent_id != ''  &&  $OrderData->parent_id > 0) {

				$data['ParentOrder'] = $ParentOrder = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->main_parent_id), '');
				$data['SplitOrderIds'] = $this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->main_parent_id);
				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 1), '');
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->main_parent_id), '');
			} else {

				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 1), '');
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->order_id), '');
			}


			$this->load->view('webshop/order/order-print-label', $data);
		} else {
			redirect('/webshop/orders');
		}
	}


	function supplierb2borderdetail()
	{
		$current_tab = $this->uri->segment(2);
		$b2b_shop_id = $this->uri->segment(4);
		$b2b_order_id = $this->uri->segment(5);
		if (isset($b2b_order_id) && $b2b_order_id > 0) {
			$data['PageTitle'] = 'Webshop - B2B - Orders';
			$data['side_menu'] = 'webshop';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';

			$shop_id		=	$this->session->userdata('ShopID');

			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $b2b_shop_id), '');
			$b2b_fbc_user_id = $FbcUser->fbc_user_id;

			$args['shop_id']		=	$b2b_shop_id;
			$args['fbc_user_id']	=	$b2b_fbc_user_id;

			$this->load->model('ShopProductModel');
			$this->ShopProductModel->init($args);

			$data['b2b_shop_id'] = $b2b_shop_id;
			$data['currency_code_seller'] = $this->CommonModel->getShopCurrency($b2b_shop_id);


			//echo $this->WebshopOrdersModel->generate_new_transaction_id().'=====';
			$data['B2BOrderData'] = $B2BOrderData = $this->ShopProductModel->getSingleDataByID('b2b_orders', array('order_id' => $b2b_order_id), '');

			if (empty($B2BOrderData)) {
				redirect('/webshop/orders');
			}

			$data['webshop_order_id'] = $webshop_order_id = $B2BOrderData->webshop_order_id;


			$b2b_orders = array();

			$b2bShops = $this->WebshopOrdersModel->getWebshopB2BShops($webshop_order_id);


			if (isset($b2bShops) && count($b2bShops) > 0) {
				foreach ($b2bShops as $key => $shop) {
					$b2b_shop_id = $shop->shop_id;
					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $b2b_shop_id), '');
					$b2b_fbc_user_id = $FbcUser->fbc_user_id;

					$args['shop_id']	=	$b2b_shop_id;
					$args['fbc_user_id']	=	$b2b_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);

					$b2b_order_list = $this->ShopProductModel->getMultiDataById('b2b_orders', array('webshop_order_id' => $webshop_order_id, 'shop_id' => $shop_id), 'order_id,increment_id', 'order_id', 'ASC');
					if (isset($b2b_order_list) && count($b2b_order_list) > 0) {
						foreach ($b2b_order_list as $value) {
							$b2b_orders[] = $value->increment_id;
						}
					}
				}
			}

			if (isset($b2b_orders) && count($b2b_orders) > 0) {
				$b2b_orders = implode(', ', $b2b_orders);
			}
			$data['b2b_orders'] = $b2b_orders;

			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);

			if ($B2BOrderData->parent_id != '') {
				$data['ParentOrder'] = $ParentOrder = $this->ShopProductModel->getSingleDataByID('b2b_orders', array('order_id' => $B2BOrderData->main_parent_id), '');
				$data['SplitOrderIds'] = $this->ShopProductModel->getSplitChildOrderIds($B2BOrderData->main_parent_id);
			}

			$data['ShippedItem'] = $ShippedItem = $this->ShopProductModel->getOrderItemsForWebShopB2B($B2BOrderData->order_id, $B2BOrderData->is_split);


			$data['OrderData'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '');


			$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 2), '');
			$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 1), '');
			$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->order_id), '');

			$this->load->view('webshop/order/webshop-b2b-order-detail', $data);
		} else {
			redirect('/webshop/orders');
		}
	}

	function updateWebshopB2BOrderStatus($order_id)
	{


		if (isset($order_id) && $order_id > 0) {
			$b2bShops = $this->WebshopOrdersModel->getWebshopB2BShops($order_id);

			$any_order_pending = false;

			if (isset($b2bShops) && count($b2bShops) > 0) {
				foreach ($b2bShops as $key => $shop) {
					$b2b_shop_id = $shop->shop_id;
					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $b2b_shop_id), '');
					$b2b_fbc_user_id = $FbcUser->fbc_user_id;

					$args['shop_id']	=	$b2b_shop_id;
					$args['fbc_user_id']	=	$b2b_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);

					$shop_id		=	$this->session->userdata('ShopID'); //new
					$b2b_order_list = $this->ShopProductModel->getMultiDataById('b2b_orders', array('webshop_order_id' => $order_id, 'shop_id' => $shop_id), 'order_id,increment_id,is_split,status', 'order_id', 'ASC');
					if (isset($b2b_order_list) && count($b2b_order_list) > 0) {
						foreach ($b2b_order_list as $value) {

							$b2b_order_id = $value->order_id;

							/*------------------------------------------------------------*/

							if ($value->is_split == 0) {
								/*
										$QtyScanItem=$this->WebshopOrdersModel->getQtyFullyScannedOrderItems($order_id);
										$AllItems=$this->WebshopOrdersModel->getOrderItems($order_id);
										if(count($QtyScanItem)==count($AllItems))
										{
											$odr_update=array('status'=>6,'updated_at'=>time());  	// Tracking Complete
											$where_arr=array('order_id'=>$order_id);
											$this->WebshopOrdersModel->updateData('sales_order',$where_arr,$odr_update);
										}else{

										}*/
								$op_ct = 0;
								$Result = $this->ShopProductModel->getMultiDataById('b2b_order_shipment_details', array('order_id' => $b2b_order_id), 'id,tracking_id');
								if (isset($Result) && Count($Result) > 0) {
									foreach ($Result as $Row) {
										if (isset($Row) && ($Row->tracking_id == '-' || $Row->tracking_id == '')) {
											$op_ct++;
										}
									}
								}


								if ($op_ct > 0 || count($Result) == 0) {
									$any_order_pending = true;
									continue;
								} else {
									$order_status = 6;

									$odr_update = array('status' => $order_status, 'tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Complete
									$where_arr = array('order_id' => $b2b_order_id);
									$this->ShopProductModel->updateData('b2b_orders', $where_arr, $odr_update);
								}
							} else {

								$SplitOrderIds = $this->ShopProductModel->getSplitChildOrderIds($b2b_order_id);
								$b2b_count = 0;
								if (isset($SplitOrderIds) && count($SplitOrderIds)) {
									foreach ($SplitOrderIds as $value) {
										$status = $value->status;
										if ($value->status == 6) {
											$b2b_count++;
										}
									}
								}

								if ($b2b_count == count($SplitOrderIds)) {
									$order_status = 6;
									$odr_update = array('status' => $order_status, 'tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Complete
									$where_arr = array('order_id' => $b2b_order_id);
									$this->ShopProductModel->updateData('b2b_orders', $where_arr, $odr_update);
								} else {
									$any_order_pending = true;
									continue;
								}
							}


							/*------------------------------------------------------------*/
						}
					}
				}
			}


			if ($any_order_pending == true) {
				$odr_update = array('status' => 5, 'tracking_complete_date' => '', 'updated_at' => time());  	// Tracking Incomplete - One one the b2b order is not completed
				$where_arr = array('order_id' => $order_id);
				$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $odr_update);
			}
		}
	}

	function openScanQtyPopup()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id'])) {

			$data['order_id'] = $_POST['order_id'];
			$data['item_id'] = $item_id = $_POST['item_id'];

			$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order_items', array('item_id' => $item_id), '');

			$View = $this->load->view('webshop/order/oi-scan-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function scanitemwithqty()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id']) && isset($_POST['qty'])) {
			$order_id = $_POST['order_id'];
			$item_id = $_POST['item_id'];
			$current_tab = $_POST['current_tab'];
			$qty = $_POST['qty'];
			$ItemExist = $this->WebshopOrdersModel->checkOrderItemsExistByItemId($order_id, $item_id);
			if (isset($ItemExist) && $ItemExist->item_id != '') {

				$item_id = $ItemExist->item_id;
				$qty_ordered = $ItemExist->qty_ordered;
				$old_qty_scanned = $ItemExist->qty_scanned;

				$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,is_split');

				if ($current_tab == 'split-order') {
					$main_oi_qty = $this->WebshopOrdersModel->getMainOrderItemQty($OrderData->main_parent_id, $ItemExist->product_id);
					$main_qty_ordered = $main_oi_qty;
				} else {

					$main_qty_ordered = $ItemExist->qty_ordered;
				}


				if (($current_tab == 'order') && ($old_qty_scanned == $qty_ordered)) {
					$arrResponse  = array('status' => 400, 'message' => 'Item already scanned all quantity.');
					echo json_encode($arrResponse);
					exit;
				}
				/*
				else if(($current_tab=='split-order') && ($old_qty_scanned==$qty_ordered)){
					$arrResponse  = array('status' =>400 ,'message'=>'Item already scanned all quantity.');
					echo json_encode($arrResponse);exit;

				}
				*/ else {

					if ($old_qty_scanned < $main_qty_ordered) {
						$this->WebshopOrdersModel->incrementOrderItemQtyScannedByQty($item_id, $qty);  //increament qty_scanned
					}

					$item_class = $this->WebshopOrdersModel->getOrderItemRowClass($item_id);


					$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,is_split');
					if ($OrderData->is_split == 0) {
						$QtyScanItem = $this->WebshopOrdersModel->getQtyFullyScannedOrderItems($order_id);
						$AllItems = $this->WebshopOrdersModel->getOrderItems($order_id);
						if (count($QtyScanItem) == count($AllItems)) {
							$odr_update = array('system_generated_split_order' => 0, 'updated_at' => time());
							$where_arr = array('order_id' => $order_id);
							$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $odr_update);
						}
					}


					$arrResponse  = array('status' => 200, 'message' => 'Item scanned successfully.', 'item_id' => $item_id, 'item_class' => $item_class);
					echo json_encode($arrResponse);
					exit;
				}
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Item does not belongs to this order!');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}


	function sendTrackingEmail()
	{

		if (!isset($_POST)) {
			$arrResponse = array('status' => 400, 'message' => 'Error While Sending Mail');
			echo json_encode($arrResponse);
			exit;
		}

		$order_id = $_POST['order_id'];
		$tracking_id = $_POST['tracking_id'];
		$shop_id = $this->session->userdata('ShopID');

		(new SendTrackingEmail())->execute($shop_id, $order_id, $tracking_id);

		$arrResponse = array('status' => 200, 'message' => 'Tracking Email Sent Successfully');
		echo json_encode($arrResponse);
		exit;
	}

	// invoice
	function invoiceGenerate($order_id)
	{
		$orderData = $this->WebshopOrdersModel->get_webshop_invoicing_data($order_id);
		$orderUserType = $orderData->checkout_method; //guest,register,login

		$ShippingAddress = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $orderData->order_id, 'address_type' => 2), '');
		$BillingAddress = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $orderData->order_id, 'address_type' => 1), '');

		$customVariables_invoice_prefix = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_prefix'), 'value');
		$customVariables_invoice_no = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_next_no'), 'value');

		if ($customVariables_invoice_prefix->value && $customVariables_invoice_no->value) {
			$invoiceNo = $customVariables_invoice_no->value;
			$invoice_next_no = $invoiceNo + 1;
			$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
		} elseif ($customVariables_invoice_prefix->value) {
			// $invoiceNo='2';
			$invoiceNo = '1';
			$invoice_next_no = $invoiceNo + 1;
			$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
			# code...
		} elseif ($customVariables_invoice_no->value) {
			$invoiceNo = $customVariables_invoice_no->value;
			$invoice_next_no = $invoiceNo + 1;
			$invoice_no = 'INV' . $invoiceNo;
		}
		// custom_variable table data updated
		$invoice_no = $invoice_no;
		$invoice_next_no = $invoice_next_no;

		// invoice table insert
		$invoice_order_type = '1'; //1-Webshop;  2-B2Webshop;
		$customer_name = $orderData->customer_name;
		$customer_first_name = $orderData->customer_firstname;
		$customer_last_name = $orderData->customer_lastname;
		$customer_id = $orderData->customer_id;
		$customer_is_guest = $orderData->customer_is_guest;
		$invoice_self = $orderData->invoice_self;
		// $shop_webshop_name=$orderData->org_shop_name;
		$shop_webshop_name = '';
		$shop_gst_no = ''; //$orderData->gst_no
		$shop_company_name = ''; //$orderData->company_name
		//echo $customer_id;
		// $orderData->invoice_self=1;

		//$customer_email=$orderData->customer_email;
		$invoice_subtotal = '';
		$invoice_tax = '';
		$invoice_grand_total = '';
		// bill invoice
		$bill_customer_first_name = $BillingAddress->first_name;
		$bill_customer_last_name = $BillingAddress->last_name;
		$bill_customer_id = ''; // new
		$bill_customer_email = ''; // new
		// $bill_customer_email=$orderData->email;
		$billing_address_line1 = $BillingAddress->address_line1; //$orderData->bill_address_line1
		$billing_address_line2 = $BillingAddress->address_line2; //$orderData->bill_address_line2
		$billing_city = $BillingAddress->city; //$orderData->bill_city
		$billing_state = $BillingAddress->state; //$orderData->bill_state
		$billing_country = $BillingAddress->country; //$orderData->bill_country
		$billing_pincode = $BillingAddress->pincode; //$orderData->bill_pincode

		$ship_address_line1 = $ShippingAddress->address_line1; //$orderData->ship_address_line1
		$ship_address_line2 = $ShippingAddress->address_line2; //$orderData->ship_address_line2
		$ship_city = $ShippingAddress->city; //$orderData->ship_city
		$ship_state = $ShippingAddress->state; //$orderData->ship_state
		$ship_country = $ShippingAddress->country; //$orderData->ship_country
		$ship_pincode = $ShippingAddress->pincode; //$orderData->ship_pincode
		$invoice_date = time();

		//get data by user id
		$invoice_due_date = '';
		//$orderUserType; exit();
		// new added data
		$invoice_type = 0;
		$invoice_generate = 0;
		$emailSend = 0;
		// customer if guest
		$customer_company = '';
		$customer_gst_no = '';
		$shipping_charges = $orderData->shipping_amount;
		$voucher_amount = $orderData->voucher_amount;
		$payment_charges = $orderData->payment_final_charge;
		//new add invoice
		$customer_email = $orderData->customer_email;
		$bill_customer_email = $orderData->customer_email;
		$bill_customer_id = $orderData->customer_id;
		// $orderData->invoice_self=0;
		if ($orderData->invoice_self == 1) {
			if ($orderUserType == 'guest') {
				$payment_term = '';
				$invoice_due_date = $invoice_date;
				$emailSend = 1;
				$invoice_generate = 1;
			} else {
				$customers_invoiceData = $this->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_id), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				if (isset($customers_invoiceData) && !empty($customers_invoiceData)) {
					$invoice_type = $customers_invoiceData->invoice_type;
					$payment_term = $customers_invoiceData->payment_term;
					$invoice_to_type = $customers_invoiceData->invoice_to_type;
					if ($customers_invoiceData->invoice_type == 1) {
						$invoice_generate = 1;
						$emailSend = 1;
					}
				} else {
					$invoice_generate = 1;
					$emailSend = 1;
					$payment_term = '';
					$invoice_due_date = $invoice_date;
					// alternate email id

				}
				if ($invoice_date && $payment_term > 0) {
					$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
					$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
					$invoice_due_date = date(strtotime($due_date));
				} else {
					$payment_term = '';
					$invoice_due_date = $invoice_date;
				}

				// bill customer data
				$bill_customer_company_name_gst = $this->CommonModel->getSingleShopDataByID('customers', array('id' => $bill_customer_id), 'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
				if (isset($bill_customer_company_name_gst)) {
					$customer_name = $bill_customer_company_name_gst->customer_name;
					$customer_company = $bill_customer_company_name_gst->company_name;
					$customer_gst_no = $bill_customer_company_name_gst->gst_no;
				}
				//end new
			}
		} else {
			// customer type
			if ($orderUserType == 'guest') {
				$invoice_generate = 1;
				$payment_term = '';
				$invoice_due_date = $invoice_date;
				$customVariables_alternative_email_id = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
				if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
					$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
					$customer_email_data_shop = $this->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
					$bill_customer_email = $customer_email_data_shop->email_id;
					$bill_customer_id = $customer_email_alternate_shop;
					// $emailSend=1;
				}
			} else {
				$customers_invoiceData = $this->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_id), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				// print_r($customers_invoiceData);exit();
				// if(isset($customers_invoiceData) && $customers_invoiceData->invoice_type==1){
				if (isset($customers_invoiceData) && !empty($customers_invoiceData)) {
					$invoice_type = $customers_invoiceData->invoice_type;
					$payment_term = $customers_invoiceData->payment_term;
					$customer_email_alternate = '';
					$invoice_to_type = $customers_invoiceData->invoice_to_type;
					if ($invoice_to_type != 0) {
						$customer_email_alternate = $customers_invoiceData->alternative_email_id;
					}
					if ($customers_invoiceData->invoice_type == 1) {
						$invoice_generate = 1;
						$emailSend = 1;
					}
				} else {
					$invoice_generate = 1;
					$payment_term = 0;
					// alternate
					$customVariables_alternative_email_id = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
					if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
						$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
						$customer_email_data_shop = $this->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
						$bill_customer_email = $customer_email_data_shop->email_id;
						$bill_customer_id = $customer_email_alternate_shop;
						// $emailSend=1;
					}
					// end alternate
				}

				if ($invoice_date && $payment_term > 0) {
					$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
					$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
					$invoice_due_date = date(strtotime($due_date));
				} else {
					$payment_term = '';
					$invoice_due_date = $invoice_date;
				}
				if (isset($invoice_to_type) && $invoice_to_type == 1) { //invoice type check
					if ($customer_email_alternate) {
						//$customer_email_alternate=0;
						$customer_email_data = $this->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate), 'email_id');
						if (isset($customer_email_data) && !empty($customer_email_data)) {
							// new add
							$bill_customer_email = $customer_email_data->email_id;
							$bill_customer_id = $customer_email_alternate;
							$customers_invoiceData_alt = $this->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
							if (isset($customers_invoiceData_alt) && !empty($customers_invoiceData_alt)) {
								$invoice_type = $customers_invoiceData_alt->invoice_type;
								$payment_term = $customers_invoiceData_alt->payment_term;
								$invoice_to_type = $customers_invoiceData_alt->invoice_to_type;
								if ($customers_invoiceData_alt->invoice_type == 1) {
									$invoice_generate = 1;
									$emailSend = 1;
								}
							} else {
								$invoice_generate = 1;
								$payment_term = 0;
								$customer_email_alternate = '';
							}


							// $emailSend=1;
						} else {
							$customVariables_alternative_email_id = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
							if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
								$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
								$customer_email_data_shop = $this->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
								$bill_customer_email = $customer_email_data_shop->email_id;
								$bill_customer_id = $customer_email_alternate_shop;
								// $emailSend=1;
								$customers_invoiceData_alt_cusVar = $this->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
								if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
									$invoice_type = $customers_invoiceData_alt_cusVar->invoice_type;
									$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
									$invoice_to_type = $customers_invoiceData_alt_cusVar->invoice_to_type;
									if ($customers_invoiceData_alt_cusVar->invoice_type == 1) {
										$invoice_generate = 1;
										$emailSend = 1;
									}
								} else {
									$invoice_generate = 1;
									$payment_term = 0;
									$customer_email_alternate = '';
								}
							}
						}
					} else {
						$customVariables_alternative_email_id = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
						if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
							$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
							$customer_email_data_shop = $this->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
							$bill_customer_email = $customer_email_data_shop->email_id;
							$bill_customer_id = $customer_email_alternate_shop;
							// $emailSend=1;
							$customers_invoiceData_alt_cusVar = $this->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
							if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
								$invoice_type = $customers_invoiceData_alt_cusVar->invoice_type;
								$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
								$invoice_to_type = $customers_invoiceData_alt_cusVar->invoice_to_type;
								if ($customers_invoiceData_alt_cusVar->invoice_type == 1) {
									$invoice_generate = 1;
									$emailSend = 1;
								}
							} else {
								$invoice_generate = 1;
								$payment_term = 0;
								$customer_email_alternate = '';
							}
						}
					}

					if ($bill_customer_id) {
						$Default_BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('customers_address', array('customer_id' => $bill_customer_id, 'is_default' => 1), '');
						if (isset($Default_BillingAddress)) {
							$bill_customer_first_name = $Default_BillingAddress->first_name;
							$bill_customer_last_name = $Default_BillingAddress->last_name;
							// $bill_customer_email=$orderData->email;
							$billing_address_line1 = $Default_BillingAddress->address_line1; //$orderData->bill_address_line1
							$billing_address_line2 = $Default_BillingAddress->address_line2; //$orderData->bill_address_line2
							$billing_city = $Default_BillingAddress->city; //$orderData->bill_city
							$billing_state = $Default_BillingAddress->state; //$orderData->bill_state
							$billing_country = $Default_BillingAddress->country; //$orderData->bill_country
							$billing_pincode = $Default_BillingAddress->pincode; //$orderData->bill_pincode
						} else {
							$bill_BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('customers_address', array('customer_id' => $bill_customer_id, ''), '');
							if (isset($bill_BillingAddress)) {
								$bill_customer_first_name = $bill_BillingAddress->first_name;
								$bill_customer_last_name = $bill_BillingAddress->last_name;
								// $bill_customer_email=$orderData->email;
								$billing_address_line1 = $bill_BillingAddress->address_line1; //$orderData->bill_address_line1
								$billing_address_line2 = $bill_BillingAddress->address_line2; //$orderData->bill_address_line2
								$billing_city = $bill_BillingAddress->city; //$orderData->bill_city
								$billing_state = $bill_BillingAddress->state; //$orderData->bill_state
								$billing_country = $bill_BillingAddress->country; //$orderData->bill_country
								$billing_pincode = $bill_BillingAddress->pincode; //$orderData->bill_pincode
							}
						}
					}
				} elseif (isset($customer_email_alternate_shop) & !empty($customer_email_alternate_shop)) { // new add 16-08-2021
					$customers_invoiceData_alt_cusVar = $this->CommonModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
					if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
						$invoice_type = $customers_invoiceData_alt_cusVar->invoice_type;
						$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
						$invoice_to_type = $customers_invoiceData_alt_cusVar->invoice_to_type;
						if ($customers_invoiceData_alt_cusVar->invoice_type == 1) {
							$invoice_generate = 1;
							$emailSend = 1;
						}
					} else {
						/*$invoice_generate=1;
						$payment_term=0;
						$customer_email_alternate='';*/
					}
				} //end invoice type check //end invoice type check //end invoice type check

				// bill customer data
				$bill_customer_company_name_gst = $this->CommonModel->getSingleShopDataByID('customers', array('id' => $bill_customer_id), 'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
				if (isset($bill_customer_company_name_gst)) {
					$customer_name = $bill_customer_company_name_gst->customer_name;
					$customer_company = $bill_customer_company_name_gst->company_name;
					$customer_gst_no = $bill_customer_company_name_gst->gst_no;
				}
			}
		} // end invoice_self

		$invoice_term = $payment_term;
		// insert invoicing data
		$insertinvoicingdataitem = array(
			'invoice_no' => $invoice_no,
			'customer_first_name' => $customer_first_name,
			'customer_last_name' => $customer_last_name,
			'customer_id' => $customer_id,
			'customer_email' => $customer_email,
			// 'shop_id'=>'',
			// 'shop_webshop_name'=>$shop_webshop_name,
			// 'shop_company_name'=>$shop_company_name,
			'shop_gst_no' => $shop_gst_no,
			'bill_customer_first_name' => $customer_name,
			'bill_customer_company_name' => $customer_company,
			'bill_customer_gst_no' => $customer_gst_no,
			// 'bill_customer_last_name'=>$customer_name,
			'bill_customer_id' => $bill_customer_id, // new $bill_customer_email$bill_customer_id
			'bill_customer_email' => $bill_customer_email, // new
			'invoice_order_nos' => $order_id,
			'invoice_order_type' => $invoice_order_type,
			'invoice_subtotal' => $invoice_subtotal,
			'invoice_tax' => $invoice_tax,
			'invoice_grand_total' => $invoice_grand_total,
			'billing_address_line1' => $billing_address_line1,
			'billing_address_line2' => $billing_address_line2,
			'billing_city' => $billing_city,
			'billing_state' => $billing_state,
			'billing_country' => $billing_country,
			'billing_pincode' => $billing_pincode,
			'ship_address_line1' => $ship_address_line1,
			'ship_address_line2' => $ship_address_line2,
			'ship_city' => $ship_city,
			'ship_state' => $ship_state,
			'ship_country' => $ship_country,
			'ship_pincode' => $ship_pincode,
			'invoice_date' => $invoice_date,
			'invoice_due_date' => $invoice_due_date,
			'invoice_term' => $invoice_term,
			'payment_charges' => $payment_charges,
			'voucher_amount' => $voucher_amount,
			'shipping_charges' => $shipping_charges,
			// 'created_by'=>$fbc_user_id,
			'created_at' => time(),
			'ip' => $_SERVER['REMOTE_ADDR']
		);
		if ($invoice_generate == 1) { //invoice generate 1-yes, 0-no
			$invoicing_one = $this->WebshopOrdersModel->insertData('invoicing', $insertinvoicingdataitem);
			//}
			//$invoicing_one='35';
			// print_r($invoicing_one);exit();
			if ($invoicing_one) {
				//update custom_variable
				$invoice_no_update = array('value' => $invoice_next_no);
				$where_invoice_arr = array('identifier' => 'invoice_next_no');

				$this->WebshopOrdersModel->updateData('custom_variables', $where_invoice_arr, $invoice_no_update);
				// sales order updated
				$invoice_sales_order = array('invoice_id' => $invoicing_one, 'invoice_date' => $invoice_date, 'invoice_flag' => 1);
				$where_sales_order_arr = array('order_id' => $order_id);
				$this->WebshopOrdersModel->updateData('sales_order', $where_sales_order_arr, $invoice_sales_order);
				// send invoice email and save invoice pdf
				$pdfGeneratePdfName = $this->generatePdfWebshop($invoicing_one); // save pdf
				//send email with attachment
				if ($pdfGeneratePdfName) {
					//update invoicing
					$invoiceFileName = array('invoice_file' => $pdfGeneratePdfName);
					$where_invoice_filename_arr = array('id' => $invoicing_one);
					$this->WebshopOrdersModel->updateData('invoicing', $where_invoice_filename_arr, $invoiceFileName);

					// sent email
					/*----------------Send Email to invoice with attchmnet--------------------*/
					if ($emailSend == 1) { // email check 1-send 0-not send
						// invoice send date add
						if ($customer_id > 0) {
							$last_invoice_send_date = array('last_invoice_sent_date' => $invoice_date);
							$where_invoice_send_email_arr = array('customer_id' => $customer_id);
							$this->WebshopOrdersModel->updateData('customers_invoice', $where_invoice_send_email_arr, $last_invoice_send_date);
						}

						//$fbc_user_id	=	$this->session->userdata('LoginID');
						$shop_id		=	$this->session->userdata('ShopID');
						$Ishop_owner = $this->CommonModel->getShopOwnerData($shop_id);
						$Iwebshop_details = $this->CommonModel->get_webshop_details($shop_id);
						$Ishop_name = $Ishop_owner->org_shop_name;
						$ItemplateId = 'system-invoice';
						//$Ito = 'rajesh@bcod.co.in';
						$Ito = $customer_email; //old 10-08-2021
						$Ito = $bill_customer_email;
						$site_logo = '';
						if (isset($Iwebshop_details)) {
							$Ishop_logo = $this->encryption->decrypt($Iwebshop_details['site_logo']);
						} else {
							$Ishop_logo = '';
						}
						$burl = base_url();
						$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);
						$Isite_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
							<img alt="' . $Ishop_name . '" border="0" src="' . $Ishop_logo . '" style="max-width:200px" />
						</a>';
						$Iusername = $customer_name;
						$ITempVars = array();
						$IDynamicVars = array();

						$ITempVars = array("##OWNER##", "##INVOICENO##", "##WEBSHOPNAME##");
						$IDynamicVars   = array($Iusername, $invoice_no, $Ishop_name);
						$ICommonVars = array($Isite_logo, $Ishop_name, $invoice_no);
						$attachment = get_s3_url('invoices/' . $pdfGeneratePdfName, $shop_id);
						//email function

						$mailSent_invoice = $this->WebshopOrdersModel->sendInvoiceHTMLEmail($Ito, $ItemplateId, $ITempVars, $IDynamicVars, $ICommonVars, $attachment);
					} else {
						// invoice generate
						if ($invoice_generate == 1) {
							// invoice updated flag
							$invoiceUpdate = array('internal_invoice_flag' => 1);
							$whereInvoiceArr = array('id' => $invoicing_one);
							$invoioceUpdated = $this->WebshopOrdersModel->updateData('invoicing', $whereInvoiceArr, $invoiceUpdate);
						}
					}
				}
			}
		}
	}
	//end invoice

	function generatePdfWebshop($invoiceID)
	{

		// pdf  data
		/*$this->load->library('Pdf');
		$data['invoice']=array('WebshopName' => 'Rajesh','CompanyName' => 'Z-Wear Distribution India Private Limited');
		$htmldata =$this->load->view('invoice/b2b/invoice_format',$data,true);
		$this->pdf->generatePdf($htmldata);*/

		// custom variable data
		// $invoice_no_update=array('value'=>$invoice_next_no);
		// $where_invoice_arr=array('identifier'=>'invoice_next_no');
		// $this->B2BOrdersModel->updateData('custom_variables',$where_invoice_arr,$invoice_no_update);
		//print_r($b2borderData);

		$invoice_id = $invoiceID;
		//echo $invoice_id;exit();
		// $invoice_id="4";
		// $order_id="58";
		$data['invoicedata'] = $this->WebshopOrdersModel->get_invoicedata_by_id($invoice_id);


		// Shop Data
		$data['custom_variables'] = $this->CommonModel->get_custom_variables();

		//getSingleDataByID

		$data['shop_id'] = $shop_id = $this->session->userdata('ShopID');
		$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($data['shop_id']);
		$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		if ($data['user_details']->parent_id == 0) {
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
		} else {
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
		}

		// dom pdf
		$this->load->library('Pdf_dom');
		//$this->load->view('invoice/b2b/invoice_format',$data);
		$htmldata = $this->load->view('invoice/webshop/invoice_format', $data, true);
		// $invoiceFileName=$this->pdf_dom->createtesting($htmldata,$invoiceID); // testing removed live
		$invoiceFileName = $this->pdf_dom->createbyshop($htmldata, $invoiceID, $data['shop_id']);
		// $invoiceFileName=$this->pdf_dom->createbyshop($htmldata,$invoiceID,$data['shop_id']); //31-07-2021
		return $invoiceFileName;
	}

	// cancel order
	// cancel order
	function cancelOrder()
	{
		if (isset($_POST['order_id'])) {
			$fbc_user_id = $this->session->userdata('LoginID'); //created by
			$order_id = $_POST['order_id'];

			//changes commment
			//$order_grandtotal_approved=$_POST['order_grandtotal_approved'];
			// $cancel_refund_type=$_POST['cancel_refund_type'];

			$customer_email = $_POST['customer_email'];
			$customer_name = $_POST['customer_name'];
			$esc_id = 'ESC-' . $_POST['esc_id']; //appned ESC order increment_id
			$increment_id = $_POST['esc_id'];
			$cancel_reason = $_POST['cancel_reason'];
			$cancel_shipment_type = $_POST['cancel_shipment_type'];
			$cancel_date = time();
			$updated_at = time();
			$created_at = time();
			// sales_order updated status=3
			/*sales order item data*/
			if ($order_id) {
				// sales_order_items
				// $itemsData=$this->CommonModel->getSingleShopDataByID('sales_order_items',array('order_id'=>$order_id),'*');
				$itemsData = $this->WebshopOrdersModel->getMultiDataById('sales_order_items', array('order_id' => $order_id), '*');
				if (isset($itemsData) && count($itemsData) > 0) {
					foreach ($itemsData as $key => $value) {
						$product_id = $value->product_id;
						$qty_ordered = $value->qty_ordered;
						//seller product updated inventory
						$product_type = $value->product_type;
						$product_inv_type = $value->product_inv_type;
						if ($qty_ordered > 0) {
							if ($product_inv_type != 'dropship') {
								$this->CommonModel->incrementAvailableQty($product_id, $qty_ordered);
							} else {
								$Product_data = $this->WebshopOrdersModel->getSingleDataByID('products', array('id' => $product_id), 'shop_id,shop_product_id');
								if (!empty($Product_data)) {
									$seller_shopcode = $Product_data->shop_id;
									$seller_product_id = $Product_data->shop_product_id;
									$this->CommonModel->incrementAvailableQtyByShopCode($seller_shopcode, $seller_product_id, $qty_ordered);
								}
							}
						}
					}
				}
			}

			// exit();
			/*end sales order item data*/
			$updatedata = array(
				'order_id' => $order_id,
				'status' => 3, // status updated cancel status=3
				// 'cancel_refund_type'=>$cancel_refund_type,
				'cancel_reason' => $cancel_reason,
				'cancel_by_customer' => 2, //cancel by 3 admin
				'cancel_by_admin' => $fbc_user_id, //login user id
				'cancel_date' => $cancel_date,
				'updated_at' => $updated_at
			);

			/*updated query*/

			$where_arr = array('order_id' => $order_id);
			$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $updatedata);

			/*end updated query*/

			// if order dropship updated seller shop database b2b_sales_order updated status 3
			$b2bShops = $this->WebshopOrdersModel->getWebshopB2BShops($order_id);
			if (isset($b2bShops) && count($b2bShops) > 0) {
				foreach ($b2bShops as $key => $shop) {
					$b2b_shop_id = $shop->shop_id;
					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $b2b_shop_id), '');
					$b2b_fbc_user_id = $FbcUser->fbc_user_id;

					$args['shop_id']	=	$b2b_shop_id;
					$args['fbc_user_id']	=	$b2b_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);
					$shop_id		=	$this->session->userdata('ShopID'); //new
					$b2b_order_list = $this->ShopProductModel->getMultiDataById('b2b_orders', array('webshop_order_id' => $order_id, 'shop_id' => $shop_id), 'order_id,status,is_split,subtotal,grand_total,discount_amount,discount_percent,main_parent_id', 'order_id', 'ASC');
					if (isset($b2b_order_list) && count($b2b_order_list) > 0) {
						foreach ($b2b_order_list as $key => $value) {
							// print_r($value->status);
							// print_r($value->order_id);
							$cancel_b2b_order_id = $value->order_id;
							$cancel_b2border_update = array('status' => 3, 'updated_at' => $updated_at);
							$cancel_where_arr = array('order_id' => $cancel_b2b_order_id);
							// $cancel_where_arr=array('webshop_order_id'=>$order_id);
							$this->ShopProductModel->updateData('b2b_orders', $cancel_where_arr, $cancel_b2border_update);
						}
					} // end if
				}
			}

			// data insert sales_order_escalations
			$insertdata = array(
				'order_id' => $order_id,
				'esc_order_id' => $esc_id,
				// 'order_grandtotal_approved'=>$order_grandtotal_approved,
				// 'cancel_refund_type'=>$cancel_refund_type,
				'cancel_reason' => $cancel_reason,
				'cancel_by_admin' => $fbc_user_id, //login user id
				'created_by' => $fbc_user_id,
				'cancel_date' => $cancel_date,
				'created_at' => $created_at,
				'ip' => $_SERVER['REMOTE_ADDR']
			);
			$escalation_id = $this->WebshopOrdersModel->insertData('sales_order_escalations', $insertdata); //insert data

			$shop_id = $this->session->userdata('ShopID');

			// send email customer cancel order email

			// $shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
			// $webshop_details=$this->CommonModel->get_webshop_details($shop_id);
			// $owner_email=$shop_owner->email;
			// $templateId ='fbcuser-order-cancelled-by-fbcuser';
			// $to =$customer_email;
			// $shop_name=$shop_owner->org_shop_name;
			// $username = $shop_owner->owner_name;
			// $site_logo = '';
			// if(isset($webshop_details)){
			//  $shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
			// }
			// $burl= base_url();
			// $shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
			// $site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">
			// 		<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
			// 	</a>';
			// $TempVars = array("##OWNER##" ,"##ORDERID##",'##WEBSHOPNAME##');
			// $DynamicVars   = array($customer_name,$increment_id,$shop_name);
			// $CommonVars=array($site_logo, $shop_name);
			// if(isset($templateId)){
			// 	$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shop_id);
			// 	if($emailSendStatusFlag==1){
			// 		$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars,$increment_id,$CommonVars);
			// 	}
			// }

			// end send email


			//end bacode generate
			$arrResponse  = array('status' => 200, 'message' => 'Order cancel successfully.');
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
		// print_r($_POST);

	}

	//cancel split order
	//cancel split order
	function cancelOrderSplit()
	{
		if (isset($_POST['order_id'])) {
			$cancel_order_qty = 0;
			$order_discount_amount = 0;
			$fbc_user_id = $this->session->userdata('LoginID'); //created by
			$order_id = $_POST['order_id'];
			$parent_id = $_POST['parent_id']; //split order parent id
			$main_parent_id = $_POST['main_parent_id']; //split order parent id

			$customer_email = $_POST['customer_email'];
			$customer_name = $_POST['customer_name'];
			$esc_id = 'ESC-' . $_POST['esc_id']; //appned ESC order increment_id
			$increment_id = $_POST['esc_id'];
			$cancel_reason = $_POST['cancel_reason'];
			$cancel_shipment_type = $_POST['cancel_shipment_type'];
			$cancel_date = time();
			$updated_at = time();
			$created_at = time();

			/*end main parent data*/
			$orderData = '';
			$orderData_order_id = '';
			if ($order_id) {
				$deltedOrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '*');

				if ($deltedOrderData->status == 0) {

					$orderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $deltedOrderData->main_parent_id), '*');
					$orderData_order_id = $orderData->order_id;

					$subtotal_original = $orderData->subtotal;
					$tax_amount_original = $orderData->tax_amount;
					$discount_amount_original = $orderData->discount_amount;
					$grand_total_original = $orderData->grand_total;
					$total_qty_ordered_original = $orderData->total_qty_ordered;


					$deltedOrderDataItem = $this->WebshopOrdersModel->getMultiDataById('sales_order_items', array('order_id' => $order_id), '*');

					foreach ($deltedOrderDataItem as $key => $delteOrderItem) {

						$product_id = $delteOrderItem->product_id;
						$qty_ordered = $delteOrderItem->qty_ordered;
						$product_type = $delteOrderItem->product_type;

						if ($product_id && $qty_ordered > 0) {
							// main order item
							$orderDataItem = $this->WebshopOrdersModel->getSingleDataByID('sales_order_items', array('product_id' => $product_id, 'order_id' => $orderData_order_id), '*');
							$mainItemQty_ordered = $orderDataItem->qty_ordered;
							$item_price_actual = $delteOrderItem->price;

							if (isset($orderData->coupon_code) && !empty($orderData->coupon_code && $orderData->discount_amount > 0.00)) {
								if (isset($orderDataItem->discount_percent) && $orderDataItem->discount_percent > 0.00) {
									// discount wise price
									$item_price_discount = ($orderDataItem->discount_percent / 100) * $item_price_actual;

									$item_price = $item_price_actual - $item_price_discount;

									$order_discount_amount = $item_price_discount * $qty_ordered;
								} else {
									$item_price = $delteOrderItem->price;
								}
							} else {

								$item_price = $delteOrderItem->price;
							}

							$item_total_price = $item_price * $qty_ordered; // updated

							$item_total_price_actual = $item_price_actual * $qty_ordered; // updated

							$item_tax_percent = $delteOrderItem->tax_percent;
							$product_inv_type = $delteOrderItem->product_inv_type;

							$deltedOrderItemPercentageByAMount = ($item_tax_percent / 100) * $item_total_price;
							$deltedOrderItemAmount = $item_total_price + $deltedOrderItemPercentageByAMount;
							// qty updated
							if ($qty_ordered > 0) {
								if ($product_inv_type != 'dropship') {
									$this->CommonModel->incrementAvailableQty($product_id, $qty_ordered);
								} else {
									$Product_data = $this->WebshopOrdersModel->getSingleDataByID('products', array('id' => $product_id), 'shop_id,shop_product_id');
									if (!empty($Product_data)) {
										$seller_shopcode = $Product_data->shop_id;
										$seller_product_id = $Product_data->shop_product_id;
										$this->CommonModel->incrementAvailableQtyByShopCode($seller_shopcode, $seller_product_id, $qty_ordered);
									}
								}
							}
							// end qty updated


							//print_r($orderDataItem);
							if ($mainItemQty_ordered == $qty_ordered) {
								// main order data
								$main_item_id = $orderDataItem->item_id;
								//order qty updated main table
								$update_total_qty_ordered = $orderData->total_qty_ordered - $qty_ordered;

								$main_base_subtotal = $orderData->base_subtotal;
								$main_subtotal = $orderData->subtotal;

								$main_base_tax_amount = $orderData->base_tax_amount;
								$main_tax_amount = $orderData->tax_amount;

								$main_base_grand_total = $orderData->base_grand_total;
								$main_grand_total = $orderData->grand_total;

								$main_total_qty_ordered = $orderData->total_qty_ordered;

								$main_order_id = $orderData->order_id;
								$main_order_barcode = $orderData->order_barcode;

								// discount
								$order_voucher_code = $orderData->voucher_code;
								$order_voucher_amount = $orderData->voucher_amount;


								$main_base_discount_amount = $orderData->base_discount_amount - $order_discount_amount;
								$main_discount_amount = $orderData->discount_amount - $order_discount_amount;
								$main_discount_percent = $delteOrderItem->discount_percent;
								$main_total_discount_amount = $delteOrderItem->total_discount_amount;
								//end discount

								// updated variable list
								$update_base_subtotal = $main_base_subtotal - $item_total_price_actual;
								$update_main_subtotal = $main_subtotal - $deltedOrderItemAmount;
								$update_base_tax_amount = $main_base_tax_amount - $deltedOrderItemPercentageByAMount;
								$update_tax_amount = $main_tax_amount - $deltedOrderItemPercentageByAMount;
								$update_base_grand_total = $main_base_grand_total - $deltedOrderItemAmount;
								if ($deltedOrderItemAmount > $main_grand_total) {
									$update_grand_total = 0.00;
								} else {
									$update_grand_total = $main_grand_total - $deltedOrderItemAmount;
								}



								// main order updated array
								$mainorderupdatedata = array(
									'base_subtotal' => $update_base_subtotal,
									'subtotal' => $update_main_subtotal,
									'base_tax_amount' => $update_base_tax_amount,
									'tax_amount' => $update_tax_amount,
									'total_qty_ordered' => $update_total_qty_ordered,
									'base_grand_total' => $update_base_grand_total,
									'base_discount_amount' => $main_base_discount_amount,
									'discount_amount' => $main_discount_amount,
									/*'voucher_amount'=>$main_voucher_amount,*/
									'grand_total' => $update_grand_total,
									'updated_at' => $updated_at
								);

								/*updated query*/

								$where_arr = array('order_id' => $main_order_id);

								//print_r($mainorderupdatedata);
								$order_main_updated = $this->WebshopOrdersModel->updateData('sales_order', $where_arr, $mainorderupdatedata);
								// deleted main order item
								if ($order_main_updated) {
									$this->WebshopOrdersModel->remove_sales_order_items($main_item_id);
								}
							} else {
								$orderDataByMainParentId = $this->WebshopOrdersModel->getMultiDataById('sales_order', array('main_parent_id' => $deltedOrderData->main_parent_id, 'order_id!=' => $order_id), '*');
								//$orderData_order_id=$orderData->order_id;
								foreach ($orderDataByMainParentId as $key => $value) {
									$orderProductDataItem = $this->WebshopOrdersModel->getMultiDataById('sales_order_items', array('product_id' => $product_id, 'order_id' => $value->order_id), '*');
									if (!empty($orderProductDataItem)) {
										// split order item
										foreach ($orderProductDataItem as $key => $orderDataValue) {
											$split_item_id = $orderDataValue->item_id;
											$split_item_total_price = $orderDataValue->total_price;
											$final_updated_total_price = $split_item_total_price - $item_total_price_actual;
											$split_qty_ordered = $orderDataValue->qty_ordered;
											$final_updated_qty_ordered = $split_qty_ordered - $qty_ordered;
											// item table update list // split item order updated array
											$splititemorderupdatedata = array(
												'qty_ordered' => $final_updated_qty_ordered,
												'total_price' => $final_updated_total_price,
												'updated_at' => $updated_at
											);
											$split_item_where_arr = array('item_id' => $split_item_id);
											$updatedSplitItemData = $this->WebshopOrdersModel->updateData('sales_order_items', $split_item_where_arr, $splititemorderupdatedata);
											// split order update list


										}
									}
								}

								// main order updated
								// main order data
								$main_item_id = $orderDataItem->item_id;

								//order qty updated main table
								$update_total_qty_ordered = $orderData->total_qty_ordered - $qty_ordered;

								$main_base_subtotal = $orderData->base_subtotal;
								$main_subtotal = $orderData->subtotal;

								$main_base_tax_amount = $orderData->base_tax_amount;
								$main_tax_amount = $orderData->tax_amount;

								$main_base_grand_total = $orderData->base_grand_total;
								$main_grand_total = $orderData->grand_total;

								$main_total_qty_ordered = $orderData->total_qty_ordered;

								$main_order_id = $orderData->order_id;
								$main_order_barcode = $orderData->order_barcode;

								// discount
								$order_voucher_code = $orderData->voucher_code;
								$order_voucher_amount = $orderData->voucher_amount;

								$main_base_discount_amount = $orderData->base_discount_amount - $order_discount_amount;
								$main_discount_amount = $orderData->discount_amount - $order_discount_amount;
								$main_discount_percent = $delteOrderItem->discount_percent;
								$main_total_discount_amount = $delteOrderItem->total_discount_amount;
								//end discount
								// updated variable list
								$update_base_subtotal = $main_base_subtotal - $item_total_price_actual;
								$update_main_subtotal = $main_subtotal - $deltedOrderItemAmount;
								$update_base_tax_amount = $main_base_tax_amount - $deltedOrderItemPercentageByAMount;
								$update_tax_amount = $main_tax_amount - $deltedOrderItemPercentageByAMount;
								$update_base_grand_total = $main_base_grand_total - $deltedOrderItemAmount;
								if ($deltedOrderItemAmount > $main_grand_total) {
									$update_grand_total = 0.00;
								} else {
									$update_grand_total = $main_grand_total - $deltedOrderItemAmount;
								}


								// main order updated array
								$mainorderupdatedata = array(
									'base_subtotal' => $update_base_subtotal,
									'subtotal' => $update_main_subtotal,
									'base_tax_amount' => $update_base_tax_amount,
									'tax_amount' => $update_tax_amount,
									'total_qty_ordered' => $update_total_qty_ordered,
									'base_grand_total' => $update_base_grand_total,
									'base_discount_amount' => $main_base_discount_amount,
									'discount_amount' => $main_discount_amount,
									/*'voucher_amount'=>$main_voucher_amount,*/
									'grand_total' => $update_grand_total,
									'updated_at' => $updated_at
								);

								/*updated query*/

								$where_arr = array('order_id' => $main_order_id);

								//print_r($mainorderupdatedata);
								$order_main_updated = $this->WebshopOrdersModel->updateData('sales_order', $where_arr, $mainorderupdatedata);
								// deleted main order item
								if ($order_main_updated) {
									$main_item_total_price = $orderDataItem->total_price;
									$final_updated_total_price = $main_item_total_price - $item_total_price_actual;
									$main_item_qty_ordered = $orderDataItem->qty_ordered;
									$final_updated_qty_ordered = $main_item_qty_ordered - $qty_ordered;
									$main_item_total_discount_amount = $orderDataItem->total_discount_amount;
									$final_item_total_discount_amount = $main_item_total_discount_amount - $order_discount_amount;
									$mainitemorderupdatedata = array(
										'qty_ordered' => $final_updated_qty_ordered,
										'total_price' => $final_updated_total_price,
										'total_discount_amount' => $final_item_total_discount_amount,
										'updated_at' => $updated_at
									);
									$main_item_where_arr = array('item_id' => $main_item_id);
									$updatedMainItemData = $this->WebshopOrdersModel->updateData('sales_order_items', $main_item_where_arr, $mainitemorderupdatedata);
								}
								// end main order updated
							}
							//print_r($orderDataItem);
						}
					}

					// delted order status update 3
					$updatedata = array(
						'order_id' => $order_id,
						'status' => 3, // status updated cancel status=3
						// 'cancel_refund_type'=>$cancel_refund_type,
						'cancel_reason' => $cancel_reason,
						'cancel_by_customer' => 2, //cancel by 3 admin
						'cancel_by_admin' => $fbc_user_id, //login user id
						'cancel_date' => $cancel_date,
						'updated_at' => $updated_at
					);
					// updated query
					$where_arr_deleted = array('order_id' => $order_id);
					$order_deleted = $this->WebshopOrdersModel->updateData('sales_order', $where_arr_deleted, $updatedata);
					// end order deleted

					$Main_Order_Update_Data = array(
						'subtotal_original' => $subtotal_original,
						'tax_amount_original' => $tax_amount_original,
						'discount_amount_original' => $discount_amount_original,
						'grand_total_original' => $grand_total_original,
						'total_qty_ordered_original' => $total_qty_ordered_original,
						'updated_at' => $updated_at
					);
					$where_arr_main_order = array('order_id' => $orderData_order_id);
					$order_main_order = $this->WebshopOrdersModel->updateData('sales_order', $where_arr_main_order, $Main_Order_Update_Data);

					if ($order_deleted) {
						// data insert sales_order_escalations
						$insertdata = array(
							'order_id' => $order_id,
							'esc_order_id' => $esc_id,
							// 'order_grandtotal_approved'=>$order_grandtotal_approved,
							// 'cancel_refund_type'=>$cancel_refund_type,
							'order_type' => 1,
							'cancel_reason' => $cancel_reason,
							'cancel_by_admin' => $fbc_user_id, //login user id
							'created_by' => $fbc_user_id,
							'cancel_date' => $cancel_date,
							'created_at' => $created_at,
							'ip' => $_SERVER['REMOTE_ADDR']
						);
						$escalation_id = $this->WebshopOrdersModel->insertData('sales_order_escalations', $insertdata); //insert data
						//barcode generate

						$shop_id = $this->session->userdata('ShopID');
						// send email customer cancel order email
						$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
						$webshop_details = $this->CommonModel->get_webshop_details($shop_id);
						$owner_email = $shop_owner->email;
						$templateId = 'fbcuser-order-cancelled-by-fbcuser';
						$to = $customer_email;
						$shop_name = $shop_owner->org_shop_name;
						$username = $shop_owner->owner_name;
						$site_logo = '';
						if (isset($webshop_details)) {
							$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
						}
						$burl = base_url();
						$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
						$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
									<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
								</a>';
						$TempVars = array("##OWNER##", "##ORDERID##", '##WEBSHOPNAME##');
						$DynamicVars   = array($customer_name, $increment_id, $shop_name);
						$CommonVars = array($site_logo, $shop_name);
						if (isset($templateId)) {
							$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
							if ($emailSendStatusFlag == 1) {
								$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars);
							}
						}

						// end send email
						/*invoice and order complate order*/
						// if($order_deleted){
						// check parent order remaining order and send email order complated
						//$parent_id=$_POST['parent_id'];
						//$main_parent_id=$_POST['main_parent_id'];
						if (isset($orderData_order_id)) {
							// if(isset($main_parent_id)){
							$order_delete_main_parent_id_data = $this->WebshopOrdersModel->getMultiDataById('sales_order', array('main_parent_id' => $orderData_order_id, 'order_id!=' => $order_id), '*');
							if (isset($order_delete_main_parent_id_data) && count($order_delete_main_parent_id_data) > 0) {

								$count = 0;
								$tracking_not_gen = false;
								foreach ($order_delete_main_parent_id_data as $value) {
									$trk_order_id = $value->order_id;

									$Result = $this->WebshopOrdersModel->getMultiDataById('sales_order_shipment_details', array('order_id' => $trk_order_id), 'id,tracking_id');
									if (isset($Result) && count($Result) > 0) {
										foreach ($Result as $Row) {
											if (isset($Row) && ($Row->tracking_id == '-' || $Row->tracking_id == '')) {
												$count++;
											}
										}
									} else {
										$tracking_not_gen = true;
									}
								}

								if ($count > 0 || $tracking_not_gen == true) {
									$order_status = 5;
								} else {
									$order_status = 6;
								}

								// main parent order status

								$odr_update = array('status' => $order_status, 'updated_at' => time());  	// Tracking Complete
								$where_arr = array('order_id' => $orderData_order_id);
								$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $odr_update);

								// check status and send email and generate invoice process
								if ($order_status == 6 && isset($orderData)) {
									$OrderData = $orderData;

									$DurationData = $this->WebshopOrdersModel->getSingleDataByID('custom_variables', array('identifier' => 'product_return_duration'), '');

									if (isset($DurationData) && $DurationData->value != '') {
										$product_return_duration = $DurationData->value;
									} else {
										$product_return_duration = 0;
									}

									$odr_update = array('tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Complete Date
									$where_arr = array('order_id' => $main_parent_id);
									$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $odr_update);

									//-------------update webshop b2b related order to  tracking complete------------------

									$increment_id = $OrderData->increment_id;


									$site_url = str_replace('admin', '', base_url());
									$order_id = base64_encode($OrderData->order_id);
									$encoded_oid = urlencode($order_id);
									$burl = base_url();
									if ($OrderData->checkout_method == 'guest') {
										$website_url = getWebsiteUrl($shop_id, $burl);
										$return_link = $website_url . '/guest-order/detail/' . $encoded_oid;
									} else {
										$website_url = getWebsiteUrl($shop_id, $burl);
										$return_link = $website_url . '/customer/my-orders/';
									}
									//----------------Send Email to shop owner--------------------
									$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
									$webshop_details = $this->CommonModel->get_webshop_details($shop_id);
									$owner_email = $shop_owner->email;
									$templateId = 'fbcuser-order-tracking-completed';
									$to = $OrderData->customer_email;
									$shop_name = $shop_owner->org_shop_name;
									$username = $OrderData->customer_firstname . ' ' . $OrderData->customer_lastname;
									$site_logo = '';
									if (isset($webshop_details)) {
										$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
									}

									$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
									$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
													<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
												</a>';
									$TempVars = array("##OWNER##", "##ORDERID##", "##RETURNDURATION##", "##RETURNLINK##", '##WEBSHOPNAME##');
									$DynamicVars   = array($username, $increment_id, $product_return_duration, $return_link, $shop_name);
									$CommonVars = array($site_logo, $shop_name);
									if (isset($templateId)) {
										$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
										if ($emailSendStatusFlag == 1) {
											$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars);
										}
									}

									//$this->updateWebshopB2BOrderStatus($order_id); //comment only webshop now

									// invoice generate webshop b2b order related

									// invoice generate
									if (isset($this->CommonModel->page_access()->acc_inv_flag) && $this->CommonModel->page_access()->acc_inv_flag == 1 && $this->CommonModel->page_access()->semi_invoice == 0) {
										$this->invoiceGenerate($OrderData->order_id);
									}
									// }

									// end process email and invoice

								}
							}
							// end start email and invoice process
						}
					}

					// end invoice and order complate order


				} else {
					$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
					echo json_encode($arrResponse);
					exit;
				}
			}

			//exit();


			//end bacode generate
			$arrResponse  = array('status' => 200, 'message' => 'Order cancel successfully.');
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
		// print_r($_POST);

	} //end cancel order split

	// end cancel order

	function OpenEditAddressPopup_r()
	{
		// $data['customer_id'] = $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
		$data['address_id'] = $address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';
		$data['order_id'] = isset($_POST['order_id']) ? $_POST['order_id'] : '';

		$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('address_id' => $address_id), '*');
		// print_r($data['ShippingAddress']);die();
		// $data['customer_address'] = $this->CommonModel->getSingleShopDataByID('customers_address',array('customer_id'=>$customer_id, 'id'=> $address_id),'*');
		$data['stateList'] =  $this->CommonModel->get_states_in();
		$data['country_list'] = $this->CommonModel->get_countries();
		$View = $this->load->view('webshop/order/edit_address', $data, true);
		$this->output->set_output($View);
	}

	public function update_shipping_address()
	{
		if (isset($_POST)) {
			$state = '';
			if (isset($_POST['country'])) {
				if ($_POST['country'] == 'IN') {
					$state = isset($_POST['state_dp']) ? $_POST['state_dp'] : '';
				} else {
					$state = isset($_POST['state']) ? $_POST['state'] : '';
				}
			}
			$first_name = $this->input->post('first_name');
			$last_name = $this->input->post('last_name');
			$address_line1 = $this->input->post('address_line1');
			$address_line2 = $this->input->post('address_line2');
			$city = $this->input->post('city');
			$country = $this->input->post('country');
			$pincode = $this->input->post('pincode');
			$mobile_no = $this->input->post('mobile_no');

			// $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';
			$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';

			$address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';
			$update_array = array(
				"first_name" => $first_name,
				"last_name" => $last_name,
				"address_line1" => $address_line1,
				"address_line2" => $address_line2,
				"city" => $city,
				"country" => $country,
				"pincode" => $pincode,
				"state" => $state,
				"mobile_no" => $mobile_no,
			);

			$rowAffected = $this->WebshopOrdersModel->update_shipping_address($update_array, $address_id);
			// print_r($rowAffected);die();
		}
		// $redirect = base_url('webshop/orders/detail/'.$order_id);
		// print_r($redirect);die();

		if ($rowAffected) {
			$redirect = base_url('webshop/order/detail/' . $order_id);
			echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));
			exit();
		} else {
			$redirect = base_url('webshop/order/detail/' . $order_id);
			echo json_encode(array('flag' => 0, 'msg' => "went something wrong!", 'redirect' => $redirect));
			exit;
		}
	}

	function openQtyPopup()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id'])) {
			$data['order_id'] = $_POST['order_id'];
			$data['item_id'] = $item_id = $_POST['item_id'];
			$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order_items', array('item_id' => $item_id), '');
			$View = $this->load->view('webshop/order/oi-qty-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function itemwithqty()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id']) && isset($_POST['qty'])) {
			$order_id = $_POST['order_id'];
			$item_id = $_POST['item_id'];
			$current_tab = $_POST['current_tab'];
			$qty = $_POST['qty'];

			$ItemExist = $this->WebshopOrdersModel->checkOrderItemsExistByItemId($order_id, $item_id);

			if (isset($ItemExist) && $ItemExist->item_id != '') {
				// order_item  data
				$item_id = $ItemExist->item_id;
				$old_qty_ordered = $ItemExist->qty_ordered;
				$old_total_price = $ItemExist->total_price;
				$productOldTaxamout = $ItemExist->tax_amount * $old_qty_ordered;
				$productNewTaxamout = $ItemExist->tax_amount * $qty;
				$product_id = $ItemExist->product_id;

				$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,base_grand_total,grand_total,base_subtotal,base_tax_amount,grand_total,subtotal,tax_amount,total_qty_ordered');

				if ($qty == $old_qty_ordered) {
					$arrResponse  = array('status' => 400, 'message' => 'Item quantity not change');
					echo json_encode($arrResponse);
					exit;
				} else {
					$total_price = $qty * $ItemExist->price;
					$odr_update = array('qty_ordered' => $qty, 'total_price' => $total_price, 'edit_order_flag' => 1, 'updated_at' => time());
					$where_arr = array('item_id' => $item_id);
					$rowAffected = $this->WebshopOrdersModel->updateData('sales_order_items', $where_arr, $odr_update);

					if ($rowAffected) {
						// sales_order data
						$base_grand_total = ($OrderData->base_grand_total - $old_total_price) + $total_price;
						$base_subtotal = ($OrderData->base_subtotal - $old_total_price) + $total_price;
						$base_tax_amount = ($OrderData->base_tax_amount - $productOldTaxamout) + $productNewTaxamout;
						$grand_total = ($OrderData->grand_total - $old_total_price) + $total_price;
						$subtotal = ($OrderData->subtotal - $old_total_price) + $total_price;
						$tax_amount = ($OrderData->tax_amount - $productOldTaxamout) + $productNewTaxamout;
						$total_qty_ordered = ($OrderData->total_qty_ordered - $old_qty_ordered) + $qty;

						// product data
						$productData = $this->WebshopOrdersModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'product_id,qty,available_qty');
						$available_qty = $productData->available_qty;

						// sales_order table update
						$sale_odr_update = array('base_grand_total' => $base_grand_total, 'base_subtotal' => $base_subtotal, 'base_tax_amount' => $base_tax_amount, 'grand_total' => $grand_total, 'subtotal' => $subtotal, 'tax_amount' => $tax_amount, 'total_qty_ordered' => $total_qty_ordered, 'updated_at' => time());
						$sale_where_arr = array('order_id' => $order_id);
						$this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);

						// product inventory table update
						if ($qty > $old_qty_ordered) {
							$decreaseqty = $qty - $old_qty_ordered;
							$new_available_qty = $available_qty - $decreaseqty;
						} elseif ($qty < $old_qty_ordered) {
							$increaseqty = $old_qty_ordered - $qty;
							$new_available_qty = $available_qty + $increaseqty;
						}
						$product_inventory_update = array('available_qty' => $new_available_qty);
						$product_where_arr = array('product_id' => $product_id);
						$this->WebshopOrdersModel->updateData('products_inventory', $product_where_arr, $product_inventory_update);

						$redirect = base_url('webshop/order/detail/' . $order_id);
						$arrResponse  = array('status' => 200, 'message' => 'Product quantity updated', 'redirect' => $redirect, 'item_id' => $item_id);
						echo json_encode($arrResponse);
						exit;
					} else {
						$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
						echo json_encode($arrResponse);
						exit;
					}
				}
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Item does not belongs to this order!');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function openPricePopup()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id'])) {
			$data['order_id'] = $_POST['order_id'];
			$data['item_id'] = $item_id = $_POST['item_id'];
			$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order_items', array('item_id' => $item_id), '');
			$View = $this->load->view('webshop/order/oi-price-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function itemwithprice()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id']) && isset($_POST['price'])) {
			$order_id = $_POST['order_id'];
			$item_id = $_POST['item_id'];
			$current_tab = $_POST['current_tab'];
			$price = $_POST['price'];

			$ItemExist = $this->WebshopOrdersModel->checkOrderItemsExistByItemId($order_id, $item_id);

			if (isset($ItemExist) && $ItemExist->item_id != '') {
				// order_item  data
				$item_id = $ItemExist->item_id;
				$old_qty_ordered = $ItemExist->qty_ordered;
				$old_total_price = $ItemExist->total_price;
				$old_price = $ItemExist->price;
				$old_tax_percent = $ItemExist->tax_percent;
				$old_tax_amount = $ItemExist->tax_amount;
				$product_id = $ItemExist->product_id;

				$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,base_grand_total,grand_total,base_subtotal,base_tax_amount,grand_total,subtotal,tax_amount,total_qty_ordered');

				if ($price == $old_price) {
					$arrResponse  = array('status' => 400, 'message' => 'Item Price not change');
					echo json_encode($arrResponse);
					exit;
				} else {
					$total_price = $old_qty_ordered * $price;
					$new_tax_amount = 0.00;
					if ($old_tax_percent > 0.00 && $price > 0.00) {
						$new_tax_amount = ($price * $old_tax_percent) / 100;
					}

					$odr_update = array('price' => $price, 'total_price' => $total_price, 'tax_amount' => $new_tax_amount, 'edit_order_flag' => 1, 'updated_at' => time());
					$where_arr = array('item_id' => $item_id);
					$rowAffected = $this->WebshopOrdersModel->updateData('sales_order_items', $where_arr, $odr_update);
					if ($rowAffected) {
						$productOldTaxamout = $old_tax_amount * $old_qty_ordered;
						$productNewTaxamout = $new_tax_amount * $old_qty_ordered;

						$productOldamout = $old_total_price;
						$productnewamout = $total_price;

						$base_grand_total  = ($OrderData->base_grand_total - $productOldamout) + $productnewamout;
						$base_subtotal  = ($OrderData->base_subtotal - $productOldamout) + $productnewamout;
						$base_tax_amount  = ($OrderData->base_tax_amount  - $productOldTaxamout) + $productNewTaxamout;
						$grand_total  = ($OrderData->grand_total  - $productOldamout) + $productnewamout;
						$subtotal  = ($OrderData->subtotal  - $productOldamout) + $productnewamout;
						$tax_amount  = ($OrderData->tax_amount  - $productOldTaxamout) + $productNewTaxamout;

						// sales_order table update
						$sale_odr_update = array('base_grand_total' => $base_grand_total, 'base_subtotal' => $base_subtotal, 'base_tax_amount' => $base_tax_amount, 'grand_total' => $grand_total, 'subtotal' => $subtotal, 'tax_amount' => $tax_amount, 'updated_at' => time());
						$sale_where_arr = array('order_id' => $order_id);
						$this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);


						$redirect = base_url('webshop/order/detail/' . $order_id);
						$arrResponse  = array('status' => 200, 'message' => 'Product Price updated', 'redirect' => $redirect, 'item_id' => $item_id);
						echo json_encode($arrResponse);
						exit;
					} else {
						$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
						echo json_encode($arrResponse);
						exit;
					}
				}
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Item does not belongs to this order!');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	public function OpenVatAmountPopup()
	{
		if (isset($_POST['order_id'])) {
			$data['order_id'] = $order_id = $_POST['order_id'];
			$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order_items', array('order_id' => $order_id), '');
			$View = $this->load->view('webshop/order/vat-amount-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	public function ConfirmTaxPercent()
	{
		if (isset($_POST['order_id'])) {
			$order_id = $_POST['order_id'];
			$tax_percent = $_POST['tax_percent'];
			$ItemExist = $this->WebshopOrdersModel->checkOrderItemsExistByOrderID($order_id);
			if (isset($ItemExist) && !empty($ItemExist)) {
				$old_tax_percent = array_column($ItemExist, 'tax_percent');
				if ($old_tax_percent[0] == $tax_percent) {
					$arrResponse  = array('status' => 400, 'message' => 'Item tax percent not change');
					echo json_encode($arrResponse);
					exit;
				}
				$new_tax_amount_sum[] = '';
				$new_price_sum[] = '';
				$new_total_price_sum[] = '';
				$discount_amount_sum[] = '';
				$total_discount_amount_sum[] = '';
				foreach ($ItemExist as $key => $value) {
					$item_id = $value->item_id;

					if ($value->price > 0.00 && $value->discount_percent > 0.00) {
						$pro_actual_price = pro_actual_price($value->price, $value->tax_percent);
						$pro_price_tax_new_actual = pro_price_tax_new_actual($pro_actual_price, $tax_percent);
						$pro_price_new = $pro_actual_price + $pro_price_tax_new_actual;
						$new_total_price = $value->qty_ordered * $pro_price_new;
						$new_discount_amount = new_discount_amount($pro_price_new, $value->discount_percent);
						$new_total_discount_amount = $value->qty_ordered * $new_discount_amount;
						$pro_price_incl_tax = pro_price_incl_tax($pro_price_new, $value->discount_percent);
						$pro_price_excl_tax = pro_price_excl_tax($pro_price_incl_tax, $tax_percent);
						$new_tax_amount =  $pro_price_incl_tax - $pro_price_excl_tax;

						$discount_amount_sum[] = $new_discount_amount;
						$total_discount_amount_sum[] = $new_total_discount_amount;
					} else {
						$pro_actual_price = $value->price - $value->tax_amount;
						$new_tax_amount = ($pro_actual_price * $tax_percent) / 100;
						$pro_price_new = $pro_actual_price + $new_tax_amount;
						$new_total_price = $pro_price_new * $value->qty_ordered;
					}
					$new_tax_amount_sum[] = $new_tax_amount * $value->qty_ordered;
					$new_price_sum[] = $pro_price_new;
					$new_total_price_sum[] = $new_total_price;

					$discount_amount = isset($new_discount_amount) ? $new_discount_amount : $value->discount_amount;
					$total_discount_amount = isset($new_total_discount_amount) ? $new_total_discount_amount : $value->total_discount_amount;

					$odr_update = array('price' => $pro_price_new, 'total_price' => $new_total_price, 'tax_amount' => $new_tax_amount, 'tax_percent' => $tax_percent, 'discount_amount' => $discount_amount, 'total_discount_amount' => $total_discount_amount, 'updated_at' => time());
					$where_arr = array('item_id' => $item_id);
					$rowAffected = $this->WebshopOrdersModel->updateData('sales_order_items', $where_arr, $odr_update);
				}
				$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,base_grand_total,voucher_amount,grand_total,base_subtotal,shipping_charge,base_tax_amount,grand_total,payment_final_charge,subtotal,tax_amount,total_qty_ordered');

				if (isset($OrderData) && !empty($OrderData)) {

					$subtotal = array_sum($new_total_price_sum);
					$base_subtotal = array_sum($new_total_price_sum);
					$base_tax_amount = array_sum($new_tax_amount_sum);
					$tax_amount = array_sum($new_tax_amount_sum);
					$total_discount_amount = array_sum($total_discount_amount_sum);

					if ($OrderData->shipping_charge > 0.00) {
						$shipping_tax_amount = ($OrderData->shipping_charge * $tax_percent) / 100;
						$shipping_amount = $OrderData->shipping_charge + $shipping_tax_amount;
						$shipping_tax_percent = $tax_percent;
					} else {
						$shipping_tax_amount = shipping_tax_amount($OrderData->shipping_charge, $tax_percent);
						$shipping_amount = $OrderData->shipping_amount;
						$shipping_tax_percent = $OrderData->shipping_tax_percent;
					}

					if ($total_discount_amount > 0.00) {
						$subtotal = $base_subtotal - $total_discount_amount;
					}
					if ($shipping_amount > 0.00) {
						$subtotal = $subtotal + $shipping_amount;
					}

					$voucher_amount = $OrderData->voucher_amount;
					if ($voucher_amount > 0) {
						if ($voucher_amount >= $subtotal) {
							$grand_total = 0;
						} else {
							$grand_total = $subtotal - $voucher_amount;
						}
					} else {
						$grand_total = $subtotal;
					}
					if ($grand_total <= 0) {
						$grand_total = 0;
					}

					$grand_total = $grand_total + $OrderData->payment_final_charge;
					$base_grand_total = $grand_total;

					$sale_odr_update = array('base_grand_total' => $base_grand_total, 'grand_total' => $grand_total, 'base_subtotal' => $base_subtotal, 'base_tax_amount' => $base_tax_amount, 'tax_amount' => $base_tax_amount, 'grand_total' => $grand_total, 'subtotal' => $subtotal, 'discount_amount' => $total_discount_amount, 'base_discount_amount' => $total_discount_amount, 'shipping_tax_amount' => $shipping_tax_amount, 'shipping_amount' => $shipping_amount, 'shipping_tax_percent' => $shipping_tax_percent, 'updated_at' => time());

					$sale_where_arr = array('order_id' => $order_id);
					$this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);

					$arrResponse  = array('status' => 200, 'message' => 'Product tax(vat) amount updated');
					echo json_encode($arrResponse);
					exit;
				} else {
					$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
					echo json_encode($arrResponse);
					exit;
				}
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}








	function openDeletePopup()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id'])) {
			$data['order_id'] = $_POST['order_id'];
			$data['item_id'] = $item_id = $_POST['item_id'];
			$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order_items', array('item_id' => $item_id), '');
			$View = $this->load->view('webshop/order/oi-delete-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function itemwithdelete()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id'])) {
			$order_id = $_POST['order_id'];
			$item_id = $_POST['item_id'];
			$current_tab = $_POST['current_tab'];

			$ItemExist = $this->WebshopOrdersModel->checkOrderItemsExistByItemId($order_id, $item_id);

			$Itemcount = $this->WebshopOrdersModel->checkOrderItemscount($order_id);
			if ($Itemcount > 1) {
				if (isset($ItemExist) && $ItemExist->item_id != '') {
					// order_item  data
					$item_id = $ItemExist->item_id;
					$old_qty_ordered = $ItemExist->qty_ordered;
					$old_total_price = $ItemExist->total_price;
					$old_price = $ItemExist->price;
					$old_tax_percent = $ItemExist->tax_percent;
					$old_tax_amount = $ItemExist->tax_amount;
					$product_id = $ItemExist->product_id;

					// remove order_item
					$this->WebshopOrdersModel->remove_sales_order_items($item_id);

					$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,base_grand_total,grand_total,base_subtotal,base_tax_amount,grand_total,subtotal,tax_amount,total_qty_ordered');

					$productTotalAmout = $old_total_price;
					$productTotalTaxamout = $old_tax_amount * $old_qty_ordered;

					$base_grand_total = $OrderData->base_grand_total - $productTotalAmout;
					$base_subtotal = $OrderData->base_subtotal - $productTotalAmout;
					$base_tax_amount  = $OrderData->base_tax_amount  - $productTotalTaxamout;
					$grand_total  = $OrderData->grand_total  - $productTotalAmout;
					$subtotal  = $OrderData->subtotal  - $productTotalAmout;
					$tax_amount  = $OrderData->tax_amount  - $productTotalTaxamout;
					$total_qty_ordered = $OrderData->total_qty_ordered - $old_qty_ordered;

					// sales_order table update
					$sale_odr_update = array('base_grand_total' => $base_grand_total, 'base_subtotal' => $base_subtotal, 'base_tax_amount' => $base_tax_amount, 'grand_total' => $grand_total, 'subtotal' => $subtotal, 'tax_amount' => $tax_amount, 'total_qty_ordered' => $total_qty_ordered, 'updated_at' => time());
					$sale_where_arr = array('order_id' => $order_id);
					$this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);

					// product qty data
					$productData = $this->WebshopOrdersModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'product_id,qty,available_qty');
					$available_qty = $productData->available_qty;

					$increaseqty = $old_qty_ordered;
					$new_available_qty = $available_qty + $increaseqty;

					// product inventory table update
					$product_inventory_update = array('available_qty' => $new_available_qty);
					$product_where_arr = array('product_id' => $product_id);
					$this->WebshopOrdersModel->updateData('products_inventory', $product_where_arr, $product_inventory_update);

					$redirect = base_url('webshop/order/detail/' . $order_id);
					$arrResponse  = array('status' => 200, 'message' => 'Product Deleted', 'redirect' => $redirect, 'item_id' => $item_id);
					echo json_encode($arrResponse);
					exit;
				} else {
					$arrResponse  = array('status' => 400, 'message' => 'Item does not belongs to this order!');
					echo json_encode($arrResponse);
					exit;
				}
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Product Cant delete');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function openAddProductPopup()
	{
		if (isset($_POST['order_id'])) {
			$data['order_id'] = $_POST['order_id'];
			$View = $this->load->view('webshop/order/oi-add-product-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function itemwithAddProduct()
	{
		if (isset($_POST['order_id']) && isset($_POST['qty'])) {
			$order_id = $_POST['order_id'];
			$current_tab = $_POST['current_tab'];
			$sku = $_POST['sku'];
			$qty = $_POST['qty'];

			$productData = $this->WebshopOrdersModel->getSingleDataByID('products', array('sku' => $sku), 'id,parent_id,product_type,product_inv_type,price,webshop_price,name,product_code,barcode,tax_percent,tax_amount,estimate_delivery_time,shop_id');

			if (empty($productData)) {
				$arrResponse  = array('status' => 400, 'message' => 'This product SKU not found');
				echo json_encode($arrResponse);
				exit;
			} else {


				// check for configurable product
				if ($productData->product_type == "configurable" || $productData->product_inv_type == 'dropship') {
					$arrResponse  = array('status' => 400, 'message' => 'This product is configurable or dropship ');
					echo json_encode($arrResponse);
					exit;
				} else {
					// get sales_order data
					$salesData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'customer_id,applied_rule_ids,ship_method_id,shipping_tax_percent');
					// get cutomer data
					$customerData = $this->WebshopOrdersModel->getSingleDataByID('customers', array('id' => $salesData->customer_id), 'customer_type_id');
					$customer_type_id = $customerData->customer_type_id;

					// got values for sales_order insert

					$shop_id = $productData->shop_id;
					$product_id = $productData->id;

					// parent_item_id not in cart
					$product_type = $productData->product_type;
					$product_inv_type = $productData->product_inv_type;
					$product_name = $productData->name;
					$barcode = $productData->barcode;

					if ($product_type == 'conf-simple') {
						//take parrent product code
						$parrent_productData = $this->WebshopOrdersModel->getSingleDataByID('products', array('id' => $productData->parent_id), 'product_code');
						$product_code = $parrent_productData->product_code;
					} else {
						$product_code = $productData->product_code;
					}

					if ($product_type == 'conf-simple') {

						$variantArray = $this->WebshopOrdersModel->getvariantsValuesByProduct($productData->id);
						$Variant_obj = array();
						foreach ($variantArray as $variant) {

							$results = $this->WebshopOrdersModel->getAllvariantsValues($variant['attr_id'], $variant['attr_value']);
							$Variant_obj[] = array(
								$results['attr_name'] => $results['attr_options_name'],
							);

							$product_variants_str = json_encode($Variant_obj);
						}
					} else {
						$product_variants_str = '';
					}

					if ($product_type == 'conf-simple') {
						$parent_product_id = $productData->parent_id;
					} else {
						$parent_product_id = '';
					}

					// estimate_delivery_time logic
					$delay_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables as cv', array('identifier' => 'delay_warehouse'), 'cv.*');
					$product_time = ($productData->estimate_delivery_time != '') ? $productData->estimate_delivery_time : 0;
					$delay_warehouse_timing = (isset($delay_warehouse) && $delay_warehouse->value != '') ? $delay_warehouse->value : 0;
					if ($product_inv_type == 'buy') {
						$estimate_delivery_time = $product_time + $delay_warehouse_timing;
					} elseif ($product_inv_type == 'virtual') {
						$product_qty = $this->WebshopOrdersModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'product_id,qty,available_qty');
						$available_qty = $product_qty->available_qty;

						if ($product_qty->qty > 0) {
							$estimate_delivery_time = $product_time + $delay_warehouse_timing;
						} else {
							$product_shop_warehouse_timing = $this->WebshopOrdersModel->get_supplier_warehouse_time($shop_id);
							$product_shop_warehouse_timing = ($product_shop_warehouse_timing != '') ? $product_shop_warehouse_timing : 0;
							$estimate_delivery_time = $product_time + $delay_warehouse_timing + $product_shop_warehouse_timing;
						}
					}

					$qty_ordered = $qty;

					$specialPriceArr = $this->WebshopOrdersModel->getSpecialPrices($product_id, $customer_type_id);
					// if($specialPriceArr!=false){
					// 	$price = $specialPriceArr[0]['special_price'];
					// 	$total_price = $specialPriceArr[0]['special_price'] * $qty;
					// }else{
					// 	$price = $productData->webshop_price;
					// 	$total_price=$productData->webshop_price * $qty;
					// }
					if ($salesData->ship_method_id != NULL && $salesData->ship_method_id > 0) {

						if ($specialPriceArr != false) {
							$eu_special_price = ($specialPriceArr[0]->special_price * $salesData->shipping_tax_percent / 100) + $specialPriceArr[0]->special_price;
							$price = $eu_special_price;
							$total_price = $eu_special_price * $qty;
						} else {

							$eu_price = ($productData->price * $salesData->shipping_tax_percent / 100) + $productData->price;
							$price = $eu_price;
							$total_price = $eu_price * $qty;
						}
					} else {
						if ($specialPriceArr != false) {
							$price = $specialPriceArr[0]->special_price;
							$total_price = $specialPriceArr[0]->special_price * $qty;
						} else {
							$price = $productData->webshop_price;
							$total_price = $productData->webshop_price * $qty;
						}
					}

					if ($salesData->ship_method_id != NULL && $salesData->ship_method_id > 0) {
						$tax_percent = $salesData->shipping_tax_percent;
						$tax_amount = ($productData->price * $tax_percent / 100);
					} else {
						$tax_percent = $productData->tax_percent;
						$tax_amount = $productData->tax_amount;	//its not correct
					}


					$fbc_user_id = $this->session->userdata('LoginID');

					$insertdataitem = array(
						'order_id' => $order_id,
						'product_id' => $product_id,
						'parent_product_id' => $parent_product_id,
						'product_type' => $product_type,
						'product_inv_type' => $product_inv_type,
						'product_name' => $product_name,
						'product_code' => $product_code,
						'sku' => $sku,
						'barcode' => $barcode,
						'estimate_delivery_time' => $estimate_delivery_time,
						'product_variants' => $product_variants_str,
						'qty_ordered' => $qty_ordered,
						'price' => $price,
						'total_price' => $total_price,
						'tax_percent' => $tax_percent,
						'tax_amount' => $tax_amount,
						'created_by' => $fbc_user_id,
						'created_by_type' => 1,
						'created_at' => time(),
						'ip' => $_SERVER['REMOTE_ADDR']
					);

					$got_inserted_id = $this->WebshopOrdersModel->insertData('sales_order_items', $insertdataitem);

					// product qty data
					$product_qty_Data = $this->WebshopOrdersModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'product_id,qty,available_qty');
					$available_qty = $product_qty_Data->available_qty;
					$new_available_qty = $available_qty - $qty_ordered;

					// product inventory table update
					$product_inventory_update = array('available_qty' => $new_available_qty);
					$product_where_arr = array('product_id' => $product_id);
					$this->WebshopOrdersModel->updateData('products_inventory', $product_where_arr, $product_inventory_update);

					// sales order table update data
					$ItemExist = $this->WebshopOrdersModel->checkOrderItemsExistByItemId($order_id, $got_inserted_id);
					$item_id = $ItemExist->item_id;
					$old_total_price = $ItemExist->total_price;
					$productNewTaxamout = $ItemExist->tax_amount * $qty;
					$product_id = $ItemExist->product_id;

					$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,base_grand_total,grand_total,base_subtotal,base_tax_amount,grand_total,subtotal,tax_amount,total_qty_ordered');

					$base_grand_total = $OrderData->base_grand_total + $total_price;
					$base_subtotal = $OrderData->base_subtotal + $total_price;
					$base_tax_amount = $OrderData->base_tax_amount + $productNewTaxamout;
					$grand_total = $OrderData->grand_total + $total_price;
					$subtotal = $OrderData->subtotal + $total_price;
					$tax_amount = $OrderData->tax_amount + $productNewTaxamout;
					$total_qty_ordered = $OrderData->total_qty_ordered + $qty;

					// sales_order table update
					$sale_odr_update = array('base_grand_total' => $base_grand_total, 'base_subtotal' => $base_subtotal, 'base_tax_amount' => $base_tax_amount, 'grand_total' => $grand_total, 'subtotal' => $subtotal, 'tax_amount' => $tax_amount, 'total_qty_ordered' => $total_qty_ordered, 'updated_at' => time());
					$sale_where_arr = array('order_id' => $order_id);
					$effected = $this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);

					$arrResponse  = array('status' => 200, 'message' => 'Product Added Successfully');
					echo json_encode($arrResponse);
					exit;
				}
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function openApproveProductPopup()
	{
		if (isset($_POST['order_id'])) {
			$data['order_id'] = $_POST['order_id'];
			// $data['OrderItemData']=$OrderItemData=$this->WebshopOrdersModel->getSingleDataByID('sales_order_items',array('item_id'=>$item_id),'');
			$View = $this->load->view('webshop/order/oi-approve-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function openInternalNotesPopup()
	{
		if (isset($_POST['order_id'])) {
			$data['order_id'] = $order_id = $_POST['order_id'];
			$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');

			$View = $this->load->view('webshop/order/internal-notes-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}


	function saveNotes()
	{

		if (isset($_POST['order_id'])) {
			$order_id = $_POST['order_id'];
			$internal_notes = $_POST['internal_notes'];
			// sales_order table update
			$sale_odr_update = array('internal_notes' => $internal_notes);
			$sale_where_arr = array('order_id' => $order_id);
			$this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);

			$redirect = base_url('webshop/order/detail/' . $order_id);
			$arrResponse  = array('status' => 200, 'message' => 'Notes Add Successfully', 'redirect' => $redirect, 'order_id' => $order_id);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function order_approve()
	{
		if (isset($_POST['order_id'])) {
			$order_id = $_POST['order_id'];
			$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), 'order_id,approved_at');
			if ($OrderData->approved_at == "") {
				// sales_order table update
				$sale_odr_update = array('approved_at' => date('Y-m-d H:i:s'), 'updated_at' => time());
				$sale_where_arr = array('order_id' => $order_id);
				$this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);

				$redirect = base_url('webshop/order/detail/' . $order_id);
				$arrResponse  = array('status' => 200, 'message' => 'Order Approved', 'redirect' => $redirect, 'order_id' => $order_id);
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Order already approved');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function printshipmentlabel_table()
	{

		$order_id = $_POST['order_id'];
		$order_shipment_id = $_POST['order_shipment_id'];

		if (isset($order_id) && $order_id > 0) {
			$_data['temp_order_id'] = $order_id;
			$_data['order_shipment_id'] = $order_shipment_id;

			$this->session->set_userdata($_data);

			$arrResponse  = array('status' => 200, 'message' => '', 'order_id' => $order_id, 'order_shipment_id' => $order_shipment_id);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function orderprintlabel_table()
	{
		$order_id = $this->uri->segment(4);
		$order_shipment_id = $this->uri->segment(5);


		if (isset($order_id) && $order_id > 0) {
			$shop_id    =   $this->session->userdata('ShopID');
			$data['OrderData'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			if (empty($OrderData)) {
				redirect('/webshop/orders');
			}

			$data['OrderItems'] = $OrderItems = $this->WebshopOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);

			if ($OrderData->parent_id != ''  &&  $OrderData->parent_id > 0) {

				$data['ParentOrder'] = $ParentOrder = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $OrderData->main_parent_id), '');
				$data['SplitOrderIds'] = $this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->main_parent_id);
				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->main_parent_id, 'address_type' => 1), '');
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->main_parent_id), '');
			} else {

				$data['ShippingAddress'] = $ShippingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 2), '');
				$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $OrderData->order_id, 'address_type' => 1), '');
				$data['OrderPaymentDetail'] = $OrderPayment = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $OrderData->order_id), '');
			}

			$data['sales_order_shipment_details'] = $this->WebshopOrdersModel->get_sales_order_shipment_details($order_id, $order_shipment_id);
			$data['temp_additional_all'] = $this->WebshopOrdersModel->get_sales_order_shipment($order_id, $order_shipment_id);
			$data['temp_additional_message'] = $data['temp_additional_all']->message;
			// print_r($data['temp_additional_message']->message);die();


			$this->load->view('webshop/order/order-print-label_table', $data);
		} else {
			redirect('/webshop/orders');
		}
	}

	function openRequestPaymentPopup()
	{
		if (isset($_POST['order_id'])) {
			$data['order_id'] = $_POST['order_id'];
			$View = $this->load->view('webshop/order/oi-request-payment-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function reqest_payment_confirm()
	{
		if (isset($_POST['order_id'])) {
			$order_id = $_POST['order_id'];
			$current_tab = $_POST['current_tab'];
			$data['OrderData'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');

			$data['shopData'] = $this->WebshopModel->getShopData($_SESSION['ShopOwnerId'], $_SESSION['ShopID']);
			$shop_id = $data['shopData']->shop_id;

			if ($data['shopData']->org_website_address == "") {
				$burl = base_url();
				$webshop_address = getWebsiteUrl($shop_id, $burl);
			} else {
				$webshop_address = $data['shopData']->org_website_address;
			}

			$data_o['order_id'] = $order_id;
			$encrypted_order_id = rtrim(base64_encode(json_encode($data_o)), '=');
			$final_url = $webshop_address . "/request-payment/" . $encrypted_order_id;

			$fbc_user_id = $this->session->userdata('LoginID');

			$insertdataitem = array(
				'order_id' => $order_id,
				'created_by' => $fbc_user_id,
				'created_at' => time(),
				'ip' => $_SERVER['REMOTE_ADDR']
			);

			$got_inserted_id = $this->WebshopOrdersModel->insertData('sales_order_request_payment', $insertdataitem);

			/*----------------Send Email to shop owner--------------------*/
			$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
			$webshop_details = $this->CommonModel->get_webshop_details($shop_id);
			$templateId = 'request_a_payment_for_order';
			$to = $OrderData->customer_email;

			$increment_id  = $OrderData->increment_id;

			$shop_name = $shop_owner->org_shop_name;
			$username = $OrderData->customer_firstname . ' ' . $OrderData->customer_lastname;
			$site_logo = '';
			if (isset($webshop_details)) {
				$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
			}
			$burl = base_url();
			$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
			$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
						<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
					</a>';
			$TempVars = array("##OWNER##", "##ORDERID##", "##REQUESTAPAYMENTLINK##", '##WEBSHOPNAME##');
			$DynamicVars   = array($username, $increment_id, $final_url, $shop_name);
			$CommonVars = array($site_logo, $shop_name);
			if (isset($templateId)) {
				$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
				if ($emailSendStatusFlag == 1) {
					$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars);
				}
			}


			$redirect = base_url('webshop/order/detail/' . $order_id);
			$arrResponse  = array('status' => 200, 'message' => 'Payment Reqest Send Successfully', 'redirect' => $redirect);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function openPaymentNotesPopup()
	{
		if (isset($_POST['order_id'])) {
			$payment_id = 7;
			$data['order_id'] = $order_id = $_POST['order_id'];
			//$data['OrderItemData']=$OrderItemData=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');
			$data['OrderPaymentData'] = $OrderPaymentData = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('payment_method_id' => $payment_id), 'payment_received_note');
			if (isset($OrderPaymentData) && !empty($OrderPaymentData->payment_received_note)) {
				$data['payment_received_note'] = $OrderPaymentData->payment_received_note;
			} else {
				$data['payment_received_note'] = '';
			}
			$data['PaymentMasterData'] = $this->CommonModel->getSingleDataByID('payment_master', array('id' => $payment_id), 'id,payment_gateway,payment_gateway_key,display_name');

			$View = $this->load->view('webshop/order/payment-notes-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function saveManualPaymentNotes()
	{
		if (isset($_POST['order_id'])) {
			$order_id = $_POST['order_id'];
			$payment_received_note = $_POST['payment_received_note'];
			$payment_method_id = $_POST['payment_method_id'];
			$payment_method_name = $_POST['payment_method_name'];
			$payment_method = $_POST['payment_method'];
			$payment_type = $_POST['payment_type'];
			$insertdataitem = array(
				'order_id' => $order_id,
				'payment_received_note' => $payment_received_note,
				'payment_method_id' => $payment_method_id,
				'payment_method_name' => $payment_method_name,
				'payment_method' => $payment_method,
				'payment_type' => $payment_type,
				'created_at' => time(),
				'ip' => $_SERVER['REMOTE_ADDR']
			);

			$sales_order_payment = $this->WebshopOrdersModel->insertData('sales_order_payment', $insertdataitem);

			if ($sales_order_payment) {
				$redirect = base_url('webshop/order/detail/' . $order_id);
				$arrResponse  = array('status' => 200, 'message' => 'Manual Payment Method Add Successfully', 'redirect' => $redirect, 'order_id' => $order_id);
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			}

			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function openshippingPopup()
	{
		if (isset($_POST['order_id'])) {
			$data['order_id'] = $order_id = $_POST['order_id'];
			$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			$data['shipping_methods'] = $this->WebshopOrdersModel->getMultiDataById('shipping_methods', array('status' => 1, 'remove_flag' => 0), '');

			$View = $this->load->view('webshop/order/shipping-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function saveshipping()
	{

		if (isset($_POST['order_id'])) {
			$order_id = $_POST['order_id'];
			$shipping_method_input = $_POST['shipping_method_input'];
			// print_r($shipping_method_input);exit();
			$shiping_selected_data = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('shipping_methods', array('id' => $shipping_method_input), '');
			// sales_order table update
			$sale_odr_update = array('ship_method_name' => $shiping_selected_data->ship_method_name, 'ship_method_id' => $shipping_method_input);
			$sale_where_arr = array('order_id' => $order_id);
			$this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);

			$redirect = base_url('webshop/order/detail/' . $order_id);
			$arrResponse  = array('status' => 200, 'message' => 'Shipping updated Successfully', 'redirect' => $redirect, 'order_id' => $order_id);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function openshippingAmountPopup()
	{
		if (isset($_POST['order_id'])) {
			$data['order_id'] = $_POST['order_id'];
			$data['shipping_amount'] = $_POST['shipping_amount'];
			$View = $this->load->view('webshop/order/shipping-amount-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function saveshippingamount()
	{
		if (isset($_POST['order_id'])) {
			$order_id = $_POST['order_id'];
			$shipping_amount = $_POST['shipping_amount'];
			$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			$OrderPaymentData = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment', array('order_id' => $order_id), '');

			$OrderPaymentData_id = $OrderPaymentData->payment_id;
			$old_shipping_amount = $OrderData->shipping_amount;
			$old_shipping_tax_amount = $OrderData->shipping_tax_amount;
			$old_shipping_charge = $OrderData->shipping_charge;
			$old_shipping_tax_percent = $OrderData->shipping_tax_percent;
			$old_subtotal = $OrderData->subtotal;
			$old_grand_total = $OrderData->grand_total;

			$sales_order_payment_update = array(
				'old_shipping_amount' => $old_shipping_amount,
				'old_shipping_tax_amount' => $old_shipping_tax_amount,
				'old_shipping_charge' => $old_shipping_charge,
				'old_shipping_tax_percent' => $old_shipping_tax_percent,
				'old_subtotal' => $old_subtotal,
				'old_grand_total' => $old_grand_total,
			);
			$sale_ord_payment_where_arr = array('payment_id' => $OrderPaymentData_id);
			$this->WebshopOrdersModel->updateData('sales_order_payment', $sale_ord_payment_where_arr, $sales_order_payment_update);

			// new

			$shipping_amount = $shipping_amount;
			$shippingCalculation = (100 + $old_shipping_tax_percent) / 100;
			$finalshippingcharge = $shipping_amount / $shippingCalculation;
			$finalshippingtaxamount = $shipping_amount - $finalshippingcharge;
			$shipping_tax_amount = number_format($finalshippingtaxamount, 2);
			$shipping_charge = number_format($finalshippingcharge, 2);
			$shipping_tax_percent = $old_shipping_tax_percent;
			$subtotal = ($old_subtotal - $old_shipping_amount) + $shipping_amount;
			$grand_total = ($subtotal - $OrderData->voucher_amount) + $OrderData->payment_final_charge;


			$sale_odr_update = array(
				'shipping_amount' => $shipping_amount,
				'shipping_tax_amount' => $shipping_tax_amount,
				'shipping_charge' => $shipping_charge,
				'shipping_tax_percent' => $shipping_tax_percent,
				'subtotal' => $subtotal,
				'grand_total' => $grand_total,
			);

			$sale_where_arr = array('order_id' => $order_id);
			$this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);

			$redirect = base_url('webshop/order/detail/' . $order_id);
			$arrResponse  = array('status' => 200, 'message' => 'Shipping amount updated Successfully', 'redirect' => $redirect, 'order_id' => $order_id);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function recalculate_order_nick()
	{
		$order_id = $this->uri->segment(3);
		(new RecalculateOrderTotals())($order_id);
	}

	function recalculate_order()
	{
		$order_id = $this->uri->segment(3);
		if (isset($order_id) && $order_id > 0) {
			$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			if (empty($OrderData)) {
				echo "Order not found,please add correct order id.";
				exit();
			} else {
				if ($OrderData->parent_id > 0) {
					echo "This is split order,split orders can't proceed further";
					exit();
				} else {
					$OrderItemData = $this->WebshopOrdersModel->getMultiDataById('sales_order_items', array('order_id' => $order_id), '');

					foreach ($OrderItemData as $key => $value) {
						$items_prices[] = $value->price * $value->qty_ordered;
						$items_qty[] = $value->qty_ordered;
						$items_tax_amount[] = $value->tax_amount * $value->qty_ordered;
					}


					$base_subtotal = array_sum($items_prices);
					$subtotal = array_sum($items_prices);


					if ($OrderData->discount_amount > 0) {
						$subtotal = $base_subtotal - $OrderData->discount_amount;
					}


					$subtotal = $subtotal + $OrderData->shipping_amount;

					if ($subtotal <= 0) {
						$subtotal = 0;
					}

					$voucher_amount = $OrderData->voucher_amount;
					if ($voucher_amount > 0) {
						if ($voucher_amount >= $subtotal) {
							$grand_total = 0;
						} else {
							$grand_total = $subtotal - $voucher_amount;
						}
					} else {
						$grand_total = $subtotal;
					}

					if ($grand_total <= 0) {
						$grand_total = 0;
					}

					$grand_total = $grand_total + $OrderData->payment_final_charge;
					$base_grand_total = $grand_total;
					$tax_amount = array_sum($items_tax_amount);
					$base_tax_amount = array_sum($items_tax_amount);
					$total_qty_ordered = array_sum($items_qty);

					// sales_order table update
					$sale_odr_update = array('base_grand_total' => $base_grand_total, 'base_subtotal' => $base_subtotal, 'base_tax_amount' => $base_tax_amount, 'grand_total' => $grand_total, 'subtotal' => $subtotal, 'tax_amount' => $tax_amount, 'total_qty_ordered' => $total_qty_ordered, 'updated_at' => time());
					$sale_where_arr = array('order_id' => $order_id);
					$run = $this->WebshopOrdersModel->updateData('sales_order', $sale_where_arr, $sale_odr_update);

					// print_r($run);
					if ($run == "TRUE") {
						echo "Order Total (base_subtotal,subtotal,tax_amount,total_qty_ordered,grand_total) Recalculated Successfully";
						exit();
					} else {
						echo "There is some issue, please try again.";
						exit();
					}
				}
			}
		} else {
			echo "Please add proper order id";
			exit();
		}
	}

	public function process_order_with_missing_items()
	{
		$refund_id = (new ProcessOrderWithMissingItems())($_GET['order_id']);
		(new ProcessPaymentRefund())($refund_id);
	}

	public function process_payment_refund()
	{
		(new ProcessPaymentRefund())($_GET['refund_id']);
	}

	function OpenGrandTotalPopup()
	{
		if (isset($_POST['order_id'])) {

			$data['order_id'] = $_POST['order_id'];
			$data['multi_currencies'] = $this->WebshopOrdersModel->getMultiDataById('multi_currencies', array('remove_flag' => 0), '');
			$data['currencies_detail'] = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $_POST['order_id']), '');
			$shop_id =	$this->session->userdata('ShopID');
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);
			$View = $this->load->view('webshop/order/grand-total-popup', $data, true);
			$this->output->set_output($View);
		} else {

			echo "error";
			exit;
		}
	}

	function selectedcurrency()
	{

		if (isset($_POST['id']) &&  $_POST['id'] != '') {
			$id = $_POST['id'];
			$data = $this->WebshopOrdersModel->getSingleDataByID('multi_currencies', array('id' => $id), '');
			$conversion_rate = $data->conversion_rate;
			$currency_symbol = $data->symbol;

			$order_id = $_POST['order_id'];
			$data = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			$total = $data->grand_total;

			$total_value = $currency_symbol . " " . number_format($total * $conversion_rate, 2, '.', '');
			//print_r($total_value);

			$grand_total = array(
				'currency_rate' => $conversion_rate,
				'total' => $total_value
			);

			echo json_encode(array('flag' => 1, 'data' => $grand_total));
			exit;
		} else {
			echo "error";
			exit;
		}
	}

	function CalculateCurrencyRate()
	{

		if (isset($_POST['conversion_rate'])  &&  $_POST['conversion_rate'] != '') {

			$conversion_rate = $_POST['conversion_rate'];
			$selected_currency_id =  $_POST['selected_id'];
			$order_id = $_POST['order_id'];
			$order_deatils = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			$grandtotal = $order_deatils->grand_total;

			$currency_data = $this->WebshopOrdersModel->getSingleDataByID('multi_currencies', array('id' => $selected_currency_id), '');
			$currency_symbol = $currency_data->symbol;
			$total_value = number_format($grandtotal * $conversion_rate, 2, '.', '');

			$grand_total = array(
				'currency_symbol' => $currency_symbol,
				'total' => $total_value
			);

			echo json_encode(array('flag' => 1, 'data' => $grand_total));
			exit;
		} else {

			echo "error";
			exit;
		}
	}

	function UpdateGrandTotalValue()
	{

		if (isset($_POST['order_id'])) {
			$order_id = $_POST['order_id'];
			$selected_currency_id = $_POST['currency_option_id'];
			$conversion_rate = $_POST['conversion_rate'];
			$selected_currency_data = $this->WebshopOrdersModel->getSingleDataByID('multi_currencies', array('id' => $selected_currency_id), '');

			$currency_detail_update = array(
				'currency_name' => $selected_currency_data->name,
				'currency_code_session' => $selected_currency_data->code,
				'currency_conversion_rate' => $conversion_rate,
				'currency_symbol' => $selected_currency_data->symbol,
				'default_currency_flag' => $selected_currency_data->is_default_currency,
			);
			$currency_detail_update_where_id = array('order_id' => $order_id);
			$updated_data = $this->WebshopOrdersModel->updateData('sales_order', $currency_detail_update_where_id, $currency_detail_update);

			$redirect = base_url('webshop/order/detail/' . $order_id);
			$arrResponse  = array('flag' => 1, 'message' => 'Currency & Conversion Rate Updated Successfully', 'redirect' => $redirect, 'order_id' => $order_id);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('flag' => 0, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function delhiveryApiWebshop($apiURL, $apiTocken, $data)
	{
		$apiUrl = $apiURL;
		$apiTocken = "authorization: Token " . $apiTocken;
		// strat curl
		$url = $apiUrl;
		$dataJson = json_encode($data);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "format=json&data=" . $dataJson,
			CURLOPT_HTTPHEADER => array(
				$apiTocken,
				"cache-control: no-cache"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			return $err;
		} else {
			return $response;
		}
	}

	function deliveryAPiProcessData($FbcUser, $OrderData, $shipment_id, $webshop_order_id, $box_weight, $apiPaymentMode)
	{
		$returnData = [];
		if (isset($FbcUser->shop_flag) && $FbcUser->shop_flag == 4) {
			$shipingApidata = $this->WebshopOrdersModel->getSingleDataByID('shipment_master', array('id' => $shipment_id), 'api_details');
			$shipmentApiUrl = '';
			$shipmentApiToken = '';
			$shipmentTrackingUrl = '';
			/*start delhivery required data static to dynamic*/
			$shipments_seller_name = '';
			$shipments_seller_add = '';
			$shipments_seller_gst_tin = '';
			$shipments_return_state = '';
			$shipments_return_city = '';
			$shipments_return_country = '';
			$shipments_return_pin = '';
			$shipments_return_name = '';
			$shipments_return_add = '';
			$shipments_pickup_name = '';
			$shipments_pickup_city = '';
			$shipments_pickup_pin = '';
			$shipments_pickup_country = '';
			$shipments_pickup_add = '';
			$shipments_pickup_state = '';
			$shipments_phone = '';

			/*end delhivery required data static to dynamic*/


			if (isset($shipingApidata) && !empty($shipingApidata)) {
				$shipingApiDetails = json_decode($shipingApidata->api_details);
				if (isset($shipingApiDetails) && !empty($shipingApiDetails)) {
					$shipmentApiUrl = $shipingApiDetails->api_url;
					$shipmentApiToken = $shipingApiDetails->api_token;
					$shipmentTrackingUrl = $shipingApiDetails->tracking_url;
					/*start delhivery required data static to dynamic*/
					$shipments_seller_name = $shipingApiDetails->shipments->seller_name ?? '';
					$shipments_seller_add = $shipingApiDetails->shipments->seller_add ?? '';
					$shipments_seller_gst_tin = $shipingApiDetails->shipments->seller_gst_tin ?? '';
					$shipments_return_state = $shipingApiDetails->shipments->return_state ?? '';
					$shipments_return_city = $shipingApiDetails->shipments->return_city ?? '';
					$shipments_return_country = $shipingApiDetails->shipments->return_country ?? '';
					$shipments_return_pin = $shipingApiDetails->shipments->return_pin ?? '';
					$shipments_return_name = $shipingApiDetails->shipments->return_name ?? '';
					$shipments_return_add = $shipingApiDetails->shipments->return_add ?? '';
					$shipments_pickup_name = $shipingApiDetails->shipments->pickup_name ?? '';
					$shipments_pickup_city = $shipingApiDetails->shipments->pickup_city ?? '';
					$shipments_pickup_pin = $shipingApiDetails->shipments->pickup_pin ?? '';
					$shipments_pickup_country = $shipingApiDetails->shipments->pickup_country ?? '';
					$shipments_pickup_add = $shipingApiDetails->shipments->pickup_add ?? '';
					$shipments_pickup_state = $shipingApiDetails->shipments->pickup_state ?? '';
					$shipments_pickup_phone = $shipingApiDetails->shipments->phone ?? '';
					/*end delhivery required data static to dynamic*/
				}
				//json_decode($jsonobj)
			}


			// webshop data
			$webshopOrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '*');
			// shipping mobile number
			if ($webshopOrderData->main_parent_id > 0) {
				$order_main_parent_id = $webshopOrderData->main_parent_id;
			} else {
				$order_main_parent_id = $webshop_order_id;
			}
			$shipping_data = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $order_main_parent_id, 'address_type' => 2), '*');
			//end shipping mobile number

			$mobile_number_shipping = $shipping_data->mobile_no;
			$ship_address_line1 = $shipping_data->address_line1;
			$ship_address_line2 = $shipping_data->address_line2;
			$ship_pincode = $shipping_data->pincode;
			$ship_city = $shipping_data->city;
			$ship_state = $shipping_data->state;
			$ship_country = $shipping_data->country;

			// webshop price
			$webshopProductPrice = 0;
			$webshopTaxValue = $webshopOrderData->tax_amount; // tax
			$webshopTotalAmount = 0;
			$boxWeight = 0;
			$productQty = 0;
			$webshopCodAmount = 0;
			$productCategoryDetails = '';
			$shipmentDataObj = array();
			$shipmentDataObj['shipments'] = array();
			$shipmentDataObj['pickup_location'] = array();

			//end webshop price

			if (isset($webshopOrderData) && !empty($webshopOrderData)) {
				$productDesc = '';
				// webshop invoicing item data
				$webshopOrderItemData = $this->WebshopOrdersModel->getMultiDataById('sales_order_items', array('order_id' => $webshop_order_id), '*');
				//print_r($webshopOrderItemData);
				//order_main_parent_id
				//exit();
				foreach ($webshopOrderItemData as $itemkey => $itemvalue) {
					if ($itemvalue->product_id > 0) {
						$WebshopOrderProduct_id = $itemvalue->product_id;
						$WebshopOrderProduct_name = $itemvalue->product_name;
						// $WebshopOrderProduct_Qty=$itemvalue->product_qty;
						$WebshopOrderProduct_Qty = $itemvalue->qty_scanned;
						// webshop product price
						$webshopProductPriceData = $this->WebshopOrdersModel->getSingleDataByID('products', array('id' => $WebshopOrderProduct_id), 'webshop_price');
						if (isset($webshopProductPriceData) && $webshopProductPriceData->webshop_price) {
							$webshopPrice = $webshopProductPriceData->webshop_price;
							$webshopProductPrice += ($webshopPrice * $WebshopOrderProduct_Qty);
						}
						// total product qty
						$productQty += $WebshopOrderProduct_Qty;


						//category
						$webshopProductCategoryId = $this->WebshopOrdersModel->getSingleDataByID('products_category', array('product_id' => $WebshopOrderProduct_id), 'category_ids');
						//end category
						if (isset($webshopProductCategoryId) && $webshopProductCategoryId->category_ids) {

							// main data base
							$productCategory = $this->CommonModel->getSingleDataByID('category', array('id' => $webshopProductCategoryId->category_ids), '*');
							// print_r($productCategory);
							$productCategoryDetails = $productCategory->cat_name;
							//end main db
						}
						// product item details
						// $productItem1['item']=array();
						$productItem['item'] = array();
						$productItem1['descr'] = $WebshopOrderProduct_name;
						// $productItem1['pcat']='Apparel';
						$productItem1['pcat'] = $productCategoryDetails;
						$productItem1['item_quantity'] = $WebshopOrderProduct_Qty;
						array_push($productItem['item'], $productItem1);

						$variant_data = '';
						if (isset($itemvalue->product_variants) && $itemvalue->product_variants != '') {
							$variants = json_decode($itemvalue->product_variants, true);
							if (isset($variants) && count($variants) > 0) {
								foreach ($variants as $pk => $single_variant) {
									if ($pk > 0) {
										$variant_data .= ', ';
									}
									foreach ($single_variant as $key => $val) {
										//$variant_data.='-'.$val;
										$variant_data .= ' ' . $key . ' - ' . $val;
									}
								}
							}
						}
						if ($variant_data) {
							$variant_data = '(' . $variant_data . ')';
						}
						$productNameVariets = $WebshopOrderProduct_name . $variant_data;
						$productDesc .= $productNameVariets . ',';
					}
				}

				if (!empty($productDesc)) {
					$productDesc = rtrim($productDesc, ',');
				}

				$shipmentData['shipments']['products_desc'] = $this->CommonModel->specialCharatcterRemove($productDesc);

				// api data
				$shipmentData['shipments']['add'] = str_replace(';', ',', $ship_address_line1) . ',' . str_replace(';', ',', $ship_address_line2);
				$shipmentData['shipments']['phone'] = $mobile_number_shipping;
				$shipmentData['shipments']['payment_mode'] = $apiPaymentMode;
				$shipmentData['shipments']['name'] = $webshopOrderData->customer_firstname . ' ' . $webshopOrderData->customer_lastname;
				$shipmentData['shipments']['pin'] = $ship_pincode;
				$shipmentData['shipments']['order'] = $webshopOrderData->order_barcode;

				$shipmentData['shipments']['city'] = $ship_city;
				$shipmentData['shipments']['state'] = $ship_state;
				$shipmentData['shipments']['country'] = $ship_country;

				// invoice grand total
				$WebshopInvoiceTotal = $webshopOrderData->grand_total;
				$webshopCodAmount = $WebshopInvoiceTotal; // pending check payment and all condition

				$shipmentData['shipments']['cod_amount'] = $webshopCodAmount;
				$shipmentData['shipments']['commodity_value'] = $webshopProductPrice;
				// $shipmentData['shipments']['tax_value']=300;
				$shipmentData['shipments']['tax_value'] = $webshopTaxValue;
				$webshopTotalAmount = $webshopProductPrice + $webshopTaxValue;
				$shipmentData['shipments']['total_amount'] = $webshopTotalAmount;

				//item data
				$shipmentData['shipments']['quantity'] = $productQty;

				// seller details
				$shipmentData['shipments']['seller_name'] = $shipments_seller_name;
				$shipmentData['shipments']['seller_add'] = $shipments_seller_add;

				$shipmentData['shipments']['seller_inv'] = $webshopOrderData->increment_id;
				// invoice date replace to created_at
				$shipmentData['shipments']['seller_inv_date'] = date('Y-m-d H:i:s');

				$shipmentData['shipments']['seller_gst_tin'] = $shipments_seller_gst_tin; // change now whuso

				//end seller details

				//return
				$shipmentData['shipments']['return_state'] = $shipments_return_state;
				$shipmentData['shipments']['return_city'] = $shipments_return_city;
				$shipmentData['shipments']['return_country'] = $shipments_return_country;
				$shipmentData['shipments']['return_pin'] = $shipments_return_pin;
				$shipmentData['shipments']['return_name'] = $shipments_return_name;
				$shipmentData['shipments']['return_add'] = $shipments_return_add;
				// end return

				// pickup address
				$pickup['name'] = $shipments_pickup_name;
				$pickup['city'] = $shipments_pickup_city;
				$pickup['pin'] = $shipments_pickup_pin;
				$pickup['country'] = $shipments_pickup_country;
				$pickup['add'] = $shipments_pickup_add;
				$pickup['state'] = $shipments_pickup_state;
				$pickup['phone'] = $shipments_pickup_phone;
				// end pickup address

				$shipmentData['shipments']['qc'] = [];
				//api call
				if (count($box_weight) > 1) {
				} else {
					if (isset($box_weight) && $box_weight[0]) {
						$boxWeight = $box_weight[0] * 1000;
					}
					$shipmentData['shipments']['weight'] = $boxWeight;
					$shipmentData['shipments']['qc'] = $productItem;
					array_push($shipmentDataObj['shipments'], $shipmentData['shipments']);
					$shipmentDataObj['pickup_location'] = $pickup;
					$apiReturnData = $this->delhiveryApiWebshop($shipmentApiUrl, $shipmentApiToken, $shipmentDataObj);
					if ($apiReturnData) {
						$tracking_id = '';
						$tracking_url = '';
						$apiResponseData = json_decode($apiReturnData);
						$apiStatus = $apiResponseData->packages[0]->status; //api status
						if ($apiStatus == 'Success') {
							$tracking_id = $apiResponseData->packages[0]->waybill;
							$tracking_url = $shipmentTrackingUrl . $tracking_id;
						}
						$returnData = array('apiReturnData' => $apiReturnData, 'apiResponseData' => $apiResponseData, 'tracking_id' => $tracking_id, 'tracking_url' => $tracking_url);
					}
				}
			}
			//end api call
		}
		return $returnData;
	}

	public function ShipmentStatus()
	{
		$current_tab = $this->uri->segment(3);
		$data['shipmentStatusList'] = (new ShipmentStatusEnum())->labelList();
		$data['PageTitle'] = 'Webshop - Orders Shipment Status';
		$data['side_menu'] = 'webshop';
		$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
		$this->load->view('webshop/shipment-status/shipment_status_list', $data);
	}

	function shipmentStatusLoadordersAjax()
	{
		$ProductData = $this->WebshopOrdersModel->get_datatables_orders_shipment_status();
		$data = array();
		foreach ($ProductData as $readData) {
			$row = array();
			$createDate = '';
			$updateDate = '';
			if (isset($readData->response) && !empty($readData->response)) {
				$orderResponse = "<span title='Click view response' class='glyphicon glyphicon-question-sign ' onclick='OpenViewResponsePopup(" . $readData->id . ")'><i class='fa fa-eye'></i></span>";
				// <button type="button" class="" onclick="OpenViewResponsePopup('.$readData->id.')">View Response</button>
			} else {
				$orderResponse = '';
			}

			if (isset($readData->status) && !empty($readData->status)) {
				$shipment_status = $this->CommonModel->CheckShipmentStatus($readData->status);
				$status_shipment = $shipment_status;
			} else {
				$status_shipment = '';
			}

			if (isset($readData->created_at) && !empty($readData->created_at)) {
				$createDate = date('d-m-Y H:i:s', strtotime($readData->created_at));
			}

			if (isset($readData->updated_at) && !empty($readData->updated_at)) {
				$updateDate = date('d-m-Y H:i:s', strtotime($readData->updated_at));
			}

			if (isset($readData->order_barcode) && !empty($readData->order_barcode)) {
				$order_url = base_url() . 'webshop/shipped-order/detail/' . $readData->order_id;
				$orderId = '<a class="link-purple " target="_blank" href="' . $order_url . '">' . $readData->order_barcode . '</a>';
			} else {
				$orderId = $readData->order_id;
			}

			if (isset($readData->tracking_url) && !empty($readData->tracking_url)) {
				$trackingID = '<a class="link-purple " target="_blank" href="' . $readData->tracking_url . '">' . $readData->tracking_id . '</a>';
			} else {
				$trackingID = $readData->tracking_id;
			}

			$row[] = $orderId;
			$row[] = $trackingID;
			$row[] = $readData->tracker_vendor;
			$row[] = $orderResponse;
			$row[] = $createDate;
			$row[] = $updateDate;
			$row[] = $status_shipment;
			$data[] = $row;
		}
		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->WebshopOrdersModel->count_all_orders_shipment_status(),
			"recordsFiltered" => $this->WebshopOrdersModel->count_filtered_orders_shipment_status(),
			"data" => $data,
		);
		echo json_encode($output);
		exit;
	}

	function OpenViewResponsePopup()
	{
		if (isset($_POST) && !empty($_POST['shipment_id'])) {
			$shipmentResponse = $this->WebshopOrdersModel->getSingleDataByID('shipment_detail_status', array('id' => $_POST['shipment_id']), 'response');
			if (isset($shipmentResponse) && !empty($shipmentResponse) && $shipmentResponse->response) {
				$data['response'] = $shipmentResponse->response;
			} else {
				$data['response'] = '';
			}
			$View = $this->load->view('webshop/shipment-status/reponsedata', $data, true);
			$this->output->set_output($View);
		}
	}

	function InitiateRefundPopup()
	{

		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			//$shop_id		=	$this->session->userdata('ShopID');

			$data['OrderData'] = $orderdata = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			//print_R($orderdata);die();
			$data['order_id'] = $order_id;
			$data['BillingAddress'] = $BillingAddress = $this->WebshopOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $order_id, 'address_type' => 1), '');
			$View = $this->load->view('webshop/order/Initiate-refund-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function InitiateRefund()
	{

		// print_R($_POST);die();
		if (isset($_POST['hidden_order_id'])) {
			$User_id = $this->session->userdata('LoginID');
			$data['order_id'] =  $order_id = $_POST['hidden_order_id'];
			$data['increment_id'] = $increment_id =  $_POST['hidden_increment_id'];
			$beneficiary_acc_no = $_POST['bene_acc_no'];
			$beneficiary_ifsc = $_POST['bene_ifsc_code'];
			$beneficiary_name = $_POST['beneficiary_name'];
			// $payment_mod = $_POST['status'];
			// $publisher_id = $_POST['hidden_publisher_id'];
			$amount_payable	 = str_replace(',', '', $_POST['amount_payable']);
			$bank_address = $_POST['bank_address'];
			$amount_type = $_POST['amount_type'];
			$bank_name = $_POST['bank_name'];

			// $data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getSingleDataByID('publisher', array('id' => $publisher_id), '');
			$data['OrderData'] = $B2BOrderDetails = $this->WebshopOrdersModel->getSingleDataByID('sales_order', array('order_id' => $order_id), '');
			$data['B2BOrderDetails'] = $OrderData = $this->WebshopOrdersModel->getSingleDataByID('b2b_orders', array('webshop_order_id' => $order_id), '');
			// print_r($OrderData);
			// die();
			$add_refund_payment_info  = $this->WebshopOrdersModel->insertData('refund_payment', array('order_id' => $order_id, 'B2b_order_id' => $OrderData->order_barcode,  'beneficiary_acc_no' => $beneficiary_acc_no, 'beneficiary_ifsc' => $beneficiary_ifsc, 'beneficiary_name' => $beneficiary_name, 'bank_address' => $bank_address, 'amount_payable' => $amount_payable, 'refund_initiated' => 1, 'refund_initiated_at' => time(), 'created_at' => time(), 'created_by' => $User_id));
			if ($add_refund_payment_info) {

				$Refund_info_table = '';
				// $shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
				// $webshop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);
				// $webshop_details = $this->CommonModel->get_webshop_details($shop_id);
				$owner_email = array('ameyas@bcod.co.in', ' heeral@whuso.in', 'hemang@whuso.in ');
				$shop_name = 'Indiamags';
				$templateId = 'initiate_refund';
				$to = $owner_email;
				$site_logo = '';
				// if (isset($webshop_details)) {
				// 	$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				// } else {
				// 	$shop_logo = '';
				// }
				$burl = base_url();
				$shop_logo = ''; // get_s3_url($shop_logo, $shop_id);
				$site_logo =  '';
				//'<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" /></a>';
				$username = 'indiamags';
				$TempVars = array();
				$DynamicVars = array();
				// print_R($OrderData);die();
				$cancel_reason = (isset($OrderData->cancel_reason) &&  $OrderData->cancel_reason != ''  ? $OrderData->cancel_reason : '');
				$TempVars = array("##CUSTOMERNAME##,##AMOUNTPAYABLE##,##ORDERDATE##,##CANCELREASON##,##CUSTOMERACCOUNTNUMBER##,##ACCOUNTTYPE##,##IFSCCODE##,##BANKNAME##,##BANKADDRESS##");
				$DynamicVars   = array($beneficiary_name, $amount_payable, $OrderData->created_at, $cancel_reason, $beneficiary_acc_no, $amount_type, $beneficiary_ifsc, $bank_name, $bank_address);
				$CommonVars = array($site_logo, $shop_name);
				$SubDynamic = $increment_id;
				if (isset($templateId)) {
					$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
					if ($emailSendStatusFlag == 1) {
						$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars);
					}
				}
				$arrResponse  = array('status' => 200, 'message' => "Payment initiated successfully.");
				echo json_encode($arrResponse);
			} else {
				$arrResponse  = array('status' => 500, 'message' => "Something went wrong");
				echo json_encode($arrResponse);
			}

			exit;



			// $View = $this->load->view('b2b/order/initiate-payment-popup', $data, true);
			// $this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function ccavenuesplitorder()
	{

		$current_tab = $this->uri->segment(2);
		$data['PageTitle'] = 'Webshop - Ccavenue-Orders';
		$data['side_menu'] = 'webshop';
		$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
		$data['ccOrders'] = $this->WebshopOrdersModel->check_ccavnue_order();

		// print_r($data['ccOrders']);
		foreach ($data['ccOrders'] as $o_key => $c_val) {
			if ($c_val['transaction_id'] != '') {
				$order_id = $c_val['order_id'];
				$OtherShops = $this->WebshopOrdersModel->getShopsForBTwoBOrders($c_val['order_id']);
				$publisher_Payment = array();
				$merchant_comm = 0;
				$total_price = 0;
				$curl_handle = curl_init();
				//  echo self::$API.$url;
				//  print_r($array);
				$post_arr =  array('order_id' => $order_id);
				$url = 'https://parkmapped.com/webapi/webshop/get_price_split_for_publisher';
				curl_setopt($curl_handle, CURLOPT_URL, $url);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl_handle, CURLOPT_POST, 1);
				curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($curl_handle, CURLOPT_POSTFIELDS, urldecode(http_build_query($post_arr)));
				$buffer = curl_exec($curl_handle);

				curl_close($curl_handle);
				$response_data = json_decode($buffer);
				$publisher_Payment = array();
				$merchant_comm = 0;
				$total_price = 0;
				if (isset($response_data->statusCode) && $response_data->statusCode == '200') {
					$i = 1;
					foreach ($response_data->publisher_item_details as $Kpublisher_id => $val_pub) {
						// print_r($val_pub->publisher_details->commision_percent);
						$commision_percent = $val_pub->publisher_details->commision_percent;
						$seller_price =   $val_pub->total_seller_items_price;
						$total_price += $val_pub->total_seller_items_price;
						$Merchant_cost = $seller_price * ($commision_percent / 100);
						$publisher_cost =  $seller_price - $Merchant_cost;
						$merchant_comm += $Merchant_cost;
						$pub_id_test = 'Test' . $i;
						$publisher_Payment[] = array('splitAmount' => number_format($publisher_cost, 2, '.', ''), 'subAccId' => $pub_id_test);
						$i++;
					}
				}
				$data['split_data_list'][$order_id][] =  json_encode($publisher_Payment);
				$data['split_data_list'][$order_id][] =  number_format($merchant_comm, 2, '.', '');
				$data[$order_id]['merComm'] =  number_format($merchant_comm, 2, '.', '');
				// print_r($data);
				// die();
				// if (isset($OtherShops)) {
				// 	foreach ($OtherShops as $shop) {
				// 		$seller_shop_id = $shop['publisher_id'];
				// 		$publisher_details = $this->WebshopOrdersModel->getPublisherIdByShopId($seller_shop_id);
				// 		$shop_order_items[$order_id][$seller_shop_id][] = $publisher_details;
				// 		$Products = $this->WebshopOrdersModel->getProductsDropShipAndVirtualWithQtyZero($order_id, $seller_shop_id);
				// 		$total_qty_ordered = 0;
				// 		$total_price = 0;
				// 		$total_price_excl_special = 0;
				// 		$discount_amout_special_price  = 0;
				// 		if ($Products != false) {
				// 			$shop_order_items[$order_id][$seller_shop_id]['item_ids'] = array();
				// 			foreach ($Products as $value) {
				// 				array_push($shop_order_items[$order_id][$seller_shop_id]['item_ids'],  $value['item_id']);
				// 				// $shop_order_items[$order_id][$seller_shop_id]['item_ids'][] = $value['item_id'];
				// 				$total_qty_ordered += $value['qty_ordered'];
				// 				$shop_product_id = $value['product_id'];
				// 			}

				// 			if (isset($shop_order_items) && count($shop_order_items) > 0) {

				// 				foreach ($shop_order_items as $orderid => $val) {
				// 					foreach ($shop_order_items[$orderid] as $seller_shop_id => $value) {
				// 						$order_item_ids = $value['item_ids'];
				// 						if (is_array($order_item_ids)) {
				// 							$item_ids = implode(',', $order_item_ids);
				// 						}
				// 						$base_grand_total = $total_price;
				// 						$base_subtotal = $total_price;
				// 						$subtotal = $total_price;
				// 						$grand_total = $total_price;
				// 						$discount_amount = 0;
				// 						$discount_percent = 0;
				// 						$total_tax_amount = 0;
				// 						$type = 2;
				// 						$OrderItems = $this->WebshopOrdersModel->getB2BOrderItemsByIds($order_id, $item_ids);
				// 						if ($OrderItems != false) {
				// 							$total_seller_items_price =  0;
				// 							foreach ($OrderItems as $item) {
				// 								$tax_percent = 0;
				// 								$tax_amount = 0;
				// 								$special_price_flag = 0;
				// 								$sp_original_price = 0.00;
				// 								$BuyerProductData =  $this->WebshopOrdersModel->getproductDetailsByShopCode($item['product_id']);
				// 								if ($BuyerProductData != false) {
				// 									$shop_product_id = $BuyerProductData['id'];
				// 									$SellerProductData =  $this->WebshopOrdersModel->getproductDetailsByShopCode($shop_product_id);
				// 									if ($SellerProductData != false) {
				// 										$checkSpecialPrice =  $this->WebshopOrdersModel->getSpecialPricingB2b($seller_shop_id, $shop_product_id);
				// 										if ($checkSpecialPrice != false) {
				// 											$special_price_flag = 1;
				// 										}
				// 										$product_id = $shop_product_id;
				// 										$parent_product_id = $SellerProductData['parent_id'];
				// 										$product_code = $SellerProductData['product_code'];
				// 										$sku = $SellerProductData['sku'];
				// 										$barcode = $SellerProductData['barcode'];
				// 										$product_name = $SellerProductData['name'];
				// 										$item_price = $SellerProductData['price'];
				// 										$item_total_price = ($SellerProductData['price'] * $item['qty_ordered']);
				// 										if ($special_price_flag == 1) {
				// 											$item_price = $checkSpecialPrice['special_price'];
				// 											$item_total_price = ($checkSpecialPrice['special_price'] * $item['qty_ordered']);
				// 										} else {
				// 											$item_price = $SellerProductData['price'];
				// 											$item_total_price = ($SellerProductData['price'] * $item['qty_ordered']);
				// 											$tax_percent = $SellerProductData['tax_percent'];
				// 											$sp_original_price = $SellerProductData['price'];
				// 										}
				// 									} else {

				// 										$parent_product_id = $item['parent_product_id'];
				// 										$product_id = $item['product_id'];
				// 										$product_code = $item['product_code'];
				// 										$sku = $item['sku'];
				// 										$barcode = $item['barcode'];
				// 										$product_name = $item['product_name'];
				// 										$item_price = $item['price'];
				// 										$item_total_price = $item['total_price'];
				// 										$tax_percent = $item['tax_percent'];
				// 									}
				// 								} else {
				// 									$parent_product_id = $item['parent_product_id'];
				// 									$product_id = $item['product_id'];
				// 									$product_code = $item['product_code'];
				// 									$sku = $item['sku'];
				// 									$barcode = $item['barcode'];
				// 									$product_name = $item['product_name'];
				// 									$item_price = $item['price'];
				// 									$item_total_price = $item['total_price'];
				// 									$tax_percent = $item['tax_percent'];
				// 								}
				// 								$special_price_original_price = ($special_price_flag == 1) ? $sp_original_price : 0;

				// 								$total_seller_items_price += $item_total_price;
				// 								$shop_order_items[$seller_shop_id]['total_seller_items_price'] = $total_seller_items_price;
				// 							}
				// 						}
				// 					}
				// 				}
				// 				$shop_order_items[$seller_shop_id]['total_seller_items_price'] = $total_seller_items_price;
				// 			}
				// 		}
				// 	}

				// 	// $data['split_data_list'] =  json_encode($publisher_Payment);
				// 	// $data['merComm'] =  number_format($merchant_comm, 2, '.', '');
				// 	// $data['reference_no'] = $increment_id;
				// }
			}
		}
		$data['shop_order_items'] = $shop_order_items;
		// echo "<pre>";
		// print_r($data);
		// die();

		$this->load->view('webshop/order/testsplt', $data);
	}

	public function abundantCartList()
	{
		$SISA_ID = $this->session->userdata('LoginID');
		if ($SISA_ID) {
			$data['ProductData'] = $this->WebshopOrdersModel->abundantCartListDetails();
			// echo "<pre>";
			// print_r($data['ProductData']);die;
			$data['PageTitle'] = 'Abundant Carts';
			$data['side_menu'] = 'abundant-carts';
			$this->load->view('webshop/order/abundantCartList', array("data" => $data));
		} else {
			return redirect('/');
		}
	}
	public function abundantCartDetails($quote_id)
	{
		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('database/attributes', $this->session->userdata('userPermission'))) {
				redirect('dashboard');
			}
		}

		$SISA_ID = $this->session->userdata('LoginID');
		if ($SISA_ID) {
			$data['ProductData'] = $this->WebshopOrdersModel->abundantCartDetails($quote_id);
			$data['ProductDetails'] = $this->WebshopOrdersModel->abundantCartProductDetails($quote_id);
			$data['getAddressDetails'] = $this->WebshopOrdersModel->abundantCartAddressDetails($quote_id);



			$data['PageTitle'] = 'Attribute Add';
			$data['side_menu'] = 'attribute';
			$this->load->view('webshop/order/abundant-cart-detail', array("data" => $data));
		} else {
			return redirect('/');
		}
	}

	public function Sent_Mail()
	{
		$quote_id = $_POST['quote_id'];

		// Get order data
		$OrderData = $this->WebshopOrdersModel->abundantCartDetails($quote_id);
		// $ProductDetails = $this->WebshopOrdersModel->abundantCartProductDetails($quote_id);
		// $product_parent_id = $ProductDetails[0]['product_parent_id']
		$OrderItems = $this->WebshopOrdersModel->getAbundantCartOrderItems($quote_id);
		$OrderDetail = $this->WebshopOrdersModel->getAbundantCartDataById($quote_id);
		$PaymentInfo = $this->WebshopOrdersModel->getOrderPaymentDataById($quote_id);
		$lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';
		// $currency_code = $currency_code;
		// echo "<pre>";
		// print_r($OrderItems);die;

		// $parent_product_id = $ProductDetails[0]['parent_product_id'];

		$email_id = $OrderData[0]['email_id'];
		$quote_id = $OrderData[0]['quote_id'];
		$mobile_no = $OrderData[0]['mobile_no'];
		$customer_name = $OrderData[0]['customer_name'];
		// print_r($mobile_no);

		// print_r($customer_name);die;
		// $oid = base64_encode($OrderDetail[0]['quote_id']);
		// print_r($oid);

		// $encoded_oid = urlencode($oid);
		// print_r($encoded_oid);

		// $guest_order_url = FRONTEND_BASE_URL . 'checkout' . $encoded_oid;
		// echo "<pre>";

		// print_r($OrderDetail);
		// die;

		// $OrderData->quote_id
		// Email sending logic
		$owner_email = [ADMIN_EMAILS];
		$shop_name = 'Indiamags';
		$templateId = 'abundant-carts';
		$to = 'snehals@bcod.co.in';
		$site_logo = '';
		$burl = base_url();
		$shop_logo = '';

		$site_logo = '<a href="' . SITE_URL . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . SITE_LOGO . '" style="max-width:200px" /></a>';

		if ($OrderDetail != false) {
			// $increment_id = $OrderDetail['increment_id'];

			// $EmailTo = $OrderDetail['customer_email'];

			// $customer_firstname = $OrderDetail['customer_firstname'];

			$subtotal = $OrderDetail[0]['subtotal'];

			// $coupon_code = $OrderDetail['coupon_code'];

			// $base_discount_amount = number_format($OrderDetail['base_discount_amount'], 2);

			// $voucher_code = $OrderDetail['voucher_code'];

			// $voucher_amount = number_format($OrderDetail['voucher_amount'], 2);

			// $tax_amount = number_format($OrderDetail['tax_amount'], 2);
			$tax_amount = $OrderDetail[0]['tax_amount'];


			$grand_total = $OrderDetail[0]['grand_total'];

			$checkout_method = $OrderDetail[0]['checkout_method'];

			$shipping_amount = $OrderDetail[0]['shipping_amount'];

			// $payment_final_charge = number_format($OrderDetail['payment_final_charge'], 2); //cod
			$payment_final_charge = $OrderDetail[0]['payment_final_charge'];



			$currency_name =  $OrderDetail[0]['currency_name'];

			$currency_code_session = $OrderDetail[0]['currency_code_session'];

			$currency_conversion_rate = $OrderDetail[0]['currency_conversion_rate'];

			// $currency_symbol = $OrderDetail[0]['currency_symbol'];

			$default_currency_flag = $OrderDetail[0]['default_currency_flag'];



			// $order_date = date('d-M-Y h:i A', $OrderDetail['created_at']);

			// if ($OrderDetail[0]['checkout_method'] == 'login') {

			if ($PaymentInfo != false) {



				$payment_method = $PaymentInfo[0]['payment_method'];

				if (isset($WebShopPaymentDetailsById['display_name']) && $WebShopPaymentDetailsById['display_name'] != null) {

					$payment_method_name = $WebShopPaymentDetailsById['display_name'];
				} else {

					$payment_method_name = $PaymentInfo[0]['payment_method_name'];
				}



				if (isset($WebShopPaymentDetailsById['message']) && $WebShopPaymentDetailsById['message'] != null) {

					$payment_method_name .= $WebShopPaymentDetailsById['message'];
				}
			} else {



				$payment_method_name = '';

				$payment_method = '';
			}
			$order_item_list = '';

			$discount_html = '';

			$voucher_html = '';

			$payment_html = '';
			$site_url = base_url();

			if ($OrderDetail[0]['checkout_method'] == 'login') {

				$lid = base64_encode($OrderDetail[0]['quote_id']);

				$encoded_lid = urlencode($lid);

				// print_r($encoded_id . 'hii');
				// die;
				// echo "hii2";

				if ($lang_code == 'fr') {

					$login_url = '<p>Vous pouvez vérifier votre commande dans Mes Commandes en <a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_lid . '">vous connectant à votre compte</a>.</p>';
				} else if ($lang_code == 'it') {

					$login_url = '<p>Puoi controllare il tuo ordine in I miei ordini <a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_lid . '">accedendo al tuo account</a>.</p>';
				} else if ($lang_code == 'pt') {

					$login_url = '<p>Você pode verificar seu pedido em Meus Pedidos <a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_lid . '">fazendo login em sua conta</a>.</p>';
				} else if ($lang_code == 'nl') {

					$login_url = '<p>U kunt uw bestelling controleren in Mijn Bestellingen door <a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_lid . '">in te loggen op uw account</a>.</p>';
				} else if ($lang_code == 'de') {

					$login_url = '<p>Sie können Ihre Bestellung unter „Meine Bestellungen“ überprüfen, <a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_lid . '"> indem Sie sich bei Ihrem Konto anmelden</a>.</p>';
				} else if ($lang_code == 'es') {

					$login_url = '<p>Puede consultar su pedido en Mis pedidos <a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_lid . '">iniciando sesión en su cuenta</a>.</p>';
				} else {

					$login_url = '<p>Complete your order <a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_lid . '">here</a> </p>';
				}
			} else if ($OrderDetail[0]['checkout_method'] == 'guest') {
				// echo "hii2";
				$encoded_id = base64_encode($OrderDetail[0]['quote_id']);

				$encoded_id = urlencode($encoded_id);

				// print_r($encoded_id);
				// die;

				if ($lang_code == 'fr') {

					$login_url = '<a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_id . '">Cliquez ici</a> pour v&#233;rifier l&#233;tat de votre commande.</p>';
				} else if ($lang_code == 'it') {

					$login_url = '<a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_id . '">Clicca qui</a> per verificare lo stato del tuo ordine.</p>';
				} else if ($lang_code == 'pt') {

					$login_url = '<a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_id . '">Clique aqui</a> para verificar o status do seu pedido.</p>';
				} else if ($lang_code == 'nl') {

					$login_url = '<a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_id . '">Klik hier</a> om de status van uw bestelling te controleren.</p>';
				} else if ($lang_code == 'de') {

					$login_url = '<a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_id . '">Klicken Sie hier,</a> um den Status Ihrer Bestellung zu &#252;berpr&#252;fen.</p>';
				} else if ($lang_code == 'es') {

					$login_url = '<a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_id . '">Haga clic aqu&#237;</a> para comprobar el estado de su pedido.</p>';
				} else {

					$login_url = '<p>Complete your order <a href="https://indiamags.com/abandoned_carts_checkout/?key=' . $encoded_id . '">here</a> </p>';
				}
			}

			if ($payment_method != '' && $payment_method == 'cod' && $payment_final_charge > 0.00) {



				if ($currency_name != '' && $currency_code_session != ''  && $default_currency_flag != 1) {



					$convertedAmount =  $currency_conversion_rate * $OrderDetail[0]['payment_final_charge'];

					$payment_final_charge_amt =  $currency_code_session . number_format($convertedAmount, 2);
				} else {

					$payment_final_charge_amt = 'INR ' . $payment_final_charge;
				}



				if ($lang_code == 'fr') {

					$Payment_Charge_txt = 'Frais de paiement';
				} else if ($lang_code == 'it') {

					$Payment_Charge_txt = 'Addebito di pagamento';
				} else if ($lang_code == 'pt') {

					$Payment_Charge_txt = 'Taxa de pagamento';
				} else if ($lang_code == 'nl') {

					$Payment_Charge_txt = 'Betalingskosten:';
				} else if ($lang_code == 'de') {

					$Payment_Charge_txt = 'Zahlungsgeb&#220;hr';
				} else if ($lang_code == 'es') {

					$Payment_Charge_txt = 'cargo de pago';
				} else {

					$Payment_Charge_txt = 'Payment Charge';
				}



				$payment_html = '<tr>

								<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

								' . $Payment_Charge_txt . '

								</td>

								<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

									<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $payment_final_charge_amt . '</span>

								</td>

							</tr>';
			}

			// $order_item_list = '';

			if (isset($OrderItems) && count($OrderItems) > 0) {

				// print_r($OrderItems);die();

				$total_itmes = count($OrderItems);

				$total_base_amount = 0;

				foreach ($OrderItems as $item) {


					$total_base_amount += $item['total_price'];

					$product_variants = '';

					if (isset($item['product_variants']) && $item['product_variants']  != '') {

						$product_variants = json_decode($item['product_variants']);
					}

					$variants = array();

					if (isset($product_variants) && $product_variants != '') {



						foreach ($product_variants as $pk => $single_variant) {

							foreach ($single_variant as $key => $val) {

								$variants[] = $key . ' : ' . $val . ' ';
							}
						}
					} else {

						$variants[] = ' ';
					}

					$variant_type = '';

					if (isset($variants) && $variants != '') {

						$variant_type = '<p style="font-weight: 500;font-size: 13px;line-height: 15px;color: #787878;">' . implode(', ', $variants) . '</p>';
					}





					if ($item['product_inv_type'] != 'dropship') {

						// $ch_obj->decrementAvailableQty($item['product_id'],$item['qty_ordered']);

					}



					if ($currency_name != '' && $currency_code_session != ''  && $default_currency_flag != 1) {





						$convertedAmount =  $currency_conversion_rate * $item['price'];

						$price_final =  number_format($convertedAmount, 2);



						$convertedAmount2 =  $currency_conversion_rate * $item['total_price'];

						$total_price_final =  number_format($convertedAmount2, 2);
					} else {



						$price_final = number_format($item['price'], 2);

						$total_price_final  = number_format($item['total_price'], 2);
					}
					$parent_product_id = $item['parent_product_id'];
					$product_id = $item['product_id'];
					$ProductImages = $this->WebshopOrdersModel->abundantCartProductImagesDetails($parent_product_id, $product_id);

					$MediaPath = CUSTOMER_EMAIL_IMAGE_URL_SHOW . '/products/thumb/';
					$base_image = '';

					foreach ($ProductImages as $img) {
						$base_image .= '<img id="" src="' . $MediaPath . $img['base_image'] . '" style="width: 100px; height: 100px;" alt="Image">';
					}
					// print_r($base_image);
					// die;
					$order_item_list .= '<tr>
						<td style="font-family: Verdana, Arial; font-weight: normal; border-collapse: collapse; vertical-align: top; padding: 10px 15px; margin: 0; border-top: 1px solid #ebebeb;" class="goods-page-image">
							' . $base_image . '
						</td>
						<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb;text-align:left">
							<p style="font-family:Verdana,Arial;font-weight:bold;margin:0 0 5px 0;color:#636363;font-style:normal;text-transform:uppercase;line-height:1.4;font-size:14px;float:left;width:100%;display:block">' . $item['product_name'] . '</p> ' . $variant_type . '
						</td>
						<td style="text-align:center;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">' . $item['qty_ordered'] . '</td>
						<td style="text-align:right;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">
							<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $price_final . '</span>
						</td>
						<td style="text-align:right;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">
							<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $total_price_final . '</span>
						</td>
					</tr>';

					// print_r($order_item_list);
					// die;
				}
			}


			if ($currency_name != '' && $currency_code_session != ''  && $default_currency_flag != 1) {



				$convertedAmount =  $currency_conversion_rate * $total_base_amount;

				$total_base_amount_final =  $currency_code_session . number_format($convertedAmount, 2);



				$convertedAmount2 =  $currency_conversion_rate * $OrderDetail[0]['tax_amount'];

				$tax_amount_final =  $currency_code_session . number_format($convertedAmount2, 2);



				$convertedAmount3 =  $currency_conversion_rate * $OrderDetail[0]['shipping_amount'];

				$shipping_amount_final =  $currency_code_session . number_format($convertedAmount3, 2);



				$convertedAmount4 =  $currency_conversion_rate * $OrderDetail[0]['subtotal'];

				$subtotal_final =  $currency_code_session . number_format($convertedAmount4, 2);



				$convertedAmount5 =  $currency_conversion_rate * $OrderDetail[0]['grand_total'];

				$grand_total_final =  $currency_code_session . number_format($convertedAmount5, 2);
			} else {

				$total_base_amount_final = 'INR ' . $total_base_amount;

				$tax_amount_final = 'INR ' . $tax_amount;

				$shipping_amount_final = 'INR ' . $shipping_amount;

				$subtotal_final = 'INR ' . $subtotal;

				$grand_total_final = 'INR ' . $grand_total;
			}



			if ($lang_code == 'fr') {

				$item_price_txt = 'Prix(' . $total_itmes . ' articles) (Taxes incluses)';

				$item_txt = 'Articles de votre commande';

				$Qty_txt = 'Quantit&#237;';

				$Price_txt = 'Prix';

				$TotalPrice_txt  = 'Prix total';

				$Taxes_txt = 'Imp&ograve;ts';

				$shipping_txt = 'Exp&#237;dition et manutention';

				$Subtotal_txt = 'Total';

				$grand_total_txt = 'Total';
			} else if ($lang_code == 'it') {

				$item_price_txt = 'Prezzo(' . $total_itmes . ' articoli) <br> (Compreso di tasse)';

				$item_txt = 'Articoli nel tuo ordine';

				$Qty_txt = 'Quantit&#224;';

				$Price_txt = 'Prezzo';

				$TotalPrice_txt  = 'Prezzo totale';

				$Taxes_txt = 'Le tasse';

				$shipping_txt = 'Spedizione &amp; Gestione';

				$Subtotal_txt = 'totale parziale';

				$grand_total_txt = 'Somma totale';
			} else if ($lang_code == 'pt') {

				$item_price_txt = 'Pre&ccedil;o(' . $total_itmes . ' itens) <br> (incluindo impostos)';

				$item_txt = 'Itens em seu pedido';

				$Qty_txt = 'Quantidade';

				$Price_txt = 'Pre&ccedil;o';

				$TotalPrice_txt  = 'Pre&ccedil;o total';

				$Taxes_txt = 'Impostos';

				$shipping_txt = 'Envio e manuseio';

				$Subtotal_txt = 'Subtotal';

				$grand_total_txt = 'Total geral';
			} else if ($lang_code == 'nl') {

				$item_price_txt = 'Prijs(' . $total_itmes . ' stuks) <br> (Inclusief belastingen)';

				$item_txt = 'Artikelen in je bestelling';

				$Qty_txt = 'Hoeveelheid';

				$Price_txt = 'Prijs';

				$TotalPrice_txt  = 'Totale prijs';

				$Taxes_txt = 'Belastingen';

				$shipping_txt = 'Verzending &amp; Behandeling';

				$Subtotal_txt = 'Subtotaal';

				$grand_total_txt = 'Eindtotaal';
			} else if ($lang_code == 'de') {

				$item_price_txt = 'Preis(' . $total_itmes . ' Artikel) <br> (Inklusive Steuern)';

				$item_txt = 'Artikel in Ihrer Bestellung';

				$Qty_txt = 'Menge';

				$Price_txt = 'Preis';

				$TotalPrice_txt  = 'Gesamtpreis';

				$Taxes_txt = 'Steuern';

				$shipping_txt = 'Versand &amp; Bearbeitung';

				$Subtotal_txt = 'Zwischensumme';

				$grand_total_txt = 'Gesamtsumme';
			} else if ($lang_code == 'es') {

				$item_price_txt = 'Precio(' . $total_itmes . ' art&#237;culos) <br> (Impuestos incluidos)';

				$item_txt = 'Art&#237;culos en tu pedido';

				$Qty_txt = 'Cantidad';

				$Price_txt = 'Precio';

				$TotalPrice_txt  = 'Precio total';

				$Taxes_txt = 'Impuestos';

				$shipping_txt = 'Env&#237;o y manejo';

				$Subtotal_txt = 'Total parcial';

				$grand_total_txt = 'Gran total';
			} else {

				$item_price_txt = 'Price(' . $total_itmes . ' items) <br> (Inclusive of taxes)';

				$item_txt = 'Items <span class="il">in</span> your <span class="il">order</span>';

				$Qty_txt = 'Qty';

				$Price_txt = 'Price';

				$TotalPrice_txt  = 'Total Price';

				$Taxes_txt = 'Taxes';

				$shipping_txt = 'Shipping &amp; Handling';

				$Subtotal_txt = 'Subtotal';

				$grand_total_txt = 'Grand Total';
			}

			$order_items = '<tr>
	
			<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:0;margin:0">
	
				<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:10px 15px;margin:0">
	
					<thead>
	
						<tr>
							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">

								Image

							</th>
							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:left;font-size:11px">
	
							' . $item_txt . '
	
							</th>
	
							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">
	
							' . $Qty_txt . '
	
							</th>
	
							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">
	
							' . $Price_txt . '
	
							</th>
	
							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">
	
							' . $TotalPrice_txt . '
	
							</th>
	
						</tr>
	
					</thead>
	
					<tbody>
	
					' . $order_item_list . '
	
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
	
											' . $item_price_txt . '
	
											</td>
	
											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
	
												<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $total_base_amount_final . '</span>
	
											</td>
	
										</tr>
	
										<tr style="padding-bottom:5px">
	
											<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
	
											' . $Taxes_txt . '
	
											</td>
	
											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
	
												<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $tax_amount_final . '</span>
	
											</td>
	
										</tr>
	
										<tr style="padding-bottom:5px">
	
											<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
	
											' . $shipping_txt . '
	
											</td>
	
											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
	
												<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $shipping_amount_final . '</span>
	
											</td>
	
										</tr>
	
										' . $discount_html . '
	
										<tr>
	
											<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
	
											' . $Subtotal_txt . '
	
											</td>
	
											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
	
												<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $subtotal_final . '</span>
	
											</td>
	
										</tr>
	
										' . $voucher_html . '
	
										' . $payment_html . '
	
										<tr>
	
											<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
	
												<strong>' . $grand_total_txt . '</strong>
	
											</td>
	
											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
	
												<strong><span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $grand_total_final . '</span></strong>
	
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

			if ($checkout_method == 'guest') {

				$oid = base64_encode($OrderDetail[0]['quote_id']);

				$encoded_oid = urlencode($oid);

				$guest_order_url = FRONTEND_BASE_URL . 'checkout' . $encoded_oid;



				if ($lang_code == 'fr') {

					$customer_note = "<p>Si vous souhaitez annuler ou retourner votre commande, veuillez <a href='" . $guest_order_url . "' target='_blank'>Cliquez ici.</a></p>";
				} else if ($lang_code == 'it') {

					$customer_note = "<p>Se desideri annullare o restituire il tuo ordine, per favore <a href='" . $guest_order_url . "' target='_blank'>clicca qui.</a></p>";
				} else if ($lang_code == 'pt') {

					$customer_note = "<p>Se pretender cancelar ou devolver a sua encomenda, por favor please <a href='" . $guest_order_url . "' target='_blank'>Clique aqui.</a></p>";
				} else if ($lang_code == 'nl') {

					$customer_note = "<p>Als u uw bestelling wilt annuleren of retourneren, alstublieft <a href='" . $guest_order_url . "' target='_blank'>Klik hier.</a></p>";
				} else if ($lang_code == 'de') {

					$customer_note = "<p>Wenn Sie Ihre Bestellung stornieren oder zur&#252;cksenden m&ouml;chten, bitte <a href='" . $guest_order_url . "' target='_blank'>Klick hier.</a></p>";
				} else if ($lang_code == 'es') {

					$customer_note = "<p>Si desea cancelar o devolver su pedido, por favor <a href='" . $guest_order_url . "' target='_blank'>haga clic aqu&#237;.</a></p>";
				} else {

					$customer_note = "<p>If you want to cancel or return you order, please <a href='" . $guest_order_url . "' target='_blank'>click here.</a></p>";
				}
			} else {

				$customer_note = '';
			}




			$customer_note = '';
		}
		$username = 'indiamags';
		$TempVars = ["##CUSTOMERQUOTEID##", "##CUSTOMERMOBILENO##", "##CUSTOMER_NOTE##", "##CUSTOMER_NAME##", "##ORDER_ITEMS##", "##LOGIN_URL##"];
		$DynamicVars = [$quote_id, $mobile_no, $customer_note, $customer_name, $order_items, $login_url];
		$CommonVars = [$site_logo, $shop_name];
		$SubDynamic = [$quote_id];

		if (isset($templateId)) {
			$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
			if ($emailSendStatusFlag == 1) {

				$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars);

				// Check if email was sent successfully
				if (!$mailSent) {
					$arrResponse = ['status' => 500, 'message' => 'Error sending email.'];
					echo json_encode($arrResponse);
					exit;
				}
			}
		}

		// Update the email_sent flag in the database
		$updateResult = $this->WebshopOrdersModel->updateEmailSentFlag($quote_id);

		if ($updateResult) {
			$arrResponse = ['status' => 200, 'message' => 'Email Sent Successfully!'];
		} else {
			$arrResponse = ['status' => 500, 'message' => 'Error updating email_sent flag.'];
		}

		echo json_encode($arrResponse);
		exit;
	}

	function RefundORderRequest()
	{
		// print_R($_POST);
		// die();
		if (isset($_POST['order_id'])) {
			$User_id = $this->session->userdata('LoginID');
			$order_id = $_POST['order_id'];
			$reason_for_cancel = $_POST['cancel_reason'];
			
			// updated status 

			$st_update = array('cancel_refund_type' => 2, 'refund_note' => $reason_for_cancel, 'updated_at' => time());
			$where_arr = array('order_id' => $order_id);
			$this->WebshopOrdersModel->updateData('sales_order', $where_arr, $st_update);
			
			$arrResponse  = array('flag' => 1, 'status' => 200, 'message' => "Order Refund successfully.");
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 500, 'message' => "Something went wrong!!");
			exit;
		}
	}
}
