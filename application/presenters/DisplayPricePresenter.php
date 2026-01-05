<?php

class DisplayPricePresenter
{
    private $ci;
    private $product;
    private $currency_conversion_rate;
    private $currency_symbol;
    private $default_currency_flag;

    public function __construct()
    {
        $this->ci = &get_instance();

        $this->currency_conversion_rate = $this->ci->session->userdata('currency_conversion_rate');
        $this->currency_symbol = $this->ci->session->userdata('currency_symbol');
        $this->default_currency_flag = $this->ci->session->userdata('default_currency_flag');
    }

    public function __invoke(ProductPresenter $product, $return_html = false)
    {
        $this->product = $product;
        ob_start();
        if ($product->product_type === 'simple' || $product->product_type === 'bundle') {
            $this->display_simple_price();
        } else {

            $this->display_configurable_price();
        }

        $display_price = ob_get_clean();

        if ($return_html === true) {
            return $display_price;
        }
        echo $display_price;
    }

    private function display_simple_price()
    {

        $vat_percent_session =  $this->ci->session->userdata('vat_percent');

        if (isset($vat_percent_session) && $vat_percent_session != '') {
            $webshop_price = $this->product->eu_webshop_price;
        } else {
            $webshop_price = $this->product->webshop_price;
        }

        if ($this->product->special_price > 0) {
            if (isset($this->product->display_original) && $this->product->display_original == 1) {
                if ($this->product->special_price < $this->product->webshop_price) { ?>
                    <?php if (THEMENAME == 'theme_zumbawear') { ?>
                        <?php //echo '<span class="text-primary font-weight-bold item-price">'; 
                        ?>
                        <?php
                        if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                            echo convert_currency_website($this->product->special_price, $this->currency_conversion_rate, $this->currency_symbol);
                        } else {
                            echo CURRENCY_TYPE . number_format($this->product->special_price, 0);
                        } ?>
                        <?php //echo '</span><span class="base-price">'; 
                        ?>
                        <s><?php
                            if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                                echo convert_currency_website($webshop_price, $this->currency_conversion_rate, $this->currency_symbol);
                            } else {
                                echo CURRENCY_TYPE . number_format($webshop_price, 0);
                            }  ?></s>
                        <?php //echo '</span>'; 
                        ?>
                        <?php
                        $percent = (($this->product->special_price - $webshop_price) / $webshop_price);
                        $final_percentage = round($percent * 100);

                        if ($final_percentage != 0.00) {
                            $this->print_discount_percentage_html($final_percentage);
                        }
                        ?>
                    <?php } else { ?>

                        <?php //echo (THEMENAME == 'theme2') ? '<span class="base-price">' : '<p class="font-weight-bold item-price">'; 
                        ?>
                        <s><?php
                            if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                                echo convert_currency_website($webshop_price, $this->currency_conversion_rate, $this->currency_symbol);
                            } else {
                                echo CURRENCY_TYPE . number_format($webshop_price, 0);
                            }  ?></s>

                        <?php //echo (THEMENAME == 'theme2') ? '</span><span class="text-primary font-weight-bold item-price">' : '</p><p class="font-weight-bold item-price">'; 
                        ?>
                        <?php
                        if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                            echo convert_currency_website($this->product->special_price, $this->currency_conversion_rate, $this->currency_symbol);
                        } else {
                            echo CURRENCY_TYPE . number_format($this->product->special_price, 0);
                        } ?>
                        <?php //echo (THEMENAME == 'theme2') ? '</span>' : '</p>'; 
                        ?>
                        <?php
                        $percent = (($this->product->special_price - $webshop_price) / $webshop_price);
                        $final_percentage = round($percent * 100);

                        if ($final_percentage != 0.00) {
                            $this->print_discount_percentage_html($final_percentage);
                        } ?>
                    <?php } ?>

                <?php } else { ?>
                    <?php //echo (THEMENAME == 'theme2' || THEMENAME == 'theme_zumbawear') ? '<span class="text-primary font-weight-bold item-price">' : '<p class="text-primary font-weight-bold item-price">'; 
                    ?>
                    <?php
                    if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                        echo convert_currency_website($this->product->special_price, $this->currency_conversion_rate, $this->currency_symbol);
                    } else {
                        echo CURRENCY_TYPE . number_format($this->product->special_price, 0);
                    }  ?>
                    <?php //echo (THEMENAME == 'theme2' || THEMENAME == 'theme_zumbawear') ? '</span>' : '</p>'; 
                    ?>
                <?php }
            } else { ?>
                <?php //echo (THEMENAME == 'theme2' || THEMENAME == 'theme_zumbawear') ? '<span class="text-primary font-weight-bold item-price">' : '<p class="font-weight-bold item-price">'; 
                ?>
                <?php
                if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                    echo convert_currency_website($this->product->special_price, $this->currency_conversion_rate, $this->currency_symbol);
                } else {
                    echo CURRENCY_TYPE . number_format($this->product->special_price, 0);
                }     ?>
                <?php //echo (THEMENAME == 'theme2' || THEMENAME == 'theme_zumbawear') ? '</span>' : '</p>'; 
                ?>
            <?php }
        } else { ?>
            <?php //echo (THEMENAME == 'theme2' || THEMENAME == 'theme_zumbawear') ? '<span class="text-primary font-weight-bold item-price">' : '<p class="font-weight-bold item-price">'; 
            ?>
            <?php
            if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                echo convert_currency_website($webshop_price, $this->currency_conversion_rate, $this->currency_symbol);
            } else {
                echo CURRENCY_TYPE . number_format($webshop_price, 0);
            } ?>
            <?php //echo (THEMENAME == 'theme2' || THEMENAME == 'theme_zumbawear') ? '</span>' : '</p>'; 
            ?>
        <?php } ?>
