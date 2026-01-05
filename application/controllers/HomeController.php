<?php

defined('BASEPATH') or exit('No direct script access allowed');



class HomeController extends CI_Controller

{
    public function __construct() {

        parent::__construct();

        $this->load->model('Product_model');
        $this->load->model('CommonModel');


        $this->load->library('pagination');

        $this->load->helper(['url', 'form', 'language']);

        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }

    }

    /*public function payment_status_show($orderId = null) {
        if (!$orderId) {
            show_error('Invalid payment reference.');
        }

        // Fetch order from your orders table
        $order = $this->db->where('id', $orderId)->get('subscription_orders')->row_array();

        if (!$order) {
            show_error('No order found for this reference.');
        }

        // Pass data to view
        $data['order'] = $order;
        $this->load->view('payment_status_view', $data);
    }*/

    public function payment_status_show($encryptedId = null) {
    if (!$encryptedId) {
        show_error('Invalid payment reference.');
    }

    $this->load->library('encryption');

    // Restore URL-safe characters
    $encryptedId = strtr($encryptedId, '-_', '+/');
    $pad = strlen($encryptedId) % 4;
    if ($pad) $encryptedId .= str_repeat('=', 4 - $pad);

    // Decode and decrypt
    $decrypted = base64_decode($encryptedId);
    $orderId = $this->encryption->decrypt($decrypted);

    if (!$orderId || !is_numeric($orderId)) {
        show_error('Invalid payment reference.');
    }

    // Fetch order
    $order = $this->db->where('id', $orderId)->get('subscription_orders')->row_array();
   
    if (!$order) {
        show_error('No order found for this reference.');
    }

    $data['order'] = $order;
    $this->load->view('payment_status_view', $data);
}


    public function index()

    {

        $data['customer_type_id'] = $this->session->userdata('CustomerTypeID') ?? 1;

        $current_time = time();

        $limit = 12;

        $offset = (int) $this->input->get('per_page');
        $category_id = null;


        $data['daily_deals_products'] = $this->Product_model->get_daily_deals($limit, $offset, $current_time, $category_id);
        $data['flash_sale_products'] = $this->Product_model->get_flash_sales($limit, $offset, $current_time, $category_id);

        $this->template->load('home', $data);

    }





    public function login()

	{

        

		$data['PageTitle']= 'Login';

		$this->template->load('merchant_login', $data);

	}



	public function register()

	{

		

		$data['PageTitle']= 'Register';

		$this->template->load('merchant_register', $data);



	}

    /*public function Pages()

    {

        $identifier = $this->uri->segment(2);

        $lang_code = '';



        $data['pagedata'] = $pagedata =  CmsRepository::get_cms_page($identifier);



        $data['identifier'] = $identifier;

        if ($pagedata->statusCode == '200') {

            $data['PageTitle'] = $pagedata->cms_page_detail->title;



            if ($pagedata->cms_page_detail->meta_title != "") {

                $data['PageMetaTitle'] = $pagedata->cms_page_detail->meta_title;

            }

            if ($pagedata->cms_page_detail->meta_keywords != "") {

                $data['PageMetaKey'] = $pagedata->cms_page_detail->meta_keywords;

            }

            if ($pagedata->cms_page_detail->meta_description != "") {

                $data['PageMetaDesc'] = $pagedata->cms_page_detail->meta_description;

            }

        }

        $cmsData = $pagedata->cms_page_detail;

        if (isset($cmsData) && $cmsData->remove_flag != 1) {

            $this->template->load('common/cms_page', $data);

        } else {

            redirect(BASE_URL);

        }

    }*/

       public function Pages()
{
    $identifier = $this->uri->segment(2);

    // Detect current language (default: English)
    $lang_code = $this->session->userdata('site_lang') ?? 'en';
 
    $data['current_lang'] = $lang_code;
    $data['identifier'] = $identifier;

    // Fetch CMS Page data
    $pagedata = CmsRepository::get_cms_page($identifier);
    $data['pagedata'] = $pagedata;

    if (!isset($pagedata->statusCode) || $pagedata->statusCode != '200') {
        redirect(BASE_URL);
        return;
    }

    $cmsData = $pagedata->cms_page_detail ?? null;
    if (!$cmsData || $cmsData->remove_flag == 1) {
        redirect(BASE_URL);
        return;
    }

    // Page Title (check French version)
    $data['PageTitle'] = ($lang_code == 'french' && !empty($cmsData->lang_title))
        ? $cmsData->lang_title
        : $cmsData->title;

    // Meta Tags
    $data['PageMetaTitle'] = $cmsData->meta_title ?? '';
    $data['PageMetaKey'] = $cmsData->meta_keywords ?? '';
    $data['PageMetaDesc'] = $cmsData->meta_description ?? '';

    // Load CMS page template
    $this->template->load('common/cms_page', $data);
}




    public function CheckValidityCouponCode()

    {

        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;

        if (!empty($_POST['coupon_code'])) {

            if (isset($_POST['nickname']) && $_POST['nickname'] != '') {

                echo json_encode(array('flag' => 0, 'msg' => "Are you a bot?"));

                exit;

            } else {

                $coupon_code = $_POST['coupon_code'];

                $couponArr = array('coupon_code' => $coupon_code);

                $ResponseData = CmsRepository::check_validity_coupon_code($shopcode, $shop_id, $couponArr);

                if (!empty($ResponseData) && isset($ResponseData) && $ResponseData->statusCode == '200') {

                    $success = $ResponseData->message;

                    echo json_encode(array('flag' => 1, 'msg' => $success));

                    exit;

                } else {

                    $error = $ResponseData->message;

                    echo json_encode(array('flag' => 0, 'msg' => $error));

                    exit;

                }

            }

        } else {

            echo json_encode(array('flag' => 0, 'msg' => "Please enter coupon code."));

            exit;

        }

    }



    public function newsletterSubscribe()

    {

        if (empty($_POST)) {

            $this->index();

        } else {

            if (isset($_POST) && $_POST['email-subscribe-btn'] == 'Send') {

                if (empty($_POST['email_subscribe'])) {

                    echo json_encode(array('flag' => 0, 'msg' => "Please enter your email address."));

                    exit;

                } elseif (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email_subscribe"])) {

                    echo json_encode(array('flag' => 0, 'msg' => "Please enter a valid Email address."));

                    exit;

                } else {

                    $email = $_POST['email_subscribe'];



                    $data['response'] = $response =  HomeDetailsRepository::newsletter_subscribe($email);



                    if ($response->is_success == 'true') {

                        $message = $response->message;

                        echo json_encode(array('flag' => 1, 'msg' => $message));

                        exit;

                    } else {

                        $message = $response->message;

                        echo json_encode(array('flag' => 0, 'msg' => $message));

                        exit;

                    }

                }

            }

        }

    }



    public function restricted_access()

    {

        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;

        $identifier = 'msg_for_customer';

        $data['ApiResponse'] = $ApiResponse =  GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);



        if ($ApiResponse->statusCode == '200') {

            $RowCV = $ApiResponse->custom_variable;

            $msg_for_customer = $RowCV->value;

        } else {

            $msg_for_customer = '';

        }

        $data['msg_for_customer'] = $msg_for_customer;

        $View = $this->load->view('common/restricted_access_popup', $data, true);

        $this->output->set_output($View);

    }



    public function updateCurrentCurrency()

    {

        if (empty($_POST)) {

            echo json_encode(array('flag' => 0, 'msg' => 'Please select currency.'));

            exit;

        } else {

            $shopcode = SHOPCODE;

            $shop_id = SHOP_ID;

            $currency_id = $_POST['currency_id'];



            $post_arr = array('currency_id' => $currency_id);



            $getCurrency = CurrencyRepository::getCurrencyData($shopcode, $shop_id, $post_arr);

            if (isset($getCurrency) && $getCurrency->statusCode == '200') {

                $currencydata = $getCurrency->currencydata;



                $sessionArr = array('default_currency_flag' => $currencydata->is_default_currency, 'currency_name' => $currencydata->name, 'currency_code_session' => $currencydata->code, 'currency_conversion_rate' => $currencydata->conversion_rate, 'currency_symbol' => $currencydata->symbol);

                $this->session->set_userdata($sessionArr);



                $this->load->helper('cookie');

                $this->input->set_cookie('site_currency_id', $currencydata->id, time() + 60 * 60 * 24 * 365, '', '/', '', ENVIRONMENT === 'production', true);



                $QuoteId = $this->session->userdata('QuoteId');

                if (isset($QuoteId) && $QuoteId > 0) {

                    $currency_post_arr = array(

                        'currency_name' => $this->session->userdata('currency_name'),

                        'currency_code_session' => $this->session->userdata('currency_code_session'),

                        'currency_conversion_rate' => $this->session->userdata('currency_conversion_rate'),

                        'currency_symbol' => $this->session->userdata('currency_symbol'),

                        'default_currency_flag' => $this->session->userdata('default_currency_flag'),

                        'quote_id' => $QuoteId

                    );

                    $updateQuoteCurrency = CurrencyRepository::updateQuoteCurrenyData($shopcode, $shop_id, $currency_post_arr);

                }



                echo json_encode(array('flag' => 1, 'msg' => 'Currency Updated.'));

                exit;

            } else {

                echo json_encode(array('flag' => 0, 'msg' => 'No currency found.'));

                exit;

            }

        }

    }



    /*function updateCurrentLanguage()

    {

        if (empty($_POST)) {

            echo json_encode(array('flag' => 0, 'msg' => 'Please select language.'));

            exit;

        }



        $language_id = $_POST['language_id'];



        if (!empty($language_id) && isset($language_id)) {

            $post_org_name_array = [

                'table_name' => 'multi_languages',

                'database_flag' => 'own',

                'where' => 'id = ?',

                'params' => [$language_id]

            ];

            $getLanguage = CommonRepository::get_multi_language_data(SHOPCODE, SHOP_ID, $post_org_name_array);



            if (!empty($getLanguage) && isset($getLanguage) && $getLanguage->statusCode == '200') {

                $languageData = (array)$getLanguage->tableData[0];



                if ($languageData) {

                    $this->session->set_userdata([

                        'lid' => $languageData['id'],

                        'site_lang' => strtolower($languageData['name']),

                        'ldisplay_name' => $languageData['display_name'],

                        'lcode' => $languageData['code'],

                        'lis_default_language' => $languageData['is_default_language']

                    ]);



                    $this->load->helper('cookie');

                    $this->input->set_cookie('site_language', $languageData['code'], time() + 60 * 60 * 24 * 365, '', '/', '', ENVIRONMENT === 'production', true);

                }

            }



            echo json_encode(array('flag' => 1, 'msg' => 'Language Updated.'));

            exit;

        }



        echo json_encode(array('flag' => 0, 'msg' => 'No language found.'));

        exit;

    }*/


        public function updateCurrentLanguage()
{
    if (empty($_POST['language_id'])) {
        echo json_encode(['flag' => 0, 'msg' => 'Please select a language.']);
        exit;
    }

    $language_id = $_POST['language_id'];

    // Define static languages (since no DB table exists)
    $languages = [
        1 => [
            'id' => 1,
            'name' => 'english',
            'display_name' => 'English',
            'code' => 'en',
            'is_default_language' => 1
        ],
        2 => [
            'id' => 2,
            'name' => 'french',
            'display_name' => 'Français',
            'code' => 'fr',
            'is_default_language' => 0
        ]
    ];

    if (!isset($languages[$language_id])) {
        echo json_encode(['flag' => 0, 'msg' => 'Invalid language selected.']);
        exit;
    }

    $languageData = $languages[$language_id];

    // Store in session
    $this->session->set_userdata([
        'lid' => $languageData['id'],
        'site_lang' => strtolower($languageData['name']),
        'ldisplay_name' => $languageData['display_name'],
        'lcode' => $languageData['code'],
        'lis_default_language' => $languageData['is_default_language']
    ]);

    // Also store cookie
    $this->load->helper('cookie');
    $this->input->set_cookie('site_language', $languageData['code'], time() + 60 * 60 * 24 * 365, '', '/', '', ENVIRONMENT === 'production', true);

    echo json_encode(['flag' => 1, 'msg' => 'Language Updated.']);
    exit;
}


    public function test_cache_contents()

    {

        var_dump($this->cache->cache_info());

    }

    public function forgotPassword()
    {
        
        $data['PageTitle'] = 'Forgot Password';
        if (empty($_POST)) {
            $this->template->load('merchant_forgot_password', $data);
        } else {
            if (isset($_POST)) {
                if (empty($_POST['email'])) {
                    echo json_encode(array('flag' => 0, 'msg' => "Please enter your email address."));
                    exit;
                } elseif (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email"])) {
                    echo json_encode(array('flag' => 0, 'msg' => "Please enter a valid Email address."));
                    exit;
                } else {
                    $lang_code = '';
                    if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
                        $lang_code = $this->session->userdata('lcode');
                    }

                    $site_url = base_url();
                    $email = $_POST['email'];
                    // $shopcode = SHOPCODE;
                    // $shop_id = SHOP_ID;



                    $webshopname = 'India Mags';
                    $shop_logo = '';

                    $data['webshop_details'] = CommonRepository::get_webshop_details();
                    if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
                        // $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                        $webshopname = $data['webshop_details']->FbcWebShopDetails->site_name;
                    }
                    $shop_logo = SITE_LOGO;
                    $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
							<img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
						</a>';

                    $postArr = array('email' => $email, 'site_logo' => $site_logo, 'site_url' => $site_url, 'lang_code' => $lang_code);
                    $response = LoginRepository::merchant_forgot_password($postArr);
                    //echo '<pre>';print_r($response);exit;

                    if (!empty($response) && (isset($response) && $response->is_success == 'true')) {
                        $userdetails = $response->userdetails;
                        $encoded_data = $userdetails->encoded_data;
                        $reset_url = BASE_URL . "merchants/reset-password/" . $encoded_data;
                        //$this->session->set_flashdata('reset_link',$reset_url);

                        $redirect = BASE_URL . "merchants/login";
                        echo json_encode(array('flag' => 1, 'msg' => "Password reset link has been sent successfully", 'redirect' => $redirect));
                        exit;
                    } else {
                        $message = "Password reset link has been not sent";
                        echo json_encode(array('flag' => 0, 'msg' => $message));
                        exit;
                    }
                }
            }
        }
    }

    public function resetPassword()
    {
        
        $urlData = $this->uri->segment(3);
        if (empty($urlData)) {
            redirect(BASE_URL . 'merchants/login');
        } else {
            if (empty($_POST)) {
                $data['urlData'] = $urlData;
                $data['PageTitle'] = 'Reset Password';
                $this->template->load('merchant_reset_password', $data);
            } else {
                // if ($_POST['reset-password-btn'] == 'Submit') {
                if (empty($_POST['password']) || empty($_POST['conf_password'])) {
                    echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                    exit;
                } elseif ($_POST['password'] != $_POST['conf_password']) {
                    echo json_encode(array('flag' => 0, 'msg' => "Confirm Password does not match."));
                    exit;
                } else {
                    $decoded_data = json_decode(base64_decode($urlData), true);
                    $email = $decoded_data['token'];
                    $password = $_POST['password'];
                    $shopcode = SHOPCODE;
                    $shop_id = SHOP_ID;

                    $apiUrl = '/webshop/merchant_reset_password'; //reset password
                    $postArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'email' => $email, 'password' => $password);
                    $response = LoginRepository::merchant_reset_password($postArr);
                    // $response = $this->restapi->post_method($apiUrl, $postArr);
                    // print_r($response);
                    // exit;
                    if (!empty($response) && (isset($response) && $response->is_success == 'true')) {
                        $message = $response->message;
                        $redirect = BASE_URL . "merchants/login";
                        echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect' => $redirect));
                        exit;
                    } else {
                        $message = "Password does not changed.";
                        echo json_encode(array('flag' => 0, 'msg' => $message));
                        exit;
                    }
                }
                /*} else {
                    echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
                    exit;
                }*/
            }
        }
    }


    public function homepage_cateory_block_product_data_ajax()

    {

        $data = "";

        $lang_code = $this->session->userdata('lcode') ?? '';

        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');



        $category_id = str_replace(',', '', $_POST['categoryid']);

        $CustomerTypeID = $this->session->userdata('CustomerTypeID') ?? '';



        $data = array('categoryid' => $category_id, 'page' => 1, 'page_size' => 3, 'vat_percent_session' => $vat_percent_session, 'lang_code' => $lang_code, 'CustomerTypeID' => $CustomerTypeID);

        $product_list = ProductRepository::product_listing(SHOPCODE, SHOP_ID, $data);



        if (isset($product_list->ProductList) && $product_list->ProductList != '') {

            foreach ($product_list->ProductList as $product_data) {

                return $this->template->load('components/product_fetch_for_home_block', ['product_list' => $product_list->ProductList, 'category_id' => $category_id]);

            }

        }

        exit();

    }
  
    public function trendingProducts()
    {
        $this->load->library('pagination');

        // Current page
        $page = $this->input->get('page') ?? 1;
        $limit = 9; // show 9 products per page
        $offset = ($page - 1) * $limit;

        // Fetch all products (API does not support offset)
        $params = [
            'identifier'          => 'recent_popular',
            'customer_type_id'    => $this->session->userdata('CustomerTypeID') ?? 1,
            'limit'               => 0, // fetch all
            'vat_percent_session' => $this->session->userdata('vat_percent') ?? '',
            'lang_code'           => $this->session->userdata('lcode') ?? '',
            'customer_login_id'   => $this->session->userdata('LoginID') ?? ''
        ];
        $allProducts = ProductRepository::get_featured_products($params);
        $total = count($allProducts);

        // Slice products manually
        $featured_product = array_slice($allProducts, $offset, $limit);

        // Pagination config
        $config['base_url'] = site_url('trending-products');
        $config['total_rows'] = $total;
        $config['per_page'] = $limit;
        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;

        // Bootstrap 4 pagination style
        $config['full_tag_open']   = '<ul class="pagination justify-content-center">';
        $config['full_tag_close']  = '</ul>';
        $config['attributes']      = ['class' => 'page-link'];
        $config['first_link']      = 'First';
        $config['last_link']       = 'Last';
        $config['first_tag_open']  = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open']   = '<li class="page-item">';
        $config['last_tag_close']  = '</li>';
        $config['next_tag_open']   = '<li class="page-item">';
        $config['next_tag_close']  = '</li>';
        $config['prev_tag_open']   = '<li class="page-item">';
        $config['prev_tag_close']  = '</li>';
        $config['cur_tag_open']    = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close']   = '</span></li>';
        $config['num_tag_open']    = '<li class="page-item">';
        $config['num_tag_close']   = '</li>';

        $this->pagination->initialize($config);

        $data['featured_product'] = $featured_product;
        $data['pagination_links'] = $this->pagination->create_links();

        $this->load->view('components/trending_products', $data);
    }
    public function newarrivalProducts()
    {
        $this->load->library('pagination');

        // Current page
        $page = $this->input->get('page') ?? 1;
        $limit = 9;
        $offset = ($page - 1) * $limit;

        // Fetch all products first (to get total count)
        $params_all = [
            'customer_type_id'    => $this->session->userdata('CustomerTypeID') ?? 1,
            'limit'               => 0,  // 0 = fetch all
            'vat_percent_session' => $this->session->userdata('vat_percent') ?? '',
            'lang_code'           => $this->session->userdata('lcode') ?? '',
            'customer_login_id'   => $this->session->userdata('LoginID') ?? ''
        ];
        $allProductsResponse = ProductRepository::get_new_arrivals($params_all);

        // Check if response is success and products exist
        $allProducts = isset($allProductsResponse->NewArrivalProduct) ? $allProductsResponse->NewArrivalProduct : [];

        $total = count($allProducts);

        // Slice for current page
        $newarrival_product = array_slice($allProducts, $offset, $limit);

        // Pagination config
        $config['base_url'] = site_url('newarrival-products');
        $config['total_rows'] = $total;
        $config['per_page'] = $limit;
        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;

        // Bootstrap 4 pagination styling
        $config['full_tag_open']   = '<ul class="pagination justify-content-center">';
        $config['full_tag_close']  = '</ul>';
        $config['attributes']      = ['class' => 'page-link'];
        $config['first_link']      = 'First';
        $config['last_link']       = 'Last';
        $config['first_tag_open']  = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open']   = '<li class="page-item">';
        $config['last_tag_close']  = '</li>';
        $config['next_tag_open']   = '<li class="page-item">';
        $config['next_tag_close']  = '</li>';
        $config['prev_tag_open']   = '<li class="page-item">';
        $config['prev_tag_close']  = '</li>';
        $config['cur_tag_open']    = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close']   = '</span></li>';
        $config['num_tag_open']    = '<li class="page-item">';
        $config['num_tag_close']   = '</li>';

        $this->pagination->initialize($config);

        $data['newarrival_product'] = $newarrival_product;
        $data['pagination_links'] = $this->pagination->create_links();
        // echo "<pre>"; print_r($data);die;
        

        $this->load->view('components/newarrival_products', $data);
    }

    public function faqs()
    {
        $data['PageTitle']= 'Your Information';
        // $this->template->load('common/faqs', $data);
        $data['faq_list'] = $this->CommonModel->getFaqData();
        // echo "<pre>";print_r($data);die;
        $this->load->view('common/faqs', $data);

    }
   public function faqs_post()
    {
        // Step 1: Validate required fields
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['question'])) {
            $this->session->set_flashdata('error', "Please enter all mandatory / compulsory fields.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        // Step 2: Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', "Please enter a valid Email address.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        // Step 3: reCAPTCHA validation
        $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        if (empty($recaptchaResponse)) {
            $this->session->set_flashdata('error', "Please complete the reCAPTCHA.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        $verifyResponse = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?secret=' . RECAPTCHA_SECRETE_KEY_V2 .
            '&response=' . $recaptchaResponse
        );
        $responseData = json_decode($verifyResponse);
        if (empty($responseData->success) || !$responseData->success) {
            $this->session->set_flashdata('error', "reCAPTCHA verification failed.");
            redirect($_SERVER['HTTP_REFERER']);
        }

        // Step 4: Prepare data for insertion
        $postArr = [
            'name'       => $_POST['name'],
            'email'      => $_POST['email'],
            'question'   => $_POST['question'],
            'created_at' => strtotime(date('Y-m-d H:i:s')),
            'ip'         => $_SERVER['REMOTE_ADDR'],
        ];

        // Step 5: Insert into 'faqs' table
        $this->db->insert('faqs', $postArr);
        $insert_id = $this->db->insert_id();
        // $this->session->set_flashdata('success', "Your question has been submitted successfully.");

        if($insert_id){
            echo json_encode(['flag'=>1,'msg'=>'Your question has been submitted successfully.']);
        } else {
            echo json_encode(['flag'=>0,'msg'=>'Failed to submit ticket.']);
        }
    }


}

