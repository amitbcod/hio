<?php

class VariableModel extends CI_Model

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

	public function getVariable()

	{

		$result = $this->shop_db->get('custom_variables');

		//echo $this->db->last_query();die();

		if($result->num_rows() > 0)

		{

			return $result->result_array();

		}

		else

		{

			return false;

		}

		

	}

	

	function get_editcustvariable($id)

	{	

		if(isset($id) && !empty($id))

		{

			$this->shop_db->where('id',$id);

			$result = $this->shop_db->get('custom_variables');

			if($result->num_rows() > 0)

			{

				return $result->row_array();

			}

			else

			{

				return false;

			}

		}

	}

	

	function get_existidentifier($identifier)

	{

		$this->shop_db->where('identifier',$identifier);

		$result = $this->shop_db->get('custom_variables');

		if($result->num_rows() > 0)

		{

			return $result->row_array();

		}

		else

		{

			return false;

		}

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

	

}
