<?php $this->load->view('common/fbc-user/header'); ?>
  <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <ul class="nav nav-pills">
  <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/customers',$this->session->userdata('userPermission'))){ ?>
	<li class=""><a href="<?php echo base_url(); ?>customers">Customer Listing</a></li>
	<?php } ?>

	<li class="active"><a data-toggle="pill" href="#customer-type-details-tab">Customer Type</a></li>
  </ul>
  
<div class="tab-content">
	<div id="customer-type-tab" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 pad-bt-40">
			<h1 class="head-name">Customer Type</h1> 
		<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/customers_type/write',$this->session->userdata('userPermission'))){ ?>
			<div class="float-right">
				<button class="purple-btn" data-toggle="modal" data-target="#create_customer_type">Create New</button>
			</div>
		<?php } ?>
		</div> <!-- form -->
		<div class="content-main form-dashboard">
			<form>
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" name="customer_type_list" id="customer_type_list">
						<thead>
							<tr>
								<th>Customer Type ID </th>
								<th>Customer Type </th>
								<th>Total Customers </th>
								<th class="no-sort">Details </th>
							</tr>
						</thead>
						<tbody>
						<?php 
						if($customer_type_details){
						foreach($customer_type_details as $key=>$val){?>
							<tr>
								<td><?php echo $val['id']?></td>
								<td><?php echo $val['name']?></td>
								<td><?php $count=$this->CustomerModel->get_customer_count($val['id']); 
								if(isset($count) && $count !='' ){echo $count;}else{ echo "-";} ?></td>
								<td><a class="link-purple" href="<?php echo base_url();?>customer-type-details/<?php echo $val['id']?>">View</a></td>
							</tr>
							
						<?php } }?>
							
						</tbody>
					</table>
				</div>

			</form>
		</div><!--end form-->
	</div> <!-- dropshipping-products -->
 </div>
 
			<div class="modal fade show" tabindex="-1" id="create_customer_type" name="create_customer_type" role="dialog">
				  <div class="modal-dialog change-pass-modal" role="document">
					<div class="modal-content">
					  <div class="modal-header">
						<h4 class="head-name">Create Customer Type</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span>
						</button>
					  </div>
					  <div class="modal-body">
						<div class="row">
						<form id="customer_type" name="customer_type" method="post" data-toggle="validator" action="<?php echo base_url();?>CustomerController/create_customer_type">
							<div class="form-group row col-sm-12">
								<label for="" class="col-sm-12 col-form-label">Customer Type <span class="required">*</span></label>
								<div class="col-sm-12">
								 <input type="text" class="form-control" id="customer_type" name="customer_type" placeholder="" required>
								<div class="error-msg"></div>
								</div>
							</div>
						<div class="modal-footer col-sm-12 ">
							<button type="submit" name="add_type" id="add_type" class="purple-btn">ADD</button>
						<!--<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>-->
						</div>
					  </form>
					</div>
				  </div>
				</div>
				</div>
			</div>
  </main>
  <script type="text/javascript" src="<?php echo SKIN_JS; ?>customer_type.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>