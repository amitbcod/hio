<form id="product-translate" method="post">
  <div class="main-inner ">
   <div class="variant-common-block variant-list product-trans-popup">
     <input type="hidden" id="hidden_contact_id" value="<?php echo $id; ?>">
      <input type="hidden" id="code" value="<?php echo $code; ?>">
      <h1 class="head-name pad-bottom-20"> <?php echo $ContactUsTitle; ?> - Translations in <?php echo  $codeName->name ;?>   </h1> 
      <div class="col-md-12">
      
           <?php if(isset($getContactUsTrans) && !empty($getContactUsTrans)){ ?>
            <div class="row"> 
            <div class="col-md-12">
              <div class="col-sm-12">
                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Message Block1*</label>
                  <textarea class="form-control editor" name="contact_us_message_trans" id="contact_us_message_trans"><?php if(isset($getContactUsTrans)){echo $getContactUsTrans->message; } ?></textarea>
                </div>
              </div>

              <div class="col-sm-12">
                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Message Block2</label>
                  <textarea class="form-control editor" name="contact_us_message_trans2" id="contact_us_message_trans2"><?php if(isset($getContactUsTrans)){echo $getContactUsTrans->message2; } ?></textarea>
                </div>
              </div>

              <div class="col-sm-12">
                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Message Block3</label>
                  <textarea class="form-control editor" name="contact_us_message_trans3" id="contact_us_message_trans3"><?php if(isset($getContactUsTrans)){echo $getContactUsTrans->message3; } ?></textarea>
                </div>
              </div>
                <!-- col-sm-6 -->

            </div><!-- col-sm-6 -->
                     
         </div>
        <?php }else{ ?>
           <div class="row"> 
            <div class="col-md-12">
              <div class="col-sm-12">
                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Message Block1*</label>
                  <textarea class="form-control editor" name="contact_us_message_trans" id="contact_us_message_trans"><?php if(isset($ContactUs)){echo $ContactUs->contact_message; } ?></textarea>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Message Block2</label>
                  <textarea class="form-control editor" name="contact_us_message_trans2" id="contact_us_message_trans2"><?php if(isset($ContactUs)){echo $ContactUs->contact_message2; } ?></textarea>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="customize-add-inner-sec page-content-textarea">
                  <label for="blockContent">Message Block3</label>
                  <textarea class="form-control editor" name="contact_us_message_trans3" id="contact_us_message_trans3"><?php if(isset($ContactUs)){echo $ContactUs->contact_message3; } ?></textarea>
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
  <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/contact_us_requests/write',$this->session->userdata('userPermission'))){ ?>
   <button class="download-btn" type="button" id="SaveTemplates"  onClick="SaveContactUsTrans();">Save</button>
 <?php } ?>
</div>

<!-- download-discard-small -->
</div>

<script type="text/javascript">   
    $('.editor').each(function () {
      var id=$(this).attr('id');
        CKEDITOR.replace(id, {
            extraPlugins :'justify', 
        });
    });
</script>