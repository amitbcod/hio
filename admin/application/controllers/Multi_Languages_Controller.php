<?php

class Multi_Languages_Controller extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
		$this->load->model('CommonModel');
		$this->load->model('Multi_Languages_Model');
        $this->load->model('WebshopModel');
        $this->load->model('CategoryModel');
        $this->load->model('EavAttributesModel');
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
    }

    public function index()
	{
        if(!empty($this->session->userdata('userPermission')) && !in_array('system/multi_languages',$this->session->userdata('userPermission'))){ 
           redirect(base_url('dashboard'));  }

			$data['languagesListing'] = $this->Multi_Languages_Model->get_all_languages();
            $data['country_master']= $this->CommonModel->get_countries();
			$data['PageTitle']='Multi Languages Settings';
			$data['side_menu']='System';
            
			$this->load->view('multi_languages/listing.php',$data); 
	}

	 public function create_multi_languages(){

        if(isset($_POST)){

            $fbc_user_id = $_SESSION['LoginID'];

            if(empty($_POST['name']) && empty($_POST['code']) && empty($_POST['display_name']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                exit;

            }else{


                $is_default_language = (isset($_POST['is_default_language']) && $_POST['is_default_language'] == 'on' ? 1 : 0);
                $status = (isset($_POST['status']) && $_POST['status'] == 'on' ? 1 : 0);
                $is_communication_language = (isset($_POST['is_communication_language']) && $_POST['is_communication_language'] == 'on' ? 1 : 0);


                $insertdata=array(
                    'name' => $_POST['name'],
                    'display_name' => $_POST['display_name'],
                    'code'=>$_POST['code'],
                    'status'=>$status,
                    'is_communication_language'=>$is_communication_language,
                    'created_by'=>$fbc_user_id,
                    'created_at'=>time(),
                    'ip'=>$_SERVER['REMOTE_ADDR']
                 );

                 $vat_id = $this->Multi_Languages_Model->insertData('multi_languages',$insertdata);

                 echo json_encode(array('flag' => 1, 'msg' => "Language Created Succesfully."));
					exit();

            }
        }else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }
    }

    function update_language(){

        if(isset($_POST)){
            if(empty($_POST['name']) && empty($_POST['code']) && empty($_POST['display_name']))
            {
                echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
                exit;

            }else{

                $is_default_language_update = (isset($_POST['is_default_language_update']) && $_POST['is_default_language_update'] == 'on' ? 1 : 0);

                $status = (isset($_POST['status_update']) && $_POST['status_update'] == 'on' ? 1 : 0);
                $is_communication_language = (isset($_POST['is_communication_language']) && $_POST['is_communication_language'] == 'on' ? 1 : 0);

                if ($is_default_language_update==1) {
                    $lan_id=$this->Multi_Languages_Model->Update_default_language();
                }

                $updatedata=array(
                    'name' => $_POST['name'],
                    'code'=>$_POST['code'],
                    'display_name'=>$_POST['display_name'],
                    'is_default_language'=> $is_default_language_update,
                    'status'=>$status,
                    'is_communication_language'=>$is_communication_language,  
                    'updated_at'=>time(),
                );

                $where_arr=array('id'=>$_POST['language_id_hidden']);
                $vat_id = $this->Multi_Languages_Model->updateData('multi_languages',$where_arr,$updatedata);

                echo json_encode(array('flag' => 1, 'msg' => "Language Updated Succesfully."));
				exit();
            }
        }else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }

    }
    public function get_language_data()
	{
		if(empty($_POST['id']))
       {
            echo json_encode(array('flag' => 0, 'msg' => "Please select vat Id."));
            exit;

        }else{
		    $languageData = $this->Multi_Languages_Model->getSingleDataByID('multi_languages',array('id'=>$_POST['id']),'');
            if(!empty($languageData)){
                echo json_encode(array('flag' => 1, 'response' => $languageData));
                exit();
            }else{
                echo json_encode(array('flag' => 1, 'response' => 'No Data found.' ));
                exit();
            }

        }

	}

