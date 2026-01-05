<?php
$getProducts = $data['ProductData'];
$OrderData = $data['OrderData'];
$PublisherPayment = $data['PublisherPayment'];

// echo "<pre>";
// print_r($OrderData);
// die;
$ids =  array();
foreach ($PublisherPayment as $item) {
	$payment_initiated = $item['payment_initiated'];
	$payment_done = $item['payment_done'];
	$ids[] = $item['id'];
}
$id = implode(',', $ids);

$order_ids = array();
$statuss = array();
$increment_ids = array();
$publisher_ids = array();


foreach ($OrderData as $item) {
	$order_ids[] = $item['webshop_order_id'];
	$statuss[] = $item['status'];
	$increment_ids[] = $item['increment_id'];
	$publisher_ids[] = $item['publisher_id'];
}

$order_id = implode(',', $order_ids);
$status = implode(',', $statuss);
$increment_id = implode(',', $increment_ids);
$publisher_id = implode(',', $publisher_ids);

// echo "<pre>";
// print_r($order_id);
// die;

?>
<?php $this->load->view('common/fbc-user/header');
$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');

?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			<input type="hidden" id="order_id" name="order_id" value="<?php echo $order_id; ?>">
			<input type="hidden" id="status" name="status" value="<?php echo $status; ?>">
			<input type="hidden" id="increment_id" name="increment_id" value="<?php echo $increment_id; ?>">
			<input type="hidden" id="publisher_id" name="publisher_id" value="<?php echo $publisher_id; ?>">


			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name">HBR Listing</h1>
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
									<!-- <?php if ($current_tab == 'shipped-orders') { ?>
										<option value="4">Tracking Missing</option>
										<option value="5">Tracking Incomplete</option>
										<option value="6">Tracking Complete</option>
									<?php } else { ?>
										<option value="0">To be processed</option>
										<option value="1">Processing</option>
										<option value="2">Complete</option>
										<option value="3">Cancelled</option>
									<?php } ?> -->
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
				<!-- <input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
				<input type="hidden" id="is_warehouse" name="is_warehouse" value="<?php if ($use_advanced_warehouse->value == "yes") {
																						echo 1;
																					} else {
																						echo 0;
																					}  ?>"> -->
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="DataTables_Table_WebshopOrders">
						<thead>
							<tr>
								<th>Order Id</th>
								<th>SKU</th>
								<th>Product Name</th>
								<th>Variants </th>
								<th>Ordered Date </th>
								<!-- <th>End Date </th> -->
								<th>Qty Ordered </th>
								<!-- <th class="<?php echo ($current_tab == 'split-order' && $OrderData->system_generated_split_order == 1) ? '' : 'd-none'; ?>">Qty Delivered </th> -->
								<!-- <th class="">Inventory</th> -->
								<!-- <th class="<?php echo ($current_tab == 'split-order' && $OrderData->system_generated_split_order == 1) ? '' : 'd-none'; ?>">Qty Pending</th> -->
								<th>Qty Scanned </th>
								<th>Location </th>
								<th>Price/Piece </th>
								<th>Total Price </th>
							</tr>
						</thead>
						<tbody>
							<?php if (isset($getProducts) && count($getProducts) > 0) {
								// echo "<pre>";
								// print_r($getProducts);
								// die;
								foreach ($getProducts as $item) {
									$total_price = 0;
									$item_class = '';
									$location_prod = $this->B2BOrdersModel->getProdLocation($item['product_id']);
									// print_r($item->order_id);
									// print_r($item->product_id);

									// if ($current_tab == 'split-order') {
									// 	$main_oi_qty = $this->B2BOrdersModel->getMainOrderItemQty($OrderData->main_parent_id, $item->product_id);
									// 	$qty_ordered = $main_oi_qty;
									// 	$pending_qty = $this->B2BOrdersModel->getPendingQtyToBeScanned($OrderData->main_parent_id, $item->product_id, 1);
									// 	$TotalRowShipped = $this->B2BOrdersModel->getShippedSingleOrderItems($OrderData->main_parent_id, $item->product_id, 1);
									// 	$delivered_qty = $TotalRowShipped->qty_scanned;
									// } else {
									// $salesOrderItemsData = $this->B2BOrdersModel->getsalesOrderItemsData($OrderData->webshop_order_id, $item->product_id);
									// 	// echo "<pre>";
									// 	// print_r($salesOrderItemsData);
									// 	// die;
									// 	// echo "<pre>";
									// 	// print_r($OrderItems);
									// 	// die;
									// 	// $CI = &get_instance();
									// 	$product_id = $item->product_id;
									// 	// Assuming $item is part of a loop or an array

									// 	// // Get attributes for the product
									// 	$get_attributes = $this->B2BOrdersModel->get_attributes($item->product_id);
									// 	// echo "<pre>";
									// 	// print_r($get_attributes);

									// 	// // Get order items for the product
									// 	$get_order_items = $this->B2BOrdersModel->get_order_items($item->product_id);
									// 	// echo "<pre>";
									// 	// print_r($get_order_items);

									// 	// Assuming $salesOrderItemsData is available
									// 	// $publisher_id = isset($salesOrderItemsData['publisher_id']) ? $salesOrderItemsData['publisher_id'] : null;

									// 	// // Check if $item->order_id is set before using it
									// 	// // echo $item->order_id;
									// 	// if (isset($item->order_id)) {
									// 	// 	// Get publisher information for the order
									// 	// 	$get_publisher = $this->B2BOrdersModel->get_publisher($item->order_id);
									// 	// 	echo "<pre>";
									// 	// 	print_r($get_publisher);
									// 	// } else {
									// 	// 	echo "Order ID is not set for the current item.";
									// 	// }

									// 	// die;
									// $salesOrderItemsID = $salesOrderItemsData['item_id'];
									$qty_ordered = $item['qty_ordered'];
									// 	$pending_qty = $this->B2BOrdersModel->getPendingQtyToBeScanned($OrderData->order_id, $item->product_id, '');
									// 	$TotalRowShipped = $this->B2BOrdersModel->getShippedSingleOrderItems($OrderData->order_id, $item->product_id);
									// 	$delivered_qty = $TotalRowShipped->qty_scanned;
									// }
									// function areAttributesSame($productArray, $attrId)
									// {
									// 	$attributes = [];

									// 	foreach ($productArray as $item) {
									// 		$productId = $item['product_id'];
									// 		$attrValue = $item['attr_value'];

									// 		// If the product_id and attr_id match, store the attribute value
									// 		if (!isset($attributes[$productId][$attrId])) {
									// 			$attributes[$productId][$attrId] = $attrValue;
									// 		} else {
									// 			// If the attribute value is different, return false
									// 			if ($attributes[$productId][$attrId] !== $attrValue) {
									// 				return false;
									// 			}
									// 		}
									// 	}

									// 	// If the loop completes without returning false, attributes are the same
									// 	return true;
									// }


									// $attrIdToCheck = 5;

									// if ($get_attributes !== null && is_array($get_attributes)) {
									// 	$attrIdToCheck = 5;

									// 	if (areAttributesSame($get_attributes, $attrIdToCheck)) {
									// 		// echo "Attributes are the same for the given products.";
									// 	} else {
									// 		// echo "Attributes are different for the given products.";
									// 	}
									// } else {
									// 	// echo "Attributes not available for the given product.";
									// }


									// die;

									// if ($item->qty_scanned <= 0) {
									// 	$item_class = 'black-row';
									// } elseif (($current_tab == 'order') && ($item->qty_scanned == $qty_ordered)) {
									// 	$item_class = 'green-row';
									// } elseif (($current_tab == 'split-order') && ($item->qty_scanned == $qty_ordered)) {
									// 	$item_class = 'green-row';
									// } elseif ($item->qty_scanned < $qty_ordered) {
									// 	$item_class = 'orange-row';
									// }

									// if (($current_tab == 'split-order' && $OrderData->system_generated_split_order == 0) || ($current_tab == 'create-shipment')) {
									// 	$item_class = 'black-row';
									// }

									if (isset($item) && isset($item['created_at'])) {
										$newSubStartDate = date(SIS_DATE_FM, $item['created_at']);
									} else {
										$newSubStartDate = '-';
									}

									// if (isset($salesOrderItemsData) && isset($salesOrderItemsData['sub_end_date'])) {
									// 	$newSubEndDate = date(SIS_DATE_FM, $salesOrderItemsData['sub_end_date']);
									// } else {
									// 	$newSubEndDate = '-';
									// }

									// print_r($newSubStartDate);

									// print_r($newSubEndDate);
									// die;
									// if ($newSubStartDate == '' || $newSubStartDate == '0' || $newSubStartDate == '01/01/1970' || $newSubEndDate == '-3600') {
									// 	$newSubStartDate = '-';
									// }
									// if ($newSubEndDate == '' || $newSubEndDate == '0' || $newSubEndDate == '01/01/1970' || $newSubEndDate == '-3600') {
									// 	$newSubEndDate = '-';
									// }

									if ($item['qty_scanned'] <= 0) {
										$total_price = 0;
									} else {
										$total_price = $item->price * $item['qty_scanned'];
									}


									$variant_html = '';
									if ($item['product_type'] == 'conf-simple') {
										$product_variants = $item['product_variants'];
										if (isset($product_variants) && $product_variants != '') {
											$variants = json_decode($product_variants, true);
											if (isset($variants) && count($variants) > 0) {
												foreach ($variants as $pk => $single_variant) {
													foreach ($single_variant as $key => $val) {
														$variant_html .= '<span class="variant-item">' . $key . ' - ' . $val . '</span><br>';
													}
												}
											}
										} else {
											$variants = '-';
										}
									} else {
										$variants = '-';
									}
							?>
									<tr class="<?php echo $item_class; ?>" id="oi-single-<?php echo $item['item_id']; ?>">

										<td><?php echo $item['order_barcode']; ?></td>
										<td><?php echo $item['sku']; ?></td>
										<td><?php echo $item['product_name']; ?></td>
										<td><?php echo ($item['product_type'] == 'conf-simple') ? $variant_html : '-'; ?>
											<button type="button" class="btn btn-primary change-add-n-btn" style="position: static;right: 0;margin-left: 5px;">
												<i class="fas fa-edit"></i>
											</button>
										</td>
										<td><?php echo $newSubStartDate; ?></td>
										<!-- <td><?php echo $newSubEndDate; ?></td> -->
										<td><?php echo  $qty_ordered; ?></td>
										<!-- <td><?php echo  $item['qty']; ?></td> -->

										<td><?php echo $item['qty_scanned']; ?></td>
										<td><?php echo $location_prod; ?></td>
										<td><?php echo  number_format($item['price'], 2); ?></td>
										<td><?php echo ($item['qty_scanned'] <= 0) ? '0' :  number_format($total_price, 2); ?></td>
									</tr>
							<?php }
							} ?>
						</tbody>
					</table>
				</div>
			</div>
			<!--end form-->
			<?php
			// foreach ($OrderData as $item) {
			?><?php // }
				?>
			<?php if ($status == 4 || $status == 5 || $status == 6 || $status == 3) {
			} else { ?>
				<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
					<div class="save-discard-btn pad-bottom-20">
						<?php
						if (isset($payment_initiated) && $payment_initiated == 2 ) {
							// print_R($PublisherPayment);
						?>
							<button name="cancel_order_btn" class="purple-btn" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $order_id; ?>" value="<?php echo $order_id; ?>" data-target="#cancel-order-modal">Cancel Order</button>
							<button class="purple-btn" id="initiate-payment-btn" onclick="Initiatepayment('<?php echo $order_id; ?>','<?php echo $increment_id; ?>','<?php echo $publisher_id; ?>' );">Initiate Payment</button>
						<?php
						} else if (empty($PublisherPayment)) {
						?>
							<button name="cancel_order_btn" class="purple-btn" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $order_id; ?>" value="<?php echo $order_id; ?>" data-target="#cancel-order-modal">Cancel Order</button>
							<button class="purple-btn" id="initiate-payment-btn" onclick="Initiatepayment('<?php echo $order_id; ?>','<?php echo $increment_id; ?>','<?php echo $publisher_id; ?>' );">Initiate Payment</button>
						<?php
						}
						?>

						<?php
						if (isset($payment_initiated) && $payment_initiated == 1 && $payment_done == 2) {
							// print_R($PublisherPayment);die;
						?>
							<button class="purple-btn" id="initiate-payment-btn" onclick="proceedpayment('<?php echo $id ?>','<?php echo $order_id; ?>','<?php echo $increment_id; ?>','<?php echo $publisher_id; ?>' );">Procced To Pay</button>
						<?php
						}
						?>
						<!-- <button class="purple-btn" id="initiate-payment-btn" onclick="Initiatepayment('<?php echo $order_id; ?>','<?php echo $increment_id; ?>','<?php echo $publisher_id; ?>' );">Initiate Payment</button> -->

						<button class="purple-btn" disabled="" id="confirm-order-btn" disabled onclick="ConfirmOrder(<?php echo $order_id; ?>);">Confirm Order </button>

						<!-- <button class="purple-btn" type="button" id="split-order-btn" onclick="OpenSplitOrderPopup(<?php echo $order_id; ?>);">Split Order </button> -->
					</div>
				<?php } ?>
			<?php } ?>
		</div>

	</div>
