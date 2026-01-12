<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorModel extends CI_Model {

    private $table = 'operators';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Create a new operator registration record
     */
    public function create_registration($data) {
        if (!isset($data['operator_id'])) {
            $data['operator_id'] = $this->generate_operator_id();
        }
        
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Update operator registration record
     */
    public function update_registration($operator_id, $data) {
        $this->db->where('id', $operator_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Get operator registration by ID
     */
    public function get_operator($operator_id) {
        $this->db->where('id', $operator_id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    /**
     * Get operator by email
     */
    public function get_operator_by_email($email) {
        $this->db->where('registering_user_email', $email);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    /**
     * Generate unique operator ID
     */
    private function generate_operator_id() {
        $prefix = 'OP';
        $timestamp = time();
        $random = substr(uniqid(), -4);
        return $prefix . $timestamp . $random;
    }

    /**
     * Get all operators (for admin review)
     */
    public function get_all_operators($status = null, $limit = 50, $offset = 0) {
        if ($status) {
            $this->db->where('registration_status', $status);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get($this->table);
        return $query->result();
    }

    /**
     * Get count of operators by status
     */
    public function get_operators_count($status = null) {
        if ($status) {
            $this->db->where('registration_status', $status);
        }
        return $this->db->count_all_results($this->table);
    }

    /**
     * Approve operator registration
     */
    public function approve_operator($operator_id, $approved_by) {
        $data = array(
            'registration_status' => 'completed',
            'account_status' => 'active',
            'approval_status' => 'approved',
            'approved_by' => $approved_by,
            'approved_at' => date('Y-m-d H:i:s')
        );
        
        $this->db->where('id', $operator_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Reject operator registration
     */
    public function reject_operator($operator_id, $reason, $rejected_by) {
        $data = array(
            'registration_status' => 'completed',
            'account_status' => 'inactive',
            'approval_status' => 'rejected',
            'approved_by' => $rejected_by,
            'notes' => $reason,
            'approved_at' => date('Y-m-d H:i:s')
        );
        
        $this->db->where('id', $operator_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Suspend operator account
     */
    public function suspend_operator($operator_id, $reason) {
        $data = array(
            'account_status' => 'suspended',
            'notes' => $reason
        );
        
        $this->db->where('id', $operator_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Check if operator profile is complete
     */
    public function is_profile_complete($operator_id) {
        $this->db->select('id');
        $this->db->where('operator_id', $operator_id);
        $this->db->where('status', 'approved');
        $query = $this->db->get('operator_profiles');
        return $query->num_rows() > 0;
    }

    /**
     * Check if operator legal compliance is complete
     */
    public function is_legal_complete($operator_id) {
        $this->db->select('id');
        $this->db->where('operator_id', $operator_id);
        $this->db->where('compliance_status', 'verified');
        $query = $this->db->get('operator_legal_compliance');
        return $query->num_rows() > 0;
    }

    /**
     * Check if operator accounting is complete
     */
    public function is_accounting_complete($operator_id) {
        $this->db->select('id');
        $this->db->where('beneficiary_id', $operator_id);
        $this->db->where('status', 'active');
        $query = $this->db->get('operator_accounting_payouts');
        return $query->num_rows() > 0;
    }
}
