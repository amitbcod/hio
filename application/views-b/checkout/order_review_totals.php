	<?php
        $cartItems = $CartData->cartItems;
        $cartDetails = $CartData->cartDetails;
 
        $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
        $currency_symbol = $this->session->userdata('currency_symbol');
        $default_currency_flag = $this->session->userdata('default_currency_flag');
?>

<p>Price (<?= count($cartItems);?> items) <br/>(Inclusive of taxes)<span class="amount-doller">
<?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->base_subtotal, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->base_subtotal, 2));
?></span></p>

<p>Taxes <span class="amount-doller">
<?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->tax_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->tax_amount, 2));
?></span></p>

<?php if (isset($cartDetails->coupon_code) && $cartDetails->coupon_code!='') { ?>
<p>Discount <span class="amount-doller">
    <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->base_discount_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->base_discount_amount, 2));?>
</span></p>
<?php } ?>

<p>Shipping Charges <span class="amount-doller"><?php
echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->shipping_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->shipping_amount, 2));
?></span></p>

<p>Sub Total <span class="amount-doller"><?php
echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->subtotal, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->subtotal, 2));
?></span></p>

<?php if (isset($cartDetails->voucher_code) && $cartDetails->voucher_code!='') { ?>
    <p>Voucher (Payment method) <span class="amount-doller">
    <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->voucher_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->voucher_amount, 2)); ?></span></p>
<?php } ?>

<?php if (isset($cartDetails->payment_final_charge) && $cartDetails->payment_final_charge!='') {
    if ($cartDetails->payment_final_charge >0.00) {?>
        <p>Payment Charge <span class="amount-doller">
        <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->payment_final_charge, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->payment_final_charge, 2));?></span></p>
<?php } } ?>

<p class="order-total">Order Total <span class="amount-doller"><?php
echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->grand_total, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->grand_total, 2));
?></span></p>