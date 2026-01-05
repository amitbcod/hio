<?php

/**
 * @property CI_Controller $CI
 */
class Cacher {

    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance(); //grab an instance of CI
        $this->initiate_cache();
    }

    public function initiate_cache()
    {
        $this->CI->load->driver('cache', array('adapter' => defined('CACHE_ADAPTER') ? CACHE_ADAPTER : 'apc', 'backup' => 'file', 'key_prefix' => 'shop' . SHOP_ID . '_'));
    }
}
