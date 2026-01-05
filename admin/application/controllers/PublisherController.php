<?php



class PublisherController extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

        $this->load->model('PublisherModel');
		$this->load->model('CommonModel');


    }



    public function publisherList()

    {

        if ($_SESSION['UserRole'] !== 'Super Admin') {

            if (

                !empty($this->session->userdata('userPermission')) &&

                !in_array('database/publishers', $this->session->userdata('userPermission'))

            ) {

                redirect('dashboard');

            }

        }



        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {



            // ✅ check if query string has status

            $status = $this->input->get('status');



            if ($status !== null && $status !== '') {

                $data['getPublishers'] = $this->PublisherModel->get_publishers_by_status($status);

            } else {

                $data['getPublishers'] = $this->PublisherModel->get_publishers();

            }



            $data['PageTitle'] = 'Publishers';

            $data['side_menu'] = 'publishers';



            $this->load->view('publishers/publishers_list', $data);



        } else {

            return redirect('/');

        }

}



    // public function check()

    // {

    //     echo $_SERVER['DOCUMENT_ROOT'];die;

    // }

    public function addPublishers()

    {

        if ($_SESSION['UserRole'] !== 'Super Admin') {

            if (!empty($this->session->userdata('userPermission')) && !in_array('database/publishers', $this->session->userdata('userPermission'))) {

                redirect('dashboard');

            }

        }



        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {

            $data['PageTitle'] = 'Publisher Add';

            $data['side_menu'] = 'publisher';

            $this->load->view('publishers/publishers_add');

        } else {

            return redirect('/');

        }

    }



    public function submitPublisher()

    {



        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {

            $publisher_id = $_POST['publisher_id'];



            if (empty($_POST['email']) || empty($_POST['publication_name']) || empty($_POST['vendor_name']) || empty($_POST['commision_percent'])) {

                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

                exit;

            } elseif (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email"])) {



                echo json_encode(array('flag' => 0, 'msg' => "Please enter a valid Email address."));

                exit;

            } elseif (($this->PublisherModel->checkEmailidExit($_POST["email"])) != 0 && $publisher_id == '') {



                echo json_encode(array('flag' => 0, 'msg' => "Email id already exist."));

                exit;

            } elseif ($this->PublisherModel->checkPublicationName($_POST["publication_name"]) != 0 && $publisher_id == '') {

                // print_r($this->PublisherModel->checkPublicationName($_POST["publication_name"]));

                // die();

                echo json_encode(array('flag' => 0, 'msg' => "Publication  Name already exist."));

                exit;

            } else {



                if ($publisher_id != '') {



                    if ($_POST['passwordCheck'] == 'check' && empty($_POST["password"])) {

                        echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

                        exit;

                    } elseif ($this->PublisherModel->checkEmailIdExitDuringUpdate($_POST["email"], $publisher_id) != 0) {

                        echo json_encode(array('flag' => 0, 'msg' => "Email id already exist."));

                        exit;

                    } else {



                        $isPasswordChecked = $_POST['passwordCheck'];

                        $hashPassword = "";

                        $emails = $this->input->post('emails');

                        $emailsArray = preg_replace('/\s+| /u', '', explode(',', $emails));

                        $implodedString = implode(', ', $emailsArray);

                        $updateData = array();

                        if ($isPasswordChecked == 'check') {

                            $hashPassword = md5($_POST["password"]);

                            $updateData = array(

                                'email'                => $_POST['email'],

                                'cc_email'                => $implodedString,

                                'merchant_cat'       => $_POST['merchant_cat'],

                                'company_name'       => $_POST['company_name'],

                                'location'       => $_POST['location'],

                                'company_address'       => $_POST['company_address'],

                                'shipment_type'       => $_POST['shipment_type'],

                                'password'            => $hashPassword,

                                'publication_name'    => $_POST['publication_name'],

                                'vendor_name'        => $_POST['vendor_name'],

                                'commision_percent' => $_POST['commision_percent'],

                                'vat_status'             => $_POST['vat_status'],

                                'vat_no'                 => $_POST['vat_no'],

                                'default_vat_percentage' => $_POST['default_vat_percentage'],

                                'split_id'          => $_POST['split_id'],

                                'phone_no'             => $_POST['phone_no'],

                                'landline_no'             => $_POST['landline_no'],

                                'description'        => $_POST['description'],

                                'status'            => $_POST['status'],

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR'],

                            );

                            $update_publisher_payment_details_data = array(

                                'publisher_id'                => $publisher_id,

                                'bank_name'            => $_POST['bank_name'],

                                'bank_branch_number'            => $_POST['bank_branch_number'],

                                'beneficiary_acc_no'            => $_POST['beneficiary_acc_no'],

                                'beneficiary_name'    => $_POST['beneficiary_name'],

                                'beneficiary_ifsc_code'        => $_POST['beneficiary_ifsc_code'],

                                'created_by'        => $SISA_ID,

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR']

                            );

                        } else {

                            $updateData = array(

                                'email'                => $_POST['email'],

                                'cc_email'                => $implodedString,

                                'merchant_cat'       => $_POST['merchant_cat'],

                                'company_name'       => $_POST['company_name'],

                                'location'       => $_POST['location'],

                                'company_address'       => $_POST['company_address'],

                                'shipment_type'       => $_POST['shipment_type'],


                                'publication_name'    => $_POST['publication_name'],

                                'vendor_name'        => $_POST['vendor_name'],

                                'commision_percent' => $_POST['commision_percent'],

                                'vat_status'             => $_POST['vat_status'],

                                'vat_no'                 => $_POST['vat_no'],

                                'default_vat_percentage' => $_POST['default_vat_percentage'],

                                'split_id'          => $_POST['split_id'],

                                'phone_no'             => $_POST['phone_no'],

                                'landline_no'             => $_POST['landline_no'],

                                'description'        => $_POST['description'],

                                'status'            => $_POST['status'],

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR'],

                            );

                            $update_publisher_payment_details_data = array(

                                'publisher_id'                => $publisher_id,

                                'bank_name'            => $_POST['bank_name'],

                                'bank_branch_number'            => $_POST['bank_branch_number'],

                                'beneficiary_acc_no'            => $_POST['beneficiary_acc_no'],

                                'beneficiary_name'    => $_POST['beneficiary_name'],

                                'beneficiary_ifsc_code'        => $_POST['beneficiary_ifsc_code'],

                                'created_by'        => $SISA_ID,

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR']

                            );

                        }

                        $is_success = $this->PublisherModel->update_publishers($updateData, $publisher_id, $update_publisher_payment_details_data);

                        if ($is_success) {
                            if ($_POST['status'] == 1) {
                                $TempVars1 = array("##NAME##" ,"##EMAILID##");
                                $DynamicVars2 = array($_POST['publication_name'], $_POST['email']);
                                
                                $merchantTemplateId = 'merchant-approval'; // Merchant email template ID
                                $merchantMailSent = $this->CommonModel->sendCommonHTMLEmail($_POST['email'], $merchantTemplateId, $TempVars1, $DynamicVars2);
                            }


                            echo json_encode("success");

                            exit;

                            // $url = base_url().'publishers';

                            // echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated","url"=>$url));

                            // exit;	

                        } else {

                            // echo json_encode("error");

                            // exit;

                            echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));

                            exit;

                        }

                    }

                } else {



                    // Add publisher

                    $emails = $this->input->post('emails');

                    $emailsArray = preg_replace('/\s+| /u', '', explode(',', $emails));

                    $implodedString = implode(', ', $emailsArray);



                    $hashPassword = md5($_POST["password"]);

                    $insertData = array(

                        'email'                => $_POST['email'],

                        'cc_email'                => $implodedString,

                        'password'            => $hashPassword,

                        'publication_name'    => $_POST['publication_name'],

                        'vendor_name'        => $_POST['vendor_name'],

                        'commision_percent' => $_POST['commision_percent'],

                        'vat_status'             => $_POST['vat_status'],

                        'vat_no'                 => $_POST['vat_no'],

                        'default_vat_percentage' => $_POST['default_vat_percentage'],

                        'split_id'          => $_POST['split_id'],

                        'phone_no'             => $_POST['phone_no'],

                        'description'        => $_POST['description'],

                        'status'            => $_POST['status'],

                        'remove_flag'       => 0,

                        'created_by'        => $SISA_ID,

                        'created_at'        => strtotime(date('Y-m-d H:i:s')),

                        'ip'                => $_SERVER['REMOTE_ADDR']

                    );



                    $is_success = $this->PublisherModel->insert_publishers($insertData);

                    if ($is_success) {

                        $publisher_id = $this->db->insert_id();

                        $insertPublisherPaymentDetailsData = array(

                            'publisher_id'                => $publisher_id,

                            'beneficiary_acc_no'            => $_POST['beneficiary_acc_no'],

                            'beneficiary_name'    => $_POST['beneficiary_name'],

                            'beneficiary_ifsc_code'        => $_POST['beneficiary_ifsc_code'],

                            'created_by'        => $SISA_ID,

                            'created_at'        => strtotime(date('Y-m-d H:i:s')),

                            'ip'                => $_SERVER['REMOTE_ADDR']

                        );

                        $is_success = $this->PublisherModel->insert_publisher_payment_details($insertPublisherPaymentDetailsData);



                        echo json_encode("success");

                        exit;

                        // $url = base_url().'publishers';

                        // echo json_encode(array('flag' => 1, 'msg' => "Successfully Added","url"=>$url));

                        // exit;	

                    } else {

                        // echo json_encode("error");

                        // exit;

                        echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));

                        exit;

                    }

                }

            }

        } else {

            return redirect('/');

        }

    }



    public function editPublisher($publisherId)

    {

        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {

            if ($publisherId) {

                $publisherDATA = $this->PublisherModel->getSingleDataByID('publisher', array('id' => $publisherId), '*');

                if ($publisherDATA == '') {

                    return redirect('/');

                }

                $data['publisher'] = $this->PublisherModel->get_publisher_detail($publisherId);

                $data['publisher_payment_details'] = $this->PublisherModel->get_publisher_payment_details($publisherId);

                // print_r($data['publisher_payment_details']);

                // die;

                $data['PageTitle'] = 'Publisher Edit';

                $data['side_menu'] = 'publisher';

                $this->load->view('publishers/publishers_edit', $data);

            } else {

                return redirect('/');

            }

        } else {

            return redirect('/');

        }

    }



    function deletePublisher()

    {

        $publisherId = $_POST['id'];

        $is_success = $this->PublisherModel->delete_publishers($publisherId);

        if ($is_success) {

            echo 'success';

            exit;

        } else {

            echo 'error';

            exit;

        }

    }



    public function publisherCommissionList()

    {

        if ($_SESSION['UserRole'] !== 'Super Admin') {

            if (!empty($this->session->userdata('userPermission')) && !in_array('database/publishers', $this->session->userdata('userPermission'))) {

                redirect('dashboard');

            }

        }



        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {

            $data['getPublishers'] = $this->PublisherModel->get_publishers();

            $data['PageTitle'] = 'Publishers';

            $data['side_menu'] = 'publisher_commission';



            // print_r($data);die();

            $this->load->view('publishers/publisher_commission_list', $data);

        } else {



            return redirect('/');

        }

    }

}

