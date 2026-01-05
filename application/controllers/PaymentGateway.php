<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PaymentGateway extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Giftcard_model');
    }

    // Show a simple payment page for testing (simulate payment)
    public function payGiftCard($order_id = null)
    {
        $order = $this->Giftcard_model->get_order((int)$order_id);
        if (!$order) show_404();

        $data['order'] = $order;
        $this->load->view('giftcards/giftcard_payment', $data);
    }

    // Simulate gateway callback / mark paid
    // In real integration this will be a callback or redirect handler from the gateway.
    public function giftcardCallback()
    {
        $order_id = (int)$this->input->post('order_id');
        $action = $this->input->post('action'); // success or fail
        $payment_ref = 'SIM-' . time();

        if (!$order_id || !$action) {
            echo "Invalid request";
            return;
        }

        if ($action === 'success') {
            // mark paid
            $this->Giftcard_model->mark_order_paid($order_id, $payment_ref);
            // issue gift card
            $issued = $this->Giftcard_model->issue_giftcard($order_id);

            // optionally send email to receiver (left as exercise)
            // redirect to success screen
            redirect('Giftcards/success/' . $order_id);
        } else {
            // mark cancelled (status 2)
            $this->db->where('id',$order_id)->update('gift_card_orders',['status'=>2,'updated_at'=>date('Y-m-d H:i:s')]);
            // show failure page or redirect back
            $this->load->view('giftcards/giftcard_failed', ['order_id' => $order_id]);
        }
    }


    
public function notify()
{
       $this->config->set_item('sess_driver', 'files');
    $this->config->set_item('sess_save_path', sys_get_temp_dir());
    $this->load->library('session');

    log_message('info', '=== MyT Money Notify Callback Triggered ===');

    // 1️⃣ Get data from GET
    $data = $this->input->get();
    log_message('info', 'GET data received: ' . print_r($data, true));

    if (empty($data)) {
        log_message('error', 'Notify called but GET data is empty');
        show_error('Empty notify payload', 400);
        return;
    }

    // 2️⃣ Identify transaction
    $merTradeNo = $data['merTradeNo'] ?? null;
    log_message('info', 'Extracted merTradeNo: ' . $merTradeNo);

    if (!$merTradeNo) {
        log_message('error', 'Notify missing merTradeNo');
        show_error('Invalid notification: missing merTradeNo.', 400);
        return;
    }

    // 3️⃣ Map gateway status → DB status
    $statusRaw = strtolower($data['status'] ?? '');
    switch ($statusRaw) {
        case 'success':
        case 'completed':
        case 'paid':
            $status = 'success';
            break;
        case 'failed':
        case 'error':
        case 'cancelled':
            $status = 'failed';
            break;
        default:
            $status = 'initiated';
    }

    $transactionRef = $data['transactionRef'] ?? null;
    $errorMsg       = $data['error_message'] ?? null;

    // 4️⃣ Update DB
    $updateData = [
        'transaction_ref' => $transactionRef,
        'status'          => $status,
        'payload'         => json_encode($data),
        'error_message'   => $errorMsg,
        'updated_at'      => date('Y-m-d H:i:s'),
    ];

    $this->db->where('mer_trade_no', $merTradeNo)
             ->update('order_mytmoney_transactions', $updateData);

    if ($this->db->affected_rows() > 0) {
        log_message('info', "✅ order_mytmoney_transactions updated for merTradeNo: {$merTradeNo}");
        echo "OK";
    } else {
        log_message('error', "⚠️ No transaction record found for merTradeNo: {$merTradeNo}");
        echo "NO RECORD UPDATED";
    }

    log_message('info', 'Notify processing completed.');
}


public function payment_status()
{
    $key = $this->input->get('key');
    $increment_id = $key ? base64_decode($key) : '';

    if (!$increment_id) {
        redirect(base_url());
        return;
    }

    // Fetch My.T Money transaction
    $response = CommonRepository::get_table_data([
        'table_name' => 'order_mytmoney_transactions',
        'where' => 'increment_id = ?',
        'params' => [$increment_id]
    ]);

    if (empty($response) || $response->statusCode != 200 || empty($response->tableData)) {
        redirect(base_url());
        return;
    }

    $transaction = $response->tableData[0];

    // Determine status
    $status = $transaction->status == 'success' ? 'success' : 'failed';

    $data = [
        'PageTitle' => $status == 'success' ? 'Thank You' : 'Payment Failed',
        'order_id'  => $transaction->order_id,
        'increment_id' => $transaction->increment_id,
        'amount' => $transaction->amount,
        'currency' => $transaction->currency,
        'status'    => $status,
        'payment_method' => 'mytmoney'
    ];

    // Load a single view which handles both success/fail messages
    $this->template->load('checkout/payment_status', $data);
}
}
