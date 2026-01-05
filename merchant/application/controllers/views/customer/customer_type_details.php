<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
	<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/customers',$this->session->userdata('userPermission'))){ ?>
		<li class=""><a href="<?php echo base_url(); ?>customers">Customer Listing</a></li>
	<?php } ?>
		<li class="active"><a  href="<?php  echo base_url();?>customertype">Customer Type</a></li>
	</ul>
	<div class="tab-content">
		<div id="customer-type-details-tab" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
		<!-- form -->
			<form name="type_details_form" id="type_details_form" method="post" action="<?php echo base_url();?>CustomerController/update_type_details/<?php echo $type_details['id'];?>">
			<input type="hidden" name="base" id="base" value="<?php echo base_url();?>">
			<div class="content-main form-dashboard">
				<div class="clear pad-bt-20"></div>
					<div class="row top-form-head">

						<div class="col-md-5">
							<label>Customer Type Name<span class="required">*</span></label>
							<input class="form-control" type="text" name="customer_type_val" id ="customer_type_val" value="<?php echo $type_details['name']?>" placeholder="Enter customer type" required>
						</div>
					</div>
			<div class="clear pad-bt-20"></div>
			<!-- <h2 class="table-heading-small">Exclusive Discounts and Coupons</h2>
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="discount_Coupons_table">
						<thead>
						<tr>
							<th>Coupon Code </th>
							<th>Discount name </th>
							<th>Start Date</th>
							<th>End Date</th>
							<th>Status</th>
							<th>Details</th>
						 </tr>
						</thead>
						<tbody>
							<?php
							// if(isset($salesrule_info) && $salesrule_info !=''){
							// 	foreach ($salesrule_info as  $salesrule) { ?>

							<tr>
								<td><?php //echo $salesrule['coupon_code']; ?></td>
								<td><?php //echo $salesrule['name']; ?></td>
								<td><?php //echo date("d/m/Y" ,strtotime($salesrule['start_date'])); ?></td>
								<td><?php //echo date("d/m/Y" ,strtotime($salesrule['end_date'])); ?></td>
								<td><?php //if($salesrule['status'] == 1)echo "Active" ; else echo "Inactive" ;?></td>
								<td><a class="link-purple" href="<?php //echo base_url(); ?>webshop/coupon-discounts/edit/<?php //echo $salesrule['rule_id']; ?>">View</a></td>
							</tr>
							<?php 	/*}
							}*/
							 ?>
						</tbody>
					</table>
				</div>
				<div class="clear pad-bt-20"></div> -->

				<h2 class="table-heading-small">Members</h2>
					<div class="table-responsive text-center">
						<table class="table table-bordered table-style" id="members_table">
						<thead>
						 <tr>
							<th>Customer Name </th>
							<th>Last Purchased On </th>
							<th>Email ID</th>
							<th>Address</th>
							<th>Details</th>
						</tr>
						</thead>
						<tbody>
							<?php if(isset($customers_by_type) && $customers_by_type !='') {
							foreach ($customers_by_type as  $value) { ?>
								<tr>
									<td><?php echo $value['first_name'].' '.$value['last_name'] ; ?></td>
									 <td><?php echo date("d/m/Y" ,$value['created_at']);?></td>
									<td><?php echo $value['email_id'] ; ?></td>
									<td><?php echo $value['city'].','.$value['state']; ?></td>
									<td><a class="link-purple" href="<?php echo base_url();?>CustomerController/customer_details/<?php echo $value['id'];?>">View</a></td>
								</tr>
						<?php	} } ?>
						</tbody>
						</table>
					</div>
		<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/customers_type/write',$this->session->userdata('userPermission'))){ ?>
					<div class="download-discard-small">
						<button class="download-btn">Save</button>
					</div><!-- download-discard-small  -->
		<?php } ?>
			</form>
			</div>
		</div> <!-- dropshipping-products -->
	</div>
</main>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>customer_type.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
