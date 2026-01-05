<?php
class LoginRepository
{
    use UsesRestAPI;

    public static function login($postArr)
    {
        $final_post_arr = array();
        $final_post_arr = $postArr;
        $ApiUrl = '/webshop/login';

        $loginResponse = self::post_method($ApiUrl, $final_post_arr);
        if (isset($loginResponse)) {
            return $loginResponse;
        }
        return '';
    }

    public static function customer_signup_otp($postArr)
    {
        $final_post_arr = array();
        $final_post_arr = $postArr;
        $ApiUrl = '/webshop/customer_signup_otp';
        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function forgot_password($postArr)
    {
        $final_post_arr = array();
        $final_post_arr = $postArr;

        $ApiUrl = '/webshop/forgot_password';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function reset_password($postArr)
    {
        $final_post_arr = array();
        $final_post_arr = $postArr;

        $ApiUrl = '/webshop/reset_password';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function merchant_forgot_password($postArr)
    {
        $final_post_arr = array();
        $final_post_arr = $postArr;

        $ApiUrl = '/webshop/merchant_forgot_password';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

     public static function merchant_reset_password($postArr)
    {
        $final_post_arr = array();
        $final_post_arr = $postArr;

        $ApiUrl = '/webshop/merchant_reset_password';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function autoLoginFromFBCAdmin($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/autoLoginFromFBCAdmin';

        $loginResponse = self::post_method($ApiUrl, $final_post_arr);
        if (isset($loginResponse)) {
            return $loginResponse;
        }
        return '';
    }

    public static function loginWithAuthToken($shopcode, $shop_id, $auth_token)
    {

        $ci = &get_instance();

        $loginPostArr = [
            'quote_id' => '',
            'vat_percent_session' => (($ci->session->userdata('vat_percent')) ? $ci->session->userdata('vat_percent') : ''),
            'currency_name' => (($ci->session->userdata('currency_name')) ? $ci->session->userdata('currency_name') : ''),
            'currency_code_session' => (($ci->session->userdata('currency_code_session')) ? $ci->session->userdata('currency_code_session') : ''),
            'currency_conversion_rate' => (($ci->session->userdata('currency_conversion_rate')) ? $ci->session->userdata('currency_conversion_rate') : ''),
            'currency_symbol' => (($ci->session->userdata('currency_symbol')) ? $ci->session->userdata('currency_symbol') : ''),
            'default_currency_flag' => (($ci->session->has_userdata('default_currency_flag')) ? $ci->session->userdata('default_currency_flag') : ''),
            'shopcode' => $shopcode,
            'shopid' => $shop_id,
            'auth_token' => $auth_token
        ];


        $loginResponse = self::post_method(
            '/webshop/loginWithAuthToken',
            $loginPostArr
        );

        if (isset($loginResponse)) {
            return $loginResponse;
        }

        return '';
    }


    public static function update_quote_customer_id($postArr)
    {
        $final_post_arr = array();
        $final_post_arr = $postArr;
        $ApiUrl = '/webshop/update_quote_customer_id';
        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }
}
