<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorProfileModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get operator profile
     */
    public function get_profile($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_profiles');
        
        if ($query->num_rows() > 0) {
            $profile = $query->row();
            // Decode JSON fields
            if (!empty($profile->service_types)) {
                $profile->service_types = json_decode($profile->service_types, TRUE);
            }
            if (!empty($profile->operating_days)) {
                $profile->operating_days = json_decode($profile->operating_days, TRUE);
            }
            if (!empty($profile->operating_areas)) {
                $profile->operating_areas = json_decode($profile->operating_areas, TRUE);
            }
            
            // Fetch legal compliance data
            $this->db->where('operator_id', $operator_id);
            $legal_query = $this->db->get('operator_legal_compliance');
            if ($legal_query->num_rows() > 0) {
                $legal = $legal_query->row();
                // Merge legal data into profile object
                $profile->business_license_number = $legal->business_license_number;
                $profile->license_type = $legal->license_type;
                $profile->license_expiry_date = $legal->license_expiry_date;
                $profile->service_package = $legal->service_package;
                $profile->proof_of_license = $legal->proof_of_license;
                $profile->insurance_certificate = $legal->insurance_certificate;
                $profile->signed_agreement = $legal->signed_agreement;
            }
            
            // Keep individual contact and social fields as is (they're now separate columns)
            return $profile;
        }
        return FALSE;
    }

    /**
     * Save operator profile
     */
    public function save_profile($operator_id, $data) {
        try {
            // Check if profile exists
            $this->db->where('operator_id', $operator_id);
            $existing = $this->db->get('operator_profiles');
            
            // Encode JSON fields
            if (isset($data['service_types']) && is_array($data['service_types'])) {
                $data['service_types'] = json_encode($data['service_types']);
            }
            if (isset($data['operating_days']) && is_array($data['operating_days'])) {
                $data['operating_days'] = json_encode($data['operating_days']);
            }
            if (isset($data['operating_areas']) && is_array($data['operating_areas'])) {
                $data['operating_areas'] = json_encode($data['operating_areas']);
            }
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            if ($existing->num_rows() > 0) {
                // Update existing
                $this->db->where('operator_id', $operator_id);
                return $this->db->update('operator_profiles', $data);
            } else {
                // Create new
                $data['operator_id'] = $operator_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                return $this->db->insert('operator_profiles', $data);
            }
        } catch (Exception $e) {
            log_message('error', 'OperatorProfileModel::save_profile - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Check if profile is complete
     */
    public function is_complete($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operator_profiles');
        
        if ($query->num_rows() > 0) {
            $profile = $query->row();
            if (!empty($profile->business_legal_name) && 
                !empty($profile->registered_address) && 
                !empty($profile->operational_address) &&
                !empty($profile->contact_phone) &&
                !empty($profile->contact_email)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Delete profile
     */
    public function delete_profile($operator_id) {
        $this->db->where('operator_id', $operator_id);
        return $this->db->delete('operator_profiles');
    }
}
