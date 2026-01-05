<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NotificationController extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('CommonModel');
		$this->load->model('UserModel');
		$this->load->model('CategoryModel');
		$this->load->model('EavAttributesModel');
		$this->load->model('B2BOrdersModel');
		$this->load->model('SupplierModel');
		$this->load->model('SellerProductModel');
		$this->load->library('S3_filesystem');

		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
	}

	public function getAllNotifications(){
		$LoginID	=	$this->session->userdata('LoginID');
		$ShopID		=	$this->session->userdata('ShopID');
        $data['PageTitle'] = 'Notifications';;
		$data['notificationData'] = $notificationData=$this->CommonModel->getUserNotifications($ShopID,$LoginID);
        $this->load->view('common/fbc-user/all_notifications', $data);
    }

	public function updateCount(){
		$LoginID	=	$this->session->userdata('LoginID');
		$ShopID		=	$this->session->userdata('ShopID');

		$updateData=array(
			'visited_flag'=>1,
			'updated_at'=>time(),
			//'ip'=>$_SERVER['REMOTE_ADDR']
		);

		$where_arr=array('to_shop_id'=>$ShopID,'to_fbc_user_id'=>$LoginID);
		$isRowAffected = $this->CommonModel->updateNotificationData('notifications',$where_arr,$updateData);
	}

	public function updateNotificationUnreadlFlag(){
		if(isset($_POST)){

			$LoginID	=	$this->session->userdata('LoginID');
			$ShopID		=	$this->session->userdata('ShopID');
			$n_id = $_POST['id'];

			$updateData=array(
				'read_flag'=>1,
				'updated_at'=>time(),
				//'ip'=>$_SERVER['REMOTE_ADDR']
			);

			$where_arr=array('id'=>$n_id);
			$isRowAffected = $this->CommonModel->updateNotificationData('notifications',$where_arr,$updateData);

			echo json_encode(array("flag"=> 1,"msg" => "Updated Successfully"));
			exit;
		}else{
			echo json_encode(array("flag"=> 0,"msg" => "Id can not be blank"));
			exit;
		}
	}

	public function getRequestedAppliedOrderDetail(){
		$LoginID	=	$this->session->userdata('LoginID');
		$ShopID		=	$this->session->userdata('ShopID');

		$order_type=$this->uri->segment(2);
		$shop_id=$this->uri->segment(4); //buyer shopid
		$applied_order_id = '';
		if($order_type == 'requested-applied-orders'){
			$applied_order_id=$this->uri->segment(5);
			if(empty($shop_id) || empty($applied_order_id)){
				redirect('dashboard');
			}
		}

		$data['PageTitle']='Applied Orders Detail';
		//$data['side_menu']='applied';
		$data['shop_id']=$shop_id;

		$data['buyer_shop_id']=$shop_id;
		$data['supplier_shop_id']=$ShopID;

		$data['flag']='requested';
		$shopData = $supplierData = $this->UserModel->getShopDetailsByShopId($shop_id);
		$data['webshop_name']= $shopData->org_shop_name;
		$data['shopData'] = $shopData ;
		$data['currency_symbol']=(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;
		$data['buyer_currency_code']=$shopData->currency_code;

		$data['b2bCustomerDetails'] = $this->SellerProductModel->getSingleDataByID('b2b_customers',array('shop_id'=>$shop_id),'');

		$sellerData = $this->UserModel->getShopDetailsByShopId($ShopID);

		$data['seller_currency_symbol']=(isset($sellerData->currency_symbol))?$sellerData->currency_symbol:$sellerData->currency_code;

		$data['seller_currency_code']=$sellerData->currency_code;


		$userData = $supplierData = $this->UserModel->getUserByUserId($shopData->fbc_user_id);
		$data['owner_name']= $userData->owner_name;
		$data['mobile_no'] = $userData->mobile_no;
		$data['email'] = $userData->email;

		$args['shop_id']	=	$shop_id;
		$args['fbc_user_id']	=	$shopData->fbc_user_id;

		$this->load->model('ShopProductModel');
		$this->ShopProductModel->init($args);

		$data['notificationData']=$notificationData=$this->SellerProductModel->getSingleDataByID('notifications',array('to_shop_id'=>$ShopID,'to_fbc_user_id'=>$LoginID,'shop_id'=>$shop_id,'area_id'=>$applied_order_id,'notification_type'=>1),'');
		$data['appliedData']=$appliedData=$this->ShopProductModel->getSingleDataByID('b2b_orders_applied',array('id'=>$applied_order_id),'');
		$data['appliedDetails'] = $appliedDetails=$this->ShopProductModel->getMultiDataById('b2b_orders_applied_details',array('applied_order_id'=>$applied_order_id),'');

		//$data['appliedCustomerTypeDetails'] = $this->SupplierModel->getMultiDataById('b2b_orders_applied_custypedetails',array('applied_order_id'=>$applied_order_id),'');
		#Neha Added
		//$CurrentuserData = $this->UserModel->getUserByUserId($shopData->fbc_user_id);
		//$Current_user_shop_id = $CurrentuserData->shop_id;
		//$Current_user_shopData = $this->UserModel->getShopDetailsByShopId($Current_user_shop_id);
		//$data['current_user_webshop_name']= '';

		if(isset($appliedData) && $appliedData->id != ''){
			$category_ids = trim($appliedData->total_categories_ids,',');
			/*$category_ids = explode(',',$appliedData->total_categories_ids);
			$category_idsArr = array_values(array_filter($category_ids));
			if(is_array($category_idsArr) && count($category_idsArr)>0){
				foreach($category_idsArr as $key=>$value){
					//$productData['product_'.$key] = $this->SupplierModel->getProductDetailsByCatId($shop_id, $value);
					$data['productData'][] = $this->SupplierModel->getProductDetailsByCatId($ShopID, $value);
				}
			}*/

			$data['category_ids'] = $category_ids;
			//echo"<pre>";print_r($data);exit;
		}
		$this->load->view('seller/suppliersNew/applied_order_detail',$data);
	}

	public function updateOrderStatus(){
		if(isset($_POST)){
			//print_r($_POST);exit;

			$LoginID	=	$this->session->userdata('LoginID');
			$ShopID		=	$this->session->userdata('ShopID');
			$n_id = $_POST['id'];
			$status = $_POST['status'];


			if($_POST['tax_status'] == "" && $status == 1){
				echo json_encode(array("flag"=> 0,"msg" => "Tax Exampted not selected."));
				exit;
			}


			$updateData=array(
				'status'=>$status,
				'updated_at'=>time(),
				//'ip'=>$_SERVER['REMOTE_ADDR']
			);

			$where_arr=array('id'=>$n_id);
			$isRowAffected = $this->CommonModel->updateNotificationData('notifications',$where_arr,$updateData);

			/*-----------Order Notification-----------*/

				$notificationData=$this->CommonModel->getUserNotificationsById($ShopID,$LoginID,$n_id);
				$to_shop_id = $notificationData->from_shop_id;
				$to_fbc_user_id = $notificationData->from_fbc_user_id;
				$applied_id = $notificationData->area_id;

				$shopData = $supplierData = $this->UserModel->getShopDetailsByShopId($ShopID);
				$webshop_name= $shopData->org_shop_name;

				$seller_shop_currency_code=$shopData->currency_code;


				$BuyerShopData= $this->UserModel->getShopDetailsByShopId($to_shop_id);
				$buyer_shop_currency_code=$BuyerShopData->currency_code;


				$from_shop_id = $ShopID;
				$from_fbc_user_id= $LoginID;
				if($status == 1){
					$notification_text = "Order confirmed from ".$webshop_name;
					$type=2; //1-b2b_order_confirmed
				}else{
					$notification_text = "Order rejected from ".$webshop_name;
					$type=3; //3-b2b_order_rejected
				}

				$args['shop_id']	=	$to_shop_id;
				$args['fbc_user_id']	=	$to_fbc_user_id;

				$total_tax_amount = 0;



				$this->load->model('ShopProductModel');
				$this->ShopProductModel->init($args);

				if($status == 1){

				$delivery_identifier = 'product_delivery_duration';
				$product_delivery_duration = $this->ShopProductModel->getSingleDataByID('custom_variables',array('identifier'=>$delivery_identifier),'');
				$product_delivery_duration=(isset($product_delivery_duration) && $product_delivery_duration->value!='')?$product_delivery_duration->value:'';

				$shop_source_bucket = get_s3_bucket($ShopID);
				$shop_upload_bucket = get_s3_bucket($to_shop_id);

				$b2b_customers = $this->SellerProductModel->getSingleDataByID('b2b_customers',array('shop_id'=>$to_shop_id),'');
				$FbcUserB2BData = $this->SellerProductModel->getSingleDataByID('b2b_customers_details',array('customer_id'=>$b2b_customers->id),'');
				//$FbcUserB2BData=$this->CommonModel->getSingleDataByID('fbc_users_b2b_details',array('shop_id'=>$ShopID),'');

				$shop_buyin_discount_percent=($FbcUserB2BData->buyin_discount>0)?$FbcUserB2BData->buyin_discount:0;
				$shop_buyin_del_time_in_days=$FbcUserB2BData->buyin_del_time;
				$shop_dropship_discount_percent=($FbcUserB2BData->dropship_discount>0)?$FbcUserB2BData->dropship_discount:0;
				$shop_dropship_del_time_in_days=$FbcUserB2BData->dropship_del_time;
				$shop_display_catalog_overseas=$FbcUserB2BData->display_catalog_overseas;
				$shop_perm_to_change_price=$FbcUserB2BData->perm_to_change_price;
				$shop_can_increase_price=$FbcUserB2BData->can_increase_price;
				$shop_can_decrease_price=$FbcUserB2BData->can_decrease_price;

				$appliedData=$this->ShopProductModel->getSingleDataByID('b2b_orders_applied',array('id'=>$applied_id),'');
				$appliedDetails=$this->ShopProductModel->getMultiDataById('b2b_orders_applied_details',array('applied_order_id'=>$applied_id),'');


				$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag($to_shop_id);

				if(is_array($appliedDetails) && count($appliedDetails)>0){
					foreach($appliedDetails as $key=>$value){


						$qty = $value->quantity;

						//$category_id=$value->category_id;  //from seller shop

						if($value->parent_id == 0){
							$product_id = $value->product_id;
							$product_type = 'simple';
							$product_inv_type = $value->product_inv_type;
						}else{
							$product_id = $value->parent_id;
							$product_type = 'configurable';
							$product_inv_type = $value->product_inv_type;
						}

						if($product_inv_type=='dropship'){
							$shop_buyin_discount_percent=0;
							$shop_buyin_del_time_in_days='';
							$shop_dropship_discount_percent=($FbcUserB2BData->dropship_discount>0)?$FbcUserB2BData->dropship_discount:0;
							$shop_dropship_del_time_in_days=$FbcUserB2BData->dropship_del_time;
						}else if($product_inv_type=='buy'){
							$shop_buyin_discount_percent=($FbcUserB2BData->buyin_discount>0)?$FbcUserB2BData->buyin_discount:0;
							$shop_buyin_del_time_in_days=$FbcUserB2BData->buyin_del_time;
							$shop_dropship_discount_percent=0;
							$shop_dropship_del_time_in_days='';
						}else{
							$shop_buyin_discount_percent=0;
							$shop_buyin_del_time_in_days='';
							$shop_dropship_discount_percent=0;
							$shop_dropship_del_time_in_days='';
						}

						$catData=$this->SellerProductModel->getMultiDataById('products_category',array('product_id'=>$product_id),'');

						// echo "<pre>";
						// print_r($catData);exit;

						$cat_id_arr=array();
						$sub_cat_id_arr=array();
						$child_cat_id_arr=array();

						if(is_array($catData) && count($catData) > 0){
							foreach($catData as $cat){
								$category_id =  $cat->category_ids;

								$CatInfo=$this->CommonModel->getSingleDataByID('category',array('id'=>$category_id),'');

								$cat_name = $CatInfo->cat_name;
								$slug = $CatInfo->slug;
								$cat_description = $CatInfo->cat_description;

								if($CatInfo->created_by_type==0){
									if($CatInfo->cat_level==0){
										$cat_id_arr[]=$category_id;
										$cat_id=$category_id;
										$sub_cat_id='';
										$child_cat_id='';

									}else if($CatInfo->cat_level==1){
										$sub_cat_id_arr[]=$category_id;
										$cat_id=$CatInfo->parent_id;
										$sub_cat_id=$category_id;
										$child_cat_id='';

									}else if($CatInfo->cat_level==2){
										$child_cat_id_arr[]=$category_id;
										$cat_id=$CatInfo->main_parent_id;
										$sub_cat_id=$CatInfo->parent_id;
										$child_cat_id=$category_id;
									}

								}else{
									if($CatInfo->cat_level==0){

										$mainCatExist=$this->CommonModel->getSingleDataByID('category',array('cat_name'=>$cat_name,'cat_level'=>0, 'shop_id'=> $to_shop_id),'');
										if(empty($mainCatExist)){
											$insertArr=array('cat_name'=>$cat_name,'slug'=>$slug,'cat_level'=>0,'cat_description'=>$cat_description,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
											$cat_id = $this->CategoryModel->insertData('category',$insertArr);
											$cat_id_arr[]=$cat_id;
										}else{
											$cat_id=$mainCatExist->id;
											$cat_id_arr[]=$cat_id;
										}

									}else if($CatInfo->cat_level==1){

										$subCatExist=$this->CommonModel->getSingleDataByID('category',array('cat_name'=>$cat_name,'cat_level'=>1, 'shop_id'=> $to_shop_id),'');

										if(empty($subCatExist)){
											$insertArr=array('cat_name'=>$cat_name,'slug'=>$slug,'cat_level'=>1,'parent_id'=>$cat_id,'cat_description'=>'','shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
											$sub_cat_id=$this->CategoryModel->insertData('category',$insertArr);
											$sub_cat_id_arr[]=$sub_cat_id;
										}else{
											$sub_cat_id=$subCatExist->id;
											$sub_cat_id_arr[]=$sub_cat_id;
										}

									}else if($CatInfo->cat_level==2){

										$IsChildExist=$this->CategoryModel->check_child_category_exist($to_shop_id,$sub_cat_id,2,$cat_name);

										if(isset($IsChildExist) && $IsChildExist['id']!=''){
											$cid=$IsChildExist['id'];
											$child_cat_id_arr[]=$cid;
										}else{
											$cc_slug=url_title($cat_name);
											$url_key = strtolower($cc_slug);

											$slugcount = $this->CategoryModel->check_category_exist_by_slug($to_shop_id,2,$cat_name);
											if ($slugcount > 0) {
												$slugcount=$slugcount+1;
												$url_key = $url_key."-".$slugcount;
											}else{
												$url_key = $url_key;
											}
											$child_cat_insert=array('cat_name'=>$cat_name,'slug'=>$url_key,'parent_id'=>$sub_cat_id,'main_parent_id'=>$cat_id,'cat_level'=>2,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'shop_id'=>$to_shop_id,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
											$this->db->insert('category',$child_cat_insert);
											$cid=$this->db->insert_id();
											$child_cat_id_arr[]=$cid;
										}
									}
								}
							}
						}

						// echo "<pre>";
						// print_r($cat_id_arr);
						// print_r($sub_cat_id_arr);
						// print_r($child_cat_id_arr);
						// exit;

						$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$ShopID),'fbc_user_id,currency_symbol,currency_code');
						$shop_currency=(isset($shopData->currency_symbol))?$shopData->currency_symbol:$shopData->currency_code;

						if($value->parent_id == 0){
							$productDetail=$this->SellerProductModel->getSingleDataByID('products',array('id'=>$value->product_id),'');
						}else{
							$productDetail=$this->SellerProductModel->getSingleDataByID('products',array('id'=>$value->parent_id),'');
						}

						//print_r($productDetail);
						//exit;



						$product_customer_type = $productDetail->customer_type_ids;
						$seller_mapped_custType = $this->SupplierModel->getAppliedCustomeTypeMapping($to_shop_id,$applied_id,$product_customer_type);
						$customer_type_ids = implode(',', array_unique(explode(',', $seller_mapped_custType->customer_type_ids)));

						if(isset($productDetail)){
							$product_code = $productDetail->product_code;
							$product_name = $productDetail->name;

							$product_slug = url_title($product_name);
							$url_key = strtolower($product_slug);
							//$url_key=$this->SellerProductModel->createproductslug($url_key);

							$PD_Exist=$this->ShopProductModel->getSingleDataByID('products',array('shop_product_id'=>$product_id, 'imported_from'=>$ShopID,'remove_flag'=>0),'id,name');
							if(isset($PD_Exist) && $PD_Exist->id!=''){


									$slugcount = $this->ShopProductModel->productslugcount($product_name,$PD_Exist->id);
									if ($slugcount > 0) {
										$slugcount=$slugcount+1;
										$url_key = $url_key."-".$slugcount;
									}else{
										$url_key = $url_key."-0";
									}

								/*****---------calculate cost price-----------------------****/
								if((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount>0) && $productDetail->price>0){
									$RowTotalData=$this->CommonModel->calculate_percent_data($productDetail->price,$FbcUserB2BData->buyin_discount);

									if($seller_shop_currency_code!=$buyer_shop_currency_code){
										/*$percent_amount=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$RowTotalData['percent_amount']);
										$converted_price=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price);*/
										$percent_amount=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($RowTotalData['percent_amount']*$appliedData->price_converted):$RowTotalData['percent_amount'];
										$converted_price=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price;

										$cost_price=$converted_price - $percent_amount;
									}else{
										$percent_amount=$RowTotalData['percent_amount'];
										$cost_price=$productDetail->price - $percent_amount;
									}
								}else{
									$percent_amount=0;
									//$cost_price=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price);
									$cost_price=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price;

								}

								if(isset($appliedData->price_converted) && $appliedData->price_converted > 0){
									$webshop_price = $productDetail->webshop_price*$appliedData->price_converted;
								}else{
									$webshop_price = $productDetail->webshop_price;
								}


								if($Rounded_price_flag == 1){
									$webshop_price_final =  round($webshop_price);
								}else{
									$webshop_price_final =  $webshop_price;
								}


								$estimate_delivery_time=$product_delivery_duration;


								/***--------Update product start-------------------------------------------------***/

								$updatedata=array(
										'name'=>$productDetail->name,
										'product_code'=>$product_code,
										'url_key'=>$url_key,
										'meta_title'=>$productDetail->meta_title,
										'meta_keyword'=>$productDetail->meta_keyword,
										'meta_description'=>$productDetail->meta_description,
										'search_keywords' => $productDetail->search_keywords,
										'promo_reference' => $productDetail->promo_reference,
										'weight'=>$productDetail->weight,
										'sku'=>$productDetail->sku,
										//'price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price),
										'price'=> (isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price,
										'barcode'=>$productDetail->barcode,
										'gender'=>$productDetail->gender,
										'base_image'=>$productDetail->base_image,
										'description'=>$productDetail->description,
										'highlights'=>$productDetail->highlights,
										'product_reviews_code'=>$productDetail->product_reviews_code,
										'launch_date'=>$productDetail->launch_date,
										'estimate_delivery_time'=>$estimate_delivery_time,
										'product_return_time'=>$productDetail->product_return_time,
										'product_drop_shipment'=>$productDetail->product_drop_shipment,
										'product_type'=>$product_type,
										'product_inv_type'=>$product_inv_type,
										'status'=>1,
										// 'shop_id'=>$to_shop_id,
										'shop_id'=>$ShopID,
										'shop_product_id'=>$product_id,
										'shop_price'=>$productDetail->price,
										'shop_cost_price'=>'',
										'shop_currency'=>$shop_currency,
										'fbc_user_id'=>$to_fbc_user_id,
										//'product_type'=>$product_type,
										'cost_price'=>$cost_price,   				// cost_price = selling price - buyin_discount
										'tax_percent'=>$productDetail->tax_percent,
										//'tax_amount'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->tax_amount),
										//'webshop_price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->webshop_price),
										'tax_amount'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->tax_amount*$appliedData->price_converted):$productDetail->tax_amount,
										'webshop_price'=>$webshop_price_final,
										'shop_buyin_discount_percent'=>$shop_buyin_discount_percent,
										'shop_buyin_del_time_in_days'=>$shop_buyin_del_time_in_days,
										'shop_dropship_discount_percent'=>$shop_dropship_discount_percent,
										'shop_dropship_del_time_in_days'=>$shop_dropship_del_time_in_days,
										'shop_display_catalog_overseas'=>$shop_display_catalog_overseas,
										'shop_perm_to_change_price'=>$shop_perm_to_change_price,
										'shop_can_increase_price'=>$shop_can_increase_price,
										'shop_can_decrease_price'=>$shop_can_decrease_price,
										'customer_type_ids' =>$customer_type_ids,
										'updated_at'=>time(),
										'imported_from'=>$ShopID,
										'ip'=>$_SERVER['REMOTE_ADDR']
								);

								$where_arr=array('id'=>$PD_Exist->id);
								$this->ShopProductModel->updateData('products',$where_arr,$updatedata);

								if($product_type=='simple'){
									$simpleInv=$this->ShopProductModel->getSingleDataByID('products_inventory',array('product_id'=>$PD_Exist->id),'id,qty,available_qty');
									$stock_qty = $simpleInv->qty + $qty;
									$new_available_qty = $simpleInv->available_qty + $qty;
									$stock_update=array('qty'=>$stock_qty,'available_qty'=>$new_available_qty);

									$whr_qty_arr=array('product_id'=>$PD_Exist->id);
									//$this->ShopProductModel->updateData('products_inventory',$whr_qty_arr,$stock_update);


								}else if($productDetail->product_type == 'configurable'){


									$slugcount = $this->ShopProductModel->productslugcount($product_name);
									if ($slugcount > 0) {
										$slugcount=$slugcount+1;
										$url_key = $url_key."-".$slugcount;
									}else{
										$url_key = $url_key."-0";
									}


									$productDetail=$this->SellerProductModel->getSingleDataByID('products',array('id'=>$value->product_id),'');
									$SP_Exist=$this->ShopProductModel->getSingleDataByID('products',array('shop_product_id'=>$value->product_id, 'imported_from'=>$ShopID,'remove_flag'=>0),'id,name');
									//print_r($SP_Exist);exit;
									if(isset($SP_Exist) && $SP_Exist->id!=''){

										if(isset($appliedData->price_converted) && $appliedData->price_converted > 0){
											$webshop_price = $productDetail->webshop_price*$appliedData->price_converted;
										}else{
											$webshop_price = $productDetail->webshop_price;
										}


										if($Rounded_price_flag == 1){
											$webshop_price_final =  round($webshop_price);
										}else{
											$webshop_price_final =  $webshop_price;
										}


										$simpleProductId = $SP_Exist->id;
										$updatedata=array(
											'name'=>$productDetail->name,
											'meta_title'=>$productDetail->meta_title,
											'meta_keyword'=>$productDetail->meta_keyword,
											'meta_description'=>$productDetail->meta_description,
											'search_keywords' => $productDetail->search_keywords,
											'promo_reference' => $productDetail->promo_reference,
											'weight'=>$productDetail->weight,
											'parent_id'=>$PD_Exist->id,
											'sku'=>$productDetail->sku,
											//'price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price),
											//'cost_price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->cost_price),
											'price'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price,
											'cost_price'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->cost_price*$appliedData->price_converted):$productDetail->cost_price,
											'barcode'=>$productDetail->barcode,
											'product_type'=>'conf-simple',
											'product_inv_type'=>$product_inv_type,
											'status'=>1,
											// 'shop_id'=>$to_shop_id,
											'launch_date'=>$productDetail->launch_date,
											'shop_id'=>$ShopID,
											'shop_product_id'=>$value->product_id,
											'shop_price'=>$productDetail->price,
											'shop_cost_price'=>'',
											'shop_currency'=>$shop_currency,
											'fbc_user_id'=>$to_fbc_user_id,
											'tax_percent'=>$productDetail->tax_percent,
											//'tax_amount'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->tax_amount),
											//'webshop_price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->webshop_price),
											'tax_amount'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->tax_amount*$appliedData->price_converted):$productDetail->tax_amount,
											'webshop_price'=>$webshop_price_final,
											'shop_buyin_discount_percent'=>$shop_buyin_discount_percent,
											'shop_buyin_del_time_in_days'=>$shop_buyin_del_time_in_days,
											'shop_dropship_discount_percent'=>$shop_dropship_discount_percent,
											'shop_dropship_del_time_in_days'=>$shop_dropship_del_time_in_days,
											'shop_display_catalog_overseas'=>$shop_display_catalog_overseas,
											'shop_perm_to_change_price'=>$shop_perm_to_change_price,
											'shop_can_increase_price'=>$shop_can_increase_price,
											'shop_can_decrease_price'=>$shop_can_decrease_price,
											'updated_at'=>time(),
											'imported_from'=>$ShopID,
											'ip'=>$_SERVER['REMOTE_ADDR']
											);
										$where =array('id'=>$simpleProductId);
										$this->ShopProductModel->updateData('products',$where,$updatedata);
									}else{
										/*****---------calculate cost price-----------------------****/
										if((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount>0) && $productDetail->price>0){
											$RowTotalData=$this->CommonModel->calculate_percent_data($productDetail->price,$FbcUserB2BData->buyin_discount);
											if($seller_shop_currency_code!=$buyer_shop_currency_code){
												/*$percent_amount=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$RowTotalData['percent_amount']);
												$converted_price=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price);*/

											    $percent_amount=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($RowTotalData['percent_amount']*$appliedData->price_converted):$RowTotalData['percent_amount'];
												$converted_price=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price;

												$cost_price=$converted_price - $percent_amount;
											}else{
												$percent_amount=$RowTotalData['percent_amount'];
												$cost_price=$productDetail->price - $percent_amount;
											}
										}else{
											$percent_amount=0;
											//$cost_price=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price);
											$cost_price=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price;

										}

										if(isset($appliedData->price_converted) && $appliedData->price_converted > 0){
											$webshop_price = $productDetail->webshop_price*$appliedData->price_converted;
										}else{
											$webshop_price = $productDetail->webshop_price;
										}


										if($Rounded_price_flag == 1){
											$webshop_price_final =  round($webshop_price);
										}else{
											$webshop_price_final =  $webshop_price;
										}

										$insertdata=array(
											'name'=>$productDetail->name,
											'meta_title'=>$productDetail->meta_title,
											'meta_keyword'=>$productDetail->meta_keyword,
											'meta_description'=>$productDetail->meta_description,
											'search_keywords' => $productDetail->search_keywords,
											'promo_reference' => $productDetail->promo_reference,
											'weight'=>$productDetail->weight,
											'parent_id'=>$PD_Exist->id,
											'sku'=>$productDetail->sku,
											//'price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price),
											'price'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price,
											'cost_price'=>$cost_price,
											'barcode'=>$productDetail->barcode,
											'product_type'=>'conf-simple',
											'product_inv_type'=>$product_inv_type,
											'status'=>1,
											// 'shop_id'=>$to_shop_id,
											'launch_date'=>$productDetail->launch_date,
											'shop_id'=>$ShopID,
											'shop_product_id'=>$value->product_id,
											'shop_price'=>$productDetail->price,
											'shop_cost_price'=>'',
											'shop_currency'=>$shop_currency,
											'tax_percent'=>$productDetail->tax_percent,
											//'tax_amount'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->tax_amount),
											//'webshop_price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->webshop_price),
											'tax_amount'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->tax_amount*$appliedData->price_converted):$productDetail->tax_amount,
											'webshop_price'=>$webshop_price_final,
											'shop_buyin_discount_percent'=>$shop_buyin_discount_percent,
											'shop_buyin_del_time_in_days'=>$shop_buyin_del_time_in_days,
											'shop_dropship_discount_percent'=>$shop_dropship_discount_percent,
											'shop_dropship_del_time_in_days'=>$shop_dropship_del_time_in_days,
											'shop_display_catalog_overseas'=>$shop_display_catalog_overseas,
											'shop_perm_to_change_price'=>$shop_perm_to_change_price,
											'shop_can_increase_price'=>$shop_can_increase_price,
											'shop_can_decrease_price'=>$shop_can_decrease_price,

											'fbc_user_id'=>$to_fbc_user_id,
											'created_at'=>time(),
											'imported_from'=>$ShopID,
											'ip'=>$_SERVER['REMOTE_ADDR']
											);
										$simpleProductId=$this->ShopProductModel->insertData('products',$insertdata);
									}
									if($simpleProductId){
										$SP_Inv_Exist=$this->ShopProductModel->getSingleDataByID('products_inventory',array('product_id'=>$simpleProductId),'id,qty,available_qty');
										if(isset($SP_Inv_Exist) && $SP_Inv_Exist->id != 0){
											$stock_qty = $SP_Inv_Exist->qty + $qty;
											$new_available_qty = $SP_Inv_Exist->available_qty + $qty;
											$stock_update=array('qty'=>$stock_qty,'available_qty'=>$new_available_qty);
											$whr_qty_arr=array('product_id'=>$simpleProductId);
										//	$this->ShopProductModel->updateData('products_inventory',$whr_qty_arr,$stock_update);
										}else{
											//$stock_insert=array('product_id'=>$simpleProductId,'qty'=>$qty,'available_qty'=>$qty,'min_qty'=>0,'is_in_stock'=>1);
											$stock_insert=array('product_id'=>$simpleProductId,'qty'=>0,'available_qty'=>0,'min_qty'=>0,'is_in_stock'=>1);
											$this->ShopProductModel->insertData('products_inventory',$stock_insert);
										}

										$variantMaster=$this->SellerProductModel->getMultiDataByID('products_variants_master',array('product_id'=>$product_id),'');
										if(is_array($variantMaster) && count($variantMaster)>0){
											foreach($variantMaster as $variant){
												$attr_id = $variant->attr_id;
												$seller_attr_id = $variant->attr_id;
												//$attrDetails=$this->EavAttributesModel->get_attribute_detail($variant->attr_id);
												$attrDetails=$this->EavAttributesModel->get_attribute_detail_ownshop_admin($variant->attr_id,$ShopID);
												if(empty($attrDetails)){
													continue;
												}

												$attr_code = $attrDetails->attr_code;
												$attr_name = $attrDetails->attr_name;
												$attr_description = $attrDetails->attr_description;
												$attr_properties = $attrDetails->attr_properties;
												if($attrDetails->created_by_type != 0){
													$attrExist=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code, 'attr_type'=>2,'shop_id'=> $to_shop_id),'');
													if(empty($attrExist)){
														$insertArr=array('attr_code'=>$attr_code,'attr_name'=>$attr_name,'attr_description'=>$attr_description,'attr_properties'=>$attr_properties,'attr_type'=>2,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
														$attr_id=$this->CategoryModel->insertData('eav_attributes',$insertArr);
													}else{
														$attr_id=$attrExist->id;

														/*$updateArr=array('attr_code'=>$attr_code,'attr_name'=>$attr_name,'attr_description'=>$attr_description,'attr_properties'=>$attr_properties,'attr_type'=>2,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'updated_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
														$whr_arr=array('id'=>$attr_id);
														$this->db->where($whr_arr);
														$this->db->update('eav_attributes', $updateArr);*/

														//$this->CategoryModel->updateData('eav_attributes',$whr_arr,$updateArr);
													}
												}
												$variantAttrExist=$this->ShopProductModel->getSingleDataByID('products_variants_master',array('product_id'=>$PD_Exist->id,'attr_id'=>$attr_id),'id');
												// print_r($variantAttrExist);exit;
												if(isset($variantAttrExist) && $variantAttrExist->id != 0){
													$variantMasterId=$variantAttrExist->id;
													$varaint_master_update=array('product_id'=>$PD_Exist->id,'attr_id'=>$attr_id);
													$attr_whr = array('id'=>$variantAttrExist->id,'product_id'=>$PD_Exist->id);
													$this->ShopProductModel->updateData('products_variants_master',$attr_whr,$varaint_master_update);
												}else{
													$varaint_master_insert=array('product_id'=>$PD_Exist->id,'attr_id'=>$attr_id);
													$this->ShopProductModel->insertData('products_variants_master',$varaint_master_insert);
												}


												$OptionSelected=$this->SellerProductModel->getSingleDataByID('products_variants',array('product_id'=>$value->product_id,'parent_id'=>$product_id,'attr_id'=>$seller_attr_id),'id,attr_value');
												if(isset($OptionSelected) && $OptionSelected->id != 0){
													$OptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('id'=>$OptionSelected->attr_value,'attr_id'=>$seller_attr_id),'');
													if(isset($OptionData) && $OptionData->id != 0){
													$attr_value=$OptionData->id;
													if($OptionData->created_by_type != 0){
														$optionExist=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_options_name'=>$OptionData->attr_options_name,'shop_id'=> $to_shop_id),'');
														if(empty($optionExist)){
															$insertArr=array('attr_id'=>$attr_id,'attr_options_name'=>$OptionData->attr_options_name,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
															$attr_value=$this->CategoryModel->insertData('eav_attributes_options',$insertArr);
														}else{
															$attr_value=$optionExist->id;
															$updateArr=array('attr_id'=>$attr_id,'attr_options_name'=>$OptionData->attr_options_name,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'updated_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
															$whr_arr=array('id'=>$attr_value);
															$this->db->where($whr_arr);
															$this->db->update('eav_attributes_options', $updateArr);
															//$this->CategoryModel->updateData('eav_attributes_options',$whr_arr,$updateArr);
														}
													}

													$productVariant=$this->ShopProductModel->getSingleDataByID('products_variants',array('product_id'=>$simpleProductId,'parent_id'=>$PD_Exist->id,'attr_id'=>$attr_id),'id,attr_value');
													if(isset($productVariant) && $productVariant->id != 0){
														$pv_update=array('attr_id'=>$attr_id,'attr_value'=>$attr_value);
														$whr_pv=array('id'=>$productVariant->id);
														$this->ShopProductModel->updateData('products_variants',$whr_pv,$pv_update);
													}else{
														$pv_insert=array('product_id'=>$simpleProductId,'parent_id'=>$PD_Exist->id,'attr_id'=>$attr_id,'attr_value'=>$attr_value);
														$this->ShopProductModel->insertData('products_variants',$pv_insert);
													}
													}
												}
											}
										}



										$confGalleryImages=$this->SellerProductModel->getMultiDataByID('products_media_gallery',array('product_id'=>$product_id,'child_id'=>$value->product_id),'');

										if(is_array($confGalleryImages) && count($confGalleryImages) > 0){
											$oldConfGalleryImages=$this->ShopProductModel->getMultiDataByID('products_media_gallery',array('product_id'=>$PD_Exist->id,'child_id'=>$simpleProductId),'id,image');
											if(isset($oldConfGalleryImages) && count($oldConfGalleryImages)>0){
												foreach($oldConfGalleryImages as $val){
													$at_whr = array('id'=>$val->id);
													$this->ShopProductModel->deleteDataById('products_media_gallery',$at_whr);
												}
											}

											foreach($confGalleryImages as $val){
												$copied = $this->copyProductImage($val, $shop_source_bucket, $shop_upload_bucket);

												if($copied === true){
													$media_insert=array('product_id'=>$PD_Exist->id,'child_id'=>$simpleProductId,'image'=>$val->image,'image_title'=>$val->image_title,'image_position'=>$val->image_position,'is_default'=>$val->is_default,'is_base_image'=>$val->is_base_image);
													$this->ShopProductModel->insertData('products_media_gallery',$media_insert);
												}
											}
										}
									}
								}

								if(is_array($cat_id_arr) && count($cat_id_arr) > 0){
									foreach($cat_id_arr as $cat_id){
										$checkCatExist=$this->ShopProductModel->getSingleDataByID('products_category',array('product_id'=>$PD_Exist->id,'category_ids'=>$cat_id,'level'=>0),'id');
										if(isset($checkCatExist) && $checkCatExist->id != 0){
											$root_cat_update=array('category_ids'=>$cat_id);
											$whr_root_cat = array('product_id'=>$PD_Exist->id,'category_ids'=>$cat_id,'level'=>0);
											$this->ShopProductModel->updateData('products_category',$whr_root_cat,$root_cat_update);
										}else{
											$root_cat_insert=array('product_id'=>$PD_Exist->id,'category_ids'=>$cat_id,'level'=>0);
											$this->ShopProductModel->insertData('products_category',$root_cat_insert);
										}

										$checkbtb_level_zero=$this->ShopProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cat_id,'level'=>0),'id');
										if(empty($checkbtb_level_zero)){
											$fbc_cat_insert=array('category_id'=>$cat_id,'level'=>0,'fbc_user_id'=>$to_fbc_user_id);
											$this->ShopProductModel->insertData('fbc_users_category_b2b',$fbc_cat_insert);
										}
									}
								}

								if(is_array($sub_cat_id_arr) && count($sub_cat_id_arr) > 0){
									foreach($sub_cat_id_arr as $sub_cat_id){
										$checkSubCatExist=$this->ShopProductModel->getSingleDataByID('products_category',array('product_id'=>$PD_Exist->id,'category_ids'=>$sub_cat_id,'level'=>1),'id');
										if(isset($checkSubCatExist) && $checkSubCatExist->id != 0){
											$sub_cat_update=array('category_ids'=>$sub_cat_id);
											$whr_sub_cat = array('product_id'=>$PD_Exist->id,'category_ids'=>$sub_cat_id,'level'=>1);
											$this->ShopProductModel->updateData('products_category',$whr_sub_cat,$sub_cat_update);
										}else{
											$sub_cat_insert=array('product_id'=>$PD_Exist->id,'category_ids'=>$sub_cat_id,'level'=>1);
											$this->ShopProductModel->insertData('products_category',$sub_cat_insert);
										}

										$checkbtb_level_one=$this->ShopProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$sub_cat_id,'level'=>1),'id');
										if(empty($checkbtb_level_one)){
											$fbc_subcat_insert=array('category_id'=>$sub_cat_id,'level'=>1,'fbc_user_id'=>$to_fbc_user_id);
											$this->ShopProductModel->insertData('fbc_users_category_b2b',$fbc_subcat_insert);
										}
									}
								}

								if(is_array($child_cat_id_arr) && count($child_cat_id_arr) > 0){
									foreach($child_cat_id_arr as $child_cat_id){
										$checkChildCatExist=$this->ShopProductModel->getSingleDataByID('products_category',array('product_id'=>$PD_Exist->id,'category_ids'=>$child_cat_id,'level'=>2),'id');
										if(isset($checkChildCatExist) && $checkChildCatExist->id != 0){
											$child_update=array('category_ids'=>$child_cat_id);
											$wh_cu=array('level'=>2,'category_ids'=>$cat_id,'product_id'=>$PD_Exist->id);
											$this->ShopProductModel->updateData('products_category',$wh_cu,$child_update);
										}else{
											//$child_insert=array('product_id'=>$insertedProductID,'category_ids'=>$child_cat_id,'level'=>2);
											$child_insert=array('product_id'=>$PD_Exist->id,'category_ids'=>$child_cat_id,'level'=>2);
											$this->ShopProductModel->insertData('products_category',$child_insert);
										}

										$checkbtb_level_two=$this->ShopProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cid,'level'=>2),'id');
										if(empty($checkbtb_level_two)){
											$fbc_childcat_insert=array('category_id'=>$cid,'level'=>2,'fbc_user_id'=>$to_fbc_user_id);
											$this->ShopProductModel->insertData('fbc_users_category_b2b',$fbc_childcat_insert);
										}
									}
								}

								//Need to Add Delete For Products_Attribute For Buyer Shop

								$productAttr=$this->SellerProductModel->getMultiDataByID('products_attributes',array('product_id'=>$product_id),'');
								// print_r($productAttr);
								if(is_array($productAttr) && count($productAttr) > 0){
									foreach($productAttr as $val){
										$attr_id = 	$from_attr_id = $val->attr_id;
										$attr_value = $val->attr_value;
										//$attrDetails=$this->EavAttributesModel->get_attribute_detail($attr_id);
										$attrDetails=$this->EavAttributesModel->get_attribute_detail_ownshop_admin($attr_id,$ShopID);
										if(empty($attrDetails)){
											continue;
										}

										$seller_attr_id =$attr_id;
										$attr_code = $attrDetails->attr_code;
										$attr_name = $attrDetails->attr_name;
										$attr_description = $attrDetails->attr_description;
										$attr_properties = $attrDetails->attr_properties;
										if($attrDetails->created_by_type != 0){
											$attrExist=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code, 'attr_type'=>1,'shop_id'=> $to_shop_id),'');
											if(empty($attrExist)){
												$insertArr=array('attr_code'=>$attr_code,'attr_name'=>$attr_name,'attr_description'=>$attr_description,'attr_properties'=>$attr_properties,'attr_type'=>1,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
												$attr_id=$this->CategoryModel->insertData('eav_attributes',$insertArr);
											}else{
												$attr_id=$attrExist->id;

												/*$updateArr=array('attr_code'=>$attr_code,'attr_name'=>$attr_name,'attr_description'=>$attr_description,'attr_properties'=>$attr_properties,'attr_type'=>1,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'updated_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
												$whr_arr=array('id'=>$attr_id);
												$this->db->where($whr_arr);
												$this->db->update('eav_attributes', $updateArr);*/
												//$this->CategoryModel->updateData('eav_attributes',$whr_arr,$updateArr);
											}

											// $args['shop_id']	=	$to_shop_id;
											// $args['fbc_user_id']	=	$to_fbc_user_id;

											// $this->load->model('ShopProductModel');
											// $this->ShopProductModel->init($args);

											$checkbtb_visiblity=$this->ShopProductModel->getSingleDataByID('fbc_users_attributes_visibility',array('attr_id'=>$attr_id),'id');

											if(empty($checkbtb_visiblity)){

												$seller_attr_visibility = $this->SellerProductModel->getSingleDataByID('fbc_users_attributes_visibility',array('attr_id'=>$seller_attr_id),'');

												$fbc_attr_insert=array(
													'attr_id'=>$attr_id,
													'display_on_frontend'=>$seller_attr_visibility->display_on_frontend,
													'filterable_with_results'=>$seller_attr_visibility->filterable_with_results,
													'created_at'=>time(),
													'created_by'=>$to_fbc_user_id
												);
												$this->ShopProductModel->insertData('fbc_users_attributes_visibility',$fbc_attr_insert);
											}
										}

										if(!empty($attr_value) && $attr_properties==5){

											$AttrOptionSelected=$this->SellerProductModel->getSingleDataByID('products_attributes',array('product_id'=>$product_id,'attr_id'=>$seller_attr_id),'id,attr_value');

												$AttrOptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('id'=>$AttrOptionSelected->attr_value,'attr_id'=>$seller_attr_id),'');

												if($AttrOptionData->created_by_type != 0){
													$optionExist=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_id'=>$attr_id,'attr_options_name'=>$AttrOptionData->attr_options_name,'shop_id'=> $to_shop_id),'');
													if(empty($optionExist)){

														$attributesData=array(
															'attr_id'    		=> $attr_id,
															'attr_options_name'	=> $AttrOptionData->attr_options_name,
															'created_by' 		=> $to_fbc_user_id,
															'shop_id'			=> $to_shop_id,
															'created_by_type' 	=> 1,
															'status'			=> 1,
															'created_at'		=> time(),
															'ip'				=> $_SERVER['REMOTE_ADDR'],
														);
														$this->db->insert('eav_attributes_options', $attributesData);
														$option_id=	$this->db->insert_id();
														$attr_value=$option_id;

													}else{
														$attr_value=$optionExist->id;

													}
												}



												/*
													$IsOptionExist=$this->EavAttributesModel->check_attributes_options_exist_by_option_id($to_shop_id,$attr_id,$attr_value);
													if(isset($IsOptionExist) && $IsOptionExist->id!=''){
														$option_id=	$IsOptionExist->id;
														$attr_value=$option_id;
													}else{
														$IsOptionExist=$this->EavAttributesModel->check_attributes_options_exist_by_option_id($from_shop_id,$from_attr_id,$attr_value);
														if(isset($IsOptionExist) && $IsOptionExist->id!=''){
															$attributesData=array(
																'attr_id'    		=> $attr_id,
																'attr_options_name'	=> $IsOptionExist->attr_options_name,
																'created_by' 		=> $to_fbc_user_id,
																'shop_id'			=> $to_shop_id,
																'created_by_type' 	=> 1,
																'status'			=> 1,
																'created_at'		=> time(),
																'ip'				=> $_SERVER['REMOTE_ADDR'],
															);
															$option_id=	$this->db->insert('eav_attributes_options', $attributesData);
															$attr_value=$option_id;
														}
													}
												*/

											}
											else if(!empty($attr_value) && $attr_properties==6){

												if( strpos($attr_value, ',') !== false ) {
													$attr_value_arr=explode(',',$attr_value);
												}else{
													$attr_value_arr[]=$attr_value;
												}

												array_filter($attr_value_arr);

												$attr_value_ids=array();

												if(isset($attr_value_arr) && count($attr_value_arr)>0){
													foreach($attr_value_arr as $attr_value_option){

														$AttrOptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('id'=>$attr_value_option),'');

                                                            if($AttrOptionData->created_by_type != 0){

                                                                $optionExist=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_id'=>$attr_id,'attr_options_name'=>$AttrOptionData->attr_options_name,'shop_id'=> $to_shop_id),'');



                                                                if(empty($optionExist)){

                                                                    $attributesData=array(
                                                                        'attr_id'    		=> $attr_id,
                                                                        'attr_options_name'	=> $AttrOptionData->attr_options_name,
                                                                        'created_by' 		=> $to_fbc_user_id,
                                                                        'shop_id'			=> $to_shop_id,
                                                                        'created_by_type' 	=> 1,
                                                                        'status'			=> 1,
                                                                        'created_at'		=> time(),
                                                                        'ip'				=> $_SERVER['REMOTE_ADDR'],
                                                                    );
                                                                	$this->db->insert('eav_attributes_options', $attributesData);
                                                                	$option_id=	$this->db->insert_id();
                                                                	$attr_value_ids[]=$option_id;
                                                                }else{

                                                                    $option_id=	$optionExist->id;
                                                                    $attr_value_ids[]=$option_id;

                                                                }

                                                            }

														/*
														$IsOptionExist=$this->EavAttributesModel->check_attributes_options_exist_by_option_id($to_shop_id,$attr_id,$attr_value_option);
														if(isset($IsOptionExist) && $IsOptionExist->id!=''){
															$option_id=	$IsOptionExist->id;
															$attr_value_ids[]=$option_id;
														}else{
															$IsOptionExist=$this->EavAttributesModel->check_attributes_options_exist_by_option_id($from_shop_id,$from_attr_id,$attr_value_option);
															if(isset($IsOptionExist) && $IsOptionExist->id!=''){
																$attributesData=array(
																	'attr_id'    		=> $attr_id,
																	'attr_options_name'	=> $IsOptionExist->attr_options_name,
																	'created_by' 		=> $to_fbc_user_id,
																	'shop_id'			=> $to_shop_id,
																	'created_by_type' 	=> 1,
																	'status'			=> 1,
																	'created_at'		=> time(),
																	'ip'				=> $_SERVER['REMOTE_ADDR'],
																);
																$option_id=	$this->db->insert('eav_attributes_options', $attributesData);
																$attr_value_ids[]=$option_id;
															}
														}

														*/
													}

													$attr_value=implode(',',$attr_value_ids);
												}

											}

										$productAttrExist=$this->ShopProductModel->getSingleDataByID('products_attributes',array('product_id'=>$PD_Exist->id,'attr_id'=>$attr_id),'id');
										if(isset($productAttrExist) && $productAttrExist->id != 0){


											$attr_update=array('product_id'=>$PD_Exist->id,'attr_id'=>$attr_id,'attr_value'=>$attr_value);
											$attr_whr = array('id'=>$productAttrExist->id,'product_id'=>$PD_Exist->id);
											$this->ShopProductModel->updateData('products_attributes',$attr_whr,$attr_update);
										}else{
											$attr_insert=array('product_id'=>$PD_Exist->id,'attr_id'=>$attr_id,'attr_value'=>$attr_value);
											$this->ShopProductModel->insertData('products_attributes',$attr_insert);
										}
									}
								}

								$galleryImages=$this->SellerProductModel->getMultiDataByID('products_media_gallery',array('product_id'=>$product_id,'child_id'=>null),'');
								//print_r($galleryImages);
								if(is_array($galleryImages) && count($galleryImages) > 0){
									$oldGalleryImages=$this->ShopProductModel->getMultiDataByID('products_media_gallery',array('product_id'=>$PD_Exist->id,'child_id'=>null),'id,image');
									if(isset($oldGalleryImages) && count($oldGalleryImages)>0){
										foreach($oldGalleryImages as $val){
											$at_whr = array('id'=>$val->id);
											$this->ShopProductModel->deleteDataById('products_media_gallery',$at_whr);
										}
									}
									foreach($galleryImages as $val){
										$copied = $this->copyProductImage($val, $shop_source_bucket, $shop_upload_bucket);
										if($copied === true){
											$media_insert = array('product_id' => $PD_Exist->id, 'image' => $val->image, 'image_title' => $val->image_title, 'image_position' => $val->image_position, 'is_default' => $val->is_default, 'is_base_image' => $val->is_base_image);
											$this->ShopProductModel->insertData('products_media_gallery', $media_insert);
										}
									}
								}

							}else{


								$PC_Exist=$this->ShopProductModel->getSingleDataByID('products',array('product_code'=>$product_code,'remove_flag'=>0),'id,name');
								if(isset($PC_Exist) && $PC_Exist->id!=''){

									$slugcount = $this->ShopProductModel->productslugcount($product_name,$PC_Exist->id);
									if ($slugcount > 0) {
										$slugcount=$slugcount+1;
										$url_key = $url_key."-".$slugcount;
									}else{
										$url_key = $url_key."-0";
									}

									$new_product_code = 'B2B-'.$product_code;
									$new_product_sku = 'B2B-'.$productDetail->sku;
								}else{

									$slugcount = $this->ShopProductModel->productslugcount($product_name);
									if ($slugcount > 0) {
										$slugcount=$slugcount+1;
										$url_key = $url_key."-".$slugcount;
									}else{
										$url_key = $url_key."-0";
									}

									$new_product_code = $product_code;
									$new_product_sku = $productDetail->sku;
								}
								/*-------------------Insert Product :start--------------------------------------*/

								if(isset($appliedData->price_converted) && $appliedData->price_converted > 0){
									$webshop_price = $productDetail->webshop_price*$appliedData->price_converted;
								}else{
									$webshop_price = $productDetail->webshop_price;
								}


								if($Rounded_price_flag == 1){
									$webshop_price_final =  round($webshop_price);
								}else{
									$webshop_price_final =  $webshop_price;
								}

								$estimate_delivery_time=$product_delivery_duration;


								$product_customer_type = $productDetail->customer_type_ids;
								$seller_mapped_custType = $this->SupplierModel->getAppliedCustomeTypeMapping($to_shop_id,$applied_id,$product_customer_type);
								$customer_type_ids = implode(',', array_unique(explode(',', $seller_mapped_custType->customer_type_ids)));

								$insertdata=array(
											'name'=>$productDetail->name,
											'product_code'=>$new_product_code,
											'url_key'=>$url_key,
											'meta_title'=>$productDetail->meta_title,
											'meta_keyword'=>$productDetail->meta_keyword,
											'meta_description'=>$productDetail->meta_description,
											'search_keywords' => $productDetail->search_keywords,
											'promo_reference' => $productDetail->promo_reference,
											'weight'=>$productDetail->weight,
											//'sku'=>$new_product_sku,
											'sku'=>$productDetail->sku,
											//'price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price),
											//'cost_price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->cost_price),
											'price'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price,
											'cost_price'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->cost_price*$appliedData->price_converted):$productDetail->cost_price,
											'barcode'=>$productDetail->barcode,
											'gender'=>$productDetail->gender,
											'base_image'=>$productDetail->base_image,
											'description'=>$productDetail->description,
											'highlights'=>$productDetail->highlights,
											'product_reviews_code'=>$productDetail->product_reviews_code,
											'launch_date'=>$productDetail->launch_date,
											'estimate_delivery_time'=>$estimate_delivery_time,
											'product_return_time'=>$productDetail->product_return_time,
											'product_drop_shipment'=>$productDetail->product_drop_shipment,
											'product_type'=>$product_type,
											'product_inv_type'=>$product_inv_type,
											'status'=>1,
											// 'shop_id'=>$to_shop_id,
											'shop_id'=>$ShopID,
											'shop_product_id'=>$product_id,
											'shop_price'=>$productDetail->price,
											'shop_cost_price'=>'',
											'shop_currency'=>$shop_currency,
											'fbc_user_id'=>$to_fbc_user_id,

											'tax_percent'=>$productDetail->tax_percent,
											//'tax_amount'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->tax_amount),
											//'webshop_price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->webshop_price),
											'tax_amount'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->tax_amount*$appliedData->price_converted):$productDetail->tax_amount,
											'webshop_price'=>$webshop_price_final,
											'shop_buyin_discount_percent'=>$shop_buyin_discount_percent,
											'shop_buyin_del_time_in_days'=>$shop_buyin_del_time_in_days,
											'shop_dropship_discount_percent'=>$shop_dropship_discount_percent,
											'shop_dropship_del_time_in_days'=>$shop_dropship_del_time_in_days,
											'shop_display_catalog_overseas'=>$shop_display_catalog_overseas,
											'shop_perm_to_change_price'=>$shop_perm_to_change_price,
											'shop_can_increase_price'=>$shop_can_increase_price,
											'shop_can_decrease_price'=>$shop_can_decrease_price,
											'customer_type_ids'=>$customer_type_ids,
											'created_at'=>time(),
											'imported_from'=>$ShopID,
											'ip'=>$_SERVER['REMOTE_ADDR']
									);
								$insertedProductID=$this->ShopProductModel->insertData('products',$insertdata);
								if($insertedProductID){
									$product_log_insert=array('product_id'=>$insertedProductID,'fbc_user_id'=>$to_fbc_user_id,'shop_id'=>$to_shop_id,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
									$this->db->insert('products_logs',$product_log_insert);
									$this->db->reset_query();
									if($productDetail->product_type == 'simple'){
										//$stock_insert=array('product_id'=>$insertedProductID,'qty'=>$qty,'available_qty'=>$qty,'min_qty'=>0,'is_in_stock'=>1);
										$stock_insert=array('product_id'=>$insertedProductID,'qty'=>0,'available_qty'=>0,'min_qty'=>0,'is_in_stock'=>1);
										$this->ShopProductModel->insertData('products_inventory',$stock_insert);



									}else if($productDetail->product_type == 'configurable'){
										$productDetail=$this->SellerProductModel->getSingleDataByID('products',array('id'=>$value->product_id),'');
										//print_r($productDetail);exit;
										/*****---------calculate cost price-----------------------****/
										if((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount>0) && $productDetail->price>0){
											$RowTotalData=$this->CommonModel->calculate_percent_data($productDetail->price,$FbcUserB2BData->buyin_discount);
											if($seller_shop_currency_code!=$buyer_shop_currency_code){
												/*$percent_amount=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$RowTotalData['percent_amount']);
												$converted_price=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price);*/

												$percent_amount=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($RowTotalData['percent_amount']*$appliedData->price_converted):$RowTotalData['percent_amount'];

												$converted_price=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price;

												if($converted_price>$percent_amount){
													$cost_price=$converted_price - $percent_amount;  //debug on
												}else{
													//$cost_price=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price);
													$cost_price=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price;
												}

											}else{
												$percent_amount=$RowTotalData['percent_amount'];
												$cost_price=$productDetail->price - $percent_amount;
											}
										}else{
											$percent_amount=0;
											//$cost_price=sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price);
											$cost_price=(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price;

										}


										if(isset($appliedData->price_converted) && $appliedData->price_converted > 0){
											$webshop_price = $productDetail->webshop_price*$appliedData->price_converted;
										}else{
											$webshop_price = $productDetail->webshop_price;
										}


										if($Rounded_price_flag == 1){
											$webshop_price_final =  round($webshop_price);
										}else{
											$webshop_price_final =  $webshop_price;
										}

										$insertdata=array(
											'name'=>$productDetail->name,
											'parent_id'=>$insertedProductID,
											'meta_title'=>$productDetail->meta_title,
											'meta_keyword'=>$productDetail->meta_keyword,
											'meta_description'=>$productDetail->meta_description,
											'search_keywords' => $productDetail->search_keywords,
											'promo_reference' => $productDetail->promo_reference,
											'weight'=>$productDetail->weight,
											//'sku'=>$new_product_sku,
											'sku'=>$productDetail->sku,
											//'price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->price),
											'price'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->price*$appliedData->price_converted):$productDetail->price,
											'cost_price'=>$cost_price,
											'barcode'=>$productDetail->barcode,
											'product_type'=>'conf-simple',
											'product_inv_type'=>$product_inv_type,
											'status'=>1,
											// 'shop_id'=>$to_shop_id,
											'launch_date'=>$productDetail->launch_date,
											'shop_id'=>$ShopID,
											'shop_product_id'=>$value->product_id,
											'shop_price'=>$productDetail->price,
											'shop_cost_price'=>'',
											'shop_currency'=>$shop_currency,
											'fbc_user_id'=>$to_fbc_user_id,
											'tax_percent'=>$productDetail->tax_percent,
											//'tax_amount'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->tax_amount),
											//'webshop_price'=>sis_convert_currency($seller_shop_currency_code,$buyer_shop_currency_code,$productDetail->webshop_price),
											'tax_amount'=>(isset($appliedData->price_converted) && $appliedData->price_converted > 0)?($productDetail->tax_amount*$appliedData->price_converted):$productDetail->tax_amount,
											'webshop_price'=>$webshop_price_final,
											'shop_buyin_discount_percent'=>$shop_buyin_discount_percent,
											'shop_buyin_del_time_in_days'=>$shop_buyin_del_time_in_days,
											'shop_dropship_discount_percent'=>$shop_dropship_discount_percent,
											'shop_dropship_del_time_in_days'=>$shop_dropship_del_time_in_days,
											'shop_display_catalog_overseas'=>$shop_display_catalog_overseas,
											'shop_perm_to_change_price'=>$shop_perm_to_change_price,
											'shop_can_increase_price'=>$shop_can_increase_price,
											'shop_can_decrease_price'=>$shop_can_decrease_price,
											'created_at'=>time(),
											'imported_from'=>$ShopID,
											'ip'=>$_SERVER['REMOTE_ADDR']
											);
										$simpleProductId=$this->ShopProductModel->insertData('products',$insertdata);
										if($simpleProductId){
											//$stock_insert2=array('product_id'=>$simpleProductId,'qty'=>$qty,'available_qty'=>$qty,'min_qty'=>0,'is_in_stock'=>1);
											$stock_insert2=array('product_id'=>$simpleProductId,'qty'=>0,'available_qty'=>0,'min_qty'=>0,'is_in_stock'=>1);
											$this->ShopProductModel->insertData('products_inventory',$stock_insert2);


											$variantMaster=$this->SellerProductModel->getMultiDataByID('products_variants_master',array('product_id'=>$product_id),'');
											if(is_array($variantMaster) && count($variantMaster)>0){
												foreach($variantMaster as $variant){
													$attr_id = $variant->attr_id;
													$seller_attr_id = $variant->attr_id;
													//$attrDetails=$this->EavAttributesModel->get_attribute_detail($variant->attr_id);
													$attrDetails=$this->EavAttributesModel->get_attribute_detail_ownshop_admin($variant->attr_id,$ShopID);
													if(empty($attrDetails)){
														continue;
													}

													$attr_code = $attrDetails->attr_code;
													$attr_name = $attrDetails->attr_name;
													$attr_description = $attrDetails->attr_description;
													$attr_properties = $attrDetails->attr_properties;
													if($attrDetails->created_by_type != 0){
														$attrExist=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code, 'attr_type'=>2,'shop_id'=> $to_shop_id,),'');

														if(empty($attrExist)){
															$insertArr=array('attr_code'=>$attr_code,'attr_name'=>$attr_name,'attr_description'=>$attr_description,'attr_properties'=>$attr_properties,'attr_type'=>2,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
															$attr_id=$this->CategoryModel->insertData('eav_attributes',$insertArr);
														}else{
															$attr_id=$attrExist->id;
														}
													}

													$variantAttrExist=$this->ShopProductModel->getSingleDataByID('products_variants_master',array('product_id'=>$insertedProductID,'attr_id'=>$attr_id),'id');  // by al
													//$variantAttrExist=$this->ShopProductModel->getSingleDataByID('products_variants_master',array('product_id'=>$PD_Exist->id,'attr_id'=>$attr_id),'id');  // by ranju
													// echo $attr_id;exit;
													if(isset($variantAttrExist) && $variantAttrExist->id != 0){
														$variantMasterId=$variantAttrExist->id;
														$varaint_master_update=array('product_id'=>$insertedProductID,'attr_id'=>$attr_id);
														$attr_whr = array('id'=>$variantAttrExist->id,'product_id'=>$PD_Exist->id);
														$this->ShopProductModel->updateData('products_variants_master',$attr_whr,$varaint_master_update);
													}else{
														$varaint_master_insert=array('product_id'=>$insertedProductID,'attr_id'=>$attr_id);
														$this->ShopProductModel->insertData('products_variants_master',$varaint_master_insert);
													}


													$OptionSelected=$this->SellerProductModel->getSingleDataByID('products_variants',array('product_id'=>$value->product_id,'parent_id'=>$product_id,'attr_id'=>$seller_attr_id),'id,attr_value');
													if(isset($OptionSelected) && $OptionSelected->id != 0){
														$OptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('id'=>$OptionSelected->attr_value,'attr_id'=>$seller_attr_id),'');
														if(isset($OptionData) && $OptionData->id != 0){
														$attr_value=$OptionData->id;
														if($OptionData->created_by_type != 0){
															$optionExist=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_options_name'=>$OptionData->attr_options_name,'shop_id'=> $to_shop_id),'');
															if(empty($optionExist)){
																$insertArr=array('attr_id'=>$attr_id,'attr_options_name'=>$OptionData->attr_options_name,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
																$attr_value=$this->CategoryModel->insertData('eav_attributes_options',$insertArr);
															}else{
																$attr_value=$optionExist->id;
																$updateArr=array('attr_id'=>$attr_id,'attr_options_name'=>$OptionData->attr_options_name,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'updated_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
																$whr_arr=array('id'=>$attr_value);
																$this->db->where($whr_arr);
																$this->db->update('eav_attributes_options', $updateArr);
																//$this->CategoryModel->updateData('eav_attributes_options',$whr_arr,$updateArr);
															}
														}

														$productVariant=$this->ShopProductModel->getSingleDataByID('products_variants',array('product_id'=>$simpleProductId,'parent_id'=>$insertedProductID,'attr_id'=>$attr_id),'id,attr_value');
														if(isset($productVariant) && $productVariant->id != 0){
															$pv_update=array('attr_id'=>$attr_id,'attr_value'=>$attr_value);
															$whr_pv=array('id'=>$productVariant->id);
															$this->ShopProductModel->updateData('products_variants',$whr_pv,$pv_update);
														}else{
															$pv_insert=array('product_id'=>$simpleProductId,'parent_id'=>$insertedProductID,'attr_id'=>$attr_id,'attr_value'=>$attr_value);
															$this->ShopProductModel->insertData('products_variants',$pv_insert);
														}
														}
													}
												}
											}

											$confGalleryImages=$this->SellerProductModel->getMultiDataByID('products_media_gallery',array('product_id'=>$product_id,'child_id'=>$value->product_id),'');
											if(is_array($confGalleryImages) && count($confGalleryImages) > 0){
												foreach($confGalleryImages as $val){
													$copied = $this->copyProductImage($val, $shop_source_bucket, $shop_upload_bucket);


													if($copied === true){
														$media_insert=array('product_id'=>$insertedProductID,'child_id'=>$simpleProductId,'image'=>$val->image,'image_title'=>$val->image_title,'image_position'=>$val->image_position,'is_default'=>$val->is_default,'is_base_image'=>$val->is_base_image);
														$this->ShopProductModel->insertData('products_media_gallery',$media_insert);
													}
												}
											}
										}
									}

									if(isset($cat_id_arr) && count($cat_id_arr) > 0){
										foreach($cat_id_arr as $cat_id){
											$root_cat_insert=array('product_id'=>$insertedProductID,'category_ids'=>$cat_id,'level'=>0);
											$this->ShopProductModel->insertData('products_category',$root_cat_insert);

											$checkbtb_level_zero=$this->ShopProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cat_id,'level'=>0),'id');
											if(empty($checkbtb_level_zero)){
												$fbc_cat_insert=array('category_id'=>$cat_id,'level'=>0,'fbc_user_id'=>$to_fbc_user_id);
												$this->ShopProductModel->insertData('fbc_users_category_b2b',$fbc_cat_insert);
											}
										}
									}

									if(isset($sub_cat_id_arr) && count($sub_cat_id_arr) > 0){
										foreach($sub_cat_id_arr as $sub_cat_id){
											$sub_cat_insert=array('product_id'=>$insertedProductID,'category_ids'=>$sub_cat_id,'level'=>1);
											$this->ShopProductModel->insertData('products_category',$sub_cat_insert);

											$checkbtb_level_one=$this->ShopProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$sub_cat_id,'level'=>1),'id');
											if(empty($checkbtb_level_one)){
												$fbc_subcat_insert=array('category_id'=>$sub_cat_id,'level'=>1,'fbc_user_id'=>$to_fbc_user_id);
												$this->ShopProductModel->insertData('fbc_users_category_b2b',$fbc_subcat_insert);
											}
										}
									}

									if(isset($child_cat_id_arr) && count($child_cat_id_arr) > 0){
										foreach($child_cat_id_arr as $child_cat_id){
											$child_insert=array('product_id'=>$insertedProductID,'category_ids'=>$child_cat_id,'level'=>2);
											$this->ShopProductModel->insertData('products_category',$child_insert);

											$checkbtb_level_two=$this->ShopProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$child_cat_id,'level'=>2),'id');
											if(empty($checkbtb_level_two)){
												$fbc_childcat_insert=array('category_id'=>$child_cat_id,'level'=>2,'fbc_user_id'=>$to_fbc_user_id);
												$this->ShopProductModel->insertData('fbc_users_category_b2b',$fbc_childcat_insert);
											}
										}
									}

									$productAttr=$this->SellerProductModel->getMultiDataByID('products_attributes',array('product_id'=>$product_id),'');
									// print_r($productAttr);
									if(is_array($productAttr) && count($productAttr) > 0){
										foreach($productAttr as $val){
											$attr_id = $from_attr_id = $val->attr_id;
											$attr_value = (isset($val->attr_value) && $val->attr_value!='')?$val->attr_value:'' ;
											//$attrDetails=$this->EavAttributesModel->get_attribute_detail($attr_id);
											$attrDetails=$this->EavAttributesModel->get_attribute_detail_ownshop_admin($attr_id,$ShopID);
											if(empty($attrDetails)){
												continue;
											}

											$seller_attr_id =$attr_id;
											$attr_code = $attrDetails->attr_code;
											$attr_name = $attrDetails->attr_name;
											$attr_description = $attrDetails->attr_description;
											$attr_properties = $attrDetails->attr_properties;
											if($attrDetails->created_by_type != 0){

												//$attrExist=$this->CommonModel->getSingleDataByID('eav_attributes',array('id'=>$attr_id, 'shop_id'=> $to_shop_id),'');

												$attrExist=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code, 'attr_type'=>1,'shop_id'=> $to_shop_id),'');

												if(empty($attrExist)){
													$insertArr=array('attr_code'=>$attr_code,'attr_name'=>$attr_name,'attr_type'=>1,'attr_description'=>$attr_description,'attr_properties'=>$attr_properties,'shop_id'=>$to_shop_id,'created_by'=>$to_fbc_user_id,'created_by_type'=>1,'status'=>1,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
													$attr_id=$this->CategoryModel->insertData('eav_attributes',$insertArr);
												}else{
													$attr_id=$attrExist->id;
												}

												$checkbtb_visiblity=$this->ShopProductModel->getSingleDataByID('fbc_users_attributes_visibility',array('attr_id'=>$attr_id),'id');

												if(empty($checkbtb_visiblity)){

													$seller_attr_visibility = $this->SellerProductModel->getSingleDataByID('fbc_users_attributes_visibility',array('attr_id'=>$seller_attr_id),'');

													$fbc_attr_insert=array(
														'attr_id'=>$attr_id,
														'display_on_frontend'=>$seller_attr_visibility->display_on_frontend,
														'filterable_with_results'=>$seller_attr_visibility->filterable_with_results,
														'created_at'=>time(),
														'created_by'=>$to_fbc_user_id
													);
													$this->ShopProductModel->insertData('fbc_users_attributes_visibility',$fbc_attr_insert);
												}
											}

											if(!empty($attr_value) && $attr_properties==5){

												$AttrOptionSelected=$this->SellerProductModel->getSingleDataByID('products_attributes',array('product_id'=>$product_id,'attr_id'=>$seller_attr_id),'id,attr_value');

												$AttrOptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('id'=>$AttrOptionSelected->attr_value,'attr_id'=>$seller_attr_id),'');

												if($AttrOptionData->created_by_type != 0){
													$optionExist=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_id'=>$attr_id,'attr_options_name'=>$AttrOptionData->attr_options_name,'shop_id'=> $to_shop_id),'');
													if(empty($optionExist)){

														$attributesData=array(
															'attr_id'    		=> $attr_id,
															'attr_options_name'	=> $AttrOptionData->attr_options_name,
															'created_by' 		=> $to_fbc_user_id,
															'shop_id'			=> $to_shop_id,
															'created_by_type' 	=> 1,
															'status'			=> 1,
															'created_at'		=> time(),
															'ip'				=> $_SERVER['REMOTE_ADDR'],
														);
														$this->db->insert('eav_attributes_options', $attributesData);
														$option_id=	$this->db->insert_id();
														$attr_value=$option_id;

													}else{
														$attr_value=$optionExist->id;

													}
												}


												/*
													$IsOptionExist=$this->EavAttributesModel->check_attributes_options_exist_by_option_id($to_shop_id,$attr_id,$attr_value);
													if(isset($IsOptionExist) && $IsOptionExist->id!=''){
														$option_id=	$IsOptionExist->id;
														$attr_value=$option_id;
													}else{
														$IsOptionExist=$this->EavAttributesModel->check_attributes_options_exist_by_option_id($from_shop_id,$from_attr_id,$attr_value);
														if(isset($IsOptionExist) && $IsOptionExist->id!=''){
															$attributesData=array(
																'attr_id'    		=> $attr_id,
																'attr_options_name'	=> $IsOptionExist->attr_options_name,
																'created_by' 		=> $to_fbc_user_id,
																'shop_id'			=> $to_shop_id,
																'created_by_type' 	=> 1,
																'status'			=> 1,
																'created_at'		=> time(),
																'ip'				=> $_SERVER['REMOTE_ADDR'],
															);
															$option_id=	$this->db->insert('eav_attributes_options', $attributesData);
															$attr_value=$option_id;

														}
													}
												*/

											}
											else if(!empty($attr_value) && $attr_properties==6){

												if( strpos($attr_value, ',') !== false ) {
													$attr_value_arr=explode(',',$attr_value);
												}else{
													$attr_value_arr[]=$attr_value;
												}

												array_filter($attr_value_arr);

											$attr_value_ids=array();

												if(isset($attr_value_arr) && count($attr_value_arr)>0){
													foreach($attr_value_arr as $attr_value_option){

														$AttrOptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('id'=>$attr_value_option),'');

                                                            if($AttrOptionData->created_by_type != 0){

                                                                $optionExist=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_id'=>$attr_id,'attr_options_name'=>$AttrOptionData->attr_options_name,'shop_id'=> $to_shop_id),'');

                                                                if(empty($optionExist)){

                                                                    $attributesData=array(
                                                                        'attr_id'    		=> $attr_id,
                                                                        'attr_options_name'	=> $AttrOptionData->attr_options_name,
                                                                        'created_by' 		=> $to_fbc_user_id,
                                                                        'shop_id'			=> $to_shop_id,
                                                                        'created_by_type' 	=> 1,
                                                                        'status'			=> 1,
                                                                        'created_at'		=> time(),
                                                                        'ip'				=> $_SERVER['REMOTE_ADDR'],
                                                                    );
                                                                	$this->db->insert('eav_attributes_options', $attributesData);
                                                                	$option_id=	$this->db->insert_id();
                                                                	$attr_value_ids[]=$option_id;
                                                                }else{

                                                                    $option_id=	$optionExist->id;
                                                                    $attr_value_ids[]=$option_id;

                                                                }

                                                            }

														/*
														$IsOptionExist=$this->EavAttributesModel->check_attributes_options_exist_by_option_id($to_shop_id,$attr_id,$attr_value_option);
														if(isset($IsOptionExist) && $IsOptionExist->id!=''){
															$option_id=	$IsOptionExist->id;
															$attr_value_ids[]=$option_id;
														}else{
															$IsOptionExist=$this->EavAttributesModel->check_attributes_options_exist_by_option_id($from_shop_id,$from_attr_id,$attr_value_option);
															if(isset($IsOptionExist) && $IsOptionExist->id!=''){
																$attributesData=array(
																	'attr_id'    		=> $attr_id,
																	'attr_options_name'	=> $IsOptionExist->attr_options_name,
																	'created_by' 		=> $to_fbc_user_id,
																	'shop_id'			=> $to_shop_id,
																	'created_by_type' 	=> 1,
																	'status'			=> 1,
																	'created_at'		=> time(),
																	'ip'				=> $_SERVER['REMOTE_ADDR'],
																);
																$option_id=	$this->db->insert('eav_attributes_options', $attributesData);
																$attr_value_ids[]=$option_id;
															}
														}
														*/
													}

													$attr_value=implode(',',$attr_value_ids);
												}

											}

											$attr_insert=array('product_id'=>$insertedProductID,'attr_id'=>$attr_id,'attr_value'=>$attr_value);
											$this->ShopProductModel->insertData('products_attributes',$attr_insert);
										}
									}

									$galleryImages=$this->SellerProductModel->getMultiDataByID('products_media_gallery',array('product_id'=>$product_id,'child_id'=>null),'');
									//print_r($galleryImages);
									if(is_array($galleryImages) && count($galleryImages) > 0){
										foreach($galleryImages as $val){
											$copied = $this->copyProductImage($val, $shop_source_bucket, $shop_upload_bucket);

											if($copied === true){
												$media_insert=array('product_id'=>$insertedProductID,'image'=>$val->image,'image_title'=>$val->image_title,'image_position'=>$val->image_position,'is_default'=>$val->is_default,'is_base_image'=>$val->is_base_image);
												$this->ShopProductModel->insertData('products_media_gallery',$media_insert);
											}
										}
									}
								}
							}
						}
					}
				}

				if(isset($appliedData) && $appliedData->id != 0){

						/*------------------Supplier db - Create/update b2b customers----------------------------------*/

						$Totalb2bItems=$this->ShopProductModel->getMultiDataById('b2b_orders_applied_details',array('applied_order_id'=>$applied_id),'');
						$TotalDropshipb2bItems=$this->ShopProductModel->getMultiDataById('b2b_orders_applied_details',array('applied_order_id'=>$applied_id,'product_inv_type' => 'dropship'),'');

						//if(count($Totalb2bItems) != count($TotalDropshipb2bItems)){

						$Checkb2bcustomerExist=$this->SellerProductModel->getSingleDataByID('b2b_customers',array('shop_id'=>$to_shop_id),'');

						if(isset($Checkb2bcustomerExist) && $Checkb2bcustomerExist->id!=''){


							if($_POST['tax_status'] > 0){

								$b2b_cust_update=array('tax_exampted'=>$_POST['tax_status'],'updated_at'=>time());
								$cust_where=array('id'=>$Checkb2bcustomerExist->id);
								$this->SellerProductModel->updateData('b2b_customers',$cust_where,$b2b_cust_update);
							} else{

								$b2b_cust_update=array('updated_at'=>time());
								$cust_where=array('id'=>$Checkb2bcustomerExist->id);
								$this->SellerProductModel->updateData('b2b_customers',$cust_where,$b2b_cust_update);
							}


						}else{
							$b2b_customer_insert=array('shop_id'=>$to_shop_id,'tax_exampted'=>$_POST['tax_status'],'created_at'=> time(), 'ip'=> $_SERVER['REMOTE_ADDR']);
							$this->SellerProductModel->insertData('b2b_customers',$b2b_customer_insert);

						}


						if($appliedData->total_buyin_products > 0 || $appliedData->total_virtual_products_withqty > 0){
							$transaction_id=$this->B2BOrdersModel->generate_new_transaction_id();
							$total_buyin_cost = $appliedData->total_buyin_cost;
							$total_virtual_cost = $appliedData->total_virtual_cost_withqty;
							$subtotal=$total_buyin_cost + $total_virtual_cost;
							$grand_total = $subtotal;

							$total_buyin_qty = $appliedData->total_buyin_products;
							$total_virtual_qty = $appliedData->total_virtual_products_withqty;
							$total_qty_ordered = $total_buyin_qty + $total_virtual_qty;


							/*****---------calculate net pay-----------------------****/
							if((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount>0) && $subtotal>0){
								$RowTotalData=$this->CommonModel->calculate_percent_data($subtotal,$FbcUserB2BData->buyin_discount);
								$percent_amount=$RowTotalData['percent_amount'];
								$grand_total=$subtotal-$percent_amount;
							}else{
								$percent_amount=0;
								$grand_total=$grand_total;

							}

							$discount_percent=$FbcUserB2BData->buyin_discount;

							$insertOrderData = array(
							   'increment_id' 		=> $transaction_id,
							   'order_barcode'		=> $transaction_id,
							   'applied_order_id'	=> $appliedData->id,
							   'shipment_type' 		=> 1,
							   'status' 			=> 0,
							   'discount_percent'	=> $discount_percent,
							   'discount_amount'	=> $percent_amount,
							   'shop_id' 			=> $to_shop_id,   //requested from shop id
							   'base_grand_total'	=> $grand_total,
							   'base_subtotal'		=> $subtotal,
							   'grand_total'		=> $grand_total,
							   'subtotal'			=> $subtotal,
							   'total_qty_ordered' 	=> $total_qty_ordered,
							   'created_at'   		=> strtotime(date('Y-m-d H:i:s')),
							   'created_by'   		=> $ShopID,
							   'ip'					=> $_SERVER['REMOTE_ADDR']
							);



							$orderId=$this->SellerProductModel->insertData('b2b_orders',$insertOrderData);
							if($orderId){

								$order_log_insert=array('order_id'=>$orderId,'shop_id'=>$ShopID,'fbc_user_id'=>$LoginID,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
								$this->db->insert('orders_logs',$order_log_insert);
								$this->db->reset_query();

								$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$to_shop_id),'');
								if(isset($shopData) && $shopData->shop_id != 0){
									if($shopData->bill_address_line1 != '' || $shopData->bill_address_line2 != ''){
										$insertBillingData = array(
										   'order_id' 			=> $orderId,
										   'address_type'		=> 1,
										   'address_line1'		=> $shopData->bill_address_line1,
										   'address_line2' 		=> $shopData->bill_address_line2,
										   'city' 				=> $shopData->bill_city,
										   'state' 				=> $shopData->bill_state,
										   'country'			=> $shopData->bill_country,
										   'pincode'			=> $shopData->bill_pincode,
										);

										$this->SellerProductModel->insertData('b2b_order_address',$insertBillingData);
									}

									if($shopData->ship_address_line1 != '' || $shopData->ship_address_line2 != ''){
										$insertBillingData = array(
										   'order_id' 			=> $orderId,
										   'address_type'		=> 2,
										   'address_line1'		=> $shopData->ship_address_line1,
										   'address_line2' 		=> $shopData->ship_address_line2,
										   'city' 				=> $shopData->ship_city,
										   'state' 				=> $shopData->ship_state,
										   'country'			=> $shopData->ship_country,
										   'pincode'			=> $shopData->ship_pincode,
										);

										$this->SellerProductModel->insertData('b2b_order_address',$insertBillingData);
									}
								}

								$b2bCust = $this->SellerProductModel->getSingleDataByID('b2b_customers',array('shop_id'=>$to_shop_id),'');

								$buyInProducts=$this->ShopProductModel->getMultiDataById('b2b_orders_applied_details',array('applied_order_id'=>$applied_id,'product_inv_type !=' => 'dropship','quantity !=' => 0),'');
								if(is_array($buyInProducts) && count($buyInProducts) >0 ){



									foreach($buyInProducts as $value){
										//$importedProductData=$this->ShopProductModel->getSingleDataByID('products',array('shop_product_id'=>$value->product_id, 'imported_from'=>$ShopID),'');
										$importedProductData=$this->SellerProductModel->getSingleDataByID('products',array('id'=>$value->product_id),'');
										if(isset($importedProductData) && $importedProductData->id != 0){
											$product_type = $importedProductData->product_type;
											$OptionValue = array();
											if($product_type == 'conf-simple'){
												$VariantMaster=$this->SellerProductModel->getVariantDetailsForProducts($ShopID,$importedProductData->id);
												// echo '<pre>';print_r($VariantMaster);

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
											$total_price = ($value->quantity * $value->price);


											/***** ----- Usha ----- *****/
											$tax_percent = 0;
											$tax_amount = 0;


											if($b2bCust->tax_exampted == 2 ){
												$tax_percent = $importedProductData->tax_percent;	//product table tax_percent [product_price = 100, tax_percent = 10%, tax_amount = 10, webshop_price=110]

												if($value->price > 0.00 && $discount_percent > 0.00) {
													$pro_price_excl_tax = $value->price - ($value->price * $discount_percent)/100;
												} else {
													$pro_price_excl_tax = $value->price;
												}

												if($tax_percent > 0.00 && $pro_price_excl_tax > 0.00) {
													$tax_amount = ($pro_price_excl_tax*$tax_percent)/100; //9

													$total_tax_amount = $total_tax_amount+($tax_amount*$value->quantity);
												}
											}
											/***** ----- Usha ----- *****/


											$insertOrderItem = array(
											   'order_id' 			=> $orderId,
											   'product_id'			=> $importedProductData->id,
											   'parent_product_id'	=> $importedProductData->parent_id,
											   'product_type' 		=> $importedProductData->product_type,
											   'product_name' 		=> $importedProductData->name,
											   'product_code' 		=> $importedProductData->product_code,
											   'sku'				=> $importedProductData->sku,
											   'barcode'			=> $importedProductData->barcode,
											   'product_variants'	=> $productVariants,
											   'qty_ordered' 		=> $value->quantity,
											   'price'   			=> $value->price,
											   'total_price'   		=> $total_price,
											   'tax_amount'         => $tax_amount, /***** ----- Usha ----- *****/
											   'tax_percent'        => $tax_percent, /***** ----- Usha ----- *****/
											   'created_at'   		=> strtotime(date('Y-m-d H:i:s')),
											   'created_by'   		=> $to_shop_id,
											   'ip'					=> $_SERVER['REMOTE_ADDR']
											);

											$this->SellerProductModel->insertData('b2b_order_items',$insertOrderItem);

											$this->SellerProductModel->decrementProductAvailableQty($importedProductData->id,$value->quantity);  //decrement available_qty
										}
									}

									/***** ----- Usha ----- *****/
									if($total_tax_amount > 0) {
										$grandtotal_updated = $grand_total+$total_tax_amount;
										$updatedata = array('tax_amount'=>$total_tax_amount, 'base_tax_amount'=>$total_tax_amount, 'grand_total'=>$grandtotal_updated,'base_grand_total'=>$grandtotal_updated);
										$where_arr=array('order_id'=>$orderId);
										$this->SellerProductModel->updateData('b2b_orders',$where_arr,$updatedata);

									}
									/***** ----- Usha ----- *****/

								}
							}
						}
					//}

				}

				/*-------------------------B2b Order End-----------------------------------*/

				}

				$updatedata = array('buyin_tax_amount'=>$total_tax_amount,'status'=>$status, 'updated_at'=>strtotime(date('Y-m-d H:i:s')), 'ip'=>$_SERVER['REMOTE_ADDR']);
				$where_arr=array('id'=>$applied_id);
				$this->ShopProductModel->updateData('b2b_orders_applied',$where_arr,$updatedata);

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

			echo json_encode(array("flag"=> 1,"msg" => "Updated Successfully"));
			exit;
		}else{
			echo json_encode(array("flag"=> 0,"msg" => "Id can not be blank"));
			exit;
		}
	}

	private function copyProductImage($val, string $shop_source_bucket, string $shop_upload_bucket): bool
	{
		foreach(['original', 'thumb', 'medium', 'large'] as $folder){
			$this->s3_filesystem->client->copy(
				$shop_source_bucket,
				'products/' . $folder . '/' . $val->image,
				$shop_upload_bucket,
				'products/' . $folder . '/' . $val->image,
			);
		}

		return true;
	}

}
