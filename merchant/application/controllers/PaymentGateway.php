<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Path where phpseclib is placed
$phpseclib_base = rtrim(APPPATH, '/') . '/third_party/phpseclib/';

// Autoloader for phpseclib (v2 or v3)
spl_autoload_register(function ($class) use ($phpseclib_base) {
    $class = ltrim($class, '\\');
    $prefixes = ['phpseclib\\', 'phpseclib3\\'];
    foreach ($prefixes as $prefix) {
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) continue;
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $paths = [
            $phpseclib_base . $relative . '.php',
            $phpseclib_base . 'src/' . $relative . '.php'
        ];
        foreach ($paths as $file) {
            if (file_exists($file)) { require_once $file; return; }
        }
    }
});

// Detect RSA class
$PHPSECLIB_RSA_CLASS = null;
if (class_exists('\\phpseclib\\Crypt\\RSA')) $PHPSECLIB_RSA_CLASS = '\\phpseclib\\Crypt\\RSA';
elseif (class_exists('\\phpseclib3\\Crypt\\RSA')) $PHPSECLIB_RSA_CLASS = '\\phpseclib3\\Crypt\\RSA';
if ($PHPSECLIB_RSA_CLASS === null) {
    log_message('error', 'phpseclib RSA class not found.');
    show_error('phpseclib RSA class not found. Please install phpseclib.');
}

class PaymentGateway extends CI_Controller {

    private $rsaClass;

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('Subscription_model');
    

        global $PHPSECLIB_RSA_CLASS;
        $this->rsaClass = $PHPSECLIB_RSA_CLASS;

        if (!$this->rsaClass) {
            show_error('phpseclib RSA class not available');
        }

