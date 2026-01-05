<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductBadges extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ProductBadges_model');
        $this->load->library('session');
        if(!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] ==''){
			redirect(BASE_URL);
		}
    }

    // List page
    public function index()
    {
        $publisher_id  = $this->session->userdata('LoginID');
        $data['pb_category']  = $this->ProductBadges_model->get_product_badges_categories();
        $data['productList']  = $this->ProductBadges_model->getProductBlockList($publisher_id);
        $data['documentList']  = $this->ProductBadges_model->getDocumentList($publisher_id);
        $data['merchantDetails']  = $this->ProductBadges_model->getMerchantDetails($publisher_id);

        
        // echo "<pre>";
        // print_r($data['documentList']);
        // die;
        $this->load->view('productbadges/index', $data); // ✅ no array('data'=>$data)

    }
    public function getAppliedProducts($catId)
    {
        $publisher_id  = $this->session->userdata('LoginID');
        $products = $this->ProductBadges_model->getAppliedProductsByCategory($catId,$publisher_id);
        //  echo "<pre>";
        // print_r($products);
        // die;
        // return as JSON for AJAX
        echo json_encode($products);
    }

public function submitApply()
{
    $company_name      = $this->input->post('company_name');
    $brn               = $this->input->post('brn');
    $contact_person    = $this->input->post('contact_person');
    $mobile            = $this->input->post('mobile');
    $email             = $this->input->post('email');
    $location          = $this->input->post('location');
    $prod_badge_cat_id = $this->input->post('prod_badge_cat_id');
    $terms             = $this->input->post('terms');
    $status            = "pending";

    $documentList_arr = $this->input->post('documentList') ?? [];
    $documentListing  = !empty($documentList_arr) ? ',' . implode(',', $documentList_arr) . ',' : '';

    $ProductList_arr = $this->input->post('productList') ?? [];

    if (!empty($ProductList_arr)) {
        foreach ($ProductList_arr as $productId) {
            $insertdata = [
                'company_name'      => $company_name,
                'prod_badge_cat_id' => $prod_badge_cat_id,
                'brn'               => $brn,
                'contact_person'    => $contact_person,
                'mobile'            => $mobile,
                'email'             => $email,
                'location'          => $location,
                'documents'         => $documentListing,
                'terms'             => $terms,
                'assigned_products' => $productId,
                'created_by'        => $_SESSION['LoginID'],
                'created_at'        => time(),
                'status'            => $status,
                'ip'                => $_SERVER['REMOTE_ADDR']
            ];

            $this->db->insert('products_badge_apply', $insertdata);

            $statusColumns = [
                1 => 'made_in_maurituus_approval_status',
                2 => 'social_empowerment_approval_status',
                3 => 'environment_friendly_approval_status',
                4 => 'health_friendly_approval_status'
            ];

            if (isset($statusColumns[$prod_badge_cat_id])) {
                $column = $statusColumns[$prod_badge_cat_id];
                $this->db->where('id', $productId);
                $this->db->set($column, $status);
                $this->db->update('products');
            }
        }
    }

    // ✅ Set flashdata
    $this->session->set_flashdata('success', 'Application submitted successfully!');

    redirect('productbadges');
}



}
