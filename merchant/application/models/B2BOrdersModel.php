<?php

class B2BOrdersModel extends CI_Model

{

	public function __construct()

	{

		parent::__construct();

		$fbc_user_id	=	$this->session->userdata('ShopOwnerId');  //old LoginID

		$shop_id		=	$this->session->userdata('ShopID');



		// $FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('fbc_user_id'=>$fbc_user_id),'shop_id,fbc_user_id,database_name');

		// if (isset($FBCData) && $FBCData->database_name != '') {

		// 	$fbc_user_database = $FBCData->database_name;



		// 	$this->load->database();

		// 	$config_app = fbc_switch_db_dynamic(DB_PREFIX . $fbc_user_database);

		// 	$this->db = $this->load->database($config_app, TRUE);

		// 	if ($this->db->conn_id) {

		// 		//do something

		// 	} else {

		// 		redirect(base_url());
		// 	}
		// } else {

		// 	redirect(base_url());
		// }
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



	function get_datatables_orders($LogindID,$price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method)
	{

		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';

		$this->_get_datatables_query_orders($LogindID,$term, $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method);

		if ($_REQUEST['length'] != -1)

			$this->db->limit($_REQUEST['length'], $_REQUEST['start']);

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->result();
	}



	public function _get_datatables_query_orders($LogindID,$term, $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method)
	{



		$main_db_name = $this->db->database;

		$current_tab = $_REQUEST['current_tab'];



		$extra_select = '';



		if ($current_tab == 'split-orders') {

			$extra_select = ' ,bos.id as order_shipment_id';
		}



		$column = array('o.increment_id', 'o.created_at', 'customer_name', '', '', '', '');

		$this->db->distinct();

		$this->db->select('o.*,IF(o.shipment_type=1,p.publication_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name' . $extra_select);

		$this->db->from('b2b_orders as o');

		// $this->db->join($main_db_name . '.fbc_users_shop as fus', 'o.shop_id = fus.shop_id', 'LEFT');

		$this->db->join('publisher as p', 'o.publisher_id = p.id', 'LEFT');
		$this->db->join('sales_order_address as soa', 'soa.order_id = o.webshop_order_id', 'LEFT');
		// $this->db->join('sales_order_address as soa', 'soa.order_id = o.order_id', 'LEFT');

		if ($current_tab == 'split-orders') {

			$this->db->join('b2b_order_shipment as bos', 'o.order_id = bos.order_id', 'LEFT');
		}



		if ($current_tab == 'orders') {

			$this->db->where('o.parent_id', '0');

			$this->db->where('o.is_split', '0');

			$this->db->where('(o.status NOT IN (3,4,5,6))');
			// $this->db->where('(o.status NOT IN (4,5,6))'); //old

		} else if ($current_tab == 'split-orders') {

			$this->db->where('o.parent_id >', '0');

			//$this->db->where('o.is_split <>','1');

			$this->db->where('bos.id IS NULL');
		} else if ($current_tab == 'shipped-orders') {

			$this->db->where('o.parent_id', '0');

			$this->db->where('o.main_parent_id', '0');

			//$this->db->where('o.is_split','0');

			$this->db->where('(o.status IN (4,5,6))');
		} else if ($current_tab == 'cancel-orders') {
			$this->db->where('o.parent_id', '0');
			$this->db->where('o.main_parent_id', '0');
			//$this->db->where('o.is_split','0');
			$this->db->where('(o.status IN (3))');
		}




		if ($term != '') {



			$this->db->where(" (

			o.increment_id LIKE '%$term%'

			OR o.grand_total LIKE '%$term%'

			OR o.customer_firstname LIKE '%$term%'

			OR o.customer_lastname LIKE '%$term%'

			OR p.publication_name LIKE '%$term%'

			OR soa.city LIKE '%$term%'

			/*OR fus.org_shop_name LIKE '%$term%'*/

			/*OR fu.owner_name LIKE '%$term%'*/

			 )");
		}



		if (!empty($price)) {



			$this->db->where('p.grand_total >=', 0);

			$this->db->where('p.grand_total <=', $price);
		}





		if (isset($order_status) && $order_status != '') {

			$this->db->where("o.status", $order_status);
		}



		if (!empty($shipment_type)) {

			$this->db->where("o.shipment_type IN ($shipment_type)");
		}



		if (!empty($payment_method)) {
			$this->db->where("wp.payment_method_id", $payment_method);
		}

		if (!empty($fromDate) && empty($toDate)) {



			$this->db->where('o.updated_at >=', strtotime($fromDate));
		} else if (!empty($toDate) && empty($fromDate)) {



			$this->db->where('o.updated_at <=', strtotime($toDate));
		} else if (!empty($toDate) && !empty($fromDate)) {

			$this->db->where('o.updated_at >=', strtotime($fromDate));

			$this->db->where('o.updated_at <=', strtotime($toDate));
		}



		/*---------------------Check for Zumbashop India Orders---------------------------------------------*/



		$UserRole = $this->session->userdata('UserRole');

		if ($UserRole == 'Zumbashop India User') {

			$FindZumbaShop = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_flag' => 2), '');

			if (isset($FindZumbaShop) && $FindZumbaShop->shop_id != '') {

				$this->db->where('o.shop_id', $FindZumbaShop->shop_id);   //$FindZumbaShop->shop_id

				$this->db->where('o.shipment_type', 2);
			}
		}

		/*-------------------------------------------------------------------------------------------------*/



		$this->db->where('(o.status NOT IN (7))');

		$this->db->where('o.publisher_id', $LogindID);



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





	public function count_all_orders($price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method)
	{

		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';



		$current_tab = $_REQUEST['current_tab'];

		$main_db_name = $this->db->database;

		$extra_select = '';



		if ($current_tab == 'split-orders') {

			$extra_select = ' ,bos.id as order_shipment_id';
		}

		$column = array('o.increment_id', 'o.created_at', 'customer_name', '', '', '', '');

		$this->db->distinct();

		$this->db->select('o.*,IF(o.shipment_type=1,p.publication_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name' . $extra_select);

		$this->db->from('b2b_orders as o');

		// $this->db->join($main_db_name . '.fbc_users_shop as fus', 'o.shop_id = fus.shop_id', 'LEFT');

		$this->db->join('publisher as p', 'o.publisher_id = p.id', 'LEFT');



		if ($current_tab == 'split-orders') {

			$this->db->join('b2b_order_shipment as bos', 'o.order_id = bos.order_id', 'LEFT');
		}



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

			/*OR fus.org_shop_name LIKE '%$term%'*/

			 )");
		}



		if (!empty($price)) {



			$this->db->where('p.grand_total >=', 0);

			$this->db->where('p.grand_total <=', $price);
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



		/*---------------------Check for Zumbashop India Orders---------------------------------------------*/



		$UserRole = $this->session->userdata('UserRole');

		if ($UserRole == 'Zumbashop India User') {

			$FindZumbaShop = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_flag' => 2), '');

			if (isset($FindZumbaShop) && $FindZumbaShop->shop_id != '') {

				$this->db->where('o.shop_id', $FindZumbaShop->shop_id);

				$this->db->where('o.shipment_type', 2);
			}
		}

		/*-------------------------------------------------------------------------------------------------*/



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



	function count_filtered_orders($LogindID,$price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method)
	{

		$term = $_REQUEST['search']['value'];

		$this->_get_datatables_query_orders($LogindID,$term, $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method);

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

		$query = $this->db->get('b2b_orders');

		return $query->row();
	}





	function getOrderCustomerNameByOrderId($order_id)

	{

		$full_name = '';

		$this->db->reset_query();



		$_order = $this->db->get_where('b2b_orders', array('order_id' => $order_id))->row();

		$shop_id = $_order->shop_id;

		//print_r($_order);

		if ($_order->shipment_type == 1) {



			$result = $this->db->get_where('fbc_users', array('shop_id' => $shop_id, 'parent_id' => 0, 'created_by' => 0))->row();

			if (isset($result) && ($result->owner_name != '')) {

				$full_name = $result->owner_name;
			} else {

				$full_name = 'Unknown';
			}
		} else {

			if ($_order->parent_id > 0) {
				$_order_data = $this->db->get_where('b2b_orders', array('order_id' => $_order->parent_id))->row();
				$full_name = $_order_data->customer_firstname . ' ' . $_order_data->customer_lastname;
			} else {
				$full_name = $_order->customer_firstname . ' ' . $_order->customer_lastname;
			}
		}

		return $full_name;
	}



	function getOrderItems($order_id)

	{

		$this->db->select("oi.*,pi.qty");

		$this->db->from('b2b_order_items as oi');

		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');

		$this->db->where('oi.order_id', $order_id);

		$this->db->order_by('oi.qty_scanned', 'asc');

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->result();
	}


	function getOrderItemsNew($order_id)

	{
		// print_r($order_id);die;

		$this->db->select("oi.*,pi.qty,soi.gift_name");

		$this->db->from('b2b_order_items as oi');

		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');
		$this->db->join('b2b_orders as bo', 'oi.order_id = bo.order_id', 'LEFT');
		$this->db->join('sales_order as so', 'so.increment_id = bo.webshop_order_id', 'LEFT');
		$this->db->join('sales_order_items as soi', 'so.increment_id = soi.order_id', 'LEFT');

		$this->db->where('oi.order_id', $order_id);

		$this->db->order_by('oi.qty_scanned', 'asc');

		$query = $this->db->get();

		// echo $this->db->last_query();exit;

		return $query->result();
	}


	function checkOrderItemsExist($order_id, $barcode)

	{

		$this->db->select("oi.*");

		$this->db->from('b2b_order_items as oi');

		$this->db->where('oi.order_id', $order_id);

		$this->db->where('oi.barcode', $barcode);

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->row();
	}



	function incrementOrderItemQtyScanned($item_id)

	{

		$sql = "UPDATE b2b_order_items   SET qty_scanned = qty_scanned + 1   WHERE item_id = $item_id";

		$this->db->query($sql);

		$this->db->reset_query();
	}



	function getOrderItemRowClass($item_id)
	{



		$item = $this->B2BOrdersModel->getSingleDataByID('b2b_order_items', array('item_id' => $item_id), 'item_id,qty_scanned,qty_ordered');

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

		$this->db->from('b2b_order_items as oi');

		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');

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

		$this->db->from('b2b_order_items as oi');

		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');

		$this->db->where('oi.order_id', $order_id);

		//$this->db->where('(oi.qty_scanned>0)');

		$this->db->order_by('oi.qty_scanned', 'asc');

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->result();
	}



	function getPartialScannedSingleOrderItems($order_id, $product_id)

	{

		$this->db->select("oi.*");

		$this->db->from('b2b_order_items as oi');

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

		$this->db->from('b2b_order_items as oi');

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

		$this->db->from('b2b_order_items as oi');

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

		$this->db->from('b2b_order_items as oi');

		//$this->db->join('b2b_orders as o','oi.order_id = o.order_id','LEFT');

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

		$this->db->from('b2b_order_items as oi');

		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');

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

		$this->db->delete('b2b_order_items');

		$this->db->reset_query();
	}



	public function getEmailTemplateById($TemplateId)

	{

		$result = $this->db->get_where('email_template', array('id' => $TemplateId))->row();

		return $result;
	}



	public function getEmailTemplateByIdentifier($identifier)
	{

		$result = $this->db->get_where('email_template', array('email_code' => $identifier))->row();

		return $result;
	}



	public function getCustomVariableByIdentifier($identifier)
	{

		$result = $this->db->get_where('custom_variables', array('identifier' => $identifier))->row();

		return $result;
	}





	public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $SubDynamic = '', $CommonVars = '', $cc)
	{
		// echo "hiii";
		// die;

		$webshop_smtp_host = 'smtp.gmail.com'; // $this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port =  465; // $this->getCustomVariableByIdentifier('smtp_port') ??
		$webshop_smtp_username = 'care@indiamags.com'; //$this->getCustomVariableByIdentifier('smtp_username') ?? 
		$webshop_smtp_password = 'rlhbawwjqezsiqjn'; //$this->getCustomVariableByIdentifier('smtp_password') ?? 
		$webshop_smtp_secure = $this->getCustomVariableByIdentifier('smtp_secure');

		$GlobalVar = $this->getCustomVariableByIdentifier('admin_email');

		if (isset($GlobalVar) && $GlobalVar->value != '') {

			$from_email = $GlobalVar->value ?? 'suzan@indiamags.com';
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



			if ($templateId == 4 || $templateId == 6) {

				if (isset($SubDynamic) && $SubDynamic != '') {

					$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
				} else {

					$subject = str_replace('##ORDERID##', '', $subject);
				}
			}

			//new code added
			if ($identifier == 'fbcuser-order-tracking-completed') {
				if (isset($identifier) && $identifier != '') {
					$subject = str_replace('##ORDERID##', $identifier, $subject);
				} else {
					$subject = str_replace('##ORDERID##', '', $subject);
				}
			}
			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);
			if ($identifier == 'request_a_payment_for_order') {
				if (isset($identifier) && $identifier != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic[1], $subject);
				}
				$emailBody = str_replace('##ORDERID##', $DynamicVars[1], $emailTemplate->content);
				$emailBody = str_replace('##PAYMENTINFOTABLE##', $DynamicVars[0], $emailBody);
				$emailBody = str_replace('##WEBSHOPNAME##', $DynamicVars[2], $emailBody);
				// $cc = 'snehals@bcod.co.in';
				$cc = 'heeral@whuso.in,anu@bcod.co.in,accounts@bcod.co.in,suzan@indiamags.com,ronika@indiamags.com';

				// print_r($cc);
				// die;

				$from_email = 'suzan@indiamags.com';
			}
			if ($identifier == 'payment_done_for_order') {
				if (isset($identifier) && $identifier != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic[1], $subject);
					$subject = str_replace('##PUBLISHERNAME##', $SubDynamic[0], $subject);
				}
				$emailBody = str_replace('##ORDERID##', $DynamicVars[2], $emailTemplate->content);
				$emailBody = str_replace('##PAYMENTINFOTABLE##', $DynamicVars[0], $emailBody);
				$emailBody = str_replace('##PUBLISHERORDERTABLE##', $DynamicVars[1], $emailBody);
				$emailBody = str_replace('##WEBSHOPNAME##', $DynamicVars[3], $emailBody);
				$from_email = 'care@indiamags.com';
				// $cc = 'support@bcod.co.in';
				$to_email_array_merge = array_merge(['heeral@whuso.in'], ['care@indiamags.com'], ['ronika@indiamags.com'], ['support@bcod.co.in'], ['suzan@indiamags.com'], [$cc]);
				// $to_email_array_merge = array_merge(['heeral@whuso.in'],['care@indiamags.com'],['ronika@indiamags.com'],['snehals@bcod.co.in'], [$cc]);
				$new_cc_email_array = [];

				foreach ($to_email_array_merge as $item) {
					if (is_array($item)) {
						$new_cc_email_array = array_merge($new_cc_email_array, $item);
					} else {
						$new_cc_email_array[] = $item;
					}
				}
				$cc = $new_cc_email_array;
				// print_r($cc);
				// die;
			}

