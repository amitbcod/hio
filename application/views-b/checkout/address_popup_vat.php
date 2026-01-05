<div class="popup-form">
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
	<div class="manage-address-popup">
		<h3><?= ($flag == 'add')?lang('Add'):lang('edit')?> <?=lang('Address')?></h3>
		<form id="address-form" method="POST" action="<?php echo BASE_URL;?>CheckoutController/addEditAddress">
		<input type="hidden" id="flag" name="flag" value="<?= $flag?>">
		<input type="hidden" id="address_id" name="address_id" value="<?= $address_id?>">
		<?php //echo '<pre>';print_r($addressData);?>
		<div class="forgotpassword-form row">
		

				<div class="form-box col-sm-6">
					<input class="form-control" type="text" id="company_name" name="company_name" placeholder="Company Name"  value="<?= (isset($addressData) && $addressData[0]->company_name != '')?$addressData[0]->company_name:''?>">
				</div><!-- form-box -->

				<div class="form-box col-sm-6">
					<input class="form-control vat_no_ignore" type="text" id="vat_no" name="vat_no" placeholder="Vat No"  value="<?= (isset($addressData) && $addressData[0]->vat_no != '')?$addressData[0]->vat_no:''?>">
				</div><!-- form-box -->

				<div class="form-box col-sm-6">
					<input class="form-control consulation_no" type="hidden" id="consulation_no" name="consulation_no" placeholder="Consultation No"  value="<?= (isset($addressData) && $addressData[0]->consulation_no != '')?$addressData[0]->consulation_no:''?>">
				</div><!-- form-box -->

				<div class="form-box col-sm-6">
					<input class="form-control res_company_name" type="hidden" id="res_company_name" name="res_company_name" placeholder="Res Company Name"  value="<?= (isset($addressData) && $addressData[0]->res_company_name != '')?$addressData[0]->res_company_name:''?>">
				</div><!-- form-box -->

				<div class="form-box col-sm-6">
					<input class="form-control res_company_address" type="hidden" id="res_company_address" name="res_company_address" placeholder="Res Company Address"  value="<?= (isset($addressData) && $addressData[0]->res_company_address != '')?$addressData[0]->res_company_address:''?>">
				</div><!-- form-box -->



			<div class="signin-btn col-sm-12">
				<input type="submit" class="black-btn blue-btn tw-button-zumbared" name="address-btn" id="address-btn" value="Submit">
			</div><!-- signin-btn -->
		</div><!-- sigin-form -->
		</form>

	</div><!-- sign-in-inner -->
</div><!-- grey-bg-user -->

<script type="text/javascript" src="<?php echo SKIN_JS; ?>address_popup_vat.js?v=<?php echo CSSJS_VERSION; ?>"></script>
