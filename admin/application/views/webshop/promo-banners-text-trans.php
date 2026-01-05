
	<div class="main-inner ">
	<div class="variant-common-block variant-list">
		<input type="hidden" id="promoId" value="<?php echo $id; ?>">
		<input type="hidden" id="code" value="<?php echo $code; ?>">
	    <h1 class="head-name pad-bottom-20">Promo Text Banners - Translate in <?php echo  $codeName->name ;?>  </h1> 
		<?php if(isset($getPromoText) && !empty($getPromoText)) { ?>	
		<div class="form-group row">	
			<div class="col-sm-12 customize-add-inner-sec page-content-textarea">
				
				<label for="pageTitle">Banner Text *</label>
                              <input class="form-control" type="text" name="banner_text" id="banner_text" maxlength="500" value="<?php if(isset($getPromoText)){ echo $getPromoText->banner_text; } ?>" placeholder="Enter Banner Text here" required>
                             
			</div>
		</div>
	<?php } else{ ?>
		<div class="form-group row">
			
			<div class="col-sm-12 customize-add-inner-sec page-content-textarea">
								
								
								<label for="pageTitle">Banner Text *</label>
                              <input class="form-control" type="text" name="banner_text" id="banner_text" maxlength="500" value="<?php if(isset($BannersDetails)){ echo $BannersDetails->banner_text; } ?>" placeholder="Enter Banner Text here" required>
                             
							</div>
		</div>
    <?php } ?> 
	
		<!-- form-group -->
		
	</div>
	<!-- variant-common-block -->
	<div class="download-discard-small ">
		<button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
	<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
		<button class="download-btn" type="button" id="type_apply"  onClick="savePromeBannersTrans();"><?php if(isset($getPromoText)){ echo "Update"; }else{ echo "Save"; }?></button>
	<?php } ?>
	</div>
	<!-- download-discard-small -->
</div>

