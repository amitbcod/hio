<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search_Term_Controller extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
		$this->load->model('CommonModel');
		$this->load->model('Search_term_model');
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
    }	

	public function index()
	{
		if($_SESSION['UserRole'] !== 'Super Admin') {
            if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/search_terms',$this->session->userdata('userPermission'))){ 
                redirect('dashboard');
            }
        }


			$data['search_terms'] = $this->Search_term_model->get_all_search_terms();
			// print_r($data['search_terms']);die();

			$data['PageTitle']='Search Terms';

			$data['side_menu']='webshop';

			$this->load->view('webshop/webshop_search_term_list',$data);

	}
}
