<?php
class EmailModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	public function getEmail($id =false)
	{
		if(isset($id) && !empty($id))
		{
			$this->db->where('id',$id);
			$result = $this->db->get('email_template');
			if($result->num_rows() > 0)
			{
				return $result->row_array();
			}
			else
			{
				return false;
			}
		}
		else
		{
			$result = $this->db->get('email_template');
			// echo $this->db->last_query();die();

			if($result->num_rows() > 0)
			{
				return $result->result_array();
			}
			else
			{
				return false;
			}
		}
	}
	public function insert_template($insert_array)
	{
		
		$result = $this->db->insert('email_template',$insert_array);
		if($result)
		{
			return true;
		}
		
	}
	public function update_template($update_array,$id)
	{
		$this->db->where('id',$id);
		$result = $this->db->update('email_template',$update_array);
		// echo $this->db->last_query();
		if($result)
		{
			return true; 
		}
		
	}

	public function get_templates_details($id)
	{
		$this->db->select('*');
        $this->db->from('multi_lang_email_template');
        $this->db->where('id', $id);
        $query = $this->db->get();
		$resultArr = $query->row();
		return $resultArr;
	}

	public function get_Multi_Templates($email_temp_id, $code)
	{
		$this->db->select('*');
        $this->db->from('multi_lang_email_template');
        $this->db->where('email_temp_id', $email_temp_id);
        $this->db->where('lang_code', $code);
        $query = $this->db->get();
		$resultArr = $query->row();
		return $resultArr;
	}
	public function countTemp($id, $code)
	{
		$this->db->select('*');
        $this->db->from('multi_lang_email_template');
        $this->db->where('email_temp_id', $id);
        $this->db->where('lang_code', $code);
        $query = $this->db->get();
		$resultArr = $query->num_rows();
		return $resultArr;
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

	public function updateEmailData($tableName,$condition,$updateData)
    {
    	$this->db->where($condition);

		$this->db->update($tableName,$updateData);
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
    }

	public function getTemplateCode($tamplate_code)
	{
		$this->db->select('*');
		$this->db->where('email_code',$tamplate_code);
		$query = $this->db->get('email_template');
        return $query->num_rows();

	}

	public function getTemplatetitle($tamplate_title)
	{
		$this->db->select('*');
		$this->db->where('title',$tamplate_title);
		$query = $this->db->get('email_template');
        return $query->num_rows();

	}
	
}
