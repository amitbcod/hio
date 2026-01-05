<?php
/**
 * @property CI_Controller $ci
 */
class HomeCategoryBanners {
    private $ci;
    private $banner_list;
    private $category_id;
    private $banner_type;
    private $lang_code;
    private $customer_type_id;

    public function __construct($banner_type, $category_id=''){
        $this->ci =& get_instance();
        $this->category_id = $category_id;
        $this->banner_type = $banner_type;
        $lang_code = $this->lang_code = $this->ci->session->userdata('lcode') ?? '' ;
        $customer_type_id = $this->customer_type_id = $this->ci->session->userdata('CustomerTypeID') ?? 1 ;
        $this->category_id = $category_id;
        $data = array('banner_type'=>$banner_type,'lang_code' => $lang_code,'customer_type_id'=>$customer_type_id);
        if($category_id !=''){
            $data2 = array('category_id'=>$category_id);
            $data = array_merge($data,$data2);
        }

        $this->banner_list = HomeDetailsRepository::get_banners($data);
      
    }

    public function render(){
        if(empty($this->banner_list)) {
           if($this->banner_type=='categorybanner' && THEMENAME=='theme2'){

           }elseif( ($this->banner_type=='zumbahomebanner' || $this->banner_type=='zumbahomeblock1') && THEMENAME=='theme_zumbawear'){

           }else{return;}
        }
        //echo $this->banner_type;
        if ($this->banner_type == "homecatblock1" || $this->banner_type == "homecatblock2") {
            $this->ci->template->load('components/homepage_static_banner_product', ['banners' => $this->banner_list,'category_id' => $this->category_id,'banner_type' => $this->banner_type,'lang_code'=>$this->lang_code,'CustomerTypeID'=>$this->customer_type_id]);
        }elseif($this->banner_type == "homebannerzumbaweartheme"){
            $this->ci->template->load('components/homepage_zumba_banner', ['banners' => $this->banner_list,'category_id' => $this->category_id,'banner_type' => $this->banner_type,'lang_code'=>$this->lang_code,'CustomerTypeID'=>$this->customer_type_id]);
        // }elseif($this->banner_type == "zumbahomeblock1"){
        //      $this->ci->template->load('components/homepage_static_banner_product', ['banners' => $this->banner_list,'category_id' => $this->category_id,'banner_type' => $this->banner_type,'lang_code'=>$this->lang_code,'CustomerTypeID'=>$this->customer_type_id]);
        }else{
            $this->ci->template->load('components/home_category_banners', ['banners' => $this->banner_list,'category_id' => $this->category_id,'banner_type' => $this->banner_type]);
        }
    }

}
