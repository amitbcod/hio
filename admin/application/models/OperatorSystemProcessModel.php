<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorSystemProcessModel extends CI_Model {
    protected $table = 'operator_system_processes';

    public function get_system_process($operator_id) {
        return $this->db->where('operator_id', $operator_id)->get($this->table)->row();
    }

    public function save_system_process($data) {
        $existing = $this->get_system_process($data['operator_id']);
        if ($existing) {
            return $this->db->where('operator_id', $data['operator_id'])->update($this->table, $data);
        }
        return $this->db->insert($this->table, $data);
    }

//defined('BASEPATH') OR exit('No direct script access allowed');

//class OperatorSystemProcessModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get operator system processes
     */
    public function get_processes($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_system_processes');
        
        if ($query->num_rows() > 0) {
            $process = $query->row();
            // Decode JSON fields
            if (!empty($process->operating_system)) {
                $process->operating_system = json_decode($process->operating_system, TRUE);
            }
            if (!empty($process->integration_platforms)) {
                $process->integration_platforms = json_decode($process->integration_platforms, TRUE);
            }
            return $process;
        }
        return FALSE;
    }

    /**
     * Save system processes
     */
    public function save_processes($operator_id, $data) {
        try {
            // Check if record exists
            $this->db->where('operator_id', $operator_id);
            $existing = $this->db->get('operator_system_processes');
            
            // Encode JSON fields
            if (isset($data['operating_system']) && is_array($data['operating_system'])) {
                $data['operating_system'] = json_encode($data['operating_system']);
            }
            if (isset($data['integration_platforms']) && is_array($data['integration_platforms'])) {
                $data['integration_platforms'] = json_encode($data['integration_platforms']);
            }
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            if ($existing->num_rows() > 0) {
                // Update existing
                $this->db->where('operator_id', $operator_id);
                return $this->db->update('operator_system_processes', $data);
            } else {
                // Create new
                $data['operator_id'] = $operator_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                return $this->db->insert('operator_system_processes', $data);
            }
        } catch (Exception $e) {
            log_message('error', 'OperatorSystemProcessModel::save_processes - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Check if system processes are complete
     */
    public function is_complete($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_system_processes');
        
        if ($query->num_rows() > 0) {
            $process = $query->row();
            if (!empty($process->service_category) && 
                !empty($process->communication_preference)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Delete system process record
     */
    public function delete_processes($operator_id) {
        $this->db->where('operator_id', $operator_id);
        return $this->db->delete('operator_system_processes');
    }
}
