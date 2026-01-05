<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_model extends CI_Model {

    public function get_plans() {
        return $this->db->get_where('subscription_plans', ['status' => 1])->result_array();
    }

    public function get_features() {
        return $this->db->get('subscription_features')->result_array();
    }

    public function get_plan_features() {
        $this->db->select('plan_features.plan_id, plan_features.feature_id, plan_features.value');
        $this->db->from('plan_features');
        return $this->db->get()->result_array();
    }

    public function get_active_plan($publisher_id) {
        return $this->db
            ->get_where('publisher_subscriptions', [
                'publisher_id' => $publisher_id,
                'status' => 'active'
            ])->row_array();
    }

    public function subscribe_plan($publisher_id, $plan_id, $ip) {
        $plan = $this->get_plan_by_id($plan_id);
        if (!$plan) return false;

        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($start_date)));

        $existing = $this->db->get_where('publisher_subscriptions', [
            'publisher_id' => $publisher_id,
            'status' => 'active'
        ])->row_array();

        if ($existing) {
            // Expire current active subscription
            $this->db->where('id', $existing['id'])->update('publisher_subscriptions', ['status' => 'expired']);
        }

        $data = [
            'publisher_id' => $publisher_id,
            'plan_id'      => $plan_id,
            'status'       => 'active',
            'start_date'   => $start_date,
            'end_date'     => $end_date,
            'ip'           => $ip,
            'created_at'   => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('publisher_subscriptions', $data);

        return true;
    }

    public function get_plan_by_id($plan_id) {
        return $this->db->get_where('subscription_plans', ['id' => $plan_id])->row_array();
    }

    // New helper: fetch order by merchant trade number
    public function get_order_by_mer_trade_no($mer_trade_no) {
        return $this->db->get_where('subscription_orders', ['mer_trade_no' => $mer_trade_no])->row_array();
    }

    // New helper: update order status
    public function update_order_status($order_id, $status) {
        return $this->db->where('id', $order_id)->update('subscription_orders', ['status' => $status]);
    }
}
