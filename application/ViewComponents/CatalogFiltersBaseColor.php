<?php
/**

 * @property CI_Controller $ci

 */
class CatalogFiltersBaseColor {
    private $ci;
    private $base_color_list;
    private $category_id;
    private $variant_id;

    public function __construct($category_id, $variant_id='', $color_filtertype='',$search_term=''){
        $this->ci =& get_instance();
        $this->category_id = $category_id ?? '';
        $this->variant_id = $variant_id ?? '';
        $this->color_filtertype = $color_filtertype ?? '';
        $lang_code = $this->ci->session->userdata('lcode') ?? '' ;
        $CustomerTypeID = $this->ci->session->userdata('CustomerTypeID') ?? 1;
        $ApiResponseBaseColor = ProductRepository::base_color_data(SHOPCODE, SHOP_ID, [
                            'categoryid'=>$this->category_id,
                            'variant_id'=>$this->variant_id,
                            'color_filtertype' =>$this->color_filtertype,
                            'search_term'=>$search_term ?? '',
                            'customer_type_id' =>$CustomerTypeID
                        ]);
        if(isset($ApiResponseBaseColor) && isset($ApiResponseBaseColor->is_success) && $ApiResponseBaseColor->is_success=='true'){
            $this->base_color_list = $ApiResponseBaseColor->BaseColorList;
        }
    }

    public function render(){ 
        if(empty($this->base_color_list)) {
           return;
        }
        return $this->base_color_list;
    }
}
