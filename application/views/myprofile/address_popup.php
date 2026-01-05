
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h3><?= ($flag == 'add')?lang('add'):lang('edit')?>Add Address</h3>
</div>
<div class="modal-body">
<div class="row ">
	<div class="content-page shadow">
	<form id="address-form" method="POST" action="<?php echo BASE_URL;?>MyProfileController/addEditAddress">
		<input type="hidden" id="flag" name="flag" value="<?= $flag?>">
		<input type="hidden" id="address_id" name="address_id" value="<?= $address_id?>">
		<?php //echo '<pre>';print_r($addressData);?>
		  <div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class="">First Name*</label>
						<input class="form-control" type="text" name="first_name" id="first_name" placeholder="First Name*" onkeypress="return isCharKey(event);" value="<?= (isset($addressData) && $addressData[0]->first_name != '')?$addressData[0]->first_name:''?>">
					</div>
				</div><!-- form-box -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class="">Last Name*</label>
						<input class="form-control" type="text" name="last_name" id="last_name" placeholder="Last Name*" onkeypress="return isCharKey(event);" value="<?= (isset($addressData) && $addressData[0]->last_name != '')?$addressData[0]->last_name:''?>">
					</div>
				</div><!-- form-box -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class="">Mobile Number</label>
						<input class="form-control" type="tel" id="mobile_no" name="mobile_no" onkeypress="return isNumberKey(event)" placeholder="Mobile Number"  value="<?= (isset($addressData) && $addressData[0]->mobile_no != '')?$addressData[0]->mobile_no:''?>">
					</div>
				</div><!-- form-box -->
			<!--</div><!-- first block-->

			<!--<div class="row">-->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class="">Building/Wing Number</label>
						<input class="form-control" type="text" id="address_line1" name="address_line1" placeholder="Building/Wing Number*" value="<?= (isset($addressData) && $addressData[0]->address_line1 != '')?$addressData[0]->address_line1:''?>" maxlength="35">
					</div>
				</div><!-- form-box -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class="">Address Line 2</label>
						<input class="form-control" type="text" id="address_line2" name="address_line2" placeholder="Address Line2" value="<?= (isset($addressData) && $addressData[0]->address_line2 != '')?$addressData[0]->address_line2:''?>" maxlength="35">
					</div>
				</div><!-- form-box -->			

				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class="">Country*</label>
							<select name="country" id="country" class="form-control">
								<!-- <option value="" selected>Select Country</option> -->
								<?php if (isset($countryList) && count($countryList) > 0) {
									foreach ($countryList as $data) { 
									 	if ($data->country_code == 'MU') {
										?>
											<option value="<?php echo $data->country_code; ?>"  <?php echo (isset($addressData) && $data->country_code == $addressData[0]->country)? "selected='selected'" : ''; ?>><?php echo $data->country_name; ?></option>
									<?php }
								 	}	
								} ?>
							</select>	
					</div>
					
				</div>
				<div class="col-md-6 state_div">
					<!-- onkeypress="return isCharKey(event);" -->
					<div class="form-group">
						<label for="new_password" class="">State*</label>
							<input class="form-control validate-char" type="text" id="state" name="state" placeholder="State"  value="<?= (isset($addressData) && $addressData[0]->state != '')?$addressData[0]->state:''?>">
					</div>
					
				</div><!-- form-box -->
				<div class="col-sm-6 dp_state_div">
					<div class="form-group">
						<label for="new_password" class="">State*</label>
						<select name="state_dp" id="state_dp" class="form-control">
							<option value="" selected><?= lang('select_state') ; echo 'Select State'?></option>
							<?php if (isset($stateList) && count($stateList) > 0) {
							foreach ($stateList as $data) { ?>
									<option value="<?php echo $data->id; ?>"  <?php echo (isset($addressData) && $data->state_name == $addressData[0]->state)? "selected='selected'" : ''; ?>><?php echo $data->state_name; ?></option>
							<?php }
							} ?>
						</select>
					</div>
				</div><!-- form-box -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class="">City*</label>
						<select class="form-control" name="city" id="shipping_city">
							<option value="">Select City</option>
							<?php if (isset($cityList) && count($cityList) > 0) {
								foreach ($cityList as $city) { ?>
									<option value="<?php echo $city->id; ?>"
											data-state="<?php echo $city->state_id; ?>"
											<?php if ($city->id == $addressData[0]->city) echo "selected"; ?>>
										<?php echo $city->city_name; ?>
									</option>
							<?php } } ?>
						</select>
						<!-- <input class="form-control validate-char" type="text" id="city" name="city" placeholder="City*" onkeypress="return isCharKey(event);" value="<?= (isset($addressData) && $addressData[0]->city != '')?$addressData[0]->city:''?>"> -->
					</div>
				</div><!-- form-box -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="new_password" class="">Postal Code*</label>
						<input class="form-control" type="text" id="pincode" name="pincode" placeholder="Postal Code*" onkeypress="return isNumberKey(event)" maxlength="50" value="<?= (isset($addressData) && $addressData[0]->pincode != '')?$addressData[0]->pincode:''?>">
					</div>
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
          <div class="form-group">

            <div class="col-md-12">

			<input type="submit" class="black-btn blue-btn tw-button-zumbared btn btn-primary" name="address-btn" id="address-btn" value="Submit">

              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

            </div>

          </div>

        </form>

      </div>
      
</div>
</div>
<script src="<?php echo SKIN_JS ?>add_edit_address.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script>
	$(document).ready(function() {
 		var base_url = "<?php echo base_url(); ?>";
		$("#state_dp").on("change", function () {
			var stateId = $(this).val();

			// Reset city dropdown
			$("#shipping_city").empty().append('<option value="">Select City</option>');

			if (stateId != "") {
				$.ajax({
					url: base_url + "MyProfileController/getCities", // Your controller function
					type: "POST",
					data: { state_id: stateId },
					dataType: "json",
					success: function (res) {
						if (res.status == "success" && res.cities.length > 0) {
							$.each(res.cities, function (i, city) {
								$("#shipping_city").append(
									'<option value="' + city.id + '">' + city.city_name + '</option>'
								);
							});
						}
					},
				});
			}
		});
	});
</script>
