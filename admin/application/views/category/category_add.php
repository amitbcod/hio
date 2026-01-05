<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

   <ul class="nav nav-pills">

      <li><a href="<?= base_url('category') ?>">Categories</a></li>

      <li class="active"><a>Add New</a></li>

   </ul> 

   <form id="categoryForm" action="<?php echo base_url('CategoryController/submitCategory'); ?>" method="post" enctype="multipart/form-data">

   <div class="main-inner min-height-480">

      <div class="customize-edit-section">

         <input type="hidden" name="bannerCount[]" value="">

         <h1 class="head-name pad-bt-20">Add Category</h1>

         <div class="row">

            <div class="col-sm-7 customize-add-inner-sec page-content-textarea-small">

               <label for="bannerHeading">Category Name <span class="required">*</span></label>

               <input class="form-control" type="text" name="categoryName" id="categoryName" value="<?php if(isset($categoryData)){ echo $categoryData->cat_name; } ?>" placeholder="Enter Category Name here" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength ='50'>

               <label for="langTitle">Category Name (French)</label>

					<input class="form-control" type="text" name="langTitle" id="langTitle" value="<?php if(isset($categoryData)){ echo $categoryData->lang_title; } ?>" placeholder="Enter French Category Name here">

               <div class="clear pad-bt-40"></div>

               <label for="bannerDescription"> Category Description</label>

               <textarea class="form-control" name="categoryDesc" id="categoryDesc" placeholder="Description Area" maxlength ='250'><?php if(isset($categoryData)){ echo $categoryData->cat_description; } ?></textarea>

               <label for="meta_title"> Meta Title</label>

               <input class="form-control" name="meta_title" id="meta_title" value="<?php if(isset($categoryData)){ echo $categoryData->meta_title; } ?>">



               <label for="meta_keyword"> Meta Keyword</label>

               <input class="form-control" name="meta_keyword" id="meta_keyword" value="<?php if(isset($categoryData)){ echo $categoryData->meta_keyword; } ?>">

               <label for="meta_description"> Meta Description</label>

               <textarea class="form-control" name="meta_description" id="meta_description" ><?php if(isset($categoryData)){ echo $categoryData->meta_description; } ?></textarea>



               <div class="clear pad-bt-40"></div>

               

               <div class="uploadPreview" id="uploadPreview">

                     <img src="<?php echo (isset($categoryData->cat_image) && !empty($categoryData->cat_image)) ? IMAGE_URL_SHOW.'/categories/'.$categoryData->cat_image : ''; ?>" width="200">

                </div>

            </div>

            <!-- col-sm-6 -->

            <div class="col-sm-5 customize-add-inner-sec">

               <label for="bannerType">Category Type <span class="required">*</span></label>

               <?php $disabled ='';if(isset($categoryData) && !empty($categoryData)){$disabled='disabled';} ?>

               <select name="category_type" id="category_type" class="form-control" <?php echo $disabled; ?>>

                  <option value="" disabled>Select Categories Type</option>

                  <option value="0">Parent</option>

                  <?php foreach($browse_category as $main_cat) { ?>

                  <option value="<?= $main_cat['id']; ?>" <?= isset($categoryData) && $categoryData->id == $main_cat['id'] ? 'Selected' : '' ?> > <?= $main_cat['cat_name']; ?></option>

                  <?php if(isset($main_cat['cat_level_1'])) { ?>

                  <?php foreach($main_cat['cat_level_1'] as $cat_level1) { ?>

                  <option value="<?= $cat_level1['id']; ?>" <?= isset($categoryData) && $categoryData->id == $cat_level1['id'] ? 'Selected' : '' ?> ><?= '-'.$cat_level1['cat_name']; ?></option>

                  <?php } if(isset($cat_level1['cat_level_2'])) { ?>

                  <?php foreach($cat_level1['cat_level_2'] as $cat_level2) { ?>

                  <option value="<?= $cat_level2['id']; ?>" <?= isset($categoryData) && $categoryData->id == $cat_level2['id'] ? 'Selected' : '' ?> ><?= '--'.$cat_level2['cat_name']; ?></option>

                  <?php } } ?> 

                  <?php } ?>   

                  <?php } ?> 

               </select>

               <div class="clear pad-bt-40"></div>

               <label for="position">Status <span class="required">*</span> </label>

               <select name="status" id="status" class="form-control">

                  <option value="1" <?php echo (isset($categoryData) && $categoryData->status == 1)?'selected':''?>>Active</option>

                  <option value="0" <?php echo (isset($categoryData) && $categoryData->status == 0)?'selected':''?>>Inactive</option>

               </select>



               <div class="clear pad-bt-40"></div>



               <label>Category Image <span class="required">*</span> </label>

               <p class="required">(note:Allow Only PNG,JPG,JPEG Images)</p>

               <div class="custom-file upload-file">

                  <input type="hidden" name="imageName[]">

                  <!-- <input type="file" class="custom-file-input" name="customFil" id="customFile" onchange="display_img(this);" style=""> -->

                  <input type="hidden" class="imageName" id="imageName" name="imageName" value="">

                  <input type="file" class="custom-file-input" name="customFil" id="customFile" style="" onchange="previewImagess();">

                  <svg style="" for="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">

                     <path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"></path>

                     <path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"></path>

                  </svg>

                  <p style="">Upload media</p>

                  <!-- img-block -->

               </div>

               <!-- upload-file -->

               <p class="upload-notes">Prefered Size: Width 1400px  X  Height 500px OR Width 1366px  X  Height 400px<br/>

                  Please optimize the Images before uploading so that it doesn't look heavy. Try to have it below 200Kbs.

               </p>

               

            </div>

            <div class="download-discard-small pos-ab-bottom">

               <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">

            <!-- <button class="white-btn" >Discard</button> -->

            <button id="categorybtn" class="download-btn">Save</button>

         </div>

            <!-- col-sm-6 -->

            

         </div>

         <!-- row -->

         

      </div>

   </div>

   </form>

</main>

<script type="text/javascript">

   function previewImagess() 

{

    var file = $("#customFile").val();

    $("#customFile").siblings("input[type='hidden']").val(file);



    var total_file=document.getElementById("customFile").files.length;

    for(var i=0;i<total_file;i++) 

    {

        var uniqid=Date.now()

        $('#uploadPreview').html('<img src="' + URL.createObjectURL(event.target.files[i]) + '" width="200">');

   }

}

</script>

<script src="<?php echo SKIN_JS; ?>category.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>

