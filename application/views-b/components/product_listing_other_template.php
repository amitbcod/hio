<div class="block-4 text-center">
	
	<figure class="">
		<a href="<?= $product_url ?>"><img
					data-src="<?php echo $prod_image; ?>"
					alt="<?= lang('image_placeholder') ?>" class="img-fluid lazy"></a>
	</figure>
	<div class="block-4-text p-1">
		<h3>
			<a href="<?= $product_url ?>"><?php
				if (!empty($prod->other_lang_name) && $prod->other_lang_name != '') {
					echo $prod->other_lang_name;
				} else {
					echo $prod->name;
				}
				?></a></h3>
		<?php $prod->display_list_price() ?>
	</div>
	<?php if($type != 'NewArrivalListing') {?>
		<?php if(!(isset($prod->stock_status)) && isset($prod->coming_soon_flag) && $prod->coming_soon_flag == 1) { ?>
			<a href="<?= $product_url ?>"><div class="comingsoon-badge"><?=lang('coming_soon_notify')?></div></a>
		<?php } ?>
	<?php } ?>
</div>
