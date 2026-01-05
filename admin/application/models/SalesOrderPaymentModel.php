<?php

class SalesOrderPaymentModel extends \CI_Model
{
	private $db;

	public function __construct()
	{
		parent::__construct();

		
	}

	public function get_payments($where = []){
		return $this->db->from('sales_order_payment')->where($where)->get()->result();
	}

	public function get_payment($payment_id){
		return $this->db
			->from('sales_order_payment')
			->where('payment_id', $payment_id)
			->get()->row();
	}

	public function get_order_payment($order_id){
		return $this->db->from('sales_order_payment')->where('order_id', $order_id)->get()->row();
	}

	public function update_payment($payment_id, array $updates)
	{
		$this->db
			->where('payment_id', $payment_id)
			->set($updates)
			->update('sales_order_payment');
	}

	public function add_payment_refund($order_id, $payment_id, $amount_default_currency, $refund_amount, $refund_currency)
	{
		$count = $this->db
			->from('sales_order_payment_refunds')
			->where('order_id', $order_id)
			->where('amount_default_currency', $amount_default_currency)
			->count_all_results();
		if($count > 0){ // Refund already added
			return;
		}

		$this->db
			->set('order_id', $order_id)
			->set('payment_id', $payment_id)
			->set('amount_default_currency', $amount_default_currency)
			->set('refund_amount', $refund_amount)
			->set('refund_currency', $refund_currency)
			->set('status', 'open')
			->set('created_at', time())
			->set('updated_at', time())
			->insert('sales_order_payment_refunds');

		return $this->db->insert_id();
	}

	public function get_refund($refund_id){
		return $this->db
			->from('sales_order_payment_refunds')
			->where('id', $refund_id)
			->get()->row();
	}

	public function update_refund($refund_id, $updates){
		return $this->db
			->set($updates)
			->set('updated_at', time())
			->where('id', $refund_id)
			->update('sales_order_payment_refunds');
	}

	public function get_order_refunds($order_id){
		return $this->db
			->from('sales_order_payment_refunds')
			->where('order_id', $order_id)
			->get();
	}

}
