<a class="site-cart" data-toggle="collapse" href="#minicart" role="button" aria-expanded="false"
    aria-controls="collapseExample">
    <span class="icon icon-shopping_cart"></span>
    <span
        class="count <?php echo ($cart_count > 0) ? '' : 'd-none'; ?>"><?php echo ($cart_count > 0) ? $cart_count : ''; ?></span>
</a>

<div class="mini-cart collapse" id="minicart">
    <?php $CartData = '';
    if (!empty($cart_response) && isset($cart_response) && $cart_response->is_success=='true') {
        $CartData = $cart_response->cartData;
    }
    
    if (isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems)> 0) {?>
    <?php $cartItems = $CartData->cartItems;?>

    <ul class="cart-left-box-block">
        <?php
                $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
                $currency_symbol = $this->session->userdata('currency_symbol');
                $default_currency_flag = $this->session->userdata('default_currency_flag');
            ?>

        <?php foreach ($cartItems as $value) {
                $base_image= ((isset($value->base_image) && $value->base_image!='') ? PRODUCT_THUMB_IMG.$value->base_image : PRODUCT_DEFAULT_IMG);
                $product_variants= (($value->product_variants != '') ? json_decode($value->product_variants) : '');

                $variants = '';
                if (isset($product_variants) && $product_variants != '') {
                    foreach ($product_variants as $pk=>$single_variant) {
                        foreach ($single_variant as $key=>$val) {
                            $variants.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';
                        }
                    }
                } ?>

        <li>
            <div class="cart-images"><img src="<?php echo $base_image; ?>"></div>
            <div class="cart-table-right">
                <a class="remove-cart" href="javascript:void(0)"
                    onclick="RemoveCartItem('<?php echo $value->item_id; ?>')"><i class="icon-delete"></i>
                </a>
                <?php if ($value->prelaunch == 1) { ?>
                <a href="<?= linkUrl('product-detail/'.$value->url_key.'?type=prelaunch') ?>">
                    <?php } else { ?>
                    <a href="<?= linkUrl('product-detail/'.$value->url_key) ?>">
                        <?php } ?>
                        <h2 class="head-cart">
                            <?php echo ((isset($value->other_lang_name) && $value->other_lang_name!='') ? $value->other_lang_name : $value->product_name); ?>
                        </h2>
                    </a>

                    <?php if (isset($product_variants) && $product_variants != '') { ?>
                    <p class="grey-light-text"><?= rtrim($variants, ", "); ?></p>
                    <?php }?>
                    <div class="price">
                        <div class="price-cart-table">
                            <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($value->price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($value->price, 2));?>
                        </div>
                    </div>
                    <?php if ($value->estimate_delivery_time != '') {?>
                    <?php if(SHOP_ID !== 1) { ?>
                    <p class="delivery-time"><?=lang('delivery_in')?> <?= $value->estimate_delivery_time ?>
                        <?=lang('days')?></p>
                    <?php } ?>
                    <?php } ?>
            </div><!-- cart-table-right -->
        </li>
        <?php }?>
    </ul>
    <button class="go-to-cart" onclick="javascript:window.location='<?= linkUrl('cart') ?>';">Go To Cart</button>
    <?php } else {?>
    <div class="card-body ">
        <div class="col-sm-12 empty-cart-cls text-center"><span class="icon icon-shopping_cart"></span>
            <h3><strong>Your Cart is Empty</strong></h3>
            <h4>Add something to make me happy :)</h4> <a href="<?php echo base_url(); ?>" class="btn btn-blue"
                data-abc="true">Continue Shopping On</a>
        </div>
    </div>
    <?php } ?>
</div><!-- mini-cart -->