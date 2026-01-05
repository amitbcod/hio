<?php

defined('BASEPATH') or exit('No direct script access allowed'); 

class B2BOrderspublisherController extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		if ($this->session->userdata('LoginID') == '') {
			redirect(base_url());
		}
		if (!empty($this->session->userdata('userPermission')) && !in_array('b2webshop/orders', $this->session->userdata('userPermission'))) {
		}

		$this->load->model('B2BOrderspublisherModel');
		$this->load->model('CommonModel'); 
		$this->load->model('ShopProductModel');
	}

	public function index()
	{
		$current_tab = $this->uri->segment(2);
		$data['PageTitle'] = 'B2B - publisher order';
		$data['side_menu'] = 'b2b';
		$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';

        $this->load->view('b2b/order/publisherorderlist', $data);
	}

	public function loadordersajax()
{
    $order_status = $this->input->post('order_status');
    $fromDate = $this->input->post('from_date');
    $toDate = $this->input->post('to_date');
    $current_tab = $this->input->post('current_tab');
    $shop_id = $this->session->userdata('ShopID');
    $ProductData = $this->B2BOrderspublisherModel->get_datatables_orders( $order_status, $fromDate, $toDate);
    
    $this->load->model('ShopProductModel');
    $data = array();
    $no = $_POST['start'];

    // Grouping orders by publisher
    $groupedOrders = [];

    foreach ($ProductData as $readData) {
        $publisher_name = $this->CommonModel->getWebShopNameByShopId($readData->publisher_id);
        
        // Initialize the publisher group if it doesn't exist
        if (!isset($groupedOrders[$publisher_name])) {
            $groupedOrders[$publisher_name] = [];
        }
        
        // Add the order to the publisher group
        $groupedOrders[$publisher_name][] = $readData;
    }

    // Build the output for each publisher
    foreach ($groupedOrders as $publisher => $orders) {
        // Add a row for the publisher name
        $data[] = ['<strong>' . htmlspecialchars($publisher) . '</strong>', '', '', '', '', '', '', '', '', '', ''];

        foreach ($orders as $readData) {
			$no++;
			$row = array();
		
			// URLs for order details
			$order_url = base_url() . 'webshop/b2b/order/detail/' . $readData->order_id;
			$print_url = base_url() . 'webshop/b2b/order/print/' . $readData->order_id;
		
			// Get order payment details and status
			$order_payment_detail_id = $readData->order_id;
			$order_payment_detail_status = $this->B2BOrderspublisherModel->order_payment_detail_status($order_payment_detail_id);
			$order_status_label = $this->CommonModel->getOrderStatusLabel($readData->status);
			$_sales_order = $this->ShopProductModel->getSingleDataByID('sales_order', array('order_id' => $readData->webshop_order_id), '');
		
			// Get customer name
			$customerName = ($readData->parent_id > 0)
				? $this->B2BOrderspublisherModel->getOrderCustomerNameByOrderId($readData->order_id)
				: $readData->customer_name;
		
			$purchaseOnDate = date(SIS_DATE_FM_WT, $readData->created_at);
			$shipping_charge = $readData->shipping_amount;
		
			// Get publisher commission and details
			$publisher_commision_per = $this->CommonModel->getWebShopCommisionByShopId($readData->publisher_id);
			$publisherdetails = $this->B2BOrderspublisherModel->getPublisherDetails($readData->publisher_id);
			$publication_name = $publisherdetails->publication_name ?? '';
		
			// Calculate total grand amount
			$total_grand_ = $readData->grand_total - $shipping_charge;
		
			// Check for the specific publication name
			if (trim($publisher) == 'The Institute of Cost Accountants of India') {
				$publisher_commision_per = 0;
				$whuso_income = 100 * $readData->total_qty_ordered;
			} else {
				$whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
			}
		
			// Check for specific product names
			$product_name = $_sales_order->product_name ?? ''; // Assuming you have product_name in $_sales_order
			if (trim($product_name) === 'Financial Times Asia (Print Edition)') {
				$publisher_commision_per = 10;
				$whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
			}
		
			if (trim($product_name) === 'Financial Times Weekend Newspaper with Magazine') {
				$publisher_commision_per = 10;
				$whuso_income = (($publisher_commision_per / 100) * ($readData->grand_total - $readData->shipping_amount));
			}
		
			// Additional check for a specific order ID
			if ($readData->order_id == '1471') {
				$publisher_commision_per = 0;
				$whuso_income = 4340;
			}
		
			// Calculate payable amount
			$Payable_Amount = ($total_grand_ - $whuso_income) + $shipping_charge;
		
			// Prepare row data
			$row[] = '<input type="checkbox" name="checkboxes[]" value="' . htmlspecialchars($publisher) . '">';
			$row[] = $readData->increment_id;
			$row[] = ($_sales_order->increment_id ?? '');
			$row[] = $purchaseOnDate;
			$row[] = ucwords($customerName);
			$row[] = $readData->total_qty_ordered;
			$row[] = number_format($readData->subtotal, 2);
			$row[] = number_format($readData->discount_amount, 2);
			$row[] = number_format($readData->tax_amount, 2);
			$row[] = number_format($shipping_charge, 2);
			$row[] = number_format($publisher_commision_per, 2) . '%';
			$row[] = number_format($whuso_income, 2);
			$row[] = number_format($Payable_Amount, 2);
			$row[] = $order_status_label . ' ' . $order_payment_detail_status;
		
		
            $data[] = $row;
        }

		
    }

    $output = array(
        "draw" => $_POST['draw'],
        "recordsTotal" => $this->B2BOrderspublisherModel->count_all_orders( $order_status, $fromDate, $toDate),
        "recordsFiltered" => $this->B2BOrderspublisherModel->count_filtered_orders( $order_status, $fromDate, $toDate),
        "data" => $data,
    );

    // Output to json format
    echo json_encode($output);
    exit;
}


