<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorUserModel extends CI_Model {
    protected $table = 'operator_users';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all users for an operator
     */
    public function get_users($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $this->db->where_not_in('status', array('Suspended'));
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0 ? $query->result() : array();
    }

    /**
     * Get single user by ID
     */
    public function get_user($id) {
        $query = $this->db->where('id', $id)->get($this->table);
        return $query->num_rows() > 0 ? $query->row() : NULL;
    }

    /**
     * Get user by email
     */
    public function get_user_by_email($email) {
        $query = $this->db->where('email', $email)->get($this->table);
        return $query->num_rows() > 0 ? $query->row() : NULL;
    }

    /**
     * Check if email exists for operator (excluding specific user)
     */
    public function email_exists($operator_id, $email, $exclude_id = NULL) {
        $this->db->where('operator_id', $operator_id);
        $this->db->where('email', $email);
        
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', $exclude_id);
        }
        
        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Add new user
     */
    public function add_user($operator_id, $data) {
        $insert_data = array(
            'operator_id' => $operator_id,
            'user_id' => $this->generate_user_id($operator_id),
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'mobile' => isset($data['mobile']) ? $data['mobile'] : NULL,
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'],
            'access_rights' => isset($data['access_rights']) ? json_encode($data['access_rights']) : NULL,
            'status' => 'Active',
            'account_reset_required' => isset($data['account_reset_required']) ? $data['account_reset_required'] : TRUE,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'created_by' => isset($data['created_by']) ? $data['created_by'] : NULL
        );
        
        return $this->db->insert($this->table, $insert_data);
    }

    /**
     * Update user
     */
    public function update_user($id, $data) {
        $update_data = array();
        
        if (isset($data['full_name'])) $update_data['full_name'] = $data['full_name'];
        if (isset($data['email'])) $update_data['email'] = $data['email'];
        if (isset($data['mobile'])) $update_data['mobile'] = $data['mobile'];
        if (isset($data['role'])) $update_data['role'] = $data['role'];
        if (isset($data['status'])) $update_data['status'] = $data['status'];
        if (isset($data['access_rights'])) $update_data['access_rights'] = json_encode($data['access_rights']);
        if (isset($data['password']) && !empty($data['password'])) {
            $update_data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        $update_data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->where('id', $id)->update($this->table, $update_data);
    }

    /**
     * Delete user
     */
    public function delete_user($id) {
        return $this->db->where('id', $id)->delete($this->table);
    }

    /**
     * Generate unique user ID
     */
    private function generate_user_id($operator_id) {
        $prefix = 'USR_' . strtoupper(substr($operator_id, 0, 3));
        $timestamp = substr(time(), -6);
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        $user_id = $prefix . '_' . $timestamp . $random;
        
        // Check for collision
        if ($this->db->where('user_id', $user_id)->count_all_results($this->table) > 0) {
            return $this->generate_user_id($operator_id);
        }
        
        return $user_id;
    }
}

/*defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorUserModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Get all users for an operator
  
    public function get_users($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $this->db->where('status', 'active');
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('operator_users');
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return array();
    }

    // Get user by ID
     
    public function get_user($user_id) {
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('operator_users');
        
        if ($query->num_rows() > 0) {
            $user = $query->row();
            if (!empty($user->permissions)) {
                $user->permissions = json_decode($user->permissions, TRUE);
            }
            return $user;
        }
        return FALSE;
    }

    // Add new user to operator
     
    public function add_user($operator_id, $data) {
        try {
            // Generate unique user_id
            $data['user_id'] = $this->generate_user_id($operator_id);
            $data['operator_id'] = $operator_id;
            $data['status'] = 'active';
            $data['created_at'] = date('Y-m-d H:i:s');
            
            // Encode permissions if array
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $data['permissions'] = json_encode($data['permissions']);
            }
            
            return $this->db->insert('operator_users', $data);
        } catch (Exception $e) {
            log_message('error', 'OperatorUserModel::add_user - ' . $e->getMessage());
            return FALSE;
        }
    }

    // Update user
     
    public function update_user($user_id, $data) {
        try {
            // Encode permissions if array
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $data['permissions'] = json_encode($data['permissions']);
            }
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $this->db->where('user_id', $user_id);
            return $this->db->update('operator_users', $data);
        } catch (Exception $e) {
            log_message('error', 'OperatorUserModel::update_user - ' . $e->getMessage());
            return FALSE;
        }
    }

    // Delete user (soft delete)
    
    public function delete_user($user_id) {
        $data = array(
            'status' => 'inactive',
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $this->db->where('user_id', $user_id);
        return $this->db->update('operator_users', $data);
    }

    // Get user count for operator
   
    public function get_user_count($operator_id) {
        $this->db->where('operator_id', $operator_id);
        $this->db->where('status', 'active');
        return $this->db->count_all_results('operator_users');
    }

    // Generate unique user ID
     
    private function generate_user_id($operator_id) {
        $prefix = 'USR_' . strtoupper(substr($operator_id, 0, 3));
        $timestamp = substr(time(), -6);
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        $user_id = $prefix . '_' . $timestamp . $random;
        
        // Check for collision
        $this->db->where('user_id', $user_id);
        if ($this->db->count_all_results('operator_users') > 0) {
            return $this->generate_user_id($operator_id);
        }
        
        return $user_id;
    }

    //Check if user email is unique within operator
     
    public function email_exists($operator_id, $email, $exclude_user_id = NULL) {
        $this->db->where('operator_id', $operator_id);
        $this->db->where('user_email', $email);
        $this->db->where('status', 'active');
        
        if ($exclude_user_id !== NULL) {
            $this->db->where('user_id !=', $exclude_user_id);
        }
        
        return $this->db->count_all_results('operator_users') > 0;
    }
}
*/