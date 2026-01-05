<div class="popup-form<?php echo (THEMENAME=='theme_zumbawear') ? ' grey-bg-user' : ''; ?>">
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
	<div class="manage-address-popup">
		<h3><?= ($flag == 'add')?lang('add'):lang('edit')?>Add Address</h3>
		<form id="address-form" method="POST" action="<?php echo BASE_URL;?>MyProfileController/addEditAddress">
		<input type="hidden" id="flag" name="flag" value="<?= $flag?>">
		<input type="hidden" id="address_id" name="address_id" value="<?= $address_id?>">
		<?php //echo '<pre>';print_r($addressData);?>
		<div class="forgotpassword-form row">
			<div class="row tw-w-full">
				<div class="form-box col-sm-6">
					<input class="form-control" type="text" name="first_name" id="first_name" placeholder="First Name*" onkeypress="return isCharKey(event);" value="<?= (isset($addressData) && $addressData[0]->first_name != '')?$addressData[0]->first_name:''?>">
				</div><!-- form-box -->
				<div class="form-box col-sm-6">
					<input class="form-control" type="text" name="last_name" id="last_name" placeholder="Last Name*" onkeypress="return isCharKey(event);" value="<?= (isset($addressData) && $addressData[0]->last_name != '')?$addressData[0]->last_name:''?>">
				</div><!-- form-box -->
				<div class="form-box col-sm-6">
					<input class="form-control" type="tel" id="mobile_no" name="mobile_no" onkeypress="return isNumberKey(event)"  minlength="7" maxlength="15" placeholder="Mobile Number"  value="<?= (isset($addressData) && $addressData[0]->mobile_no != '')?$addressData[0]->mobile_no:''?>">
				</div><!-- form-box -->
			</div><!-- first block-->

			<div class="row tw-w-full">
				<div class="form-box col-sm-6">
					<input class="form-control" type="text" id="address_line1" name="address_line1" placeholder="Address Line1*" value="<?= (isset($addressData) && $addressData[0]->address_line1 != '')?$addressData[0]->address_line1:''?>" maxlength="35">
				</div><!-- form-box -->
				<div class="form-box col-sm-6">
					<input class="form-control" type="text" id="address_line2" name="address_line2" placeholder="Address Line2" value="<?= (isset($addressData) && $addressData[0]->address_line2 != '')?$addressData[0]->address_line2:''?>" maxlength="35">
				</div><!-- form-box -->

				<div class="form-box col-sm-6">
					<input class="form-control" type="text" id="pincode" name="pincode" placeholder="Postal Code*" onkeypress="return isNumberKey(event)" maxlength="50" value="<?= (isset($addressData) && $addressData[0]->pincode != '')?$addressData[0]->pincode:''?>">
				</div><!-- form-box -->

				<div class="form-box col-sm-6">
					<input class="form-control validate-char" type="text" id="city" name="city" placeholder="City*" onkeypress="return isCharKey(event);" value="<?= (isset($addressData) && $addressData[0]->city != '')?$addressData[0]->city:''?>">
				</div><!-- form-box -->

				<div class="form-box col-sm-6">
					<select name="country" id="country" class="form-control">
					<option value="" selected>Select Country</option>
					<?php if (isset($countryList) && count($countryList) > 0) {
					foreach ($countryList as $data) { ?>
							<option value="<?php echo $data->country_code; ?>"  <?php echo (isset($addressData) && $data->country_code == $addressData[0]->country)? "selected='selected'" : ''; ?>><?php echo $data->country_name; ?></option>
					<?php }
					} ?>
				</select>
				</div>
				<div class="form-box col-sm-6 state_div">
					<!-- onkeypress="return isCharKey(event);" -->
					<input class="form-control validate-char" type="text" id="state" name="state" placeholder="State"  value="<?= (isset($addressData) && $addressData[0]->state != '')?$addressData[0]->state:''?>">
				</div><!-- form-box -->
				<div class="form-box col-sm-6 dp_state_div">
					<select name="state_dp" id="state_dp" class="form-control">
						<option value="" selected><?=lang('select_state')?></option>
						<?php if (isset($stateList) && count($stateList) > 0) {
						foreach ($stateList as $data) { ?>
								<option value="<?php echo $data->state_name; ?>"  <?php echo (isset($addressData) && $data->state_name == $addressData[0]->state)? "selected='selected'" : ''; ?>><?php echo $data->state_name; ?></option>
						<?php }
						} ?>
					</select>
				</div><!-- form-box -->

			</div><!-- second block-->
			<div class="row tw-w-full">

				<input type="hidden" id="session_vat_flag" value="<?=$this->session->session_vat_flag ?? '0'?>">
				<?php if($this->session->session_vat_flag === 1): ?>
				<div class="form-box col-sm-6">
					<input class="form-control" type="text" id="company_name" name="company_name" placeholder="<?=lang('companyname')?>"  value="<?= (isset($addressData) && $addressData[0]->company_name != '')?$addressData[0]->company_name:''?>">
				</div><!-- form-box -->

				<div class="form-box col-sm-6">
					<input class="form-control"  <?php echo  (isset($addressData) && $addressData[0]->vat_no != '')?'readonly':'';?>  type="text" id="vat_no" name="vat_no" placeholder="<?=lang('vatno')?>"  value="<?= (isset($addressData) && $addressData[0]->vat_no != '')?$addressData[0]->vat_no:''?>" onkeyup="this.value = this.value.toUpperCase();">
					<div class="loaderDiv" style="display: none"><span ><?=lang('please_wait')?><div class="loader"></div></span></div>
						<input type="hidden" name="vat_flag" id="vatFlag" value="0">
				</div><!-- form-box -->
			</div><!-- third block-->
			<div class="form-box col-sm-6">
				<input class="form-control" type="hidden" id="consulation_no" name="consulation_no" placeholder="Consulation No"  value="<?= (isset($addressData) && $addressData[0]->consulation_no != '')?$addressData[0]->consulation_no:''?>">
			</div><!-- form-box -->

			<div class="form-box col-sm-6">
				<input class="form-control" type="hidden" id="res_company_name" name="res_company_name" placeholder="Res Company Name"  value="<?= (isset($addressData) && $addressData[0]->res_company_name != '')?$addressData[0]->res_company_name:''?>">
			</div><!-- form-box -->

			<div class="form-box col-sm-6">
				<input class="form-control" type="hidden" id="res_company_address" name="res_company_address" placeholder="Res Company Address"  value="<?= (isset($addressData) && $addressData[0]->res_company_address != '')?$addressData[0]->res_company_address:''?>">
			</div><!-- form-box -->

			<?php endif; ?>

			<div class="signin-btn col-sm-12">
				<input type="submit" class="black-btn blue-btn tw-button-zumbared" name="address-btn" id="address-btn" value="Submit">
			</div><!-- signin-btn -->
		</div><!-- sigin-form -->
		</form>

	</div><!-- sign-in-inner -->
</div><!-- grey-bg-user -->
<script src="<?php echo SKIN_JS ?>add_edit_address.js?v=<?php echo CSSJS_VERSION; ?>"></script>
