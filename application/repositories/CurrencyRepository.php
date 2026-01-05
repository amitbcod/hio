<?php
class CurrencyRepository
{
    use UsesRestAPI;
    public static function getCurrencyList($shopcode, $shop_id)
    {
        $post_arr=array("shopcode"=>$shopcode, "shop_id"=>$shop_id);
        $currenyList_APIUrl = '/webshop/getCurrencyList';
        $result = self::post_method($currenyList_APIUrl, $post_arr, 660);

        return $result;
    }


    public static function getCurrencyData($post_arr)
    {
        $final_post_arr = array();
        $final_post_arr = $post_arr;

        $currenyList_APIUrl = '/webshop/getCurrencyById';
        $result = self::post_method($currenyList_APIUrl, $final_post_arr, 670);

        return $result;
    }

    public static function updateQuoteCurrenyData($shopcode, $shop_id, $post_arr)
    {
        $final_post_arr = array();
        $post_arr1=array("shopcode"=>$shopcode, "shop_id"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $post_arr);

        $updatecurrenyQuote_APIUrl = '/webshop/updateQuoteCurrenyData';
        $result = self::post_method($updatecurrenyQuote_APIUrl, $final_post_arr);

        return $result;
    }
}
