<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SearchController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->library('pagination');
        $this->load->library('Ajax_pagination');
        // $this->perPage = 3;
    }

    public function searchResultPage()
    {
        $LoginID = $this->session->userdata('LoginID');
        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;

        $data['PageTitle'] = 'Search';
        $data['PageMetaTitle'] = $_GET['s'] . ' - indiamags';
        $data['PageMetaDesc'] = $_GET['s'];
        $data['PageMetaKey'] = $_GET['s'];
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if (!isset($_GET['s']) || $_GET['s'] == '') {
            // show_404();
            // exit;
        }

        $page = 0;

        $identifier = 'browse_by_gender_enabled';
        $data['customVariable'] = $customVariable =  GlobalRepository::get_custom_variable($identifier);
        $identity = 'product_listing_get_show_records_list';

        $customVariable =  GlobalRepository::get_custom_variable($identity);

        $data['show_limit'] = $show_limit = 0;
        if (isset($customVariable->statusCode) && $customVariable->statusCode == '200') {
            $variable = $customVariable->custom_variable;
            $data['show_limit'] = explode("::", $variable->value);
            $show_limit_drp = $data['show_limit'][0];
        }

        $data['search_term'] = $search_term = urlencode($_GET['s']);
        $data['sort_val'] = $sort_val = !empty($_GET['sort']) && $_GET['sort'] !== 'undefined' ? $_GET['sort'] : 'newest';
        $data['show_limit_selected'] = $show_limit = !empty($_GET['limit']) && is_numeric($_GET['limit']) ? $_GET['limit'] : $show_limit_drp;
        $page = (isset($_GET['page']) ? $_GET['page'] : 0);

        if (!str_contains($search_term, '+%2B')) {
            $search_term = urldecode($search_term);
        }

        // echo $show_limit;
        // if(str_contains)
        $gender = (isset($_GET['gender']) ? explode(",", $_GET['gender']) : array());
        $price_range = (isset($_GET['price_range']) ? $_POST['price_range'] : array());
        $variantId = (isset($_GET['variantId']) ? explode(",", $_GET['variantId']) : array());
        $variantVal = (isset($_GET['variantVal']) ? explode(",", $_GET['variantVal']) : array());
        $attributeArr = (isset($_GET['attribute']) ? explode(",", $_GET['attribute']) : array());

        $productArrCat = array('search_term' => $search_term);
        $categorySearch =  ProductRepository::geSearchtCategoryIds($productArrCat);
        $categoryIdsarr =  array();
        if (!empty($categorySearch) && (isset($categorySearch->statusCode) && $categorySearch->statusCode == '200')) {
            foreach ($categorySearch->categoryIds as $catKey => $catval) {
                array_push($categoryIdsarr, $catval->id);
            }
        }

        $lang_code = '';
        if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
            $lang_code = $this->session->userdata('lcode');
        }

        if (isset($LoginID)) {
            $productArr1 = array('search_term' => $search_term, 'options' => $sort_val, 'page' => $page, 'page_size' => $show_limit, 'customer_type_id' => $customer_type_id, 'vat_percent_session' => $vat_percent_session, 'customer_login_id' => $LoginID, 'lang_code' => $lang_code, 'gender' => $gender, 'variant_id_arr' => $variantId, 'variant_attr_value_arr' => $variantVal, 'attribute_arr' => $attributeArr, 'categoryIdsarr' => $categoryIdsarr);
        } else {
            $productArr1 = array('search_term' => $search_term, 'options' => $sort_val, 'page' => $page, 'page_size' => $show_limit, 'customer_type_id' => $customer_type_id, 'vat_percent_session' => $vat_percent_session, 'customer_login_id' => 0, 'lang_code' => $lang_code, 'gender' => $gender, 'variant_id_arr' => $variantId, 'variant_attr_value_arr' => $variantVal, 'attribute_arr' => $attributeArr, 'categoryIdsarr' => $categoryIdsarr);
        }
        // print_r($productArr1);
        $productCount = 0;
        $main_product_list = ProductRepository::product_listing($productArr1);
        // echo "<pre>" ;print_r($main_product_list);
        // die();
        // print_r($main_product_list);
        if (!empty($main_product_list) && (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200')) {
            $productCount = $main_product_list->ProductListCount;
        }

       
        if ($productCount == 0) {
            $foundProducts = [];
            $search_terms = array_filter(explode(' ', strtolower(trim($search_term)))); // Split the search term into words and filter out empty terms
        
            foreach ($search_terms as $term) {
                $productArr1['search_term'] = $term;
                $fallback_results = ProductRepository::product_listing($productArr1);
        
                if (!empty($fallback_results) && $fallback_results->statusCode == '200') {
                    $foundProducts = array_merge($foundProducts, $fallback_results->ProductList);
                }
            }
        
            // Remove duplicate products if needed
            $foundProducts = array_unique($foundProducts, SORT_REGULAR);
        
            // Update the product list with found products
            if (!empty($foundProducts)) {
                $main_product_list->ProductList = $foundProducts;
                $productCount = count($foundProducts);
            }
        }

        $data['product_list'] = $main_product_list;
        $data['current_viewmode'] = (isset($_GET['viewmode']) ? $_GET['viewmode'] : 'grid-view');

        if ($page > 0) {
            $cur_page = $page * $show_limit - $show_limit;
        } else {
            $cur_page = 1;
        }
        // echo  count($main_product_list->ProductList;
        // die();
        //pagination configuration
        $config['target']      = '#product-list-section';
        $config['base_url']    = BASE_URL . 'ProductsController/sort_by';
        $config['total_rows']  = $productCount;
        $config['per_page']    = $show_limit;
        $config['cur_page']    = $cur_page;
        $config['link_func']   = 'sort_by';
        $config['search_terms']   = $search_term;
        $this->ajax_pagination->initialize($config);

        $data['PaginationLink'] = $this->ajax_pagination->create_links();

        if ($productCount > 0) {
            $searchTermArr = array('search_term' => $search_term);
            $saveSearch = SearchRepository::save_search_term($shopcode, $shop_id, $searchTermArr);
        }

        $identifier = 'restricted_access';
        $ApiResponse =  GlobalRepository::get_custom_variable($identifier);
        if ($ApiResponse->statusCode == '200') {
            $RowCV = $ApiResponse->custom_variable;
            $restricted_access = $RowCV->value;
        } else {
            $restricted_access = 'no';
        }
        $data['restricted_access'] = $restricted_access;
      
        // $webshop_name_shop = GlobalRepository::get_fbc_users_shop();
        // $data['shop_flag_shop']=$webshop_name_shop->result->shop_flag ?? '';

        $this->template->load('search/search_result_page', $data);
    }

	public function getSearchSuggestion()
	{
		if (!empty($_POST)) {
			// $shopcode = SHOPCODE;
			// $shop_id = SHOP_ID;
			$search_term = $_POST['search_key'];
			// $search_term = urlencode($search_term);
			// $get_search_terms = SearchRepository::get_search_terms($search_term);
			$get_search_terms = SearchRepository::get_search_terms_post($search_term);
			// print_R($get_search_terms);
			$html = "";
			if (isset($get_search_terms->statusCode) && $get_search_terms->statusCode == '200') {
				$search_result = $get_search_terms->search_result;
				$count = 0;
				$html .= '<ul>';
				foreach ($search_result as $value) {
					if ($count < 3) {
						$html .= '<li><a href="' . BASE_URL . '/searchresult/?s=' . urlencode($value->search_term) . '">' . $value->search_term . '</a></li>';
					}
					$count++;
				}
				if ($count > 3) {
					$html .= '<li><a href="' . BASE_URL . '/searchresult/?s=' . urlencode($search_term) . '" style="color: #E02222;">See More</a></li>';
				}
				$html .= '</ul>';
			} else {

				$html = 'No products Found';
			}

			echo $html;
			exit;
		}
	}
	
}
