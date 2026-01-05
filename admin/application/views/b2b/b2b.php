<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="profile-details busniess-details min-height-480">
		<div class="row">
			<form id="B2BAccessForm" method="POST" action="<?php echo base_url('B2BController/postB2BAccess') ?>" enctype="multipart/form-data">
			<div class="col-md-12">
				<h2>B2Webshop Details</h2>
				<div class="row">
					<div class="col-sm-6 profile-details-inner">
						<label>Enable  Business-2-Business</label>
						<div class="switch-onoff">
						<?php
							$checked = '';
$display = 'style="display: none;"';
if ($shopData->b2b_status == 1) {
	$checked ='checked';
	$display = '';
}
?>
						<label class="checkbox">
						<input type="checkbox" name="B2BStatusChk" id="B2BStatusChk" autocomplete="off" <?php echo $checked; ?> >
							<span class="checked"></span>
						</label>
						</div>
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 profile-details-inner">
						<label>Allow Access to Admin</label>
						<div class="switch-onoff">
						<label class="checkbox">
						<?php
$isEnabled = (isset($shopData->b2b_allow_access_to_admin) && $shopData->b2b_allow_access_to_admin == 1) ? 'checked' : '';
?>
						<input type="checkbox" name="adminAccessChk" id="adminAccessChk" autocomplete="off" <?php echo $isEnabled; ?> >
							<span class="checked"></span>
						</label>
						</div>
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 profile-details-inner role-in-company toggle-b2b-details" <?php echo $display; ?>>
						<label>Allow  Dropshipping</label>
						<div class="switch-onoff">
						<label class="checkbox">
						<?php
	$allowDropShip = (isset($b2bData->allow_dropship) && $b2bData->allow_dropship == 1) ? 'checked' : '';
$displayDropShip = (isset($b2bData->allow_dropship) && $b2bData->allow_dropship == 1) ? '' : 'display:none';
?>
						<input type="checkbox" name="dropShipChk" id="dropShipChk" autocomplete="off" <?php echo $allowDropShip; ?> >
							<span class="checked"></span>
						</label>
						</div><!-- checkbox switch-onoff -->

						<div class="profile-inside-box toggle-dropshp" style="<?php echo $displayDropShip;?>">
							<label>Dropshipping  Discount</label>
							<input class="form-control" type="text" name="dropshipDiscount" id="dropshipDiscount" value="<?php echo isset($b2bData->dropship_discount) ? $b2bData->dropship_discount : '';?>" placeholder="Discount in %">
						</div><!-- profile-inside-box -->

						<div class="profile-inside-box toggle-dropshp" style="<?php echo $displayDropShip;?>">
							<label>Dropshipping  Delivery Time <span class="required">*</span></label>
							<input class="form-control" type="text" name="dropshipTime" id="dropshipTime" value="<?php echo isset($b2bData->dropship_del_time) ? $b2bData->dropship_del_time : '';?>" placeholder="Days">
						</div><!-- profile-inside-box -->
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 profile-details-inner role-in-company toggle-b2b-details" <?php echo $display; ?> >
						<label>Allow  BuyIn</label>
						<div class="switch-onoff">
						<label class="checkbox">
						<?php
	$allowBuyIn = (isset($b2bData->allow_buyin) && $b2bData->allow_buyin == 1) ? 'checked' : '';
$displayBuyIn = (isset($b2bData->allow_buyin) && $b2bData->allow_buyin == 1) ? '' : 'display:none';
?>
						<input type="checkbox" name="buyInChk" id="buyInChk" autocomplete="off" <?php echo $allowBuyIn; ?>>
							<span class="checked"></span>
						</label>
						</div><!-- checkbox switch-onoff -->

						<div class="profile-inside-box toggle-buy-in" style="<?php echo $displayBuyIn;?>">
							<label>BuyIn  Discount</label>
							<input class="form-control" type="text" name="buyinDiscount" id="buyinDiscount" value="<?php echo isset($b2bData->buyin_discount) ? $b2bData->buyin_discount : '';?>" placeholder="Discount in %">
						</div><!-- profile-inside-box -->

						<div class="profile-inside-box toggle-buy-in" style="<?php echo $displayBuyIn;?>">
							<label>BuyIn  Delivery Time <span class="required">*</span></label>
							<input class="form-control" type="text" name="buyinTime" id="buyinTime" value="<?php echo isset($b2bData->buyin_del_time) ? $b2bData->buyin_del_time : '';?>" placeholder="Days">
						</div><!-- profile-inside-box -->
					</div><!-- col-sm-6 -->
					<div class="col-sm-6 profile-details-inner toggle-b2b-details" <?php echo $display; ?>>
						<label>Display Catalogues Overseas</label>
						<div class="switch-onoff">
						<label class="checkbox">
						<?php
	$displayCatlog = (isset($b2bData->display_catalog_overseas) && $b2bData->display_catalog_overseas == 1) ? 'checked' : '';
