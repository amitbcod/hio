<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorAccountingModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get operator accounting info
     */
    public function get_accounting($operator_id) {
        $this->db->where('beneficiary_id', $operator_id);
        $query = $this->db->get('operator_accounting_payouts');
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return FALSE;
    }

    /**
     * Save operator accounting info
     */
    public function save_accounting($operator_id, $data) {
        try {
            // Check if accounting record exists
            $this->db->where('beneficiary_id', $operator_id);
            $existing = $this->db->get('operator_accounting_payouts');
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            if ($existing->num_rows() > 0) {
                // Update existing
                $this->db->where('beneficiary_id', $operator_id);
                return $this->db->update('operator_accounting_payouts', $data);
            } else {
                // Create new
                $data['beneficiary_id'] = $operator_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                return $this->db->insert('operator_accounting_payouts', $data);
            }
        } catch (Exception $e) {
            log_message('error', 'OperatorAccountingModel::save_accounting - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Get payout history
     */
    public function get_payout_history($operator_id, $limit = 20, $offset = 0) {
        // table uses `beneficiary_id` and `created_at` per schema
        $this->db->where('beneficiary_id', $operator_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('operator_payout_history');
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return array();
    }

    /**
     * Get total payouts for operator
     */
    public function get_total_payouts($operator_id) {
        $this->db->select('COALESCE(SUM(payout_amount), 0) as total');
        $this->db->where('beneficiary_id', $operator_id);
        $query = $this->db->get('operator_payout_history');
        
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        }
        return 0;
    }

    /**
     * Get total pending payouts
     */
    public function get_pending_payouts($operator_id) {
        $this->db->select('COALESCE(SUM(payout_amount), 0) as total');
        $this->db->where('beneficiary_id', $operator_id);
        // status field in schema is ENUM('Pending','Processing','Paid','Failed')
        $this->db->where('status', 'Pending');
        $query = $this->db->get('operator_payout_history');
        
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        }
        return 0;
    }

    /**
     * Check if accounting is complete
     */
    public function is_complete($operator_id) {
        $this->db->where('beneficiary_id', $operator_id);
        $query = $this->db->get('operator_accounting_payouts');
        
        if ($query->num_rows() > 0) {
            $accounting = $query->row();
            // Check required fields that were just saved
            if (!empty($accounting->bank_account_holder_name) && 
                !empty($accounting->bank_name) &&
                !empty($accounting->account_number) &&
                !empty($accounting->payment_schedule)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Get bank account details
     */
    public function get_bank_details($operator_id) {
        $this->db->where('beneficiary_id', $operator_id);
        $query = $this->db->get('operator_accounting_payouts');
        
        if ($query->num_rows() > 0) {
            $accounting = $query->row();
            return array(
                'holder_name' => $accounting->bank_account_holder_name,
                'bank_name' => $accounting->bank_name,
                'account_number' => $accounting->account_number,
                'currency' => $accounting->currency
            );
        }
        return FALSE;
    }

    /**
     * Delete accounting record
     */
    public function delete_accounting($operator_id) {
        $this->db->where('beneficiary_id', $operator_id);
        return $this->db->delete('operator_accounting_payouts');
    }
}
