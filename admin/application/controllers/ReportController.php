<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ReportController extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		if ($this->session->userdata('LoginID') == '') {
			redirect(base_url());
		}

		$this->load->model('UserModel');
		$this->load->model('ReportModel');
		$this->load->model('CommonModel');
		$this->load->library('encryption');
	}

	public function customerList()
	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('reports', $this->session->userdata('userPermission'))) {
				redirect('dashboard');
			}
		}

		if (isset($_SESSION['LoginID'])) {
			$current_tab = $this->uri->segment(1);
			$shop_id		=	$this->session->userdata('ShopID');
			$data['PageTitle'] = 'Customers Reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$this->load->view('report/customers_reports', $data);
		} else {
			redirect(base_url('customer/login'));
		}
	}




	function postGenerateCustomers()
	{
		if (isset($_POST) && $_POST['fromDate'] != '' && $_POST['toDate'] != '') {
			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$fromDate = date(strtotime($fromDate_get));
			$toDate = date(strtotime($toDate_get));
			$customer_data_get = $this->ReportModel->getCustomerCSVImport($fromDate, $toDate);
			// echo "<pre>"; print_r($customer_data_get ); echo "</pre>"; die();
			if (isset($report_data_get) && count($report_data_get) > 0) {
				$this->downloadCustomerCsv($fromDate_get, $toDate_get);
			} else {
				$this->downloadCustomerCsv($fromDate_get, $toDate_get);
			}
		} else {
			$data['fromDate'] = $_POST['fromDate'];
			$data['toDate'] = $_POST['toDate'];
			$data['PageTitle'] = 'Reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			// $data['errorMsg']='No records found.';
			$data['errorMsg'] = 'Please select date.';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $this->session->userdata('ShopID')), '');
			$data['shop_flag'] = $FbcUser->shop_flag;
			$this->load->view('report/report_list', $data);
			/*echo json_encode(array('flag' => 0, 'msg' => "Please select date"));
			exit;*/
		}
	}


	public function downloadCustomerCsv($fromDate, $toDate)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array("Customer Id", "First Name", "Last Name", "Email", "Mobile No", "Gender", "Country Code", "Dob", "Company Name", "Vat No", "Customer Type", "Customer Type Id", "Access Prelaunch Product", "Allow Catalog Builder", "Created At", "Address First Name", "Address Last Name", "Address Mobile No", "Address Line1", "Address Line2", "Address City", "Address State", "Address Country", "Address Pincode", "Address Company Name", "Address Vat No", "Address Consulattion No", "Address Res Company Name", "Res Company Address", "Address Vet Vews Valid Flag", "Default Address");

		$customer_data = $this->ReportModel->getCustomerCSVImport($fromDate, $toDate);
		// echo '<pre>'; print_r($customer_data);
		// invoice_detail table
		if (isset($customer_data) && count($customer_data) > 0) {

			$ExportValuesArr1 = array();
			foreach ($customer_data as $key => $value) {
				$customer_id = $value['id'];
				$first_name = $value['first_name'];
				$last_name = $value['last_name'];
				$email_id = $value['email_id'];
				$mobile_no = $value['mobile_no'];
				$gender = $value['gender'];
				$country_code = $value['country_code'];
				$dob = $value['dob'];
				$company_name = $value['company_name'];
				$gst_no = $value['gst_no'];
				$customer_type_id = $value['customer_type_id'];
				$customer_type_name = $value['customer_type_name'];
				$access_prelanch_product = $value['access_prelanch_product'];
				$allow_catlog_builder = $value['allow_catlog_builder'];
				$created_at = $value['created_at'];
				$created = '';
				if ($created_at) {
					$created = date(SIS_DATE_FM_WT, $created_at);
				}
				$address_data = $this->ReportModel->getAddress($customer_id);
				if (isset($address_data) && count($address_data) > 0) {
					foreach ($address_data as $key => $value1) {
						$address_first_name = $value1['address_first_name'];
						$address_last_name = $value1['address_last_name'];
						$address_address_line2 = $value1['address_address_line2'];
						$address_address_line1 = $value1['address_address_line1'];
						$address_city = $value1['address_city'];
						$address_mobile_no = $value1['address_mobile_no'];
						$address_state = $value1['address_state'];
						$address_country = $value1['address_country'];
						$address_pincode = $value1['address_pincode'];
						$address_company_name = $value1['address_company_name'];
						$address_vat_no = $value1['address_vat_no'];
						$address_consulation_no = $value1['address_consulation_no'];
						$address_res_company_name = $value1['address_res_company_name'];
						$address_res_company_address = $value1['address_res_company_address'];
						$address_vat_vies_valid_flag = $value1['address_vat_vies_valid_flag'];
						$is_default = $value1['is_default'];

						$SingleRow = array($customer_id, $first_name, $last_name, $email_id, $mobile_no, $gender, $country_code, $dob, $company_name, $gst_no, $customer_type_name, $customer_type_id, $access_prelanch_product, $allow_catlog_builder, $created, $address_first_name, $address_last_name, $address_mobile_no, $address_address_line1, $address_address_line2, $address_city, $address_state, $address_country, $address_pincode, $address_company_name, $address_vat_no, $address_consulation_no, $address_res_company_name, $address_res_company_address, $address_vat_vies_valid_flag, $is_default);

						$ExportValuesArr1[] = $SingleRow;
					}
				} else {
					$address_first_name = '';
					$address_last_name = '';
					$address_mobile_no = '';
					$address_address_line1 = '';
					$address_address_line2 = '';
					$address_city = '';
					$address_state = '';
					$address_country = '';
					$address_pincode = '';
					$address_company_name = '';
					$address_vat_no = '';
					$address_consulation_no = '';
					$address_res_company_name = '';
					$address_res_company_address = '';
					$address_vat_vies_valid_flag = '';
					$is_default = '';

					$SingleRow = array($customer_id, $first_name, $last_name, $email_id, $mobile_no, $gender, $country_code, $dob, $company_name, $gst_no, $customer_type_name, $customer_type_id, $access_prelanch_product, $allow_catlog_builder, $created, $address_first_name, $address_last_name, $address_mobile_no, $address_address_line1, $address_address_line2, $address_city, $address_state, $address_country, $address_pincode, $address_company_name, $address_vat_no, $address_consulation_no, $address_res_company_name, $address_res_company_address, $address_vat_vies_valid_flag, $is_default);

					$ExportValuesArr1[] = $SingleRow;
				}
			}
			$filename_csv = 'SIS-Report-Customer-' . date('d-m-Y') . '.csv';
			// $filename_csv = 'CA-Sales-Order-Report-' . time() . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");
			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
				foreach ($ExportValuesArr1 as $readData_one) {
					fputcsv($file_csv, $readData_one);
				}
			}
			fclose($file_csv);
			exit();
		} else {
			// echo 'No records found.';
			$filename_csv = 'SIS-Report-Customer-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			fclose($file_csv);
			exit();
		}
	}

	public function publisherList()
	{
		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('reports', $this->session->userdata('userPermission'))) {
				redirect('dashboard');
			}
		}

		if (isset($_SESSION['LoginID'])) {
			$current_tab = $this->uri->segment(1);
			$data['PageTitle'] = 'Publisher Reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$this->load->view('report/publisher_reports', $data);
		} else {
			redirect(base_url('customer/login'));
		}
	}

	function postGeneratePublisher()
	{
		if (isset($_POST) && $_POST['fromDate'] != '' && $_POST['toDate'] != '') {
			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$fromDate = date(strtotime($fromDate_get));
			$toDate = date(strtotime($toDate_get));
			$publisher_data_get = $this->ReportModel->getPublisherCSVImport($fromDate, $toDate);
			if (isset($publisher_data_get) && count($publisher_data_get) > 0) {

				$this->downloadPublisherCsv($fromDate_get, $toDate_get, $publisher_data_get);
			} else {
				$report_export_header = array("Publisher Id", "Email", "Publication Name", "Vendor Name", "Commission Percent", "Mobile No", "Description", "Created At");

				$filename_csv = 'Indiamags-Report-Publisher-' . date('d-m-Y') . '.csv';
				header("Content-Description: File Transfer");
				header("Content-Disposition: attachment; filename=$filename_csv");
				header("Content-Type: application/csv; ");
				// file creation
				$file_csv = fopen('php://output', 'w');
				fputcsv($file_csv, $report_export_header);
				fclose($file_csv);
				exit();
			}
		} else {

			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
			exit;
		}
	}

	public function downloadPublisherCsv($fromDate, $toDate, $publisher_data)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array("Publisher Id", "Email", "Publication Name", "Vendor Name", "Commission Percent", "Mobile No", "Description", "Created At");
		$ExportValuesArray = array();
		foreach ($publisher_data as $key => $value) {
			$publisher_id = $value['id'];
			$email = $value['email'];
			$publication_name = $value['publication_name'];
			$vendor_name = $value['vendor_name'];
			$commision_percent = $value['commision_percent'];
			$phone_no = $value['phone_no'];
			$description = $value['description'];
			$created_at = $value['created_at'];
			$created = '';
			if ($created_at) {
				$created = date(SIS_DATE_FM_WT, $created_at);
			}
			$SingleRow = array($publisher_id, $email, $publication_name, $vendor_name, $commision_percent, $phone_no, $description, $created);
			$ExportValuesArr1[] = $SingleRow;
		}
		$filename_csv = 'Indiamags-Report-Publisher-' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename_csv");
		header("Content-Type: application/csv; ");
		// file creation
		$file_csv = fopen('php://output', 'w');

		fputcsv($file_csv, $report_export_header);
		if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
			foreach ($ExportValuesArr1 as $readData_one) {
				fputcsv($file_csv, $readData_one);
			}
		}
		fclose($file_csv);
		exit();
	}

	public function reportlist()
	{
		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('reports', $this->session->userdata('userPermission'))) {
				redirect('dashboard');
			}
		}

		if (isset($_SESSION['LoginID'])) {

			$current_tab = $this->uri->segment(1);
			$shop_id		=	$this->session->userdata('ShopID');
			$data['PageTitle'] = 'Reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$data['ReportList'] = $ReportList = $this->ReportModel->getReportList();
			$data['CatalogueDiscount'] = $getCatalogueDiscountList = $this->ReportModel->getCatalogueDiscountList();
			//$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);
			$this->load->view('report/report_main', $data);
		} else {
			redirect(base_url('customer/login'));
		}
	}

	public function getreportlist()
	{

		if (isset($_POST)) {
			// print_r($_POST);
			// die();
			$shop_id = $this->session->userdata('ShopID');
			$search_param = array();
			if (!empty($_POST['search'])) {
				$search_param['keyword'] = $_POST['search'];
			}
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$data['ReportList'] = $ReportList = $this->ReportModel->getReportList($search_param);
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);
			$data['CatalogueDiscount'] = $getCatalogueDiscountList = $this->ReportModel->getCatalogueDiscountList();
			// print_r($data );
			// die();
			$this->load->view('report/report_list', $data);
		}
	}

	function postGenerateReport()
	{
		if (isset($_POST) && $_POST['fromDate'] != '' && $_POST['toDate'] != '') {
			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$fromDate = date(strtotime($fromDate_get));
			$toDate = date(strtotime($toDate_get));
			$report_data_get = $this->ReportModel->getReportCSVImport($fromDate, $toDate);
			// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();
			if (isset($report_data_get) && count($report_data_get) > 0) {
				$this->downloadReportCsv($fromDate_get, $toDate_get);
				// echo 1;exit();
			} else {
				$this->downloadReportCsv($fromDate_get, $toDate_get);
				// $data['fromDate']=$fromDate_get;
				// $data['toDate']=$toDate_get;
				// $data['PageTitle']='Reports';
				// $data['side_menu']='reports';
				// $data['current_tab']=(isset($current_tab) && $current_tab!='')?$current_tab:'';
				// $data['errorMsg']='No records found.';
				// $FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
				// $data['shop_flag']=$FbcUser->shop_flag;
				// $this->load->view('report/report_list',$data);
			}
			// echo json_encode(array('flag' => 1, 'msg' => "Sales report generate success"));
			// exit;
		} else {
			$data['fromDate'] = $_POST['fromDate'];
			$data['toDate'] = $_POST['toDate'];
			$data['PageTitle'] = 'Reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			// $data['errorMsg']='No records found.';
			$data['errorMsg'] = 'Please select date.';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $this->session->userdata('ShopID')), '');
			$data['shop_flag'] = $FbcUser->shop_flag;
			$this->load->view('report/report_list', $data);
			/*echo json_encode(array('flag' => 0, 'msg' => "Please select date"));
			exit;*/
		}
	}
	//public function downloadReportcsv()
	public function downloadReportCsv($fromDate, $toDate)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array("Order #", "Redeem Date", "Voucher Code", "Customer Type", "Customer Name", "Email", "Amount Redeem", "Status");

		$reports_data = $this->ReportModel->getReportCSVImport($fromDate, $toDate);
		//echo count($sales_data);exit();
		// invoice_detail table
		if (isset($reports_data) && count($reports_data) > 0) {

			$ExportValuesArr1 = array();
			foreach ($reports_data as $key => $value) {
				$customer_id = $value['customer_id'];
				$increment_id = $value['increment_id'];
				$order_date_data = $value['created_at'];
				$voucher_code = $value['voucher_code'];
				$customer_login_type = $value['customer_login_type'];
				$CustomerName = $value['customer_firstname'] . ' ' . $value['customer_lastname'];
				$customer_email = $value['customer_email'];
				// $redeem_amount=$value['discount_amount'];
				$voucher_amount = $value['voucher_amount'];
				$status = $value['status'];
				$status_value = $this->ReportModel->getStatus($status);

				$order = $increment_id;

				if ($customer_id == 0) {
					$customer_login_type = 'Not Logged In';
				}

				$order_date = '';
				if ($order_date_data) {
					$order_date = date(SIS_DATE_FM_WT, $order_date_data);
				}

				$SingleRow = array($order, $order_date, $voucher_code, $customer_login_type, $CustomerName, $customer_email, number_format($voucher_amount, 2), $status_value);
				$ExportValuesArr1[] = $SingleRow;
			}
			$filename_csv = 'SIS-Voucher-Report-' . date('d-m-Y') . '.csv';
			// $filename_csv = 'CA-Sales-Order-Report-' . time() . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
				foreach ($ExportValuesArr1 as $readData_one) {
					fputcsv($file_csv, $readData_one);
				}
			}
			fclose($file_csv);
			exit();
		} else {
			// echo 'No records found.';
			$filename_csv = 'SIS-Voucher-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			fclose($file_csv);
			exit();
		}
	} //end download

	public function discountlist()
	{
		/* if(!empty($this->session->userdata('userPermission')) && !in_array('reports',$this->session->userdata('userPermission'))){
            redirect(base_url('dashboard'));  } */

		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('reports', $this->session->userdata('userPermission'))) {
				redirect('dashboard');
			}
		}

		if (isset($_SESSION['LoginID'])) {

			$current_tab = $this->uri->segment(1);
			$shop_id = $this->session->userdata('ShopID');
			$data['PageTitle'] = 'Coupon Overview';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$data['DiscountList'] = $DiscountList = $this->ReportModel->getDiscountList();
			// $data['CatalogueDiscount'] = $getCatalogueDiscountList =$this->ReportModel->getCatalogueDiscountList();
			//$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);
			// print_r($DiscountList);
			$this->load->view('report/discount_main', $data);
		} else {
			redirect(base_url('customer/login'));
		}
	}

	public function getdiscountlist()
	{
		if (isset($_POST)) {
			// print_r($_POST);
			// die();
			$shop_id = $this->session->userdata('ShopID');
			$search_param = array();
			if (!empty($_POST['search'])) {
				$search_param['keyword'] = $_POST['search'];
			}
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$data['DiscountList'] = $DiscountList = $this->ReportModel->getDiscountList($search_param);
			$data['currency_code'] = $this->CommonModel->getShopCurrency($shop_id);
			// $data['CatalogueDiscount'] = $getCatalogueDiscountList =$this->ReportModel->getCatalogueDiscountList();
			// print_r($DiscountList);
			// die();
			$this->load->view('report/discount_list', $data);
		}
	}

	function postGenerateDiscount()
	{
		if (isset($_POST) && $_POST['fromDate'] != '' && $_POST['toDate'] != '') {
			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$fromDate = date(strtotime($fromDate_get));
			$toDate = date(strtotime($toDate_get));
			$report_data_get = $this->ReportModel->getDiscountCSVImport($fromDate, $toDate);
			// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();
			if (isset($report_data_get) && count($report_data_get) > 0) {
				$this->downloadDiscountCsv($fromDate_get, $toDate_get);
			} else {
				$this->downloadDiscountCsv($fromDate_get, $toDate_get);
				// $data['fromDate']=$fromDate_get;
				// $data['toDate']=$toDate_get;
				// $data['PageTitle']='Reports';
				// $data['side_menu']='reports';
				// $data['current_tab']=(isset($current_tab) && $current_tab!='')?$current_tab:'';
				// $data['errorMsg']='No records found.';
				// $FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
				// $data['shop_flag']=$FbcUser->shop_flag;
				// $this->load->view('report/discount_list',$data);
			}
		} else {
			$data['fromDate'] = $_POST['fromDate'];
			$data['toDate'] = $_POST['toDate'];
			$data['PageTitle'] = 'Reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			// $data['errorMsg']='No records found.';
			$data['errorMsg'] = 'Please select date.';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $this->session->userdata('ShopID')), '');
			$data['shop_flag'] = $FbcUser->shop_flag;
			$this->load->view('report/discount_list', $data);
			/*echo json_encode(array('flag' => 0, 'msg' => "Please select date"));
			exit;*/
		}
	}

	public function downloadDiscountCsv($fromDate, $toDate)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array("Order #", "Redeem Date", "Coupon Code", "Customer Type", "Customer Name", "Email", "Discount Redeem", "Status");

		$reports_data = $this->ReportModel->getDiscountCSVImport($fromDate, $toDate);
		//echo count($sales_data);exit();
		// invoice_detail table
		if (isset($reports_data) && count($reports_data) > 0) {

			$ExportValuesArr1 = array();
			foreach ($reports_data as $key => $value) {
				$customer_id = $value['customer_id'];
				$increment_id = $value['increment_id'];
				$order_date_data = $value['created_at'];
				$coupon_code = $value['coupon_code'];
				$customer_login_type = $value['customer_login_type'];
				$CustomerName = $value['customer_firstname'] . ' ' . $value['customer_lastname'];
				$customer_email = $value['customer_email'];
				// $redeem_amount=$value['discount_amount'];
				$discount_amount = $value['discount_amount'];
				$status = $value['status'];
				$status_value = $this->ReportModel->getStatus($status);

				$order = $increment_id;

				if ($customer_id == 0) {
					$customer_login_type = 'Not Logged In';
				}

				$order_date = '';
				if ($order_date_data) {
					$order_date = date(SIS_DATE_FM_WT, $order_date_data);
				}
				// $CustomerName=trim($order_address->first_name).' '.trim($order_address->last_name);

				$SingleRow = array($order, $order_date, $coupon_code, $customer_login_type, $CustomerName, $customer_email, number_format($discount_amount, 2), $status_value);
				$ExportValuesArr1[] = $SingleRow;
			}
			$filename_csv = 'SIS-Coupon-Report-' . date('d-m-Y') . '.csv';
			// $filename_csv = 'CA-Sales-Order-Report-' . time() . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
				foreach ($ExportValuesArr1 as $readData_one) {
					fputcsv($file_csv, $readData_one);
				}
			}
			fclose($file_csv);
			exit();
		} else {
			// echo 'No records found.';
			// $ExportValuesArr1=array();
			// foreach ($reports_data as $key => $value) {
			// 	$increment_id=$value['increment_id'];
			// 	$order_date_data=$value['created_at'];
			// 	$coupon_code=$value['coupon_code'];
			// 	$CustomerName=$value['customer_firstname'].' '.$value['customer_lastname'];
			// 	$customer_email=$value['customer_email'];
			// 	// $redeem_amount=$value['discount_amount'];
			// 	$discount_amount=$value['discount_amount'];
			// 	$order=$increment_id;
			// 	$order_date='';
			// 	if($order_date_data){
			// 		$order_date=date(SIS_DATE_FM_WT,$order_date_data);
			// 	}
			// 	// $CustomerName=trim($order_address->first_name).' '.trim($order_address->last_name);

			// 	$SingleRow=array($order,$order_date,$coupon_code,$CustomerName,$customer_email,number_format($discount_amount,2));
			// 	$ExportValuesArr1[]=$SingleRow;
			// }
			$filename_csv = 'SIS-Coupon-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			// if(isset($ExportValuesArr1) && count($ExportValuesArr1)>0){
			// 	foreach ($ExportValuesArr1 as $readData_one) {
			// 		fputcsv($file_csv, $readData_one);
			// 	}
			// }
			fclose($file_csv);
			exit();
		}
	} //end download postGenerateSalesOverview

	public function saleslist()
	{
		/* if(!empty($this->session->userdata('userPermission')) && !in_array('reports',$this->session->userdata('userPermission'))){
            redirect(base_url('dashboard'));  } */

		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('reports', $this->session->userdata('userPermission'))) {
				redirect('dashboard');
			}
		}

		if (isset($_SESSION['LoginID'])) {

			$current_tab = $this->uri->segment(1);
			$shop_id = $this->session->userdata('ShopID');
			$data['PageTitle'] = 'Sales Overview';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			//$data['webshop_namelist'] = $this->ReportModel->b2b_webshop_name_list();
			$this->load->view('report/sales_main', $data);
		} else {
			redirect(base_url('customer/login'));
		}
	}

	function postGenerateSalesOverview()
	{
		if (isset($_POST) && $_POST['fromDate'] != '' && $_POST['toDate'] != '') {
			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$order_status = $_POST['order_status'];
			$this->downloadSalesOverviewCsv($fromDate_get, $toDate_get, $order_status);
		} else {
			$data['fromDate'] = $_POST['fromDate'];
			$data['toDate'] = $_POST['toDate'];
			$data['PageTitle'] = 'Reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$data['errorMsg'] = 'Please select date.';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $this->session->userdata('ShopID')), '');
			$data['shop_flag'] = $FbcUser->shop_flag;
			$this->load->view('report/sales_main', $data);
		}
	}

	public function downloadSalesOverviewCsv($fromDate, $toDate, $order_status = '')
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array(
			"Status", "Order #", "Order Date", "Order Completion Date", "Checkout Method", "Payment Mode", "Customer Type", "Customer Name", "Email",
			"Billing Name",
			"Billing Mobile",
			"Billing Addressline 1",
			"Billing Addressline 2",
			"Billing City",
			"Billing State",
			"Billing Country",
			"Billing Pincode",
			"Shipping Name",
			"Shipping Mobile",
			"Shipping Addressline 1",
			"Shipping Addressline 2",
			"Shipping City",
			"Shipping State",
			"Shipping Country",
			"Shipping Pincode",
			// "Email",

			"Total Quantity Ordered", "Base Subtotal", "Discount Redeem", "Discount Code", "Tax Charges", "Shipping Charges", "Subtotal", "Voucher Redeem", "Voucher Code", "Payment Charges", "Grand Total"
		);

		$reports_data = $this->ReportModel->getSalesOverviewCSVImport($fromDate, $toDate, $order_status);
		$order_ids = array_column($reports_data, 'order_id');
		// invoice_detail table
		if (isset($reports_data) && count($reports_data) > 0) {
			$addresses = $this->ReportModel->getAddressesForMultipleOrders($order_ids);
			$ExportValuesArr1 = array();
			foreach ($reports_data as $key => $value) {
				$customer_id = $value['customer_id'];
				$order_id = $value['order_id'];
				$increment_id = $value['increment_id'];
				$order = $increment_id;
				$order_date_data = $value['created_at'];
				$order_completion_date = $value['tracking_complete_date'];
				$checkout_method = $value['checkout_method'];
				$order_payment_method = $value['order_payment_method'];
				$customer_login_type = $value['customer_login_type'];
				$CustomerName = $value['customer_firstname'] . ' ' . $value['customer_lastname'];
				$customer_email = $value['customer_email'];

				$AddressByType1 = $addresses[$order_id][1];
				$billingValues = array();

				$billingValues['billingName'] = $AddressByType1->first_name . ' ' . $AddressByType1->last_name;
				$billingValues['billing_mobile_no'] = $AddressByType1->mobile_no;
				$billingValues['address_line1'] = $AddressByType1->address_line1;
				$billingValues['address_line2'] = $AddressByType1->address_line2;
				$billingValues['city'] = $AddressByType1->city;
				$billingValues['state'] = $AddressByType1->state;
				$billingValues['country'] = $AddressByType1->country;
				$billingValues['pincode'] = $AddressByType1->pincode;

				// echo '<pre>'; print_r($billingValues);

				$AddressByType2 = $addresses[$order_id][2];
				$shippingValues = array();
				$shippingValues['shippingName'] = $AddressByType2->first_name . ' ' . $AddressByType2->last_name;
				$shippingValues['mobile_no'] = $AddressByType2->mobile_no;
				$shippingValues['address_line1'] = $AddressByType2->address_line1;
				$shippingValues['address_line2'] = $AddressByType2->address_line2;
				$shippingValues['city'] = $AddressByType2->city;
				$shippingValues['state'] = $AddressByType2->state;
				$shippingValues['country'] = $AddressByType2->country;
				$shippingValues['pincode'] = $AddressByType2->pincode;


				$total_qty_ordered = $value['total_qty_ordered'];
				$base_subtotal = $value['base_subtotal'];
				$discount_amount = $value['discount_amount'];
				$coupon_code = $value['coupon_code'];
				$tax_amount = $value['tax_amount'];
				$shipping_amount = $value['shipping_amount'];
				$subtotal = $value['subtotal'];
				$voucher_amount = $value['voucher_amount'];
				$voucher_code = $value['voucher_code'];
				$payment_final_charge = $value['payment_final_charge'];
				$grand_total = $value['grand_total'];
				$status_num = $value['status'];

				if ($customer_id == 0) {
					$customer_login_type = 'Not Logged In';
				}

				$order_date = '';
				$orderCompletionDate = '';
				if ($order_date_data) {
					$order_date = date(SIS_DATE_FM_WT, $order_date_data);
				}

				if ($order_completion_date) {
					$orderCompletionDate = date(SIS_DATE_FM_WT, $order_completion_date);
				}
				if (empty($order_payment_method) || $order_payment_method == '') {
					$order_payment_method = 'voucher';
				}

				if ($status_num == 1) {
					$status = 'Processing';
				} elseif ($status_num == 2) {
					$status = 'Complete';
				} elseif ($status_num == 3) {
					$status = 'Cancelled';
				} elseif ($status_num == 4) {
					$status = 'Tracking Missing';
				} elseif ($status_num == 5) {
					$status = 'Tracking  Incomplete';
				} elseif ($status_num == 6) {
					$status = 'Tracking Complete';
				} elseif ($status_num == 7) {
					$status = 'Pending';
				} else {
					$status = 'To be processed';
				}

				$SingleRow = array(
					$status, $order, $order_date, $orderCompletionDate, $checkout_method, $order_payment_method, $customer_login_type, $CustomerName, $customer_email,
					$billingValues['billingName'],
					$billingValues['billing_mobile_no'],
					$billingValues['address_line1'],
					$billingValues['address_line2'],
					$billingValues['city'],
					$billingValues['state'],
					$billingValues['country'],
					$billingValues['pincode'],
					$shippingValues['shippingName'],
					$shippingValues['mobile_no'],
					$shippingValues['address_line1'],
					$shippingValues['address_line2'],
					$shippingValues['city'],
					$shippingValues['state'],
					$shippingValues['country'],
					$shippingValues['pincode'],
					$total_qty_ordered,
					number_format($base_subtotal, 2),
					number_format($discount_amount, 2),
					$coupon_code,
					number_format($tax_amount, 2),
					number_format($shipping_amount, 2),
					number_format($subtotal, 2),
					number_format($voucher_amount, 2),
					$voucher_code,
					$payment_final_charge,
					number_format($grand_total, 2),
				);
				$ExportValuesArr1[] = $SingleRow;
			}

			// echo '<pre>'; print_r($ExportValuesArr1); echo '</pre>';  die();
			$filename_csv = 'SIS-Webshop-Orders-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
				foreach ($ExportValuesArr1 as $readData_one) {
					fputcsv($file_csv, $readData_one);
				}
			}
			fclose($file_csv);
			exit();
		} else {
			$filename_csv = 'SIS-Webshop-Orders-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			fclose($file_csv);
			exit();
		}
	} //end download downloadSalesOverviewCsv

	public function b2webshopOrderReport()
	{
		if (isset($_POST) && $_POST['fromDate'] != '' && $_POST['toDate'] != '' && $_POST['webshop_name'] != '') {
			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$webshop_id = $_POST['webshop_name'];
			$fromDate = date(strtotime($fromDate_get));
			$toDate = date(strtotime($toDate_get));
			$b2webshopOrder = $this->ReportModel->getb2webshopOrderCSVImport($fromDate, $toDate, $webshop_id);
			// echo "<pre>"; print_r($b2webshopOrder ); echo "</pre>"; // die();
			if (isset($b2webshopOrder) && count($b2webshopOrder) > 0) {
				$this->downloadB2WebshopOrderCsv($fromDate_get, $toDate_get, $webshop_id);
			} else {
				$this->downloadB2WebshopOrderCsv($fromDate_get, $toDate_get, $webshop_id);
			}
		} else {
			$data['fromDate'] = $_POST['fromDate'];
			$data['toDate'] = $_POST['toDate'];
			$data['PageTitle'] = 'Reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$data['errorMsg'] = 'Please select date.';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $this->session->userdata('ShopID')), '');
			$data['shop_flag'] = $FbcUser->shop_flag;
			$this->load->view('report/sales_main', $data);
		}
	}

	public function downloadB2WebshopOrderCsv($fromDate, $toDate, $webshop_name)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array(
			"Status", "Order #", "Order Date", "Order Completion Date", "Webshop Name",
			"Shipment", "Customer Name",
			// "Billing Name",
			// "Billing Mobile",
			"Billing Addressline 1",
			"Billing Addressline 2",
			"Billing City",
			"Billing State",
			"Billing Country",
			"Billing Pincode",
			// "Shipping Name",
			// "Shipping Mobile",
			"Shipping Addressline 1",
			"Shipping Addressline 2",
			"Shipping City",
			"Shipping State",
			"Shipping Country",
			"Shipping Pincode",
			// "Email",

			"Total Quantity Ordered", "Base Subtotal", "Discount Redeem", "Discount Percentage", "Tax Charges",
			"Subtotal", "Grand Total"
		);

		$b2webshopOrder = $this->ReportModel->getb2webshopOrderCSVImport($fromDate, $toDate, $webshop_name);
		// echo count($b2webshopOrder);exit();
		// invoice_detail table
		if (isset($b2webshopOrder) && count($b2webshopOrder) > 0) {

			$ExportValuesArr1 = array();
			foreach ($b2webshopOrder as $key => $value) {
				$increment_id = $value['increment_id'];
				$order = $increment_id;
				$order_date_data = $value['created_at'];
				$order_completion_date = $value['tracking_complete_date'];
				$org_shop_name = $value['org_shop_name'];
				$shipment_type = $value['shipment_type'];
				$CustomerName = $value['customer_firstname'] . ' ' . $value['customer_lastname'];

				if ($shipment_type == 1) {
					$billingValues['address_line1'] = $value['bill_address_line1'];
					$billingValues['address_line2'] = $value['bill_address_line2'];
					$billingValues['city'] = $value['bill_city'];
					$billingValues['state'] = $value['bill_state'];
					$billingValues['country'] = $value['bill_country'];
					$billingValues['pincode'] = $value['bill_pincode'];

					$shippingValues['address_line1'] = $value['ship_address_line1'];
					$shippingValues['address_line2'] = $value['ship_address_line2'];
					$shippingValues['city'] = $value['ship_city'];
					$shippingValues['state'] = $value['ship_state'];
					$shippingValues['country'] = $value['ship_country'];
					$shippingValues['pincode'] = $value['ship_pincode'];
				} else {
					$billingValues['address_line1'] = '';
					$billingValues['address_line2'] = '';
					$billingValues['city'] = '';
					$billingValues['state'] = '';
					$billingValues['country'] = '';
					$billingValues['pincode'] = '';

					$shippingValues['address_line1'] = $value['line1'];
					$shippingValues['address_line2'] = $value['line2'];
					$shippingValues['city'] = $value['soa_city'];
					$shippingValues['state'] = $value['soa_state'];
					$shippingValues['country'] = $value['soa_country'];
					$shippingValues['pincode'] = $value['soa_pincode'];
				}

				$total_qty_ordered = $value['total_qty_ordered'];
				$base_subtotal = $value['base_subtotal'];
				$discount_amount = $value['discount_amount'];
				$discount_percent = $value['discount_percent'];
				$tax_amount = $value['tax_amount'];
				$subtotal = $value['subtotal'];
				$grand_total = $value['grand_total'];
				$status_num = $value['status'];

				$order_date = '';
				$orderCompletionDate = '';
				$shipment = '';
				if ($order_date_data) {
					$order_date = date(SIS_DATE_FM_WT, $order_date_data);
				}

				if ($order_completion_date) {
					$orderCompletionDate = date(SIS_DATE_FM_WT, $order_completion_date);
				}

				if ($shipment_type == 1) {
					$shipment = 'Buy In';
				} elseif ($shipment_type == 2) {
					$shipment = 'Dropship';
				}

				if ($status_num == 1) {
					$status = 'Processing';
				} elseif ($status_num == 2) {
					$status = 'Complete';
				} elseif ($status_num == 3) {
					$status = 'Cancelled';
				} elseif ($status_num == 4) {
					$status = 'Tracking Missing';
				} elseif ($status_num == 5) {
					$status = 'Tracking  Incomplete';
				} elseif ($status_num == 6) {
					$status = 'Tracking Complete';
				} elseif ($status_num == 7) {
					$status = 'Pending';
				} else {
					$status = 'To be processed';
				}

				$SingleRow = array(
					$status, $order, $order_date, $orderCompletionDate,
					$org_shop_name, $shipment,
					$CustomerName,
					$billingValues['address_line1'],
					$billingValues['address_line2'],
					$billingValues['city'],
					$billingValues['state'],
					$billingValues['country'],
					$billingValues['pincode'],
					$shippingValues['address_line1'],
					$shippingValues['address_line2'],
					$shippingValues['city'],
					$shippingValues['state'],
					$shippingValues['country'],
					$shippingValues['pincode'],
					$total_qty_ordered,
					number_format($base_subtotal, 2),
					number_format($discount_amount, 2),
					$discount_percent,
					number_format($tax_amount, 2),
					number_format($subtotal, 2),
					number_format($grand_total, 2),
				);
				$ExportValuesArr1[] = $SingleRow;
			}

			// echo '<pre>'; print_r($ExportValuesArr1); echo '</pre>';  die();
			$filename_csv = 'SIS-B2Webshop-Orders-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
				foreach ($ExportValuesArr1 as $readData_one) {
					fputcsv($file_csv, $readData_one);
				}
			}
			fclose($file_csv);
			exit();
		} else {
			$filename_csv = 'SIS-B2Webshop-Orders-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			fclose($file_csv);
			exit();
		}
	} //end download downloadSalesOverviewCsv

	public function returnlist()
	{

		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('reports', $this->session->userdata('userPermission'))) {
				redirect('dashboard');
			}
		}

		if (isset($_SESSION['LoginID'])) {
			/* if(!empty($this->session->userdata('userPermission')) && !in_array('reports',$this->session->userdata('userPermission'))){
            redirect(base_url('dashboard'));  } */
			$current_tab = $this->uri->segment(1);
			$shop_id = $this->session->userdata('ShopID');
			$data['PageTitle'] = 'Return and Refund reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$this->load->view('report/returns_main', $data);
		} else {
			redirect(base_url('customer/login'));
		}
	}

	function postGenerateReturnReport()
	{
		if (isset($_POST) && $_POST['fromDate'] != '' && $_POST['toDate'] != '') {
			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$fromDate = date(strtotime($fromDate_get));
			$toDate = date(strtotime($toDate_get));
			$report_data_get = $this->ReportModel->getReturnRefundReportCSVImport($fromDate, $toDate);
			// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();
			if (isset($report_data_get) && count($report_data_get) > 0) {
				$this->downloadReturnRefundReportCsv($fromDate_get, $toDate_get);
			} else {
				$this->downloadReturnRefundReportCsv($fromDate_get, $toDate_get);
			}
		} else {
			$data['fromDate'] = $_POST['fromDate'];
			$data['toDate'] = $_POST['toDate'];
			$data['PageTitle'] = 'Return and Refund reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$data['errorMsg'] = 'Please select date.';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $this->session->userdata('ShopID')), '');
			$data['shop_flag'] = $FbcUser->shop_flag;
			$this->load->view('report/returns_main', $data);
		}
	}

	public function downloadReturnRefundReportCsv($fromDate, $toDate)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array(
			"Order No", "Return Order No", "Purchased on", "Customer Type", "Customer Name", "Return Status", "Refund Status", "Customer Return Reason", "Return Request Date", "Return Request Due Date", "Return Received Date", "Order Payment Mode", "Return Order Total", "Total Refund Approved", "Refund Mode", "Store Credit Coupon Code", "Bank Name", "Branch Name", "Account Number", "IFSC/IBAN", "BIC/SWIFT", "Account Holder Name"
		);

		$reports_data = $this->ReportModel->getReturnRefundReportCSVImport($fromDate, $toDate);
		// echo count($reports_data);exit();
		// invoice_detail table
		if (isset($reports_data) && count($reports_data) > 0) {

			$ExportValuesArr1 = array();
			foreach ($reports_data as $key => $value) {
				$customer_id = $value['customer_id'];
				$increment_id = $value['increment_id'];
				$order = $increment_id;
				$return_order_increment_id = $value['return_order_increment_id'];
				$purchased_on = $value['purchased_on'];
				$customer_login_type = $value['customer_login_type'];
				$CustomerName = $value['customer_firstname'] . ' ' . $value['customer_lastname'];
				$status_num = $value['status'];
				$refund_status = $value['refund_status'];
				$reason_for_return = $value['reason_for_return'];
				$return_request_date = $value['created_at'];
				$return_request_due_date = $value['return_request_due_date'];
				$return_recieved_date = $value['return_recieved_date'];
				$payment_method_name = $value['payment_method_name'];
				$order_grandtotal = $value['order_grandtotal'];
				$order_grandtotal_approved = $value['order_grandtotal_approved'];
				$refund_payment_mode = $value['refund_payment_mode'];
				$refund_coupon_code = $value['refund_coupon_code'];
				$bank_name = $value['bank_name'];
				$bank_branch = $value['bank_branch'];
				$bank_acc_no = $value['bank_acc_no'];
				$ifsc_iban = $value['ifsc_iban'];
				$bic_swift = $value['bic_swift'];
				$acc_holder_name = $value['acc_holder_name'];

				if ($customer_id == 0) {
					$customer_login_type = 'Not Logged In';
				}

				$purchased_on_date = '';
				$returnRequestDate = '';
				$returnRequestDueDate = '';
				$returnRecievedDate = '';

				if ($purchased_on) {
					$purchased_on_date = date(SIS_DATE_FM_WT, $purchased_on);
				}

				if ($return_request_date) {
					$returnRequestDate = date(SIS_DATE_FM_WT, $return_request_date);
				}

				if ($return_request_due_date) {
					$returnRequestDueDate = date(SIS_DATE_FM_WT, $return_request_due_date);
				}

				if ($return_recieved_date) {
					$returnRecievedDate = date(SIS_DATE_FM_WT, $return_recieved_date);
				}

				if ($status_num == 2) {
					$status = 'confirmreturn(admin-pending)';
				} elseif ($status_num == 3) {
					$status = 'approved';
				} elseif ($status_num == 4) {
					$status = 'partiallyapproved';
				} elseif ($status_num == 5) {
					$status = 'rejected';
				}

				if ($refund_status == 1) {
					$refundStatus = 'Completed';
				} else if ($refund_status == 2) {
					$refundStatus = 'Rejected';
				} else {
					$refundStatus = 'Pending';
				}

				if ($refund_payment_mode == 1) {
					$refundPaymentMode = 'Store Credits';
				} else if ($refund_payment_mode == 2) {
					$refundPaymentMode = 'Bank Transfer';
				} else if ($refund_payment_mode == 3 || $refund_payment_mode == 4) {
					$refundPaymentMode = $payment_method_name;
				} else {
					$refundPaymentMode = 'notselected';
				}

				$SingleRow = array(
					$order, $return_order_increment_id, $purchased_on_date, $customer_login_type, $CustomerName,
					$status,
					$refundStatus,
					$reason_for_return,
					$returnRequestDate,
					$returnRequestDueDate,
					$returnRecievedDate,
					$payment_method_name,
					number_format($order_grandtotal, 2),
					number_format($order_grandtotal_approved, 2),
					$refundPaymentMode,
					$refund_coupon_code,
					$bank_name,
					$bank_branch,
					$bank_acc_no,
					$ifsc_iban,
					$bic_swift,
					$acc_holder_name,

				);
				$ExportValuesArr1[] = $SingleRow;
			}

			// echo '<pre>'; print_r($ExportValuesArr1); echo '</pre>';  die();
			$filename_csv = 'SIS-Return-Refund-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
				foreach ($ExportValuesArr1 as $readData_one) {
					fputcsv($file_csv, $readData_one);
				}
			}
			fclose($file_csv);
			exit();
		} else {
			$filename_csv = 'SIS-Return-Refund-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			fclose($file_csv);
			exit();
		}
	} //end download

	function postEscalationsReport()
	{
		if (isset($_POST) && $_POST['fromDate'] != '' && $_POST['toDate'] != '') {
			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$fromDate = date(strtotime($fromDate_get));
			$toDate = date(strtotime($toDate_get));
			$report_data_get = $this->ReportModel->getEscalationsReportCSVImport($fromDate, $toDate);
			// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();
			if (isset($report_data_get) && count($report_data_get) > 0) {
				$this->downloadEscalationsReportCsv($fromDate_get, $toDate_get);
			} else {
				$this->downloadEscalationsReportCsv($fromDate_get, $toDate_get);
			}
		} else {
			$data['fromDate'] = $_POST['fromDate'];
			$data['toDate'] = $_POST['toDate'];
			$data['PageTitle'] = 'Return and Refund reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$data['errorMsg'] = 'Please select date.';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $this->session->userdata('ShopID')), '');
			$data['shop_flag'] = $FbcUser->shop_flag;
			$this->load->view('report/returns_main', $data);
		}
	}

	public function downloadEscalationsReportCsv($fromDate, $toDate)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array(
			"Order No", "Escalation Order No", "Purchased on", "Customer Type", "Customer Name", "Refund Status", "Escalations Reason", "Requested On", "Order Payment Mode", "Order Grand Total", "Total Refund Approved", "Refund Mode", "Cheque Already Recived", "Store Credit Coupon"
		);

		$reports_data = $this->ReportModel->getEscalationsReportCSVImport($fromDate, $toDate);
		// echo count($reports_data);exit();
		// invoice_detail table
		if (isset($reports_data) && count($reports_data) > 0) {

			$ExportValuesArr1 = array();
			foreach ($reports_data as $key => $value) {
				$customer_id = $value['customer_id'];
				$increment_id = $value['increment_id'];
				$order = $increment_id;
				$esc_order_id = $value['esc_order_id'];
				$purchased_on = $value['purchased_on'];
				$customer_login_type = $value['customer_login_type'];
				$CustomerName = $value['customer_firstname'] . ' ' . $value['customer_lastname'];
				// $status_num=$value['refund_status'];
				$refund_status = $value['refund_status'];
				$Escalations_reason = $value['cancel_reason'];
				$request_date = $value['created_at'];
				$payment_method_name = $value['payment_method_name'];
				$order_grandtotal = $value['grand_total'];
				$order_grandtotal_approved = $value['order_grandtotal_approved'];
				$refund_payment_mode = $value['cancel_refund_type'];
				$cheque_already_recived = $value['cheque_already_recived'];
				$refund_coupon_code = $value['refund_coupon_code'];

				if ($customer_id == 0) {
					$customer_login_type = 'Not Logged In';
				}

				$purchased_on_date = '';
				$RequestDate = '';

				if ($purchased_on) {
					$purchased_on_date = date(SIS_DATE_FM_WT, $purchased_on);
				}

				if ($request_date) {
					$RequestDate = date(SIS_DATE_FM_WT, $request_date);
				}

				if ($refund_status == 1) {
					$refundStatus = 'Completed';
				} else if ($refund_status == 2) {
					$refundStatus = 'Rejected';
				} else {
					$refundStatus = 'Pending';
				}

				if ($refund_payment_mode == 1) {
					$refundPaymentMode = 'Store Credits';
				} else if ($refund_payment_mode == 2) {
					$refundPaymentMode = 'offline credit';
				} else {
					$refundPaymentMode = 'none';
				}

				if ($cheque_already_recived == 1) {
					$chequeAlreadyRecived = 'Yes';
				} else if ($cheque_already_recived == 2) {
					$chequeAlreadyRecived = 'No';
				} else {
					$chequeAlreadyRecived = '';
				}

				$SingleRow = array(
					$order, $esc_order_id, $purchased_on_date,
					$customer_login_type,
					$CustomerName,
					// $status,
					$refundStatus,
					$Escalations_reason,
					$RequestDate,
					$payment_method_name,
					number_format($order_grandtotal, 2),
					number_format($order_grandtotal_approved, 2),
					$refundPaymentMode,
					$chequeAlreadyRecived,
					$refund_coupon_code,


				);
				$ExportValuesArr1[] = $SingleRow;
			}

			// echo '<pre>'; print_r($ExportValuesArr1); echo '</pre>';  die();
			$filename_csv = 'SIS-Escalations-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
				foreach ($ExportValuesArr1 as $readData_one) {
					fputcsv($file_csv, $readData_one);
				}
			}
			fclose($file_csv);
			exit();
		} else {
			$filename_csv = 'SIS-Escalations-Report-' . date('d-m-Y') . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $report_export_header);
			fclose($file_csv);
			exit();
		}
	} //end download

	function webshop_chart_Report()
	{
		/* if(!empty($this->session->userdata('userPermission')) && !in_array('sales_analytics/webshop_reports',$this->session->userdata('userPermission'))){
            redirect(base_url('dashboard'));  } */
		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('sales_analytics/webshop_reports', $this->session->userdata('userPermission'))) {
				redirect('dashboard');
			}
		}
		$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		//$data['user_shop_details'] = $this->CommonModel->getShopData($data['user_details']->fbc_user_id,$data['user_details']->shop_id);
		// print_r($data['user_shop_details']->currency_symbol);
		// die();
		$data['PageTitle'] = 'Webshop Reports';

		$data['side_menu'] = 'webshopreports';
		// Get oldest year
		$data_get = $this->ReportModel->get_oldest_date();
		$data['data_year'] = date('Y', $data_get->date);

		// Get best selling products
		$data['best_selling_products'] = $this->ReportModel->get_best_selling_products();


		// print_r(date('Y',$data_get->date));die();

		// Get sells by year/month
		// $jan_start=date("Y").'-01-01';$jan_end=date("Y").'-12-31';
		// $jan_start='2021-01-01';$jan_end='2021-12-31';
		$jan_start = date("Y") . '-01-01';
		$jan_end = date("Y") . '-01-31';
		$feb_start = date("Y") . '-02-01';
		$feb_end = date("Y") . '-02-31';
		$mar_start = date("Y") . '-03-01';
		$mar_end = date("Y") . '-03-31';
		$apr_start = date("Y") . '-04-01';
		$apr_end = date("Y") . '-04-31';
		$may_start = date("Y") . '-05-01';
		$may_end = date("Y") . '-05-31';
		$jun_start = date("Y") . '-06-01';
		$jun_end = date("Y") . '-06-31';
		$jul_start = date("Y") . '-07-01';
		$jul_end = date("Y") . '-07-31';
		$aug_start = date("Y") . '-08-01';
		$aug_end = date("Y") . '-08-31';
		$sep_start = date("Y") . '-09-01';
		$sep_end = date("Y") . '-09-31';
		$oct_start = date("Y") . '-10-01';
		$oct_end = date("Y") . '-10-31';
		$nov_start = date("Y") . '-11-01';
		$nov_end = date("Y") . '-11-31';
		$dec_start = date("Y") . '-12-01';
		$dec_end = date("Y") . '-12-31';

		$jan_data_get = $this->ReportModel->getSalesOverview_chart($jan_start, $jan_end);
		$feb_data_get = $this->ReportModel->getSalesOverview_chart($feb_start, $feb_end);
		$mar_data_get = $this->ReportModel->getSalesOverview_chart($mar_start, $mar_end);
		$apr_data_get = $this->ReportModel->getSalesOverview_chart($apr_start, $apr_end);
		$may_data_get = $this->ReportModel->getSalesOverview_chart($may_start, $may_end);
		$jun_data_get = $this->ReportModel->getSalesOverview_chart($jun_start, $jun_end);
		$jul_data_get = $this->ReportModel->getSalesOverview_chart($jul_start, $jul_end);
		$aug_data_get = $this->ReportModel->getSalesOverview_chart($aug_start, $aug_end);
		$sep_data_get = $this->ReportModel->getSalesOverview_chart($sep_start, $sep_end);
		$oct_data_get = $this->ReportModel->getSalesOverview_chart($oct_start, $oct_end);
		$nov_data_get = $this->ReportModel->getSalesOverview_chart($nov_start, $nov_end);
		$dec_data_get = $this->ReportModel->getSalesOverview_chart($dec_start, $dec_end);

		// Get revenue by year/month
		$jan_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($jan_start, $jan_end);
		$feb_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($feb_start, $feb_end);
		$mar_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($mar_start, $mar_end);
		$apr_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($apr_start, $apr_end);
		$may_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($may_start, $may_end);
		$jun_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($jun_start, $jun_end);
		$jul_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($jul_start, $jul_end);
		$aug_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($aug_start, $aug_end);
		$sep_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($sep_start, $sep_end);
		$oct_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($oct_start, $oct_end);
		$nov_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($nov_start, $nov_end);
		$dec_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($dec_start, $dec_end);

		// echo $jan_data_get[0]['count'];die();
		// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();

		// sales data by month/days
		$year = date('Y');
		$month = date('m');

		// $year='2021';
		// $month='08';

		$end_date = date("t", strtotime($year . '-' . $month));

		for ($i = 1; $i <= $end_date; $i++) {
			$month_start_date = $year . '-' . $month . '-' . $i;
			$k = $i + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data_by_day = $this->ReportModel->getSalesOverview_chart($month_start_date, $month_end_date);
			$got_days[] = $year . '-' . $month . '-' . $i;
			$got_data[] = $data_by_day[0]['count'];
		}
		// sales revenue data by month/days
		for ($i = 1; $i <= $end_date; $i++) {
			$month_start_date = $year . '-' . $month . '-' . $i;
			$k = $i + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data_by_day_rev = $this->ReportModel->getSalesOverview_chart_rev($month_start_date, $month_end_date);
			$got_days_rev[] = $year . '-' . $month . '-' . $i;
			if ($data_by_day_rev[0]['sum'] == "") {
				$got_data_rev[] = 0;
			} else {
				$got_data_rev[] = $data_by_day_rev[0]['sum'];
			}
		}

		$data['data'] = [$jan_data_get[0]['count'], $feb_data_get[0]['count'], $mar_data_get[0]['count'], $apr_data_get[0]['count'], $may_data_get[0]['count'], $jun_data_get[0]['count'], $jul_data_get[0]['count'], $aug_data_get[0]['count'], $sep_data_get[0]['count'], $oct_data_get[0]['count'], $nov_data_get[0]['count'], $dec_data_get[0]['count']];

		$data['data_rev'] = [$jan_data_get_rev[0]['sum'], $feb_data_get_rev[0]['sum'], $mar_data_get_rev[0]['sum'], $apr_data_get_rev[0]['sum'], $may_data_get_rev[0]['sum'], $jun_data_get_rev[0]['sum'], $jul_data_get_rev[0]['sum'], $aug_data_get_rev[0]['sum'], $sep_data_get_rev[0]['sum'], $oct_data_get_rev[0]['sum'], $nov_data_get_rev[0]['sum'], $dec_data_get_rev[0]['sum']];

		$data['data_by_day_dates'] = $got_days;
		$data['data_by_day_sales'] = $got_data;
		$data['data_by_day_dates_rev'] = $got_days_rev;
		$data['data_by_day_sales_rev'] = $got_data_rev;

		$this->load->view('webshopreports', $data);
	}

	function webshop_chart_Report_ajax()
	{
		// $jan_start=date("Y").'-01-01';$jan_end=date("Y").'-12-31';
		// $jan_start='2021-01-01';$jan_end='2021-12-31';
		$year = $this->input->post('year');
		// print_r($this->input->post('year'));die();
		$jan_start = $year . '-01-01';
		$jan_end = $year . '-01-31';
		$feb_start = $year . '-02-01';
		$feb_end = $year . '-02-31';
		$mar_start = $year . '-03-01';
		$mar_end = $year . '-03-31';
		$apr_start = $year . '-04-01';
		$apr_end = $year . '-04-31';
		$may_start = $year . '-05-01';
		$may_end = $year . '-05-31';
		$jun_start = $year . '-06-01';
		$jun_end = $year . '-06-31';
		$jul_start = $year . '-07-01';
		$jul_end = $year . '-07-31';
		$aug_start = $year . '-08-01';
		$aug_end = $year . '-08-31';
		$sep_start = $year . '-09-01';
		$sep_end = $year . '-09-31';
		$oct_start = $year . '-10-01';
		$oct_end = $year . '-10-31';
		$nov_start = $year . '-11-01';
		$nov_end = $year . '-11-31';
		$dec_start = $year . '-12-01';
		$dec_end = $year . '-12-31';

		$jan_data_get = $this->ReportModel->getSalesOverview_chart($jan_start, $jan_end);
		$feb_data_get = $this->ReportModel->getSalesOverview_chart($feb_start, $feb_end);
		$mar_data_get = $this->ReportModel->getSalesOverview_chart($mar_start, $mar_end);
		$apr_data_get = $this->ReportModel->getSalesOverview_chart($apr_start, $apr_end);
		$may_data_get = $this->ReportModel->getSalesOverview_chart($may_start, $may_end);
		$jun_data_get = $this->ReportModel->getSalesOverview_chart($jun_start, $jun_end);
		$jul_data_get = $this->ReportModel->getSalesOverview_chart($jul_start, $jul_end);
		$aug_data_get = $this->ReportModel->getSalesOverview_chart($aug_start, $aug_end);
		$sep_data_get = $this->ReportModel->getSalesOverview_chart($sep_start, $sep_end);
		$oct_data_get = $this->ReportModel->getSalesOverview_chart($oct_start, $oct_end);
		$nov_data_get = $this->ReportModel->getSalesOverview_chart($nov_start, $nov_end);
		$dec_data_get = $this->ReportModel->getSalesOverview_chart($dec_start, $dec_end);

		// echo $jan_data_get[0]['count'];die();
		// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();

		$data['data'] = [$jan_data_get[0]['count'], $feb_data_get[0]['count'], $mar_data_get[0]['count'], $apr_data_get[0]['count'], $may_data_get[0]['count'], $jun_data_get[0]['count'], $jul_data_get[0]['count'], $aug_data_get[0]['count'], $sep_data_get[0]['count'], $oct_data_get[0]['count'], $nov_data_get[0]['count'], $dec_data_get[0]['count']];
		// $this->load->view('webshopreports',$data);
		echo json_encode(array('data' => $data['data']));
		exit();
	}

	function webshop_chart_Report_ajax_rev()
	{
		// $jan_start=date("Y").'-01-01';$jan_end=date("Y").'-12-31';
		// $jan_start='2021-01-01';$jan_end='2021-12-31';
		$year = $this->input->post('year');
		// print_r($this->input->post('year'));die();
		$jan_start = $year . '-01-01';
		$jan_end = $year . '-01-31';
		$feb_start = $year . '-02-01';
		$feb_end = $year . '-02-31';
		$mar_start = $year . '-03-01';
		$mar_end = $year . '-03-31';
		$apr_start = $year . '-04-01';
		$apr_end = $year . '-04-31';
		$may_start = $year . '-05-01';
		$may_end = $year . '-05-31';
		$jun_start = $year . '-06-01';
		$jun_end = $year . '-06-31';
		$jul_start = $year . '-07-01';
		$jul_end = $year . '-07-31';
		$aug_start = $year . '-08-01';
		$aug_end = $year . '-08-31';
		$sep_start = $year . '-09-01';
		$sep_end = $year . '-09-31';
		$oct_start = $year . '-10-01';
		$oct_end = $year . '-10-31';
		$nov_start = $year . '-11-01';
		$nov_end = $year . '-11-31';
		$dec_start = $year . '-12-01';
		$dec_end = $year . '-12-31';

		$jan_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($jan_start, $jan_end);
		$feb_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($feb_start, $feb_end);
		$mar_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($mar_start, $mar_end);
		$apr_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($apr_start, $apr_end);
		$may_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($may_start, $may_end);
		$jun_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($jun_start, $jun_end);
		$jul_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($jul_start, $jul_end);
		$aug_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($aug_start, $aug_end);
		$sep_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($sep_start, $sep_end);
		$oct_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($oct_start, $oct_end);
		$nov_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($nov_start, $nov_end);
		$dec_data_get_rev = $this->ReportModel->getSalesOverview_chart_rev($dec_start, $dec_end);

		// echo $jan_data_get[0]['count'];die();
		// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();

		$data['data_rev'] = [$jan_data_get_rev[0]['sum'], $feb_data_get_rev[0]['sum'], $mar_data_get_rev[0]['sum'], $apr_data_get_rev[0]['sum'], $may_data_get_rev[0]['sum'], $jun_data_get_rev[0]['sum'], $jul_data_get_rev[0]['sum'], $aug_data_get_rev[0]['sum'], $sep_data_get_rev[0]['sum'], $oct_data_get_rev[0]['sum'], $nov_data_get_rev[0]['sum'], $dec_data_get_rev[0]['sum']];
		// $this->load->view('webshopreports',$data);
		echo json_encode(array('data_rev' => $data['data_rev']));
		exit();
	}

	function b2webshop_chart_Report()
	{
		if (!empty($this->session->userdata('userPermission')) && !in_array('sales_analytics/b2Webshop_reports', $this->session->userdata('userPermission'))) {
			redirect(base_url('dashboard'));
		}
		$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		$data['user_shop_details'] = $this->CommonModel->getShopData($data['user_details']->fbc_user_id, $data['user_details']->shop_id);
		// print_r($data['user_shop_details']->currency_symbol);
		// die();
		$data['PageTitle'] = 'B2webshop Reports';

		$data['side_menu'] = 'b2webshopreports';
		// Get oldest year
		$data_get = $this->ReportModel->b2b_get_oldest_date();
		$data['data_year'] = date('Y', $data_get->date);
		// print_r($data['data_year']);
		// die();

		// Get best selling products
		$data['best_selling_products'] = $this->ReportModel->b2b_get_best_selling_products();
		// print_r($data['best_selling_products']);
		// die();

		// Get sells by year/month
		// $jan_start=date("Y").'-01-01';$jan_end=date("Y").'-12-31';
		// $jan_start='2021-01-01';$jan_end='2021-12-31';
		$jan_start = date("Y") . '-01-01';
		$jan_end = date("Y") . '-01-31';
		$feb_start = date("Y") . '-02-01';
		$feb_end = date("Y") . '-02-31';
		$mar_start = date("Y") . '-03-01';
		$mar_end = date("Y") . '-03-31';
		$apr_start = date("Y") . '-04-01';
		$apr_end = date("Y") . '-04-31';
		$may_start = date("Y") . '-05-01';
		$may_end = date("Y") . '-05-31';
		$jun_start = date("Y") . '-06-01';
		$jun_end = date("Y") . '-06-31';
		$jul_start = date("Y") . '-07-01';
		$jul_end = date("Y") . '-07-31';
		$aug_start = date("Y") . '-08-01';
		$aug_end = date("Y") . '-08-31';
		$sep_start = date("Y") . '-09-01';
		$sep_end = date("Y") . '-09-31';
		$oct_start = date("Y") . '-10-01';
		$oct_end = date("Y") . '-10-31';
		$nov_start = date("Y") . '-11-01';
		$nov_end = date("Y") . '-11-31';
		$dec_start = date("Y") . '-12-01';
		$dec_end = date("Y") . '-12-31';

		$jan_data_get = $this->ReportModel->b2getSalesOverview_chart($jan_start, $jan_end);
		$feb_data_get = $this->ReportModel->b2getSalesOverview_chart($feb_start, $feb_end);
		$mar_data_get = $this->ReportModel->b2getSalesOverview_chart($mar_start, $mar_end);
		$apr_data_get = $this->ReportModel->b2getSalesOverview_chart($apr_start, $apr_end);
		$may_data_get = $this->ReportModel->b2getSalesOverview_chart($may_start, $may_end);
		$jun_data_get = $this->ReportModel->b2getSalesOverview_chart($jun_start, $jun_end);
		$jul_data_get = $this->ReportModel->b2getSalesOverview_chart($jul_start, $jul_end);
		$aug_data_get = $this->ReportModel->b2getSalesOverview_chart($aug_start, $aug_end);
		$sep_data_get = $this->ReportModel->b2getSalesOverview_chart($sep_start, $sep_end);
		$oct_data_get = $this->ReportModel->b2getSalesOverview_chart($oct_start, $oct_end);
		$nov_data_get = $this->ReportModel->b2getSalesOverview_chart($nov_start, $nov_end);
		$dec_data_get = $this->ReportModel->b2getSalesOverview_chart($dec_start, $dec_end);

		// Get revenue by year/month
		$jan_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($jan_start, $jan_end);
		$feb_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($feb_start, $feb_end);
		$mar_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($mar_start, $mar_end);
		$apr_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($apr_start, $apr_end);
		$may_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($may_start, $may_end);
		$jun_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($jun_start, $jun_end);
		$jul_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($jul_start, $jul_end);
		$aug_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($aug_start, $aug_end);
		$sep_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($sep_start, $sep_end);
		$oct_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($oct_start, $oct_end);
		$nov_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($nov_start, $nov_end);
		$dec_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($dec_start, $dec_end);

		// echo $jan_data_get[0]['count'];die();
		// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();

		// sales data by month/days
		$year = date('Y');
		$month = date('m');

		// $year='2021';
		// $month='08';

		$end_date = date("t", strtotime($year . '-' . $month));

		for ($i = 1; $i <= $end_date; $i++) {
			$month_start_date = $year . '-' . $month . '-' . $i;
			$k = $i + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data_by_day = $this->ReportModel->b2getSalesOverview_chart($month_start_date, $month_end_date);
			$got_days[] = $year . '-' . $month . '-' . $i;
			$got_data[] = $data_by_day[0]['count'];
		}
		// sales revenue data by month/days
		for ($i = 1; $i <= $end_date; $i++) {
			$month_start_date = $year . '-' . $month . '-' . $i;
			$k = $i + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data_by_day_rev = $this->ReportModel->b2getSalesOverview_chart_rev($month_start_date, $month_end_date);
			$got_days_rev[] = $year . '-' . $month . '-' . $i;
			if ($data_by_day_rev[0]['sum'] == "") {
				$got_data_rev[] = 0;
			} else {
				$got_data_rev[] = $data_by_day_rev[0]['sum'];
			}
		}

		$data['data'] = [$jan_data_get[0]['count'], $feb_data_get[0]['count'], $mar_data_get[0]['count'], $apr_data_get[0]['count'], $may_data_get[0]['count'], $jun_data_get[0]['count'], $jul_data_get[0]['count'], $aug_data_get[0]['count'], $sep_data_get[0]['count'], $oct_data_get[0]['count'], $nov_data_get[0]['count'], $dec_data_get[0]['count']];

		$data['data_rev'] = [$jan_data_get_rev[0]['sum'], $feb_data_get_rev[0]['sum'], $mar_data_get_rev[0]['sum'], $apr_data_get_rev[0]['sum'], $may_data_get_rev[0]['sum'], $jun_data_get_rev[0]['sum'], $jul_data_get_rev[0]['sum'], $aug_data_get_rev[0]['sum'], $sep_data_get_rev[0]['sum'], $oct_data_get_rev[0]['sum'], $nov_data_get_rev[0]['sum'], $dec_data_get_rev[0]['sum']];

		$data['data_by_day_dates'] = $got_days;
		$data['data_by_day_sales'] = $got_data;
		$data['data_by_day_dates_rev'] = $got_days_rev;
		$data['data_by_day_sales_rev'] = $got_data_rev;
		$this->load->view('b2webshopreports', $data);
	}

	function b2webshop_chart_Report_ajax()
	{
		// $jan_start=date("Y").'-01-01';$jan_end=date("Y").'-12-31';
		// $jan_start='2021-01-01';$jan_end='2021-12-31';
		$year = $this->input->post('year');
		// print_r($this->input->post('year'));die();
		$jan_start = $year . '-01-01';
		$jan_end = $year . '-01-31';
		$feb_start = $year . '-02-01';
		$feb_end = $year . '-02-31';
		$mar_start = $year . '-03-01';
		$mar_end = $year . '-03-31';
		$apr_start = $year . '-04-01';
		$apr_end = $year . '-04-31';
		$may_start = $year . '-05-01';
		$may_end = $year . '-05-31';
		$jun_start = $year . '-06-01';
		$jun_end = $year . '-06-31';
		$jul_start = $year . '-07-01';
		$jul_end = $year . '-07-31';
		$aug_start = $year . '-08-01';
		$aug_end = $year . '-08-31';
		$sep_start = $year . '-09-01';
		$sep_end = $year . '-09-31';
		$oct_start = $year . '-10-01';
		$oct_end = $year . '-10-31';
		$nov_start = $year . '-11-01';
		$nov_end = $year . '-11-31';
		$dec_start = $year . '-12-01';
		$dec_end = $year . '-12-31';

		$jan_data_get = $this->ReportModel->b2getSalesOverview_chart($jan_start, $jan_end);
		$feb_data_get = $this->ReportModel->b2getSalesOverview_chart($feb_start, $feb_end);
		$mar_data_get = $this->ReportModel->b2getSalesOverview_chart($mar_start, $mar_end);
		$apr_data_get = $this->ReportModel->b2getSalesOverview_chart($apr_start, $apr_end);
		$may_data_get = $this->ReportModel->b2getSalesOverview_chart($may_start, $may_end);
		$jun_data_get = $this->ReportModel->b2getSalesOverview_chart($jun_start, $jun_end);
		$jul_data_get = $this->ReportModel->b2getSalesOverview_chart($jul_start, $jul_end);
		$aug_data_get = $this->ReportModel->b2getSalesOverview_chart($aug_start, $aug_end);
		$sep_data_get = $this->ReportModel->b2getSalesOverview_chart($sep_start, $sep_end);
		$oct_data_get = $this->ReportModel->b2getSalesOverview_chart($oct_start, $oct_end);
		$nov_data_get = $this->ReportModel->b2getSalesOverview_chart($nov_start, $nov_end);
		$dec_data_get = $this->ReportModel->b2getSalesOverview_chart($dec_start, $dec_end);

		// echo $jan_data_get[0]['count'];die();
		// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();

		$data['data'] = [$jan_data_get[0]['count'], $feb_data_get[0]['count'], $mar_data_get[0]['count'], $apr_data_get[0]['count'], $may_data_get[0]['count'], $jun_data_get[0]['count'], $jul_data_get[0]['count'], $aug_data_get[0]['count'], $sep_data_get[0]['count'], $oct_data_get[0]['count'], $nov_data_get[0]['count'], $dec_data_get[0]['count']];
		// $this->load->view('webshopreports',$data);
		echo json_encode(array('data' => $data['data']));
		exit();
	}

	function b2webshop_chart_Report_ajax_rev()
	{
		// $jan_start=date("Y").'-01-01';$jan_end=date("Y").'-12-31';
		// $jan_start='2021-01-01';$jan_end='2021-12-31';
		$year = $this->input->post('year');
		// print_r($this->input->post('year'));die();
		$jan_start = $year . '-01-01';
		$jan_end = $year . '-01-31';
		$feb_start = $year . '-02-01';
		$feb_end = $year . '-02-31';
		$mar_start = $year . '-03-01';
		$mar_end = $year . '-03-31';
		$apr_start = $year . '-04-01';
		$apr_end = $year . '-04-31';
		$may_start = $year . '-05-01';
		$may_end = $year . '-05-31';
		$jun_start = $year . '-06-01';
		$jun_end = $year . '-06-31';
		$jul_start = $year . '-07-01';
		$jul_end = $year . '-07-31';
		$aug_start = $year . '-08-01';
		$aug_end = $year . '-08-31';
		$sep_start = $year . '-09-01';
		$sep_end = $year . '-09-31';
		$oct_start = $year . '-10-01';
		$oct_end = $year . '-10-31';
		$nov_start = $year . '-11-01';
		$nov_end = $year . '-11-31';
		$dec_start = $year . '-12-01';
		$dec_end = $year . '-12-31';

		$jan_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($jan_start, $jan_end);
		$feb_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($feb_start, $feb_end);
		$mar_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($mar_start, $mar_end);
		$apr_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($apr_start, $apr_end);
		$may_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($may_start, $may_end);
		$jun_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($jun_start, $jun_end);
		$jul_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($jul_start, $jul_end);
		$aug_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($aug_start, $aug_end);
		$sep_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($sep_start, $sep_end);
		$oct_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($oct_start, $oct_end);
		$nov_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($nov_start, $nov_end);
		$dec_data_get_rev = $this->ReportModel->b2getSalesOverview_chart_rev($dec_start, $dec_end);

		// echo $jan_data_get[0]['count'];die();
		// echo "<pre>"; print_r($report_data_get ); echo "</pre>"; die();

		$data['data_rev'] = [$jan_data_get_rev[0]['sum'], $feb_data_get_rev[0]['sum'], $mar_data_get_rev[0]['sum'], $apr_data_get_rev[0]['sum'], $may_data_get_rev[0]['sum'], $jun_data_get_rev[0]['sum'], $jul_data_get_rev[0]['sum'], $aug_data_get_rev[0]['sum'], $sep_data_get_rev[0]['sum'], $oct_data_get_rev[0]['sum'], $nov_data_get_rev[0]['sum'], $dec_data_get_rev[0]['sum']];
		// $this->load->view('webshopreports',$data);
		echo json_encode(array('data_rev' => $data['data_rev']));
		exit();
	}

	function webshop_line_chart_Report_ajax()
	{
		// sales data by month/days
		$year = $_POST['year'];
		$month = $_POST['month'];

		// $year='2021';
		// $month='08';

		$end_date = date("t", strtotime($year . '-' . $month));

		for ($i = 1; $i <= $end_date; $i++) {
			$month_start_date = $year . '-' . $month . '-' . $i;
			$k = $i + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data_by_day = $this->ReportModel->getSalesOverview_chart($month_start_date, $month_end_date);
			$got_days[] = $year . '-' . $month . '-' . $i;
			$got_data[] = $data_by_day[0]['count'];
		}

		$data['data_by_day_dates'] = $got_days;
		$data['data_by_day_sales'] = $got_data;
		echo json_encode(array('data_date' => $data['data_by_day_dates'], 'data' => $data['data_by_day_sales']));
		exit();
	}

	function webshop_line_chart_Report_ajax_rev()
	{
		// sales data by month/days
		$year = $_POST['year'];
		$month = $_POST['month'];

		// $year='2021';
		// $month='08';

		$end_date = date("t", strtotime($year . '-' . $month));

		for ($i = 1; $i <= $end_date; $i++) {
			$month_start_date = $year . '-' . $month . '-' . $i;
			$k = $i + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data_by_day_rev = $this->ReportModel->getSalesOverview_chart_rev($month_start_date, $month_end_date);
			$got_days_rev[] = $year . '-' . $month . '-' . $i;
			if ($data_by_day_rev[0]['sum'] == "") {
				$got_data_rev[] = 0;
			} else {
				$got_data_rev[] = $data_by_day_rev[0]['sum'];
			}
		}

		$data['data_by_day_dates_rev'] = $got_days_rev;
		$data['data_by_day_sales_rev'] = $got_data_rev;
		echo json_encode(array('data_date' => $data['data_by_day_dates_rev'], 'data' => $data['data_by_day_sales_rev']));
		exit();
	}

	function b2webshop_line_chart_Report_ajax()
	{
		// sales data by month/days
		$year = $_POST['year'];
		$month = $_POST['month'];

		// $year='2021';
		// $month='08';

		$end_date = date("t", strtotime($year . '-' . $month));

		for ($i = 1; $i <= $end_date; $i++) {
			$month_start_date = $year . '-' . $month . '-' . $i;
			$k = $i + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data_by_day = $this->ReportModel->b2getSalesOverview_chart($month_start_date, $month_end_date);
			$got_days[] = $year . '-' . $month . '-' . $i;
			$got_data[] = $data_by_day[0]['count'];
		}

		$data['data_by_day_dates'] = $got_days;
		$data['data_by_day_sales'] = $got_data;
		echo json_encode(array('data_date' => $data['data_by_day_dates'], 'data' => $data['data_by_day_sales']));
		exit();
	}

	function b2webshop_line_chart_Report_ajax_rev()
	{
		// sales data by month/days
		$year = $_POST['year'];
		$month = $_POST['month'];

		// $year='2021';
		// $month='08';

		$end_date = date("t", strtotime($year . '-' . $month));

		for ($i = 1; $i <= $end_date; $i++) {
			$month_start_date = $year . '-' . $month . '-' . $i;
			$k = $i + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data_by_day_rev = $this->ReportModel->b2getSalesOverview_chart_rev($month_start_date, $month_end_date);
			$got_days_rev[] = $year . '-' . $month . '-' . $i;
			if ($data_by_day_rev[0]['sum'] == "") {
				$got_data_rev[] = 0;
			} else {
				$got_data_rev[] = $data_by_day_rev[0]['sum'];
			}
		}

		$data['data_by_day_dates_rev'] = $got_days_rev;
		$data['data_by_day_sales_rev'] = $got_data_rev;
		echo json_encode(array('data_date' => $data['data_by_day_dates_rev'], 'data' => $data['data_by_day_sales_rev']));
		exit();
	}



	function test()
	{

		$year = '2021';
		$month = '08';

		// $year=date('Y');
		// $month=date('m');
		// echo $month."<br>";

		$end_date = date("t", strtotime($year . '-' . $month));
		echo $end_date . "<br>";
		for ($i = 1; $i <= $end_date; $i++) {
			$month_start_date = $year . '-' . $month . '-' . $i;
			$k = $i + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data = $this->ReportModel->getSalesOverview_chart($month_start_date, $month_end_date);
			$got_days[] = $year . '-' . $month . '-' . $i;
			$got_data[] = $data[0]['count'];
		}
		// echo"<pre>";
		// print_r($got_data);
		// print_r($got_days);



		for ($j = 1; $j <= $end_date; $j++) {
			$month_start_date = $year . '-' . $month . '-' . $j;
			$k = $j + 1;
			$month_end_date = $year . '-' . $month . '-' . $k;
			$data_by_day_rev = $this->ReportModel->getSalesOverview_chart_rev($month_start_date, $month_end_date);
			$got_days_rev[] = $year . '-' . $month . '-' . $j;
			if ($data_by_day_rev[0]['sum'] == "") {
				$got_data_rev[] = 0;
			} else {
				$got_data_rev[] = $data_by_day_rev[0]['sum'];
			}
		}

		echo "<pre>";
		print_r($got_data_rev);
		print_r($got_days_rev);



		// $dec_start=date("Y").'-12-01';$dec_end=date("Y").'-12-31';

		// $jan_data_get =$this->ReportModel->getSalesOverview_chart($jan_start,$jan_end);


	}


	public function productList()
	{
		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('reports', $this->session->userdata('userPermission'))) {
				redirect('dashboard');
			}
		}

		if (isset($_SESSION['LoginID'])) {
			$current_tab = $this->uri->segment(1);
			$shop_id		=	$this->session->userdata('ShopID');
			$data['PageTitle'] = 'Product Reports';
			$data['side_menu'] = 'reports';
			$data['current_tab'] = (isset($current_tab) && $current_tab != '') ? $current_tab : '';
			$data['getAllCategory'] = $this->ReportModel->getAllCategory();
			$this->load->view('report/product_report', $data);
		} else {
			redirect(base_url('customer/login'));
		}
	}

	function postGenerateProduct()
	{

		//print_r($_POST);
		//die();


		if (($_POST['fromDate'] != '' || $_POST['toDate'] != '') && !array_key_exists("categoryid", $_POST)  && $_POST['upper'] == '0' && $_POST['lower'] == '0') {

			//echo "Only date";die();

			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$fromDate = date(strtotime($fromDate_get));
			$toDate = date(strtotime($toDate_get));


			$product_data_get = $this->ReportModel->getProductData($fromDate, $toDate);
			if (isset($product_data_get) && count($product_data_get) > 0) {
				$this->downloadProductCsv($fromDate_get, $toDate_get, $product_data_get);
			} else {
				$report_export_header = array("Product Id", "Product Type", "Product Inventry Type", "Product Name", "Publication Name", "Catogy Name", "Product Code", "Product Variant", " SKU", "Sub Issu", "Product Creat Date");

				$filename_csv = 'Indiamags-Report-Product-' . date('d-m-Y') . '.csv';
				header("Content-Description: File Transfer");
				header("Content-Disposition: attachment; filename=$filename_csv");
				header("Content-Type: application/csv; ");
				// file creation
				$file_csv = fopen('php://output', 'w');
				fputcsv($file_csv, $report_export_header);
				fclose($file_csv);
				exit();
			}
		} else if (isset($_POST['categoryid']) || ($_POST['fromDate'] != '' || $_POST['toDate'] != '') && $_POST['upper'] == '0' || $_POST['lower'] == '0') {

			if ($_POST['lower'] == '0') {
				//echo "zero";die();

				$fromDate_get = $_POST['fromDate'];
				$toDate_get = $_POST['toDate'];
				$catID = $_POST['categoryid'];
				$fromDate = date(strtotime($fromDate_get));
				$toDate = date(strtotime($toDate_get));


				$ProductDataWithCat = $this->ReportModel->getCatProductData($fromDate, $toDate, $catID);
				// print_r($ProductDataWithCat);die();

				if (isset($ProductDataWithCat) && count($ProductDataWithCat) > 0) {

					$this->downloadReportByCatCsv($fromDate_get, $toDate_get, $ProductDataWithCat);
				} else {
					$report_export_header = array("Product Name", "Publication Name", "Catogy Name", "Product Code", " SKU", "Sub Issu", "Product Creat Date");
					$filename_csv = 'Indiamags-Report-ByCategoryProduct-' . date('d-m-Y') . '.csv';
					header("Content-Description: File Transfer");
					header("Content-Disposition: attachment; filename=$filename_csv");
					header("Content-Type: application/csv; ");
					// file creation
					$file_csv = fopen('php://output', 'w');
					fputcsv($file_csv, $report_export_header);
					fclose($file_csv);
					exit();
				}
			} else {

				//echo"all select";die();

				$fromDate_get = $_POST['fromDate'];
				$toDate_get = $_POST['toDate'];
				$catID = $_POST['categoryid'];
				$startprice = $_POST['upper'];
				$endprice = $_POST['lower'];
				$fromDate = date(strtotime($fromDate_get));
				$toDate = date(strtotime($toDate_get));
				// print_r($fromDate);
				// echo"hy";
				// print_r($toDate);
				// die();
				$ProductDataWithCatAndPrice = $this->ReportModel->getProductDataWithCatAndPrice($fromDate, $toDate, $catID, $startprice, $endprice);

				// print_r($ProductDataWithCatAndPrice);die();

				if (isset($ProductDataWithCatAndPrice) && count($ProductDataWithCatAndPrice) > 0) {

					$this->downloadReportByCatAndPrice($fromDate_get, $toDate_get, $ProductDataWithCatAndPrice);
				} else {
					$report_export_header = array("ID", "Product Name", "Publication Name", "Product Code", "Category Name", "SKU", "Sub Issu", "price", "Product Creat Date");
					$filename_csv = 'Indiamags-Report-ByCategoryPriceProduct-' . date('d-m-Y') . '.csv';
					header("Content-Description: File Transfer");
					header("Content-Disposition: attachment; filename=$filename_csv");
					header("Content-Type: application/csv; ");
					// file creation
					$file_csv = fopen('php://output', 'w');
					fputcsv($file_csv, $report_export_header);
					fclose($file_csv);
					exit();
				}
			}
		} elseif ($_POST['lower'] != '' || ($_POST['fromDate'] != '' || $_POST['toDate'] != '') && !array_key_exists("categoryid", $_POST)) {

			//echo"only Price";die();

			$fromDate_get = $_POST['fromDate'];
			$toDate_get = $_POST['toDate'];
			$startprice = $_POST['upper'];
			$endprice = $_POST['lower'];
			$fromDate = date(strtotime($fromDate_get));
			$toDate = date(strtotime($toDate_get));

			$ProductDataWithPrice = $this->ReportModel->getProductDataPrice($fromDate, $toDate, $startprice, $endprice);
			//print_r($ProductDataWithPrice);die();

			if (isset($ProductDataWithPrice) && count($ProductDataWithPrice) > 0) {

				$this->downloadReportByOnlyPrice($fromDate_get, $toDate_get, $ProductDataWithPrice);
			} else {
				$report_export_header = array("ID", "Product Name", "Publication Name", "Product Code", " SKU", "Sub Issu", "price", "Product Creat Date");
				$filename_csv = 'Indiamags-Report-ByPriceProduct-' . date('d-m-Y') . '.csv';
				header("Content-Description: File Transfer");
				header("Content-Disposition: attachment; filename=$filename_csv");
				header("Content-Type: application/csv; ");
				// file creation
				$file_csv = fopen('php://output', 'w');
				fputcsv($file_csv, $report_export_header);
				fclose($file_csv);
				exit();
			}
		} else {
			echo json_encode(array('flag' => 0, 'msg' => "Please enter all mandatory / compulsory fields."));
			exit;
		}
	}


	public function downloadProductCsv($fromDate, $toDate, $product_data_get)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array("Product Id", "Product Type", "Product Inventry Type", "Product Name", "Publication Name", "Product Code", "Product Variant", " SKU", "Sub Issu", "Product Creat Date");
		$ExportValuesArray = array();
		foreach ($product_data_get as $key => $value) {
			$product_id = $value['id'];
			$type = $value['product_type'];
			$Inventry_type = $value['product_inv_type'];
			$product_name = $value['name'];
			$publication_name = $value['publication_name'];
			// $catogry_name=$value['cat_name'];
			$product_code = $value['product_code'];
			$product_variant = $value['attr_name'];
			$sku = $value['sku'];
			$sub_issu = $value['sub_issues'];
			// $price=$value['price'];
			// $cost_price=$value['cost_price'];
			// $tax_percent =$value['tax_percent'];
			// $webshop_price=$value['webshop_price'];
			$created_at = $value['created_at'];
			$created = '';
			if ($created_at) {
				$created = date(SIS_DATE_FM_WT, $created_at);
			}
			$SingleRow = array($product_id, $type, $Inventry_type, $product_name, $publication_name, $product_code, $product_variant, $sku, $sub_issu, $created);
			$ExportValuesArr1[] = $SingleRow;
		}
		$filename_csv = 'Indiamags-Report-Product-' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename_csv");
		header("Content-Type: application/csv; ");
		// file creation
		$file_csv = fopen('php://output', 'w');

		fputcsv($file_csv, $report_export_header);
		if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
			foreach ($ExportValuesArr1 as $readData_one) {
				fputcsv($file_csv, $readData_one);
			}
		}
		fclose($file_csv);
		exit();
	}


	//Report By Category filter
	public function downloadReportByCatCsv($fromDate, $toDate, $ProductDataWithCat)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array("Product Name", "Publication Name", "Catogy Name", "Product Code", " SKU", "Sub Issu", "Product Creat Date");
		$ExportValuesArray = array();
		foreach ($ProductDataWithCat as $key => $value) {
			// $product_id=$value['PID'];
			$product_name = $value['name'];
			$publication_name = $value['publication_name'];
			$catogry_name = $value['cat_name'];
			$product_code = $value['product_code'];
			$sku = $value['sku'];
			$sub_issu = $value['sub_issues'];
			$created_at = $value['created_at'];
			$created = '';
			if ($created_at) {
				$created = date(SIS_DATE_FM_WT, $created_at);
			}
			$SingleRow = array($product_name, $publication_name, $catogry_name, $product_code, $sku, $sub_issu, $created);
			$ExportValuesArr1[] = $SingleRow;
		}
		$filename_csv = 'Indiamags-Report-ByCategoryProduct-' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename_csv");
		header("Content-Type: application/csv; ");
		// file creation
		$file_csv = fopen('php://output', 'w');

		fputcsv($file_csv, $report_export_header);
		if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
			foreach ($ExportValuesArr1 as $readData_one) {
				fputcsv($file_csv, $readData_one);
			}
		}
		fclose($file_csv);
		exit();
	}


	//Report By  only Price filter
	public function downloadReportByOnlyPrice($fromDate, $toDate, $ProductDataWithPrice)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array("ID", "Product Name", "Publication Name", "Product Code", " SKU", "Sub Issu", "price", "Product Creat Date");
		$ExportValuesArray = array();
		foreach ($ProductDataWithPrice as $key => $value) {
			$product_id = $value['id'];
			$product_name = $value['name'];
			$publication_name = $value['publication_name'];
			// $catogry_name=$value['cat_name'];
			$product_code = $value['product_code'];
			$sku = $value['sku'];
			$sub_issu = $value['sub_issues'];
			$price = $value['price'];
			$created_at = $value['created_at'];
			$created = '';
			if ($created_at) {
				$created = date(SIS_DATE_FM_WT, $created_at);
			}
			$SingleRow = array($product_id, $product_name, $publication_name, $product_code, $sku, $sub_issu, $price, $created);
			$ExportValuesArr1[] = $SingleRow;
		}
		$filename_csv = 'Indiamags-Report-ByPriceProduct-' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename_csv");
		header("Content-Type: application/csv; ");
		// file creation
		$file_csv = fopen('php://output', 'w');

		fputcsv($file_csv, $report_export_header);
		if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
			foreach ($ExportValuesArr1 as $readData_one) {
				fputcsv($file_csv, $readData_one);
			}
		}
		fclose($file_csv);
		exit();
	}


	//Report By  Cat Price filter
	public function downloadReportByCatAndPrice($fromDate, $toDate, $ProductDataWithCatAndPrice)
	{
		if ($fromDate && $toDate) {
			$fromDate = date(strtotime($fromDate));
			$toDate = date(strtotime($toDate));
		} else {
			$fromDate = '';
			$toDate = '';
		}
		//csv header
		$report_export_header = array("ID", "Product Name", "Publication Name", "Product Code", "Category Name", "SKU", "Sub Issu", "price", "Product Creat Date");
		$ExportValuesArray = array();
		foreach ($ProductDataWithCatAndPrice as $key => $value) {
			$product_id = $value['id'];
			$product_name = $value['name'];
			$publication_name = $value['publication_name'];
			$product_code = $value['product_code'];
			$catogry_name = $value['cat_name'];
			$sku = $value['sku'];
			$sub_issu = $value['sub_issues'];
			$price = $value['price'];
			$created_at = $value['created_at'];
			$created = '';
			if ($created_at) {
				$created = date(SIS_DATE_FM_WT, $created_at);
			}
			$SingleRow = array($product_id, $product_name, $publication_name, $product_code, $catogry_name, $sku, $sub_issu, $price, $created);
			$ExportValuesArr1[] = $SingleRow;
		}
		$filename_csv = 'Indiamags-Report-ByCategoryPriceProduct-' . date('d-m-Y') . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename_csv");
		header("Content-Type: application/csv; ");
		// file creation
		$file_csv = fopen('php://output', 'w');

		fputcsv($file_csv, $report_export_header);
		if (isset($ExportValuesArr1) && count($ExportValuesArr1) > 0) {
			foreach ($ExportValuesArr1 as $readData_one) {
				fputcsv($file_csv, $readData_one);
			}
		}
		fclose($file_csv);
		exit();
	}
}
