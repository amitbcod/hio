<?php
defined('BASEPATH') or exit('No direct script access allowed');

class VatSession
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        // Load session if not loaded
        if (!isset($this->CI->session)) {
            $this->CI->load->library('session');
        }
    }

    public function setVatSession(): void
    {
        $shopcode = SHOPCODE ?? '';
        $shop_id = SHOP_ID ?? 0;

        $vat_percent_session = $this->CI->session->userdata('vat_percent');

        if (!empty($vat_percent_session)) {
            $this->CI->session->set_userdata('session_vat_flag', 1);
            return;
        }

        $FbcShopDetails = GlobalRepository::get_fbc_users_shop($shop_id)->result ?? null;

        if (empty($FbcShopDetails) || ($FbcShopDetails->vat_flag ?? 0) != 1) {
            $this->setIpCountry();
            $this->CI->session->set_userdata('session_vat_flag', 0);
            return;
        }

        $this->CI->session->set_userdata('session_vat_flag', 1);

        $current_country_code = ip_visitor_country();

        // Try VAT data based on visitor IP country
        $vat_res = CommonRepository::get_shop_vat_data($shopcode, $shop_id, $current_country_code);

        if ($this->setVatDetailsFromResponse($vat_res, $current_country_code)) {
            return;
        }

        // Fallback to shop default country
        $default_country_code = $FbcShopDetails->country_code ?? '';
        $vat_res = CommonRepository::get_shop_vat_data($shopcode, $shop_id, $default_country_code);

        if (!$this->setVatDetailsFromResponse($vat_res, $current_country_code)) {
            // No VAT data found, set default zero values
            $this->CI->session->set_userdata([
                'vat_id' => '',
                'ip_country' => $current_country_code,
                'vat_country' => '',
                'vat_percent' => 0,
                'is_eu_country' => '',
                'deduct_vat' => ''
            ]);
        }
    }

    private function setVatDetailsFromResponse($vat_res, string $current_country_code): bool
    {
        if (empty($vat_res->VatDetails) || !($vat_res->is_success ?? true)) {
            return false;
        }

        $VatDetails = $vat_res->VatDetails;

        $vat_no_session = $this->CI->session->userdata('vat_no_session') ?? '';
        $update_percentage = ($vat_no_session !== '' && ($VatDetails->deduct_vat ?? 0) == 1) ? 0 : ($VatDetails->vat_percentage ?? 0);

        $sessionArr = [
            'vat_id' => $VatDetails->id ?? '',
            'ip_country' => $current_country_code,
            'vat_country' => $VatDetails->country_code ?? '',
            'vat_percent' => $update_percentage,
            'is_eu_country' => $VatDetails->is_eu_country ?? '',
            'deduct_vat' => $VatDetails->deduct_vat ?? ''
        ];

        $this->CI->session->set_userdata($sessionArr);
        return true;
    }

    private function setIpCountry(): void
    {
        $ip_country = $this->CI->session->userdata('ip_country') ?? '';
        if (empty($ip_country)) {
            $current_country_code = ip_visitor_country();
            $this->CI->session->set_userdata('ip_country', $current_country_code);
        }
    }
}
