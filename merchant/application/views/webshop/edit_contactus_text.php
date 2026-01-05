<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">

		<li><a href="<?= base_url('webshop/contact-us-requests') ?>">Contact Us Requests</a></li>
		<li class="active"><a href="<?= base_url('webshop/edit-contact-us-text') ?>">Edit Contact Us Text</a></li>
	</ul>

    <div class="profile-details busniess-details editcontactus">
        <div class="row">
            <form class="col-sm-12" id="editcontactustextform" method="POST" action="<?= base_url('WebshopController/submit_contactus_text') ?>">
              
                <div class="col-md-12">
                    <h2>Edit Contact Us Text</h2>
                    <div class="row">
               <?php 
                     if(!empty($contact_us_info))
                    	{
						 foreach ($contact_us_info as  $value) { ?>
                         <input type="hidden" name="row_id" value="<?= $value['id']; ?>">
                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                           
                            <div class="profile-inside-box">
                                <label>Enable Email</label>
                                <div class="switch-onoff">
                                <label class="checkbox">
                                <?php 
                                $enableemail = '';
                                $display_e_feild = 'style="display: none;"';
                                $required='';
                                if($value['contact_email_enabled'] == 1){
                                    $enableemail = 'checked';
                                    $display_e_feild = '';
                                    $required='required';
                                }
                                ?>
                                <input type="hidden"  name="email_check" id="email_check" value="">
                                <input type="checkbox" name="enalble_email" id="enalble_email" autocomplete="off" <?php echo $enableemail ?> > 
                                    <span class="checked"></span>
                                </label>
                                </div>
                            </div><!-- profile-inside-box -->
                            
                            <div class="profile-inside-box email_sec" <?php echo  $display_e_feild ?> >
                                <label for="domainName">Email</label>
                                <div>
                                    <input class="form-control" type="text"  name="email" id="email" <?php $required ?> value="<?php echo $value['contact_email']; ?>" placeholder="Enter Email ">
                                </div>
                                
                            </div><!-- profile-inside-box -->
   
                            <div class="profile-inside-box">
                                <label>Enable Address</label>
                                <div class="switch-onoff">
                                <label class="checkbox">
                                <?php 
                                $enableaddress = '';
                                $display_a_feild = 'style="display: none;"';
                                $required_a='';
                                if($value['contact_address_enabled'] == 1){
                                    $enableaddress = 'checked';
                                    $display_a_feild = '';
                                    $required_a='required';
                                }
                                ?>
                              	<input type="hidden"  name="address_check" id="address_check" value="">
                                <input type="checkbox" name="enable_address" id="enable_address" autocomplete="off" <?php echo $enableaddress; ?> > 
                                    <span class="checked"></span>
                                </label>
                                </div>
                            </div><!-- profile-inside-box -->
                           
                        </div><!-- col-sm-6 -->
                       <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                            
                            <div class="profile-inside-box">
                                <label>Enable Phone</label>
                                <div class="switch-onoff">
                                <label class="checkbox">
                               
                                <?php 
                                $enablephone = '';
                                $display_p_feild = 'style="display: none;"';
                                $required_p='';
                                if($value['contact_phone_enabled'] == 1){
                                    $enablephone = 'checked';
                                    $display_p_feild = '';
                                    $required_p='required';
                                }
                                ?>
                                <input type="hidden"  name="phone_check" id="phone_check" value="">
                                <input type="checkbox" name="enable_phone" id="enable_phone" autocomplete="off" <?php echo $enablephone; ?> > 
                                    <span class="checked"></span>
                                </label>
                                </div>
                            </div><!-- profile-inside-box -->
                            
                             <div class="profile-inside-box phone_sec " <?php echo $display_p_feild; ?>>
                                <label for="domainName">Phone</label>
                                <div>
                                    <input class="form-control" type="text"  name="phone" id="phone" <?php $required_p ?> value="<?php echo $value['contact_phone']; ?>" placeholder="Enter Phone">
                                </div>
                                
                            </div><!-- profile-inside-box -->
                            
                            
                        </div><!-- col-sm-6 -->
                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec add_sec" <?php echo $display_a_feild; ?> >
                            <label class="">Main Office</label>
                             <div class="profile-inside-box">
                               <textarea class="form-control" name="main_office"  value="" id="main_office" placeholder="Main Office" <?php echo $required_a ?> ><?php echo $value['contact_address'] ?></textarea>
                            </div><!-- profile-inside-box -->
                         </div>
                       	<div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                        	 <label class="">Message Block1</label>
                        	 <div class="profile-inside-box">                                
                             <textarea class="form-control" name="message" id="message" value="" placeholder="Contact Us Message"><?php echo $value['contact_message']; ?></textarea>
                            </div><!-- profile-inside-box -->
                         </div>

                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                        	 <label class="">Message Block2</label>
                        	 <div class="profile-inside-box">                                
                             <textarea class="form-control" name="message_block2" id="message_block2" value="" placeholder="Contact Us Message"><?php echo $value['contact_message2']; ?></textarea>
                            </div><!-- profile-inside-box -->
                        </div>
                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                        	 <label class="">Message Block3</label>
                        	 <div class="profile-inside-box">                                
                             <textarea class="form-control" name="message_block3" id="message_block3" value="" placeholder="Contact Us Message"><?php echo $value['contact_message3']; ?></textarea>
                            </div><!-- profile-inside-box -->
                        </div>

                        <?php   }	}else
                        {  ?>
                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                           
                            <div class="profile-inside-box">
                                <label>Enable Email</label>
                                <div class="switch-onoff">
                                <label class="checkbox">
                                <input type="hidden"  name="email_check" id="email_check" value="">
                                <input type="checkbox" name="enalble_email" id="enalble_email" autocomplete="off" > 
                                    <span class="checked"></span>
                                </label>
                                </div>
                            </div><!-- profile-inside-box -->
                            
                             <div class="profile-inside-box email_sec" style="display: none;">
                                <label for="domainName">Email</label>
                                <div>
                                    <input class="form-control" type="text"  name="email" id="email" value="" placeholder="Enter Email ">
                                </div>
                                
                            </div><!-- profile-inside-box -->
                           <div class="profile-inside-box">
                                <label>Enable Address</label>
                                <div class="switch-onoff">
                                <label class="checkbox">
                                <input type="hidden"  name="address_check" id="address_check" value="">
                                <input type="checkbox" name="enable_address" id="enable_address"  autocomplete="off" > 
                                    <span class="checked"></span>
                                </label>
                                </div>
                            </div><!-- profile-inside-box -->
                           
                        </div><!-- col-sm-6 -->
                       <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                             <div class="profile-inside-box">
                                <label>Enable Phone</label>
                                <div class="switch-onoff">
                                <label class="checkbox">
                                <input type="hidden"  name="phone_check" id="phone_check" value="">
                                <input type="checkbox" name="enable_phone" id="enable_phone" autocomplete="off" > 
                                    <span class="checked"></span>
                                </label>
                                </div>
                            </div><!-- profile-inside-box -->
                            
                           
                             <div class="profile-inside-box phone_sec" style="display: none;">
                                <label for="domainName">Phone</label>
                                <div>
                                    <input class="form-control" type="text"  name="phone" id="phone" value="" placeholder="Enter Phone">
                                </div>
                                
                            </div><!-- profile-inside-box -->
                            
                            
                        </div><!-- col-sm-6 -->
                       <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec add_sec" style="display: none;" >
                            <label class="">Main Office</label>
                             <div class="profile-inside-box">
                               <textarea class="form-control" name="main_office" id="main_office" placeholder="Main Office"></textarea>
                            </div><!-- profile-inside-box -->
                         </div>
                       	<div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                        	 <label class="">Message Block1</label>
                        	 <div class="profile-inside-box">                                
                             <textarea class="form-control" name="message" id="message" placeholder="Contact Us Message"></textarea>
                            </div><!-- profile-inside-box -->
                        </div>
                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                        	 <label class="">Message Block2</label>
                        	 <div class="profile-inside-box">                                
                             <textarea class="form-control" name="message_block2" id="message_block2" value="" placeholder="Contact Us Message"></textarea>
                            </div><!-- profile-inside-box -->
                        </div>
                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                        	 <label class="">Message Block3</label>
                        	 <div class="profile-inside-box">                                
                             <textarea class="form-control" name="message_block3" id="message_block3" value="" placeholder="Contact Us Message"></textarea>
                            </div><!-- profile-inside-box -->
                        </div>
                         
                       <?php } ?>	
                    </div>

                 
                    <div class="save-discard-btn">
                        <input type="submit" name="save_contact_edit" value="Save" class="purple-btn">
                    </div>
                </div><!-- row -->
            </form>
        </div><!-- profile-details-block -->
    </div>
</main>
<script src="<?php echo SKIN_JS; ?>contactus.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>