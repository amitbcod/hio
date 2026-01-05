<?php
class Multi_Currencies_Model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		
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


	public function updateData($tableName,$condition,$updateData)
    {
		$this->db->where($condition);
		   
		  $this->db->update($tableName,$updateData);
		  if($this->db->affected_rows() > 0)
		  {
			 
			return true;
		  }
		  else
		  {
			return false;
		  }
		  
		  $this->db->reset_query();
    }



    public function get_all_currencies()
	{
        $main_db_name=$this->db->database;

        $this->db->select('*');
        $this->db->from('multi_currencies');
        $this->db->where('remove_flag', 0);
        $this->db->order_by("id", "DESC");
		$query = $this->db->get();
		
		$resultArr = $query->result_array();
		return $resultArr;

    } 
	
	public function getSingleDataByID($tableName,$condition,$select)
	  {
		if(!empty($select))
		{
		  $this->db->select($select);
		}
		$this->db->where($condition);
		$query = $this->db->get($tableName);
		return $query->row();
	  }

	public function Update_default_currency()
	  {
		
		

		$this->db->select('*');
        $this->db->from('multi_currencies');
        $this->db->where('is_default_currency', 1);
		$query = $this->db->get();
		$data=$query->row();
		// return $data->id;


    	$this->db->where(array('id'=>$data->id));
    	$this->db->update('multi_currencies', array('is_default_currency' => '0'));

    

		// return $query->row();
	  }  

}
