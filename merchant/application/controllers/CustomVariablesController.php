<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CustomVariablesController extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
		$this->load->model('CommonModel');
		$this->load->model('VariableModel');
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
    }
	
	public function index()
	{
			$data['CustomVariables'] = $this->VariableModel->getVariable();
			$data['PageTitle']='Custom Variables';
			$data['side_menu']='System';
			$this->load->view('custom_variables/variable_manage.php',$data);
	}
	
	
	function editCustVariable()
	{
		$id = $_POST['id'];
	    $CustVariable = $this->VariableModel->get_editcustvariable($id);
		echo json_encode(array('flag' => 1,'data'=>$CustVariable)); exit();
	}
	
	function VariablesPost()
	{
		if(isset($_POST) && $_POST != '')
		{
			$Record = $this->VariableModel->get_existidentifier($_POST['VariableCode']);
			
			if(empty($_POST['VariableCode']) && empty($_POST['VariableName']) && empty($_POST['VariableValue'])){
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
			    exit;
			}else if($Record > 0){
				echo json_encode(array('flag'=>0, 'msg'=>"Custom variable code ".trim($_POST["VariableCode"])." allready exist."));
			    exit;
			}
			else{
					$loginId = $_SESSION['LoginID'];
					$MakeIdentifier = str_replace(" ", "_", $_POST['VariableCode']);
					$nIdentifier = strtolower($MakeIdentifier);
					
					$insertdata=array(	
						'identifier'=> $nIdentifier,
						'name'=> $_POST['VariableName'],
						'value'=> $_POST['VariableValue'],
						'created_by'=> $loginId,
						'created_by_type'=> 1,
						'created_at'=>time(),
						'ip'=>$_SERVER['REMOTE_ADDR']
					);

					$variable_id=$this->VariableModel->insertData('custom_variables',$insertdata);
					if($variable_id){
						echo json_encode(array('flag' => 1, 'msg' => "Success"));
						exit();
					}else{
						echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));
						exit;
					}
					
			}
		}else{
			echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
			exit;
		}
	}
	
	function editVariablesPost()
	{
		if(isset($_POST) && $_POST != '')
		{
			if(empty($_POST['VariableCode']) && empty($_POST['VariableName']) && empty($_POST['VariableValue'])){
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
			    exit;
			}
			else{
					$variable_id = $_POST['Id'];	
					
					$MakeIdentifier = str_replace(" ", "_", $_POST['VariableCode']);
					$nIdentifier = strtolower($MakeIdentifier);
					
					$where_arr=array('id'=>$variable_id);
					$update=array(	
						'identifier'=> $nIdentifier,
						'name'=> $_POST['VariableName'],
						'value'=> $_POST['VariableValue'],
						'updated_at'=>time(),
						'ip'=>$_SERVER['REMOTE_ADDR']
					);

					$rowAffected = $this->VariableModel->updateNewData('custom_variables',$where_arr,$update);
					if($rowAffected){
						echo json_encode(array('flag' => 1, 'msg' => "Success"));
						exit();
					}else{
						echo json_encode(array('flag' => 0, 'msg' => "nothing to update!"));
						exit;
					}
			}
		}else{
			echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
			exit;
		}
	}
	
	
}
