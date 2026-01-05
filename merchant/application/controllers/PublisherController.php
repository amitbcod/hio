<?php 
class PublisherController extends CI_Controller
{
    public function __construct()

    {

         parent::__construct();

		 $this->load->model('PublisherModel');

    }

	public function merchantsList()

	{ 

		if(isset($_SESSION['UserRole']) && $_SESSION['UserRole'] !== 'Super Admin') {

            if(!empty($this->session->userdata('userPermission')) && !in_array('database/publishers',$this->session->userdata('userPermission'))){ 

                redirect('dashboard');

            }

        }

		$SISA_ID=$this->session->userdata('LoginID');

		if($SISA_ID){			

			$data['getPublishers'] = $this->PublisherModel->get_publishers();

			$data['PageTitle']='Merchants';

			$data['side_menu']='publishers';

			

            $this->load->view('publishers/merchants_list',$data);  

		}else{

			return redirect('/'); 

		}

	}

    function openAdminUserPasswordPopup()

    {

        $data['PageTitle'] = 'Reset Password';

        $this->load->view('forgot_password/forgot_password_new', $data);

    }

    public function addMerchants()

	{

        if(isset($_SESSION['UserRole']) && $_SESSION['UserRole'] !== 'Super Admin') {

            if(!empty($this->session->userdata('userPermission')) && !in_array('database/publishers',$this->session->userdata('userPermission'))){ 

                redirect('dashboard');

            }

        }

        

		$SISA_ID=$this->session->userdata('LoginID');

		if($SISA_ID){		

			$data['PageTitle']='Merchant Add';

			$data['side_menu']='publisher';

			$this->load->view('publishers/merchants_add');  

		}else{

			return redirect('/'); 

		}

	}

    public function submitMerchant() {

        $SISA_ID = $this->session->userdata('LoginID');

        if ($SISA_ID) {        

            $publisher_id = $_POST['publisher_id'];

            if (empty($_POST['email']) || empty($_POST['publication_name']) || empty($_POST['vendor_name']) || empty($_POST['commision_percent']) || empty($_POST['phone_no'])) {

                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

                exit;

            } elseif (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email"])) {

                echo json_encode(array('flag' => 0, 'msg' => "Please enter a valid Email address."));

                exit;

            } elseif (($this->PublisherModel->checkEmailidExit($_POST["email"])) != 0 && $publisher_id == '') {

                echo json_encode(array('flag' => 0, 'msg' => "Email id already exist."));

                exit;

            } else {

                // Handle image upload if provided

                $shop_image = "";

                if (isset($_FILES['shop_image']) && $_FILES['shop_image']['name'] != "") {

                    $config['upload_path'] = FCPATH . 'public/images/shop_images/';

                    $config['allowed_types'] = 'jpg|jpeg|png|gif';

                    $config['max_size'] = 2048; // 2MB

                    $config['file_name'] = time() . '_' . basename($_FILES['shop_image']['name']);



                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('shop_image')) {

                        $uploadData = $this->upload->data();

                        $shop_image = $uploadData['file_name'];

                    } else {

                        // Optional: log error but don't stop process

                        log_message('error', 'Shop image upload failed: ' . $this->upload->display_errors());

                    }

                }
                // Fetch old merchant data (only if editing/updating)
                $merchant = null;
                if (!empty($publisher_id)) {
                    $merchant = $this->PublisherModel->getMerchantById($publisher_id); 
                }
                // echo "<pre>";
                // print_r($merchant);die;

                // --- Delivery Policy ---
                $delivery_policy = $merchant->delivery_policy ?? null; // keep old value if exists
                if (!empty($_FILES['delivery_policy']['name'])) {
                    $config['upload_path']   = SIS_SERVER_PATH . '/' . 'uploads/delivery_policy/';
                    $config['allowed_types'] = 'doc|docx|jpg|jpeg|png|pdf|xlsx';
                    $config['max_size']      = 5120;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('delivery_policy')) {
                        $uploadData      = $this->upload->data();
                        $delivery_policy = $uploadData['file_name']; // overwrite only if new uploaded
                    } else {
                        $arrResponse = array('status' => 400, 'message' => $this->upload->display_errors());
                        echo json_encode($arrResponse);
                        exit;
                    }
                }

                // --- Return Policy ---
                $return_policy = $merchant->return_policy ?? null;
                if (!empty($_FILES['return_policy']['name'])) {
                    $config['upload_path']   = SIS_SERVER_PATH . '/' . 'uploads/return_policy/';
                    $config['allowed_types'] = 'doc|docx|jpg|jpeg|png|pdf|xlsx';
                    $config['max_size']      = 5120;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('return_policy')) {
                        $uploadData      = $this->upload->data();
                        $return_policy = $uploadData['file_name']; // overwrite only if new uploaded
                    } else {
                        $arrResponse = array('status' => 400, 'message' => $this->upload->display_errors());
                        echo json_encode($arrResponse);
                        exit;
                    }
                }

