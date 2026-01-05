<?php
class SearchRepository
{
    use UsesRestAPI;

    public static function save_search_term($shopcode, $shop_id, $searchTermArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $searchTermArr);

        $ApiUrl = '/webshop/save_search_term';

        $saveSearch = self::post_method($ApiUrl, $final_post_arr);
        if (isset($saveSearch)) {
            return $saveSearch;
        }
        return '';
    }

    public static function get_blog_nextpre_blog($blog_id)
    {
        $result = self::get_method('/webshop/get_blog_nextpre_blog/' . $blog_id);
        return $result;
    }


    public static function get_search_terms($search_term)
    {
        $search_term = urlencode($search_term);
        $result = self::get_method('/webshop/get_search_terms/' . $search_term, 3000);
        return $result;
    }

    public static function get_search_terms_post($searchTermArr)
    {
        $final_post_arr = array();
        $post_arr1 = array("search_term" => urlencode($searchTermArr));
        // $final_post_arr = array_merge($post_arr1, $searchTermArr);

        $ApiUrl = '/webshop/get_search_terms_post';
        // print_r($post_arr1);
        $saveSearch = self::post_method($ApiUrl, $post_arr1);
        // print_r($saveSearch);
        if (isset($saveSearch)) {
            return $saveSearch;
        }
        return '';
    }

    public static function get_prodcut_nextpre_products_Category($product_id, $categoryID)
    {
        $result = self::get_method('/webshop/get_prodcut_nextpre_products_Category/' . $product_id . '/' . $categoryID, 940);
        return $result;
    }

    public static function get_prodcut_nextpre_products($product_id)
    {

        $result = self::get_method('/webshop/get_prodcut_nextpre_products/' . $product_id, 950);
        return $result;
    }

    public static function get_prodcut_nextpre_products_newarrival($shopcode, $shop_id, $product_id, $customer_type_id, $badge = '')
    {
        if ($badge != '') {
            $result = self::get_method('/webshop/get_product_nextpre_products_newarrivals/' . $shopcode . '/' . $shop_id . '/' . $product_id . '/' . $customer_type_id . '/' . $badge, 950);
        } else {
            $result = self::get_method('/webshop/get_product_nextpre_products_newarrivals/' . $shopcode . '/' . $shop_id . '/' . $product_id . '/' . $customer_type_id, 950);
        }
        return $result;
    }
}
