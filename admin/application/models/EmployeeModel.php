<?php

class EmployeeModel extends CI_Model 
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


	public function getChildData($pid){
			$this->cust_db->select('*');
			$this->cust_db->from('employee_resource_master');
			$this->cust_db->where('parent_id',$pid);
			$query = $this->cust_db->get();
			$resultArr = $query->result_array();
			return $resultArr;
	}

	public function insertRole($data)
    { 
		$this->cust_db->insert("employee_role_master",$data);
		$last_role_id = $this->cust_db->insert_id();
        return $last_role_id;
    }
	public function insertRoleResource($roleId,$resource_checkbox='')
	{
		
			if($resource_checkbox!='')
			{
			 for($i = 0; $i < count($resource_checkbox); $i++){
			//print_r($resource_checkbox[$i]);
			$data = array(
				'role_id' => $roleId,
				'resource_id' => $resource_checkbox[$i]
			  );
			$this->cust_db->insert("employee_role_resource",$data);
		}
		}
	}
	
	public function getEmpRoles()
	{
		$this->cust_db->select('*');
        $this->cust_db->from('employee_role_master');
        $this->cust_db->where('remove_flag', 0);
        $query = $this->cust_db->get();
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function getParentData()

	{
		$this->cust_db->select('*');
        $this->cust_db->from('employee_resource_master');
		$this->cust_db->where('parent_id',0);
        $get_resource_parentid_table = $this->cust_db->get();
		$get_tabledata = $get_resource_parentid_table->result_array();
		return $get_tabledata;
	}

	public function child_data_by_emp_id($eid){
			$this->cust_db->select('employee_resource_master.*,employee_role_resource.*,employee_resource_master.id as childId');
			$this->cust_db->from('employee_role_resource');
			$this->cust_db->join('employee_resource_master','employee_resource_master.id=employee_role_resource.resource_id','LEFT');
			$this->cust_db->where('role_id',$eid);
			$get_resource_table = $this->cust_db->get();
			$get_table = $get_resource_table->result_array();
			return $get_table;

	}

	public function getSingleRoleNameByID($roleId)
	{
		$this->cust_db->select('*');
		$this->cust_db->where('id', $roleId);
		$get_resource_table= $this->cust_db->get('employee_role_master');
		$get_table = $get_resource_table->row();
		return $get_table;
	}

	public function updateRole($roleId,$data)
	{
	
		$this->cust_db->set($data);
		$this->cust_db->where('id',$roleId);
		$resultArr = $this->cust_db->update('employee_role_master');
		return $resultArr;
	}

	public function deleteRole($roleId)
		{
			$this->cust_db->where('role_id',$roleId);
			$resultArr = $this->cust_db->delete('employee_role_resource');
			return $resultArr;
		}
    public function delete_role($id)
		{
			$this->cust_db->set('remove_flag',1);
			$this->cust_db->where('id',$id);
			$resultArr = $this->cust_db->update('employee_role_master');
			return $resultArr;
		}
}
