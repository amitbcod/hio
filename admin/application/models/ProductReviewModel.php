<?php
class ProductReviewModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getProductReviewList($page='',$limit='',$search_param='')
	{

		$this->db->select('pr.*,p.name,p.id as product_id, c.first_name, c.last_name, c.email_id, c.id as customer_id,');
		$this->db->from('products_reviews pr');
		// $this->db->or_where('pr.country','');
		$this->db->join('products p','p.id = pr.product_id','Left');
		$this->db->join('customers c','c.id = pr.customer_id','Left');

		if(isset($search_param['keyword']) && $search_param['keyword'] !="")
		{
			$this->db->group_start();
			$this->db->like('c.first_name',$search_param['keyword']);
			$this->db->or_like('c.last_name',$search_param['keyword']);
			if(isset($search_param['keyword']))
			{
				$fullname= explode(" ",$search_param['keyword']);
				$fname= $fullname[0];
				$this->db->or_like('c.first_name',$fname);
				if(isset($fullname[1]))
				{
					$lname= $fullname[1];
					$this->db->or_like('c.first_name',$lname);
				}
			}
			$this->db->or_like('p.name',$search_param['keyword']);
			$this->db->or_like('pr.review',$search_param['keyword']);
			$this->db->or_like('pr.rating',$search_param['keyword']);
			$this->db->group_end();
		}

		if ($limit != '' && $page != '') {
			$this->db->limit($limit, $page);
		 }

		$this->db->order_by('pr.id', 'DESC');
		$result = $this->db->get();
		 //echo $this->db->last_query();exit;
		if($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return false;
		}
	}

	public function getProductReviewCount($search_param='')
	{

		$this->db->select('pr.id');
		$this->db->from('products_reviews pr');
		$this->db->where('pr.status',1);
		$this->db->join('products p','p.id = pr.product_id','Left');
		$this->db->join('customers c','c.id = pr.customer_id','Left');

		if(isset($search_param['keyword']) && $search_param['keyword'] !="")
		{
			$this->db->group_start();
			$this->db->like('c.first_name',$search_param['keyword']);
			$this->db->or_like('c.last_name',$search_param['keyword']);
			if(isset($search_param['keyword']))
			{
				$fullname= explode(" ",$search_param['keyword']);
				$fname= $fullname[0];
				$this->db->or_like('c.first_name',$fname);
				if(isset($fullname[1]))
				{
					$lname= $fullname[1];
					$this->db->or_like('c.first_name',$lname);
				}
			}
			$this->db->or_like('p.name',$search_param['keyword']);
			$this->db->or_like('pr.review',$search_param['keyword']);
			$this->db->or_like('pr.rating',$search_param['keyword']);
			$this->db->group_end();
		}
		$this->db->order_by('pr.id', 'DESC');
		$result = $this->db->get();

		if($result->num_rows() > 0) {
			return $result->num_rows();
		} else {
			return false;
		}
	}

	public function getProductReviewById($review_id)
	{

		$this->db->select('pr.*,p.name,p.id as product_id, c.first_name, c.last_name, c.email_id, c.id as customer_id,');
		$this->db->from('products_reviews pr');
		$this->db->where('pr.status',1);
		$this->db->where('pr.id',$review_id);
		$this->db->join('products p','p.id = pr.product_id','Left');
		$this->db->join('customers c','c.id = pr.customer_id','Left');


		$result = $this->db->get();
		if($result->num_rows() > 0) {
			return $result->row_array();
		} else {
			return false;
		}
	}

    public function deleteData($tableName,$condition)
    {
		$this->db->where($condition);
		$this->db->delete($tableName);
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
    }
    public function change_reviews_status($status, $id)
	{
		$this->db->set('status', $status);
		$this->db->where('id', $id);
		$query = $this->db->update('products_reviews');
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
	}
}
