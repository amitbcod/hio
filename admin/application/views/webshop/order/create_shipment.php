<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('webshop/order/breadcrums'); ?>

	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
				<?php  $this->load->view('webshop/order/order-top-info'); ?>


				<!-- form -->
				<div class="content-main form-dashboard">
					<?php  $this->load->view('webshop/order/order-customer-info'); ?>


			<form name="create-shipment" id="create-shipment" method="POST" action="<?php echo base_url(); ?>WebshopOrdersController/createShipment">
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
				<?php  $this->load->view('webshop/order/order-items'); ?>
			</div>
			<?php
				$paymentMethod='';
				if(isset($OrderPaymentDetail) && !empty($OrderPaymentDetail) ){
					$paymentMethod=$OrderPaymentDetail->payment_method;
				}
			?>
			<div class="clear pad-bottom-20"></div>
			<div class="row">
				<div class="col-sm-3">
					<h2 class="table-heading-small">Shipping Service</h2>
					<select class="form-control required-field <?php if($paymentMethod=='cod' && (isset($webshop_order_shop_flag) && $webshop_order_shop_flag==4)){ echo 'shipment_api';}?>" name="shipment_id" id="shipment_id">
						<option value="">Select Shipment Service</option>
						<?php if(isset($ShipmentService) && count($ShipmentService)>0){
							foreach($ShipmentService as $value){
								$selectedOption='';
								if(isset($webshop_order_shop_flag) && $webshop_order_shop_flag==4 ){
										if(isset($OrderData->order_id) && !empty($OrderData->order_id)){
											if(isset($paymentMethod) && $paymentMethod=='cod'){
												if($value->name=='Delivery'){
													$selectedOption="selected";
												}
											}
										}
								}

							?>
							<option value="<?php echo $value->id; ?>" <?=$selectedOption?>><?php echo $value->name; ?></option>
						<?php } } ?>
					</select>
				</div>
			</div>
			<?php
				if(isset($webshop_order_shop_flag) && !empty($webshop_order_shop_flag) && $webshop_order_shop_flag==4){
			?>
					<input type="hidden" name="webshop_shipping_pincode" id="webshop_shipping_pincode" value="<?=$ShippingAddress->pincode?>">
					<input type="hidden" name="webshop_shop_id" id="webshop_shop_id" value="<?=$this->session->userdata('ShopID')?>">
			<?php
				}
			?>
			<div class="save-discard-btn confirm-shipment-btn pad-bottom-20">
				<!-- <button class="white-btn" type="button" onclick="PrintShippingLabel();">Print Packing List</button> -->
			   <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
				<button class="purple-btn" id="createShipment" type="button" onclick="CreateShipmentSave();" <?php if($OrderData->status >=4){ echo 'disabled';}?> >Confirm Shipment </button>
				<?php } ?>
			</div>
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

 // alert($('.item-weight-input').length);

  objTo.append(divtest);
  $('#total_weight_fields').val($('.item-weight-input').length);

  //showhideAddWeightBtn();


}

function remove_item_weight_fields(rid) {
  $('.removeclass' + rid).remove();
  //showhideAddWeightBtn();
  $('#total_weight_fields').val($('.item-weight-input').length);


  if($('.item-weight-input').length<=0){
	$('#total_weight_fields').val('1');
  }


}


</script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop-order-item.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript">
	/*shipping service*/
	$(".shipment_api").change(function(){
		var shipmentId=$(this).val();
		var webshop_shop_id = $('#webshop_shop_id').val();
		var webshop_shipping_pincode = $('#webshop_shipping_pincode').val();
		if(shipmentId==3){
			$.ajax({
					url: BASE_URL+"B2BOrdersController/delhiveryApiPincodePrepaid",
					type: "POST",
					data: {
						shipmentId:shipmentId,
						webshop_shipping_pincode:webshop_shipping_pincode,
						webshop_shop_id:webshop_shop_id
					},
					beforeSend: function(){
							$('#ajax-spinner').show();
					},
					success: function(response) {
						$('#ajax-spinner').hide();
						var obj = JSON.parse(response);
						if(obj.status == 200) {
							swal('Success',obj.message,'success');
						}else{
							$('#shipment_id').prop('selectedIndex',0);
							swal('Error',obj.message,'error');
							return false;
						}
					}
			});
		}
	});
	/*shipping service*/
</script>
<?php $this->load->view('common/fbc-user/footer'); ?>
