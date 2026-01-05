<?php
defined('BASEPATH') or exit('No direct script access allowed');

class B2BOrdersController extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		if ($this->session->userdata('LoginID') == '') {
			redirect(base_url());
		}
		if (!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/orders', $this->session->userdata('userPermission'))) {
			// redirect(base_url('dashboard'));
		}

		$this->load->model('UserModel');
		$this->load->model('B2BModel');
		$this->load->model('B2BOrdersModel');
		$this->load->model('CommonModel'); // added this model invoicing generate
		$this->load->model('WebshopOrdersModel');
	}

	/*public function index()
	{

		$current_tab = $this->uri->segment(2);
		$data['PageTitle'] = 'B2B - Orders';
		$data['side_menu'] = 'b2b';
		$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
		//echo $this->B2BOrdersModel->generate_new_transaction_id().'=====';
		$this->load->view('b2b/order/orderlist', $data);
		// $this->load->view('webshop/order/orderlist', $data);
	}*/

	public function b2c_orders_list()
	{
		$current_tab = $this->uri->segment(2);
		$this->load->model('B2BOrdersModel');
		//$this->load->model('SalesOrderModel'); // Model for sales_order
		$data['PageTitle'] = 'B2C Orders';
		$data['current_tab'] = 'deliveries';

		// Fetch B2C orders (parent orders)
		$b2c_orders = $this->B2BOrdersModel->getAllB2COrders();

		$orders_data = [];
		foreach ($b2c_orders as $b2c) {
			// Get all related B2B sub-orders
			$sub_orders = $this->B2BOrdersModel->getSubOrdersByWebshopOrderId($b2c->order_id);

			$orders_data[] = [
				'parent' => $b2c,
				'sub_orders' => $sub_orders
			];
		}

		$data['orders_data'] = $orders_data;

		$this->load->view('b2b/order/b2c_orders_list', $data);
	}


	public function b2c_complete_orders_list()
	{
		$this->load->model('B2BOrdersModel');
		//$this->load->model('SalesOrderModel'); // Model for sales_order
		$data['PageTitle'] = 'B2C Orders';
		$data['current_tab'] = 'complete-orders';

		// Fetch B2C orders (parent orders)
		$b2c_orders = $this->B2BOrdersModel->getAllcompleteB2COrders();

		$orders_data = [];
		foreach ($b2c_orders as $b2c) {
			// Get all related B2B sub-orders
			$sub_orders = $this->B2BOrdersModel->getSubOrdersByWebshopOrderId($b2c->order_id);

			$orders_data[] = [
				'parent' => $b2c,
				'sub_orders' => $sub_orders
			];
		}

		$data['orders_data'] = $orders_data;

		$this->load->view('b2b/order/b2c_orders_list', $data);
	}


	public function delivery_orders_list()
{
    $this->load->model('B2BOrdersModel');
    $this->load->model('ShopProductModel');
    $this->load->library('pagination');

    $data['PageTitle'] = 'B2B - Delivery Orders';
    $data['side_menu'] = 'b2b';
    $data['current_tab'] = 'delivery-orders';

    // Filters
    $price          = $this->input->get('price');
    $order_status   = $this->input->get('order_status');
    $shipment_type  = $this->input->get('shipment_type');
    $fromDate       = $this->input->get('from_date');
    $toDate         = $this->input->get('to_date');
    $payment_method = $this->input->get('payment_method');

    // Pagination setup
    $config['base_url']   = base_url('webshop/delivery-orders');
    $config['total_rows'] = $this->B2BOrdersModel->count_all_delivery_orders(
        $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method
    );
    $config['per_page']   = 10;
    $config['uri_segment'] = 3;
    $config['reuse_query_string'] = true;
    $this->pagination->initialize($config);

    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

    $orders = $this->B2BOrdersModel->get_datatables_delivery_orders(
        $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method
    );

    $orderRows = [];
    foreach ($orders as $readData) {
        // Build table row
        $order_url = base_url('webshop/b2b/order/detail/' . $readData->order_id);
        $print_url = base_url('b2b/delivery-order/print/' . $readData->order_id);

        $order_status_label  = $this->CommonModel->getOrderStatusLabel($readData->status);
        $shipment_type_label = $this->CommonModel->getOrderShipmentLabel($readData->shipment_type);

        $customerName = ($readData->parent_id > 0)
            ? $this->B2BOrdersModel->getOrderCustomerNameByOrderId($readData->order_id)
            : $readData->customer_name;

        $publisher_name = $this->CommonModel->getWebShopNameByShopId($readData->publisher_id);

        $purchaseOnDate = ($readData->main_parent_id > 0 || $readData->parent_id > 0)
            ? date(SIS_DATE_FM_WT, $this->CommonModel->getSingleShopDataByID(
                    'b2b_orders',
                    ['order_id' => $readData->main_parent_id],
                    'created_at'
                )->created_at)
            : date(SIS_DATE_FM_WT, $readData->created_at);

        $_sales_order = $this->ShopProductModel->getSingleDataByID(
            'sales_order',
            ['order_id' => $readData->webshop_order_id],
            ''
        );

        // Fetch delivery attempts
        $deliveryAttempts = $this->db
            ->where('order_id', $readData->order_id)
            ->order_by('delivery_attempt_no', 'ASC')
            ->get('b2b_orders_delivery_details')
            ->result();

			echo "";

        // Delivery status
        $delivery_status_label = 'Not Assigned';
        if (!empty($deliveryAttempts)) {
            $lastAttempt = end($deliveryAttempts);
            $delivery_status_label = $this->CommonModel->getDeliveryStatusLabel($lastAttempt->delivery_status);
        }

        $row = [];
        $row[] = '<a class="link-purple" href="' . $order_url . '">' . $readData->increment_id . '</a>';
        $row[] = $_sales_order->increment_id ?? '';
        $row[] = $purchaseOnDate;
        $row[] = ucwords($customerName);
        $row[] = $publisher_name;
        $row[] = $order_status_label;
        $row[] = $delivery_status_label;

        // Details button
        $row[] = '<button class="btn btn-info btn-sm" onclick="openDeliveryPopup(' . $readData->order_id . ')">View</button>';

        // Action buttons
        if ($readData->status == 9 || $readData->status == 8) {
            $row[] = '-';
        } elseif ($readData->status == 13) {
            $row[] = '<button class="btn btn-primary color2 btn-sm" onclick="markAsCollected(' . $readData->order_id . ')">Mark as Collected</button>';
        } elseif (empty($deliveryAttempts)) {
            $row[] = '<button class="btn btn-primary btn-sm color1" onclick="AssignNewDeliveryPopup(' . $readData->order_id . ',1)">Assign Delivery</button>';
        } else {
            $lastAttempt = end($deliveryAttempts);
            if ($lastAttempt->delivery_status == 8) {
                $row[] = '<span class="green">Delivered</span>';
            } elseif (in_array($lastAttempt->delivery_status, [2, 4, 6])) {
                if ($lastAttempt->delivery_attempt_no < 3) {
                    $nextAttempt = $lastAttempt->delivery_attempt_no + 1;
                    $row[] = '<button class="btn btn-warning color1 btn-sm" onclick="AssignNewDeliveryPopup(' . $readData->order_id . ',' . $nextAttempt . ')">Re-Attempt Delivery</button>';
                } else {
                    $row[] = '<span class="red">-</span>';
                }
            } else {
                $row[] = '<button class="btn btn-success btn-sm color2" onclick="markAsDelivered(' . $readData->order_id . ')">Mark as Delivered</button>
                          <button class="btn btn-secondary btn-sm color3" onclick="MarkAsFailedPopup(' . $readData->order_id . ',' . $lastAttempt->delivery_attempt_no . ')">Mark as Failed</button>';
            }
        }

        $orderRows[] = [
            'row_data' => $row,
            'delivery_attempts' => $deliveryAttempts,
            'order_id' => $readData->order_id
        ];
    }

    $data['orders'] = $orderRows;
    $data['pagination'] = $this->pagination->create_links();

    // Load a dedicated view for delivery orders
    $this->load->view('b2b/order/delivery_orders_list', $data);
}

	public function index()
{
    $current_tab = $this->uri->segment(2);
    $data['PageTitle'] = 'B2B - Orders';
    $data['side_menu'] = 'b2b';
    $data['current_tab'] = ($current_tab != '') ? $current_tab : '';

    $this->load->model('B2BOrdersModel');
    $this->load->model('ShopProductModel');
    $this->load->library('pagination');

    // Fetch filters if any
    $price          = $this->input->get('price');
    $order_status   = $this->input->get('order_status');
    $shipment_type  = $this->input->get('shipment_type');
    $fromDate       = $this->input->get('from_date');
    $toDate         = $this->input->get('to_date');
    $payment_method = $this->input->get('payment_method');

    if ($current_tab == 'delivery-orders') {
        // Pagination config
        $config['base_url']   = base_url('webshop/delivery-orders');
        $config['total_rows'] = $this->B2BOrdersModel->count_all_delivery_orders(
            $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method
        );
        $config['per_page']   = 10; // show 10 orders per page
        $config['uri_segment'] = 3;
        $config['reuse_query_string'] = true;
        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        // Fetch **only delivery orders** using new function
        $orders = $this->B2BOrdersModel->get_datatables_delivery_orders(
            $price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method
        );

        $orderRows = [];
        foreach ($orders as $readData) {
            $order_url = base_url('webshop/b2b/order/detail/' . $readData->order_id);
            $print_url = base_url('b2b/delivery-order/print/' . $readData->order_id);

            $order_status_label  = $this->CommonModel->getOrderStatusLabel($readData->status);
            $shipment_type_label = $this->CommonModel->getOrderShipmentLabel($readData->shipment_type);

            $customerName = ($readData->parent_id > 0)
                ? $this->B2BOrdersModel->getOrderCustomerNameByOrderId($readData->order_id)
                : $readData->customer_name;

            $publisher_name = $this->CommonModel->getWebShopNameByShopId($readData->publisher_id);

            $purchaseOnDate = ($readData->main_parent_id > 0 || $readData->parent_id > 0)
                ? date(SIS_DATE_FM_WT, $this->CommonModel->getSingleShopDataByID(
                        'b2b_orders',
                        ['order_id' => $readData->main_parent_id],
                        'created_at'
                    )->created_at)
                : date(SIS_DATE_FM_WT, $readData->created_at);

            $_sales_order = $this->ShopProductModel->getSingleDataByID(
                'sales_order',
                ['order_id' => $readData->webshop_order_id],
                ''
            );

            // Fetch delivery attempts
            $deliveryAttempts = $this->db
                ->where('order_id', $readData->order_id)
                ->order_by('delivery_attempt_no', 'ASC')
                ->get('b2b_orders_delivery_details')
                ->result();

            // Delivery status
            $delivery_status_label = 'Not Assigned';
            if (!empty($deliveryAttempts)) {
                $lastAttempt = end($deliveryAttempts);
                $delivery_status_label = $this->CommonModel->getDeliveryStatusLabel($lastAttempt->delivery_status);
            }

            // Build table row
            $row = [];
            $row[] = '<a class="link-purple" href="' . $order_url . '">' . $readData->increment_id . '</a>';
            $row[] = $_sales_order->increment_id ?? '';
            $row[] = $purchaseOnDate;
            $row[] = ucwords($customerName);
            $row[] = $publisher_name;
            $row[] = $order_status_label;
            $row[] = $delivery_status_label;
            $row[] = '<button class="btn btn-info btn-sm" onclick="openDeliveryPopup(' . $readData->order_id . ')">View</button>';

            // Delivery actions
            if ($readData->status == 9 || $readData->status == 8) {
                $row[] = '-';
            } elseif ($readData->status == 13) {
                $row[] = '<button class="btn btn-primary color2 btn-sm" onclick="markAsCollected(' . $readData->order_id . ')">Mark as Collected</button>';
            } elseif (empty($deliveryAttempts)) {
                $row[] = '<button class="btn btn-primary btn-sm color1" onclick="AssignNewDeliveryPopup(' . $readData->order_id . ',1)">Assign Delivery</button>';
            } else {
                $lastAttempt = end($deliveryAttempts);
                if ($lastAttempt->delivery_status == 8) {
                    $row[] = '<span class="green">Delivered</span>';
                } elseif (in_array($lastAttempt->delivery_status, [2, 4, 6])) {
                    if ($lastAttempt->delivery_attempt_no < 3) {
                        $nextAttempt = $lastAttempt->delivery_attempt_no + 1;
                        $row[] = '<button class="btn btn-warning color1 btn-sm" onclick="AssignNewDeliveryPopup(' . $readData->order_id . ',' . $nextAttempt . ')">Re-Attempt Delivery</button>';
                    } else {
                        $row[] = '<span class="red">-</span>';
                    }
                } else {
                    $row[] = '<button class="btn btn-success btn-sm color2" onclick="markAsDelivered(' . $readData->order_id . ')">Mark as Delivered</button>
                              <button class="btn btn-secondary btn-sm color3" onclick="MarkAsFailedPopup(' . $readData->order_id . ',' . $lastAttempt->delivery_attempt_no . ')">Mark as Failed</button>';
                }
            }

            $orderRows[] = [
                'row_data' => $row,
                'delivery_attempts' => $deliveryAttempts,
                'order_id' => $readData->order_id
            ];
        }

        $data['orders'] = $orderRows;
        $data['pagination'] = $this->pagination->create_links();
    }

    // Load view (other tabs will continue using JS as usual)
    $this->load->view('b2b/order/orderlist', $data);
}
function loadordersajax()
{
    $price          = $this->input->post('price');
    $order_status   = $this->input->post('order_status');
    $shipment_type  = $this->input->post('shipment_type');
    $fromDate       = $this->input->post('from_date');
    $toDate         = $this->input->post('to_date');
    $current_tab    = $this->input->post('current_tab');
    $payment_method = $this->input->post('payment_method');
    $shop_id        = $this->session->userdata('ShopID');

    if ($current_tab == 'pickup-orders') {
        $shipment_type = 2;
    }

    $ProductData = $this->B2BOrdersModel->get_datatables_orders(
        $price,
        $order_status,
        $shipment_type,
        $fromDate,
        $toDate,
        $payment_method
    );

    $this->load->model('ShopProductModel');
    $data = array();
    $no = $_POST['start'];

    foreach ($ProductData as $readData) {
        $no++;
        $row = array();

        // Set URLs
        switch ($current_tab) {
            case 'b2b-orders':
                $order_url = base_url() . 'webshop/b2b/order/detail/' . $readData->order_id;
                $print_url = base_url() . 'webshop/b2b/order/print/' . $readData->order_id;
                break;
            case 'split-orders':
                $order_url = base_url() . 'b2b/split-order/detail/' . $readData->order_id;
                $print_url = base_url() . 'b2b/order/print/' . $readData->order_id;
                break;
            case 'pickup-orders':
                $order_url = base_url() . 'webshop/b2b/pickup-order/detail/' . $readData->order_id;
                $print_url = base_url() . 'b2b/shipped-order/print/' . $readData->order_id;
                break;
            case 'delivery-orders':
                $order_url = base_url() . 'webshop/b2b/delivery-order/detail/' . $readData->order_id;
                $print_url = base_url() . 'b2b/delivery-order/print/' . $readData->order_id;
                break;
            case 'shipped-orders':
                $order_url = base_url() . 'b2b/shipped-order/detail/' . $readData->order_id;
                $print_url = base_url() . 'b2b/shipped-order/print/' . $readData->order_id;
                break;
            case 'cancel-orders':
                $order_url = base_url() . 'webshop/b2b/order/detail/' . $readData->order_id;
                $print_url = base_url() . 'webshop/b2b/order/print/' . $readData->order_id;
                break;
            default:
                $order_url = base_url();
                $print_url = base_url();
        }

        $order_status_label  = $this->CommonModel->getOrderStatusLabel($readData->status);
        $shipment_type_label = $this->CommonModel->getOrderShipmentLabel($readData->shipment_type);

        // Customer name
        $customerName = ($readData->parent_id > 0) 
                        ? $this->B2BOrdersModel->getOrderCustomerNameByOrderId($readData->order_id) 
                        : $readData->customer_name;

        $publisher_name = $this->CommonModel->getWebShopNameByShopId($readData->publisher_id);

        $purchaseOnDate = ($readData->main_parent_id > 0 || $readData->parent_id > 0) 
                          ? date(SIS_DATE_FM_WT, $this->CommonModel->getSingleShopDataByID(
                                'b2b_orders', 
                                ['order_id' => $readData->main_parent_id, 'order_id' => $readData->main_parent_id], 
                                'created_at'
                            )->created_at) 
                          : date(SIS_DATE_FM_WT, $readData->created_at);

        $_sales_order = $this->ShopProductModel->getSingleDataByID('sales_order', ['order_id' => $readData->webshop_order_id], '');
        
        // Build row
        $row[] = $readData->increment_id;
        $row[] = $_sales_order->increment_id ?? '';
        $row[] = $purchaseOnDate;
        $row[] = ucwords($customerName);
        $row[] = $publisher_name;
        $row[] = $order_status_label;

        // Pickup tab extra
        if ($current_tab == 'pickup-orders') {
            $pickupData = $this->CommonModel->getPickupDataByOrderId($readData->order_id);
            $pickup_status_label = 'Pending';
            if ($pickupData) {
                switch ($pickupData->pickup_status) {
                    case 0: $pickup_status_label = 'Pending'; break;
                    case 1: $pickup_status_label = 'Not Assigned'; break;
                    case 2: $pickup_status_label = 'Pick Up Assigned'; break;
                    case 3: $pickup_status_label = 'Picked Up'; break;
                    case 4: $pickup_status_label = 'Delivered To Warehouse'; break;
                }
            }
            $row[] = $pickup_status_label;
        }

        // Delivery tab extra
        if ($current_tab == 'delivery-orders') {
            $deliveryData = $this->CommonModel->getDeliveryDataByOrderId($readData->order_id);
            $delivery_status_label = $deliveryData 
                                     ? $this->CommonModel->getDeliveryStatusLabel($deliveryData->delivery_status) 
                                     : 'Not Assigned';
            $row[] = $delivery_status_label;
        }

      
		if ($current_tab == 'delivery-orders') {
			$row[] = '<button class="btn btn-info btn-sm" onclick="openDeliveryPopup(' . $readData->order_id . ')">View</button>';
		} else {
			$row[] = '<a class="link-purple" href="' . $order_url . '">View</a>';
		}

        // Pickup actions
        if ($current_tab == 'pickup-orders') {
            if ($pickupData && $pickupData->pickup_status == 4) {
                $row[] = '<span class="green">Received</span>';
            } elseif (!$pickupData || $pickupData->pickup_status < 2) {
                $row[] = '<button class="btn btn-primary btn-sm color1" onclick="AssignNewPickupPopup(' . $readData->order_id . ')">Create Pickup</button>';
            } else {
                $row[] = '<button class="btn btn-success btn-sm color2" onclick="MarkPickupReceived(' . $readData->order_id . ')">Mark as Received</button>';
            }
        }

        // Delivery actions
       // Delivery actions
if ($current_tab == 'delivery-orders') {

    if ($readData->status == 9 || $readData->status == 8) {
        // Collected orders: no actions
        $row[] = '-';
    } 
    elseif ($readData->status == 13) {
        // Only "Mark as Collected"
        $row[] = '<button class="btn btn-primary color2 btn-sm" onclick="markAsCollected(' . $readData->order_id . ')">Mark as Collected</button>';
    } 
    elseif (!$deliveryData) {
        // First time
        $row[] = '<button class="btn btn-primary btn-sm color1" onclick="AssignNewDeliveryPopup(' . $readData->order_id . ',1)">Assign Delivery</button>';
    } 
    elseif ($deliveryData->delivery_status == 8) {
        $row[] = '<span class="green">Delivered</span>';
    } 
    elseif (in_array($deliveryData->delivery_status, [2, 4, 6])) {
        if ($deliveryData->delivery_attempt_no < 3) {
            $nextAttempt = $deliveryData->delivery_attempt_no + 1;
            $row[] = '<button class="btn btn-warning color1 btn-sm" onclick="AssignNewDeliveryPopup(' . $readData->order_id . ',' . $nextAttempt . ')">Re-Attempt Delivery</button>';
        } else {
            $row[] = '<span class="red">-</span>';
        }
    } 
    else {
        $row[] = '<button class="btn btn-success btn-sm color2" onclick="markAsDelivered(' . $readData->order_id . ')">Mark as Delivered</button>
                  <button class="btn btn-secondary btn-sm color3" onclick="MarkAsFailedPopup(' . $readData->order_id . ',' . $deliveryData->delivery_attempt_no . ')">Mark as Failed</button>';
    }
}


        $data[] = $row;
    }

    $output = [
        "draw"            => $_POST['draw'],
        "recordsTotal"    => $this->B2BOrdersModel->count_all_orders($price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method),
        "recordsFiltered" => $this->B2BOrdersModel->count_filtered_orders($price, $order_status, $shipment_type, $fromDate, $toDate, $payment_method),
        "data"            => $data,
    ];

    echo json_encode($output);
    exit;
}

	function GetOrderByIncrementId()
	{

		$increment_id = $_POST['increment_id'];
		$orderdata = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('increment_id' => $increment_id), 'order_id,increment_id,parent_id,main_parent_id,status');
		if (isset($orderdata)) {
			if ($orderdata->main_parent_id != 0) {
				$redirect_url = base_url() . 'b2b/split-order/detail/' . $orderdata->order_id;
			} else if ($orderdata->status == 4 || $orderdata->status == 5 || $orderdata->status == 6) {
				$redirect_url = base_url() . 'b2b/shipped-order/detail/' . $orderdata->order_id;
			} else {
				$redirect_url = base_url() . 'b2b/order/detail/' . $orderdata->order_id;
			}
			$arrResponse  = array('flag' => 1, 'redirect' => $redirect_url);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('flag' => 0, 'message' => 'No order found.');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function detail()
	{
		$current_tab = $this->uri->segment(3);
		$order_id = $this->uri->segment(5);
		//echo $current_tab.'----'.$order_id;exit;
		if (isset($order_id) && $order_id > 0) {
			$data['PageTitle'] = 'B2B - Orders';
			$data['side_menu'] = 'b2b';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';

			$shop_id		=	$this->session->userdata('ShopID');

			//echo $this->B2BOrdersModel->generate_new_transaction_id().'=====';
			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			// echo $this->db->last_query();
			// die();

			/*echo "<pre>";
			print_r($OrderData);
			exit;*/
			if (empty($OrderData)) {
				redirect('webshop/b2b-orders');
			}
			$data['OrderItems'] = $OrderItems = $this->B2BOrdersModel->getOrderItems($order_id);
			//$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);
			$data['currency_code'] = '';




			$QtyScanItem = $this->B2BOrdersModel->getQtyFullyScannedOrderItems($order_id);
			$data['scanned_qty'] = count($QtyScanItem);
			$PendingScanQty = $this->B2BOrdersModel->getQtyPendingScannedOrderItems($order_id);
			$data['pending_scanned_qty'] = count($PendingScanQty);


			if ($OrderData->shipment_type == 2) {
				if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {
					$webshop_order_id = $OrderData->webshop_order_id;
					$webshop_shop_id = $OrderData->publisher_id;
					$B2b_increment_id = $OrderData->increment_id;
					$patent_order_id = $OrderData->order_id;

					// $FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
					// $webshop_fbc_user_id = $FbcUser->fbc_user_id;

					$args['shop_id']	=	$webshop_shop_id;
					// $args['fbc_user_id']	=	$webshop_fbc_user_id;

					$this->load->model('ShopProductModel');
					// $this->ShopProductModel->init($args);

					$data['ShippingAddress'] = $ShippingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), '');
					$data['BillingAddress'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 1), '');
					$data['PublisherPayment'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('publisher_payment', array('order_id' => $patent_order_id, 'B2b_order_id' => $B2b_increment_id), '');
					$data['OrderData'] = $OrderItemData = $this->ShopProductModel->getSingleDataByID('b2b_orders', array('webshop_order_id' => $webshop_order_id, 'increment_id' => $B2b_increment_id), '');

					$data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getSingleDataByID('publisher', array('id' => $OrderItemData->publisher_id), '');
					$data['FormattedAddress'] = $this->ShopProductModel->getFormattedAddress($ShippingAddress);
				}
			}

			// echo $current_tab;
			// die();
			if ($current_tab == 'order') {
				/*if ($OrderData->status == 1) {
					redirect(base_url() . 'b2b/order/create-shipment/' . $OrderData->order_id);
				}*/
				// print_R($data);
				// die();
				$this->load->view('b2b/order/main-order-detail', $data);
			} else if ($current_tab == 'pickup-order') {
				
				$this->load->view('b2b/order/pickup-order-detail', $data);
			} else if ($current_tab == 'split-order') {
				if ($OrderData->status == 1) {
					redirect(base_url() . 'b2b/order/create-shipment/' . $OrderData->order_id);
				}

				$data['ParentOrder'] = $ParentOrder = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $OrderData->main_parent_id), '');
				$data['SplitOrderIds'] = $this->B2BOrdersModel->getSplitChildOrderIds($OrderData->main_parent_id);

				$this->load->view('b2b/order/split-order-detail', $data);
			} else if ($current_tab == 'shipped-order') {
				$data['SplitOrderIds'] = $this->B2BOrdersModel->getSplitChildOrderIds($OrderData->order_id);
				$data['ShippedItem'] = $ShippedItem = $this->B2BOrdersModel->getShippedOrderItems($OrderData->order_id, $OrderData->is_split);
				$this->load->view('b2b/order/shipped-order-detail', $data);
			} else {
				redirect('/b2b/orders');
			}
		} else {
			redirect('/b2b/orders');
		}
	}

	function scanbarcodemanually()
	{
		if (isset($_POST['order_id']) && isset($_POST['barcode_item'])) {

			$order_id = $_POST['order_id'];
			$barcode = $_POST['barcode_item'];
			$current_tab = $_POST['current_tab'];
			$ItemExist = $this->B2BOrdersModel->checkOrderItemsExist($order_id, $barcode);
			if (isset($ItemExist) && $ItemExist->item_id != '') {

				$item_id = $ItemExist->item_id;
				$qty_ordered = $ItemExist->qty_ordered;
				$old_qty_scanned = $ItemExist->qty_scanned;

				$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,is_split');

				if ($current_tab == 'split-order') {
					$main_oi_qty = $this->B2BOrdersModel->getMainOrderItemQty($OrderData->main_parent_id, $ItemExist->product_id);
					$main_qty_ordered = $main_oi_qty;
				} else {

					$main_qty_ordered = $ItemExist->qty_ordered;
				}


				if (($current_tab == 'order') && ($old_qty_scanned == $qty_ordered)) {
					$arrResponse  = array('status' => 400, 'message' => 'Barcode <b>' . $barcode . '</b> already scanned all quantity.');
					echo json_encode($arrResponse);
					exit;
				}
				/*
				else if(($current_tab=='split-order') && ($old_qty_scanned==$qty_ordered)){
					$arrResponse  = array('status' =>400 ,'message'=>'Barcode already scanned all quantity.');
					echo json_encode($arrResponse);exit;

				}
				*/ else {

					if ($old_qty_scanned < $main_qty_ordered) {
						$this->B2BOrdersModel->incrementOrderItemQtyScanned($item_id);  //increament qty_scanned
					}

					$item_class = $this->B2BOrdersModel->getOrderItemRowClass($item_id);


					$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,is_split');
					if ($OrderData->is_split == 0) {
						$QtyScanItem = $this->B2BOrdersModel->getQtyFullyScannedOrderItems($order_id);
						$AllItems = $this->B2BOrdersModel->getOrderItems($order_id);
						if (count($QtyScanItem) == count($AllItems)) {
							$odr_update = array('system_generated_split_order' => 0, 'updated_at' => time());
							$where_arr = array('order_id' => $order_id);
							$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $odr_update);
						}
					}


					$arrResponse  = array('status' => 200, 'message' => 'Barcode scanned successfully.', 'item_id' => $item_id, 'item_class' => $item_class);
					echo json_encode($arrResponse);
					exit;
				}
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Barcode <b>' . $barcode . '</b> does not belongs to this order!');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function refreshOrderItems()
	{
		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$current_tab = $_POST['current_tab'];
			$data['current_tab'] = $current_tab;
			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,status');
			$shop_id		=	$this->session->userdata('ShopID');
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);
			$data['OrderItems'] = $OrderItems = $this->B2BOrdersModel->getOrderItems($order_id);
			$QtyScanItem = $this->B2BOrdersModel->getQtyFullyScannedOrderItems($order_id);
			$data['scanned_qty'] = count($QtyScanItem);
			$PendingScanQty = $this->B2BOrdersModel->getQtyPendingScannedOrderItems($order_id);
			$data['pending_scanned_qty'] = count($PendingScanQty);
			$View = $this->load->view('b2b/order/order-items', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function openSplitOrderPopup()
	{
		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$shop_id		=	$this->session->userdata('ShopID');

			$data['order_id'] = $order_id;
			$View = $this->load->view('b2b/order/split-confirmation-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function splitorderconfirm()
	{
		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$split_message = $_POST['split_message'];

			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$shop_upload_path = 'shop' . $shop_id;

			$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');

			$FbcUserB2BData = $this->CommonModel->getSingleDataByID('fbc_users_b2b_details', array('shop_id' => $OrderData->shop_id), '');
			if (isset($FbcUserB2BData) && $FbcUserB2BData->buyin_discount != '') {
				$discount_percent = $FbcUserB2BData->buyin_discount;
			} else {
				$discount_percent = 0;
			}


			$QtyScanItem = $this->B2BOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);

			if (count($QtyScanItem) <= 0) {
				$arrResponse  = array('status' => 400, 'message' => 'Order can not split, Please scan completely atleast one item.');
				echo json_encode($arrResponse);
				exit;
			}

			$parent_id = $OrderData->parent_id;

			$increment_id = $OrderData->increment_id;
			$temp_inc_id = str_replace('B2B-', '', $increment_id);
			$inc_id_arr = explode('-', $increment_id);
			$middle_inc_id = $inc_id_arr[1];
			$next_letter = '';
			$inc_prefix = 'B2B-';

			$order_shop_id = $webshop_shop_id = $OrderData->shop_id;


			$split_order_one = '';
			$split_flag = '';


			if ($OrderData->is_split == 0) {

				if (strpos($temp_inc_id, '-') !== false) {

					$next_letter	=	$inc_id_arr[2];
					//$next_letter 	=	get_next_letter($next_letter);
					$order_one		=	$inc_prefix . $middle_inc_id . '-' . $next_letter;
					$new_next_letter 	=	get_next_letter($next_letter);
					$order_two		=	$inc_prefix . $middle_inc_id . '-' . $new_next_letter;
					$main_parent_id	=	$OrderData->main_parent_id;
					$split_flag = 2;
				} else {

					$next_letter = 'A';
					$order_one		=	$inc_prefix . $middle_inc_id . '-' . $next_letter;
					$new_next_letter 	=	get_next_letter($next_letter);
					$order_two		=	$inc_prefix . $middle_inc_id . '-' . $new_next_letter;
					$main_parent_id	=	$OrderData->order_id;
					$split_flag = 1;
				}

				//echo $temp_inc_id.'-----'.$order_one.'-----'.$order_two.'-----'.$split_flag.'-----';exit;
				$QtyScanItem = $this->B2BOrdersModel->getQtyFullyScannedOrderItems($order_id);


				//$QtyScanItem=$this->B2BOrdersModel->getQtyFullyScannedOrderItems($order_id);
				$QtyPartialScanItem = $this->B2BOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);
				$QtyPendingItem = $this->B2BOrdersModel->getQtyPendingScannedOrderItems($order_id);

				//echo '------'.count($QtyPartialScanItem).'=============='.count($QtyPendingItem);


				if (isset($split_flag) && $split_flag == 2) {

					$this->B2BOrdersModel->removeOldOrderItems($order_id);

					if (isset($QtyPartialScanItem) && count($QtyPartialScanItem) > 0) {
						$sutotal_one = 0;
						$totalqty_one = 0;
						foreach ($QtyPartialScanItem as $item) {
							$total_price = 0;
							$total_price = $item->price * $item->qty_scanned;
							$sutotal_one = $total_price + $sutotal_one;
							$totalqty_one = $totalqty_one + $item->qty_scanned;
						}

						/*****---------calculate net pay-----------------------****/

						if ((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount > 0) && $sutotal_one > 0) {
							$RowTotalData = $this->CommonModel->calculate_percent_data($sutotal_one, $FbcUserB2BData->buyin_discount);
							$percent_amount = $RowTotalData['percent_amount'];
							$grandtotal_one = $sutotal_one - $percent_amount;
						} else {
							$percent_amount = 0;
							$grandtotal_one = $sutotal_one;
						}

						$split_update = array('grand_total' => $grandtotal_one, 'subtotal' => $sutotal_one, 'base_grand_total' => $grandtotal_one, 'base_subtotal' => $sutotal_one, 'total_qty_ordered' => $totalqty_one, 'system_generated_split_order' => 0, 'updated_at' => time());
						$where_arr = array('order_id' => $order_id);

						$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $split_update);

						$split_order_one = $order_id;
						if ($split_order_one) {

							foreach ($QtyPartialScanItem as $item) {

								$insertdataitem = array(
									'order_id' => $split_order_one,
									'product_id' => $item->product_id,
									'parent_product_id' => $item->parent_product_id,
									'product_type' => $item->product_type,
									'product_name' => $item->product_name,
									'product_code' => $item->product_code,
									'sku' => $item->sku,
									'barcode' => $item->barcode,
									'product_variants' => $item->product_variants,
									'qty_ordered' => $item->qty_ordered,
									'qty_scanned' => $item->qty_scanned,
									'price' => $item->price,
									'total_price' => $item->total_price,
									'tax_percent' => $item->tax_percent,
									'tax_amount' => $item->tax_amount,
									'discount_amount' => $item->discount_amount,
									'applied_rule_ids' => $item->applied_rule_ids,
									'created_by' => $fbc_user_id,
									'created_at' => time(),
									'ip' => $_SERVER['REMOTE_ADDR']
								);

								$this->B2BOrdersModel->insertData('b2b_order_items', $insertdataitem);
							}
						}
					}
				} else {


					if (isset($QtyPartialScanItem) && count($QtyPartialScanItem) > 0) {
						$sutotal_one = 0;
						$totalqty_one = 0;

						if (isset($QtyPartialScanItem) && count($QtyPartialScanItem) > 0) {
							foreach ($QtyPartialScanItem as $item) {

								$total_price = 0;
								$total_price = $item->price * $item->qty_scanned;
								$sutotal_one = $total_price + $sutotal_one;
								$totalqty_one = $totalqty_one + $item->qty_scanned;
							}
						}


						/*****---------calculate net pay-----------------------****/

						if ((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount > 0) && $sutotal_one > 0) {
							$RowTotalData = $this->CommonModel->calculate_percent_data($sutotal_one, $FbcUserB2BData->buyin_discount);
							$percent_amount = $RowTotalData['percent_amount'];
							$grandtotal_one = $sutotal_one - $percent_amount;
						} else {
							$percent_amount = 0;
							$grandtotal_one = $sutotal_one;
						}



						$insertdataone = array(
							'increment_id' => $order_one,
							'order_barcode' => $order_one,
							'shipment_type' => $OrderData->shipment_type,
							'webshop_order_id' => $OrderData->webshop_order_id,
							'status' => 1,
							'parent_id' => $OrderData->order_id,
							'main_parent_id' => $main_parent_id,
							'shop_id' => $OrderData->shop_id,
							'base_grand_total' => $grandtotal_one,
							'base_subtotal' => $sutotal_one,
							'grand_total' => $grandtotal_one,
							'discount_percent'	=> $discount_percent,
							'discount_amount'	=> $percent_amount,
							'subtotal' => $sutotal_one,
							'total_qty_ordered' => $totalqty_one,
							'system_generated_split_order' => 0,
							'created_by' => $fbc_user_id,
							'created_at' => time(),
							'ip' => $_SERVER['REMOTE_ADDR']
						);

						$split_order_one = $this->B2BOrdersModel->insertData('b2b_orders', $insertdataone);

						if ($split_order_one) {

							foreach ($QtyPartialScanItem as $item) {



								$insertdataitem = array(
									'order_id' => $split_order_one,
									'product_id' => $item->product_id,
									'parent_product_id' => $item->parent_product_id,
									'product_type' => $item->product_type,
									'product_name' => $item->product_name,
									'product_code' => $item->product_code,
									'sku' => $item->sku,
									'barcode' => $item->barcode,
									'product_variants' => $item->product_variants,
									'qty_ordered' => $item->qty_ordered,
									'qty_scanned' => $item->qty_scanned,
									'price' => $item->price,
									'total_price' => $item->total_price,
									'tax_percent' => $item->tax_percent,
									'tax_amount' => $item->tax_amount,
									'discount_amount' => $item->discount_amount,
									'applied_rule_ids' => $item->applied_rule_ids,
									'created_by' => $fbc_user_id,
									'created_at' => time(),
									'ip' => $_SERVER['REMOTE_ADDR']
								);

								$this->B2BOrdersModel->insertData('b2b_order_items', $insertdataitem);
							}
						}
					}
				}


				/*-------------------Pending item------------------------------*/



				if (isset($QtyPendingItem) && count($QtyPendingItem) > 0) {
					$sutotal_two = 0;
					$totalqty_two = 0;
					foreach ($QtyPendingItem as $item) {
						$total_price = 0;
						$total_price = $item->price * $item->qty_ordered;
						$sutotal_two = $total_price + $sutotal_two;
						$totalqty_two = $totalqty_two + $item->qty_ordered;
					}

					/*****---------calculate net pay-----------------------****/

					if ((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount > 0) && $sutotal_two > 0) {
						$RowTotalData = $this->CommonModel->calculate_percent_data($sutotal_two, $FbcUserB2BData->buyin_discount);
						$percent_amount = $RowTotalData['percent_amount'];
						$grandtotal_two = $sutotal_two - $percent_amount;
					} else {
						$percent_amount = 0;
						$grandtotal_two = $sutotal_two;
					}

					$insertdatatwo = array(
						'increment_id' => $order_two,
						'order_barcode' => $order_two,
						'shipment_type' => $OrderData->shipment_type,
						'webshop_order_id' => $OrderData->webshop_order_id,
						'status' => 0,
						'parent_id' => $OrderData->order_id,
						'main_parent_id' => $main_parent_id,
						'shop_id' => $OrderData->shop_id,
						'base_grand_total' => $grandtotal_two,
						'base_subtotal' => $sutotal_two,
						'grand_total' => $grandtotal_two,
						'discount_percent'	=> $discount_percent,
						'discount_amount'	=> $percent_amount,
						'subtotal' => $sutotal_two,
						'total_qty_ordered' => $totalqty_two,
						'system_generated_split_order' => 1,
						'created_by' => $fbc_user_id,
						'created_at' => time(),
						'ip' => $_SERVER['REMOTE_ADDR']
					);

					$split_order_two = $this->B2BOrdersModel->insertData('b2b_orders', $insertdatatwo);

					if ($split_order_two) {

						foreach ($QtyPendingItem as $item2) {

							$CheckPartialScanned = $this->B2BOrdersModel->getPartialScannedSingleOrderItems($order_id, $item2->product_id);
							if (isset($CheckPartialScanned) && $CheckPartialScanned->item_id != '') {
								$qty_ordered = $CheckPartialScanned->qty_ordered - $CheckPartialScanned->qty_scanned;
							} else {
								$qty_ordered = $item2->qty_ordered;
							}


							$insertdataitem = array(
								'order_id' => $split_order_two,
								'product_id' => $item2->product_id,
								'parent_product_id' => $item2->parent_product_id,
								'product_type' => $item2->product_type,
								'product_name' => $item2->product_name,
								'product_code' => $item2->product_code,
								'sku' => $item2->sku,
								'barcode' => $item2->barcode,
								'product_variants' => $item2->product_variants,
								'qty_ordered' => $qty_ordered,
								'qty_scanned' => 0,
								'price' => $item2->price,
								'total_price' => $item2->total_price,
								'tax_percent' => $item2->tax_percent,
								'tax_amount' => $item2->tax_amount,
								'discount_amount' => $item2->discount_amount,
								'applied_rule_ids' => $item2->applied_rule_ids,
								'created_by' => $fbc_user_id,
								'created_at' => time(),
								'ip' => $_SERVER['REMOTE_ADDR']
							);

							$this->B2BOrdersModel->insertData('b2b_order_items', $insertdataitem);
						}
					}
				}

				/*------------update main order-----------------------------*/
				$split_update = array('is_split' => 1, 'status' => 1, 'updated_at' => time());
				$where_arr = array('order_id' => $order_id);

				$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $split_update);

				$SplitOrderIdsName = $order_one . ' & ' . $order_two;

				/*----------------Send Email to shop owner--------------------*/
				$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
				$webshop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);
				$webshop_details = $this->CommonModel->get_webshop_details($shop_id);
				$owner_email = $webshop_owner->email;
				$shop_name = $shop_owner->org_shop_name;
				$templateId = 'fbcuser-split-order';
				$to = $owner_email;
				$site_logo = '';
				if (isset($webshop_details)) {
					$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				} else {
					$shop_logo = '';
				}
				$burl = base_url();
				$shop_logo = get_s3_url($shop_logo, $shop_id);
				$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
					<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
				</a>';
				$username = $webshop_owner->owner_name;
				$TempVars = array();
				$DynamicVars = array();

				$TempVars = array("##OWNER##", "##ORDERID##", "##ORDER_NOTE##", "##SPLITORDERID##", "##WEBSHOPNAME##");
				$DynamicVars   = array($username, $increment_id, $split_message, $SplitOrderIdsName, $shop_name);
				$CommonVars = array($site_logo, $shop_name);
				if (isset($templateId)) {
					$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
					if ($emailSendStatusFlag == 1) {
						$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars);
					}
				}


				$arrResponse  = array('status' => 200, 'message' => 'Order split successfully.', 'split_order_id' => $split_order_one);
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Order can not split.');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
	}


	function confirmOrder()
	{

		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$current_tab = $_POST['current_tab'];

			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$shop_upload_path = 'shop' . $shop_id;

			$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');


			if ($current_tab == 'order') {
				$Parent_order_id = $OrderData->order_id;
			} else if ($current_tab == 'split-order') {
				$Parent_order_id = $OrderData->main_parent_id;
			}

			if ($order_id != $Parent_order_id) {
			}

			$NewOrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $Parent_order_id), '');

			$status_arr = array('0', '1', '2', '3');
			/*
			if(in_array($NewOrderData->status,$status_arr)){
				$split_update=array('status'=>4,'updated_at'=>time());
				$where_arr=array('order_id'=>$Parent_order_id);
				$this->B2BOrdersModel->updateData('b2b_orders',$where_arr,$split_update);
			}
			*/

			$st_update = array('status' => 1, 'updated_at' => time());  // Processing
			$where_arr = array('order_id' => $order_id);
			$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $st_update);

			$arrResponse  = array('status' => 200, 'message' => 'Order confirmed successfully.');
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function createShipmentPage()
	{

		$order_id = $this->uri->segment(4);
		if (isset($order_id) && $order_id > 0) {
			$data['PageTitle'] = 'B2B - Orders';
			$data['side_menu'] = 'b2b';
			$data['current_tab'] = 'create-shipment';

			$this->session->unset_userdata('temp_box_weight');
			$this->session->unset_userdata('temp_order_id');
			$this->session->unset_userdata('temp_additional_message');

			$shop_id		=	$this->session->userdata('ShopID');

			/*
			$CheckShipmentExist=$this->B2BOrdersModel->CheckShipmentExist($order_id);

			if(isset($CheckShipmentExist) && $CheckShipmentExist->id!='' ){
				redirect('b2b/orders/');
			}
			*/

			$data['ShipmentService'] = $ShipmentService = $this->B2BOrdersModel->getMultiDataById('shipment_master', array('status' => 1), '', 'id', 'ASC');


			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			$main_order_id = $order_id;
			if ($OrderData->parent_id != '' && $OrderData->parent_id > 0) {
				$data['ParentOrder'] = $ParentOrder = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $OrderData->main_parent_id), '');
				$data['SplitOrderIds'] = $this->B2BOrdersModel->getSplitChildOrderIds($OrderData->main_parent_id);
				$main_order_id = $OrderData->main_parent_id;
			}

			$OwnShop = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $shop_id), '');

			if (isset($OwnShop->shop_flag) && $OwnShop->shop_flag == 1) {

				$MainOrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $main_order_id), '');

				if ((isset($MainOrderData->webshop_order_id) && $MainOrderData->webshop_order_id > 0) && ($MainOrderData->shipment_type == 2)) {
					$webshop_shop_id = $MainOrderData->shop_id;
					$FindZumbaShop = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
					if (isset($FindZumbaShop) && ($FindZumbaShop->shop_flag == 2 || $FindZumbaShop->shop_flag == 4)) {
						$webshop_fbc_user_id = $FindZumbaShop->fbc_user_id;

						$args['shop_id']	=	$webshop_shop_id;
						$args['fbc_user_id']	=	$webshop_fbc_user_id;


						$this->load->model('ShopProductModel');
						$this->ShopProductModel->init($args);

						$data['ShipmentService'] = $ShipmentService = $this->ShopProductModel->getMultiDataById('shipment_master', array('status' => 1), '', 'id', 'ASC');
					}
				}
			}

			$MainOrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $main_order_id), '');

			if ($MainOrderData->shipment_type == 2) {


				if (isset($MainOrderData->webshop_order_id)  && $MainOrderData->webshop_order_id > 0) {
					$webshop_order_id = $MainOrderData->webshop_order_id;
					$webshop_shop_id = $MainOrderData->shop_id;

					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
					$webshop_fbc_user_id = $FbcUser->fbc_user_id;
					if ($FbcUser->shop_flag) {
						$data['webshop_order_shop_flag'] = $FbcUser->shop_flag; //new shop flag order webshop
					}
					$args['shop_id']	=	$webshop_shop_id;
					$args['fbc_user_id']	=	$webshop_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);
					$data['sales_order_data_invoice'] = $this->ShopProductModel->getOrderDatabyOrderIdsForWebshop($webshop_order_id); // new invoice
					$data['webshop_order_id']	=	$webshop_order_id;
					$data['webshop_shop_id']	=	$webshop_shop_id;
					$data['webshop_fbc_user_id']	=	$webshop_fbc_user_id;

					$data['ShippingAddress'] = $ShippingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), '');
					$data['BillingAddress'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 1), '');

					$data['FormattedAddress'] = $this->ShopProductModel->getFormattedAddress($ShippingAddress);
				}
			}

			$data['order_shipment_type'] = $MainOrderData->shipment_type ?? '';
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);

			$data['OrderItems'] = $OrderItems = $this->B2BOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);

			$this->load->view('b2b/order/create_shipment', $data);
		} else {
			redirect('/b2b/orders');
		}
	}

	function createShipment()
	{
		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
			$order_id = $_POST['order_id'];
			$box_weight = $_POST['box_weight'];
			$shipment_id = $_POST['shipment_id'];
			$additional_message = $_POST['additional_message'];

			// api added new
			$tracking_id = '';
			$tracking_url = '';
			$apiReturnData = '';
			// end api added new

			if ($shipment_id == '') {

				$arrResponse  = array('status' => 400, 'message' => 'Please select shipment service.');
				echo json_encode($arrResponse);
				exit;
			} else {

				$fbc_user_id	=	$this->session->userdata('LoginID');
				$shop_id		=	$this->session->userdata('ShopID');

				$count = 1;

				if (isset($box_weight) && count($box_weight) > 0) {
					$apiStatus = ''; //new addw
					// start api
					$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), ''); //testing removed after development twice used it
					//print_r($OrderData);
					if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {
						$webshop_order_id = $OrderData->webshop_order_id;
						$webshop_shop_id = $OrderData->shop_id;
						$parent_id = $OrderData->main_parent_id; //b2b order table supplier
						$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
						$webshop_fbc_user_id = $FbcUser->fbc_user_id;
						$args['shop_id']	=	$webshop_shop_id;
						$args['fbc_user_id']	=	$webshop_fbc_user_id;
						$this->load->model('ShopProductModel');
						$this->ShopProductModel->init($args);
						$paymentMethodShopflag2 = $this->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id' => $webshop_order_id), 'payment_method');
						//print_r($paymentMethodShopflag2->payment_method);
						if (isset($paymentMethodShopflag2) && $paymentMethodShopflag2->payment_method == 'cod' && $shipment_id == 3) {
							//$shipment_id==3(Delivery LR)
							if (isset($FbcUser->shop_flag) && ($FbcUser->shop_flag == 2 || $FbcUser->shop_flag == 4)) {
								$webshopOrderData = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '*');
								if ($FbcUser->shop_flag == 4) {
									$apiResponse = $this->deliveryB2BAPiProcessData($order_id, $OrderData->order_barcode, $shipment_id, $webshop_order_id, $box_weight, 'COD');
									$apiResponseData = $apiResponse['apiResponseData'];
									$apiStatus = $apiResponseData->packages[0]->status;
									$tracking_id = $apiResponse['tracking_id']; //new
									$tracking_url = $apiResponse['tracking_url']; //new
									$apiReturnData = $apiResponse['apiReturnData']; //new
								} else {

									$shipingApidata = $this->ShopProductModel->getSingleDataByID('shipment_master', array('id' => $shipment_id), 'api_details');
									$shipmentApiUrl = '';
									$shipmentApiToken = '';
									$shipmentTrackingUrl = '';
									/*start delhivery required data static to dynamic*/
									$shipments_seller_name = '';
									$shipments_seller_add = '';
									$shipments_seller_gst_tin = '';
									$shipments_return_state = '';
									$shipments_return_city = '';
									$shipments_return_country = '';
									$shipments_return_pin = '';
									$shipments_return_name = '';
									$shipments_return_add = '';
									$shipments_pickup_name = '';
									$shipments_pickup_city = '';
									$shipments_pickup_pin = '';
									$shipments_pickup_country = '';
									$shipments_pickup_add = '';
									$shipments_pickup_state = '';
									$shipments_phone = '';
									/*end delhivery required data static to dynamic*/

									if (isset($shipingApidata) && !empty($shipingApidata)) {
										$shipingApiDetails = json_decode($shipingApidata->api_details);
										if (isset($shipingApiDetails) && !empty($shipingApiDetails)) {
											$shipmentApiUrl = $shipingApiDetails->api_url;
											$shipmentApiToken = $shipingApiDetails->api_token;
											$shipmentTrackingUrl = $shipingApiDetails->tracking_url;

											/*start delhivery required data static to dynamic*/
											$shipments_seller_name = $shipingApiDetails->shipments->seller_name ?? '';
											$shipments_seller_add = $shipingApiDetails->shipments->seller_add ?? '';
											$shipments_seller_gst_tin = $shipingApiDetails->shipments->seller_gst_tin ?? '';
											$shipments_return_state = $shipingApiDetails->shipments->return_state ?? '';
											$shipments_return_city = $shipingApiDetails->shipments->return_city ?? '';
											$shipments_return_country = $shipingApiDetails->shipments->return_country ?? '';
											$shipments_return_pin = $shipingApiDetails->shipments->return_pin ?? '';
											$shipments_return_name = $shipingApiDetails->shipments->return_name ?? '';
											$shipments_return_add = $shipingApiDetails->shipments->return_add ?? '';
											$shipments_pickup_name = $shipingApiDetails->shipments->pickup_name ?? '';
											$shipments_pickup_city = $shipingApiDetails->shipments->pickup_city ?? '';
											$shipments_pickup_pin = $shipingApiDetails->shipments->pickup_pin ?? '';
											$shipments_pickup_country = $shipingApiDetails->shipments->pickup_country ?? '';
											$shipments_pickup_add = $shipingApiDetails->shipments->pickup_add ?? '';
											$shipments_pickup_state = $shipingApiDetails->shipments->pickup_state ?? '';
											$shipments_pickup_phone = $shipingApiDetails->shipments->phone ?? '';
											/*end delhivery required data static to dynamic*/
										}
										//json_decode($jsonobj)
									}
									// webshop data
									$webshopOrderInvoiceData = $this->ShopProductModel->getSingleDataByID('invoicing', array('invoice_order_nos' => $webshop_order_id, 'invoice_order_type' => 1), '*');
									// shipping mobile number
									$mobile_number_shipping_data = $this->ShopProductModel->getSingleShopDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), 'mobile_no');
									$mobile_number_shipping = $mobile_number_shipping_data->mobile_no;
									//end shipping mobile number


									// webshop price
									$webshopProductPrice = 0;
									$webshopTaxValue = $webshopOrderInvoiceData->invoice_tax; // invoice tax
									$webshopTotalAmount = 0;
									$boxWeight = 0;
									$productQty = 0;
									$webshopCodAmount = 0;
									$productCategoryDetails = '';
									//$shipmentData['shipments']
									$shipmentDataObj = array();
									$shipmentDataObj['shipments'] = array();
									$shipmentDataObj['pickup_location'] = array();
									// $shipmentDataObj['shipments']['qc']=array();

									//end webshop price

									if (isset($webshopOrderData) && !empty($webshopOrderData) && isset($webshopOrderInvoiceData) && !empty($webshopOrderInvoiceData)) {
										$productDesc = '';
										// webshop invoicing item data
										$webshopOrderInvoiceItemData = $this->ShopProductModel->getMultiDataById('invoicing_details', array('invoice_id' => $webshopOrderInvoiceData->id), '*');
										foreach ($webshopOrderInvoiceItemData as $itemkey => $itemvalue) {
											//print_r($itemvalue);
											if ($itemvalue->product_id > 0) {
												$WebshopOrderProduct_id = $itemvalue->product_id;
												$WebshopOrderProduct_name = $itemvalue->product_name;
												$WebshopOrderProduct_hns_code = $itemvalue->product_hsn_code;
												$WebshopOrderProduct_Qty = $itemvalue->product_qty;
												// $WebshopOrderProduct_hns_code=$webshopOrderInvoiceData->product_hsn_code;
												// webshop product price
												$webshopProductPriceData = $this->ShopProductModel->getSingleDataByID('products', array('id' => $WebshopOrderProduct_id), 'webshop_price');
												if (isset($webshopProductPriceData) && $webshopProductPriceData->webshop_price) {
													$webshopPrice = $webshopProductPriceData->webshop_price;
													$webshopProductPrice += ($webshopPrice * $WebshopOrderProduct_Qty);
												}
												// total product qty
												$productQty += $WebshopOrderProduct_Qty;


												//category
												$webshopProductCategoryId = $this->ShopProductModel->getSingleDataByID('products_category', array('product_id' => $WebshopOrderProduct_id), 'category_ids');
												//end category
												if (isset($webshopProductCategoryId) && $webshopProductCategoryId->category_ids) {

													// main data base
													$productCategory = $this->CommonModel->getSingleDataByID('category', array('id' => $webshopProductCategoryId->category_ids), '*');
													// print_r($productCategory);
													$productCategoryDetails = $productCategory->cat_name;
													//end main db
												}
												// product item details
												// $productItem1['item']=array();
												$productItem['item'] = array();
												$productItem1['descr'] = $WebshopOrderProduct_name;
												// $productItem1['pcat']='Apparel';
												$productItem1['pcat'] = $productCategoryDetails;
												$productItem1['item_quantity'] = $WebshopOrderProduct_Qty;
												array_push($productItem['item'], $productItem1);

												$variant_data = '';
												if (isset($itemvalue->product_variants) && $itemvalue->product_variants != '') {
													$variants = json_decode($itemvalue->product_variants, true);
													if (isset($variants) && count($variants) > 0) {
														foreach ($variants as $pk => $single_variant) {
															if ($pk > 0) {
																$variant_data .= ', ';
															}
															foreach ($single_variant as $key => $val) {
																//$variant_data.='-'.$val;
																$variant_data .= ' ' . $key . ' - ' . $val;
															}
														}
													}
												}
												if ($variant_data) {
													$variant_data = '(' . $variant_data . ')';
												}
												$productNameVariets = $WebshopOrderProduct_name . $variant_data;
												$productDesc .= $productNameVariets . ',';
											}
										}

										if (!empty($productDesc)) {
											$productDesc = rtrim($productDesc, ',');
										}
										// print_r($productDesc);
										$shipmentData['shipments']['products_desc'] = $this->CommonModel->specialCharatcterRemove($productDesc);
										// end webshop invoicing item data
										// print_r(count($webshopOrderInvoiceItemData));exit();
										// api data
										$shipmentData['shipments']['add'] = str_replace(';', ',', $webshopOrderInvoiceData->ship_address_line1) . ',' . str_replace(';', ',', $webshopOrderInvoiceData->ship_address_line2);
										$shipmentData['shipments']['phone'] = $mobile_number_shipping;
										$shipmentData['shipments']['payment_mode'] = 'COD';
										$shipmentData['shipments']['name'] = $webshopOrderInvoiceData->customer_first_name . ' ' . $webshopOrderInvoiceData->customer_last_name;
										$shipmentData['shipments']['pin'] = $webshopOrderInvoiceData->ship_pincode;
										$shipmentData['shipments']['order'] = $webshopOrderData->order_barcode . '-' . $OrderData->order_barcode;

										$shipmentData['shipments']['city'] = $webshopOrderInvoiceData->ship_city;
										$shipmentData['shipments']['state'] = $webshopOrderInvoiceData->ship_state;
										$shipmentData['shipments']['country'] = $webshopOrderInvoiceData->ship_country;

										// invoice grand total
										$WebshopInvoiceTotal = $webshopOrderInvoiceData->invoice_grand_total;
										$webshopCodAmount = $WebshopInvoiceTotal; // pending check payment and all condition


										$shipmentData['shipments']['cod_amount'] = $webshopCodAmount;
										$shipmentData['shipments']['commodity_value'] = $webshopProductPrice;
										// $shipmentData['shipments']['tax_value']=300;
										$shipmentData['shipments']['tax_value'] = $webshopTaxValue;
										$webshopTotalAmount = $webshopProductPrice + $webshopTaxValue;
										$shipmentData['shipments']['total_amount'] = $webshopTotalAmount;


										//item data

										$shipmentData['shipments']['quantity'] = $productQty;

										// seller details
										$shipmentData['shipments']['seller_name'] = $shipments_seller_name;
										$shipmentData['shipments']['seller_add'] = $shipments_seller_add;
										$shipmentData['shipments']['seller_inv'] = $webshopOrderInvoiceData->invoice_no;
										$shipmentData['shipments']['seller_inv_date'] = '';
										if (isset($webshopOrderInvoiceData->invoice_date) && $webshopOrderInvoiceData->invoice_date != '') {
											$invDate = date(DATE_PIC_FM, $webshopOrderInvoiceData->invoice_date);
											$shipmentData['shipments']['seller_inv_date'] = date('Y-m-d H:i:s', strtotime($invDate));
										}
										// $shipmentData['shipments']['seller_inv_date']=date('Y-m-d H:i:s');;
										$shipmentData['shipments']['seller_gst_tin'] = $shipments_seller_gst_tin;

										//return
										$shipmentData['shipments']['return_state'] = $shipments_return_state;
										$shipmentData['shipments']['return_city'] = $shipments_return_city;
										$shipmentData['shipments']['return_country'] = $shipments_return_country;
										$shipmentData['shipments']['return_pin'] = $shipments_return_pin;
										$shipmentData['shipments']['return_name'] = $shipments_return_name;
										$shipmentData['shipments']['return_add'] = $shipments_return_add;

										// pickup address
										$pickup['name'] = $shipments_pickup_name;
										$pickup['city'] = $shipments_pickup_city;
										$pickup['pin'] = $shipments_pickup_pin;
										$pickup['country'] = $shipments_pickup_country;
										$pickup['add'] = $shipments_pickup_add;
										$pickup['state'] = $shipments_pickup_state;
										$pickup['phone'] = $shipments_pickup_phone;
										// end pickup address

										$shipmentData['shipments']['qc'] = [];
										//api call
										if (count($box_weight) > 1) {
										} else {
											//$apiData=array('test' =>1 );
											if (isset($box_weight) && $box_weight[0]) {
												$boxWeight = $box_weight[0] * 1000;
											}
											$shipmentData['shipments']['weight'] = $boxWeight;
											// $shipmentData['shipments']['qc']=$productItem;
											// array_push($shipmentData['shipments']['qc'],$productItem);
											// $shipmentData['shipments']['qc']=$productItem;
											$shipmentData['shipments']['qc'] = $productItem;
											array_push($shipmentDataObj['shipments'], $shipmentData['shipments']);
											// array_push($shipmentDataObj['shipments'],$productItem);
											// array_push($shipmentDataObj['shipments'],$shipmentData['shipments']);
											$shipmentDataObj['pickup_location'] = $pickup;
											// array_push($shipmentDataObj['shipments']['qc'],$productItem);
											/*print_r($shipmentDataObj);
												exit();*/
											$apiReturnData = $this->delhiveryApi($shipmentApiUrl, $shipmentApiToken, $shipmentDataObj);

											if ($apiReturnData) {
												// print_r($apiReturnData);
												$apiResponseData = json_decode($apiReturnData);
												// $apiResponseData->packages[0]
												$apiStatus = $apiResponseData->packages[0]->status; //api status
												if ($apiStatus == 'Success') {
													$tracking_id = $apiResponseData->packages[0]->waybill;
													$tracking_url = $shipmentTrackingUrl . $tracking_id;
													//$tracking_id
												}
												// $apiResponseData->packages[0]->remarks[0] // error remark
												//$waybill=$apiResponseData->packages[0]->waybill;
												//print_r($apiResponseData->packages[0]->remarks[0]);
											}
										}
									}
									//end api call
								} // shop_flag condtion check

							}
						} elseif (isset($paymentMethodShopflag2) && $paymentMethodShopflag2->payment_method != 'cod' && $shipment_id == 3) {

							if (isset($FbcUser->shop_flag) && ($FbcUser->shop_flag == 2 || $FbcUser->shop_flag == 4)) {
								if ($FbcUser->shop_flag == 4) {
									$apiResponse = $this->deliveryB2BAPiProcessData($order_id, $OrderData->order_barcode, $shipment_id, $webshop_order_id, $box_weight, 'Prepaid');
									$apiResponseData = $apiResponse['apiResponseData'];
									$apiStatus = $apiResponseData->packages[0]->status;
									$tracking_id = $apiResponse['tracking_id']; //new
									$tracking_url = $apiResponse['tracking_url']; //new
									$apiReturnData = $apiResponse['apiReturnData']; //new
								} else {
									$shipingApidata = $this->ShopProductModel->getSingleDataByID('shipment_master', array('id' => $shipment_id), 'api_details');
									$shipmentApiUrl = '';
									$shipmentApiToken = '';
									$shipmentTrackingUrl = '';

									/*start delhivery required data static to dynamic*/
									$shipments_seller_name = '';
									$shipments_seller_add = '';
									$shipments_seller_gst_tin = '';
									$shipments_return_state = '';
									$shipments_return_city = '';
									$shipments_return_country = '';
									$shipments_return_pin = '';
									$shipments_return_name = '';
									$shipments_return_add = '';
									$shipments_pickup_name = '';
									$shipments_pickup_city = '';
									$shipments_pickup_pin = '';
									$shipments_pickup_country = '';
									$shipments_pickup_add = '';
									$shipments_pickup_state = '';
									$shipments_phone = '';
									/*end delhivery required data static to dynamic*/

									if (isset($shipingApidata) && !empty($shipingApidata)) {
										$shipingApiDetails = json_decode($shipingApidata->api_details);
										if (isset($shipingApiDetails) && !empty($shipingApiDetails)) {
											$shipmentApiUrl = $shipingApiDetails->api_url;
											$shipmentApiToken = $shipingApiDetails->api_token;
											$shipmentTrackingUrl = $shipingApiDetails->tracking_url;
											/*start delhivery required data static to dynamic*/
											$shipments_seller_name = $shipingApiDetails->shipments->seller_name ?? '';
											$shipments_seller_add = $shipingApiDetails->shipments->seller_add ?? '';
											$shipments_seller_gst_tin = $shipingApiDetails->shipments->seller_gst_tin ?? '';
											$shipments_return_state = $shipingApiDetails->shipments->return_state ?? '';
											$shipments_return_city = $shipingApiDetails->shipments->return_city ?? '';
											$shipments_return_country = $shipingApiDetails->shipments->return_country ?? '';
											$shipments_return_pin = $shipingApiDetails->shipments->return_pin ?? '';
											$shipments_return_name = $shipingApiDetails->shipments->return_name ?? '';
											$shipments_return_add = $shipingApiDetails->shipments->return_add ?? '';
											$shipments_pickup_name = $shipingApiDetails->shipments->pickup_name ?? '';
											$shipments_pickup_city = $shipingApiDetails->shipments->pickup_city ?? '';
											$shipments_pickup_pin = $shipingApiDetails->shipments->pickup_pin ?? '';
											$shipments_pickup_country = $shipingApiDetails->shipments->pickup_country ?? '';
											$shipments_pickup_add = $shipingApiDetails->shipments->pickup_add ?? '';
											$shipments_pickup_state = $shipingApiDetails->shipments->pickup_state ?? '';
											$shipments_pickup_phone = $shipingApiDetails->shipments->phone ?? '';
											/*end delhivery required data static to dynamic*/
										}
										//json_decode($jsonobj)
									}
									// webshop data
									$webshopOrderData = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '*');
									$webshopOrderInvoiceData = $this->ShopProductModel->getSingleDataByID('invoicing', array('invoice_order_nos' => $webshop_order_id, 'invoice_order_type' => 1), '*');
									// shipping mobile number
									$mobile_number_shipping_data = $this->ShopProductModel->getSingleShopDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), 'mobile_no');
									$mobile_number_shipping = $mobile_number_shipping_data->mobile_no;
									//end shipping mobile number


									// webshop price
									$webshopProductPrice = 0;
									$webshopTaxValue = $webshopOrderInvoiceData->invoice_tax; // invoice tax
									$webshopTotalAmount = 0;
									$boxWeight = 0;
									$productQty = 0;
									$webshopCodAmount = 0;
									$productCategoryDetails = '';
									//$shipmentData['shipments']
									$shipmentDataObj = array();
									$shipmentDataObj['shipments'] = array();
									$shipmentDataObj['pickup_location'] = array();
									// $shipmentDataObj['shipments']['qc']=array();

									//end webshop price

									if (isset($webshopOrderData) && !empty($webshopOrderData) && isset($webshopOrderInvoiceData) && !empty($webshopOrderInvoiceData)) {
										$productDesc = '';
										// webshop invoicing item data
										$webshopOrderInvoiceItemData = $this->ShopProductModel->getMultiDataById('invoicing_details', array('invoice_id' => $webshopOrderInvoiceData->id), '*');
										foreach ($webshopOrderInvoiceItemData as $itemkey => $itemvalue) {
											//print_r($itemvalue);

											if ($itemvalue->product_id > 0) {
												$WebshopOrderProduct_id = $itemvalue->product_id;
												$WebshopOrderProduct_name = $itemvalue->product_name;
												$WebshopOrderProduct_hns_code = $itemvalue->product_hsn_code;
												$WebshopOrderProduct_Qty = $itemvalue->product_qty;
												// $WebshopOrderProduct_hns_code=$webshopOrderInvoiceData->product_hsn_code;
												// webshop product price
												$webshopProductPriceData = $this->ShopProductModel->getSingleDataByID('products', array('id' => $WebshopOrderProduct_id), 'webshop_price');
												if (isset($webshopProductPriceData) && $webshopProductPriceData->webshop_price) {
													$webshopPrice = $webshopProductPriceData->webshop_price;
													$webshopProductPrice += ($webshopPrice * $WebshopOrderProduct_Qty);
												}
												// total product qty
												$productQty += $WebshopOrderProduct_Qty;


												//category
												$webshopProductCategoryId = $this->ShopProductModel->getSingleDataByID('products_category', array('product_id' => $WebshopOrderProduct_id), 'category_ids');
												//end category
												if (isset($webshopProductCategoryId) && $webshopProductCategoryId->category_ids) {

													// main data base
													$productCategory = $this->CommonModel->getSingleDataByID('category', array('id' => $webshopProductCategoryId->category_ids), '*');
													// print_r($productCategory);
													$productCategoryDetails = $productCategory->cat_name;
													//end main db
												}
												// product item details
												// $productItem1['item']=array();
												$productItem['item'] = array();
												$productItem1['descr'] = $WebshopOrderProduct_name;
												$productItem1['pcat'] = $productCategoryDetails;
												$productItem1['item_quantity'] = $WebshopOrderProduct_Qty;
												array_push($productItem['item'], $productItem1);

												$variant_data = '';
												if (isset($itemvalue->product_variants) && $itemvalue->product_variants != '') {
													$variants = json_decode($itemvalue->product_variants, true);
													if (isset($variants) && count($variants) > 0) {
														foreach ($variants as $pk => $single_variant) {
															if ($pk > 0) {
																$variant_data .= ', ';
															}
															foreach ($single_variant as $key => $val) {
																//$variant_data.='-'.$val;
																$variant_data .= ' ' . $key . ' - ' . $val;
															}
														}
													}
												}
												if ($variant_data) {
													$variant_data = '(' . $variant_data . ')';
												}
												$productNameVariets = $WebshopOrderProduct_name . $variant_data;
												$productDesc .= $productNameVariets . ',';
												//$productDesc.=$WebshopOrderProduct_name;
											}
										}
										if (!empty($productDesc)) {
											$productDesc = rtrim($productDesc, ',');
										}
										// print_r($productDesc);
										//$shipmentData['shipments']['products_desc']=$productDesc;

										$shipmentData['shipments']['products_desc'] = $this->CommonModel->specialCharatcterRemove($productDesc);

										//changes

										// end webshop invoicing item data
										// print_r(count($webshopOrderInvoiceItemData));exit();
										// api data

										$shipmentData['shipments']['add'] = str_replace(';', ',', $webshopOrderInvoiceData->ship_address_line1) . ',' . str_replace(';', ',', $webshopOrderInvoiceData->ship_address_line2);
										$shipmentData['shipments']['phone'] = $mobile_number_shipping;
										// $shipmentData['shipments']['payment_mode']='COD';//old
										$shipmentData['shipments']['payment_mode'] = 'Prepaid';
										$shipmentData['shipments']['name'] = $webshopOrderInvoiceData->customer_first_name . ' ' . $webshopOrderInvoiceData->customer_last_name;
										$shipmentData['shipments']['pin'] = $webshopOrderInvoiceData->ship_pincode;
										$shipmentData['shipments']['order'] = $webshopOrderData->order_barcode . '-' . $OrderData->order_barcode;
										//$shipmentData['shipments']['order']=$webshopOrderData->order_barcode.'-'.$OrderData->order_barcode.time();

										$shipmentData['shipments']['city'] = $webshopOrderInvoiceData->ship_city;
										$shipmentData['shipments']['state'] = $webshopOrderInvoiceData->ship_state;
										$shipmentData['shipments']['country'] = $webshopOrderInvoiceData->ship_country;

										// invoice grand total
										$WebshopInvoiceTotal = $webshopOrderInvoiceData->invoice_grand_total;
										$webshopCodAmount = $WebshopInvoiceTotal; // pending check payment and all condition


										// $shipmentData['shipments']['cod_amount']=$webshopCodAmount;
										$shipmentData['shipments']['commodity_value'] = $webshopProductPrice;
										// $shipmentData['shipments']['tax_value']=300;
										$shipmentData['shipments']['tax_value'] = $webshopTaxValue;
										$webshopTotalAmount = $webshopProductPrice + $webshopTaxValue;
										$shipmentData['shipments']['total_amount'] = $webshopTotalAmount;


										//item data

										$shipmentData['shipments']['quantity'] = $productQty;

										// product details
										//$qc=$productItem;
										//end product details

										//end item data

										// seller details
										$shipmentData['shipments']['seller_name'] = $shipments_seller_name;
										$shipmentData['shipments']['seller_add'] = $shipments_seller_add;
										$shipmentData['shipments']['seller_inv'] = $webshopOrderInvoiceData->invoice_no;
										$shipmentData['shipments']['seller_inv_date'] = '';
										if (isset($webshopOrderInvoiceData->invoice_date) && $webshopOrderInvoiceData->invoice_date != '') {
											$invDate = date(DATE_PIC_FM, $webshopOrderInvoiceData->invoice_date);
											$shipmentData['shipments']['seller_inv_date'] = date('Y-m-d H:i:s', strtotime($invDate));
										}
										$shipmentData['shipments']['seller_gst_tin'] = $shipments_seller_gst_tin;

										//return
										$shipmentData['shipments']['return_state'] = $shipments_return_state;
										$shipmentData['shipments']['return_city'] = $shipments_return_city;
										$shipmentData['shipments']['return_country'] = $shipments_return_country;
										$shipmentData['shipments']['return_pin'] = $shipments_return_pin;
										$shipmentData['shipments']['return_name'] = $shipments_return_name;
										$shipmentData['shipments']['return_add'] = $shipments_return_add;

										$pickup['name'] = $shipments_pickup_name;
										$pickup['city'] = $shipments_pickup_city;
										$pickup['pin'] = $shipments_pickup_pin;
										$pickup['country'] = $shipments_pickup_country;
										$pickup['add'] = $shipments_pickup_add;
										$pickup['state'] = $shipments_pickup_state;
										$pickup['phone'] = $shipments_pickup_phone;

										$shipmentData['shipments']['qc'] = [];
										//api call
										if (count($box_weight) > 1) {
										} else {
											//$apiData=array('test' =>1 );
											if (isset($box_weight) && $box_weight[0]) {
												$boxWeight = $box_weight[0] * 1000;
											}
											$shipmentData['shipments']['weight'] = $boxWeight;
											// $shipmentData['shipments']['qc']=$productItem;
											// array_push($shipmentData['shipments']['qc'],$productItem);
											// $shipmentData['shipments']['qc']=$productItem;
											$shipmentData['shipments']['qc'] = $productItem;
											array_push($shipmentDataObj['shipments'], $shipmentData['shipments']);
											// array_push($shipmentDataObj['shipments'],$productItem);
											// array_push($shipmentDataObj['shipments'],$shipmentData['shipments']);
											$shipmentDataObj['pickup_location'] = $pickup;
											// array_push($shipmentDataObj['shipments']['qc'],$productItem);
											$apiReturnData = $this->delhiveryApi($shipmentApiUrl, $shipmentApiToken, $shipmentDataObj);

											/*print_r($shipmentDataObj);
														exit();*/
											if ($apiReturnData) {

												$apiResponseData = json_decode($apiReturnData);
												// $apiResponseData->packages[0]
												// print_r($apiResponseData);
												// exit();
												if (isset($apiResponseData) && isset($apiResponseData->packages[0]->status)) {
													$apiStatus = $apiResponseData->packages[0]->status; //api status
													if ($apiStatus == 'Success') {
														$tracking_id = $apiResponseData->packages[0]->waybill;
														$tracking_url = $shipmentTrackingUrl . $tracking_id;
														//$tracking_id
													}
												}
												// $apiResponseData->packages[0]->remarks[0] // error remark
												//$waybill=$apiResponseData->packages[0]->waybill;
												//print_r($apiResponseData->packages[0]->remarks[0]);
											}
										}
									}
									//end api call
								} // end shop_flag check condition
							}
						} //end else if
					}
					//echo '<pre>';
					//print_r($shipmentDataObj);
					//print_r($apiReturnData);
					//echo '</pre>';
					//exit();
					/*api new changes add*/
					if ($apiStatus != 'Success' && $shipment_id == 3) {
						if (isset($apiStatus) && isset($apiResponseData->packages[0]->remarks)) {
							$apiMsg = $apiResponseData->packages[0]->remarks;
						} else {
							$apiMsg = $apiResponseData->rmk;
						}
						$arrResponse  = array('status' => 400, 'message' => $apiMsg);
						echo json_encode($arrResponse);
						exit;
					}
					/*end api new changes add*/
					/*print_r($shipmentDataObj);
					exit();*/

					// end api start


					$insertData = array(
						'order_id' => $order_id,
						'shipment_id' => $shipment_id,
						'message' => $additional_message,
						'created_by' => $fbc_user_id,
						'created_at' => time(),
						'ip' => $_SERVER['REMOTE_ADDR']
					);

					$order_shipment_id =		$this->B2BOrdersModel->insertData('b2b_order_shipment', $insertData);

					foreach ($box_weight as $box_val) {
						$insertData = array(
							'order_id' => $order_id,
							'order_shipment_id' => $order_shipment_id,
							'box_number' => $count,
							'weight' => $box_val,
							'tracking_id' => $tracking_id,
							'tracking_url' => $tracking_url,
							'api_response' => $apiReturnData,
							'created_by' => $fbc_user_id,
							'created_at' => time(),

						);

						$this->B2BOrdersModel->insertData('b2b_order_shipment_details', $insertData);

						$count++;
					}
				}


				$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');



				if ($OrderData->parent_id > 0) {
					//$ParentOrder=$this->B2BOrdersModel->getSingleDataByID('b2b_orders',array('order_id'=>$OrderData->main_parent_id),'');
					$Parent_order_id = $OrderData->main_parent_id;
				} else {
					$Parent_order_id = $order_id;
				}

				/*----------------Decrement Order Qty---------------------------*/
				$this->B2BOrdersModel->decrementOrderItemStock($order_id);

				/*---------------- Order Status update---------------------------*/

				$order_status = 4;

				if ($OrderData->parent_id != '') {
					$SplitOrderIds = $this->B2BOrdersModel->getSplitChildOrderIds($Parent_order_id);
					$count_new = 0;
					if (isset($SplitOrderIds) && count($SplitOrderIds)) {
						foreach ($SplitOrderIds as $value) {
							$trk_order_id = $value->order_id;

							$Row = $this->B2BOrdersModel->getSingleDataByID('b2b_order_shipment_details', array('tracking_id <>' => '-', 'order_id' => $trk_order_id), 'id,tracking_id');
							if (isset($Row) && $Row->tracking_id != '-') {
								$count_new++;
							}
						}
					}
					if ($count_new > 0) {
						$order_status = 5;
					} else {
						$order_status = 4;
					}
				} else {
					$order_status = 4;
				}

				//echo $order_status.'================'.$Parent_order_id;exit;


				$os_update = array('status' => $order_status, 'updated_at' => time()); 		// Tracking Missing  OR Tracking Incomplete
				$where_arr = array('order_id' => $Parent_order_id);
				$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $os_update);


				$increment_id = $OrderData->increment_id;
				$MainOrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $Parent_order_id), '');
				$webshop_shop_id = $MainOrderData->shop_id;

				if ($OrderData->shipment_type == 1) {

					/*----------------Send Email to shop owner--------------------*/
					$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);

					$webshop_details = $this->CommonModel->get_webshop_details($shop_id);

					$webshop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);
					$owner_email = $webshop_owner->email;
					$shop_name = $shop_owner->org_shop_name;
					$templateId = 'fbcuser-order-shipment-created';
					$to = $owner_email;
					$site_logo = '';
					if (isset($webshop_details)) {
						$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
					} else {
						$shop_logo = '';
					}
					$burl = base_url();
					$shop_logo = get_s3_url($shop_logo, $shop_id);
					$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
					<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
				</a>';
					$username = $webshop_owner->owner_name;
					$TempVars = array();
					$DynamicVars = array();

					$TempVars = array("##OWNER##", "##ORDERID##", "##MESSAGE##", "##WEBSHOPNAME##");
					$DynamicVars   = array($username, $increment_id, $additional_message, $shop_name);
					$CommonVars = array($site_logo, $shop_name);
					if (isset($templateId)) {
						$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
						if ($emailSendStatusFlag == 1) {
							$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars);
						}
					}
				} else {

					/*-----------Send order from Webshop seller db-----------------------------------------*/
					$main_order_id = $order_id;
					if ($OrderData->parent_id != '' && $OrderData->parent_id > 0) {

						$main_order_id = $OrderData->main_parent_id;
					}

					$MainOrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $main_order_id), '');

					if (isset($MainOrderData->webshop_order_id)  && $MainOrderData->webshop_order_id > 0) {
						$webshop_order_id = $MainOrderData->webshop_order_id;
						$webshop_shop_id = $MainOrderData->shop_id;

						$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
						$webshop_fbc_user_id = $FbcUser->fbc_user_id;

						$args['shop_id']	=	$webshop_shop_id;
						$args['fbc_user_id']	=	$webshop_fbc_user_id;

						$this->load->model('ShopProductModel');
						$this->ShopProductModel->init($args);

						$WebshopOrderData = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '');
						$webshop_increment_id = $WebshopOrderData->increment_id;

						$shop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);

						$owner_email = $WebshopOrderData->customer_email;
						$webshop_details = $this->CommonModel->get_webshop_details($webshop_shop_id);
						$shop_name = $shop_owner->org_shop_name;
						$templateId = 'fbcuser-order-shipment-created';
						$to = $owner_email;
						$site_logo = '';
						if (isset($webshop_details)) {
							$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
						} else {
							$shop_logo = '';
						}
						$shop_logo = get_s3_url($shop_logo, $shop_id);
						$site_logo =  '<a href="' . base_url() . '" style="color:#1E7EC8;">
							<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
						</a>';
						$username = $WebshopOrderData->customer_firstname . ' ' . $WebshopOrderData->customer_lastname;
						$TempVars = array();
						$DynamicVars = array();

						$TempVars = array("##OWNER##", "##ORDERID##", "##MESSAGE##", "##WEBSHOPNAME##");
						$DynamicVars   = array($username, $webshop_increment_id, $additional_message, $shop_name);
						$CommonVars = array($site_logo, $shop_name);
						if (isset($templateId)) {
							$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $webshop_shop_id);
							if ($emailSendStatusFlag == 1) {
								$mailSent = $this->ShopProductModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $webshop_increment_id, $CommonVars);
							}
						}
					}
				}



				$arrResponse  = array('status' => 200, 'message' => 'Shipment created successfully.', 'order_id' => $Parent_order_id);
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
	}


	function saveTrackingId()
	{

		if (isset($_POST['order_id']) && $_POST['order_id'] != '') {

			$shop_id		=	$this->session->userdata('ShopID');
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$order_id = $_POST['order_id'];
			$invoice_order_id = $_POST['order_id'];
			$tracking_id = $_POST['tracking_id'];

			if (isset($tracking_id) &&  count($tracking_id) > 0) {
				foreach ($tracking_id as $key => $value) {
					$value = isset($value) ? $value : '-';
					$tracking_url = $_POST['tracking_url'][$key];
					$_updatedata = array('tracking_id' => $value, 'tracking_url' => $tracking_url, 'updated_at' => time());
					$where_arr = array('id' => $key);
					$this->B2BOrdersModel->updateData('b2b_order_shipment_details', $where_arr, $_updatedata);
				}
			}

			$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');

			$web_shop_id = $OrderData->shop_id;



			if ($OrderData->is_split == 0) {
				/*
				$QtyScanItem=$this->B2BOrdersModel->getQtyFullyScannedOrderItems($order_id);
				$AllItems=$this->B2BOrdersModel->getOrderItems($order_id);
				if(count($QtyScanItem)==count($AllItems))
				{
					$odr_update=array('status'=>6,'updated_at'=>time());  	// Tracking Complete
					$where_arr=array('order_id'=>$order_id);
					$this->B2BOrdersModel->updateData('b2b_orders',$where_arr,$odr_update);
				}else{

				}*/
				$op_ct = 0;
				$Result = $this->B2BOrdersModel->getMultiDataById('b2b_order_shipment_details', array('order_id' => $order_id), 'id,tracking_id');
				if (isset($Result) && Count($Result) > 0) {
					foreach ($Result as $Row) {
						if (isset($Row) && ($Row->tracking_id == '-' || $Row->tracking_id == '')) {
							$op_ct++;
						}
					}
				}


				if ($op_ct > 0) {
					$order_status = 5;
				} else {

					/*--------------------Check if webshop order is completed-------------------------------------------*/

					$order_status = $this->ApplyWebshopOrderChanges($order_id);

					/*---------------------------------------------------------------*/
				}

				$odr_update = array('status' => $order_status, 'updated_at' => time());  	// Tracking Complete
				$where_arr = array('order_id' => $order_id);
				$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $odr_update);

				if ($order_status == 6) {

					$odr_update = array('tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Completed  date
					$where_arr = array('order_id' => $order_id);
					$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $odr_update);



					/*-------------update webshop b2b related order to  tracking complete------------------*/

					$increment_id = $OrderData->increment_id;
					$web_shop_id = $OrderData->shop_id;

					if ($OrderData->shipment_type == 1) {



						$burl = base_url();

						/*----------------Send Email to shop owner--------------------*/
						$webshop_owner = $this->CommonModel->getShopOwnerData($web_shop_id);
						$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
						$webshop_details = $this->CommonModel->get_webshop_details($shop_id);
						$owner_email = $shop_owner->email;
						$templateId = 'fbcuser-b2b-order-tracking-completed';
						$to = $webshop_owner->email;
						$shop_name = $shop_owner->org_shop_name;
						$username = $webshop_owner->owner_name;
						$site_logo = '';
						if (isset($webshop_details)) {
							$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
						}
						$burl = base_url();
						$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
						$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
								<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
							</a>';
						$TempVars = array("##OWNER##", "##ORDERID##", '##WEBSHOPNAME##');
						$DynamicVars   = array($username, $increment_id, $shop_name);
						$CommonVars = array($site_logo, $shop_name);
						if (isset($templateId)) {
							$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
							if ($emailSendStatusFlag == 1) {
								$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars);
							}
						}

						// invoice create and send invoice
						if (isset($this->CommonModel->page_access()->acc_inv_flag) && $this->CommonModel->page_access()->acc_inv_flag == 1 && $this->CommonModel->page_access()->semi_invoice == 0) {
							// invoice create and send invoice
							$b2borderData = $this->B2BOrdersModel->get_b2border_invoicing_data($order_id);
							// $customVariablesData=$this->CommonModel->get_custom_variables();
							// print_r($b2borderData);
							if ($b2borderData) {
								$shopName = $b2borderData->org_shop_name;
								// invoice type
								if ($b2borderData->invoice_type == 1) {
									//$invoiceType="Invoice Per Order";
									// invoice no and next no
									$customVariables_invoice_prefix = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_prefix'), 'value');
									$customVariables_invoice_no = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_next_no'), 'value');
									if ($customVariables_invoice_prefix->value && $customVariables_invoice_no->value) {
										$invoiceNo = $customVariables_invoice_no->value;
										$invoice_next_no = $invoiceNo + 1;
										$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
									} elseif ($customVariables_invoice_prefix->value) {
										// $invoiceNo='2';
										$invoiceNo = '1';
										$invoice_next_no = $invoiceNo + 1;
										$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
										# code...
									} elseif ($customVariables_invoice_no->value) {
										$invoiceNo = $customVariables_invoice_no->value;
										$invoice_next_no = $invoiceNo + 1;
										$invoice_no = 'INV' . $invoiceNo;
									}
									// custom_variable table data updated
									$invoice_no = $invoice_no;
									$invoice_next_no = $invoice_next_no;

									// invoice table insert

									$invoice_order_type = '2'; //1-Webshop;  2-B2Webshop;
									$shop_id = $shop_id; //old
									// $shop_id=$web_shop_id;
									$customer_name = $b2borderData->customer_name;
									$shop_webshop_name = $b2borderData->org_shop_name;
									$shop_gst_no = $b2borderData->gst_no;
									$shop_company_name = $b2borderData->company_name;
									$customer_email = $b2borderData->email;
									$invoice_subtotal = $b2borderData->subtotal;
									$invoice_tax = $b2borderData->subtotal;
									$invoice_grand_total = $b2borderData->subtotal;
									$bill_customer_first_name = $b2borderData->customer_name;
									$bill_customer_last_name = '';
									$bill_customer_id = '';
									$bill_customer_email = $b2borderData->email;
									$billing_address_line1 = $b2borderData->bill_address_line1;
									$billing_address_line2 = $b2borderData->bill_address_line2;
									$billing_city = $b2borderData->bill_city;
									$billing_state = $b2borderData->bill_state;
									$billing_country = $b2borderData->bill_country;
									$billing_pincode = $b2borderData->bill_pincode;
									$ship_address_line1 = $b2borderData->ship_address_line1;
									$ship_address_line2 = $b2borderData->ship_address_line2;
									$ship_city = $b2borderData->ship_city;
									$ship_state = $b2borderData->ship_state;
									$ship_country = $b2borderData->ship_country;
									$ship_pincode = $b2borderData->ship_pincode;
									$invoice_date = time();


									//get data by user id
									$invoice_due_date = '';
									if ($b2borderData->fbc_user_id) {
										$b2b_customers_invoiceData = $this->CommonModel->getSingleShopDataByID('b2b_customers_invoice', array('customer_id' => $b2borderData->fbc_user_id), 'invoice_type,payment_term');
										$invoice_type = $b2b_customers_invoiceData->invoice_type;
										$payment_term = $b2b_customers_invoiceData->payment_term;
										if ($invoice_date && $payment_term > 0) {
											//$daysAdd='+ '.$payment_term.'days';
											$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
											//$due_date=date('Y-m-d', strtotime($dateAdd. $daysAdd));
											$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
											$invoice_due_date = date(strtotime($due_date));
										} else {
										}
									} else {
										$payment_term = '';
										$due_date = '';
									}

									$invoice_term = $payment_term;

									$fbc_user_id_order = $b2borderData->fbc_user_id; //users and users_shop
									$email_sent_flag = '';

									// insert invoicing data
									$insertinvoicingdataitem = array(
										'invoice_no' => $invoice_no,
										'customer_first_name' => $customer_name,
										'customer_id' => $fbc_user_id_order,
										'customer_email' => $customer_email,
										'shop_id' => $web_shop_id,
										'shop_webshop_name' => $shop_webshop_name,
										'shop_company_name' => $shop_company_name,
										'shop_gst_no' => $shop_gst_no,
										'bill_customer_first_name' => $customer_name,
										// 'bill_customer_last_name'=>$customer_name,
										'bill_customer_id' => $fbc_user_id_order,
										'bill_customer_email' => $customer_email,
										'invoice_order_nos' => $order_id,
										'invoice_order_type' => $invoice_order_type,
										'invoice_subtotal' => $invoice_subtotal,
										'invoice_tax' => $invoice_tax,
										'invoice_grand_total' => $invoice_grand_total,
										'billing_address_line1' => $billing_address_line1,
										'billing_address_line2' => $billing_address_line2,
										'billing_city' => $billing_city,
										'billing_state' => $billing_state,
										'billing_country' => $billing_country,
										'billing_pincode' => $billing_pincode,
										'ship_address_line1' => $ship_address_line1,
										'ship_address_line2' => $ship_address_line2,
										'ship_city' => $ship_city,
										'ship_state' => $ship_state,
										'ship_country' => $ship_country,
										'ship_pincode' => $ship_pincode,
										'invoice_date' => $invoice_date,
										'invoice_due_date' => $invoice_due_date,
										'invoice_term' => $invoice_term,
										'created_by' => $fbc_user_id,
										'created_at' => time(),
										'ip' => $_SERVER['REMOTE_ADDR']
									);

									$invoicing_one = $this->B2BOrdersModel->insertData('invoicing', $insertinvoicingdataitem);

									if ($invoicing_one) {
										//update custom_variable
										$invoice_no_update = array('value' => $invoice_next_no);
										$where_invoice_arr = array('identifier' => 'invoice_next_no');

										$this->B2BOrdersModel->updateData('custom_variables', $where_invoice_arr, $invoice_no_update);
										// b2b order updated
										$invoice_b2b_order = array('invoice_id' => $invoicing_one, 'invoice_date' => $invoice_date, 'invoice_flag' => 1);
										$where_b2b_order_arr = array('order_id' => $order_id);
										$this->B2BOrdersModel->updateData('b2b_orders', $where_b2b_order_arr, $invoice_b2b_order);

										// send invoice email and save invoice pdf
										$pdfGeneratePdfName = $this->generatePdf($invoicing_one); // save pdf
										//send email with attachment
										if ($pdfGeneratePdfName) {
											//update invoicing
											$invoiceFileName = array('invoice_file' => $pdfGeneratePdfName);
											$where_invoice_filename_arr = array('id' => $invoicing_one);
											$this->B2BOrdersModel->updateData('invoicing', $where_invoice_filename_arr, $invoiceFileName);

											// sent email
											/*----------------Send Email to invoice with attchmnet--------------------*/
											$Ishop_owner = $this->CommonModel->getShopOwnerData($shop_id);
											$Iwebshop_details = $this->CommonModel->get_webshop_details($shop_id);
											$Ishop_name = $Ishop_owner->org_shop_name;
											$ItemplateId = 'system-invoice';
											// $Ito = 'rajesh@bcod.co.in';
											$Ito = $customer_email;
											$site_logo = '';
											if (isset($Iwebshop_details)) {
												$Ishop_logo = $this->encryption->decrypt($Iwebshop_details['site_logo']);
											} else {
												$Ishop_logo = '';
											}
											$burl = base_url();
											$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);
											$Isite_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
												<img alt="' . $Ishop_name . '" border="0" src="' . $Ishop_logo . '" style="max-width:200px" />
											</a>';
											$Iusername = $customer_name;
											$ITempVars = array();
											$IDynamicVars = array();

											$ITempVars = array("##OWNER##", "##INVOICENO##", "##WEBSHOPNAME##");
											$IDynamicVars   = array($Iusername, $invoice_no, $Ishop_name);
											$ICommonVars = array($Isite_logo, $Ishop_name, $invoice_no);
											$attachment = get_s3_url('invoices/' . $pdfGeneratePdfName, $shop_id);
											//email function
											$mailSent_invoice = $this->B2BOrdersModel->sendInvoiceHTMLEmail($Ito, $ItemplateId, $ITempVars, $IDynamicVars, $ICommonVars, $attachment);
											if ($mailSent_invoice && $fbc_user_id_order > 0) {
												$last_invoice_send_date = array('last_invoice_sent_date' => $invoice_date);
												$where_invoice_send_email_arr = array('customer_id' => $fbc_user_id_order);
												$this->B2BOrdersModel->updateData('customers_invoice', $where_invoice_send_email_arr, $last_invoice_send_date);
											}
										}
										//
									}
								}
								/*elseif($b2borderData->invoice_type==2){
									//$invoiceType="Invoice Daily";
								}elseif($b2borderData->invoice_type==3){
									//$invoiceType="Invoice weekly";
								}elseif($b2borderData->invoice_type==4){
									//$invoiceType="Invoice Monthly";
								}else{
									//$invoiceType="Invoice Per Order";
								}*/
							}
						}
					} else if ($OrderData->shipment_type == 2) {

						if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {



							$webshop_order_id = $OrderData->webshop_order_id;
							$webshop_shop_id = $OrderData->shop_id;

							$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
							$webshop_fbc_user_id = $FbcUser->fbc_user_id;

							$args['shop_id']	=	$webshop_shop_id;
							$args['fbc_user_id']	=	$webshop_fbc_user_id;

							$this->load->model('ShopProductModel');
							$this->ShopProductModel->init($args);

							$DurationData = $this->ShopProductModel->getSingleDataByID('custom_variables', array('identifier' => 'product_return_duration'), '');

							if (isset($DurationData) && $DurationData->value != '') {
								$product_return_duration = $DurationData->value;
							} else {
								$product_return_duration = 0;
							}

							$WebshopOrderData = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '');
							$webshop_increment_id = $WebshopOrderData->increment_id;


							$site_url = str_replace('admin', '', base_url());
							$order_id_webshop = base64_encode($WebshopOrderData->order_id);
							$encoded_oid = urlencode($order_id_webshop);
							$burl = base_url();


							$shop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);

							$owner_email = $WebshopOrderData->customer_email;
							$webshop_details = $this->CommonModel->get_webshop_details($webshop_shop_id);
							$shop_name = $shop_owner->org_shop_name;
							$templateId = 'fbcuser-order-tracking-completed';
							$to = $owner_email;
							$site_logo = '';
							if (isset($webshop_details)) {
								$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
							} else {
								$shop_logo = '';
							}


							if ($WebshopOrderData->checkout_method == 'guest') {
								$website_url = getWebsiteUrl($webshop_shop_id, $burl);
								$return_link = $website_url . '/guest-order/detail/' . $encoded_oid;
							} else {
								$website_url = getWebsiteUrl($webshop_shop_id, $burl);
								$return_link = $website_url . '/customer/my-orders/';
							}

							$shop_logo = get_s3_url($shop_logo, $shop_id);
							$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
									<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
								</a>';
							$username = $WebshopOrderData->customer_firstname . ' ' . $WebshopOrderData->customer_lastname;
							$TempVars = array("##OWNER##", "##ORDERID##", "##RETURNDURATION##", "##RETURNLINK##", '##WEBSHOPNAME##');
							$DynamicVars   = array($username, $webshop_increment_id, $product_return_duration, $return_link, $shop_name);
							$CommonVars = array($site_logo, $shop_name);
							if (isset($templateId)) {
								$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
								if ($emailSendStatusFlag == 1) {
									$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $webshop_increment_id, $CommonVars);
								}
							}
						}

						// invoice create and send invoice

						// invoice create and send invoice
						$b2borderData = $this->B2BOrdersModel->get_b2border_invoicing_data($invoice_order_id);
						// $b2borderData=$this->B2BOrdersModel->get_b2border_invoicing_data($order_id);
						// $b2borderData=$this->B2BOrdersModel->get_b2border_invoicing_data($OrderData->webshop_order_id);

						if ($b2borderData) { // start invoice


							$shopName = $b2borderData->org_shop_name;
							// invoice type
							if ($b2borderData->invoice_type == 1) {
								//$invoiceType="Invoice Per Order";
								// invoice no and next no
								$customVariables_invoice_prefix = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_prefix'), 'value');
								$customVariables_invoice_no = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_next_no'), 'value');
								if ($customVariables_invoice_prefix->value && $customVariables_invoice_no->value) {
									$invoiceNo = $customVariables_invoice_no->value;
									$invoice_next_no = $invoiceNo + 1;
									$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
								} elseif ($customVariables_invoice_prefix->value) {
									// $invoiceNo='2';
									$invoiceNo = '1';
									$invoice_next_no = $invoiceNo + 1;
									$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
									# code...
								} elseif ($customVariables_invoice_no->value) {
									$invoiceNo = $customVariables_invoice_no->value;
									$invoice_next_no = $invoiceNo + 1;
									$invoice_no = 'INV' . $invoiceNo;
								}
								// custom_variable table data updated
								$invoice_no = $invoice_no;
								$invoice_next_no = $invoice_next_no;

								// invoice table insert

								$invoice_order_type = '2'; //1-Webshop;  2-B2Webshop;
								$shop_id = $shop_id;
								$web_shop_id = $b2borderData->shop_id;
								$customer_name = $b2borderData->customer_name;
								$shop_webshop_name = $b2borderData->org_shop_name;
								$shop_gst_no = $b2borderData->gst_no;
								$shop_company_name = $b2borderData->company_name;
								$customer_email = $b2borderData->email;
								$invoice_subtotal = $b2borderData->subtotal;
								$invoice_tax = $b2borderData->subtotal;
								$invoice_grand_total = $b2borderData->subtotal;
								$bill_customer_first_name = $b2borderData->customer_name;
								$bill_customer_last_name = '';
								$bill_customer_id = '';
								$bill_customer_email = $b2borderData->email;
								$billing_address_line1 = $b2borderData->bill_address_line1;
								$billing_address_line2 = $b2borderData->bill_address_line2;
								$billing_city = $b2borderData->bill_city;
								$billing_state = $b2borderData->bill_state;
								$billing_country = $b2borderData->bill_country;
								$billing_pincode = $b2borderData->bill_pincode;
								$ship_address_line1 = $b2borderData->ship_address_line1;
								$ship_address_line2 = $b2borderData->ship_address_line2;
								$ship_city = $b2borderData->ship_city;
								$ship_state = $b2borderData->ship_state;
								$ship_country = $b2borderData->ship_country;
								$ship_pincode = $b2borderData->ship_pincode;
								$invoice_date = time();


								//get data by user id
								$invoice_due_date = '';
								if ($b2borderData->fbc_user_id) {
									$b2b_customers_invoiceData = $this->CommonModel->getSingleShopDataByID('b2b_customers_invoice', array('customer_id' => $b2borderData->fbc_user_id), 'invoice_type,payment_term');
									$invoice_type = $b2b_customers_invoiceData->invoice_type;
									$payment_term = $b2b_customers_invoiceData->payment_term;
									if ($invoice_date && $payment_term > 0) {
										//$daysAdd='+ '.$payment_term.'days';
										$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
										//$due_date=date('Y-m-d', strtotime($dateAdd. $daysAdd));
										$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
										$invoice_due_date = date(strtotime($due_date));
									} else {
									}
								} else {
									$payment_term = '';
									$due_date = '';
								}

								$invoice_term = $payment_term;

								$fbc_user_id_order = $b2borderData->fbc_user_id; //users and users_shop
								$email_sent_flag = '';

								// insert invoicing data
								$insertinvoicingdataitem = array(
									'invoice_no' => $invoice_no,
									'customer_first_name' => $customer_name,
									'customer_id' => $fbc_user_id_order,
									'customer_email' => $customer_email,
									'shop_id' => $web_shop_id,
									'shop_webshop_name' => $shop_webshop_name,
									'shop_company_name' => $shop_company_name,
									'shop_gst_no' => $shop_gst_no,
									'bill_customer_first_name' => $customer_name,
									// 'bill_customer_last_name'=>$customer_name,
									'bill_customer_id' => $fbc_user_id_order,
									'bill_customer_email' => $customer_email,
									'invoice_order_nos' => $invoice_order_id,
									'invoice_order_type' => $invoice_order_type,
									'invoice_subtotal' => $invoice_subtotal,
									'invoice_tax' => $invoice_tax,
									'invoice_grand_total' => $invoice_grand_total,
									'billing_address_line1' => $billing_address_line1,
									'billing_address_line2' => $billing_address_line2,
									'billing_city' => $billing_city,
									'billing_state' => $billing_state,
									'billing_country' => $billing_country,
									'billing_pincode' => $billing_pincode,
									'ship_address_line1' => $ship_address_line1,
									'ship_address_line2' => $ship_address_line2,
									'ship_city' => $ship_city,
									'ship_state' => $ship_state,
									'ship_country' => $ship_country,
									'ship_pincode' => $ship_pincode,
									'invoice_date' => $invoice_date,
									'invoice_due_date' => $invoice_due_date,
									'invoice_term' => $invoice_term,
									'created_by' => $fbc_user_id,
									'created_at' => time(),
									'ip' => $_SERVER['REMOTE_ADDR']
								);

								$invoicing_one = $this->B2BOrdersModel->insertData('invoicing', $insertinvoicingdataitem);

								if ($invoicing_one) {
									// b2b order updated
									$invoice_b2b_order = array('invoice_id' => $invoicing_one, 'invoice_date' => $invoice_date, 'invoice_flag' => 1);
									$where_b2b_order_arr = array('order_id' => $order_id);
									$this->B2BOrdersModel->updateData('b2b_orders', $where_b2b_order_arr, $invoice_b2b_order);

									//update custom_variable
									$invoice_no_update = array('value' => $invoice_next_no);
									$where_invoice_arr = array('identifier' => 'invoice_next_no');

									$this->B2BOrdersModel->updateData('custom_variables', $where_invoice_arr, $invoice_no_update);
									// send invoice email and save invoice pdf
									$pdfGeneratePdfName = $this->generatePdf($invoicing_one); // save pdf
									//send email with attachment
									if ($pdfGeneratePdfName) {
										//update invoicing
										$invoiceFileName = array('invoice_file' => $pdfGeneratePdfName);
										$where_invoice_filename_arr = array('id' => $invoicing_one);
										$this->B2BOrdersModel->updateData('invoicing', $where_invoice_filename_arr, $invoiceFileName);

										// sent email
										/*----------------Send Email to invoice with attchmnet--------------------*/
										$Ishop_owner = $this->CommonModel->getShopOwnerData($shop_id);
										$Iwebshop_details = $this->CommonModel->get_webshop_details($shop_id);
										$Ishop_name = $Ishop_owner->org_shop_name;
										$ItemplateId = 'system-invoice';
										// $Ito = 'rajesh@bcod.co.in';
										$Ito = $customer_email;
										$site_logo = '';
										if (isset($Iwebshop_details)) {
											$Ishop_logo = $this->encryption->decrypt($Iwebshop_details['site_logo']);
										} else {
											$Ishop_logo = '';
										}
										$burl = base_url();
										$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);
										$Isite_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
											<img alt="' . $Ishop_name . '" border="0" src="' . $Ishop_logo . '" style="max-width:200px" />
										</a>';
										$Iusername = $customer_name;
										$ITempVars = array();
										$IDynamicVars = array();

										$ITempVars = array("##OWNER##", "##INVOICENO##", "##WEBSHOPNAME##");
										$IDynamicVars   = array($Iusername, $invoice_no, $Ishop_name);
										$ICommonVars = array($Isite_logo, $Ishop_name, $invoice_no);
										$attachment = get_s3_url('invoices/' . $pdfGeneratePdfName, $shop_id);
										//email function
										$mailSent_invoice = $this->B2BOrdersModel->sendInvoiceHTMLEmail($Ito, $ItemplateId, $ITempVars, $IDynamicVars, $ICommonVars, $attachment);

										if ($mailSent_invoice && $fbc_user_id_order > 0) {
											$last_invoice_send_date = array('last_invoice_sent_date' => $invoice_date);
											$where_invoice_send_email_arr = array('customer_id' => $fbc_user_id_order);
											$this->B2BOrdersModel->updateData('customers_invoice', $where_invoice_send_email_arr, $last_invoice_send_date);
										}
									}
									//
								}
							}
							/*elseif($b2borderData->invoice_type==2){
								//$invoiceType="Invoice Daily";
							}elseif($b2borderData->invoice_type==3){
								//$invoiceType="Invoice weekly";
							}elseif($b2borderData->invoice_type==4){
								//$invoiceType="Invoice Monthly";
							}else{
								//$invoiceType="Invoice Per Order";
							}*/
						} // end invoice


					}
				}
			} else {

				$SplitOrderIds = $this->B2BOrdersModel->getSplitChildOrderIds($order_id);
				$count = 0;
				$tracking_not_gen_b2b = false;
				if (isset($SplitOrderIds) && count($SplitOrderIds)) {
					foreach ($SplitOrderIds as $value) {
						$trk_order_id = $value->order_id;

						$Result = $this->B2BOrdersModel->getMultiDataById('b2b_order_shipment_details', array('order_id' => $trk_order_id), 'id,tracking_id');
						if (isset($Result) && count($Result) > 0) {
							foreach ($Result as $Row) {
								if (isset($Row) && ($Row->tracking_id == '-' || $Row->tracking_id == '')) {
									$count++;
								}
							}
						} else {
							$tracking_not_gen_b2b = true;
						}
					}
				}

				if ($count > 0 || $tracking_not_gen_b2b == true) {
					$order_status = 5;
				} else {

					/*--------------------Check if webshop order is completed-------------------------------------------*/

					$order_status = $this->ApplyWebshopOrderChanges($order_id);

					/*---------------------------------------------------------------*/
				}

				$odr_update = array('status' => $order_status, 'updated_at' => time());  	// Tracking Complete
				$where_arr = array('order_id' => $order_id);
				$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $odr_update);


				if ($order_status == 6) {

					$odr_update = array('tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Completed  date
					$where_arr = array('order_id' => $order_id);
					$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $odr_update);


					$increment_id = $OrderData->increment_id;
					$web_shop_id = $OrderData->shop_id;

					if ($OrderData->shipment_type == 1) {

						$burl = base_url();

						/*----------------Send Email to shop owner--------------------*/
						$webshop_owner = $this->CommonModel->getShopOwnerData($web_shop_id);
						$shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
						$webshop_details = $this->CommonModel->get_webshop_details($shop_id);
						$owner_email = $shop_owner->email;
						$templateId = 'fbcuser-b2b-order-tracking-completed';
						$to = $webshop_owner->email;
						$shop_name = $shop_owner->org_shop_name;
						$username = $webshop_owner->owner_name;
						$site_logo = '';
						if (isset($webshop_details)) {
							$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
						}
						$burl = base_url();
						$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
						$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
								<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
							</a>';
						$TempVars = array("##OWNER##", "##ORDERID##", '##WEBSHOPNAME##');
						$DynamicVars   = array($username, $increment_id, $shop_name);
						$CommonVars = array($site_logo, $shop_name);
						if (isset($templateId)) {
							$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
							if ($emailSendStatusFlag == 1) {
								$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars);
							}
						}

						if (isset($this->CommonModel->page_access()->acc_inv_flag) && $this->CommonModel->page_access()->acc_inv_flag == 1  && $this->CommonModel->page_access()->semi_invoice == 0) {
							// invoice create and send invoice
							$b2borderData = $this->B2BOrdersModel->get_b2border_invoicing_data($order_id);
							// $customVariablesData=$this->CommonModel->get_custom_variables();
							// print_r($b2borderData);
							if ($b2borderData) {
								$shopName = $b2borderData->org_shop_name;
								// invoice type
								if ($b2borderData->invoice_type == 1) {
									//$invoiceType="Invoice Per Order";
									// invoice no and next no
									$customVariables_invoice_prefix = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_prefix'), 'value');
									$customVariables_invoice_no = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_next_no'), 'value');
									if ($customVariables_invoice_prefix->value && $customVariables_invoice_no->value) {
										$invoiceNo = $customVariables_invoice_no->value;
										$invoice_next_no = $invoiceNo + 1;
										$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
									} elseif ($customVariables_invoice_prefix->value) {
										// $invoiceNo='2';
										$invoiceNo = '1';
										$invoice_next_no = $invoiceNo + 1;
										$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
										# code...
									} elseif ($customVariables_invoice_no->value) {
										$invoiceNo = $customVariables_invoice_no->value;
										$invoice_next_no = $invoiceNo + 1;
										$invoice_no = 'INV' . $invoiceNo;
									}
									// custom_variable table data updated
									$invoice_no = $invoice_no;
									$invoice_next_no = $invoice_next_no;

									// invoice table insert

									$invoice_order_type = '2'; //1-Webshop;  2-B2Webshop;
									$shop_id = $shop_id;
									$customer_name = $b2borderData->customer_name;
									$shop_webshop_name = $b2borderData->org_shop_name;
									$shop_gst_no = $b2borderData->gst_no;
									$shop_company_name = $b2borderData->company_name;
									$customer_email = $b2borderData->email;
									$invoice_subtotal = $b2borderData->subtotal;
									$invoice_tax = $b2borderData->subtotal;
									$invoice_grand_total = $b2borderData->subtotal;
									$bill_customer_first_name = $b2borderData->customer_name;
									$bill_customer_last_name = '';
									$bill_customer_id = '';
									$bill_customer_email = $b2borderData->email;
									$billing_address_line1 = $b2borderData->bill_address_line1;
									$billing_address_line2 = $b2borderData->bill_address_line2;
									$billing_city = $b2borderData->bill_city;
									$billing_state = $b2borderData->bill_state;
									$billing_country = $b2borderData->bill_country;
									$billing_pincode = $b2borderData->bill_pincode;
									$ship_address_line1 = $b2borderData->ship_address_line1;
									$ship_address_line2 = $b2borderData->ship_address_line2;
									$ship_city = $b2borderData->ship_city;
									$ship_state = $b2borderData->ship_state;
									$ship_country = $b2borderData->ship_country;
									$ship_pincode = $b2borderData->ship_pincode;
									$invoice_date = time();


									//get data by user id
									$invoice_due_date = '';
									if ($b2borderData->fbc_user_id) {
										$b2b_customers_invoiceData = $this->CommonModel->getSingleShopDataByID('b2b_customers_invoice', array('customer_id' => $b2borderData->fbc_user_id), 'invoice_type,payment_term');
										$invoice_type = $b2b_customers_invoiceData->invoice_type;
										$payment_term = $b2b_customers_invoiceData->payment_term;
										if ($invoice_date && $payment_term > 0) {
											//$daysAdd='+ '.$payment_term.'days';
											$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
											//$due_date=date('Y-m-d', strtotime($dateAdd. $daysAdd));
											$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
											$invoice_due_date = date(strtotime($due_date));
										} else {
										}
									} else {
										$payment_term = '';
										$due_date = '';
									}

									$invoice_term = $payment_term;

									$fbc_user_id_order = $b2borderData->fbc_user_id; //users and users_shop
									$email_sent_flag = '';

									// insert invoicing data
									$insertinvoicingdataitem = array(
										'invoice_no' => $invoice_no,
										'customer_first_name' => $customer_name,
										'customer_id' => $fbc_user_id_order,
										'customer_email' => $customer_email,
										'shop_id' => $web_shop_id,
										'shop_webshop_name' => $shop_webshop_name,
										'shop_company_name' => $shop_company_name,
										'shop_gst_no' => $shop_gst_no,
										'bill_customer_first_name' => $customer_name,
										// 'bill_customer_last_name'=>$customer_name,
										'bill_customer_id' => $fbc_user_id_order,
										'bill_customer_email' => $customer_email,
										'invoice_order_nos' => $order_id,
										'invoice_order_type' => $invoice_order_type,
										'invoice_subtotal' => $invoice_subtotal,
										'invoice_tax' => $invoice_tax,
										'invoice_grand_total' => $invoice_grand_total,
										'billing_address_line1' => $billing_address_line1,
										'billing_address_line2' => $billing_address_line2,
										'billing_city' => $billing_city,
										'billing_state' => $billing_state,
										'billing_country' => $billing_country,
										'billing_pincode' => $billing_pincode,
										'ship_address_line1' => $ship_address_line1,
										'ship_address_line2' => $ship_address_line2,
										'ship_city' => $ship_city,
										'ship_state' => $ship_state,
										'ship_country' => $ship_country,
										'ship_pincode' => $ship_pincode,
										'invoice_date' => $invoice_date,
										'invoice_due_date' => $invoice_due_date,
										'invoice_term' => $invoice_term,
										'created_by' => $fbc_user_id,
										'created_at' => time(),
										'ip' => $_SERVER['REMOTE_ADDR']
									);

									$invoicing_one = $this->B2BOrdersModel->insertData('invoicing', $insertinvoicingdataitem);

									if ($invoicing_one) {
										//update custom_variable
										$invoice_no_update = array('value' => $invoice_next_no);
										$where_invoice_arr = array('identifier' => 'invoice_next_no');

										$this->B2BOrdersModel->updateData('custom_variables', $where_invoice_arr, $invoice_no_update);
										// b2b order updated
										$invoice_b2b_order = array('invoice_id' => $invoicing_one, 'invoice_date' => $invoice_date, 'invoice_flag' => 1);
										$where_b2b_order_arr = array('order_id' => $order_id);
										$this->B2BOrdersModel->updateData('b2b_orders', $where_b2b_order_arr, $invoice_b2b_order);

										// send invoice email and save invoice pdf
										$pdfGeneratePdfName = $this->generatePdf($invoicing_one); // save pdf
										//send email with attachment
										if ($pdfGeneratePdfName) {
											//update invoicing
											$invoiceFileName = array('invoice_file' => $pdfGeneratePdfName);
											$where_invoice_filename_arr = array('id' => $invoicing_one);
											$this->B2BOrdersModel->updateData('invoicing', $where_invoice_filename_arr, $invoiceFileName);

											// sent email
											/*----------------Send Email to invoice with attchmnet--------------------*/
											$Ishop_owner = $this->CommonModel->getShopOwnerData($shop_id);
											$Iwebshop_details = $this->CommonModel->get_webshop_details($shop_id);
											$Ishop_name = $Ishop_owner->org_shop_name;
											$ItemplateId = 'system-invoice';
											// $Ito = 'rajesh@bcod.co.in';
											$Ito = $customer_email;
											$site_logo = '';
											if (isset($Iwebshop_details)) {
												$Ishop_logo = $this->encryption->decrypt($Iwebshop_details['site_logo']);
											} else {
												$Ishop_logo = '';
											}
											$burl = base_url();
											$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);
											$Isite_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
													<img alt="' . $Ishop_name . '" border="0" src="' . $Ishop_logo . '" style="max-width:200px" />
												</a>';
											$Iusername = $customer_name;
											$ITempVars = array();
											$IDynamicVars = array();

											$ITempVars = array("##OWNER##", "##INVOICENO##", "##WEBSHOPNAME##");
											$IDynamicVars   = array($Iusername, $invoice_no, $Ishop_name);
											$ICommonVars = array($Isite_logo, $Ishop_name, $invoice_no);
											$attachment = get_s3_url('invoices/' . $pdfGeneratePdfName, $shop_id);
											//email function
											$mailSent_invoice = $this->B2BOrdersModel->sendInvoiceHTMLEmail($Ito, $ItemplateId, $ITempVars, $IDynamicVars, $ICommonVars, $attachment);
											if ($mailSent_invoice && $fbc_user_id_order > 0) {
												$last_invoice_send_date = array('last_invoice_sent_date' => $invoice_date);
												$where_invoice_send_email_arr = array('customer_id' => $fbc_user_id_order);
												$this->B2BOrdersModel->updateData('customers_invoice', $where_invoice_send_email_arr, $last_invoice_send_date);
											}
										}
										//
									}
								}
								/*elseif($b2borderData->invoice_type==2){
										//$invoiceType="Invoice Daily";
									}elseif($b2borderData->invoice_type==3){
										//$invoiceType="Invoice weekly";
									}elseif($b2borderData->invoice_type==4){
										//$invoiceType="Invoice Monthly";
									}else{
										//$invoiceType="Invoice Per Order";
									}*/
							}
						} // check invoice option
					} else if ($OrderData->shipment_type == 2) {
						// b2b invoice
						if (isset($this->CommonModel->page_access()->acc_inv_flag) && $this->CommonModel->page_access()->acc_inv_flag == 1 && $this->CommonModel->page_access()->semi_invoice == 0) {
							// invoice create and send invoice
							$b2borderData = $this->B2BOrdersModel->get_b2border_invoicing_data($order_id);
							if ($b2borderData) {
								$shopName = $b2borderData->org_shop_name;
								// invoice type
								if ($b2borderData->invoice_type == 1) {
									$customVariables_invoice_prefix = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_prefix'), 'value');
									$customVariables_invoice_no = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_next_no'), 'value');
									if ($customVariables_invoice_prefix->value && $customVariables_invoice_no->value) {
										$invoiceNo = $customVariables_invoice_no->value;
										$invoice_next_no = $invoiceNo + 1;
										$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
									} elseif ($customVariables_invoice_prefix->value) {
										// $invoiceNo='2';
										$invoiceNo = '1';
										$invoice_next_no = $invoiceNo + 1;
										$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
										# code...
									} elseif ($customVariables_invoice_no->value) {
										$invoiceNo = $customVariables_invoice_no->value;
										$invoice_next_no = $invoiceNo + 1;
										$invoice_no = 'INV' . $invoiceNo;
									}
									// custom_variable table data updated
									$invoice_no = $invoice_no;
									$invoice_next_no = $invoice_next_no;
									// invoice table insert
									$invoice_order_type = '2'; //1-Webshop;  2-B2Webshop;
									$shop_id = $shop_id;
									$web_shop_id = $b2borderData->shop_id;
									$customer_name = $b2borderData->customer_name;
									$shop_webshop_name = $b2borderData->org_shop_name;
									$shop_gst_no = $b2borderData->gst_no;
									$shop_company_name = $b2borderData->company_name;
									$customer_email = $b2borderData->email;
									$invoice_subtotal = $b2borderData->subtotal;
									$invoice_tax = $b2borderData->subtotal;
									$invoice_grand_total = $b2borderData->subtotal;
									$bill_customer_first_name = $b2borderData->customer_name;
									$bill_customer_last_name = '';
									$bill_customer_id = '';
									$bill_customer_email = $b2borderData->email;
									$billing_address_line1 = $b2borderData->bill_address_line1;
									$billing_address_line2 = $b2borderData->bill_address_line2;
									$billing_city = $b2borderData->bill_city;
									$billing_state = $b2borderData->bill_state;
									$billing_country = $b2borderData->bill_country;
									$billing_pincode = $b2borderData->bill_pincode;
									$ship_address_line1 = $b2borderData->ship_address_line1;
									$ship_address_line2 = $b2borderData->ship_address_line2;
									$ship_city = $b2borderData->ship_city;
									$ship_state = $b2borderData->ship_state;
									$ship_country = $b2borderData->ship_country;
									$ship_pincode = $b2borderData->ship_pincode;
									$invoice_date = time();
									//get data by user id
									$invoice_due_date = '';
									if ($b2borderData->fbc_user_id) {
										$b2b_customers_invoiceData = $this->CommonModel->getSingleShopDataByID('b2b_customers_invoice', array('customer_id' => $b2borderData->fbc_user_id), 'invoice_type,payment_term');
										$invoice_type = $b2b_customers_invoiceData->invoice_type;
										$payment_term = $b2b_customers_invoiceData->payment_term;
										if ($invoice_date && $payment_term > 0) {
											//$daysAdd='+ '.$payment_term.'days';
											$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
											//$due_date=date('Y-m-d', strtotime($dateAdd. $daysAdd));
											$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
											$invoice_due_date = date(strtotime($due_date));
										} else {
										}
									} else {
										$payment_term = '';
										$due_date = '';
									}

									$invoice_term = $payment_term;
									$fbc_user_id_order = $b2borderData->fbc_user_id; //users and users_shop
									$email_sent_flag = '';
									// insert invoicing data
									$insertinvoicingdataitem = array(
										'invoice_no' => $invoice_no,
										'customer_first_name' => $customer_name,
										'customer_id' => $fbc_user_id_order,
										'customer_email' => $customer_email,
										'shop_id' => $web_shop_id,
										'shop_webshop_name' => $shop_webshop_name,
										'shop_company_name' => $shop_company_name,
										'shop_gst_no' => $shop_gst_no,
										'bill_customer_first_name' => $customer_name,
										'bill_customer_id' => $fbc_user_id_order,
										'bill_customer_email' => $customer_email,
										'invoice_order_nos' => $order_id,
										'invoice_order_type' => $invoice_order_type,
										'invoice_subtotal' => $invoice_subtotal,
										'invoice_tax' => $invoice_tax,
										'invoice_grand_total' => $invoice_grand_total,
										'billing_address_line1' => $billing_address_line1,
										'billing_address_line2' => $billing_address_line2,
										'billing_city' => $billing_city,
										'billing_state' => $billing_state,
										'billing_country' => $billing_country,
										'billing_pincode' => $billing_pincode,
										'ship_address_line1' => $ship_address_line1,
										'ship_address_line2' => $ship_address_line2,
										'ship_city' => $ship_city,
										'ship_state' => $ship_state,
										'ship_country' => $ship_country,
										'ship_pincode' => $ship_pincode,
										'invoice_date' => $invoice_date,
										'invoice_due_date' => $invoice_due_date,
										'invoice_term' => $invoice_term,
										'created_by' => $fbc_user_id,
										'created_at' => time(),
										'ip' => $_SERVER['REMOTE_ADDR']
									);
									$invoicing_one = $this->B2BOrdersModel->insertData('invoicing', $insertinvoicingdataitem);
									if ($invoicing_one) {
										//update custom_variable
										$invoice_no_update = array('value' => $invoice_next_no);
										$where_invoice_arr = array('identifier' => 'invoice_next_no');

										$this->B2BOrdersModel->updateData('custom_variables', $where_invoice_arr, $invoice_no_update);
										// b2b order updated
										$invoice_b2b_order = array('invoice_id' => $invoicing_one, 'invoice_date' => $invoice_date, 'invoice_flag' => 1);
										$where_b2b_order_arr = array('order_id' => $order_id);
										$this->B2BOrdersModel->updateData('b2b_orders', $where_b2b_order_arr, $invoice_b2b_order);

										// send invoice email and save invoice pdf
										$pdfGeneratePdfName = $this->generatePdf($invoicing_one); // save pdf
										//send email with attachment
										if ($pdfGeneratePdfName) {
											//update invoicing
											$invoiceFileName = array('invoice_file' => $pdfGeneratePdfName);
											$where_invoice_filename_arr = array('id' => $invoicing_one);
											$this->B2BOrdersModel->updateData('invoicing', $where_invoice_filename_arr, $invoiceFileName);

											// sent email
											/*----------------Send Email to invoice with attchmnet--------------------*/
											$Ishop_owner = $this->CommonModel->getShopOwnerData($shop_id);
											$Iwebshop_details = $this->CommonModel->get_webshop_details($shop_id);
											$Ishop_name = $Ishop_owner->org_shop_name;
											$ItemplateId = 'system-invoice';
											// $Ito = 'rajesh@bcod.co.in';
											$Ito = $customer_email;
											$site_logo = '';
											if (isset($Iwebshop_details)) {
												$Ishop_logo = $this->encryption->decrypt($Iwebshop_details['site_logo']);
											} else {
												$Ishop_logo = '';
											}
											$burl = base_url();
											$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);
											$Isite_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
														<img alt="' . $Ishop_name . '" border="0" src="' . $Ishop_logo . '" style="max-width:200px" />
													</a>';
											$Iusername = $customer_name;
											$ITempVars = array();
											$IDynamicVars = array();

											$ITempVars = array("##OWNER##", "##INVOICENO##", "##WEBSHOPNAME##");
											$IDynamicVars   = array($Iusername, $invoice_no, $Ishop_name);
											$ICommonVars = array($Isite_logo, $Ishop_name, $invoice_no);
											$attachment = get_s3_url('invoices/' . $pdfGeneratePdfName, $shop_id);
											//email function
											$mailSent_invoice = $this->B2BOrdersModel->sendInvoiceHTMLEmail($Ito, $ItemplateId, $ITempVars, $IDynamicVars, $ICommonVars, $attachment);
											if ($mailSent_invoice && $fbc_user_id_order > 0) {
												$last_invoice_send_date = array('last_invoice_sent_date' => $invoice_date);
												$where_invoice_send_email_arr = array('customer_id' => $fbc_user_id_order);
												$this->B2BOrdersModel->updateData('customers_invoice', $where_invoice_send_email_arr, $last_invoice_send_date);
											}
										}
									}
								}
							}
						} // check invoice option
					}
				}
			}

			$arrResponse  = array('status' => 200, 'message' => 'Tracking data saved successfully.');
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
	}

	public function printdetails($order_id = '')
	{
		$data['PageTitle'] = 'B2B - Print Details';
		$order_id = $this->uri->segment(4);
		if (isset($order_id) && $order_id > 0) {
			$data['order_id'] = $order_id;
			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			$show_cust_addr_check = $this->B2BOrdersModel->getSingleDataByID('custom_variables', array('identifier' => 'pickinglist_show_cust_addr'), '');
			$data['show_cust_addr'] = $show_cust_addr_check->value;

			if (empty($OrderData)) {
				redirect('/b2b/orders');
			}

			if ($OrderData->shipment_type == 2) {
				if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {
					$webshop_order_id = $OrderData->webshop_order_id;
					$webshop_shop_id = $OrderData->shop_id;

					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
					$webshop_fbc_user_id = $FbcUser->fbc_user_id;

					$args['shop_id']	=	$webshop_shop_id;
					$args['fbc_user_id']	=	$webshop_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);

					if (isset($show_cust_addr_check) && $show_cust_addr_check->value == 'yes') {
						$data['ShippingAddress'] = $ShippingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), '');
						$data['BillingAddress'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 1), '');

						$data['FormattedAddress_ship'] = $this->ShopProductModel->getFormattedAddress($ShippingAddress);
					}
				}
			}


			$data['OrderItems'] = $OrderItems = $this->B2BOrdersModel->getOrderItems($order_id);

			$this->load->view('b2b/order/printdetails', $data);
		} else {
			redirect('/b2b/orders');
		}
	}

	function shippedorderprint()
	{
		$current_tab = $this->uri->segment(2);
		$order_id = $this->uri->segment(4);
		if (isset($order_id) && $order_id > 0) {
			$data['PageTitle'] = 'B2B - Shipped Order Print';
			$data['side_menu'] = 'b2b';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';

			$shop_id		=	$this->session->userdata('ShopID');

			//echo $this->B2BOrdersModel->generate_new_transaction_id().'=====';
			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');

			if (empty($OrderData)) {
				redirect('/b2b/orders');
			}

			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);

			if ($OrderData->shipment_type == 2) {
				if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {
					$webshop_order_id = $OrderData->webshop_order_id;
					$webshop_shop_id = $OrderData->shop_id;

					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
					$webshop_fbc_user_id = $FbcUser->fbc_user_id;

					$args['shop_id']	=	$webshop_shop_id;
					$args['fbc_user_id']	=	$webshop_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);

					$data['ShippingAddress'] = $ShippingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), '');
					$data['BillingAddress'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 1), '');

					$data['FormattedAddress'] = $this->ShopProductModel->getFormattedAddress($ShippingAddress);
				}
			}




			if ($current_tab == 'shipped-order') {
				$data['SplitOrderIds'] = $this->B2BOrdersModel->getSplitChildOrderIds($OrderData->order_id);
				$data['ShippedItem'] = $ShippedItem = $this->B2BOrdersModel->getShippedOrderItems($OrderData->order_id, $OrderData->is_split);
				$this->load->view('b2b/order/shipped-order-print', $data);
			} else {
				redirect('/b2b/orders');
			}
		} else {
			redirect('/b2b/orders');
		}
	}

	function printshipmentlabel()
	{

		$order_id = $_POST['order_id'];
		if (isset($order_id) && $order_id > 0) {
			$_data['temp_box_weight'] = $box_weight = $_POST['box_weight'];
			$_data['temp_order_id'] = $order_id = $_POST['order_id'];
			$_data['temp_additional_message'] = $additional_message = $_POST['additional_message'];

			$this->session->set_userdata($_data);

			$arrResponse  = array('status' => 200, 'message' => '', 'order_id' => $order_id);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong1082');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function orderprintlabel()
	{
		$order_id = $this->uri->segment(4);
		if (isset($order_id) && $order_id > 0) {
			$shop_id		=	$this->session->userdata('ShopID');
			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			if (empty($OrderData)) {
				redirect('/b2b/orders');
			}

			$data['temp_box_weight'] = $this->session->userdata('temp_box_weight');
			$data['temp_order_id'] = $this->session->userdata('temp_order_id');
			$data['temp_additional_message'] = $this->session->userdata('temp_additional_message');

			$main_order_id = $order_id;
			if ($OrderData->parent_id != '' && $OrderData->parent_id > 0) {

				$main_order_id = $OrderData->main_parent_id;
			}

			$MainOrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $main_order_id), '');

			if ($MainOrderData->shipment_type == 2) {

				if (isset($MainOrderData->webshop_order_id)  && $MainOrderData->webshop_order_id > 0) {
					$webshop_order_id = $MainOrderData->webshop_order_id;
					$webshop_shop_id = $MainOrderData->shop_id;

					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
					$webshop_fbc_user_id = $FbcUser->fbc_user_id;

					$args['shop_id']	=	$webshop_shop_id;
					$args['fbc_user_id']	=	$webshop_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);

					$data['ShippingAddress'] = $ShippingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), '');
					$data['BillingAddress'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 1), '');

					$data['FormattedAddress'] = $this->ShopProductModel->getFormattedAddress($ShippingAddress);
				}
			}


			$data['OrderItems'] = $OrderItems = $this->B2BOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);
			$this->load->view('b2b/order/order-print-label', $data);
		} else {
			redirect('/b2b/orders');
		}
	}

	function openScanQtyPopup()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id'])) {

			$data['order_id'] = $_POST['order_id'];
			$data['item_id'] = $item_id = $_POST['item_id'];

			$data['OrderItemData'] = $OrderItemData = $this->B2BOrdersModel->getSingleDataByID('b2b_order_items', array('item_id' => $item_id), '');

			$View = $this->load->view('b2b/order/oi-scan-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function scanitemwithqty()
	{
		if (isset($_POST['order_id']) && isset($_POST['item_id']) && isset($_POST['qty'])) {
			$order_id = $_POST['order_id'];
			$item_id = $_POST['item_id'];
			$current_tab = $_POST['current_tab'];
			$qty = $_POST['qty'];

			$ItemExist = $this->B2BOrdersModel->checkOrderItemsExistByItemId($order_id, $item_id);
			if (isset($ItemExist) && $ItemExist->item_id != '') {

				$item_id = $ItemExist->item_id;
				$qty_ordered = $ItemExist->qty_ordered;
				$old_qty_scanned = $ItemExist->qty_scanned;

				$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,is_split');

				if ($current_tab == 'split-order') {
					$main_oi_qty = $this->B2BOrdersModel->getMainOrderItemQty($OrderData->main_parent_id, $ItemExist->product_id);
					$main_qty_ordered = $main_oi_qty;
				} else {

					$main_qty_ordered = $ItemExist->qty_ordered;
				}


				if (($current_tab == 'order') && ($old_qty_scanned == $qty_ordered)) {
					$arrResponse  = array('status' => 400, 'message' => 'Item already scanned all quantity.');
					echo json_encode($arrResponse);
					exit;
				}
				/*
				else if(($current_tab=='split-order') && ($old_qty_scanned==$qty_ordered)){
					$arrResponse  = array('status' =>400 ,'message'=>'Item already scanned all quantity.');
					echo json_encode($arrResponse);exit;

				}
				*/ else {

					if ($old_qty_scanned <= $main_qty_ordered) {
						$this->B2BOrdersModel->incrementOrderItemQtyScannedByQty($item_id, $qty);  //increament qty_scanned
					}

					$item_class = $this->B2BOrdersModel->getOrderItemRowClass($item_id);


					$OrderDataNew = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), 'order_id,system_generated_split_order,main_parent_id,is_split');
					if ($OrderDataNew->is_split == 0) {
						$QtyScanItem = $this->B2BOrdersModel->getQtyFullyScannedOrderItems($order_id);
						$AllItems = $this->B2BOrdersModel->getOrderItems($order_id);
						if (count($QtyScanItem) == count($AllItems)) {
							$odr_update = array('system_generated_split_order' => 0, 'updated_at' => time());
							$where_arr = array('order_id' => $order_id);
							$this->B2BOrdersModel->updateData('b2b_orders', $where_arr, $odr_update);
						}
					}


					$arrResponse  = array('status' => 200, 'message' => 'Item scanned successfully.', 'item_id' => $item_id, 'item_class' => $item_class);
					echo json_encode($arrResponse);
					exit;
				}
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Item does not belongs to this order!');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong!');
			echo json_encode($arrResponse);
			exit;
		}
	}


	function ApplyWebshopOrderChanges($order_id)
	{

		$shop_id		=	$this->session->userdata('ShopID');

		$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');

		$temp_order_status = '6';

		if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {
			$webshop_order_id = $OrderData->webshop_order_id;
			$webshop_shop_id = $OrderData->shop_id;

			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
			$webshop_fbc_user_id = $FbcUser->fbc_user_id;

			$args['shop_id']	=	$webshop_shop_id;
			$args['fbc_user_id']	=	$webshop_fbc_user_id;

			$this->load->model('ShopProductModel');
			$this->ShopProductModel->init($args);

			$WebshopOrder = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '');

			$WebshopOrderOtherShopItems = $this->ShopProductModel->getMultiDataById('sales_order_items', array('order_id' => $webshop_order_id, 'shop_id <> ' => $shop_id), '');

			if (count($WebshopOrderOtherShopItems) > 0) {

				if ($WebshopOrder->is_split == 0) {


					$op_ct = 0;
					$Result = $this->ShopProductModel->getMultiDataById('sales_order_shipment_details', array('order_id' => $webshop_order_id), 'id,tracking_id');
					if (isset($Result) && Count($Result) > 0) {
						foreach ($Result as $Row) {
							if (isset($Row) && ($Row->tracking_id == '-' || $Row->tracking_id == '')) {
								$op_ct++;
							}
						}
					}

					if ($op_ct > 0 || count($Result) == 0) {
						$temp_order_status = 5;

						/*----------------update webshop order status-----------------------------------------*/

						$odr_update = array('status' => $temp_order_status, 'updated_at' => time());  	// Tracking Completed  date
						$where_arr = array('order_id' => $webshop_order_id);
						$this->ShopProductModel->updateData('sales_order', $where_arr, $odr_update);
					} else {
						$temp_order_status = 6;

						/*----------------update webshop order status-----------------------------------------*/

						$odr_update = array('status' => $temp_order_status, 'tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Completed  date
						$where_arr = array('order_id' => $webshop_order_id);
						$this->ShopProductModel->updateData('sales_order', $where_arr, $odr_update);

						$this->sendnotifytob2bshopcustomer($OrderData->order_id);
					}
				} else {


					$WSplitOrderIds = $this->ShopProductModel->getSplitChildOrderIdsForWebshop($webshop_order_id);
					$op_ct = 0;
					$ship_count = 0;
					$tracking_not_gen = false;
					if (isset($WSplitOrderIds) && count($WSplitOrderIds)) {
						foreach ($WSplitOrderIds as $value) {

							$Result = $this->ShopProductModel->getMultiDataById('sales_order_shipment_details', array('order_id' => $value->order_id), 'id,tracking_id');
							if (isset($Result) && Count($Result) > 0) {
								foreach ($Result as $Row) {
									if (isset($Row) && ($Row->tracking_id == '-' || $Row->tracking_id == '')) {
										$op_ct++;
									}
								}
							} else {
								$tracking_not_gen = true;
							}
						}
					}

					if ($op_ct > 0 || $tracking_not_gen == true) {

						$temp_order_status = 5;

						/*----------------update webshop order status-----------------------------------------*/

						$odr_update = array('status' => $temp_order_status, 'updated_at' => time());  	// Tracking Completed  date
						$where_arr = array('order_id' => $webshop_order_id);
						$this->ShopProductModel->updateData('sales_order', $where_arr, $odr_update);
					} else {
						$temp_order_status = 6;

						/*----------------update webshop order status-----------------------------------------*/

						$odr_update = array('status' => $temp_order_status, 'tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Completed  date
						$where_arr = array('order_id' => $webshop_order_id);
						$this->ShopProductModel->updateData('sales_order', $where_arr, $odr_update);

						$this->sendnotifytob2bshopcustomer($OrderData->order_id);
					}
				}
			} else {


				$temp_order_status = 6;


				/*----------------update webshop order status-----------------------------------------*/

				$odr_update = array('status' => $temp_order_status, 'tracking_complete_date' => time(), 'updated_at' => time());  	// Tracking Completed  date
				$where_arr = array('order_id' => $webshop_order_id);
				$this->ShopProductModel->updateData('sales_order', $where_arr, $odr_update);

				$this->sendnotifytob2bshopcustomer($OrderData->order_id);
			}
		} else {
			$temp_order_status = 6;
		}


		return $temp_order_status;
	}


	function sendnotifytob2bshopcustomer($b2b_order_id)
	{
		$shop_id		=	$this->session->userdata('ShopID');

		$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $b2b_order_id), '');

		if ($OrderData->shipment_type == 2) {
			if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {
				$webshop_order_id = $OrderData->webshop_order_id;
				$webshop_shop_id = $OrderData->shop_id;

				$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
				$webshop_fbc_user_id = $FbcUser->fbc_user_id;

				$args['shop_id']	=	$webshop_shop_id;
				$args['fbc_user_id']	=	$webshop_fbc_user_id;

				$this->load->model('ShopProductModel');
				$this->ShopProductModel->init($args);

				$DurationData = $this->ShopProductModel->getSingleDataByID('custom_variables', array('identifier' => 'product_return_duration'), '');

				if (isset($DurationData) && $DurationData->value != '') {
					$product_return_duration = $DurationData->value;
				} else {
					$product_return_duration = 0;
				}

				$WebshopOrderData = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '');
				// billing customer name
				$webshopBillData = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 1), '');
				if (isset($webshopBillData) && !empty($webshopBillData)) {
					$billUserName = $webshopBillData->first_name . ' ' . $webshopBillData->last_name;
				} else {
					$billUserName = $WebshopOrderData->customer_firstname . ' ' . $WebshopOrderData->customer_lastname;
				}

				$webshop_increment_id = $WebshopOrderData->increment_id;


				$site_url = str_replace('admin', '', base_url());
				$order_id = base64_encode($WebshopOrderData->order_id);
				$encoded_oid = urlencode($order_id);
				$burl = base_url();


				$shop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);

				$owner_email = $WebshopOrderData->customer_email;
				$webshop_details = $this->CommonModel->get_webshop_details($webshop_shop_id);
				$shop_name = $shop_owner->org_shop_name;
				$templateId = 'fbcuser-order-tracking-completed';
				$to = $owner_email;
				$site_logo = '';
				if (isset($webshop_details)) {
					$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				} else {
					$shop_logo = '';
				}


				if ($WebshopOrderData->checkout_method == 'guest') {
					$website_url = getWebsiteUrl($webshop_shop_id, $burl);
					$return_link = $website_url . '/guest-order/detail/' . $encoded_oid;
				} else {
					$website_url = getWebsiteUrl($webshop_shop_id, $burl);
					$return_link = $website_url . '/customer/my-orders/';
				}

				$shop_logo = get_s3_url($shop_logo, $shop_id);
				$site_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
						<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
					</a>';
				// $username = $WebshopOrderData->customer_firstname.' '.$WebshopOrderData->customer_lastname;
				$username = $billUserName;
				$TempVars = array("##OWNER##", "##ORDERID##", "##RETURNDURATION##", "##RETURNLINK##", '##WEBSHOPNAME##');
				$DynamicVars   = array($username, $webshop_increment_id, $product_return_duration, $return_link, $shop_name);
				$CommonVars = array($site_logo, $shop_name);
				if (isset($templateId)) {
					$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $shop_id);
					if ($emailSendStatusFlag == 1) {
						$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $webshop_increment_id, $CommonVars);
					}
				}

				// start invoice
				// invoice_generate_webshop
				// $OrderData->webshop_order_id
				// check shop_flag!=2 send invoice
				// access
				if (isset($this->ShopProductModel->page_access_shop()->acc_inv_flag) && $this->ShopProductModel->page_access_shop()->acc_inv_flag == 1 && $this->ShopProductModel->page_access_shop()->semi_invoice == 0) {
					if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {
						$webshop_order_id = $OrderData->webshop_order_id;
						$webshop_shop_id = $OrderData->shop_id;
						$parent_id = $OrderData->main_parent_id; //b2b order table supplier
						$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
						$webshop_fbc_user_id = $FbcUser->fbc_user_id;
						// generate and send invoice
						if (isset($FbcUser->shop_flag) && $FbcUser->shop_flag != 2) {
							$invoice_gen = $this->invoiceGenerate_webshop($webshop_order_id, $b2b_order_id, $parent_id, $webshop_shop_id, $webshop_fbc_user_id);
						}
					}
				}
				//end invoice
			}
		}
	}

	//invoice
	function generatePdf($invoiceID)
	{

		// pdf  data
		/*$this->load->library('Pdf');
		$data['invoice']=array('WebshopName' => 'Rajesh','CompanyName' => 'Z-Wear Distribution India Private Limited');
		$htmldata =$this->load->view('invoice/b2b/invoice_format',$data,true);
		$this->pdf->generatePdf($htmldata);*/

		// custom variable data
		// $invoice_no_update=array('value'=>$invoice_next_no);
		// $where_invoice_arr=array('identifier'=>'invoice_next_no');
		// $this->B2BOrdersModel->updateData('custom_variables',$where_invoice_arr,$invoice_no_update);
		//print_r($b2borderData);

		$invoice_id = $invoiceID;
		//echo $invoice_id;exit();
		// $invoice_id="4";
		// $order_id="58";
		$data['invoicedata'] = $this->B2BOrdersModel->get_invoicedata_by_id($invoice_id);
		//print_r($data['invoicedata']);exit();
		// print_r($data['invoicedata']);
		/*$data['b2borderData']=$this->B2BOrdersModel->get_b2border_invoicing_data($order_id);
		$data['b2borderData_item']=$this->B2BOrdersModel->getOrderItems($order_id);
		$data['b2bItemTax']=$this->B2BOrdersModel->get_order_item_tax_percent($order_id);*/

		// Shop Data
		$data['custom_variables'] = $this->CommonModel->get_custom_variables();

		//getSingleDataByID

		$data['shop_id'] = $this->session->userdata('ShopID');
		// $data['shop_id'] = $shop_id = $this->session->userdata('ShopID');
		$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($data['shop_id']);
		$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		if ($data['user_details']->parent_id == 0) {
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
		} else {
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
		}

		// dom pdf
		$this->load->library('Pdf_dom');
		//$this->load->view('invoice/b2b/invoice_format',$data);
		$htmldata = $this->load->view('invoice/b2b/invoice_format', $data, true);
		// $invoiceFileName=$this->pdf_dom->create($htmldata,$invoiceID);
		$invoiceFileName = $this->pdf_dom->createbyshop($htmldata, $invoiceID, $data['shop_id']);
		return $invoiceFileName;
	}

	// webshop invoice generate
	function invoiceGenerate_webshop($order_id, $b2b_order_id, $parent_id, $webshop_shop_id, $webshop_fbc_user_id)
	{
		// variable define
		$order_id = $order_id;
		$b2b_order_id = $b2b_order_id;
		$parent_id = $parent_id;
		//$webshop_shop_id=$_POST['webshop_shop_id'];
		$args['shop_id']	=	$webshop_shop_id;
		$args['fbc_user_id']	=	$webshop_fbc_user_id;
		$this->load->model('ShopProductModel');
		$this->ShopProductModel->init($args);
		$orderData = $this->ShopProductModel->get_webshop_invoicing_b2b_generate_data($order_id);
		$orderUserType = $orderData->checkout_method; //guest,register,login

		$ShippingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $orderData->order_id, 'address_type' => 2), '');
		$BillingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $orderData->order_id, 'address_type' => 1), '');
		$customVariables_invoice_prefix = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_prefix'), 'value');
		$customVariables_invoice_no = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_next_no'), 'value');
		if ($customVariables_invoice_prefix->value && $customVariables_invoice_no->value) {
			$invoiceNo = $customVariables_invoice_no->value;
			$invoice_next_no = $invoiceNo + 1;
			$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
		} elseif ($customVariables_invoice_prefix->value) {
			// $invoiceNo='2';
			$invoiceNo = '1';
			$invoice_next_no = $invoiceNo + 1;
			$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
			# code...
		} elseif ($customVariables_invoice_no->value) {
			$invoiceNo = $customVariables_invoice_no->value;
			$invoice_next_no = $invoiceNo + 1;
			$invoice_no = 'INV' . $invoiceNo;
		}
		// custom_variable table data updated
		$invoice_no = $invoice_no;
		$invoice_next_no = $invoice_next_no;

		// invoice table insert
		$invoice_order_type = '1'; //1-Webshop;  2-B2Webshop;
		$shop_id = '';
		$customer_name = $orderData->customer_name;
		$customer_first_name = $orderData->customer_firstname;
		$customer_last_name = $orderData->customer_lastname;
		$customer_id = $orderData->customer_id;
		$customer_is_guest = $orderData->customer_is_guest;
		$invoice_self = $orderData->invoice_self;
		$shop_webshop_name = '';
		$shop_gst_no = ''; //$orderData->gst_no
		$shop_company_name = ''; //$orderData->company_name

		$invoice_subtotal = '';
		$invoice_tax = '';
		$invoice_grand_total = '';
		// bill invoice
		$bill_customer_first_name = $BillingAddress->first_name;
		$bill_customer_last_name = $BillingAddress->last_name;
		$bill_customer_id = '';
		$bill_customer_email = '';
		// $bill_customer_email=$orderData->email;
		$billing_address_line1 = $BillingAddress->address_line1; //$orderData->bill_address_line1
		$billing_address_line2 = $BillingAddress->address_line2; //$orderData->bill_address_line2
		$billing_city = $BillingAddress->city; //$orderData->bill_city
		$billing_state = $BillingAddress->state; //$orderData->bill_state
		$billing_country = $BillingAddress->country; //$orderData->bill_country
		$billing_pincode = $BillingAddress->pincode; //$orderData->bill_pincode

		$ship_address_line1 = $ShippingAddress->address_line1; //$orderData->ship_address_line1
		$ship_address_line2 = $ShippingAddress->address_line2; //$orderData->ship_address_line2
		$ship_city = $ShippingAddress->city; //$orderData->ship_city
		$ship_state = $ShippingAddress->state; //$orderData->ship_state
		$ship_country = $ShippingAddress->country; //$orderData->ship_country
		$ship_pincode = $ShippingAddress->pincode; //$orderData->ship_pincode
		$invoice_date = time();

		//get data by user id
		$invoice_due_date = '';
		//$orderUserType; exit();
		$invoice_type = 0;
		$invoice_generate = 0;
		$emailSend = 0;
		// new added data
		// customer if guest
		$customer_company = '';
		$customer_gst_no = '';
		$shipping_charges = $orderData->shipping_amount;
		$voucher_amount = $orderData->voucher_amount;
		$payment_charges = $orderData->payment_final_charge;

		$customer_email = $orderData->customer_email;
		$bill_customer_email = $orderData->customer_email;
		$bill_customer_id = $orderData->customer_id;

		// updated 10-08-2021
		if ($orderData->invoice_self == 1) {
			if ($orderUserType == 'guest') {
				$payment_term = '';
				$invoice_due_date = $invoice_date;
				$emailSend = 1;
				$invoice_generate = 1;
			} else {
				$customers_invoiceData = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_id), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				if (isset($customers_invoiceData) && !empty($customers_invoiceData)) {
					$invoice_type = $customers_invoiceData->invoice_type;
					$payment_term = $customers_invoiceData->payment_term;
					$invoice_to_type = $customers_invoiceData->invoice_to_type;
					if ($customers_invoiceData->invoice_type == 1) {
						$invoice_generate = 1;
						$emailSend = 1;
					}
				} else {
					$invoice_generate = 1;
					$emailSend = 1;
					$payment_term = '';
					$invoice_due_date = $invoice_date;
					// alternate email id

				}
				if ($invoice_date && $payment_term > 0) {
					$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
					$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
					$invoice_due_date = date(strtotime($due_date));
				} else {
					$payment_term = '';
					$invoice_due_date = $invoice_date;
				}

				// bill customer data
				$bill_customer_company_name_gst = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $bill_customer_id), 'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
				if (isset($bill_customer_company_name_gst)) {
					$customer_name = $bill_customer_company_name_gst->customer_name;
					$customer_company = $bill_customer_company_name_gst->company_name;
					$customer_gst_no = $bill_customer_company_name_gst->gst_no;
				}
			}
		} else {
			// customer type
			if ($orderUserType == 'guest') {
				$invoice_generate = 1;
				$payment_term = '';
				$invoice_due_date = $invoice_date;
				$customVariables_alternative_email_id = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
				if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
					$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
					$customer_email_data_shop = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
					$bill_customer_email = $customer_email_data_shop->email_id;
					$bill_customer_id = $customer_email_alternate_shop;
					// $emailSend=1;
				}
			} else {
				$customers_invoiceData = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_id), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				// print_r($customers_invoiceData);exit();
				// if(isset($customers_invoiceData) && $customers_invoiceData->invoice_type==1){
				if (isset($customers_invoiceData) && !empty($customers_invoiceData)) {
					$invoice_type = $customers_invoiceData->invoice_type;
					$payment_term = $customers_invoiceData->payment_term;
					$customer_email_alternate = '';
					$invoice_to_type = $customers_invoiceData->invoice_to_type;
					if ($invoice_to_type != 0) {
						$customer_email_alternate = $customers_invoiceData->alternative_email_id;
					}
					if ($customers_invoiceData->invoice_type == 1) {
						$invoice_generate = 1;
						$emailSend = 1;
					}
				} else {
					$invoice_generate = 1;
					$payment_term = 0;
					// alternate
					$customVariables_alternative_email_id = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
					if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
						$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
						$customer_email_data_shop = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
						$bill_customer_email = $customer_email_data_shop->email_id;
						$bill_customer_id = $customer_email_alternate_shop;
						// $emailSend=1;
					}
					// end alternate
				}

				if ($invoice_date && $payment_term > 0) {
					$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
					$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
					$invoice_due_date = date(strtotime($due_date));
				} else {
					$payment_term = '';
					$invoice_due_date = $invoice_date;
				}
				if (isset($invoice_to_type) && $invoice_to_type == 1) { //invoice type check
					if ($customer_email_alternate) {
						//$customer_email_alternate=0;
						$customer_email_data = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate), 'email_id');
						if (isset($customer_email_data) && !empty($customer_email_data)) {
							// new add
							$bill_customer_email = $customer_email_data->email_id;
							$bill_customer_id = $customer_email_alternate;
							$customers_invoiceData_alt = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
							if (isset($customers_invoiceData_alt) && !empty($customers_invoiceData_alt)) {
								$invoice_type = $customers_invoiceData_alt->invoice_type;
								$payment_term = $customers_invoiceData_alt->payment_term;
								$invoice_to_type = $customers_invoiceData_alt->invoice_to_type;
								if ($customers_invoiceData_alt->invoice_type == 1) {
									$invoice_generate = 1;
									$emailSend = 1;
								}
							} else {
								$invoice_generate = 1;
								$payment_term = 0;
								$customer_email_alternate = '';
							}


							// $emailSend=1;
						} else {
							$customVariables_alternative_email_id = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
							if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
								$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
								$customer_email_data_shop = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
								$bill_customer_email = $customer_email_data_shop->email_id;
								$bill_customer_id = $customer_email_alternate_shop;
								// $emailSend=1;
								$customers_invoiceData_alt_cusVar = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
								if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
									$invoice_type = $customers_invoiceData_alt_cusVar->invoice_type;
									$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
									$invoice_to_type = $customers_invoiceData_alt_cusVar->invoice_to_type;
									if ($customers_invoiceData_alt_cusVar->invoice_type == 1) {
										$invoice_generate = 1;
										$emailSend = 1;
									}
								} else {
									$invoice_generate = 1;
									$payment_term = 0;
									$customer_email_alternate = '';
								}
							}
						}
					} else {
						$customVariables_alternative_email_id = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
						if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
							$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
							$customer_email_data_shop = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
							$bill_customer_email = $customer_email_data_shop->email_id;
							$bill_customer_id = $customer_email_alternate_shop;
							// $emailSend=1;
							$customers_invoiceData_alt_cusVar = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
							if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
								$invoice_type = $customers_invoiceData_alt_cusVar->invoice_type;
								$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
								$invoice_to_type = $customers_invoiceData_alt_cusVar->invoice_to_type;
								if ($customers_invoiceData_alt_cusVar->invoice_type == 1) {
									$invoice_generate = 1;
									$emailSend = 1;
								}
							} else {
								$invoice_generate = 1;
								$payment_term = 0;
								$customer_email_alternate = '';
							}
						}
					}

					if ($bill_customer_id) {
						$Default_BillingAddress = $this->ShopProductModel->getSingleDataByID('customers_address', array('customer_id' => $bill_customer_id, 'is_default' => 1), '');
						if (isset($Default_BillingAddress)) {
							$bill_customer_first_name = $Default_BillingAddress->first_name;
							$bill_customer_last_name = $Default_BillingAddress->last_name;
							// $bill_customer_email=$orderData->email;
							$billing_address_line1 = $Default_BillingAddress->address_line1; //$orderData->bill_address_line1
							$billing_address_line2 = $Default_BillingAddress->address_line2; //$orderData->bill_address_line2
							$billing_city = $Default_BillingAddress->city; //$orderData->bill_city
							$billing_state = $Default_BillingAddress->state; //$orderData->bill_state
							$billing_country = $Default_BillingAddress->country; //$orderData->bill_country
							$billing_pincode = $Default_BillingAddress->pincode; //$orderData->bill_pincode
						} else {
							$bill_BillingAddress = $this->ShopProductModel->getSingleDataByID('customers_address', array('customer_id' => $bill_customer_id, ''), '');
							if (isset($bill_BillingAddress)) {
								$bill_customer_first_name = $bill_BillingAddress->first_name;
								$bill_customer_last_name = $bill_BillingAddress->last_name;
								// $bill_customer_email=$orderData->email;
								$billing_address_line1 = $bill_BillingAddress->address_line1; //$orderData->bill_address_line1
								$billing_address_line2 = $bill_BillingAddress->address_line2; //$orderData->bill_address_line2
								$billing_city = $bill_BillingAddress->city; //$orderData->bill_city
								$billing_state = $bill_BillingAddress->state; //$orderData->bill_state
								$billing_country = $bill_BillingAddress->country; //$orderData->bill_country
								$billing_pincode = $bill_BillingAddress->pincode; //$orderData->bill_pincode
							}
						}
					}
				} elseif (isset($customer_email_alternate_shop) & !empty($customer_email_alternate_shop)) { // new add 16-08-2021
					$customers_invoiceData_alt_cusVar = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
					if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
						$invoice_type = $customers_invoiceData_alt_cusVar->invoice_type;
						$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
						$invoice_to_type = $customers_invoiceData_alt_cusVar->invoice_to_type;
						if ($customers_invoiceData_alt_cusVar->invoice_type == 1) {
							$invoice_generate = 1;
							$emailSend = 1;
						}
					} else {
						/*$invoice_generate=1;
						$payment_term=0;
						$customer_email_alternate='';*/
					}
				} //end invoice type check //end invoice type check

				// bill customer data
				$bill_customer_company_name_gst = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $bill_customer_id), 'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
				if (isset($bill_customer_company_name_gst)) {
					$customer_name = $bill_customer_company_name_gst->customer_name;
					$customer_company = $bill_customer_company_name_gst->company_name;
					$customer_gst_no = $bill_customer_company_name_gst->gst_no;
				}
			}
		} // end invoice_self


		// end updated 10-08-2021

		$invoice_term = $payment_term;
		// insert invoicing data
		$insertinvoicingdataitem = array(
			'invoice_no' => $invoice_no,
			'customer_first_name' => $customer_first_name,
			'customer_last_name' => $customer_last_name,
			'customer_id' => $customer_id,
			'customer_email' => $customer_email,
			// 'shop_id'=>'',
			// 'shop_webshop_name'=>$shop_webshop_name,
			// 'shop_company_name'=>$shop_company_name,
			'shop_gst_no' => $shop_gst_no,
			'bill_customer_first_name' => $customer_name,
			'bill_customer_company_name' => $customer_company,
			'bill_customer_gst_no' => $customer_gst_no,
			// 'bill_customer_last_name'=>$customer_name,
			// 'bill_customer_id'=>$fbc_user_id_order,
			'bill_customer_id' => $bill_customer_id, // new $bill_customer_email$bill_customer_id
			'bill_customer_email' => $bill_customer_email, // new
			'invoice_order_nos' => $order_id,
			'invoice_order_type' => $invoice_order_type,
			'invoice_subtotal' => $invoice_subtotal,
			'invoice_tax' => $invoice_tax,
			'invoice_grand_total' => $invoice_grand_total,
			'billing_address_line1' => $billing_address_line1,
			'billing_address_line2' => $billing_address_line2,
			'billing_city' => $billing_city,
			'billing_state' => $billing_state,
			'billing_country' => $billing_country,
			'billing_pincode' => $billing_pincode,
			'ship_address_line1' => $ship_address_line1,
			'ship_address_line2' => $ship_address_line2,
			'ship_city' => $ship_city,
			'ship_state' => $ship_state,
			'ship_country' => $ship_country,
			'ship_pincode' => $ship_pincode,
			'invoice_date' => $invoice_date,
			'invoice_due_date' => $invoice_due_date,
			'invoice_term' => $invoice_term,
			'payment_charges' => $payment_charges,
			'voucher_amount' => $voucher_amount,
			'shipping_charges' => $shipping_charges,
			// 'b2b_orderid'=>$webshop_b2b_order_id,//testing purpose comment
			'b2b_orderid' => $b2b_order_id, //testing purpose comment
			// 'created_by'=>$fbc_user_id,
			'created_at' => time(),
			'ip' => $_SERVER['REMOTE_ADDR']
		);

		if ($invoice_generate == 1) { //invoice generate 1-yes, 0-no
			$invoicing_one = $this->ShopProductModel->insertData('invoicing', $insertinvoicingdataitem);
			//}
			//$invoicing_one='35';
			// print_r($invoicing_one);exit();
			if ($invoicing_one) {
				//update custom_variable
				$invoice_no_update = array('value' => $invoice_next_no);
				$where_invoice_arr = array('identifier' => 'invoice_next_no');

				$setting_update = $this->ShopProductModel->updateData('custom_variables', $where_invoice_arr, $invoice_no_update);
				// sales order updated
				$invoice_sales_order = array('invoice_id' => $invoicing_one, 'invoice_date' => $invoice_date, 'invoice_flag' => 1);
				$where_sales_order_arr = array('order_id' => $order_id);
				$this->ShopProductModel->updateData('sales_order', $where_sales_order_arr, $invoice_sales_order);
				// send invoice email and save invoice pdf
				$pdfGeneratePdfName = $this->generatePdfWebshop_b2b($invoicing_one, $args['shop_id'], $args['fbc_user_id'], $parent_id, $b2b_order_id); // save pdf
				//send email with attachment
				if ($pdfGeneratePdfName) {
					//update invoicing
					$invoiceFileName = array('invoice_file' => $pdfGeneratePdfName);
					$where_invoice_filename_arr = array('id' => $invoicing_one);
					$this->ShopProductModel->updateData('invoicing', $where_invoice_filename_arr, $invoiceFileName);

					// sent email
					//$emailSend=0;
					/*----------------Send Email to invoice with attchmnet--------------------*/
					if ($emailSend == 1) { // email check 1-send 0-not send
						// invoice send date add
						if ($customer_id > 0) {
							$last_invoice_send_date = array('last_invoice_sent_date' => $invoice_date);
							$where_invoice_send_email_arr = array('customer_id' => $customer_id);
							$this->ShopProductModel->updateData('customers_invoice', $where_invoice_send_email_arr, $last_invoice_send_date);
						}

						//$fbc_user_id	=	$this->session->userdata('LoginID');
						$shop_id		=	$webshop_shop_id;
						$Ishop_owner = $this->CommonModel->getShopOwnerData($shop_id);
						$Iwebshop_details = $this->CommonModel->get_webshop_details($shop_id);
						$Ishop_name = $Ishop_owner->org_shop_name;
						$ItemplateId = 'system-invoice';
						//$Ito = 'rajesh@bcod.co.in';
						$Ito = $bill_customer_email;
						if (isset($Iwebshop_details)) {
							$Ishop_logo = $this->encryption->decrypt($Iwebshop_details['site_logo']);
						} else {
							$Ishop_logo = '';
						}
						$burl = base_url();
						$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);
						$Isite_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
							<img alt="' . $Ishop_name . '" border="0" src="' . $Ishop_logo . '" style="max-width:200px" />
						</a>';
						$Iusername = $customer_name;
						$ITempVars = array();
						$IDynamicVars = array();

						$ITempVars = array("##OWNER##", "##INVOICENO##", "##WEBSHOPNAME##");
						$IDynamicVars   = array($Iusername, $invoice_no, $Ishop_name);
						$ICommonVars = array($Isite_logo, $Ishop_name, $invoice_no);
						$attachment = get_s3_url('invoices/' . $pdfGeneratePdfName, $shop_id);
						//email function
						$mailSent_invoice = $this->ShopProductModel->sendCommonHTMLEmailWebshop($webshop_shop_id, $Ito, $ItemplateId, $ITempVars, $IDynamicVars, $ICommonVars, $attachment);
					} else {
						// invoice generate
						if ($invoice_generate == 1) {
							// invoice updated flag
							$invoiceUpdate = array('internal_invoice_flag' => 1);
							$whereInvoiceArr = array('id' => $invoicing_one);
							$invoioceUpdated = $this->ShopProductModel->updateData('invoicing', $whereInvoiceArr, $invoiceUpdate);
						}
					}
				} // pdf generate
			}
		} //invoice generate


	} // end webshop invoice generate


	// new invoice start b2b webshop

	// new b2b invoice generate button click
	function invoiceGenerate_b2b()
	{
		if (isset($_POST['order_id'])) {
			$order_id = $_POST['order_id'];
			$webshop_b2b_order_id = $_POST['webshop_b2b_order_id'];
			$b2b_order_id = $_POST['b2b_order_id']; //split order id supplier b2b_order table
			$parent_id = $_POST['parent_id'];
			//$webshop_shop_id=$_POST['webshop_shop_id'];
			$args['shop_id']	=	$_POST['webshop_shop_id'];
			$args['fbc_user_id']	=	$_POST['webshop_fbc_user_id'];
			$this->load->model('ShopProductModel');
			$this->ShopProductModel->init($args);
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}

		// testing

		/*$order_id=360;
			$b2b_order_id=124;
			$parent_id=0;
			$webshop_b2b_order_id='B2B-1078';
			$args['shop_id']	=3;
			$args['fbc_user_id']	=3;

			$this->load->model('ShopProductModel');
			$this->ShopProductModel->init($args); */
		// ecnd testing

		$orderData = $this->ShopProductModel->get_webshop_invoicing_b2b_generate_data($order_id);
		$orderUserType = $orderData->checkout_method; //guest,register,login

		$ShippingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $orderData->order_id, 'address_type' => 2), '');
		$BillingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $orderData->order_id, 'address_type' => 1), '');

		$customVariables_invoice_prefix = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_prefix'), 'value');
		$customVariables_invoice_no = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'invoice_next_no'), 'value');
		//print_r($customVariables_invoice_no);exit();
		if ($customVariables_invoice_prefix->value && $customVariables_invoice_no->value) {
			$invoiceNo = $customVariables_invoice_no->value;
			$invoice_next_no = $invoiceNo + 1;
			$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
		} elseif ($customVariables_invoice_prefix->value) {
			// $invoiceNo='2';
			$invoiceNo = '1';
			$invoice_next_no = $invoiceNo + 1;
			$invoice_no = $customVariables_invoice_prefix->value . $invoiceNo;
			# code...
		} elseif ($customVariables_invoice_no->value) {
			$invoiceNo = $customVariables_invoice_no->value;
			$invoice_next_no = $invoiceNo + 1;
			$invoice_no = 'INV' . $invoiceNo;
		}
		// custom_variable table data updated
		$invoice_no = $invoice_no;
		$invoice_next_no = $invoice_next_no;

		// invoice table insert
		$invoice_order_type = '1'; //1-Webshop;  2-B2Webshop;
		$shop_id = '';
		$customer_name = $orderData->customer_name;
		$customer_first_name = $orderData->customer_firstname;
		$customer_last_name = $orderData->customer_lastname;
		$customer_id = $orderData->customer_id;
		$customer_is_guest = $orderData->customer_is_guest;
		$invoice_self = $orderData->invoice_self;
		// $shop_webshop_name=$orderData->org_shop_name;
		$shop_webshop_name = '';
		$shop_gst_no = ''; //$orderData->gst_no
		$shop_company_name = ''; //$orderData->company_name
		//echo $customer_id;
		// $orderData->invoice_self=1;

		//$customer_email=$orderData->customer_email;
		$invoice_subtotal = '';
		$invoice_tax = '';
		$invoice_grand_total = '';
		// bill invoice
		$bill_customer_first_name = $BillingAddress->first_name;
		$bill_customer_last_name = $BillingAddress->last_name;
		$bill_customer_id = '';
		$bill_customer_email = '';
		// $bill_customer_email=$orderData->email;
		$billing_address_line1 = $BillingAddress->address_line1; //$orderData->bill_address_line1
		$billing_address_line2 = $BillingAddress->address_line2; //$orderData->bill_address_line2
		$billing_city = $BillingAddress->city; //$orderData->bill_city
		$billing_state = $BillingAddress->state; //$orderData->bill_state
		$billing_country = $BillingAddress->country; //$orderData->bill_country
		$billing_pincode = $BillingAddress->pincode; //$orderData->bill_pincode

		$ship_address_line1 = $ShippingAddress->address_line1; //$orderData->ship_address_line1
		$ship_address_line2 = $ShippingAddress->address_line2; //$orderData->ship_address_line2
		$ship_city = $ShippingAddress->city; //$orderData->ship_city
		$ship_state = $ShippingAddress->state; //$orderData->ship_state
		$ship_country = $ShippingAddress->country; //$orderData->ship_country
		$ship_pincode = $ShippingAddress->pincode; //$orderData->ship_pincode
		$invoice_date = time();

		//get data by user id
		$invoice_due_date = '';
		//$orderUserType; exit();
		$invoice_type = 0;
		$invoice_generate = 0;
		$emailSend = 0;
		// new added data
		$customer_company = '';
		$customer_gst_no = '';
		$shipping_charges = $orderData->shipping_amount;
		$voucher_amount = $orderData->voucher_amount;
		$payment_charges = $orderData->payment_final_charge;
		//new add invoice
		$customer_email = $orderData->customer_email;
		$bill_customer_email = $orderData->customer_email;
		$bill_customer_id = $orderData->customer_id;
		// print_r($orderData->invoice_self);
		// $orderData->invoice_self=0;
		if ($orderData->invoice_self == 1) {
			if ($orderUserType == 'guest') {
				$payment_term = '';
				$invoice_due_date = $invoice_date;
				// $emailSend=1;
				$invoice_generate = 1;
			} else {
				$customers_invoiceData = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_id), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				if (isset($customers_invoiceData) && !empty($customers_invoiceData)) {
					$invoice_type = $customers_invoiceData->invoice_type;
					$payment_term = $customers_invoiceData->payment_term;
					$invoice_to_type = $customers_invoiceData->invoice_to_type;
					// if($customers_invoiceData->invoice_type==1){
					$invoice_generate = 1;
					// $emailSend=1;
					// }
				} else {
					$invoice_generate = 1;
					$payment_term = '';
					$invoice_due_date = $invoice_date;
					// alternate email id

				}
				if ($invoice_date && $payment_term > 0) {
					$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
					$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
					$invoice_due_date = date(strtotime($due_date));
				} else {
					$payment_term = '';
					$invoice_due_date = $invoice_date;
				}

				// bill customer data
				$bill_customer_company_name_gst = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $bill_customer_id), 'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
				if (isset($bill_customer_company_name_gst)) {
					$customer_name = $bill_customer_company_name_gst->customer_name;
					$customer_company = $bill_customer_company_name_gst->company_name;
					$customer_gst_no = $bill_customer_company_name_gst->gst_no;
				}
			}
		} else {

			// updated 10-08-2021
			if ($orderUserType == 'guest') {
				$invoice_generate = 1;
				$payment_term = '';
				$invoice_due_date = $invoice_date;
				$customVariables_alternative_email_id = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
				if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
					$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
					$customer_email_data_shop = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
					$bill_customer_email = $customer_email_data_shop->email_id;
					$bill_customer_id = $customer_email_alternate_shop;
					// $emailSend=1;
				}
			} else {
				$customers_invoiceData = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_id), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				// print_r($customers_invoiceData);exit();
				// if(isset($customers_invoiceData) && $customers_invoiceData->invoice_type==1){
				if (isset($customers_invoiceData) && !empty($customers_invoiceData)) {
					$invoice_type = $customers_invoiceData->invoice_type;
					$payment_term = $customers_invoiceData->payment_term;
					$customer_email_alternate = '';
					$invoice_to_type = $customers_invoiceData->invoice_to_type;
					if ($invoice_to_type != 0) {
						$customer_email_alternate = $customers_invoiceData->alternative_email_id;
					}
					// if($customers_invoiceData->invoice_type==1){
					$invoice_generate = 1;
					$emailSend = 1;
					// }
				} else {
					$invoice_generate = 1;
					$payment_term = 0;
					//$customer_email_alternate='';

					// alternate
					$customVariables_alternative_email_id = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
					if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
						$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
						$customer_email_data_shop = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
						$bill_customer_email = $customer_email_data_shop->email_id;
						$bill_customer_id = $customer_email_alternate_shop;
						// $emailSend=1;
					}
					// end alternate

				}
				// print_r($customer_email_alternate);
				// exit();

				if ($invoice_date && $payment_term > 0) {
					$dateAdd = date(DATE_PIC_FM, $invoice_date); // invoice due date
					$due_date = date('Y-m-d', strtotime($dateAdd . ' + ' . $payment_term . ' days'));
					$invoice_due_date = date(strtotime($due_date));
				} else {
					$payment_term = '';
					$invoice_due_date = $invoice_date;
				}

				if (isset($invoice_to_type) && $invoice_to_type == 1) { //invoice type check
					if ($customer_email_alternate) {
						//echo $customer_email_alternate;
						//$customer_email_alternate=0;
						$customer_email_data = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate), 'email_id');
						if (isset($customer_email_data) && !empty($customer_email_data)) {
							// new add
							$bill_customer_email = $customer_email_data->email_id;
							$bill_customer_id = $customer_email_alternate;
							$customers_invoiceData_alt = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
							if (isset($customers_invoiceData_alt) && !empty($customers_invoiceData_alt)) {
								$invoice_type = $customers_invoiceData_alt->invoice_type;
								$payment_term = $customers_invoiceData_alt->payment_term;
								$invoice_to_type = $customers_invoiceData_alt->invoice_to_type;
								if ($customers_invoiceData_alt->invoice_type == 1) {
									$invoice_generate = 1;
									$emailSend = 1;
								}
							} else {
								$invoice_generate = 1;
								$payment_term = 0;
								$customer_email_alternate = '';
							}


							// $emailSend=1;
						} else {
							$customVariables_alternative_email_id = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
							if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
								$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
								$customer_email_data_shop = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
								$bill_customer_email = $customer_email_data_shop->email_id;
								$bill_customer_id = $customer_email_alternate_shop;
								// $emailSend=1;
								$customers_invoiceData_alt_cusVar = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
								if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
									$invoice_type = $customers_invoiceData_alt_cusVar->invoice_type;
									$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
									$invoice_to_type = $customers_invoiceData_alt_cusVar->invoice_to_type;
									if ($customers_invoiceData_alt_cusVar->invoice_type == 1) {
										$invoice_generate = 1;
										$emailSend = 1;
									}
								} else {
									$invoice_generate = 1;
									$payment_term = 0;
									$customer_email_alternate = '';
								}
							}
						}
					} else {
						$customVariables_alternative_email_id = $this->ShopProductModel->getSingleShopDataByID('custom_variables', array('identifier' => 'webshopcust_def_inv_altemail'), 'value');
						if (isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value) {
							$customer_email_alternate_shop = $customVariables_alternative_email_id->value;
							$customer_email_data_shop = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $customer_email_alternate_shop), 'email_id');
							$bill_customer_email = $customer_email_data_shop->email_id;
							$bill_customer_id = $customer_email_alternate_shop;
							// $emailSend=1;
							$customers_invoiceData_alt_cusVar = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
							if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
								$invoice_type = $customers_invoiceData_alt_cusVar->invoice_type;
								$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
								$invoice_to_type = $customers_invoiceData_alt_cusVar->invoice_to_type;
								if ($customers_invoiceData_alt_cusVar->invoice_type == 1) {
									$invoice_generate = 1;
									$emailSend = 1;
								}
							} else {
								$invoice_generate = 1;
								$payment_term = 0;
								$customer_email_alternate = '';
							}
						}
					}

					if ($bill_customer_id) {
						$Default_BillingAddress = $this->ShopProductModel->getSingleDataByID('customers_address', array('customer_id' => $bill_customer_id, 'is_default' => 1), '');
						if (isset($Default_BillingAddress)) {
							$bill_customer_first_name = $Default_BillingAddress->first_name;
							$bill_customer_last_name = $Default_BillingAddress->last_name;
							// $bill_customer_email=$orderData->email;
							$billing_address_line1 = $Default_BillingAddress->address_line1; //$orderData->bill_address_line1
							$billing_address_line2 = $Default_BillingAddress->address_line2; //$orderData->bill_address_line2
							$billing_city = $Default_BillingAddress->city; //$orderData->bill_city
							$billing_state = $Default_BillingAddress->state; //$orderData->bill_state
							$billing_country = $Default_BillingAddress->country; //$orderData->bill_country
							$billing_pincode = $Default_BillingAddress->pincode; //$orderData->bill_pincode
						} else {
							$bill_BillingAddress = $this->ShopProductModel->getSingleDataByID('customers_address', array('customer_id' => $bill_customer_id, ''), '');
							if (isset($bill_BillingAddress)) {
								$bill_customer_first_name = $bill_BillingAddress->first_name;
								$bill_customer_last_name = $bill_BillingAddress->last_name;
								// $bill_customer_email=$orderData->email;
								$billing_address_line1 = $bill_BillingAddress->address_line1; //$orderData->bill_address_line1
								$billing_address_line2 = $bill_BillingAddress->address_line2; //$orderData->bill_address_line2
								$billing_city = $bill_BillingAddress->city; //$orderData->bill_city
								$billing_state = $bill_BillingAddress->state; //$orderData->bill_state
								$billing_country = $bill_BillingAddress->country; //$orderData->bill_country
								$billing_pincode = $bill_BillingAddress->pincode; //$orderData->bill_pincode
							}
						}
					}
				} elseif (isset($customer_email_alternate_shop) & !empty($customer_email_alternate_shop)) { // new add 16-08-2021
					$customers_invoiceData_alt_cusVar = $this->ShopProductModel->getSingleShopDataByID('customers_invoice', array('customer_id' => $customer_email_alternate_shop), 'invoice_type,payment_term,invoice_to_type,alternative_email_id');
					if (isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)) {
						$invoice_type = $customers_invoiceData_alt_cusVar->invoice_type;
						$payment_term = $customers_invoiceData_alt_cusVar->payment_term;
						$invoice_to_type = $customers_invoiceData_alt_cusVar->invoice_to_type;
						if ($customers_invoiceData_alt_cusVar->invoice_type == 1) {
							$invoice_generate = 1;
							$emailSend = 1;
						}
					} else {
						/*$invoice_generate=1;
						$payment_term=0;
						$customer_email_alternate='';*/
					}
				} //end invoice type check

				// bill customer data
				$bill_customer_company_name_gst = $this->ShopProductModel->getSingleShopDataByID('customers', array('id' => $bill_customer_id), 'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
				if (isset($bill_customer_company_name_gst)) {
					$customer_name = $bill_customer_company_name_gst->customer_name;
					$customer_company = $bill_customer_company_name_gst->company_name;
					$customer_gst_no = $bill_customer_company_name_gst->gst_no;
				}
			}
			// end updated 10-08-2021

		}	// invoice_self

		$invoice_term = $payment_term;
		// insert invoicing data
		$insertinvoicingdataitem = array(
			'invoice_no' => $invoice_no,
			'customer_first_name' => $customer_first_name,
			'customer_last_name' => $customer_last_name,
			'customer_id' => $customer_id,
			'customer_email' => $customer_email,
			// 'shop_id'=>'',
			// 'shop_webshop_name'=>$shop_webshop_name,
			// 'shop_company_name'=>$shop_company_name,
			'shop_gst_no' => $shop_gst_no,
			'bill_customer_first_name' => $customer_name,
			'bill_customer_company_name' => $customer_company,
			'bill_customer_gst_no' => $customer_gst_no,
			// 'bill_customer_last_name'=>$customer_name,
			// 'bill_customer_id'=>$fbc_user_id_order,
			'bill_customer_id' => $bill_customer_id, // new $bill_customer_email$bill_customer_id
			'bill_customer_email' => $bill_customer_email, // new
			'invoice_order_nos' => $order_id,
			'invoice_order_type' => $invoice_order_type,
			'invoice_subtotal' => $invoice_subtotal,
			'invoice_tax' => $invoice_tax,
			'invoice_grand_total' => $invoice_grand_total,
			'billing_address_line1' => $billing_address_line1,
			'billing_address_line2' => $billing_address_line2,
			'billing_city' => $billing_city,
			'billing_state' => $billing_state,
			'billing_country' => $billing_country,
			'billing_pincode' => $billing_pincode,
			'ship_address_line1' => $ship_address_line1,
			'ship_address_line2' => $ship_address_line2,
			'ship_city' => $ship_city,
			'ship_state' => $ship_state,
			'ship_country' => $ship_country,
			'ship_pincode' => $ship_pincode,
			'invoice_date' => $invoice_date,
			'invoice_due_date' => $invoice_due_date,
			'invoice_term' => $invoice_term,
			'payment_charges' => $payment_charges,
			'voucher_amount' => $voucher_amount,
			'shipping_charges' => $shipping_charges,
			// 'b2b_orderid'=>$webshop_b2b_order_id,//testing purpose comment
			'b2b_orderid' => $b2b_order_id, //testing purpose comment
			// 'created_by'=>$fbc_user_id,
			'created_at' => time(),
			'ip' => $_SERVER['REMOTE_ADDR']
		);
		// print_r($insertinvoicingdataitem);exit();
		if ($invoice_generate == 1) { //invoice generate 1-yes, 0-no
			$invoicing_one = $this->ShopProductModel->insertData('invoicing', $insertinvoicingdataitem);
			//}
			//$invoicing_one='35';
			// print_r($invoicing_one);exit();
			if ($invoicing_one) {
				//update custom_variable
				$invoice_no_update = array('value' => $invoice_next_no);
				$where_invoice_arr = array('identifier' => 'invoice_next_no');

				$setting_update = $this->ShopProductModel->updateData('custom_variables', $where_invoice_arr, $invoice_no_update);
				// sales order updated
				$invoice_sales_order = array('invoice_id' => $invoicing_one, 'invoice_date' => $invoice_date, 'invoice_flag' => 1);
				$where_sales_order_arr = array('order_id' => $order_id);
				$this->ShopProductModel->updateData('sales_order', $where_sales_order_arr, $invoice_sales_order);
				// send invoice email and save invoice pdf
				$pdfGeneratePdfName = $this->generatePdfWebshop_b2b_flag2($invoicing_one, $args['shop_id'], $args['fbc_user_id'], $parent_id, $b2b_order_id); // save pdf
				//send email with attachment
				if ($pdfGeneratePdfName) {
					//update invoicing
					$invoiceFileName = array('invoice_file' => $pdfGeneratePdfName);
					$where_invoice_filename_arr = array('id' => $invoicing_one);
					$this->ShopProductModel->updateData('invoicing', $where_invoice_filename_arr, $invoiceFileName);

					// sent email
					$emailSend = 0;
					/*----------------Send Email to invoice with attchmnet--------------------*/
					if ($emailSend == 1) { // email check 1-send 0-not send
						// invoice send date add
						if ($customer_id > 0) {
							$last_invoice_send_date = array('last_invoice_sent_date' => $invoice_date);
							$where_invoice_send_email_arr = array('customer_id' => $customer_id);
							$this->ShopProductModel->updateData('customers_invoice', $where_invoice_send_email_arr, $last_invoice_send_date);
						}

						//$fbc_user_id	=	$this->session->userdata('LoginID');
						$shop_id		=	$this->session->userdata('ShopID');
						$Ishop_owner = $this->CommonModel->getShopOwnerData($shop_id);
						$Iwebshop_details = $this->CommonModel->get_webshop_details($shop_id);
						$Ishop_name = $Ishop_owner->org_shop_name;
						$ItemplateId = 'system-invoice';
						//$Ito = 'rajesh@bcod.co.in';
						$Ito = $customer_email;
						if (isset($Iwebshop_details)) {
							$Ishop_logo = $this->encryption->decrypt($Iwebshop_details['site_logo']);
						} else {
							$Ishop_logo = '';
						}
						$burl = base_url();
						$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);
						$Isite_logo =  '<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;">
							<img alt="' . $Ishop_name . '" border="0" src="' . $Ishop_logo . '" style="max-width:200px" />
						</a>';
						$Iusername = $customer_name;
						$ITempVars = array();
						$IDynamicVars = array();

						$ITempVars = array("##OWNER##", "##INVOICENO##", "##WEBSHOPNAME##");
						$IDynamicVars   = array($Iusername, $invoice_no, $Ishop_name);
						$ICommonVars = array($Isite_logo, $Ishop_name, $invoice_no);
						$attachment = get_s3_url('invoices/' . $pdfGeneratePdfName, $shop_id);
						//email function
						$mailSent_invoice = $this->WebshopOrdersModel->sendInvoiceHTMLEmail($Ito, $ItemplateId, $ITempVars, $IDynamicVars, $ICommonVars, $attachment);
					} else {
						// invoice generate
						if ($invoice_generate == 1) {
							// invoice updated flag
							$invoiceUpdate = array('internal_invoice_flag' => 1);
							$whereInvoiceArr = array('id' => $invoicing_one);
							$invoioceUpdated = $this->ShopProductModel->updateData('invoicing', $whereInvoiceArr, $invoiceUpdate);
						}
					}
					$arrResponse  = array('status' => 200, 'message' => 'Invoice generate successfully.');
					echo json_encode($arrResponse);
					exit;
				} else {
					$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
					echo json_encode($arrResponse);
					exit;
				}
			}
		}
	}


	// pdf generate
	//
	//function generatePdfWebshop_b2b($invoiceID){
	function generatePdfWebshop_b2b($invoiceID, $webshop_shop_id, $webshop_fbc_user_id, $parent_id, $b2b_order_id)
	{
		//echo '<pre>';
		//print_r($invoiceID);
		/*$parent_id=81;
		$b2b_order_id=83;*/
		// exit();//
		/*$args['shop_id']	=	3;
		$args['fbc_user_id']	= 3;*/
		$args['shop_id']	=	$webshop_shop_id;
		$args['fbc_user_id']	= $webshop_fbc_user_id;

		$this->load->model('ShopProductModel');
		$this->ShopProductModel->init($args);

		/*$webshop_shop_id=3;
		$webshop_fbc_user_id=3;*/
		$webshop_shop_id = $webshop_shop_id;
		$webshop_fbc_user_id = $webshop_fbc_user_id;
		$data['parent_id'] = $parent_id;
		$data['b2b_order_id'] = $b2b_order_id;

		$invoice_id = $invoiceID;
		$data['invoicedata'] = $this->ShopProductModel->get_invoicedata_by_id($invoice_id);
		// Shop Data
		$data['custom_variables'] = $this->ShopProductModel->get_custom_variables();
		//getSingleDataByID
		$data['shop_id'] = $webshop_shop_id;
		$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($webshop_shop_id);
		$data['user_details'] = $this->CommonModel->GetUserByUserId($webshop_fbc_user_id);
		if ($data['user_details']->parent_id == 0) {
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
		} else {
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
		}
		// dom pdf
		$this->load->library('Pdf_dom');
		//$this->load->view('invoice/b2b/invoice_format',$data);
		$htmldata = $this->load->view('invoice/b2b/invoice_format_webshop', $data, true);
		// $invoiceFileName=$this->pdf_dom->createtesting($htmldata,$invoiceID); // testing removed live
		$invoiceFileName = $this->pdf_dom->createbyshopB2b($htmldata, $invoiceID, $data['shop_id']);
		return $invoiceFileName;
	}
	// end pdf generate

	// end new invoice start b2b webshop

	//invoice shop flag2
	function generatePdfWebshop_b2b_flag2($invoiceID, $webshop_shop_id, $webshop_fbc_user_id, $parent_id, $b2b_order_id)
	{
		//echo '<pre>';
		//print_r($invoiceID);
		/*$parent_id=81;
		$b2b_order_id=83;*/
		// exit();//
		/*$args['shop_id']	=	3;
		$args['fbc_user_id']	= 3;*/
		$args['shop_id']	=	$webshop_shop_id;
		$args['fbc_user_id']	= $webshop_fbc_user_id;

		$this->load->model('ShopProductModel');
		$this->ShopProductModel->init($args);

		/*$webshop_shop_id=3;
		$webshop_fbc_user_id=3;*/
		$webshop_shop_id = $webshop_shop_id;
		$webshop_fbc_user_id = $webshop_fbc_user_id;
		$data['parent_id'] = $parent_id;
		$data['b2b_order_id'] = $b2b_order_id;

		$invoice_id = $invoiceID;
		$data['invoicedata'] = $this->ShopProductModel->get_invoicedata_by_id($invoice_id);
		// Shop Data
		$data['custom_variables'] = $this->ShopProductModel->get_custom_variables();
		//getSingleDataByID
		$data['shop_id'] = $webshop_shop_id;
		$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($webshop_shop_id);
		$data['user_details'] = $this->CommonModel->GetUserByUserId($webshop_fbc_user_id);
		if ($data['user_details']->parent_id == 0) {
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
		} else {
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
		}
		// dom pdf
		$this->load->library('Pdf_dom');
		//$this->load->view('invoice/b2b/invoice_format',$data);
		$htmldata = $this->load->view('invoice/b2b/invoice_format_webshop_flag_2', $data, true);
		// $invoiceFileName=$this->pdf_dom->createtesting($htmldata,$invoiceID); // testing removed live
		$invoiceFileName = $this->pdf_dom->createbyshopB2b($htmldata, $invoiceID, $data['shop_id']);
		return $invoiceFileName;
	}
	// end pdf generate

	/*new send tracking email b2b to webshop order user*/
	function sendTrackingEmailWebshopUser()
	{
		if (isset($_POST)) {

			$shop_id = $this->session->userdata('ShopID');
			$order_id = $_POST['order_id'];
			$tracking_id = $_POST['tracking_id'];

			$OrderData = $this->CommonModel->getSingleShopDataByID('b2b_orders', array('order_id' => $order_id), '');
			$shipment_details = $this->CommonModel->getSingleShopDataByID('b2b_order_shipment_details', array('order_id' => $order_id, 'id' => $tracking_id), '');
			if (isset($OrderData) && !empty($OrderData)) {
				$b2b_order_increment_id = $OrderData->increment_id;
				$webshop_order_id = $OrderData->webshop_order_id;
				$webshop_order_shop_id = $OrderData->shop_id;

				//webshop data
				$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_order_shop_id), '');
				$webshop_fbc_user_id = $FbcUser->fbc_user_id;



				$args['shop_id']	=	$webshop_order_shop_id;
				$args['fbc_user_id']	=	$webshop_fbc_user_id;

				$this->load->model('ShopProductModel');
				$this->ShopProductModel->init($args);
				$webshopData = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '');
				// $webshopData=$this->ShopProductModel->getSingleDataByID('sales_order',array('order_id'=>$webshop_order_id),'increment_id,customer_email');
			}

			$shop_owner = $this->CommonModel->getShopOwnerData($webshop_order_shop_id);
			$webshop_details = $this->CommonModel->get_webshop_details($webshop_order_shop_id);

			// print_r($webshopData);exit();


			$templateId = 'fbcuser-b2b-dropship-order-tracking-details';
			// $to = 'rajesh@bcod.co.in';
			$to = $webshopData->customer_email;
			$shop_name = $shop_owner->org_shop_name;
			$username = $webshopData->customer_firstname . ' ' . $webshopData->customer_lastname;
			$increment_id = $webshopData->increment_id;

			$site_logo = '';
			if (isset($webshop_details)) {
				$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
			}

			$burl = base_url();
			if (isset($webshop_order_shop_id) && !empty($webshop_order_shop_id) && $webshop_order_shop_id == 3) {
				$webshop_address = "https://zumbashop.in";
			} else {
				$webshop_address = getWebsiteUrl($webshop_order_shop_id, $burl);
			}

			$shop_logo = get_s3_url($shop_logo ?? '', $shop_id);
			$site_logo =  '<a href="' . $webshop_address . '" style="color:#1E7EC8;">
					<img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" />
				</a>';


			$box_no = "Box " . $shipment_details->box_number;
			$box_weight = $shipment_details->weight;
			$tracking_no = $shipment_details->tracking_id;
			$tracking_url = $shipment_details->tracking_url;

			$TempVars = array("##OWNER##", "##ORDERID##", "##B2BORDERID##", "##BOXNO##", "##BOXWEIGHT##",  "##TRACKINGNO##",  "##TRACKINGURL##", '##WEBSHOPNAME##');
			$DynamicVars   = array($username, $increment_id, $b2b_order_increment_id, $box_no, $box_weight, $tracking_no, $tracking_url, $shop_name);
			$CommonVars = array($site_logo, $shop_name);
			//print_r($DynamicVars );exit();
			if (isset($templateId)) {
				$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId, $webshop_order_shop_id);
				if ($emailSendStatusFlag == 1) {
					$mailSent = $this->ShopProductModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $increment_id, $CommonVars);
				}
			}


			$odr_update = array('email_sent_flag' => 1, 'updated_at' => time());
			$where_arr = array('order_id' => $order_id, 'id' => $tracking_id);
			$this->B2BOrdersModel->updateData('b2b_order_shipment_details', $where_arr, $odr_update);

			$arrResponse  = array('status' => 200, 'message' => 'Tracking Email Sent Successfully');
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Error While Sending Mail');
			echo json_encode($arrResponse);
			exit;
		}
	}
	/*end send tracking email b2b to webshop order user*/
	/*function testshipment(){
		$printData=$this->B2BOrdersModel->getOrderCustomerNameByOrderId_test(27);
		print_r($printData);

	}*/

	// start api delivery
	function delhiveryApi($apiURL, $apiTocken, $data)
	{
		//
		//print_r($data);exit();
		$apiUrl = $apiURL;
		// $apiTocken=$apiTocken;
		$apiTocken = "authorization: Token " . $apiTocken;

		// strat curl
		$url = $apiUrl;
		$dataJson = json_encode($data);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			// CURLOPT_POSTFIELDS => $dataJson,
			CURLOPT_POSTFIELDS => "format=json&data=" . $dataJson,
			// CURLOPT_POSTFIELDS => http_build_query($dataJson),
			// CURLOPT_POSTFIELDS => '"format=json&data='.$dataJson.'"',
			CURLOPT_HTTPHEADER => array(
				$apiTocken,
				// "authorization: Token c172fee48668ecf8f265d1a313049b70dbf86a63",
				"cache-control: no-cache"
				// "postman-token: c89d9cc9-ba86-f26d-3d0b-7306f38c121c"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return $err;
		} else {
			return $response;
		}
		//end new


		// end curl


	}

	function delhiveryApiPackageSlip()
	{
		$apiPackungUrl = $_POST['packing_slip_url'] . $_POST['tracking_id'];
		$apiTocken = "authorization: Token " . $_POST['shipmentApiToken'];
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $apiPackungUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				$apiTocken,
				"cache-control: no-cache"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
			$arrResponse  = array('status' => 400, 'message' => 'Error While Download Slip', 'responsData' => $err);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 200, 'message' => 'Download Slip Successfully', 'responsData' => $response);
			echo json_encode($arrResponse);
			exit;
		}



		//



		// end curl


	}

	function delhiveryApiPickupRequest()
	{
		$boxId = $_POST['box_id'];
		$apiPickupRequestUrl = $_POST['pickup_request_url'];
		$apiTocken = "authorization: Token " . $_POST['shipmentApiToken'];
		$data = array('pickup_time' => $_POST['pickupTime'], 'pickup_date' => $_POST['pickupDate'], 'pickup_location' => $_POST['pickupLocation'], 'expected_package_count' => $_POST['expectedPkdQty']);
		$dataJson = json_encode($data);
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $apiPickupRequestUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			// CURLOPT_POSTFIELDS => $dataJson,
			CURLOPT_POSTFIELDS => $dataJson,
			CURLOPT_HTTPHEADER => array(
				$apiTocken,
				"content-type: application/json",
				"cache-control: no-cache"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
			$arrResponse  = array('status' => 400, 'message' => 'Error While Pickup Request', 'responsData' => $err);
			echo json_encode($arrResponse);
			exit;
		} else {
			if ($boxId) {
				$invoiceFileName = array('pickup_request_status' => 1, 'pickup_request_response' => $response);
				$where_box_arr = array('id' => $boxId);
				$this->B2BOrdersModel->updateData('b2b_order_shipment_details', $where_box_arr, $invoiceFileName);
			}
			$arrResponse  = array('status' => 200, 'message' => 'Pickup Request Successfully', 'responsData' => $response);
			echo json_encode($arrResponse);
			exit;
		}
	}

	function delhiveryApiPackageSlipPrint()
	{
		//$apiPackungUrl='https://track.delhivery.com/api/p/packing_slip?wbns=9000810000744';
		$apiPackungUrl = $_POST['packing_slip_url'] . $_POST['tracking_id'];
		$apiTocken = "authorization: Token " . $_POST['shipmentApiToken'];
		$currencyWebshop = $_POST['currencyWebshop'];
		/*$currencyWebshop="INR";
		$apiTocken="authorization: Token 0b1e96ce6b40cb861a62900f5338d57a397e7b71";*/
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $apiPackungUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				$apiTocken,
				"cache-control: no-cache"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$data['Response'] = json_decode($response);
		$data['currencyWebshop'] = $currencyWebshop;
		$customerName = $data['Response']->packages[0]->name;
		/*if ($err) {
			   echo "cURL Error #:" . $err;
			  $arrResponse  = array('status' =>400 ,'message'=>'Error While Download Slip','responsData'=>$err);
			  echo json_encode($arrResponse);exit;
			} else {
			  $arrResponse  = array('status' =>200 ,'message'=>'Download Slip Successfully','responsData'=>$response);
			  echo json_encode($arrResponse);exit;
			}*/

		$this->load->library('Pdf_dom');
		//$this->load->view('invoice/b2b/invoice_format',$data);
		$htmldata = $this->load->view('b2b/order/delivery-api/shipping-delivery-slip', $data, true);
		// $invoiceFileName=$this->pdf_dom->createtesting($htmldata,$invoiceID); // testing removed live
		$pdfData = $this->pdf_dom->createbyPrintingSlip($htmldata, $customerName);
		//print_r($invoiceFileName);


		// end curl
		return $pdfData;
	}

	//end api

	//manual create shipment
	/*function manualCreateShipment(){
		// print_r($_POST);exit();
		if(isset($_POST['order_id']) && isset($_POST['shipment_id']) && $_POST['order_id']!='' && $_POST['box_id']!='')
		{
			$order_id=$_POST['order_id'];
			$box_id=$_POST['box_id'];
			$box_weight=$_POST['box_weight'];
			$shipment_id=$_POST['shipment_id'];

			// api added new
			$tracking_id='';
			$tracking_url='';
			$apiReturnData='';
			// end api added new

			if($shipment_id==''){

				$arrResponse  = array('status' =>400 ,'message'=>'Please select shipment service.');
				echo json_encode($arrResponse);exit;
			}else{

				$fbc_user_id	=	$this->session->userdata('LoginID');
				$shop_id		=	$this->session->userdata('ShopID');

				$count=1;

				//if(isset($box_weight) && count($box_weight)>0){

					// start api
					$OrderData=$this->B2BOrdersModel->getSingleDataByID('b2b_orders',array('order_id'=>$order_id),''); //testing removed after development twice used it
					//print_r($OrderData);
					if(isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id>0){
							$webshop_order_id=$OrderData->webshop_order_id;
							$webshop_shop_id=$OrderData->shop_id;
							$parent_id=$OrderData->main_parent_id; //b2b order table supplier
							$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$webshop_shop_id),'');
							$webshop_fbc_user_id=$FbcUser->fbc_user_id;
							$args['shop_id']	=	$webshop_shop_id;
							$args['fbc_user_id']	=	$webshop_fbc_user_id;
							$this->load->model('ShopProductModel');
							$this->ShopProductModel->init($args);
							$paymentMethodShopflag2=$this->ShopProductModel->getSingleDataByID('sales_order_payment',array('order_id'=>$webshop_order_id),'payment_method');
							//print_r($paymentMethodShopflag2->payment_method);
							if(isset($paymentMethodShopflag2) && $paymentMethodShopflag2->payment_method=='cod' && $shipment_id==3){
								//$shipment_id==3(Delivery LR)
								if(isset($FbcUser->shop_flag) && $FbcUser->shop_flag==2){
									$shipingApidata=$this->ShopProductModel->getSingleDataByID('shipment_master',array('id'=>$shipment_id),'api_details');
									$shipmentApiUrl='';
									$shipmentApiToken='';
									$shipmentTrackingUrl='';

									if(isset($shipingApidata) && !empty($shipingApidata)){
										$shipingApiDetails=json_decode($shipingApidata->api_details);
										if(isset($shipingApiDetails) && !empty($shipingApiDetails)){
											$shipmentApiUrl=$shipingApiDetails->api_url;
											$shipmentApiToken=$shipingApiDetails->api_token;
											$shipmentTrackingUrl=$shipingApiDetails->tracking_url;
										}
										//json_decode($jsonobj)
									}
									// webshop data
									$webshopOrderData=$this->ShopProductModel->getSingleDataByID('sales_order',array('order_id'=>$webshop_order_id),'*');
									$webshopOrderInvoiceData=$this->ShopProductModel->getSingleDataByID('invoicing',array('invoice_order_nos'=>$webshop_order_id,'invoice_order_type'=>1),'*');
									// shipping mobile number
									$mobile_number_shipping_data=$this->ShopProductModel->getSingleShopDataByID('sales_order_address',array('order_id' =>$webshop_order_id, 'address_type' =>2),'mobile_no');
	    							$mobile_number_shipping=$mobile_number_shipping_data->mobile_no;
	    							//end shipping mobile number


	    							// webshop price
	    							$webshopProductPrice=0;
	    							$webshopTaxValue=$webshopOrderInvoiceData->invoice_tax;// invoice tax
	    							$webshopTotalAmount=0;
	    							$boxWeight=0;
	    							$productQty=0;
	    							$webshopCodAmount=0;
	    							$productCategoryDetails='';
	    							//$shipmentData['shipments']
	    							$shipmentDataObj=array();
	    							$shipmentDataObj['shipments']=array();
									$shipmentDataObj['pickup_location']=array();
									// $shipmentDataObj['shipments']['qc']=array();

	    							//end webshop price

									if(isset($webshopOrderData) && !empty($webshopOrderData) && isset($webshopOrderInvoiceData) && !empty($webshopOrderInvoiceData)){
										$productDesc='';
										// webshop invoicing item data
		    							$webshopOrderInvoiceItemData=$this->ShopProductModel->getMultiDataById('invoicing_details',array('invoice_id'=>$webshopOrderInvoiceData->id),'*');
		    							foreach($webshopOrderInvoiceItemData as $itemkey => $itemvalue){
		    								//print_r($itemvalue);
		    								if($itemvalue->product_id > 0){
			    								$WebshopOrderProduct_id=$itemvalue->product_id;
			    								$WebshopOrderProduct_name=$itemvalue->product_name;
			    								$WebshopOrderProduct_hns_code=$itemvalue->product_hsn_code;
			    								$WebshopOrderProduct_Qty=$itemvalue->product_qty;
			    								// $WebshopOrderProduct_hns_code=$webshopOrderInvoiceData->product_hsn_code;
			    								// webshop product price
			    								$webshopProductPriceData=$this->ShopProductModel->getSingleDataByID('products',array('id'=>$WebshopOrderProduct_id),'webshop_price');
			    								if(isset($webshopProductPriceData) && $webshopProductPriceData->webshop_price){
			    									$webshopPrice=$webshopProductPriceData->webshop_price;
			    									$webshopProductPrice += ($webshopPrice * $WebshopOrderProduct_Qty);
			    								}
			    								// total product qty
			    								$productQty += $WebshopOrderProduct_Qty;


			    								//category
			    								$webshopProductCategoryId=$this->ShopProductModel->getSingleDataByID('products_category',array('product_id'=>$WebshopOrderProduct_id),'category_ids');
			    								//end category
			    								if(isset($webshopProductCategoryId) && $webshopProductCategoryId->category_ids){

				    								// main data base
				    								$productCategory=$this->CommonModel->getSingleDataByID('category',array('id' =>$webshopProductCategoryId->category_ids),'*');
				    								// print_r($productCategory);
				    								$productCategoryDetails=$productCategory->cat_name;
				    								//end main db
			    								}
			    								// product item details
			    								// $productItem1['item']=array();
			    								$productItem['item']=array();
			    								$productItem1['descr']=$WebshopOrderProduct_name;
			    								// $productItem1['pcat']='Apparel';
			    								$productItem1['pcat']=$productCategoryDetails;
			    								$productItem1['item_quantity']=$WebshopOrderProduct_Qty;
			    								array_push($productItem['item'], $productItem1);

			    								$variant_data='';
												if(isset($itemvalue->product_variants) && $itemvalue->product_variants!=''){
													$variants=json_decode($itemvalue->product_variants, true);
													if(isset($variants) && count($variants)>0){
														foreach($variants as $pk=>$single_variant){
															if($pk > 0){ $variant_data.= ', ';}
															foreach($single_variant as $key=>$val){
																//$variant_data.='-'.$val;
																$variant_data.=' '.$key.' - '.$val;
															}
														}
													}
												}
												if($variant_data){ $variant_data='('.$variant_data.')';}
												$productNameVariets=$WebshopOrderProduct_name.$variant_data;
			    								$productDesc.=$productNameVariets.',';
		    								}
		    							}

		    							if(!empty($productDesc)){
		    								$productDesc=rtrim($productDesc,',');
		    							}
		    							// print_r($productDesc);
		    							$shipmentData['shipments']['products_desc']=$productDesc;
		    							// end webshop invoicing item data
		    							// print_r(count($webshopOrderInvoiceItemData));exit();
										// api data
											$shipmentData['shipments']['add']=$webshopOrderInvoiceData->ship_address_line1.','.$webshopOrderInvoiceData->ship_address_line2;
											$shipmentData['shipments']['phone']=$mobile_number_shipping;
											$shipmentData['shipments']['payment_mode']='COD';
											$shipmentData['shipments']['name']=$webshopOrderInvoiceData->customer_first_name.' '.$webshopOrderInvoiceData->customer_last_name;
											$shipmentData['shipments']['pin']=$webshopOrderInvoiceData->ship_pincode;
											$shipmentData['shipments']['order']=$webshopOrderData->order_barcode.'-'.$OrderData->order_barcode;
											// $shipmentData['shipments']['order']=$webshopOrderData->order_barcode.'-'.$OrderData->order_barcode.'-1';

											$shipmentData['shipments']['city']=$webshopOrderInvoiceData->ship_city;
											$shipmentData['shipments']['state']=$webshopOrderInvoiceData->ship_state;
											$shipmentData['shipments']['country']=$webshopOrderInvoiceData->ship_country;

											// invoice grand total
											$WebshopInvoiceTotal=$webshopOrderInvoiceData->invoice_grand_total;
											$webshopCodAmount=$WebshopInvoiceTotal; // pending check payment and all condition


											$shipmentData['shipments']['cod_amount']=$webshopCodAmount;
											$shipmentData['shipments']['commodity_value']=$webshopProductPrice;
											// $shipmentData['shipments']['tax_value']=300;
											$shipmentData['shipments']['tax_value']=$webshopTaxValue;
											$webshopTotalAmount = $webshopProductPrice + $webshopTaxValue;
											$shipmentData['shipments']['total_amount']=$webshopTotalAmount;


											//item data

											$shipmentData['shipments']['quantity']=$productQty;

											// product details
											//$qc=$productItem;
											//end product details

											//end item data

											// seller details
											$shipmentData['shipments']['seller_name']='WHUSO SURFACE';
											$shipmentData['shipments']['seller_add']='A/103 1ST PUSHPAKUNJ VAIBHAV CHS LTD SV ROAD BEHIND DIGAMBER JAIN TEMPLE BORIVALI WEST Maharashtra 400092 , Mumbai, MAHARASHTRA ,India 400092';
											$shipmentData['shipments']['seller_inv']=$webshopOrderInvoiceData->invoice_no;
											$shipmentData['shipments']['seller_inv_date']='';
											if(isset($webshopOrderInvoiceData->invoice_date) && $webshopOrderInvoiceData->invoice_date!=''){
												$invDate=date(DATE_PIC_FM,$webshopOrderInvoiceData->invoice_date);
												$shipmentData['shipments']['seller_inv_date']=date('Y-m-d H:i:s',strtotime($invDate));
											}
											// $shipmentData['shipments']['seller_inv_date']=date('Y-m-d H:i:s');;
											$shipmentData['shipments']['seller_gst_tin']='27AACCW7360C1ZD';

											//return
											$shipmentData['shipments']['return_state']='Maharashtra';
											$shipmentData['shipments']['return_city']='Mumbai';
											$shipmentData['shipments']['return_country']='India';
											$shipmentData['shipments']['return_pin']='400092';
											$shipmentData['shipments']['return_name']='WHUSO SURFACE';
											$shipmentData['shipments']['return_add']='209, Lalji`s Shopping Center, S.V. Road , Lalji`s Shopping Center, Opp. Indraprastha Shopping Center, Borivali West';
											// $shipmentData['shipments']['waybill']=;
											// $shipmentData['shipments']['order_date']=date('Y-m-d H:i:s'); // shipping date
											// $shipmentData['shipments']['document_date']=date('Y-m-d H:i:s');

											$pickup['name']='WHUSO Ecommerce Solutions Pvt. Ltd.';
											$pickup['city']='Mumbai';
											$pickup['pin']='400092';
											$pickup['country']='India';
											$pickup['add']='209, Lalji`s Shopping Center, S.V. Road , Lalji`s Shopping Center, Opp. Indraprastha Shopping Center, Borivali West';
											$pickup['state']='Maharashtra';
											$pickup['phone']='9867444136';

										$shipmentData['shipments']['qc']=[];
										//api call
										if($box_weight){
											//$apiData=array('test' =>1 );
											if(isset($box_weight) && $box_weight[0]){
												$boxWeight=$box_weight[0] * 1000;
											}
											$shipmentData['shipments']['weight']=$box_weight * 1000;
											// $shipmentData['shipments']['weight']=$boxWeight;
											// $shipmentData['shipments']['qc']=$productItem;
											// array_push($shipmentData['shipments']['qc'],$productItem);
											// $shipmentData['shipments']['qc']=$productItem;
											$shipmentData['shipments']['qc']=$productItem;
											array_push($shipmentDataObj['shipments'],$shipmentData['shipments']);
											// array_push($shipmentDataObj['shipments'],$productItem);
											// array_push($shipmentDataObj['shipments'],$shipmentData['shipments']);
											$shipmentDataObj['pickup_location']=$pickup;
											// array_push($shipmentDataObj['shipments']['qc'],$productItem);

											$apiReturnData=$this->delhiveryApi($shipmentApiUrl,$shipmentApiToken,$shipmentDataObj);

											if($apiReturnData){
												// print_r($apiReturnData);
												$apiResponseData=json_decode($apiReturnData);
												// $apiResponseData->packages[0]
												$apiStatus=$apiResponseData->packages[0]->status; //api status
												if($apiStatus=='Success'){
													$tracking_id=$apiResponseData->packages[0]->waybill;
													$tracking_url=$shipmentTrackingUrl.$tracking_id;
													//$tracking_id
												}
												// $apiResponseData->packages[0]->remarks[0] // error remark
												//$waybill=$apiResponseData->packages[0]->waybill;
												//print_r($apiResponseData->packages[0]->remarks[0]);
											}
										}
									}
									//end api call

								}
							}
					}



					// echo '<pre>';
					// print_r($shipmentDataObj);
					// print_r($apiReturnData);
					// echo '</pre>';
					// exit();

					// end api start


					// $insertData=array(
							// 'order_id'=>$order_id,
							// 'shipment_id'=>$shipment_id,
					// /		'message'=>$additional_message,
							// 'created_by'=>$fbc_user_id,
							// 'created_at'=>time(),
							// 'ip'=>$_SERVER['REMOTE_ADDR']
					// );

					// $order_shipment_id=		$this->B2BOrdersModel->insertData('b2b_order_shipment',$insertData);

					// foreach($box_weight as $box_val){
						$whereupdateData=array(
								'tracking_id'=>$tracking_id,
								'tracking_url'=>$tracking_url,
								'api_response'=>$apiReturnData,
								'updated_by'=>$fbc_user_id,
								'updated_at'=>time(),

						);
						// $this->B2BOrdersModel->insertData('b2b_order_shipment_details',$insertData);
						$where_shipment_arr=array('id'=>$box_id);

						$this->B2BOrdersModel->updateData('b2b_order_shipment_details',$where_shipment_arr,$whereupdateData);
						//print_r();

						// $count++;
					// }
				//}

				if(isset($apiStatus) && $apiStatus=='Success'){
					$arrResponse  = array('status' =>200 ,'message'=>'Shipment created successfully.','order_id'=>$order_id);
				}elseif(isset($apiStatus) && $apiStatus=='Fail') {
					// $apiStatus=$apiResponseData->packages[0]->remarks;
					$apiMsg=$apiStatus;
					if(isset($apiStatus) && isset($apiResponseData->packages[0]->remarks)){
						$apiMsg=$apiResponseData->packages[0]->remarks;
					}
					$arrResponse  = array('status' =>400 ,'message'=>$apiMsg,'order_id'=>$order_id);
				}

				//$arrResponse  = array('status' =>200 ,'message'=>'Shipment created successfully.','order_id'=>$order_id);
				echo json_encode($arrResponse);exit;

			}

		}else {
			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong.');
			echo json_encode($arrResponse);exit;
		}

	}*/

	// new
	function manualCreateShipment()
	{
		// print_r($_POST);exit();
		if (isset($_POST['order_id']) && isset($_POST['shipment_id']) && $_POST['order_id'] != '' && $_POST['box_id'] != '') {
			$order_id = $_POST['order_id'];
			$box_id = $_POST['box_id'];
			$box_weight = $_POST['box_weight'];
			$shipment_id = $_POST['shipment_id'];

			// api added new
			$tracking_id = '';
			$tracking_url = '';
			$apiReturnData = '';
			// end api added new

			if ($shipment_id == '') {

				$arrResponse  = array('status' => 400, 'message' => 'Please select shipment service.');
				echo json_encode($arrResponse);
				exit;
			} else {

				$fbc_user_id	=	$this->session->userdata('LoginID');
				$shop_id		=	$this->session->userdata('ShopID');

				$count = 1;

				//if(isset($box_weight) && count($box_weight)>0){

				// start api
				$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), ''); //testing removed after development twice used it
				//print_r($OrderData);
				if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {
					$webshop_order_id = $OrderData->webshop_order_id;
					$webshop_shop_id = $OrderData->shop_id;
					$parent_id = $OrderData->main_parent_id; //b2b order table supplier
					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
					$webshop_fbc_user_id = $FbcUser->fbc_user_id;
					$args['shop_id']	=	$webshop_shop_id;
					$args['fbc_user_id']	=	$webshop_fbc_user_id;
					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);
					$paymentMethodShopflag2 = $this->ShopProductModel->getSingleDataByID('sales_order_payment', array('order_id' => $webshop_order_id), 'payment_method');
					//print_r($paymentMethodShopflag2->payment_method);
					if (isset($paymentMethodShopflag2) && $paymentMethodShopflag2->payment_method == 'cod' && $shipment_id == 3) {
						//$shipment_id==3(Delivery LR)
						if (isset($FbcUser->shop_flag) && $FbcUser->shop_flag == 2) {
							$shipingApidata = $this->ShopProductModel->getSingleDataByID('shipment_master', array('id' => $shipment_id), 'api_details');
							$shipmentApiUrl = '';
							$shipmentApiToken = '';
							$shipmentTrackingUrl = '';

							if (isset($shipingApidata) && !empty($shipingApidata)) {
								$shipingApiDetails = json_decode($shipingApidata->api_details);
								if (isset($shipingApiDetails) && !empty($shipingApiDetails)) {
									$shipmentApiUrl = $shipingApiDetails->api_url;
									$shipmentApiToken = $shipingApiDetails->api_token;
									$shipmentTrackingUrl = $shipingApiDetails->tracking_url;
								}
								//json_decode($jsonobj)
							}
							// webshop data
							$webshopOrderData = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '*');
							$webshopOrderInvoiceData = $this->ShopProductModel->getSingleDataByID('invoicing', array('invoice_order_nos' => $webshop_order_id, 'invoice_order_type' => 1), '*');
							// shipping mobile number
							$mobile_number_shipping_data = $this->ShopProductModel->getSingleShopDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), 'mobile_no');
							$mobile_number_shipping = $mobile_number_shipping_data->mobile_no;
							//end shipping mobile number


							// webshop price
							$webshopProductPrice = 0;
							$webshopTaxValue = $webshopOrderInvoiceData->invoice_tax; // invoice tax
							$webshopTotalAmount = 0;
							$boxWeight = 0;
							$productQty = 0;
							$webshopCodAmount = 0;
							$productCategoryDetails = '';
							//$shipmentData['shipments']
							$shipmentDataObj = array();
							$shipmentDataObj['shipments'] = array();
							$shipmentDataObj['pickup_location'] = array();
							// $shipmentDataObj['shipments']['qc']=array();

							//end webshop price

							if (isset($webshopOrderData) && !empty($webshopOrderData) && isset($webshopOrderInvoiceData) && !empty($webshopOrderInvoiceData)) {
								$productDesc = '';
								// webshop invoicing item data
								$webshopOrderInvoiceItemData = $this->ShopProductModel->getMultiDataById('invoicing_details', array('invoice_id' => $webshopOrderInvoiceData->id), '*');
								foreach ($webshopOrderInvoiceItemData as $itemkey => $itemvalue) {
									//print_r($itemvalue);
									if ($itemvalue->product_id > 0) {
										$WebshopOrderProduct_id = $itemvalue->product_id;
										$WebshopOrderProduct_name = $itemvalue->product_name;
										$WebshopOrderProduct_hns_code = $itemvalue->product_hsn_code;
										$WebshopOrderProduct_Qty = $itemvalue->product_qty;
										// $WebshopOrderProduct_hns_code=$webshopOrderInvoiceData->product_hsn_code;
										// webshop product price
										$webshopProductPriceData = $this->ShopProductModel->getSingleDataByID('products', array('id' => $WebshopOrderProduct_id), 'webshop_price');
										if (isset($webshopProductPriceData) && $webshopProductPriceData->webshop_price) {
											$webshopPrice = $webshopProductPriceData->webshop_price;
											$webshopProductPrice += ($webshopPrice * $WebshopOrderProduct_Qty);
										}
										// total product qty
										$productQty += $WebshopOrderProduct_Qty;


										//category
										$webshopProductCategoryId = $this->ShopProductModel->getSingleDataByID('products_category', array('product_id' => $WebshopOrderProduct_id), 'category_ids');
										//end category
										if (isset($webshopProductCategoryId) && $webshopProductCategoryId->category_ids) {

											// main data base
											$productCategory = $this->CommonModel->getSingleDataByID('category', array('id' => $webshopProductCategoryId->category_ids), '*');
											// print_r($productCategory);
											$productCategoryDetails = $productCategory->cat_name;
											//end main db
										}
										// product item details
										// $productItem1['item']=array();
										$productItem['item'] = array();
										$productItem1['descr'] = $WebshopOrderProduct_name;
										// $productItem1['pcat']='Apparel';
										$productItem1['pcat'] = $productCategoryDetails;
										$productItem1['item_quantity'] = $WebshopOrderProduct_Qty;
										array_push($productItem['item'], $productItem1);

										$variant_data = '';
										if (isset($itemvalue->product_variants) && $itemvalue->product_variants != '') {
											$variants = json_decode($itemvalue->product_variants, true);
											if (isset($variants) && count($variants) > 0) {
												foreach ($variants as $pk => $single_variant) {
													if ($pk > 0) {
														$variant_data .= ', ';
													}
													foreach ($single_variant as $key => $val) {
														//$variant_data.='-'.$val;
														$variant_data .= ' ' . $key . ' - ' . $val;
													}
												}
											}
										}
										if ($variant_data) {
											$variant_data = '(' . $variant_data . ')';
										}
										$productNameVariets = $WebshopOrderProduct_name . $variant_data;
										$productDesc .= $productNameVariets . ',';
									}
								}

								if (!empty($productDesc)) {
									$productDesc = rtrim($productDesc, ',');
								}
								// print_r($productDesc);
								$shipmentData['shipments']['products_desc'] = $this->CommonModel->specialCharatcterRemove($productDesc);
								// end webshop invoicing item data
								// print_r(count($webshopOrderInvoiceItemData));exit();
								// api data
								$shipmentData['shipments']['add'] = $webshopOrderInvoiceData->ship_address_line1 . ',' . $webshopOrderInvoiceData->ship_address_line2;
								$shipmentData['shipments']['phone'] = $mobile_number_shipping;
								$shipmentData['shipments']['payment_mode'] = 'COD';
								$shipmentData['shipments']['name'] = $webshopOrderInvoiceData->customer_first_name . ' ' . $webshopOrderInvoiceData->customer_last_name;
								$shipmentData['shipments']['pin'] = $webshopOrderInvoiceData->ship_pincode;
								$shipmentData['shipments']['order'] = $webshopOrderData->order_barcode . '-' . $OrderData->order_barcode;
								// $shipmentData['shipments']['order']=$webshopOrderData->order_barcode.'-'.$OrderData->order_barcode.'-1';

								$shipmentData['shipments']['city'] = $webshopOrderInvoiceData->ship_city;
								$shipmentData['shipments']['state'] = $webshopOrderInvoiceData->ship_state;
								$shipmentData['shipments']['country'] = $webshopOrderInvoiceData->ship_country;

								// invoice grand total
								$WebshopInvoiceTotal = $webshopOrderInvoiceData->invoice_grand_total;
								$webshopCodAmount = $WebshopInvoiceTotal; // pending check payment and all condition


								$shipmentData['shipments']['cod_amount'] = $webshopCodAmount;
								$shipmentData['shipments']['commodity_value'] = $webshopProductPrice;
								// $shipmentData['shipments']['tax_value']=300;
								$shipmentData['shipments']['tax_value'] = $webshopTaxValue;
								$webshopTotalAmount = $webshopProductPrice + $webshopTaxValue;
								$shipmentData['shipments']['total_amount'] = $webshopTotalAmount;


								//item data

								$shipmentData['shipments']['quantity'] = $productQty;

								// product details
								//$qc=$productItem;
								//end product details

								//end item data

								// seller details
								$shipmentData['shipments']['seller_name'] = 'WHUSO SURFACE';
								$shipmentData['shipments']['seller_add'] = 'A/103 1ST PUSHPAKUNJ VAIBHAV CHS LTD SV ROAD BEHIND DIGAMBER JAIN TEMPLE BORIVALI WEST Maharashtra 400092 , Mumbai, MAHARASHTRA ,India 400092';
								$shipmentData['shipments']['seller_inv'] = $webshopOrderInvoiceData->invoice_no;
								$shipmentData['shipments']['seller_inv_date'] = '';
								if (isset($webshopOrderInvoiceData->invoice_date) && $webshopOrderInvoiceData->invoice_date != '') {
									$invDate = date(DATE_PIC_FM, $webshopOrderInvoiceData->invoice_date);
									$shipmentData['shipments']['seller_inv_date'] = date('Y-m-d H:i:s', strtotime($invDate));
								}
								// $shipmentData['shipments']['seller_inv_date']=date('Y-m-d H:i:s');;
								$shipmentData['shipments']['seller_gst_tin'] = '27AACCW7360C1ZD';

								//return
								$shipmentData['shipments']['return_state'] = 'Maharashtra';
								$shipmentData['shipments']['return_city'] = 'Mumbai';
								$shipmentData['shipments']['return_country'] = 'India';
								$shipmentData['shipments']['return_pin'] = '400092';
								$shipmentData['shipments']['return_name'] = 'WHUSO Ecommerce Solutions Pvt. Ltd.';
								$shipmentData['shipments']['return_add'] = '209, Lalji`s Shopping Center, S.V. Road , Lalji`s Shopping Center, Opp. Indraprastha Shopping Center, Borivali West';
								// $shipmentData['shipments']['waybill']=;
								// $shipmentData['shipments']['order_date']=date('Y-m-d H:i:s'); // shipping date
								// $shipmentData['shipments']['document_date']=date('Y-m-d H:i:s');

								$pickup['name'] = 'Z-weardistribution'; // live uncomment
								// $pickup['name']='WHUSO SURFACE';// live comment
								$pickup['city'] = 'Mumbai';
								$pickup['pin'] = '400001';
								$pickup['country'] = 'India';
								$pickup['add'] = 'Shop No 1, Bouna Casa Building, 6, Homji Street, Sir P M Road, Fort, Opp. Kashmir Government Art Emporium,';
								$pickup['state'] = 'Maharashtra';
								$pickup['phone'] = '9594342022';

								$shipmentData['shipments']['qc'] = [];
								//api call
								if ($box_weight) {
									//$apiData=array('test' =>1 );
									if (isset($box_weight) && $box_weight[0]) {
										$boxWeight = $box_weight[0] * 1000;
									}
									$shipmentData['shipments']['weight'] = $box_weight * 1000;
									// $shipmentData['shipments']['weight']=$boxWeight;
									// $shipmentData['shipments']['qc']=$productItem;
									// array_push($shipmentData['shipments']['qc'],$productItem);
									// $shipmentData['shipments']['qc']=$productItem;
									$shipmentData['shipments']['qc'] = $productItem;
									array_push($shipmentDataObj['shipments'], $shipmentData['shipments']);
									// array_push($shipmentDataObj['shipments'],$productItem);
									// array_push($shipmentDataObj['shipments'],$shipmentData['shipments']);
									$shipmentDataObj['pickup_location'] = $pickup;
									// array_push($shipmentDataObj['shipments']['qc'],$productItem);
									/*print_r($shipmentDataObj);
											exit();*/
									$apiReturnData = $this->delhiveryApi($shipmentApiUrl, $shipmentApiToken, $shipmentDataObj);

									if ($apiReturnData) {
										// print_r($apiReturnData);
										$apiResponseData = json_decode($apiReturnData);
										// $apiResponseData->packages[0]
										$apiStatus = $apiResponseData->packages[0]->status; //api status
										if ($apiStatus == 'Success') {
											$tracking_id = $apiResponseData->packages[0]->waybill;
											$tracking_url = $shipmentTrackingUrl . $tracking_id;
											//$tracking_id
										}
										// $apiResponseData->packages[0]->remarks[0] // error remark
										//$waybill=$apiResponseData->packages[0]->waybill;
										//print_r($apiResponseData->packages[0]->remarks[0]);
									}
								}
							}
							//end api call

						}
					} elseif (isset($paymentMethodShopflag2) && $paymentMethodShopflag2->payment_method != 'cod' && $shipment_id == 3) {

						//exit();
						//$shipment_id==3(Delivery LR)
						if (isset($FbcUser->shop_flag) && $FbcUser->shop_flag == 2) {
							$shipingApidata = $this->ShopProductModel->getSingleDataByID('shipment_master', array('id' => $shipment_id), 'api_details');
							$shipmentApiUrl = '';
							$shipmentApiToken = '';
							$shipmentTrackingUrl = '';

							if (isset($shipingApidata) && !empty($shipingApidata)) {
								$shipingApiDetails = json_decode($shipingApidata->api_details);
								if (isset($shipingApiDetails) && !empty($shipingApiDetails)) {
									$shipmentApiUrl = $shipingApiDetails->api_url;
									$shipmentApiToken = $shipingApiDetails->api_token;
									$shipmentTrackingUrl = $shipingApiDetails->tracking_url;
								}
								//json_decode($jsonobj)
							}
							// webshop data
							$webshopOrderData = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '*');
							$webshopOrderInvoiceData = $this->ShopProductModel->getSingleDataByID('invoicing', array('invoice_order_nos' => $webshop_order_id, 'invoice_order_type' => 1), '*');
							// shipping mobile number
							$mobile_number_shipping_data = $this->ShopProductModel->getSingleShopDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), 'mobile_no');
							$mobile_number_shipping = $mobile_number_shipping_data->mobile_no;
							//end shipping mobile number


							// webshop price
							$webshopProductPrice = 0;
							$webshopTaxValue = $webshopOrderInvoiceData->invoice_tax; // invoice tax
							$webshopTotalAmount = 0;
							$boxWeight = 0;
							$productQty = 0;
							$webshopCodAmount = 0;
							$productCategoryDetails = '';
							//$shipmentData['shipments']
							$shipmentDataObj = array();
							$shipmentDataObj['shipments'] = array();
							$shipmentDataObj['pickup_location'] = array();
							// $shipmentDataObj['shipments']['qc']=array();

							//end webshop price
							if (isset($webshopOrderData) && !empty($webshopOrderData) && isset($webshopOrderInvoiceData) && !empty($webshopOrderInvoiceData)) {

								$productDesc = '';
								// webshop invoicing item data
								$webshopOrderInvoiceItemData = $this->ShopProductModel->getMultiDataById('invoicing_details', array('invoice_id' => $webshopOrderInvoiceData->id), '*');
								foreach ($webshopOrderInvoiceItemData as $itemkey => $itemvalue) {
									//print_r($itemvalue);

									if ($itemvalue->product_id > 0) {
										$WebshopOrderProduct_id = $itemvalue->product_id;
										$WebshopOrderProduct_name = $itemvalue->product_name;
										$WebshopOrderProduct_hns_code = $itemvalue->product_hsn_code;
										$WebshopOrderProduct_Qty = $itemvalue->product_qty;
										// $WebshopOrderProduct_hns_code=$webshopOrderInvoiceData->product_hsn_code;
										// webshop product price
										$webshopProductPriceData = $this->ShopProductModel->getSingleDataByID('products', array('id' => $WebshopOrderProduct_id), 'webshop_price');
										if (isset($webshopProductPriceData) && $webshopProductPriceData->webshop_price) {
											$webshopPrice = $webshopProductPriceData->webshop_price;
											$webshopProductPrice += ($webshopPrice * $WebshopOrderProduct_Qty);
										}
										// total product qty
										$productQty += $WebshopOrderProduct_Qty;


										//category
										$webshopProductCategoryId = $this->ShopProductModel->getSingleDataByID('products_category', array('product_id' => $WebshopOrderProduct_id), 'category_ids');
										//end category
										if (isset($webshopProductCategoryId) && $webshopProductCategoryId->category_ids) {

											// main data base
											$productCategory = $this->CommonModel->getSingleDataByID('category', array('id' => $webshopProductCategoryId->category_ids), '*');
											// print_r($productCategory);
											$productCategoryDetails = $productCategory->cat_name;
											//end main db
										}
										// product item details
										// $productItem1['item']=array();
										$productItem['item'] = array();
										$productItem1['descr'] = $WebshopOrderProduct_name;
										$productItem1['pcat'] = $productCategoryDetails;
										$productItem1['item_quantity'] = $WebshopOrderProduct_Qty;
										array_push($productItem['item'], $productItem1);

										$variant_data = '';
										if (isset($itemvalue->product_variants) && $itemvalue->product_variants != '') {
											$variants = json_decode($itemvalue->product_variants, true);
											if (isset($variants) && count($variants) > 0) {
												foreach ($variants as $pk => $single_variant) {
													if ($pk > 0) {
														$variant_data .= ', ';
													}
													foreach ($single_variant as $key => $val) {
														//$variant_data.='-'.$val;
														$variant_data .= ' ' . $key . ' - ' . $val;
													}
												}
											}
										}
										if ($variant_data) {
											$variant_data = '(' . $variant_data . ')';
										}
										$productNameVariets = $WebshopOrderProduct_name . $variant_data;
										$productDesc .= $productNameVariets . ',';
										//$productDesc.=$WebshopOrderProduct_name;
									}
								}
								if (!empty($productDesc)) {
									$productDesc = rtrim($productDesc, ',');
								}
								// print_r($productDesc);
								$shipmentData['shipments']['products_desc'] = $this->CommonModel->specialCharatcterRemove($productDesc);
								// end webshop invoicing item data
								//print_r($shipmentData);exit();
								// api data
								$shipmentData['shipments']['add'] = $webshopOrderInvoiceData->ship_address_line1 . ',' . $webshopOrderInvoiceData->ship_address_line2;
								$shipmentData['shipments']['phone'] = $mobile_number_shipping;
								// $shipmentData['shipments']['payment_mode']='COD';//old
								$shipmentData['shipments']['payment_mode'] = 'Prepaid';
								$shipmentData['shipments']['name'] = $webshopOrderInvoiceData->customer_first_name . ' ' . $webshopOrderInvoiceData->customer_last_name;
								$shipmentData['shipments']['pin'] = $webshopOrderInvoiceData->ship_pincode;
								$shipmentData['shipments']['order'] = $webshopOrderData->order_barcode . '-' . $OrderData->order_barcode;

								$shipmentData['shipments']['city'] = $webshopOrderInvoiceData->ship_city;
								$shipmentData['shipments']['state'] = $webshopOrderInvoiceData->ship_state;
								$shipmentData['shipments']['country'] = $webshopOrderInvoiceData->ship_country;

								// invoice grand total
								$WebshopInvoiceTotal = $webshopOrderInvoiceData->invoice_grand_total;
								$webshopCodAmount = $WebshopInvoiceTotal; // pending check payment and all condition


								// $shipmentData['shipments']['cod_amount']=$webshopCodAmount;
								$shipmentData['shipments']['commodity_value'] = $webshopProductPrice;
								// $shipmentData['shipments']['tax_value']=300;
								$shipmentData['shipments']['tax_value'] = $webshopTaxValue;
								$webshopTotalAmount = $webshopProductPrice + $webshopTaxValue;
								$shipmentData['shipments']['total_amount'] = $webshopTotalAmount;


								//item data

								$shipmentData['shipments']['quantity'] = $productQty;

								// product details
								//$qc=$productItem;
								//end product details

								//end item data

								// seller details
								$shipmentData['shipments']['seller_name'] = 'WHUSO SURFACE';
								$shipmentData['shipments']['seller_add'] = 'A/103 1ST PUSHPAKUNJ VAIBHAV CHS LTD SV ROAD BEHIND DIGAMBER JAIN TEMPLE BORIVALI WEST Maharashtra 400092 , Mumbai, MAHARASHTRA ,India 400092';
								$shipmentData['shipments']['seller_inv'] = $webshopOrderInvoiceData->invoice_no;
								$shipmentData['shipments']['seller_inv_date'] = '';
								if (isset($webshopOrderInvoiceData->invoice_date) && $webshopOrderInvoiceData->invoice_date != '') {
									$invDate = date(DATE_PIC_FM, $webshopOrderInvoiceData->invoice_date);
									$shipmentData['shipments']['seller_inv_date'] = date('Y-m-d H:i:s', strtotime($invDate));
								}
								$shipmentData['shipments']['seller_gst_tin'] = '27AACCW7360C1ZD';

								//return
								$shipmentData['shipments']['return_state'] = 'Maharashtra';
								$shipmentData['shipments']['return_city'] = 'Mumbai';
								$shipmentData['shipments']['return_country'] = 'India';
								$shipmentData['shipments']['return_pin'] = '400092';
								$shipmentData['shipments']['return_name'] = 'WHUSO Ecommerce Solutions Pvt. Ltd.';
								$shipmentData['shipments']['return_add'] = '209, Lalji`s Shopping Center, S.V. Road , Lalji`s Shopping Center, Opp. Indraprastha Shopping Center, Borivali West';
								// $shipmentData['shipments']['waybill']=;

								//$shipmentData['shipments']['order_date']=date('Y-m-d H:i:s'); // shipping date

								$pickup['name'] = 'Z-weardistribution'; // live uncomment
								// $pickup['name']='WHUSO SURFACE';// live comment
								$pickup['city'] = 'Mumbai';
								$pickup['pin'] = '400001';
								$pickup['country'] = 'India';
								$pickup['add'] = 'Shop No 1, Bouna Casa Building, 6, Homji Street, Sir P M Road, Fort, Opp. Kashmir Government Art Emporium,';
								$pickup['state'] = 'Maharashtra';
								$pickup['phone'] = '9594342022';


								/*old*/
								/*$pickup['name']='WHUSO Ecommerce Solutions Pvt. Ltd.';// live uncomment
											// $pickup['name']='WHUSO SURFACE';// live comment
											$pickup['city']='Mumbai';
											$pickup['pin']='400092';
											$pickup['country']='India';
											$pickup['add']='209, Lalji`s Shopping Center, S.V. Road , Lalji`s Shopping Center, Opp. Indraprastha Shopping Center, Borivali West';
											$pickup['state']='Maharashtra';
											$pickup['phone']='9867444136';*/

								/*end old*/


								$shipmentData['shipments']['qc'] = [];
								//api call
								/*if(count($box_weight) > 1){

										}else{*/
								if ($box_weight) {
									//$apiData=array('test' =>1 );
									if (isset($box_weight) && $box_weight[0]) {
										$boxWeight = $box_weight[0] * 1000;
									}
									$shipmentData['shipments']['weight'] = $boxWeight;
									// $shipmentData['shipments']['qc']=$productItem;
									// array_push($shipmentData['shipments']['qc'],$productItem);
									// $shipmentData['shipments']['qc']=$productItem;
									$shipmentData['shipments']['qc'] = $productItem;
									array_push($shipmentDataObj['shipments'], $shipmentData['shipments']);
									// array_push($shipmentDataObj['shipments'],$productItem);
									// array_push($shipmentDataObj['shipments'],$shipmentData['shipments']);
									$shipmentDataObj['pickup_location'] = $pickup;
									// array_push($shipmentDataObj['shipments']['qc'],$productItem);
									$apiReturnData = $this->delhiveryApi($shipmentApiUrl, $shipmentApiToken, $shipmentDataObj);

									/*print_r($shipmentDataObj);
												exit();*/
									if ($apiReturnData) {

										$apiResponseData = json_decode($apiReturnData);
										// $apiResponseData->packages[0]
										// print_r($apiResponseData);
										// exit();
										if (isset($apiResponseData) && isset($apiResponseData->packages[0]->status)) {
											$apiStatus = $apiResponseData->packages[0]->status; //api status
											if ($apiStatus == 'Success') {
												$tracking_id = $apiResponseData->packages[0]->waybill;
												$tracking_url = $shipmentTrackingUrl . $tracking_id;
												//$tracking_id
											}
										}
										// $apiResponseData->packages[0]->remarks[0] // error remark
										//$waybill=$apiResponseData->packages[0]->waybill;
										//print_r($apiResponseData->packages[0]->remarks[0]);
									}
								}
							}
							//end api call

						}
					} //end else if
				}



				/*echo '<pre>';
					print_r($apiResponseData->error);
					echo '</pre>';
					exit();*/

				// end api start


				/*$insertData=array(
							'order_id'=>$order_id,
							'shipment_id'=>$shipment_id,
							'message'=>$additional_message,
							'created_by'=>$fbc_user_id,
							'created_at'=>time(),
							'ip'=>$_SERVER['REMOTE_ADDR']
					);

					$order_shipment_id=		$this->B2BOrdersModel->insertData('b2b_order_shipment',$insertData);*/

				// foreach($box_weight as $box_val){
				$whereupdateData = array(
					/*'order_id'=>$order_id,
								'order_shipment_id'=>$order_shipment_id,
								'box_number'=>$count,
								'weight'=>$box_val,*/
					'tracking_id' => $tracking_id,
					'tracking_url' => $tracking_url,
					'api_response' => $apiReturnData,
					'updated_by' => $fbc_user_id,
					'updated_at' => time(),

				);
				// $this->B2BOrdersModel->insertData('b2b_order_shipment_details',$insertData);
				$where_shipment_arr = array('id' => $box_id);

				$this->B2BOrdersModel->updateData('b2b_order_shipment_details', $where_shipment_arr, $whereupdateData);
				//print_r();

				// $count++;
				// }
				//}
				//print_r($apiStatus);exit();
				if (isset($apiStatus) && $apiStatus == 'Success') {
					$arrResponse  = array('status' => 200, 'message' => 'Shipment created successfully.', 'order_id' => $order_id);
				} elseif (isset($apiStatus) && $apiStatus == 'Fail') {
					// $apiStatus=$apiResponseData->packages[0]->remarks;
					$apiMsg = $apiStatus;
					if (isset($apiStatus) && isset($apiResponseData->packages[0]->remarks)) {
						$apiMsg = $apiResponseData->packages[0]->remarks;
					}
					$arrResponse  = array('status' => 400, 'message' => $apiMsg, 'order_id' => $order_id);
				} else {
					if ($apiResponseData->error == 1) {
						$arrResponse  = array('status' => 400, 'message' => $apiResponseData->rmk, 'order_id' => $order_id);
					}
				}

				//$arrResponse  = array('status' =>200 ,'message'=>'Shipment created successfully.','order_id'=>$order_id);
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong.');
			echo json_encode($arrResponse);
			exit;
		}
	}
	//end new

	//end manual create shipment

	/*pincode check prepaid delivery api */
	function delhiveryApiPincodePrepaid()
	{
		$webshop_shipping_pincode = $_POST['webshop_shipping_pincode'];
		$webshop_shop_id = $_POST['webshop_shop_id'];
		$shipmentId = $_POST['shipmentId'];

		$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
		$webshop_fbc_user_id = $FbcUser->fbc_user_id;
		$args['shop_id']	=	$webshop_shop_id;
		$args['fbc_user_id']	=	$webshop_fbc_user_id;
		$this->load->model('ShopProductModel');
		$this->ShopProductModel->init($args);

		/*start*/
		if (isset($FbcUser->shop_flag) && ($FbcUser->shop_flag == 2 || $FbcUser->shop_flag == 4)) {
			$shipingApidata = $this->ShopProductModel->getSingleDataByID('shipment_master', array('id' => $shipmentId), 'api_details');
			$shipmentApiPincodeUrl = '';
			$shipmentApiToken = '';
			// $shipmentTrackingUrl='';

			if (isset($shipingApidata) && !empty($shipingApidata)) {
				$shipingApiDetails = json_decode($shipingApidata->api_details);
				if (isset($shipingApiDetails) && !empty($shipingApiDetails)) {
					$shipmentApiPincodeUrl = $shipingApiDetails->pincode_url;
					$shipmentApiToken = $shipingApiDetails->api_token;
					// $shipmentTrackingUrl=$shipingApiDetails->tracking_url;
				}
				//json_decode($jsonobj)
			}
		}
		/*end*/
		// $webshop_shipping_pincode='400000';
		/*print_r($_POST);
		exit();*/
		//echo 'test';exit();
		// $apiPackungUrl='https://staging-express.delhivery.com/api/p/packing_slip?wbns=5220410000254';
		// $apiPackungUrl='https://staging-express.delhivery.com/c/api/pin-codes/json/?filter_codes=400000';
		$apiPincodeUrl = $shipmentApiPincodeUrl . $webshop_shipping_pincode;
		// $apiPackungUrl=$_POST['packing_slip_url'].$_POST['tracking_id'];
		$apiTocken = "authorization: Token " . $shipmentApiToken;
		// $apiTocken="authorization: Token c172fee48668ecf8f265d1a313049b70dbf86a63";
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $apiPincodeUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				$apiTocken,
				"cache-control: no-cache"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		if ($err) {
			echo "cURL Error #:" . $err;
			$arrResponse  = array('status' => 400, 'message' => 'Error While Delivery', 'responsData' => $err);
			echo json_encode($arrResponse);
			exit;
		} else {
			$reData = json_decode($response);
			$resPrepaid = 'Prepaid service not available to this ZIP code ' . $webshop_shipping_pincode;
			$status = '400';
			//echo count($response); exit();
			if (isset($reData) && isset($reData->delivery_codes[0]->postal_code->pre_paid)) {
				$rePrepaid = $reData->delivery_codes[0]->postal_code->pre_paid;
				if (isset($rePrepaid) && $rePrepaid == 'Y') {
					$resPrepaid = 'Prepaid service availaible to this zip code' . $webshop_shipping_pincode;
					$status = '200';
				} elseif (isset($rePrepaid) && $rePrepaid == 'N') {
					$resPrepaid = 'Prepaid service not available to this ZIP code ' . $webshop_shipping_pincode;
					$status = '400';
				} else {
					$resPrepaid = 'Prepaid service not available to this ZIP code ' . $webshop_shipping_pincode;
				}
			}
			$arrResponse  = array('status' => $status, 'message' => $resPrepaid, 'responsData' => $response);
			echo json_encode($arrResponse);
			exit;
		}



		//



		// end curl


	}
	/*end pincode check prepaid*/

	function printshipmentlabel_table()
	{

		$order_id = $_POST['order_id'];
		$order_shipment_id = $_POST['order_shipment_id'];

		if (isset($order_id) && $order_id > 0) {
			$_data['temp_order_id'] = $order_id = $_POST['order_id'];
			$_data['order_shipment_id'] = $order_shipment_id;

			$this->session->set_userdata($_data);

			$arrResponse  = array('status' => 200, 'message' => '', 'order_id' => $order_id, 'order_shipment_id' => $order_shipment_id);
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Something went wrong1082');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function orderprintlabel_table()
	{
		$order_id = $this->uri->segment(4);
		$order_shipment_id = $this->uri->segment(5);

		if (isset($order_id) && $order_id > 0) {
			$shop_id		=	$this->session->userdata('ShopID');
			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			if (empty($OrderData)) {
				redirect('/b2b/orders');
			}

			$main_order_id = $order_id;
			if ($OrderData->parent_id != '' && $OrderData->parent_id > 0) {

				$main_order_id = $OrderData->main_parent_id;
			}

			$MainOrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $main_order_id), '');

			if ($MainOrderData->shipment_type == 2) {

				if (isset($MainOrderData->webshop_order_id)  && $MainOrderData->webshop_order_id > 0) {
					$webshop_order_id = $MainOrderData->webshop_order_id;
					$webshop_shop_id = $MainOrderData->shop_id;

					$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $webshop_shop_id), '');
					$webshop_fbc_user_id = $FbcUser->fbc_user_id;

					$args['shop_id']	=	$webshop_shop_id;
					$args['fbc_user_id']	=	$webshop_fbc_user_id;

					$this->load->model('ShopProductModel');
					$this->ShopProductModel->init($args);

					$data['ShippingAddress'] = $ShippingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), '');
					$data['BillingAddress'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 1), '');

					$data['FormattedAddress'] = $this->ShopProductModel->getFormattedAddress($ShippingAddress);
				}
			}

			$data['b2b_order_shipment_details'] = $this->B2BOrdersModel->get_b2b_order_shipment_details($order_id, $order_shipment_id);
			$data['temp_additional_all'] = $this->B2BOrdersModel->get_b2b_order_shipment($order_id, $order_shipment_id);
			$data['temp_additional_message'] = $data['temp_additional_all']->message;
			// print_r($data['temp_additional_message']->message);die();

			$data['OrderItems'] = $OrderItems = $this->B2BOrdersModel->getQtyPartialOrFullScannedOrderItems($order_id);
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);
			$this->load->view('b2b/order/order-print-label_table', $data);
		} else {
			redirect('/b2b/orders');
		}
	}

	function deliveryB2BAPiProcessData($b2b_order_id, $B2B_order_barcode, $shipment_id, $webshop_order_id, $box_weight, $apiPaymentMode)
	{
		$returnData = [];
		$shipingApidata = $this->ShopProductModel->getSingleDataByID('shipment_master', array('id' => $shipment_id), 'api_details');
		$shipmentApiUrl = '';
		$shipmentApiToken = '';
		$shipmentTrackingUrl = '';
		/*start delhivery required data static to dynamic*/
		$shipments_seller_name = '';
		$shipments_seller_add = '';
		$shipments_seller_gst_tin = '';
		$shipments_return_state = '';
		$shipments_return_city = '';
		$shipments_return_country = '';
		$shipments_return_pin = '';
		$shipments_return_name = '';
		$shipments_return_add = '';
		$shipments_pickup_name = '';
		$shipments_pickup_city = '';
		$shipments_pickup_pin = '';
		$shipments_pickup_country = '';
		$shipments_pickup_add = '';
		$shipments_pickup_state = '';
		$shipments_phone = '';
		/*end delhivery required data static to dynamic*/
		if (isset($shipingApidata) && !empty($shipingApidata)) {
			$shipingApiDetails = json_decode($shipingApidata->api_details);
			if (isset($shipingApiDetails) && !empty($shipingApiDetails)) {
				$shipmentApiUrl = $shipingApiDetails->api_url;
				$shipmentApiToken = $shipingApiDetails->api_token;
				$shipmentTrackingUrl = $shipingApiDetails->tracking_url;
				/*start delhivery required data static to dynamic*/
				$shipments_seller_name = $shipingApiDetails->shipments->seller_name ?? '';
				$shipments_seller_add = $shipingApiDetails->shipments->seller_add ?? '';
				$shipments_seller_gst_tin = $shipingApiDetails->shipments->seller_gst_tin ?? '';
				$shipments_return_state = $shipingApiDetails->shipments->return_state ?? '';
				$shipments_return_city = $shipingApiDetails->shipments->return_city ?? '';
				$shipments_return_country = $shipingApiDetails->shipments->return_country ?? '';
				$shipments_return_pin = $shipingApiDetails->shipments->return_pin ?? '';
				$shipments_return_name = $shipingApiDetails->shipments->return_name ?? '';
				$shipments_return_add = $shipingApiDetails->shipments->return_add ?? '';
				$shipments_pickup_name = $shipingApiDetails->shipments->pickup_name ?? '';
				$shipments_pickup_city = $shipingApiDetails->shipments->pickup_city ?? '';
				$shipments_pickup_pin = $shipingApiDetails->shipments->pickup_pin ?? '';
				$shipments_pickup_country = $shipingApiDetails->shipments->pickup_country ?? '';
				$shipments_pickup_add = $shipingApiDetails->shipments->pickup_add ?? '';
				$shipments_pickup_state = $shipingApiDetails->shipments->pickup_state ?? '';
				$shipments_pickup_phone = $shipingApiDetails->shipments->phone ?? '';
				/*end delhivery required data static to dynamic*/
			}
		}
		// webshop data
		$webshopOrderData = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $webshop_order_id), '*');

		if ($webshopOrderData->main_parent_id > 0) {
			$order_main_parent_id = $webshopOrderData->main_parent_id;
		} else {
			$order_main_parent_id = $webshop_order_id;
		}
		$shipping_data = $this->ShopProductModel->getSingleShopDataByID('sales_order_address', array('order_id' => $order_main_parent_id, 'address_type' => 2), '*');

		$mobile_number_shipping = $shipping_data->mobile_no;
		$ship_address_line1 = $shipping_data->address_line1;
		$ship_address_line2 = $shipping_data->address_line2;
		$ship_pincode = $shipping_data->pincode;
		$ship_city = $shipping_data->city;
		$ship_state = $shipping_data->state;
		$ship_country = $shipping_data->country;

		$webshopProductPrice = 0;
		$webshopTaxValue = $webshopOrderData->tax_amount; // tax
		$webshopTotalAmount = 0;
		$boxWeight = 0;
		$productQty = 0;
		$webshopCodAmount = 0;
		$productCategoryDetails = '';
		$B2bProductscanned_QTY = 0;
		//$shipmentData['shipments']
		$shipmentDataObj = array();
		$shipmentDataObj['shipments'] = array();
		$shipmentDataObj['pickup_location'] = array();
		// $shipmentDataObj['shipments']['qc']=array();

		//end webshop price

		if (isset($webshopOrderData) && !empty($webshopOrderData)) {
			$productDesc = '';
			// webshop invoicing item data
			//$webshopOrderInvoiceItemData=$this->ShopProductModel->getMultiDataById('invoicing_details',array('invoice_id'=>$webshopOrderInvoiceData->id),'*');
			$webshopOrderItemData = $this->ShopProductModel->getMultiDataById('sales_order_items', array('order_id' => $webshop_order_id), '*');
			foreach ($webshopOrderItemData as $itemkey => $itemvalue) {
				//print_r($itemvalue);
				if ($itemvalue->product_id > 0) {
					$WebshopOrderProduct_id = $itemvalue->product_id;
					$WebshopOrderProduct_name = $itemvalue->product_name;
					$WebshopOrderProduct_sku = $itemvalue->sku;
					// $WebshopOrderProduct_Qty=$itemvalue->qty_scanned;
					$WebshopOrderProduct_Qty = $itemvalue->qty_ordered;
					$WebshopOrderProduct_price = $itemvalue->price;
					// webshop product price
					$B2bProductscannedQTY = $this->B2BOrdersModel->getSingleDataByID('b2b_order_items', array('order_id' => $b2b_order_id, 'sku' => $WebshopOrderProduct_sku), 'qty_scanned');
					if (isset($B2bProductscannedQTY) && $B2bProductscannedQTY->qty_scanned) {
						$B2bProductscanned_QTY = $B2bProductscannedQTY->qty_scanned;
					}
					// total product qty
					$productQty += $WebshopOrderProduct_Qty;
					$webshopProductPrice += ($WebshopOrderProduct_price * $B2bProductscanned_QTY);

					//category
					$webshopProductCategoryId = $this->ShopProductModel->getSingleDataByID('products_category', array('product_id' => $WebshopOrderProduct_id), 'category_ids');
					//end category
					if (isset($webshopProductCategoryId) && $webshopProductCategoryId->category_ids) {

						// main data base
						$productCategory = $this->CommonModel->getSingleDataByID('category', array('id' => $webshopProductCategoryId->category_ids), '*');
						// print_r($productCategory);
						$productCategoryDetails = $productCategory->cat_name;
						//end main db
					}
					// product item details
					// $productItem1['item']=array();
					$productItem['item'] = array();
					$productItem1['descr'] = $WebshopOrderProduct_name;
					// $productItem1['pcat']='Apparel';
					$productItem1['pcat'] = $productCategoryDetails;
					$productItem1['item_quantity'] = $WebshopOrderProduct_Qty;
					array_push($productItem['item'], $productItem1);

					$variant_data = '';
					if (isset($itemvalue->product_variants) && $itemvalue->product_variants != '') {
						$variants = json_decode($itemvalue->product_variants, true);
						if (isset($variants) && count($variants) > 0) {
							foreach ($variants as $pk => $single_variant) {
								if ($pk > 0) {
									$variant_data .= ', ';
								}
								foreach ($single_variant as $key => $val) {
									//$variant_data.='-'.$val;
									$variant_data .= ' ' . $key . ' - ' . $val;
								}
							}
						}
					}
					if ($variant_data) {
						$variant_data = '(' . $variant_data . ')';
					}
					$productNameVariets = $WebshopOrderProduct_name . $variant_data;
					$productDesc .= $productNameVariets . ',';
				}
			}

			if (!empty($productDesc)) {
				$productDesc = rtrim($productDesc, ',');
			}

			$shipmentData['shipments']['products_desc'] = $this->CommonModel->specialCharatcterRemove($productDesc);
			// api data
			$shipmentData['shipments']['add'] = str_replace(';', ',', $ship_address_line1) . ',' . str_replace(';', ',', $ship_address_line2);
			$shipmentData['shipments']['phone'] = $mobile_number_shipping;
			$shipmentData['shipments']['payment_mode'] = $apiPaymentMode;
			$shipmentData['shipments']['name'] = $webshopOrderData->customer_firstname . ' ' . $webshopOrderData->customer_lastname;
			$shipmentData['shipments']['pin'] = $ship_pincode;
			$shipmentData['shipments']['order'] = $webshopOrderData->order_barcode . '-' . $B2B_order_barcode;

			$shipmentData['shipments']['city'] = $ship_city;
			$shipmentData['shipments']['state'] = $ship_state;
			$shipmentData['shipments']['country'] = $ship_country;
			// invoice grand total
			$WebshopInvoiceTotal = $webshopOrderData->grand_total;
			$webshopCodAmount = $WebshopInvoiceTotal; // pending check payment and all condition

			$shipmentData['shipments']['cod_amount'] = $webshopCodAmount;
			$shipmentData['shipments']['commodity_value'] = $webshopProductPrice;
			// $shipmentData['shipments']['tax_value']=300;
			$shipmentData['shipments']['tax_value'] = $webshopTaxValue;
			$webshopTotalAmount = $webshopProductPrice;
			$shipmentData['shipments']['total_amount'] = $webshopTotalAmount;

			//item data
			$shipmentData['shipments']['quantity'] = $productQty;

			// seller details
			$shipmentData['shipments']['seller_name'] = $shipments_seller_name;
			$shipmentData['shipments']['seller_add'] = $shipments_seller_add;

			$shipmentData['shipments']['seller_inv'] = $webshopOrderData->increment_id;
			$shipmentData['shipments']['seller_inv_date'] = date('Y-m-d H:i:s');

			$shipmentData['shipments']['seller_gst_tin'] = $shipments_seller_gst_tin; // change now whuso
			//end seller details
			//return
			$shipmentData['shipments']['return_state'] = $shipments_return_state;
			$shipmentData['shipments']['return_city'] = $shipments_return_city;
			$shipmentData['shipments']['return_country'] = $shipments_return_country;
			$shipmentData['shipments']['return_pin'] = $shipments_return_pin;
			$shipmentData['shipments']['return_name'] = $shipments_return_name;
			$shipmentData['shipments']['return_add'] = $shipments_return_add;
			// end return
			// pickup address
			$pickup['name'] = $shipments_pickup_name;
			$pickup['city'] = $shipments_pickup_city;
			$pickup['pin'] = $shipments_pickup_pin;
			$pickup['country'] = $shipments_pickup_country;
			$pickup['add'] = $shipments_pickup_add;
			$pickup['state'] = $shipments_pickup_state;
			$pickup['phone'] = $shipments_pickup_phone;
			// end pickup address

			$shipmentData['shipments']['qc'] = [];
			//api call
			if (count($box_weight) > 1) {
			} else {
				if (isset($box_weight) && $box_weight[0]) {
					$boxWeight = $box_weight[0] * 1000;
				}
				$shipmentData['shipments']['weight'] = $boxWeight;
				$shipmentData['shipments']['qc'] = $productItem;
				array_push($shipmentDataObj['shipments'], $shipmentData['shipments']);
				$shipmentDataObj['pickup_location'] = $pickup;
				$apiReturnData = $this->delhiveryApi($shipmentApiUrl, $shipmentApiToken, $shipmentDataObj);
				if ($apiReturnData) {
					$tracking_id = '';
					$tracking_url = '';
					$apiResponseData = json_decode($apiReturnData);
					$apiStatus = $apiResponseData->packages[0]->status; //api status
					if ($apiStatus == 'Success') {
						$tracking_id = $apiResponseData->packages[0]->waybill;
						$tracking_url = $shipmentTrackingUrl . $tracking_id;
					}
					$returnData = array('apiReturnData' => $apiReturnData, 'apiResponseData' => $apiResponseData, 'tracking_id' => $tracking_id, 'tracking_url' => $tracking_url);
				}
			}
		}
		return $returnData;
	}
	function InitiateOrderPopup()
	{
		if (isset($_POST['order_id'])) {

			$data['order_id'] = $_POST['order_id'];
			$data['increment_id'] = $_POST['inc_id'];
			$data['publisher_id'] = $_POST['publisher_id'];
			// print_r($_POST['publisher_id']);
			// 			die;

			$data['OrderData'] = $OrderItemData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $_POST['order_id'], 'increment_id' => $_POST['inc_id']), '');
			// echo "<pre>";
			// print_r($data['OrderData']);
			// die;
			$webshop_order_id = '';

			$webshop_order_id = '';

			if (is_object($OrderItemData) && property_exists($OrderItemData, 'webshop_order_id')) {
				$webshop_order_id = $OrderItemData->webshop_order_id;
			} else {

			}

			// print_r($webshop_order_id);
			// die;

			$data['get_products'] = $get_products = $this->B2BOrdersModel->get_order_Product($webshop_order_id);
			// echo "<pre>";
			// print_r($data['get_products']);
			// die;

			// foreach ($get_products as $item) {
			// 	// echo "<pre>";
			// 	// print_r($item);
			// 	// die;
			// 	$product_id = $item['product_id'];
			// 	$id = $item['id'];
			// 	$order_ids = $item['order_id'];
			// 	$publisher_id = $item['publisher_id'];
			// 	// print_r($product_id);
			// 	// Process $product_id as needed


			// 	$data['get_order_details'] = $get_order_details = $this->B2BOrdersModel->get_order_details($product_id, $id, $order_ids, $publisher_id);
			// }

			// echo "<pre>";
            // print_r($get_order_details);
            // die;

			$data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getSingleDataByID('publisher', array('id' => $OrderItemData->publisher_id), '');
			$data['PublisherPayemntData'] = $PublisherPaymentData = $this->B2BOrdersModel->getSingleDataByID('publisher_payment_details', array('publisher_id' => $OrderItemData->publisher_id), '');

			$View = $this->load->view('b2b/order/initiate-payment-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function InitiateOrder()
	{
		// print_R($_POST);
		// die();
		if (isset($_POST['hidden_order_id'])) {
			$User_id = $this->session->userdata('LoginID');
			$data['order_id'] =  $order_id = $_POST['hidden_order_id'];
			$data['increment_id'] = $increment_id =  $_POST['hidden_b2b_order_id'];
			$beneficiary_acc_no = $_POST['bene_acc_no'];
			$beneficiary_ifsc = $_POST['bene_ifsc_code'];
			$beneficiary_name = $_POST['beneficiary_name'];
			$payment_mod = $_POST['status'];
			$publisher_id = $_POST['hidden_publisher_id'];
			$amount_payable	 = str_replace(',', '', $_POST['amount_payable']);
			$remarks = $_POST['remarks'];

			$data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getSingleDataByID('publisher', array('id' => $publisher_id), '');
			$data['B2BOrderDetails'] = $B2BOrderDetails = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('sales_order', array('order_id' => $B2BOrderDetails->webshop_order_id), '');
			// echo $this->db->last_query();
			$add_publisher_payment_info  = $this->B2BOrdersModel->insertData('publisher_payment', array('order_id' => $order_id, 'B2b_order_id' => $increment_id, 'publisher_id' => $publisher_id, 'beneficiary_acc_no' => $beneficiary_acc_no, 'beneficiary_ifsc' => $beneficiary_ifsc, 'beneficiary_name' => $beneficiary_name, 'remarks' => $remarks, 'amount_payable' => $amount_payable, 'payment_initiated' => 1, 'payment_initiated_at' => time(), 'created_at' => time(), 'created_by' => $User_id, 'payment_mod' => $payment_mod));

			if ($add_publisher_payment_info) {

				$Payment_info_table = '';
				$Payment_info_table .= '<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #eaeaea; font-family:Arial,Helvetica,sans-serif;font-size:13px">
					<thead>
						<tr>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">B2B Order Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Publisher Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Acc No</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary IFSC</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Name</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Amount Payable</th>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $order_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $increment_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $publisher_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_acc_no . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_ifsc . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_name . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $amount_payable . '</td>
					</tr>
					</tbody>
				</table>';

				// $shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
				// $webshop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);
				$webshop_details = $this->CommonModel->get_webshop_details();
				$owner_email = ADMIN_EMAILS;
				$shop_name = 'Indiamags';
				$templateId = 'request_a_payment_for_order';
				// $to = 'snehals@bcod.co.in';
				$to = $owner_email;

				$site_logo = '';
				// if (isset($webshop_details)) {
				// 	$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				// } else {
				// 	$shop_logo = '';
				// }
				$burl = base_url();
				$shop_logo = ''; // get_s3_url($shop_logo, $shop_id);

				$site_logo =  '';
				'<a href="' . SITE_URL . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . SITE_LOGO . '" style="max-width:200px" /></a>';
				$username = 'indiamags';
				$TempVars = array();
				$DynamicVars = array();

				$TempVars = array("##PAYMENTINFOTABLE##,##ORDERID##,##WEBSHOPNAME##");
				$DynamicVars   = array($Payment_info_table, $OrderData->increment_id, $shop_name);
				$CommonVars = array($site_logo, $shop_name);
				$SubDynamic = array($publisherdetails->publication_name, $increment_id);

				if (isset($templateId)) {
					$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
					// echo "hiii" . $emailSendStatusFlag;
					if ($emailSendStatusFlag == 1) {

						$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars, '');
						// var_dump($mailSent);
						// print_r($mailSent);
					}
					// die;
				}

				$arrResponse  = array('status' => 200, 'message' => "Payment initiated successfully.");
				echo json_encode($arrResponse);
			} else {
				$arrResponse  = array('status' => 500, 'message' => "Something went wrong");
				echo json_encode($arrResponse);
			}

			exit;



			// $View = $this->load->view('b2b/order/initiate-payment-popup', $data, true);
			// $this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function ProceedPaymentPopup()
	{
		if (isset($_POST['id'])) {

			$data['PublisherPayemntData'] = $PublisherPaymentData = $this->B2BOrdersModel->getSingleDataByID('publisher_payment', array('id' => $_POST['id']), '');
			// print_R($data['PublisherPayemntData']);die();
			// $data['order_id'] = $_POST['order_id'];
			// $data['increment_id'] = $_POST['inc_id'];


			// $data['OrderData'] = $OrderItemData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $_POST['order_id'], 'increment_id' => $_POST['inc_id']), '');
			// $data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getSingleDataByID('publisher', array('id' => $OrderItemData->publisher_id), '');

			$View = $this->load->view('b2b/order/proceed-payment-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}
	function PaymentDone()
	{
		// print_R($_POST);
		// die();
		if (isset($_POST['id'])) {
			$User_id = $this->session->userdata('LoginID');
			$data['id'] =  $order_id = $_POST['hidden_order_id'];
			$data['hidden_b2b_order_id'] =  $increment_id = $_POST['hidden_b2b_order_id'];
			$data['hidden_publisher_id'] =  $publisher_id = $_POST['hidden_publisher_id'];
			$data['bene_acc_no'] =  $beneficiary_acc_no = $_POST['bene_acc_no'];
			$data['beneficiary_name'] =  $beneficiary_name = $_POST['beneficiary_name'];
			$data['bene_ifsc_code'] =  $beneficiary_ifsc = $_POST['bene_ifsc_code'];
			$data['amount_payable'] =  $amount_payable = $_POST['amount_payable'];
			$publisher_id = $_POST['hidden_publisher_id'];
			$data['utr_no'] =  $utr_no = $_POST['utr_no'];

			$data['payment_date'] = $payment_date =	$_POST['payment_date'];
			$convert_payment_date = strtotime(str_replace('/', '-', $data['payment_date']));

			$data['comments'] =  $comments = $_POST['comments'];

			$data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getSingleDataByID('publisher', array('id' => $publisher_id), '');
			$data['PublisherPayemntData'] = $PublisherPaymentData = $this->B2BOrdersModel->getSingleDataByID('publisher_payment', array('id' => $_POST['id']), '');
			$where_shipment_arr = array('id' => $_POST['id']);
			$data['B2BOrderDetails'] = $B2BOrderDetails = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			$data['B2BOrderItemsDetails'] = $B2BOrderItemsDetails = $this->B2BOrdersModel->getMultiDataById('b2b_order_items', array('order_id' => $order_id), '');
			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('sales_order', array('order_id' => $B2BOrderDetails->webshop_order_id), '');
			$data['OrderItemsData'] = $OrderItemsData = $this->B2BOrdersModel->getMultiDataById('sales_order_items', array('order_id' => $B2BOrderDetails->webshop_order_id), '');
			$data['OrderAddressData'] = $OrderAddressData = $this->B2BOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $B2BOrderDetails->webshop_order_id, 'address_type' => 2), '');

			// echo "<pre>";
			// print_r($B2BOrderItemsDetails);
			// print_r($OrderItemsData);
			// die();
			$add_publisher_payment_info  = $this->B2BOrdersModel->updateData('publisher_payment', $where_shipment_arr, array('payment_done' => 1, 'utr_no' => $_POST['utr_no'], 'payment_done_at' => time(), 'payment_date' => $convert_payment_date, 'comments' => $comments, 'updated_at	' => time(), 'updated_by' => $User_id));
			if ($PublisherPaymentData) {


				$Payment_info_table = '';
				$Payment_info_table .= '<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #eaeaea; font-family:Arial,Helvetica,sans-serif;font-size:13px">
					<thead>
						<tr>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">B2B Order Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Publisher Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Acc No</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary IFSC</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Name</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">UTR No</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Payment Date</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Comments</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Amount Payable</th>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderData->increment_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $increment_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $publisher_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_acc_no . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_ifsc . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_name . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $utr_no . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $payment_date . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $comments . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $amount_payable . '</td>
					</tr>
					</tbody>
				</table>';
				$PublisherOrdertable = '';
				$PublisherOrdertable .= '<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #eaeaea; font-family:Arial,Helvetica,sans-serif;font-size:13px" >
					<thead>
						<tr>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Id</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">B2B Order Id</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Customer Email</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Name</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Company name</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Street</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Zip</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping City</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping State</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Phone Number</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Magazine</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Subscription</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Issues</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Price</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">QTY</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Commission %</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Commission</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Amount Payable</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Free Gift</th>

						</tr>
					</thead>
					<tbody>';
				$total_cover_price = 0;
				$total_amount_payable = 0;
				$total_whuso_income = 0;
				$shipping_charge = $B2BOrderDetails->shipping_amount;
				foreach ($B2BOrderItemsDetails as $keyOID => $valOid) {
					foreach ($OrderItemsData as $keyo => $valo) {
						if ($valOid->product_id ==  $valo->product_id) {

							$productsData = $this->B2BOrdersModel->getSingleDataByID('products', array('id' => $valo->product_id), '');
							// $B2b_items = $this->ShopProductModel->getSingleDataByID('b2b_order_items', array('order_id' => $OrderData->order_id), '');
							$getCategory = $this->B2BOrdersModel->getCategory($valo->parent_product_id);
							$getCategoriesDetails = $this->B2BOrdersModel->getCategoriesDetails($valo->parent_product_id);
            				// $publisherdetails = $this->B2BOrdersModel->getPublisherDetails($valo->publisher_id);
            				// $publication_name = $publisherdetails->publication_name ?? null;
							

							$showCustomerEmail = false;

							if (!empty($getCategoriesDetails)) {
								foreach ($getCategoriesDetails as $catRow) {
									if ((int)$catRow['category_ids'] === 41) {
										$showCustomerEmail = true;
										break; // No need to continue once 41 is found
									}
								}
							}
							
							// Parent ID check
							$allowedParentIds = [225, 4458, 4494, 4477, 4482, 4470, 3939, 612, 621, 2075];
							
							if (in_array((int)$productsData->parent_id, $allowedParentIds)) {
								$showCustomerEmail = true;
							}
							
							// Final email assignment
							$customerEmail = $showCustomerEmail ? $OrderData->customer_email : 'care@indiamags.com';
							// print_r($customerEmail); die; 
							$shipping_charge_product = $productsData->shipping_amount;
							$sub_issue = $valo->sub_issues;
							$cover_price = $valo->price;
							$total_webshop_price = 0;
							
							if (isset($productsData->webshop_price)) {
								$total_webshop_price += (float)$productsData->webshop_price;
							}
							// echo "<pre>";
							// print_r($total_webshop_price); 
							
							// die;
							if ($publisherdetails->publication_name === 'Amar Chitra Katha (Books)') {
								$cover_price=$total_webshop_price;
								$pub_com_percent = 30;
								$cover_price_shipping = $total_webshop_price;
							} elseif($getCategory){
								$cover_price = $total_webshop_price;
								$pub_com_percent = $valo->pub_com_percent;
								$cover_price_shipping = $total_webshop_price; // written on 19-09-24
							}else{
								$cover_price = $valo->price - $shipping_charge_product;
								$pub_com_percent = $valo->pub_com_percent;
								$cover_price_shipping = $valo->price;// written on 19-09-24
							}

							
							
							
							// $cover_price_shipping = $valo->price - $shipping_charge_product; // commented on 18-09-24
							// $cover_price_shipping = $valo->price + $shipping_charge_product; // written on 18-09-24
							// $cover_price_shipping = $valo->price - $shipping_charge;
							
							// $total_cover_price += ($cover_price * $valOid->qty_ordered); // commented on 18-09-24
							$total_cover_price += ($cover_price_shipping * $valOid->qty_ordered); // written on 18-09-24
							
							
							$gift_name = $valo->gift_name;
							$productRowPrice = $cover_price * $valOid->qty_ordered;
					
							// print_r($cover_price_shipping);
							// $whuso_income = (($pub_com_percent  / 100) * ($productRowPrice));  // commented on 18-09-24
							$whuso_income = (($pub_com_percent  / 100) * ($productRowPrice));  // written on 19-09-24

							// echo "<pre>";
							// print_r($whuso_income); 
							// // print_r($cover_price_shipping); 

							// die;
							// $Payable_Amount = ($productRowPrice - $whuso_income) + $shipping_charge_product;  // commented on 18-09-24
							// $Payable_Amount = ($productRowPrice - $whuso_income); // written on 18-09-24
							$Payable_Amount = ($productRowPrice - $whuso_income) + ($shipping_charge_product * $valOid->qty_ordered); // written on 18-09-24
							if($B2BOrderDetails->order_id == '1895' || $B2BOrderDetails->order_id == '1732' || $B2BOrderDetails->order_id == '1739' || $B2BOrderDetails->order_id == '1737' || $B2BOrderDetails->order_id == '1735' || $B2BOrderDetails->order_id == '1979' || $B2BOrderDetails->order_id == '1999'|| $B2BOrderDetails->order_id == '2007' || $B2BOrderDetails->order_id == '2008' || $B2BOrderDetails->order_id == '1805' ||  $B2BOrderDetails->order_id == '1780' ||  $B2BOrderDetails->order_id == '1794'){
								$cover_price_shipping = $valo->price + $shipping_charge_product;
								$whuso_income = (($valo->pub_com_percent / 100) * ($valo->price));
								$Payable_Amount = ($valo->price - $whuso_income) + ($shipping_charge_product * $valOid->qty_ordered);
								$total_cover_price = $valo->price + $shipping_charge_product;
							}
							if($B2BOrderDetails->order_id == '1777' ){
								$cover_price_shipping = $valo->price + $shipping_charge_product;
								$whuso_income = (($valo->pub_com_percent / 100) * ($valo->price));
								$Payable_Amount = ($valo->price - $whuso_income) + ($shipping_charge_product * $valOid->qty_ordered);
								$total_cover_price = $cover_price_shipping + 1520;
								
							}
						
							if($B2BOrderDetails->order_id == '1682'){
								$cover_price_shipping = $valo->price;
								$whuso_income = (($valo->pub_com_percent / 100) * ($valo->price - $shipping_charge_product));
								$Payable_Amount = ($valo->price - $whuso_income);
								$total_cover_price = $valo->price;
							}
							 
							if($B2BOrderDetails->order_id == '1745'){
								$cover_price_shipping = $valo->price + $shipping_charge_product;
								$whuso_income = (($valo->pub_com_percent / 100) * ($valo->price * $valOid->qty_ordered));
								$Payable_Amount = (($valo->price * $valOid->qty_ordered) - $whuso_income) + ($shipping_charge_product * $valOid->qty_ordered);
								$total_cover_price = ($valo->price + $shipping_charge_product) * $valOid->qty_ordered;
							}
							if($B2BOrderDetails->order_id == '3137'){
								$cover_price_shipping=0;
								$shipping_charge_product=0;
								$whuso_income = (($valo->pub_com_percent / 100) * ($valo->price * $valOid->qty_ordered));
								$Payable_Amount = (($valo->price * $valOid->qty_ordered) - $whuso_income) + ($shipping_charge_product * $valOid->qty_ordered);
								$total_cover_price = ($valo->price + $shipping_charge_product) * $valOid->qty_ordered;
							}
							if ($productsData->name === 'Down To Earth Magazine' || $productsData->name === 'Down To Earth Hindi Magazine') {
								$whuso_income = (($valo->pub_com_percent / 100) * ($B2BOrderDetails->grand_total));
								$Payable_Amount = ($B2BOrderDetails->grand_total - $whuso_income) + ($shipping_charge_product * $valOid->qty_ordered);
							}
							$total_whuso_income += $whuso_income;
							// print_r($valo->price + $shipping_charge_product);
							// print_r($valo->price);
							// print_r($whuso_income);
							// print_r($Payable_Amount);
							// print_r($Payable_Amount);

						}
						
					}
					//$shipping_charge = $B2BOrderDetails->shipping_amount;
					$total_grand_ = $B2BOrderDetails->grand_total - $shipping_charge;
					// die;

					/*$whuso_income = (($pub_com_percent  / 100) * ($total_grand_));
					$total_whuso_income += $whuso_income;
					// $whuso_income = (($pub_com_percent  / 100) * $cover_price);
					$Payable_Amount = ($total_grand_ - $whuso_income) + $shipping_charge;
					// $Payable_Amount = $cover_price  - $whuso_income;*/
					//$productRowPrice = $cover_price * $valOid->qty_ordered;
					
					// if($getCategory){
					// }else{
					// 	$Payable_Amount = ($productRowPrice - $whuso_income);// written on 19-09-24
					// }
					// print_r($Payable_Amount);die;
					
					$total_amount_payable += $Payable_Amount;
					$variants = json_decode($valOid->product_variants, true);
					$variant_html = '';
					if (isset($variants) && count($variants) > 0) {
						foreach ($variants as $pk => $single_variant) {
							foreach ($single_variant as $key => $val) {
								$variant_html .= '<span class="variant-item">' . $key . ' - ' . $val . '</span><br>';
							}
						}
					}
					

					$PublisherOrdertable .= '<tr>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderData->increment_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $increment_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $customerEmail . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->first_name . ' ' . $OrderAddressData->last_name . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->company_name . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->address_line1 . ' ' . $OrderAddressData->address_line2 . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->pincode . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->city . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->state . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->mobile_no . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $valOid->product_name . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $variant_html . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $sub_issue . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . ($cover_price_shipping * $valOid->qty_ordered) . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $valOid->qty_ordered . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $pub_com_percent . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $whuso_income . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $Payable_Amount . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $gift_name . '</td>
					</tr>';


				}
				$PublisherOrdertable .= '
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"> </td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $total_cover_price . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $pub_com_percent . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $total_whuso_income . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $total_amount_payable . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				</tbody>
				</table>';
				// $shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
				// $webshop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);
				// echo
				// $PublisherOrdertable;
				// die();
				// print_R($publisherdetails->email);
				// die();
				$webshop_details = $this->CommonModel->get_webshop_details();
				$owner_email = ADMIN_EMAILS;
				$shop_name = 'Indiamags';
				$templateId = 'payment_done_for_order';
				$to = $publisherdetails->email; //$owner_email;
				// $to = 'newdev.bcod@gmail.com';
				// $to = 'snehals@bcod.co.in';

				$cc_emails = $publisherdetails->cc_email; //$owner_email;

				// $cc_emails = 'ronika@indiamags.com'; //$owner_email;

				// echo "<pre>";
				// print_r($to);
				// echo "<pre>";
				// print_r($cc_emails);
				// die;
				$cc = explode(', ', $cc_emails);
				$site_logo = '';
				// if (isset($webshop_details)) {
				// 	$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				// } else {
				// 	$shop_logo = '';
				// }
				$burl = base_url();
				$shop_logo = ''; // get_s3_url($shop_logo, $shop_id);

				$site_logo =  '';
				'<a href="' . SITE_URL . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . SITE_LOGO . '" style="max-width:200px" /></a>';
				$username = 'indiamags';
				$TempVars = array();
				$DynamicVars = array();

				$TempVars = array("##PAYMENTINFOTABLE##,##PUBLISHERORDERTABLE##,##ORDERID##,##WEBSHOPNAME##");
				$DynamicVars   = array($Payment_info_table, $PublisherOrdertable, $OrderData->increment_id, $shop_name);
				$CommonVars = array($site_logo, $shop_name);
				$SubDynamic = array($publisherdetails->publication_name, $increment_id);

				if (isset($templateId)) {
					$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
					if ($emailSendStatusFlag == 1) {
						$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars, $cc);
						// $copy_email_to = 'suzan@indiamags.com'; //'snehals@bcod.co.in'; //'suzan@indiamags.com';
						// $mailSentTo = $this->B2BOrdersModel->sendCommonHTMLEmail($copy_email_to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars, '');
						// $sales_head_email =  "heeral@whuso.in"; //'saroj@bcod.co.in'; //"heeral@whuso.in";
						// $mailSentto_ = $this->B2BOrdersModel->sendCommonHTMLEmail($sales_head_email, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars, '');
					}
				}

				// $arrResponse  = array('status' => 200, 'message' => "Payment initiated successfully.");
				// echo json_encode($arrResponse);
			} else {
				$arrResponse  = array('status' => 500, 'message' => "Something went wrong");
				echo json_encode($arrResponse);
			}

			$arrResponse  = array('status' => 200, 'message' => "Payment done successfully.");
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 500, 'message' => "Something went wrong!!");
			exit;
		}
	}

	/*function PaymentDone_test()
	{

		
		$_POST['id'] = 93;
		if (isset($_POST['id'])) {
			$_POST['hidden_order_id'] = 690;
			// $_POST['id'] = 93;
			$User_id = $this->session->userdata('LoginID');
			// $data['id'] =  $order_id = $_POST['hidden_order_id'];
			// $data['hidden_b2b_order_id'] =  $increment_id = $_POST['hidden_b2b_order_id'];
			// $data['hidden_publisher_id'] =  $publisher_id = $_POST['hidden_publisher_id'];
			// $data['bene_acc_no'] =  $beneficiary_acc_no = $_POST['bene_acc_no'];
			// $data['beneficiary_name'] =  $beneficiary_name = $_POST['beneficiary_name'];
			// $data['bene_ifsc_code'] =  $beneficiary_ifsc = $_POST['bene_ifsc_code'];
			// $data['amount_payable'] =  $amount_payable = $_POST['amount_payable'];
			// $publisher_id = $_POST['hidden_publisher_id'];
			// $data['utr_no'] =  $utr_no = $_POST['utr_no'];



			$data['id'] =  $order_id = $_POST['hidden_order_id'];

			// $data['hidden_b2b_order_id'] =  $increment_id = 'B2B-1229';
			// $data['hidden_publisher_id'] =  $publisher_id = 77;
			// $data['bene_acc_no'] =  $beneficiary_acc_no = '234353453';
			// $data['beneficiary_name'] =  $beneficiary_name = 'bfbfb';
			// $data['bene_ifsc_code'] =  $beneficiary_ifsc = '35345345';
			// $data['amount_payable'] =  $amount_payable = '2142.00';
			// $publisher_id = 77;
			// $data['utr_no'] =  $utr_no = 'djiwenf8900';

			$data['hidden_b2b_order_id'] =  $increment_id = 'B2B-1257';
			$data['hidden_publisher_id'] =  $publisher_id = 77;
			$data['bene_acc_no'] =  $beneficiary_acc_no = '123456789';
			$data['beneficiary_name'] =  $beneficiary_name = 'rajeshtest';
			$data['bene_ifsc_code'] =  $beneficiary_ifsc = 'raj123456';
			$data['amount_payable'] =  $amount_payable = '1090.00';
			$data['utr_no'] =  $utr_no = 'testutrno12345';


			$data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getSingleDataByID('publisher', array('id' => $publisher_id), '');
			$data['PublisherPayemntData'] = $PublisherPaymentData = $this->B2BOrdersModel->getSingleDataByID('publisher_payment', array('id' => $_POST['id']), '');
			$where_shipment_arr = array('id' => $_POST['id']);
			$data['B2BOrderDetails'] = $B2BOrderDetails = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			$data['B2BOrderItemsDetails'] = $B2BOrderItemsDetails = $this->B2BOrdersModel->getMultiDataById('b2b_order_items', array('order_id' => $order_id), '');
			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('sales_order', array('order_id' => $B2BOrderDetails->webshop_order_id), '');
			$data['OrderItemsData'] = $OrderItemsData = $this->B2BOrdersModel->getMultiDataById('sales_order_items', array('order_id' => $B2BOrderDetails->webshop_order_id), '');
			$data['OrderAddressData'] = $OrderAddressData = $this->B2BOrdersModel->getSingleDataByID('sales_order_address', array('order_id' => $B2BOrderDetails->webshop_order_id, 'address_type' => 2), '');




			
			// $add_publisher_payment_info  = $this->B2BOrdersModel->updateData('publisher_payment', $where_shipment_arr, array('payment_done' => 1, 'utr_no' => $_POST['utr_no'], 'payment_done_at' => time(), 'updated_at	' => time(), 'updated_by' => $User_id));
			if ($PublisherPaymentData) {


				$Payment_info_table = '';
				$Payment_info_table .= '<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #eaeaea; font-family:Arial,Helvetica,sans-serif;font-size:13px">
					<thead>
						<tr>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">B2B Order Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Publisher Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Acc No</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary IFSC</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Name</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">UTR No</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Amount Payable</th>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderData->increment_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $increment_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $publisher_id . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_acc_no . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_ifsc . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_name . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $utr_no . '</td>
						<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $amount_payable . '</td>
					</tr>
					</tbody>
				</table>';
				$PublisherOrdertable = '';
				$PublisherOrdertable .= '<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #eaeaea; font-family:Arial,Helvetica,sans-serif;font-size:13px" >
					<thead>
						<tr>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Id</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">B2B Order Id</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Date</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Customer Email</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Name</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Street</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Zip</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping City</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping State</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Phone Number</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Magazine</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Subscription</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Issues</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Price</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">QTY</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Commission %</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Commission</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Amount Payable</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Free Gift</th>

						</tr>
					</thead>
					<tbody>';
				$total_cover_price = 0;
				$total_amount_payable = 0;
				$total_whuso_income = 0;
				//$Payable_Amount = 0;

				$shipping_charge = $B2BOrderDetails->shipping_amount;

				foreach ($B2BOrderItemsDetails as $keyOID => $valOid) {
					foreach ($OrderItemsData as $keyo => $valo) {
						if ($valOid->product_id ==  $valo->product_id) {

							//echo $valo->product_id;
							// $productsData = $this->B2BOrdersModel->getSingleDataByID('products', array('id' => $valo->product_id), '');
							// $shipping_charge_product = $productsData->shipping_amount;

							$sub_issue = $valo->sub_issues;
							$cover_price = $valo->price;
							$cover_price_shipping = $valo->price - $shipping_charge;
							// $cover_price_shipping = $valo->price - $shipping_charge_product;
							// $cover_price = $valo->price;
							$total_cover_price += ($cover_price * $valOid->qty_ordered);
							$pub_com_percent = $valo->pub_com_percent;
							$gift_name = $valo->gift_name;


							
						}
					}

					

					//echo  $shipping_charge = $B2BOrderDetails->shipping_amount; die();


					

					//$shipping_charge = $B2BOrderDetails->shipping_amount;





					//echo $shipping_charge ;
					// $total_grand_ = $B2BOrderDetails->grand_total - $shipping_charge;

					//$total_grand_ = $B2BOrderDetails->grand_total - $shipping_charge;

					//$whuso_income = (($pub_com_percent  / 100) * ($total_grand_));
					
					// $whuso_income = (($pub_com_percent  / 100) * $cover_price);

					// $Payable_Amount = ($total_grand_ - $whuso_income) + $shipping_charge; //old



					$productRowPrice = $cover_price_shipping * $valOid->qty_ordered;

					$whuso_income = (($pub_com_percent  / 100) * ($productRowPrice));

					$total_whuso_income += $whuso_income;

					$Payable_Amount = ($productRowPrice - $whuso_income) + $shipping_charge;

					// $Payable_Amount = ($productRowPrice - $whuso_income) + $shipping_charge_product;

					// $Payable_Amount = $cover_price  - $whuso_income;
					$total_amount_payable += $Payable_Amount;
					$variants = json_decode($valOid->product_variants, true);
					$variant_html = '';
					if (isset($variants) && count($variants) > 0) {
						foreach ($variants as $pk => $single_variant) {
							foreach ($single_variant as $key => $val) {
								$variant_html .= '<span class="variant-item">' . $key . ' - ' . $val . '</span><br>';
							}
						}
					}
					$PublisherOrdertable .= '<tr>
						
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderData->increment_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $increment_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . date(SIS_DATE_FM, $OrderData->created_at) . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderData->customer_email  . ' </td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->first_name . ' ' . $OrderAddressData->last_name . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->address_line1 . ' ' . $OrderAddressData->address_line2 . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->pincode . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->city . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->state . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $OrderAddressData->mobile_no . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $valOid->product_name . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' .	$variant_html . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $sub_issue . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . ($cover_price * $valOid->qty_ordered) . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $valOid->qty_ordered . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $pub_com_percent . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $whuso_income . '</td>
				
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $Payable_Amount . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $gift_name . '</td>
					</tr>';
				}


				$PublisherOrdertable .= '
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"> </td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $total_cover_price . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $pub_com_percent . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $total_whuso_income . '</td>
			
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $total_amount_payable . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				</tbody>
				</table>';
				// $shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
				// $webshop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);
				// echo
				// $PublisherOrdertable;
				// die();
				// print_R($publisherdetails->email);
				// die();
			// 	echo "<pre>";
			// print_r($PublisherOrdertable);
			// print_r($OrderItemsData);
			// die();
				$webshop_details = $this->CommonModel->get_webshop_details();
				$owner_email = array(ADMIN_EMAILS);
				$shop_name = 'Indiamags';
				$templateId = 'payment_done_for_order';
				$to = $publisherdetails->email; //$owner_email;
				$site_logo = '';
				// if (isset($webshop_details)) {
				// 	$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				// } else {
				// 	$shop_logo = '';
				// }
				$burl = base_url();
				$shop_logo = ''; // get_s3_url($shop_logo, $shop_id);

				$site_logo =  '';
				'<a href="' . SITE_URL . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . SITE_LOGO . '" style="max-width:200px" /></a>';
				$username = 'indiamags';
				$TempVars = array();
				$DynamicVars = array();

				$TempVars = array("##PAYMENTINFOTABLE##,##PUBLISHERORDERTABLE##,##ORDERID##,##WEBSHOPNAME##");
				$DynamicVars   = array($Payment_info_table, $PublisherOrdertable, $OrderData->increment_id, $shop_name);
				$CommonVars = array($site_logo, $shop_name);
				$SubDynamic = array($publisherdetails->publication_name, $increment_id);

				if (isset($templateId)) {
					$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
					if ($emailSendStatusFlag == 1) {
						$to = 'rajesh@bcod.co.in';
						// print_r($CommonVars);
						$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars,'mangal@bcod.co.in');


						// $copy_email_to = 'suzan@indiamags.com'; //'snehals@bcod.co.in'; //'suzan@indiamags.com';
						// $mailSentTo = $this->B2BOrdersModel->sendCommonHTMLEmail($copy_email_to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars);
						// $sales_head_email =  "heeral@whuso.in"; //'saroj@bcod.co.in'; //"heeral@whuso.in";
						// $mailSentto_ = $this->B2BOrdersModel->sendCommonHTMLEmail($sales_head_email, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars);
					}
				}

				// $arrResponse  = array('status' => 200, 'message' => "Payment initiated successfully.");
				// echo json_encode($arrResponse);
			} else {
				$arrResponse  = array('status' => 500, 'message' => "Something went wrong");
				echo json_encode($arrResponse);
			}

			$arrResponse  = array('status' => 200, 'message' => "Payment initiated successfully.");
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 500, 'message' => "Something went wrong!!");
			exit;
		}
	


		// end
	}*/

	function CancelORderRequest()
	{
		// print_R($_POST);
		// die();
		if (isset($_POST['order_id'])) {
			$User_id = $this->session->userdata('LoginID');
			$data['order_id'] =  $order_id = $_POST['order_id'];
			$data['reason_for_cancel'] =  $reason_for_cancel = $_POST['cancel_reason'];
			$data['OrderItemsDetails'] = $OrderItemsDetails = $this->B2BOrdersModel->getOrderItems($order_id);
			$OrderData = $this->B2BOrdersModel->b2b_cancel_order_request($order_id, $reason_for_cancel, $User_id);
			$data['B2BOrderDetails'] = $B2BOrderDetails = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			$sales_order_id = $B2BOrderDetails->webshop_order_id;
			$cancel_sales_order_item = $this->B2BOrdersModel->cancel_order_request($sales_order_id, $reason_for_cancel, $User_id);
			// $where_shipment_arr = array('id' => $_POST['id']);
			// $add_publisher_payment_info  = $this->B2BOrdersModel->updateData('publisher_payment', $where_shipment_arr,array('payment_done' => 1, 'utr_no' => $_POST['utr_no'], 'payment_done_at' => time(), 'updated_at	' => time(), 'updated_by' => $User_id));

			// $owner_email = array('ameyas@bcod.co.in');
			// 	$shop_name = 'Indiamags';
			// 	$templateId = 'storecredit-voucher-cancelnorder';
			// 	$to = $owner_email;
			// 	$site_logo = '';
			// 	// if (isset($webshop_details)) {
			// 	// 	$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
			// 	// } else {
			// 	// 	$shop_logo = '';
			// 	// }
			// 	$burl = base_url();
			// 	$shop_logo = '';// get_s3_url($shop_logo, $shop_id);
			// 	$site_logo =  '';
			// 	//'<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" /></a>';
			// 	$username = 'indiamags';
			// 	$TempVars = array();
			// 	$DynamicVars = array();
			// 			// print_R($OrderData);die();
			// 	$TempVars = array("##PAYMENTINFOTABLE##");
			// 	$DynamicVars   = array($Payment_info_table);
			// 	$CommonVars = array($site_logo, $shop_name);
			// 	$SubDynamic = array($publisherdetails->publication_name, $increment_id);
			// 	if (isset($templateId)) {
			// 		$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
			// 		if ($emailSendStatusFlag == 1) {
			// 			$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars);
			// 		}
			// 	}
			$arrResponse  = array('status' => 200, 'message' => "Order Cancled successfully.");
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 500, 'message' => "Something went wrong!!");
			exit;
		}
	}

	function openVariantPopup()
	{
		// print_r('hii');die;
		if (isset($_POST['item_id'])) {
			// $data['order_id']=$_POST['order_id'];
			$data['item_id'] = $item_id = $_POST['item_id'];
			$data['OrderItemData'] = $OrderItemData = $this->WebshopOrdersModel->getSingleDataByID('sales_order_items', array('item_id' => $item_id), '');
			// print_r($data['OrderItemData']);die;
			$View = $this->load->view('webshop/order/oi-variants-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	public function itemwithvar()
	{
		// echo "<pre>";print_r($_POST);die();
		$item_id = $this->input->post('item_id');
		// print_r($item_id);die;

		// $old_start_date = date('d/m/Y' , $this->input->post('start_date'));
		// $old_end_date = date('d/m/Y' , $this->input->post('end_date'));
		$old_start_date = date('d/m/Y', strtotime($this->input->post('start_date')));
		$old_end_date = date('d/m/Y', strtotime($this->input->post('end_date')));


		$convertedstart_date = str_replace("/", "-", $old_start_date);
		// print_r($convertedstart_date);
		$convertedend_date = str_replace("/", "-", $old_end_date);
		// print_r($convertedend_date);die;

		$start_date = strtotime($convertedstart_date);
		$end_date = strtotime($convertedend_date);
		$res = $this->B2BOrdersModel->updateVariant($item_id, $start_date, $end_date);

		$arrResponse  = array('status' => 200, 'message' => 'Updated Successfully!');
		echo json_encode($arrResponse);
		exit;
	}



	public function hbr_listing()
	{
		$SISA_ID = $this->session->userdata('LoginID');
		if ($SISA_ID) {
			$data['ProductData'] = $ProductData = $this->B2BOrdersModel->HBRListDetails();
			// echo "<pre>";
			// print_r($ProductData);
			// die;
			// foreach ($ProductData as $item) {
			// 	$order_id = $item['order_id'];
			// }

			// // print_r($order_id);
			// // $data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			// $data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('webshop_order_id' => $order_id), '');
			// echo "<pre>";
			// print_r($OrderData);
			// die;

			// // foreach ($OrderData as $items) {
			// // 	$B2b_increment_id = $items['increment_id'];
			// // 	$patent_order_id = $items['order_id'];

			// // }
			// $B2b_increment_id = $OrderData->increment_id;
			// $patent_order_id = $OrderData->webshop_order_id;

			// $data['PublisherPayment'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('publisher_payment', array('order_id' => $patent_order_id, 'B2b_order_id' => $B2b_increment_id), '');
			// echo "<pre>";
			// print_r($data['PublisherPayment']);
			// die;

			$order_id = array();
			foreach ($ProductData as $item) {
				// echo "<pre>";
				// print_r($item);
				// die;
				$order_id[] = $item['order_id'];
			}
			// $OrderData = '';
			// print_r($order_id);
			// die;
			// $data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			// $data['OrderData'] = $OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('webshop_order_id' => $order_id), '');
			$data['OrderData'] = $OrderData = array();  // Initialize $OrderData as an empty array

			if (!empty($order_id)) {
				$OrderData = $this->B2BOrdersModel->B2BOrderDetails($order_id);
				$data['OrderData'] = $OrderData;
			}
			// echo "<pre>";
			// print_r($OrderData);
			// die;

			$B2b_increment_id = array();
			$patent_order_id = array();

			foreach ($OrderData as $items) {
				$B2b_increment_id[] = $items['increment_id'];
				$patent_order_id[] = $items['webshop_order_id'];
			}
			// echo "<pre>";
			// print_r($B2b_increment_id);
			// // die;
			// echo "<pre>";
			// print_r($patent_order_id);
			// die;
			// $B2b_increment_id = $OrderData->increment_id;
			// $patent_order_id = $OrderData->webshop_order_id;

			$data['PublisherPayment'] = $BillingAddress = array();  // Initialize $data['PublisherPayment'] as an empty array
			// Initialize $BillingAddress as an empty array

			if (!empty($B2b_increment_id) && !empty($patent_order_id)) {
				$BillingAddress = $this->B2BOrdersModel->getPublisherPayment($B2b_increment_id, $patent_order_id);
				$data['PublisherPayment'] = $BillingAddress;
			}

			// $data['PublisherPayment'] = $BillingAddress = $this->B2BOrdersModel->getPublisherPayment($B2b_increment_id, $patent_order_id);
			// echo "<pre>";
			// print_r($data['PublisherPayment']);
			// die;

			// $data['OrderItems'] = $OrderItems = $this->B2BOrdersModel->getHBROrderItems($order_id);
			// echo "<pre>";
			// print_r($data['OrderItems']);
			// die;

			$data['PageTitle'] = 'Abundant Carts';
			$data['side_menu'] = 'abundant-carts';
			$this->load->view('b2b/order/HBR_Listing', array("data" => $data));
		} else {
			return redirect('/');
		}
	}


	function HBRInitiateOrderPopup()
	{
		// echo "<pre>";
		// print_r($_POST);
		// die;


		if (isset($_POST['order_id'])) {

			$order_id = $_POST['order_id'];
			$increment_id = $_POST['inc_id'];
			$publisher_id = $_POST['publisher_id'];
			// print_r($order_id);
			// print_r($increment_id);
			// print_r($publisher_id);

			// die;
			// $order_id =	$_POST['order_id'];

			// $data['OrderData'] = $OrderItemData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $_POST['order_id'], 'increment_id' => $_POST['inc_id']), '');

			$data['OrderData'] = $OrderItemData = $this->B2BOrdersModel->OrderItemDataDetails($order_id, $increment_id);

			// echo "<pre>";
			// print_r($data['OrderData']);
			// die;
			$webshop_order_id = array();
			foreach ($OrderItemData as $order) {
				// Check if the key 'webshop_order_id' exists in the current order array
				if (isset($order['webshop_order_id'])) {
					// Access the 'webshop_order_id' property here
					$webshop_order_id[] = $order['webshop_order_id'];
				} else {
					// Handle the case where the 'webshop_order_id' property doesn't exist
					echo 'webshop_order_id does not exist in the current order array.';
				}
			}
			// print_r($webshop_order_id);
			// die;

			$data['get_products'] = $get_products = $this->B2BOrdersModel->get_publisher($webshop_order_id);
			// echo "<pre>";
			// print_r($data['get_products']);
			// die;

			foreach ($get_products as $item) {
				// echo "<pre>";
				// print_r($item);
				// die;
				$product_id = $item['product_id'];
				$id = $item['id'];
				$order_ids = $item['order_id'];
				$publisher_id = $item['publisher_id'];
				// print_r($product_id);
				// Process $product_id as needed


				$data['get_order_details'] = $get_order_details = $this->B2BOrdersModel->get_order_details($product_id, $id, $order_ids, $publisher_id);
			}
			// echo "<pre>";
			// print_r($get_order_details);
			// die;
			$publisher_id = array();
			foreach ($OrderItemData as $order) {
				// Check if the key 'publisher_id' exists in the current order array
				if (isset($order['publisher_id'])) {
					// Access the 'publisher_id' property here
					$publisher_id[] = $order['publisher_id'];
				} else {
					// Handle the case where the 'publisher_id' property doesn't exist
					echo 'publisher_id does not exist in the current order array.';
				}
			}

			$data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getPublisherDetails($publisher_id);
			// echo "<pre>";
			// print_r($publisherdetails);
			// die;

			$data['PublisherPayemntData'] = $PublisherPaymentData = $this->B2BOrdersModel->getPublisherPaymentDetails($publisher_id);
			// echo "<pre>";
			// print_r($data['PublisherPayemntData']);
			// die;

			$View = $this->load->view('b2b/order/hbr-initiate-payment-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function HBRInitiateOrder()
	{
		// echo "<pre>";
		// print_R($_POST);
		// die();
		if (isset($_POST['hidden_order_id'])) {
			$User_id = $this->session->userdata('LoginID');
			$data['order_id'] =  $order_id = $_POST['hidden_order_id'];
			$data['increment_id'] = $increment_id =  $_POST['hidden_b2b_order_id'];
			// print_R($data['order_id']);
			// print_R($data['increment_id']);
			// die();

			$hidden_order_ids = explode(',', $data['order_id']);
			$hidden_b2b_order_ids = explode(',', $data['increment_id']);

			// print_R($hidden_order_ids);
			// print_R($hidden_b2b_order_ids);
			// die();

			$beneficiary_acc_no = $_POST['bene_acc_no'];
			$beneficiary_ifsc = $_POST['bene_ifsc_code'];
			$beneficiary_name = $_POST['beneficiary_name'];
			$payment_mod = $_POST['status'];
			$publisher_id = $_POST['hidden_publisher_id'];
			$amount_payable	 = str_replace(',', '', $_POST['amount_payable']);
			$hidden_amount_payable = $_POST['hidden_amount_payable'];
			$remarks = $_POST['remarks'];

			$last_inserted_publisher_payment_id = array();
			$data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getSingleDataByID('publisher', array('id' => $publisher_id), '');
			// echo "<pre>";
			// print_R($data['PublisherDetails']);
			// die;
			// $data['B2BOrderDetails'] = $B2BOrderDetails = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			$data['B2BOrderDetails'] = $B2BOrderDetails = $this->B2BOrdersModel->B2BOrderDetails($hidden_order_ids);

			// echo "<pre>";
			// print_R($data['B2BOrderDetails']);
			// die;

			$webshop_order_id = array();
			foreach ($B2BOrderDetails as $order) {
				// Check if the key 'webshop_order_id' exists in the current order array
				if (isset($order['webshop_order_id'])) {
					// Access the 'webshop_order_id' property here
					$webshop_order_id[] = $order['webshop_order_id'];
				} else {
					// Handle the case where the 'webshop_order_id' property doesn't exist
					echo 'webshop_order_id does not exist in the current order array.';
				}
			}


			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getOrderData($webshop_order_id);
			// echo "<pre>";
			// print_R($data['OrderData']);
			// die;
			// echo $this->db->last_query();


			// $hidden_order_ids = explode(',', $data['order_id']);
			// $hidden_b2b_order_ids = explode(',', $data['increment_id']);
			// echo "<pre>";
			// print_R($hidden_order_ids);
			// die;
			// echo "<pre>";
			// print_R($hidden_b2b_order_ids);
			// die;

			$count = count($hidden_order_ids);

			for ($i = 0; $i < $count; $i++) {
				$order_id = $order_ids[] = $hidden_order_ids[$i];
				$b2b_order_id = $b2b_order_ids[] = $hidden_b2b_order_ids[$i];
				// print_R($order_id);
				// print_R($b2b_order_id);
				$add_publisher_payment  = array(
					'order_id' => $order_id,
					'B2b_order_id' => $b2b_order_id,
					'publisher_id' => $publisher_id,
					'beneficiary_acc_no' => $beneficiary_acc_no,
					'beneficiary_ifsc' => $beneficiary_ifsc,
					'beneficiary_name' => $beneficiary_name,
					'remarks' => $remarks,
					'amount_payable' => $hidden_amount_payable,
					'payment_initiated' => 1,
					'payment_initiated_at' => time(),
					'created_at' => time(),
					'created_by' => $User_id,
					'payment_mod' => $payment_mod
				);

				// echo "<pre>";
				// print_R($add_publisher_payment);
				// die;
				$add_publisher_payment = $this->B2BOrdersModel->insertPublisherPaymentData($add_publisher_payment);
				// print_r($add_publisher_payment);
				// die;
				// $last_inserted_publisher_payment_id;
				if ($add_publisher_payment) {
					// Append the last inserted ID to the array
					$last_inserted_publisher_payment_id[] = $add_publisher_payment;
					echo "Inserted ID for iteration $i: $add_publisher_payment\n";
				} else {
					// Handle the case where insertion fails
					echo "Insertion failed for iteration $i\n";
				}
			}
			// print_r($last_inserted_publisher_payment_id);

			// die;
			// $add_publisher_payment_info = $this->B2BOrdersModel->getRecentlyInsertedIds();
			$get_publisher_payment_details = $this->B2BOrdersModel->get_publisher_payment_details($last_inserted_publisher_payment_id);
			// echo "<pre>";
			// print_R($get_publisher_payment_details);
			// die;
			// die;
			// if ($add_publisher_payment_info) {
			// 	echo "Record inserted successfully\n";
			// } else {
			// 	echo "Error inserting record: " . $add_publisher_payment_info->getLastError() . "\n";
			// }
			// echo "<pre>";
			// print_R($add_publisher_payment_info);
			// die;
			// echo "<pre>";
			// print_R($get_publisher_payment_details);
			// die;

			$increment_id = '';
			$Order_increment_id = '';
			$order_item_list = '';
			foreach ($get_publisher_payment_details as $get_details) {
				$order_id = $get_details['order_id'];
				// $increment_id = $get_details['B2b_order_id'];

				if (isset($get_details['B2b_order_id'])) {
					$increment_id = $get_details['B2b_order_id'];
				} else {
					// Handle the case where the 'publisher_id' property doesn't exist
					// echo 'publication_name does not exist in the current Publisher array.';
				}

				$publisher_id = $get_details['publisher_id'];
				$beneficiary_acc_no = $get_details['beneficiary_acc_no'];
				$beneficiary_ifsc = $get_details['beneficiary_ifsc'];
				$beneficiary_name = $get_details['beneficiary_name'];
				// $hidden_amount_payable = $get_details['amount_payable'];
				// $shipping_charge = $get_details['shipping_amount'];
				// $total_grand_ = $get_details['grand_total'] - $shipping_charge;

				// $productRowPrice = $cover_price * $get_details_qty_ordered;

				// $whuso_income = (($pub_com_percent  / 100) * ($productRowPrice));

				// $total_whuso_income += $whuso_income;

				$Payable_Amount = $_POST['hidden_amount_payable'];

				// $cover_price = $PaymentDetails_price;
				// $total_cover_price += ($cover_price * $PaymentDetails_qty_ordered);

				$order_item_list .= '<tr>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $order_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $increment_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $publisher_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_acc_no . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_ifsc . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_name . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $Payable_Amount . '</td>
				</tr>';
				// print_R('order_id' . $order_id);
				// print_R('increment_id' . $increment_id);
				// print_R('publisher_id' . $publisher_id);
				// print_R('beneficiary_acc_no' . $beneficiary_acc_no);
				// print_R('beneficiary_ifsc' . $beneficiary_ifsc);
				// print_R('beneficiary_name' . $beneficiary_name);
				// print_R('amount_payable' . $amount_payable);
			}
			foreach ($OrderData as $order) {
				if (isset($order['grand_total'])) {
					$Order_increment_id = $order['increment_id'];
				} else {
				}
			}
			// die;
			$grand_total_final = 'INR' . $_POST['amount_payable'];
			$total_itmes = count($B2BOrderDetails);

			if ($get_publisher_payment_details) {

				$Payment_info_table = '';
				$Payment_info_table .= '<tr>

					<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:0;margin:0">

						<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #eaeaea; font-family:Arial,Helvetica,sans-serif;font-size:13px">
							<thead>
								<tr>
									<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Id</th>
									<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">B2B Order Id</th>
									<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Publisher Id</th>
									<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Acc No</th>
									<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary IFSC</th>
									<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Name</th>
									<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Amount Payable</th>
								</tr>
							</thead>
							<tbody>
							' . $order_item_list . '

							</tbody>
						</table>

					</td>

				</tr>
				<tr>
					<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:0;margin:0">
						<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0;border-top:1px dashed #c3ced4;border-bottom:1px dashed #c3ced4">
							<tbody>
								<tr>
									<td style="font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;padding:20px 15px;margin:0;text-align:right;line-height:20px">

										<table cellpadding="0" cellspacing="0" border="0" style="width:100%;padding:0;margin:0">

											<tbody>
												<tr>
													<td  align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														Price(' . $total_itmes . ' items)
													</td>
													<td  align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
													' . $grand_total_final . '
													</td>
												</tr>
												<tr>
													<td  align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														Subtotal
													</td>
													<td  align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
													' . $grand_total_final . '
													</td>
												</tr>
												<tr>
													<td  align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														Grand Total
													</td>
													<td  align="right" style="padding:3px 9px;font-family:Verdana,Arial;font-weight:normal;border-collapse:collapse;vertical-align:top;margin:0">
														<strong><span style="font-family:&quot;Helvetica Neue&quot;,Verdana,Arial,sans-serif">' . $grand_total_final . '</span></strong>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>';

				// $shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
				// $webshop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);
				$webshop_details = $this->CommonModel->get_webshop_details();
				$owner_email = ADMIN_EMAILS;
				$shop_name = 'Indiamags';
				$templateId = 'hbr_request_a_payment_for_order';
				$to = $owner_email;
				// $to = 'bhagyashree@bcod.co.in';
				$site_logo = '';
				// if (isset($webshop_details)) {
				// 	$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				// } else {
				// 	$shop_logo = '';
				// }
				$burl = base_url();
				$shop_logo = ''; // get_s3_url($shop_logo, $shop_id);

				$site_logo =  '';
				'<a href="' . SITE_URL . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . SITE_LOGO . '" style="max-width:200px" /></a>';
				$username = 'indiamags';
				$TempVars = array();
				$DynamicVars = array();

				$TempVars = array("##PAYMENTINFOTABLE##,##ORDERID##,##WEBSHOPNAME##");
				$DynamicVars   = array($Payment_info_table, $Order_increment_id, $shop_name);
				$CommonVars = array($site_logo, $shop_name);
				$SubDynamic = array($publisherdetails->publication_name, $increment_id);

				if (isset($templateId)) {
					$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
					// echo "hiii" . $emailSendStatusFlag;
					if ($emailSendStatusFlag == 1) {

						$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars, '');
						// var_dump($mailSent);
						// print_r($mailSent);
					}
					// die;
				}

				$arrResponse  = array('status' => 200, 'message' => "Payment initiated successfully.");
				echo json_encode($arrResponse);
			} else {
				$arrResponse  = array('status' => 500, 'message' => "Something went wrong");
				echo json_encode($arrResponse);
			}

			exit;



			// $View = $this->load->view('b2b/order/initiate-payment-popup', $data, true);
			// $this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function HBRProceedPaymentPopup()
	{
		// echo "<pre>";
		// print_r($_POST);
		// die;
		if (isset($_POST['id'])) {
			$order_id = $_POST['order_id'];
			$increment_id = $_POST['inc_id'];
			$publisher_id = $_POST['publisher_id'];
			$id = $_POST['id'];


			$data['OrderData'] = $OrderItemData = $this->B2BOrdersModel->OrderItemDataDetails($order_id, $increment_id);
			// echo "<pre>";
			// print_r($data['OrderData']);
			// die;
			$webshop_order_id = array();
			foreach ($OrderItemData as $order) {
				// Check if the key 'webshop_order_id' exists in the current order array
				if (isset($order['webshop_order_id'])) {
					// Access the 'webshop_order_id' property here
					$webshop_order_id[] = $order['webshop_order_id'];
				} else {
					// Handle the case where the 'webshop_order_id' property doesn't exist
					echo 'webshop_order_id does not exist in the current order array.';
				}
			}

			$data['get_products'] = $get_products = $this->B2BOrdersModel->get_publisher($webshop_order_id);
			// echo "<pre>";
			// print_r($get_products);
			// die;

			foreach ($get_products as $item) {
				$product_id = $item['product_id'];
				$id = $item['id'];
				$order_id = $item['order_id'];
				$publisher_id = $item['publisher_id'];

				// Process $product_id as needed
				$data['get_order_details'] = $get_order_details = $this->B2BOrdersModel->get_order_details($product_id, $id, $order_id, $publisher_id);
			}
			// echo "<pre>";
			// print_r($get_order_details);
			// die;
			$publisher_id = array();
			foreach ($OrderItemData as $order) {
				// Check if the key 'publisher_id' exists in the current order array
				if (isset($order['publisher_id'])) {
					// Access the 'publisher_id' property here
					$publisher_id[] = $order['publisher_id'];
				} else {
					// Handle the case where the 'publisher_id' property doesn't exist
					echo 'publisher_id does not exist in the current order array.';
				}
			}

			$data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getPublisherDetails($publisher_id);
			// echo "<pre>";
			// print_r($data['PublisherDetails']);
			// die;
			// print_r($_POST['id']);
			// die;




			$data['PublisherPayemntData'] = $PublisherPaymentData = $this->B2BOrdersModel->PublisherPaymentData($_POST['id']);

			// print_R($data['PublisherPayemntData']);die();
			// $data['order_id'] = $_POST['order_id'];
			// $data['increment_id'] = $_POST['inc_id'];
			// echo "<pre>";
			// print_r($data['PublisherPayemntData']);
			// die;

			// $data['OrderData'] = $OrderItemData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $_POST['order_id'], 'increment_id' => $_POST['inc_id']), '');
			// $data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getSingleDataByID('publisher', array('id' => $OrderItemData->publisher_id), '');

			$View = $this->load->view('b2b/order/hbr-proceed-payment-popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function HBRPaymentDone()
	{
		// echo "<pre>";
		// print_R($_POST);
		// die();
		if (isset($_POST['id'])) {
			$User_id = $this->session->userdata('LoginID');
			$data['id'] =  $order_id = $_POST['hidden_order_id'];
			$data['hidden_b2b_order_id'] =  $increment_id = $_POST['hidden_b2b_order_id'];
			$data['hidden_publisher_id'] =  $publisher_id = $_POST['hidden_publisher_id'];
			$data['bene_acc_no'] =  $beneficiary_acc_no = $_POST['bene_acc_no'];
			$data['beneficiary_name'] =  $beneficiary_name = $_POST['beneficiary_name'];
			$data['bene_ifsc_code'] =  $beneficiary_ifsc = $_POST['bene_ifsc_code'];
			$data['amount_payable'] =  $amount_payable = $_POST['amount_payable'];
			$publisher_id = $_POST['hidden_publisher_id'];
			$data['utr_no'] =  $utr_no = $_POST['utr_no'];
			$data['hidden_amount_payable'] =  $hidden_amount_payable = $_POST['hidden_amount_payable'];

			$hidden_order_ids = explode(',', $order_id);
			$hidden_b2b_order_ids = explode(',', $increment_id);

			$data['OrderData'] = $OrderItemData = $this->B2BOrdersModel->OrderItemDataDetails($order_id, $increment_id);
			$data['PublisherDetails'] = $publisherdetails = $this->B2BOrdersModel->getPublisherDetails($publisher_id);
			$data['PublisherPayemntData'] = $PublisherPaymentData = $this->B2BOrdersModel->PublisherPaymentData($_POST['id']);
			$data['B2BOrderDetails'] = $B2BOrderDetails = $this->B2BOrdersModel->B2BOrderDetails($hidden_order_ids);

			// echo "<pre>";
			// print_r($publisherdetails);
			// die;
			$cc_email = '';
			foreach ($publisherdetails as $order) {

				if (isset($order['cc_email'])) {
					$cc_email = $order['cc_email'];
				}
				if (isset($order['publication_name'])) {
					$publication_name = $order['publication_name'];
				}
				if (isset($order['email'])) {
					$publisher_email = $order['email'];
				}
			}


			foreach ($B2BOrderDetails as $orders) {
				$B2B_item_order_id[] = $orders['order_id'];
			}
			$data['B2BOrderItemsDetails'] = $B2BOrderItemsDetails = $this->B2BOrdersModel->B2BOrderItemsDetails($B2B_item_order_id);

			// echo "<pre>";
			// print_R($data['B2BOrderItemsDetails']);
			// die;

			$webshop_order_id = array();
			foreach ($B2BOrderDetails as $order) {
				// Check if the key 'webshop_order_id' exists in the current order array
				if (isset($order['webshop_order_id'])) {
					// Access the 'webshop_order_id' property here
					$webshop_order_id[] = $order['webshop_order_id'];
				} else {
					// Handle the case where the 'webshop_order_id' property doesn't exist
					echo 'webshop_order_id does not exist in the current order array.';
				}
			}

			$data['OrderData'] = $OrderData = $this->B2BOrdersModel->getOrderData($webshop_order_id);
			$data['OrderItemsData'] = $OrderItemsData = $this->B2BOrdersModel->OrderItemsData($webshop_order_id);

			$data['get_products'] = $get_products = $this->B2BOrdersModel->get_publisher($webshop_order_id);
			// echo "<pre>";
			// print_r($get_products);
			// die;

			foreach ($get_products as $item) {
				$product_id = $item['product_id'];
				$id = $item['id'];
				$order_ids = $item['order_id'];
				$publisher_id = $item['publisher_id'];
				// print_r($order_ids);
				// Process $product_id as needed


				$data['get_order_details'] = $get_order_details = $this->B2BOrdersModel->get_order_details($product_id, $id, $order_ids, $publisher_id);
			}

			// echo "<pre>";
			// print_R($data['OrderItemsData']);
			// die;
			$data['OrderAddressData'] = $OrderAddressData = $this->B2BOrdersModel->OrderAddressData($webshop_order_id);

			// $where_shipment_arr = array('id' => $_POST['id']);
			$where_shipment_arr = explode(',', $_POST['id']);
			$update_publisher_payment = $this->B2BOrdersModel->updatePublisherPaymentData($where_shipment_arr, $utr_no, $User_id);
			// echo "<pre>";
			// print_R($PublisherPaymentData);
			// die;
			$order_item_list = '';

			$data['FinalPaymentDetails'] = $FinalPaymentDetails = $this->B2BOrdersModel->FinalPaymentDetails($B2B_item_order_id, $webshop_order_id, $hidden_order_ids);

			foreach ($PublisherPaymentData as $get_details) {

				$order_id = $get_details['order_id'];
				$increment_id = $get_details['B2b_order_id'];
				$publisher_id = $get_details['publisher_id'];
				$beneficiary_acc_no = $get_details['beneficiary_acc_no'];
				$beneficiary_ifsc = $get_details['beneficiary_ifsc'];
				$beneficiary_name = $get_details['beneficiary_name'];
				$utr_no = $get_details['utr_no'];
				// $hidden_amount_payable = $get_details['hidden_amount_payable'];
				// $amount_payable = $get_details['amount_payable'];

				$order_item_list .= '<tr>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $order_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $increment_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $publisher_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_acc_no . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_ifsc . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $beneficiary_name . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $utr_no . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $hidden_amount_payable . '</td>
				</tr>';
			}


			if ($PublisherPaymentData) {


				$Payment_info_table = '';
				$Payment_info_table .= '<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #eaeaea; font-family:Arial,Helvetica,sans-serif;font-size:13px">
					<thead>
						<tr>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">B2B Order Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Publisher Id</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Acc No</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary IFSC</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Beneficiary Name</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">UTR No</th>
							<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Amount Payable</th>
						</tr>
					</thead>
					<tbody>
					' . $order_item_list . '

					</tbody>
				</table>';
				$PublisherOrdertable = '';
				$PublisherOrdertable .= '<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #eaeaea; font-family:Arial,Helvetica,sans-serif;font-size:13px" >
					<thead>
						<tr>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Id</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">B2B Order Id</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Order Date</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Customer Email</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Name</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Street</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Zip</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping City</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping State</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Shipping Phone Number</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Magazine</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Subscription</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Issues</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Price</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">QTY</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Commission %</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Commission</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Amount Payable</th>
						<th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Free Gift</th>

						</tr>
					</thead>
					<tbody>';
				$total_cover_price = 0;
				$total_amount_payable = 0;
				$total_whuso_income = 0;
				// echo "<pre>";
				// print_r($FinalPaymentDetails);
				// die;
				// foreach ($B2BOrderItemsDetails as $keyOID => $valOid) {
				foreach ($FinalPaymentDetails as $PaymentDetails) {

					// foreach ($B2BOrderDetails as $orders) {
					// 	$shipping_charge = $orders['shipping_amount'];
					// 	$total_grand_ = $orders['grand_total'] - $shipping_charge;
					// }


					if (isset($PaymentDetails['product_id'])) {
						$PaymentDetails_product_id = $PaymentDetails['product_id'];
					}
					if (isset($PaymentDetails['qty_ordered'])) {
						$PaymentDetails_qty_ordered = $PaymentDetails['qty_ordered'];
					}
					if (isset($PaymentDetails['product_variants'])) {
						$PaymentDetails_product_variants = $PaymentDetails['product_variants'];
					}
					if (isset($PaymentDetails['product_name'])) {
						$PaymentDetails_product_name = $PaymentDetails['product_name'];
					}
					if (isset($PaymentDetails['sale_order_product_id'])) {
						$PaymentDetails_sale_order_product_id = $PaymentDetails['sale_order_product_id'];
					}
					if (isset($PaymentDetails['sub_issues'])) {
						$PaymentDetails_sub_issues = $PaymentDetails['sub_issues'];
					}
					if (isset($PaymentDetails['price'])) {
						$PaymentDetails_price = $PaymentDetails['price'];
					}
					if (isset($PaymentDetails['pub_com_percent'])) {
						$PaymentDetails_pub_com_percent = $PaymentDetails['pub_com_percent'];
					}
					if (isset($PaymentDetails['gift_name'])) {
						$PaymentDetails_gift_name = $PaymentDetails['gift_name'];
					}

					if ($PaymentDetails_product_id ==  $PaymentDetails_sale_order_product_id) {
						$sub_issue = $PaymentDetails_sub_issues;
						$cover_price = $PaymentDetails_price;
						$total_cover_price += ($cover_price * $PaymentDetails_qty_ordered);
						$pub_com_percent = $PaymentDetails_pub_com_percent;
						$gift_name = $PaymentDetails_gift_name;
					}
					$customer_email = $PaymentDetails['customer_email'];
					$order_date = $PaymentDetails['created_at'];

					if (isset($PaymentDetails['first_name'], $PaymentDetails['last_name'])) {
						$shipping_name = $PaymentDetails['first_name'] . ' ' . $PaymentDetails['last_name'];
					}
					if (isset($PaymentDetails['address_line1'], $PaymentDetails['address_line2'])) {
						$address =	$PaymentDetails['address_line1'] . ' ' . $PaymentDetails['address_line2'];
					}
					if (isset($PaymentDetails['pincode'])) {
						$pincode =	$PaymentDetails['pincode'];
					}
					if (isset($PaymentDetails['city'])) {
						$city =	$PaymentDetails['city'];
					}
					if (isset($PaymentDetails['state'])) {
						$state =	$PaymentDetails['state'];
					}
					if (isset($PaymentDetails['mobile_no'])) {
						$mobile_no =	$PaymentDetails['mobile_no'];
					}
					$order_id = $PaymentDetails['webshop_order_id'];
					$increment_id = $PaymentDetails['order_barcode'];

					/*$whuso_income = (($pub_com_percent  / 100) * ($total_grand_));
					$total_whuso_income += $whuso_income;
					// $whuso_income = (($pub_com_percent  / 100) * $cover_price);
					$Payable_Amount = ($total_grand_ - $whuso_income) + $shipping_charge;
					// $Payable_Amount = $cover_price  - $whuso_income;*/

					$shipping_charge = $PaymentDetails['shipping_amount'];
					$total_grand_ = $PaymentDetails['grand_total'] - $shipping_charge;

					$productRowPrice = $cover_price * $PaymentDetails_qty_ordered;

					$whuso_income = (($pub_com_percent  / 100) * ($productRowPrice));

					$total_whuso_income += $whuso_income;

					$Payable_Amount = ($productRowPrice - $whuso_income) + $shipping_charge;


					$total_amount_payable += $Payable_Amount;
					$variants = json_decode($PaymentDetails_product_variants, true);
					$variant_html = '';
					if (isset($variants) && count($variants) > 0) {
						foreach ($variants as $pk => $single_variant) {
							foreach ($single_variant as $key => $val) {
								$variant_html .= '<span class="variant-item">' . $key . ' - ' . $val . '</span><br>';
							}
						}
					}



					$PublisherOrdertable .= '<tr>

					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $order_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $increment_id . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . date(SIS_DATE_FM, $order_date) . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $customer_email  . ' </td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $shipping_name . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $address . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $pincode . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $city . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $state . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $mobile_no . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $PaymentDetails_product_name . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' .	$variant_html . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $sub_issue . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . ($cover_price * $PaymentDetails_qty_ordered) . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $PaymentDetails_qty_ordered . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $pub_com_percent . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $whuso_income . '</td>

					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $Payable_Amount . '</td>
					<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $gift_name . '</td>
					</tr>';
				}
				$PublisherOrdertable .= '
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"> </td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $total_cover_price . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $pub_com_percent . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $total_whuso_income . '</td>

				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc">' . $total_amount_payable . '</td>
				<td align="left" valign="top" style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc"></td>
				</tbody>
				</table>';
				// $shop_owner = $this->CommonModel->getShopOwnerData($shop_id);
				// $webshop_owner = $this->CommonModel->getShopOwnerData($webshop_shop_id);
				// echo
				// $PublisherOrdertable;
				// die();
				// print_R($publisherdetails->email);
				// die();



				$webshop_details = $this->CommonModel->get_webshop_details();
				$owner_email = array(ADMIN_EMAILS);
				$shop_name = 'Indiamags';
				$templateId = 'hbr_payment_done_for_order';
				$to = $publisher_email; //$owner_email;

				$cc_emails = $cc_email; //$owner_email;

				// echo "<pre>";
				// print_r($to);
				// echo "<pre>";
				// print_r($cc_emails);
				// die;
				$cc = explode(', ', $cc_emails);
				$site_logo = '';
				// if (isset($webshop_details)) {
				// 	$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
				// } else {
				// 	$shop_logo = '';
				// }
				$burl = base_url();
				$shop_logo = ''; // get_s3_url($shop_logo, $shop_id);

				$site_logo =  '';
				'<a href="' . SITE_URL . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . SITE_LOGO . '" style="max-width:200px" /></a>';
				$username = 'indiamags';
				$TempVars = array();
				$DynamicVars = array();

				$TempVars = array("##PAYMENTINFOTABLE##,##PUBLISHERORDERTABLE##,##ORDERID##,##WEBSHOPNAME##");
				$DynamicVars   = array($Payment_info_table, $PublisherOrdertable, $increment_id, $shop_name);
				$CommonVars = array($site_logo, $shop_name);
				$SubDynamic = array($publication_name, $increment_id);

				if (isset($templateId)) {
					$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
					if ($emailSendStatusFlag == 1) {
						$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars, $cc);
						// $copy_email_to = 'suzan@indiamags.com'; //'snehals@bcod.co.in'; //'suzan@indiamags.com';
						// $mailSentTo = $this->B2BOrdersModel->sendCommonHTMLEmail($copy_email_to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars);
						// $sales_head_email =  "heeral@whuso.in"; //'saroj@bcod.co.in'; //"heeral@whuso.in";
						// $mailSentto_ = $this->B2BOrdersModel->sendCommonHTMLEmail($sales_head_email, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars);
					}
				}

				// $arrResponse  = array('status' => 200, 'message' => "Payment initiated successfully.");
				// echo json_encode($arrResponse);
			} else {
				$arrResponse  = array('status' => 500, 'message' => "Something went wrong");
				echo json_encode($arrResponse);
			}

			$arrResponse  = array('status' => 200, 'message' => "Payment done successfully.");
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 500, 'message' => "Something went wrong!!");
			exit;
		}
	}

	function HBRCancelORderRequest()
	{
		// print_R($_POST);
		// die();
		if (isset($_POST['order_id'])) {
			$User_id = $this->session->userdata('LoginID');
			$data['order_id'] =  $order_id = $_POST['order_id'];
			$data['reason_for_cancel'] =  $reason_for_cancel = $_POST['cancel_reason'];
			$data['OrderItemsDetails'] = $OrderItemsDetails = $this->B2BOrdersModel->getOrderItems($order_id);
			$OrderData = $this->B2BOrdersModel->b2b_cancel_order_request($order_id, $reason_for_cancel, $User_id);
			$data['B2BOrderDetails'] = $B2BOrderDetails = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			$sales_order_id = $B2BOrderDetails->webshop_order_id;
			$cancel_sales_order_item = $this->B2BOrdersModel->cancel_order_request($sales_order_id, $reason_for_cancel, $User_id);
			// $where_shipment_arr = array('id' => $_POST['id']);
			// $add_publisher_payment_info  = $this->B2BOrdersModel->updateData('publisher_payment', $where_shipment_arr,array('payment_done' => 1, 'utr_no' => $_POST['utr_no'], 'payment_done_at' => time(), 'updated_at	' => time(), 'updated_by' => $User_id));

			// $owner_email = array('ameyas@bcod.co.in');
			// 	$shop_name = 'Indiamags';
			// 	$templateId = 'storecredit-voucher-cancelnorder';
			// 	$to = $owner_email;
			// 	$site_logo = '';
			// 	// if (isset($webshop_details)) {
			// 	// 	$shop_logo = $this->encryption->decrypt($webshop_details['site_logo']);
			// 	// } else {
			// 	// 	$shop_logo = '';
			// 	// }
			// 	$burl = base_url();
			// 	$shop_logo = '';// get_s3_url($shop_logo, $shop_id);
			// 	$site_logo =  '';
			// 	//'<a href="' . getWebsiteUrl($shop_id, $burl) . '" style="color:#1E7EC8;"><img alt="' . $shop_name . '" border="0" src="' . $shop_logo . '" style="max-width:200px" /></a>';
			// 	$username = 'indiamags';
			// 	$TempVars = array();
			// 	$DynamicVars = array();
			// 			// print_R($OrderData);die();
			// 	$TempVars = array("##PAYMENTINFOTABLE##");
			// 	$DynamicVars   = array($Payment_info_table);
			// 	$CommonVars = array($site_logo, $shop_name);
			// 	$SubDynamic = array($publisherdetails->publication_name, $increment_id);
			// 	if (isset($templateId)) {
			// 		$emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
			// 		if ($emailSendStatusFlag == 1) {
			// 			$mailSent = $this->B2BOrdersModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars);
			// 		}
			// 	}
			$arrResponse  = array('status' => 200, 'message' => "Order Cancled successfully.");
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 500, 'message' => "Something went wrong!!");
			exit;
		}
	}

	public function AssignNewDeliveryPopup() {
    $order_id   = $this->input->post('order_id');
    $attempt_no = $this->input->post('attempt_no');

    // Fetch dynamic driver data (delivery persons)
    $delivery_persons = $this->CommonModel->getAllDeliveryPersons();

    // Pass data to view
    $data = [
        'order_id'        => $order_id,
        'attempt_no'      => $attempt_no,
        'delivery_persons' => $delivery_persons
    ];

    // Load the view
    $this->load->view('b2b/order/assign-delivery-popup', $data);
}

/*public function AssignNewDelivery()
{
    if (isset($_POST['order_id']) && isset($_POST['attempt_no'])) {
        $User_id      = $this->session->userdata('LoginID');
        $order_id     = $_POST['order_id'];
        $attempt_no   = (int)$_POST['attempt_no']; // this should be current attempt number
        $driver_id    = $_POST['driver_id'];
        $delivery_date= $_POST['delivery_date'];
        $remarks      = $_POST['remarks'] ?? '';

        // Convert delivery_date to proper format
        $delivery_date = DateTime::createFromFormat('d/m/Y', $delivery_date);
        $delivery_date = $delivery_date ? $delivery_date->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

        // Determine delivery status and order status
        if ($attempt_no == 1) {
            // First-time delivery
            $delivery_status = 1; // Shipped / Assigned
            $order_status    = 4; // Shipped in b2b_orders
        } elseif ($attempt_no == 2) {
            $delivery_status = 3; // Attempt 2
            $order_status    = 5;
        } elseif ($attempt_no == 3) {
            $delivery_status = 5; // Attempt 3
            $order_status    = 6;
        } else {
            // Default fallback
            $delivery_status = 1;
            $order_status    = 4;
        }

        // Insert new delivery record
        $insertData = [
            'order_id'            => $order_id,
            'delivery_type'       => 2,
            'driver_id'           => $driver_id,
            'delivery_date'       => $delivery_date,
            'remarks'             => $remarks,
            'delivery_status'     => $delivery_status,
            'delivery_attempt_no' => $attempt_no, // store current attempt
            'generate_by'         => $User_id,
            'created_at'          => date('Y-m-d H:i:s'),
            'ip'                  => $this->input->ip_address()
        ];

        $this->CommonModel->insertData('b2b_orders_delivery_details', $insertData);

        // Update main order status
        $this->CommonModel->updateData(
            'b2b_orders',
            ['order_id' => $order_id],
            ['status' => $order_status]
        );

        echo json_encode(['status' => 200, 'message' => 'Delivery attempt assigned and order status updated.']);
        exit;

    } else {
        echo json_encode(['status' => 500, 'message' => 'Invalid data.']);
        exit;
    }
}*/

/*public function AssignNewDelivery()
{
    if (isset($_POST['order_id']) && isset($_POST['driver_id']) && isset($_POST['delivery_date'])) {
        $User_id       = $this->session->userdata('LoginID');
        $order_id      = $_POST['order_id'];
        $driver_id     = $_POST['driver_id'];
        $delivery_date = $_POST['delivery_date'];
        $remarks       = $_POST['remarks'] ?? '';

        // Convert delivery_date to proper format
        $delivery_date = DateTime::createFromFormat('d/m/Y', $delivery_date);
        $delivery_date = $delivery_date ? $delivery_date->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

        // Get last delivery attempt for this order
        $lastAttempt = $this->db->where('order_id', $order_id)
            ->order_by('delivery_attempt_no', 'DESC')
            ->limit(1)
            ->get('b2b_orders_delivery_details')
            ->row();

        $currentAttempt = $lastAttempt ? (int)$lastAttempt->delivery_attempt_no : 0;
        $nextAttempt    = $currentAttempt + 1;

        // Allow maximum 2 attempts
        if ($nextAttempt > 2) {
            echo json_encode(['status' => 400, 'message' => 'Maximum 2 delivery attempts allowed.']);
            exit;
        }

        // Map delivery + order status
        if ($nextAttempt == 1) {
            $delivery_status = 1; // First Attempt Assigned
            $order_status    = 4; // Shipped
        } elseif ($nextAttempt == 2) {
            $delivery_status = 3; // Second Attempt Assigned
            $order_status    = 5; // Attempt 2
        } else {
            // Fallback (should never hit because of limit above)
            $delivery_status = 1;
            $order_status    = 4;
        }

        // Insert new delivery attempt record
        $insertData = [
            'order_id'            => $order_id,
            'delivery_type'       => 2,
            'driver_id'           => $driver_id,
            'delivery_date'       => $delivery_date,
            'remarks'             => $remarks,
            'delivery_status'     => $delivery_status,
            'delivery_attempt_no' => $nextAttempt,
            'generate_by'         => $User_id,
            'created_at'          => date('Y-m-d H:i:s'),
            'ip'                  => $this->input->ip_address()
        ];

        $this->CommonModel->insertData('b2b_orders_delivery_details', $insertData);

        // Update b2b_orders table with new status
        $this->CommonModel->updateData(
            'b2b_orders',
            ['order_id' => $order_id],
            ['status' => $order_status]
        );

        echo json_encode(['status' => 200, 'message' => "Delivery attempt #$nextAttempt assigned successfully."]);
        exit;

    } else {
        echo json_encode(['status' => 500, 'message' => 'Invalid data.']);
        exit;
    }
}*/

public function AssignNewDelivery()
{
    if (isset($_POST['order_id'], $_POST['driver_id'], $_POST['delivery_date'])) {
        $User_id       = $this->session->userdata('LoginID');
        $order_id      = $_POST['order_id'];
        $driver_id     = $_POST['driver_id'];
        $delivery_date = trim($_POST['delivery_date']);
        $remarks       = $_POST['remarks'] ?? '';

        // ✅ Normalize date — handle both Y-m-d (HTML5 date input) and d/m/Y (manual input)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $delivery_date)) {
            // Y-m-d format
            $delivery_date = DateTime::createFromFormat('Y-m-d', $delivery_date);
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $delivery_date)) {
            // d/m/Y format
            $delivery_date = DateTime::createFromFormat('d/m/Y', $delivery_date);
        } else {
            $delivery_date = false;
        }

        $delivery_date = $delivery_date ? $delivery_date->format('Y-m-d 00:00:00') : date('Y-m-d 00:00:00');

        // Get last delivery attempt for this order
        $lastAttempt = $this->db->where('order_id', $order_id)
            ->order_by('delivery_attempt_no', 'DESC')
            ->limit(1)
            ->get('b2b_orders_delivery_details')
            ->row();

        $currentAttempt = $lastAttempt ? (int)$lastAttempt->delivery_attempt_no : 0;
        $nextAttempt    = $currentAttempt + 1;

        // Allow maximum 2 attempts
        if ($nextAttempt > 2) {
            echo json_encode(['status' => 400, 'message' => 'Maximum 2 delivery attempts allowed.']);
            exit;
        }

        // Map delivery + order status
        $delivery_status = ($nextAttempt == 1) ? 1 : 3;
        $order_status    = ($nextAttempt == 1) ? 4 : 5;

        // Insert new delivery attempt record
        $insertData = [
            'order_id'            => $order_id,
            'delivery_type'       => 2,
            'driver_id'           => $driver_id,
            'delivery_date'       => $delivery_date,
            'remarks'             => $remarks,
            'delivery_status'     => $delivery_status,
            'delivery_attempt_no' => $nextAttempt,
            'generate_by'         => $User_id,
            'created_at'          => date('Y-m-d H:i:s'),
            'ip'                  => $this->input->ip_address()
        ];

        $this->CommonModel->insertData('b2b_orders_delivery_details', $insertData);

        // Update b2b_orders table
        $this->CommonModel->updateData('b2b_orders', ['order_id' => $order_id], ['status' => $order_status]);

        echo json_encode(['status' => 200, 'message' => "Delivery attempt #$nextAttempt assigned successfully."]);
        exit;
    }

    echo json_encode(['status' => 500, 'message' => 'Invalid data.']);
    exit;
}

public function AssignNewPickupPopup() {
    $order_id = $this->input->post('order_id');


	$delivery_persons = $this->CommonModel->getAllDeliveryPersons();

	$data = [
	'order_id' => $order_id,
	'delivery_persons' => $delivery_persons
    ];
    // Load the pickup popup view
    $this->load->view('b2b/order/assign-pickup-popup', $data);
}



public function AssignNewPickup()
{
    if (isset($_POST['order_id'])) {
        $User_id   = $this->session->userdata('LoginID');
        $order_id  = $_POST['order_id'];
        $driver_id = $_POST['driver_id'] ?? null;
        $remarks   = $_POST['remarks'] ?? '';

        // Set pickup_status to 2 (Assigned) since we are assigning a driver
        $pickup_status = 2; // 0-Pending, 1-Not Assigned, 2-Pick Up Assigned, 3-Picked Up, 4-Delivered To Warehouse

        // Insert new pickup record
        $insertData = [
            'order_id'      => $order_id,
            'driver_id'     => $driver_id,
            'remarks'       => $remarks,
            'pickup_status' => $pickup_status,
            'generate_by'   => $User_id,
            'created_at'    => date('Y-m-d H:i:s'),
            'ip'            => $this->input->ip_address()
        ];

        $this->CommonModel->insertData('b2b_orders_pickup_details', $insertData);

        // Update main order status in b2b_orders
        $order_status = 11; // Pickup assigned
        $this->CommonModel->updateData(
            'b2b_orders',
            ['order_id' => $order_id],
            ['status' => $order_status]
        );

        echo json_encode(['status' => 200, 'message' => 'Pickup assigned successfully.']);
        exit;

    } else {
        echo json_encode(['status' => 500, 'message' => 'Invalid data.']);
        exit;
    }
}


public function MarkPickupReceived()
{
    $order_id = $this->input->post('order_id');
    $User_id  = $this->session->userdata('LoginID');

    if (!$order_id) {
        echo json_encode(['status' => 500, 'message' => 'Invalid order ID.']);
        exit;
    }

    // Get existing pickup record
    $pickupData = $this->CommonModel->getPickupDataByOrderId($order_id);

    if (!$pickupData) {
        echo json_encode(['status' => 404, 'message' => 'Pickup record not found.']);
        exit;
    }

    // Update pickup status to "Received" (example: status 4 = Delivered To Warehouse)
    $updateData = [
        'pickup_status' => 4, // Delivered to warehouse
        'updated_at'    => date('Y-m-d H:i:s'),
    ];

    $this->CommonModel->updateData(
        'b2b_orders_pickup_details',
        ['order_id' => $order_id],
        $updateData
    );

    // Optionally update main order status
    $this->CommonModel->updateData(
        'b2b_orders',
        ['order_id' => $order_id],
        ['status' => 12] // Received To Warehouse
    );

    echo json_encode(['status' => 200, 'message' => 'Pickup marked as received.']);
    exit;
}

public function MarkAsFailed()
{
    if (isset($_POST['order_id']) && isset($_POST['attempt_no'])) {
        $User_id    = $this->session->userdata('LoginID');
        $order_id   = $_POST['order_id'];
        $attempt_no = (int)$_POST['attempt_no'];
        $reason     = $_POST['reason_for_attempt_failed'] ?? '';

        if (empty($reason)) {
            echo json_encode(['status' => 500, 'message' => 'Reason for failure is required.']);
            exit;
        }

        // Map failed attempt status
        switch ($attempt_no) {
            case 1:
                $failedStatus = 2; // Failed Attempt 1
                break;
            case 2:
                $failedStatus = 4; // Failed Attempt 2
                break;
            case 3:
                $failedStatus = 6; // Failed Attempt 3
                break;
            default:
                $failedStatus = 2;
        }

        // Check if delivery attempt exists
        $record = $this->CommonModel->getSingleDataByID(
            'b2b_orders_delivery_details',
            ['order_id' => $order_id, 'delivery_attempt_no' => $attempt_no],
            '*'  // select all columns
        );

        if (!$record) {
            echo json_encode(['status' => 500, 'message' => 'Delivery attempt not found.']);
            exit;
        }

        // Update delivery attempt
        $updated = $this->CommonModel->updateData(
            'b2b_orders_delivery_details',
            ['order_id' => $order_id, 'delivery_attempt_no' => $attempt_no],
            [
                'reason_for_attempt_failed' => $reason,
                'delivery_status'           => $failedStatus,
                'updated_at'                => date('Y-m-d H:i:s')
            ]
        );

        // If this is the 3rd attempt, update main order status to 13 (Warehouse Pickup)
        if ($attempt_no == 2) {
            $this->CommonModel->updateData(
                'b2b_orders',                   // Table
                ['order_id' => $order_id],      // Condition
                ['status' => 13]                 // Update status
            );
        }

        if ($updated) {
            echo json_encode(['status' => 200, 'message' => 'Delivery attempt marked as failed.']);
        } else {
            echo json_encode(['status' => 500, 'message' => 'No changes were made.']);
        }
        exit;
    } else {
        echo json_encode(['status' => 500, 'message' => 'Invalid data.']);
        exit;
    }
}



public function MarkAsFailedPopup() {
    $order_id   = $this->input->post('order_id');
    $attempt_no = $this->input->post('attempt_no');

    $data['order_id']   = $order_id;
    $data['attempt_no'] = $attempt_no;

    $this->load->view('b2b/order/mark-failed-popup', $data);
}


	public function markDelivered()
	{
		if ($this->input->post('order_id')) {
			$order_id = $this->input->post('order_id');
 			$User_id    = $this->session->userdata('LoginID');


			  // Get last delivery attempt for this order
        $lastAttempt = $this->db->where('order_id', $order_id)
            ->order_by('delivery_attempt_no', 'DESC')
            ->limit(1)
            ->get('b2b_orders_delivery_details')
            ->row();

        $currentAttempt = $lastAttempt ? (int)$lastAttempt->delivery_attempt_no : 0;
        $nextAttempt    = $currentAttempt + 1;

			 $insertData = [
            'order_id'            => $order_id,
            'delivery_type'       => 2,
            'driver_id'           => '',
            'delivery_date'       => date('Y-m-d H:i:s'),
            'remarks'             => 'Order Delivered Successfully',
            'delivery_status'     => 8,
            'delivery_attempt_no' =>   $nextAttempt,
			'reason_for_attempt_failed' => 'Success',
            'generate_by'         => $User_id,
            'created_at'          => date('Y-m-d H:i:s'),
            'ip'                  => $this->input->ip_address()
        ];

        $this->CommonModel->insertData('b2b_orders_delivery_details', $insertData);

			// Update status to Delivered (8)
			$updated = $this->CommonModel->updateData(
				'b2b_orders',
				['order_id' => $order_id],
				['status' => 8]
			);

			$orderData = $this->CommonModel->getOrderDataByb2bOrderId($order_id);

			if ($orderData && !empty($orderData->webshop_order_id)) {
				$updated2 = $this->CommonModel->updateData(
					'sales_order',
					['order_id' => $orderData->webshop_order_id],
					['status' => 2]
				);
			}

			if ($updated) {
				echo json_encode(['status' => 200, 'message' => 'Order marked as delivered successfully.']);
			} else {
				echo json_encode(['status' => 500, 'message' => 'Failed to update order status.']);
			}
		} else {
			echo json_encode(['status' => 500, 'message' => 'Invalid request.']);
		}
	}



	public function markCollected()
	{
		if ($this->input->post('order_id')) {
			$order_id = $this->input->post('order_id');
		$User_id    = $this->session->userdata('LoginID');

			  // Get last delivery attempt for this order
        $lastAttempt = $this->db->where('order_id', $order_id)
            ->order_by('delivery_attempt_no', 'DESC')
            ->limit(1)
            ->get('b2b_orders_delivery_details')
            ->row();

        $currentAttempt = $lastAttempt ? (int)$lastAttempt->delivery_attempt_no : 0;
        $nextAttempt    = $currentAttempt + 1;


				 // Insert new delivery attempt record
        $insertData = [
            'order_id'            => $order_id,
            'delivery_type'       => 2,
            'driver_id'           => '',
            'delivery_date'       => date('Y-m-d H:i:s'),
            'remarks'             => 'Customer Collected Order From Warehouse',
            'delivery_status'     => 9,
            'delivery_attempt_no' => $nextAttempt,
			'reason_for_attempt_failed' => 'Success',
            'generate_by'         => $User_id,
            'created_at'          => date('Y-m-d H:i:s'),
            'ip'                  => $this->input->ip_address()
        ];

        $this->CommonModel->insertData('b2b_orders_delivery_details', $insertData);

			// Update status to Delivered (8)
			$updated = $this->CommonModel->updateData(
				'b2b_orders',
				['order_id' => $order_id],
				['status' => 9]
			);

			$orderData = $this->CommonModel->getOrderDataByb2bOrderId($order_id);

			if ($orderData && !empty($orderData->webshop_order_id)) {
				$updated2 = $this->CommonModel->updateData(
					'sales_order',
					['order_id' => $orderData->webshop_order_id],
					['status' => 2]
				);
			}

			if ($updated) {
				echo json_encode(['status' => 200, 'message' => 'Order marked as collected successfully.']);
			} else {
				echo json_encode(['status' => 500, 'message' => 'Failed to update order status.']);
			}
		} else {
			echo json_encode(['status' => 500, 'message' => 'Invalid request.']);
		}
	}

	public function getDeliveryAttemptsPopup() {
		$order_id = $this->input->post('order_id');

		$deliveryAttempts = $this->B2BOrdersModel->getMultiDataById(
			'b2b_orders_delivery_details', 
			['order_id' => $order_id], 
			'', 
			'id', 
			'ASC'
		);

		$OrderData = $this->B2BOrdersModel->getSingleDataByID('b2b_orders', ['order_id' => $order_id], '*');

		$data['deliveryAttempts'] = $deliveryAttempts;
		$data['OrderData'] = $OrderData;

		// Load view as HTML
		$this->load->view('b2b/order/delivery_attempts_popup', $data);
	}


	// ===============================
// PARENT LEVEL DELIVERY FUNCTIONS
// ===============================

/**
 * Show Assign Delivery popup for Parent-Level Delivery
 */
public function AssignParentDeliveryPopup()
{
    $webshop_order_id = $this->input->post('webshop_order_id');
    $attempt_no       = $this->input->post('attempt_no');

    if (!$webshop_order_id) {
        echo "Invalid request";
        return;
    }

    // Fetch all delivery persons
    $delivery_persons = $this->CommonModel->getAllDeliveryPersons();
    foreach ($delivery_persons as &$d) {
        $d->name = trim($d->first_name . ' ' . $d->last_name);
    }

    $data = [
        'webshop_order_id' => $webshop_order_id,
        'attempt_no'       => $attempt_no,
        'delivery_persons' => $delivery_persons,
    ];

    $this->load->view('b2b/order/assign_parent_delivery_popup', $data);
}


/**
 * Save parent-level delivery assignment (popup submit)
 */
public function saveParentDeliveryAssignment()
{
    $User_id          = $this->session->userdata('LoginID');
    $webshop_order_id = $this->input->post('webshop_order_id');
    $driver_id        = $this->input->post('driver_id');
    $delivery_date    = $this->input->post('delivery_date');
    $remarks          = $this->input->post('remarks');

    header('Content-Type: application/json');

    if (!$webshop_order_id || !$driver_id || !$delivery_date) {
        echo json_encode(['status' => 400, 'msg' => 'All fields are required.']);
        return;
    }

    // Start transaction
    $this->db->trans_start();

    // Re-fetch last attempt (fresh) and compute nextAttempt
    $lastAttempt = $this->db
        ->where('webshop_order_id', $webshop_order_id)
        ->where('is_parent_level', 1)
        ->order_by('delivery_attempt_no', 'DESC')
        ->limit(1)
        ->get('b2b_orders_delivery_details')
        ->row();

    $nextAttempt = $lastAttempt ? ((int)$lastAttempt->delivery_attempt_no + 1) : 1;

    if ($nextAttempt > 2) {
        $this->db->trans_complete();
        echo json_encode(['status' => 400, 'msg' => 'Maximum 2 delivery attempts allowed.']);
        return;
    }

    // Double-insert prevention: check if a row for this attempt already exists
    $already = $this->db
        ->where('webshop_order_id', $webshop_order_id)
        ->where('is_parent_level', 1)
        ->where('delivery_attempt_no', $nextAttempt)
        ->get('b2b_orders_delivery_details')
        ->row();

    if ($already) {
        // Someone else already created it (or duplicate request)
        $this->db->trans_complete();
        log_message('error', "Duplicate delivery insert prevented for webshop_order_id={$webshop_order_id}, attempt={$nextAttempt}");
        echo json_encode(['status' => 409, 'msg' => 'This delivery attempt has already been created.']);
        return;
    }

    $delivery_status = ($nextAttempt == 1) ? 1 : 3;
    $order_status    = ($nextAttempt == 1) ? 4 : 5;

    $insert = [
        'webshop_order_id'         => $webshop_order_id,
        'is_parent_level'          => 1,
        'delivery_type'            => 2,
        'driver_id'                => $driver_id,
        'own_delivery_person_name' => NULL,
        'delivery_date'            => $delivery_date,
        'remarks'                  => $remarks,
        'delivery_status'          => $delivery_status,
        'delivery_attempt_no'      => $nextAttempt,
        'generate_by'              => $User_id ?? 0,
        'created_at'               => date('Y-m-d H:i:s'),
        'ip'                       => $this->input->ip_address(),
    ];

    $this->db->insert('b2b_orders_delivery_details', $insert);
    $insert_id = $this->db->insert_id();

    // Update sub-orders under parent
    $this->db->where('webshop_order_id', $webshop_order_id)->update('b2b_orders', ['status' => $order_status]);

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
        log_message('error', "Failed to insert delivery for webshop_order_id={$webshop_order_id}");
        echo json_encode(['status' => 500, 'msg' => 'Failed to assign delivery, try again.']);
        return;
    }

    log_message('info', "Parent delivery assigned: webshop_order_id={$webshop_order_id}, attempt={$nextAttempt}, id={$insert_id}");
    echo json_encode(['status' => 200, 'msg' => 'Parent-level delivery assigned successfully.']);
}



/**
 * Fetch Parent-Level Delivery Details (popup)
 */
public function getParentDeliveryDetailsPopup()
{
    $webshop_order_id = $this->input->post('webshop_order_id');

    $data['attempts'] = $this->db->where('webshop_order_id', $webshop_order_id)
                                 ->where('is_parent_level', 1)
                                 ->order_by('delivery_attempt_no', 'ASC')
                                 ->get('b2b_orders_delivery_details')
                                 ->result();

    $this->load->view('b2b/order/parent_delivery_details_popup', $data);
}


/**
 * Assign delivery to all sub-orders directly (bulk assign)
 */
public function assign_delivery_bulk()
{
    $webshop_order_id = $this->input->post('webshop_order_id');
    $User_id          = $this->session->userdata('LoginID');

    $parent = $this->db->get_where('webshop_orders', ['order_id' => $webshop_order_id])->row();
    if (!$parent) {
        echo json_encode(['status' => false, 'msg' => 'Parent order not found.']);
        return;
    }

    // Prevent duplicate parent delivery record
    $existing = $this->db->where('webshop_order_id', $webshop_order_id)
                         ->where('is_parent_level', 1)
                         ->get('b2b_orders_delivery_details')
                         ->row();
    if ($existing) {
        echo json_encode(['status' => false, 'msg' => 'Parent-level delivery already assigned.']);
        return;
    }

    $insert = [
        'webshop_order_id'    => $webshop_order_id,
        'is_parent_level'     => 1,
        'delivery_type'       => 2,
        'delivery_status'     => 1,
        'delivery_attempt_no' => 1,
        'delivery_date'       => date('Y-m-d'),
        'generate_by'         => $User_id ?? 0,
        'created_at'          => date('Y-m-d H:i:s'),
        'ip'                  => $this->input->ip_address(),
    ];

    $this->db->insert('b2b_orders_delivery_details', $insert);
    echo json_encode(['status' => true, 'msg' => 'Delivery assigned for all sub-orders (parent-level).']);
}


/**
 * Mark Parent as Collected
 */
public function markParentCollected()
{
    $webshop_order_id = $this->input->post('webshop_order_id');
    $User_id = $this->session->userdata('LoginID');

    if (!$webshop_order_id) {
        echo json_encode(['status' => 500, 'message' => 'Invalid parent order ID.']);
        exit;
    }

    $nextAttempt = $this->getNextParentAttempt($webshop_order_id);

    $insertData = [
        'webshop_order_id'         => $webshop_order_id,
        'is_parent_level'          => 1,
        'delivery_type'            => 2,
        'driver_id'                => '',
        'delivery_date'            => date('Y-m-d H:i:s'),
        'remarks'                  => 'Collected by customer from warehouse.',
        'delivery_status'          => 9,
        'delivery_attempt_no'      => $nextAttempt,
        'reason_for_attempt_failed'=> 'Success',
        'generate_by'              => $User_id ?? 0,
        'created_at'               => date('Y-m-d H:i:s'),
        'ip'                       => $this->input->ip_address(),
    ];
    $this->CommonModel->insertData('b2b_orders_delivery_details', $insertData);

    //$this->CommonModel->updateData('webshop_orders', ['order_id' => $webshop_order_id], ['status' => 9]);
	
    $this->CommonModel->updateData('b2b_orders', ['webshop_order_id' => $webshop_order_id], ['status' => 9]);

	if ($webshop_order_id && !empty($webshop_order_id)) {
		$updated2 = $this->CommonModel->updateData(
			'sales_order',
			['order_id' => $webshop_order_id],
			['status' => 2]
		);
	}


    echo json_encode(['status' => 200, 'message' => 'Parent order marked as collected successfully.']);
}


/**
 * Mark Parent as Delivered
 */
public function markParentDelivered()
{
    $webshop_order_id = $this->input->post('webshop_order_id');
    $User_id = $this->session->userdata('LoginID');

    if (!$webshop_order_id) {
        echo json_encode(['status' => 500, 'message' => 'Invalid parent order ID.']);
        exit;
    }

    $nextAttempt = $this->getNextParentAttempt($webshop_order_id);

    $insertData = [
        'webshop_order_id'         => $webshop_order_id,
        'is_parent_level'          => 1,
        'delivery_type'            => 2,
        'driver_id'                => '',
        'delivery_date'            => date('Y-m-d H:i:s'),
        'remarks'                  => 'Parent order delivered successfully.',
        'delivery_status'          => 8,
        'delivery_attempt_no'      => $nextAttempt,
        'reason_for_attempt_failed'=> 'Success',
        'generate_by'              => $User_id ?? 0,
        'created_at'               => date('Y-m-d H:i:s'),
        'ip'                       => $this->input->ip_address(),
    ];
    $this->CommonModel->insertData('b2b_orders_delivery_details', $insertData);

    //$this->CommonModel->updateData('webshop_orders', ['order_id' => $webshop_order_id], ['status' => 8]);
    $this->CommonModel->updateData('b2b_orders', ['webshop_order_id' => $webshop_order_id], ['status' => 8]);

	if ($webshop_order_id && !empty($webshop_order_id)) {
		$updated2 = $this->CommonModel->updateData(
			'sales_order',
			['order_id' => $webshop_order_id],
			['status' => 2]
		);
	}


    echo json_encode(['status' => 200, 'message' => 'Parent order marked as delivered successfully.']);
}


/**
 * Mark Parent Delivery as Failed (after selecting reason)
 */
public function markParentFailed()
{
    $webshop_order_id = $this->input->post('webshop_order_id');
    $attempt_no       = (int)$this->input->post('attempt_no');
    $reason           = $this->input->post('reason_for_attempt_failed');
    $User_id          = $this->session->userdata('LoginID');

    if (!$webshop_order_id || !$attempt_no || empty($reason)) {
        echo json_encode(['status' => 400, 'message' => 'Invalid request data.']);
        exit;
    }

    $failedStatus = ($attempt_no == 2) ? 4 : 2;

    $record = $this->db->where([
        'webshop_order_id' => $webshop_order_id,
        'delivery_attempt_no' => $attempt_no,
        'is_parent_level' => 1
    ])->get('b2b_orders_delivery_details')->row();

    if (!$record) {
        echo json_encode(['status' => 404, 'message' => 'Parent delivery attempt not found.']);
        exit;
    }

    $this->CommonModel->updateData(
        'b2b_orders_delivery_details',
        ['webshop_order_id' => $webshop_order_id, 'delivery_attempt_no' => $attempt_no, 'is_parent_level' => 1],
        [
            'reason_for_attempt_failed' => $reason,
            'delivery_status' => $failedStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]
    );

    // On 2nd fail, mark all as "return to warehouse"
    if ($attempt_no == 2) {
       // $this->CommonModel->updateData('webshop_orders', ['order_id' => $webshop_order_id], ['status' => 13]);
        $this->CommonModel->updateData('b2b_orders', ['webshop_order_id' => $webshop_order_id], ['status' => 13]);
    }

    echo json_encode(['status' => 200, 'message' => 'Parent delivery attempt marked as failed.']);
}


/**
 * Load popup for "Mark Parent as Failed"
 */
public function markParentFailedPopup()
{
    $webshop_order_id = $this->input->post('webshop_order_id');
    $attempt_no       = $this->input->post('attempt_no');

    $data = [
        'webshop_order_id' => $webshop_order_id,
        'attempt_no'       => $attempt_no,
    ];

    $this->load->view('b2b/order/parent_mark_failed_popup', $data);
}


/**
 * Helper: Get next parent delivery attempt number
 */
private function getNextParentAttempt($webshop_order_id)
{
    $lastAttempt = $this->db->where('webshop_order_id', $webshop_order_id)
                            ->where('is_parent_level', 1)
                            ->order_by('delivery_attempt_no', 'DESC')
                            ->limit(1)
                            ->get('b2b_orders_delivery_details')
                            ->row();

    return $lastAttempt ? ((int)$lastAttempt->delivery_attempt_no + 1) : 1;
}
/**
 * Collect Parent Order from Warehouse
 */
public function collectParentFromWarehouse()
{
    $webshop_order_id = $this->input->post('webshop_order_id');
    $User_id = $this->session->userdata('LoginID');

    if (!$webshop_order_id) {
        echo json_encode(['status' => 400, 'message' => 'Invalid parent order ID.']);
        exit;
    }

    $nextAttempt = $this->getNextParentAttempt($webshop_order_id);

    $insertData = [
        'webshop_order_id'         => $webshop_order_id,
        'is_parent_level'          => 1,
        'delivery_type'            => 2,
        'driver_id'                => '',
        'delivery_date'            => date('Y-m-d H:i:s'),
        'remarks'                  => 'Collected from warehouse.',
        'delivery_status'          => 9,
        'delivery_attempt_no'      => $nextAttempt,
        'reason_for_attempt_failed'=> 'Success',
        'generate_by'              => $User_id ?? 0,
        'created_at'               => date('Y-m-d H:i:s'),
        'ip'                       => $this->input->ip_address(),
    ];

    $this->CommonModel->insertData('b2b_orders_delivery_details', $insertData);

    //$this->CommonModel->updateData('webshop_orders', ['order_id' => $webshop_order_id], ['status' => 9]);
    $this->CommonModel->updateData('b2b_orders', ['webshop_order_id' => $webshop_order_id], ['status' => 9]);

	
	if ($webshop_order_id && !empty($webshop_order_id)) {
		$updated2 = $this->CommonModel->updateData(
			'sales_order',
			['order_id' => $webshop_order_id],
			['status' => 2]
		);
	}


    echo json_encode(['status' => 200, 'message' => 'Parent order collected from warehouse successfully.']);
}

}
