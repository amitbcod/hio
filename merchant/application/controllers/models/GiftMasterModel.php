<?php
class GiftMasterModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

    public function get_gift_master()
    {
		$this->db->select('*');
		$this->db->order_by('id','DESC');
		$query = $this->db->get('gift_master');
		$resultArr = $query->result_array();
		return $resultArr;
    }

	public function insert_gift_master($gift_master_data){

		return $this->db->insert('gift_master',$gift_master_data);
	}

	public function update_gift_master($gift_master_data,$gift_master_id){

		$this->db->where('id',$gift_master_id);					
		return  $this->db->update('gift_master', $gift_master_data);
	}

}
?>