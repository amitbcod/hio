<?php $this->load->view('common/fbc-user/header'); ?>
<?php
   $fbc_user_id = $this->session->userdata('LoginID');
 //  $shop_id = $this->session->userdata('ShopID');
 //  $shop_upload_path='shop'.$shop_id;
   ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
   <ul class="nav nav-pills">
      <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li>
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
            <h1 class="head-name pad-bt-20">&nbsp; Banner - Edit </h1>
            <div class="float-right">
               <button class="white-btn" id="addNewBanner"> +  Add New</button>
            </div>
         </div>
         <!-- d-flex -->
         <!-- form -->
         <form method="POST" id="bannerForm" action="<?= base_url('WebshopController/submitBanners') ?>">
            <input type="hidden" name="block_id" value="<?= $blockID?>">
            <div class="content-main form-dashboard">
               <input type="hidden" id="banner_count" name="banner_count" value="<?php
                  (isset($banners) && count($banners) > 0) ? print_r(count($banners)) : 1 ?>">
               <?php
                  $i = 1;
                  if(isset($banners) && count($banners) > 0){

                  	$bannersCount = count($banners);

                  	foreach ($banners as $bannArr) {
						$customer_type_ids = $bannArr->customer_type_ids;
						$customer_type_ids_arr=array();
						if(isset($bannArr->customer_type_ids) && $bannArr->customer_type_ids!=''){
							if( strpos($customer_type_ids, ',') !== false ) {
								$customer_type_ids_arr=explode(',',$customer_type_ids);

							}else{
								$customer_type_ids_arr[]=$customer_type_ids;
							}
						}else{
							$customer_type_ids_arr=array();
						}
                  		$id2 = $bannArr->category_ids;
                  		$id = $bannArr->id;
                  	   //$category_ids= explode(",",$bannArr->category_ids);
                  		$category_ids = explode(',', $bannArr->category_ids ?? '');
                  	 ?>
               <div class="customize-edit-section">
                  <input type="hidden" name="bannerID[]" value="<?= $bannArr->id ?>">
                  <input type="hidden" name="bannerCount[]" value="<?= $i ?>">
                  <h1 class="head-name pad-bt-20">Banner <?= $i ?>
                     <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
                     <button type="button" class="delete-btn float-right deleteBlock" data-toggle="modal" data-target="#deleteModal" data-id="<?= $bannArr->id ?>"><i class="fas fa-trash-alt"></i> Delete</button>
                     <?php } ?>
                  </h1>
                  <div class="row">
                     <div class="col-sm-7 customize-add-inner-sec page-content-textarea-small">
                        <label for="bannerHeading">Banner Heading </label>
                        <input class="form-control" type="text" name="bannerHeading[]" value="<?= $bannArr->heading?>" placeholder="Enter Banner Heading here">
                        <div class="clear pad-bt-40"></div>
                        <?php if(isset($static_blocks_info) && $static_blocks_info !='' && ($static_blocks_info->identifier =="categorybanner" || $static_blocks_info->identifier =="homecatblock1" || $static_blocks_info->identifier =="homecatblock2" ) )
                           {	?>
                        <label for="select_category">Select Category </label>
                        <select class="form-control" name="m_category<?= $i ?>[]" id="m_category<?= $i ?>" <?php if($static_blocks_info->identifier =="categorybanner"){ echo "multiple"; } ?> >
                           <?php foreach($browse_category as $main_cat) { ?>
                           <option value="<?= $main_cat['id']; ?>" <?php foreach ($category_ids as  $value) {
                              if($value== $main_cat['id'])
                              {
                              	echo "selected";
                              }
                              } ?> > <?= $main_cat['cat_name']; ?></option>
                           <?php if(isset($main_cat['cat_level_1'])  && $main_cat['cat_level_1'] !='')
                              { ?>
                           <?php foreach($main_cat['cat_level_1'] as $cat_level1) { ?>
                           <option value="<?= $cat_level1['id']; ?>"
                              <?php foreach ($category_ids as  $value) {
                                 if($value== $cat_level1['id'])
                                 {
                                 	echo "selected";
                                 }
                                 } ?> ><?php echo '- '.$cat_level1['cat_name']; ?></option>
                           <?php if(isset($cat_level1['cat_level_2']) && $main_cat['cat_level_2'] !='') { ?>
                           <?php foreach($cat_level1['cat_level_2'] as $cat_level2) { ?>
                           <option value="<?= $cat_level2['id']; ?>"
                              <?php foreach ($category_ids as  $value) {
                                 if($value== $cat_level2['id'])
                                 {
                                 	echo "selected";
                                 }
                                 } ?>
                              ><?php echo '-- '.$cat_level2['cat_name']; ?></option>
                           <?php } } ?>
                           <?php } ?>
                           <?php } ?>
                           <?php } ?>
                        </select>
                        <div class="clear pad-bt-40"></div>
                        <?php	}   ?>
                        <label for="bannerDescription">Banner Description </label>
                        <textarea class="form-control" name="bannerDescription[]" id="bannerDescription<?= $i ?>" placeholder="Description Area"><?= $bannArr->description ?></textarea>
                        <div class="clear pad-bt-40"></div>


						<label for="position">Start Date </label>
						<input type="date" class="start_date form-control" id="start_date" name="start_date[]"
						 value="<?php echo (isset($bannArr->start_date) && $bannArr->start_date > 0)?$bannArr->start_date : '' ?>"
						placeholder="Enter start date">
                        <div class="clear pad-bt-40"></div>

						<label for="position">End Date </label>
						<input type="date" class="end_date form-control" id="end_date" name="end_date[]"
						value="<?php echo (isset($bannArr->end_date) && $bannArr->end_date > 0)? $bannArr->end_date : '' ?>" placeholder="Enter start date">
                        <div class="clear pad-bt-40"></div>
                        <div class="uploadPreview" id="uploadPreview_<?php echo $i; ?>">
                           <img src="<?php echo base_url().'../uploads/banners/'.$bannArr->banner_image ?>" width="200">
                        </div>

                        <?php if(isset($static_blocks_info) && $static_blocks_info !='' && $static_blocks_info->identifier =="homebannerzumbaweartheme" ){ ?>
                        <div class="clear pad-bt-40"></div>
                        <div class="uploadPreviewMobile" id="uploadPreviewMobile_<?php echo $i; ?>">
                           <img src="<?php echo base_url().'../uploads/banners/mobile/'.$bannArr->mobile_banner_image ?>" width="200">
                        </div>
                        <?php } ?>
                     </div>
                     <!-- col-sm-6 -->
                     <div class="col-sm-5 customize-add-inner-sec">
                        <label for="bannerType">Type</label>
                        <select class="form-control type" id="type" name="bannerType[]" >
                           <option
                              <?php
                                 if(isset($static_blocks_info) && $static_blocks_info !='' && $static_blocks_info->identifier =="homebanner")
                                 {
                                 	echo "selected";
                                 } ?>
                              value="1">Home</option>
                           <option
                              <?php
                                 if(isset($static_blocks_info) && $static_blocks_info !='' && ($static_blocks_info->identifier =="categorybanner" || $static_blocks_info->identifier =="homecatblock1" || $static_blocks_info->identifier =="homecatblock2") )
                                 {
                                 	echo "selected";
                                 } ?>
                              value="2">Category</option>
                           <option value="3">Others</option>
                        </select>
                        <div class="clear pad-bt-40"></div>
                        <label for="buttonText">Banner Button Text </label>
                        <input class="form-control" type="text" name="buttonText[]" value="<?= $bannArr->button_text ?>" placeholder="Botton text">
                        <div class="clear pad-bt-40"></div>
                        <label for="buttonLink">Link Button to </label>
                        <input class="form-control" type="text" name="buttonLink[]" value="<?= $bannArr->link_button_to ?>" placeholder="Enter destination url">
                        <div class="clear pad-bt-40"></div>
                        <label for="position">Position </label>
                        <input class="form-control" type="number" name="position[]" value="<?= $bannArr->position ?>" placeholder="Enter Postion">
                        <div class="clear pad-bt-40"></div>

                        <label for="position">Status </label>
                        <select class="form-control" name="status[]" id="disc_status" >
                           <option value="1" <?php echo (isset($bannArr) && $bannArr->status == 1)?'selected':''?> >Active</option>
                           <option value="0" <?php echo (isset($bannArr) && $bannArr->status == 0)?'selected':''?> >Inactive</option>
                        </select>
                        <div class="clear pad-bt-40"></div>



						<label for="position">Apply </label>

						<select name="customer_type_ids[]" id="customer_type_ids" multiple class="type_ds form-control">
							<option value="0" <?php echo (in_array(0,$customer_type_ids_arr))?'selected':''; ?>>All Customer types</option>

							<?php if(isset($CustomerTypeMaster) && count($CustomerTypeMaster)>0){
							foreach($CustomerTypeMaster as $valueOne){

								if(in_array($valueOne->id,$customer_type_ids_arr)){
									$selected='selected';
								}else{
									$selected='';
								}
							?>
							<option value="<?php echo $valueOne->id; ?>" <?php echo $selected ?>><?php echo $valueOne->name; ?></option>

							<?php } } ?>
						</select>
                        <div class="clear pad-bt-40"></div>

                        <label>Banner Image *</label>
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
                        </div>
                        <!-- upload-file -->
                        <?php if(isset($static_blocks_info) && $static_blocks_info !='' && ( $static_blocks_info->identifier =="homebanner" || $static_blocks_info->identifier =="homebannerzumbaweartheme" )) { ?>
                        <p class="upload-notes">Prefered Size: Width 1400px  X  Height 500px OR Width 1366px  X  Height 400px<br/>
                           Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.
                        </p>
                        <?php  if($static_blocks_info->identifier =="homebannerzumbaweartheme"){ ?>
                        <div class="clear pad-bt-40"></div>
                        <label>Mobile Banner Image *</label>
                        <div class="custom-file upload-file">
                           <input type="hidden" class="imageNameMobile" id="imageNameMobile" name="imageNameMobile[]" value="<?= $bannArr->mobile_banner_image ?>">
                           <input type="file" class="custom-file-input" name="customFilMobile[]" id="customFileMobile_<?= $i ?>" style="" onchange="previewImagesMobile(<?php echo $i; ?>);">
                           <svg style="" for="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z">
                              </path>
                              <path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"></path>
                           </svg>
                           <p style="">Upload media</p>
                           <!-- img-block -->
                        </div>
                        <!-- upload-file -->
                        <p class="upload-notes">Prefered Size: Width 480px  X  Height 560px<br/>
                           Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.
                        </p>
                        <?php  } ?>
                        <?php } else if(isset($static_blocks_info) && $static_blocks_info !='' && $static_blocks_info->identifier =="categorybanner") { ?>
                        <p class="upload-notes">Prefered Size: Width 825px  X Height 160px<br/>
                           Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.
                        </p>
                        <?php }else if(isset($static_blocks_info) && $static_blocks_info !='' && ($static_blocks_info->identifier =="homecatblock1" || $static_blocks_info->identifier =="homecatblock2" )) { ?>
                        <p class="upload-notes">Prefered Size: Width 225px  X Height 225px<br/>
                           Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.
                        </p>
                        <?php } else {} ?>
                     </div>
                     <!-- col-sm-6 -->
                  </div>
                  <!-- row -->

               </div>
               <!-- customize-edit -->
               <script type="text/javascript">
                  // CKEDITOR.replace('bannerDescription'+<?php //echo $i; ?>).config.allowedContent = true;
               </script>
               <?php $i++; }
                  }else{

                  	$bannersCount = 1;
                  	?>
               <div class="customize-edit-section">
                  <input type="hidden" name="bannerCount[]" value="<?= $bannersCount ?>">
                  <h1 class="head-name pad-bt-20">Banner 1 </h1>
                  <div class="row">
                     <div class="col-sm-7 customize-add-inner-sec page-content-textarea-small">
                        <label for="bannerHeading">Banner Heading </label>
                        <input class="form-control" type="text" name="bannerHeading[]" value="" placeholder="Enter Banner Heading here">
                        <div class="clear pad-bt-40"></div>
                        <?php if(isset($static_blocks_info) && $static_blocks_info !='' && ($static_blocks_info->identifier =="categorybanner" || $static_blocks_info->identifier =="homecatblock1" || $static_blocks_info->identifier =="homecatblock2" ) )	{	?>
                        <label for="select_category">Select Category </label>
                        <select class="form-control" name="m_category<?php echo $bannersCount; ?>[]" id="m_category<?php echo $bannersCount; ?>" <?php if($static_blocks_info->identifier =="categorybanner"){ echo "multiple"; } ?> >
                           <?php foreach ($browse_category as  $main_cat) { ?>
                           <option value="<?= $main_cat['id']; ?>"><?= $main_cat['cat_name']; ?></option>
                           <?php if(isset($main_cat['cat_level_1'])  && $main_cat['cat_level_1'] !='')	{ ?>
                           <?php foreach($main_cat['cat_level_1'] as $cat_level1) { ?>
                           <option value="<?= $cat_level1['id']; ?>" ><?php echo '- '.$cat_level1['cat_name']; ?></option>
                           <?php if(isset($cat_level1['cat_level_2']) && $cat_level1['cat_level_2'] !='') { ?>
                           <?php foreach($cat_level1['cat_level_2'] as $cat_level2) { ?>
                           <option value="<?= $cat_level2['id']; ?>"><?php echo '-- '.$cat_level2['cat_name']; ?></option>
                           <?php }  ?>
                           <?php } ?>
                           <?php } ?>
                           <?php } ?>
                           <?php } ?>
                        </select>
                        <div class="clear pad-bt-40"></div>
                        <?php	}   ?>
                        <label for="bannerDescription">Banner Description </label>
                        <textarea class="form-control" name="bannerDescription[]" id="bannerDescription" placeholder="Description Area"></textarea>
                        <div class="clear pad-bt-40"></div>

						<label for="position">Start Date </label>
						<input type="date" class="start_date form-control" id="start_date" name="start_date[]"
						 value=""
						placeholder="Enter start date">
                        <div class="clear pad-bt-40"></div>

						<label for="position">End Date </label>
						<input type="date" class="end_date form-control" id="end_date" name="end_date[]"
						value="" placeholder="Enter start date">
                        <div class="clear pad-bt-40"></div>


                        <div class="uploadPreview" id="uploadPreview_<?php echo $bannersCount; ?>">
                        </div>
                        <?php if(isset($static_blocks_info) && $static_blocks_info !='' && $static_blocks_info->identifier =="homebannerzumbaweartheme" ){ ?>
                        <div class="clear pad-bt-40"></div>
                        <div class="uploadPreviewMobile" id="uploadPreviewMobile_<?php echo $bannersCount; ?>"></div>
                        <?php } ?>
                     </div>
                     <!-- col-sm-6 -->
                     <div class="col-sm-5 customize-add-inner-sec">
                        <label for="bannerType">Type</label>
                        <select class="form-control type" name="bannerType[]" id="bannerType">
                           <option value="1">Home</option>
                           <option value="2">Category</option>
                           <option value="3">Others</option>
                        </select>
                        <div class="clear pad-bt-40"></div>
                        <label for="buttonText">Banner Button Text </label>
                        <input class="form-control" type="text" name="buttonText[]" value="" placeholder="Botton text">
                        <div class="clear pad-bt-40"></div>
                        <label for="buttonLink">Link Button to </label>
                        <input class="form-control" type="text" name="buttonLink[]" value="" placeholder="Enter destination url">
                        <div class="clear pad-bt-40"></div>

                        <label for="position">Position </label>
                        <input class="form-control" type="number" name="position[]" placeholder="Enter Postion">
                        <div class="clear pad-bt-40"></div>

						<label for="position">Status </label>
                        <select name="status[]" id="status[]" class="form-control">
							<option value="1">Active</option>
							<option value="0">Inactive</option>
						</select>
                        <div class="clear pad-bt-40"></div>

						<label for="position">Apply </label>
						<select name="customer_type_ids[]" id="customer_type_ids" multiple class="type_ds form-control">
							<option selected value="0">All Customer types</option>

							<?php if(isset($CustomerTypeMaster) && count($CustomerTypeMaster)>0){
							foreach($CustomerTypeMaster as $valueOne){
							?>
							<option value="<?php echo $valueOne->id; ?>"><?php echo $valueOne->name; ?></option>

							<?php } } ?>
						</select>
                        <div class="clear pad-bt-40"></div>


                        <label>Banner Image *</label>
                        <div class="custom-file upload-file">
                           <input type="hidden" name="imageName[]">
                           <input type="file" class="custom-file-input" name="customFil[]" id="customFile_<?php echo $bannersCount; ?>" style="" onchange="previewImages(<?php echo $bannersCount; ?>);" required>
                           <svg style="" for="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"></path>
                              <path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"></path>
                           </svg>
                           <p style="">Upload media</p>
                           <!-- img-block -->
                        </div>
                        <!-- upload-file -->
                        <?php if(isset($static_blocks_info) && $static_blocks_info !='' && ( $static_blocks_info->identifier =="homebanner" || $static_blocks_info->identifier =="homebannerzumbaweartheme" ) ) { ?>
                        <p class="upload-notes">Prefered Size: Width 1400px  X  Height 500px OR Width 1366px  X  Height 400px<br/>
                           Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.
                        </p>
                        <?php  if($static_blocks_info->identifier =="homebannerzumbaweartheme"){ ?>
                        <div class="clear pad-bt-40"></div>
                        <label>Mobile Banner Image*</label>
                        <div class="custom-file upload-file">
                           <input type="hidden" name="imageNameMobile[]">
                           <input type="file" class="custom-file-input" name="customFilMobile[]" id="customFileMobile_<?php echo $bannersCount; ?>" style="" onchange="previewImagesMobile(<?php echo $bannersCount; ?>);" required>
                           <svg style="" for="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"></path>
                              <path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"></path>
                           </svg>
                           <p style="">Upload media</p>
                           <!-- img-block -->
                        </div>
                        <!-- upload-file -->
                        <p class="upload-notes">Prefered Size: Width 480px  X  Height 560px<br/>
                           Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.
                        </p>
                        <?php } ?>
                        <?php } else if(isset($static_blocks_info) && $static_blocks_info !='' && $static_blocks_info->identifier =="categorybanner") { ?>
                        <p class="upload-notes">Prefered Size: Width 825px  X Height 160px<br/>
                           Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.
                        </p>
                        <?php }else if(isset($static_blocks_info) && $static_blocks_info !='' && ($static_blocks_info->identifier =="homecatblock1" || $static_blocks_info->identifier =="homecatblock2" )) { ?>
                        <p class="upload-notes">Prefered Size: Width 225px X Height 225px<br/>
                           Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.
                        </p>
                        <?php } else {} ?>
                     </div>
                     <!-- col-sm-6 -->
                  </div>
                  <!-- row -->
               </div>
               <!-- customize-edit -->
               <script type="text/javascript">
                  // CKEDITOR.replace('bannerDescription').config.allowedContent = true;
               </script>
               <?php } ?>
               <div id="bannerAddDive">
               </div>
               <div class="download-discard-small ">
                  <button class="white-btn" type="button">Discard</button>
                  <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
                  <button class="download-btn" id="save_banners" type="submit">Save</button>
                  <?php } ?>
               </div>
               <!-- download-discard-small  -->
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
               <h1 class="head-name">Are you sure? you want to Delete Banner!</h1>
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
<script>

