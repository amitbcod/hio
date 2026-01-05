<?php

class ProductPresenter extends BasePresenter
{
    public function product_image($type = 'thumb'){
        if($type === 'thumb' && SHOP_ID === 1 && defined('IMGIX_URL')) {
            return $this->imgix_url($type);
        }

        $path = ['thumb' => PRODUCT_THUMB_IMG][$type] ?? PRODUCT_THUMB_IMG;

        if (!empty($this->base_image)) {
            return $path.$this->base_image;
        }

        return PRODUCT_DEFAULT_IMG;
    }

    public function display_list_price($return_html = false){
        return (new DisplayPricePresenter)($this, $return_html);
    }

    public function imgix_url($type){
        return IMGIX_URL . '/products/original/' . $this->base_image . '?auto=format&h=450&w=450&fit=fill&fill=blur';
    }

    public function display_product_badge(){
        //return (new DisplayProductBadge)($this->launch_date);
        $todayDate=date('Y-m-d');
        $startDate=$this->launch_date;
        $date=date('d-m-Y',$startDate);
        $days = (strtotime($todayDate) - $startDate) / (60 * 60 * 24);
        $badge='';
        if($days<=45){
            $badge='<span class="new-badge">New</span>';
        }
        return $badge;
    }

}
