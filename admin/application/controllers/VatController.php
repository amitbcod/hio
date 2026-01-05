<?php

class VatController extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
		$this->load->model('CommonModel');
		$this->load->model('VatModel');
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}

        if(!empty($this->session->userdata('userPermission')) && !in_array('system/vat_settings',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard'));
        }
    }
	
	public function index()
	{
		
			$data['vatListing'] = $this->VatModel->get_all_vat();

            $data['country_master']= $this->CommonModel->get_countries();

			$data['PageTitle']='VAT Settings';
			$data['side_menu']='System';
			$this->load->view('vat/listing.php',$data);
		
	}

    public function create_vat(){

        if(isset($_POST)){

            $fbc_user_id = $_SESSION['LoginID'];

            if(empty($_POST['country_code']) && empty($_POST['vat_percentage']) && empty($_POST['deduct_vat']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                exit;

            }else{

                $is_eu_country = (isset($_POST['is_eu_country']) && $_POST['is_eu_country'] == 'on' ? 1 : 0);

                $insertdata=array(	
                    'country_code' => $_POST['country_code'],
                    'is_eu_country'=>$is_eu_country,
                    'vat_percentage'=> $_POST['vat_percentage'],
                    'deduct_vat'  =>$_POST['deduct_vat'],              
                    'created_by'=>$fbc_user_id,
                    'created_at'=>time(),
                    'ip'=>$_SERVER['REMOTE_ADDR']
                 );

                 $vat_id = $this->VatModel->insertData('vat_settings',$insertdata);

                 echo json_encode(array('flag' => 1, 'msg' => "Vat Created Succesfully."));
					exit();
               



            }
        }else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }
    }


    function update_vat(){

        if(isset($_POST)){

           

            if(empty($_POST['country_code_update']) && empty($_POST['vat_percentage_update']) && empty($_POST['deduct_vat_update']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                exit;

            }else{

                $is_eu_country = (isset($_POST['is_eu_country_update']) && $_POST['is_eu_country_update'] == 'on' ? 1 : 0);
        
                $updatedata=array(	
                    'country_code' => $_POST['country_code_update'],
                    'is_eu_country'=>$is_eu_country,
                    'vat_percentage'=> $_POST['vat_percentage_update'],
                    'deduct_vat'  =>$_POST['deduct_vat_update'],                        
                    'updated_at'=>time(),
                );

                $where_arr=array('id'=>$_POST['vat_id_hidden']); 
                $vat_id = $this->VatModel->updateData('vat_settings',$where_arr,$updatedata);

                echo json_encode(array('flag' => 1, 'msg' => "Vat Updated Succesfully."));
				exit();
               

            }
        }else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }

    }



    public function get_vat_data()
	{
		if(empty($_POST['vat_id']))
       {
            echo json_encode(array('flag' => 0, 'msg' => "Please select vat Id."));
            exit;

        }else{
		    $vatData = $this->VatModel->getSingleDataByID('vat_settings',array('id'=>$_POST['vat_id']),'');
            if(!empty($vatData)){
                echo json_encode(array('flag' => 1, 'response' => $vatData));
                exit();
            }else{
                echo json_encode(array('flag' => 1, 'response' => 'No Data found.' ));
                exit();
            }
           
    
        }
		
	}


    public function delete_vat(){

        if(isset($_POST)){

            if(empty($_POST['vat_id']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please select vat id to delete."));
                exit;

            }else{

                $updatedata=array(	
                    'remove_flag' => 1,      
                    'updated_at'=>time(),
                );

                $where_arr=array('id'=>$_POST['vat_id']); 
                $vat_id = $this->VatModel->updateData('vat_settings',$where_arr,$updatedata);

                 echo json_encode(array('flag' => 1, 'msg' => "Vat Deleted Succesfully."));
				exit();
               

            }
        }else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }

    }


}