			if ($identifier == 'hbr_request_a_payment_for_order') {
				if (isset($identifier) && $identifier != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic[1], $subject);
				}
				$emailBody = str_replace('##ORDERID##', $DynamicVars[1], $emailTemplate->content);
				$emailBody = str_replace('##PAYMENTINFOTABLE##', $DynamicVars[0], $emailBody);
				$emailBody = str_replace('##WEBSHOPNAME##', $DynamicVars[2], $emailBody);
				$cc = 'saroj@bcod.co.in,snehals@bcod.co.in';
				// $cc = 'hemang@whuso.in,heeral@whuso.in,saroj@bcod.co.in,snehals@bcod.co.in,accounts@bcod.co.in';

				// print_r($cc);
				// die;

				$from_email = 'suzan@indiamags.com';
			}
			if ($identifier == 'hbr_payment_done_for_order') {
				if (isset($identifier) && $identifier != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic[1], $subject);
					$subject = str_replace('##PUBLISHERNAME##', $SubDynamic[0], $subject);
				}
				$emailBody = str_replace('##ORDERID##', $DynamicVars[2], $emailTemplate->content);
				$emailBody = str_replace('##PAYMENTINFOTABLE##', $DynamicVars[0], $emailBody);
				$emailBody = str_replace('##PUBLISHERORDERTABLE##', $DynamicVars[1], $emailBody);
				$emailBody = str_replace('##WEBSHOPNAME##', $DynamicVars[3], $emailBody);
				// $from_email = 'suzan@indiamags.com';
				// $cc = 'heeral@whuso.in';
				$to_email_array_merge = array_merge(['snehals@bcod.co.in'], ['saroj@bcod.co.in'], [$cc]);
				// $to_email_array_merge = array_merge(['heeral@whuso.in'], ['care@indiamags.com'], ['ronika@indiamags.com'], ['snehals@bcod.co.in'], ['suzan@indiamags.com'], [$cc]);



				$new_cc_email_array = [];

				foreach ($to_email_array_merge as $item) {
					if (is_array($item)) {
						$new_cc_email_array = array_merge($new_cc_email_array, $item);
					} else {
						$new_cc_email_array[] = $item;
					}
				}
				$cc = $new_cc_email_array;
			}
			//new code added 07-10-2021

			// $emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);

			// print_r($TempVars);
			// print_r($DynamicVars);
			// echo $emailBody;
			// die();
			/*

			$data['title'] = $title;

			$data['subject'] = $subject;

			$data['content'] = $emailBody;



			$content = $this->load->view('email_template/email_content', $data, TRUE);

			*/



			$FinalContentBody = $HeaderPart . $emailBody . $FooterPart;
			// die;
			if ($this->CommonModel->sendHTMLMailSMTP($EmailTo, $subject, $FinalContentBody, $from_email, $attachment = "", $webshop_smtp_host, $webshop_smtp_port, $webshop_smtp_username, $webshop_smtp_password, $webshop_smtp_secure, $cc, $identifier)) {
				return true;
			} else {
				return false;
			}
		}
	}



	function getSplitChildOrderIds($main_parent_id)
	{

		$this->db->select("order_id,increment_id");

		$this->db->from('b2b_orders');

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

		$this->db->from('b2b_order_items as oi');

		$this->db->join('b2b_orders as o', 'oi.order_id = o.order_id', 'LEFT');

		$this->db->join('b2b_order_shipment as oshp', 'oi.order_id = oshp.order_id', 'LEFT');

		if (isset($is_split) && $is_split == 1) {

			$this->db->where('o.main_parent_id', $order_id);
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

		$this->db->from('b2b_order_shipment');

		$this->db->where('order_id', $order_id);

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->row();
	}



	function getShippedSingleOrderItems($order_id, $product_id, $is_split = '')

	{

		$this->db->select("sum(qty_scanned) as qty_scanned");

		$this->db->from('b2b_order_items as oi');

		$this->db->join('b2b_orders as o', 'oi.order_id = o.order_id', 'LEFT');

		$this->db->join('b2b_order_shipment as oshp', 'oi.order_id = oshp.order_id', 'LEFT');

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

		$sql = "	UPDATE products_inventory SET qty = CASE   WHEN qty <= 0 THEN 0 WHEN qty >=$qty THEN  qty - $qty END WHERE product_id = $product_id ";

		$this->db->query($sql);

		return true;
	}



	function decrementOrderItemStock($order_id)
	{

		$OrderItems = $this->B2BOrdersModel->getOrderItems($order_id);

		if (isset($OrderItems) && count($OrderItems) > 0) {

			foreach ($OrderItems as $value) {

				$product_id = $value->product_id;

				$qty_scanned = $value->qty_scanned;

				$this->decrementProductStockQty($product_id, $qty_scanned);
			}
		}
	}





	function getOrderItemsWithOrderId($order_id, $is_split = '')

	{

		$this->db->select("oi.*,o.increment_id");

		$this->db->from('b2b_order_items as oi');

		$this->db->join('b2b_orders as o', 'oi.order_id = o.order_id', 'LEFT');

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

		$this->db->from('b2b_order_items as oi');

		$this->db->where('oi.order_id', $order_id);

		$this->db->where('oi.item_id', $item_id);

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->row();
	}



	function incrementOrderItemQtyScannedByQty($item_id, $qty)

	{

		$sql = "UPDATE b2b_order_items   SET qty_scanned = qty_scanned + $qty   WHERE item_id = $item_id";

		$this->db->query($sql);

		$this->db->reset_query();
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

	function get_b2border_invoicing_data($order_id)
	{
		// function ttest(){
		//$order_id='36';
		$order_id = $order_id;
		$main_db_name = $this->db->database;
		$current_tab = 'shipped-orders';
		//$order_status='6';

		$extra_select = '';

		$column = array('o.increment_id', 'o.created_at', 'customer_name', '', '', '', '');
		$this->db->distinct();
		// $this->db->select('o.*,c.invoice_type,fu.fbc_user_id,IF(o.shipment_type=1,fu.owner_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name, fus.org_shop_name'.$extra_select);
		$this->db->select('o.*,c.invoice_type,c.payment_term,fu.fbc_user_id,IF(o.shipment_type=1,CONCAT(fu.owner_name) ) as customer_name, fus.org_shop_name, fus.gst_no, fus.bill_address_line1, fus.bill_address_line2, fus.bill_city, fus.bill_state, fus.bill_country, fus.bill_pincode, fus.ship_address_line1, fus.ship_address_line2, fus.ship_city, fus.ship_state, fus.ship_country, fus.ship_pincode, fu.email, fus.company_name' . $extra_select);

		$this->db->from('b2b_orders as o');
		$this->db->join($main_db_name . '.fbc_users_shop as fus', 'o.shop_id = fus.shop_id', 'LEFT');
		$this->db->join($main_db_name . '.fbc_users as fu', 'o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id', 'LEFT');

		$this->db->join('b2b_customers_invoice as c', 'fus.fbc_user_id = c.customer_id', 'LEFT');


		if ($current_tab == 'shipped-orders') {
			$this->db->where('o.parent_id', '0');
			$this->db->where('o.main_parent_id', '0');
			//$this->db->where('o.is_split','0');
			// $this->db->where('(o.status IN (4,5,6))');
			// $this->db->where('(o.status IN (6))'); testing purpose comment
		}



		if (!empty($order_status)) {
			//$this->db->where("o.status",$order_status);
		}


		/*---------------------Check for Zumbashop India Orders---------------------------------------------*/

		$UserRole = $this->session->userdata('UserRole');
		if ($UserRole == 'Zumbashop India User') {
			$FindZumbaShop = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_flag' => 2), '');
			if (isset($FindZumbaShop) && $FindZumbaShop->shop_id != '') {
				$this->db->where('o.shop_id', $FindZumbaShop->shop_id);   //$FindZumbaShop->shop_id
				$this->db->where('o.shipment_type', 2);
			}
		}
		/*-------------------------------------------------------------------------------------------------*/
		$this->db->where('(o.status NOT IN (7))');
		// $this->db->where_in('o.order_id',$order_id);
		$this->db->where('o.order_id', $order_id);

		$this->db->order_by('o.order_id', 'desc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		//return $query->result();
		return $query->row();
	}

	function get_pdf_b2border_invoicing_data($order_id)
	{
		// function ttest(){
		//$order_id='36';
		$order_id = $order_id;
		$main_db_name = $this->db->database;
		$current_tab = 'shipped-orders';
		//$order_status='6';

		$extra_select = '';

		$column = array('o.increment_id', 'o.created_at', 'customer_name', 'fus.org_shop_name', '', '', '', '');
		$this->db->distinct();
		// $this->db->select('o.*,c.invoice_type,fu.fbc_user_id,IF(o.shipment_type=1,fu.owner_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name, fus.org_shop_name'.$extra_select);
		$this->db->select('o.*,c.invoice_type,c.payment_term,fu.fbc_user_id,IF(o.shipment_type=1,fu.owner_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name, fus.org_shop_name, fus.gst_no, fus.bill_address_line1, fus.bill_address_line2, fus.bill_city, fus.bill_state, fus.bill_country, fus.bill_pincode, fus.ship_address_line1, fus.ship_address_line2, fus.ship_city, fus.ship_state, fus.ship_country, fus.ship_pincode, fu.email, fus.company_name' . $extra_select);
		// $this->db->select('o.*,c.invoice_type,IF(o.shipment_type=1,fu.owner_name,CONCAT(o.customer_firstname, " ", o.customer_lastname) ) as customer_name, fus.org_shop_name'.$extra_select);
		$this->db->from('b2b_orders as o');
		$this->db->join($main_db_name . '.fbc_users_shop as fus', 'o.shop_id = fus.shop_id', 'LEFT');
		$this->db->join($main_db_name . '.fbc_users as fu', 'o.shop_id = fu.shop_id AND fus.fbc_user_id = fu.fbc_user_id', 'LEFT');

		$this->db->join('b2b_customers_invoice as c', 'fus.fbc_user_id = c.customer_id', 'LEFT');


		if ($current_tab == 'shipped-orders') {
			$this->db->where('o.parent_id', '0');
			$this->db->where('o.main_parent_id', '0');
			//$this->db->where('o.is_split','0');
			// $this->db->where('(o.status IN (4,5,6))');
			// $this->db->where('(o.status IN (6))'); testing purpose comment
		}



		if (!empty($order_status)) {
			//$this->db->where("o.status",$order_status);
		}


		/*---------------------Check for Zumbashop India Orders---------------------------------------------*/

		$UserRole = $this->session->userdata('UserRole');
		if ($UserRole == 'Zumbashop India User') {
			$FindZumbaShop = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_flag' => 2), '');
			if (isset($FindZumbaShop) && $FindZumbaShop->shop_id != '') {
				$this->db->where('o.shop_id', $FindZumbaShop->shop_id);   //$FindZumbaShop->shop_id
				$this->db->where('o.shipment_type', 2);
			}
		}
		/*-------------------------------------------------------------------------------------------------*/
		$this->db->where('(o.status NOT IN (7))');
		$this->db->where_in('o.order_id', $order_id);
		// $this->db->where('o.order_id',$order_id);

		$this->db->order_by('o.order_id', 'desc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
		return $query->result();
		// return $query->row();
	}

	// order items data by tax
	function get_order_item_tax_percent($order_id)
	{
		$this->db->select("tax_percent,sum(total_price) as taxsum");
		$this->db->from('b2b_order_items');
		$this->db->where('order_id', $order_id);
		$this->db->group_by('tax_percent');
		$this->db->order_by('tax_percent');
		$query = $this->db->get();
		return $query->result();
	}

	// order items data
	function getOrder_multi_Items($order_id)
	{
		$this->db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type");
		$this->db->from('b2b_order_items as oi');
		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');
		$this->db->join('b2b_orders as o', 'oi.order_id = o.order_id', 'LEFT');
		$this->db->where_in('oi.order_id', $order_id);
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		//echo $this->db->last_query();exit;
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
				return true;
			} else {
				return false;
			}
		}
	}


	public function getProdLocation($product_id)
	{
		$this->db->select("prod_location");
		$this->db->from('products');
		$this->db->where('id', $product_id);
		$query = $this->db->get();
		
		$result = $query->row();

		// Check if result is null or prod_location is empty
		if ($result && !empty($result->prod_location)) {
			return $result->prod_location;
		} else {
			return '-';
		}
	}


	function getOrderCustomerNameByOrderId_test($order_id)

	{

		$full_name = '';

		$this->db->reset_query();



		$_order = $this->db->get_where('b2b_orders', array('order_id' => $order_id))->row();

		$shop_id = $_order->shop_id;

		//print_r($_order);

		if ($_order->shipment_type == 1) {



			$result = $this->db->get_where('fbc_users', array('shop_id' => $shop_id, 'parent_id' => 0, 'created_by' => 0))->row();

			if (isset($result) && ($result->owner_name != '')) {

				$full_name = $result->owner_name;
			} else {

				$full_name = 'Unknown';
			}
		} else {

			if ($_order->parent_id > 0) {
				$_order_data = $this->db->get_where('b2b_orders', array('order_id' => $_order->parent_id))->row();
				$full_name = $_order_data->customer_firstname . ' ' . $_order_data->customer_lastname;
			} else {
				$full_name = $_order->customer_firstname . ' ' . $_order->customer_lastname;
			}
		}

		return $full_name;
	}

	public function get_b2b_order_shipment_details($order_id, $order_shipment_id)
	{
		$this->db->select('*');
		$this->db->from('b2b_order_shipment_details');
		$this->db->where('order_shipment_id', $order_shipment_id);
		$query = $this->db->get();
		return $query->result_array();
	}

	public function get_b2b_order_shipment($order_id, $shipment_id)
	{
		$this->db->select('*');
		$this->db->from('b2b_order_shipment');
		$this->db->where('id', $shipment_id);
		$this->db->where('order_id', $order_id);
		$query = $this->db->get();
		return $query->row();
	}

	function  b2b_cancel_order_request($order_id, $reason_for_cancel, $User_id)
	{

		$status = 3;
		$cancel_by_customer = 1;
		$time = time();
		$params = array($reason_for_cancel, time(), $User_id, $order_id);

		$update_row = "UPDATE b2b_orders set  status = 3 ,cancel_reason = '$reason_for_cancel', cancel_date = $time , cancel_by_admin = $User_id where  order_id = $order_id";
		$query = $this->db->query($update_row);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	function  cancel_order_request($order_id, $reason_for_cancel, $User_id)
	{

		$status = 3;
		$cancel_by_customer = 1;
		$time = time();
		$params = array($reason_for_cancel, time(), $User_id, $order_id);

		$update_row = "UPDATE sales_order_items set  cancel_reason = '$reason_for_cancel', cancel_date = $time , cancel_by_admin = $User_id where  order_id = $order_id";
		$query = $this->db->query($update_row);
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}


	function getsalesOrderItemsData($order_id, $product_id)
	{

		$this->db->select("*");
		$this->db->from('sales_order_items');
		$this->db->where('order_id', $order_id);
		$this->db->where('product_id', $product_id);
		$query = $this->db->get();
		// echo $this->db->last_query();exit;

		return $query->row_array();
	}

	function updateVariant($item_id, $start_date, $end_date)
	{
		$data	=  array('sub_start_date' => $start_date, 'sub_end_date' => $end_date);
		$this->db->where('item_id', $item_id);
		$this->db->update('sales_order_items', $data);
		// echo $this->db->last_query();die();
		return true;
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function get_publisher($order_id)
	{
		$this->db->select('soi.*, pa.id, pa.attr_id, pa.attr_value');
		$this->db->from('sales_order_items as soi');
		$this->db->join('products_attributes as pa', 'soi.product_id = pa.product_id', 'left');
		$this->db->where_in('soi.order_id', $order_id);
		$this->db->where('soi.publisher_id ', '139');

		$query = $this->db->get();
		// echo $this->db->last_query();die;

		return $query->result_array();
	}


	public function get_order_Product($order_id)
	{
		$this->db->select('soi.*, pa.id, pa.attr_id, pa.attr_value');
		$this->db->from('sales_order_items as soi');
		$this->db->join('products_attributes as pa', 'soi.product_id = pa.product_id', 'left');
		$this->db->where_in('soi.order_id', $order_id);
		$query = $this->db->get();
		// echo $this->db->last_query();die;

		return $query->result_array();
	}

	public function get_order_details($product_id, $attribute_id, $order_id, $publisher_id)
	{
		$this->db->select('soi.*, pa.id, pa.attr_id, pa.attr_value,bo.status,bo.grand_total');
		$this->db->from('sales_order_items as soi');
		$this->db->join('products_attributes as pa', 'soi.product_id = pa.product_id', 'left');
		$this->db->join('b2b_orders as bo', 'soi.order_id = bo.webshop_order_id', 'left');
		$this->db->where('soi.product_id', $product_id);
		$this->db->where('pa.id', $attribute_id);
		$this->db->where('soi.publisher_id ', $publisher_id);
		$this->db->where('bo.status', '0');
		$this->db->where('bo.publisher_id ', '139');

		$query = $this->db->get();
		// echo $this->db->last_query();die;

		return $query->result_array();
	}

	public function HBRListDetails()
	{
		// $this->db->select('soi.*, pa.id, pa.attr_id, pa.attr_value,bo.status,bo.grand_total,bo.order_barcode');
		// $this->db->from('sales_order_items as soi');
		// $this->db->join('products_attributes as pa', 'soi.product_id = pa.product_id', 'left');
		// $this->db->join('b2b_orders as bo', 'soi.order_id = bo.webshop_order_id', 'left');
		// // $this->db->join('publisher_payment as pp', 'pp.order_id = bo.webshop_order_id', 'left');
		// $this->db->where('soi.product_id', '226');
		// $this->db->where('soi.publisher_id ', '139');
		// $this->db->where('bo.status', '0');
		// // $this->db->where('pp.payment_done', '2');


		$this->db->select('soi.*, pa.id, pa.attr_id, pa.attr_value, bo.status, bo.grand_total, bo.order_barcode');
		$this->db->from('sales_order_items as soi');
		$this->db->join('products_attributes as pa', 'soi.product_id = pa.product_id', 'left');
		$this->db->join('b2b_orders as bo', 'soi.order_id = bo.webshop_order_id', 'left');
		$this->db->join('publisher_payment as pp_with_condition', 'pp_with_condition.order_id = bo.webshop_order_id AND pp_with_condition.payment_done = 2', 'left');
		$this->db->join('publisher_payment as pp_without_condition', 'pp_without_condition.order_id = bo.webshop_order_id AND pp_without_condition.payment_done != 2', 'left');
		// $this->db->join('publisher_payment as pp', 'pp.order_id = bo.webshop_order_id', 'left');

		$this->db->where('soi.product_id', '226');
		$this->db->where('soi.publisher_id', '139');
		$this->db->where('bo.publisher_id', '139');

		// $this->db->where('pp_with_condition.publisher_id', '139');
		// $this->db->where('pp_without_condition.publisher_id', '139');
		$this->db->where('bo.status', '0');
		$this->db->where('(pp_with_condition.order_id IS NOT NULL OR pp_without_condition.order_id IS NULL)');
		// $this->db->group_by('soi.item_id');


		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function B2BOrderDetails($hidden_order_ids)
	{

		// print_r($hidden_order_ids);die;
		// $hidden_order_ids_array = explode(',', $hidden_order_ids);
		$hidden_order_ids_array = array_map('trim', $hidden_order_ids);


		$this->db->select('*');
		$this->db->from('b2b_orders');
		$this->db->where('publisher_id', '139');
		$this->db->where_in('webshop_order_id', $hidden_order_ids_array);

		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function getPublisherPayment($B2b_increment_id, $patent_order_id)
	{

		// print_r($order_id);die;

		$this->db->select('*');
		$this->db->from('publisher_payment');
		$this->db->where_in('order_id', $patent_order_id);
		$this->db->where_in('B2b_order_id', $B2b_increment_id);

		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function OrderItemDataDetails($order_id, $increment_id)
	{
		$order_ids_array = explode(',', $order_id);
		$order_ids_array = array_map('trim', $order_ids_array);
		// echo "<pre>";
		// print_r($order_ids_array);
		// die;
		$increment_id_array = explode(',', $increment_id);
		$increment_id_array = array_map('trim', $increment_id_array);

		$this->db->select('*');
		$this->db->from('b2b_orders');
		$this->db->where_in('webshop_order_id', $order_ids_array);
		$this->db->where_in('increment_id', $increment_id_array);
		$query = $this->db->get();
		// echo $this->db->last_Query();
		// die;
		$resultArr = $query->result_array();
		return $resultArr;
	}



	public function getPublisherDetails($publisher_id)
	{

		// print_r($publisher_id);die;

		$this->db->select('*');
		$this->db->from('publisher');
		$this->db->where_in('id', $publisher_id);
		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function getPublisherPaymentDetails($publisher_id)
	{

		// print_r($order_id);die;

		$this->db->select('*');
		$this->db->from('publisher_payment_details');
		$this->db->where_in('publisher_id', $publisher_id);
		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function getOrderData($webshop_order_id)
	{

		// print_r($order_id);die;

		$this->db->select('*');
		$this->db->from('sales_order');
		$this->db->where_in('order_id', $webshop_order_id);
		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function insertPublisherPaymentData($data)
	{
		$result = $this->db->insert('publisher_payment', $data);

		if ($result) {
			return $this->db->insert_id();
		} else {
			// Handle the case where insertion fails
			return false;
		}
	}


	public function getRecentlyInsertedIds()
	{
		$limit = 10;
		$this->db->select('id');
		$this->db->from('publisher_payment');
		$this->db->order_by('created_at', 'desc');
		$this->db->limit($limit);

		$query = $this->db->get();
		$result = $query->result();

		$insertedIds = array();
		foreach ($result as $row) {
			$insertedIds[] = $row->id;
		}
		// echo $this->db->last_Query();
		// 		die;
		return $insertedIds;
	}
	public function get_publisher_payment_details($last_inserted_publisher_payment_id)
	{

		// print_r($order_id);die;

		$this->db->select('*');
		$this->db->from('publisher_payment');
		$this->db->where_in('id', $last_inserted_publisher_payment_id);
		$query = $this->db->get();
		// echo $this->db->last_Query();
		// die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function PublisherPaymentData($id)
	{

		// print_r($id);die;
		$ids_array = explode(',', $id);
		$ids_array = array_map('trim', $ids_array);
		$this->db->select('*');
		$this->db->from('publisher_payment');
		$this->db->where_in('id', $ids_array);
		$query = $this->db->get();
		// echo $this->db->last_Query();
		// die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function B2BOrderItemsDetails($B2B_item_order_id)
	{

		// print_r($hidden_order_ids);die;
		// $hidden_order_ids_array = explode(',', $hidden_order_ids);
		// $hidden_order_ids_array = array_map('trim', $hidden_order_ids);


		$this->db->select('*');
		$this->db->from('b2b_order_items');
		$this->db->where_in('order_id', $B2B_item_order_id);

		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function OrderItemsData($webshop_order_id)
	{

		// print_r($order_id);die;
		$this->db->select('*');
		$this->db->from('sales_order_items');
		$this->db->where_in('order_id', $webshop_order_id);
		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}

	public function OrderAddressData($webshop_order_id)
	{

		// print_r($order_id);die;

		$this->db->select('*');
		$this->db->from('sales_order_address');
		$this->db->where_in('order_id', $webshop_order_id);
		$this->db->where('address_type', '2');

		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function getCategory($product_id)
	{
		// Using the query builder for selecting the category for a given product ID
		$this->db->select('*');
		$this->db->from('products_category');
		$this->db->where('product_id', $product_id);
		$this->db->where('category_ids', '8');

		$query = $this->db->get();
	// echo $this->db->last_Query();die;
		// Check if any rows are returned
		if ($query->num_rows() > 0) {
			return $query->row_array(); // Return the row as an array
		} else {
			return false; // No data found
		}
	}

	public function getCategoriesDetails($product_id)
	{
		$this->db->select('*');
		$this->db->from('products_category');
		$this->db->where('product_id', $product_id);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result_array(); // ✅ Returns all rows as an array of arrays
		} else {
			return false;
		}
	}

	
	public function updatePublisherPaymentData($where_shipment_arr, $utr_no, $User_id)
	{
		// $id = $data['id'];
		// print_r($id);
		// die;
		// $this->db->where('id', $id);
		// $this->db->update('publisher_payment', $data);

		$data	=  array('payment_done' => 1, 'utr_no' => $utr_no, 'payment_done_at' => time(), 'updated_at' => time(), 'updated_by' => $User_id);

		$this->db->where_in('id', $where_shipment_arr);

		$this->db->update('publisher_payment', $data);

		// echo $this->db->last_query();
		// die;
		// Check for success based on affected rows
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
	public function FinalPaymentDetails($B2B_item_order_id, $webshop_order_id, $hidden_order_ids)
	{
		$hidden_order_ids_array = array_map('trim', $hidden_order_ids);

		$this->db->select('bo.*,so.customer_email,so.created_at, soi.product_id as sale_order_product_id, soi.sub_issues, soi.price, soi.pub_com_percent, soi.gift_name, boi.product_id, boi.product_id,boi.qty_ordered,boi.product_variants,boi.product_name,soa.first_name,soa.last_name,soa.address_line1,soa.address_line2,soa.pincode,soa.city,soa.state,soa.mobile_no');
		$this->db->from('b2b_orders as bo ');
		$this->db->join('sales_order as so', 'so.order_id = bo.webshop_order_id', 'left');
		$this->db->join('sales_order_items as soi', 'soi.order_id = bo.webshop_order_id', 'left');
		$this->db->join('b2b_order_items as boi', 'boi.order_id = bo.order_id', 'left');
		$this->db->join('sales_order_address as soa', 'soa.order_id = soi.order_id', 'left');
		$this->db->where_in('boi.order_id', $B2B_item_order_id);
		$this->db->where_in('soi.order_id', $webshop_order_id);
		$this->db->where_in('bo.webshop_order_id', $hidden_order_ids_array);
		$this->db->where('soa.address_type', '2');


		$query = $this->db->get();
		// echo $this->db->last_Query();die;
		$resultArr = $query->result_array();
		return $resultArr;
	}
	public function order_payment_detail_status($order_payment_detail_id)
	{
		// Initialize the status variable
		$status = '';

		$this->db->where('order_id', $order_payment_detail_id);
		$query = $this->db->get('publisher_payment');
		
		if ($query->num_rows() > 0) {
			$result = $query->result();
			// print_r($result);die;

			foreach ($result as $res) {
				$resArray = json_decode(json_encode($res), true);

				if (isset($resArray['payment_initiated']) && $resArray['payment_initiated'] == 1 && $resArray['payment_done'] == 1) {
					$status = '<span style="color: green;">(Payment Done)</span>';
				} elseif (isset($resArray['payment_done']) && $resArray['payment_done'] == 2 && $resArray['payment_initiated'] == 1) {
					$status = '<span style="color: red;">(Payment Initiated)</span>';
				}
			}
		}

		return $status;
	}
}
