<?php
class CronOutOfStockModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		// $fbc_user_id	=	$this->session->userdata('ShopOwnerId');  //old LoginID
		// $shop_id		=	$this->session->userdata('ShopID');

		// $FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('fbc_user_id'=>$fbc_user_id),'shop_id,fbc_user_id,database_name');
		// if(isset($FBCData) && $FBCData->database_name!='')
		// {
		// 	$fbc_user_database=$FBCData->database_name;

		// 	$this->load->database();
		// 	$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
		// 	$this->seller_db = $this->load->database($config_app,TRUE);
		// 	if($this->seller_db->conn_id) {
		// 		//do something
		// 	} else {
		// 		redirect(base_url());
		// 	}
		// }else{
		// 	redirect(base_url());
		// }
	}

	public function get_all_products()
	{
		$this->seller_db->select('p.id,p.product_type,p.product_inv_type,p.remove_flag,p.status,p.shop_id,p.coming_soon_flag');
		$this->seller_db->from('products as p');
		$this->seller_db->where('p.product_type <>','conf-simple');
		$this->seller_db->where('p.remove_flag','0');
		$query = $this->seller_db->get();
		$resultArr = $query->result_array();
		// echo $this->seller_db->last_query();exit;
		return $resultArr;
	}

	public function getAvailableInventory($product_id, $shopcode, $seller_shopcode='')
  	{
  		$shop_db =  DB_NAME_PREFIX.$shopcode; // constant variable
  		// $main_db = DB_NAME; //Constant variable

		$sql = "SELECT $shop_db.products_inventory.* FROM $shop_db.products_inventory WHERE $shop_db.products_inventory.product_id = ".$product_id."";
		 $query= $this->seller_db->query($sql);
		 $inventory = $query->row();
		// echo $this->seller_db->last_query();exit;


			if (isset($inventory) &&  $inventory !=''){
				$Product=$this->getproductDetailsByShopCode($shopcode,$product_id);//

				$product_inv_type=$Product->product_inv_type;

				if(($product_inv_type=='dropship')  || ($product_inv_type=='virtual' && $inventory->available_qty <= 0)){
					$shop_product_id=$Product->shop_product_id;

					if($shop_product_id>0 && $shop_product_id !=''){
						$seller_db =  DB_NAME_PREFIX.$seller_shopcode;
						// $new_param=array($shop_product_id);  //updated by al
						$sql1 = "SELECT $seller_db.products_inventory.* FROM $seller_db.products_inventory WHERE $seller_db.products_inventory.product_id = ".$shop_product_id."";

						$query1 = $this->seller_db->query($sql1);//,$new_param

						$inventory1 = $query1->row();


							if (isset($inventory1) &&  $inventory1 !=''){
								return $inventory1;  // return result
							}else{
								return false;
							}

					}else{
						return false;
					}

				}else{

					return $inventory; // return result
				}
			}else{
				return false;
			}

  	}


  	public function getproductDetailsByShopCode($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_PREFIX.$shopcode; // constant variable

		$sql = "SELECT $shop_db.products.* FROM $shop_db.products WHERE $shop_db.products.id = ".$product_id."";

  		$query = $this->seller_db->query($sql);
  		$product_detail = $query->row();

			if (isset($product_detail) && $product_detail !=''){
				return $product_detail;
			}else{
				return false;
			}


  	}

  	public function configurableProduct($shopcode,$shopid,$product_id)
  	{
  		$shop_db =  DB_NAME_PREFIX.$shopcode; // constant variable

		$sql = "SELECT $shop_db.products.* FROM $shop_db.products WHERE $shop_db.products.parent_id = ".$product_id."";

  		$query =  $this->seller_db->query($sql);
  		// echo $this->seller_db->last_query();exit;
  		$config_product = $query->result();

			if (isset($config_product) && $config_product !=''){
				return $config_product;
			}else{
				return false;
			}


  	}

  	public function get_all_products_with_comingsoon()
	{
		$this->seller_db->select('p.id,p.product_type,p.product_inv_type,p.status,p.shop_id,p.coming_soon_flag,p.base_image as product_img,p.url_key,p.name as product_name');
		$this->seller_db->from('products as p');
		$this->seller_db->where('p.coming_soon_flag', 1);
		$this->seller_db->where('p.product_type <>','conf-simple');
		$this->seller_db->where('p.remove_flag','0');
		$query = $this->seller_db->get();
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function getCustomerDetail($product_id){
		$this->seller_db->select('p.id,p.email_id,p.customer_id,c.first_name,c.last_name');
		$this->seller_db->from('products_keep_me_notify p');
		$this->seller_db->join('customers c','p.customer_id = c.id', 'left');
		$this->seller_db->where('p.product_id', $product_id);
		$this->seller_db->where('p.notify_email_sent', 0);
		$query = $this->seller_db->get();
		$resultArr = $query->result();
		return $resultArr;
	}

	public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars,$SubDynamic='',$CommonVars = '',$shopcode = ''){

		$webshop_smtp_host=$this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port=$this->getCustomVariableByIdentifier('smtp_port');
		$webshop_smtp_username=$this->getCustomVariableByIdentifier('smtp_username');
		$webshop_smtp_password=$this->getCustomVariableByIdentifier('smtp_password');
		$webshop_smtp_secure=$this->getCustomVariableByIdentifier('smtp_secure');


		$GlobalVar=$this->getCustomVariableByIdentifier('admin_email');
		if(isset($GlobalVar) && $GlobalVar->value!=''){
			$from_email=$GlobalVar->value;
		}else{
			$shop_id		=	$shopcode;
			$FBCData=$this->CommonModel->getShopOwnerData($shop_id);
			$from_email=$FBCData->email;
		}

		$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);


		if(isset($emailTemplate) && $emailTemplate->id!='')
		{

			$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header');
			$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer');


			$HeaderPart=$emailHeaderTemplate->content;
			$FooterPart=$emailFooterTemplate->content;
			if(isset($CommonVars) && $CommonVars!='')
			{
				$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
				$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);

			}

			$templateId=$emailTemplate->id;

			$subject = $emailTemplate->subject;

			$title = $emailTemplate->title;

			if(isset($SubDynamic) && $SubDynamic!=''){
				$subject = str_replace('##PRODUCTNAME##', $SubDynamic, $subject);

			}else{
				$subject = str_replace('##PRODUCTNAME##', '', $subject);

			}

			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);

//			$FinalContentBody=$HeaderPart.$emailBody.$FooterPart;
			$FinalContentBody=$emailBody;

			if($this->CommonModel->sendHTMLMailSMTP($EmailTo, $subject, $FinalContentBody,$from_email, $attachment="",$webshop_smtp_host->value, $webshop_smtp_port->value, $webshop_smtp_username->value, $webshop_smtp_password->value, $webshop_smtp_secure->value))
			{
				return true;
			}else{

				return false;
			}

		}
	}

	public function getCustomVariableByIdentifier($identifier){

		$result = $this->seller_db->get_where('custom_variables',array('identifier'=>$identifier))->row();

		return $result;

	}

	public function getEmailTemplateByIdentifier($identifier){

		$result = $this->seller_db->get_where('email_template',array('email_code'=>$identifier))->row();

		return $result;

	}



}