<?php
    }

    private function display_configurable_price()
    {

        if ($this->product->min_price == $this->product->max_price) {
            if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                $price = convert_currency_website($this->product->min_price, $this->currency_conversion_rate, $this->currency_symbol);
            } else {
                $price = CURRENCY_TYPE . number_format($this->product->min_price, 0);
            }
            $price1 = $this->product->min_price;
        } else {
            if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {

                $price1 = convert_currency_website($this->product->min_price, $this->currency_conversion_rate, $this->currency_symbol);
                $price2 = convert_currency_website($this->product->max_price, $this->currency_conversion_rate, $this->currency_symbol);
                $price = $this->get_price_range_display($price1, $price2);
            } else {
                $price = $this->get_price_range_display(CURRENCY_TYPE . number_format($this->product->min_price, 0), CURRENCY_TYPE . number_format($this->product->max_price, 0));
                // echo "else ".($price); 
                // // print_r($this->product);
                // exit;
            }
            $price1 = $this->product->min_price;
        }

        if ($this->product->special_price <= 0) {
            $this->print_price_html($price);
            return;
        }

        if ($this->product->special_min_price == $this->product->special_max_price) {
            if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                $special_price = convert_currency_website($this->product->special_min_price, $this->currency_conversion_rate, $this->currency_symbol);
            } else {
                $special_price = CURRENCY_TYPE . number_format($this->product->special_min_price, 0);
            }
            $special_price1 = $this->product->special_min_price;
        } else {
            if ($this->ci->session->userdata('currency_code_session') && $this->default_currency_flag != 1) {
                $price_min = convert_currency_website($this->product->special_min_price, $this->currency_conversion_rate, $this->currency_symbol);
                $price_max = convert_currency_website($this->product->special_max_price, $this->currency_conversion_rate, $this->currency_symbol);
                $special_price = $this->get_price_range_display($price_min, $price_max);
            } else {
                $special_price = $this->get_price_range_display(
                    CURRENCY_TYPE . number_format($this->product->special_min_price, 0),
                    CURRENCY_TYPE . number_format($this->product->special_max_price, 0)
                );
            }


            $special_price1 = $this->product->special_min_price; //new
        }

        if (!isset($this->product->display_original) || $this->product->display_original != 1) {
            $this->print_price_html($special_price);
            return;
        }

        if (($this->product->special_min_price === $this->product->special_max_price) && ($this->product->min_price === $this->product->max_price)) {
            if ($special_price1 < $price1) {
                $percent = (($special_price1 - $price1) / $price1);
                $final_percentage = round($percent * 100);

                if (THEMENAME == 'theme_zumbawear') {
                    $this->print_price_html($special_price);
                    $this->print_price_html(CURRENCY_TYPE . number_format(round($price1, 2), 2), true);
                } else {
                    $this->print_price_html(CURRENCY_TYPE . round($price1, 2), true);
                    $this->print_price_html($special_price);
                }

                if ($final_percentage !== 0) {
                    $this->print_discount_percentage_html($final_percentage);
                }
            } else {

                $this->print_price_html($special_price);
            }
            return;
        }

        if ($special_price1 < $price1) {
            $percent = (($special_price1 - $price1) / $price1);
            $final_percentage = round($percent * 100);

            if (THEMENAME == 'theme_zumbawear') {

                $this->print_price_html($special_price);
                $this->print_price_html(CURRENCY_TYPE . number_format(round($price1, 2), 2), true);
            } else {

                $this->print_price_html(CURRENCY_TYPE . number_format(round($price1, 2), 0), true);
                $this->print_price_html($special_price);
            }

            if ($final_percentage !== 0) {
                $this->print_discount_percentage_html($final_percentage);
            }
            return;
        }
        $this->print_price_html($special_price);
    }

    private function print_price_html($price, $strikethrough = false)
    {

        if ($strikethrough) {
            if (THEMENAME == 'theme2' || THEMENAME == 'theme_zumbawear') {
                echo <<<HTML
                    <s  style="margin-right:10px">$price</s>
                    HTML;
            } else {
                echo <<<HTML
                <s class="test"  style="margin-right:10px">$price</s>
                HTML;
            }
        } else {
            if (THEMENAME == 'theme2' || THEMENAME == 'theme_zumbawear') {
                echo <<<HTML
                $price
                HTML;
            } else {
                echo <<<HTML
                $price
                HTML;
            }
        }
    }

    private function print_discount_percentage_html($percentage)
    {
        $percentage = number_format(round($percentage, 0), 0);

        if (THEMENAME == 'theme_zumbawear') {
            // NM: Hack to avoid showing "SALE" for regular ZIN prices
            if ($this->ci->session->userdata('CustomerTypeID') === 3 && abs($percentage) < 21) {
                echo "<!-- [ZIN] -->";
            }
            if ($percentage <= -10) {
            echo <<<HTML
            <span class="tw-flex-1"></span>
            <span class="price save-discount">$percentage%</span>
            HTML;
            }
        } else {
            if ($percentage <= -10) {
            echo <<<HTML
            <span class="tw-flex-1"></span>
            <span class="price save-discount">($percentage%)</span>
            HTML;
            }
        }
    }

    private function get_price_range_display($min_price, $max_price)
    {
        if (SHOP_ID === 3) {
            return $min_price;
        }
        // return $min_price . ' - ' . $max_price;
        return $min_price;
    }
}