        $this->config->load('payment');
    }

    /**
     * Step 1: Initiate My.T Money payment
     */
    public function mytMoney($order_id = null) {
        $order = $this->db->get_where('subscription_orders', ['id' => $order_id])->row_array();
        if (!$order) show_404();

        $cfg = $this->config->item('myt_money');

        $merchantAppId = $cfg['app_id'];
        $apiKey        = $cfg['api_key'];
        $publicKey     = $cfg['public_key'];
        $notifyUrl     = $cfg['notify_url'];
        $returnUrl     = $cfg['return_url'];
        $mode          = $cfg['mode'];

        $merTradeNo = (int)(microtime(true) * 1000);
        $this->db->where('id', $order_id)->update('subscription_orders', ['mer_trade_no' => $merTradeNo]);

        $payload = [
            "totalPrice" => $order['amount'],
            "currency"   => "MUR",
            "merTradeNo" => $merTradeNo,
            "notifyUrl"  => $notifyUrl,
            "returnUrl"  => $returnUrl,
            "remark"     => "Subscription Plan Payment",
            "lang"       => "en"
        ];

        $payloadJson = json_encode($payload);

        // Encrypt payload with RSA
        $rsa = new $this->rsaClass();
        $rsa->setEncryptionMode($this->rsaClass::ENCRYPTION_OAEP);
        $rsa->loadKey($publicKey);
        $encryptedPayload = base64_encode($rsa->encrypt($payloadJson));

        // Create HMAC signature
        $paymentType = "S";
        $signatureData = "appId={$merchantAppId}&merTradeNo={$merTradeNo}&payload={$encryptedPayload}&paymentType={$paymentType}";
        $sign = base64_encode(hash_hmac('sha512', $signatureData, $apiKey, true));

        log_message('info', "MyT Payment Payload: {$payloadJson}");
        log_message('info', "MyT Encrypted Payload: {$encryptedPayload}");
        log_message('info', "MyT Payment Signature: {$sign}");

        $gatewayUrl = $mode === 'sandbox'
            ? "https://pay.mytmoney.mu/Mt/web/payments"
            : "https://pay.mytmoney.mu/Mt/web/payments";

        echo '<html><body>';
        echo '<form id="mytForm" action="' . $gatewayUrl . '" method="POST">';
        echo '<input type="hidden" name="appId" value="' . $merchantAppId . '">';
        echo '<input type="hidden" name="merTradeNo" value="' . $merTradeNo . '">';
        echo '<input type="hidden" name="payload" value="' . $encryptedPayload . '">';
        echo '<input type="hidden" name="paymentType" value="' . $paymentType . '">';
        echo '<input type="hidden" name="sign" value="' . $sign . '">';
        echo '</form>';
        echo '<script>document.getElementById("mytForm").submit();</script>';
        echo '</body></html>';
    }

    /**
     * Step 2A: My.T Money Server-to-Server Notify (GET)
     */
    public function mytNotify() {
        log_message('info', '=== MyT Notify Called ===');

        // GET data from My.T Money notify
        $data = $this->input->get();
        log_message('info', 'MyT Notify Data: ' . print_r($data, true));

        $this->_handleMytResponse(true, $data);
    }

    /**
     * Step 2B: My.T Money Return (Browser Redirect)
     * Just read DB and show user status, no DB update.
     */
    /*public function mytCallback() {
        log_message('info', '=== MyT Callback Called ===');

        $merTradeNo = $this->input->get_post('merTradeNo') ?? null;

        if (!$merTradeNo) {
            $this->session->set_flashdata('error', 'Missing merTradeNo.');
            redirect('subscription');
            return;
        }

        $order = $this->db->get_where('subscription_orders', ['mer_trade_no' => $merTradeNo])->row_array();

        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect('subscription');
            return;
        }

        // Display user-friendly payment status
        if ($order['status'] === 'paid') {
            redirect(base_url('subscription?payment=success'));
        } elseif ($order['status'] === 'failed') {
            redirect(base_url('subscription?payment=error'));
        } else {
            redirect(base_url('subscription?payment=pending'));
        }
    }*/

        public function mytCallback()
        {
            log_message('info', '=== MyT Callback Called ===');

            $merTradeNo = $this->input->get_post('merTradeNo') ?? null;

            if (!$merTradeNo) {
                log_message('error', 'Missing merTradeNo in callback.');
                show_error('Invalid payment reference. Missing merTradeNo.', 400);
                return;
            }

            // Fetch subscription order
            $order = $this->db->get_where('subscription_orders', ['mer_trade_no' => $merTradeNo])->row_array();

            if (!$order) {
                log_message('error', 'Order not found for merTradeNo: ' . $merTradeNo);
                show_error('Order not found.', 404);
                return;
            }

            // Redirect to detailed payment status page
            $orderId = $order['order_id'] ?? $order['id'] ?? null;

            if (!$orderId) {
                log_message('error', 'Order ID missing for MyT callback.');
                show_error('Invalid order data.', 400);
                return;
            }

            // ✅ Redirect to your new payment status page
            $this->load->library('encryption');

            $encrypted = $this->encryption->encrypt($orderId);
            $encryptedUrl = rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
            $frontendUrl = str_replace('/merchant', '', base_url("payment/status/{$encryptedUrl}"));
            redirect($frontendUrl);
        
        }


    /**
     * Handle My.T Money callback (Notify URL only)
     */
    private function _handleMytResponse($isServerCall = true, $overrideData = null) {
    $data = $overrideData ?? array_merge($this->input->get(), $this->input->post());
    log_message('info', '=== MyT Notify Data Processed === ' . print_r($data, true));

    if (empty($data)) {
        log_message('error', 'MyT Notify: Empty payload');
        if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Empty data']);
        return;
    }

    $merTradeNo = $data['merTradeNo'] ?? null;
    if (!$merTradeNo) {
        log_message('error', 'MyT Notify: Missing merTradeNo');
        if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Missing merTradeNo']);
        return;
    }

    $order = $this->db->get_where('subscription_orders', ['mer_trade_no' => $merTradeNo])->row_array();
    if (!$order) {
        log_message('error', "Order not found for merTradeNo: $merTradeNo");
        if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Order not found']);
        return;
    }

    $tradeStatus = strtoupper($data['tradeStatus'] ?? '');
    $errorCode   = $data['errorCode'] ?? '';
    $timestamp   = $data['timestamp'] ?? '';

    if ($tradeStatus === 'TRADE_FINISHED' || ($data['resultCode'] ?? '') === '0') {
        $status = 'paid';
    } else {
        $status = 'failed';
    }

    $updateData = [
        'status'         => $status,
        'transaction_id' => $data['tradeNo'] ?? null,
        'error_code'     => $errorCode,
        'payment_timestamp' => $timestamp,  // <-- match DB column
        'updated_at'     => date('Y-m-d H:i:s')
    ];

    $this->db->where('id', $order['id'])
            ->update('subscription_orders', $updateData); 

    if ($status === 'paid') {
        $this->Subscription_model->subscribe_plan($order['publisher_id'], $order['plan_id'], $this->input->ip_address());
        log_message('info', "MyT Payment SUCCESS → Order ID: {$order['id']} | merTradeNo: {$merTradeNo}");
    } else {
        log_message('error', "MyT Payment FAILED → Order ID: {$order['id']} | tradeStatus: {$tradeStatus}");
    }

    if ($isServerCall) return $this->_sendJson(['status' => $status, 'message' => 'Payment processed']);
}


    /**
     * Send JSON response for server-to-server notify
     */
    private function _sendJson($data) {
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }



}
