<?php
$check_pincode_serviceable = 'N';
$cartItems = $CartData->cartItems;
$cartDetails = $CartData->cartDetails;

$razorpay_api_key = '';
$razorpay_api_key_secret = '';
/*new email check*/
if (isset($quoteData->customer_email)) {
    if (isset($quoteData->customer_email) && !empty($quoteData->customer_email) && $quoteData->customer_email != '') {
        $emailQuot = explode('@', $quoteData->customer_email);
        $emailQuotData = $emailQuot[1];
    } else {
        $emailQuotData = '';
    }
} else {
    $emailQuotData = '';
}
if (SHOP_ID === 3 && $emailQuotData === 'protonmail.com') {
    return;
}
/*end new email check*/

if (isset($quote_payment_data) && count($quote_payment_data) > 0) {
    $quote_payment_id = $quote_payment_data[0]->payment_method_id;
}
$checked = '';

if (isset($PaymentMethods) && count($PaymentMethods) > 0) {
    foreach ($PaymentMethods as $value) {
        $shop_flag_check = (isset($shop_flag) ? $shop_flag : '');
        if (isset($quote_payment_id) && $quote_payment_id != '') {
            if ($quote_payment_id == $value->payment_id) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
        }

        if ($value->payment_gateway_key == 'cod') {
            $COD_Avail_Flag = 'no';
            if ($shop_flag_check == 2 || $shop_flag_check == 4) {

                if ($shop_flag_check == 2 && $cartDetails->coupon_code == 'ZSBC10') {
                    $COD_Avail_Flag = 'no';
                } else {
                    if (isset($ShipAddress) && $ShipAddress->country == 'IN' && $cartDetails->subtotal < 10000 && $shop_flag_check == 2) {
                        $COD_Avail_Flag = 'yes';
                    } else if (isset($ShipAddress) && $ShipAddress->country == 'IN' && $shop_flag_check == 4) {
                        $COD_Avail_Flag = 'yes';
                    } else {
                        $COD_Avail_Flag = 'no';
                    }
                }

                if (isset($COD_Avail_Flag) && $COD_Avail_Flag == 'yes') {
                    $curl_url = PINCODE_API_URL . "?token=" . PINCODE_API_TOKEN . "&filter_codes=" . $ShipAddress->pincode;
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $curl_url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                    } else {
                        $result = json_decode($response, true);

                        if (!empty($result['delivery_codes'])) {
                            $check_pincode_serviceable = $result['delivery_codes'][0]['postal_code']['cod'];
                        } else {
                        }
                    }


                    if ($check_pincode_serviceable == 'Y') {
                        if (($value->payment_id == 3) && (isset($value->gateway_details) && $value->gateway_details != '')) {
                            $gateway_details = json_decode($value->gateway_details);

                            $razorpay_api_key = (isset($gateway_details->api_key) && $gateway_details->api_key != '') ? $gateway_details->api_key : '';
                            $razorpay_api_key_secret = (isset($gateway_details->api_key_secret) && $gateway_details->api_key_secret != '') ? $gateway_details->api_key_secret : '';
                        } else {
                        }

                        $title = ((isset($value->display_name) && $value->display_name != "") ? $value->display_name : $value->payment_gateway);
?>
                        <p><label class="radio-label-checkout <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? 'disabled' : ''; ?>"><input class="radio-checkout  single-payment ccc" type="radio" value="<?php echo $value->payment_id; ?>" name="payment_method" <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? '' : $checked; ?> <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? 'disabled reaonly' : ''; ?>><?php echo $title; ?>
                                <span class="radio-check"></span></label></p>
                        <?php if (isset($value->message) && $value->message != "") { ?>
                            <div class="message-box-payment">
                                <?php echo $value->message; ?>
                            </div>
                        <?php } ?>
                        <input type="hidden" name="payment_type_<?php echo $value->payment_id; ?>" id="payment_type_<?php echo $value->payment_id; ?>" value="<?php echo $value->payment_type; ?>">

                <?php
                    }
                }
            } else {

                $title = ((isset($value->display_name) && $value->display_name != "") ? $value->display_name : $value->payment_gateway);
                ?>
                <p><label class="radio-label-checkout <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? 'disabled' : ''; ?>"><input class="radio-checkout  single-payment " type="radio" value="<?php echo $value->payment_id; ?>" name="payment_method" <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? '' : $checked; ?> <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? 'disabled reaonly' : ''; ?>><?php echo $title; ?>
                        <span class="radio-check"></span></label></p>
                <?php if (isset($value->message) && $value->message != "") { ?>
                    <div class="message-box-payment">
                        <?php echo $value->message; ?>
                    </div>
                <?php } ?>
                <input type="hidden" name="payment_type_<?php echo $value->payment_id; ?>" id="payment_type_<?php echo $value->payment_id; ?>" value="<?php echo $value->payment_type; ?>">
            <?php
            } //end check shop flag

        } else {
            if (($value->payment_id == 3) && (isset($value->gateway_details) && $value->gateway_details != '')) {
                $gateway_details = json_decode($value->gateway_details);

                $razorpay_api_key = (isset($gateway_details->api_key) && $gateway_details->api_key != '') ? $gateway_details->api_key : '';
                $razorpay_api_key_secret = (isset($gateway_details->api_key_secret) && $gateway_details->api_key_secret != '') ? $gateway_details->api_key_secret : '';
            }

            $title = ((isset($value->display_name) && $value->display_name != "") ? $value->display_name : $value->payment_gateway);

            ?>

            <p><label class="radio-label-checkout <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? 'disabled' : ''; ?>"><input class="radio-checkout  single-payment" type="radio" value="<?= $value->payment_id ?>" name="payment_method" <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? '' : $checked; ?> <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? 'disabled reaonly' : ''; ?>>
                    <?php echo $title; ?> <span class="radio-check"></span>
                </label></p>
            <?php if (!empty($value->message) && $value->message != '') { ?>
                <div class="message-box-payment daaad">
                    <?php //echo $value->message; 
                    ?>
                </div>
            <?php } ?>
            <input type="hidden" name="payment_type_<?= $value->payment_id ?>" id="payment_type_<?= $value->payment_id ?>" value="<?= $value->payment_type ?>">

<?php
        }
    }
} ?>
<p style="color: #e91c1c; display:none" id="payment_warning" name="payment_warning">Please select payment method</p>
<?php if ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) { ?>
    <p><label class="radio-label-checkout"><input class="radio-checkout  single-payment" type="radio" value="voucher" name="payment_method" <?php echo ($cartDetails->voucher_code != '' && $cartDetails->grand_total <= 0) ? 'checked' : ''; ?>><?= lang('voucher') ?> <span class="radio-check"></span></label> </p>
    <input type="hidden" name="payment_type_voucher" id="payment_type_voucher" value="voucher">
<?php } ?>