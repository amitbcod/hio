<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorStatusModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get operator status review
     */
    public function get_status_review($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_status_review');
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return FALSE;
    }

    /**
     * Get registration progress
     */
    public function get_registration_progress($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_registration_progress');
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return FALSE;
    }

    /**
     * Calculate overall completion percentage
     */
    public function calculate_completion_percentage($operator_id) {
        $sections = 8;
        $completed = 0;
        
        // Check each section
        $this->db->where('operator_id', $operator_id);
        if ($this->db->get('operator_profiles')->num_rows() > 0) $completed++;
        
        $this->db->where('operator_id', $operator_id);
        if ($this->db->get('operator_legal_compliance')->num_rows() > 0) $completed++;
        
        $this->db->where('operator_id', $operator_id);
        if ($this->db->get('operator_system_processes')->num_rows() > 0) $completed++;
        
        $this->db->where('operator_id', $operator_id);
        if ($this->db->get('operator_collaboration_agreements')->num_rows() > 0) $completed++;
        
        $this->db->where('operator_id', $operator_id);
        if ($this->db->get('operator_users')->num_rows() > 0) $completed++;
        
        $this->db->where('beneficiary_id', $operator_id);
        if ($this->db->get('operator_accounting_payouts')->num_rows() > 0) $completed++;
        
        $this->db->where('operator_id', $operator_id);
        if ($this->db->get('operator_service_operations')->num_rows() > 0) $completed++;
        
        return round(($completed / $sections) * 100);
    }

    /**
     * Get section completion status
     */
    public function get_section_status($operator_id) {
        $status = array();
        
        // Profile
        $this->db->where('operator_id', $operator_id);
        $status['profile'] = $this->db->get('operator_profiles')->num_rows() > 0;
        
        // Legal
        $this->db->where('operator_id', $operator_id);
        $status['legal'] = $this->db->get('operator_legal_compliance')->num_rows() > 0;
        
        // System Process
        $this->db->where('operator_id', $operator_id);
        $status['system_process'] = $this->db->get('operator_system_processes')->num_rows() > 0;
        
        // Collaboration
        $this->db->where('operator_id', $operator_id);
        $status['collaboration'] = $this->db->get('operator_collaboration_agreements')->num_rows() > 0;
        
        // Users
        $this->db->where('operator_id', $operator_id);
        $status['users'] = $this->db->get('operator_users')->num_rows() > 0;
        
        // Accounting
        $this->db->where('beneficiary_id', $operator_id);
        $status['accounting'] = $this->db->get('operator_accounting_payouts')->num_rows() > 0;
        
        // Operations
        $this->db->where('operator_id', $operator_id);
        $status['operations'] = $this->db->get('operator_service_operations')->num_rows() > 0;
        
        return $status;
    }

    /**
     * Update status review notes
     */
    public function update_status_notes($operator_id, $notes) {
        $this->db->where('operator_id', $operator_id);
        $existing = $this->db->get('operator_status_review');
        
        $data = array(
            'notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        if ($existing->num_rows() > 0) {
            $this->db->where('operator_id', $operator_id);
            return $this->db->update('operator_status_review', $data);
        } else {
            $data['operator_id'] = $operator_id;
            $data['status'] = 'pending_review';
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert('operator_status_review', $data);
        }
    }

    /**
     * Update operator account status
     */
    public function update_account_status($operator_id, $status, $admin_notes = NULL) {
        $data = array(
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        if ($admin_notes !== NULL) {
            $data['admin_notes'] = $admin_notes;
        }
        
        $this->db->where('operator_id', $operator_id);
        return $this->db->update('operator_status_review', $data);
    }

    /**
     * Get accounts pending review
     */
    public function get_pending_review($limit = 20, $offset = 0) {
        $this->db->where('status', 'pending_review');
        $this->db->order_by('created_at', 'ASC');
        $this->db->limit($limit, $offset);
        return $this->db->get('operator_status_review')->result();
    }

    /**
     * Check if all required sections are completed
     */
    public function is_registration_complete($operator_id) {
        $section_status = $this->get_section_status($operator_id);
        
        // For now, require at least profile and legal to be completed
        return isset($section_status['profile']) && 
               isset($section_status['legal']) &&
               $section_status['profile'] && 
               $section_status['legal'];
    }
}
