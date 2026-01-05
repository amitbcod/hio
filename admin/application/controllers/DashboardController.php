<?php
defined('BASEPATH') or exit('No direct script access allowed');
class DashboardController extends CI_Controller
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
		$this->load->model('DashboardModel');
		if (isset($_SESSION) && isset($_SESSION['ShopDatabaseName']) && $_SESSION['ShopDatabaseName'] != '') {
			$this->load->model('EmployeeModel');
		}

		if (!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] == '') {
			redirect(BASE_URL);
		}
	}

	public function index()
	{

		$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
		$data['PageTitle'] = 'Dashboard';
		$data['user_count'] = $this->CommonModel->get_data_count('adminusers');
		$data['publisher_count'] = $this->CommonModel->get_data_count('publisher');
		$data['customer_count'] = $this->CommonModel->get_data_count('customers');
		$data['product_count'] = $this->CommonModel->get_data_count('products');
		$data['daily_order_count'] = $this->CommonModel->get_daily_order_count();
		$data['daily_sale_count'] = $this->CommonModel->get_daily_sale_count();
		$data['daily_earnings_count'] = $this->CommonModel->get_daily_earnings_count();
		$data['weekly_sale_count'] = $this->CommonModel->get_weekly_sale_count();
		// echo "<pre>";
		// print_r($data['weekly_sale_count']);die;
		$data['monthly_sale_count'] = $this->CommonModel->get_monthly_sale_count();
		$data['yearly_sale_count'] = $this->CommonModel->get_yearly_sale_count();
		$get_sub = $this->CommonModel->getSalesOrderItems();

		$data['pending_merchants'] = $this->CommonModel->count_pending_publishers();
		$data['pending_products'] = $this->CommonModel->count_pending_products();

		// if ($get_sub) {

		// 	foreach ($get_sub as $key_sub => $val_Sub) {
		// 		$get_subscription_period = $this->CommonModel->get_sub_period($val_Sub['product_id']);
		// 		if ($get_subscription_period && $get_subscription_period['sub_time'] != '') {
		// 			$sub_start_time = strtotime('first day of +1 month', $val_Sub['created_at']);
		// 			$update_sub_start_time = $this->CommonModel->update_sub_start($val_Sub['item_id'],  $sub_start_time);
		// 			$sub_end_time = strtotime($get_subscription_period['sub_time'], $sub_start_time);
		// 			$update_sub_start_time = $this->CommonModel->update_sub_end($val_Sub['item_id'],  $sub_end_time);
		// 		}
		// 	}
		// }
		$data['get_subcription_data'] = $this->CommonModel->get_all_subscription();
		$this->load->view('dashboard.php', $data);
	}

	public function employee_role()
	{
		if (!empty($this->session->userdata('userPermission')) && !in_array('fbc_usermanagement/employee_role', $this->session->userdata('userPermission'))) {
			redirect(base_url('dashboard'));
		}
		$data['PageTitle'] = 'Employee Role';
		$data['empRole'] = $this->EmployeeModel->getEmpRoles();
		$this->load->view('employee_role', $data);
	}

	public function createRole()
	{
		if (!empty($this->session->userdata('userPermission')) && !in_array('fbc_usermanagement/employee_role', $this->session->userdata('userPermission'))) {
			redirect(base_url('dashboard'));
		}
		$roleId = $this->uri->segment(2);
		$data['PageTitle'] = 'Create Employee Role';
		$data['roleId'] = $roleId;
		$data['parentData'] = $this->EmployeeModel->getParentData();
		$data['singleRole'] = $this->EmployeeModel->getSingleRoleNameByID($roleId);
		$this->load->view('create_employee_role', $data);
	}

	public function CreateRoleResource()
	{
		if ($this->input->post('role_name') != "" && $this->input->post('resource_access') != "") {
			$roleId = $_POST['roleId'];
			$data['role_name'] = $this->input->post('role_name');
			$data['resource_access'] = $this->input->post('resource_access');
			$resource_checkbox['employee_resource'] = $this->input->post('myArray');
			if ($roleId > 0) {
				if ($_POST['resource_access'] == 0) {
					$this->EmployeeModel->deleteRole($roleId);
					$this->EmployeeModel->updateRole($roleId, $data);
					$redirect = base_url('employee_role');
					echo json_encode(array('flag' => 1, 'msg' => "Role Updated.", 'redirect' => $redirect));
					exit;
				} else {
					$this->EmployeeModel->updateRole($roleId, $data);
					$this->EmployeeModel->deleteRole($roleId);
					$result = $this->EmployeeModel->insertRoleResource($roleId, $resource_checkbox['employee_resource']);
					$redirect = base_url('employee_role');
					echo json_encode(array('flag' => 1, 'msg' => "Role Updated.", 'redirect' => $redirect));
					exit;
				}
			} else {
				$response = $this->EmployeeModel->insertRole($data);
				$result = $this->EmployeeModel->insertRoleResource($response, $resource_checkbox['employee_resource']);
				$redirect = base_url('employee_role');
				echo json_encode(array('flag' => 1, 'msg' => "New Role Created.", 'redirect' => $redirect));
				exit;
			}
		} else {
			echo json_encode(array('flag' => 0, 'msg' => "Please fill all requrired fields"));
			exit();
		}
	}

	public function employee_delete_role()
	{
		$id = $this->input->post('id');
		$result = $this->EmployeeModel->delete_role($id);
		if ($result == true) {
			echo json_encode(array('flag' => 1, 'msg' => "Role Deleted Successfully."));
			exit;
		} else {
			echo json_encode(array('flag' => 0, 'msg' => "Role UnDeleted Successfully."));
			exit;
		}
	}

	public function update_user_details()
	{
		if (isset($_POST)) {
			if (empty($_POST['owner_name']) || empty($_POST['email']) || empty($_POST['mobile_no']) || empty($_POST['company_name'])) {
				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
				exit;
			} else if (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email"])) {
				echo json_encode(array('flag' => 0, 'msg' => "Please enter a valid Email address."));
				exit;
			} else {
				$bill_state = '';
				if ($_POST['bill_country'] == 'IN') {
					$bill_state = $_POST['bill_state_dp'];
				} else {
					$bill_state = $_POST['bill_state'];
				}
				$ship_state = '';
				if ($_POST['ship_country'] == 'IN') {
					$ship_state = $_POST['ship_state_dp'];
				} else {
					$ship_state = $_POST['ship_state'];
				}
				if ($_POST['bill_country'] == 'IN' && $_POST['ship_country'] == 'IN') {
					if ($bill_state != $ship_state) {
						echo json_encode(array('flag' => 0, 'msg' => "Billing state and Shipping State should be same for Country India."));
						exit;
					}
				}
				$update_fbc_users = array(
					'owner_name' => $_POST['owner_name'],
					'mobile_no' => $_POST['mobile_no'],
					'email' => $_POST['email']
				);
				$update_fbc_users_shop = array(
					"org_shop_name" =>  $_POST['webshop_name'],
					"company_name" =>  $_POST['company_name'],
					"gst_no" =>  $_POST['gst_number'],
					"vat_n_translation" =>  $_POST['vat_n_translation'],
					"vat_percent_translation" =>  $_POST['vat_percent_translation'],
					"bill_address_line1" =>  $_POST['bill_address_line1'],
					"bill_address_line2" =>  $_POST['bill_address_line2'],
					"bill_city" =>  $_POST['bill_city'],
					"bill_state" =>  $bill_state,
					"bill_country" =>  $_POST['bill_country'],
					"bill_pincode" =>  $_POST['bill_pincode'],
					"ship_address_line1" =>  $_POST['ship_address_line1'],
					"ship_address_line2" =>  $_POST['ship_address_line2'],
					"ship_city" =>  $_POST['ship_city'],
					"ship_state" =>  $ship_state,
					"ship_country" =>  $_POST['ship_country'],
					"ship_pincode" =>  $_POST['ship_pincode'],
					"bank_name" =>  $_POST['bank_name'],
					"bank_branch" =>  $_POST['bank_branch'],
					"bank_ifsc" =>  $_POST['bank_ifsc'],
					"bank_acc_no" =>  $_POST['bank_acc_no'],
					"bank_acc_name" =>  $_POST['bank_acc_name'],
					"bic_swift"	=> $_POST['bic_swift'],
					"org_website_address" => $_POST['website_address']
				);
				$update_user_details = $this->UserModel->update_fbc_users($update_fbc_users);
				if ($update_user_details) {
					$update_user_shop_details = $this->UserModel->update_fbc_users_shop($update_fbc_users_shop);
					if ($update_user_shop_details) {
						echo json_encode(array('flag' => 1, "msg" => "Success"));
						exit();
					} else {
						echo json_encode(array('flag' => 0, "msg" => "User Shop Data was Not Updated"));
						exit();
					}
				} else {
					echo json_encode(array('flag' => 0, "msg" => "User Data was Not Updated"));
					exit();
				}
			}
		} else {
			echo json_encode(array('flag' => 0, "msg" => "Please Post Data"));
			exit();
		}
	}

	public function employee_details($fbc_user_id = false)
	{
		if (isset($fbc_user_id) && $fbc_user_id) {
			$data['fbc_user_id'] = $fbc_user_id;
			$data['emp_roles'] = $this->CommonModel->GetEmpRole();
			$data['emp_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);
			$data['emp_personal_details'] = $this->CommonModel->GetEmpByUserId($fbc_user_id);
		} else {
			$data['emp_roles'] = $this->CommonModel->GetEmpRole();
			$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		}
		$this->load->view('employee_details.php', $data);
	}

	public function insert_employee_details()
	{
		if (isset($_POST)) {
			$time = time();
			if (empty($_POST['emp_name']) || empty($_POST['emp_email']) || empty($_POST['emp_mobile'])) {
				echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
				exit;
			} else if (!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["emp_email"])) {
				echo json_encode(array('flag' => 0, 'msg' => "Please enter a valid Email address."));
				exit;
			} else {
				if (isset($_POST['action']) && $_POST['action'] == 'insert') {
					$user_details = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
					$check_email_exist = $this->CommonModel->GetUserByEmail($_POST['emp_email']);
					if (!$check_email_exist) {
						$hashPassword = password_hash($_POST['emp_password'], PASSWORD_DEFAULT);
						$insert_array_fbc_users = array(
							"email" => $_POST['emp_email'],
							"password" => $hashPassword,
							"parent_id" => $_POST['parent_id'],
							"owner_name" => $_POST['emp_name'],
							"mobile_no" => $_POST['emp_mobile'],
							"status" => "1",
							"created_by" => $_SESSION['LoginID'],
							"created_at" => $time,
							'ip' => $_SERVER['REMOTE_ADDR']
						);
						$insert_employee = $this->UserModel->insert_employee($insert_array_fbc_users, $_POST['identifier']);
						if ($insert_employee) {
							$get_employee_details = $this->CommonModel->GetUserByEmail($_POST['emp_email']);
							$fbc_users_emp_details = array(
								'fbc_user_id' => $get_employee_details['fbc_user_id'],
								'residential_address' => $_POST['emp_address'],
								'role_in_company' => $_POST['emp_role'],
								"created_by" => $_SESSION['LoginID'],
								"created_at" => $time
							);
							$insert_employee_details = $this->UserModel->insert_employee_details($fbc_users_emp_details);
							if ($insert_employee) {
								$redirect = BASE_URL . 'dashboard';
								echo json_encode(array("flag" => 1, "status" => "200", "msg" => "Success", "redirect" => $redirect));
								exit();
							} else {
								echo json_encode(array("flag" => 0, "status" => "205", "msg" => "Adding new Employee Details failed"));
								exit();
							}
						} else {
							echo json_encode(array("flag" => 0, "status" => "204", "msg" => "Adding new Employee failed"));
							exit();
						}
					} else {
						echo json_encode(array("flag" => 0, "status" => "203", "msg" => "Email Id already Exist"));
						exit();
					}
				} else if (isset($_POST['action']) && $_POST['action'] == 'update') {
					$update_array_fbc_users = array(
						"owner_name" => $_POST['emp_name'],
						"mobile_no" => $_POST['emp_mobile'],
						"updated_at" => $time,
						'ip' => $_SERVER['REMOTE_ADDR']
					);
					$update_employee = $this->UserModel->update_employee($update_array_fbc_users, $_POST['fbc_user_id']);
					if ($update_employee) {
						$update_fbc_users_emp_details = array(
							'residential_address' => $_POST['emp_address'],
							'role_in_company' => $_POST['emp_role'],
							'updated_at' => time()
						);
						$update_employee_details = $this->UserModel->update_employee_details($update_fbc_users_emp_details, $_POST['fbc_user_id']);
						if ($update_employee_details) {
							$redirect = BASE_URL . 'dashboard';
							echo json_encode(array("flag" => 1, "status" => "200", "msg" => "Updated Successfully", "redirect" => $redirect));
							exit();
						} else {
							echo json_encode(array("flag" => 0, "status" => "205", "msg" => "Updating new Employee Details failed"));
							exit();
						}
					} else {
						echo json_encode(array("flag" => 0, "status" => "206", "msg" => "Updating Employee  failed"));
						exit();
					}
				}
			}
		} else {
			echo json_encode(array("flag" => 0, "msg" => "Please Post Data"));
			exit();
		}
	}

	public function change_password($user_id = false)
	{
		if ($user_id) {
			$fbc_user_id = $user_id;
		}
		if (isset($_POST['old_password']) && isset($_POST['new_password'])) {
			$data['user_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);
			if (password_verify($_POST['old_password'], $data['user_details']->password)) {
				$hashPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
				$update_data = array(
					"password" => $hashPassword
				);
				$update_password = $this->UserModel->update_password($update_data, $fbc_user_id);
				if ($update_password) {
					echo json_encode(array("flag" => 1, "msg" => "Updated Successfully"));
					exit();
				} else {
					echo json_encode(array("flag" => 0, "msg" => "Updating employee  failed"));
					exit();
				}
			} else {
				echo json_encode(array("flag" => 0, "msg" => "Old password doesn't match"));
				exit();
			}
		}
	}

	public function change_email($user_id = false)
	{
		if ($user_id) {
			$fbc_user_id = $user_id;
		}
		if (isset($_POST['current_email']) && isset($_POST['new_email'])) {
			$data['user_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);
			if ($_POST['current_email'] === $data['user_details']->email) {
				if ($_POST['new_email'] != $data['user_details']->email) {
					$new_email = $_POST['new_email'];
					$email_exists = $this->UserModel->email_exists($new_email);
					if (!$email_exists) {
						$update_data = array(
							"email" => $new_email
						);
						$update_email = $this->UserModel->update_email($update_data, $fbc_user_id);
						if ($update_email) {
							$redirect = BASE_URL . 'dashboard';
							echo json_encode(array("flag" => 1, "msg" => "Updated Successfully"));
							exit();
						} else {
							echo json_encode(array("flag" => 0, "msg" => "Updating Email  failed"));
							exit();
						}
					} else {
						echo json_encode(array("flag" => 0, "msg" => "New Email Already Exists "));
						exit();
					}
				} else {
					echo json_encode(array("flag" => 0, "msg" => "Email Already Exists "));
					exit();
				}
			} else {
				echo json_encode(array("flag" => 0, "msg" => "Current Email doesn't match"));
				exit();
			}
		}
	}

	public function change_employee_status()
	{
		if (isset($_POST) && !empty($_POST)) {
			$change_employee_status = $this->UserModel->change_employee_status($_POST['status'], $_POST['fbc_usr_id']);
			if ($change_employee_status) {
				if ($_POST['status'] == 2) {
					echo json_encode(array("flag" => 1, "msg" => "Updated Successfully", "status" => '1'));
					exit();
				} else if ($_POST['status'] == 1) {
					echo json_encode(array("flag" => 1, "msg" => "Updated Successfully", "status" => '2'));
					exit();
				}
			} else {
				echo json_encode(array("flag" => 0, "msg" => "Updating employee  failed"));
				exit();
			}
		} else {
			echo json_encode(array("flag" => 0, "msg" => "Please Post Data"));
			exit();
		}
	}

	public function emp_change_password($user_id = false)
	{
		if ($user_id) {
			$fbc_user_id = $user_id;
		}
		if ($_SESSION['LoginID'] == $fbc_user_id) {
			if (isset($_POST['old_password']) && isset($_POST['new_password'])) {
				$data['user_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);
				if (password_verify($_POST['old_password'], $data['user_details']->password)) {
					$hashPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
					$update_data = array(
						"password" => $hashPassword
					);
					$update_password = $this->UserModel->update_password($update_data, $fbc_user_id);
					if ($update_password) {
						echo json_encode(array("flag" => 1, "msg" => "Updated Successfully"));
						exit;
					} else {
						echo json_encode(array("flag" => 0, "msg" => "Updating employee  failed"));
						exit;
					}
				} else {
					echo json_encode(array("flag" => 0, "msg" => "Old password doesn't match"));
					exit;
				}
			}
		} else {
			if (isset($_POST['new_password'])) {
				$data['user_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);
				$hashPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
				$update_data = array(
					"password" => $hashPassword
				);
				$update_password = $this->UserModel->update_password($update_data, $fbc_user_id);
				if ($update_password) {
					echo json_encode(array("flag" => 1, "msg" => "Updated Successfully"));
					exit();
				} else {
					echo json_encode(array("flag" => 0, "msg" => "Updating employee  failed"));
					exit();
				}
			}
		}
	}

	function email_exists()
	{
		$user_id = $this->input->post('user_id');

		$current_email = $this->input->post('current_email');
		if ($current_email) {
			$Email_Exist = $this->DashboardModel->email_exists($current_email, $user_id);
		}

		$new_email = $this->input->post('new_email');
		if ($new_email) {
			$Email_Exist = $this->DashboardModel->email_exists($new_email, $user_id);
		}


		if ($Email_Exist) {
			echo 'false';
			exit();
		} else {
			echo 'true';
			exit();
		}
	}

	function openAdminUserPopup()
	{
		$data['user_id'] = $_SESSION['user_id'];
		$data['admin_user_id'] = $_SESSION['LoginID'];
		$data['PageTitle'] = 'Edit User Email';
		$data['side_menu'] = 'dashboard';
		$data['change'] = 'email';
		//$data['type']=$type=isset($_POST['type'])?$_POST['type']:'';
		$View = $this->load->view('customer/change_user_details', $data, true);
		$this->output->set_output($View);
	}

	function edit_admin_user_email()
	{
		$user_id = $this->input->post('user_id');
		$current_email = $this->input->post('current_email');
		$new_email = $this->input->post('new_email');

		$checkUserExists = $this->DashboardModel->check_user_exists($user_id);

		if ($current_email == "" || $new_email == "") {
			echo json_encode(array('status' => 404, 'msg' => 'Please Fill All Fields'));
			exit();
		}

		if ($checkUserExists[0]['email'] === $new_email) {
			echo json_encode(array('status' => 404, 'msg' => 'Cannot Re-enter Same Email Again'));
			exit();
		}

		$checkEmailExist = $this->DashboardModel->check_user_email_exists($current_email, $user_id);

		if ($checkEmailExist) {
			$data['data'] = array(
				'email' => $new_email,
				'updated_at' => time()
			);

			$data['condition'] = array('id' => $user_id);

			$result = $this->DashboardModel->update_admin_user_email($data);

			if ($result) {
				echo json_encode(array('status' => 200, 'msg' => 'Email Updated Succesfully'));
				exit();
			} else {
				echo json_encode(array('status' => 404, 'msg' => 'Failed in Updating Email'));
				exit();
			}
		} else {
			echo json_encode(array('status' => 404, 'msg' => 'No Data Found'));
			exit();
		}
	}

	function openAdminUserPasswordPopup()
	{
		$data['user_id'] = $_SESSION['user_id'];
		$data['admin_user_id'] = $_SESSION['LoginID'];
		$data['PageTitle'] = 'Edit User Password';
		$data['side_menu'] = 'dashboard';
		$data['change'] = 'password';
		//$data['type']=$type=isset($_POST['type'])?$_POST['type']:'';
		$View = $this->load->view('customer/change_user_details', $data, true);
		$this->output->set_output($View);
	}

	function edit_admin_user_password()
	{
		$user_id = $this->input->post('user_id');
		$old_password = md5($this->input->post('old_password'));
		$new_password = md5($this->input->post('new_password'));
		$conf_new_password = md5($this->input->post('conf_new_password'));

		if ($old_password == "" || $new_password == "" && $conf_new_password == "") {
			echo json_encode(array('status' => 404, 'msg' => 'Please Fill all fields'));
			exit();
		}

		if ($new_password !== $conf_new_password) {
			echo json_encode(array('status' => 404, 'msg' => 'Passwords are Different. Please Check Again'));
			exit();
		}

		if ($old_password === $new_password) {
			echo json_encode(array('status' => 404, 'msg' => 'Old and New Passwords are Same. Please Create New Password'));
			exit();
		}

		$checkPasswordExist = $this->DashboardModel->check_user_password_exists($old_password, $user_id);

		if ($checkPasswordExist) {
			$data['data'] = array(
				'password' => $new_password,
				'updated_at' => time()
			);

			$data['condition'] = array('id' => $user_id);

			$result = $this->DashboardModel->update_admin_user_password($data);

			if ($result) {
				echo json_encode(array('status' => 200, 'msg' => 'Password Updated Succesfully'));
				exit();
			} else {
				echo json_encode(array('status' => 404, 'msg' => 'Failed in Updating Password'));
				return false;
			}
		} else {
			echo json_encode(array('status' => 404, 'msg' => 'No Data Found'));
			return false;
		}
	}

	function edit_admin_user_details()
	{
		$user_id = $this->input->post('user_id');
		$first_name = $this->input->post('first_name');
		$usertype = $this->input->post('usertype');
		$last_name = $this->input->post('last_name');
		$username = $this->input->post('username');
		$cp_radio = $this->input->post('cp_radio');

		if ($first_name == "" || $usertype == "" || $last_name == "" || $username == "" || $cp_radio == "") {
			echo json_encode(array('status' => 404, 'msg' => 'Please Fill all fields'));
			return false;
		}

		$data['data'] = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'user_type' => $usertype,
			'status' => $cp_radio,
			'updated_at' => time()
		);

		$data['condition'] = array('id' => $user_id);

		$result = $this->DashboardModel->update_admin_user_details($data);

		if ($result) {
			echo json_encode(array('status' => 200, 'msg' => 'Updated Succesfully'));
			exit();
		} else {
			echo json_encode(array('status' => 404, 'msg' => 'Updated unSuccesfully'));
			return false;
		}
	}
	public function handleRenewalClick()
	{
		$item_id = $_POST['item_id'];
		// $customer_id = $_POST['customer_id'];
		$order_id = $_POST['order_id'];

		// print_r($item_id);
		// // print_r($customer_id);
		// print_r($order_id);

		// die;
		$get_customer_details = $this->DashboardModel->get_customer_details($order_id);
		$PaymentInfo = $this->DashboardModel->getOrderPaymentDataById($order_id);
		$OrderItems = $this->DashboardModel->getRenewalOrderItems($order_id);
		$get_store_mobile_details = $this->DashboardModel->get_store_mobile_details();
		// echo "<pre>";
		// print_r($get_store_mobile_details);
		// die;
		$lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';

		// Assuming $OrderItems is an array of associative arrays
		if (!empty($OrderItems)) {
			// Accessing the sub_end_date property of the first associative array in the array
			$end_date = $OrderItems[0]['sub_end_date'];

			// Outputting or using the $end_date value
			echo $end_date;
		} else {
			// Handle the case where $OrderItems is empty
			echo "No order items found.";
		}

		$sub_end_date = date('d-M-Y', $end_date);

		$customer_name	= $get_customer_details->customer_firstname . ' ' . $get_customer_details->customer_lastname;
		// print_r($customer_name);
		// die;
		$store_mobile =	$get_store_mobile_details->value;
		$owner_email = [ADMIN_EMAILS];
		$shop_name = 'Indiamags';
		$templateId = 'customer_subscription_renewal';
		$to = $get_customer_details->customer_email;
		$site_logo = '';
		$burl = base_url();
		$shop_logo = '';

		$site_logo = '<a href="' . SITE_URL . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . SITE_LOGO . '" style="max-width:200px" /></a>';

		if ($get_customer_details != false) {
			// $increment_id = $get_customer_details['increment_id'];

			// $EmailTo = $get_customer_details['customer_email'];

			// $customer_firstname = $get_customer_details['customer_firstname'];

			$subtotal = $get_customer_details->subtotal;

			// $coupon_code = $get_customer_details['coupon_code'];

			// $base_discount_amount = number_format($get_customer_details['base_discount_amount'], 2);

			// $voucher_code = $get_customer_details['voucher_code'];

			// $voucher_amount = number_format($get_customer_details['voucher_amount'], 2);

			// $tax_amount = number_format($get_customer_details['tax_amount'], 2);
			$tax_amount = $get_customer_details->tax_amount;


			$grand_total = $get_customer_details->grand_total;

			$checkout_method = $get_customer_details->checkout_method;

			$shipping_amount = $get_customer_details->shipping_amount;

			// $payment_final_charge = number_format($get_customer_details['payment_final_charge'], 2); //cod
			$payment_final_charge = $get_customer_details->payment_final_charge;



			$currency_name =  $get_customer_details->currency_name;

			$currency_code_session = $get_customer_details->currency_code_session;

			$currency_conversion_rate = $get_customer_details->currency_conversion_rate;

			// $currency_symbol = $get_customer_details->currency_symbol;

			$default_currency_flag = $get_customer_details->default_currency_flag;



			// $order_date = date('d-M-Y h:i A', $get_customer_details['created_at']);

			// if ($get_customer_details->checkout_method == 'login') {

			if ($PaymentInfo != false) {



				$payment_method = $PaymentInfo[0]['payment_method'];

				if (isset($WebShopPaymentDetailsById['display_name']) && $WebShopPaymentDetailsById['display_name'] != null) {

					$payment_method_name = $WebShopPaymentDetailsById['display_name'];
				} else {

					$payment_method_name = $PaymentInfo[0]['payment_method_name'];
				}



				if (isset($WebShopPaymentDetailsById['message']) && $WebShopPaymentDetailsById['message'] != null) {

					$payment_method_name .= $WebShopPaymentDetailsById['message'];
				}
			} else {



				$payment_method_name = '';

				$payment_method = '';
			}
			$order_item_list = '';

			$discount_html = '';

			$voucher_html = '';

			$payment_html = '';

			if ($get_customer_details->checkout_method == 'login') {



				if ($lang_code == 'fr') {

					$login_url = '<p>Vous pouvez vérifier votre commande dans Mes Commandes en <a href="' . FRONTEND_BASE_URL . 'checkout">vous connectant à votre compte</a>.</p>';
				} else if ($lang_code == 'it') {

					$login_url = '<p>Puoi controllare il tuo ordine in I miei ordini <a href="' . FRONTEND_BASE_URL . 'checkout">accedendo al tuo account</a>.</p>';
				} else if ($lang_code == 'pt') {

					$login_url = '<p>Você pode verificar seu pedido em Meus Pedidos <a href="' . FRONTEND_BASE_URL . 'checkout">fazendo login em sua conta</a>.</p>';
				} else if ($lang_code == 'nl') {

					$login_url = '<p>U kunt uw bestelling controleren in Mijn Bestellingen door <a href="' . FRONTEND_BASE_URL . 'checkout">in te loggen op uw account</a>.</p>';
				} else if ($lang_code == 'de') {

					$login_url = '<p>Sie können Ihre Bestellung unter „Meine Bestellungen“ überprüfen, <a href="' . FRONTEND_BASE_URL . 'checkout"> indem Sie sich bei Ihrem Konto anmelden</a>.</p>';
				} else if ($lang_code == 'es') {

					$login_url = '<p>Puede consultar su pedido en Mis pedidos <a href="' . FRONTEND_BASE_URL . 'checkout">iniciando sesión en su cuenta</a>.</p>';
				} else {

					$login_url = '<a href="' . FRONTEND_BASE_URL . 'checkout">CheckOut Now</a>';
				}
			} else if ($get_customer_details->checkout_method == 'guest') {

				$encoded_id = base64_encode($get_customer_details->order_id);

				$encoded_id = urlencode($encoded_id);

				if ($lang_code == 'fr') {

					$login_url = '<a href="' . FRONTEND_BASE_URL . 'checkout' . $encoded_id . '">Cliquez ici</a> pour v&#233;rifier l&#233;tat de votre commande.</p>';
				} else if ($lang_code == 'it') {

					$login_url = '<a href="' . FRONTEND_BASE_URL . 'checkout' . $encoded_id . '">Clicca qui</a> per verificare lo stato del tuo ordine.</p>';
				} else if ($lang_code == 'pt') {

					$login_url = '<a href="' . FRONTEND_BASE_URL . 'checkout' . $encoded_id . '">Clique aqui</a> para verificar o status do seu pedido.</p>';
				} else if ($lang_code == 'nl') {

					$login_url = '<a href="' . FRONTEND_BASE_URL . 'checkout' . $encoded_id . '">Klik hier</a> om de status van uw bestelling te controleren.</p>';
				} else if ($lang_code == 'de') {

					$login_url = '<a href="' . FRONTEND_BASE_URL . 'checkout' . $encoded_id . '">Klicken Sie hier,</a> um den Status Ihrer Bestellung zu &#252;berpr&#252;fen.</p>';
				} else if ($lang_code == 'es') {

					$login_url = '<a href="' . FRONTEND_BASE_URL . 'checkout' . $encoded_id . '">Haga clic aqu&#237;</a> para comprobar el estado de su pedido.</p>';
				} else {

					$login_url = '<a href="' . FRONTEND_BASE_URL . 'checkout' . $encoded_id . '">CheckOut Now</a>';
				}
			}

			if ($payment_method != '' && $payment_method == 'cod' && $payment_final_charge > 0.00) {



				if ($currency_name != '' && $currency_code_session != ''  && $default_currency_flag != 1) {



					$convertedAmount =  $currency_conversion_rate * $get_customer_details->payment_final_charge;

					$payment_final_charge_amt =  $currency_code_session . number_format($convertedAmount, 2);
				} else {

					$payment_final_charge_amt = 'INR' . $payment_final_charge;
				}



				if ($lang_code == 'fr') {

					$Payment_Charge_txt = 'Frais de paiement';
				} else if ($lang_code == 'it') {

					$Payment_Charge_txt = 'Addebito di pagamento';
				} else if ($lang_code == 'pt') {

					$Payment_Charge_txt = 'Taxa de pagamento';
				} else if ($lang_code == 'nl') {

					$Payment_Charge_txt = 'Betalingskosten:';
				} else if ($lang_code == 'de') {

					$Payment_Charge_txt = 'Zahlungsgeb&#220;hr';
				} else if ($lang_code == 'es') {

					$Payment_Charge_txt = 'cargo de pago';
				} else {

					$Payment_Charge_txt = 'Payment Charge';
				}



				$payment_html = '<tr>

								<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

								' . $Payment_Charge_txt . '

								</td>

								<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

									<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $payment_final_charge_amt . '</span>

								</td>

							</tr>';
			}

			// $order_item_list = '';

			if (isset($OrderItems) && count($OrderItems) > 0) {

				// print_r($OrderItems);die();

				$total_itmes = count($OrderItems);

				$total_base_amount = 0;

				foreach ($OrderItems as $item) {


					$total_base_amount += $item['total_price'];

					$product_variants = '';

					if (isset($item['product_variants']) && $item['product_variants']  != '') {

						$product_variants = json_decode($item['product_variants']);
					}

					$variants = array();

					if (isset($product_variants) && $product_variants != '') {



						foreach ($product_variants as $pk => $single_variant) {

							foreach ($single_variant as $key => $val) {

								$variants[] = $key . ' : ' . $val . ' ';
							}
						}
					} else {

						$variants[] = ' ';
					}

					$variant_type = '';

					if (isset($variants) && $variants != '') {

						$variant_type = '<p style="font-weight: 500;font-size: 13px;line-height: 15px;color: #787878;">' . implode(', ', $variants) . '</p>';
					}





					if ($item['product_inv_type'] != 'dropship') {

						// $ch_obj->decrementAvailableQty($item['product_id'],$item['qty_ordered']);

					}



					if ($currency_name != '' && $currency_code_session != ''  && $default_currency_flag != 1) {





						$convertedAmount =  $currency_conversion_rate * $item['price'];

						$price_final =  number_format($convertedAmount, 2);



						$convertedAmount2 =  $currency_conversion_rate * $item['total_price'];

						$total_price_final =  number_format($convertedAmount2, 2);
					} else {



						$price_final = number_format($item['price'], 2);

						$total_price_final  = number_format($item['total_price'], 2);
					}
					$parent_product_id = $item['parent_product_id'];
					$product_id = $item['product_id'];
					$ProductImages = $this->DashboardModel->abundantCartProductImagesDetails($parent_product_id, $product_id);

					$MediaPath = CUSTOMER_EMAIL_IMAGE_URL_SHOW . '/products/thumb/';
					$base_image = '';

					foreach ($ProductImages as $img) {
						$base_image .= '<img id="" src="' . $MediaPath . $img['base_image'] . '" style="width: 100px; height: 100px;" alt="Image">';
					}
					// print_r($base_image);
					// die;
					$order_item_list .= '<tr>
						<td style="font-family: Verdana, Arial; font-weight: normal; border-collapse: collapse; vertical-align: top; padding: 10px 15px; margin: 0; border-top: 1px solid #ebebeb;" class="goods-page-image">
							' . $base_image . '
						</td>
						<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb;text-align:left">
							<p style="font-family:Verdana,Arial;font-weight:bold;margin:0 0 5px 0;color:#636363;font-style:normal;text-transform:uppercase;line-height:1.4;font-size:14px;float:left;width:100%;display:block">' . $item['product_name'] . '</p> ' . $variant_type . '
						</td>
						<td style="text-align:center;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">' . $item['qty_ordered'] . '</td>
						<td style="text-align:right;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">
							<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $price_final . '</span>
						</td>
						<td style="text-align:right;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:10px 15px;margin:0;border-top:1px solid #ebebeb">
							<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $total_price_final . '</span>
						</td>
					</tr>';

					// print_r($order_item_list);
					// die;
				}
			}


			if ($currency_name != '' && $currency_code_session != ''  && $default_currency_flag != 1) {



				$convertedAmount =  $currency_conversion_rate * $total_base_amount;

				$total_base_amount_final =  $currency_code_session . number_format($convertedAmount, 2);



				$convertedAmount2 =  $currency_conversion_rate * $get_customer_details->tax_amount;

				$tax_amount_final =  $currency_code_session . number_format($convertedAmount2, 2);



				$convertedAmount3 =  $currency_conversion_rate * $get_customer_details->shipping_amount;

				$shipping_amount_final =  $currency_code_session . number_format($convertedAmount3, 2);



				$convertedAmount4 =  $currency_conversion_rate * $get_customer_details->subtotal;

				$subtotal_final =  $currency_code_session . number_format($convertedAmount4, 2);



				$convertedAmount5 =  $currency_conversion_rate * $get_customer_details->grand_total;

				$grand_total_final =  $currency_code_session . number_format($convertedAmount5, 2);
			} else {

				$total_base_amount_final = 'INR' . $total_base_amount;

				$tax_amount_final = 'INR' . $tax_amount;

				$shipping_amount_final = 'INR' . $shipping_amount;

				$subtotal_final = 'INR' . $subtotal;

				$grand_total_final = 'INR' . $grand_total;
			}



			if ($lang_code == 'fr') {

				$item_price_txt = 'Prix(' . $total_itmes . ' articles) (Taxes incluses)';

				$item_txt = 'Articles de votre commande';

				$Qty_txt = 'Quantit&#237;';

				$Price_txt = 'Prix';

				$TotalPrice_txt  = 'Prix total';

				$Taxes_txt = 'Imp&ograve;ts';

				$shipping_txt = 'Exp&#237;dition et manutention';

				$Subtotal_txt = 'Total';

				$grand_total_txt = 'Total';
			} else if ($lang_code == 'it') {

				$item_price_txt = 'Prezzo(' . $total_itmes . ' articoli) <br> (Compreso di tasse)';

				$item_txt = 'Articoli nel tuo ordine';

				$Qty_txt = 'Quantit&#224;';

				$Price_txt = 'Prezzo';

				$TotalPrice_txt  = 'Prezzo totale';

				$Taxes_txt = 'Le tasse';

				$shipping_txt = 'Spedizione &amp; Gestione';

				$Subtotal_txt = 'totale parziale';

				$grand_total_txt = 'Somma totale';
			} else if ($lang_code == 'pt') {

				$item_price_txt = 'Pre&ccedil;o(' . $total_itmes . ' itens) <br> (incluindo impostos)';

				$item_txt = 'Itens em seu pedido';

				$Qty_txt = 'Quantidade';

				$Price_txt = 'Pre&ccedil;o';

				$TotalPrice_txt  = 'Pre&ccedil;o total';

				$Taxes_txt = 'Impostos';

				$shipping_txt = 'Envio e manuseio';

				$Subtotal_txt = 'Subtotal';

				$grand_total_txt = 'Total geral';
			} else if ($lang_code == 'nl') {

				$item_price_txt = 'Prijs(' . $total_itmes . ' stuks) <br> (Inclusief belastingen)';

				$item_txt = 'Artikelen in je bestelling';

				$Qty_txt = 'Hoeveelheid';

				$Price_txt = 'Prijs';

				$TotalPrice_txt  = 'Totale prijs';

				$Taxes_txt = 'Belastingen';

				$shipping_txt = 'Verzending &amp; Behandeling';

				$Subtotal_txt = 'Subtotaal';

				$grand_total_txt = 'Eindtotaal';
			} else if ($lang_code == 'de') {

				$item_price_txt = 'Preis(' . $total_itmes . ' Artikel) <br> (Inklusive Steuern)';

				$item_txt = 'Artikel in Ihrer Bestellung';

				$Qty_txt = 'Menge';

				$Price_txt = 'Preis';

				$TotalPrice_txt  = 'Gesamtpreis';

				$Taxes_txt = 'Steuern';

				$shipping_txt = 'Versand &amp; Bearbeitung';

				$Subtotal_txt = 'Zwischensumme';

				$grand_total_txt = 'Gesamtsumme';
			} else if ($lang_code == 'es') {

				$item_price_txt = 'Precio(' . $total_itmes . ' art&#237;culos) <br> (Impuestos incluidos)';

				$item_txt = 'Art&#237;culos en tu pedido';

				$Qty_txt = 'Cantidad';

				$Price_txt = 'Precio';

				$TotalPrice_txt  = 'Precio total';

				$Taxes_txt = 'Impuestos';

				$shipping_txt = 'Env&#237;o y manejo';

				$Subtotal_txt = 'Total parcial';

				$grand_total_txt = 'Gran total';
			} else {

				$item_price_txt = 'Price(' . $total_itmes . ' items) <br> (Inclusive of taxes)';

				$item_txt = 'Items <span class="il">in</span> your <span class="il">order</span>';

				$Qty_txt = 'Qty';

				$Price_txt = 'Price';

				$TotalPrice_txt  = 'Total Price';

				$Taxes_txt = 'Taxes';

				$shipping_txt = 'Shipping &amp; Handling';

				$Subtotal_txt = 'Subtotal';

				$grand_total_txt = 'Grand Total';
			}

			$order_items = '<tr>

			<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:0;margin:0">

				<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:10px 15px;margin:0">

					<thead>

						<tr>
							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">

								Image

							</th>
							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:left;font-size:11px">

							' . $item_txt . '

							</th>

							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:center;font-size:11px">

							' . $Qty_txt . '

							</th>

							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">

							' . $Price_txt . '

							</th>

							<th style="font-family:Verdana,Arial;font-weight:700;padding:10px 15px;background:#f1f1f1;text-transform:uppercase;text-align:right;font-size:11px">

							' . $TotalPrice_txt . '

							</th>

						</tr>

					</thead>

					<tbody>

					' . $order_item_list . '

					</tbody>



				</table>

			</td>

		</tr>

		<tr>

			<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:0;margin:0">

				<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0;border-top:1px dashed #c3ced4;border-bottom:1px dashed #c3ced4">

					<tbody>

						<tr>

							<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:20px 15px;margin:0;text-align:right;line-height:20px">

								<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0">

									<tbody>

									<tr style="padding-bottom:5px">

											<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

											' . $item_price_txt . '

											</td>

											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

												<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $total_base_amount_final . '</span>

											</td>

										</tr>

										<tr style="padding-bottom:5px">

											<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

											' . $Taxes_txt . '

											</td>

											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

												<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $tax_amount_final . '</span>

											</td>

										</tr>

										<tr style="padding-bottom:5px">

											<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

											' . $shipping_txt . '

											</td>

											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

												<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $shipping_amount_final . '</span>

											</td>

										</tr>

										' . $discount_html . '

										<tr>

											<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

											' . $Subtotal_txt . '

											</td>

											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

												<span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $subtotal_final . '</span>

											</td>

										</tr>

										' . $voucher_html . '

										' . $payment_html . '

										<tr>

											<td colspan="3" align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

												<strong>' . $grand_total_txt . '</strong>

											</td>

											<td align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">

												<strong><span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $grand_total_final . '</span></strong>

											</td>

										</tr>

									</tbody>

								</table>

							</td>

						</tr>

					</tbody>

				</table>

			</td>

		</tr>';

			if ($checkout_method == 'guest') {

				$oid = base64_encode($get_customer_details->order_id);

				$encoded_oid = urlencode($oid);

				$guest_order_url = FRONTEND_BASE_URL . 'checkout' . $encoded_oid;



				if ($lang_code == 'fr') {

					$customer_note = "<p>Si vous souhaitez annuler ou retourner votre commande, veuillez <a href='" . $guest_order_url . "' target='_blank'>Cliquez ici.</a></p>";
				} else if ($lang_code == 'it') {

					$customer_note = "<p>Se desideri annullare o restituire il tuo ordine, per favore <a href='" . $guest_order_url . "' target='_blank'>clicca qui.</a></p>";
				} else if ($lang_code == 'pt') {

					$customer_note = "<p>Se pretender cancelar ou devolver a sua encomenda, por favor please <a href='" . $guest_order_url . "' target='_blank'>Clique aqui.</a></p>";
				} else if ($lang_code == 'nl') {

					$customer_note = "<p>Als u uw bestelling wilt annuleren of retourneren, alstublieft <a href='" . $guest_order_url . "' target='_blank'>Klik hier.</a></p>";
				} else if ($lang_code == 'de') {

					$customer_note = "<p>Wenn Sie Ihre Bestellung stornieren oder zur&#252;cksenden m&ouml;chten, bitte <a href='" . $guest_order_url . "' target='_blank'>Klick hier.</a></p>";
				} else if ($lang_code == 'es') {

					$customer_note = "<p>Si desea cancelar o devolver su pedido, por favor <a href='" . $guest_order_url . "' target='_blank'>haga clic aqu&#237;.</a></p>";
				} else {

					$customer_note = "<p>If you want to cancel or return you order, please <a href='" . $guest_order_url . "' target='_blank'>click here.</a></p>";
				}
			} else {

				$customer_note = '';
			}




			$customer_note = '';
		}
		$username = 'indiamags';
		$TempVars = ["##CUSTOMERORDERID##",  "##CUSTOMER_NOTE##", "##CUSTOMER_NAME##",  "##ORDER_ITEMS##", "##LOGIN_URL##", "##SUBSCRIPTION_END_DATE##", "##STORE_MOBILE##"];
		$DynamicVars = [$order_id, $customer_note, $customer_name, $order_items, $login_url, $sub_end_date, $store_mobile];
		$CommonVars = [$site_logo, $shop_name];
		$SubDynamic = [$order_id];

		if (isset($templateId)) {
			$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
			if ($emailSendStatusFlag == 1) {

				$mailSent = $this->DashboardModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars);

				// Check if email was sent successfully
				if (!$mailSent) {
					$arrResponse = ['status' => 500, 'message' => 'Error sending email.'];
					echo json_encode($arrResponse);
					exit;
				}
			}
		}

		// Update the email_sent flag in the database
		$updateResult = $this->DashboardModel->updateEmailSentFlag($order_id);

		if ($updateResult) {
			$arrResponse = ['status' => 200, 'message' => 'Email Sent Successfully!'];
		} else {
			$arrResponse = ['status' => 500, 'message' => 'Error updating email_sent flag.'];
		}

		echo json_encode($arrResponse);
		exit;


		// echo "<pre>";
		// print_r($get_cutstomer_details);
		// die;
	}
}
