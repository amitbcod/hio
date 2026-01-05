<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Shop_model extends CI_Model {



    public function __construct() {

        parent::__construct();

    }



    public function get_count($state = null, $city = null, $zipcode = null)

    {

        $this->db->from('publisher');



        if (!empty($state)) {

            $this->db->where('state', $state);

        }

        if (!empty($city)) {

            $this->db->where('city', $city);

        }

        if (!empty($zipcode)) {

            $this->db->where('zipcode', $zipcode);

        }



        return $this->db->count_all_results();

    }



    public function get_shops($limit, $offset, $state = null, $city = null, $zipcode = null)

    {

        $this->db->select('*');

        $this->db->from('publisher');



        if (!empty($state)) {

            $this->db->where('state', $state);

        }

        if (!empty($city)) {

            $this->db->where('city', $city);

        }

        if (!empty($zipcode)) {

            $this->db->where('zipcode', $zipcode);

        }

        $this->db->where('status', '1');

        $this->db->limit($limit, $offset);

        $query = $this->db->get();

        return $query->result();

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

    public function shop_details($id) {

        $this->db->select('*'); // Later you can join with images if needed

        $this->db->from('publisher');

        $this->db->where('id', $id);  // <-- FIXED

        $query = $this->db->get();

        return $query->row(); // return single record, not array

    }

    public function get_shops_products($shop_id, $limit, $offset) {

        return $this->db->where('publisher_id', $shop_id)

                        ->limit($limit, $offset)

                        ->get('products')

                        ->result();

    }



    public function get_count_products($shop_id) {

        return $this->db->where('publisher_id', $shop_id)

                        ->count_all_results('products');

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