public function delete_language(){

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
                $vat_id = $this->Multi_Languages_Model->updateData('multi_languages',$where_arr,$updatedata);

                 echo json_encode(array('flag' => 1, 'msg' => "Language Deleted Succesfully."));
				exit();
            }
        }else{

            echo json_encode(array('flag' => 0, 'msg' => "Error while processing."));
			exit;
        }

    }

    public function languageTranslate()
    {
        if(!empty($this->session->userdata('userPermission')) && !in_array('seller/catalog_translation',$this->session->userdata('userPermission'))){ 
            redirect(base_url('dashboard')); }

        $data = array();
        $data['side_menu']='languageTranslate';
        $data['PageTitle']='Category Translation';
        $data['browse_category'] = $this->WebshopModel->getAllCategories();
        $data['languagesListing'] = $this->Multi_Languages_Model->getLanguages();
        $this->load->view('multi_languages/language-category.php',$data); 
    }

    function openeditcategorypopup(){

        if(isset($_POST['id']) && $_POST['id']!=''){
            $data['category_id']=$category_id=$_POST['id'];
            $data['CategoryDetail']=$this->CategoryModel->get_category_detail($category_id);
            $data['code'] =$code = $_POST['code'];
            $data['codeName']= $codeName = $this->Multi_Languages_Model->getCodeName($code);
            $data['getLang'] =  $this->Multi_Languages_Model->getMultiLangCategory($category_id,$code);
            $View = $this->load->view('multi_languages/root_category_edit', $data, true);
            $this->output->set_output($View);
        }else{
            echo "error";exit;
        }
    }

    function updaterootcategory()
    {
        $fbc_user_id = $_SESSION['LoginID'];
        if(isset($_POST['cat_name']) && $_POST['cat_name']!=''){
         $id = $_POST['hidden_cat_id'];
         $code = $_POST['code'];
         $chekCategory = $this->Multi_Languages_Model->CountMultiLangCategory($id, $code);
         if($chekCategory > 0)
         {
            $where_arr= array('category_id'=>$id,'lang_code'=>$code);
            $updatetdata=array(
                    'cat_name' => $_POST['cat_name'],
                    'cat_description' => $_POST['cat_description'],
                    'category_id' => $id,
                    'lang_code'=>$_POST['code'],
                    'created_by'=>$fbc_user_id,
                    'updated_at'=>time(),
                    'ip'=>$_SERVER['REMOTE_ADDR']
                 );
                     $vat_id =$this->Multi_Languages_Model->updateData('multi_lang_category',$where_arr,$updatetdata);
                     echo json_encode(array('flag' => 1, 'msg' => "Translation Updated Successfully."));
                     exit();
         }
         else
         {
             $insertdata=array(
                    'cat_name' => $_POST['cat_name'],
                    'cat_description' => $_POST['cat_description'],
                    'category_id' => $id,
                    'lang_code'=>$_POST['code'],
                    'created_at'=>time(),
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                 );
                  $this->Multi_Languages_Model->insertData('multi_lang_category',$insertdata);
                  echo json_encode(array('flag' => 1, 'msg' => "Translation Added Successfully."));
                  exit();
         }


        }
    }

public function variantsTranslationView(){
if(!empty($this->session->userdata('userPermission')) && !in_array('seller/variants_translation',$this->session->userdata('userPermission'))){ 
             redirect(base_url('dashboard'));}

        $shop_id = $this->session->userdata('ShopID');
        $data = array();
        $data['side_menu']='languageTranslate';
        $data['PageTitle']='Variants Translation';
        $data['VariantsBySeller']=$this->EavAttributesModel->get_variant_masters($shop_id);
        $data['languagesListing'] = $this->Multi_Languages_Model->getLanguages();
        $this->load->view('multi_languages/variants/variants-translation',$data); 
    } 

