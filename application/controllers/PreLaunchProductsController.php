<?php

defined('BASEPATH') or exit('No direct script access allowed');



class PreLaunchProductsController extends CI_Controller
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

        $LoginID = $this->session->userdata('LoginID');

        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;

        $lang_code='';
		if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
			$lang_code=$this->session->userdata('lcode');
		}



        $postArr = array('customer_id'=>$LoginID);

        $response = CustomerRepository::customer_get_personal_info($shopcode, $shop_id, $postArr);

        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $customerData = $response->customerData;



            if ($customerData->access_prelanch_product == 1  && $LoginID > 0) {
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





                $productApiUrl = '/webshop/prelauch_product_listing';

                $productArr1 = array('options'=>$sort_val,'page'=>$page,'page_size'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'lang_code'=>$lang_code);

                $productCount = 0;





                $main_product_list= PreLaunchProductsRepository::prelauch_product_listing($shopcode, $shop_id, $productArr1);
                if (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200') {
                    $productCount = $main_product_list->ProductListCount;
                }



                //print_r($main_product_list);



                $data['product_list'] = $main_product_list;

                $data['current_viewmode'] = (isset($_GET['viewmode']) ? $_GET['viewmode'] : 'grid-view');



                if ($page > 0) {
                    $cur_page = $page * $show_limit - $show_limit;
                } else {
                    $cur_page = 1;
                }



                //pagination configuration

                $config['target']      = '#product-list-section';

                $config['base_url']    = BASE_URL.'PreLaunchProductsController/sort_by';

                $config['total_rows']  = $productCount;

                $config['per_page']    = $show_limit;

                $config['cur_page']    = $cur_page;

                $config['link_func']   = 'sort_by';

                $this->ajax_pagination->initialize($config);



                $data['PaginationLink'] = $this->ajax_pagination->create_links();
                $this->template->load('product/prelauch_product_list', $data);
            } else {
                redirect(BASE_URL());
            }
        } else {
            redirect(BASE_URL());
        }
    }



    public function sort_by()
    {
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');

        $customer_type_id = $this->session->userdata('CustomerTypeID');

        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;





        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;

        $lang_code='';
		if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
			$lang_code=$this->session->userdata('lcode');
		}



        if (!empty($_POST)) {
            $sort_val = (isset($_POST['sort_val']) ? $_POST['sort_val'] : '');

            $show_limit = (isset($_POST['show_limit']) ? $_POST['show_limit'] : '');





            //calc offset number

            $page = (isset($_POST['page']) ? $_POST['page']:0);

            if ($page > 0) {
                $offset = $page;
            } else {
                $offset = 0;
            }

            $productArr1 = array('options'=>$sort_val,'page'=>$offset,'page_size'=>$show_limit,'customer_type_id'=>$customer_type_id,'vat_percent_session'=>$vat_percent_session,'lang_code'=>$lang_code);

            $productCount = 0;

            $main_product_list= PreLaunchProductsRepository::prelauch_product_listing($shopcode, $shop_id, $productArr1);
            if (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200') {
                $productCount = $main_product_list->ProductListCount;
            }



            $data['product_list'] = $main_product_list;







            //pagination configuration

            $config['target']      = '#product-list-section';

            $config['base_url']    = BASE_URL.'PreLaunchProductsController/sort_by';

            $config['total_rows']  = $productCount;

            $config['per_page']    = $show_limit;

            $config['link_func']   = 'sort_by';

            $this->ajax_pagination->initialize($config);



            $data['current_viewmode'] = $_POST['current_viewmode'];
            $this->template->load('product/prelauch_product_sort_by', $data);
        }
    }
}
