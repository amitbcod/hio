<style type="text/css">
.fe-new-item li {
    position: relative;
    list-style: none;
}

.fe-new-item li:hover .product-add-to-cart {
    opacity: 1;
}
</style>
<li>
    <?php if($type != 'PrelaunchListing') {?>
        <span class="wish-list">
            <a href="javascript:void(0);" onclick="addToWishlist(<?php echo $prod->id; ?>);">
                <?php if (isset($prod->wishlist_status) && $prod->wishlist_status==1) { ?>
                    <img id="heart_wishlist_img_<?php echo $prod->id;?>"
                        src="<?php echo TEMP_SKIN_IMG ?>/heart-wishlist-active.png">
                <?php } else { ?>
                    <img id="heart_wishlist_img_<?php echo $prod->id;?>"
                        src="<?php echo TEMP_SKIN_IMG ?>/heart-wishlist-inactive.png">
                <?php } ?>
            </a>
        </span>
    <?php } ?>
    <div class="product-list-block-page">
        <a href="<?= $product_url ?>"><img class="lazy" data-src="<?php echo $prod_image; ?>"></a>
    </div><!-- product-list-block-page -->

    <div class="product-short-name">
        <a href="<?= $product_url ?>">
            <h3><?php echo ((isset($prod->other_lang_name) && $prod->other_lang_name!='') ? $prod->other_lang_name : $prod->name); ?>
            </h3>
        </a>
    </div><!-- product-short-name -->

    <div class="product-price">
        <?php $prod->display_list_price() ?>
    </div><!-- product-price -->

    <div class="product-add-to-cart">
        <?php if($type === 'PrelaunchListing'): ?>
            <?php if ($prod->product_type === 'simple') { ?>
                <button class="add-to-cart-btn" data-prelaunch="yes" data-product-id="<?php echo $prod->id;?>" data-qty="1"
                    onclick="addToCart(this);">Add To Cart</button>
            <?php }else{ ?>
                <button class="add-to-cart-btn"
                    onclick="gotoLocation('<?php echo BASE_URL.'product-detail/'.$prod->url_key.'?type=prelaunch'; ?>');">Add To
                    Cart</button>
            <?php } ?>
        <?php else: ?>
            <?php if (isset($prod->stock_status)) { ?>
                <?php if ($prod->stock_status=='Instock' && $prod->product_type=='simple') { ?>
                    <?php if (isset($restricted_access) && $restricted_access == 'yes' && $customer_id ==0) { ?>
                        <a><button type="button" id="add_to_cart" onclick="openRestrictedAccessPopup()"
                        class="add-to-cart-btn"><?=lang('ADD_TO_CART')?></button></a>
                    <?php } else { ?>
                        <button class="add-to-cart-btn" data-product-id="<?php echo $prod->id;?>" data-qty="1"
                        onclick="addToCart(this);">Add To Cart</button>
                    <?php } ?>
                <?php } elseif ($prod->stock_status=='Instock' && ($prod->product_type=='configurable' || $prod->product_type=='bundle')) { ?>
                    <button class="add-to-cart-btn" onclick="gotoLocation('<?= $product_url ?>');">Add To Cart</button>
                <?php } ?>
            <?php } else { ?>
                <?php if(isset($prod->coming_soon_flag) && $prod->coming_soon_flag == 1) { ?>
                    <button class="add-to-cart-btn" onclick="gotoLocation('<?= $product_url ?>');">Notify Me</button>
                    <a href="<?= $product_url ?>">
                        <div class="comingsoon-badge">Comming Soon</div>
                    </a>
                <?php } else { ?>
                    <button class="add-to-cart-btn out-of-stock-btn">Out Of Stock</button>
                <?php } ?>
            <?php } ?>
        <?php endif; // prelaunchlisting ?>
    </div><!-- product-add-to-cart -->
    <div id="addtocart-message-<?php echo $prod->id; ?>"
        class="addtocart-message addtocart-message-<?php echo $prod->id; ?>">
    </div>
</li>