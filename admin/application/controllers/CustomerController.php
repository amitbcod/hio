<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CustomerController extends CI_Controller

{

	/**

	 * Index Page for this controller.

	 *

	 * Maps to the following URL

	 * 		http://example.com/index.php/welcome

	 *	- or -

	 * 		http://example.com/index.php/welcome/index

	 *	- or -

	 * Since this controller is set as the default controller in

	 * config/routes.php, it's displayed at http://example.com/

	 *

	 * So any other public methods not prefixed with an underscore will

	 * map to /index.php/welcome/<method_name>

	 * @see https://codeigniter.com/user_guide/general/urls.html

	 */

	function __construct()

	{

		parent::__construct();

		$this->load->model('CommonModel');

		$this->load->model('UserModel');

		$this->load->model('CustomerModel');

		$this->load->model('InvoicingModel');

		//$this->load->model('ManagerModel');

		$this->load->helper('url');

		// $this->load->model('NotificationModel');



		if (!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] == '') {

			redirect(BASE_URL);

		}

	}



	public function index()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/customers_type', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['side_menu'] = 'webShop';

		$data['customer_type_details'] = $this->CustomerModel->get_customer_type_details();

		$this->load->view('customer/customer_type.php', $data);

	}



	public function create_customer_type()

	{

		if (isset($_POST) && !empty($_POST)) {

			$time = time();

			if (empty($_POST['customer_type'])) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

				exit;

			} else {

				$insert_array = array(

					"name" => $_POST['customer_type'],

					"created_at" => $time,

					"created_by" =>	$_SESSION['LoginID'],

					"ip" => $_SERVER['REMOTE_ADDR']

				);

				$insert_customer_type = $this->CustomerModel->add_customer_type($insert_array);

				if ($insert_customer_type) {

					echo json_encode(array("flag" => 1, "status" => "200", "msg" => "Success"));

					exit();

				} else {

					echo json_encode(array("flag" => 0, "status" => "204", "msg" => "Adding new Customer type failed"));

					exit();

				}

			}

		} else {

			echo json_encode(array("flag" => 0, "status" => "204", "msg" => "Please Post Data"));

			exit();

		}

	}





	public function customer_type_details($type_id)

	{

		if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/customers_type', $this->session->userdata('userPermission'))) {

			redirect(base_url('dashboard'));

		}

		$data['side_menu'] = 'webShop';

		$data['type_details'] = $this->CustomerModel->get_single_customer_type_details($type_id);

		$customerType = $this->CustomerModel->get_single_customer_type_details($type_id);

		if ($customerType == '') {

			redirect('dashboard');

		}

		$data['customers_by_type'] = $this->CustomerModel->get_all_customer_by_type($type_id);

		$data['salesrule_info'] = $this->CustomerModel->get_all_salesrule_by_cust_type($type_id);





		// echo "<pre>";print_r($data['salesrule_info']);

		// die();

		$this->load->view('customer/customer_type_details.php', $data);

	}



	public function update_type_details($type_id = false)

	{

		if (isset($_POST) && !empty($_POST)) {

			$time = time();

			if (empty($_POST['customer_type_val'])) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

				exit;

			} else if (!$type_id) {

				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));

				exit;

			} else {

				$update_array = array(

					"name" => $_POST['customer_type_val'],

					"updated_at" => $time

				);

				$update_customer_type = $this->CustomerModel->update_customer_type($update_array, $type_id);

				if ($update_customer_type) {

					echo json_encode(array("flag" => 1, "status" => "200", "msg" => "Success"));

					exit();

				} else {

					echo json_encode(array("flag" => 0, "status" => "204", "msg" => "Updating  Customer type failed"));

					exit();

				}

			}

		} else {

			echo json_encode(array("flag" => 0, "status" => "204", "msg" => "Please Post Data"));

			exit();

		}

	}

	//***

	public function customers()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/customers/read', $this->session->userdata('userPermission'))) {

				// redirect('dashboard');

			}

		}



		$data['PageTitle'] = 'webShop - Customers';

		$data['side_menu'] = 'webShop';

		//$data['customer_listing'] = $this->CustomerModel->get_all_customers();

		// echo "<pre>";print_r($data);

		// die();

		$this->load->view('customer/webshop_customers_listing', $data);

	}



	public function add_customer()

	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {

			if (!empty($this->session->userdata('userPermission')) && !in_array('webshop/customers/write', $this->session->userdata('userPermission'))) {

				redirect('dashboard');

			}

		}



		$data['PageTitle'] = 'webShop - Add Customer';

		$data['side_menu'] = 'webShop';

		$data['customer_types'] =  $this->CommonModel->get_customer_types();

		$restricted_access = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'restricted_access'), 'value');

		$data['restricted_access'] = $restricted_access->value;

		$data['stateList'] =  $this->CommonModel->get_states_in();

		$data['country_list'] = $this->CommonModel->get_countries();

		$this->load->view('customer/add_customer', $data);

	}



	public function save_customer()

	{

		if (isset($_POST)) {

			$loginId = $this->session->userdata('LoginID');

			if (isset($_POST['email'])) {

				$customer_details = $this->CustomerModel->get_customer_details_by_email($_POST['email']);

			}

			if (isset($_POST['dob'])) {

				$date = date_create($_POST['dob']);

			}



			$state = '';

			if (isset($_POST['country'])) {

				if ($_POST['country'] == 'IN') {

					$state = isset($_POST['state_dp']) ? $_POST['state_dp'] : '';

				} else {

					$state = isset($_POST['state']) ? $_POST['state'] : '';

				}

			}



			if (empty($customer_details)) {

				$insertdata = array(

					'first_name' => isset($_POST['first_name']) ? $_POST['first_name'] : '',

					'last_name' => isset($_POST['last_name']) ? $_POST['last_name'] : '',

					'email_id' => isset($_POST['email']) ? $_POST['email'] : '',

					'password' => isset($_POST['password']) ? md5($_POST['password']) : '',

					'mobile_no' => isset($_POST['mobile_no']) ? $_POST['mobile_no'] : '',

					'gender' => isset($_POST['gender']) ? $_POST['gender'] : '',

					'country_code' => isset($_POST['country']) ? $_POST['country'] : '',

					'dob' =>  isset($date) ? date_format($date, "Y-m-d") : '',

					'company_name' => isset($_POST['company_name']) ? $_POST['company_name'] : '',

					'gst_no' => isset($_POST['GST_no']) ? $_POST['GST_no'] : '',

					'customer_type_id' => isset($_POST['customer_type_id']) ? $_POST['customer_type_id'] : '',

					'access_prelanch_product' => isset($_POST['access_prelanch_product']) ? 1 : 0,

					'status' => 1,

					'created_by' => 0,

					'created_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);

				$customer_id = $this->CustomerModel->insertData('customers', $insertdata);

				if (isset($customer_id)) {

					$address_data = array(

						'customer_id' => $customer_id,

						'first_name' => isset($_POST['first_name']) ? $_POST['first_name'] : '',

						'last_name' => isset($_POST['last_name']) ? $_POST['last_name'] : '',

						'mobile_no' => isset($_POST['mobile_no']) ? $_POST['mobile_no'] : '',

						'address_line1' => isset($_POST['address_line1']) ? $_POST['address_line1'] : '',

						'address_line2' => isset($_POST['address_line2']) ? $_POST['address_line2'] : '',

						'city' => isset($_POST['city']) ? $_POST['city'] : '',

						'state' => $state,

						'country' => isset($_POST['country']) ? $_POST['country'] : '',

						'pincode' => isset($_POST['pincode']) ? $_POST['pincode'] : '',

						'company_name' => isset($_POST['company_name']) ? $_POST['company_name'] : '',

						'vat_no' => isset($_POST['GST_no']) ? $_POST['GST_no'] : '',

						'is_default' => 1,

						'created_at' => time(),

						'ip' => $_SERVER['REMOTE_ADDR']

					);
					$full_address = $address_data['address_line1'] . ' ' . $address_data['address_line2'] . ' ' .
									$address_data['city'] . ' ' . $address_data['state'] . ' ' .
									$address_data['country'] . ' ' . $address_data['pincode'];

					$encoded_address = urlencode($full_address);
					$apiKey = 'AIzaSyAH2XRKr0rfw3h4z8ZYa2P4YiuhVhdKCZ0'; // Replace with your valid Google API Key
					$geocodeURL = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&key={$apiKey}";

					// Safer to use cURL instead of file_get_contents
					$response = file_get_contents($geocodeURL);
					$responseData = json_decode($response, true);

					if (!empty($responseData['results'][0]['geometry']['location'])) {
						$latitude  = $responseData['results'][0]['geometry']['location']['lat'];
						$longitude = $responseData['results'][0]['geometry']['location']['lng'];
						$address_data['latitude']  = $latitude;
						$address_data['longitude'] = $longitude;
					} else {
						$address_data['latitude']  = null;
						$address_data['longitude'] = null;
					}
					$customers_address_id = $this->CustomerModel->insertData('customers_address', $address_data);



					//  if(isset($customers_address_id)  && isset($customer_id))

					//  {

					//  	$shop_id =	$this->session->userdata('ShopID');

					//  	$shop_owner=$this->CommonModel->getShopOwnerData($shop_id);

					//  	$webshop_details=$this->CommonModel->get_webshop_details($shop_id);

					//  	$shop_name='BEEPZS05';

					//  	$webshopName = $shop_owner->org_shop_name;

					// 	$webshopName = 'BEEPZS05';

					//  	if($webshopName!=false){

					// 		$webshop_name = $webshopName;

					// 	}else{

					// 		$webshop_name = '';

					// 	}

					// 	$site_logo = '';

					// 	if(isset($webshop_details)){

					// 	 $shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);

					// 	}

					// 	else{

					// 		$shop_logo = '';

					// 	}

					// 	$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';

					// 	$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';

					// 	$customer_email = isset($_POST['email']) ? $_POST['email'] : '';

					// 	$customer_password = isset($_POST['password']) ? $_POST['password'] : '';

					// 	$emailTo= isset($_POST['email']) ? $_POST['email'] : '' ;

					// 	$name = $first_name.' '.$last_name;



					// 	$identifier = "customer-register-successful-admin";

					// 	$TempVars=array('##CUSTOMERNAME##','##WEBSHOPNAME##','##CUSTOMEREMAIL##','##CUSTOMERPASSWORD##');

					// 	$DynamicVars=array($name, $webshop_name,$customer_email, $customer_password);

					// 	$burl= base_url();

					//  	$shop_logo = get_s3_url($shop_logo, $shop_id);

					// 	$site_logo =  '<a href="'.getWebsiteUrl($shop_id,$burl).'" style="color:#1E7EC8;">

					// 		<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />

					// 	</a>';

					//  	 $shop_logo = '';





					// 	$language_code = isset($_POST['lang_code']) ? $_POST['lang_code'] : '';



					// 	$lang_code='';

					// 	if($language_code !='')

					// 	{

					// 		$languageData = $this->Multi_Languages_Model->getSingleDataByID('multi_languages',array('code'=>$language_code), '');

					// 		$is_default_language=isset($languageData) ? $languageData->is_default_language : '';

					// 		if($is_default_language == 0){

					// 		$lang_code=$language_code;

					// 		}

					// 	}



					// 	$CommonVars=array($site_logo, $shop_name);

					// 	if(isset($identifier)){

					// 		$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($identifier,$shop_id);

					// 		if($emailSendStatusFlag==1){

					// 			$send_email=$this->CustomerModel->sendCommonHTMLEmail($emailTo,$identifier,$TempVars,$DynamicVars,$webshop_name,$CommonVars,$lang_code);

					// 		}

					// 	}

					//  }

					$redirect = base_url('customers');

					echo json_encode(array('flag' => 1, 'msg' => "Success", 'redirect' => $redirect));

					exit();

				} else {

					echo json_encode(array('flag' => 0, 'msg' => "Nothing to Insert!"));

					exit;

				}

			} else {

				$redirect = base_url('add-customer');

				echo json_encode(array('flag' => 0, 'msg' => "User already registered with this email address", 'redirect' => $redirect));

				exit;

			}

		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Nothing to Insert!"));

			exit;

		}

	}



	public function customer_details($customer_id)

	{

		$data['PageTitle'] = 'webShop - Customers Details';

		$data['side_menu'] = 'webShop';

		$data['customer_id'] = $customer_id;

		// print_r($customer_id);die();



		$CustomerID = $this->CustomerModel->get_single_customer_details($customer_id);



		$data['customer_details'] = $this->CustomerModel->get_customer_details($customer_id);

		//$data['account_manager_details'] = $this->ManagerModel->get_account_manager_details();

		$shop_id =	$this->session->userdata('ShopID');

		// $ShopData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'currency_symbol,currency_code,org_website_address');

		//$data['org_website_address']= $ShopData->org_website_address;

		//$currency_symbol=(isset($ShopData->currency_symbol))?$ShopData->currency_symbol:$ShopData->currency_code;

		$data['currency_symbol'] = 'INR';

		$data['customer_types'] = $this->CustomerModel->get_customer_type_details();

		$data['customer_order_details'] = $this->CustomerModel->get_Customer_order_details($customer_id);

		$data['customer_country'] = $this->CommonModel->get_country_name_by_code($data['customer_details'][0]['country_code']);

		$data['customer_address_book'] = $this->CustomerModel->get_Customer_AddressBook($customer_id);

		$restricted_access = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'restricted_access'), 'value');

		$data['restricted_access'] = $restricted_access->value;

		$data['InvoiceList'] = $this->CustomerModel->getinvoicesbycustomerId($customer_id);

		$data['InvoiceGenerateList'] = $this->InvoicingModel->get_Customer_invoicing_list($customer_id);

		$data['customerId'] = $customer_id;

		$data['customers_info'] = $this->CommonModel->get_customers_info();

		$data['webshopcust_def_inv_altemail'] = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');

		$data['customer_return_order_list'] = $this->CustomerModel->getCustomerOrderReturnDataById($customer_id);

		$this->load->view('customer/webshop-customer-details-order.php', $data);

	}



	public function update_customer_detail()

	{

		if (isset($_POST['submit']) && !empty($_POST['submit'])) {

			$customer_type = $this->input->post('customer_type');

			$customer_id = $this->input->post('text_hidden');





			if (isset($_POST['access_prelaunch']) && $_POST['access_prelaunch'] == 'on') {

				$access_prelanch_product = 1;

			} else {

				$access_prelanch_product = 0;

			}



			if (isset($_POST['allow_catlog_builder']) && $_POST['allow_catlog_builder'] == 'on') {

				$allow_catlog_builder = 1;

			} else {

				$allow_catlog_builder = 0;

			}



			$update_array = array(

				"customer_type_id" => $customer_type,



				"access_prelanch_product" => $access_prelanch_product,

				"allow_catlog_builder" => $allow_catlog_builder



			);

			$update_customer_detail = $this->CustomerModel->update_customer_detail($update_array, $customer_id);



			// $this->load->view('customer/webshop-customer-details-order.php',$data);

			redirect(base_url() . 'CustomerController/customer_details/' . $customer_id, 'refresh');

		}

	}



	public function getwebshopCustomerList()

	{

		if (isset($_POST)) {

			// print_r($_POST);

			// die();

			$search_param = array();

			// Shop, owner, created date - keyword

			if (!empty($_POST['search'])) {

				$search_param['keyword'] = $_POST['search'];

			}

			$data['customer_listing'] = $customer_listing = $this->CustomerModel->get_all_customers($search_param);

			// print_r($data );

			// die();

			$this->load->view('customer/webshopcustomerlist', $data);

		}

	}



	//***



	public function postwebshopCustomerInvoice()

	{

		// $seller_db= $this->seller_db->database;

		$LoginID = $_SESSION['LoginID'];

		$ShopID = $_SESSION['ShopID'];

		$ShopOwnerId = $_SESSION['ShopOwnerId'];



		if (isset($_POST['customerId'])) {

			$invoice = isset($_POST['invoice']) ? $_POST['invoice'] : '';

			$customer_id = isset($_POST['customerId']) ? $_POST['customerId'] : '';

			$webshopCustomerInvoiceData = $this->CustomerModel->getInvoiceBywebshopCustomerId($customer_id);

			if (isset($customer_id)) {

				$invDailyAmt = '';

				$invWeeklyAmt = '';

				$invMonthlyAmt = '';

				$invoice_to = $this->CommonModel->custom_filter_input($_POST['invoice_to']);

				$payment_term = $this->CommonModel->custom_filter_input($_POST['payment_term']);



				if (isset($invoice)) {

					$invoiceType = $invoice;

					if ($invoice == 2) {

						$invDailyAmt = $this->CommonModel->custom_filter_input($_POST['invDailyAmt']);

					} else if ($invoice == 3) {

						$invWeeklyAmt = $this->CommonModel->custom_filter_input($_POST['invWeeklyAmt']);

					} else if ($invoice == 4) {

						$invMonthlyAmt = $this->CommonModel->custom_filter_input($_POST['invMonthlyAmt']);

					}

				} else {

					$invoiceType = '0';

				}



				if ($invoice_to == '1') {

					$alternate_email = $this->CommonModel->custom_filter_input($_POST['alternate_email']);

				} else {

					$alternate_email = '';

				}

				$customerId = $this->CommonModel->custom_filter_input($_POST['customerId']);





				if (empty($webshopCustomerInvoiceData)) {

					$insertData = array(

						'customer_id' => $customerId,

						'invoice_type' => $invoiceType,

						'inv_daily_max_inv_amt' => $invDailyAmt,

						'inv_weekly_max_inv_amt' => $invWeeklyAmt,

						'inv_monthly_max_inv_amt' => $invMonthlyAmt,

						'invoice_to_type' => $invoice_to,

						'alternative_email_id' => $alternate_email,

						'payment_term' => $payment_term,

						'created_by' => $LoginID,

						'created_at' => strtotime(date('Y-m-d H:i:s')),

						// 'updated_at' 		=> ,

						'ip' => $_SERVER['REMOTE_ADDR']

					);

					$rowAffected = $this->seller_db->insert('customers_invoice', $insertData);

				} else {

					$updateData = array(

						'invoice_type' => $invoiceType,

						'inv_daily_max_inv_amt' => $invDailyAmt,

						'inv_weekly_max_inv_amt' => $invWeeklyAmt,

						'inv_monthly_max_inv_amt' => $invMonthlyAmt,

						'invoice_to_type' => $invoice_to,

						'alternative_email_id' => $alternate_email,

						'payment_term' => $payment_term,

						// 'created_by' => $LoginID,

						// 'created_at' => strtotime(date('Y-m-d H:i:s')),

						'updated_at' => strtotime(date('Y-m-d H:i:s'))

						// 'ip' =>$_SERVER['REMOTE_ADDR']



					);

					$this->seller_db->where(array('id' => $webshopCustomerInvoiceData->id));

					$rowAffected = $this->seller_db->update('customers_invoice', $updateData);

				}

				//}

			}

			//redirect(base_url()."b2b/customer/detail-invoice/".$shop_id);

			if ($rowAffected) {

				echo json_encode(array('flag' => 1, 'customer_id' => $customer_id, 'msg' => "Success",));

				exit();

			} else {

				echo json_encode(array('flag' => 0, 'customer_id' => $customer_id, 'msg' => "went something wrong!"));

				exit;

			}

		}

	}



	function OpenEditPersonalInfoPopup()

	{

		$data['customer_id'] = $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';

		$fbc_user_id	=	$this->session->userdata('LoginID');

		$shop_id		=	$this->session->userdata('ShopID');

		$data['customer_details'] = $this->CommonModel->getSingleShopDataByID('customers', array('id' => $customer_id), '*');

		// echo "<pre>" ; print_r($data['customer_details']);die();

		$restricted_access = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'restricted_access'), 'value');

		$data['restricted_access'] = $restricted_access->value;

		$data['stateList'] =  $this->CommonModel->get_states_in();

		$data['country_list'] = $this->CommonModel->get_countries();

		$View = $this->load->view('customer/edit_customer', $data, true);

		$this->output->set_output($View);

	}



	function OpenEditAddressPopup()

	{

		$data['customer_id'] = $customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';

		$data['address_id'] = $address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';

		$data['customer_address'] = $this->CommonModel->getSingleShopDataByID('customers_address', array('customer_id' => $customer_id, 'id' => $address_id), '*');

		$data['stateList'] =  $this->CommonModel->get_states_in();

		$data['country_list'] = $this->CommonModel->get_countries();

		$View = $this->load->view('customer/edit_customer_address', $data, true);

		$this->output->set_output($View);

	}



	public function update_customer_info()

	{

		if (isset($_POST)) {

			if (isset($_POST['dob'])) {

				$dob = date_create($_POST['dob']);

			}

			$first_name = $this->input->post('first_name');

			$last_name = $this->input->post('last_name');

			$company_name = $this->input->post('company_name');

			$GST_no = $this->input->post('GST_no');

			// $dob= $this->input->post('dob');

			$mobile_no = $this->input->post('mobile_no');

			$gender = $this->input->post('gender');

			$country = $this->input->post('country');

			$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';



			$update_array = array(

				"first_name" => $first_name,

				"last_name" => $last_name,

				"company_name" => $company_name,

				"gst_no" => $GST_no,

				"dob" => isset($dob) ? date_format($dob, "Y-m-d") : '',

				"mobile_no" => $mobile_no,

				"gender" => $gender,

				"country_code" => $country,

			);



			$rowAffected = $this->CustomerModel->update_customer_detail($update_array, $customer_id);

		}

		if (isset($_POST['company_name']) || isset($_POST['gst_no'])) {

			$update_add = array(

				"company_name" => $company_name,

				"vat_no" => $GST_no,

			);

			$update_customer_com_get = $this->CustomerModel->update_customer_deault_billaddr($update_add, $customer_id);

		}



		if ($rowAffected) {

			$redirect = base_url('customer-details/' . $customer_id);

			echo json_encode(array('flag' => 1, 'customer_id' => $customer_id, 'msg' => "Success", 'redirect' => $redirect));

			exit();

		} else {

			$redirect = base_url('customer-details/' . $customer_id);

			echo json_encode(array('flag' => 0, 'customer_id' => $customer_id, 'msg' => "went something wrong!", 'redirect' => $redirect));

			exit;

		}

	}



	public function update_customer_address()

	{

		if (isset($_POST)) {

			$state = '';

			if (isset($_POST['country'])) {

				if ($_POST['country'] == 'IN') {

					$state = isset($_POST['state_dp']) ? $_POST['state_dp'] : '';

				} else {

					$state = isset($_POST['state']) ? $_POST['state'] : '';

				}

			}

			$first_name = $this->input->post('first_name');

			$last_name = $this->input->post('last_name');

			$address_line1 = $this->input->post('address_line1');

			$address_line2 = $this->input->post('address_line2');

			$city = $this->input->post('city');

			$country = $this->input->post('country');

			$pincode = $this->input->post('pincode');

			$mobile_no = $this->input->post('mobile_no');



			$customer_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : '';

			$address_id = isset($_POST['address_id']) ? $_POST['address_id'] : '';

			$update_array = array(

				"first_name" => $first_name,

				"last_name" => $last_name,

				"address_line1" => $address_line1,

				"address_line2" => $address_line2,

				"city" => $city,

				"country" => $country,

				"pincode" => $pincode,

				"state" => $state,

				"mobile_no" => $mobile_no,

			);
			$full_address = $update_array['address_line1'] . ' ' . $update_array['address_line2'] . ' ' .
				$update_array['city'] . ' ' . $update_array['state'] . ' ' .
				$update_array['country'] . ' ' . $update_array['pincode'];

			$encoded_address = urlencode($full_address);
			$apiKey = 'AIzaSyAH2XRKr0rfw3h4z8ZYa2P4YiuhVhdKCZ0'; // Replace with your valid Google API Key
			$geocodeURL = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&key={$apiKey}";

			// Safer to use cURL instead of file_get_contents
			$response = file_get_contents($geocodeURL);
			$responseData = json_decode($response, true);

			if (!empty($responseData['results'][0]['geometry']['location'])) {
				$latitude  = $responseData['results'][0]['geometry']['location']['lat'];
				$longitude = $responseData['results'][0]['geometry']['location']['lng'];
				$update_array['latitude']  = $latitude;
				$update_array['longitude'] = $longitude;
			} else {
				$update_array['latitude']  = null;
				$update_array['longitude'] = null;
			}


			$rowAffected = $this->CustomerModel->update_customer_address($update_array, $customer_id, $address_id);

		}



		if ($rowAffected) {

			$redirect = base_url('customer-details/' . $customer_id);

			echo json_encode(array('flag' => 1, 'customer_id' => $customer_id, 'msg' => "Success", 'redirect' => $redirect));

			exit();

		} else {

			$redirect = base_url('customer-details/' . $customer_id);

			echo json_encode(array('flag' => 0, 'customer_id' => $customer_id, 'msg' => "went something wrong!", 'redirect' => $redirect));

			exit;

		}

	}



	public function loadcustomerssajax()

	{



		$customer_listing = $this->CustomerModel->get_datatables_customer_details();

		// echo "<pre>";

		// print_r($customer_listing);

		// die();

		$data = array();



		foreach ($customer_listing as $readData) {



			$row  = array();

			$row[] = $readData->id;

			$row[] = $readData->first_name . ' ' . $readData->last_name;



			$customer_details = $this->CustomerModel->get_customer_details($readData->id);

			$lastrow =  (count($customer_details) - 1);

			if (isset($customer_details[$lastrow]['created_at']) && $customer_details[$lastrow]['created_at'] != '') {

				$row[] = date("d-m-y", $customer_details[$lastrow]['created_at']);

			}



			$row[] = $readData->email_id;

			$row[] = $readData->city . ', ' . $readData->state;



			$view_url = base_url() . 'customer-details/' . $readData->id;

			$row[] = '<a class="link-purple " target="_blank" href="' . $view_url . '">View</a>';



			$data[] = $row;

		}



		$output = array(

			"draw" => $_POST['draw'],

			"recordsTotal" => $this->CustomerModel->countcustomersrecord(),

			"recordsFiltered" => $this->CustomerModel->countfiltercustomersrecord(),

			"data" => $data,

		);



		echo json_encode($output);

		exit;

	}

	public function faqs() {
		$faqs = $this->CommonModel->get_faqs();
		$data['faqs'] = $faqs; // Wrap in an array to pass to view
		// echo "<pre>";
		// print_r($data);
		// die();
		$this->load->view('faqs_list', $data);
	}

	public function faq_edit($id) {
		$data['faqs'] = $this->CommonModel->get_faqs_details($id);
		$this->load->view('edit_faq', $data);
	}
	public function update_faqs()
	{
		$id = $this->input->post('id');
		$answer = $this->input->post('answer');
		$status = $this->input->post('status');

		$postArr = [
			'answer' => $answer,
			'status' => $status,
			'updated_at' => strtotime(date('Y-m-d H:i:s')),
		];

		$this->db->where('id', $id);
		$updated = $this->db->update('faqs', $postArr);

		if($updated){
			$this->session->set_flashdata('success', "FAQ has been updated successfully.");
			redirect(base_url('faqs'));
		} else {
			$this->session->set_flashdata('error', "Failed to update FAQ. Please try again.");
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
	public function help_desk() {
		$data['help_desk'] = $this->CommonModel->get_help_desk();
		$this->load->view('help_desk_list', $data);
	}

	public function help_desk_edit($id) {
		$data['ticket'] = $this->CommonModel->get_help_desk_details($id);
		$customer_id = $data['ticket']['customer_id'];
		$data['orders'] = $this->CommonModel->get_customer_orders($customer_id);

		// 3. Get all products for the selected order
		$order_id = $data['ticket']['order_id'];
		$data['products'] = $this->CommonModel->get_order_products($order_id); // returns array of products

		// 4. Status labels for orders (if needed)
		$data['status_labels'] = [
			0 => 'Complete',
			1 => 'Pending',
			2 => 'Cancelled',
		];
		// echo "<pre>";
		// print_r($data);die;
		$this->load->view('edit_help_desk', $data);
	}
	public function update_help_desk()
	{
		$id = $this->input->post('id');
		$admin_reply = trim($this->input->post('admin_reply'));

		if (empty($admin_reply)) {
			$this->session->set_flashdata('error', "Reply cannot be empty.");
			redirect($_SERVER['HTTP_REFERER']);
			return;
		}

		// Get the original ticket details
		$ticket = $this->db->where('id', $id)->get('help_desk')->row();

		if (!$ticket) {
			$this->session->set_flashdata('error', "Ticket not found.");
			redirect($_SERVER['HTTP_REFERER']);
			return;
		}

		// Prepare a new record (duplicate main fields)
		$insertData = [
			'ticket_id'    => $ticket->ticket_id,
			'subject'      => $ticket->subject,
			'category'     => $ticket->category,
			'priority'     => $ticket->priority,
			'customer_id'  => $ticket->customer_id,
			'message'      => $ticket->message, // admin reply only
			'attachment'   => $ticket->attachment, // optional, could allow admin to upload reply files too
			'order_id'     => $ticket->order_id,
			'products'     => $ticket->products,
			'admin_reply'  => $admin_reply,
			'status' => 1,
			'created_at'    => strtotime(date('Y-m-d H:i:s')),
            'updated_at'    => strtotime(date('Y-m-d H:i:s')),
			'ip'           => $_SERVER['REMOTE_ADDR'],
		];

		$inserted = $this->db->insert('help_desk', $insertData);

		if ($inserted) {
			// optionally, mark the original ticket as "open" or "replied"
			redirect(base_url('help_desk'));
			$this->session->set_flashdata('success', "Reply added successfully.");
		} else {
			$this->session->set_flashdata('error', "Failed to add reply. Please try again.");
		}

		redirect(base_url('help_desk/view/' . $id));
	}
	public function view($order_id, $product_id)
	{
		// Get all messages for this order + product
		$this->db->where('order_id', $order_id);
		$this->db->where('products', $product_id);
		$this->db->order_by('created_at', 'ASC');
		$help_desk_data = $this->db->get('help_desk')->result();

		// Get order details
		$order = $this->CommonModel->get_order_by_id($order_id);

		// Get product details
		$product = $this->CommonModel->get_product_by_order($order_id, $product_id);

		$data['help_desk_data'] = $help_desk_data;
		$data['order'] = $order;
		$data['product'] = $product;

		$this->load->view('help_desk_conversation', $data);
	}



	


}

