<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Image_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function update_product_image($oldImage, $newImage) {
  
        $data = ['base_image' => $newImage];
        // $this->db->where('id', "157"); // Assuming 'id' is the primary key in your 'products' table
        $this->db->where('base_image', $oldImage);
        return $this->db->update('products', $data); // Adjust table name and conditions as necessary
    }

    public function update_banner_image($oldImage, $newImage) {

        $data = ['banner_image' => $newImage];
        // $this->db->where('id', "4"); // Assuming 'id' is the primary key in your 'products' table
        $this->db->where('banner_image', $oldImage);
        return $this->db->update('banners', $data); // Adjust table name and conditions as necessary
    }
}
