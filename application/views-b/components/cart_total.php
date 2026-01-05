<?php
    $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
    $currency_symbol = $this->session->userdata('currency_symbol');
    $default_currency_flag = $this->session->userdata('default_currency_flag');
    if(isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems)>0) {
        $cartItems = $CartData->cartItems;
        $cartDetails = $CartData->cartDetails;
?>
    <div class="price-deails-cart">
        <h2>Price Details</h2>
        <div class="cart-price-box">
            <span class="price-title">Price (<?= count($cartItems);?> items)<br/>(Inclusive of taxes)</span> <span><?php 
            echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->base_subtotal, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->base_subtotal, 2));?></span>
        </div><!-- cart-price-box -->

        <div class="cart-price-box">
            <span class="price-title">Taxes</span> 
            <span><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->tax_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->tax_amount, 2));?></span>
        </div><!-- cart-price-box -->

        <?php if (isset($cartDetails->coupon_code) && $cartDetails->coupon_code!='') { ?>
        <div class="cart-price-box">
            <span class="price-title">Discount</span>
            <span><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->base_discount_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->base_discount_amount, 2));?></span>
        </div>
        <?php } ?>
        <div class="cart-price-box">
        <?php
        if ((isset($ShipAddress->address_id) && $ShipAddress->address_id!='') || (isset($_GET['order_id']) && $_GET['order_id']>0)) {?>
            <span class="price-title">Shipping Charges</span>
            <span><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->shipping_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->shipping_amount, 2));?></span>
        <?php } else { ?>
            <span class="price-title">Shipping Charges</span> <span>To be determined</span>
        <?php }  ?>
        </div>

        <div class="coupon-code">
            <form id="form-coupon" class="checkout_coupon" method="POST">
                <input type="hidden" name="coupon_type" value="0">
                    <p>Discount Code </p>
                    <input id="coupon_code" name="coupon_code" type="text" class="form-control"
                    value="<?php echo ($cartDetails->coupon_code!='') ? $cartDetails->coupon_code : '';?>" <?php echo (isset($cartDetails->coupon_code) && $cartDetails->coupon_code!='') ? 'readonly' : ''; ?> placeholder="Discount Code">
                    <?php if ($cartDetails->coupon_code =='') { ?>
                    <input type="submit" name="apply_coupon" value="Apply">
					<?php } else { ?>
                    <input type="button" onclick="removeDiscount('<?php echo $cartDetails->coupon_code ?>',0);" name="remove_coupon" value="Remove" class="remove-coupon">
                <?php } ?>	
            </form>
            <div id="coupon-message" class="coupon_code_message"></div>
        </div><!-- coupon-code -->

        <div class="cart-price-box">
            <span class="price-title">Sub Total</span>
            <span><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->subtotal, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->subtotal, 2));
            ?></span>
        </div>

        <div class="coupon-code">
            <form id="form-voucher" class="checkout_voucher" method="POST">
                <input type="hidden" name="coupon_type" value="1">
                <p>Voucher Code</p>
                <input id="voucher_code" name="coupon_code" type="text" class="form-control" value="<?php echo ($cartDetails->voucher_code!='') ? $cartDetails->voucher_code : '';?>" <?php echo (isset($cartDetails->voucher_code) && $cartDetails->voucher_code!='') ? 'readonly' : ''; ?> placeholder="Voucher Code">
                <?php if ($cartDetails->voucher_code =='') { ?>
                <input type="submit" name="apply_coupon" value="Apply">
                <?php } else { ?>
                <input type="button" onclick="removeDiscount('<?php echo $cartDetails->voucher_code ?>',1);" name="remove_coupon" value="Remove" class="remove-coupon">
                <?php } ?>
                <div id="voucher-message" class="coupon_code_message"></div>
            </form>
        </div><!-- coupon-code -->

        
        <?php if ($cartDetails->voucher_code!='') { ?>
        <div class="cart-price-box">
            <span class="price-title">Voucher Payment Method</span>
            <span><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->voucher_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->voucher_amount, 2));?></span>
        </div>
        <?php } ?>
        <!-- cod -->
        <?php 
           if(isset($cartType) && $cartType=='checkoutPage'){
                if ($cartDetails->payment_final_charge >0.00) {
        ?>
                    <div class="cart-price-box">
                        <span class="price-title">Payment Charge</span>
                        <span><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->payment_final_charge, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->payment_final_charge, 2));?></span>
                    </div>
        <?php  
            }   }  
        ?>
        <div class="amount-payable">
            <p>Amount Payable  <span class="final-price">
            <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->grand_total, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->grand_total, 2));?></span></p>
        </div><!-- amount-payable -->
		
		<?php if(isset($cartType) && $cartType=='cartPage') { ?>
			<a class="mob-view" href="<?php echo base_url(); ?>checkout"><button class="checkout" type="button"><?=lang('checkout')?></button></a>
		<?php } ?>

    </div><!-- price-deails-cart -->
<?php 
    }
?>