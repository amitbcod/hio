<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    	<li class="active"><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
		<li><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
		<li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
		<li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
		
  	</ul>
  	<div class="tab-content">
    	<div id="customize-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
	
      		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          		<h1 class="head-name pad-bt-20"> &nbsp; <?php if(isset($page)){ echo "Edit Page"; }else{ echo "Add New Page"; } ?> </h1> 
        	</div>
		
	        <!-- form -->
	        <div class="content-main form-dashboard">
            	<form method="POST" action="<?= base_url('WebshopController/submitCMSPage')?>" id="cmspageForm">
            		<input type="hidden" name="page_id" value="<?php if(isset($page)){ echo $page->id; } ?>">
            		<div class="customize-add-section">
						<div class="row col-md-12">
							<div class="col-sm-7 customize-add-inner-sec">
								<label for="pageTitle">Page Title *</label>
								<input class="form-control" type="text" name="pageTitle" id="pageTitle" value="<?php if(isset($page)){ echo $page->title; } ?>" placeholder="Enter Page Title here" required>
							</div><!-- col-sm-6 -->
							
							<div class="col-sm-5 customize-add-inner-sec">
								<label for="metaTitle">Meta Title</label>
								<input class="form-control" type="text" name="metaTitle" id="metaTitle" value="<?php if(isset($page)){ echo $page->meta_title; } ?>" placeholder="Enter Meta Title here">
							</div><!-- col-sm-6 -->
							
							<div class="col-sm-7 customize-add-inner-sec">
								<label for="pageIdentifier">Page Identifier *</label>
								<input class="form-control" type="text" name="pageIdentifier" id="pageIdentifier" value="<?php if(isset($page)){ echo $page->identifier; } ?>" placeholder="Page Identifier" required>
							</div><!-- col-sm-6 -->
							
							<div class="col-sm-5 customize-add-inner-sec">
								<label for="metaKeyword">Meta Keyword</label>
								<input class="form-control" type="text" name="metaKeyword" id="metaKeyword" value="<?php if(isset($page)){ echo $page->meta_keywords; } ?>" placeholder="Meta Keyword">
							</div><!-- col-sm-6 -->
							
							<div class="col-sm-7 customize-add-inner-sec page-content-textarea">
								<label for="pageContent">Page Content *</label>
								<textarea class="form-control" name="pageContent" id="pageContent" required><?php if(isset($page)){ echo $page->content; } ?></textarea>
							</div><!-- col-sm-6 -->
							
							<div class="col-sm-5 customize-add-inner-sec page-content-textarea-small">
								<label for="metaDescription">Meta Description</label>
								<textarea class="form-control" name="metaDescription" id="metaDescription" placeholder="Description area"><?php if(isset($page)){ echo $page->meta_description; } ?></textarea>
								<div class="clear pad-bt-40"></div>
								<label for="status">Status *</label>
								<div class="customize-add-radio-section row">
									<?php 
									$published = 'checked';
									$hold = '';
									if(isset($page))
									{ 
										if($page->status == 2)
										{
											$published = '';
											$hold = 'checked';
										}
									}
									?>
									<div class="radio col-sm-6">

									  <label><input type="radio" name="status" value="1" id="published" <?= $published ?>>Published <span class="checkmark"></span></label>
									</div><!-- radio -->
									<div class="radio col-sm-6">
									  <label><input type="radio" name="status" value="2" id="hold" <?= $hold ?>>On-Hold <span class="checkmark"></span></label>
									</div><!-- radio -->
								</div><!-- customize-add-radio-section -->
								
							</div><!-- col-sm-6 -->

						</div><!-- row -->
					</div><!-- customize-add-section -->

				
					<div class="download-discard-small ">
						<?php if(isset($page)){ ?>
							<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
							<button type="button" class="delete-btn" data-toggle="modal" data-target="#deleteModal"><i class="fas fa-trash-alt"></i> Delete</button>
					    	<?php } ?>
						<?php } ?>
						
						<button class="white-btn" type="reset">Discard</button>
						<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
						<button class="download-btn" type="submit"><?php if(isset($page)){ echo "Update"; }else{ echo "Save"; }?></button>
					    <?php } ?>
					 </div><!-- download-discard-small  -->
            	</form>
        	</div>
        	<!--end form-->
    	</div>
    </div>
</main>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="cmsDeleteForm" method="POST" action="<?= base_url('WebshopController/deleteCMAPage')?>">
				<input type="hidden" name="cmsPageID" id="cmsPageID" value="<?php if(isset($page)){ echo $page->id; } ?>">
				<div class="modal-header">
					<h1 class="head-name">Are you sure? you want to Delete Page!</h1>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
					<button type="submit" class="btn btn-primary filter-btn gradient-btn">Delete</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php //print_r(BASE_URL.''."cms_ckeditor_file_upload.php");exit; ?>
<script type="text/javascript">
	//CKEDITOR.replace('pageContent').config.allowedContent = true;
	CKEDITOR.replace( 'pageContent', {extraPlugins :'justify',				
 		filebrowserUploadMethod: "form",
		 filebrowserUploadUrl: BASE_URL+"cms_ckeditor_file_upload.php"
 	});
</script>
<script src="<?php echo SKIN_JS; ?>webshop.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>