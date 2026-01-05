<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
  <h1 class="head-name">Order Details </h1>
  <div class="float-right resend-req-pay-block">

 <!-- start cancel order button -->
 <?php
		if($OrderData->checkout_method == 'guest' && $OrderData->status == 6)
		{
			$order_id=base64_encode($OrderData->order_id);
			$encoded_oid = urlencode($order_id);
			$burl= base_url();
			$shop_id = $this->session->userdata('ShopID');
				$website_url = getWebsiteUrl($shop_id,$burl);
				$return_link = $website_url.'/guest-order/detail/'.$encoded_oid;
			?>
			<a class="copy_link btn purple-btn" style="color: #fff;" type="button" href="<?php echo $return_link; ?>">Copy Return Link</a>
<?php } ?>

<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
 <?php
//   $webshopPaymentsStripe=$this->CommonModel->getSingleShopDataByID('webshop_payments_stripe',array('payment_id'=>'6'),'status');
  $webshopPaymentsStripe='';
  $order_request_payment_data=$this->WebshopOrdersModel->get_order_requst_payment($OrderData->order_id);

  if ($order_request_payment_data=="" && isset($webshopPaymentsStripe)) {
  	if ((isset($OrderPaymentDetail) && ($OrderPaymentDetail->payment_method_id==4 || $OrderPaymentDetail->payment_method_id==5)) || (!isset($OrderPaymentDetail) && $OrderData->grand_total > 0)) {
	  	?>
		  	<!-- <button name="req_payment" class="purple-btn small-button" onclick="OpenRequestPaymentPopup(<?php //echo $OrderData->order_id; ?>);">Request Payment</button> -->
		  <?php
		}
  }else{
  	if ((isset($OrderPaymentDetail) && ($OrderPaymentDetail->payment_method_id==4 || $OrderPaymentDetail->payment_method_id==5 || $OrderPaymentDetail->payment_method_name=="")) || (!isset($OrderPaymentDetail) && $OrderData->grand_total > 0)) {
	  	if(isset($webshopPaymentsStripe) && $webshopPaymentsStripe->status==2){
  			echo "<span class='last-requested'>Last Requested On : ".date('d/m/Y',$order_request_payment_data->created_at)."</span>";

			  $data['shopData']= $this->WebshopModel->getShopData($_SESSION['ShopOwnerId'],$_SESSION['ShopID']);
			  $shop_id=$data['shopData']->shop_id;
			  if ($data['shopData']->org_website_address == "") {
				  $burl= base_url();
				  $webshop_address = getWebsiteUrl($shop_id,$burl);
			  }else{
				  $webshop_address = $data['shopData']->org_website_address;
			  }
			  $data_o['order_id'] = $OrderData->order_id;
			  $encrypted_order_id = rtrim(base64_encode(json_encode($data_o)), '=');
			  $request_link=$webshop_address."/request-payment/".$encrypted_order_id;
		?>

			<a class="btn purple-btn" id="copy_request_link" style="color: #fff;" type="button" href="<?php echo $request_link; ?>">Copy Payment Request Link</a>
			<button name="req_payment" class="purple-btn small-button" onclick="OpenRequestPaymentPopup(<?php echo $OrderData->order_id; ?>);">Resend Request Payment</button>
		  <?php
			}
		}
  }

  ?>
<?php
	$use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');
	if ($use_advanced_warehouse->value=="yes") {
		if($OrderData->approved_at !=''){
			if ($current_tab !='split-order' && $OrderData->parent_id ==0) {
				//echo "<b>Order approved at :</b> ".$OrderData->approved_at;
			}
		}else{
			if ($current_tab !='split-order' && $OrderData->parent_id ==0) {
			?>
			<!-- <button name="cancel_order_btn" class="purple-btn small-button" onclick="OpenApproveProductPopup(<?php //echo $OrderData->order_id; ?>);">Approve Order</button> -->
			<?php
			}
		}
	}

?>

<?php
	if(isset($cancel_order) && $cancel_order=='able_to_cancel'){
		//if(isset($OrderData->main_parent_id) && $OrderData->main_parent_id ==0 ){
		if($OrderData->parent_id >0 ){
?>
	<button name="cancel_order_btn" class="purple-btn small-button" data-toggle="modal" id="cancel_order_btn" data-id="" value="" data-target="#cancel-order-modal">Delete Order</button>
<?php
		}else{
?>
  		<button name="cancel_order_btn" class="purple-btn small-button" data-toggle="modal" id="cancel_order_btn" data-id="" value="" data-target="#cancel-order-modal">Cancel Order</button>
<?php
		} //end if split check
	}
?>
  <!-- end cancel order button -->
  <?php } ?>

  <?php if($current_tab!='supplier-b2b-order' ) { ?>

  <?php if(isset($b2b_orders) && count($b2b_orders)>0){ ?>
	<button class="white-btn dropdown-toggle" type="button" id="dropdownMenuButton2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="View B2B Orders">View B2B Orders </button>
		<div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
		<?php if(isset($b2b_orders) && count($b2b_orders)>0){
		foreach($b2b_orders as $val){?>
			<a target="_blank" class="m-2 dropdown-item" href="<?php echo base_url() ?>webshop/supplier-b2b-order/detail/<?php echo $val['shop_id']; ?>/<?php echo $val['order_id']; ?>"><?php echo $val['increment_id']; ?></a>
			<div class="dropdown-divider"></div>
		<?php } } ?>
		</div>
  <?php }   ?>



  <?php if( $OrderData->parent_id>0){?>

		<button class="white-btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="View Split Orders">View Split Orders </button>
		<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		<?php if(isset($SplitOrderIds) && count($SplitOrderIds)>0){
		foreach($SplitOrderIds as $spo){?>
			<a id="" class="m-2 dropdown-item" href="<?php echo base_url() ?>webshop/split-order/detail/<?php echo $spo->order_id; ?>"><?php echo $spo->increment_id; ?></a>
			<div class="dropdown-divider"></div>
		<?php } } ?>
		</div>

  <?php } ?>


	<?php if($current_tab=='order' || $current_tab=='split-order' ) { ?>
	<a class="purple-btn" type="button" target="_blank" href="<?php echo base_url(); ?>webshop/order/print/<?php echo $OrderData->order_id; ?>">Print</a>
	<?php }else if($current_tab=='shipped-order' ){ ?>
	<a class="purple-btn" type="button" target="_blank" href="<?php echo base_url(); ?>webshop/shipped-order/print/<?php echo $OrderData->order_id; ?>">Print</a>
	<?php } ?>
	<div class="internal-notes">
		<!-- <button name="cancel_order_btn" class="purple-btn small-button notes-btn" onclick="OpenNotesPopup(<?php echo $OrderData->order_id; ?>);">Notes</button> -->

	</div>


	<?php }else{ ?>
	<a class="purple-btn" type="button"  href="<?php echo base_url(); ?>webshop/b2b/order/detail/<?php echo $webshop_order_id; ?>">Back To Main Order</a>
	<?php } ?>
  </div>
</div>
