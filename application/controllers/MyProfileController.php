<?php

defined('BASEPATH') or exit('No direct script access allowed');



class MyProfileController extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

        if ($this->session->userdata('LoginID') == '') {

            redirect(BASE_URL . 'customer/login');
        }
        $this->load->model('CommonModel');
    }



    public function getProfileDetails()

    {

        $data['PageTitle'] = 'My Profile - Personal Information';

        $LoginID = $_SESSION['LoginID'];





        $data['side_tab'] = 'account_info';



        $postArr = array('customer_id' => $LoginID);

        $response = CustomerRepository::customer_get_personal_info($postArr);

        if (!empty($response) && isset($response) && $response->is_success == 'true') {

            $data['customerData'] = $customerData = $response->customerData;

            $data['profilePercentage'] = $response->profile_percentage;
        }



        $table = 'country_master';

        $flag = 'own';

        $postArr2 = array('table_name' => $table, 'database_flag' => $flag);

        $response2 = CommonRepository::get_table_data($postArr2, 3600);

        if (!empty($response2) && isset($response2) &&  $response2->is_success == 'true') {

            $data['countryList'] = $response2->tableData;
        }



        // $shopDataflag = GlobalRepository::get_fbc_users_shop();

        // $data['shop_flag'] = $shopDataflag->result->shop_flag ?? '';



        $identifier = 'restricted_access';

        $ApiResponse = GlobalRepository::get_custom_variable($identifier);

        if (!empty($ApiResponse) && isset($ApiResponse) && $ApiResponse->statusCode == '200') {

            $RowCV = $ApiResponse->custom_variable;

            $restricted_access = $RowCV->value;
        } else {

            $restricted_access = 'no';
        }

        $data['restricted_access'] = $restricted_access;



        // $apiUrl = '/webshop/customer_get_profile_completion'; //customer_get_personal_info

        // $postArr = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'customer_id'=>$LoginID);

        // $response= $this->restapi->post_method($apiUrl,$postArr);

        // //echo '<pre>';print_r($response);//exit;

        // if($response->is_success=='true'){

        // 	$data['customerData'] = $response->customerData;

        // }



        $this->template->load('myprofile/personal_info', $data);
    }



    public function updateCustomerInfo()

    {

        if (!empty($_POST)) {

            if (empty($_POST['first_name']) || empty($_POST['last_name'])) {

                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

                exit;
            }

            // else if(!empty($_POST['dob']) && !preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/",$_POST['dob'])){

            // 	echo json_encode(array('flag'=>0, 'msg'=>"Date format is incorrect."));

            // 	exit;

            // }

            $LoginID = $_SESSION['LoginID'];

            // $shopcode = SHOPCODE;

            // $shop_id = SHOP_ID;



            $first_name = $_POST['first_name'];

            $last_name = $_POST['last_name'];

            $gender = isset($_POST['gender']) ? $_POST['gender'] : '';

            // $mobile_no = $_POST['mobile_no'];

            $country_code = $_POST['country'];

            $dob = ($_POST['dob'] != '') ? date("Y-m-d", strtotime($_POST['dob'])) : '';

            $company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';

            $gst_no = isset($_POST['gst_no']) ? $_POST['gst_no'] : '';



            $postArr = array(

                'customer_id' => $LoginID,

                'first_name' => $first_name,

                'last_name' => $last_name,

                'gender' => $gender,

                // 'mobile_no'=>$mobile_no,

                'country_code' => $country_code,

                'dob' => $dob,

                'company_name' => $company_name,

                'gst_no' => $gst_no,

                'ip' => $_SERVER['REMOTE_ADDR']

            );

            $response = CustomerRepository::customer_update_personal_info($postArr);

            //echo '<pre>';print_r($response);exit;

            $message = $response->message;

            if (!empty($response) && isset($response) && $response->is_success == 'true') {

                $sessionArr = array('FirstName' => $first_name, 'LastName' => $last_name);

                $this->session->set_userdata($sessionArr);



                echo json_encode(array('flag' => 1, 'msg' => $message));

                exit;
            } else {

                echo json_encode(array('flag' => 0, 'msg' => $message));

                exit;
            }
        }
    }

    public function helpDesk()
    {
        $customer_id = $_SESSION['LoginID'];
        $data['PageTitle'] = 'Create New Ticket';
        $data['side_tab']  = 'help_desk';

        $data['orders'] = $this->CommonModel->get_customer_orders($customer_id, 50, 0);
        $data['help_desk_data'] = $this->CommonModel->get_help_desk_data($customer_id);
        // echo "<pre>";print_r($data['help_desk_data']);die;


        // echo "<pre>";print_r($data);die;
        $this->template->load('myprofile/help_desk', $data);
    }

    public function messaging()
    {
        $customer_id = $_SESSION['LoginID'];
        $data['PageTitle'] = 'Messages';
        $data['side_tab']  = 'messaging';

        // Get the latest message per product
        $this->db->select('pq.*');
        $this->db->from('product_questions pq');
        $this->db->where('pq.customer_id', $customer_id);
        $this->db->order_by('pq.created_at', 'DESC');
        $query = $this->db->get();
        $all_messages = $query->result();

        // Keep only the latest message per product_id
        $latest_per_product = [];
        foreach ($all_messages as $msg) {
            if (!isset($latest_per_product[$msg->product_id])) {
                $latest_per_product[$msg->product_id] = $msg;
            }
        }

        $data['messaging_data'] = $latest_per_product;
        $this->template->load('myprofile/messaging', $data);
    }


    public function get_order_products()
    {
        $order_id = $this->input->post('order_id');


        $products = $this->CommonModel->get_order_products($order_id);
        // echo "<pre>";print_r($products);die;

        echo json_encode($products);
    }
    public function helpDeskPost()
    {
        $subject     = $this->input->post('subject');
        $category    = $this->input->post('category_id');
        $priority    = $this->input->post('priority_id');
        $message     = $this->input->post('message');
        $order_id    = $this->input->post('order_id');
        $product_id  = $this->input->post('product_id');
        $customer_id = $_SESSION['LoginID'];

        if (empty($subject) || empty($category) || empty($priority) || empty($message)) {
            echo json_encode(['flag' => 0, 'msg' => 'Please fill all required fields.']);
            return;
        }

        // Handle file upload
        $attachment_name = '';
        if (isset($_FILES['attachment']) && $_FILES['attachment']['name'] != '') {
            $config['upload_path']   = './uploads/help_desk_attachment/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size']      = 2048; // 2MB
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('attachment')) {
                $fileData = $this->upload->data();
                $attachment_name = $fileData['file_name'];
            } else {
                echo json_encode(['flag' => 0, 'msg' => $this->upload->display_errors()]);
                return;
            }
        }

        // --- Check if a ticket already exists for this order + product ---
        $existing_ticket = $this->db
            ->where('order_id', $order_id)
            ->where('products', $product_id)
            ->where('customer_id', $customer_id)
            ->order_by('id', 'ASC') // get the first ticket for this combination
            ->get('help_desk')
            ->row();

        if ($existing_ticket) {
            $ticket_id = $existing_ticket->ticket_id; // reuse existing ticket ID
        } else {
            // generate new ticket_id
            $last_ticket = $this->db->order_by('id', 'DESC')->get('help_desk')->row();
            if ($last_ticket && !empty($last_ticket->ticket_id)) {
                $last_number = intval($last_ticket->ticket_id);
                $ticket_id = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $ticket_id = '0001';
            }
        }

        // Insert new ticket message (or follow-up)
        $postArr = [
            'ticket_id'    => $ticket_id,
            'subject'      => $subject,
            'category'     => $category,
            'priority'     => $priority,
            'message'      => $message,
            'order_id'     => $order_id,
            'products'     => $product_id,
            'customer_id'  => $customer_id,
            'attachment'   => $attachment_name,
            'admin_reply'  => '',  // empty for customer
            'status'       => 0,   // Not opened
            'created_at'   => strtotime(date('Y-m-d H:i:s')),
            'updated_at'   => strtotime(date('Y-m-d H:i:s')),
            'ip'           => $_SERVER['REMOTE_ADDR'],
        ];

        $this->db->insert('help_desk', $postArr);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            echo json_encode(['flag' => 1, 'msg' => 'Your ticket has been submitted successfully.', 'ticket_id' => $ticket_id]);
        } else {
            echo json_encode(['flag' => 0, 'msg' => 'Failed to submit ticket.']);
        }
    }

    public function messagingPost()
    {
        $name     = $this->input->post('name');
        $category    = $this->input->post('category');
        $email    = $this->input->post('email') ?? '';
        $message     = $this->input->post('message');
        $product_id  = $this->input->post('product_id');
        $merchant_id    = $this->input->post('merchant_id');
        $customer_id = $_SESSION['LoginID'];

        if (empty($message)) {
            echo json_encode(['flag' => 0, 'msg' => 'Please fill required field.']);
            return;
        }

        // Insert new ticket message (or follow-up)
        $postArr = [
            'name'      => $name,
            'category'     => $category,
            'email'     => $email,
            'message'      => $message,
            'product_id'     => $product_id,
            'merchant_id'   => $merchant_id,
            'customer_id'  => $customer_id,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
            'ip'           => $_SERVER['REMOTE_ADDR'],
        ];

        $this->db->insert('product_questions', $postArr);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            echo json_encode(['flag' => 1, 'msg' => 'Your reply has been submitted successfully.']);
        } else {
            echo json_encode(['flag' => 0, 'msg' => 'Failed to submit reply.']);
        }
    }

    public function viewTicket($order_id, $product_id)
    {
        $customer_id = $_SESSION['LoginID'];

        // Get all messages for this order + product
        $this->db->where('order_id', $order_id);
        $this->db->where('products', $product_id);
        $this->db->order_by('created_at', 'ASC');
        $help_desk_data = $this->db->get('help_desk')->result();

        // Get order details
        $order = $this->CommonModel->get_order_by_id($order_id); // we'll create this function

        // Get product details
        $product = $this->CommonModel->get_product_by_order($order_id, $product_id);

        $data['help_desk_data'] = $help_desk_data;
        $data['order'] = $order;
        $data['product'] = $product;

        $this->load->view('myprofile/help_desk_conversation', $data);
    }

    public function viewMessage($product_id)
    {
        $customer_id = $_SESSION['LoginID'];

        $this->db->where('customer_id', $customer_id);
        $this->db->where('product_id', $product_id);
        $this->db->order_by('id', 'ASC');
        $messaging_data = $this->db->get('product_questions')->result();
        $data['messaging_data'] = $messaging_data;
        $this->load->view('myprofile/messaging_conversation', $data);
    }



    public function changeEmail()

    {



        $LoginID = $_SESSION['LoginID'];

        if (empty($_POST)) {

            $data['LoginID'] = $_SESSION['LoginID'];

            $View = $this->load->view('myprofile/change-email-popup', $data, true);

            $this->output->set_output($View);
        } else {

            if (isset($_POST)) {

                if (empty($_POST['new_email'])) {

                    echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

                    exit;
                } else {



                    $email = $_SESSION['EmailID'];

                    $postArr = array('customer_id' => $LoginID);

                    $response = CustomerRepository::customer_get_personal_info($postArr);



                    $customerData = $response->customerData;

                    if ($_POST['new_email'] != $customerData->email_id) {

                        $new_email = $_POST['new_email'];

                        $postArr1 = array('email_id' => $new_email);

                        $EmailExits = CustomerRepository::customer_email_exits($postArr1);



                        if (!isset($EmailExits->customerData)) {

                            $changePostArr = array('email' => $new_email, 'customer_id' => $LoginID);

                            $resetResponse = CustomerRepository::change_email($changePostArr);

                            if (!empty($resetResponse) && isset($resetResponse)) {

                                $message = $resetResponse->message;

                                if ($response->is_success == 'true') {

                                    $this->session->set_userdata('EmailID', $new_email);

                                    echo json_encode(array('flag' => 1, 'msg' => $message));

                                    exit;
                                } else {

                                    echo json_encode(array('flag' => 0, 'msg' => $message));

                                    exit;
                                }
                            } else {

                                echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong!'));

                                exit;
                            }
                        } else {

                            echo json_encode(array("flag" => 2, "msg" => "New Email Already Exists "));
                        }
                    } else {

                        echo json_encode(array("flag" => 2, "msg" => "Email Already Exists "));

                        exit;
                    }
                }
            }
        }
    }



    public function changePassword()

    {

        $LoginID    =    $_SESSION['LoginID'];

        if (empty($_POST)) {

            $data['LoginID']    =    $_SESSION['LoginID'];

            $View = $this->load->view('myprofile/change_password_popup', $data, true);

            $this->output->set_output($View);
        } else {

            if (isset($_POST)) {

                if (empty($_POST['old_password']) || empty($_POST['new_password']) || empty($_POST['conf_password'])) {

                    echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

                    exit;
                } elseif ($_POST['new_password'] != $_POST['conf_password']) {

                    echo json_encode(array('flag' => 0, 'msg' => "Confirm Password does not match."));

                    exit;
                } else {



                    $password = $_POST['new_password'];

                    $email = $_SESSION['EmailID'];



                    $postArr = array('customer_id' => $LoginID);

                    $response = CustomerRepository::customer_get_personal_info($postArr);



                    if (!empty($response) && isset($response)) {

                        $message = $response->message;

                        if ($response->is_success == 'true') {

                            $customerData = $response->customerData;

                            if (md5($_POST['old_password']) != $customerData->password) {

                                echo json_encode(array('flag' => 0, 'msg' => "Old password is incorrect."));

                                exit;
                            }



                            $resetPostArr = array('email' => $email, 'password' => $password);

                            $resetResponse = LoginRepository::reset_password($resetPostArr);

                            if (!empty($resetResponse) && isset($resetResponse)) {

                                $message = $resetResponse->message;

                                if ($response->is_success == 'true') {

                                    echo json_encode(array('flag' => 1, 'msg' => $message));

                                    exit;
                                } else {

                                    echo json_encode(array('flag' => 0, 'msg' => $message));

                                    exit;
                                }
                            } else {

                                echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong!'));

                                exit;
                            }
                        } else {

                            echo json_encode(array('flag' => 0, 'msg' => $message));

                            exit;
                        }
                    } else {

                        echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong!'));

                        exit;
                    }
                }
            } else {

                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

                exit;
            }
        }
    }



    public function getAddressDetails()

    {

        $data['PageTitle'] = 'My Profile - Manage Address';

        $data['side_tab'] = 'my_address';



        $this->template->load('myprofile/manage_address', $data);
    }



    public function openAddressPopup()

    {

        $LoginID    =    $_SESSION['LoginID'];

        //print_r($_POST);exit;

        if (isset($_POST)) {

            $data['LoginID'] = $_SESSION['LoginID'];

            $data['flag'] = $_POST['flag'];

            $data['customer_id'] = $_POST['customer_id'];

            $data['address_id']    = $_POST['address_id'];



            if ($_POST['flag'] == 'edit') {

                $table = 'customers_address';

                $flag = 'own';

                $where = 'id = ? AND customer_id = ?';

                $order_by = 'ORDER BY id DESC';

                $params = array($_POST['address_id'], $LoginID);

                $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);

                $response = CommonRepository::get_table_data($postArr);

                if (!empty($response) && isset($response) && $response->is_success == 'true') {

                    $data['addressData'] = $response->tableData;
                }
            } else {

                $table = 'customers_address';

                $flag = 'own';

                $where = 'customer_id = ?';

                $order_by = 'ORDER BY id DESC';

                $params = array($LoginID);

                $postArr = array('table_name' => $table, 'database_flag' => $flag, 'where' => $where, 'order_by' => $order_by, 'params' => $params);

                $response = CommonRepository::get_table_data($postArr);

                if (!empty($response) && isset($response) && $response->is_success == 'true') {

                    $data['addressData'] = $response->tableData;
                }
            }



            $postArr = array('table_name' => 'country_master', 'database_flag' => 'own');

            $response = CommonRepository::get_table_data($postArr, 3600);

            if (!empty($response) && isset($response) && $response->is_success == 'true') {

                $data['countryList'] = $response->tableData;
            }



            $postArr3 = array('table_name' => 'country_state_master_in', 'database_flag' => 'main');

            $response3 = CommonRepository::get_table_data($postArr3, 3600);

            if (!empty($response3) && isset($response3) && $response3->is_success == 'true') {

                $data['stateList'] = $response3->tableData;
            }


            $table = 'city_master';
            $flag = 'main';
            $postArr4 = array('table_name' => $table, 'database_flag' => $flag);
            $response4 = CommonRepository::get_table_data($postArr4, 3600);
            if (!empty($response4) && isset($response4) && $response4->is_success == 'true') {
                $data['cityList'] = $response4->tableData;
            }
            // echo "<pre>";

            // print_r($data);die;



            $View = $this->load->view('myprofile/address_popup', $data, true);

            $this->output->set_output($View);
        }
    }


    public function getCities()
    {
        $state_id = $this->input->post('state_id');
        $cities = $this->CommonModel->getCitiesByState($state_id);
        echo json_encode(['status' => 'success', 'cities' => $cities]);
    }
    public function addEditAddress()

    {

        if (empty($_POST)  || empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['address_line1']) || empty($_POST['city']) || empty($_POST['pincode']) || empty($_POST['country'])) {

            echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

            exit;
        }



        $LoginID = $_SESSION['LoginID'];

        $address_id = $_POST['address_id'];



        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;



        $first_name = $_POST['first_name'];

        $last_name = $_POST['last_name'];

        $address_line1 = $_POST['address_line1'];

        $address_line2 = $_POST['address_line2'];

        $city = $_POST['city'];

        $state = ($_POST['country'] === 'IN' ? $_POST['state_dp'] : $_POST['state']);

        $pincode = $_POST['pincode'];

        $mobile_no = $_POST['mobile_no'];

        $country = $_POST['country'];



        $company_name = $_POST['company_name'] ?? '';

        $vat_no = $_POST['vat_no'] ?? '';

        $consulation_no = $_POST['consulation_no'] ?? '';

        $res_company_name = $_POST['res_company_name'] ?? '';

        $res_company_address = $_POST['res_company_address'] ?? '';



        $vat_vies_valid_flag = $_POST['vat_flag'] ?? ''; //valid 1,  not valid 0





        $postArr = array(

            'customer_id' => $LoginID,

            'first_name' => $first_name,

            'last_name' => $last_name,

            'address_line1' => $address_line1,

            'address_line2' => $address_line2,

            'city' => $city,

            'state' => $state,

            'pincode' => $pincode,

            'mobile_no' => $mobile_no,

            'country_code' => $country,

            'customer_address_id' => $address_id,

            'company_name' => $company_name,

            'vat_no' => $vat_no,

            'consulation_no' => $consulation_no,

            'res_company_name' => $res_company_name,

            'res_company_address' => $res_company_address,

            'vat_vies_valid_flag' => $vat_vies_valid_flag,

        );
        $full_address = $postArr['address_line1'] . ' ' . $postArr['address_line2'] . ' ' .
            $postArr['city'] . ' ' . $postArr['state'] . ' ' .
            $postArr['country'] . ' ' . $postArr['pincode'];

        $encoded_address = urlencode($full_address);
        $apiKey = 'AIzaSyAH2XRKr0rfw3h4z8ZYa2P4YiuhVhdKCZ0'; // Replace with your valid Google API Key
        $geocodeURL = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&key={$apiKey}";

        // Safer to use cURL instead of file_get_contents
        $response = file_get_contents($geocodeURL);
        $responseData = json_decode($response, true);

        if (!empty($responseData['results'][0]['geometry']['location'])) {
            $latitude  = $responseData['results'][0]['geometry']['location']['lat'];
            $longitude = $responseData['results'][0]['geometry']['location']['lng'];
            $postArr['latitude']  = $latitude;
            $postArr['longitude'] = $longitude;
        } else {
            $postArr['latitude']  = null;
            $postArr['longitude'] = null;
        }

        $response = CustomerRepository::customer_address_add_edit($shopcode, $shop_id, $postArr);

        if (!empty($response) && isset($response)) {

            $message = $response->message;

            if ($response->is_success == 'true') {

                echo json_encode(array('flag' => 1, 'msg' => $message));

                exit;
            }



            echo json_encode(array('flag' => 0, 'msg' => $message));

            exit;
        }



        echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong!'));

        exit;
    }



    public function makeDefaultAddress()

    {

        $LoginID    =    $_SESSION['LoginID'];

        //print_r($_POST);exit;

        if (isset($_POST) && $_POST['address_id'] != '') {

            $LoginID = $_SESSION['LoginID'];

            $address_id = $_POST['address_id'];



            $shopcode = SHOPCODE;

            $shop_id = SHOP_ID;





            $postArr = array('customer_id' => $LoginID, 'customer_address_id' => $address_id);

            $response = CustomerRepository::customer_address_setdefault($shopcode, $shop_id, $postArr);

            if (!empty($response) && isset($response)) {

                $message = $response->message;

                if ($response->is_success == 'true') {

                    echo json_encode(array('flag' => 1, 'msg' => $message));

                    exit;
                } else {

                    echo json_encode(array('flag' => 0, 'msg' => $message));

                    exit;
                }
            } else {

                echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong!'));

                exit;
            }
        } else {

            echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

            exit;
        }
    }



    public function removeAddress()

    {

        $LoginID    =    $_SESSION['LoginID'];

        //print_r($_POST);exit;

        if (isset($_POST) && $_POST['address_id'] != '') {

            $LoginID = $_SESSION['LoginID'];

            $address_id = $_POST['address_id'];



            $shopcode = SHOPCODE;

            $shop_id = SHOP_ID;





            $postArr = array('customer_id' => $LoginID, 'customer_address_id' => $address_id);

            $response = CustomerRepository::customer_address_delete($shopcode, $shop_id, $postArr);

            if (!empty($response) && isset($response)) {

                $message = $response->message;

                if ($response->is_success == 'true') {

                    echo json_encode(array('flag' => 1, 'msg' => $message));

                    exit;
                } else {

                    echo json_encode(array('flag' => 0, 'msg' => $message));

                    exit;
                }
            } else {

                echo json_encode(array('flag' => 0, 'msg' => 'Something went wrong!'));

                exit;
            }
        } else {

            echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

            exit;
        }
    }
}
