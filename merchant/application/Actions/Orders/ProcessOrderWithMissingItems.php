<?php

namespace App\Actions\Orders;

use CI_Controller;
use WebshopOrdersModel;

/**
 * @var CI_Controller $ci
 */
class ProcessOrderWithMissingItems
{
	private $ci;

	public function __construct(){
		$this->ci = &get_instance();
		$this->ci->load->model('WebshopOrdersModel');
		$this->ci->load->model('SalesOrderPaymentModel');
	}

	public function __invoke($order_id){
		// Make sure qty_original is entered.
		$this->ci->WebshopOrdersModel->update_qty_original_from_qty_ordered($order_id);

		// overwrite qty_ordered with qty_scanned
		$this->ci->WebshopOrdersModel->update_qty_ordered_from_qty_scanned($order_id);

		// Recalculate order totals
		(new RecalculateOrderTotals())($order_id);

		// Create Refund for difference
		$updatedOrderData = $this->ci->WebshopOrdersModel->get_webshop_invoicing_data($order_id);
		$orderPayment = $this->ci->SalesOrderPaymentModel->get_order_payment($order_id);

		$difference_amount = $orderPayment->amount - $updatedOrderData->grand_total;
		$difference_refund_amount = $orderPayment->payment_amount - ($updatedOrderData->grand_total * $updatedOrderData->currency_conversion_rate);
		if($difference_amount > 0.05) {
			file_put_contents('logs/refund-log.txt', PHP_EOL . 'Adding refund in DB for: € ' . $difference_amount, FILE_APPEND);
			$orderPaymentRefundId = $this->ci->SalesOrderPaymentModel->add_payment_refund($order_id, $orderPayment->payment_id, $difference_amount, $difference_refund_amount, $updatedOrderData->currency_code_session);
			file_put_contents('logs/refund-log.txt', PHP_EOL . 'Adding refund in DB with ID:' . $orderPaymentRefundId, FILE_APPEND);
			return $orderPaymentRefundId;
		}
	}
}
