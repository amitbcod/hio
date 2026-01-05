<?php $this->load->view('common/header'); ?>
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
					<h4 class="manage-add-head"><?=lang('upload_upc_file')?>   <a href="<?php echo base_url()."customer/upc-catlog-listing" ?>"><button class="black-btn float-right"><?=lang('back_to_cat_listing')?></button></a></h4>
					
					
						<p><?=lang('cat_desc')?>.</p>
					
					<div class="personal-info-form col-sm-12 upload-upc-file">
						<form class="row" id="create-catlog-form" method="POST" action="<?php echo BASE_URL;?>SpecialFeaturesController/submit_catlog">
							<div class="col-sm-6 ">
								<label><?=lang('csv_file')?></label>
								<input type="file" placeholder="" name="upc_file" id="upc_file" value="" accept=".csv" >
							</div>
						
							<div class="col-sm-12 row">
								<div class="col-sm-6 ">
									<label><?=lang('catalog_name')?></label>
									<input type="text" name="catalog_name" id="catalog_name" placeholder="" value="" >
								</div>
								<div class="col-sm-6 ">
									<label><?=lang('customer_name')?></label>
									<input type="text" name="customer_name" id="customer_name" placeholder="" value="<?= (isset($customerData->first_name) && $customerData->first_name != '' && isset($customerData->last_name) && $customerData->last_name != '')?$customerData->first_name.' '.$customerData->last_name :'' ?>" >
								</div>
								<div class="col-sm-6 ">
									<label><?=lang('customer_email')?></label>
									<input type="text" placeholder="" name="email" id="email" value="<?= (isset($customerData->email_id) && $customerData->email_id != '')?$customerData->email_id :'' ?>" >
								</div>
								<div class="col-sm-6 ">
									<label><?=lang('phone_no')?></label>
									<input type="text" placeholder="" value="<?= (isset($customerData->mobile_no) && $customerData->mobile_no != '')?$customerData->mobile_no :'' ?>" name="phone_no" id="phone_no">
								</div>
							</div>
							
							<div class="col-sm-12">
								
								<div class="checkbox">
									<label><?=lang('show_website_qty')?> <input type="checkbox" name="show_qtys" id="show_qtys"></label>
									
								</div>
							</div>

							<div class="col-sm-12">
								
								<div class="checkbox">
									<label><?=lang('show_retail_price')?> <input type="checkbox" name="show_retail_price" id="show_retail_price"></label>
									
								</div>
							</div>

							<div class="col-sm-12">
								
								<div class="checkbox">
									<label><?=lang('show_coll_name')?> <input type="checkbox" name="show_coll_name" id="show_coll_name"></label>
									
								</div>
							</div>

							<div class="col-sm-12">
								
								<div class="checkbox">
									<label><?=lang('show_style_code')?> <input type="checkbox" name="show_style_code" id="show_style_code" ></label>
									
								</div>
							</div>

							<div class="col-sm-12 last-barcode">
								
								<div class="checkbox">
									<label><?=lang('show_barcode_code')?> <input type="checkbox" name="show_upc" id="show_upc"></label>
									
								</div>
							</div>

							<div class="col-sm-6">
								<label><?=lang('sort_by')?></label>
								<select name="sort_by" id="sort_by">
									<option value="0"><?=lang('category')?></option>
									<!-- <option value="1">Collection</option> -->
								</select>
							</div>
							<div class="col-sm-6">
								<label><?=lang('display_currency')?></label>
								<select name="display_currency" id="display_currency">
									<option value="<?= (isset($currency_code) && $currency_code != '')?$currency_code :'' ?>"><?php echo (isset($currency_name) && $currency_name != '') ? $currency_name :'' ?></option>
								</select>
							</div>
							
	
							<div class="personal-info-btn col-sm-12">
								<button type="submit" class="black-btn" ><?=lang('upod_create_catalog')?></button>
								<!-- <a style="color:#fff" href="images/upc-catalog-new.jpg">Upload & Create Catalog</a> -->
								<!-- <button class="black-btn"><a style="color:#fff" href="images/upc-catalog-new.jpg">Upload & Create Catalog</a></button> -->
								
							</div><!-- signin-btn -->
						</form>
					</div>
				</div><!-- col-md-9 -->
				
          </div><!-- row -->
      </div><!-- container -->
    </div><!-- my-profile-page-full -->

<?php $this->load->view('common/footer'); ?>
   
 <script src="<?php echo SKIN_JS ?>special_features.js?v=<?php echo CSSJS_VERSION; ?>"></script>
