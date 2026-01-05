<?php
class PreLaunchProductsRepository
{
    use UsesRestAPI;

    public static function prelauch_product_listing($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/prelauch_product_listing';

        $response = self::post_method($ApiUrl, $final_post_arr, 3000);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function prelauch_product_listing_all($shopcode, $shop_id, $customer_type_id)
    {
        $post_arr=array("shopcode"=>$shopcode, "shopid"=>$shop_id,"customer_type_id"=>$customer_type_id);

        $ApiUrl = '/webshop/prelauch_product_listing_all';

        $response = self::post_method($ApiUrl, $post_arr, 3000);
        if (isset($response)) {
            return $response;
        }
        return '';
    }
}
