<?php
defined('BASEPATH') or exit('No direct script access allowed');
class CronTestController  extends CI_Controller
{
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
	 function __construct()
	{
		parent::__construct();
		$this->load->helper('url');	
		$this->load->model('CronTestModel');
	}
	
	
	public function testcron()
	{
		$data['PageTitle']='Cron gmp_testbit(a, index)';
		$data['side_menu']='bulk-add';
		$shop_id=$this->uri->segment(3);
		

		
		$shopData=$this->CommonModel->getShopOwnerData($shop_id);
		if(isset($shopData))
		{
			$shop_database_name=$shopData->database_name;
			$shop_fbc_user_id=$shopData->fbc_user_id;

			if(isset($shop_database_name) && $shop_database_name!='')
			{
				$this->load->database();
				$config_app = fbc_switch_db_dynamic(DB_PREFIX.$shop_database_name);		
				$this->seller_db = $this->load->database($config_app,TRUE);
				if($this->seller_db->conn_id) {
					//do something
				} else {
					redirect(base_url());
				}
			}else{
				redirect(base_url());
			}

		}

		$insertdata_arr=array(	
				'created_at'=>time()
		);
		$rowAffected=$this->CronTestModel->insertData('cron_test_table',$insertdata_arr);
		if(isset($rowAffected))
		{
			$redirect = base_url();
			echo json_encode(array('flag' => 1, 'msg' => "Successfully Inserted ",'redirect'=>$redirect));
			exit();
		}else{
			echo json_encode(array('flag' => 0, 'msg' => "Nothing to Insert!"));
			exit;
		}
	}


}
