<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('webshop/order/breadcrums'); ?>

	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
				<?php  $this->load->view('webshop/order/order-top-info'); ?>


				<!-- form -->

				<div class="content-main form-dashboard">

					<?php  $this->load->view('webshop/order/order-customer-info'); ?>
					<form name="tracking-shipment-frm" id="tracking-shipment-frm" method="POST" action="<?php echo base_url(); ?>WebshopOrdersController/createShipment">
					<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
					<input type="hidden" id="order_id" name="order_id" value="<?php echo $OrderData->order_id; ?>">
						<h2 class="table-heading-small">Tracking Details</h2>
							<div class="table-responsive text-center">
								<table class="table table-bordered table-style"  id="DT_WebshopOrderShipped">
									<thead>
										<tr>
											<th>Order Number  </th>
											<th>Box Number  </th>
											<th>Weight (Kg)</th>
											<th>Tracking Details</th>
											<th>Delivery slip</th>

										</tr>
									</thead>
									<tbody>
									<?php
										// new added
										$webshop_fbc_user_id=$this->session->userdata('LoginID');
										$webshop_shop_id= $this->session->userdata('ShopID');
										$webshop_order_id =$OrderData->order_id;
										//end
									  if($OrderData->is_split=='1'){
										foreach($SplitOrderIds as $split_order){
											$so_id=$split_order->order_id;
											$CheckShipmentCreated=$this->WebshopOrdersModel->getSingleDataByID('sales_order_shipment',array('order_id'=>$so_id),'');
											if(isset($CheckShipmentCreated) && $CheckShipmentCreated->id!=''){
												$shipment_master_type_id=$CheckShipmentCreated->shipment_id; //api
												$Boxes=$this->WebshopOrdersModel->getMultiDataById('sales_order_shipment_details',array('order_id'=>$so_id),'','id','ASC');
												$count_box=count($Boxes);
												$shipingApidata=$this->WebshopOrdersModel->getSingleDataByID('shipment_master',array('id'=>$CheckShipmentCreated->shipment_id),'api_details');
												$packing_slip_url='';
												$shipmentApiToken='';
												$shipmentTrackingUrl='';

												if(isset($shipingApidata) && !empty($shipingApidata)){
													$shipingApiDetails=json_decode($shipingApidata->api_details);
													if(isset($shipingApiDetails) && !empty($shipingApiDetails)){
														$packing_slip_url=$shipingApiDetails->packing_slip_url;
														$shipmentApiToken=$shipingApiDetails->api_token;
														$pickup_request_url=$shipingApiDetails->pickup_request_url;
														if(isset($shipingApiDetails->shipments)  && isset($shipingApiDetails->shipments->pickup_name)){
															$pickupName=$shipingApiDetails->shipments->pickup_name;
														}
														$pickupLocation=$pickupName;
														$expectedPkdQty=1;
														$indiaData=$this->CommonModel->indiaTimeSet();
														if(isset($indiaData)){
															$pickupDate=$indiaData['date'];
															$pickupTime=$indiaData['time'];
														}
													}
												}
											}else{
												$count_box=0;
												$Boxes=array();
											}
												?>

											<?php if($count_box>0){

											foreach($Boxes as $key=>$box){

											$btn_name = ($box->email_sent_flag==0)?"Send":"Resend";
											?>
											<tr>
											<?php if($key==0){ ?>
											<td rowspan="<?php echo ($count_box>1)?$count_box:''; ?>" class="vertical-middle"><?php echo $split_order->increment_id; ?></td>
											<?php } ?>
											<td>Box <?php echo $box->box_number; ?></td>
											<td><?php echo $box->weight; ?></td>
											<td><span class="tracking-id-table"><input type="text" placeholder="Tracking ID " name="tracking_id[<?php echo $box->id; ?>]" class="form-control input-xs valid-alphanum" value="<?php echo (isset($box->tracking_id) && $box->tracking_id!='')?$box->tracking_id:''; ?>"><input type="text" name="tracking_url[<?php echo $box->id; ?>]" placeholder="Tracking URL" class="form-control input-xs valid-url" value="<?php echo (isset($box->tracking_url) && $box->tracking_url!='')?$box->tracking_url:''; ?>"></span>
											<?php if(isset($box->tracking_id) && $box->tracking_id!='') {
													$CheckShipmentStatus=$this->WebshopOrdersModel->getSingleDataByID('shipment_detail_status',array('order_id'=>$OrderData->order_id,'shipment_detail_id'=>$box->id,'shipment_id'=>$box->order_shipment_id),'status');
													if(isset($CheckShipmentStatus) && !empty($CheckShipmentStatus)){
														$status_shipment=$this->CommonModel->CheckShipmentStatus($CheckShipmentStatus->status);
														if(isset($status_shipment) && !empty($status_shipment)){
															echo '<a herf="#" class="checkShipmentstatus">'.$status_shipment.'</a>';
														}
													}

												?>
												 <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
												<button class="purple-btn" type="button" onclick="sendTrackingEmail(<?php echo $so_id; ?>, <?php echo $box->id; ?>);"><?php echo $btn_name ?>

												</button>
											<?php } ?>
											<?php } ?>
												<!-- start delivery api packaging slip -->
												<div class="tracking-id-btn">
													<!-- // start api -->
													 <?php
														$paymentMethodShopflag2=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$webshop_order_id),'payment_method');
														if(isset($paymentMethodShopflag2) && $shipment_master_type_id!='' && $shipment_master_type_id==3 && $paymentMethodShopflag2->payment_method!=''){
															if(isset($box->tracking_id) && $box->tracking_id!='') {
																$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($webshop_shop_id);
																$data['user_details'] = $this->CommonModel->GetUserByUserId($webshop_fbc_user_id);
																if($data['user_details']->parent_id == 0)
																{
																	$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
																}else{
																	$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
																}
																if(isset($data['user_shop_details']) && $data['user_shop_details']->currency_code){
																	$currencyWebshop=$data['user_shop_details']->currency_code;
																}else{
																	$currencyWebshop='';
																}

														?>
															<button class="purple-btn" type="button" onclick="generatedApiPackageSlip('<?php echo $packing_slip_url;?>','<?php echo $shipmentApiToken;?>','<?php echo $box->tracking_id;?>','<?php echo $this->WebshopOrdersModel->getOrderCustomerNameByOrderId($OrderData->order_id);?>','<?php echo $currencyWebshop;?>')">Download Package Slip</button>


														<?php }
													 	}
													?>
													<!-- end api inigration code -->
												</div>
												<!--  end delivery api packaging slip -->
											</td>
											<?php if($key==0){ ?>
											<td rowspan="<?php echo ($count_box>1)?$count_box:''; ?>" class="vertical-middle"><button class="white-btn" type="button" onclick="PrintShippingLabel_table(<?php echo $so_id; ?>,<?php echo $box->order_shipment_id; ?>);">Print</button></td>
											<?php } ?>

												</tr>
												<?php
												}
											}else{
											?>
											<tr class="">
											<td rowspan="" class="vertical-middle"><?php echo $split_order->increment_id; if($split_order->status == 3){echo' (Cancel)';}?></td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											</tr>

											<?php } ?>




										<?php } ?>


										<?php }else{
											$CheckShipmentCreated=$this->WebshopOrdersModel->getSingleDataByID('sales_order_shipment',array('order_id'=>$OrderData->order_id),'');
											if(isset($CheckShipmentCreated) && $CheckShipmentCreated->id!=''){
												$shipment_master_type_id=$CheckShipmentCreated->shipment_id; //api
												$Boxes=$this->WebshopOrdersModel->getMultiDataById('sales_order_shipment_details',array('order_id'=>$OrderData->order_id),'','id','ASC');
												$count_box=count($Boxes);
												$shipingApidata=$this->WebshopOrdersModel->getSingleDataByID('shipment_master',array('id'=>$CheckShipmentCreated->shipment_id),'api_details');
												$packing_slip_url='';
												$shipmentApiToken='';
												$shipmentTrackingUrl='';

												if(isset($shipingApidata) && !empty($shipingApidata)){
													// $shipingApiDetails=json_decode($shipingApidata->api_details);
													// if(isset($shipingApiDetails) && !empty($shipingApiDetails)){
													// 	$packing_slip_url=$shipingApiDetails->packing_slip_url;
													// 	$shipmentApiToken=$shipingApiDetails->api_token;
													// 	$pickup_request_url=$shipingApiDetails->pickup_request_url;
													// 	if(isset($shipingApiDetails->shipments)  && isset($shipingApiDetails->shipments->pickup_name)){
													// 		$pickupName=$shipingApiDetails->shipments->pickup_name;
													// 	}
													// 	$pickupLocation=$pickupName;
													// 	$expectedPkdQty=1;
													// 	$indiaData=$this->CommonModel->indiaTimeSet();
													// 	if(isset($indiaData)){
													// 		$pickupDate=$indiaData['date'];
													// 		$pickupTime=$indiaData['time'];
													// 	}
													// }
												}
											}else{
												$count_box=0;
												$Boxes=array();
											}
												?>

											<?php if($count_box>0){


											foreach($Boxes as $key=>$box){

											$btn_name = ($box->email_sent_flag==0)?"Send":"Resend";
												?>
											<tr>
											<?php if($key==0){ ?>
											<td rowspan="<?php echo ($count_box>1)?$count_box:''; ?>" class="vertical-middle"><?php echo $OrderData->increment_id; ?></td>
											<?php } ?>
											<td>Box <?php echo $box->box_number; ?></td>
											<td><?php echo $box->weight; ?></td>
											<td><span class="tracking-id-table"><input type="text" placeholder="Tracking ID" name="tracking_id[<?php echo $box->id; ?>]" class="form-control input-xs" value="<?php echo (isset($box->tracking_id) && $box->tracking_id!='')?$box->tracking_id:''; ?>"><input type="text" name="tracking_url[<?php echo $box->id; ?>]" class="form-control input-xs valid-url" placeholder="Tracking URL" value="<?php echo (isset($box->tracking_url) && $box->tracking_url!='')?$box->tracking_url:''; ?>"></span>

											<?php if(isset($box->tracking_id) && $box->tracking_id!='') {
													$CheckShipmentStatus=$this->WebshopOrdersModel->getSingleDataByID('shipment_detail_status',array('order_id'=>$OrderData->order_id,'shipment_detail_id'=>$box->id,'shipment_id'=>$box->order_shipment_id),'status');
													if(isset($CheckShipmentStatus) && !empty($CheckShipmentStatus)){
														$status_shipment=$this->CommonModel->CheckShipmentStatus($CheckShipmentStatus->status);
														if(isset($status_shipment) && !empty($status_shipment)){
															echo '<a herf="#" class="checkShipmentstatus">'.$status_shipment.'</a>';
														}
													}
											?>
												 <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
												<button class="purple-btn" type="button" onclick="sendTrackingEmail(<?php echo $OrderData->order_id; ?>, <?php echo $box->id; ?>);"><?php echo $btn_name ?></button>
												<?php } ?>
											<?php } ?>
												<!-- start delivery api packaging slip -->
												<div class="tracking-id-btn">
													<!-- // start api -->
													 <?php
														$paymentMethodShopflag2=$this->WebshopOrdersModel->getSingleDataByID('sales_order_payment',array('order_id'=>$OrderData->order_id),'payment_method');
														if(isset($paymentMethodShopflag2) && $shipment_master_type_id!='' && $shipment_master_type_id==3 && $paymentMethodShopflag2->payment_method!=''){

														if(isset($box->tracking_id) && $box->tracking_id!='') {

														$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($webshop_shop_id);
														$data['user_details'] = $this->CommonModel->GetUserByUserId($webshop_fbc_user_id);
														if($data['user_details']->parent_id == 0)
														{
															$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
														}else{
															$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
														}
														if(isset($data['user_shop_details']) && $data['user_shop_details']->currency_code){
															$currencyWebshop=$data['user_shop_details']->currency_code;
														}else{
															$currencyWebshop='';
														}

													?>
														<button class="purple-btn" type="button" onclick="generatedApiPackageSlip('<?php echo $packing_slip_url;?>','<?php echo $shipmentApiToken;?>','<?php echo $box->tracking_id;?>','<?php echo $this->WebshopOrdersModel->getOrderCustomerNameByOrderId($OrderData->order_id);?>','<?php echo $currencyWebshop;?>')">Download Package Slip</button>


													<?php }
													 }
													?>
													<!-- end api inigration code -->
												</div>
												<!--  end delivery api packaging slip -->
											</td>
											<?php if($key==0){ ?>
											<td rowspan="<?php echo ($count_box>1)?$count_box:''; ?>" class="vertical-middle"><button class="white-btn" type="button" onclick="PrintShippingLabel_table(<?php echo $OrderData->order_id; ?>,<?php echo $box->order_shipment_id; ?>);">Print</button></td>
											<?php } ?>

												</tr>
												<?php
												}



											}else{
											?>
											<tr class="">
											<td rowspan="" class="vertical-middle"><?php echo $OrderData->increment_id; ?></td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											<td>-</td>
											</tr>

											<?php }



										}


										?>

										<?php
								if(isset($b2b_orders) && count($b2b_orders)>0){
									foreach($b2b_orders as $b2b_order) {

									$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$b2b_order['shop_id']),'');
									$b2b_fbc_user_id=$FbcUser->fbc_user_id;

									$b2b_args['shop_id']	=	$b2b_order['shop_id'];
									$b2b_args['fbc_user_id']	=	$b2b_fbc_user_id;

									$this->load->model('ShopProductModel');
									$this->ShopProductModel->init($b2b_args);

									if($b2b_order['is_split']=='1'){

									$SplitOrderIds=$this->ShopProductModel->getSplitChildOrderIds($b2b_order['order_id']);
											foreach($SplitOrderIds as $split_order){
												$so_id=$split_order->order_id;
												$CheckShipmentCreated=$this->ShopProductModel->getSingleDataByID('b2b_order_shipment',array('order_id'=>$so_id),'');
												if(isset($CheckShipmentCreated) && $CheckShipmentCreated->id!=''){
													$Boxes=$this->ShopProductModel->getMultiDataById('b2b_order_shipment_details',array('order_id'=>$so_id),'','id','ASC');
													$count_box=count($Boxes);
												}else{
													$count_box=0;
													$Boxes=array();
												}
													?>

												<?php if($count_box>0){


												foreach($Boxes as $key=>$box){ ?>
												<tr>
												<?php if($key==0){ ?>
												<td rowspan="<?php echo ($count_box>1)?$count_box:''; ?>" class="vertical-middle"><?php echo $split_order->increment_id; ?></td>
												<?php } ?>
												<td>Box <?php echo $box->box_number; ?></td>
												<td><?php echo $box->weight; ?></td>
												<td><span class="tracking-id-table">Condition 1<input disabled type="text" placeholder="Tracking ID" name="tracking_id[<?php echo $box->id; ?>]" class="form-control input-xs valid-alphanum" value="<?php echo (isset($box->tracking_id) && $box->tracking_id!='')?$box->tracking_id:''; ?>"><input disabled type="text" name="tracking_url[<?php echo $box->id; ?>]"  placeholder="Tracking URL"class="form-control input-xs valid-alphanum" value="<?php echo (isset($box->tracking_url) && $box->tracking_url!='')?$box->tracking_url:''; ?>"></span></td>
												<?php if($key==0){ ?>
												<td rowspan="<?php echo ($count_box>1)?$count_box:''; ?>" class="vertical-middle"><button class="white-btn" type="button" onclick="PrintShippingLabel_table(<?php echo $so_id; ?>,<?php echo $box->order_shipment_id; ?>);">Print</button></td>
												<?php } ?>

													</tr>
													<?php
													}
												}else{
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


											<?php }else{


												$CheckShipmentCreated=$this->ShopProductModel->getSingleDataByID('b2b_order_shipment',array('order_id'=>$b2b_order['order_id']),'');
												if(isset($CheckShipmentCreated) && $CheckShipmentCreated->id!=''){
													$Boxes=$this->ShopProductModel->getMultiDataById('b2b_order_shipment_details',array('order_id'=>$b2b_order['order_id']),'','id','ASC');
													$count_box=count($Boxes);
												}else{
													$count_box=0;
													$Boxes=array();
												}
													?>

												<?php if($count_box>0){


												foreach($Boxes as $key=>$box){ ?>
												<tr>
												<?php if($key==0){ ?>
												<td rowspan="<?php echo ($count_box>1)?$count_box:''; ?>" class="vertical-middle"><?php echo $b2b_order['increment_id']; ?></td>
												<?php } ?>
												<td>Box <?php echo $box->box_number; ?></td>
												<td><?php echo $box->weight; ?></td>


												<td><span class="tracking-id-table"><input disabled type="text" placeholder="Tracking ID" name="tracking_id[<?php echo $box->id; ?>]" class="form-control input-xs valid-alphanum" value="<?php echo (isset($box->tracking_id) && $box->tracking_id!='')?$box->tracking_id:''; ?>"><input disabled type="text" name="tracking_url[<?php echo $box->id; ?>]"  placeholder="Tracking URL"class="form-control input-xs valid-alphanum" value="<?php echo (isset($box->tracking_url) && $box->tracking_url!='')?$box->tracking_url:''; ?>"></span></td>
												<td>-</td>
													</tr>
													<?php
													}
												}else{
												?>
												<tr class="">
												<td rowspan="" class="vertical-middle"><?php echo $b2b_order['increment_id']; ?></td>
												<td>-</td>
												<td>-</td>
												<td>-</td>
												<td>-</td>
												</tr>

												<?php }



											}

									}

								}
							?>


									</tbody>
								</table>
							</div>
						<?php if($use_advanced_warehouse->value === 'yes'): ?>
							<h2 class="table-heading-small">Product Details</h2>
							<?php  $this->load->view('webshop/order/order-items'); ?>
						<?php else: ?>
							<?php  $this->load->view('webshop/order/all_scanned_items'); ?>
						<?php endif; ?>


							<div class="save-discard-btn confirm-shipment-btn pad-bottom-20">
								<?php if($use_advanced_warehouse->value !== 'yes'): ?>
								<button class="white-btn"   type="button" data-toggle="collapse" data-target="#collapseExamplePD" aria-expanded="false" aria-controls="collapseExamplePD">View Order Details </button>
								<?php endif; ?>
								<?php
								$OwnShopItems=$this->WebshopOrdersModel->getMultiDataById('sales_order_items',array('order_id'=>$OrderData->order_id,'product_inv_type <>'=>'dropship'),'');
								if(isset($OwnShopItems) && count($OwnShopItems)>0){
									if($OrderData->status!=6){
								?>
								<button class="purple-btn" type="button" onclick="SaveTrackingID();">Save</button>
								<?php } } ?>
							</div>
							</form>
				</div>

				<!--end form-->
			</div>
	</div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop-order-item.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script>
function sendTrackingEmail(order_id,tracking_id){
	if(order_id!='' && tracking_id != ''){

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/sendTrackingEmail",
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
	/*api receipt generated*/
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
	/*end api receipt generated*/
</script>



<?php $this->load->view('common/fbc-user/footer'); ?>
