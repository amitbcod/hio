<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('b2b/order/breadcrums'); ?>

	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
				<?php  $this->load->view('b2b/order/order-top-info'); ?>
				<!-- form -->
				<div class="content-main form-dashboard">

					<?php  $this->load->view('b2b/order/order-customer-info'); ?>
					<form name="tracking-shipment-frm" id="tracking-shipment-frm" method="POST" action="<?php echo base_url(); ?>B2BOrdersController/createShipment">
					<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
					<input type="hidden" id="order_id" name="order_id" value="<?php echo $OrderData->order_id; ?>">
						

							<?php  //$this->load->view('b2b/order/all_scanned_items'); ?>
							<h2 class="table-heading-small">Products Details</h2>
							<?php $this->load->view('b2b/order/order-items'); ?>

							<div class="save-discard-btn confirm-shipment-btn pad-bottom-20">
								<!-- <button class="white-btn"   type="button" data-toggle="collapse" data-target="#collapseExamplePD" aria-expanded="false" aria-controls="collapseExamplePD">View Order Details </button> -->
								<?php if ($OrderData->status!=6) { ?>
									<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
									<!-- <button class="purple-btn" type="button" onclick="SaveTrackingID();">Save</button> -->
								    <?php } ?>
								<?php } ?>
							</div>
							</form>
				</div>

				<!--end form-->
			</div>
	</div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b-order-item.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script>
function sendTrackingEmail(order_id,tracking_id){
	if(order_id!='' && tracking_id != ''){
		$('#ajax-spinner').show();
		$.ajax({
				url: BASE_URL+"B2BOrdersController/sendTrackingEmailWebshopUser",
				type: "POST",
				data: {
				  order_id:order_id,
				  tracking_id:tracking_id
				},

				success: function(response) {
					$('#ajax-spinner').hide();
					var obj = JSON.parse(response);

					if(obj.status == 200) {

						setTimeout(function() {
							swal({
								title: "Success",
								text: obj.message,
								type: "success"
							}, function() {
								window.location.reload(1);
							});
						}, 1000);


					}else{
						swal('Error',obj.message,'error');
						return false;
					}

			}
		});

	}else{
		return false;
	}

}

function generatedApiPackageSlip(packing_slip_url,shipmentApiToken,tracking_id,customerName,currencyWebshop){
	if(tracking_id != ''){
		var customerName=customerName;
		$('#ajax-spinner').show();
		$.ajax({
				url: BASE_URL+"B2BOrdersController/delhiveryApiPackageSlipPrint",
				type: "POST",
				data: {
				  packing_slip_url:packing_slip_url,
				  shipmentApiToken:shipmentApiToken,
				  tracking_id:tracking_id,
				  currencyWebshop:currencyWebshop
				},
				xhrFields: {
		            responseType: 'blob'
		        },

				success: function(response) {
					$('#ajax-spinner').hide();
					var a = document.createElement('a');
		            var url = window.URL.createObjectURL(response);
		            a.href = url;
		            a.download = customerName;
		            document.body.append(a);
		            a.click();
		            a.remove();
		            window.URL.revokeObjectURL(url);
			}
		});

	}else{
		return false;
	}

}

function requestApiPickup(pickup_request_url,shipmentApiToken,pickupDate,pickupTime,pickupLocation,expectedPkdQty,box_id){

	if(pickupTime != ''){
		$('#ajax-spinner').show();
		$.ajax({
				url: BASE_URL+"B2BOrdersController/delhiveryApiPickupRequest",
				type: "POST",
				data: {
				  pickup_request_url:pickup_request_url,
				  shipmentApiToken:shipmentApiToken,
				  pickupDate:pickupDate,
				  pickupTime:pickupTime,
				  pickupLocation:pickupLocation,
				  expectedPkdQty:expectedPkdQty,
				  box_id:box_id
				},

				success: function(response) {
					$('#ajax-spinner').hide();
					var obj = JSON.parse(response);

					if(obj.status == 200) {

						setTimeout(function() {
							swal({
								title: "Success",
								text: obj.message,
								type: "success"
							}, function() {
								window.location.reload(1);
							});
						}, 1000);


					}else{
						swal('Error',obj.message,'error');
						return false;
					}

			}
		});

	}else{
		swal('Error','Unable to schedule pick up today!','error');
		return false;
	}

}

function manualCreateShipment(packing_slip_url,shipmentApiToken,customerName,currencyWebshop,box_id,order_id,shipment_id,box_weight){

	if(order_id != ''){
		$('#ajax-spinner').show();
		$.ajax({
				url: BASE_URL+"B2BOrdersController/manualCreateShipment",
				type: "POST",
				data: {
				  packing_slip_url:packing_slip_url,
				  shipmentApiToken:shipmentApiToken,
				  customerName:customerName,
				  currencyWebshop:currencyWebshop,
				  order_id:order_id,
				  box_id:box_id,
				  box_weight:box_weight,
				  shipment_id:shipment_id
				},

				success: function(response) {
					$('#ajax-spinner').hide();
					var obj = JSON.parse(response);

					if(obj.status == 200) {

						setTimeout(function() {
							swal({
								title: "Success",
								text: obj.message,
								type: "success"
							}, function() {
								window.location.reload(1);
							});
						}, 1000);


					}else{
						swal('Error',obj.message,'error');
						return false;
					}

			}
		});

	}else{
		swal('Error','Unable to create shipment!','error');
		return false;
	}

}
</script>
<?php $this->load->view('common/fbc-user/footer'); ?>
