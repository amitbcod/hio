<?php

/**
 * @property SalesOrderPaymentModel $SalesOrderPaymentModel
 * @property WebshopOrdersModel $WebshopOrdersModel
 */
class UpdatePaymentTotalsController extends \CI_Controller
{
	function __construct()
	{
		parent::__construct();

		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
		$this->load->model('SalesOrderPaymentModel');
		$this->load->model('WebshopOrdersModel');
	}

	public function updateEmptyPaymentAmounts(){
		$this->stripe();
		$this->paypal();
	}

	private function paypal(): void
	{
		$paypal_payments = $this->SalesOrderPaymentModel->get_payments(['payment_amount' => null, 'payment_method' => 'paypal_express', 'response_data !=' => null]);

		$default_currency = 'EUR';

		foreach ($paypal_payments as $payment) {
			$response = json_decode($payment->response_data);

			$this->SalesOrderPaymentModel->update_payment($payment->payment_id, [
				'amount' => $response->currency_code === $default_currency ? $response->payment_gross : $this->getOrderTotal($payment->order_id),
				'payment_currency' => $response->currency_code,
				'payment_amount' => $response->payment_gross,
				'status' => strtolower($response->payment_status),
			]);
		}
	}

	private function stripe(): void
	{
		$payments = $this->SalesOrderPaymentModel->get_payments(['payment_amount' => null, 'payment_method' => 'stripe_payment', 'response_data !=' => null]);

		$default_currency = 'EUR';

		foreach ($payments as $payment) {
			$response_data = $payment->response_data;

			if(substr($response_data, -3) === 'ID}'){
				$response_data .= '"}';
			}

			$request = json_decode($payment->request_data);
			$response = json_decode($response_data);

			$this->SalesOrderPaymentModel->update_payment($payment->payment_id, [
				'amount' => $request->currency === $default_currency ? $request->amount / 100 : $this->getOrderTotal($payment->order_id),
				'payment_currency' => $request->currency,
				'payment_amount' => $request->amount / 100,
				'status' => strtolower($response->payment_status) === 'paid' ? 'completed' : strtolower($response->payment_status),
			]);
		}
	}

	private function getOrderTotal($order_id)
	{
		return $this->WebshopOrdersModel->getOrderById($order_id)->grand_total;
	}
}
