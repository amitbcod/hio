<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SpecialFeaturesController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $method = $this->router->fetch_method();
        if (!empty($method) && $method != 'upc_catlog_view') {
            if ($this->session->userdata('LoginID') == '') {
                redirect(BASE_URL.'customer/login');
            }
        }
    }

    public function special_features()
    {
        $data['PageTitle']= 'My Profile - Special Features';
        $LoginID = $_SESSION['LoginID'];

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $data['side_tab'] = 'special_features';

        $postArr = array('customer_id'=>$LoginID);
        $response = CustomerRepository::customer_get_personal_info($shopcode, $shop_id, $postArr);
        //echo '<pre>';print_r($response);//exit;
        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $data['customerData'] = $response->customerData;
            $data['profilePercentage'] = $response->profile_percentage;
        }

        $this->template->load('myprofile/my-profile-special-features', $data);
    }

    public function upc_catlog_listing()
    {
        $data['PageTitle']= 'My Profile - Special Features';
        $LoginID = $_SESSION['LoginID'];

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $data['side_tab'] = 'special_features';

        $postArr = array('customer_id'=>$LoginID);
        $response = CustomerRepository::customer_get_personal_info($shopcode, $shop_id, $postArr);
        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $data['customerData'] = $response->customerData;
            $data['profilePercentage'] = $response->profile_percentage;
        }

        // $apiUrl2 = '/webshop/get_table_data'; //get_catlog_builder list
        // $table = 'catalog_builder';
        // $flag = 'own';
        // $order_by = 'ORDER BY id DESC';
        // $where = 'customer_id  = ?';
        // $params = array($LoginID);
        // $postArr2 = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'table_name'=>$table,'database_flag'=>$flag,'where'=>$where,'order_by'=>$order_by,'params'=>$params);
        // $response2= $this->restapi->post_method($apiUrl2,$postArr2);
        // //echo '<pre>';print_r($response2);//exit;
        // if($response2->is_success=='true'){
        // 	$data['catlog_builder_list'] = $response2->tableData;
        //       }

        $this->template->load('myprofile/my-profile-upc-catlog-listing', $data);
    }

    public function upc_catlog_listing_test()
    {
        $data['PageTitle']= 'My Profile - Special Features';
        $LoginID = $_SESSION['LoginID'];

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $data['side_tab'] = 'special_features';

        $postArr = array('customer_id'=>$LoginID);
        $response = CustomerRepository::customer_get_personal_info($shopcode, $shop_id, $postArr);

        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $data['customerData'] = $response->customerData;
            $data['profilePercentage'] = $response->profile_percentage;
        }

        // $apiUrl2 = '/webshop/get_table_data'; //get_catlog_builder list
        // $table = 'catalog_builder';
        // $flag = 'own';
        // $order_by = 'ORDER BY id DESC';
        // $where = 'customer_id  = ?';
        // $params = array($LoginID);
        // $postArr2 = array('shopcode'=>$shopcode,'shopid'=>$shop_id,'table_name'=>$table,'database_flag'=>$flag,'where'=>$where,'order_by'=>$order_by,'params'=>$params);
        // $response2= $this->restapi->post_method($apiUrl2,$postArr2);
        // //echo '<pre>';print_r($response2);//exit;
        // if($response2->is_success=='true'){
        // 	$data['catlog_builder_list'] = $response2->tableData;
        // }
        // echo "<pre>";print_r($data);die();

        $this->load->view('myprofile/my-profile-upc-catlog-listing_test', $data);
    }

    public function catlog_builder_scanning()
    {
        $data['PageTitle']= 'My Profile - Special Features';
        $data['customer_id']=$LoginID = $_SESSION['LoginID'];

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $data['side_tab'] = 'special_features';

        $postArr = array('customer_id'=>$LoginID);
        $response = CustomerRepository::customer_get_personal_info($shopcode, $shop_id, $postArr);

        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $data['customerData'] = $response->customerData;
            $data['profilePercentage'] = $response->profile_percentage;
        }

        $postArr = array('customer_id'=>$LoginID);
        $response = ProductRepository::scanned_products_listing_new($shopcode, $shop_id, $postArr);
        $scanned_productsData= array();
        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            // $scanned_productsData = $response->scanned_productsData;
            $data['total_scanned_products_count'] = $response->scanned_products_count;
        }

        $this->template->load('myprofile/catlog_builder_scanning', $data);
    }

    public function getProductSku()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $term_requested = $_GET['term'];

        $postArr = array('term'=>$term_requested);
        $response = ProductRepository::getProductSku($shopcode, $shop_id, $postArr);

        $ProductName_Sku='';
        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $ProductName_Sku =$response->products;
        } else {
            $ProductName_Sku='';
        }
        echo json_encode($ProductName_Sku);
    }


    public function checkProduct()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $barcode =  $this->input->post('barcode_code');
        $sku = $this->input->post('sku');

        $qty = $this->input->post('qty');
        $prod_qty = ($qty!="")?$qty:1;
        $jsonQTY = ($qty!="")?$qty:1;

        $postArr = array('sku'=>$sku,'barcode'=>$barcode,'qty'=>$prod_qty);
        $response = ProductRepository::getAvailableProducts($shopcode, $shop_id, $postArr);

        $AvailableProducts='';
        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $AvailableProducts =$response->AvailableProducts;
        } else {
            $AvailableProducts='';
        }

        if (empty($AvailableProducts)) {
            $arrResponse  = array('status' =>400 ,'message'=>'Barcode/Sku not found.');
            echo json_encode($arrResponse);
            exit;
        } else {
            $customer_id = $this->session->userdata('LoginID');
            $product_id= $AvailableProducts->id;
            $parent_id= $AvailableProducts->parent_id;

            $postArr = array(
                    'product_id'=>$product_id,
                    'parent_id'=>$parent_id,
                    'customer_id'=>isset($customer_id) ? $customer_id : '',
                    'product_name'=>(isset($AvailableProducts->name)) ? $AvailableProducts->name : '',
                    'barcode'=>(isset($AvailableProducts->barcode)) ? $AvailableProducts->barcode : '',
                    'sku'=>(isset($AvailableProducts->sku)) ? $AvailableProducts->sku : '',
                    'variants'=>(isset($AvailableProducts->variants)) ? $AvailableProducts->variants : '',
                    'launch_date'=>(isset($AvailableProducts->launch_date)) ? $AvailableProducts->launch_date : '',
                    'qty_scanned'=>(isset($prod_qty)) ? $prod_qty : '',
                    'webshop_price'=>(isset($AvailableProducts->webshop_price)) ? $AvailableProducts->webshop_price : '',
                    // 'price'=>(isset($AvailableProducts->price)) ? $AvailableProducts->price : '',
                );
            $response = ProductRepository::insert_scanned_products($shopcode, $shop_id, $postArr);
            // print_r($response);die();
            $AvailableProducts='';
            if (!empty($response) && (isset($response) && $response->is_success=='true')) {
                $arrResponse  = array('status' =>200 ,'message'=>'Barcode scanned successfully.');
                echo json_encode($arrResponse);
                exit;
            } else {
                $arrResponse  = array('status' =>400 ,'message'=>'Problem while scanning.');
                echo json_encode($arrResponse);
                exit;
            }
        }
    }

    public function exportCSV()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $LoginID = $_SESSION['LoginID'];
        $postArr = array('customer_id'=>$LoginID);
        $response = ProductRepository::scanned_products_listing_new($shopcode, $shop_id, $postArr);

        $scanned_productsData= array();
        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $scanned_productsData = $response->scanned_productsData;
            // $data['scanned_products_count'] = $response->scanned_products_count;
        }
        // print_r($scanned_productsData);die();
        $price='';
        foreach ($scanned_productsData as $scanned_product) {
            $SingleRow =array(
                    $scanned_product->barcode,
                    $scanned_product->product_name,
                    $scanned_product->sku,
                    $scanned_product->variants,
                    date("d/m/Y", $scanned_product->launch_date),
                    $scanned_product->qty_scanned,
                    $scanned_product->webshop_price,
                    $price
                );

            $ExportValuesArr[]=$SingleRow;
        }
        // print_r($ExportValuesArr);die();

        // file name
        $filename = "SacnnedProductsCSV.csv";
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");

        $sis_export_header =array("UPC","product_name","sku","variants","launch_date","quantity","retail_price","price");

        // file creation
        $file = fopen('php://output', 'w');
        fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM !!!!!
        fputcsv($file, $sis_export_header);

        if (isset($scanned_productsData) && count($scanned_productsData)>0) {
            foreach ($ExportValuesArr as $readData) {
                fputcsv($file, $readData);
            }
        }
        fclose($file);
        //readfile ($filename);
        $this->deleteALLScannedProduct();
        // header('Location: http://customer/login');
        // echo '<script type="text/JavaScript"> location.reload(); </script>';
        // redirect(BASE_URL.'catlog-builder/scanning');
        exit();
    }

    public function deleteALLScannedProduct()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $LoginID = $_SESSION['LoginID'];
        $postArr = array(
                                'customer_id'=>$LoginID
                            );
        // $response= $this->restapi->post_method($apiUrl,$postArr);
        $response = ProductRepository::deleteAllScannedProduct($shopcode, $shop_id, $postArr);
        // redirect(BASE_URL.'customer/login');
            // echo '<script type="text/JavaScript"> location.reload(); </script>';
    }


    public function loadScannedProductsajax()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $LoginID = $_SESSION['LoginID'];
        /*$apiUrl = "/webshop/scanned_products_listing/$shopcode/$shop_id/$LoginID"; //get_table_data
        $postArr = array();
        $response= $this->restapi->get_method($apiUrl,$postArr);*/

        /*new post method */
        $postArr = array('customer_id'=>$LoginID);
        $response = ProductRepository::scanned_products_listing_new($shopcode, $shop_id, $postArr);
        /*end new post method */


        $scanned_productsData= array();
        $scanned_products_count=0;
        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $scanned_productsData = $response->scanned_productsData;
            $scanned_products_count = $response->scanned_products_count;
        }
        // print_r($scanned_products_count);
        // print_r($scanned_productsData);die();
        // $inboundList =  $this->InboundModel->getAllInboundSavedProductsById($order_id);

        //$total_count =  count( $this->InboundModel->getAllInboundSavedProductsById($order_id));

        // $total_count = (is_array($inboundList)) ? count($inboundList) : 0;
        $data = array();
        $no = isset($_POST['start']) ? $_POST['start'] : 0;

        if (is_array($scanned_productsData)) {
            foreach ($scanned_productsData as $readData) {

                // $checkbox = ($readData['restock_status'] == 1)?"checked":"";
                // $check_restock = '<div class="switch-onoff"><label class="checkbox"><input name="restock_'.$readData['product_id'].'" type="checkbox" '.$checkbox .' autocomplete="off"><span class="checked"></span></label>
                // </div>';
                // $location = $readData['location'];


                // $variants_html = '';
                // $product_variants=$readData['product_variant'];
                // if(isset($product_variants) && $product_variants!=''){
                // 	$variants=json_decode($product_variants, true);
                // 	if(isset($variants) && count($variants)>0){


                // 		foreach($variants as $pk=>$single_variant){
                // 			foreach($single_variant as $key=>$val){

                // 			$variants_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';

                // 			}
                // 		}
                // 	}
                //   }else{
                // 	 $variants_html='-';
                //   }


                $no++;
                $row = array();
                $row[]=$readData->barcode;
                $row[]='<input class="form-control" name="product_ids[]" type="hidden" value="'.$readData->product_name.'" placeholder="Enter Location">'. $readData->product_name;
                $row[]=$readData->sku;
                $row[]=$readData->variants;
                $row[]=date("d/m/Y", $readData->launch_date);
                //$row[]=$readData['qty_scanned'];
                $row[]='<input id="qty_scanned_'.$readData->id.'" name="qty_scanned_'.$readData->id.'" class="form-control enter-location-field location-input" type="text" value="'.$readData->qty_scanned.'" placeholder="Enter Qty Scanned" onfocusout="updateQtyScanned('.$readData->id.')">';

                $row[]=$readData->webshop_price;
                $row[]='<a class="link-purple" onclick="deleteScannedProduct('.$readData->id.')" href="javascript:void(0)">Delete</a>';
                // $row[]=$readData['qty_scanned']*$readData['webshop_price'];
                // $row[]='<input id="location_'.$readData['product_id'].'" name="location_'.$readData['product_id'].'" class="form-control enter-location-field location-input" type="text" value="'.$location.'" placeholder="Enter Location" onfocusout="updateLocation('.$readData['product_id'].')">';
                // $row[]=$check_restock;
                // $row[]=$readData['product_inv_type'];
                // $row[]='<a class="link-purple" onclick="deleteInboundProducts('.$readData['product_id'].','.$order_id.')" href="javascript:void(0)">Delete</a>';

                $data[] = $row;
            }
        }

        // $TotalCount = $this->InboundModel->getTotalQtyScannedAndPrice($order_id);
        //uncomment for edit qty n work
        // $odr_update=array(
        // 	'total_products'=>$TotalCount->qty_scanned,
        // 	'total_price'=>$TotalCount->price,
        // );
        // $where_arr=array('id'=>$order_id);
        // $this->InboundModel->updateData('inbound',$where_arr,$odr_update);



        $output = array(
                        "draw" => isset($_POST['draw']) ? $_POST['draw'] : '' ,
                        "recordsTotal" => $scanned_products_count,
                        "recordsFiltered" =>$scanned_products_count,
                        "data" => $data,
                );

        //output to json format
        echo json_encode($output);
    }

    public function loadcatloglbuilderlistajax()
    {
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        $LoginID = $_SESSION['LoginID'];

        $apiUrl2 = '/webshop/get_table_data'; //get_catlog_builder list
        $table = 'catalog_builder';
        $flag = 'own';
        $order_by = 'ORDER BY id DESC';
        $where = 'customer_id  = ?';
        $params = array($LoginID);
        $postArr2 = array('table_name'=>$table,'database_flag'=>$flag,'where'=>$where,'order_by'=>$order_by,'params'=>$params);
        $response2 = CommonRepository::get_table_data($shopcode, $shop_id, $postArr2);
        // echo '<pre>';print_r($response2);//exit;
        $catloglbuilderlistData= array();
        $catloglbuilderlistData_count=0;
        if (!empty($response2) && (isset($response2) && $response2->is_success=='true')) {
            $catloglbuilderlistData = $response2->tableData;
            $catloglbuilderlistData_count= (is_array($catloglbuilderlistData)) ? count($catloglbuilderlistData) : 0;
        }


        $data = array();
        $no = isset($_POST['start']) ? $_POST['start'] : 0;


        $key= $this->config->item('encryption_key');
        $this->encryption->initialize(array('driver' => 'mcrypt'));

        if (is_array($catloglbuilderlistData)) {
            foreach ($catloglbuilderlistData as $readData) {
                $no++;
                $enc_id = $this->encryption->encrypt($readData->id);
                $enc_id_url_safe = strtr($enc_id, array('+' => '.', '=' => '-', '/' => '~'));
                $row = array();
                $row[]=$readData->catalog_name;
                $row[]=$readData->customer_name;
                $row[]=$readData->email;
                $row[]=$readData->phone_no;
                $row[]=date("d-m-Y", $readData->created_at);
                $row[]='<a target="_blank" class="link-purple" href="'.base_url().'customer/upc-catlog/'.$enc_id_url_safe.'">view</a>';

                $data[] = $row;
            }
        }

        $output = array(
                        "draw" => isset($_POST['draw']) ? $_POST['draw'] : 0 ,
                        "recordsTotal" => $catloglbuilderlistData_count,
                        "recordsFiltered" =>$catloglbuilderlistData_count,
                        "data" => $data,
                );

        //output to json format
        echo json_encode($output);
    }

    public function updateQtyScanned()
    {
        // print_r($_POST);exit;
        if (isset($_POST)) {
            if (empty($_POST['customer_id']) || empty($_POST['product_id']) || empty($_POST['qty_scanned'])) {
                echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
                exit;
            }
            $customer_id = $_POST['customer_id'];
            $product_id = $_POST['product_id'];
            $qty_scanned = $_POST['qty_scanned'];
            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;
            $LoginID = $_SESSION['LoginID'];

            $postArr = array(
                                'customer_id'=>$customer_id,
                                'id'=>$product_id,
                                'qty_scanned'=>$qty_scanned
                            );
            $response = ProductRepository::update_scanned_qty($shopcode, $shop_id, $postArr);
            // echo '<pre>';print_r($response);exit;
            $message = $response->message;
            if (!empty($response) && (isset($response) && $response->is_success=='true')) {
                echo json_encode(array('flag'=>1, 'msg'=>$message));
                exit;
            } else {
                echo json_encode(array('flag'=>0, 'msg'=>$message));
                exit;
            }
        } else {
            $arrResponse  = array('status' =>400 ,'message'=>'Error while updating Qty.');
            echo json_encode($arrResponse);
            exit;
        }
    }

    public function deleteScannedProduct()
    {
        // print_r($_POST);exit;
        if (isset($_POST)) {
            if (empty($_POST['id'])) {
                echo json_encode(array('flag'=>0, 'msg'=>"Please enter all mandatory / compulsory fields."));
                exit;
            }
            $id = $_POST['id'];
            $shopcode = SHOPCODE;
            $shop_id = SHOP_ID;
            $LoginID = $_SESSION['LoginID'];

            $postArr = array(
                                'id'=>$id
                            );
            $response = ProductRepository::deleteScannedProduct($shopcode, $shop_id, $postArr);
            // echo '<pre>';print_r($response);exit;
            $message = $response->message;
            if (!empty($response) && (isset($response) && $response->is_success=='true')) {
                echo json_encode(array('flag'=>1, 'msg'=>$message));
                exit;
            } else {
                echo json_encode(array('flag'=>0, 'msg'=>$message));
                exit;
            }
        } else {
            $arrResponse  = array('status' =>400 ,'message'=>'Error while updating Qty.');
            echo json_encode($arrResponse);
            exit;
        }
    }


    public function create_catlog()
    {
        $data['PageTitle']= 'My Profile - Special Features';
        $LoginID = $_SESSION['LoginID'];

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $data['side_tab'] = 'special_features';

        $postArr = array('customer_id'=>$LoginID);
        $response = ProductRepository::scanned_products_listing_new($shopcode, $shop_id, $postArr);

        if (!empty($response) && (isset($response) && $response->is_success=='true')) {
            $data['customerData'] = $response->customerData;
            $data['profilePercentage'] = $response->profile_percentage;
        }

        $currency_code='';
		$shopDataflag = GlobalRepository::get_fbc_users_shop()?->result;
        if (!empty($shopDataflag)) {
            $data['currency_code']= $currency_code = $shopDataflag->currency_code;
        } else {
            $data['currency_code']='';
        }
        if (isset($currency_code) && $currency_code!='') {
            $table = 'country_master';
            $flag = 'main';
            $where = 'currency_code  = ?';
            $params = array($currency_code);
            $post_currency_name_arr = array('table_name'=>$table,'database_flag'=>$flag,'where'=>$where,'params'=>$params);
            $currency_name_response = CommonRepository::get_table_data($shopcode, $shop_id, $post_currency_name_arr, 3600);
            if (!empty($currency_name_response) && (isset($currency_name_response) && isset($currency_name_response->tableData[0]))) {
                $data['currency_name']= $currency_code = $currency_name_response->tableData[0]->currency_name;
            } else {
                $data['currency_name']='';
            }
        }
        // print_r($data['currency_name']);die();
        $this->template->load('myprofile/my-profile-upc-catlog-create', $data);
    }

    public function submit_catlog()
    {
        $LoginID = $_SESSION['LoginID'];
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;
        if (!empty($_FILES['upc_file']['name'])) {
            $appne_msg='';
            $allowed =  array('csv'); //you can mentions all the allowed file format you need to accept, like .jpg, gif.
            $filename = $_FILES['upc_file']['name']; // csv_file is the file name on the form

            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            if (!in_array($ext, $allowed)) {
                $appne_msg.=" Please Upload Files With .CSV Extenion Only.";
                $arrResponse  = array('status' =>400 ,'flag'=> 0,'msg'=>$appne_msg);
                echo json_encode($arrResponse);
                exit;
            }
            $upc_not_found= array();
            $product_data=array();

            $file_data = fopen($_FILES['upc_file']['tmp_name'], 'r');

            $count=0;
            $headers='';
            while ($row = fgetcsv($file_data)) {
                $headers = $row;
                if ($count==0) {
                    break;
                }
                $count++;
            }
            while ($row = fgetcsv($file_data)) {
                $product_data[] = $row;
            }
            // echo "<pre>";print_r($headers);
            $headers = array_map('strtolower', $headers);
            // echo "<pre>";print_r($yourArray);die();

            $show_csv_qtys=0;
            $show_csv_price=0;
            if (in_array('quantity', $headers)) {
                $show_csv_qtys=1;
            }
            if (in_array('price', $headers)) {
                $show_csv_price=1;
            }



            if ($appne_msg !='') {
                $arrResponse  = array('status' =>400 ,'flag' => 0 ,'msg'=>$appne_msg);
                echo json_encode($arrResponse);
                exit;
            } else {
                if (isset($_POST)) {//check $data added condition here
                    $tmp = sys_get_temp_dir();
                    $csv_upload_path = $tmp;
                    $config["upload_path"] = $csv_upload_path;
                    $config["allowed_types"] = 'csv';
                    $this->load->library('upload', $config);
                    $csv_file_name='';
                    if ($this->upload->do_upload('upc_file')) {
                        $data = $this->upload->data();
                        $csv_file_name=$data["file_name"];
                    }



                    if (!isset($csv_file_name) && $csv_file_name =='') {
                        $redirect = base_url('customer/upc-catlog-listing');
                        $arrResponse  = array('status' =>500 ,'msg'=>'error','redirect' =>$redirect);
                        echo json_encode($arrResponse);
                        exit;
                    }
                    $time = time();
                    $catalog_name = (isset($_POST['catalog_name']) && $_POST['catalog_name']!='') ? $_POST['catalog_name'] : 'catlog-builder-'.$LoginID.'-'.$time.'.csv';
                    $shopcode = SHOPCODE;
                    $shop_id = SHOP_ID;
                    $LoginID = $_SESSION['LoginID'];

                    $postArr = array(
                                                    'customer_id'=>$LoginID,
                                                    'csv_file'=>(isset($csv_file_name) && $csv_file_name!='')? $csv_file_name :'',
                                                    'catalog_name'=>$catalog_name,
                                                    'customer_name'=>isset($_POST['customer_name']) ? $_POST['customer_name'] : '',
                                                    'email'=>isset($_POST['email']) ? $_POST['email'] : '',
                                                    'phone_no'=>isset($_POST['phone_no']) ? $_POST['phone_no'] : '',
                                                    'show_qtys'=>isset($_POST['show_qtys']) ? 1 : 0 ,
                                                    'show_retail_price'=>isset($_POST['show_retail_price']) ? 1 : 0,
                                                    'show_coll_name'=>isset($_POST['show_coll_name']) ? 1 : 0 ,
                                                    'show_style_code'=>isset($_POST['show_style_code']) ? 1 : 0 ,
                                                    'show_upc'=>isset($_POST['show_upc']) ? 1 : 0 ,
                                                    'show_csv_qtys'=>(isset($show_csv_qtys) && $show_csv_qtys != 0) ? 1 : 0 ,
                                                    'show_csv_price'=>(isset($show_csv_price) && $show_csv_price != 0)  ? 1 : 0 ,
                                                    'sort_by'=>isset($_POST['sort_by']) ? $_POST['sort_by'] : '' ,
                                                    'display_currency'=>isset($_POST['display_currency']) ? $_POST['display_currency'] : '' ,
                                                    'created_by'=>isset($LoginID) ? $LoginID : ''
                                                );
                    // print_r($postArr);die();
                    $response = ProductRepository::add_to_catlog_builder($shopcode, $shop_id, $postArr);
                    //echo '<pre>';print_r($response);exit;
                    $message = $response->message;
                    $lastInsertId= $response->lastInsertId;
                    //insert in catlobuilder_items

                    $CSVValidItemsExistsFlag = 0;
                    $CSVInvalidValidItemsAry = array();

                    if (!empty($response) && (isset($response) && $response->is_success=='true')) {
                        if (isset($product_data) && count($product_data)>=1) {
                            foreach ($product_data as $index=>$product) {
                                $upc=$product[0];
                                $apiUrl2 = '/webshop/get_product_data'; //get_table_data
                                $postArr2 = array('shopcode'=>$shopcode,'barcode'=>$upc);
                                $response2= $this->restapi->post_method($apiUrl2, $postArr2);
                                // echo '<pre>';print_r($response2);exit;
                                if (!empty($response2) && (isset($response2) && $response2->is_success=='true')) {
                                    $singeProductData1 = $response2->product_data[0];
                                    if (in_array("product_name", $headers)) {
                                        $key = array_search('product_name', $headers);
                                        $product_name= $product[$key];
                                    }
                                    if (in_array("sku", $headers)) {
                                        $key = array_search('sku', $headers);
                                        $sku= $product[$key];
                                    }
                                    if (in_array("variants", $headers)) {
                                        $key = array_search('variants', $headers);
                                        $variants= $product[$key];
                                    }
                                    if (in_array("launch_date", $headers)) {
                                        $key = array_search('launch_date', $headers);
                                        $launch_date= $product[$key];
                                    }
                                    if (in_array("quantity", $headers)) {
                                        $key = array_search('quantity', $headers);
                                        $quantity= $product[$key];
                                    }
                                    if (in_array("retail_price", $headers)) {
                                        $key = array_search('retail_price', $headers);
                                        $retail_price= $product[$key];
                                        if (isset($retail_price) && $retail_price !='') {
                                            $retail_price= str_replace(",", ".", $retail_price);
                                        }
                                    }
                                    if (in_array("price", $headers)) {
                                        $key = array_search('price', $headers);
                                        $price= $product[$key];
                                        if (isset($price) && $price !='') {
                                            $price= str_replace(",", ".", $price);
                                        }
                                    }

                                    $postArr = array(

                                                        'catalog_builder_id'=>$lastInsertId,
                                                        'upc'=>(isset($upc) && $upc!='') ? $upc :'',
                                                        'product_id'=>isset($singeProductData1->id) ? $singeProductData1->id : '',
                                                        'parent_id'=>isset($singeProductData1->parent_id) ? $singeProductData1->parent_id : '',
                                                        'product_name'=>isset($product_name) ? $product_name : '',
                                                        'sku'=>isset($sku) ? $sku : '',
                                                        'variants'=>isset($variants) ? $variants : '' ,
                                                        'launch_date'=>isset($launch_date) ? strtotime($launch_date) : '',
                                                        'quantity'=>isset($quantity) ? $quantity : '',
                                                        'retail_price'=>isset($retail_price) ? $retail_price : '',
                                                        'price'=>isset($price) ? $price : ''
                                                                    );

                                    $response = ProductRepository::add_to_catlog_builder_items($shopcode, $shop_id, $postArr);

                                    $CSVValidItemsExistsFlag = 1;
                                } else {
                                    $CSVInvalidValidItemsAry[] = $upc;
                                }
                            }
                            if ($CSVValidItemsExistsFlag == 0) {

                                            //Delete from catalogbuilder where id=$lastInsertId
                                $postArr = array(
                                                'catlog_id'=>$lastInsertId
                                                );

                                $response = SpecialFeaturesRepository::catlog_builder_delete($shopcode, $postArr);

                                echo json_encode(array('flag'=>0, 'msg'=>"All the UPCs that you have uploaded does not exists in our database.
											Please upload the csv file with valid UPCs"));
                                exit;
                            } elseif ($CSVValidItemsExistsFlag == 1 && count($CSVInvalidValidItemsAry) > 0) {
                                $CSVInvalidValidItemsStr = implode(', ', $CSVInvalidValidItemsAry);

                                $message = 'Catalog created successfully.
											Please Note: All the UPCs that we have listed below are not uploaded because they does not exists in our database.'.$CSVInvalidValidItemsStr;
                            } else {
                                $message = 'Catalog created successfully.';
                            }
                        }

                        $redirect= base_url('customer/upc-catlog-listing');
                        echo json_encode(array('flag'=>1, 'msg'=>$message,'redirect'=>$redirect));
                        exit;
                    // $arrResponse  = array('status' =>200 ,'message'=>'Qty updated.', 'total_price'=>$price, 'total_products'=>$products);
                                    // echo json_encode($arrResponse);exit;
                    } else {
                        echo json_encode(array('flag'=>0, 'msg'=>$message));
                        exit;
                        // $arrResponse  = array('status' =>400 ,'message'=>'Error while updating Qty.');
                                    // echo json_encode($arrResponse);exit;
                    }
                } else {
                    $redirect = base_url('customer/upc-catlog-listing');
                    $arrResponse  = array('status' =>500 ,'msg'=>'error','redirect' =>$redirect);
                    echo json_encode($arrResponse);
                    exit;
                }
            }
        } else {
            $arrResponse  = array('status' =>400 ,'flag' => 0,'msg'=>'Please upload proper csv file');
            echo json_encode($arrResponse);
            exit;
        }
    }

    public function upc_catlog_view()
    {
        $data['PageTitle']= 'My Profile - Special Features';
        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        $key= $this->config->item('encryption_key');
        $this->encryption->initialize(array('driver' => 'mcrypt'));

        $data['side_tab'] = 'special_features';
        $enc_id_url_safe=$this->uri->segment(3);
        $enc_id = strtr($enc_id_url_safe, array('.' => '+', '-' => '=', '~' => '/'));
        $catlog_id = $this->encryption->decrypt($enc_id);

        $table = 'catalog_builder';
        $flag = 'own';
        $where = 'id  = ?';
        $params = array($catlog_id);
        $post_arr = array('table_name'=>$table,'database_flag'=>$flag,'where'=>$where,'params'=>$params);
        $catloglbuilderData = CommonRepository::get_table_data($shopcode, $shop_id, $post_arr);
        if (!empty($catloglbuilderData) && (isset($catloglbuilderData) && $catloglbuilderData->is_success=='false')) {
            redirect(BASE_URL('404_override'));
        } else {
            $currency_code='';

			$shopDataflag = GlobalRepository::get_fbc_users_shop()?->result;
            if (!empty($shopDataflag)) {
                $data['currency_code']= $currency_code = $shopDataflag->currency_code;
            } else {
                $data['currency_code']='';
            }
            if (isset($currency_code) && $currency_code!='') {
                $table = 'country_master';
                $flag = 'main';
                $where = 'currency_code  = ?';
                $params = array($currency_code);
                $post_currency_symbol_arr = array('table_name'=>$table,'database_flag'=>$flag,'where'=>$where,'params'=>$params);
                $currency_symbol_response =  CommonRepository::get_table_data($shopcode, $shop_id, $post_currency_symbol_arr, 3600);
                if (!empty($currency_symbol_response) && (isset($currency_symbol_response) && isset($currency_symbol_response->tableData[0]))) {
                    $data['currency_symbol']= $currency_symbol = $currency_symbol_response->tableData[0]->currency_symbol;
                } else {
                    $data['currency_symbol']='';
                }
            }


            if ($catloglbuilderData->tableData[0]) {
                $data['catloglbuilderData']=$catloglbuilderData->tableData[0];
            } else {
                $data['catloglbuilderData']='';
            }

            $table2 = 'catalog_builder_items';
            $flag2 = 'own';
            $where2= 'catalog_builder_id  = ?';
            $params2 = array($catlog_id);
            $post_arr2 = array('table_name'=>$table2,'database_flag'=>$flag2,'where'=>$where2,'params'=>$params2);
            $catloglbuilderitemsDataResponse = CommonRepository::get_table_data($shopcode, $shop_id, $post_arr2);
            if (!empty($catloglbuilderitemsDataResponse) && (isset($catloglbuilderitemsDataResponse) && isset($catloglbuilderitemsDataResponse->tableData))) {
                $data['catloglbuilderitemsData']=$catloglbuilderitemsData = $catloglbuilderitemsDataResponse->tableData;
            } else {
                $data['catloglbuilderitemsData']='';
            }

            $product_ids= array();
            if (!empty($catloglbuilderitemsData) && $catloglbuilderitemsData !='') {
                foreach ($catloglbuilderitemsData as $item) {
                    if ($item->parent_id == 0) {
                        if (!in_array($item->product_id, $product_ids)) {
                            array_push($product_ids, $item->product_id);
                        }
                    } else {
                        if (!in_array($item->parent_id, $product_ids)) {
                            array_push($product_ids, $item->parent_id);
                        }
                    }
                }
            }


            $product_ids_str = implode(',', $product_ids);

            if ($catloglbuilderData->tableData[0]->sort_by == 0) {
                $navCatApiUrl = '/webshop/get_shop_categories_new'; //browse by category

                $navCatArr = array('catlog_id'=> $catlog_id,'product_ids_str'=>$product_ids_str);
                // print_r($navCatArr);
                $shop_category = SpecialFeaturesRepository::get_shop_categories_new($shopcode, $shop_id, $navCatArr);
                if (!empty($shop_category) && (isset($shop_category) && $shop_category->statusCode=='200')) {
                    $data['shop_category'] = $shop_category->AllCategoryLevels;
                } else {
                    $data['shop_category'] = '';
                }
            }

            // echo "<pre>";
            // print_r($data['shop_category']);die();

            $this->template->load('myprofile/my-profile-upc-catlog-view', $data);
        }
    }
}
