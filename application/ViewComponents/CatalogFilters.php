<?php
/**
 * @property CI_Controller $ci
 */
class CatalogFilters
{
    private $ci;
    private $catqalog_filter;
    private $color_access;
    private $search_term;
    private $filter_type;

    private $sizes = [
        'XXS' => 1,
        'XS' => 2,
        'S' => 3,
        'M' => 4,
        'L' => 5,
        'XL' => 6,
        'XXL' => 7,
        'XXXL' => 8,
        'XS/S' => 21,
        'S/M' => 22,
        'M/L' => 23,
        'L/XL' => 24,
        'XL/XXL' => 25,
    ];

    public function __construct($category_id='',$filter_type='',$search_term=''){
        $this->ci =& get_instance();
        $lang_code='';
        if(!empty($this->ci->session->userdata('lcode')) && $this->ci->session->userdata('lis_default_language')==0){
          $lang_code=$this->ci->session->userdata('lcode');
        }

        $this->catqalog_filter = ProductRepository::get_catalog_filters([
            'categoryid'=>$category_id ?? '',
            'filter_type'=>$filter_type ?? '',
            'search_term'=>$search_term ?? '',
            'options'=>'newest',
            'customer_type_id' => $this->ci->session->userdata('CustomerTypeID') ?? 1,
            'lang_code' => $lang_code
        ]);

        $this->sort_variants();

        $color_base_access='';
        $identifierColor='use_base_colors';
        $ApiResponseColor =  GlobalRepository::get_custom_variable($identifierColor);
        if ($ApiResponseColor->statusCode=='200'){
            $RowCVColor=$ApiResponseColor->custom_variable;
            $color_base_access=$RowCVColor->value;
        }
        $this->color_access = $color_base_access;
        $this->search_term = $search_term;
        $this->filter_type = $filter_type;

    }

    public function render(){

        if(empty($this->catqalog_filter)) {
           return;
        }
        $this->ci->template->load('components/catalog_filters', ['catalogFilter' => $this->catqalog_filter,'color_access'=> $this->color_access,'search_term' => $this->search_term,'filter_type'=> $this->filter_type]);
    }

    private function sort_variants(){
        if(!empty($this->catqalog_filter->productCatalogFilter->variant_listing)){
            foreach($this->catqalog_filter->productCatalogFilter->variant_listing as $variant => $variantOptions){
                $variantKey = explode('__', $variant)[0];
                if($variantKey === 'shoe_size'){
                    usort($variantOptions, function($sizeA, $sizeB){
                        return ((float) $sizeA->attr_options_name) <=> ((float) $sizeB->attr_options_name);
                    });
                    $this->catqalog_filter->productCatalogFilter->variant_listing->$variant = $variantOptions;
                }

                if($variantKey === 'size'){
                    usort($variantOptions, function($sizeA, $sizeB){
                        return ($this->sizes[$sizeA->attr_options_name] ?? 100) <=> ($this->sizes[$sizeB->attr_options_name] ?? 100);
                    });
                    $this->catqalog_filter->productCatalogFilter->variant_listing->$variant = $variantOptions;
                }
            }
        }

    }
}
