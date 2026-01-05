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
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
        $this->config->load('payment');

        global $PHPSECLIB_RSA_CLASS;
        $this->rsaClass = $PHPSECLIB_RSA_CLASS;

        if (!$this->session->userdata('LoginID')) {
            redirect(BASE_URL);
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

        $amount = $service['price'] * $qty;

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
        $notifyUrl     = $cfg['notify_url'];
        $returnUrl     = site_url('addons/payment_callback/' . $transaction_id);
        $mode          = $cfg['mode'];

        // Create unique trade number
        $merTradeNo = (string)(microtime(true) * 1000);
        $this->db->where('id', $transaction_id)
                 ->update('merchant_addon_transactions', ['mer_trade_no' => $merTradeNo]);

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
        $rsa->setEncryptionMode($this->rsaClass::ENCRYPTION_OAEP);
        $rsa->loadKey($publicKey);
        $encryptedPayload = base64_encode($rsa->encrypt($payloadJson));

        // Create HMAC signature
        $paymentType = "S";
        $signatureData = "appId={$merchantAppId}&merTradeNo={$merTradeNo}&payload={$encryptedPayload}&paymentType={$paymentType}";
        $sign = base64_encode(hash_hmac('sha512', $signatureData, $apiKey, true));

        // Gateway URL
        $gateway_url = "https://pay.mytmoney.mu/Mt/web/payments";

        log_message('info', "Addon Payment Payload: {$payloadJson}");
        log_message('info', "Addon Encrypted Payload: {$encryptedPayload}");
        log_message('info', "Addon Payment Signature: {$sign}");

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

    public function details($service_id)
    {
        $service = $this->Addon_model->get_service_by_id($service_id);
        if (!$service) show_404();
        $data['service'] = $service;
        $this->load->view('addons/service_details', $data);
    }
}
