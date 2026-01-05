<?php
    if(isset($PaymentMethods) && count($PaymentMethods)>0) {
        foreach ($PaymentMethods as $key => $value) {
           if ($value->payment_gateway_key=='paypal_express' || $value->payment_gateway_key=='stripe_payment' || $value->payment_id==1 || $value->payment_id==6) { //payment+_id_check
                    $title = ((isset($value->display_name) && $value->display_name != "") ? $value->display_name : $value->payment_gateway);
?>
                    <p>
                        <label class="radio-label-checkout"><input
                                    class="radio-checkout  single-payment" type="radio"
                                    value="<?php echo $value->payment_id; ?>"
                                    name="payment_method"><?php echo $title; ?>
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
    } 

?>