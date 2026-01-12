<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorLegalModel extends CI_Model {
    protected $table = 'operator_legal_compliance';

    public function get_legal($operator_id) {
        return $this->db->where('operator_id', $operator_id)->get($this->table)->row();
    }

    public function save_legal($data) {
        $existing = $this->get_legal($data['operator_id']);
        if ($existing) {
            return $this->db->where('operator_id', $data['operator_id'])->update($this->table, $data);
        }
        return $this->db->insert($this->table, $data);
    }
//}

            
          /*  $data['updated_at'] = date('Y-m-d H:i:s');
            
            if ($existing->num_rows() > 0) {
                // Update existing
                $this->db->where('operator_id', $operator_id);
                return $this->db->update('operator_legal_compliance', $data);
            } else {
                // Create new
                $data['operator_id'] = $operator_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                return $this->db->insert('operator_legal_compliance', $data);
            }
        } catch (Exception $e) {
            log_message('error', 'OperatorLegalModel::save_legal - ' . $e->getMessage());
            return FALSE;
        }
    }*/

    /**
     * Check if legal compliance is complete
     */
    public function is_complete($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_legal_compliance');
        
        if ($query->num_rows() > 0) {
            $legal = $query->row();
            if (!empty($legal->business_license_number) && 
                !empty($legal->license_type) &&
                !empty($legal->license_expiry_date) &&
                !empty($legal->compliance_status)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Check license expiry
     */
    public function is_license_expired($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_legal_compliance');
        
        if ($query->num_rows() > 0) {
            $legal = $query->row();
            $expiry_date = strtotime($legal->license_expiry_date);
            return $expiry_date < time();
        }
        return FALSE;
    }

    /**
     * Get license expiry date
     */
    public function get_license_expiry_date($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_legal_compliance');
        
        if ($query->num_rows() > 0) {
            return $query->row()->license_expiry_date;
        }
        return NULL;
    }

    /**
     * Delete legal record
     */
    public function delete_legal($operator_id) {
        $this->db->where('operator_id', $operator_id);
        return $this->db->delete('operator_legal_compliance');
    }
}
