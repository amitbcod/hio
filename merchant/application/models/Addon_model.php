<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Addon_model extends CI_Model {

    public function get_addons_with_services()
    {
        $this->db->select('c.id as category_id, c.name as category_name, 
                           s.id as service_id, s.title, s.description, s.price');
        $this->db->from('addon_categories c');
        $this->db->join('addon_services s', 's.category_id = c.id', 'left');
        $this->db->where('c.status', 1);
        $this->db->where('s.status', 1);
        $this->db->order_by('c.id', 'ASC');
        $this->db->order_by('s.id', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_service_by_id($service_id)
    {
        $this->db->select('s.*, c.name as category_name');
        $this->db->from('addon_services s');
        $this->db->join('addon_categories c', 's.category_id = c.id', 'left');
        $this->db->where('s.id', $service_id);
        $query = $this->db->get();
        return $query->row_array();
    }
}
