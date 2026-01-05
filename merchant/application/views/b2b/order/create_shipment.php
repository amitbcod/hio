<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('b2b/order/breadcrums'); ?>
	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
				<?php  $this->load->view('b2b/order/order-top-info'); ?>
				<!-- form -->
				<div class="content-main form-dashboard">
					<?php  $this->load->view('b2b/order/order-customer-info'); ?>
			<form name="create-shipment" id="create-shipment" method="POST" action="<?php echo base_url(); ?>B2BOrdersController/createShipment">
			<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
			<input type="hidden" id="order_id" name="order_id" value="<?php echo $OrderData->order_id; ?>">
			<!-- barcode-qty-box -->
			<div class="row shipping-details-confirm-shipment">
				<div class="col-sm-12">
					<h2 class="bank-head exc-term">Shipping Details </h2>
				</div>
				<div class="col-sm-5">
					<label>Shipping boxes</label>
						<input type="hidden" name="total_weight_fields" id="total_weight_fields"  value="1">
						<div class="" id="item_weight_fields">
							<div class="boxes item-weight-input"><span>Box 1 :</span> <input class="form-control" type="text" name="box_weight[]" value="" placeholder="Weight in kgs" onkeypress="return isNumberKey(event);"><span class="kg"> kg </span></div>
						</div>
					<div class="boxes"><a class="add-new"  href="javascript:void(0);" onclick="add_item_weight_fields();"> +  Add New</a></div>
				</div>
				<!-- col-sm-5 -->
				<div class="col-sm-5">
					<label>Additional Message</label>
					<textarea class="form-control" placeholder="Message" name="additional_message" id="additional_message"></textarea>
				</div>
				<!-- col-sm-6 -->
			</div>
			<h2 class="table-heading-small">Shipping Products</h2>
			<div id="order-item-outer">
				<?php  $this->load->view('b2b/order/order-items'); ?>
			</div>
			<?php
				$paymentMethod='';
