<?php
class WishlistRepository
{
    use UsesRestAPI;

    public static function wishlist_getproduct($customer_id, $product_id)
    {
        $result = self::get_method('/webshop/wishlist_getproduct/'.$customer_id.'/'.$product_id);
        return $result;
    }

    public static function addtowishlist($postArr)
    {
        $ApiUrl = '/webshop/addtowishlist';
        $response = self::post_method($ApiUrl, $postArr,);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function mywishlists($postArr)
    {
        $final_post_arr = array();
        $final_post_arr = $postArr;
        $ApiUrl = '/webshop/mywishlists';
        $response = self::post_method($ApiUrl, $final_post_arr);
        if (isset($response)) {
            return $response;
        }
        return '';
    }

    public static function wishlist_deleteproduct($wishlist_id)
    {
        $result = self::get_method('/webshop/wishlist_deleteproduct/'.$wishlist_id);
        return $result;
    }
}
