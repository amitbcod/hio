<?php
class SpecialFeaturesRepository
{
    use UsesRestAPI;

    public static function get_product_data($shopcode, $postArr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/get_product_data';

        $response = self::post_method($ApiUrl, $final_post_arr, 1000);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function catlog_builder_delete($shopcode, $postArr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/catlog_builder_delete';

        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function get_shop_categories_new($shopcode, $shop_id, $postArr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode,"shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

        $ApiUrl = '/webshop/get_shop_categories_new';

        $response = self::post_method($ApiUrl, $final_post_arr, 1010);
        if (isset($response)) {
            return $response;
        }
        return '';
    }
}
