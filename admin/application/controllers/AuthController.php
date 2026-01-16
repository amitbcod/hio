<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('AuthModel');
        $this->load->library('form_validation');
        $this->load->library('session');
    }

    /**
     * Show signup form
     */
    public function signup() {
        // If already logged in, redirect to dashboard
        if ($this->session->userdata('operator_id')) {
            redirect('dashboard');
        }

        $data = array();
        
        // Handle form submission
        if ($this->input->post()) {
            if ($this->validate_signup()) {
                if ($this->process_signup()) {
                    $is_owner = $this->input->post('is_owner');
                    if ($is_owner == 'no') {
                        $this->session->set_flashdata('success', 'Account created successfully! The owner will receive an email to verify their identity, accept the Terms & Conditions, and set an owner password before full access is granted.');
                    } else {
                        $this->session->set_flashdata('success', 'Account created successfully! The owner will receive an email to verify your identity and accept the Terms & Conditions before full access is granted.');
                    }
                    redirect('auth/login');
                } else {
                    $data['error'] = 'Registration failed. Please try again.';
                }
            } else {
                $data['errors'] = validation_errors('<p class="error">', '</p>');
            }
        }

        // Load auth signup view directly with layout
        $this->load->view('auth/signup', $data);
    }

    /**
     * Show login form
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if ($this->session->userdata('operator_id')) {
            redirect('dashboard');
        }

        $data = array();
        
        // Handle form submission
        if ($this->input->post()) {
            // Accept either `email`/`password` or `user_email`/`user_password` from views
            $email = $this->input->post('email', TRUE);
            if (empty($email)) {
                $email = $this->input->post('user_email', TRUE);
            }

            $password = $this->input->post('password');
            if (empty($password)) {
                $password = $this->input->post('user_password');
            }

            if (empty($email) || empty($password)) {
                $this->session->set_flashdata('error', 'Email and password are required.');
                redirect('auth/login');
            } else {
                $operator = $this->AuthModel->authenticate($email, $password);

                if ($operator === 'not_approved') {
                    $this->session->set_flashdata('error', 'Your account is not verified yet. Please try again later.');
                    redirect('auth/login');
                } else if ($operator) {
                    // Check account status
                    if ($operator->account_status == 'archived') {
                        $this->session->set_flashdata('error', 'Your account has been archived. Please contact support.');
                        redirect('auth/login');
                    } else if ($operator->account_status == 'suspended') {
                        $this->session->set_flashdata('error', 'Your account is currently suspended. Please contact support.');
                        redirect('auth/login');
                    } else {
                        // Set session data
                        $session_data = array(
                            'operator_id' => $operator->operator_id,
                            'email' => $operator->email,
                            'full_name' => $operator->full_name,
                            'user_type' => $operator->user_type,
                            'account_status' => $operator->account_status,
                            'is_logged_in' => TRUE
                        );
                        
                        $this->session->set_userdata($session_data);
                        
                        // Log successful login
                        log_message('info', 'Operator login successful: ' . $operator->operator_id);
                        
                        $this->session->set_flashdata('success', 'Welcome back, ' . $operator->full_name . '!');
                        // Redirect operators to profile page
                        redirect('operator/profile');
                    }
                } else {
                    $this->session->set_flashdata('error', 'Invalid email or password.');
                    log_message('info', 'Failed login attempt for email: ' . $email);
                    redirect('auth/login');
                }
            }
        }

        // Show login form
        $this->load->view('auth/login', $data);
    }

    /**
     * Logout
     */
    public function logout() {
        log_message('info', 'Operator logout: ' . $this->session->userdata('operator_id'));
        $this->session->sess_destroy();
        redirect('auth/login');
    }

    /**
     * Validate signup form
     */
    private function validate_signup() {
        // Common validation rules
        $this->form_validation->set_rules('user_type', 'Account Type', 'required|in_list[Operator,MPO,Agent]');
        $this->form_validation->set_rules('is_owner', 'Owner Status', 'required|in_list[yes,no]');
        $this->form_validation->set_rules('business_legal_name', 'Business Legal Name', 'required|min_length[3]|max_length[255]');
        $this->form_validation->set_rules('country_of_operation', 'Country of Operation', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/]');
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');
        $this->form_validation->set_rules('agree_terms', 'Terms Agreement', 'required');

        // If is_owner = yes, validate owner fields
        if ($this->input->post('is_owner') == 'yes') {
            $this->form_validation->set_rules('user_full_name', 'Full Name', 'required|min_length[3]|max_length[255]');
            $this->form_validation->set_rules('user_email', 'Email', 'required|valid_email|max_length[255]');
            $this->form_validation->set_rules('user_phone', 'Phone Number', 'required|regex_match[/^\+?[0-9\s\-\(\)]{7,20}$/]');
            
            // Check if email already exists for owner
            $email_to_check = $this->input->post('user_email');
            if (!empty($email_to_check) && $this->AuthModel->email_exists($email_to_check)) {
                $this->form_validation->set_message('user_email', 'This email is already registered.');
                return FALSE;
            }
        } else {
            // If is_owner = no, validate non-owner fields
            $this->form_validation->set_rules('non_owner_full_name', 'Full Name', 'required|min_length[3]|max_length[255]');
            $this->form_validation->set_rules('non_owner_email', 'Email', 'required|valid_email|max_length[255]');
            $this->form_validation->set_rules('non_owner_phone', 'Phone Number', 'required|regex_match[/^\+?[0-9\s\-\(\)]{7,20}$/]');
            $this->form_validation->set_rules('user_role', 'User Role', 'required');
            
            // Also validate owner information fields
            $this->form_validation->set_rules('owner_full_name', 'Owner Full Name', 'required|min_length[3]|max_length[255]');
            $this->form_validation->set_rules('owner_email', 'Owner Email', 'required|valid_email|max_length[255]');
            $this->form_validation->set_rules('owner_phone', 'Owner Phone Number', 'required|regex_match[/^\+?[0-9\s\-\(\)]{7,20}$/]');
            
            // Check if email already exists for non-owner
            $email_to_check = $this->input->post('non_owner_email');
            if (!empty($email_to_check) && $this->AuthModel->email_exists($email_to_check)) {
                $this->form_validation->set_message('non_owner_email', 'This email is already registered.');
                return FALSE;
            }
            
            // Check if owner email already exists
            $owner_email_to_check = $this->input->post('owner_email');
            if (!empty($owner_email_to_check) && $this->AuthModel->email_exists($owner_email_to_check)) {
                $this->form_validation->set_message('owner_email', 'This owner email is already registered.');
                return FALSE;
            }
        }

        return $this->form_validation->run();
    }

    /**
     * Process signup - create operator account
     */
    private function process_signup() {
        try {
            $is_owner = $this->input->post('is_owner');
            
            // Prepare data based on owner status
            if ($is_owner == 'yes') {
                $data = array(
                    'user_type' => $this->input->post('user_type'),
                    'is_owner' => 'yes',
                    'business_legal_name' => $this->input->post('business_legal_name'),
                    'country_of_operation' => $this->input->post('country_of_operation'),
                    'email' => $this->input->post('user_email'),
                    'phone' => $this->input->post('user_phone'),
                    'full_name' => $this->input->post('user_full_name'),
                    'password' => $this->input->post('password'),
                    'role' => 'Owner'
                );
            } else {
                $data = array(
                    'user_type' => $this->input->post('user_type'),
                    'is_owner' => 'no',
                    'business_legal_name' => $this->input->post('business_legal_name'),
                    'country_of_operation' => $this->input->post('country_of_operation'),
                    'email' => $this->input->post('non_owner_email'),
                    'phone' => $this->input->post('non_owner_phone'),
                    'full_name' => $this->input->post('non_owner_full_name'),
                    'password' => $this->input->post('password'),
                    'role' => $this->input->post('user_role'),
                    // Owner information
                    'owner_full_name' => $this->input->post('owner_full_name'),
                    'owner_email' => $this->input->post('owner_email'),
                    'owner_phone' => $this->input->post('owner_phone')
                );
            }

            $operator_id = $this->AuthModel->register($data);
            
            if ($operator_id) {
                log_message('info', 'New operator registered: ' . $operator_id);
                return TRUE;
            }
            
            return FALSE;
        } catch (Exception $e) {
            log_message('error', 'SignUp error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Forgot password - send reset link
     */
    public function forgot_password() {
        $data = array();
        
        if ($this->input->post()) {
            $email = $this->input->post('email', TRUE);
            
            if (empty($email)) {
                $data['error'] = 'Please enter your email address.';
            } else {
                $operator = $this->AuthModel->get_by_email($email);
                
                if ($operator) {
                    // Generate reset token
                    $token = $this->AuthModel->create_reset_token($email);
                    
                    if ($token) {
                        // TODO: Send email with reset link
                        // $reset_link = base_url('auth/reset_password/' . $token . '/' . $email);
                        // $this->send_reset_email($email, $operator->full_name, $reset_link);
                        
                        $data['success'] = 'Password reset link has been sent to your email address.';
                        log_message('info', 'Password reset requested for: ' . $email);
                    } else {
                        $data['error'] = 'Could not generate reset token. Please try again.';
                    }
                } else {
                    // Don't reveal if email exists (security best practice)
                    $data['success'] = 'If this email exists, a reset link will be sent.';
                }
            }
        }

        $this->load->view('common_head');
        $this->load->view('auth/forgot_password', $data);
        $this->load->view('common_footer');
    }

    /**
     * Reset password
     */
    public function reset_password($token = '', $email = '') {
        $data = array();
        
        if (empty($token) || empty($email)) {
            $data['error'] = 'Invalid reset link.';
        } else {
            // Validate token
            if (!$this->AuthModel->validate_reset_token($token, $email)) {
                $data['error'] = 'Reset link is invalid or expired.';
            } else if ($this->input->post()) {
                $password = $this->input->post('password');
                $password_confirm = $this->input->post('password_confirm');
                
                if (empty($password) || empty($password_confirm)) {
                    $data['error'] = 'Password fields are required.';
                } else if ($password !== $password_confirm) {
                    $data['error'] = 'Passwords do not match.';
                } else if (strlen($password) < 8) {
                    $data['error'] = 'Password must be at least 8 characters.';
                } else {
                    // Update password
                    $operator = $this->AuthModel->get_by_email($email);
                    if ($operator && $this->AuthModel->update_password($operator->operator_id, $password)) {
                        // Clear reset token
                        $this->AuthModel->clear_reset_token($email);
                        
                        $this->session->set_flashdata('success', 'Your password has been reset successfully. Please login with your new password.');
                        redirect(base_url('auth/login'));
                    } else {
                        $data['error'] = 'Could not update password. Please try again.';
                    }
                }
            }
        }

        $data['token'] = $token;
        $data['email'] = $email;
        $this->load->view('common_head');
        $this->load->view('auth/reset_password', $data);
        $this->load->view('common_footer');
    }
}
