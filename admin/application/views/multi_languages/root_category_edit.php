
	<div class="main-inner ">
	<div class="variant-common-block variant-list">
		<input type="hidden" id="hidden_cat_id" value="<?php echo (isset($CategoryDetail->id))?$CategoryDetail->id:''; ?>">
		<input type="hidden" id="code" value="<?php echo $code; ?>">
		<h1 class="head-name pad-bottom-20">Category Translate in <?php echo  $codeName->name ;?> for <?php echo $CategoryDetail->cat_name; ?></h1>
		<?php if(isset($getLang) && !empty($getLang)) { ?>	
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Category Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="<?php echo (isset($getLang->cat_name))?$getLang->cat_name:''; ?>" name="cat_name" id="cat_name"  >
				<span class="error" id="rc-error"></span>
			</div>
		</div>
	<?php } else{ ?>
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Category Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="<?php echo (isset($CategoryDetail->cat_name))?$CategoryDetail->cat_name:''; ?>" name="cat_name" id="cat_name"  >
				<span class="error" id="rc-error"></span>
			</div>
		</div>
	<?php } ?>
		<!-- form-group -->

   <?php if(isset($getLang) && !empty($getLang)) { ?>	
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Category Description</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" value="<?php echo (isset($getLang->cat_description))?$getLang->cat_description:''; ?>"  name="cat_description" id="cat_description" >
			</div>
		</div>
	<?php } else{ ?>
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Category Description</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" value="<?php echo (isset($CategoryDetail->cat_description))?$CategoryDetail->cat_description:''; ?>"  name="cat_description" id="cat_description" >
			</div>
		</div>
	<?php } ?>
		<!-- form-group -->
		
	</div>
	<!-- variant-common-block -->
	<div class="download-discard-small ">
		<button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
	<?php if(empty($this->session->userdata('userPermission')) || in_array('seller/catalog_translation/write',$this->session->userdata('userPermission'))){ ?>
		<button class="download-btn" type="button" id="type_apply"  onClick="SaveExistingCategory();">Save</button>
	<?php } ?>
	</div>
	<!-- download-discard-small -->
</div>
