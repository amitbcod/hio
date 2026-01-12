<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorAgreementModel extends CI_Model {
    protected $table = 'operator_collaboration_agreements';

    public function get_agreement($operator_id) {
        return $this->db->where('operator_id', $operator_id)->get($this->table)->row();
    }

    public function save_agreement($data) {
        $existing = $this->get_agreement($data['operator_id']);
        if ($existing) {
            return $this->db->where('operator_id', $data['operator_id'])->update($this->table, $data);
        }
        return $this->db->insert($this->table, $data);
    }



    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get collaboration agreement
     */
    /*public function get_agreement($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_collaboration_agreements');
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return FALSE;
    }*/

    /**
     * Save collaboration agreement
     */
    /*public function save_agreement($operator_id, $data) {
        try {
            // Check if agreement exists
            $this->db->where('operator_id', $operator_id);
            $existing = $this->db->get('operator_collaboration_agreements');
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            if ($existing->num_rows() > 0) {
                // Update existing
                $this->db->where('operator_id', $operator_id);
                return $this->db->update('operator_collaboration_agreements', $data);
            } else {
                // Create new
                $data['operator_id'] = $operator_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                return $this->db->insert('operator_collaboration_agreements', $data);
            }
        } catch (Exception $e) {
            log_message('error', 'OperatorAgreementModel::save_agreement - ' . $e->getMessage());
            return FALSE;
        }
    }
*/
    /**
     * Check if agreement is complete
     */
    public function is_complete($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_collaboration_agreements');
        
        if ($query->num_rows() > 0) {
            $agreement = $query->row();
            if (!empty($agreement->agreement_status) && 
                $agreement->agreement_status === 'signed' &&
                !empty($agreement->agreement_effective_date)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Get agreement status
     */
    public function get_status($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_collaboration_agreements');
        
        if ($query->num_rows() > 0) {
            return $query->row()->agreement_status;
        }
        return 'pending';
    }

    /**
     * Update agreement status
     */
    public function update_status($operator_id, $status) {
        $data = array(
            'agreement_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        if ($status === 'signed') {
            $data['agreement_effective_date'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('operator_id', $operator_id);
        return $this->db->update('operator_collaboration_agreements', $data);
    }

    /**
     * Delete agreement record
     */
    public function delete_agreement($operator_id) {
        $this->db->where('operator_id', $operator_id);
        return $this->db->delete('operator_collaboration_agreements');
    }
}
