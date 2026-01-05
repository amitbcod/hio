<?php

/**
 * @property CI_Controller $ci
 */
class TrendingProducts
{
    private $ci;
    private $trending_product;

    public function __construct($limit=""){
        $this->ci =& get_instance();

        $limit_var = $limit ?? 25 ;
        $this->trending_product = ProductRepository::get_featured_products(SHOPCODE, SHOP_ID, [
            'identifier' => 'trending',
            'customer_type_id' => $this->ci->session->userdata('CustomerTypeID') ?? 1,
            'limit'=> $limit_var,
            'vat_percent_session' => $this->ci->session->userdata('vat_percent') ?? '',
            'lang_code' => $this->ci->session->userdata('lcode') ?? ''
        ]);

    }

    public function render(){
        if(empty($this->trending_product)) {
           return;
        }

        $this->ci->template->load('components/trending_products', ['trending_product'=>$this->trending_product]);

    }

}
