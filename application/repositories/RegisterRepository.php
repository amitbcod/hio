<?php
class RegisterRepository
{
    use UsesRestAPI;

    public static function register($postArr)
    {
        $final_post_arr = array();
        $final_post_arr =  $postArr;
        $ApiUrl = '/webshop/register';

        $RegisterResponse = self::post_method($ApiUrl, $final_post_arr);
        if (isset($RegisterResponse)) {
            return $RegisterResponse;
        }
        return '';
    }
}
