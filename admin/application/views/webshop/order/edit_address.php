<div class="tab-content">
  <div  class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
      <h1 class="head-name">Edit Shipping Address</h1> <?php //echo $this->session->flashdata('item'); ?>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
    </div><!-- d-flex -->
        <!-- form -->
    <form id='edit_customer_address_form' name='edit_customer_address_form' action="<?php echo base_url(); ?>WebshopOrdersController/update_shipping_address" method="post">
      <div class="content-main form-dashboard warehouse-setting">       
          <div class="customize-add-section">       
            <div class="row">           
                <div class="right-form-sec">          
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>First Name<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="first_name" required   name="first_name" value="<?php if(isset($ShippingAddress->first_name)) { echo $ShippingAddress->first_name ; }   ?>" placeholder="">  
                  </div>
                 <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Address Line 1<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="address_line1" required  name="address_line1" value="<?php if(isset($ShippingAddress->address_line1)) { echo $ShippingAddress->address_line1 ; }   ?>" placeholder="">  
                  </div>  
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>City<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="city" required  name="city" value="<?php if(isset($ShippingAddress->city)) { echo $ShippingAddress->city ; }   ?>" placeholder="">  
                  </div> 
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Country<span class="required">*</span></label>            
                     <select name="country" id="country" required  class="country form-control">
                      <option value="">Select Country</option>
              <?php    foreach($country_list as $key=>$val)
                    { ?>
                        <option value="<?php echo $val['country_code'] ;?>" <?php if(isset($ShippingAddress->country) && $ShippingAddress->country == $val['country_code'] ) {echo 'selected' ;} ?> > <?php echo $val['country_name'] ;?></option>
              <?php } ?>
                    </select>
                  </div>  
                   <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Mobile Number<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="mobile_no" required  name="mobile_no" value="<?php if(isset($ShippingAddress->mobile_no)) { echo $ShippingAddress->mobile_no ; }   ?>" placeholder="">  
                  </div>      
                </div>  

                <div class="left-form-sec">
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Last Name<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="last_name" required   name="last_name" value="<?php if(isset($ShippingAddress->last_name)) { echo $ShippingAddress->last_name ; }   ?>" placeholder="">  
                  </div>  
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Address Line 2</label>            
                    <input class="form-control" type="text" id="address_line2" name="address_line2" value="<?php if(isset($ShippingAddress->address_line2)) { echo $ShippingAddress->address_line2 ; }   ?>" placeholder="">  
                  </div>  
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Pincode<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="pincode" required  name="pincode" value="<?php if(isset($ShippingAddress->pincode)) { echo $ShippingAddress->pincode ; }   ?>" placeholder="">  
                  </div>                                    
                  <div class="col-sm-6 customize-add-inner-sec ">
                    <label>State<span class="required">*</span></label> 
                    <div class="form-box  state_div">
                       <input class="form-control " type="text" id="state" required name="state" placeholder="State*"  value="<?php if(isset($ShippingAddress->state)) { echo $ShippingAddress->state ; }   ?>">
                    </div><!-- form-box -->
                    <div class="form-box dp_state_div">
                        <select name="state_dp" id="state_dp" required class="form-control">
                        <option value="" selected>Select State<span class="required">*</span></option>
                        <?php if(isset($stateList) && count($stateList) > 0) { 
                          foreach($stateList as $data) { ?>
                            <option value="<?php echo $data['state_name']; ?>"  <?php if(isset($ShippingAddress->state) && $ShippingAddress->state == $data['state_name'] ) { echo 'selected' ; }   ?> ><?php echo $data['state_name']; ?></option>
                        <?php } } ?>
                        </select> 
                    </div>
                  </div> 
                 
                </div>             
            </div><!-- row -->               
                  
          </div>
        </div>  

      <div class="download-discard-small text-center"> 
        <button class="white-btn" type="button"data-dismiss="modal">Discard</button> 
        <!-- <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>"> -->
        <input type="hidden" name="address_id" value="<?php echo $ShippingAddress->address_id; ?>">  
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">     

        <input type="submit" name="submit" value="Save" class="purple-btn">      
      </div>   
    </form>
        <!--end form-->
  </div>
</div>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>edit_customer_address.js"></script>


