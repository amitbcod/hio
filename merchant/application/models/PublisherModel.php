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



	public function get_city()

    {

		$this->db->select('*');

		$query = $this->db->get('city_master');

		$resultArr = $query->result_array();

		return $resultArr;

    }
	public function get_states()

    {

		$this->db->select('*');

		$query = $this->db->get('country_state_master_in');

		$resultArr = $query->result_array();

		return $resultArr;

    }

	public function getCitiesByState($state_id)
    {
        return $this->db
            ->select('*')
            ->from('city_master')
            ->where('state_id', $state_id)
            ->get()
            ->result();
    }

	public function getMerchantById($publisher_id)
    {
        return $this->db
            ->select('*')
            ->from('publisher')
            ->where('id', $publisher_id)
            ->get()
            ->result();
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



	/*public function update_publishers($publisher_data,$publisher_id){



		$this->db->where('id',$publisher_id);					

		return  $this->db->update('publisher', $publisher_data);

	}*/
public function update_publishers($publisher_data, $publisher_id, $publisher_payment_details_data)
	{
		// Update 'publisher' table
		$this->db->where('id', $publisher_id);
		$this->db->update('publisher', $publisher_data);
		// echo $this->db->last_Query();


		$this->db->where('publisher_id', $publisher_id);
		$result = $this->db->get('publisher_payment_details');
		
		if ($result->num_rows() > 0) {
			// ID exists, perform an update
			$this->db->where('publisher_id', $publisher_id);
			$this->db->update('publisher_payment_details', $publisher_payment_details_data);
		} else {
			// ID does not exist, perform an insert
			$this->db->insert('publisher_payment_details', $publisher_payment_details_data);
		}


		// Update 'publisher_payment_details' table

		// echo $this->db->last_Query();
		// die;

		// Return true if both updates were successful
		return $this->db->affected_rows() > 0;
	}


	public function delete_publishers($publisher_id){



		$updateData=array(  

			'remove_flag'=>1,

		);



		$this->db->where('id',$publisher_id);					

		return  $this->db->update('publisher', $updateData);

	}



    public function get_publisher_detail($id)

	{

		$result = $this->db->get_where('publisher',array('id'=>$id))->row();

		return $result ;



	}

public function get_publisher_payment_details($id)
	{
		$result = $this->db->get_where('publisher_payment_details', array('publisher_id' => $id))->row();
		return $result;
	}

}

?>