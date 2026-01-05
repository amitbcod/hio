<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Subscription_model');
        $this->load->library('session'); // publisher login stored in session
        	if(!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] ==''){
			redirect(BASE_URL);
		}
    }

    // Show plans and active plan
    public function index() {
        log_message('info', 'Subscription session data: ' . json_encode($this->session->userdata()));

        $publisher_id = $this->session->userdata('LoginID'); // corrected

        $data['plans'] = $this->Subscription_model->get_plans();
        $data['features'] = $this->Subscription_model->get_features();
        $data['plan_features'] = $this->Subscription_model->get_plan_features();
        $data['active_plan'] = $this->Subscription_model->get_active_plan($publisher_id);
     
        // Restructure plan_features for easy lookup
        $plan_features = [];
        foreach ($data['plan_features'] as $pf) {
            $plan_features[$pf['feature_id']][$pf['plan_id']] = $pf['value'];
        }
        $data['plan_features'] = $plan_features;

        $this->load->view('subscription_table', $data);
    }

    // Handle subscription request
    public function subscribe() {
        $publisher_id = $this->session->userdata('LoginID');
        $plan_id = $this->input->post('plan_id');

        if(!$publisher_id || !$plan_id) {
            $this->session->set_flashdata('error', 'Invalid request.');
            redirect('subscription');
        }

        // Get plan details
        $plan = $this->Subscription_model->get_plan_by_id($plan_id);
        if (!$plan) {
            $this->session->set_flashdata('error', 'Invalid plan selected.');
            redirect('subscription');
        }

        // Create a temporary order record before redirecting to My.T
        $orderData = [
            'publisher_id' => $publisher_id,
            'plan_id'      => $plan_id,
            'amount'       => $plan['price'],
            'status'       => 'pending',
            'created_at'   => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('subscription_orders', $orderData);
        $order_id = $this->db->insert_id();

        // Redirect to payment controller
        redirect('paymentGateway/mytMoney/'.$order_id);
    }

}
