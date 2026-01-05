<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductsNotifyController extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
		
		$this->load->model('ProductNotifyModel');
		$this->load->model('CommonModel');
		$this->load->library('encryption');

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		
	}
	
	public function ProductsList()
	{
		$data['PageTitle']='Webshop - Coming Soon Products';
		$data['side_menu']='webshop';
		$this->load->view('webshop/ProductNotifyList',$data);
	}

	public function loadordersajax()
	{
		$products_list = $this->ProductNotifyModel->get_datatables_data();
		$data = array();
		foreach($products_list as $value){

			$row= array();
			$row[] = $value->id;
			$row[] = $value->email_id;

			$customer_name =  (isset($value->customer_id) &&  $value->customer_id != 0 ) ? $value->first_name .' '. $value->last_name : '';

			if($customer_name!= ''){ 
				$url = base_url().'customer-details/'. $value->customer_id;
				$customer = "<a href=" . $url .">".  $customer_name ."</a>";
			}else{
				$customer = '-';
			}

			$row[] = $customer;

			if(isset($value->product_name))
			{
				$url = base_url().'seller/product/edit/'.$value->product_id;
				$product_name = "<a href=" . $url .">". $value->product_name ."</a>";
			}

			$row[] = $product_name;
			$row[] = $value->notify_count;
			$row[] = date('d/m/Y',$value->created_at);

			$data[] = $row;  
		}
		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->ProductNotifyModel->countTotalRecords(),
			"recordsFiltered" => $this->ProductNotifyModel->countFiltteredProduct(),
			"data" => $data,
		);
		echo json_encode($output);
		exit();
		
	}

	
}
