<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
	<div class="container">
		<div class="breadcrum">
			<ul class="breadcrumb">
				<li><a href="<?php echo base_url(); ?>">Home</a></li>
				<!--<li><span class="icon icon-keyboard_arrow_right"></span></li>-->
				<li class="active">My Profile</li>
			</ul>
		</div>
	</div>
</div><!-- breadcrum section -->


<div class="my-profile-page-full">
	<div class="container">
		<div class="row">
			<?php $this->load->view('common/profile_sidebar'); ?>

			<div class="col-sm-9 col-md-9">
				<div class="content-page">
					<div class="row">
						<div class="col-sm-4 col-md-6">
							<h1>Personal Information</h1>
						</div>
						<div class="col-sm-8 col-md-6 on-right">
							<button class="btn btn-primary" onclick="openChangePasswordPopup(<?= $customerData->id ?>)">Change Password</button>
							<button class="btn btn-primary" onclick="openChangeEmailPopup(<?= $customerData->id ?>)">Change Email</button>
						</div>
						<div class="col-sm-12">
							<div class="profile-complete" style="margin-top:15px;">
								<div>Profile Complete <!-- <span class="profile-percentage">60%</span>--> </div>
								<div class="progress">
									<div class="progress-bar" role="progressbar" aria-valuenow="<?= $profilePercentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $profilePercentage ?>%;">
										<?= $profilePercentage ?>%
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<form class="default-form" id="customer-personal-info-form" method="POST" action="<?php echo BASE_URL; ?>MyProfileController/updateCustomerInfo">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label>Firstname</label>
											<input type="text" class="form-control" placeholder="" value="<?= $_SESSION['FirstName'] ?>" id="first_name" name="first_name">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>Lastname</label>
											<input type="text" class="form-control" placeholder="" value="<?= $_SESSION['LastName'] ?>" id="last_name" name="last_name">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>Email</label>
											<input type="email" class="form-control" placeholder="" value="<?= $_SESSION['EmailID'] ?>" id="email" name="name" disabled="">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>Mobile Number</label>
											<input type="text" class="form-control" placeholder="" minlength="10" maxlength="10" value="<?= (isset($customerData->mobile_no) && $customerData->mobile_no != '') ? $customerData->mobile_no : '' ?>" id="mobile_no" name="mobile_no" disabled="">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>Country</label>
											<select class="form-control" name="country" id="country">
												<option value="" selected><?= lang('selectcountry') ?></option>
												<?php if (isset($countryList) && count($countryList) > 0) {
													foreach ($countryList as $data) { ?>
														<option value="<?php echo $data->country_code; ?>" <?php echo $data->country_code == $customerData->country_code ? "selected='selected'" : ''; ?>><?php echo $data->country_name; ?></option>
												<?php }
												} ?>
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label style="display: block;">Date of Birth</label>
											<input type="hidden" class="form-control" id="dob" value="<?= (isset($customerData->dob) && $customerData->dob != '') ? date("Y-m-d", strtotime($customerData->dob)) : '' ?>">
										</div>
									</div>
									<div class="col-md-12">
										<?php if (isset($restricted_access) && $restricted_access == 'yes') { ?>
											<div class="col-sm-6">
												<label><?= lang('companyname') ?></label>
												<input type="text" class="form-control" placeholder="" value="<?= (isset($customerData->company_name) && $customerData->company_name != '') ? $customerData->company_name : '' ?>" <?php if ($restricted_access == 'yes') {
																																																										echo "disabled";
																																																									} ?> id="company_name" name="company_name">
											</div>
											<div class="col-sm-6">
												<?php if ($shop_flag == 1) { ?>
													<label><?= lang('gst_number') ?></label>
												<?php } else { ?>
													<label><?= lang('vat_number') ?></label>
												<?php } ?>
												<input type="text" class="form-control" placeholder="" value="<?= (isset($customerData->gst_no) && $customerData->gst_no != '') ? $customerData->gst_no : '' ?>" <?php if ($restricted_access == 'yes') {
																																																						echo "disabled";
																																																					} ?> id="gst_no" name="gst_no">
											</div>
										<?php } ?>
										<div class="male-female-section">
											<label>Gender</label>
											<div class="male-female-inner">
												<label class="radio-label-checkout"><input class="radio-checkout " type="radio" value="male" name="gender" <?= (isset($customerData->gender) && $customerData->gender == 'male') ? 'checked="checked"' : '' ?>>Male <span class="radio-check"></span></label>
												<label class="radio-label-checkout"><input class="radio-checkout" type="radio" value="female" name="gender" <?= (isset($customerData->gender) && $customerData->gender == 'female') ? 'checked="checked"' : '' ?>>Female <span class="radio-check"></span></label>
											</div>
										</div>
									</div>
									<div class="col-md-12 text-center">
										<button type="submit" class="btn btn-primary">Submit</button>
										<a href="<?php echo base_url(); ?>" class="btn btn-secondary">Continue Shopping</a>
									</div>
								</div><!-- .row ends -->
							</form>
							<!-- END FORM-->
						</div>
					</div>
				</div><!-- .content-page ends -->
				<!-- ******************** MANAGE PERSONAL INFORMATION ENDS ******************** -->
			</div>
			<!--<h4 class="manage-add-head upc-head"><span>Personal Infomation </span><button class="black-btn float-right btn btn-primary" onclick="openChangePasswordPopup(<?= $customerData->id ?>)">Change Password</button>
							<button class="black-btn float-right btn btn-primary" onclick="openChangeEmailPopup(<?= $customerData->id ?>)">Change Email</button>
						</h4>-->

			<?php if (SHOP_ID === 1 && $this->session->userdata('CustomerTypeID') === 3) : ?>
				<!--<div style="font-weight: bold"><?= lang('zin_sync_member') ?></div>-->
			<?php endif; ?>

			<!--<div class="profile-complete">
							<p>Profile Complete <!-- <span class="profile-percentage">60%</span>--> </p>
			<!--<div class="progress">
							<div class="progress-bar" role="progressbar" aria-valuenow="<?= $profilePercentage ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $profilePercentage ?>%">
								<span class="sr-only"><?= $profilePercentage ?>%</span>
							</div>
							</div><!-- progress -->
			<!--</div><!-- profile complete -->
			<!--</div><!-- col-md-9 -->

		</div><!-- row -->
	</div><!-- container -->
</div><!-- my-profile-page-full -->

<?php $this->load->view('common/footer'); ?>
<script src="<?php echo SKIN_JS ?>myprofile.js?v=<?php echo CSSJS_VERSION; ?>"></script>
</body>

</html>