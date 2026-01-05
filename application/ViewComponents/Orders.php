<?php

/**
 * @property CI_Controller $ci
 */
class Orders
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function list($login_type)
    {
        $data['login_type'] = $login_type;
        $secret_return = $this->ci->input->get('secret-return') ?? '';
        if ($login_type == 'general') {
            $LoginID = $this->ci->session->userdata('LoginID');
            $page = ($this->ci->uri->segment(3) == '' || $this->ci->uri->segment(3) == null) ? 1 : $this->ci->uri->segment(3);
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $offset = ($offset == 1) ? 0 : $offset;

            $response = OrdersRepository::my_orders_listing($LoginID, $limit, $offset);
            if (!empty($response) && isset($response) && isset($response->is_success) && $response->is_success == 'true') {
                $OrderList = $response->OrderData;
                $data['Total_Order'] = $response->Total_Order;
                $data['Limit'] = $limit;
                $data['Page'] = $page;
                $config = array();
                $config["first_tag_open"] = "<li class='page-item'>";
                $config["first_tag_close"] = "</li>";
                $config["last_tag_open"] = "<li class='page-item'>";
                $config["last_tag_close"] = "</li>";
                $config["next_tag_open"] = "<li class='page-item'>";
                $config["next_tag_close"] = "</li>";
                $config["prev_tag_open"] = "<li class='page-item'>";
                $config["prev_tag_close"] = "</li>";
                $config["cur_tag_open"] = "<li class='page-item active'><a>";
                $config["cur_tag_close"] = "</a></li>";
                $config["num_tag_open"] = "<li class='page-item'>";
                $config["num_tag_close"] = "</li>";
                $config['use_page_numbers'] = true;
                $config["base_url"] = base_url() . "customer/my-orders";
                $config["total_rows"] = $response->Total_Order;
                $config["per_page"] = $limit;
                $this->ci->pagination->initialize($config);
                $data["links"] = $this->ci->pagination->create_links();
                if (isset($OrderList) && count($OrderList) > 0) {
                    foreach ($OrderList as $order) {
                        $order_id = $order->order_id;
                        if (isset($secret_return) && !empty($secret_return) && $secret_return == 1 && $order->status == 6) {
                            $order->flag = 'able_to_return';
                        }
                        $order->ReturnOrders = $order->return_orders;
                    }
                }
                $data['OrderList'] = $OrderList;
            }
            $this->ci->template->load('components/order/customer_general_orders', $data);
        } else {
            $order_id = urldecode($this->ci->uri->segment(3));
            $order_id = base64_decode($order_id);
            $postArr = array();
            $response = OrdersRepository::my_order_detail($order_id);
            if (!empty($response) && $response->is_success == 'true') {
                $OrderData = $response->OrderData;
                $response->OrderData;
                $data['OrderData'] = $OrderData;
                $order_id = $data['OrderData']->order_id;
                if (isset($secret_return) && !empty($secret_return) && $secret_return == 1 && $OrderData->status == 6) {
                    $data['OrderData']->flag = 'able_to_return';
                } else {
                    $data['OrderData']->flag = $this->checkOperationFlag($order_id);
                }
                $data['OrderData']->ReturnOrders = $this->returnOrderListing($order_id);
            }
            $this->ci->template->load('components/order/customer_guest_orders', $data);
        }
    }

    public function checkOperationFlag($order_id)
    {
        $flag = '';
        $postArr = array('order_id' => $order_id, 'operation_name' => 'can_able_to_cancel');
        $response = OrdersRepository::order_operation_checks(SHOPCODE, SHOP_ID, $postArr);
        if (!empty($response)) {
            if ($response->is_success == 'true') {
                $flag = $response->flag;
                if ($response->flag == 'able_to_cancel') {
                    $flag = $response->flag;
                } else {
                    /*----------------------check if order is available for return-------------------------*/
                    $product_return_duration_count = 0;
                    $product_return_duration = 'product_return_duration';
                    $CVApiResponse = GlobalRepository::get_custom_variable(SHOPCODE, SHOP_ID, $product_return_duration);
                    if (!empty($CVApiResponse) && $CVApiResponse->statusCode == '200') {
                        $RowCV = $CVApiResponse->custom_variable;
                        $product_return_duration_count = $RowCV->value;
                    } else {
                        $product_return_duration_count = 0;
                    }

                    if ($product_return_duration_count > 0) {
                        $postArr = array('order_id' => $order_id, 'operation_name' => 'can_able_to_return', 'product_return_duration' => $product_return_duration_count);
                        $response2 = OrdersRepository::order_operation_checks(SHOPCODE, SHOP_ID, $postArr);
                        if (!empty($response2) && isset($response2) && $response2->is_success == 'true') {
                            $flag = $response2->flag;
                        }
                    }
                }
            }
        }

        return $flag;
    }


    public function returnOrderListing($order_id)
    {

        $ret_response = OrdersRepository::my_return_orders_listing(SHOPCODE, SHOP_ID, $order_id);
        if (!empty($ret_response) && $ret_response->is_success == 'true') {
            $ReturnOrders = $ret_response->ReturnOrderCollection;
        } else {
            $ReturnOrders = array();
        }

        return $ReturnOrders;
    }
}
