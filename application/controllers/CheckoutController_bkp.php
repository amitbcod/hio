<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CheckoutController extends CI_Controller
{
    function  __construct()
    {
        parent::__construct();
        $this->load->library('paypal_lib');
        $this->load->library('email');
        $this->load->model('CommonModel');
        $this->load->model('Giftcard_model');
    }

    public function index()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $quote_id = '';
        $session_id = '';
        $customer_id = 0;

        /*start shop_flag*/
        //shop flag
        $webshop_name_shop = GlobalRepository::get_fbc_users_shop();
        $data['shop_flag_shop'] = $webshop_name_shop->result->shop_flag ?? '';
        $data['acc_inv_flag'] = $webshop_name_shop->result->acc_inv_flag ?? '';
        /*end shop_flag*/

        if ($this->session->userdata('QuoteId')) {
            $quote_id = $this->session->userdata('QuoteId');
        } else {
            redirect('/');
        }

        if ($this->session->userdata('sis_session_id')) {
            $session_id = $this->session->userdata('sis_session_id');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
        }

        $lang_code = '';
        if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
            $lang_code = $this->session->userdata('lcode');
        }

        $cartArr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'customer_id' => $customer_id, 'lang_code' => $lang_code);
        $cart = CartRepository::cart_listing_check_cod($shopcode, $shop_id, $cartArr);
        if (!empty($cart) && isset($cart) && ($cart->is_success == 'true')) {
            $data['CartData'] = $cart->cartData;
        } else {
            redirect('/');
            $data['CartData'] = array();
        }

        $data['QuoteId'] = $quote_id;


        $table2 = 'country_master';
        $flag = 'own';
        $postArr2 = array('table_name' => $table2, 'database_flag' => $flag);
        $response2 = CommonRepository::get_table_data($postArr2, 3600);
        if (!empty($response2) && isset($response2) && $response2->statusCode == 200) {
            $data['countryList'] = $response2->tableData;
        } else {
            $data['countryList'] = array();
        }

        // $data['countryList'] = array('Mauritius');

        $table = 'city_master';
        $flag = 'main';
        $postArr4 = array('table_name' => $table, 'database_flag' => $flag);
        $response4 = CommonRepository::get_table_data($postArr4, 3600);
        if (!empty($response4) && isset($response4) && $response4->is_success == 'true') {
            $data['cityList'] = $response4->tableData;
        }



        $allowedStates = array('Delhi', 'Uttar Pradesh', 'Haryana');
        
        if (isset($data['CartData']->cartItems) && is_array($data['CartData']->cartItems)) {
            
            foreach ($data['CartData']->cartItems as $item) {
                
                if (isset($item->product_name)) {
                    $product_name = $item->product_name;
                    $table = 'country_state_master_in';
                    $flag = 'main';
                    $postArr3 = array('table_name' => $table, 'database_flag' => $flag);
                    $response3 = CommonRepository::get_table_data($postArr3, 3600);

                    if (!empty($response3) && isset($response3) && $response3->is_success == true) {
                        $stateList = $response3->tableData;
                        $filteredStateList = array();
                        foreach ($response3->tableData as $state) {

                            if ($product_name === 'Dinamalar Newspaper') {
                                if (in_array($state->state_name, $allowedStates)) {
                                    $filteredStateList[] = $state;
                                }
                            } else {
                                $filteredStateList[] = $state;
                            }
                        }
                        usort($filteredStateList, function($a, $b) {
                            return strcmp($a->state_name, $b->state_name);
                        });
        
                        $data['stateList'] = $filteredStateList;
                    }
                    // break;
                }
            }
        }

        /*--------------------Customer address-------------------*/
        if ($customer_id) {
            $table = 'customers_address';
            $flag = 'own';
            $where = 'customer_id = ? AND remove_flag = ?';
            $order_by = 'ORDER BY id DESC';
            $params = array($customer_id, 0);
            $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
            $response = CommonRepository::get_table_data($postArr);
            if (!empty($response) && isset($response) && $response->is_success == 'true') {
                $data['addressList'] = $response->tableData;
            } else {
                $data['addressList'] = array();
            }
        } else {
            $data['addressList'] = array();
        }

        $payArr = array('country_code' => COUNTRY_CODE);
        $MethodResult = CheckoutRepository::payment_methods_listing($payArr);
        if (!empty($MethodResult) && isset($MethodResult) && ($MethodResult->is_success == 'true')) {
            $data['PaymentMethods'] = $MethodResult->PaymentMethods;
        } else {
            $data['PaymentMethods'] = array();
        }

        $ShipToCountry = GlobalRepository::get_custom_variable('shipping_country');
        if (!empty($ShipToCountry) && isset($ShipToCountry) && ($ShipToCountry->is_success == 'true')) {
            $data['ShipToCountry'] = $ShipToCountry->custom_variable;
        } else {
            $data['ShipToCountry'] = array();
        }

        $data['restricted_access'] = GlobalRepository::get_custom_variable('restricted_access', true) ?? 'no';

        $data['hidden_mobile_no'] = '';
        $data['hidden_email_id'] = '';
        // razor pay
        if ((isset($_GET['order_id']) && $_GET['order_id'] > 0)) {

            // billing mobile number
            $table_mob = 'sales_order_address';
            $flag_mob = 'own';
            $where_mob = 'order_id = ? and address_type = ?';
            $params_mob = array($_GET['order_id'], 1);
            $postArr_mob = array('table_name' => $table_mob, 'database_flag' => $flag_mob, 'where' => $where_mob, 'params' => $params_mob);
            $response_mob = CommonRepository::get_table_data($shopcode, $shop_id, $postArr_mob);
            if (!empty($response_mob) && isset($response_mob) && isset($response_mob->tableData[0])) {
                $data['hidden_mobile_no'] = $response_mob->tableData[0]->mobile_no;
            }
            // email id
            $table_email = 'sales_order';
            $flag_email = 'own';
            $where_email = 'order_id = ?';
            $params_email = array($_GET['order_id']);
            $postArr_email = array('table_name' => $table_email, 'database_flag' => $flag_email, 'where' => $where_email, 'params' => $params_email);
            $response_email = CommonRepository::get_table_data($shopcode, $shop_id, $postArr_email);
            if (!empty($response_email) && isset($response_email) && $response_email->statusCode == 200) {
                $data['hidden_email_id'] = $response_email->tableData[0]->customer_email;
            }
        }
        // end razor pay

        $data['request_for_invoice_access_data'] = GlobalRepository::get_custom_variable('request_for_invoice_default_webcust', true) ?? '';

        //new added
        if (isset($quote_id) && $quote_id > 0) {
            /*quote data*/
            $quoteTable = 'sales_quote';
            $quoteFlag = 'own';
            $quoteWhere = 'quote_id = ? ';
            $quoteParams = array($quote_id);
            $quotePostArr = array('table_name' => $quoteTable, 'database_flag' => $quoteFlag, 'where' => $quoteWhere, 'order_by' => '', 'params' => $quoteParams);
            $quoteResponse = CommonRepository::get_table_data($quotePostArr);
            if (!empty($quoteResponse) && isset($quoteResponse) && $quoteResponse->is_success == 'true') {
                $data['quoteData'] = $quoteResponse->tableData[0];
                //print_r($data['quoteData']);
                $quoteData = $quoteResponse->tableData[0];
                if (isset($quoteData->customer_email) && !empty($quoteData->customer_email) && $quoteData->customer_email != '') {
                    $emailQuot = explode('@', $quoteData->customer_email);
                    if (isset($emailQuot)) {
                        $data['emailQuotData'] = $emailQuot[1];
                    }
                } else {
                    $data['emailQuotData'] = '';
                }
            } else {
                $data['quoteData'] = array();
                $data['emailQuotData'] = '';
            }
            /*end quote data*/

            // Quote address data
            $data['quote_address_data'] = array();
            $quoteTable = 'sales_quote_address';
            $quoteFlag = 'own';
            $quoteWhere = 'quote_id = ? ';
            $quoteParams = array($quote_id);
            $quotePostArr = array('table_name' => $quoteTable, 'database_flag' => $quoteFlag, 'where' => $quoteWhere, 'order_by' => '', 'params' => $quoteParams);
            $quoteResponse = CommonRepository::get_table_data($quotePostArr);
            if (!empty($quoteResponse) && isset($quoteResponse) && $quoteResponse->is_success == 'true') {
                foreach ($quoteResponse->tableData as $key => $value) {
                    $data['quote_address_data'][] = $value;
                }
            }

            // Quote payment data
            $data['quote_payment_data'] = array();
            $quoteTable = 'sales_quote_payment';
            $quoteFlag = 'own';
            $quoteWhere = 'quote_id = ? ';
            $quoteParams = array($quote_id);
            $quoteSelect = ' payment_method_id,payment_method ';
            $quotePostArr = array('table_name' => $quoteTable, 'database_flag' => $quoteFlag, 'where' => $quoteWhere, 'order_by' => '', 'params' => $quoteParams, 'select' => $quoteSelect);
            $quoteResponse = CommonRepository::get_table_data($quotePostArr);
            if (!empty($quoteResponse) && isset($quoteResponse) && $quoteResponse->is_success == 'true') {
                foreach ($quoteResponse->tableData as $key => $value) {
                    $data['quote_payment_data'][] = $value;
                }
            }
        } else {
            $data['quoteData'] = array();
        }
        //end new added

        // check terms and condition
        $identifier_tnc = 'order_check_termsconditions';
        $ApiResponse_tnc = GlobalRepository::get_custom_variable($identifier_tnc);

        if ($ApiResponse_tnc->statusCode == '200') {
            $RowCV = $ApiResponse_tnc->custom_variable;
            $data['order_check_termsconditions'] = $RowCV->value;
        } else {
            $data['order_check_termsconditions'] = 'no';
        }

        $this->template->load('checkout/shop_checkout.php', $data);
    }




   public function getCities()
    {
        $state_id = $this->input->post('state_id');
        $this->load->model('CommonModel');  
        $cities = $this->CommonModel->getCitiesByState($state_id);
        echo json_encode(['status' => 'success', 'cities' => $cities]);
    }

    public function customerLoginOtpEmail()
    {
        if (empty($_POST['emailmobile'])) {
            echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
            exit;
        } else {

            $emailmobile = $_POST['emailmobile'];
            if (filter_var($emailmobile, FILTER_VALIDATE_EMAIL)) {
                $table1 = 'customers';
                $flag1 = 'own';
                $where1 = 'email_id = ?';
                $params1 = array($emailmobile);
                $postArr1 = array('table_name' => $table1, 'database_flag' => $flag1, 'where' => $where1, 'params' => $params1);
                $response1 = CommonRepository::get_table_data($postArr1);
                if (!empty($response1) && isset($response1) && $response1->statusCode == 200) {
                    echo json_encode(array('flag' => 1, 'msg' => "Email already exist!"));
                    exit;
                } else {
                    echo json_encode(array('flag' => 3, 'msg' => "We cannot find an account with that Email"));
                    exit;
                }
            } else {

                $table2 = 'customers';
                $flag2 = 'own';
                $where2 = 'mobile_no = ?';
                $params2 = array($emailmobile);
                $postArr2 = array('table_name' => $table2, 'database_flag' => $flag2, 'where' => $where2, 'params' => $params2);
                $response2 = CommonRepository::get_table_data($postArr2);
                if (!empty($response2) && isset($response2) && $response2->statusCode == 200) {
                    $otp_random_generate = mt_rand(1000, 9999);
                    $otp_post = array('mobile_no' => $emailmobile, 'otp' => $otp_random_generate);
                    $customer_signup_otp = LoginRepository::customer_signup_otp($otp_post);
                    if (isset($customer_signup_otp) && !empty($customer_signup_otp)) {
                        $postArr = array('mobile_no' => $emailmobile);
                        $get_customer_signup_data = CustomerRepository::get_customer_signup_otp($postArr);
                        echo json_encode(array('flag' => 2, 'msg' => $customer_signup_otp->message, 'data' => $get_customer_signup_data->customer_signup_otp_data->otp));
                        exit;
                    } else {
                        echo json_encode(array('flag' => 3, 'msg' => "Something went wrong"));
                        exit;
                    }
                }
                echo json_encode(array('flag' => 3, 'msg' => "We cannot find an account with that Mobile number"));
                exit;
            }
        }
    }

    public function login()
    {
        if (isset($_POST)) {
            if (empty($_POST)) {
                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                exit;
            } else {

                if (isset($_POST['nickname']) && $_POST['nickname'] != '') {
                    echo json_encode(array(
                        'flag' => 0,
                        'msg' => "Are you a bot?"
                    ));
                    exit;
                }

                $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');

                $currency_name = (($this->session->userdata('currency_name')) ? $this->session->userdata('currency_name') : '');
                $currency_code_session = (($this->session->userdata('currency_code_session')) ? $this->session->userdata('currency_code_session') : '');
                $currency_conversion_rate = (($this->session->userdata('currency_conversion_rate')) ? $this->session->userdata('currency_conversion_rate') : '');
                $currency_symbol = (($this->session->userdata('currency_symbol')) ? $this->session->userdata('currency_symbol') : '');
                $default_currency_flag = (($this->session->has_userdata('default_currency_flag')) ? $this->session->userdata('default_currency_flag') : '');


                $quote_id = $this->session->userdata('QuoteId');
                $emailmobile = (isset($_POST['emailmobile'])) ? $_POST['emailmobile'] : '';
                $otp_verification = (isset($_POST['otp_verification'])) ? $_POST['otp_verification'] : '';
                $email = '';
                $mobile_no = '';
                if (filter_var($emailmobile, FILTER_VALIDATE_EMAIL)) {
                    $email = (isset($_POST['emailmobile'])) ? $_POST['emailmobile'] : '';
                } else {
                    $mobile_no = (isset($_POST['emailmobile'])) ? $_POST['emailmobile'] : '';
                }
                $password = (isset($_POST['password']) || $_POST['password'] != '' ? $_POST['password'] : $_POST['login_password']);
                $recaptcha = $_POST['g-recaptcha-response'];
                // $shopcode = SHOPCODE;
                // $shop_id = SHOP_ID;

                if (isset($otp_verification) && !empty($otp_verification)) {
                    $postArr = array('mobile_no' => $mobile_no);
                    $get_customer_signup_data = CustomerRepository::get_customer_signup_otp($postArr);
                    if (isset($get_customer_signup_data->customer_signup_otp_data)) {
                        if ($_POST['otp_verification'] != $get_customer_signup_data->customer_signup_otp_data->otp) {
                            echo json_encode(array('flag' => 0, 'msg' => "Please enter valid OTP!"));
                            exit;
                        }
                    }
                }

                $loginPostArr = array('email' => $email, 'mobile_no' => $mobile_no, 'otp_verification' => $otp_verification, 'password' => $password, 'quote_id' => $quote_id);
                // print_r($loginPostArr);
                // die();
                $loginResponse = LoginRepository::login($loginPostArr);


                if (!empty($loginResponse) && isset($loginResponse) && $loginResponse->is_success == 'true') {
                    $message = $loginResponse->message;
                    $userdetails = $loginResponse->userdetails;
                    $LoginID = $userdetails->LoginID;
                    $LoginToken = $userdetails->LoginToken;
                    $FirstName = $userdetails->FirstName;
                    $LastName = $userdetails->LastName;
                    $EmailID = $userdetails->EmailID;
                    $customer_type_id = $userdetails->customer_type_id;
                    $QuoteId = $userdetails->QuoteId;

                    if ($QuoteId != '') {
                        $quoteArry = array('QuoteId' => $QuoteId);
                        $this->session->set_userdata($quoteArry);
                    }


                    $sessionArr = array('LoginToken' => $LoginToken, 'LoginID' => $LoginID, 'FirstName' => $FirstName, 'LastName' => $LastName, 'EmailID' => $EmailID, 'CustomerTypeID' => $customer_type_id);
                    $this->session->set_userdata($sessionArr);

                    if ($this->session->userdata('QuoteId')) {
                        $cartArr = array('quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $LoginID, 'session_id' => $LoginToken, 'checkout_method' => 'login');
                        $cart = CheckoutRepository::update_quote_customer_id($cartArr);
                    }


                    if (isset($_SESSION['currentPageUrl']) && $_SESSION['currentPageUrl'] != '') {
                        $redirect = BASE_URL . $_SESSION['currentPageUrl'];
                    } else {
                        $redirect = BASE_URL . "checkout";
                    }

                    $postArr = array('customer_id' => $LoginID);
                    $response = CustomerRepository::customer_get_personal_info($postArr);
                    //  print_r($response);
                    // die();
                    if (!empty($response) && $response->is_success == 'true') {
                        $customerData = $response->customerData;
                        if ((isset($customerData->access_prelanch_product) && $customerData->access_prelanch_product == 1) || (isset($customerData->allow_catlog_builder) && $customerData->allow_catlog_builder == 1)) {
                            $this->session->set_userdata('special_features', 1);
                        }
                    }

                    $this->validateQuoteData($customer_type_id);

                    echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect' => $redirect));
                    exit;
                } else {
                    echo json_encode(array('flag' => 0, 'msg' => $loginResponse->message));
                    exit;
                }
            }
        }
    }

    public function validateQuoteData($customer_type_id)
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if ($this->session->userdata('sis_session_id')) {
            $session_id = $this->session->userdata('sis_session_id');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
        }

        $quote_id = $this->session->userdata('QuoteId');
        $cartArr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'customer_id' => $customer_id);
        $cart = CartRepository::cart_listing($cartArr);
        if (!empty($cart) && isset($cart) && ($cart->is_success == 'true')) {
            $CartData = $cart->cartData;
            $cartDetails = $CartData->cartDetails;

            if (isset($cartDetails->coupon_code) && $cartDetails->coupon_code != '') {
                $table2 = 'salesrule_coupon';
                $flag = 'own';
                $where = 'coupon_code = ?';
                $params = array($cartDetails->coupon_code);
                $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                $response2 = CommonRepository::get_table_data($postArr2);
                if (!empty($response2) && isset($response2) && $response2->statusCode == 200) {
                    $CouponDetails = $response2->tableData;

                    $rule_id = $CouponDetails[0]->rule_id;
                    $table2 = 'salesrule';
                    $flag = 'own';
                    $where = 'rule_id = ?';
                    $params = array($rule_id);
                    $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                    $response3 = CommonRepository::get_table_data($postArr2);
                    if (!empty($response3) && isset($response3) && $response3->statusCode == 200) {
                        $RuleDetails = $response3->tableData;
                        $apply_to = $RuleDetails[0]->apply_to;
                        $coupon_type = $RuleDetails[0]->coupon_type;
                        if (strpos($apply_to, ',') !== false) {
                            $apply_to_arr = explode(',', $apply_to);
                        } else {
                            $apply_to_arr[] = $apply_to;
                        }

                        if (!in_array($customer_type_id, $apply_to_arr)) {
                            $coupon_code = $cartDetails->coupon_code;
                            $removeCouponArr = array('coupon_code' => $coupon_code, 'coupon_type' => $coupon_type, 'quote_id' => $quote_id);
                            $CartResponseData = CartRepository::remove_coupon_code($removeCouponArr);
                        } else {
                            $coupon_code = $cartDetails->coupon_code;
                            $couponArr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'coupon_code' => $coupon_code, 'coupon_type' => $coupon_type, 'customer_id' => $customer_id);
                            $CartResponseData = CartRepository::apply_coupon_code($couponArr);
                        }
                    }
                } else {
                    $CouponDetails = '';
                }
            } elseif (isset($cartDetails->voucher_code) && $cartDetails->voucher_code != '') {
                $table2 = 'salesrule_coupon';
                $flag = 'own';
                $where = 'coupon_code = ?';
                $params = array($cartDetails->voucher_code);
                $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                $response2 = CommonRepository::get_table_data($postArr2);
                if (!empty($response2) && isset($response2) && $response2->statusCode == 200) {
                    $CouponDetails = $response2->tableData;
                    $rule_id = $CouponDetails[0]->rule_id;
                    $table2 = 'salesrule';
                    $flag = 'own';
                    $where = 'rule_id = ?';
                    $params = array($rule_id);
                    $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                    $response3 = CommonRepository::get_table_data($postArr2);
                    if (!empty($response3) && isset($response3) && $response3->statusCode == 200) {
                        $RuleDetails = $response3->tableData;
                        $apply_to = $RuleDetails[0]->apply_to;
                        $coupon_type = $RuleDetails[0]->coupon_type;
                        if (strpos($apply_to, ',') !== false) {
                            $apply_to_arr = explode(',', $apply_to);
                        } else {
                            $apply_to_arr[] = $apply_to;
                        }

                        if (!in_array($customer_type_id, $apply_to_arr)) {
                            $coupon_code = $cartDetails->voucher_code;
                            $removeCouponArr = array('coupon_code' => $coupon_code, 'coupon_type' => $coupon_type, 'quote_id' => $quote_id);
                            $CartResponseData = CartRepository::remove_coupon_code($removeCouponArr);
                        } else {
                            $coupon_code = $cartDetails->voucher_code;
                            $couponArr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'coupon_code' => $coupon_code, 'coupon_type' => $coupon_type, 'customer_id' => $customer_id);
                            $CartResponseData = CartRepository::apply_coupon_code($couponArr);
                        }
                    }
                } else {
                    $CouponDetails = '';
                }
            }
        }
    }

    public function register()
    {
        if (isset($_POST)) {
            if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['agree_chk']) || empty($_POST['password']) || empty($_POST['conf_password'])) {
                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                exit;
            } elseif ($_POST['password'] != $_POST['conf_password']) {
                echo json_encode(array('flag' => 0, 'msg' => "Confirm Password does not match."));
                exit;
            } else {

                $identity = 'captcha_check_flag';
                // $captcha_flag = GlobalRepository::get_custom_variable($identity);
                // if (isset($captcha_flag->statusCode) && $captcha_flag->statusCode == '200') {
                //     $variable = $captcha_flag->custom_variable;
                //     $captcha_check_flag = $variable->value;
                // }else{
                //     $captcha_check_flag = 'no';
                // }

                // if(verifyRecaptcha() === false && $captcha_check_flag =='yes'){
                //     echo json_encode([
                //         'flag' => 0,
                //         'msg' => 'Robot verification failed',
                //     ]);
                //     exit;
                // }

                if (isset($_POST['nickname']) && $_POST['nickname'] != '') {
                    echo json_encode(array(
                        'flag' => 0,
                        'msg' => "Are you a bot?"
                    ));
                    exit;
                }

                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $conf_password = $_POST['conf_password'];
                $country_code = 'IN';
                $phone_prefix = (isset($_POST['phone_prefix'])) ? $_POST['phone_prefix'] : 91;
                //$country_code = $this->ip_visitor_country();
                $mobile_no = $_POST['mobile_no'];
                $lang_code = '';
                if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
                    $lang_code = $this->session->userdata('lcode');
                }

                $postArr = array('mobile_no' => $mobile_no);
                $get_customer_signup_data = CustomerRepository::get_customer_signup_otp($postArr);
                if (isset($get_customer_signup_data->customer_signup_otp_data)) {
                    if ($_POST['otp_verification'] != $get_customer_signup_data->customer_signup_otp_data->otp) {
                        echo json_encode(array('flag' => 0, 'msg' => "Please enter valid OTP!"));
                        exit;
                    }
                }
                $webshopname = '';
                $shop_logo = SITE_LOGO;

                $data['webshop_details'] = CommonRepository::get_webshop_details();
                if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
                    // $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                    $webshopname = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_name);
                }

                // $webshopname = GlobalRepository::get_fbc_users_shop()?->result?->org_shop_name ?? '';
                // $shop_logo = SITE_LOGO.'/'.$shop_logo;
                $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
                            <img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
                        </a>';

                $postArr = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone_prefix' => $phone_prefix,
                    'mobile_no' => $mobile_no,
                    'password' => $password,
                    'country_code' => $country_code,
                    'site_logo' => $site_logo,
                    'lang_code' => $lang_code,
                    'verifyRecaptcha' => 'no'
                );

                $RegisterResponse = RegisterRepository::register($postArr);
                if (!empty($RegisterResponse) && isset($RegisterResponse) && $RegisterResponse->is_success == 'true') {
                    $message = $RegisterResponse->message;
                    $quote_id = $this->session->userdata('QuoteId');
                    $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');

                    // $currency_name = (($this->session->userdata('currency_name')) ? $this->session->userdata('currency_name') : '');
                    // $currency_code_session = (($this->session->userdata('currency_code_session')) ? $this->session->userdata('currency_code_session') : '');
                    // $currency_conversion_rate = (($this->session->userdata('currency_conversion_rate')) ? $this->session->userdata('currency_conversion_rate') : '');
                    // $currency_symbol = (($this->session->userdata('currency_symbol')) ? $this->session->userdata('currency_symbol') : '');
                    // $default_currency_flag = (($this->session->has_userdata('default_currency_flag')) ? $this->session->userdata('default_currency_flag') : '');

                    $loginPostArr = array('email' => $email, 'password' => $password, 'quote_id' => $quote_id, 'mobile_no' => $mobile_no);
                    $loginResponse = LoginRepository::login($loginPostArr);
                    if (!empty($loginResponse) && isset($loginResponse) && $loginResponse->is_success == 'true') {
                        $message = $loginResponse->message;
                        $userdetails = $loginResponse->userdetails;
                        $LoginID = $userdetails->LoginID;
                        $LoginToken = $userdetails->LoginToken;
                        $FirstName = $userdetails->FirstName;
                        $LastName = $userdetails->LastName;
                        $EmailID = $userdetails->EmailID;
                        $customer_type_id = $userdetails->customer_type_id;
                        $sessionArr = array('LoginToken' => $LoginToken, 'LoginID' => $LoginID, 'FirstName' => $FirstName, 'LastName' => $LastName, 'EmailID' => $EmailID, 'CustomerTypeID' => $customer_type_id);
                        $this->session->set_userdata($sessionArr);

                        if ($this->session->userdata('QuoteId')) {
                            $cartArr = array('quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $LoginID, 'session_id' => $LoginToken, 'checkout_method' => 'register');
                            $cart = CheckoutRepository::update_quote_customer_id($cartArr);
                        }

                        $this->validateQuoteData($customer_type_id);

                        $redirect = base_url() . "checkout";
                        echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect' => $redirect));
                        exit;
                    } else {
                        $message = $loginResponse->message ?? 'Something went wrong!';
                        echo json_encode(array('flag' => 0, 'msg' => $message));
                        exit;
                    }
                } else {
                    $message = $RegisterResponse->message ?? 'Something went wrong!';
                    echo json_encode(array('flag' => 0, 'msg' => $message));
                    exit;
                }
            }
        }
    }



    public function placeorder()
    {


        $CURRENCY_CODE = 'MUR';
        $error = '';

        // print_r($_POST);
        // die();

        if ($this->session->userdata('QuoteId')) {
            $quote_id = $this->session->userdata('QuoteId');
        } else {
            redirect('/');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
            $session_id = $this->session->userdata('LoginToken');
        } else {
            $customer_id = '0';
            $session_id = $this->session->userdata('sis_session_id');
        }

        $lang_code = '';
        $lis_default_language = '';
        // if(!empty($this->session->userdata('lcode'))){
        //     $lang_code=$this->session->userdata('lcode');
        // }

        if (!empty($this->session->userdata('lis_default_language'))) {
            $lis_default_language = $this->session->userdata('lis_default_language');
        }

        if (isset($_POST)) {
            $postArrCheck = array('quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $customer_id);
            $response_qty_items = CheckoutRepository::check_quote_item_available($postArrCheck);
            if (!empty($response_qty_items) && isset($response_qty_items) && ($response_qty_items->statusCode == 500)) {
                redirect(base_url() . 'cart/');
            }

            // request for self invoice
            if (isset($_POST['request_for_invoice'])) {
                $invoice_self = $_POST['request_for_invoice'];
            } else {
                $invoice_self = '0';
            }


            // Subscribe to Newsletter
            if (isset($_POST['subscribe_to_newsletter'])) {
                $subscribe_newsletter = $_POST['subscribe_to_newsletter'];
            } else {
                $subscribe_newsletter = '0';
            }

            $checkout_method = (isset($_POST['checkout_method']) && $_POST['checkout_method'] != '') ? $_POST['checkout_method'] : '';
            //$save_in_addressbook=(isset($_POST['save_in_addressbook']) && $_POST['save_in_addressbook']=='1')?1:'';
            $postArr = array('quote_id' => $this->session->userdata('QuoteId'), 'session_id' => $session_id, 'customer_id' => $customer_id, 'invoice_self' => $invoice_self, 'subscribe_newsletter' => $subscribe_newsletter);
            $postArr['checkout_method'] = $checkout_method;
            $random_key = generateToken('20', 'Numeric');

            foreach ($_POST as $key => $value) {
                $postArr[$key] = $value;
            }

            $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

            if (!empty($this->session->userdata('CURRENCY_CODE'))) {
                $postArr['currency_code'] = $this->session->userdata('CURRENCY_CODE');
            }

            $postArr['currency_code'] = 'MUR';


            // $postArr['lang_code'] = $lang_code;
            $postArr['lis_default_language'] = $lis_default_language;
            $cust_email_id = '';
            if ($customer_id > 0) {
                $postArr['customer_id'] = $customer_id;

                $customerpostArr = array('customer_id' => $customer_id);
                $response = CustomerRepository::customer_get_personal_info($customerpostArr);
                if (!empty($response) && isset($response) && $response->is_success == 'true') {
                    $shipping_email_id = $response->customerData->email_id;
                    $cust_email_id = $response->customerData->email_id;
                    $postArr['shipping_email_id'] = $shipping_email_id;
                } else {
                }
            }

            $order_response = CheckoutRepository::place_order($postArr);


            if (!empty($order_response) && isset($order_response) && ($order_response->is_success == 'true')) {
                $increment_id = $order_response->increment_id;
                $order_id = $order_response->order_id;
                $order_barcode = $order_response->order_barcode;
                $encode_oid = base64_encode($increment_id);

                $voucher_code   = $order_response->voucher_code;
                $voucher_amount = $order_response->voucher_amount;


                $voucher_code   = $order_response->voucher_code;
                $voucher_amount = $order_response->voucher_amount;

                if($voucher_code !="" && $voucher_amount !=""){
                    
                    $this->load->model('Giftcard_model');  
                    $gift_card_id = $this->Giftcard_model->getGiftCardIdByCode($voucher_code);
                    if($gift_card_id ){
                        $this->Giftcard_model->deductGiftCardOnOrder($gift_card_id, $customer_id, $order_barcode, $voucher_amount);
                    }
                   
                }

                if ($order_id != '' && $payment_method == 3) {

                    /*-----------------------Razorpay action----------------------------------------*/
                    //redirect('checkout/?order_id='.$order_id.'&payment_id='.$payment_method.'&increment_id='.$increment_id);

                    $amount = ($order_response->grand_total * 100);

                    $data = array(
                        'amount' => $amount,
                        'currency' => $CURRENCY_CODE,
                        'receipt' => $increment_id
                    );

                    $orderPaymentArr = array('order_id' => $order_id, 'amount' => $order_response->grand_total, 'payment_amount' => $order_response->grand_total, 'payment_currency' => CURRENCY_CODE);
                    $ResponseArr = CheckoutRepository::update_order_payment_status_info($orderPaymentArr);

                    $table2 = 'webshop_payments';
                    $flag = 'own';
                    $where = 'payment_id = ?';
                    $params = array($payment_method);
                    $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                    $response2 = CommonRepository::get_table_data($postArr2, 3600);
                    if (!empty($response2) && isset($response2) && $response2->is_success == true) {
                        $Row = $response2->tableData[0];
                        $gateway_details = json_decode($Row->gateway_details);

                        $_api_key = (isset($gateway_details->api_key) && $gateway_details->api_key != '') ? $gateway_details->api_key : '';
                        $_api_key_secret = (isset($gateway_details->api_key_secret) && $gateway_details->api_key_secret != '') ? $gateway_details->api_key_secret : '';
                    } else {
                        $_api_key = '';
                        $_api_key_secret = '';
                    }

                    if ($_api_key != '' && $_api_key_secret != '') {
                        $ch = $this->create_order_razorpay($data, $_api_key, $_api_key_secret);
                        $result = curl_exec($ch);

                        $response_array = json_decode($result, true);
                        $razorpay_order_id = $response_array['id'];


                        $razorpay_request = json_encode($data);

                        $razorpayArr = array('razorpay_request' => $razorpay_request, 'razorpay_response' => $result);
                        $ResponseRazor = CheckoutRepository::insert_razorpay_data($order_id, $razorpayArr);

                        if ($razorpay_order_id != '') {

                            $orderPaymentArr = array('order_id' => $order_id, 'status' => 'completed');
                            $ResponseArr = CheckoutRepository::update_order_payment_status_info($orderPaymentArr);

                            redirect('checkout/?order_id=' . $order_id . '&payment_id=' . $payment_method . '&increment_id=' . $increment_id . '&rp_order_id=' . $razorpay_order_id);
                        } else {
                            $this->session->set_userdata('checkout_error', 'Failed to create order in Razorpay.');
                            redirect(base_url() . 'checkout/');
                        }
                    } else {
                        $this->session->set_userdata('checkout_error', 'Razorpay invalid gateway details.');
                        redirect(base_url() . 'checkout/');
                    }
                } elseif ($order_id != '' && $payment_method == 6) {
                    /*------------------Stripe Payment action---------------------------*/

                    // $amount = ($order_response->grand_total * 100);
                    if ($this->session->userdata('currency_code_session') != '' && $this->session->userdata('default_currency_flag') == 0) {
                        $total_final_convert = $order_response->grand_total * $this->session->userdata('currency_conversion_rate');
                        $amountStripeConvert = number_format($total_final_convert, 2, '.', '') * 100;

                        $CURRENCY_CODE = $this->session->userdata('currency_code_session');
                    } else {
                        $total_final_convert = $order_response->grand_total;
                        $amountStripeConvert = number_format($order_response->grand_total, 2, '.', '') * 100;
                        // $CURRENCY_CODE = CURRENCY_CODE;

                        $CURRENCY_CODE = 'IN';
                    }

                    $orderPaymentArr = array('order_id' => $order_id, 'amount' => $order_response->grand_total, 'payment_amount' => number_format($total_final_convert, 2, '.', ''), 'payment_currency' => $CURRENCY_CODE);

                    $ResponseArr = CheckoutRepository::update_order_payment_status_info($orderPaymentArr);


                    //start main database
                    $table_main = 'payment_master';
                    $flag_main = 'main';
                    $where_main = 'id  = ?';
                    $params_main = array($payment_method);
                    $post_main_array = array('table_name' => $table_main, 'database_flag' => $flag_main, 'where' => $where_main, 'params' => $params_main);
                    $main_payment_master_data = CommonRepository::get_table_data($post_main_array, 3600);
                    if (!empty($main_payment_master_data) && isset($main_payment_master_data) && $main_payment_master_data->is_success == true) {
                        $keyMainData = json_decode($main_payment_master_data->tableData[0]->gateway_details);
                        $keyData = $keyMainData->key;
                    } else {
                        $keyData = '';
                    }

                    // comission main
                    $post_com_array = [
                        'table_name' => 'payment_com_master',
                        'database_flag' => 'main',
                        'where' => 'payment_id  = ? ',
                        'params' => [$payment_method]
                    ];
                    $com_payment_master_data = CommonRepository::get_table_data($post_com_array, 3600);

                    // line_item_heading new
                    // $stripe_line_item_heading = GlobalRepository::get_fbc_users_shop()?->result?->org_shop_name ?? '';

                    if (!empty($com_payment_master_data) && isset($com_payment_master_data) && $com_payment_master_data->is_success == true) {
                        if ($this->session->userdata('currency_code_session') != '' && $this->session->userdata('default_currency_flag') == 0) {

                            $table_com = 'payment_com_master_currency';
                            $flag_com = 'main';
                            $where_com = 'payment_com_master_id = ? AND currency_code = ?';
                            $params_com = array($com_payment_master_data->tableData[0]->id, $CURRENCY_CODE);
                            $post_com_array = array('table_name' => $table_com, 'database_flag' => $flag_com, 'where' => $where_com, 'params' => $params_com);
                            $payment_com_master_currency_data = CommonRepository::get_table_data($post_com_array, 3600);

                            if (!empty($com_payment_master_data) && isset($com_payment_master_data) && $com_payment_master_data->is_success == true) {
                                $stripe_payment_method = $payment_com_master_currency_data->tableData[0]->payment_method;
                            } else {
                                $stripe_payment_method = $com_payment_master_data->tableData[0]->payment_method;
                            }
                        } else {
                            $stripe_payment_method = $com_payment_master_data->tableData[0]->payment_method;
                        }

                        $commison_fee_percent = $com_payment_master_data->tableData[0]->commison_fee_percent;
                        $transaction_fee_fixed = $com_payment_master_data->tableData[0]->transaction_fee_fixed;

                        if (isset($commison_fee_percent)) {
                            $commison_fee_percent = $commison_fee_percent;
                        } else {
                            $commison_fee_percent = 0;
                        }
                        if (isset($transaction_fee_fixed)) {
                            $transaction_fee_fixed = $transaction_fee_fixed;
                        } else {
                            $transaction_fee_fixed = 0;
                        }

                        $split_fbc_percentage = $commison_fee_percent; //percentage main
                        $split_fbc_percentage_amount = ($order_response->grand_total * $commison_fee_percent) / 100;
                        $split_fbc_fixed = $transaction_fee_fixed;
                        $fbc_payment_amount = (number_format($split_fbc_percentage_amount, 2, '.', '') + number_format($split_fbc_fixed, 2, '.', ''));
                        // $fbc_payment_amount = (number_format($split_fbc_percentage_amount,2) + $split_fbc_fixed ) ;
                        $webshop_payment_amount = $order_response->grand_total - $fbc_payment_amount;
                    } else {
                        $stripe_line_item_heading = '';
                        $stripe_payment_method = '';
                        $commison_fee_percent = '';
                        $transaction_fee_fixed = '';
                        $split_fbc_percentage = 0; //percentage main
                        $split_fbc_percentage_amount = 0; //amount percentage
                        $split_fbc_fixed = 0;
                        $fbc_payment_amount = 0;
                        $webshop_payment_amount = $order_response->grand_total - $fbc_payment_amount;
                    }
                    // end comission main

                    //end main database
                    /*echo $fbc_payment_amount ;
                    exit();*/
                    $table2 = 'webshop_payments';
                    $flag = 'own';
                    $where = 'payment_id = ?';
                    $params = array($payment_method);
                    $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                    $response2 =  CommonRepository::get_table_data($postArr2, 3600);

                    if ($this->session->userdata('currency_code_session') != '' && $this->session->userdata('default_currency_flag') == 0) {
                        $fbc_payment_amount_final = $fbc_payment_amount * $this->session->userdata('currency_conversion_rate');
                        $fbc_payment_amount_final = (number_format($fbc_payment_amount_final, 2, '.', ''));
                    } else {
                        $fbc_payment_amount_final = $fbc_payment_amount;
                    }

                    if ($this->session->userdata('lcode') != '') {
                        $locale = $this->session->userdata('lcode');
                    } else {
                        $locale = 'en';
                    }

                    /* data */
                    $data = array(
                        'amount' => $amountStripeConvert,
                        'currency' => $CURRENCY_CODE,
                        'receipt' => $increment_id,
                        'fbc_payment_amount' => $fbc_payment_amount_final * 100,
                        'order_id' => $order_id,
                        'increment_id' => $increment_id,
                        'encode_oid' => $encode_oid,
                        'stripe_payment_method' => $stripe_payment_method,
                        'stripe_line_item_heading' => $stripe_line_item_heading,
                        'locale' => $locale
                    );


                    // updated data
                    $dataStripe = array('split_fbc_percentage' => $split_fbc_percentage, 'split_fbc_percentage_amount' => $split_fbc_percentage_amount, 'split_fbc_fixed' => $split_fbc_fixed, 'fbc_payment_amount' => $fbc_payment_amount, 'webshop_payment_amount' => $webshop_payment_amount,);
                    $orderArrStripe = array('order_id' => $order_id, 'stripUpdateData' => $dataStripe);
                    $ResponseArrStripe = CheckoutRepository::update_order_payment_status_info($orderArrStripe);
                    if (!empty($response2) && isset($response2) && $response2->is_success == true) {
                        $Row = $response2->tableData[0];
                        $gateway_details = json_decode($Row->gateway_details);
                        $connected_stripe_account_id = (isset($gateway_details->connected_stripe_account_id) && $gateway_details->connected_stripe_account_id != '') ? $gateway_details->connected_stripe_account_id : '';
                        $key_secret = $keyData;

                        $url_add = 'order_id=' . $order_id . '&increment_id=' . $increment_id;

                        $cancel_url = base_url() . 'order/failed';
                        $success_url = base_url();
                        // $success_url=base_url().'stripe_success';
                        // $success_url=base_url().'stripe_success?'.$url_add;

                        $url = array('cancel_url' => $cancel_url, 'success_url' => $success_url);

                        /*start request*/


                        $pay_request = json_encode($data);
                        $orderArr = array('order_id' => $order_id, 'pay_request' => $pay_request);
                        $ResponseArr = CheckoutRepository::update_order_payment_status_info($orderArr);
                        /*end request*/

                        $stripe_create_order_data = $this->create_order_stripe($data, $connected_stripe_account_id, $key_secret, $url);
                        //print_r($stripe_create_order_data);
                        //exit();
                        if (isset($stripe_create_order_data)) {
                            if (isset($stripe_create_order_data->url)) {
                                header('Location:' . $stripe_create_order_data->url);
                                //echo $session->url;
                            } else {
                                $this->session->set_userdata('checkout_error', 'Something went wrong with stripe, please try again.');
                                redirect(base_url() . 'checkout/');
                            }
                        } else {
                            $this->session->set_userdata('checkout_error', 'Something went wrong. Please try again.');
                            redirect('/checkout');
                        }
                    } else {
                        //$connected_stripe_account_id='';
                        $this->session->set_userdata('checkout_error', 'Something went wrong with stripe, please try again.');
                        redirect(base_url() . 'checkout/');
                    }
                } elseif ($order_id != '' && $payment_method == 1) {

                    $cancel_url = base_url() . 'order/failed/?key=' . $encode_oid;
                    $success_url = base_url() . 'order/success/?key=' . $encode_oid;
                    $notifyURL = base_url() . 'order/ipn/?quote_id=' . $quote_id;


                    if ($this->session->userdata('currency_code_session') != '' && $this->session->userdata('default_currency_flag') == 0) {
                        $total_final_amount = $order_response->grand_total * $this->session->userdata('currency_conversion_rate');
                        $amountPaypalConvert = number_format($total_final_amount, 2, '.', '');

                        $CURRENCY_CODE = $this->session->userdata('currency_code_session');
                    } else {
                        $total_final_amount = $order_response->grand_total;
                        $amountPaypalConvert = number_format($order_response->grand_total, 2, '.', '');
                        $CURRENCY_CODE = CURRENCY_CODE;
                    }

                    $orderPaymentArr = array('order_id' => $order_id, 'amount' => $order_response->grand_total, 'payment_amount' => number_format($total_final_amount, 2, '.', ''), 'payment_currency' => $CURRENCY_CODE);
                    $ResponseArr = CheckoutRepository::update_order_payment_status_info($orderPaymentArr);

                    // Add fields to paypal form
                    $this->paypal_lib->add_field('return', $success_url);
                    $this->paypal_lib->add_field('cancel_return', $cancel_url);
                    $this->paypal_lib->add_field('notify_url', $notifyURL);
                    $this->paypal_lib->add_field('item_name', 'ZumbaWear ');
                    $this->paypal_lib->add_field('custom', $customer_id);
                    $this->paypal_lib->add_field('item_number',  $order_id);
                    $this->paypal_lib->add_field('amount',  $amountPaypalConvert);
                    $this->paypal_lib->add_field('currency_code', $CURRENCY_CODE);

                    // Render paypal form
                    $this->paypal_lib->paypal_auto_form();
                } elseif ($order_id != '' && $payment_method == 2) {

                    $cancel_url = base_url() . 'order/failed/?key=' . $encode_oid;
                    $success_url = base_url() . 'order/success/?key=' . $encode_oid;
                    $notifyURL = base_url() . 'order/ipn/?quote_id=' . $quote_id;
                    $tid = time();
                    $merchant_id = '43092';
                    if ($this->session->userdata('currency_code_session') != '' && $this->session->userdata('default_currency_flag') == 0) {
                        $total_final_amount = $order_response->grand_total * $this->session->userdata('currency_conversion_rate');
                        $amount = number_format($total_final_amount, 2, '.', '');

                        $CURRENCY_CODE = $this->session->userdata('currency_code_session');
                    } else {
                        $total_final_amount = $order_response->grand_total;
                        $amount = number_format($order_response->grand_total, 2, '.', '');
                        $CURRENCY_CODE = CURRENCY_CODE;
                    }

                    $orderPaymentArr = array('order_id' => $order_id, 'amount' => $order_response->grand_total, 'payment_amount' => number_format($total_final_amount, 2, '.', ''), 'payment_currency' => $CURRENCY_CODE);
                    // $ResponseArr = CheckoutRepository::update_order_payment_status_info($orderPaymentArr);

                    $cc_post_data = array(
                        'tid' =>  $tid,
                        'order_id' => $increment_id,
                        'merchant_id' => $merchant_id,
                        'currency' => 'INR',
                        'redirect_url' =>  $success_url,
                        'cancel_url' => $cancel_url,
                        'language' => 'EN',
                        'amount' => $amount,
                    );
                    // $curl_handle = curl_init();
                    //echo $API; 
                    // include('Crypto.php');
                    $working_key = '14009EDD96791A43248582F20364D4C8'; //Shared by CCAVENUES
                    $data['access_code'] =  $access_code = 'AVUC05CH96AM83CUMA'; //Shared by CCAVENUES
                    $merchant_data = '';

                    foreach ($cc_post_data as $key => $value) {
                        $merchant_data .= $key . '=' . $value . '&';
                    }
                    $Shipping_Name = $postArr['shipping_first_name'] . " " . $postArr['shipping_last_name'];
                    $Billing_Name = $postArr['billing_first_name'] . " " . $postArr['billing_last_name'];
                    if (isset($_POST['billing_address_options']) && $_POST['billing_address_options'] != '') {
                        $orderPaymentArr = array('customer_address_id' => $_POST['billing_address_options']);
                        $ResponseArr = CheckoutRepository::get_customer_address($orderPaymentArr);
                        if (isset($ResponseArr->statusCode) && $ResponseArr->statusCode == 200) {
                            // print_r($ResponseArr->customerData);
                            // die();

                            $Billing_Name  =  $ResponseArr->customerData->first_name . " " .  $ResponseArr->customerData->last_name;
                            $_POST['billing_address'] = $ResponseArr->customerData->address_line1;
                            $_POST['billing_city'] = $ResponseArr->customerData->city;
                            $_POST['b_state_dp'] =  $ResponseArr->customerData->state;
                            $_POST['billing_pincode'] =  $ResponseArr->customerData->pincode;
                            $_POST['billing_country'] = $ResponseArr->customerData->pincode;
                            $_POST['billing_mobile_no'] =  $ResponseArr->customerData->mobile_no;
                            $_POST['billing_email_id'] = $cust_email_id;
                        }
                        // print_r($_POST);
                        // die();
                    }

                    if (isset($_POST['address_options']) && $_POST['address_options'] != '') {
                        $orderPaymentArr = array('customer_address_id' => $_POST['address_options']);
                        $ResponseArr = CheckoutRepository::get_customer_address($orderPaymentArr);
                        if (isset($ResponseArr->statusCode) && $ResponseArr->statusCode == 200) {


                            $Shipping_Name  =  $ResponseArr->customerData->first_name . " " .  $ResponseArr->customerData->last_name;
                            $postArr['shipping_address'] = $ResponseArr->customerData->address_line1;
                            $postArr['shipping_city'] = $ResponseArr->customerData->city;
                            $_POST['s_state_dp'] =  $ResponseArr->customerData->state;
                            $postArr['shipping_pincode'] =  $ResponseArr->customerData->pincode;
                            $postArr['shipping_country'] = $ResponseArr->customerData->pincode;
                            $postArr['shipping_mobile_no'] =  $ResponseArr->customerData->mobile_no;
                        }
                    }

                    $this->test_cc($order_id, $encode_oid, $quote_id, $increment_id, $amount, $Shipping_Name, $postArr['shipping_address'], $postArr['shipping_country'], $postArr['shipping_pincode'],  $postArr['shipping_city'], $postArr['shipping_mobile_no'], $Billing_Name, $_POST['billing_address'], $_POST['billing_city'], $_POST['b_state_dp'], $_POST['billing_pincode'], $_POST['billing_country'], $_POST['billing_mobile_no'], $_POST['billing_email_id'], $_POST['s_state_dp']);
                    // $data['encrypted_data'] = $encrypted_data = encrypt($merchant_data, $working_key); // Method for encrypting the data.
                    // $data['production_url'] = '	https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction&encRequest=' . $encrypted_data . '&access_code=' . $access_code;


                    $this->session->set_userdata('checkout_message', 'Order created successfully');
                    $View =  $this->load->view('checkout/testcc.php', $data, true);
                    $this->output->set_output($View);
                    // redirect(base_url() . 'checkout/?paymentURL=' . $production_url);
                    die();

                    // redirect('order/success/?key=' . $encode_oid);
                } else {
                    $this->session->set_userdata('checkout_message', 'Order created successfully');

                    redirect('order/success/?key=' . $encode_oid);
                }

                


            } else {
                $this->session->set_userdata('checkout_error', 'Something went wrong. Please try again.');
                redirect(base_url() . 'checkout/');
            }
        } else {
            $this->session->set_userdata('checkout_error', 'Something went wrong. Please try again.');
            redirect('/checkout');
        }
    }
    // public function order_payment($order_id)
    // {
    //     // echo $order_id;die;
    //     $order_id = ['order_id' => '2146'];
    //     $ResponseArr = CheckoutRepository::get_order_payment($order_id);
        
    //     $data =$ResponseArr->OrderData;
    //     // print_r($ResponseArr->OrderData);die;
    //     $View =  $this->load->view('checkout/order_payment.php', $data, true);
    //     $this->output->set_output($View);
    // }

    // public function place_order_payment()
    // {
        
    //     $order_id = 2146;
    //     $encode_oid = base64_encode(2807); 
    //     $quote_id =''; 
    //     $increment_id='2807';
    //     $amount='59668.00'; 
    //     $Shipping_Name='Mr Bhavin Jani' ; 
    //     $shipping_address = '1st Floor, Terminal 4, SVPI Airport'; 
    //     $shipping_country = 'IN'; 
    //     $shipping_pincode = '382475';  
    //     $shipping_city = 'Ahmedabad';
    //     $shipping_mobile_no = '9099900322'; 
    //     $Billing_Name='Mr Bhavin Jani'; 
    //     $billing_address ='1st Floor, Terminal 4, SVPI Airport'; 
    //     $billing_city ='Ahmedabad'; 
    //     $b_state_dp ='Gujarat'; 
    //     $billing_pincode ='382475';
    //     $billing_country ='IN'; 
    //     $billing_mobile_no ='9099900322';
    //     $billing_email_id ='Aishwarya.ramanan@adani.com';
    //     $s_state_dp ='Gujarat';

    //     $this->test_cc($order_id, $encode_oid, $quote_id, $increment_id, $amount, $Shipping_Name, $shipping_address, $shipping_country, $shipping_pincode,  $shipping_city, $shipping_mobile_no, $Billing_Name, $billing_address, $billing_city, $b_state_dp, $billing_pincode, $billing_country, $billing_mobile_no, $billing_email_id, $s_state_dp);

    //     $data['access_code'] =  $access_code = 'AVUC05CH96AM83CUMA'; 
    //     $this->session->set_userdata('checkout_message', 'Order created successfully');
    //     // $View =  $this->load->view('checkout/testcc.php', $data, true);
    //     // $this->output->set_output($View);

        
    // }

    public function order_payment($order_id)
    {
        // echo $order_id;die;
        $order_id = ['order_id' => '2906'];
        $ResponseArr = CheckoutRepository::get_order_payment($order_id);
        
        $data =$ResponseArr->OrderData;
        // echo "<pre>";
        // print_r($ResponseArr->OrderData);die;
        $View =  $this->load->view('checkout/order_payment.php', $data, true);
        $this->output->set_output($View);
    }

    public function place_order_payment()
    {
        
        $order_id = 2906;
        $encode_oid = base64_encode(3567); 
        $quote_id =''; 
        $increment_id='3567';
        $amount='23455.00'; 
        $Shipping_Name='Neha Singh' ; 
        $shipping_address = '91, Kalpataru synergy, Opposite Grand Hyatt Hotel, Vakola, Santacruz East'; 
        $shipping_country = 'IN'; 
        $shipping_pincode = '400055';  
        $shipping_city = 'Mumbai';
        $shipping_mobile_no = '7738750803'; 
        $Billing_Name='Neha Singh'; 
        $billing_address ='91, Kalpataru synergy, Opposite Grand Hyatt Hotel, Vakola, Santacruz East'; 
        $billing_city ='Mumbai'; 
        $b_state_dp ='Maharashtra'; 
        $billing_pincode ='400055';
        $billing_country ='IN'; 
        $billing_mobile_no ='7738750803';
        $billing_email_id ='neha.singh@kalpataru.com';
        $s_state_dp ='Maharashtra';

        $this->test_cc($order_id, $encode_oid, $quote_id, $increment_id, $amount, $Shipping_Name, $shipping_address, $shipping_country, $shipping_pincode,  $shipping_city, $shipping_mobile_no, $Billing_Name, $billing_address, $billing_city, $b_state_dp, $billing_pincode, $billing_country, $billing_mobile_no, $billing_email_id, $s_state_dp);

        $data['access_code'] =  $access_code = 'AVUC05CH96AM83CUMA'; 
        $this->session->set_userdata('checkout_message', 'Order created successfully');
        // $View =  $this->load->view('checkout/testcc.php', $data, true);
        // $this->output->set_output($View);

        // redirect('order/success/?key=' . $encode_oid);
    }
    public function test_cc($order_id = '', $encode_oid = '', $quote_id = '', $increment_id = '', $amount = '', $Shipping_Name = '', $shipping_address = '', $shipping_country = '', $shipping_pincode = '', $shipping_city = '', $shipping_mobile_no = '', $Billing_Name = '', $billing_address = '', $billing_city = '', $billing_state = '', $billing_zip = '', $billing_country = '', $billing_tel = '', $billing_email = '', $delivery_state = '')
    {
        $merchant_id = 21651;
        $post_arr =  array('order_id' => $order_id);
        $response_data = CheckoutRepository::get_price_split_for_publisher($post_arr);
        // echo "<pre>";
        // PRINT_R($response_data);
        // die();
        $publisher_Payment = array();
        $merchant_comm = 0;
        $total_price = 0;
        if (isset($response_data->statusCode) && $response_data->statusCode == '200') {
            $i = 1;
            foreach ($response_data->publisher_item_details as $Kpublisher_id => $val_pub) {
                // print_r($val_pub->publisher_details->commision_percent);
                $commision_percent = $val_pub->publisher_details->commision_percent;
                $seller_price =   $val_pub->total_seller_items_price;
                $total_price += $val_pub->total_seller_items_price;
                $Merchant_cost = $seller_price * ($commision_percent / 100);
                $publisher_cost =  $seller_price - $Merchant_cost;
                $merchant_comm += $Merchant_cost;
                $pub_id_test = 'Test' . $i;
                $publisher_Payment[] = array('splitAmount' => number_format($publisher_cost, 2, '.', ''), 'subAccId' => $pub_id_test);
                $i++;
            }
        }
        $data['split_data_list'] =  json_encode($publisher_Payment);
        $data['merComm'] =  number_format($merchant_comm, 2, '.', '');
        $data['reference_no'] = $increment_id;
        // echo number_format($publisher_cost, 2, '.', '');
        // echo   number_format($total_price, 2, '.', '');
        // die();
        $cancel_url = base_url() . 'order/failed/?key=' . $encode_oid;
        $success_url = base_url() . 'CheckoutController/cc_sucess/?key=' . $encode_oid;
        $notifyURL = base_url() . 'order/ipn/?quote_id=' . $quote_id;
        $tid = time();

        $cc_post_data = array(
            'tid' =>  $tid,
            'order_id' => $increment_id,
            'merchant_id' => $merchant_id,
            'currency' => 'INR',
            'redirect_url' =>  $success_url,
            'cancel_url' => $cancel_url,
            'language' => 'EN',
            'command' => 'createSplitPayout',
            'amount' => $amount,
            'billing_name' => $Billing_Name,
            'billing_address' => $billing_address,
            'billing_city' => $billing_city,
            'billing_state' => $billing_state,
            'billing_zip' => $billing_zip,
            'billing_country' => $billing_country,
            'billing_tel' => $billing_tel,
            'billing_email' => $billing_email,
            'delivery_name' => $Shipping_Name,
            'delivery_address' => $shipping_address,
            'delivery_city' => $shipping_city,
            'delivery_state' => $delivery_state,
            'delivery_zip' => $shipping_pincode,
            'delivery_country' => $shipping_country,
            'delivery_tel' => $shipping_mobile_no,
            'split_tdr_charge_type' => 'M',
            'merComm' => $merchant_comm,
            'split_data_list' => $publisher_Payment,
            'request_type' => 'JSON',
            'version' => 1.2
        );
        // print_r($cc_post_data);
        // die();
        $merchant_data = '';
        foreach ($cc_post_data as $key => $value) {
            $merchant_data .= $key . '=' . $value . '&';
        }
        $data['merchant_data'] = $merchant_data;
        include('Crypto.php');
        $data['working_key'] =  $working_key = '14009EDD96791A43248582F20364D4C8'; //Shared by CCAVENUES
        $data['access_code'] =  $access_code = 'AVUC05CH96AM83CUMA';
        $data['encrypted_data'] = $encrypted_data =  encrypt($merchant_data, $working_key); // Method for encrypting the data. //Shared by CCAVENUES
        // echo "<pre>";
        // print_R($data);
        // die();
        $post_array = array(
            'encRequest' => $encrypted_data,
            'access_code' => $access_code

        );
        $data['production_url'] = $production_url = '	https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction&encRequest=' . $encrypted_data . '&access_code=' . $access_code;


        // $curl_handle = curl_init();
        // curl_setopt($curl_handle, CURLOPT_URL, 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction');
        // curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curl_handle, CURLOPT_POST, 1);
        // curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt($curl_handle, CURLOPT_POSTFIELDS, urldecode(http_build_query($post_array)));
        // $buffer = curl_exec($curl_handle);

        // curl_close($curl_handle);
        // print_r($buffer);

        // return json_decode($buffer);
        // print_r($data);
        // die();
        $View =  $this->load->view('checkout/testcc.php', $data, true);
        $this->output->set_output($View);
    }
    
    public function cc_sucess()
    {

        include('Crypto.php');
        $merchant_id = 21651;

        $workingKey = '14009EDD96791A43248582F20364D4C8';      //Working Key should be provided here.
        $encResponse = $_POST["encResp"];          //This is the response sent by the CCAvenue Server
        $rcvdString = decrypt($encResponse, $workingKey);    //Crypto Decryption used as per the specified working key.
        $order_status = "";
        $decryptValues = explode('&', $rcvdString);
        $dataSize = sizeof($decryptValues);
        $this->load->helper('url');
        echo "<center>";
        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);
            if ($i == 3)  $order_status = $information[1];
            if ($i == 1) $data['bank_ref_no'] = $refernece_no = $information[1];
            if ($i == 0) $data['increment_id'] = $increment_id = $information[1];
        }
        if ($order_status === "Success") {
            // echo "asdasd" . $refernece_no . $order_status;
            echo "<br>Thank you for shopping with us. Your credit card has been charged and your transaction is successful. We will be shipping your order to you soon.";
            $encode_oid = base64_encode($increment_id);
            $post_arr = array('order_id' => $increment_id, 'transaction_id' => $refernece_no);
            $response_data = CheckoutRepository::update_payment_transaction_id($post_arr);
            echo $success_url = base_url() . 'order/success/?key=' . $encode_oid;
            header("location:" . $success_url);
            die();
            // rediect($success_url);
            $this->test_split_cc($refernece_no);


            /////////////////////////////////////
        } else if ($order_status === "Aborted") {
            echo "<br>Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail";

            $encode_oid = base64_encode($increment_id);
            echo $success_url = base_url() . 'order/failed/?key=' . $encode_oid;

            header("location:" . $success_url);
            die();
        } else if ($order_status === "Failure") {
            echo "<br>Thank you for shopping with us.However,the transaction has been declined.";
            $encode_oid = base64_encode($increment_id);
            echo $success_url = base_url() . 'order/failed/?key=' . $encode_oid;

            header("location:" . $success_url);
            die();
        } else {
            echo "<br>Security Error. Illegal access detected";
            $encode_oid = base64_encode($increment_id);
            echo $success_url = base_url() . 'order/failed/?key=' . $encode_oid;

            header("location:" . $success_url);
            die();
        }
        // echo "<br><br>";
        // echo "<table cellspacing=4 cellpadding=4>";
        // for ($i = 0; $i < $dataSize; $i++) {
        //     $information = explode('=', $decryptValues[$i]);
        //     echo '<tr><td>' . $information[0] . '</td><td>' . $information[1] . '</td></tr>';
        // }
        // echo "</table><br>";
        // echo "</center>";
    }
    public function  test_split_cc($refno)
    {
        //////////////////////////////////////
        // include('Crypto.php');
        $merchant_id = 21651;
        $data['bank_ref_no'] = $bank_ref_no = $refno;
        $order_id = 552;
        $increment_id = 1245;
        $post_arr =  array('order_id' => $order_id);
        $response_data = CheckoutRepository::get_price_split_for_publisher($post_arr);
        $cancel_url = base_url() . 'order/failed/?key=' . $encode_oid;
        $success_url = base_url() . 'CheckoutController/cc_split_sucess/?key=' . $encode_oid;
        $notifyURL = base_url() . 'order/ipn/?quote_id=' . $quote_id;
        $tid = time();

        // print_r($cc_post_data);
        // die();
        $publisher_Payment = array();
        $merchant_comm = 0;
        $total_price = 0;
        if (isset($response_data->statusCode) && $response_data->statusCode == '200') {
            $i = 1;
            foreach ($response_data->publisher_item_details as $Kpublisher_id => $val_pub) {
                // print_r($val_pub->publisher_details->commision_percent);
                $commision_percent = $val_pub->publisher_details->commision_percent;
                $seller_price =   $val_pub->total_seller_items_price;
                $total_price += $val_pub->total_seller_items_price;
                $Merchant_cost = $seller_price * ($commision_percent / 100);
                $publisher_cost =  $seller_price - $Merchant_cost;
                $merchant_comm += $Merchant_cost;
                $pub_id_test = 'Test' . $i;
                $publisher_Payment[] = array('splitAmount' => number_format($publisher_cost, 2, '.', ''), 'subAccId' => $pub_id_test);
                $i++;
            }
        }
        $cc_post_data = array(
            'tid' =>  $tid,
            'order_id' => $increment_id,
            'merchant_id' => $merchant_id,
            'currency' => 'INR',
            'access_code' => 'AVWY05KG08CE27YWEC',
            'redirect_url' =>  $success_url,
            'cancel_url' => $cancel_url,
            'language' => 'EN',
            'command' => 'createSplitPayout',
            'split_tdr_charge_type' => 'M',
            'merComm' => $merchant_comm,
            'split_data_list' => $publisher_Payment,
            'split_data' => 'Yes',
            'request_type' => 'JSON',
            'reference_no' => $bank_ref_no,
            'version' => 1.2
        );

        // $cc_post_data = array(
        //     'redirect_url' =>  $success_url,
        //     'cancel_url' => $cancel_url,
        //     'language' => 'EN',
        //     'command' => 'createSplitPayout',
        //     'split_tdr_charge_type' => 'M',
        //     'merComm' => $merchant_comm,
        //     'split_data_list' => $publisher_Payment,
        //     'split_data' => 'Yes',
        //     'request_type' => 'JSON',
        //     'reference_no' => $bank_ref_no,
        //     'version' => 1.2
        // );
        $data['split_data_list'] = $split_data_list = json_encode($publisher_Payment);
        $data['merComm'] = $merComm =  number_format($merchant_comm, 2, '.', '');
        $merchant_data = '';
        foreach ($cc_post_data as $key => $value) {
            $merchant_data .= $key . '=' . $value . '&';
        }
        $data['merchant_data'] = $merchant_data;
        $data['working_key'] =  $working_key = '14009EDD96791A43248582F20364D4C8'; //Shared by CCAVENUES
        $data['access_code'] =  $access_code = 'AVUC05CH96AM83CUMA';
        $data['encrypted_data'] = $encrypted_data =  encrypt($merchant_data, $working_key);
        // print_R($data);
        // die();
    ?>
        <!-- <form method="post" name="redirect" action="https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction"> -->
        <form method="post" name="redirect" action="https://apitest.ccavenue.com/apis/servlet/DoWebTrans">
            <!-- <form method="post" name="redirect" action="https://180.179.175.17/apis/servlet/DoWebTrans"> -->
            <?php
            echo "<input type=hidden name=encRequest value=$encrypted_data>";
            echo "<input type=hidden name=access_code value=$access_code>";
            echo "<input type=hidden name=Access_code value=$access_code>";
            echo "<input type=hidden name=Command value=createSplitPayout>";
            echo "<input type=hidden name=command value=createSplitPayout>";
            echo "<input type=hidden name=request_type value=JSON>";
            echo "<input type=hidden name=split_tdr_charge_type value=M>";
            echo "<input type=hidden name=reference_no value=$bank_ref_no>";
            echo "<input type=hidden name=split_data_list value=$split_data_list>";
            echo "<input type=hidden name=merComm value=$merComm>";
            echo "<input type=hidden name=split_data value=Yes>";
            echo "<input type=hidden name=version value=1.2>";
            ?>
        </form>
        <script language='javascript'>
            // document.redirect.submit();
        </script>
<?php
        $View =  $this->load->view('checkout/testsplitcc.php', $data, true);
        $this->output->set_output($View);
    }
    public function cc_split_sucess()
    {
        $workingKey = '2314A21051BDE9127D391B7CB73C010A';    //Working Key should be provided here.
        $encResponse = $_POST["encResp"];            //This is the response sent by the CCAvenue Server
        $rcvdString = decrypt($encResponse, $workingKey);        //Crypto Decryption used as per the specified working key.
        $order_status = "";
        $decryptValues = explode('&', $rcvdString);
        $dataSize = sizeof($decryptValues);
        echo "<center>";

        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);
            if ($i == 3)    $order_status = $information[1];
        }

        if ($order_status === "Success") {
            echo "<br>Thank you for shopping with us. Your credit card has been charged and your transaction is successful. We will be shipping your order to you soon.";
        } else if ($order_status === "Aborted") {
            echo "<br>Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail";
        } else if ($order_status === "Failure") {
            echo "<br>Thank you for shopping with us.However,the transaction has been declined.";
        } else {
            echo "<br>Security Error. Illegal access detected";
        }

        echo "<br><br>";

        echo "<table cellspacing=4 cellpadding=4>";
        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);
            echo '<tr><td>' . $information[0] . '</td><td>' . $information[1] . '</td></tr>';
        }

        echo "</table><br>";
        echo "</center>";
    }
    public function success()
    {
        $CURRENCY_CODE = 'MUR';

        if (!empty($this->session->userdata('CURRENCY_CODE'))) {
            $CURRENCY_CODE = $this->session->userdata('CURRENCY_CODE');
        }


        $data['PageTitle'] = 'Thank You';

        $site_url = base_url();

        $customer_id = $this->session->userdata('LoginID') ?? '0';

        $key = $_GET['key'] ?? '';
        $PayerID = $_GET['PayerID'] ?? '';

        if ($key !== '' && $PayerID === '') {
            $lang_code = '';
            if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
                $lang_code = $this->session->userdata('lcode');
            }

            $response2 = CommonRepository::get_table_data([
                'table_name' => 'sales_order',
                'database_flag' => 'own',
                'where' => 'increment_id = ?',
                'params' => [base64_decode($key)]
            ]);


            if (!empty($response2) && $response2->statusCode == 200) {

                $OrderInfo = $response2->tableData[0];

                $data['gtagEvent'] = (object)[
                    'transaction_id' => $OrderInfo->order_barcode,
                    'value' => $OrderInfo->grand_total,
                    'tax' => $OrderInfo->shipping_tax_amount + $OrderInfo->tax_amount,
                    'shipping' => $OrderInfo->shipping_amount,
                    'currency' => 'EUR',
                    'coupon' => $OrderInfo->voucher_code . $OrderInfo->coupon_code,
                ];

                if ($OrderInfo->status == 7) {
                    $order_id = $OrderInfo->order_id;
                    $data['order_id'] = $OrderInfo->order_id;
                    $customer_id = $OrderInfo->customer_id;
                    $shop_logo = SITE_LOGO;

                    $data['webshop_details'] = CommonRepository::get_webshop_details();
                    if (!empty($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
                        // $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                        $webshopname = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_name);
                    }
                    // $temp = GlobalRepository::get_fbc_users_shop()?->result;
                    // if (!empty($data['webshop_name'])) {
                    // 	$data['shop_flag']=$data['webshop_name']->shop_flag;
                    // }

                    $fbc_shop_details = GlobalRepository::get_fbc_users_shop();
                    $fbc_users_shop = $fbc_shop_details->result;

                    $data['paymentMethodcod'] = '';
                    if (!empty($fbc_users_shop)) {
                        // payment method
                        $response_cod = CommonRepository::get_table_data([
                            'table_name' => 'sales_order_payment',
                            'database_flag' => 'own',
                            'where' => 'order_id = ?',
                            'params' => [$data['order_id']]
                        ]);


                        if (!empty($response_cod) && isset($response_cod->tableData[0])) {
                            $data['paymentMethodcod'] = $response_cod->tableData[0]->payment_method;
                        }
                        //payment method
                        // billing mobile number
                        $response_mob = CommonRepository::get_table_data([
                            'table_name' => 'sales_order_address',
                            'database_flag' => 'own',
                            'where' => 'order_id = ? and address_type = ?',
                            'params' => [$data['order_id'], 1]
                        ]);


                        if (!empty($response_mob) && isset($response_mob)) {
                            $data['billingMobile'] = $response_mob->tableData[0]->mobile_no;
                        } else {
                            $data['billingMobile'] = '';
                        }
                        //payment method
                        $data['shop_flag'] = $fbc_users_shop->shop_flag ?? 0;
                        if ($data['shop_flag'] == 2 && $data['paymentMethodcod'] === 'cod') {
                            $codArr = array('order_id' => $data['order_id'], 'mobile_no' => $data['billingMobile']);
                            if ($this->session->userdata('checkout_message')) {
                                $response_cod_otp = CheckoutRepository::send_cod_otp($codArr);
                            }
                        } else {


                            $shop_logo = SITE_LOGO;
                            $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
											<img src="https://ymstore.whuso.in/uploads/yellow-markets-logo.png" alt="Yellow Markets" width="248" height="auto">
										</a>';

                            $orderArr = array('order_id' => $order_id, 'currency_code' => $CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);


                            CheckoutRepository::send_order_confirmation_email($orderArr);

                            $b2bArr = array('order_id' => $order_id, 'currency_code' => $CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);
                             //print_r($b2bArr);
                            // exit;

                            CheckoutRepository::generate_b2b_order_for_webshop($b2bArr);

                            // $this->session->unset_userdata('QuoteId');
                            if ($customer_id != 0) {
                                $check_quote = CheckoutRepository::remove_quote(
                                    ['quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $customer_id]
                                );

                                $this->session->unset_userdata('QuoteId');
                            }
                            $this->session->set_userdata('checkout_message', 'Order created successfully');
                        }
                    }
                } else {
                    redirect(base_url());
                }
            }
        } elseif (isset($_GET['sessionId']) || $PayerID != '') {

            $key = $PayerID != '' ? $_GET['key'] : $_GET['keys'];

            // Get Order Details
            $response2 = CommonRepository::get_table_data([
                'table_name' => 'sales_order',
                'database_flag' => 'own',
                'where' => 'increment_id = ?',
                'params' => [base64_decode($key)]
            ]);

            if (!empty($response2) && $response2->statusCode == 200) { // && stripe_success_page_flag=0
                $OrderInfo = $response2->tableData[0];
                $customer_id = $OrderInfo->customer_id;
                // get data order_payment stripe stripe_success_page_flag =0
                // get payment details
                $response3 = CommonRepository::get_table_data([
                    'table_name' => 'sales_order_payment',
                    'database_flag' => 'own',
                    'where' => 'order_id = ?',
                    'params' => [$OrderInfo->order_id]
                ]);

                if (!empty($response3) && $response3->statusCode == 200 && isset($response3->tableData[0]->stripe_success_page_flag) && $response3->tableData[0]->stripe_success_page_flag == 0) {
                    $this->session->set_userdata('checkout_message', 'Order created successfully');

                    // update order
                    CheckoutRepository::update_order_payment_status_info(
                        ['order_id' => $OrderInfo->order_id, 'stripe_success_page_flag' => 1]
                    );

                    $data['paymentMethodcod'] = '';

                    // shop flag
                    // $data['webshop_name'] = GlobalRepository::get_fbc_users_shop()?->result;
                    // if (!empty($data['webshop_name'])) {
                    // 	$data['shop_flag']=$data['webshop_name']->shop_flag;
                    // }
                } else if ($PayerID != '') {
                } else {
                    redirect(base_url());
                }
            } else {
                redirect(base_url());
            }
        }
        // print_r($_SESSION);
        // die();
        if ($PayerID === '') {
            CheckoutRepository::remove_quote(
                ['quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $customer_id]
            );
            $this->session->unset_userdata('QuoteId');
        }


        if ($this->session->userdata('checkout_message')) {
            $this->session->unset_userdata('checkout_message');
        } else {
            redirect('/');
        }
        $this->template->load('checkout/success', $data);
    }

    public function failed()
    {


        $key = $_GET['key'];
        if ($key != '') {
            $increment_id = base64_decode($key);

            $table2 = 'sales_order';
            $flag = 'own';
            $where = 'increment_id = ?';
            $params = array($increment_id);
            $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
            $response2 = CommonRepository::get_table_data($postArr2);
            // print_r($response2);
            if (!empty($response2) && isset($response2) && $response2->statusCode == 200) {
                $OrderInfo = $response2->tableData[0];
                $order_id = $OrderInfo->order_id;
                $order_staus = $OrderInfo->status;

                if ($order_staus == 7) {
                    $orderArr = array('order_id' => $order_id, 'status' => 3);  // 3-Cancelled
                    CheckoutRepository::update_order_status($orderArr);
                } else {
                    redirect(base_url());
                }
            }
        }

        $this->session->set_flashdata('msg', 'failed_payment');
        $this->template->load('checkout/failed');
        // redirect(base_url() . 'order/failed?key=' . $key);
    }

    function ipn()
    {
        if (isset($_GET['request_id']) && $_GET['request_id'] != '') { // request payment

            $paypalInfo    = $this->input->post();
            $data['user_id'] = $paypalInfo['custom'];
            $data['item_number']    = $paypalInfo["item_number"];
            $data['txn_id']    = $paypalInfo["txn_id"];
            $data['payment_gross'] = $paypalInfo["mc_gross"];
            $data['currency_code'] = $paypalInfo["mc_currency"];
            $data['payer_email'] = $paypalInfo["payer_email"];
            $data['payment_status']    = $paypalInfo["payment_status"];

            $paypalURL = $this->paypal_lib->paypal_url;
            $result    = $this->paypal_lib->curlPost($paypalURL, $paypalInfo);

            //check whether the payment is verified
            if (preg_match("/VERIFIED/i", $result)) {
                $shopcode = SHOPCODE;
                $shop_id = SHOP_ID;

                $table2 = 'sales_order';
                $flag = 'own';
                $where = 'order_id = ?';
                $params = array($paypalInfo["item_number"]);
                $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                $response2 = CommonRepository::get_table_data($shopcode, $shop_id, $postArr2);
                if (!empty($response2) && isset($response2)) {
                    $OrderInfo = $response2->tableData[0];
                    $lang_code = '';
                    if (!empty($OrderInfo->language_code) && $OrderInfo->is_default_language == 0) {
                        $lang_code = $OrderInfo->language_code;
                    }


                    if ($paypalInfo["payment_status"] == 'Completed') {
                        // insert histroy table
                        $table3 = 'sales_order_payment';
                        $flag3 = 'own';
                        $where3 = 'order_id = ?';
                        $params3 = array($OrderInfo->order_id);
                        $postArr3 = array('table_name' => $table3, 'database_flag' => $flag3, 'where' => $where3, 'params' => $params3);
                        $response3 = CommonRepository::get_table_data($shopcode, $shop_id, $postArr3);

                        if (!empty($response3) && isset($response3) && $response3->statusCode == 200 && isset($response3->tableData[0]->stripe_success_page_flag) && $response3->tableData[0]->stripe_success_page_flag == 0) {

                            $salesPaymentData = $response3->tableData[0];
                            $old_payment_id = $salesPaymentData->payment_id;
                            $old_payment_method_id = $salesPaymentData->payment_method_id;
                            $old_payment_method = $salesPaymentData->payment_method;
                            $old_payment_method_name = $salesPaymentData->payment_method_name;
                            $old_payment_type = $salesPaymentData->payment_type;
                            $old_currency_code = $salesPaymentData->currency_code;
                            // insert old sales_order_payment data new sales_order_payment_history
                            $order_payment_array = array('order_id' => $OrderInfo->order_id, 'order_payment_id' => $old_payment_id, 'payment_method_id' => $old_payment_method_id, 'payment_method' => $old_payment_method, 'payment_method_name' => $old_payment_method_name, 'payment_type' => $old_payment_type);
                            $dataResponse = CheckoutRepository::insert_old_payment_method($shopcode, $shop_id, $order_payment_array);
                            // order payment table data updated
                            $update_payment_method_id = 1; // 1-paypal
                            $update_payment_method = 'paypal_express';
                            $update_payment_method_name = 'Paypal Express Checkout';
                            $update_payment_type = 1;
                            $orderArrUpdated = array('order_id' => $OrderInfo->order_id, 'payment_method_id' => $update_payment_method_id, 'payment_method' => $update_payment_method, 'payment_method_name' => $update_payment_method_name, 'payment_type' => $update_payment_type);
                            $ResponseArrUpdated = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderArrUpdated);
                        }

                        $response_data =  json_encode($data);
                        $this->session->set_userdata('checkout_message', 'Request repayment successfully');
                        $orderArr_paypal = array('order_id' => $paypalInfo["item_number"], 'transaction_id' => $paypalInfo["txn_id"], 'payment_intent' => $paypalInfo["payer_id"], 'pay_response' => $response_data, 'status' => strtolower($paypalInfo["payment_status"]));
                        $ResponseArr2 = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderArr_paypal);
                    } //transaction complete and and order status

                } //Order Check
            }
        } else {

            $quote_id = $_GET['quote_id'];

            if (isset($quote_id) && $quote_id != '') {
                $paypalInfo    = $this->input->post();
                $data['user_id'] = $paypalInfo['custom'];
                $data['item_number']    = $paypalInfo["item_number"];
                $data['txn_id']    = $paypalInfo["txn_id"];
                $data['payment_gross'] = $paypalInfo["mc_gross"];
                $data['currency_code'] = $paypalInfo["mc_currency"];
                $data['payer_email'] = $paypalInfo["payer_email"];
                $data['payment_status']    = $paypalInfo["payment_status"];

                $paypalURL = $this->paypal_lib->paypal_url;
                $result    = $this->paypal_lib->curlPost($paypalURL, $paypalInfo);

                //check whether the payment is verified
                if (preg_match("/VERIFIED/i", $result)) {
                    //insert the transaction data into the database

                    $shopcode = SHOPCODE;
                    $shop_id = SHOP_ID;

                    $table2 = 'sales_order';
                    $flag = 'own';
                    $where = 'order_id = ?';
                    $params = array($paypalInfo["item_number"]);
                    $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                    $response2 = CommonRepository::get_table_data($shopcode, $shop_id, $postArr2);
                    if (!empty($response2) && isset($response2)) {
                        $OrderInfo = $response2->tableData[0];
                        $lang_code = '';
                        if (!empty($OrderInfo->language_code) && $OrderInfo->is_default_language == 0) {
                            $lang_code = $OrderInfo->language_code;
                        }


                        if ($paypalInfo["payment_status"] == 'Completed' && $OrderInfo->status == 7) {

                            $site_url = base_url();

                            $response_data =  json_encode($data);
                            $this->session->set_userdata('checkout_message', 'Order created successfully');
                            $orderArr_paypal = array('order_id' => $paypalInfo["item_number"], 'transaction_id' => $paypalInfo["txn_id"], 'payment_intent' => $paypalInfo["payer_id"], 'pay_response' => $response_data, 'status' => strtolower($paypalInfo["payment_status"]));
                            $ResponseArr2 = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderArr_paypal);

                            /*$webshop_details = CommonRepository::get_webshop_details($shopcode,$shop_id);
                            if(!empty($webshop_details) && isset($webshop_details) && $webshop_details->is_success=='true'){
                                $shop_logo = $this->encryption->decrypt($webshop_details->FbcWebShopDetails->site_logo);
                            }*/

                            $table = 'fbc_users_webshop_details';
                            $flag = 'main';
                            $where = 'shop_id  = ?';
                            $params = array($shop_id);
                            $post_org_name_array = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                            $webshop_details = CommonRepository::get_table_data($shopcode, $shop_id, $post_org_name_array);
                            if (isset($webshop_details) && $webshop_details->is_success == 'true') {
                                $shop_logo = $this->encryption->decrypt($webshop_details->tableData[0]->site_logo);
                            }

                            $table = 'fbc_users_shop';
                            $flag = 'main';
                            $where = 'shop_id  = ?';
                            $params = array($shop_id);
                            $post_org_name_array = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                            $webshop_name = CommonRepository::get_table_data($shopcode, $shop_id, $post_org_name_array);
                            if (isset($webshop_name) && $webshop_name->is_success == 'true') {
                                $webshopname = $webshop_name->tableData[0]->org_shop_name;
                            }

                            $shop_logo = SITE_LOGO . '/' . $shop_logo;
                            $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
                                    <img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
                                </a>';

                            $orderArr = array('order_id' => $paypalInfo["item_number"], 'currency_code' => CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);
                            $ResponseArr = CheckoutRepository::send_order_confirmation_email($shopcode, $shop_id, $orderArr);

                            $b2bArr = array('order_id' => $paypalInfo["item_number"], 'currency_code' => CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);
                            $b2bResponseArr = CheckoutRepository::generate_b2b_order_for_webshop($shopcode, $shop_id, $b2bArr);


                            if (!empty($b2bResponseArr) && isset($b2bResponseArr) && $b2bResponseArr->is_success == false) {
                            }

                            $cartArr = array('quote_id' => $quote_id, 'customer_id' => $paypalInfo['custom']);
                            $cart = CheckoutRepository::remove_quote($shopcode, $shop_id, $cartArr);
                        }
                    }
                }
            } else {
            }
        }
    }

    public function setCheckoutMethod()
    {
        if (isset($_POST['checkout_method']) && $_POST['checkout_method'] != '') {

            $checkout_method = $_POST['   '];
            if ($this->session->userdata('QuoteId')) {
                $quote_id = $this->session->userdata('QuoteId');
            } else {
                redirect('/');
            }

            $cartArr = array('quote_id' => $this->session->userdata('QuoteId'), 'checkout_method' => $checkout_method);
            $cart = CheckoutRepository::set_checkout_method($shopcode, $shop_id, $cartArr);
            echo "success";
            exit;
        } else {
            echo "error";
            exit;
        }
    }

    public function saveQuoteAddress()
    {

        if (empty($_POST)) {
            echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
            exit;
        }


        $error = '';


        if ($this->session->userdata('QuoteId')) {
            $quote_id = $this->session->userdata('QuoteId');
        } else {
            redirect('/');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
            $session_id = $this->session->userdata('LoginToken');
        } else {
            $customer_id = '0';
            $session_id = $this->session->userdata('sis_session_id');
        }

        $postArr = array('quote_id' => $this->session->userdata('QuoteId'), 'session_id' => $session_id, 'customer_id' => $customer_id);

        // if (GlobalRepository::get_fbc_users_shop()->result->shop_flag === 2) {
        // 	// cod updated
        // 	$codArr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'customer_id' => $customer_id);
        // 	CartRepository::payment_charge_updated($shopcode, $shop_id, $codArr);
        // 	//end cod
        // }

        foreach ($_POST as $key => $value) {
            $postArr[$key] = $value;
        }

        $postArr['customer_id'] = $customer_id;

        $postArr['company_name'] = ((isset($_POST['company_name']) && $_POST['company_name'] != '') ? $_POST['company_name'] : '');
        $postArr['vat_no'] = $vat_no = ((isset($_POST['vat_no']) && $_POST['vat_no'] != '') ? $_POST['vat_no'] : '');
        // $postArr['consulation_no'] = ((isset($_POST['consulation_no']) && $_POST['consulation_no'] !='')?$_POST['consulation_no']:'');
        // $postArr['res_company_name'] = ((isset($_POST['res_company_name']) && $_POST['res_company_name'] !='')?$_POST['res_company_name']:'');
        // $postArr['res_company_address'] =((isset($_POST['res_company_address']) && $_POST['res_company_address'] !='')?$_POST['res_company_address']:'');

        // $postArr['vat_vies_valid_flag'] = $vat_vies_valid_flag = ((isset($_POST['vat_vies_valid_flag']) && $_POST['vat_vies_valid_flag'] !='')?$_POST['vat_vies_valid_flag']:'');

        $response = CheckoutRepository::save_quote_address($postArr);

        if (!empty($response) && isset($response) && $response->is_success == 'true') {

            if ($this->session->userdata('session_vat_flag') == 0) {
                $this->session->set_userdata('ip_country', $response->country_code);
            }

            if ($response->eu_shipping_response != '') {
                $eu_shipping_response = $response->eu_shipping_response;
                $result = '';
                foreach ($eu_shipping_response as $shipping_method) {
                    $estimate_delivery = $response->quote_estimate_delivery + $shipping_method->delivery_days;
                    $add_dates = '+' . $estimate_delivery . "days";
                    $new_date = date('d-m-Y', strtotime($add_dates));

                    $shipping_tax_percent = $this->session->userdata('vat_percent');
                    $shipping_tax_amount = ($shipping_method->ship_rate * $shipping_tax_percent) / 100;
                    $shipping_amount = $shipping_method->ship_rate + $shipping_tax_amount;

                    $checked = ($this->session->userdata('shipping_charge_id') == $shipping_method->id ? 'checked' : '');

                    $result .= '<label class="radio-label-checkout"><input class="radio-checkout  single-shipping-method" type="radio" value="' . $shipping_method->id . '" name="shipping_method" ' . $checked . '>' . $shipping_method->ship_method_name . ' <span class="radio-check"></span> <small>(Rate  ' . CURRENCY_TYPE . number_format($shipping_amount, 2) . ')</small>  - <small>Expected Delivery in ' . $estimate_delivery . ' days.</small></label>';
                }

                $shipping_response_flag = 1;
            } else {
                //$result = 'No shipping rate found.';
                $identifier = 'shipping_method_not_available';

                $ApiResponse =  GlobalRepository::get_custom_variable($identifier);
                if ($ApiResponse->statusCode == '200') {
                    $RowCV = $ApiResponse->custom_variable;
                    $msg_for_customer = $RowCV->value;
                } else {
                    $msg_for_customer = '';
                }


                $result = '<p><label class="radio-label-checkout">' . $msg_for_customer . '</label> </p>';
                $shipping_response_flag = 0;
            }

            // if ($_POST['address_type'] == 1 && $vat_vies_valid_flag == 1) {
            // 	$this->session->set_userdata('vat_no_session', $vat_no);
            // } elseif ($_POST['address_type'] == 1 && $vat_vies_valid_flag == 0) {
            // 	$this->session->unset_userdata('vat_no_session');
            // }

            $customer_address_id = (isset($response->customer_address_id) && $response->customer_address_id != '') ? $response->customer_address_id : '';
            $email_id = (isset($response->email_id) && $response->email_id != '') ? $response->email_id : '';
            $mobile_no = (isset($response->mobile_no) && $response->mobile_no != '') ? $response->mobile_no : '';
            echo json_encode(array('flag' => 1, 'msg' => $response->message, 'address_id' => $customer_address_id, 'email_id' => $email_id, 'mobile_no' => $mobile_no, 'country_code' => $response->country_code, 'eu_shipping_response' => $result, 'shipping_response_flag' => $shipping_response_flag));
            exit;
        } elseif (!empty($response) && isset($response) && $response->is_success == 'false') {
            echo json_encode(array('flag' => 0, 'msg' => $response->message));
            exit;
        } else {
            echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong'));
            exit;
        }
    }

    public function saveShippingMethod()
    {
        if ($_POST['shipping_charge_id'] != '') {
            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;

            if ($this->session->userdata('QuoteId')) {
                $quote_id = $this->session->userdata('QuoteId');
            } else {
                redirect('/');
            }

            $shipping_charge_id = $_POST['shipping_charge_id'];

            $cartArr = array('quote_id' => $this->session->userdata('QuoteId'), 'vat_percent' => $this->session->userdata('vat_percent'), 'shipping_charge_id' => $shipping_charge_id);

            $result = CheckoutRepository::save_eu_shippping_method($shopcode, $shop_id, $cartArr);

            $this->session->set_userdata('shipping_charge_id', $shipping_charge_id);

            return $result;
        } else {
            echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong'));
            exit;
        }
    }



    public function saveQuotePaymentMethod()
    {
        // echo "<pre>";
        // print_r($_POST);die;
        if ($_POST['payment_id'] != '' && $_POST['payment_type'] != '') {


            if ($this->session->userdata('QuoteId')) {
                $quote_id = $this->session->userdata('QuoteId');
            } else {
                redirect('/');
            }

            $payment_id = $_POST['payment_id'];
            $payment_type = $_POST['payment_type'];

            $cartArr = array('quote_id' => $this->session->userdata('QuoteId'), 'payment_type' => $payment_type, 'payment_id' => $payment_id);

            $response = CheckoutRepository::set_checkout_payment_method($cartArr);
            if (!empty($response) && isset($response) && $response->is_success == 'true') {
                echo json_encode(array('flag' => 1, 'msg' => $response->message));
                exit;
            } elseif (!empty($response) && isset($response) && $response->is_success == 'false') {
                echo json_encode(array('flag' => 0, 'msg' => $response->message));
                exit;
            } else {
                echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong'));
                exit;
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong'));
            exit;
        }
    }

    public function ip_visitor_country()
    {
        return ip_visitor_country();
    }


    public function razorpaycallback()
    {
        if (!empty($this->input->post('razorpay_order_id')) && !empty($this->input->post('razorpay_payment_id')) && !empty($this->input->post('merchant_order_id'))) {
            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;

            $razorpay_payment_id = $this->input->post('razorpay_payment_id');
            $razorpay_order_id = $this->input->post('razorpay_order_id');
            $razorpay_signature = $this->input->post('razorpay_signature');

            $merchant_order_id = $increment_id = $this->input->post('merchant_order_id');
            $currency_code = CURRENCY_CODE;
            $amount = $this->input->post('merchant_total');
            $payment_id = $_POST['payment_id'];
            $order_id = $this->input->post('order_id');
            $encode_oid = base64_encode($increment_id);



            $request_param = array('amount' => $amount, 'currency' => $currency_code, 'razorpay_order_id' => $razorpay_order_id, 'razorpay_payment_id' => $razorpay_payment_id, 'merchant_order_id' => $merchant_order_id);

            $success = false;
            $error = '';

            $pay_request = json_encode($request_param);
            $orderArr = array('order_id' => $order_id, 'pay_request' => $pay_request);
            $ResponseArr = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderArr);

            //verify Signature
            $table2 = 'webshop_payments';
            $flag = 'own';
            $where = 'payment_id = ?';
            $params = array($payment_id);
            $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
            $response2 = CommonRepository::get_table_data($shopcode, $shop_id, $postArr2);
            if (!empty($response2) && isset($response2) && $response2->is_success == true) {
                $Row = $response2->tableData[0];
                $gateway_details = json_decode($Row->gateway_details);

                $_api_key = (isset($gateway_details->api_key) && $gateway_details->api_key != '') ? $gateway_details->api_key : '';
                $_api_key_secret = (isset($gateway_details->api_key_secret) && $gateway_details->api_key_secret != '') ? $gateway_details->api_key_secret : '';
            } else {
                $_api_key = '';
                $_api_key_secret = '';
            }

            if ($_api_key != '' && $_api_key_secret != '') {
                $payload = $merchant_order_id . '|' . $razorpay_payment_id;
                $payload2 = $razorpay_order_id . '|' . $razorpay_payment_id;

                $expectedSignature = hash_hmac('sha256', $payload, $_api_key_secret);
                $expectedSignature2 = hash_hmac('sha256', $payload2, $_api_key_secret);


                if ($expectedSignature2 == $razorpay_signature) {
                    $success = true;
                }
            } else {
                $success = false;
            }

            if ($success === true) {
                /*---------------------save transasction-------------------------------------*/

                $orderArr = array('order_id' => $order_id, 'transaction_id' => $razorpay_payment_id);
                $ResponseArr2 = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderArr);


                $redirect_to = base_url() . 'order/success/?key=' . $encode_oid;

                $this->session->set_userdata('checkout_message', 'Order created successfully');

                echo json_encode(array('flag' => 1, 'msg' => "Order created successfully", "redirect" => $redirect_to));
                exit;
            } else {
                $redirect_to = base_url() . 'order/failed/?key=' . $encode_oid;


                echo json_encode(array('flag' => 0, 'msg' => $error, "redirect" => $redirect_to));
                exit;
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => "An error occured. Contact site administrator, please!"));
            exit;
        }
    }


    // callback method
    public function razorpaycallbackOld()
    {
        if ($_SESSION['QuoteId'] == '') {
            redirect(base_url('checkout', 'refresh'));
        }
        //print_r($_POST);exit;
        if (!empty($this->input->post('razorpay_payment_id')) && !empty($this->input->post('merchant_order_id'))) {
            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;

            $razorpay_payment_id = $this->input->post('razorpay_payment_id');
            $merchant_order_id = $increment_id = $this->input->post('merchant_order_id');
            $currency_code = CURRENCY_CODE;
            $amount = $this->input->post('merchant_total');

            $payment_id = $_POST['payment_id'];

            $order_id = $this->input->post('order_id');

            $encode_oid = base64_encode($increment_id);

            $data = array(
                'amount' => $amount,
                'currency' => $currency_code,
                //  'receipt' => $increment_id
            );

            $request_param = array('amount' => $amount, 'currency' => $currency_code, 'razorpay_payment_id' => $razorpay_payment_id, 'merchant_order_id' => $merchant_order_id);

            $success = false;
            $error = '';

            $opayapi = '/webshop/update_order_payment_status_info'; //update order status


            try {
                $pay_info = array();
                $pay_request = json_encode($request_param);


                $orderArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'order_id' => $order_id, 'pay_request' => $pay_request);

                $ResponseArr = $this->restapi->post_method($opayapi, $orderArr);

                $apiUrl2 = '/webshop/get_table_data'; //get_table_data
                $table2 = 'webshop_payments';
                $flag = 'own';
                $where = 'payment_id = ?';
                $params = array($payment_id);
                $postArr2 = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                $response2 = $this->restapi->post_method($apiUrl2, $postArr2);
                if (isset($response2) && $response2->is_success == true) {
                    $Row = $response2->tableData[0];
                    $gateway_details = json_decode($Row->gateway_details);

                    $_api_key = (isset($gateway_details->api_key) && $gateway_details->api_key != '') ? $gateway_details->api_key : '';
                    $_api_key_secret = (isset($gateway_details->api_key_secret) && $gateway_details->api_key_secret != '') ? $gateway_details->api_key_secret : '';
                } else {
                    $_api_key = '';
                    $_api_key_secret = '';
                }



                if ($_api_key != '' && $_api_key_secret != '') {
                    $ch = $this->get_curl_handle($razorpay_payment_id, $data, $_api_key, $_api_key_secret);
                    //execute post
                    $result = curl_exec($ch);
                    //echo "<pre>";print_r(json_decode($result, true));exit;
                    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($result === false) {
                        $success = false;
                        $error = 'Curl error: ' . curl_error($ch);
                    } else {
                        $response_array = json_decode($result, true);
                        //echo "<pre>";print_r($response_array);exit;
                        //Check success response

                        if ($http_status === 200 and isset($response_array['error']) === false) {
                            $success = true;
                        } else {
                            $success = false;
                            if (!empty($response_array['error']['code'])) {
                                $error = $response_array['error']['code'] . ':' . $response_array['error']['description'];
                            } else {
                                $error = 'RAZORPAY_ERROR:Invalid Response <br/>' . $result;
                            }
                        }

                        /*-------------update payment------------------------------------*/
                        $pay_response = $result;
                        $orderArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'order_id' => $order_id, 'pay_response' => $pay_response);

                        $ResponseArr2 = $this->restapi->post_method($opayapi, $orderArr);
                    }
                    //close connection
                    curl_close($ch);
                } else {
                    $success = false;
                    echo json_encode(array('flag' => 0, 'msg' => 'Razorpay invalid gateway details.'));
                    exit;
                }
            } catch (Exception $e) {
                $success = false;
                $error = 'OPENCART_ERROR:Request to Razorpay Failed';
            }

            if ($success === true) {
                //echo "<pre>";print_r($response_array);
                //echo $response_array['card']['network'];exit;

                /*---------------------save transasction-------------------------------------*/

                $orderArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'order_id' => $order_id, 'transaction_id' => $response_array['id'], 'status' => $response_array['status']);

                $ResponseArr2 = $this->restapi->post_method($opayapi, $orderArr);


                $redirect_to = base_url() . 'order/success/?key=' . $encode_oid;

                $this->session->set_userdata('checkout_message', 'Order created successfully');

                echo json_encode(array('flag' => 1, 'msg' => "Order created successfully", "redirect" => $redirect_to));
                exit;
            } else {
                $redirect_to = base_url() . 'order/failed/?key=' . $encode_oid;


                echo json_encode(array('flag' => 0, 'msg' => $error, "redirect" => $redirect_to));
                exit;
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => "An error occured. Contact site administrator, please!"));
            exit;
        }
    }

    private function create_order_razorpay($data, $key_id, $key_secret)
    {
        $url = 'https://api.razorpay.com/v1/orders';

        //$fields_string = "amount=$amount";
        $params = http_build_query($data);
        //cURL Request
        //echo  $key_secret ;
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        //curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).'/ca-bundle.crt');
        return $ch;
    }

    // initialized cURL Request
    private function get_curl_handle($payment_id, $data, $key_id, $key_secret)
    {
        $url = 'https://api.razorpay.com/v1/payments/' . $payment_id . '/capture';

        //$fields_string = "amount=$amount";
        $params = http_build_query($data);
        //cURL Request

        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        //curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).'/ca-bundle.crt');
        return $ch;
    }


    public function refreshOrderTotals()
    {
        if ($this->session->userdata('QuoteId')) {
            $quote_id = $this->session->userdata('QuoteId');
        } else {
            redirect('/');
        }

        if ($this->session->userdata('sis_session_id')) {
            $session_id = $this->session->userdata('sis_session_id');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
        } else {
            $customer_id = 0;
        }


        $cartArr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'customer_id' => $customer_id);
        $cart = CartRepository::cart_listing($cartArr);
        if (!empty($cart) && isset($cart) && ($cart->is_success == 'true')) {
            $data['CartData'] = $cart->cartData;
            $table = 'sales_quote_address';
            $flag = 'own';
            $where = 'quote_id = ? AND  address_type = ? ';
            $order_by = 'ORDER BY address_id DESC';
            $params = array($quote_id, 2);
            $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
            $response = CommonRepository::get_table_data($postArr);
            if (!empty($response) && isset($response) && $response->is_success == 'true') {
                $data['ShipAddress'] = $response->tableData[0];
            } else {
                $data['ShipAddress'] = array();
            }

            $table1 = 'sales_quote_payment';
            $flag1 = 'own';
            $where1 = 'quote_id = ?';
            $params1 = array($quote_id);
            $postArr1 = array('table_name' => $table1, 'database_flag' => $flag1, 'where' => $where1, 'params' => $params1);
            $response1 = CommonRepository::get_table_data($postArr1);
            if (!empty($response1) && isset($response1) && $response1->is_success == 'true') {
                $data['PaymentDetails'] = $response1->tableData[0];
            } else {
                $data['PaymentDetails'] = array();
            }
        } else {
            redirect('/');
            $data['CartData'] = array();
        }
        // echo "<pre>";
        // print_r($data);
        // die;
        $data['QuoteId'] = $quote_id;
        $View = $this->load->view('checkout/order_review_totals', $data, true);
        $this->output->set_output($View);
    }

    public function refreshSidebar()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;


        if ($this->session->userdata('QuoteId')) {
            $quote_id = $this->session->userdata('QuoteId');
        } else {
            redirect('/');
        }

        if ($this->session->userdata('sis_session_id')) {
            $session_id = $this->session->userdata('sis_session_id');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
        } else {
            $customer_id = 0;
        }


        $cartArr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'customer_id' => $customer_id);
        $cart = CartRepository::cart_listing($cartArr);
        if (!empty($cart) && isset($cart) && ($cart->is_success == 'true')) {
            $data['CartData'] = $cart->cartData;

            $table = 'sales_quote_address';
            $flag = 'own';
            $where = 'quote_id = ? AND  address_type = ? ';
            $order_by = 'ORDER BY address_id DESC';
            $params = array($quote_id, 2);
            $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
            $response = CommonRepository::get_table_data($postArr);
            if (!empty($response) && isset($response) && $response->is_success == 'true') {
                $data['ShipAddress'] = $response->tableData[0];
            } else {
                $data['ShipAddress'] = array();
            }
        } else {
            redirect('/');
            $data['CartData'] = array();
            $data['ShipAddress'] = array();
        }

        $data['QuoteId'] = $quote_id;
        $View = $this->load->view('cart/cart_total', $data, true);
        $this->output->set_output($View);
    }


    public function refreshPaymentMethods()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;


        if ($this->session->userdata('QuoteId')) {
            $quote_id = $this->session->userdata('QuoteId');
        } else {
            redirect('/');
        }

        if ($this->session->userdata('sis_session_id')) {
            $session_id = $this->session->userdata('sis_session_id');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
        } else {
            $customer_id = 0;
        }

        //shop flag
        $table = 'fbc_users_shop';
        $flag = 'main';
        $where = 'shop_id  = ?';
        $params = array($shop_id);
        $post_org_name_array = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
        $data['webshop_name'] = CommonRepository::get_table_data($shopcode, $shop_id, $post_org_name_array);
        $data['shop_flag'] = '';
        if (!empty($data['webshop_name']) && isset($data['webshop_name']) && $data['webshop_name']->tableData[0]) {
            $data['shop_flag'] = $data['webshop_name']->tableData[0]->shop_flag;
        }

        $cartArr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'customer_id' => $customer_id);
        $cart = CartRepository::cart_listing($shopcode, $shop_id, $cartArr);
        if (!empty($cart) && isset($cart) && ($cart->is_success == 'true')) {
            $data['CartData'] = $cart->cartData;
            $table = 'sales_quote_address';
            $flag = 'own';
            $where = 'quote_id = ? AND  address_type = ? ';
            $order_by = 'ORDER BY address_id DESC';
            $params = array($quote_id, 2);
            $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
            $response = CommonRepository::get_table_data($shopcode, $shop_id, $postArr);
            if (!empty($response) && isset($response) && $response->is_success == 'true') {
                $data['ShipAddress'] = $response->tableData[0];
            } else {
                $data['ShipAddress'] = array();
            }

            /*quote data*/
            $quoteTable = 'sales_quote';
            $quoteFlag = 'own';
            $quoteWhere = 'quote_id = ? ';
            $quoteParams = array($quote_id);
            $quotePostArr = array('table_name' => $quoteTable, 'database_flag' => $quoteFlag, 'where' => $quoteWhere, 'order_by' => '', 'params' => $quoteParams);
            $quoteResponse = CommonRepository::get_table_data($shopcode, $shop_id, $quotePostArr);
            if (!empty($quoteResponse) && isset($quoteResponse) && $quoteResponse->is_success == 'true') {
                $data['quoteData'] = $quoteResponse->tableData[0];
            } else {
                $data['quoteData'] = array();
            }
            /*end quote data*/

            // Quote payment data
            $data['quote_payment_data'] = array();
            $quoteTable = 'sales_quote_payment';
            $quoteFlag = 'own';
            $quoteWhere = 'quote_id = ? ';
            $quoteParams = array($quote_id);
            $quotePostArr = array('table_name' => $quoteTable, 'database_flag' => $quoteFlag, 'where' => $quoteWhere, 'order_by' => '', 'params' => $quoteParams);
            $quoteResponse = CommonRepository::get_table_data($shopcode, $shop_id, $quotePostArr);
            if (!empty($quoteResponse) && isset($quoteResponse) && $quoteResponse->is_success == 'true') {
                foreach ($quoteResponse->tableData as $key => $value) {
                    $data['quote_payment_data'][] = $value;
                }
            }

            $payArr = array('country_code' => COUNTRY_CODE);
            $MethodResult = CheckoutRepository::payment_methods_listing($shopcode, $shop_id, $payArr);
            if (!empty($MethodResult) && isset($MethodResult) && ($MethodResult->is_success == 'true')) {
                $data['PaymentMethods'] = $MethodResult->PaymentMethods;
            } else {
                $data['PaymentMethods'] = array();
            }
        } else {
            redirect('/');
            $data['CartData'] = array();
            $data['ShipAddress'] = array();
            $data['PaymentMethods'] = array();
        }

        $data['QuoteId'] = $quote_id;
        $View = $this->load->view('checkout/payment_methods', $data, true);
        $this->output->set_output($View);
    }
    //cod
    public function otpVerifiedMethod()
    {
        if ($_POST['order_id'] != '' && $_POST['otp_password'] != '' && $_POST['phone_no'] != '') {

            $order_id = $_POST['order_id'];
            $otp_password = $_POST['otp_password'];

            // billing mobile number
            $table_mob = 'sales_order_cod_otp';
            $flag_mob = 'own';
            $where_mob = 'order_id = ? and otp = ?';
            $params_mob = array($order_id, $otp_password);
            $postArr_mob = array('table_name' => $table_mob, 'database_flag' => $flag_mob, 'where' => $where_mob, 'params' => $params_mob);
            $response_mob = CommonRepository::get_table_data($postArr_mob);
            if (!empty($response_mob) && isset($response_mob) && $response_mob->is_success == 'true') {
                echo json_encode(array('flag' => 1, 'msg' => 'Otp verified successfully.'));
                exit;
            } else {
                echo json_encode(array('flag' => 0, 'msg' => 'Otp verified failed.'));
                exit;
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong'));
            exit;
        }
    }

    public function otpVerifiedSuccess()
    {
        if ($_POST['order_id'] != '') {
            $site_url = base_url();
            $order_id = $_POST['order_id'];
            // $lang_code='';
            // if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
            //     $lang_code=$this->session->userdata('lcode');
            // }

            if ($order_id) {
                $data['order_id'] = $order_id;
                $site_logo = '';
                $data['webshop_details'] = CommonRepository::get_webshop_details();
                if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
                    $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                }
                $table = 'fbc_users_shop';
                $flag = 'main';
                $where = 'shop_id  = ?';
                $params = array($shop_id);
                $post_org_name_array = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                $data['webshop_name'] = CommonRepository::get_table_data($post_org_name_array);
                if (!empty($data['webshop_name']) && isset($data['webshop_name'])) {
                    if (isset($data['webshop_name']) && $data['webshop_name']->is_success == 'true') {
                        $webshopname = $data['webshop_name']->tableData[0]->org_shop_name;
                    }
                    $shop_logo = SITE_LOGO . '/' . $shop_logo;
                    $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
                            <img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
                        </a>';

                    $orderArr = array('order_id' => $order_id, 'currency_code' => CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);
                    $ResponseArr = CheckoutRepository::send_order_confirmation_email($shopcode, $shop_id, $orderArr);

                    $b2bArr = array('order_id' => $order_id, 'currency_code' => CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);
                    $b2bResponseArr = CheckoutRepository::generate_b2b_order_for_webshop($shopcode, $shop_id, $b2bArr);
                    if (!empty($b2bResponseArr) && isset($b2bResponseArr) && $b2bResponseArr->is_success == false) {
                    }

                    // removed otp after success
                    $post_org_name_array_otp = array('order_id' => $order_id);
                    CheckoutRepository::remove_otp($shopcode, $shop_id, $post_org_name_array_otp);
                }
            }
        }
    }

    public function otpRegenerateMethod()
    {
        if ($_POST['order_id'] != '' && $_POST['phone_no'] != '') {
            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;

            $order_id = $_POST['order_id'];
            $phone_no = $_POST['phone_no'];
            // removed otp after success
            $post_org_name_array_otp = array('order_id' => $order_id);
            $response_remove = CheckoutRepository::remove_otp($shopcode, $shop_id, $post_org_name_array_otp);
            if (!empty($response_remove) && isset($response_remove) && $response_remove) {
                // billing mobile number
                $table_mob = 'sales_order_address';
                $flag_mob = 'own';
                $where_mob = 'order_id = ? and address_type = ?';
                $params_mob = array($order_id, 1);
                $postArr_mob = array('table_name' => $table_mob, 'database_flag' => $flag_mob, 'where' => $where_mob, 'params' => $params_mob);
                $response_mob = CommonRepository::get_table_data($shopcode, $shop_id, $postArr_mob);
                $data['billingMobile'] = '';
                if (!empty($response_mob) && isset($response_mob)) {
                    $data['billingMobile'] = $response_mob->tableData[0]->mobile_no;
                }
                // new api send_cod_otp($order_id,$phone_no,)
                $codArr = array('order_id' => $order_id, 'mobile_no' => $data['billingMobile']);
                $response_cod_otp = CheckoutRepository::send_cod_otp($shopcode, $shop_id, $codArr);
                if (!empty($response_cod_otp) && isset($response_cod_otp) && $response_cod_otp->is_success == 'true') {
                    //otp verified
                    //$this->otpVerifiedSuccess($order_id);
                    echo json_encode(array('flag' => 1, 'msg' => 'Otp regenerate successfully.'));
                    exit;
                } else {
                    echo json_encode(array('flag' => 0, 'msg' => 'Otp regenerate failed.'));
                    exit;
                }
            }
        } else {
            echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong'));
            exit;
        }
    }

    /*stripe payment*/

    //placeorder payment method 6 call function
    private function create_order_stripe($data, $account_id, $key_secret, $url)
    {
        //default
        $stripePaymentMethod = '';

        if (isset($data)) {
            if ($data['encode_oid']) {
                $this->session->set_userdata('sKey', $data['encode_oid']);
            }
            if (isset($data['stripe_payment_method']) && !empty($data['stripe_payment_method'])) {
                $stripePaymentMethod = json_decode($data['stripe_payment_method'], true);
            }
        }
        //$account_id='acct_1JucaVRhcptcc6X3'; //tempary
        //$currency='eur';
        $currency = $data['currency'];
        $item_name = $data['stripe_line_item_heading']; //Zumbawear Order
        $locale = $data['locale'];

        $line_items = array('name' => $item_name, 'amount' => $data['amount'], 'currency' => $currency, 'quantity' => 1);
        $dataStripe = array('application_fee_amount' => $data['fbc_payment_amount'], 'line_items' => $line_items);

        \Stripe\Stripe::setApiKey($key_secret); //

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => [$stripePaymentMethod],
                // 'payment_method_types' => ['card','alipay','bancontact','eps','giropay','ideal','klarna','p24','sepa_debit','sofort'],
                'line_items' => [[$dataStripe['line_items']]],
                "metadata" => ["order_id" => $data['encode_oid']],
                'payment_intent_data' => mb_strpos($key_secret, 'test') === false ? [
                    'application_fee_amount' => $dataStripe['application_fee_amount'],
                ] : [],
                'mode' => 'payment',
                'locale' => $locale,
                'success_url' => $url['success_url'] . 'order/success/?sessionId={CHECKOUT_SESSION_ID}&keys=' . $data['encode_oid'],
                'cancel_url' => $url['cancel_url'] . '?key=' . $data['encode_oid'],
            ], ['stripe_account' => $account_id]);


            // Use Stripe's library to make requests...
        } catch (\Stripe\Exception\CardException $e) {
            $this->session->set_userdata('checkout_error', 'Something went wrong with stripe, please try again.1');
            redirect(base_url() . 'checkout/');
            /*$session=2;
          // Since it's a decline, \Stripe\Exception\CardException will be caught
          echo 'Status is:' . $e->getHttpStatus() . '\n';
          echo 'Type is:' . $e->getError()->type . '\n';
          echo 'Code is:' . $e->getError()->code . '\n';
          // param is '' in this case
          echo 'Param is:' . $e->getError()->param . '\n';
          echo 'Message is:' . $e->getError()->message . '\n';*/
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            $this->session->set_userdata('checkout_error', 'Something went wrong with stripe, please try again.2');
            redirect(base_url() . 'checkout/');
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            // print_r($e);exit();
            $this->session->set_userdata('checkout_error', 'Something went wrong with stripe, please try again.3');
            redirect(base_url() . 'checkout/');
        } catch (\Stripe\Exception\AuthenticationException $e) {
            $this->session->set_userdata('checkout_error', 'Something went wrong with stripe, please try again.4');
            redirect(base_url() . 'checkout/');
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $this->session->set_userdata('checkout_error', 'Something went wrong with stripe, please try again.5');
            redirect(base_url() . 'checkout/');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $session = 6;
            $this->session->set_userdata('checkout_error', 'Something went wrong with stripe, please try again.6');
            redirect(base_url() . 'checkout/');
        } catch (Exception $e) {
            $this->session->set_userdata('checkout_error', 'Something went wrong with stripe, please try again.7');
            redirect(base_url() . 'checkout/');
        }

        return $session;
    }

    // webhook triger after call function
    public function stripe_webhook($key, $stripe_session_id, $pay_response, $payment_status)
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $payment_intent = '';
        if ($pay_response) {
            $pay_response = json_encode($pay_response);

            $pay_response1 = json_decode($pay_response);
            $payment_intent = $pay_response1->payment_intent;
        }

        $site_url = base_url();
        if ($key != '') {
            $increment_id = base64_decode($key);
            $table2 = 'sales_order';
            $flag = 'own';
            $where = 'increment_id = ?';
            $params = array($increment_id);
            $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
            $response2 = CommonRepository::get_table_data($shopcode, $shop_id, $postArr2);
            if (!empty($response2) && isset($response2)) {
                $OrderInfo = $response2->tableData[0];

                $status = strtolower($payment_status) === 'paid' ? 'completed' : strtolower($payment_status);
                $orderPaymentArr = array('order_id' => $OrderInfo->order_id, 'status' => $status);
                $ResponseArr = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderPaymentArr);

                $lang_code = '';
                if (!empty($OrderInfo->language_code) && $OrderInfo->is_default_language == 0) {
                    $lang_code = $OrderInfo->language_code;
                }
                $orderArr = array('order_id' => $OrderInfo->order_id, 'transaction_id' => $stripe_session_id, 'pay_response' => $pay_response, 'payment_intent' => $payment_intent);
                $this->session->set_userdata('checkout_message', 'Order created successfully');
                $ResponseArr2 = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderArr);
                if ($OrderInfo->status == 7) {
                    $order_id = $OrderInfo->order_id;
                    $data['order_id'] = $OrderInfo->order_id;
                    $site_logo = '';
                    $data['webshop_details'] = CommonRepository::get_webshop_details($shopcode, $shop_id);
                    if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
                        $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                    }
                    $table = 'fbc_users_shop';
                    $flag = 'main';
                    $where = 'shop_id  = ?';
                    $params = array($shop_id);
                    $post_org_name_array = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
                    $data['webshop_name'] = CommonRepository::get_table_data($shopcode, $shop_id, $post_org_name_array);
                    $data['paymentMethodcod'] = '';
                    if (!empty($data['webshop_name']) && isset($data['webshop_name'])) {
                        // payment method
                        $table_cod = 'sales_order_payment';
                        $flag_cod = 'own';
                        $where_cod = 'order_id = ?';
                        $params_cod = array($order_id);
                        $postArr_cod = array('table_name' => $table_cod, 'database_flag' => $flag_cod, 'where' => $where_cod, 'params' => $params_cod);
                        $response_cod = CommonRepository::get_table_data($shopcode, $shop_id, $postArr_cod);
                        if (!empty($response_cod) && isset($response_cod) && isset($response_cod->tableData[0])) {
                            $data['paymentMethodcod'] = $response_cod->tableData[0]->payment_method;
                        }

                        $table_mob = 'sales_order_address';
                        $flag_mob = 'own';
                        $where_mob = 'order_id = ? and address_type = ?';
                        $params_mob = array($order_id, 1);
                        $postArr_mob = array('table_name' => $table_mob, 'database_flag' => $flag_mob, 'where' => $where_mob, 'params' => $params_mob);
                        $response_mob = CommonRepository::get_table_data($shopcode, $shop_id, $postArr_mob);
                        $data['billingMobile'] = '';
                        if (!empty($response_mob) && isset($response_mob)) {
                            $data['billingMobile'] = $response_mob->tableData[0]->mobile_no;
                        }
                        //payment method
                        $data['shop_flag'] = '';
                        if ($data['webshop_name']->tableData[0]) {
                            $data['shop_flag'] = $data['webshop_name']->tableData[0]->shop_flag;
                        }

                        if (isset($data['webshop_name']) && $data['webshop_name']->is_success == 'true') {
                            $webshopname = $data['webshop_name']->tableData[0]->org_shop_name;
                        }
                        $shop_logo = SITE_LOGO . '/' . $shop_logo;
                        $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
                                        <img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
                                    </a>';

                        $orderArr = array('order_id' => $order_id, 'currency_code' => CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);
                        $ResponseArr = CheckoutRepository::send_order_confirmation_email($shopcode, $shop_id, $orderArr);

                        $b2bArr = array('order_id' => $order_id, 'currency_code' => CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);
                        $b2bResponseArr = CheckoutRepository::generate_b2b_order_for_webshop($shopcode, $shop_id, $b2bArr);
                        if (!empty($b2bResponseArr) && isset($b2bResponseArr) && $b2bResponseArr->is_success == false) {
                        }
                    }
                } else {
                    redirect(base_url());
                }
            } else {
                redirect(base_url());
            }
        }
    }

    function stripe_webhook_request($key, $stripe_session_id, $pay_response, $payment_status)
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
        } else {
            $customer_id = '0';
        }

        $payment_intent = '';
        if ($pay_response) {
            $pay_response = json_encode($pay_response);

            $pay_response1 = json_decode($pay_response);
            $payment_intent = $pay_response1->payment_intent;
        }

        if ($key != '') {
            $increment_id = base64_decode($key);
            $table2 = 'sales_order';
            $flag = 'own';
            $where = 'increment_id = ?';
            $params = array($increment_id);
            $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
            $response2 = CommonRepository::get_table_data($shopcode, $shop_id, $postArr2);
            if (!empty($response2) && isset($response2)) {

                $OrderInfo = $response2->tableData[0];

                // insert histroy table
                $table3 = 'sales_order_payment';
                $flag3 = 'own';
                $where3 = 'order_id = ?';
                $params3 = array($OrderInfo->order_id);
                $postArr3 = array('table_name' => $table3, 'database_flag' => $flag3, 'where' => $where3, 'params' => $params3);
                $response3 = CommonRepository::get_table_data($shopcode, $shop_id, $postArr3);

                if (!empty($response3) && isset($response3) && $response3->statusCode == 200 && isset($response3->tableData[0]->stripe_success_page_flag) && $response3->tableData[0]->stripe_success_page_flag == 0) {
                    $salesPaymentData = $response3->tableData[0];
                    $old_payment_id = $salesPaymentData->payment_id;
                    $old_payment_method_id = $salesPaymentData->payment_method_id;
                    $old_payment_method = $salesPaymentData->payment_method;
                    $old_payment_method_name = $salesPaymentData->payment_method_name;
                    $old_payment_type = $salesPaymentData->payment_type;
                    $old_currency_code = $salesPaymentData->currency_code;
                    // insert old sales_order_payment data new sales_order_payment_history
                    $order_payment_array = array('order_id' => $OrderInfo->order_id, 'order_payment_id' => $old_payment_id, 'payment_method_id' => $old_payment_method_id, 'payment_method' => $old_payment_method, 'payment_method_name' => $old_payment_method_name, 'payment_type' => $old_payment_type);
                    $dataResponse = CheckoutRepository::insert_old_payment_method($shopcode, $shop_id, $order_payment_array);

                    // order payment table data updated
                    $update_payment_method_id = 6;
                    $update_payment_method = 'stripe_payment';
                    $update_payment_method_name = 'Stripe';
                    $update_payment_type = 2;
                    // $update_currency_code=6;
                    $orderArrUpdated = array('order_id' => $OrderInfo->order_id, 'payment_method_id' => $update_payment_method_id, 'payment_method' => $update_payment_method, 'payment_method_name' => $update_payment_method_name, 'payment_type' => $update_payment_type);
                    $ResponseArrUpdated = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderArrUpdated);
                }

                $status = strtolower($payment_status) === 'paid' ? 'completed' : strtolower($payment_status);
                $orderPaymentArr = array('order_id' => $OrderInfo->order_id, 'status' => $status);
                $ResponseArr = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderPaymentArr);

                $lang_code = '';
                if (!empty($OrderInfo->language_code) && $OrderInfo->is_default_language == 0) {
                    $lang_code = $OrderInfo->language_code;
                }
                $orderArr = array('order_id' => $OrderInfo->order_id, 'transaction_id' => $stripe_session_id, 'pay_response' => $pay_response, 'payment_intent' => $payment_intent);
                $this->session->set_userdata('checkout_message', 'Request repayment successfully');
                $ResponseArr2 = CheckoutRepository::update_order_payment_status_info($shopcode, $shop_id, $orderArr);
            } else {
                // redirect(base_url());
            }
        }
    }

    // webhook triger function
    public function checkoutSessionCompleted()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $payment_method = 6;

        //start main database
        $table_main = 'payment_master';
        $flag_main = 'main';
        $where_main = 'id  = ?';
        $params_main = array($payment_method);
        $post_main_array = array('table_name' => $table_main, 'database_flag' => $flag_main, 'where' => $where_main, 'params' => $params_main);
        $main_payment_master_data = CommonRepository::get_table_data($shopcode, $shop_id, $post_main_array);
        if (!empty($main_payment_master_data) && isset($main_payment_master_data) && $main_payment_master_data->is_success == true) {
            $keyMainData = json_decode($main_payment_master_data->tableData[0]->gateway_details);
            $key_secret = $keyMainData->key;
        } else {
            $key_secret = '';
        }

        //
        $table2 = 'webshop_payments';
        $flag = 'own';
        $where = 'payment_id = ?';
        $params = array($payment_method);
        $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
        $response2 = CommonRepository::get_table_data($shopcode, $shop_id, $postArr2);
        if (!empty($response2) && isset($response2) && $response2->is_success == true) {
            $Row = $response2->tableData[0];
            $gateway_details = json_decode($Row->gateway_details);
            $endpoint_secret = (isset($gateway_details->checkout_session_completed_webhook_key) && $gateway_details->checkout_session_completed_webhook_key != '') ? $gateway_details->checkout_session_completed_webhook_key : '';
        } else {
            $endpoint_secret = '';
        }


        \Stripe\Stripe::setApiKey($key_secret);
        //echo $endpoint_secret;
        // $endpoint_secret = 'whsec_fgS5f2kTpXDJaQdlOPkWgFytziTi4Vqn';//

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE']; //signature
        $event = null;

        try {
            /*$event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
            );*/
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        $evendata = $event->data->object;
        $stripe_session_id = $event->data->object->id;
        $order_id = $event->data->object->metadata->order_id;
        $order_type = $event->data->object->metadata->order_type ?? '';
        $payment_status = $event->data->object->payment_status;

        // file_put_contents('output.txt', var_export($event->data->object, TRUE));
        switch ($event->type) {
            case 'checkout.session.completed':
                // $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
                if ($order_type == 'request_order') {
                    $this->stripe_webhook_request($order_id, $stripe_session_id, $event->data->object, $payment_status);
                } else {
                    $this->stripe_webhook($order_id, $stripe_session_id, $event->data->object, $payment_status);
                }
                break;
                // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }
        http_response_code(200);
    }
    /*end stripe payment*/


    /*order issue*/

    public function liveOrder($increment_id)
    {
        // exit;
        $site_url = base_url();
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $table2 = 'sales_order';
        $flag = 'own';
        $where = 'increment_id = ?';
        $params = array($increment_id);
        $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
        $response2 = CommonRepository::get_table_data($postArr2);
        // print_r($response2);
        //         exit;

        if (!empty($response2) && isset($response2) && $response2->statusCode == 200) {
            $OrderInfo = $response2->tableData[0];

            if ($OrderInfo->status == 7) {

                $order_id = $OrderInfo->order_id;
                $data['order_id'] = $OrderInfo->order_id;
                $customer_id = $OrderInfo->customer_id;
                $shop_logo = SITE_LOGO;

                $data['webshop_details'] = CommonRepository::get_webshop_details();
                if (!empty($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
                    // $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                    $webshopname = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_name);
                }
                

                $fbc_shop_details = GlobalRepository::get_fbc_users_shop();
                $fbc_users_shop = $fbc_shop_details->result;

                $data['paymentMethodcod'] = '';
                if (!empty($fbc_users_shop)) {
                    // payment method
                    $response_cod = CommonRepository::get_table_data([
                        'table_name' => 'sales_order_payment',
                        'database_flag' => 'own',
                        'where' => 'order_id = ?',
                        'params' => [$data['order_id']]
                    ]);


                    if (!empty($response_cod) && isset($response_cod->tableData[0])) {
                        $data['paymentMethodcod'] = $response_cod->tableData[0]->payment_method;
                    }
                    //payment method
                    // billing mobile number
                    $response_mob = CommonRepository::get_table_data([
                        'table_name' => 'sales_order_address',
                        'database_flag' => 'own',
                        'where' => 'order_id = ? and address_type = ?',
                        'params' => [$data['order_id'], 1]
                    ]);


                    if (!empty($response_mob) && isset($response_mob)) {
                        $data['billingMobile'] = $response_mob->tableData[0]->mobile_no;
                    } else {
                        $data['billingMobile'] = '';
                    }
                    //payment method
                    $data['shop_flag'] = $fbc_users_shop->shop_flag ?? 0;


                    if ($data['shop_flag'] == 2 && $data['paymentMethodcod'] === 'cod') {
                        $codArr = array('order_id' => $data['order_id'], 'mobile_no' => $data['billingMobile']);
                        if ($this->session->userdata('checkout_message')) {
                            $response_cod_otp = CheckoutRepository::send_cod_otp($codArr);
                        }
                    } else {


                        $shop_logo = SITE_LOGO;
                        $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
                                        <img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
                                    </a>';

                        $orderArr = array('order_id' => $order_id, 'currency_code' => $CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);


                        CheckoutRepository::send_order_confirmation_email($orderArr);

                        $b2bArr = array('order_id' => $order_id, 'currency_code' => $CURRENCY_CODE, 'site_url' => $site_url, 'site_logo' => $site_logo, 'lang_code' => $lang_code);
                       
                        CheckoutRepository::generate_b2b_order_for_webshop($b2bArr);

                    }
                }

            } else {
                //redirect(base_url());
            }
        } else {
            $order_id = '';
        }
    }

    /*end order issue*/

    public function CheckoutSetVatSession()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $current_vatpercent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
            $session_id = $this->session->userdata('LoginToken');
        } else {
            $customer_id = '0';
            $session_id = $this->session->userdata('sis_session_id');
        }

        $ApiUrl = '/webshop/get_users_shop_details';
        $post_data = array('shop_id' => $shop_id);
        $response_shop = $this->restapi->post_method($ApiUrl, $post_data);

        if (isset($response_shop) && $response_shop->is_success == 'true') {
            $FbcShopDetails = $response_shop->FbcWebShopDetails;


            if ($FbcShopDetails->vat_flag == 1) {
                $this->session->set_userdata('session_vat_flag', $FbcShopDetails->vat_flag);

                $post_data = array(
                    'shopcode' => $shopcode,
                    'shop_id' => $shop_id,
                    'country_code' => $_POST['shipping_country']
                );

                $ApiUrl = '/webshop/get_shop_vat_data';
                $vat_data = $this->restapi->post_method($ApiUrl, $post_data);

                if (!empty($vat_data->VatDetails) && $vat_data->is_success == 'true') {
                    $vat_no_session = (($this->session->userdata('vat_no_session')) ? $this->session->userdata('vat_no_session') : '');

                    $VatDetails = $vat_data->VatDetails;

                    if (isset($vat_no_session) && $vat_no_session != '' && $VatDetails->deduct_vat == 1) {
                        $update_percentage = 0;
                    } else {
                        $update_percentage = $VatDetails->vat_percentage;
                    }

                    $sessionArr = array('vat_id' => $VatDetails->id, 'ip_country' => $_POST['shipping_country'], 'vat_country' => $VatDetails->country_code, 'vat_percent' => $update_percentage, 'is_eu_country' => $VatDetails->is_eu_country, 'deduct_vat' => $VatDetails->deduct_vat);
                    $this->session->set_userdata($sessionArr);

                    //Update Quote
                    //if($current_vatpercent_session != $VatDetails->vat_percentage){

                    $post_data = array(
                        'shopcode' => $shopcode,
                        'shop_id' => $shop_id,
                        'quote_id' => $this->session->userdata('QuoteId'),
                        'customer_id' => $customer_id,
                        'session_id' => $session_id,
                        'customer_type_id' => $this->session->userdata('CustomerTypeID'),
                        'vat_percent' => $update_percentage
                    );

                    $ApiUrl = '/webshop/update_quote_as_vat_change';
                    $vat_data = $this->restapi->post_method($ApiUrl, $post_data);

                    $response = (new Checkout())->EUShippingResponse($shopcode, $shop_id, $this->session->userdata('QuoteId'), $update_percentage);

                    echo json_encode(array('flag' => 0, 'msg' => 'Success', 'percent' => $update_percentage, 'eu_shipping_response' => $response['eu_shipping_response'], 'shipping_response_flag' => $response['shipping_response_flag']));
                    exit;
                } else {
                    $post_data2 = array(
                        'shopcode' => $shopcode,
                        'shop_id' => $shop_id,
                        'country_code' => $FbcShopDetails->country_code
                    );

                    $ApiUrl = '/webshop/get_shop_vat_data';
                    $vat_data2 = $this->restapi->post_method($ApiUrl, $post_data2);

                    if (!empty($vat_data2->VatDetails) && $vat_data2->is_success == 'true') {
                        $vat_no_session = (($this->session->userdata('vat_no_session')) ? $this->session->userdata('vat_no_session') : '');

                        $VatDetails = $vat_data2->VatDetails;

                        if (isset($vat_no_session) && $vat_no_session != '' && $VatDetails->deduct_vat == 1) {
                            $update_percentage2 = 0;
                        } else {
                            $update_percentage2 = $VatDetails->vat_percentage;
                        }

                        $sessionArr = array('vat_id' => $VatDetails->id, 'ip_country' => $_POST['shipping_country'], 'vat_country' => $VatDetails->country_code, 'vat_percent' => $update_percentage2, 'is_eu_country' => $VatDetails->is_eu_country, 'deduct_vat' => $VatDetails->deduct_vat);
                        $this->session->set_userdata($sessionArr);

                        //Update Quote
                        //if($current_vatpercent_session != $VatDetails->vat_percentage){

                        $post_data = array(
                            'shopcode' => $shopcode,
                            'shop_id' => $shop_id,
                            'quote_id' => $this->session->userdata('QuoteId'),
                            'customer_id' => $customer_id,
                            'session_id' => $session_id,
                            'customer_type_id' => $this->session->userdata('CustomerTypeID'),
                            'vat_percent' => $update_percentage2
                        );

                        $ApiUrl = '/webshop/update_quote_as_vat_change';
                        $vat_data = $this->restapi->post_method($ApiUrl, $post_data);

                        $response = (new Checkout())->EUShippingResponse($shopcode, $shop_id, $this->session->userdata('QuoteId'), $update_percentage2);

                        echo json_encode(array('flag' => 0, 'msg' => 'Success', 'percent' => $update_percentage2, 'eu_shipping_response' => $response['eu_shipping_response'], 'shipping_response_flag' => $response['shipping_response_flag']));
                        exit;
                    } else {
                        $sessionArr = array('vat_id' => '', 'ip_country' => $_POST['shipping_country'], 'vat_country' => '', 'vat_percent' => 0, 'is_eu_country' => '', 'deduct_vat' => '');
                        $this->session->set_userdata($sessionArr);

                        //Update Quote
                        if ($current_vatpercent_session != 0) {
                            $post_data = array(
                                'shopcode' => $shopcode,
                                'shop_id' => $shop_id,
                                'quote_id' => $this->session->userdata('QuoteId'),
                                'customer_id' => $customer_id,
                                'session_id' => $session_id,
                                'customer_type_id' => $this->session->userdata('CustomerTypeID'),
                                'vat_percent' => 0
                            );

                            $ApiUrl = '/webshop/update_quote_as_vat_change';
                            $vat_data = $this->restapi->post_method($ApiUrl, $post_data);

                            $response = (new Checkout())->EUShippingResponse($shopcode, $shop_id, $this->session->userdata('QuoteId'), 0);

                            echo json_encode(array('flag' => 0, 'msg' => 'Success', 'percent' => 0, 'eu_shipping_response' => $response['eu_shipping_response'], 'shipping_response_flag' => $response['shipping_response_flag']));
                            exit;
                        } else {
                            echo json_encode(array('flag' => 1, 'msg' => 'Current Precent and Previous Percent Same'));
                            exit;
                        }
                    }
                }
            } else {
                $this->session->set_userdata('session_vat_flag', $FbcShopDetails->vat_flag);

                echo json_encode(array('flag' => 1, 'msg' => 'Fail', 'percent' => 'No vat setting Enabled'));
                exit;
            }
        }
    }


    public function CheckoutListing()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
            $session_id = $this->session->userdata('LoginToken');
        } else {
            $customer_id = '0';
            $session_id = $this->session->userdata('sis_session_id');
        }

        $cartApiUrl = '/webshop/cart_listing'; //Cart Listing
        $cartArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'session_id' => $session_id, 'quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $customer_id);

        $cart = $this->restapi->post_method($cartApiUrl, $cartArr);

        if (isset($cart) && ($cart->is_success == 'true')) {
            $CartData = $cart->cartData;
            $cartItems = $CartData->cartItems;
        } else {
            $cartItems = '';
        }

        $data['cartItems'] = $cartItems;

        $View = $this->load->view('checkout/checkout_order_item_list', $data, true);
        $this->output->set_output($View);
    }

    public function checkvatAlreadyExits()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $vat_no = $_POST['vat_no'];

        $identity = 'vies_checker_time_in_hr';

        $GetVieshrsApiUrl = '/webshop/get_custom_variable/' . $shopcode . '/' . $shop_id . '/' . $identity; //Global API -
        $showHrs = $this->restapi->get_method($GetVieshrsApiUrl);

        if ($showHrs->statusCode == '200') {
            $RowCV = $showHrs->custom_variable;
            $hrs = $RowCV->value;
        } else {
            $hrs = 0;
        }

        $hrs_dynamic_val = '-' . $hrs . ' hour';

        $hour_ago = strtotime($hrs_dynamic_val);

        //echo $hour_ago;exit;


        $table_shop = 'vat_log';
        $flag_shop = 'own';
        $where_shop = 'vat_no = ? and created_at >= ?';
        $order_by = 'ORDER BY id DESC LIMIT 1';

        $params_shop = array($vat_no, $hour_ago);
        $post_org_name_array_shop = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'table_name' => $table_shop, 'database_flag' => $flag_shop, 'where' => $where_shop, 'order_by' => $order_by, 'params' => $params_shop);
        $webshop_name_url_shop = '/webshop/get_table_data'; //get_table_data
        $vat_details = $this->restapi->post_method($webshop_name_url_shop, $post_org_name_array_shop);

        if ($vat_details->is_success == true && !empty($vat_details->tableData)) {
            echo json_encode(array('flag' => 0, 'msg' => '', 'result' => $vat_details->tableData[0]));
        } else {
            echo json_encode(array('flag' => 1, 'msg' => 'No data found'));
        }
    }

    public function addVatLogging()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
        } else {
            $customer_id = '0';
        }


        $post_data = array(
            'shopcode' => $shopcode,
            'shop_id' => $shop_id,
            'type' => $_POST['type'],
            'customer_id' => $customer_id,
            'customer_address_id' => $_POST['address_id'],
            'quote_id' => (isset($_POST['quote_id']) ? $_POST['quote_id'] : ''),
            'order_id' => (isset($_POST['order_id']) ? $_POST['order_id'] : ''),
            'vat_no' => $_POST['request'],
            'consulation_no' => (isset($_POST['response']['Identifier']) ? $_POST['response']['Identifier'] : ''),
            'company_name' => (isset($_POST['response']['Company_name']) ? $_POST['response']['Company_name'] : ''),
            'company_address' => (isset($_POST['response']['Company_address']) ? $_POST['response']['Company_address'] : ''),
            'request_data' => $_POST['request'],
            'response_data' => $_POST['response'],
            'response_type' => $_POST['response_type'],
            'ip' => $_SERVER["REMOTE_ADDR"],

        );

        $ApiUrl = '/webshop/add_vat_log';
        $vat_data = $this->restapi->post_method($ApiUrl, $post_data);

        echo json_encode(array('response' => $vat_data));
        exit;
    }


    public function openAddressPopup()
    {
        $LoginID    =   $_SESSION['LoginID'];
        //print_r($_POST);exit;
        if (isset($_POST)) {
            $data['LoginID'] = $_SESSION['LoginID'];
            $data['flag'] = $_POST['flag'];
            $data['customer_id'] = $_POST['customer_id'];
            $data['address_id'] = $_POST['address_id'];

            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;

            if ($_POST['flag'] == 'edit') {
                $apiUrl = '/webshop/get_table_data'; //get_table_data
                $table = 'customers_address';
                $flag = 'own';
                $where = 'id = ? AND customer_id = ?';
                $order_by = 'ORDER BY id DESC';
                $params = array($_POST['address_id'], $LoginID);
                $postArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
                $response = $this->restapi->post_method($apiUrl, $postArr);
                // echo '<pre>';print_r($response);exit;
                if ($response->is_success == 'true') {
                    $data['addressData'] = $response->tableData;
                }
            }

            //resume here
            $View = $this->load->view('checkout/address_popup_vat', $data, true);
            $this->output->set_output($View);
        }
    }

    public function addEditAddress()
    {
        $LoginID = $_SESSION['LoginID'];
        $flag = $_POST['flag'];
        $address_id = $_POST['address_id'];

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;


        $company_name = $_POST['company_name'];
        $vat_no = $_POST['vat_no'];
        $consulation_no = $_POST['consulation_no'];
        $res_company_name = $_POST['res_company_name'];
        $res_company_address = $_POST['res_company_address'];

        $apiUrl = '/webshop/update_vatdetails_checkout'; //customer_address_add_edit
        $postArr = array(
            'shopcode' => $shopcode,
            'shopid' => $shop_id,
            'customer_id' => $LoginID,
            'customer_address_id' => $address_id,
            'company_name' => $company_name,
            'vat_no' => $vat_no,
            'consulation_no' => $consulation_no,
            'res_company_name' => $res_company_name,
            'res_company_address' => $res_company_address,
        );
        $response = $this->restapi->post_method($apiUrl, $postArr);
        //echo '<pre>';print_r($response);//exit;
        $message = $response->message;
        if ($response->is_success == 'true') {
            echo json_encode(array('flag' => 1, 'msg' => $message));
            exit;
        } else {
            echo json_encode(array('flag' => 0, 'msg' => $message));
            exit;
        }
    }



    ///////////////////////////////////////////
    public function test_checkout()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $quote_id = '';
        $session_id = '';
        $customer_id = 0;

        /*start shop_flag*/
        //shop flag
        $webshop_name_shop = GlobalRepository::get_fbc_users_shop();
        $data['shop_flag_shop'] = $webshop_name_shop->result->shop_flag ?? '';
        $data['acc_inv_flag'] = $webshop_name_shop->result->acc_inv_flag ?? '';
        /*end shop_flag*/

        if ($this->session->userdata('QuoteId')) {
            $quote_id = $this->session->userdata('QuoteId');
        } else {
            redirect('/');
        }

        if ($this->session->userdata('sis_session_id')) {
            $session_id = $this->session->userdata('sis_session_id');
        }

        if ($this->session->userdata('LoginID')) {
            $customer_id = $this->session->userdata('LoginID');
        }

        $lang_code = '';
        if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
            $lang_code = $this->session->userdata('lcode');
        }

        $cartArr = array('session_id' => $session_id, 'quote_id' => $quote_id, 'customer_id' => $customer_id, 'lang_code' => $lang_code);
        $cart = CartRepository::cart_listing_check_cod($shopcode, $shop_id, $cartArr);
        if (!empty($cart) && isset($cart) && ($cart->is_success == 'true')) {
            $data['CartData'] = $cart->cartData;
        } else {
            redirect('/');
            $data['CartData'] = array();
        }

        $data['QuoteId'] = $quote_id;


        $table2 = 'country_master';
        $flag = 'own';
        $postArr2 = array('table_name' => $table2, 'database_flag' => $flag);
        $response2 = CommonRepository::get_table_data($postArr2, 3600);
        if (!empty($response2) && isset($response2) && $response2->statusCode == 200) {
            $data['countryList'] = $response2->tableData;
        } else {
            $data['countryList'] = array();
        }

        $table = 'country_state_master_in';
        $flag = 'main';
        $postArr3 = array('table_name' => $table, 'database_flag' => $flag);
        $response3 = CommonRepository::get_table_data($postArr3, 3600);
        if (!empty($response3) && isset($response3) && $response3->is_success == 'true') {
            $data['stateList'] = $response3->tableData;
        }

        /*--------------------Customer address-------------------*/
        if ($customer_id) {
            $table = 'customers_address';
            $flag = 'own';
            $where = 'customer_id = ? AND remove_flag = ?';
            $order_by = 'ORDER BY id DESC';
            $params = array($customer_id, 0);
            $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);
            $response = CommonRepository::get_table_data($postArr);
            if (!empty($response) && isset($response) && $response->is_success == 'true') {
                $data['addressList'] = $response->tableData;
            } else {
                $data['addressList'] = array();
            }
        } else {
            $data['addressList'] = array();
        }

        $payArr = array('country_code' => COUNTRY_CODE);
        $MethodResult = CheckoutRepository::payment_methods_listing($payArr);
        if (!empty($MethodResult) && isset($MethodResult) && ($MethodResult->is_success == 'true')) {
            $data['PaymentMethods'] = $MethodResult->PaymentMethods;
        } else {
            $data['PaymentMethods'] = array();
        }

        $ShipToCountry = GlobalRepository::get_custom_variable('shipping_country');
        if (!empty($ShipToCountry) && isset($ShipToCountry) && ($ShipToCountry->is_success == 'true')) {
            $data['ShipToCountry'] = $ShipToCountry->custom_variable;
        } else {
            $data['ShipToCountry'] = array();
        }

        $data['restricted_access'] = GlobalRepository::get_custom_variable('restricted_access', true) ?? 'no';

        $data['hidden_mobile_no'] = '';
        $data['hidden_email_id'] = '';
        // razor pay
        if ((isset($_GET['order_id']) && $_GET['order_id'] > 0)) {

            // billing mobile number
            $table_mob = 'sales_order_address';
            $flag_mob = 'own';
            $where_mob = 'order_id = ? and address_type = ?';
            $params_mob = array($_GET['order_id'], 1);
            $postArr_mob = array('table_name' => $table_mob, 'database_flag' => $flag_mob, 'where' => $where_mob, 'params' => $params_mob);
            $response_mob = CommonRepository::get_table_data($shopcode, $shop_id, $postArr_mob);
            if (!empty($response_mob) && isset($response_mob) && isset($response_mob->tableData[0])) {
                $data['hidden_mobile_no'] = $response_mob->tableData[0]->mobile_no;
            }
            // email id
            $table_email = 'sales_order';
            $flag_email = 'own';
            $where_email = 'order_id = ?';
            $params_email = array($_GET['order_id']);
            $postArr_email = array('table_name' => $table_email, 'database_flag' => $flag_email, 'where' => $where_email, 'params' => $params_email);
            $response_email = CommonRepository::get_table_data($shopcode, $shop_id, $postArr_email);
            if (!empty($response_email) && isset($response_email) && $response_email->statusCode == 200) {
                $data['hidden_email_id'] = $response_email->tableData[0]->customer_email;
            }
        }
        // end razor pay

        $data['request_for_invoice_access_data'] = GlobalRepository::get_custom_variable('request_for_invoice_default_webcust', true) ?? '';

        //new added
        if (isset($quote_id) && $quote_id > 0) {
            /*quote data*/
            $quoteTable = 'sales_quote';
            $quoteFlag = 'own';
            $quoteWhere = 'quote_id = ? ';
            $quoteParams = array($quote_id);
            $quotePostArr = array('table_name' => $quoteTable, 'database_flag' => $quoteFlag, 'where' => $quoteWhere, 'order_by' => '', 'params' => $quoteParams);
            $quoteResponse = CommonRepository::get_table_data($quotePostArr);
            if (!empty($quoteResponse) && isset($quoteResponse) && $quoteResponse->is_success == 'true') {
                $data['quoteData'] = $quoteResponse->tableData[0];
                //print_r($data['quoteData']);
                $quoteData = $quoteResponse->tableData[0];
                if (isset($quoteData->customer_email) && !empty($quoteData->customer_email) && $quoteData->customer_email != '') {
                    $emailQuot = explode('@', $quoteData->customer_email);
                    if (isset($emailQuot)) {
                        $data['emailQuotData'] = $emailQuot[1];
                    }
                } else {
                    $data['emailQuotData'] = '';
                }
            } else {
                $data['quoteData'] = array();
                $data['emailQuotData'] = '';
            }
            /*end quote data*/

            // Quote address data
            $data['quote_address_data'] = array();
            $quoteTable = 'sales_quote_address';
            $quoteFlag = 'own';
            $quoteWhere = 'quote_id = ? ';
            $quoteParams = array($quote_id);
            $quotePostArr = array('table_name' => $quoteTable, 'database_flag' => $quoteFlag, 'where' => $quoteWhere, 'order_by' => '', 'params' => $quoteParams);
            $quoteResponse = CommonRepository::get_table_data($quotePostArr);
            if (!empty($quoteResponse) && isset($quoteResponse) && $quoteResponse->is_success == 'true') {
                foreach ($quoteResponse->tableData as $key => $value) {
                    $data['quote_address_data'][] = $value;
                }
            }

            // Quote payment data
            $data['quote_payment_data'] = array();
            $quoteTable = 'sales_quote_payment';
            $quoteFlag = 'own';
            $quoteWhere = 'quote_id = ? ';
            $quoteParams = array($quote_id);
            $quoteSelect = ' payment_method_id,payment_method ';
            $quotePostArr = array('table_name' => $quoteTable, 'database_flag' => $quoteFlag, 'where' => $quoteWhere, 'order_by' => '', 'params' => $quoteParams, 'select' => $quoteSelect);
            $quoteResponse = CommonRepository::get_table_data($quotePostArr);
            if (!empty($quoteResponse) && isset($quoteResponse) && $quoteResponse->is_success == 'true') {
                foreach ($quoteResponse->tableData as $key => $value) {
                    $data['quote_payment_data'][] = $value;
                }
            }
        } else {
            $data['quoteData'] = array();
        }
        //end new added

        // check terms and condition
        $identifier_tnc = 'order_check_termsconditions';
        $ApiResponse_tnc = GlobalRepository::get_custom_variable($identifier_tnc);

        if ($ApiResponse_tnc->statusCode == '200') {
            $RowCV = $ApiResponse_tnc->custom_variable;
            $data['order_check_termsconditions'] = $RowCV->value;
        } else {
            $data['order_check_termsconditions'] = 'no';
        }

        $this->template->load('checkout/shop_checkout', $data);
    }

    
    public function abandoned_carts_checkout()
    {
        $data['PageTitle'] = 'Cart';
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $session_id = '';
        $customer_id = '';
        $lang_code = '';
        // echo "<pre>";
        // print_r($_SESSION);
        // die;
        $quote_id_new = isset($_GET['key']) ? urldecode($_GET['key']) : '';
        $quote_id_decode = base64_decode($quote_id_new);

        $this->session->set_userdata(['QuoteId' => $quote_id_decode]);
        $quote_id = $this->session->userdata('QuoteId');

        $OrderData = CheckoutRepository::abundantCartDetails($quote_id);
        // echo "<pre>";
        // print_r($OrderData);
        // die;
        $customer_id_session = $OrderData->customer_id;
        
        
        $this->session->set_userdata(['LoginID' => $customer_id_session]);
        $customer_id = $this->session->userdata('LoginID');
        
        $session_id_session = $OrderData->session_id;
        $this->session->set_userdata(['sis_session_id' => $session_id_session]);
        $session_id = $this->session->userdata('sis_session_id');

        $lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';

        $_POST['lang_code'] = 'en';

        $lang_code_session = isset($_POST['lang_code']) ? $_POST['lang_code'] : '';
        $this->session->set_userdata(['lcode' => $lang_code_session]);

        $lang_code = $this->session->userdata('lcode');
        // echo "<pre>";
        // print_r($_SESSION);
        // die;

        // echo "quote_id_new: $quote_id <br>";
        // echo "session_id: $session_id <br>";
        // echo "customer_id: $customer_id <br>";
        // echo "lang_code: $lang_code <br>";

        // die;

        $data['QuoteId'] = $quote_id;
        // print_r($data['QuoteId']);
        // die;
        $data['lcode'] = $lang_code;
        // print_r($data['lcode']);
        // die;

        $data['LoginID'] = $customer_id;
        // print_r($data['LoginID']);
        // die;
        $data['sis_session_id'] = $session_id;
        // print_r($session_id);
        // die;
        // echo "<pre>";
        // print_r($_SESSION);
        // die;

        $this->template->load('cart/cart', $data);
    }

    
}
