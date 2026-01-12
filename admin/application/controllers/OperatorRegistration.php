<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperatorRegistration extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('OperatorModel');
        $this->load->library('form_validation');
    }

    // Step 1: Registration - Operator onboarding and sign-up details
    public function step1() {
        $data = array();
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('business_owner', 'Business Owner', 'required');
            $this->form_validation->set_rules('business_legal_name', 'Business Legal Name', 'required|min_length[3]');
            $this->form_validation->set_rules('country_of_operation', 'Country of Operation', 'required');
            $this->form_validation->set_rules('registering_user_email', 'Email Address', 'required|valid_email|is_unique[operators.registering_user_email]');
            $this->form_validation->set_rules('registering_user_phone', 'Mobile Number', 'required|numeric');
            $this->form_validation->set_rules('registering_user_full_name', 'Full Name', 'required|min_length[3]');
            $this->form_validation->set_rules('registering_user_role', 'User Role', 'required');
            $this->form_validation->set_rules('registering_user_password', 'Password', 'required|min_length[8]');
            
            if ($this->form_validation->run() == FALSE) {
                $data['errors'] = validation_errors();
            } else {
                $registration_data = array(
                    'business_owner_confirmation' => $this->input->post('business_owner'),
                    'business_legal_name' => $this->input->post('business_legal_name'),
                    'country_of_operation' => $this->input->post('country_of_operation'),
                    'registering_user_email' => $this->input->post('registering_user_email'),
                    'registering_user_phone' => $this->input->post('registering_user_phone'),
                    'registering_user_full_name' => $this->input->post('registering_user_full_name'),
                    'registering_user_role' => $this->input->post('registering_user_role'),
                    'registering_user_password_hash' => password_hash($this->input->post('registering_user_password'), PASSWORD_BCRYPT),
                    'current_step' => 1,
                    'registration_status' => 'in_progress'
                );
                
                $operator_id = $this->OperatorModel->create_registration($registration_data);
                
                if ($operator_id) {
                    $this->session->set_userdata('operator_id', $operator_id);
                    $this->session->set_userdata('registration_step', 1);
                    
                    // If owner confirmation is 'yes', skip owner data and go to profile
                    if ($this->input->post('business_owner') == 'yes') {
                        redirect(base_url() . 'OperatorRegistration/step2');
                    } else {
                        // If no, redirect to owner data entry
                        redirect(base_url() . 'OperatorRegistration/step1_owner');
                    }
                } else {
                    $data['errors'] = 'Failed to save registration data. Please try again.';
                }
            }
        }
        
        $data['title'] = 'Step 1: Registration';
        $data['step'] = 1;
        $this->load->view('operator_registration/step1', $data);
    }

    // Step 1 Owner Data (if user is not the owner)
    public function step1_owner() {
        $operator_id = $this->session->userdata('operator_id');
        if (!$operator_id) {
            redirect(base_url() . 'OperatorRegistration/step1');
        }
        
        $data = array();
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('owner_email', 'Owner Email', 'required|valid_email');
            $this->form_validation->set_rules('owner_phone', 'Owner Phone', 'required|numeric');
            $this->form_validation->set_rules('owner_full_name', 'Owner Full Name', 'required|min_length[3]');
            $this->form_validation->set_rules('owner_password', 'Owner Password', 'required|min_length[8]');
            
            if ($this->form_validation->run() == FALSE) {
                $data['errors'] = validation_errors();
            } else {
                $owner_data = array(
                    'owner_email' => $this->input->post('owner_email'),
                    'owner_phone' => $this->input->post('owner_phone'),
                    'owner_full_name' => $this->input->post('owner_full_name'),
                    'owner_password_hash' => password_hash($this->input->post('owner_password'), PASSWORD_BCRYPT)
                );
                
                if ($this->OperatorModel->update_registration($operator_id, $owner_data)) {
                    redirect(base_url() . 'OperatorRegistration/step2');
                } else {
                    $data['errors'] = 'Failed to save owner data. Please try again.';
                }
            }
        }
        
        $data['title'] = 'Step 1: Owner Information';
        $data['step'] = '1a';
        $this->load->view('operator_registration/step1_owner', $data);
    }

    // Step 2: Profile - Profile and contact information
    public function step2() {
        $operator_id = $this->session->userdata('operator_id');
        if (!$operator_id) {
            redirect(base_url() . 'OperatorRegistration/step1');
        }
        
        $data = array();
        $operator = $this->OperatorModel->get_operator($operator_id);
        
        if ($this->input->post()) {
            $profile_data = array(
                'profile_data' => json_encode($this->input->post())
            );
            
            if ($this->OperatorModel->update_registration($operator_id, $profile_data)) {
                redirect(base_url() . 'OperatorRegistration/step3');
            }
        }
        
        $data['operator'] = $operator;
        $data['title'] = 'Step 2: Profile Information';
        $data['step'] = 2;
        $this->load->view('operator_registration/step2', $data);
    }

    // Step 3: Legal - Compliance and tourism licensing data
    public function step3() {
        $operator_id = $this->session->userdata('operator_id');
        if (!$operator_id) {
            redirect(base_url() . 'OperatorRegistration/step1');
        }
        
        $data = array();
        $operator = $this->OperatorModel->get_operator($operator_id);
        
        if ($this->input->post()) {
            $legal_data = array(
                'legal_data' => json_encode($this->input->post())
            );
            
            if ($this->OperatorModel->update_registration($operator_id, $legal_data)) {
                redirect(base_url() . 'OperatorRegistration/step4');
            }
        }
        
        $data['operator'] = $operator;
        $data['title'] = 'Step 3: Legal Compliance';
        $data['step'] = 3;
        $this->load->view('operator_registration/step3', $data);
    }

    // Step 4: Accounting - Financial and payout configuration
    public function step4() {
        $operator_id = $this->session->userdata('operator_id');
        if (!$operator_id) {
            redirect(base_url() . 'OperatorRegistration/step1');
        }
        
        $data = array();
        $operator = $this->OperatorModel->get_operator($operator_id);
        
        if ($this->input->post()) {
            $accounting_data = array(
                'accounting_data' => json_encode($this->input->post())
            );
            
            if ($this->OperatorModel->update_registration($operator_id, $accounting_data)) {
                redirect(base_url() . 'OperatorRegistration/step5');
            }
        }
        
        $data['operator'] = $operator;
        $data['title'] = 'Step 4: Accounting Configuration';
        $data['step'] = 4;
        $this->load->view('operator_registration/step4', $data);
    }

    // Step 5: System Processes - Booking and workflow logic links
    public function step5() {
        $operator_id = $this->session->userdata('operator_id');
        if (!$operator_id) {
            redirect(base_url() . 'OperatorRegistration/step1');
        }
        
        $data = array();
        $operator = $this->OperatorModel->get_operator($operator_id);
        
        if ($this->input->post()) {
            $system_processes_data = array(
                'system_processes_data' => json_encode($this->input->post())
            );
            
            if ($this->OperatorModel->update_registration($operator_id, $system_processes_data)) {
                redirect(base_url() . 'OperatorRegistration/step6');
            }
        }
        
        $data['operator'] = $operator;
        $data['title'] = 'Step 5: System Processes';
        $data['step'] = 5;
        $this->load->view('operator_registration/step5', $data);
    }

    // Step 6: Service Operation - Operational logistics and delivery data
    public function step6() {
        $operator_id = $this->session->userdata('operator_id');
        if (!$operator_id) {
            redirect(base_url() . 'OperatorRegistration/step1');
        }
        
        $data = array();
        $operator = $this->OperatorModel->get_operator($operator_id);
        
        if ($this->input->post()) {
            $service_operation_data = array(
                'service_operation_data' => json_encode($this->input->post())
            );
            
            if ($this->OperatorModel->update_registration($operator_id, $service_operation_data)) {
                redirect(base_url() . 'OperatorRegistration/step7');
            }
        }
        
        $data['operator'] = $operator;
        $data['title'] = 'Step 6: Service Operation';
        $data['step'] = 6;
        $this->load->view('operator_registration/step6', $data);
    }

    // Step 7: Status Review - System-controlled account review fields
    public function step7() {
        $operator_id = $this->session->userdata('operator_id');
        if (!$operator_id) {
            redirect(base_url() . 'OperatorRegistration/step1');
        }
        
        $data = array();
        $operator = $this->OperatorModel->get_operator($operator_id);
        
        if ($this->input->post()) {
            $registration_update = array(
                'status_review_data' => json_encode($this->input->post()),
                'registration_status' => 'completed',
                'current_step' => 7,
                'submitted_at' => date('Y-m-d H:i:s')
            );
            
            if ($this->OperatorModel->update_registration($operator_id, $registration_update)) {
                $this->session->unset_userdata('operator_id');
                $this->session->unset_userdata('registration_step');
                redirect(base_url() . 'OperatorRegistration/success');
            }
        }
        
        $data['operator'] = $operator;
        $data['title'] = 'Step 7: Status Review';
        $data['step'] = 7;
        $this->load->view('operator_registration/step7', $data);
    }

    // Success page
    public function success() {
        $data['title'] = 'Registration Submitted Successfully';
        $this->load->view('operator_registration/success', $data);
    }
}
