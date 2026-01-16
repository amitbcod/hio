<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('auth');
        $this->load->model('OperatorModel');
        $this->load->model('OperatorProfileModel');
        $this->load->model('OperatorLegalModel');
        $this->load->model('OperatorSystemProcessModel');
        $this->load->model('OperatorAgreementModel');
        $this->load->model('OperatorUserModel');
        $this->load->model('OperatorAccountingModel');
        $this->load->model('OperatorServiceModel');
        
        // Require login for all methods
        AuthHelper::require_login();
    }

    /**
     * Helper method to get section completion status
     */
    private function get_section_status($operator_id) {
        $profile_complete = $this->OperatorProfileModel->is_complete($operator_id);
        $legal_complete = $this->OperatorLegalModel->is_complete($operator_id);
        $system_process_complete = $this->OperatorSystemProcessModel->is_complete($operator_id);
        $collaboration_complete = $this->OperatorAgreementModel->is_complete($operator_id);
        $users_complete = $this->OperatorUserModel->get_user_count($operator_id) > 0;
        $accounting_complete = $this->OperatorAccountingModel->is_complete($operator_id);
        $operations_complete = $this->OperatorServiceModel->is_complete($operator_id);
        
        // Status review is complete when all other sections are complete
        $status_review_complete = $profile_complete && $legal_complete && 
                                  $system_process_complete && $collaboration_complete && 
                                  $users_complete && $accounting_complete && 
                                  $operations_complete;
        
        return array(
            'profile' => $profile_complete,
            'system_process' => $system_process_complete,
            'collaboration' => $collaboration_complete,
            'users' => $users_complete,
            'accounting' => $accounting_complete,
            'operations' => $operations_complete,
            'legal' => $legal_complete,
            'status_review' => $status_review_complete
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
     * Main dashboard home - redirect to profile
     */
    public function index() {
        redirect('operator/profile');
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
        
        // Get operator data for business name
        $operator = $this->OperatorModel->get_operator($operator_id);
        
        // Debug: Check what we're getting
        error_log("Dashboard profile - operator_id: " . $operator_id);
        error_log("Dashboard profile - operator object: " . print_r($operator, true));
        
        $data['operator'] = $operator;
        
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
        
        if ($this->input->post()) {
            if ($this->save_system_process($operator_id)) {
                $this->session->set_flashdata('success', 'System process information saved successfully!');
                redirect('operator/system_process');
            } else {
                $data['error'] = 'Failed to save system process information.';
            }
        }
        
        $system = $this->OperatorSystemProcessModel->get_system_process($operator_id);
        $data['system'] = $system;
        
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
     * Download responsibilities PDF
     */
    public function download_responsibilities() {
        $operator_id = AuthHelper::get_operator_id();
        $operator = $this->OperatorModel->get_operator($operator_id);
        
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="responsibilities_' . $operator_id . '.pdf"');
        
        // Creating a simple PDF content
        $pdf_content = "%PDF-1.4\n";
        $pdf_content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf_content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf_content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 5 0 R >>\nendobj\n";
        $pdf_content .= "4 0 obj\n<< /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >>\nendobj\n";
        $pdf_content .= "5 0 obj\n<< /Length 500 >>\nstream\n";
        $pdf_content .= "BT /F1 18 Tf 50 700 Td (OPERATOR RESPONSIBILITIES) Tj ET\n";
        $pdf_content .= "BT /F1 12 Tf 50 670 Td (Holidays.io Platform Agreement) Tj ET\n";
        $pdf_content .= "BT /F1 10 Tf 50 640 Td (Operator: " . (isset($operator->full_name) ? $operator->full_name : 'N/A') . ") Tj ET\n";
        $pdf_content .= "BT /F1 10 Tf 50 610 Td (Date: " . date('Y-m-d') . ") Tj ET\n";
        $pdf_content .= "BT /F1 10 Tf 50 570 Td (Standard Terms & Conditions Apply:) Tj ET\n";
        $pdf_content .= "BT /F1 9 Tf 50 550 Td (1. Maintain accurate service information) Tj ET\n";
        $pdf_content .= "BT /F1 9 Tf 50 535 Td (2. Respond to bookings within 24 hours) Tj ET\n";
        $pdf_content .= "BT /F1 9 Tf 50 520 Td (3. Provide quality service as described) Tj ET\n";
        $pdf_content .= "BT /F1 9 Tf 50 505 Td (4. Comply with platform policies) Tj ET\n";
        $pdf_content .= "BT /F1 9 Tf 50 490 Td (5. Handle customer complaints professionally) Tj ET\n";
        $pdf_content .= "endstream\nendobj\n";
        $pdf_content .= "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000056 00000 n \n0000000115 00000 n \n0000000214 00000 n \n0000000304 00000 n \n";
        $pdf_content .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n854\n%%EOF";
        
        echo $pdf_content;
        exit;
    }

    /**
     * Status Review section (read-only)
     */
    /**
     * Status Review section
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
        
        // Get operator info for defaults
        $operator = $this->OperatorModel->get_operator($operator_id);
        $operator_name = $operator ? $operator->full_name : 'System Administrator';
        
        $system_data = array(
            'operator_id' => $operator_id,
            'service_category' => $this->input->post('service_category'),
            'communication_preference' => $this->input->post('communication_preference'),
            'assigned_operator_name' => $this->input->post('assigned_operator_name') ?: $operator_name,
            'assigned_operator_role' => $this->input->post('assigned_operator_role') ?: 'Primary Operator',
            'status' => $this->input->post('status') ?: 'draft'
        );

        return $this->OperatorSystemProcessModel->save_system_process($system_data);
    }

    private function save_collaboration($operator_id) {
        $this->load->model('OperatorAgreementModel');
        
        // Generate unique agreement_id
        $agreement_id = 'AGR_' . $operator_id . '_' . time();
        
        $agreement_data = array(
            'agreement_id' => $agreement_id,
            'operator_id' => $operator_id,
            'contact_management_name' => $this->input->post('contact_management_name'),
            'contact_management_email' => $this->input->post('contact_management_email'),
            'contact_management_phone' => $this->input->post('contact_management_phone'),
            'contact_management_mobile' => $this->input->post('contact_management_mobile'),
            'contact_accounting_name' => $this->input->post('contact_accounting_name'),
            'contact_accounting_email' => $this->input->post('contact_accounting_email'),
            'contact_accounting_phone' => $this->input->post('contact_accounting_phone'),
            'contact_accounting_mobile' => $this->input->post('contact_accounting_mobile'),
            'agreement_type' => $this->input->post('agreement_type'),
            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),
            'renewal_date' => $this->input->post('renewal_date'),
            'commission_model' => $this->input->post('commission_model'),
            'commission_value' => $this->input->post('commission_value'),
            'marketing_contribution_percent' => $this->input->post('marketing_contribution'),
            'responsibilities_document' => $this->input->post('responsibilities'),
            'status' => $this->input->post('status')
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
            'currency_preference' => $this->input->post('currency_preference'),
            'vat_number' => $this->input->post('vat_number'),
            'vat_exempted' => $this->input->post('vat_exempted') ? 1 : 0,
            'commission_type' => $this->input->post('commission_type'),
            'commission_value' => $this->input->post('commission_value'),
            'payment_schedule' => $this->input->post('payment_schedule'),
            'status' => $this->input->post('status') ?: 'active'
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
            // Operating hours for each day
            'monday_open' => $this->input->post('monday_open'),
            'monday_close' => $this->input->post('monday_close'),
            'tuesday_open' => $this->input->post('tuesday_open'),
            'tuesday_close' => $this->input->post('tuesday_close'),
            'wednesday_open' => $this->input->post('wednesday_open'),
            'wednesday_close' => $this->input->post('wednesday_close'),
            'thursday_open' => $this->input->post('thursday_open'),
            'thursday_close' => $this->input->post('thursday_close'),
            'friday_open' => $this->input->post('friday_open'),
            'friday_close' => $this->input->post('friday_close'),
            'saturday_open' => $this->input->post('saturday_open'),
            'saturday_close' => $this->input->post('saturday_close'),
            'sunday_open' => $this->input->post('sunday_open'),
            'sunday_close' => $this->input->post('sunday_close'),
            'service_notes' => $this->input->post('service_notes'),
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