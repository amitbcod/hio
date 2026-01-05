
	<div class="main-inner ">
	<div class="variant-common-block variant-list">
		<input type="hidden" id="hidden_menu_id" value="<?php echo $id; ?>">
		<input type="hidden" id="code" value="<?php echo $code; ?>">
		<h1 class="head-name pad-bottom-20">Top Menu - <?php echo  $menu_details->menu_name ;?> Translate in <?php echo  $codeName->name ;?>  </h1>
		<?php if(isset($getMenu) && !empty($getMenu)) { ?>	
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Menu Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="<?php echo (isset($getMenu->menu_name))?$getMenu->menu_name:''; ?>" name="menu_name" id="menu_name"  >
				<span class="error" id="rc-error"></span>
			</div>
		</div>
	<?php } else{ ?>
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Menu Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="<?php echo (isset($menu_details->menu_name))?$menu_details->menu_name:''; ?>" name="menu_name" id="menu_name"  >
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
		<button class="download-btn" type="button" id="type_apply"  onClick="SaveExistingMenu();">Save</button>
	<?php } ?>
	</div>
	<!-- download-discard-small -->
</div>
