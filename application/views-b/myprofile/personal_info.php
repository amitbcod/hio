<?php $this->load->view('common/header'); ?>
    <div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>">Home</a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active">My Profile</li>
				</ul>
			</div>
        </div>
      </div><!-- breadcrum section -->


     <div class="my-profile-page-full">
     	 <div class="container">
          	<div class="row">
				<?php $this->load->view('common/profile_sidebar'); ?>

				<div class="col-md-9 col-lg-9 ">
					<h4 class="manage-add-head upc-head"><span>Personal Infomation </span><button class="black-btn float-right" onclick="openChangePasswordPopup(<?= $customerData->id ?>)">Change Password</button>
						<button class="black-btn float-right" onclick="openChangeEmailPopup(<?= $customerData->id ?>)">Change Email</button>
					</h4>

                    <?php if(SHOP_ID === 1 && $this->session->userdata('CustomerTypeID') === 3): ?>
                        <div style="font-weight: bold"><?=lang('zin_sync_member')?></div>
                    <?php endif; ?>

					<div class="profile-complete">
						<p>Profile Complete <!-- <span class="profile-percentage">60%</span>--> </p>
						<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="<?= $profilePercentage ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $profilePercentage ?>%">
						  <span class="sr-only"><?= $profilePercentage ?>%</span>
						</div>
					  </div><!-- progress -->
					</div><!-- profile complete -->

					<div class="personal-info-form col-sm-12">
						<form class="row" id="customer-personal-info-form" method="POST" action="<?php echo BASE_URL;?>MyProfileController/updateCustomerInfo">
							<div class="col-sm-6">
								<label>Firstname</label>
								<input type="text" placeholder="" value="<?= $_SESSION['FirstName'] ?>" id="first_name" name="first_name">
							</div>
							<div class="col-sm-6">
								<label>Lastname</label>
								<input type="text" placeholder="" value="<?= $_SESSION['LastName'] ?>" id="last_name" name="last_name">
							</div>

							<div class="col-sm-12 male-female-section">
								<label>Gender</label>
								<div class="male-female-inner">
									<label class="radio-label-checkout"><input class="radio-checkout" type="radio" value="male" name="gender" <?= (isset($customerData->gender) && $customerData->gender == 'male')?'checked="checked"':''?>>Male <span class="radio-check"></span></label>
									<label class="radio-label-checkout"><input class="radio-checkout" type="radio" value="female" name="gender" <?= (isset($customerData->gender) && $customerData->gender == 'female')?'checked="checked"':''?>>Female <span class="radio-check"></span></label>
								</div>
							</div>

							<div class="col-sm-6">
								<label>Email</label>
								<input type="email" placeholder="" value="<?= $_SESSION['EmailID'] ?>" id="email" name="name" disabled="">
							</div>
							
							<div class="col-sm-6">
								<label>Mobile Number</label>
								<input type="text" placeholder="" minlength="10" maxlength="10" value="<?= (isset($customerData->mobile_no) && $customerData->mobile_no != '')?$customerData->mobile_no:'' ?>"  id="mobile_no" name="mobile_no" >
							</div>

							<div class="col-sm-6">
								<label>Country</label>
								<select name="country" id="country">
								<option value="" selected><?=lang('selectcountry')?></option>
								<?php if (isset($countryList) && count($countryList) > 0) {
    foreach ($countryList as $data) { ?>
										<option value="<?php echo $data->country_code; ?>" <?php echo $data->country_code == $customerData->country_code ? "selected='selected'" : ''; ?>><?php echo $data->country_name; ?></option>
								<?php }
} ?>
							  </select>
							</div>

							<div class="col-sm-6">
								<label>Date of Birth</label>
								<input type="hidden" id="dob" value="<?= (isset($customerData->dob) && $customerData->dob != '')?date("Y-m-d", strtotime($customerData->dob)):'' ?>">
							</div>
							<?php if (isset($restricted_access) && $restricted_access == 'yes') { ?>
								<div class="col-sm-6">
									<label><?=lang('companyname')?></label>
									<input type="text" placeholder="" value="<?= (isset($customerData->company_name) && $customerData->company_name != '')?$customerData->company_name:'' ?>" <?php if ($restricted_access == 'yes') {
    echo "disabled";
}?> id="company_name" name="company_name">
								</div>
								<div class="col-sm-6">
									<?php if ($shop_flag == 1) { ?>
									<label><?=lang('gst_number')?></label>
									<?php } else { ?>
									<label><?=lang('vat_number')?></label>
									<?php } ?>
									<input type="text" placeholder="" value="<?= (isset($customerData->gst_no) && $customerData->gst_no != '')?$customerData->gst_no:'' ?>" <?php if ($restricted_access == 'yes') {
    echo "disabled";
}?> id="gst_no" name="gst_no">
								</div>
							<?php } ?>

							<div class="personal-info-btn col-sm-12">
								<button class="black-btn">Submit</button>
							</div><!-- signin-btn -->
						</form>
					</div>
				</div><!-- col-md-9 -->

          </div><!-- row -->
      </div><!-- container -->
    </div><!-- my-profile-page-full -->

   <?php $this->load->view('common/footer'); ?>
   <script src="<?php echo SKIN_JS ?>myprofile.js?v=<?php echo CSSJS_VERSION; ?>"></script>
  </body>
</html>
