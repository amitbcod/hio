<?php

/**
 * @property CI_Controller $CI;
 */
class CheckLiveStatus
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function handle()
    {
        $result = SettingsRepository::shop_livestatus();
        if ($this->isUnderMaintenance($result)) {
            echo $this->CI->load->view('under_construction', [], true);
            exit;
        }

        $this->loadShopDetails($result);
    }

    private function loadShopDetails($result): void
    {
        if (empty($result->ShopDetails)) {
            return;
        }

        $arr_obj = $result->ShopDetails;

        // Define constants safely
        defined('CURRENCY_TYPE') || define('CURRENCY_TYPE', $arr_obj->currency_symbol ?? '₹');
        defined('CURRENCY_CODE') || define('CURRENCY_CODE', $arr_obj->currency_code ?? 'INR');
        defined('COUNTRY_CODE') || define('COUNTRY_CODE', $arr_obj->country_code ?? 'IN');

        // Optional: set session values if needed
        // $this->CI->session->set_userdata([
        //     'currency_code_session' => $arr_obj->currency_code ?? 'INR',
        //     'currency_symbol' => $arr_obj->currency_symbol ?? '₹'
        // ]);

        // Determine site title
        $site_name = '';
        $result1 = CommonRepository::get_webshop_details();
        if (!empty($result1) && $result1->is_success === 'true') {
            $site_name = $result1->FbcWebShopDetails->site_name ?? '';
        }

        defined('SITE_TITLE') || define(
            'SITE_TITLE',
            $site_name ?: ($arr_obj->org_shop_name ?? 'My Shop')
        );
    }

    private function isUnderMaintenance($result): bool
    {
        if (!isset($result->is_success) || $result->is_success !== 'true') {
            return true;
        }

        if (empty($result->ShopDetails)) {
            return true;
        }

        $arr_obj = $result->ShopDetails;

        // Shop live check
        if (($arr_obj->website_live ?? 0) == 1) {
            return false;
        }

        // Test mode access
        if (($arr_obj->enable_test_mode ?? 0) == 1) {
            $current_ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $allowed_ips = explode(',', $arr_obj->test_mode_access_ips ?? '');
            if (in_array($current_ip, $allowed_ips)) {
                return false; // Allow access for test IPs
            }
        }

        return true; // Default: under maintenance
    }
}
