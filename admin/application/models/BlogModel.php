<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BlogModel extends CI_Model {

  public function blog_exists($name)
  {
    $this->db->where('title', $name);
      $query = $this->db->get('blogs');
      if($query->num_rows() > 0) {
        return true;
      }
      else {
        return false;
      }
    }

    public function blog_details_exists($name)
    {
      $this->db->where('title', $name);
        $query = $this->db->get('blogs_details');
        if($query->num_rows() > 0) {
          return true;
        }
        else {
          return false;
        }
      }

    function get_products_details($search='') {
        $this->db->select('id, name');
        $this->db->from('products');
        $this->db->where('remove_flag', 0);
        $this->db->where('product_type !=', 'conf-simple');

        if($search!=''){
			$this->db->where("(
                name LIKE '%$search%'
			)");
		}

        $query = $this->db->get();
        $products = $query->result_array();

        // Initialize Array with fetched data
      $data = array();
      foreach($products as $product){
          $data[] = array("id"=>$product['id'], "text"=>$product['name']);
      }
      return $data;
    }

    function insert_blogs($data) {
        $this->db->insert('blogs', $data);

        $last_id = $this->db->insert_id();

        return $last_id;
    }

    function insert_blogs_details($data, $id) {

        for($i=0; $i<count($data['title']); $i++) {
            $data_new = array(
                'blog_id' => $id,
                'title' => $data['title'][$i],
                'description' => $data['description'][$i],
                'product_id'=>$data['product_id'][$i],
                'display_subscription_details' =>$data['display_subscription_details'][$i]
             );

             $this->db->insert('blogs_details', $data_new);
        }

        return true;
    }

    function get_datatables_blogs_details() {
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->get_datatables_all_blogs_deatils($term);
		if($_REQUEST['length'] != -1)
		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->db->get();
		return $query->result();
	}

    function get_datatables_all_blogs_deatils($term ='') {

		$column = array('id','title','description','url_key','status','created_at', '');

		$this->db->select('*,');
		$this->db->from('blogs');


		if($term!=''){
			$this->db->where("(
                title LIKE '%$term%'
			)");
		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}else{
			$this->db->order_by("id", "asc");
		}
	}

    function countblogrecord() {
		$this->db->select('*');
		$this->db->from('blogs');
		//$this->db->where('status',1);
		$query = $this->db->count_all_results();
		return $query;
	}

	function countfilterblogrecord() {
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->get_datatables_all_blogs_deatils($term);
		$query = $this->db->get();
		return $query->num_rows();
	}

    function delete_blogs_record($id) {
        $this->db->where('id', $id);

		$query = $this->db->delete('blogs');

		if($query) return true;
		else return false;
    }

    function delete_blogs_details_record($id) {
        $this->db->where('blog_id', $id);

		$query = $this->db->delete('blogs_details');

		if($query) return true;
		else return false;
    }

    function delete_blog_detail_record($id) {
        $this->db->where('id', $id);

		$query = $this->db->delete('blogs_details');

		if($query) return true;
		else return false;
    }

    function get_blogs_data($id) {
        $this->db->where('id', $id);

        $query = $this->db->get('blogs');
		return $query->row_array();
    }

    function get_blogs_details_data($id) {
        $this->db->where('blog_id', $id);

        $query = $this->db->get('blogs_details');
		return $query->result_array();
    }

    function update_blogs($conditions)
    {
		$this->db->where($conditions['condition']);
		$query = $this->db->update('blogs', $conditions['data']);
		if($query) return true;
		else return false;
    }

    public function updateNewData($tableName,$condition,$updateData)
    {
		$this->db->where($condition);
		$this->db->update($tableName,$updateData);
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
    }

    public function insertData($table,$data)
	{
	    $this->db->insert($table,$data);
	    if($this->db->affected_rows() > 0)
	    {
			$last_insert_id=$this->db->insert_id();
	      	return $last_insert_id;
	    }else{
	      	return false;
	    }
	}

}

?>
