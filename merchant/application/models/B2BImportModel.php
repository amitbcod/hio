<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class B2BImportModel extends CI_Model {

    function __construct()
	{
		parent::__construct();
       // $this->load->database();

       $fbc_user_id	=	$this->session->userdata('ShopOwnerId');  //old LoginID
	   $shop_id	=	$this->session->userdata('ShopID');

	}


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

	public function insertDataSupplier($table,$data,$seller_shop_id)
	{

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$seller_shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);


			$this->seller_db->insert($table,$data);
			if($this->seller_db->affected_rows() > 0)
			{
				$last_insert_id=$this->seller_db->insert_id();
				return $last_insert_id;
			}
			else
			{
				return false;
			}
		}
	}

	public function updateData($tableName,$condition,$updateData)
    {
		$this->db->where($condition);

		  $this->db->update($tableName,$updateData);
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




	//deleteData
	function deleteDataById($tablename,$where){
		$this->db->delete($tablename,$where);
		$this->db->reset_query();
	}

	//deleteData
	function deleteDataByIdSupplier($tablename,$where,$seller_shop_id){

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$seller_shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$this->seller_db->delete($tablename,$where);
			$this->seller_db->reset_query();
		}
	}

	 //getSingleDataByID
	 public function getSingleDataByIDSupplier($supplier_shop_id,$tableName,$condition,$select)
	  {

	  	$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$supplier_shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

		    if(!empty($select))
		    {
		      $this->seller_db->select($select);
		    }
		    $this->seller_db->where($condition);
		    $query = $this->seller_db->get($tableName);

		   // echo $this->seller_db->last_query();exit;

		    return $query->row();

		}
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

	///getDataByID
	public function getMultiDataByIdSupplier($supplier_shop_id,$tableName,$condition,$select,$order_by_column='',$order_by_type='')
	{
		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$supplier_shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

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




    function getUserShopDetails(){

        $this->db->select("*");
        $this->db->from('fbc_users_shop');
        $this->db->join('fbc_users','fbc_users_shop.fbc_user_id = fbc_users.fbc_user_id','INNER');
        $this->db->where('fbc_users_shop.database_name !=', NULL);
        $this->db->where('fbc_users_shop.b2b_status', 1);
        $this->db->where('fbc_users.status', 1);
        $query = $this->db->get();

        return $query->result();


    }

    function getB2BCustomer($seller_db_name, $shop_id){

       $query =$this->db->query("SELECT * FROM ".DB_PREFIX.$seller_db_name.".b2b_customers WHERE `shop_id` = ".$shop_id);
        return $query->row();
    }

    function getB2BCustomerDetails($supplier_shop_id,$shop_id){


		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$supplier_shop_id),'shop_id,fbc_user_id,database_name');
		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);


			$this->seller_db->select("*");
			$this->seller_db->from('b2b_customers');
			$this->seller_db->join('b2b_customers_details','b2b_customers.id = b2b_customers_details.customer_id','INNER');
			$this->seller_db->where('b2b_customers.shop_id', $shop_id);

			$query = $this->seller_db->get();

			return $query->row();

		}


    }


	public function getB2Bterms($supplier_shop_id){

		$this->db->select("payments_terms_upload,terms_condition_upload");
        $this->db->from('fbc_users_b2b_details');
        $this->db->where('shop_id', $supplier_shop_id);
        $query = $this->db->get();

        return $query->row();


	}

	public function getProductNameSku($supplier_shop_id,$term_requested) {

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$supplier_shop_id),'shop_id,fbc_user_id,database_name');

		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);


			$result =$this->seller_db->query("SELECT * FROM products WHERE `product_type` != 'configurable' AND `remove_flag` = '0' and name like '%".$term_requested."%' or sku like '%".$term_requested."%'");
			// echo $this->seller_db->last_query();exit;
			return $result->result();

		}


    }


	public function CheckProductsAvailable($barcode='',$sku='', $supplier_shop_id = 0){

		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$supplier_shop_id),'shop_id,fbc_user_id,database_name');

		if(isset($FBCData) && $FBCData->database_name!='')
		{
			$fbc_user_database=$FBCData->database_name;
			$fbc_user_id=$FBCData->fbc_user_id;

			$this->load->database();
			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);
			$this->seller_db = $this->load->database($config_app,TRUE);

			$main_db_name=$this->db->database;

			if($barcode !='' || $sku !='') {
				$this->seller_db->select('p.id, p.parent_id, p.name, p.sku, p.product_type, p.launch_date, p.price, cg.id as category_id, pc.category_ids, cg.cat_name, pi.qty,p.tax_percent');
				$this->seller_db->from('products as p');
				$this->seller_db->join('products_category as pc','p.id = pc.product_id OR p.parent_id = pc.product_id','LEFT');
				$this->seller_db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
				$this->seller_db->join($main_db_name.'.category as cg','cg.id = pc.category_ids','LEFT');
				if($barcode !=''){
					$this->seller_db->where('barcode', $barcode);
				}
				if($sku !=''){
					$this->seller_db->where('sku', $sku);
				}

				$this->seller_db->where('remove_flag', 0);
				$this->seller_db->group_by('p.id');
				$result = $this->seller_db->get();
				if($result->num_rows() > 0 ){

					$first_result = $result->row();

					//print_r($first_result);

					if($first_result->product_type== 'conf-simple'){

						$this->seller_db->select('p.id, p.parent_id, p.name, p.sku, p.product_type, p.launch_date, p.price, cg.id as category_id, pc.category_ids, cg.cat_name, pi.qty,p.tax_percent');
						$this->seller_db->from('products as p');
						$this->seller_db->join('products_category as pc','p.id = pc.product_id OR p.parent_id = pc.product_id','LEFT');
						$this->seller_db->join('products_inventory as pi','p.id = pi.product_id','LEFT');
						$this->seller_db->join($main_db_name.'.category as cg','cg.id = pc.category_ids','LEFT');
						$this->seller_db->where('p.parent_id', $first_result->parent_id);
						$this->seller_db->where('remove_flag', 0);
						$this->seller_db->group_by('p.id');


						$result2 = $this->seller_db->get();
						return $result2->result();

					}else{
						return $result->result();
					}
				}else{
					return false;
				}
			}else{
				return false;
			}


		}



    }


	public function b2b_imports_items($import_id){


		$this->db->select('product_id,parent_id');
		$this->db->from('single_b2b_imports_items');
		$this->db->where('import_id', $import_id);
		$this->db->group_by('parent_id');

		$query = $this->db->get();
		$result = $query->row();

		if($result->parent_id == 0){
			return $result->product_id;
		}else{
			return $result->parent_id;
		}

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




}
