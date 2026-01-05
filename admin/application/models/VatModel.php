<?php
class VatModel extends CI_Model
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
			$this->fbc_db = $this->load->database($config_app,TRUE);
		}else{
			redirect(base_url());
		}

	}

    public function insertData($table,$data)
	{
		$this->fbc_db->reset_query();
		  
	    $this->fbc_db->insert($table,$data);
	    if($this->fbc_db->affected_rows() > 0)
	    {
			$last_insert_id=$this->fbc_db->insert_id();
	        return $last_insert_id;
	    }
	    else
	    {
	      return false;
	    }
	}


	public function updateData($tableName,$condition,$updateData)
    {
		$this->fbc_db->where($condition);
		   
		  $this->fbc_db->update($tableName,$updateData);
		  if($this->fbc_db->affected_rows() > 0)
		  {
			 
			return true;
		  }
		  else
		  {
			return false;
		  }
		  
		  $this->fbc_db->reset_query();
    }



    public function get_all_vat()
	{
        $main_db_name=$this->db->database;

        $this->fbc_db->select('vs.*,cm.country_name');
        $this->fbc_db->from('vat_settings as vs');
        $this->fbc_db->join($main_db_name.'.country_master as cm','cm.country_code = vs.country_code','LEFT');
        $this->fbc_db->where('remove_flag', 0);

        $this->fbc_db->order_by("id", "DESC");
		$query = $this->fbc_db->get();
		
		$resultArr = $query->result_array();
		return $resultArr;

    } 
	
	public function getSingleDataByID($tableName,$condition,$select)
	  {
		if(!empty($select))
		{
		  $this->fbc_db->select($select);
		}
		$this->fbc_db->where($condition);
		$query = $this->fbc_db->get($tableName);
		return $query->row();
	  }

}
