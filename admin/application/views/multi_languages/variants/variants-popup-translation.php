
	<div class="main-inner ">
	<div class="variant-common-block variant-list">
		 <input type="hidden" id="id" value="<?php echo (isset($variant->id))?$variant->id:''; ?>"> 
		 <input type="hidden" id="attr_type" value="<?php echo (isset($variant->attr_type))?$variant->attr_type:''; ?>"> 
		 <input type="hidden" id="code" value="<?php echo $code; ?>"> 
		<h1 class="head-name pad-bottom-20">Variant Translate in <?php echo  $codeName->name ;?> for <?php echo $variant->attr_name; ?></h1>
		<?php if(isset($MultiLangVariant) && !empty($MultiLangVariant)) { ?>	
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Variant Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="<?php echo (isset($MultiLangVariant->attr_name))?$MultiLangVariant->attr_name:''; ?>" name="variant_name" id="variant_name"  >
				<span class="error" id="rc-error"></span>
			</div>
		</div>
	<?php } else{ ?>
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Variant Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="<?php echo (isset($variant->attr_name))?$variant->attr_name:''; ?>" name="variant_name" id="variant_name"  >
				<span class="error" id="rc-error"></span>
			</div>
		</div>
	<?php } ?>
		<!-- form-group -->

   <?php if(isset($MultiLangVariant) && !empty($MultiLangVariant)) { ?>	
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Variant Description</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" value="<?php echo (isset($MultiLangVariant->attr_description))?$MultiLangVariant->attr_description:''; ?>"  name="variant_desc" id="variant_desc" >
			</div>
		</div>
	<?php } else{ ?>
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Variant Description</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" value="<?php echo (isset($variant->attr_description))?$variant->attr_description:''; ?>"  name="variant_desc" id="variant_desc" >
			</div>
		</div>
	<?php } ?>
		<!-- form-group -->
		
	</div>
	<!-- variant-common-block -->
	<div class="download-discard-small ">
		<button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
	<?php if(empty($this->session->userdata('userPermission')) || in_array('seller/variants_translation/write',$this->session->userdata('userPermission'))){ ?>
		<button class="download-btn" type="button" id="type_apply"  onClick="saveVariantTranslation();">Save</button>
	<?php } ?>
	</div>
	<!-- download-discard-small -->
</div>
