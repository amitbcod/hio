<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ReturnOrderController extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
		$this->load->model('UserModel');
		$this->load->model('ReturnOrderModel');
		$this->load->model('WebshopOrdersModel');
	}

	public function index()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/returns',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab='expected_return';
		$data['current_tab']=$current_tab;
		$data['PageTitle']='Webshop - Expected Retruns';
		$data['side_menu']='webShop';
		$this->load->view('webshop/returns/expected_return_orders',$data);
	}

	public function requestOrderList()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/returns',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab='return_request';
		$data['current_tab']=$current_tab;
		$data['PageTitle']='Webshop - Request Retruns';
		$data['side_menu']='webshop';
		$this->load->view('webshop/returns/requested_return_orders',$data);
	}


	function expectedreturndorder()
	{
		$current_tab = '';
		$CustomData = $this->ReturnOrderModel->getSingleDataByID('custom_variables',array('identifier'=>'product_return_duration'),'');
		if(isset($CustomData) && $CustomData->value!=''){
			$product_return_duration=$CustomData->value;
		}else{
			$product_return_duration=0;
		}


		$shop_id		=	$this->session->userdata('ShopID');

        $ProductData = $this->ReturnOrderModel->get_datatables_expected_return_orders();


		$data = array();
		$no = $_POST['start'];

		$FinalData=array();

		foreach ($ProductData as $key=>$readData) {
			$tracking_complete_date=$readData->tracking_complete_date;
			$now = time(); //
			$your_date = $tracking_complete_date;
			$datediff = $now - $your_date;

			$no_of_days= round($datediff / (60 * 60 * 24));

			if($no_of_days<=$product_return_duration){
				$FinalData[]=$readData;
			}else{
				unset($readData);
			}

		}

		$NewProductData =  (object) $FinalData;


		foreach ($NewProductData as $readData) {
			$no++;
			$row = array();
			$qty='';

			$order_url='';

			$order_url=base_url().'webshop/shipped-order/detail/'.$readData->order_id;
			$print_url=base_url().'webshop/shipped-order/print/'.$readData->order_id;


			$payment_type=$this->CommonModel->getPaymentTypeLabel($readData->payment_type);



			if($product_return_duration>0){

					$tracking_complete_date=date(DATE_PIC_FM,$readData->tracking_complete_date);

					$return_request_due_date= date(SIS_DATE_FM, strtotime($tracking_complete_date. ' + '.$product_return_duration.' days'));
					//$return_request_due_date=strtotime($return_request_due_date);
			}else{
				$return_request_due_date='-';
			}


			$shipment_type_label=$readData->payment_method_name;

			$row[]=$readData->increment_id;
			$row[]=date(SIS_DATE_FM_WT,$readData->created_at);
			$row[]=$readData->customer_name;
			//$row[]= '-';
			$row[]= $return_request_due_date;
			$row[]=$payment_type;
			$row[]='<a class="link-purple " target="_blank" href="'.$print_url.'">Print</a>';
			$row[]='<a class="link-purple" href="'.$order_url.'">View</a>';

			$data[] = $row;

		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->ReturnOrderModel->count_all_expected_return_orders(),
						"recordsFiltered" => $this->ReturnOrderModel->count_filtered_expected_return_orders(),
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);
		exit;
	}


	function requestedreturndorder()
	{

		$current_tab = '';

		$shop_id		=	$this->session->userdata('ShopID');

        $OrderData = $this->ReturnOrderModel->get_datatables_return_request_orders();


		$data = array();
		$no = $_POST['start'];




		foreach ($OrderData as $readData) {
			$no++;
			$row = array();
			$qty='';

			$order_url='';

			$order_url=base_url().'webshop/return-request-order/detail/'.$readData->return_order_id;
			$print_url= base_url().'webshop/return-order/print/'.$readData->return_order_id;

			$status_label=$this->CommonModel->getReturnOrderStatusLabel($readData->status);
			$payment_type=$this->CommonModel->getPaymentTypeLabel($readData->payment_type);


			$shipment_type_label=$readData->payment_method_name;

			$row[]=$readData->return_order_increment_id;
			$row[]=date(SIS_DATE_FM_WT,$readData->order_created_at);
			$row[]=$readData->customer_name;
			//$row[]= '-';
			$row[]=date(SIS_DATE_FM,$readData->created_at);
			$row[]=$status_label;
			$row[]=$payment_type;
			$row[]='<a class="link-purple " target="_blank" href="'.$print_url.'">Print</a>';
			$row[]='<a class="link-purple" href="'.$order_url.'">View</a>';

			$data[] = $row;

		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->ReturnOrderModel->count_all_return_request_orders(),
						"recordsFiltered" => $this->ReturnOrderModel->count_filtered_return_request_orders(),
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);
		exit;
	}




	function detail(){
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/returns',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab=$this->uri->segment(2);
		$return_order_id=$this->uri->segment(4);
		if(isset($return_order_id) && $return_order_id>0){
			$data['PageTitle']='Webshop - Return Order Detail';
			$data['side_menu']='webshop';

			$shop_id		=	$this->session->userdata('ShopID');

			//echo $this->WebshopOrdersModel->generate_new_transaction_id().'=====';
			$data['ReturnOrderData']=$ReturnOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return',array('return_order_id'=>$return_order_id),'');

			$order_id=$ReturnOrderData->order_id;
			$data['OrderData']=$OrderData=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');

			if(empty($ReturnOrderData)){
				redirect('/webshop/orders/return-request');
			}

			$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);

			$data['OrderItems']=$OrderItems=$this->ReturnOrderModel->getReturnOrderItems($return_order_id);

			$data['ShippingAddress']=$ShippingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>2),'');
			$data['BillingAddress']=$BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>1),'');
			$data['SplitOrderIds']=$this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->order_id);

			$data['OrderPaymentDetail']=$OrderPayment=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->order_id),'');





			$this->load->view('webshop/returns/return-order-detail',$data);
		}else{
			redirect('/webshop/orders/return-request');
		}
	}


	function scanbarcodemanually(){
		if(isset($_POST['return_order_id']) && isset($_POST['barcode_item']))
		{
			$return_order_id=$_POST['return_order_id'];
			$barcode=$_POST['barcode_item'];
			//$current_tab=$_POST['current_tab'];
			$ItemExist=$this->ReturnOrderModel->checkOrderItemsExist($return_order_id,$barcode);
			if(isset($ItemExist) && $ItemExist->return_order_item_id!=''){

				$item_id=$ItemExist->return_order_item_id;
				$qty_ordered=$ItemExist->qty_return;
				$old_qty_scanned=$ItemExist->qty_return_recieved;


				if($old_qty_scanned==$qty_ordered){
					$arrResponse  = array('status' =>400 ,'message'=>'Barcode <b>'.$barcode.'</b> already scanned all quantity.');
					echo json_encode($arrResponse);exit;

				}

				else {

					if($old_qty_scanned<$qty_ordered){
						$this->ReturnOrderModel->incrementReturnOrderItemQtyScanned($item_id,$return_order_id);  //increament qty_received
					}


					$arrResponse  = array('status' =>200 ,'message'=>'Barcode scanned successfully.');
					echo json_encode($arrResponse);exit;
				}
			}else{
				$arrResponse  = array('status' =>400 ,'message'=>'Barcode <b>'.$barcode.'</b> does not belongs to this order!');
				echo json_encode($arrResponse);exit;
			}
		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong!');
				echo json_encode($arrResponse);exit;
		}
	}

	function refreshOrderItems()
	{
		if(isset($_POST['return_order_id']) && $_POST['return_order_id']!='')
		{
			$return_order_id=$_POST['return_order_id'];

			$data['ReturnOrderData']=$ReturnOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return',array('return_order_id'=>$return_order_id),'');

			$order_id=$ReturnOrderData->order_id;

			$shop_id		=	$this->session->userdata('ShopID');


			$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);

			$data['OrderItems']=$OrderItems=$this->ReturnOrderModel->getReturnOrderItems($return_order_id);

			$View = $this->load->view('webshop/returns/return-items', $data, true);
			$this->output->set_output($View);

		}else{
			echo "error";exit;
		}
	}

	function updatestock(){
		if(isset($_POST['return_order_item_id']) && $_POST['return_order_item_id']!='')
		{
			$return_order_id=$_POST['return_order_id'];
			$return_order_item_id=$_POST['return_order_item_id'];
			$flag=$_POST['flag'];


				$nameUpdate =array(
					'is_restock'	=> $flag,'updated_at'=>time()
				);
					$wher_arr=array('return_order_id'=>$return_order_id,'return_order_item_id'=> $return_order_item_id);
					$this->ReturnOrderModel->updateData('sales_order_return_items', $wher_arr,$nameUpdate);


			echo "success";exit;
		}else{
			echo "error";exit;
		}
	}

	function updateQtyApproved(){
		if(isset($_POST['return_order_item_id']) && $_POST['return_order_item_id']!='')
		{
			$return_order_id=$_POST['return_order_id'];
			$return_order_item_id=$_POST['return_order_item_id'];
			$qty_return_approved=$_POST['qty_approved'];

			$ReturnOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return',array('return_order_id'=>$return_order_id),'');

			$ReturnItemData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return_items',array('return_order_item_id'=>$return_order_item_id),'');

			if(isset($ReturnItemData->discount_amount) && $ReturnItemData->discount_amount>0){
				$price=$ReturnItemData->price-$ReturnItemData->discount_amount;  // discount amount must be deducted

			}else{
				$price=$ReturnItemData->price;
			}
			$total_price_approved=$price*$qty_return_approved;


			$nameUpdate =array(
				'qty_return_approved'	=> $qty_return_approved,'total_price_approved'	=> $total_price_approved,'updated_at'=>time()
			);
			$wher_arr=array('return_order_id'=>$return_order_id,'return_order_item_id'=> $return_order_item_id);
			$this->ReturnOrderModel->updateData('sales_order_return_items', $wher_arr,$nameUpdate);

			$this->ReturnOrderModel->UpdateOrderAfterQtyApproved($return_order_id);


			/*$QtyApprovedItem=$this->ReturnOrderModel->getFullyApprovedOrderItems($ReturnOrderData->return_order_id);
			$AllItems=$this->ReturnOrderModel->getReturnOrderItems($ReturnOrderData->return_order_id);
			if(count($QtyApprovedItem)==count($AllItems))
			{
				$status=3;
			}else{
				$status=4;
			}


			$odr_update=array('status'=>$status,'status_updated_date'=>time(),'updated_at'=>time());  //partially approved
			$where_arr=array('return_order_id'=>$return_order_id);
			$this->WebshopOrdersModel->updateData('sales_order_return',$where_arr,$odr_update);*/

			$OrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return',array('return_order_id'=>$return_order_id),'');

			$order_grandtotal_approved=$OrderData->order_grandtotal_approved;
			$arrResponse  = array('status' =>200 ,'message'=>'Qty approved successfully.','refund_approved'=>$order_grandtotal_approved);
			echo json_encode($arrResponse);exit;
		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong!');
			echo json_encode($arrResponse);exit;
		}
	}

	function rejectReturnRequest(){
		if(isset($_POST['return_order_id']) && $_POST['return_order_id']!='')
		{
			$return_order_id=$_POST['return_order_id'];

			/*$nameUpdate =array(
				'status'	=> 5,'refund_status'=>2,'updated_at'=>time()
			);*/
			$nameUpdate =array(
				'status'	=> 5,'updated_at'=>time()
			);

			//rejected

			$wher_arr=array('return_order_id'=>$return_order_id);
			$this->ReturnOrderModel->updateData('sales_order_return', $wher_arr,$nameUpdate);

			$arrResponse  = array('status' =>200 ,'message'=>'Request rejected successfully.');
			echo json_encode($arrResponse);exit;

		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong!');
			echo json_encode($arrResponse);exit;
		}
	}

	function saveReturnReceiveDate(){
		if(isset($_POST['return_order_id']) && $_POST['return_order_id']!='')
		{
			$return_order_id=$_POST['return_order_id'];
			$return_recieved_date=$_POST['return_recieved_date'];


			if($return_recieved_date!=''){
				$return_recieved_date=str_replace('/','-',$return_recieved_date);
			}

			$nameUpdate = array(
				'return_recieved_date'	=> strtotime($return_recieved_date),'updated_at'=>time()
			);

			$wher_arr=array('return_order_id'=>$return_order_id);
			$this->ReturnOrderModel->updateData('sales_order_return', $wher_arr,$nameUpdate);


			$arrResponse  = array('status' =>200 ,'message'=>'Updated successfully.');
			echo json_encode($arrResponse);exit;

		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong!');
			echo json_encode($arrResponse);exit;
		}
	}

	function openScanQtyPopup()
	{
		if(isset($_POST['return_order_id']) && isset($_POST['return_order_item_id'])){

			$data['return_order_id']=$_POST['return_order_id'];
			$data['return_order_item_id']=$item_id=$_POST['return_order_item_id'];

			$data['OrderItemData']=$OrderItemData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return_items',array('return_order_item_id'=>$item_id),'');

			$View = $this->load->view('webshop/returns/oi-scan-popup', $data, true);
			$this->output->set_output($View);
		}else{
			echo "error";exit;
		}
	}

	function scanitemwithqty(){
		if(isset($_POST['return_order_id']) && isset($_POST['return_order_item_id']) && isset($_POST['qty']))
		{
			$order_id=$_POST['return_order_id'];
			$item_id=$_POST['return_order_item_id'];
			$current_tab=$_POST['current_tab'];
			$qty=$_POST['qty'];

			$ItemExist=$this->ReturnOrderModel->checkOrderItemsExistByItemId($order_id,$item_id);
			if(isset($ItemExist) && $ItemExist->return_order_item_id!=''){

				$item_id=$ItemExist->return_order_item_id;
				$qty_return=$ItemExist->qty_return;
				$old_qty_scanned=$ItemExist->qty_return_recieved;

				 $main_qty_ordered=$ItemExist->qty_return;

				if(($old_qty_scanned==$qty_return)){
					$arrResponse  = array('status' =>400 ,'message'=>'Item already scanned all quantity.');
					echo json_encode($arrResponse);exit;

				}
				else {

					if($old_qty_scanned<$main_qty_ordered){
						$this->ReturnOrderModel->incrementOrderItemQtyScannedByQty($item_id,$qty);  //increament qty_scanned
					}

					$item_class='';
					/*
					$odr_update=array('status'=>4,'updated_at'=>time());  //partially approved
					$where_arr=array('return_order_id'=>$order_id);
					$this->WebshopOrdersModel->updateData('sales_order_return',$where_arr,$odr_update);
					*/


					$arrResponse  = array('status' =>200 ,'message'=>'Item scanned successfully.','item_id'=>$item_id,'item_class'=>$item_class);
					echo json_encode($arrResponse);exit;
				}
			}else{
				$arrResponse  = array('status' =>400 ,'message'=>'Item does not belongs to this order!');
				echo json_encode($arrResponse);exit;
			}
		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong!');
				echo json_encode($arrResponse);exit;
		}
	}

	function confirmReturnRequest(){

		if(isset($_POST['return_order_id']) && $_POST['return_order_id']!='')
		{
			$return_order_id=$_POST['return_order_id'];

			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$QtyScanItem=$this->ReturnOrderModel->getQtyFullyScannedOrderItems($return_order_id);
			$AllItems=$this->ReturnOrderModel->getReturnOrderItems($return_order_id);
			$QtyApprovedItem=$this->ReturnOrderModel->getReturnOdrItemApprovedCount($return_order_id);
			if(count($QtyScanItem)==count($AllItems) && $QtyApprovedItem == count($QtyScanItem) && $QtyApprovedItem==count($AllItems))
			{
				$status=3;
			}else{
				$status=4;
			}
			$st_update=array('status'=>$status,'refund_status'=>0,'status_updated_date'=>time(),'updated_at'=>time());

			$where_arr=array('return_order_id'=>$return_order_id);
			$this->WebshopOrdersModel->updateData('sales_order_return',$where_arr,$st_update);

			$arrResponse  = array('status' =>200 ,'message'=>'Order return confirmed successfully.');
			echo json_encode($arrResponse);exit;



		}else {
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong.');
			echo json_encode($arrResponse);exit;
		}

	}




	public function requestedrefund()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/refunds',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab='refund_request';

		$data['current_tab']=$current_tab;
		$data['PageTitle']='Webshop - Request Refund';
		$data['side_menu']='webshop';
		$this->load->view('webshop/returns/refund_orders',$data);
	}

	public function completedrefund()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/refunds',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab='refund_complete';
		$data['current_tab']=$current_tab;
		$data['PageTitle']='Webshop - Completed Refund';
		$data['side_menu']='webshop';
		$this->load->view('webshop/returns/refund_orders',$data);
	}



	function requestedrefundorder()
	{

		$current_tab =$_POST['current_tab'];

		$shop_id		=	$this->session->userdata('ShopID');

        $OrderData = $this->ReturnOrderModel->get_datatables_refund_request_orders($current_tab);


		$data = array();
		$no = $_POST['start'];



		foreach ($OrderData as $readData) {
			$no++;
			$row = array();
			$qty='';

			$order_url='';

			$order_url=base_url().'webshop/refund-request-order/detail/'.$readData->return_order_id;
			$print_url=base_url().'webshop/refund-order/print/'.$readData->return_order_id;

			$payment_type=$this->CommonModel->getPaymentTypeLabel($readData->payment_type);


			$status_label=$this->CommonModel->getReturnOrderStatusLabel($readData->status);
			$refundstatus_label=$this->CommonModel->getRefundOrderStatusLabel($readData->refund_status);

			$shipment_type_label=$readData->payment_method_name;

			$row[]=$readData->return_order_increment_id;
			$row[]=date(SIS_DATE_FM_WT,$readData->order_created_at);
			$row[]=$readData->customer_name;
			//$row[]= '-';
			$row[]=date(SIS_DATE_FM,$readData->created_at);
			$row[]=$status_label;
			$row[]=$refundstatus_label;
			$row[]=$payment_type;
			$row[]='<a class="link-purple " target="_blank" href="'.$print_url.'">Print</a>';
			$row[]='<a class="link-purple" href="'.$order_url.'">View</a>';

			$data[] = $row;

		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->ReturnOrderModel->count_all_refund_request_orders($current_tab),
						"recordsFiltered" => $this->ReturnOrderModel->count_filtered_refund_request_orders($current_tab),
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);
		exit;
	}


	function refundorderdetail(){
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/refunds',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab=$this->uri->segment(2);
		$return_order_id=$this->uri->segment(4);
		if(isset($return_order_id) && $return_order_id>0){
			$data['PageTitle']='Webshop - Refund Order Detail';
			$data['side_menu']='webshop';
			$data['current_tab']=$current_tab;

			$shop_id		=	$this->session->userdata('ShopID');

			//echo $this->WebshopOrdersModel->generate_new_transaction_id().'=====';
			$data['ReturnOrderData']=$ReturnOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return',array('return_order_id'=>$return_order_id),'');

			$order_id=$ReturnOrderData->order_id;
			$data['OrderData']=$OrderData=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');
			$data['returnStripeData']=$returnStripeData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return_stripe',array('return_order_id'=>$return_order_id),'');

			if(empty($ReturnOrderData)){
				redirect('/webshop/orders/refund-request');
			}

			$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);

			$data['OrderItems']=$OrderItems=$this->ReturnOrderModel->getReturnOrderItems($return_order_id);

			$data['ShippingAddress']=$ShippingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>2),'');
			$data['BillingAddress']=$BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>1),'');
			$data['SplitOrderIds']=$this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->order_id);

			$data['OrderPaymentDetail']=$OrderPayment=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->order_id),'');





			$this->load->view('webshop/returns/refund-order-detail',$data);
		}else{
			redirect('/webshop/orders/refund-request');
		}
	}


	function openConfirmRefundPopup()
	{
		if(isset($_POST['return_order_id']) && isset($_POST['return_order_id'])){

			$data['return_order_id']=$return_order_id=$_POST['return_order_id'];

			$data['ReturnOrderData']=$ReturnOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return',array('return_order_id'=>$return_order_id),'');

			$View = $this->load->view('webshop/returns/refund-confirmation-popup', $data, true);
			$this->output->set_output($View);
		}else{
			echo "error";exit;
		}
	}

	function refundorderconfirm(){
		if(isset($_POST['return_order_id']) && $_POST['return_order_id']!=''){


			$data['return_order_id']=$return_order_id=$_POST['return_order_id'];

			$data['ReturnOrderData']=$ReturnOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return',array('return_order_id'=>$return_order_id),'');
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$order_id=$ReturnOrderData->order_id;
			$data['OrderData']=$OrderData=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');

			$salesPaymentData = $this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$order_id),'transaction_id,payment_intent_id,payment_method_id');

			$customer_email=$OrderData->customer_email;
			$customer_name=$OrderData->customer_firstname.' '.$OrderData->customer_lastname;


			//$refund_message=$_POST['refund_message'];
			$refund_message=(isset($_POST['refund_message']) ? $_POST['refund_message'] : '');

			$odr_update=array('refund_status'=>1,'refund_approve_reject_message'=>$refund_message,'refund_approve_reject_date'=>time(),'updated_at'=>time());  //Completed
			$where_arr=array('return_order_id'=>$return_order_id);

			$this->ReturnOrderModel->updateData('sales_order_return',$where_arr,$odr_update);


			if($ReturnOrderData->refund_payment_mode==1){

				$this->load->model('WebshopModel');

				$ret_increment_id=$ReturnOrderData->return_order_increment_id;


				$CustomerTypes=$this->WebshopOrdersModel->getMultiDataById('customers_type_master',array(),'');

				$ct_types='';
				if(isset($CustomerTypes) && count($CustomerTypes)>0){
					foreach($CustomerTypes as $val){
						$ct_types_arr[]=$val->id;
					}

					$ct_types=implode(',',$ct_types_arr);

				}

				$increment_id=$OrderData->increment_id;
				$coupon_amount=$ReturnOrderData->order_grandtotal_approved;

				$discount_name = 'Refund Voucher for return order  #'.$increment_id;
				$description = 'Refund Voucher for return order #'.$increment_id;
				$start_date = date('Y-m-d');
				$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
				$apply_condition = 'discount_on_mincartval';
				$apply_type = 'by_fixed';

				$disc_status=1;

				$refund_coupon_code = 'REF-'.$increment_id.'-'.time();
				// $refund_coupon_code = 'REF-'.$increment_id;//old 27-09-2021

				$insertdata=array(
					'name'=>$discount_name,
					'description'=>$description,
					'type'=>3,
					'coupon_type'=>1,
					'usage_per_customer'=>1,
					'usge_per_coupon'=>1,
					'start_date'=>$start_date,
					'end_date'=>$end_date,
					'status'=>$disc_status,
					'apply_type'=>$apply_type,
					'apply_condition'=>$apply_condition,
					'discount_amount'=>$coupon_amount,
					'apply_to'=>$ct_types,
					'created_at'=>time(),
					'created_by'=>$fbc_user_id,
					'ip'=>$_SERVER['REMOTE_ADDR']
				);
				$sales_rule_id=$this->WebshopModel->insertData('salesrule',$insertdata);
				if($sales_rule_id){
					$insert_coupon=array(
						'rule_id'=>$sales_rule_id,
						'coupon_code'=>$refund_coupon_code,
						'created_by'=>$fbc_user_id,
						'created_at'=>time(),
						'ip'=>$_SERVER['REMOTE_ADDR']
					);
					$sales_rule_coupn_id=$this->WebshopModel->insertData('salesrule_coupon',$insert_coupon);

					if($sales_rule_coupn_id){
						$odr_update=array('refund_coupon_code'=>$refund_coupon_code,'updated_at'=>time());
						$where_arr=array('return_order_id'=>$return_order_id);
						$this->ReturnOrderModel->updateData('sales_order_return',$where_arr,$odr_update);
					}

				}

				$start_date = date('Y-m-d');
				$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
				$expiry_date = date('j F Y', strtotime($end_date));


				/*----------------Send Email to customer--------------------*/
				$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
				$amountwithcurrency=$shop_owner->currency_code." ".$coupon_amount;
				$webshop_details=$this->CommonModel->get_webshop_details($shop_id);
				$owner_email=$shop_owner->email;
				$templateId ='storecredit-voucher-returnorder';
				$to = $customer_email;
				$shop_name=$shop_owner->org_shop_name;
				$username = $shop_owner->owner_name;
				$site_logo = '';
				if(isset($webshop_details)){
				 $shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				}
				$burl= base_url();
				$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
				$site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">
						<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
					</a>';
				$TempVars = array();
				$DynamicVars = array();

				$TempVars = array("##CUSTOMERNAME##" ,"##RETURNORDERID##","##VOUCHERCODE##","##VOUCHERAMOUNT##",'##WEBSHOPNAME##',"##VOUCHEREXPIRYDATE##");
				$DynamicVars   = array($customer_name,$ret_increment_id,$refund_coupon_code,$amountwithcurrency,$shop_name,$expiry_date);
				$CommonVars=array($site_logo, $shop_name);
				if(isset($templateId)){
					$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shop_id);
					if($emailSendStatusFlag==1){
						$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars,$ret_increment_id,$CommonVars);
					}
				}
				//$this->WebshopOrdersModel->sendCommonHTMLEmail('usha@bcod.co.in', $templateId, $TempVars,$DynamicVars,$ret_increment_id,$CommonVars);// testing

			}


			if($ReturnOrderData->refund_payment_mode==3){

				$this->load->model('WebshopModel');

				$WebShopPaymentDetailsById =  $this->ReturnOrderModel->getSingleDataByID('webshop_payments',array('payment_id'=>$salesPaymentData->payment_method_id),'');
				$keyMainData=json_decode($WebShopPaymentDetailsById->gateway_details);
				$keyData=$keyMainData->key;

				$refund_strip_orderData = $this->ReturnOrderModel->getRefundStripeOrderIdLastInserted($order_id);

				if(isset($refund_strip_orderData)){

					//Changes to do
					if($refund_strip_orderData->order_grandtotal_remaining >= $ReturnOrderData->order_grandtotal_approved){
						if($OrderData->currency_code_session != '' && $OrderData->default_currency_flag == 0){
							$currency = $OrderData->currency_code_session;
							$conversion_rate = $OrderData->currency_conversion_rate;
							$total_amount =$ReturnOrderData->order_grandtotal_approved*$conversion_rate;
							$Amount_in_cents = number_format($total_amount, 2, '.', '') * 100;
						}else{
							$currency ='';
							$total_amount =$ReturnOrderData->order_grandtotal_approved;
							$Amount_in_cents = number_format($ReturnOrderData->order_grandtotal_approved, 2, '.', '') * 100;
						}
						

						
						$stripe = new \Stripe\StripeClient($keyData);
						$stripe->refunds->create([
							'payment_intent'=> $salesPaymentData->payment_intent_id,
							'amount'=> $Amount_in_cents,
							//'currency'=>strtolower($currency)
						]);  

						$insertdata=array(	
							'return_order_id'=>$return_order_id,
							'order_id'=>$order_id,
							'order_grandtotal_approved'=>$ReturnOrderData->order_grandtotal_approved,
							'order_grandtotal_approved_converted_currency'=>$currency,
							'order_grandtotal_approved_converted'=>$total_amount,
							'order_grandtotal_approved_stripe'=>$ReturnOrderData->order_grandtotal_approved,
							'order_grandtotal_remaining'=>$refund_strip_orderData->order_grandtotal_remaining-$ReturnOrderData->order_grandtotal_approved,
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('sales_order_return_stripe',$insertdata);

					}else{

						if($OrderData->currency_code_session != '' && $OrderData->default_currency_flag == 0){
							$currency = $OrderData->currency_code_session;
							$conversion_rate = $OrderData->currency_conversion_rate;
							$total_amount =$refund_strip_orderData->order_grandtotal_remaining*$conversion_rate;
							$Amount_in_cents = number_format($total_amount, 2, '.', '') * 100;
						}else{
							$currency ='';
							$total_amount =$refund_strip_orderData->order_grandtotal_remaining;
							$Amount_in_cents = number_format($refund_strip_orderData->order_grandtotal_remaining, 2, '.', '') * 100;
						}
						
						//Stripe			
						$stripe = new \Stripe\StripeClient($keyData);
						$stripe->refunds->create([
							'payment_intent'=> $salesPaymentData->payment_intent_id,
							'amount'=> $Amount_in_cents,
							//'currency'=>strtolower($currency)
						]);  

						//Stripe end

						//Voucher 

						$ret_increment_id=$ReturnOrderData->return_order_increment_id;
						$coupon_amount = $ReturnOrderData->order_grandtotal_approved-$refund_strip_orderData->order_grandtotal_remaining;
				
						$CustomerTypes=$this->WebshopOrdersModel->getMultiDataById('customers_type_master',array(),'');
						
						$ct_types='';
						if(isset($CustomerTypes) && count($CustomerTypes)>0){
							foreach($CustomerTypes as $val){
								$ct_types_arr[]=$val->id;
							}
							
							$ct_types=implode(',',$ct_types_arr);
							
						}
						
						$increment_id=$OrderData->increment_id;
						
						
						$discount_name = 'Refund Voucher for return order  #'.$increment_id;
						$description = 'Refund Voucher for return order #'.$increment_id;
						$start_date = date('Y-m-d');
						$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
						$apply_condition = 'discount_on_mincartval';
						$apply_type = 'by_fixed';
						
						$refund_coupon_code = 'REF-'.$increment_id.'-'.time();
						// $refund_coupon_code = 'REF-'.$increment_id;//old 27-09-2021
						
						$insertdata=array(	
							'name'=>$discount_name,
							'description'=>$description,
							'type'=>3,
							'coupon_type'=>1,
							'usage_per_customer'=>1,
							'usge_per_coupon'=>1,
							'start_date'=>$start_date,
							'end_date'=>$end_date, 
							'status'=>1,
							'apply_type'=>$apply_type,
							'apply_condition'=>$apply_condition,
							'discount_amount'=>$coupon_amount,
							'apply_to'=>$ct_types,
							'created_at'=>time(),
							'created_by'=>$fbc_user_id,
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('salesrule',$insertdata);
						if($sales_rule_id){
							$insert_coupon=array(	
								'rule_id'=>$sales_rule_id,
								'coupon_code'=>$refund_coupon_code,
								'created_by'=>$fbc_user_id,
								'created_at'=>time(),
								'ip'=>$_SERVER['REMOTE_ADDR']
							);
							$sales_rule_coupn_id=$this->WebshopModel->insertData('salesrule_coupon',$insert_coupon);
							
							if($sales_rule_coupn_id){
								$odr_update=array('refund_coupon_code'=>$refund_coupon_code,'updated_at'=>time());  
								$where_arr=array('return_order_id'=>$return_order_id);								
								$this->ReturnOrderModel->updateData('sales_order_return',$where_arr,$odr_update);
							}
							
						}

						$start_date = date('Y-m-d');
						$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
						$expiry_date = date('j F Y', strtotime($end_date));
						
						
						/*----------------Send Email to customer--------------------*/
						$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
						$amountwithcurrency=$shop_owner->currency_code." ".$coupon_amount;
						$webshop_details=$this->CommonModel->get_webshop_details($shop_id);
						$owner_email=$shop_owner->email;
						$templateId ='storecredit-voucher-returnorder';
						$to = $customer_email;
						$shop_name=$shop_owner->org_shop_name;
						$username = $shop_owner->owner_name;
						$site_logo = '';
						if(isset($webshop_details)){
						$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
						}
						$burl= base_url();
						$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
						$site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">
								<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
							</a>';
						$TempVars = array();
						$DynamicVars = array();
						
						$TempVars = array("##CUSTOMERNAME##" ,"##RETURNORDERID##","##VOUCHERCODE##","##VOUCHERAMOUNT##",'##WEBSHOPNAME##',"##VOUCHEREXPIRYDATE##");
						$DynamicVars   = array($customer_name,$ret_increment_id,$refund_coupon_code,$amountwithcurrency,$shop_name,$expiry_date);
						$CommonVars=array($site_logo, $shop_name);
						if(isset($templateId)){
							$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shop_id);
							if($emailSendStatusFlag==1){
								$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars,$ret_increment_id,$CommonVars);
							}
						}
						

						//Voucher End

						$insertdata=array(	
							'return_order_id'=>$return_order_id,
							'order_id'=>$order_id,
							'order_grandtotal_approved'=>$refund_strip_orderData->order_grandtotal_remaining,
							'order_grandtotal_approved_converted_currency'=>$currency,
							'order_grandtotal_approved_converted'=>$total_amount,
							'order_grandtotal_approved_stripe'=>$refund_strip_orderData->order_grandtotal_remaining,
							'order_grandtotal_approved_voucher'=>$coupon_amount,
							'order_grandtotal_remaining'=>0,
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('sales_order_return_stripe',$insertdata);

					}	

				}else{

					if($OrderData->grand_total >= $ReturnOrderData->order_grandtotal_approved){
						
						if($OrderData->currency_code_session != '' && $OrderData->default_currency_flag == 0){
							$currency = $OrderData->currency_code_session;
							$conversion_rate = $OrderData->currency_conversion_rate;
							$total_amount =$ReturnOrderData->order_grandtotal_approved*$conversion_rate;
							$Amount_in_cents = number_format($total_amount, 2, '.', '') * 100;
						}else{
							$currency ='';
							$total_amount =$ReturnOrderData->order_grandtotal_approved;
							$Amount_in_cents = number_format($ReturnOrderData->order_grandtotal_approved, 2, '.', '') * 100;
						}
						

						$stripe = new \Stripe\StripeClient($keyData);
						$stripe->refunds->create([
							'payment_intent'=> $salesPaymentData->payment_intent_id,
							'amount'=> $Amount_in_cents,
							//'currency'=>strtolower($currency)
						]);  

						$insertdata=array(	
							'return_order_id'=>$return_order_id,
							'order_id'=>$order_id,
							'order_grandtotal_approved'=>$ReturnOrderData->order_grandtotal_approved,
							'order_grandtotal_approved_converted_currency'=>$currency,
							'order_grandtotal_approved_converted'=>$total_amount,
							'order_grandtotal_approved_stripe'=>$ReturnOrderData->order_grandtotal_approved,
							'order_grandtotal_remaining'=>$OrderData->grand_total-$ReturnOrderData->order_grandtotal_approved,
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('sales_order_return_stripe',$insertdata);


					}else{

						if($OrderData->currency_code_session != '' && $OrderData->default_currency_flag == 0){
							$currency = $OrderData->currency_code_session;
							$conversion_rate = $OrderData->currency_conversion_rate;
							$total_amount =$OrderData->grand_total*$conversion_rate;
							$Amount_in_cents = number_format($total_amount, 2, '.', '') * 100;
						}else{
							$currency ='';
							$total_amount =$OrderData->grand_total;
							$Amount_in_cents = number_format($OrderData->grand_total, 2, '.', '') * 100;
						}
						
						//Stripe
						$stripe = new \Stripe\StripeClient($keyData);
						$stripe->refunds->create([
							'payment_intent'=> $salesPaymentData->payment_intent_id,
							'amount'=> $Amount_in_cents,
							//'currency'=>strtolower($currency)
						]);  

						//Stripe end

						//Voucher 

						$ret_increment_id=$ReturnOrderData->return_order_increment_id;
						$coupon_amount = $ReturnOrderData->order_grandtotal_approved-$OrderData->grand_total;
				
						$CustomerTypes=$this->WebshopOrdersModel->getMultiDataById('customers_type_master',array(),'');
						
						$ct_types='';
						if(isset($CustomerTypes) && count($CustomerTypes)>0){
							foreach($CustomerTypes as $val){
								$ct_types_arr[]=$val->id;
							}
							
							$ct_types=implode(',',$ct_types_arr);
							
						}
						
						$increment_id=$OrderData->increment_id;
						
						
						$discount_name = 'Refund Voucher for return order  #'.$increment_id;
						$description = 'Refund Voucher for return order #'.$increment_id;
						$start_date = date('Y-m-d');
						$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
						$apply_condition = 'discount_on_mincartval';
						$apply_type = 'by_fixed';
						
						$refund_coupon_code = 'REF-'.$increment_id.'-'.time();
						// $refund_coupon_code = 'REF-'.$increment_id;//old 27-09-2021
						
						$insertdata=array(	
							'name'=>$discount_name,
							'description'=>$description,
							'type'=>3,
							'coupon_type'=>1,
							'usage_per_customer'=>1,
							'usge_per_coupon'=>1,
							'start_date'=>$start_date,
							'end_date'=>$end_date, 
							'status'=>1,
							'apply_type'=>$apply_type,
							'apply_condition'=>$apply_condition,
							'discount_amount'=>$coupon_amount,
							'apply_to'=>$ct_types,
							'created_at'=>time(),
							'created_by'=>$fbc_user_id,
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('salesrule',$insertdata);
						if($sales_rule_id){
							$insert_coupon=array(	
								'rule_id'=>$sales_rule_id,
								'coupon_code'=>$refund_coupon_code,
								'created_by'=>$fbc_user_id,
								'created_at'=>time(),
								'ip'=>$_SERVER['REMOTE_ADDR']
							);
							$sales_rule_coupn_id=$this->WebshopModel->insertData('salesrule_coupon',$insert_coupon);
							
							if($sales_rule_coupn_id){
								$odr_update=array('refund_coupon_code'=>$refund_coupon_code,'updated_at'=>time());  
								$where_arr=array('return_order_id'=>$return_order_id);								
								$this->ReturnOrderModel->updateData('sales_order_return',$where_arr,$odr_update);
							}
							
						}

						$start_date = date('Y-m-d');
						$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
						$expiry_date = date('j F Y', strtotime($end_date));
						
						
						/*----------------Send Email to customer--------------------*/
						$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
						$amountwithcurrency=$shop_owner->currency_code." ".$coupon_amount;
						$webshop_details=$this->CommonModel->get_webshop_details($shop_id);
						$owner_email=$shop_owner->email;
						$templateId ='storecredit-voucher-returnorder';
						$to = $customer_email;
						$shop_name=$shop_owner->org_shop_name;
						$username = $shop_owner->owner_name;
						$site_logo = '';
						if(isset($webshop_details)){
						$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
						}
						$burl= base_url();
						$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
						$site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">
								<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
							</a>';
						$TempVars = array();
						$DynamicVars = array();
						
						$TempVars = array("##CUSTOMERNAME##" ,"##RETURNORDERID##","##VOUCHERCODE##","##VOUCHERAMOUNT##",'##WEBSHOPNAME##',"##VOUCHEREXPIRYDATE##");
						$DynamicVars   = array($customer_name,$ret_increment_id,$refund_coupon_code,$amountwithcurrency,$shop_name,$expiry_date);
						$CommonVars=array($site_logo, $shop_name);
						if(isset($templateId)){
							$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shop_id);
							if($emailSendStatusFlag==1){
								$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars,$ret_increment_id,$CommonVars);
							}
						}
						

						//Voucher End

						$insertdata=array(	
							'return_order_id'=>$return_order_id,
							'order_id'=>$order_id,
							'order_grandtotal_approved'=>$OrderData->grand_total,
							'order_grandtotal_approved_converted_currency'=>$currency,
							'order_grandtotal_approved_converted'=>$total_amount,
							'order_grandtotal_approved_stripe'=>$OrderData->grand_total,
							'order_grandtotal_approved_voucher'=>$coupon_amount,
							'order_grandtotal_remaining'=>0,							
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('sales_order_return_stripe',$insertdata);

					}
				}
			}


			//Paypal Refund

			if($ReturnOrderData->refund_payment_mode==4){

				$ShopData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'currency_code');
				if(isset($ShopData) && isset($ShopData->currency_code)){
					$shop_currency_code=$ShopData->currency_code;
				}else{
					$shop_currency_code='';	
				}
				$this->load->model('WebshopModel');
				$WebShopPaymentDetailsById =  $this->ReturnOrderModel->getSingleDataByID('webshop_payments',array('payment_id'=>$salesPaymentData->payment_method_id),'');
				$keyMainData=json_decode($WebShopPaymentDetailsById->gateway_details);
				
				$client_id=$keyMainData->client_id;
				$secret_key=$keyMainData->secret_key;
				$PaypalApiUrl=$keyMainData->paypal_api_url;

				$refund_strip_orderData = $this->ReturnOrderModel->getRefundStripeOrderIdLastInserted($order_id);

				if(isset($refund_strip_orderData)){

					//Changes to do
					if($refund_strip_orderData->order_grandtotal_remaining >= $ReturnOrderData->order_grandtotal_approved){				
						if($OrderData->currency_code_session != '' && $OrderData->default_currency_flag == 0){
							$currency = $OrderData->currency_code_session;
							$conversion_rate = $OrderData->currency_conversion_rate;
							$total_amount =$ReturnOrderData->order_grandtotal_approved*$conversion_rate;
							$Amount_in_cents = number_format($total_amount, 2, '.', '');
							
						}else{
							$currency =$shop_currency_code;
							$total_amount =$ReturnOrderData->order_grandtotal_approved;
							$Amount_in_cents = number_format($ReturnOrderData->order_grandtotal_approved, 2, '.', '');
						}
									
						$apiURL=$PaypalApiUrl.'/v1/payments/capture/'.$salesPaymentData->transaction_id.'/refund';

						$array = array(
						'amount'=>
							array('currency'=>$currency,'total'=>$Amount_in_cents)
						);
						$postField=json_encode($array);

						$ch=curl_init();
						$headers=array('Content-Type: application/json','Authorization: Basic '.base64_encode($client_id.':'.$secret_key));	
						curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
						curl_setopt($ch, CURLOPT_URL, $apiURL);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
						
						$response = curl_exec($ch);
						curl_close($ch);

						$insertdata_paypal=array(	
							'resource_id'=>$return_order_id,
							'type'=>1,
							'input'=>$postField,
							'output'=>$response,
						);
						
						$paypal_refund_details=$this->WebshopModel->insertData('paypal_refund_details',$insertdata_paypal);

						$insertdata=array(	
							'return_order_id'=>$return_order_id,
							'order_id'=>$order_id,
							'order_grandtotal_approved'=>$ReturnOrderData->order_grandtotal_approved,
							'order_grandtotal_approved_converted_currency'=>$currency,
							'order_grandtotal_approved_converted'=>$total_amount,
							'order_grandtotal_approved_stripe'=>$ReturnOrderData->order_grandtotal_approved,
							'order_grandtotal_remaining'=>$refund_strip_orderData->order_grandtotal_remaining-$ReturnOrderData->order_grandtotal_approved,							
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('sales_order_return_stripe',$insertdata);

					}else{

						if($OrderData->currency_code_session != '' && $OrderData->default_currency_flag == 0){	
							$currency = $OrderData->currency_code_session;
							$conversion_rate = $OrderData->currency_conversion_rate;
							$total_amount =$refund_strip_orderData->order_grandtotal_remaining*$conversion_rate;
							$Amount_in_cents = number_format($total_amount, 2, '.', '');
						}else{
							$currency =$shop_currency_code;
							$total_amount =$refund_strip_orderData->order_grandtotal_remaining;
							$Amount_in_cents = number_format($refund_strip_orderData->order_grandtotal_remaining, 2, '.', '');
						}
						
						//Paypal						
						$apiURL=$PaypalApiUrl.'/v1/payments/capture/'.$salesPaymentData->transaction_id.'/refund';

						$array = array(
						'amount'=>
							array('currency'=>$currency,'total'=>$Amount_in_cents)
						);
						$postField=json_encode($array);

						$ch=curl_init();
						$headers=array('Content-Type: application/json','Authorization: Basic '.base64_encode($client_id.':'.$secret_key));	
						curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
						curl_setopt($ch, CURLOPT_URL, $apiURL);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
						
						$response = curl_exec($ch);
						curl_close($ch);

						$insertdata_paypal=array(	
							'resource_id'=>$return_order_id,
							'type'=>1,
							'input'=>$postField,
							'output'=>$response,
						);
						
						$paypal_refund_details=$this->WebshopModel->insertData('paypal_refund_details',$insertdata_paypal);
						//paypal end

						//Voucher 

						$ret_increment_id=$ReturnOrderData->return_order_increment_id;
						$coupon_amount = $ReturnOrderData->order_grandtotal_approved-$refund_strip_orderData->order_grandtotal_remaining;
				
						$CustomerTypes=$this->WebshopOrdersModel->getMultiDataById('customers_type_master',array(),'');
						
						$ct_types='';
						if(isset($CustomerTypes) && count($CustomerTypes)>0){
							foreach($CustomerTypes as $val){
								$ct_types_arr[]=$val->id;
							}
							
							$ct_types=implode(',',$ct_types_arr);
							
						}
						
						$increment_id=$OrderData->increment_id;
						
						
						$discount_name = 'Refund Voucher for return order  #'.$increment_id;
						$description = 'Refund Voucher for return order #'.$increment_id;
						$start_date = date('Y-m-d');
						$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
						$apply_condition = 'discount_on_mincartval';
						$apply_type = 'by_fixed';
						
						$refund_coupon_code = 'REF-'.$increment_id.'-'.time();
					
						$insertdata=array(	
							'name'=>$discount_name,
							'description'=>$description,
							'type'=>3,
							'coupon_type'=>1,
							'usage_per_customer'=>1,
							'usge_per_coupon'=>1,
							'start_date'=>$start_date,
							'end_date'=>$end_date, 
							'status'=>1,
							'apply_type'=>$apply_type,
							'apply_condition'=>$apply_condition,
							'discount_amount'=>$coupon_amount,
							'apply_to'=>$ct_types,
							'created_at'=>time(),
							'created_by'=>$fbc_user_id,
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('salesrule',$insertdata);
						if($sales_rule_id){
							$insert_coupon=array(	
								'rule_id'=>$sales_rule_id,
								'coupon_code'=>$refund_coupon_code,
								'created_by'=>$fbc_user_id,
								'created_at'=>time(),
								'ip'=>$_SERVER['REMOTE_ADDR']
							);
							$sales_rule_coupn_id=$this->WebshopModel->insertData('salesrule_coupon',$insert_coupon);
							
							if($sales_rule_coupn_id){
								$odr_update=array('refund_coupon_code'=>$refund_coupon_code,'updated_at'=>time());  
								$where_arr=array('return_order_id'=>$return_order_id);								
								$this->ReturnOrderModel->updateData('sales_order_return',$where_arr,$odr_update);
							}
							
						}

						$start_date = date('Y-m-d');
						$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
						$expiry_date = date('j F Y', strtotime($end_date));
						
						
						/*----------------Send Email to customer--------------------*/
						$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
						$amountwithcurrency=$shop_owner->currency_code." ".$coupon_amount;
						$webshop_details=$this->CommonModel->get_webshop_details($shop_id);
						$owner_email=$shop_owner->email;
						$templateId ='storecredit-voucher-returnorder';
						$to = $customer_email;
						$shop_name=$shop_owner->org_shop_name;
						$username = $shop_owner->owner_name;
						$site_logo = '';
						if(isset($webshop_details)){
						$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
						}
						//$burl= base_url();
						//$webshop_address = "https://shop".$shop_id.".sh".trim($burl,"https:/");
						$shop_logo = getWebSiteLogo($shop_id,$shop_logo) ;
						$site_logo =  '<a href="'.base_url().'" style="color:#1E7EC8;">
								<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
							</a>';
						$TempVars = array();
						$DynamicVars = array();
						
						$TempVars = array("##CUSTOMERNAME##" ,"##RETURNORDERID##","##VOUCHERCODE##","##VOUCHERAMOUNT##",'##WEBSHOPNAME##',"##VOUCHEREXPIRYDATE##");
						$DynamicVars   = array($customer_name,$ret_increment_id,$refund_coupon_code,$amountwithcurrency,$shop_name,$expiry_date);
						$CommonVars=array($site_logo, $shop_name);
						if(isset($templateId)){
							$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shop_id);
							if($emailSendStatusFlag==1){
								$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars,$ret_increment_id,$CommonVars);
							}
						}
						

						//Voucher End

						$insertdata=array(	
							'return_order_id'=>$return_order_id,
							'order_id'=>$order_id,
							'order_grandtotal_approved'=>$refund_strip_orderData->order_grandtotal_remaining,
							'order_grandtotal_approved_converted_currency'=>$currency,
							'order_grandtotal_approved_converted'=>$total_amount,
							'order_grandtotal_approved_stripe'=>$refund_strip_orderData->order_grandtotal_remaining,
							'order_grandtotal_approved_voucher'=>$coupon_amount,
							'order_grandtotal_remaining'=>0,						
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('sales_order_return_stripe',$insertdata);

					}	

				}else{

					if($OrderData->grand_total >= $ReturnOrderData->order_grandtotal_approved){
						
						if($OrderData->currency_code_session != '' && $OrderData->default_currency_flag == 0){	
							$currency = $OrderData->currency_code_session;
							$conversion_rate = $OrderData->currency_conversion_rate;
							$total_amount =$ReturnOrderData->order_grandtotal_approved*$conversion_rate;
							$Amount_in_cents = number_format($total_amount, 2, '.', '');
						}else{
							$currency =$shop_currency_code;
							$total_amount =$ReturnOrderData->order_grandtotal_approved;
							$Amount_in_cents = number_format($ReturnOrderData->order_grandtotal_approved, 2, '.', '');
						}
						

						$apiURL=$PaypalApiUrl.'/v1/payments/capture/'.$salesPaymentData->transaction_id.'/refund';

						$array = array(
						'amount'=>
							array('currency'=>$currency,'total'=>$Amount_in_cents)
						);
						$postField=json_encode($array);

						$ch=curl_init();
						$headers=array('Content-Type: application/json','Authorization: Basic '.base64_encode($client_id.':'.$secret_key));	
						curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
						curl_setopt($ch, CURLOPT_URL, $apiURL);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
						
						$response = curl_exec($ch);
						curl_close($ch);

						$insertdata_paypal=array(	
							'resource_id'=>$return_order_id,
							'type'=>1,
							'input'=>$postField,
							'output'=>$response,
						);
						
						$paypal_refund_details=$this->WebshopModel->insertData('paypal_refund_details',$insertdata_paypal);

						$insertdata=array(	
							'return_order_id'=>$return_order_id,
							'order_id'=>$order_id,
							'order_grandtotal_approved'=>$ReturnOrderData->order_grandtotal_approved,
							'order_grandtotal_approved_converted_currency'=>$currency,
							'order_grandtotal_approved_converted'=>$total_amount,
							'order_grandtotal_approved_stripe'=>$ReturnOrderData->order_grandtotal_approved,				
							'order_grandtotal_remaining'=>$OrderData->grand_total-$ReturnOrderData->order_grandtotal_approved,		
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('sales_order_return_stripe',$insertdata);


					}else{

						if($OrderData->currency_code_session != '' && $OrderData->default_currency_flag == 0){		
							$currency = $OrderData->currency_code_session;
							$conversion_rate = $OrderData->currency_conversion_rate;
							$total_amount =$OrderData->grand_total*$conversion_rate;
							$Amount_in_cents =number_format($total_amount, 2, '.', '');
						}else{
							$currency =$shop_currency_code;
							$total_amount =$OrderData->grand_total;
							$Amount_in_cents =number_format($OrderData->grand_total, 2, '.', '');
						}
						
						//Paypal
						$apiURL=$PaypalApiUrl.'/v1/payments/capture/'.$salesPaymentData->transaction_id.'/refund';

						$array = array(
						'amount'=>
							array('currency'=>$currency,'total'=>$Amount_in_cents)
						);
						$postField=json_encode($array);

						$ch=curl_init();
						$headers=array('Content-Type: application/json','Authorization: Basic '.base64_encode($client_id.':'.$secret_key));	
						curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
						curl_setopt($ch, CURLOPT_URL, $apiURL);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
						
						$response = curl_exec($ch);
						curl_close($ch);

						$insertdata_paypal=array(	
							'resource_id'=>$return_order_id,
							'type'=>1,
							'input'=>$postField,
							'output'=>$response,
						);
						
						$paypal_refund_details=$this->WebshopModel->insertData('paypal_refund_details',$insertdata_paypal);
						//Paypal end

						//Voucher 

						$ret_increment_id=$ReturnOrderData->return_order_increment_id;
						$coupon_amount = $ReturnOrderData->order_grandtotal_approved-$OrderData->grand_total;
				
						$CustomerTypes=$this->WebshopOrdersModel->getMultiDataById('customers_type_master',array(),'');
						
						$ct_types='';
						if(isset($CustomerTypes) && count($CustomerTypes)>0){
							foreach($CustomerTypes as $val){
								$ct_types_arr[]=$val->id;
							}
							
							$ct_types=implode(',',$ct_types_arr);
							
						}
						
						$increment_id=$OrderData->increment_id;
						
						
						$discount_name = 'Refund Voucher for return order  #'.$increment_id;
						$description = 'Refund Voucher for return order #'.$increment_id;
						$start_date = date('Y-m-d');
						$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
						$apply_condition = 'discount_on_mincartval';
						$apply_type = 'by_fixed';
						
						$refund_coupon_code = 'REF-'.$increment_id.'-'.time();
						
						$insertdata=array(	
							'name'=>$discount_name,
							'description'=>$description,
							'type'=>3,
							'coupon_type'=>1,
							'usage_per_customer'=>1,
							'usge_per_coupon'=>1,
							'start_date'=>$start_date,
							'end_date'=>$end_date, 
							'status'=>1,
							'apply_type'=>$apply_type,
							'apply_condition'=>$apply_condition,
							'discount_amount'=>$coupon_amount,
							'apply_to'=>$ct_types,
							'created_at'=>time(),
							'created_by'=>$fbc_user_id,
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('salesrule',$insertdata);
						if($sales_rule_id){
							$insert_coupon=array(	
								'rule_id'=>$sales_rule_id,
								'coupon_code'=>$refund_coupon_code,
								'created_by'=>$fbc_user_id,
								'created_at'=>time(),
								'ip'=>$_SERVER['REMOTE_ADDR']
							);
							$sales_rule_coupn_id=$this->WebshopModel->insertData('salesrule_coupon',$insert_coupon);
							
							if($sales_rule_coupn_id){
								$odr_update=array('refund_coupon_code'=>$refund_coupon_code,'updated_at'=>time());  
								$where_arr=array('return_order_id'=>$return_order_id);								
								$this->ReturnOrderModel->updateData('sales_order_return',$where_arr,$odr_update);
							}
							
						}

						$start_date = date('Y-m-d');
						$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
						$expiry_date = date('j F Y', strtotime($end_date));
						
						
						/*----------------Send Email to customer--------------------*/
						$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
						$amountwithcurrency=$shop_owner->currency_code." ".$coupon_amount;
						$webshop_details=$this->CommonModel->get_webshop_details($shop_id);
						$owner_email=$shop_owner->email;
						$templateId ='storecredit-voucher-returnorder';
						$to = $customer_email;
						$shop_name=$shop_owner->org_shop_name;
						$username = $shop_owner->owner_name;
						$site_logo = '';
						if(isset($webshop_details)){
						$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
						}
						//$burl= base_url();
						//$webshop_address = "https://shop".$shop_id.".sh".trim($burl,"https:/");
						$shop_logo = getWebSiteLogo($shop_id,$shop_logo);  
						$site_logo =  '<a href="'.base_url().'" style="color:#1E7EC8;">
								<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
							</a>';
						$TempVars = array();
						$DynamicVars = array();
						
						$TempVars = array("##CUSTOMERNAME##" ,"##RETURNORDERID##","##VOUCHERCODE##","##VOUCHERAMOUNT##",'##WEBSHOPNAME##',"##VOUCHEREXPIRYDATE##");
						$DynamicVars   = array($customer_name,$ret_increment_id,$refund_coupon_code,$amountwithcurrency,$shop_name,$expiry_date);
						$CommonVars=array($site_logo, $shop_name);
						if(isset($templateId)){
							$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shop_id);
							if($emailSendStatusFlag==1){
								$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars,$ret_increment_id,$CommonVars);
							}
						}
						

						//Voucher End
						$insertdata=array(	
							'return_order_id'=>$return_order_id,
							'order_id'=>$order_id,
							'order_grandtotal_approved'=>$OrderData->grand_total,
							'order_grandtotal_approved_converted_currency'=>$currency,
							'order_grandtotal_approved_converted'=>$total_amount,
							'order_grandtotal_approved_stripe'=>$OrderData->grand_total,
							'order_grandtotal_approved_voucher'=>$coupon_amount,
							'order_grandtotal_remaining'=>0,						
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_id=$this->WebshopModel->insertData('sales_order_return_stripe',$insertdata);

					}

				}
			}

			$arrResponse  = array('status' =>200 ,'message'=>'Order refund confirmed successfully.');
			echo json_encode($arrResponse);exit;

		}else{
			$arrResponse  = array('status' =>403 ,'message'=>'Something went wrong.');
			echo json_encode($arrResponse);exit;

		}
	}

	public function return_order_print()
	{
		$data['PageTitle']='Webshop - Returns Order-Print';

		$return_order_id =$this->uri->segment(4);
		if(isset($return_order_id) && $return_order_id>0){

			$shop_id		=	$this->session->userdata('ShopID');
			$data['ReturnOrderData']=$ReturnOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return',array('return_order_id'=>$return_order_id),'');

			$order_id=$ReturnOrderData->order_id;
			$data['OrderData']=$OrderData =$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');

			if(empty($ReturnOrderData)){
				redirect('/webshop/orders/refund-request');
			}

			$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);

			$data['OrderItems']=$OrderItems=$this->ReturnOrderModel->getReturnOrderItems($return_order_id);

			$data['ShippingAddress']=$ShippingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>2),'');
			$data['BillingAddress']=$BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>1),'');
			$data['SplitOrderIds']=$this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->order_id);

			$data['OrderPaymentDetail']=$OrderPayment=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->order_id),'');
			// echo "<pre>";

			// print_r($data['currency_code']);
			// print_r($data['ReturnOrderData']);
			// print_r($data['OrderData']);
			// print_r($data['OrderItems']);
			// print_r($data['ShippingAddress']);
			// print_r($data['BillingAddress']);
			// print_r($data['SplitOrderIds']);
			// print_r($data['OrderPaymentDetail']);
			// die();

			$this->load->view('webshop/returns/return_order_print',$data);
		}else{
			redirect('/webshop/orders/refund-request');
		}
	}

	function rejectRefundRequest(){
		if(isset($_POST['return_order_id']) && $_POST['return_order_id']!='')
		{
			$return_order_id=$_POST['return_order_id'];

			$nameUpdate =array(
				'refund_status'	=> 2,'updated_at'=>time(),'refund_approve_reject_date'=>time()
			);

			//rejected

			$wher_arr=array('return_order_id'=>$return_order_id);
			$this->ReturnOrderModel->updateData('sales_order_return', $wher_arr,$nameUpdate);

			$arrResponse  = array('status' =>200 ,'message'=>'Refund rejected successfully.');
			echo json_encode($arrResponse);exit;

		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong!');
			echo json_encode($arrResponse);exit;
		}
	}

	/*start cancel order*/
	public function requestedEscalations()
	{
	if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/refunds',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab='escalations_request';
		$data['current_tab']=$current_tab;
		$data['PageTitle']='Webshop - Request Escalations';
		$data['side_menu']='webshop';
		$this->load->view('webshop/escalations/escalations_request',$data);
	}

	public function completedEscalations()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/refunds',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab='escalations_completed';
		$data['current_tab']=$current_tab;
		$data['PageTitle']='Webshop - Complated Escalations';
		$data['side_menu']='webshop';
		$this->load->view('webshop/escalations/escalations_complated',$data);
	}


	function requestedEscalationsOrder()
	{

		//$current_tab ='';
		 $current_tab =$_POST['current_tab'];

		$shop_id		=	$this->session->userdata('ShopID');

        $OrderData = $this->ReturnOrderModel->get_datatables_escalations_request_orders($current_tab);
        $data = array();
		$no = $_POST['start'];



		foreach ($OrderData as $readData) {
			$no++;
			$row = array();
			$qty='';

			$order_url='';

			$order_url=base_url().'webshop/escalations-request-order/detail/'.$readData->id;
			$print_url=base_url().'webshop/escalations-order/print/'.$readData->id;

			$payment_type=$this->CommonModel->getPaymentTypeLabel($readData->payment_type);


			$status_label='';
			$refundstatus_label='';
			$status_label=$this->CommonModel->getReturnOrderStatusLabel($readData->refund_status);
			$refundstatus_label=$this->CommonModel->getRefundOrderStatusLabel($readData->refund_status);

			$shipment_type_label=$readData->payment_method_name;

			$row[]=$readData->esc_order_id;
			$row[]=date(SIS_DATE_FM_WT,$readData->order_created_at);
			$row[]=$readData->customer_name;
			//$row[]= '-';
			if($readData->created_at >0){$cdate=date(SIS_DATE_FM,$readData->created_at);}else{$cdate='';}
			$row[]=$cdate;
			$row[]=$status_label;
			$row[]=$refundstatus_label;
			$row[]=$payment_type;
			// $row[]='<a class="link-purple " target="_blank" href="'.$print_url.'">Print</a>';//old
			$row[]='<a class="link-purple" href="'.$order_url.'">View</a>';

			$data[] = $row;

		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->ReturnOrderModel->count_all_refund_request_orders($current_tab),
						"recordsFiltered" => $this->ReturnOrderModel->count_filtered_refund_request_orders($current_tab),
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);
		exit;
	}

	// request details
	function escalationsRequestOrderDetail(){
	 if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/refunds',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab=$this->uri->segment(2);
		$escalations_order_id=$this->uri->segment(4);
		if(isset($escalations_order_id) && $escalations_order_id>0){
			$data['PageTitle']='Webshop - Request Escalations Orders Detail';
			$data['side_menu']='webshop';
			$data['current_tab']=$current_tab;

			$shop_id=$this->session->userdata('ShopID');

			//echo $this->WebshopOrdersModel->generate_new_transaction_id().'====='; ReturnOrderData
			$data['EscalationsOrderData']=$EscalationsOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_escalations',array('id'=>$escalations_order_id),'');

			$order_id=$EscalationsOrderData->order_id;
			$data['OrderData']=$OrderData=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');
			if(empty($EscalationsOrderData)){
				redirect('/webshop/escalations/refund-request');
			}

			if(isset($OrderData->main_parent_id) && $OrderData->main_parent_id!=0){
				$data['ParentOrder']=$ParentOrder=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$OrderData->main_parent_id),'');
			}

			$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);

			$data['OrderItems']=$OrderItems=$this->ReturnOrderModel->getEscalationsOrderItems($order_id);

			$data['ShippingAddress']=$ShippingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>2),'');
			$data['BillingAddress']=$BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>1),'');
			$data['SplitOrderIds']=$this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->order_id);

			if(isset($OrderData->main_parent_id) && $OrderData->main_parent_id!=0){
				$data['OrderPaymentDetail']=$OrderPayment=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->main_parent_id),'');
			}else{
				$data['OrderPaymentDetail']=$OrderPayment=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->order_id),'');
			}

			// cancel order data
			$data['order_grandtotal_approved'] = 0;
			$data['order_grandtotal_approved'] = $OrderData->subtotal + $OrderData->payment_final_charge;
			$data['none_cancel']='';
			$data['store_cancel']='';
			$data['offline_cancel']='';
			//echo $OrderPaymentDetail->payment_method;
			if(isset($OrderPayment->payment_method) && !empty($OrderPayment->payment_method)){
				//echo $OrderPaymentDetail->payment_method;
				$payment_method_cancel=$OrderPayment->payment_method;
				if($payment_method_cancel=='cod'){
					$data['none_cancel']='checked';
					$data['store_cancel']='disabled';
					$data['offline_cancel']='disabled';
					$data['strip_cancel']='disabled';
				}elseif($payment_method_cancel=='via_transfer'){
					$data['none_cancel']='checked';
					$data['store_cancel']='';
					$data['offline_cancel']='';
					$data['strip_cancel']='';
				}elseif($payment_method_cancel=='stripe_payment'){
					
					$data['strip_cancel']='checked';
					$data['none_cancel']='';
					$data['store_cancel']='';
					$data['offline_cancel']='';
				}else{
					$data['none_cancel']='';
					$data['store_cancel']='checked';
					$data['offline_cancel']='';
					$data['strip_cancel']='';
				}


				if($payment_method_cancel == 'via_transfer' || $payment_method_cancel == 'cod' ) {
					if($OrderData->voucher_code != '' && $OrderData->voucher_amount > 0.00 ) {
						$order_grandtotal_approved = $OrderData->voucher_amount;
						$data['order_grandtotal_approved']=$order_grandtotal_approved;
						if($order_grandtotal_approved > 0 ){
							$data['none_cancel']='';
							$data['store_cancel']='checked';
							$data['offline_cancel']='';
						}
					}else{
						$data['order_grandtotal_approved'] = 0;
					}
				}

				// changes after removed popup cancel_refund_type none, store credit, transfer

			}

			$online_stripe_payment_refund=$this->WebshopOrdersModel->getSingleDataByID('custom_variables',array('identifier'=>'online_stripe_payment_refund'),'');
			if (isset($online_stripe_payment_refund) && $online_stripe_payment_refund->value=='yes') {
				$data['strip_cancel_enable']="yes";
			}else{
				$data['strip_cancel_enable']="no";
			}

			//echo $OrderData->voucher_code;exit();

			$this->load->view('webshop/escalations/escalations-order-detail',$data);
		}else{
			redirect('/webshop/escalations/escalations-request');
		}
	}

	//compalted details
	function escalationsCompleteOrderDetail(){
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/refunds',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$current_tab=$this->uri->segment(2);
		$return_order_id=$this->uri->segment(4);
		if(isset($return_order_id) && $return_order_id>0){
			$data['PageTitle']='Webshop - Refund Order Detail';
			$data['side_menu']='webshop';
			$data['current_tab']=$current_tab;

			$shop_id		=	$this->session->userdata('ShopID');

			//echo $this->WebshopOrdersModel->generate_new_transaction_id().'=====';
			$data['ReturnOrderData']=$ReturnOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_return',array('return_order_id'=>$return_order_id),'');

			$order_id=$ReturnOrderData->order_id;
			$data['OrderData']=$OrderData=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');

			if(empty($ReturnOrderData)){
				redirect('/webshop/orders/refund-request');
			}

			$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);

			$data['OrderItems']=$OrderItems=$this->ReturnOrderModel->getReturnOrderItems($return_order_id);

			$data['ShippingAddress']=$ShippingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>2),'');
			$data['BillingAddress']=$BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$OrderData->order_id,'address_type'=>1),'');
			$data['SplitOrderIds']=$this->WebshopOrdersModel->getSplitChildOrderIds($OrderData->order_id);

			$data['OrderPaymentDetail']=$OrderPayment=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->order_id),'');





			$this->load->view('webshop/returns/refund-order-detail',$data);
		}else{
			redirect('/webshop/orders/refund-request');
		}
	}

	// ajax popup
	function openConfirmEscalationsPopup()
	{
		if(isset($_POST['escalations_order_id']) && isset($_POST['escalations_order_id'])){

			$data['escalations_order_id']=$escalations_order_id=$_POST['escalations_order_id'];

			$data['EscalationsOrderData']=$EscalationsOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_escalations',array('id'=>$escalations_order_id),'');

			$View = $this->load->view('webshop/escalations/escalations-confirmation-popup', $data, true);
			$this->output->set_output($View);
		}else{
			echo "error";exit;
		}
	}


	function rejectEscalationsRequest(){
		if(isset($_POST['escalations_order_id']) && $_POST['escalations_order_id']!='')
		{
			$escalations_order_id=$_POST['escalations_order_id'];

			$nameUpdate =array(
				'refund_status'	=> 2,'updated_at'=>time(),'refund_approve_reject_date'=>time()
			);

			//rejected

			$wher_arr=array('id'=>$escalations_order_id);
			$this->ReturnOrderModel->updateData('sales_order_escalations', $wher_arr,$nameUpdate);

			$arrResponse  = array('status' =>200 ,'message'=>'Refund rejected successfully.');
			echo json_encode($arrResponse);exit;

		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong!');
			echo json_encode($arrResponse);exit;
		}
	}

	// cancel order confirm
	function escalationsorderconfirm(){

		if(isset($_POST['escalations_order_id']) && $_POST['escalations_order_id']!=''){
			
			$data['escalations_order_id']=$escalations_order_id=$_POST['escalations_order_id'];
			
			$data['EscalationsOrderData']=$EscalationsOrderData=$this->ReturnOrderModel->getSingleDataByID('sales_order_escalations',array('id'=>$escalations_order_id),'');
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$ShopData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'currency_code');
			if(isset($ShopData) && isset($ShopData->currency_code)){
				$shop_currency_code=$ShopData->currency_code;
			}else{
				$shop_currency_code='';	
			}

			$order_id=$EscalationsOrderData->order_id;
			$data['OrderData']=$OrderData=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$order_id),'');
			
			$customer_email=$OrderData->customer_email;
			$customer_name=$OrderData->customer_firstname.' '.$OrderData->customer_lastname;
			$OrderData_increment_id=$OrderData->increment_id;
			
			$order_grandtotal_approved_online=$EscalationsOrderData->order_grandtotal_approved_online;
			if($EscalationsOrderData->order_grandtotal_approved > 0.00){
				if(isset($EscalationsOrderData->cancel_refund_type) &&  $EscalationsOrderData->cancel_refund_type==4 || $EscalationsOrderData->cancel_refund_type==3){
				  if(isset($EscalationsOrderData) && $EscalationsOrderData->order_type==1){
				  		$ParentOrder=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$OrderData->main_parent_id),'');

				  		$cancelOrderPaymentMethod='';
						$order_id=$EscalationsOrderData->order_id;
						$sales_order_payment_details =  $this->ReturnOrderModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->main_parent_id),'');
						$WebShopPaymentDetailsById =  $this->ReturnOrderModel->getSingleDataByID('webshop_payments',array('payment_id'=>$sales_order_payment_details->payment_method_id),'');
						$cancelOrderPaymentMethod=$sales_order_payment_details->payment_method;

						if($cancelOrderPaymentMethod=='paypal_express'){
							$keyMainData=json_decode($WebShopPaymentDetailsById->gateway_details);
							$PaypalclientId=$keyMainData->client_id;
							$Paypalsecret=$keyMainData->secret_key;
							$PaypalApiUrl=$keyMainData->paypal_api_url;
						}elseif($cancelOrderPaymentMethod=='stripe_payment'){
								$keyMainData=json_decode($WebShopPaymentDetailsById->gateway_details);
								$keyData=$keyMainData->key;
						}

						//deleted split order
						if ($EscalationsOrderData->cancel_refund_type==3) {

							if($order_grandtotal_approved_online > 0 || $order_grandtotal_approved_online> 0.00){

								// if(isset($ParentOrder->currency_conversion_rate) && $ParentOrder->currency_conversion_rate !=''){
								if($ParentOrder->currency_code_session != '' && $ParentOrder->default_currency_flag == 0){
									$currency = $ParentOrder->currency_code_session;
									$conversion_rate = $ParentOrder->currency_conversion_rate;
									$total_amount =$order_grandtotal_approved_online*$conversion_rate;
									$Amount_in_stripe_refund = number_format($total_amount, 2, '.', '') * 100;
								}else{
									$Amount_in_stripe_refund = number_format($order_grandtotal_approved_online, 2, '.', '') * 100;
								}
								if($cancelOrderPaymentMethod=='stripe_payment'){
									$stripe = new \Stripe\StripeClient($keyData);
									$s_got_data=$stripe->refunds->create([
										'payment_intent'=> $sales_order_payment_details->payment_intent_id,
										'amount'=> $Amount_in_stripe_refund,
										//'currency'=>strtolower($currency)
									]);
								}elseif ($cancelOrderPaymentMethod=='paypal_express'){
									if($ParentOrder->currency_code_session != '' && $ParentOrder->default_currency_flag == 0){
										$shop_currency_code = $ParentOrder->currency_code_session;
										$conversion_rate = $ParentOrder->currency_conversion_rate;
										$refund_Amount =$order_grandtotal_approved_online*$conversion_rate;

									}else{
										$shop_currency_code = $shop_currency_code;
										$refund_Amount=$EscalationsOrderData->order_grandtotal_approved_online;
									}

									$apiPaypalData=array();
									$transaction_id=$sales_order_payment_details->transaction_id;
									$apiPaypalData=array('resource_id'=>$escalations_order_id,'type'=>3,'transaction_id'=>$transaction_id,'refund_Amount'=>$refund_Amount,'order_type'=>$EscalationsOrderData->order_type,'currency_code'=>$shop_currency_code,'order_id'=>$OrderData_increment_id,'pay_pal_api_url'=>$PaypalApiUrl);
									$responseRefundApi=$this->PayPalRefundApi($apiPaypalData,$PaypalclientId,$Paypalsecret);
								}
							}
							
						}elseif($EscalationsOrderData->cancel_refund_type==4){

							if($order_grandtotal_approved_online > 0 || $order_grandtotal_approved_online> 0.00){

								if($ParentOrder->currency_code_session != '' && $ParentOrder->default_currency_flag == 0){
									$currency = $ParentOrder->currency_code_session;
									$conversion_rate = $ParentOrder->currency_conversion_rate;
									$total_amount =$order_grandtotal_approved_online*$conversion_rate;
									$Amount_in_stripe_refund = number_format($total_amount, 2, '.', '') * 100;
								}else{
									$Amount_in_stripe_refund = number_format($order_grandtotal_approved_online, 2, '.', '') * 100;
								}
								if($cancelOrderPaymentMethod=='stripe_payment'){
									$stripe = new \Stripe\StripeClient($keyData);
									$s_got_data=$stripe->refunds->create([
										'payment_intent'=> $sales_order_payment_details->payment_intent_id,
										'amount'=> $Amount_in_stripe_refund,
										//'currency'=>strtolower($currency)
									]);  
								}elseif ($cancelOrderPaymentMethod=='paypal_express'){
									if($ParentOrder->currency_code_session != '' && $ParentOrder->default_currency_flag == 0){
										$shop_currency_code = $ParentOrder->currency_code_session;
										$conversion_rate = $ParentOrder->currency_conversion_rate;
										$refund_Amount =$order_grandtotal_approved_online*$conversion_rate;
									}else{
										$shop_currency_code = $shop_currency_code;
										$refund_Amount=$order_grandtotal_approved_online;
									}
									$apiPaypalData=array();
									$transaction_id=$sales_order_payment_details->transaction_id;
									$apiPaypalData=array('resource_id'=>$escalations_order_id,'type'=>3,'transaction_id'=>$transaction_id,'refund_Amount'=>$refund_Amount,'order_type'=>$EscalationsOrderData->order_type,'currency_code'=>$shop_currency_code,'order_id'=>$OrderData_increment_id,'pay_pal_api_url'=>$PaypalApiUrl);
									$responseRefundApi=$this->PayPalRefundApi($apiPaypalData,$PaypalclientId,$Paypalsecret);
								}
							}
							
							$this->load->model('WebshopModel');
							$ret_increment_id=$EscalationsOrderData->esc_order_id;
							$CustomerTypes=$this->WebshopOrdersModel->getMultiDataById('customers_type_master',array(),'');
							
							$ct_types='';
							if(isset($CustomerTypes) && count($CustomerTypes)>0){
								foreach($CustomerTypes as $val){
									$ct_types_arr[]=$val->id;
								}
								
								$ct_types=implode(',',$ct_types_arr);
								
							}
						
							$increment_id=$OrderData->increment_id;
							$coupon_amount=$EscalationsOrderData->order_grandtotal_approved_online_voucher;
							
							$discount_name = 'Refund Voucher for return order  #'.$increment_id;
							$description = 'Refund Voucher for return order #'.$increment_id;
							$start_date = date('Y-m-d');
							$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
							$apply_condition = 'discount_on_mincartval';
							$apply_type = 'by_fixed';
							
							$disc_status=1;
							
							$refund_coupon_code = 'REFESC-'.$increment_id.'-'.time();
							// $refund_coupon_code = $ret_increment_id;//old
							
							$insertdata=array(	
								'name'=>$discount_name,
								'description'=>$description,
								'type'=>3,
								'coupon_type'=>1,
								'usage_per_customer'=>1,
								'usge_per_coupon'=>1,
								'start_date'=>$start_date,
								'end_date'=>$end_date, 
								'status'=>$disc_status,
								'apply_type'=>$apply_type,
								'apply_condition'=>$apply_condition,
								'discount_amount'=>$coupon_amount,
								'apply_to'=>$ct_types,
								'created_at'=>time(),
								'created_by'=>$fbc_user_id,
								'ip'=>$_SERVER['REMOTE_ADDR']
							);
							$sales_rule_id=$this->WebshopModel->insertData('salesrule',$insertdata);
							if($sales_rule_id){
								$insert_coupon=array(	
									'rule_id'=>$sales_rule_id,
									'coupon_code'=>$refund_coupon_code,
									'email_address'=>$customer_email,
									'email_sent'=>1,
									'created_by'=>$fbc_user_id,
									'created_at'=>time(),
									'ip'=>$_SERVER['REMOTE_ADDR']
								);
								$sales_rule_coupn_id=$this->WebshopModel->insertData('salesrule_coupon',$insert_coupon);
								
								if($sales_rule_coupn_id){
									$odr_update=array('refund_coupon_code'=>$refund_coupon_code,'updated_at'=>time());  
									$where_arr=array('id'=>$escalations_order_id);								
									$this->ReturnOrderModel->updateData('sales_order_escalations',$where_arr,$odr_update);
								}
								
							}

							$start_date = date('Y-m-d');
							$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
							$expiry_date = date('j F Y', strtotime($end_date));
							
							
							/*----------------Send Email to customer--------------------*/

							$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
							$amountwithcurrency=$shop_owner->currency_code." ".$coupon_amount;
							$webshop_details=$this->CommonModel->get_webshop_details($shop_id);
							$owner_email=$shop_owner->email;
							$templateId ='storecredit-voucher-returnorder';
							$to = $customer_email;
							$shop_name=$shop_owner->org_shop_name;
							$username = $shop_owner->owner_name;
							$site_logo = '';
							if(isset($webshop_details)){
							 $shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
							}
							$burl= base_url();
							$shop_logo = get_s3_url($shop_logo ?? '', $shop_id); 
							$site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">
									<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
								</a>';
							$TempVars = array();
							$DynamicVars = array();
							
							$TempVars = array("##CUSTOMERNAME##" ,"##RETURNORDERID##","##VOUCHERCODE##","##VOUCHERAMOUNT##",'##WEBSHOPNAME##',"##VOUCHEREXPIRYDATE##");
							$DynamicVars   = array($customer_name,$ret_increment_id,$refund_coupon_code,$amountwithcurrency,$shop_name,$expiry_date);
							$CommonVars=array($site_logo, $shop_name);
							if(isset($templateId)){
								$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shop_id);
								if($emailSendStatusFlag==1){
									$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars,$ret_increment_id,$CommonVars);
								}
							}

						}

				  	}else{
				  		// cancel order
						$cancelOrderPaymentMethod='';
						$order_id=$EscalationsOrderData->order_id;
						$sales_order_payment_details =  $this->ReturnOrderModel->getSingleDataByID('sales_order_payment',array('order_id'=>$order_id),'');
						$WebShopPaymentDetailsById =  $this->ReturnOrderModel->getSingleDataByID('webshop_payments',array('payment_id'=>$sales_order_payment_details->payment_method_id),'');

						$cancelOrderPaymentMethod=$sales_order_payment_details->payment_method;

						if($sales_order_payment_details->payment_method=='paypal_express'){
							$keyMainData=json_decode($WebShopPaymentDetailsById->gateway_details);
							$PaypalclientId=$keyMainData->client_id;
							$Paypalsecret=$keyMainData->secret_key;
							$PaypalApiUrl=$keyMainData->paypal_api_url;
						}elseif ($sales_order_payment_details->payment_method=='stripe_payment') {
								$keyMainData=json_decode($WebShopPaymentDetailsById->gateway_details);
								$keyData=$keyMainData->key;
						}

						if ($EscalationsOrderData->cancel_refund_type==3) {
							if($cancelOrderPaymentMethod=='stripe_payment'){
								$stripe = new \Stripe\StripeClient($keyData);
								$s_got_data=$stripe->refunds->create([
									'payment_intent'=> $sales_order_payment_details->payment_intent_id							
								]); 
							}elseif($cancelOrderPaymentMethod=='paypal_express'){
								if($ParentOrder->currency_code_session != '' && $ParentOrder->default_currency_flag == 0){
										$shop_currency_code = $ParentOrder->currency_code_session;
										$conversion_rate = $ParentOrder->currency_conversion_rate;
										$refund_Amount =$order_grandtotal_approved_online*$conversion_rate;
								}else{
									$shop_currency_code = $shop_currency_code;
									$refund_Amount =$order_grandtotal_approved_online;
								}

								$apiPaypalData=array();
								$transaction_id=$sales_order_payment_details->transaction_id;
								$apiPaypalData=array('resource_id'=>$escalations_order_id,'type'=>2,'transaction_id'=>$transaction_id,'refund_Amount'=>$refund_Amount,'order_type'=>$EscalationsOrderData->order_type,'currency_code'=>$shop_currency_code,'order_id'=>$OrderData_increment_id,'pay_pal_api_url'=>$PaypalApiUrl);
								$responseRefundApi=$this->PayPalRefundApi($apiPaypalData,$PaypalclientId,$Paypalsecret);
								
							}
						}elseif($EscalationsOrderData->cancel_refund_type==4){
							if($cancelOrderPaymentMethod=='stripe_payment'){

								$stripe = new \Stripe\StripeClient($keyData);
								$s_got_data=$stripe->refunds->create([
									'payment_intent'=> $sales_order_payment_details->payment_intent_id							
								]);  
							}elseif ($cancelOrderPaymentMethod=='paypal_express'){
								if($ParentOrder->currency_code_session != '' && $ParentOrder->default_currency_flag == 0){
									$shop_currency_code = $ParentOrder->currency_code_session;
									$conversion_rate = $ParentOrder->currency_conversion_rate;
									$refund_Amount =$order_grandtotal_approved_online*$conversion_rate;
								}else{
									$shop_currency_code = $shop_currency_code;
									$refund_Amount =$order_grandtotal_approved_online;
								}
								$apiPaypalData=array();
								$transaction_id=$sales_order_payment_details->transaction_id;
								$apiPaypalData=array('resource_id'=>$escalations_order_id,'type'=>2,'transaction_id'=>$transaction_id,'refund_Amount'=>$refund_Amount,'order_type'=>$EscalationsOrderData->order_type,'currency_code'=>$shop_currency_code,'order_id'=>$OrderData_increment_id,'pay_pal_api_url'=>$PaypalApiUrl);
								$responseRefundApi=$this->PayPalRefundApi($apiPaypalData,$PaypalclientId,$Paypalsecret);
							}

							$this->load->model('WebshopModel');
							$ret_increment_id=$EscalationsOrderData->esc_order_id;
							$CustomerTypes=$this->WebshopOrdersModel->getMultiDataById('customers_type_master',array(),'');
							
							$ct_types='';
							if(isset($CustomerTypes) && count($CustomerTypes)>0){
								foreach($CustomerTypes as $val){
									$ct_types_arr[]=$val->id;
								}
								
								$ct_types=implode(',',$ct_types_arr);
								
							}
						
							$increment_id=$OrderData->increment_id;
							$coupon_amount=$EscalationsOrderData->order_grandtotal_approved_online_voucher;
							
							$discount_name = 'Refund Voucher for return order  #'.$increment_id;
							$description = 'Refund Voucher for return order #'.$increment_id;
							$start_date = date('Y-m-d');
							$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
							$apply_condition = 'discount_on_mincartval';
							$apply_type = 'by_fixed';
							
							$disc_status=1;
							
							$refund_coupon_code = 'REFESC-'.$increment_id.'-'.time();
							// $refund_coupon_code = $ret_increment_id;//old
							
							$insertdata=array(	
								'name'=>$discount_name,
								'description'=>$description,
								'type'=>3,
								'coupon_type'=>1,
								'usage_per_customer'=>1,
								'usge_per_coupon'=>1,
								'start_date'=>$start_date,
								'end_date'=>$end_date, 
								'status'=>$disc_status,
								'apply_type'=>$apply_type,
								'apply_condition'=>$apply_condition,
								'discount_amount'=>$coupon_amount,
								'apply_to'=>$ct_types,
								'created_at'=>time(),
								'created_by'=>$fbc_user_id,
								'ip'=>$_SERVER['REMOTE_ADDR']
							);
							$sales_rule_id=$this->WebshopModel->insertData('salesrule',$insertdata);
							if($sales_rule_id){
								$insert_coupon=array(	
									'rule_id'=>$sales_rule_id,
									'coupon_code'=>$refund_coupon_code,
									'email_address'=>$customer_email,
									'email_sent'=>1,
									'created_by'=>$fbc_user_id,
									'created_at'=>time(),
									'ip'=>$_SERVER['REMOTE_ADDR']
								);
								$sales_rule_coupn_id=$this->WebshopModel->insertData('salesrule_coupon',$insert_coupon);
								
								if($sales_rule_coupn_id){
									$odr_update=array('refund_coupon_code'=>$refund_coupon_code,'updated_at'=>time());  
									$where_arr=array('id'=>$escalations_order_id);								
									$this->ReturnOrderModel->updateData('sales_order_escalations',$where_arr,$odr_update);
								}
								
							}

							$start_date = date('Y-m-d');
							$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
							$expiry_date = date('j F Y', strtotime($end_date));
							
							
							/*----------------Send Email to customer--------------------*/

							$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
							$amountwithcurrency=$shop_owner->currency_code." ".$coupon_amount;
							$webshop_details=$this->CommonModel->get_webshop_details($shop_id);
							$owner_email=$shop_owner->email;
							$templateId ='storecredit-voucher-returnorder';
							$to = $customer_email;
							$shop_name=$shop_owner->org_shop_name;
							$username = $shop_owner->owner_name;
							$site_logo = '';
							if(isset($webshop_details)){
							 $shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
							}
							$burl= base_url();
							$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
							$site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">
									<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
								</a>';
							$TempVars = array();
							$DynamicVars = array();
							
							$TempVars = array("##CUSTOMERNAME##" ,"##RETURNORDERID##","##VOUCHERCODE##","##VOUCHERAMOUNT##",'##WEBSHOPNAME##',"##VOUCHEREXPIRYDATE##");
							$DynamicVars   = array($customer_name,$ret_increment_id,$refund_coupon_code,$amountwithcurrency,$shop_name,$expiry_date);
							$CommonVars=array($site_logo, $shop_name);
							if(isset($templateId)){
								$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shop_id);
								if($emailSendStatusFlag==1){
									$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars,$ret_increment_id,$CommonVars);
								}
							}


						}

					} // end cancel order

				}else{
				
					$this->load->model('WebshopModel');
					
					$ret_increment_id=$EscalationsOrderData->esc_order_id;
					
					
					$CustomerTypes=$this->WebshopOrdersModel->getMultiDataById('customers_type_master',array(),'');
					
					$ct_types='';
					if(isset($CustomerTypes) && count($CustomerTypes)>0){
						foreach($CustomerTypes as $val){
							$ct_types_arr[]=$val->id;
						}
						
						$ct_types=implode(',',$ct_types_arr);
						
					}
				
					$increment_id=$OrderData->increment_id;
					$coupon_amount=$EscalationsOrderData->order_grandtotal_approved;
					
					$discount_name = 'Refund Voucher for return order  #'.$increment_id;
					$description = 'Refund Voucher for return order #'.$increment_id;
					$start_date = date('Y-m-d');
					$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
					$apply_condition = 'discount_on_mincartval';
					$apply_type = 'by_fixed';
					
					$disc_status=1;
					
					$refund_coupon_code = 'REFESC-'.$increment_id.'-'.time();
					// $refund_coupon_code = $ret_increment_id;//old
					
					$insertdata=array(	
						'name'=>$discount_name,
						'description'=>$description,
						'type'=>3,
						'coupon_type'=>1,
						'usage_per_customer'=>1,
						'usge_per_coupon'=>1,
						'start_date'=>$start_date,
						'end_date'=>$end_date, 
						'status'=>$disc_status,
						'apply_type'=>$apply_type,
						'apply_condition'=>$apply_condition,
						'discount_amount'=>$coupon_amount,
						'apply_to'=>$ct_types,
						'created_at'=>time(),
						'created_by'=>$fbc_user_id,
						'ip'=>$_SERVER['REMOTE_ADDR']
					);
					$sales_rule_id=$this->WebshopModel->insertData('salesrule',$insertdata);
					if($sales_rule_id){
						$insert_coupon=array(	
							'rule_id'=>$sales_rule_id,
							'coupon_code'=>$refund_coupon_code,
							'email_address'=>$customer_email,
							'email_sent'=>1,
							'created_by'=>$fbc_user_id,
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$sales_rule_coupn_id=$this->WebshopModel->insertData('salesrule_coupon',$insert_coupon);
						
						if($sales_rule_coupn_id){
							$odr_update=array('refund_coupon_code'=>$refund_coupon_code,'updated_at'=>time());  
							$where_arr=array('id'=>$escalations_order_id);								
							$this->ReturnOrderModel->updateData('sales_order_escalations',$where_arr,$odr_update);
						}
						
					}

					$start_date = date('Y-m-d');
					$end_date = date('Y-m-d', strtotime('+1 year', strtotime($start_date)) );
					$expiry_date = date('j F Y', strtotime($end_date));
					
					
					/*----------------Send Email to customer--------------------*/

					$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);
					$amountwithcurrency=$shop_owner->currency_code." ".$coupon_amount;
					$webshop_details=$this->CommonModel->get_webshop_details($shop_id);
					$owner_email=$shop_owner->email;
					$templateId ='storecredit-voucher-returnorder';
					$to = $customer_email;
					$shop_name=$shop_owner->org_shop_name;
					$username = $shop_owner->owner_name;
					$site_logo = '';
					if(isset($webshop_details)){
					 $shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
					}
					$burl= base_url();
					$shop_logo = get_s3_url($shop_logo ?? '', $shop_id); 
					$site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">
							<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
						</a>';
					$TempVars = array();
					$DynamicVars = array();
					
					$TempVars = array("##CUSTOMERNAME##" ,"##RETURNORDERID##","##VOUCHERCODE##","##VOUCHERAMOUNT##",'##WEBSHOPNAME##',"##VOUCHEREXPIRYDATE##");
					$DynamicVars   = array($customer_name,$ret_increment_id,$refund_coupon_code,$amountwithcurrency,$shop_name,$expiry_date);
					$CommonVars=array($site_logo, $shop_name);
					if(isset($templateId)){
						$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shop_id);
						if($emailSendStatusFlag==1){
							$mailSent = $this->WebshopOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars,$DynamicVars,$ret_increment_id,$CommonVars);
						}
					}

				
			

				}    
			}
			
			$refund_message=(isset($_POST['refund_message']) ? $_POST['refund_message'] : '');
			
			$odr_update=array('refund_status'=>1,'refund_approve_reject_message'=>$refund_message,'refund_approve_reject_date'=>time(),'updated_at'=>time());  //Completed
			$where_arr=array('id'=>$escalations_order_id);		
			$this->ReturnOrderModel->updateData('sales_order_escalations',$where_arr,$odr_update);

			$arrResponse  = array('status' =>200 ,'message'=>'Order refund confirmed successfully.');
			echo json_encode($arrResponse);exit;
				
		}else{
			$arrResponse  = array('status' =>403 ,'message'=>'Something went wrong.');
			echo json_encode($arrResponse);exit;
				
		}
	}


	function escalationsRefundMethod(){
		$order_grandtotal_approved=$_POST['order_grandtotal_approved'];
		$cancel_refund_type=$_POST['cancel_refund_type'];
		$escalationsId=$_POST['escalationsId'];
		$voucher_amount=$_POST['voucher_amount'];
		$order_grandtotal_approved_online=$_POST['order_grandtotal_approved_online'];

		$cancel_date=time();
		$updated_at=time();
		// echo json_encode($cancel_refund_type);exit;
		if($escalationsId){
			$updatedata=array(
				'order_grandtotal_approved'=>$order_grandtotal_approved,
				'cancel_refund_type'=>$cancel_refund_type,
				'order_grandtotal_approved_online'=>$order_grandtotal_approved_online,
				'order_grandtotal_approved_online_voucher'=>$voucher_amount,
				/*'status'=>3,// status updated cancel status=3
				// 'cancel_refund_type'=>$cancel_refund_type,
				'cancel_reason'=>$cancel_reason,
				'cancel_by_customer'=>2,//cancel by 3 admin*/
				// 'cancel_by_admin'=>$fbc_user_id,//login user id
				'cancel_date'=>$cancel_date,
				'updated_at'=>$updated_at
			);
			/*updated query*/
			$where_arr=array('id'=>$escalationsId);
			$this->WebshopOrdersModel->updateData('sales_order_escalations',$where_arr,$updatedata);

			//
			//end bacode generate
			$arrResponse  = array('status' =>200 ,'message'=>'Order refund mode updated successfully.');
			echo json_encode($arrResponse);exit;

		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong.');
			echo json_encode($arrResponse);exit;
		}

	}

	
	function PayPalRefundApi($apiPaypalData,$clientId,$secret){
		$order_type='0';
		$ECSId=$apiPaypalData['resource_id'];
		$refundType=$apiPaypalData['type'];
		$pay_pal_api_url=$apiPaypalData['pay_pal_api_url'];
		$clientId=$clientId;
		$secret=$secret;
		if(isset($apiPaypalData) && isset($apiPaypalData['order_type']) && $apiPaypalData['order_type']==1){
			$order_type=$apiPaypalData['order_type'];
			$refund_Amount=$apiPaypalData['refund_Amount'];
			$currency_code=$apiPaypalData['currency_code'];
			$order_id=$apiPaypalData['order_id'];
		}else{
			$refund_Amount='';
			$currency_code='';
			$order_id='';
		}
		$transaction_id=$apiPaypalData['transaction_id'];
		$apiURL=$pay_pal_api_url.'/v1/payments/sale/'.$transaction_id.'/refund';

		$array = array(
		'amount'=>
			array('currency'=>$currency_code,'total'=>$refund_Amount),
		'invoice_no'=> $order_id,
		);
		$postField=json_encode($array);

		$ch=curl_init();
		$headers=array('Content-Type: application/json','Authorization: Basic '.base64_encode($clientId.':'.$secret));
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch, CURLOPT_URL, $apiURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		if($order_type==1){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
		}else{
			curl_setopt($ch, CURLOPT_POSTFIELDS, "{}");
		}
		
		$result = curl_exec($ch);
		$err = curl_error($ch);
		$result_array=json_decode($result,true);
		curl_close($ch);
		
		if(isset($result_array)){
			if(isset($result_array['state']) && $result_array['state']=='completed'){
				$paypalinsertdata=array(	
					'resource_id'=>$ECSId,
					'type'=>$refundType,
					'input'=>json_encode($apiPaypalData),
					'output'=>json_encode($result_array)
				);
				$this->load->model('WebshopModel');
				$this->WebshopModel->insertData('paypal_refund_details',$paypalinsertdata);
			}else{
				if(isset($result_array) && $result_array['message']!=''){
					$arrResponse  = array('status' =>403 ,'message'=>$result_array['message']);
				}else{
					$arrResponse  = array('status' =>403 ,'message'=>'Something went wrong.');
				}
				echo json_encode($arrResponse);exit;
			}
		}else{
			$arrResponse  = array('status' =>403 ,'message'=>'Something went wrong.');
			echo json_encode($arrResponse);exit;
		}
	}

	/*end cancel order*/

	function shippingCost(){
		if(isset($_POST['return_order_id']) && $_POST['return_order_id']!='')
		{
			$return_order_id=$_POST['return_order_id'];
			$shipping_charge_flag=$_POST['shipping_charge_flag'];
			$shipping_amount=$_POST['shippingAmount'];
			$return_approved=$_POST['return_approved'];

			if($shipping_charge_flag==1){
				$st_update=array('order_grandtotal_approved'=>$return_approved,'shipping_charge_flag'=>$shipping_charge_flag,'shipping_amount'=>$shipping_amount,'status_updated_date'=>time(),'updated_at'=>time());
			}else{
				$shipping_amount=0.00;
				$st_update=array('order_grandtotal_approved'=>$return_approved,'shipping_charge_flag'=>$shipping_charge_flag,'shipping_amount'=>$shipping_amount,'status_updated_date'=>time(),'updated_at'=>time());
			}
			
			$where_arr=array('return_order_id'=>$return_order_id);
			$this->WebshopOrdersModel->updateData('sales_order_return',$where_arr,$st_update);
			$arrResponse  = array('status' =>200 ,'message'=>'Order Shipping amount added successfully.');
			echo json_encode($arrResponse);exit;
		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong.');
			echo json_encode($arrResponse);exit;
		}
	}

	function paypal_refund_details ()
	{
		$paypalData = $this->ReturnOrderModel->paypal_refund_detail();
		echo "<pre>"; 
		print_r($paypalData);
	}
}
