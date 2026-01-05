<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminuserModel extends CI_Model {

		public function email_exists($email, $id='')
		{
			$this->db->where('email', $email);
				if($id!=''){
					$this->db->where('id !=', $id);
				}
				$query = $this->db->get('adminusers');
				if($query->num_rows() > 0) {
					return true;
				}
				else {
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

	public function get_datatables_adminuser_details() {
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->get_datatables_all_adminuser_deatils($term);
		if($_REQUEST['length'] != -1)
		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_datatables_all_adminuser_deatils($term ='') {

		$column = array('au.id','au.first_name','au.email','au.username','rm.role_name','au.status','');

		$this->db->select('au.*,rm.id as role_id, rm.role_name,');
		$this->db->from('adminusers au');
		$this->db->join('role_master rm', 'rm.id=au.role_id', 'left');
		

		if($term!=''){
			$this->db->where("(
				au.first_name LIKE '%$term%'
				    OR au.last_name LIKE '%$term%'
				    OR au.username LIKE '%$term%'
                    OR au.email LIKE '%$term%'
                )");
		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}else{
			$this->db->order_by("au.id", "asc");
		}
	}

	function countadminuserrecord() {
		$this->db->select('*');
		$this->db->from('adminusers');
		//$this->db->where('status',1);
		$query = $this->db->count_all_results();
		return $query;
	}

	function countfilteradminuserrecord() {
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->get_datatables_all_adminuser_deatils($term);
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function insert_user($user) {
		return $this->db->insert('adminusers', $user);
	}

	public function display_records_by_id($id) {
		$this->db->where('id', $id);
		$user_data = $this->db->get('adminusers')->row_array();
		$role_id = isset($user_data['role_id']) ? $user_data['role_id'] : 0;
		if($role_id) {
			$this->db->select('adminusers.*,role_master.id as role_id, role_master.role_name,');
			$this->db->where('adminusers.id', $id);
			$this->db->join('role_master', 'role_master.id=adminusers.role_id');
			$query = $this->db->get('adminusers');
			return $query->row_array();
		}

		return $user_data;
	}

	public function update_admin_user_details($conditions)
    {
		$this->db->where($conditions['condition']);
		$query = $this->db->update('adminusers', $conditions['data']);
		if($query) return true;
		else return false;
    }

	public function delete_admin_user_records($id) {
		$this->db->where('id', $id);

		$query = $this->db->delete('adminusers');

		if($query) return true;
		else return false;
	}

	public function get_role_type() {
		$query = $this->db->get('role_master');
		return $query->result_array();
	}
}


?>
