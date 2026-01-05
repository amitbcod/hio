<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Path where you placed phpseclib inside application/third_party/phpseclib/
$phpseclib_base = rtrim(APPPATH, '/') . '/third_party/phpseclib/';

// Register an autoloader that supports both phpseclib (v2) and phpseclib3 (v3)
spl_autoload_register(function ($class) use ($phpseclib_base) {
    $class = ltrim($class, '\\');
    $prefixes = ['phpseclib\\','phpseclib3\\'];

    foreach ($prefixes as $prefix) {
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) continue;
        $relative = str_replace('\\','/', substr($class, strlen($prefix)));
        $candidates = [
            $phpseclib_base . $relative . '.php',
            $phpseclib_base . 'src/' . $relative . '.php'
        ];
        foreach ($candidates as $file) {
            if (file_exists($file)) { require_once $file; return; }
        }
    }
});

// Detect RSA class (v2 or v3)
$PHPSECLIB_RSA_CLASS = null;
if (class_exists('\\phpseclib\\Crypt\\RSA')) $PHPSECLIB_RSA_CLASS = '\\phpseclib\\Crypt\\RSA';
elseif (class_exists('\\phpseclib3\\Crypt\\RSA')) $PHPSECLIB_RSA_CLASS = '\\phpseclib3\\Crypt\\RSA';
if ($PHPSECLIB_RSA_CLASS === null) {
    log_message('error', 'phpseclib RSA class not found. Check application/third_party/phpseclib installation.');
    show_error('phpseclib RSA class not found. Check application/third_party/phpseclib installation.');
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
    }

    // Start payment with My.T Money (2.0)
    public function mytMoney($order_id = null) {
        $order = $this->db->get_where('subscription_orders', ['id' => $order_id])->row_array();
        if (!$order) show_404();

        $this->config->load('payment');
        $cfg = $this->config->item('myt_money');

        $merchantAppId = $cfg['app_id'];
        $apiKey        = $cfg['api_key'];
        $publicKey     = $cfg['public_key'];
        $notifyUrl     = $cfg['notify_url'];
        $returnUrl     = $cfg['return_url'];
        $mode          = $cfg['mode'];

        $merTradeNo = (int)(microtime(true)*1000);

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

        // Encrypt payload using RSA
        $rsa = new $this->rsaClass();
        $rsa->setEncryptionMode($this->rsaClass::ENCRYPTION_OAEP);
        $rsa->loadKey($publicKey);
        $encryptedPayload = base64_encode($rsa->encrypt($payloadJson));

        // Generate HMAC signature
        $paymentType = "S";
        $signatureData = "appId={$merchantAppId}&merTradeNo={$merTradeNo}&payload={$encryptedPayload}&paymentType={$paymentType}";
        $sign = base64_encode(hash_hmac('sha512', $signatureData, $apiKey, true));

        $gatewayUrl = $mode === 'sandbox'
            ? "https://pay.sandbox.mytmoney.mu/Mt/web/payments"
            : "https://pay.mytmoney.mu/Mt/web/payments";

        echo '<html><body>';
        echo '<form id="mytForm" action="'.$gatewayUrl.'" method="POST">';
        echo '<input type="hidden" name="appId" value="'.$merchantAppId.'">';
        echo '<input type="hidden" name="merTradeNo" value="'.$merTradeNo.'">';
        echo '<input type="hidden" name="payload" value="'.$encryptedPayload.'">';
        echo '<input type="hidden" name="paymentType" value="'.$paymentType.'">';
        echo '<input type="hidden" name="sign" value="'.$sign.'">';
        echo '</form>';
        echo '<script>document.getElementById("mytForm").submit();</script>';
        echo '</body></html>';
    }

    // Callback/notify URL from My.T Money
    public function mytCallback() {
        $data = $this->input->post();

        $required = ['errorCode','tradeNo','merTradeNo','msg','tradeStatus','timestamp','sign'];
        foreach($required as $field) {
            if(!isset($data[$field])) show_error("Missing $field");
        }

        $merTradeNo = $data['merTradeNo'];
        $order = $this->db->get_where('subscription_orders', ['mer_trade_no' => $merTradeNo])->row_array();
        if (!$order) show_404();

        $this->config->load('payment');
        $cfg = $this->config->item('myt_money');
        $apiKey = $cfg['api_key'];

        $signString = "";
        $fields = ['merTradeNo','msg','errorCode','tradeNo','tradeStatus','timestamp','mf1','mf2','mf3','mf4','mf5'];
        foreach($fields as $f){
            $signString .= isset($data[$f]) ? "$f=".$data[$f]."&" : "$f=&";
        }
        $signString = rtrim($signString, "&");

        $generatedSignature = base64_encode(hash_hmac('sha256', $signString, $apiKey, true));

        if($generatedSignature !== $data['sign']) {
            $this->session->set_flashdata('error', 'Payment verification failed. Invalid signature.');
            $this->db->where('id', $order['id'])->update('subscription_orders', ['status' => 'failed']);
            redirect('subscription');
        }

        if(strtoupper($data['tradeStatus']) === 'SUCCESS') {
            $this->db->where('id', $order['id'])->update('subscription_orders', ['status' => 'paid']);
            $this->Subscription_model->subscribe_plan($order['publisher_id'], $order['plan_id'], $this->input->ip_address());
            $this->session->set_flashdata('success', 'Payment successful! Subscription activated.');
        } else {
            $this->db->where('id', $order['id'])->update('subscription_orders', ['status' => 'failed']);
            $this->session->set_flashdata('error', 'Payment failed or cancelled.');
        }

        redirect('subscription');
    }
}
