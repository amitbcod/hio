<?php

defined('BASEPATH') or exit('No direct script access allowed');



###+------------------------------------------------------------------------------------------------

###| BCOD WEB SOLUTIONS PVT. LTD., MUMBAI [ www.bcod.co.in ]

###+------------------------------------------------------------------------------------------------

###| Code By - Ketan Vyas (ketanv@bcod.co.in)

###+------------------------------------------------------------------------------------------------

###| Date - Dec 2022

###+------------------------------------------------------------------------------------------------

use App\Services\Trackers\ShipmentStatusEnum;

class DashboardModel extends CI_Model {



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



    public function check_user_email_exists($curr_email, $id) {

        $this->db->where('email', $curr_email);

        $this->db->where('id', $id);

        $query = $this->db->get('adminusers');

        if($query->num_rows() > 0) {

			return true;

		}

		else {

			return false;

		}

    }



	public function check_user_exists($id) {

		$this->db->where('id', $id);

		$query = $this->db->get('adminusers');

		return $query->result_array();

	}



    public function update_admin_user_email($data)

    {

		$this->db->where($data['condition']);

		$query = $this->db->update('adminusers', $data['data']);

		if($query) return true;

		else return false;

    }



    public function check_user_password_exists($curr_pass, $id) {

        $this->db->where('password', $curr_pass);

        $this->db->where('id', $id);

        $query = $this->db->get('adminusers');

        if($query->num_rows() > 0) {

			return true;

		}

		else {

			return false;

		}

    }



    public function update_admin_user_password($data)

    {

		$this->db->where($data['condition']);

		$query = $this->db->update('adminusers', $data['data']);

		if($query) return true;

		else return false;

    }



    public function update_admin_user_details($conditions)

    {

		$this->db->where($conditions['condition']);

		$query = $this->db->update('adminusers', $conditions['data']);

		if($query) return true;

		else return false;

    }

	public function get_avg_ratings_by_merchant($merchant_id) {
        $this->db->select('merchant_id, AVG(rating) as avg_rating');
        $this->db->from('product_reviews');
        $this->db->where('merchant_id', $merchant_id);
        $this->db->group_by('merchant_id');
        $query = $this->db->get();
        return $query->row(); // one merchant
    }

}



?>