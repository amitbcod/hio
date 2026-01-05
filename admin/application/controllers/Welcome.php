<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	public function test()
	{
		 $config_app = fbc_switch_db_dynamic('parkmosp_shopinshop_shop_1');
		echo "Hi!!!<br>";	

		
		echo "Data from Main Databse<br>";
		
		$this->db->select("*");
        $this->db->from('fbc_users');
        $query = $this->db->get();
        $result= $query->result_array();
		
		$this->db->reset_query();
		
		var_dump($result);


		echo "Data from shop Databse<br>";
		$this->app_db = $this->load->database($config_app,TRUE);
		$this->app_db->select("*");
        $this->app_db->from('products');
        $query = $this->app_db->get();
        $result_shop= $query->result_array();
		var_dump($result_shop);
		
		
		
	}
}
