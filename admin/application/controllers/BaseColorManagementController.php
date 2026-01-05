<?php

class BaseColorManagementController extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
		$this->load->model('CommonModel');
		$this->load->model('ColorModel');
        $this->load->model('EavAttributesModel');
		$this->load->model('Multi_Languages_Model');
        $this->load->model('WebshopModel');
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
        if(!empty($this->session->userdata('userPermission')) && !in_array('system/base_color_management',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));
        }
    }
	
	public function index()
	{
        $data['PageTitle']='Base Color Management';
        $data['side_menu']='System';
        $data['color_details'] = $this->ColorModel->getData();
        $this->load->view('base_color_management/color_management',$data);
	
	}

    public function color_details($id = false){

        $fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

        $data['PageTitle']='Base Color Management Details';
        $data['side_menu']='System';

        $attr_codes = 'color';
        $attr_code = $this->EavAttributesModel->getVariantDataByAttrCode($attr_codes,$shop_id);
        
        $attr_id = $attr_code[0]['id'];
        $data['variant'] = $this->EavAttributesModel->get_attributes_options_by_seller($shop_id,$attr_id);

        if(isset($id) && !empty($id)){
            
            $data['action'] = 'update';
			$data['color_detail'] = $this->ColorModel->getData($id);
            $data['variant_deatils'] = $this->ColorModel->getVariantData($id);
			$this->load->view('base_color_management/color_management_details',$data);

        }
        else{

            $data['action'] = 'insert';
            $this->load->view('base_color_management/color_management_details',$data);

        }

    }

    public function submit_color_management_details($id = false){

      
        if(empty($_POST['title']) && !isset($_POST['variant'])) {
            echo json_encode(array('flag'=>0, 'msg'=>"Please check color."));
            exit;
        }
        else{
            if($_POST['action'] == 'insert')
            {
               
                if(!isset($_POST['color_status']) ||  $_POST['color_status'] == '' )
                {
                    $color_status = $_POST['color_status'] = 0;
                }
                else
                {
                    $color_status = $_POST['color_status'] = 1;
                }
                
                $insert_array = array(
                "color_name" => $_POST['title'],
                "square_color" => $_POST['square_color'],
                "status"=> $color_status,
                "created_by" => $_SESSION['LoginID'],
                "created_at" => time(),
                "ip" => $_SERVER['REMOTE_ADDR'],
                );

                $insert_support = $this->ColorModel->insert_data('base_colors',$insert_array);
               
                if($insert_support)
                {
                    $varient_id = $_POST['variant'];
                    foreach($varient_id as $value){
                        $data = array( 
                            "base_color_id" => $insert_support,
                            "variant_option_id" =>  $value, 
                            "created_by" => $_SESSION['LoginID'],
                            "created_at" => time(),
                            "updates_at" => time(),
                            "ip" => $_SERVER['REMOTE_ADDR'],
                        );
                       
                        $inserted_data = $this->ColorModel->insert_data('base_colors_variants',$data); 
                    } 
                    if($inserted_data) 
                    {
                        $redirect = BASE_URL.'base-color-management';
                        echo json_encode(array('flag'=>1, 'msg'=>"Base Color Created Succesfully" , 'redirect'=>$redirect));
                        exit;
                    }
                    else{
                        echo json_encode(array('flag'=>0, 'msg'=>"Adding new Base Color Failed" ));
                        exit;
                    }
                  
                }
                else{
                    echo json_encode(array('flag'=>0, 'msg'=>"Adding new Base Color Failed"));
                    exit;
                }
            }

            else if($_POST['action'] == 'update') {

                $id = $_POST['id']; 

                if(!isset($_POST['color_status']) ||  $_POST['color_status'] == '')
                {
                    $color_status = $_POST['color_status'] = 0;
                }
                else
                {
                    $color_status = $_POST['color_status'] = 1;
                }
                
                    $update_array = array(
                        "color_name" => $_POST['title'],
                        "square_color" => $_POST['square_color'],
                        "status"=> $color_status,
                        "updates_at	" => time(),
                        "ip" => $_SERVER['REMOTE_ADDR'],
                    );

                    $this->ColorModel->update_data($update_array,$id);
                    $delete_data = $this->ColorModel->variant_delete($id);
                    
                    $varient_id = $_POST['variant'];
                    foreach($varient_id as $value) {
                        $variant_update_array = array(
                            "base_color_id" => $id,
                            "variant_option_id" => $value, 
                            "created_by" => $_SESSION['LoginID'],
                            "created_at" => time(),
                            "updates_at" => time(),
                            "ip" => $_SERVER['REMOTE_ADDR'],
                        );
                    
                    $inserted_data = $this->ColorModel->insert_data('base_colors_variants',$variant_update_array);
                    }
                    if($inserted_data)
                    {	
                        $redirect = BASE_URL.'base-color-management';
                        echo json_encode(array('flag'=>1, 'msg'=>"Success",'redirect' => $redirect));
                        exit;
                    }
                    else{
                        echo json_encode(array('flag'=>0, 'msg'=>"updating Base Color Failed"));
                        exit;
                    }
            }
            
        }

    }

    public function delete_Color(){
        if(isset($_POST)){
            if(empty($_POST['id']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please select Base Color id to delete."));
                exit;

            }
            else{
                $id = $_POST['id'];
                $this->ColorModel->delete_data($id);

                echo json_encode(array('flag' => 1, 'msg' => "Deleted Succesfully."));
				exit;
            }
        }
        else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }
    }


    public function checkColorName() {
        if(isset($_POST['title']) &&  $_POST['title']!='')
		{
			if($_POST['action']=='insert'){
				$color_name = $_POST['title'];
                $ColorName_Exist=$this->WebshopModel->getSingleDataByID('base_colors',array('color_name'=>$color_name),'id,color_name');
                if(isset($ColorName_Exist) && $ColorName_Exist->id!=''){
					echo 'false';
                    exit;
				}
                else{
					echo 'true';
                    exit;
				}

			}
            else if($_POST['action']=='update'){

                $id = $_POST['id'];
                $color_name = $_POST['title'];
                $ColorName_Exist=$this->WebshopModel->getSingleDataByID('base_colors',array('color_name'=>$color_name),'id,color_name');
				if(isset($ColorName_Exist) && $ColorName_Exist->id!=$id){
					echo 'false';
                    exit;
				}
                else{
			 		echo 'true';
                    exit;
				}
               
			}
            else{
				echo 'true';exit;
			}
		}
        else
        {
			 echo 'true';
             exit;
		}
    }


    public function variantcolor_deatils()
    {
        $data['PageTitle']='Variant Color';
        $data['side_menu']='System';

        $fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

        $attr_codes = 'color';
        $attr_code = $this->EavAttributesModel->getVariantDataByAttrCode($attr_codes,$shop_id);
        
        $attr_id = $attr_code[0]['id'];
        $data['variant'] = $this->EavAttributesModel->get_attributes_options_by_seller($shop_id,$attr_id);
        //print_r($data['variant']);
        $this->load->view('base_color_management/variant_details',$data);
    }

	
}
