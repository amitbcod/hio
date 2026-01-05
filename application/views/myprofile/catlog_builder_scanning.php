<?php $this->load->view('common/header'); ?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.css"/>
 
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>"><?=lang('bred_home')?></a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active"><?=lang('my_profile')?></li>
				</ul>
			</div>
        </div>
      </div><!-- breadcrum section -->
   

     <div class="my-profile-page-full">
      <div class="container">
          <div class="row">
				<?php $this->load->view('common/profile_sidebar'); ?>
				
				<div class="col-md-9 col-lg-9 ">
					<h4 class="manage-add-head"><?=lang('scanning')?>   <a href="<?php echo base_url()."customer/upc-catlog-listing" ?>"><button class="black-btn float-right"><?=lang('back_to_cat_listing')?></button></a></h4>
					
					
						<p><?=lang('total_products')?> : <strong><?php echo isset($total_scanned_products_count) ? $total_scanned_products_count : '0';    ?></strong></p>
					
					<div class="personal-info-form col-sm-12 scanning-panel">
						<form class="row">

							<div class="col-sm-12 row">
								<div class="col-sm-4 ">
									<input type="hidden" placeholder="customer_id" value="<?php echo $customer_id ?>" name="customer_id" id="customer_id" >

									<input type="text" placeholder="<?=lang('barcode')?>" id="barcode_item" name="barcode_item"  onmouseover="this.focus();" autofocus >
									<br>
									
									<input type="text" placeholder="<?=lang('product_name_sku')?>" name="sku" id="sku" >

								</div>
								<div class="col-sm-4 qty-scan">
									<input type="text" placeholder="<?=lang('quantity')?>" value="1" name="qty" id="qty">
								</div>
								<div class="col-sm-4 qty-scan">
									<button type="submit" class="btn btn-black" onclick="ScanBarcodeManually(); return false;" ><?=lang('enter_qty')?></button>
								</div>
								
							</div>


							<div class="table-responsive text-center">
								<table class="table table-bordered table-style " id="scanned_products_table">
								  <thead>
									<tr>
									  <th><?=lang('upc')?></th>
									  <th><?=lang('product_name_scanning')?> </th>
									  <th><?=lang('sku')?></th>
									  <th><?=lang('variants_scanning')?></th>
									  <th><?=lang('launch_date')?> </th>
									  <th><?=lang('quantity')?> </th>	
									  <th><?=lang('retail_price')?> </th>	
									  <th><?=lang('action')?> </th>	
									</tr>
								  </thead>
								  
								</table>
							  </div>
							
						</form>
							<div class="personal-info-btn col-sm-12 text-right">
								<a  href="<?php echo base_url()."catlog-builder/download-csv" ?>"><button name="download_csv" id="download_csv" value="<?php echo $customer_id; ?>" class="black-btn"><?=lang('download_csv')?></button></a>
							</div><!-- signin-btn -->
					</div>
				</div><!-- col-md-9 -->
				
          </div><!-- row -->
      </div><!-- container -->
    </div><!-- my-profile-page-full -->


 <script type="text/javascript" src="<?php echo SKIN_JS; ?>catlog_builder_scanning.js?v=<?php echo CSSJS_VERSION; ?>"></script>
 <script src="<?php echo SKIN_JS ?>special_features.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<?php $this->load->view('common/footer'); ?>