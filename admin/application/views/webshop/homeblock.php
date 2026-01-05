<?php $this->load->view('common/fbc-user/header'); ?>
<?php
$fbc_user_id = $this->session->userdata('LoginID');
$shop_id = $this->session->userdata('ShopID');
$shop_upload_path='shop'.$shop_id;
?>
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
    	<div id="static-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">

	      	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
	          	<h1 class="head-name pad-bt-20">&nbsp; Homeblock  </h1>

	        </div><!-- d-flex -->

	        <!-- form -->
	        <form method="POST" id="homeblockForm" action="<?= base_url('WebshopController/submitHomeblock') ?>">
	        	<input type="hidden" name="block_id" value="<?= $blockID?>">
	        	<div class="content-main form-dashboard">

	        		<?php $i=1;
	        			if(isset($banners) && count($banners) > 0){

	        			$bannersCount = count($banners);

        				foreach ($banners as $bannArr) {
        					$id = $bannArr->id;

        				 ?>

	        				<div class="customize-edit-section">
	        					<input type="hidden" name="bannerID[]" value="<?= $bannArr->id ?>">
	        					<input type="hidden" name="bannerCount[]" value="<?= $i ?>">
        						<input type="hidden" name="homeblockType[]" value="4">
							 	<h1 class="head-name pad-bt-20">Homeblock <?= $i ?>

							 	</h1>
								<div class="row">
									<div class="col-sm-7 customize-add-inner-sec page-content-textarea-small">

										<label for="bannerHeading">Homeblock Heading </label>
										<input class="form-control" type="text" name="bannerHeading[]" value="<?= $bannArr->heading?>" placeholder="Enter Banner Heading here">
										<div class="clear pad-bt-40"></div>
									<label>Homeblock Image *</label>

										<div class="custom-file upload-file">
										<input type="hidden" class="imageName" id="imageName" name="imageName[]" value="<?= $bannArr->banner_image ?>">
										<input type="file" class="custom-file-input" name="customFil[]" id="customFile_<?= $i ?>" style="" onchange="previewImages(<?php echo $i; ?>);">
										<svg style="" for="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									  		<path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z">
									  		</path>
										  	<path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"></path>
										</svg>
										<p style="">Upload media</p>
											<!-- img-block -->
										</div><!-- upload-file -->
										<!-- col-sm-6 -->

									<div class="col-sm-5 customize-add-inner-sec">


										<label for="buttonText">Homeblock Button Text </label>
										<input class="form-control" type="text" name="buttonText[]" value="<?= $bannArr->button_text ?>" placeholder="Botton text">
										<div class="clear pad-bt-40"></div>

										<label for="buttonLink">Link Button to </label>
										<input class="form-control" type="text" name="buttonLink[]" value="<?= $bannArr->link_button_to ?>" placeholder="Enter destination url">
										<div class="clear pad-bt-40"></div>

										<label for="position">Position </label>
										<input class="form-control" type="number" name="position[]" value="<?= $bannArr->position ?>" placeholder="Enter Postion">
										<div class="clear pad-bt-40"></div>



									</div><!-- col-sm-6 -->
								</div><!-- row -->
								<div class="row">
									<div class="col-md-6">
										<p class="upload-notes">Prefered Size: Width 540px  X  Height 290px<br/>
										Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.</p>

									<div class="uploadPreview" id="uploadPreview_<?php echo $i; ?>">
											<img src="<?= base_url().'../uploads/banners/'.$bannArr->banner_image ?>" width="200">
										</div>
									</div>
									<div class="col-md-6">
									<?php
									//$shopData = $this->CommonModel->getShopData($_SESSION['ShopOwnerId'],$_SESSION['ShopID']);
									//if(isset($shopData) && $shopData->multi_lang_flag == 1) { ?>
									<!-- <div class="col-md-12 language-tranlations">
									   <h6>Language Translations</h6>
									   <table class="table table-bordered table-style flag-table">
									      <thead>
									         <tr>
									            <th><?php foreach ($languagesListing as $key => $value) {?>
									               <img title="<?php echo $value['name']; ?>" src="<?php echo SKIN_IMG.$value['code'].'-flag.png' ?>">
									               <?php } ?>
									            </th>
									         </tr>
									      </thead>
									      <tbody>
									         <tr>
									            <td>
									               <?php

									                foreach ($languagesListing as $key => $value){

							                          $code = $value['code'];
							                          $count = $this->WebshopModel->countHomeBlock($id, $code);
							                          if ($count > '0') {
							                           ?>
										               <a title="<?php echo $value['name']; ?>" class="edit-cat fa fa-edit"  onclick="OpenHomeBlock(<?php echo $id ?>,'<?php echo $value['code']; ?>');"></a>
										               <?php } else { ?>
										               <a title="<?php echo $value['name']; ?>" class="edit-cat fa fa-plus"  onclick="OpenHomeBlock(<?php echo $id ?>,'<?php echo $value['code']; ?>');"></a>
										               <?php }} ?>
									            </td>
									         </tr>
									      </tbody>
									   </table>
									</div> -->
									<?php //} ?>
									</div>
								</div>
							</div><!-- customize-edit -->

	        		<?php  $i++; } ?>
	        	<?php  } else{  ?>
	        		<?php for($i=1;$i<=2;$i++) {	?>
	        			<div class="customize-edit-section">
	        					<input type="hidden" name="bannerID[]" value="">
	        					<input type="hidden" name="bannerCount[]" value="<?= $i ?>">
	        					<input type="hidden" name="homeblockType[]" value="4">
							 	<h1 class="head-name pad-bt-20">Homeblock <?= $i ?>

							 	</h1>
								<div class="row">
									<div class="col-sm-7 customize-add-inner-sec page-content-textarea-small">

										<label for="bannerHeading">Homeblock Heading </label>
										<input class="form-control" type="text" name="bannerHeading[]" value="" placeholder="Enter Banner Heading here">
										<div class="clear pad-bt-40"></div>
									<label>Homeblock Image *</label>

										<div class="custom-file upload-file">
										<input type="hidden" class="imageName" id="imageName" name="imageName[]" value="">
										<input type="file" class="custom-file-input" name="customFil[]" id="customFile_<?= $i ?>" style="" onchange="previewImages(<?php echo $i; ?>);">
										<svg style="" for="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									  		<path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z">
									  		</path>
										  	<path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"></path>
										</svg>
										<p style="">Upload media</p>
											<!-- img-block -->
										</div><!-- upload-file -->
										<p class="upload-notes">Prefered Size: Width 540px  X  Height 290px<br/>
										Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.</p>

								<div class="uploadPreview" id="uploadPreview_<?php echo $i; ?>">
									<img src="<?php echo base_url().'../uploads/banners/'.$bannArr->mobile_banner_image ?>" width="200">
										</div>
									</div><!-- col-sm-6 -->

									<div class="col-sm-5 customize-add-inner-sec">


										<label for="buttonText">Homeblock Button Text </label>
										<input class="form-control" type="text" name="buttonText[]" value="" placeholder="Botton text">
										<div class="clear pad-bt-40"></div>

										<label for="buttonLink">Link Button to </label>
										<input class="form-control" type="text" name="buttonLink[]" value="" placeholder="Enter destination url">
										<div class="clear pad-bt-40"></div>

										<label for="position">Position </label>
										<input class="form-control" type="number" name="position[]" value="" placeholder="Enter Postion">
										<div class="clear pad-bt-40"></div>



									</div><!-- col-sm-6 -->
								</div><!-- row -->
							</div><!-- customize-edit -->

	       		<?php  } ?>
    		<?php  } ?>

    				<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
					<div class="download-discard-small ">
						<!-- <button class="white-btn" type="button">Discard</button> -->
						<button class="download-btn" id="save_homeblock" type="submit">Save</button>
					 </div><!-- download-discard-small  -->
					<?php } ?>
		        </div>
	        </form>

	        <!--end form-->
    	</div>
  	</div>
</main>
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="blockDeleteForm" method="POST" action="<?= base_url('WebshopController/deleteBanner')?>">
				<input type="hidden" name="blockID" id="blockID">
				<div class="modal-header">
					<h1 class="head-name">Are you sure? you want to Delete Homeblock!</h1>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
					<button type="submit" class="purple-btn">Delete</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?php echo SKIN_JS; ?>webshop.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
