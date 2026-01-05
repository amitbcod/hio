<?php
class CronModel extends CI_Model
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

		//getSingleDataByID
		public function getSingleDataByID($tableName,$condition,$select)
		{
			if(!empty($select))
			{
				$this->seller_db->select($select);
			}
			$this->seller_db->where($condition);
			$query = $this->seller_db->get($tableName);
			return $query->row();
		}

	    //
		public function getOrderData($tableName,$condition,$select)
		{
		    if(!empty($select))
		    {
		      $this->seller_db->select($select);
		    }
		    $this->seller_db->where($condition);
		    $query = $this->seller_db->get($tableName);
		    //print_r($query);
		    return $query->result_array();
		}

		// order data by invoice type
		public function getOrderShopIdByInvoiceType($invoice_type)
		{
			$main_db_name=$this->db->database;
		    $this->seller_db->select('o.shop_id,bos.inv_daily_max_inv_amt,bos.inv_weekly_max_inv_amt,bos.inv_monthly_max_inv_amt');
			$this->seller_db->from('b2b_orders as o');
			$this->seller_db->join($main_db_name.'.fbc_users_shop as fus','o.shop_id = fus.shop_id','LEFT');
			$this->seller_db->join($main_db_name.'.fbc_users as fu','o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id','LEFT');
			$this->seller_db->join('b2b_customers_invoice as bos','fus.fbc_user_id = bos.customer_id','LEFT');
			// $query = $this->seller_db->get($tableName);
			$this->seller_db->where("bos.invoice_type",$invoice_type);
			$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0));
			$this->seller_db->group_by('o.shop_id');
			$query = $this->seller_db->get();
		    return $query->result_array();
		}

		// order data by shop id
		function getOrderDataByShopId($shopId,$invoice_type){
			//print_r($shopId);
			$main_db_name=$this->db->database;
		    $this->seller_db->select('o.*,c.invoice_type,c.payment_term,fu.fbc_user_id,IF(o.shipment_type=1,fu.owner_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name, fus.org_shop_name, fus.gst_no, fus.bill_address_line1, fus.bill_address_line2, fus.bill_city, fus.bill_state, fus.bill_country, fus.bill_pincode, fus.ship_address_line1, fus.ship_address_line2, fus.ship_city, fus.ship_state, fus.ship_country, fus.ship_pincode, fu.email, fus.company_name');
			$this->seller_db->from('b2b_orders as o');
			$this->seller_db->join($main_db_name.'.fbc_users_shop as fus','o.shop_id = fus.shop_id','LEFT');
			$this->seller_db->join($main_db_name.'.fbc_users as fu','o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id','LEFT');
			// $this->seller_db->join('b2b_customers_invoice as bos','fus.fbc_user_id = bos.customer_id','LEFT');
			$this->seller_db->join('b2b_customers_invoice as c','fus.fbc_user_id = c.customer_id','LEFT');
			// $query = $this->seller_db->get($tableName);
			$this->seller_db->where("c.invoice_type",$invoice_type);
			$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0,'o.shop_id' => $shopId));
			// $this->seller_db->group_by('o.shop_id');
			$query = $this->seller_db->get();
		    return $query->result_array();
		}

		// insert data
	  public function insertData($table,$data)
	  {
		 $this->seller_db->reset_query();

	    $this->seller_db->insert($table,$data);
	    if($this->seller_db->affected_rows() > 0)
	    {
			$last_insert_id=$this->seller_db->insert_id();
	      return $last_insert_id;
	    }
	    else
	    {
	      return false;
	    }
	  }

		// updated data

		public function updateData($tableName,$condition,$updateData)
	    {
			$this->seller_db->where($condition);

			  $this->seller_db->update($tableName,$updateData);
			  if($this->seller_db->affected_rows() > 0)
			  {

				return true;
			  }
			  else
			  {
				return false;
			  }

			  $this->seller_db->reset_query();
	    }

	    public function updateDataTesting($tableName,$condition,$updateData)
	    {
			  $this->seller_db->where($condition);

			  $query=$this->seller_db->update($tableName,$updateData);

			  if($this->seller_db->affected_rows() > 0)
			  {

				return true;
			  }
			  else
			  {
				return false;
			  }

			  $this->seller_db->reset_query();
	    }

	    // invoice data
		public function get_invoicedata_by_id($invoice_id)
		{
			$this->seller_db->select("*");
			$this->seller_db->from('invoicing');
			$this->seller_db->where('id',$invoice_id);
			$query = $this->seller_db->get();
			return $query->row();

		}

	// send invoice email
	public function sendInvoiceHTMLEmail($shopId,$EmailTo, $identifier, $TempVars, $DynamicVars,$CommonVars,$attachment){

		$webshop_smtp_host=$this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port=$this->getCustomVariableByIdentifier('smtp_port');
		$webshop_smtp_username=$this->getCustomVariableByIdentifier('smtp_username');
		$webshop_smtp_password=$this->getCustomVariableByIdentifier('smtp_password');
		$webshop_smtp_secure=$this->getCustomVariableByIdentifier('smtp_secure');

		/*$GlobalVar=$this->getCustomVariableByIdentifier('admin_email');
		if(isset($GlobalVar) && $GlobalVar->value!=''){
			$from_email=$GlobalVar->value;
		}else{*/
			$shop_id		=	$shopId;
			// $shop_id		=	$this->session->userdata('ShopID');
			$FBCData=$this->CommonModel->getShopOwnerData($shop_id);
			$from_email=$FBCData->email;
		//}

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

			if($templateId==21){
				if(isset($CommonVars) && $CommonVars!=''){
					$subject = str_replace('##INVOICENO##', $CommonVars[2], $subject);
					$subject = str_replace('##WEBSHOPNAME##', $CommonVars[1], $subject);
				}else{
					$subject = str_replace('##INVOICENO##', '', $subject);
					$subject = str_replace('##WEBSHOPNAME##', '', $subject);
				}
			}

			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);

			/*
			$data['title'] = $title;
			$data['subject'] = $subject;
			$data['content'] = $emailBody;

			$content = $this->load->view('email_template/email_content', $data, TRUE);
			*/

			$FinalContentBody=$HeaderPart.$emailBody.$FooterPart;

			if($this->CommonModel->sendHTMLMailSMTPAttchment($EmailTo, $subject, $FinalContentBody,$from_email, $attachment, $webshop_smtp_host->value, $webshop_smtp_port->value, $webshop_smtp_username->value, $webshop_smtp_password->value, $webshop_smtp_secure->value))
			{
				$this->email->clear(TRUE);
				return true;
			}else{

				return false;
			}

		}
	}

	// webshop
	public function sendInvoiceHTMLEmailWebshop($shopId,$EmailTo, $identifier, $TempVars, $DynamicVars,$CommonVars,$attachment){

		$webshop_smtp_host=$this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port=$this->getCustomVariableByIdentifier('smtp_port');
		$webshop_smtp_username=$this->getCustomVariableByIdentifier('smtp_username');
		$webshop_smtp_password=$this->getCustomVariableByIdentifier('smtp_password');
		$webshop_smtp_secure=$this->getCustomVariableByIdentifier('smtp_secure');

		// get invoice file name
		/*if($attachment){
			$invoiceData=$this->getInvoiceFileName($attachment);
			//echo $invoiceData->invoice_file;
		}*/



		//print_r($attachment);
		/*$GlobalVar=$this->getCustomVariableByIdentifier('admin_email');
		if(isset($GlobalVar) && $GlobalVar->value!=''){
			$from_email=$GlobalVar->value;
		}else{*/
			$shop_id		=	$shopId;
			// $shop_id		=	$this->session->userdata('ShopID');
			$FBCData=$this->CommonModel->getShopOwnerData($shop_id);
			$from_email=$FBCData->email;
		//}

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

			if($templateId==21){
				if(isset($CommonVars) && $CommonVars!=''){
					$subject = str_replace('##INVOICENO##', $CommonVars[2], $subject);
					$subject = str_replace('##WEBSHOPNAME##', $CommonVars[1], $subject);
				}else{
					$subject = str_replace('##INVOICENO##', '', $subject);
					$subject = str_replace('##WEBSHOPNAME##', '', $subject);
				}
			}

			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);

			/*
			$data['title'] = $title;
			$data['subject'] = $subject;
			$data['content'] = $emailBody;

			$content = $this->load->view('email_template/email_content', $data, TRUE);
			*/

			$FinalContentBody=$HeaderPart.$emailBody.$FooterPart;

			if($this->CommonModel->sendHTMLMailSMTPAttchment($EmailTo, $subject, $FinalContentBody,$from_email, $attachment, $webshop_smtp_host->value, $webshop_smtp_port->value, $webshop_smtp_username->value, $webshop_smtp_password->value, $webshop_smtp_secure->value))
			{
				$this->email->clear(TRUE);
				return true;
			}else{

				return false;
			}



		}
	}
	// end email

	public function getCustomVariableByIdentifier($identifier){
		$result = $this->seller_db->get_where('custom_variables',array('identifier'=>$identifier))->row();
		return $result;
	}

	// email required
	public function getEmailTemplateByIdentifier($identifier){
		$result = $this->seller_db->get_where('email_template',array('email_code'=>$identifier))->row();
		return $result;
	}

	// invoice file name
	public function getInvoiceFileName($invoiceID){
		$result = $this->seller_db->get_where('invoicing',array('id'=>$invoiceID))->row();
		return $result;
	}

	// order items data
	function getOrder_multi_Items($order_id)
	{
		$this->seller_db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type");
		$this->seller_db->from('b2b_order_items as oi');
		$this->seller_db->join('products_inventory as pi','oi.product_id = pi.product_id','LEFT');
		$this->seller_db->join('b2b_orders as o','oi.order_id = o.order_id','LEFT');
		$this->seller_db->where_in('oi.order_id',$order_id);
		$this->seller_db->order_by('oi.qty_scanned','asc');
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	}

	// webshop order data

	public function getOrderDataWebshop($invoice_type){
		$this->seller_db->select('o.customer_id,c.inv_daily_max_inv_amt,c.inv_weekly_max_inv_amt,c.inv_monthly_max_inv_amt');
		// $this->seller_db->select('o.customer_id');
		//$this->seller_db->select("o.*,c.invoice_type,c.invoice_to_type,c.alternative_email_id");
		$this->seller_db->from('sales_order as o');
		$this->seller_db->join('customers_invoice as c','o.customer_id = c.customer_id','LEFT');
		$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0));
		$this->seller_db->where("c.invoice_type",$invoice_type);
		$this->seller_db->group_by('o.customer_id');
		$this->seller_db->order_by('o.customer_id','desc');
		$query = $this->seller_db->get();
		return $query->result();
	}

	public function getOrderDataByCustomerId($customerIds,$invoice_type){
		$this->seller_db->select('o.*,c.invoice_type,c.invoice_to_type,c.payment_term,c.alternative_email_id,CONCAT(o.customer_firstname, " ", o.customer_lastname) as customer_name');
		$this->seller_db->from('sales_order as o');
		$this->seller_db->join('customers_invoice as c','o.customer_id = c.customer_id','LEFT');
		$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0,'o.customer_id' => $customerIds,'c.invoice_type' => $invoice_type));
		$query = $this->seller_db->get();
		// print_r($this->seller_db->last_query());
		return $query->result();
	}

	// order items data
	function getOrder_webshop_multi_Items($order_id)
	{
		/*$this->seller_db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type");
		$this->seller_db->from('sales_order_items as oi');
		$this->seller_db->join('products_inventory as pi','oi.product_id = pi.product_id','LEFT');
		$this->seller_db->join('sales_orders as o','oi.order_id = o.order_id','LEFT');
		$this->seller_db->where_in('oi.order_id',$order_id);
		$this->seller_db->order_by('oi.qty_scanned','asc');*/

		$this->seller_db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type,o.checkout_method, o.invoice_self, o.coupon_code as order_coupon_code");
		$this->seller_db->from('sales_order_items as oi');
		$this->seller_db->join('products_inventory as pi','oi.product_id = pi.product_id','LEFT');
		$this->seller_db->join('sales_order as o','oi.order_id = o.order_id','LEFT');
		$this->seller_db->where("oi.product_inv_type IN ('buy','virtual')");  //adde by al later
		//$this->seller_db->where("pi.qty>0");								  //adde by al later
		// $this->seller_db->where('oi.order_id',$order_id);
		$this->seller_db->where_in('oi.order_id',$order_id);
		$this->seller_db->order_by('oi.qty_scanned','asc');
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	}

	// order data by invoice type weekly
		public function getOrderShopIdBy_Invoice_Type($invoice_type,$dateCondition)
		{
			//print_r($dateCondition);
			$main_db_name=$this->db->database;
		    $this->seller_db->select('o.shop_id,bos.inv_daily_max_inv_amt,bos.inv_weekly_max_inv_amt,bos.inv_monthly_max_inv_amt');
			$this->seller_db->from('b2b_orders as o');
			$this->seller_db->join($main_db_name.'.fbc_users_shop as fus','o.shop_id = fus.shop_id','LEFT');
			$this->seller_db->join($main_db_name.'.fbc_users as fu','o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id','LEFT');
			$this->seller_db->join('b2b_customers_invoice as bos','fus.fbc_user_id = bos.customer_id','LEFT');
			// $query = $this->seller_db->get($tableName);
			$this->seller_db->where("bos.invoice_type",$invoice_type);
			$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0));
			$this->seller_db->where($dateCondition);
			$this->seller_db->group_by('o.shop_id');
			$query = $this->seller_db->get();
		    return $query->result_array();
		}

	// weekly

		// order data by shop id
		function getOrderData_By_ShopId($shopId,$invoice_type,$weekly_condition){
			//print_r($shopId);
			$main_db_name=$this->db->database;
		    $this->seller_db->select('o.*,c.invoice_type,c.payment_term,fu.fbc_user_id,IF(o.shipment_type=1,fu.owner_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name, fus.org_shop_name, fus.gst_no, fus.bill_address_line1, fus.bill_address_line2, fus.bill_city, fus.bill_state, fus.bill_country, fus.bill_pincode, fus.ship_address_line1, fus.ship_address_line2, fus.ship_city, fus.ship_state, fus.ship_country, fus.ship_pincode, fu.email, fus.company_name');
			$this->seller_db->from('b2b_orders as o');
			$this->seller_db->join($main_db_name.'.fbc_users_shop as fus','o.shop_id = fus.shop_id','LEFT');
			$this->seller_db->join($main_db_name.'.fbc_users as fu','o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id','LEFT');
			// $this->seller_db->join('b2b_customers_invoice as bos','fus.fbc_user_id = bos.customer_id','LEFT');
			$this->seller_db->join('b2b_customers_invoice as c','fus.fbc_user_id = c.customer_id','LEFT');
			// $query = $this->seller_db->get($tableName);
			$this->seller_db->where("c.invoice_type",$invoice_type);
			$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0,'o.shop_id' => $shopId));
			$this->seller_db->where($weekly_condition);
			// $this->seller_db->group_by('o.shop_id');
			$query = $this->seller_db->get();
		    return $query->result_array();
		}

		// webshop order data

		public function getOrderData_Webshop($invoice_type,$weekly_condition){ //weekly
			$this->seller_db->select('o.customer_id,c.inv_daily_max_inv_amt,c.inv_weekly_max_inv_amt,c.inv_monthly_max_inv_amt');
			//$this->seller_db->select("o.*,c.invoice_type,c.invoice_to_type,c.alternative_email_id");
			$this->seller_db->from('sales_order as o');
			$this->seller_db->join('customers_invoice as c','o.customer_id = c.customer_id','LEFT');
			$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0));
			$this->seller_db->where("c.invoice_type",$invoice_type);
			$this->seller_db->where($weekly_condition);
			$this->seller_db->group_by('o.customer_id');
			$this->seller_db->order_by('o.customer_id','desc');
			$query = $this->seller_db->get();
			return $query->result();
		}

		// weekly
		public function getOrderDataBy_CustomerId($customerIds,$invoice_type,$dateCondition){
			$this->seller_db->select('o.*,c.invoice_type,c.invoice_to_type,c.payment_term,c.alternative_email_id,CONCAT(o.customer_firstname, " ", o.customer_lastname) as customer_name');
			$this->seller_db->from('sales_order as o');
			$this->seller_db->join('customers_invoice as c','o.customer_id = c.customer_id','LEFT');
			$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0,'o.customer_id' => $customerIds,'c.invoice_type' => $invoice_type));
			$this->seller_db->where($dateCondition);
			$query = $this->seller_db->get();
			return $query->result();
		}
}
