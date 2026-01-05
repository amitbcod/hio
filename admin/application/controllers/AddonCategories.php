<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AddonCategories extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('AddonCategory_model');
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
    }

    public function index() {
        $data['categories'] = $this->AddonCategory_model->get_all();
        $this->load->view('addon_categories/index', $data);
    }

    public function create() {
        if ($this->input->post()) {
            $insert = [
                'name'   => $this->input->post('name'),
                'status' => $this->input->post('status'),
                'ip'     => $this->input->ip_address()
            ];
            $this->AddonCategory_model->insert($insert);
            $this->session->set_flashdata('success', 'Addon Category added!');
            redirect('addonCategories');
        }
        $this->load->view('addon_categories/create');
    }

    public function edit($id) {
        $data['category'] = $this->AddonCategory_model->get($id);
        if (!$data['category']) show_404();

        if ($this->input->post()) {
            $update = [
                'name'   => $this->input->post('name'),
                'status' => $this->input->post('status'),
                'ip'     => $this->input->ip_address()
            ];
            $this->AddonCategory_model->update($id, $update);
            $this->session->set_flashdata('success', 'Addon Category updated!');
            redirect('addonCategories');
        }
        $this->load->view('addon_categories/edit', $data);
    }

    public function delete($id) {
        $this->AddonCategory_model->delete($id);
        $this->session->set_flashdata('success', 'Addon Category deleted!');
        redirect('addonCategories');
    }
}
