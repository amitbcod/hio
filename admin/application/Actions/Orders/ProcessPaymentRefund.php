<?php

namespace App\Actions\Orders;

use Stripe\StripeClient;

class ProcessPaymentRefund
{
	private $ci;

	public function __construct(){
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'Construct start ProcessPaymentRefund', FILE_APPEND);
		$this->ci = &get_instance();
		$this->ci->load->model('WebshopOrdersModel');
		$this->ci->load->model('SalesOrderPaymentModel');
		$this->ci->load->model('ReturnOrderModel');
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'Construct end ProcessPaymentRefund', FILE_APPEND);
	}

	public function __invoke($refund_id){
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'In ProcessPaymentRefund with id:' . $refund_id, FILE_APPEND);
		$refund = $this->ci->SalesOrderPaymentModel->get_refund($refund_id);
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'Refund details:' . json_encode($refund), FILE_APPEND);
		if(is_null($refund)){
			return;
		}
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'Found Refund with id:' . $refund_id, FILE_APPEND);
		$payment = $this->ci->SalesOrderPaymentModel->get_payment($refund->payment_id);
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'Found payment for refund', FILE_APPEND);

		$response = null;

		switch ((int) $payment->payment_method_id) {
			case 6:
				file_put_contents('logs/refund-log.txt', PHP_EOL . 'starting stripe refund', FILE_APPEND);
				$response = $this->processStripeRefund($payment->payment_intent_id, $refund->refund_amount);
				break;
			case 1:
				file_put_contents('logs/refund-log.txt', PHP_EOL . 'starting paypal refund', FILE_APPEND);
				$response = $this->processPaypalRefund($payment->transaction_id, $refund->refund_amount, $refund->refund_currency);
				break;
		}
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'REfund completed, going to update database refund status', FILE_APPEND);
		$this->ci->SalesOrderPaymentModel->update_refund($refund_id, ['response_data' => $response, 'status' => 'refund_requested']);
		file_put_contents('logs/refund-log.txt', PHP_EOL . 'Updated database refund status', FILE_APPEND);
	}

	private function processStripeRefund($payment_intent_id, $refund_amount)
	{
		$amount_in_cents = round($refund_amount * 100);
		$WebShopPaymentDetailsById =  $this->ci->ReturnOrderModel->getSingleDataByID('webshop_payments', ['payment_id'=> 6],'');
		$stripe_api_key = json_decode($WebShopPaymentDetailsById->gateway_details)->key;

		$stripe = new StripeClient($stripe_api_key);
		$response = $stripe->refunds->create([
			'payment_intent' => $payment_intent_id,
			'amount' => $amount_in_cents,
		]);
		return $response;
	}

	private function processPaypalRefund($transaction_id, $refund_amount, $currency)
	{
		$WebShopPaymentDetailsById =  $this->ci->ReturnOrderModel->getSingleDataByID('webshop_payments', ['payment_id'=>1],'');
		$paypalGatewayDetails=json_decode($WebShopPaymentDetailsById->gateway_details);

		$client_id = $paypalGatewayDetails->client_id;
		$secret_key = $paypalGatewayDetails->secret_key;
		$PaypalApiUrl = $paypalGatewayDetails->paypal_api_url;

		$apiURL=$PaypalApiUrl.'/v1/payments/capture/' . $transaction_id . '/refund';

		$postField = json_encode([
			'amount'=> ['currency' => $currency, 'total' => $refund_amount]
		]);

		$headers = [
			'Content-Type: application/json',
			'Authorization: Basic '.base64_encode($client_id.':'.$secret_key)
		];

		$ch=curl_init();
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch, CURLOPT_URL, $apiURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}
}
