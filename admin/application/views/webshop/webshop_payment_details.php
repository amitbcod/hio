<?php 
	$this->load->view('common/fbc-user/header'); 
	//print_r($account_status);

	/*working*/
	/*$wpsData=json_decode($webshopPaymentsStripe->create_con_act_response, true);
	//rtrim($string, ",")
	print_r($variants['id']);*/

?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    	<li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
		<li ><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
		<li class="active"><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
		<li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
		
  	</ul>
		<?php // print_r_custom($get_gateway_details);


						if($get_gateway_details['payment_type'] == 1)
						{
							$payment_type = 'Direct Payment';
						}
						else if($get_gateway_details['payment_type'] == 2)
						{
							$payment_type = 'Split Payment';
						}
						
						$gateway_credentials = json_decode($get_gateway_details['gateway_details']);
						//print_r($shop_gateway_credentials);

						
						// print_r_custom($gateway_credentials);
						$payment_detail = "";
						$webshop_gateway_credentials = "";
						if($shop_gateway_credentials && $shop_gateway_credentials['gateway_details'] != "")
						{
							//echo "abbbbbbbbbbbbbbbbbbb";
							$webshop_gateway_credentials = json_decode($shop_gateway_credentials['gateway_details']);
							$payment_detail = json_decode($shop_gateway_credentials['gateway_details'],1);
						}
		?>
		<div class="tab-content">
    <div id="payment-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
	
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name pad-bt-20"><i class="fas  fa-angle-left"></i> &nbsp; <?php echo $get_gateway_details['payment_gateway'] ?></h1> 
        </div>
		
        <!-- form -->
        <div class="content-main form-dashboard">
            <form id="gatway_details_form" name="gatway_details_form" method="post" data-toggle="validator" action="<?php echo base_url(); ?>WebshopController/store_gateway_credentials">
			<div class="payment-mode-details">
				<div class="row">
					<div class="col-sm-12 customize-add-inner-sec">
					
						<label>Payment Type:</label>  <h6  style="display: inline;"><?php echo $payment_type?></h6>
						
						<div class="payment-type-box"> 
						<textarea class="form-control" name="type_details" id="type_details" required><?php echo $get_gateway_details['payment_type_details']?></textarea>
						</div>
					</div><!-- col-sm-6 -->


					<div class="col-sm-12 customize-add-inner-sec">			
						<label>Payment Display Title:</label>  
						<input class="form-control" value="<?php echo (isset($shop_gateway_credentials['display_name']) ? $shop_gateway_credentials['display_name'] : $get_gateway_details['display_name']  )?>" type="text" name="display_name" id="display_name">
					</div>

					<div class="col-sm-12 customize-add-inner-sec">			
						<label>Payment Message:</label>  
						<div class="payment-type-box"> 
							<textarea class="form-control" name="message" id="message">
							<?php echo (isset($shop_gateway_credentials['message']) ? $shop_gateway_credentials['message'] : $get_gateway_details['message']  )?>
							</textarea>
						</div>
					</div>
					
					
					<?php if($get_gateway_details['payment_gateway_key'] != 'via_transfer' && $get_gateway_details['payment_gateway_key'] != 'cod') { ?>
					<div class="col-sm-6 customize-add-inner-sec">
						<label>Gateway Details</label>
						
						<div class="getway-details">
							<?php
							
									if(isset($gateway_credentials)){
											//print_r($gateway_credentials);
										foreach($gateway_credentials as $key_gateway=>$val_gateway){

							?>
											<div class="input-box-sec-payment">
												
												<?php 
													if($gateway_get_id==6 && $key_gateway=='connected_stripe_account_id'){ 
														//echo "1";
												?>
												<label><?php echo  ucfirst(str_replace('_',' ',$key_gateway)) ;?><span class="required">*</span> </label>
												<input type="text" class="form-control gateway_cred" type="text" name="<?php echo $key_gateway; ?>" id="<?php echo $key_gateway ?>" value="<?php if(isset($connect_account_id)){echo $connect_account_id; } ?>" placeholder="" <?php if($gateway_get_id==6){echo'readonly';} ?>>
												<?php
													}elseif($gateway_get_id==6 && $key_gateway=='key') {
														//echo "2";

														//echo $SecretKey;

													if(isset($SecretKey)){
														$SecretKey; 
													?>
														<label>Secret <?php echo  ucfirst(str_replace('_',' ',$key_gateway)) ;?><span class="required">*</span> </label>
													<?php
														echo '<input type="text" name="'.$key_gateway.'" class="form-control" id="'.$key_gateway.'" value="'.$SecretKey.'" readonly>';
													}else{
														$SecretKey='';
														//echo '<input type="hidden" name="'.$key_gateway.'" id="'.$key_gateway.'" value="" >';
													}

													$accountSecretKey="<label> Secret ".ucfirst(str_replace('_',' ',$key_gateway))."<span class='required'>*</span> </label><input type='text' class='form-control gateway_cred' type='text' name='".$key_gateway."' id='".$key_gateway."' value='".$SecretKey."' placeholder='Please enter secret key' autocomplete='off'><br/> <button type='button' class='download-btn float-right' id='stripeAccountSaveSecretKey' >Save Secret Key</button>";

														//echo '<input type="hidden" name="'.$key_gateway.'" id="'.$key_gateway.'" value="" >';
													}elseif($gateway_get_id==6 && $key_gateway=='checkout_session_completed_webhook_key') {

														//echo "3";
												?>
												<label><?php echo  ucfirst(str_replace('_',' ',$key_gateway)) ;?><span class="required">*</span> </label>
												<input type="text" class="form-control gateway_cred" type="text" name="<?php echo $key_gateway; ?>" id="<?php echo $key_gateway ?>" value="<?php echo (isset($checkout_session_completed_webhook_key) && $checkout_session_completed_webhook_key !='')?$checkout_session_completed_webhook_key:'' ?>" placeholder="" readonly>
												<?php
													}else{

														
												?>
												<label><?php echo  ucfirst(str_replace('_',' ',$key_gateway)) ;?><span class="required">*</span> </label>
												<input type="text" class="form-control gateway_cred" type="text" name="<?php echo $key_gateway; ?>" id="<?php echo $key_gateway ?>" value="<?php echo (isset($payment_detail[$key_gateway]) && $payment_detail[$key_gateway] !='')?$payment_detail[$key_gateway]:'' ?>" placeholder="" <?php if($gateway_get_id==6){echo'readonly';} ?>>
												<?php } ?>
											</div><!-- input-box-sec-payment -->

							<?php 		}
									}
								//} //payment type check
							?>		
						</div><!-- getway-details -->
						<?php 
						//print_r($owner_country);
						//$owner_country
						//$shopData->country_code
									//echo 'ok';
								if($gateway_get_id==6){
									if(isset($webshopPaymentsStripe)){
										$connect_account_id=$webshopPaymentsStripe->connect_account_id;
										$account_return_status=$webshopPaymentsStripe->account_return_status;
										$ccAcc='collapsed';
										$ccAccount='';
										$ccAccLink='';
										$ccAccountLink='show';
										$account_link_response=$webshopPaymentsStripe->account_link_response;
									}else{
										$account_return_status='';
										$connect_account_id='';
										$ccAcc='';
										$ccAccount='show';
										$ccAccLink='collapsed';
										$ccAccountLink='';
										$account_link_response='';
									}
						?>
							<div  id="accordion">
									<div class="customize-add-inner-sec create-connectd">
										<div class="getway-details">
										<label class="stip-payment step-1">Create a Connected  Account <span data-toggle="collapse" href="#collapseOne"   aria-expanded="false"  class="minus-btn <?=$ccAcc?>"></span></label>
										<div class="collapse multi-collapse step-1-details <?=$ccAccount?>"  id="collapseOne"  data-parent="#accordion">

											<div class="field-sec">
												<label>Account  Type </label> 
												<div class="input-box">
													<select class="form-control" readonly id="stripeAccountType">
														<option value="standard" >Standard</option>
													</select>
												</div><!-- input-box -->
											</div><!-- field-sec -->

											<div class="field-sec">
												<label>Email Address</label> 
												<div class="input-box">
													<!-- <input type="text" readonly placeholder="" id="stripeEmail" value="<?php if(isset($owner_email)){ echo $owner_email->site_contact_email;}?>" class="form-control"> -->
													<?php 
														if($connect_account_id!='' && !empty($connect_account_id)){
													?>
													<input type="text" readonly placeholder="" id="stripeEmail" value="<?php if(isset($owner_email)){ echo $owner_email->site_contact_email;}?>" class="form-control">
													<!-- <span class="glyphicon glyphicon-question-sign tooltip"><i class="fa fa-info"></i><span class="tooltiptext">You can change your email id if you want </span></span> -->
													<?php }else{ ?>
													<input type="text" placeholder="" id="stripeEmail" value="<?php if(isset($owner_email)){ echo $owner_email->site_contact_email;}?>" class="form-control">

													<?php } ?>
												</div><!-- input-box -->
											</div><!-- field-sec -->

											<div class="field-sec">
												<label>Country </label> 
												<div class="input-box">
													<select class="form-control" readonly id="stripeCountry">
														<?php if(isset($owner_country)){ ?>
															<option value="<?=$shopData->country_code?>"> <?php echo $owner_country;?></option>
														<?php } ?>
													</select>
												</div><!-- input-box -->
											</div><!-- field-sec -->
											<?php 
												if($connect_account_id!='' && !empty($connect_account_id)){
											?>
											<!-- <div class="field-sec">
												<button type="button" class="download-btn float-right">Save</button>
											</div> -->
											<?php }else{?>

											<div class="field-sec">
												<input type="hidden" id="payment_type_id" value="<?=$gateway_get_id?>">
												<button type="button" class="download-btn float-right" id="stripeCreateAccount">Next</button>
											</div><!-- field-sec -->
											<?php } ?>

										</div><!-- step-1-details -->
										</div>
									</div><!-- col-sm-6 -->
									
									<div class="customize-add-inner-sec create-connectd">
										<div class="getway-details">
										<label class="stip-payment step-2">Create an Account Link <span data-toggle="collapse" href="#collapseTwo" class="minus-btn <?=$ccAccLink?>"></span></label>
										<div class="collapse multi-collapse  step-1-details <?=$ccAccountLink?>" id="collapseTwo"  data-parent="#accordion">

											<?php 
												//echo $account_link_response;
												if(($account_link_response=='' & empty($account_link_response)) || $account_status==0){

													if($account_link_response=='' & empty($account_link_response)){
											?>
											<div class="msg-block">
												<p class="success-msg">Your connected account has been created successfully. <br>Please click on “Create an Account Link ” to process further.</p>
											</div>
											<?php			
													}elseif ($account_status==0) {
														if($account_return_status==2){
											?>
											<div class="msg-block">
												<p class="process-msg">Your onboarding process is incomplete. You can continue onboarding later or click the below button to continue again.</p>
											</div>
											<?php				
														}elseif ($account_return_status==3) {
															
														//}
											?>
											<div class="msg-block">
												<p class="errors-msg">Your onboarding was unsuccessful. Please click below  button and start again.</p>
											</div>
											<?php
													}else{
											?>
												<div class="msg-block">
													<p class="success-msg">Your connected account has been created successfully. <br>Please click on “Create an Account Link ” to process further.</p>
												</div>
											<?php			
													}
												}
											?>
											

											<!-- 
											// rauth
											<div class="msg-block">
												<p class="errors-msg">Your onboarding was unsuccessful. Please click below  button and start again.</p>
											</div> -->

											<div class="field-sec">
												<input type="hidden" id="payment_type_id" value="<?=$gateway_get_id?>">
												<input type="hidden" id="connect_account_id" value="<?=$connect_account_id?>">
												<input type="hidden" id="cuUrl" value="<?=BASE_URL?>webshop/payment_details/">
												<button type="button" class="download-btn float-right" id="stripeCreateAccountLink" >Create an Account Link</button>
											</div><!-- field-sec -->
											<?php }elseif(!empty($account_link_response) && $account_status==1){ ?>
											<div class="msg-block">
												<p class="success-msg">You have successfully completed with the onboarding process. Please enter your live secrete key to proceed further.<!-- Now you can able to enable the stipe payment gateway on your website. --></p>

												<?php echo $accountSecretKey; ?>
												<input type="hidden" id="payment_type_id" value="<?=$gateway_get_id?>">
												<input type="hidden" id="connect_account_id" value="<?=$connect_account_id?>">
												<input type="hidden" id="cuUrl" value="<?=BASE_URL?>webshop/payment_details/">
												
											</div>
											<?php }elseif(!empty($account_link_response) && $account_status==2){  ?>
											<div class="msg-block">
												<p class="success-msg">You have successfully completed with the setup process. Now you can able to enable the stipe payment gateway on your website.</p>
												
											</div>
											<?php } ?>
										</div><!-- step-1-details -->
										</div>
									</div><!-- col-sm-6 -->
							</div><!-- accordion -->

							<?php
								}
								?>
					</div><!-- col-sm-6 -->
					<?php } ?>
					
					<div class="col-sm-5 customize-add-inner-sec page-content-textarea-small">
						<label>Integrate with Website</label>
						<?php 
							if($gateway_get_id==6 && $account_status==''){
								$integrate_checked='';
							}else{
								$integrate_checked='checked';
							}	
						?>

						<div class="customize-add-radio-section row">
							<div class="radio col-sm-6">
							  <label><input type="radio" name="integration_with_us" <?php echo (isset($shop_gateway_credentials['integrate_with_ws']) && ($shop_gateway_credentials['integrate_with_ws'] == 0 || $shop_gateway_credentials['integrate_with_ws'] == NULL ))? '' : $integrate_checked ;?> value="1" <?php if($gateway_get_id==6 && !empty($account_link_response) && $account_status!=2){ echo 'disabled';}elseif($gateway_get_id==6 && $account_status==''){echo 'disabled';} ?>>Integrate <span class="checkmark"></span></label>
							</div><!-- radio -->
							<div class="radio col-sm-6">
							  <label><input type="radio" name="integration_with_us" <?php echo (isset($shop_gateway_credentials['integrate_with_ws']) && ($shop_gateway_credentials['integrate_with_ws'] == 0 || $shop_gateway_credentials['integrate_with_ws'] == NULL ))? 'checked' : '' ;?>   value="0">Deny <span class="checkmark"></span></label>
							</div><!-- radio -->
						</div><!-- customize-add-radio-section -->
						
					</div><!-- col-sm-6 -->

				</div><!-- row -->
			</div><!-- customize-add-section -->
			
			<div class="download-discard-small ">
				<a href="<?php echo base_url();?>webshop/payment"><button type="button" class="white-btn">Discard</button></a>
			<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
				<button type="submit" class="download-btn">Save Draft</button>
			<?php } ?>
			 </div><!-- download-discard-small  -->

			 <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $get_gateway_details['id']?>">
			</form>
        </div>
        <!--end form-->
    </div>
	
	

  </div>

</main>
<script type="text/javascript">
	$(function () { 
      	CKEDITOR.replace('type_details', {
	     extraPlugins :'justify', 
     		allowedContent: true,	
	    }); 
	    CKEDITOR.replace('message', {
	     extraPlugins :'justify', 
     		readonly: true,	
	    });  	 
    });
</script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_payment.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>