if (isset($order_shipment_type) && $order_shipment_type=='2') {
	$paymentMethodShopflag2=$this->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id'=>$webshop_order_id), 'payment_method');
	if (isset($paymentMethodShopflag2->payment_method) && $paymentMethodShopflag2->payment_method!='' && $paymentMethodShopflag2->payment_method=='cod') {
		$paymentMethod='cod';
	} else {
		$paymentMethod='Prepaid';
	}
}
?>
			<div class="clear pad-bottom-20"></div>
			<div class="row">
				<div class="col-sm-3">
					<h2 class="table-heading-small">Shipping Service</h2>
					<select class="form-control required-field shipment_api " name="shipment_id" id="shipment_id">
						<option value="">Select Shipment Service </option>
						<?php if (isset($ShipmentService) && count($ShipmentService)>0) {
							foreach ($ShipmentService as $value) {
								$selectedOption='';
								/*if (isset($webshop_order_shop_flag) && ($webshop_order_shop_flag==2 || $webshop_order_shop_flag==4)) {
									if (isset($webshop_order_id) && !empty($webshop_order_id)) {
										$paymentMethodShopflag2=$this->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id'=>$webshop_order_id), 'payment_method');
										if (isset($paymentMethodShopflag2) && $paymentMethodShopflag2->payment_method=='cod') {
											if ($value->name=='Delivery') {
												$selectedOption="selected";
											}
										}
									}
								}*/
								?>
							<option value="<?php echo $value->id; ?>" <?=$selectedOption?>><?php echo $value->name; ?></option>
						<?php }
							} ?>
					</select>
				</div>
			</div>
			<div class="save-discard-btn confirm-shipment-btn pad-bottom-20">
				<button class="white-btn" type="button"  onclick="PrintShippingLabel();">Print Packing List<!-- Print Shipping Labels --> </button>
			<?php
				if (isset($webshop_order_shop_flag) && ($webshop_order_shop_flag==2 || $webshop_order_shop_flag==4)) {
					echo '<input type="hidden" id="webshop_shop_id" value="'.$webshop_shop_id.'">';
				}

				if (isset($webshop_order_shop_flag) && $webshop_order_shop_flag==2) {
					$webshopOrderPrantId='';
					$webshopOrderMainPrantId='';
					$webshop_invoice_id=0;
					$webshop_invoice_flag=0;
					$webshop_invoice_b2b_orderid=0;
					if (isset($sales_order_data_invoice)) {
						$webshopOrderPrantId=$OrderData->parent_id; //b2b order table supplier
						$webshopOrderMainPrantId=$OrderData->main_parent_id; //b2b order table supplier
						$webshop_invoice_id=$sales_order_data_invoice->invoice_id;
						$webshop_invoice_flag=$sales_order_data_invoice->invoice_flag;
						echo '<input type="hidden" id="webshop_b2b_order_id" value="'.$OrderData->increment_id.'">';
						echo '<input type="hidden" id="b2b_order_id" value="'.$OrderData->order_id.'">';
						echo '<input type="hidden" id="webshop_order_id" value="'.$webshop_order_id.'">';
						echo '<input type="hidden" id="webshopParentId" value="'.$webshopOrderMainPrantId.'">';
						echo '<input type="hidden" id="webshop_shop_id" value="'.$webshop_shop_id.'">';
						echo '<input type="hidden" id="webshop_fbc_user_id" value="'.$webshop_fbc_user_id.'">';
						if ($webshop_invoice_id>0) {
							$invoicingData=$this->ShopProductModel->get_invoicedata_by_id($webshop_invoice_id);
							if (isset($invoicingData)) {
								$webshop_invoice_b2b_orderid=$invoicingData->b2b_orderid;
								$invoice_b2b_split=$invoicingData->b2b_orderid;// spilit invoice id
								$invoice_file=$invoicingData->invoice_file;// spilit invoice id
								echo '<input type="hidden" id="webshop_invoice_file" value="'.$invoice_file.'">';
							}
						}
					}

					if ($webshop_invoice_b2b_orderid == $OrderData->order_id) {
						$s3_base_url = get_s3_base_url($webshop_shop_id);
						?>
				<button class="white-btn" id="downloadInvoice" type="button" onclick="DownloadInvoice('<?php echo $s3_base_url; ?>');" >Download Invoice </button>
				<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
				<button class="purple-btn" id="createShipment" type="button" onclick="CreateShipmentSave();" >Confirm Shipment </button>
			  <?php } ?>
			<?php } else { ?>
				<button class="white-btn" id="generateInvoice" type="button" onclick="GenerateInvoice();" >Invoice Generate </button>
				<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
				<button class="purple-btn" id="createShipment" type="button" onclick="CreateShipmentSave();" disabled >Confirm Shipment </button>
			<?php } ?>
			<?php }
			} else { ?>
				<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
				<button class="purple-btn" id="createShipment" type="button" onclick="CreateShipmentSave();" <?php if ($OrderData->status >= 4) {
					echo 'disabled';
				}?>>Confirm Shipment </button>
			<?php } ?>
			<?php } ?>
			</div>
			<!-- new added -->
			<?php if (isset($ShippingAddress)) { ?>
				<input type="hidden" name="webshop_shipping_pincode" id="webshop_shipping_pincode" value="<?=$ShippingAddress->pincode?>">
				<!-- end new added -->
			<?php } ?>
		</form>
				</div>
				<!--end form-->
			</div>
	</div>
</main>
<script>
let room = 1;
function add_item_weight_fields() {
var room=$('#total_weight_fields').val();
  room++;
  var objTo = document.getElementById('item_weight_fields')
  var divtest = document.createElement("div");
  divtest.setAttribute("class", " removeclass" + room);
  var rdiv = 'removeclass' + room;
  divtest.innerHTML = '<div class="boxes item-weight-input" id="item_weight_'+room+'" ><span>Box '+room+' :</span> <input class="form-control required-field"  type="text" name="box_weight[]" value="" placeholder="Weight in kgs" onkeypress="return isNumberKey(event);"><span class="kg"> kg </span><button class="btn btn-danger btn-sm" type="button" onclick="remove_item_weight_fields(' + room + ');"> <i class="fa fa-minus"></i> </button></div>';
  objTo.append(divtest);
  $('#total_weight_fields').val($('.item-weight-input').length);
}
function remove_item_weight_fields(rid) {
  $('.removeclass' + rid).remove();
  $('#total_weight_fields').val($('.item-weight-input').length);
  if($('.item-weight-input').length<=0){
	$('#total_weight_fields').val('1');
  }
}
</script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b-order-item.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
