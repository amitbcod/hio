<?php

class CheckoutRepository
{
    use UsesRestAPI;

    public static function save_eu_shippping_method($shopcode, $shop_id, $cartArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shop_id" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $cartArr);
        $save_eu_shipping_method_APIUrl = '/webshop/save_eu_shippping_method';
        $eu_shipping_res = self::post_method($save_eu_shipping_method_APIUrl, $final_post_arr);
        if (isset($eu_shipping_res) && $eu_shipping_res->is_success == 'true') {
            return $eu_shipping_res;
        }
        return '';
    }

    public static function get_shipping_charges($shopcode, $shop_id, $PostArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $PostArr);
        $get_shipping_charges_method_APIUrl = '/webshop/get_shipping_charges';
        $eu_shipping_res = self::post_method($get_shipping_charges_method_APIUrl, $final_post_arr);

        return $eu_shipping_res;
    }

    public static function payment_methods_listing($cc_post_arr)
    {
        $final_post_arr = array();
        $final_post_arr = $cc_post_arr;

        $cart_APIUrl = '/webshop/payment_methods_listing';

        $cart = self::post_method($cart_APIUrl, $final_post_arr, 600);
        if (isset($cart) && $cart->is_success == 'true') {
            return $cart;
        }
        return '';
    }

    public static function update_quote_customer_id($cc_post_arr)
    {
        $final_post_arr = array();
        $final_post_arr = $cc_post_arr;

        $cartApiUrl = '/webshop/update_quote_customer_id';

        $cart = self::post_method($cartApiUrl, $final_post_arr);
        if (isset($cart) && $cart->is_success == 'true') {
            return $cart;
        }
        return '';
    }

    public static function check_quote_item_available($cc_post_arr)
    {
        $check_quote_ApiUrl = '/webshop/check_quote_item_available';

        $check_quote = self::post_method($check_quote_ApiUrl, $cc_post_arr);
        if (isset($check_quote) && $check_quote->is_success == 'true') {
            return $check_quote;
        }
        return '';
    }

    public static function place_order($cc_post_arr)
    {
        $final_post_arr = array();
        $place_order_ApiUrl = '/webshop/place_order';
        $place_order = self::post_method($place_order_ApiUrl, $cc_post_arr);
        if (isset($place_order) && $place_order->is_success == 'true') {
            return $place_order;
        }
        return '';
    }

    public static function insert_razorpay_data($order_id, $cc_post_arr)
    {
        $opayapi = '/webshop/insert_razorpay_data/' . $order_id;
        $responseRazorpay = self::post_method($opayapi, $cc_post_arr);
        if (isset($responseRazorpay) && $responseRazorpay->is_success == 'true') {
            return $responseRazorpay;
        }
        return '';
    }

    public static function update_order_payment_status_info($cc_post_arr)
    {
        $final_post_arr = array();
        $update_order_ApiUrl = '/webshop/update_order_payment_status_info';
        $update_order = self::post_method($update_order_ApiUrl, $cc_post_arr);
        if (isset($update_order) && $update_order->is_success == 'true') {
            return $update_order;
        }
        return '';
    }

    public static function save_quote_address($cc_post_arr)
    {
        $save_quaote_ApiUrl = '/webshop/save_quote_address';
        $save_quaote = self::post_method($save_quaote_ApiUrl, $cc_post_arr);
        if (isset($save_quaote) && $save_quaote->is_success == 'true') {
            return $save_quaote;
        }
        return '';
    }

    public static function update_order_status($cc_post_arr)
    {
        $update_order_ApiUrl = '/webshop/update_order_status';
        $update_order = self::post_method($update_order_ApiUrl, $cc_post_arr);
        if (isset($update_order) && $update_order->is_success == 'true') {
            return $update_order;
        }
        return '';
    }

    public static function set_checkout_payment_method($cc_post_arr)
    {
        //      echo "<pre>";
        // print_r($cc_post_arr);die;
        $set_checkout_ApiUrl = '/webshop/set_checkout_payment_method';
        $set_checkout = self::post_method($set_checkout_ApiUrl, $cc_post_arr);
        if (isset($set_checkout) && $set_checkout->is_success == 'true') {
            return $set_checkout;
        }
        return '';
    }

    public static function send_cod_otp($cc_post_arr)
    {
        $send_otp_ApiUrl = '/webshop/send_cod_otp';
        $send_otp = self::post_method($send_otp_ApiUrl, $cc_post_arr);
        if (isset($send_otp) && $send_otp->is_success == 'true') {
            return $send_otp;
        }
        return '';
    }

    public static function send_order_confirmation_email($cc_post_arr)
    {

        $send_order_ApiUrl = '/webshop/send_order_confirmation_email';
        $send_order = self::post_method($send_order_ApiUrl, $cc_post_arr);
        if (isset($send_order) && isset($send_order->is_success) && $send_order->is_success == 'true') {
            return $send_order;
        }
        return '';
    }

    public static function generate_b2b_order_for_webshop($cc_post_arr)
    {

        $generate_b2b_order_ApiUrl = '/webshop/generate_b2b_order_for_webshop';
        $generate_b2b_order = self::post_method($generate_b2b_order_ApiUrl, $cc_post_arr);

        if (isset($generate_b2b_order) && $generate_b2b_order->is_success == 'true') {
            return $generate_b2b_order;
        }
        return '';
    }

    public static function remove_quote($cc_post_arr)
    {
        $remove_quote_ApiUrl = '/webshop/remove_quote';
        $remove_quote_order = self::post_method($remove_quote_ApiUrl, $cc_post_arr);
        if (isset($remove_quote_order) && $remove_quote_order->is_success == 'true') {
            return $remove_quote_order;
        }
        return 'false';
    }

    public static function remove_otp($shopcode, $shop_id, $cc_post_arr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $cc_post_arr);
        $remove_otp_ApiUrl = '/webshop/remove_otp';
        $remove_otp_order = self::post_method($remove_otp_ApiUrl, $final_post_arr);
        if (isset($remove_otp_order) && $remove_otp_order->is_success == 'true') {
            return $remove_otp_order;
        }
        return '';
    }

    public static function set_checkout_method($shopcode, $shop_id, $cc_post_arr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $cc_post_arr);
        $set_checkout_ApiUrl = '/webshop/set_checkout_method';
        $set_checkout = self::post_method($set_checkout_ApiUrl, $final_post_arr);
        if (isset($set_checkout) && $set_checkout->is_success == 'true') {
            return $set_checkout;
        }
        return '';
    }

    public static function insert_payment_method($shopcode, $shop_id, $cartArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shop_id" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $cartArr);
        $insert_payment_method_APIUrl = '/webshop/insert_payment_method';
        $payment_method_res = self::post_method($insert_payment_method_APIUrl, $final_post_arr);
        if (isset($payment_method_res) && $payment_method_res->is_success == 'true') {
            return $payment_method_res;
        }
        return '';
    }

    public static function insert_old_payment_method($shopcode, $shop_id, $cartArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shop_id" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $cartArr);
        $insert_payment_method_APIUrl = '/webshop/insert_sales_order_payment_history';
        $payment_method_res = self::post_method($insert_payment_method_APIUrl, $final_post_arr);
        if (isset($payment_method_res) && $payment_method_res->is_success == 'true') {
            return $payment_method_res;
        }
        return '';
    }
    public static function get_price_split_for_publisher($cc_post_arr)
    {
        $final_post_arr = array();
        $final_post_arr = $cc_post_arr;

        $cart_APIUrl = '/webshop/get_price_split_for_publisher';

        $cart = self::post_method($cart_APIUrl, $final_post_arr, 600);
        // echo "<pre>";
        // print_r($cart);
        // die();
        if (isset($cart) && $cart->is_success == 'true') {
            return $cart;
        }
        return '';
    }

    public static function update_payment_transaction_id($cc_post_arr)
    {

        $set_checkout_ApiUrl = '/webshop/update_transaction_id';
        $set_checkout = self::post_method($set_checkout_ApiUrl, $cc_post_arr);
        if (isset($set_checkout) && $set_checkout->is_success == 'true') {
            return $set_checkout;
        }
        return '';
    }

    public static function get_customer_address($cc_post_arr)
    {
        $final_post_arr = array();
        $place_order_ApiUrl = '/webshop/get_customer_address';
        $place_order = self::post_method($place_order_ApiUrl, $cc_post_arr);
        if (isset($place_order) && $place_order->is_success == 'true') {
            return $place_order;
        }
        return '';
    }

    public static function abundantCartDetails($quote_id)
    {
        $abundantCartDetails_ApiUrl = '/webshop/abundantCartDetails';
        $abundantCartDetails = self::post_method($abundantCartDetails_ApiUrl, ['quote_id' => $quote_id]);

        // echo "<pre>";
        // print_r($abundantCartDetails);
        // die;

        if (isset($abundantCartDetails) && $abundantCartDetails->is_success == 'true') {
            return $abundantCartDetails;
        }
        return '';
    }
    public static function get_order_payment($cc_post_arr)
    {
        // echo "hi";exit;
        $final_post_arr = array();
        $final_post_arr = $cc_post_arr;
        $place_order_ApiUrl = '/webshop/get_order_payment';
        // print_r($final_post_arr);die;
        $place_order = self::post_method($place_order_ApiUrl, $final_post_arr);
        // print_r($place_order);die;
        if (isset($place_order) && $place_order->is_success == 'true') {
            return $place_order;
        }
        return '';
    }
}
