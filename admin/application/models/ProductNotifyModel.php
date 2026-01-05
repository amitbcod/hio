<?php
class ProductNotifyModel extends CI_Model
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
			$this->shop_db = $this->load->database($config_app,TRUE);
		}else{
			redirect(base_url());
		}

	}

	public function get_datatables_data(){

		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_get_datatables_query_product($term);
		if($_REQUEST['length'] != -1)
		$this->shop_db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->shop_db->get();
		return $query->result();

	}

	public function _get_datatables_query_product($term=''){

		$column = array('pr.id','pr.email_id', 'c.first_name','p.name', 'pr.notify_count','pr.created_at');

		$this->shop_db->select('pr.*,p.name as product_name ,p.id as product_id, c.first_name, c.last_name, c.id as customer_id, c.customer_type_id');
		$this->shop_db->from('products_keep_me_notify pr');
		$this->shop_db->join('products p','p.id = pr.product_id','Left');
		$this->shop_db->join('customers c','c.id = pr.customer_id','Left');
		$this->shop_db->where('pr.notify_email_sent','!1');
		if($term !=''){
		  $this->shop_db->where(" (
			pr.id LIKE '%$term%'
			OR pr.email_id LIKE '%$term%'
			OR c.first_name LIKE '%$term%'
			OR c.last_name LIKE '%$term%'
			OR p.name LIKE '%$term%'
			)");
		}

		if(isset($_REQUEST['order']))
		{
			$this->shop_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}else{
			$this->shop_db->order_by('pr.id', 'DESC');
		}

	}

	public function countTotalRecords(){

		$this->shop_db->select('*');
		$this->shop_db->from('products_keep_me_notify');
		$this->shop_db->where('notify_email_sent','!1');
		$query = $this->shop_db->get();
		$result = $query->result();
		return count($result);
	}

	public function countFiltteredProduct()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value']!='')?$_REQUEST['search']['value']:'';
		$this->_get_datatables_query_product($term);
		$query = $this->shop_db->get();
		return $query->num_rows();
	}



}
