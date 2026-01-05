<?php 

class GiftMasterController extends CI_Controller
{
    public function __construct()
    {
         parent::__construct();
		 $this->load->model('GiftMasterModel');
    }
	
	public function giftMasterList()
	{
        if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/gift_master',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }

		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){			
			$data['getGiftMaster'] = $this->GiftMasterModel->get_gift_master();
			$data['PageTitle']='Gift Master';
			$data['side_menu']='giftmaster';
            $this->load->view('giftmaster',$data);  
		}else{
			return redirect('/'); 
		}
	}

    public function add_edit_GiftMaster()
	{
        
        // $giftName = $this->CommonModel->custom_filter_input($_POST['name']);
        
        // $giftName_exist_count = $this->GiftMasterModel->checkNameExist($giftName);

        // print_r($giftName_exist_count);die();
        //     if (sizeof($category_exist_count) > 0) {
        //         echo json_encode(array('flag' => 0, 'msg' => "Category Name Already Exist"));
        //         exit;
        //     }
        $is_success = 0;
        $giftMasterId = $_POST['giftMasterId'];
        if ($giftMasterId == ''){
            $insertData=array(  
                'name'    		    => $_POST['name'],
                'created_at'        => strtotime(date('Y-m-d H:i:s')),
                'ip'				=> $_SERVER['REMOTE_ADDR']                
            );
            $is_success = $this->GiftMasterModel->insert_gift_master($insertData);
        }
        else{
            //print_r($_POST);die();
            $updateData=array(  
                'name'    		    => $_POST['name'],
                'updated_at'		=> strtotime(date('Y-m-d H:i:s')),
                'ip'				=> $_SERVER['REMOTE_ADDR'],
            );
            $is_success = $this->GiftMasterModel->update_gift_master($updateData,$giftMasterId);

        }

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