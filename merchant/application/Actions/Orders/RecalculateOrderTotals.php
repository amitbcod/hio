<?php

namespace App\Actions\Orders;

class RecalculateOrderTotals
{
	private $ci;
	private $seller_db;

	public function __construct(){
		$this->ci = &get_instance();
		$this->ci->load->model('WebshopOrdersModel');
	}
	public function __invoke($order_id){
		$this->seller_db = $this->ci->WebshopOrdersModel->get_seller_db_connection();

		$this->seller_db
			->where('order_id', $order_id)
			->set('total_price', 'price * qty_ordered', false)
			->set('discount_amount', 'price * (discount_percent / 100)', false)

//			->set('total_price', 'price * qty_original', false)
//			->set('total_discount_amount', 'discount_amount * qty_original', false)
			->update('sales_order_items');
		$this->seller_db
			->where('order_id', $order_id)
			->set('total_discount_amount', 'discount_amount * qty_ordered', false)
			->update('sales_order_items');

		$subtotals = $this->seller_db
			->where('order_id', $order_id)
			->select_sum('total_price')
			->select_sum('total_discount_amount')
			->select('SUM(tax_amount * qty_ordered) as total_tax')
			->get('sales_order_items')->row();
		$base_subtotal = $subtotals->total_price;
		$tax_subtotal = $subtotals->total_tax;
		$discount_subtotal = $subtotals->total_discount_amount;

		$this->seller_db
			->where('order_id', $order_id)
			->set('base_subtotal', $base_subtotal)
			->set('discount_amount', $discount_subtotal)
			->set('tax_amount', "$tax_subtotal * (1 - (discount_percent / 100))", false)
			->update('sales_order');

		$this->seller_db
			->where('order_id', $order_id)
			->set('subtotal', 'base_subtotal - discount_amount + shipping_amount', false)
			->update('sales_order');

		$this->seller_db
			->where('order_id', $order_id)
			->set('grand_total', 'subtotal - voucher_amount + payment_final_charge', false)
			->update('sales_order');

		$this->seller_db
			->where('order_id', $order_id)
			->set('base_discount_amount', 'discount_amount', false)
			->set('base_grand_total', 'grand_total', false)
			->set('base_tax_amount', 'tax_amount', false)
			->set('base_shipping_amount', 'shipping_amount', false)
			->set('base_shipping_tax_amount', 'shipping_tax_amount', false)
			->update('sales_order');

	}
}
