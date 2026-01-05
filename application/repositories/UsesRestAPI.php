<?php

trait UsesRestAPI
{
    private static $API = API_URL;

    private static function get_method($url, $cacheTTL = 0)
    {

        // if ($cacheTTL > 0 && function_exists('get_instance')) {
        //     $cacheKey = preg_replace("/[^a-z0-9.]/", "", strtolower($url));
        //     $ci = &get_instance();
        //     if (($result = $ci->cache->get($cacheKey)) !== false) {
        //         return json_decode($result);
        //     }
        // }

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, self::$API . $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, FALSE);

        $buffer = curl_exec($curl_handle);
        // if ($buffer === FALSE) {
        //     die("Curl failed: " . curL_error($curl_handle));
        // }
        curl_close($curl_handle);

        // if ($cacheTTL > 0 && function_exists('get_instance')) {
        //     $ci->cache->save($cacheKey, $buffer, $cacheTTL);
        // }

        return json_decode($buffer);
    }

    private static function post_method($url, $array, $cacheTTL = 0)
    {
        $curl_handle = curl_init();
        //  echo self::$API.$url;
        //  print_r($array);
        curl_setopt($curl_handle, CURLOPT_URL, self::$API . $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($curl_handle, CURLOPT_POSTFIELDS, urldecode(http_build_query($array)));
        if (is_array($array)) {
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, urldecode(http_build_query($array)));
        } else {
            // handle gracefully — either skip encoding or wrap it
            log_message('error', 'post_method expected array but got: ' . gettype($array) . ' in URL ' . $url);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $array);
        }

        $buffer = curl_exec($curl_handle);

        curl_close($curl_handle);
        return json_decode($buffer);
    }

    private static function post_method_with_json($url, $array)
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, self::$API . $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, json_encode($array));
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        return json_decode($buffer);
    }

    private static function delete($id)
    {
        $array = ["item_id" => $id];
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, self::$API);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($array));
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "DELETE");

        return curl_exec($curl_handle);
    }
}
