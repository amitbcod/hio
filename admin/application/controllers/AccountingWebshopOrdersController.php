<?php

use App\Actions\Invoices\CreateInvoiceFromSalesOrder;
use App\Actions\Invoices\GenerateInvoicePdf;
use App\Actions\Invoices\GetInvoiceHtml;
use App\Actions\Invoices\SendInvoiceByEmail;

defined('BASEPATH') OR exit('No direct script access allowed');

class AccountingWebshopOrdersController extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
		if(!empty($this->session->userdata('userPermission')) && !in_array('accounting',$this->session->userdata('userPermission'))){
           redirect(base_url('dashboard'));  }

		$this->load->model('AccountingWebshopOrdersModel');
		$this->load->model('WebshopOrdersModel');
		$this->load->model('B2BOrdersModel');
		$this->load->model('UserModel');
		$this->load->model('CommonModel');
		$this->load->library('encryption');
		// $this->load->model('AccountingModel');

	}

	public function index()
	{
		$current_tab=$this->uri->segment(2);
		$data['PageTitle']='Accounting - B2Webshop Orders to be Billed';
		$data['side_menu']='accounting';
		$data['current_tab']='Webshop Orders';

		// $data['current_tab']=(isset($current_tab) && $current_tab!='')?$current_tab:'';

		//echo $this->AccountingWebshopOrdersModel->generate_new_transaction_id().'=====';
		$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
		$data['shop_flag']=$FbcUser->shop_flag;

		$access_page=$this->CommonModel->page_access();
		if(isset($access_page->acc_inv_flag) && $access_page->acc_inv_flag==1){
			$this->load->view('accounting/order/orderlist',$data);
		}else{
			$this->load->view('accounting/order/access',$data);
		}

	}

	function loadordersajax()
	{
        $AccountingWebshopData = $this->AccountingWebshopOrdersModel->getWebshopOrderNotInvoice();
		$data = array();
		$no = $_POST['start'];

		$customerBillNameId=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
		$use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');
		$customerBillNamedata=$this->AccountingWebshopOrdersModel->get_customer_data_accounting_ajax();
		$customer_invoice_data=$this->AccountingWebshopOrdersModel->get_customer_invoice_data_accounting_ajax();

		foreach ($AccountingWebshopData as $readData) {
			$no++;
			$row = array();
				$customer_Name=$readData->customer_firstname.' '.$readData->customer_lastname;
				$invoiceType='';
				$customerBillName_custom='';
				$customerBillName='';
				$invoiceSelf=$readData->invoice_self;
				$customerType='';

				if($readData->customer_id==0){
					if($invoiceSelf==1){
						$customerBillName=$customer_Name;
						$invoiceType="Invoice Per Order";
					}else{
						$customerBillName=$customer_Name;
						$customerBillName_custom='custom_variables';
					}
					$customerType='Guest';
				}else{
					$customerType='<a class="link-purple" target="_blank" href="'.base_url().'CustomerController/customer_details/'.$readData->customer_id.'"> Registered </a>';
					$customer_Name='<a class="link-purple" target="_blank" href="'.base_url().'CustomerController/customer_details/'.$readData->customer_id.'">'.$customer_Name.'</a>';
					if($readData->invoice_to_type==0){
						$customerBillName=$customer_Name;
					}elseif($readData->invoice_to_type==1){
						if($readData->alternative_email_id){
							$customerBillName_custom='custom_id';
						}else{
							$customerBillName_custom='custom_variables';
						}
					}

					// invoice type
					if($readData->invoice_type==1){
						$invoiceType="Invoice Per Order";
					}elseif($readData->invoice_type==2){
						$invoiceType="Invoice Daily";
					}elseif($readData->invoice_type==3){
						$invoiceType="Invoice weekly";
					}elseif($readData->invoice_type==4){
						$invoiceType="Invoice Monthly";
					}else{
						$invoiceType="Invoice Per Order";
					}

				}

				// custom variable
				if($customerBillName_custom) {
					if($customerBillName_custom=='custom_variables'){
						$customerBillId=$customerBillNameId->value;
					}elseif ($customerBillName_custom=='custom_id') {
						$customerBillId=$readData->alternative_email_id;
					}

					if($customerBillId){

						$key=array_search($customerBillId, array_column($customerBillNamedata, 'id'));
						$customerBillNameGet=$customerBillNamedata[$key];
						$customer_BillName=$customerBillNameGet->first_name.' '.$customerBillNameGet->last_name;

						$customerBillName='<a class="link-purple" target="_blank" href="'.base_url().'CustomerController/customer_details/'.$customerBillId.'">'.$customer_BillName.'</a>';
						// invoice type
						if($customerType=='Guest' && $invoiceSelf==0){
							$key=array_search($customerBillId, array_column($customer_invoice_data, 'customer_id'));
							$invoiceData=$customer_invoice_data[$key];

							// invoice type
							if(isset($invoiceData->invoice_type)){
								if($invoiceData->invoice_type==1){
									$invoiceType="Invoice Per Order";
								}elseif($invoiceData->invoice_type==2){
									$invoiceType="Invoice Daily";
								}elseif($invoiceData->invoice_type==3){
									$invoiceType="Invoice weekly";
								}elseif($invoiceData->invoice_type==4){
									$invoiceType="Invoice Monthly";
								}else{
									$invoiceType="Invoice Per Order";
								}
							}else{
								$invoiceType="Invoice Per Order";
							}

						}elseif($customerType!='Guest' && $invoiceSelf==0){

								$key=array_search($customerBillId, array_column($customer_invoice_data, 'customer_id'));
								$invoiceData=$customer_invoice_data[$key];

								if(isset($invoiceData->invoice_type)){
									if($invoiceData->invoice_type==1){
										$invoiceType="Invoice Per Order";
									}elseif($invoiceData->invoice_type==2){
										$invoiceType="Invoice Daily";
									}elseif($invoiceData->invoice_type==3){
										$invoiceType="Invoice weekly";
									}elseif($invoiceData->invoice_type==4){
										$invoiceType="Invoice Monthly";
									}else{
										$invoiceType="Invoice Per Order";
									}
								}else{
									$invoiceType="Invoice Per Order";
								}
						}

					}
				}

				$no++;
				$row = array();
				$row[]='<input type="checkbox" name="check_id[]" id="check_all" class="check_all" value="'.$readData->order_id.'">';
				$row[]="#".$readData->increment_id;
				$row[]=$customer_Name;
				$row[]=$invoiceType;

				if ($use_advanced_warehouse->value=="yes") {
					$warehouse_status=$readData->warehouse_status;
					$row[]=$this->CommonModel->getWarehouse_status_name($warehouse_status);
				}
				$row[]=$customerBillName;
				$row[]=$customerType;
				if($readData->tracking_complete_date){$trDate=date(SIS_DATE_FM,$readData->tracking_complete_date);}else{$trDate='';}
				$row[]=$trDate;
				$row[]='<a class="link-purple" href="'.base_url().'webshop/shipped-order/detail/'.$readData->order_id.'">View</a>';
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->AccountingWebshopOrdersModel->count_all_WebshopOrderNotInvoice(),
						"recordsFiltered" => $this->AccountingWebshopOrdersModel->count_filtered_WebshopOrderNotInvoice(),
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);
		exit();
	}

	function loadordersajax_not()
	{
        $AccountingWebshopData = $this->AccountingWebshopOrdersModel->getWebshopOrderNotInvoice_not();
		$data = array();
		$no = $_POST['start'];

		$customerBillNameId=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
		$use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');
		$customerBillNamedata=$this->AccountingWebshopOrdersModel->get_customer_data_accounting_ajax();
		$customer_invoice_data=$this->AccountingWebshopOrdersModel->get_customer_invoice_data_accounting_ajax();

		foreach ($AccountingWebshopData as $readData) {
			$no++;
			$row = array();
				$customer_Name=$readData->customer_firstname.' '.$readData->customer_lastname;
				$invoiceType='';
				$customerBillName_custom='';
				$customerBillName='';
				$invoiceSelf=$readData->invoice_self;
				$customerType='';

				if($readData->customer_id==0){
					if($invoiceSelf==1){
						$customerBillName=$customer_Name;
						$invoiceType="Invoice Per Order";
					}else{
						$customerBillName=$customer_Name;
						$customerBillName_custom='custom_variables';
					}
					$customerType='Guest';

				}else{
					$customerType='<a class="link-purple" target="_blank" href="'.base_url().'CustomerController/customer_details/'.$readData->customer_id.'"> Registered </a>';
					$customer_Name='<a class="link-purple" target="_blank" href="'.base_url().'CustomerController/customer_details/'.$readData->customer_id.'">'.$customer_Name.'</a>';
					if($readData->invoice_to_type==0){
						$customerBillName=$customer_Name;
					}elseif($readData->invoice_to_type==1){
						if($readData->alternative_email_id){
							$customerBillName_custom='custom_id';
						}else{
							$customerBillName_custom='custom_variables';
						}
					}

					// invoice type
					if($readData->invoice_type==1){
						$invoiceType="Invoice Per Order";
					}elseif($readData->invoice_type==2){
						$invoiceType="Invoice Daily";
					}elseif($readData->invoice_type==3){
						$invoiceType="Invoice weekly";
					}elseif($readData->invoice_type==4){
						$invoiceType="Invoice Monthly";
					}else{
						$invoiceType="Invoice Per Order";
					}

				}

				// custom variable
				if($customerBillName_custom) {
					if($customerBillName_custom=='custom_variables'){
						$customerBillId=$customerBillNameId->value;
					}elseif ($customerBillName_custom=='custom_id') {
						$customerBillId=$readData->alternative_email_id;
					}
					if($customerBillId){

						$key=array_search($customerBillId, array_column($customerBillNamedata, 'id'));
						$customerBillNameGet=$customerBillNamedata[$key];
						$customer_BillName=$customerBillNameGet->first_name.' '.$customerBillNameGet->last_name;

						$customerBillName='<a class="link-purple" target="_blank" href="'.base_url().'CustomerController/customer_details/'.$customerBillId.'">'.$customer_BillName.'</a>';

						// invoice type
						if($customerType=='Guest' && $invoiceSelf==0){
							$key=array_search($customerBillId, array_column($customer_invoice_data, 'customer_id'));
							$invoiceData=$customer_invoice_data[$key];

							// invoice type
							if(isset($invoiceData->invoice_type)){
								if($invoiceData->invoice_type==1){
									$invoiceType="Invoice Per Order";
								}elseif($invoiceData->invoice_type==2){
									$invoiceType="Invoice Daily";
								}elseif($invoiceData->invoice_type==3){
									$invoiceType="Invoice weekly";
								}elseif($invoiceData->invoice_type==4){
									$invoiceType="Invoice Monthly";
								}else{
									$invoiceType="Invoice Per Order";
								}
							}else{
								$invoiceType="Invoice Per Order";
							}

						}elseif($customerType!='Guest' && $invoiceSelf==0){
								$key=array_search($customerBillId, array_column($customer_invoice_data, 'customer_id'));
								$invoiceData=$customer_invoice_data[$key];

								if(isset($invoiceData->invoice_type)){
									if($invoiceData->invoice_type==1){
										$invoiceType="Invoice Per Order";
									}elseif($invoiceData->invoice_type==2){
										$invoiceType="Invoice Daily";
									}elseif($invoiceData->invoice_type==3){
										$invoiceType="Invoice weekly";
									}elseif($invoiceData->invoice_type==4){
										$invoiceType="Invoice Monthly";
									}else{
										$invoiceType="Invoice Per Order";
									}
								}else{
									$invoiceType="Invoice Per Order";
								}
						}
					}
				}

				$no++;
				$row = array();
				$row[]='<input type="checkbox" name="check_id[]" id="check_all" class="check_all" value="'.$readData->order_id.'">';
				$row[]="#".$readData->increment_id;
				$row[]=$customer_Name;
				$row[]=$invoiceType;

				if ($use_advanced_warehouse->value=="yes") {
					$warehouse_status=$readData->warehouse_status;
					$row[]=$this->CommonModel->getWarehouse_status_name($warehouse_status);
				}
				$row[]=$customerBillName;
				$row[]=$customerType;
				if($readData->tracking_complete_date){$trDate=date(SIS_DATE_FM,$readData->tracking_complete_date);}else{$trDate='';}
				$row[]=$trDate;
				$row[]='<a class="link-purple" href="'.base_url().'webshop/shipped-order/detail/'.$readData->order_id.'">View</a>';
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->AccountingWebshopOrdersModel->count_all_WebshopOrderNotInvoice_not(),
						"recordsFiltered" => $this->AccountingWebshopOrdersModel->count_filtered_WebshopOrderNotInvoice_not(),
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);
		exit();
	}

	public function b2WebshopList() {

			// $data['messageList'] = $this->AccountingModel->getMessageDetails($_SESSION['ShopID'],$search_term);
			$data['PageTitle']='Accounting - B2Webshop Orders to be Billed';
			$data['side_menu']='accounting';
			$data['current_tab']='B2Webshop Orders';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
			$data['shop_flag']=$FbcUser->shop_flag;
			$access_page=$this->CommonModel->page_access();
			if(isset($access_page->acc_inv_flag) && $access_page->acc_inv_flag==1){
				$this->load->view('accounting/order/b2Webshoplist',$data);
			}else{
				$this->load->view('accounting/order/access',$data);
			}


	}

	function loadordersB2Webshopajax()
	{
        $AccountingB2WebshopData = $this->AccountingWebshopOrdersModel->getB2WebshopOrderNotInvoice();
        // print_r($AccountingWebshopData);
        //print_r($AccountingB2WebshopData);exit();
        //$total_count = (is_array($AccountingWebshopData)) ? count($AccountingWebshopData) : 0;
		$data = array();
		$no = $_POST['start'];
		foreach ($AccountingB2WebshopData as $readData) {
			$no++;
			$row = array();
			// $customerName=$readData->customer_name;//old
			$customerName=$readData->owner_name;
			$customer_Name='<a class="link-purple" target="_blank" href="'.base_url().'b2b/customer/detail/'.$readData->fbc_user_id.'">'.$customerName.'</a>';
			// $customer_Name=$readData->customer_firstname.''.$readData->customer_lastname;
			$invoiceType='';
			//$customerBillName_custom=$customer_Name;
			// $customerBillName=$customer_Name;//old
			$customerBillName=$customer_Name;
			// $invoiceSelf=$readData->invoice_self;
			$customerType='';

				//echo $readData->invoice_type;exit();
				// print_r($readData);exit();
				if($readData->invoice_type==1){
					$invoiceType="Invoice Per Order";
				}elseif($readData->invoice_type==2){
					$invoiceType="Invoice Daily";
				}elseif($readData->invoice_type==3){
					$invoiceType="Invoice weekly";
				}elseif($readData->invoice_type==4){
					$invoiceType="Invoice Monthly";
				}else{
					$invoiceType="Invoice Per Order";
					// $invoiceType="None";
				}

				$no++;
				$row = array();
				$row[]='<input type="checkbox" name="check_id[]" id="check_all" class="check_all" value="'.$readData->order_id.'">';
				$row[]="#".$readData->increment_id;
				$row[]=$readData->org_shop_name;
				$row[]=$customer_Name;
				$row[]=$invoiceType;
				$row[]=$customerBillName;
				if($readData->tracking_complete_date){$trDate=date(SIS_DATE_FM,$readData->tracking_complete_date);}else{$trDate='';}
				$row[]=$trDate;
				$row[]='<a class="link-purple" target="_blank" href="'.base_url().'b2b/shipped-order/detail/'.$readData->order_id.'">View</a>';
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->AccountingWebshopOrdersModel->count_all_B2WebshopOrderNotInvoice(),
						"recordsFiltered" => $this->AccountingWebshopOrdersModel->count_filtered_B2WebshopOrderNotInvoice(),
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);
		exit;
	}

	public function invoicingList() {

			// $data['messageList'] = $this->AccountingModel->getMessageDetails($_SESSION['ShopID'],$search_term);
			$data['PageTitle']='Accounting - Invoicing';
			$data['side_menu']='accounting';
			$data['current_tab']='Invoicing';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
			$data['shop_flag']=$FbcUser->shop_flag;
			$access_page=$this->CommonModel->page_access();
			if(isset($access_page->acc_inv_flag) && $access_page->acc_inv_flag==1){
				$this->load->view('accounting/order/invoicinglist',$data);
			}else{
				$this->load->view('accounting/order/access',$data);
			}

	}

	function loadinvoicingsajax()
	{
        $AccountingInvoicingWebshopData = $this->AccountingWebshopOrdersModel->getInvoicingWebshopOrderNotInvoice();

        //$total_count = (is_array($AccountingWebshopData)) ? count($AccountingWebshopData) : 0;
		$data = array();
		$no = $_POST['start'];
		foreach ($AccountingInvoicingWebshopData as $readData) {
			$no++;
			$row = array();
			$customer_Name=$readData->customer_first_name.''.$readData->customer_last_name;
			$invoiceType='';
			$customerBillName_custom='';
			$customerType='';
			$status='';
			$invoiceDate='';
			$resentDate='';
			$resendInvoice='-';

			if($readData->invoice_order_type==1){
				$invoiceType="Webshop";
				if(isset($readData->invoice_order_nos) && !empty($readData->invoice_order_nos)){
					$selfInvoiceCount=$this->WebshopOrdersModel->getSingleDataByID('sales_order',array('order_id'=>$readData->invoice_order_nos),'invoice_self');
					if(isset($selfInvoiceCount) && $selfInvoiceCount->invoice_self==1){
						$invoice_file=$readData->invoice_file;
						$resendInvoice="<a class='link-purple' onClick=reSendInvoice('".$readData->id."','".$invoice_file."') >Resend Invoice</a>";
					}
				}
			}elseif($readData->invoice_order_type==2){
				$invoiceType="B2Webshop";
			}
			if($readData->created_at){
				$invoiceDate=date(SIS_DATE_FM_WT,$readData->invoice_date);
			}
			if($readData->last_resent_date){
				$resentDate=date(SIS_DATE_FM_WT,$readData->last_resent_date);
			}

			$no++;
			$row = array();
			$row[]="#".$readData->invoice_no;
			$row[]=$invoiceDate;
			$row[]=$customer_Name;
			$row[]=$invoiceType;
			$row[]=$readData->invoice_grand_total;
			/*$row[]=$status;
			$row[]=$resentDate;*/
			$row[]=$resendInvoice;
			if($readData->invoice_file){
				if($this->session->userdata('ShopID')>0){
					$row[]='<a class="link-purple" target="_blank" href="'.get_s3_url('invoices/'.$readData->invoice_file).'">View</a>';
				}else{ $row[]='';}
			}else{
				$row[]='';
			}
			// $row[]='<a class="link-purple" href="'.base_url().'webshop/shipped-order/detail/'.$readData->id.'">View</a>';
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->AccountingWebshopOrdersModel->count_all_InvoicingWebshopOrderNotInvoice(),
						"recordsFiltered" => $this->AccountingWebshopOrdersModel->count_filtered_InvoicingWebshopOrderNotInvoice(),
						"data" => $data,
				);

		//output to json format
		echo json_encode($output);
		exit;
	}

	/*function testing(){
		print_r($this->AccountingWebshopOrdersModel->testtest());
	}*/

	// csv export

	public function downlaodInvoiceAllCsv(){
		$data['type']=$type=isset($_POST['type'])?$_POST['type']:'';
		if($data['type']== 'exporttAll')
		{
			$data['PageTitle']='Invoicing - CSV Export All ';
			$data['side_menu']='invoicing';

		}else
		{
			$data['PageTitle']='Invoicing - CSV Export';
			$data['side_menu']='invoicing';
		}
		$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
		$data['shop_flag']=$FbcUser->shop_flag;
		$View = $this->load->view('accounting/order/download/bulk_invoice', $data, true);//pending
		$this->output->set_output($View);
	}

	// download
	public function downloadinvocingcsv()
	{

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		/* login shop details*/
			$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($shop_id);
			$data['user_details'] = $this->CommonModel->GetUserByUserId($fbc_user_id);
			if($data['user_details']->parent_id == 0)
			{
				$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
			}else{
				$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
			}

			$shop_bill_state=$data['user_shop_details']->bill_state;
			$shop_bill_country=$data['user_shop_details']->bill_country;

			/*print_r($data['user_shop_details']);
			exit();*/
		/* end login shop details*/


		// $invoing_data =$this->AccountingWebshopOrdersModel->getinvoicingForCSVImport();// invoice table
		$invoing_data =$this->AccountingWebshopOrdersModel->getinvoicingForCSVImport_details(); // invoice_detail table
		$inv_export_header = array("Order Id","Order Date","Bill Date","Due Date","Terms Bill No","Customers Name","Order Customer","Item(Product/Service)","HSN Code","Product Name","Product SKU","Product Category","Product Qty Sold","Place of Supply","Product Rate","GST Rates Applicable","CGST Amt","SGST Amt","IGST Amt","Total GST Amt","Total Amt Excluding GST","Total Amt");
 // file name
		if(isset($invoing_data ) && count($invoing_data )>0)
			{
				$ExportValuesArr=array();
				foreach($invoing_data  as $invoing_val){
					//print_r($invoing_val);
					$order_id=$invoing_val->order_id;
					$order_date='';
					$invoice_date='';
					$invoice_due_date='';
					if($invoing_val->order_date){
						$order_date=date(DATE_PIC_FM,$invoing_val->order_date);
					}
					if($invoing_val->invoice_date){
						$invoice_date=date(DATE_PIC_FM,$invoing_val->invoice_date);
					}
					if($invoing_val->invoice_due_date){
						$invoice_due_date=date(DATE_PIC_FM,$invoing_val->invoice_due_date);
					}
					$invoice_no=$invoing_val->invoice_no;
					$customers_name=$invoing_val->bill_customer_first_name.' '.$invoing_val->bill_customer_last_name;
					$order_customers=$invoing_val->customer_first_name.' '.$invoing_val->customer_last_name;
					// custom variable
					$custom_variables_data=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'sales_account'),'value');
					$item_product_service=$custom_variables_data->value;// custom variable
					$hsn_code=$invoing_val->product_hsn_code;// custom variable
					$product_name=$invoing_val->product_name;
					$product_sku=$invoing_val->product_sku;
					$product_category=$invoing_val->product_category;
					$product_qty=$invoing_val->product_qty;
					$place_of_supply=$invoing_val->place_of_supply;
					$product_rate=number_format($invoing_val->product_price,2);
					$gst_rate_applicable=$invoing_val->gst_rates_applicable;
					//igst cgst sgst
					$ship_state=$invoing_val->ship_state;
					$billing_state=$invoing_val->billing_state;
					$ship_country=$invoing_val->ship_country;
					$billing_country=$invoing_val->billing_country;
					$cgst_amount1='';
					$sgst_amount1='';
					$igst_amount1='';
					if($gst_rate_applicable > 0){
						if($shop_bill_country=='IN' && $ship_country=='IN'){
							if($shop_bill_state==$ship_state){
								$divedGst_row_amount=$invoing_val->gst_row_amount/2;
								$cgst_amount1=number_format($divedGst_row_amount,2);
								$sgst_amount1=number_format($divedGst_row_amount,2);
						//echo $gst_rate_applicable;
							}else{
								$igst_amount1=number_format($invoing_val->gst_row_amount,2);
							}
						}
					}

					// exit();
					$variant_data='';
					$colour_val = '';
					$size_val = '';
					$Other_val = array();
					if(isset($invoing_val->product_variants) && $invoing_val->product_variants!=''){
						$variants=json_decode($invoing_val->product_variants, true);
						if(isset($variants) && count($variants)>0){
							foreach($variants as $pk=>$single_variant){
								if($pk > 0){ $variant_data.= ', ';}
								foreach($single_variant as $key=>$val){
									//$variant_data.='-'.$val;
									// $variant_data.=' '.$key.' - '.$val;
									if($key == 'Color'){

										$colour_val = " - ".$val;

									}else if($key == 'Size' || $key == 'Shoe Size'){

										$size_val = " - ".$val;
									}else{

										$Other_val[] =  $val;

									}
								}
							}
						}
					}
					//if($variant_data){ $variant_data='('.$variant_data.')';}
					$other_val = implode("-",$Other_val);
					$product_variants=$product_name.$size_val.$colour_val.$other_val;
					//$product_variants=$product_name.' '.$variant_data; //combine product and varients

					$gst_amount=$invoing_val->gst_amount;
					$cgst_amount=$cgst_amount1;
					$sgst_amount=$sgst_amount1;
					$igst_amount=$igst_amount1;
					$total_gst_amount=number_format($invoing_val->gst_row_amount,2);
					$total_amount_excluding_gst=number_format($invoing_val->total_amount_excluding_gst,2);
					$total_amount=number_format($invoing_val->total_amount_including_gst,2);
					$order_increment_id='';
					if($invoing_val->invoice_order_type==1){//webshop
						$orderData=$this->CommonModel->getSingleShopDataByID('sales_order',array('order_id'=>$order_id),'increment_id');
						if($orderData){
							$order_increment_id='#'.$orderData->increment_id;
						}
					}elseif($invoing_val->invoice_order_type==2){//b2b
						$orderData=$this->CommonModel->getSingleShopDataByID('b2b_orders',array('order_id'=>$order_id),'increment_id');
						if($orderData){
							$order_increment_id='#'.$orderData->increment_id;
						}
					}


					//print_r($invoing_val);exit();
					$SingleRow=array($order_increment_id,$order_date,$invoice_date,$invoice_due_date,$invoice_no,$customers_name,$order_customers,$item_product_service,$hsn_code,$product_variants,$product_sku,$product_category,$product_qty,$place_of_supply,$product_rate,$gst_rate_applicable,$cgst_amount,$sgst_amount,$igst_amount,$total_gst_amount,$total_amount_excluding_gst,$total_amount);
					// $SingleRow=array("$sku","$barcode","$name","$launch_date","$variants_new","$cat_name","$inventory","$cost_price","$tax_percent","$selling_price","$webshop_price");


					$ExportValuesArr[]=$SingleRow;




				}
				// print_r_custom($ExportValuesArr);exit;
			}
			$filename = 'Invoicing-' . time() . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename");
			header("Content-Type: application/csv; ");

			// file creation
			$file = fopen('php://output', 'w');
			fputcsv($file, $inv_export_header);
			if(isset($ExportValuesArr) && count($ExportValuesArr)>0){

				foreach ($ExportValuesArr as $readData) {
					fputcsv($file, $readData);
				}
			}
			fclose($file);
			/*exit;
			echo "success";exit;*/
	} //end download

	// check box all button webshop invoice generate
	function postCheckedInvoice(){
		if(isset($_POST) && isset($_POST['invoice_check_list'])){
			$billDate=$_POST['billDate'];
			$checkedCatArr = isset($_POST['check_id'])?$_POST['check_id']:array();
			if(is_array($checkedCatArr) && count($checkedCatArr) <= 0){
				echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));
				exit;
			}
			if(is_array($checkedCatArr) && count($checkedCatArr)>0){
				foreach($checkedCatArr as $key=>$value){
					$order_id=$value;
					if($order_id){
						$orderData=$this->invoiceGenerate($order_id,$billDate);
						// $orderData=$this->invoiceGenerate($order_id);//old
						// $orderData=$this->WebshopOrdersModel->get_webshop_invoicing_data($order_id);
						//print_r($orderData);
					}

				}
			}
			echo json_encode(array('flag' => 1, 'msg' => "Invoice generate success"));
			exit;
		}else{
			echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));
			exit;
		}
	}


	// check box all button webshop invoice generate
	function postCheckedInvoiceB2b(){
		if(isset($_POST) && isset($_POST['invoice_check_list'])){
			$billDate=$_POST['billDate'];
			$checkedCatArr = isset($_POST['check_id'])?$_POST['check_id']:array();
			if(is_array($checkedCatArr) && count($checkedCatArr) <= 0){
				echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));
				exit;
			}
			if(is_array($checkedCatArr) && count($checkedCatArr)>0){
				foreach($checkedCatArr as $key=>$value){
					$order_id=$value;
					if($order_id){
						$orderData=$this->invoiceGenerateB2b($order_id,$billDate);
						// $orderData=$this->invoiceGenerateB2b($order_id);//old
						// $orderData=$this->WebshopOrdersModel->get_webshop_invoicing_data($order_id);
						//print_r($orderData);
					}

				}
			}
			echo json_encode(array('flag' => 1, 'msg' => "Invoice generate success"));
			exit;
		}else{
			echo json_encode(array('flag' => 0, 'msg' => "Please seclect at least one Category"));
			exit;
		}
	}


	/*start webshop invoice generate*/
	// invoice

	function send_invoice_by_email(){
		$invoice_id = (int) $_GET['invoice_id'];
		(new SendInvoiceByEmail())($invoice_id);
	}

	function test_invoiceGenerate(){
		$order_id = 1461;
//		$order_id = 1449;
//		$order_id = 1525;
		$billDate = '30-04-2022';

		$invoice_id = (new CreateInvoiceFromSalesOrder())($order_id, $billDate);
		$html = (new GetInvoiceHtml())($invoice_id);
		echo $html;
//		$pdf = (new GenerateInvoicePdf())($html, $invoice_id, 1);
//		(new SendInvoiceByEmail())($invoice_id, $pdf);
	}

	function invoiceGenerate($order_id,$billDate){
		$shop_id		=	$this->session->userdata('ShopID');
		if((int) $shop_id === 1){
			$invoice_id = (new CreateInvoiceFromSalesOrder())($order_id, $billDate);
			$html = (new GetInvoiceHtml())($invoice_id);
			$pdf = (new GenerateInvoicePdf())($html, $invoice_id, 1);
			(new SendInvoiceByEmail())($invoice_id, $pdf);

			return;
		}

		$orderData=$this->WebshopOrdersModel->get_webshop_invoicing_data($order_id);

		$orderUserType=$orderData->checkout_method; //guest,register,login

		$ShippingAddress=$ShippingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$orderData->order_id,'address_type'=>2),'');
		$BillingAddress=$BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('sales_order_address',array('order_id'=>$orderData->order_id,'address_type'=>1),'');

		// print_r($ShippingAddress);
		$customVariables_invoice_prefix=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'invoice_prefix'),'value');
		$customVariables_invoice_no=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'invoice_next_no'),'value');
		if($customVariables_invoice_prefix->value && $customVariables_invoice_no->value){
			$invoiceNo=$customVariables_invoice_no->value;
			$invoice_next_no=$invoiceNo+1;
			$invoice_no=$customVariables_invoice_prefix->value.$invoiceNo;
		}elseif($customVariables_invoice_prefix->value){
			// $invoiceNo='2';
			$invoiceNo='1';
			$invoice_next_no=$invoiceNo+1;
			$invoice_no=$customVariables_invoice_prefix->value.$invoiceNo;
			# code...
		}elseif($customVariables_invoice_no->value) {
			$invoiceNo=$customVariables_invoice_no->value;
			$invoice_next_no=$invoiceNo+1;
			$invoice_no='INV'.$invoiceNo;
		}
		// custom_variable table data updated
		$invoice_no=$invoice_no;
		$invoice_next_no=$invoice_next_no;

		// invoice table insert
		$invoice_order_type='1'; //1-Webshop;  2-B2Webshop;
		$shop_id='';
		$customer_name=$orderData->customer_name;
		$customer_first_name=$orderData->customer_firstname;
		$customer_last_name=$orderData->customer_lastname;
		$customer_id=$orderData->customer_id;
		$customer_is_guest=$orderData->customer_is_guest;
		$invoice_self=$orderData->invoice_self;
		// $shop_webshop_name=$orderData->org_shop_name;
		$shop_webshop_name='';
		$shop_gst_no='';//$orderData->gst_no
		$shop_company_name='';//$orderData->company_name
		//echo $customer_id;
		// $orderData->invoice_self=1;

		//$customer_email=$orderData->customer_email;
		$invoice_subtotal=0;
		$invoice_tax=0;
		$invoice_grand_total=0;
		// bill invoice
		$bill_customer_first_name=$BillingAddress->first_name;
		$bill_customer_last_name=$BillingAddress->last_name;
		$bill_customer_id='';
		$bill_customer_email='';
		// $bill_customer_email=$orderData->email;
		$billing_address_line1=$BillingAddress->address_line1;//$orderData->bill_address_line1
		$billing_address_line2=$BillingAddress->address_line2;//$orderData->bill_address_line2
		$billing_city=$BillingAddress->city;//$orderData->bill_city
		$billing_state=$BillingAddress->state;//$orderData->bill_state
		$billing_country=$BillingAddress->country;//$orderData->bill_country
		$billing_pincode=$BillingAddress->pincode;//$orderData->bill_pincode

		$ship_address_line1=$ShippingAddress->address_line1;//$orderData->ship_address_line1
		$ship_address_line2=$ShippingAddress->address_line2;//$orderData->ship_address_line2
		$ship_city=$ShippingAddress->city;//$orderData->ship_city
		$ship_state=$ShippingAddress->state;//$orderData->ship_state
		$ship_country=$ShippingAddress->country;//$orderData->ship_country
		$ship_pincode=$ShippingAddress->pincode;//$orderData->ship_pincode
		if($billDate && !empty($billDate)){
			$invoice_date=date(strtotime($billDate));
		}else{
			$invoice_date=time();
		}

		//get data by user id
		$invoice_due_date='';
		//$orderUserType; exit();
		$invoice_type=0;
		$invoice_generate=0;
		$emailSend=0;
		// customer if guest
		$customer_company='';
		$customer_gst_no='';
		$shipping_charges=$orderData->shipping_amount;
		$voucher_amount=$orderData->voucher_amount;
		$payment_charges=$orderData->payment_final_charge;
		//new add invoice
		$customer_email=$orderData->customer_email;
		$bill_customer_email=$orderData->customer_email;
		$bill_customer_id=$orderData->customer_id;

		// updated 10-08-2021
		// $orderData->invoice_self=0;
		if($orderData->invoice_self==1){
			if($orderUserType=='guest'){
				$payment_term='';
				$invoice_due_date=$invoice_date;
				$emailSend=1;
				$invoice_generate=1;
			}else{
				$customers_invoiceData=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$customer_id),'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				if(isset($customers_invoiceData) && !empty($customers_invoiceData)){
					$invoice_type=$customers_invoiceData->invoice_type;
					$payment_term=$customers_invoiceData->payment_term;
					$invoice_to_type=$customers_invoiceData->invoice_to_type;
					// if($customers_invoiceData->invoice_type==1){
						$invoice_generate=1;
						$emailSend=1;
					// }
				}else{
					$invoice_generate=1;
					$emailSend=1;
					$payment_term='';
					$invoice_due_date=$invoice_date;
					// alternate email id

				}
				if($invoice_date && $payment_term > 0){
					$dateAdd=date(DATE_PIC_FM,$invoice_date); // invoice due date
					$due_date=date('Y-m-d', strtotime($dateAdd. ' + '.$payment_term.' days'));
					$invoice_due_date=date(strtotime($due_date));
				}else{
					$payment_term='';$invoice_due_date=$invoice_date;
				}

				// bill customer data
				$bill_customer_company_name_gst = $this->CommonModel->getSingleShopDataByID('customers',array('id'=>$bill_customer_id),'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
				if(isset($bill_customer_company_name_gst)){
					$customer_name=$bill_customer_company_name_gst->customer_name;
					$customer_company=$bill_customer_company_name_gst->company_name;
					$customer_gst_no=$bill_customer_company_name_gst->gst_no;
				}
				//end new

			}

		}else{
			// customer type
			if($orderUserType=='guest'){
				$invoice_generate=1;
				$payment_term='';
				$invoice_due_date=$invoice_date;
				$customVariables_alternative_email_id=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
				if(isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value){
					$customer_email_alternate_shop=$customVariables_alternative_email_id->value;
					$customer_email_data_shop = $this->CommonModel->getSingleShopDataByID('customers',array('id'=>$customer_email_alternate_shop),'email_id');
					$bill_customer_email=$customer_email_data_shop->email_id;
					$bill_customer_id=$customer_email_alternate_shop;
					// $emailSend=1;
				}
			}else{
				$customers_invoiceData=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$customer_id),'invoice_type,payment_term,invoice_to_type,alternative_email_id');
				// print_r($customers_invoiceData);exit();
				// if(isset($customers_invoiceData) && $customers_invoiceData->invoice_type==1){
				if(isset($customers_invoiceData) && !empty($customers_invoiceData)){
					$invoice_type=$customers_invoiceData->invoice_type;
					$payment_term=$customers_invoiceData->payment_term;
					$customer_email_alternate='';
					$invoice_to_type=$customers_invoiceData->invoice_to_type;
					if($invoice_to_type!=0){
						$customer_email_alternate=$customers_invoiceData->alternative_email_id;
					}
					// if($customers_invoiceData->invoice_type==1){
						$invoice_generate=1;
						$emailSend=1;
					// }
				}else{
					$invoice_generate=1;
					$payment_term=0;
					// alternate
						$customVariables_alternative_email_id=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
						if(isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value){
							$customer_email_alternate_shop=$customVariables_alternative_email_id->value;
							$customer_email_data_shop = $this->CommonModel->getSingleShopDataByID('customers',array('id'=>$customer_email_alternate_shop),'email_id');
							$bill_customer_email=$customer_email_data_shop->email_id;
							$bill_customer_id=$customer_email_alternate_shop;
							// $emailSend=1;
						}
					// end alternate
				}

				if($invoice_date && $payment_term > 0){
					$dateAdd=date(DATE_PIC_FM,$invoice_date); // invoice due date
					$due_date=date('Y-m-d', strtotime($dateAdd. ' + '.$payment_term.' days'));
					$invoice_due_date=date(strtotime($due_date));
				}else{
					$payment_term='';$invoice_due_date=$invoice_date;
				}
				if(isset($invoice_to_type) && $invoice_to_type==1){ //invoice type check
					if($customer_email_alternate){
						//$customer_email_alternate=0;
						$customer_email_data = $this->CommonModel->getSingleShopDataByID('customers',array('id'=>$customer_email_alternate),'email_id');
						if(isset($customer_email_data) && !empty($customer_email_data)){
						// new add
							$bill_customer_email=$customer_email_data->email_id;
							$bill_customer_id=$customer_email_alternate;
							$customers_invoiceData_alt=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$customer_email_alternate),'invoice_type,payment_term,invoice_to_type,alternative_email_id');
							if(isset($customers_invoiceData_alt) && !empty($customers_invoiceData_alt)){
								$invoice_type=$customers_invoiceData_alt->invoice_type;
								$payment_term=$customers_invoiceData_alt->payment_term;
								$invoice_to_type=$customers_invoiceData_alt->invoice_to_type;
								// if($customers_invoiceData_alt->invoice_type==1){
									$invoice_generate=1;
									$emailSend=1;
								// }
							}else{
								$invoice_generate=1;
								$payment_term=0;
								$customer_email_alternate='';
							}


							// $emailSend=1;
						}else{
							$customVariables_alternative_email_id=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
							if(isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value){
								$customer_email_alternate_shop=$customVariables_alternative_email_id->value;
								$customer_email_data_shop = $this->CommonModel->getSingleShopDataByID('customers',array('id'=>$customer_email_alternate_shop),'email_id');
								$bill_customer_email=$customer_email_data_shop->email_id;
								$bill_customer_id=$customer_email_alternate_shop;
								// $emailSend=1;
								$customers_invoiceData_alt_cusVar=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$customer_email_alternate_shop),'invoice_type,payment_term,invoice_to_type,alternative_email_id');
								if(isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)){
									$invoice_type=$customers_invoiceData_alt_cusVar->invoice_type;
									$payment_term=$customers_invoiceData_alt_cusVar->payment_term;
									$invoice_to_type=$customers_invoiceData_alt_cusVar->invoice_to_type;
									// if($customers_invoiceData_alt_cusVar->invoice_type==1){
										$invoice_generate=1;
										$emailSend=1;
									// }
								}else{
									$invoice_generate=1;
									$payment_term=0;
									$customer_email_alternate='';
								}

							}
						}
					}else{
						$customVariables_alternative_email_id=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'webshopcust_def_inv_altemail'),'value');
						if(isset($customVariables_alternative_email_id) && $customVariables_alternative_email_id->value){
							$customer_email_alternate_shop=$customVariables_alternative_email_id->value;
							$customer_email_data_shop = $this->CommonModel->getSingleShopDataByID('customers',array('id'=>$customer_email_alternate_shop),'email_id');
							$bill_customer_email=$customer_email_data_shop->email_id;
							$bill_customer_id=$customer_email_alternate_shop;
							// $emailSend=1;
							$customers_invoiceData_alt_cusVar=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$customer_email_alternate_shop),'invoice_type,payment_term,invoice_to_type,alternative_email_id');
							if(isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)){
								$invoice_type=$customers_invoiceData_alt_cusVar->invoice_type;
								$payment_term=$customers_invoiceData_alt_cusVar->payment_term;
								$invoice_to_type=$customers_invoiceData_alt_cusVar->invoice_to_type;
								// if($customers_invoiceData_alt_cusVar->invoice_type==1){
									$invoice_generate=1;
									$emailSend=1;
								// }
							}else{
								$invoice_generate=1;
								$payment_term=0;
								$customer_email_alternate='';
							}

						}
					}

					if($bill_customer_id){
						$Default_BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('customers_address',array('customer_id'=>$bill_customer_id,'is_default'=>1),'');
						if(isset($Default_BillingAddress)){
							$bill_customer_first_name=$Default_BillingAddress->first_name;
							$bill_customer_last_name=$Default_BillingAddress->last_name;
							// $bill_customer_email=$orderData->email;
							$billing_address_line1=$Default_BillingAddress->address_line1;//$orderData->bill_address_line1
							$billing_address_line2=$Default_BillingAddress->address_line2;//$orderData->bill_address_line2
							$billing_city=$Default_BillingAddress->city;//$orderData->bill_city
							$billing_state=$Default_BillingAddress->state;//$orderData->bill_state
							$billing_country=$Default_BillingAddress->country;//$orderData->bill_country
							$billing_pincode=$Default_BillingAddress->pincode;//$orderData->bill_pincode
						}else{
							$bill_BillingAddress=$this->WebshopOrdersModel->getSingleDataByID('customers_address',array('customer_id'=>$bill_customer_id,''),'');
							if(isset($bill_BillingAddress)){
								$bill_customer_first_name=$bill_BillingAddress->first_name;
								$bill_customer_last_name=$bill_BillingAddress->last_name;
								// $bill_customer_email=$orderData->email;
								$billing_address_line1=$bill_BillingAddress->address_line1;//$orderData->bill_address_line1
								$billing_address_line2=$bill_BillingAddress->address_line2;//$orderData->bill_address_line2
								$billing_city=$bill_BillingAddress->city;//$orderData->bill_city
								$billing_state=$bill_BillingAddress->state;//$orderData->bill_state
								$billing_country=$bill_BillingAddress->country;//$orderData->bill_country
								$billing_pincode=$bill_BillingAddress->pincode;//$orderData->bill_pincode
							}
						}
					}
				}elseif(isset($customer_email_alternate_shop) &!empty($customer_email_alternate_shop)){ // new add 16-08-2021
			    	$customers_invoiceData_alt_cusVar=$this->CommonModel->getSingleShopDataByID('customers_invoice',array('customer_id'=>$customer_email_alternate_shop),'invoice_type,payment_term,invoice_to_type,alternative_email_id');
					if(isset($customers_invoiceData_alt_cusVar) && !empty($customers_invoiceData_alt_cusVar)){
						$invoice_type=$customers_invoiceData_alt_cusVar->invoice_type;
						$payment_term=$customers_invoiceData_alt_cusVar->payment_term;
						$invoice_to_type=$customers_invoiceData_alt_cusVar->invoice_to_type;
						if($customers_invoiceData_alt_cusVar->invoice_type==1){
							$invoice_generate=1;
							$emailSend=1;
						}
					}else{
						/*$invoice_generate=1;
						$payment_term=0;
						$customer_email_alternate='';*/
					}

			    } //end invoice type check //end invoice type check //end invoice type check

				// bill customer data
				$bill_customer_company_name_gst = $this->CommonModel->getSingleShopDataByID('customers',array('id'=>$bill_customer_id),'company_name,gst_no,CONCAT(first_name, " ", last_name) as customer_name');
				if(isset($bill_customer_company_name_gst)){
					$customer_name=$bill_customer_company_name_gst->customer_name;
					$customer_company=$bill_customer_company_name_gst->company_name;
					$customer_gst_no=$bill_customer_company_name_gst->gst_no;
				}

			}
		}// end invoice_self
		// end updated 10-08-2021
		if(isset($payment_term) && $payment_term!=''){
			$invoice_term=$payment_term;
		}else{
			$invoice_term=0;
		}


		/*shop_flag=1 only zwear invoice date set*/
		$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
					$webshop_fbc_user_id=$FbcUser->fbc_user_id;
		if(isset($FbcUser) && $FbcUser->shop_flag==1){
			// $orderData->increment_id;
			// echo $FbcUser->shop_flag;
			// $orderData->increment_id =1020;
			if($orderData->increment_id >=1020 && $orderData->increment_id <=1033){
				$custom_dateAdd='31-07-2021'; // invoice due date
				$due_date_custom=date('Y-m-d', strtotime($custom_dateAdd));
				// $invoice_date
				// invoice_due_date
				//$invoice_due_date=date(strtotime($due_date_custom));
				$invoice_date=date(strtotime($due_date_custom));
				if($invoice_date && $payment_term > 0){
					$dateAdd=date(DATE_PIC_FM,$invoice_date); // invoice due date
					$due_date=date('Y-m-d', strtotime($dateAdd. ' + '.$payment_term.' days'));
					$invoice_due_date=date(strtotime($due_date));
				}else{
					$payment_term='';$invoice_due_date=$invoice_date;
				}

				//echo date(DATE_PIC_FM,$invoice_due_date);
			}
		}
		/*End shop_flag=1 only zwear invoice date set*/


		// insert invoicing data
		$insertinvoicingdataitem=array(
				'invoice_no'=>$invoice_no,
				'customer_first_name'=>$customer_first_name,
				'customer_last_name'=>$customer_last_name,
				'customer_id'=>$customer_id,
				'customer_email'=>$customer_email,
				// 'shop_id'=>'',
				// 'shop_webshop_name'=>$shop_webshop_name,
				// 'shop_company_name'=>$shop_company_name,
				'shop_gst_no'=>$shop_gst_no,
				'bill_customer_first_name'=>$customer_name,
				'bill_customer_last_name'=>$customer_name,
				'bill_customer_company_name'=>$customer_company,
				'bill_customer_gst_no'=>$customer_gst_no,
				// 'bill_customer_last_name'=>$customer_name,
				// 'bill_customer_id'=>$fbc_user_id_order,
				'bill_customer_id'=>$bill_customer_id, // new $bill_customer_email$bill_customer_id
				'bill_customer_email'=>$bill_customer_email, // new
				'invoice_order_nos'=>$order_id,
				'invoice_order_type'=>$invoice_order_type,
				'invoice_subtotal'=>$invoice_subtotal,
				'invoice_tax'=>$invoice_tax,
				'invoice_grand_total'=>$invoice_grand_total,
				'billing_address_line1'=>$billing_address_line1,
				'billing_address_line2'=>$billing_address_line2,
				'billing_city'=>$billing_city,
				'billing_state'=>$billing_state,
				'billing_country'=>$billing_country,
				'billing_pincode'=>$billing_pincode,
				'ship_address_line1'=>$ship_address_line1,
				'ship_address_line2'=>$ship_address_line2,
				'ship_city'=>$ship_city,
				'ship_state'=>$ship_state,
				'ship_country'=>$ship_country,
				'ship_pincode'=>$ship_pincode,
				'invoice_date'=>$invoice_date,
				'invoice_due_date'=>$invoice_due_date,
				'invoice_term'=>$invoice_term,
				'payment_charges'=>$payment_charges,
				'voucher_amount'=>$voucher_amount,
				'shipping_charges'=>$shipping_charges,
				// 'created_by'=>$fbc_user_id,
				'created_at'=>time(),
				'ip'=>$_SERVER['REMOTE_ADDR']
		);
		/*print_r($insertinvoicingdataitem);
		exit();*/
		if($invoice_generate==1){ //invoice generate 1-yes, 0-no
			$invoicing_one=$this->WebshopOrdersModel->insertData('invoicing',$insertinvoicingdataitem);
		//}
		//$invoicing_one='35';
		// print_r($invoicing_one);exit();
		if($invoicing_one){
			//update custom_variable
			$invoice_no_update=array('value'=>$invoice_next_no);
			$where_invoice_arr=array('identifier'=>'invoice_next_no');

			$this->WebshopOrdersModel->updateData('custom_variables',$where_invoice_arr,$invoice_no_update);
			// sales order updated
			$invoice_sales_order=array('invoice_id'=>$invoicing_one,'invoice_date'=>$invoice_date,'invoice_flag'=>1);
			$where_sales_order_arr=array('order_id'=>$order_id);
			$this->WebshopOrdersModel->updateData('sales_order',$where_sales_order_arr,$invoice_sales_order);
		   // send invoice email and save invoice pdf
			$pdfGeneratePdfName=$this->generatePdfWebshop($invoicing_one); // save pdf
			//send email with attachment
			if($pdfGeneratePdfName){
				//update invoicing
				$invoiceFileName=array('invoice_file'=>$pdfGeneratePdfName);
				$where_invoice_filename_arr=array('id'=>$invoicing_one);
				$this->WebshopOrdersModel->updateData('invoicing',$where_invoice_filename_arr,$invoiceFileName);

				// sent email
				//----------------Send Email to invoice with attchmnet--------------------
					if($emailSend==1){ // email check 1-send 0-not send
						// invoice send date add
						if($customer_id >0 ){
							$last_invoice_send_date=array('last_invoice_sent_date'=>$invoice_date);
							$where_invoice_send_email_arr=array('customer_id'=>$customer_id);
							$this->WebshopOrdersModel->updateData('customers_invoice',$where_invoice_send_email_arr,$last_invoice_send_date);
						}

						//$fbc_user_id	=	$this->session->userdata('LoginID');
						$shop_id		=	$this->session->userdata('ShopID');
						$Ishop_owner=$this->CommonModel->getShopOwnerData($shop_id);
						$Iwebshop_details=$this->CommonModel->get_webshop_details($shop_id);
						$Ishop_name=$Ishop_owner->org_shop_name;
						$ItemplateId ='system-invoice';
						 //$Ito = 'rajesh@bcod.co.in';
						$Ito = $bill_customer_email;
						$site_logo = '';
						if(isset($Iwebshop_details)){
						 $Ishop_logo = $this->encryption->decrypt($Iwebshop_details['site_logo']);
						}
						else{
							 $Ishop_logo = '';
						}

						$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);

						$Isite_logo =  '<a href="'.base_url().'" style="color:#1E7EC8;">
							<img alt="'.$Ishop_name.'" border="0" src="'.$Ishop_logo.'" style="max-width:200px" />
						</a>';
						$Iusername = $customer_name;
						$ITempVars = array();
						$IDynamicVars = array();

						$ITempVars = array("##OWNER##" ,"##INVOICENO##","##WEBSHOPNAME##");
						$IDynamicVars   = array($Iusername,$invoice_no,$Ishop_name);
						$ICommonVars=array($Isite_logo, $Ishop_name, $invoice_no);
						$attachment = get_s3_url('invoices/' . $pdfGeneratePdfName, $shop_id);
						//email function

						$mailSent_invoice = $this->WebshopOrdersModel->sendInvoiceHTMLEmail($Ito, $ItemplateId, $ITempVars,$IDynamicVars,$ICommonVars,$attachment);


				}else{
					// invoice generate
					if($invoice_generate==1){
						// invoice updated flag
						$invoiceUpdate=array('internal_invoice_flag'=>1);
						$whereInvoiceArr=array('id'=>$invoicing_one);
						$invoioceUpdated=$this->WebshopOrdersModel->updateData('invoicing',$whereInvoiceArr,$invoiceUpdate);
					}

				}

			}
		}

	  }


	}

	function generatePdfWebshop($invoiceID){
		$invoice_id=$invoiceID;
		//echo $invoice_id;exit();
		// $invoice_id="4";
		// $order_id="58";
		$data['invoicedata']=$this->WebshopOrdersModel->get_invoicedata_by_id($invoice_id);


		// Shop Data
		$data['custom_variables']=$this->CommonModel->get_custom_variables();

		//getSingleDataByID
		$shop_id = $this->session->userdata('ShopID');
		$data['shop_id'] = $this->session->userdata('ShopID');
		$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($data['shop_id']);
		$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		if($data['user_details']->parent_id == 0)
		{
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
		}else{
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
		}

		// dom pdf
		$this->load->library('Pdf_dom');
		//$this->load->view('invoice/b2b/invoice_format',$data);
		// $htmldata =$this->load->view('invoice/webshop/invoice_format',$data,true);//testing
		$htmldata =$this->load->view('invoice/webshop/invoice_format',$data,true);//live
		//$invoiceFileName=$this->pdf_dom->createbyshopTesting($htmldata,$invoiceID,$data['shop_id']);
		$invoiceFileName=$this->pdf_dom->createbyshop($htmldata,$invoiceID,$data['shop_id']);
		return $invoiceFileName;
	}
	/*end webshop invoice generate*/


	// b2b generate pdf
	/*start b2b invoice generate*/
	function invoiceGenerateB2b($order_id,$billDate){
		$b2borderData=$this->B2BOrdersModel->get_b2border_invoicing_data($order_id);
		$invoice_order_id=$order_id;
		$shop_id=$this->session->userdata('ShopID');
		$fbc_user_id=$this->session->userdata('LoginID');
		if($b2borderData){ // start invoice
			$shopName=$b2borderData->org_shop_name;
			// invoice type
			//if($b2borderData->invoice_type==1){
				//$invoiceType="Invoice Per Order";
				// invoice no and next no
				$customVariables_invoice_prefix=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'invoice_prefix'),'value');
				$customVariables_invoice_no=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'invoice_next_no'),'value');
				if($customVariables_invoice_prefix->value && $customVariables_invoice_no->value){
					$invoiceNo=$customVariables_invoice_no->value;
					$invoice_next_no=$invoiceNo+1;
					$invoice_no=$customVariables_invoice_prefix->value.$invoiceNo;
				}elseif($customVariables_invoice_prefix->value){
					// $invoiceNo='2';
					$invoiceNo='1';
					$invoice_next_no=$invoiceNo+1;
					$invoice_no=$customVariables_invoice_prefix->value.$invoiceNo;
					# code...
				}elseif ($customVariables_invoice_no->value) {
					$invoiceNo=$customVariables_invoice_no->value;
					$invoice_next_no=$invoiceNo+1;
					$invoice_no='INV'.$invoiceNo;
				}
				// custom_variable table data updated
				$invoice_no=$invoice_no;
				$invoice_next_no=$invoice_next_no;

				// invoice table insert

				$invoice_order_type='2'; //1-Webshop;  2-B2Webshop;
				$shop_id=$shop_id;
				$b2bshop_id=$b2borderData->shop_id;
				$customer_name=$b2borderData->customer_name;
				$shop_webshop_name=$b2borderData->org_shop_name;
				$shop_gst_no=$b2borderData->gst_no;
				$shop_company_name=$b2borderData->company_name;
				$customer_email=$b2borderData->email;
				$invoice_subtotal=$b2borderData->subtotal;
				$invoice_tax=$b2borderData->subtotal;
				$invoice_grand_total=$b2borderData->subtotal;
				$bill_customer_first_name=$b2borderData->customer_name;
				$bill_customer_last_name='';
				$bill_customer_id='';
				$bill_customer_email=$b2borderData->email;
				$billing_address_line1=$b2borderData->bill_address_line1;
				$billing_address_line2=$b2borderData->bill_address_line2;
				$billing_city=$b2borderData->bill_city;
				$billing_state=$b2borderData->bill_state;
				$billing_country=$b2borderData->bill_country;
				$billing_pincode=$b2borderData->bill_pincode;
				$ship_address_line1=$b2borderData->ship_address_line1;
				$ship_address_line2=$b2borderData->ship_address_line2;
				$ship_city=$b2borderData->ship_city;
				$ship_state=$b2borderData->ship_state;
				$ship_country=$b2borderData->ship_country;
				$ship_pincode=$b2borderData->ship_pincode;
				if($billDate && !empty($billDate)){
					$invoice_date=date(strtotime($billDate));
				}else{
					$invoice_date=time();
				}


				//get data by user id
				$payment_term=0;
				$invoice_due_date=$invoice_date;
				$payment_term=0;
				if($b2borderData->fbc_user_id){
					$b2b_customers_invoiceData=$this->CommonModel->getSingleShopDataByID('b2b_customers_invoice',array('customer_id'=>$b2borderData->fbc_user_id),'invoice_type,payment_term');
					if($b2b_customers_invoiceData){
						$invoice_type=$b2b_customers_invoiceData->invoice_type;
						$payment_term=$b2b_customers_invoiceData->payment_term;
					}

					if($invoice_date && $payment_term > 0){
						//$daysAdd='+ '.$payment_term.'days';
						$dateAdd=date(DATE_PIC_FM,$invoice_date); // invoice due date
						//$due_date=date('Y-m-d', strtotime($dateAdd. $daysAdd));
						$due_date=date('Y-m-d', strtotime($dateAdd. ' + '.$payment_term.' days'));
						$invoice_due_date=date(strtotime($due_date));
					}else{

					}

				}

				$invoice_term=$payment_term;

				$fbc_user_id_order=$b2borderData->fbc_user_id; //users and users_shop
				$email_sent_flag='';

				// insert invoicing data
				$insertinvoicingdataitem=array(
						'invoice_no'=>$invoice_no,
						'customer_first_name'=>$customer_name,
						'customer_id'=>$fbc_user_id_order,
						'customer_email'=>$customer_email,
						'shop_id'=>$b2bshop_id,
						'shop_webshop_name'=>$shop_webshop_name,
						'shop_company_name'=>$shop_company_name,
						'shop_gst_no'=>$shop_gst_no,
						'bill_customer_first_name'=>$customer_name,
						// 'bill_customer_last_name'=>$customer_name,
						'bill_customer_id'=>$fbc_user_id_order,
						'bill_customer_email'=>$customer_email,
						'invoice_order_nos'=>$invoice_order_id,
						'invoice_order_type'=>$invoice_order_type,
						'invoice_subtotal'=>$invoice_subtotal,
						'invoice_tax'=>$invoice_tax,
						'invoice_grand_total'=>$invoice_grand_total,
						'billing_address_line1'=>$billing_address_line1,
						'billing_address_line2'=>$billing_address_line2,
						'billing_city'=>$billing_city,
						'billing_state'=>$billing_state,
						'billing_country'=>$billing_country,
						'billing_pincode'=>$billing_pincode,
						'ship_address_line1'=>$ship_address_line1,
						'ship_address_line2'=>$ship_address_line2,
						'ship_city'=>$ship_city,
						'ship_state'=>$ship_state,
						'ship_country'=>$ship_country,
						'ship_pincode'=>$ship_pincode,
						'invoice_date'=>$invoice_date,
						'invoice_due_date'=>$invoice_due_date,
						'invoice_term'=>$invoice_term,
						'created_by'=>$fbc_user_id,
						'created_at'=>time(),
						'ip'=>$_SERVER['REMOTE_ADDR']
				);

				$invoicing_one=$this->B2BOrdersModel->insertData('invoicing',$insertinvoicingdataitem);

				if($invoicing_one){
					// b2b order updated
					$invoice_b2b_order=array('invoice_id'=>$invoicing_one,'invoice_date'=>$invoice_date,'invoice_flag'=>1);
					$where_b2b_order_arr=array('order_id'=>$order_id);
					$this->B2BOrdersModel->updateData('b2b_orders',$where_b2b_order_arr,$invoice_b2b_order);

					//update custom_variable
					$invoice_no_update=array('value'=>$invoice_next_no);
					$where_invoice_arr=array('identifier'=>'invoice_next_no');

					$this->B2BOrdersModel->updateData('custom_variables',$where_invoice_arr,$invoice_no_update);
				   // send invoice email and save invoice pdf
					$pdfGeneratePdfName=$this->generatePdf($invoicing_one); // save pdf
					//send email with attachment
					if($pdfGeneratePdfName){
						//update invoicing
						$invoiceFileName=array('invoice_file'=>$pdfGeneratePdfName);
						$where_invoice_filename_arr=array('id'=>$invoicing_one);
						$this->B2BOrdersModel->updateData('invoicing',$where_invoice_filename_arr,$invoiceFileName);

						// sent email
				/*----------------Send Email to invoice with attchmnet--------------------*/
						$Ishop_owner=$this->CommonModel->getShopOwnerData($shop_id);
						$Iwebshop_details=$this->CommonModel->get_webshop_details($shop_id);
						$Ishop_name=$Ishop_owner->org_shop_name;
						$ItemplateId ='system-invoice';
						// $Ito = 'rajesh@bcod.co.in';
						$Ito = $customer_email;
						$site_logo = '';
						if(isset($Iwebshop_details)){
						 $Ishop_logo = $this->encryption->decrypt($Iwebshop_details['site_logo']);
						}
						else{
							 $Ishop_logo = '';
						}
						$Ishop_logo = get_s3_url($Ishop_logo, $shop_id);
						$Isite_logo =  '<a href="'.base_url().'" style="color:#1E7EC8;">
							<img alt="'.$Ishop_name.'" border="0" src="'.$Ishop_logo.'" style="max-width:200px" />
						</a>';
						$Iusername = $customer_name;
						$ITempVars = array();
						$IDynamicVars = array();

						$ITempVars = array("##OWNER##" ,"##INVOICENO##","##WEBSHOPNAME##");
						$IDynamicVars   = array($Iusername,$invoice_no,$Ishop_name);
						$ICommonVars=array($Isite_logo, $Ishop_name, $invoice_no);
						$attachment = get_s3_url('invoices/' . $pdfGeneratePdfName, $shop_id);
						//email function
						$mailSent_invoice = $this->B2BOrdersModel->sendInvoiceHTMLEmail($Ito, $ItemplateId, $ITempVars,$IDynamicVars,$ICommonVars,$attachment);
						/*$mailSent_invoice1 = $this->B2BOrdersModel->sendInvoiceHTMLEmail('rajesh@bcod.co.in', $ItemplateId, $ITempVars,$IDynamicVars,$ICommonVars,$attachment);
						$mailSent_invoice1 = $this->B2BOrdersModel->sendInvoiceHTMLEmail('usha@bcod.co.in', $ItemplateId, $ITempVars,$IDynamicVars,$ICommonVars,$attachment);*/

						if($mailSent_invoice && $fbc_user_id_order > 0 ){
							$last_invoice_send_date=array('last_invoice_sent_date'=>$invoice_date);
							$where_invoice_send_email_arr=array('customer_id'=>$fbc_user_id_order);
							$this->B2BOrdersModel->updateData('customers_invoice',$where_invoice_send_email_arr,$last_invoice_send_date);
						}
					}
				   //
				}


			//}
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


	function generatePdf($invoiceID){
		$invoice_id=$invoiceID;
		$data['invoicedata']=$this->B2BOrdersModel->get_invoicedata_by_id($invoice_id);

		// Shop Data
		$data['custom_variables']=$this->CommonModel->get_custom_variables();

		//getSingleDataByID

		$data['shop_id'] = $this->session->userdata('ShopID');
		// $data['shop_id'] = $shop_id = $this->session->userdata('ShopID');
		$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($data['shop_id']);
		$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		if($data['user_details']->parent_id == 0)
		{
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
		}else{
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
		}

		// dom pdf
		$this->load->library('Pdf_dom');
		//$this->load->view('invoice/b2b/invoice_format',$data);
		$htmldata =$this->load->view('invoice/b2b/invoice_format_checkbox',$data,true);
		// $invoiceFileName=$this->pdf_dom->create($htmldata,$invoiceID);
		//$invoiceFileName=$this->pdf_dom->createbyshopTesting($htmldata,$invoiceID,$data['shop_id']);
		 $invoiceFileName=$this->pdf_dom->createbyshop($htmldata,$invoiceID,$data['shop_id']);
		return $invoiceFileName;
	}


	/*testing tax not including*/
	/*function generatePdf_tax_test($invoiceID){
		$invoice_id=$invoiceID;
		$data['invoicedata']=$this->B2BOrdersModel->get_invoicedata_by_id($invoice_id);

		// Shop Data
		$data['custom_variables']=$this->CommonModel->get_custom_variables();

		//getSingleDataByID

		$data['shop_id'] = $this->session->userdata('ShopID');
		// $data['shop_id'] = $shop_id = $this->session->userdata('ShopID');
		$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($data['shop_id']);
		$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		if($data['user_details']->parent_id == 0)
		{
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
		}else{
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
		}

		// dom pdf
		$this->load->library('Pdf_dom');
		//$this->load->view('invoice/b2b/invoice_format',$data);
		$htmldata =$this->load->view('invoice/b2b/invoice_format_checkbox_test',$data,true);
		// $invoiceFileName=$this->pdf_dom->create($htmldata,$invoiceID);
		//$invoiceFileName=$this->pdf_dom->createbyshopTesting($htmldata,$invoiceID,$data['shop_id']);
		 $invoiceFileName=$this->pdf_dom->createbyshop_tax_test($htmldata,$invoiceID,$data['shop_id']);

		$invoiceFileName1=array('invoice_file'=>$invoiceFileName);
		$where_invoice_filename_arr=array('id'=>$invoice_id);
		$this->B2BOrdersModel->updateData('invoicing',$where_invoice_filename_arr,$invoiceFileName1);

		return $invoiceFileName;
	}*/
	/*end testing tax not including*/

	function sales_report(){
		$data['PageTitle']='Accounting - Sales Report';
		$data['side_menu']='accounting';
		$data['current_tab']='Sales Report';
		/*$access_page=$this->CommonModel->page_access();
		if(isset($access_page->acc_inv_flag) && $access_page->acc_inv_flag==1){
			$this->load->view('accounting/order/b2Webshoplist',$data);
		}else{
			$this->load->view('accounting/order/access',$data);
		}*/
		$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
		$data['shop_flag']=$FbcUser->shop_flag;
		if($FbcUser->shop_flag!=2){
			redirect(base_url().'accounting/webshop');
		}
		$this->load->view('accounting/order/sales_report',$data);

	}

	function postGenerateReport(){
		if(isset($_POST) && $_POST['fromDate']!='' && $_POST['toDate']!=''){
			$fromDate_get=$_POST['fromDate'];
			$toDate_get=$_POST['toDate'];
			$fromDate=date(strtotime($fromDate_get));
			$toDate=date(strtotime($toDate_get));
			$sales_data_get =$this->AccountingWebshopOrdersModel->getSalesReportCSVImport($fromDate,$toDate);

			if(isset($sales_data_get) && count($sales_data_get) > 0){
				$this->downloadSalesReportcsv($fromDate_get,$toDate_get);
			}else{
				/*echo json_encode(array('flag' => 1, 'msg' => "No records found."));
				exit;*/
				//redirect(base_url().'accounting/salesreport');
				$data['fromDate']=$fromDate_get;
				$data['toDate']=$toDate_get;
				$data['PageTitle']='Accounting - Sales Report';
				$data['side_menu']='accounting';
				$data['current_tab']='Sales Report';
				$data['errorMsg']='No records found.';
				$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
				$data['shop_flag']=$FbcUser->shop_flag;
				$this->load->view('accounting/order/sales_report',$data);
			}

			// echo json_encode(array('flag' => 1, 'msg' => "Sales report generate success"));
			// exit;
		}else{
			$data['fromDate']=$_POST['fromDate'];
			$data['toDate']=$_POST['toDate'];
			$data['PageTitle']='Accounting - Sales Report';
			$data['side_menu']='accounting';
			$data['current_tab']='Sales Report';
			$data['errorMsg']='Please seclect date.';
			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$this->session->userdata('ShopID')),'');
			$data['shop_flag']=$FbcUser->shop_flag;
			$this->load->view('accounting/order/sales_report',$data);
			/*echo json_encode(array('flag' => 0, 'msg' => "Please seclect date"));
			exit;*/
		}
	}

	// download
	//public function downloadSalesReportcsv()
	public function downloadSalesReportcsv($fromDate,$toDate)
	{

		// $fromDate='20-08-2021';
		// $toDate='14-09-2021';
		if($fromDate && $toDate){
			$fromDate=date(strtotime($fromDate));
			$toDate=date(strtotime($toDate));
		}else{
			$fromDate='';
			$toDate='';
		}


		//csv header
		$sales_report_export_header = array("Order #","Order Date","Order Complete Date","HSN Code","Customer Name","Pincode","State","Grand Total","Subtotal","Net Amt for 0%","GST 0%","Net Amt for 5%","GST 5%","Net Amt for 12%","GST 12%","Net Amt for 18%","GST 18%","Net Amt for Other%","GST Other%");


		// $invoing_data =$this->AccountingWebshopOrdersModel->getinvoicingForCSVImport();// invoice table
		$sales_data =$this->AccountingWebshopOrdersModel->getSalesReportCSVImport($fromDate,$toDate);
		// echo
		// invoice_detail table
		if(isset($sales_data) && count($sales_data) > 0){

			$ExportValuesArr1=array();
			foreach ($sales_data as $key => $value) {
				$increment_id=$value['increment_id'];
				$order_date_data=$value['created_at'];
				$tracking_complete_date=$value['tracking_complete_date'];
				$coupon_code=$value['coupon_code'];
				$order_discount_percent=$value['discount_percent']; //b2b order percentage
				$order=$increment_id;
				$order_date='';
				if($order_date_data){
					$order_date=date(SIS_DATE_FM_WT,$order_date_data);
				}

				$order_complete_date='';
				if($tracking_complete_date){
					$order_complete_date=date(SIS_DATE_FM_WT,$tracking_complete_date);
				}


				//address table data
				$order_address=$this->CommonModel->getSingleShopDataByID('sales_order_address',array('order_id'=>$value['order_id'],'address_type'=>2),'pincode,state,first_name,last_name');

				$pincode=$order_address->pincode;
				$state=$order_address->state;
				$CustomerName=trim($order_address->first_name).' '.trim($order_address->last_name);
				// new price

				// gst
				$net_amt_0=0;
				$gst_0=0;
				$net_amt_5=0;
				$gst_5=0;
				$net_amt_12=0;
				$gst_12=0;
				$net_amt_18=0;
				$gst_18=0;
				$net_amt_other=0;
				$gst_other=0;
				$ItemTaxAmount=0;
				$ItemRowTaxAmount=0;
				$ItemTaxPercent=0;

				$sales_item_data =$this->AccountingWebshopOrdersModel->getSalesItemReportCSVImport($value['order_id']);

				$product_hsn_code='';
				foreach ($sales_item_data as $itemkey => $itemvalue) {

					/*hsn code*/
					$resultHsn=$this->CommonModel->getHsncodeIdByShopId($this->session->userdata('ShopID'));
					$hsnMainId='';
					if($resultHsn){
						$hsnMainId=$resultHsn->id;
					}

					// start hsn code

					$parent_product_id=$itemvalue['parent_product_id'];// product id
					if($hsnMainId){
						//echo 'no';exit();
						// if($Items->product_type=='conf-simple'){
						if($itemvalue['product_type']=='conf-simple'){
							$FinalproductID = $itemvalue['parent_product_id'];
						}else{
							$FinalproductID = $itemvalue['product_id'];
						}

						// hsn code
						$shopProductAttributes=$this->CommonModel->getSingleShopDataByID('products_attributes',array('product_id'=>$FinalproductID,'attr_id'=>$hsnMainId),'*');

						if($shopProductAttributes){
							$product_hsn_code.=$shopProductAttributes->attr_value.'|';
							// $product_hsn_code=$shopProductAttributes->attr_value;

						}


					}

					// end hsn code


					/*end hsn code*/


					$ItemTaxPercent=$itemvalue['tax_percent'];
					$ItemQty = $itemvalue['qty_ordered'];//old

					$price=$itemvalue['price'];

					$pro_price_excl_tax=0;
					$pro_price_incl_tax=0;


					if($coupon_code != "" && $price > 0.00 && $order_discount_percent > 0.00) {
						$pro_price_incl_tax = $price - ($price*$order_discount_percent)/100;
					} else {
						$pro_price_incl_tax = $price;
					}

					if($ItemTaxPercent > 0.00 && $pro_price_incl_tax > 0.00) {
						$pro_price_excl_tax = $pro_price_incl_tax / ((100+$ItemTaxPercent)/100);
						$ItemTaxAmount =  $pro_price_incl_tax - $pro_price_excl_tax;
						$ItemRowTaxAmount = ($ItemTaxAmount*$ItemQty);
						// $ItemRowTaxAmount = number_format($ItemTaxAmount*$ItemQty,2);
					} else{
						$pro_price_excl_tax = $pro_price_incl_tax;
						$ItemTaxPercent = 0;
						$ItemRowTaxAmount =0;

					}
					// echo $pro_price_excl_tax;
					// exit();


					if($ItemTaxPercent == 0.00){
						$net_amt_0 += $pro_price_excl_tax * $ItemQty;
						$gst_0 += $ItemTaxAmount * $ItemQty;
					}else if($ItemTaxPercent == 5.00 || $ItemTaxPercent == 5){
						$net_amt_5 += $pro_price_excl_tax * $ItemQty;
						$gst_5 += $ItemTaxAmount * $ItemQty;
						// $gst_5 += $ItemRowTaxAmount;
					}else if($ItemTaxPercent == 12.00 || $ItemTaxPercent == 12){
						$net_amt_12 += $pro_price_excl_tax * $ItemQty;
						$gst_12 += $ItemTaxAmount * $ItemQty;
						// $gst_12 += $ItemRowTaxAmount;
					}else if($ItemTaxPercent == 18.00 || $ItemTaxPercent == 18){
						$net_amt_18 += $pro_price_excl_tax * $ItemQty;
						$gst_18 +=$ItemTaxAmount * $ItemQty;
						// $gst_18 += $ItemRowTaxAmount;
					}else{
						$net_amt_other += $pro_price_excl_tax * $ItemQty;
						$gst_other+=$ItemTaxAmount * $ItemQty;
						// $gst_other+=$ItemRowTaxAmount;
					}



				} //item for each
				//exit();
				$all_gst=$gst_0 + $gst_5 + $gst_12 + $gst_18 + $gst_other;
				$all_net_amt=$net_amt_0 + $net_amt_5 + $net_amt_12 + $net_amt_18 + $net_amt_other;

				$subtotal=$all_net_amt;
				$grand_total=$subtotal + $all_gst;

				/*if (substr($product_hsn_code, -1, 1) == ',')
				{
				  $product_hsn_code = substr($product_hsn_code, 0, -1);
				}*/
				// strval
				$product_hsn_code_d='"'.$product_hsn_code.'"';
				$product_hsn_code_data=substr_replace($product_hsn_code ,"",-1);

				$SingleRow=array($order,$order_date,$order_complete_date,$product_hsn_code_data,$CustomerName,$pincode,$state,number_format($grand_total,2),number_format($subtotal,2),number_format($net_amt_0,2),number_format($gst_0,2),number_format($net_amt_5,2),number_format($gst_5,2),number_format($net_amt_12,2),number_format($gst_12,2),number_format($net_amt_18,2),number_format($gst_18,2),number_format($net_amt_other,2),number_format($gst_other,2));

				//print_r($SingleRow);


				$ExportValuesArr1[]=$SingleRow;
			}

			$filename_csv = 'CA-Sales-Order-Report-' . date('d-m-Y') . '.csv';
			// $filename_csv = 'CA-Sales-Order-Report-' . time() . '.csv';
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$filename_csv");
			header("Content-Type: application/csv; ");

			// file creation
			$file_csv = fopen('php://output', 'w');
			fputcsv($file_csv, $sales_report_export_header);
			if(isset($ExportValuesArr1) && count($ExportValuesArr1)>0){
				foreach ($ExportValuesArr1 as $readData_one) {
					fputcsv($file_csv, $readData_one);
				}
			}
			// print_r($file_csv);
			fclose($file_csv);
		}else{
			echo 'No records found.';
		}


	} //end download

	/*end sales report*/

	/* b2b issue generete pdf */
	function generatePdfB2BIssue($invoiceID){
		exit();
		$invoice_id=$invoiceID;
		$data['invoicedata']=$this->B2BOrdersModel->get_invoicedata_by_id($invoice_id);
		$invoice_file=$data['invoicedata']->invoice_file;

		//print_r($data['invoicedata']->invoice_file);exit();

		// Shop Data
		$data['custom_variables']=$this->CommonModel->get_custom_variables();

		//getSingleDataByID

		$data['shop_id'] = $this->session->userdata('ShopID');
		// $data['shop_id'] = $shop_id = $this->session->userdata('ShopID');
		$data['user_web_shop_details'] = $this->CommonModel->get_webshop_details($data['shop_id']);
		$data['user_details'] = $this->CommonModel->GetUserByUserId($_SESSION['LoginID']);
		if($data['user_details']->parent_id == 0)
		{
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->fbc_user_id);
		}else{
			$data['user_shop_details'] = $this->UserModel->getShopDetailsByfbcuserid($data['user_details']->parent_id);
		}

		// dom pdf
		$this->load->library('Pdf_dom');
		//$this->load->view('invoice/b2b/invoice_format',$data);
		// $htmldata =$this->load->view('invoice/b2b/invoice_format_checkbox',$data,true);
		$htmldata =$this->load->view('invoice/b2b/invoice_format_checkbox_issue',$data,true);
		 // $invoiceFileName=$this->pdf_dom->createbyshop($htmldata,$invoiceID,$data['shop_id']);
		$invoiceFileName=$this->pdf_dom->createbyshopIssue($htmldata,$invoice_file,$data['shop_id']);
		return $invoiceFileName;
	}
	/* end b2b issue generate pdf */

	function resendInvoice(){
		$invoice_id=$_POST['id'];
		$pdf = $_POST['invoice_file'];
		if(isset($pdf) && isset($invoice_id)){
			(new SendInvoiceByEmail())($invoice_id, $pdf);
		}
		echo json_encode(array('flag' => 1, 'msg' => "Resend invoice succesfully"));
		exit();
	}
}
