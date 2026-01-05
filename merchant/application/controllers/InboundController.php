<?php



use League\Csv\Reader;



defined('BASEPATH') OR exit('No direct script access allowed');



class InboundController extends CI_Controller {



	function __construct()

	{

		parent::__construct();



		$this->load->model('CommonModel');

        $this->load->model('InboundModel');

		$this->load->model('SellerProductModel');

		$this->load->model('B2BImportModel');



		if($this->session->userdata('LoginID')==''){

			redirect(base_url());

		}

		if(!empty($this->session->userdata('userPermission')) && !in_array('seller/database',$this->session->userdata('userPermission'))){ 

           redirect(base_url('dashboard'));  }

	}



	public function index()

	{



		$data['PageTitle']='Inbound List';

		$data['side_menu']='inbound';



		$this->load->view('seller/inbound/list',$data);

	}



	public function OpenApproveInbounndPopup()

	{

		if(isset($_POST['id'])){

			$data['id']=$_POST['id'];



			$View = $this->load->view('seller/inbound/inbound-approve-popup', $data, true);

			$this->output->set_output($View);

		}else{

			echo "error";exit;

		}

	}



	public function inbound_approve(){

		if(isset($_POST['id']))

		{

			$id=$_POST['id'];

			$inboundData= $this->CommonModel->getSingleShopDataByID('inbound',array('id'=>$id),'id,approved_at');

			if ($inboundData->approved_at=="") {



				$inb_update=array('approved_at'=>date('Y-m-d H:i:s'),'updated_at'=>time());

				$inb_where_arr=array('id'=>$id);

				$this->InboundModel->updateData('inbound',$inb_where_arr,$inb_update);



				$redirect = base_url('seller/inbound');

				$arrResponse  = array('status' =>200 ,'message'=>'Inbound Approved','redirect'=>$redirect);

				echo json_encode($arrResponse);exit;

			}else{

				$arrResponse  = array('status' =>400 ,'message'=>'Inbound already approved');

				echo json_encode($arrResponse);exit;

			}

		}else{

			$arrResponse  = array('status' =>400 ,'message'=>'Something went wrong!');

				echo json_encode($arrResponse);exit;

		}

	}



    public function add()

	{



		$data['PageTitle']='Inbound Add';

		$data['side_menu']='inbound';

		$shop_id		=	$this->session->userdata('ShopID');

		//echo $shop_id;

		$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);



