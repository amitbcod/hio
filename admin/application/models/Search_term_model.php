<?php 
class Search_term_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		
	}

	public function get_all_search_terms()
	{
        $main_db_name=$this->db->database;

        $this->db->select('id,search_term,popularity');
        $this->db->from('search_terms');
        $this->db->order_by("popularity", "DESC");
		$query = $this->db->get();
		$resultArr = $query->result_array();
		return $resultArr;

    } 
	
}