</script>
<script type="text/javascript">
   var bCont = <?php echo $bannersCount; ?>;
   var i = bCont+1;
   // console.log(i);
   var selected = "selected";
      $("#addNewBanner").on("click", () => {
          let appendDiv =
              '<div class="customize-edit-section appendBanner">'+
              '<input type="hidden" name="bannerCount[]" value="'+i+'">'+
                  '<h1 class="head-name pad-bt-20">Banner '+i+'</h1>'+
                  '<div class="row">'+
                      '<div class="col-sm-7 customize-add-inner-sec page-content-textarea-small">'+

                          '<label>Banner Heading </label>'+
                          '<input class="form-control" type="text" name="bannerHeading[]" id="bannerHeading" value="" placeholder="Enter Banner Heading here">'+
                          '<div class="clear pad-bt-40"></div>'+

           <?php if(isset($static_blocks_info) && $static_blocks_info !='' && ($static_blocks_info->identifier =="categorybanner" || $static_blocks_info->identifier =="homecatblock1" || $static_blocks_info->identifier =="homecatblock2" ) )	{	?>

   				'<label for="select_category">Select Category </label>'+

   				'<select class="form-control" name="m_category'+i+'[]" id="m_category'+i+'" <?php if($static_blocks_info->identifier =="categorybanner"){ echo "multiple"; } ?> >'+


   				<?php foreach ($browse_category as  $main_cat) { ?>
   					'<option value="<?= $main_cat['id']; ?>"><?= $main_cat['cat_name']; ?></option>'+
   					<?php if(isset($main_cat['cat_level_1'])  && $main_cat['cat_level_1'] !='')	{ ?>
   		                <?php foreach($main_cat['cat_level_1'] as $cat_level1) { ?>
   							'<option value="<?= $cat_level1['id']; ?>" ><?php echo '- '.$cat_level1['cat_name']; ?></option>'+
   		                        <?php if(isset($cat_level1['cat_level_2']) && $cat_level1['cat_level_2'] !='') { ?>
   		                            <?php foreach($cat_level1['cat_level_2'] as $cat_level2) { ?>
   		                             '<option value="<?= $cat_level2['id']; ?>"><?php echo '-- '.$cat_level2['cat_name']; ?></option>'+
   		                            <?php }  ?>
   		                        <?php } ?>
   						<?php } ?>
   					<?php } ?>
   				<?php } ?>

   				'</select>'+
   				'<div class="clear pad-bt-40"></div>'+



   	<?php	}   ?>


                          '<label>Banner Description </label>'+
                          '<textarea class="form-control" name="bannerDescription[]" id="bannerDescription'+i+'" placeholder="Description Area"></textarea>'+
                          '<div class="clear pad-bt-40"></div>'+

						  '<label for="position">Start Date </label>'+
							'<input type="date" class="start_date form-control"  name="start_date[]"'+
							'value=""'+
							'placeholder="Enter start date">'+
							'<div class="clear pad-bt-40"></div>'+

							'<label for="position">End Date </label>'+
							'<input type="date" class="end_date form-control"  name="end_date[]"'+
							'value="" placeholder="Enter start date">'+
							'<div class="clear pad-bt-40"></div>'+



                          '<div class="uploadPreview" id="uploadPreview_'+i+'"></div>'+
   					<?php if(isset($static_blocks_info) && $static_blocks_info !='' && $static_blocks_info->identifier =="homebannerzumbaweartheme" ){ ?>
   						'<div class="clear pad-bt-40"></div>'+
   						'<div class="uploadPreviewMobile" id="uploadPreviewMobile_'+i+'"></div>'+
   					<?php } ?>

                      '</div>'+

                      '<div class="col-sm-5 customize-add-inner-sec">'+
                          '<label for="bannerType">Type</label>'+
                          '<select class="form-control type" name="bannerType[]">'+

                              <?php if(isset($static_blocks_info) && $static_blocks_info !='' && ( $static_blocks_info->identifier =="homebanner" || $static_blocks_info->identifier =="homebannerzumbaweartheme" ))
      { ?>
   						'<option value="1">Home</option>'+
   						<?php } ?>
                               <?php
      if(isset($static_blocks_info) && $static_blocks_info !='' && ($static_blocks_info->identifier =="categorybanner" || $static_blocks_info->identifier =="homecatblock1" || $static_blocks_info->identifier =="homecatblock2" ) )
      { ?>
   						'<option value="2">Category</option>'+
   						<?php }if(isset($static_blocks_info) && $static_blocks_info !='' && ($static_blocks_info->identifier !="categorybanner" || $static_blocks_info->identifier !="homecatblock1" || $static_blocks_info->identifier !="homecatblock2") && $static_blocks_info->identifier !="homebanner" && $static_blocks_info->identifier !="homebannerzumbaweartheme") { ?>
   							'<option value="2">Home</option>'+
   							'<option value="2">Category</option>'+
   							'<option value="3">Others</option>'+
   						<?php } ?>

                          '</select>'+
                          '<div class="clear pad-bt-40"></div>'+

                          '<label>Banner Button Text </label>'+
                          '<input class="form-control" type="text" name="buttonText[]" id="buttonText" value="" placeholder="Botton text">'+
                          '<div class="clear pad-bt-40"></div>'+

                          '<label>Link Button to </label>'+
                          '<input class="form-control" type="text" name="buttonLink[]" id="buttonLink" value="" placeholder="Enter destination url">'+
                          '<div class="clear pad-bt-40"></div>'+

                          '<label for="position">Position </label>'+
   											'<input class="form-control" type="number" name="position[]" placeholder="Enter Postion">'+
   											'<div class="clear pad-bt-40"></div>'+

						'<label for="position">Status </label>'+
                        '<select name="status[]" class="form-control">'+
							'<option value="1">Active</option>'+
							'<option value="0">Inactive</option>'+
						'</select>'+
                        '<div class="clear pad-bt-40"></div>'+

						'<label for="position">Apply </label>'+
						'<select name="customer_type_ids[]" id="customer_type_ids" multiple class="type_ds form-control">'+
							'<option selected value="0">All Customer types</option>'+

							<?php if(isset($CustomerTypeMaster) && count($CustomerTypeMaster)>0){
							foreach($CustomerTypeMaster as $valueOne){
							?>
							'<option value="<?php echo $valueOne->id; ?>"><?php echo $valueOne->name; ?></option>'+

							<?php } } ?>
						'</select>'+
                        '<div class="clear pad-bt-40"></div>'+




                          '<label>Banner Image *</label>'+
                          '<div class="custom-file upload-file">'+
                          '<input type="hidden" name="imageName[]">'+
                              '<input type="file" class="custom-file-input" name="customFil[]" id="customFile_'+i+'" onchange="previewImages('+i+');" style="" required>'+
                              '<svg style="" for="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">'+
                                  '<path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"></path>'+
                                  '<path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"></path>'+
                              '</svg>'+
                              '<p style="">Upload media</p>'+
                          '</div>'+
   					<?php if(isset($static_blocks_info) && $static_blocks_info !='' && ( $static_blocks_info->identifier =="homebanner" || $static_blocks_info->identifier =="homebannerzumbaweartheme" )) { ?>
   					'<p class="upload-notes">Prefered Size: Width 1400px  X  Height 500px OR Width 1366px  X  Height 400px<br/>Please optimize the Images before uploading so that it does not look heavy. Try to have it below 200Kbs.</p>'+
   					<?php  if($static_blocks_info->identifier =="homebannerzumbaweartheme"){ ?>
   						'<div class="clear pad-bt-40"></div>'+

   						'<label>Mobile Banner Image *</label>'+
   						'<div class="custom-file upload-file">'+
   						'<input type="hidden" name="imageNameMobile[]">'+
   							'<input type="file" class="custom-file-input" name="customFilMobile[]" id="customFileMobile_'+i+'" onchange="previewImagesMobile('+i+');" style="" required>'+
   							'<svg style="" for="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">'+
   								'<path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"></path>'+
   								'<path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"></path>'+
   							'</svg>'+
   							'<p style="">Upload media</p>'+
   						'</div>'+
   						'<p class="upload-notes">Prefered Size: Width 480px  X  Height 560px<br/>Please optimize the Images before uploading so that it doesn`t look heavy. Try to have it below 200Kbs.</p>'+
   					<?php } ?>
   					<?php } else if(isset($static_blocks_info) && $static_blocks_info !='' && $static_blocks_info->identifier =="categorybanner") { ?>
   					'<p class="upload-notes">Prefered Size: Width 825px  X Height 160px<br/>Please optimize the Images before uploading so that it does not look heavy. Try to have it below 200Kbs.</p>'+
   					<?php } else if(isset($static_blocks_info) && $static_blocks_info !='' && ($static_blocks_info->identifier =="homecatblock1" || $static_blocks_info->identifier =="homecatblock2")) { ?>
   					'<p class="upload-notes">Prefered Size: Width 225px X Height 225px<br/>Please optimize the Images before uploading so that it does not look heavy. Try to have it below 200Kbs.</p>'+
   					<?php } else {} ?>
                      '</div>'+
                  '</div>'+
              '</div>';
          $("#bannerAddDive").append(appendDiv);
          // console.log(i);
          jQuery('#m_category'+i).multiselect({
   			placeholder: 'Select Category',
   			search: true
   		});
          console.log('bannerDescription'+i);
          // CKEDITOR.replace('bannerDescription'+i).config.allowedContent = true;
         CKEDITOR.replace('bannerDescription'+i,
                      {
                          on:
                         {
                             'instanceReady': function(evt) {
                                 evt.editor.document.on('keyup', function() {
                                     document.getElementById('bannerDescription'+i).value = evt.editor.getData();
                                      console.log(document.getElementById('bannerDescription'+i).value );
                                 });

                                evt.editor.document.on('paste', function() {
                                    document.getElementById('bannerDescription'+i).value = evt.editor.getData();
                                 });
                             }
                         }
                      });
          i++;
      });
