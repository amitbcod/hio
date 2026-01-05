<?php
class CmsRepository
{
    use UsesRestAPI;

    public static function get_cms_page($identifier)
    {
		return self::get_method('/webshop/get_cms_page/'.$identifier);

    }

	public static function check_validity_coupon_code($shopcode,$shop_id,$post_arr)
	{
		$final_post_arr = array();
		$post_arr1=array("shopcode"=>$shopcode, "shop_id"=>$shop_id);   
		$final_post_arr = array_merge($post_arr1,$post_arr);
		$APIUrl = '/webshop/check_validity_coupon_code';
		$table_data = self::post_method($APIUrl,$final_post_arr);
		if(isset($table_data) || $table_data->is_success == 'true' || $table_data->is_success == 'false' ){
			return $table_data;
		}
		return '';
	}
}
