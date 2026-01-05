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
						<h2 class="table-heading-small">Tracking Details</h2>
							<div class="table-responsive text-center">
								<table class="table table-bordered table-style"  id="DT_B2BOrderShipped">
									<thead>
										<tr>
											<th>Order Number  </th>
											<th>Box Number  </th>
											<th>Weight (Kgs)</th>
											<th>Tracking ID</th>
											<th>Delivery slip</th>
										</tr>
									</thead>
									<tbody>
									<?php


										$packing_slip_url='';
$shipmentApiToken='';
$pickup_request_url='';
$pickupDate='';
$pickupTime='';
$pickupLocation='';
$expectedPkdQty='';
$shipment_master_type_id='';

$webshop_order_id=$OrderData->webshop_order_id;

if ($OrderData->is_split=='1') {
	foreach ($SplitOrderIds as $split_order) {
		$so_id=$split_order->order_id;
		$CheckShipmentCreated=$this->B2BOrdersModel->getSingleDataByID('b2b_order_shipment', array('order_id'=>$so_id), '');
		if (isset($CheckShipmentCreated) && $CheckShipmentCreated->id!='') {
			$Boxes=$this->B2BOrdersModel->getMultiDataById('b2b_order_shipment_details', array('order_id'=>$so_id), '', 'id', 'ASC');
			$count_box=count($Boxes);
		} else {
			$count_box=0;
			$Boxes=array();
		}
		?>

											<?php if ($count_box>0) {
												foreach ($Boxes as $key=>$box) {
													// start api
													if (!empty($box->tracking_id)) {
														$OrderData->order_id;

														$webshop_shop_id=$OrderData->shop_id;
														$parent_id=$OrderData->main_parent_id; //b2b order table supplier
														$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id'=>$webshop_shop_id), '');
														$webshop_fbc_user_id=$FbcUser->fbc_user_id;
														$args['shop_id']	=	$webshop_shop_id;
														$args['fbc_user_id']	=	$webshop_fbc_user_id;
														$this->load->model('ShopProductModel');
														$this->ShopProductModel->init($args);

														$shipingApidata=$this->ShopProductModel->getSingleDataByID('shipment_master', array('id'=>$CheckShipmentCreated->shipment_id), 'api_details');

														if (isset($shipingApidata) && !empty($shipingApidata)) {
															$shipingApiDetails=json_decode($shipingApidata->api_details);
															if (isset($shipingApiDetails) && !empty($shipingApiDetails)) {
																$packing_slip_url=$shipingApiDetails->packing_slip_url;
																$shipmentApiToken=$shipingApiDetails->api_token;
															}
														}
													}

													$btn_name = ($box->email_sent_flag==0) ? "Send" : "Resend";
													$disabled_input = ($box->email_sent_flag==0) ? "" : "disabled";
													?>
											<tr>
											<?php if ($key==0) { ?>
											<td rowspan="<?php echo ($count_box>1) ? $count_box : ''; ?>" class="vertical-middle"><?php echo $split_order->increment_id; ?></td>
											<?php } ?>
											<td>Box <?php echo $box->box_number; ?></td>
											<td><?php echo $box->weight; ?></td>
											<td><span class="tracking-id-table"><input type="text" placeholder="Tracking ID" name="tracking_id[<?php echo $box->id; ?>]" class="form-control input-xs valid-alphanum" value="<?php echo (isset($box->tracking_id) && $box->tracking_id!='') ? $box->tracking_id : '-'; ?>" <?=$disabled_input?>><input type="text" name="tracking_url[<?php echo $box->id; ?>]" placeholder="Tracking URL" class="form-control input-xs valid-url" value="<?php echo (isset($box->tracking_url) && $box->tracking_url!='') ? $box->tracking_url : ''; ?>"  <?=$disabled_input?>></span>
											<!-- new send email button add -->
											<?php if (isset($box->tracking_id) && $box->tracking_id!='') { ?>
												<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
												<button class="purple-btn" type="button" onclick="sendTrackingEmail(<?php echo $so_id; ?>, <?php echo $box->id; ?>);"><?php echo $btn_name; ?></button>
											    <?php } ?>
											<?php } ?>
											<!-- end new send email button add -->
										    </td>
											<?php if ($key==0) { ?>
											<td rowspan="<?php echo ($count_box>1) ? $count_box : ''; ?>" class="vertical-middle"><button class="white-btn" type="button" onclick="PrintShippingLabel_table(<?php echo $so_id; ?>,<?php echo $box->order_shipment_id; ?>);">Print</button></td>
											<?php } ?>
												</tr>
												<?php
												}
											} else {
												?>
											<tr class="">
											<td rowspan="" class="vertical-middle"><?php echo $split_order->increment_id; ?></td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											</tr>

											<?php } ?>

										<?php } ?>

										<?php } else {
											$CheckShipmentCreated=$this->B2BOrdersModel->getSingleDataByID('b2b_order_shipment', array('order_id'=>$OrderData->order_id), '');

											if (isset($CheckShipmentCreated) && $CheckShipmentCreated->id!='') {
												$shipment_master_type_id=$CheckShipmentCreated->shipment_id; //api
												$Boxes=$this->B2BOrdersModel->getMultiDataById('b2b_order_shipment_details', array('order_id'=>$OrderData->order_id), '', 'id', 'ASC');
												$count_box=count($Boxes);
											} else {
												$count_box=0;
												$Boxes=array();
											}

											$webshop_shop_id=$OrderData->shop_id;
											$parent_id=$OrderData->main_parent_id;
											$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id'=>$webshop_shop_id), '');
											$webshop_fbc_user_id=$FbcUser->fbc_user_id;
											$args['shop_id']	=	$webshop_shop_id;
											$args['fbc_user_id']	=	$webshop_fbc_user_id;
											$this->load->model('ShopProductModel');
											$this->ShopProductModel->init($args);

											?>

											<?php if ($count_box>0) {
												foreach ($Boxes as $key=>$box) {
													$box_weight=$box->weight;
													$shipingApidata=$this->ShopProductModel->getSingleDataByID('shipment_master', array('id'=>$CheckShipmentCreated->shipment_id), 'api_details');

													if (isset($shipingApidata) && !empty($shipingApidata)) {
														$shipingApiDetails=json_decode($shipingApidata->api_details);
														if (isset($shipingApiDetails) && !empty($shipingApiDetails)) {
															$packing_slip_url=$shipingApiDetails->packing_slip_url;
															$shipmentApiToken=$shipingApiDetails->api_token;
															$pickup_request_url=$shipingApiDetails->pickup_request_url;
															$pickupLocation='WHUSO SURFACE';
															$expectedPkdQty=1;
															$indiaData=$this->CommonModel->indiaTimeSet();
															if (isset($indiaData)) {
																$pickupDate=$indiaData['date'];
																$pickupTime=$indiaData['time'];
															}
														}
													}

													$btn_name = ($box->email_sent_flag==0) ? "Send" : "Resend";
													$disabled_input = ($box->email_sent_flag==0) ? "" : "disabled";
													?>
											<tr>
											<?php if ($key==0) { ?>
											<td rowspan="<?php echo ($count_box>1) ? $count_box : ''; ?>" class="vertical-middle"><?php echo $OrderData->increment_id; ?></td>
											<?php } ?>
											<td>Box <?php echo $box->box_number; ?></td>
											<td><?php echo $box->weight; ?></td>
											<td>

												<span class="tracking-id-table"><input type="text" name="tracking_id[<?php echo $box->id; ?>]" placeholder="Tracking ID" class="form-control input-xs" value="<?php echo (isset($box->tracking_id) && $box->tracking_id!='') ? $box->tracking_id : '-'; ?>" <?=$disabled_input?>><input type="text" name="tracking_url[<?php echo $box->id; ?>]" placeholder="Tracking URL" class="form-control input-xs valid-url" value="<?php echo (isset($box->tracking_url) && $box->tracking_url!='') ? $box->tracking_url : ''; ?>" <?=$disabled_input?>></span>
											  <div class="tracking-id-btn">
											<!-- new send email button add -->
											<?php if (isset($box->tracking_id) && $box->tracking_id!='') { ?>
												<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
												<button class="purple-btn" type="button" onclick="sendTrackingEmail(<?php echo $OrderData->order_id; ?>, <?php echo $box->id; ?>);"><?php echo $btn_name; ?></button>
											    <?php } ?>
											<?php } ?>
											<!-- end new send email button add -->
											<!-- api inigration code -->
											<?php
														$paymentMethodShopflag2=$this->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id'=>$webshop_order_id), 'payment_method');
													if (isset($paymentMethodShopflag2) && $shipment_master_type_id!='' && $shipment_master_type_id==3 && $paymentMethodShopflag2->payment_method!='') {
														if (isset($box->tracking_id) && $box->tracking_id!='') {
															$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($webshop_shop_id);
															$data['user_details'] = $this->CommonModel->GetUserByUserId($webshop_fbc_user_id);
															if ($data['user_details']->parent_id == 0) {
																$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
															} else {
																$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
															}
															if (isset($data['user_shop_details']) && $data['user_shop_details']->currency_code) {
																$currencyWebshop=$data['user_shop_details']->currency_code;
															} else {
																$currencyWebshop='';
															}

															?>

												<button class="purple-btn" type="button" onclick="generatedApiPackageSlip('<?php echo $packing_slip_url;?>','<?php echo $shipmentApiToken;?>','<?php echo $box->tracking_id;?>','<?php echo $this->B2BOrdersModel->getOrderCustomerNameByOrderId($OrderData->order_id);?>','<?php echo $currencyWebshop;?>')">Download Package Slip</button>

											<?php } else {
												$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($webshop_shop_id);
												$data['user_details'] = $this->CommonModel->GetUserByUserId($webshop_fbc_user_id);
												if ($data['user_details']->parent_id == 0) {
													$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
												} else {
													$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
												}
												if (isset($data['user_shop_details']) && $data['user_shop_details']->currency_code) {
													$currencyWebshop=$data['user_shop_details']->currency_code;
												} else {
													$currencyWebshop='';
												}
												?>
												<button class="purple-btn" type="button" onclick="manualCreateShipment('<?php echo $packing_slip_url;?>','<?php echo $shipmentApiToken;?>','<?php echo $this->B2BOrdersModel->getOrderCustomerNameByOrderId($OrderData->order_id);?>','<?php echo $currencyWebshop;?>','<?php echo $box->id;?>','<?php echo $OrderData->order_id;?>','<?php echo $shipment_master_type_id;?>','<?php echo $box_weight;?>')">Create Shipment</button>
											<?php
											}
													}
													?>

												</div>
											</td>
											<?php if ($key==0) { ?>
											<td rowspan="<?php echo ($count_box>1) ? $count_box : ''; ?>" class="vertical-middle"><button class="white-btn" type="button" onclick="PrintShippingLabel_table(<?php echo $OrderData->order_id; ?>,<?php echo $box->order_shipment_id; ?>);">Print</button></td>
											<?php } ?>
												</tr>
											<?php
												}
											} else {
												?>
											<tr class="">
											<td rowspan="" class="vertical-middle"><?php echo $OrderData->increment_id; ?></td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											</tr>

											<?php }
											} ?>
									</tbody>
								</table>
							</div>

							<?php  $this->load->view('b2b/order/all_scanned_items'); ?>

							<div class="save-discard-btn confirm-shipment-btn pad-bottom-20">
								<button class="white-btn"   type="button" data-toggle="collapse" data-target="#collapseExamplePD" aria-expanded="false" aria-controls="collapseExamplePD">View Order Details </button>
								<?php if ($OrderData->status!=6) { ?>
									<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
									<button class="purple-btn" type="button" onclick="SaveTrackingID();">Save</button>
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
