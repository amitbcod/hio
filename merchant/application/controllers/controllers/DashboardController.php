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
		if(isset($_SESSION) && isset($_SESSION['ShopDatabaseName']) && $_SESSION['ShopDatabaseName']!=''){
			$this->load->model('EmployeeModel');
		}
		if(!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] ==''){
			redirect(BASE_URL);
		}
	}
	
	public function index()
	{
		$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
		$data['PageTitle']= 'Dashboard';
		$data['user_count'] = $this->CommonModel->get_data_count('adminusers');
		$data['publisher_count'] = $this->CommonModel->get_data_count('publisher');
		$data['customer_count'] = $this->CommonModel->get_data_count('customers');
		$data['product_count'] = $this->CommonModel->get_data_count('products',$LogindID);
		$get_sub = $this->CommonModel->getSalesOrderItems($LogindID);
		if($get_sub)
		{

			foreach($get_sub as $key_sub=>$val_Sub){
				$get_subscription_period = $this->CommonModel->get_sub_period($val_Sub['product_id']);
				if($get_subscription_period && $get_subscription_period['sub_time'] != '')
				{
					$sub_start_time = strtotime('first day of +1 month',$val_Sub['created_at']);
					$update_sub_start_time = $this->CommonModel->update_sub_start($val_Sub['item_id'],  $sub_start_time);
					$sub_end_time = strtotime($get_subscription_period['sub_time'] ,$sub_start_time);
					$update_sub_start_time = $this->CommonModel->update_sub_end($val_Sub['item_id'],  $sub_end_time);
				}

			}
		}
		$data['get_subcription_data'] = $this->CommonModel->get_all_subscription($LogindID);
		$this->load->view('dashboard.php', $data);
	}

	public function employee_role(){
		if(!empty($this->session->userdata('userPermission')) && !in_array('fbc_usermanagement/employee_role',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$data['PageTitle']= 'Employee Role'; 
		$data['empRole'] =$this->EmployeeModel->getEmpRoles();
		$this->load->view('employee_role',$data);	
	}

	public function createRole()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('fbc_usermanagement/employee_role',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));  }
		$roleId = $this->uri->segment(2);
		$data['PageTitle']= 'Create Employee Role';
		$data['roleId'] = $roleId;
		$data['parentData']= $this->EmployeeModel->getParentData();
		$data['singleRole']= $this->EmployeeModel->getSingleRoleNameByID($roleId);
		$this->load->view('create_employee_role',$data);	
	}
	
	public function CreateRoleResource()
	{
			if ($this->input->post('role_name')!="" && $this->input->post('resource_access')!="") {
			    $roleId = $_POST['roleId'];
				$data['role_name'] = $this->input->post('role_name');
				$data['resource_access'] = $this->input->post('resource_access');
				$resource_checkbox['employee_resource'] = $this->input->post('myArray');
				if($roleId > 0)
				{
					if($_POST['resource_access'] ==0)
					{
					$this->EmployeeModel->deleteRole($roleId);
					$this->EmployeeModel->updateRole($roleId,$data);
					$redirect = base_url('employee_role');
					echo json_encode(array('flag' => 1, 'msg' => "Role Updated." ,'redirect' => $redirect));
					exit;
					}
					else{
					$this->EmployeeModel->updateRole($roleId,$data);
					$this->EmployeeModel->deleteRole($roleId);
				    $result = $this->EmployeeModel->insertRoleResource($roleId,$resource_checkbox['employee_resource']);
					$redirect = base_url('employee_role');
					echo json_encode(array('flag' => 1, 'msg' => "Role Updated." ,'redirect' => $redirect));
					exit;
					}	
				}
				else{
					$response = $this->EmployeeModel->insertRole($data);
				    $result = $this->EmployeeModel->insertRoleResource($response,$resource_checkbox['employee_resource']);
					$redirect = base_url('employee_role');
					echo json_encode(array('flag' => 1, 'msg' => "New Role Created." ,'redirect' => $redirect));
					exit;
				    }
			}else{
					echo json_encode(array('flag' => 0, 'msg' => "Please fill all requrired fields"));
					exit();
			}
		
	}

	public function employee_delete_role(){
		$id = $this->input->post('id');
		$result = $this->EmployeeModel->delete_role($id);
		if($result==true){		
			echo json_encode(array('flag'=>1, 'msg'=>"Role Deleted Successfully."));
			exit;}
		else{
			echo json_encode(array('flag'=>0, 'msg'=>"Role UnDeleted Successfully."));
			exit;
		}
	}
	
	public function update_user_details()
	{
		if(isset($_POST))
		{
			if(empty($_POST['owner_name']) || empty($_POST['email']) || empty($_POST['mobile_no']) || empty($_POST['company_name'])) {
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			else if( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email"])) {
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter a valid Email address."));
				exit;
			}
			else
			{			
				$bill_state='';
				if($_POST['bill_country'] == 'IN')
				{
					$bill_state= $_POST['bill_state_dp'];
				}else
				{
					$bill_state= $_POST['bill_state'];
				}
				$ship_state='';
				if($_POST['ship_country'] == 'IN')
				{
					$ship_state= $_POST['ship_state_dp'];
				}else
				{
					$ship_state= $_POST['ship_state'];
				}
				if($_POST['bill_country'] == 'IN' && $_POST['ship_country'] == 'IN')
				{
					if($bill_state !=$ship_state )
					{
						echo json_encode(array('flag'=>0, 'msg'=>"Billing state and Shipping State should be same for Country India."));
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
				if($update_user_details)
				{
					$update_user_shop_details = $this->UserModel->update_fbc_users_shop($update_fbc_users_shop);
					if($update_user_shop_details)
					{
						echo json_encode(array('flag'=>1,"msg" => "Success"));
						exit();
					}
					else
					{
						echo json_encode(array('flag'=>0,"msg" => "User Shop Data was Not Updated"));
						exit();
					}
				}
				else
				{
					echo json_encode(array('flag'=>0,"msg" => "User Data was Not Updated"));
					exit();
				}
			}
		}
		else
		{
			echo json_encode(array('flag'=>0,"msg" => "Please Post Data")	);
			exit();
		}
	}

	public function employee_details($fbc_user_id = false)
	{
		if(isset($fbc_user_id) && $fbc_user_id)
		{
			$data['fbc_user_id'] = $fbc_user_id;
			$data['emp_roles'] = $this->CommonModel->GetEmpRole();
			$data['emp_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);
			$data['emp_personal_details'] = $this->CommonModel->GetEmpByUserId($fbc_user_id);
		}
		else
		{
			$data['emp_roles'] = $this->CommonModel->GetEmpRole();
			$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		}
		$this->load->view('employee_details.php', $data);
	}
	
	public function insert_employee_details()
	{	
		if(isset($_POST))
		{
			$time = time();
			if(empty($_POST['emp_name']) || empty($_POST['emp_email']) || empty($_POST['emp_mobile'])) {
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			else if( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["emp_email"])) {
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter a valid Email address."));
				exit;
			}
			else
			{
				if(isset($_POST['action']) && $_POST['action'] == 'insert')
				{
					$user_details = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
					$check_email_exist = $this->CommonModel->GetUserByEmail($_POST['emp_email']);
					if(!$check_email_exist)
					{
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
						$insert_employee = $this->UserModel->insert_employee($insert_array_fbc_users,$_POST['identifier']);
						if($insert_employee)
						{
							$get_employee_details = $this->CommonModel->GetUserByEmail($_POST['emp_email']);
							$fbc_users_emp_details = array(
							'fbc_user_id' => $get_employee_details['fbc_user_id'],
							'residential_address' => $_POST['emp_address'],
							'role_in_company' => $_POST['emp_role'],
							"created_by" => $_SESSION['LoginID'],
							"created_at" => $time
							);
							$insert_employee_details = $this->UserModel->insert_employee_details($fbc_users_emp_details);
							if($insert_employee)
							{
								$redirect = BASE_URL.'dashboard';
								echo json_encode(array("flag"=> 1,"status" => "200" ,"msg" => "Success","redirect" => $redirect ));
								exit();
							}
							else
							{
								echo json_encode(array("flag"=> 0,"status" => "205" ,"msg" => "Adding new Employee Details failed"));
								exit();
							}							
						}
						else
						{
							echo json_encode(array("flag"=> 0,"status" => "204" ,"msg" => "Adding new Employee failed"));
							exit();
						}
					}
					else
					{
						echo json_encode(array("flag"=> 0,"status" => "203" ,"msg" => "Email Id already Exist"));
						exit();
					}
				}
				else if(isset($_POST['action']) && $_POST['action'] == 'update')
				{
							$update_array_fbc_users = array(
							"owner_name" => $_POST['emp_name'],
							"mobile_no" => $_POST['emp_mobile'],
							"updated_at" => $time,
							'ip' => $_SERVER['REMOTE_ADDR']
						);
						$update_employee = $this->UserModel->update_employee($update_array_fbc_users,$_POST['fbc_user_id']);
						if($update_employee)
						{
							$update_fbc_users_emp_details = array(
							'residential_address' => $_POST['emp_address'],
							'role_in_company' => $_POST['emp_role'],
							'updated_at' => time()
							);
							$update_employee_details = $this->UserModel->update_employee_details($update_fbc_users_emp_details,$_POST['fbc_user_id']);
							if($update_employee_details)
							{
								$redirect = BASE_URL.'dashboard';
								echo json_encode(array("flag"=> 1,"status" => "200" ,"msg" => "Updated Successfully","redirect" => $redirect));
								exit();
							}
							else
							{
								echo json_encode(array("flag"=> 0,"status" => "205" ,"msg" => "Updating new Employee Details failed"));
								exit();
							}
						}
						else
						{
							echo json_encode(array("flag"=> 0,"status" => "206" ,"msg" => "Updating Employee  failed"));
							exit();
						}
				}
			}
		}
		else
		{
			echo json_encode(array("flag"=> 0,"msg" => "Please Post Data")	);
			exit();
		}
	}
	
	public function change_password($user_id = false)
	{
		if($user_id)
		{
			$fbc_user_id = $user_id;
		}
				if(isset($_POST['old_password']) && isset($_POST['new_password']))
				{
					$data['user_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);	
					if(password_verify($_POST['old_password'],$data['user_details']->password))
					{
						$hashPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
						$update_data = array(
							"password" => $hashPassword
						);
						$update_password = $this->UserModel->update_password($update_data,$fbc_user_id);
						if($update_password)
						{
							echo json_encode(array("flag"=> 1,"msg" => "Updated Successfully"));
							exit();
						}
						else
						{
							echo json_encode(array("flag"=> 0,"msg" => "Updating employee  failed"));
							exit();
						}
					}
					else
					{
						echo json_encode(array("flag"=> 0,"msg" => "Old password doesn't match"));
						exit();
						
					}
				}		
	}

	public function change_email($user_id = false)
	{	
		if($user_id)
		{
			$fbc_user_id = $user_id;
		}
				if(isset($_POST['current_email']) && isset($_POST['new_email']))
				{
					$data['user_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);
					if($_POST['current_email'] === $data['user_details']->email)
					{
						if($_POST['new_email'] != $data['user_details']->email)
						{
								$new_email = $_POST['new_email'];
								$email_exists = $this->UserModel->email_exists($new_email);
								if(!$email_exists)
								{
									$update_data = array(
									"email" => $new_email);
									$update_email = $this->UserModel->update_email($update_data,$fbc_user_id);
									if($update_email)
									{
										$redirect = BASE_URL.'dashboard';
										echo json_encode(array("flag"=> 1,"msg" => "Updated Successfully"));
										exit();
									}
									else
									{
										echo json_encode(array("flag"=> 0,"msg" => "Updating Email  failed"));
										exit();
									}
								}else
								{
									echo json_encode(array("flag"=> 0,"msg" => "New Email Already Exists "));
									exit();
								}							
						}else
						{
								echo json_encode(array("flag"=> 0,"msg" => "Email Already Exists "));
								exit();
						}
					}
					else
					{
						echo json_encode(array("flag"=> 0,"msg" => "Current Email doesn't match"));
						exit();
					}
				}
	}
	
	public function change_employee_status()
	{
		if(isset($_POST) && !empty($_POST))
		{
			$change_employee_status = $this->UserModel->change_employee_status($_POST['status'],$_POST['fbc_usr_id']);
			if($change_employee_status)
			{
				if($_POST['status'] == 2)
				{
					echo json_encode(array("flag"=> 1,"msg" => "Updated Successfully","status" => '1'));
					exit();
				}
				else if($_POST['status'] == 1)
				{
					echo json_encode(array("flag"=> 1,"msg" => "Updated Successfully","status" => '2'));
					exit();
				}
			}
			else
			{
				echo json_encode(array("flag"=> 0,"msg" => "Updating employee  failed"));
				exit();
			}
		}
		else{
			echo json_encode(array("flag"=> 0,"msg" => "Please Post Data"));
			exit();
		}			
	}

	public function emp_change_password($user_id = false)
	{
		if($user_id)
		{
			$fbc_user_id = $user_id;
		}
			if($_SESSION['LoginID']==$fbc_user_id){
				if(isset($_POST['old_password']) && isset($_POST['new_password']))
				{
					$data['user_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);	
					if(password_verify($_POST['old_password'],$data['user_details']->password))
					{
						$hashPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
						$update_data = array(
							"password" => $hashPassword
						);
						$update_password = $this->UserModel->update_password($update_data,$fbc_user_id);
						if($update_password)
						{
							echo json_encode(array("flag"=> 1,"msg" => "Updated Successfully"));
							exit;
						}						
						else
						{
							echo json_encode(array("flag"=> 0,"msg" => "Updating employee  failed"));
							exit;
						}
					}
					else
					{
						echo json_encode(array("flag"=> 0,"msg" => "Old password doesn't match"));
						exit;
					}
				}
			}else{
				if(isset($_POST['new_password']))
				{
					$data['user_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);	
					$hashPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
					$update_data = array(
						"password" => $hashPassword
					);
					$update_password = $this->UserModel->update_password($update_data,$fbc_user_id);
					if($update_password)
					{
						echo json_encode(array("flag"=> 1,"msg" => "Updated Successfully"));
						exit();
					}					
					else
					{
						echo json_encode(array("flag"=> 0,"msg" => "Updating employee  failed"));
						exit();
					}
					
				}
			}
	}

	function email_exists() {
        $user_id = $this->input->post('user_id');

        $current_email = $this->input->post('current_email');
		if($current_email) {
			$Email_Exist = $this->DashboardModel->email_exists($current_email, $user_id);
		}

        $new_email = $this->input->post('new_email');
		if($new_email) {
			$Email_Exist = $this->DashboardModel->email_exists($new_email, $user_id);
		}
        

        if ($Email_Exist) {
            echo 'false';
            exit();
        }
        else {
            echo 'true';
            exit();
        }
    }

	function openAdminUserPopup(){
		$data['user_id'] = $_SESSION['user_id'];
		$data['admin_user_id'] = $_SESSION['LoginID'];
		$data['PageTitle']='Edit User Email';
		$data['side_menu']='dashboard';
		$data['change'] = 'email';
		//$data['type']=$type=isset($_POST['type'])?$_POST['type']:'';
		$View = $this->load->view('customer/change_user_details', $data, true);
		$this->output->set_output($View);
	}

	function edit_admin_user_email() {
		$user_id = $this->input->post('user_id');
        $current_email = $this->input->post('current_email');
        $new_email = $this->input->post('new_email');

		$checkUserExists = $this->DashboardModel->check_user_exists($user_id);

        if($current_email == "" || $new_email == "")
        {
            echo json_encode(array('status'=>404,'msg'=>'Please Fill All Fields'));
            exit();
        }

		if($checkUserExists[0]['email'] === $new_email) {
			echo json_encode(array('status'=>404,'msg'=>'Cannot Re-enter Same Email Again'));
            exit();
		}

		$checkEmailExist = $this->DashboardModel->check_user_email_exists($current_email, $user_id);

		if($checkEmailExist) {
			$data['data'] = array(
				'email' => $new_email,
				'updated_at' => time()
			);
	
			$data['condition'] = array('id'=>$user_id);
	
			$result = $this->DashboardModel->update_admin_user_email($data);
	
			if($result)
			{
				echo json_encode(array('status'=>200,'msg'=>'Email Updated Succesfully'));
				exit();
			}
			else{
				echo json_encode(array('status'=>404,'msg'=>'Failed in Updating Email'));
				exit();
			}
		}
		else {
			echo json_encode(array('status'=>404,'msg'=>'No Data Found'));
			exit();
		}
	}

	function openAdminUserPasswordPopup(){
		$data['user_id'] = $_SESSION['user_id'];
		$data['admin_user_id'] = $_SESSION['LoginID'];
		$data['PageTitle']='Edit User Password';
		$data['side_menu']='dashboard';
		$data['change'] = 'password';
		//$data['type']=$type=isset($_POST['type'])?$_POST['type']:'';
		$View = $this->load->view('customer/change_user_details', $data, true);
		$this->output->set_output($View);
	}

	function edit_admin_user_password() {
		$user_id = $this->input->post('user_id');
        $old_password = md5($this->input->post('old_password'));
        $new_password = md5($this->input->post('new_password'));
        $conf_new_password = md5($this->input->post('conf_new_password'));
		
        if($old_password == "" || $new_password == "" && $conf_new_password == "")
        {
			echo json_encode(array('status'=>404,'msg'=>'Please Fill all fields'));
            exit();
        }
		
		if($new_password !== $conf_new_password)
        {
			echo json_encode(array('status'=>404,'msg'=>'Passwords are Different. Please Check Again'));
            exit();
        }

		if($old_password === $new_password)
        {
            echo json_encode(array('status'=>404,'msg'=>'Old and New Passwords are Same. Please Create New Password'));
            exit();
        }

		$checkPasswordExist = $this->DashboardModel->check_user_password_exists($old_password, $user_id);

		if($checkPasswordExist) {
			$data['data'] = array(
				'password' => $new_password,
				'updated_at' => time()
			);
	
			$data['condition'] = array('id'=>$user_id);
	
			$result = $this->DashboardModel->update_admin_user_password($data);
	
			if($result)
			{
				echo json_encode(array('status'=>200,'msg'=>'Password Updated Succesfully'));
				exit();
			}
			else{
				echo json_encode(array('status'=>404,'msg'=>'Failed in Updating Password'));
				return false;
			}
		}
		else {
			echo json_encode(array('status'=>404,'msg'=>'No Data Found'));
			return false;
		}
	}

	function edit_admin_user_details() {
		$user_id = $this->input->post('user_id');
        $first_name = $this->input->post('first_name');
        $usertype = $this->input->post('usertype');
        $last_name = $this->input->post('last_name');
        $username = $this->input->post('username');
        $cp_radio = $this->input->post('cp_radio');

        if($first_name == "" || $usertype == "" || $last_name == "" || $username == "" || $cp_radio == "")
        {
            echo json_encode(array('status'=>404,'msg'=>'Please Fill all fields'));
            return false;
        }

        $data['data'] = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_type' => $usertype,
            'status' => $cp_radio,
            'updated_at' => time()
        );

        $data['condition'] = array('id'=>$user_id);

        $result = $this->DashboardModel->update_admin_user_details($data);

        if($result)
        {
            echo json_encode(array('status'=>200,'msg'=>'Updated Succesfully'));
			exit();
        }
		else{
			echo json_encode(array('status'=>404,'msg'=>'Updated unSuccesfully'));
			return false;
		}
	}

}
