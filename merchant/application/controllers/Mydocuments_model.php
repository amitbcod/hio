<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mydocuments_model extends CI_Model {

    public function get_mydocuments($publisher_id)
    {
        return $this->db
            ->where('merchant_id', $publisher_id)
            ->get('mydocuments')
            ->result_array();
    }
    public function get_mydocuments_data($id)
    {
        return $this->db
            ->where('id', $id)
            ->get('mydocuments')
            ->row();
    }

}