		$this->load->view('seller/inbound/add',$data);

	}



	public function showImportForm()

	{

		$data['PageTitle']='Inbound Import';

		$data['side_menu']='inbound';



		$this->load->view('seller/inbound/import',$data);

	}



	public function importPost(){

		if(empty($_POST['name'])){

			exit("Name field is required");

		}



		if(empty($_FILES['upload_csv_file']['name'])){

			exit("No file uploaded");

		}

		if(strtolower(pathinfo($_FILES['upload_csv_file']['name'], PATHINFO_EXTENSION)) !== 'csv' ){

			exit("Uploaded file not CSV");

		}



		$get_last_inbound_number = $this->InboundModel->generate_new_transaction_id();

		$insertdatamain= [

			'inbound_no' => $get_last_inbound_number,

			'name' => $_POST['name'],

			'total_products' => 0,

			'total_price' => 0,

			'print_pro_lables' => 0,

			'month' => null,

			'year' => null,

			'label_size' => null,

			'status' => 0,

			'created_at' => time(),

			'updated_at' => time(),

			'ip' => $_SERVER['REMOTE_ADDR']

		];



		$inbound_transaction_id = $this->InboundModel->insertData('inbound',$insertdatamain);



		$csv = Reader::createFromPath($_FILES['upload_csv_file']['tmp_name'], 'r');

		$csv->setHeaderOffset(0);



		foreach($csv as $record){

			$barcodes[] = $record['upc'] ?? '-';

		}

		$product_ids = $this->getProductIdsForBarcodes($barcodes);



		foreach($csv as $record){

			if(!empty($product_ids[$record['upc']])){

				$insertdataitem=array(

					'inbound_id' => $inbound_transaction_id,

					'product_id' => $product_ids[$record['upc']],

					'product_variant' => $this->getProductVariant($product_ids[$record['upc']]) ?? '',

					'qty_scanned' => $record['quantity'],

					'location' => null,

					'restock_status' => 1,

					'created_at' => time(),

					'updated_at' => time(),

					'ip' => $_SERVER['REMOTE_ADDR']

				);



				$this->InboundModel->insertData('inbound_items_saved',$insertdataitem);

			}

		}

		$TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($inbound_transaction_id);



		$odr_update = ['total_products'=>$TotalCount->qty_scanned,'total_price'=>$TotalCount->price];

		$where_arr = ['id'=>$inbound_transaction_id];

		$this->InboundModel->updateData('inbound',$where_arr,$odr_update);



		redirect(base_url().'seller/inbound/view/'.$inbound_transaction_id);

	}


	public function getProductSku(){
		$LogindID = isset($_SESSION['LoginID']) ? $_SESSION['LoginID'] : '';
		$term_requested = $_GET['term'];

		// echo "<pre>";print_r($term_requested);die;
		$search_for_barcode_flag=(isset($_GET['search_for_barcode_flag']) && $_GET['search_for_barcode_flag'] != '') ? $_GET['search_for_barcode_flag'] : '';

		$ProductName_Sku = $this->InboundModel->getProductNameSku($term_requested,$search_for_barcode_flag,$LogindID);


		echo json_encode($ProductName_Sku); exit();



	}



    public function checkProduct(){



    	$warehouse_eta =  (isset($_POST['warehouse_eta']) ? $_POST['warehouse_eta'] : '' );

    	$carrier =  (isset($_POST['carrier']) ? $_POST['carrier'] : '' );

    	$carrier_reference =  (isset($_POST['carrier_reference']) ? $_POST['carrier_reference'] : '' );

    	$carton_count =  (isset($_POST['carton_count']) ? $_POST['carton_count'] : '' );

    	$po_reference =  (isset($_POST['po_reference']) ? $_POST['po_reference'] : '' );



        $barcode =  $this->input->post('barcode_code');

        $sku = $this->input->post('sku');

		$name = $this->input->post('name');

		$qty = $this->input->post('qty');

		$product_label = $this->input->post('product_label');

		$select_month = $this->input->post('select_month');

		$select_year = $this->input->post('select_year');

		$label_size = $this->input->post('label_size');





		$shop_id		=	$this->session->userdata('ShopID');

		$shop_details =  $this->InboundModel->getShopDetails($shop_id);

		$webshop_details =  $this->InboundModel->getWebshopDetails($shop_id,$shop_details['fbc_user_id']);



		$company_Address = '';



		if($shop_details['bill_address_line1'] !=''){



			if(isset($shop_details['bill_country']) &&  $shop_details['bill_country'] !='')

			{



			$CountryRow = $this->db->get_where('country_master',array('country_code'=>$shop_details['bill_country']))->row();

			$Country=$CountryRow->country_name;

			}else{

				$Country='';

			}



			$company_Address.=$shop_details['bill_address_line1'].', '.$shop_details['bill_address_line2'].'<br>';

			$company_Address.=$shop_details['bill_city'].', '.$shop_details['bill_state'].'<br>';

			$company_Address.=$Country.' '.$shop_details['bill_pincode'];

		}



		$currency_code = $shop_details['currency_code'];



		$prod_qty = ($qty!="")?$qty:1;

		$jsonQTY = ($qty!="")?$qty:1;



        $productData = $this->InboundModel->CheckProductsAvailable($barcode,$sku);

        $product_cat_str='';

        if(isset($productData) && !empty($productData))

        {

        	$cat_product_id= $productData->id;

        	if($productData->product_type == 'conf-simple')

        	{

        		$cat_product_id = $productData->parent_id;

        	}

        	$product_main_category = $this->InboundModel->getProductCategoryById($cat_product_id,0);

	       	if(isset($product_main_category) && count($product_main_category) > 0){



	       		foreach ($product_main_category as $key => $value)

	       		{

		       		$product_cat_str.= $value->cat_name;

		       		$product_child_category = $this->InboundModel->getProductChildCategoryById($cat_product_id,$value->category_ids,1);

		   			if(isset($product_child_category) && count($product_child_category) > 0)

		   			{

		       			foreach ($product_child_category as $key => $value) {

		       				$product_cat_str.= ' - '.$value->cat_name;

		       			}

		       		}



		       		$product_cat_str.= ', ';

	       		}

	       	}



	       	if($product_cat_str !='' )

	       	{

	       		$product_cat_str= substr($product_cat_str, 0,-2);



	       	}

        }



		$location = $productData->prod_location;

		$barcode_url = getBarcodeUrl($productData->barcode);



		if($name == NULL){

			$arrResponse  = array('status' =>400 ,'message'=>'Please enter name.');

			echo json_encode($arrResponse);exit;



		}else if(empty($productData )){



			$arrResponse  = array('status' =>400 ,'message'=>'Barcode/Sku not found.');

			echo json_encode($arrResponse);exit;



		}else{

			$get_last_inbound_number = $this->InboundModel->generate_new_transaction_id();

			$insertdatamain=array(

				'inbound_no'=>$get_last_inbound_number,

				'name'=>$name,



				'warehouse_eta'=>$warehouse_eta,

				'carrier'=>$carrier,

				'carrier_reference'=>$carrier_reference,

				'carton_count'=>$carton_count,

				'po_reference'=>$po_reference,



				'total_products'=>0,

				'total_price'=>0,

				'print_pro_lables'=>$product_label,

				'month'=>$select_month,

				'year'=>$select_year,

				'label_size'=>$label_size,

				'status'=>0,

				'created_at'=>time(),

				'updated_at'=>time(),

				'ip'=>$_SERVER['REMOTE_ADDR']

			);



			$last_inserted = $this->InboundModel->insertData('inbound',$insertdatamain);



			$json_result ="";



			if($productData->product_type =='conf-simple'){

				$json_result = $this->getProductVariant($productData->id);

			}





			if($last_inserted){

				$insertdataitem=array(

					'inbound_id'=>$last_inserted,

					'product_id'=>$productData->id,

					'product_variant'=>$json_result,

					'qty_scanned'=>$prod_qty,

					'location'=>$location,

					'restock_status'=>1,

					'created_at'=>time(),

					'updated_at'=>time(),

					'ip'=>$_SERVER['REMOTE_ADDR']



				);



				$this->InboundModel->insertData('inbound_items_saved',$insertdataitem);





				$TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($last_inserted);



				$odr_update=array('total_products'=>$TotalCount->qty_scanned,'total_price'=>$TotalCount->price);

				$where_arr=array('id'=>$last_inserted);

				$this->InboundModel->updateData('inbound',$where_arr,$odr_update);







			}



			$variants_html = '';

			$product_variants=$json_result;

			if(isset($product_variants) && $product_variants!=''){

				$variants=json_decode($product_variants, true);

				if(isset($variants) && count($variants)>0){





					foreach($variants as $pk=>$single_variant){

						foreach($single_variant as $key=>$val){



						$variants_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';



						}

					}

				}

				}else{

					$variants_html='-';

				}







			$print_html = '';

			if($product_label == 1){



				$import_date = $select_month." ".$select_year;



				if($label_size == '10x7.5'){



					$print_html = '<html lang="en"> <head> </head><body style="margin:0;padding:0;vertical-align:top;font-family: helvetica, sans-serif;font-size: 9px;background:#fff;font-weight:400;color:#000000;"><table  cellpadding="0" cellspacing="0" style="font-family:helvetica;font-size: 9px;margin:0 auto;text-align:left;padding: 0px 0px;background-color:#f5f5f5;"  width="370" height="275" align="center"><tbody><tr><td><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 9px;margin:0 auto;text-align:left;padding: 0 0px;background-color:#ffffff;" width="377" align="center"><tbody><tr><td style="padding: 0 9px;"><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin: 0px auto 5px;text-align:left;padding: 0 0px;border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><h1 style="font-weight: 400;font-size: 11px;line-height: 11px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-bottom:0;">Imported &amp; Marketed by:</h1><p style="font-weight:400;font-size: 9px;line-height: 14px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 5px;">'.$shop_details['company_name'].'<br>'.$company_Address.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0px auto 5px;text-align:leeft;padding: 0 0px; border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight:400;font-size: 9px;line-height: 11px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom:2px;">SKU Code : '.$productData->sku.'</p><p style="font-weight:400;font-size: 9px;line-height: 11px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom:2px;">Product &nbsp;&nbsp; : '.$productData->name.'</p><p style="font-weight:400;font-size: 9px;line-height: 11px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom:2px;">Product Category : '.$product_cat_str.'</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Variant &nbsp;&nbsp;&nbsp;&nbsp; : '.$variants_html.'</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Quantity : 1</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">MRP ('.$currency_code.') : '.$productData->webshop_price.' (Incl.of All Taxes)</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 5px;">Import Month : '.$import_date.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 9px;margin:0px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Customer Care : Tel: '.$webshop_details['mobile_no'].'</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: normal;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Email : '.$webshop_details['email'].'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 9px;margin: 5px auto 5px;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: center;" width="100%"><img src="'.$barcode_url.'" width="20"></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>';





				}else{



					$print_html = '<html lang="en"> <head> </head><body style="margin:0; padding:0;vertical-align:top; font-family: helvetica; font-size: 14px; background:#fff; font-weight:400;color:#000000;"><table cellpadding="0" cellspacing="0" style="font-family:helvetica;font-size: 14px;margin:0 auto;text-align:left;padding: 0px 0px;background-color:#f5f5f5;" width="100%" align="center"><tbody><tr><td><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0 auto;text-align:left;padding: 0 0px;background-color:#ffffff;" width="410" align="center"><tbody><tr><td style="padding:0 20px;"><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:20px auto 5px;text-align:left;padding: 0 0px ; border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><h1 style="font-weight: 400;font-size: 14px;line-height: 20px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-bottom:0;">Imported & Marketed by:</h1><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;">'.$shop_details['company_name'].'<br>'.$company_Address.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0px auto 5px;text-align:leeft;padding: 0 0px; border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">SKU Code : '.$productData->sku.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Product &nbsp;&nbsp; : '.$productData->name.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Product Category : '.$product_cat_str.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Variant &nbsp;&nbsp;&nbsp;&nbsp; : '.$variants_html.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Quantity : 1</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">MRP ('.$currency_code.') : '.$productData->webshop_price.' (Incl.of All Taxes)</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 15px;">Import Month : '.$import_date.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Customer Care : Tel: + '.$webshop_details['mobile_no'].'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: normal;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Email : '.$webshop_details['email'].'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:30px auto 25px ;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: center;" width="100%"><img src="'.$barcode_url.'" width="165"></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>';



				}







			}



			$arrResponse  = array('status' =>200 ,'message'=>'Barcode scanned successfully.','id'=>$last_inserted, 'qty'=>$jsonQTY, 'printStatus' => $product_label , 'printHTML'=>$print_html);

			echo json_encode($arrResponse);exit;



		}



    }



	public function AddMoreProducts()

	{

		$order_id = $this->input->post('order_id');



		$barcode =  $this->input->post('barcode_code');

        $sku = $this->input->post('sku');

		$name = $this->input->post('name');

		$qty = $this->input->post('qty');

		$product_label = $this->input->post('product_label');

		$select_month = $this->input->post('select_month');

		$select_year = $this->input->post('select_year');

		$label_size = $this->input->post('label_size');





		$shop_id		=	$this->session->userdata('ShopID');

		$shop_details =  $this->InboundModel->getShopDetails($shop_id);

		$webshop_details =  $this->InboundModel->getWebshopDetails($shop_id,$shop_details['fbc_user_id']);



		$company_Address = '';



		if($shop_details['bill_address_line1'] !=''){



			if(isset($shop_details['bill_country']) &&  $shop_details['bill_country'] !='')

			{



			$CountryRow = $this->db->get_where('country_master',array('country_code'=>$shop_details['bill_country']))->row();

			$Country=$CountryRow->country_name;

			}else{

				$Country='';

			}



			$company_Address.=$shop_details['bill_address_line1'].', '.$shop_details['bill_address_line2'].'<br>';

			$company_Address.=$shop_details['bill_city'].', '.$shop_details['bill_state'].'<br>';

			$company_Address.=$Country.' '.$shop_details['bill_pincode'];

		}



		$currency_code = $shop_details['currency_code'];



		$prod_qty = ($qty!="")?$qty:1;

		$jsonQTY = ($qty!="")?$qty:1;



        $productData = $this->InboundModel->CheckProductsAvailable($barcode,$sku);



       	$product_cat_str='';

        if(isset($productData) && !empty($productData))

        {

        	$cat_product_id= $productData->id;

        	if($productData->product_type == 'conf-simple')

        	{

        		$cat_product_id = $productData->parent_id;

        	}

        	$product_main_category = $this->InboundModel->getProductCategoryById($cat_product_id,0);

	       	if(isset($product_main_category) && count($product_main_category) > 0){



	       		foreach ($product_main_category as $key => $value)

	       		{

		       		$product_cat_str.= $value->cat_name;

		       		$product_child_category = $this->InboundModel->getProductChildCategoryById($cat_product_id,$value->category_ids,1);

		   			if(isset($product_child_category) && count($product_child_category) > 0)

		   			{

		       			foreach ($product_child_category as $key => $value) {

		       				$product_cat_str.= ' - '.$value->cat_name;

		       			}

		       		}



		       		$product_cat_str.= ', ';

	       		}

	       	}



	       	if($product_cat_str !='' )

	       	{

	       		$product_cat_str= substr($product_cat_str, 0,-2);



	       	}

        }

		$location = $productData->prod_location;

		$barcode_url = getBarcodeUrl($productData->barcode);





		if($name == NULL){

			$arrResponse  = array('status' =>400 ,'message'=>'Please enter name.');

			echo json_encode($arrResponse);exit;



		}else if(empty($productData )){



			$arrResponse  = array('status' =>400 ,'message'=>'Barcode/Sku not found.');

			echo json_encode($arrResponse);exit;



		}else{



			$ExistingProd = $this->InboundModel->CheckIfInboundProductExist($productData->id,$order_id);



			if(isset($ExistingProd) && count($ExistingProd) > 0){



				$prod_qty = $ExistingProd['qty_scanned']+$prod_qty;



				$odr_update=array('qty_scanned'=>$prod_qty,'updated_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);

				$where_arr=array('inbound_id'=>$order_id,'product_id'=>$ExistingProd['product_id']);

				$this->InboundModel->updateData('inbound_items_saved',$where_arr,$odr_update);





				$TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($order_id);



				$odr_update=array(

					'name'=>$name,

					'total_products'=>$TotalCount->qty_scanned,

					'total_price'=>$TotalCount->price,

					'print_pro_lables'=>$product_label,

					'month'=>$select_month,

					'year'=>$select_year,

					'label_size'=>$label_size,

					'updated_at'=>time(),

					'ip'=>$_SERVER['REMOTE_ADDR']



				);

				$where_arr=array('id'=>$order_id);

				$this->InboundModel->updateData('inbound',$where_arr,$odr_update);





				$variants_html = '';

				$product_variants=$ExistingProd['product_variant'];

				if(isset($product_variants) && $product_variants!=''){

					$variants=json_decode($product_variants, true);

					if(isset($variants) && count($variants)>0){





						foreach($variants as $pk=>$single_variant){

							foreach($single_variant as $key=>$val){



							$variants_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';



							}

						}

					}

				  }else{

					 $variants_html='-';

				  }





				$print_html = '';

				if($product_label == 1){



					$import_date = $select_month." ".$select_year;



					if($label_size == '10x7.5'){



						$print_html = '<html lang="en"> <head> </head><body style="margin: 0 0 ;padding:0;vertical-align:top;font-family: helvetica, sans-serif;font-size: 9px;background:#fff;font-weight:400;color:#000000;"><table cellpadding="0" cellspacing="0" style="font-family:helvetica;font-size: 9px;margin:0 auto;text-align:left;padding: 0px 0px;background-color:#f5f5f5;"  width="370"  align="center"><tbody><tr><td><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 9px;margin:0 auto;text-align:left;padding: 0 0px;background-color:#ffffff;" align="center"><tbody><tr><td style="padding: 0 9px;"><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin: 0px auto 5px;text-align:left;padding: 0 0px;border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><h1 style="font-weight: 400;font-size: 11px;line-height: 10px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-bottom:0;">Imported &amp; Marketed by:</h1><p style="font-weight:400;font-size: 9px;line-height: 14px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 5px;">'.$shop_details['company_name'].'<br>'.$company_Address.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0px auto 5px;text-align:leeft;padding: 0 0px; border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight:400;font-size: 9px;line-height: 10px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom:2px;">SKU Code : '.$productData->sku.'</p><p style="font-weight:400;font-size: 9px;line-height: 10px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom:2px;">Product &nbsp;&nbsp; : '.$productData->name.'</p><p style="font-weight:400;font-size: 9px;line-height: 10px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom:2px;">Product Category : '.$product_cat_str.'</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Variant &nbsp;&nbsp;&nbsp;&nbsp; : '.$variants_html.'</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Quantity : 1</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">MRP ('.$currency_code.') : '.$productData->webshop_price.' (Incl.of All Taxes)</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 5px;">Import Month : '.$import_date.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 9px;margin:0px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Customer Care : Tel: '.$webshop_details['mobile_no'].'</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: normal;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Email : '.$webshop_details['email'].'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 9px;margin: 5px auto 5px;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: center;" width="100%"><img src="'.$barcode_url.'" width="20" ></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>';







					}else{



						$print_html = '<html lang="en"> <head> </head><body style="margin:0; padding:0;vertical-align:top; font-family: helvetica; font-size: 14px; background:#fff; font-weight:400;color:#000000;"><table cellpadding="0" cellspacing="0" style="font-family:helvetica;font-size: 14px;margin:0 auto;text-align:left;padding: 0px 0px;background-color:#f5f5f5;" width="100%" align="center"><tbody><tr><td><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0 auto;text-align:left;padding: 0 0px;background-color:#ffffff;" width="410" align="center"><tbody><tr><td style="padding:0 20px;"><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:20px auto 5px;text-align:left;padding: 0 0px ; border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><h1 style="font-weight: 400;font-size: 14px;line-height: 20px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-bottom:0;">Imported & Marketed by:</h1><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;">'.$shop_details['company_name'].'<br>'.$company_Address.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0px auto 5px;text-align:leeft;padding: 0 0px; border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">SKU Code : '.$productData->sku.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Product &nbsp;&nbsp; : '.$productData->name.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Product Category : '.$product_cat_str.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Variant &nbsp;&nbsp;&nbsp;&nbsp; : '.$variants_html.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Quantity : 1</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">MRP ('.$currency_code.') : '.$productData->webshop_price.' (Incl.of All Taxes)</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 15px;">Import Month : '.$import_date.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Customer Care : Tel: + '.$webshop_details['mobile_no'].'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: normal;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Email : '.$webshop_details['email'].'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:30px auto 25px ;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: center;" width="100%"><img src="'.$barcode_url.'" width="165"></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>';



					}

				}







				$arrResponse  = array('status' =>200 ,'message'=>'Product Updated successfully.', 'total_price'=>$TotalCount->price, 'total_products'=>$TotalCount->qty_scanned, 'qty'=>$jsonQTY, 'printStatus' => $product_label , 'printHTML'=>$print_html);

				echo json_encode($arrResponse);exit;



			}else{



				$json_result = "";



				if($productData->product_type =='conf-simple'){

					$variantArray = $this->InboundModel->getvariantsValuesByProduct($productData->id);

					$Variant_obj = array();

					foreach($variantArray as $variant){



						$results = $this->InboundModel->getAllvariantsValues($variant['attr_id'],$variant['attr_value']);

						$Variant_obj[] = array(

							$results['attr_name']=>$results['attr_options_name'],

						);



						$json_result = json_encode($Variant_obj);



					}

				}







					$insertdataitem=array(

						'inbound_id'=>$order_id,

						'product_id'=>$productData->id,

						'product_variant'=>$json_result,

						'qty_scanned'=>$prod_qty,

						'location'=>$location,

						'restock_status'=>1,

						'created_at'=>time(),

						'updated_at'=>time(),

						'ip'=>$_SERVER['REMOTE_ADDR']



					);



					$this->InboundModel->insertData('inbound_items_saved',$insertdataitem);



					$TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($order_id);



					$odr_update=array(

						'name'=>$name,

						'total_products'=>$TotalCount->qty_scanned,

						'total_price'=>$TotalCount->price,

						'print_pro_lables'=>$product_label,

						'month'=>$select_month,

						'year'=>$select_year,

						'label_size'=>$label_size,

						'updated_at'=>time(),

						'ip'=>$_SERVER['REMOTE_ADDR']



					);

					$where_arr=array('id'=>$order_id);

					$this->InboundModel->updateData('inbound',$where_arr,$odr_update);





					$variants_html = '';

					$product_variants=$json_result;

					if(isset($product_variants) && $product_variants!=''){

						$variants=json_decode($product_variants, true);

						if(isset($variants) && count($variants)>0){





							foreach($variants as $pk=>$single_variant){

								foreach($single_variant as $key=>$val){



								$variants_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';



								}

							}

						}

					}else{

						$variants_html='-';

					}





					$print_html = '';

					if($product_label == 1){



						$import_date = $select_month." ".$select_year;



						if($label_size == '10x7.5'){



							$print_html = '<html lang="en"> <head> </head><body style="margin:0 0;padding:0;vertical-align:top;font-family: helvetica, sans-serif;font-size: 9px;background:#fff;font-weight:400;color:#000000;"><table cellpadding="0" cellspacing="0" style="font-family:helvetica;font-size: 9px;margin:0 auto;text-align:left;padding: 0px 0px;background-color:#f5f5f5;" width="370"  align="center"><tbody><tr><td><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 9px;margin:0 auto;text-align:left;padding: 0 0px;background-color:#ffffff;" width="377" align="center"><tbody><tr><td style="padding: 0 9px;"><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin: 0px auto 5px;text-align:left;padding: 0 0px;border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><h1 style="font-weight: 400;font-size: 10px;line-height: 10px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-bottom:0;">Imported &amp; Marketed by:</h1><p style="font-weight:400;font-size: 9px;line-height: 14px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 5px;">'.$shop_details['company_name'].'<br>'.$company_Address.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0px auto 5px;text-align:leeft;padding: 0 0px; border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight:400;font-size: 9px;line-height: 10px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom:2px;">SKU Code : '.$productData->sku.'</p><p style="font-weight:400;font-size: 9px;line-height: 10px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom:2px;">Product &nbsp;&nbsp; : '.$productData->name.'</p><p style="font-weight:400;font-size: 9px;line-height: 10px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom:2px;">Product Category : '.$product_cat_str.'</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Variant &nbsp;&nbsp;&nbsp;&nbsp; : '.$variants_html.'</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Quantity : 1</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">MRP ('.$currency_code.') : '.$productData->webshop_price.' (Incl.of All Taxes)</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 5px;">Import Month : '.$import_date.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 9px;margin:0px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Customer Care : Tel: '.$webshop_details['mobile_no'].'</p><p style="font-weight:400;font-size: 9px;line-height: 9px;letter-spacing: 0em;text-transform: normal;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 1px;">Email : '.$webshop_details['email'].'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 9px;margin: 5px auto 5px;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: center;" width="100%"><img src="'.$barcode_url.'" width="20"></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>';







						}else{



							$print_html = '<html lang="en"> <head> </head><body style="margin:0; padding:0;vertical-align:top; font-family: helvetica; font-size: 14px; background:#fff; font-weight:400;color:#000000;"><table cellpadding="0" cellspacing="0" style="font-family:helvetica;font-size: 14px;margin:0 auto;text-align:left;padding: 0px 0px;background-color:#f5f5f5;" width="100%" align="center"><tbody><tr><td><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0 auto;text-align:left;padding: 0 0px;background-color:#ffffff;" width="410" align="center"><tbody><tr><td style="padding:0 20px;"><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:20px auto 5px;text-align:left;padding: 0 0px ; border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><h1 style="font-weight: 400;font-size: 14px;line-height: 20px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-bottom:0;">Imported & Marketed by:</h1><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;">'.$shop_details['company_name'].'<br>'.$company_Address.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0px auto 5px;text-align:leeft;padding: 0 0px; border-bottom: 1px solid #000;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">SKU Code : '.$productData->sku.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Product &nbsp;&nbsp; : '.$productData->name.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Product Category : '.$product_cat_str.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Variant &nbsp;&nbsp;&nbsp;&nbsp; : '.$variants_html.'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Quantity : 1</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">MRP ('.$currency_code.') : '.$productData->webshop_price.' (Incl.of All Taxes)</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 15px;">Import Month : '.$import_date.'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:0px auto 0px;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: left;" width="100%"><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: capitalize;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Customer Care : Tel: + '.$webshop_details['mobile_no'].'</p><p style="font-weight: 400;font-size: 14px;line-height: 18px;letter-spacing: 0em;text-transform: normal;color: #000000;font-family: helvetica;margin-top:0;margin-bottom: 3px;">Email : '.$webshop_details['email'].'</p></td></tr></tbody></table><table cellpadding="0" cellspacing="0" style="font-family: helvetica;font-size: 14px;margin:30px auto 25px ;text-align:left;padding: 0 0px;" width="100%" align="center"><tbody><tr><td style="padding:0;vertical-align: middle; text-align: center;" width="100%"><img src="'.$barcode_url.'" width="165"></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>';



						}

					}





					$arrResponse  = array('status' =>200 ,'message'=>'Product added successfully.', 'total_price'=>$TotalCount->price, 'total_products'=>$TotalCount->qty_scanned, 'qty'=>$jsonQTY, 'printStatus' => $product_label , 'printHTML'=>$print_html);

					echo json_encode($arrResponse);exit;







			}





		}



	}







    public function loadInboundajax(){



		$search_term = $this->input->post('search[value]');

		$inboundList = $this->InboundModel->getInboundDetails($search_term);

		$total_count = (is_array($inboundList)) ? count($inboundList) : 0;

		$data = array();

		$no = $_POST['start'];





		if(is_array($inboundList))

		{

			foreach ($inboundList as $readData) {



				if($readData['status'] == 1){

					$link = '<a class="link-purple" href="'.base_url().'seller/inbound/order/detail/'.$readData['id'].'">View</a>';

				}else{

					$link = '<a class="link-purple" href="'.base_url().'seller/inbound/view/'.$readData['id'].'">View</a>';

				}



				$no++;

				$row = array();

				$row[]=$readData['inbound_no'];

				$row[]=$readData['name'];

				$row[]=date('d-m-Y h:i', $readData['created_at']);

				$row[]=$readData['total_products'];

				$row[]=($readData['status'] == 1)?'Created':'Draft Saved';

				$use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');



				if ($use_advanced_warehouse->value=="yes") {

					$warehouse_status=$readData['warehouse_status'];

					$row[]=$this->CommonModel->getWarehouse_status_name($warehouse_status);



				}



				$row[]=$link;



				$data[] = $row;



			}

		}

		$output = array(

						"draw" => $_POST['draw'],

						"recordsTotal" => $this->InboundModel->count_all_InboundOrder(),

						"recordsFiltered" =>$this->InboundModel->count_all_InboundOrder(),

						"data" => $data,

				);



		//output to json format

		echo json_encode($output);

		exit;

	}





	public function loadInboundProductsajax(){



		$order_id= $this->input->post('order_id');



		$inboundList =  $this->InboundModel->getAllInboundSavedProductsById($order_id);



		$use_advanced_warehouse = $this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value')->value === 'yes';



		//$total_count =  count( $this->InboundModel->getAllInboundSavedProductsById($order_id));



		$total_count = (is_array($inboundList)) ? count($inboundList) : 0;

		$data = array();

		$no = $_POST['start'];



		if(is_array($inboundList))

		{

			foreach ($inboundList as $readData) {



				$checkbox = ($readData['restock_status'] == 1)?"checked":"";

				$check_restock = '<div class="switch-onoff"><label class="checkbox"><input name="restock_'.$readData['product_id'].'" type="checkbox" '.$checkbox .' autocomplete="off"><span class="checked"></span></label>

				</div>';

				$location = $readData['location'];





				$variants_html = '';

				$product_variants=$readData['product_variant'];

				if(isset($product_variants) && $product_variants!=''){

					$variants=json_decode($product_variants, true);

					if(isset($variants) && count($variants)>0){





						foreach($variants as $pk=>$single_variant){

							foreach($single_variant as $key=>$val){



							$variants_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';



							}

						}

					}

				  }else{

					 $variants_html='-';

				  }





				$no++;

				$row = array();

				$row[]='<input class="form-control" name="product_ids[]" type="hidden" value="'.$readData['product_id'].'" placeholder="Enter Location">'. $readData['name'];

				if($use_advanced_warehouse){

					$row[]=$readData['sku'] . (

						!empty($readData['warehouse_updated_at']) ?

							' <span style="width: 8px; height: 8px; display: inline-block; background: #00dd00;" title="Product exists in warehouse"></span>'

							: ' <span style="width: 8px; height: 8px; display: inline-block; background: #bd2130;" title="Product does not exist in warehouse"></span>');

				} else {

					$row[]=$readData['sku'];

				}



				$row[]=$variants_html;

				//$row[]=$readData['qty_scanned'];

				$row[]='<input id="qty_scanned_'.$readData['product_id'].'" name="qty_scanned_'.$readData['product_id'].'" class="form-control enter-location-field location-input" type="text" value="'.$readData['qty_scanned'].'" placeholder="Enter Qty Scanned" onfocusout="updateQtyScanned('.$readData['product_id'].')">';

				$row[]=$readData['webshop_price'];

				$row[]=$readData['qty_scanned']*$readData['webshop_price'];

				$row[]='<input id="location_'.$readData['product_id'].'" name="location_'.$readData['product_id'].'" class="form-control enter-location-field location-input" type="text" value="'.$location.'" placeholder="Enter Location" onfocusout="updateLocation('.$readData['product_id'].')">';

				$row[]=$check_restock;

				$row[]=$readData['product_inv_type'];



				if(!empty($this->session->userdata('userPermission')) && !in_array('seller/database/write',$this->session->userdata('userPermission'))){ //<!---Bipin--->

				 	$row[] ='-';

				}else{

					$row[]='<a class="link-purple" onclick="deleteInboundProducts('.$readData['product_id'].','.$order_id.')" href="javascript:void(0)">Delete</a>';

				}



				$data[] = $row;



			}

		}



		$TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($order_id);



		$odr_update=array(

			'total_products'=>$TotalCount->qty_scanned,

			'total_price'=>$TotalCount->price,

		);

		$where_arr=array('id'=>$order_id);

		$this->InboundModel->updateData('inbound',$where_arr,$odr_update);







		$output = array(

						"draw" => $_POST['draw'],

						"recordsTotal" => $total_count,

						"recordsFiltered" =>$total_count,

						"data" => $data,

				);



		//output to json format

		echo json_encode($output);

		exit;

	}







	public function editInbound(){



		$data['PageTitle']='Inbound Add';

		$data['side_menu']='inbound';

		$shop_id		=	$this->session->userdata('ShopID');

		$order_id=$this->uri->segment(4);



		$TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($order_id);

		$odr_update=array(

			'total_products'=>$TotalCount->qty_scanned,

			'total_price'=>$TotalCount->price,

		);

		$where_arr=array('id'=>$order_id);

		$this->InboundModel->updateData('inbound',$where_arr,$odr_update);





		$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);

		$data['inboundData']=$this->InboundModel->getInboundData($order_id);

		$this->load->view('seller/inbound/editInbound',$data);



	}



	public function submitAction(){



		$order_id = $_POST['order_id'];

		$name = $_POST['name'];



        $warehouse_eta =  (isset($_POST['warehouse_eta']) ? $_POST['warehouse_eta'] : '' );

    	$carrier =  (isset($_POST['carrier']) ? $_POST['carrier'] : '' );

    	$carrier_reference =  (isset($_POST['carrier_reference']) ? $_POST['carrier_reference'] : '' );

    	$carton_count =  (isset($_POST['carton_count']) ? $_POST['carton_count'] : '' );

    	$po_reference =  (isset($_POST['po_reference']) ? $_POST['po_reference'] : '' );



		$product_label =  (isset($_POST['product_label']))?1:0;

		$select_month = $_POST['select_month'];

		$select_year = $_POST['select_year'];

		$label_size = $_POST['label_size'];



		if(isset($_POST['btn'])){

			if($_POST['btn'] == 0){



				//print_r($_POST);

				if(isset($_POST['product_ids'])){



					 foreach($_POST['product_ids'] as $product_id){



						$location = $_POST['location_'.$product_id];

						$post_restock = (isset($_POST['restock_'.$product_id]))?1:0;





						$odr_update=array('location'=>$location,'restock_status'=>$post_restock,'updated_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);

						$where_arr=array('inbound_id'=>$order_id,'product_id'=>$product_id);

						$this->InboundModel->updateData('inbound_items_saved',$where_arr,$odr_update);



					 }

				}





				$TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($order_id);



				$odr_update=array(

					'name'=>$_POST['name'],

					'warehouse_eta'=>$warehouse_eta,

					'carrier'=>$carrier,

					'carrier_reference'=>$carrier_reference,

					'carton_count'=>$carton_count,

					'po_reference'=>$po_reference,



					'total_products'=>$TotalCount->qty_scanned,

					'total_price'=>$TotalCount->price,

					'print_pro_lables'=>(isset($_POST['product_label']))?1:0,

					'month'=>$select_month,

					'year'=>$select_year,

					'label_size'=>$label_size,

					'updated_at'=>time(),

				);

				$where_arr=array('id'=>$order_id);

				$this->InboundModel->updateData('inbound',$where_arr,$odr_update);



			}else{

				//Create Inbound Trigger



				if(isset($_POST['product_ids'])){



					foreach($_POST['product_ids'] as $product_id){



					   	$location = $_POST['location_'.$product_id];

					   	$post_restock = (isset($_POST['restock_'.$product_id]))?1:0;

						$getData = $this->InboundModel->getSavedInboundProducts($product_id,$order_id);







						$insertdataitem=array(

							'inbound_id'=>$order_id,

							'product_id'=>$product_id,

							'qty_scanned'=>$getData['qty_scanned'],

							'location'=>$location,

							'restock_status'=>$post_restock,

							'product_name'=>$getData['name'],

							'variants'=>$getData['product_variant'],

							'price'=>$getData['webshop_price'],

							'total_price'=>$getData['webshop_price']*$getData['qty_scanned'],

							'product_inv_type'=>$getData['product_inv_type'],

							'created_at'=>time(),

							'updated_at'=>time(),

							'ip'=>$_SERVER['REMOTE_ADDR']



						);



						$this->InboundModel->insertData('inbound_items',$insertdataitem);



						//Update Product and Stock

							if(isset($location) && $location != NULL){

								$product_update=array('prod_location'=>$location);

								$where_arr=array('id'=>$product_id,);

								$this->InboundModel->updateData('products',$where_arr,$product_update);

							}



							if($post_restock == 1){

								$qty = ($getData['qty']+$getData['qty_scanned']);

								$available_qty = ($getData['available_qty']+$getData['qty_scanned']);

								$product_qty_update=array('qty'=>$qty,'available_qty'=>$available_qty);

								$where_arr=array('product_id'=>$product_id);

								$this->InboundModel->updateData('products_inventory',$where_arr,$product_qty_update);

							}



					}

			   }



			   $TotalCount = $this->InboundModel->getTotalInboundItemQtyPrice($order_id);



				$odr_update=array(

					'name'=>$name,



					'warehouse_eta'=>$warehouse_eta,

					'carrier'=>$carrier,

					'carrier_reference'=>$carrier_reference,

					'carton_count'=>$carton_count,

					'po_reference'=>$po_reference,



					'total_products'=>$TotalCount->qty_scanned,

					'total_price'=> $TotalCount->price,

					'print_pro_lables'=>$product_label,

					'month'=>$select_month,

					'year'=>$select_year,

					'label_size'=>$label_size,

					'status'=>1,

					'updated_at'=>time(),

					'ip'=>$_SERVER['REMOTE_ADDR']

				);

				$where_arr=array('id'=>$order_id);

				$this->InboundModel->updateData('inbound',$where_arr,$odr_update);





			}



			redirect(base_url().'seller/inbound');



		}else{



		}



	}



	public function viewInboundOrder(){



		$data['PageTitle']='Inbound Order';

		$data['side_menu']='inbound';

		$shop_id		=	$this->session->userdata('ShopID');

		$order_id=$this->uri->segment(5);

		$data['advance_warehouse_flag'] = $this->InboundModel->getCustomData();

		$data['inboundData'] = $inboundData = $this->InboundModel->getInboundData($order_id);

		$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);







		$this->load->view('seller/inbound/OrderInbound',$data);



	}



	public function deleteProducts(){



		if(isset($_POST)) {

			$order_id = $_POST['order_id'];

			$product_id = $_POST['product_id'];



			$where=array(

				'inbound_id'=>$order_id,

				'product_id'=>$product_id

			);

			$this->InboundModel->deleteDataById('inbound_items_saved',$where);



			$TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($order_id);



			$odr_update=array(



				'total_products'=>$TotalCount->qty_scanned,

				'total_price'=>$TotalCount->price,

				'updated_at'=>time(),

			);

			$where_arr=array('id'=>$order_id);

			$this->InboundModel->updateData('inbound',$where_arr,$odr_update);





			$price = (isset($TotalCount->price) && $TotalCount->price !="") ? $TotalCount->price : '0.00';

			$products = (isset($TotalCount->qty_scanned) &&$TotalCount->qty_scanned !="") ? $TotalCount->qty_scanned : '';



			$arrResponse  = array('status' =>200 ,'message'=>'Product deleted successfully.', 'total_price'=>$price, 'total_products'=>$products);

			echo json_encode($arrResponse);exit;



		}else{

			$arrResponse  = array('status' =>400 ,'message'=>'Error in deleting products.');

			echo json_encode($arrResponse);exit;



		}





	}





	public function loadInboundOrderedProductsajax(){



		$order_id= $this->input->post('order_id');



		$inboundList =  $this->InboundModel->getAllInboundOrderedProductsById($order_id);





	//	$total_count = count($this->InboundModel->getAllInboundOrderedProductsById($order_id));

		$total_count = (is_array($inboundList)) ? count($inboundList) : 0;

		$data = array();

		$no = $_POST['start'];



		if(is_array($inboundList))

		{

			foreach ($inboundList as $readData) {



				$check_restock = ($readData['restock_status'] == 1)?"Yes":"No";



				$variants_html = '';

				$product_variants=$readData['variants'];

				if(isset($product_variants) && $product_variants!=''){

					$variants=json_decode($product_variants, true);

					if(isset($variants) && count($variants)>0){





						foreach($variants as $pk=>$single_variant){

							foreach($single_variant as $key=>$val){



							$variants_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';



							}

						}

					}

				  }else{

					 $variants_html='-';

				  }





				$no++;

				$row = array();



				$row[]=$readData['inbound_no'];

				$row[]=$readData['product_name'];

				$row[]=$readData['sku'];

				$row[]=$variants_html;

				$row[]=$readData['qty_scanned'];

				$row[]=$readData['price'];

				$row[]=$readData['total_price'];

				$row[]=$readData['location'];

				$row[]=$check_restock;

				$row[]=$readData['product_inv_type'];





				$data[] = $row;



			}

		}



		$output = array(

						"draw" => $_POST['draw'],

						"recordsTotal" => $total_count,

						"recordsFiltered" =>$total_count,

						"data" => $data,

				);



		//output to json format

		echo json_encode($output);

		exit;

	}



	public function updateLocation(){



		//print_r($_POST);exit;



		if(isset($_POST)) {



			$order_id = $_POST['order_id'];

			$product_id = $_POST['product_id'];

			$location = $_POST['location'];





			$odr_update=array(

				'location'=>$location,

			);



			$where_arr=array('inbound_id'=>$order_id,'product_id'=>$product_id);

			$this->InboundModel->updateData('inbound_items_saved',$where_arr,$odr_update);



			$arrResponse  = array('status' =>200 ,'message'=>'Location updated.');

			echo json_encode($arrResponse);exit;



		}else{

			$arrResponse  = array('status' =>400 ,'message'=>'Error while updating location.');

			echo json_encode($arrResponse);exit;



		}





	}



	public function updateQtyScanned(){



		//print_r($_POST);exit;



		if(isset($_POST)) {



			$order_id = $_POST['order_id'];

			$product_id = $_POST['product_id'];

			$qty_scanned = $_POST['qty_scanned'];





			$odr_update=array(

				'qty_scanned'=>$qty_scanned,

			);



			$where_arr=array('inbound_id'=>$order_id,'product_id'=>$product_id);

			$this->InboundModel->updateData('inbound_items_saved',$where_arr,$odr_update);



			$TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($order_id);



			$odr_update=array(



				'total_products'=>$TotalCount->qty_scanned,

				'total_price'=>$TotalCount->price,

				'updated_at'=>time(),

			);

			$where_arr=array('id'=>$order_id);

			$this->InboundModel->updateData('inbound',$where_arr,$odr_update);





			$price = (isset($TotalCount->price) && $TotalCount->price !="") ? $TotalCount->price : '0.00';

			$products = (isset($TotalCount->qty_scanned) &&$TotalCount->qty_scanned !="") ? $TotalCount->qty_scanned : '';





			$arrResponse  = array('status' =>200 ,'message'=>'Qty updated.', 'total_price'=>$price, 'total_products'=>$products);

			echo json_encode($arrResponse);exit;



		}else{

			$arrResponse  = array('status' =>400 ,'message'=>'Error while updating Qty.');

			echo json_encode($arrResponse);exit;



		}





	}



	// Export Saved Order in CSV/Excel format

	public function exportCSV(){



		$order_id= $_POST['order_inbound_id'];

		$inbound_details = $this->InboundModel->getInboundAndNameNoById($order_id);



		$shop_id = $this->session->userdata('ShopID');



		$resultHsn=$this->CommonModel->getHsncodeIdByShopId($shop_id);

		$hsnMainId='';

		if($resultHsn){

			$hsnMainId=$resultHsn->id;

		}



		if($_POST['inbound_export'] == 'csv'){



			// get data

			$inboundList =  $this->InboundModel->getAllInboundSavedProductsById($order_id);



			foreach($inboundList as $data){

				if($data['parent_id'] != 0 ){

					$product_main_id = $data['parent_id'];

				}else{

					$product_main_id = $data['product_id'];

				}





				// hsn code

				if($hsnMainId !=""){

					$shopProductAttributes=$this->CommonModel->getSingleShopDataByID('products_attributes',array('product_id'=>$product_main_id,'attr_id'=>$hsnMainId),'*');



					//print_r($shopProductAttributes);

					if(isset($shopProductAttributes) && $shopProductAttributes != ""){

						$HSN_SAC =$shopProductAttributes->attr_value;

					}else{

						$HSN_SAC ='';

					}

				}else{

					$HSN_SAC ='';

				}



				if($data['product_variant'] != NULL ){



					$product_var = json_decode($data['product_variant']);

					$colour_val = '';

					$size_val = '';

					$Other_val = array();

					foreach($product_var as $varaint){



						foreach($varaint as $key=>$value){



							if($key == 'Color'){



								$colour_val = " - ".$value;



							}else if($key == 'Size' || $key == 'Shoe Size'){



								$size_val = " - ".$value;

							}else{



								$Other_val[] =  $value;



							}

						}



					}



					$other_val = implode("-",$Other_val);

					$product_name =  $data['name'].$size_val.$colour_val.$other_val;

				}else{

					$product_name = $data['name'];

				}







				//$Category ='';

				$cat_name=$this->SellerProductModel->getProductsMaintCategoryNames($product_main_id);

				$Income_Account = 'Sales - Merchandise';

				$QuantityOnHand = '';

				$LowStockAlert = '';

				$QuantityAsOfDate = '';



				$SingleRow =array(

					$product_name,

					$data['sku'],

					$HSN_SAC,

					$data['qty_scanned'],

					$cat_name,

					$Income_Account,

					$QuantityOnHand,

					$LowStockAlert,

					$QuantityAsOfDate



				);



				$ExportValuesArr[]=$SingleRow;

			}



				// file name

				$filename = $inbound_details['inbound_no']."-".$inbound_details['name'].".csv";

				header("Content-Description: File Transfer");

				header("Content-Disposition: attachment; filename=\"{$filename}\"");

				header("Content-Type: application/csv; ");



				$sis_export_header =array("Product/Service Name","SKU","HSN/SAC","Unit","Category","Income Account","Quantity on Hand","Low Stock Alert","Quantity as-of Date");



				// file creation

				$file = fopen('php://output', 'w');

				fputs( $file, "\xEF\xBB\xBF" ); // UTF-8 BOM !!!!!

				fputcsv($file, $sis_export_header);



				if(isset($inboundList) && count($inboundList)>0){



					foreach ($ExportValuesArr as $readData) {

						fputcsv($file, $readData);

					}

				}

				fclose($file);

				//readfile ($filename);

				exit();

			}



			if($_POST['inbound_export'] == 'excel'){



				// get data

			$inboundList =  $this->InboundModel->getAllInboundSavedProductsById($order_id);



			foreach($inboundList as $data){

				if($data['parent_id'] != 0 ){

					$product_main_id = $data['parent_id'];

				}else{

					$product_main_id = $data['product_id'];

				}





				// hsn code

				if($hsnMainId !=""){

					$shopProductAttributes=$this->CommonModel->getSingleShopDataByID('products_attributes',array('product_id'=>$product_main_id,'attr_id'=>$hsnMainId),'*');



					//print_r($shopProductAttributes);

					if(isset($shopProductAttributes) && $shopProductAttributes != ""){

						$HSN_SAC =$shopProductAttributes->attr_value;

					}else{

						$HSN_SAC ='';

					}

				}else{

					$HSN_SAC ='';

				}



				if($data['product_variant'] != NULL ){



					$product_var = json_decode($data['product_variant']);

					$colour_val = '';

					$size_val = '';

					$Other_val = array();

					foreach($product_var as $varaint){



						foreach($varaint as $key=>$value){



							if($key == 'Color'){



								$colour_val = " - ".$value;



							}else if($key == 'Size' || $key == 'Shoe Size'){



								$size_val = " - ".$value;

							}else{



								$Other_val[] =  $value;



							}

						}



					}



					$other_val = implode("-",$Other_val);

					$product_name =  $data['name'].$size_val.$colour_val.$other_val;

				}else{

					$product_name = $data['name'];

				}







				//$Category ='';

				$cat_name=$this->SellerProductModel->getProductsMaintCategoryNames($product_main_id);

				$Income_Account = 'Sales - Merchandise';

				$QuantityOnHand = '';

				$LowStockAlert = '';

				$QuantityAsOfDate = '';



				$SingleRow =array(

					$product_name,

					$data['sku'],

					$HSN_SAC,

					$data['qty_scanned'],

					$cat_name,

					$Income_Account,

					$QuantityOnHand,

					$LowStockAlert,

					$QuantityAsOfDate



				);



				$ExportValuesArr[]=$SingleRow;

			}



				// file name

				$filename = $inbound_details['inbound_no']."-".$inbound_details['name'].".xls";

				header("Content-Description: File Transfer");

				header("Content-Disposition: attachment; filename=\"{$filename}\"");

				header("Content-Type: application/xls; ");



				$sis_export_header =array("Product/Service Name","SKU","HSN/SAC","Unit","Category","Income Account","Quantity on Hand","Low Stock Alert","Quantity as-of Date");



				// file creation

				$file = fopen('php://output', 'w');

				fputs( $file, "\xEF\xBB\xBF" ); // UTF-8 BOM !!!!!

				fputcsv($file, $sis_export_header);



				if(isset($inboundList) && count($inboundList)>0){



					foreach ($ExportValuesArr as $readData) {

						fputcsv($file, $readData);

					}

				}

				fclose($file);

				//readfile ($filename);

				exit();





			}



	}







	// Export Inbound Order in CSV/Excel format

	public function exportCSVOrdered(){



		$order_id= $_POST['order_inbound_id'];

		$inbound_details = $this->InboundModel->getInboundAndNameNoById($order_id);



		$shop_id = $this->session->userdata('ShopID');



		$resultHsn=$this->CommonModel->getHsncodeIdByShopId($shop_id);

		$hsnMainId='';

		if($resultHsn){

			$hsnMainId=$resultHsn->id;

		}



		if($_POST['inbound_export'] == 'csv'){



			// get data

			$inboundList =  $this->InboundModel->getAllInboundOrderedProductsById($order_id);



			foreach($inboundList as $data){

				if($data['parent_id'] != 0 ){

					$product_main_id = $data['parent_id'];

				}else{

					$product_main_id = $data['product_id'];

				}



				// hsn code

				if($hsnMainId !=""){

					$shopProductAttributes=$this->CommonModel->getSingleShopDataByID('products_attributes',array('product_id'=>$product_main_id,'attr_id'=>$hsnMainId),'*');



					//print_r($shopProductAttributes);

					if(isset($shopProductAttributes) && $shopProductAttributes != ""){

						$HSN_SAC =$shopProductAttributes->attr_value;

					}else{

						$HSN_SAC ='';

					}

				}else{

					$HSN_SAC ='';

				}



				if($data['variants'] != NULL ){



					$product_var = json_decode($data['variants']);

					$colour_val = '';

					$size_val = '';

					$Other_val = array();

					foreach($product_var as $varaint){



						foreach($varaint as $key=>$value){



							if($key == 'Color'){



								$colour_val = " - ".$value;



							}else if($key == 'Size' || $key == 'Shoe Size'){



								$size_val = " - ".$value;

							}else{



								$Other_val[] =  $value;



							}

						}



					}



					$other_val = implode("-",$Other_val);

					$product_name =  $data['product_name'].$size_val.$colour_val.$other_val;

				}else{

					$product_name = $data['product_name'];

				}





				//$Category ='';

				$cat_name=$this->SellerProductModel->getProductsMaintCategoryNames($product_main_id);

				$Income_Account = 'Sales - Merchandise';

				$QuantityOnHand = '';

				$LowStockAlert = '';

				$QuantityAsOfDate = '';



				$SingleRow =array(

					$product_name,

					$data['sku'],

					$HSN_SAC,

					$data['qty_scanned'],

					$cat_name,

					$Income_Account,

					$QuantityOnHand,

					$LowStockAlert,

					$QuantityAsOfDate



				);



				$ExportValuesArr[]=$SingleRow;

			}



				// file name

				$filename = $inbound_details['inbound_no']."-".$inbound_details['name'].".csv";

				header("Content-Description: File Transfer");

				header("Content-Disposition: attachment; filename=\"{$filename}\"");

				header("Content-Type: application/csv; ");



				$sis_export_header =array("Product/Service Name","SKU","HSN/SAC","Unit","Category","Income Account","Quantity on Hand","Low Stock Alert","Quantity as-of Date");



				// file creation

				$file = fopen('php://output', 'w');

				fputs( $file, "\xEF\xBB\xBF" ); // UTF-8 BOM !!!!!

				fputcsv($file, $sis_export_header);



				if(isset($inboundList) && count($inboundList)>0){



					foreach ($ExportValuesArr as $readData) {

						fputcsv($file, $readData);

					}

				}

				fclose($file);

				//readfile ($filename);

				exit();

			}



			if($_POST['inbound_export'] == 'excel'){



				// get data

			$inboundList =  $this->InboundModel->getAllInboundOrderedProductsById($order_id);



			foreach($inboundList as $data){

				if($data['parent_id'] != 0 ){

					$product_main_id = $data['parent_id'];

				}else{

					$product_main_id = $data['product_id'];

				}



				// hsn code

				if($hsnMainId !=""){

					$shopProductAttributes=$this->CommonModel->getSingleShopDataByID('products_attributes',array('product_id'=>$product_main_id,'attr_id'=>$hsnMainId),'*');



					//print_r($shopProductAttributes);

					if(isset($shopProductAttributes) && $shopProductAttributes != ""){

						$HSN_SAC =$shopProductAttributes->attr_value;

					}else{

						$HSN_SAC ='';

					}

				}else{

					$HSN_SAC ='';

				}



				if($data['variants'] != NULL ){



					$product_var = json_decode($data['variants']);

					$colour_val = '';

					$size_val = '';

					$Other_val = array();

					foreach($product_var as $varaint){



						foreach($varaint as $key=>$value){



							if($key == 'Color'){



								$colour_val = " - ".$value;



							}else if($key == 'Size' || $key == 'Shoe Size'){



								$size_val = " - ".$value;

							}else{



								$Other_val[] =  $value;



							}

						}



					}



					$other_val = implode("-",$Other_val);

					$product_name =  $data['product_name'].$size_val.$colour_val.$other_val;

				}else{

					$product_name = $data['product_name'];

				}



				//$Category ='';

				$cat_name=$this->SellerProductModel->getProductsMaintCategoryNames($product_main_id);

				$Income_Account = 'Sales - Merchandise';

				$QuantityOnHand = '';

				$LowStockAlert = '';

				$QuantityAsOfDate = '';



				$SingleRow =array(

					$product_name,

					$data['sku'],

					$HSN_SAC,

					$data['qty_scanned'],

					$cat_name,

					$Income_Account,

					$QuantityOnHand,

					$LowStockAlert,

					$QuantityAsOfDate



				);



				$ExportValuesArr[]=$SingleRow;

			}



				// file name

				$filename = $inbound_details['inbound_no']."-".$inbound_details['name'].".xls";

				header("Content-Description: File Transfer");

				header("Content-Disposition: attachment; filename=\"{$filename}\"");

				header("Content-Type: application/xls; ");



				$sis_export_header =array("Product/Service Name","SKU","HSN/SAC","Unit","Category","Income Account","Quantity on Hand","Low Stock Alert","Quantity as-of Date");



				// file creation

				$file = fopen('php://output', 'w');

				fputs( $file, "\xEF\xBB\xBF" ); // UTF-8 BOM !!!!!

				fputcsv($file, $sis_export_header);



				if(isset($inboundList) && count($inboundList)>0){



					foreach ($ExportValuesArr as $readData) {

						fputcsv($file, $readData);

					}

				}

				fclose($file);

				//readfile ($filename);

				exit();





			}



	}





	public function printOrder(){



		$order_id=$this->uri->segment(4);

		if(isset($order_id) && $order_id>0){



			$shop_id		=	$this->session->userdata('ShopID');



			$data['inboundData']=$inboundData = $this->InboundModel->getInboundData($order_id);

			$data['inboundProducts']=$inboundProducts =  $this->InboundModel->getAllInboundOrderedProductsById($order_id);



			$data['currency_code']=$this->CommonModel->getShopCurrency($shop_id);



			$this->load->view('seller/inbound/order-print',$data);



		}else{



			redirect('/seller/inbound');

		}

	}



	public function getProductSku_for_add_product(){



		$term_requested = $_GET['term'];

		$ProductName_Sku = $this->InboundModel->getProductNameSku_for_add_product($term_requested);



		echo json_encode($ProductName_Sku);

		exit;

	}



	private function getProductIdsForBarcodes(array $barcodes)

	{

		$shop_id = $this->session->userdata('ShopID');

		$shop_db =  DB_NAME_PREFIX.$shop_id;



		$product_ids = [];

		$barcodes_string = implode("','", $barcodes);

		$query = $this->db->query("SELECT id, barcode FROM {$shop_db}.products WHERE barcode IN ('$barcodes_string')");

		foreach($query->result() as $row){

			$product_ids[$row->barcode] = $row->id;

		}

		return $product_ids;

	}



	private function getProductVariant($product_id)

	{

		$variantArray = $this->InboundModel->getvariantsValuesByProduct($product_id);

		$Variant_obj = array();

		foreach ($variantArray as $variant) {



			$results = $this->InboundModel->getAllvariantsValues($variant['attr_id'], $variant['attr_value']);

			$Variant_obj[] = array(

				$results['attr_name'] => $results['attr_options_name'],

			);



			$json_result = json_encode($Variant_obj);



		}

		return $json_result;

	}



}

