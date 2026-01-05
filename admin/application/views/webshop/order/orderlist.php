<?php $this->load->view('common/fbc-user/header');
$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');

?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php $this->load->view('webshop/order/breadcrums'); ?>



	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name">Webshop Orders </h1>
				<div class="float-right product-filter-div ">
					<div class="search-div d-none" id="pro-search-div">
						<input class="form-control form-control-dark top-search" id="custome-filter" type="text" placeholder="Search" aria-label="Search">
						<button type="button" class="btn btn-sm search-icon" onclick="FilterProductDataTable();"><i class="fas fa-search"></i></button>
					</div>
					<!-- filter section start -->
					<div class="filter">
						<button>
							<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z" />
							</svg>
							Filter
						</button>
					</div>
					<div class="filter-section">
						<span class="reset-arrow"><a href="javascript:void(0);" onclick="location.reload();">Reset</a></span>
						<div class="close-arrow"> <i class="fa fa-angle-left"></i> </div>

						<div class="filter filter-inside">
							<button>
								<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"></path>
								</svg>
								Filter
							</button>
						</div>

						<div class="justify-content-center my-4 status-box">
							<h3>Order Status</h3>
							<div class="col-md-12">
								<select class="form-control" name="order_status" id="order_status">
									<option value="">--Select--</option>
									<?php if ($current_tab == 'shipped-orders') { ?>
										<option value="4">Tracking Missing</option>
										<option value="5">Tracking Incomplete</option>
										<option value="6">Tracking Complete</option>
									<?php } else { ?>
										<!-- <option value="7">Pending</option> -->
										<option value="0">To be processed</option>
										<option value="1">Processing</option>
										<option value="2">Complete</option>
										<option value="3">Cancelled</option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="justify-content-center my-4 status-box">
							<h3>Payment Method</h3>
							<div class="col-md-12">
								<select class="form-control" name="payment_method" id="payment_method">
									<option value="">--Select--</option>
									<?php if ($current_tab == 'shipped-orders') { ?>
										<option value="2">Cc Avenue</option>
										<option value="8">Cheque/Funds Transfer</option>
									<?php } else { ?>
										<!-- <option value="7">Pending</option> -->
										<option value="2">Cc Avenue</option>
										<option value="8">Cheque/Funds Transfer</option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="justify-content-center my-4 price-range">
							<h3>Grand Total Price Range</h3>
							<form class="range-field w-100">
								<input id="slider11" class="border-0" value="0" type="range" min="0" max="100000" />
							</form>
							<span class="zero-value">0</span>
							<span class="font-weight-bold text-primary ml-2 mt-1 valueSpan"></span>
						</div>

						<!-- range-box -->
						<div class="justify-content-center my-4 supplier-box d-none">
							<h3>Shipment Type</h3>
							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control" name="shipment_type[]" value="1"><span class="checked"></span> Buy In</label></div>
							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control" name="shipment_type[]" value="2"><span class="checked"></span> Dropship</label></div>
						</div>
						<!-- range-box -->
						<div class="justify-content-center my-4 last-updated">
							<h3>Last Updated</h3>
							<div class="col-md-5"><input type="text" class="form-control" id="from_date"></div>
							<div class="col-md-2">To</div>
							<div class="col-md-5"><input type="text" class="form-control" id="to_date"></div>
						</div>
						<!-- range-box -->
						<div class="filter-btn-box">
							<button class="filter-btn" onclick="FilterOrdersDataTable();">Filter</button>
						</div>
					</div>
					<!-- filter section -->
					<!-- filter section close -->
				</div>
				<!-- product filter div -->
			</div>
			<!-- form -->
			<div class="content-main form-dashboard">
				<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
				<input type="hidden" id="is_warehouse" name="is_warehouse" value="<?php if ($use_advanced_warehouse->value == "yes") {
																						echo 1;
																					} else {
																						echo 0;
																					}  ?>">
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="DataTables_Table_WebshopOrders">
						<thead>
							<tr>
								<th>Order Number </th>
								<th>Purchased On </th>
								<th>Customer Name </th>
								<!--th>Webshop Name </th-->
								<th>Status </th>
								<?php
								if ($use_advanced_warehouse->value == "yes") {
									echo "<th>Warehouse Status</th>";
								}
								?>
								<th>Payment Method </th>
								<th>Coupon Code </th>
								<th>Gift Card </th>
								<th>Refund Status </th>
								<th>Print </th>
								<th>Details </th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			<!--end form-->
		</div>

	</div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_list.js"></script>
<div id="refund-order-modal-1" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Refund Order</h4>
				</div>

				<div class="modal-body">
					<h5>Are you sure you want to refund this order ?</h5>
					<form id="refund-order-form" method="POST" action="#">
						<div class="cancel-order-form">
							<div class="form-box">
								<textarea class="form-control" name="cancel_reason" id="cancel_reason" placeholder="Reason for Refund*" required="required"></textarea>
								<input type="hidden" id="refund_order_id" name="order_id" >
							</div><!-- form-box -->

							<div class="signin-btn">
								<input type="submit" class="black-btn blue-btn" name="submit" id="submit_cancel_order" value="Confirm">
								<input type="button" class="black-btn blue-btn" data-dismiss="modal" name="cancel" id="cancel" value="Cancel">
							</div><!-- signin-btn -->
						</div><!-- sigin-form -->
					</form>
				</div>
			</div>
		</div>
	</div>

<?php $this->load->view('common/fbc-user/footer'); ?>

<script type="text/javascript">
	
	function refundPayment(orderId) {
		$('#refund_order_id').val(orderId);
		$("#refund-order-modal-1").modal();
	}

	$('#refund-order-form').submit(function(e) {

		e.preventDefault();
		var fd = new FormData($('#refund-order-form')[0]);
		var order_id = (fd.get('order_id'));

		if (order_id != '') {
			$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL + "WebshopOrdersController/RefundORderRequest",
				data: fd,
				processData: false,
				contentType: false,
				//async:false,
				beforeSend: function() {
					// $('#ajax-spinner').show();
				},
				success: function(response) {
					var response1 = JSON.parse(response);

					if (response1.flag == 1) {
						console.log(response1);
						$('#refund-order-modal').modal('hide');
						console.log(response)
                        swal({
	                        title: "",
	                        icon: "success",
	                        text: response1.message,
	                        buttons: false,
                        })
                        setTimeout(function() {
                        	location.reload();

                        }, 1000);
					} else {
						//grecaptcha.reset();
						swal({
							title: "",
							icon: "error",
							text: response1.message,
							//buttons: false,
						}).then(function() {
							location.reload();
						});

						// setTimeout(function() {
						// //window.location.href = response.redirect;

						// }, 1000);
					}
				},
				error: function(error) {
					console.log(error);
				}
			});
		} else {
			return false;
		}


	});

</script>