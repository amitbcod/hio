<?php
class CustomerRepository
{
    use UsesRestAPI;

    public static function get_customer_signup_otp($postArr)
    {
    	$final_post_arr = array();
        $final_post_arr = $postArr;
		$response = self::post_method('/webshop/get_customer_signup_otp', $final_post_arr);
		return $response ?? '';
	}

    public static function customer_get_personal_info($postArr)
    {
    	$final_post_arr = array();
        $final_post_arr = $postArr;

		$response = self::post_method('/webshop/customer_get_personal_info', $final_post_arr);
		return $response ?? '';
	}

	public static function customer_feedback($postArr)
    {
    	$final_post_arr = array();
        $final_post_arr = $postArr;

		$response = self::post_method('/webshop/customer_feedback', $final_post_arr);
		return $response ?? '';
	}

    public static function customer_email_exits($postArr)
    {	
    	$final_post_arr = array();
        $final_post_arr = $postArr;

		$response = self::post_method('/webshop/customer_email_exits', $final_post_arr);
		return $response ?? '';
	}

    public static function change_email($postArr)
    {
    	$final_post_arr=array();
        $final_post_arr = $postArr;

		$response = self::post_method('/webshop/change_email', $final_post_arr);

		return $response ?? '';
	}

    public static function customer_update_personal_info( $postArr)
    {
        // $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr =  $postArr;

		$response = self::post_method('/webshop/customer_update_personal_info', $final_post_arr);

		return $response ?? '';
	}

    public static function customer_address_add_edit($shopcode, $shop_id, $postArr)
    {
		$post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

		$response = self::post_method('/webshop/customer_address_add_edit', $final_post_arr);

		return $response ?? '';
	}

    public static function customer_address_setdefault($shopcode, $shop_id, $postArr)
    {
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);

		$response = self::post_method('/webshop/customer_address_setdefault', $final_post_arr);

		return $response ?? '';
	}

    public static function customer_address_delete($shopcode, $shop_id, $postArr)
    {
        $post_arr1=array("shopcode"=>$shopcode, "shopid"=>$shop_id);
        $final_post_arr = array_merge($post_arr1, $postArr);
		$response = self::post_method('/webshop/customer_address_delete', $final_post_arr);

		return $response ?? '';
	}
}
