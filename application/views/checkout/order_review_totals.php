<?php
$cartItems = $CartData->cartItems;
$cartDetails = $CartData->cartDetails;

$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
$currency_symbol = $this->session->userdata('currency_symbol');
$default_currency_flag = $this->session->userdata('default_currency_flag');
?>
<ul>
    <li>
        <em>
            <?= sprintf(lang('price_items'), count($cartItems)); ?> <br />
            <?= lang('inclusive_taxes'); ?><span class="amount-doller"></span>
        </em>
        <strong class="price">
            <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) 
                ? convert_currency_website($cartDetails->base_subtotal, $currency_conversion_rate, $currency_symbol) 
                : CURRENCY_TYPE . number_format($cartDetails->base_subtotal, 2)); ?>
        </strong>
    </li>
    <li>
        <em><?= lang('taxes'); ?></em>
        <strong class="price">
            <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) 
                ? convert_currency_website($cartDetails->tax_amount, $currency_conversion_rate, $currency_symbol) 
                : CURRENCY_TYPE . number_format($cartDetails->tax_amount, 2)); ?>
        </strong>
    </li>
    <li>
        <em><?= lang('shipping_cost'); ?></em>
        <strong class="price">
            <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) 
                ? convert_currency_website($cartDetails->shipping_amount, $currency_conversion_rate, $currency_symbol) 
                : CURRENCY_TYPE . number_format($cartDetails->shipping_amount, 2)); ?>
        </strong>
    </li>

    <?php
    if ($PaymentDetails->payment_method_id == 2){
        $totalAmount = $cartDetails->base_subtotal + $cartDetails->shipping_amount;
        
        if (!empty($cartDetails->base_discount_amount)) {
            $totalAmount -= $cartDetails->base_discount_amount;
        } elseif (!empty($cartDetails->voucher_amount)) {
            $totalAmount -= $cartDetails->voucher_amount;
        }

        if ($totalAmount >= 5000) {
            $gatewayCharges = $totalAmount * 0.02; ?>
            <li>
                <em><?= lang('payment_gateway_charges'); ?></em>
                <strong class="price">
                    <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1)
                        ? convert_currency_website($gatewayCharges, $currency_conversion_rate, $currency_symbol)
                        : CURRENCY_TYPE . number_format($gatewayCharges, 2)); ?>
                </strong>
            </li>
        <?php }
    }
    ?>

    <?php if (isset($cartDetails->coupon_code) && $cartDetails->coupon_code != '') { ?>
        <li>
            <em><?= lang('discount'); ?></em>
            <strong class="price">
                <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) 
                    ? convert_currency_website($cartDetails->base_discount_amount, $currency_conversion_rate, $currency_symbol) 
                    : CURRENCY_TYPE . number_format($cartDetails->base_discount_amount, 2)); ?>
            </strong>
        </li>
    <?php } ?>

    <?php if (isset($cartDetails->voucher_code) && $cartDetails->voucher_code != '') { ?>
        <li>
            <em><?= lang('gift_card_amount'); ?></em>
            <strong class="price">
                <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) 
                    ? convert_currency_website($cartDetails->voucher_amount, $currency_conversion_rate, $currency_symbol) 
                    : CURRENCY_TYPE . number_format($cartDetails->voucher_amount, 2)); ?>
            </strong>
        </li>
    <?php } ?>

    <li class="checkout-total-price">
        <em><?= lang('total'); ?></em>
        <strong class="price">
            <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) 
                ? convert_currency_website($cartDetails->grand_total, $currency_conversion_rate, $currency_symbol) 
                : CURRENCY_TYPE . number_format($cartDetails->grand_total, 2)); ?>
        </strong>
    </li>
</ul>
