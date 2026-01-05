<?php
class B2BModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		// $FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shop_id,fbc_user_id,database_name');
		// if (isset($FBCData) && $FBCData->database_name != '') {
		// 	$fbc_user_database = $FBCData->database_name;

		// 	$this->load->database();
		// 	$config_app = fbc_switch_db_dynamic(DB_PREFIX . $fbc_user_database);
		// 	$this->seller_db = $this->load->database($config_app, TRUE);
		// } else {
		// 	redirect(base_url());
		// }
	}


	public function getSingleproducts_special_price($special_price_id)
	{
		$this->seller_db->select('psp.*,p.sku,p.name,p.price');
		$this->seller_db->from('products_special_prices_b2b as psp');
		$this->seller_db->join('products as p', 'p.id=psp.product_id');
		$this->seller_db->where(array('psp.id' => $special_price_id));


		$query = $this->seller_db->get();
		$result = $query->result();
		return $result;
	}

	public function getAllproducts_special_prices_b2b($shop_id)
	{
		$this->seller_db->select('psp.*,p.sku,p.name,p.price');
		$this->seller_db->from('products_special_prices_b2b as psp');
		$this->seller_db->where(array('psp.shop_id' => $shop_id));
		$this->seller_db->join('products as p', 'p.id=psp.product_id');
		$this->seller_db->order_by('psp.special_price_from', 'DESC');
		$this->seller_db->where(array('p.remove_flag' => 0)); //18-7-21
		// $this->seller_db->where(array('p.status'=>1)); //18-7-21
		$query = $this->seller_db->get();
		$result = $query->result();
		return $result;
	}
	public function getSingleDataByID($tableName, $condition, $select)
	{
		if (!empty($select)) {
			$this->seller_db->select($select);
		}
		$this->seller_db->where($condition);
		$query = $this->seller_db->get($tableName);
		return $query->row();
	}

	public function B2BdeleteData($tableName, $condition)
	{
		$this->seller_db->where($condition);
		$this->seller_db->delete($tableName);
		if ($this->seller_db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getVariants($product_id)
	{
		$main_db_name = $this->db->database;

		$this->seller_db->select('ev.attr_name, evp.attr_options_name');
		$this->seller_db->from('products_variants pv');
		$this->seller_db->join($main_db_name . '.eav_attributes ev', 'ev.id=pv.attr_id', 'INNER');
		$this->seller_db->join($main_db_name . '.eav_attributes_options evp', 'evp.id=pv.attr_value AND ev.id = evp.attr_id', 'INNER');
		$this->seller_db->where(array('pv.product_id' => $product_id));
		$query = $this->seller_db->get();
		$result = $query->result_array();

		return $result;
	}

	public function insertData($table, $data)
	{
		$this->seller_db->insert($table, $data);
		if ($this->seller_db->affected_rows() > 0) {
			$last_insert_id = $this->seller_db->insert_id();
			return $last_insert_id;
		} else {
			return false;
		}
	}

	public function updateNewData($tableName, $condition, $updateData)
	{
		$this->seller_db->where($condition);
		$this->seller_db->update($tableName, $updateData);
		if ($this->seller_db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getspecailpricingForCSVImport($shop_id)
	{
		$this->seller_db->select('psp.*,p.id as product_id,p.parent_id,p.product_type,p.sku,p.name,p.webshop_price,p.remove_flag');
		$this->seller_db->from('products_special_prices_b2b as psp');
		$this->seller_db->join('products as p', 'p.id=psp.product_id');
		$this->seller_db->join('products as parent', 'p.parent_id = parent.id', 'left');
		$this->seller_db->where(array('psp.shop_id' => $shop_id));
		$this->seller_db->where(array('p.remove_flag' => 0));	//05-07-21
		$this->seller_db->where('(parent.remove_flag IS NULL OR parent.remove_flag = 0)');
		$query = $this->seller_db->get();
		// echo $this->theme_db->last_query();die();
		$result = $query->result();
		return $result;
	}

	public function check_product_exists_by_sku($sku)
	{
		$this->seller_db->select('*');
		// $this->seller_db->where('status',1);
		$this->seller_db->where('sku', $sku);
		$this->seller_db->where('remove_flag', 0);  //05-07-21
		$query = $this->seller_db->get('products');
		$resultArr = $query->row_array();
		//echo $this->db->last_query();
		return $resultArr;
	}

	public function check_SP__by_ID_Cust_type($product_id)
	{
		$this->seller_db->select('*');
		$this->seller_db->where('product_id', $product_id);
		$query = $this->seller_db->get('products_special_prices_b2b');
		$resultArr = $query->row_array();
		//echo $this->db->last_query();
		return $resultArr;
	}

	public function getB2BCatSubCatDetails($shop_id)
	{

		$main_db_name = $this->db->database;

		$this->seller_db->distinct();
		$this->seller_db->select('b2bCat.*, cg1.cat_name, cg.cat_name as sub_cat_name, cg.parent_id');
		$this->seller_db->from('fbc_users_category_b2b as b2bCat');
		$this->seller_db->join($main_db_name . '.category as cg', 'cg.id = b2bCat.category_id AND cg.cat_level=1', 'INNER');
		$this->seller_db->join($main_db_name . '.category as cg1', 'cg1.id = cg.parent_id AND cg1.cat_level=0', 'INNER');
		$this->seller_db->join($main_db_name . '.fbc_users_shop as FUS', 'FUS.fbc_user_id = b2bCat.fbc_user_id', 'INNER');
		$this->seller_db->where(array('FUS.shop_id' => $shop_id, 'b2bCat.level' => 1));
		$query = $this->seller_db->get();
		//print_r($this->seller_db->last_query());
		return $query->result();
	}

	public function getUersB2BDetailsByShopId($shop_id)
	{
		$this->db->select('FBD.*, FUS.org_shop_name as webshop_name, FUS.fbc_user_id, FUS.shop_id, FU.owner_name');
		$this->db->from('fbc_users_b2b_details FBD');
		$this->db->join('fbc_users_shop FUS', 'FUS.shop_id = FBD.shop_id', 'inner');
		$this->db->join('fbc_users FU', 'FU.fbc_user_id = FUS.fbc_user_id', 'inner');
		$this->db->where(array('FBD.shop_id' => $shop_id));
		$query = $this->db->get();
		//print_r($this->db->last_query());
		$row = $query->row();
		return $row;
	}

	public function getUersNewB2BDetailsByShopId($seller_shop_id, $shop_id)
	{
		$main_db_name = $this->db->database;
		$seller_db =  DB_NAME_PREFIX . $seller_shop_id;
		$this->seller_db->select('BCD.*');
		$this->seller_db->from($seller_db . '.b2b_customers_details BCD');
		$this->seller_db->join($seller_db . '.b2b_customers as BC', 'BC.id = BCD.customer_id', 'inner');
		$this->seller_db->where(array('BC.shop_id' => $shop_id));
		$query = $this->seller_db->get();
		// print_r($this->seller_db->last_query());
		$row = $query->row();
		return $row;
	}

	//***
	public function get_all_customers($search_param = '')
	{
		// $main=$this->db->database;
		$seller_db = $this->seller_db->database;
		$this->db->select('main.*, sbc.shop_id, us.org_shop_name , us.bill_state , cm.country_name, bo.created_at, sum(bo.subtotal) as total_purchase');
		$this->db->from('fbc_users as main');
		$this->db->join($seller_db . '.b2b_customers as sbc', 'main.shop_id = sbc.shop_id');
		$this->db->join('fbc_users_shop as us', 'main.shop_id = us.shop_id');
		$this->db->join($seller_db . '.country_master as cm', 'cm.country_code = us.country_code');
		$this->db->join($seller_db . '.b2b_orders as bo', 'bo.shop_id = sbc.shop_id');
		$this->db->where('bo.status !=', 3);
		$this->db->where('bo.status !=', 7);
		$this->db->where('bo.parent_id ', 0);
		$this->db->where('bo.main_parent_id', 0);

		if (isset($search_param['keyword']) && $search_param['keyword'] != "") {
			$this->db->group_start();
			$this->db->like('main.owner_name', $search_param['keyword']);
			$this->db->or_like('us.org_shop_name', $search_param['keyword']);
			$this->db->group_end();
		}

		$query = $this->db->get();
		// print_r($this->db->last_query());
		$resultArr = $query->result_array();

		return $resultArr;
	}

	public function getB2BOrderDetailsByShopId($shop_id, $customerId)
	{
		$seller_db = $this->seller_db->database;
		$this->db->select('main.*, us.*, cm.country_name as bill_country, bcd.* ');
		$this->db->from('fbc_users_shop as us');
		$this->db->join('fbc_users as main', 'main.fbc_user_id = us.fbc_user_id');
		$this->db->join($seller_db . '.country_master as cm', 'cm.country_code = us.country_code');
		$this->db->join($seller_db . '.b2b_customers as bc', 'bc.shop_id =us.shop_id');
		$this->db->join($seller_db . '.b2b_customers_details as bcd', 'bc.id =bcd.customer_id');

		$this->db->where('us.shop_id =' . $shop_id);

		$this->db->where('bcd.customer_id =' . $customerId);
		$query = $this->db->get();
		$row = $query->row();
		// echo $this->db->last_query();
		return $row;
	}


	//B2B Customer  added by al

	function get_datatables_b2b_customers()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';
		$this->_get_datatables_query_b2b_customers($term);
		if ($_REQUEST['length'] != -1)
			$this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->seller_db->get();
		//echo $this->seller_db->last_query();exit;
		return $query->result();
	}

	public function _get_datatables_query_b2b_customers($term = '')
	{

		$main_db_name = $this->db->database;


		$column = array('fus.org_shop_name', 'customer_name	', '', '', '', '', '');
		$this->seller_db->distinct();
		$this->seller_db->select('o.*,fu.owner_name as customer_name, fus.org_shop_name,fu.email,fus.ship_state,cm.country_name,fus.ship_country');
		$this->seller_db->from('b2b_customers as o');
		$this->seller_db->join($main_db_name . '.fbc_users_shop as fus', 'o.shop_id = fus.shop_id', 'LEFT');
		$this->seller_db->join($main_db_name . '.fbc_users as fu', 'o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id', 'LEFT');
		$this->seller_db->join($main_db_name . '.country_master as cm', 'fus.ship_country = cm.country_code', 'LEFT');

		if ($term != '') {

			$this->seller_db->where(" (
			fu.email LIKE '%$term%'
			OR fu.owner_name LIKE '%$term%'
			OR fus.org_shop_name LIKE '%$term%'
			OR fus.ship_state LIKE '%$term%'
			OR cm.country_name LIKE '%$term%'
			 )");
		}


		if (!empty($fromDate) && empty($toDate)) {

			$this->seller_db->where('o.updated_at >=', strtotime($fromDate));
		} else if (!empty($toDate) && empty($fromDate)) {

			$this->seller_db->where('o.updated_at <=', strtotime($toDate));
		} else if (!empty($toDate) && !empty($fromDate)) {
			$this->seller_db->where('o.updated_at >=', strtotime($fromDate));
			$this->seller_db->where('o.updated_at <=', strtotime($toDate));
		}

		if (isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->seller_db->order_by(key($order), $order[key($order)]);
		} else {
			$this->seller_db->order_by('o.id', 'desc');
		}
	}


	public function count_all_b2b_customers()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';


		$main_db_name = $this->db->database;

		$column = array('fus.org_shop_name', 'customer_name', '', '', '', '', '');
		$this->seller_db->distinct();
		$this->seller_db->select('o.*,fu.owner_name as customer_name, fus.org_shop_name,fu.email,fus.ship_state,cm.country_name,fus.ship_country');
		$this->seller_db->from('b2b_customers as o');

		$this->seller_db->join($main_db_name . '.fbc_users_shop as fus', 'o.shop_id = fus.shop_id', 'LEFT');
		$this->seller_db->join($main_db_name . '.fbc_users as fu', 'o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id', 'LEFT');
		$this->seller_db->join($main_db_name . '.country_master as cm', 'fus.ship_country = cm.country_code', 'LEFT');

		if ($term != '') {

			$this->seller_db->where(" (
			fu.email LIKE '%$term%'
			OR fu.owner_name LIKE '%$term%'
			OR fus.org_shop_name LIKE '%$term%'
			OR fus.ship_state LIKE '%$term%'
			OR cm.country_name LIKE '%$term%'
			 )");
		}

		if (!empty($fromDate) && empty($toDate)) {

			$this->seller_db->where('o.updated_at >=', strtotime($fromDate));
		} else if (!empty($toDate) && empty($fromDate)) {

			$this->seller_db->where('o.updated_at <=', strtotime($toDate));
		} else if (!empty($toDate) && !empty($fromDate)) {
			$this->seller_db->where('o.updated_at >=', strtotime($fromDate));
			$this->seller_db->where('o.updated_at <=', strtotime($toDate));
		}

		if (isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->seller_db->order_by(key($order), $order[key($order)]);
		} else {
			$this->seller_db->order_by('o.id', 'desc');
		}
		return $this->seller_db->count_all_results();
	}

	function count_filtered_b2b_customers()
	{
		$term = $_REQUEST['search']['value'];
		$this->_get_datatables_query_b2b_customers($term);
		$query = $this->seller_db->get();
		return $query->num_rows();
	}

	function getlastpurchasedate($shop_id)
	{
		$this->seller_db->select('created_at');
		$this->seller_db->from('b2b_orders');
		$this->seller_db->where(array('shop_id' => $shop_id));
		$this->seller_db->order_by('order_id', 'desc');
		$query = $this->seller_db->get();
		$row = $query->row();
		return $row;
	}

	function gettotalpurchasebyshop($shop_id)
	{
		$this->seller_db->select('sum(grand_total) as total');
		$this->seller_db->from('b2b_orders');
		$this->seller_db->where(array('shop_id' => $shop_id));
		$this->seller_db->where('status NOT IN (3,7)');
		$this->seller_db->where('parent_id', 0);
		$this->seller_db->where('main_parent_id', 0);
		$query = $this->seller_db->get();
		$row = $query->row();
		return $row;
	}

	function getlastpurchasedatebyshop($shop_id)
	{
		$this->seller_db->select('created_at');
		$this->seller_db->from('b2b_orders');
		$this->seller_db->where(array('shop_id' => $shop_id));
		$this->seller_db->where('status NOT IN (3,7)');
		$this->seller_db->where('parent_id', 0);
		$this->seller_db->where('main_parent_id', 0);
		$this->seller_db->order_by('order_id', 'desc');
		$this->seller_db->limit(1);
		$query = $this->seller_db->get();
		$row = $query->row();
		// print_r($this->seller_db->last_query());exit;
		return $row;
	}

	function getordersbyshop($shop_id)
	{
		$this->seller_db->select('*');
		$this->seller_db->from('b2b_orders');
		$this->seller_db->where(array('shop_id' => $shop_id));
		$this->seller_db->order_by('order_id', 'desc');
		$query = $this->seller_db->get();
		$Result = $query->result();
		return $Result;
	}

	// customer invoice by shop id
	function getinvoicesbyshop($customerId)
	{
		$this->seller_db->select('*');
		$this->seller_db->from('b2b_customers_invoice');
		$this->seller_db->where(array('customer_id' => $customerId));
		$query = $this->seller_db->get();
		$Result = $query->row();
		// $Result = $query->result();
		return $Result;
	}

	// customer id by shop id
	function getCustomerIdbyshop($shop_id)
	{
		$this->seller_db->select('id');
		$this->seller_db->from('b2b_customers');
		$this->seller_db->where(array('shop_id' => $shop_id));
		$query = $this->seller_db->get();
		$Result = $query->row();
		return $Result->id;
	}

	function getCustomerInvoiceB2BByShopId($customerId)
	{
		$this->seller_db->select('id');
		$this->seller_db->from('b2b_customers_invoice');
		$this->seller_db->where(array('customer_id' => $customerId));
		$query = $this->seller_db->get();
		$Result = $query->row();
		return $Result;
	}

	public function getB2BCatSubCatDetailsRewised($shop_id)
	{

		$main_db_name = $this->db->database;

		$this->seller_db->distinct();
		$this->seller_db->select('b2bCat.*, IF(cg.parent_id <= 0, cg.cat_name, cg2.cat_name) AS cat_name, cg.parent_id,IF(cg.parent_id >0, cg1.cat_name,"-") as  sub_cat_name'); //, cg.cat_name as sub_cat_name,
		$this->seller_db->from('fbc_users_category_b2b as b2bCat');
		$this->seller_db->join($main_db_name . '.category as cg', 'cg.id = b2bCat.category_id', 'LEFT');
		$this->seller_db->join($main_db_name . '.category as cg1', 'cg1.id = cg.id', 'LEFT');
		$this->seller_db->join($main_db_name . '.category as cg2', 'cg2.id = cg.parent_id', 'LEFT');
		$this->seller_db->join($main_db_name . '.fbc_users_shop as FUS', 'FUS.fbc_user_id = b2bCat.fbc_user_id', 'INNER');
		$this->seller_db->where(array('FUS.shop_id' => $shop_id));
		$this->seller_db->where('(b2bCat.level IN (0,1))');
		$this->seller_db->where('cg.cat_name <>', NULL);

		$query = $this->seller_db->get();
		//print_r($this->seller_db->last_query());exit;
		return $query->result();
	}

	public function getDatatable_special_prices_b2b($B2Bshop_id)
	{

		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';
		$this->get_datatables_All_special_prices_b2b($term, $B2Bshop_id);
		if ($_REQUEST['length'] != -1)
			$this->seller_db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->seller_db->get();
		return $query->result();
	}

	public function get_datatables_All_special_prices_b2b($term = '', $B2Bshop_id = 0)
	{

		$column = array('', 'p.sku', 'p.name', 'p.webshop_price', '', 'p.price', 'psp.special_price', 'psp.special_price_from', 'psp.special_price_to', '', '');
		$this->seller_db->select('psp.*,p.sku,p.name,p.price');
		$this->seller_db->from('products_special_prices_b2b as psp');
		$this->seller_db->where(array('psp.shop_id' => $B2Bshop_id));
		$this->seller_db->join('products as p', 'p.id=psp.product_id');
		$this->seller_db->where(array('p.remove_flag' => 0));


		if ($term != '') {
			$this->seller_db->where(" (
				  p.sku LIKE '%$term%'
				  OR p.name LIKE '%$term%'
				  OR p.price LIKE '%$term%'

				   )");
		}

		if (isset($_REQUEST['order'])) // here order processing
		{
			$this->seller_db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		} else {
			$this->seller_db->order_by('psp.special_price_from', 'DESC');
		}
	}

	public function countfilterspecialprice_b2b($B2Bshop_id)
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';
		$this->get_datatables_All_special_prices_b2b($term, $B2Bshop_id);
		$query = $this->seller_db->get();
		return $query->num_rows();
	}

	public function countspecialpricerecord_b2b($B2Bshop_id)
	{
		$this->seller_db->select('*');
		$this->seller_db->from('products_special_prices_b2b as psp');
		$this->seller_db->where(array('psp.shop_id' => $B2Bshop_id));
		$this->seller_db->join('products as p', 'p.id=psp.product_id');
		$this->seller_db->where(array('p.remove_flag' => 0));
		$query = $this->seller_db->count_all_results();
		return $query;
	}

	public function getVariantsByID($product_id)
	{

		$main_db_name = $this->db->database;

		$this->seller_db->select('ev.attr_name, evp.attr_options_name, product_id');
		$this->seller_db->from('products_variants pv');
		$this->seller_db->join($main_db_name . '.eav_attributes ev', 'ev.id=pv.attr_id', 'INNER');
		$this->seller_db->join($main_db_name . '.eav_attributes_options evp', 'evp.id=pv.attr_value AND ev.id = evp.attr_id', 'INNER');
		$this->seller_db->where_in('pv.product_id', $product_id);

		$query = $this->seller_db->get();
		$result = $query->result_array();
		return $result;
	}
}
