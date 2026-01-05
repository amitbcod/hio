<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class InboundModel extends CI_Model {



    function __construct()

	{

		parent::__construct();

       // $this->load->database();



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







	function generate_new_transaction_id()

    {

		$payment_id='';

        $user_transaction_id = $this->getLastUserTransactionId();

		if(isset($user_transaction_id) && $user_transaction_id->inbound_no!='')

		{

			$last_inc_id		= $user_transaction_id->inbound_no;

			$last_order_id		= str_replace('INB-','',$last_inc_id);

			$payment_id         = $last_order_id+1;

		}else{

			 $payment_id        = 1001;

		}



		$transaction_id = 'INB-'.$payment_id;

        return $transaction_id;

    }



	function getLastUserTransactionId()

	 {

		$this->db->select('id,inbound_no');

		$this->db->order_by('id','desc');

		$this->db->limit(1);

		$query = $this->db->get('inbound');

		return $query->row();

	 }





	public function getTotalQtyScannedAndPrice($inbound_id){



		$this->db->select("sum(itms.qty_scanned) as qty_scanned, sum(itms.qty_scanned*p.webshop_price) as price");

		$this->db->from('inbound_items_saved as itms');

		$this->db->join('products as p','itms.product_id = p.id','LEFT');

		$this->db->where('itms.inbound_id',$inbound_id);

		$this->db->where('p.remove_flag',0);

		$query = $this->db->get();

		//echo $this->db->last_query();//exit;



		return $query->row();

	}



	public function getTotalInboundItemQtyPrice($inbound_id){



		$this->db->select("sum(qty_scanned) as qty_scanned, sum(total_price) as price");

		$this->db->from('inbound_items');

		$this->db->where('inbound_id',$inbound_id);



		$query = $this->db->get();

		return $query->row();



	}



    public function getInboundDetails($search_term = ''){

        $this->db->select('*');

		$this->db->from('inbound');

		$this->db->order_by('id DESC');

		if($_REQUEST['length'] != -1)

		$this->db->limit($_REQUEST['length'], $_REQUEST['start']);

		$result = $this->db->get();

		 //echo $this->db->last_query();exit;

		if($result->num_rows() > 0 ){

			return $result->result_array();

		}else{

			return false;

		}



    }



	function count_all_InboundOrder(){



		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';



		$this->db->select('*');

		$this->db->from('inbound');



		$result = $this->db->get();



		return $result->num_rows();

	}



	public function getInboundData($order_id){



		$this->db->select('*');

		$this->db->from('inbound');

		$this->db->where('id',$order_id);



		$result = $this->db->get();

		return $result->row_array();



	}



    public function CheckProductsAvailable($barcode='',$sku=''){



        if($barcode !='' || $sku !='') {

            $this->db->select('*');

            $this->db->from('products');

            if($barcode !=''){

                $this->db->where('barcode', $barcode);

            }

            if($sku !=''){

                $this->db->where('sku', $sku);

            }

            $this->db->where('remove_flag', 0);

            $result = $this->db->get();

            if($result->num_rows() > 0 ){

                return $result->row();

            }else{

                return false;

            }

        }else{

            return false;

        }



    }


    public function getProductCategoryById($id,$level)
    {
    	$main_db_name=$this->db->database;
        $this->db->select('pc.category_ids, cg.cat_name, cg.cat_level, cg.parent_id');
        $this->db->from('products_category as pc');
		$this->db->join($main_db_name.'.category as cg','cg.id = pc.category_ids AND  cg.cat_level In ('.$level.')','INNER');
		$this->db->where('pc.product_id',$id);
		$query = $this->db->get();
		// echo $this->db->last_query();exit;
		return $query->result();
    }

    public function getProductChildCategoryById($id,$category_id,$level)
    {
    	$main_db_name=$this->db->database;
        $this->db->select('pc.category_ids, cg.cat_name, cg.cat_level, cg.parent_id');
        $this->db->from('products_category as pc');
		$this->db->join($main_db_name.'.category as cg','cg.id = pc.category_ids AND cg.parent_id= '.$category_id.' AND cg.cat_level In ('.$level.')','INNER');
		$this->db->where('pc.product_id',$id);
		$query = $this->db->get();
		// echo $this->db->last_query();exit;
		return $query->result();
    }


	public function getProductNameSku($term_requested,$search_for_barcode_flag='')

    {

        $barcode_search_qry= '';
    	if (isset($search_for_barcode_flag) && $search_for_barcode_flag != '') {
    		$barcode_search_qry= " or barcode like '%".$term_requested."%'";
    	}
        $result =$this->db->query("SELECT * FROM products WHERE `product_type` != 'configurable' AND `remove_flag` = '0' and name like '%".$term_requested."%' or sku like '%".$term_requested."%' ".$barcode_search_qry." ");

		// echo $this->db->last_query();

        return $result->result();

    }





	public function getAllInboundSavedProductsById($inbound_id){



		$this->db->select("*");

		$this->db->from('inbound_items_saved as itms');

		$this->db->join('products as p','p.id = itms.product_id','INNER');

		$this->db->where('itms.inbound_id',$inbound_id);

		$this->db->where('p.remove_flag',0);

		$this->db->order_by("itms.id", "DESC");

		$query = $this->db->get();

		//echo $this->db->last_query();//exit;



		return $query->result_array();

	}









	public function getAllInboundOrderedProductsById($inbound_id){



		$this->db->select("itms.*,inbound.inbound_no,p.parent_id,p.sku");

		$this->db->from('inbound_items as itms');

		$this->db->join('products as p',' p.id = itms.product_id','INNER');

		$this->db->join('inbound','inbound.id = itms.inbound_id','INNER');

		$this->db->where('itms.inbound_id',$inbound_id);

		$this->db->order_by("itms.id", "DESC");

		$query = $this->db->get();

		//echo $this->db->last_query();//exit;



		return $query->result_array();

	}



	public function CheckIfInboundProductExist($product_id,$order_id){



		$this->db->select("*");

		$this->db->from('inbound_items_saved');

		$this->db->where('inbound_id',$order_id);

		$this->db->where('product_id',$product_id);



		$query = $this->db->get();



		return $query->row_array();



	}



	public function getSavedInboundProducts($product_id,$order_id){



		$this->db->select("ibsm.*, p.product_inv_type,p.price,p.webshop_price,p.name,p.shop_id,pi.qty,pi.available_qty");

		$this->db->from('inbound_items_saved as ibsm');

		$this->db->join('products as p','p.id = ibsm.product_id','INNER');

		$this->db->join('products_inventory as pi','ibsm.product_id = pi.product_id','INNER');

		$this->db->where('ibsm.inbound_id',$order_id);

		$this->db->where('ibsm.product_id',$product_id);



		$query = $this->db->get();



		return $query->row_array();



	}







	public function getvariantsValuesByProduct($product_id){



		$this->db->select("*");

		$this->db->from('products_variants');

		$this->db->where('product_id',$product_id);

		$query = $this->db->get();



		return $query->result_array();

	}



	public function getAllvariantsValues($attr_id,$attr_value){





		$this->db->select('eav_attributes.attr_name, eav_option.attr_options_name');

		$this->db->join('eav_attributes_options as eav_option','eav_option.attr_id = eav_attributes.id','LEFT');

		$this->db->where('eav_attributes.id',$attr_id);

		$this->db->where('eav_option.id',$attr_value);



		$query = $this->db->get('eav_attributes');

		return $query->row_array();

	}



	public function getShopDetails($shop_id){



		$this->db->select('*');

		$this->db->where('shop_id',$shop_id);

		$query = $this->db->get('fbc_users_shop');

		return $query->row_array();





	}



	function getWebshopDetails($shop_id, $fbc_user_id){





		$this->db->select('site_contact_email as email ,site_contact_no as mobile_no');

		$this->db->where('shop_id',$shop_id);

		$query = $this->db->get('fbc_users_webshop_details');



		if($query->num_rows() > 0 ){

			return $query->row_array();

		}else{



			$this->db->select('email, mobile_no');

			$this->db->where('fbc_user_id',$fbc_user_id);

			$query = $this->db->get('fbc_users');



			return $query->row_array();



		}



	}



	function getInboundAndNameNoById($order_id){



		$this->db->select('inbound_no,name');

		$this->db->where('id',$order_id);

		$query = $this->db->get('inbound');

		return $query->row_array();

	}

	public function getProductNameSku_for_add_product($term_requested)
    {
        $result =$this->db->query("SELECT * FROM products WHERE `product_type` != 'configurable' AND `remove_flag` = '0' AND `product_inv_type` != 'dropship' AND (name like '%".$term_requested."%' OR sku like '%".$term_requested."%' ) ");
		// echo $this->db->last_query();
        return $result->result();

    }

    public function getCustomData()
	{
		$result = $this->db->get_where('custom_variables', array('identifier' => 'use_advanced_warehouse'))->row();
		return $result;
	}


}
