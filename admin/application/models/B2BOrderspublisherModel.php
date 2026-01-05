<?php

class B2BOrderspublisherModel extends CI_Model

{

	public function __construct()

	{

		parent::__construct();

		$fbc_user_id	=	$this->session->userdata('ShopOwnerId');  //old LoginID

		$shop_id		=	$this->session->userdata('ShopID');


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





	function get_datatables_orders($order_status, $fromDate, $toDate)
	{

		$term = (isset($_REQUEST['search']['value']) && $_REQUEST['search']['value'] != '') ? $_REQUEST['search']['value'] : '';

		$this->_get_datatables_query_orders($term, $order_status, $fromDate, $toDate);

		if ($_REQUEST['length'] != -1)

			$this->db->limit($_REQUEST['length'], $_REQUEST['start']);

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->result();
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







	public function _get_datatables_query_orders($term, $order_status,  $fromDate, $toDate)
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



			$this->db->where('o.created_at >=', strtotime($fromDate));
		} else if (!empty($toDate) && empty($fromDate)) {



			$this->db->where('o.created_at <=', strtotime($toDate));
		} else if (!empty($toDate) && !empty($fromDate)) {

			$this->db->where('o.created_at >=', strtotime($fromDate));

			$this->db->where('o.created_at <=', strtotime($toDate));
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





	public function count_all_orders( $order_status, $fromDate, $toDate)
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



	function count_filtered_orders($order_status,  $fromDate, $toDate)
	{

		$term = $_REQUEST['search']['value'];

		$this->_get_datatables_query_orders($term, $order_status, $fromDate, $toDate);

		$query = $this->db->get();

		return $query->num_rows();
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

		$webshop_smtp_host = 'smtp.gmail.com'; 
		$webshop_smtp_port =  465; // $this->getCustomVariableByIdentifier('smtp_port') ??
		$webshop_smtp_username = 'care@indiamags.com'; //$this->getCustomVariableByIdentifier('smtp_username') ??
		$webshop_smtp_password = 'Snail@2024'; //$this->getCustomVariableByIdentifier('smtp_password') ??
		$webshop_smtp_secure = $this->getCustomVariableByIdentifier('smtp_secure');

		$GlobalVar = $this->getCustomVariableByIdentifier('admin_email');

		if (isset($GlobalVar) && $GlobalVar->value != '') {

			$from_email = $GlobalVar->value ?? 'jyotipnd52@gmail.com';
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
				$cc = 'jyotipnd52@gmail.com';
			

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
				$from_email = 'suzan@indiamags.com';
				$cc = 'jyotipnd52@gmail.com';
				
			}

			if ($identifier == 'hbr_request_a_payment_for_order') {
				if (isset($identifier) && $identifier != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic[1], $subject);
				}
				$emailBody = str_replace('##ORDERID##', $DynamicVars[1], $emailTemplate->content);
				$emailBody = str_replace('##PAYMENTINFOTABLE##', $DynamicVars[0], $emailBody);
				$emailBody = str_replace('##WEBSHOPNAME##', $DynamicVars[2], $emailBody);
				$cc = 'jyotipnd52@gmail.com';
				

				$from_email = 'suzan@indiamags.com';
			}
			if ($identifier == 'hbr_payment_done_for_order') {
				if (isset($identifier) && $identifier != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic[1], $subject);
					$subject = str_replace('##PUBLISHERNAME##', $SubDynamic[0], $subject);
				}
				// Ensure the DynamicVars array has at least 4 elements before accessing them
			if (isset($DynamicVars[2])) {
				$emailBody = str_replace('##ORDERID##', $DynamicVars[2], $emailTemplate->content);
			} else {
				$emailBody = str_replace('##ORDERID##', 'N/A', $emailTemplate->content); // or handle it appropriately
			}

			if (isset($DynamicVars[0])) {
				$emailBody = str_replace('##PAYMENTINFOTABLE##', $DynamicVars[0], $emailBody);
			} else {
				$emailBody = str_replace('##PAYMENTINFOTABLE##', 'No payment info available', $emailBody);
			}

			if (isset($DynamicVars[1])) {
				$emailBody = str_replace('##PUBLISHERORDERTABLE##', $DynamicVars[1], $emailBody);
			} else {
				$emailBody = str_replace('##PUBLISHERORDERTABLE##', 'No order details available', $emailBody);
			}

			if (isset($DynamicVars[3])) {
				$emailBody = str_replace('##WEBSHOPNAME##', $DynamicVars[3], $emailBody);
			} else {
				$emailBody = str_replace('##WEBSHOPNAME##', 'Unknown webshop', $emailBody);
			}

				$to_email_array_merge = array_merge(['jyotipnd52@gmail.com'], ['jyotipnd52@gmail.com'], [$cc]);



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

	
	
}
