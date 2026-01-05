<?php 

class AttributeController extends CI_Controller
{
    public function __construct()
    {
         parent::__construct();
		 $this->load->model('EavAttributesModel');
    }
	
	public function attributeList()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/attributes',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }

		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){			
			$data['getAttribute'] = $this->EavAttributesModel->get_attributes_masters();
			$data['PageTitle']='Attribute';
			$data['side_menu']='attribute';
			$this->load->view('attribute/attribute_list',$data);  
		}else{
			return redirect('/'); 
		}
	}

	public function addAttribute()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('database/attributes',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }
		
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){		
			$data['PageTitle']='Attribute Add';
			$data['side_menu']='attribute';
			$this->load->view('attribute/attribute_add');  
		}else{
			return redirect('/'); 
		}
	}

	public function editAttribute($attributeId)
	{
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){	
			if($attributeId){
				$Att_ID_DATA= $this->EavAttributesModel->getSingleDataByID('eav_attributes',array('id'=>$attributeId),'*');
				$data['attribute'] = $this->EavAttributesModel->get_attribute_detail($attributeId);
				$valuesArray = $this->EavAttributesModel->get_attribute_option_values($attributeId);	
				if($Att_ID_DATA == '' || $valuesArray == ''){
					redirect('dashboard');

				}			
				$strValues = implode(",",array_map(function($a) {return implode("~",$a);},$valuesArray));
				$data['attributevalues']=$strValues;
				$data['PageTitle']='Attribute Edit';
				$data['side_menu']='attribute';
				$this->load->view('attribute/attribute_edit',$data);  
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

	public function submitAttribute()
	{
		$SISA_ID=$this->session->userdata('LoginID');
		if($SISA_ID){
			if(empty($_POST['attribute_name']) || empty($_POST['attribute_code'])|| empty($_POST['attribute_properties'] ) )
			{
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
				exit;
			}
			elseif(($_POST['attribute_properties']== '6' || $_POST['attribute_properties']=='5') && (empty($_POST['tagsValues']) && empty($_POST['tagsnewValues'])) ){
				echo json_encode(array('flag'=>0, 'msg'=>"Please enter attribute values"));
				exit;
			}
			else{
				$array_attributes = $_POST;
				$attribute_id = $_POST['attribute_id'];
				$attribute_code = $_POST['attribute_code'];

		    	if($attribute_id!= ''){

					$is_success = $this->EavAttributesModel->insert_update_attributes($array_attributes,$SISA_ID,$attribute_id,1);
					if($is_success){
						if($_POST['attribute_properties'] == 5 || $_POST['attribute_properties'] == 6){
							$strTags='';
							if($array_attributes['tagsnewValues'] !='' && $array_attributes['tagsValues'] !=''){
								$strTags=$array_attributes['tagsValues'].",".$array_attributes['tagsnewValues'];
							}
							elseif($array_attributes['tagsnewValues'] !=''){
								$strTags = $array_attributes['tagsnewValues'];
							}
							elseif($array_attributes['tagsValues'] !=''){
								$strTags = $array_attributes['tagsValues'];
			
							}
							 $this->db->delete('eav_attributes_options',['attr_id'=>$attribute_id]);
							if($strTags != '')
							{
								$tagsValues = explode(',', $strTags);
								foreach ($tagsValues as $key => $value) {
									if($value != ''){
										$this->EavAttributesModel->insert_attributes_option_value($value,$attribute_id,$SISA_ID);
									}
								}
							}
						}
						echo json_encode("update");
                            exit;
						// $url = base_url().'attribute';
						// echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated","url"=>$url));
						// exit;		
					}
					else{
						echo json_encode(array('flag' => 0, 'msg' => "Something went wrong. Please try again"));
						exit;
					}
		    	}else{
					//Insert attribute
					$codeFund = $this->EavAttributesModel->getAttributeCode($attribute_code,'1');
					if($codeFund){
						echo json_encode(array('flag' => 0, 'msg' => "Attribute Code Exist!"));
						exit;
					}
					elseif($this->EavAttributesModel->getAttributeName($_POST["attribute_name"]) != 0){
						echo json_encode(array('flag' => 0, 'msg' => "Attribute Name Exist!"));
						exit;
					}
					$is_success = $this->EavAttributesModel->insert_update_attributes($array_attributes,$SISA_ID,$attribute_id,1);
					if($is_success){
						$insert_id = $this->db->insert_id();
						if($_POST['attribute_properties'] == 5 || $_POST['attribute_properties'] == 6){
	
							$tagsValues = explode(',', $_POST['tagsValues']);
							foreach ($tagsValues as $key => $value) {
								if($value != ''){
	
									$this->EavAttributesModel->insert_attributes_option_value($value,$insert_id,$SISA_ID);
								}
							}
						}
						echo json_encode("insert");
                            exit;
						// $url = base_url().'attribute';
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
}
