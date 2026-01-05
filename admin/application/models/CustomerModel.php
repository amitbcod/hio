<?php
class CustomerModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function add_customer_type($insert_array)
	{
		 $this->db->insert('customers_type_master',$insert_array);
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
	}
	
	public function get_customer_type_details()
	{		
		$this->db->order_by("id", "ASC");
		$query = $this->db->get('customers_type_master');
	    return $query->result_array();
			if ($query->num_rows() > 0)
		   {
				$result = $query->result_array();
				return $result;
		   }
		   else{
			   return false;
		   }
	}

	public function get_customer_count($customer_type_id)
	{
		$this->db->where('customer_type_id',$customer_type_id);
		$query = $this->db->get('customers');

	    //return $query->result_array();
			if ($query->num_rows() > 0)
		   {
				$result = $query->num_rows();
				return $result;
		   }
		   else{
			   return false;
		   }
	}

	public function get_customer_details_by_email($email_id)
	{
		$this->db->where('email_id',$email_id);
		$query = $this->db->get('customers');

	    //return $query->result_array();
			if ($query->num_rows() > 0)
		   {
				$result = $query->row_array();
				return $result;
		   }
		   else{
			   return false;
		   }
	}

	public function get_all_customer_by_type($customer_type_id)
	{
		$this->db->select('customers.*, customers_address.city, customers_address.state,customers_address.mobile_no, sales_order.*');
	  	$this->db->from('customers','customers_address','sales_order');
		$this->db->where('customers.status',1);
		$this->db->where('customer_type_id',$customer_type_id);
		$this->db->join('customers_address','customers_address.customer_id = customers.id','LEFT');
		$this->db->join('sales_order','sales_order.customer_id = customers.id','LEFT');
		$this->db->order_by("sales_order.created_at", "desc");
		$this->db->group_by('customers.id');
		$query = $this->db->get();
		// $query = $this->db->get('customers');
			if ($query->num_rows() > 0)
		   {
				$result = $query->result_array();
				return $result;
		   }
		   else{
			   return false;
		   }
	}

	 public function get_single_customer_type_details($id)
	 {
		 $this->db->where('id',$id);
		 $query = $this->db->get('customers_type_master');
			if ($query->num_rows() > 0)
		   {
				$result = $query->row_array();
				return $result;
		   }
		   else{
			   return false;
		   }
		 
	 }
	  public function update_customer_type($update_array,$id)
	  {
		  $this->db->where('id',$id);
		  $query = $this->db->update('customers_type_master',$update_array);
		  // echo $this->db->last_query();
		  if ($query)
		   {
				
				return true;
		   }
		   else{
			   return false;
		   }
	  }
	  //***
	  public function get_all_customers($search_param='')
	  { 	
	  	$this->db->select('customers.*, customers_address.city, customers_address.state,customers_address.mobile_no, sales_order.*');
	  	$this->db->from('customers','customers_address','sales_order');
		$this->db->where('customers.status',1);
		$this->db->join('customers_address','customers_address.customer_id = customers.id','LEFT');
		$this->db->join('sales_order','sales_order.customer_id = customers.id','LEFT');
		// $this->db->where('sales_order.status',1);
		// $this->db->where('sales_order.status !=',3);
		// $this->db->where('sales_order.status !=',7);
		// $this->db->where('sales_order.parent_id ',0);
		// $this->db->where('sales_order.main_parent_id',0);
		// $this->db->where('customers_address.is_default',1);
		//$this->db->where('customers_address.remove_flag',0);
		/*$this->db->where('customers_address.remove_flag = 0');
		$this->db->or_where('customers_address.remove_flag IS NULL');*/
		//$this->db->where('customers_address.remove_flag !=1');
		$this->db->where('customers_address.remove_flag = 0 OR customers_address.remove_flag IS NULL');//old
		if(isset($search_param['keyword']) && $search_param['keyword'] !="")
		{
			$this->db->group_start();
			$this->db->like('customers.first_name',$search_param['keyword']);
			$this->db->or_like('customers.last_name',$search_param['keyword']);
			if(isset($search_param['keyword']))
			{
			  		$fullname= explode(" ",$search_param['keyword']);
			  		$fname= $fullname[0];	
			 $this->db->or_like('customers.first_name',$fname);
			  		if(isset($fullname[1]))
			  		{
			  			$lname= $fullname[1];
			$this->db->or_like('customers.first_name',$lname);
			  		}

			}
			$this->db->or_like('customers.email_id',$search_param['keyword']);
			$this->db->group_end();

		}
		$this->db->order_by("sales_order.created_at", "desc");
		$this->db->group_by('customers.id');
		$query = $this->db->get();
		
		$resultArr = $query->result_array();
		// echo $this->db->last_query();
		// die();
		return $resultArr;
	  }
	  public function get_single_customer_details($customer_id)
	  {
	  	$this->db->select('customers.*,customers_address.address_line1,customers_address.address_line2,customers_address.pincode,customers_address.city,customers_address.is_default,customers_address.state,customers_address.mobile_no,country_master.country_name, sales_order.*');
	  	$this->db->from('customers','customers_address','sales_order');
	  	$this->db->where('customers.id',$customer_id);
		$this->db->where('customers.status',1);
		$this->db->join('customers_address','customers_address.customer_id = '.$customer_id);
		$this->db->join('sales_order','sales_order.customer_id = '.$customer_id);
		$this->db->join('country_master','country_master.country_code = customers_address.country');
		$this->db->where('sales_order.status',1);
		$this->db->where('sales_order.status !=',3);
		$this->db->where('sales_order.status !=',7);
		$this->db->where('sales_order.parent_id ',0);
		$this->db->where('sales_order.main_parent_id',0);
		// $this->db->where('customers_address.is_default',1);
		
		$query = $this->db->get();
		$resultArr = $query->result_array();
		// echo $this->db->last_query();
		return $resultArr;

	  }

	 //  public function get_customer_details($customer_id)
	 //  {
	 //  	$this->db->select('customers.*,customers_address.address_line1,customers_address.address_line2,customers_address.pincode,customers_address.city,customers_address.is_default,customers_address.state,customers_address.mobile_no,country_master.country_name, sales_order.*');
	 //  	$this->db->from('customers','customers_address','sales_order');
	 //  	$this->db->where('customers.id',$customer_id);
		// $this->db->where('customers.status',1);
		// $this->db->join('customers_address','customers_address.customer_id = '.$customer_id,'LEFT');
		// $this->db->join('sales_order','sales_order.customer_id = '.$customer_id,'LEFT');
		// $this->db->join('country_master','country_master.country_code = customers_address.country','LEFT');
		// // $this->db->where('sales_order.status',1);
		// // $this->db->where('sales_order.status !=',3);
		// // $this->db->where('sales_order.status !=',7);
		// // $this->db->where('sales_order.parent_id ',0);
		// // $this->db->where('sales_order.main_parent_id',0);
		// // $this->db->where('customers_address.is_default',1);
		// $this->db->where('customers_address.remove_flag',0);
		// $query = $this->db->get();
		// $resultArr = $query->result_array();
		// // echo $this->db->last_query();
		// return $resultArr;

	 //  }

	  	public function get_customer_details($customer_id)
	  {
	  	$this->db->select('customers.*');
	  	$this->db->from('customers');
	  	$this->db->where('customers.id',$customer_id);
		$this->db->where('customers.status',1);
		$query = $this->db->get();
		$resultArr = $query->result_array();
		// echo $this->db->last_query();
		return $resultArr;

	  }

	   public function update_customer_detail($update_array,$id)
	  {
		  $this->db->where('id',$id);
		  $query = $this->db->update('customers',$update_array);
		  // echo $this->db->last_query();
		  if ($query)
		   {
				
				return true;
		   }
		   else{
			   return false;
		   }
	  }

	  public function update_customer_address($update_array,$customer_id,$address_id)
	  {
		  $this->db->where('customer_id',$customer_id);
		  $this->db->where('id',$address_id);
		  $query = $this->db->update('customers_address',$update_array);
		  // echo $this->db->last_query();
		  if ($query)
		   {
				
				return true;
		   }
		   else{
			   return false;
		   }
	  }

	  public function update_customer_deault_billaddr($update_add,$customer_id)
	  {
		  $this->db->where('customer_id',$customer_id);
		  $this->db->where('is_default', '1');
		  $query = $this->db->update('customers_address',$update_add);
		  // echo $this->db->last_query();
		  if ($query)
		   {
				
				return true;
		   }
		   else{
			   return false;
		   }
	  }

	  public function get_Customer_order_details($customer_id)
	  {
	  	$this->db->select('sales_order.*, sop.payment_method, sop.payment_method_name, iv.invoice_no');
	  	$this->db->where('sales_order.customer_id',$customer_id);
	  	$this->db->where('sales_order.status !=', 7);
	  	$this->db->from('sales_order');
	  	$this->db->join('sales_order_payment as sop','sales_order.order_id = sop.order_id','LEFT');
	  	$this->db->join('invoicing as iv','sales_order.invoice_id = iv.id','LEFT');
	  	$query = $this->db->get();
		
		$resultArr = $query->result_array();
		// echo $this->db->last_query();
		return $resultArr;
	  }
	 //***

	  public function get_all_salesrule_by_cust_type($customer_type_id)
	{
		$this->db->select('salesrule.*,src.coupon_id, src.coupon_code');
	  	$this->db->from('salesrule');
		$this->db->where('FIND_IN_SET('.$customer_type_id.', salesrule.apply_to)');
		// $this->db->where('customer_type_id',$customer_type_id);
		$this->db->join('salesrule_coupon as src','src.rule_id = salesrule.rule_id','LEFT');
		// $this->db->join('sales_order','sales_order.customer_id = customers.id','LEFT');
		// $this->db->order_by("sales_order.created_at", "desc");
		$this->db->group_by('src.coupon_id');
		$query = $this->db->get();
		// echo $this->db->last_query();
		// $query = $this->db->get('customers');
			if ($query->num_rows() > 0)
		   {
				$result = $query->result_array();
				return $result;
		   }
		   else{
			   return false;
		   }
	}
	  //**


	public function getInvoiceBywebshopCustomerId($custmer_id){
		$db= $this->db->database;
		$this->db->select('id');	
		$this->db->from('customers_invoice');
		$this->db->where(array('customer_id' => $custmer_id)); 
		$query = $this->db->get();
		$Result = $query->row();	
		return $Result;
	}

	// customer invoice by shop id
	 function getinvoicesbycustomerId($customerId)
	 {
	 	$this->db->select('*');	
		$this->db->from('customers_invoice');
		$this->db->where(array('customer_id' => $customerId)); 
		$query = $this->db->get();
		$Result = $query->row();		
		// $Result = $query->result();	
		return $Result;		
	 }

	
	function  get_Customer_AddressBook($customerId){

		$this->db->select('customers_address.*,country_master.country_name');
		$this->db->from('customers_address');
		$this->db->where('customers_address.customer_id',$customerId);
		$this->db->where('customers_address.remove_flag',0);
		$this->db->join('country_master','country_master.country_code = customers_address.country','LEFT');

		$query = $this->db->get();
		$resultArr = $query->result_array();
		// echo $this->db->last_query();
		return $resultArr;

	} 

	public function insertData($table,$data)
	{
		 $this->db->reset_query();
		  
	    $this->db->insert($table,$data);
	    if($this->db->affected_rows() > 0)
	    {
			$last_insert_id=$this->db->insert_id();
	      return $last_insert_id;
	    }
	    else
	    {
	      return false;
	    }
	}

	
	public function getEmailTemplateByIdentifier($identifier,$lang_code=''){
		// $result = $this->db->get_where('email_template',array('email_code'=>$identifier))->row();		
		if($lang_code!=''){
  			$this->db->select('email_template.*,multi_lang_email_template.subject as other_lang_subject,multi_lang_email_template.content as other_lang_content');	
  			$this->db->from('email_template');	
			$this->db->join("multi_lang_email_template","email_template.id = multi_lang_email_template.email_temp_id and  multi_lang_email_template.lang_code ='$lang_code'","LEFT");
			$this->db->where('email_template.email_code',$identifier);
			$query = $this->db->get();
			$result = $query->row();
		}else{
  			$result = $this->db->get_where('email_template',array('email_code'=>$identifier))->row();
		} 			
		return $result ;
	}

	public function getCustomVariableByIdentifier($identifier){
		$result = $this->db->get_where('custom_variables',array('identifier'=>$identifier))->row();		
		return $result;
	}

	public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars,$SubDynamic='',$CommonVars = '',$lang_code='')
	{	
		$webshop_smtp_host=$this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port=$this->getCustomVariableByIdentifier('smtp_port');
		$webshop_smtp_username=$this->getCustomVariableByIdentifier('smtp_username');
		$webshop_smtp_password=$this->getCustomVariableByIdentifier('smtp_password');
		$webshop_smtp_secure=$this->getCustomVariableByIdentifier('smtp_secure');

		$GlobalVar=$this->getCustomVariableByIdentifier('admin_email');
		if(isset($GlobalVar) && $GlobalVar->value!=''){
			$from_email=$GlobalVar->value;
		}else{
			$shop_id		=	$this->session->userdata('ShopID');
			$FBCData=$this->CommonModel->getShopOwnerData($shop_id);
			$from_email=$FBCData->email;
		}
		
		if($lang_code!=''){
			$emailTemplate = $this->getEmailTemplateByIdentifier($identifier,$lang_code);
		}else{
			$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);
		}

		if(isset($emailTemplate) && $emailTemplate->id!='')
		{
			if($lang_code!=''){
				$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header',$lang_code);		
				$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer',$lang_code);
			}else{
				$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header');		
				$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer');
			}

			if(isset($emailTemplate->other_lang_content) && $emailTemplate->other_lang_content !=''){
				$emailContent = $emailTemplate->other_lang_content;
			}else{
				$emailContent = $emailTemplate->content;
			}

			if(isset($emailTemplate->other_lang_subject) && $emailTemplate->other_lang_subject !=''){
				$subject = $emailTemplate->other_lang_subject;
			}else{
				$subject = $emailTemplate->subject;
			}
			
			$HeaderPart=$emailHeaderTemplate->content;
			$FooterPart=$emailFooterTemplate->content;
			
			if(isset($CommonVars) && $CommonVars!='')
			{
				$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
				$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);
			}

			$templateId=$emailTemplate->id;
			
			// $subject = $emailTemplate->subject;
			$title = $emailTemplate->title;
			if(isset($SubDynamic) && $SubDynamic!=''){
				$subject = str_replace('##WEBSHOPNAME##', $SubDynamic, $subject);
			}else{
				$subject = str_replace('##WEBSHOPNAME##', '', $subject);
			}
			$emailBody = str_replace($TempVars, $DynamicVars, $emailContent);
			
			// $data['title'] = $title;
			// $data['subject'] = $subject;
			// $data['content'] = $emailBody;

			// $content = $this->load->view('email_template/email_content', $data, TRUE);
			$FinalContentBody=$HeaderPart.$emailBody.$FooterPart;
			if($this->CommonModel->sendHTMLMailSMTP($EmailTo, $subject, $FinalContentBody,$from_email, $attachment="",$webshop_smtp_host->value, $webshop_smtp_port->value, $webshop_smtp_username->value, $webshop_smtp_password->value, $webshop_smtp_secure->value))
			{
				return true;
			}else{
				
				return false;
			}
		}
	}

	public function get_datatables_customer_details()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->get_datatables_all_customer_deatils($term);
		if($_REQUEST['length'] != -1)
		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		return $query->result();
	}

	public function get_datatables_all_customer_deatils( $term ='')
	{

		$column = array('customers.id','customers.first_name','customers.created_at','customers.email_id','customers_address.city','');
		
		$this->db->select('customers.*, customers_address.city, customers_address.state,customers_address.mobile_no, sales_order.*');
	  	$this->db->from('customers', 'customers_address', 'sales_order');
		$this->db->where('customers.status',1);
		$this->db->join('customers_address', 'customers_address.customer_id = customers.id','LEFT');
		$this->db->join('sales_order', 'sales_order.customer_id = customers.id','LEFT');
		//$this->db->where(array('customers_address.remove_flag'=>0));
		$this->db->where('(customers_address.remove_flag = 0 OR customers_address.remove_flag IS NULL)');
		$this->db->group_by('customers.id');

		

		if($term!=''){
			$this->db->where(" (
				customers.first_name LIKE '%$term%'
				OR customers.last_name LIKE '%$term%'
				OR customers.created_at LIKE '%$term%'
				OR customers.email_id LIKE '%$term%'
				OR customers_address.city LIKE '%$term%'
				  
			)");	
		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}else{
			$this->db->order_by("customers.id", "desc");
		}

	}

	public function countfiltercustomersrecord()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->get_datatables_all_customer_deatils($term);
		$query = $this->db->get();
		return $query->num_rows();

	}

	public function countcustomersrecord()
	{
		$this->db->select('*');
		$this->db->from('customers');
		$this->db->where('status',1);
		$query = $this->db->count_all_results();
		return $query;
	}

	public function getCustomerOrderReturnDataById($customer_id)
	{

		$this->db->select('sor.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type,o.created_at as order_created_at');
		$this->db->from('sales_order_return as sor');
		$this->db->where('sor.customer_id',$customer_id);
		$this->db->join('sales_order_payment as wp','sor.order_id = wp.order_id','LEFT');	
		$this->db->join('sales_order as o',' sor.order_id =o.order_id','LEFT');	
		$this->db->where('(sor.status  NOT IN (0,1))'); 
		$query = $this->db->get();
		return $query->result_array();

	}
	public function getOrderDetailsData($customer_id)
	{
		$this->db->select('s.*, sop.payment_method_name');
		$this->db->from('sales_order as s');
		$this->db->join('sales_order_payment as sop', 's.order_id = sop.order_id', 'LEFT');
		$this->db->where('s.customer_id', $customer_id);
		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
}
