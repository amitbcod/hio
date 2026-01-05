<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_model extends CI_Model {

    // Plans
    public function get_plans() {
        return $this->db->get('subscription_plans')->result_array();
    }

    public function get_plan($id) {
        return $this->db->get_where('subscription_plans', ['id'=>$id])->row_array();
    }

    public function add_plan($data) {
        return $this->db->insert('subscription_plans', $data);
    }

    public function update_plan($id, $data) {
        return $this->db->update('subscription_plans', $data, ['id'=>$id]);
    }

    public function delete_plan($id) {
        return $this->db->delete('subscription_plans', ['id'=>$id]);
    }

    // Features
    public function get_features() {
        return $this->db->get('subscription_features')->result_array();
    }

    public function get_feature($id) {
        return $this->db->get_where('subscription_features', ['id'=>$id])->row_array();
    }

    public function add_feature($data) {
        return $this->db->insert('subscription_features', $data);
    }

    public function update_feature($id, $data) {
        return $this->db->update('subscription_features', $data, ['id'=>$id]);
    }

    public function delete_feature($id) {
        return $this->db->delete('subscription_features', ['id'=>$id]);
    }

    // Plan Features Mapping
    public function get_plan_features() {
        return $this->db->get('plan_features')->result_array();
    }

    public function save_plan_features($features) {
        foreach($features as $feature_id => $plans) {
            foreach($plans as $plan_id => $value) {
                $exists = $this->db->get_where('plan_features', ['plan_id'=>$plan_id, 'feature_id'=>$feature_id])->row_array();
                if($exists) {
                    $this->db->update('plan_features', ['value'=>$value], ['id'=>$exists['id']]);
                } else {
                    $this->db->insert('plan_features', ['plan_id'=>$plan_id, 'feature_id'=>$feature_id, 'value'=>$value]);
                }
            }
        }
    }
}
