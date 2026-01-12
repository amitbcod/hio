<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthHelper {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Check if user is logged in
     * If not, redirect to login
     */
    public static function require_login() {
        $CI =& get_instance();
        
        if (!$CI->session->userdata('is_logged_in')) {
            $CI->session->set_flashdata('error', 'Please login to access this page.');
            redirect(base_url('auth/login'));
        }
    }

    /**
     * Check if user is logged in
     * If yes, redirect to dashboard
     */
    public static function require_not_logged_in() {
        $CI =& get_instance();
        
        if ($CI->session->userdata('is_logged_in')) {
            redirect(base_url('operator/dashboard'));
        }
    }

    /**
     * Get current logged in operator ID
     */
    public static function get_operator_id() {
        $CI =& get_instance();
        return $CI->session->userdata('operator_id');
    }

    /**
     * Get current logged in operator email
     */
    public static function get_operator_email() {
        $CI =& get_instance();
        return $CI->session->userdata('email');
    }

    /**
     * Get current logged in operator full name
     */
    public static function get_operator_name() {
        $CI =& get_instance();
        return $CI->session->userdata('full_name');
    }

    /**
     * Check if current user is admin
     */
    public static function is_admin() {
        $CI =& get_instance();
        return $CI->session->userdata('user_type') === 'Admin';
    }

    /**
     * Check if user can access module
     */
    public static function can_access($module, $permission = 'read') {
        $CI =& get_instance();
        
        $operator_id = $CI->session->userdata('operator_id');
        $user_type = $CI->session->userdata('user_type');
        
        if ($user_type === 'Admin') {
            return TRUE;
        }
        
        // For now, allow all operators to access their own data
        // In production, implement full permission matrix
        return TRUE;
    }

    /**
     * Redirect to login with message
     */
    public static function redirect_login($message = '') {
        $CI =& get_instance();
        
        if (!empty($message)) {
            $CI->session->set_flashdata('error', $message);
        }
        redirect(base_url('auth/login'));
    }
}
