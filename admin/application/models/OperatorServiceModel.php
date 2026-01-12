<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorServiceModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get operator service operations
     */
    public function get_operations($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_service_operations');
        
        if ($query->num_rows() > 0) {
            $operations = $query->row();
            // Decode JSON fields
            if (!empty($operations->service_types)) {
                $operations->service_types = json_decode($operations->service_types, TRUE);
            }
            if (!empty($operations->operating_days)) {
                $operations->operating_days = json_decode($operations->operating_days, TRUE);
            }
            if (!empty($operations->access_rights)) {
                $operations->access_rights = json_decode($operations->access_rights, TRUE);
            }
            return $operations;
        }
        return FALSE;
    }

    /**
     * Save service operations
     */
    public function save_operations($operator_id, $data) {
        try {
            // Check if record exists
            $this->db->where('operator_id', $operator_id);
            $existing = $this->db->get('operator_service_operations');
            
            // Encode JSON fields
            if (isset($data['service_types']) && is_array($data['service_types'])) {
                $data['service_types'] = json_encode($data['service_types']);
            }
            if (isset($data['operating_days']) && is_array($data['operating_days'])) {
                $data['operating_days'] = json_encode($data['operating_days']);
            }
            if (isset($data['access_rights']) && is_array($data['access_rights'])) {
                $data['access_rights'] = json_encode($data['access_rights']);
            }
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            if ($existing->num_rows() > 0) {
                // Update existing
                $this->db->where('operator_id', $operator_id);
                return $this->db->update('operator_service_operations', $data);
            } else {
                // Create new
                $data['operator_id'] = $operator_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                return $this->db->insert('operator_service_operations', $data);
            }
        } catch (Exception $e) {
            log_message('error', 'OperatorServiceModel::save_operations - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Check if service operations are complete
     */
    public function is_complete($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_service_operations');
        
        if ($query->num_rows() > 0) {
            $operations = $query->row();
            if (!empty($operations->service_types) && 
                !empty($operations->operating_days) &&
                !empty($operations->support_contact)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Get service types
     */
    public function get_service_types($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_service_operations');
        
        if ($query->num_rows() > 0) {
            $operations = $query->row();
            if (!empty($operations->service_types)) {
                return json_decode($operations->service_types, TRUE);
            }
        }
        return array();
    }

    /**
     * Get operating days
     */
    public function get_operating_days($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_service_operations');
        
        if ($query->num_rows() > 0) {
            $operations = $query->row();
            if (!empty($operations->operating_days)) {
                return json_decode($operations->operating_days, TRUE);
            }
        }
        return array();
    }

    /**
     * Delete operations record
     */
    public function delete_operations($operator_id) {
        $this->db->where('operator_id', $operator_id);
        return $this->db->delete('operator_service_operations');
    }
}
