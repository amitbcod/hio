<?php

defined('BASEPATH') or exit('No direct script access allowed');



class MyOrdersController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->session->userdata('LoginID') == '') {
            redirect(BASE_URL.'customer/login');
        }

        $this->load->library("pagination");

    }

    /*

    public function getProfileDetails(){

        $data['PageTitle']= 'My Profile - Personal Information';

        $LoginID = $_SESSION['LoginID'];



        $shopcode = SHOPCODE;

        $shop_id = SHOP_ID;



        $data['side_tab'] = 'account_info';



        $apiUrl = '/webshop/customer_get_personal_info'; //customer_get_personal_info

        $postArr = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'customer_id'=>$LoginID);

        $response= $this->restapi->post_method($apiUrl,$postArr);

        //echo '<pre>';print_r($response);//exit;

        if($response->is_success=='true'){

            $data['customerData'] = $response->customerData;

        }



        $apiUrl2 = '/webshop/get_table_data'; //get_table_data

        $table = 'country_master';

        $flag = 'own';

        $postArr2 = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'table_name'=>$table,'database_flag'=>$flag);

        $response2= $this->restapi->post_method($apiUrl2,$postArr2);

        //echo '<pre>';print_r($response2);//exit;

        if($response2->is_success=='true'){

            $data['countryList'] = $response2->tableData;

        }

        $this->load->view('myprofile/personal_info', $data);

    }



    */

    

    
    public function getOrders()
    {
        $data['PageTitle']= 'My Profile - Orders';
        $data['side_tab'] = 'my_orders';

        $this->resetPrintDetails();
        $this->template->load('myprofile/my_orders', $data);
    }



    public function returnOrderDetail()
    {
        $data['PageTitle']= 'My Profile - Return Order';

		$shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $data['side_tab'] = 'my_orders';
        $return_order_id=$this->uri->segment(4);

        if (empty($return_order_id)) {
			redirect('customer/my-orders');
			return;
		}

		$shopDataflag = GlobalRepository::get_fbc_users_shop()?->result;
		if (!empty($shopDataflag)) {
			$data['shop_flag'] = $shopDataflag->shop_flag;
			$data['country_code'] = $shopDataflag->country_code;
		} else {
			$data['shop_flag'] = '';
			$data['country_code'] = '';
		}

		$data['online_stripe_payment_refund'] = GlobalRepository::get_custom_variable($shopcode, $shop_id, 'online_stripe_payment_refund', true) ?? 'no';

		$response = OrdersRepository::my_return_order_detail($shopcode, $shop_id, $return_order_id);
		if (!empty($response) && isset($response) && $response->is_success == 'true') {
			$OrderData = $response->OrderData;

			$data['OrderData'] = $OrderData;
		} else {
			$data['OrderData'] = array();
		}

		$this->template->load('myprofile/return_order_detail', $data);
	}



    public function setTempPrintDetails()
    {
		$this->session->set_userdata('reason_for_return', $_POST['reason_for_return']);
        $this->session->set_userdata('refund_payment_mode', $_POST['refund_payment_mode'] ?? '');
        $this->session->set_userdata('bank_name', $_POST['bank_name']);
        $this->session->set_userdata('bank_branch', $_POST['bank_branch']);
        $this->session->set_userdata('bic_swift', $_POST['bic_swift']);
        $this->session->set_userdata('ifsc_iban', $_POST['ifsc_iban']);
        $this->session->set_userdata('bank_acc_no', $_POST['bank_acc_no']);

        echo "success";
        exit;
    }

    public function resetPrintDetails()
    {
        $this->session->unset_userdata('reason_for_return');
        $this->session->unset_userdata('refund_payment_mode');
        $this->session->unset_userdata('bank_name');
        $this->session->unset_userdata('bank_branch');
        $this->session->unset_userdata('bic_swift');
        $this->session->unset_userdata('ifsc_iban');
        $this->session->unset_userdata('bank_acc_no');
    }

    public function printReturnOrder()
    {
        $data['PageTitle']= 'My Profile - Return Order';

		$shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $return_order_id=$this->uri->segment(3);

        if ($return_order_id!='') {
            $postPrintArr = array('return_order_id'=>$return_order_id);
            $printresponse= OrdersRepository::return_order_print($shopcode, $shop_id, $postPrintArr);

            $response= OrdersRepository::my_return_order_detail($shopcode, $shop_id, $return_order_id);

            $return_address_field='order_return_address';
            $ReturnApiResponse = GlobalRepository::get_custom_variable($shopcode, $shop_id, $return_address_field);
            if (!empty($ReturnApiResponse) && isset($ReturnApiResponse) && $ReturnApiResponse->statusCode=='200') {
                $ReturnValue=$ReturnApiResponse->custom_variable;
                $return_address=$ReturnValue->value;
                $data['returnAddress']=$return_address;
            } else {
                $return_address='';
                $data['returnAddress']=$return_address;
            }
            /*end return address*/

            if ($response->is_success=='true') {
				$data['OrderData']= $response->OrderData;
            } else {
                $data['OrderData'] = array();
            }

            $data['webshop_details'] = CommonRepository::get_webshop_details($shopcode, $shop_id);

            $this->template->load('myprofile/print_return_order', $data);
        } else {
            redirect('customer/my-orders');
        }
    }



    public function addReturnRequest()
    {
        $LoginID = $_SESSION['LoginID'];
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if (isset($_POST['order_id']) && isset($_POST['selected_item'])) {
            $order_id=$_POST['order_id'];
            $increment_id=$_POST['increment_id'];
            $flag='return';
            $selected_item=$_POST['selected_item'];

            $postArr=array('order_id'=>$order_id,'increment_id'=>$increment_id,'selected_item'=>$selected_item,'flag'=>$flag,'customer_id'=>$LoginID);
            $response = OrdersRepository::return_order_request($shopcode, $shop_id, $postArr);

            if (!empty($response) && isset($response) && $response->statusCode=='200') {
				$redirect_to=base_url().'customer/my-orders/return-detail/'.$response->return_order_id;
                echo json_encode(array('flag'=>1, 'msg'=> $response->message,'redirect_to'=>$redirect_to));
                exit;
            }

			echo json_encode(array('flag'=>0, 'msg'=> $response->message));
			exit;
		}

		echo json_encode(array('flag'=>0, 'msg'=>"Unable to post request."));
		exit;
	}

    public function show_tracking_details()
    {

        if (isset($_POST['order_id'])) {
            $order_id=$_POST['order_id'];
            $postArr=array('order_id'=>$order_id);
            $response = OrdersRepository::tracking_details_request($postArr);
            // print_r($response);die();

            if (!empty($response) && isset($response) && $response->statusCode=='200') {
                $data['tracking_data'] = $response->tracking_data;
                //  print_r($response);die();

                $this->template->load('myprofile/order_tracking_detail_ajax', $data);
            } else {
                $data['tracking_data'] = $response->tracking_data;
                $this->template->load('myprofile/order_tracking_detail_ajax', $data);
            }

        } else {
            echo json_encode(array('flag'=>0, 'msg'=>"Unable to post request."));
            exit;
        }

    }



    public function confirmReturn()
    {
        $LoginID = $_SESSION['LoginID'];
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if (isset($_POST['order_id']) && isset($_POST['reason_for_return']) && (isset($_POST['item_qty']) &&  count($_POST['item_qty'])>0)) {
            $postArr=array('customer_id'=>$LoginID);

            foreach ($_POST as $postkey=>$post_field) {
                $postArr[$postkey]=$post_field;
            }

            $response = OrdersRepository::return_order_confirm($shopcode, $shop_id, $postArr);
            if (!empty($response)) {
                if ($response->statusCode=='200') {
					$redirect_to=base_url().'customer/my-orders/return-detail/'.$response->return_order_id;
                    echo json_encode(array('flag'=>1, 'msg'=> $response->message,'redirect_to'=>$redirect_to));
                    exit;
                }

				echo json_encode(array('flag'=>0, 'msg'=> $response->message));
				exit;
			}

			echo json_encode(array('flag'=>0, 'msg'=>'Unable to post request.'));
			exit;
		}

		echo json_encode(array('flag'=>0, 'msg'=>"Unable to post request."));
		exit;
	}

    public function cancelOrder()
    {

		if (!isset($_POST)) {
			echo json_encode(array('flag' => 0, 'msg' => "Please two enter all mandatory / compulsory fields."));
			exit;
		}

		if (empty($_POST['cancel_reason']) && empty($_POST['order_id'])) {
			echo json_encode(array('flag' => 0, 'msg' => "Please  one enter all mandatory / compulsory fields."));
			exit;
		}
		$reason_for_cancel = isset($_POST['cancel_reason']) ? $_POST['cancel_reason'] : '';
		$order_id = $_POST['order_id'];

        $webshopname = 'India Mags';


        $shop_logo = SITE_LOGO;
		$data['webshop_details'] = CommonRepository::get_webshop_details();
		if (!empty($data['webshop_details']) && isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
            $webshopname = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_name);

		}


		$site_logo = '<a href="' . base_url() . '" style="color:#1E7EC8;">
					<img alt="' . $webshopname . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
				</a>';
		$lang_code = '';
		if (!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language') == 0) {
			$lang_code = $this->session->userdata('lcode');
		}
        if(!empty($this->session->userdata('CURRENCY_CODE'))){
            $CURRENCY_CODE=$this->session->userdata('CURRENCY_CODE');
        }
        else{
            $CURRENCY_CODE = 'IN';
        }
		$postArr = array('order_id' => $order_id, 'reason_for_cancel' => $reason_for_cancel, 'site_logo' => $site_logo, 'currency_code' => $CURRENCY_CODE, 'lang_code' => $lang_code);
		
        $response = OrdersRepository::cancel_order_request($postArr);
		if (!empty($response) && isset($response) && $response->is_success == 'true') {
			$redirect_to = base_url() . 'customer/my-orders/';
			echo json_encode(array('flag' => 1, 'msg' => $response->message, 'redirect_to' => $redirect_to));
			exit;
		}

		echo json_encode(array('flag' => 0, 'msg' => $response->message));
		exit;
	}
}