public function attributesTranslationView(){
if(!empty($this->session->userdata('userPermission')) && !in_array('seller/attibutes_translation',$this->session->userdata('userPermission'))){ 
             redirect(base_url('dashboard'));}
        $shop_id = $this->session->userdata('ShopID');
        $data = array();
        $data['side_menu']='languageTranslate';
        $data['PageTitle']='Attributes Translation';
        $data['AttributesList']=$this->EavAttributesModel->get_attributes_masters($shop_id);
        $data['languagesListing'] = $this->Multi_Languages_Model->getLanguages();
        $this->load->view('multi_languages/attributes/attributes-translation',$data); 
    } 

    public function OpenEditVariantsPopup(){

        if(isset($_POST['id']) && $_POST['id']!=''){
            $data['id'] = $id = $_POST['id'];
            $data['variant']=$this->CommonModel->getVariantByID($id);
            $data['code'] =$code = $_POST['code'];
            $data['codeName']= $codeName = $this->Multi_Languages_Model->getCodeName($code);
            $data['MultiLangVariant'] = $this->Multi_Languages_Model->getSingleDataByID('multi_lang_eav_attributes',
            array('attr_id'=>$id,'lang_code'=>$code),'*');
            $View = $this->load->view('multi_languages/variants/variants-popup-translation', $data, true);
            $this->output->set_output($View);
        }else{
            echo "error";exit;
        }
    }

    public function OpenEditAttributesPopup(){

        if(isset($_POST['id']) && $_POST['id']!=''){
            $data['id'] = $id = $_POST['id'];
            $data['variant']=$this->CommonModel->getVariantByID($id);
            $data['code'] =$code = $_POST['code'];
            $data['codeName']= $codeName = $this->Multi_Languages_Model->getCodeName($code);
            $data['MultiLangVariant'] = $this->Multi_Languages_Model->getSingleDataByID('multi_lang_eav_attributes',
            array('attr_id'=>$id,'lang_code'=>$code),'*');
            $View = $this->load->view('multi_languages/attributes/attributes-popup-translation', $data, true);
            $this->output->set_output($View);
        }else{
            echo "error";exit;
        }
    }

   public function saveVariantTranslation()
    {
        $fbc_user_id = $_SESSION['LoginID'];
        if(isset($_POST['variant_name']) && $_POST['variant_name']!=''){
         $id = $_POST['id'];
         $code = $_POST['code'];
         $countVariant = $this->Multi_Languages_Model->getSingleDataByID('multi_lang_eav_attributes',
            array('attr_id'=>$id,'lang_code'=>$code),'id');
         if(isset($countVariant))
         {
            $where_arr= array('attr_id'=>$id,'lang_code'=>$code); 
            $updatetdata=array( 
                    'attr_name' => $_POST['variant_name'],
                    'attr_description' => $_POST['variant_desc'],    
                    'updated_at'=>time(),
                    'ip'=>$_SERVER['REMOTE_ADDR']
                 );
                     $vat_id =$this->Multi_Languages_Model->updateData('multi_lang_eav_attributes',$where_arr,$updatetdata);
                     echo json_encode(array('flag' => 1, 'msg' => "Variant Translation Updated Successfully."));
                     exit();
         }
         else
         {
             $insertdata=array( 
                    'attr_name' => $_POST['variant_name'],
                    'attr_description' => $_POST['variant_desc'],
                    'attr_id' => $id,
                    'attr_type' => $_POST['attr_type'],
                    'lang_code'=>$_POST['code'],       
                    'created_at'=>time(),
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                 );
                  $this->Multi_Languages_Model->insertData('multi_lang_eav_attributes',$insertdata);
                  echo json_encode(array('flag' => 1, 'msg' => "Variant Translated Successfully."));
                  exit();
            }
        
        }   
    }

    public function saveAttributesTranslation()
    {
        $fbc_user_id = $_SESSION['LoginID'];
        if(isset($_POST['attributes_name']) && $_POST['attributes_name']!=''){
         $id = $_POST['id'];
         $code = $_POST['code'];
         $countVariant = $this->Multi_Languages_Model->getSingleDataByID('multi_lang_eav_attributes',
            array('attr_id'=>$id,'lang_code'=>$code),'id');
         if(isset($countVariant))
         {
            $where_arr= array('attr_id'=>$id,'lang_code'=>$code); 
            $updatetdata=array( 
                    'attr_name' => $_POST['attributes_name'],
                    'attr_description' => $_POST['attributes_desc'],    
                    'updated_at'=>time(),
                    'ip'=>$_SERVER['REMOTE_ADDR']
                 );
                     $vat_id =$this->Multi_Languages_Model->updateData('multi_lang_eav_attributes',$where_arr,$updatetdata);
                     echo json_encode(array('flag' => 1, 'msg' => "Attribute Translation Updated Successfully."));
                     exit();
         }
         else
         {
             $insertdata=array( 
                    'attr_name' => $_POST['attributes_name'],
                    'attr_description' => $_POST['attributes_desc'],
                    'attr_id' => $id,
                    'attr_type' => $_POST['attr_type'],
                    'lang_code'=>$_POST['code'],       
                    'created_at'=>time(),
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                 );
                  $this->Multi_Languages_Model->insertData('multi_lang_eav_attributes',$insertdata);
                  echo json_encode(array('flag' => 1, 'msg' => "Attribute Translated Successfully."));
                  exit();
            }
        
        }  
        echo json_encode(array('flag' => 0, 'msg' => "Please enter all required fields."));
                  exit(); 
    }

}
