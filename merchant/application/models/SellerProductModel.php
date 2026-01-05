<?php
/**
 * Dec 2020
 */
class SellerProductModel extends CI_Model
{

	function __construct()
	{
	}


	public function updateData($tableName,$condition,$updateData)
    {
		$this->db->where($condition);

		  $this->db->update($tableName,$updateData);
		  // echo $this->db->last_query();//exit;
		  if($this->db->affected_rows() > 0)
		  {

			return true;
		  }
		  else
		  {
			return false;
		  }

		  $this->db->reset_query();
    }



		///getDataByID
	public function getMultiDataById($tableName,$condition,$select,$order_by_column='',$order_by_type='')
	{
		if(!empty($select))
		{
			$this->db->select($select);
		}
		$this->db->where($condition);

		if(isset($order_by_column) &&  $order_by_column != ''){
			$this->db->order_by($order_by_column,$order_by_type);
		}

		$query = $this->db->get($tableName);
		return $query->result();
	}

      //getSingleDataByID
  public function getSingleDataByID($tableName,$condition,$select)
  {
    if(!empty($select))
    {
      $this->db->select($select);
    }
    $this->db->where($condition);
    $query = $this->db->get($tableName);
    return $query->row();
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

	function get_datatables_products($price,$inventory,$supplier,$fromDate,$toDate,$count,$image_filter,$LogindID){
		$term = $_REQUEST['search']['value'];
		$this->_get_datatables_query_products($term,$price,$inventory,$supplier,$fromDate,$toDate,$image_filter,$LogindID);
		if($_REQUEST['length'] != -1)
		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->db->get();
		if($count == 'count')
		{
			return $query->num_rows();
		}
		else
		{
			return $query->result();
		}
	}


	function get_datatables_products_all_count($price,$inventory,$supplier,$fromDate,$toDate,$image_filter,$LogindID){
		$term = $_REQUEST['search']['value'];
		$this->_get_datatables_query_products($term,$price,$inventory,$supplier,$fromDate,$toDate,$image_filter,$LogindID);
		$query = $this->db->get();
		return $query->num_rows();
	}

    public function _get_datatables_query_products($term,$price,$inventory,$supplier,$fromDate,$toDate,$image_filter,$LogindID) {

		$main_db_name=$this->db->database;
		$column = array('p.name','', 'p.product_code','pi.qty', 'p.price','p.updated_at','','');
		$this->db->distinct();
		$this->db->select('p.*,pi.qty'); 
		$this->db->from('products as p');
		$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
		$this->db->where('p.product_type <>','conf-simple');
		$this->db->where('p.publisher_id',$LogindID);
		$this->db->where('p.remove_flag','0');
		if($term !=''){

		  $this->db->where(" (
			p.name LIKE '%".$this->db->escape_like_str($term)."%'
			OR p.description LIKE '%".$this->db->escape_like_str($term)."%'
			OR p.product_code LIKE '%".$this->db->escape_like_str($term)."%'
			OR p.sku LIKE '%".$this->db->escape_like_str($term)."%'
			OR p.barcode LIKE '%".$this->db->escape_like_str($term)."%'
			OR p.id IN (select `parent_id` FROM `products` WHERE `product_type` = 'conf-simple' AND (`barcode` LIKE '%".$this->db->escape_like_str($term)."%' OR `sku` LIKE '%".$this->db->escape_like_str($term)."%'))
			 )");
		}

		if($image_filter == 'image_filter')
		{
			$this->db->where('`p`.`id` NOT IN (SELECT `product_id` FROM `products_media_gallery`)', NULL, FALSE);
			$this->db->group_by('p.id');
		}

		 if(!empty($price))
		{

			$this->db->where('p.price >=',0);
			$this->db->where('p.price <=', $price);
		}

		if(!empty($inventory))
		{
			$this->db->where('pi.qty >=',0);
			$this->db->where('pi.qty <=', $inventory);
		}

		if(!empty($supplier))
		{
			$selected_supplier=array();
			if( strpos($supplier, ',') !== false ) {
				$selected_supplier=explode(',',$supplier);
			}else{
				$selected_supplier[]=$supplier;
			}

			if(count($selected_supplier)==1){
				if($selected_supplier[0]=='Self')
				{
					$fbc_user_id	=	$this->session->userdata('LoginID');
					$this->db->where('p.shop_id',0);
				}
				else if($selected_supplier[0]=='B2B'){
					$this->db->where('p.shop_id >',0);
				}
			}else{
				$this->db->where('p.shop_id',0);
				$this->db->where('p.shop_id >',0);
			}
		}

		if(!empty($fromDate) && empty($toDate)){

			$this->db->where('p.updated_at >=',strtotime($fromDate));
		}
		else if(!empty($toDate) && empty($fromDate)){

			$this->db->where('p.updated_at <=',strtotime($toDate));
		}
		else if(!empty($toDate) && !empty($fromDate))
		{
			$this->db->where('p.updated_at >=',strtotime($fromDate));
			$this->db->where('p.updated_at <=',strtotime($toDate));
		}

		if(isset($_REQUEST['order'])) // here order processing
		{

			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
			// $this->db->order_by('coalesce(updated_at, created_at) DESC');

		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->db->order_by(key($order), $order[key($order)]);
		}else{
			$this->db->order_by('p.id', 'desc');
		}

    }


   public function count_all_products($price,$inventory,$supplier,$fromDate,$toDate) {
	   $main_db_name=$this->db->database;
	   $column = array('p.name','', 'p.shop_id','pi.qty', 'p.price','p.updated_at','');
	   $term = $_REQUEST['search']['value'];
		$this->db->distinct();
		$this->db->select('p.*,pi.qty');  //,cg.cat_name,pc.category_ids
		$this->db->from('products as p');
		$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
		//$this->db->join('products_category as pc','p.id = pc.product_id AND pc.level=0','LEFT');
		//$this->db->join($main_db_name.'.category as cg','cg.id = pc.category_ids AND cg.cat_level=0','LEFT');
		// $this->db->join($main_db_name.'.fbc_users_shop as fus','p.shop_id = fus.shop_id','LEFT');
		$this->db->where('p.product_type <>','conf-simple');
		$this->db->where('p.product_inv_type <>','dropship');
		$this->db->where('p.remove_flag','0');

		if($term !=''){

		   $this->db->where(" (
			p.name LIKE '%".$this->db->escape_like_str($term)."%'
			OR p.description LIKE '%".$this->db->escape_like_str($term)."%'
			OR p.product_code LIKE '%".$this->db->escape_like_str($term)."%'
			OR p.sku LIKE '%".$this->db->escape_like_str($term)."%'

			 )");

		}

		  if(!empty($price))
		{

			$this->db->where('p.price >=',0);
			$this->db->where('p.price <=', $price);
		}

		if(!empty($inventory))
		{
			$this->db->where('pi.qty >=',0);
			$this->db->where('pi.qty <=', $inventory);
		}




		if(!empty($supplier))
		{
			if( strpos($supplier, ',') !== false ) {
				$selected_supplier=explode(',',$supplier);
			}else{
				$selected_supplier[]=$supplier;
			}

			if(count($selected_supplier)==1){
				if($selected_supplier[0]=='Self')
				{
					$fbc_user_id	=	$this->session->userdata('LoginID');
					$this->db->where('p.shop_id',0);
				}
				else if($selected_supplier[0]=='B2B'){
					$this->db->where('p.shop_id >',0);
				}
			}else{
				$this->db->where('p.shop_id',0);
				$this->db->where('p.shop_id >',0);
			}
		}

		if(!empty($fromDate) && empty($toDate)){

			$this->db->where('p.updated_at >=',strtotime($fromDate));
		}
		else if(!empty($toDate) && empty($fromDate)){

			$this->db->where('p.updated_at <=',strtotime($toDate));
		}
		else if(!empty($toDate) && !empty($fromDate))
		{
			$this->db->where('p.updated_at >=',strtotime($fromDate));
			$this->db->where('p.updated_at <=',strtotime($toDate));
		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->db->order_by(key($order), $order[key($order)]);
		}else{
			$this->db->order_by('p.id', 'desc');
		}
       return $this->db->count_all_results();
    }

	 function count_filtered_products($price,$inventory,$supplier,$fromDate,$toDate){
		$term = $_REQUEST['search']['value'];
		$this->_get_datatables_query_products($term,$price,$inventory,$supplier,$fromDate,$toDate);
		$query = $this->db->get();
		return $query->num_rows();
	 }

	function getStockForConfigProduct($product_id)
	{
		$Row=$this->getVariantProducts($product_id);


		if(isset($Row) && $Row->product_ids!=''){
			$product_ids=$Row->product_ids;
			$sql="SELECT sum(qty) as qty from products_inventory where product_id IN ($product_ids) ";
			$query = $this->db->query($sql);
			return $query->row();

		}else{
			return false;
		}


	}

	function getVariantProducts($product_id){
		$sql = "SELECT GROUP_CONCAT(id) as product_ids FROM `products` where parent_id = $product_id ";
		$query = $this->db->query($sql);
		return $query->row();
	}

	function getVariantProductscount($product_ids){
		$count='';
		if(!empty($product_ids)){
		$sql = "SELECT count(id) as count from `products` where product_inv_type ='buy' and  id In (".$product_ids.");";
		}else
		{
			$sql = "SELECT count(id) as count from `products` where product_inv_type ='buy' and  id In (0);";
		}
		$query = $this->db->query($sql);
		$count= $query->row(0)->count;
		// echo $this->db->last_query();exit;
		return $count;
	}

	function getAttrByProductId($product_id){
		$sql = "SELECT GROUP_CONCAT(attr_id) as attr_ids FROM `products_attributes` where product_id = $product_id ";
		$query = $this->db->query($sql);
		return $query->row();
	}

	function getVariantMasterByProductId($product_id){
		$sql = "SELECT GROUP_CONCAT(attr_id) as attr_ids FROM `products_variants_master` where product_id = $product_id ";
		$query = $this->db->query($sql);
		// echo $this->db->last_query();exit;
		return $query->row();
	}

	function getCatelogAllAttrs(){
		$sql = "SELECT GROUP_CONCAT(distinct(t2.attr_id)) as attr_ids FROM products t1, `products_attributes` t2 where t1.id=t2.product_id and t1.remove_flag=0";
		$query = $this->db->query($sql);
		return $query->row();
	}

	function getCatelogAllVariants(){
		$sql = "SELECT GROUP_CONCAT(distinct(t2.attr_id)) as attr_ids FROM products t1, `products_variants_master` t2 where t1.id=t2.product_id and t1.remove_flag=0";
		$query = $this->db->query($sql);
		// echo $this->db->last_query();exit;
		return $query->row();
	}


	function getVariantMasterForProducts($product_id)
    {
		$main_db_name=$this->db->database;
		$this->db->select('pvm.*,ma.attr_name,ma.attr_code');
		$this->db->from('products_variants_master as pvm');
		$this->db->join($main_db_name.'.eav_attributes as ma','pvm.attr_id = ma.id AND ma.attr_type=2','LEFT');
		$this->db->where('pvm.product_id',$product_id);
		$query = $this->db->get();
		$resultArr = $query->result_array();

		return $resultArr;
    }

	function getVariantProductsByIds($product_ids)
    {

		$this->db->select('p.*,pi.qty,pi.available_qty');
		$this->db->from('products as p');
		$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
		$this->db->where_in('p.id',$product_ids);
		$query = $this->db->get();
		$resultArr = $query->result_array();
		//echo $this->db->last_query();exit;
		return $resultArr;
    }


	function deleteDataById($tablename,$where){

		$this->db->delete($tablename,$where);
		// echo $this->db->last_query();exit;

		$this->db->reset_query();
		return true;
	}

	function getProductForCSVImport($root_category_id,$sub_category){
		$main_db_name=$this->db->database;
		$this->db->distinct();
		$this->db->select('p.*,pc.category_ids,pi.qty,cg1.cat_name as sub_category');
		$this->db->from('products as p');
		$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
		$this->db->join('products_category as pc','p.id = pc.product_id','LEFT');
		$this->db->join($main_db_name.'.category as cg1','cg1.id = pc.category_ids AND pc.level=1','LEFT');
		$this->db->where('p.product_type <>','conf-simple');
		$this->db->where('p.product_inv_type <>','dropship');
		$this->db->where('p.remove_flag','0');
		//$this->db->where("(pc.category_ids=$root_category_id AND pc.level=0)");
		if(!empty($sub_category)){
		$this->db->where("(pc.category_ids=$sub_category AND pc.level=1)");
		}
		$query = $this->db->get();
		$resultArr = $query->result_array();
		//echo $this->db->last_query();exit;
		return $resultArr;


	}
	// $root_category_id,$sub_category
	function getProductForCSVImportUpdate(){
		// $main_db_name=$this->db->database;
		// $this->db->distinct();
		// $this->db->select('p.*,pc.category_ids,pi.qty,cg1.cat_name as sub_category');
		// $this->db->from('products as p');
		// $this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
		// $this->db->join('products_category as pc','p.id = pc.product_id','LEFT');
		// $this->db->join($main_db_name.'.category as cg1','cg1.id = pc.category_ids AND pc.level=1','LEFT');
		// $this->db->where('p.product_type <>','configurable');
		// $this->db->where('p.product_inv_type <>','dropship');
		// $this->db->where("(pc.category_ids=$sub_category AND pc.level=1)");
		// $query = $this->db->get();
		// $resultArr = $query->result_array();
		// // echo $this->db->last_query();exit;
		// return $resultArr;

		$this->db->select('p.*, pi.qty, pc.category_ids ,pi.qty');
			$this->db->from('products as p');
			$this->db->join('products_category as pc','p.id = pc.product_id OR p.parent_id = pc.product_id','LEFT');  //pc.level =1
			$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
			//$this->db->where( 'pc.category_ids', $root_category_id);
			// $this->db->where( 'pc.category_ids' => $cat_id));
			$this->db->where_in('p.product_type' , array('simple','conf-simple'));
			$this->db->where('p.remove_flag','0');
			$this->db->group_by('p.id');
			$query = $this->db->get();
			// print_r($this->db->last_query());
			return $query->result();
	}

	function getProductDataForCSVUpdate(){
		$this->db->select('p.*, parent.remove_flag as p_remove_flag');
			$this->db->from('products as p');
			$this->db->join('products as parent', 'p.parent_id = parent.id', 'left');
			$this->db->where_in('p.product_type' , array('simple','conf-simple'));
			$this->db->where('p.remove_flag','0');
			$this->db->where('(parent.remove_flag IS NULL OR parent.remove_flag = 0)');
			$this->db->group_by('p.id');
			$query = $this->db->get();
			// print_r($this->db->last_query());
			return $query->result();
	}

	function getProductForCSVImportUpdate_opt(){

		$this->db->select('p.*, pi.qty, pc.category_ids ,pi.qty,pi.available_qty,CASE WHEN p.product_type = "conf-simple" THEN (SELECT `remove_flag` FROM products WHERE `id`= `p`.parent_id) ELSE 0 END as parent_remove_flag');
			$this->db->from('products as p');
			$this->db->join('products_category as pc','p.id = pc.product_id OR p.parent_id = pc.product_id','LEFT');  //pc.level =1
			$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
			$this->db->where_in('p.product_type' , array('simple','conf-simple'));
			$this->db->where('p.remove_flag','0');
			$this->db->group_by('p.id');
			$query = $this->db->get();
			// print_r($this->db->last_query());
			return $query->result();
	}

	//$root_category_id,$sub_category
	function getProductForCSVImportUpdate_1(){

		$this->db->select('`p`.product_code,p.launch_date,p.customer_type_ids,p.product_type');
			$this->db->from('products as p');
			$this->db->where_in('p.product_type' , array('simple','configurable'));
			$this->db->where('p.remove_flag','0');
			$this->db->group_by('p.id');
			$query = $this->db->get();
			// print_r($this->db->last_query());exit();
			return $query->result();
	}

	function getProductForInventoryTypesCSVUpdate(){
		$main_db_name=$this->db->database;
		$this->db->select('p.id,p.parent_id,p.sku,p.product_inv_type,pi.qty,fus.org_shop_name,p.launch_date ');
			$this->db->from('products as p');
			$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
			$this->db->join($main_db_name.'.fbc_users_shop as fus','fus.shop_id = p.shop_id');
			$this->db->where_in('p.product_type' , array('simple','conf-simple'));
			$this->db->where('p.remove_flag','0');
			$this->db->where('p.shop_id <>','0');
			$this->db->group_by('p.id');
			$query = $this->db->get();
			// print_r($this->db->last_query());exit();
			return $query->result();
	}

	function getAllProductForCSVImport_opt(){

		$this->db->select('p.id,p.product_type,p.name,p.product_code,p.gender,p.description,p.highlights,p.product_reviews_code,p.launch_date,p.customer_type_ids,p.estimate_delivery_time,p.product_return_time,p.product_drop_shipment,p.meta_title,p.meta_keyword,p.meta_description,p.search_keywords,p.promo_reference,p.can_be_returned,p.coming_soon_flag,pi.qty, pc.category_ids');
		$this->db->distinct();
		$this->db->from('products as p');
		$this->db->join('products_category as pc','p.id = pc.product_id OR p.parent_id = pc.product_id','LEFT');  //pc.level =1
		$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
		// $this->db->where( 'pc.category_ids', $root_category_id);
		// $this->db->where( 'pc.category_ids' => $cat_id));
		$this->db->where_in('p.product_type' , array('simple','configurable'));

		// $this->db->where('p.product_inv_type <>','dropship');
		$this->db->where('p.remove_flag','0');
		$this->db->group_by('p.id');
		$query = $this->db->get();
		// print_r($this->db->last_query());exit();
		// die();
		return $query->result();
	}

	function getAllProductForCSVImport(){

		$this->db->select('p.*, pi.qty, pc.category_ids');
		$this->db->distinct();
		$this->db->from('products as p');
		$this->db->join('products_category as pc','p.id = pc.product_id OR p.parent_id = pc.product_id','LEFT');  //pc.level =1
		$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
		// $this->db->where( 'pc.category_ids', $root_category_id);
		// $this->db->where( 'pc.category_ids' => $cat_id));
		$this->db->where_in('p.product_type' , array('simple','configurable'));

		// $this->db->where('p.product_inv_type <>','dropship');
		$this->db->where('p.remove_flag','0');
		$this->db->group_by('p.id');
		$query = $this->db->get();
		// print_r($this->db->last_query());
		// die();
		return $query->result();
	}

	  public function productslugcount($name,$product_id=''){
		  $count='';
		$this->db->select('count(*) as slugcount');
		$this->db->from('products');
		if(isset($product_id) && $product_id>0){
			$this->db->where('id <>', $product_id);
		}
		$this->db->where('parent_id', 0);
		$this->db->where('name', $name);
		$query = $this->db->get();
		$count= $query->row(0)->slugcount;
		if(isset($product_id) && $product_id>0){
			$count=$count+1;

		}
		return $count;
	}


	public function createproductslugold($url_key)
	{
		$count = 0;
		$slug_name = $url_key;
		while(true)
		{
			$this->db->from('products')->where('url_key', $slug_name);
			if ($this->db->count_all_results() > 0) break;
			$slug_name = $slug_name . '-' . (++$count);
		}
		return $slug_name;
	}

	public function createproductslug($name)
	{
		 $table='products';    //Write table name
		 $field='url_key';         //Write field name
		 $slug = $name;  //Write title for slug
		 $slug = url_title($name);
		 $key=NULL;
		 $value=NULL;
		 $i = 0;
		 $params = array ();
		 $params[$field] = $slug;

		if($key)$params["$key !="] = $value;

		while ($this->db->from($table)->where($params)->get()->num_rows())
			{
				if (!preg_match ('/-{1}[0-9]+$/', $slug ))
				$slug .= '-' . ++$i;
				else
				$slug = preg_replace ('/[0-9]+$/', ++$i, $slug );
				$params [$field] = $slug;
			}

		return $alias=$slug;

	}

	function getProductBaseImage($product_id){
		$sql = "SELECT * FROM `products_media_gallery` where product_id = $product_id AND is_base_image =  1 ";
		$query = $this->db->query($sql);
		return $query->row();
	}


	function get_datatables_dropship_products($price,$inventory,$supplier,$fromDate,$toDate,$image_filter){
		$term = $_REQUEST['search']['value'];
		$this->_get_datatables_query_dropship_products($term,$price,$inventory,$supplier,$fromDate,$toDate,$image_filter);
		if($_REQUEST['length'] != -1)
		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

    public function _get_datatables_query_dropship_products($term,$price,$inventory,$supplier,$fromDate,$toDate,$image_filter) {

		$main_db_name=$this->db->database;

		$column = array('p.name','cg.cat_name', 'p.shop_id','', 'p.price','p.updated_at','');
		$this->db->distinct();
		$this->db->select('p.*,pc.category_ids,cg.cat_name');
		$this->db->from('products as p');
		//$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
		$this->db->join('products_category as pc','p.id = pc.product_id AND pc.level=1','LEFT');
		$this->db->join($main_db_name.'.category as cg','cg.id = pc.category_ids AND cg.cat_level=1','LEFT');
		// $this->db->join($main_db_name.'.fbc_users_shop as fus','p.shop_id = fus.shop_id','LEFT');
		$this->db->where('p.product_type <>','conf-simple');
		$this->db->where('p.product_inv_type','dropship');
		$this->db->where('p.remove_flag','0');


		if($term !=''){

		  $this->db->where(" (
			p.name LIKE '%$term%'
			OR p.description LIKE '%$term%'
			OR p.product_code LIKE '%$term%'
			OR p.sku LIKE '%$term%'
			OR cg.cat_name LIKE '%$term%'
			 )");

		}

		if($image_filter == 'image_filter')
		{
			$this->db->where('`p`.`id` NOT IN (SELECT `product_id` FROM `products_media_gallery`)', NULL, FALSE);
			$this->db->group_by('p.id');
		}

		 if(!empty($price))
		{

			$this->db->where('p.price >=',0);
			$this->db->where('p.price <=', $price);
		}

		/*
		if(!empty($inventory))
		{
			$this->db->where('pi.qty >=',0);
			$this->db->where('pi.qty <=', $inventory);
		}
		*/

		if(!empty($supplier))
		{
			$selected_supplier=array();
			if( strpos($supplier, ',') !== false ) {
				$selected_supplier=explode(',',$supplier);
			}else{
				$selected_supplier[]=$supplier;
			}

			if(count($selected_supplier)==1){
				if($selected_supplier[0]=='Self')
				{
					$fbc_user_id	=	$this->session->userdata('LoginID');
					$this->db->where('p.shop_id',0);
				}
				else if($selected_supplier[0]=='B2B'){
					$this->db->where('p.shop_id >',0);
				}
			}else{
				$this->db->where('p.shop_id',0);
				$this->db->where('p.shop_id >',0);
			}
		}

		if(!empty($fromDate) && empty($toDate)){

			$this->db->where('p.updated_at >=',strtotime($fromDate));
		}
		else if(!empty($toDate) && empty($fromDate)){

			$this->db->where('p.updated_at <=',strtotime($toDate));
		}
		else if(!empty($toDate) && !empty($fromDate))
		{
			$this->db->where('p.updated_at >=',strtotime($fromDate));
			$this->db->where('p.updated_at <=',strtotime($toDate));
		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
			//$this->db->order_by('coalesce(updated_at, created_at) DESC');
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->db->order_by(key($order), $order[key($order)]);
		}else{
			$this->db->order_by('p.id', 'desc');
		}

    }


   public function count_all_dropship_products($price,$inventory,$supplier,$fromDate,$toDate,$image_filter) {
	   $main_db_name=$this->db->database;
	   $column = array('p.name','pc.category_ids', 'p.shop_id','', 'p.price','p.updated_at','');
	   $term = $_REQUEST['search']['value'];
		$this->db->distinct();
		$this->db->select('p.*,pc.category_ids,cg.cat_name');
		$this->db->from('products as p');
		//$this->db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
		$this->db->join('products_category as pc','p.id = pc.product_id AND pc.level=1','LEFT');
		$this->db->join($main_db_name.'.category as cg','cg.id = pc.category_ids AND cg.cat_level=1','LEFT');
		$this->db->join($main_db_name.'.fbc_users_shop as fus','p.shop_id = fus.shop_id','LEFT');
		$this->db->where('p.product_type <>','conf-simple');
		$this->db->where('p.product_inv_type','dropship');
		$this->db->where('p.remove_flag','0');

		if($term !=''){

		  $this->db->where(" (
			p.name LIKE '%$term%'
			OR p.description LIKE '%$term%'
			OR p.product_code LIKE '%$term%'
			OR p.sku LIKE '%$term%'
			OR cg.cat_name LIKE '%$term%'
			 )");

		}

		if($image_filter == 'image_filter')
		{
			$this->db->where('`p`.`id` NOT IN (SELECT `product_id` FROM `products_media_gallery`)', NULL, FALSE);
			$this->db->group_by('p.id');
		}

		  if(!empty($price))
		{

			$this->db->where('p.price >=',0);
			$this->db->where('p.price <=', $price);
		}
		/*
		if(!empty($inventory))
		{
			$this->db->where('pi.qty >=',0);
			$this->db->where('pi.qty <=', $inventory);
		}
		*/



		if(!empty($supplier))
		{
			if( strpos($supplier, ',') !== false ) {
				$selected_supplier=explode(',',$supplier);
			}else{
				$selected_supplier[]=$supplier;
			}

			if(count($selected_supplier)==1){
				if($selected_supplier[0]=='Self')
				{
					$fbc_user_id	=	$this->session->userdata('LoginID');
					$this->db->where('p.shop_id',0);
				}
				else if($selected_supplier[0]=='B2B'){
					$this->db->where('p.shop_id >',0);
				}
			}else{
				$this->db->where('p.shop_id',0);
				$this->db->where('p.shop_id >',0);
			}
		}

		if(!empty($fromDate) && empty($toDate)){

			$this->db->where('p.updated_at >=',strtotime($fromDate));
		}
		else if(!empty($toDate) && empty($fromDate)){

			$this->db->where('p.updated_at <=',strtotime($toDate));
		}
		else if(!empty($toDate) && !empty($fromDate))
		{
			$this->db->where('p.updated_at >=',strtotime($fromDate));
			$this->db->where('p.updated_at <=',strtotime($toDate));
		}

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->db->order_by(key($order), $order[key($order)]);
		}else{
			$this->db->order_by('p.id', 'desc');
		}
       return $this->db->count_all_results();
    }

	 function count_filtered_dropship_products($price,$inventory,$supplier,$fromDate,$toDate,$image_filter){
		$term = $_REQUEST['search']['value'];
		$this->_get_datatables_query_dropship_products($term,$price,$inventory,$supplier,$fromDate,$toDate,$image_filter);
		$query = $this->db->get();
		return $query->num_rows();
	 }

	function calculate_webshop_price($price,$percent='')
	{
		$Response=array();
		$tax_amount=0;

		$webshop_price=$price;

		if($price>0 && $percent>0){
			$tax_amount = ($percent / 100) * $price;

			$webshop_price=$tax_amount+$price;
		}

		$Response['tax_amount'] = $tax_amount;
		$Response['webshop_price'] = $webshop_price;

		return $Response;
	}

	 function decrementProductAvailableQty($product_id,$available_qty)
	 {
		 $this->db->reset_query();
		 $sql="	UPDATE products_inventory SET available_qty =  available_qty - $available_qty WHERE product_id = $product_id ";
		 $this->db->query($sql);
		 return true;
	 }

	 function getProductsMaintCategoryNames($product_id){
		$catgory_name='-';
		$main_db_name=$this->db->database;
		$sql = "SELECT GROUP_CONCAT(c.cat_name) as cat_name FROM `products_category` as pc LEFT JOIN $main_db_name.category as c ON pc.category_ids = c.id  where pc.product_id = $product_id AND pc.level=0";
		$query = $this->db->query($sql);
		$Row= $query->row();

		if(isset($Row) && $Row->cat_name!=''){
			$catgory_name=$Row->cat_name;
		}

		return $catgory_name;
	}

	function getProductsallCategoryNames($product_id){
		$catgory_name='-';
		$main_db_name=$this->db->database;
		$sql = "SELECT GROUP_CONCAT(c.cat_name separator ',') as cat_name FROM `products_category` as pc LEFT JOIN $main_db_name.category as c ON pc.category_ids = c.id  where pc.product_id = $product_id  and level = 0";
		// $sql = "SELECT GROUP_CONCAT(c.cat_name separator '>>') as cat_name FROM `products_category` as pc LEFT JOIN $main_db_name.category as c ON pc.category_ids = c.id  where pc.product_id = $product_id ";
		$query = $this->db->query($sql);
		$Row= $query->row();

		if(isset($Row) && $Row->cat_name!=''){
			$catgory_name=$Row->cat_name;
		}

		return $catgory_name;
	}

	function getCategoryProductCount($category_id,$cat_level)
	{
		$this->db->select('p.*');
		$this->db->from('products as p');
		$this->db->join('products_category as pc','p.id = pc.product_id','LEFT');
		$this->db->where(array('pc.category_ids'=>$category_id,'p.remove_flag'=>0));
		$this->db->order_by('p.created_at','desc');
		$query = $this->db->get('');

		return $query->num_rows();
	}

	function getVariantDetailsForProducts($shop_id, $product_id){

			$main_db_name=$this->db->database;
			// $this->db->select('pvm.*,ma.attr_name');
			// $this->db->from('products_variants_master as pvm');
			// $this->db->join($main_db_name.'.eav_attributes as ma','pvm.attr_id = ma.id AND ma.attr_type=2','LEFT');
			// $this->db->where('pvm.product_id',$product_id);
			$this->db->select('pv.*,ma.attr_name,mao.attr_options_name');
			$this->db->from('products_variants as pv');
			$this->db->join($main_db_name.'.eav_attributes as ma','pv.attr_id = ma.id AND ma.attr_type=2','LEFT');
			$this->db->join($main_db_name.'.eav_attributes_options as mao','mao.id = pv.attr_value AND ma.attr_type=2','LEFT');
			$this->db->where('pv.product_id',$product_id);
			$query = $this->db->get();
			//print_r($this->db->last_query());
			$resultArr = $query->result();

			return $resultArr;

    }

    public function getMultiLangProduct($product_id, $code)
	{
		$this->db->select('*');
        $this->db->from('multi_lang_products');
        $this->db->where('product_id', $product_id);
        $this->db->where('lang_code', $code);
        $query = $this->db->get();
		$resultArr = $query->row();
		return $resultArr;
	}

	public function countCustomProduct($id, $code)
	{
		$this->db->select('*');
        $this->db->from('multi_lang_products');
        $this->db->where('product_id', $id);
        $this->db->where('lang_code', $code);
        $query = $this->db->get();
		$resultArr = $query->num_rows();
		return $resultArr;
	}

	public function updateProductData($tableName,$condition,$updateData)
    {
    	$this->db->where($condition);

		$this->db->update($tableName,$updateData);
		if($this->db->affected_rows() > 0){
			return true;
		}else{
			return false;
		}
    }

	// public function getPricePermissionByShopID($seller_id,$owner_id)
	// {
	// 	$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$seller_id),'shop_id,fbc_user_id,database_name');

	// 	$db = DB_PREFIX.$FBCData->database_name;

	// 	$this->db->select('*');
	// 	$this->db->from($db.'.b2b_customers t1');
	// 	$this->db->join($db.'.b2b_customers_details t2', 't1.id = t2.customer_id');
	// 	$this->db->where($db.'.t1.shop_id',$owner_id);

	// 	$query = $this->db->get();
	// 	return $query->row();

	// }

	public function get_datatables_products_adjustemnt(){
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_get_datatables_products_inventory_adjustemnt($term);
		if($_REQUEST['length'] != -1)
		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	public function _get_datatables_products_inventory_adjustemnt($term='')
	{
		  $column = array('pia.id','p.sku','p.name','pia.type','pia.adjustment','pia.processed', 'pia.source');
			$this->db->select('pia.*, p.name,p.sku');
			$this->db->from('products_inventory_adjustments as pia');
			$this->db->join('products as p','p.id = pia.product_id','LEFT');
			if($term!=''){
		  $this->db->where(" (
				p.sku LIKE '%$term%'
				OR pia.type LIKE '%$term%'
				OR pia.adjustment LIKE '%$term%'
				OR pia.processed LIKE '%$term%'
				OR pia.source LIKE '%$term%'
				OR p.name LIKE '%$term%'
				 )");
		  }

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}else{
				$this->db->order_by('pia.id', 'DESC');
			}
	}

  public function countFiltteredProductInventory()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_get_datatables_products_inventory_adjustemnt($term);
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function countProductInventoryAdjustmentRow()
		{
			$this->db->select('*');
			$this->db->from('products_inventory_adjustments');
			$query = $this->db->count_all_results();
			return $query;
		}

	//new function
	public function getDataProductAttrMultiple($tableName,$condition1,$condition2,$select)
	{
		$condition1 = implode(',',$condition1);
		$condition2 = implode(',',$condition2);
		if(!empty($select))
		{
		$this->db->select($select);
		}
		$this->db->where("product_id IN (".$condition1.") AND attr_id IN (".$condition2.") ",null,false);
		$query = $this->db->get($tableName);
		return $query->result_array();
	}

	public function getProductMediaVariants ($product_id){
		$this->db->select('media_variant_id');
		$this->db->from('products');
		$this->db->where('id',$product_id);
		$query = $this->db->get();
		return $query->row();
	}

	function getVariantDetailsByProductID($product_id,$variant_id){

		$main_db_name=$this->db->database;

		$this->db->select('DISTINCT(mao.attr_options_name),ma.attr_name,mao.id as attr_value');
		$this->db->from('products_variants as pv');
		$this->db->join($main_db_name.'.eav_attributes as ma','pv.attr_id = ma.id AND ma.attr_type=2','LEFT');
		$this->db->join($main_db_name.'.eav_attributes_options as mao','mao.id = pv.attr_value AND ma.attr_type=2','LEFT');
		$this->db->where('pv.parent_id',$product_id);
		$this->db->where('pv.attr_id',$variant_id);
		$query = $this->db->get();
		//print_r($this->db->last_query());
		$resultArr = $query->result();

		return $resultArr;
	}

	public function getProductMediaGalleryDataByID($product_id,$media_id){
		$this->db->select('*');
		$this->db->from('products_media_gallery');
		$this->db->where('product_id',$product_id);
		$this->db->where('id',$media_id);
		$query = $this->db->get();
		//print_r($this->db->last_query());
		$resultArr = $query->result();
		return $resultArr;

	}

	function get_publication() {
		$this->db->select('*');
		$this->db->from('publisher');
		$query = $this->db->get();
		//print_r($this->db->last_query());
		$resultArr = $query->result();
		return $resultArr;
	}

	function get_publication_by_id($id) {
		$this->db->select('*');
		$this->db->from('publisher');
		$this->db->where('id', $id);
		$query = $this->db->get();
		//print_r($this->db->last_query());
		$resultArr = $query->row();
		return $resultArr->commision_percent;
	}

	function get_product_commission_by_id($id) {
		$this->db->select('*');
		$this->db->from('products');
		$this->db->where('publisher_id', $id);
		$query = $this->db->get();
		//print_r($this->db->last_query());
		$resultArr = $query->row();
		return $resultArr->pub_com_percent;
	}

	function get_gifts_data() {
		$this->db->select('*');
		$this->db->from('gift_master');
		$query = $this->db->get();
		return $query->result_array();
	}

	function getBundleForProducts($product_id)
    {
		$main_db_name=$this->db->database;
		$this->db->select('pb.*');
		$this->db->from('products_bundles as pb');
		//$this->db->join($main_db_name.'.eav_attributes as ma','pvm.attr_id = ma.id AND ma.attr_type=2','LEFT');
		$this->db->where('pb.bundle_product_id',$product_id);
		$query = $this->db->get();
		$resultArr = $query->result_array();
		return $resultArr;
    }
    function removedBundleProductItemById($bundle_product_id)
    {
		$this->db->select('pb.*,p.price as pprice,p.cost_price as pcost_price,p.tax_percent as ptax_percent,p.tax_amount as ptax_amount,p.webshop_price as pwebshop_price');
		$this->db->from('products_bundles as pb');
		$this->db->join('products as p','pb.bundle_product_id = p.id','LEFT');
		$this->db->where('pb.id',$bundle_product_id);
		$query = $this->db->get();
		$resultArr = $query->row();
		return $resultArr;
    }
	
}
