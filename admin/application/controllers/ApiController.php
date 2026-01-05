<?php

use App\Actions\Emails\SendShipmentConfirmationEmail;
use App\Actions\Emails\SendTrackingEmail;
use App\Actions\Orders\ProcessOrderWithMissingItems;
use App\Actions\Orders\ProcessPaymentRefund;

/**
 * @property WebshopOrdersModel $WebshopOrdersModel
 */
class ApiController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		if($_SERVER['HTTP_API_TOKEN'] !== '27c016c3-459f-4de0-bbbc-8d596d01a7b7'){
			echo "unauthorized";
			exit;
		}
		$this->load->model('WebshopOrdersModel');
	}

	public function send_shipment_confirmation_email()
	{
		$order_id = $this->input->post('order_id');
		$shop_id = $this->input->post('shop_id');
		$additional_message = $this->input->post('additional_message') ?? '';

		$OrderData = $this->WebshopOrdersModel->getSingleDataByID('sales_order', ['order_id'=>$order_id],'');

		(new SendShipmentConfirmationEmail())->execute(
			$OrderData,
			$shop_id,
			$OrderData->customer_email,
			$OrderData->customer_firstname.' '.$OrderData->customer_lastname,
			$OrderData->increment_id,
			$additional_message
		);

		echo "success";
		exit;
	}

	public function send_tracking_email()
	{
		$order_id = $this->input->post('order_id');
		$tracking_id = $this->input->post('tracking_id');
		$shop_id = $this->input->post('shop_id');

		(new SendTrackingEmail())->execute($shop_id, $order_id, $tracking_id);

		echo "success";
		exit;
	}


	public function process_refund_for_order_with_missing_items(){
		file_put_contents('logs/refund-log.txt', PHP_EOL . PHP_EOL . 'New Refund for ', FILE_APPEND);
		$order_id = $this->input->post('order_id');
		file_put_contents('logs/refund-log.txt', $order_id, FILE_APPEND);

		$refund_id = (new ProcessOrderWithMissingItems())($order_id);
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'Refund ID: ' . $refund_id, FILE_APPEND);
		(new ProcessPaymentRefund())($refund_id);
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'Refund Completed: ' . $refund_id, FILE_APPEND);

		echo "success";
		exit;
	}

}
