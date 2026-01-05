<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CustomerController extends CI_Controller
{
    function  __construct()
    {
        parent::__construct();
        $this->load->library('paypal_lib');
        $this->load->library('email');
        $this->load->model('CommonModel');

        $this->load->helper(['url', 'form', 'language']);

        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }
    }
    public function open_feedback_popup()
    {
        $data['LoginID'] = $LoginID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
        if (empty($_POST)) {
            $View = $this->load->view('feedback/feedback-popup', $data, true);
            $this->output->set_output($View);
        } else {
            if (isset($_POST)) {
                if (empty($_POST['email'])) {
                    echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                    exit;
                } else {
                    $data['LoginID'] = $LoginID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
                    $postArr = array(
                        'customer_id' => $LoginID,
                        'name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'where_here_abou_us' => $_POST['where_here_abou_us'],
                        'details' => $_POST['details']
                    );

                    $FeedbackResponse = CustomerRepository::customer_feedback($postArr);
                    $message = $FeedbackResponse->message;
                    if (!empty($FeedbackResponse) && (isset($FeedbackResponse) && $FeedbackResponse->is_success == 'true')) {
                        echo json_encode(array('flag' => 1, 'msg' => $message));
                        exit;
                    } else {
                        echo json_encode(array('flag' => 1, 'msg' => 'Something went wrong'));
                        exit;
                    }
                }
            }
        }
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
            } /*else {

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
            }*/
        }
    }

    public function send_review_emails() {
        $two_days_ago = date('Y-m-d', strtotime('-2 days'));
        $orders = $this->CommonModel->get_orders_by_date($two_days_ago);
        // echo "";print_r($orders);die;

        if(empty($orders)) return; // no orders to process

        // foreach ($orders as $order) {
        // }
        $review_link = base_url('review/form/'.$orders->order_id);
        $new_review_link = "<p><a href=\"{$review_link}\">(Click here) </a>to review your products</p>";
        $customer_name = $orders->customer_firstname . ' ' . $orders->customer_lastname;

        $webshop_name = "Yellow Market";

        $TempVars = array("##CUSTOMERNAME##", "##REVIEWLINK##", "##WEBSHOPNAME##");
        $DynamicVars = array($customer_name, $new_review_link, $webshop_name);

        $recipientEmail = "snehals@bcod.co.in";

        // $recipientEmail = $order->customer_email; // send to customer in live
        $templateId = 'customer-review-feedback'; // template ID

        $mailSent = $this->CommonModel->sendCommonHTMLEmail($recipientEmail, $templateId, $TempVars, $DynamicVars);

        if(!$mailSent){
            log_message('error', "Review email failed for Order ID: {$orders->order_id}, Email: {$recipientEmail}");
        }
    }


    public function review_form($order_id)
    {

        // Customer is logged in → proceed with your original code
        // $data = $this->CommonModel->get_b2b_orders($order_id);
        // $sale_orders_data = $this->CommonModel->get_sale_orders($data->webshop_order_id);
        // $customer_data = $this->CommonModel->get_customer_data($sale_orders_data->customer_email);

        // if ($customer_data) {
        //     // ✅ Set session for logged-in user
        //     $this->session->set_userdata([
        //         'LoginID'   => $customer_data->id,
        //     ]);
        //     echo "<pre>";
        //     print_r($_SESSION);die;
        //     $redirect = $this->session->flashdata('login_redirect');

        //     if ($redirect) {
        //         redirect(BASE_URL . 'customer/login'); // corrected BASE_URL
        //         return; 
        //     } else {
        //     }
        // }
        $data['order_id'] = $order_id;
        $data['items'] = $this->CommonModel->get_order_with_items($order_id);

        // Load the review form view
        $this->load->view('review_form', $data);
    }



    public function review_save($order_id) {
        $ratings = $this->input->post('ratings');
        $comments = $this->input->post('comments');
        $data = $this->CommonModel->get_b2b_orders($order_id);
        $sale_orders_data = $this->CommonModel->get_sale_orders($data->webshop_order_id);
        $customer_data = $this->CommonModel->get_customer_data($sale_orders_data->customer_email);


        // echo "<pre>";print_r($data);die;

        if(!empty($ratings)) {
            $this->db->insert('product_reviews', [
                'order_id'   => $order_id,
                'B2B_order_id'   => $data->order_barcode,
                'customer_id'  => $customer_data->id,
                'merchant_id'  => $data->publisher_id,
                'rating'     => $ratings,
                'comments'   => !empty($comments) ? $comments : null,
                'created_at' => date(format: 'Y-m-d H:i:s')
            ]);

            // After inserting all reviews, mark order as reviewed
            $this->db->where('order_id', $order_id);
            $this->db->update('b2b_orders', ['reveiw_sent' => 1]);

            if($this->db->affected_rows() > 0){
                redirect(base_url('thank-you'));
            } else {
                echo "No rows updated!";
            }

        }

        $this->session->set_flashdata('success', 'Thanks for your feedback!');
        redirect(base_url('thank-you'));
    }
    public function merchantLoginOtpEmail()
    {
        if (empty($_POST['inputEmail'])) {
            echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
            exit;
        } else {
            $inputEmail = $_POST['inputEmail'];
            if (filter_var($inputEmail, FILTER_VALIDATE_EMAIL)) {
                $table1 = 'publisher';
                $flag1 = 'own';
                $where1 = 'email = ?';
                $params1 = array($inputEmail);
                $postArr1 = array('table_name' => $table1, 'database_flag' => $flag1, 'where' => $where1, 'params' => $params1);
                $response1 = CommonRepository::get_table_data($postArr1);
                if (!empty($response1) && isset($response1) && $response1->statusCode == 200) {
                    echo json_encode(array('flag' => 1, 'msg' => "Email already exist!"));
                    exit;
                } else {
                    echo json_encode(array('flag' => 3, 'msg' => "We cannot find an account with that Email"));
                    exit;
                }
            } /*else {

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
            }*/
        }
    }


    public function customer_signup_otp()
    {

        if (empty($_POST['mobile_no'])) {
            echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
            exit;
        } else {
            $mobile_no = $_POST['mobile_no'];
            $email = $_POST['email'];
            $table2 = 'customers';
            $flag = 'own';
            $where = 'mobile_no = ?';
            $params = array($mobile_no);
            $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);
            $response2 = CommonRepository::get_table_data($postArr2);
            if (isset($response2) && !empty($response2) && $response2->statusCode == 200) {
                echo json_encode(array('flag' => 0, 'msg' => "Mobile number already exist!"));
                exit;
            }
            if (isset($_POST['email']) && !empty($_POST['email'])) {
                $flag1 = 'own';
                $where1 = 'email_id = ?';
                $params1 = array($email);
                $postArr1 = array('table_name' => $table2, 'database_flag' => $flag1, 'where' => $where1, 'params' => $params1);
                $response1 = CommonRepository::get_table_data($postArr1);
                if (!empty($response1) && isset($response1) && $response1->statusCode == 200) {
                    echo json_encode(array('flag' => 0, 'msg' => "Email already exist!"));
                    exit;
                }
            }
            $otp_random_generate = mt_rand(1000, 9999);
            $otp_post = array('mobile_no' => $mobile_no, 'otp' => $otp_random_generate);
            $customer_signup_otp = LoginRepository::customer_signup_otp($otp_post);
            if (isset($customer_signup_otp) && !empty($customer_signup_otp)) {
                $postArr = array('mobile_no' => $mobile_no);
                $get_customer_signup_data = CustomerRepository::get_customer_signup_otp($postArr);
                echo json_encode(array('flag' => 1, 'msg' => $customer_signup_otp->message, 'data' => $get_customer_signup_data->customer_signup_otp_data->otp));
                exit;
            }
        }
    }

    public function register()
    {
        // echo "hi";die;
        if (isset($_SESSION['LoginID']) && $_SESSION['LoginID'] != '') {
            redirect(BASE_URL('customer/account'));
        }
        $data['PageTitle'] = 'Register';
        if (empty($_POST)) {
            $this->template->load('register', $data);
        } else {
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
                    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . RECAPTCHA_SECRETE_KEY_V2 . '&response=' . $_POST['g_recaptcha_response']);

                    // Decode json data 
                    $responseData = json_decode($verifyResponse);

                    if (!$responseData->success) {
                        echo json_encode(array('flag' => 0, 'msg' => "Please check on the reCAPTCHA box."));
                        exit;
                    }
                    if (empty($_POST['mobile_no'])) {
                        echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                        exit;
                    } else {
                        $mobile_no = $_POST['mobile_no'];
                        $email = $_POST['email'];
                        $table2 = 'customers';
                        $flag = 'own';
                        $where = 'mobile_no = ?';
                        $params = array($mobile_no);
                        $postArr2 = array('table_name' => $table2, 'database_flag' => $flag, 'where' => $where, 'params' => $params);

                        $response2 = CommonRepository::get_table_data($postArr2);
                        if (isset($response2) && !empty($response2) && $response2->statusCode == 200) {
                            echo json_encode(array('flag' => 0, 'msg' => "Mobile number already exist!"));
                            exit;
                        }

                        if (isset($_POST['email']) && !empty($_POST['email'])) {
                            $flag1 = 'own';
                            $where1 = 'email_id = ?';
                            $params1 = array($email);
                            $postArr1 = array('table_name' => $table2, 'database_flag' => $flag1, 'where' => $where1, 'params' => $params1);
                            $response1 = CommonRepository::get_table_data($postArr1);
                            if (!empty($response1) && isset($response1) && $response1->statusCode == 200) {
                                echo json_encode(array('flag' => 0, 'msg' => "Email already exist!"));
                                exit;
                            }
                        }
                    }

                    $first_name = $_POST['first_name'];
                    $last_name = $_POST['last_name'];
                    $email = (isset($_POST['email'])) ? $_POST['email'] : '';
                    $password = $_POST['password'];
                    $conf_password = $_POST['conf_password'];
                    //$phone_prefix = $_POST['phone_prefix'];
                    $phone_prefix = (isset($_POST['phone_prefix'])) ? $_POST['phone_prefix'] : 91;
                    $mobile_no = $_POST['mobile_no'];
                    // $country_code = $this->ip_visitor_country();
                    $country_code = 'IN';

                    $postArr = array('mobile_no' => $mobile_no);
                    // $get_customer_signup_data = CustomerRepository::get_customer_signup_otp($postArr);
                    // if (isset($get_customer_signup_data->customer_signup_otp_data)) {
                    //     if ($_POST['otp_verification'] != $get_customer_signup_data->customer_signup_otp_data->otp) {
                    //         echo json_encode(array('flag' => 0, 'msg' => "Please enter valid OTP!"));
                    //         exit;
                    //     }
                    // }

                    $webshopname = '';
                    $shop_logo = '';

                    $data['webshop_details'] = CommonRepository::get_webshop_details();
                    if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
                        $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                        $webshopname = $data['webshop_details']->FbcWebShopDetails->site_name;
                    }
                    $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
							<img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
						</a>';
                    $lang_code = '';
                    if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
                        $lang_code = $this->session->userdata('lcode');
                    }

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
                        'verifyRecaptcha' => 'no',
                        'ip' => $_SERVER['REMOTE_ADDR']
                    );
                    //print_r($postArr);exit;

                    $RegisterResponse = RegisterRepository::register($postArr);
                    $message = $RegisterResponse->message;
                    if (!empty($RegisterResponse) && (isset($RegisterResponse) && $RegisterResponse->is_success == 'true')) {
                        $QuoteId = '';
                        if ($this->session->userdata('QuoteId')) {
                            $QuoteId = $this->session->userdata('QuoteId');
                        }

                        $loginPostArr = array('email' => $email, 'password' => $password, 'quote_id' => $QuoteId, 'mobile_no' => $mobile_no);
                        $loginResponse = LoginRepository::login($loginPostArr);
                        $login_message = $loginResponse->message;

                        if (!empty($loginResponse) && (isset($loginResponse) && $loginResponse->is_success == 'true')) {

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
                                $cartApiUrl = '/webshop/update_quote_customer_id'; //Cart Listing
                                $cartArr = array('quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $LoginID, 'session_id' => $LoginToken, 'checkout_method' => 'register');
                                $loginResponse = LoginRepository::update_quote_customer_id($cartArr);
                                // $cart = $this->restapi->post_method($cartApiUrl, $cartArr);
                            }
                            $this->validateQuoteData($customer_type_id);
                            if (isset($_POST['display_page']) && $_POST['display_page'] == 'checkout') {
                                $redirect = base_url() . "checkout";
                            } else {
                                $redirect = base_url() . "customer/account";
                            }
                            echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect' => $redirect));
                            exit;
                        } else {
                            echo json_encode(array('flag' => 0, 'msg' => $login_message));
                            exit;
                        }
                    } else {
                        echo json_encode(array('flag' => 0, 'msg' => $message));
                        exit;
                    }
                }
            }
        }
    }

    public function login()
    {

        if (isset($_SESSION['LoginID']) && $_SESSION['LoginID'] != '') {
            redirect(BASE_URL('customer/account'));
        }
        $data['PageTitle'] = 'Login';
        if (empty($_POST)) {
            $this->template->load('login', $data);
        } else {

            if (isset($_POST['nickname']) && $_POST['nickname'] != '') {
                echo json_encode(array(
                    'flag' => 0,
                    'msg' => "Are you a bot?"
                ));
                exit;
            }

            $remember_me = (isset($_POST['remember_me']) && $_POST['remember_me']  != '' ? $_POST['remember_me'] : '');
            $password = $_POST['login_password'];

            $emailmobile = (isset($_POST['emailmobile'])) ? $_POST['emailmobile'] : '';
            $otp_verification = (isset($_POST['otp_verification'])) ? $_POST['otp_verification'] : '';
            $email = '';
            $mobile_no = '';
            if (filter_var($emailmobile, FILTER_VALIDATE_EMAIL)) {
                $email = (isset($_POST['emailmobile'])) ? $_POST['emailmobile'] : '';
            } else {
                $mobile_no = (isset($_POST['emailmobile'])) ? $_POST['emailmobile'] : '';
            }

            if (isset($mobile_no) && !empty($mobile_no)) {
                if (isset($otp_verification) && !empty($otp_verification)) {
                    $postArr = array('mobile_no' => $mobile_no);
                    $get_customer_signup_data = CustomerRepository::get_customer_signup_otp($postArr);
                    if (isset($get_customer_signup_data->customer_signup_otp_data)) {
                        if ($_POST['otp_verification'] != $get_customer_signup_data->customer_signup_otp_data->otp) {
                            echo json_encode(array('flag' => 0, 'msg' => "Please enter valid OTP!"));
                            exit;
                        }
                    }
                } else {
                    echo json_encode(array('flag' => 0, 'msg' => "Please enter OTP!"));
                    exit;
                }
            }



            $QuoteId = '';
            if ($this->session->userdata('QuoteId')) {
                $QuoteId = $this->session->userdata('QuoteId');
            }
            $webshopname = 'Indiamags';
            $shop_logo = '';

            $shop_logo = '';

            $data['webshop_details'] = CommonRepository::get_webshop_details();
            if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
                $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
                $webshopname = $data['webshop_details']->FbcWebShopDetails->site_name;
            }
            $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
					<img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
				</a>';
            $loginPostArr = array('email' => $email, 'mobile_no' => $mobile_no, 'otp_verification' => $otp_verification, 'remember' => $remember_me, 'email' => $email, 'password' => $password, 'quote_id' => $QuoteId, 'site_logo' => $site_logo);
            // print_R($loginPostArr );die();
            $loginResponse = LoginRepository::login($loginPostArr);


            $message = $loginResponse->message;
            if (!empty($loginResponse) && (isset($loginResponse) && $loginResponse->is_success == 'true')) {

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

                if (isset($_SESSION['currentPageUrl']) && $_SESSION['currentPageUrl'] != '') {
                    $redirect = BASE_URL . $_SESSION['currentPageUrl'];
                } else {
                    $redirect = BASE_URL . "customer/account";
                }


                if ($this->session->userdata('QuoteId')) {
                    // $cartApiUrl = '/webshop/update_quote_customer_id'; //Cart Listing
                    // $session_id = ((isset($_SESSION['sis_session_id']) && $_SESSION['sis_session_id'] != '' ) ? $_SESSION['sis_session_id'] :$LoginToken);
                    $cartArr = array('quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $LoginID, 'session_id' => $LoginToken, 'checkout_method' => 'login');
                    // $cart = $this->restapi->post_method($cartApiUrl, $cartArr);

                    // $postArr = array('mobile_no'=>$mobile_no);
                    $get_customer_signup_data = CommonRepository::UpdateQuoteToCustomer($cartArr);
                    // echo"Hellocartcheckget_customer_signup_data";print_R($get_customer_signup_data);die();



                }

                $this->validateQuoteData($customer_type_id);

                if ($remember_me == 1) {
                    setcookie('auth_token', $userdetails->AuthToken, time() + 60 * 60 * 24 * 100, '/', '', true, true);
                }

                $postArr = array('customer_id' => $LoginID);
                $response = CustomerRepository::customer_get_personal_info($postArr);
                if (!empty($response) && $response->is_success == 'true') {
                    $customerData = $response->customerData;
                    if ((isset($customerData->access_prelanch_product) && $customerData->access_prelanch_product == 1) || (isset($customerData->allow_catlog_builder) && $customerData->allow_catlog_builder == 1)) {
                        $this->session->set_userdata('special_features', 1);
                    }
                }

                echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect' => $redirect));
                exit;
            }

            echo json_encode(array('flag' => 0, 'msg' => $message));
            exit;
        }
    }

    public function validateQuoteData($customer_type_id)
    {


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

    public function login_popup()
    {
        if (isset($_SESSION['LoginID']) && $_SESSION['LoginID'] != '') {
            redirect(BASE_URL('customer/account'));
        }

        if (empty($_POST)) {
            $data['PageTitle'] = 'Login';
            $this->template->load('login', $data);
            return;
        }

        if (empty($_POST['email-popup']) || empty($_POST['password-popup'])) {
            echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
            exit;
        }

        if (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email-popup"])) {
            echo json_encode(array('flag' => 0, 'msg' => "Please enter a valid Email address."));
            exit;
        }

        if (isset($_POST['nickname-popup']) && $_POST['nickname-popup'] != '') {
            echo json_encode(array(
                'flag' => 0,
                'msg' => "Are you a bot?"
            ));
            exit;
        }

        $identity = 'captcha_check_flag';
        $captcha_flag = GlobalRepository::get_custom_variable(SHOPCODE, SHOP_ID, $identity);
        if (isset($captcha_flag->statusCode) && $captcha_flag->statusCode == '200') {
            $variable = $captcha_flag->custom_variable;
            $captcha_check_flag = $variable->value;
        } else {
            $captcha_check_flag = 'no';
        }

        if ($captcha_check_flag === 'yes' && verifyRecaptcha() === false) {
            echo json_encode([
                'flag' => 0,
                'msg' => 'Robot verification failed',
            ]);
            exit;
        }

        $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');

        $currency_name = (($this->session->userdata('currency_name')) ? $this->session->userdata('currency_name') : '');
        $currency_code_session = (($this->session->userdata('currency_code_session')) ? $this->session->userdata('currency_code_session') : '');
        $currency_conversion_rate = (($this->session->userdata('currency_conversion_rate')) ? $this->session->userdata('currency_conversion_rate') : '');
        $currency_symbol = (($this->session->userdata('currency_symbol')) ? $this->session->userdata('currency_symbol') : '');
        $default_currency_flag = (($this->session->has_userdata('default_currency_flag')) ? $this->session->userdata('default_currency_flag') : '');

        $remember_me = (isset($_POST['remember_me']) && $_POST['remember_me']  != '' ? $_POST['remember_me'] : '');
        $email = $_POST['email-popup'];
        $password = $_POST['password-popup'];
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $QuoteId = '';
        if ($this->session->userdata('QuoteId')) {
            $QuoteId = $this->session->userdata('QuoteId');
        }


        $data['webshop_details'] = CommonRepository::get_webshop_details($shopcode, $shop_id);
        if (!empty($data['webshop_details']) && (isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true')) {
            $shop_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
        }
        $webshopname = GlobalRepository::get_fbc_users_shop()?->result?->org_shop_name ?? '';
        $shop_logo = SITE_LOGO . '/' . $shop_logo;
        $site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
				<img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
			</a>';

        $loginPostArr = array('remember' => $remember_me, 'email' => $email, 'password' => $password, 'quote_id' => $QuoteId, 'site_logo' => $site_logo, 'vat_percent_session' => $vat_percent_session, 'currency_name' => $currency_name, 'currency_code_session' => $currency_code_session, 'currency_conversion_rate' => $currency_conversion_rate, 'currency_symbol' => $currency_symbol, 'default_currency_flag' => $default_currency_flag, 'verifyRecaptcha' => 'no');
        $loginResponse = LoginRepository::login($shopcode, $shop_id, $loginPostArr);

        $message = $loginResponse->message;
        if (!empty($loginResponse) && (isset($loginResponse) && $loginResponse->is_success == 'true')) {
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
            if (isset($_SESSION['currentPageUrl']) && $_SESSION['currentPageUrl'] != '') {
                $redirect = BASE_URL . $_SESSION['currentPageUrl'];
            } else {
                $redirect = BASE_URL . "customer/account";
            }

            if ($this->session->userdata('QuoteId')) {
                $cartApiUrl = '/webshop/update_quote_customer_id'; //Cart Listing
                $cartArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $LoginID, 'session_id' => $LoginToken);
                $cart = $this->restapi->post_method($cartApiUrl, $cartArr);
            }

            $this->validateQuoteData($customer_type_id);

            if ($remember_me == 1) {
                setcookie('auth_token', $userdetails->AuthToken, time() + 60 * 60 * 24 * 100, '/', '', true, true);
            }

            $postArr = array('customer_id' => $LoginID);
            $response = CustomerRepository::customer_get_personal_info($shopcode, $shop_id, $postArr);
            if (!empty($response) && $response->is_success == 'true') {
                $customerData = $response->customerData;
                if ((isset($customerData->access_prelanch_product) && $customerData->access_prelanch_product == 1) || (isset($customerData->allow_catlog_builder) && $customerData->allow_catlog_builder == 1)) {
                    $this->session->set_userdata('special_features', 1);
                }
            }

            echo json_encode(array('flag' => 1, 'msg' => $message, 'redirect' => $redirect));
            exit;
        }

        echo json_encode(array('flag' => 0, 'msg' => $message));
        exit;
    }

    public function forgotPassword()
    {
        if (isset($_SESSION['LoginID']) && $_SESSION['LoginID'] != '') {
            redirect(BASE_URL('customer/account'));
        }
        $data['PageTitle'] = 'Forgot Password';
        if (empty($_POST)) {
            $this->template->load('forgot_password', $data);
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
                    $response = LoginRepository::forgot_password($postArr);
                    //echo '<pre>';print_r($response);exit;

                    if (!empty($response) && (isset($response) && $response->is_success == 'true')) {
                        $userdetails = $response->userdetails;
                        $encoded_data = $userdetails->encoded_data;
                        $reset_url = BASE_URL . "customer/reset-password/" . $encoded_data;
                        //$this->session->set_flashdata('reset_link',$reset_url);

                        $redirect = BASE_URL . "customer/login";
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
        if (isset($_SESSION['LoginID']) && $_SESSION['LoginID'] != '') {
            redirect(BASE_URL('customer/account'));
        }
        $urlData = $this->uri->segment(3);
        if (empty($urlData)) {
            redirect(BASE_URL . 'customer/login');
        } else {
            if (empty($_POST)) {
                $data['urlData'] = $urlData;
                $data['PageTitle'] = 'Reset Password';
                $this->template->load('reset_password', $data);
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

                    $apiUrl = '/webshop/reset_password'; //reset password
                    $postArr = array('shopcode' => $shopcode, 'shopid' => $shop_id, 'email' => $email, 'password' => $password);
                    $response = LoginRepository::reset_password($postArr);
                    // $response = $this->restapi->post_method($apiUrl, $postArr);
                    // print_r($response);
                    // exit;
                    if (!empty($response) && (isset($response) && $response->is_success == 'true')) {
                        $message = $response->message;
                        $redirect = BASE_URL . "customer/login";
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

    public function logout()
    {


        if (isset($_SESSION['LoginToken']) && $_SESSION['LoginToken'] != '') {

            $LoginToken = $this->session->userdata('LoginToken');
            // $shopcode = SHOPCODE;
            // $shop_id = SHOP_ID;

            $apiUrl = '/webshop/logout'; //logout
            $postArr = array('LoginToken' => $LoginToken);
            $response = $this->restapi->post_method($apiUrl, $postArr);
            // print_r($response);exit;
            if ((isset($_SESSION['special_features']) && $_SESSION['special_features'] == 1)) {
                $this->session->unset_userdata('special_features');
            }

            $this->session->unset_userdata('LoginToken');
            $this->session->unset_userdata('LoginID');
            $this->session->unset_userdata('sis_session_id');
            $this->session->unset_userdata('QuoteId');
            session_destroy();
            setcookie('auth_token', '', time() - 1, '/', '', true, true);


            redirect(BASE_URL);
        } else {

     $this->session->unset_userdata('LoginToken');
            $this->session->unset_userdata('LoginID');
            $this->session->unset_userdata('sis_session_id');
            $this->session->unset_userdata('QuoteId');
            session_destroy();
            setcookie('auth_token', '', time() - 1, '/', '', true, true);
            redirect(BASE_URL . 'customer/login');
        }
    }

    public function ip_visitor_country()
    {
        return ip_visitor_country();
    }

    public function autologin()
    {
        if (isset($_SESSION['LoginID']) && $_SESSION['LoginID'] != '') {
            // redirect(BASE_URL('customer/account'));
            redirect(BASE_URL('customer/my-orders'));

        } else {

            $previous_QuoteId = (($this->session->userdata('QuoteId')) ? $this->session->userdata('QuoteId') : '');
            $vat_percent_session = (($this->session->userdata('vat_percent')) ? $this->session->userdata('vat_percent') : '');
            $currency_name = (($this->session->userdata('currency_name')) ? $this->session->userdata('currency_name') : '');
            $currency_code_session = (($this->session->userdata('currency_code_session')) ? $this->session->userdata('currency_code_session') : '');
            $currency_conversion_rate = (($this->session->userdata('currency_conversion_rate')) ? $this->session->userdata('currency_conversion_rate') : '');
            $currency_symbol = (($this->session->userdata('currency_symbol')) ? $this->session->userdata('currency_symbol') : '');
            $default_currency_flag = (($this->session->has_userdata('default_currency_flag')) ? $this->session->userdata('default_currency_flag') : '');

            $email = base64_decode($_GET['email']);
            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;
            $QuoteId = '';

            $remember_me = $_GET['z'] === '1' ? '1' : '';

            $this->session->unset_userdata('QuoteId');
            $loginPostArr = array('remember' => $remember_me, 'email' => $email, 'quote_id' => $QuoteId, 'previous_QuoteId' => $previous_QuoteId, 'vat_percent_session' => $vat_percent_session, 'currency_name' => $currency_name, 'currency_code_session' => $currency_code_session, 'currency_conversion_rate' => $currency_conversion_rate, 'currency_symbol' => $currency_symbol, 'default_currency_flag' => $default_currency_flag);
            $loginResponse = LoginRepository::autoLoginFromFBCAdmin($shopcode, $shop_id, $loginPostArr);

            if ($loginResponse->is_success == 'true') {
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

                $redirect = BASE_URL . "customer/account";

                if (isset($_SESSION['login_referrer'])) {
                    $redirect = $_SESSION['login_referrer'];
                    unset($_SESSION['login_referrer']);
                }

                if ($this->session->userdata('QuoteId')) {
                    $cartArr = array('quote_id' => $this->session->userdata('QuoteId'), 'customer_id' => $LoginID, 'session_id' => $LoginToken, 'checkout_method' => 'login');
                    $cart = CheckoutRepository::update_quote_customer_id($cartArr);
                }


                if ($remember_me == 1) {
                    setcookie('auth_token', $userdetails->AuthToken, time() + 60 * 60 * 24 * 100, '/', '', true, true);
                }

                $postArr = array('customer_id' => $LoginID);
                $response = CustomerRepository::customer_get_personal_info($postArr);
                if (!empty($response) && $response->is_success == 'true') {
                    $customerData = $response->customerData;
                    if ((isset($customerData->access_prelanch_product) && $customerData->access_prelanch_product == 1) || (isset($customerData->allow_catlog_builder) && $customerData->allow_catlog_builder == 1)) {
                        $this->session->set_userdata('special_features', 1);
                    }
                }

                redirect($redirect);
            } else {
                redirect(BASE_URL);
            }
        }
    } //main
}
