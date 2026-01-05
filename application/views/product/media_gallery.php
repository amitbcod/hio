<div class="product-main-image">
    <?php $default_product_image='';
		$default_product_image = ((isset($ProductData->base_image) && $ProductData->base_image!='') ? PRODUCT_LARGE_IMG.$ProductData->base_image : PRODUCT_DEFAULT_IMG);
   
        if (empty($refresh_flag)) { ?>
        <img src="<?php echo $default_product_image; ?>" 
            alt="<?php echo $ProductData->name; ?>"
            data-BigImgsrc="<?php echo $default_product_image; ?>"
            class="img-responsive">
    <?php } ?>
</div>

<div class="product-other-images">
    <?php if(isset($ProductData->mediaGallery) && !empty($ProductData->mediaGallery)){
        foreach($ProductData->mediaGallery as $media){
            if($media->is_base_image == 1 && !empty($refresh_flag)){ 
                continue;
            }
            $product_image='';
            $product_image = ((isset($media->image) && $media->image!='') ? PRODUCT_LARGE_IMG.$media->image : PRODUCT_DEFAULT_IMG);	
            ?>
            <a href="<?php echo $product_image; ?>" class="fancybox-button" rel="photos-lib">
                <img alt="<?php echo $ProductData->name; ?>" src="<?php echo $product_image; ?>">
            </a>

        <?php } ?>
    <?php }else{ ?>
        <a href="<?php echo PRODUCT_DEFAULT_IMG; ?>" class="fancybox-button" rel="photos-lib">
            <img alt="<?php echo $ProductData->name; ?>" src="<?php echo PRODUCT_DEFAULT_IMG; ?>">
        </a>
    <?php } ?>
</div>