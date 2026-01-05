<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminGiftcards extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Giftcard_model');
    }

    public function index()
    {
        $data['values'] = $this->db->get('gift_card_values')->result();
        $this->load->view('admin/giftcard_values_list', $data);
    }

    public function add()
    {
        if ($this->input->post()) {
            $this->db->insert('gift_card_values', [
                'title' => $this->input->post('title'),
                'amount' => $this->input->post('amount'),
                'active' => $this->input->post('active') ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            redirect('AdminGiftcards');
        }
        $this->load->view('admin/giftcard_value_form');
    }

    public function edit($id)
    {
        if ($this->input->post()) {
            $this->db->where('id',$id)->update('gift_card_values', [
                'title' => $this->input->post('title'),
                'amount' => $this->input->post('amount'),
                'active' => $this->input->post('active') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            redirect('AdminGiftcards');
        }
        $data['value'] = $this->db->get_where('gift_card_values', ['id'=>$id])->row();
        $this->load->view('admin/giftcard_value_form', $data);
    }

    public function delete($id)
    {
        $this->db->delete('gift_card_values', ['id'=>$id]);
        redirect('AdminGiftcards');
    }
}
