<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('auth');
        $this->load->model('OperatorModel');
        
        // Require login for all methods
        AuthHelper::require_login();
    }

    /**
     * Helper method to get section completion status
     */
    private function get_section_status($operator_id) {
        $profile_complete = $this->OperatorModel->is_profile_complete($operator_id);
        $legal_complete = $this->OperatorModel->is_legal_complete($operator_id);
        $accounting_complete = $this->OperatorModel->is_accounting_complete($operator_id);
        
        return array(
            'profile' => $profile_complete,
            'legal' => $legal_complete,
            'system_process' => false,
            'collaboration' => false,
            'users' => false,
            'accounting' => $accounting_complete,
            'operations' => false,
            'status_review' => false
        );
    }

    /**
     * Helper method to set current section in data
     */
    private function add_current_section(&$data, $operator_id, $section) {
        $data['current_section'] = $section;
        $data['section_status'] = $this->get_section_status($operator_id);
        $completed_sections = count(array_filter($data['section_status']));
        $total_sections = count($data['section_status']);
        $data['completion_percentage'] = $total_sections > 0 ? round(($completed_sections / $total_sections) * 100) : 0;
    }

    /**
     * Main dashboard home
     */
    public function index() {
        $operator_id = AuthHelper::get_operator_id();
        
        $operator = $this->OperatorModel->get_operator($operator_id);
        
        $data = array(
            'operator' => $operator,
            'page_title' => 'Dashboard',
            'section' => 'home',
            'operator_name' => AuthHelper::get_operator_name(),
            'account_status' => isset($operator->account_status) ? $operator->account_status : 'pending_verification',
            'last_updated' => isset($operator->updated_at) ? $operator->updated_at : null
        );

        // Get completion status for all sections
        $data['section_status'] = $this->get_section_status($operator_id);
        
        // Calculate completion percentage based on 8 steps
        $completed_sections = count(array_filter($data['section_status']));
        $total_sections = count($data['section_status']);
        $data['completion_percentage'] = $total_sections > 0 ? round(($completed_sections / $total_sections) * 100) : 0;
        
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/home', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * Profile section
     */
    public function profile() {
        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'Profile Information',
            'section' => 'profile'
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'profile');

        // Load existing profile if exists
        $this->load->model('OperatorProfileModel');
        $profile = $this->OperatorProfileModel->get_profile($operator_id);
        
        if ($this->input->post()) {
            // Save profile
            if ($this->save_profile($operator_id)) {
                $this->session->set_flashdata('success', 'Profile information saved successfully!');
                redirect(base_url('index.php/operator/profile'));
            } else {
                $data['error'] = 'Failed to save profile information.';
            }
        }

        $data['profile'] = $profile;
        
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/profile', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * Legal & Compliance section
     */
    public function legal() {
        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'Legal & Compliance',
            'section' => 'legal'
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'legal');

        $this->load->model('OperatorLegalModel');
        $legal = $this->OperatorLegalModel->get_legal($operator_id);
        
        if ($this->input->post()) {
            if ($this->save_legal($operator_id)) {
                $this->session->set_flashdata('success', 'Legal information saved successfully!');
                redirect(base_url('index.php/operator/legal'));
            } else {
                $data['error'] = 'Failed to save legal information.';
            }
        }

        $data['legal'] = $legal;
        
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/legal', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * System Processes section
     */
    public function system_process() {
        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'System Processes',
            'section' => 'system_process'
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'system_process');

        $this->load->model('OperatorSystemProcessModel');
        $system = $this->OperatorSystemProcessModel->get_system_process($operator_id);
        
        if ($this->input->post()) {
            if ($this->save_system_process($operator_id)) {
                $this->session->set_flashdata('success', 'System process information saved successfully!');
                redirect(base_url('dashboard/system_process'));
            } else {
                $data['error'] = 'Failed to save system process information.';
            }
        }

        $data['system_process'] = $system;
        
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/system_process', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * Collaboration Agreement section
     */
    public function collaboration() {
        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'Collaboration Agreement',
            'section' => 'collaboration'
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'collaboration');

        $this->load->model('OperatorAgreementModel');
        $agreement = $this->OperatorAgreementModel->get_agreement($operator_id);
        
        if ($this->input->post()) {
            if ($this->save_collaboration($operator_id)) {
                $this->session->set_flashdata('success', 'Collaboration agreement saved successfully!');
                redirect(base_url('dashboard/collaboration'));
            } else {
                $data['error'] = 'Failed to save collaboration agreement.';
            }
        }

        $data['agreement'] = $agreement;
        
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/collaboration', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * Users & Staff section
     */
    public function users() {
        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'Users & Staff Management',
            'section' => 'users'
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'users');

        $this->load->model('OperatorUserModel');
        $users = $this->OperatorUserModel->get_users($operator_id);
        
        $data['users'] = $users;
        
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/users', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * Add new user
     */
    public function users_add() {
        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'Add New User',
            'section' => 'users'
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'users');

        // Define available roles
        $data['roles'] = array(
            'Admin' => 'Admin',
            'Head of Department' => 'Head of Department',
            'Reservation Manager' => 'Reservation Manager',
            'Operational Manager' => 'Operational Manager',
            'Finance Manager' => 'Finance Manager',
            'Marketing Manager' => 'Marketing Manager',
            'Support Manager' => 'Support Manager',
            'Content Manager' => 'Content Manager'
        );

        $this->load->model('OperatorUserModel');

        if ($this->input->post()) {
            $user_data = array(
                'full_name' => $this->input->post('full_name'),
                'email' => $this->input->post('email'),
                'mobile' => $this->input->post('mobile'),
                'password' => $this->input->post('password'),
                'role' => $this->input->post('role'),
                'access_rights' => $this->input->post('access_rights') ? $this->input->post('access_rights') : array(),
                'account_reset_required' => TRUE,
                'created_by' => $operator_id
            );

            // Validate required fields
            if (empty($user_data['full_name']) || empty($user_data['email']) || empty($user_data['password'])) {
                $data['error'] = 'Full name, email, and password are required.';
            } elseif (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'Invalid email format.';
            } elseif ($this->OperatorUserModel->email_exists($operator_id, $user_data['email'])) {
                $data['error'] = 'Email already exists for another user.';
            } elseif (strlen($user_data['password']) < 8) {
                $data['error'] = 'Password must be at least 8 characters long.';
            } else {
                if ($this->OperatorUserModel->add_user($operator_id, $user_data)) {
                    $this->session->set_flashdata('success', 'New user added successfully!');
                    redirect(site_url('dashboard/users'));
                } else {
                    $data['error'] = 'Failed to add user. Please try again.';
                }
            }
        }

        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/users_add', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * Edit user
     */
    public function users_edit($user_id = NULL) {
        if (!$user_id) {
            show_error('User not found', 404);
        }

        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'Edit User',
            'section' => 'users',
            'user_id' => $user_id
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'users');

        // Define available roles
        $data['roles'] = array(
            'Admin' => 'Admin',
            'Head of Department' => 'Head of Department',
            'Reservation Manager' => 'Reservation Manager',
            'Operational Manager' => 'Operational Manager',
            'Finance Manager' => 'Finance Manager',
            'Marketing Manager' => 'Marketing Manager',
            'Support Manager' => 'Support Manager',
            'Content Manager' => 'Content Manager'
        );

        $this->load->model('OperatorUserModel');
        $user = $this->OperatorUserModel->get_user($user_id);

        if (!$user || $user->operator_id !== $operator_id) {
            show_error('Unauthorized access', 403);
        }

        $data['user'] = $user;

        if ($this->input->post()) {
            $user_data = array(
                'full_name' => $this->input->post('full_name'),
                'email' => $this->input->post('email'),
                'mobile' => $this->input->post('mobile'),
                'role' => $this->input->post('role'),
                'status' => $this->input->post('status'),
                'access_rights' => $this->input->post('access_rights') ? $this->input->post('access_rights') : array()
            );

            // Add password only if provided
            $password = $this->input->post('password');
            if (!empty($password)) {
                $user_data['password'] = $password;
            }

            // Validate required fields
            if (empty($user_data['full_name']) || empty($user_data['email'])) {
                $data['error'] = 'Full name and email are required.';
            } elseif (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'Invalid email format.';
            } elseif ($this->OperatorUserModel->email_exists($operator_id, $user_data['email'], $user_id)) {
                $data['error'] = 'Email already exists for another user.';
            } elseif (!empty($password) && strlen($password) < 8) {
                $data['error'] = 'Password must be at least 8 characters long.';
            } else {
                if ($this->OperatorUserModel->update_user($user_id, $user_data)) {
                    $this->session->set_flashdata('success', 'User updated successfully!');
                    redirect(site_url('dashboard/users'));
                } else {
                    $data['error'] = 'Failed to update user. Please try again.';
                }
            }
        }

        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/users_edit', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * Delete user
     */
    public function users_delete($user_id = NULL) {
        if (!$user_id) {
            show_error('User not found', 404);
        }

        $operator_id = AuthHelper::get_operator_id();
        
        $this->load->model('OperatorUserModel');
        $user = $this->OperatorUserModel->get_user($user_id);

        if (!$user || $user->operator_id !== $operator_id) {
            show_error('Unauthorized access', 403);
        }

        if ($this->OperatorUserModel->delete_user($user_id)) {
            $this->session->set_flashdata('success', 'User deleted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete user.');
        }

        redirect(site_url('dashboard/users'));
    }

    /**
     * Accounting & Payouts section
     */
    public function accounting() {
        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'Accounting & Payouts',
            'section' => 'accounting'
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'accounting');

        $this->load->model('OperatorAccountingModel');
        $accounting = $this->OperatorAccountingModel->get_accounting($operator_id);
        $payouts = $this->OperatorAccountingModel->get_payout_history($operator_id);
        
        if ($this->input->post()) {
            if ($this->save_accounting($operator_id)) {
                $this->session->set_flashdata('success', 'Accounting information saved successfully!');
                redirect(base_url('dashboard/accounting'));
            } else {
                $data['error'] = 'Failed to save accounting information.';
            }
        }

        $data['accounting'] = $accounting;
        $data['payouts'] = $payouts;
        
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/accounting', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * Service Operations section
     */
    public function operations() {
        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'Service Operations',
            'section' => 'operations'
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'operations');

        $this->load->model('OperatorServiceModel');
        $service = $this->OperatorServiceModel->get_operations($operator_id);
        
        if ($this->input->post()) {
            if ($this->save_operations($operator_id)) {
                $this->session->set_flashdata('success', 'Service operations saved successfully!');
                redirect(base_url('dashboard/operations'));
            } else {
                $data['error'] = 'Failed to save service operations.';
            }
        }

        $data['service_operations'] = $service;
        
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/operations', $data);
        $this->load->view('dashboard/footer', $data);
    }

    /**
     * Status Review section (read-only)
     */
    public function status_review() {
        $operator_id = AuthHelper::get_operator_id();
        
        $data = array(
            'operator_id' => $operator_id,
            'page_title' => 'Status Review',
            'section' => 'status_review'
        );

        // Provide current_section and completion status for header/sidebar
        $this->add_current_section($data, $operator_id, 'status_review');

        $this->load->model('OperatorStatusModel');
        $status_review = $this->OperatorStatusModel->get_status_review($operator_id);
        $completion_percent = $this->OperatorStatusModel->calculate_completion_percentage($operator_id);
        $section_status = $this->OperatorStatusModel->get_section_status($operator_id);
        
        // Get operator account status if available
        $operator = $this->OperatorModel->get_operator($operator_id);
        $account_status = isset($operator->account_status) ? $operator->account_status : 'pending_verification';
        
        $admin_notes = '';
        if ($status_review) {
            if (isset($status_review->admin_notes)) $admin_notes = $status_review->admin_notes;
            elseif (isset($status_review->notes)) $admin_notes = $status_review->notes;
        }
        
        $data['status_review'] = $status_review;
        $data['completion_percent'] = $completion_percent;
        $data['section_status'] = $section_status;
        $data['account_status'] = $account_status;
        $data['admin_notes'] = $admin_notes;
        
        $this->load->view('dashboard/header', $data);
        $this->load->view('dashboard/sidebar', $data);
        $this->load->view('dashboard/sections/status_review', $data);
        $this->load->view('dashboard/footer', $data);
    }

    // Private functions for saving data

    private function save_profile($operator_id) {
        $this->load->model('OperatorProfileModel');
        
        $profile_data = array(
            'operator_id' => $operator_id,
            'business_legal_name' => $this->input->post('business_legal_name'),
            'business_registration_number' => $this->input->post('business_registration_number'),
            'registered_address' => $this->input->post('registered_address'),
            'operational_address' => $this->input->post('operational_address'),
            'years_in_operation' => $this->input->post('years_in_operation'),
            'trading_name' => $this->input->post('trading_name'),
            'company_description' => $this->input->post('company_description'),
            'status' => 'draft'
        );

        // Handle service types (multi-select)
        $service_types = $this->input->post('service_types');
        if (is_array($service_types)) {
            $profile_data['service_types'] = json_encode($service_types);
        }

        // Handle contact details - save as individual fields
        $profile_data['contact_name'] = $this->input->post('contact_name');
        $profile_data['contact_phone'] = $this->input->post('contact_phone');
        $profile_data['contact_email'] = $this->input->post('contact_email');

        // Handle social media links - save as individual fields
        $profile_data['facebook_link'] = $this->input->post('facebook_link');
        $profile_data['instagram_link'] = $this->input->post('instagram_link');
        $profile_data['linkedin_link'] = $this->input->post('linkedin_link');

        // Handle company logo file upload
        if (!empty($_FILES['company_logo']['name'])) {
            $upload_path = './uploads/company_logos/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, TRUE);
            }
            
            // Generate unique filename
            $file_ext = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
            $file_name = $operator_id . '_' . time() . '.' . $file_ext;
            $upload_file = $upload_path . $file_name;
            
            // Validate file type
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array(strtolower($file_ext), $allowed_types)) {
                $this->session->set_flashdata('error', 'Invalid file type. Only JPG, PNG, and GIF are allowed.');
                return FALSE;
            }
            
            // Validate file size (max 5MB)
            if ($_FILES['company_logo']['size'] > 5242880) {
                $this->session->set_flashdata('error', 'File size too large. Maximum 5MB allowed.');
                return FALSE;
            }
            
            // Upload file
            if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_file)) {
                $profile_data['company_logo'] = 'uploads/company_logos/' . $file_name;
            } else {
                $this->session->set_flashdata('error', 'Failed to upload company logo.');
                return FALSE;
            }
        }

        return $this->OperatorProfileModel->save_profile($operator_id, $profile_data);
    }

    private function save_legal($operator_id) {
        $this->load->model('OperatorLegalModel');
        
        $legal_data = array(
            'operator_id' => $operator_id,
            'business_license_number' => $this->input->post('business_license_number'),
            'license_type' => $this->input->post('license_type'),
            'license_expiry_date' => $this->input->post('license_expiry_date'),
            'service_package' => $this->input->post('service_package'),
            'compliance_status' => 'pending_verification'
        );

        return $this->OperatorLegalModel->save_legal($legal_data);
    }

    private function save_system_process($operator_id) {
        $this->load->model('OperatorSystemProcessModel');
        
        $system_data = array(
            'operator_id' => $operator_id,
            'service_category' => $this->input->post('service_category'),
            'communication_preference' => $this->input->post('communication_preference'),
            'status' => 'draft'
        );

        return $this->OperatorSystemProcessModel->save_system_process($system_data);
    }

    private function save_collaboration($operator_id) {
        $this->load->model('OperatorAgreementModel');
        
        $agreement_data = array(
            'operator_id' => $operator_id,
            'contact_management_name' => $this->input->post('contact_management_name'),
            'contact_management_email' => $this->input->post('contact_management_email'),
            'contact_accounting_name' => $this->input->post('contact_accounting_name'),
            'contact_accounting_email' => $this->input->post('contact_accounting_email'),
            'agreement_type' => $this->input->post('agreement_type'),
            'commission_value' => $this->input->post('commission_value'),
            'status' => 'Draft'
        );

        return $this->OperatorAgreementModel->save_agreement($agreement_data);
    }

    private function save_accounting($operator_id) {
        $this->load->model('OperatorAccountingModel');
        
        $accounting_data = array(
            'bank_account_holder_name' => $this->input->post('bank_account_holder_name'),
            'bank_name' => $this->input->post('bank_name'),
            'account_number' => $this->input->post('account_number'),
            'iban' => $this->input->post('iban'),
            'swift_code' => $this->input->post('swift_code'),
            'vat_number' => $this->input->post('vat_number'),
            'payment_schedule' => $this->input->post('payment_schedule'),
            'status' => 'active'
        );

        return $this->OperatorAccountingModel->save_accounting($operator_id, $accounting_data);
    }

    private function save_operations($operator_id) {
        $this->load->model('OperatorServiceModel');
        
        $service_data = array(
            'operator_id' => $operator_id,
            'service_location' => $this->input->post('service_location'),
            'is_nationwide' => $this->input->post('is_nationwide') ? TRUE : FALSE,
            'is_gps_location' => ($this->input->post('service_location') === 'gps') ? TRUE : FALSE,
            'gps_coordinates' => $this->input->post('gps_coordinates'),
            'has_pickup_dropoff' => $this->input->post('has_pickup_dropoff') ? TRUE : FALSE,
            'pickup_dropoff_surcharge' => $this->input->post('pickup_dropoff_surcharge') ?: NULL,
            'pickup_dropoff_free' => $this->input->post('pickup_dropoff_free') ? TRUE : FALSE,
            'pickup_dropoff_details' => $this->input->post('pickup_dropoff_details'),
            'emergency_contact_name' => $this->input->post('emergency_contact_name'),
            'emergency_contact_phone' => $this->input->post('emergency_contact_phone'),
            'emergency_contact_email' => $this->input->post('emergency_contact_email'),
            'opening_time' => $this->input->post('opening_time'),
            'closing_time' => $this->input->post('closing_time'),
            'status' => 'draft'
        );

        // Handle operating days (multi-select)
        $operating_days = $this->input->post('operating_days');
        if (is_array($operating_days)) {
            $service_data['operating_days'] = json_encode($operating_days);
        }

        // Handle operating areas
        $operating_areas = $this->input->post('operating_areas');
        if (is_array($operating_areas)) {
            $service_data['operating_areas'] = json_encode($operating_areas);
        }

        return $this->OperatorServiceModel->save_operations($operator_id, $service_data);
    }

    /**
     * AJAX endpoint to save legal compliance from profile modal
     */
    public function save_legal_ajax() {
        // Check if it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request', 400);
        }

        $operator_id = AuthHelper::get_operator_id();
        
        if (!$operator_id) {
            echo json_encode(array('success' => FALSE, 'message' => 'Unauthorized'));
            return;
        }

        $this->load->model('OperatorLegalModel');
        
        $legal_data = array(
            'operator_id' => $operator_id,
            'business_license_number' => $this->input->post('business_license_number'),
            'license_type' => $this->input->post('license_type'),
            'license_expiry_date' => $this->input->post('license_expiry_date'),
            'service_package' => $this->input->post('service_package'),
            'compliance_status' => 'pending_verification'
        );

        // Handle file uploads
        if (!empty($_FILES['proof_of_license']['name'])) {
            $legal_data['proof_of_license'] = $this->handle_legal_file_upload('proof_of_license', $operator_id);
        }

        if (!empty($_FILES['insurance_certificate']['name'])) {
            $legal_data['insurance_certificate'] = $this->handle_legal_file_upload('insurance_certificate', $operator_id);
        }

        if (!empty($_FILES['signed_agreement']['name'])) {
            $legal_data['signed_agreement'] = $this->handle_legal_file_upload('signed_agreement', $operator_id);
        }

        if ($this->OperatorLegalModel->save_legal($legal_data)) {
            echo json_encode(array('success' => TRUE, 'message' => 'Legal information saved successfully'));
        } else {
            echo json_encode(array('success' => FALSE, 'message' => 'Failed to save legal information'));
        }
    }

    /**
     * Helper function to handle legal document file uploads
     */
    private function handle_legal_file_upload($field_name, $operator_id) {
        if (empty($_FILES[$field_name]['name'])) {
            return NULL;
        }

        $upload_path = './uploads/legal_documents/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }
        
        $file_ext = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
        $file_name = $operator_id . '_' . $field_name . '_' . time() . '.' . $file_ext;
        $upload_file = $upload_path . $file_name;
        
        $allowed_types = array('pdf', 'jpg', 'jpeg', 'png');
        if (!in_array(strtolower($file_ext), $allowed_types)) {
            return NULL;
        }
        
        if ($_FILES[$field_name]['size'] > 10485760) { // 10MB max
            return NULL;
        }
        
        if (move_uploaded_file($_FILES[$field_name]['tmp_name'], $upload_file)) {
            return 'uploads/legal_documents/' . $file_name;
        }
        
        return NULL;
    }}