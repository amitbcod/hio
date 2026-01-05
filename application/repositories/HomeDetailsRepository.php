<?php
// shop3/application/repositories/ProductRepository.php

class HomeDetailsRepository
{
    use UsesRestAPI;


     public static function get_subscription_variants($post_arr)
    {
        $final_post_arr = array();
        $final_post_arr = $post_arr;
        $APIUrl = '/webshop/get_subscription_variants';
        $banners = self::post_method($APIUrl, $final_post_arr);

        if (isset($banners) && $banners->is_success == 'true') {
            // $result=$banners;
            return $banners;
        }
        return '';
    }



    public static function get_banners($post_arr)
    {
        $final_post_arr = array();
        $final_post_arr = $post_arr;
        $APIUrl = '/webshop/get_banners';
        $banners = self::post_method($APIUrl, $final_post_arr);

        if (isset($banners) && $banners->is_success == 'true') {
            // $result=$banners;
            return $banners;
        }
        return '';
    }

     public static function get_promo_text_banners($post_arr)
    {  
        $final_post_arr = array();
        $final_post_arr = $post_arr;
        $promo_APIUrl = '/webshop/get_promo_text_banners';
        $result = self::post_method($promo_APIUrl, $final_post_arr);
        if (isset($result) && $result->is_success == 'true') {
            // $result=$banners;
            return $result;
        }
        return '';



    }

    public static function get_static_block($identifier, $lang_code='')
    {
        if($lang_code!=''){
          $result = self::get_method('/webshop/get_static_block/'.$identifier.'/'.$lang_code);
        }else{
          $result = self::get_method('/webshop/get_static_block/'.$identifier);
         }
      
        return $result;
    }

    public static function get_website_texts()
    {
        $result = self::get_method('/webshop/get_website_texts/');
        return $result;
    }

    public static function get_menus($post_arr)
    {
        $final_post_arr = array();
        $final_post_arr =  $post_arr;
        $menus_APIUrl = '/webshop/get_menus';

        $result = self::post_method($menus_APIUrl, $final_post_arr);
        if (isset($result) && $result->is_success == 'true') {
            return $result;
        }
        return '';
    }

    public static function newsletter_subscribe($email)
    {
        $APIUrl = '/webshop/newsletter_subscribe';
        $post_arr = [
         'email' => $email
      ];

        $result = self::post_method($APIUrl, $post_arr);
        if (isset($result)) {
            // $result=$banners;
            return $result;
        }
        return '';
    }
}
