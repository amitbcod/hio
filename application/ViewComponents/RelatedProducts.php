<?php

/**
 * @property CI_Controller $ci
 */
class RelatedProducts
{
    private $ci;
    private $related_product;
    private $category_id;
    private $product_url;

    public function __construct($product_url,$category_id=''){

        $this->ci =& get_instance();
        $this->category_id = $category_id;
        $this->product_url = $product_url;

        $LoginID = $this->ci->session->userdata('LoginID');
        $customer_type_id = $this->ci->session->userdata('CustomerTypeID') ?? 1;
        $lang_code = $this->ci->session->userdata('lcode') ?? '';
        if (isset($LoginID)) {
            $productArr1 = array('categoryid'=>$category_id,'options'=>'newest','page'=>0,'page_size'=>15,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$this->ci->session->userdata('vat_percent'),'customer_login_id'=>$LoginID,'lang_code'=>$lang_code);
        } else {
            $productArr1 = array('categoryid'=>$category_id,'options'=>'newest','page'=>0,'page_size'=>15,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$this->ci->session->userdata('vat_percent'),'customer_login_id'=>$LoginID,'lang_code'=>$lang_code)                                                                                                                                                                                             ;
        }

        $this->related_product = ProductRepository::product_listing(SHOPCODE, SHOP_ID, $productArr1);
    }

    public function render(){
        if(empty($this->related_product)) {
           return;
        }

        $this->ci->template->load('components/related_products', ['related_product'=>$this->related_product,'category_id'=>$this->category_id,'current_prod_url'=>$this->product_url]);

    }



}
