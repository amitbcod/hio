<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mydocuments extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mydocuments_model');
        $this->load->library('session');
        if (!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] == '') {
            redirect(BASE_URL);
        }
    }

    // List page
    public function index()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $mydocument = $this->Mydocuments_model->get_mydocuments($publisher_id);
        // echo "<pre>";print_r($mydocument);die;
        $this->load->view('mydocuments/mydocumentsList', array('mydocument' => $mydocument));
    }

    public function add()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $this->load->view('mydocuments/add_mydocument');
    }

    public function insert()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $document_name = $this->input->post('document_name', true);



        // ===== Handle File Upload =====
        if (!empty($_FILES['document_file']['name'])) {
            $config['upload_path']   = SIS_SERVER_PATH . '/' . 'uploads/documents/';
            $config['allowed_types'] = 'doc|docx|jpg|jpeg|png|pdf|xlsx';

            $config['max_size']      = 5120; // 5 MB

            // create folder if not exists
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('document_file')) {
                $uploadData   = $this->upload->data();
                $document_file = $uploadData['file_name'];
            } else {
                $arrResponse = array('status' => 400, 'message' => $this->upload->display_errors());
                echo json_encode($arrResponse);
                exit;
            }
        }

        // print_r($document_file); die;


        // ===== Insert into DB =====
        $insert = [
            'merchant_id'   => $publisher_id,
            'document_name' => $document_name,
            'document_file' => $document_file,
            'created_at'    => time(),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];

        $this->db->insert('mydocuments', $insert);

        $arrResponse = [
            'status'  => 200,
            'message' => 'Document added successfully.',
            'redirect_url' => base_url('mydocuments') // 👈 redirect target
        ];

        echo json_encode($arrResponse);
        exit;
    }

    // Service details page
    public function edit($id)
    {
        $data = $this->Mydocuments_model->get_mydocuments_data($id);
        // echo "<pre>";print_r($data);die;

        $this->load->view('mydocuments/edit_mydocument', array('data' => $data));
    }
    public function update($id)
    {
        $publisher_id  = $this->session->userdata('LoginID');
        $document_name = $this->input->post('document_name', true);

        // Get the old record first
        $oldDoc = $this->Mydocuments_model->get_mydocuments_data($id);
        $document_file = $oldDoc->document_file; // keep old file by default

        // ===== Handle File Upload (if new file uploaded) =====
        if (!empty($_FILES['document_file']['name'])) {
            $config['upload_path']   = SIS_SERVER_PATH . '/' . 'uploads/documents/';
            $config['allowed_types'] = 'doc|docx|jpg|jpeg|png|pdf|xlsx';

            $config['max_size']      = 5120; // 5 MB

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('document_file')) {
                $uploadData   = $this->upload->data();
                $document_file = $uploadData['file_name']; // replace with new file
            } else {
                $arrResponse = array('status' => 400, 'message' => $this->upload->display_errors());
                echo json_encode($arrResponse);
                exit;
            }
        }

        // ===== Update DB =====
        $update = [
            'merchant_id'   => $publisher_id,
            'document_name' => $document_name,
            'document_file' => $document_file, // either new file OR old one
            'updated_at'    => time(),
            'ip'            => $_SERVER['REMOTE_ADDR']
        ];

        $this->db->where('id', $id)->update('mydocuments', $update);

        $arrResponse = [
            'status'  => 200,
            'message' => 'Document updated successfully.',
            'redirect_url' => base_url('mydocuments')
        ];

        echo json_encode($arrResponse);
        exit;
    }

    public function messaging()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $data['messaging'] = $this->CommonModel->get_messaging($publisher_id);
        $this->load->view('messaging_list', $data);
    }


    public function messages_view($product_id, $customer_identifier)
    {
        $publisher_id = $this->session->userdata('LoginID');

        $customer_identifier = urldecode($customer_identifier); // decode email if passed

        $this->db->from('product_questions');
        $this->db->where('product_id', $product_id);
        $this->db->where('merchant_id', $publisher_id);

        if (is_numeric($customer_identifier) && $customer_identifier > 0) {
            // Registered customer
            $this->db->where('customer_id', $customer_identifier);
        } else {
            // Anonymous customer, use email
            $this->db->where('customer_id', 0);
            $this->db->where('email', $customer_identifier);
        }

        $this->db->order_by('id', 'ASC');
        $product_questions_data = $this->db->get()->result();

        $data['product_questions_data'] = $product_questions_data;
        $data['product_id'] = $product_id;
        $data['customer_identifier'] = $customer_identifier;

        $this->load->view('messaging_conversation', $data);
    }

    public function update_messaging()
    {
        $publisher_id = $this->session->userdata('LoginID');
        $product_id = $this->input->post('product_id');
        $customer_identifier = $this->input->post('customer_identifier');
        $merchant_reply = trim($this->input->post('merchant_reply'));

        if (!empty($merchant_reply)) {
            // Fetch the latest message for this product + merchant + customer
            $this->db->from('product_questions');
            $this->db->where('product_id', $product_id);
            $this->db->where('merchant_id', $publisher_id);

            if (is_numeric($customer_identifier) && $customer_identifier > 0) {
                $this->db->where('customer_id', $customer_identifier);
            } else {
                $this->db->where('customer_id', 0);
                $this->db->where('email', $customer_identifier);
            }

            $this->db->order_by('created_at', 'ASC');
            $this->db->limit(1);
            $last_msg = $this->db->get()->row();

            // Case 1: If the latest message has an empty merchant_reply, just update it
            if (!empty($last_msg) && empty($last_msg->merchant_reply) && !empty($last_msg->message)) {
                $update_data = [
                    'merchant_reply' => $merchant_reply,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->db->where('id', $last_msg->id);
                $this->db->update('product_questions', $update_data);
            }
            // Case 2: Otherwise, insert a new reply record
            else {
                $insert_data = [
                    'product_id' => $product_id,
                    'merchant_id' => $publisher_id,
                    'customer_id' => ($last_msg && $last_msg->customer_id > 0) ? $last_msg->customer_id : 0,
                    'email' => $last_msg ? $last_msg->email : $customer_identifier,
                    'name' => $last_msg ? $last_msg->name : '',
                    'category' => $last_msg ? $last_msg->category : '',
                    'message' => '', // merchant replying
                    'merchant_reply' => $merchant_reply,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->db->insert('product_questions', $insert_data);
            }

            $this->session->set_flashdata('success', 'Reply sent successfully!');
        }

        redirect('Mydocuments/messages_view/' . $product_id . '/' . urlencode($customer_identifier));

    }
}
