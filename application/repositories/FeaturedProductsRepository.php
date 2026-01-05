<?php
class FeaturedProductsRepository
{
    use UsesRestAPI;

    public static function featured_product_listing($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);
        $ApiUrl = '/webshop/featured_product_listing';
        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function newarrival_product_listing($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);
        $ApiUrl = '/webshop/newarrival_product_listing';
        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

}
