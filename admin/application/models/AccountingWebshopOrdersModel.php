<?php
class AccountingWebshopOrdersModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		
		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			
			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);		
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
	
	
  	// accounting
  	function getWebshopOrderNotInvoice()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_query_WebshopOrderNotInvoice($term);
		$query = $this->seller_db->get();
		$result=$query->result();
		foreach ($result as $key => $value) {

			if ($value->invoice_self == 1) {
				// tab 1
			}elseif($value->customer_id > 0){
				$invoiceData=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$value->customer_id),'*');
				// print_r($invoiceData);
				if(!empty($invoiceData->invoice_to_type)){
					if($invoiceData->invoice_to_type == 0){
			        	// tab 1
			        }else{
			        	$custom_variable_data=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
						// print_r($custom_variable_data);

			        	if(!empty($custom_variable_data) && $invoiceData->invoice_to_type != $custom_variable_data->value){
			                //1st tab
			            }else{ 
			            	unset($result[$key]);
			            }
			           
			        }
			    }else{
			        unset($result[$key]);
			    }

			}else{
				unset($result[$key]);

			}

		}

		$result=array_values($result);
		return array_slice($result, $_REQUEST['start'], $_REQUEST['length'],true);

	}

	function getWebshopOrderNotInvoice_not()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_query_WebshopOrderNotInvoice($term);
		$query = $this->seller_db->get();
		$result=$query->result();

		foreach ($result as $key => $value) {
			if ($value->invoice_self == 0) {
				if($value->customer_id > 0) {
					$invoiceData=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$value->customer_id),'*');
					// print_r($invoiceData);
					if(!empty($invoiceData->invoice_to_type)){
						if($invoiceData->invoice_to_type == 0){
							unset($result[$key]);
				        }else{
				        	$custom_variable_data=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
							// print_r($custom_variable_data);

				        	if(!empty($custom_variable_data) && $invoiceData->invoice_to_type != $custom_variable_data->value){
				        		unset($result[$key]);
				            }
				        }
				    }
				}  

			}else{
				unset($result[$key]);
			}
		}	

		$result=array_values($result);
		return array_slice($result, $_REQUEST['start'], $_REQUEST['length'],false);
	}

	public function _query_WebshopOrderNotInvoice($term){
		$column = array('', '','', '','','','o.tracking_complete_date','');
		$this->seller_db->select("o.*,c.invoice_type,c.invoice_to_type,c.alternative_email_id");		
		$this->seller_db->from('sales_order as o');	
		$this->seller_db->join('customers_invoice as c','o.customer_id = c.customer_id','LEFT');
		$this->seller_db->join('invoicing as i','o.order_id = i.invoice_order_nos AND i.invoice_order_type=1','LEFT');
		// $this->seller_db->where('o.status',6);
		$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0)); 
		$this->seller_db->where('i.invoice_order_nos',null);
		if($term !=''){
		  $this->seller_db->where(" (
			o.increment_id LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%' OR o.customer_lastname LIKE '%$term%'
			 )");
		} 
		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}else{
			$this->seller_db->order_by('o.tracking_complete_date','desc');
			// $this->seller_db->order_by('o.order_id','desc');
		}
	}

	// count_filtered_orders, count_all_orders
	function count_all_WebshopOrderNotInvoice(){
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_query_WebshopOrderNotInvoice($term);
		$query = $this->seller_db->get();
		$result=$query->result();
		// print_r($result);
		foreach ($result as $key => $value) {

			if ($value->invoice_self == 1) {
				// tab 1
			}elseif($value->customer_id > 0){
				$invoiceData=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$value->customer_id),'*');
				// print_r($invoiceData);
				if(!empty($invoiceData->invoice_to_type)){
					if($invoiceData->invoice_to_type == 0){
			        	// tab 1
			        }else{
			        	$custom_variable_data=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
						// print_r($custom_variable_data);

			        	if(!empty($custom_variable_data) && $invoiceData->invoice_to_type != $custom_variable_data->value){
			                //1st tab
			            }else{ 
			            	unset($result[$key]);
			                // continue;
			            }
			           
			        }
			    }else{
			        unset($result[$key]);
			        // continue;
			    }

			}else{
				unset($result[$key]);
				// continue;

			}

		}

		return count($result);

	}

	function count_all_WebshopOrderNotInvoice_not(){
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_query_WebshopOrderNotInvoice($term);
		$query = $this->seller_db->get();
		$result=$query->result();
		// print_r($result);
		foreach ($result as $key => $value) {
			if ($value->invoice_self == 0) {
				if($value->customer_id > 0) {
					$invoiceData=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$value->customer_id),'*');
					// print_r($invoiceData);
					if(!empty($invoiceData->invoice_to_type)){
						if($invoiceData->invoice_to_type == 0){
							unset($result[$key]);
							// continue;
				        }else{
				        	$custom_variable_data=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
							// print_r($custom_variable_data);

				        	if(!empty($custom_variable_data) && $invoiceData->invoice_to_type != $custom_variable_data->value){
				        		unset($result[$key]);
				            	// continue;
				            }
				        }
				    }
				}  

			}else{
				unset($result[$key]);
				// continue;
			}
		}

		return count($result);

	}

	function count_filtered_WebshopOrderNotInvoice(){
		$term = $_REQUEST['search']['value'];
		$this->_query_WebshopOrderNotInvoice($term);
		// if($_REQUEST['length'] != -1){
		// $this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']);
		// }
		$query = $this->seller_db->get();
		$result=$query->result();
		// print_r($result);
		foreach ($result as $key => $value) {

			if ($value->invoice_self == 1) {
				// tab 1
			}elseif($value->customer_id > 0){
				$invoiceData=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$value->customer_id),'*');
				// print_r($invoiceData);
				if(!empty($invoiceData->invoice_to_type)){
					if($invoiceData->invoice_to_type == 0){
			        	// tab 1
			        }else{
			        	$custom_variable_data=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
						// print_r($custom_variable_data);

			        	if(!empty($custom_variable_data) && $invoiceData->invoice_to_type != $custom_variable_data->value){
			                //1st tab
			            }else{ 
			            	unset($result[$key]);
			                // continue;
			            }
			           
			        }
			    }else{
			        unset($result[$key]);
			        // continue;
			    }

			}else{
				unset($result[$key]);
				// continue;

			}

		}

		return count($result);
	}

	function count_filtered_WebshopOrderNotInvoice_not(){
		$term = $_REQUEST['search']['value'];
		$this->_query_WebshopOrderNotInvoice($term);
		// if($_REQUEST['length'] != -1){
		// $this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']);
		// }
		$query = $this->seller_db->get();
		$result=$query->result();
		// print_r($result);
		foreach ($result as $key => $value) {
			if ($value->invoice_self == 0) {
				if($value->customer_id > 0) {
					$invoiceData=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$value->customer_id),'*');
					// print_r($invoiceData);
					if(!empty($invoiceData->invoice_to_type)){
						if($invoiceData->invoice_to_type == 0){
							unset($result[$key]);
							// continue;
				        }else{
				        	$custom_variable_data=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
							// print_r($custom_variable_data);

				        	if(!empty($custom_variable_data) && $invoiceData->invoice_to_type != $custom_variable_data->value){
				        		unset($result[$key]);
				            	// continue;
				            }
				        }
				    }
				}  

			}else{
				unset($result[$key]);
				// continue;
			}
		}

		return count($result);
	}


	// B2webshop
	 
	// accounting
  	function getB2WebshopOrderNotInvoice()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_query_B2WebshopOrderNotInvoice($term);
		if($_REQUEST['length'] != -1)
		$this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	}

	public function _query_B2WebshopOrderNotInvoice($term){
		$main_db_name=$this->db->database;
		$current_tab='shipped-orders';
		$order_status='6';
		
		$extra_select='';

		$column = array('', '','', '','','','o.tracking_complete_date','');
		$this->seller_db->distinct();
		$this->seller_db->select('o.*,c.invoice_type,fu.fbc_user_id,IF(o.shipment_type=1,fu.owner_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name, fus.org_shop_name,fu.owner_name'.$extra_select);
		// $this->seller_db->select('o.*,c.invoice_type,IF(o.shipment_type=1,fu.owner_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name, fus.org_shop_name'.$extra_select);
		$this->seller_db->from('b2b_orders as o');	
		$this->seller_db->join($main_db_name.'.fbc_users_shop as fus','o.shop_id = fus.shop_id','LEFT');
		$this->seller_db->join($main_db_name.'.fbc_users as fu','o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id','LEFT');
		
		$this->seller_db->join('b2b_customers as b2c','b2c.shop_id = o.shop_id','LEFT');	
		$this->seller_db->join('b2b_customers_invoice as c','b2c.id = c.customer_id','LEFT');	
		$this->seller_db->join('invoicing as i','o.order_id = i.invoice_order_nos AND i.invoice_order_type=2','LEFT');
		// $this->seller_db->join('invoicing as i','o.order_id = i.invoice_order_nos','LEFT');
		// $this->seller_db->where('o.status',6);
		$this->seller_db->where('i.invoice_order_nos',null);

		if($current_tab=='shipped-orders')
		{
			$this->seller_db->where('o.parent_id','0'); 
			$this->seller_db->where('o.main_parent_id','0'); 
			//$this->seller_db->where('o.is_split','0');   
			$this->seller_db->where('(o.status IN (4,5,6))'); 
		}
		
		
		if($term !=''){
					  
		  $this->seller_db->where(" (
			o.increment_id LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%'
			OR o.order_id LIKE '%$term%'
			OR fus.org_shop_name LIKE '%$term%'
			 )");
			  
		} 
		
		/*if(!empty($order_status))
		{
			$this->seller_db->where("o.status",$order_status); 
		}*/
		
		$this->seller_db->where(array('o.status' => 6,'o.invoice_flag' => 0)); //order status invoice flag

		if(!empty($shipment_type))
		{
			$this->seller_db->where("o.shipment_type IN ($shipment_type)"); 
		}
		
		
		if(!empty($fromDate) && empty($toDate)){
			
			$this->seller_db->where('o.updated_at >=',strtotime($fromDate)); 
		}
		else if(!empty($toDate) && empty($fromDate)){
			
			$this->seller_db->where('o.updated_at <=',strtotime($toDate)); 
		}
		else if(!empty($toDate) && !empty($fromDate))
		{
			$this->seller_db->where('o.updated_at >=',strtotime($fromDate));
			$this->seller_db->where('o.updated_at <=',strtotime($toDate));
		}
		
		/*---------------------Check for Zumbashop India Orders---------------------------------------------*/
		
		$UserRole= $this->session->userdata('UserRole');
		if($UserRole=='Zumbashop India User')
		{
			$FindZumbaShop=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_flag'=>2),'');
			if(isset($FindZumbaShop) && $FindZumbaShop->shop_id!='')
			{
				$this->seller_db->where('o.shop_id',$FindZumbaShop->shop_id);   //$FindZumbaShop->shop_id
				$this->seller_db->where('o.shipment_type',2);
				
			}
		}
		/*-------------------------------------------------------------------------------------------------*/
		
		$this->seller_db->where('(o.status NOT IN (7))'); 
		
		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}else{
			$this->seller_db->order_by('o.tracking_complete_date','desc');
			// $this->seller_db->order_by('o.order_id','desc');
		}
	}

	// count_filtered_orders, count_all_orders
	function count_all_B2WebshopOrderNotInvoice(){
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_query_B2WebshopOrderNotInvoice($term);
		return $this->seller_db->count_all_results();
	}
	function count_filtered_B2WebshopOrderNotInvoice(){
		$term = $_REQUEST['search']['value'];
		$this->_query_B2WebshopOrderNotInvoice($term);
		$query = $this->seller_db->get();
		return $query->num_rows();
	}

	// invoicing

	  	function getInvoicingWebshopOrderNotInvoice()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_query_InvoicingWebshopOrderNotInvoice($term);
		if($_REQUEST['length'] != -1)
		$this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']); //display data issue
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	}

	public function _query_InvoicingWebshopOrderNotInvoice($term){
		$this->seller_db->select('*');
		$this->seller_db->from('invoicing');	
		
		//$this->seller_db->where('',null);
		if($term !=''){
					  
		  $this->seller_db->where(" (
			invoice_no LIKE '%$term%'
			OR customer_first_name LIKE '%$term%'
			OR customer_last_name LIKE '%$term%'
			OR resent_flag LIKE '%$term%'
			 )");
			  
		}
		$this->seller_db->order_by('id', 'desc');
	}

	// count_filtered_orders, count_all_orders
	function count_all_InvoicingWebshopOrderNotInvoice(){
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_query_InvoicingWebshopOrderNotInvoice($term);
		return $this->seller_db->count_all_results();
	}
	function count_filtered_InvoicingWebshopOrderNotInvoice(){
		$term = $_REQUEST['search']['value'];
		$this->_query_InvoicingWebshopOrderNotInvoice($term);
		$query = $this->seller_db->get();
		return $query->num_rows();
	}

	/*function testtest()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		
		$this->seller_db->select('*');
		$this->seller_db->from('invoicing');	
		
		//$this->seller_db->where('',null);
		if($term !=''){
					  
		  $this->seller_db->where(" (
			invoice_no LIKE '%$term%'
			OR customer_first_name LIKE '%$term%'
			OR customer_last_name LIKE '%$term%'
			OR resent_flag LIKE '%$term%'
			 )");
			  
		}
		$this->seller_db->order_by('id', 'desc');

		$query = $this->seller_db->get();
		print_r(count($query->result()));
		print_r($query->result());
     
	}*/

	public function getinvoicingForCSVImport()
    {
    	$this->seller_db->select('*');
		$this->seller_db->from('invoicing');
		$query = $this->seller_db->get();
		$result = $query->result();
		return $result;
    }

    // invoicing detail
    public function getinvoicingForCSVImport_details()
    {
    	$this->seller_db->select('id.*,i.invoice_no,i.customer_first_name,i.customer_last_name,i.customer_id,i.shop_id,i.bill_customer_first_name,i.bill_customer_last_name,i.invoice_date,i.invoice_due_date,i.invoice_term,i.billing_country,i.ship_country,i.billing_state,i.ship_state,i.invoice_order_type');
		$this->seller_db->from('invoicing_details as id');
		$this->seller_db->join('invoicing as i','id.invoice_id = i.id','JOIN');
		$query = $this->seller_db->get();
		$result = $query->result();
		// print_r($this->seller_db->last_query());
		return $result;
    }

    // insert data
    //insertData
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

	/*sales report*/
    public function getSalesReportCSVImport($fromdate,$todate)
    {
    	$this->seller_db->select('order_id,increment_id,created_at,coupon_code,discount_percent,tracking_complete_date');
		$this->seller_db->from('sales_order');
		//$this->seller_db->where('created_at BETWEEN "'.$fromdate.'" and "'.$todate.'"');
		$this->seller_db->where('tracking_complete_date >=', $fromdate);
		$this->seller_db->where('tracking_complete_date <=', $todate);
		/*$this->seller_db->where('created_at >=', $fromdate);
		$this->seller_db->where('created_at <=', $todate);
		*/
		$this->seller_db->where('status', 6);
		$query = $this->seller_db->get();
		$result = $query->result_array();
		// print_r($this->seller_db->last_query());
		return $result;
    }


    public function getSalesItemReportCSVImport($orderId)
    {
    	$this->seller_db->select('*');
		$this->seller_db->from('sales_order_items');
		//$this->seller_db->where('created_at BETWEEN "'.$fromdate.'" and "'.$todate.'"');
		$this->seller_db->where('order_id', $orderId);
		$query = $this->seller_db->get();
		$result = $query->result_array();
		return $result;
    }

	/*end sales report*/

	public function get_customer_data_accounting_ajax()
    {
    	$this->seller_db->select('id, email_id, first_name, last_name');
		$this->seller_db->from('customers');
		$query = $this->seller_db->get();
		$result = $query->result();
		return $result;
    }

    public function get_customer_invoice_data_accounting_ajax()
    {
    	$this->seller_db->select('id,invoice_type,customer_id');
		$this->seller_db->from('customers_invoice');
		$query = $this->seller_db->get();
		$result = $query->result();
		return $result;
    }
	
}
