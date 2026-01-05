<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TestimonialModel extends CI_Model {

  public function name_exists($name)
  {
    $this->db->where('client_name', $name);
      $query = $this->db->get('testimonials');
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

    function insert_testimonial($user) {
		return $this->db->insert('testimonials', $user);
	}

    function get_datatables_testimonials_details() {
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->get_datatables_all_testimonials_deatils($term);
		if($_REQUEST['length'] != -1)
		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->db->get();
		return $query->result();
	}

    function get_datatables_all_testimonials_deatils($term ='') {

		$column = array('id','client_name','client_description','client_company','status','');

		$this->db->select('*,');
		$this->db->from('testimonials');


		if($term!=''){
			$this->db->where("(
                client_name LIKE '%$term%'
				OR client_description LIKE '%$term%'
				OR client_company LIKE '%$term%'
				OR website LIKE '%$term%'
                OR testimonial LIKE '%$term%'
			)");
		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}else{
			$this->db->order_by("id", "asc");
		}
	}

    function counttestimonialrecord() {
		$this->db->select('*');
		$this->db->from('testimonials');
		//$this->db->where('status',1);
		$query = $this->db->count_all_results();
		return $query;
	}

	function countfiltertestimonialrecord() {
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->get_datatables_all_testimonials_deatils($term);
		$query = $this->db->get();
		return $query->num_rows();
	}

    function delete_testimonial_record($id) {
        $this->db->where('id', $id);

		$query = $this->db->delete('testimonials');

		if($query) return true;
		else return false;
    }

    function display_records_by_id($id) {
        $this->db->where('id', $id);

        $query = $this->db->get('testimonials');
		return $query->row_array();
    }

    function update_testimonial_details($conditions)
    {
		$this->db->where($conditions['condition']);
		$query = $this->db->update('testimonials', $conditions['data']);
		if($query) return true;
		else return false;
    }

	function getTestimonialsDetails(){

        $response = array();

        // Select record
        $this->db->select('*');
        $q = $this->db->get('testimonials');
        $response = $q->result_array();

        return $response;
    }
}

?>
