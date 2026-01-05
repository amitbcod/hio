<form id="product-translate">
	<div class="main-inner ">
   <div class="variant-common-block variant-list product-trans-popup">
     <input type="hidden" id="hidden_cms_id" value="<?php echo $cms_id; ?>">
      <input type="hidden" id="code" value="<?php echo $code; ?>">
      <h1 class="head-name pad-bottom-20"><?php echo $CmsPage->title ?> - Translations in <?php echo  $codeName->name ;?>   </h1>
      <div class="col-md-12">
      	 <?php if(isset($getPage) && !empty($getPage)){ ?>
          <div class="row">
            <div class="col-md-6">
               <div class="col-md-12 customize-add-inner-sec">
                <label for="pageTitle">Page Title *</label>
                <input class="form-control" type="text" name="pageTitle_lang" id="pageTitle_lang" value="<?php if(isset($getPage)){ echo $getPage->title; } ?>" placeholder="Enter Page Title here" required>
              </div><!-- col-sm-6 -->
              <div class="col-md-12 customize-add-inner-sec page-content-textarea">
                <label for="pageContent">Page Content *</label>
                <textarea class="form-control" name="pageContent_lang" id="pageContent_lang" required><?php if(isset($getPage)){ echo $getPage->content; } ?></textarea>
              </div><!-- col-sm-6 -->
            </div>
            
            <div class="col-md-6">
              <div class="col-md-12 customize-add-inner-sec">
                <label for="metaTitle">Meta Title</label>
                <input class="form-control" type="text" name="metaTitle_lang" id="metaTitle_lang" value="<?php if(isset($getPage)){ echo $getPage->meta_title; } ?>" placeholder="Enter Meta Title here">
              </div><!-- col-sm-6 -->
              <div class="col-md-12 customize-add-inner-sec">
                <label for="metaKeyword">Meta Keyword</label>
                <input class="form-control" type="text" name="metaKeyword_lang" id="metaKeyword_lang" value="<?php if(isset($getPage)){ echo $getPage->meta_keywords; } ?>" placeholder="Meta Keyword">
              </div><!-- col-sm-6 -->

              <div class="col-md-12 customize-add-inner-sec">
                <label for="metaKeyword">Meta Description</label>
                <textarea class="form-control" name="metaDescription_lang" id="metaDescription_lang" placeholder="Description area"><?php if(isset($getPage)){ echo $getPage->meta_description; } ?></textarea>
              </div><!-- col-sm-6 -->

               
            </div>
         </div>
       <?php }else{ ?>
         <div class="row">
            <div class="col-md-6">
               <div class="col-md-12 customize-add-inner-sec">
                <label for="pageTitle">Page Title *</label>
                <input class="form-control" type="text" name="pageTitle_lang" id="pageTitle_lang" value="<?php if(isset($CmsPage)){ echo $CmsPage->title; } ?>" placeholder="Enter Page Title here" required>
              </div><!-- col-sm-6 -->
              <div class="col-md-12 customize-add-inner-sec page-content-textarea">
                <label for="pageContent">Page Content *</label>
                <textarea class="form-control" name="pageContent_lang" id="pageContent_lang" required><?php if(isset($CmsPage)){ echo $CmsPage->content; } ?></textarea>
              </div><!-- col-sm-6 -->
            </div>
            
            <div class="col-md-6">

              <div class="col-md-12 customize-add-inner-sec">
                <label for="metaTitle">Meta Title</label>
                <input class="form-control" type="text" name="metaTitle_lang" id="metaTitle_lang" value="<?php if(isset($CmsPage)){ echo $CmsPage->meta_title; } ?>" placeholder="Enter Meta Title here">
              </div><!-- col-sm-6 -->
              <div class="col-md-12 customize-add-inner-sec">
                <label for="metaKeyword">Meta Keyword</label>
                <input class="form-control" type="text" name="metaKeyword_lang" id="metaKeyword_lang" value="<?php if(isset($CmsPage)){ echo $CmsPage->meta_keywords; } ?>" placeholder="Meta Keyword">
              </div><!-- col-sm-6 -->

              <div class="col-md-12 customize-add-inner-sec">
                <label for="metaKeyword">Meta Description</label>
                <textarea class="form-control" name="metaDescription_lang" id="metaDescription_lang" placeholder="Description area"><?php if(isset($CmsPage)){ echo $CmsPage->meta_description; } ?></textarea>
              </div><!-- col-sm-6 -->

               
            </div>
         </div>
       <?php } ?>
      </div>
   </div>
</div>
</form>
<!-- variant-common-block -->
<div class="download-discard-small ">
   <button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
 <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
   <button class="download-btn" type="button" id="type_apply"  onClick="SaveExistingCms();">Save</button>
 <?php } ?>
</div>

<!-- download-discard-small -->
</div>
<script type="text/javascript">
   $(function () {  
          CKEDITOR.replace('pageContent_lang',{
          extraPlugins :'justify', 
          extraAllowedContent : "span(*)",
          allowedContent: true, 
          }); 
            
          CKEDITOR.dtd.$removeEmpty.span = 0;
          CKEDITOR.dtd.$removeEmpty.i = 0; 
      });
</script>