<?php
class CategoryNewModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function checkSlugExist($slug)
    {
        $result = $this->db->get_where('category', array('slug' => $slug,'parent_id'=> 0, 'cat_level'=> 0,'main_parent_id' => 0))->result();
        return $result;
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

    public function checkSubSlugExist($slug)
    {
        $result = $this->db->get_where('category', array('slug' => $slug))->result();
        return $result;
    }

    public function checkSlugExistItself($slug,$id)
    {
        $result = $this->db->get_where('category', array('slug' => $slug,'parent_id'=> 0, 'cat_level'=> 0,'main_parent_id' => 0, 'id !='=>$id))->result();
        return $result;
    }

	public function getAttribute()
    {
        $this->db->select('id,attr_name,is_default');
        $this->db->from('eav_attributes');
        $this->db->where('attr_type',1);
        $this->db->where('created_by_type',0);
        $this->db->where('status',1);
        $this->db->order_by('is_default','DESC');
        $result = $this->db->get();
        return $result->result();
    }

    public function getSubCategoryList()
    {
        $result =$this->db->query("
            SELECT C.*, 
            (select P.id from category P where P.id = C.parent_id and P.parent_id = 0) as catID ,
            (select P.cat_name from category P where P.id = C.parent_id and  P.parent_id = 0) as catName 
            FROM category C
            where C.cat_level = 1 ORDER BY C.id DESC;");
           // print_r($this->db->last_query());exit;

        return $result->result();
    }

    public function check_cat_level($id){
        $this->db->select('id,cat_level,parent_id,main_parent_id');
		$this->db->from('category');
        $this->db->where('id', $id);
		$result = $this->db->get();
        return $result->row();
    }

    public function getAllCategories()
	{
		$this->db->select('main_cat.id,main_cat.cat_name,main_cat.cat_level,main_cat.status');
		$this->db->from('category main_cat');
		$this->db->where('main_cat.cat_level',0);
		$result = $this->db->get();

		if($result->num_rows() > 0)
		{
			$final_arr = array();
			$browseByCategory = $result->result_array();

			foreach ($browseByCategory as $cat)
			{
				$firstLevelCategory = $this->firstLevelCategory($cat['id']);
                
				if($firstLevelCategory != false)
				{
					foreach($firstLevelCategory as $cat1)
					{
						$secondLevelCategory = $this->secondLevelCategory($cat1['id'],$cat['id']);
						if($secondLevelCategory != false)
						{
							foreach($secondLevelCategory as $cat2) {
								$arr2['id'] = $cat2['id'];
								$arr2['cat_name'] = $cat2['cat_name'];
								$arr2['c
								at_level'] = $cat2['cat_level'];
								$arr2['category_id'] = isset($cat2['category_id']) ? $cat2['category_id'] : '';
								$cat1['cat_level_2'][] = $arr2;

						        $thirdLevelCategory = $this->thirdLevelCategory($cat2['id'],$cat['id']);
                                if($thirdLevelCategory !=false){
                                    foreach ($thirdLevelCategory as $cat3) {
								        $cat2['cat_level_3'][] = $cat3;

                                    }
                                }
                               
							}
						}
						$cat['cat_level_1'][] = $cat1;
					}
				}

				$final_arr[] = $cat;
			}
           // echo '<pre>';print_r($final_arr);exit;

			return $final_arr;
		}else{
			return false;
		}

	}

    public function firstLevelCategory($category_id)
	{
		$this->db->select('cat_level1.id,cat_level1.cat_name,cat_level1.cat_level,cat_level1.status');
		$this->db->from('category cat_level1');
		$this->db->where('cat_level1.parent_id',$category_id);
		$this->db->where('cat_level1.cat_level',1);
		
		$result = $this->db->get();
		if($result->num_rows() > 0)
		{
			return $result->result_array();
		}else{
			return false;
		}

	}

    public function thirdLevelCategory($cat_parent_id,$cat_main_parent_id)
	{
		$this->db->select('cat_level3.id,cat_level3.cat_name,cat_level3.cat_level');
		$this->db->from('category cat_level3');
		$this->db->where('cat_level3.parent_id',$cat_parent_id);
		$this->db->where('cat_level3.main_parent_id',$cat_main_parent_id);
		$this->db->where('cat_level3.cat_level',3);
		
		$result = $this->db->get();
		if($result->num_rows() > 0)
		{
			return $result->result_array();
		}else{
			return false;
		}

	}

    public function secondLevelCategory($cat_parent_id,$cat_main_parent_id)
	{
		$this->db->select('cat_level2.id,cat_level2.cat_name,cat_level2.cat_level,cat_level2.status');
		$this->db->from('category cat_level2');
		$this->db->where('cat_level2.parent_id',$cat_parent_id);
		$this->db->where('cat_level2.main_parent_id',$cat_main_parent_id);
		$this->db->where('cat_level2.cat_level',2);
		
		$result = $this->db->get();
		if($result->num_rows() > 0)
		{
			return $result->result_array();
		}else{
			return false;
		}

	}

    public function getVariant()
    {
        $this->db->select('id,attr_name,is_default');
        $this->db->from('eav_attributes');
        $this->db->where('attr_type',2);
        $this->db->where('created_by_type',0);
        $this->db->where('status',1);
        $this->db->order_by('is_default','DESC');
        $result = $this->db->get();
        return $result->result();
    }

    public function getCategory()
    {
        $this->db->select('id,cat_name');
        $this->db->from('category');
        $this->db->where('parent_id',0);
        $this->db->where('main_parent_id',0);
        $this->db->where('cat_level',0);
        //$this->db->where('status',1);
        $this->db->where('created_by_type',0);
        $this->db->order_by('cat_name');
        $result = $this->db->get();
        return $result->result();
    }

    public function getCategorybyID($cate_id)
    {
        $this->db->select('*');
        $this->db->from('category');
        $this->db->where('parent_id',0);
        $this->db->where('main_parent_id',0);
        $this->db->where('cat_level',0);
        //$this->db->where('status',1);
        $this->db->where('created_by_type',0);
        $this->db->where('id',$cate_id);
        $this->db->order_by('cat_name');
        $result = $this->db->get();
        return $result->row();
    }

    public function getSubCategorybyID($cate_id)
    {
        $this->db->select('*');
        $this->db->from('category');
        $this->db->where('parent_id',$cate_id);
        $this->db->where('main_parent_id',$cate_id);
        $this->db->where('cat_level',1);
        $this->db->where('status',1);
        $this->db->where('created_by_type',0);
        $this->db->order_by('cat_name');
        $result = $this->db->get();
        return $result->result();
    }


    public function getSubCateID($subcat_id,$cate_id)
    {
        $this->db->select('*');
        $this->db->from('category');
        $this->db->where('parent_id',$cate_id);
        //$this->db->where('main_parent_id',$cate_id);
        $this->db->where('cat_level',1);
        //$this->db->where('status',1);
        $this->db->where('created_by_type',0);
        $this->db->order_by('cat_name');
        $this->db->where('id',$subcat_id);
        $result = $this->db->get();
        return $result->row();
    }

    public function geSubCategoryTagData($subcat_id)
    {
        $this->db->select('*');
        $this->db->from('category');
        $this->db->where('parent_id',$subcat_id);
        $this->db->where('cat_level',2);
        $this->db->where('status',1);
        $this->db->where('created_by_type',0);
        $this->db->order_by('cat_name');
        $result = $this->db->get();
        return $result->result();
    }


		// added by al
		
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
	
	public function get_child_category_for_seller($shop_id,$parent_cat_id,$cat_level)
    {        
		$this->db->select('*');
		$this->db->where('status',1);
		$this->db->where('parent_id',$parent_cat_id);		
		$this->db->where('cat_level',$cat_level);
		$this->db->where("(`shop_id` = $shop_id OR `created_by_type` = 0)");
		$this->db->order_by('cat_name','asc');	
		$query = $this->db->get('category');
		$resultArr = $query->result_array();
		//echo $this->db->last_query();
		return $resultArr;
    }


}
