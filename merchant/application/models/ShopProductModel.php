<?php

/**

 * Dec 2020

 */

class ShopProductModel extends CI_Model

{



	private $fbc_user_id = NULL;

	private $shop_id = NULL;



	function init($args)

	{



		if (empty($args['fbc_user_id']) ||  empty($args['shop_id'])) {

			redirect(base_url());
		}



		$fbc_user_id	=	$args['fbc_user_id'];

		$shop_id		=	$args['shop_id'];



		$this->fbc_user_id = $fbc_user_id;

		$this->shop_id = $shop_id;





		/*$FBCData = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $shop_id), 'shop_id,fbc_user_id,database_name');





		if (isset($FBCData) && $FBCData->database_name != '') {

			$fbc_user_database = $FBCData->database_name;



			$this->load->database();

			$config_app = fbc_switch_db_dynamic(DB_PREFIX . $fbc_user_database);

			$this->db = $this->load->database($config_app, TRUE);

			if ($this->db->conn_id) {

				//do something

			} else {

				redirect(base_url());
			}
		} else {

			redirect(base_url());
		}*/
	}







	function __construct()

	{

		parent::__construct();

		/*

		$fbc_user_id=$this->fbc_user_id;

		$shop_id=$this->shop_id;





		$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('fbc_user_id'=>$fbc_user_id),'shop_id,fbc_user_id,database_name');

		if(isset($FBCData) && $FBCData->database_name!='')

		{

			$fbc_user_database=$FBCData->database_name;



			$this->load->database();

			$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);

			$this->db = $this->load->database($config_app,TRUE);

			if($this->db->conn_id) {

				//do something

			} else {

				redirect(base_url());

			}

		}else{

			redirect(base_url());

		}

		*/
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
		// echo $this->db->last_query();
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



	function get_datatables_products($price, $inventory, $supplier, $fromDate, $toDate)
	{

		$term = $_REQUEST['search']['value'];

		$this->_get_datatables_query_products($term, $price, $inventory, $supplier, $fromDate, $toDate);

		if ($_REQUEST['length'] != -1)

			$this->db->limit($_REQUEST['length'], $_REQUEST['start']);

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->result();
	}



	public function _get_datatables_query_products($term, $price, $inventory, $supplier, $fromDate, $toDate)
	{



		$main_db_name = $this->db->database;



		$column = array('p.name', 'cg.cat_name', 'p.shop_id', 'pi.qty', 'p.price', 'p.updated_at', '');

		$this->db->distinct();

		$this->db->select('p.*,pc.category_ids,pi.qty,cg.cat_name,IF(p.shop_id >0, fus.org_shop_name,"-") as  org_shop_name');

		$this->db->from('products as p');

		$this->db->join('products_inventory as pi', 'p.id = pi.product_id', 'LEFT');

		$this->db->join('products_category as pc', 'p.id = pc.product_id AND pc.level=1', 'LEFT');

		$this->db->join($main_db_name . '.category as cg', 'cg.id = pc.category_ids AND cg.cat_level=1', 'LEFT');

		$this->db->join($main_db_name . '.fbc_users_shop as fus', 'p.fbc_user_id = fus.fbc_user_id', 'LEFT');

		$this->db->where('p.product_type <>', 'conf-simple');

		$this->db->where('p.product_inv_type <>', 'dropship');

		$this->db->where('p.remove_flag', '0');



		if ($term != '') {



			$this->db->where(" (

			p.name LIKE '%$term%'

			OR p.description LIKE '%$term%'

			OR p.product_code LIKE '%$term%'

			OR p.sku LIKE '%$term%'

			OR cg.cat_name LIKE '%$term%'

			 )");
		}



		if (!empty($price)) {



			$this->db->where('p.price >=', 0);

			$this->db->where('p.price <=', $price);
		}



		if (!empty($inventory)) {

			$this->db->where('pi.qty >=', 0);

			$this->db->where('pi.qty <=', $inventory);
		}



		if (!empty($supplier)) {

			$selected_supplier = array();

			if (strpos($supplier, ',') !== false) {

				$selected_supplier = explode(',', $supplier);
			} else {

				$selected_supplier[] = $supplier;
			}



			if (count($selected_supplier) == 1) {

				if ($selected_supplier[0] == 'Self') {

					$fbc_user_id	=	$this->session->userdata('LoginID');

					$this->db->where('p.shop_id', 0);
				} else if ($selected_supplier[0] == 'B2B') {

					$this->db->where('p.shop_id >', 0);
				}
			} else {

				$this->db->where('p.shop_id', 0);

				$this->db->where('p.shop_id >', 0);
			}
		}



		if (!empty($fromDate) && empty($toDate)) {



			$this->db->where('p.updated_at >=', strtotime($fromDate));
		} else if (!empty($toDate) && empty($fromDate)) {



			$this->db->where('p.updated_at <=', strtotime($toDate));
		} else if (!empty($toDate) && !empty($fromDate)) {

			$this->db->where('p.updated_at >=', strtotime($fromDate));

			$this->db->where('p.updated_at <=', strtotime($toDate));
		}



		if (isset($_REQUEST['order'])) // here order processing

		{

			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		} else if (isset($this->order)) {

			$order = $this->order;

			$this->db->order_by(key($order), $order[key($order)]);
		} else {

			$this->db->order_by('p.id', 'desc');
		}
	}





	public function count_all_products($price, $inventory, $supplier, $fromDate, $toDate)
	{

		$main_db_name = $this->db->database;

		$column = array('p.name', 'pc.category_ids', 'p.shop_id', 'pi.qty', 'p.price', 'p.updated_at', '');

		$term = $_REQUEST['search']['value'];

		$this->db->distinct();

		$this->db->select('p.*,pc.category_ids,pi.qty,cg.cat_name,IF(p.shop_id >0, fus.org_shop_name,"-") as  org_shop_name');

		$this->db->from('products as p');

		$this->db->join('products_inventory as pi', 'p.id = pi.product_id', 'LEFT');

		$this->db->join('products_category as pc', 'p.id = pc.product_id AND pc.level=1', 'LEFT');

		$this->db->join($main_db_name . '.category as cg', 'cg.id = pc.category_ids AND cg.cat_level=1', 'LEFT');

		$this->db->join($main_db_name . '.fbc_users_shop as fus', 'p.fbc_user_id = fus.fbc_user_id', 'LEFT');

		$this->db->where('p.product_type <>', 'conf-simple');

		$this->db->where('p.product_inv_type <>', 'dropship');

		$this->db->where('p.remove_flag', '0');



		if ($term != '') {



			$this->db->where(" (

			p.name LIKE '%$term%'

			OR p.description LIKE '%$term%'

			OR p.product_code LIKE '%$term%'

			OR p.sku LIKE '%$term%'

			OR cg.cat_name LIKE '%$term%'

			 )");
		}



		if (!empty($price)) {



			$this->db->where('p.price >=', 0);

			$this->db->where('p.price <=', $price);
		}



		if (!empty($inventory)) {

			$this->db->where('pi.qty >=', 0);

			$this->db->where('pi.qty <=', $inventory);
		}









		if (!empty($supplier)) {

			if (strpos($supplier, ',') !== false) {

				$selected_supplier = explode(',', $supplier);
			} else {

				$selected_supplier[] = $supplier;
			}



			if (count($selected_supplier) == 1) {

				if ($selected_supplier[0] == 'Self') {

					$fbc_user_id	=	$this->session->userdata('LoginID');

					$this->db->where('p.shop_id', 0);
				} else if ($selected_supplier[0] == 'B2B') {

					$this->db->where('p.shop_id >', 0);
				}
			} else {

				$this->db->where('p.shop_id', 0);

				$this->db->where('p.shop_id >', 0);
			}
		}



		if (!empty($fromDate) && empty($toDate)) {



			$this->db->where('p.updated_at >=', strtotime($fromDate));
		} else if (!empty($toDate) && empty($fromDate)) {



			$this->db->where('p.updated_at <=', strtotime($toDate));
		} else if (!empty($toDate) && !empty($fromDate)) {

			$this->db->where('p.updated_at >=', strtotime($fromDate));

			$this->db->where('p.updated_at <=', strtotime($toDate));
		}



		if (isset($_REQUEST['order'])) // here order processing

		{

			$this->db->order_by($column[$_REQUEST['order']['0']['column']], $_REQUEST['order']['0']['dir']);
		} else if (isset($this->order)) {

			$order = $this->order;

			$this->db->order_by(key($order), $order[key($order)]);
		} else {

			$this->db->order_by('p.id', 'desc');
		}

		return $this->db->count_all_results();
	}



	function count_filtered_products($price, $inventory, $supplier, $fromDate, $toDate)
	{

		$term = $_REQUEST['search']['value'];

		$this->_get_datatables_query_products($term, $price, $inventory, $supplier, $fromDate, $toDate);

		$query = $this->db->get();

		return $query->num_rows();
	}



	function getStockForConfigProduct($product_id)

	{

		$Row = $this->getVariantProducts($product_id);





		if (isset($Row) && $Row->product_ids != '') {

			$product_ids = $Row->product_ids;

			$sql = "SELECT sum(qty) as qty from products_inventory where product_id IN ($product_ids) ";

			$query = $this->db->query($sql);

			return $query->row();
		} else {

			return false;
		}
	}



	function getVariantProducts($product_id)
	{

		$sql = "SELECT GROUP_CONCAT(id) as product_ids FROM `products` where parent_id = $product_id ";

		$query = $this->db->query($sql);

		return $query->row();
	}





	function getVariantMasterForProducts($product_id)

	{

		$main_db_name = $this->db->database;

		$this->db->select('pvm.*,ma.attr_name');

		$this->db->from('products_variants_master as pvm');

		$this->db->join($main_db_name . '.eav_attributes as ma', 'pvm.attr_id = ma.id AND ma.attr_type=2', 'LEFT');

		$this->db->where('pvm.product_id', $product_id);

		$query = $this->db->get();

		$resultArr = $query->result_array();



		return $resultArr;
	}



	function getVariantProductsByIds($product_ids)

	{



		$this->db->select('p.*,pi.qty,pi.available_qty');

		$this->db->from('products as p');

		$this->db->join('products_inventory as pi', 'p.id = pi.product_id', 'LEFT');

		$this->db->where_in('p.id', $product_ids);

		$this->db->where('p.remove_flag', '0');

		$query = $this->db->get();

		$resultArr = $query->result_array();

		//echo $this->db->last_query();exit;

		return $resultArr;
	}



	public function productslugcount($name, $product_id = '')
	{

		$count = '';

		$this->db->select('count(*) as slugcount');

		$this->db->from('products');

		if (isset($product_id) && $product_id > 0) {

			$this->db->where('id <>', $product_id);
		}

		$this->db->where('parent_id', 0);

		$this->db->where('name', $name);

		$query = $this->db->get();

		$count = $query->row(0)->slugcount;

		if (isset($product_id) && $product_id > 0) {

			$count = $count + 1;
		}

		return $count;
	}



	function deleteDataById($tablename, $where)
	{



		$this->db->delete($tablename, $where);

		$this->db->reset_query();
	}



	public function getSellerProductCount()
	{

		$count = '';

		$this->db->select('count(*) as count');

		$this->db->from('products');

		$this->db->where('product_type <>', 'conf-simple');

		$this->db->where('status <>', '2');   // deleted

		$this->db->where('parent_id', 0);

		$this->db->where('remove_flag', '0');

		$query = $this->db->get();

		$count = $query->row(0)->count;



		return $count;
	}



	function getMainOrderItemQty($order_id, $product_id)

	{

		$qty_ordered = '';

		$this->db->select("oi.qty_ordered");

		$this->db->from("b2b_order_items as oi");

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



	function getSplitChildOrderIds($main_parent_id)
	{

		$this->db->select("order_id,increment_id,status");

		$this->db->from('b2b_orders');

		$this->db->where('main_parent_id', $main_parent_id);

		$this->db->order_by('order_id', 'asc');

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->result();
	}



	function getOrderItemsForWebShopB2B($order_id, $is_split = '')

	{

		$this->db->select("oi.*,o.increment_id,o.shop_id");

		$this->db->from('b2b_order_items as oi');

		$this->db->join('b2b_orders as o', 'oi.order_id = o.order_id', 'LEFT');

		if (isset($is_split) && $is_split == 1) {

			$this->db->where('o.main_parent_id', $order_id);
		} else {

			$this->db->where('o.order_id', $order_id);
		}



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



	function getSplitChildOrderIdsForWebshop($main_parent_id)
	{

		$this->db->select("order_id,increment_id,status");

		$this->db->from('sales_order');

		$this->db->where('main_parent_id', $main_parent_id);

		$this->db->order_by('order_id', 'asc');

		$query = $this->db->get();

		//echo $this->db->last_query();exit;

		return $query->result();
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





	/*public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars,$SubDynamic='',$CommonVars = ''){



		$GlobalVar=$this->getCustomVariableByIdentifier('admin_email');

		if(isset($GlobalVar) && $GlobalVar->value!=''){

			$from_email=$GlobalVar->value;

		}else{

			$shop_id		=	$this->session->userdata('ShopID');

			$FBCData=$this->CommonModel->getShopOwnerData($shop_id);

			$from_email=$FBCData->email;

		}



		$emailTemplate = $this->getEmailTemplateByIdentifier($identifier);

		if(isset($emailTemplate) && $emailTemplate->id!='')

		{



			$emailHeaderTemplate = $this->getEmailTemplateByIdentifier('email-header');

			$emailFooterTemplate = $this->getEmailTemplateByIdentifier('email-footer');



			$HeaderPart=$emailHeaderTemplate->content;

			$FooterPart=$emailFooterTemplate->content;

			if(isset($CommonVars) && $CommonVars!='')

			{

				$HeaderPart = str_replace('##SITELOGO##', $CommonVars[0], $HeaderPart);

				$FooterPart = str_replace('##WEBSHOPNAME##', $CommonVars[1], $FooterPart);

			}



			$templateId=$emailTemplate->id;



			$subject = $emailTemplate->subject;

			$title = $emailTemplate->title;



			if($templateId==4 || $templateId==6){

				if(isset($SubDynamic) && $SubDynamic!=''){

					$subject = str_replace('##ORDERID##', $SubDynamic, $subject);

				}else{

					$subject = str_replace('##ORDERID##', '', $subject);

				}

			}



			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);






			$FinalContentBody=$HeaderPart.$emailBody.$FooterPart;



			if($this->CommonModel->sendHTMLMailSMTP($EmailTo, $subject, $FinalContentBody, $from_email, $attachment=""))

			{

				return true;

			}else{



				return false;

			}



		}

	}*/

	public function sendCommonHTMLEmail($EmailTo, $identifier, $TempVars, $DynamicVars, $SubDynamic = '', $CommonVars = '')
	{

		$webshop_smtp_host = $this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port = $this->getCustomVariableByIdentifier('smtp_port');
		$webshop_smtp_username = $this->getCustomVariableByIdentifier('smtp_username');
		$webshop_smtp_password = $this->getCustomVariableByIdentifier('smtp_password');
		$webshop_smtp_secure = $this->getCustomVariableByIdentifier('smtp_secure');

		//print_r($DynamicVars);exit();
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

			if ($templateId == 4 || $templateId == 6) {
				if (isset($SubDynamic) && $SubDynamic != '') {
					$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
				} else {
					$subject = str_replace('##ORDERID##', '', $subject);
				}
			}

			if ($identifier == 'fbcuser-b2b-dropship-order-tracking-details') {
				$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
				$subject = str_replace('##B2BORDERID##', $DynamicVars[2], $subject);
			}
			// new
			if ($identifier == 'fbcuser-order-tracking-completed') {
				$subject = str_replace('##ORDERID##', $SubDynamic, $subject);
			}

			$emailBody = str_replace($TempVars, $DynamicVars, $emailTemplate->content);
			/*
			$data['title'] = $title;
			$data['subject'] = $subject;
			$data['content'] = $emailBody;

			$content = $this->load->view('email_template/email_content', $data, TRUE);
			*/

			$FinalContentBody = $HeaderPart . $emailBody . $FooterPart;
			if ($this->CommonModel->sendHTMLMailSMTP($EmailTo, $subject, $FinalContentBody, $from_email, $attachment = "", $webshop_smtp_host->value, $webshop_smtp_port->value, $webshop_smtp_username->value, $webshop_smtp_password->value, $webshop_smtp_secure->value)) {
				return true;
			} else {

				return false;
			}
		}
	}

	// webshop email send
	public function sendCommonHTMLEmailWebshop($shop_id, $EmailTo, $identifier, $TempVars, $DynamicVars, $CommonVars, $attachment)
	{
		// public function sendCommonHTMLEmailWebshop($shop_id,$EmailTo, $identifier, $TempVars, $DynamicVars,$SubDynamic='',$CommonVars = ''){

		$webshop_smtp_host = $this->getCustomVariableByIdentifier('smtp_host');
		$webshop_smtp_port = $this->getCustomVariableByIdentifier('smtp_port');
		$webshop_smtp_username = $this->getCustomVariableByIdentifier('smtp_username');
		$webshop_smtp_password = $this->getCustomVariableByIdentifier('smtp_password');
		$webshop_smtp_secure = $this->getCustomVariableByIdentifier('smtp_secure');

		$GlobalVar = $this->getCustomVariableByIdentifier('admin_email');
		if (isset($GlobalVar) && $GlobalVar->value != '') {
			$from_email = $GlobalVar->value;
		} else {
			$shop_id		=	$shop_id;
			// $shop_id		=	$this->session->userdata('ShopID');
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
			//print_r($attachment);exit();
			if ($this->CommonModel->sendHTMLMailSMTPAttchment($EmailTo, $subject, $FinalContentBody, $from_email, $attachment, $webshop_smtp_host->value, $webshop_smtp_port->value, $webshop_smtp_username->value, $webshop_smtp_password->value, $webshop_smtp_secure->value)) {
				$this->email->clear(TRUE);
				return true;
			} else {

				return false;
			}
		}
	}

	function getVariantDetailsForProducts($shop_id, $product_id)
	{



		$main_db_name = $this->db->database;

		// $this->db->select('pvm.*,ma.attr_name');

		// $this->db->from('products_variants_master as pvm');

		// $this->db->join($main_db_name.'.eav_attributes as ma','pvm.attr_id = ma.id AND ma.attr_type=2','LEFT');

		// $this->db->where('pvm.product_id',$product_id);

		$this->db->select('pv.*,ma.attr_name,mao.attr_options_name');

		$this->db->from('products_variants as pv');

		$this->db->join($main_db_name . '.eav_attributes as ma', 'pv.attr_id = ma.id AND ma.attr_type=2', 'LEFT');

		$this->db->join($main_db_name . '.eav_attributes_options as mao', 'mao.id = pv.attr_value AND ma.attr_type=2', 'LEFT');

		$this->db->where('pv.product_id', $product_id);

		$query = $this->db->get();

		//print_r($this->db->last_query());

		$resultArr = $query->result();



		return $resultArr;
	}

	// new invoice
	function getOrderDatabyOrderIdsForWebshop($order_id)
	{
		$this->db->select("*");
		$this->db->from('sales_order');
		$this->db->where('order_id', $order_id);
		$query = $this->db->get();
		return $query->row();
	}

	// new invoice
	// b2b invoice generate
	// invoice
	function get_webshop_invoicing_b2b_generate_data($order_id)
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

		$this->db->where('o.order_id', $order_id);
		/*$this->db->where('o.parent_id','0');
			$this->db->where('o.main_parent_id','0'); */
		$query = $this->db->get();
		return $query->row();
	}

	// invoice
	function get_webshop_invoicing_b2b_generate_parent_id_data($order_id, $parent_id, $b2b_order_id)
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
		//$this->db->join($main_db_name.'.b2b_orders as b2b','b2b.webshop_order_id = o.order_id AND b2b.parent_id=$parent_id','LEFT');

		$this->db->where('o.order_id', $order_id);
		// $this->db->where('b2b.order_id',$b2b_order_id);
		/*$this->db->where('o.parent_id','0');
			$this->db->where('o.main_parent_id','0'); */
		$query = $this->db->get();
		return $query->row();
	}

	// invoice create new invoice
	public function get_invoicedata_by_id($invoice_id)
	{
		$this->db->select("*");
		$this->db->from('invoicing');
		$this->db->where('id', $invoice_id);
		$query = $this->db->get();
		return $query->row();
	}

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

	//
	// order items data
	function getOrder_multi_Items($order_id)
	{
		/*$this->db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi','oi.product_id = pi.product_id','LEFT');
		$this->db->join('sales_orders as o','oi.order_id = o.order_id','LEFT');
		$this->db->where_in('oi.order_id',$order_id);
		$this->db->order_by('oi.qty_scanned','asc');*/

		$this->db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type,o.checkout_method, o.invoice_self, o.coupon_code as order_coupon_code,sop.payment_method,sop.payment_method_name");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');
		$this->db->join('sales_order as o', 'oi.order_id = o.order_id', 'LEFT');
		$this->db->join('sales_order_payment as sop', 'oi.order_id = sop.order_id', 'LEFT');
		$this->db->where("oi.product_inv_type IN ('dropship')");  //adde by al later
		//$this->db->where("pi.qty>0");								  //adde by al later
		// $this->db->where('oi.order_id',$order_id);
		$this->db->where_in('oi.order_id', $order_id);
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		// echo $this->db->last_query();exit;
		return $query->result();
	}

	// new query shipping charge, shipping tax, shipping amount, payment charge,
	function getOrder_multi_Items_new($order_id)
	{
		/*$this->db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi','oi.product_id = pi.product_id','LEFT');
		$this->db->join('sales_orders as o','oi.order_id = o.order_id','LEFT');
		$this->db->where_in('oi.order_id',$order_id);
		$this->db->order_by('oi.qty_scanned','asc');*/

		$this->db->select("oi.*,pi.qty,o.increment_id,o.discount_percent as order_discount_percent,o.shipment_type as order_shipment_type,o.checkout_method, o.invoice_self, o.coupon_code as order_coupon_code,sop.payment_method,sop.payment_method_name,o.payment_charge,o.payment_tax_percent,o.payment_tax_amount,o.payment_final_charge,o.shipping_amount,o.shipping_tax_amount,o.shipping_charge,o.shipping_tax_percent,o.total_qty_ordered");
		$this->db->from('sales_order_items as oi');
		$this->db->join('products_inventory as pi', 'oi.product_id = pi.product_id', 'LEFT');
		$this->db->join('sales_order as o', 'oi.order_id = o.order_id', 'LEFT');
		$this->db->join('sales_order_payment as sop', 'oi.order_id = sop.order_id', 'LEFT');
		$this->db->where("oi.product_inv_type IN ('dropship')");  //adde by al later
		//$this->db->where("pi.qty>0");								  //adde by al later
		// $this->db->where('oi.order_id',$order_id);
		$this->db->where_in('oi.order_id', $order_id);
		$this->db->order_by('oi.qty_scanned', 'asc');
		$query = $this->db->get();
		// echo $this->db->last_query();exit;
		return $query->result();
	}

	// new
	public function getSingleShopDataByID($tableName, $condition, $select)
	{
		if (!empty($select)) {
			$this->db->select($select);
		}
		$this->db->where($condition);
		$query = $this->db->get($tableName);
		return $query->row();
	}
	// new
	public function get_custom_variables()
	{
		$query = $this->db->get('custom_variables');
		// echo $this->db->last_query();
		return $query->result_array();
	}

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

	//page access

	// access invoice
	function page_access_shop()
	{
		$fbc_user_id = $this->fbc_user_id;
		$FBCData = $this->CommonModel->getSingleDataByID('fbc_users', array('fbc_user_id' => $fbc_user_id), 'email,shop_id');
		$FBCShopData = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $FBCData->shop_id), '');
		//$access_data['fbcshopdata_access']=$FBCShopData;
		return $FBCShopData;
	}

	// product category invoice
	function getProductsMaintCategoryNames($product_id)
	{
		$catgory_name = '-';
		$main_db_name = $this->db->database;
		$sql = "SELECT GROUP_CONCAT(c.cat_name separator ',') as cat_name FROM `products_category` as pc LEFT JOIN $main_db_name.category as c ON pc.category_ids = c.id  where pc.product_id = $product_id  and level = 0";
		// $sql = "SELECT GROUP_CONCAT(c.cat_name separator '>>') as cat_name FROM `products_category` as pc LEFT JOIN $main_db_name.category as c ON pc.category_ids = c.id  where pc.product_id = $product_id ";
		$query = $this->db->query($sql);
		$Row = $query->row();

		if (isset($Row) && $Row->cat_name != '') {
			$catgory_name = $Row->cat_name;
		}

		return $catgory_name;
	}
}
