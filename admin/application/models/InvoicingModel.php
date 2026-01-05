<?php
class InvoicingModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	
	
  	// webshop single customer invoicing list
  	public function get_Customer_invoicing_list($customer_id)
	  {
	  	$this->db->select('*');
	  	$this->db->where('customer_id',$customer_id);
	  	$this->db->where('invoice_order_type', 1);//1-webshop 2-b2webshop
	  	$this->db->from('invoicing');
	  	// $this->cust_db->join('sales_order_payment as sop','sales_order.order_id = sop.order_id');
	  	$query = $this->db->get();
		
		$resultArr = $query->result_array();
		// echo $this->cust_db->last_query();
		return $resultArr;
	}

	// b2b single customer invoicing list
  	public function get_b2b_customer_invoicing_list($customer_id)
	  {
	  	$this->db->select('*');
	  	$this->db->where('bill_customer_id',$customer_id);
	  	// $this->db->where('customer_id',$customer_id);//old 24-08-2021
	  	$this->db->where('invoice_order_type', 2);//1-webshop 2-b2webshop
	  	$this->db->from('invoicing');
	  	// $this->cust_db->join('sales_order_payment as sop','sales_order.order_id = sop.order_id');
	  	$query = $this->db->get();
		
		$resultArr = $query->result_array();
		// echo $this->cust_db->last_query();
		return $resultArr;
	}

	
}
