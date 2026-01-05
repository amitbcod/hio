<?php $this->load->view('common/fbc-user/header');
$CI = &get_instance();
?>
<link href="<?php echo SKIN_CSS; ?>dashboard1.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="tab-content common-tab-section min-height-480">

		<div id="live-tab" class="tab-pane fade" style="opacity:1; display:block;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name">Dashboard </h1>
			</div>
			<!-- form -->
			<div class="content-main form-dashboard">
				<div class="dashboard-section">
					<ul class="dashboard-list">
						<!-- <li><a href=""><p><span><?php echo  isset($user_count) ? $user_count : 'No Users found'; ?></span>User Count</p></a></li> -->
						<li>
							<a href="<?php echo base_url() . "seller/warehouse" ?>">
								<p><span><?php echo  isset($product_count) ? $product_count : 'No Products found'; ?></span>Products</p>
							</a>
						</li>
						<!-- <li><a href="<?php echo base_url() . "publishers" ?>"><p><span><?php echo  isset($publisher_count) ? $publisher_count : 'No Publishers found'; ?></span>Publishers</p></a></li> -->
						<!-- <li><a href="<?php echo base_url() . "customers" ?>"><p><span><?php echo  isset($customer_count) ? $customer_count : 'No Customers found'; ?></span>Customers</p></a></li> -->
					</ul>
				</div>
			</div>
			<!--end form-->
		</div> <!-- dropshipping-products -->

	</div>
	<h1 class="head-name">Subscription list </h1>

	<div class="table-responsive text-center">
		<table class="table table-bordered table-style" id="datatableattribute">
			<thead>
				<tr>
					<th>Order Id</th>
					<th>Customer Name</th>
					<th>Phone NO.</th>
					<th>Product Name</th>
					<th>Subscription Start Date</th>
					<th>Subscription End Date</th>
					<th>Time to end</th>
					<!-- <th>Action</th> -->
				</tr>
			</thead>
			<tbody>
				<?php foreach ($get_subcription_data as $attribute) {
					$get_Order_itemid = $CI->CommonModel->getSingleDataByID('sales_order', array('order_id' => $attribute['order_id']), 'increment_id,customer_id');
					$get_product_name = $CI->CommonModel->getSingleDataByID('products', array('id' => $attribute['product_id']), 'name');
					$get_customer_name = $CI->CommonModel->getSingleDataByID('sales_order_address', array('order_id' => $attribute['order_id']), '');
					$date_diff = abs($attribute['sub_end_date'] - $attribute['sub_start_date']);
					$years = floor($date_diff / (365 * 60 * 60 * 24));
					$months = floor(($date_diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
					$days = floor(($date_diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
					$bgcolour = '';
					$now = strtotime('today');
					$ended = '';
					if ($attribute['sub_end_date'] < $now) {
						$ended = 'ended';
					}
					if ($months >= 3) {
						$bgcolour = 'bgcolor="yellow"';
					} else if ($months == 1) {
						$bgcolour = 'bgcolor="red"';
					}
					if ($ended == 'ended') {
						$bgcolour = 'bgcolor="red"';
					}
					$TTE = '';
					if ($years != 0) {
						$TTE .= $years . 'years,';
					}
					if ($months != 0) {
						$TTE .= $months . 'months,';
					}
					if ($days != 0) {
						$TTE .= $days . 'days';
					}
				?>
					<tr <?php echo $bgcolour; ?>>
						<td><?php echo $get_Order_itemid->increment_id; ?></td>
						<td><?php echo ((isset($get_customer_name->first_name) && $get_customer_name->first_name != NUll) ? $get_customer_name->first_name . ' ' . $get_customer_name->last_name : 'Name Not available'); ?></td>
						<td><?php echo ((isset($get_customer_name->mobile_no) && $get_customer_name->mobile_no != NUll) ? $get_customer_name->mobile_no  : 'Conatct Not available'); ?></td>
						<td><?php echo $get_product_name->name; ?></td>
						<td><?php echo date('d-M-Y', $attribute['sub_start_date']); ?></td>
						<td class="<?php echo $ended; ?>"><?php echo date('d-M-Y', $attribute['sub_end_date']); ?></td>
						<td><?php echo $TTE; //printf("%d years, %d months, %d days", $years, $months, $days);
							?></td>
						<!-- <td></td> -->

					</tr>
				<?php  } ?>
			</tbody>
		</table>
	</div>
</main>
<script>
	$(document).ready(function() {
		$("#datatableattribute").dataTable({
			"order": [],
			"aaSorting": [],
			"ordering": [],
			"language": {
				"infoFiltered": "",
				"search": '',
				"searchPlaceholder": "Search",
				"paginate": {
					next: '<i class="fas fa-angle-right"></i>',
					previous: '<i class="fas fa-angle-left"></i>'
				}
			},
			stateSave: true,
			ordering: false,
		});
	});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
