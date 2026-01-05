<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Giftcards extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Giftcard_model');
        $this->load->helper('string');
        $this->config->load('payment');
    }

    // List gift card values
    public function index()
    {
        $data['values'] = $this->Giftcard_model->get_values();
        $this->load->view('giftcards/index', $data);
    }

    // Show purchase form for a value id
    public function purchase($value_id = null)
    {
        $user_id = $this->session->userdata('LoginID');
        if (!$user_id) redirect('customer/login');

        $value = $this->Giftcard_model->get_value((int)$value_id);
        if (!$value) show_404();

        $data['value'] = $value;
        $this->load->view('giftcards/purchase', $data);
    }

    // Process purchase request -> create pending order & redirect to payment
    public function processPurchase()
    {
        $user_id = $this->session->userdata('LoginID');
        if (!$user_id) {
            echo json_encode(['status' => 0, 'msg' => 'Please login first.']);
            return;
        }

        $value_id = (int)$this->input->post('value_id');
        $receiver_name = trim($this->input->post('receiver_name'));
        $receiver_email = trim($this->input->post('receiver_email'));
        $message = trim($this->input->post('message'));

        if (!$value_id || !$receiver_name || !$receiver_email) {
            echo json_encode(['status' => 0, 'msg' => 'Please fill required fields.']);
            return;
        }

        $value = $this->Giftcard_model->get_value($value_id);
        if (!$value) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid gift card value.']);
            return;
        }

        // Create order_number
        $order_number = 'GCORD-' . time() . '-' . strtoupper(random_string('alnum', 6));

        $order_data = [
            'order_number'   => $order_number,
            'user_id'        => $user_id,
            'value_id'       => $value->id,
            'amount'         => $value->amount,
            'receiver_name'  => $receiver_name,
            'receiver_email' => $receiver_email,
            'message'        => $message,
            'status'         => 1, // pending
            'created_at'     => date('Y-m-d H:i:s')
        ];

        $order_id = $this->Giftcard_model->create_order($order_data);

        if ($order_id) {
            $order_response = $this->Giftcard_model->get_order($order_id);
            $this->initMytTransaction($order_response);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'Failed to create order. Try again.']);
        }
    }

    // Initialize MyT Money transaction
    public function initMytTransaction($order_response)
    {
        log_message('info', '=== MyT Money Transaction Init ===');

        // phpseclib RSA init
        $phpseclib_base = rtrim(APPPATH, '/') . '/third_party/phpseclib/';
        static $rsaClass = null;
        if ($rsaClass === null) {
            spl_autoload_register(function ($class) use ($phpseclib_base) {
                $class = ltrim($class, '\\');
                foreach (['phpseclib\\', 'phpseclib3\\'] as $prefix) {
                    if (strncmp($prefix, $class, strlen($prefix)) !== 0) continue;
                    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
                    foreach ([$phpseclib_base . $relative . '.php', $phpseclib_base . 'src/' . $relative . '.php'] as $file) {
                        if (file_exists($file)) { require_once $file; return; }
                    }
                }
            });

            if (class_exists('\\phpseclib3\\Crypt\\RSA')) $rsaClass = '\\phpseclib3\\Crypt\\RSA';
            elseif (class_exists('\\phpseclib\\Crypt\\RSA')) $rsaClass = '\\phpseclib\\Crypt\\RSA';
            else show_error('phpseclib RSA class not found.');
        }

        $rsa = new $rsaClass();
        $rsa->setEncryptionMode($rsaClass::ENCRYPTION_OAEP);

        $order_id     = $order_response->id;
        $increment_id = $order_response->order_number;
        $amount       = 1; // or $order_response->amount
        $callbackUrl  = base_url('Giftcards/success/?key=' . base64_encode($increment_id));
        $notifyUrl    = base_url('Giftcards/mytNotify');

        // Insert transaction into new table
        $insertData = [
            'order_id'        => $order_id,
            'increment_id'    => $increment_id,
            'transaction_ref' => null,
            'amount'          => $amount,
            'currency'        => 'MUR',
            'status'          => 'initiated',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('giftcard_order_mytmoney_transactions', $insertData);
        $transaction_id = $this->db->insert_id();

        $cfg           = $this->config->item('myt_money');
        $merchantAppId = $cfg['app_id'];
        $apiKey        = $cfg['api_key'];
        $publicKey     = $cfg['public_key'];

        $merTradeNo = (string)(microtime(true) * 1000);
        $payload = [
            "totalPrice" => $amount,
            "currency"   => "MUR",
            "merTradeNo" => $merTradeNo,
            "notifyUrl"  => $notifyUrl,
            "returnUrl"  => $callbackUrl,
            "remark"     => "Gift Card #$increment_id",
            "lang"       => "en"
        ];
        $payloadJson = json_encode($payload);
        $rsa->loadKey($publicKey);
        $encryptedPayload = base64_encode($rsa->encrypt($payloadJson));

        $paymentType   = "S";
        $signatureData = "appId={$merchantAppId}&merTradeNo={$merTradeNo}&payload={$encryptedPayload}&paymentType={$paymentType}";
        $sign          = base64_encode(hash_hmac('sha512', $signatureData, $apiKey, true));

        // Update transaction
        $this->db->where('id', $transaction_id)->update('giftcard_order_mytmoney_transactions', [
            'mer_trade_no' => $merTradeNo,
            'payload'      => $payloadJson
        ]);

        // Redirect to gateway
        $gatewayUrl = "https://pay.mytmoney.mu/Mt/web/payments";
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

    // MyT Money notify callback
 // MyT Money notify callback (server-to-server)
public function mytNotify()
{
    // Gather notify data from GET or POST
    $notifyData = [
        'merTradeNo'   => $this->input->get('merTradeNo'),
        'msg'          => $this->input->get('msg'),
        'errorCode'    => $this->input->get('errorCode'),
        'tradeNo'      => $this->input->get('tradeNo'),
        'tradeStatus'  => $this->input->get('tradeStatus'),
        'timestamp'    => $this->input->get('timestamp'),
        'sign'         => $this->input->get('sign')
    ];

    log_message('info', 'Giftcard Notify GET data: ' . json_encode($notifyData));

    // Validate required fields
    if (empty($notifyData['merTradeNo']) || empty($notifyData['tradeStatus'])) {
        log_message('error', 'Invalid notify data received.');
        show_error('Invalid data', 400);
        return;
    }

    $order_number   = $notifyData['merTradeNo'];
    $payment_ref    = $notifyData['tradeNo'] ?? null;
    $status         = ($notifyData['tradeStatus'] === 'TRADE_FINISHED' && $notifyData['errorCode'] === '000')
                      ? 'success'
                      : 'failed';
    $payload        = json_encode($notifyData);

    // Update transaction record in giftcard_order_mytmoney_transactions
    $this->db->where('mer_trade_no', $order_number)
             ->update('giftcard_order_mytmoney_transactions', [
                 'response_payload' => $payload,
                 'status'           => $status,
                 'transaction_ref'  => $payment_ref,
                 'updated_at'       => date('Y-m-d H:i:s')
             ]);

    // Process successful payment
    if ($status === 'success') {
        $order = $this->Giftcard_model->get_order_by_number($order_number);

        if ($order && $order->status == 0) {
            $this->Giftcard_model->mark_order_paid($order->id, $payment_ref);
            $this->Giftcard_model->issue_giftcard($order->id);
            log_message('info', "Giftcard order marked as paid: OrderID {$order->id}, PaymentRef {$payment_ref}");
        } else {
            log_message('info', "Order not found or already processed: {$order_number}");
        }
    } else {
        log_message('error', "Giftcard payment failed: Order {$order_number}, Status {$notifyData['tradeStatus']}, ErrorCode {$notifyData['errorCode']}");
    }

    echo 'OK';
}

// Payment success page
public function success()
{
    $key = $this->input->get('key');
    if (!$key) show_404();

    $increment_id = base64_decode($key);

    // Fetch order
    $order = $this->Giftcard_model->get_order_by_number($increment_id);
    if (!$order) show_404();

    // Auto-login customer if not already logged in
    if (!$this->session->userdata('LoginID')) {

        // ✅ Fetch user details directly from 'customers' table
        $customer = $this->db
            ->select('id, first_name, last_name, email_id, customer_type_id, access_prelanch_product, allow_catlog_builder')
            ->from('customers')
            ->where('id', $order->user_id)
            ->get()
            ->row();

        if ($customer) {
            // Set session like in login()
            $sessionArr = array(
                'LoginID'        => $customer->id,
                'FirstName'      => $customer->first_name,
                'LastName'       => $customer->last_name,
                'EmailID'        => $customer->email_id,
                'CustomerTypeID' => $customer->customer_type_id
            );

            $this->session->set_userdata($sessionArr);

            // Optional: special feature access flags
            if (
                (isset($customer->access_prelanch_product) && $customer->access_prelanch_product == 1) ||
                (isset($customer->allow_catlog_builder) && $customer->allow_catlog_builder == 1)
            ) {
                $this->session->set_userdata('special_features', 1);
            }

            log_message('info', "Giftcard success(): Customer auto-logged in (ID: {$customer->id})");
        } else {
            log_message('error', "Giftcard success(): Customer not found for order user_id {$order->user_id}");
        }
    }

    // ✅ Check if payment transaction is successful
    $txn = $this->db
        ->select('status')
        ->from('giftcard_order_mytmoney_transactions')
        ->where('order_id', $order->id)
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get()
        ->row();

        echo "<pre>";
        print_r( $txn);

    // ✅ Issue gift card only if payment success and not already issued
    if (!$gift_card = $this->db->get_where('gift_cards', ['order_id' => $order->id])->row()) {

        echo "inside check exisst";
        if ($txn && strtolower($txn->status) === 'success') {
                echo "inside transcation check------------".$order->id.'--------';
            $gift_card = $this->Giftcard_model->issue_giftcard($order->id);
             echo "after issued";
            log_message('info', "Giftcard issued for OrderID {$order->id}");
        } else {
            log_message('info', "Giftcard not issued — transaction not successful for OrderID {$order->id}");
        }
    }

    exit;
    // ✅ Add credit transaction if missing
    if ($gift_card) {
        $exists = $this->db
            ->where('gift_card_id', $gift_card->id)
            ->where('type', 'credit')
            ->get('gift_card_transactions')
            ->row();

        if (!$exists) {
            $this->Giftcard_model->add_transaction(
                $order->user_id,
                $gift_card->id,
                'credit',
                $gift_card->balance,
                1
            );
        }
    }

    $data['order'] = $order;
    $data['gift_card'] = $gift_card;
    $this->load->view('giftcards/success', $data);
}


    // Payment failed page
    public function failed($order_id = null)
    {
        $order_number = null;
        if ($order_id) {
            $order = $this->Giftcard_model->get_order((int)$order_id);
            if ($order) $order_number = $order->order_number;
        }
        $data['order_number'] = $order_number;
        $this->load->view('giftcards/giftcard_failed', $data);
    }

    // Customer gift card balance & transactions
    public function mycards()
    {
        $user_id = $this->session->userdata('LoginID');
        if (!$user_id) redirect('login');

        $balance = $this->Giftcard_model->get_user_balance($user_id);
        $transactions = $this->Giftcard_model->get_user_transactions($user_id);
        $gift_cards = $this->Giftcard_model->get_user_giftcards($user_id);
        $gift_cards_map = [];
        foreach ($gift_cards as $gcard) {
            $gift_cards_map[$gcard->id] = $gcard;
        }

        $data['balance'] = $balance;
        $data['transactions'] = $transactions;
        $data['gift_cards_map'] = $gift_cards_map;

        $this->load->view('myprofile/my_cards', $data);
    }

    // Apply gift card to cart
    public function applyGiftCard()
    {
        $this->output->set_content_type('application/json');

        $gift_code  = trim($this->input->post('gift_code'));
        $session_id = trim($this->input->post('session_id'));
        $user_id = $this->session->userdata('LoginID') ?? 0;

        if (empty($gift_code) || empty($session_id) || $user_id == 0) {
            echo json_encode(['status'=>'error','message'=>'Gift code or session ID missing.']);
            return;
        }

        $result = $this->Giftcard_model->applyGiftCardToCart($gift_code, $session_id, $user_id);
        echo json_encode($result);
    }

    // Remove gift card from cart
    public function removeGiftCard()
    {
        $this->output->set_content_type('application/json');

        $gift_code  = $this->input->post('gift_code');
        $session_id = $this->input->post('session_id');
        $user_id    = $this->session->userdata('LoginID') ?? 0;

        if (empty($gift_code) || empty($session_id) || $user_id == 0) {
            echo json_encode(['status'=>'error','message'=>'Missing parameters']);
            return;
        }

        $result = $this->Giftcard_model->removeGiftCardFromCart($gift_code, $session_id, $user_id);
        echo json_encode($result);
    }
}
