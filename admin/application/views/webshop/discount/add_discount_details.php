<?php $this->load->view('common/fbc-user/header'); ?> 
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <?php $this->load->view('webshop/discount/breadcrums');?>

  <div class="tab-content">
    <div id="catalogue-discounts-details-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name pad-bt-20">Discounts Details</h1> 
      </div><!-- d-flex -->
		
		  <!-- form -->
     <form name="discount-frm-add" id="discount-frm-add" method="POST" action="<?php echo base_url() ?>WebshopController/add_salesrule_discount_detail">  
      <input type="hidden" name="rules_id" id="rules_id" value="<?php if(isset($salesruleData)){ echo $salesruleData->rule_id; } ?>">
      <input type="hidden" value="<?php if(isset($salesruleData)){ echo $salesruleData->coupon_id; } ?>" id="c_id" name="c_id">
      <input type="hidden" value="<?php echo $pg_type; ?>" id="current_page" name="current_page">
      <input type="hidden" value="<?php echo $discount_type; ?>" id="current_discount_type" name="current_discount_type">

			<div class="customize-add-section">
				<div class="row">
  				<div class="left-form-sec">
  					<div class="col-sm-6 customize-add-inner-sec">
  						<label>Discount Name</label> 
  						<input class="form-control" type="text" name="discount_name" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->name :''?>" placeholder="Enter discount name">
  					</div><!-- col-sm-6 -->
  					
  					<div class="col-sm-6 customize-add-inner-sec page-content-textarea">
  						<label for="description" >Description</label>
  						<textarea class="form-control" id="description" name="description"><?php if(isset($salesruleData)){ echo $salesruleData->description; } ?></textarea>
  					</div><!-- col-sm-6 -->
		        
            <div class="col-sm-6 customize-add-inner-sec">
  						<label>Start Date</label>
  				    <input type="text" class="form-control" id="start_date" name="start_date" value="<?php echo (isset($salesruleData) && $salesruleData != '')?date('d-m-Y',strtotime($salesruleData->start_date)) : date('d-m-Y'); ?>" placeholder="Enter start date">
  					</div><!-- col-sm-6 -->

  					<div class="col-sm-6 customize-add-inner-sec">
  						<label>End Date</label>
  					  <input type="text" class="form-control" id="end_date" name="end_date" value="<?php echo (isset($salesruleData) && $salesruleData != '')?date('d-m-Y',strtotime($salesruleData->end_date)) : date('d-m-Y'); ?>" placeholder="Enter end date">
  					</div><!-- col-sm-6 -->
				  </div>
  				<div class="right-form-sec">
  					<div class="col-sm-6 customize-add-inner-sec">
  						<label>Discount Code</label>
  						<input class="form-control" type="text" name="coupon_code" id="coupon_code" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->coupon_code :''?>" placeholder="Enter Discount code">
            </div><!-- col-sm-6 -->
  										
  					<div class="col-sm-6 customize-add-inner-sec">
  						<label>Status</label>
  						<select class="form-control" name="disc_status" id="disc_status" >
                <option value="1" <?php echo (isset($salesruleData) && $salesruleData->status == 1)?'selected':''?> >Active</option>
                <option value="0" <?php echo (isset($salesruleData) && $salesruleData->status == 0)?'selected':''?> >Inactive</option>
              </select>
  					</div><!-- col-sm-6 -->
    										
  					<div class="col-sm-6 customize-add-inner-sec">
  						<label>Apply</label>
  						<select class="form-control" name="apply_percent" id="apply_percent">
                <option value="by_percent" <?php echo (isset($salesruleData) && $salesruleData->apply_type == 'by_percent')?'selected':''?>>By % of the original price</option>
               <!--  <option value="by_fixed" <?php //echo (isset($salesruleData) && $salesruleData->apply_type == 'by_fixed')?'selected':''?>>By fixed amount of the original price</option> -->
              </select>
  					</div><!-- col-sm-6 -->
    					
            <div class="col-sm-6 customize-add-inner-sec">
              <?php if(isset($salesruleData)) { 
                      $disc_lbl = ($salesruleData->apply_type == 'by_percent')?'Discount %':'Amount';
                    }else{
                      $disc_lbl = 'Discount %';
                    }
              ?>
              <label class="disc_lbl"><?php echo $disc_lbl; ?></label>
              <input type="number" class="form-control" id="discount_amnt" name="discount_amnt" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->discount_amount :''?>" placeholder="Enter discount">
            </div><!-- col-sm-6 -->
										
    				<div class="col-sm-6 customize-add-inner-sec">
  						<label>Apply To</label>
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
			
			<h1 class="head-name mar-top" style="font-weight:500;font-size:16px;">Apply on</h1>
			<div class="content-main form-dashboard">
        <div class="table-responsive text-center make-virtual-table">
          <table class="table table-bordered table-style" id="discountTableCatList"> 
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
                <?php if($discount_type != 'product'){ ?>
                <th>Details </th>
                <?php } ?>
              </tr>
            </thead>
            <tbody>
              <?php if(isset($catData) && count($catData)>0) { ?>
              <?php foreach($catData as $value) { ?>
              <tr>
                <td>
                  <label class="checkbox">
                    <input type="checkbox" <?php echo (isset($cat_arr) && in_array($value->category_id, $cat_arr)) ? 'checked' : ''; ?> class="form-control b2b-cat-check <?php echo ($value->parent_id>0)?'b2b-pc-'.$value->parent_id:'b2b-c-'.$value->category_id; ?>" name="checked_cat[]" value="<?php echo $value->category_id;?>" onclick="DiscountCheckRelatedCat(this,<?php echo $value->category_id; ?>,<?php echo $value->parent_id; ?>,<?php echo $value->level; ?>)" >
                    <span class="checked"></span>
                  </label>
                </td>
                <td><?php echo $value->cat_name; ?></td>
                <td><?php echo $value->sub_cat_name; ?></td>
                <td><?php echo $value->product_count; ?></td>
                <?php if($discount_type != 'product'){ ?>
                <td>
                  <a class="link-purple" href="<?php echo BASE_URL.'webshop/discount-product-list/'.$value->category_id;?>" target="_blank">View</a>
                </td>
                <?php } ?>
              </tr>
                <?php } } ?> 
              </tbody>
            </table>
          </div>
          <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/discounts/write',$this->session->userdata('userPermission'))){ ?>
          <div class="download-discard-small mar-top">
            <?php if(isset($salesruleData)) { ?>
            <button type="button" class="white-btn" data-toggle="modal" data-target="#deleteModal">Delete</button>
            <?php } ?>
            <?php if($discount_type == 'product'){ ?>
            <button class="download-btn" id="saveandcountinue" type="submit">Save And Continue</button>
            <?php }else{ ?>
            <button class="download-btn" type="submit">Save</button>
            <?php } ?>
          </div><!-- download-discard-small  -->
           <?php } ?>
        </div>  
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