?>
						<input type="checkbox" name="catalogChk" id="catalogChk" autocomplete="off" <?php echo $displayCatlog; ?> >
							<span class="checked"></span>
						</label>
						</div>
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 profile-details-inner toggle-b2b-details" <?php echo $display; ?>>
						<label>Payment terms for seller</label>
						<div id="upload_paymentTermsFile">
						<?php
$imageSrc = '';
if (isset($b2bData->payments_terms_upload) && $b2bData->payments_terms_upload != '') {
	$ext = pathinfo($b2bData->payments_terms_upload, PATHINFO_EXTENSION);
	if ($ext == 'pdf') {
		$imageSrc=SKIN_IMG.'pdf-icon.png';
	} elseif ($ext == 'doc' || $ext == 'docx') {
		$imageSrc=SKIN_IMG.'document-icon.png';
	} else {
		$imageSrc=get_s3_url('documents/payment_terms/'.$b2bData->payments_terms_upload);
	}
	$payment_url=get_s3_url('documents/payment_terms/'.$b2bData->payments_terms_upload);
	?>
							<span class="single-img"><a href="javascript:void(0);" onclick="removeFile('paymentTermsFile')" class="rm-media">X</a><a  target ="_blank" href="<?php echo $payment_url;  ?>"><img class="thumb" src="<?php echo $imageSrc;?>" title=""></a></span>
						<?php } ?>
						</div>
						<div class="custom-file"  style="<?php echo ($imageSrc == '') ? 'display:block' : 'display:none'?>">
						  <input type="file" class="custom-file-input" name="paymentTermsFile" id="paymentTermsFile" aria-describedby="paymentTermsFile" onchange="readFileURL(this);">
						  <label class="custom-file-label" for="paymentTermsFile">Upload</label>
						</div>
						<input type="hidden" name="hidden_paymentTermsFile" id="hidden_paymentTermsFile" value="<?php echo ($imageSrc != '') ? $b2bData->payments_terms_upload : '';?>">
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 profile-details-inner mar-top-zero toggle-b2b-details" <?php echo $display; ?>>
						<label>Permission to change the price</label>
						<div class="switch-onoff">
						<label class="checkbox">
						<?php
		$canChangePrice = (isset($b2bData->perm_to_change_price) && $b2bData->perm_to_change_price == 1) ? 'checked' : '';
$displayChangePrice = (isset($b2bData->perm_to_change_price) && $b2bData->perm_to_change_price == 1) ? '' : 'display:none';
?>
						<input type="checkbox" name="priceChk" id="priceChk" autocomplete="off" <?php echo $canChangePrice; ?> >
							<span class="checked"></span>
						</label>
						</div>

						<div class="col-sm-12 profile-details-inner mar-top-zero toggle-price" style="<?php echo $displayChangePrice;?>">
							<label class="pad-left-20">Can Increase the Price <span class="required">*</span></label>
							<div class="switch-onoff">
							<label class="checkbox">
							<?php
		$canIncPrice = (isset($b2bData->can_increase_price) && $b2bData->can_increase_price == 1) ? 'checked' : '';
?>
							<input type="checkbox" name="incPriceChk" id="incPriceChk" autocomplete="off" <?php echo $canIncPrice; ?> class="price-chk" >
								<span class="checked"></span>
							</label>
							</div>
						</div><!-- col-sm-6 -->

						<div class="col-sm-12 profile-details-inner mar-top-zero toggle-price" style="<?php echo $displayChangePrice;?>">
							<label class="pad-left-20">Can Decrease the Price</label>
							<div class="switch-onoff">
							<label class="checkbox">
							<?php
	$canDecPrice = (isset($b2bData->can_decrease_price) && $b2bData->can_decrease_price == 1) ? 'checked' : '';
