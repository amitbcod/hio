
	<div class="main-inner ">
	<div class="variant-common-block variant-list">
		<input type="hidden" id="hidden_footer_id" value="<?php echo $id; ?>">
		<input type="hidden" id="code" value="<?php echo $code; ?>">
		 <h1 class="head-name pad-bottom-20">Footer Block -  Translate in <?php echo  $codeName->name ;?>  </h1>
		<?php if(isset($getHomeBlock) && !empty($getHomeBlock)){ ?>
			<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Footer Block Heading</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="<?php if(isset($getHomeBlock)){ echo $getHomeBlock->heading; } ?>" name="bannerHeading_lang" id="bannerHeading_lang"  >
				<span class="error" id="rc-error"></span>
			</div>
		</div>
	<?php }else { ?>
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Footer Block Heading</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="<?php if(isset($banners)){ echo $banners->heading; } ?>" name="bannerHeading_lang" id="bannerHeading_lang"  >
				<span class="error" id="rc-error"></span>
			</div>
		</div>
	<?php } ?>
		<!-- form-group -->
	</div>
	<!-- variant-common-block -->
	<div class="download-discard-small ">
		<button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
	<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
		<button class="download-btn" type="button" id="type_apply"  onClick="SaveFooterBlock();">Save</button>
	<?php } ?>
	</div>
	<!-- download-discard-small -->
</div>
