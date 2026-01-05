<?php

/**
 * @property CI_Controller $ci
 */
class HomeProductsList
{
    private $ci;
    private $featured_product;
    private $home_product_listing;
    private $hindi_magazines_product;
    private $regional_magazine_product;
    private $category_id;
    private $page_name;

    public function __construct($category_id, $page_name)
    {
        $this->ci = &get_instance();
        $this->page_name = $page_name ?? '';
        $category_id = $category_id ?? '';
        $productArr1 = array('categoryid' => $category_id);
        $this->home_product_listing = ProductRepository::get_product_list($productArr1);

        $limit_var = $limit ?? 25;

        $this->hindi_magazines_product = ProductRepository::get_hindi_magazines_products([
            'identifier' => 'hindi_magazines',
            'filter_type' => 'hindi_magazines',
            'customer_type_id' => $this->ci->session->userdata('CustomerTypeID') ?? 1,
            'limit' => $limit_var,
            'vat_percent_session' => $this->ci->session->userdata('vat_percent') ?? '',
            'lang_code' => $this->ci->session->userdata('lcode') ?? '',
            'customer_login_id' => $this->ci->session->userdata('LoginID') ?? ''
        ]);


        $this->regional_magazine_product = ProductRepository::get_regional_magazine_products([
            'identifier' => 'regional_magazine',
            'filter_type' => 'regional_magazine',
            'customer_type_id' => $this->ci->session->userdata('CustomerTypeID') ?? 1,
            'limit' => $limit_var,
            'vat_percent_session' => $this->ci->session->userdata('vat_percent') ?? '',
            'lang_code' => $this->ci->session->userdata('lcode') ?? '',
            'customer_login_id' => $this->ci->session->userdata('LoginID') ?? ''
        ]);
    }

    public function render()
    {

        if (empty($this->featured_product)) {
            return;
        }

        $this->ci->template->load('components/featured_products', ['featured_product' => $this->featured_product]);
    }
    public function magazineswithgifts()
    {

        if (empty($this->home_product_listing)) {
            return 'empty';
        }

        if ($this->page_name == 'JOURNALS' || $this->page_name == "CHILDREN'S MAGAZINE" || $this->page_name == 'FASHION') {
            $this->ci->template->load('components/home_categorys_products', ['section_title' => $this->page_name, 'magazinngift_product' => $this->home_product_listing]);
        } else {
            $this->ci->template->load('components/magazines_gift_products', ['magazinngift_product' => $this->home_product_listing]);
        }
    }

    public function productwithgiftslisting()
    {
        $this->page_name;
        $productArr1 = array('limit' => 3, 'customer_type_id' => 1);
        $home_gift_product_listing = ProductRepository::get_gift_products_list($productArr1);

        if ($this->page_name == 'INTERNATIONAL') {
            $this->ci->template->load('components/gift_products_listing', ['section_title' => $this->page_name, 'magazinngift_product' => $home_gift_product_listing]);
        }
    }


    public function hindi_magazine()
    {
        // $category_id = 3;
        // $this->hindi_magazines_product = ProductRepository::get_hindi_magazines_products(['category_id' => $category_id]);
        // print_r($this->hindi_magazines_product);
        // die;// print_r($this->hindi_magazines_product);
        // die;
        // print_r($this->hindi_magazines_product->hindi_magazines);
        $hindi_magazine_product = null; // Initialize with null instead of an empty string
       // $data = $this->hindi_magazines_product->hindi_magazines;
$data = "";
        if (empty($data)) {
            return 'empty';
        } else {
            $hindi_magazine_product = $data;
            $this->ci->template->load('components/hindi_magazine_products', ['section_title' => $this->page_name, 'hindi_magazine_product' => $hindi_magazine_product]);
        }
    }

    public function regional_magazine()
    {
        // print_r($this->regional_magazine_product);
        // die;
        $regional_magazine_product = null; // Initialize with null instead of an empty string
        //$data = $this->regional_magazine_product->regional_magazine;
        $data = "";
        if (empty($data)) {
            return 'empty';
        } else {
            $regional_magazine_product = $data;
            $this->ci->template->load('components/regional_magazine_products', ['section_title' => $this->page_name, 'regional_magazine_product' => $regional_magazine_product]);
        }
    }
}
