<?php
     if(isset($product_id)){
?>
        <span class="add-wish-list">
            <a href="javascript:void(0);" onclick="addToWishlist(<?php echo $product_id;?>);">
                <?php if (!empty($wishlistData) && isset($wishlistData) && $wishlistData->statusCode=='200') { ?>
                <img id="heart_wishlist_img_<?php echo $product_id;?>" src="<?php echo SKIN_URL; ?>images/heart-wishlist-active.png">&nbsp; <?=lang('wishlisted')?>
                <?php } else { ?>
                <img id="heart_wishlist_img_<?php echo $product_id;?>" src="<?php echo SKIN_URL; ?>images/heart-wishlist-black.png">&nbsp; <?=lang('add_to_wishlist')?>
                <?php } ?>
            </a>
        </span>
<?php
    }
?>