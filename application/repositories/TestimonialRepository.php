<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TestimonialRepository {

    use UsesRestAPI;

    public static function testtimonialList() {
        // echo 1;die();
 
        $final_post_arr = array();
         $post_arr1=array("page"=>'', "page_size"=>'');
 
        $ApiUrl = '/webshop/testimonial_listing';
 
         $result = self::post_method($ApiUrl, $post_arr1);
         return $result;
     }
}

?>