<form id="product-translate">
  <div class="main-inner ">
   <div class="variant-common-block variant-list product-trans-popup">
     <input type="hidden" id="hidden_banner_id" value="<?php echo $id; ?>">
      <input type="hidden" id="code" value="<?php echo $code; ?>">
      <h1 class="head-name pad-bottom-20">Banner - Translations in <?php echo  $codeName->name ;?>   </h1> 
      <div class="col-md-12">
        <?php if(isset($getBanners) && !empty($getBanners)){ ?>
            <div class="row">
            <div class="col-md-12">
                
               <div class="row">
                 <div class="col-sm-6">
                   <div class="customize-add-inner-sec">
                  <label for="blockTitle">Banner Heading</label>
                  <input class="form-control" type="text" name="banner_heading_lang" id="banner_heading_lang" value="<?= isset($getBanners) ? $getBanners->heading : '' ?>" placeholder="Enter Block Title here" required>
                </div>
                 </div>
                 <div class="col-sm-6">
                   <div class="customize-add-inner-sec">
                  <label for="blockTitle">Banner Button Text</label>
                  <input class="form-control" type="text" name="buttonText_lang" id="buttonText_lang" value="<?= isset($getBanners) ? $getBanners->button_text : '' ?>" placeholder="Enter Block Title here" required>
                </div>
                 </div>
               </div>

                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Block Content *</label>
                  <textarea class="form-control" name="desc_lang" id="desc_lang"><?php if(isset($getBanners)){ echo $getBanners->description; } ?></textarea>
                </div><!-- col-sm-6 -->

            </div><!-- col-sm-6 -->
                     
         </div>
       <?php }else{ ?>
  <div class="row">
            <div class="col-md-12">
                
               <div class="row">
                 <div class="col-sm-6">
                   <div class="customize-add-inner-sec">
                  <label for="blockTitle">Banner Heading</label>
                  <input class="form-control" type="text" name="banner_heading_lang" id="banner_heading_lang" value="<?= isset($banners) ? $banners->heading : '' ?>" placeholder="Enter Block Title here" required>
                </div>
                 </div>
                 <div class="col-sm-6">
                   <div class="customize-add-inner-sec">
                  <label for="blockTitle">Banner Button Text</label>
                  <input class="form-control" type="text" name="buttonText_lang" id="buttonText_lang" value="<?= isset($banners) ? $banners->button_text : '' ?>" placeholder="Enter Block Title here" required>
                </div>
                 </div>
               </div>

                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Block Content *</label>
                  <textarea class="form-control" name="desc_lang" id="desc_lang"><?php if(isset($banners)){ echo $banners->description; } ?></textarea>
                </div><!-- col-sm-6 -->

            </div><!-- col-sm-6 -->
                     
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
   <button class="download-btn" type="button" id="type_apply"  onClick="SaveBanner();">Save</button>
 <?php } ?>
</div>

<!-- download-discard-small -->
</div>
<script type="text/javascript">   
   $(function () {  
          CKEDITOR.replace('desc_lang',{
            extraPlugins :'justify', 
      extraAllowedContent : "span(*)",
          allowedContent: true, 
          }); 
            
          CKEDITOR.dtd.$removeEmpty.span = 0;
          CKEDITOR.dtd.$removeEmpty.i = 0; 
      });
</script>