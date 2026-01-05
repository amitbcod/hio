<?php

/**

 * @property CI_Controller $ci

 */

class ProductDetails {
    private $ci;
    private $product_id;
    private $level_type;
    private $type;
    private $list_array;

    public function __construct(){
        $this->ci =& get_instance();
    }

    public function productDetailsPrice($productData,$type){
        $this->type = $type;
        $this->list_array = array('productData'=>$productData,'type' => $this->type);
        if(empty($this->list_array)) {
           return;
        }

        $this->ci->template->load('components/product_details_price', $this->list_array);

    }

    public function breadcrum($product_id, $type, $level_type=''){
        $this->product_id = $product_id;
        $this->level_type = $level_type;
        $this->type = $type;
        $this->list_array = '';
        $lang_code = $this->ci->session->userdata('lcode') ?? '' ;
        if($this->type=='main'){
            $this->category_list_main = ProductRepository::get_product_category_by_level($this->product_id, $this->level_type);
            $this->list_array=array('categoryList' => $this->category_list_main,'type' => $this->type);

        }elseif($this->type=='sub'){
            $this->category_list_main = ProductRepository::get_product_category_by_level($this->product_id, 0);
            $this->category_list_sub = ProductRepository::get_product_category_by_level($this->product_id, $this->level_type);
            $this->list_array=array('categoryList' => $this->category_list_main,'categoryListSub' => $this->category_list_sub,'type' => $this->type);
        }
        if(empty($this->list_array)) {
           return;
        }

        $this->ci->template->load('components/product_details_breadcrum', $this->list_array);
    }


    public function productStockVariant($productData,$CategoryIds=''){
        
        $this->CategoryIds = $CategoryIds;

		if(isset($productData->product_variants) && count($productData->product_variants) > 0 ){
			$productData->product_variants = $this->sortProductDataVariants($productData->product_variants);
		}

        $this->list_array = array('productData'=>$productData,'CategoryIds' => $this->CategoryIds);
        
        if(empty($this->list_array)) {
           return;
        }
        
        $this->ci->template->load('components/product_stock_variants', $this->list_array);

    }

    public function productSpecifications($productData){
        $this->productData = $productData;
        $this->list_array = array('productData'=>$this->productData);
        if(empty($this->list_array)) {
           return;
        }

        $this->ci->template->load('components/product_specifications', $this->list_array);

    }

    public function wishlist($product_id, $customer_id=''){
		if(empty($customer_id)){
			return;
		}

        $this->product_id = $product_id;
        $this->customer_id = $customer_id;
        $this->list_array = '';
        $this->wish_list = WishlistRepository::wishlist_getproduct($this->customer_id, $this->product_id);
        $this->list_array=array('wishlistData' => $this->wish_list,'product_id' => $this->product_id);
        if(empty($this->list_array)) {
           return;
        }

       $this->ci->template->load('components/product_wishlist', $this->list_array);
    }

    public function sortProductDataVariants($productVariants){

        foreach($productVariants as $productVariant){
            if($productVariant->variant_code === 'shoe_size'){
                usort($productVariant->variant_options,
                    function ($optionA, $optionB) {
						return floatval($optionA->attr_options_name) <=> floatval($optionB->attr_options_name);
                    }
                );
            }

        }
        return $productVariants;
    }

}
