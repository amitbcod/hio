<form id="product-translate" method="post">
  <div class="main-inner ">
   <div class="variant-common-block variant-list product-trans-popup">
     <input type="hidden" id="hidden_temp_id" value="<?php echo $id; ?>">
      <input type="hidden" id="code" value="<?php echo $code; ?>">
      <h1 class="head-name pad-bottom-20"> <?php echo $template_detail['title']; ?> - Translations in <?php echo  $codeName->name ;?>   </h1> 
      <div class="col-md-12">
      
            <?php if(isset($getEmail) && !empty($getEmail)){ ?>
              <div class="row">
            <div class="col-md-12">
              <div class="col-sm-12">
                   <div class="customize-add-inner-sec">
                  <label for="blockTitle">Template Subject *</label>
                  <input class="form-control" type="text" name="template_subject_trans" id="template_subject_trans" value="<?php if(isset($getEmail)){ echo $getEmail->subject; } ?>" placeholder="Enter Template Subject" required>
                </div>
                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Template Content *</label>
                  <textarea class="form-control" name="template_content_trans" id="template_content_trans"><?php if(isset($getEmail)){echo $getEmail->content; } ?></textarea>
                </div>

                 </div>
                <!-- col-sm-6 -->

            </div><!-- col-sm-6 -->
                     
         </div>
       <?php }else{ ?>
        <div class="row">
            <div class="col-md-12">
              <div class="col-sm-12">
                   <div class="customize-add-inner-sec">
                  <label for="blockTitle">Template Subject *</label>
                  <input class="form-control" type="text" name="template_subject_trans" id="template_subject_trans" value="<?php echo(isset($template_detail['subject'])) ? $template_detail['subject'] : ''?>" placeholder="Enter Template Subject" required>
                </div>
                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Template Content *</label>
                  <textarea class="form-control" name="template_content_trans" id="template_content_trans"><?php echo(isset($template_detail['content'])) ? $template_detail['content'] : ''?></textarea>
                </div>

                 </div>
                <!-- col-sm-6 -->

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
  <?php if(empty($this->session->userdata('userPermission')) || in_array('system/email_template/write',$this->session->userdata('userPermission'))){  ?>
   <button class="download-btn" type="button" id="SaveTemplates"  onClick="SaveTemplates();">Save</button>
 <?php } ?>
</div>

<!-- download-discard-small -->
</div>

<script type="text/javascript">   
  CKEDITOR.replace('template_content_trans', {
     extraPlugins :'justify', 
    });
</script>