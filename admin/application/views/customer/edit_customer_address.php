<div class="tab-content">
  <div  class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
      <h1 class="head-name">Edit Customer's Address</h1> <?php //echo $this->session->flashdata('item'); ?>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
    </div><!-- d-flex -->
        <!-- form -->
    <form id='edit_customer_address_form' name='edit_customer_address_form' action="<?php echo base_url(); ?>CustomerController/update_customer_address" method="post">
      <div class="content-main form-dashboard warehouse-setting">       
          <div class="customize-add-section">       
            <div class="row">           
                <div class="right-form-sec">          
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>First Name<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="first_name" required   name="first_name" value="<?php if(isset($customer_address->first_name)) { echo $customer_address->first_name ; }   ?>" placeholder="" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength = "30">  
                  </div>
                 <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Address Line 1</label>            
                    <input class="form-control" type="text" id="address_line1"  name="address_line1" value="<?php if(isset($customer_address->address_line1)) { echo $customer_address->address_line1 ; }   ?>" placeholder="" maxlength="50">  
                  </div>  
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>City</label>            
                    <input class="form-control" type="text" id="city"  name="city" value="<?php if(isset($customer_address->city)) { echo $customer_address->city ; }   ?>" placeholder="">  
                  </div> 
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Country</label>            
                     <select name="country" id="country"  class="country form-control">
                      <option value="">Select Country</option>
              <?php    foreach($country_list as $key=>$val)
                    { ?>
                        <option value="<?php echo $val['country_code'] ;?>" <?php if(isset($customer_address->country) && $customer_address->country == $val['country_code'] ) {echo 'selected' ;} ?> > <?php echo $val['country_name'] ;?></option>
              <?php } ?>
                    </select>
                  </div>  
                   <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Mobile Number</label>            
                    <input class="form-control" type="text" id="mobile_no"   name="mobile_no" value="<?php if(isset($customer_address->mobile_no)) { echo $customer_address->mobile_no ; }   ?>" placeholder="" pattern="[0-9]{3}[0-9]{3}[0-9]{4}" maxlength="12">  
                  </div>      
                </div>  

                <div class="left-form-sec">
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Last Name<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="last_name" required   name="last_name" value="<?php if(isset($customer_address->last_name)) { echo $customer_address->last_name ; }   ?>" placeholder="" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength = "30">  
                  </div>  
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Address Line 2</label>            
                    <input class="form-control" type="text" id="address_line2"  name="address_line2" value="<?php if(isset($customer_address->address_line2)) { echo $customer_address->address_line2 ; }   ?>" placeholder="" maxlength="50">  
                  </div>  
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Pincode</label>            
                    <input class="form-control" type="number" id="pincode"  name="pincode" value="<?php if(isset($customer_address->pincode)) { echo $customer_address->pincode ; }   ?>"  pattern="[0-9]{3}[0-9]{3}[0-9]{4}" maxlength="6">  
                  </div>                                    
                  <div class="col-sm-6 customize-add-inner-sec ">
                    <label>State</label> 
                    <div class="form-box  state_div">
                       <input class="form-control " type="text" id="state" name="state" placeholder="State*"  value="<?php if(isset($customer_address->state)) { echo $customer_address->state ; }   ?>">
                    </div><!-- form-box -->
                    <div class="form-box dp_state_div">
                        <select name="state_dp" id="state_dp" class="form-control">
                        <option value="" selected>Select State*</option>
                        <?php if(isset($stateList) && count($stateList) > 0) { 
                          foreach($stateList as $data) { ?>
                            <option value="<?php echo $data['state_name']; ?>"  <?php if(isset($customer_address->state) && $customer_address->state == $data['state_name'] ) { echo 'selected' ; }   ?> ><?php echo $data['state_name']; ?></option>
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
        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
        <input type="hidden" name="address_id" value="<?php echo $address_id; ?>">     
        <input type="submit" name="submit" value="Save" class="purple-btn">      
      </div>   
    </form>
        <!--end form-->
  </div>
</div>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>edit_customer_address.js"></script>