</script>
<script type="text/javascript">
   function previewImagesMobile(id)
   {
   	var file = $("#customFileMobile_"+id).val();
   	$("#customFileMobile_"+id).siblings("input[type='hidden']").val(file);
   	var total_file=document.getElementById("customFileMobile_"+id).files.length;
   	for(var i=0;i<total_file;i++)
   	{
   		var uniqid=Date.now()
   		$('#uploadPreviewMobile_'+id).html('<img src="' + URL.createObjectURL(event.target.files[i]) + '" width="200">');
   	}
   }
   // var count= $('#banner_count').val();
   // for(i=1; i<=count ; i++)
   // {
   	jQuery('[id*=m_category]').multiselect({
   		placeholder: 'Select Category',
   		search: true
   	});
   // }

   // $("#bannerDescription").change(function(){
   // 	console.log('hii');
   //   $("#bannerDescription").val();
   // });

</script>
<script type="text/javascript">
   var  val= "<?php echo $static_blocks_info->identifier ;	 ?>";
   // console.log(val);
   //changed for the category selection issue
   if(val=='categorybanner' || val=='homecatblock1' || val=='homecatblock2'){
   $(".type option[value*='1']").remove();
   $(".type option[value*='3']").remove();
   }


//    if(val=='homebanner' || val=='homebannerzumbaweartheme' ){
//    $("select option[value*='2']").remove();
//    $("select option[value*='3']").remove();
//    }

if(val=='homebanner' || val=='homebannerzumbaweartheme' ){
	$(".type option[value='2']").remove();
	$(".type option[value='3']").remove();
}

</script>
<script src="<?php echo SKIN_JS; ?>webshop.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
