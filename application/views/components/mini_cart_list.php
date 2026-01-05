<?php
$CartData = '';
if (!empty($cart_response) && isset($cart_response) && $cart_response->is_success == 'true') {
    $CartData = $cart_response->cartData;
}

?>
<div class="top-cart-block">


    <div class="top-cart-info">
        <a href="javascript:void(0);" class="top-cart-info-count"><?php echo ($cart_count > 0) ? $cart_count : 0 ?>
         <span><?= $this->lang->line('items'); ?></span></a>


        <a href="javascript:void(0);" class="top-cart-info-value">
            <?php 
if (is_object($CartData) && isset($CartData->cartDetails->base_grand_total)) {
    echo ($CartData->cartDetails->base_grand_total > 0) ? 'MUR' . $CartData->cartDetails->base_grand_total : 'MUR 0';
} else {
    echo 'MUR 0';
}
?></a>
    </div>
    <i class="fa fa-shopping-cart"></i>

    <div class="top-cart-content-wrapper">
        <?php
        if (isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems) > 0) { ?>
            <?php $cartItems = $CartData->cartItems; ?>
            <?php
            $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
            $currency_symbol = $this->session->userdata('currency_symbol');
            $default_currency_flag = $this->session->userdata('default_currency_flag');
            ?>
            <div class="top-cart-content">
                <ul class="scroller" style="height: 250px;">
                    <?php foreach ($cartItems as $value) {
                        $base_image = ((isset($value->base_image) && $value->base_image != '') ? PRODUCT_THUMB_IMG . $value->base_image : PRODUCT_DEFAULT_IMG);
                        $product_variants = (($value->product_variants != '') ? json_decode($value->product_variants) : '');
                        $qty_ordered = $value->qty_ordered;

                        $variants = '';
                        if (isset($product_variants) && $product_variants != '') {
                            foreach ($product_variants as $pk => $single_variant) {
                                foreach ($single_variant as $key => $val) {
                                    $variants .= $key . ' - ' . $val;
                                }
                            }
                        } ?>

                        <li>
                            <a href="<?= linkUrl('product-detail/' . $value->url_key . '?type=prelaunch') ?>">
                                <img src="<?php echo $base_image; ?>" alt="<?php echo ((isset($value->other_lang_name) && $value->other_lang_name != '') ? $value->other_lang_name : $value->product_name); ?>" width="37" height="34">
                            </a>
                            <span class="cart-content-count">x <?php echo $qty_ordered; ?></span>
                            <strong>
                                <?php echo ((isset($value->other_lang_name) && $value->other_lang_name != '') ? $value->other_lang_name : $value->product_name); ?>.</br>
                                <?php if (isset($product_variants) && $product_variants != '') { ?>
                                    [<?= rtrim($variants, ", "); ?>]
                                <?php } ?>
                            </strong>

                            <em><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($value->price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($value->price, 2)); ?></em>
                            <a href="javascript:void(0);" class="del-goods" onclick="RemoveCartItem('<?php echo $value->item_id; ?>')">&nbsp;</a>

                        </li>
                    <?php } ?>



                </ul>
                <div class="text-right">
                    <a href="<?= base_url(); ?>cart" class="btn btn-default">
    <?= $this->lang->line('view_cart'); ?>
</a>

                    <!-- <a href="<?php echo base_url(); ?>checkout" class="btn btn-primary">Checkout</a> -->
                </div>
            </div>
        <?php } else { ?>
            <div class="top-cart-content">
<p style="padding:8px 0;text-align:center;">
  <?= $this->lang->line('cart_empty'); ?>
</p>

<p style="padding:8px 0;text-align:center;">
  <a href="<?= base_url(); ?>" class="btn btn-blue" data-abc="true">
    <?= $this->lang->line('continue_shopping'); ?>
  </a>
</p>

            </div>
        <?php } ?>
    </div>
</div>