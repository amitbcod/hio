<?php $this->load->view('common/fbc-user/header'); ?>
<style> .customize-add-inner-sec {
    margin-bottom: 39px;
}</style>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    	<li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
		<li class="active"><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
		<li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
		<li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
		
  	</ul>
  	<div class="tab-content">
    	<div id="customize-tab" class="tab-pane fade in active common-tab-section min-height-480" style="opacity:1;">
	
      		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          		<h1 class="head-name pad-bt-20"> &nbsp; <?php if(isset($sBlock)){ echo "Edit";}else{ echo "Add New";} ?>
          	</h1> 
          		
        	</div>
		
	        <!-- form -->
	        <div class="content-main form-dashboard">
            	<form method="POST" action="<?= base_url('WebshopController/submitStaticBlock') ?>" id="staticBlockForm">
            		<input type="hidden" name="block_id" value="<?= isset($sBlock) ? $sBlock->id : '' ?>">
            		<div class="customize-add-section">
						<div class="row col-sm-12">
							<div class="col-sm-6">
								<div class="customize-add-inner-sec">
									<label for="blockType">Type *</label>
									
									<select name="blockType" id="blockType" class="form-control" required <?= isset($sBlock) ? 'disabled' : '' ?>>
										<option value="">Select Type</option>
										<option value="1" <?= isset($sBlock) && $sBlock->type == 1 ? 'Selected' : '' ?>>Block</option>
										<option value="2" <?= isset($sBlock) && $sBlock->type == 2 ? 'Selected' : '' ?>>Header Scripts</option>
										<option value="3" <?= isset($sBlock) && $sBlock->type == 3 ? 'Selected' : '' ?>>Footer Scripts</option>
										<option value="4" <?= isset($sBlock) && $sBlock->type == 4 ? 'Selected' : '' ?>>Menus</option>
										<option value="5" <?= isset($sBlock) && $sBlock->type == 5 ? 'Selected' : '' ?>>Banner</option>
										<option value="6" <?= isset($sBlock) && $sBlock->type == 6 ? 'Selected' : '' ?>>Other</option>
									</select>
								</div><!-- col-sm-6 -->

								<div class="customize-add-inner-sec">
									<label for="blockTitle">Block Title *</label>
									<input class="form-control" type="text" name="blockTitle" id="blockTitle" value="<?= isset($sBlock) ? $sBlock->title : '' ?>" placeholder="Enter Block Title here" required>
								</div><!-- col-sm-6 -->

								<div class="customize-add-inner-sec">
									<label for="blockIdentifier">Block Identifier *</label>
									<input class="form-control" type="text" name="blockIdentifier" id="blockIdentifier" value="<?= isset($sBlock) ? $sBlock->identifier : '' ?>" 
									<?php 
										if(isset($sBlock) && $sBlock->is_default == 1)
										{echo "readonly";}
									?>
									placeholder="Block Identifier" required>
								</div><!-- col-sm-6 -->

								<div class="customize-add-inner-sec">
									<label for="status">Status *</label>
									<div class="customize-add-radio-section row">
										<div class="radio col-sm-6">
										  <label>
										  	<input type="radio" name="status" value="1" id="published" <?= isset($sBlock) && $sBlock->status == 1 ? 'checked' : 'checked' ?> >Published <span class="checkmark"></span>
										  </label>
										</div><!-- radio -->
										<div class="radio col-sm-6">
										  <label>
										  	<input type="radio" name="status" value="2" id="hold" <?= isset($sBlock) && $sBlock->status == 2 ? 'checked' : '' ?>>On-Hold <span class="checkmark"></span>
										  </label>
										</div><!-- radio -->
									</div><!-- customize-add-radio-section -->
								</div>
							</div>
							<div class="col-sm-6">
								<div class="customize-add-inner-sec page-content-textarea">
									<label for="blockContent">Block Content *</label>
									<textarea class="form-control" name="blockContent" id="blockContent" required><?= isset($sBlock) ? $sBlock->content : '' ?></textarea>
								</div><!-- col-sm-6 -->

							</div>
						</div><!-- row -->
						<?php 
						 if(isset($sBlock) && !empty($sBlock)){
						 if($sBlock->identifier == 'headerscript' || $sBlock->identifier == 'footerscript'){
						 	echo '';
						  }else{ 
							 ?>
							
						    <?php } ?>
						  <?php } ?>
						<div class="download-discard-small ">
									<button class="white-btn" type="button" onclick="window.location.href='<?= base_url('webshop/static-blocks') ?>';">Discard</button>
								<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
									<button class="download-btn" type="submit">Save</button>
								<?php } ?>
								 </div><!-- download-discard-small  -->
					</div><!-- customize-add-section -->
            	</form>
        	</div>
        	<!--end form-->
    	</div>
    </div>
</main>
<?php if(isset($sBlock)&&!empty($sBlock) && ($sBlock->identifier == 'headerscript' || $sBlock->identifier == 'footerscript')){
 }else{ ?>
<script type="text/javascript">
CKEDITOR.replace( 'blockContent', {
	extraPlugins :'justify',
 filebrowserUploadMethod: "form",

  filebrowserUploadUrl: BASE_URL+"UploadCKEditorFilesController/upload"
 });
 
CKEDITOR.config.allowedContent = true;
CKEDITOR.config.protectedSource.push(/<\?[\s\S]*?\?>/g);
</script>
<?php } ?>
<script src="<?php echo SKIN_JS; ?>webshop.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>