                // --- Refund Policy ---
                $refund_policy = $merchant->refund_policy ?? null;
                if (!empty($_FILES['refund_policy']['name'])) {
                    $config['upload_path']   = SIS_SERVER_PATH . '/' . 'uploads/refund_policy/';
                    $config['allowed_types'] = 'doc|docx|jpg|jpeg|png|pdf|xlsx';
                    $config['max_size']      = 5120;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('refund_policy')) {
                        $uploadData      = $this->upload->data();
                        $refund_policy = $uploadData['file_name']; // overwrite only if new uploaded
                    } else {
                        $arrResponse = array('status' => 400, 'message' => $this->upload->display_errors());
                        echo json_encode($arrResponse);
                        exit;
                    }
                }

                // --- Banner Image ---
                $banner_img = $merchant->banner_img ?? null;
                if (!empty($_FILES['banner_img']['name'])) {
                    $config['upload_path']   = SIS_SERVER_PATH . '/' . 'uploads/banner_img/';
                    $config['allowed_types'] = 'doc|docx|jpg|jpeg|png|pdf|xlsx';
                    $config['max_size']      = 5120;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('banner_img')) {
                        $uploadData      = $this->upload->data();
                        $banner_img = $uploadData['file_name']; // overwrite only if new uploaded
                    } else {
                        $arrResponse = array('status' => 400, 'message' => $this->upload->display_errors());
                        echo json_encode($arrResponse);
                        exit;
                    }
                }



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

                        $updateData = array();

                        if ($isPasswordChecked == 'check') {

                            $hashPassword = md5($_POST["password"]);

                            $updateData = array(

                                'email'             => $_POST['email'],

                                'password'          => $hashPassword,

                                'publication_name'  => $_POST['publication_name'],

                                'vendor_name'       => $_POST['vendor_name'],

                                'merchant_cat'       => $_POST['merchant_cat'],

                                'shipment_type'       => $_POST['shipment_type'],

                                'commision_percent' => $_POST['commision_percent'],

                                'vat_status'             => $_POST['vat_status'],

                                'vat_no'                 => $_POST['vat_no'],

                                'brn_no'                 => $_POST['brn_no'],

                                'default_vat_percentage' => $_POST['default_vat_percentage'],

                                'phone_no'          => $_POST['phone_no'],

                                'landline_no'             => $_POST['landline_no'],

                                'description'       => $_POST['description'],

                                'company_name'       => $_POST['company_name'],

                                'location'       => $_POST['location'],

                                'company_address'       => $_POST['company_address'],

                                'state'       => $_POST['state'],

                                'city'       => $_POST['city'],

                                'zipcode'       => $_POST['zipcode'],

                                'status'            => $_POST['status'],

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR'],

                            );

                        } else {

                            $updateData = array(

                                'email'             => $_POST['email'],

                                'publication_name'  => $_POST['publication_name'],

                                'vendor_name'       => $_POST['vendor_name'],

                                'merchant_cat'       => $_POST['merchant_cat'],

                                'shipment_type'       => $_POST['shipment_type'],

                                'commision_percent' => $_POST['commision_percent'],

                                'vat_status'             => $_POST['vat_status'],

                                'vat_no'                 => $_POST['vat_no'],

                                'brn_no'                 => $_POST['brn_no'],

                                'default_vat_percentage' => $_POST['default_vat_percentage'],

                                'phone_no'          => $_POST['phone_no'],

                                'landline_no'             => $_POST['landline_no'],

                                'description'       => $_POST['description'],

                                'company_name'       => $_POST['company_name'],

                                'location'       => $_POST['location'],

                                'company_address'       => $_POST['company_address'],

                                'state'       => $_POST['state'],

                                'city'       => $_POST['city'],

                                'zipcode'       => $_POST['zipcode'],

                                'status'            => $_POST['status'],

                                'updated_at'        => strtotime(date('Y-m-d H:i:s')),

                                'ip'                => $_SERVER['REMOTE_ADDR'],

                            );

                        }

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


                        if (!empty($_FILES['delivery_policy']['name'])) {
                            $updateData['delivery_policy'] = $delivery_policy;
                        }
                        if (!empty($_FILES['return_policy']['name'])) {
                            $updateData['return_policy'] = $return_policy;
                        }
                        if (!empty($_FILES['refund_policy']['name'])) {
                            $updateData['refund_policy'] = $refund_policy;
                        }
                        if (!empty($_FILES['banner_img']['name'])) {
                            $updateData['banner_img'] = $banner_img;
                        }

                        // Add shop image if uploaded

                        if ($shop_image != "") {

                            $updateData['shop_image'] = $shop_image;

                        }
                        // -----------------------------
                       // Build the full address string
                        $full_address = trim(
                    $_POST['company_address'] . ' ' .
                            (!empty($_POST['location']) ? $_POST['location'] . ' ' : '') .
                            $_POST['city'] . ' ' .
                            $_POST['state'] . ' ' .
                            $_POST['zipcode'] . ' ' .
                            (isset($_POST['country']) ? $_POST['country'] : '')
                        );

                        // Encode for URL
                        $encoded_address = urlencode($full_address);

                        // Google Maps API
                        $apiKey = 'AIzaSyAH2XRKr0rfw3h4z8ZYa2P4YiuhVhdKCZ0'; // Replace with your real key
                        $geocodeURL = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&key={$apiKey}";

                        $response = file_get_contents($geocodeURL);
                        $responseData = json_decode($response, true);

                        if (!empty($responseData['results'][0]['geometry']['location'])) {
                            $latitude  = $responseData['results'][0]['geometry']['location']['lat'];
                            $longitude = $responseData['results'][0]['geometry']['location']['lng'];
                            $updateData['latitude']  = $latitude;
                            $updateData['longitude'] = $longitude;
                        } else {
                            $updateData['latitude']  = null;
                            $updateData['longitude'] = null;
                        }
                        // echo "<pre>";
                        // print_r($updateData);die;

                        



                        $is_success = $this->PublisherModel->update_publishers($updateData, $publisher_id, $update_publisher_payment_details_data);

                        if ($is_success) {

                            $url = base_url() . 'PublisherController/editMerchant/'. $publisher_id;

            

                            echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated", "url" => $url));

                            exit;

                        } else {

                            echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));

                            exit;

                        }

                    }

                } else {

                    // Add publisher

                    $hashPassword = md5($_POST["password"]);

                    $insertData = array(

                        'email'             => $_POST['email'],

                        'password'          => $hashPassword,

                        'publication_name'  => $_POST['publication_name'],

                        'vendor_name'       => $_POST['vendor_name'],

                        'commision_percent' => $_POST['commision_percent'],

                        'phone_no'          => $_POST['phone_no'],

                        'description'       => $_POST['description'],

                        'status'            => $_POST['status'],

                        'remove_flag'       => 0,

                        'created_by'        => $SISA_ID,

                        'created_at'        => strtotime(date('Y-m-d H:i:s')),

                        'ip'                => $_SERVER['REMOTE_ADDR']

                    );



                    // Add shop image if uploaded

                    if ($shop_image != "") {

                        $insertData['shop_image'] = $shop_image;

                    }



                    $is_success = $this->PublisherModel->insert_publishers($insertData);

                    if ($is_success) {

                        $url = base_url() . 'publishers';

                        echo json_encode(array('flag' => 1, 'msg' => "Successfully Added", "url" => $url));

                        exit;

                    } else {

                        echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));

                        exit;

                    }

                }

            }



        } else {

            return redirect('/');

        }

    }



    public function editMerchant($publisherId)

	{

		$SISA_ID=$this->session->userdata('LoginID');

		if($SISA_ID){	

			if($publisherId){

				$data['publisher'] = $this->PublisherModel->get_publisher_detail($publisherId);

                $data['publisher_payment_details'] = $this->PublisherModel->get_publisher_payment_details($publisherId);

				$data['state'] = $this->PublisherModel->get_states();

				$data['city'] = $this->PublisherModel->get_city();



				$data['PageTitle']='Merchant Edit';

				$data['side_menu']='publisher';

				$this->load->view('publishers/merchants_edit',$data);  

			}else{

				return redirect('/'); 

			}

		}else{

			return redirect('/');

		}

	}



    function deletePublisher()

	{

        $publisherId = $_POST['id'];

        $is_success = $this->PublisherModel->delete_publishers($publisherId);

        if($is_success){

            echo 'success';

            exit;

        }

        else{

            echo 'error';

            exit;

        }

	}

}



?>