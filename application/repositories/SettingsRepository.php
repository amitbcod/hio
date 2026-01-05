<?php

// shop3/application/repositories/SettingsRepository.php

class SettingsRepository
{
    use UsesRestAPI;

    public static function shop_livestatus()
    {
        // using a static variable allows calling the methods as many times as you want, without calling the API again.
        static $result;
        if (isset($result)) {
            return $result;
        }
        $result = self::get_method('/webshop/shop_livestatus');
        // echo "<pre>";
        // print_r($result);
        // exit;
        return $result;
    }
}
