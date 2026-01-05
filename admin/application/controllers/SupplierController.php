<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SupplierController extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('SupplierModel');
		$this->load->model('B2BModel');
		$this->load->model('CommonModel');
		$this->load->model('UserModel');
		$this->load->model('SellerProductModel');
		$this->load->model('B2BImportModel');

		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
	}

	public function index()
	{

		$data['PageTitle']='Search Supplier List';
		$data['side_menu']='search';
		$data['popularSuppList'] = $popularSuppList = $this->SupplierModel->getPopularSuppliersList();

		// print_r($popularSuppList);
		//$this->load->view('seller/suppliers/supplier_main',$data);
		$this->load->view('seller/suppliers/search_tab',$data);
	}

	public function getSeacrhList(){
		if(isset($_POST)){
			//print_r($_POST);
			$search_param =array();
			// Shop, owner, category name - keyword
			if(!empty($_POST['search'])) {
				$search_param['keyword'] = $_POST['search'];

			}
			$data['popularSuppList'] = $popularSuppList = $this->SupplierModel->getPopularSuppliersList($search_param);
			//print_r($popularSuppList );
			$this->load->view('seller/suppliers/supplier_list',$data);
		}
	}

	public function getSupplierDetail(){

		$data['PageTitle']='Supplier Detail';
		$data['side_menu']='search';
		$id = $this->uri->segment(3);
		$shop_id = $this->session->userdata('ShopID');
		if(empty($id )){
			redirect(base_url());
		}
		/*
		$supplierData = $this->B2BModel->getUersNewB2BDetailsByShopId($id,$shop_id);
		if(!empty($supplierData))
		{
			$data['supplierData'] = $supplierData;
		}else
		{
			//Default b2b details
			$data['supplierData'] = $supplierData = $this->B2BModel->getUersB2BDetailsByShopId($id);
		}
		if(empty($supplierData)){
			redirect(base_url());
		}
		$data['catData'] = $catData = $this->SupplierModel->getB2BCatDetailsRewised($supplierData->shop_id);  //new getB2BCatDetailsRewised  old getB2BCatDetails
		$shop_id=$supplierData->shop_id;
		$data['FbcUserB2BData']=$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$shop_id),'');
		*/

		$supplierData1 = $this->B2BModel->getUersB2BDetailsByShopId($id);

		$supplierData = $this->B2BModel->getUersNewB2BDetailsByShopId($id,$shop_id);
		if(!empty($supplierData))
		{
			//$data['supplierData'] = $supplierData;
			$supplierData->payments_terms_upload = $supplierData1->payments_terms_upload;
			$supplierData->terms_condition_upload = $supplierData1->terms_condition_upload;
			$supplierData->webshop_name = $supplierData1->webshop_name;
			$supplierData->fbc_user_id = $supplierData1->fbc_user_id;
			$supplierData->shop_id = $supplierData1->shop_id;
			$supplierData->owner_name = $supplierData1->owner_name;

			$data['supplierData'] = $supplierData;

			if(empty($supplierData)){
				redirect(base_url());
			}
			$data['catData'] = $catData = $this->SupplierModel->getB2BCatDetailsRewised($supplierData->shop_id);  //new getB2BCatDetailsRewised  old getB2BCatDetails
			$shop_id=$supplierData->shop_id;

		}else
		{
			//Default b2b details
			$data['supplierData'] = $supplierData1;
			if(empty($supplierData1)){
				redirect(base_url());
			}
			$data['catData'] = $catData = $this->SupplierModel->getB2BCatDetailsRewised($supplierData1->shop_id);  //new getB2BCatDetailsRewised  old getB2BCatDetails
			$shop_id=$supplierData1->shop_id;
		}

		$data['FbcUserB2BData']=$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$shop_id),'');

		$this->load->view('seller/suppliers/supplier_details',$data);
	}

	public function postCheckedCatList(){
		if(isset($_POST) && isset($_POST['product_list_next_btn'])){
			$shop_id = isset($_POST['shop_id'])?$_POST['shop_id']:'';
			$checkedCatArr = isset($_POST['checked_cat'])?$_POST['checked_cat']:array();
			if(is_array($checkedCatArr) && count($checkedCatArr) <= 0){
				echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));
				exit;
			}
			$data['shop_id'] = $shop_id;
			$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'fbc_user_id,currency_symbol,currency_code');
			$data['currency_symbol']=(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;
			$data['fbc_user_id'] = $shopData->fbc_user_id;


			if(is_array($checkedCatArr) && count($checkedCatArr)>0){
				foreach($checkedCatArr as $key=>$value){
					//$productData['product_'.$key] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);
					$data['productData'][] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);
				}
			}
			//echo"<pre>";print_r($data);exit;
			$data['FbcUserB2BData']=$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$shop_id),'');
			$theHTMLResponse = $this->load->view('seller/suppliers/supplier_product_list',$data, true);

			echo json_encode(array('flag' => 1, 'msg' => $theHTMLResponse));
			exit;

		}else{
			echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));
			exit;
		}
	}

	public function postCheckedCatListRevised(){
		//print_r($_GET);
		$LoginID = $this->session->userdata('LoginID');
		$CurrentuserData = $this->UserModel->getUserByUserId($LoginID);
		$Current_user_shop_id = $CurrentuserData->shop_id;

		if(isset($_POST) && isset($_POST['product_list_next_btn'])){
			$shop_id = isset($_POST['shop_id'])?$_POST['shop_id']:'';
			$checkedCatArr = isset($_POST['checked_cat'])?$_POST['checked_cat']:array();
			if(is_array($checkedCatArr) && count($checkedCatArr) <= 0){
				echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));
				exit;
			}
			$data['shop_id'] = $shop_id;
			$data['CountAppliedProduct'] = $this->SupplierModel->getAppliedProductsCount($shop_id);

			$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'fbc_user_id,currency_symbol,currency_code');
			$data['currency_symbol']=(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;
			$data['fbc_user_id'] = $shopData->fbc_user_id;


			/*if(is_array($checkedCatArr) && count($checkedCatArr)>0){
				foreach($checkedCatArr as $key=>$value){
					//$productData['product_'.$key] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);
					//$data['productData'][] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);

					$data['category_ids'][] = implode(', ', $value);


				}
			}*/
			$data['category_ids'] = implode(',', $checkedCatArr);
			$data['FbcUserB2BData']=$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$shop_id),'');
			$data['B2BCustomer_Details'] = $B2BCustomer_Details  = $this->B2BImportModel->getB2BCustomerDetails($shop_id,$Current_user_shop_id);
			$theHTMLResponse = $this->load->view('seller/suppliers/supplier_product_listRevised',$data,true);

			echo json_encode(array('flag' => 1, 'msg' => $theHTMLResponse));
			exit;



		}else{
			echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));
			exit;
		}
	}

	public function postCheckedProductList(){
		//echo "<pre>";print_r($_POST);exit;
		$LoginID = $this->session->userdata('LoginID');
		if(isset($_POST)){
			$shop_id = (isset($_POST['shop_id']) && $_POST['shop_id'] != '')?$_POST['shop_id']:'';
			$draft_id = (isset($_POST['draft_id']) && $_POST['draft_id'] != '')?$_POST['draft_id']:'';
			$saved_order_id = (isset($_POST['saved_order_id']) && $_POST['saved_order_id'] != '')?$_POST['saved_order_id']:'';
			$checkedProductArr=isset($_POST['checkedProduct'])?$_POST['checkedProduct']:array();

			$show_flag = (isset($_POST['show_flag']) && $_POST['show_flag'] != '')?$_POST['show_flag']:0;

			if(is_array($checkedProductArr) && count($checkedProductArr) <= 0){
				echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Product", 'product_id' => ''));
				exit;
			}
			$i = 0;
			$bProCount = 0;
			$bProWithouQtyCount= 0;
			$vProQtyCount = 0;
			$vProCount = 0;
			$dProCount = 0;
			$totalBuyinPrice = 0;
			$totalBuyinWithouQtyPrice = 0;
			$totalVirtualQtyPrice = 0;
			$totalVirtualPrice = 0;
			$totalDropshipPrice = 0;
			$product_inv_type = '';
			$categoryArr = array();

			//print_r($_POST);

			if(is_array($checkedProductArr) && count($checkedProductArr) > 0){

				foreach($checkedProductArr as $key=>$val){
					$product_id = $val;

					$category_id=isset($_POST['category_'.$product_id])?$_POST['category_'.$product_id]:'';


					$buyin=isset($_POST['buyin_'.$category_id.'_'.$product_id])?$_POST['buyin_'.$category_id.'_'.$product_id]:'';
					$virtual=isset($_POST['virtual_'.$category_id.'_'.$product_id])?$_POST['virtual_'.$category_id.'_'.$product_id]:'';
					$dropship=isset($_POST['dropship_'.$category_id.'_'.$product_id])?$_POST['dropship_'.$category_id.'_'.$product_id]:'';
					$qty=(isset($_POST['qty_'.$category_id.'_'.$product_id]) && $_POST['qty_'.$category_id.'_'.$product_id] != '')?$_POST['qty_'.$category_id.'_'.$product_id]:0;

					$price=isset($_POST['price_'.$category_id.'_'.$product_id])?$_POST['price_'.$category_id.'_'.$product_id]:'';
					$parent_id=isset($_POST['parent_'.$category_id.'_'.$product_id])?$_POST['parent_'.$category_id.'_'.$product_id]:'';

					/*if(!empty($buyin) && ($qty == '' || $qty <= 0)){
						echo json_encode(array('flag' => 0, 'msg' => 'Quantity should not be blank or 0', 'product_id' => $product_id));
						exit;
					}*/

					if(!empty($buyin)){
						//$bProCount ++;
						//$bProCount += $qty;
						//$totalBuyinPrice += ($qty * $price);
						//$product_inv_type = 'buy';

						$product_inv_type = 'buy';
						if( $qty != 0 ){

							$bProCount += $qty;
							$totalBuyinPrice += ($qty * $price);
						} else {
							$bProWithouQtyCount ++;
							$totalBuyinWithouQtyPrice += ($qty * $price);
						}

					}

					if(!empty($virtual)){
						$product_inv_type = 'virtual';
						if( $qty != 0 ){
							//$vProQtyCount ++;
							$vProQtyCount += $qty;
							$totalVirtualQtyPrice += ($qty * $price);
						} else {
							$vProCount ++;
							$totalVirtualPrice += ($qty * $price);
						}
					}

					if(!empty($dropship)){
						$dProCount ++;
						//$totalDropshipPrice += $price;
						$product_inv_type = 'dropship';
					}


					$categoryArr[] = $category_id;
				}
				$totalProCount = $bProCount + $bProWithouQtyCount + $vProQtyCount + $vProCount + $dProCount;
				$categoryArr = array_filter(array_unique($categoryArr));

				$category_ids='';
				if(isset($categoryArr) && count($categoryArr)>0){
					$category_ids = implode(',',$categoryArr);
					$category_ids = ','.$category_ids.',';
				}
				//print_r($category_ids);
				if(!empty($draft_id)){
					$draftData=$this->SupplierModel->getSingleDataByID('b2b_orders_draft',array('id'=>$draft_id),'');
					$draft=$this->SupplierModel->getMultiDataById('b2b_orders_draft_details',array('draft_order_id'=>$draft_id),'');
					if(isset($draftData) && $draftData->id != '' ){
						$updateData=array(
								//'supplier_shop_id'=>$draftData->shop_id,
								'total_categories_ids'=>$category_ids,
								'total_categories_count'=>count($categoryArr),
								'total_products_count'=>$totalProCount,
								'total_buyin_products'=>$bProCount,
								'total_buyin_cost'=>$totalBuyinPrice,
								'total_buyin_products_withoutqty'=>$bProWithouQtyCount,
								'total_buyin_cost_withoutqty'=>$totalBuyinWithouQtyPrice,
								'total_virtual_products_withqty'=>$vProQtyCount,
								'total_virtual_cost_withqty'=>$totalVirtualQtyPrice,
								'total_virtual_products'=>$vProCount,
								'total_virtual_cost'=>$totalVirtualPrice,
								'total_dropship_products'=>$dProCount,
								'total_dropship_cost'=>$totalDropshipPrice,
								//'created_by'=>$LoginID,
								'updated_at'=>time(),
								'show_flag'=>$show_flag,
								//'ip'=>$_SERVER['REMOTE_ADDR']
						);

						$where_arr=array('id'=>$draft_id, 'supplier_shop_id'=>$draftData->supplier_shop_id);
						$isRowAffected = $this->SupplierModel->updateData('b2b_orders_draft',$where_arr,$updateData);
						if(isset($draft) && count($draft)>0){
							foreach($draft as $val){
								$at_whr = array('draft_order_id'=>$draft_id,'id'=>$val->id);
								$this->SupplierModel->deleteDataById('b2b_orders_draft_details',$at_whr);
							}
						}
					}
				}else{
					$insertdata=array(
							'supplier_shop_id'=>$shop_id,
							'total_categories_ids'=>$category_ids,
							'total_categories_count'=>count($categoryArr),
							'total_products_count'=>$totalProCount,
							'total_buyin_products'=>$bProCount,
							'total_buyin_cost'=>$totalBuyinPrice,
							'total_buyin_products_withoutqty'=>$bProWithouQtyCount,
							'total_buyin_cost_withoutqty'=>$totalBuyinWithouQtyPrice,
							'total_virtual_products_withqty'=>$vProQtyCount,
							'total_virtual_cost_withqty'=>$totalVirtualQtyPrice,
							'total_virtual_products'=>$vProCount,
							'total_virtual_cost'=>$totalVirtualPrice,
							'total_dropship_products'=>$dProCount,
							'total_dropship_cost'=>$totalDropshipPrice,
							'created_by'=>$LoginID,
							'created_at'=>time(),
							'show_flag'=>$show_flag,
							'ip'=>$_SERVER['REMOTE_ADDR']
					);
					$draft_id=$this->SupplierModel->insertData('b2b_orders_draft',$insertdata);
				}

				foreach($checkedProductArr as $key=>$val){
					$i +=1;
					$product_id = $val;
					$category_id=isset($_POST['category_'.$product_id])?$_POST['category_'.$product_id]:''; //new rule

					$buyin=isset($_POST['buyin_'.$category_id.'_'.$product_id])?$_POST['buyin_'.$category_id.'_'.$product_id]:'';
					$virtual=isset($_POST['virtual_'.$category_id.'_'.$product_id])?$_POST['virtual_'.$category_id.'_'.$product_id]:'';
					$dropship=isset($_POST['dropship_'.$category_id.'_'.$product_id])?$_POST['dropship_'.$category_id.'_'.$product_id]:'';
					$qty=(isset($_POST['qty_'.$category_id.'_'.$product_id]) && $_POST['qty_'.$category_id.'_'.$product_id] != '')?$_POST['qty_'.$category_id.'_'.$product_id]:0;

					$price=isset($_POST['price_'.$category_id.'_'.$product_id])?$_POST['price_'.$category_id.'_'.$product_id]:'';
					$parent_id=isset($_POST['parent_'.$category_id.'_'.$product_id])?$_POST['parent_'.$category_id.'_'.$product_id]:'';



					if(!empty($buyin)){
						$product_inv_type = 'buy';
					}

					if(!empty($virtual)){
						$product_inv_type = 'virtual';
					}

					if(!empty($dropship)){
						$product_inv_type = 'dropship';
					}

					if($draft_id){
						$insertdata=array(
								'draft_order_id'=>$draft_id,
								'product_id'=>$product_id,
								'parent_id'=>$parent_id,
								'product_inv_type'=>$product_inv_type,
								'quantity'=>$qty,
								'price'=>$price,
								'category_id'=>$category_id
						);
						$product_id=$this->SupplierModel->insertData('b2b_orders_draft_details',$insertdata);
					}
				}
				if($draft_id){
					$data['shop_id']= $shop_id;
					$data['draft_id']= $draft_id;
					$data['saved_order_id']= $saved_order_id;
					$shopData = $supplierData = $this->UserModel->getShopDetailsByShopId($shop_id);
					$data['currency_symbol']=(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;
					$data['webshop_name']= $shopData->org_shop_name;

					$data['supplier_currency_code']=(isset($shopData->currency_code))?$shopData->currency_code:"";

					$userData = $supplierData = $this->UserModel->getUserByUserId($shopData->fbc_user_id);
					$data['owner_name']= $userData->owner_name;

					#Neha Added
					$CurrentuserData = $this->UserModel->getUserByUserId($LoginID);
					$Current_user_shop_id = $CurrentuserData->shop_id;
					$Current_user_shopData = $this->UserModel->getShopDetailsByShopId($Current_user_shop_id);
					$data['current_user_webshop_name']= $Current_user_shopData->org_shop_name;

					$data['buyer_currency_code']=(isset($Current_user_shopData->currency_code))?$Current_user_shopData->currency_code:"";

					$product_ids = $this->SupplierModel->getProductIds($Current_user_shop_id,$draft_id);
					$uniqueProductIds = implode(',', array_unique(explode(',', $product_ids->product_id)));

					$data['buyer_Customer_type'] = $this->CommonModel->get_customer_types();
					#End Neha Added

					//echo $draft_id.'============='.$saved_order_id;exit;

					if(isset($saved_order_id) && $saved_order_id>0){
						$data['draftData']=$savedData=$this->SupplierModel->getSingleDataByID('b2b_orders_saved',array('id'=>$saved_order_id),'');
						$data['show_flag'] = $savedData->show_flag;
						$data['SavedCustomerType'] = $this->SupplierModel->getMultiDataById('b2b_orders_saved_custypedetails',array('saved_order_id'=>$saved_order_id),'');
					}else{
						$data['draftData']=$draftData=$this->SupplierModel->getSingleDataByID('b2b_orders_draft',array('id'=>$draft_id),'');
						$data['show_flag'] = $draftData->show_flag;
						$data['SavedCustomerType'] = array();
					}
					$data['FbcUserB2BData']=$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$shop_id),'');


					$getCustomerTypes = $this->SupplierModel->getCustomerTypesByProductsId($shop_id,$uniqueProductIds);
					if($getCustomerTypes->customer_type !=NULL){
						$uniqueCustomerTypes = implode(',', array_unique(explode(',', $getCustomerTypes->customer_type)));
					}else{
						$uniqueCustomerTypes = 0;
					}

					$data['seller_selected_Customer_type'] = $this->SupplierModel->get_customer_types_selected($shop_id,$uniqueCustomerTypes);

					$seller_selected_Customer_type = explode(',', $uniqueCustomerTypes);
					if (in_array(0, $seller_selected_Customer_type)) {
						$data['AllExist'] = 'yes';
					}else {
						$data['AllExist'] = 'no';
					}

					$data['seller_ct_count'] = count($data['seller_selected_Customer_type']);


					$theHTMLResponse = $this->load->view('seller/suppliers/order_overview',$data, true);
					echo json_encode(array('flag' => 1, 'msg' => $theHTMLResponse));
					exit;
				}
			}else{
				echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Product", 'product_id' => ''));
				exit;
			}
		}else{
			echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Product", 'product_id' => ''));
			exit;
		}
	}

	public function checkProductQty(){
		//print_r($_POST);//exit;

		if(isset($_POST) && !empty($_POST['shop_id']) && !empty($_POST['product_id'])){
			$shop_id = $_POST['shop_id'];
			$product_id = $_POST['product_id'];
			$qty = $_POST['qty'];
			$flag = $_POST['flag'];

			if($_POST['qty'] == '' || $_POST['qty'] == 0){
				if($flag == 'buyin'){
					echo json_encode(array('flag' => 0, 'msg' => 'Quantity should not be blank or 0'));
					exit;
				}else{
					echo json_encode(array('flag' => 1, 'msg' => 'success'));
					exit;
				}
			}
			// echo $qty;
			if($qty != 0){
				$productStock = $this->SupplierModel->getProductStock($shop_id,$product_id);
				if($qty > $productStock->qty){
					echo json_encode(array('flag' => 0, 'product_id' => $product_id, 'msg' => 'Quantity is exceeded'));
					exit;
				}else{
					echo json_encode(array('flag' => 1, 'product_id' => $product_id, 'msg' => 'success'));
					exit;
				}
			}
		}
	}

	public function postSupplierOrder(){
		//print_r($_POST);exit;
		$LoginID = $this->session->userdata('LoginID');
		$ShopID  = $this->session->userdata('ShopID');
		if(isset($_POST) && (isset($_POST['save_order']) || isset($_POST['apply_order']))){

			$price_converted = (isset($_POST['price_converted']) && $_POST['price_converted']!= '')?$_POST['price_converted']:'';
			$draft_id = (isset($_POST['draft_id']) && $_POST['draft_id']!= '')?$_POST['draft_id']:'';
			$saved_order_id = (isset($_POST['saved_order_id']) && $_POST['saved_order_id']!= '')?$_POST['saved_order_id']:'';

			if(!empty($draft_id)){
				$draftData=$this->SupplierModel->getSingleDataByID('b2b_orders_draft',array('id'=>$draft_id),'');
				$draft=$this->SupplierModel->getMultiDataById('b2b_orders_draft_details',array('draft_order_id'=>$draft_id),'');

				if(isset($draftData) && $draftData->id != '' ){
					if(isset($_POST['save_order'])){

						if(empty($saved_order_id)){


							$insertdatamain=array(
									'supplier_shop_id'=>$draftData->supplier_shop_id,
									'total_categories_ids'=>$draftData->total_categories_ids,
									'total_categories_count'=>$draftData->total_categories_count,
									'total_products_count'=>$draftData->total_products_count,
									'total_buyin_products'=>$draftData->total_buyin_products,
									'total_buyin_cost'=>$draftData->total_buyin_cost,
									'total_buyin_products_withoutqty'=>$draftData->total_buyin_products_withoutqty,
									'total_buyin_cost_withoutqty'=>$draftData->total_buyin_cost_withoutqty,
									'total_virtual_products_withqty'=>$draftData->total_virtual_products_withqty,
									'total_virtual_cost_withqty'=>$draftData->total_virtual_cost_withqty,
									'total_virtual_products'=>$draftData->total_virtual_products,
									'total_virtual_cost'=>$draftData->total_virtual_cost,
									'total_dropship_products'=>$draftData->total_dropship_products,
									'total_dropship_cost'=>$draftData->total_dropship_cost,
									'created_by'=>$LoginID,
									'created_at'=>time(),
									'show_flag' => $_POST['show_flag'],
									'ip'=>$_SERVER['REMOTE_ADDR']
							);
							$saved_id=$this->SupplierModel->insertData('b2b_orders_saved',$insertdatamain);
							if($saved_id){
								if(is_array($draft) && count($draft) > 0){
									foreach($draft as $value){
										$insertdatasaveitem=array(
												'saved_order_id'=>$saved_id,
												'product_id'=>$value->product_id,
												'parent_id'=>$value->parent_id,
												'product_inv_type'=>$value->product_inv_type,
												'quantity'=>$value->quantity,
												'price'=>$value->price,
												'category_id'=>$value->category_id
										);
										$isRowAffected=$this->SupplierModel->insertData('b2b_orders_saved_details',$insertdatasaveitem);
									}

									if(isset($_POST['seller_all'] )){
										$insertdatasaveCustType=array(
											'saved_order_id'=>$saved_id,
											'supplier_customer_type_id'=>$_POST['seller_all'],
											'buyer_customer_type_id'=>$_POST['buyer_all'],

										);
										$isRowAffectedAll=$this->SupplierModel->insertData('b2b_orders_saved_custypedetails',$insertdatasaveCustType);
									}
									if($_POST['count_other_types'] > 0) {

										for($i = 1; $i <= $_POST['count_other_types']; $i++ ){

											$insertdatasaveCustTypeOther=array(
												'saved_order_id'=>$saved_id,
												'supplier_customer_type_id'=>$_POST['seller_count_'.$i],
												'buyer_customer_type_id'=>$_POST['buyer_count_'.$i],

											);
											$isRowAffectedOther=$this->SupplierModel->insertData('b2b_orders_saved_custypedetails',$insertdatasaveCustTypeOther);


										}
									}


								}
							}
						}else{


							$savedData=$this->SupplierModel->getSingleDataByID('b2b_orders_saved',array('id'=>$saved_order_id,'supplier_shop_id'=>$draftData->supplier_shop_id),'');

							$savedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_saved_details',array('saved_order_id'=>$saved_order_id),'');
							$updateData=array(
									//'supplier_shop_id'=>$draftData->supplier_shop_id,
									'total_categories_ids'=>$draftData->total_categories_ids,
									'total_categories_count'=>$draftData->total_categories_count,
									'total_products_count'=>$draftData->total_products_count,
									'total_buyin_products'=>$draftData->total_buyin_products,
									'total_buyin_cost'=>$draftData->total_buyin_cost,
									'total_buyin_products_withoutqty'=>$draftData->total_buyin_products_withoutqty,
									'total_buyin_cost_withoutqty'=>$draftData->total_buyin_cost_withoutqty,
									'total_virtual_products_withqty'=>$draftData->total_virtual_products_withqty,
									'total_virtual_cost_withqty'=>$draftData->total_virtual_cost_withqty,
									'total_virtual_products'=>$draftData->total_virtual_products,
									'total_virtual_cost'=>$draftData->total_virtual_cost,
									'total_dropship_products'=>$draftData->total_dropship_products,
									'total_dropship_cost'=>$draftData->total_dropship_cost,
									//'created_by'=>$LoginID,
									'updated_at'=>time(),
									'show_flag' => $_POST['show_flag'],
									//'ip'=>$_SERVER['REMOTE_ADDR']
							);

							$where_arr=array('id'=>$saved_order_id, 'supplier_shop_id'=>$draftData->supplier_shop_id);
							$isRowAffected = $this->SupplierModel->updateData('b2b_orders_saved',$where_arr,$updateData);
							if(isset($savedDetails) && count($savedDetails)>0){
								foreach($savedDetails as $val){
									$at_whr = array('saved_order_id'=>$saved_order_id,'id'=>$val->id);
									$this->SupplierModel->deleteDataById('b2b_orders_saved_details',$at_whr);
								}
							}
							if(is_array($draft) && count($draft) > 0){
								foreach($draft as $value){
									$insertdatasitem=array(
											'saved_order_id'=>$saved_order_id,
											'product_id'=>$value->product_id,
											'parent_id'=>$value->parent_id,
											'product_inv_type'=>$value->product_inv_type,
											'quantity'=>$value->quantity,
											'price'=>$value->price,
											'category_id'=>$value->category_id

									);
									$isRowAffected=$this->SupplierModel->insertData('b2b_orders_saved_details',$insertdatasitem);
								}
							}

							$at_whr = array('saved_order_id'=>$saved_order_id);
						 	$this->SupplierModel->deleteDataById('b2b_orders_saved_custypedetails',$at_whr);

							if(isset($_POST['seller_all'] )){
								$insertdatasaveCustType=array(
									'saved_order_id'=>$saved_order_id,
									'supplier_customer_type_id'=>$_POST['seller_all'],
									'buyer_customer_type_id'=>$_POST['buyer_all'],

								);
								$isRowAffectedAll=$this->SupplierModel->insertData('b2b_orders_saved_custypedetails',$insertdatasaveCustType);
							}

							if($_POST['count_other_types'] > 0) {

								for($i = 1; $i <= $_POST['count_other_types']; $i++ ){

									$insertdatasaveCustTypeOther=array(
										'saved_order_id'=>$saved_order_id,
										'supplier_customer_type_id'=>$_POST['seller_count_'.$i],
										'buyer_customer_type_id'=>$_POST['buyer_count_'.$i],

									);
									$isRowAffectedOther=$this->SupplierModel->insertData('b2b_orders_saved_custypedetails',$insertdatasaveCustTypeOther);


								}
							}

						}

						$redirect = BASE_URL.'seller/supplier-saved-orders/';
					}

					if(isset($_POST['apply_order'])){

						if(isset($_POST['seller_all'] )){
							if($_POST['buyer_all'] == null || $_POST['buyer_all'] == ""){
								echo json_encode(array('flag' => 0, 'msg' => 'Please select Customer Type'));
								exit;
							}
						}


						if($_POST['count_other_types'] > 0) {
							for($i = 1; $i <= $_POST['count_other_types']; $i++ ){
								if($_POST['buyer_count_'.$i] == null || $_POST['buyer_count_'.$i] == ""){
									echo json_encode(array('flag' => 0, 'msg' => 'Please select Customer Type'));
									exit;
								}

							}
						}

						if(!empty($saved_order_id)){
							$draftData=$this->SupplierModel->getSingleDataByID('b2b_orders_saved',array('id'=>$saved_order_id,'supplier_shop_id'=>$draftData->supplier_shop_id),'');
							$draft=$this->SupplierModel->getMultiDataById('b2b_orders_saved_details',array('saved_order_id'=>$saved_order_id),'');
						}
						else if(!empty($draft_id))
						{
							$draftData=$this->SupplierModel->getSingleDataByID('b2b_orders_draft',array('id'=>$draft_id,'supplier_shop_id'=>$draftData->supplier_shop_id),'');
							$draft=$this->SupplierModel->getMultiDataById('b2b_orders_draft_details',array('draft_order_id'=>$draft_id),'');
						}



						$supplierData = $this->B2BModel->getUersB2BDetailsByShopId($draftData->supplier_shop_id);

						$buyin_cost = $draftData->total_buyin_cost + $draftData->total_virtual_cost_withqty;

						$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$draftData->supplier_shop_id),'');
						if((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount>0) && $buyin_cost>0){
							$RowTotalData=$this->CommonModel->calculate_percent_data($buyin_cost,$FbcUserB2BData->buyin_discount);
							$percent_amount=$RowTotalData['percent_amount'];
							$net_pay_amount=$buyin_cost-$percent_amount;
						}else{
							$percent_amount=0;
							$net_pay_amount=$buyin_cost;
						}

						$insertdata_applied=array(
								'supplier_shop_id'=>$draftData->supplier_shop_id,
								'total_categories_ids'=>$draftData->total_categories_ids,
								'total_categories_count'=>$draftData->total_categories_count,
								'total_products_count'=>$draftData->total_products_count,
								'total_buyin_products'=>$draftData->total_buyin_products,
								'total_buyin_cost'=>$draftData->total_buyin_cost,
								'total_buyin_products_withoutqty'=>$draftData->total_buyin_products_withoutqty,
								'total_buyin_cost_withoutqty'=>$draftData->total_buyin_cost_withoutqty,
								'total_virtual_products_withqty'=>$draftData->total_virtual_products_withqty,
								'total_virtual_cost_withqty'=>$draftData->total_virtual_cost_withqty,
								'total_virtual_products'=>$draftData->total_virtual_products,
								'total_virtual_cost'=>$draftData->total_virtual_cost,
								'total_dropship_products'=>$draftData->total_dropship_products,
								'total_dropship_cost'=>$draftData->total_dropship_cost,
								'dropship_discount'=>$supplierData->dropship_discount,
								'buyin_discount'=>$FbcUserB2BData->buyin_discount,
								'buyin_discount_amount'=>$percent_amount,
								'total_buyin_net_cost'=>$net_pay_amount,
								'price_converted'=>$price_converted,
								'created_by'=>$LoginID,
								'created_at'=>time(),
								'ip'=>$_SERVER['REMOTE_ADDR']
						);
						$applied_id=$this->SupplierModel->insertData('b2b_orders_applied',$insertdata_applied);
						if($applied_id){

							if(isset($draft) && count($draft) > 0){

							$toShopData = $supplierData = $this->UserModel->getShopDetailsByShopId($draftData->supplier_shop_id);
							$to_shop_id = $draftData->supplier_shop_id;
							$to_fbc_user_id = $toShopData->fbc_user_id;

							$args['shop_id']	=	$to_shop_id;
							$args['fbc_user_id']	=	$to_fbc_user_id;

							$this->load->model('ShopProductModel');
							$this->ShopProductModel->init($args);

								foreach($draft as $value){

									$importedProductData=$this->ShopProductModel->getSingleDataByID('products',array('id'=>$value->product_id),'');
									if(isset($importedProductData) && $importedProductData->id!=''){
										$product_type = $importedProductData->product_type;
										$productName = $importedProductData->name;
										$OptionValue = array();
										if($product_type == 'conf-simple'){
											$VariantMaster=$this->ShopProductModel->getVariantDetailsForProducts($draftData->supplier_shop_id,$importedProductData->id);

											if(isset($VariantMaster) && count($VariantMaster)>0){
												foreach($VariantMaster as $attr){
													$OptionValue[] = array($attr->attr_name => $attr->attr_options_name);
													//echo '<pre>';print_r($OptionValue);
												}
											}
											$productVariants = json_encode($OptionValue);
										}else{
											$productVariants = '';
										}
									}else{
										$productName = '';
										$productVariants = '';
									}


									$insernewapplyitem=array(
											'applied_order_id'=>$applied_id,
											'product_id'=>$value->product_id,
											'parent_id'=>$value->parent_id,
											'product_inv_type'=>$value->product_inv_type,
											'quantity'=>$value->quantity,
											'price'=>$value->price,
											'category_id'=>$value->category_id,
											'product_name'=>$productName,
											'product_variants'=>$productVariants
									);
									$this->SupplierModel->insertData('b2b_orders_applied_details',$insernewapplyitem);

								}

								if(isset($_POST['seller_all'] )){
									$insertdataApplyCustType=array(
										'applied_order_id'=>$applied_id,
										'supplier_customer_type_id'=>$_POST['seller_all'],
										'buyer_customer_type_id'=>$_POST['buyer_all'],

									);
									$isRowAffectedAll=$this->SupplierModel->insertData('b2b_orders_applied_custypedetails',$insertdataApplyCustType);
								}

								if($_POST['count_other_types'] > 0) {

									for($i = 1; $i <= $_POST['count_other_types']; $i++ ){

										$insertdataApplyCustTypeOther=array(
											'applied_order_id'=>$applied_id,
											'supplier_customer_type_id'=>$_POST['seller_count_'.$i],
											'buyer_customer_type_id'=>$_POST['buyer_count_'.$i],

										);
										$isRowAffectedOther=$this->SupplierModel->insertData('b2b_orders_applied_custypedetails',$insertdataApplyCustTypeOther);


									}
								}

							}
						}

						//B2B Customer Insert
						$Checkb2bcustomerExist=$this->SupplierModel->getSupplierCustomerByShopId($draftData->supplier_shop_id,$ShopID);

						//print_r($Checkb2bcustomerExist);
						if(isset($Checkb2bcustomerExist) && $Checkb2bcustomerExist->id!=''){

						}else{
							//$b2b_customer_insert=array('shop_id'=>$ShopID,'created_at'=> time(), 'ip'=> $_SERVER['REMOTE_ADDR']);
							$cust_id= $this->SupplierModel->insertB2BCustDataOtherDB($draftData->supplier_shop_id,$ShopID);
							$b2bCustomerDetailExist = $this->SupplierModel->getB2bCustomerdetailsByShopId($draftData->supplier_shop_id,$cust_id);

							if(isset($b2bCustomerDetailExist) && $b2bCustomerDetailExist->id!=''){

							}else{
								$b2bCustomerDetail = $this->SupplierModel->insertB2BCustomerDetail($draftData->supplier_shop_id,$cust_id);
							}
						}



						if(!empty($saved_order_id)){
							$savedData=$this->SupplierModel->getSingleDataByID('b2b_orders_saved',array('id'=>$saved_order_id,'supplier_shop_id'=>$draftData->supplier_shop_id),'');
							$savedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_saved_details',array('saved_order_id'=>$saved_order_id),'');



							if(isset($savedDetails) && count($savedDetails)>0){
								foreach($savedDetails as $val){
									$at_whr = array('saved_order_id'=>$saved_order_id,'id'=>$val->id);
									$this->SupplierModel->deleteDataById('b2b_orders_saved_details',$at_whr);

								}
							}

							if(isset($savedData)){
								$at_whr = array('id'=>$savedData->id);
								$this->SupplierModel->deleteDataById('b2b_orders_saved',$at_whr);

							}
						}

						$toShopData = $supplierData = $this->UserModel->getShopDetailsByShopId($draftData->supplier_shop_id);
						$to_shop_id = $draftData->supplier_shop_id;
						$to_fbc_user_id = $toShopData->fbc_user_id;

						$shopData = $supplierData = $this->UserModel->getShopDetailsByShopId($ShopID);
						$webshop_name= $shopData->org_shop_name;
						$from_shop_id = $ShopID;
						$from_fbc_user_id= $LoginID;
						$notification_text = "New order from ".$webshop_name;

						$type=1; //1-b2b_order_request

						$args['shop_id']	=	$to_shop_id;
						$args['fbc_user_id']	=	$to_fbc_user_id;

						$this->load->model('ShopProductModel');
						$this->ShopProductModel->init($args);

						$insertNotiData = array(
						   'from_shop_id' 		=> $from_shop_id,
						   'from_fbc_user_id'	=> $from_fbc_user_id,
						   'to_shop_id'			=> $to_shop_id,
						   'to_fbc_user_id' 	=> $to_fbc_user_id,
						   'shop_id' 			=> $ShopID,
						   'area_id' 			=> $applied_id,
						   'notification_text'	=> $notification_text,
						   'notification_type' 	=> $type,
						   'created_at'   		=> strtotime(date('Y-m-d H:i:s')),
						   'ip'					=>$_SERVER['REMOTE_ADDR']
						);

						$isRowAffected=$this->ShopProductModel->insertData('notifications',$insertNotiData);

						/*-----------Order Notification-----------*/
						$redirect = BASE_URL.'seller/supplier-applied-orders/';


					}

					if($isRowAffected){
						if(isset($draft) && count($draft)>0){
							foreach($draft as $val){
								$at_whr = array('draft_order_id'=>$draft_id,'id'=>$val->id);
								$this->SupplierModel->deleteDataById('b2b_orders_draft_details',$at_whr);

							}
						}

						if(isset($draftData)){
							$at_whr = array('id'=>$draftData->id);
							$this->SupplierModel->deleteDataById('b2b_orders_draft',$at_whr);

						}
					}

				}

				//echo $redirect.'===========';exit;


				echo json_encode(array('flag' => 1, 'msg' => 'Success', 'redirect' => $redirect));
				exit;
			}
		}
	}

	public function getSavedOrders(){
		$data['PageTitle']='Saved Orders List';
		$data['side_menu']='saved';
		$data['savedOrders'] = $savedOrders = $this->SupplierModel->getSavedOrders();
		//print_r($savedOrders );
		$this->load->view('seller/suppliers/saved_tab',$data);
	}

	public function getDraftSavedOrderDetail(){
		$order_type=$this->uri->segment(2);
		$shop_id=$this->uri->segment(4);
		$saved_order_id = '';
		$draft_id = '';
		if($order_type == 'saved-orders'){
			$saved_order_id=$this->uri->segment(5);
			if(empty($shop_id) || empty($saved_order_id)){
				redirect('/seller/supplier-saved-orders/');
			}
		}
		if($order_type == 'draft-orders'){
			$draft_id=$this->uri->segment(5);
			if(empty($shop_id) || empty($draft_id)){
				redirect('/seller/supplier-list/');
			}
		}

		$data['PageTitle']='Supplier Detail';
		$data['side_menu']='search';
		$data['shop_id'] = $shop_id;
		$data['saved_order_id'] = $saved_order_id;
		$data['draft_id'] = $draft_id;
		$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'fbc_user_id,currency_symbol,currency_code');
		$data['currency_symbol']=(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;
		$data['fbc_user_id'] = $shopData->fbc_user_id;

		if($order_type == 'saved-orders'){
			$savedData=$this->SupplierModel->getSingleDataByID('b2b_orders_saved',array('id'=>$saved_order_id,'supplier_shop_id'=>$shop_id),'');
			$data['savedDetails'] = $savedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_saved_details',array('saved_order_id'=>$saved_order_id),'');
		}

		 if($order_type == 'draft-orders'){
			$savedData=$this->SupplierModel->getSingleDataByID('b2b_orders_draft',array('id'=>$draft_id,'supplier_shop_id'=>$shop_id),'');
			$data['savedDetails'] = $savedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_draft_details',array('draft_order_id'=>$draft_id),'');

		}

		if(isset($savedData) && $savedData->id != ''){
			//$category_ids = trim($savedData->total_categories_ids,',');
			$category_ids = explode(',',$savedData->total_categories_ids);
			$category_idsArr = array_values(array_filter($category_ids));
			if(is_array($category_idsArr) && count($category_idsArr)>0){
				foreach($category_idsArr as $key=>$value){
					//$productData['product_'.$key] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);
					$data['productData'][] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);
				}
			}
			//echo"<pre>";print_r($data);exit;
			$this->load->view('seller/suppliers/edit_supplier_product_list',$data);
		}else{
			redirect('/seller/supplier-saved-orders/');
		}
	}


	public function getDraftSavedOrderDetailRevised(){
		$order_type=$this->uri->segment(2);
		$shop_id=$this->uri->segment(4);
		$saved_order_id = '';
		$draft_id = '';

		$LoginID = $this->session->userdata('LoginID');
		$CurrentuserData = $this->UserModel->getUserByUserId($LoginID);
		$Current_user_shop_id = $CurrentuserData->shop_id;

		$data['FbcUserB2BData']=$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$shop_id),'');
		$data['B2BCustomer_Details'] = $B2BCustomer_Details  = $this->B2BImportModel->getB2BCustomerDetails($shop_id,$Current_user_shop_id);

		if($order_type == 'saved-orders'){
			$saved_order_id=$this->uri->segment(5);
			if(empty($shop_id) || empty($saved_order_id)){
				redirect('/seller/supplier-saved-orders/');
			}
		}
		if($order_type == 'draft-orders'){
			$draft_id=$this->uri->segment(5);
			if(empty($shop_id) || empty($draft_id)){
				redirect('/seller/supplier-list/');
			}
		}

		$data['PageTitle']='Supplier Detail';
		$data['side_menu']='search';
		$data['shop_id'] = $shop_id;

		$data['CountAppliedProduct'] = $this->SupplierModel->getAppliedProductsCount($shop_id);

		$data['saved_order_id'] = $saved_order_id;
		$data['draft_id'] = $draft_id;
		$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'fbc_user_id,currency_symbol,currency_code');
		$data['currency_symbol']=(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;
		$data['fbc_user_id'] = $shopData->fbc_user_id;

		if($order_type == 'saved-orders'){
			$savedData=$this->SupplierModel->getSingleDataByID('b2b_orders_saved',array('id'=>$saved_order_id,'supplier_shop_id'=>$shop_id),'');
			$data['show_flag'] = $savedData->show_flag;
			$data['savedDetails'] = $savedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_saved_details',array('saved_order_id'=>$saved_order_id),'');
		}

		 if($order_type == 'draft-orders'){
			$savedData=$this->SupplierModel->getSingleDataByID('b2b_orders_draft',array('id'=>$draft_id,'supplier_shop_id'=>$shop_id),'');
			$data['show_flag'] = $savedData->show_flag;
			$data['savedDetails'] = $savedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_draft_details',array('draft_order_id'=>$draft_id),'');

		}

		if(isset($savedData) && $savedData->id != ''){
			$data['category_ids'] = $category_ids = trim($savedData->total_categories_ids,',');
			/*$category_ids = explode(',',$savedData->total_categories_ids);
			$category_idsArr = array_values(array_filter($category_ids));
			if(is_array($category_idsArr) && count($category_idsArr)>0){
				foreach($category_idsArr as $key=>$value){
					//$productData['product_'.$key] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);
					$data['productData'][] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);
				}
			}*/
			//echo"<pre>";print_r($data);exit;
			$this->load->view('seller/suppliers/edit_supplier_product_listRevised',$data);
		}else{
			redirect('/seller/supplier-saved-orders/');
		}
	}

	public function getAppliedOrders(){
		$data['PageTitle']='Applied Orders List';
		$data['side_menu']='applied';
		$data['appliedOrders'] = $appliedOrders = $this->SupplierModel->getAppliedOrders();
		//print_r($savedOrders );
		$this->load->view('seller/suppliers/applied_tab',$data);
	}

	public function getAppliedOrderDetail(){

		$login_user_id	=	$this->session->userdata('LoginID');
		$login_user_shop_id		=	$this->session->userdata('ShopID');

		$order_type=$this->uri->segment(2);
		$shop_id=$this->uri->segment(4);
		$applied_order_id = '';
		if($order_type == 'applied-orders'){
			$applied_order_id=$this->uri->segment(5);
			if(empty($shop_id) || empty($applied_order_id)){
				redirect('/seller/supplier-applied-orders/');
			}
		}
		$data['PageTitle']='Applied Orders Detail';
		$data['side_menu']='applied';
		$data['shop_id']=$shop_id;

		$data['buyer_shop_id']=$login_user_shop_id;
		$data['supplier_shop_id']=$shop_id;
		$shopData = $supplierData = $this->UserModel->getShopDetailsByShopId($shop_id);
		$data['webshop_name']= $shopData->org_shop_name;
		$data['seller_currency_symbol']=(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;


		$BuyerData = $this->UserModel->getShopDetailsByShopId($login_user_shop_id);

		$data['buyer_currency_symbol']=(isset($BuyerData->currency_symbol))?$BuyerData->currency_symbol:$BuyerData->currency_code;

		$data['buyer_currency_code']=$BuyerData->currency_code;
		$data['seller_currency_code']=$shopData->currency_code;

		//echo '==========='.$BuyerData->currency_code.'---------------'.$shopData->currency_code;



		$userData = $supplierData = $this->UserModel->getUserByUserId($shopData->fbc_user_id);
		$data['owner_name']= $userData->owner_name;
		$data['appliedData']=$appliedData=$this->SupplierModel->getSingleDataByID('b2b_orders_applied',array('id'=>$applied_order_id),'');
		$data['appliedDetails'] = $appliedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_applied_details',array('applied_order_id'=>$applied_order_id),'');
		$data['appliedCustomerTypeDetails'] = $this->SupplierModel->getMultiDataById('b2b_orders_applied_custypedetails',array('applied_order_id'=>$applied_order_id),'');


		#Neha Added
		$CurrentuserData = $this->UserModel->getUserByUserId($login_user_id);
		$Current_user_shop_id = $CurrentuserData->shop_id;
		$Current_user_shopData = $this->UserModel->getShopDetailsByShopId($Current_user_shop_id);
		$data['current_user_webshop_name']= $Current_user_shopData->org_shop_name;

		$data['buyer_Customer_type'] = $this->CommonModel->get_customer_types();

		//print_r($appliedDetails);exit;
		$data['FbcUserB2BData']=$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$shop_id),'');
		$data['seller_Customer_type'] = $this->SupplierModel->getCustomerMasterByShop($shop_id);
		//$data['seller_selected_Customer_type'] = $this->SupplierModel->get_customer_types_selected($shop_id,$uniqueCustomerTypes);

		if(isset($appliedData) && $appliedData->id != ''){
			$category_ids = trim($appliedData->total_categories_ids,',');
			/*$category_ids = explode(',',$appliedData->total_categories_ids);
			$category_idsArr = array_values(array_filter($category_ids));
			if(is_array($category_idsArr) && count($category_idsArr)>0){
				foreach($category_idsArr as $key=>$value){

					$data['productData'][] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);
				}
			}*/
			$data['category_ids'] = $category_ids;
			//echo"<pre>";print_r($data);exit;
		}

		//$data['b2bCustomerDetails']=$this->SupplierModel->getSupplierCustomerByShopId($shop_id,$login_user_shop_id);

		$this->load->view('seller/suppliers/applied_order_detail',$data);
	}


	public function fetchAllProductsByCategory(){

		$LoginID = $this->session->userdata('LoginID');
		$CurrentuserData = $this->UserModel->getUserByUserId($LoginID);
		$Current_user_shop_id = $CurrentuserData->shop_id;

		$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$_POST['shop_id']),'fbc_user_id,currency_symbol,currency_code');
	  	$currency_symbol =(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;

		$ProductDetails = $this->SupplierModel->getProductDetailsByCatIdNew($_POST['shop_id'], $_POST['category_ids'], $_POST['show_flag']);
		$total_count = (is_array($ProductDetails)) ? count($ProductDetails) : 0;
		$data = array();
		$no = $_POST['start'];

		$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$_POST['shop_id']),'');
		$B2BCustomer_Details  = $this->B2BImportModel->getB2BCustomerDetails($_POST['shop_id'],$Current_user_shop_id);

		if(!empty($B2BCustomer_Details)){

			$disabled_buy = ($B2BCustomer_Details->allow_buyin==0)?'disabled':'';
			$disabled_virtual = ($B2BCustomer_Details->allow_dropship==0 && $B2BCustomer_Details->allow_buyin==0)?'disabled':'';
			$disabled_dropship = ($B2BCustomer_Details->allow_dropship==0 )?'disabled':'';
			$disabled = ($B2BCustomer_Details->allow_dropship==0 && $B2BCustomer_Details->allow_buyin==0)?'disabled':'';

		}else{

			$disabled_buy = ($FbcUserB2BData->allow_buyin==0)?'disabled':'';
			$disabled_virtual = ($FbcUserB2BData->allow_dropship==0 && $FbcUserB2BData->allow_buyin==0)?'disabled':'';
			$disabled_dropship = ($FbcUserB2BData->allow_dropship==0 )?'disabled':'';
			$disabled = ($FbcUserB2BData->allow_dropship==0 && $FbcUserB2BData->allow_buyin==0)?'disabled':'';

		}

		if(is_array($ProductDetails))
		{
			foreach ($ProductDetails as $readData) {

				$VariantMaster=$this->SupplierModel->getVariantDetailsForProducts($_POST['shop_id'],$readData->id);

				$variant = '-';
				$OptionValue = array();
				if(isset($VariantMaster) && count($VariantMaster)>0){
					foreach($VariantMaster as $attr){
						$OptionValue[] = array('label' => $attr->attr_name, 'value' => $attr->attr_options_name);
					}

					if(is_array($OptionValue) && count($OptionValue) >0){
						foreach($OptionValue as $val){
						$variant = $val['label'].': '.$val['value'].'<br>';
						}
					}else{
						$variant = '-';
					}

				}


				$launch_date = (isset($readData->launch_date) && $readData->launch_date != 0 ) ? date('d-m-Y' ,$readData->launch_date) : " ";

					/*if($readData->parent_id != 0){
						$get_launch_date = $this->SupplierModel->getLaunchDateById($_POST['shop_id'],$readData->parent_id);
						$launch_date = (isset($get_launch_date['launch_date']) && $get_launch_date['launch_date'] != 0 ) ? date('d-m-Y', $get_launch_date['launch_date']) : " ";
					}else{
						$launch_date = (isset($readData->launch_date) && $readData->launch_date != 0 ) ? date('d-m-Y' ,$readData->launch_date) : " ";
					} */

					$product_id = ($readData->parent_id != 0)?$readData->parent_id:$readData->id;
					$href = BASE_URL.'product/detail/'.$_POST['shop_id'].'/'.$product_id;


					$no++;
					$row = array();



					$row[]='<label class="checkbox"><input type="checkbox" class="form-control chk-line-'.$readData->id.' main-checkbox pid-'.$readData->category_id.'-'.$readData->id.'" data-product_id="'.$readData->id.'" value="'.$readData->id.'" id="checkedProduct_'.$readData->category_id.'_'.$readData->id.'" name="checkedProduct[]" onclick="getProductCheckUncheck(this.value,'.$readData->category_id.')"  '.$disabled.'><span class="checked"></span></label>';

					$row[]='<a class="link-purple" href="'.$href.'" target="_blank">View</a>';
					$row[]=$readData->sku;
					$row[]=$readData->name;
					$row[]=$readData->cat_name;
					$row[]=$variant;
					$row[]=($readData->qty > 0)  ? "In Stock" : "Out of Stock";
					$row[]=$launch_date;


					//$row[]='<th>Buy&nbsp;In </th>';
					$row[]='<label class="checkbox"><input type="checkbox" class="form-control chk-line-'.$readData->id.' buyin-checkbox pid-'.$readData->id.' chk-'.$readData->category_id.'-'.$readData->id.'" data-product_id="'.$readData->id.'"  id="buyin_'.$readData->category_id.'_'.$readData->id.'" name="buyin_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->id.'" onclick="getCheckboxCheckUncheck(this.value,'.$readData->category_id.')" '.$disabled_buy.' ><span class="checked"></span></label>';



					//$row[]='<th>Virtual </th>';
					$row[]='<label class="checkbox"><input type="checkbox" class="form-control chk-line-'.$readData->id.' virtual-checkbox pid-'.$readData->id.' chk-'.$readData->category_id.'-'.$readData->id.'" data-product_id="'.$readData->id.'"  id="virtual_'.$readData->category_id.'_'.$readData->id.'" name="virtual_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->id.'" onclick="getCheckboxCheckUncheck(this.value,'.$readData->category_id.')" '.$disabled_virtual.' ><span class="checked" ></span></label>';



					//$row[]='<th>Dropship </th>';
					$row[]='<label class="checkbox"><input type="checkbox" class="form-control chk-line-'.$readData->id.'  dropship-checkbox pid-'.$readData->id.' chk-'.$readData->category_id.'-'.$readData->id.'" id="dropship_'.$readData->category_id.'_'.$readData->id.'" name="dropship_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->id.'" onclick="getCheckboxCheckUncheck(this.value,'.$readData->category_id.')" data-product_id="'.$readData->id.'" '.$disabled_dropship.' ><span class="checked"></span></label>';



					$row[]='<input type="text" class="form-control qty-table-box " id="qty_'.$readData->category_id.'_'.$readData->id.'" name="qty_'.$readData->category_id.'_'.$readData->id.'" onkeypress="return isNumberKey(event);" onblur="checkQty(this.value,'.$readData->id.','.$readData->category_id.')" pattern="[0-9]*" disabled><p id="qtyError_'.$readData->category_id.'_'.$readData->id.'" class="qty-error"></p><input type="hidden" class="pcat" name="category_'.$readData->id.'" value="'.$readData->category_id.'"><input type="hidden" name="price_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->price.'"><input type="hidden" name="parent_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->parent_id.'">';

					$row[]=$currency_symbol." ".number_format($readData->price,2);




					$data[] = $row;

				}
		}


		if(isset($_REQUEST['order'][0]['column']) && $_REQUEST['order'][0]['column'] == 7 ){

			if($_REQUEST['order'][0]['dir'] == 'asc' ){

				array_multisort(array_map(function($element) {
					return strtotime($element[7]);
				}, $data), SORT_ASC, $data);
			}

			if($_REQUEST['order'][0]['dir'] == 'desc' ){

				array_multisort(array_map(function($element) {
					return strtotime($element[7]);
				}, $data), SORT_DESC, $data);

			}

		}





			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $total_count,
							"recordsFiltered" =>$total_count,
							"data" => $data,
					);

			//output to json format
			echo json_encode($output);
			exit;

	}



	public function fetchAllEditedProductsByCategory(){
		//echo $_POST['saved_order_id']."====".$_POST['draft_id']."<br>";

		$LoginID = $this->session->userdata('LoginID');
		$CurrentuserData = $this->UserModel->getUserByUserId($LoginID);
		$Current_user_shop_id = $CurrentuserData->shop_id;

		$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$_POST['shop_id']),'fbc_user_id,currency_symbol,currency_code');
	  	$currency_symbol =(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;

		if(isset($_POST['draft_id']) && $_POST['draft_id'] != NULL ){
			//echo 	"draft_id";
			$data['savedDetails'] = $savedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_draft_details',array('draft_order_id'=>$_POST['draft_id']),'');

		}
		if(isset($_POST['saved_order_id']) && $_POST['saved_order_id'] != NULL){
			//echo 	"saved_order_id";
			$data['savedDetails'] = $savedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_saved_details',array('saved_order_id'=>$_POST['saved_order_id']),'');
		}


		$product_ids = array();
		if(is_array($savedDetails) && count($savedDetails)>0){
			foreach($savedDetails as $val){
				$product_ids[] = $val->product_id;
			}
		}

		$ProductDetails = $this->SupplierModel->getProductDetailsByCatIdNew($_POST['shop_id'], $_POST['category_ids'], $_POST['show_flag']);
		$total_count = (is_array($ProductDetails)) ? count($ProductDetails) : 0;
		$data = array();
		$no = $_POST['start'];

		$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$_POST['shop_id']),'');
		$B2BCustomer_Details  = $this->B2BImportModel->getB2BCustomerDetails($_POST['shop_id'],$Current_user_shop_id);

		if(!empty($B2BCustomer_Details)){

			$disabled_buy = ($B2BCustomer_Details->allow_buyin==0)?'disabled':'';
			$disabled_virtual = ($B2BCustomer_Details->allow_dropship==0 && $B2BCustomer_Details->allow_buyin==0)?'disabled':'';
			$disabled_dropship = ($B2BCustomer_Details->allow_dropship==0 )?'disabled':'';
			$disabled = ($B2BCustomer_Details->allow_dropship==0 && $B2BCustomer_Details->allow_buyin==0)?'disabled':'';

		}else{

			$disabled_buy = ($FbcUserB2BData->allow_buyin==0)?'disabled':'';
			$disabled_virtual = ($FbcUserB2BData->allow_dropship==0 && $FbcUserB2BData->allow_buyin==0)?'disabled':'';
			$disabled_dropship = ($FbcUserB2BData->allow_dropship==0 )?'disabled':'';
			$disabled = ($FbcUserB2BData->allow_dropship==0 && $FbcUserB2BData->allow_buyin==0)?'disabled':'';

		}

		if(is_array($ProductDetails))
		{
			foreach ($ProductDetails as $readData) {

				$buyin = '';
				$virtual = '';
				$dropship = '';
				$qty = '';
				$qtyVal = 0;

				foreach($savedDetails as $val){
					if($val->product_id == $readData->id && $val->product_inv_type == 'buy'){
						//$buyin = 'buyin_'.$readData->id;
						$buyin = 'buyin_'.$readData->category_id.'_'.$readData->id;
						$qty = 'qty_'.$readData->id;
						$qtyVal = $val->quantity;


					}

					if($val->product_id == $readData->id && $val->product_inv_type == 'virtual'){
						//$virtual = 'virtual_'.$readData->id;
						$virtual = 'virtual_'.$readData->category_id.'_'.$readData->id;
						$qty = 'qty_'.$readData->id;
						$qtyVal = $val->quantity;


					}

					if($val->product_id == $readData->id && $val->product_inv_type == 'dropship'){
						//$dropship = 'dropship_'.$readData->id;
						$dropship = 'dropship_'.$readData->category_id.'_'.$readData->id;

					}
				}

				$chkClass = (in_array($readData->id, $product_ids))?'checked':'';
				//$buyChkClass = ('buyin_'.$readData->id == $buyin)?'checked':'';
				$buyChkClass = ('buyin_'.$readData->category_id.'_'.$readData->id == $buyin)?'checked':'';
				//$virtualChkClass = ('virtual_'.$readData->id == $virtual)?'checked':'';
				$virtualChkClass = ('virtual_'.$readData->category_id.'_'.$readData->id == $virtual)?'checked':'';
				//$dropshipChkClass = ('dropship_'.$readData->id == $dropship)?'checked':'';
				$dropshipChkClass = ('dropship_'.$readData->category_id.'_'.$readData->id == $dropship)?'checked':'';


				$qtyDisabled = (isset($qty) && $qty == '')?'disabled':'';

				$product_id = ($readData->parent_id != 0)?$readData->parent_id:$readData->id;
				$href = BASE_URL.'product/detail/'.$_POST['shop_id'].'/'.$product_id;


				$VariantMaster=$this->SupplierModel->getVariantDetailsForProducts($_POST['shop_id'],$readData->id);

				$variant = '-';
				$OptionValue = array();
				if(isset($VariantMaster) && count($VariantMaster)>0){
					foreach($VariantMaster as $attr){
						$OptionValue[] = array('label' => $attr->attr_name, 'value' => $attr->attr_options_name);
					}

					if(is_array($OptionValue) && count($OptionValue) >0){
						foreach($OptionValue as $val){
						$variant = $val['label'].': '.$val['value'].'<br>';
						}
					}else{
						$variant = '-';
					}

				}

				$launch_date = (isset($readData->launch_date) && $readData->launch_date != 0 ) ? date('d-m-Y' ,$readData->launch_date) : " ";

				/*if($readData->parent_id != 0){
					$get_launch_date = $this->SupplierModel->getLaunchDateById($_POST['shop_id'],$readData->parent_id);
					$launch_date = (isset($get_launch_date['launch_date']) && $get_launch_date['launch_date'] != 0 ) ? date('d-m-Y', $get_launch_date['launch_date']) : " ";
				}else{

					$launch_date = (isset($readData->launch_date) && $readData->launch_date != 0 ) ? date('d-m-Y' ,$readData->launch_date) : " ";

				} */


				$no++;
				$row = array();


				$row[]='<label class="checkbox"><input type="checkbox" class="form-control main-checkbox" value="'.$readData->id.'" id="checkedProduct_'.$readData->category_id.'_'.$readData->id.'" name="checkedProduct[]" onclick="getProductCheckUncheck(this.value,'.$readData->category_id.')" '.$chkClass.'><span class="checked"></span></label>';
				$row[]='<a class="link-purple" href="'.$href.'" target="_blank">View</a>';
				$row[]=$readData->sku;
				$row[]=$readData->name;
				$row[]=$readData->cat_name;
				$row[]=$variant;
				$row[]=($readData->qty > 0)  ? "In Stock" : "Out of Stock";
				$row[]=$launch_date;


				//$row[]='<th>Buy&nbsp;In </th>';
				$row[]='<label class="checkbox"><input type="checkbox" class="form-control buyin-checkbox chk-'.$readData->category_id.'-'.$readData->id.'" id="buyin_'.$readData->category_id.'_'.$readData->id.'" name="buyin_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->id.'" onclick="getCheckboxCheckUncheck(this.value,'.$readData->category_id.')" '.$disabled_buy.' '.$buyChkClass.'><span class="checked"></span></label>';


				//$row[]='<th>Virtual </th>';
				$row[]='<label class="checkbox"><input type="checkbox" class="form-control virtual-checkbox chk-'.$readData->category_id.'-'.$readData->id.'" id="virtual_'.$readData->category_id.'_'.$readData->id.'" name="virtual_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->id.'" onclick="getCheckboxCheckUncheck(this.value,'.$readData->category_id.')" '.$disabled_virtual.' '.$virtualChkClass.'><span class="checked" ></span></label>';


				//$row[]='<th>Dropship </th>';
				$row[]='<label class="checkbox"><input type="checkbox" class="form-control dropship-checkbox chk-'.$readData->category_id.'-'.$readData->id.'" id="dropship_'.$readData->category_id.'_'.$readData->id.'" name="dropship_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->id.'" onclick="getCheckboxCheckUncheck(this.value,'.$readData->category_id.')" '.$disabled_dropship.' '.$dropshipChkClass.'><span class="checked"></span></label>';



				$order_qty = ($qtyVal != 0) ? $qtyVal : "";
				$row[]='<input type="text" class="form-control qty-table-box" id="qty_'.$readData->category_id.'_'.$readData->id.'" name="qty_'.$readData->category_id.'_'.$readData->id.'" onkeypress="return isNumberKey(event);" onblur="checkQty(this.value,'.$readData->id.','.$readData->category_id.')" value="'.$order_qty.'" pattern="[0-9]*" '.$qtyDisabled.'><p id="qtyError_'.$readData->category_id.'_'.$readData->id.'" class="qty-error"></p><input type="hidden" class="pcat" name="category_'.$readData->id.'" value="'.$readData->category_id.'"><input type="hidden" name="price_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->price.'"><input type="hidden" name="parent_'.$readData->category_id.'_'.$readData->id.'" value="'.$readData->parent_id.'">';



				$row[]=$currency_symbol." ".number_format($readData->price,2);


				$data[] = $row;




			}
		}


		if(isset($_REQUEST['order'][0]['column']) && $_REQUEST['order'][0]['column'] == 7 ){

			if($_REQUEST['order'][0]['dir'] == 'asc' ){

				array_multisort(array_map(function($element) {
					return strtotime($element[7]);
				}, $data), SORT_ASC, $data);
			}

			if($_REQUEST['order'][0]['dir'] == 'desc' ){

				array_multisort(array_map(function($element) {
					return strtotime($element[7]);
				}, $data), SORT_DESC, $data);

			}

		}

			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $total_count,
				"recordsFiltered" =>$total_count,
				"data" => $data,
			);

			//output to json format
			echo json_encode($output);
			exit;
	}

	public function fetchAllAppliedProducts(){


		$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$_POST['supplier_shop_id']),'fbc_user_id,currency_symbol,currency_code');
	  	$currency_symbol =(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;


		 if($_POST['order_type'] == 'requested-applied-orders'){

			$shopDataBuyer = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$_POST['buyer_shop_id']),'fbc_user_id,currency_symbol,currency_code');

			$args['shop_id']	=	$_POST['buyer_shop_id'];
			$args['fbc_user_id']	=	$shopDataBuyer->fbc_user_id;

			$this->load->model('ShopProductModel');
			$this->ShopProductModel->init($args);

			$appliedData=$this->ShopProductModel->getSingleDataByID('b2b_orders_applied',array('id'=>$_POST['applied_order_id']),'');
			$appliedDetails=$this->ShopProductModel->getMultiDataById('b2b_orders_applied_details',array('applied_order_id'=>$_POST['applied_order_id']),'');

		 }else{

			$appliedData=$this->SupplierModel->getSingleDataByID('b2b_orders_applied',array('id'=>$_POST['applied_order_id']),'');
			$appliedDetails=$this->SupplierModel->getMultiDataById('b2b_orders_applied_details',array('applied_order_id'=>$_POST['applied_order_id']),'');

		 }


		$product_ids = array();
		if(is_array($appliedDetails) && count($appliedDetails)>0){
			foreach($appliedDetails as $val){
				$product_ids[] = $val->product_id;
			}
		}


		$ProductDetails = $this->SupplierModel->getProductListIncludingAppliedProducts($_POST['supplier_shop_id'], $_POST['category_ids']);
		$total_count = (is_array($ProductDetails)) ? count($ProductDetails) : 0;
		$data = array();
		$no = $_POST['start'];

		//$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$_POST['shop_id']),'');


		if(is_array($ProductDetails))
		{
			foreach ($ProductDetails as $readData) {



				$buyin = '';
				$virtual = '';
				$dropship = '';
				$qty = '';
				$qtyVal = 0;

				foreach($appliedDetails as $val){
					if($val->product_id == $readData->id && $val->product_inv_type == 'buy'){
						$buyin = 'buyin_'.$readData->id;
						$qty = 'qty_'.$readData->id;
						$qtyVal = $val->quantity;
					}

					if($val->product_id == $readData->id && $val->product_inv_type == 'virtual'){
						$virtual = 'virtual_'.$readData->id;
						$qty = 'qty_'.$readData->id;
						$qtyVal = $val->quantity;
					}

					if($val->product_id == $readData->id && $val->product_inv_type == 'dropship'){
						$dropship = 'dropship_'.$readData->id;
					}
				}

				$chkClass = (in_array($readData->id, $product_ids))?'checked':'';
				$buyChkClass = ('buyin_'.$readData->id == $buyin)?'checked':'';

				$virtualChkClass = ('virtual_'.$readData->id == $virtual)?'checked':'';

				$dropshipChkClass = ('dropship_'.$readData->id == $dropship)?'checked':'';

				$qtyDisabled = (isset($qty) && $qty == '')?'disabled':'';

				$product_id = ($readData->parent_id != 0)?$readData->parent_id:$readData->id;
				$href = BASE_URL.'product/detail/'.$_POST['supplier_shop_id'].'/'.$product_id;


				$VariantMaster=$this->SupplierModel->getVariantDetailsForProducts($_POST['supplier_shop_id'],$readData->id);

				$variant = '-';
				$OptionValue = array();
				if(isset($VariantMaster) && count($VariantMaster)>0){
					foreach($VariantMaster as $attr){
						$OptionValue[] = array('label' => $attr->attr_name, 'value' => $attr->attr_options_name);
					}

					if(is_array($OptionValue) && count($OptionValue) >0){
						foreach($OptionValue as $val){
						$variant = $val['label'].': '.$val['value'].'<br>';
						}
					}else{
						$variant = '-';
					}

				}

				$launch_date = (isset($readData->launch_date) && $readData->launch_date != 0 ) ? date('d-m-Y' ,$readData->launch_date) : " ";

				/*if($readData->parent_id != 0){
					$get_launch_date = $this->SupplierModel->getLaunchDateById($_POST['supplier_shop_id'],$readData->parent_id);
					$launch_date = (isset($get_launch_date['launch_date']) && $get_launch_date['launch_date'] != 0 ) ? date('d-m-Y', $get_launch_date['launch_date']) : " ";
				}else{

					$launch_date = (isset($readData->launch_date) && $readData->launch_date != 0 ) ? date('d-m-Y' ,$readData->launch_date) : " ";

				} */


				if(in_array($readData->id, $product_ids)){

					$no++;
					$row = array();


					$row[]='<label class="checkbox"><input type="checkbox" class="form-control main-checkbox" value="'.$readData->id.'" id="checkedProduct_'.$readData->category_id.'_'.$readData->id.'" name="checkedProduct[]" onclick="getProductCheckUncheck(this.value,'.$readData->category_id.')" '.$chkClass.'><span class="checked"></span></label>';
					$row[]='<a class="link-purple" href="'.$href.'" target="_blank">View</a>';
					$row[]=$readData->sku;
					$row[]=$readData->name;
					$row[]=$readData->cat_name;
					$row[]=$variant;
					$row[]=($readData->qty > 0)  ? "In Stock" : "Out of Stock";
					$row[]=$launch_date;

					$row[]='<label class="checkbox"><input type="checkbox" class="form-control buyin-checkbox chk-'.$readData->id.'" id="buyin_'.$readData->id.'" name="buyin_'.$readData->id.'" value="'.$readData->id.'" '.$buyChkClass.'><span class="checked"></span></label>';

					$row[]='<label class="checkbox"><input type="checkbox" class="form-control virtual-checkbox chk-'.$readData->id.'" id="virtual_'.$readData->id.'" name="virtual_'.$readData->id.'" value="'.$readData->id.'" '.$virtualChkClass.'><span class="checked" ></span></label>';

					$row[]='<label class="checkbox"><input type="checkbox" class="form-control dropship-checkbox chk-'.$readData->id.'" id="dropship_<'.$readData->id.'" name="dropship_'.$readData->id.'" value="'.$readData->id.'" '.$dropshipChkClass.'><span class="checked"></span></label>';

					$row[]='<input type="text" class="form-control qty-table-box" id="qty_'.$readData->id.'" name="qty_'.$readData->id.'" onkeypress="return isNumberKey(event);" onblur="checkQty(this.value,'.$readData->id.')" value="'.($qtyVal != 0) ? $qtyVal : "".'" pattern="[0-9]*" '.$qtyDisabled.'><p id="qtyError_'.$readData->id.'" class="qty-error"></p>';

					$row[]=$currency_symbol." ".number_format($readData->price,2);


					$data[] = $row;

				}


			}

		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => count($appliedDetails),
			"recordsFiltered" =>count($appliedDetails),
			"data" => $data,
		);

		echo json_encode($output);
		exit;
	}


}
