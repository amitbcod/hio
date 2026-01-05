<?php 

class PublisherController extends CI_Controller
{
    public function __construct()
    {
         parent::__construct();
		 $this->load->model('PublisherModel');
    }
	
	public function publisherList()
	{
        if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/publishers',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }

		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){			
			$data['getPublishers'] = $this->PublisherModel->get_publishers();
			$data['PageTitle']='Publishers';
			$data['side_menu']='publishers';
            $this->load->view('publishers/publishers_list',$data);  
		}else{

			return redirect('/'); 
		}
	}
    function openAdminUserPasswordPopup()
    {
        $data['PageTitle'] = 'Reset Password';
        $this->load->view('forgot_password/forgot_password_new', $data);
    }

    public function addPublishers()
	{
        if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/publishers',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }
        
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){		
			$data['PageTitle']='Publisher Add';
			$data['side_menu']='publisher';
			$this->load->view('publishers/publishers_add');  
		}else{
			return redirect('/'); 
		}
	}

    public function submitPublisher(){
        $SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){		
            $publisher_id = $_POST['publisher_id'];
			if(empty($_POST['email']) || empty($_POST['publication_name'] )|| empty($_POST['vendor_name'] )|| empty($_POST['commision_percent'] )|| empty($_POST['phone_no'] ) )
			{
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
            elseif( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $_POST["email"])) {

                echo json_encode(array('flag'=>0, 'msg'=>"Please enter a valid Email address."));
                exit;
            }
            elseif(($this->PublisherModel->checkEmailidExit($_POST["email"])) != 0 && $publisher_id==''){
    
                echo json_encode(array('flag'=>0, 'msg'=>"Email id already exist."));
                exit;
            }
            else{
    
                if($publisher_id != ''){

                    if($_POST['passwordCheck']=='check' && empty($_POST["password"])){
                        echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
                        exit;
                    }
                    elseif($this->PublisherModel->checkEmailIdExitDuringUpdate($_POST["email"],$publisher_id) !=0){
                        echo json_encode(array('flag'=>0, 'msg'=>"Email id already exist."));
                        exit;
                    }
                    else{

                        $isPasswordChecked = $_POST['passwordCheck'];
                        $hashPassword = "";
                        $updateData = array();
                        if ($isPasswordChecked == 'check'){
                            $hashPassword = md5($_POST["password"]);
                            $updateData=array(  
                                'email'    			=> $_POST['email'],
                                'password'			=> $hashPassword,
                                'publication_name'	=> $_POST['publication_name'],
                                'vendor_name'		=> $_POST['vendor_name'],
                                'commision_percent' => $_POST['commision_percent'],
                                'phone_no' 		    => $_POST['phone_no'],
                                'description'		=> $_POST['description'],
                                'status'			=> $_POST['status'],
                                'updated_at'		=> strtotime(date('Y-m-d H:i:s')),
                                'ip'				=> $_SERVER['REMOTE_ADDR'],
                            );
                        }
                        else{
                            $updateData=array(  
                                'email'    			=> $_POST['email'],
                                'publication_name'	=> $_POST['publication_name'],
                                'vendor_name'		=> $_POST['vendor_name'],
                                'commision_percent' => $_POST['commision_percent'],
                                'phone_no' 		    => $_POST['phone_no'],
                                'description'		=> $_POST['description'],
                                'status'			=> $_POST['status'],
                                'updated_at'		=> strtotime(date('Y-m-d H:i:s')),
                                'ip'				=> $_SERVER['REMOTE_ADDR'],
                            );
                        }
                        $is_success = $this->PublisherModel->update_publishers($updateData,$publisher_id);
                        if($is_success){
                            $url = base_url().'DashboardController/index';
                            echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated","url"=>$url));
                            exit;	
                        }
                        else{
                            echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));
                            exit;
                        }
                    }
                }
                else{
// Add publisher
                    $hashPassword = md5($_POST["password"]);
                    $insertData=array(  
                        'email'    		    => $_POST['email'],
                        'password'			=> $hashPassword,
                        'publication_name'	=> $_POST['publication_name'],
                        'vendor_name'	    => $_POST['vendor_name'],
                        'commision_percent' => $_POST['commision_percent'],
                        'phone_no' 		    => $_POST['phone_no'],
                        'description'		=> $_POST['description'],
                        'status'		    => $_POST['status'],
                        'remove_flag'       =>0,
                        'created_by'        =>$SISA_ID,
                        'created_at'        => strtotime(date('Y-m-d H:i:s')),
                        'ip'				=> $_SERVER['REMOTE_ADDR']                
                    );

                    $is_success = $this->PublisherModel->insert_publishers($insertData);
                    if($is_success){
                        $url = base_url().'publishers';
                        echo json_encode(array('flag' => 1, 'msg' => "Successfully Added","url"=>$url));
                        exit;	
                    }
                    else{
                        echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));
                        exit;
                    }
                }
            }            

		}else{
			return redirect('/'); 
		}
    }

    public function editPublisher($publisherId)
	{
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){	
			if($publisherId){
				$data['publisher'] = $this->PublisherModel->get_publisher_detail($publisherId);
				$data['PageTitle']='Publisher Edit';
				$data['side_menu']='publisher';
				$this->load->view('publishers/publishers_edit',$data);  
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