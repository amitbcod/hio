<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Subscription extends CI_Controller
{



    public function __construct()
    {

        parent::__construct();

        $this->load->model('Subscription_model');

        $this->load->library(['session', 'form_validation']);

        $this->load->helper(['url', 'form']);
    }



    // -----------------------------

    // List all plans and features

    // -----------------------------

    public function index()
    {

        $data['plans'] = $this->Subscription_model->get_plans();

        $data['features'] = $this->Subscription_model->get_features();



        // Load the main listing view

        $this->load->view('subscription_list', $data);
    }



    // -----------------------------

    // Add/Edit Plan

    // -----------------------------

    public function edit_plan($id = null)
    {
        if ($this->input->post()) {
            $price = (float)$this->input->post('price');
            $yearly_price = (float)$this->input->post('yearly_price');
            $vat = (float)$this->input->post('vat_percent');

            // ✅ Calculate VAT on yearly_price
            $yearly_price_with_vat = $yearly_price + ($yearly_price * $vat / 100);

            $plan_data = [
                'name'          => $this->input->post('name'),
                'price'         => $price,
                'yearly_price'  => $yearly_price, // Store price after VAT
                'vat_percent'   => $vat,
                'final_price'   => $yearly_price_with_vat,
                'status'        => $this->input->post('status')
            ];

            if ($id) {
                $this->Subscription_model->update_plan($id, $plan_data);
                $this->session->set_flashdata('success', 'Plan updated successfully.');
            } else {
                $this->Subscription_model->add_plan($plan_data);
                $this->session->set_flashdata('success', 'Plan added successfully.');
            }

            redirect('subscription');
        }

        $data = [];
        if ($id) {
            $data['plan'] = $this->Subscription_model->get_plan($id);
            if (!$data['plan']) {
                show_404();
            }
        }

        $this->load->view('plan_form', $data);
    }





    // -----------------------------

    // Delete Plan

    // -----------------------------

    public function delete_plan($id)
    {

        $this->Subscription_model->delete_plan($id);

        $this->session->set_flashdata('success', 'Plan deleted successfully.');

        redirect('subscription');
    }



    // -----------------------------

    // Add/Edit Feature

    // -----------------------------

    public function edit_feature($id = null)
    {



        if ($this->input->post()) {

            $feature_data = [

                'feature_name' => $this->input->post('feature_name')

            ];



            if ($id) {

                $this->Subscription_model->update_feature($id, $feature_data);

                $this->session->set_flashdata('success', 'Feature updated successfully.');
            } else {

                $this->Subscription_model->add_feature($feature_data);

                $this->session->set_flashdata('success', 'Feature added successfully.');
            }



            redirect('subscription');
        }



        $data = [];

        if ($id) {

            $data['feature'] = $this->Subscription_model->get_feature($id);

            if (!$data['feature']) {

                show_404(); // Feature not found

            }
        }



        $this->load->view('feature_form', $data); // Make sure view file exists

    }



    // -----------------------------

    // Delete Feature

    // -----------------------------

    public function delete_feature($id)
    {

        $this->Subscription_model->delete_feature($id);

        $this->session->set_flashdata('success', 'Feature deleted successfully.');

        redirect('subscription');
    }



    public function map_features()
    {

        $data['plans'] = $this->Subscription_model->get_plans();

        $data['features'] = $this->Subscription_model->get_features();



        // fetch existing mapping

        $plan_features = $this->Subscription_model->get_plan_features();

        $mapped = [];

        foreach ($plan_features as $pf) {

            $mapped[$pf['feature_id']][$pf['plan_id']] = $pf['value'];
        }

        $data['mapped'] = $mapped;



        $this->load->view('map_features', $data);
    }



    public function save_feature_mapping()
    {

        $mapping = $this->input->post('mapping'); // matches your input names



        if (!empty($mapping)) {

            foreach ($mapping as $feature_id => $plan_values) {

                foreach ($plan_values as $plan_id => $value) {

                    $value = trim($value);

                    if ($value === '') continue;



                    // check if record exists

                    $existing = $this->db->get_where('plan_features', [

                        'feature_id' => $feature_id,

                        'plan_id' => $plan_id

                    ])->row_array();



                    if ($existing) {

                        $this->db->update('plan_features', ['value' => $value], ['id' => $existing['id']]);
                    } else {

                        $this->db->insert('plan_features', [

                            'feature_id' => $feature_id,

                            'plan_id' => $plan_id,

                            'value' => $value

                        ]);
                    }
                }
            }

            $this->session->set_flashdata('success', 'Feature mapping saved successfully.');
        } else {

            $this->session->set_flashdata('error', 'No values to save.');
        }



        redirect('subscription/map_features');
    }
}
