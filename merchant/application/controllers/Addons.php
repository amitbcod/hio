<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load phpseclib for RSA encryption
$phpseclib_base = rtrim(APPPATH, '/') . '/third_party/phpseclib/';
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
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Detect RSA class
$PHPSECLIB_RSA_CLASS = null;
if (class_exists('\\phpseclib\\Crypt\\RSA')) {
    $PHPSECLIB_RSA_CLASS = '\\phpseclib\\Crypt\\RSA';
} elseif (class_exists('\\phpseclib3\\Crypt\\RSA')) {
    $PHPSECLIB_RSA_CLASS = '\\phpseclib3\\Crypt\\RSA';
}
if ($PHPSECLIB_RSA_CLASS === null) {
    log_message('error', 'phpseclib RSA class not found.');
    show_error('phpseclib RSA class not found. Please install phpseclib.');
}

class Addons extends CI_Controller
{
    private $rsaClass;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Addon_model');
        $this->load->model('UserModel');
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
        $this->config->load('payment');

        global $PHPSECLIB_RSA_CLASS;
        $this->rsaClass = $PHPSECLIB_RSA_CLASS;

        // Only skip login check for these methods
        $public_methods = ['mytNotify', 'mytCallback', 'status'];

        if (!in_array($this->router->fetch_method(), $public_methods)) {
            if (!$this->session->userdata('LoginID')) {
                redirect(BASE_URL);
            }
        }
    }

    public function index()
    {
        $addons = $this->Addon_model->get_addons_with_services();

        $data['addon_data'] = [];
        foreach ($addons as $row) {
            $data['addon_data'][$row['category_name']][] = $row;
        }

        $this->load->view('addons/index', $data);
    }

    public function buy($service_id)
    {
        $publisher_id = $this->session->userdata('LoginID');
        $qty = $this->input->post('qty');
        $qty = (!empty($qty) && $qty > 0) ? $qty : 1;

        $service = $this->Addon_model->get_service_by_id($service_id);
        if (!$service) {
            show_error('Invalid service.');
        }

        //$amount = $service['price'] * $qty;
   $amount = 1;
        // Save purchase
        $purchase = [
            'merchant_id' => $publisher_id,
            'service_id'  => $service_id,
            'qty'         => $qty,
            'status'      => 'pending',
            'created_at'  => date('Y-m-d H:i:s'),
            'ip'          => $this->input->ip_address()
        ];
        $this->db->insert('merchant_addon_purchases', $purchase);
        $purchase_id = $this->db->insert_id();

        // Create a transaction
        $transaction = [
            'purchase_id' => $purchase_id,
            'merchant_id' => $publisher_id,
            'amount'      => $amount,
            'status'      => 'initiated',
            'created_at'  => date('Y-m-d H:i:s')
        ];
        $this->db->insert('merchant_addon_transactions', $transaction);
        $transaction_id = $this->db->insert_id();

        // Start secure payment
        $this->initiate_payment($transaction_id, $amount);
    }

    /**
     * Initiate secure my.t money payment (RSA + HMAC)
     */
    private function initiate_payment($transaction_id, $amount)
    {
        $cfg = $this->config->item('myt_money');

        $merchantAppId = $cfg['app_id'];
        $apiKey        = $cfg['api_key'];
        $publicKey     = $cfg['public_key'];
        $notifyUrl     = site_url('addons/mytNotify'); // should point to addons/mytNotify
        $returnUrl     = site_url('addons/mytCallback'); // frontend return
        $mode          = $cfg['mode'] ?? 'production';

        // Create unique trade number
        $merTradeNo = (string)(microtime(true) * 1000);

        // Persist mer_trade_no to transaction (ensure column exists)
        $this->db->where('id', $transaction_id)
                 ->update('merchant_addon_transactions', ['mer_trade_no' => $merTradeNo, 'updated_at' => date('Y-m-d H:i:s')]);

        // Prepare payload
        $payload = [
            "totalPrice" => $amount,
            "currency"   => "MUR",
            "merTradeNo" => $merTradeNo,
            "notifyUrl"  => $notifyUrl,
            "returnUrl"  => $returnUrl,
            "remark"     => "Addon Purchase",
            "lang"       => "en"
        ];

        $payloadJson = json_encode($payload);

        // Encrypt payload using RSA public key
        $rsa = new $this->rsaClass();
        // Use OAEP if available
        if (defined($this->rsaClass . '::ENCRYPTION_OAEP')) {
            $rsa->setEncryptionMode($this->rsaClass::ENCRYPTION_OAEP);
        }
        $rsa->loadKey($publicKey);
        $encryptedPayload = base64_encode($rsa->encrypt($payloadJson));

        // Create HMAC signature
        $paymentType = "S";
        $signatureData = "appId={$merchantAppId}&merTradeNo={$merTradeNo}&payload={$encryptedPayload}&paymentType={$paymentType}";
        $sign = base64_encode(hash_hmac('sha512', $signatureData, $apiKey, true));

        // Gateway URL (sandbox or production)
        $gateway_url = ($mode === 'sandbox')
            ? "https://pay.sandbox.mytmoney.mu/Mt/web/payments"
            : "https://pay.mytmoney.mu/Mt/web/payments";

        log_message('info', "Addon Payment Payload: {$payloadJson}");
        log_message('info', "Addon Encrypted Payload: {$encryptedPayload}");
        log_message('info', "Addon Payment Signature: {$sign}");
        log_message('info', "Addon Gateway URL: {$gateway_url}");

        // Auto-submit secure payment form
        echo '<html><body>';
        echo '<form id="mytForm" method="POST" action="' . $gateway_url . '">';
        echo '<input type="hidden" name="appId" value="' . htmlspecialchars($merchantAppId) . '">';
        echo '<input type="hidden" name="merTradeNo" value="' . htmlspecialchars($merTradeNo) . '">';
        echo '<input type="hidden" name="payload" value="' . htmlspecialchars($encryptedPayload) . '">';
        echo '<input type="hidden" name="paymentType" value="' . htmlspecialchars($paymentType) . '">';
        echo '<input type="hidden" name="sign" value="' . htmlspecialchars($sign) . '">';
        echo '<p>Redirecting to payment gateway...</p>';
        echo '<script>document.getElementById("mytForm").submit();</script>';
        echo '</body></html>';
    }

    /**
     * Legacy-ish: callback used previously by code (kept for compatibility)
     * You may keep or remove this; initiate_payment now points to addons/mytCallback
     */
    public function payment_callback($transaction_id)
    {
        $response = $this->input->post();

        // Log response
        $this->db->where('id', $transaction_id)
                 ->update('merchant_addon_transactions', [
                     'gateway_response' => json_encode($response),
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);

        $status = (!empty($response['status']) && strtolower($response['status']) == 'success') ? 'success' : 'failed';
        $this->db->where('id', $transaction_id)->update('merchant_addon_transactions', ['status' => $status]);

        // Update purchase status
        $transaction = $this->db->get_where('merchant_addon_transactions', ['id' => $transaction_id])->row();
        if ($transaction && $status == 'success') {
            $this->db->where('id', $transaction->purchase_id)
                     ->update('merchant_addon_purchases', ['status' => 'paid']);
            $this->session->set_flashdata('success', 'Payment successful! Add-on activated.');
        } else {
            $this->db->where('id', $transaction->purchase_id)
                     ->update('merchant_addon_purchases', ['status' => 'cancelled']);
            $this->session->set_flashdata('error', 'Payment failed or cancelled.');
        }

        redirect('addons');
    }

    /**
     * Frontend callback (user redirected here after payment)
     * Expects merTradeNo in POST/GET from gateway
     */
    public function mytCallback()
    {
        log_message('info', '=== MyT Addon Callback Called ===');

        $merTradeNo = $this->input->get_post('merTradeNo') ?? null;

        if (!$merTradeNo) {
            log_message('error', 'Missing merTradeNo in Addon callback.');
            show_error('Invalid payment reference. Missing merTradeNo.', 400);
            return;
        }

        // Fetch transaction
        $transaction = $this->db->get_where('merchant_addon_transactions', ['mer_trade_no' => $merTradeNo])->row_array();
        if (!$transaction) {
            log_message('error', 'Addon transaction not found for merTradeNo: ' . $merTradeNo);
            show_error('Transaction not found.', 404);
            return;
        }

        // Redirect to Addon payment status page using encrypted token
        $this->load->library('encryption');
        $encrypted = $this->encryption->encrypt($transaction['id']);
        $token = rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
         $frontendUrl = base_url("addons/status/{$token}");
        redirect($frontendUrl);
    }

    /**
     * Server-to-server notify endpoint for MyT Money
     * MyT should call this in your merchant settings
     */
    public function mytNotify()
    {
        log_message('info', '=== MyT Addon Notify Called ===');

        // GET data from My.T Money notify callback
        $data = $this->input->get();
        log_message('info', 'MyT Addon Notify Data: ' . print_r($data, true));

        $this->_handleMytAddonResponse(true, $data);
    }

    /**
     * Shared handler to process responses from MyT (notify & callback can reuse)
     *
     * @param bool $isServerCall
     * @param array|null $overrideData
     * @return void|string JSON if server call
     */
    private function _handleMytAddonResponse($isServerCall = true, $overrideData = null)
    {
        $data = $overrideData ?? array_merge($this->input->get(), $this->input->post());
        log_message('info', '=== MyT Addon Response Data Processed === ' . print_r($data, true));

        if (empty($data)) {
            log_message('error', 'MyT Addon Notify: Empty payload');
            if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Empty data']);
            return;
        }

        $merTradeNo = $data['merTradeNo'] ?? null;
        if (!$merTradeNo) {
            log_message('error', 'MyT Addon Notify: Missing merTradeNo');
            if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Missing merTradeNo']);
            return;
        }

        // Fetch related transaction
        $transaction = $this->db->get_where('merchant_addon_transactions', ['mer_trade_no' => $merTradeNo])->row_array();
        if (!$transaction) {
            log_message('error', "Addon Transaction not found for merTradeNo: $merTradeNo");
            if ($isServerCall) return $this->_sendJson(['status' => 'fail', 'message' => 'Transaction not found']);
            return;
        }

        $tradeStatus = strtoupper($data['tradeStatus'] ?? '');
        $errorCode   = $data['errorCode'] ?? '';
        $timestamp   = $data['timestamp'] ?? '';

        // Determine final payment status
        if ($tradeStatus === 'TRADE_FINISHED' || ($data['resultCode'] ?? '') === '0') {
            $status = 'success';
        } else {
            $status = 'failed';
        }

        // Update transaction table
        $updateData = [
            'status'            => $status,
            'transaction_ref'   => $data['tradeNo'] ?? null,
            'gateway_response'  => json_encode($data),
            'updated_at'        => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $transaction['id'])->update('merchant_addon_transactions', $updateData);

        // Update related purchase record
        $purchaseStatus = ($status === 'success') ? 'paid' : 'cancelled';
        $this->db->where('id', $transaction['purchase_id'])->update('merchant_addon_purchases', ['status' => $purchaseStatus]);

        if ($status === 'success') {
            log_message('info', "✅ MyT Addon Payment SUCCESS → Txn ID: {$transaction['id']} | merTradeNo: {$merTradeNo}");
        } else {
            log_message('error', "❌ MyT Addon Payment FAILED → Txn ID: {$transaction['id']} | tradeStatus: {$tradeStatus}");
        }

        if ($isServerCall) return $this->_sendJson(['status' => $status, 'message' => 'Addon payment processed']);
    }

    /**
     * User-facing status page for addon payment
     * URL: /addons/status/{token}
     * token is base64url( encrypted(transaction_id) )
     */
    /*public function status($token = null)
    {
        if (!$token) show_error('Invalid request.', 400);

        $this->load->library('encryption');

        // decode token
        $decoded = base64_decode(strtr($token, '-_', '+/'));
        if ($decoded === false) {
            show_error('Invalid token.', 400);
            return;
        }

        $transaction_id = $this->encryption->decrypt($decoded);
        if (!$transaction_id) {
            show_error('Invalid token or expired.', 400);
            return;
        }

        $txn = $this->db->get_where('merchant_addon_transactions', ['id' => $transaction_id])->row_array();
        if (!$txn) show_error('Transaction not found.');

        $data['transaction'] = $txn;
        $data['purchase'] = $this->db->get_where('merchant_addon_purchases', ['id' => $txn['purchase_id']])->row_array();

        // Load a simple view — create application/views/addons/payment_status.php
        $this->load->view('addons/payment_status', $data);
    }*/


public function status($token = null)
{
    if (!$token) show_error('Invalid request.', 400);

    $this->load->library('encryption');

    // Decode token
    $decoded = base64_decode(strtr($token, '-_', '+/'));
    if ($decoded === false) {
        show_error('Invalid token.', 400);
        return;
    }

    $transaction_id = $this->encryption->decrypt($decoded);
    if (!$transaction_id) {
        show_error('Invalid token or expired.', 400);
        return;
    }

    // Fetch transaction and purchase
    $txn = $this->db->get_where('merchant_addon_transactions', ['id' => $transaction_id])->row_array();
    if (!$txn) show_error('Transaction not found.');

    $purchase = $this->db->get_where('merchant_addon_purchases', ['id' => $txn['purchase_id']])->row_array();

    // ✅ AUTO-LOGIN LOGIC
    $merchant_id = $txn['merchant_id'] ?? null;
    if ($merchant_id && !$this->session->userdata('LoginID')) {
        $publisher = $this->db->get_where('publisher', ['id' => $merchant_id, 'status' => 1])->row();
        if ($publisher) {
            $LoginToken = bin2hex(random_bytes(16));
            $this->session->set_userdata([
                'LoginID'    => $publisher->id,
                'LoginToken' => $LoginToken
            ]);

            // Optional: track login session if same logic used elsewhere
            if (method_exists($this->UserModel, 'insertIntoLoginSession')) {
                $this->UserModel->insertIntoLoginSession($LoginToken, $publisher->id);
            }

            // Update last login
            $this->db->where('id', $publisher->id)
                     ->update('publisher', ['last_login_at' => time()]);

            log_message('info', "✅ Auto login for publisher ID {$publisher->id} ({$publisher->email}) during Addon status.");
        } else {
            log_message('error', "❌ Auto login failed for merchant ID {$merchant_id}");
        }
    }

    $data['transaction'] = $txn;
    $data['purchase'] = $purchase;

    $this->load->view('addons/payment_status', $data);
}


    /**
     * Helper for consistent JSON response
     */
    private function _sendJson($response)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function details($service_id)
    {
        $service = $this->Addon_model->get_service_by_id($service_id);
        if (!$service) show_404();
        $data['service'] = $service;
        $this->load->view('addons/service_details', $data);
    }
}
