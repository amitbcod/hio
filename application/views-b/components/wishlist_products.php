<?php 
$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
$currency_symbol = $this->session->userdata('currency_symbol');
$default_currency_flag = $this->session->userdata('default_currency_flag');

if (isset($wishlistData) && $wishlistData != '') { ?>
    <ul class="cart-left-box-block">
        <?php foreach ($wishlistData as $list) {
            $price = ProductPresenter::from($list);
            $prod_image= ((isset($list->base_image) && $list->base_image!='') ? PRODUCT_THUMB_IMG.$list->base_image : PRODUCT_DEFAULT_IMG);
        ?>
        <li>
            <div class="cart-images">
                <a href="<?php echo BASE_URL.'product-detail/'.$list->url_key; ?>"><img src="<?php echo $prod_image; ?>"></a>
            </div>
            <div class="cart-table-right">
                 <a class="remove-cart" href="javascript:void(0)" onclick="RemoveWishlistItem('<?php echo $list->wishlist_id; ?>')"><i class="<?php echo (THEMENAME=='theme_zumbawear') ? 'fa fa-trash' : 'icon-delete'; ?>"></i>Remove</a>
                 <a href="<?php echo BASE_URL.'product-detail/'.$list->url_key; ?>">
                    <h2 class="head-cart"><?php echo ((isset($list->other_lang_name) && $list->other_lang_name!='') ? $list->other_lang_name : $list->name);?></h2>
                </a>
                <div class="price">
                    <div class="price-cart-table">
                    <?php $price->display_list_price(); ?>
                    </div>
                    <!-- <div class="discount-amt">15% Save</div>  -->
                </div>
            </div><!-- cart-table-right -->
            <div class="wishlist-addcart-btn">
                <?php
                $inventory = $list->product_inventory;
                if ($list->product_type == 'simple') {
                    if ($inventory->status == 'instock') { ?>
                        <button class="addtocart-blue" data-product-id="<?php echo $list->id;?>" data-qty="1" onclick="addToCart(this);">Add To Cart</button>
                    <?php } else { ?>
                        <button class="add-to-cart-btn out-of-stock-btn <?php echo (THEMENAME=='theme_zumbawear') ? 'tw-bg-zumbared  tw-text-white tw-p-1 tw-ml-3 tw-text-uppercase tw-px-4' : ''; ?>" disabled="">Out Of Stock</button>
                <?php } } else {
                    if ($inventory->status == 'instock') { ?>
                        <button onclick="gotoLocation('<?php echo BASE_URL.'product-detail/'.$list->url_key; ?>');" class="addtocart-blue">Add To Cart</button>
                    <?php } else { ?>
                     <button class="add-to-cart-btn out-of-stock-btn <?php echo (THEMENAME=='theme_zumbawear') ? ' tw-bg-zumbared  tw-text-white tw-p-1 tw-ml-3 tw-text-uppercase tw-px-4' : ''; ?>" disabled="">Out Of Stock</button>
                <?php } } ?>

            </div><!-- wishlist-addcart-btn -->
            <div id="addtocart-message-<?php echo $list->id; ?>" class="addtocart-message addtocart-message-<?php echo $list->id; ?>"></div>
        </li>
        <?php } ?>
    </ul>
<?php } ?>