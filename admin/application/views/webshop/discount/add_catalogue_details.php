<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <ul class="nav nav-pills">
    <li class="active"><a href="<?= base_url('webshop/catalogue-discounts') ?>">Catalogue Discounts</a></li>
    <li><a href="#product-discounts-details-tab">Product Discounts</a></li>
    <li><a href="#coupon-code-details-tab">Coupon Code</a></li>
    <li><a href="#email-coupon-details-tab">Email Coupon</a></li>
  </ul>

  <div class="tab-content">
    <div id="catalogue-discounts-details-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name pad-bt-20">Discounts Details</h1> 
      </div><!-- d-flex -->
		
		  <!-- form -->
     <form name="discount-frm-add" id="discount-frm-add" method="POST" action="<?php echo base_url() ?>WebshopController/add_catalogue_discount_detail">        
  			<div class="customize-add-section">
  				<div class="row">
    				<div class="left-form-sec">
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Discount Name</label>
    						<input class="form-control" type="text" name="discount_name" value="" placeholder="Enter discount name">
    					</div><!-- col-sm-6 -->
    					
    					<div class="col-sm-6 customize-add-inner-sec page-content-textarea">
    						<label>Description</label>
    						<textarea class="form-control mini-text-editor" id="description" name="description"></textarea>
    					</div><!-- col-sm-6 -->
  			
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Start Date</label>
    				    <input type="text" class="form-control" id="start_date" name="start_date" value="<?php echo date('d-m-Y'); ?>" placeholder="Enter start date">
    					</div><!-- col-sm-6 -->

    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>End Date</label>
    					  <input type="text" class="form-control" id="end_date" name="end_date" value="<?php echo date('d-m-Y'); ?>" placeholder="Enter end date">
    					</div><!-- col-sm-6 -->
  				  </div>
    				<div class="right-form-sec">
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Coupon Code</label>
    						<input class="form-control" type="text" name="coupon_code" id="coupon_code" placeholder="Enter coupon code">
              </div><!-- col-sm-6 -->
    										
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Status</label>
    						<select class="form-control" name="disc_status" id="disc_status" >
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
    					</div><!-- col-sm-6 -->
    										
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Apply</label>
    						<select class="form-control" name="apply_percent" id="apply_percent">
                  <option value="by_percent">By % of the original price</option>
                  <option value="by_fixed">By fixed amount of the original price</option>
                </select>
    					</div><!-- col-sm-6 -->
    					
              <div class="col-sm-6 customize-add-inner-sec">
                <label class="disc_lbl">Discount %</label>
                <input type="number" class="form-control" id="discount_amnt" name="discount_amnt" placeholder="Enter discount">
              </div><!-- col-sm-6 -->
  										
      				<div class="col-sm-6 customize-add-inner-sec">
    						<label>Apply To</label>
    						<select class="form-control" name="apply_to[]" id="apply_to" multiple>
                  <?php if(isset($customer_type) && count($customer_type)>0){
                  foreach($customer_type as $cust_type){ ?>
                    <option value="<?php echo $cust_type->id; ?>"><?php echo $cust_type->name; ?></option>
                  <?php } } ?>
                </select>
    					</div><!-- col-sm-6 -->
  				  </div>
          </div><!-- row -->
  			</div><!-- customize-add-section -->
			
  			<h1 class="head-name mar-top" style="font-weight:500;font-size:16px;">Apply on</h1>
  			<div class="content-main form-dashboard">
          <div class="table-responsive text-center make-virtual-table">
            <table class="table table-bordered table-style" id="discountCatList">
              <thead>
                <tr>
				          <th>
                    <label class="checkbox">
                      <input type="checkbox" class="form-control"><span class="checked"></span>
                    </label>
                  </th>
                  <th>Categories </th>
                  <th>Sub - Categories </th>
                  <th>Products Available </th>
                  <th>Details </th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($catData as $value) { ?>
                <tr>
                  <td>
                    <label class="checkbox">
                      <input type="checkbox" class="form-control" name="checked_cat[]" value="<?php echo $value->category_id;?>">
                      <span class="checked"></span>
                    </label>
                  </td>
                  <td><?php echo $value->cat_name; ?></td>
                  <td><?php echo $value->sub_cat_name; ?></td>
                  <td><?php echo $value->product_count; ?></td>
                  <td>
                    <a class="link-purple" href="#">View</a>
                  </td>
                </tr>
                <?php } ?> 
              </tbody>
            </table>
          </div>
          <div class="download-discard-small mar-top">
            <button class="download-btn" type="submit">Save</button>
          </div><!-- download-discard-small  -->
        </div>  
      </form>
    </div>
	</div>
</main>
<script src="<?php echo SKIN_JS; ?>discounts.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?> 