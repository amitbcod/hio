<form id="product-translate">
	<div class="main-inner ">
   <div class="variant-common-block variant-list product-trans-popup">
      <input type="hidden" id="hidden_product_id" value="<?php echo $product_id; ?>">
      <input type="hidden" id="code" value="<?php echo $code; ?>">
      <h1 class="head-name pad-bottom-20">Product Translate in - <?php echo  $codeName->name ;?>   </h1>
      <div class="col-md-12">
      	<?php if(isset($getProduct) && !empty($getProduct)){ ?> 
      	 <div class="row">
            <div class="col-md-6">
               <div class="col-md-12">
                  <input type="text" class="form-control" name="product_name_lang" value="<?php echo $getProduct->name; ?>" 
                  id="product_name_lang" placeholder="Product Name *">
               </div>
               <div class="col-md-12">
                  <p class="mt-4">Description <span class="required">*</span></p>
                  <textarea class="form-control product-highlight-textarea " id="description_lang"  name="description_lang" ><?php echo (isset($getProduct->description) && $getProduct->description!='')?$getProduct->description:''; ?></textarea>
               </div>
            </div>
            <div class="col-md-6">
               <div class="col-sm-12">
                  <p>Highlights <span class="required">*</span></p>
                  <textarea class="formcontrol- product-highlight-textarea " id="highlights_lang"  name="highlights_lang" ><?php echo (isset($getProduct->highlights) && $getProduct->highlights!='')?$getProduct->highlights:''; ?></textarea>
               </div>
               <p class="mt-4">Meta Keyword</p>
               <div class="col-md-12">
                  <input type="text" class="form-control" name="meta_keyword_lang" id="meta_keyword_lang" placeholder=""  value="<?php echo (isset($getProduct->meta_keyword) && $getProduct->meta_keyword!='')?$getProduct->meta_keyword:''; ?>">
               </div>
               <div class="col-md-12">
                  <p class="mt-4">Meta Title</p>
                  <input type="text" class="form-control" name="meta_title_lang" id="meta_title_lang" placeholder="" value="<?php echo (isset($getProduct->meta_title) && $getProduct->meta_title!='')?$getProduct->meta_title:''; ?>">
               </div>
               <div class="col-md-12">
                  <p>Meta Description</p>
                  <textarea class="form-control product-highlight-textarea " id="meta_description_lang"  name="meta_description_lang" ><?php echo (isset($getProduct->meta_description) && $getProduct->meta_description!='')?$getProduct->meta_description:''; ?></textarea>
               </div>
            </div>
         </div>

         <?php }else{ ?>
         	<div class="row">
            <div class="col-md-6">
               <div class="col-md-12">
                  <input type="text" class="form-control" name="product_name_lang" value="<?php echo $ProductData->name; ?>" id="product_name_lang" placeholder="Product Name *">
               </div>
               <div class="col-md-12">
                  <p class="mt-4">Description</p>
                  <textarea class="form-control product-highlight-textarea " id="description_lang"  name="description_lang" ><?php echo (isset($ProductData->description) && $ProductData->description!='')?$ProductData->description:''; ?></textarea>
               </div>
            </div>
            <div class="col-md-6">
               <div class="col-sm-12">
                  <p>Highlights</p>
                  <textarea class="form-control product-highlight-textarea " id="highlights_lang"  name="highlights_lang" ><?php echo (isset($ProductData->highlights) && $ProductData->highlights!='')?$ProductData->highlights:''; ?></textarea>
               </div>
               <p class="mt-4">Meta Keyword</p>
               <div class="col-md-12">
                  <input type="text" class="form-control" name="meta_keyword_lang" id="meta_keyword_lang" placeholder=""  value="<?php echo (isset($ProductData->meta_keyword) && $ProductData->meta_keyword!='')?$ProductData->meta_keyword:''; ?>">
               </div>
               <div class="col-md-12">
                  <p class="mt-4">Meta Title</p>
                  <input type="text" class="form-control" name="meta_title_lang" id="meta_title_lang" placeholder="" value="<?php echo (isset($ProductData->meta_title) && $ProductData->meta_title!='')?$ProductData->meta_title:''; ?>">
               </div>
               <div class="col-md-12">
                  <p>Meta Description</p>
                  <textarea class="form-control product-highlight-textarea " id="meta_description_lang"  name="meta_description_lang" ><?php echo (isset($ProductData->meta_description) && $ProductData->meta_description!='')?$ProductData->meta_description:''; ?></textarea>
               </div>
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
<?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
   <button class="download-btn" type="button" id="type_apply"  onClick="SaveExistingProduct();">Save</button>
<?php } ?>
</div>

<!-- download-discard-small -->
</div>
<script type="text/javascript">
   $(function () { 
        CKEDITOR.replace('description_lang', {
       extraPlugins :'justify', 
       extraAllowedContent : "span(*)",
        allowedContent: true, 
      });      
    CKEDITOR.replace('highlights_lang',{
         extraPlugins :'justify', 
      extraAllowedContent : "span(*)",
        allowedContent: true, 
        });           
        CKEDITOR.dtd.$removeEmpty.span = 0;
        CKEDITOR.dtd.$removeEmpty.i = 0; 
    });
</script>