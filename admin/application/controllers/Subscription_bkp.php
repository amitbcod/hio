<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Subscription_model');
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form']);
    }

    // -----------------------------
    // List all plans and features
    // -----------------------------
    public function index() {
        $data['plans'] = $this->Subscription_model->get_plans();
        $data['features'] = $this->Subscription_model->get_features();

        // Load the main listing view
        $this->load->view('subscription_list', $data);
    }

    // -----------------------------
    // Add/Edit Plan
    // -----------------------------
    public function edit_plan($id = null) {

        if($this->input->post()) {
            $plan_data = [
                'name' => $this->input->post('name'),
                'price' => $this->input->post('price'),
                'yearly_price' => $this->input->post('yearly_price'),
                'status' => $this->input->post('status')
            ];

            if($id) {
                $this->Subscription_model->update_plan($id, $plan_data);
                $this->session->set_flashdata('success', 'Plan updated successfully.');
            } else {
                $this->Subscription_model->add_plan($plan_data);
                $this->session->set_flashdata('success', 'Plan added successfully.');
            }

            redirect('subscription');
        }

        $data = [];
        if($id) {
            $data['plan'] = $this->Subscription_model->get_plan($id);
            if(!$data['plan']){
                show_404(); // Plan not found
            }
        }

        $this->load->view('plan_form', $data); // Make sure view file exists
    }

    // -----------------------------
    // Delete Plan
    // -----------------------------
    public function delete_plan($id) {
        $this->Subscription_model->delete_plan($id);
        $this->session->set_flashdata('success', 'Plan deleted successfully.');
        redirect('subscription');
    }

    // -----------------------------
    // Add/Edit Feature
    // -----------------------------
    public function edit_feature($id = null) {

        if($this->input->post()) {
            $feature_data = [
                'feature_name' => $this->input->post('feature_name')
            ];

            if($id) {
                $this->Subscription_model->update_feature($id, $feature_data);
                $this->session->set_flashdata('success', 'Feature updated successfully.');
            } else {
                $this->Subscription_model->add_feature($feature_data);
                $this->session->set_flashdata('success', 'Feature added successfully.');
            }

            redirect('subscription');
        }

        $data = [];
        if($id) {
            $data['feature'] = $this->Subscription_model->get_feature($id);
            if(!$data['feature']){
                show_404(); // Feature not found
            }
        }

        $this->load->view('feature_form', $data); // Make sure view file exists
    }

    // -----------------------------
    // Delete Feature
    // -----------------------------
    public function delete_feature($id) {
        $this->Subscription_model->delete_feature($id);
        $this->session->set_flashdata('success', 'Feature deleted successfully.');
        redirect('subscription');
    }
}
