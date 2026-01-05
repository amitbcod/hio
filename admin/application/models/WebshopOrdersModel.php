<?php
class WebshopOrdersModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function updateData($tableName, $condition, $updateData)
	{
		$this->db->where($condition);

		$this->db->update($tableName, $updateData);
		if ($this->db->affected_rows() > 0) {

			return true;
		} else {
			return false;
		}

		$this->db->reset_query();
	}



	///getDataByID
	public function getMultiDataById($tableName, $condition, $select, $order_by_column = '', $order_by_type = '')
	{
		if (!empty($select)) {
			$this->db->select($select);
		}
		$this->db->where($condition);

		if (isset($order_by_column) &&  $order_by_column != '') {
			$this->db->order_by($order_by_column, $order_by_type);
		}

		$query = $this->db->get($tableName);
		return $query->result();
	}

	//getSingleDataByID
	public function getSingleDataByID($tableName, $condition, $select)
	{
		if (!empty($select)) {
			$this->db->select($select);
		}
		$this->db->where($condition);
		$query = $this->db->get($tableName);
		return $query->row();
	}



	//insertData
	public function insertData($table, $data)
	{
		$this->db->reset_query();

		$this->db->insert($table, $data);
		if ($this->db->affected_rows() > 0) {
			$last_insert_id = $this->db->insert_id();
			return $last_insert_id;
		} else {
			return false;
		}
	}

	function get_datatables_orders($price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method)
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';
		$this->_get_datatables_query_orders($term, $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method);
		if ($_REQUEST['length'] != -1)
			$this->db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	/*public function _get_datatables_query_orders($term='',$price,$order_status,$shipment_type,$fromDate,$toDate) {

		$main_db_name=$this->db->database;
		$current_tab=$_REQUEST['current_tab'];

		$extra_select='';

		if($current_tab=='split-orders'){
			$extra_select=' ,bos.id as order_shipment_id';
		}

		$column = array('o.increment_id','o.created_at', 'customer_name','', '','','','');
		$this->db->distinct();
		$this->db->select('o.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as customer_name, wp.payment_method_name,wp.payment_type '.$extra_select);
		$this->db->from('sales_order as o');
		//$this->db->join($main_db_name.'.fbc_users_shop as fus','o.shop_id = fus.shop_id','LEFT');
		//$this->db->join($main_db_name.'.fbc_users as fu','o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id','LEFT');
		if($current_tab=='split-orders'){
			$this->db->join('sales_order_shipment as bos','o.order_id = bos.order_id','LEFT');
		}
		$this->db->join('sales_order_payment as wp','o.order_id = wp.order_id','LEFT');


		if($current_tab=='orders'){
			$this->db->where('o.parent_id','0');
			$this->db->where('o.is_split','0');
			$this->db->where('(o.status NOT IN (3,4,5,6))');
			// $this->db->where('(o.status NOT IN (4,5,6))');
		}
		else if($current_tab=='split-orders')
		{
			$this->db->where('o.parent_id >','0');
			//$this->db->where('o.is_split <>','1');
			$this->db->where('bos.id IS NULL');


		}
		else if($current_tab=='shipped-orders')
		{
			$this->db->where('o.parent_id','0');
			$this->db->where('o.main_parent_id','0');
			//$this->db->where('o.is_split','0');
			$this->db->where('(o.status IN (4,5,6))');
		}
		else if($current_tab=='cancel-orders')
		{
			$this->db->where('o.parent_id','0');
			$this->db->where('o.is_split','0');
			$this->db->where('(o.status IN (3))');
		}

		if($term !=''){

		  $this->db->where(" (
			o.increment_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'
			 )");

		}

		 if(!empty($price))
		{

			$this->db->where('o.grand_total >=',0);
			$this->db->where('o.grand_total <=', $price);
		}


		if(!empty($order_status))
		{
			$this->db->where("o.status",$order_status);
		}

		if(!empty($shipment_type))
		{
			$this->db->where("o.shipment_type IN ($shipment_type)");
		}


		if(!empty($fromDate) && empty($toDate)){

			$this->db->where('o.updated_at >=',strtotime($fromDate));
		}
		else if(!empty($toDate) && empty($fromDate)){

			$this->db->where('o.updated_at <=',strtotime($toDate));
		}
		else if(!empty($toDate) && !empty($fromDate))
		{
			$this->db->where('o.updated_at >=',strtotime($fromDate));
			$this->db->where('o.updated_at <=',strtotime($toDate));
		}

		$this->db->where('(o.status NOT IN (7))');

		if(isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		}
		else if(isset($this->order))
		{
			 $order = $this->order;
			 $this->db->order_by(key($order), $order[key($order)]);
		}else{
			$this->db->order_by('o.order_id', 'desc');
		}

    }*/ //old

	public function _get_datatables_query_orders($term, $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method)
	{
		// echo $order_status.' _get_datatables_query_orders';
		$main_db_name = $this->db->database;
		$current_tab = $_REQUEST['current_tab'];

		$extra_select = '';

		if ($current_tab == 'split-orders') {
			$extra_select = ' ,bos.id as order_shipment_id';
		}

		$column = array('o.increment_id', 'o.created_at', 'customer_name', '', '', '', '', '');
		$this->db->distinct();
		$this->db->select('o.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as customer_name, wp.payment_method_name,wp.payment_type ' . $extra_select);
		$this->db->from('sales_order as o');
		//$this->db->join($main_db_name.'.fbc_users_shop as fus','o.shop_id = fus.shop_id','LEFT');
		//$this->db->join($main_db_name.'.fbc_users as fu','o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id','LEFT');
		if ($current_tab == 'split-orders') {
			$this->db->join('sales_order_shipment as bos', 'o.order_id = bos.order_id', 'LEFT');
		}
		$this->db->join('sales_order_payment as wp', 'o.order_id = wp.order_id', 'INNER');
		$this->db->join('sales_order_address as soa', 'o.order_id = soa.order_id', 'INNER');

		if ($current_tab == 'orders') {
			$this->db->where('o.parent_id', '0');
			$this->db->where('o.is_split', '0');
			$this->db->where('(o.status NOT IN (3,4,5,6))');
		} else if ($current_tab == 'split-orders') {
			$this->db->where('o.parent_id >', '0');
			//$this->db->where('o.is_split <>','1');
			$this->db->where('bos.id IS NULL');
			$this->db->where('(o.status NOT IN (3,4,5,6))');  //new
		} else if ($current_tab == 'shipped-orders') {
			$this->db->where('o.parent_id', '0');
			$this->db->where('o.main_parent_id', '0');
			//$this->db->where('o.is_split','0');
			$this->db->where('(o.status IN (4,5,6))');
		} else if ($current_tab == 'cancel-orders') {
			// $this->db->where('o.parent_id','0'); //new
			$this->db->where('o.is_split', '0');
			$this->db->where('(o.status IN (3))');
		}

		if ($term != '') {
			$this->db->where(" (
			o.increment_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'
			OR o.customer_firstname LIKE '%$term%'
			OR o.customer_lastname LIKE '%$term%'
			OR o.company_name LIKE '%$term%'
			OR o.coupon_code LIKE '%$term%'
			OR o.voucher_code LIKE '%$term%'
			OR o.customer_email LIKE '%$term%'
			OR soa.mobile_no LIKE '%$term%'
			OR soa.city LIKE '%$term%'
			 )");
		}

		if (!empty($price)) {

			$this->db->where('o.grand_total >=', 0);
			$this->db->where('o.grand_total <=', $price);
		}

		// if ($order_status = 'Tracking Complete') {
		// 	$order_status =6;
		// }elseif ($order_status = 'Tracking Incomplete') {
		// 	$order_status =5;
		// }elseif ($order_status = 'Tracking Missing') {
		// 	$order_status =4;
		// }elseif ($order_status = 'Cancelled') {
		// 	$order_status =3;
		// }elseif ($order_status = 'Complete') {
		// 	$order_status =2;
		// }elseif ($order_status = 'Processing') {
		// 	$order_status =1;
		// }
		// if($current_tab!='shipped-orders')
		// {
		if (isset($order_status) && $order_status != '') {
			$this->db->where("o.status", $order_status);
		}
		// }

		if (!empty($shipment_type)) {
			$this->db->where("o.shipment_type IN ($shipment_type)");
		}


		if (!empty($fromDate) && empty($toDate)) {

			$this->db->where('o.updated_at >=', strtotime($fromDate));
		} else if (!empty($toDate) && empty($fromDate)) {

			$this->db->where('o.updated_at <=', strtotime($toDate));
		} else if (!empty($toDate) && !empty($fromDate)) {
			$this->db->where('o.updated_at >=', strtotime($fromDate));
			$this->db->where('o.updated_at <=', strtotime($toDate));
		}

		if (isset($payment_method) && $payment_method != '') {
			$this->db->where("wp.payment_method_id", $payment_method);
		}

		$this->db->where('(o.status NOT IN (7))');

		if (isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		} else {
			$this->db->order_by('o.order_id', 'desc');
		}
	}


	public function count_all_orders($price, $order_status, $shipment_type, $fromDate, $toDate)
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';

		$current_tab = $_REQUEST['current_tab'];
		$main_db_name = $this->db->database;
		$extra_select = '';

		if ($current_tab == 'split-orders') {
			$extra_select = ' ,bos.id as order_shipment_id';
		}
		$column = array('o.increment_id', 'o.created_at', 'customer_name', '', '', '', '', '');
		$this->db->distinct();
		$this->db->select('o.*,CONCAT(o.customer_firstname, " ", o.customer_lastname) as  customer_name,wp.payment_method_name,wp.payment_type ' . $extra_select);
		$this->db->from('sales_order as o');

		//$this->db->join($main_db_name.'.fbc_users_shop as fus','o.shop_id = fus.shop_id','LEFT');
		//$this->db->join($main_db_name.'.fbc_users as fu','o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id','LEFT');

		if ($current_tab == 'split-orders') {
			$this->db->join('sales_order_shipment as bos', 'o.order_id = bos.order_id', 'LEFT');
		}

		$this->db->join('sales_order_payment as wp', 'o.order_id = wp.order_id', 'LEFT');

		if ($current_tab == 'orders') {
			$this->db->where('o.parent_id', '0');
			$this->db->where('o.is_split', '0');
			$this->db->where('(o.status NOT IN (4,5,6))');
		} else if ($current_tab == 'split-orders') {
			$this->db->where('o.parent_id >', '0');
			//$this->db->where('o.is_split <>','1');
			$this->db->where('bos.id IS NULL');
		} else if ($current_tab == 'shipped-orders') {
			$this->db->where('o.parent_id', '0');
			$this->db->where('o.is_split', '0');
			$this->db->where('(o.status IN (4,5,6))');
		}


		if ($term != '') {

			$this->db->where(" (
			o.increment_id LIKE '%$term%'
			OR o.grand_total LIKE '%$term%'

			 )");
		}

		if (!empty($price)) {

			$this->db->where('o.grand_total >=', 0);
			$this->db->where('o.grand_total <=', $price);
		}


		if (!empty($order_status)) {
			$this->db->where("o.status", $order_status);
		}

		if (!empty($shipment_type)) {
			$this->db->where("o.shipment_type IN ($shipment_type)");
		}

		if (!empty($fromDate) && empty($toDate)) {

			$this->db->where('o.updated_at >=', strtotime($fromDate));
		} else if (!empty($toDate) && empty($fromDate)) {

			$this->db->where('o.updated_at <=', strtotime($toDate));
		} else if (!empty($toDate) && !empty($fromDate)) {
			$this->db->where('o.updated_at >=', strtotime($fromDate));
			$this->db->where('o.updated_at <=', strtotime($toDate));
		}

		$this->db->where('(o.status NOT IN (7))');

		if (isset($_REQUEST['order'])) // here order processing
		{
			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		} else {
			$this->db->order_by('o.order_id', 'desc');
		}
		return $this->db->count_all_results();
	}

	function count_filtered_orders($price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method)
	{
		// $term = $_REQUEST['search']['value'];
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';
		$this->_get_datatables_query_orders($term, $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method);
		$query = $this->db->get();
		return $query->num_rows();
	}


	function generate_new_transaction_id()
	{
		$payment_id = '';
		$user_transaction_id = $this->getLastUserTransactionId();
		if (isset($user_transaction_id) && $user_transaction_id->increment_id != '') {
			$last_inc_id		= $user_transaction_id->increment_id;
			$last_order_id		= str_replace('B2B-', '', $last_inc_id);
			$payment_id         = $last_order_id + 1;
		} else {
			$payment_id        = 1001;
		}

		$transaction_id = 'B2B-' . $payment_id;
		return $transaction_id;
	}

	function getLastUserTransactionId()
	{
		$this->db->select('order_id,increment_id');
		$this->db->where('parent_id', '0');
		$this->db->where('main_parent_id', '0');
		$this->db->order_by('order_id', 'desc');
		$this->db->limit(1);
		$query = $this->db->get('sales_order');
		return $query->row();
	}


	function getOrderCustomerNameByOrderId($order_id)
	{
		$full_name = '';
		$this->db->reset_query();

		$_order = $this->db->get_where('sales_order', array('order_id' => $order_id))->row();
		// $shop_id=$_order->shop_id;// old
		$shop_id = $this->session->userdata('ShopID');

		if ($_order->shipment_type == 1) {

			$result = $this->db->get_where('fbc_users', array('shop_id' => $shop_id, 'parent_id' => 0, 'created_by' => 0))->row();
			if (isset($result) && ($result->owner_name != '')) {
				$full_name = $result->owner_name;
			} else {
				$full_name = 'Unknown';
			}
		} else {
			$full_name = $_order->customer_firstname . ' ' . $_order->customer_lastname;
		}
		return $full_name;
	}

	function getOrderItems($order_id)
	{
		$this->db->select("oi.*,pi.qty,p.prod_location");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');
		$this->db->join('products as p', 'oi.product_id = p.id', 'LEFT');
		$this->db->where("oi.product_inv_type IN ('buy','virtual')");  //adde by al later
		//$this->db->where("pi.qty>0");								  //adde by al later
		$this->db->where('oi.order_id', $order_id);
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	function checkOrderItemsExist($order_id, $barcode)
	{
		$this->db->select("oi.*");
		$this->db->from('sales_order_items as oi');
		$this->db->where('oi.order_id', $order_id);
		$this->db->where('oi.barcode', $barcode);
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->row();
	}

	function incrementOrderItemQtyScanned($item_id)
	{
		$sql = "UPDATE sales_order_items   SET qty_scanned = qty_scanned + 1   WHERE item_id = $item_id";
		$this->db->query($sql);
		$this->db->reset_query();
	}

	function getOrderItemRowClass($item_id)
	{

		$item = $this->getSingleDataByID('sales_order_items', array('item_id' => $item_id), 'item_id,qty_scanned,qty_ordered');
		$item_class = '';
		if (isset($item) && $item->item_id != '') {

			if ($item->qty_scanned <= 0) {
				$item_class = 'black-row';
			} else if ($item->qty_scanned == $item->qty_ordered) {
				$item_class = 'green-row';
			} else if ($item->qty_scanned < $item->qty_ordered) {
				$item_class = 'orange-row';
			}
		}
		return $item_class;
	}

	function getQtyFullyScannedOrderItems($order_id)
	{
		$this->db->select("oi.*,pi.qty");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');
		$this->db->where("oi.product_inv_type IN ('buy','virtual')");  //adde by al later
		//$this->db->where("pi.qty>0");								  //adde by al later
		$this->db->where('oi.order_id', $order_id);
		$this->db->where('(oi.qty_ordered=oi.qty_scanned)');
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	function getQtyPartialOrFullScannedOrderItems($order_id)
	{
		$this->db->select("oi.*,pi.qty");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');
		$this->db->where("oi.product_inv_type IN ('buy','virtual')");  //adde by al later
		//$this->db->where("pi.qty>0");								  //adde by al later
		$this->db->where('oi.order_id', $order_id);
		$this->db->where('(oi.qty_scanned>0)');
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	function getPartialScannedSingleOrderItems($order_id, $product_id)
	{
		$this->db->select("oi.*");
		$this->db->from('sales_order_items as oi');
		$this->db->where('oi.order_id', $order_id);
		$this->db->where('oi.product_id', $product_id);
		$this->db->where('(oi.qty_scanned>0)');
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->row();
	}

	function getSingleOrderItemByProductId($order_id, $product_id)
	{
		$qty_ordered = '';
		$this->db->select("oi.*");
		$this->db->from('sales_order_items as oi');
		$this->db->where('oi.order_id', $order_id);
		$this->db->where('oi.product_id', $product_id);
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();

		$Row = $query->row();

		return $Row;
	}

	function getMainOrderItemQty($order_id, $product_id)
	{
		$qty_ordered = '';
		$this->db->select("oi.qty_ordered");
		$this->db->from('sales_order_items as oi');
		$this->db->where('oi.order_id', $order_id);
		$this->db->where('oi.product_id', $product_id);
		//$this->db->where('(oi.qty_scanned>0)');
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();

		$Row = $query->row();
		$qty_ordered = $Row->qty_ordered;
		//echo $this->db->last_query();exit;
		return $qty_ordered;
	}

	function getTotalScannedItemQty($order_id, $product_id, $split_order = '')
	{
		$qty_scanned = '0';
		$child_ids = array();
		$child_ids_arr = $this->getSplitChildOrderIds($order_id);
		if (isset($child_ids_arr) && count($child_ids_arr) > 0) {
			foreach ($child_ids_arr as $val) {
				$child_ids[] = $val->order_id;
			}
		} else {
			$child_ids = array();
		}

		$this->db->select("sum(oi.qty_scanned) as qty_scanned");
		$this->db->from('sales_order_items as oi');
		//$this->db->join('sales_order as o','oi.order_id = o.order_id','LEFT');
		if (isset($split_order) && $split_order == 1) {

			if (isset($child_ids) && count($child_ids) > 0) {

				$this->db->where_in('oi.order_id', $child_ids);
			} else {
				$this->db->where('oi.order_id', 0);
			}
		} else {
			$this->db->where('oi.order_id', $order_id);
		}
		$this->db->where('oi.product_id', $product_id);
		$this->db->where('(oi.qty_scanned>0)');
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();

		$Row = $query->row();
		$qty_scanned = $Row->qty_scanned;
		//echo $this->db->last_query();exit;
		return $qty_scanned;
	}

	function getQtyPendingScannedOrderItems($order_id)
	{
		$this->db->select("oi.*,pi.qty");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');
		$this->db->where("oi.product_inv_type IN ('buy','virtual')");  //adde by al later
		//$this->db->where("pi.qty>0");								  //adde by al later
		$this->db->where('oi.order_id', $order_id);
		$this->db->where('(oi.qty_scanned<oi.qty_ordered  OR oi.qty_scanned=0)');
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}



	function removeOldOrderItems($order_id)
	{
		$this->db->where('order_id', $order_id);
		$this->db->delete('sales_order_items');
		$this->db->reset_query();
	}

	public function getEmailTemplateById($TemplateId)
	{
		$result = $this->db->get_where('email_template', array('id' => $TemplateId))->row();
		return $result;
	}

	public function getEmailTemplateByIdentifier($identifier, $lang_code = '')
	{
		if ($lang_code != '') {
			$this->db->select('email_template.*,multi_lang_email_template.subject as other_lang_subject,multi_lang_email_template.content as other_lang_content');
			$this->db->from('email_template');
			$this->db->join("multi_lang_email_template", "email_template.id = multi_lang_email_template.email_temp_id and  multi_lang_email_template.lang_code ='$lang_code'", "LEFT");
			$this->db->where('email_template.email_code', $identifier);
			$query = $this->db->get();
			$result = $query->row();
		} else {
			$result = $this->db->get_where('email_template', array('email_code' => $identifier))->row();
		}
		return $result;
	}

	public function getCustomVariableByIdentifier($identifier)
	{
		$result = $this->db->get_where('custom_variables', array('identifier' => $identifier))->row();
		return $result;
	}


	public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $SubDynamic = '', $CommonVars = '', $lang_code = '')
	{

		$webshop_smtp_host = 'smtp.gmail.com'; // $this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port =  465; // $this->getCustomVariableByIdentifier('smtp_port') ??
		$webshop_smtp_username = 'care@indiamags.com'; //$this->getCustomVariableByIdentifier('smtp_username') ?? 
		$webshop_smtp_password = 'ZwupmjHd@!1852'; //$this->getCustomVariableByIdentifier('smtp_password') ?? 
		$webshop_smtp_secure = $this->getCustomVariableByIdentifier('smtp_secure');

		$GlobalVar = $this->getCustomVariableByIdentifier('admin_email');
		if (isset($GlobalVar) && $GlobalVar->value != '') {
			$from_email = $GlobalVar->value;
		} else {
			$shop_id		=	$this->session->userdata('ShopID');
			$FBCData = $this->CommonModel->getShopOwnerData($shop_id);
			$from_email = $FBCData->email;
		}
		if ($lang_code != '') {
			$emailTemplate = $this->getEmailTemplateByIdentifier($identifier, $lang_code);
		} else {
			$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);
		}

		if (isset($emailTemplate) && $emailTemplate->id != '') {

			if ($lang_code != '') {
				$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header', $lang_code);
				$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer', $lang_code);
			} else {
				$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header');
				$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer');
			}

			if (isset($emailTemplate->other_lang_content) && $emailTemplate->other_lang_content != '') {
				$emailContent = $emailTemplate->other_lang_content;
			} else {
				$emailContent = $emailTemplate->content;
			}
			if (isset($emailTemplate->other_lang_subject) && $emailTemplate->other_lang_subject != '') {
				$subject = $emailTemplate->other_lang_subject;
			} else {
				$subject = $emailTemplate->subject;
			}

			$HeaderPart = $emailHeaderTemplate->content;
			$FooterPart = $emailFooterTemplate->content;
			if (isset($CommonVars) && $CommonVars != '') {
				$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
				$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);
			}

			$templateId = $emailTemplate->id;

			/*if(isset($SubDynamic) && $SubDynamic!=''){
				$subject = $SubDynamic;
			}
			else
			{
				$subject = $emailTemplate->subject;
			}*/

			//$subject = $emailTemplate->subject;


			$title = $emailTemplate->title;

			if ($templateId == 4 || $templateId == 6 || $templateId == 18 || $templateId == 19 || $templateId == 20) {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
				} else {
					$subject = str_replace('##ORDERID##', '', $subject);
				}
			} else if ($templateId == 15) {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = str_replace('##RETURNORDERID##', $SubDynamic, $subject);
				} else {
					$subject = str_replace('##RETURNORDERID##', '', $subject);
				}
			} else if ($templateId == 13 || $templateId == 12) {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = $SubDynamic;
				} else {
					$subject = $subject;
				}
			} else if ($identifier == 'fbcuser-order-cancelled-by-fbcuser') {
				if (isset($DynamicVars) && $DynamicVars != '') {
					$subject = str_replace('##ORDERID##', $DynamicVars[1], $subject);
				}
			} else if ($identifier == 'request_a_payment_for_order') {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
				} else {
					$subject = str_replace('##ORDERID##', '', $subject);
				}
			}

			if ($identifier == 'initiate_refund') {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
				} else {
					$subject = str_replace('##ORDERID##', '', $subject);
				}
			}



			$emailBody = str_replace($TempVars, $DynamicVars, $emailContent);
			if ($identifier == 'initiate_refund') {
				$emailBody = str_replace('##CUSTOMERNAME##', $DynamicVars[0], $emailTemplate->content);
				$emailBody = str_replace('##AMOUNTPAYABLE##', $DynamicVars[1], $emailBody);
				$emailBody = str_replace('##ORDERDATE##', date(SIS_DATE_FM_WT, $DynamicVars[2]), $emailBody);
				$emailBody = str_replace('##CANCELREASON##', $DynamicVars[3], $emailBody);
				$emailBody = str_replace('##CUSTOMERACCOUNTNUMBER##', $DynamicVars[4], $emailBody);
				$emailBody = str_replace('##ACCOUNTTYPE##', $DynamicVars[5], $emailBody);
				$emailBody = str_replace('##IFSCCODE##', $DynamicVars[6], $emailBody);
				$emailBody = str_replace('##BANKNAME##', $DynamicVars[7], $emailBody);
				$emailBody = str_replace('##BANKADDRESS##', $DynamicVars[8], $emailBody);
				$emailBody = str_replace('##ORDERID##', $SubDynamic, $emailBody);
			}

			if ($identifier == 'abundant-carts') {
				if (isset($identifier) && $identifier != '') {
					$subject = str_replace('##CUSTOMERQUOTEID##', $SubDynamic[0], $subject);
				}
				// $emailBody = str_replace('##CUSTOMERQUOTEID##', $DynamicVars[2], $emailTemplate->content);
				$emailBody = str_replace('##CUSTOMERQUOTEID##', $DynamicVars[0], $emailBody);
				$emailBody = str_replace('##CUSTOMERMOBILENO##', $DynamicVars[1], $emailBody);
				// $from_email = 'ameyas@bcod.co.in';
				$cc = 'bhagyashree@bcod.co.in';
			}


			$FinalContentBody = utf8_decode($HeaderPart . $emailBody . $FooterPart);
			// die;
			if ($this->CommonModel->sendHTMLMailSMTP($EmailTo, $subject, $FinalContentBody, $from_email, $attachment = "", $webshop_smtp_host, $webshop_smtp_port, $webshop_smtp_username, $webshop_smtp_password, $webshop_smtp_secure, $cc)) {
				return true;
			} else {

				return false;
			}
		} else {
		}
	}

	function getSplitChildOrderIds($main_parent_id)
	{
		$this->db->select("order_id,increment_id,status");
		$this->db->from('sales_order');
		$this->db->where('main_parent_id', $main_parent_id);
		$this->db->order_by('order_id', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	function getPendingQtyToBeScanned($order_id, $product_id, $split_order = '')
	{

		$PendingScanQty = 0;

		$ItemData = $this->getSingleOrderItemByProductId($order_id, $product_id);
		$total_scan_for_item = $this->getTotalScannedItemQty($order_id, $product_id, $split_order);

		if (isset($total_scan_for_item) && $total_scan_for_item > 0) {
			$PendingScanQty = $ItemData->qty_ordered - $total_scan_for_item;
		} else {
			$PendingScanQty = $ItemData->qty_ordered;
		}
		return $PendingScanQty;
	}

	function getShippedOrderItems($order_id, $is_split = '')
	{
		$this->db->select("oi.*,o.increment_id");
		$this->db->from('sales_order_items as oi');
		$this->db->join('sales_order as o', 'oi.order_id = o.order_id', 'LEFT');
		$this->db->join('sales_order_shipment as oshp', 'oi.order_id = oshp.order_id', 'LEFT');
		if (isset($is_split) && $is_split == 1) {
			$this->db->where('o.main_parent_id', $order_id);
			$this->db->group_by('product_id');
		} else {
			$this->db->where('o.order_id', $order_id);
		}
		$this->db->where('(oi.qty_scanned>0)');
		$this->db->where('(oshp.order_id IS NOT NULL)');
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	function CheckShipmentExist($order_id)
	{
		$this->db->select("*");
		$this->db->from('sales_order_shipment');
		$this->db->where('order_id', $order_id);
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->row();
	}

	function getShippedSingleOrderItems($order_id, $product_id, $is_split = '')
	{
		$this->db->select("sum(qty_scanned) as qty_scanned");
		$this->db->from('sales_order_items as oi');
		$this->db->join('sales_order as o', 'oi.order_id = o.order_id', 'LEFT');
		$this->db->join('sales_order_shipment as oshp', 'oi.order_id = oshp.order_id', 'LEFT');
		if (isset($is_split) && $is_split == 1) {
			$this->db->where('o.main_parent_id', $order_id);
		} else {
			$this->db->where('o.order_id', $order_id);
		}
		$this->db->where('oi.product_id', $product_id);
		$this->db->where('(oi.qty_scanned>0)');
		$this->db->where('(oshp.order_id IS NOT NULL)');
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->row();
	}


	function decrementProductStockQty($product_id, $qty)
	{
		$this->db->reset_query();
		$sql = "	UPDATE products_inventory SET qty = CASE   WHEN qty >= $qty THEN  qty - $qty ELSE 0 END WHERE product_id = $product_id ";
		$this->db->query($sql);
		return true;
	}

	function decrementOrderItemStock($order_id)
	{
		$OrderItems = $this->getOrderItems($order_id);
		if (isset($OrderItems) && count($OrderItems) > 0) {
			foreach ($OrderItems as $value) {
				$product_id = $value->product_id;
				$qty_scanned = $value->qty_scanned;
				$this->decrementProductStockQty($product_id, $qty_scanned);
			}
		}
	}


	function getFormattedAddress($Row)
	{
		$shipping_address = '';


		if ($Row->address_line1 != '') {

			if (isset($Row->country) &&  $Row->country != '') {
				$CountryRow = $this->db->get_where('country_master', array('country_code' => $Row->country))->row();
				$Country = $CountryRow->country_name;
			} else {
				$Country = '';
			}

			$shipping_address .= $Row->address_line1 . ', ' . $Row->address_line2 . '<br>';
			$shipping_address .= $Row->city . ', ' . $Row->state . '<br>';
			$shipping_address .= $Country . ' ' . $Row->pincode;
		} else {
		}
		return $shipping_address;
	}

	function getWebshopB2BShops($order_id)
	{
		$sql = "SELECT oi.shop_id FROM `sales_order_items` as oi  LEFT JOIN products_inventory as pi ON oi.product_id = pi.product_id WHERE oi.product_inv_type IN ('dropship') AND  oi.order_id = $order_id  group by oi.shop_id ";
		$query = $this->db->query($sql);
		return $query->result();
	}


	function getOrderItemsWithOrderId($order_id, $is_split = '')
	{
		$this->db->select("oi.*,o.increment_id");
		$this->db->from('sales_order_items as oi');
		$this->db->join('sales_order as o', 'oi.order_id = o.order_id', 'LEFT');
		if (isset($is_split) && $is_split == 1) {
			$this->db->where('oi.main_parent_id', $order_id);
		} else {
			$this->db->where('oi.order_id', $order_id);
		}
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	function checkOrderItemsExistByItemId($order_id, $item_id)
	{
		$this->db->select("oi.*");
		$this->db->from('sales_order_items as oi');
		$this->db->where('oi.order_id', $order_id);
		$this->db->where('oi.item_id', $item_id);
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->row();
	}

	function incrementOrderItemQtyScannedByQty($item_id, $qty)
	{
		$sql = "UPDATE sales_order_items   SET qty_scanned = qty_scanned + $qty   WHERE item_id = $item_id";
		$this->db->query($sql);
		$this->db->reset_query();
	}

	// invoice
	function get_webshop_invoicing_data($order_id)
	{
		$main_db_name = $this->db->database;
		//$current_tab=$_REQUEST['current_tab'];

		$extra_select = '';

		//$column = array('o.increment_id','o.created_at', 'customer_name','', '','','','');
		$this->db->distinct();
		$this->db->select('o.*,c.invoice_type,c.payment_term,c.alternative_email_id,CONCAT(o.customer_firstname, " ", o.customer_lastname) as customer_name, wp.payment_method_name,wp.payment_type ' . $extra_select);
		$this->db->from('sales_order as o');
		$this->db->join('sales_order_payment as wp', 'o.order_id = wp.order_id', 'LEFT');
		$this->db->join('customers_invoice as c', 'o.customer_id = c.customer_id', 'LEFT');

		/*if($current_tab=='shipped-orders')
		{*/
		$this->db->where('o.order_id', $order_id);
		$this->db->where('o.parent_id', '0');
		$this->db->where('o.main_parent_id', '0');
		//$this->db->where('o.is_split','0');
		// $this->db->where('(o.status IN (4,5,6))');
		// }
		// $this->db->where($condition);
		// $query = $this->db->get($tableName);//
		$query = $this->db->get();
		return $query->row();
	}

	// invoice create
	public function get_invoicedata_by_id($invoice_id)
	{
		$this->db->select("*");
		$this->db->from('invoicing');
		$this->db->where('id', $invoice_id);
		$query = $this->db->get();
		return $query->row();
	}

	// order items data
	function getOrder_multi_Items($order_id)
	{
		/*$this->db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi','oi.product_id = pi.product_id','LEFT');
		$this->db->join('sales_orders as o','oi.order_id = o.order_id','LEFT');
		$this->db->where_in('oi.order_id',$order_id);
		$this->db->order_by('oi.qty_scanned','asc');*/

		//old $this->db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type,o.checkout_method, o.invoice_self, o.coupon_code as order_coupon_code,sop.payment_method,sop.payment_method_name,o.total_qty_ordered");
		$this->db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type,o.checkout_method, o.invoice_self, o.coupon_code as order_coupon_code,sop.payment_method,sop.payment_method_name,o.payment_charge,o.payment_tax_percent,o.payment_tax_amount,o.payment_final_charge,o.shipping_amount,o.shipping_tax_amount,o.shipping_charge,o.shipping_tax_percent,o.total_qty_ordered");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');
		$this->db->join('sales_order as o', 'oi.order_id = o.order_id', 'LEFT');
		$this->db->join('sales_order_payment as sop', 'oi.order_id = sop.order_id', 'LEFT');
		$this->db->where("oi.product_inv_type IN ('buy','virtual')");  //adde by al later
		//$this->db->where("pi.qty>0");								  //adde by al later
		// $this->db->where('oi.order_id',$order_id);
		$this->db->where_in('oi.order_id', $order_id);
		//		$this->db->order_by('oi.qty_scanned','asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	function getInvoiceItems($invoice_id)
	{
		$this->db->select('oi.*, p.sku');
		$this->db->from('invoicing_details as oi');
		$this->db->join('products as p', 'oi.product_id = p.id', 'LEFT');

		$this->db->where('oi.invoice_id', $invoice_id);
		$this->db->order_by('oi.id', 'asc');
		$query = $this->db->get();
		//		echo $this->db->last_query();exit;
		return $query->result();
	}

	function getInvoicePayments($order_id)
	{
		$this->db->from('sales_order_payment')
			->where('order_id', $order_id);
		$query = $this->db->get();
		//		echo $this->db->last_query();exit;
		return $query->result();
	}



	// send invoice email
	public function sendInvoiceHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $CommonVars, $attachment)
	{

		$webshop_smtp_host = $this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port = $this->getCustomVariableByIdentifier('smtp_port');
		$webshop_smtp_username = $this->getCustomVariableByIdentifier('smtp_username');
		$webshop_smtp_password = $this->getCustomVariableByIdentifier('smtp_password');
		$webshop_smtp_secure = $this->getCustomVariableByIdentifier('smtp_secure');

		$GlobalVar = $this->getCustomVariableByIdentifier('admin_email');
		if (isset($GlobalVar) && $GlobalVar->value != '') {
			$from_email = $GlobalVar->value;
		} else {
			$shop_id		=	$this->session->userdata('ShopID');
			$FBCData = $this->CommonModel->getShopOwnerData($shop_id);
			$from_email = $FBCData->email;
		}

		$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);
		if (isset($emailTemplate) && $emailTemplate->id != '') {

			$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header');
			$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer');

			$HeaderPart = $emailHeaderTemplate->content;
			$FooterPart = $emailFooterTemplate->content;
			if (isset($CommonVars) && $CommonVars != '') {
				$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);
				$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);
			}

			$templateId = $emailTemplate->id;

			$subject = $emailTemplate->subject;
			$title = $emailTemplate->title;

			if ($templateId == 21) {
				if (isset($CommonVars) && $CommonVars != '') {
					$subject = str_replace('##INVOICENO##', $CommonVars[2], $subject);
					$subject = str_replace('##WEBSHOPNAME##', $CommonVars[1], $subject);
				} else {
					$subject = str_replace('##INVOICENO##', '', $subject);
					$subject = str_replace('##WEBSHOPNAME##', '', $subject);
				}
			}

			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);

			/*
			$data['title'] = $title;
			$data['subject'] = $subject;
			$data['content'] = $emailBody;

			$content = $this->load->view('email_template/email_content', $data, TRUE);
			*/

			$FinalContentBody = $HeaderPart . $emailBody . $FooterPart;

			if ($this->CommonModel->sendHTMLMailSMTPAttchment($EmailTo, $subject, $FinalContentBody, $from_email, $attachment, $webshop_smtp_host->value, $webshop_smtp_port->value, $webshop_smtp_username->value, $webshop_smtp_password->value, $webshop_smtp_secure->value)) {
				$this->email->clear(TRUE);
				return true;
			} else {

				return false;
			}
		}
	}
	// new invoice
	// invoice voucher used amount sum
	public function get_invoice_sum_voucher_amount_by_id($order_id, $type)
	{
		$this->db->select('SUM(voucher_used_amount) as total_used_voucher_amount');
		$this->db->from('invoicing');
		$this->db->where('invoice_order_type', $type);
		$this->db->where_in('invoice_order_nos', $order_id);
		$this->db->group_by('invoice_order_nos');
		$query = $this->db->get();
		return $query->result();
	}

	// new invoice
	// invoice create new invoice
	public function get_invoice_count_by_id($order_id, $type)
	{
		$this->db->select("*");
		$this->db->from('invoicing');
		$this->db->where('invoice_order_type', $type);
		$this->db->where_in('invoice_order_nos', $order_id);
		$query = $this->db->get();
		return $query->result();
	}

	//cancel order
	function getCancelOrderItems($order_id, $is_split = '')
	{
		$this->db->select("oi.*,o.increment_id");
		$this->db->from('sales_order_items as oi');
		$this->db->join('sales_order as o', 'oi.order_id = o.order_id', 'LEFT');
		$this->db->join('sales_order_shipment as oshp', 'oi.order_id = oshp.order_id', 'LEFT');
		if (isset($is_split) && $is_split == 1) {
			$this->db->where('o.main_parent_id', $order_id);
		} else {
			$this->db->where('o.order_id', $order_id);
		}
		// $this->db->where('(oi.qty_scanned>0)');
		$this->db->where('(oshp.order_id IS NULL)');
		$this->db->where('o.status', 3);
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}
	//end cancel order item list

	function getSplitProductsEstimateTime($order_id, $product_id)
	{

		$this->db->select("estimate_delivery_time");
		$this->db->from('sales_order_items');
		$this->db->where('order_id', $order_id);
		$this->db->where('product_id', $product_id);

		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->row();
	}

	public function update_shipping_address($update_array, $address_id)
	{
		// $this->db->where('customer_id',$customer_id);
		$this->db->where('address_id', $address_id);
		$query = $this->db->update('sales_order_address', $update_array);
		// echo $this->db->last_query();
		if ($query) {

			return true;
		} else {
			return false;
		}
	}

	public function checkOrderItemscount($order_id)
	{
		$this->db->select("item_id");
		$this->db->from('sales_order_items');
		$this->db->where('order_id', $order_id);
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->num_rows();
	}

	function remove_sales_order_items($item_id)
	{
		$this->db->where('item_id', $item_id);
		$this->db->delete('sales_order_items');
		$this->db->reset_query();
	}

	public function getSpecialPrices($product_id, $customer_type_id)
	{
		$date = strtotime(date('d-m-Y'));

		$this->db->select("psp.*");
		$this->db->from('products_special_prices as psp');
		$this->db->where('psp.product_id', $product_id);
		$this->db->where('psp.customer_type_id', $customer_type_id);
		$this->db->where('psp.special_price_from <=', $date);
		$this->db->where('psp.special_price_to >=', $date);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function get_product_variant_details($parent_product_id, $product_id)
	{
		$this->db->select("*");
		$this->db->from('products_variants');
		$this->db->where('parent_id', $parent_product_id);
		$this->db->where('product_id', $product_id);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function getvariantsValuesByProduct($product_id)
	{

		$this->db->select("*");
		$this->db->from('products_variants');
		$this->db->where('product_id', $product_id);
		$query = $this->db->get();
		// echo $this->db->last_query();exit;
		return $query->result_array();
	}

	public function getAllvariantsValues($attr_id, $attr_value)
	{
		$this->db->select('eav_attributes.attr_name, eav_option.attr_options_name');
		$this->db->join('eav_attributes_options as eav_option', 'eav_option.attr_id = eav_attributes.id', 'LEFT');
		$this->db->where('eav_attributes.id', $attr_id);
		$this->db->where('eav_option.id', $attr_value);
		$query = $this->db->get('eav_attributes');
		return $query->row_array();
	}

	public function get_supplier_warehouse_time($shop_id)
	{
		$shop_code = '_shop' . $shop_id;
		$shop_db = $this->db->database . $shop_code;
		$custom_variables = "custom_variables";
		$sql = "SELECT * FROM $shop_db.$custom_variables where  `identifier` = 'delay_warehouse'";
		$query = $this->db->query($sql);
		return $query->row_array();
	}

	public function get_sales_order_shipment_details($order_id, $order_shipment_id)
	{
		$this->db->select('*');
		$this->db->from('sales_order_shipment_details');
		$this->db->where('order_shipment_id', $order_shipment_id);
		$query = $this->db->get();
		return $query->result_array();
	}

	public function get_sales_order_shipment($order_id, $shipment_id)
	{
		$this->db->select('*');
		$this->db->from('sales_order_shipment');
		$this->db->where('id', $shipment_id);
		$this->db->where('order_id', $order_id);
		$query = $this->db->get();
		return $query->row();
	}

	public function get_order_requst_payment($order_id)
	{
		$this->db->select('*');
		$this->db->from('sales_order_request_payment');
		$this->db->where('order_id', $order_id);
		$this->db->order_by('id', 'DESC');
		$query = $this->db->get();
		return $query->row();
	}

	function getOrderNumberById($order_id)
	{
		$query = $this->db->select('increment_id')
			->from('sales_order')
			->where('order_id', $order_id)
			->get();
		return $query->row()->increment_id ?? '';
	}

	function getOrderById($order_id)
	{
		$query = $this->db
			->from('sales_order')
			->where('order_id', $order_id)
			->get();
		return $query->row();
	}

	public function update_qty_original_from_qty_ordered($order_id)
	{
		$this->db
			->where('order_id', $order_id)
			->where('qty_original', null)
			->set('qty_original', 'qty_ordered', false)
			->update('sales_order_items');
	}

	public function update_qty_ordered_from_qty_scanned($order_id)
	{
		$this->db
			->where('order_id', $order_id)
			->set('qty_ordered', 'qty_scanned', false)
			->update('sales_order_items');
	}

	public function get_db_connection()
	{
		return $this->db;
	}

	public function getReturnDataById($order_id)
	{
		$this->db->select('return_order_increment_id,return_order_id');
		$this->db->from('sales_order_return');
		$this->db->where('order_id', $order_id);
		$this->db->where('status  NOT IN (0,1)');
		$query = $this->db->get();
		return $query->result();
	}
	function checkOrderItemsExistByOrderID($order_id)
	{
		$this->db->select("oi.*");
		$this->db->from('sales_order_items as oi');
		$this->db->where('oi.order_id', $order_id);
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	// shipment satatus
	function get_datatables_orders_shipment_status()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';
		$this->_get_datatables_query_orders_shipment_status($term);
		if ($_REQUEST['length'] != -1)
			$this->db->limit($_REQUEST['length'], $_REQUEST['start']);
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
	}

	public function _get_datatables_query_orders_shipment_status($term)
	{
		$current_tab = $_REQUEST['current_tab'];
		$extra_select = ',so.order_barcode';
		$this->db->select('s.*,sosd.tracking_id,sosd.tracking_url' . $extra_select);
		$this->db->from('shipment_detail_status as s');
		$this->db->join('sales_order as so', 's.order_id = so.order_id', 'LEFT');
		$this->db->join('sales_order_shipment_details as sosd', 's.shipment_detail_id = sosd.id', 'LEFT');
		if (isset($current_tab) && !empty($current_tab)) {
			$this->db->where('s.status', $current_tab);
		}
		if ($term != '') {
			$this->db->where(" (
			so.order_barcode LIKE '%$term%'
			OR sosd.tracking_id LIKE '%$term%'
			OR s.tracker_vendor LIKE '%$term%'
			)");
		}
		$this->db->order_by('s.order_id', 'asc');
	}

	public function count_all_orders_shipment_status()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';
		$current_tab = $_REQUEST['current_tab'];
		$extra_select = ',so.order_barcode';
		$this->db->select('s.*,sosd.tracking_id' . $extra_select);
		$this->db->from('shipment_detail_status as s');
		$this->db->join('sales_order as so', 's.order_id = so.order_id', 'LEFT');
		$this->db->join('sales_order_shipment_details as sosd', 's.shipment_detail_id = sosd.id', 'LEFT');
		if (isset($current_tab) && !empty($current_tab)) {
			$this->db->where('s.status', $current_tab);
		}
		if ($term != '') {
			$this->db->where(" (
			so.order_barcode LIKE '%$term%'
			OR sosd.tracking_id LIKE '%$term%'
			OR s.tracker_vendor LIKE '%$term%'
			)");
		}
		$this->db->order_by('s.order_id', 'asc');
		return $this->db->count_all_results();
	}

	function count_filtered_orders_shipment_status()
	{
		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';
		$this->_get_datatables_query_orders_shipment_status($term);
		$query = $this->db->get();
		return $query->num_rows();
	}
	public function check_ccavnue_order()
	{
		$this->db->select('sop.order_id as sop_id,sop.transaction_id,sop.payment_id,so.*');
		$this->db->where('sop.payment_method', 'cc_avenue');
		$this->db->where('sop.transaction_id !=', "");
		$this->db->from('sales_order_payment as sop');
		$this->db->join('sales_order as so', 'sop.order_id = so.order_id', 'LEFT');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}
	function getShopsForBTwoBOrders($order_id)
	{





		$param = array($order_id);



		/*

		$query = "SELECT p.shop_id FROM $shop_db.sales_order_items as oi  INNER JOIN $shop_db.products as p ON oi.product_id = p.id LEFT JOIN $shop_db.products_inventory as pi ON oi.product_id = pi.product_id WHERE p.product_inv_type IN ('dropship','virtual') AND pi.qty <=0 and oi.order_id = ? AND p.shop_product_id > 0 group by p.shop_id";

		*/



		$sql = "SELECT oi.publisher_id FROM sales_order_items as oi  INNER JOIN products as p ON oi.product_id = p.id LEFT JOIN products_inventory as pi ON oi.product_id = pi.product_id WHERE/* p.product_inv_type IN ('dropship') AND */ oi.order_id = $order_id  group by oi.publisher_id";



		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	function getPublisherIdByShopId($publisher_id)
	{



		$sql =  "SELECT * FROM publisher where id = " . $publisher_id;

		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			return $query->row_array();
		} else {
			return false;
		}
	}

	function getProductsDropShipAndVirtualWithQtyZero($order_id, $seller_shop_id)
	{

		$param = array($order_id, $seller_shop_id);




		$sql = "SELECT oi.item_id,oi.price,oi.qty_ordered,oi.total_price,oi.publisher_id,oi.product_id FROM sales_order_items as oi  INNER JOIN products as p ON oi.product_id = p.id LEFT JOIN products_inventory as pi ON oi.product_id = pi.product_id WHERE /*p.product_inv_type IN ('dropship') AND */ oi.order_id = $order_id AND oi.publisher_id= $seller_shop_id ";



		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	function getB2BOrderItemsByIds($order_id, $item_ids)
	{





		// $shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable

		// $main_db = DB_NAME; //Constant variable



		$param = array($order_id);


		$Result = "SELECT * FROM sales_order_items where order_id=$order_id AND item_id IN ($item_ids)";



		$query = $this->db->query($Result);
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function getproductDetailsByShopCode($product_id)
	{
		$param = [$product_id];
		$Result = "SELECT products.* FROM products WHERE products.id = $product_id ";



		$query = $this->db->query($Result);
		if ($query->num_rows() > 0) {
			return $query->row_array();
		} else {
			return false;
		}
	}
	function getSpecialPricingB2b($buyer_shopid, $seller_product_id)
	{



		// $shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable

		$params = array($seller_product_id, $buyer_shopid, time());
		$time = time();

		$Result = "SELECT * FROM products_special_prices_b2b where product_id = $seller_product_id and publisher_id = $buyer_shopid AND $time BETWEEN special_price_from and special_price_to";
		// print_r($params);
		// die();

		$query = $this->db->query($Result);
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}
	// end shipment status


	public function abundantCartListDetails()
	{
		$this->db->select('`sq`.*, `c`.email_id, `c`.mobile_no , `c`.first_name , `c`.last_name ');
		$this->db->from('sales_quote as sq');
		$this->db->join('customers as c', 'sq.customer_id = c.id', 'left');
		// $this->db->where('sq.quote_id', $quote_id);
		$this->db->where('sq.customer_id <> ', 0);
		$this->db->order_by('sq.updated_at', 'desc');


		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function abundantCartDetails($quote_id)
	{
		$this->db->select('`sq`.*, `c`.email_id, `c`.mobile_no , CONCAT(`c`.first_name , " " , `c`.last_name ) as customer_name');
		$this->db->from('sales_quote as sq');
		$this->db->join('customers as c', 'sq.customer_id = c.id', 'left');
		// $this->db->where('sq.quote_id', $quote_id);
		$this->db->where('sq.quote_id', $quote_id);

		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function abundantCartProductDetails($quote_id)
	{
		$this->db->select('*');
		$this->db->from('sales_quote_items');
		$this->db->where('quote_id', $quote_id);

		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function abundantCartAddressDetails($quote_id)
	{
		$this->db->select('*');
		$this->db->from('sales_quote_address');
		$this->db->where('quote_id', $quote_id);

		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function updateEmailSentFlag($quote_id)
	{
		// print_r($quote_id);die;

		$SentMailData = array(
			'email_sent'		=> 1,
			'updated_at' => strtotime(date('Y-m-d H:i:s')),
		);
		$this->db->where('quote_id', $quote_id);

		return $this->db->update('sales_quote', $SentMailData);
		// echo $this->db->last_Query();die;
	}


	public function getAbundantCartOrderItems($quote_id)
	{
		$this->db->select('*');
		$this->db->from('sales_quote_items');
		$this->db->where('quote_id', $quote_id);
		$this->db->where('product_type !=', 'bundle');


		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function getAbundantCartDataById($quote_id)

	{

		$this->db->select('*');
		$this->db->from('sales_quote');
		$this->db->where('quote_id', $quote_id);


		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	// public function getAbundantCartOrderItems($quote_id)

	// {

	// 	$sql =  "SELECT * FROM sales_quote_items where `quote_id` = '$quote_id' AND product_type != 'bundle'";

	// 	$Result  = $this->dbl->dbl_conn->rawQuery($sql);

	// 	if ($this->dbl->dbl_conn->getLastErrno() === 0) {

	// 		if ($this->dbl->dbl_conn->count > 0) {

	// 			return $Result;
	// 		} else {

	// 			return false;
	// 		}
	// 	} else {

	// 		return false;
	// 	}
	// }

	public function getOrderPaymentDataById($quote_id)
	{
		$this->db->select('*');
		$this->db->from('sales_quote_payment');
		$this->db->where('quote_id', $quote_id);

		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function abundantCartProductImagesDetails($parent_product_id, $product_id)
	{
		$this->db->select('*');
		$this->db->from('products');
		if ($parent_product_id != 0) {
			$this->db->where('id', $parent_product_id);
		} else {
			$this->db->where('id', $product_id);
		}
		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
}
