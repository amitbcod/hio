<?php

class OauthRepository
{
    use UsesRestAPI;

    public static function attempt_login($code, $redirect_uri)
    {
        $response = self::post_method('/oauth/zumba/attempt_login', [
            "shopcode" => SHOPCODE,
            "shopid" => SHOP_ID,
            "code" => $code,
            "redirect_uri" => $redirect_uri,
        ]);

        return $response ?? '';
    }

    public static function confirm_user_email($email, $instructor_id)
    {
        $response = self::post_method('/oauth/zumba/confirm_user_email', [
            "shopcode" => SHOPCODE,
            "shopid" => SHOP_ID,
            "email" => $email,
            "instructor_id" => $instructor_id,
        ]);

        return $response ?? '';
    }
}