?>
							<input type="checkbox" name="decPriceChk" id="decPriceChk" autocomplete="off" <?php echo $canDecPrice; ?> class="price-chk" >
								<span class="checked"></span>
							</label>
							</div>
						</div><!-- col-sm-6 -->
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 profile-details-inner mar-top-zero toggle-b2b-details" <?php echo $display; ?>>
						<label>Terms and Conditions</label>
						<div id="upload_termsCondFile">
						<?php
						$imageSrc = '';
if (isset($b2bData->terms_condition_upload) && $b2bData->terms_condition_upload != '') {
	$ext = pathinfo($b2bData->terms_condition_upload, PATHINFO_EXTENSION);
	if ($ext == 'pdf') {
		$imageSrc=SKIN_IMG.'pdf-icon.png';
	} elseif ($ext == 'doc' || $ext == 'docx') {
		$imageSrc=SKIN_IMG.'document-icon.png';
	} else {
		$imageSrc = get_s3_url('documents/terms_condition/'.$b2bData->terms_condition_upload);
	}
	$tnc_url = get_s3_url('documents/terms_condition/'.$b2bData->terms_condition_upload);
	?>
							<span class="single-img"><a href="javascript:void(0);" onclick="removeFile('termsCondFile')" class="rm-media">X</a><a target="_blank"

							 href="<?php echo $tnc_url;  ?>"><img class="thumb" src="<?php echo $imageSrc;?>" title=""></a></span>
						<?php } ?>
						</div>
						<div class="custom-file" style="<?php echo ($imageSrc == '') ? 'display:block' : 'display:none'?>">
						  <input type="file" class="custom-file-input" name="termsCondFile" id="termsCondFile" aria-describedby="termsCondFile" onchange="readFileURL(this);">
						  <label class="custom-file-label" for="termsCondFile">Upload</label>
						</div>
						<input type="hidden" name="hidden_termsCondFile" id="hidden_termsCondFile" value="<?php echo ($imageSrc != '') ? $b2bData->terms_condition_upload : '';?>">
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 profile-details-inner toggle-b2b-details" <?php echo $display; ?>>
						<label>Payment Term</label>
						<div class="switch-onoff">
						<label class="checkbox">
						<?php
		$payment_term = (isset($b2bData->enable_payment_term) && $b2bData->enable_payment_term == 1) ? 'checked' : '';
?>
						<input type="checkbox" name="payment_term" id="payment_term" autocomplete="off" <?php echo $payment_term; ?> >
							<span class="checked"></span>
						</label>
						</div>
					</div><!-- col-sm-6 -->
				</div>

				<div class="col-md-12 toggle-b2b-details" <?php echo $display; ?>>
					<div class="table-responsive text-center force-overflow">
						<table class="table table-bordered table-style" id="b2bCatList">
							<thead>
								<tr>
								<th>Categories</th>
								<th>Sub - Categories</th>
								<th>Catalogues To Be Display</th>
								</tr>
							</thead>
						  	<tbody>
							<?php
							if (!empty($catSubCatData)) {
								foreach ($catSubCatData as $value) {
									$readonly_attr='';
									if ($value->cat_level==1) {
										$readonly_attr='';
										if (!empty($cat_parents)) {
											$readonly_attr='';
											if (in_array($value->id, $cat_parents)) {
												$readonly_attr='readonly';
											}
										}
									}
									?>
							<tr>
							  <td><?php echo $value->cat_name; ?></td>
							  <td><?php echo $value->sub_cat_name; ?></td>
							  <td>
								<label class="checkbox">
								<input type="checkbox" class="b2b-cat-check <?= ($value->parent_id>0) ? 'b2b-pc-'.$value->parent_id : 'b2b-c-'.$value->id; ?>" name="b2bEnable[]" autocomplete="off" value="<?php echo $value->id; ?>" <?php echo ($value->b2b_enabled == 1) ? 'checked' : ''; ?>   <?php echo $readonly_attr; ?> onclick="B2BCheckRelatedCat(this,<?php echo $value->id; ?>,<?php echo $value->cat_level; ?>)">
									<span class="checked"></span>
								</label>
							  </td>
							</tr>
							<?php }
								}
?>
							</tbody>
						</table>
					  </div>
				</div>
			<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/b2webshop/write', $this->session->userdata('userPermission'))) { ?>
				<div class="save-discard-btn">
					<input type="submit" value="Save" class="purple-btn">
				</div>
			<?php } ?>
			</div>
			</form>
		</div><!-- row -->
	</div><!-- profile-details-block -->
</main>
<script src="<?php echo SKIN_JS; ?>b2b.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
