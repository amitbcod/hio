<?php
defined('BASEPATH') or exit('No direct script access allowed');
class ShippingChargesController extends CI_Controller
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
		$this->load->model('CommonModel');
		$this->load->helper('url');
		$this->load->model('CustomerModel');
		$this->load->model('WebshopModel');
		$this->load->model('ShippingChargesModel');
		
		
		if(!isset($_SESSION['LoginID']) || $_SESSION['LoginID'] ==''){
			redirect(BASE_URL);
		}
		
	}
	
	public function index()
	{
		$data['side_menu']='webShop';
		$data['PageTitle']='Shipping Charges';
		$shop_id =	$this->session->userdata('ShopID');
		$data['shipping_charges_info'] = $this->ShippingChargesModel->get_all_shipping_charges();
		$this->load->view('webshop/shipping/shipping_charges',$data);
	}
	
	public function submit_shipping_charges()
	{
		//|| empty($_POST['blockIdentifier'])
		if(empty($_POST['tax_type']) && $_POST['tax_type'] == 0 ) 
		{
			echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
			exit;
		}else{
			$tax_type = $this->CommonModel->custom_filter_input($_POST['tax_type']);
			if($_POST['tax_type']== 3 )
			{
				// $pattern = '^[0-9][0-9]?(?:\.[0-9][0-9]?)?$';
				$tax_fix_percentage= $this->CommonModel->custom_filter_input($_POST['tax_fix_percentage']);

			}
			// $nIdentifier = str_replace(" ", "-", trim($nIdentifier));
			// $nIdentifier = preg_replace('\d{1,2}\.\d{2}', '', $nIdentifier);
			// $nIdentifier = strtolower($nIdentifier);

			$shop_id = $this->session->userdata('ShopID');
	    	// $webshop_details = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$shop_id),'shipping_tax_type,shipping_tax_fix_percentage');

			// if($_POST['tax_type']== 3)
			// {
			// 	//Update
			// 	$where_arr=array('shop_id'=>$shop_id);
			// 	$update=array(	
			// 		'shipping_tax_type'=> $tax_type,
			// 		'shipping_tax_fix_percentage'=> $tax_fix_percentage,
			// 		// 'updated_at'=>time(),
			// 		// 'ip'=>$_SERVER['REMOTE_ADDR']
			// 	);

			// 	$rowAffected = $this->CommonModel->updateData('fbc_users_shop',$where_arr,$update);
			// 	if($rowAffected){
			// 		$redirect = base_url('webshop/shipping-charges');
			// 		echo json_encode(array('flag' => 1, 'msg' => "Success",'redirect'=>$redirect));
			// 		exit();
			// 	}else{
			// 		echo json_encode(array('flag' => 0, 'msg' => "Nothing to update!"));
			// 		exit;
			// 	}

			// }else
			//{
				//Insert
				$default_shipping_tax_percentage= 0.00;
				$where_arr=array('shop_id'=>$shop_id);
				$update=array(	
					'shipping_tax_type'=> $tax_type,
					'shipping_tax_fix_percentage'=> $default_shipping_tax_percentage,
					// 'updated_at'=>time(),
					// 'ip'=>$_SERVER['REMOTE_ADDR']
				);

				$rowAffected = $this->CommonModel->updateData('fbc_users_shop',$where_arr,$update);
				if($rowAffected){
					$redirect = base_url('webshop/shipping-charges');
					echo json_encode(array('flag' => 1, 'msg' => "Success",'redirect'=>$redirect));
					exit();
				}else{
					echo json_encode(array('flag' => 0, 'msg' => "Nothing to update!"));
					exit;
				}
			//}
		}
	}

	public function createnew_shipping_charges()
	{
		$data['side_menu']='webShop';
		$data['PageTitle']='Shipping Charges - Create New';
		$shop_id = $this->session->userdata('ShopID');
		$data['customer_type_details'] = $this->CustomerModel->get_customer_type_details();
		$data['currency_symbol']=$this->CommonModel->getShopCurrency($shop_id);
		$data['country_name']=$this->CommonModel->getShopCountryname($shop_id);
		$data['shipping_charges_master_info'] = $this->ShippingChargesModel->get_all_shipping_charges();
		if(!empty($data['shipping_charges_master_info']) && !empty($data['customer_type_details']))
		{
			$new_data= array_diff(array_column($data['customer_type_details'], 'id'), array_column($data['shipping_charges_master_info'], 'customer_type_id'));
			if(empty($new_data))
			{
				$data['new_customer_type_details']= '';
			}else
			{
				$data['new_customer_type_details'] = $this->ShippingChargesModel->get_customer_type_name($new_data);
			}
			
		}else
		{
			$data['new_customer_type_details']= $data['customer_type_details'];
		}
		
		$this->load->view('webshop/shipping/createnew_shipping_charges',$data);
	}

	public function edit_shipping_charge()
	{
		$data['side_menu']='webShop';
		$data['PageTitle']='Shipping Charges - Edit';
		$shipping_charge_ID = $this->uri->segment(3);
		//$shop_id = $this->session->userdata('ShopID');
		$data['customer_type_details'] = $this->CustomerModel->get_customer_type_details();
		// $data['currency_symbol']=$this->CommonModel->getShopCurrency($shop_id);
		// $data['country_name']=$this->CommonModel->getShopCountryname($shop_id);
		$data['shipping_charges_master_by_id'] = $this->ShippingChargesModel->getSingleDataByID('shipping_charges_master',array('id'=>$shipping_charge_ID),'*');
		$data['shipping_charges_master_weight_by_id'] = $this->ShippingChargesModel->getMultiDataById('shipping_charges_master_weight',array('shipping_id'=>$shipping_charge_ID),'*');

		$data['shipping_charges_master_info'] = $this->ShippingChargesModel->get_all_shipping_charges();
		$new_data= array_diff(array_column($data['customer_type_details'], 'id'), array_column($data['shipping_charges_master_info'], 'customer_type_id'));



		$cust_id= $data['shipping_charges_master_by_id']->customer_type_id;
			$data['new_customer_type_details'] = $this->CustomerModel->get_single_customer_type_details($cust_id);
		 array_push($new_data,$cust_id);
		
		if(count($new_data) > 0 )
		{
			$data['new_customer_type_details'] = $this->ShippingChargesModel->get_customer_type_name($new_data);
			// $data['new_customer_type_details']= $new_data ;
		}else
		{
			$cust_id= $data['shipping_charges_master_by_id']->customer_type_id;
			$data['new_customer_type_details'] = $this->CustomerModel->get_single_customer_type_details($cust_id);
		}
		
		$data['num_rows'] =  count($data['shipping_charges_master_weight_by_id']);
		
		$this->load->view('webshop/shipping/edit_shipping_charge',$data);
	}

	public function save_shipping_charges()
	{	
		$based_on_cart= isset($_POST['based_on_cart']) ? 1: 0;
		$based_on_country= isset($_POST['based_on_country']) ? 1: 0;
		$based_on_cart_weight= isset($_POST['based_on_cart_weight']) ? 1: 0;
		
		if( $based_on_cart ==0 && $based_on_country == 0 && $based_on_cart_weight==0)	
		{
			echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
			exit;
		}else
		{
			if(!empty($_POST['shipping_charge_id']) && $_POST['shipping_charge_id'] != '')
			{
				$id=$_POST['shipping_charge_id'];
				$shipping_code = $_POST['shipping_code'];
				$loginId = $this->session->userdata('LoginID');
					$updateData=array(	
							'shipping_code'=> $shipping_code,
							'status'=> $_POST['status'],
							'shipping_details'=> $_POST['descp'],
							'customer_type_id'=> $_POST['customer_type'],
							'cart_cost_flag'=> isset($_POST['based_on_cart']) ? 1: 0,
							'cart_cost_freeship_flag' => isset($_POST['free_shipping']) ? 1: 0,
							'shipping_country_flag'=> isset($_POST['based_on_country']) ? 1: 0,
							'cart_weight_flag'=> isset($_POST['based_on_cart_weight']) ? 1: 0,
							'cart_cost_charge'=> $_POST['charge'],
							'cart_cost_freeship'=> $_POST['free_shipping_charge'],
							'charge_in_own_webshop_country'=> $_POST['charge_in_own_country'],
							'charge_in_other_country'=> $_POST['charge_in_other_country'],
							'created_by'=> $loginId,
							'updated_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
					$where_arr=array('id'=>$id);
					$shipping_charge_id=$this->ShippingChargesModel->updateNewData('shipping_charges_master',$where_arr,$updateData);
					
					$where_arr_for_weight_del=array('shipping_id'=>$id);
					$this->ShippingChargesModel->deleteData('shipping_charges_master_weight',$where_arr_for_weight_del);

					$row_count = count($_POST['row_count']);

					for ($i = 0; $i < $row_count; $i++) {
						$insertdata=array(	
							'shipping_id'=> $id,
							'charge'=> $_POST['charge_on_cart_weight'][$i],
							'from_weight'=> $_POST['min_weight'][$i],
							'to_weight'=> $_POST['max_weight'][$i],
							
							
						);
					$shipping_master_wight_id =$this->ShippingChargesModel->insertData('shipping_charges_master_weight',$insertdata);
					}
					if($shipping_charge_id){
						$redirect = base_url('webshop/shipping-charges');
						echo json_encode(array('flag' => 1, 'msg' => "Successfully Updated",'redirect'=>$redirect));
						exit();
					}else{
						echo json_encode(array('flag' => 0, 'msg' => "Nothing to update!"));
						exit;
					}

			}else
			{
				$shipping_code = $_POST['shipping_code'];
				$check_shipping_code= $this->WebshopModel->getSingleDataByID('shipping_charges_master',array('shipping_code'=>$shipping_code),'*');
				if(empty($check_shipping_code))
				{
					$shipping_code= $this->CommonModel->custom_filter_input($_POST['shipping_code']);
					$loginId = $this->session->userdata('LoginID');
					$insertdata=array(	
							'shipping_code'=> $shipping_code,
							'status'=> $_POST['status'],
							'shipping_details'=> $_POST['descp'],
							'customer_type_id'=> $_POST['customer_type'],
							'cart_cost_flag'=> isset($_POST['based_on_cart']) ? 1: 0,
							'cart_cost_freeship_flag' => isset($_POST['free_shipping']) ? 1: 0,
							'shipping_country_flag'=> isset($_POST['based_on_country']) ? 1: 0,
							'cart_weight_flag'=> isset($_POST['based_on_cart_weight']) ? 1: 0,
							'cart_cost_charge'=> $_POST['charge'],
							'cart_cost_freeship'=> $_POST['free_shipping_charge'],
							'charge_in_own_webshop_country'=> $_POST['charge_in_own_country'],
							'charge_in_other_country'=> $_POST['charge_in_other_country'],
							'created_by'=> $loginId,
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
						);
					$shipping_charge_id=$this->ShippingChargesModel->insertData('shipping_charges_master',$insertdata);

					$row_count = count($_POST['row_count']);

					for ($i = 0; $i < $row_count; $i++) {
						$insertdata=array(	
							'shipping_id'=> $shipping_charge_id,
							'charge'=> $_POST['charge_on_cart_weight'][$i],
							'from_weight'=> $_POST['min_weight'][$i],
							'to_weight'=> $_POST['max_weight'][$i],
							
							
						);
					$shipping_master_wight_id =$this->ShippingChargesModel->insertData('shipping_charges_master_weight',$insertdata);
					}
					if($shipping_charge_id){
						$redirect = base_url('webshop/shipping-charges');
						echo json_encode(array('flag' => 1, 'msg' => "Success",'redirect'=>$redirect));
						exit();
					}else{
						echo json_encode(array('flag' => 0, 'msg' => "Nothing to update!"));
						exit;
					}
				}else
				{
					echo json_encode(array('flag'=>0, 'msg'=>"Shipping Code Already Exists."));
					exit;
				}
			
			}
			
		}
	}

	public function delete_shipping_charge()
	{		
		$shipping_charge_id = $_POST['shipping_charge_id'];

		$where_arr=array('id'=>$shipping_charge_id);
		$rowAffected = $this->ShippingChargesModel->deleteData('shipping_charges_master',$where_arr);

		$where_arr1=array('shipping_id'=>$shipping_charge_id);
		$rowAffected1 = $this->ShippingChargesModel->deleteData('shipping_charges_master_weight',$where_arr1);
		if($rowAffected && $rowAffected1)
		{
			$redirect = base_url('webshop/shipping-charges');
			echo json_encode(array('flag' => 1, 'msg' => "Successfully Deleted",'redirect'=>$redirect));
			exit();
		}else{
			echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));
			exit;
		}
	}

	public function delete_cart_weight()
	{		
		$row_id = $_POST['row_id'];

		$where_arr=array('id'=>$row_id);
		$rowAffected = $this->ShippingChargesModel->deleteData('shipping_charges_master_weight',$where_arr);
		if($rowAffected )
		{
			$redirect = base_url('webshop/shipping-charges');
			echo json_encode(array('flag' => 1, 'msg' => "Successfully Deleted Row",'redirect'=>$redirect));
			exit();
		}else{
			echo json_encode(array('flag' => 0, 'msg' => "nothing to delete!"));
			exit;
		}
	}

	


}
