<div class="exzoom hidden" id="exzoom">
	<div class="exzoom_img_box">

	<?php
	$default_product_image='';
		$default_product_image = ((isset($ProductData->base_image) && $ProductData->base_image!='') ? PRODUCT_LARGE_IMG.$ProductData->base_image : PRODUCT_DEFAULT_IMG);
    ?>

		<ul class='exzoom_img_ul'>
		<?php if (empty($refresh_flag)) { ?>
		<li><img src="<?php echo $default_product_image; ?>"  title="<?php echo $ProductData->name; ?>"/></li>
		<?php } ?>

		<?php if (isset($ProductData->mediaGallery) && !empty($ProductData->mediaGallery)) {
        foreach ($ProductData->mediaGallery as $media) {
            if ($media->is_base_image==1 && empty($refresh_flag)) {
                continue;
            }
            $product_image='';
			$product_image = ((isset($media->image) && $media->image!='') ? PRODUCT_LARGE_IMG.$media->image : PRODUCT_DEFAULT_IMG);	
			?>
			<li><img src="<?php echo $product_image; ?>"  title="<?php echo $ProductData->name; ?>"/></li>
			<?php
        }
    } else { ?>
			<li><img src="<?php echo PRODUCT_DEFAULT_IMG; ?>"/></li>
			<?php } ?>
		</ul>
	</div>
	<?php if (isset($ProductData->mediaGallery) && !empty($ProductData->mediaGallery)) { ?>
	<div class="exzoom_nav"></div>
	<?php } ?>
	<?php if (count($ProductData->mediaGallery)>6) { ?>
	<p class="exzoom_btn">
		<a href="javascript:void(0);" class="exzoom_prev_btn"> < </a>
		<a href="javascript:void(0);" class="exzoom_next_btn"> > </a>
	</p>
	<?php } ?>
</div>

<script type="text/javascript">
$(document).ready(function ($) {
	$('.container').imagesLoaded( function() {
		$("#exzoom").exzoom({
			autoPlay: false,
		});
		$("#exzoom").removeClass('hidden')
	});
});

</script>