<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Register new operator account
     */
    public function register($data) {
        try {
            // Generate unique operator_id
            $operator_id = $this->generate_operator_id();

            $insert_data = array(
                'operator_id' => $operator_id,
                'user_type' => $data['user_type'],
                'is_owner' => $data['is_owner'],
                'business_legal_name' => isset($data['business_legal_name']) ? $data['business_legal_name'] : '',
                'email' => $data['email'],
                'phone' => $data['phone'],
                'full_name' => $data['full_name'],
                'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                'account_status' => 'pending_verification',
                'operator_approve_flag' => 0,
                'registration_status' => 'in_progress',
                'current_step' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            // If not owner, add owner information to the same record
            if ($data['is_owner'] == 'no' && isset($data['owner_full_name'])) {
                $insert_data['owner_full_name'] = $data['owner_full_name'];
                $insert_data['owner_email'] = $data['owner_email'];
                $insert_data['owner_phone'] = $data['owner_phone'];
            }

            // Insert main operator record
            if ($this->db->insert('operators', $insert_data)) {
                // Insert registration progress tracking
                $this->db->insert('operator_registration_progress', array(
                    'operator_id' => $operator_id,
                    'current_step' => 1,
                    'step1_password' => TRUE,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ));

                // Create initial status review record
                $this->db->insert('operator_status_review', array(
                    'operator_id' => $operator_id,
                    'account_status' => 'Pending',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ));

                return $operator_id;
            }
            return FALSE;
        } catch (Exception $e) {
            log_message('error', 'AuthModel::register() - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Authenticate operator login
     */
    public function authenticate($email, $password) {
        try {
            $this->db->where('email', $email);
            $this->db->where('account_status !=', 'archived');
            $query = $this->db->get('operators');

            if ($query->num_rows() > 0) {
                $operator = $query->row();
                
                // Verify password
                if (password_verify($password, $operator->password_hash)) {
                    // Check if operator is approved
                    if (isset($operator->operator_approve_flag) && $operator->operator_approve_flag == 0) {
                        return 'not_approved';
                    }
                    
                    // Update last login (needs to be added to operators table)
                    $this->db->where('id', $operator->id);
                    $this->db->update('operators', array('updated_at' => date('Y-m-d H:i:s')));
                    
                    return $operator;
                }
            }
            return FALSE;
        } catch (Exception $e) {
            log_message('error', 'AuthModel::authenticate() - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Check if email exists
     */
    public function email_exists($email) {
        $this->db->where('email', $email);
        $query = $this->db->get('operators');
        return $query->num_rows() > 0;
    }

    /**
     * Get operator by ID
     */
    public function get_operator($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operators');
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return FALSE;
    }

    /**
     * Generate unique operator ID
     * Format: OP{timestamp}{random}
     */
    public function generate_operator_id() {
        $timestamp = substr(microtime(TRUE) * 10000, 0, 10);
        $random = strtoupper(substr(md5(microtime()), 0, 4));
        $operator_id = 'OP' . $timestamp . $random;
        
        // Ensure uniqueness
        while ($this->id_exists($operator_id)) {
            $random = strtoupper(substr(md5(microtime()), 0, 4));
            $operator_id = 'OP' . $timestamp . $random;
        }
        
        return $operator_id;
    }

    /**
     * Check if operator ID exists
     */
    public function id_exists($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $query = $this->db->get('operators');
        return $query->num_rows() > 0;
    }

    /**
     * Update password
     */
    public function update_password($operator_id, $new_password) {
        try {
            $update_data = array(
                'password_hash' => password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]),
                'updated_at' => date('Y-m-d H:i:s')
            );
            
            $this->db->where('operator_id', $operator_id);
            return $this->db->update('operators', $update_data);
        } catch (Exception $e) {
            log_message('error', 'AuthModel::update_password() - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Validate email/password reset token
     */
    public function validate_reset_token($token, $email) {
        try {
            $this->db->where('email', $email);
            $this->db->where('password_reset_token', $token);
            $this->db->where('password_reset_expiry >', date('Y-m-d H:i:s'));
            $query = $this->db->get('operators');
            
            return $query->num_rows() > 0;
        } catch (Exception $e) {
            log_message('error', 'AuthModel::validate_reset_token() - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Generate password reset token
     */
    public function create_reset_token($email) {
        try {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+2 hours'));
            
            $update_data = array(
                'password_reset_token' => $token,
                'password_reset_expiry' => $expiry,
                'updated_at' => date('Y-m-d H:i:s')
            );
            
            $this->db->where('email', $email);
            if ($this->db->update('operators', $update_data)) {
                return $token;
            }
            return FALSE;
        } catch (Exception $e) {
            log_message('error', 'AuthModel::create_reset_token() - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Clear password reset token
     */
    public function clear_reset_token($email) {
        try {
            $update_data = array(
                'password_reset_token' => NULL,
                'password_reset_expiry' => NULL,
                'updated_at' => date('Y-m-d H:i:s')
            );
            
            $this->db->where('email', $email);
            return $this->db->update('operators', $update_data);
        } catch (Exception $e) {
            log_message('error', 'AuthModel::clear_reset_token() - ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Get operator by email
     */
    public function get_by_email($email) {
        $this->db->where('email', $email);
        $query = $this->db->get('operators');
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return FALSE;
    }
}
