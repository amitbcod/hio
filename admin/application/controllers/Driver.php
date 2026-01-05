<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Driver extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Driver_model');
        $this->load->library(['session', 'form_validation', 'upload']);
        $this->load->helper(['url', 'form']);
    }

    // List all drivers
    public function index() {
        $data['drivers'] = $this->Driver_model->get_all();
        $this->load->view('driver_list', $data);
    }

    // Add/Edit Driver
    public function edit($id = null) {
        if ($this->input->post()) {

            // Upload configuration
            $config['upload_path']   = FCPATH . 'admin/public/images/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size']      = 2048;
            $config['encrypt_name']  = TRUE;

            // Ensure directory exists
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->upload->initialize($config);
            $profile_photo = null;

            if (!empty($_FILES['profile_photo']['name'])) {
                if ($this->upload->do_upload('profile_photo')) {
                    $uploadData = $this->upload->data();
                    $profile_photo = $uploadData['file_name'];
                } else {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect(current_url());
                }
            }

            // Collect form data
            $driver_data = [
                'first_name'        => $this->input->post('first_name'),
                'last_name'         => $this->input->post('last_name'),
                'mobile_no'         => $this->input->post('mobile_no'),
                'email'             => $this->input->post('email'),
                'driver_licence_no' => $this->input->post('driver_licence_no'),
                'licence_plate_no'  => $this->input->post('licence_plate_no'),
            ];

            // Handle password logic
            $password = $this->input->post('password');

            if ($id) {
                // Edit mode: update only if password is provided
                if (!empty($password)) {
                    $driver_data['password'] = password_hash($password, PASSWORD_BCRYPT);
                }
            } else {
                // Add mode: password is required
                if (empty($password)) {
                    $this->session->set_flashdata('error', 'Password is required.');
                    redirect(current_url());
                } else {
                    $driver_data['password'] = password_hash($password, PASSWORD_BCRYPT);
                }
            }

            // Set profile photo if uploaded
            if ($profile_photo) {
                $driver_data['profile_photo'] = $profile_photo;
            }

            if ($id) {
                $this->Driver_model->update($id, $driver_data);
                $this->session->set_flashdata('success', 'Driver updated successfully.');
            } else {
                $this->Driver_model->insert($driver_data);
                $this->session->set_flashdata('success', 'Driver added successfully.');
            }

            redirect('driver');
        }

        // Load data for edit form
        $data = [];
        if ($id) {
            $data['driver'] = $this->Driver_model->get($id);
        }

        $this->load->view('driver_form', $data);
    }

    // Delete Driver
    public function delete($id) {
        $driver = $this->Driver_model->get($id);
        if ($driver && !empty($driver['profile_photo'])) {
            $file = './admin/public/images/' . $driver['profile_photo'];
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $this->Driver_model->delete($id);
        $this->session->set_flashdata('success', 'Driver deleted successfully.');
        redirect('driver');
    }
}
