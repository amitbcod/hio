<?php
class PublisherModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

    public function get_publishers()
    {
		$this->db->select('*');
		$this->db->where('remove_flag',0);
		$this->db->order_by('id','DESC');
		$query = $this->db->get('publisher');
		$resultArr = $query->result_array();
		return $resultArr;
    }


    public function checkEmailidExit($emailid){
        $this->db->select('*');
		$this->db->where('email',$emailid);
		$query = $this->db->get('publisher');
        return $query->num_rows();
    }

	public function checkEmailIdExitDuringUpdate($emailid,$publisher_id){
        $this->db->select('*');
		$this->db->where('email',$emailid);
		$this->db->where_not_in('id',$publisher_id);
		$query = $this->db->get('publisher');
        return $query->num_rows();
    }

    public function insert_publishers($publisher_data){

		return $this->db->insert('publisher',$publisher_data);
	}

	public function update_publishers($publisher_data,$publisher_id){

		$this->db->where('id',$publisher_id);					
		return  $this->db->update('publisher', $publisher_data);
	}

	public function delete_publishers($publisher_id){

		$updateData=array(  
			'remove_flag'       =>1,
		);

		$this->db->where('id',$publisher_id);					
		return  $this->db->update('publisher', $updateData);
	}

    public function get_publisher_detail($id)
	{
		$result = $this->db->get_where('publisher',array('id'=>$id))->row();
		return $result ;

	}

}
?>