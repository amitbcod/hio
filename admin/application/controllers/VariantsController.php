<?php 

class VariantsController extends CI_Controller
{
    public function __construct()
    {
         parent::__construct();
		 $this->load->model('EavAttributesModel');
    }
	
	public function varaintList()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/variants',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }

		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){			
			$data['getAttribute'] = $this->EavAttributesModel->get_variant_masters();
			$data['PageTitle']='Variants';
			$data['side_menu']='variants';
			$this->load->view('variants/variants_list',$data);  
		}else{
			return redirect('/'); 
		}
	}

	public function addVariants()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/variants',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }
		
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){		
			$data['PageTitle']='Variant Add';
			$data['side_menu']='variant';
			$this->load->view('variants/variants_add');  
		}else{
			return redirect('/'); 
		}
	}

	public function editVariant($attributeId)
	{
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){	
			if($attributeId){
				$Vairant_ID_DATA= $this->EavAttributesModel->getSingleDataByID('eav_attributes',array('id'=>$attributeId),'*');
				$data['attribute'] = $this->EavAttributesModel->get_attribute_detail($attributeId);
				$valuesArray = $this->EavAttributesModel->get_attribute_option_values($attributeId);				
				if($Vairant_ID_DATA == '' || $valuesArray == ''){
					redirect('dashboard');

				}
				$strValues = implode(",",array_map(function($a) {return implode("~",$a);},$valuesArray));

				$data['attributevalues']=$strValues;
				$data['PageTitle']='Variant Edit';
				$data['side_menu']='variant';
				$this->load->view('variants/variants_edit',$data);  
			}else{
				return redirect('/'); 
			}
		}else{
			return redirect('/');
		}
	}

	public function getAttribute()
	{	
		$attribute_id = $_POST['id'];	
		if($attribute_id){
			$attributevalues = $this->AttributeModel->getAttributeValues($attribute_id);
			echo json_encode(array('flag'=>1, 'data'=>$attributevalues));
			exit;
		}else{
			echo json_encode(array('flag'=>0));
			exit;
		}
	}

	public function submitVariant()
	{
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){
			if(empty($_POST['attribute_name']) || empty($_POST['attribute_code']) )
			{
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			elseif(empty($_POST['tagsValues']) && empty($_POST['tagsnewValues']) ){

				echo json_encode(array('flag'=>0, 'msg'=>"Please enter variant values"));
				exit;
			}
			else{
				$array_variant = $_POST;
				$variant_id = $_POST['attribute_id'];
				$variant_code = $_POST['attribute_code'];
		    	if($variant_id!= ''){
					$is_success = $this->EavAttributesModel->insert_update_attributes($array_variant,$SISA_ID,$variant_id,2);
					if($is_success){

						$strTags='';
						if($array_variant['tagsnewValues'] !='' && $array_variant['tagsValues'] !=''){
							$strTags=$array_variant['tagsValues'].",".$array_variant['tagsnewValues'];
						}
						elseif($array_variant['tagsnewValues'] !=''){
							$strTags = $array_variant['tagsnewValues'];
						}
						elseif($array_variant['tagsValues'] !=''){
							$strTags = $array_variant['tagsValues'];
						}
						 $this->db->delete('eav_attributes_options',['attr_id'=>$variant_id]);
						if($strTags != '')
						{
							$tagsValues = explode(',', $strTags);
							foreach ($tagsValues as $key => $value) {
								if($value != ''){
									$this->EavAttributesModel->insert_attributes_option_value($value,$variant_id,$SISA_ID);
								}
							}
						}
						echo json_encode("update");
                        exit;
						// $url = base_url().'variants';
						// echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated","url"=>$url));
						// exit;		
					}
					else{		
						echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));
						exit;
					}
		    	}else{
					//insert variant
					$codeFund = $this->EavAttributesModel->getAttributeCode($variant_code,'2');
					if($codeFund){
						echo json_encode(array('flag' => 0, 'msg' => "Variant Code Exist!"));
						exit;
					}elseif($this->EavAttributesModel->getVariantNameCode($_POST["attribute_name"]) != 0){
						echo json_encode(array('flag' => 0, 'msg' => "Variant Name Exist!"));
						exit;
					}	
					$is_success = $this->EavAttributesModel->insert_update_attributes($array_variant,$SISA_ID,$variant_id,2);
					if($is_success){
						$insert_id = $this->db->insert_id();
						$tagsValues = explode(',', $_POST['tagsValues']);
						foreach ($tagsValues as $key => $value) {
							if($value != ''){
								$this->EavAttributesModel->insert_attributes_option_value($value,$insert_id,$SISA_ID);
							}
						}
						echo json_encode("insert");
                        exit;
						// $url = base_url().'variants';
						// echo json_encode(array('flag' => 1, 'msg' => "Successfully Added","url"=>$url));
						// exit;				
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
	
	public function SubscriptionList()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/variants',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }

		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){			
			$data['getAttribute'] = $this->EavAttributesModel->get_subscription_attribute_option_values(6);
			// print_R($data['getAttribute'] );die();
			$data['PageTitle']='Variants';
			$data['side_menu']='variants';
			$this->load->view('variants/subscription_list.php',$data);  
		}else{
			return redirect('/'); 
		}
	}

	public function submitSubscribtionTime()
	{
		// print_r($_POST);die();
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){
			if(empty($_POST['eav_option_id']) || empty($_POST['eav_option_name']) )
			{
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			else{
				$eav_option_id = $_POST['eav_option_id'];
				$eav_option_name = $_POST['eav_option_name'];
				$sub_time = $_POST['sub_time'];
				$is_exist = $this->EavAttributesModel->check_if_sub_time_exist($eav_option_id);
				// print_r($is_exist); die();
		    	if($is_exist){
					
						$update_array = array(

							"eav_option_id" => $eav_option_id,
							"eav_option_name" =>  $eav_option_name,
							"sub_time" =>  $sub_time,
							"updated_at" => time(),
							"updated_by" => $SISA_ID
						);

						$update_sub_time =  $this->EavAttributesModel->update_sub_time($update_array,$is_exist['id']);
						echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated"));
						exit;
						// $url = base_url().'variants';
						// echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated","url"=>$url));
						// exit;		
				}else{
					$insert_array = array(

						"eav_option_id" => $eav_option_id,
						"eav_option_name" =>  $eav_option_name,
						"sub_time" =>  $sub_time,
						"created_at" => time(),
						"created_by" => $SISA_ID
					);
					$update_sub_time =  $this->EavAttributesModel->insert_sub_time($insert_array);
					echo json_encode(array('flag' => 1, 'msg' => "Successfully Inserted"));
					exit;
						// $url = base_url().'variants';
						// echo json_encode(array('flag' => 1, 'msg' => "Successfully Added","url"=>$url));
						// exit;				
				}
						
			}	
		}
		else{
			return redirect('/'); 
		}
	}
}
