<?php $this->load->view('common/fbc-user/header'); ?> 
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <?php $this->load->view('webshop/discount/breadcrums');?>

  <div class="tab-content">
    <div id="catalogue-discounts-details-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name pad-bt-20">Discounts Details</h1> 
      </div><!-- d-flex -->
		
		  <!-- form -->
      <form name="coupon-code-frm-add" id="coupon-code-frm-add" method="POST" action="<?php echo base_url() ?>WebshopController/add_salesrule_couponcode_discount_detail">  
        <input type="hidden" name="rules_id" id="rules_id" value="<?php if(isset($salesruleData)){ echo $salesruleData->rule_id; } ?>">
        <input type="hidden" value="<?php if(isset($salesruleData)){ echo $salesruleData->coupon_id; } ?>" id="c_id" name="c_id">
        <input type="hidden" value="<?php echo $pg_type; ?>" id="current_page" name="current_page">
        <input type="hidden" value="<?php echo $discount_type; ?>" id="current_discount_type" name="current_discount_type">

  			<div class="customize-add-section">
  				<div class="row">
    				<div class="left-form-sec coupon-code-select coupon-code-select-product-list">
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Discount Name <span class="required">*</span></label> 
    						<input class="form-control" type="text" name="discount_name" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->name :''?>" placeholder="Enter discount name">
    					</div><!-- col-sm-6 -->
    					
    					<div class="col-sm-6 customize-add-inner-sec page-content-textarea">
    						<label for="description" >Description</label>
    						<textarea class="form-control" id="description" name="description"><?php if(isset($salesruleData)){ echo $salesruleData->description; } ?></textarea>
    					</div><!-- col-sm-6 -->
  		        
              <div class="col-sm-6 customize-add-inner-sec">
    						<label>Start Date <span class="required">*</span></label>
    				    <input type="text" class="form-control" id="start_date" name="start_date" value="<?php echo (isset($salesruleData) && $salesruleData != '')?date('d-m-Y',strtotime($salesruleData->start_date)) : date('d-m-Y'); ?>" placeholder="Enter start date">
    					</div><!-- col-sm-6 -->

    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>End Date <span class="required">*</span></label>
    					  <input type="text" class="form-control" id="end_date" name="end_date" value="<?php echo (isset($salesruleData) && $salesruleData != '')?date('d-m-Y',strtotime($salesruleData->end_date)) : date('d-m-Y'); ?>" placeholder="Enter end date">
    					</div><!-- col-sm-6 -->

              <?php 
                $style = '';
                if(isset($salesruleData)) {
                  $style = ($salesruleData->apply_condition=='discount_on_mincartval') ? 'display:block':'display:none;';
                } 
              ?>

              <div class="col-sm-6 customize-add-inner-sec mar-tp-max">
                <?php if(isset($salesruleData)) { 
                        $coupon_lbl = ($salesruleData->coupon_type == 0)?'Discount':'Voucher';
                      }else{
                        $coupon_lbl = 'Discount'; 
                      }
                ?>
                <label>Coupon Type : <span class="cp_type"><?php echo $coupon_lbl; ?></span> </label> 
              </div>

              <div class="col-sm-6 customize-add-inner-sec">
                <label>Condition</label>
                <select class="form-control" name="conditions" id="cp_conditions" <?php echo (isset($pg_type) && $pg_type=='edit')?'disabled':'';?> >
                  <option value="discount_on_mincartval" <?php echo (isset($salesruleData) && $salesruleData->apply_condition == 'discount_on_mincartval')?'selected':''?>>Discount on min cart value</option>
                  <!-- <option value="buyx_getyfree" <?php echo (isset($salesruleData) && $salesruleData->apply_condition == 'buyx_getyfree')?'selected':''?>>Buy X, Get Y Free</option>
                  <option value="free_sample" <?php echo (isset($salesruleData) && $salesruleData->apply_condition == 'free_sample')?'selected':''?>>Free Sample</option> -->
                </select>
                <?php if(isset($pg_type) && $pg_type=='edit') { ?>
                <input type="hidden" name="conditions" value="<?php echo (isset($salesruleData) && $salesruleData->apply_condition!='')?$salesruleData->apply_condition:''?>" />
                <?php } ?>
              </div><!-- col-sm-6 -->
              
              <div class="condition-left-sec-min-cart-val" style="<?= $style ?>">
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Apply</label>
                  <select class="form-control" name="apply_percent" id="apply_percent">
                    <option value="by_percent" <?php echo (isset($salesruleData) && $salesruleData->apply_type == 'by_percent')?'selected':''?>>By % of the total cart price</option>
                    <option value="by_fixed" <?php echo (isset($salesruleData) && $salesruleData->apply_type == 'by_fixed')?'selected':''?>>By fixed amount of the total cart price</option>
                  </select>
                </div><!-- col-sm-6 -->
                <div class="col-sm-6 customize-add-inner-sec">
                  <?php if(isset($salesruleData)) { 
                          $disc_lbl = ($salesruleData->apply_type == 'by_percent')?'Discount %':'Amount';
                        }else{
                          $disc_lbl = 'Discount %';
                        }
                  ?>
                  <label class="disc_lbl"><?php echo $disc_lbl; ?><span class="required">*</span></label>
                  <input type="number" class="form-control" id="discount_amnt" name="discount_amnt" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->discount_amount :''?>" placeholder="Enter discount">
                </div><!-- col-sm-6 -->

                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Min Cart value<span class="required">*</span> </label>
                  <input type="number" class="form-control" name="cart_val" id="cart_val" placeholder="5000" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->min_cart_value :''?>">
                  <?php //echo $currency_symbol;?>
                </div><!-- col-sm-6 -->
              </div>

              <div class="condition-left-sec-xy" style="<?= (isset($salesruleData) && $salesruleData->apply_condition=='buyx_getyfree') ? 'display:block' : 'display: none;' ?>">
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Select Product (X)</label>
                  <input class="form-control buy_x" type="text" name="product_x" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->buyx_product :''?>" placeholder="Product SKU">
                  <span class="or-sec">Or</span>
                  <button class="white-btn" type="button" onclick="openProductListPopup('buy-x')">Select from product list</button>
                </div><!-- col-sm-6 -->
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>No. of products</label>
                  <input type="number" class="form-control" name="product_x_num" id="" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->buyx_product_qty :''?>" placeholder="Enter number">
                </div><!-- col-sm-6 -->
              
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Select Product (Y)</label>
                  <input class="form-control get_y" type="text" name="product_y" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->gety_product :''?>" placeholder="Product SKU">
                  <span class="or-sec">Or</span>
                  <button class="white-btn" type="button" onclick="openProductListPopup('get-y')">Select from product list</button> 
                </div><!-- col-sm-6 -->
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>No. of products</label>
                  <input type="number" class="form-control" name="product_y_num" id="" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->gety_product_qty :''?>" placeholder="Enter number">
                </div><!-- col-sm-6 -->
              </div>

              <div class="condition-left-sec-free-sample" style="<?= (isset($salesruleData) && $salesruleData->apply_condition=='free_sample') ? 'display:block' : 'display: none;' ?>">
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Min Cart value </label>
                  <input type="text" class="form-control" name="cart_val_free" id="cart_val_free" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->min_cart_value :''?>" placeholder="5000"><?php //echo $currency_symbol;?>
                </div><!-- col-sm-6 -->
                
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Select Free Product </label>
                  <input class="form-control get_y" type="text" name="product_free" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->gety_product :''?>" placeholder="Product SKU">
                  <span class="or-sec">Or</span>
                  <button class="white-btn" type="button" onclick="openProductListPopup('get-y')">Select from product list</button>
                </div><!-- col-sm-6 -->
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>No. of products</label>
                  <input type="number" class="form-control" name="product_free_num" id="" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->gety_product_qty :''?>" placeholder="Enter number">
                </div><!-- col-sm-6 -->
                
                <!-- <div class="col-sm-12 row">
                  <button class="white-btn">+ Add more</button>
                </div> --><!-- col-sm-6 -->
              
              </div>

  				  </div>

    				<div class="right-form-sec coupon-code-select">
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Coupon Code <span class="required">*</span></label>
    						<input class="form-control" type="text" name="coupon_code" id="coupon_code" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->coupon_code :''?>" placeholder="Enter coupon code">
              </div><!-- col-sm-6 -->
    										
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Status <span class="required">*</span></label>
    						<select class="form-control" name="disc_status" id="disc_status" >
                  <option value="1" <?php echo (isset($salesruleData) && $salesruleData->status == 1)?'selected':''?> >Active</option>
                  <option value="0" <?php echo (isset($salesruleData) && $salesruleData->status == 0)?'selected':''?> >Inactive</option>
                </select>
    					</div><!-- col-sm-6 -->
      				
              <div class="col-sm-6 customize-add-inner-sec">
                <label>Uses Per Coupon <span class="required">*</span></label>
                <input type="number" class="form-control" name="uses_per_coupon" id="uses_per_coupon" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->usge_per_coupon :''?>" placeholder="Enter uses">
              </div><!-- col-sm-6 -->

              <div class="col-sm-6 customize-add-inner-sec">
                <label>Uses per Customer <span class="required">*</span></label>
                <input type="number" class="form-control" name="uses_per_customer" id="uses_per_customer" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->usage_per_customer :''?>" placeholder="Enter uses">
              </div><!-- col-sm-6 -->

    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Apply To </label>
    						<select class="form-control" name="apply_to[]" id="apply_to" multiple>
                  <?php if(isset($customer_type) && count($customer_type)>0){
                  foreach($customer_type as $cust_type){ ?>
                    <option value="<?php echo $cust_type->id; ?>" <?php echo (isset($cust_typ_arr) && in_array($cust_type->id, $cust_typ_arr)) ? 'selected' : ''; ?> > <?php echo $cust_type->name; ?></option>
                  <?php } } ?>
                </select>
    					</div><!-- col-sm-6 -->
  				  </div>

          </div><!-- row -->
  			</div><!-- customize-add-section -->
      <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/discounts/write',$this->session->userdata('userPermission'))){ ?>
  			<div class="download-discard-small mar-top">
          <?php if(isset($salesruleData)) { ?>
            <button type="button" class="white-btn" data-toggle="modal" data-target="#deleteModal">Delete</button>
          <?php } ?>
            <button class="download-btn"  id="save_coupon" type="submit">Save</button>
        </div><!-- download-discard-small  -->
      <?php } ?>
      </form>
    </div>
  </div>
</main>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="discountDeleteForm" method="POST" action="<?= base_url('WebshopController/deleteDiscountDetail')?>">
        <input type="hidden" name="cp_ruleId" id="cp_ruleId" value="<?php if(isset($salesruleData)){ echo $salesruleData->rule_id; } ?>">
        <input type="hidden" value="<?php echo $discount_type; ?>" name="curr_discount_type">
        <div class="modal-header">
          <h1 class="head-name">Are you sure? you want to Delete Discount!</h1>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
          <button type="submit" class="download-btn">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- End -->
<script type="text/javascript">
 $(function () { 
        CKEDITOR.replace('description', {
       extraPlugins :'justify', 
        allowedContent: true, 
      });      
    });
</script>
<script src="<?php echo SKIN_JS; ?>discounts.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?> 