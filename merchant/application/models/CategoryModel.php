<?php
class CategoryModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	public function get_category_for_seller()
    {   
		$this->db->select('*');
		$this->db->where('status',1);
		$this->db->where('cat_level',0);
		$this->db->order_by('cat_name','asc');	
		$query = $this->db->get('category');
		$resultArr = $query->result_array();
		//echo $this->db->last_query();
		return $resultArr;
    }
	
	public function get_child_category($parent_id)
    {         
    	$this->db->order_by('cat_name','ASC');
        $query = $this->db->get_where('category',array('parent_id'=>$parent_id,'status'=>'1','cat_level'=>'1'));
        $resultArr = $query->result_array();
		return $resultArr;
    }
	
	public function get_child_category_for_seller($parent_cat_id,$cat_level)
    {        
		$this->db->select('*');
		$this->db->where('status',1);
		$this->db->where('parent_id',$parent_cat_id);		
		$this->db->where('cat_level',$cat_level);
		$this->db->order_by('cat_name','asc');	
		$query = $this->db->get('category');
		$resultArr = $query->result_array();
		//echo $this->db->last_query();
		return $resultArr;
    }
	
	public function check_child_category_exist($parent_cat_id,$cat_level,$cat_name)
    {        
		$this->db->select('*');
		$this->db->where('status',1);
		$this->db->where('cat_name',$cat_name);
		$this->db->where('parent_id',$parent_cat_id);		
		$this->db->where('cat_level',$cat_level);
		$this->db->order_by('cat_name','asc');	
		$query = $this->db->get('category');
		$resultArr = $query->row_array();
		//echo $this->db->last_query();
		return $resultArr;
    }
	
	public function check_child_category_exist_by_id($parent_cat_id,$cat_level,$id)
    {        
		$this->db->select('*');
		$this->db->where('status',1);
		$this->db->where('id',$id);
		$this->db->where('parent_id',$parent_cat_id);		
		$this->db->where('cat_level',$cat_level);
		$this->db->order_by('cat_name','asc');	
		$query = $this->db->get('category');
		$resultArr = $query->row_array();
		//echo $this->db->last_query();
		return $resultArr;
    }
	
	
	
	
		public function get_category_detail($id)
	{
		$result = $this->db->get_where('category',array('id'=>$id))->row();		
		return $result ;
		
	}	
	
	 //insertData
	public function insertData($table,$data)
	{
		 $this->db->reset_query();
		  
		$this->db->insert($table,$data);
		if($this->db->affected_rows() > 0)
		{
			$last_insert_id=$this->db->insert_id();
		  return $last_insert_id;
		}
		else
		{
		  return false;
		}
	}
  
	public function updateData($tableName,$condition,$updateData)
    {
      $this->seller_db->update($tableName,$updateData,$condition);
      if($this->seller_db->affected_rows() > 0)
      {
        return true;
      }
      else
      {
        return false;
      }
    }
	
		public function get_category_names_by_ids($ids)
    {   
		$this->db->select('GROUP_CONCAT(cat_name) as cat_name');
		$this->db->where('status',1);
		$this->db->where_in('id',$ids);
		$this->db->order_by('id','asc');	
		$query = $this->db->get('category');
		$resultArr = $query->row();
		
		return $resultArr;
    }
	
	public function check_category_exist_by_level($cat_level,$cat_name)
    {        
		$this->db->select('*');
		$this->db->where('status',1);
		$this->db->where('cat_name',$cat_name);	
		$this->db->where('cat_level',$cat_level);
		$this->db->order_by('cat_name','asc');	
		$query = $this->db->get('category');
		$resultArr = $query->row_array();
		//echo $this->db->last_query();
		return $resultArr;
    }
	
	
	/************************Category Tree*****************************/
	public function get_categories_for_shop(){

        $this->db->select('*');
        $this->db->from('category');
		$this->db->where('status',1);
		$this->db->where('cat_level',0);
		$this->db->order_by('cat_name','asc');	
        $parent = $this->db->get();
        
        $categories = $parent->result_array();
        $i=0;
		if(isset($categories) && count($categories)>0){
			foreach($categories as $p_cat){

				$categories[$i]['sub_category'] = $this->sub_categories_for_shop($p_cat['id']);
				$i++;
			}
		}
        return $categories;
    }

    public function sub_categories_for_shop($parent_id){

        $this->db->select('*');
        $this->db->from('category');
        $this->db->where('parent_id', $parent_id);
		$this->db->where('status',1);
		$this->db->order_by('cat_name','asc');	

        $child = $this->db->get();
        $categories = $child->result_array();
        $i=0;
		if(isset($categories) && count($categories)>0){
			foreach($categories as $p_cat){

				$categories[$i]['sub_category'] = $this->sub_categories_for_shop($p_cat['id']);
				$i++;
			}
		}
        return $categories;       
    }

	
	public function check_category_exist_by_cat_id($cat_level,$id)
    {        
		$this->db->select('*');
		$this->db->where('status',1);
		$this->db->where('id',$id);	
		$this->db->where('cat_level',$cat_level);
		$this->db->order_by('cat_name','asc');	
		$query = $this->db->get('category');
		$resultArr = $query->row_array();
		//echo $this->db->last_query();
		return $resultArr;
    }
	
	public function check_category_exist_by_slug($cat_level,$cat_name,$exclude_cid='')
    {        
		$this->db->select('*');
		$this->db->where('status',1);
		$this->db->where('cat_name',$cat_name);	
		//$this->db->where('cat_level',$cat_level);
		if(isset($exclude_cid) && $exclude_cid!=''){
			$this->db->where('id <>',$exclude_cid);
		}
		$this->db->order_by('cat_name','asc');	
		$query = $this->db->get('category');
		$resultArr = $query->num_rows();
		//echo $this->db->last_query();
		return $resultArr;
    }
	
	
	
	
}
