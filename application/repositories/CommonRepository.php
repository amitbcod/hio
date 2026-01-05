<?php

class CommonRepository
{
    use UsesRestAPI;

    public static function get_webshop_details()
    {
        static $result;
        if (isset($result)) {
            return $result;
        }

        $result = self::get_method('/webshop/get_webshop_details');
        return $result;
    }

    public static function get_default_currency($shop_id)
    {
        return self::post_method('/webshop/get_default_currency', ['shop_id' => $shop_id]);
    }

    public static function get_default_language($shopcode, $shop_id)
    {
        return self::post_method(
            '/webshop/get_default_language',
            ['shopcode' => $shopcode, 'shop_id' => $shop_id],
            610
        );
    }

    public static function get_shop_vat_data($shopcode, $shop_id, $country_code)
    {
        static $result = [];
        if (isset($result[$country_code])) {
            return $result[$country_code];
        }

        $result[$country_code] =  self::post_method(
            '/webshop/get_shop_vat_data',
            ['shopcode' => $shopcode, 'shop_id' => $shop_id, 'country_code' => $country_code],
            620
        );

        return $result[$country_code];
    }

    public static function get_table_data($post_arr, $cacheTTL = 0)
    {
        $final_post_arr = array();
        $final_post_arr =  $post_arr;

        $table_data = self::post_method('/webshop/get_table_data', $final_post_arr, $cacheTTL);
        if (isset($table_data) && $table_data->is_success == 'true') {
            return $table_data;
        }
        return '';
    }

    public static function basic_product_detail($product_id)
    {
        $post_arr = array("product_id" => $product_id);
        $response = self::post_method('/webshop/basic_product_detail', $post_arr, 640);
        return $response ?? '';
    }

    /*multi language*/
    public static function get_multi_language_data($shopcode, $shop_id, $post_arr)
    {
        $post_arr1 = array("shopcode" => $shopcode, "shopid" => $shop_id);
        $final_post_arr = array_merge($post_arr1, $post_arr);

        $table_data = self::post_method('/webshop/get_table_data', $final_post_arr, 640);
        if (isset($table_data) && $table_data->is_success == 'true') {
            return $table_data;
        }
        return '';
    }

    public static function get_all_multi_language_data()
    {
        $where = 'status = ? and remove_flag = ?';
        $params = array(1, 0);

        $post_arr1 = ["shopcode" => SHOPCODE, "shopid" => SHOP_ID, 'table_name' => 'multi_languages', 'database_flag' => 'own', 'where' => $where, 'params' => $params];
        $table_data = self::post_method('/webshop/get_table_data', $post_arr1, 650);

        if (isset($table_data) && $table_data->is_success == 'true') {
            return $table_data->tableData;
        }
        return '';
    }
    /*end multi language*/

    public static function add_email_notified($post_arr)
    {
        $table_data = self::post_method('/webshop/add_email_notified', $post_arr);

        if (isset($table_data) && $table_data->is_success == 'true') {
            return $table_data;
        }
        return '';
    }

    public static function get_blog_listing($post_arr)
    {
        // echo "<pre>";print_r($post_arr);die;
        $table_data = self::post_method('/webshop/get_blog_listing', $post_arr);
        if (isset($table_data) && $table_data->is_success == 'true') {
            return $table_data;
        }
        return '';
    }
    public static function blog_detail($post_arr)
    {
        $table_data = self::post_method('/webshop/blog_detail', $post_arr);
        // print_r($table_data);
        if (isset($table_data) && $table_data->is_success == 'true') {
            return $table_data;
        }
        return '';
    }

    public static function UpdateQuoteToCustomer($post_arr)
    {
        //    print_R($post_arr);

        $table_data = self::post_method('/webshop/update_quote_customer_id', $post_arr);
        // print_R($table_data);die();
        if (isset($table_data) && $table_data->is_success == 'true') {
            return $table_data;
        }
        return '';
    }
}
