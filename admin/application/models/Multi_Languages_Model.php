<?php
class Multi_Languages_Model extends CI_Model
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

    public function get_all_languages()
	{
        $main_db_name=$this->db->database;

        $this->db->select('*');
        $this->db->from('multi_languages');
        $this->db->where('remove_flag', 0);
        $this->db->order_by("id", "DESC");
		$query = $this->db->get();
		$resultArr = $query->result_array();
		return $resultArr;

    } 

    public function getLanguages()
	{
        $main_db_name=$this->db->database;

        $this->db->select('*');
        $this->db->from('multi_languages');
        $this->db->where('remove_flag', 0);
        $this->db->where('is_default_language',0);
        $this->db->order_by("id", "DESC");
		$query = $this->db->get();
		$resultArr = $query->result_array();
		return $resultArr;
    } 
    public function getCodeName($code)
	{
        $main_db_name=$this->db->database;

        $this->db->select('name');
        $this->db->from('multi_languages');
        $this->db->where('code', $code);
		$query = $this->db->get();
		$resultArr = $query->row();
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

	public function Update_default_language()
	  {
		
		$this->db->select('*');
        $this->db->from('multi_languages');
        $this->db->where('is_default_language', 1);
		$query = $this->db->get();
		$data=$query->row();
    	$this->db->where(array('id'=>$data->id));
    	$this->db->update('multi_languages', array('is_default_language' => '0'));
	  } 

	  public function getMenuType()
	{
		$result = $this->db->get_where('static_blocks')->row();
		return $result;
	}

	public function CountMultiLangCategory($id, $code)
	{
		$this->db->select('*');
        $this->db->from('multi_lang_category');
        $this->db->where('category_id', $id);
        $this->db->where('lang_code', $code);
        $query = $this->db->get();
		$resultArr = $query->num_rows();
		return $resultArr;
	}

	public function getMultiLangCategory($category_id, $code)
	{
		$this->db->select('*');
        $this->db->from('multi_lang_category');
        $this->db->where('category_id', $category_id);
        $this->db->where('lang_code', $code);
        $query = $this->db->get();
		$resultArr = $query->row();
		return $resultArr;
	}



}
