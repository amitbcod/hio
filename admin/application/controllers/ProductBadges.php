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
        
        // echo "<pre>";
        // print_r($data['documentList']);
        // die;
        $this->load->view('productbadges/index', $data); // ✅ no array('data'=>$data)

    }
    public function getAppliedProducts($catId)
    {
        // $publisher_id  = $this->session->userdata('LoginID');
        $products = $this->ProductBadges_model->getAppliedProductsByCategory($catId);
        //  echo "<pre>";
        // print_r($products);
        // die;
        // return as JSON for AJAX
        echo json_encode($products);
    }

    public function updateStatuses()
    {
        $updates = $this->input->post('updates', true);
        $success = true;

        $statusColumns = [
            1 => 'made_in_maurituus_approval_status',
            2 => 'social_empowerment_approval_status',
            3 => 'environment_friendly_approval_status',
            4 => 'health_friendly_approval_status'
        ];

        if (!empty($updates) && is_array($updates)) {
            foreach ($updates as $u) {
                $productId         = (int)$u['id'];
                $status            = $u['status'];
                $prod_badge_cat_id = (int)$u['prod_badge_cat_id'];

                if (isset($statusColumns[$prod_badge_cat_id])) {
                    $column = $statusColumns[$prod_badge_cat_id];

                    // ✅ Update products table
                    $res1 = $this->db->where('id', $productId)
                                    ->update('products', [$column => $status]);

                    // ✅ Update the exact row in products_badge_apply
                    $res2 = $this->db->where('prod_badge_cat_id', $prod_badge_cat_id)
                                    ->where('assigned_products', $productId)
                                    ->update('products_badge_apply', ['status' => $status]);

                    if (!$res1 || !$res2) {
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }
        } else {
            $success = false;
        }

        echo json_encode([
            'success'  => $success,
            'redirect' => base_url('productbadges')
        ]);
    }

    public function submitApply(){

        $company_name = $_POST['company_name'];
        $brn = $_POST['brn'];
        $contact_person = $_POST['contact_person'];
        $mobile = $_POST['mobile'];
        $email = $_POST['email'];
        $location = $_POST['location'];
        $prod_badge_cat_id = $_POST['prod_badge_cat_id'];
        $terms = $_POST['terms'];

        $documentList_arr = isset($_POST['documentList']) ? $_POST['documentList'] : array();
            if (isset($documentList_arr)) {
                $documentList = implode(',', $documentList_arr);
                $documentListing = ',' . $documentList . ',';
            }

        $ProductList_arr = isset($_POST['productList']) ? $_POST['productList'] : array();
            if (isset($ProductList_arr)) {
                $ProductList = implode(',', $ProductList_arr);
                $ProductListing = ',' . $ProductList . ',';
            }
        $insertdata = array(
            'company_name' => $company_name,
            'prod_badge_cat_id' => $prod_badge_cat_id,
            'brn' => $brn,
            'contact_person' => $contact_person,
            'mobile' => $mobile,
            'email' => $email,
            'location' => $location,
            'documents' => $documentListing,
            'terms' => $terms,
            'assigned_products' => $ProductListing,
            'created_by' => $_SESSION['LoginID'],
            'created_at' => time(),
            'ip' => $_SERVER['REMOTE_ADDR']
        );
        $this->db->insert('products_badge_apply', $insertdata);   
        redirect('productbadges');
    }

}
