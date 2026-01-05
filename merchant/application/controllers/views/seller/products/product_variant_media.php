<form id="product-Media">
   <div class="main-inner">
      <div class="variant-common-block variant-list product-trans-popup manage-media-poup-new ">
		<input type="hidden" id="hidden_product_id" value="<?php echo $product_id; ?>" name="product_id">
		<h1 class="head-name pad-bottom-20">Manage Media</h1>
		<div class="col-md-12">
			<?php  //print_r($variant_options_name) ;?>
			<?php foreach ($variant_options_name as $data) { ?>
				<p class="mt-4"><?php echo $data->attr_name . ' - ' . $data->attr_options_name; ?></p>
				<input type="hidden" value="<?php echo $data->attr_value; ?>" name="attr_option[]" class='attr_option' id='attr_option'>
				<div class="row image-checkbox-select">
					<?php foreach ($ProductMedia as $media) { ?>
						<div class="col-sm-2">
							<?php $selected_media = $this->SellerProductModel->getProductMediaGalleryDataByID($product_id, $media->id);?>
							<span class="single-img radio" id="media-file-<?php echo $media->id; ?>">
								<img src="<?=get_s3_url('products/thumb/' . $media->image) ?>" class="thumb">
								<input type="checkbox" name="media_attr_<?php echo $data->attr_value; ?>[]" class="media_attr_id" value='<?php echo $media->id; ?>'<?php echo (isset($selected_media) && $selected_media[0]->attr_option_id == $data->attr_value && $selected_media[0]->id == $media->id ? 'checked' : ''); ?>>&nbsp;
								<label class="checkbox-label"></label>
								<span class="manage-m-radio">
									<label>
									<input type="radio" name="default_variant_<?php echo $data->attr_value; ?>" value='<?php echo $media->id; ?>' <?php echo (isset($selected_media) && $selected_media[0]->is_default_variant == 1 && $selected_media[0]->id == $media->id && $selected_media[0]->attr_option_id == $data->attr_value ? 'checked' : ''); ?>>&nbsp;
									<span class="checkmark"></span>
									</label>
								</span>
							</span>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
         </div>
      </div>
   </div>
</form>
<!-- variant-common-block -->
<div class="download-discard-small ">
   <button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
   <?php if (empty($this->session->userdata('userPermission')) || in_array('seller/database/write', $this->session->userdata('userPermission'))) { ?>
   <button class="download-btn" type="button" id="type_apply"  onClick="SaveProductMediaByAttr();">Save</button>
   <?php
	} ?>
</div>