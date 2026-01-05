<?php
defined('BASEPATH') or exit('No direct script access allowed');
class CronOutOfStockController  extends CI_Controller
{
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	 function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('CronOutOfStockModel');
	}


	public function outofstockcheck()
	{
		$data['PageTitle']='Warehouse - Manual Out Of Stock Check';
		$data['side_menu']='bulk-add';
		$shop_id=$this->uri->segment(3);



		$shopData=$this->CommonModel->getShopOwnerData($shop_id);
		if(isset($shopData))
		{
			$shop_database_name=$shopData->database_name;
			$shop_fbc_user_id=$shopData->fbc_user_id;

			if(isset($shop_database_name) && $shop_database_name!='')
			{
				$this->load->database();
				$config_app = fbc_switch_db_dynamic(DB_PREFIX.$shop_database_name);
				$this->seller_db = $this->load->database($config_app,TRUE);
				if($this->seller_db->conn_id) {
					//do something
				} else {
					redirect(base_url());
				}
			}else{
				redirect(base_url());
			}

		}

		$data['customVariable_out_of_stock'] = $customVariable_out_of_stock=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'out_of_stock'),'value');

		if($customVariable_out_of_stock->value == 'no')
		{
		//check constants
			$shopcode = $shop_id;

			$product_listing = $this->CronOutOfStockModel->get_all_products();

			if($product_listing == false)
			{
				$error='New product not available';
				$redirect = base_url('seller/product/bulk-add');
				echo json_encode(array('flag' => 1, 'msg' => $error,'redirect'=>$redirect));
				exit();
			}else
			{
				foreach($product_listing as $value)
				{
					$InvFlag = 0;
						if($value['product_type'] == 'simple')
						{
							if($value['product_inv_type'] == 'buy'){
								$product_inv = $this->CronOutOfStockModel->getAvailableInventory($value['id'],$shopcode);
								if(is_numeric($product_inv->available_qty)){
									if($product_inv->available_qty > 0){
										$InvFlag = 1;
									}
								}

							}else if($value['product_inv_type'] == 'virtual'){
								// $seller_shopcode = 'shop'.$value['shop_id'];
								$seller_shopcode = $value['shop_id'];
								$product_inv1 = $this->CronOutOfStockModel->getAvailableInventory($value['id'],$shopcode,$seller_shopcode);
								if($product_inv1->available_qty > 0){
									// $value['stock_status'] = 'Instock';
									$InvFlag = 1;
								}
							}else if($value['product_inv_type'] == 'dropship'){
								$seller_shopcode = $value['shop_id'];
								//$product_inv2 = $webshop_obj->getAvailableInventory($value['shop_product_id'],$seller_shopcode);
								$product_inv2 = $this->CronOutOfStockModel->getAvailableInventory($value['id'],$shopcode,$seller_shopcode);
								if($product_inv2->available_qty > 0){
									$InvFlag = 1;
								}
							}

							if($InvFlag == 1)
							{
								$updateData=array(
								'status' => 1,
								// 'updated_at' => strtotime(date('Y-m-d H:i:s'))
								);

								$this->seller_db->where(array('id'=>$value['id']));
								$rowAffected = $this->seller_db->update('products', $updateData);

							} else
							{
								$status = ($value['coming_soon_flag']==1) ? 1 : 2 ;
								$updateData=array(
								'status' => $status,
								// 'updated_at' => strtotime(date('Y-m-d H:i:s'))
								);
								$this->seller_db->where(array('id'=>$value['id']));
								$rowAffected = $this->seller_db->update('products', $updateData);
							}

						}else
						{
							$configProduct = $this->CronOutOfStockModel->configurableProduct($shopcode,$shop_id,$value['id']);
							if($configProduct!=false && isset($configProduct))
							{
								foreach ($configProduct as $conf)
								{
									if($conf->product_inv_type == 'buy'){

										$product_inv = $this->CronOutOfStockModel->getAvailableInventory($conf->id,$shopcode);

										if(is_numeric($product_inv->available_qty)){
											if($product_inv->available_qty > 0){
												$InvFlag = 1;
												break;
											}
										}
									}else if($conf->product_inv_type == 'virtual'){

										$seller_shopcode = $conf->shop_id;

										$product_inv1 = $this->CronOutOfStockModel->getAvailableInventory($conf->id,$shopcode,$seller_shopcode);
										if(is_numeric($product_inv1->available_qty)){
											if($product_inv1->available_qty > 0){
												$InvFlag = 1;
												break;
											}
										}

									}else if($conf->product_inv_type == 'dropship'){
										// $seller_shopcode = 'shop'.$conf['shop_id'];
										$seller_shopcode = $conf->shop_id;
										//$product_inv2 = $webshop_obj->getAvailableInventory($conf['shop_product_id'],$seller_shopcode);
										$product_inv2 = $this->CronOutOfStockModel->getAvailableInventory($conf->id,$shopcode,$seller_shopcode);
										if(is_numeric($product_inv2->available_qty)){
											if($product_inv2->available_qty > 0){
												$InvFlag = 1;
												break;
											}
										}
									}

								}

								if($InvFlag == 1) {

									$updateData=array(
									'status' => 1,
									//'updated_at' => strtotime(date('Y-m-d H:i:s'))
									);
									$this->seller_db->where(array('id'=>$value['id']));
									$this->seller_db->or_where(array('parent_id'=>$value['id']));
									$rowAffected = $this->seller_db->update('products', $updateData);
								} else {
									$status = ($value['coming_soon_flag']==1) ? 1 : 2 ;
									$updateData=array(
									'status' => $status,
									//'updated_at' => strtotime(date('Y-m-d H:i:s'))
									);
									$this->seller_db->where(array('id'=>$value['id']));
									$this->seller_db->or_where(array('parent_id'=>$value['id']));
									$rowAffected = $this->seller_db->update('products', $updateData);
								}

							}else{
									$status = ($value['coming_soon_flag']==1) ? 1 : 2 ;
									$updateData=array(
									'status' => $status,
									// 'updated_at' => strtotime(date('Y-m-d H:i:s'))
									);
									$this->seller_db->where(array('id'=>$value['id']));
									$rowAffected = $this->seller_db->update('products', $updateData);
								}
						}
				}
			}
			if(isset($rowAffected))
			{
				$redirect = base_url('seller/product/bulk-add');
				echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated ",'redirect'=>$redirect));
				exit();
			}else{
				echo json_encode(array('flag' => 0, 'msg' => "Nothing to update!"));
				exit;
			}

		}else{
			echo json_encode(array('flag' => 0, 'msg' => "Since the OutOfStock Products are allowed to display on frontend, No permission to run this Cron."));
			exit;
		}

	}

	public function comingsoonitemsinstock(){

		$shop_id=$this->uri->segment(3);

		$shopData=$this->CommonModel->getShopOwnerData($shop_id);
		if(isset($shopData))
		{
			$shop_database_name=$shopData->database_name;
			$shop_fbc_user_id=$shopData->fbc_user_id;

			if(isset($shop_database_name) && $shop_database_name!='')
			{
				$this->load->database();
				$config_app = fbc_switch_db_dynamic(DB_PREFIX.$shop_database_name);
				$this->seller_db = $this->load->database($config_app,TRUE);
				if($this->seller_db->conn_id) {
					//do something
				} else {
					redirect(base_url());
				}
			}else{
				redirect(base_url());
			}

		}

		$shopcode = $shop_id;

		$product_listing = $this->CronOutOfStockModel->get_all_products_with_comingsoon();

		if($product_listing == false)
		{
			$error='Coming Soon product not available';
			echo json_encode(array('flag' => 0, 'msg' => $error));
			exit();
		}
		else{
			$ComingSoonUpdateFlag = 0;
			foreach($product_listing as $value){

				$InvFlag = 0;
						if($value['product_type'] == 'simple')
						{
							if($value['product_inv_type'] == 'buy'){
								$product_inv = $this->CronOutOfStockModel->getAvailableInventory($value['id'],$shopcode);
								if(is_numeric($product_inv->available_qty)){
									if($product_inv->available_qty > 0){
										$InvFlag = 1;
									}
								}

							}else if($value['product_inv_type'] == 'virtual'){
								$seller_shopcode = $value['shop_id'];
								$product_inv1 = $this->CronOutOfStockModel->getAvailableInventory($value['id'],$shopcode,$seller_shopcode);
								if($product_inv1->available_qty > 0){
									$InvFlag = 1;
								}
							}else if($value['product_inv_type'] == 'dropship'){
								$seller_shopcode = $value['shop_id'];
								$product_inv2 = $this->CronOutOfStockModel->getAvailableInventory($value['id'],$shopcode,$seller_shopcode);
								if($product_inv2->available_qty > 0){
									$InvFlag = 1;
								}
							}

							if($InvFlag == 1)
							{
								$rowAffected = $this->comingsoonemail($shopcode,$value);

								$updateData=array(
										'coming_soon_flag' => 0,
									);
								$this->seller_db->where(array('id'=>$value['id']));
								$this->seller_db->update('products', $updateData);

								$ComingSoonUpdateFlag = 1;
							}

						}
						else{

							$configProduct = $this->CronOutOfStockModel->configurableProduct($shopcode,$shop_id,$value['id']);

							if($configProduct!=false && isset($configProduct))
							{
								foreach ($configProduct as $conf)
								{
									if($conf->product_inv_type == 'buy'){

										$product_inv = $this->CronOutOfStockModel->getAvailableInventory($conf->id,$shopcode);

										if(is_numeric($product_inv->available_qty)){
											if($product_inv->available_qty > 0){
												$InvFlag = 1;
												break;
											}
										}
									}else if($conf->product_inv_type == 'virtual'){

										$seller_shopcode = $conf->shop_id;

										$product_inv1 = $this->CronOutOfStockModel->getAvailableInventory($conf->id,$shopcode,$seller_shopcode);

										if($product_inv1->available_qty > 0){
											$InvFlag = 1;
											break;
										}

									}else if($conf->product_inv_type == 'dropship'){

										$seller_shopcode = $conf->shop_id;
										$product_inv2 = $this->CronOutOfStockModel->getAvailableInventory($conf->id,$shopcode,$seller_shopcode);

										if($product_inv2->available_qty > 0){
											$InvFlag = 1;
											break;
										}
									}
								}

								if($InvFlag == 1)
								{
									$rowAffected = $this->comingsoonemail($shopcode,$value);

									$updateData=array(
										'coming_soon_flag' => 0,
									);
									$this->seller_db->where(array('id'=>$value['id']));
									$this->seller_db->update('products', $updateData);

									$ComingSoonUpdateFlag = 1;
								}

							}


						}
			}
			if(isset($ComingSoonUpdateFlag) && $ComingSoonUpdateFlag == 1 )
			{
				echo json_encode(array('flag' => 1, 'msg' => "Action done successfully"));
				exit();
			}else{
				echo json_encode(array('flag' => 0, 'msg' => "Nothing to update!"));
				exit;
			}

		}

	}

	public function comingsoonemail($shopcode,$product_value){

		$notify_email_sent = 0;

		$get_customer_data = $this->CronOutOfStockModel->getCustomerDetail($product_value['id']);
		if(isset($get_customer_data) && !empty($get_customer_data))	{

			$templateId ='coming_soon_items_in_stock';

			$shop_owner=$this->CommonModel->getShopOwnerData($shopcode);
			$webshop_details=$this->CommonModel->get_webshop_details($shopcode);

			$shop_name=$shop_owner->org_shop_name;
			$owner_email=$shop_owner->email;

			$baseurl= base_url();
			$website_url = getWebsiteUrl($shopcode,$baseurl);

			$site_logo = '';
			if(isset($webshop_details)){
				$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
			}
			else{
				$shop_logo = '';
			}

			// Temporarily set session for ShopID  as WebshopModel is using it (used to get S3 url)
			$this->session->set_userdata('ShopID', $shopcode);

			$shop_logo = getWebSiteLogo($shopcode,$shop_logo);
			$site_logo =  '<a href="'.$website_url.'" style="color:#1E7EC8;">
				<img alt="'.$shop_name.'" border="0" src="'.$shop_logo.'" style="max-width:200px" />
			</a>';

			$TempVars = array('##CUSTOMERNAME##' ,'##PRODUCTNAME##','##WEBSHOPNAME##','##PRODUCTIMAGE##','##PRODUCTURL##');

			$CommonVars=array($site_logo, $shop_name);


			if(isset($templateId)) {

				$emailSendStatusFlag=$this->CommonModel->sendEmailStatus($templateId,$shopcode);
				if($emailSendStatusFlag==1){

					$product_name = $product_value['product_name'];
					$product_id = $product_value['id'];
					$product_url = $website_url.'/product-detail/'.$product_value['url_key'];
					$product_img =  get_s3_url('products/large/'.$product_value['product_img'],$shopcode);
//					$product_img = '<a href= "' .$product_url.'"> <img alt="'.$product_name.'" src="'.$productimg.'"/> </a>';
					$webshop_name = $shop_name;

					foreach($get_customer_data as $data) {

						$to = $data->email_id;
						$username =  $data->first_name != '' ? $data->first_name .' '. $data->last_name : '';

						$DynamicVars = array($username,$product_name,$webshop_name, $product_img ,$product_url);
						$mailSent = $this->CronOutOfStockModel->sendCommonHTMLEmail($to,$templateId,$TempVars,$DynamicVars,$product_name,$CommonVars,$shopcode);

						if($mailSent == true){
							$notify_email_sent = 1;

							$updateData=array(
								'notify_email_sent' => 1,
							);
							$this->seller_db->where(array('id'=>$data->id));
							$rowAffected = $this->seller_db->update('products_keep_me_notify', $updateData);

						}
					}
				}
			}

		}

		return $notify_email_sent;
	}
}
