<?php
class ShippingChargesModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_all_shipping_charges()
	{
			$query = $this->db->get('shipping_charges_master');
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
	  		$this->db->select($select);
		}
		$this->db->where($condition);
		$query = $this->db->get($tableName);
		return $query->row();
	}

	public function getMultiDataById($tableName,$condition,$select)
	{
		if(!empty($select)){
	  		$this->db->select($select);
		}
		$this->db->where($condition);
		$query = $this->db->get($tableName);
		return $query->result();
	}

	public function insertData($table,$data)
	{
	    $this->db->insert($table,$data);
	    if($this->db->affected_rows() > 0)
	    {
			$last_insert_id=$this->db->insert_id();
	      	return $last_insert_id;
	    }else{
	      	return false;
	    }
	}

	public function updateNewData($tableName,$condition,$updateData)
    {
		$this->db->where($condition);
		$this->db->update($tableName,$updateData);
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
    }

    public function get_customer_type_name($where_array)
    {
    	$this->db->where_in( 'id', $where_array );
    	$query = $this->db->get('customers_type_master');
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
		$this->db->where($condition);
		$this->db->delete($tableName);
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
    }
}
