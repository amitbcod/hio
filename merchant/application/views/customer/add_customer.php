<?php $this->load->view('common/fbc-user/header'); ?> 

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <ul class="nav nav-pills">
    <li><a  href="<?php echo base_url(); ?>customers">Customer Listing</a></li>
    <li><a  href="#customer-type-tab">Customer Type</a></li>
  </ul>
  

<div class="tab-content">
    <div  class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Add Customer</h1> <?php //echo $this->session->flashdata('item'); ?>

        </div><!-- d-flex -->
        <!-- form -->
        <form id='add_customer_form' name='add_customer_form' action="<?php echo base_url(); ?>CustomerController/save_customer" method="post">
        
        <div class="content-main form-dashboard warehouse-setting">       
          <div class="customize-add-section">       
            <div class="row">           
                <div class="right-form-sec">          
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>First Name<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="first_name" required   name="first_name" value="<?php //if(isset($contact_us_email)) { echo $contact_us_email ; }   ?>" placeholder="">  
                  </div>
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Email<span class="required">*</span></label>            
                    <input class="form-control" type="email" id="email" required   name="email" value="<?php //if(isset($admin_email)) { echo $admin_email ; }   ?>" placeholder="" autocomplete="false">  
                 </div> 
                 <div class="col-sm-6 customize-add-inner-sec">         
                    <label for="currency" class="">Customer type </label>   
                    <select name="customer_type_id"  id="customer_type_id" class="form-control"  >         
                      <option value="" >Select Customer type</option> 
                       <?php if(isset($customer_types) && count($customer_types) > 0) { 
                          foreach($customer_types as $data) { 
                        $disabled = '';
                        if($data['id'] == 1) { $disabled = 'disabled';}?>
                          <option value="<?php echo $data['id']; ?>" <?php echo $disabled ?>>
                              <?php echo $data['name']; ?></option>
                        <?php } } ?>
                    </select>
                 </div> 
               <!--   <div class="col-sm-6 customize-add-inner-sec">            
                    <label>Allow Catlog Builder</label>
                      <div class="switch-onoff">
                        <label class="checkbox">
                          <input type="checkbox" name="allow_catlog_builder" id="allow_catlog_builder" autocomplete="off"> 
                          <span class="checked"></span>
                        </label>
                      </div>        
                  </div>   -->           
                </div>  
                <div class="left-form-sec">
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Last Name<span class="required">*</span></label>            
                    <input class="form-control" type="text" id="last_name" required   name="last_name" value="<?php //if(isset($contact_us_email)) { echo $contact_us_email ; }   ?>" placeholder="">  
                  </div>                                      
                  <div class="col-sm-6 customize-add-inner-sec ">         
                      <label>Password<span class="required">*</span></label>            
                      <input class="form-control" type="password" id="password" required   name="password" value="<?php //if(isset($admin_email)) { echo $admin_email ; }   ?>" placeholder="" autocomplete="false">  
                  </div>
                
                  <!-- <div class="col-sm-6 customize-add-inner-sec">            
                    <label>Access Pre-launch Product</label>
                      <div class="switch-onoff">
                        <label class="checkbox">
                          <input type="checkbox" name="access_prelanch_product" id="access_prelanch_product" autocomplete="off"> 
                          <span class="checked"></span>
                        </label>
                      </div>        
                  </div> -->
                </div>             
             </div><!-- row -->               
                  
          </div>
        </div>  
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Personal Information</h1> <?php //echo $this->session->flashdata('item'); ?>

        </div>
        <div class="content-main form-dashboard warehouse-setting">       
          <div class="customize-add-section">       
            <div class="row">           
                <div class="right-form-sec">  
                   <?php if($restricted_access == 'yes') { ?>
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Company Name</label>            
                    <input class="form-control" type="text" id="company_name"  name="company_name" value="" placeholder="">  
                  </div>  
                  <?php } ?>      
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Country</label>            
                     <select name="country" id="country"  class="country form-control">
                      <option value="">Select Country</option>
              <?php    foreach($country_list as $key=>$val)
                    { ?>
                        <option value="<?php echo $val['country_code'] ;?>" <?php  //echo $selected ?>> <?php echo $val['country_name'] ;?></option>
              <?php } ?>
                    </select>
                  </div>
                  <div class="col-sm-6 customize-add-inner-sec ">         
                      <label>Date Of Birth</label>            
                     <input type="text" class="form-control" id="dob" name="dob"  >  
                  </div>
                  
                </div>  
                <div class="left-form-sec">
                   <?php if($restricted_access == 'yes') { ?>
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>GST Number</label>            
                    <input class="form-control" type="text" id="GST_no"  name="GST_no" value="<?php //if(isset($contact_us_email)) { echo $contact_us_email ; }   ?>" placeholder="">  
                  </div> 
                  <?php } ?>   
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Mobile Number</label>            
                    <input class="form-control" type="text" id="mobile_no"   name="mobile_no" value="<?php //if(isset($contact_us_email)) { echo $contact_us_email ; }   ?>" placeholder="">  
                  </div> 
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label class="gender">Gender</label>            
                    <div class="radio">
                        <label><input type="radio" name="gender"  value="male">Male <span class="checkmark"></span></label>
                    </div><!-- radio -->
                    <div class="radio">
                        <label><input type="radio" name="gender"   value="female">Female <span class="checkmark"></span></label>
                    </div><!-- radio -->
                 </div>      
                </div>             
            </div><!-- row -->              
                  
          </div>
        </div>

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Address </h1> 

        </div>
        <div class="content-main form-dashboard warehouse-setting">       
          <div class="customize-add-section">       
            <div class="row">           
                <div class="right-form-sec">  
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Address Line 1</label>            
                      <input class="form-control" type="text" id="address_line1"  name="address_line1" value="" placeholder="">  
                  </div>        
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>City</label>            
                      <input class="form-control" type="text" id="city"  name="city" value="" placeholder="">  
                  </div> 
                  <div class="col-sm-6 customize-add-inner-sec ">
                    <label>State</label> 
                    <div class="form-box  state_div">
                       <input class="form-control " type="text" id="state" name="state" placeholder="State*"  value="">
                    </div><!-- form-box -->
                    <div class="form-box dp_state_div">
                        <select name="state_dp" id="state_dp" class="form-control">
                        <option value="" selected>Select State*</option>
                        <?php if(isset($stateList) && count($stateList) > 0) { 
                          foreach($stateList as $data) { ?>
                            <option value="<?php echo $data['state_name']; ?>"  ><?php echo $data['state_name']; ?></option>
                        <?php } } ?>
                        </select> 
                    </div>
                  </div> 

        

                </div>

                <div class="left-form-sec">
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Address Line 2 </label>            
                    <input class="form-control" type="text" id="address_line2"  name="address_line2" value="<?php //if(isset($contact_us_email)) { echo $contact_us_email ; }   ?>" placeholder="">  
                  </div> 
                  <div class="col-sm-6 customize-add-inner-sec ">         
                    <label>Pincode</label>            
                      <input class="form-control" type="text" id="pincode"  name="pincode" value="" placeholder="">  
                  </div> 
                    
                </div>             
            </div><!-- row -->              
                  
          </div>
        </div>

        <div class="download-discard-small ">       
          <input type="submit" value="Save" class="purple-btn">      
        </div>   
        </form>
        <!--end form-->
    </div>
  </div>

</main>


<script type="text/javascript" src="<?php echo SKIN_JS; ?>add_customer.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>


