<?php 



class CategoryController extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

		$this->load->model('CategoryNewModel');

	

    }

	

	public function categoryList()

	{

		if($_SESSION['UserRole'] !== 'Super Admin') {

            if(!empty($this->session->userdata('userPermission')) && !in_array('database/category',$this->session->userdata('userPermission'))){ 

                redirect('dashboard');

            }

        }



		$SISA_ID=$this->session->userdata('LoginID');

		if($SISA_ID){

			$data['categories'] = $this->CategoryNewModel->getSubCategoryList();

			$data['browse_category'] = $this->CategoryNewModel->getAllCategories();

			$data['PageTitle']='Category';

			$data['side_menu']='category';

			$this->load->view('category/category_list',$data);

		}else{

			return redirect('/'); 

		}

	}



	public function addCategory()

	{

		if($_SESSION['UserRole'] !== 'Super Admin') {

            if(!empty($this->session->userdata('userPermission')) && !in_array('database/category',$this->session->userdata('userPermission'))){ 

                //redirect('dashboard');

				redirect('/admin/category');

            }

        }

		

		$LoginID=$this->session->userdata('LoginID');

		if($LoginID){

			$data['category_id']=$category_id = $this->uri->segment(3);

			if(isset($category_id)){

				$cat_ID_DATA= $this->CategoryNewModel->getSingleDataByID('category',array('id'=>$category_id),'*');

				if($cat_ID_DATA == ''){

					redirect('dashboard');

				}

				$url = base_url();

				$data['url'] =  rtrim($url,"/admin");

				$data['categoryData'] =$this->CategoryNewModel->getSingleDataByID('category',array('id'=>$category_id),'*');

				

			}

			$data['PageTitle']='Add Category';

			$data['side_menu']='category';

			$data['browse_category'] = $this->CategoryNewModel->getAllCategories();



			$this->load->view('category/category_add',$data);

		}else{

			return redirect('/'); 

		}

	}



	public function submitCategory()

	{

		

		$LoginID=$this->session->userdata('LoginID');

		if($LoginID){

			if(empty($_POST))

			{

				echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));

				exit;

			}else{

				$categoryName = $this->CommonModel->custom_filter_input($_POST['categoryName']);

				

				$category_id = $_POST['category_id'];

				if($category_id != '')

				{

					$categoryName = $this->CommonModel->custom_filter_input($_POST['categoryName']);

					$categorySlug = $categoryName;

					$categorySlug = str_replace(" ", "-", trim($categorySlug));

					$categorySlug = preg_replace('/[^A-Za-z0-9-]/', '', $categorySlug);

					$categorySlug = strtolower($categorySlug);

					$category_exist_count = $this->CategoryNewModel->checkSlugExistItself($categorySlug,$category_id);

					if (sizeof($category_exist_count) > 0) {

						echo json_encode(array('flag' => 0, 'msg' => "Category Name Already Exist"));

						exit;

					}

					$catImage =$this->CategoryNewModel->getSingleDataByID('category',array('id'=>$category_id),'cat_image');

					$imageName=$catImage->cat_image;

					if(isset($_FILES['customFil']['name']) && !empty($_FILES['customFil']['name']))

					{	

						$config['upload_path'] = SIS_SERVER_PATH.'/'.'uploads/categories/';

						$config['allowed_types'] = 'jpg|jpeg|png';

						$config['encrypt_name'] = true;

						$config['detect_mime'] = true;

						$this->load->library('upload', $config);

						$this->upload->initialize($config);



						if (!$this->upload->do_upload('customFil')) {

							$imageName='';

							



						} else {

						

							$cat_img = $this->upload->data();

							$imageName = $cat_img['file_name'];

						}

					}



					$updateData=array(  

						'cat_name'    		=> $categoryName,

						'lang_title' => isset($_POST['langTitle']) ? $this->CommonModel->custom_filter_input($_POST['langTitle']) : '',

						'cat_description'	=> $_POST['categoryDesc'],

						'meta_title'	=> $_POST['meta_title'],

						'meta_keyword'	=> $_POST['meta_keyword'],

						'meta_description'	=> $_POST['meta_description'],

						'cat_image'			=> $imageName,

						'status'			=> $_POST['status'],

						'updated_at'		=> strtotime(date('Y-m-d H:i:s')),

						'ip'				=> $_SERVER['REMOTE_ADDR'],

					);



					$this->db->where(array('id' => $category_id));

					$afftedRow = $this->db->update('category', $updateData);

					if($afftedRow){

						echo json_encode("update");

                            exit;

						// $url = base_url().'category';

						// echo json_encode(array('flag' => 1, 'msg' => "Updated successfully",'url'=>$url));

						// exit();

					}else{

						echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

						exit;

					}

				}else{

					

					//insert

					if(empty($_POST['categoryName'])){

						echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));

						exit;

					}else if(empty($_POST['imageName'])){

						echo json_encode(array('flag'=>0, 'msg'=>"Please upload image / compulsory fields."));

						exit;



					}else{

						$categoryName = $this->CommonModel->custom_filter_input($_POST['categoryName']);

						$categorySlug = $categoryName;

						$categorySlug = str_replace(" ", "-", trim($categorySlug));

						$categorySlug = preg_replace('/[^A-Za-z0-9-]/', '', $categorySlug);

						$categorySlug = strtolower($categorySlug);

						$category_exist_count = $this->CategoryNewModel->checkSlugExist($categorySlug);

						if (sizeof($category_exist_count) > 0) {

							echo json_encode(array('flag' => 0, 'msg' => "Category Name Already Exist"));

							exit;

						}



						$category_type = $_POST['category_type'];

						$parent_id = 0;

						$main_parent_id = 0;

						$cat_level = 0;



						if(isset($category_type) && $category_type != 0){

							$parent_id = $category_type;

							$main_parent_id = $category_type;

							$check_cat_level = $this->CategoryNewModel->check_cat_level($category_type);

							if($check_cat_level->cat_level == 0){

								$cat_level = 1;

								$parent_id = $check_cat_level->id;

								$main_parent_id = $check_cat_level->id;

							}elseif($check_cat_level->cat_level == 1){

								$cat_level = 2;

								$parent_id = $check_cat_level->id;

								$main_parent_id = $check_cat_level->main_parent_id;

							}else{

								$cat_level = 3;

								$parent_id = $check_cat_level->id;

								$main_parent_id = $check_cat_level->main_parent_id;

							}

						}else{

							$parent_id = 0;	

							$main_parent_id = 0;

						}



					$imageName='';

					if(isset($_FILES['customFil']['name']) && !empty($_FILES['customFil']['name']))

					{	

						$config['upload_path'] = SIS_SERVER_PATH.'/'.'uploads/categories/';

						$config['allowed_types'] = 'jpg|jpeg|png';

						$config['encrypt_name'] = true;

						$config['detect_mime'] = true;

						$this->load->library('upload', $config);

						$this->upload->initialize($config);

						if (!$this->upload->do_upload('customFil')) {

							$imageName='';

						} else {

							$logo_imgdata = $this->upload->data();

							$imageName = $logo_imgdata['file_name'];

						}

					}

						$insertData=array(  

							'cat_name'    		=> $categoryName,
							
							'lang_title' => isset($_POST['langTitle']) ? $this->CommonModel->custom_filter_input($_POST['langTitle']) : '',

							'slug'				=> $categorySlug,

							'cat_description'	=> $_POST['categoryDesc'],

							'meta_title'	=> $_POST['meta_title'],

							'meta_keyword'	=> $_POST['meta_keyword'],

							'meta_description'	=> $_POST['meta_description'],

							'parent_id'	=> $parent_id,

							'main_parent_id'	=> $main_parent_id,

							'cat_level'	=> $cat_level,

							'cat_image'	=> $imageName,

							'status'	=> $_POST['status'],

							// 'created_by_type' 	=> 0,

							'created_at'		=> strtotime(date('Y-m-d H:i:s')),

							'ip'				=> $_SERVER['REMOTE_ADDR'],

						);

						$this->db->insert('category', $insertData);

						$insert_id = $this->db->insert_id();

						if($insert_id){

							echo json_encode("success");

                            exit;

							// $url = base_url().'category';

							// echo json_encode(array('flag' => 1, 'msg' => "Successfully","url"=>$url));

							// exit;

						}else{

							echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));

							exit;

						}

					}

					

				}

			}	

			echo json_encode(array('flag' => 1,'msg' => "Update Successfully!!"));

			exit();

		}else{

			return redirect('/'); 

		}

	}





	



	// function getAjaxAttr()

	// {

	// 	$result = $this->CategoryModel->getAttribute();

	// 	echo json_encode($result);

	// 	exit();

	// }



	// function getAjaxAttrData()

	// {

	// 	$attr= $_GET['attr_id'];

	// 	$result = $this->AttributeModel->getAttributeById($attr);

	// 	$attributevalues = $this->AttributeModel->getAttributeValues($attr);

	// 	echo json_encode(array('result'=>$result, 'attributevalues'=>$attributevalues));

	// 	exit();

	// }



	// function getAjaxVarint()

	// {

	// 	$result = $this->CategoryModel->getVariant();

	// 	echo json_encode($result);

	// 	exit();

	// }



	// function getAjaxVarintData()

	// {

	// 	$variant= $_GET['variant_id'];

	// 	$result = $this->VariantModel->getVariantById($variant);

	// 	$variantvalues = $this->VariantModel->getVariantValues($variant);

	// 	echo json_encode(array('result'=>$result, 'variantvalues'=>$variantvalues));

	// 	exit();

	// }





	// function getSubCatAjaxCalog()

	// {

	// 	$subCategoryId= $_GET['subCategoryId'];

	// 	$result = $this->CategoryModel->geSubCategoryTagData($subCategoryId);

	// 	echo json_encode(array('data'=>$result));

	// 	exit();

	// }







	

}

