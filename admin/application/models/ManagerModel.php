<?php 

class ManagerModel extends CI_Model
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
			$this->cust_db = $this->load->database($config_app,TRUE);
		}else{
			redirect(base_url());
		}

	}

	public function add_account_manager($insert_array)
	{
		 $this->cust_db->insert('acc_managers_master',$insert_array);
		if($this->cust_db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
	}

	public function get_account_manager_details()
	{		
		$this->cust_db->order_by("id", "ASC");
		$query = $this->cust_db->get('acc_managers_master');
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

	public function get_manager_count($acc_manager_id)
	{
		$this->cust_db->where('acc_manager_id',$acc_manager_id);
		$query = $this->cust_db->get('customers');

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

	 public function get_single_account_manager_details($id)
	 {
		 $this->cust_db->where('id',$id);
		 $query = $this->cust_db->get('acc_managers_master');
			if ($query->num_rows() > 0)
		   {
				$result = $query->row_array();
				return $result;
		   }
		   else{
			   return false;
		   }
		 
	 }

	public function update_manager_account($update_array,$id)
	  {
		  $this->cust_db->where('id',$id);
		  $query = $this->cust_db->update('acc_managers_master',$update_array);
		  // echo $this->cust_db->last_query();
		  if ($query)
		   {
				
				return true;
		   }
		   else{
			   return false;
		   }
	  }


	public function get_all_manager_by_type($acc_manager_id)
	{
		$this->cust_db->select('customers.*, customers_address.city, customers_address.state,customers_address.mobile_no, sales_order.*');
	  	$this->cust_db->from('customers','customers_address','sales_order');
		$this->cust_db->where('customers.status',1);
		$this->cust_db->where('acc_manager_id',$acc_manager_id);
		$this->cust_db->join('customers_address','customers_address.customer_id = customers.id','LEFT');
		$this->cust_db->join('sales_order','sales_order.customer_id = customers.id','LEFT');
		$this->cust_db->order_by("sales_order.created_at", "desc");
		$this->cust_db->group_by('customers.id');
		$query = $this->cust_db->get();
		// $query = $this->cust_db->get('customers');
			if ($query->num_rows() > 0)
		   {
				$result = $query->result_array();
				return $result;
		   }
		   else{
			   return false;
		   }
	}

}
