<?php
/**
 * @property CI_Controller $ci
 */
class NewArrivalProducts
{
    
    private $ci;
    private $new_arrival_product;
    private $limit;
    private $display_type;

    public function __construct($limit,$display_type){
        $this->ci =& get_instance();
        $this->display_type = $display_type;

        $this->new_arrival_product = ProductRepository::get_new_arrivals([            
            'customer_type_id' => $this->ci->session->userdata('CustomerTypeID') ?? 1,
            'limit'=> $limit,
            'vat_percent_session' => $this->ci->session->userdata('vat_percent') ?? '',
            'lang_code' => $this->ci->session->userdata('lcode') ?? '',
            'customer_login_id' => $this->ci->session->userdata('LoginID') ?? ''
        ]);

        // print_r($this->new_arrival_product);exit();
    }

    public function render(){ 

        if(empty($this->new_arrival_product)) {
           return;
        }

        $this->ci->template->load('components/new_arrivals_product', ['new_arrival' => $this->new_arrival_product,'display_type' => $this->display_type]);
    }

    public function new_arrivals_products(){
        if(empty($this->new_arrival_product)) {
           return;
        }
 
        $this->ci->template->load('components/home_newarrivals_products', ['new_arrival' => $this->new_arrival_product]);
    }

}