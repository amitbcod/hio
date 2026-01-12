<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Proxy to AuthHelper class if present, otherwise provide fallback implementations
if (file_exists(__DIR__ . '/AuthHelper.php')) {
    require_once __DIR__ . '/AuthHelper.php';
}

if (!function_exists('require_login')) {
    function require_login() {
        $CI =& get_instance();
        if (!empty($CI) && $CI->load) {
            if (class_exists('AuthHelper')) {
                return AuthHelper::require_login();
            }
            if (!$CI->session->userdata('is_logged_in')) {
                $CI->session->set_flashdata('error', 'Please login to access this page.');
                redirect(base_url('auth/login'));
            }
        }
    }
}

if (!function_exists('require_not_logged_in')) {
    function require_not_logged_in() {
        $CI =& get_instance();
        if (class_exists('AuthHelper')) {
            return AuthHelper::require_not_logged_in();
        }
        if ($CI->session->userdata('is_logged_in')) {
            redirect(base_url('operator'));
        }
    }
}

if (!function_exists('get_operator_id')) {
    function get_operator_id() {
        $CI =& get_instance();
        if (class_exists('AuthHelper')) {
            return AuthHelper::get_operator_id();
        }
        return $CI->session->userdata('operator_id');
    }
}

if (!function_exists('get_operator_email')) {
    function get_operator_email() {
        $CI =& get_instance();
        if (class_exists('AuthHelper')) {
            return AuthHelper::get_operator_email();
        }
        return $CI->session->userdata('email');
    }
}

if (!function_exists('get_operator_name')) {
    function get_operator_name() {
        $CI =& get_instance();
        if (class_exists('AuthHelper')) {
            return AuthHelper::get_operator_name();
        }
        return $CI->session->userdata('full_name');
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        $CI =& get_instance();
        if (class_exists('AuthHelper')) {
            return AuthHelper::is_admin();
        }
        return $CI->session->userdata('user_type') === 'Admin';
    }
}

if (!function_exists('redirect_login')) {
    function redirect_login($message = '') {
        $CI =& get_instance();
        if (class_exists('AuthHelper')) {
            return AuthHelper::redirect_login($message);
        }
        if (!empty($message)) {
            $CI->session->set_flashdata('error', $message);
        }
        redirect(base_url('auth/login'));
    }
}
