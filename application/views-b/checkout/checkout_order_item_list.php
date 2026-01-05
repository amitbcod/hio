<?php
    $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
    $currency_symbol = $this->session->userdata('currency_symbol');
    $default_currency_flag = $this->session->userdata('default_currency_flag');
?>
<?php foreach ($cartItems as $value) { 
        
        $base_image = ((isset($value->base_image) && $value->base_image!='') ? PRODUCT_THUMB_IMG.$value->base_image : PRODUCT_DEFAULT_IMG);
        $product_variants= (($value->product_variants != '') ? json_decode($value->product_variants) : '');	

        $variants = '';
        if (isset($product_variants) && $product_variants != '') {
            foreach ($product_variants as $pk=>$single_variant) {
                foreach ($single_variant as $key=>$val) {
                    $variants.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';
                }
            }
        }
    ?>
    <li>
    <div class="cart-images"><img src="<?php echo $base_image; ?>"></div>
    <div class="cart-table-right">
        <h2 class="head-cart"><?php echo ((isset($value->other_lang_name) && $value->other_lang_name!='') ? $value->other_lang_name : $value->product_name);?></h2>
        <?php if (isset($product_variants) && $product_variants != '') { ?>
            <p class="grey-light-text"><?= rtrim($variants, ", "); ?></p>
            <?php }?>
        <div class="price"> <div class="price-cart-table">
        <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($value->price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($value->price, 2));
        ?></div> <div class="qty-box-cart"> <span><?=lang('quantity')?></span> <?= $value->qty_ordered?> </div> </div>
    <?php if(SHOP_ID !== 1) { ?>
        <p class="delivery-time"><?= ($value->estimate_delivery_time != '')?lang('delivery_in').' '. $value->estimate_delivery_time.' '.lang('days'):'';?> </p>
        <?php } ?>
    </div><!-- cart-table-right -->
    </li>
<?php } ?>