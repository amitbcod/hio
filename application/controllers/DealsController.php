<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DealsController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->library('pagination');

        $this->load->helper(['url', 'form', 'language']);

        $site_lang = $this->session->userdata('site_lang');
        if ($site_lang) {
            $this->lang->load('content', $site_lang);
        } else {
            $this->lang->load('content', 'english');
        }
    }

    // ✅ Daily Deals (existing, unchanged)
    public function index($category_id = null) {
        $current_time = time();
        $limit = 12;
        $offset = (int) $this->input->get('per_page');

        $data['products'] = $this->Product_model->get_daily_deals($limit, $offset, $current_time, $category_id);
        $total_rows = $this->Product_model->count_daily_deals($current_time, $category_id);

        if ($category_id) {
            $config['base_url'] = site_url('daily-deals/category/'.$category_id);
        } else {
            $config['base_url'] = site_url('daily-deals');
        }
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['reuse_query_string'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['attributes'] = ['class' => 'page-link'];

        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        // Sidebar categories (daily deals only)
        $allCategories = $this->Product_model->get_daily_deal_categories($current_time);
        $categories = [];
        foreach($allCategories as $cat){
            $categories[] = (object)[
                'id' => $cat->id,
                'cat_name' => $cat->cat_name,
                'parent_id' => $cat->parent_id ?? 0
            ];
        }

        $data['categories'] = $categories;
        $data['active_category'] = $category_id;

        $this->load->view('daily_deals', $data);
    }

    // ✅ Flash Sale (new)
    public function flash_sale($category_id = null) {
        $current_time = time();
        $limit = 12;
        $offset = (int) $this->input->get('per_page');

        $data['products'] = $this->Product_model->get_flash_sales($limit, $offset, $current_time, $category_id);
        $total_rows = $this->Product_model->count_flash_sales($current_time, $category_id);

        if ($category_id) {
            $config['base_url'] = site_url('flash-sale/category/'.$category_id);
        } else {
            $config['base_url'] = site_url('flash-sale');
        }
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['reuse_query_string'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['attributes'] = ['class' => 'page-link'];

        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        // Sidebar categories (flash sale only)
        $allCategories = $this->Product_model->get_flash_sale_categories($current_time);
        $categories = [];
        foreach($allCategories as $cat){
            $categories[] = (object)[
                'id' => $cat->id,
                'cat_name' => $cat->cat_name,
                'parent_id' => $cat->parent_id ?? 0
            ];
        }

        $data['categories'] = $categories;
        $data['active_category'] = $category_id;

        $this->load->view('flash_sale', $data);
    }
}
