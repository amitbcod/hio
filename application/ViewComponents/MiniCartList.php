<?php
/**
 * @property CI_Controller $ci
 */
class MiniCartList {
    private $ci;
    private $mini_cart_list;
    private $cart_count = 0;

    public function __construct(){
        $this->ci =& get_instance();

        $this->mini_cart_list = CartRepository::cart_listing($this->get_cc_post_arr());
        $this->cart_count = $this->mini_cart_list->cartData->cartCount ?? 0;
    }

    public function render(){
        $this->ci->template->load('components/mini_cart_list', ['cart_response' => $this->mini_cart_list, 'cart_count' => $this->cart_count]);
    }

    private function get_cc_post_arr(){
        $cc_post_arr = [];
        
        $cc_post_arr['session_id'] = $this->ci->session->userdata('sis_session_id');
        // $cc_post_arr['lang_code'] = $this->ci->session->userdata('lcode') ?? '' ;

        if ($this->ci->session->userdata('LoginID')) {
            $cc_post_arr['customer_id'] = $this->ci->session->userdata('LoginID');
        }
        if ($this->ci->session->userdata('QuoteId')) {
            $cc_post_arr['quote_id'] = $this->ci->session->userdata('QuoteId');   
        }
        return $cc_post_arr;
    }
}