public function initiatepaymentpublisher() {
    header('Access-Control-Allow-Origin: *'); 
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json');

    // Get the POST data
    $selectedOrders = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 400, 'message' => 'Invalid JSON received.', 'error' => json_last_error_msg()]);
        return;
    }

    // Prepare email content
    $PublisherOrdertable = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
    $PublisherOrdertable .= '<thead><tr>
        <th>Order Number</th>
        <th>Webshop Order No.</th>
        <th>Purchased On</th>
        <th>Customer Name</th>
        <th>Quantity</th>
        <th>B2B Order Total</th>
        <th>Discount</th>
        <th>B2B Taxes Amount</th>
        <th>Shipping Amount</th>
        <th>Publisher Commission</th>
        <th>Whuso Income</th>
        <th>B2B Net Payable Amount</th>
    </tr></thead><tbody>';

    $orderNumbers = []; // To keep track of order numbers for email subject

    foreach ($selectedOrders as $publisher => $orders) {
        if (!isset($orders['orders']) || !is_array($orders['orders'])) {
            continue; // Skip if 'orders' is not set or not an array
        }
		$PublisherOrdertable .= '<tr><td colspan="12" style="font-weight: bold; text-align: center;">Publisher: ' . htmlspecialchars($publisher) . '</td></tr>'; // Publisher name

        foreach ($orders['orders'] as $order) {
            $orderNumber = htmlspecialchars($order['orderNumber'] ?? ''); // Safe access
            $orderNumbers[] = $orderNumber;

            $PublisherOrdertable .= '<tr>
                <td>' . $orderNumber . '</td>
                <td>' . htmlspecialchars($order['webshopOrderNo'] ?? '') . '</td>
                <td>' . htmlspecialchars($order['purchaseDate'] ?? '') . '</td>
                <td>' . htmlspecialchars($order['customerName'] ?? '') . '</td>
                <td>' . htmlspecialchars($order['quantity'] ?? 0) . '</td>
                <td>' . number_format($order['baseSubtotal'] ?? 0, 2) . '</td>
                <td>' . number_format($order['discountAmount'] ?? 0, 2) . '</td>
                <td>' . number_format($order['taxAmount'] ?? 0, 2) . '</td>
                <td>' . number_format($order['shippingAmount'] ?? 0, 2) . '</td>
                <td>' . number_format($order['publisherCommission'] ?? 0, 2) . '%</td>
                <td>' . number_format($order['whusoIncome'] ?? 0, 2) . '</td>
                <td>' . number_format($order['netPayableAmount'] ?? 0, 2) . '</td>
            </tr>';
        }
        
        // Append total price for this publisher
        $totalAmount = $orders['total'] ?? 0; // Ensure total is set
        $PublisherOrdertable .= '<tr>
            <td colspan="11" style="font-weight: bold;">Total Price for ' . htmlspecialchars($publisher) . ':</td>
            <td style="font-weight: bold;">' . number_format($totalAmount, 2) . '</td>
        </tr>';
    }

    $PublisherOrdertable .= '</tbody></table>';

    // Only use the last order number for the email

    // Send email with the table
    $templateId = 'hbr_payment_done_for_order'; // Define your email template ID
    $to = 'jyotipnd52@gmail.com'; // Replace with actual recipient email
    $cc = ''; // CC if necessary
    $ccEmails = explode(',', $cc);
    $shop_name = 'indiamags';
    $site_logo = ''; // Define your logo URL here

    $CommonVars = array($site_logo, $shop_name);
    $SubDynamic = array($publisher, $orderNumber);

    // Check email send status
    $emailSendStatusFlag = $this->CommonModel->sendEmailStatus($templateId);
    if ($emailSendStatusFlag == 1) {
        $TempVars = ["##PUBLISHERORDERTABLE##"];
		$DynamicVars   = array('', $PublisherOrdertable, $orderNumber, $shop_name);

        // Ensure $DynamicVars is not empty before sending
        if (!empty($DynamicVars) && !is_null($DynamicVars[0])) {
            $mailSent = $this->B2BOrderspublisherModel->sendCommonHTMLEmail($to, $templateId, $TempVars, $DynamicVars, $SubDynamic, $CommonVars, $ccEmails);

            if ($mailSent) {
                echo json_encode(['status' => 200, 'message' => 'Payment initiated and email sent successfully.']);
            } else {
                // Log the error
                log_message('error', 'Email could not be sent: ' . $this->email->print_debugger());
                echo json_encode(['status' => 500, 'message' => 'Payment initiated, but email could not be sent.']);
            }
        } else {
            echo json_encode(['status' => 500, 'message' => 'Dynamic variables are empty. Email not sent.']);
        }
    } else {
        echo json_encode(['status' => 500, 'message' => 'Email status check failed.']);
    }

    exit(); // Ensure nothing is sent after this
}



	function detail()
	{
		$current_tab = $this->uri->segment(3);
		$order_id = $this->uri->segment(5);
		if (isset($order_id) && $order_id > 0) {
			$data['PageTitle'] = 'B2B - Orders';
			$data['side_menu'] = 'b2b';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';

			$shop_id		=	$this->session->userdata('ShopID');

			$data['OrderData'] = $OrderData = $this->B2BOrderspublisherModel->getSingleDataByID('b2b_orders', array('order_id' => $order_id), '');
			if (empty($OrderData)) {
				redirect('webshop/b2b-orders');
			}
			$data['OrderItems'] = $OrderItems = $this->B2BOrderspublisherModel->getOrderItems($order_id);
			$data['currency_code'] = '';




			$QtyScanItem = $this->B2BOrderspublisherModel->getQtyFullyScannedOrderItems($order_id);
			$data['scanned_qty'] = count($QtyScanItem);
			$PendingScanQty = $this->B2BOrderspublisherModel->getQtyPendingScannedOrderItems($order_id);
			$data['pending_scanned_qty'] = count($PendingScanQty);


			if ($OrderData->shipment_type == 2) {
				if (isset($OrderData->webshop_order_id)  && $OrderData->webshop_order_id > 0) {
					$webshop_order_id = $OrderData->webshop_order_id;
					$webshop_shop_id = $OrderData->publisher_id;
					$B2b_increment_id = $OrderData->increment_id;
					$patent_order_id = $OrderData->order_id;


					$args['shop_id']	=	$webshop_shop_id;

					$this->load->model('ShopProductModel');

					$data['ShippingAddress'] = $ShippingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 2), '');
					$data['BillingAddress'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('sales_order_address', array('order_id' => $webshop_order_id, 'address_type' => 1), '');
					$data['PublisherPayment'] = $BillingAddress = $this->ShopProductModel->getSingleDataByID('publisher_payment', array('order_id' => $patent_order_id, 'B2b_order_id' => $B2b_increment_id), '');
					$data['OrderData'] = $OrderItemData = $this->ShopProductModel->getSingleDataByID('b2b_orders', array('webshop_order_id' => $webshop_order_id, 'increment_id' => $B2b_increment_id), '');

					$data['PublisherDetails'] = $publisherdetails = $this->B2BOrderspublisherModel->getSingleDataByID('publisher', array('id' => $OrderItemData->publisher_id), '');
					$data['FormattedAddress'] = $this->ShopProductModel->getFormattedAddress($ShippingAddress);
				}
			}
			if ($current_tab == 'order') {
				if ($OrderData->status == 1) {
					redirect(base_url() . 'b2b/order/create-shipment/' . $OrderData->order_id);
				}
				$this->load->view('b2b/order/main-order-detail', $data);
			} else if ($current_tab == 'split-order') {
				if ($OrderData->status == 1) {
					redirect(base_url() . 'b2b/order/create-shipment/' . $OrderData->order_id);
				}

				$data['ParentOrder'] = $ParentOrder = $this->B2BOrderspublisherModel->getSingleDataByID('b2b_orders', array('order_id' => $OrderData->main_parent_id), '');
				$data['SplitOrderIds'] = $this->B2BOrderspublisherModel->getSplitChildOrderIds($OrderData->main_parent_id);

				$this->load->view('b2b/order/split-order-detail', $data);
			} else if ($current_tab == 'shipped-order') {
				$data['SplitOrderIds'] = $this->B2BOrderspublisherModel->getSplitChildOrderIds($OrderData->order_id);
				$data['ShippedItem'] = $ShippedItem = $this->B2BOrderspublisherModel->getShippedOrderItems($OrderData->order_id, $OrderData->is_split);
				$this->load->view('b2b/order/shipped-order-detail', $data);
			} else {
				redirect('/b2b/orders');
			}
		} else {
			redirect('/b2b/orders');
		}
	}

	
	


	




	

	
}
