<?php

class Multi_Currencies_Controller extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
		$this->load->model('CommonModel');
		$this->load->model('Multi_Currencies_Model');
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}

    }
	
	public function index()
	{
		
			$data['currenciesListing'] = $this->Multi_Currencies_Model->get_all_currencies();

            $data['country_master']= $this->CommonModel->get_countries();

			$data['PageTitle']='Multi Currencies Settings';
			$data['side_menu']='System';
            // print_r($data['currenciesListing']);die();
			$this->load->view('multi_currencies/listing.php',$data);
		
	}

    public function create_multi_currencies(){

        if(isset($_POST)){

            $fbc_user_id = $_SESSION['LoginID'];

            if(empty($_POST['currency_name']) && empty($_POST['currency_code']) && empty($_POST['currency_conversion_rate']) && empty($_POST['currency_symbol']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                exit;

            }else{


                $is_default_currency = (isset($_POST['is_default_currency']) && $_POST['is_default_currency'] == 'on' ? 1 : 0);
                $status = (isset($_POST['status']) && $_POST['status'] == 'on' ? 1 : 0);




                $insertdata=array(	
                    'name' => $_POST['currency_name'],
                    'code'=>$_POST['currency_code'],
                    'conversion_rate'=>$_POST['currency_conversion_rate'],
                    'symbol'=>$_POST['currency_symbol'],              
                    'status'=>$status,
                    'created_by'=>$fbc_user_id, 
                    'created_at'=>time(),
                    'ip'=>$_SERVER['REMOTE_ADDR']
                 );

                 $vat_id = $this->Multi_Currencies_Model->insertData('multi_currencies',$insertdata);

                 echo json_encode(array('flag' => 1, 'msg' => "Currency Created Succesfully."));
					exit();
               



            }
        }else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }
    }


    function update_currency(){

        if(isset($_POST)){

           

            if(empty($_POST['currency_name_update']) && empty($_POST['currency_code_update']) && empty($_POST['currency_conversion_rate_update']) && empty($_POST['currency_symbol_update']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                exit;

            }else{

                $is_default_currency_update = (isset($_POST['is_default_currency_update']) && $_POST['is_default_currency_update'] == 'on' ? 1 : 0);
                $status = (isset($_POST['status_update']) && $_POST['status_update'] == 'on' ? 1 : 0);

                if ($is_default_currency_update==1) {
                    $cur_id=$this->Multi_Currencies_Model->Update_default_currency();
                }

                    

        
                $updatedata=array(	
                    'name' => $_POST['currency_name_update'],
                    'code'=>$_POST['currency_code_update'],
                    'is_default_currency'=> $is_default_currency_update,
                    'conversion_rate'=>$_POST['currency_conversion_rate_update'],
                    'symbol'=>$_POST['currency_symbol_update'],              
                    'status'=>$status,                      
                    'updated_at'=>time(),
                );

                $where_arr=array('id'=>$_POST['currency_id_hidden']); 
                $vat_id = $this->Multi_Currencies_Model->updateData('multi_currencies',$where_arr,$updatedata);
                


                echo json_encode(array('flag' => 1, 'msg' => "Currency Updated Succesfully."));
				exit();
               

            }
        }else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }

    }



    public function get_currency_data()
	{
		if(empty($_POST['id']))
       {
            echo json_encode(array('flag' => 0, 'msg' => "Please select vat Id."));
            exit;

        }else{
		    $currencyData = $this->Multi_Currencies_Model->getSingleDataByID('multi_currencies',array('id'=>$_POST['id']),'');

            if(!empty($currencyData)){
                echo json_encode(array('flag' => 1, 'response' => $currencyData));
                exit();
            }else{
                echo json_encode(array('flag' => 1, 'response' => 'No Data found.' ));
                exit();
            }
           
    
        }
		
	}


    public function delete_currency(){

        if(isset($_POST)){

            if(empty($_POST['id']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please select currency id to delete."));
                exit;

            }else{

                $updatedata=array(	
                    'remove_flag' => 1,      
                    'updated_at'=>time(),
                );

                $where_arr=array('id'=>$_POST['id']); 
                $vat_id = $this->Multi_Currencies_Model->updateData('multi_currencies',$where_arr,$updatedata);

                 echo json_encode(array('flag' => 1, 'msg' => "Currency Deleted Succesfully."));
				exit();
               

            }
        }else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }

    }


}
