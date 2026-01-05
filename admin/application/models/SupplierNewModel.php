<?php
class SupplierNewModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);
			if($this->seller_db->conn_id) {
				//do something
			} else {
				redirect(base_url());
			}
		}else{
			redirect(base_url());
		}
	}


	public function getProductLaunchDates($shop_id){

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$this->seller_db->distinct();
			$this->seller_db->select('p.launch_date');
			$this->seller_db->from('products as p');
			$this->seller_db->where('p.remove_flag','0');
			$this->seller_db->order_by('p.launch_date','DESC');

			$query = $this->seller_db->get();

			return $query->result();
		}

	}

	public function getPopularSuppliersList($search_param='')
    {
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		//print_r($search_param);
		$this->db->select('FUS.fbc_user_id, FUS.shop_id, FUS.org_shop_name as webshop_name, FU.owner_name');
		$this->db->from('fbc_users_shop FUS');
		$this->db->join('fbc_users FU', 'FU.fbc_user_id = FUS.fbc_user_id', 'inner' );
		$this->db->where(array('FU.status'=>1, 'FUS.b2b_status'=>1, 'FUS.shop_id !=' => $shop_id));

		// Shop, owner, category name - keyword
		if(isset($search_param['keyword']) && $search_param['keyword'] !="")
		{
			$this->db->group_start();
			$this->db->like('FU.owner_name',$search_param['keyword']);
			$this->db->or_like('FUS.org_shop_name',$search_param['keyword']);
			$this->db->or_like('FUS.company_name',$search_param['keyword']);
			$this->db->group_end();
		}
        $query = $this->db->get();
		//print_r($this->db->last_query());
        $result = $query->result();
		//print_r($result);
		$finalResult = array();
		$b2bCatArr = array();
		$CatArr = array();
		if(isset($search_param['keyword']) && $search_param['keyword'] !=""){
			if(empty($result )){
				$cateDetails=$this->getCategoryDetail('',$search_param);
				if(is_array($cateDetails) && count($cateDetails) > 0){
					foreach($cateDetails as $cat1){
						$CatArr[] = $cat1->id;
					}

					$this->db->select('FUS.fbc_user_id, FUS.shop_id, FUS.org_shop_name as webshop_name, FU.owner_name');
					$this->db->from('fbc_users_shop FUS');
					$this->db->join('fbc_users FU', 'FU.fbc_user_id = FUS.fbc_user_id', 'inner' );
					$this->db->where(array('FU.status'=>1, 'FUS.b2b_status'=>1, 'FUS.shop_id !=' => $shop_id));
					$query = $this->db->get();
					//print_r($this->db->last_query());
					$result1 = $query->result();
					// print_r($result);
					foreach($result1 as $key=>$value){
						$fbc_user_id = $value->fbc_user_id;
						$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$value->shop_id),'shop_id,fbc_user_id,database_name');
						if(isset($FBCData) && $FBCData->database_name!='')
						{
							$fbc_user_database=$FBCData->database_name;

							$this->load->database();
							$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
							$this->seller_db = $this->load->database($config_app,TRUE);
							$b2bCat=$this->getB2BCatCountByUserID($value->fbc_user_id);
							$b2bCatCount =   $b2bCat;  //(is_array($b2bCat) && count($b2bCat) > 0) ? count($b2bCat) : 0;
							if($b2bCatCount > 0){
								$b2bCat=$this->getB2BCatByUserID($value->fbc_user_id);
								foreach($b2bCat as $cat){
									$b2bCatArr[] = $cat->category_id;
								}
							}
						}

						if(array_intersect($CatArr,$b2bCatArr)){
							$finalResult[] = (object)array('shop_id' => $value->shop_id, 'owner_name' => $value->owner_name, 'webshop_name' => $value->webshop_name, 'cat_count' => $b2bCatCount);
						}
					}
				}
			}
		}
		if(is_array($result) && count($result) > 0){
			foreach($result as $key=>$value){
				$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$value->shop_id),'shop_id,fbc_user_id,database_name');
				if(isset($FBCData) && $FBCData->database_name!='')
				{
					$fbc_user_database=$FBCData->database_name;

					$this->load->database();
					$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
					$this->seller_db = $this->load->database($config_app,TRUE);
					$b2bCat=$this->getB2BCatCountByUserID($value->fbc_user_id);
					$b2bCatCount = $b2bCat; //(is_array($b2bCat) && count($b2bCat) > 0) ? count($b2bCat) : 0;
					if($b2bCatCount > 0){


						$finalResult[] = (object)array('shop_id' => $value->shop_id, 'owner_name' => $value->owner_name, 'webshop_name' => $value->webshop_name, 'cat_count' => $b2bCatCount);
					}
				}
			}
		}
		//print_r($finalResult);
		return $finalResult;
    }


	public function getB2BCatByUserIDLevel0($fbc_user_id)

	{
		$main_db_name=$this->db->database;
		$this->seller_db->select('fbc_users_category_b2b.*,cg1.cat_name');
		$this->seller_db->from('fbc_users_category_b2b');
		$this->seller_db->join($main_db_name.'.category as cg1','cg1.id = fbc_users_category_b2b.category_id AND cg1.cat_level=0','RIGHT');
		$this->seller_db->where(array('fbc_user_id' => $fbc_user_id));
		$this->seller_db->where('level IN (0)');
		$this->seller_db->group_by('category_id');

		$query = $this->seller_db->get();
		//print_r($this->seller_db->last_query());
		return $query->result();
	}

	public function getB2BCatByUserID($fbc_user_id)
	{
		//$result = $this->seller_db->get_where('fbc_users_category_b2b', array('fbc_user_id' => $fbc_user_id, 'level'=>1, 'b2b_enabled'=>1))->num_rows();
		//$result = $this->seller_db->get_where('fbc_users_category_b2b', array('fbc_user_id' => $fbc_user_id, 'level'=>1, 'b2b_enabled'=>1))->result();
		$this->seller_db->select('*');
		$this->seller_db->from('fbc_users_category_b2b');
		$this->seller_db->where(array('fbc_user_id' => $fbc_user_id, 'b2b_enabled'=>1));
		$this->seller_db->where('level IN (0,1)');
		$this->seller_db->group_by('category_id');

		$query = $this->seller_db->get();
		//print_r($this->seller_db->last_query());
		return $query->result();
	}

	public function getB2BCatCountByUserID($fbc_user_id)
	{
		$this->seller_db->select('COUNT(DISTINCT(category_id)) as category_count');
		$this->seller_db->from('fbc_users_category_b2b');
		$this->seller_db->where(array('fbc_user_id' => $fbc_user_id, 'b2b_enabled'=>1));
		$this->seller_db->where('level IN (0,1)');
		$query = $this->seller_db->get();

		return $query->result()[0]->category_count;
	}

	public function getProductCountByCatId($cat_id)
	{
		$this->seller_db->select('p.*');
		$this->seller_db->from('products as p');
		$this->seller_db->join('products_category as pc','p.id = pc.product_id OR p.parent_id = pc.product_id','INNER');
		$this->seller_db->where(array('p.product_inv_type' => 'buy', 'pc.category_ids' => $cat_id));
		$this->seller_db->where_in('p.product_type' , array('simple','conf-simple'));
		$this->seller_db->where('p.remove_flag','0');
		$this->seller_db->where('pc.level IN (0,1)');
		$query = $this->seller_db->get();
		//print_r($this->seller_db->last_query());
		return $query->num_rows();
	}

	public function getProductCountByCatIdRevised($cat_id, $product_ids = '')
	{
		$this->seller_db->select('COUNT(p.id) as total');
		$this->seller_db->from('products p, products_category pc');
		$where = '(p.id = pc.product_id OR p.parent_id = pc.product_id)';
		$this->seller_db->where($where);
		$this->seller_db->where(array('p.product_inv_type' => 'buy', 'pc.category_ids' => $cat_id));
		$this->seller_db->where_in('p.product_type' , array('simple','conf-simple'));
		$this->seller_db->where('p.remove_flag','0');
		$this->seller_db->where('pc.level IN (0,1)');
		if($product_ids != NULL){
		$this->seller_db->where('p.id NOT IN ('.$product_ids.')');
		}
		//and p.id notin(1,3,4,5)
		$query = $this->seller_db->get();
		//print_r($this->seller_db->last_query());
		return $query->result();
	}

	public function getCategoryDetail($id="", $search_param='')
	{
		$this->db->select('id, cat_name, shop_id');
        $this->db->from('category');
		$this->db->where(array('status' => 1));

        // category name - keyword
		if(isset($search_param['keyword']) && $search_param['keyword'] !="")
		{
			$this->db->like('cat_name',$search_param['keyword']);
			//$this->db->group_by('shop_id');
		}

		$query = $this->db->get();
		//print_r($this->db->last_query());
        $result = $query->result();
		return $result ;

	}

	public function getB2BCatDetails($shop_id){
		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$main_db_name=$this->db->database;
			//$this->seller_db->distinct();
			$this->seller_db->select('b2bCat.*, cg1.cat_name, cg.cat_name as sub_cat_name, cg.parent_id');
			$this->seller_db->from('fbc_users_category_b2b as b2bCat');
			$this->seller_db->join($main_db_name.'.category as cg','cg.id = b2bCat.category_id AND cg.cat_level=1','LEFT');
			$this->seller_db->join($main_db_name.'.category as cg1','cg1.id = cg.parent_id AND cg1.cat_level=0','LEFT');
			$this->seller_db->join('products_category as pc','pc.category_ids = b2bCat.category_id AND pc.level = 1','LEFT');
			$this->seller_db->where(array('b2bCat.fbc_user_id' => $fbc_user_id, 'b2bCat.b2b_enabled'=>1));    //remmove b2bCat.level = 1
			$this->seller_db->where('(b2bCat.level IN (0,1))');  //added this condition
			$this->seller_db->group_by('b2bCat.category_id');
			$query = $this->seller_db->get();
			//print_r($this->seller_db->last_query());
			//return $query->result();
			$result =  $query->result();

			$finalResult = array();
			if(is_array($result) && count($result) > 0){
				foreach($result as $key=>$value){
					$productCount=$this->getProductCountByCatId($value->category_id);
					if($productCount > 0){
						$finalResult[] = (object)array('category_id' => $value->category_id, 'cat_name' => $value->cat_name, 'sub_cat_name' => $value->sub_cat_name, 'parent_id' => $value->parent_id, 'product_count' => $productCount);
					}
				}
			}

			return $finalResult;
		}
	}




	public function getProductDetailsByCatId($shop_id, $show_flag='', $launch_date='',$stock_status='', $cat_id='',$gender=''){

		$applied_products_ids = $this->getAppliedProductsByShopId($shop_id);
		//$term = $_REQUEST['search']['value'];


		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$main_db_name=$this->db->database;
			//$this->seller_db->distinct();
			$this->seller_db->select('p.id, p.parent_id, p.name, p.sku, p.launch_date, p.price, cg.id as category_id, pc.category_ids, cg.cat_name, pi.qty');
			$this->seller_db->from('products as p');
			$this->seller_db->join('products_category as pc','p.id = pc.product_id OR p.parent_id = pc.product_id','LEFT');  //pc.level =1
			$this->seller_db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
			$this->seller_db->join($main_db_name.'.category as cg','cg.id = pc.category_ids','LEFT');
			//$this->seller_db->where(array('p.product_inv_type' => 'buy'));
			$this->seller_db->where_in('p.product_inv_type' , array('buy','virtual'));
			if($cat_id != '' and $cat_id > 0){
				$this->seller_db->where_in('pc.category_ids', $cat_id);
				//$this->seller_db->where('pc.category_ids IN ('.$cat_id.')');
			}
			$this->seller_db->where_in('p.product_type' , array('simple','conf-simple'));
			$this->seller_db->where('p.remove_flag','0');

			if($launch_date != ''){
				$this->seller_db->where('p.launch_date', strtotime($launch_date));
			}

			if($gender !=''){
				/*$this->seller_db->where(" (
				  p.gender LIKE '%".$this->seller_db->escape_like_str($gender)."%'
				   )");
				*/
				//$this->seller_db->where('find_in_set("'.$gender.'", gender) <> 0');
				$reg_expo = 'REGEXP [[:<:]]'.$gender.'[[:>:]]';
				$this->seller_db->where("(CASE WHEN p.product_type = 'conf-simple' THEN (SELECT gender from products WHERE id = (p.parent_id)) WHEN p.product_type = 'simple' THEN p.gender ELSE 0 END)  REGEXP '[[:<:]]".$gender."[[:>:]]'");
			}

			/*if($term !=''){
				$this->seller_db->where(" (
				  p.name LIKE '%".$this->seller_db->escape_like_str($term)."%'
				  OR p.sku LIKE '%".$this->seller_db->escape_like_str($term)."%'

				   )");
			  } */

			if($stock_status > 0 ) {
				if($stock_status == 2 ){
					$this->seller_db->where('pi.qty', 0);
				}else{
					$this->seller_db->where('pi.qty > 0');
				}

			}


			if($applied_products_ids != NULL && isset($show_flag) && $show_flag == 0){
				//$this->seller_db->where('p.id NOT IN ('.$applied_products_ids->applied_product_ids.')');
				$applied_products_ids_arr = array();
				$this->seller_db->group_start();
				$applied_products_ids_list = explode(",", $applied_products_ids);
				foreach($applied_products_ids_list as $value) {
					$applied_products_ids_arr[] = $value;
				}
				$applied_products_ids_chunk = array_chunk($applied_products_ids_arr,500);

				foreach($applied_products_ids_chunk as $applied_ids_chunk)
				{
					$this->seller_db->where_not_in('p.id', $applied_ids_chunk);

				}
				$this->seller_db->group_end();
			}


			$this->seller_db->group_by('p.id');
			if(isset($_REQUEST['order'][0]['column']) && $_REQUEST['order'][0]['column'] == 7 ){
				if($_REQUEST['order'][0]['dir'] == 'asc' ){
					$this->seller_db->order_by('p.launch_date','ASC');
				}

				if($_REQUEST['order'][0]['dir'] == 'desc' ){
					$this->seller_db->order_by('p.launch_date','DESC');
				}

			}
			$query = $this->seller_db->get();
			//print_r($this->seller_db->last_query());
			return $query->result();
		}
	}


	public function getProductListIncludingAppliedProducts($shop_id, $cat_id){


		$term = $_REQUEST['search']['value'];

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$main_db_name=$this->db->database;
			//$this->seller_db->distinct();
			$this->seller_db->select('p.id, p.parent_id, p.name, p.sku, p.launch_date, p.price, cg.id as category_id, pc.category_ids, cg.cat_name, pi.qty',);
			$this->seller_db->from('products as p');
			$this->seller_db->join('products_category as pc','p.id = pc.product_id OR p.parent_id = pc.product_id','LEFT');  //pc.level =1
			$this->seller_db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
			$this->seller_db->join($main_db_name.'.category as cg','cg.id = pc.category_ids','LEFT');
			//$this->seller_db->where(array('p.product_inv_type' => 'buy'));
			$this->seller_db->where_in('p.product_inv_type' , array('buy','virtual'));
			//$this->seller_db->where_in('pc.category_ids', $cat_id);
			$this->seller_db->where('pc.category_ids IN ('.$cat_id.')');

			$this->seller_db->where_in('p.product_type' , array('simple','conf-simple'));
			$this->seller_db->where('p.remove_flag','0');



			if($term !=''){
				$this->seller_db->where(" (
				  p.name LIKE '%".$this->seller_db->escape_like_str($term)."%' 
				  OR p.sku LIKE '%".$this->seller_db->escape_like_str($term)."%' 
				  
				   )");
			  }


			$this->seller_db->group_by('p.id');
			//$this->seller_db->order_by('p.launch_date','DESC');

			$query = $this->seller_db->get();
			//print_r($this->seller_db->last_query());
			return $query->result();
		}
	}

	function getVariantDetailsForProducts($shop_id, $product_id){
		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);
			$main_db_name=$this->db->database;
			// $this->seller_db->select('pvm.*,ma.attr_name');
			// $this->seller_db->from('products_variants_master as pvm');
			// $this->seller_db->join($main_db_name.'.eav_attributes as ma','pvm.attr_id = ma.id AND ma.attr_type=2','LEFT');
			// $this->seller_db->where('pvm.product_id',$product_id);
			$this->seller_db->select('pv.*,ma.attr_name,mao.attr_options_name');
			$this->seller_db->from('products_variants as pv');
			$this->seller_db->join($main_db_name.'.eav_attributes as ma','pv.attr_id = ma.id AND ma.attr_type=2','LEFT');
			$this->seller_db->join($main_db_name.'.eav_attributes_options as mao','mao.id = pv.attr_value AND ma.attr_type=2','LEFT');
			$this->seller_db->where('pv.product_id',$product_id);
			$query = $this->seller_db->get();
			//print_r($this->seller_db->last_query());
			$resultArr = $query->result();

			return $resultArr;
		}
    }

	 //getSingleDataByID
	public function getSingleDataByID($tableName,$condition,$select){
		if(!empty($select))
		{
		  $this->seller_db->select($select);
		}
		$this->seller_db->where($condition);
		$query = $this->seller_db->get($tableName);
		// print_r($this->seller_db->last_query());
		return $query->row();
	}

	public function getMultiDataById($tableName,$condition,$select,$order_by_column='',$order_by_type=''){
		if(!empty($select))
		{
			$this->seller_db->select($select);
		}
		$this->seller_db->where($condition);

		if(isset($order_by_column) &&  $order_by_column != ''){
			$this->seller_db->order_by($order_by_column,$order_by_type);
		}

		$query = $this->seller_db->get($tableName);
		return $query->result();
	}

	function getProductStock($shop_id,$product_id){
		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$this->seller_db->select('qty');
			$this->seller_db->from('products_inventory');
			$this->seller_db->where('product_id',$product_id);
			$query = $this->seller_db->get();
			//print_r($this->seller_db->last_query());
			$row = $query->row();
			return $row;
		}
	}

	//insertData
	public function insertData($table,$data){
		$this->seller_db->reset_query();

		$this->seller_db->insert($table,$data);

		if($this->seller_db->affected_rows() > 0){
			$last_insert_id=$this->seller_db->insert_id();
			return $last_insert_id;
		}else{
			return false;
		}
	}

	function printLastQuery(){
		echo $this->seller_db->last_query();
	}

	function getSavedOrders(){
		$main_db_name=$this->db->database;

		$this->seller_db->select('FUS.org_shop_name as webshop_name, FUS.currency_symbol, FU.owner_name, OS.*');
		$this->seller_db->from('b2b_orders_saved as OS');
		$this->seller_db->join($main_db_name.'.fbc_users_shop as FUS','FUS.shop_id = OS.supplier_shop_id','LEFT');
		$this->seller_db->join($main_db_name.'.fbc_users as FU','FU.fbc_user_id = FUS.fbc_user_id','LEFT');
		$this->seller_db->order_by('created_at','DESC');
		$query = $this->seller_db->get();
		//print_r($this->seller_db->last_query());
		$result = $query->result();
		return $result;
	}

	function getAppliedOrders(){
		$main_db_name=$this->db->database;

		$this->seller_db->select('FUS.org_shop_name as webshop_name, FUS.currency_symbol, FU.owner_name, OA.*');
		$this->seller_db->from('b2b_orders_applied as OA');
		$this->seller_db->join($main_db_name.'.fbc_users_shop as FUS','FUS.shop_id = OA.supplier_shop_id','LEFT');
		$this->seller_db->join($main_db_name.'.fbc_users as FU','FU.fbc_user_id = FUS.fbc_user_id','LEFT');
		$this->seller_db->order_by("OA.created_at", "DESC");
		$query = $this->seller_db->get();
		// print_r($this->seller_db->last_query());
		$result = $query->result();
		return $result;
	}

	//updateData
	public function updateData($tableName,$condition,$updateData){
		$this->seller_db->where($condition);

		$this->seller_db->update($tableName,$updateData);
		if($this->seller_db->affected_rows() > 0){
			return true;
		}else {
			return false;
		}
		$this->seller_db->reset_query();
    }

	//deleteData
	function deleteDataById($tablename,$where){
		$this->seller_db->delete($tablename,$where);
		$this->seller_db->reset_query();
	}

	public function getB2BCatDetailsRewised($shop_id){

		$applied_products_ids = $this->getAppliedProductsByShopId($shop_id);
		//print_r($applied_products_ids);

		$main_db_name=$this->db->database;
		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			//echo $fbc_user_id;exit;

			$this->seller_db->distinct();
			$this->seller_db->select('b2bCat.*, IF(cg.parent_id <= 0, cg.cat_name, cg2.cat_name) AS cat_name, cg.parent_id,IF(cg.parent_id >0, cg1.cat_name,"-") as  sub_cat_name');
			$this->seller_db->from('fbc_users_category_b2b as b2bCat');
			$this->seller_db->join($main_db_name.'.category as cg','cg.id = b2bCat.category_id','LEFT');
			$this->seller_db->join($main_db_name.'.category as cg1','cg1.id = cg.id','LEFT');
			$this->seller_db->join($main_db_name.'.category as cg2','cg2.id = cg.parent_id','LEFT');
			$this->seller_db->join('products_category as pc','pc.category_ids = b2bCat.category_id','LEFT');
			$this->seller_db->where(array('b2bCat.fbc_user_id' => $fbc_user_id, 'b2bCat.b2b_enabled'=>1));    //remmove b2bCat.level = 1

			$this->seller_db->where('(b2bCat.level IN (0,1))');
			$this->seller_db->order_by('b2bCat.category_id ASC,cg.parent_id ASC');

			$query = $this->seller_db->get();
			$result= $query->result();


			$finalResult = array();
			if(is_array($result) && count($result) > 0){
				foreach($result as $key=>$value){
					//$productCount=$this->getProductCountByCatIdRevised($value->category_id, $applied_products_ids->applied_product_ids); //new getProductCountByCatIdRevised  old getProductCountByCatId
					//print_r($productCount);
					//if($productCount[0]->total > 0){
						$finalResult[] = (object)array('level' => $value->level,'category_id' => $value->category_id, 'cat_name' => $value->cat_name, 'sub_cat_name' => $value->sub_cat_name, 'parent_id' => $value->parent_id, 'product_count' => '');
					//}
				}
			}


			return $finalResult;
		}
	}


	public function getProductIds($shop_id,$draft_id){


		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$this->seller_db->select('GROUP_CONCAT(product_id SEPARATOR ",") as product_id');

			$this->seller_db->from('b2b_orders_draft_details');
			$this->seller_db->where('draft_order_id',$draft_id);
			$query = $this->seller_db->get();
			$row = $query->row();
			return $row;
		}


	}


	public function getCustomerTypesByProductsId($shop_id,$product_ids){

		$new_product_ids = rtrim($product_ids, ', ');

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

		$result = $this->seller_db->query("SELECT GROUP_CONCAT(customer_type_ids SEPARATOR ',' ) as customer_type FROM (SELECT *, CASE WHEN id in (".$new_product_ids.") THEN @idlist := CONCAT(IFNULL(@idlist,''),',',parent_id) WHEN FIND_IN_SET(id,@idlist) THEN @idlist := CONCAT(@idlist,',',parent_id) END as checkId FROM products ORDER BY id DESC) as T WHERE checkId IS NOT NULL and product_type != 'conf-simple' and remove_flag = 0");

		return $result->row();


		}
	}

	public function get_customer_types_selected($shop_id,$customer_types){

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$query = $this->seller_db->query("SELECT *  FROM `customers_type_master` WHERE `id` IN (".$customer_types.")");
			$result = $query->result();
			return $result;




		}
	}

	public function getAppliedCustomeTypeMapping($shop_id,$applied_id,$customer_types){

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$query = $this->seller_db->query("SELECT GROUP_CONCAT(buyer_customer_type_id SEPARATOR ',') as customer_type_ids FROM `b2b_orders_applied_custypedetails` WHERE `applied_order_id` = ".$applied_id." AND `supplier_customer_type_id` IN (".$customer_types.")");
			$result = $query->row();
			return $result;




		}
	}


	public function getCustomerMasterByShop($shop_id){

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$query = $this->seller_db->query("SELECT *  FROM `customers_type_master` ");
			$result = $query->result();
			return $result;

		}
	}


	public function getLaunchDateById($shop_id,$product_id){

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$query = $this->seller_db->query("SELECT launch_date  FROM `products` WHERE `id` = ".$product_id );
			$result = $query->row_array();
			return $result;

		}
	}

	public function getAppliedProductsByShopId($shop_id){

		$applied_products_ids = "";

		//$sql="select GROUP_CONCAT(distinct(t2.product_id) SEPARATOR ',') as applied_product_ids from b2b_orders_applied t1, b2b_orders_applied_details t2 where t1.id=t2.applied_order_id and t1.status!=2 and t1.supplier_shop_id =".$shop_id;
		$sql="select distinct(t2.product_id) as applied_product_ids from b2b_orders_applied t1, b2b_orders_applied_details t2 where t1.id=t2.applied_order_id and t1.status!=2 and t1.supplier_shop_id =".$shop_id;
		$query = $this->seller_db->query($sql);
		$result = $query->result();
		if(is_array($result) && count($result) > 0){
			$applied_products_ids_ary = array();
			foreach($result as $v) {
				$applied_products_ids_ary[] = $v->applied_product_ids;
			}
			$applied_products_ids = implode(",",$applied_products_ids_ary);
		}
		return $applied_products_ids;


	}

	public function getAppliedProductsCount($shop_id){

		$sql="select COUNT(distinct(t2.product_id)) as applied_product_count from b2b_orders_applied t1, b2b_orders_applied_details t2 where t1.id=t2.applied_order_id and t1.status!=2 and t1.supplier_shop_id =".$shop_id;
		$query = $this->seller_db->query($sql);
		$result = $query->row();

		return $result->applied_product_count;

	}


	public function getSupplierCustomerByShopId($seller_shop_id,$shop_id){


		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$seller_shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_shop_db = $this->load->database($config_app,TRUE);

			$this->seller_shop_db->select('*');
			$this->seller_shop_db->from('b2b_customers');
			$this->seller_shop_db->where('shop_id',$shop_id);
			$query = $this->seller_shop_db->get();

			//print_r($this->seller_shop_db->last_query());

			$row = $query->row();
			return $row;
		}


	}

	public function insertB2BCustDataOtherDB($seller_shop_id,$shop_id){

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$seller_shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_shop_db = $this->load->database($config_app,TRUE);

			$b2b_customer_insert=array('shop_id'=>$shop_id,'created_at'=> time(), 'ip'=> $_SERVER['REMOTE_ADDR']);
			$this->seller_shop_db->insert('b2b_customers',$b2b_customer_insert);

			if($this->seller_shop_db->affected_rows() > 0){

				return $this->seller_shop_db->insert_id();;
			}else{
				return false;
			}


		}

	}


	public function getB2bCustomerdetailsByShopId($seller_shop_id,$cust_id){


		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$seller_shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_shop_db = $this->load->database($config_app,TRUE);

			$this->seller_shop_db->select('id');
			$this->seller_shop_db->from('b2b_customers_details');
			$this->seller_shop_db->where('customer_id',$cust_id);
			$query = $this->seller_shop_db->get();

			//print_r($this->seller_shop_db->last_query());

			$row = $query->row();
			return $row;
		}


	}

	public function insertB2BCustomerDetail($seller_shop_id,$cust_id)
	{
		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$seller_shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_shop_db = $this->load->database($config_app,TRUE);

			$defaultCustomerDetail =  $this->B2BModel->getUersB2BDetailsByShopId($seller_shop_id);
			$insertData=array(
							//'id'                   => $shop_id,
							//'customer_id'			=> $defaultCustomerDetail->shop_id,
							'customer_id' =>		 $cust_id,
							'allow_dropship'		=> $defaultCustomerDetail->allow_dropship,
							'dropship_discount'		 => $defaultCustomerDetail->dropship_discount,
							'dropship_del_time'		 => $defaultCustomerDetail->dropship_del_time,
							'allow_buyin'			 => $defaultCustomerDetail->allow_buyin,
							'buyin_discount' 		=> $defaultCustomerDetail->buyin_discount,
							'buyin_del_time' 		=> $defaultCustomerDetail->buyin_del_time,
							'display_catalog_overseas' 	=> $defaultCustomerDetail->display_catalog_overseas,
							'perm_to_change_price' 	=> $defaultCustomerDetail->perm_to_change_price,
							'can_increase_price' 	=> $defaultCustomerDetail->can_increase_price,
							'can_decrease_price' 	=> $defaultCustomerDetail->can_decrease_price,
							'enable_payment_term' => $defaultCustomerDetail->enable_payment_term,
							'created_at' 			=> strtotime(date('Y-m-d H:i:s')),
							'ip'					=>$_SERVER['REMOTE_ADDR']
							);

			$this->seller_shop_db->insert('b2b_customers_details',$insertData);

			if($this->seller_shop_db->affected_rows() > 0){

				return true;
			}else{
				return false;
			}


		}

	}







}
