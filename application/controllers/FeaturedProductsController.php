<?php
defined('BASEPATH') or exit('No direct script access allowed');

class FeaturedProductsController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->library('pagination');
        $this->load->library('Ajax_pagination');
        // $this->perPage = 3;
    }

    public function productList()
    {
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        $customer_login_id = (($this->session->userdata('LoginID')) ? $this->session->userdata('LoginID') : 0);
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $lang_code='';
        if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
            $lang_code=$this->session->userdata('lcode');
        }

        $identifier='restricted_access';
        $ApiResponse =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);
        if ($ApiResponse->statusCode=='200') {
            $RowCV=$ApiResponse->custom_variable;
            $restricted_access=$RowCV->value;
        } else {
            $restricted_access='no';
        }
        $data['restricted_access'] = $restricted_access;

        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;
        $page=0;

        $identifier = 'browse_by_gender_enabled';
        $data['customVariable'] = $customVariable =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);
        
        $identity = 'product_listing_get_show_records_list';
        $showLimit =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identity);
        $data['show_limit'] = $show_limit = 0;

        if (isset($showLimit->statusCode) && $showLimit->statusCode == '200') {
            $variable = $showLimit->custom_variable;
            $data['show_limit'] = explode("::", $variable->value);
            $show_limit_drp = $data['show_limit'][0];
        }

        $data['sort_val'] = $sort_val = (isset($_GET['sort']) ? $_GET['sort'] : 'newest');
        $data['show_limit_selected'] = $show_limit = (isset($_GET['limit']) ? $_GET['limit'] : $show_limit_drp);
        $page = (isset($_GET['page']) ? $_GET['page'] : 0);

        $gender = (isset($_GET['gender']) ? explode(",", $_GET['gender']) : array());
        $price_range = (isset($_GET['price_range']) ? $_POST['price_range'] : array());
        $variantId = (isset($_GET['variantId']) ? explode(",", $_GET['variantId']) : array());
        $variantVal = (isset($_GET['variantVal']) ? explode(",", $_GET['variantVal']) : array());
        $attributeArr = (isset($_GET['attribute']) ? explode(",", $_GET['attribute']) : array());

        $productApiUrl = '/webshop/featured_product_listing';

        $productArr1 = array('identifier'=>'featured','options'=>$sort_val,'page'=>$page,'limit'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'lang_code'=>$lang_code,'customer_login_id'=>$customer_login_id,'','gender'=>$gender,'price_range'=>$price_range,'variant_id_arr'=>$variantId,'variant_attr_value_arr'=>$variantVal,'attribute_arr'=>$attributeArr);

        $productCount = 0;

        $main_product_list= FeaturedProductsRepository::featured_product_listing($shopcode, $shop_id, $productArr1);
        // print_r($main_product_list);exit();
        if (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200') {
            $productCount = $main_product_list->ProductListCount;
        }

        $data['product_list'] = $main_product_list;
        $data['current_viewmode'] = (isset($_GET['viewmode']) ? $_GET['viewmode'] : 'grid-view');

        if ($page > 0) {
            $cur_page = $page * $show_limit - $show_limit;
        } else {
            $cur_page = 1;
        }

        //pagination configuration
        $config['target']      = '#product-list-section';
        $config['base_url']    = BASE_URL.'FeaturedProductsController/sort_by';
        $config['total_rows']  = $productCount;
        $config['per_page']    = $show_limit;
        $config['cur_page']    = $cur_page;
        $config['link_func']   = 'sort_by';

        $this->ajax_pagination->initialize($config);
        $data['PaginationLink'] = $this->ajax_pagination->create_links();
        
        $this->template->load('product/featured_product_list', $data);
         
    }

    public function sort_by()
    {
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        $customer_login_id = (($this->session->userdata('LoginID')) ? $this->session->userdata('LoginID') : 0);     
        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $lang_code='';
        if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
            $lang_code=$this->session->userdata('lcode');
        }

        $identifier='restricted_access';
        $ApiResponse =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);
        if ($ApiResponse->statusCode=='200') {
            $RowCV=$ApiResponse->custom_variable;
            $restricted_access=$RowCV->value;
        } else {
            $restricted_access='no';
        }
        $data['restricted_access'] = $restricted_access;

        if (!empty($_POST )) {
            $data['sort_val'] = $sort_val = !empty($_POST['sort_val']) && $_POST['sort_val'] !== 'undefined' ? $_POST['sort_val'] : 'newest';
            $page_sort_type = (isset($_POST['page_sort_type']) ? $_POST['page_sort_type'] : '');
            $show_limit = (isset($_POST['show_limit']) ? $_POST['show_limit'] : '');
            $variantId = (isset($_POST['variantId']) ? $_POST['variantId'] : array());
            $variantVal = (isset($_POST['variantVal']) ? $_POST['variantVal'] : array());
            $attributeArr = (isset($_POST['attributeArr']) ? $_POST['attributeArr'] : array());
            $gender = (isset($_POST['gender']) ? $_POST['gender'] : array());
            $cat_Id = (isset($_POST['cat_Id']) ? $_POST['cat_Id'] : '');
            $price_range = (isset($_POST['price_range']) ? $_POST['price_range'] : array());

            //calc offset number
            $data['page_sort_type'] = $page_sort_type;
            $page = (isset($_POST['page']) ? $_POST['page']:0);
            if ($page > 0) {
                $offset = $page;
            } else {
                $offset = 0;
            }

            $productArr1 = array('identifier'=>'featured','options'=>$sort_val,'page'=>$offset,'limit'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'lang_code'=>$lang_code,'customer_login_id'=>$customer_login_id,'','gender'=>$gender,'price_range'=>$price_range,'variant_id_arr'=>$variantId,'variant_attr_value_arr'=>$variantVal,'attribute_arr'=>$attributeArr);

            $productCount = 0;

            $main_product_list= FeaturedProductsRepository::featured_product_listing($shopcode, $shop_id, $productArr1);
            if (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200') {
                $productCount = $main_product_list->ProductListCount;
            }

            $data['product_list'] = $main_product_list;

            //pagination configuration
            $config['target']      = '#product-list-section';
            $config['base_url']    = BASE_URL.'FeaturedProductsController/sort_by';
            $config['total_rows']  = $productCount;
            $config['per_page']    = $show_limit;
            $config['link_func']   = 'sort_by';

            $this->ajax_pagination->initialize($config);

            $data['current_viewmode'] = $_POST['current_viewmode'];
            $data['PaginationLink'] = $this->ajax_pagination->create_links();

            $this->template->load('product/featured_product_sort_by', $data);

        }
    }

    public function productList_trending()
    {
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        $customer_login_id = (($this->session->userdata('LoginID')) ? $this->session->userdata('LoginID') : 0);
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $lang_code='';
        if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
            $lang_code=$this->session->userdata('lcode');
        }

        $identifier='restricted_access';
        $ApiResponse =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);
        if ($ApiResponse->statusCode=='200') {
            $RowCV=$ApiResponse->custom_variable;
            $restricted_access=$RowCV->value;
        } else {
            $restricted_access='no';
        }
        $data['restricted_access'] = $restricted_access;

        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;
        $page=0;

        $identity = 'product_listing_get_show_records_list';
        $showLimit =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identity);
        $data['show_limit'] = $show_limit = 0;

        if (isset($showLimit->statusCode) && $showLimit->statusCode == '200') {
            $variable = $showLimit->custom_variable;
            $data['show_limit'] = explode("::", $variable->value);
            $show_limit_drp = $data['show_limit'][0];
        }

        $data['sort_val'] = $sort_val = (isset($_GET['sort']) ? $_GET['sort'] : 'newest');
        $data['show_limit_selected'] = $show_limit = (isset($_GET['limit']) ? $_GET['limit'] : $show_limit_drp);
        $page = (isset($_GET['page']) ? $_GET['page'] : 0);

        $productApiUrl = '/webshop/featured_product_listing';

        $productArr1 = array('identifier'=>'trending','options'=>$sort_val,'page'=>$page,'limit'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'lang_code'=>$lang_code,'customer_login_id'=>$customer_login_id);

        $productCount = 0;

        $main_product_list= FeaturedProductsRepository::featured_product_listing($shopcode, $shop_id, $productArr1);
        if (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200') {
            $productCount = $main_product_list->ProductListCount;
        }

        $data['product_list'] = $main_product_list;
        $data['current_viewmode'] = (isset($_GET['viewmode']) ? $_GET['viewmode'] : 'grid-view');

        if ($page > 0) {
            $cur_page = $page * $show_limit - $show_limit;
        } else {
            $cur_page = 1;
        }

        //pagination configuration
        $config['target']      = '#product-list-section';
        $config['base_url']    = BASE_URL.'FeaturedProductsController/sort_by_trending';
        $config['total_rows']  = $productCount;
        $config['per_page']    = $show_limit;
        $config['cur_page']    = $cur_page;
        $config['link_func']   = 'sort_by';

        $this->ajax_pagination->initialize($config);
        $data['PaginationLink'] = $this->ajax_pagination->create_links();

        $this->template->load('product/trending_product_list', $data);
         
    }

    public function sort_by_trending()
    {
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        $customer_login_id = (($this->session->userdata('LoginID')) ? $this->session->userdata('LoginID') : 0);       
        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $lang_code='';
        if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
            $lang_code=$this->session->userdata('lcode');
        }

        $identifier='restricted_access';
        $ApiResponse =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);
        if ($ApiResponse->statusCode=='200') {
            $RowCV=$ApiResponse->custom_variable;
            $restricted_access=$RowCV->value;
        } else {
            $restricted_access='no';
        }
        $data['restricted_access'] = $restricted_access;

        if (!empty($_POST )) {
            $data['sort_val'] = $sort_val = (isset($_POST['sort_val']) ? $_POST['sort_val'] : 'newest');
            $show_limit = (isset($_POST['show_limit']) ? $_POST['show_limit'] : '');

            //calc offset number
            $page = (isset($_POST['page']) ? $_POST['page']:0);
            if ($page > 0) {
                $offset = $page;
            } else {
                $offset = 0;
            }

            $productArr1 = array('identifier'=>'trending','options'=>$sort_val,'page'=>$page,'limit'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'lang_code'=>$lang_code,'customer_login_id'=>$customer_login_id);

            $productCount = 0;

            $main_product_list= FeaturedProductsRepository::featured_product_listing($shopcode, $shop_id, $productArr1);
            if (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200') {
                $productCount = $main_product_list->ProductListCount;
            }

            $data['product_list'] = $main_product_list;

            //pagination configuration
            $config['target']      = '#product-list-section';
            $config['base_url']    = BASE_URL.'FeaturedProductsController/sort_by_trending';
            $config['total_rows']  = $productCount;
            $config['per_page']    = $show_limit;
            $config['link_func']   = 'sort_by';

            $this->ajax_pagination->initialize($config);

            $data['current_viewmode'] = $_POST['current_viewmode'];
            $data['PaginationLink'] = $this->ajax_pagination->create_links();

            $this->template->load('product/trending_product_sort_by', $data);

        }
    }

    public function productList_newarrival()
    {
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        $customer_login_id = (($this->session->userdata('LoginID')) ? $this->session->userdata('LoginID') : 0);
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $lang_code='';
        if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
            $lang_code=$this->session->userdata('lcode');
        }

        $identifier='restricted_access';
        $ApiResponse =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);
        if ($ApiResponse->statusCode=='200') {
            $RowCV=$ApiResponse->custom_variable;
            $restricted_access=$RowCV->value;
        } else {
            $restricted_access='no';
        }
        $data['restricted_access'] = $restricted_access;

        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;
        $page=0;

        $identifier = 'browse_by_gender_enabled';
        $data['customVariable'] = $customVariable =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);

        $identity = 'product_listing_get_show_records_list';
        $showLimit =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identity);
        $data['show_limit'] = $show_limit = 0;

        if (isset($showLimit->statusCode) && $showLimit->statusCode == '200') {
            $variable = $showLimit->custom_variable;
            $data['show_limit'] = explode("::", $variable->value);
            $show_limit_drp = $data['show_limit'][0];
        }

        $data['sort_val'] = $sort_val = (isset($_GET['sort']) ? $_GET['sort'] : 'newest');
        if(THEMENAME == 'theme_zumbawear'){
            $data['show_limit_selected'] = $show_limit = (isset($_GET['limit']) ? $_GET['limit'] : $show_limit_drp);
        }
        else{
            $data['show_limit_selected'] = $show_limit = 25; 
        }
        $page = (isset($_GET['page']) ? $_GET['page'] : 0);

        $gender = (isset($_GET['gender']) ? explode(",", $_GET['gender']) : array());
        $price_range = (isset($_GET['price_range']) ? $_POST['price_range'] : array());
        $variantId = (isset($_GET['variantId']) ? explode(",", $_GET['variantId']) : array());
        $variantVal = (isset($_GET['variantVal']) ? explode(",", $_GET['variantVal']) : array());
        $attributeArr = (isset($_GET['attribute']) ? explode(",", $_GET['attribute']) : array());

        $badge = '';
        if(THEMENAME == 'theme_zumbawear'){
            $show_limit=$show_limit;
            $badge = 1;
        }
        else{
            $show_limit=25;
        }

        $productApiUrl = '/webshop/newarrival_product_listing';

        $productArr1 = array('options'=>$sort_val,'page'=>$page,'limit'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'lang_code'=>$lang_code,'customer_login_id'=>$customer_login_id,'badge'=>$badge,'gender'=>$gender,'price_range'=>$price_range,'variant_id_arr'=>$variantId,'variant_attr_value_arr'=>$variantVal,'attribute_arr'=>$attributeArr);
        $productCount = 0;

        $main_product_list= FeaturedProductsRepository::newarrival_product_listing($shopcode, $shop_id, $productArr1);
        if (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200') {
            $productCount = $main_product_list->ProductListCount;
        }

        $data['product_list'] = $main_product_list;
        $data['current_viewmode'] = (isset($_GET['viewmode']) ? $_GET['viewmode'] : 'grid-view');

        if ($page > 0) {
            $cur_page = $page * $show_limit - $show_limit;
        } else {
            $cur_page = 1;
        }

        //pagination configuration
        $config['target']      = '#product-list-section';
        $config['base_url']    = BASE_URL.'FeaturedProductsController/sort_by_newarrival';
        $config['total_rows']  = $productCount;
        $config['per_page']    = $show_limit;
        $config['cur_page']    = $cur_page;
        $config['link_func']   = 'sort_by';

        $this->ajax_pagination->initialize($config);
        $data['PaginationLink'] = $this->ajax_pagination->create_links();

        $this->template->load('product/newarrival_product_list', $data);
         
    }

    public function sort_by_newarrival()
    {
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        $customer_login_id = (($this->session->userdata('LoginID')) ? $this->session->userdata('LoginID') : 0);
        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $lang_code='';
        if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
            $lang_code=$this->session->userdata('lcode');
        }

        $identifier='restricted_access';
        $ApiResponse =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);
        if ($ApiResponse->statusCode=='200') {
            $RowCV=$ApiResponse->custom_variable;
            $restricted_access=$RowCV->value;
        } else {
            $restricted_access='no';
        }
        $data['restricted_access'] = $restricted_access;

        if (!empty($_POST )) {
            $data['sort_val'] = $sort_val = (isset($_POST['sort_val']) ? $_POST['sort_val'] : 'newest');
            $show_limit = (isset($_POST['show_limit']) ? $_POST['show_limit'] : '');
            $page_sort_type = (isset($_POST['page_sort_type']) ? $_POST['page_sort_type'] : '');
            $variantId = (isset($_POST['variantId']) ? $_POST['variantId'] : array());
            $variantVal = (isset($_POST['variantVal']) ? $_POST['variantVal'] : array());
            $attributeArr = (isset($_POST['attributeArr']) ? $_POST['attributeArr'] : array());
            $gender = (isset($_POST['gender']) ? $_POST['gender'] : array());
            $cat_Id = (isset($_POST['cat_Id']) ? $_POST['cat_Id'] : '');
            $price_range = (isset($_POST['price_range']) ? $_POST['price_range'] : array());

            $data['page_sort_type'] = $page_sort_type;
            //calc offset number
            $page = (isset($_POST['page']) ? $_POST['page']:0);
            if ($page > 0) {
                $offset = $page;
            } else {
                $offset = 0;
            }

            $badge = '';
            if(THEMENAME == 'theme_zumbawear'){
                $show_limit=$show_limit;
                $badge = 1;
            }
            else{
                $show_limit=25;
            }
            $productArr1 = array('options'=>$sort_val,'page'=>$offset,'limit'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'lang_code'=>$lang_code,'customer_login_id'=>$customer_login_id,'badge'=>$badge,'gender'=>$gender,'price_range'=>$price_range,'variant_id_arr'=>$variantId,'variant_attr_value_arr'=>$variantVal,'attribute_arr'=>$attributeArr);

            $productCount = 0;

            $main_product_list= FeaturedProductsRepository::newarrival_product_listing($shopcode, $shop_id, $productArr1);
            if (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200') {
                $productCount = $main_product_list->ProductListCount;
            }

            $data['product_list'] = $main_product_list;

            //pagination configuration
            $config['target']      = '#product-list-section';
            $config['base_url']    = BASE_URL.'FeaturedProductsController/sort_by_newarrival';
            $config['total_rows']  = $productCount;
            $config['per_page']    = $show_limit;
            $config['link_func']   = 'sort_by';

            $this->ajax_pagination->initialize($config);

            $data['current_viewmode'] = $_POST['current_viewmode'];
            $data['PaginationLink'] = $this->ajax_pagination->create_links();

            $this->template->load('product/newarrival_product_sort_by', $data);
        }
    }

}
