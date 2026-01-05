<form id="product-Media-variant">
	<div class="main-inner">
		<div class="variant-common-block variant-list product-trans-popup">
			<input type="hidden" id="hidden_product_id" value="<?php echo $product_id; ?>" name="product_id">
			<h1 class="head-name pad-bottom-20">Manage Media</h1>
			<div class="col-md-12">
				<?php //print_r($getProduct); ?>
				<?php if(isset($getProduct) && !empty($getProduct)){ ?>
				<input type="radio" id="product_variant" name="product_variant" value="0" <?php echo(isset($Product_media_variant->media_variant_id) && $Product_media_variant->media_variant_id == 0 ? 'checked' : ''); ?>>
				<label> SKU Level </label><br>
				<?php foreach($getProduct as $data){?>
					<input type="radio" id="product_variant" name="product_variant" value="<?php echo $data['attr_id'];?>" <?php echo(isset($Product_media_variant->media_variant_id) && $Product_media_variant->media_variant_id == $data['attr_id'] ? 'checked' : ''); ?>>
					<label><?php echo $data['attr_name']; ?></label>
					<?php if(isset($Product_media_variant->media_variant_id) && $Product_media_variant->media_variant_id == $data['attr_id'] && $Product_media_variant->media_variant_id > 0){ ?>
						<a class="float-right" href="javascript:void(0);" onclick="OpenMediaVariantProduct(<?php echo $product_id ?>)"> Manage Media </a>
					<?php } ?>
					<br>
				<?php }
				}
				?>
			</div>
		</div>
	</div>
</form>
<!-- variant-common-block -->
<div class="download-discard-small ">
   <button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
   <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
   	<button class="download-btn" type="button" id="type_apply"  onClick="SaveProductMediaVariant();">Save</button>
   <?php } ?>
</div>
