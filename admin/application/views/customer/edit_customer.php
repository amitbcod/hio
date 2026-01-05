<div class="tab-content">

  <div  class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

      <h1 class="head-name">Edit Customer's Personal Information</h1> <?php //echo $this->session->flashdata('item'); ?>

        <button type="button" class="close" data-dismiss="modal" aria-label="Close">

          <span aria-hidden="true">&times;</span>

        </button>

    </div><!-- d-flex -->

        <!-- form -->

    <form id='edit_customer_form' name='edit_customer_form' action="<?php echo base_url(); ?>CustomerController/update_customer_info" method="post">

      <div class="content-main form-dashboard warehouse-setting">       

          <div class="customize-add-section">       

            <div class="row">           

                <div class="right-form-sec">          

                  <div class="col-sm-6 customize-add-inner-sec ">         

                    <label>First Name<span class="required">*</span></label>            

                    <input class="form-control" type="text" id="first_name" required   name="first_name" value="<?php if(isset($customer_details->first_name)) { echo $customer_details->first_name ; }   ?>" placeholder="" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength = "30">  

                  </div>

                   <?php if($restricted_access == 'yes') { ?>

                 <div class="col-sm-6 customize-add-inner-sec ">         

                    <label>Company Name</label>            

                    <input class="form-control" type="text" id="company_name"  name="company_name" value="<?php if(isset($customer_details->company_name)) { echo $customer_details->company_name ; }   ?>" placeholder="">  

                  </div> 

                  <?php } ?>    

                  <div class="col-sm-6 customize-add-inner-sec ">         

                      <label>Date Of Birth</label>            

                     <input type="text" class="form-control" id="dob" value="<?php if(isset($customer_details->dob)) { echo date("d-m-Y",strtotime($customer_details->dob)) ; }   ?>" name="dob"  >  

                  </div>  

                  <div class="col-sm-6 customize-add-inner-sec ">         

                    <label class="gender">Gender</label>            

                    <div class="radio">

                        <label><input type="radio" name="gender"  <?php if(isset($customer_details->gender) && $customer_details->gender == 'male') { echo "checked" ; }   ?> value="male">Male <span class="checkmark"></span></label>

                    </div><!-- radio -->

                    <div class="radio">

                        <label><input type="radio" name="gender" <?php if(isset($customer_details->gender) && $customer_details->gender == 'female') { echo "checked" ; }   ?>   value="female">Female <span class="checkmark"></span></label>

                    </div><!-- radio -->

                  </div>          

                       

                </div>  

                <div class="left-form-sec">

                  <div class="col-sm-6 customize-add-inner-sec ">         

                    <label>Last Name<span class="required">*</span></label>            

                    <input class="form-control" type="text" id="last_name" required   name="last_name" value="<?php if(isset($customer_details->last_name)) { echo $customer_details->last_name ; }   ?>" placeholder="" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength = "30">  

                  </div>    

                   <?php if($restricted_access == 'yes') { ?>                                  

                  <div class="col-sm-6 customize-add-inner-sec ">         

                    <label>GST Number</label>            

                    <input class="form-control" type="text" id="GST_no"  name="GST_no" value="<?php if(isset($customer_details->gst_no)) { echo $customer_details->gst_no ; }   ?>" placeholder="">  

                  </div> 

                <?php } ?>

                  <div class="col-sm-6 customize-add-inner-sec ">         

                    <label>Mobile Number</label>            

                    <input class="form-control" type="number" id="mobile_no"   name="mobile_no" value="<?php if(isset($customer_details->mobile_no)) { echo $customer_details->mobile_no ; }   ?>" placeholder="" pattern="[0-9]{3}[0-9]{3}[0-9]{4}">  

                  </div>

                  <div class="col-sm-6 customize-add-inner-sec ">         

                    <label>Country</label>            

                     <select name="country" id="country"  class="country form-control">

                      <option value="">Select Country</option>

              <?php    foreach($country_list as $key=>$val)

                    { ?>

                        <option value="<?php echo $val['country_code'] ;?>" <?php if(isset($customer_details->country_code) && $customer_details->country_code == $val['country_code'] ) {echo 'selected' ;} ?> > <?php echo $val['country_name'] ;?></option>

              <?php } ?>

                    </select>

                  </div>



                </div>             

            </div><!-- row -->               

                  

          </div>

        </div>  



      <div class="download-discard-small text-center"> 

        <button class="white-btn" type="button"data-dismiss="modal">Discard</button> 

        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">     

        <input type="submit" name="submit" value="Save" class="purple-btn">      

      </div>   

    </form>

        <!--end form-->

  </div>

</div>



<script type="text/javascript" src="<?php echo SKIN_JS; ?>edit_customer.js"></script>





