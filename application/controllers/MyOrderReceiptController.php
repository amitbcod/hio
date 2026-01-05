<?php
defined('BASEPATH') or exit('No direct script access allowed');
class MyOrderReceiptController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function printReceiptOrder(){

        $data['PageTitle']= 'My Profile - Receipt Order';
        $order_id = urldecode($this->uri->segment(3));
        $order_id = base64_decode($order_id);

        $response= OrdersRepository::my_order_detail($order_id);
        if (!empty($response) && $response->is_success=='true') {
            $OrderData = $response->OrderData;
            $data['OrderData']=$OrderData;

            if($OrderData->customer_id > 0 && $this->session->userdata('LoginID') == ''){
                redirect(BASE_URL.'customer/login');
            }

          $postArr = array('order_id'=>$order_id);
          $customerData= OrdersRepository::get_customer_address_by_order_id($postArr);
            if (!empty($customerData) && $customerData->is_success=='true') {
                $data['billing_address']=$customerData->tableData[1];
                $data['shipping_address']=$customerData->tableData[2];
                $data['payment_method']=$customerData->tableData[3];
            }
             $data['webshop_details'] = CommonRepository::get_webshop_details();
             $this->template->load('myprofile/print_receipt_order', $data);
        }else{
            redirect(BASE_URL);
        }

        
       
    }
}
