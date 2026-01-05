<?php
class EuShippingChargesModel extends CI_Model
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
			$this->shop_db = $this->load->database($config_app,TRUE);
		}else{
			redirect(base_url());
		}
	}

	public function get_all_shipping_methods()
	{
		$this->shop_db->where('remove_flag', 0);
		$query = $this->shop_db->get('shipping_methods');


	    //return $query->result_array();
			if ($query->num_rows() > 0)
		   {
				$result = $query->result_array();
				return $result;
		   }
		   else{
			   return false;
		   }
	}

	public function getSingleDataByID($tableName,$condition,$select)
	{
		if(!empty($select)){
	  		$this->shop_db->select($select);
		}
		$this->shop_db->where($condition);
		$query = $this->shop_db->get($tableName);
		return $query->row();
	}

	public function getMultiDataById($tableName,$condition,$select)
	{
		if(!empty($select)){
	  		$this->shop_db->select($select);
		}
		$this->shop_db->where($condition);
		$query = $this->shop_db->get($tableName);
		return $query->result();
	}

	public function insertData($table,$data)
	{
	    $this->shop_db->insert($table,$data);
	    if($this->shop_db->affected_rows() > 0)
	    {
			$last_insert_id=$this->shop_db->insert_id();
	      	return $last_insert_id;
	    }else{
	      	return false;
	    }
	}

	public function updateNewData($tableName,$condition,$updateData)
    {
		$this->shop_db->where($condition);
		$this->shop_db->update($tableName,$updateData);
		if($this->shop_db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
    }

    public function get_customer_type_name($where_array)
    {
    	$this->shop_db->where_in( 'id', $where_array );
    	$query = $this->shop_db->get('customers_type_master');
			if ($query->num_rows() > 0)
		   {
				$result = $query->result_array();
				return $result;
		   }
		   else{
			   return false;
		   }
    }

    public function deleteData($tableName,$condition)
    {
		$this->shop_db->where($condition);
		$this->shop_db->delete($tableName);
		if($this->shop_db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
    }

    public function get_all_shipping_methods_charges()
	{
		// $this->shop_db->where('ship_method_id', $ship_method_id);
		$query = $this->shop_db->get('shipping_methods_charges');
		//return $query->result_array();
			if ($query->num_rows() > 0)
		   {
				$result = $query->result_array();
				return $result;
		   }
		   else{
			   return false;
		   }
	}

	public function delete_all_shipping_method_charges(){
		$this->shop_db->empty_table('shipping_methods_charges');
	}

	public function start_transaction(){
		$this->shop_db->trans_start();
	}

	public function complete_transaction(){
		$this->shop_db->trans_complete();
	}
}
