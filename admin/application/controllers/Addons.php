<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Addons extends CI_Controller
{



    public function __construct()
    {

        parent::__construct();

        $this->load->model('Addon_model');

        $this->load->model('AddonCategory_model'); // Load category model

        $this->load->helper(array('form', 'url'));

        $this->load->library('session');
    }



    // List all addon services

    public function index()
    {

        $data['addons'] = $this->Addon_model->get_all();

        $this->load->view('addons/list', $data);
    }



    // Create new addon

    public function create()
    {
        $data['categories'] = $this->AddonCategory_model->get_all(); // Get all categories

        if ($this->input->post()) {
            $price = (float)$this->input->post('price');
            $vat_percent = (float)$this->input->post('vat_percent');

            // Calculate VAT and final price
            $tax_amount = 0;
            $final_price = $price;

            if ($price > 0 && $vat_percent > 0) {
                $tax_amount = ($vat_percent / 100) * $price;
                $final_price = $price + $tax_amount;
            }

            $insert = [
                'category_id' => $this->input->post('category_id'),
                'title'       => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'price'       => $price,
                'vat_percent' => $vat_percent,
                'final_price' => $final_price,
                'status'      => $this->input->post('status'),
                'ip'          => $this->input->ip_address(),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];

            $this->Addon_model->insert($insert);
            $this->session->set_flashdata('success', 'Addon created successfully');
            redirect('addons');
        }

        $this->load->view('addons/create', $data);
    }




    // Edit existing addon

    public function edit($id)
    {
        // print_r($_POST); exit;


        $data['addon'] = $this->Addon_model->get($id);
        $data['categories'] = $this->AddonCategory_model->get_all();

        if (!$data['addon']) {
            show_404();
        }

        if ($this->input->post()) {
            $price = (float)$this->input->post('price');
            $vat_percent = (float)$this->input->post('vat_percent');

            // Calculate VAT & final price
            $tax_amount = 0;
            $final_price = $price;

            if ($price > 0 && $vat_percent > 0) {
                $tax_amount = ($vat_percent / 100) * $price;
                $final_price = $price + $tax_amount;
            }

            $update = [
                'category_id' => $this->input->post('category_id'),
                'title'       => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'price'       => $price,
                'vat_percent' => $vat_percent,
                'final_price' => $final_price,
                'status'      => $this->input->post('status'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];

            $this->Addon_model->update($id, $update);

            $this->session->set_flashdata('success', 'Addon updated successfully');
            redirect('addons');
        }

        $this->load->view('addons/edit', $data);
    }




    // Delete addon

    public function delete($id)
    {

        $this->Addon_model->delete($id);

        $this->session->set_flashdata('success', 'Addon deleted successfully');

        redirect('addons');
    }
}
