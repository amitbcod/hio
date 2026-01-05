<form id="product-translate">
  <div class="main-inner ">
   <div class="variant-common-block variant-list product-trans-popup">
     <input type="hidden" id="hidden_footer_id" value="<?php echo $id; ?>">
      <input type="hidden" id="code" value="<?php echo $code; ?>">
      <h1 class="head-name pad-bottom-20"><?php echo $sBlock->title ?> - Translations in <?php echo  $codeName->name ;?>   </h1> 
      <div class="col-md-12">
        
          <?php if(isset($getFooterBlock) && !empty($getFooterBlock)){ ?>
            <div class="row">
            <div class="col-md-12">
                
               <div class="customize-add-inner-sec">
                  <label for="blockTitle">Block Title *</label>
                  <input class="form-control" type="text" name="blockTitle_lang" id="blockTitle_lang" value="<?= isset($getFooterBlock) ? $getFooterBlock->title : '' ?>" placeholder="Enter Block Title here" required>
                </div>

                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Block Content *</label>
                  <textarea class="form-control" name="blockContent_lang" id="blockContent_lang"><?php if(isset($getFooterBlock)){ echo $getFooterBlock->content; } ?></textarea>
                </div><!-- col-sm-6 -->

            </div><!-- col-sm-6 -->
                     
         </div>
       <?php } else{ ?>
        <div class="row">
            <div class="col-md-12">
                
               <div class="customize-add-inner-sec">
                  <label for="blockTitle">Block Title *</label>
                  <input class="form-control" type="text" name="blockTitle_lang" id="blockTitle_lang" value="<?= isset($sBlock) ? $sBlock->title : '' ?>" placeholder="Enter Block Title here" required>
                </div>

                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Block Content *</label>
                  <textarea class="form-control" name="blockContent_lang" id="blockContent_lang"><?php if(isset($sBlock)){ echo $sBlock->content; } ?></textarea>
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
   <button class="download-btn" type="button" id="type_apply"  onClick="SaveFooter();">Save</button>
 <?php } ?>
</div>

<!-- download-discard-small -->
</div>
<script type="text/javascript">   
  CKEDITOR.replace('blockContent_lang', {
     extraPlugins :'justify',  
    });
</script>