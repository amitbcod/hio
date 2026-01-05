<?php

class EmailTemplateController extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
		$this->load->model('CommonModel');
		$this->load->model('EmailModel');
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
    }
	
	public function index()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('system/email_template/read',$this->session->userdata('userPermission'))){ 
                // redirect('dashboard');
            }
        }
			$data['EmailTemplate'] = $this->EmailModel->getEmail();
			$data['PageTitle']='Email Template';
			$data['side_menu']='System';
			$this->load->view('email/email_template.php',$data);
		
	}
	
	public function template_details($id = false)
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('system/email_template/write',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }
		
			$data['side_menu']='System';
		
			if(isset($id) && !empty($id))
			{
				$data['action'] = 'update';
				$data['template_detail'] = $this->EmailModel->getEmail($id);
				if($data['template_detail'] == '')
				{
					redirect('dashboard');

				}
				$this->load->view('email/template_detail.php',$data);
			}
			else
			{
				$data['action'] = 'insert';
				$this->load->view('email/template_detail.php',$data);
				
			}
			
	}
	
	public function submit_template_details($id = false)
	{

		
		
			if(empty($_POST['title']) && empty($_POST['email_code']) && empty($_POST['template_subject']) && empty($_POST['template_content'])) {
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			elseif($this->EmailModel->getTemplateCode($_POST["template_code"]) != 0 && $_POST['action'] == 'insert' ) {

				echo json_encode(array('flag' => 0, 'msg' => "Email Code Exist!"));
				exit;	
			}
			elseif($this->EmailModel->getTemplatetitle($_POST["title"]) != 0 && $_POST['action'] == 'insert' ){
				echo json_encode(array('flag' => 0, 'msg' => "Template Title  Allready Exist!"));
				exit;
			}
			else {

				$time = time();
				if(!isset($_POST['template_status']) ||  $_POST['template_status'] == '')
					{
						$temp_status = $_POST['template_status'] = 0;
					}
					else
					{
						$temp_status = $_POST['template_status'] = 1;
					}
				if($_POST['action'] == 'insert')
				{
					$insert_array = array(
					"title" => $_POST['title'],
					"email_code" => $_POST['template_code'],
					"subject" => $_POST['template_subject'],
					"content" => $_POST['template_content'],
					"status"=>$temp_status,
					"created_by" => $_SESSION['LoginID'],
					"created_at" => $time
					);
					$insert_support = $this->EmailModel->insert_template($insert_array);
					if($insert_support)
					{
						$redirect = BASE_URL.'email-template';
						echo json_encode(array('flag'=>1, 'msg'=>"Success",'redirect' => $redirect)); exit();
					}
					else{
						echo json_encode(array('flag'=>0, 'msg'=>"Adding new Support Failed")); exit();
					}
				}
				
				else if($_POST['action'] == 'update')
				{ 	
					if(!isset($_POST['template_status']) ||  $_POST['template_status'] == '')
					{
						$temp_status = $_POST['template_status'] = 0;
					}
					else
					{
						$temp_status = $_POST['template_status'] = 1;
					}
					$update_array = array(
					"title" => $_POST['title'],
					"email_code" => $_POST['template_code'],
					"subject" => $_POST['template_subject'],
					"content" => $_POST['template_content'],
					"status" => $temp_status,
					"updated_at" => $time
					);
					$update_support = $this->EmailModel->update_template($update_array,$id);
					if($update_support)
					{	
						$redirect = BASE_URL.'email-template';
						echo json_encode(array('flag'=>1, 'msg'=>"Success",'redirect' => $redirect));  exit();
					}
					else{
						echo json_encode(array('flag'=>0, 'msg'=>"updating Support Failed"));  exit();
					}
				}
			}
		
		
	}

	public function openTemplates(){
        if(isset($_POST['id']) && $_POST['id']!=''){
            $data['id']= $id = $_POST['id'];	
            $data['code'] =$code = $_POST['code'];
            $data['template_detail'] = $this->EmailModel->getEmail($id);
		    $data['templates_details'] = $this->EmailModel->get_templates_details($id);
            $data['codeName']= $codeName = $this->Multi_Languages_Model->getCodeName($code);
            $data['getEmail'] =  $this->EmailModel->get_Multi_Templates($id,$code);
            $View = $this->load->view('email/templates_translations.php', $data, true);
            $this->output->set_output($View);
        }else{
            echo "error";exit;
        }
    }

	public function emailTemplates()
    {
        $fbc_user_id = $_SESSION['LoginID'];
        if(isset($_POST) && $_POST !=''){
         $id = $_POST['hidden_temp_id'];
         $code = $_POST['code'];
         $count = $this->EmailModel->countTemp($id, $code);
         if($count > 0)
         {
            $where_arr= array('email_temp_id'=>$id,'lang_code'=>$code); 
            $updatetdata=array( 
            	    'email_temp_id' => $id,
            	    'lang_code'=>$_POST['code'],
                    'subject' => $_POST['template_subject_trans'],
                    'content'=>$_POST['template_content_trans'],
                    'updated_at'=>time(),   
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                 );
                     $vat_id =$this->EmailModel->updateEmailData('multi_lang_email_template',$where_arr,$updatetdata);
                     echo json_encode(array('flag' => 1, 'msg' => "Email Templates Translation Updated Successfully."));
                     exit();
         }
         else
         {
             $insertdata=array( 
                    'email_temp_id' => $id,
            	    'lang_code'=>$_POST['code'],
                    'subject' => $_POST['template_subject_trans'],
                    'content'=>$_POST['template_content_trans'],
                    'created_by'=>$fbc_user_id,
                    'created_at'=>time(),   
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                 );
                  $this->EmailModel->insertData('multi_lang_email_template',$insertdata);
                  echo json_encode(array('flag' => 1, 'msg' => "Email Templates Translation Successfully."));
                  exit();
         }

        
        }   
    }
}
