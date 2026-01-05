<?php
class ContactusRepository
{
    use UsesRestAPI;
    public static function contact_us($postArr)
    {
        $final_post_arr = array();
        $final_post_arr = $postArr;

        $APIUrl = '/webshop/contact_us';
        $ContactusResponse = self::post_method($APIUrl, $final_post_arr);
        if (isset($ContactusResponse) && $ContactusResponse->is_success == 'true') {
            return $ContactusResponse;
        }
        return '';
    }

    public static function get_communication_lang_select($shopcode, $shop_id)
    {

        $post_arr = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $promo_APIUrl = '/webshop/get_communication_lang_select';

        $result = self::post_method($promo_APIUrl, $post_arr, 300);
        if (isset($result) && $result->is_success == 'true') {
            // $result=$banners;
            return $result;
        }
        return '';
    }
}
