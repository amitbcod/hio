<?php
/**
 * @property CI_Controller $ci
 */
class WishlistProducts
{
    private $ci;
    private $wishlist_product;

    public function __construct(){
        $this->ci =& get_instance();

        $lang_code='';
		if(!empty($this->ci->session->userdata('lcode')) && $this->ci->session->userdata('lis_default_language')==0){
			$lang_code=$this->ci->session->userdata('lcode');
		}

        $response = WishlistRepository::mywishlists([  
            'customer_id' => $this->ci->session->userdata('LoginID') ?? 0,
            'customer_type_id' => $this->ci->session->userdata('CustomerTypeID') ?? 1,
            'lang_code' => $lang_code
        ]);

        if (!empty($response) && isset($response) && $response->is_success=='true') {
            $this->wishlist_product = $response->myWishlist;
        }
    }

    public function render(){
        if(empty($this->wishlist_product)) {
           return;
        }

        
        $this->ci->template->load('components/wishlist_products', ['wishlistData' => $this->wishlist_product]);
    }

}
