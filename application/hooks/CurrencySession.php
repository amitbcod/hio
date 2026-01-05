<?php

class CurrencySession
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();

        if (!isset($this->CI->session)) {  //Check if session lib is loaded or not
            $this->CI->load->library('session');  //If not loaded, then load it here
        }
    }

   public function setCurrencySession()
    {
        if (!empty($this->CI->session->userdata('currency_code_session'))) {
            return;
        }

        $shop_details = GlobalRepository::get_fbc_users_shop();

        $multi_currency_flag = 0;
        if (!empty($shop_details) && !empty($shop_details->result)) {
            $multi_currency_flag = $shop_details->result->multi_currency_flag;
        }

        $this->CI->session->set_userdata('multi_currency_flag', $multi_currency_flag);

        if ($multi_currency_flag != 1) {
            return;
        }

        if (!empty($_COOKIE['site_currency_id']) && $this->load_currency($_COOKIE['site_currency_id'])) {
            return;
        }

        if (defined('SHOP_ID') && SHOP_ID === 1 && $this->load_currency_by_country_ip()) {
            return;
        }

        $this->load_default_currency();
    }

    private function load_currency($currency_id, $currency_res = null)
    {
        if ($currency_res === null) {
            $currency_res = CurrencyRepository::getCurrencyData(['currency_id' => $currency_id]);
        }


        if (isset($currency_res) && $currency_res->statusCode == '200') {
            $currencydata = $currency_res->currencydata;

            $this->CI->session->set_userdata([
                'default_currency_flag' => $currencydata->is_default_currency,
                'currency_name' => $currencydata->name,
                'currency_code_session' => $currencydata->code,
                'currency_conversion_rate' => $currencydata->conversion_rate,
                'currency_symbol' => $currencydata->symbol
            ]);
            return true;
        }

        return false;
    }

    private function load_default_currency()
    {
        $currency_res = CommonRepository::get_default_currency(SHOPCODE);

        return $this->load_currency(null, $currency_res);
    }


    private function load_currency_by_country_ip()
    {
        $current_country_code = ip_visitor_country();

        $currency_id = 1;

        switch ($current_country_code) {
            case 'GB':
            case 'UK':
                $currency_id = 2;
                break;
            case 'SE':
                $currency_id = 6;
                break;
            case 'DK':
                $currency_id = 4;
                break;
            case 'NO':
                $currency_id = 5;
                break;
            case 'CH':
                $currency_id = 7;
                break;
        }

        return $this->load_currency($currency_id);
    }
}