</main>
<!-- <script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_list.js"></script> -->


<?php $this->load->view('common/fbc-user/footer'); ?>
<script type="text/javascript">
	$(document).ready(function() {
		var table = $("#DataTables_Table_WebshopOrders").DataTable();
		table.destroy();
		$("#DataTables_Table_WebshopOrders").DataTable({
			"order": [], // Initial no order.
			"language": {
				"infoFiltered": "",
				"search": '',
				"searchPlaceholder": "Search",
				"paginate": {
					next: '<i class="fas fa-angle-right"></i>',
					previous: '<i class="fas fa-angle-left"></i>'
				}
			}
		});
		$("#initiate_payment_form").submit(function(event) {
			// alert("Handler for .submit() called.");
			event.preventDefault();
		});
	});

	function initiatepaymentform() {
		console.log("initiatepaymentform");
		$('#initiate_payment_form').validate({
			ignore: [],
			rules: {
				beneficiary_name: {
					required: true,
				},
				bene_acc_no: {
					required: true,
				},
				bene_ifsc_code: {
					required: true,
				},
			}


		});
		var form = $('#initiate_payment_form');
		$.ajax({
			url: BASE_URL + "B2BOrdersController/HBRInitiateOrder",
			type: "POST",
			datatype: 'json',
			data: form.serialize(),
			success: function(response) {
				$('#modal').modal('hide');
				var response = jQuery.parseJSON(response);
				// console.log(response.message,response,typeof(response));
				if (response.status == 200) {
					swal({
							title: "Success",
							icon: "success",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				} else {

					swal({
							title: "warning",
							html: true,
							icon: "error",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				}

			}
		});
		// code
	}

	function Initiatepayment(order_id, inc_id, publisher_id) {
		// var order_id = document.getElementById("order_id").value;
		// var order_id = document.getElementById("order_id").value;
		// alert('orderid' + order_id);
		// alert('inc_id' + inc_id);
		// alert('publisher_id' + publisher_id);

		// return false;
		// var product_id = document.getElementById("product_id").value;
		// var publisher_id = document.getElementById("publisher_id").value;

		if (order_id != '') {
			$.ajax({
				url: BASE_URL + "B2BOrdersController/HBRInitiateOrderPopup",
				type: "POST",
				data: {
					order_id: order_id,
					inc_id: inc_id,
					publisher_id: publisher_id,
					// product_id: product_id

				},
				success: function(response) {
					if (response != 'error') {
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					} else {
						return false;
					}

				}
			});
		} else {
			return false;
		}

	}

	function proceedpayment(id, order_id, inc_id, publisher_id) {
		// alert(order_id);
		// return false;
		if (order_id != '') {
			$.ajax({
				url: BASE_URL + "B2BOrdersController/HBRProceedPaymentPopup",
				type: "POST",
				data: {
					id: id,
					order_id: order_id,
					inc_id: inc_id,
					publisher_id: publisher_id,
				},
				success: function(response) {
					if (response != 'error') {
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					} else {
						return false;
					}

				}
			});
		} else {
			return false;
		}

	}


	$(document).on('click', '#categorybtn', function(event) {
		// alert("Handler for .submit() called.");
		event.preventDefault();
		console.log(this);
		$('#initiate_payment_form').validate({
			ignore: [],
			rules: {
				beneficiary_name: {
					required: true,
				},
				bene_acc_no: {
					required: true,
				},
				bene_ifsc_code: {
					required: true,
				},
			}


		})
		var form = $('#initiate_payment_form');
		// $.ajax({
		// 	url: BASE_URL + "B2BOrdersController/InitiateOrder",
		// 	type: "POST",
		// 	datatype: 'json',
		// 	data: form.serialize(),
		// 	success: function(response) {
		// 		$('#modal').modal('hide');
		// 		var response = jQuery.parseJSON(response);
		// 		// console.log(response.message,response,typeof(response));
		// 		if (response.status == 200) {
		// 			swal({
		// 					title: "Success",
		// 					icon: "success",
		// 					text: response.message,
		// 					buttons: true,
		// 				},
		// 				function() {
		// 					location.reload();
		// 				})
		// 		} else {

		// 			swal({
		// 					title: "warning",
		// 					html: true,
		// 					icon: "error",
		// 					text: response.message,
		// 					buttons: true,
		// 				},
		// 				function() {
		// 					location.reload();
		// 				})
		// 		}

		// 	}
		// });
		// code
	});

	function paymentdone(id) {
		// alert(id);
		$.ajax({
			url: BASE_URL + "B2BOrdersController/HBRPaymentDone",
			type: "POST",
			data: {
				id: id,
				utr_no: utr_no
			},
			success: function(response) {
				$('#modal').modal('hide');
				var response = jQuery.parseJSON(response);
				// console.log(response.message,response,typeof(response));
				if (response.status == 200) {
					swal({
							title: "Success",
							icon: "success",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				} else {

					swal({
							title: "warning",
							html: true,
							icon: "error",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				}

			}
		});
	}
	$(document).on('submit', '#payment_done_form', function(event) {
		// alert("Handler for .submit() called.");
		event.preventDefault();
		console.log(this);
		$('#payment_done_form').validate({
			ignore: [],
			rules: {
				beneficiary_name: {
					required: true,
				},
			}

		})
		var form = $('#payment_done_form');
		$.ajax({
			url: BASE_URL + "B2BOrdersController/HBRPaymentDone",
			type: "POST",
			datatype: 'json',
			data: form.serialize(),
			success: function(response) {
				$('#modal').modal('hide');
				var response = jQuery.parseJSON(response);
				// console.log(response.message,response,typeof(response));
				if (response.status == 200) {
					swal({
							title: "Success",
							icon: "success",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				} else {

					swal({
							title: "warning",
							html: true,
							icon: "error",
							text: response.message,
							buttons: true,
						},
						function() {
							location.reload();
						})
				}

			}
		});
		// code
	});

	$(document).on('submit', 'form#initiate_payment_form', function(event) {
		// alert("Handler for .submit() called.");
		event.preventDefault();
		console.log(this);
		$(this).validate({
			ignore: [],

			rules: {
				beneficiary_name: {
					required: true,
				},
			}

		})
		// code
	});


	$('#cancel-order-form').submit(function(e) {

		e.preventDefault();
		var fd = new FormData($('#cancel-order-form')[0]);
		var order_id = (fd.get('order_id'));

		if (order_id != '') {
			$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL + "B2BOrdersController/HBRCancelORderRequest",
				data: fd,
				processData: false,
				contentType: false,
				//async:false,
				beforeSend: function() {
					// $('#ajax-spinner').show();
				},
				success: function(response) {
					console.log(response);
					var response1 = JSON.parse(response);
					if (response1.flag == 1) {
						$('#cancel-order-modal').modal('hide');
						swal({
							title: "",
							icon: "success",
							text: response1.message,
							//buttons: false,
							timer: 3000
						}).then(function() {
							location.reload();
						});

					} else {
						//grecaptcha.reset();
						swal({
							title: "",
							icon: "error",
							text: response1.message,
							//buttons: false,
							timer: 3000
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
