<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class B2BController extends CI_Controller
{

		function __construct()
		{
			parent::__construct();
			$this->load->model('UserModel');
			$this->load->model('CommonModel');
			$this->load->model('B2BModel');
			$this->load->model('InvoicingModel');
			$this->load->model('WebshopModel');
		  $this->load->model('SellerProductModel');
		  $this->load->model('CustomerModel');
			//$this->load->model('SellerProductModel');
			if($this->session->userdata('LoginID')==''){
				redirect(base_url());
			}

			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
			if(isset($FBCData) && $FBCData->database_name!='')
			{
				$fbc_user_database=$FBCData->database_name;

				$this->load->database();
				$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
				$this->seller_db = $this->load->database($config_app,TRUE);
			}else{
				redirect(base_url());
			}
		}

		public function index()
		{
			if(!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/b2webshop',$this->session->userdata('userPermission'))){
           redirect(base_url('dashboard'));  }

			$ShopID = $_SESSION['ShopID'];
			$data['PageTitle']='B2B-customers';
			$data['side_menu']='b2b';
			// $data['owner_detail']= $this->WebshopModel->getOwener($_SESSION['ShopOwnerId']);
			$data['shopData'] = $shopData = $this->UserModel->getShopDetailsByShopId($ShopID);
			$data['b2bData'] = $b2bData = $this->B2BModel->getUersB2BDetailsByShopId($ShopID);
			$data['catSubCatData'] = $catData = $this->B2BModel->getB2BCatSubCatDetailsRewised($ShopID);  //old getB2BCatSubCatDetails New getB2BCatSubCatDetailsRewised

			$selected_parent_ids=array();
			if(isset($catData) && count($catData)>0){
				foreach($catData as $val){
					if($val->level==0){
						$selected_parent_ids[]=$val->category_id;
					}
				}
			}
			$data['cat_parents']=$selected_parent_ids;
			//print_r($catData);
			$this->load->view('b2b/b2b',$data);
		}

		public function loadspecialpricingajax()
	{
		$data['shop_id'] = $shop_id = $this->session->userdata('ShopID');
		$data['currency_symbol']=$currency_symbol = $this->CommonModel->getShopCurrency($shop_id);
		$B2Bshop_id = $_POST['B2Bshop_id'];
		//$allProducts = $this->B2BModel->getAllproducts_special_prices_b2b($B2Bshop_id);
		$allProducts = $this->B2BModel->getDatatable_special_prices_b2b($B2Bshop_id);
		$Special_product_ids = array_column($allProducts, 'product_id');

		if(!empty($Special_product_ids))
		{
			$variants = $this->B2BModel->getVariantsByID($Special_product_ids);
			$variant_data_id= array_column($variants, 'product_id');

			$this->variants_match = $this->CommonModel->array_group_data($variants, 'product_id');
		}


		$data = array();
		foreach ($allProducts as $readData) {

			//$cat_name=$this->SellerProductModel->getProductsMaintCategoryNames($readData->product_id);

			$row = array();
			$row[]='<input type="checkbox"  name="chk_sp[]" value="'.$readData->id.'" > ';
			$row[]=$readData->sku;
			$row[]=$readData->name;

			$variant = " ";
			if(in_array($readData->product_id, $variant_data_id, true)){
				$variantValueData =$this->variants_match[$readData->product_id];
				if(isset($variantValueData) && $variantValueData>0){

					foreach ($variantValueData as $value) {

						$variant .= $value['attr_name']. ':'. $value['attr_options_name']. ','  ;

					}
					$variant =  rtrim($variant," , ");
				}
            }else
           	{
				$variant = "-";
			}

			$row[]=$variant;
			$row[]=$currency_symbol.' '. $readData->price;

			$row[]= $currency_symbol.' '.$readData->special_price;
			$row[]=  date("d-m-Y", $readData->special_price_from);
			$row[]=  date("d-m-Y", $readData->special_price_to);
				$current_date= date("Y-m-d");
	          	$from_date= date("Y-m-d", $readData->special_price_from);
	          	$to_date= date("Y-m-d", $readData->special_price_to);
          	if($current_date >= $from_date && $current_date <=  $to_date )
          	{
          	  $row[]= "Active";
         	 }elseif($to_date < $current_date)
          	{
           	 $row[]= "Inactive";
         	 }elseif($from_date > $current_date)
         	 {
         	   $row[]= "Upcomming";
         	 }


  if(!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/customers/write',$this->session->userdata('userPermission'))){
			$row[]='<a class="link-purple" href="'.base_url().'B2BController/b2b_edit_special_pricing/'.$readData->id.'">View</a>';
			}else{
			$row[]='<a class="link-purple" href="'.base_url().'B2BController/b2b_edit_special_pricing/'.$readData->id.'">View</a>/ <a class="link-purple trash" data-toggle="modal" data-target="#deleteModalForRow"  id="'.$readData->id.'"  data-id="'.$readData->id.'">Delete</a>';
		}

			$data[] = $row;
		}

		// echo "<pre>"; print_r($data);// die();
		$output = array(

			"draw" => $_POST['draw'],
			"recordsTotal" => $this->B2BModel->countspecialpricerecord_b2b($B2Bshop_id),
			"recordsFiltered" => $this->B2BModel->countfilterspecialprice_b2b($B2Bshop_id),
			"data" => $data,

		);
		//output to json format
		echo json_encode($output);
		exit;
	}


	public function b2b_edit_special_pricing()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/customers',$this->session->userdata('userPermission'))){
           redirect(base_url('dashboard'));  }

		$data['side_menu']='webshop';
		$data['current_tab']='specialPricing';
		$data['PageTitle']='Edit B2B Special Pricing';
		$data['shop_id'] = $shop_id = $this->session->userdata('ShopID');
		$data['currency_symbol']=$this->CommonModel->getShopCurrency($shop_id);
		$data['special_price_id'] = $special_price_id = $rule_Id = $this->uri->segment(3);
		$data['products_special_price'] = $this->B2BModel->getSingleproducts_special_price($special_price_id);
		foreach ($data['products_special_price'] as $value) {
			$variants = $this->WebshopModel->getVariants($value->product_id);
			$variant = (Object)$variants;

			$cat_name=$this->SellerProductModel->getProductsMaintCategoryNames($value->product_id);

			$value->cat_name = $cat_name;
			$value->variant = $variant;
			$ProductData[] = $value;
		}
		$data['product_details'] = $ProductData;
	    $data['special_pricing_link'] = base_url('B2BController/b2b_special_pricing');
		$this->load->view('b2b/customer/b2b_edit_special_pricing',$data);

	}

	public function b2b_special_pricing()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/customers',$this->session->userdata('userPermission'))){
           redirect(base_url('dashboard'));  }

		$data['current_tab']='B2B-specialPricing';
		$data['PageTitle']='B2B Special Pricing';
		$shop_id=$this->uri->segment(3);
		$data['shop_id']=$shop_id;
		$data['b2b_bulk_add_special_pricing_link'] = base_url('B2BController/b2b_bulk_add_special_pricing/'.$shop_id);
		$data['customerId']= $customerId = $this->B2BModel->getCustomerIdbyshop($shop_id);
		$data['customer_details'] =$customer_details= $this->B2BModel->getB2BOrderDetailsByShopId($shop_id,$customerId);
		$data['shop_id'] = $data['shop_id'];
		$data['add_special_pricing_link'] = base_url('B2BController/add_special_pricing/'.$shop_id);
		$this->load->view('b2b/customer/b2b-special-pricing',$data);
	}

	public function b2b_bulk_add_special_pricing()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/customers',$this->session->userdata('userPermission'))){
           redirect(base_url('dashboard'));  }

		$data['PageTitle']='B2Bwebshop - CSV Import';
		$data['side_menu']='B2Bwebshop';
		$data['current_tab']='specialPricing';
		$shop_id=$this->uri->segment(3);
		$data['shop_id']=$shop_id;
		$data['B2Bspecial_pricing_link'] = base_url('B2BController/b2b_special_pricing/'.$shop_id);
		$this->load->view('b2b/customer/b2b_bulk_add_special_pricing',$data);
	}

	public function add_special_pricing()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/customers',$this->session->userdata('userPermission'))){
           redirect(base_url('dashboard'));  }

		$data['current_tab']='specialPricing';
		$data['PageTitle']='B2B Add Special Pricing';
		$data['shop_id'] = $shop_id = $this->session->userdata('ShopID');
		$data['currency_symbol']=$this->CommonModel->getShopCurrency($shop_id);
		$allProducts = $this->WebshopModel->getAllProductsData();
		foreach ($allProducts as $value) {
			$variants = $this->WebshopModel->getVariants($value->id);
			$variant = (Object)$variants;

			$cat_name=$this->SellerProductModel->getProductsMaintCategoryNames($value->id);

			$value->cat_name = $cat_name;
			$value->variant = $variant;
			$ProductData[] = $value;
	}
		$shop_id=$this->uri->segment(3);
		$data['shop_id']=$shop_id;

		$data['customerId']= $customerId = $this->B2BModel->getCustomerIdbyshop($shop_id);
		$data['customer_details'] =$customer_details= $this->B2BModel->getB2BOrderDetailsByShopId($shop_id,$customerId);
		$data['shop_id'] = $data['shop_id'];

		$data['product_details'] = $ProductData;
		$data['customer_type_details'] = $this->CustomerModel->get_customer_type_details();
		$data['special_pricing_link'] = base_url('B2BController/b2b_special_pricing/'.$shop_id);
		$this->load->view('b2b/customer/b2b-add-special-pricing',$data);
	}

	public function save_special_pricing()
	{
			if(!isset($_POST['product_id']) && $_POST['product_id'] == '' && !isset($_POST['price']) && $_POST['price']=='' )
		{
			echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
			exit;
		}
		else
		{
			$product_id = $_POST['product_id'];
			$check_specidal_price_id= $this->B2BModel->getSingleDataByID('products_special_prices_b2b',array('product_id'=>$product_id),'*');
				if(empty($check_specidal_price_id))
				{
			$shop_id = $this->input->post('shop_id');
			$loginId = $this->session->userdata('LoginID');
			 	$insertdata=array(
							'product_id'=> $_POST['product_id'],
							'shop_id'=> $shop_id,
							'special_price'=> $_POST['special_price'],
							'special_price_from'=> strtotime($_POST['from_date']),
							'special_price_to'=> strtotime($_POST['to_date']),
							'created_by'=> $loginId,
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR'],
						);
					$special_pricing_id=$this->B2BModel->insertData('products_special_prices_b2b',$insertdata);
					if($special_pricing_id){
						$redirect = base_url('B2BController/b2b_special_pricing/'.$shop_id);
						echo json_encode(array('flag' => 1, 'msg' => "Success",'redirect'=>$redirect));
						exit();
					}else{
						echo json_encode(array('flag' => 0, 'msg' => "Nothing to Insert!"));
						exit;
					}
				}
				else
				{

					$edit_link= base_url('B2BController/b2b_edit_special_pricing/'.$check_specidal_price_id->id);
					$message="B2B Special pricing rule already added for this product
					Please <a href='".$edit_link."' target='_blank'>Click Here!</a> to view the pricing rule";
					echo json_encode(array('flag'=>0, 'msg'=>$message));
					exit;
				}
		}
	}


	public function b2b_update_special_pricing()
	{
		$special_price_id = $_POST['special_price_id'];
		$product_id = $_POST['product_id'];
		$shop_id = $_POST['shop_id'];

				$loginId = $this->session->userdata('LoginID');
				$updatetdata=array(
						'product_id'=> $product_id,
						'special_price'=> $_POST['special_price'],
						'special_price_from'=> strtotime($_POST['from_date']),
						'special_price_to'=> strtotime($_POST['to_date']),
						'updated_at'=>time(),
						'ip'=>$_SERVER['REMOTE_ADDR']
					);
				$where_arr= array('id'=>$special_price_id,'product_id'=>$product_id);
				$special_pricing_id=$this->B2BModel->updateNewData('products_special_prices_b2b',$where_arr,$updatetdata);
				if($special_pricing_id){
					$redirect = base_url('B2BController/b2b_special_pricing/'.$shop_id);
					echo json_encode(array('flag' => 1, 'msg' => "Updated Successfully",'redirect'=>$redirect));
					exit();
				}else{
					echo json_encode(array('flag' => 0, 'msg' => "Nothing to Update!"));
					exit;
				}
	}


	public function get_selling_price()
	{
		if(isset($_POST['product_id']) &&  $_POST['product_id']!='')
		{
			$product_id= $_POST['product_id'];
			$price =$this->B2BModel->getSingleDataByID('products',array('id'=>$product_id),'price');
			echo json_encode(array('flag' => 1, 'msg' => "nothing to update!", 'price' => $price));
			exit;

		}else{
			echo json_encode(array('flag' => 0, 'msg' => "nothing to update!", 'price' => ""));
							exit;
		}

	}


	public function delete_special_pricing()
	{
		$row_id = $_POST['row_id'];
		$B2Bshop_id=$this->uri->segment(3);
		$where_arr=array('id'=>$row_id);
		$rowAffected = $this->B2BModel->B2BdeleteData('products_special_prices_b2b',$where_arr);
		if($rowAffected )
		{
			$redirect = base_url('B2BController/b2b_special_pricing/'.$B2Bshop_id);
			echo json_encode(array('flag' => 1, 'msg' => "Successfully Deleted Row",'redirect'=>$redirect));
			exit();
		}else{
			echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));
			exit;
		}
	}

	public function delete_all_special_pricing()
	{
		$B2Bshop_id=$this->uri->segment(3);
		$sp_arr= (isset($_POST['chk_sp']) && $_POST['chk_sp'] !='') ? $_POST['chk_sp'] : '';
		if(isset($sp_arr) && $sp_arr!='')
		{
			foreach ($sp_arr as  $value)
			{
				$where_arr=array('id'=>$value);
				$rowAffected = $this->B2BModel->B2BdeleteData('products_special_prices_b2b',$where_arr);
			}
			if($rowAffected > 0 )
				{
					$redirect = base_url('B2BController/b2b_special_pricing/'.$B2Bshop_id);
					echo json_encode(array('flag' => 1, 'msg' => "Successfully Deleted Rows",'redirect'=>$redirect));
					exit();
				}else{
					echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));
					exit;
				}
		}else
		{
			echo json_encode(array('flag' => 0, 'msg' => "Please Select Special pricing to delete."));
			exit;
		}

	}

	public function openbulkselectcategory(){
		    $data['type']=$type=isset($_POST['type'])?$_POST['type']:'';

			$B2Bshop_id =$_POST['B2Bshop_id'];
			$data['B2Bshop_id'] = $B2Bshop_id;
			$data['PageTitle']='B2BWebshop - CSV Import';
			$data['side_menu']='B2Bwebshop';

		$View = $this->load->view('b2b/customer/B2Bbulk_category', $data, true);//pending
		$this->output->set_output($View);
	}
	public function downloadproductcsvB2B()
	{

		$B2Bshop_id=$this->uri->segment(3);
		$special_pricing =$this->B2BModel->getspecailpricingForCSVImport($B2Bshop_id);
		$sis_export_header = ["sku","special_price","special_price_from","special_price_to"];

 		$filename = 'SISB2BSpecialPricing-' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		$file = fopen('php://output', 'wb');
		fputcsv($file, $sis_export_header);

		if(isset($special_pricing ) && count($special_pricing )>0)
			{
				foreach($special_pricing  as $special_price_val){
					$product_type=$special_price_val->product_type;
					if($special_price_val->sku === ''){
						continue;
					}
					if($product_type === 'conf-simple' || $product_type === 'simple')
					{
						fputcsv($file, [
							$special_price_val->sku,
							$special_price_val->special_price,
							!empty($special_price_val->special_price_from) ? date("d-m-Y", $special_price_val->special_price_from) : '-',
					   	!empty($special_price_val->special_price_to) ? date("d-m-Y", $special_price_val->special_price_to) : '-']);
					}
				}
			}
			fclose($file);
			exit;
	}

	public	function checkcsvdata()
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

			$shop_upload_path='shop'.$shop_id;

			$sku_not_found=array();
			$special_pricing=array();

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

			  while($row = fgetcsv($file_data))
			 {
			  $special_pricing[] = $row;
			 }

			 if(isset($special_pricing) && count($special_pricing)>=1)
			 {
				foreach($special_pricing as $index=>$specialss)
				{
					$sku=$specialss[0];
					$special_price=$specialss[1];
					$special_price_from=$specialss[2];
					$special_price_to = $specialss[3];
					$Product_details='';

					if($sku!='' &&  $special_price!='' && $special_price_from!='' && $special_price_to !='')
					{
						$Product_details=$this->B2BModel->check_product_exists_by_sku($sku);

						if(isset($Product_details) && $Product_details['id']!=''){

						}else{
							$sku_not_found[]=$sku;
						}

					}else
					{
						 $arrResponse  = array('status' =>400 ,'message'=>'Invalid csv file');
						echo json_encode($arrResponse);exit;
					}

				}
			 }
			 if(count($sku_not_found)>0){
					  $sku_str=implode(', ',$sku_not_found);
					  $appne_msg.=" SKU  $sku_str not found in database.";
				 }
			if(count($special_pricing)<=0){
				   $appne_msg.=" special_pricing data not found.";
				  }
			if($appne_msg !=''){
					 $arrResponse  = array('status' =>400 ,'message'=>$appne_msg);
					echo json_encode($arrResponse);exit;
				 }else{
					 $arrResponse  = array('status' =>200 ,'message'=>"Special Pricing sheet validated, Please continue to upload.");
					echo json_encode($arrResponse);exit;
				 }


		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Please upload proper csv file');
				echo json_encode($arrResponse);exit;
		}

	}


	function import_special_pricing()
	{
		// print_r_custom($_FILES);
		$special_pricing =array();
		$id = $this->input->post('B2Bshop_id');
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$shop_upload_path='shop'.$shop_id;

		if(!empty($_FILES['upload_csv_file']['name']))
		{
			 $file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			 while($row = fgetcsv($file_data))
			 {
			  $special_pricing[] = $row;
			 }

			 $headers=$special_pricing[0];
			 $attr_not_found=array();
			if(isset($special_pricing) && count($special_pricing)>1){
				  foreach($special_pricing as $index=>$special_price){

					if($index==0){
						continue;
					}
					$sku=$special_price[0];
					$special_price_val=$special_price[1];
					$special_price_from=strtotime($special_price[2]);
					$special_price_to=strtotime($special_price[3]);
					$Product_details='';

					if($sku!='')
					{
						$Product_details=$this->B2BModel->check_product_exists_by_sku($sku);
						if(isset($Product_details) && $Product_details['id']!='')
						{
							$product_id= $Product_details['id'];
							$special_pricing_details= $this->B2BModel->check_SP__by_ID_Cust_type($product_id);
							if(isset($special_pricing_details) && $special_pricing_details['id']!='')
							{

								$updatedata=array(

										'special_price'=>$special_price_val,
										'special_price_from'=>$special_price_from,
										'special_price_to'=>$special_price_to,
										'shop_id' => $id,
										'updated_at'=>time(),
										'ip'=>$_SERVER['REMOTE_ADDR']
								);

								$where_arr=array('product_id'=>$product_id);
								$this->WebshopModel->updateNewData('products_special_prices_b2b',$where_arr,$updatedata);
								// $arrResponse  = array('status' =>200 ,'message'=>'Special Prices sheet Imported Successfully.');
								// echo json_encode($arrResponse);exit;

							}else
							{
								$insertdata=array(
										'product_id'=>$product_id,
										'special_price'=>$special_price_val,
										'special_price_from'=>$special_price_from,
										'special_price_to'=>$special_price_to,
										'shop_id' => $id,
										'created_by'=>$fbc_user_id,
										'created_at'=>time(),
										'ip'=>$_SERVER['REMOTE_ADDR']
								);
								$special_pricing_id=$this->B2BModel->insertData('products_special_prices_b2b	',$insertdata);

								// $arrResponse  = array('status' =>200 ,'message'=>'Special Prices sheet Imported Successfully.');
								// echo json_encode($arrResponse);exit;

							}

						}else
						{

							$arrResponse  = array('status' =>400 ,'message'=>'Please upload valid csv file');
							echo json_encode($arrResponse);exit;
						}

					}else
					{
						$sku_not_found[]=$sku;
						if(count($sku_not_found)>0)
							{
								  $sku_str=implode(', ',$sku_not_found);
								  $appne_msg.=" SKU  $sku_str not found in database.";
							}
					}


				  }

				$arrResponse  = array('status' =>200 ,'message'=>'Special Prices sheet Imported Successfully.');
								echo json_encode($arrResponse);exit;
			  }else{
				  $arrResponse  = array('status' =>400 ,'message'=>'Please have some data in csv file');
					echo json_encode($arrResponse);exit;
			  }

			 //print_r_custom($product_data);
		}else{
			$arrResponse  = array('status' =>400 ,'message'=>'Please upload valid csv file');
				echo json_encode($arrResponse);exit;
		}

	}

	function openbulkuploadpopup(){
		$data['PageTitle']='B2B Webshop - CSV Import';
		$data['side_menu']='bulk-add';
		$B2Bshop_id = $_POST['B2Bshop_id'];
		$data['B2Bshop_id'] = $B2Bshop_id;
		$View = $this->load->view('b2b/customer/b2b_bulk_upload_csv', $data, true);
		$this->output->set_output($View);
	}

	public function postB2BAccess()
	{
		//print_r($_POST);print_r($_FILES);exit;
		$LoginID = $_SESSION['LoginID'];
		$ShopID = $_SESSION['ShopID'];
		$ShopOwnerId = $_SESSION['ShopOwnerId'];
		if(isset($_POST['B2BStatusChk']) || isset($_POST['adminAccessChk']))
		{
			$B2BStatus = isset($_POST['B2BStatusChk']) ? $_POST['B2BStatusChk'] : '';
			$adminAccess = isset($_POST['adminAccessChk']) ? $_POST['adminAccessChk'] : '';
			$b2bData = $this->B2BModel->getUersB2BDetailsByShopId($ShopID);
			if(isset($_POST['B2BStatusChk']))
			{
				$allowed = array('jpg','jpeg','png','doc','docx','pdf');

				$filename_payment = $_FILES['paymentTermsFile']['name'];
				$filesize_payment = $_FILES['paymentTermsFile']['size'];

				$ext_payment = strtolower(pathinfo($filename_payment, PATHINFO_EXTENSION));

				$filename_terms = $_FILES['termsCondFile']['name'];
				$filesize_terms = $_FILES['termsCondFile']['size'];

				$ext_terms = strtolower(pathinfo($filename_terms, PATHINFO_EXTENSION));

				$filesize = MAX_FILESIZE_BYTE;
				$size = MAX_FILESIZE_MB;

				if(isset($_POST['dropShipChk']) && empty($_POST['dropshipTime'])) {
					echo json_encode(array('flag'=>0, 'msg'=>"Please enter Dropship delivery time."));
					exit;
				} else if(isset($_POST['buyInChk']) && empty($_POST['buyinTime'])) {
					echo json_encode(array('flag'=>0, 'msg'=>"Please enter Buyin delivery time."));
					exit;
				} else if(isset($_POST['priceChk']) && (empty($_POST['incPriceChk']) && empty($_POST['decPriceChk']))) {
					echo json_encode(array('flag'=>0, 'msg'=>"Please select at least one option from price change permission"));
					exit;
				} else if(!empty($filename_payment) && !in_array($ext_payment, $allowed)) {
					echo json_encode(array('flag'=>0, 'msg'=>"Invalid file type."));
					exit;
				} else if(!empty($filename_payment) && $filesize_payment > $filesize) {
					echo json_encode(array('flag'=>0, 'msg'=>"Limit exceeds above $size for $filename_payment."));
					exit;
				}else if(!empty($filename_terms) && !in_array($ext_terms, $allowed)) {
					echo json_encode(array('flag'=>0, 'msg'=>"Invalid file type."));
					exit;
				}else if(!empty($filename_terms) && $filesize_terms > $filesize) {
					echo json_encode(array('flag'=>0, 'msg'=>"Limit exceeds above $size for $filename_terms."));
					exit;
				}else {

					$adminAccessChk = (isset($_POST['adminAccessChk'])) ? 1 : 0;
					$dropShipChk = (isset($_POST['dropShipChk'])) ? 1 : 0;
					$buyInChk = (isset($_POST['buyInChk'])) ? 1 : 0;
					$catalogChk = (isset($_POST['catalogChk'])) ? 1 : 0;
					$priceChk = (isset($_POST['priceChk'])) ? 1 : 0;
					$incPriceChk = (isset($_POST['incPriceChk'])) ? 1 : 0;
					$decPriceChk = (isset($_POST['decPriceChk'])) ? 1 : 0;

					$dropshipDiscount = $this->CommonModel->custom_filter_input($_POST['dropshipDiscount']);
					$dropshipTime = $this->CommonModel->custom_filter_input($_POST['dropshipTime']);
					$buyinDiscount = $this->CommonModel->custom_filter_input($_POST['buyinDiscount']);
					$buyinTime = $this->CommonModel->custom_filter_input($_POST['buyinTime']);

					$payment_term = (isset($_POST['payment_term'])) ? 1 : 0;

					$allowed_types = ['jpg','jpeg','png','doc','docx','pdf'];

					if(!empty($filename_payment)){
						$ext = pathinfo($_FILES['paymentTermsFile']['name'], PATHINFO_EXTENSION);
						$file_name = "PAYMENT_TERMS_{$ShopID}.{$ext}";

						if(!in_array(strtolower($ext), $allowed_types)){
							echo json_encode(array('flag'=>0, "File type not allowed"));
							exit;
						}

						$this->load->library('s3_filesystem');
						$this->s3_filesystem->putFile($_FILES['paymentTermsFile']['tmp_name'], "/documents/payment_terms/{$file_name}");
						$paymentTermsFile = $file_name;

					}else{
						$paymentTermsFile = $_POST['hidden_paymentTermsFile'];
					}

					if(!empty($filename_terms)){
						$ext = pathinfo($_FILES['termsCondFile']['name'], PATHINFO_EXTENSION);
						$file_name = "TERMS_COND_{$ShopID}.{$ext}";

						if(!in_array(strtolower($ext), $allowed_types)){
							echo json_encode(array('flag'=>0, "File type not allowed"));
							exit;
						}

						$this->load->library('s3_filesystem');
						$this->s3_filesystem->putFile($_FILES['termsCondFile']['tmp_name'], "/documents/terms_condition/{$file_name}");
						$termsCondFile = $file_name;
					}else{
						$termsCondFile = $_POST['hidden_termsCondFile'];
					}

					$b2bCatArr = array();
					$b2bCat = $this->B2BModel->getB2BCatSubCatDetailsRewised($ShopID);  //old getB2BCatSubCatDetails  new getB2BCatSubCatDetailsRewised
					foreach($b2bCat as $cat){
						$b2bCatArr[] = $cat->id;
					}

					$b2bCatEnArr = array();
					$b2bEnableArr = isset($_POST['b2bEnable'])?$_POST['b2bEnable']:array();
					if(is_array($b2bEnableArr) && count($b2bEnableArr)>0){
						foreach($b2bEnableArr as $value){
							$b2bCatEnArr[] = $value;
						}
					}

					$enable_b2b =array_intersect($b2bCatArr, $b2bCatEnArr);
					//echo '<pre>';print_r($enable_b2b);
					$disable_b2b=array_diff($b2bCatArr,$b2bCatEnArr);
					//echo '<pre>';print_r($disable_b2b);
					//exit;
					if(is_array($enable_b2b) && count($enable_b2b) > 0){
						$updateEnableData	=  array('b2b_enabled' => 1);
						$this->seller_db->where_in('id',$enable_b2b);
						//$this->seller_db->where(array('fbc_user_id' => $ShopOwnerId, 'level' => 1));
						$this->seller_db->where(array('fbc_user_id' => $ShopOwnerId));
						$this->seller_db->update('fbc_users_category_b2b',$updateEnableData);
					}

					if(is_array($disable_b2b) && count($disable_b2b) > 0){
						$updateDisableData	=  array('b2b_enabled' => 0);
						$this->seller_db->where_in('id',$disable_b2b);
						//$this->seller_db->where(array('fbc_user_id' => $ShopOwnerId, 'level' => 1));
						$this->seller_db->where(array('fbc_user_id' => $ShopOwnerId));
						$this->seller_db->update('fbc_users_category_b2b',$updateDisableData);
					}

					if(empty($b2bData)){
						$insertData=array(
							'shop_id'				=> $ShopID,
							'shop_owner_user_id'	=> $ShopOwnerId,
							'allow_dropship'		=> $dropShipChk,
							'dropship_discount'		 => $dropshipDiscount,
							'dropship_del_time'		 => $dropshipTime,
							'allow_buyin'			 => $buyInChk,
							'buyin_discount' 		=> $buyinDiscount,
							'buyin_del_time' 		=> $buyinTime,
							'display_catalog_overseas' 	=> $catalogChk,
							'perm_to_change_price' 	=> $priceChk,
							'can_increase_price' 	=> $incPriceChk,
							'can_decrease_price' 	=> $decPriceChk,
							'payments_terms_upload' => $paymentTermsFile,
							'terms_condition_upload' => $termsCondFile,
							'enable_payment_term' => $payment_term,
							'created_by' 			=> $LoginID,
							'created_at' 			=> strtotime(date('Y-m-d H:i:s')),
							'ip'					=>$_SERVER['REMOTE_ADDR']
						);
						$rowAffected = $this->db->insert('fbc_users_b2b_details', $insertData);

					}else{
						$updateData=array(

							'allow_dropship'		=> $dropShipChk,
							'dropship_discount'		 => $dropshipDiscount,
							'dropship_del_time'		 => $dropshipTime,
							'allow_buyin'			 => $buyInChk,
							'buyin_discount' 		=> $buyinDiscount,
							'buyin_del_time' 		=> $buyinTime,
							'display_catalog_overseas' 	=> $catalogChk,
							'perm_to_change_price' 	=> $priceChk,
							'can_increase_price' 	=> $incPriceChk,
							'can_decrease_price' 	=> $decPriceChk,
							'payments_terms_upload' => $paymentTermsFile,
							'terms_condition_upload' => $termsCondFile,
							'enable_payment_term' => $payment_term,
							'updated_at' 			=> strtotime(date('Y-m-d H:i:s'))
						);
						$this->db->where(array('id'=>$b2bData->id,'shop_id'=> $ShopID,'shop_owner_user_id'=> $ShopOwnerId,));
						$rowAffected = $this->db->update('fbc_users_b2b_details', $updateData);
					}

					$updateShopData=array(
						'b2b_allow_access_to_admin'	=> $adminAccessChk,
						'b2b_status'	=> 1,
					);
					$this->db->where(array('shop_id'=> $ShopID,'fbc_user_id'=> $ShopOwnerId,));
					$rowAffected = $this->db->update('fbc_users_shop', $updateShopData);
				}
			}else{
				$B2BStatus = (isset($_POST['B2BStatus'])) ? 1 : 0;
				$updateShopData=array(
					'b2b_allow_access_to_admin'	=> 1,
					'b2b_status'	=> $B2BStatus
				);
				$this->db->where(array('shop_id'=> $ShopID,'fbc_user_id'=> $ShopOwnerId,));
				$rowAffected = $this->db->update('fbc_users_shop', $updateShopData);
			}
		}else{
			//enable access to admin
			$updateShopData=array(
				'b2b_allow_access_to_admin' => 0,
				'b2b_status'	=> 0,
			);
			$this->db->where(array('shop_id'=> $ShopID,'fbc_user_id'=> $ShopOwnerId,));
			$rowAffected = $this->db->update('fbc_users_shop', $updateShopData);
		}

		if($rowAffected){
			echo json_encode(array('flag' => 1, 'msg' => "Success",));
			exit();
		}else{
			echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));
			exit;
		}
	}

	//***
	public function customer_listing()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/customers',$this->session->userdata('userPermission'))){
           redirect(base_url('dashboard'));  }
		$data['PageTitle']='B2B - Customers';
		$data['side_menu']='b2b';
		$this->load->view('b2b/customer/B2B-customers-listing.php',$data);
	}

	public function get_single_b2b_customer_details()
	{
		if(!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/customers',$this->session->userdata('userPermission'))){
           redirect(base_url('dashboard'));  }
		$data['PageTitle']='B2B - Customers';
		$data['side_menu']='b2b';
		$shop_id=$this->uri->segment(4);
		$data['shop_id']=$shop_id;
		$data['customerId']= $customerId = $this->B2BModel->getCustomerIdbyshop($shop_id);
		//$data['customer_details'] =$this->B2BModel->getB2BOrderDetailsByShopId($shop_id);
		$data['b2bCustomerInfo'] = $b2bCustomerInfo = $this->CommonModel->getSingleShopDataByID('b2b_customers',array('id'=>$customerId),'import_through_quickpage,tax_exampted');
		// print_r($data['b2bCustomerInfo']);die();
		$data['customer_details'] =$customer_details= $this->B2BModel->getB2BOrderDetailsByShopId($shop_id,$customerId);
		if(empty($customer_details)){
			redirect('b2b/customers');
		}

		$data['OrderList']=$this->B2BModel->getordersbyshop($shop_id);
		$data['InvoiceList']=$this->B2BModel->getinvoicesbyshop($data['customerId']);
		// $data['InvoiceGenerateList']=$this->InvoicingModel->get_b2b_customer_invoicing_list($data['customerId']);//old data
		$data['InvoiceGenerateList']=$this->InvoicingModel->get_b2b_customer_invoicing_list($shop_id);
		$total_purchase_row=$this->B2BModel->gettotalpurchasebyshop($shop_id);

		$data['currency_code']=$currency_code=$this->CommonModel->getShopCurrency($shop_id);

		$last_purchase_date=$this->B2BModel->getlastpurchasedatebyshop($shop_id);

		if(isset($last_purchase_date) && $last_purchase_date->created_at!=''){
			$last_purchase_date=$last_purchase_date->created_at;
		}else{
			$last_purchase_date='';
		}
		// print_r($last_purchase_date);die();
		$data['last_purchase_date']=$last_purchase_date;

		if(isset($total_purchase_row) && $total_purchase_row->total!=''){
			$total_purchase=number_format($total_purchase_row->total,2);
		}else{
			$total_purchase='-';
		}

		$data['total_purchase']=$total_purchase;
		// echo "<pre>";		// print_r($data);		// die();
		$this->load->view('b2b/customer/B2B-customer-details.php',$data);
	}

	public function postExclusiveterms()
	{
		// print_r($_POST);print_r($_FILES);exit;
		$customer_id = isset($_POST['b2b_customer_id']) ? $_POST['b2b_customer_id'] : '';
		$b2bCustomerData = $this->CommonModel->getSingleShopDataByID('b2b_customers_details',array('customer_id'=>$customer_id),'*');
			if(isset($_POST['b2b_customer_id']))
			{
				if(isset($_POST['dropShipChk']) && empty($_POST['dropshipTime'])) {
					echo json_encode(array('flag'=>0, 'msg'=>"Please enter Dropship delivery time."));
					exit;
				} else if(isset($_POST['buyInChk']) && empty($_POST['buyinTime'])) {
					echo json_encode(array('flag'=>0, 'msg'=>"Please enter Buyin delivery time."));
					exit;
				} else if(isset($_POST['priceChk']) && (empty($_POST['incPriceChk']) && empty($_POST['decPriceChk']))) {
					echo json_encode(array('flag'=>0, 'msg'=>"Please select at least one option from price change permission"));
					exit;
				} else {
					$customer_id_for_b2b = $_POST['customer_id_for_b2b'];
					$dropShipChk = (isset($_POST['dropShipChk'])) ? 1 : 0;
					$buyInChk = (isset($_POST['buyInChk'])) ? 1 : 0;
					$catalogChk = (isset($_POST['catalogChk'])) ? 1 : 0;
					$priceChk = (isset($_POST['priceChk'])) ? 1 : 0;
					$incPriceChk = (isset($_POST['incPriceChk'])) ? 1 : 0;
					$decPriceChk = (isset($_POST['decPriceChk'])) ? 1 : 0;

					$dropshipDiscount = $this->CommonModel->custom_filter_input($_POST['dropshipDiscount']);
					$dropshipTime = $this->CommonModel->custom_filter_input($_POST['dropshipTime']);
					$buyinDiscount = $this->CommonModel->custom_filter_input($_POST['buyinDiscount']);
					$buyinTime = $this->CommonModel->custom_filter_input($_POST['buyinTime']);
					$payment_term = (isset($_POST['payment_term'])) ? 1 : 0;
					if(empty($b2bCustomerData)){
						$insertData=array(
							'customer_id'			=> $customer_id_for_b2b,
							'allow_dropship'		=> $dropShipChk,
							'dropship_discount'		 => $dropshipDiscount,
							'dropship_del_time'		 => $dropshipTime,
							'allow_buyin'			 => $buyInChk,
							'buyin_discount' 		=> $buyinDiscount,
							'buyin_del_time' 		=> $buyinTime,
							'display_catalog_overseas' 	=> $catalogChk,
							'perm_to_change_price' 	=> $priceChk,
							'can_increase_price' 	=> $incPriceChk,
							'can_decrease_price' 	=> $decPriceChk,
							'enable_payment_term' => $payment_term,
							'created_at' 			=> strtotime(date('Y-m-d H:i:s')),
							'ip'					=>$_SERVER['REMOTE_ADDR']
						);
						$rowAffected = $this->seller_db->insert('b2b_customers_details', $insertData);

					}else{
						$updateData=array(

							'allow_dropship'		=> $dropShipChk,
							'dropship_discount'		 => $dropshipDiscount,
							'dropship_del_time'		 => $dropshipTime,
							'allow_buyin'			 => $buyInChk,
							'buyin_discount' 		=> $buyinDiscount,
							'buyin_del_time' 		=> $buyinTime,
							'display_catalog_overseas' 	=> $catalogChk,
							'perm_to_change_price' 	=> $priceChk,
							'can_increase_price' 	=> $incPriceChk,
							'can_decrease_price' 	=> $decPriceChk,
							'enable_payment_term' => $payment_term,
							'updated_at' 			=> strtotime(date('Y-m-d H:i:s'))
						);
						$this->seller_db->where(array('customer_id'=> $customer_id));
						$rowAffected = $this->seller_db->update('b2b_customers_details', $updateData);
					}
					$import_through_quickpage = (isset($_POST['import_through_quickpage'])) ? 1 : 0;
					$tax_exampted = (isset($_POST['tax_exampted'])) ? 1 : 2;
					if(isset($import_through_quickpage) || isset($tax_exampted))
					{
						$updateData=array(
							'import_through_quickpage' 	=> $import_through_quickpage,
							'tax_exampted' => $tax_exampted
						);
						// print_r($customer_id);
						// die();
						$this->seller_db->where(array('id'=> $customer_id));
						$rowAffected1 = $this->seller_db->update('b2b_customers', $updateData);
					}

				}
			}else{
				echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));
			exit;
			}

		if($rowAffected && $rowAffected1){
			echo json_encode(array('flag' => 1, 'msg' => "Success",));
			exit();
		}else{
			echo json_encode(array('flag' => 0, 'msg' => "went something wrong!"));
			exit;
		}
	}



	public function postB2BCustomerInvoice(){
		$LoginID = $_SESSION['LoginID'];
		$ShopID = $_SESSION['ShopID'];
		$ShopOwnerId = $_SESSION['ShopOwnerId'];

		if(isset($_POST['customerId']))
		{
			$invoice = isset($_POST['invoice']) ? $_POST['invoice'] : '';
			$shop_id =$_POST['shopId'];
			$b2bInvoiceData = $this->B2BModel->getCustomerInvoiceB2BByShopId($_POST['customerId']);
			$payment_term = $this->CommonModel->custom_filter_input($_POST['payment_term']);
			if(isset($_POST['customerId']))
			{
					$invDailyAmt = 0.00;
					$invWeeklyAmt = 0.00;
					$invMonthlyAmt = 0.00;
					if(isset($invoice)){
						$invoiceType=$invoice;
						if($invoice==2){
							$invDailyAmt = $this->CommonModel->custom_filter_input($_POST['invDailyAmt']);
						}else if($invoice==3){
							$invWeeklyAmt = $this->CommonModel->custom_filter_input($_POST['invWeeklyAmt']);

						}else if($invoice==4){
							$invMonthlyAmt = $this->CommonModel->custom_filter_input($_POST['invMonthlyAmt']);

						}
					}else{
						$invoiceType='0';
					}
					$customerId = $this->CommonModel->custom_filter_input($_POST['customerId']);


					if(empty($b2bInvoiceData)){
						$insertData=array(
							'customer_id' => $customerId,
							'invoice_type' => $invoiceType,
							'inv_daily_max_inv_amt' => $invDailyAmt,
							'inv_weekly_max_inv_amt' => $invWeeklyAmt,
							'inv_monthly_max_inv_amt' => $invMonthlyAmt,
							'last_invoice_sent_date' => 0,
							'payment_term' => $payment_term,
							'created_by' => $LoginID,
							'created_at' => strtotime(date('Y-m-d H:i:s')),
							// 'updated_at' 		=> ,
							'ip' =>$_SERVER['REMOTE_ADDR']
						);
						$rowAffected = $this->seller_db->insert('b2b_customers_invoice', $insertData);

					}else{
						$updateData=array(
							'invoice_type' => $invoiceType,
							'inv_daily_max_inv_amt' => $invDailyAmt,
							'inv_weekly_max_inv_amt' => $invWeeklyAmt,
							'inv_monthly_max_inv_amt' => $invMonthlyAmt,
							'payment_term' => $payment_term,
							// 'created_by' => $LoginID,
							// 'created_at' => strtotime(date('Y-m-d H:i:s')),
							'updated_at' => strtotime(date('Y-m-d H:i:s'))
							// 'ip' =>$_SERVER['REMOTE_ADDR']

						);
						$this->seller_db->where(array('id'=>$b2bInvoiceData->id));
						$rowAffected = $this->seller_db->update('b2b_customers_invoice', $updateData);
					}
				//}
			}
			//redirect(base_url()."b2b/customer/detail-invoice/".$shop_id);
			if($rowAffected){
				echo json_encode(array('flag' => 1, 'shop_id' => $shop_id, 'msg' => "Success",));
				exit();
			}else{
				echo json_encode(array('flag' => 0, 'shop_id' => $shop_id, 'msg' => "went something wrong!"));
				exit;
			}
		}
	}

	public function getB2BCustomerList(){
		if(isset($_POST)){
			// print_r($_POST);
			// die();
			$search_param =array();
			// Shop, owner, category name - keyword
			if(!empty($_POST['search'])) {
				$search_param['keyword'] = $_POST['search'];

			}
			$data['customer_listing'] = $customer_listing = $this->B2BModel->get_all_customers($search_param);
			//print_r($popularSuppList );
			$this->load->view('b2b/customer/b2bcustomerlist',$data);

		}
	}


	function loadb2bcustomersajax()
	{

		$shop_id		=	$this->session->userdata('ShopID');
        $ProductData = $this->B2BModel->get_datatables_b2b_customers();


		$data = array();
		$no = $_POST['start'];
		foreach ($ProductData as $readData) {
			$no++;
			$row = array();

			$order_url=base_url().'b2b/customer/detail/'.$readData->shop_id;
			$Row=$this->B2BModel->getlastpurchasedate($readData->shop_id);
			$total_purchase_row=$this->B2BModel->gettotalpurchasebyshop($readData->shop_id);

			$currency_code=$this->CommonModel->getShopCurrency($shop_id);


			if($readData->tax_exampted == 1) {
				$tax_exampted = "YES";
			}elseif($readData->tax_exampted == 2){
				$tax_exampted = "NO";
			}else{
				$tax_exampted = "NOT DEFINED";
			}



			if(isset($Row) && $Row->created_at!=''){
				$last_purchase_date=date('d-m-Y',$Row->created_at).' | '.date('h:i A',$Row->created_at);
			}else{
				$last_purchase_date='-';
			}

			if(isset($total_purchase_row) && $total_purchase_row->total!=''){
				$total_purchase=number_format($total_purchase_row->total,2);
			}else{
				$total_purchase='-';
			}

			$row[]= $readData->customer_name;
			$row[]= $readData->org_shop_name;
			$row[]=$readData->ship_state.', '.$readData->country_name;
			$row[]=$readData->email;
			$row[]=$tax_exampted;

			$row[]= $last_purchase_date;
			$row[]= $currency_code.' '.$total_purchase;
			$row[]='<a class="link-purple" href="'.$order_url.'">View</a>';

			$data[] = $row;

		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->B2BModel->count_all_b2b_customers(),
						"recordsFiltered" => $this->B2BModel->count_filtered_b2b_customers(),
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);
		exit;
	}

}
