<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class ReportModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getReportList($search_param = '')
	{
		$this->db->select('ctm.name as customer_login_type, so.customer_id, so.order_id,so.coupon_code,so.status, so.voucher_code, so.cancel_by_customer, so.created_at, so.increment_id, so.customer_firstname, so.customer_lastname, so.customer_email, so.voucher_amount,');
		$this->db->from('sales_order so');
		$this->db->join('customers c', 'c.id = so.customer_id', 'left');
		$this->db->join('customers_type_master ctm', 'ctm.id = c.customer_type_id', 'left');
		$this->db->where('((so.status <> 7) AND ((so.status <> 3) OR so.cancel_by_customer != 0))');
		$this->db->where('so.parent_id', 0);
		$this->db->where('so.voucher_code is not null');
		$this->db->where('so.voucher_code !=" "');

		if (isset($search_param['keyword']) && $search_param['keyword'] != "") {
			$this->db->group_start();
			$this->db->like('so.customer_firstname', $search_param['keyword']);
			$this->db->or_like('so.customer_lastname', $search_param['keyword']);
			if (isset($search_param['keyword'])) {
				$fullname = explode(" ", $search_param['keyword']);
				$fname = $fullname[0];
				$this->db->or_like('so.customer_firstname', $fname);
				if (isset($fullname[1])) {
					$lname = $fullname[1];
					$this->db->or_like('so.customer_lastname', $lname);
				}
			}
			$this->db->or_like('so.increment_id', $search_param['keyword']);
			$this->db->or_like('so.voucher_code', $search_param['keyword']);
			$this->db->or_like('so.customer_email', $search_param['keyword']);
			$this->db->or_like('so.voucher_amount', $search_param['keyword']);
			$this->db->or_like('ctm.name', $search_param['keyword']);
			$this->db->group_end();
		}

		$this->db->order_by('so.created_at', 'DESC');
		$result = $this->db->get();
		// echo $this->db->last_query();
		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return false;
		}
	}

	public function getCustomerCSVImport($fromdate, $todate)
	{
		$this->db->select('c.*,c.id,c.first_name,c.last_name,c.email_id,c.mobile_no,c.gender,c.country_code,c.dob,c.company_name,c.gst_no,c.access_prelanch_product,c.allow_catlog_builder,c.created_at,c.customer_type_id, ctm.name as customer_type_name');
		$this->db->from('customers c');
		$this->db->join('customers_type_master ctm', 'ctm.id = c.customer_type_id', 'LEFT');
		$this->db->where('c.created_at >=', $fromdate);
		$this->db->where('c.created_at <=', $todate);
		$this->db->where('c.status', 1);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}

	public function getPublisherCSVImport($fromdate, $todate)
	{
		$this->db->select('id,email,publication_name,vendor_name,commision_percent,phone_no,description,created_at');
		$this->db->where('created_at >=', $fromdate);
		$this->db->where('created_at <=', $todate);
		$this->db->where('status', 1);
		$this->db->where('remove_flag', 0);
		$query = $this->db->get('publisher');
		$result = $query->result_array();
		// print_r($query->result_array());
		// exit;
		return $result;
	}

	public function getAddress($customer_id)
	{
		$this->db->select('cus_add.first_name as address_first_name,cus_add.last_name as address_last_name,cus_add.mobile_no as address_mobile_no,cus_add.address_line1 as address_address_line1,cus_add.address_line2 as address_address_line2,cus_add.city as address_city,cus_add.state as address_state,cus_add.country as address_country,cus_add.pincode as address_pincode,cus_add.company_name as address_company_name,cus_add.vat_no as address_vat_no,cus_add.consulation_no as address_consulation_no,cus_add.res_company_name as address_res_company_name,cus_add.res_company_address as address_res_company_address,cus_add.is_default,cus_add.vat_vies_valid_flag as address_vat_vies_valid_flag');

		$this->db->from('customers_address cus_add');
		$this->db->where('cus_add.remove_flag', 0);
		$this->db->order_by('cus_add.is_default', 'DESC');
		$this->db->where('cus_add.customer_id', $customer_id);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}

	public function getReportCSVImport($fromdate, $todate)
	{
		$this->db->select(' ctm.name as customer_login_type, so.customer_id, so.order_id,so.coupon_code,so.status, so.voucher_code, so.cancel_by_customer, so.created_at, so.increment_id, so.customer_firstname, so.customer_lastname, so.customer_email, so.voucher_amount,');
		$this->db->from('sales_order so');
		$this->db->join('customers c', 'c.id = so.customer_id', 'left');
		$this->db->join('customers_type_master ctm', 'ctm.id = c.customer_type_id', 'left');
		$this->db->where('so.created_at >=', $fromdate);
		$this->db->where('so.created_at <=', $todate);
		$this->db->where(' ( (so.status <> 7) AND ((so.status <> 3) OR so.cancel_by_customer != 0)) ');
		$this->db->where('so.parent_id', 0);
		$this->db->where('so.voucher_code is not null');
		$this->db->where('so.voucher_code !=" "');

		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}

	public function getCatalogueDiscountList()
	{
		$this->db->select('sr.*, src.coupon_id, src.coupon_code');
		$this->db->from('salesrule as sr');
		$this->db->join('salesrule_coupon as src', 'src.rule_id = sr.rule_id', 'INNER');
		$this->db->where(array('sr.remove_flag' => 0));
		$this->db->order_by('sr.start_date', 'DESC');
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	public function getDiscountList($search_param = '')
	{
		$this->db->select(' ctm.name as customer_login_type, so.customer_id, so.order_id,so.coupon_code,so.status, so.voucher_code, so.cancel_by_customer, so.created_at, so.increment_id, so.customer_firstname, so.customer_lastname, so.customer_email, so.voucher_amount, so.discount_amount,');
		$this->db->from('sales_order so');
		$this->db->join('customers c', 'c.id = so.customer_id', 'left');
		$this->db->join('customers_type_master ctm', 'ctm.id = c.customer_type_id', 'left');
		$this->db->where('((so.status <> 7) AND ((so.status <> 3) OR so.cancel_by_customer != 0))');
		$this->db->where('so.parent_id', 0);
		$this->db->where('so.coupon_code is not null');
		$this->db->where('so.coupon_code !=" "');

		if (isset($search_param['keyword']) && $search_param['keyword'] != "") {
			$this->db->group_start();
			$this->db->like('so.customer_firstname', $search_param['keyword']);
			$this->db->or_like('so.customer_lastname', $search_param['keyword']);
			if (isset($search_param['keyword'])) {
				$fullname = explode(" ", $search_param['keyword']);
				$fname = $fullname[0];
				$this->db->or_like('so.customer_firstname', $fname);
				if (isset($fullname[1])) {
					$lname = $fullname[1];
					$this->db->or_like('so.customer_lastname', $lname);
				}
			}
			$this->db->or_like('so.increment_id', $search_param['keyword']);
			$this->db->or_like('so.coupon_code', $search_param['keyword']);
			$this->db->or_like('so.customer_email', $search_param['keyword']);
			$this->db->or_like('so.discount_amount', $search_param['keyword']);
			$this->db->or_like('ctm.name', $search_param['keyword']);
			$this->db->group_end();
		}
		$this->db->order_by('so.created_at', 'DESC');
		$result = $this->db->get();
		// echo $this->db->last_query();
		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return false;
		}
	}

	public function getDiscountCSVImport($fromdate, $todate)
	{

		$this->db->select(' ctm.name as customer_login_type, so.customer_id, so.order_id,so.coupon_code,so.status, so.voucher_code, so.cancel_by_customer, so.created_at, so.increment_id, so.customer_firstname, so.customer_lastname, so.customer_email, so.discount_amount,');
		$this->db->from('sales_order so');
		$this->db->join('customers c', 'c.id = so.customer_id', 'left');
		$this->db->join('customers_type_master ctm', 'ctm.id = c.customer_type_id', 'left');
		$this->db->where('so.created_at >=', $fromdate);
		$this->db->where('so.created_at <=', $todate);
		$this->db->where(' ( (so.status <> 7) AND ((so.status <> 3) OR so.cancel_by_customer != 0)) ');
		$this->db->where('so.parent_id', 0);
		$this->db->where('so.coupon_code is not null');
		$this->db->where('so.coupon_code !=" "');

		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function getSalesOverviewCSVImport($fromdate, $todate, $order_status = '')
	{
		$this->db->select('so.*, ctm.name as customer_login_type, sop.payment_method as order_payment_method,');
		// soa.address_type, soa.first_name, soa.last_name, soa.mobile_no, soa.address_line1, soa.address_line2, soa.city, soa.state, soa.country, soa.pincode
		$this->db->from('sales_order so');
		$this->db->join('sales_order_payment sop', 'sop.order_id = so.order_id', 'left');
		$this->db->join('customers c', 'c.id = so.customer_id', 'left');
		$this->db->join('customers_type_master ctm', 'ctm.id = c.customer_type_id', 'left');
		// $this->db->join('sales_order_address soa','soa.order_id = so.order_id','left');
		$this->db->where('so.created_at >=', $fromdate);
		$this->db->where('so.created_at <=', $todate);
		// $this->db->where('so.status',6);
		$this->db->where('so.status !=', 3);
		if ($order_status == "N/A") {
			$this->db->where('so.status !=', 7);
		} else {
			$this->db->where('so.status =', $order_status);
		}
		$this->db->where('so.parent_id', 0);
		$this->db->group_by('so.increment_id');
		$query = $this->db->get();
		// print_r($this->db->last_query());
		// die();
		$result = $query->result_array();
		return $result;
	}

	public function getAddressByType($order_id, $address_type)
	{
		// $this->db->select('so.*, sop.payment_method as order_payment_method, soa.address_type, soa.first_name, soa.last_name, soa.mobile_no, soa.address_line1, soa.address_line2, soa.city, soa.state, soa.country, soa.pincode');
		$this->db->select('soa.*,');
		$this->db->from('sales_order_address soa');
		$this->db->where('soa.order_id', $order_id);
		$this->db->where('soa.address_type', $address_type);

		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result();
		return $result;
	}

	public function getAddressesForMultipleOrders(array $order_ids)
	{
		$this->db->select('*');
		$this->db->from('sales_order_address');
		$this->db->where_in('order_id', $order_ids);

		$query = $this->db->get();
		// print_r($this->db->last_query());
		$addresses = $query->result();
		$result = [];
		foreach ($addresses as $address) {
			$result[$address->order_id][$address->address_type] = $address;
		}
		return $result;
	}

	public function getb2webshopOrderCSVImport($fromdate, $todate, $webshop_id)
	{
		$main_db_name = $this->db->database;

		$this->db->select('bo.*, fusr.org_shop_name, soa.address_line1 as line1 , soa.address_line2 as line2, soa.city as soa_city, soa.state as soa_state, soa.country as soa_country, soa.pincode as soa_pincode, fusr.bill_address_line1 , fusr.bill_address_line2, fusr.bill_city, fusr.bill_state, fusr.bill_country, fusr.bill_pincode, fusr.ship_address_line1 , fusr.ship_address_line2, fusr.ship_city, fusr.ship_state, fusr.ship_country, fusr.ship_pincode,');

		$this->db->from('b2b_orders bo');
		$this->db->join($main_db_name . '.fbc_users_shop fusr', 'fusr.shop_id = bo.shop_id', 'left');
		$this->db->join('sales_order_address soa', 'soa.order_id = bo.webshop_order_id', 'left');
		$this->db->where('bo.created_at >=', $fromdate);
		$this->db->where('bo.created_at <=', $todate);
		$this->db->where('bo.status !=', 7);
		$this->db->where('bo.parent_id', 0);
		if ($webshop_id != 'all') {
			$this->db->where('bo.shop_id', $webshop_id);
		}
		// $this->db->group_by('bo.increment_id');
		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function getReturnRefundReportCSVImport($fromdate, $todate)
	{
		$status = array(0, 1);
		$this->db->select('sor.*, ctm.name as customer_login_type,so.customer_id, so.increment_id, so.created_at as purchased_on, so.customer_firstname, so.customer_lastname, sop.payment_method_name');
		$this->db->from('sales_order_return sor');
		$this->db->join('sales_order so', 'so.order_id = sor.order_id', 'left');
		$this->db->join('sales_order_payment sop', 'sop.order_id = sor.order_id', 'left');
		$this->db->join('customers c', 'c.id = so.customer_id', 'left');
		$this->db->join('customers_type_master ctm', 'ctm.id = c.customer_type_id', 'left');
		$this->db->where('so.created_at >=', $fromdate);
		$this->db->where('so.created_at <=', $todate);
		$this->db->where_not_in('sor.status', $status);
		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function getEscalationsReportCSVImport($fromdate, $todate)
	{
		$this->db->select('soe.*, ctm.name as customer_login_type,so.customer_id, so.increment_id, so.created_at as purchased_on, so.customer_firstname, so.customer_lastname, so.grand_total, sop.payment_method_name');
		$this->db->from('sales_order_escalations soe');
		$this->db->join('sales_order so', 'so.order_id = soe.order_id', 'left');
		$this->db->join('sales_order_payment sop', 'sop.order_id = soe.order_id', 'left');
		$this->db->join('customers c', 'c.id = so.customer_id', 'left');
		$this->db->join('customers_type_master ctm', 'ctm.id = c.customer_type_id', 'left');
		$this->db->where('so.created_at >=', $fromdate);
		$this->db->where('so.created_at <=', $todate);
		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function getSalesOverview_chart($fromdate, $todate)
	{
		// $this->db->select('order_id');
		$this->db->select('COUNT(order_id) as count');
		$this->db->from('sales_order');
		$this->db->where('created_at >=', date(strtotime($fromdate)));
		$this->db->where('created_at <=', date(strtotime($todate)));
		$this->db->where('status', 6);
		$this->db->where('parent_id', 0);
		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function getSalesOverview_chart_rev($fromdate, $todate)
	{
		// $this->db->select('order_id');
		$this->db->select('SUM(grand_total) as sum');
		$this->db->from('sales_order');
		$this->db->where('created_at >=', date(strtotime($fromdate)));
		$this->db->where('created_at <=', date(strtotime($todate)));
		$this->db->where('status', 6);
		$this->db->where('parent_id', 0);
		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function b2getSalesOverview_chart($fromdate, $todate)
	{
		// $this->db->select('order_id');
		$this->db->select('COUNT(order_id) as count');
		$this->db->from('b2b_orders');
		$this->db->where('created_at >=', date(strtotime($fromdate)));
		$this->db->where('created_at <=', date(strtotime($todate)));
		$this->db->where('status', 6);
		$this->db->where('parent_id', 0);
		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function b2getSalesOverview_chart_rev($fromdate, $todate)
	{
		// $this->db->select('order_id');
		$this->db->select('SUM(grand_total) as sum');
		$this->db->from('b2b_orders');
		$this->db->where('created_at >=', date(strtotime($fromdate)));
		$this->db->where('created_at <=', date(strtotime($todate)));
		$this->db->where('status', 6);
		$this->db->where('parent_id', 0);
		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function get_oldest_date()
	{
		// $this->db->select('order_id');
		$this->db->select('MIN(created_at) as date');
		$this->db->from('sales_order');
		//$this->db->where('status',6);
		//$this->db->where('parent_id',0);
		$query = $this->db->get();
		$ret = $query->row();
		// print_r($this->db->last_query());
		$result = $ret;
		return $result;
	}

	public function b2b_get_oldest_date()
	{
		// $this->db->select('order_id');
		$this->db->select('MIN(created_at) as date');
		$this->db->from('b2b_orders');
		//$this->db->where('status',6);
		//$this->db->where('parent_id',0);
		$query = $this->db->get();
		$ret = $query->row();
		// print_r($this->db->last_query());
		$result = $ret;
		return $result;
	}

	public function get_best_selling_products()
	{
		// $this->db->select('order_id');
		$this->db->select('soi.product_id, soi.product_name, COUNT(soi.product_id) as sales, SUM(soi.total_price) as sales_revenue, soi.sku, soi.barcode');
		$this->db->from('sales_order_items soi');
		$this->db->join('sales_order so', 'so.order_id = soi.order_id', 'left');
		$this->db->where('so.status', 6);
		$this->db->group_by('soi.product_id HAVING COUNT(soi.product_id)>0');
		$this->db->order_by('sales ', 'DESC');
		$this->db->limit(15, 0);

		$query = $this->db->get();
		//print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function b2b_get_best_selling_products()
	{
		$this->db->select('boi.product_id, boi.product_name, COUNT(boi.product_id) as sales, SUM(boi.total_price) as sales_revenue, boi.sku, boi.barcode');
		$this->db->from('b2b_order_items boi');
		$this->db->join('b2b_orders bo', 'bo.order_id = boi.order_id', 'left');
		$this->db->where('bo.status', 6);
		$this->db->group_by('boi.product_id HAVING COUNT(boi.product_id)>0');
		$this->db->order_by('sales ', 'DESC');
		$this->db->limit(15, 0);

		$query = $this->db->get();
		//print_r($this->db->last_query());
		$result = $query->result_array();
		return $result;
	}

	public function b2b_webshop_name_list()
	{

		$main_db_name = $this->db->database;

		$this->db->select('a.*');
		$this->db->from('b2b_customers b');
		$this->db->join($main_db_name . '.fbc_users_shop as a', 'b.shop_id = a.shop_id');
		$result = $this->db->get();
		return $result->result();
	}

	public function getStatus($status_id)
	{

		if ($status_id == 0) {
			$status = 'To Be Processed';
		} elseif ($status_id == 1) {
			$status = 'Processing';
		} elseif ($status_id == 2) {
			$status = 'Complete';
		} elseif ($status_id == 3) {
			$status = 'Cancelled';
		} elseif ($status_id == 4) {
			$status = 'Tracking Missing';
		} elseif ($status_id == 5) {
			$status = 'Tracking Incomplete';
		} elseif ($status_id == 6) {
			$status = 'Tracking Complete';
		}
		return $status;
	}



	public function getProductData($fromdate, $todate)
	{
		$this->db->select('p.id,p.product_type,p.product_inv_type,p.name,p.product_code,p.sku,p.sub_issues,p.meta_title,p.price,p.cost_price,p.tax_percent,p.tax_amount,p.webshop_price,pv.product_id,pv.attr_id,eav.id as eavID,eav.attr_name,p.created_at,p.publisher_id,pub.id as PUBID ,pub.publication_name');
		// $this->db->select('p.id,p.product_type,p.product_inv_type,p.name,p.product_code,p.sku,p.sub_issues,p.meta_title,p.price,p.cost_price,p.tax_percent,p.tax_amount,p.webshop_price,pv.product_id,pv.attr_id,eav.id as eavID,eav.attr_name,p.created_at,p.publisher_id,pc.category_ids as cat_ids, c.cat_name,pub.id as PUBID ,pub.publication_name');
		$this->db->from('products p');
		// $this->db->join('products_category pc','p.id = pc.product_id','left');
		// $this->db->join('category c','pc.category_ids = c.id','left');
		$this->db->join('publisher pub', ' p.publisher_id= pub.id', 'left');
		$this->db->join('products_variants_master pv', 'p.id = pv.product_id', 'left');
		$this->db->join('eav_attributes eav', 'pv.attr_id = eav.id', 'left');
		$this->db->where('p.created_at >=', $fromdate);
		$this->db->where('p.created_at <=', $todate);
		$this->db->where('p.status', 1);
		$this->db->where('p.remove_flag', 0);
		$this->db->where('p.parent_id', 0);
		$query = $this->db->get();
		//echo $this->db->last_query();die();
		$result = $query->result_array();
		//  print_r($query->result_array());
		//  exit;
		return $result;
	}


	public function getAllCategory()
	{
		$this->db->select('*');
		$this->db->from('category');
		$this->db->where('parent_id', 0);
		$this->db->where('cat_level', 0);
		$this->db->where('status', 1);
		$query = $this->db->get();
		// print_r($this->db->last_query());
		$result = $query->result();
		// print_r($result);die();
		return $result;
	}


	public function getCatProductData($fromdate, $todate, $catID)
	{
		$this->db->select('p.id as PID,p.name,p.product_code,p.sku,p.sub_issues,p.created_at,p.publisher_id,pc.category_ids as cat_ids,c.id,c.cat_name,pub.id as PUBID ,pub.publication_name');
		$this->db->from('products p');
		$this->db->join('products_category pc', 'p.id = pc.product_id', 'left');
		$this->db->join('category c', 'pc.category_ids = c.id', 'left');
		$this->db->join('publisher pub', ' p.publisher_id= pub.id', 'left');
		$this->db->where_in('c.id ', $catID);
		// $this->db->select('COUNT(c.cat_name) as Catcount');
		// $this->db->group_by('p.id HAVING COUNT(p.id)>0');
		$this->db->where('p.created_at >=', $fromdate);
		$this->db->where('p.created_at <=', $todate);
		$this->db->where('p.status', 1);
		$this->db->where('p.remove_flag', 0);
		$this->db->where('p.parent_id', 0);
		$query = $this->db->get();
		$result = $query->result_array();
		//  print_r($query->result_array());
		//  exit;
		return $result;
	}



	public function getProductDataPrice($fromDate, $toDate, $startprice, $endprice)
	{
		//echo $startprice ;die();
		$this->db->select('p.id,p.name,p.product_code,p.sku,p.sub_issues,p.created_at,p.price,p.publisher_id,pub.id as PUBID ,pub.publication_name');
		$this->db->from('products p');
		$this->db->join('publisher pub', ' p.publisher_id= pub.id', 'left');
		$this->db->where('p.price !=', 0);
		$this->db->where('p.price >=', $startprice);
		$this->db->where('p.price <=', $endprice);
		$this->db->where('p.created_at >=', $fromDate);
		$this->db->where('p.created_at <=', $toDate);
		// $this->db->where('status',1);

		// $this->db->where('remove_flag',0);

		$query = $this->db->get();
		//  echo $this->db->last_query();die();
		$result = $query->result_array();
		//  print_r($result);
		//  exit;

		return $result;
	}


	public function getProductDataWithCatAndPrice($fromdate, $todate, $catID, $startprice, $endprice)
	{
		$this->db->select('p.id as PID,p.name,p.product_code,p.sku,p.sub_issues,p.created_at,p.publisher_id,p.price,pc.category_ids as cat_ids,c.id,c.cat_name,pub.id as PUBID ,pub.publication_name');
		$this->db->from('products p');
		$this->db->join('products_category pc', 'p.id = pc.product_id', 'left');
		$this->db->join('category c', 'pc.category_ids = c.id', 'left');
		$this->db->join('publisher pub', ' p.publisher_id= pub.id', 'left');
		$this->db->where_in('c.id ', $catID);
		// $this->db->select('COUNT(c.cat_name) as Catcount');
		// $this->db->group_by('p.id HAVING COUNT(p.id)>0');
		$this->db->where('p.price !=', 0);
		$this->db->where('p.price >=', $startprice);
		$this->db->where('p.price <=', $endprice);
		$this->db->where('p.created_at >=', $fromdate);
		$this->db->where('p.created_at <=', $todate);
		$this->db->where('p.status', 1);
		$this->db->where('p.remove_flag', 0);
		//$this->db->where('p.parent_id',0);
		$query = $this->db->get();
		//echo $this->db->last_query();die();
		$result = $query->result_array();
		//print_r($query->result_array());
		//exit;
		return $result;
	}
}
