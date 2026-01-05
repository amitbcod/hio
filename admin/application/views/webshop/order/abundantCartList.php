<?php
$getProducts = $data['ProductData'];
// echo "<pre>";
// print_r($getProducts);
// die;
// foreach ($getProducts as &$item) {

// }



// Format the timestamp
// $formattedDate = date('d/m/Y | h:i A', $timestamp);

// Display the result
// echo $formattedDate;
// die;

// echo "<pre>";
// print_r($getProducts);
// die;
?>
<?php $this->load->view('common/fbc-user/header');
$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'use_advanced_warehouse'), 'value');

?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name">Abandoned Cart</h1>
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
										<option value="0">To be processed</option>
										<option value="1">Processing</option>
										<option value="2">Complete</option>
										<option value="3">Cancelled</option>
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
								<th>Quote Id </th>
								<th>Last Updated At </th>
								<th>Customer Name </th>
								<th>Customer Email</th>
								<th>Customer Mobile No</th>
								<!-- <th>Coupon Code </th>
								<th>Voucher Code </th> -->
								<!-- <th>Print </th> -->
								<th>Details </th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($getProducts as $Product) {
								// $formattedDate = date('d/m/Y | h:i A', $Product['created_at']);
								$formattedDate = date('d/m/Y | h:i A', $Product['updated_at']);
							?>
								<tr>
									<td><?php echo $Product['quote_id']; ?></td>
									<td><?php echo $formattedDate; ?></td>
									<?php
									if ($Product['first_name'] !== null && $Product['first_name'] !== "") { ?>
										<td><?php echo $Product['first_name'] . ' ' . $Product['last_name']; ?></td>
									<?php } else { ?>
										<td>-</td>

									<?php }
									?>
									<?php
									if ($Product['email_id'] !== null && $Product['email_id'] !== "") { ?>
										<td><?php echo $Product['email_id']; ?></td>
									<?php } else { ?>
										<td>-</td>

									<?php }
									?>
									<?php
									if ($Product['mobile_no'] !== null && $Product['mobile_no'] !== "") { ?>
										<td><?php echo $Product['mobile_no']; ?></td>
									<?php } else { ?>
										<td>-</td>

									<?php }
									?>
									<!-- <?php
									if ($Product['coupon_code'] !== null && $Product['coupon_code'] !== "") { ?>
										<td><?php echo $Product['coupon_code']; ?></td>
									<?php } else { ?>
										<td>-</td>

									<?php }
									?>
									<?php
									if ($Product['voucher_code'] !== null && $Product['voucher_code'] !== "") { ?>
										<td><?php echo $Product['voucher_code']; ?></td>
									<?php } else { ?>
										<td>-</td>

									<?php }
									?> -->

									<!-- <td><?php echo $Product['email_id']; ?></td> -->
									<!-- <td><?php echo $Product['coupon_code']; ?></td> -->
									<!-- <td><?php echo $Product['voucher_code']; ?></td> -->
									<td>
										<a class="link-purple" href="<?php echo base_url('WebshopOrdersController/abundantCartDetails/') . $Product['quote_id'] ?>">View</a>
									</td>
								</tr>
							<?php  } ?>
						</tbody>
					</table>
				</div>
			</div>


			<!--end form-->
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

	});
</script>