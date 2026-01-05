<?php
    if(isset($productData)){
        if($type=='price'){
            $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
            $currency_symbol = $this->session->userdata('currency_symbol');
            $default_currency_flag = $this->session->userdata('default_currency_flag');
?>
            <?php if ($ProductData->special_price>0) { ?>

            <?php if (isset($ProductData->display_original) && $ProductData->display_original==1) { ?>

            <?php if ($ProductData->special_price < $ProductData->webshop_price) { ?>
                <span class="discounted-price special-price" id="product_price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($ProductData->least_price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($ProductData->least_price, 2));?></span>
                <span class="special-price" id="discounted_price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($ProductData->special_price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($ProductData->special_price, 2));?></span>

                <?php 
                $percent = (($ProductData->special_price - $ProductData->webshop_price) / $ProductData->webshop_price);
                $final_percentage = round($percent * 100);
                
                if ($final_percentage !== 0) { ?>
                    <span class="price save-discount">(<?= number_format($final_percentage) ?>%)</span>
                <?php } ?>

                <?php if ($ProductData->tax_amount > 0) { ?> <?=lang('inclusive_of_taxes')?> <?php } ?>
            <?php } else { ?>

                <span class="special-price" id="discounted_price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($ProductData->special_price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($ProductData->special_price, 2)); ?></span> <?php if ($ProductData->tax_amount > 0) { ?> <?=lang('inclusive_of_taxes')?> <?php } ?>

            <?php } } else { ?>

                <span class="special-price" id="discounted_price"><?php
                    echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($ProductData->special_price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($ProductData->special_price, 2));
                    ?></span> <?php if ($ProductData->tax_amount > 0) { ?> <?=lang('inclusive_of_taxes')?> <?php } ?>
            <?php } ?>

            <?php } else { ?>
            <span class="special-price" id="product_price"><?php
                echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($ProductData->least_price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($ProductData->least_price, 2));
                ?> </span><?php if ($ProductData->tax_amount > 0) {?><?=lang('inclusive_of_taxes')?><?php } ?>
            <?php } ?>


<?php
        }
    }
?>