<?php
defined('BASEPATH') or exit('No direct script access allowed');
class ManagerController extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('CommonModel');
		$this->load->model('UserModel');
		$this->load->model('ManagerModel');
		$this->load->model('InvoicingModel');
		$this->load->helper('url');
		// $this->load->model('NotificationModel');
		
		if(!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] ==''){
			redirect(BASE_URL);
		}
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/account_managers',$this->session->userdata('userPermission'))){ 
           redirect(base_url('dashboard'));  }

		$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			
			$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
			if(isset($FBCData) && $FBCData->database_name!='')
			{
				$fbc_user_database=$FBCData->database_name;
				
				$this->load->database();
				$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);		
				$this->seller_db = $this->load->database($config_app,TRUE);
			}else{
				redirect(base_url());
			}
		
	}
	
	public function index()
	{
		$data['side_menu']='webShop';
	    $data['account_manager_details'] = $this->ManagerModel->get_account_manager_details();
		$this->load->view('manager/account_manager.php',$data);
	}

	public function managerAccount_details($type_id)
	{
		$data['side_menu']='webShop';
		$data['type_details'] = $this->ManagerModel->get_single_account_manager_details($type_id);
		$data['manager_by_type'] = $this->ManagerModel->get_all_manager_by_type($type_id);
		$this->load->view('manager/manager_account_details',$data);
	}

	public function create_account_manager()
	{
		if(isset($_POST) && !empty($_POST))
		{
			$time = time();
			if(empty($_POST['name'])) {
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			else
			{
				$insert_array = array(
								"name"=> $_POST['name'],
								"created_at" => $time,
								"created_by" =>	$_SESSION['LoginID'],
								"ip" => $_SERVER['REMOTE_ADDR']
				);
				$insert_account = $this->ManagerModel->add_account_manager($insert_array);
				if($insert_account)
				{
					echo json_encode(array("flag"=> 1,"status" => "200" ,"msg" => "Success")); exit();
				}
				else{
					echo json_encode(array("flag"=> 0,"status" => "204" ,"msg" => "Adding new Account Manager failed")); exit();
				}
			}
		}
		else
		{
			echo json_encode(array("flag"=> 0,"status" => "204" ,"msg" => "Please Post Data")); exit();
		}
		
	}


	public function update_type_details($type_id = false)
	{
		if(isset($_POST) && !empty($_POST))
		{
			$time= time();
			if(empty($_POST['name_val'])) {
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			else if(!$type_id){
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			
			else
			{
				$update_array = array(
								"name"=> $_POST['name_val'],
								"updated_at" => $time
				);
				$update_manager_account = $this->ManagerModel->update_manager_account($update_array,$type_id);
				if($update_manager_account)
				{
					echo json_encode(array("flag"=> 1,"status" => "200" ,"msg" => "Success")); exit();
				}
				else{
					echo json_encode(array("flag"=> 0,"status" => "204" ,"msg" => "Updating  Account Manager failed")); exit();
				}
			}
			
		}
		else
		{
			echo json_encode(array("flag"=> 0,"status" => "204" ,"msg" => "Please Post Data")); exit();
		}
		
	}
	


}/*****End******/
