<?php

defined('BASEPATH') or exit('No direct script access allowed');



class ProductsController extends CI_Controller
{

    public function __construct()
    {

        parent::__construct();

        //$this->load->library('pagination');
        $this->load->model('Shop_model');
        $this->load->library('Ajax_pagination');

        // $this->perPage = 3;
        $this->load->helper(['url', 'form', 'language']);
        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }
    }



    public function productList()
    {

        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');

        $data['customer_id'] = $LoginID = $this->session->userdata('LoginID');

        $customer_type_id = $this->session->userdata('CustomerTypeID');

        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;



        $block = 'categorybanner';

        $identifier = 'browse_by_gender_enabled';



        $lang_code = '';

        if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {

            $lang_code = $this->session->userdata('lcode');
        }



        $cat_slug = '';



        $data['main_cat'] = $main_cat = $this->uri->segment(2);

        $data['level1_cat'] = $level1_cat = $this->uri->segment(3);

        $data['level2_cat'] = $level2_cat = $this->uri->segment(4);



        if ($level2_cat) {

            $cat_slug = $main_cat . '/' . $level1_cat . '/' . $level2_cat;

            $categoryArr_level2 = array('categoryslug' => $cat_slug, 'lang_code' => $lang_code);

            $category_level2 = ProductRepository::get_category_details($categoryArr_level2);

            if ($category_level2->is_success == 'true') {

                if (isset($category_level2->CategoryDetails->lang_cat_name) && $category_level2->CategoryDetails->lang_cat_name != '') {

                    $data['level2_cat_name'] = $category_level2->CategoryDetails->lang_cat_name;
                } else {

                    $data['level2_cat_name'] = $category_level2->CategoryDetails->cat_name;
                }
            }
        }

        if ($level1_cat) {

            $cat_slug = $main_cat . '/' . $level1_cat;

            $categoryArr_level1 = array('categoryslug' => $cat_slug, 'lang_code' => $lang_code);

            $category_level1 = ProductRepository::get_category_details($categoryArr_level1);

            if ($category_level1->is_success == 'true') {

                if (isset($category_level1->CategoryDetails->lang_cat_name) && $category_level1->CategoryDetails->lang_cat_name != '') {

                    $data['level1_cat_name'] = $category_level1->CategoryDetails->lang_cat_name;
                } else {

                    $data['level1_cat_name'] = $category_level1->CategoryDetails->cat_name;
                }
            }
        }

        if ($main_cat) {

            $cat_slug = $main_cat;

            $categoryArr_main = array('categoryslug' => $cat_slug, 'lang_code' => $lang_code);

            $category_main = ProductRepository::get_category_details($categoryArr_main);



            if ($category_main->is_success == 'true') {

                if (isset($category_main->CategoryDetails->lang_cat_name) && $category_main->CategoryDetails->lang_cat_name != '') {

                    $data['main_cat_name'] = $category_main->CategoryDetails->lang_cat_name;
                } else {

                    $data['main_cat_name'] = $category_main->CategoryDetails->cat_name;
                }
            }
        }





        if ($level2_cat) {

            $cat_slug = $main_cat . '/' . $level1_cat . '/' . $level2_cat;
        } elseif ($level1_cat) {

            $cat_slug = $main_cat . '/' . $level1_cat;
        } elseif ($main_cat) {

            $cat_slug = $main_cat;
        }



        $data['customVariable'] = $customVariable = GlobalRepository::get_custom_variable($identifier);

        $identity = 'product_listing_get_show_records_list';



        $showLimit = GlobalRepository::get_custom_variable($identity);



        $data['show_limit'] = $show_limit = 0;

        if (isset($showLimit->statusCode) && $showLimit->statusCode == '200') {

            $variable = $showLimit->custom_variable;

            $data['show_limit'] = explode("::", $variable->value);

            $show_limit_drp = $data['show_limit'][0];
        }





        $data['sort_val'] = $sort_val = !empty($_GET['sort']) && $_GET['sort'] !== 'undefined' ? $_GET['sort'] : 'newest';

        $data['show_limit_selected'] = $show_limit = !empty($_GET['limit']) && is_numeric($_GET['limit']) ? $_GET['limit'] : $show_limit_drp;

        $page = (isset($_GET['page']) ? $_GET['page'] : 0);



        $gender = (isset($_GET['gender']) ? explode(",", $_GET['gender']) : array());

        $price_range = (isset($_GET['price_range']) ? $_POST['price_range'] : array());

        $variantId = (isset($_GET['variantId']) ? explode(",", $_GET['variantId']) : array());

        $variantVal = (isset($_GET['variantVal']) ? explode(",", $_GET['variantVal']) : array());

        $attributeArr = (isset($_GET['attribute']) ? explode(",", $_GET['attribute']) : array());



        $categoryArr = array('categoryslug' => $cat_slug, 'lang_code' => $lang_code);

        $data['category'] = $category = ProductRepository::get_category_details($categoryArr);

        // print_r($category->CategoryDetails->cat_name);

        if (isset($category) && $category->is_success == true) {
            $data['PageMetaDesc'] = $category->CategoryDetails->cat_description;
            $data['PageMetaKey'] = "Art and culture, Automotive, Best offers, Business, Celebrity, Delivery, Design, Digital publications, Diverse genres, Entertainment, Expert advice, Fashion, Financial, Fitness, Food and cooking, Free shipping, Gaming, Health and wellness, Home and Decor, Inspiring articles, Interactive experience, Latest trends and fashion, Lifestyle, Lifestyle trends, Men, Multiple payment options,, Music, News and current affairs, Niche, Online magazine, Online, Outdoor, Parenting, Personalized suggestions, Photography, Popular, Science and technology, Sports, Technology, Top-rated, Travel, Weekly and monthly issues, Women, magazine, magazines, western union, american express, master card, visa, paypal";
        }

        if (!empty($category) && $category->is_success === 'true') {

            $data['cat_obj'] = $cat_obj = $category->CategoryDetails;



            $data['current_category_id'] = $cat_obj->id;



            if (isset($LoginID)) {

                $productArr1 = array('categoryid' => $cat_obj->id, 'options' => $sort_val, 'gender' => $gender, 'variant_id_arr' => $variantId, 'variant_attr_value_arr' => $variantVal, 'attribute_arr' => $attributeArr, 'page' => $page, 'page_size' => $show_limit, 'customer_type_id' => $customer_type_id, 'vat_percent_session' => $vat_percent_session, 'customer_login_id' => $LoginID, 'lang_code' => $lang_code);
            } else {

                $productArr1 = array('categoryid' => $cat_obj->id, 'options' => $sort_val, 'gender' => $gender, 'variant_id_arr' => $variantId, 'variant_attr_value_arr' => $variantVal, 'attribute_arr' => $attributeArr, 'page' => $page, 'page_size' => $show_limit, 'customer_type_id' => $customer_type_id, 'vat_percent_session' => $vat_percent_session, 'customer_login_id' => 0, 'lang_code' => $lang_code);
            }
        } else {

            redirect(BASE_URL);
        }



        $productCount = 0;





        $main_product_list = ProductRepository::product_listing($productArr1);

        // print_r($main_product_list);
        // die;



        if (!empty($main_product_list) && (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200')) {

            $productCount = $main_product_list->ProductListCount;
        }



        $data['product_list'] = $main_product_list;
        // echo "<pre>";
        // print_r($data['product_list']);
        // die;




        $data['current_viewmode'] = (isset($_GET['viewmode']) ? $_GET['viewmode'] : 'grid-view');



        if ($page > 0) {

            $cur_page = $page * $show_limit - $show_limit;
        } else {

            $cur_page = 1;
        }





        //pagination configuration

        $config['target'] = '#product-list-section';

        $config['base_url'] = BASE_URL . 'ProductsController/sort_by';

        $config['total_rows'] = $productCount;

        $config['per_page'] = $show_limit;

        $config['cur_page'] = $cur_page;

        $config['link_func'] = 'sort_by';

        $this->ajax_pagination->initialize($config);



        $data['PaginationLink'] = $this->ajax_pagination->create_links();



        $identifier = 'restricted_access';

        $ApiResponse = GlobalRepository::get_custom_variable($identifier);

        if ($ApiResponse->statusCode == '200') {

            $RowCV = $ApiResponse->custom_variable;

            $restricted_access = $RowCV->value;
        } else {

            $restricted_access = 'no';
        }

        $data['restricted_access'] = $restricted_access;



        if (!empty($cat_obj->lang_cat_name) && $cat_obj->lang_cat_name != '') {

            $data['PageTitle'] = $cat_obj->lang_cat_name;
        } else {

            $data['PageTitle'] = $cat_obj->cat_name;
            $data['PageMetaTitle'] = $cat_obj->meta_title;
            $data['PageMetaKey'] = $cat_obj->meta_keyword;

            $data['PageMetaDesc'] = $cat_obj->meta_description;

        }



        $data['subsVariantsData'] = HomeDetailsRepository::get_subscription_variants([

            'Identifier' => 'subscription'

        ]);

        $data['catalogFilter'] = ProductRepository::get_catalog_filters($productArr1);



        $this->template->load('product/product_list', $data);
    }

    public function productQuickDetails()
    {
        $data['customer_id'] = $LoginID = $this->session->userdata('LoginID');
        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;

        $data['refresh_flag'] = '';

        $data['product_slug'] = $product_slug = $_POST['productslug'];
        $data['productLink'] = $_POST['produrl'];

        if ($product_slug != '') {
            $ProductArr = array('product_url_key' => $product_slug);
            $ResponseData = ProductRepository::product_detail($ProductArr);

            if (!empty($ResponseData) && isset($ResponseData) && $ResponseData->statusCode == '200') {
                $ProductData = $ResponseData->ProductData;
                $data['ProductData'] = $ProductData;

                if (isset($ProductData->other_lang_name) && $ProductData->other_lang_name != '') {
                    $data['PageTitle'] = $ProductData->other_lang_name;
                } else {
                    $data['PageTitle'] = $ProductData->name;
                }
                if ($ProductData->meta_title != "") {
                    $data['PageMetaTitle'] = $ProductData->meta_title;
                }
                if ($ProductData->meta_keyword != "") {
                    $data['PageMetaKey'] = $ProductData->meta_keyword;
                }
                if ($ProductData->meta_description != "") {
                    $data['PageMetaDesc'] = $ProductData->meta_description;
                }

                $identifier = 'restricted_access';
                $ApiResponse = GlobalRepository::get_custom_variable($identifier);

                if (!empty($ApiResponse) && isset($ApiResponse) && $ApiResponse->statusCode == '200') {
                    $RowCV = $ApiResponse->custom_variable;
                    $restricted_access = $RowCV->value;
                } else {
                    $restricted_access = 'no';
                }
                $data['restricted_access'] = $restricted_access;
                $this->template->load('components/fast_view_product_details', $data);
            }
        } else {
            echo json_encode(array('status' => 500, 'message' => "Something went wrong!"));
            exit;
        }
    }



    public function sort_by()
    {

        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');

        $data['customer_id'] = $LoginID = $this->session->userdata('LoginID');

        $customer_type_id = $this->session->userdata('CustomerTypeID');

        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;





        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;



        if (!empty($_POST)) {



            $showLimitVariable = GlobalRepository::get_custom_variable('product_listing_get_show_records_list');

            if (isset($showLimitVariable->statusCode) && $showLimitVariable->statusCode == '200') {

                $show_limit_drp = explode("::", $showLimitVariable->custom_variable->value)[0];
            }



            $sort_val = !empty($_POST['sort_val']) && $_POST['sort_val'] !== 'undefined' ? $_POST['sort_val'] : 'newest';

            $show_limit = !empty($_POST['show_limit']) && is_numeric($_POST['show_limit']) ? $_POST['show_limit'] : $show_limit_drp;



            $cat_Id = (isset($_POST['cat_Id']) ? $_POST['cat_Id'] : '');

            $subscription = (isset($_POST['subscription']) ? $_POST['subscription'] : array());

            $price_range = (isset($_POST['price_range']) ? $_POST['price_range'] : array());

            $variantId = (isset($_POST['variantId']) ? $_POST['variantId'] : array());

            $variantVal = (isset($_POST['variantVal']) ? $_POST['variantVal'] : array());

            $attributeArr = (isset($_POST['attributeArr']) ? $_POST['attributeArr'] : array());

            $search_terms = (isset($_POST['search_terms']) ? $_POST['search_terms'] : '');

            $page_sort_type = (isset($_POST['page_sort_type']) ? $_POST['page_sort_type'] : '');

            $language_arr = (isset($_POST['language_arr']) ? $_POST['language_arr'] : '');


            $categoryIdsarr = array();
            $productArrCat = array('search_term' => $search_terms);

            $categorySearch = ProductRepository::geSearchtCategoryIds($productArrCat);

            if (!empty($categorySearch) && (isset($categorySearch->statusCode) && $categorySearch->statusCode == '200')) {
                foreach ($categorySearch->categoryIds as $catKey => $catval) {
                    array_push($categoryIdsarr, $catval->id);
                }
                // print_r($categoryIdsarr);
                // die();
            }
            if (isset($_GET['s'])) {
                $search_terms = urlencode($_GET['s']);
            }

            // print_r($categoryIdsarr);
            // die();
            $data['search_term'] = $search_terms;

            $data['page_sort_type'] = $page_sort_type;

            $data['language_arr'] = $language_arr;

            //calc offset number

            $page = (isset($_POST['page']) ? $_POST['page'] : 0);

            if ($page > 0) {

                $offset = $page;
            } else {

                $offset = 0;
            }



            $data['current_category_id'] = $cat_Id;



            $lang_code = '';

            if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {

                $lang_code = $this->session->userdata('lcode');
            }



            if (isset($LoginID)) {

                $productArr1 = array('categoryid' => $cat_Id, 'options' => $sort_val, 'subscription' => $subscription, 'price_range' => $price_range, 'variant_id_arr' => $variantId, 'variant_attr_value_arr' => $variantVal, 'attribute_arr' => $attributeArr, 'search_term' => $search_terms, 'page' => $offset, 'page_size' => $show_limit, 'customer_type_id' => $customer_type_id, 'vat_percent_session' => $vat_percent_session, 'customer_login_id' => $LoginID, 'lang_code' => $lang_code, 'categoryIdsarr' => $categoryIdsarr, 'language_arr' => $language_arr);
            } else {

                $productArr1 = array('categoryid' => $cat_Id, 'options' => $sort_val, 'subscription' => $subscription, 'price_range' => $price_range, 'variant_id_arr' => $variantId, 'variant_attr_value_arr' => $variantVal, 'attribute_arr' => $attributeArr, 'search_term' => $search_terms, 'page' => $offset, 'page_size' => $show_limit, 'customer_type_id' => $customer_type_id, 'vat_percent_session' => $vat_percent_session, 'customer_login_id' => 0, 'lang_code' => $lang_code, 'categoryIdsarr' => $categoryIdsarr, 'language_arr' => $language_arr);
            }





            $productCount = 0;

            // print_R($productArr1);
            // die();

            $main_product_list = ProductRepository::product_listing($productArr1);

            if (!empty($main_product_list) && (isset($main_product_list->statusCode) && $main_product_list->statusCode == '200')) {

                $productCount = $main_product_list->ProductListCount;
            }



            $data['product_list'] = $main_product_list;



            $identifier = 'restricted_access';

            $ApiResponse = GlobalRepository::get_custom_variable($identifier);



            if ($ApiResponse->statusCode == '200') {

                $RowCV = $ApiResponse->custom_variable;

                $restricted_access = $RowCV->value;
            } else {

                $restricted_access = 'no';
            }

            $data['restricted_access'] = $restricted_access;



            //pagination configuration

            $config['target'] = '#product-list-section';

            $config['base_url'] = BASE_URL . 'ProductsController/sort_by';

            $config['total_rows'] = $productCount;

            $config['per_page'] = $show_limit;

            $config['link_func'] = 'sort_by';

            $this->ajax_pagination->initialize($config);



            $data['current_viewmode'] = $_POST['current_viewmode'];



            $this->template->load('product/product_sort_by', $data);
        }
    }



    public function productDetails()
    {
        $data['customer_id'] = $LoginID = $this->session->userdata('LoginID');
        $customer_type_id = $this->session->userdata('CustomerTypeID');
        $data['customer_type_id'] = $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;

        $data['refresh_flag'] = '';

        $data['product_slug'] = $product_slug = $this->uri->segment(2);

        if ($product_slug != '') {
            $ProductArr = array('product_url_key' => $product_slug);
            $ResponseData = ProductRepository::product_detail($ProductArr);
            // echo '<pre>';
            // print_r($ResponseData);
            // exit;
            if (!empty($ResponseData) && isset($ResponseData) && $ResponseData->statusCode == '200') {

                $qty_identifier = 'product_detail_page_max_qty';
                $QtyApiResponse = GlobalRepository::get_custom_variable($qty_identifier);
                if (!empty($QtyApiResponse) && $QtyApiResponse->statusCode == '200') {
                    $RowCV = $QtyApiResponse->custom_variable;
                    $qty_limit = $RowCV->value;
                } else {
                    $qty_limit = 50;
                }
                $data['qty_limit'] = $qty_limit;

                $limit_identifier = 'review_display_limit';
                $limitResponse = GlobalRepository::get_custom_variable($limit_identifier);
                if (!empty($limitResponse) && $limitResponse->statusCode == '200') {
                    $RowLimit = $limitResponse->custom_variable;
                    $review_display_limit = $RowLimit->value;
                    $data['limit'] = $review_display_limit;
                } else {
                    $data['limit'] = $review_display_limit = 5;
                }


                $ProductData = $ResponseData->ProductData;

                $reviewArr = array('product_id' => $ProductData->id, 'limit' => $review_display_limit);
                $reviewResponse = ProductReviewRepository::get_product_reviews($reviewArr);


                if (!empty($reviewResponse) && isset($reviewResponse) && $reviewResponse->statusCode == '200') {
                    $data['reviewResponse'] = $reviewResponse->productReviewsList;
                    $data['reviewCountResponse'] = $reviewResponse->productReviewsCount;
                } else {
                    $data['reviewResponse'] = '';
                    $data['reviewCountResponse'] = 0;
                }
                $CatApiResponse = ProductRepository::get_product_categorys($ProductData->id);
                $data['CategoryIds'] = '';
                if (!empty($CatApiResponse) && isset($CatApiResponse) && $CatApiResponse->statusCode == '200') {
                    if ($CatApiResponse->CategoryIds != '') {
                        if ($CatApiResponse->CategoryIds->cat_ids != '') {
                            $data['CategoryIds'] = explode(',', $CatApiResponse->CategoryIds->cat_ids);
                        }
                    }
                }
                //Start Prev And Next

                if (isset($_GET['type']) && !empty($_GET['type'])) {
                    $type = $_GET['type'];
                    $search_term = isset($_GET['term']) ? $_GET['term'] : '';

                    $data['prev_url'] = '';
                    $data['next_url'] = '';

                    if ($type == "new_arrival") {
                        $post_arr = array('customer_type_id' => $customer_type_id, 'limit' => 25);
                        $SearchDetails = ProductRepository::get_new_arrivals($post_arr);
                        if (!empty($SearchDetails) && isset($SearchDetails)) {
                            foreach ($SearchDetails as $key => $value) {
                                if ($product_slug == $value->url_key) {
                                    $prev = array_key_exists($key - 1, $SearchDetails) ? $SearchDetails[$key - 1] : false;
                                    $next = array_key_exists($key + 1, $SearchDetails) ? $SearchDetails[$key + 1] : false;
                                    if ($prev) {
                                        $data['prev_url'] = $prev_url = base_url('product-detail/' . $prev->url_key . '?type=new_arrival');
                                    }
                                    if ($next) {
                                        $data['next_url'] = $next_url = base_url('product-detail/' . $next->url_key . '?type=new_arrival');
                                    }
                                }
                            }
                        }
                    } elseif ($type == "trending") {
                        $post_arr = array('identifier' => 'trending', 'customer_type_id' => $customer_type_id, 'limit' => 25);
                        $SearchDetails = ProductRepository::get_featured_products($post_arr);
                        if (!empty($SearchDetails) && isset($SearchDetails)) {
                            foreach ($SearchDetails as $key => $value) {
                                if ($product_slug == $value->url_key) {
                                    $prev = array_key_exists($key - 1, $SearchDetails) ? $SearchDetails[$key - 1] : false;
                                    $next = array_key_exists($key + 1, $SearchDetails) ? $SearchDetails[$key + 1] : false;
                                    if ($prev) {
                                        $data['prev_url'] = $prev_url = base_url('product-detail/' . $prev->url_key . '?type=trending');
                                    }
                                    if ($next) {
                                        $data['next_url'] = $next_url = base_url('product-detail/' . $next->url_key . '?type=trending');
                                    }
                                }
                            }
                        }
                    } elseif ($type == "featured") {
                        $post_arr = array('identifier' => 'featured', 'customer_type_id' => $customer_type_id, 'limit' => 25);
                        $SearchDetails = ProductRepository::get_featured_products($post_arr);
                        if (!empty($SearchDetails) && isset($SearchDetails)) {
                            foreach ($SearchDetails as $key => $value) {
                                if ($product_slug == $value->url_key) {
                                    $prev = array_key_exists($key - 1, $SearchDetails) ? $SearchDetails[$key - 1] : false;
                                    $next = array_key_exists($key + 1, $SearchDetails) ? $SearchDetails[$key + 1] : false;
                                    if ($prev) {
                                        $data['prev_url'] = $prev_url = base_url('product-detail/' . $prev->url_key . '?type=featured');
                                    }
                                    if ($next) {
                                        $data['next_url'] = $next_url = base_url('product-detail/' . $next->url_key . '?type=featured');
                                    }
                                }
                            }
                        }
                    } elseif ($type == "search" && $search_term != '') {
                        $post_arr = array('search_term' => $search_term, 'customer_type_id' => $customer_type_id);
                        $resultArr = ProductRepository::product_listing($post_arr);
                        if (!empty($resultArr) && isset($resultArr)) {
                            $SearchDetails = $resultArr->ProductList;

                            foreach ($SearchDetails as $key => $value) {
                                if ($product_slug == $value->url_key) {
                                    $prev = array_key_exists($key - 1, $SearchDetails) ? $SearchDetails[$key - 1] : false;
                                    $next = array_key_exists($key + 1, $SearchDetails) ? $SearchDetails[$key + 1] : false;
                                    if ($prev) {
                                        $data['prev_url'] = $prev_url = base_url('product-detail/' . $prev->url_key . '?type=search&term=' . $search_term);
                                    }
                                    if ($next) {
                                        $data['next_url'] = $next_url = base_url('product-detail/' . $next->url_key . '?type=search&term=' . $search_term);
                                    }
                                }
                            }
                        }
                    } elseif ($type == "all") {
                        $data['prev_url'] = '';
                        $data['next_url'] = '';

                        $resultArr = SearchRepository::get_prodcut_nextpre_products($ProductData->id, $data['customer_type_id']);
                        if (!empty($resultArr) && isset($resultArr) && $resultArr->statusCode == 200) {
                            $SearchDetails = $resultArr->ProductPrevNexList;

                            $prev = isset($SearchDetails->prev_arr) ? $SearchDetails->prev_arr : false;
                            $next = isset($SearchDetails->next_arr) ? $SearchDetails->next_arr : false;
                            if ($prev) {
                                $data['prev_url'] = $prev_url = base_url('product-detail/' . $prev->url_key . '?type=all');
                            }
                            if ($next) {
                                $data['next_url'] = $next_url = base_url('product-detail/' . $next->url_key . '?type=all');
                            }
                        }
                    } elseif ($type == "category") {
                        $current_category_id = isset($_GET['categoryId']) ? $_GET['categoryId'] : '';

                        if ($current_category_id != null) {
                            $data['prev_url'] = '';
                            $data['next_url'] = '';

                            $resultArr = SearchRepository::get_prodcut_nextpre_products_Category($ProductData->id, $data['customer_type_id'], $current_category_id);
                            if (!empty($resultArr) && isset($resultArr) && $resultArr->statusCode == 200) {
                                $SearchDetails = $resultArr->ProductPrevNexList;

                                $next = isset($SearchDetails->prev_arr) ? $SearchDetails->prev_arr : false;
                                $prev = isset($SearchDetails->next_arr) ? $SearchDetails->next_arr : false;
                                if ($prev) {
                                    $data['prev_url'] = $prev_url = base_url('product-detail/' . $prev->url_key . '?type=category&categoryId=' . $current_category_id);
                                }
                                if ($next) {
                                    $data['next_url'] = $next_url = base_url('product-detail/' . $next->url_key . '?type=category&categoryId=' . $current_category_id);
                                }
                            }
                        } else {
                            $data['prev_url'] = '';
                            $data['next_url'] = '';

                            $resultArr = SearchRepository::get_prodcut_nextpre_products($ProductData->id, $data['customer_type_id']);
                            if (!empty($resultArr) && isset($resultArr) && $resultArr->statusCode == 200) {
                                $SearchDetails = $resultArr->ProductPrevNexList;

                                $prev = isset($SearchDetails->prev_arr) ? $SearchDetails->prev_arr : false;
                                $next = isset($SearchDetails->next_arr) ? $SearchDetails->next_arr : false;
                                if ($prev) {
                                    $data['prev_url'] = $prev_url = base_url('product-detail/' . $prev->url_key);
                                }
                                if ($next) {
                                    $data['next_url'] = $next_url = base_url('product-detail/' . $next->url_key);
                                }
                            }
                        }
                    } else {
                        redirect(BASE_URL('product-detail/' . $product_slug));
                    }
                }
                if (!isset($_GET['type']) || empty($_GET['type'])) {
                    $data['prev_url'] = '';
                    $data['next_url'] = '';
                    $resultArr = SearchRepository::get_prodcut_nextpre_products($ProductData->id, $data['customer_type_id']);
                    if (!empty($resultArr) && isset($resultArr) && $resultArr->statusCode == 200) {
                        $SearchDetails = $resultArr->ProductPrevNexList;

                        $prev = isset($SearchDetails->prev_arr) ? $SearchDetails->prev_arr : false;
                        $next = isset($SearchDetails->next_arr) ? $SearchDetails->next_arr : false;
                        if ($prev) {
                            $data['prev_url'] = $prev_url = base_url('product-detail/' . $prev->url_key);
                        }
                        if ($next) {
                            $data['next_url'] = $next_url = base_url('product-detail/' . $next->url_key);
                        }
                    }
                }

                //End

                $data['ProductData'] = $ProductData;
                $data['CatApiResponse'] = $CatApiResponse;
                // echo "<pre>";
                // print_r($data['ProductData']);
                // die();

                if (isset($ProductData->other_lang_name) && $ProductData->other_lang_name != '') {
                    $data['PageTitle'] = $ProductData->other_lang_name;
                } else {
                    $data['PageTitle'] = $ProductData->name . " - " . $ProductData->publication_name;
                }
                if ($ProductData->meta_title != "") {
                    $data['PageMetaTitle'] = $ProductData->meta_title . " - " . $ProductData->publication_name;
                }
                if ($ProductData->meta_keyword != "") {
                    $data['PageMetaKey'] = $ProductData->meta_keyword . " - " . $ProductData->publication_name;
                }
                if ($ProductData->meta_description != "") {
                    $data['PageMetaDesc'] = $ProductData->meta_description . " - " . $ProductData->publication_name;
                }

                $identifier = 'restricted_access';
                $ApiResponse = GlobalRepository::get_custom_variable($identifier);

                if (!empty($ApiResponse) && isset($ApiResponse) && $ApiResponse->statusCode == '200') {
                    $RowCV = $ApiResponse->custom_variable;
                    $restricted_access = $RowCV->value;
                } else {
                    $restricted_access = 'no';
                }
                $data['restricted_access'] = $restricted_access;
                $data['rating'] = $this->Shop_model->get_avg_ratings_by_merchant($ProductData->publisher_id);
                // echo "<pre>";
                // print_r($data['rating']);
                // die();
                $customer_name = '';
                if (!empty($LoginID)) {
                    $this->db->select('first_name, last_name');
                    $this->db->from('customers');
                    $this->db->where('id', $LoginID);
                    $customer = $this->db->get()->row();

                    if ($customer) {
                        $customer_name = trim($customer->first_name . ' ' . $customer->last_name);
                    }
                }
                $data['customer_name'] = $customer_name;

                $this->template->load('product/product_details', $data);
            } else {
                redirect(BASE_URL('404_override'));
                // show_404();
            }
        } else {
            redirect(BASE_URL('404_override'));
            // show_404();
        }
    }

    public function saveQuestion()
    {
        $name = $this->input->post('name');
        $category = $this->input->post('category');
        $email = $this->input->post('email') ?? '';
        $message = $this->input->post('message');
        $product_id = $this->input->post('product_id');
        $merchant_id = $this->input->post('merchant_id');
        $customer_id = $this->input->post('customer_id');

        $data = [
            'name' => $name,
            'category' => $category,
            'email' => $email,
            'message' => $message,
            'product_id' => $product_id,
            'merchant_id' => $merchant_id,
            'customer_id' => $customer_id,
            'created_at' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];

        $this->db->insert('product_questions', $data);

        echo json_encode(['flag' => 1, 'msg' => 'Enquire Sent Sucessfully.']);
    }

    public function getMediaVariantProduct()
    {


        $product_id = $_POST['product_id'];
        $attr_option_value = $_POST['attr_option_value'];
        $media_variant_id = $_POST['media_variant_id'];

        $galleryImageByVariantArr = array('product_id' => $product_id, 'attr_option_value' => $attr_option_value, 'media_variant_id' => $media_variant_id);
        $gImgResponse = ProductRepository::get_gallery_images_by_variants($galleryImageByVariantArr);
        if (isset($gImgResponse) && $gImgResponse->statusCode == '200' && $gImgResponse->MediaGalleryData->media_count > 0) {
            $data['ProductData'] = $gImgResponse->MediaGalleryData;
            if ($gImgResponse->MediaGalleryData->media_count == 1) {
                $product_base_img = $gImgResponse->MediaGalleryData->base_image;
                $var_img = $gImgResponse->MediaGalleryData->mediaGallery[0]->image;
                if ($product_base_img == $var_img) {
                    $media_count = 0;
                } else {
                    $media_count = $gImgResponse->MediaGalleryData->media_count;
                }
            } else {
                $media_count = $gImgResponse->MediaGalleryData->media_count;
            }

            $data['refresh_flag'] = 'config-refresh';

            $mediaGallery = $this->load->view('product/media_gallery', $data, true);
            $arrResponse = array('flag' => 1, 'mediaGallery' => $mediaGallery);
            echo json_encode($arrResponse);
            exit;
        }
    }



    public function getVariantProduct()
    {

        if ($_POST['product_id'] != '' && count($_POST['selected_variant']) > 0) {

            $customer_type_id = $this->session->userdata('CustomerTypeID');
            $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;

            $product_id = $_POST['product_id'];
            $total_variant = $_POST['total_variant'];
            $selected_variant = $_POST['selected_variant'];
            $media_variant_id = $_POST['media_variant_id'];

            $ConfSimpleParam = array('product_id' => $product_id, 'selected_variant' => $selected_variant, 'total_variant' => $total_variant, 'customer_type_id' => $customer_type_id);
            $confsimpleApiUrl = '/webshop/get_conf_simprod_by_variants';
            // $CSResponse = $this->restapi->post_method($confsimpleApiUrl, $ConfSimpleParam);
            $CSResponse = ProductRepository::get_conf_simprod_by_variants($ConfSimpleParam);
            //  print_r($CSResponse);exit;
            $qty_identifier = 'product_detail_page_max_qty';
            $customVariableApiUrl = '/webshop/get_custom_variable/' . $qty_identifier; //Global API - customvariable
            // $QtyApiResponse = $this->restapi->get_method($customVariableApiUrl);
            $QtyApiResponse = ProductRepository::get_custom_variable($qty_identifier);
            //   print_r($QtyApiResponse);exit;

            if ($QtyApiResponse->statusCode == '200') {
                $RowCV = $QtyApiResponse->custom_variable;
                $qty_limit = $RowCV->value;
            } else {
                $qty_limit = 50;
            }
            $data['qty_limit'] = $qty_limit;
            //print_r($CSResponse);exit;

            if (isset($CSResponse) && $CSResponse->statusCode == '200') {
                $message = $CSResponse->message;
                $ConfigSimpleDetails = $CSResponse->ConfigSimpleDetails;
                $mediaGallery = '';
                $media_count = '';
                // if($media_variant_id == 0) {
                //     $galleryImageByVariantArr = array('product_id'=>$product_id,'child_product_id'=>$ConfigSimpleDetails->conf_simple_pro_id,'media_variant_id'=>$media_variant_id);
                //     $gImgResponse=ProductRepository::get_gallery_images_by_variants($galleryImageByVariantArr);


                //     if (isset($gImgResponse) && $gImgResponse->statusCode=='200' && $gImgResponse->MediaGalleryData->media_count > 0) {
                //         $data['ProductData'] = $gImgResponse->MediaGalleryData;

                //         if ($gImgResponse->MediaGalleryData->media_count==1) {
                //             $product_base_img=$gImgResponse->MediaGalleryData->base_image;
                //             $var_img=$gImgResponse->MediaGalleryData->mediaGallery[0]->image;
                //             if ($product_base_img==$var_img) {
                //                 $media_count=0;
                //             } else {
                //                 $media_count=$gImgResponse->MediaGalleryData->media_count;
                //             }
                //         } else {
                //             $media_count=$gImgResponse->MediaGalleryData->media_count;
                //         }



                //         $data['refresh_flag'] = 'config-refresh';

                //         $mediaGallery = $this->load->view('product/media_gallery', $data, true);
                //     }
                // }

                $arrResponse = array('status' => 200, 'message' => $message, 'ConfigSimpleDetails' => $ConfigSimpleDetails, 'mediaGallery' => $mediaGallery, 'media_count' => $media_count, 'qty_limit' => $qty_limit);
            } else {
                $message = $CSResponse->message;

                $arrResponse = array('status' => 403, 'message' => $message);
            }
        } else {
            $arrResponse = array('status' => 403, 'message' => 'Something went wrong.');
        }
        echo json_encode($arrResponse);
        exit;
    }

    public function getVariantProductNew()
    {

        if ($_POST['product_id'] != '') {

            $customer_type_id = $this->session->userdata('CustomerTypeID');
            $customer_type_id = isset($customer_type_id) ? $customer_type_id : 1;

            $parent_id = $_POST['parent_id'];
            $product_id = $_POST['product_id'];
            $total_variant = $_POST['total_variant'];
            $selected_variant = $_POST['selected_variant'];
            $media_variant_id = $_POST['media_variant_id'];

            $ConfSimpleParam = array('product_id' => $product_id, 'parent_id' => $parent_id, 'selected_variant' => $selected_variant, 'total_variant' => $total_variant, 'customer_type_id' => $customer_type_id);
            $confsimpleApiUrl = '/webshop/get_conf_simprod_by_variants_new';
            // $CSResponse = $this->restapi->post_method($confsimpleApiUrl, $ConfSimpleParam);
            $CSResponse = ProductRepository::get_conf_simprod_by_variants_new($ConfSimpleParam);
            // print_r($CSResponse);
            // exit;
            $qty_identifier = 'product_detail_page_max_qty';
            $customVariableApiUrl = '/webshop/get_custom_variable/' . $qty_identifier; //Global API - customvariable
            // $QtyApiResponse = $this->restapi->get_method($customVariableApiUrl);
            $QtyApiResponse = ProductRepository::get_custom_variable($qty_identifier);
            //   print_r($QtyApiResponse);exit;

            if ($QtyApiResponse->statusCode == '200') {
                $RowCV = $QtyApiResponse->custom_variable;
                $qty_limit = $RowCV->value;
            } else {
                $qty_limit = 50;
            }
            $data['qty_limit'] = $qty_limit;
            // print_r($CSResponse);
            // exit;

            if (isset($CSResponse) && $CSResponse->statusCode == '200') {
                $message = $CSResponse->message;
                $ConfigSimpleDetails = $CSResponse->ConfigSimpleDetails;
                $mediaGallery = '';
                $media_count = '';
                // if($media_variant_id == 0) {
                //     $galleryImageByVariantArr = array('product_id'=>$product_id,'child_product_id'=>$ConfigSimpleDetails->conf_simple_pro_id,'media_variant_id'=>$media_variant_id);
                //     $gImgResponse=ProductRepository::get_gallery_images_by_variants($galleryImageByVariantArr);


                //     if (isset($gImgResponse) && $gImgResponse->statusCode=='200' && $gImgResponse->MediaGalleryData->media_count > 0) {
                //         $data['ProductData'] = $gImgResponse->MediaGalleryData;

                //         if ($gImgResponse->MediaGalleryData->media_count==1) {
                //             $product_base_img=$gImgResponse->MediaGalleryData->base_image;
                //             $var_img=$gImgResponse->MediaGalleryData->mediaGallery[0]->image;
                //             if ($product_base_img==$var_img) {
                //                 $media_count=0;
                //             } else {
                //                 $media_count=$gImgResponse->MediaGalleryData->media_count;
                //             }
                //         } else {
                //             $media_count=$gImgResponse->MediaGalleryData->media_count;
                //         }



                //         $data['refresh_flag'] = 'config-refresh';

                //         $mediaGallery = $this->load->view('product/media_gallery', $data, true);
                //     }
                // }

                $arrResponse = array('status' => 200, 'message' => $message, 'ConfigSimpleDetails' => $ConfigSimpleDetails, 'mediaGallery' => $mediaGallery, 'media_count' => $media_count, 'qty_limit' => $qty_limit);
            } else {
                $message = $CSResponse->message;

                $arrResponse = array('status' => 403, 'message' => $message);
            }
        } else {
            $arrResponse = array('status' => 403, 'message' => 'Something went wrong.');
        }
        echo json_encode($arrResponse);
        exit;
    }

    public function productNotified()
    {

        $LoginID = $_SESSION['LoginID'] ?? '0';


        if (!empty($_POST)) {

            $data['LoginID'] = $_SESSION['LoginID'] ?? '0';

            // insert data

            $product_id = $_POST['product_id'] ?? '';

            $email = $_POST['email'] ?? '';

            $data['email'] = $email;

            $customer_id = $LoginID;

            $notifiedArray = array('product_id' => $product_id, 'email' => $email, 'customer_id' => $customer_id);

            $product_notified_data = CommonRepository::add_email_notified($notifiedArray);

            return $this->template->load('product/product_notified_popup.php', $data);
        }
    }



    public function productNotifiedSubscribe()
    {

        if (!empty($_POST)) {

            $email = $_POST['email'];

            $response = HomeDetailsRepository::newsletter_subscribe($email);

            if (isset($response) && !empty($response) && $response->is_success == 'true') {

                echo 1;
            } else {

                echo 0;
            }
        }
    }



    public function getBundleChildValidateQty()
    {



        if ($this->session->userdata('QuoteId')) {

            $quote_id = $this->session->userdata('QuoteId');
        } else {

            $quote_id = '';
        }



        $conf_simple_array = (isset($_POST['conf_simple_array']) ? $_POST['conf_simple_array'] : array());

        $bundle_chid_ids = (isset($_POST['bundle_chid_ids']) ? $_POST['bundle_chid_ids'] : '');

        $BundleQtyArr = array('qty' => $_POST['qty'], 'main_bundle_id' => $_POST['product_id'], 'bundle_products_ids' => $bundle_chid_ids, 'conf_simple_array' => $conf_simple_array, 'quote_id' => $quote_id);

        $response = ProductRepository::getBundleChildValidateQty($BundleQtyArr);

        if (isset($response) && !empty($response) && $response->is_success == 'true') {

            $arrResponse = array('status' => 200, 'message' => $response->message);
        } else {

            $arrResponse = array('status' => 403, 'message' => $response->message);
        }



        echo json_encode($arrResponse);

        exit;
    }



    public function getSimpleValidateQty()
    {

        if ($this->session->userdata('QuoteId')) {
            $quote_id = $this->session->userdata('QuoteId');
        } else {

            $quote_id = '';
        }

        $BundleQtyArr = array('qty' => $_POST['qty'], 'product_id' => $_POST['product_id'], 'parent_product_id' => $_POST['parent_product_id'], 'quote_id' => $quote_id);
        $response = ProductRepository::getSimpleValidateQty($BundleQtyArr);
        if (isset($response) && !empty($response) && $response->is_success == 'true') {
            $arrResponse = array('status' => 200, 'message' => $response->message);
        } else {
            $arrResponse = array('status' => 403, 'message' => $response->message);
        }
        echo json_encode($arrResponse);

        exit;
    }
}