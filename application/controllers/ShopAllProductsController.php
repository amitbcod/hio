<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ShopAllProductsController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Ajax_pagination');
    }

    public function productList()
    {
        $LoginID = $this->session->userdata('LoginID');
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;

        $data['PageTitle']= 'Shop All';
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $page=0;

        $identifier = 'product_listing_get_show_records_list';

        $customVariable =  GlobalRepository::get_custom_variable($identifier);

        $data['show_limit'] = $show_limit = 0;
        if (isset($customVariable->statusCode) && $customVariable->statusCode == '200') {
            $variable = $customVariable->custom_variable;
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


        $lang_code='';
		if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
			$lang_code=$this->session->userdata('lcode');
		}

        if (isset($LoginID)) {
            $productArr1 = array('options'=>$sort_val,'gender'=>$gender,'variant_id_arr'=>$variantId,'variant_attr_value_arr'=>$variantVal,'attribute_arr'=>$attributeArr,'page'=>$page,'page_size'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'customer_login_id'=>$LoginID,'lang_code'=>$lang_code);
        } else {
            $productArr1 = array('options'=>$sort_val,'gender'=>$gender,'variant_id_arr'=>$variantId,'variant_attr_value_arr'=>$variantVal,'attribute_arr'=>$attributeArr,'page'=>$page,'page_size'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'customer_login_id'=>0,'lang_code'=>$lang_code);
        }

        $productCount = 0;
        $main_product_list = ProductRepository::product_listing($productArr1);
        if (!empty($main_product_list) &&(isset($main_product_list->statusCode) && $main_product_list->statusCode == '200')) {
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
        $config['base_url']    = BASE_URL.'ProductsController/sort_by';
        $config['total_rows']  = $productCount;
        $config['per_page']    = $show_limit;
        $config['cur_page']    = $cur_page;
        $config['link_func']   = 'sort_by';
        $this->ajax_pagination->initialize($config);

        $data['PaginationLink'] = $this->ajax_pagination->create_links();

        $identifier='restricted_access';
        $ApiResponse =  GlobalRepository::get_custom_variable($identifier);
        if ($ApiResponse->statusCode=='200') {
            $RowCV=$ApiResponse->custom_variable;
            $restricted_access=$RowCV->value;
        } else {
            $restricted_access='no';
        }
        $data['restricted_access'] = $restricted_access;

        $this->template->load('shopAll/product_list', $data);
    }

}
