<?php
class ProductReviewRepository
{
    use UsesRestAPI;

    public static function get_product_reviews($reviewArr)
    {
        $final_post_arr = array();
        // $final_post_arr = array_merge($post_arr1, $reviewArr);

        $ApiUrl = '/webshop/get_product_reviews';
        $response = self::post_method($ApiUrl, $reviewArr, 450);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function add_product_review($reviewArr)
    {

        $ApiUrl = '/webshop/add_product_review';
        $response = self::post_method($ApiUrl, $reviewArr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function productReview_notification($reviewArr)
   {

      $ApiUrl = '/webshop/productReview_notification';
      $response = self::post_method($ApiUrl,$reviewArr);
      if(isset($response))
      {
        return $response;
      }
      return '';
   }
}
