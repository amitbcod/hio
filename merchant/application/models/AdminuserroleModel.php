<?php

class AdminuserroleModel extends CI_Model {
    function get_admin_user_role($id='', $mode='') {
        $this->db->select('*');
        $this->db->from('role_master rm'); 
        $this->db->join('adminusers au', 'au.user_type=rm.id');
        if($id !== '' && $mode=='user') {    
            $this->db->where('au.id',$id);      
        }
        if($id !== '' && $mode=='users') {    
            $this->db->where('au.id !=',$id);      
        }
        $query = $this->db->get(); 
        if($query->num_rows() != 0)
        {
            return $query->result_array();
        }
        else
        {
            return false;
        }
    }

    function get_role_master($user_id='') {
        $this->db->where('remove_flag',0);
        $this->db->where('created_by',$user_id);
        $query = $this->db->get('role_master');
        if($query->num_rows() != 0)
        {
            return $query->result_array();
        }
        else
        {
            return false;
        }
    }

    function update_admin_user_role($user_id) {
        $data = array( 
            'user_type'	=>  0 , 
            'updated_at'=>  time()
        );
        $this->db->where('id', $user_id);
        $query = $this->db->update('adminusers', $data);
		if($query) return true;
		else return false;
    }

    function get_parent_data() {
        $this->db->where('parent_id',0);
        $this->db->where('tree_level',0);
        $query = $this->db->get('resource_master');
        if($query->num_rows() != 0)
        {
            return $query->result_array();
        }
        else
        {
            return false;
        }
    }

    function get_child_data_l1($id='') {
        $this->db->where('parent_id',$id);
        $this->db->where('tree_level',1);
        $query = $this->db->get('resource_master');
        if($query->num_rows() != 0)
        {
            return $query->result_array();
        }
        else
        {
            return false;
        }
    }

    function get_child_data_l2($id='') {
        $this->db->where('parent_id',$id);
        $this->db->where('tree_level',2);
        $query = $this->db->get('resource_master');
        //echo $this->db->last_query();die();
        if($query->num_rows() != 0)
        {
            return $query->result_array();
        }
        else
        {
            return false;
        }
    }

    public function insertRole($data)
    { 
		$this->db->insert("role_master",$data);
		$last_role_id = $this->db->insert_id();
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
				'resource_id' => $resource_checkbox[$i],
                'created_at' => time(),
                'ip' => $_SERVER['REMOTE_ADDR']
			  );
			$this->db->insert("role_resource",$data);
		}
		}
	}

    public function getParentData()
	{
		$this->db->select('*');
        $this->db->from('resource_master');
		$this->db->where('parent_id',0);
        $get_resource_parentid_table = $this->db->get();
		$get_tabledata = $get_resource_parentid_table->result_array();
		return $get_tabledata;
	}

    public function getSingleRoleNameByID($roleId)
	{
		$this->db->select('*');
		$this->db->where('id', $roleId);
		$get_resource_table= $this->db->get('role_master');
		$get_table = $get_resource_table->row();
		return $get_table;
	}

    public function updateRole($roleId,$data)
	{
        $this->db->set('update_at',time());
		$this->db->set($data);
		$this->db->where('id',$roleId);
		$resultArr = $this->db->update('role_master');
		return $resultArr;
	}

    public function deleteRole($roleId)
	{
		$this->db->where('role_id',$roleId);
		$resultArr = $this->db->delete('role_resource');
		return $resultArr;
	}

    public function delete_role($id)
	{
        $this->db->set('remove_flag',1);
        $this->db->set('update_at',time());
        $this->db->where('id',$id);
        $resultArr = $this->db->update('role_master');
        return true;
	}

    function child_data_by_id($roleId) {
        $this->db->select('resource_master.*,role_resource.*,resource_master.id as childId');
		$this->db->from('role_resource');
		$this->db->join('resource_master','resource_master.id=role_resource.resource_id','LEFT');
		$this->db->where('role_id',$roleId);
		$get_resource_table = $this->db->get();
		$get_table = $get_resource_table->result_array();
		return $get_table;
    }
}