<?php

use League\Csv\Reader;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property EuShippingChargesModel $EuShippingChargesModel
 */
class EuShippingChargesController extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('CommonModel');
		$this->load->helper('url');
		$this->load->model('CustomerModel');
		$this->load->model('WebshopModel');
		$this->load->model('EuShippingChargesModel');

		if(!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] ==''){
			redirect(BASE_URL);
		}
		if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/shipping_charges',$this->session->userdata('userPermission'))){ 
			redirect(base_url('dashboard'));  }

	}

	public function index()
	{
		$data['side_menu']='webShop';
		$data['PageTitle']='Shipping Charges';
		$shop_id =	$this->session->userdata('ShopID');
		$data['shipping_charges_info'] = $this->EuShippingChargesModel->get_all_shipping_methods();
		$this->load->view('webshop/eu_shipping/eu_shipping_charges',$data);
	}

	public function submit_shipping_charges()
	{

		if(empty($_POST['ship_method_name']) )
		{
			echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
			exit;
		}else
		{
			//Insert
			$loginId = $this->session->userdata('LoginID');
			$status = (isset($_POST['status']) && $_POST['status'] == 'on' ? 1 : 0);
			$insertdata=array(
				'ship_method_name'=> isset($_POST['ship_method_name']) ? $_POST['ship_method_name'] : '',
				'status'=> $status,
				'created_by'=> $loginId,
				'created_at'=>time(),
				'ip'=>$_SERVER['REMOTE_ADDR']
			);

			$rowAffected = $this->EuShippingChargesModel->insertData('shipping_methods',$insertdata);
			if($rowAffected){
				$redirect = base_url('webshop/eu-shipping-charges');
				echo json_encode(array('flag' => 1, 'msg' => "Success",'redirect'=>$redirect));
				exit();
			}else{
				echo json_encode(array('flag' => 0, 'msg' => "Nothing to update!"));
				exit;
			}

		}
	}

	function update_shipping_charge(){
        if(isset($_POST))
        {
            if(empty($_POST['ship_method_name']) && empty($_POST['status']) )
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                exit;
            }else{
                $status = (isset($_POST['status']) && $_POST['status'] == 'on' ? 1 : 0);
                $updatedata=array(
                    'ship_method_name' => $_POST['ship_method_name'],
                    'status'=>$status,
                    'updated_at'=>time(),
                );

                $where_arr=array('id'=>$_POST['shipping_charge_id_hidden']);
                $shipping_charge_id = $this->EuShippingChargesModel->updateNewData('shipping_methods',$where_arr,$updatedata);
                $redirect = base_url('webshop/eu-shipping-charges');
                echo json_encode(array('flag' => 1, 'msg' => "Shipping Charge Updated Succesfully.",'redirect'=>$redirect));
				exit();
            }
        }else{
            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }
    }


	public function load_eu_specialpricingajax()
	{
		$shipping_charges = $this->EuShippingChargesModel->get_all_shipping_methods();
		$data = array();
		if (isset($shipping_charges) && !empty($shipping_charges))
		{
			foreach ($shipping_charges as $readData)
			{
				$row = array();
				$row[]=$readData['id'];
				$row[]=$readData['ship_method_name'];
				$row[]=($readData['status'] == 1) ? 'Enabled' : 'Disabled' ;

				if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/shipping_charges/write',$this->session->userdata('userPermission'))){  //Bipin
			   $row[]= '-';
		   }else{
				$row[]='<a class="link-purple trash"  href="javascript:void(0);"  onclick="deleteShippingCharge('.$readData['id'].');" >Delete</a>';
			}
				// $row[]='<a class="link-purple view_shipping_charge_detail" id="'.$readData['id'].'"  href="javascript:void(0);" >View</a>';
				$row[]='<a class="link-purple view_shipping_charge_detail" id="'.$readData['id'].'"  href="javascript:void(0);" onclick="editShippingCharge('.$readData['id'].');" >View</a>';
				$data[] = $row;
			}

			// echo "<pre>"; print_r($data);// die();
			$output = array(
							"data" => $data,
					);
			//output to json format
			echo json_encode($output);
			exit;
		}else{
			$output = array(
							"data" => $data,
					);
			//output to json format
			echo json_encode($output);
			exit;
		}
	}

	public function delete_shipping_charge()
	{
        if(isset($_POST)){
            if(empty($_POST['id']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please select Shipping Charge id to delete."));
                exit;
            }else{
                $updatedata=array(
                    'remove_flag' => 1,
                    'updated_at'=>time(),
                );
                $where_arr=array('id'=>$_POST['id']);
                $shipping_charge_id = $this->EuShippingChargesModel->updateNewData('shipping_methods',$where_arr,$updatedata);
                 echo json_encode(array('flag' => 1, 'msg' => "Shipping Charge Deleted Succesfully."));
				exit();
            }
        }else{
            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }
	}

	public function get_shipping_charge_data()
	{
		if(empty($_POST['shipping_charge_id']))
       {
            echo json_encode(array('flag' => 0, 'msg' => "Please select Shipping Charge Id."));
            exit;
        }else
        {
		    $ShippingChargeData = $this->EuShippingChargesModel->getSingleDataByID('shipping_methods',array('id'=>$_POST['shipping_charge_id']),'');

            if(!empty($ShippingChargeData)){
                echo json_encode(array('flag' => 1, 'response' => $ShippingChargeData));
                exit();
            }else{
                echo json_encode(array('flag' => 1, 'response' => 'No Data found.' ));
                exit();
            }
        }

	}

	function OpenDownloadPopup(){
		$data['PageTitle']='Sample - CSV Import';
		$data['side_menu']='bulk-add';
		$View = $this->load->view('webshop/eu_shipping/eu-shipping-download-popup', $data, true);
		$this->output->set_output($View);
	}

	function OpenUploadPopup(){
		$data['PageTitle']='Shipping Rates - CSV Import';
		$data['side_menu']='bulk-add';
		$View = $this->load->view('webshop/eu_shipping/eu-shipping-upload-popup', $data, true);
		$this->output->set_output($View);
	}

	function DownloadSampleCSV()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		// $shipping_methods_charges=$this->EuShippingChargesModel->getMultiDataById('shipping_methods_charges',array('ship_method_id'=>$ship_method_id),'');
		$shipping_methods_charges=$this->EuShippingChargesModel->get_all_shipping_methods_charges();
		$sis_export_header = array("country_code","shipping_method_id","weight_in_kg","shipping_rate","no_of_delivery_days");
		// print_r($shipping_methods_charges);die();
			if(isset($shipping_methods_charges) && $shipping_methods_charges!=''){

				$ExportValuesArr=array();
				foreach($shipping_methods_charges as $shipping_methods_charge){

					$country_code=$shipping_methods_charge['country_code'];
					$ship_method_id=$shipping_methods_charge['ship_method_id'];
					$weight_in_kg=$shipping_methods_charge['weight'];
					$ship_rate=$shipping_methods_charge['ship_rate'];
					$delivery_days=$shipping_methods_charge['delivery_days'];

					$SingleRow=array("$country_code","$ship_method_id","$weight_in_kg","$ship_rate","$delivery_days");



					$ExportValuesArr[]=$SingleRow;


				}

			// echo "<pre>"; print_r_custom($ExportValuesArr);exit;

			}

			$filename = 'SISProducts-Sample-Shipping-CSV' . time() . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename");
			header("Content-Type: application/csv; ");

			// file creation
			$file = fopen('php://output', 'w');

			fputcsv($file, $sis_export_header);

			if(isset($ExportValuesArr) && count($ExportValuesArr)>0){

				foreach ($ExportValuesArr as $readData) {
					fputcsv($file, $readData);
				}
			}
			fclose($file);
			exit;

			echo "success";exit;

	}

	function CheckCSVShippingData()
	{
		if(!empty($_FILES['upload_csv_file']['name']))
		{
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$appne_msg='';

			$allowed =  array('csv'); //you can mentions all the allowed file format you need to accept, like .jpg, gif.
			$filename = $_FILES['upload_csv_file']['name']; // csv_file is the file name on the form

			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if(!in_array($ext,$allowed) ) {
				$appne_msg.=" Please Upload Files With .CSV Extenion Only.";
				$arrResponse  = array('status' =>400 ,'message'=>$appne_msg);
					echo json_encode($arrResponse);exit;
			}

			$shipping_method_id_not_found = array();
			$country_code_not_found = array();
			$shipping_charge_data=array();

			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			$count=0;
			$headers='';
			 while($row = fgetcsv($file_data))
			 {

				  $headers = $row;
				  if($count==0){
					  break;
				  }
				  $count++;
			 }

			 // print_r($headers);die();
			  while($row = fgetcsv($file_data))
			 {
			  $shipping_charge_data[] = $row;
			 }


			 if(isset($shipping_charge_data) && count($shipping_charge_data)>=1){
				foreach($shipping_charge_data as $index=>$shipping_charge)
				{
					// $sku=$product[0];
					if(in_array("country_code", $headers))
					{
						$key1 = array_search('country_code', $headers);
						$country_code= $shipping_charge[$key1];
						if(!isset($country_code) || $country_code == '' ){
							$arrResponse  = array('status' =>400 ,'message'=>'Data in Country_code column is misssing');
							echo json_encode($arrResponse);exit;
						}
					}else
					{
						$arrResponse  = array('status' =>400 ,'message'=>'Country_code column is misssing');
							echo json_encode($arrResponse);exit;
					}

					if($country_code!='' ){
						$countryCodeLength= strlen($country_code);
						if($countryCodeLength == 2 ){

						}else{
							$country_code_not_found[]=$country_code;
						}
					}else
					{
						$appne_msg.=" Please upload proper csv file.";
						$arrResponse  = array('status' =>400 ,'message'=>$appne_msg);
					echo json_encode($arrResponse);exit;
					}

					if(in_array("shipping_method_id", $headers))
					{
						$key1 = array_search('shipping_method_id', $headers);
						$shipping_method_id= $shipping_charge[$key1];
						if(!isset($shipping_method_id) || $shipping_method_id == '' ){
							$arrResponse  = array('status' =>400 ,'message'=>'Data in shipping_method_id column is misssing');
							echo json_encode($arrResponse);exit;
						}
					}else
					{
						$arrResponse  = array('status' =>400 ,'message'=>'shipping_method_id column is misssing');
							echo json_encode($arrResponse);exit;
					}

					if($shipping_method_id!='' ){

						$check_shipping_method_id=$this->EuShippingChargesModel->getSingleDataByID('shipping_methods',array('id'=>$shipping_method_id,'remove_flag' => 0),'id');
						// print_r($check_shipping_method_id);die();
						if(isset($check_shipping_method_id) && $check_shipping_method_id->id!=''){

						}else{
							$shipping_method_id_not_found[]=$shipping_method_id;
						}
					}else
					{
						$appne_msg.=" Please upload proper csv file.";
						$arrResponse  = array('status' =>400 ,'message'=>$appne_msg);
					echo json_encode($arrResponse);exit;
					}

					if(in_array("weight_in_kg", $headers))
					{
						$key1 = array_search('weight_in_kg', $headers);
						$weight_in_kg= $shipping_charge[$key1];
						if(!isset($weight_in_kg) || $weight_in_kg == '' ){
							$arrResponse  = array('status' =>400 ,'message'=>'Data in weight_in_kg column is misssing');
							echo json_encode($arrResponse);exit;
						}
					}else
					{
						$arrResponse  = array('status' =>400 ,'message'=>'weight_in_kg column is misssing');
							echo json_encode($arrResponse);exit;
					}

					if(in_array("shipping_rate", $headers))
					{
						$key1 = array_search('shipping_rate', $headers);
						$shipping_rate= $shipping_charge[$key1];
						if(!isset($shipping_rate) || $shipping_rate == '' ){
							$arrResponse  = array('status' =>400 ,'message'=>'Data in shipping_rate column is misssing');
							echo json_encode($arrResponse);exit;
						}
					}else
					{
						$arrResponse  = array('status' =>400 ,'message'=>'shipping_rate column is misssing');
							echo json_encode($arrResponse);exit;
					}




				}
			 }

				if(count($country_code_not_found)>0){
					 $country_code_str=implode(', ',$country_code_not_found);
					  $appne_msg.="In 'country_code' column value '$country_code_str' not found in database.";
				 }

				if(count($shipping_method_id_not_found)>0){
					 $shipping_method_id_str=implode(', ',$shipping_method_id_not_found);
					  $appne_msg.="In 'shipping_method_id' column value '$shipping_method_id_str' not found in database.";
				 }

				  if(count($shipping_charge_data)<=0){
				   $appne_msg.=" shipping charge data not found.";
				  }

				 if($appne_msg !=''){
					 $arrResponse  = array('status' =>400 ,'message'=>$appne_msg);
					echo json_encode($arrResponse);exit;
				 }else{
					 $arrResponse  = array('status' =>200 ,'message'=>"shipping_charge_data sheet validated, Please continue to upload.");
					echo json_encode($arrResponse);exit;
				 }

		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Please upload proper csv file');
				echo json_encode($arrResponse);exit;
		}

	}

	function UpdateShippingData()
	{
		if(empty($_FILES['upload_csv_file']['name'])) {
			return;
		}

		$csv = Reader::createFromPath($_FILES['upload_csv_file']['tmp_name'], 'r');
		$csv->setHeaderOffset(0);


		if($csv->count() <= 1) {
			$arrResponse = array('status' => 400, 'message' => 'Please have some data in csv file');
			echo json_encode($arrResponse);
			exit;
		}

		$this->EuShippingChargesModel->start_transaction();

		$this->EuShippingChargesModel->delete_all_shipping_method_charges();

		foreach ($csv as $shipping_charge_record) {
			foreach(['country_code', 'shipping_method_id', 'weight_in_kg', 'shipping_rate'] as $requiredField) {
				if (empty($shipping_charge_record[$requiredField])) {
					echo json_encode(['status' => 400, 'message' => "Data in $requiredField column is misssing"]);
					exit;
				}
			}

			$insertdata = array(
				'country_code' => $shipping_charge_record['country_code'],
				'ship_method_id' => $shipping_charge_record['shipping_method_id'],
				'weight' => $shipping_charge_record['weight_in_kg'],
				'ship_rate' => $shipping_charge_record['shipping_rate'],
				'delivery_days' => $shipping_charge_record['no_of_delivery_days'],
				'cart_value_2' => !empty($shipping_charge_record['cart_value_2'])  ? $shipping_charge_record['cart_value_2'] : null,
				'rate_2' => !empty($shipping_charge_record['rate_2']) || $shipping_charge_record['rate_2'] === '0' ? $shipping_charge_record['rate_2'] : null,
				'cart_value_3' => !empty($shipping_charge_record['cart_value_3']) ? $shipping_charge_record['cart_value_3'] : null,
				'rate_3' => !empty($shipping_charge_record['rate_3']) || $shipping_charge_record['rate_3'] === '0' ? $shipping_charge_record['rate_3'] : null,
				'created_by' => $this->session->userdata('LoginID') ?? '',
				'created_at' => time(),
				'ip' => $_SERVER['REMOTE_ADDR']
			);
			$this->EuShippingChargesModel->insertData('shipping_methods_charges', $insertdata);
		}
		$this->EuShippingChargesModel->complete_transaction();
		$arrResponse = array('status' => 200, 'message' => 'Shipping methods charges Imported Successfully.');
		echo json_encode($arrResponse);
		exit;
	}

}
