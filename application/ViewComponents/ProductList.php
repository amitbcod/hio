<?php

/**
 * @property CI_Controller $ci
 */
class ProductList {
    private $ci;
    private $product_id;
    private $level_type;
    private $type;
	private $viewmode;
    private $list_array;
    private $current_category_id;
    private $search_term;

	const BADGE_COMINGSOON = 'product-badge-comingsoon';
	const BADGE_NEW = 'product-badge-new';
	const BADGE_SALE = 'product-badge-sale';
	const BADGE_HALLOWEEN = 'product-badge-halloween';
	const BADGE_BUNDLE = 'product-badge-bundle';

    public function __construct(){
        $this->ci =& get_instance();
    }

    public function productListData($prodData,$prod_image,$type,$viewmode='',$page_name='',$section_view=''){
       
        $this->type = $type;
		$this->viewmode = $viewmode ??'';
        $this->list_array = array('prod_image'=>$prod_image,'prod'=>$prodData,'type' => $this->type,'viewmode' => $this->viewmode,'page_name'=>$page_name);
        if(empty($this->list_array)) {
           return;
        }
        $this->current_category_id=$prodData->current_category_id ?? ' ';
        $this->search_term=$prodData->search_term ?? ' ';
		$this->list_array['product_display_price'] = $prodData->display_list_price(true);
		$this->list_array['product_badge'] = $this->getProductBadge();
        $this->list_array['product_url'] = $this->getProductUrl();
        $this->list_array['check'] = $section_view;
		if($page_name != ''){
			$this->ci->template->load('components/product_listing_other_template', $this->list_array);
        }
        // elseif($page_name == 'new_arrivals_prod'){
        //     $this->ci->template->load('components/newarrival_product_listing_page_item', $this->list_array);
        // }
        else{
			$this->ci->template->load('components/product_listing_page_item', $this->list_array);
		}

    }

	private function getProductBadge(){
		$prod = $this->list_array['prod'];
        

		if($prod->product_type === 'bundle'){
			return self::BADGE_BUNDLE;
		}

		// Check coming soon
		if(!(isset($prod->stock_status)) && isset($prod->coming_soon_flag) && $prod->coming_soon_flag == 1) {
			return self::BADGE_COMINGSOON;
		}

		// check new
		$days = (time() - $prod->launch_date) / (60 * 60 * 24);
		if($days <= 45){
			return self::BADGE_NEW;
		}
        // NM: Hack to avoid showing "SALE" for regular ZIN prices
		if(strpos($this->list_array['product_display_price'], '<s>') !== false && strpos($this->list_array['product_display_price'], '[ZIN]') === false){
			return self::BADGE_SALE;
		}

       


		return '';
	}

    private function getProductUrl(){
        switch($this->type){
            case 'PrelaunchListing':
                $link_type = 'prelaunch';
                break;
            case 'FeaturedListing':
                $link_type = 'featured';
                break;
			case 'TrendingListing':
				$link_type = 'trending';
				break;
            case 'NewArrivalListing':
                $link_type = 'new_arrival';
                break;
            case "SearchListing";
                $link_type = 'search&term='.$this->search_term;
                break;
			case "AllProductsList";
				$link_type = 'all';
				break;
            default:
                $link_type = 'category&categoryId='.($this->current_category_id ?? '');
        }

        return linkUrl('product-detail/' . $this->list_array['prod']->url_key . '?type='.$link_type);
    }
}
