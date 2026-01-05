<?php
class Driver_model extends CI_Model {

    private $image_path = 'admin/public/images/';

    public function get_all() {
        return $this->db->get('driver_details')->result_array();
    }

    public function get($id) {
        return $this->db->get_where('driver_details', ['id' => $id])->row_array();
    }

    public function insert($data) {
        // Handle file upload if present
        if (!empty($_FILES['profile_photo']['name'])) {
            $upload = $this->upload_image();
            if ($upload['status']) {
                $data['profile_photo'] = $upload['file_name'];
            } else {
                return false; // stop insert if upload fails
            }
        }
        return $this->db->insert('driver_details', $data);
    }

    public function update($id, $data) {
        // Handle file upload if present
        if (!empty($_FILES['profile_photo']['name'])) {
            $upload = $this->upload_image();
            if ($upload['status']) {
                $data['profile_photo'] = $upload['file_name'];
            } else {
                return false; // stop update if upload fails
            }
        }
        return $this->db->update('driver_details', $data, ['id' => $id]);
    }

    public function delete($id) {
        // Get driver for deleting image also
        $driver = $this->get($id);
        if ($driver && !empty($driver['profile_photo'])) {
            $file = FCPATH . $this->image_path . $driver['profile_photo'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        return $this->db->delete('driver_details', ['id' => $id]);
    }

    private function upload_image() {
        $config['upload_path']   = FCPATH . $this->image_path;
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size']      = 2048; // 2MB
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('profile_photo')) {
            return ['status' => true, 'file_name' => $this->upload->data('file_name')];
        } else {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            return ['status' => false];
        }
    }
}
