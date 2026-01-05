<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <?php $this->load->view('webshop/discount/breadcrums');?>

  <div class="tab-content"> 
    <div id="catalogue-discounts-details-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name pad-bt-20">Discounts Details</h1> 
      </div><!-- d-flex -->
		
		  <!-- form -->
      <form name="coupon-code-frm-add" id="coupon-code-frm-add" method="POST" action="<?php echo base_url() ?>WebshopController/add_salesrule_email_discount_detail">  
        <input type="hidden" name="rules_id" id="rules_id" value="<?php if(isset($salesruleData)){ echo $salesruleData->rule_id; } ?>">
        <input type="hidden" value="<?php if(isset($salesruleData)){ echo $salesruleData->coupon_id; } ?>" id="c_id" name="c_id">
        <input type="hidden" value="<?php echo $pg_type; ?>" id="current_page" name="current_page">
        <input type="hidden" value="<?php echo $discount_type; ?>" id="current_discount_type" name="current_discount_type">

  			<div class="customize-add-section">
  				<div class="row">
    				<div class="left-form-sec coupon-code-select coupon-code-select-product-list">
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
                <input type="text" class="form-control" id="start_date" name="start_date" value="<?php echo (isset($salesruleData) && $salesruleData != '')?date('d-m-Y',strtotime($salesruleData->start_date)) : date('d-m-Y'); ?>" placeholder="Enter start date" <?php echo $non_editable_field; ?>>
              </div><!-- col-sm-6 -->
  		        
             </div>
           
    				<div class="right-form-sec coupon-code-select">
    					<div class="col-sm-6 customize-add-inner-sec">
    						<div class="customize-add-radio-section row coupon-code-radio unique-code-block">
                  <div class="radio col-sm-12">
                    <label><input type="radio" name="cpradio" value="1" <?php echo (isset($salesruleData) && $salesruleData->email_coupon_type == 1)?'checked':''?> <?php echo $non_editable_field; ?> >Unique Code <span class="checkmark"></span></label>
                    <label class="prefix_lb">Prefix if any</label>
                    <input class="form-control" type="text" maxlength="4" name="prefix" id="prefix" value="<?php echo (isset($salesruleData) && $salesruleData->email_coupon_type == 1)?$salesruleData->coupon_code_prefix :''?>" placeholder="Enter prefix" disabled <?php echo $non_editable_field; ?> >
                  </div><!-- radio -->
                  <div class="radio col-sm-12 radio-input-sec">
                    <label>
                      <input type="radio" name="cpradio" value="0" <?php echo (isset($salesruleData) && $salesruleData->email_coupon_type == 0)?'checked':''?> <?php if(!isset($salesruleData)) { echo 'checked'; } ?> <?php echo $non_editable_field; ?> >Fixed Code <span class="checkmark"></span></label>

                      <input class="form-control" type="text" name="coupon_code" id="coupon_code" value="<?php echo (isset($salesruleData) && $salesruleData->email_coupon_type == 0)?$salesruleData->coupon_code :''?>" placeholder="Enter coupon code" <?php echo $non_editable_field; ?> >
                  </div><!-- radio -->
                </div>
              </div><!-- col-sm-6 -->
    										
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Status</label>
    						<select class="form-control" name="disc_status" id="disc_status" >
                  <option value="1" <?php echo (isset($salesruleData) && $salesruleData->status == 1)?'selected':''?> >Active</option>
                  <option value="0" <?php echo (isset($salesruleData) && $salesruleData->status == 0)?'selected':''?> >Inactive</option>
                </select>
    					</div><!-- col-sm-6 -->

      				<div class="col-sm-6 customize-add-inner-sec">
                <label>End Date</label>
                <input type="text" class="form-control" id="end_date" name="end_date" value="<?php echo (isset($salesruleData) && $salesruleData != '')?date('d-m-Y',strtotime($salesruleData->end_date)) : date('d-m-Y'); ?>" placeholder="Enter end date" <?php echo $non_editable_field; ?>>
              </div><!-- col-sm-6 -->
            </div>

            <div class="left-form-sec full-width-form-sec coupon-code-select">
              <div class="col-sm-6 customize-add-inner-sec page-content-textarea">
                <label>Apply To</label>
                <div class="all-mail"></div>
                <input type="text" class="form-control" name="apply_to" id="apply_to_email" value="<?php echo (isset($salesruleData) && $salesruleData->email_ids != '')?$salesruleData->email_ids :''?>" placeholder="Enter Email" <?php echo $non_editable_field; ?>>
              </div><!-- col-sm-6 -->
              
              <div class="col-sm-6 customize-add-inner-sec page-content-textarea">
                <label>Subject</label>
                <input type="text" class="form-control" name="email_subject" id="email_subject" value="<?php echo (isset($salesruleData) && $salesruleData->email_subject != '')?$salesruleData->email_subject :''?>" placeholder="Enter Subject" <?php echo $non_editable_field; ?>>
              </div><!-- col-sm-6 -->

             <div class="col-sm-6 customize-add-inner-sec page-content-textarea">
                <label>Message</label>
                <textarea class="form-control" placeholder="Message"id="email_message" name="email_message" <?php echo $non_editable_field; ?>><?php if(isset($salesruleData)){ echo $salesruleData->message; } ?></textarea>
              </div><!-- col-sm-6 -->
            </div>
             <!-- preview section start -->
        <div class="row col-sm-12 preview-email-coupon-discounts">
              <div class="preview-section-coupon left-form-sec  col-sm-6">
                <div class="email-msg-preview">
                  <h6 class="customer-name">Dear Customer,</h6>
                  <div class="customer-message-area red-msg">
                    <p class="red-msg">##MESSAGE##</p>
                  </div>
                  <div class="customer-discount-area">
                    <p>Here is the special Discount just for you.</p>
                    <p>Discount Code - ##DISCOUNTCODE## </p>
                    <p>Valid Till - ##DISCOUNTEXPIRYDATE## </p>
                  </div>

                  <div class="customer-regards-area">
                    <p>Kind Regards,</p>
                    <p>##WEBSHOPNAME##</p>
                  </div>
                </div>
              </div>

              <div class="preview-section-coupon left-form-sec preview-voucher-img  col-sm-6">
                <div class="email-msg-preview">
                  <h6 class="customer-name">Dear Customer,</h6>
                  <div class="customer-message-area red-msg">
                    <p class="red-msg">##MESSAGE##</p>
                  </div>
                  <div class="customer-discount-area">
                    <p>Here is the Voucher just for you.</p>
                    <p>Voucher Code - ##VOUCHERCODE## </p>
                    <p>Valid Till - ##VOUCHEREXPIRYDATE## </p>
                  </div>

                  <div class="customer-regards-area">
                    <p>Kind Regards,</p>
                    <p>##WEBSHOPNAME##</p>
                  </div>
                </div>
              </div>
        </div>
              <!-- preview section  end-->
            <div class="left-form-sec coupon-code-select coupon-code-select-product-list">
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
                  <option value="buyx_getyfree" <?php echo (isset($salesruleData) && $salesruleData->apply_condition == 'buyx_getyfree')?'selected':''?>>Buy X, Get Y Free</option>
                  <option value="free_sample" <?php echo (isset($salesruleData) && $salesruleData->apply_condition == 'free_sample')?'selected':''?>>Free Sample</option>
                </select>
                
              </div><!-- col-sm-6 -->
              
              <div class="condition-left-sec-min-cart-val" style="<?= $style ?>">
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Apply</label>
                  <select class="form-control" name="apply_percent" id="apply_percent" <?php echo (isset($pg_type) && $pg_type=='edit')?'disabled':'';?> >
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
                  <label class="disc_lbl"><?php echo $disc_lbl; ?></label>
                  <input type="number" class="form-control" id="discount_amnt" name="discount_amnt" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->discount_amount :''?>" placeholder="Enter discount" <?php echo $non_editable_field; ?>>
                </div><!-- col-sm-6 -->

                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Min Cart value</label>
                  <input type="number" class="form-control" name="cart_val" id="cart_val" placeholder="5000" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->min_cart_value :''?>" <?php echo $non_editable_field; ?>>
                  <?php //echo $currency_symbol;?> &nbsp;&nbsp;&#8377;
                </div><!-- col-sm-6 -->
              </div>

              <div class="condition-left-sec-xy" style="<?= (isset($salesruleData) && $salesruleData->apply_condition=='buyx_getyfree') ? 'display:block' : 'display: none;' ?>">
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Select Product (X)</label>
                  <input class="form-control buy_x" type="text" name="product_x" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->buyx_product :''?>" placeholder="Product SKU" <?php echo $non_editable_field; ?> >
                  <span class="or-sec">Or</span>
                  <button class="white-btn" type="button" onclick="openProductListPopup('buy-x')" <?php echo $non_editable_field; ?> >Select from product list</button>
                </div><!-- col-sm-6 -->
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>No. of products</label>
                  <input type="number" class="form-control" name="product_x_num" id="" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->buyx_product_qty :''?>" placeholder="Enter number" <?php echo $non_editable_field; ?>>
                </div><!-- col-sm-6 -->
              
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Select Product (Y)</label>
                  <input class="form-control get_y" type="text" name="product_y" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->gety_product :''?>" placeholder="Product SKU" <?php echo $non_editable_field; ?>>
                  <span class="or-sec">Or</span>
                  <button class="white-btn" type="button" onclick="openProductListPopup('get-y')" <?php echo $non_editable_field; ?>>Select from product list</button> 
                </div><!-- col-sm-6 -->
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>No. of products</label>
                  <input type="number" class="form-control" name="product_y_num" id="" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->gety_product_qty :''?>" placeholder="Enter number" <?php echo $non_editable_field; ?>>
                </div><!-- col-sm-6 -->
              </div>

              <div class="condition-left-sec-free-sample" style="<?= (isset($salesruleData) && $salesruleData->apply_condition=='free_sample') ? 'display:block' : 'display: none;' ?>">
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Min Cart value </label>
                  <input type="text" class="form-control" name="cart_val_free" id="cart_val_free" value="<?php echo (isset($salesruleData) && $salesruleData != '')?$salesruleData->min_cart_value :''?>" placeholder="5000" <?php echo $non_editable_field; ?>><?php echo $currency_symbol;?>
                </div><!-- col-sm-6 -->
                <?php if(isset($free_sample_arr) && !empty($free_sample_arr)) { 
                  foreach ($free_sample_arr as $key => $value) { ?>
                    <div class="after-add-more">
                      <div class="col-sm-6 customize-add-inner-sec">
                        <label>Select Free Product </label>
                        <input class="form-control get_y" type="text" name="product_free[]" value="<?php echo $key;?>" placeholder="Product SKU" <?php echo $non_editable_field; ?>>
                        <span class="or-sec">Or</span>
                        <button class="white-btn" type="button" onclick="openProductListPopup('get-y')" <?php echo $non_editable_field; ?>>Select from product list</button>
                      </div><!-- col-sm-6 -->
                      <div class="col-sm-6 customize-add-inner-sec change">
                        <label>No. of products</label>
                        <input type="number" class="form-control" name="product_free_num[]" value="<?php echo $value;?>" placeholder="Enter number" <?php echo $non_editable_field; ?>>
                      </div><!-- col-sm-6 -->
                    </div>
                  <?php } }else{ ?>
                <div class="after-add-more">
                  <div class="col-sm-6 customize-add-inner-sec">
                    <label>Select Free Product </label>
                    <input class="form-control get_y" type="text" name="product_free[]" value="" placeholder="Product SKU">
                    <span class="or-sec">Or</span>
                    <button class="white-btn" type="button" onclick="openProductListPopup('get-y')">Select from product list</button>
                  </div><!-- col-sm-6 -->
                  <div class="col-sm-6 customize-add-inner-sec change">
                    <label>No. of products</label>
                    <input type="number" class="form-control" name="product_free_num[]" value="" placeholder="Enter number">
                  </div><!-- col-sm-6 -->
                </div>
                <div class="col-sm-12 row">
                  <button class="white-btn add-more">+ Add more</button> 
                </div> <!-- col-sm-6 -->
                <?php } ?>
              </div>
            </div>
        <?php
          if(isset($DiplayData) && !empty($DiplayData)){ ?>
            <div class="right-form-sec  coupon-code-select coupon-code-select-product-list">
              <div class="email-coupon-table">
                <table>
                  <thead>
                    <th>Email</th>
                    <th>Coupon Code</th>
                  </thead>
                    <tbody>
                      <?php
                        foreach ($DiplayData as $value) { ?>
                          <tr>
                            <td><?php echo $value->email_address ?></td>
                            <td><?php echo $value->coupon_code ?></td>
                          </tr>   
                      <?php } ?> 
                    </tbody>
                </table>
              </div>
            </div>
        <?php  } ?>
           
          </div><!-- row -->
  			</div><!-- customize-add-section -->
        <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/discounts/write',$this->session->userdata('userPermission'))){ ?>
  			<div class="download-discard-small mar-top">
          <?php if(isset($salesruleData)) { ?>
            <button type="button" class="white-btn" data-toggle="modal" data-target="#deleteModal">Delete</button>
          <?php } ?>
            <button class="download-btn" id="save_coupon" type="submit">Save</button>
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
        CKEDITOR.replace('email_message', {
       extraPlugins :'justify', 
        allowedContent: true, 
      });      
    });
</script>
<script src="<?php echo SKIN_JS; ?>discounts.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?> 