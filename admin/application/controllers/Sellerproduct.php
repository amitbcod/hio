<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sellerproduct extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		if ($this->session->userdata('LoginID') == '') {
			redirect(base_url());
		}

		$this->load->model('CategoryModel');
		$this->load->model('EavAttributesModel');
		$this->load->model('SellerProductModel');
		// $this->load->model('B2BImportModel');
		// $this->load->model('InboundModel');
		//$this->load->model('Multi_Languages_Model');
		$this->load->library('image_lib');
		// $this->load->library('pagination');
		//$this->load->library('Image_upload');
	}
	public function index()
	{
		if ($_SESSION['UserRole'] !== 'Super Admin') {
			if (!empty($this->session->userdata('userPermission')) && !in_array('database/product', $this->session->userdata('userPermission'))) {
				// redirect('dashboard');
			}
		}

		$data['PageTitle'] = 'Warehouse';
		$data['side_menu'] = 'warehouse';
		$this->load->view('seller/products/main', $data);
	}

	function set_upload_options()
	{
		// upload an image options
		$config = array();
		$config['upload_path'] = $_SERVER['DOCUMENT_ROOT'] . '/indiamags/uploads';
		$config['remove_spaces'] = TRUE;
		$config['encrypt_name'] = TRUE; // for encrypting the name
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size'] = '78000';
		$config['overwrite'] = FALSE;
		return $config;
	}

	public function productInventoryAdjustment()
	{
		$data['PageTitle'] = 'product Inventory Adjustment';
		$data['side_menu'] = 'overview';
		$this->load->view('seller/products/simple-overview-page', $data);
	}
	public function loadproductInventoryAdjustmentAjax()
	{
		$overview_listing = $this->SellerProductModel->get_datatables_products_adjustemnt();
		$data = array();
		foreach ($overview_listing as $readData) {
			$row  = array();
			$row[] = $readData->id;
			$row[] = $readData->sku;
			$row[] = $readData->name;
			$row[] = $readData->type;
			$row[] = $readData->adjustment;
			$row[] = $readData->processed;
			$row[] = $readData->source;
			$data[] = $row;
		}
		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->SellerProductModel->countProductInventoryAdjustmentRow(),
			"recordsFiltered" => $this->SellerProductModel->countFiltteredProductInventory(),
			"data" => $data,
		);
		echo json_encode($output);
		exit;
	}

	public function dropship()
	{
		$data['PageTitle'] = 'Dropship';
		$data['side_menu'] = 'dropship';

		$this->load->view('seller/products/dropship', $data);
	}

	function loadproductsajax()
	{
		$input_price  = $this->input->post('price');
		$inventory    = $this->input->post('inventory');
		$supplier     = $this->input->post('supplier');
		$image_filter = (($this->input->post('image_filter')) != '') ? $this->input->post('image_filter') : '-';
		$fromDate     = $this->input->post('from_date');
		$toDate       = $this->input->post('to_date');
		
		// ✅ new: approval_status filter from JS
		$approval_status = $this->input->post('approval_status');

		$count = '';
		$ProductData = $this->SellerProductModel->get_datatables_products(
			$input_price,
			$inventory,
			$supplier,
			$fromDate,
			$toDate,
			$count,
			$image_filter,
			$approval_status // ✅ pass to model
		);

		$data = array();
		$no = $_POST['start'];
		foreach ($ProductData as $readData) {
			$no++;
			$row = array();
			$qty = '';

			if ($readData->product_type == 'configurable') {
				$Row = $this->SellerProductModel->getStockForConfigProduct($readData->id);
				$qty = ($Row) ? $Row->qty : '-';
				$price = '-';
				$webshop_price = '-';
			} else {
				$qty = $readData->qty;
				$price = number_format($readData->price, 2);
				$webshop_price = number_format($readData->webshop_price, 2);
			}

			$cat_name = $this->SellerProductModel->getProductsMaintCategoryNames($readData->id);
			if ($approval_status === 'pending') {
				$row[] = '<input type="checkbox" class="product-checkbox" value="' . $readData->id . '">';
			}
			$row[] = $readData->name;
			$row[] = $cat_name;
			$row[] = $readData->product_code;
			$row[] = $qty;
			$row[] = $price;
			$row[] = $webshop_price;
			$row[] = (isset($readData->updated_at)) ? date(SIS_DATE_FM, $readData->updated_at) : date(SIS_DATE_FM, $readData->created_at);
			$row[] = '<a class="link-purple" href="' . base_url() . 'seller/product/edit/' . $readData->id . '">View</a>';

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->SellerProductModel->get_datatables_products(
				$input_price,
				$inventory,
				$supplier,
				$fromDate,
				$toDate,
				'count',
				$image_filter,
				$approval_status // ✅ also here
			),
			"recordsFiltered" => $this->SellerProductModel->get_datatables_products_all_count(
				$input_price,
				$inventory,
				$supplier,
				$fromDate,
				$toDate,
				$image_filter,
				$approval_status // ✅ also here
			),
			"data" => $data,
		);

		echo json_encode($output);
		exit;
	}

	public function updateStatuses()
	{
		$updates = $this->input->post('updates');
		$success = true;

		if (!empty($updates) && is_array($updates)) {
			foreach ($updates as $u) {
				$productId = (int)$u['id'];
				$status    = $u['status'];

				// ✅ Map string to int
				if ($status === 'approve') {
					$statusValue = 1;
				} elseif ($status === 'reject') {
					$statusValue = 2;
				} else {
					$statusValue = 0; // pending
				}

				$res1 = $this->db->where('id', $productId)
								->update('products', ['approval_status' => $statusValue]);

				if (!$res1) {
					$success = false;
				}
			}
		} else {
			$success = false;
		}

		echo json_encode([
			'success'  => $success,
		]);
	}



	function loaddropshipproductsajax()
	{

		$input_price = $this->input->post('price');
		$inventory = $this->input->post('inventory');
		$supplier = $this->input->post('supplier');
		$image_filter = (($this->input->post('image_filter')) != '') ? $this->input->post('image_filter') : '-';
		$fromDate = $this->input->post('from_date');
		$toDate = $this->input->post('to_date');

		$shop_id		=	$this->session->userdata('ShopID');

		$ShopData = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $shop_id), 'currency_symbol,currency_code');
		$currency_symbol = (isset($ShopData->currency_symbol)) ? $ShopData->currency_symbol : $ShopData->currency_code;

		$ProductData = $this->SellerProductModel->get_datatables_dropship_products($input_price, $inventory, $supplier, $fromDate, $toDate, $image_filter);
		// echo "<pre>";
		// print_r($ProductData);
		// die();

		$data = array();
		$no = $_POST['start'];
		foreach ($ProductData as $readData) {
			$no++;
			$row = array();
			$qty = '';
			if ($readData->product_type == 'configurable') {
				$r2 = $this->SellerProductModel->getVariantProducts($readData->id);
				$Row = $this->SellerProductModel->getStockForConfigProduct($readData->id);
				if ($Row) {
					$qty = '--';
				} else {
					$qty = '-';
				}
				$price = '-';
			} else {
				$qty = '--';
				$price = $currency_symbol . ' ' . number_format($readData->price, 2);
			}

			$cat_name = $this->SellerProductModel->getProductsMaintCategoryNames($readData->id);

			$row[] = $readData->name;
			$row[] = $cat_name;
			$row[] = $readData->org_shop_name;
			$row[] = $readData->product_code;
			$row[] = $qty;
			$row[] = $price;
			$row[] = (isset($readData->updated_at)) ? date(SIS_DATE_FM, $readData->updated_at) : date(SIS_DATE_FM, $readData->created_at);
			$row[] = '<a class="link-purple" href="' . base_url() . 'seller/product/edit/' . $readData->id . '">View</a>';

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->SellerProductModel->count_all_dropship_products($input_price, $inventory, $supplier, $fromDate, $toDate, $image_filter),
			"recordsFiltered" => $this->SellerProductModel->count_filtered_dropship_products($input_price, $inventory, $supplier, $fromDate, $toDate, $image_filter),
			"data" => $data,
		);

		//output to json format
		echo json_encode($output);
		exit;
	}

	function add()
	{
		$data['side_menu'] = 'add_product';
		$data['PageTitle'] = 'Add Product';
		$return_identifier = 'product_return_duration';
		$delivery_identifier = 'product_delivery_duration';
		$data['product_return_duration'] = $product_return_duration = $this->CommonModel->getSingleShopDataByID('custom_variables as cv', array('identifier' => $return_identifier), 'cv.*');

		$data['product_delivery_duration'] = $product_delivery_duration = $this->CommonModel->getSingleShopDataByID('custom_variables as cv', array('identifier' => $delivery_identifier), 'cv.*');

		$data['publishers'] = $publishers = $this->CommonModel->getAllPublishers();
		// echo '<pre>';print_r($publishers);exit;

		$data['CustomerTypeMaster'] = $this->SellerProductModel->getMultiDataById('customers_type_master', array(), '');


		$fbc_user_id = $this->session->userdata('LoginID');

		$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag();

		// $shop_upload_path='shop'.$shop_id;

		$data['CategoryTree'] = $this->CategoryModel->get_categories_for_shop();

		$data['publication'] = $this->SellerProductModel->get_publication();


		// print_r($data['publication']);
		// die();

		if (empty($_POST)) {
			$this->load->view('seller/products/add_product', $data);
		} else {

			// echo "<pre>";print_r($_FILES);
			//echo "<pre>";print_r($_POST);
			//exit;

			if (empty($_POST['product_name']) || empty($_POST['product_code']) || empty($_POST['description']) || empty($_POST['highlights'])) {
				//$arrResponse  = array('status' =>403 ,'message'=>'Please enter all mandatory fields!');
				echo json_encode(array('status' => 403, 'message' => "Please enter all mandatory fields!"));
				exit;
			} elseif (empty($_POST['category'])) {
				echo json_encode(array('status' => 403, 'message' => "Please add atleast one category!"));
				exit;
			} elseif (empty($_POST['default_image'])) {
				echo json_encode(array('status' => 403, 'message' => "Please add  Default Image!"));
				exit;
			} elseif (empty($_POST['attributes']) || empty($_POST['added_attr'])) {
				echo json_encode(array('status' => 403, 'message' => "Please add atleast one attributes!"));
				exit;
			} else if (empty($_POST['cost_price']) &&  empty($_POST['variant_cost_price']) && $_POST['product_type'] == 'configurable') {
				echo json_encode(array('status' => 403, 'message' => "Please add atleast one variations"));
				exit;
				//$arrResponse  = array('status' =>403 ,'message'=>'Please add atleast one variations!');
			}
			//else if($_POST['product_type']=='configurable' && $_POST['added_variant']==''){
			// $arrResponse  = array('status' =>403 ,'message'=>'Please add atleast one variations!');
			//}
			else {
				//$PC_Exist=$this->SellerProductModel->getSingleDataByID('products',array('product_code'=>1),'id,name');
				$product_name = $this->CommonModel->custom_filter_input($_POST['product_name']);
				$product_code = $this->CommonModel->custom_filter_input($_POST['product_code']);
				$langTitle = $this->CommonModel->custom_filter_input($_POST['langTitle']);

				$description = $_POST['description'];
				$highlights = $_POST['highlights'];
				$lang_description = $_POST['lang_description'];
				$lang_highlights = $_POST['lang_highlights'];
				$product_reviews_code = $this->CommonModel->custom_filter_input($_POST['product_reviews_code']);
				$launch_date = (isset($_POST['launch_date']) && $_POST['launch_date'] != '') ? strtotime($_POST['launch_date']) : strtotime(date('d-m-Y'));
				$estimate_delivery_time = $this->CommonModel->custom_filter_input($_POST['estimate_delivery_time']);
				$product_return_time = $this->CommonModel->custom_filter_input($_POST['product_return_time']);
				//$product_drop_shipment=$this->CommonModel->custom_filter_input($_POST['product_drop_shipment']);
				$product_type = $this->CommonModel->custom_filter_input($_POST['product_type']);

				$publication_id = $this->CommonModel->custom_filter_input($_POST['product_publication']);
				$type_of_commission = $this->CommonModel->custom_filter_input($_POST['type-of-commission']);
				$pub_com_percentage = $this->CommonModel->custom_filter_input($_POST['pub_com_percentage']);

				if (isset($product_type) && $product_type == 'bundle') {
					$sku = '';
					$barcode = '';
					$price = $this->CommonModel->custom_filter_input($_POST['bundle_price']);
					$cost_price = $price;
					$webshop_price = $this->CommonModel->custom_filter_input($_POST['bundle_webshopprice']);
					$tax_amount = $this->CommonModel->custom_filter_input($_POST['tax_amount']);

					if (isset($_POST['bundle_tax_percent']) && !empty($_POST['bundle_tax_percent'])) {
						$tax_percent = max($_POST['bundle_tax_percent']);
					} else {
						$tax_percent = 0.00;
					}
					$tax_amount = $this->CommonModel->custom_filter_input($_POST['tax_amount']);
				} else {
					$sku = isset($_POST['sku']) ? $this->CommonModel->custom_filter_input($_POST['sku']) : '';
					//commission code
					//$barcode=isset($_POST['sku'])?$this->CommonModel->custom_filter_input($_POST['barcode']):'';
					$stock_qty = isset($_POST['stock_qty']) ? $this->CommonModel->custom_filter_input($_POST['stock_qty']) : '';
					$price = isset($_POST['price']) ? $this->CommonModel->custom_filter_input($_POST['price']) : '';
					$cost_price = isset($_POST['cost_price']) ? $this->CommonModel->custom_filter_input($_POST['cost_price']) : '';
					$tax_percent = isset($_POST['tax_percent']) ? $this->CommonModel->custom_filter_input($_POST['tax_percent']) : '';
					$webshop_price = isset($_POST['webshop_price']) ? $this->CommonModel->custom_filter_input($_POST['webshop_price']) : '';
				}

				$status = $_POST['status'] ?? '1';
				$product_returnable = $_POST['product-return'] ?? '1';
				$coming_product = $_POST['coming-product'];
				$is_fragile = $_POST['is_fragile'];

				if (isset($_POST['prod_location']) && is_array($_POST['prod_location'])) {
					$product_variant_simple = '';
				} else {
					$product_variant_simple = isset($_POST['prod_location']) ? $this->CommonModel->custom_filter_input($_POST['prod_location']) : '';
				}

				$meta_title = isset($_POST['meta_title']) ? $_POST['meta_title'] : '';
				$meta_keyword = isset($_POST['meta_keyword']) ? $_POST['meta_keyword'] : '';
				$meta_description = isset($_POST['meta_description']) ? $_POST['meta_description'] : '';
				$search_keywords = isset($_POST['search_keywords']) ? $_POST['search_keywords'] : '';
				$promo_reference = isset($_POST['promo_reference']) ? $_POST['promo_reference'] : '';
				//$weight=isset($_POST['weight'])?$_POST['weight']:'';
				$sub_issue = isset($_POST['sub_issues']) ? $_POST['sub_issues'] : '';
				$sel_gift = isset($_POST['sel_gifts']) ? $_POST['sel_gifts'] : '';
				if (isset($product_type) && $product_type != 'bundle') {
					if ($product_type == 'simple') {
						if ($Rounded_price_flag == 1) {
							$RowInfo = $this->SellerProductModel->calculate_webshop_price($price, $tax_percent);
							$tax_amount = $RowInfo['tax_amount'];
							$webshop_price = $RowInfo['webshop_price'];
						} else {
							$webshop_price = $price;
							$tax_amount = 0;
							if ($price > 0 && $tax_percent > 0) {
								$tax_amount = ($tax_percent / 100) * $price;
								$webshop_price = $tax_amount + $price;
							}
						}
					} else {
						$webshop_price = 0;
						$tax_amount = 0;
					}
				}
				if (isset($_POST['shipping_amount']) && $_POST['shipping_amount'] > 0) {
					$shipping_amount =  $_POST['shipping_amount'];
				} else {
					$shipping_amount = 0;
				}
				$category = (isset($_POST['category']) && count($_POST['category']) > 0) ? $_POST['category'] : array();
				$sub_category = (isset($_POST['sub_category']) && count($_POST['sub_category']) > 0) ? $_POST['sub_category'] : array();
				$child_category = (isset($_POST['child_category']) && count($_POST['child_category']) > 0) ? $_POST['child_category'] : array();

				$attributes = isset($_POST['attributes']) ? $_POST['attributes'] : array();

				$gender = isset($_POST['gender']) ? $_POST['gender'] : '';

				if (isset($gender) && is_array($gender)) {
					$gender = implode(',', $gender);
				} else {
					$gender = $gender;
				}

				$default_image = (isset($_POST['default_image']) && $_POST['default_image'] != '') ? $_POST['default_image'] : '';


				$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('product_code' => $product_code, 'remove_flag' => 0), 'id,name');
				if (isset($PC_Exist) && $PC_Exist->id != '') {
					$arrResponse  = array('status' => 403, 'message' => 'Product Code exist.');

					/*}else if(empty($default_image) || $default_image==''){
						 $arrResponse  = array('status' =>403 ,'message'=>'Please set at least one default image.');*/
				} else {
					$slug = url_title($product_name);
					$url_key = strtolower($slug);
					//$url_key=$this->SellerProductModel->createproductslug($url_key);

					$slugcount = $this->SellerProductModel->productslugcount($product_name);
					// echo $slugcount;
					// die();
					if ($slugcount > 0) {
						$slugcount = $slugcount + 1;
						$url_key = $url_key . "-" . $slugcount;
					} else {
						$url_key = $url_key . "-0";
					}

					/*-------------------Insert Product :start--------------------------------------*/

					$insertdata = array(
						'name' => $product_name,
						'product_code' => $product_code,
						'lang_title' => $langTitle,

						'url_key' => $url_key,
						'meta_title' => $meta_title,
						'meta_keyword' => $meta_keyword,
						'meta_description' => $meta_description,
						'search_keywords' => $search_keywords,
						'promo_reference' => $promo_reference,
						//'weight'=>$weight,
						'sku' => $sku,
						'price' => $price,  //selling price
						// 'shipping_amount' => $shipping_amount,
						'tax_percent' => $tax_percent,
						'tax_amount' => $tax_amount,
						'webshop_price' => $webshop_price,
						// 'shipping_amount ' => $shipping_amount,
						'cost_price' => $cost_price,
						'gender' => $gender,
						'publisher_id' => $publication_id,
						'pub_com_per_type' => $type_of_commission,
						'pub_com_percent' => $pub_com_percentage,
						'base_image' => $default_image,
						'description' => $description,
						'highlights' => $highlights,

						'lang_description' => $lang_description,
						'lang_highlights' => $lang_highlights,

						'product_reviews_code' => $product_reviews_code,
						'launch_date' => $launch_date,
						'estimate_delivery_time' => $estimate_delivery_time,
						'product_return_time' => $product_return_time,
						'product_type' => $product_type,
						'product_inv_type' => 'buy',
						'status' => $status,
						'can_be_returned' => $product_returnable,
						'coming_soon_flag' => $coming_product,
						'is_fragile_flag' => $is_fragile,
						'created_at' => time(),
						'sub_issues' => $sub_issue,
						'gift_id' => $sel_gift,
						'ip' => $_SERVER['REMOTE_ADDR']
					);
					$product_id = $this->SellerProductModel->insertData('products', $insertdata);
					if ($product_id) {
						if (isset($product_type) && $product_type == 'bundle') {
							/*-------------------Insert products_bundles :start--------------------------------------*/
							if (isset($_POST['bundle_product_id']) && count($_POST['bundle_product_id']) > 0) {
								for ($bcount = 0; $bcount < count($_POST['bundle_product_id']); $bcount++) {
									//$bundle_product_id=$product_id;
									//$bundle_sku=$this->CommonModel->custom_filter_input($_POST['bundle_sku'][$bcount]);
									//$bundle_barcode=$this->CommonModel->custom_filter_input($_POST['bundle_barcode'][$bcount]);
									$item_product_id = $this->CommonModel->custom_filter_input($_POST['bundle_product_id'][$bcount]);
									$bundle_product_parent_id = $this->CommonModel->custom_filter_input($_POST['bundle_product_parent_id'][$bcount]);
									$bundle_product_type = $this->CommonModel->custom_filter_input($_POST['bundle_product_type'][$bcount]);
									$bundle_variant_options_get = $this->CommonModel->custom_filter_input($_POST['bundle_variant_options'][$bcount]);
									if (isset($bundle_variant_options_get) && !empty($bundle_variant_options_get)) {

										//$bundle_variant_options = str_replace("'", '"', $bundle_variant_options_get);
										$bundle_variant_options =  str_replace("'", '"', $_POST['bundle_variant_options'][$bcount]);
									} else {
										$bundle_variant_options = $bundle_variant_options_get;
									}
									$bundle_price = $this->CommonModel->custom_filter_input($_POST['price'][$bcount]);
									$bundle_tax_percent = $this->CommonModel->custom_filter_input($_POST['bundle_tax_percent'][$bcount]);
									$bundle_tax_amount = $this->CommonModel->custom_filter_input($_POST['bundle_tax_amount'][$bcount]);
									$bundle_webshop_price = $this->CommonModel->custom_filter_input($_POST['bundle_webshop_price'][$bcount]);
									$bundle_default_qty = $this->CommonModel->custom_filter_input($_POST['bundle_default_qty'][$bcount]);
									$bundle_position = $this->CommonModel->custom_filter_input($_POST['bundle_position'][$bcount]);


									$insertdata = array(
										'bundle_product_id' => $product_id,
										'product_id' => $item_product_id,
										'product_parent_id' => $bundle_product_parent_id,
										'product_type' => $bundle_product_type,
										'variant_options' => $bundle_variant_options,
										//'sku'=>$bundle_sku,
										//'barcode'=>$bundle_barcode,
										'price' => $bundle_price,
										'tax_percent' => $bundle_tax_percent,
										'tax_amount' => $bundle_tax_amount,
										'webshop_price' => $bundle_webshop_price,
										'default_qty' => $bundle_default_qty,
										'position' => $bundle_position,
										'created_at' => time(),
										'ip' => $_SERVER['REMOTE_ADDR']
									);
									$product_bundle_id = $this->SellerProductModel->insertData('products_bundles', $insertdata);
								}
							}
						} else {
							$stock_insert = array('product_id' => $product_id, 'qty' => $stock_qty, 'available_qty' => $stock_qty, 'min_qty' => 0, 'is_in_stock' => 1);
							$this->SellerProductModel->insertData('products_inventory', $stock_insert);
						}


						/*--------- Categoryinsert start------------------------------------------*/
						if (isset($category) && count($category) > 0) {
							foreach ($category as $cat) {
								$root_cat_insert = array('product_id' => $product_id, 'category_ids' => $cat, 'level' => 0);
								$this->SellerProductModel->insertData('products_category', $root_cat_insert);

								// $checkbtb_level_zero=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cat,'level'=>0),'id');

								// if(empty($checkbtb_level_zero)){
								// $fbc_cat_insert=array('category_id'=>$cat,'level'=>0,'fbc_user_id'=>$fbc_user_id);
								// $this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_cat_insert);
								// }
							}
						}

						if (isset($sub_category) && count($sub_category) > 0) {
							foreach ($sub_category as $cat) {
								$sub_cat_insert = array('product_id' => $product_id, 'category_ids' => $cat, 'level' => 1);
								$this->SellerProductModel->insertData('products_category', $sub_cat_insert);

								// $checkbtb_level_one=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cat,'level'=>1),'id');
								// if(empty($checkbtb_level_one)){
								// 	$fbc_subcat_insert=array('category_id'=>$cat,'level'=>1,'fbc_user_id'=>$fbc_user_id);
								// 	$this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_subcat_insert);
								// }
							}
						}

						if (isset($child_category) && count($child_category) > 0) {
							foreach ($child_category as $cat) {
								$child_cat_insert = array('product_id' => $product_id, 'category_ids' => $cat, 'level' => 2);
								$this->SellerProductModel->insertData('products_category', $child_cat_insert);

								/*$checkbtb_level_two = $this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b', array('category_id' => $cat, 'level' => 2), 'id');
								if (empty($checkbtb_level_two)) {
									$fbc_subcat_insert = array('category_id' => $cat, 'level' => 2, 'fbc_user_id' => $fbc_user_id);
									$this->SellerProductModel->insertData('fbc_users_category_b2b', $fbc_subcat_insert);
								}*/
							}
						}

						/*------------------------------------------------*/



						if (isset($attributes) && count($attributes) > 0) {
							foreach ($attributes as $attr_id => $attr_value) {
								$AttrData = $this->CommonModel->getSingleDataByID('eav_attributes', array('id' => $attr_id), '');
								$attr_properties = $AttrData->attr_properties;
								if ($attr_properties == 6) {
									if (isset($attr_value) && count($attr_value) > 0) {
										$attr_value = implode(',', $attr_value);
									} else {
										$attr_value = '';
									}
								}

								$attr_insert = array('product_id' => $product_id, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
								$this->SellerProductModel->insertData('products_attributes', $attr_insert);
							}
						}

						$arrResponse  = array('status' => 200, 'message' => 'Product added successfully.');

						$base_image = '';

						if (isset($product_type) && $product_type != 'bundle') { // start new bundle condition
							if ($product_type == 'simple') {

								//$barcode_update=array('barcode'=>$barcode,'updated_at'=>time());
								//$where_arr=array('id'=>$product_id);

								//$this->SellerProductModel->updateData('products',$where_arr,$barcode_update);

							} else if ($product_type == 'configurable') {

								$added_variant_arr = array();
								$added_variant = $_POST['added_variant'];

								if (strpos($added_variant, ',') !== false) {
									$added_variant_arr = explode(',', $added_variant);
								} else {
									$added_variant_arr[] = $added_variant;
								}

								if (isset($added_variant_arr) && count($added_variant_arr) > 0) {
									$VariantList = $this->EavAttributesModel->getVariantDataByIds($added_variant_arr);
									if (isset($VariantList) && count($VariantList) > 0) {
										foreach ($VariantList as $attr) {
											$attr_id = $attr['id'];
											$pvm_insert = array('product_id' => $product_id, 'attr_id' => $attr_id, 'position' => 0);
											$this->SellerProductModel->insertData('products_variants_master', $pvm_insert);
										}
									}
								}


								$variant_sku_arr = isset($_POST['variant_sku']) ? $_POST['variant_sku'] : array();
								$variant_stock_arr = isset($_POST['variant_stock']) ? $_POST['variant_stock'] : array();
								$variant_cost_price_arr = isset($_POST['variant_cost_price']) ? $_POST['variant_cost_price'] : array();
								$variant_price_arr = isset($_POST['variant_price']) ? $_POST['variant_price'] : array();
								$variant_tax_percent_arr = isset($_POST['variant_tax_percent']) ? $_POST['variant_tax_percent'] : array();
								$variant_webshop_price_arr = isset($_POST['variant_webshop_price']) ? $_POST['variant_webshop_price'] : array();
								//$variant_barcode_arr=isset($_POST['variant_barcode'])?$_POST['variant_barcode']:array();
								//$variant_weight_arr=isset($_POST['variant_weight'])?$_POST['variant_weight']:array();
								$variant_gifts_arr = isset($_POST['gifts']) ? $_POST['gifts'] : array();
								$sub_issue_arr = isset($_POST['sub_issue']) ? $_POST['sub_issue'] : array();

								$variant_shipping_charge_arr = isset($_POST['variant_shipping_charge']) ? $_POST['variant_shipping_charge'] : array();

								if (isset($variant_sku_arr) && count($variant_sku_arr) > 0) {


									foreach ($variant_sku_arr as $key => $val) {
										$sub_issue = $sub_issue_arr[$key];
										$gifts = $variant_gifts_arr[$key];
										$simple_sku = $val;
										$simple_stock = $variant_stock_arr[$key];
										$simple_cost_price = $variant_cost_price_arr[$key];
										$simple_price = $variant_price_arr[$key];
										//$simple_barcode=$variant_barcode_arr[$key];

										$simple_tax_percent = $variant_tax_percent_arr[$key];
										$simple_webshop_price = $variant_webshop_price_arr[$key];
										//$simple_weight=$variant_weight_arr[$key];
										$variant_shipping_charge = $variant_shipping_charge_arr[$key];

										if ($Rounded_price_flag = 1) {
											$RowInfo = $this->SellerProductModel->calculate_webshop_price($simple_price, $simple_tax_percent);
											$simple_tax_amount = $RowInfo['tax_amount'];
											$simple_webshop_price = $RowInfo['webshop_price'];
										} else {
											$simple_webshop_price = $simple_price;
											$simple_tax_amount = 0;
											if ($simple_price > 0 && $simple_tax_percent > 0) {
												$simple_tax_amount = ($simple_tax_percent / 100) * $simple_price;
												$simple_webshop_price = $simple_tax_amount + $simple_price;
											}
										}




										$insertsimpleproduct = array(
											'name' => $product_name,
											'parent_id' => $product_id,
											'sku' => $simple_sku,
											'price' => $simple_price,
											'cost_price' => $simple_cost_price,
											'tax_percent' => $simple_tax_percent,
											'tax_amount' => $simple_tax_amount,
											'webshop_price' => $simple_webshop_price,
											'shipping_amount ' => $variant_shipping_charge,
											// 'shipping_amount ' => $shipping_amount,
											//'barcode'=>$simple_barcode,
											//'weight'=>$simple_weight,
											'launch_date' => $launch_date,
											'product_type' => 'conf-simple',
											'product_inv_type' => 'buy',
											'status' => $status,
											'can_be_returned' => $product_returnable,
											'created_at' => time(),
											'sub_issues' => $sub_issue,
											'gift_id' => $gifts,
											'ip' => $_SERVER['REMOTE_ADDR']
										);
										$simple_product_id = $this->SellerProductModel->insertData('products', $insertsimpleproduct);



										if ($simple_product_id) {


											$stock_insert2 = array('product_id' => $simple_product_id, 'qty' => $simple_stock, 'available_qty' => $simple_stock, 'min_qty' => 0, 'is_in_stock' => 1);
											$this->SellerProductModel->insertData('products_inventory', $stock_insert2);


											if (isset($added_variant_arr) && count($added_variant_arr) > 0) {
												$VariantList = $this->EavAttributesModel->getVariantDataByIds($added_variant_arr);
												if (isset($VariantList) && count($VariantList) > 0) {
													foreach ($VariantList as $attr) {
														$attr_id = $attr['id'];
														$AttrData = $this->CommonModel->getSingleDataByID('eav_attributes', array('id' => $attr_id), 'id,attr_name,attr_code');
														$attr_code = $AttrData->attr_code;
														$attr_code = strtolower($attr_code);

														$variation_selected = $_POST['variant_' . $attr_code][$key];


														if ($variation_selected > 0) {

															$pv_insert = array('product_id' => $simple_product_id, 'parent_id' => $product_id, 'attr_id' => $attr_id, 'attr_value' => $variation_selected);
															$this->SellerProductModel->insertData('products_variants', $pv_insert);
														}
													}
												}
											}
										}
									}
								}
							}
						}



						/*-----------------------upload media imgs---------------------------------------*/
						//print_r($_FILES);exit;
						if (isset($_FILES["gallery_image"]["name"]) && !empty($_FILES["gallery_image"]["name"]) && count($_FILES["gallery_image"]["name"]) > 0) {

							// **Check count of images (max 5)**
							if (count($_FILES["gallery_image"]["name"]) > 5) {
								$arrResponse = array('status' => 400, 'message' => 'You can upload a maximum of 5 images only.');
								echo json_encode($arrResponse);
								exit;
							}

							$config2["upload_path"] = SIS_SERVER_PATH . '/' . 'uploads/products/original/';
							$config2["allowed_types"] = 'jpg|jpeg|JPG|JPEG|png|PNG';
							$config2['max_size'] = 1024 * 5; // (not used since we manually check)
							$config2['max_width'] = '0';
							$config2['max_height'] = '0';
							$config2['overwrite'] = FALSE;
							$config2['encrypt_name'] = TRUE;

							for ($count = 0; $count < count($_FILES["gallery_image"]["name"]); $count++) {
								$_FILES["gfile"]["name"] = $attachment_name = $_FILES["gallery_image"]["name"][$count];
								$_FILES["gfile"]["type"] = $_FILES["gallery_image"]["type"][$count];
								$_FILES["gfile"]["tmp_name"] = $_FILES["gallery_image"]["tmp_name"][$count];
								$_FILES["gfile"]["error"] = $_FILES["gallery_image"]["error"][$count];
								$_FILES["gfile"]["size"] = $_FILES["gallery_image"]["size"][$count];

								// **Check file extension manually**
								$fileExt = strtolower(pathinfo($_FILES["gfile"]["name"], PATHINFO_EXTENSION));
								$allowedExt = ['jpg', 'jpeg', 'png'];
								if (!in_array($fileExt, $allowedExt)) {
									$arrResponse = array('status' => 403, 'message' => 'Please upload only jpg, jpeg, or png files.');
									echo json_encode($arrResponse);
									exit;
								}

								// **Check file size (max 500 KB)**
								if ($_FILES["gfile"]["size"] > 500 * 1024) { // 500 KB = 500 * 1024 bytes
									$arrResponse = array('status' => 400, 'message' => "File '" . $_FILES["gfile"]["name"] . "' exceeds 500KB limit.");
									echo json_encode($arrResponse);
									exit;
								}

								// Upload
								$this->load->library('upload', $config2);
								if ($this->upload->do_upload('gfile')) {
									$data = $this->upload->data();
									unset($this->upload);
									$image = $data["file_name"];
									$orig_name = $data["orig_name"];

									if ($default_image == $orig_name) {
										$is_default = 1;
										$is_base_image = 1;
										$base_image = $image;
									} else {
										$is_default = 0;
										$is_base_image = 0;
									}

									$thumb_folder = SIS_SERVER_PATH . '/' . 'uploads/products/thumb/';
									$medium_folder = SIS_SERVER_PATH . '/' . 'uploads/products/medium/';
									$large_folder = SIS_SERVER_PATH . '/' . 'uploads/products/large/';
									$original_folder = SIS_SERVER_PATH . '/' . 'uploads/products/original/';

									$thumb = $this->makeThumbnail($image, $original_folder, $thumb_folder, $width = '300', $height = '300');
									$this->makeThumbnail($image, $original_folder, $medium_folder, $width = '500', $height = '500');
									$this->makeThumbnail($image, $original_folder, $large_folder, $width = '800', $height = '800');

									$media_insert = array(
										'product_id' => $product_id,
										'image' => $image,
										'image_title' => $orig_name,
										'image_position' => $count,
										'is_default' => $is_default,
										'is_base_image' => $is_base_image
									);
									$this->SellerProductModel->insertData('products_media_gallery', $media_insert);
								} else {
									$arrResponse = array('status' => 400, 'message' => $this->upload->display_errors());
									echo json_encode($arrResponse);
									exit;
								}
							}
						} else {
							$base_image = '';
						}




						if ($base_image != '') {
							$product_img = array('base_image' => $base_image);
							$where_arr = array('id' => $product_id);
							$this->SellerProductModel->updateData('products', $where_arr, $product_img);
						}
						
						if (isset($_FILES['digit_pdf']['name']) && !empty($_FILES['digit_pdf']['name'])) {
							// Assume product_id is passed in the request
							//

							// Set upload path
							$upload_path = SIS_SERVER_PATH2 . '/uploads/digit_pdf/' . $product_id . '/';
							if (!is_dir($upload_path)) {
								mkdir($upload_path, 0777, true);  // Create the directory if it doesn't exist
							}

							// Save uploaded PDF
							$file_extension = pathinfo($_FILES['digit_pdf']['name'], PATHINFO_EXTENSION);
							if ($file_extension != 'pdf') {
								echo json_encode(['status' => 400, 'message' => 'Invalid file type. Please upload a PDF.']);
								exit;
							}

							$digit_pdf = "digit_pdf-" . uniqid() . '.' . $file_extension;
							$pdf_path = $upload_path . $digit_pdf;

							if (!move_uploaded_file($_FILES['digit_pdf']['tmp_name'], $pdf_path)) {
								echo json_encode(['status' => 400, 'message' => 'Failed to upload PDF']);
								exit;
							}

							// Convert PDF to images using pdftoppm
							$output_images = [];
							$output_image_path = $upload_path . 'digit_pdf_page_';

							// Run pdftoppm to convert PDF pages to PNG images
							$command = "/usr/bin/pdftoppm -png \"$pdf_path\" \"$output_image_path\" 2>&1";

							exec($command, $output, $return_var);

							// Debugging: Log the exec command output and return status
							error_log('Exec command output: ' . implode("\n", $output));
							error_log('Exec command return value: ' . $return_var);

							if ($return_var === 0) {
								// Success
								$images = glob($upload_path . 'digit_pdf_page_*.png');
								foreach ($images as $image) {
									$output_images[] = basename($image);
								}
									$product_img = ['digit_pdf' => $digit_pdf];
								$where_arr = ['id' => $product_id];
								$this->SellerProductModel->updateData('products', $where_arr, $product_img);
								echo json_encode(['status' => 200, 'images' => $output_images]);
							} else {
								// Capture the error output
								echo json_encode([
									'status' => 400,
									'message' => 'Failed to convert PDF to images',
									'error_output' => implode("\n", $output), // Display the command output/error for debugging
									'error_code' => $return_var
								]);
							}

							exit;
						} else {
							$digit_pdf = '';
						}


						$product_img = ['digit_pdf' => $digit_pdf];
						$where_arr = ['id' => $product_id];
						$this->SellerProductModel->updateData('products', $where_arr, $product_img);
					}
				}
				echo json_encode($arrResponse);
				exit;
			}
		}
	}


	function makeThumbnail($file_name, $original_path, $thumb_path, $width = '', $height = '')
	{

		$config_thumb = array(
			'allowed_types'     => 'jpg|jpeg|gif|png', //only accept these file types
			'max_size'          => 5 * 1024, //5MB max
			//'encrypt_name'		=> TRUE,
			'upload_path'       => $original_path //upload directory
		);

		$width = (isset($width)) ? $width : '100';
		$height = (isset($height)) ? $height : '';
		$this->load->library('upload', $config_thumb);

		//your desired config for the resize() function
		$config_thumb = array(
			'source_image'      => $original_path . $file_name, //path to the uploaded image
			'new_image'         => $thumb_path . $file_name, //path to
			'maintain_ratio'    => true,
			'width'             => $width,
			'height'            => $height
		);
		if (!$this->image_lib->resize()) {
			// print_r("heloooo");

			echo $this->image_lib->display_errors();
		}
		// echo '<pre>';print_r($config_thumb);exit;

		//this is the magic line that enables you generate multiple thumbnails
		//you have to call the initialize() function each time you call the resize()
		//otherwise it will not work and only generate one thumbnail
		$this->load->library('image_lib');
		// echo '<pre>';print_r($config_thumb);exit;

		//this is the magic line that enables you generate multiple thumbnails
		//you have to call the initialize() function each time you call the resize()
		//otherwise it will not work and only generate one thumbnail
		$this->image_lib->initialize($config_thumb);
		$this->image_lib->resize();
		return true;
	}


	function refreshrootcategory()
	{

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		$ParentCategory = $this->CommonModel->get_category_for_seller($shop_id);

		$optionHTML = '<option value="">Select  Category</option>';
		if (isset($ParentCategory) && count($ParentCategory) > 0) {
			$flag = 'add';
			foreach ($ParentCategory as $value) {
				$optionHTML .= '<option value="' . $value["id"] . '" >' . $value["cat_name"] . '</option>';
			}
			$optionHTML .= '<option value="new" >Add New</option>';
		}
		echo $optionHTML;
		exit;
	}


	function loadsubcategory()
	{
		if (isset($_POST['parent_id']) && $_POST['parent_id'] != '') {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$data['parent_id'] = $parent_id = $_POST['parent_id'];
			$data['product_id'] = (isset($_POST['product_id']) && $_POST['product_id'] != '') ? $_POST['parent_id'] : '';
			$data['SubCategory'] = $SubCategory = $this->CategoryModel->get_child_category_for_seller($shop_id, $_POST['parent_id'], 1);


			echo $twoleveloptionHTML;
			exit;
		} else {
			echo 'error';
			exit;
		}
	}


	function getsubcategory()
	{

		if (isset($_POST['parent_id']) && $_POST['parent_id'] != '') {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$TwoLevelList = $this->CategoryModel->get_child_category_for_seller($shop_id, $_POST['parent_id'], 1);

			$twoleveloptionHTML = '<option value="">Select Sub Category</option>';
			if (isset($TwoLevelList) && count($TwoLevelList) > 0) {
				$flag = 'add';
				foreach ($TwoLevelList as $value) {
					$twoleveloptionHTML .= '<option value="' . $value["id"] . '" >' . $value["cat_name"] . '</option>';
				}
				$twoleveloptionHTML .= '<option value="new" >Add New</option>';
			}
			echo $twoleveloptionHTML;
			exit;
		} else {
			echo 'error';
			exit;
		}
	}


	function getsubcategorytags()
	{

		if (isset($_POST['parent_id']) && $_POST['parent_id'] != '') {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$ThirdLevelList = $this->CategoryModel->get_child_category_for_seller($shop_id, $_POST['parent_id'], 2);
			$cat_str = '';
			$tags_arr = array();
			if (isset($ThirdLevelList) && count($ThirdLevelList) > 0) {
				foreach ($ThirdLevelList as $value) {
					$tags_arr[$value['id']] = $value["cat_name"];
				}

				$cat_str = implode(',', $tags_arr);
			}

			echo json_encode($tags_arr);
			exit;
		} else {
			echo 'error';
			exit;
		}
	}

	function getattributesbycategory()
	{
		if (isset($_POST['category_id']) && $_POST['category_id'] != '') {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$CategoryDetail = $this->CategoryModel->get_category_detail($_POST['category_id']);


			if (isset($CategoryDetail) && $CategoryDetail->selected_attributes != '') {

				$selected_attributes = $CategoryDetail->selected_attributes;
				$selected_attributes = substr($selected_attributes, 1, -1);
				if (strpos($selected_attributes, ',') !== false) {
					$selected_attributes_arr = explode(',', $selected_attributes);
				} else {
					$selected_attributes_arr[] = $selected_attributes;
				}
				if (count($selected_attributes_arr) > 0) {

					$AttributesList = $this->EavAttributesModel->get_attributes_for_seller($selected_attributes_arr);
					$data['AttributesList'] = $AttributesList;
				} else {
					$data['AttributesList'] = array();
				}
			} else {
				$data['AttributesList'] = array();
			}
			$data['CategoryDetail'] = $CategoryDetail;
			$View = $this->load->view('seller/products/dynamic_attribute_loop', $data, true);
			$this->output->set_output($View);
		} else {
			echo 'error';
			exit;
		}
	}

	function loadsingleattribute()
	{
		if (isset($_POST['attr_id']) && $_POST['attr_id'] != '') {
			$selected_attributes_arr[] = $_POST['attr_id'];

			$AttributesList = $this->EavAttributesModel->get_attributes_for_seller($selected_attributes_arr);
			$data['AttributesList'] = $AttributesList;
			$View = $this->load->view('seller/products/load_attributes', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function getvariantbycategory()
	{
		if (isset($_POST['category_id']) && $_POST['category_id'] != '') {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$CategoryDetail = $this->CategoryModel->get_category_detail($_POST['category_id']);
			$data['CategoryDetail'] = $CategoryDetail;

			$data['flag'] = $flag = isset($_POST['flag']) ? $_POST['flag'] : '';

			if (isset($_POST['flag'])  && $flag == 'edit_variant') {

				$product_id = $_POST['pid'];
				$VariantMaster = $this->SellerProductModel->getVariantMasterForProducts($product_id);
				$data['VariantMaster'] = $VariantMaster;
			} else {
				/*
				if(isset($CategoryDetail) && $CategoryDetail->selected_variants!=''){

					$selected_variants=$CategoryDetail->selected_variants;

					$selected_variants = substr($selected_variants, 1, -1);
					if( strpos($selected_variants, ',') !== false ) {
						$selected_variants_arr=explode(',',$selected_variants);
					}else{
						$selected_variants_arr[]=$selected_variants;
					}
					if(count($selected_variants_arr)>0){

						//$VariantList=$this->EavAttributesModel->get_variant_by_category($selected_variants_arr);

						$data['selected_variants']=$selected_variants_arr;
					}else{
						$data['selected_variants']=array();
					}
				}else{

					$data['selected_variants']=array();
				}
				*/

				$data['VariantsBySeller'] = $this->EavAttributesModel->get_variant_masters($shop_id);
			}

			$data['Rounded_price_flag'] = $this->CommonModel->getRoundedPriceFlag();

			$View = $this->load->view('seller/products/variant_popup', $data, true);
			$this->output->set_output($View);
		} else {
			echo 'error';
			exit;
		}
	}

	function openvariantmaster()
	{
		if (isset($_POST['category_id']) && $_POST['category_id'] != '') {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$CategoryDetail = $this->CategoryModel->get_category_detail($_POST['category_id']);
			$data['CategoryDetail'] = $CategoryDetail;

			$data['flag'] = $flag = isset($_POST['flag']) ? $_POST['flag'] : '';

			$data['VariantMaster'] = $this->EavAttributesModel->get_variant_masters($shop_id);


			$View = $this->load->view('seller/products/variant_select_list', $data, true);
			$this->output->set_output($View);
		} else {
			echo 'error';
			exit;
		}
	}


	function openaddcategorypopup()
	{
		$data['test'] = '1';
		$View = $this->load->view('seller/products/root_category_add', $data, true);
		$this->output->set_output($View);
	}

	function openaddsubcategorypopup()
	{
		$data['DefaultAttrList'] = $this->EavAttributesModel->get_default_attributes();
		$data['DefaultVariantList'] = $this->EavAttributesModel->get_default_variants();

		$View = $this->load->view('seller/products/sub_category_add', $data, true);
		$this->output->set_output($View);
	}



	function saverootcategory()
	{
		if (isset($_POST['rc_name']) && $_POST['rc_name'] != '') {

			$rc_name = $_POST['rc_name'];
			$rc_description = $_POST['rc_description'];

			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$checkCate = $this->CategoryModel->check_category_exist_by_level($shop_id, 0, $rc_name);

			if (isset($checkCate) && $checkCate['id'] != '') {
				echo "error2";
				exit;
			} else {

				$slug = url_title($rc_name);
				$url_key = strtolower($slug);

				$slugcount = $this->CategoryModel->check_category_exist_by_slug($shop_id, 0, $rc_name);
				if ($slugcount > 0) {
					$slugcount = $slugcount + 1;
					$url_key = $url_key . "-" . $slugcount;
				} else {
					$url_key = $url_key;
				}


				$insertArr = array('cat_name' => $rc_name, 'slug' => $url_key, 'cat_level' => 0, 'cat_description' => $rc_description, 'shop_id' => $shop_id, 'created_by' => $fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
				$cat_id = $this->CategoryModel->insertData('category', $insertArr);


				if ($cat_id) {

					$sub_cat = (isset($_POST['sub_cat'])) ? $_POST['sub_cat'] : array();
					if (isset($sub_cat) && count($sub_cat) > 0) {
						foreach ($sub_cat as $val) {
							if (isset($val) && !empty($val)) {
								$sc_slug = url_title($val);
								$sc_url_key = strtolower($sc_slug);

								$slugcount = $this->CategoryModel->check_category_exist_by_slug($shop_id, 1, $val);
								if ($slugcount > 0) {
									$slugcount = $slugcount + 1;
									$sc_url_key = $sc_url_key . "-" . $slugcount;
								} else {
									$sc_url_key = $sc_url_key;
								}


								$insertArr = array('cat_name' => $val, 'slug' => $sc_url_key, 'cat_level' => 1, 'parent_id' => $cat_id, 'cat_description' => '', 'shop_id' => $shop_id, 'created_by' => $fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
								$this->CategoryModel->insertData('category', $insertArr);
							}
						}
					}
					echo $cat_id;
					exit;
				} else {
					echo "error";
					exit;
				}
			}
		} else {
			echo "error";
			exit;
		}
	}

	function openeditcategorypopup()
	{
		if (isset($_POST['category_id']) && $_POST['category_id'] != '') {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$data['category_id'] = $category_id = $_POST['category_id'];
			$data['CategoryDetail'] = $this->CategoryModel->get_category_detail($category_id);
			$data['SubCategoryList'] = $this->CategoryModel->get_child_category_for_seller($shop_id, $category_id, 1);

			$View = $this->load->view('seller/products/root_category_edit', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function opensubcategoryeditpopup()
	{
		if (isset($_POST['category_id']) && $_POST['category_id'] != '') {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$data['category_id'] = $category_id = $_POST['category_id'];
			$data['CategoryDetail'] = $CategoryDetail = $this->CategoryModel->get_category_detail($category_id);

			$ThirdLevelList = $this->CategoryModel->get_child_category_for_seller($fbc_user_id, $category_id, 2);

			$cat_str = '';
			$tags_arr = array();
			if (isset($ThirdLevelList) && count($ThirdLevelList) > 0) {
				foreach ($ThirdLevelList as $value) {
					$tags_arr[$value['id']] = $value["cat_name"];
				}
				$cat_str = implode(',', $tags_arr);

				$data['category_tags'] = $cat_str;
			} else {
				$data['category_tags'] = $cat_str = '';
			}

			$SellerAttr = $this->CommonModel->getSingleDataByID('category_attribute_mapping_shop', array('category_id' => $category_id), '');

			if (!empty($SellerAttr) && $SellerAttr->id != '') {


				if (isset($SellerAttr) && $SellerAttr->selected_attributes != '') {

					$selected_attributes = $SellerAttr->selected_attributes;
					$selected_attributes = substr($selected_attributes, 1, -1);
					if (strpos($selected_attributes, ',') !== false) {
						$selected_attributes_arr = explode(',', $selected_attributes);
					} else {
						$selected_attributes_arr[] = $selected_attributes;
					}
					if (count($selected_attributes_arr) > 0) {

						$AttributesList = $this->EavAttributesModel->get_attributes_for_seller($selected_attributes_arr);
						$data['DefaultAttrList'] = $AttributesList;
					} else {
						$data['DefaultAttrList'] = array();
					}
				} else {
					$data['DefaultAttrList'] = array();
				}


				if (isset($SellerAttr) && $SellerAttr->selected_variants != '') {

					$selected_variants = $SellerAttr->selected_variants;

					$selected_variants = substr($selected_variants, 1, -1);
					if (strpos($selected_variants, ',') !== false) {
						$selected_variants_arr = explode(',', $selected_variants);
					} else {
						$selected_variants_arr[] = $selected_variants;
					}
					if (count($selected_variants_arr) > 0) {

						$VariantList = $this->EavAttributesModel->get_variant_by_category($selected_variants_arr);

						$data['VariantList'] = $VariantList;
					} else {
						$data['VariantList'] = array();
					}
				} else {

					$data['VariantList'] = array();
				}
				$data['seller_attr'] = 1;
			} else {
				//$data['DefaultAttrList']=$this->EavAttributesModel->get_default_attributes();
				//$data['DefaultVariantList']=$this->EavAttributesModel->get_default_variants();

				if (isset($CategoryDetail) && $CategoryDetail->selected_attributes != '') {

					$selected_attributes = $CategoryDetail->selected_attributes;
					$selected_attributes = substr($selected_attributes, 1, -1);
					if (strpos($selected_attributes, ',') !== false) {
						$selected_attributes_arr = explode(',', $selected_attributes);
					} else {
						$selected_attributes_arr[] = $selected_attributes;
					}
					if (count($selected_attributes_arr) > 0) {

						$AttributesList = $this->EavAttributesModel->get_attributes_for_seller($selected_attributes_arr);
						$data['DefaultAttrList'] = $AttributesList;
					} else {
						$data['DefaultAttrList'] = array();
					}
				} else {
					$data['DefaultAttrList'] = array();
				}


				if (isset($CategoryDetail) && $CategoryDetail->selected_variants != '') {

					$selected_variants = $CategoryDetail->selected_variants;

					$selected_variants = substr($selected_variants, 1, -1);
					if (strpos($selected_variants, ',') !== false) {
						$selected_variants_arr = explode(',', $selected_variants);
					} else {
						$selected_variants_arr[] = $selected_variants;
					}
					if (count($selected_variants_arr) > 0) {

						$VariantList = $this->EavAttributesModel->get_variant_by_category($selected_variants_arr);

						$data['VariantList'] = $VariantList;
						$data['VariantList'] = $VariantList;
					} else {
						$data['VariantList'] = array();
					}
				} else {

					$data['VariantList'] = array();
				}

				$data['seller_attr'] = 2;
			}


			$View = $this->load->view('seller/products/sub_category_edit', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}





	function openattributelistpopup()
	{
		if (isset($_POST['category_id']) && $_POST['category_id'] != '') {
			$CategoryDetail = $this->CategoryModel->get_category_detail($_POST['category_id']);
			//var_dump($CategoryDetail);exit;
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			/*
			if(isset($CategoryDetail) && $CategoryDetail->selected_attributes!=''){

				$selected_attributes=$CategoryDetail->selected_attributes;
				$selected_attributes = substr($selected_attributes, 1, -1);
				if( strpos($selected_attributes, ',') !== false ) {
					$selected_attributes_arr=explode(',',$selected_attributes);
				}else{
					$selected_attributes_arr[]=$selected_attributes;
				}
				if(count($selected_attributes_arr)>0){

					$AttributesList=$this->EavAttributesModel->get_attributes_for_seller($selected_attributes_arr);
					$data['AttributesList']=$AttributesList;
				}else{
					$data['AttributesList']=array();
				}
			}else{
				$data['AttributesList']=$this->EavAttributesModel->get_default_attributes();
			}
			*/


			$data['CategoryDetail'] = $CategoryDetail;
			$data['flag'] = $flag = isset($_POST['flag']) ? $_POST['flag'] : '';
			if (isset($flag) && $flag == 'edit_attr') {
				$data['AttributesBySeller'] = $this->EavAttributesModel->get_attributes_masters($shop_id);
			} else {
				$data['AttributesList'] = $this->EavAttributesModel->get_attributes_masters($shop_id);
			}
			$View = $this->load->view('seller/products/attribute_select_list', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	function openaddattributepopup()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$data['fbc_user_id'] = $fbc_user_id;
		$data['category_id'] = $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';

		$View = $this->load->view('seller/products/attribute_add', $data, true);
		$this->output->set_output($View);
	}

	function saveattribute()
	{
		//insert
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');


		if (empty($_POST['attribute_name'])  || empty($_POST['attribute_properties'])) {
			$arrResponse  = array('status' => 400, 'message' => 'Please enter mandatory feilds');
			echo json_encode($arrResponse);
			exit;
		} else if (($_POST['attribute_properties'] == 5 || $_POST['attribute_properties'] == 6) && (empty($_POST['tagsValues']))) {

			echo json_encode(array('status' => 400, 'message' => "Please enter Attribute values."));
			exit;
		} else {


			$attribute_id = (isset($_POST['attribute_id']) && $_POST['attribute_id'] != '') ? $_POST['attribute_id'] : 0;

			$display_on_frontend = (isset($_POST['display_on_frontend']) && $_POST['display_on_frontend'] == 1) ? 1 : '0';
			$filterable_with_results = (isset($_POST['filterable_with_results']) && $_POST['filterable_with_results'] == 1) ? 1 : '0';
			$category_id = $_POST['category_id'];
			$attribute_code = $_POST['attribute_code'];
			$attribute_code = strtolower($attribute_code);

			$codeFund = $this->EavAttributesModel->getAttributeCode($attribute_code, $attribute_id, $fbc_user_id, 1);
			if ($attribute_id != 0) {
				if ($codeFund  && $codeFund->id != $attribute_id) {
					echo json_encode(array('status' => 400, 'message' => "Attribute code exist."));
					exit;
				}
			} else if ($attribute_id == 0) {
				if ($codeFund) {
					echo json_encode(array('status' => 400, 'message' => "Attribute code exist."));
					exit;
				}
			}


			if (isset($attribute_id) && $attribute_id > 0) {


				$updateData = array(
					'attr_code'    		=> $attribute_code,
					'attr_name'			=> $_POST['attribute_name'],
					'attr_description'	=> $_POST['attribute_description'],
					//'attr_type'			=> 1,
					//'created_by' 		=> $fbc_user_id,
					//'created_by_type' 	=> 1,
					'updated_at'		=> time(),
					'ip'				=> $_SERVER['REMOTE_ADDR'],
				);
				$this->db->where('id', $attribute_id);
				$this->db->update('eav_attributes', $updateData);

				$this->db->reset_query();



				$check_attr_visib_exist = $this->SellerProductModel->getSingleDataByID('fbc_users_attributes_visibility', array('attr_id' => $attribute_id), 'id');
				if (isset($check_attr_visib_exist) && $check_attr_visib_exist->id != '') {
					$fbc_atr_setting_upate = array('filterable_with_results' => $filterable_with_results, 'display_on_frontend' => $display_on_frontend, 'updated_at' => time());

					$where_arr = array('attr_id' => $attribute_id);

					$this->SellerProductModel->updateData('fbc_users_attributes_visibility', $where_arr, $fbc_atr_setting_upate);
				} else {
					$fbc_atr_setting_insert = array('attr_id' => $attribute_id, 'filterable_with_results' => $filterable_with_results, 'display_on_frontend' => $display_on_frontend, 'created_by' => $fbc_user_id, 'created_at' => time());
					$this->SellerProductModel->insertData('fbc_users_attributes_visibility', $fbc_atr_setting_insert);
				}



				if ($_POST['attribute_properties'] == 5 || $_POST['attribute_properties'] == 6) {
					if ($_POST['tagsValues'] != '' || $_POST['tagsnewValues'] != '') {
						if ($_POST['tagsValues'] != '') {
							$tagsValues = explode(',', $_POST['tagsValues']);

							$tagsValues = array_values(array_filter($tagsValues));

							$OldOptionsBySeller = $this->CommonModel->getMultiDataById('eav_attributes_options', array('created_by' => $fbc_user_id, 'attr_id' => $attribute_id), 'id,attr_id,attr_options_name');
							$prev_options_ids = array();
							if (isset($OldOptionsBySeller) && count($OldOptionsBySeller) > 0) {
								foreach ($OldOptionsBySeller as $ct) {
									$prev_options_ids[] = $ct->attr_options_name;
								}
							}

							$result = array_diff_assoc($prev_options_ids, $tagsValues);
							if (count($result) > 0) {
								foreach ($result as $val) {
									$cdeletedata = array('attr_options_name' => $val, 'created_by' => $fbc_user_id, 'attr_id' => $attribute_id);
									//$this->db->delete('eav_attributes_options', $cdeletedata);
									//$this->db->reset_query();
								}
							}




							foreach ($tagsValues as $key => $value) {
								$IsOptionExist = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('attr_options_name' => $value, 'attr_id' => $attribute_id), 'id');
								if (isset($IsOptionExist) && $IsOptionExist->id != '') {
								} else {
									$attributesData = array(
										'attr_id'    		=> $attribute_id,
										'attr_options_name'	=> $value,
										'created_by' 		=> $fbc_user_id,
										'shop_id'			=> $shop_id,
										'created_by_type' 	=> 1,
										'status'			=> 1,
										'created_at'		=> time(),
										'ip'				=> $_SERVER['REMOTE_ADDR'],
									);
									$this->db->insert('eav_attributes_options', $attributesData);
								}
							}
						}
					}
				}


				$arrResponse  = array('status' => 200, 'message' => 'Attribute updated successfully.', 'category_id' => $category_id, 'attr_id' => $attribute_id);
				echo json_encode($arrResponse);
				exit;
			} else {
				//insert


				$insertData = array(
					'attr_code'    		=> $attribute_code,
					'attr_name'			=> $_POST['attribute_name'],
					'attr_type'			=> 1,
					'attr_description'	=> $_POST['attribute_description'],
					'attr_properties' 	=> $_POST['attribute_properties'],
					'created_by' 		=> $fbc_user_id,
					'shop_id'			=> $shop_id,
					'created_by_type' 	=> 1,
					'status'			=> 1,
					'created_at'		=> time(),
					'ip'				=> $_SERVER['REMOTE_ADDR'],
				);
				$this->db->insert('eav_attributes', $insertData);
				$insert_id = $this->db->insert_id();



				$fbc_atr_setting_insert = array('attr_id' => $insert_id, 'filterable_with_results' => $filterable_with_results, 'display_on_frontend' => $display_on_frontend, 'created_by' => $fbc_user_id, 'created_at' => time());
				$this->SellerProductModel->insertData('fbc_users_attributes_visibility', $fbc_atr_setting_insert);

				if ($_POST['attribute_properties'] == 5 || $_POST['attribute_properties'] == 6) {

					$tagsValues = explode(',', $_POST['tagsValues']);
					$tagsValues = array_filter(array_unique($tagsValues));
					if (isset($tagsValues) && count($tagsValues) > 0) {
						foreach ($tagsValues as $key => $value) {
							if ($value != '') {
								$attributesData = array(
									'attr_id'    		=> $insert_id,
									'attr_options_name'	=> $value,
									'created_by' 		=> $fbc_user_id,
									'shop_id'			=> $shop_id,
									'created_by_type' 	=> 1,
									'status'			=> 1,
									'created_at'		=> time(),
									'ip'				=> $_SERVER['REMOTE_ADDR'],
								);
								$this->db->insert('eav_attributes_options', $attributesData);
							}
						}
					}
				}


				$arrResponse  = array('status' => 200, 'message' => 'Attribute added successfully.', 'category_id' => $category_id, 'attr_id' => $insert_id);
				echo json_encode($arrResponse);
				exit;
			}
		}
	}


	function savesubcategorynew()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		if (empty($_POST['category_id'])  || empty($_POST['sub_cat_name'])) {
			$arrResponse  = array('status' => 400, 'message' => 'Please add mandatory feilds');
			echo json_encode($arrResponse);
			exit;
		}
		/*
		else if(empty($_POST['sub_child_ids'])){
			$arrResponse  = array('status' =>400 ,'message'=>'Please add catalogue');
			echo json_encode($arrResponse);exit;
		}*/ else {

			$sub_category = $_POST['category_id'];
			$sub_cat_name = $_POST['sub_cat_name'];

			$CategoryDetail = $this->CategoryModel->get_category_detail($sub_category);
			$root_category_id = $CategoryDetail->parent_id;

			$child_category = (isset($_POST['sub_child_ids']) && $_POST['sub_child_ids'] != '') ? $_POST['sub_child_ids'] : '';


			if (isset($child_category) && $child_category != '') {
				if (strpos($child_category, ',') !== false) {
					$child_category_arr = explode(',', $child_category);
				} else {
					$child_category_arr[] = $child_category;
				}


				$child_category_arr = array_values(array_filter($child_category_arr));

				$ThirdLevelList = $this->CategoryModel->get_child_category_for_seller($shop_id, $sub_category, 2);
				$prev_child_ids = array();
				if (isset($ThirdLevelList) && count($ThirdLevelList) > 0) {
					foreach ($ThirdLevelList as $ct) {
						$prev_child_ids[] = $ct['cat_name'];
					}
				}

				$result = array_diff_assoc($prev_child_ids, $child_category_arr);
				if (count($result) > 0) {
					foreach ($result as $val) {
						$cdeletedata = array('cat_name' => $val, 'cat_level' => 2, 'created_by' => $fbc_user_id);
						$this->db->delete('category', $cdeletedata);
						$this->db->reset_query();
					}
				}

				if (count($child_category_arr) > 0) {
					foreach ($child_category_arr as $cat_name) {
						$IsChildExist = $this->CategoryModel->check_child_category_exist($shop_id, $sub_category, 2, $cat_name);
						if (isset($IsChildExist) && $IsChildExist['id'] != '') {
							$cid = $IsChildExist['id'];
							//$child_cat_id_arr[]=$cid;
						} else {

							$slug = url_title($cat_name);
							$url_key = strtolower($slug);

							$slugcount = $this->CategoryModel->check_category_exist_by_slug($shop_id, 2, $cat_name);
							if ($slugcount > 0) {
								$slugcount = $slugcount + 1;
								$url_key = $url_key . "-" . $slugcount;
							} else {
								$url_key = $url_key;
							}


							$child_cat_insert = array('cat_name' => $cat_name, 'parent_id' => $sub_category, 'main_parent_id' => $root_category_id, 'cat_level' => 2, 'created_by' => $fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'shop_id' => $shop_id, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR'], 'slug' => $url_key);
							$this->db->insert('category', $child_cat_insert);
							$cid = $this->db->insert_id();
							//$child_cat_id_arr[]=$cid;
						}
					}

					$this->db->reset_query();
				}
			}




			if (isset($_POST['sub_cat_attr']) && count($_POST['sub_cat_attr']) > 0) {
				$select_attr = $_POST['sub_cat_attr'];

				$select_attr = array_filter(array_unique($select_attr));
				$select_attr_str = implode(',', $select_attr);
				$select_attr_str = ',' . $select_attr_str . ',';
			} else {
				$select_attr_str = '';
			}


			if (isset($_POST['sub_cat_variant']) && count($_POST['sub_cat_variant']) > 0) {
				$select_variant = $_POST['sub_cat_variant'];

				$select_variant = array_filter(array_unique($select_variant));
				$select_variant_str = implode(',', $select_variant);
				$select_variant_str = ',' . $select_variant_str . ',';
			} else {
				$select_variant_str = '';
			}

			if ($CategoryDetail->created_by_type == 1) {
				//$new_slug=url_title($sub_cat_name);
				$sc_slug = url_title($sub_cat_name);
				$new_slug = strtolower($sc_slug);

				$slugcount = $this->CategoryModel->check_category_exist_by_slug($shop_id, 2, $sub_cat_name, $sub_category);
				if ($slugcount > 0) {
					$slugcount = $slugcount + 1;
					$new_slug = $new_slug . "-" . $slugcount;
				} else {
					$new_slug = $new_slug;
				}



				$updateData = array('cat_name' => $sub_cat_name, 'slug' => $new_slug, 'updated_at' => time());
				$this->db->where('id', $sub_category);
				$this->db->update('category', $updateData);
				$this->db->reset_query();

				$IsRowExist = $this->CommonModel->getSingleDataByID('category_attribute_mapping_shop', array('category_id' => $sub_category), '');
				if (isset($IsRowExist) && $IsRowExist->id != '') {
					$updateData = array('selected_attributes' => $select_attr_str, 'selected_variants' => $select_variant_str, 'updated_at' => time());
					$this->db->where('id', $IsRowExist->id);
					$this->db->update('category_attribute_mapping_shop', $updateData);
					$this->db->reset_query();
				} else {
					$insertData = array('category_id' => $sub_category, 'selected_attributes' => $select_attr_str, 'selected_variants' => $select_variant_str, 'created_by' => $fbc_user_id, 'shop_id' => $shop_id, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);

					$this->db->insert('category_attribute_mapping_shop', $insertData);
					$this->db->reset_query();
				}
			}

			$arrResponse  = array('status' => 200, 'message' => 'Sub Category saved successfully.', 'category_id' => $sub_category, 'root_category_id' => $root_category_id);
			echo json_encode($arrResponse);
			exit;
		}
	}

	function refreshtempsubcatattr()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$type = $_POST['type'];
		$data['type'] = $type;
		if ($type == 'attributes') {
			if (empty($_POST['category_id'])  || empty($_POST['select_attr'])) {
				$arrResponse  = array('status' => 400, 'message' => 'Please enter mandatory feilds');
			} else {
				$category_id = $_POST['category_id'];
				if (isset($_POST['select_attr']) && count($_POST['select_attr']) > 0) {

					$select_attr = $_POST['select_attr'];


					$select_attr = array_filter(array_unique($select_attr));

					$AttributesList = $this->EavAttributesModel->getAttrDataByIds($select_attr);
					$data['AttributesList'] = $AttributesList;
					$View = $this->load->view('seller/products/attr_checkbox_list', $data, true);
					$this->output->set_output($View);
				}
			}
		} else if ($type == 'variants') {
			if (empty($_POST['category_id'])  || empty($_POST['select_attr'])) {
				$arrResponse  = array('status' => 400, 'message' => 'Please enter mandatory feilds');
			} else {
				$category_id = $_POST['category_id'];
				if (isset($_POST['select_attr']) && count($_POST['select_attr']) > 0) {

					$select_attr = $_POST['select_attr'];


					$select_attr = array_filter(array_unique($select_attr));

					$VariantList = $this->EavAttributesModel->getVariantDataByIds($select_attr);
					$data['VariantList'] = $VariantList;
					$View = $this->load->view('seller/products/attr_checkbox_list', $data, true);
					$this->output->set_output($View);
				}
			}
		} else {
			echo "error";
			exit;
		}
	}


	function updaterootcategory()
	{
		if (isset($_POST['rc_name']) && $_POST['rc_name'] != '') {

			$rc_name = $_POST['rc_name'];
			$rc_description = $_POST['rc_description'];
			$category_id = $_POST['category_id'];

			//print_r_custom($_POST);exit;

			$CateInfo = $this->CommonModel->getSingleDataByID('category', array('id' => $category_id), 'id,created_by_type');

			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');

			$slug = url_title($rc_name);
			$url_key = strtolower($slug);

			if ($CateInfo->created_by_type == 1) {

				$slugcount = $this->CategoryModel->check_category_exist_by_slug($shop_id, 0, $rc_name, $category_id);
				if ($slugcount > 0) {
					$slugcount = $slugcount + 1;
					$url_key = $url_key . "-" . $slugcount;
				} else {
					$url_key = $url_key;
				}



				$update_arr = array('cat_name' => $rc_name, 'slug' => $url_key, 'cat_description' => $rc_description, 'updated_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
				$this->db->where('id', $category_id);
				$this->db->update('category', $update_arr);
			}

			$this->db->reset_query();


			if ($category_id) {

				$sub_cat = (isset($_POST['sub_cat'])) ? $_POST['sub_cat'] : array();
				$hidden_sub_cat = (isset($_POST['hidden_sub_cat'])) ? $_POST['hidden_sub_cat'] : array();

				if (isset($sub_cat) && count($sub_cat) > 0) {
					foreach ($sub_cat as $sbkey => $val) {

						if (isset($val) && $val != '') {
							$sc_slug = url_title($val);
							$sc_url_key = strtolower($sc_slug);
							$sb_category_id = $hidden_sub_cat[$sbkey];
							if (isset($sb_category_id) && $sb_category_id != '') {
								$IsChildExist = $this->CategoryModel->check_child_category_exist_by_id($shop_id, $category_id, 1, $sb_category_id);
							} else {
								$IsChildExist = '';
							}
							if (isset($IsChildExist) && $IsChildExist['id'] != '') {
								$cid = $IsChildExist['id'];

								if ($IsChildExist['created_by_type'] == 1) {

									$slugcount = $this->CategoryModel->check_category_exist_by_slug($shop_id, 1, $val, $cid);
									if ($slugcount > 0) {
										$slugcount = $slugcount + 1;
										$sc_url_key = $sc_url_key . "-" . $slugcount;
									} else {
										$sc_url_key = $sc_url_key;
									}


									$update_sub_arr = array('cat_name' => $val, 'slug' => $sc_url_key, 'updated_at' => time());
									$this->db->where('id', $cid);
									$this->db->update('category', $update_sub_arr);
								}
							} else {


								$slugcount = $this->CategoryModel->check_category_exist_by_slug($shop_id, 1, $val);
								if ($slugcount > 0) {
									$slugcount = $slugcount + 1;
									$sc_url_key = $sc_url_key . "-" . $slugcount;
								} else {
									$sc_url_key = $sc_url_key;
								}


								$insertArr = array('cat_name' => $val, 'slug' => $sc_url_key, 'cat_level' => 1, 'parent_id' => $category_id, 'cat_description' => '', 'shop_id' => $shop_id, 'created_by' => $fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
								$this->CategoryModel->insertData('category', $insertArr);
								$cid = $this->db->insert_id();

								/*$checkbtb_level_two = $this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b', array('category_id' => $cid, 'level' => 2), 'id');
								if (empty($checkbtb_level_two)) {
									$fbc_childcat_insert = array('category_id' => $cid, 'level' => 1, 'fbc_user_id' => $fbc_user_id);
									$this->SellerProductModel->insertData('fbc_users_category_b2b', $fbc_childcat_insert);
								}*/
							}

							$this->db->reset_query();
						}
					}
				}


				echo $category_id;
				exit;
			} else {
				echo "error";
				exit;
			}
		} else {
			echo "error";
			exit;
		}
	}

	function openeditattributepopup()
	{
		$attribute_id = $_POST['attr_id'];
		if ($attribute_id) {
			$data['category_id'] = $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
			$data['attribute'] = $this->EavAttributesModel->get_attribute_detail($attribute_id);

			$data['attribute_display'] = $this->SellerProductModel->getSingleDataByID('fbc_users_attributes_visibility', array('attr_id' => $attribute_id), '');

			$this->load->view('seller/products/attribute_edit', $data);
		} else {
			echo 'error';
			exit;
		}
	}

	public function getAttribute()
	{
		$attribute_id = $_POST['id'];
		if ($attribute_id) {
			$attributevalues = $this->CommonModel->GetDropDownOptions($attribute_id);
			echo json_encode(array('flag' => 1, 'data' => $attributevalues));
			exit;
		} else {
			echo json_encode(array('flag' => 0));
			exit;
		}
	}

	public function testindex()
	{
		//I'm just using rand() function for data example
		$temp = rand(10000, 99999);
		$this->set_barcode($temp);
	}

	private function set_barcode($code)
	{
		//load library
		$this->load->library('zend');
		//load in folder Zend
		$this->zend->load('Zend/Barcode');
		//generate barcode
		Zend_Barcode::render('code128', 'image', array('text' => $code), array());
	}

	function openeditvariantpopup()
	{
		$attribute_id = $_POST['attr_id'];
		if ($attribute_id) {
			$data['category_id'] = $category_id = (isset($_POST['category_id']) && $_POST['category_id'] != '') ? $_POST['category_id'] : '';
			$data['attribute'] = $this->EavAttributesModel->get_attribute_detail($attribute_id);
			$data['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';

			$data['attribute_display'] = $this->SellerProductModel->getSingleDataByID('fbc_users_attributes_visibility', array('attr_id' => $attribute_id), '');

			$this->load->view('seller/products/variant_edit', $data);
		} else {
			echo 'error';
			exit;
		}
	}

	function savevariant()
	{
		//insert
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		if (empty($_POST['attribute_name'])) {
			$arrResponse  = array('status' => 400, 'message' => 'Please enter variation name');
			echo json_encode($arrResponse);
			exit;
		} else {

			$attribute_id = (isset($_POST['attribute_id']) && $_POST['attribute_id'] != '') ? $_POST['attribute_id'] : 0;

			$display_on_frontend = (isset($_POST['display_on_frontend']) && $_POST['display_on_frontend'] == 1) ? 1 : '0';
			$filterable_with_results = (isset($_POST['filterable_with_results']) && $_POST['filterable_with_results'] == 1) ? 1 : '0';
			$category_id = $_POST['category_id'];
			$attribute_code = $_POST['attribute_code'];
			$attribute_code = strtolower($attribute_code);

			$codeFund = $this->EavAttributesModel->getAttributeCode($attribute_code, $attribute_id, $fbc_user_id, 2);
			if ($attribute_id != 0) {
				if ($codeFund  && $codeFund->id != $attribute_id) {
					echo json_encode(array('status' => 400, 'message' => "Attribute code exist."));
					exit;
				}
			} else if ($attribute_id == 0) {
				if ($codeFund) {
					echo json_encode(array('status' => 400, 'message' => "Attribute code exist."));
					exit;
				}
			}



			if (isset($attribute_id) && $attribute_id > 0) {

				$updateData = array(
					'attr_code'    		=> $attribute_code,
					'attr_name'			=> $_POST['attribute_name'],
					'attr_description'	=> $_POST['attribute_description'],
					//'attr_type'			=> 2,
					//'created_by' 		=> $fbc_user_id,
					//'created_by_type' 	=> 1,
					'updated_at'		=> time(),
					'ip'				=> $_SERVER['REMOTE_ADDR'],
				);
				$this->db->where('id', $attribute_id);
				$this->db->update('eav_attributes', $updateData);

				$check_attr_visib_exist = $this->SellerProductModel->getSingleDataByID('fbc_users_attributes_visibility', array('attr_id' => $attribute_id), 'id');
				if (isset($check_attr_visib_exist) && $check_attr_visib_exist->id != '') {
					$fbc_atr_setting_upate = array('filterable_with_results' => $filterable_with_results, 'display_on_frontend' => $display_on_frontend, 'updated_at' => time());

					$where_arr = array('attr_id' => $attribute_id);

					$this->SellerProductModel->updateData('fbc_users_attributes_visibility', $where_arr, $fbc_atr_setting_upate);
				} else {
					$fbc_atr_setting_insert = array('attr_id' => $attribute_id, 'filterable_with_results' => $filterable_with_results, 'display_on_frontend' => $display_on_frontend, 'created_by' => $fbc_user_id, 'created_at' => time());
					$this->SellerProductModel->insertData('fbc_users_attributes_visibility', $fbc_atr_setting_insert);
				}

				if ($_POST['tagsValues'] != '') {
					$tagsValues = explode(',', $_POST['tagsValues']);

					$tagsValues = array_values(array_filter($tagsValues));

					$OldOptionsBySeller = $this->CommonModel->getMultiDataById('eav_attributes_options', array('created_by' => $fbc_user_id, 'attr_id' => $attribute_id), 'id,attr_id,attr_options_name');
					$prev_options_ids = array();
					if (isset($OldOptionsBySeller) && count($OldOptionsBySeller) > 0) {
						foreach ($OldOptionsBySeller as $ct) {
							$prev_options_ids[] = $ct->attr_options_name;
						}
					}

					$result = array_diff_assoc($prev_options_ids, $tagsValues);
					if (count($result) > 0) {
						foreach ($result as $val) {
							$cdeletedata = array('attr_options_name' => $val, 'created_by' => $fbc_user_id, 'attr_id' => $attribute_id);
							//$this->db->delete('eav_attributes_options', $cdeletedata);
							//$this->db->reset_query();
						}
					}


					foreach ($tagsValues as $key => $value) {
						$IsOptionExist = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('attr_options_name' => $value, 'attr_id' => $attribute_id), 'id');
						if (isset($IsOptionExist) && $IsOptionExist->id != '') {
						} else {
							$attributesData = array(
								'attr_id'    		=> $attribute_id,
								'attr_options_name'	=> $value,
								'created_by' 		=> $fbc_user_id,
								'shop_id'			=> $shop_id,
								'created_by_type' 	=> 1,
								'status'			=> 1,

								'created_at'		=> time(),
								'ip'				=> $_SERVER['REMOTE_ADDR'],
							);
							$this->db->insert('eav_attributes_options', $attributesData);
						}
					}
				}

				$arrResponse  = array('status' => 200, 'message' => 'Variant updated successfully.', 'category_id' => $category_id, 'attr_id' => $attribute_id);
			} else {
				//insert
				$insertData = array(
					'attr_code'    		=> $attribute_code,
					'attr_name'			=> $_POST['attribute_name'],
					'attr_type'			=> 2,
					'attr_description'	=> $_POST['attribute_description'],
					'attr_properties' 	=> 5,
					'created_by' 		=> $fbc_user_id,
					'shop_id'			=> $shop_id,
					'created_by_type' 	=> 1,
					'status'			=> 1,
					'created_at'		=> time(),
					'ip'				=> $_SERVER['REMOTE_ADDR'],
				);
				$this->db->insert('eav_attributes', $insertData);
				$insert_id = $this->db->insert_id();



				$fbc_atr_setting_insert = array('attr_id' => $insert_id, 'filterable_with_results' => $filterable_with_results, 'display_on_frontend' => $display_on_frontend, 'created_by' => $fbc_user_id, 'created_at' => time());
				$this->SellerProductModel->insertData('fbc_users_attributes_visibility', $fbc_atr_setting_insert);

				$tagsValues = explode(',', $_POST['tagsValues']);
				$tagsValues = array_filter(array_unique($tagsValues));


				if (isset($tagsValues) && count($tagsValues) > 0) {
					foreach ($tagsValues as $key => $value) {
						if ($value != '') {
							$attributesData = array(
								'attr_id'    		=> $insert_id,
								'attr_options_name'	=> $value,
								'created_by' 		=> $fbc_user_id,
								'shop_id'			=> $shop_id,
								'created_by_type' 	=> 1,
								'status'			=> 1,
								'created_at'		=> time(),
								'ip'				=> $_SERVER['REMOTE_ADDR'],
							);
							$this->db->insert('eav_attributes_options', $attributesData);
						}
					}
				}



				$arrResponse  = array('status' => 200, 'message' => 'Variant added successfully.', 'category_id' => $category_id, 'attr_id' => $insert_id);
			}

			echo json_encode($arrResponse);
			exit;
		}
	}



	function openaddvariantpopup()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		//$category_id=(isset($_POST['category_id']) && $_POST['category_id']>0)?$_POST['category_id']:0;

		$data['fbc_user_id'] = $fbc_user_id;
		$data['category_id'] = $category_id = (isset($_POST['category_id']) && $_POST['category_id'] > 0) ? $_POST['category_id'] : 0;

		$View = $this->load->view('seller/products/add_variant', $data, true);
		$this->output->set_output($View);
	}


	function loadvariants()
	{
		if ($_POST['pro_code'] != '' && (count($_POST['variant_options']) > 0)) {
			$pro_code = $_POST['pro_code'];
			$variant_options = $_POST['variant_options'];
			$opt_main_arr = array();

			if (isset($variant_options) && count($variant_options) > 0) {
				$VariantList = $this->EavAttributesModel->getVariantDataByIds($variant_options);
				$data['VariantList'] = $VariantList;

				if (isset($VariantList) && count($VariantList) > 0) {
					foreach ($VariantList  as  $val) {
						$attr_id = $val['id'];
						$OptionList = $this->CommonModel->GetDropDownOptions($val['id']);
						$opt_single_arr = array();
						if (isset($OptionList) && count($OptionList) > 0) {
							foreach ($OptionList as $opt) {
								$opt_single_arr[] = $opt['id'];
							}
							$opt_main_arr[$attr_id] = $opt_single_arr;
						}
					}
				}

				if (isset($opt_main_arr) && count($opt_main_arr) > 0) {
					$Combinations = fbc_cartesian_product($opt_main_arr);
				} else {
					$Combinations = array();
				}
				$data['Combinations'] = $Combinations;

				$data['pro_code'] = $pro_code;

				$View = $this->load->view('seller/products/dynamic_variant_loop', $data, true);
				$this->output->set_output($View);
			}
		} else {
			echo "error";
			exit;
		}
	}


	function addsinglevariant()
	{
		if ($_POST['pro_code'] != '' && $_POST['variant_options'] != '') {
			$pro_code = $_POST['pro_code'];
			$variant_options = $_POST['variant_options'];


			$variant_options_arr = array();


			if (strpos($variant_options, ',') !== false) {
				$variant_options_arr = explode(',', $variant_options);
			} else {

				$variant_options_arr[] = $variant_options;
			}
			$opt_main_arr = array();

			if (isset($variant_options_arr) && count($variant_options_arr) > 0) {
				$VariantList = $this->EavAttributesModel->getVariantDataByIds($variant_options_arr);

				$data['VariantList'] = $VariantList;

				if (isset($VariantList) && count($VariantList) > 0) {
					foreach ($VariantList  as  $val) {
						$attr_id = $val['id'];
						$OptionList = $this->CommonModel->GetDropDownOptions($val['id']);
						$opt_single_arr = array();
						if (isset($OptionList) && count($OptionList) > 0) {
							foreach ($OptionList as $opt) {
								$opt_single_arr[] = $opt['id'];
							}
							$opt_main_arr[$attr_id] = $opt_single_arr;
						}
					}
				}

				if (isset($opt_main_arr) && count($opt_main_arr) > 0) {
					$Combinations = fbc_cartesian_product($opt_main_arr);
				} else {
					$Combinations = array();
				}

				if (isset($_POST['flag']) && $_POST['flag'] == 'edit') {
					$data['variant_products_count'] = $_POST['variant_products_count'];
					$data['get_own_products_count'] = $_POST['get_own_products_count'];
				}

				$data['Combinations'] = $Combinations;

				$data['pro_code'] = $pro_code;
				$data['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
				$View = $this->load->view('seller/products/load_variants', $data, true);
				$this->output->set_output($View);
			}
		} else {
			echo "error";
			exit;
		}
	}

	function checkproductcode()
	{
		if (isset($_POST['product_code']) &&  $_POST['product_code'] != '') {
			if ($_POST['flag'] == 'add') {
				$product_code = $_POST['product_code'];
				$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('product_code' => $product_code, 'remove_flag' => 0), 'id,name');
				if (isset($PC_Exist) && $PC_Exist->id != '') {
					echo 'false';
					exit;
				} else {
					echo 'true';
					exit;
				}
			} else if ($_POST['flag'] == 'edit') {
				$pid = $_POST['pid'];
				$product_code = $_POST['product_code'];
				$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('product_code' => $product_code, 'remove_flag' => 0), 'id,name');
				if (isset($PC_Exist) && $PC_Exist->id != $pid) {
					echo 'false';
					exit;
				} else {
					echo 'true';
					exit;
				}
			} else {
				echo 'true';
				exit;
			}
		} else {
			echo 'true';
			exit;
		}
	}


	function checkskuexist()
	{
		if (isset($_POST['sku']) &&  $_POST['sku'] != '') {


			if ($_POST['product_type'] == 'simple') {
				if ($_POST['flag'] == 'add') {
					$sku = $_POST['sku'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('sku' => $sku, 'remove_flag' => 0), 'id,name');
					if (isset($PC_Exist) && $PC_Exist->id != '') {
						echo 'false';
						exit;
					} else {
						echo 'true';
						exit;
					}
				} else if ($_POST['flag'] == 'edit') {
					$pid = $_POST['pid'];
					$sku = $_POST['sku'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('sku' => $sku, 'remove_flag' => 0), 'id,name');
					if (isset($PC_Exist) && $PC_Exist->id != $pid) {
						echo 'false';
						exit;
					} else {
						echo 'true';
						exit;
					}
				} else {
					echo 'true';
					exit;
				}
			} else if ($_POST['product_type'] == 'configurable') {
				if ($_POST['flag'] == 'add') {
					$sku = $_POST['sku'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('sku' => $sku, 'remove_flag' => 0), 'id,name');
					if (isset($PC_Exist) && $PC_Exist->id != '') {
						echo 'false';
						exit;
					} else {
						echo 'true';
						exit;
					}
				} else if ($_POST['flag'] == 'edit') {
					$pid = $_POST['pid'];
					$sku = $_POST['sku'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('sku' => $sku, 'remove_flag' => 0), 'id,name');
					if (isset($PC_Exist) && $PC_Exist->id != $pid) {
						echo 'false';
						exit;
					} else {
						echo 'true';
						exit;
					}
				} else {
					echo 'true';
					exit;
				}
			} else {
				echo 'true';
				exit;
			}
		} else {
			echo 'true';
			exit;
		}
	}

	function checkbarcodeexist()
	{
		if (isset($_POST['barcode']) &&  $_POST['barcode'] != '') {
			if ($_POST['product_type'] == 'simple') {
				if ($_POST['flag'] == 'add') {
					$barcode = $_POST['barcode'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('barcode' => $barcode, 'remove_flag' => 0), 'id,name');
					if (isset($PC_Exist) && $PC_Exist->id != '') {
						echo 'false';
						exit;
					} else {
						echo 'true';
						exit;
					}
				} else if ($_POST['flag'] == 'edit') {
					$pid = $_POST['pid'];
					$barcode = $_POST['barcode'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('barcode' => $barcode, 'remove_flag' => 0), 'id,name');
					if (isset($PC_Exist) && $PC_Exist->id != $pid) {
						echo 'false';
						exit;
					} else {
						echo 'true';
						exit;
					}
				} else {
					echo 'true';
					exit;
				}
			} else if ($_POST['product_type'] == 'configurable') {
				if ($_POST['flag'] == 'add') {
					$barcode = $_POST['barcode'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('barcode' => $barcode, 'remove_flag' => 0), 'id,name');
					if (isset($PC_Exist) && $PC_Exist->id != '') {
						echo 'false';
						exit;
					} else {
						echo 'true';
						exit;
					}
				} else if ($_POST['flag'] == 'edit') {
					$pid = $_POST['pid'];
					$barcode = $_POST['barcode'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('barcode' => $barcode, 'remove_flag' => 0), 'id,name');
					if (isset($PC_Exist) && $PC_Exist->id != $pid) {
						echo 'false';
						exit;
					} else {
						echo 'true';
						exit;
					}
				} else {
					echo 'true';
					exit;
				}
			} else {
				echo 'true';
				exit;
			}
		} else {
			echo 'true';
			exit;
		}
	}

	public function update_inv_type()
	{
		$product_id = $this->uri->segment(4);
		$inv_type_dp = isset($_POST['inv_type_dp']) ? $this->CommonModel->custom_filter_input($_POST['inv_type_dp']) : '';
		if ($inv_type_dp == '') {
			$arrResponse  = array('status' => 400, 'message' => "Please Select Type to update.");
			echo json_encode($arrResponse);
			exit;
		}
		$ckb_Variants = isset($_POST['ckb_Variant']) ? $_POST['ckb_Variant'] : array();
		// $ckbCheckAllVariants=isset($_POST['ckbCheckAllVariants'])?$this->CommonModel->custom_filter_input($_POST['ckbCheckAllVariants']):'';
		// print_r($ckbCheckAllVariants);
		if (!empty($ckb_Variants) &&  $ckb_Variants != '') {
			foreach ($ckb_Variants as $value) {
				$updatedata = array(
					'product_inv_type' => $inv_type_dp,
					'updated_at' => time(),
				);
				$where_arr = array('id' => $value);
				$rows_affected = $this->SellerProductModel->updateData('products', $where_arr, $updatedata);
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => "Please Select Variants checkbox to update.");
			echo json_encode($arrResponse);
			exit;
		}
		if ($rows_affected) {
			$arrResponse  = array('status' => 200, 'message' => "Product updated successfully.");
			echo json_encode($arrResponse);
			exit;
		} else {
			$arrResponse  = array('status' => 400, 'message' => "Something went wrong.");
			echo json_encode($arrResponse);
			exit;
		}
	}

	public function openeditproductpopup()
	{
		if (isset($_POST['product_id']) && $_POST['product_id'] != '') {
			$product_id = $_POST['product_id'];
			$data['product_id'] = $product_id;
			$data['code'] = $code = $_POST['code'];
			$data['codeName'] = $codeName = $this->Multi_Languages_Model->getCodeName($code);
			$data['getProduct'] =  $this->SellerProductModel->getMultiLangProduct($product_id, $code);
			$data['ProductData'] = $ProductData = $this->SellerProductModel->getSingleDataByID('products', array('id' => $product_id), '');
			$View = $this->load->view('seller/products/multi_lag_product', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	public function saveProductTranslate()
	{
		$fbc_user_id = $_SESSION['LoginID'];
		if (isset($_POST['product_name_lang']) && $_POST['product_name_lang'] != '') {
			$id = $_POST['hidden_product_id'];
			$code = $_POST['code'];
			$checkProduct = $this->SellerProductModel->countCustomProduct($id, $code);
			if ($checkProduct > 0) {
				$where_arr = array('product_id' => $id, 'lang_code' => $code);
				$updatetdata = array(
					'name' => $_POST['product_name_lang'],
					'highlights' => $_POST['highlights_lang'],
					'description' => $_POST['description_lang'],
					'meta_description' => $_POST['meta_description_lang'],
					'meta_keyword' => $_POST['meta_keyword_lang'],
					'meta_title' => $_POST['meta_title_lang'],
					'product_id' => $id,
					'lang_code' => $_POST['code'],
					'updated_at' => time(),
					'ip' => $_SERVER['REMOTE_ADDR'],
				);
				$vat_id = $this->SellerProductModel->updateProductData('multi_lang_products', $where_arr, $updatetdata);
				echo json_encode(array('flag' => 1, 'msg' => "Product Translation Updated Successfully."));
				exit();
			} else {
				$insertdata = array(
					'name' => $_POST['product_name_lang'],
					'highlights' => $_POST['highlights_lang'],
					'description' => $_POST['description_lang'],
					'meta_description' => $_POST['meta_description_lang'],
					'meta_keyword' => $_POST['meta_keyword_lang'],
					'meta_title' => $_POST['meta_title_lang'],
					'product_id' => $id,
					'lang_code' => $_POST['code'],
					'created_at' => time(),
					'created_by' => $fbc_user_id,
					'ip' => $_SERVER['REMOTE_ADDR'],
				);
				$this->SellerProductModel->insertData('multi_lang_products', $insertdata);
				echo json_encode(array('flag' => 1, 'msg' => "Product Translation Added Successfully."));
				exit();
			}
		}
	}

	function editproduct()
	{



		$product_id = $this->uri->segment(4);

		$ProductData = $this->SellerProductModel->getSingleDataByID('products', array('id' => $product_id), '');
		if ($ProductData == '') {
			redirect('/seller/warehouse');
		}
		if (empty($product_id)) {
			redirect('/seller/warehouse');
		} else {

			$fbc_user_id	=	$this->session->userdata('LoginID');

			$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag();
			if (empty($_POST)) {




				$data['CustomerTypeMaster'] = $this->SellerProductModel->getMultiDataById('customers_type_master', array(), '');
				$data['publishers'] = $publishers = $this->CommonModel->getAllPublishers();
				$data['CategoryTree'] = $this->CategoryModel->get_categories_for_shop();

				$data['ProductData'] = $ProductData = $this->SellerProductModel->getSingleDataByID('products', array('id' => $product_id), '');
				// $customer_type_ids=$ProductData->customer_type_ids;
				$customer_type_ids_arr = array();
				if (isset($ProductData->customer_type_ids) && $ProductData->customer_type_ids != '') {
					if (strpos($customer_type_ids, ',') !== false) {
						$customer_type_ids_arr = explode(',', $customer_type_ids);
					} else {
						$customer_type_ids_arr[] = $customer_type_ids;
					}
				} else {
					$customer_type_ids_arr = array();
				}

				$customer_type_ids_arr = array_filter($customer_type_ids_arr, function ($v) {
					return $v !== false && !is_null($v) && ($v != '' || $v == '0');
				});

				$data['customer_type_ids_selected'] = $customer_type_ids_arr;

				$MainCat = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 0), 'category_ids');

				$root_cat_arr = array();
				if (isset($MainCat) && count($MainCat)) {
					foreach ($MainCat as $val) {
						$root_cat_arr[] = $val->category_ids;
					}
				}
				$data['cat_level_zero_selected'] = $root_cat_arr;
				//print_r($root_cat_arr);

				$SubCat = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 1), 'category_ids');
				$sub_cat_arr = array();
				if (isset($SubCat) && count($SubCat)) {
					foreach ($SubCat as $val) {
						$sub_cat_arr[] = $val->category_ids;
					}
				}
				$data['cat_level_one_selected'] = $sub_cat_arr;

				//print_r($sub_cat_arr);


				$TagsCat = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 2), 'category_ids');
				$tags_cat_arr = array();
				if (isset($TagsCat) && count($TagsCat)) {
					foreach ($TagsCat as $val) {
						$tags_cat_arr[] = $val->category_ids;
					}
				}
				$data['cat_level_two_selected'] = $tags_cat_arr;

				$ProductAttr = $this->SellerProductModel->getMultiDataById('products_attributes', array('product_id' => $product_id), '');
				$data['AttributesList'] = $ProductAttr;

				$ProductMedia = $this->SellerProductModel->getMultiDataById('products_media_gallery', array('product_id' => $product_id), '');

				//print_r($ProductMedia);die();
				$data['ProductMedia'] = $ProductMedia;

				if ($ProductData->product_type == 'configurable') {
					$VariantMaster = $this->SellerProductModel->getVariantMasterForProducts($product_id);
					$data['VariantMaster'] = $VariantMaster;

					$VariantProductsRow = $this->SellerProductModel->getVariantProducts($product_id);

					if ($VariantProductsRow->product_ids == '') {
						$data['variant_products_count'] = $variant_products_count = 0;
					} else {
						$variant_product_ids = explode(',', $VariantProductsRow->product_ids);
						$data['variant_products_count'] = $variant_products_count = count($variant_product_ids);
					}

					$get_own_products_count = $this->SellerProductModel->getVariantProductscount($VariantProductsRow->product_ids);

					$data['get_own_products_count'] = $get_own_products_count;

					if (isset($VariantProductsRow->product_ids) && $VariantProductsRow->product_ids != '') {
						if (strpos($VariantProductsRow->product_ids, ',') !== false) {
							$VariantProductsIds = explode(',', $VariantProductsRow->product_ids);
						} else {
							$VariantProductsIds[] = $VariantProductsRow->product_ids;
						}

						$VariantProducts = $this->SellerProductModel->getVariantProductsByIds($VariantProductsIds);
					} else {
						$VariantProducts = array();
					}

					$data['VariantProducts'] = $VariantProducts;
				} elseif ($ProductData->product_type == 'bundle') {

					$BundleProduct = $this->SellerProductModel->getBundleForProducts($product_id);
					$data['BundleProduct'] = $BundleProduct;
				} else {
					$ProductStock = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'qty,available_qty');
					$data['ProductStock'] = $ProductStock;
				}
				$url = base_url();
				$data['url'] =  rtrim($url, "/admin");

				//commission data
				$data['publication'] = $this->SellerProductModel->get_publication();

				//get gifts to data
				$data['giftsTo'] = $this->SellerProductModel->get_gifts_data();

				$this->load->view('seller/products/edit_product', $data);
			} else {

				// echo "<pre>";print_r($_FILES);
				// echo "<pre>";
				// print_r($_POST);
				// die();
				//echo "<pre>";print_r($_POST['attributes']);
				if (empty($_POST['product_name']) || empty($_POST['product_code']) ||  empty($_POST['description']) || empty($_POST['highlights'])) {
					$arrResponse  = array('status' => 403, 'message' => 'Please enter all mandatory fields!');
					echo json_encode($arrResponse);
					exit;
				} else if ($_POST['product_type'] == 'configurable' && $_POST['added_variant'] == '') {
					$arrResponse  = array('status' => 403, 'message' => 'Please add at least one variations!');
					echo json_encode($arrResponse);
					exit;
				} else {

					$OldProductData = $this->SellerProductModel->getSingleDataByID('products', array('id' => $product_id), '');
					$old_product_name = $OldProductData->name;

					$product_name = $this->CommonModel->custom_filter_input($_POST['product_name']);
					$product_code = $this->CommonModel->custom_filter_input($_POST['product_code']);
					$langTitle = $this->CommonModel->custom_filter_input($_POST['langTitle']);
					
					$description = $_POST['description'];
					$highlights = $_POST['highlights'];

					$lang_description = $_POST['lang_description'];
					$lang_highlights = $_POST['lang_highlights'];
					$product_reviews_code = $this->CommonModel->custom_filter_input($_POST['product_reviews_code']);
					$launch_date = (isset($_POST['launch_date']) && $_POST['launch_date'] != '') ? strtotime($_POST['launch_date']) : strtotime(date('d-m-Y'));
					$estimate_delivery_time = $this->CommonModel->custom_filter_input($_POST['estimate_delivery_time']);
					$product_return_time = $this->CommonModel->custom_filter_input($_POST['product_return_time']);
					$product_type = $this->CommonModel->custom_filter_input($_POST['product_type']);


					if (isset($product_type) && $product_type == 'bundle') {

						$sku = '';
						$barcode = '';
						$gifts = '';
						$sub_issue = '';

						$price = $this->CommonModel->custom_filter_input($_POST['bundle_price']);
						$cost_price = $price;
						$webshop_price = $_POST['bundle_webshopprice'];


						$tax_amount = $_POST['tax_amount'];

						if (isset($_POST['bundle_tax_percent']) && !empty($_POST['bundle_tax_percent'])) {
							$tax_percent = max($_POST['bundle_tax_percent']);
						} else {
							$tax_percent = 0.00;
						}
					} else {

						$sku = isset($_POST['sku']) ? $this->CommonModel->custom_filter_input($_POST['sku']) : '';
						//$barcode=isset($_POST['sku'])?$this->CommonModel->custom_filter_input($_POST['barcode']):'';
						$stock_qty = isset($_POST['stock_qty']) ? $this->CommonModel->custom_filter_input($_POST['stock_qty']) : '';
						$price = isset($_POST['price']) ? $this->CommonModel->custom_filter_input($_POST['price']) : '';
						$cost_price = isset($_POST['cost_price']) ? $this->CommonModel->custom_filter_input($_POST['cost_price']) : '';
					}

					$status = $_POST['status'];
					$approval_status = $_POST['approval_status'];

					$product_returnable = $_POST['product-return'] ?? '1';
					$coming_product = $_POST['coming-product'];
					$is_fragile = $_POST['is_fragile'];


					//$weight=isset($_POST['weight'])?$this->CommonModel->custom_filter_input($_POST['weight']):'';



					$attributes = isset($_POST['attributes']) ? $_POST['attributes'] : array();

					$customer_type_ids = isset($_POST['customer_type_ids']) ? $_POST['customer_type_ids'] : array();
					if (count($customer_type_ids) > 0) {
						$customer_type_ids_str = implode(',', $customer_type_ids);
					} else {
						$customer_type_ids_str = '';
					}

					$gender = isset($_POST['gender']) ? $_POST['gender'] : '';

					$default_image = (isset($_POST['default_image']) && $_POST['default_image'] != '') ? $_POST['default_image'] : $OldProductData->base_image;

					$digit_pdf = (isset($_POST['digit_pdf']) && $_POST['digit_pdf'] != '') ? $_POST['digit_pdf'] : $OldProductData->digit_pdf;


					$tax_percent = isset($_POST['tax_percent']) ? $this->CommonModel->custom_filter_input($_POST['tax_percent']) : '';
					if ($product_type != 'bundle') {
						$webshop_price = isset($_POST['webshop_price']) ? $this->CommonModel->custom_filter_input($_POST['webshop_price']) : '';
					}


					$category = (isset($_POST['category']) && count($_POST['category']) > 0) ? $_POST['category'] : array();
					$sub_category = (isset($_POST['sub_category']) && count($_POST['sub_category']) > 0) ? $_POST['sub_category'] : array();
					$child_category = (isset($_POST['child_category']) && count($_POST['child_category']) > 0) ? $_POST['child_category'] : array();


					if (isset($product_type) && $product_type == 'bundle') {
					} else {

						if ($product_type == 'simple') {
							if ($Rounded_price_flag == 1) {
								$RowInfo = $this->SellerProductModel->calculate_webshop_price($price, $tax_percent);
								$tax_amount = $RowInfo['tax_amount'];
								$webshop_price = $RowInfo['webshop_price'];
							} else {
								$webshop_price = $price;
								$tax_amount = 0;
								if ($price > 0 && $tax_percent > 0) {
									$tax_amount = ($tax_percent / 100) * $price;
									$webshop_price = $tax_amount + $price;
								}
							}
						} else {
							$webshop_price = 0;
							$tax_amount = 0;
						}
					}


					if (isset($gender) && is_array($gender)) {
						$gender = implode(',', $gender);
					} else {
						$gender = $gender;
					}

					$meta_title = isset($_POST['meta_title']) ? $_POST['meta_title'] : '';
					$meta_keyword = isset($_POST['meta_keyword']) ? $_POST['meta_keyword'] : '';
					$meta_description = isset($_POST['meta_description']) ? $_POST['meta_description'] : '';
					$search_keywords = isset($_POST['search_keywords']) ? $_POST['search_keywords'] : '';
					$promo_reference = isset($_POST['promo_reference']) ? $_POST['promo_reference'] : '';

					//product commission code
					$publication_id = isset($_POST['product_publication']) ? $_POST['product_publication'] : '';
					$type_of_commission = isset($_POST['type-of-commission']) ? $_POST['type-of-commission'] : '';
					$pub_com_percentage = isset($_POST['pub_com_percentage']) ? $_POST['pub_com_percentage'] : '';

					$gifts = isset($_POST['gifts_to']) ? $this->CommonModel->custom_filter_input($_POST['gifts_to']) : '';
					$sub_issue = isset($_POST['sub_issues']) ? $this->CommonModel->custom_filter_input($_POST['sub_issues']) : '';


					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('product_code' => $product_code, 'remove_flag' => 0), 'id,name');
					if (isset($PC_Exist) && $PC_Exist->id != $product_id) {
						$arrResponse  = array('status' => 403, 'message' => 'Product Code exist.');
					} else {

						if ($old_product_name != $product_name) {
							$slug = url_title($product_name);
							$url_key = strtolower($slug);
							//$url_key=$this->SellerProductModel->createproductslug($url_key);

							$slugcount = $this->SellerProductModel->productslugcount($product_name, $product_id);
							if ($slugcount > 0) {

								$url_key = $url_key . "-" . $slugcount;
							} else {
								$url_key = $url_key . "-0";
							}
						} else {
							$url_key = $OldProductData->url_key;
						}
						if (isset($_POST['shipping_amount']) && $_POST['shipping_amount'] > 0) {
							$shipping_amount =  $_POST['shipping_amount'];
						} else {
							$shipping_amount = 0;
						}
						/*-------------------Insert Product :start--------------------------------------*/

						$updatedata = array(
							'name' => $product_name,
							'product_code' => $product_code,
							'lang_title' => $langTitle,
							'url_key' => $url_key,
							'meta_title' => $meta_title,
							'meta_keyword' => $meta_keyword,
							'meta_description' => $meta_description,
							'search_keywords' => $search_keywords,
							'promo_reference' => $promo_reference,
							//'weight'=>$weight,
							'sku' => $sku,
							'price' => $price,
							// 'shipping_amount ' => $shipping_amount,
							'cost_price' => $cost_price,
							'tax_percent' => $tax_percent,
							'tax_amount' => $tax_amount,
							'webshop_price' => $webshop_price,
							'gender' => $gender,
							'publisher_id' => $publication_id,
							'pub_com_per_type' => $type_of_commission,
							'pub_com_percent' => $pub_com_percentage,
							'description' => $description,
							'highlights' => $highlights,
							'lang_description' => $lang_description,
							'lang_highlights' => $lang_highlights,
							'base_image' => $default_image,
							'product_reviews_code' => $product_reviews_code,
							'status' => $status,
							'approval_status' => $approval_status,
							'can_be_returned' => $product_returnable,
							'coming_soon_flag' => $coming_product,
							'is_fragile_flag' => $is_fragile,
							'launch_date' => $launch_date,
							'estimate_delivery_time' => $estimate_delivery_time,
							'product_return_time' => $product_return_time,
							'product_type' => $product_type,
							'updated_at' => time(),
							//gifts to and sub issue
							'gift_id' => $gifts,
							'sub_issues' => $sub_issue,
							'ip' => $_SERVER['REMOTE_ADDR']
						);

						$where_arr = array('id' => $product_id);
						$this->SellerProductModel->updateData('products', $where_arr, $updatedata);

						/**--------------Product url update-----------------------------**/


						if ($product_id) {

							/*--------------------Main product created-------------------------------------------------------*/
							if (isset($product_type) && $product_type == 'bundle') {

								/*-------------------Insert /Update products_bundles :start--------------------------------------*/
								if (isset($_POST['bundle_product_id']) && count($_POST['bundle_product_id']) > 0) {
									for ($bcount = 0; $bcount < count($_POST['bundle_product_id']); $bcount++) {
										//$bundle_product_id=$product_id;
										//$bundle_sku=$this->CommonModel->custom_filter_input($_POST['bundle_sku'][$bcount]);
										//$bundle_barcode=$this->CommonModel->custom_filter_input($_POST['bundle_barcode'][$bcount]);
										//	bundle_row_type
										$bundle_row_type = $this->CommonModel->custom_filter_input($_POST['bundle_row_type'][$bcount]);
										$item_product_id = $this->CommonModel->custom_filter_input($_POST['bundle_product_id'][$bcount]);
										$bundle_product_parent_id = $this->CommonModel->custom_filter_input($_POST['bundle_product_parent_id'][$bcount]);
										$bundle_product_type = $this->CommonModel->custom_filter_input($_POST['bundle_product_type'][$bcount]);
										$bundle_variant_options_get = $this->CommonModel->custom_filter_input($_POST['bundle_variant_options'][$bcount]);
										if (isset($bundle_variant_options_get) && !empty($bundle_variant_options_get)) {
											$bundle_variant_options = str_replace("'", '"', $bundle_variant_options_get);
										} else {
											$bundle_variant_options = $bundle_variant_options_get;
										}
										$bundle_price = $this->CommonModel->custom_filter_input($_POST['price'][$bcount]);
										$bundle_tax_percent = $this->CommonModel->custom_filter_input($_POST['bundle_tax_percent'][$bcount]);
										$bundle_tax_amount = $this->CommonModel->custom_filter_input($_POST['bundle_tax_amount'][$bcount]);
										$bundle_webshop_price = $this->CommonModel->custom_filter_input($_POST['bundle_webshop_price'][$bcount]);
										$bundle_default_qty = $this->CommonModel->custom_filter_input($_POST['bundle_default_qty'][$bcount]);
										$bundle_position = $this->CommonModel->custom_filter_input($_POST['bundle_position'][$bcount]);

										if ($bundle_row_type == 'edit') {
											// update bundle product
											$bundle_id_row = $this->CommonModel->custom_filter_input($_POST['bundle_id_row'][$bcount]);

											$updateBundleProductdata = array(
												// 'bundle_product_id'=>$product_id,
												// 'product_id'=>$item_product_id,
												// 'product_parent_id'=>$bundle_product_parent_id,
												// 'product_type'=>$bundle_product_type,
												// 'variant_options'=>$bundle_variant_options,
												//'sku'=>$bundle_sku,
												//'barcode'=>$bundle_barcode,
												'price' => $bundle_price,
												'tax_percent' => $bundle_tax_percent,
												'tax_amount' => $bundle_tax_amount,
												'webshop_price' => $bundle_webshop_price,
												'default_qty' => $bundle_default_qty,
												'position' => $bundle_position,
												'updated_at' => time(),
												'ip' => $_SERVER['REMOTE_ADDR']
											);

											$where_arr_bundle_product = array('id' => $bundle_id_row);
											$this->SellerProductModel->updateData('products_bundles', $where_arr_bundle_product, $updateBundleProductdata);
										} else {
											// insert bundle product
											$insertdata = array(
												'bundle_product_id' => $product_id,
												'product_id' => $item_product_id,
												'product_parent_id' => $bundle_product_parent_id,
												'product_type' => $bundle_product_type,
												'variant_options' => $bundle_variant_options,
												//'sku'=>$bundle_sku,
												//'barcode'=>$bundle_barcode,
												'price' => $bundle_price,
												'tax_percent' => $bundle_tax_percent,
												'tax_amount' => $bundle_tax_amount,
												'webshop_price' => $bundle_webshop_price,
												'default_qty' => $bundle_default_qty,
												'position' => $bundle_position,
												'created_at' => time(),
												'ip' => $_SERVER['REMOTE_ADDR']
											);
											$product_bundle_id = $this->SellerProductModel->insertData('products_bundles', $insertdata);
										}
									}
								}
							} else {
								if ($product_type == 'simple') {
									$OldProductQtyData = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'qty,available_qty');


									if ($OldProductQtyData->qty == $stock_qty) {
									} else {

										if ($OldProductQtyData->available_qty == $OldProductQtyData->qty) {
											$new_available_qty = $stock_qty;
										} else {
											if (isset($OldProductQtyData) && $OldProductQtyData->available_qty > 0) {
												$old_available_qty = $OldProductQtyData->available_qty;

												$old_ordered_qty = $OldProductQtyData->qty - $old_available_qty;
											} else {
												$old_ordered_qty = 0;
											}

											if ($stock_qty == 0) {
												$new_available_qty = 0;
											} else if ($stock_qty > 0 && $stock_qty > $OldProductQtyData->qty) {
												$new_available_qty = $stock_qty - $old_ordered_qty;
											} else {
												$new_available_qty = $stock_qty - $old_ordered_qty;
												$new_available_qty = ($new_available_qty <= 0) ? 0 : $new_available_qty;
											}
										}


										$stock_update = array('qty' => $stock_qty, 'available_qty' => $new_available_qty);
										$whr_qty_arr = array('product_id' => $product_id);
										$this->SellerProductModel->updateData('products_inventory', $whr_qty_arr, $stock_update);
									}
								}
							}



							/*------------categoroy update : start------------------------------------------*/

							if (empty($category) || count($category) <= 0) {

								$cdeletedata = array('level' => 0, 'product_id' => $product_id);
								$this->SellerProductModel->deleteDataById('products_category', $cdeletedata);
							} else {

								$oldMainCats = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 0), 'id,category_ids');
								if (isset($oldMainCats) && count($oldMainCats) > 0) {

									$old_main_category_arr = array();
									foreach ($oldMainCats as $val) {
										$old_main_category_arr[] = $val->category_ids;
									}

									$old_main_category_arr = array_values(array_filter($old_main_category_arr));

									$result = array_merge(array_diff($category, $old_main_category_arr), array_diff($old_main_category_arr, $category));

									if (count($result) > 0) {
										foreach ($result as $val) {
											$cdeletedata = array('category_ids' => $val, 'level' => 0, 'product_id' => $product_id);
											$this->SellerProductModel->deleteDataById('products_category', $cdeletedata);
										}
									}
								}
							}

							if (empty($sub_category) || count($sub_category) <= 0) {
								$cdeletedata = array('level' => 1, 'product_id' => $product_id);
								$this->SellerProductModel->deleteDataById('products_category', $cdeletedata);
							} else {
								$oldMainCats = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 1), 'id,category_ids');
								if (isset($oldMainCats) && count($oldMainCats) > 0) {

									$old_sub_category_arr = array();
									foreach ($oldMainCats as $val) {
										$old_sub_category_arr[] = $val->category_ids;
									}

									$old_sub_category_arr = array_values(array_filter($old_sub_category_arr));

									$result = array_merge(array_diff($sub_category, $old_sub_category_arr), array_diff($old_sub_category_arr, $sub_category));

									if (count($result) > 0) {
										foreach ($result as $val) {
											$cdeletedata = array('category_ids' => $val, 'level' => 1, 'product_id' => $product_id);
											$this->SellerProductModel->deleteDataById('products_category', $cdeletedata);
										}
									}
								}
							}

							if (empty($child_category) || count($child_category) <= 0) {
								$cdeletedata = array('level' => 2, 'product_id' => $product_id);
								$this->SellerProductModel->deleteDataById('products_category', $cdeletedata);
							} else {
								$oldMainCats = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 2), 'id,category_ids');
								if (isset($oldMainCats) && count($oldMainCats) > 0) {

									$old_child_category_arr = array();
									foreach ($oldMainCats as $val) {
										$old_child_category_arr[] = $val->category_ids;
									}

									$old_child_category_arr = array_values(array_filter($old_child_category_arr));


									$result = array_merge(array_diff($child_category, $old_child_category_arr), array_diff($old_child_category_arr, $child_category));

									if (count($result) > 0) {
										foreach ($result as $val) {
											$cdeletedata = array('category_ids' => $val, 'level' => 2, 'product_id' => $product_id);
											$this->SellerProductModel->deleteDataById('products_category', $cdeletedata);
										}
									}
								}
							}


							if (isset($category) && count($category) > 0) {

								foreach ($category as $cat) {
									$check_cat_exist = $this->SellerProductModel->getSingleDataByID('products_category', array('category_ids' => $cat, 'level' => 0, 'product_id' => $product_id), '');

									if (empty($check_cat_exist)) {


										$root_cat_insert = array('product_id' => $product_id, 'category_ids' => $cat, 'level' => 0);
										$this->SellerProductModel->insertData('products_category', $root_cat_insert);

										// $checkbtb_level_zero=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cat,'level'=>0),'id');

										// if(empty($checkbtb_level_zero)){
										// $fbc_cat_insert=array('category_id'=>$cat,'level'=>0,'fbc_user_id'=>$fbc_user_id);
										// $this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_cat_insert);
										// }

									}
								}
							}

							//exit;

							if (isset($sub_category) && count($sub_category) > 0) {
								foreach ($sub_category as $cat) {
									$check_cat_exist = $this->SellerProductModel->getSingleDataByID('products_category', array('category_ids' => $cat, 'level' => 1, 'product_id' => $product_id), '');

									if (empty($check_cat_exist)) {
										$sub_cat_insert = array('product_id' => $product_id, 'category_ids' => $cat, 'level' => 1);
										$this->SellerProductModel->insertData('products_category', $sub_cat_insert);

										// $checkbtb_level_one=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cat,'level'=>1),'id');
										// if(empty($checkbtb_level_one)){
										// 	$fbc_subcat_insert=array('category_id'=>$cat,'level'=>1,'fbc_user_id'=>$fbc_user_id);
										// 	$this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_subcat_insert);
										// }
									}
								}
							}

							if (isset($child_category) && count($child_category) > 0) {
								foreach ($child_category as $cat) {

									$check_cat_exist = $this->SellerProductModel->getSingleDataByID('products_category', array('category_ids' => $cat, 'level' => 2, 'product_id' => $product_id), '');

									if (empty($check_cat_exist)) {

										$child_cat_insert = array('product_id' => $product_id, 'category_ids' => $cat, 'level' => 2);
										$this->SellerProductModel->insertData('products_category', $child_cat_insert);

										// $checkbtb_level_two=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cat,'level'=>2),'id');
										// if(empty($checkbtb_level_two)){
										// 	$fbc_subcat_insert=array('category_id'=>$cat,'level'=>2,'fbc_user_id'=>$fbc_user_id);
										// 	$this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_subcat_insert);
										// }
									}
								}
							}


							/*------------Categoroy update : End------------------------------------------*/

							if (isset($attributes) && count($attributes) > 0) {

								$new_attr_ids = array_keys($attributes);
								$new_attr_ids_arr = array_values(array_filter($new_attr_ids));

								$OldAttr = $this->SellerProductModel->getMultiDataById('products_attributes', array('product_id' => $product_id), 'attr_id');
								$prev_attr_ids = array();
								if (isset($OldAttr) && count($OldAttr) > 0) {
									foreach ($OldAttr as $val) {
										$prev_attr_ids[] = $val->attr_id;
									}
								}

								$result = array_diff_assoc($prev_attr_ids, $new_attr_ids_arr);

								if (count($result) > 0) {

									foreach ($result as $val) {
										$at_whr = array('product_id' => $product_id, 'attr_id' => $val);

										$result = $this->SellerProductModel->deleteDataById('products_attributes', $at_whr);
									}
								}
								foreach ($attributes as $attr_id => $attr_value) {
									if (in_array($attr_id, $prev_attr_ids)) {

										$AttrData = $this->CommonModel->getSingleDataByID('eav_attributes', array('id' => $attr_id), '');
										$attr_properties = $AttrData->attr_properties;
										if ($attr_properties == 6) {
											if (isset($attr_value) && count($attr_value) > 0) {
												$attr_value = implode(',', $attr_value);
											} else {
												$attr_value = '';
											}
										}

										$attr_update = array('attr_value' => $attr_value);
										$wh_at = array('product_id' => $product_id, 'attr_id' => $attr_id);
										$this->SellerProductModel->updateData('products_attributes', $wh_at, $attr_update);
									} else {

										$AttrData = $this->CommonModel->getSingleDataByID('eav_attributes', array('id' => $attr_id), '');
										$attr_properties = $AttrData->attr_properties;
										if ($attr_properties == 6) {
											if (isset($attr_value) && count($attr_value) > 0) {
												$attr_value = implode(',', $attr_value);
											} else {
												$attr_value = '';
											}
										}

										$attr_insert = array('product_id' => $product_id, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
										$this->SellerProductModel->insertData('products_attributes', $attr_insert);
									}
								}
							}


							$arrResponse  = array('status' => 200, 'message' => 'Product updated successfully.');

							$base_image = $default_image;

							/*------------------------Generate Barcode--Imae----------------------------------*/
							if (isset($product_type) && $product_type != 'bundle') {
								if ($product_type == 'simple') {

									/* if($OldProductData->barcode!=$barcode)
									{
										$barcode_update=array('barcode'=>$barcode,'updated_at'=>time());
										$where_arr=array('id'=>$product_id);

										$this->SellerProductModel->updateData('products',$where_arr,$barcode_update);
									} */
								} else if ($product_type == 'configurable') {
									$deleted_simple_ids_arr = array();
									$deleted_simple_ids = isset($_POST['deleted_vs']) ? $_POST['deleted_vs'] : '';
									if (isset($deleted_simple_ids) && $deleted_simple_ids != '') {

										if (strpos($deleted_simple_ids, ',') !== false) {
											$deleted_simple_ids_arr = explode(',', $deleted_simple_ids);
										} else {
											$deleted_simple_ids_arr[] = $deleted_simple_ids;
										}

										$result = array_values(array_filter($deleted_simple_ids_arr));

										if (isset($result) && count($result) > 0) {
											foreach ($result as $val) {

												$at_whr_inv = array('product_id' => $val);
												$this->SellerProductModel->deleteDataById('products_inventory', $at_whr_inv);

												//$at_whr_img = array('product_id'=>$product_id,'child_id'=>$val);
												//$this->SellerProductModel->deleteDataById('products_media_gallery',$at_whr_img);

												$at_whr1 = array('parent_id' => $product_id, 'product_id' => $val);
												$this->SellerProductModel->deleteDataById('products_variants', $at_whr1);

												$at_whr = array('product_type' => 'conf-simple', 'id' => $val);
												$this->SellerProductModel->deleteDataById('products', $at_whr);
											}
										}
									}
									/*-----------------------------------------------*/


									$added_variant_arr = array();
									$added_variant = $_POST['added_variant'];

									if (strpos($added_variant, ',') !== false) {
										$added_variant_arr = explode(',', $added_variant);
									} else {
										$added_variant_arr[] = $added_variant;
									}


									$variant_sku_arr = isset($_POST['variant_sku']) ? $_POST['variant_sku'] : array();
									$variant_stock_arr = isset($_POST['variant_stock']) ? $_POST['variant_stock'] : array();
									$variant_price_arr = isset($_POST['variant_price']) ? $_POST['variant_price'] : array();
									$variant_cost_price_arr = isset($_POST['variant_cost_price']) ? $_POST['variant_cost_price'] : array();
									//$variant_barcode_arr=isset($_POST['variant_barcode'])?$_POST['variant_barcode']:array();
									$conf_simple_arr = isset($_POST['conf_simple']) ? $_POST['conf_simple'] : array();
									$variant_tax_percent_arr = isset($_POST['variant_tax_percent']) ? $_POST['variant_tax_percent'] : array();
									$variant_webshop_price_arr = isset($_POST['variant_webshop_price']) ? $_POST['variant_webshop_price'] : array();
									//$variant_weight_arr=isset($_POST['variant_weight'])?$_POST['variant_weight']:array();
									// shipping charge
									$variant_shipping_charge_arr = isset($_POST['variant_shipping_charge']) ? $_POST['variant_shipping_charge'] : array();

									//Gifts to and Subissues
									$gifts_to_arr = isset($_POST['gifts']) ? $_POST['gifts'] : array();
									$sub_issues_arr = isset($_POST['sub_issue']) ? $_POST['sub_issue'] : array();

									//var_dump($variant_sku_arr);
									//var_dump($conf_simple);exit;

									if (isset($variant_sku_arr) && count($variant_sku_arr) > 0) {

										foreach ($variant_sku_arr as $key => $val) {
											$simple_sku = $val;
											//Gifts to and Subissues
											$gifts = $gifts_to_arr[$key];
											$sub_issue = $sub_issues_arr[$key];
											$simple_stock = $variant_stock_arr[$key];
											$simple_price = $variant_price_arr[$key];
											$simple_cost_price = $variant_cost_price_arr[$key];
											//$simple_barcode=$variant_barcode_arr[$key];
											$conf_simple = $conf_simple_arr[$key];


											$simple_tax_percent = $variant_tax_percent_arr[$key];
											$simple_webshop_price = $variant_webshop_price_arr[$key];
											$variant_shipping_charge = $variant_shipping_charge_arr[$key];
											//$simple_weight=$variant_weight_arr[$key];

											if ($Rounded_price_flag == 1) {
												$RowInfo = $this->SellerProductModel->calculate_webshop_price($simple_price, $simple_tax_percent);
												$simple_tax_amount = $RowInfo['tax_amount'];
												$simple_webshop_price = $RowInfo['webshop_price'];
											} else {
												$simple_webshop_price = $simple_price;
												$simple_tax_amount = 0;
												if ($simple_price > 0 && $simple_tax_percent > 0) {
													$simple_tax_amount = ($simple_tax_percent / 100) * $simple_price;
													$simple_webshop_price = $simple_tax_amount + $simple_price;
												}
											}


											if ($conf_simple) {

												$ConfSimpleExist = $this->SellerProductModel->getSingleDataByID('products', array('id' => $conf_simple, 'parent_id' => $product_id), 'id,barcode');

												if (isset($ConfSimpleExist) && $ConfSimpleExist->id != '') {

													$old_simple_barcode = $ConfSimpleExist->barcode;
													$simple_product_id = $ConfSimpleExist->id;
												} else {

													$old_simple_barcode = '';
													$simple_product_id = '';
												}
											} else {

												$old_simple_barcode = '';
												$simple_product_id = '';
											}


											if (isset($simple_product_id) && $simple_product_id > 0) {



												$updatesimpleproduct = array(
													'name' => $product_name,
													'parent_id' => $product_id,
													'sku' => $simple_sku,
													'cost_price' => $simple_cost_price,
													'price' => $simple_price,
													'tax_percent' => $simple_tax_percent,
													'tax_amount' => $simple_tax_amount,
													'webshop_price' => $simple_webshop_price,
													'launch_date' => $launch_date,
													'shipping_amount ' => $variant_shipping_charge,
													// 'shipping_amount ' => $shipping_amount,
													//'barcode'=>$simple_barcode,
													//'weight'=>$simple_weight,
													'status' => $status,
													'can_be_returned' => $product_returnable,
													'updated_at' => time(),
													'gift_id' => $gifts,
													'sub_issues' => $sub_issue,
													'ip' => $_SERVER['REMOTE_ADDR']
												);
												$conf_where = array('id' => $simple_product_id);

												$this->SellerProductModel->updateData('products', $conf_where, $updatesimpleproduct);


												$OldProductQtyData = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $simple_product_id), 'qty,available_qty');

												if ($OldProductQtyData->qty == $simple_stock) {
												} else {

													if ($OldProductQtyData->available_qty == $OldProductQtyData->qty) {
														$new_available_qty = $simple_stock;
													} else {
														if (isset($OldProductQtyData) && $OldProductQtyData->available_qty > 0) {
															$old_available_qty = $OldProductQtyData->available_qty;

															$old_ordered_qty = $OldProductQtyData->qty - $old_available_qty;
														} else {
															$old_ordered_qty = 0;
														}

														if ($simple_stock == 0) {
															$new_available_qty = 0;
														} else if ($simple_stock > 0 && $simple_stock > $OldProductQtyData->qty) {
															$new_available_qty = $simple_stock - $old_ordered_qty;
														} else {
															$new_available_qty = $simple_stock - $old_ordered_qty;
															$new_available_qty = ($new_available_qty <= 0) ? 0 : $new_available_qty;
														}
													}

													$stock_update = array('qty' => $simple_stock, 'available_qty' => $new_available_qty);
													$whr_qty_arr = array('product_id' => $simple_product_id);
													$this->SellerProductModel->updateData('products_inventory', $whr_qty_arr, $stock_update);
												}



												//$this->SellerProductModel->deleteDataById('products_variants',array('product_id'=>$simple_product_id));

												if (isset($added_variant_arr) && count($added_variant_arr) > 0) {
													// print_R($added_variant_arr);
													$VariantList = $this->EavAttributesModel->getVariantDataByIds($added_variant_arr);
													// print_R($VariantList);
													// echo "count" . count($VariantList);
													if (isset($VariantList) && count($VariantList) > 0) {
														foreach ($VariantList as $attr) {
															$attr_id = $attr['id'];
															$AttrData = $this->CommonModel->getSingleDataByID('eav_attributes', array('id' => $attr_id), 'id,attr_name,attr_code');
															$attr_code = $AttrData->attr_code;
															$attr_code = strtolower($attr_code);

															$OptionSelected = $this->SellerProductModel->getSingleDataByID('products_variants', array('product_id' => $simple_product_id, 'parent_id' => $product_id, 'attr_id' => $attr_id), 'id,attr_value');


															$attr_option_selected = (isset($OptionSelected) && $OptionSelected->attr_value != '') ? $OptionSelected->attr_value : '';


															if (isset($_POST['variant_' . $attr_code][$key])) {
																$variation_selected = $_POST['variant_' . $attr_code][$key];
															} else {
																$variation_selected = 0;
															}

															if ($variation_selected > 0) {

																if (isset($OptionSelected) && $OptionSelected->id != '') {
																	$pv_update = array('attr_id' => $attr_id, 'attr_value' => $variation_selected);
																	$whr_pv = array('id' => $OptionSelected->id);
																	$this->SellerProductModel->updateData('products_variants', $whr_pv, $pv_update);
																} else {
																	/*
																		$pv_insert=array('product_id'=>$simple_product_id,'parent_id'=>$product_id,'attr_id'=>$attr_id,'attr_value'=>$variation_selected);
																		$this->SellerProductModel->insertData('products_variants',$pv_insert);

																		*/
																}
															}
														}
													}
												}
											} else {


												$insertsimpleproduct = array(
													'name' => $product_name,
													'parent_id' => $product_id,
													'sku' => $simple_sku,
													'price' => $simple_price,
													'cost_price' => $simple_cost_price,
													'tax_percent' => $simple_tax_percent,
													'tax_amount' => $simple_tax_amount,
													'webshop_price' => $simple_webshop_price,
													'launch_date' => $launch_date,
													'shipping_amount ' => $variant_shipping_charge,
													// 'shipping_amount ' => $shipping_amount,
													//'barcode'=>$simple_barcode,
													//'weight'=>$simple_weight,
													'product_type' => 'conf-simple',
													'product_inv_type' => 'buy',
													'status' => 1,
													'can_be_returned' => $product_returnable,
													'created_at' => time(),
													'gift_id' => $gifts,
													'sub_issues' => $sub_issue,
													'ip' => $_SERVER['REMOTE_ADDR']
												);
												$simple_product_id = $this->SellerProductModel->insertData('products', $insertsimpleproduct);

												$stock_insert2 = array('product_id' => $simple_product_id, 'qty' => $simple_stock, 'available_qty' => $simple_stock, 'min_qty' => 0, 'is_in_stock' => 1);
												$this->SellerProductModel->insertData('products_inventory', $stock_insert2);




												//$this->SellerProductModel->deleteDataById('products_variants',array('product_id'=>$simple_product_id));

												if (isset($added_variant_arr) && count($added_variant_arr) > 0) {
													$VariantList = $this->EavAttributesModel->getVariantDataByIds($added_variant_arr);
													if (isset($VariantList) && count($VariantList) > 0) {
														foreach ($VariantList as $attr) {
															$attr_id = $attr['id'];
															$AttrData = $this->CommonModel->getSingleDataByID('eav_attributes', array('id' => $attr_id), 'id,attr_name,attr_code');
															$attr_code = $AttrData->attr_code;
															$attr_code = strtolower($attr_code);
															if (isset($_POST['variant_' . $attr_code][$key])) {
																$variation_selected = $_POST['variant_' . $attr_code][$key];
															} else {
																$variation_selected = 0;
															}

															if ($variation_selected > 0) {

																$pv_insert = array('product_id' => $simple_product_id, 'parent_id' => $product_id, 'attr_id' => $attr_id, 'attr_value' => $variation_selected);
																$this->SellerProductModel->insertData('products_variants', $pv_insert);
															}
														}
													}
												}
											}



											// if($simple_product_id){

											// 	/*-----------------------------Img upload---------------------------------------------------*/
											// 	if(!empty($_FILES["variant_image"]["name"][$key]))
											// 	{
											// 		$count=$key;
											// 		$_FILES["vfile"]["name"] = $attachment_name=$_FILES["variant_image"]["name"][$count];
											// 		$_FILES["vfile"]["type"] = $_FILES["variant_image"]["type"][$count];
											// 		$_FILES["vfile"]["tmp_name"] = $_FILES["variant_image"]["tmp_name"][$count];
											// 		$_FILES["vfile"]["error"] = $_FILES["variant_image"]["error"][$count];
											// 		$_FILES["vfile"]["size"] = $_FILES["variant_image"]["size"][$count];


											// 		if($_FILES["vfile"]["size"]>7*1048576)
											// 		{
											// 			$arrResponse  = array('status' =>400 ,'message'=>"Limit exceeds above 7 MB for ".$_FILES["vfile"]["name"]);
											// 			echo json_encode($arrResponse);exit;
											// 		}

											// 		$extension = $this->image_upload::ALLOWED_MIME_TYPES[$_FILES["vfile"]["type"]] ?? '';
											// 		if($extension === ''){
											// 			echo json_encode(['status' =>400 ,'message'=>"File type not allowed for ".$_FILES["vfile"]["name"]]);exit;
											// 		}

											// 		$image=md5($_FILES['vfile']['name']) . '_' . time() . '.' . $extension;
											// 		$orig_name=$_FILES['vfile']['name'];

											// 		$this->image_upload->upload_image(
											// 			$_FILES['vfile'],
											// 			'products',
											// 			$image,
											// 			['store_main' => false],
											// 			[
											// 				'thumb' => ['width' => 300, 'height' => 300],
											// 				'medium' => ['width' => 500, 'height' => 500],
											// 				'large' => ['width' => 800, 'height' => 800],
											// 				'original' => ['width' => 'auto', 'height' => 'auto'],
											// 			]
											// 		);


											// 			if($default_image==$orig_name)
											// 			{
											// 				$is_default=1;
											// 				$is_base_image=1;
											// 				$base_image=$image;
											// 			}else{
											// 				$is_default=0;
											// 				$is_base_image=0;
											// 			}

											// 			$media_insert=array('product_id'=>$product_id,'child_id'=>$simple_product_id,'image'=>$image,'image_title'=>$orig_name,'image_position'=>$count,'is_default'=>$is_default,'is_base_image'=>$is_base_image);
											// 			$this->SellerProductModel->insertData('products_media_gallery',$media_insert);
											// 		}
											// }else{
											// 		if($OldProductData->base_image!=$default_image){
											// 			$img_row=$this->SellerProductModel->getSingleDataByID('products_media_gallery',array('product_id'=>$product_id,'image_title'=>$default_image),'');
											// 			if(isset($img_row) && $img_row->id!=''){
											// 				$base_image=$img_row->image;
											// 			}else{
											// 				$base_image=$OldProductData->base_image;
											// 			}
											// 		}else{
											// 			$base_image=$OldProductData->base_image;
											// 		}
											// }
										}
									}
									/**-----------------------End of variation loop---------------------------------------------**/
								}

								/*---------------------------Main loop end-------------------------------------------------------*/
							}
						}
					}



					$deleted_md_ids = isset($_POST['deleted_md']) ? $_POST['deleted_md'] : '';
					if (isset($deleted_md_ids) && $deleted_md_ids != '') {


						if (strpos($deleted_md_ids, ',') !== false) {
							$deleted_md_ids_arr = explode(',', $deleted_md_ids);
						} else {
							$deleted_md_ids_arr[] = $deleted_md_ids;
						}

						$result = array_values(array_filter($deleted_md_ids_arr));

						if (isset($result) && count($result) > 0) {
							foreach ($result as $val) {
								$at_whr = array('id' => $val);
								$this->SellerProductModel->deleteDataById('products_media_gallery', $at_whr);
							}
						}
					}
					/*-----------------------------------------------*/

					/*-----------------------upload media imgs---------------------------------------*/


					if (isset($_FILES["gallery_image"]["name"]) && !empty($_FILES["gallery_image"]["name"]) && count($_FILES["gallery_image"]["name"]) > 0) {

						// **Check count of images (max 5)**
						if (count($_FILES["gallery_image"]["name"]) > 5) {
							$arrResponse = array('status' => 400, 'message' => 'You can upload a maximum of 5 images only.');
							echo json_encode($arrResponse);
							exit;
						}

						$config2["upload_path"] = SIS_SERVER_PATH . '/' . 'uploads/products/original/';
						$config2["allowed_types"] = 'jpg|jpeg|JPG|JPEG|png|PNG';
						$config2['max_size'] = 1024 * 5; // (not used since we manually check)
						$config2['max_width'] = '0';
						$config2['max_height'] = '0';
						$config2['overwrite'] = FALSE;
						$config2['encrypt_name'] = TRUE;

						for ($count = 0; $count < count($_FILES["gallery_image"]["name"]); $count++) {
							$_FILES["gfile"]["name"] = $attachment_name = $_FILES["gallery_image"]["name"][$count];
							$_FILES["gfile"]["type"] = $_FILES["gallery_image"]["type"][$count];
							$_FILES["gfile"]["tmp_name"] = $_FILES["gallery_image"]["tmp_name"][$count];
							$_FILES["gfile"]["error"] = $_FILES["gallery_image"]["error"][$count];
							$_FILES["gfile"]["size"] = $_FILES["gallery_image"]["size"][$count];

							// **Check file extension manually**
							$fileExt = strtolower(pathinfo($_FILES["gfile"]["name"], PATHINFO_EXTENSION));
							$allowedExt = ['jpg', 'jpeg', 'png'];
							if (!in_array($fileExt, $allowedExt)) {
								$arrResponse = array('status' => 403, 'message' => 'Please upload only jpg, jpeg, or png files.');
								echo json_encode($arrResponse);
								exit;
							}

							// **Check file size (max 500 KB)**
							if ($_FILES["gfile"]["size"] > 500 * 1024) { // 500 KB = 500 * 1024 bytes
								$arrResponse = array('status' => 400, 'message' => "File '" . $_FILES["gfile"]["name"] . "' exceeds 500KB limit.");
								echo json_encode($arrResponse);
								exit;
							}

							// Upload
							$this->load->library('upload', $config2);
							if ($this->upload->do_upload('gfile')) {
								$data = $this->upload->data();
								unset($this->upload);
								$image = $data["file_name"];
								$orig_name = $data["orig_name"];

								if ($default_image == $orig_name) {
									$is_default = 1;
									$is_base_image = 1;
									$base_image = $image;
								} else {
									$is_default = 0;
									$is_base_image = 0;
								}

								$thumb_folder = SIS_SERVER_PATH . '/' . 'uploads/products/thumb/';
								$medium_folder = SIS_SERVER_PATH . '/' . 'uploads/products/medium/';
								$large_folder = SIS_SERVER_PATH . '/' . 'uploads/products/large/';
								$original_folder = SIS_SERVER_PATH . '/' . 'uploads/products/original/';

								$thumb = $this->makeThumbnail($image, $original_folder, $thumb_folder, $width = '300', $height = '300');
								$this->makeThumbnail($image, $original_folder, $medium_folder, $width = '500', $height = '500');
								$this->makeThumbnail($image, $original_folder, $large_folder, $width = '800', $height = '800');

								$media_insert = array(
									'product_id' => $product_id,
									'image' => $image,
									'image_title' => $orig_name,
									'image_position' => $count,
									'is_default' => $is_default,
									'is_base_image' => $is_base_image
								);
								$this->SellerProductModel->insertData('products_media_gallery', $media_insert);
							} else {
								$arrResponse = array('status' => 400, 'message' => $this->upload->display_errors());
								echo json_encode($arrResponse);
								exit;
							}
						}
					} else {
						$base_image = $default_image;
					}
					/***---------------------------------------------Img upload end-------------------------------***/


					if ($base_image != '') {
						$product_img = array('base_image' => $base_image);
						$where_arr = array('id' => $product_id);
						$this->SellerProductModel->updateData('products', $where_arr, $product_img);

						//if((array_sum($_FILES['gallery_image']['error']) > 0) || (array_sum($_FILES['variant_image']['error']) > 0)){

						$img_row = $this->SellerProductModel->getSingleDataByID('products_media_gallery', array('product_id' => $product_id, 'image' => $base_image), '');

						if (isset($img_row) && $img_row->id != '') {

							$img_id = $img_row->id;

							$product_img_deff = array('is_default' => 0, 'is_base_image' => 0);
							$where_arr_deff = array('product_id' => $product_id);
							$this->SellerProductModel->updateData('products_media_gallery', $where_arr_deff, $product_img_deff);

							$product_img_def = array('is_default' => 1, 'is_base_image' => 1);
							$where_arr_def = array('id' => $img_id);
							$this->SellerProductModel->updateData('products_media_gallery', $where_arr_def, $product_img_def);
						}

						//}

					}

					if (isset($_FILES['digit_pdf']['name']) && !empty($_FILES['digit_pdf']['name'])) {
						// Assume product_id is passed in the request
						// Set upload path
						$upload_path = SIS_SERVER_PATH2 . '/uploads/digit_pdf/' . $product_id . '/';
						if (!is_dir($upload_path)) {
							mkdir($upload_path, 0777, true); // Create the directory if it doesn't exist
						}

						// Check if a previous PDF exists and delete it
						$existing_pdf = glob($upload_path . 'digit_pdf-*.pdf'); // Matches any existing digit_pdf file
						foreach ($existing_pdf as $file) {
							unlink($file); // Delete the file
						}

						// Save uploaded PDF
						$file_extension = pathinfo($_FILES['digit_pdf']['name'], PATHINFO_EXTENSION);
						if ($file_extension != 'pdf') {
							echo json_encode(['status' => 400, 'message' => 'Invalid file type. Please upload a PDF.']);
							exit;
						}

						$digit_pdf = "digit_pdf-" . uniqid() . '.' . $file_extension;
						$pdf_path = $upload_path . $digit_pdf;

						if (!move_uploaded_file($_FILES['digit_pdf']['tmp_name'], $pdf_path)) {
							echo json_encode(['status' => 400, 'message' => 'Failed to upload PDF']);
							exit;
						}

						// Convert PDF to images using pdftoppm
						$output_images = [];
						$output_image_path = $upload_path . 'digit_pdf_page_';

						// Run pdftoppm to convert PDF pages to PNG images
						$command = "/usr/bin/pdftoppm -png \"$pdf_path\" \"$output_image_path\" 2>&1";

						exec($command, $output, $return_var);

						// Debugging: Log the exec command output and return status
						error_log('Exec command output: ' . implode("\n", $output));
						error_log('Exec command return value: ' . $return_var);

						if ($return_var === 0) {
							// Success
							$images = glob($upload_path . 'digit_pdf_page_*.png');
							foreach ($images as $image) {
								$output_images[] = basename($image);
							}
							echo json_encode(['status' => 200, 'images' => $output_images]);
						} else {
							// Capture the error output
							echo json_encode([
								'status' => 400,
								'message' => 'Failed to convert PDF to images',
								'error_output' => implode("\n", $output), // Display the command output/error for debugging
								'error_code' => $return_var
							]);
						}
					$product_img = ['digit_pdf' => $digit_pdf];
					$where_arr = ['id' => $product_id];
					$this->SellerProductModel->updateData('products', $where_arr, $product_img);
					
						exit;
						
					} else {
						$digit_pdf = $digit_pdf; // If no file is uploaded, retain the existing file
					}

					// print_r($digit_pdf);die;

					// Update the database with the new file name
					$product_img = ['digit_pdf' => $digit_pdf];
					$where_arr = ['id' => $product_id];
					$this->SellerProductModel->updateData('products', $where_arr, $product_img);
					
					echo json_encode($arrResponse);
					exit;
				}
			}
		}
	}


	function bulkadd()
	{

		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$data['customVariable_out_of_stock'] = $customVariable_out_of_stock = $this->CommonModel->getSingleShopDataByID('custom_variables', array('identifier' => 'out_of_stock'), 'value');
		$this->load->view('seller/products/bulk-add', $data);
	}


	function openbulkselectcategory()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$data['type'] = $type = isset($_POST['type']) ? $_POST['type'] : '';
		$View = $this->load->view('seller/products/bulk-category', $data, true);
		$this->output->set_output($View);
	}

	function openDownloadAll()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$data['type'] = $type = isset($_POST['type']) ? $_POST['type'] : '';
		$View = $this->load->view('seller/products/bulk-download-all', $data, true);
		$this->output->set_output($View);
	}

	function OpenBulkSelectCategoryUpdate()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$data['type'] = $type = isset($_POST['type']) ? $_POST['type'] : '';
		$View = $this->load->view('seller/products/bulk-category-update', $data, true);
		$this->output->set_output($View);
	}

	function OpenOnlineInventoryPopup()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$data['type'] = $type = isset($_POST['type']) ? $_POST['type'] : '';
		$View = $this->load->view('seller/products/bulk-inventory-update', $data, true);
		$this->output->set_output($View);
	}

	function OpenBulkSelectCategoryUpdate_1()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$data['type'] = $type = isset($_POST['type']) ? $_POST['type'] : '';
		$View = $this->load->view('seller/products/bulk-category-update-1', $data, true);
		$this->output->set_output($View);
	}

	function BulkInventoryTypesDownloadPopup()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$View = $this->load->view('seller/products/bulk-inventory_type-download', $data, true);
		$this->output->set_output($View);
	}

	function BulkAttributesDownloadPopup()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$data['type'] = $type = isset($_POST['type']) ? $_POST['type'] : '';
		$View = $this->load->view('seller/products/bulk-attributes-download', $data, true);
		$this->output->set_output($View);
	}

	function BulkweightLoccationDownloadPopup()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$data['type'] = $type = isset($_POST['type']) ? $_POST['type'] : '';
		$View = $this->load->view('seller/products/bulk-weight-location-download', $data, true);
		$this->output->set_output($View);
	}

	public function OpenOutofStockModal()
	{
		$data['PageTitle'] = 'Warehouse - Manual Out Of Stock Check';
		$data['side_menu'] = 'bulk-add';
		$data['shop_id'] = $shop_id =	$this->session->userdata('ShopID');
		$data['type'] = $type = isset($_POST['type']) ? $_POST['type'] : '';
		$View = $this->load->view('seller/products/out_of_stock', $data, true);
		$this->output->set_output($View);
	}


	function bulkcategoryattrselect()
	{


		if ($_POST['root_category_id'] != '' && $_POST['sub_category'] != '') {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$data['category_id'] = $category_id = $_POST['root_category_id'];
			$data['CategoryDetail'] = $CategoryDetail = $this->CategoryModel->get_category_detail($category_id);

			$data['sub_category'] = $sub_category = $_POST['sub_category'];
			$data['SubCategoryDetail'] = $SubCategoryDetail = $this->CategoryModel->get_category_detail($sub_category);


			$SellerAttr = $this->CommonModel->getSingleDataByID('category_attribute_mapping_shop', array('category_id' => $sub_category), '');

			if (!empty($SellerAttr) && $SellerAttr->id != '') {


				if (isset($SellerAttr) && $SellerAttr->selected_attributes != '') {

					$selected_attributes = $SellerAttr->selected_attributes;
					$selected_attributes = substr($selected_attributes, 1, -1);
					if (strpos($selected_attributes, ',') !== false) {
						$selected_attributes_arr = explode(',', $selected_attributes);
					} else {
						$selected_attributes_arr[] = $selected_attributes;
					}
					if (count($selected_attributes_arr) > 0) {

						$AttributesList = $this->EavAttributesModel->get_attributes_for_seller($selected_attributes_arr);
						$data['DefaultAttrList'] = $AttributesList;
					} else {
						$data['DefaultAttrList'] = array();
					}
				} else {
					$data['DefaultAttrList'] = array();
				}


				if (isset($SellerAttr) && $SellerAttr->selected_variants != '') {

					$selected_variants = $SellerAttr->selected_variants;

					$selected_variants = substr($selected_variants, 1, -1);
					if (strpos($selected_variants, ',') !== false) {
						$selected_variants_arr = explode(',', $selected_variants);
					} else {
						$selected_variants_arr[] = $selected_variants;
					}
					if (count($selected_variants_arr) > 0) {

						$VariantList = $this->EavAttributesModel->get_variant_by_category($selected_variants_arr);

						$data['VariantList'] = $VariantList;
					} else {
						$data['VariantList'] = array();
					}
				} else {

					$data['VariantList'] = array();
				}
				$data['seller_attr'] = 1;
			} else {


				//$data['DefaultAttrList']=$this->EavAttributesModel->get_default_attributes();
				//$data['DefaultVariantList']=$this->EavAttributesModel->get_default_variants();

				if (isset($CategoryDetail) && $CategoryDetail->selected_attributes != '') {

					$selected_attributes = $CategoryDetail->selected_attributes;
					$selected_attributes = substr($selected_attributes, 1, -1);
					if (strpos($selected_attributes, ',') !== false) {
						$selected_attributes_arr = explode(',', $selected_attributes);
					} else {
						$selected_attributes_arr[] = $selected_attributes;
					}
					if (count($selected_attributes_arr) > 0) {

						$AttributesList = $this->EavAttributesModel->get_attributes_for_seller($selected_attributes_arr);
						$data['DefaultAttrList'] = $AttributesList;
					} else {
						$data['DefaultAttrList'] = array();
					}
				} else {
					$data['DefaultAttrList'] = array();
				}




				if (isset($CategoryDetail) && $CategoryDetail->selected_variants != '') {

					$selected_variants = $CategoryDetail->selected_variants;

					$selected_variants = substr($selected_variants, 1, -1);
					if (strpos($selected_variants, ',') !== false) {
						$selected_variants_arr = explode(',', $selected_variants);
					} else {
						$selected_variants_arr[] = $selected_variants;
					}
					if (count($selected_variants_arr) > 0) {

						$VariantList = $this->EavAttributesModel->get_variant_by_category($selected_variants_arr);

						$data['VariantList'] = $VariantList;
						$data['VariantList'] = $VariantList;
					} else {
						$data['VariantList'] = array();
					}
				} else {

					$data['VariantList'] = array();
				}

				$data['seller_attr'] = 2;
			}

			$View = $this->load->view('seller/products/sub_category_bulk_select', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}


	function downloadproductcsv()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		if (empty($_GET['root_category_id'])  || empty($_GET['sub_category'])) {
			$arrResponse  = array('status' => 400, 'message' => 'Please add mandatory feilds');
			echo json_encode($arrResponse);
			exit;
		} else {

			$sub_category = $_GET['sub_category'];
			$root_category_id = $_GET['root_category_id'];


			if (isset($_GET['attributes']) && $_GET['attributes'] != '') {
				$selected_attributes = $_GET['attributes'];

				if (strpos($selected_attributes, ',') !== false) {
					$selected_attributes_arr = explode(',', $selected_attributes);
				} else {
					$selected_attributes_arr[] = $selected_attributes;
				}

				$selected_attributes_arr = array_filter(array_unique($selected_attributes_arr));
				$csv_attr = $this->EavAttributesModel->get_attributes_for_seller($selected_attributes_arr);
			} else {
				$selected_attributes_arr = array();
				$csv_attr = array();
			}

			if (isset($_GET['variants']) && $_GET['variants'] != '') {
				$selected_variants = $_GET['variants'];

				if (strpos($selected_variants, ',') !== false) {
					$selected_variants_arr = explode(',', $selected_variants);
				} else {
					$selected_variants_arr[] = $selected_variants;
				}

				$selected_variants_arr = array_filter(array_unique($selected_variants_arr));
				$csv_variant = $this->EavAttributesModel->get_variant_by_category($selected_variants_arr);
			} else {
				$selected_variants_arr = array();
				$csv_variant = array();
			}

			$Products = $this->SellerProductModel->getProductForCSVImport($root_category_id, $sub_category);

			$sis_export_header = array("product_type", "product_name", "product_code", "category", "sub_category", "tags", "gender", "description", "highlights", "product_reviews_code", "launch_date", "estimate_delivery_time", "product_drop_shipment", "sku", "barcode", "inventory", "cost_price", "selling_price", "tax_percent", "images", "default_img_name", "status", "customer_types", "meta_title", "meta_keyword", "meta_description", "search_keywords", "promo_reference", "weight");

			if (isset($csv_attr) && count($csv_attr) > 0) {
				foreach ($csv_attr as $attr) {
					if ($attr['is_default']) {
						continue;
					}
					$attr_code = $attr['attr_code'];
					$attr_code = 'attribute_' . $attr_code;
					array_push($sis_export_header, $attr_code);
				}
			}

			if (isset($csv_variant) && count($csv_variant) > 0) {
				$master_variant = '';
				foreach ($csv_variant as $attr) {

					$attr_code = $attr['attr_code'];
					$master_variant .= $attr_code . ',';
				}

				array_push($sis_export_header, 'master_variant');


				foreach ($csv_variant as $attr) {

					$attr_code = $attr['attr_code'];
					$attr_code = 'variant_' . $attr_code;
					array_push($sis_export_header, $attr_code);
				}
			}


			// file name


			if (isset($Products) && count($Products) > 0) {

				$ExportValuesArr = array();
				foreach ($Products as $product) {

					$VariantProducts = array();

					$product_id = $product['id'];
					$product_type = $product['product_type'];
					$product_name = $product['name'];
					$parent_product = '';

					$product_code = $product['product_code'];
					$category = '-';
					$sub_category = $product['sub_category'];
					$tags = '-';

					$gender = $product['gender'];

					$description = $product['description'];
					$highlights = $product['highlights'];

					$product_reviews_code = $product['product_reviews_code'];

					$launch_date = (isset($product['launch_date']) && $product['launch_date'] != '') ? date(SIS_DATE_FM, $product['launch_date']) : '';
					$estimate_delivery_time = $product['estimate_delivery_time'];
					$product_return_time = $product['product_return_time'];
					$product_drop_shipment = $product['product_drop_shipment'];
					$sku = ($product_type == 'simple' || $product_type == 'conf-simple') ? $product['sku'] : '';
					$barcode = ($product_type == 'simple' || $product_type == 'conf-simple') ? $product['barcode'] : '';

					$cost_price = ($product_type == 'simple' || $product_type == 'conf-simple') ? round($product['cost_price'], 2) : '';
					$price = ($product_type == 'simple' || $product_type == 'conf-simple') ? round($product['price'], 2) : '';
					$tax_percent = ($product_type == 'simple' || $product_type == 'conf-simple') ? round($product['tax_percent'], 2) : '';
					$tax_amount = ($product_type == 'simple' || $product_type == 'conf-simple') ? $product['tax_amount'] : '';
					$webshop_price = ($product_type == 'simple' || $product_type == 'conf-simple') ? $product['webshop_price'] : '';




					$default_img_name = $product['base_image'];

					$status = $product['status'];

					$meta_title = $product['meta_title'];
					$meta_keyword = $product['meta_keyword'];
					$meta_description = $product['meta_description'];
					$search_keywords = $product['search_keywords'];
					$promo_reference = $product['promo_reference'];
					$weight = $product['weight'];

					$customer_types = $product['customer_type_ids'];

					$product_base_image = $this->SellerProductModel->getProductBaseImage($product_id);
					if (isset($product_base_image) && $product_base_image->id != '') {
						$default_img_name = $product_base_image->image_title;
					} else {
						$default_img_name = '';
					}


					if ($product_type == 'simple' || $product_type == 'configurable') {
						$ProductMedia = $this->SellerProductModel->getMultiDataById('products_media_gallery', array('product_id' => $product_id, 'child_id' => NULL), '');
					} else {
						$ProductMedia = array();
					}

					$img_arr = array();
					$img_str = '';
					if (isset($ProductMedia) && count($ProductMedia) > 0) {
						foreach ($ProductMedia as $media) {
							$img_arr[] = $media->image_title;
						}
						$img_str = implode(',', $img_arr);
					}


					$images = $img_str;

					$MainCat = $this->SellerProductModel->getSingleDataByID('products_category', array('product_id' => $product_id, 'level' => 0, 'category_ids' => $root_category_id), 'category_ids');
					$category_id = $MainCat->category_ids;

					$MainCatName = $this->CommonModel->getSingleDataByID('category', array('id' => $category_id), 'id,cat_name');
					$root_category = $MainCatName->cat_name;

					$ChildCat = $this->SellerProductModel->getSingleDataByID('products_category', array('product_id' => $product_id, 'level' => 2), 'category_ids');
					if (isset($ChildCat) && $ChildCat->category_ids != '') {
						if (strpos($ChildCat->category_ids, ',') !== false) {
							$chid_cat_arr = explode(',', $ChildCat->category_ids);
						} else {
							$chid_cat_arr[] = $ChildCat->category_ids;
						}

						$CatRow = $this->CategoryModel->get_category_names_by_ids($chid_cat_arr);


						$category_tags = $CatRow->cat_name;
					} else {
						$category_tags = '';
					}





					$VariantProductsIds = array();
					$variant_master_str = '';
					if ($product_type == 'configurable') {
						$VariantMaster = $this->SellerProductModel->getVariantMasterForProducts($product_id);
						foreach ($VariantMaster as $vm) {
							$variant_master_str .= $vm['attr_code'] . ',';
						}

						$VariantProductsRow = $this->SellerProductModel->getVariantProducts($product_id);
						if (isset($VariantProductsRow->product_ids) && $VariantProductsRow->product_ids != '') {

							if (strpos($VariantProductsRow->product_ids, ',') !== false) {
								$VariantProductsIds = explode(',', $VariantProductsRow->product_ids);
							} else {
								$VariantProductsIds[] = $VariantProductsRow->product_ids;
							}
							$VariantProducts = $this->SellerProductModel->getVariantProductsByIds($VariantProductsIds);
						} else {
							$VariantProducts = array();
						}
						$inventory = '';
					} else {
						$variant_master_str = '';
						$VariantProducts = array();
						$ProductStock = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'qty');
						$inventory = ($product_type == 'simple') ? $ProductStock->qty : '';
					}

					$pro_attr_str = '';

					$SingleRow = array("$product_type", "$product_name", "$product_code", "$root_category", "$sub_category", "$category_tags", "$gender", "$description", "$highlights", "$product_reviews_code", "$launch_date", "$estimate_delivery_time", "$product_drop_shipment", "$sku", "$barcode", "$inventory", "$cost_price", "$price", "$tax_percent", "$images", "$default_img_name", "$status", "$customer_types", "$meta_title", "$meta_keyword", "$meta_description", "$search_keywords", "$promo_reference", "$weight");


					if (isset($csv_attr) && count($csv_attr) > 0) {
						foreach ($csv_attr as $attr) {
							if ($attr['is_default']) {
								continue;
							}
							$attr_id = $attr['id'];
							$ProductAttr = $this->SellerProductModel->getSingleDataByID('products_attributes', array('product_id' => $product_id, 'attr_id' => $attr_id), '');


							$attrDetails = $this->EavAttributesModel->get_attribute_detail($attr_id);
							$attr_properties = $attrDetails->attr_properties;
							if (isset($ProductAttr) && $ProductAttr->id != '') {
								$pro_attr_str = $attr_value = $ProductAttr->attr_value;

								/*----------------------------------------------------------*/

								if (!empty($attr_value) && $attr_properties == 5) {

									$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_option_id($shop_id, $attr_id, $attr_value);
									if (isset($IsOptionExist) && $IsOptionExist->id != '') {
										$pro_attr_str = $IsOptionExist->attr_options_name;
									}
								} else if (!empty($attr_value) && $attr_properties == 6) {

									if (strpos($attr_value, ',') !== false) {
										$attr_value_arr = explode(',', $attr_value);
									} else {
										$attr_value_arr[] = $attr_value;
									}

									array_filter($attr_value_arr);

									$attr_value_ids = array();

									if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
										foreach ($attr_value_arr as $attr_value_option) {
											$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_option_id($shop_id, $attr_id, $attr_value_option);
											if (isset($IsOptionExist) && $IsOptionExist->id != '') {

												$attr_value_ids[] = $IsOptionExist->attr_options_name;
											} else {
											}
										}

										$pro_attr_str = implode(',', $attr_value_ids);
									}
								}

								/*------------------------------------------------------------*/
							} else {
								$pro_attr_str = '';
							}


							array_push($SingleRow, $pro_attr_str);
						}
					}

					if ($product_type == 'configurable') {

						if ($variant_master_str) {
							array_push($SingleRow, $variant_master_str);
						}
					}


					$ExportValuesArr[] = $SingleRow;


					if (isset($VariantProducts) && count($VariantProducts) > 0) {
						$ExportChildArr = array();
						$parent_product = $product_name;
						foreach ($VariantProducts as $variant) {
							$product_type = $variant['product_type'];
							$sku = ($product_type == 'conf-simple') ? $variant['sku'] : '';
							$barcode = ($product_type == 'conf-simple') ? $variant['barcode'] : '';
							$inventory = ($product_type == 'conf-simple') ? $variant['qty'] : '';
							$cost_price = ($product_type == 'conf-simple') ? round($variant['cost_price'], 2) : '';
							$price = ($product_type == 'conf-simple') ? round($variant['price'], 2) : '';
							$tax_percent = ($product_type == 'conf-simple') ? round($variant['tax_percent'], 2) : '';
							$tax_amount = ($product_type == 'conf-simple') ? $variant['tax_amount'] : '';
							$webshop_price = ($product_type == 'conf-simple') ? $variant['webshop_price'] : '';

							$weight = ($product_type == 'conf-simple') ? $variant['weight'] : '';


							if ($product_type == 'conf-simple') {
								$ProductMedia = $this->SellerProductModel->getMultiDataById('products_media_gallery', array('product_id' => $product_id, 'child_id' => $variant['id']), '');
							} else {
								$ProductMedia = array();
							}

							$child_img_arr = array();
							$child_img_str = '';
							if (isset($ProductMedia) && count($ProductMedia) > 0) {
								foreach ($ProductMedia as $media) {
									$child_img_arr[] = $media->image_title;
								}
								$child_img_str = implode(',', $child_img_arr);
							}


							$child_images = $child_img_str;


							$SingleRow = array("$product_type", "$product_name", "$product_code", "", "", "", "", "", "", "", "", "", "", "$sku", "$barcode", "$inventory", "$cost_price", "$price", "$tax_percent", "$child_images", "", "$status", "", "", "", "", "", "", "$weight");


							if (isset($csv_attr) && count($csv_attr) > 0) {
								foreach ($csv_attr as $attr) {
									if ($attr['is_default']) {
										continue;
									}
									$pro_attr_str = '';
									array_push($SingleRow, $pro_attr_str);
								}
							}

							$variant_master_str2 = '';
							array_push($SingleRow, $variant_master_str2);

							//print_r_custom($csv_variant);

							if (isset($csv_variant) && count($csv_variant) > 0) {

								foreach ($csv_variant as $attr) {
									$attr_id = $attr['id'];

									$OptionSelected = $this->SellerProductModel->getSingleDataByID('products_variants', array('product_id' => $variant['id'], 'parent_id' => $product_id, 'attr_id' => $attr_id), 'attr_value');

									$attr_option_selected = (isset($OptionSelected) && $OptionSelected->attr_value != '') ? $OptionSelected->attr_value : '';


									if ($attr_option_selected) {
										$OptionData = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('id' => $attr_option_selected, 'attr_id' => $attr_id), 'id,attr_id,attr_options_name');
										if (isset($OptionData) && $OptionData->id != '') {
											$attr_label = $OptionData->attr_options_name;
										} else {
											$attr_label = '';
										}
									} else {
										$attr_label = '';
									}


									array_push($SingleRow, $attr_label);
								}
							}


							$ExportValuesArr[] = $SingleRow;
						}
					}
				}

				//print_r_custom($ExportValuesArr);exit;

			}



			$filename = 'SISProducts-' . time() . '.csv';
			header("Content-Description: File Transfer");

			header("Content-Disposition: attachment; filename=$filename");
			header("Content-Type: application/csv; charset=UTF-8 ");

			// file creation
			$file = fopen('php://output', 'w');
			fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM !!!!!
			fputcsv($file, $sis_export_header);

			if (isset($ExportValuesArr) && count($ExportValuesArr) > 0) {

				foreach ($ExportValuesArr as $readData) {
					fputcsv($file, $readData);
				}
			}
			fclose($file);
			exit;

			echo "success";
			exit;
		}
	}

	function DownloadAllProductCSV()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$Products = $this->SellerProductModel->getAllProductForCSVImport();
		$sis_export_header = array("product_type", "inventory_type", "product_name", "product_code", "category", "gender", "description", "highlights", "product_reviews_code", "launch_date", "estimate_delivery_time", "sku", "barcode", "inventory", "cost_price", "selling_price", "tax_percent", "webshop_price", "images", "default_img_name", "status", "meta_title", "meta_keyword", "meta_description", "search_keywords", "promo_reference", "weight", "location");

		// file name
		if (isset($Products) && count($Products) > 0) {

			$ExportValuesArr = array();
			$sis_export_header_attr = array();
			$sis_export_header_attr_ids = array();

			$attr_value_ids = $this->SellerProductModel->getCatelogAllAttrs();
			$attr_value_ids_arr = json_decode(json_encode($attr_value_ids), true);
			$attr_value_ids_arr_new = explode(',', $attr_value_ids_arr['attr_ids']);
			$csv_attr = $this->EavAttributesModel->get_attributes_for_seller($attr_value_ids_arr_new);


			if (isset($csv_attr) && count($csv_attr) > 0) {
				foreach ($csv_attr as $attr) {
					if ($attr['is_default']) {
						continue;
					}
					$attr_code = $attr['attr_code'];
					$attr_id = $attr['id'];
					$attr_code = 'attribute_' . $attr_code;
					if (!in_array($attr_code, $sis_export_header)) {
						array_push($sis_export_header, $attr_code);
						array_push($sis_export_header_attr, $attr_code);
						array_push($sis_export_header_attr_ids, $attr_id);
					}
				}
			}
			// catlog variants
			$sis_export_header_variants = array();
			$sis_export_header_variants_ids = array();

			$Variant_Ids = $this->SellerProductModel->getCatelogAllVariants();
			$Variant_Ids_arr = json_decode(json_encode($Variant_Ids), true);
			$Variant_Ids_arr_new = explode(',', $Variant_Ids_arr['attr_ids']);
			$csv_variant = $this->EavAttributesModel->get_variant_by_category($Variant_Ids_arr_new);

			if (isset($csv_variant) && count($csv_variant) > 0) {
				$master_variant = '';
				foreach ($csv_variant as $attr) {

					$attr_code = $attr['attr_code'];
					$master_variant .= $attr_code . ',';
				}
				if (!in_array('master_variant', $sis_export_header)) {
					array_push($sis_export_header, 'master_variant');
					//array_push($sis_export_header_variants,$attr_code);
				}

				foreach ($csv_variant as $attr) {

					$attr_code = $attr['attr_code'];
					$attr_id = $attr['id'];
					$attr_code = 'variant_' . $attr_code;
					if (!in_array($attr_code, $sis_export_header)) {
						array_push($sis_export_header, $attr_code);
						array_push($sis_export_header_variants, $attr_code);
						array_push($sis_export_header_variants_ids, $attr_id);
					}
				}
			}

			foreach ($Products as $product) {

				$VariantProducts = array();

				$product_id = $product->id;

				$product_type = $product->product_type;
				$inventory_type = 'buy';
				// if($product->product_inv_type == 'buy' && $product->shop_id == 0 )
				// {
				// 	$inventory_type = 'Own';
				// }elseif ($product->product_inv_type == 'buy' && $product->shop_id > 0) {
				// 	$inventory_type = 'buy';
				// }elseif ($product->product_inv_type == 'virtual') {
				// 	$inventory_type = 'virtual';
				// }else{
				// 	$inventory_type = 'dropship';
				// }

				$product_name = $product->name;
				$parent_product = '';

				$product_code = $product->product_code;
				//$category='-';
				$category = $this->SellerProductModel->getProductsallCategoryNames($product_id);

				$gender = $product->gender;

				$description = $product->description;
				$highlights = $product->highlights;

				$product_reviews_code = $product->product_reviews_code;

				$launch_date = (isset($product->launch_date) && $product->launch_date != '') ? date(SIS_DATE_FM, $product->launch_date) : '';
				$estimate_delivery_time = $product->estimate_delivery_time;
				$product_return_time = $product->product_return_time;
				// $product_drop_shipment=$product->product_drop_shipment;
				$sku = ($product_type == 'simple' || $product_type == 'conf-simple') ? $product->sku : '';
				$barcode = ($product_type == 'simple' || $product_type == 'conf-simple') ? $product->barcode : '';

				$cost_price = ($product_type == 'simple' || $product_type == 'conf-simple') ? round($product->cost_price, 2) : '';
				$price = ($product_type == 'simple' || $product_type == 'conf-simple') ? round($product->price, 2) : '';
				$tax_percent = ($product_type == 'simple' || $product_type == 'conf-simple') ? round($product->tax_percent, 2) : '';

				$tax_amount = ($product_type == 'simple' || $product_type == 'conf-simple') ? $product->tax_amount : '';
				$webshop_price = ($product_type == 'simple' || $product_type == 'conf-simple') ? $product->webshop_price : '';

				$default_img_name = $product->base_image;

				$status = $product->status;

				$meta_title = $product->meta_title;
				$meta_keyword = $product->meta_keyword;
				$meta_description = $product->meta_description;
				$search_keywords = $product->search_keywords;
				$promo_reference = $product->promo_reference;
				$weight = $product->weight;
				$location = $product->prod_location;

				// $customer_types=$product->customer_type_ids;

				$product_base_image = $this->SellerProductModel->getProductBaseImage($product_id);
				if (isset($product_base_image) && $product_base_image->id != '') {
					$default_img_name = $product_base_image->image_title;
				} else {
					$default_img_name = '';
				}

				if ($product_type == 'simple' || $product_type == 'configurable') {
					$ProductMedia = $this->SellerProductModel->getMultiDataById('products_media_gallery', array('product_id' => $product_id, 'child_id' => NULL), '');
				} else {
					$ProductMedia = array();
				}

				$img_arr = array();
				$img_str = '';
				if (isset($ProductMedia) && count($ProductMedia) > 0) {
					foreach ($ProductMedia as $media) {
						$img_arr[] = $media->image_title;
					}
					$img_str = implode(',', $img_arr);
				}
				$images = $img_str;

				$VariantProductsIds = array();
				$variant_master_str = '';
				if ($product_type == 'configurable') {
					$VariantMaster = $this->SellerProductModel->getVariantMasterForProducts($product_id);
					foreach ($VariantMaster as $vm) {
						$variant_master_str .= $vm['attr_code'] . ',';
					}

					$VariantProductsRow = $this->SellerProductModel->getVariantProducts($product_id);
					if (isset($VariantProductsRow->product_ids) && $VariantProductsRow->product_ids != '') {

						if (strpos($VariantProductsRow->product_ids, ',') !== false) {
							$VariantProductsIds = explode(',', $VariantProductsRow->product_ids);
						} else {
							$VariantProductsIds[] = $VariantProductsRow->product_ids;
						}
						$VariantProducts = $this->SellerProductModel->getVariantProductsByIds($VariantProductsIds);
					} else {
						$VariantProducts = array();
					}
					$inventory = '';
				} else {
					$variant_master_str = '';
					$VariantProducts = array();
					$ProductStock = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'qty');
					$inventory = ($product_type == 'simple') ? $ProductStock->qty : '';
				}

				$pro_attr_str = '';
				$SingleRow = array("$product_type", "$inventory_type", "$product_name", "$product_code", "$category", "$gender", "$description", "$highlights", "$product_reviews_code", "$launch_date", "$estimate_delivery_time", "$sku", "$barcode", "$inventory", "$cost_price", "$price", "$tax_percent", "$webshop_price", "$images", "$default_img_name", "$status", "$meta_title", "$meta_keyword", "$meta_description", "$search_keywords", "$promo_reference", "$weight", "$location");

				if (isset($sis_export_header_attr_ids) && count($sis_export_header_attr_ids) > 0) {
					foreach ($sis_export_header_attr_ids as $attr) {
						// if($attr['is_default']){
						// 	continue;
						// }
						$attr_id = $attr;
						$ProductAttr = $this->SellerProductModel->getSingleDataByID('products_attributes', array('product_id' => $product_id, 'attr_id' => $attr_id), '');


						$attrDetails = $this->EavAttributesModel->get_attribute_detail($attr_id);
						$attr_properties = $attrDetails->attr_properties;
						if (isset($ProductAttr) && $ProductAttr->id != '') {
							$pro_attr_str = $attr_value = $ProductAttr->attr_value;

							/*----------------------------------------------------------*/

							if (!empty($attr_value) && $attr_properties == 5) {

								$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_option_id($shop_id, $attr_id, $attr_value);
								if (isset($IsOptionExist) && $IsOptionExist->id != '') {
									$pro_attr_str = $IsOptionExist->attr_options_name;
								}
							} else if (!empty($attr_value) && $attr_properties == 6) {

								if (strpos($attr_value, ',') !== false) {
									$attr_value_arr = explode(',', $attr_value);
								} else {
									$attr_value_arr[] = $attr_value;
								}

								array_filter($attr_value_arr);

								$attr_value_ids = array();

								if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
									foreach ($attr_value_arr as $attr_value_option) {
										$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_option_id($shop_id, $attr_id, $attr_value_option);
										if (isset($IsOptionExist) && $IsOptionExist->id != '') {

											$attr_value_ids[] = $IsOptionExist->attr_options_name;
										} else {
										}
									}

									$pro_attr_str = implode(',', $attr_value_ids);
								}
							}

							/*------------------------------------------------------------*/
						} else {
							$pro_attr_str = '';
						}

						array_push($SingleRow, $pro_attr_str);
					}
				}


				if ($product_type == 'configurable') {

					if ($variant_master_str) {
						array_push($SingleRow, $variant_master_str);
					}
				}


				$ExportValuesArr[] = $SingleRow;


				if (isset($VariantProducts) && count($VariantProducts) > 0) {
					$ExportChildArr = array();
					$parent_product = $product_name;
					foreach ($VariantProducts as $variant) {
						$product_type = $variant['product_type'];
						$inventory_type = 'buy';
						// if( $variant['product_inv_type'] == 'buy' && $variant['shop_id'] == 0 )
						// {
						// 	$inventory_type = 'Own';
						// }elseif ($variant['product_inv_type'] == 'buy' && $variant['shop_id'] > 0) {
						// 	$inventory_type = 'buy';
						// }elseif ($variant['product_inv_type'] == 'virtual') {
						// 	$inventory_type = 'virtual';
						// }else{
						// 	$inventory_type = 'dropship';
						// }
						$sku = ($product_type == 'conf-simple') ? $variant['sku'] : '';
						$barcode = ($product_type == 'conf-simple') ? $variant['barcode'] : '';
						$inventory = ($product_type == 'conf-simple') ? $variant['qty'] : '';
						$cost_price = ($product_type == 'conf-simple') ? round($variant['cost_price'], 2) : '';
						$price = ($product_type == 'conf-simple') ? round($variant['price'], 2) : '';
						$tax_percent = ($product_type == 'conf-simple') ? round($variant['tax_percent'], 2) : '';
						$tax_amount = ($product_type == 'conf-simple') ? $variant['tax_amount'] : '';
						$webshop_price = ($product_type == 'conf-simple') ? $variant['webshop_price'] : '';

						$weight = ($product_type == 'conf-simple') ? $variant['weight'] : '';


						if ($product_type == 'conf-simple') {
							$ProductMedia = $this->SellerProductModel->getMultiDataById('products_media_gallery', array('product_id' => $product_id, 'child_id' => $variant['id']), '');
						} else {
							$ProductMedia = array();
						}

						$child_img_arr = array();
						$child_img_str = '';
						if (isset($ProductMedia) && count($ProductMedia) > 0) {
							foreach ($ProductMedia as $media) {
								$child_img_arr[] = $media->image_title;
							}
							$child_img_str = implode(',', $child_img_arr);
						}

						$child_images = $child_img_str;

						$SingleRow = array("$product_type", "$inventory_type", "$product_name", "$product_code", "", "", "", "", "", "", "", "", "$sku", "$barcode", "$inventory", "$cost_price", "$price", "$tax_percent", "$webshop_price", "$child_images", "", "$status", "", "", "", "", "", "", "$weight", "$location");

						if (isset($sis_export_header_attr_ids) && count($sis_export_header_attr_ids) > 0) {
							foreach ($sis_export_header_attr_ids as $attr) {
								// if($attr['is_default']){
								// 	continue;
								// }
								$pro_attr_str = '';
								array_push($SingleRow, $pro_attr_str);
							}
						}

						$variant_master_str2 = '';
						array_push($SingleRow, $variant_master_str2);

						if (isset($sis_export_header_variants_ids) && count($sis_export_header_variants_ids) > 0) {

							foreach ($sis_export_header_variants_ids as $attr) {
								$attr_id = $attr;

								$OptionSelected = $this->SellerProductModel->getSingleDataByID('products_variants', array('product_id' => $variant['id'], 'parent_id' => $product_id, 'attr_id' => $attr_id), 'attr_value');

								$attr_option_selected = (isset($OptionSelected) && $OptionSelected->attr_value != '') ? $OptionSelected->attr_value : '';


								if ($attr_option_selected) {
									$OptionData = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('id' => $attr_option_selected, 'attr_id' => $attr_id), 'id,attr_id,attr_options_name');
									if (isset($OptionData) && $OptionData->id != '') {
										$attr_label = $OptionData->attr_options_name;
									} else {
										$attr_label = '';
									}
								} else {
									$attr_label = '';
								}


								array_push($SingleRow, $attr_label);
							}
						} else {
							$attr_label = '';
							array_push($SingleRow, $attr_label);
						}


						$ExportValuesArr[] = $SingleRow;
					}
				}
			}


			// print_r_custom($ExportValuesArr);exit;

		}

		$filename = 'All_Catalog_Products-' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// file creation
		$file = fopen('php://output', 'w');

		fputcsv($file, $sis_export_header);

		if (isset($ExportValuesArr) && count($ExportValuesArr) > 0) {

			foreach ($ExportValuesArr as $readData) {
				fputcsv($file, $readData);
			}
		}
		fclose($file);
		exit;

		echo "success";
		exit;
	}

	function downloadproductcsvUpdate()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$this->load->model('WebshopModel');


		$Products = $this->SellerProductModel->getProductForCSVImportUpdate_opt();
		$sis_export_header = array("product_name", "sku", "barcode", "inventory", "cost_price", "selling_price", "tax_percent", "variant");

		if (isset($Products) && count($Products) > 0) {

			$ExportValuesArr = array();
			$product_ids = array_column($Products, 'id');

			$variant = $this->WebshopModel->getVariantsMultiple($product_ids);
			$variants_match_ids = array_column($variant, 'product_id');
			$this->variants_match = $this->CommonModel->array_group($variant, 'product_id');

			// print_r($this->variants_match);exit();

			foreach ($Products as $product) {
				$json_result = "";
				$product_id = $product->id;
				$product_type = $product->product_type;

				if (($product_type == 'conf-simple' && $product->parent_remove_flag == 0) || $product_type == 'simple') {

					$product_name = $product->name;
					$sku = $product->sku;
					$barcode = $product->barcode;
					$inventory = $product->qty;

					$cost_price = round($product->cost_price, 2);
					$price = round($product->price, 2);
					$tax_percent = round($product->tax_percent, 2);
					$pro_attr_str = '';

					if (in_array($product_id, $variants_match_ids, true)) {
						$variantValueData = $this->variants_match[$product_id];
						if (isset($variantValueData) && $variantValueData > 0) {
							foreach ($variantValueData as $value1) {
								$json_result .= $value1->attr_name . ' : ' . $value1->attr_options_name . ' ; ';
							}
							$json_result =  rtrim($json_result, " ; ");
						}
					}

					$SingleRow = array("$product_name", "$sku", "$barcode", "$inventory", "$cost_price", "$price", "$tax_percent", "$json_result");
				}

				if (isset($sku) && $sku != '') {
					$ExportValuesArr[] = $SingleRow;
				}
			}
		}

		$filename = 'SISProducts-Update-' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// file creation
		$file = fopen('php://output', 'wb');

		fputcsv($file, $sis_export_header);

		if (isset($ExportValuesArr) && count($ExportValuesArr) > 0) {

			foreach ($ExportValuesArr as $readData) {
				fputcsv($file, $readData);
			}
		}
		fclose($file);
		exit;

		echo "success";
		exit;
	}

	function DownloadOnlineInventoryCSVUpdate()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$Products = $this->SellerProductModel->getProductForCSVImportUpdate_opt();
		$sis_export_header = array("product_name", "sku", "barcode", "inventory", "online_inventory", "cost_price", "selling_price", "tax_percent");

		if (isset($Products) && count($Products) > 0) {

			$ExportValuesArr = array();
			foreach ($Products as $product) {

				$product_id = $product->id;
				$product_type = $product->product_type;

				if (($product_type == 'conf-simple' && $product->parent_remove_flag == 0) || $product_type == 'simple') {

					$product_name = $product->name;
					$sku = $product->sku;
					$barcode = $product->barcode;
					$inventory = $product->qty;
					$online_inventory = $product->available_qty;
					$cost_price = round($product->cost_price, 2);
					$price = round($product->price, 2);
					$tax_percent = round($product->tax_percent, 2);
					$pro_attr_str = '';

					$SingleRow = array("$product_name", "$sku", "$barcode", "$inventory", "$online_inventory", "$cost_price", "$price", "$tax_percent");
				}


				if (isset($sku) && $sku != '') {
					$ExportValuesArr[] = $SingleRow;
				}
			}
		}

		$filename = 'SISProductsInventory-Update-' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// file creation
		$file = fopen('php://output', 'wb');

		fputcsv($file, $sis_export_header);

		if (isset($ExportValuesArr) && count($ExportValuesArr) > 0) {

			foreach ($ExportValuesArr as $readData) {
				fputcsv($file, $readData);
			}
		}
		fclose($file);
		exit;

		echo "success";
		exit;
	}

	function DownloadAllProductAttributesCSVOld()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$Products = $this->SellerProductModel->getAllProductForCSVImport_opt();
		//new
		$this->Products_data = $Products;
		$sis_export_header = array("product_code", "product_name", "gender", "description", "highlights", "product_reviews_code", "launch_date", "customer_types", "estimate_delivery_time", "product_drop_shipment", "meta_title", "meta_keyword", "meta_description", "search_keywords", "promo_reference", "can_be_returned", "coming_soon_flag");
		// ,"weight","location");

		// file name
		if (isset($Products) && count($Products) > 0) {


			$ExportValuesArr = array();
			// catlog attributes
			$sis_export_header_attr = array();
			$sis_export_header_attr_ids = array();

			$attr_value_ids = $this->SellerProductModel->getCatelogAllAttrs();
			$attr_value_ids_arr = json_decode(json_encode($attr_value_ids), true);
			$attr_value_ids_arr_new = explode(',', $attr_value_ids_arr['attr_ids']);
			$csv_attr = $this->EavAttributesModel->get_attributes_for_seller($attr_value_ids_arr_new);
			// new
			$product_ids = array_column($Products, 'id');
			$csv_attr_ids = array_column($csv_attr, 'id');
			$ProductAttr1 = $this->SellerProductModel->getDataProductAttrMultiple('products_attributes', $product_ids, $csv_attr_ids, '');
			//$this->ProductAttr_data=$ProductAttr1;
			$attrDetailsMultiple = $this->EavAttributesModel->get_attribute_detail_opt_multiple($csv_attr_ids);

			$this->attrDetailsMultipleConfigData = array_column($attrDetailsMultiple, 'id');
			$key = array_search(10, $this->attrDetailsMultipleConfigData);

			if (isset($csv_attr) && count($csv_attr) > 0) {
				foreach ($csv_attr as $attr) {
					if ($attr['is_default']) {
						continue;
					}
					$attr_code = $attr['attr_code'];
					$attr_id = $attr['id'];
					$attr_code = 'attribute_' . $attr_code;
					if (!in_array($attr_code, $sis_export_header)) {
						array_push($sis_export_header, $attr_code);
						array_push($sis_export_header_attr, $attr_code);
						array_push($sis_export_header_attr_ids, $attr_id);
						// $sis_export_header_attr['inputs']['id'] = $attr_id;
					}
				}
			}

			foreach ($Products as $product) {

				$VariantProducts = array();

				$product_id = $product->id;
				$product_type = $product->product_type;
				$product_name = $product->name;
				$parent_product = '';

				$product_code = $product->product_code;

				$gender = $product->gender;

				$description = $product->description;
				$highlights = $product->highlights;
				$product_reviews_code = $product->product_reviews_code;

				$launch_date = (isset($product->launch_date) && $product->launch_date != '') ? date(SIS_DATE_FM, $product->launch_date) : '';
				$customer_types = $product->customer_type_ids;
				$estimate_delivery_time = $product->estimate_delivery_time;
				$product_return_time = $product->product_return_time;
				$product_drop_shipment = $product->product_drop_shipment;
				$meta_title = $product->meta_title;
				$meta_keyword = $product->meta_keyword;
				$meta_description = $product->meta_description;
				$search_keywords = $product->search_keywords;
				$promo_reference = $product->promo_reference;
				$can_be_returned = $product->can_be_returned;
				$coming_product = $product->coming_soon_flag;

				$pro_attr_str = '';
				$SingleRow = array("$product_code", "$product_name", "$gender", "$description", "$highlights", "$product_reviews_code", "$launch_date", "$customer_types", "$estimate_delivery_time", "$product_drop_shipment", "$meta_title", "$meta_keyword", "$meta_description", "$search_keywords", "$promo_reference", "$can_be_returned", "$coming_product");
				// ,"$weight","$location");
				if (isset($sis_export_header_attr_ids) && count($sis_export_header_attr_ids) > 0) {
					$i = 1;

					foreach ($sis_export_header_attr_ids as $attr) {
						$attr_id = $attr;
						$var1 = $product_id;
						$var2 = $attr_id;

						$filtered_array_products_attributes = array_filter($ProductAttr1, function ($val) use ($var1, $var2) {
							return ($val['product_id'] == $var1 and $val['attr_id'] == $var2);
						});

						$keyProductAttr = array_search($attr_id, $this->attrDetailsMultipleConfigData);
						if (isset($keyProductAttr) && $keyProductAttr !== '') {
							$attr_properties = $attrDetailsMultiple[$keyProductAttr]['attr_properties'];
						} else {
							$attr_properties = '';
						}

						$products_attributes_new_aarray = array_values($filtered_array_products_attributes);

						// print_r($attr_properties);

						if (isset($products_attributes_new_aarray) && !empty($products_attributes_new_aarray) && $products_attributes_new_aarray[0]['id'] != '') {
							$pro_attr_str = $attr_value = $products_attributes_new_aarray[0]['attr_value'];

							/*----------------------------------------------------------*/

							if (!empty($attr_value) && $attr_properties == 5) {

								$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_option_id_opt($shop_id, $attr_id, $attr_value);
								if (isset($IsOptionExist) && $IsOptionExist->id != '') {
									$pro_attr_str = $IsOptionExist->attr_options_name;
								}
							} else if (!empty($attr_value) && $attr_properties == 6) {

								if (strpos($attr_value, ',') !== false) {
									$attr_value_arr = explode(',', $attr_value);
								} else {
									$attr_value_arr[] = $attr_value;
								}

								array_filter($attr_value_arr);
								//print_r($attr_value_arr);
								$attr_value_ids = array();

								if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
									foreach ($attr_value_arr as $attr_value_option) {
										$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_option_id_opt($shop_id, $attr_id, $attr_value_option);
										if (isset($IsOptionExist) && $IsOptionExist->id != '') {
											$attr_value_ids[] = $IsOptionExist->attr_options_name;
										} else {
										}
									}
									$pro_attr_str = implode(',', $attr_value_ids);
								}
							}
							/*------------------------------------------------------------*/
						} else {
							$pro_attr_str = '';
						}
						// echo '<pre>'.$i.'</pre>';
						// print_r($pro_attr_str);
						//$SingleRow=array_push($SingleRow,$pro_attr_str);
						// $i++;
						array_push($SingleRow, $pro_attr_str);
					}
				} else {
					$pro_attr_str = '';
					array_push($SingleRow, $pro_attr_str);
				}
				//exit();
				$ExportValuesArr[] = $SingleRow;
			}

			//exit();
			// print_r_custom($ExportValuesArr);exit;

		}

		$filename = 'All_Catalog_Products_Attributes-' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// file creation
		$file = fopen('php://output', 'wb');

		fputcsv($file, $sis_export_header);

		if (isset($ExportValuesArr) && count($ExportValuesArr) > 0) {

			foreach ($ExportValuesArr as $readData) {
				fputcsv($file, $readData);
			}
		}
		fclose($file);
		exit;

		echo "success";
		exit;
	}

	function DownloadAllProductAttributesCSV()
	{

		$time_start = microtime(true);
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$Products = $this->SellerProductModel->getAllProductForCSVImport_opt();
		//new
		$this->Products_data = $Products;
		$sis_export_header = array("product_code", "product_name", "gender", "description", "highlights", "product_reviews_code", "launch_date", "customer_types", "estimate_delivery_time", "product_drop_shipment", "meta_title", "meta_keyword", "meta_description", "search_keywords", "promo_reference", "can_be_returned", "coming_soon_flag");
		// ,"weight","location");

		// file name
		if (isset($Products) && count($Products) > 0) {


			$ExportValuesArr = array();
			// catlog attributes
			$sis_export_header_attr = array();
			$sis_export_header_attr_ids = array();

			$attr_value_ids = $this->SellerProductModel->getCatelogAllAttrs();
			$attr_value_ids_arr = json_decode(json_encode($attr_value_ids), true);
			$attr_value_ids_arr_new = explode(',', $attr_value_ids_arr['attr_ids']);
			$csv_attr = $this->EavAttributesModel->get_attributes_for_seller($attr_value_ids_arr_new);
			// new
			$product_ids = array_column($Products, 'id');
			$csv_attr_ids = array_column($csv_attr, 'id');
			$ProductAttr1 = $this->SellerProductModel->getDataProductAttrMultiple('products_attributes', $product_ids, $csv_attr_ids, '');

			$this->ProductAttr_data = $ProductAttr1;
			$attrDetailsMultiple = $this->EavAttributesModel->get_attribute_detail_opt_multiple($csv_attr_ids);

			$this->attrDetailsMultipleConfigData = array_column($attrDetailsMultiple, 'id');

			// new
			$IsOptionExistMultiple = $this->EavAttributesModel->check_attributes_options_exist_by_option_id_opt_multiple($shop_id, $attr_value_ids_arr_new);
			$this->IsOptionExistMultipleData = array_column($IsOptionExistMultiple, 'id');

			if (isset($csv_attr) && count($csv_attr) > 0) {
				foreach ($csv_attr as $attr) {
					if ($attr['is_default']) {
						continue;
					}
					$attr_code = $attr['attr_code'];
					$attr_id = $attr['id'];
					$attr_code = 'attribute_' . $attr_code;
					if (!in_array($attr_code, $sis_export_header)) {
						array_push($sis_export_header, $attr_code);
						array_push($sis_export_header_attr, $attr_code);
						array_push($sis_export_header_attr_ids, $attr_id);
					}
				}
			}


			foreach ($Products as $product) {

				$VariantProducts = array();

				$product_id = $product->id;
				$product_type = $product->product_type;
				$product_name = $product->name;
				$parent_product = '';

				$product_code = $product->product_code;

				$gender = $product->gender;

				$description = $product->description;
				$highlights = $product->highlights;
				$product_reviews_code = $product->product_reviews_code;

				$launch_date = (isset($product->launch_date) && $product->launch_date != '') ? date(SIS_DATE_FM, $product->launch_date) : '';
				$customer_types = $product->customer_type_ids;
				$estimate_delivery_time = $product->estimate_delivery_time;
				$product_return_time = $product->product_return_time;
				$product_drop_shipment = $product->product_drop_shipment;
				$meta_title = $product->meta_title;
				$meta_keyword = $product->meta_keyword;
				$meta_description = $product->meta_description;
				$search_keywords = $product->search_keywords;
				$promo_reference = $product->promo_reference;
				$can_be_returned = $product->can_be_returned;
				$coming_product = $product->coming_soon_flag;

				$pro_attr_str = '';
				$SingleRow = array("$product_code", "$product_name", "$gender", "$description", "$highlights", "$product_reviews_code", "$launch_date", "$customer_types", "$estimate_delivery_time", "$product_drop_shipment", "$meta_title", "$meta_keyword", "$meta_description", "$search_keywords", "$promo_reference", "$can_be_returned", "$coming_product");
				// ,"$weight","$location");
				if (isset($sis_export_header_attr_ids) && count($sis_export_header_attr_ids) > 0) {
					$i = 1;
					//$SingleRow=$this->productsAttributesData($ProductAttr1,$SingleRow,$sis_export_header_attr_ids,$product_id,$this->attrDetailsMultipleConfigData,$attrDetailsMultiple,$IsOptionExistMultiple);
					foreach ($sis_export_header_attr_ids as $attr) {
						$attr_id = $attr;
						$var1 = $product_id;
						$var2 = $attr_id;

						$filtered_array_products_attributes = array_filter($ProductAttr1, function ($val) use ($var1, $var2) {
							return ($val['product_id'] == $var1 and $val['attr_id'] == $var2);
						});


						$keyProductAttr = array_search($attr_id, $this->attrDetailsMultipleConfigData);
						if (isset($keyProductAttr) && $keyProductAttr !== '') {
							$attr_properties = $attrDetailsMultiple[$keyProductAttr]['attr_properties'];
						} else {
							$attr_properties = '';
						}

						$products_attributes_new_aarray = array_values($filtered_array_products_attributes);

						if (isset($products_attributes_new_aarray) && !empty($products_attributes_new_aarray) && $products_attributes_new_aarray[0]['id'] != '') {
							$pro_attr_str = $attr_value = $products_attributes_new_aarray[0]['attr_value'];



							if (!empty($attr_value) && $attr_properties == 5) {
								$IsOptionExist = array_search($attr_value, $this->IsOptionExistMultipleData);
								if (isset($IsOptionExist) && $IsOptionExist !== '') {
									$pro_attr_str = $IsOptionExistMultiple[$IsOptionExist]['attr_options_name'];
								}
							} else if (!empty($attr_value) && $attr_properties == 6) {
								if (strpos($attr_value, ',') !== false) {
									$attr_value_arr = explode(',', $attr_value);
								} else {
									$attr_value_arr[] = $attr_value;
								}

								array_filter($attr_value_arr);

								$attr_value_ids = array();

								if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
									foreach ($attr_value_arr as $attr_value_option) {

										$IsOptionExist = array_search($attr_value_option, $this->IsOptionExistMultipleData);
										if (isset($IsOptionExist) && $IsOptionExist !== '') {
											$attr_value_ids[] = $IsOptionExistMultiple[$IsOptionExist]['attr_options_name'];
										}
									}
									$pro_attr_str = implode(',', $attr_value_ids);
								}
							}
						} else {
							$pro_attr_str = '';
						}
						array_push($SingleRow, $pro_attr_str);
					}
				} else {
					$pro_attr_str = '';
					array_push($SingleRow, $pro_attr_str);
				}

				$ExportValuesArr[] = $SingleRow;
			}
		}

		$filename = 'All_Catalog_Products_Attributes-' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// file creation
		$file = fopen('php://output', 'wb');

		fputcsv($file, $sis_export_header);

		if (isset($ExportValuesArr) && count($ExportValuesArr) > 0) {
			foreach ($ExportValuesArr as $readData) {
				fputcsv($file, $readData);
			}
		}
		fclose($file);
		exit;

		echo "success";
		exit;

		// $time_elapsed_secs = microtime(true) - $time_start;
		// echo $time_elapsed_secs;exit();

	}

	function productsAttributesData($ProductAttr1, $SingleRow, $sis_export_header_attr_ids, $product_id, $attrDetailsMultipleConfigData, $attrDetailsMultiple, $IsOptionExistMultiple)
	{

		foreach ($sis_export_header_attr_ids as $attr) {
			$attr_id = $attr;
			$var1 = $product_id;
			$var2 = $attr_id;

			$filtered_array_products_attributes = array_filter($ProductAttr1, function ($val) use ($var1, $var2) {
				return ($val['product_id'] == $var1 and $val['attr_id'] == $var2);
			});

			// $filtered_array_products_attributes = array_filter($ProductAttr1, function($val) use($var1, $var2){
			// 	return ($val['product_id']==$var1);
			// });

			$keyProductAttr = array_search($attr_id, $attrDetailsMultipleConfigData);
			if (isset($keyProductAttr) && $keyProductAttr !== '') {
				$attr_properties = $attrDetailsMultiple[$keyProductAttr]['attr_properties'];
			} else {
				$attr_properties = '';
			}

			$products_attributes_new_aarray = array_values($filtered_array_products_attributes);

			if (isset($products_attributes_new_aarray) && !empty($products_attributes_new_aarray) && $products_attributes_new_aarray[0]['id'] != '') {
				$pro_attr_str = $attr_value = $products_attributes_new_aarray[0]['attr_value'];

				/*----------------------------------------------------------*/

				if (!empty($attr_value) && $attr_properties == 5) {
					$IsOptionExist = array_search($attr_value, $this->IsOptionExistMultipleData);
					if (isset($IsOptionExist) && $IsOptionExist !== '') {
						$pro_attr_str = $IsOptionExistMultiple[$IsOptionExist]['attr_options_name'];
					}
				} else if (!empty($attr_value) && $attr_properties == 6) {

					if (strpos($attr_value, ',') !== false) {
						$attr_value_arr = explode(',', $attr_value);
					} else {
						$attr_value_arr[] = $attr_value;
					}

					array_filter($attr_value_arr);

					$attr_value_ids = array();

					if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
						foreach ($attr_value_arr as $attr_value_option) {

							$IsOptionExist = array_search($attr_value_option, $this->IsOptionExistMultipleData);
							if (isset($IsOptionExist) && $IsOptionExist !== '') {
								$attr_value_ids[] = $IsOptionExistMultiple[$IsOptionExist]['attr_options_name'];
							}
						}

						$pro_attr_str = implode(',', $attr_value_ids);
					}
				}

				/*------------------------------------------------------------*/
			} else {
				$pro_attr_str = '';
			}

			array_push($SingleRow, $pro_attr_str);
		}

		//return $SingleRow1;

	}

	function ProductAttributesCSV_import_template_download()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$Products = $this->SellerProductModel->getAllProductForCSVImport_opt();
		$sis_export_header = array("product_code", "product_name", "gender", "description", "highlights", "product_reviews_code", "launch_date", "customer_types", "estimate_delivery_time", "product_drop_shipment", "meta_title", "meta_keyword", "meta_description", "search_keywords", "promo_reference", "can_be_returned", "coming_soon_flag");

		if (isset($Products) && count($Products) > 0) {

			$ExportValuesArr = array();
			// catlog attributes
			$sis_export_header_attr = array();
			$sis_export_header_attr_ids = array();

			$attr_value_ids = $this->SellerProductModel->getCatelogAllAttrs();
			$attr_value_ids_arr = json_decode(json_encode($attr_value_ids), true);
			$attr_value_ids_arr_new = explode(',', $attr_value_ids_arr['attr_ids']);
			$csv_attr = $this->EavAttributesModel->get_attributes_for_seller($attr_value_ids_arr_new);

			if (isset($csv_attr) && count($csv_attr) > 0) {
				foreach ($csv_attr as $attr) {
					if ($attr['is_default']) {
						continue;
					}
					$attr_code = $attr['attr_code'];
					$attr_id = $attr['id'];
					$attr_code = 'attribute_' . $attr_code;
					if (!in_array($attr_code, $sis_export_header)) {
						array_push($sis_export_header, $attr_code);
						array_push($sis_export_header_attr, $attr_code);
						array_push($sis_export_header_attr_ids, $attr_id);
					}
				}
			}
		}

		$filename = 'Products_Attributes_Template-' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// file creation
		$file = fopen('php://output', 'wb');

		fputcsv($file, $sis_export_header);

		if (isset($ExportValuesArr) && count($ExportValuesArr) > 0) {
			foreach ($ExportValuesArr as $readData) {
				fputcsv($file, $readData);
			}
		}
		fclose($file);
		exit;
	}


	function downloadproductcsvUpdate_1()
	{
		$Products = $this->SellerProductModel->getProductForCSVImportUpdate_1();
		$sis_export_header = array("product_code", "launch_date", "customer_types");

		$filename = 'SISProducts-Update-' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// file creation
		$file = fopen('php://output', 'wb');

		fputcsv($file, $sis_export_header);

		// file name
		if (isset($Products) && count($Products) > 0) {

			$ExportValuesArr = array();
			foreach ($Products as $product) {

				$launch_date = (isset($product->launch_date) && $product->launch_date != '') ? date(SIS_DATE_FM, $product->launch_date) : '';
				fputcsv($file, [
					$product->product_code,
					$launch_date,
					$product->customer_type_ids
				]);
			}
		}


		fclose($file);
		exit;
	}

	function DownloadProductInventoryTypeCSV()
	{
		$Products = $this->SellerProductModel->getProductForInventoryTypesCSVUpdate();

		$sis_export_header = array("sku", "supplier_name", "launch_date", "inventory", "products_inv_type");

		$filename = 'SISProducts-Update-' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// file creation
		$file = fopen('php://output', 'wb');

		fputcsv($file, $sis_export_header);

		// file name
		if (isset($Products) && count($Products) > 0) {

			foreach ($Products as $product) {
				$launch_date = date("d-m-Y", $product->launch_date);
				fputcsv($file, [
					$product->sku,
					$product->org_shop_name,
					($product->launch_date = '') ? '-' : $launch_date,
					$product->qty,
					$product->product_inv_type
				]);
			}
		}

		fclose($file);
		exit;
	}

	function DownloadWeightLocationCSV()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$Products = $this->SellerProductModel->getProductDataForCSVUpdate();

		$sis_export_header = array("sku", "cost_price", "selling_price", "tax_percent", "weight", "location");

		$filename = 'SISProducts-Update-Weight-Location' . time() . '.csv';
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/csv; ");

		// file creation
		$file = fopen('php://output', 'wb');
		fputcsv($file, $sis_export_header);
		if (isset($Products) && count($Products) > 0) {

			foreach ($Products as $product) {

				if (!isset($product->sku) || $product->sku == '') {
					continue;
				}

				$product_type = $product->product_type;

				if ($product_type === 'conf-simple'  || $product_type === 'simple') {
					fputcsv($file, [
						$product->sku,
						$product->cost_price,
						$product->price,
						$product->tax_percent,
						$product->weight,
						$product->prod_location
					]);
				}
			}
		}


		fclose($file);
		exit;
	}

	function openbulkuploadpopup()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		//$data['type']=$type=isset($_POST['type'])?$_POST['type']:'';
		$View = $this->load->view('seller/products/bulk-upload-csv', $data, true);
		$this->output->set_output($View);
	}

	function openbulkuploadpopupupdate()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		//$data['type']=$type=isset($_POST['type'])?$_POST['type']:'';
		$View = $this->load->view('seller/products/bulk-update-csv', $data, true);
		$this->output->set_output($View);
	}

	function openbulkuploadpopupupdate_1()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		//$data['type']=$type=isset($_POST['type'])?$_POST['type']:'';
		$View = $this->load->view('seller/products/bulk-update-csv-1', $data, true);
		$this->output->set_output($View);
	}

	function BulkUploadInventoryTypesPopup()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$View = $this->load->view('seller/products/bulk-inventory-type-update-csv', $data, true);
		$this->output->set_output($View);
	}

	function BulkUploadAttributesPopup()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$View = $this->load->view('seller/products/bulk-attributes-update-csv', $data, true);
		$this->output->set_output($View);
	}

	function BulkUploadweightLocationPopup()
	{
		$data['PageTitle'] = 'Warehouse - CSV Import';
		$data['side_menu'] = 'bulk-add';
		$View = $this->load->view('seller/products/bulk-weight-location-update-csv', $data, true);
		$this->output->set_output($View);
	}

	function checkcsvdata()
	{

		if (!empty($_FILES['upload_csv_file']['name'])) {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$appne_msg = '';

			$allowed =  array('csv'); //you can mentions all the allowed file format you need to accept, like .jpg, gif.
			$filename = $_FILES['upload_csv_file']['name']; // csv_file is the file name on the form


			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if (!in_array($ext, $allowed)) {
				$appne_msg .= " Please Upload Files With .CSV Extenion Only.";
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			}


			$attr_not_found = array();
			$variant_not_found = array();

			$cat_not_found = array();
			$sub_cat_not_found = array();
			$image_not_found = array();

			$attr_str = '';
			$var_str = '';
			$img_str = '';
			$product_data = array();
			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');
			$count = 0;
			$headers = '';
			while ($row = fgetcsv($file_data)) {
				$headers = $row;
				if ($count == 0) {
					break;
				}
				$count++;
			}
			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}

			if (isset($product_data) && count($product_data) >= 1) {
				foreach ($product_data as $index => $product) {

					if (in_array("category", $headers)) {
						$key1 = array_search('category', $headers);
						$category = $product[$key1];
					}
					if (in_array("sub_category", $headers)) {
						$key2 = array_search('sub_category', $headers);
						$sub_category = $product[$key2];
					}

					$MainCat = '';
					$SubCat = '';

					if ($category != '') {
						$MainCat = $this->CategoryModel->check_category_exist_by_level(0, $category);

						if (isset($MainCat) && $MainCat['id'] != '') {
						} else {
							$cat_not_found[] = $category;
						}
					}

					// if($sub_category!='' ){

					// 	if(isset($MainCat) && $MainCat['id']!='')
					// 	{
					// 		$SubCat=$this->CategoryModel->check_child_category_exist($MainCat['id'],1,$sub_category);
					// 		if(isset($SubCat) && $SubCat['id']!=''){

					// 		}else{
					// 			$sub_cat_not_found[]=$sub_category;
					// 		}
					// 	}else{
					// 		$sub_cat_not_found[]=$sub_category;
					// 	}
					// }
				}
			}

			

			if (isset($headers) && count($headers) > 0) {
				foreach ($headers as $columns) {
					if (strpos($columns, 'attribute_') !== false) {
						$attr_code = str_replace('attribute_', '', $columns);
						//$CheckAttr=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>1),'id');
						$CheckAttr = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code(1, $attr_code);
						// echo "<pre>";print_r($CheckAttr);die;
						if (isset($CheckAttr) && $CheckAttr->id != '') {
						} else {
							$attr_not_found[] = $columns;
						}
					}
					if (strpos($columns, 'variant_') !== false) {
						$attr_code = str_replace('variant_', '', $columns);
						//$CheckAttr=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>2),'id');
						$CheckAttr = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code(2, $attr_code);
						if (isset($CheckAttr) && $CheckAttr->id != '') {
						} else {
							$variant_not_found[] = $columns;
						}
					} else {
						continue;
					}
				}


				if (count($attr_not_found) > 0) {
					$attr_str = implode(', ', $attr_not_found);
					$appne_msg .= " Attributes column $attr_str not found in database.";
				}

				if (count($variant_not_found) > 0) {
					$var_str = implode(', ', $variant_not_found);
					$appne_msg .= " Variants column $var_str not found in database.";
				}


				if (count($cat_not_found) > 0) {
					$cat_str = implode(', ', $cat_not_found);
					$appne_msg .= " Category  $cat_str not found in database.";
				}

				//  if(count($sub_cat_not_found)>0){
				// 	 $sub_cat_str=implode(', ',$sub_cat_not_found);
				// 	  $appne_msg.=" Sub Category $sub_cat_str not found in database.";
				//  }

				if (count($product_data) <= 0) {
					$appne_msg .= " Product data not found.";
				}

				if ($appne_msg != '') {
					$arrResponse  = array('status' => 400, 'message' => $appne_msg);
					echo json_encode($arrResponse);
					exit;
				} else {
					$arrResponse  = array('status' => 200, 'message' => "Product sheet validated, Please continue to upload.");
					echo json_encode($arrResponse);
					exit;
				}
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Invalid csv file');
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Please upload proper csv file');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function checkupdatecsvdata()
	{
		
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$appne_msg = '';

			$allowed =  array('csv'); //you can mentions all the allowed file format you need to accept, like .jpg, gif.
			$filename = $_FILES['upload_csv_file']['name']; // csv_file is the file name on the form

			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if (!in_array($ext, $allowed)) {
				$appne_msg .= " Please Upload Files With .CSV Extenion Only.";
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			}


			$sku_not_found = array();

			$product_data = array();

			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			$count = 0;
			$headers = '';
			while ($row = fgetcsv($file_data)) {

				$headers = $row;
				if ($count == 0) {
					break;
				}
				$count++;
			}

			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}
			

			if (isset($product_data) && count($product_data) >= 1) {
				foreach ($product_data as $index => $product) {

					$sku = $product[1];

					if ($sku != '') {

						$check_sku = $this->SellerProductModel->getSingleDataByID('products', array('sku' => $sku, 'remove_flag' => 0), 'sku');

						if (isset($check_sku) && $check_sku->sku != '') {
						} else {
							$sku_not_found[] = $sku;
						}
					} else {
						$appne_msg .= " Please upload proper csv file.";
						$arrResponse  = array('status' => 400, 'message' => $appne_msg);
						echo json_encode($arrResponse);
						exit;
					}
				}
			}

			if (count($sku_not_found) > 0) {
				$sku_str = implode(', ', $sku_not_found);
				$appne_msg .= " Sku column $sku_str not found in database.";
			}




			if (count($product_data) <= 0) {
				$appne_msg .= " Product data not found.";
			}

			if ($appne_msg != '') {
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 200, 'message' => "ProductUpdate sheet validated, Please continue to upload.");
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Please upload proper csv file');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function checkupdatecsvdata_1()
	{
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$appne_msg = '';

			$allowed =  array('csv'); //you can mentions all the allowed file format you need to accept, like .jpg, gif.
			$filename = $_FILES['upload_csv_file']['name']; // csv_file is the file name on the form

			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if (!in_array($ext, $allowed)) {
				$appne_msg .= " Please Upload Files With .CSV Extenion Only.";
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			}

			$product_code_not_found = array();

			$product_data = array();

			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			$count = 0;
			$headers = '';
			while ($row = fgetcsv($file_data)) {

				$headers = $row;
				if ($count == 0) {
					break;
				}
				$count++;
			}

			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}


			if (isset($product_data) && count($product_data) >= 1) {
				foreach ($product_data as $index => $product) {
					$product_code = $product[0];


					if ($product_code != '') {
						$check_product_code = $this->SellerProductModel->getSingleDataByID('products', array('product_code' => $product_code, 'remove_flag' => 0), 'product_code');

						if (isset($check_product_code) && $check_product_code->product_code != '') {
						} else {
							$product_code_not_found[] = $product_code;
						}
					} else {
						$arrResponse  = array('status' => 400, 'message' => 'Please upload proper csv file');
						echo json_encode($arrResponse);
						exit;
					}
				}
			}

			if (count($product_code_not_found) > 0) {
				$product_str = implode(', ', $product_code_not_found);
				$appne_msg .= " Productcode column $product_str not found in database.";
			}


			if (count($product_data) <= 0) {
				$appne_msg .= " Product data not found.";
			}

			if ($appne_msg != '') {
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 200, 'message' => "ProductUpdate sheet validated, Please continue to upload.");
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Please upload proper csv file');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function CheckupdateCSVData_Inv_type()
	{
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$appne_msg = '';

			$allowed =  array('csv'); //you can mentions all the allowed file format you need to accept, like .jpg, gif.
			$filename = $_FILES['upload_csv_file']['name']; // csv_file is the file name on the form

			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if (!in_array($ext, $allowed)) {
				$appne_msg .= " Please Upload Files With .CSV Extenion Only.";
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			}

			$sku_not_found = array();
			$wrong_inv_type = array();

			$product_data = array();

			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			$count = 0;
			$headers = '';
			while ($row = fgetcsv($file_data)) {

				$headers = $row;
				if ($count == 0) {
					break;
				}
				$count++;
			}

			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}


			if (isset($product_data) && count($product_data) >= 1) {
				foreach ($product_data as $index => $product) {
					$sku = $product[0];
					$product_inv_type = $product[4];
					if ($sku != '') {
						$check_sku = $this->SellerProductModel->getSingleDataByID('products', array('sku' => $sku, 'remove_flag' => 0, 'shop_id <>' => 0), 'sku');
						if ($product_inv_type == 'buy' || $product_inv_type == 'virtual' || $product_inv_type == 'dropship') {
						} else {
							$wrong_inv_type[] = $sku;
						}
						if (isset($check_sku) && $check_sku->sku != '') {
						} else {
							$sku_not_found[] = $sku;
						}
					} else {
						$arrResponse  = array('status' => 400, 'message' => 'Please upload proper csv file');
						echo json_encode($arrResponse);
						exit;
					}
				}
			}

			if (count($wrong_inv_type) > 0) {
				$sku_str = implode(', ', $wrong_inv_type);
				$appne_msg .= "products_inv_type column data for sku $sku_str not found in database.";
			}

			if (count($sku_not_found) > 0) {
				$sku_str = implode(', ', $sku_not_found);
				$appne_msg .= " Sku column $sku_str not found in database.";
			}


			if (count($product_data) <= 0) {
				$appne_msg .= " Product data not found.";
			}

			if ($appne_msg != '') {
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 200, 'message' => "ProductUpdate sheet validated, Please continue to upload.");
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Please upload proper csv file');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function CheckupdateCSVAttributesData()
	{
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$appne_msg = '';

			$allowed =  array('csv'); //you can mentions all the allowed file format you need to accept, like .jpg, gif.
			$filename = $_FILES['upload_csv_file']['name']; // csv_file is the file name on the form

			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if (!in_array($ext, $allowed)) {
				$appne_msg .= " Please Upload Files With .CSV Extenion Only.";
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			}

			$product_code_not_found = array();


			$product_data = array();

			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			$count = 0;
			$headers = '';
			while ($row = fgetcsv($file_data)) {

				$headers = $row;
				if ($count == 0) {
					break;
				}
				$count++;
			}
			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}

			if (isset($product_data) && count($product_data) >= 1) {
				foreach ($product_data as $index => $product) {
					$product_code = $product[0];

					if ($product_code != '') {
						$check_product_code = $this->SellerProductModel->getSingleDataByID('products', array('product_code' => $product_code, 'remove_flag' => 0), 'product_code');

						if (isset($check_product_code) && $check_product_code->product_code != '') {
						} else {
							$product_code_not_found[] = $product_code;
						}
					} else {
						$arrResponse  = array('status' => 400, 'message' => 'Please upload proper csv file');
						echo json_encode($arrResponse);
						exit;
					}

					if (in_array("product_name", $headers)) {
						$key = array_search('product_name', $headers);
						$product_name = $product[$key];
						if (!isset($product_name) || $product_name == '') {
							$arrResponse  = array('status' => 400, 'message' => 'Data in Product_name column is misssing');
							echo json_encode($arrResponse);
							exit;
						}
					}

					if (in_array("description", $headers)) {
						$key1 = array_search('description', $headers);
						$product_description = $product[$key1];
						if (!isset($product_description) || $product_description == '') {
							$arrResponse  = array('status' => 400, 'message' => 'Data in Description column is misssing');
							echo json_encode($arrResponse);
							exit;
						}
					}

					if (in_array("highlights", $headers)) {
						$key2 = array_search('highlights', $headers);
						$product_highlights = $product[$key2];
						if (!isset($product_highlights) || $product_highlights == '') {
							$arrResponse  = array('status' => 400, 'message' => 'Data in Highlights column is misssing');
							echo json_encode($arrResponse);
							exit;
						}
					}
				}
			}

			if (count($product_code_not_found) > 0) {
				$product_str = implode(', ', $product_code_not_found);
				$appne_msg .= " Productcode column $product_str not found in database.";
			}


			if (count($product_data) <= 0) {
				$appne_msg .= " Product data not found.";
			}

			if ($appne_msg != '') {
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 200, 'message' => "ProductUpdate sheet validated, Please continue to upload.");
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Please upload proper csv file');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function CheckupdateCSVWeightLocationData()
	{
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$fbc_user_id	=	$this->session->userdata('LoginID');
			$shop_id		=	$this->session->userdata('ShopID');
			$appne_msg = '';

			$allowed =  array('csv'); //you can mentions all the allowed file format you need to accept, like .jpg, gif.
			$filename = $_FILES['upload_csv_file']['name']; // csv_file is the file name on the form

			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if (!in_array($ext, $allowed)) {
				$appne_msg .= " Please Upload Files With .CSV Extenion Only.";
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			}

			$sku_not_found = array();

			$product_data = array();

			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			$count = 0;
			$headers = '';
			while ($row = fgetcsv($file_data)) {

				$headers = $row;
				if ($count == 0) {
					break;
				}
				$count++;
			}

			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}


			if (isset($product_data) && count($product_data) >= 1) {
				foreach ($product_data as $index => $product) {

					$sku = $product[0];

					if ($sku != '') {

						$check_sku = $this->SellerProductModel->getSingleDataByID('products', array('sku' => $sku, 'remove_flag' => 0), 'sku');

						if (isset($check_sku) && $check_sku->sku != '') {
						} else {
							$sku_not_found[] = $sku;
						}
					} else {
						$appne_msg .= " Please upload proper csv file.";
						$arrResponse  = array('status' => 400, 'message' => $appne_msg);
						echo json_encode($arrResponse);
						exit;
					}
				}
			}

			if (count($sku_not_found) > 0) {
				$sku_str = implode(', ', $sku_not_found);
				$appne_msg .= " Sku column $sku_str not found in database.";
			}




			if (count($product_data) <= 0) {
				$appne_msg .= " Product data not found.";
			}

			if ($appne_msg != '') {
				$arrResponse  = array('status' => 400, 'message' => $appne_msg);
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 200, 'message' => "ProductUpdate sheet validated, Please continue to upload.");
				echo json_encode($arrResponse);
				exit;
			}
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Please upload proper csv file');
			echo json_encode($arrResponse);
			exit;
		}
	}

	function updateproducts()
	{
		// print_r_custom($_FILES);
		$product_data = array();
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag();

		$shop_upload_path = 'shop' . $shop_id;
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');
			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}

			$headers = $product_data[0];

			$sku_not_found = array();
			$sku_list = array();
			//echo "<pre>"; print_r($product_data);exit;
			if (isset($product_data) && count($product_data) > 1) {
				foreach ($product_data as $index => $product) {
					if ($index == 0) {
						continue;
					}
					$product_name = $product[0];
					$sku = $product[1];
					$barcode = $product[2];
					$stock_qty = $product[3];
					$cost_price = $product[4];
					$selling_price = $product[5];
					$tax_percent = $product[6];

					//Added by AL on 2nd april
					if (strpos($selling_price, ',') !== false) {
						$selling_price = str_replace(',', '.', $selling_price);
					}

					if (strpos($cost_price, ',') !== false) {
						$cost_price = str_replace(',', '.', $cost_price);
					}

					if (strpos($tax_percent, ',') !== false) {
						$tax_percent = str_replace(',', '.', $tax_percent);
					}

					if ($Rounded_price_flag == 1) {
						$RowInfo = $this->SellerProductModel->calculate_webshop_price($selling_price, $tax_percent);
						$tax_amount = $RowInfo['tax_amount'];
						$webshop_price = $RowInfo['webshop_price'];
					} else {
						$webshop_price = $selling_price;
						$tax_amount = 0;
						if ($selling_price > 0 && $tax_percent > 0) {
							$tax_amount = ($tax_percent / 100) * $selling_price;
							$webshop_price = $tax_amount + $selling_price;
						}
					}

					$whr_pdroduct_arr = array('sku' => $sku, 'remove_flag' => 0);

					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', $whr_pdroduct_arr, 'id,sku,barcode,cost_price,price,tax_percent,tax_amount,webshop_price,updated_at');
					// echo "<pre>";print_r($PC_Exist);
					// die();

					if (isset($PC_Exist) && $PC_Exist->id != '') {
						// echo "hii";
						/***--------Update product start-------------------------------------------------***/
						$product_id = $PC_Exist->id;
						$product_sku = $PC_Exist->sku;
						$updatedata = array(

							//'sku'=>($sku !='')? $sku : $PC_Exist->sku,
							// 'barcode' => ($barcode !='') ? $barcode : $PC_Exist->barcode,
							'cost_price' => ($cost_price != '') ? $cost_price : $PC_Exist->cost_price,
							'price' => $selling_price,
							'tax_percent' => $tax_percent,
							'tax_amount' => $tax_amount,
							'webshop_price' => $webshop_price,
							'updated_at' => time(),
							'ip' => $_SERVER['REMOTE_ADDR']
						);

						$where_arr = array('id' => $product_id);
						// echo "<pre>";print_r($updatedata);
						// print_r($where_arr);
						// die();
						$this->SellerProductModel->updateData('products', $where_arr, $updatedata);

						$OldProductQtyData = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'qty,available_qty');

						if ($OldProductQtyData->qty == $stock_qty) {
						} else {

							if ($OldProductQtyData->available_qty == $OldProductQtyData->qty) {
								$new_available_qty = $stock_qty;
							} else {

								if (isset($OldProductQtyData) && $OldProductQtyData->available_qty > 0) {
									$old_available_qty = $OldProductQtyData->available_qty;

									$old_ordered_qty = $OldProductQtyData->qty - $old_available_qty;
								} else {
									$old_ordered_qty = 0;
								}

								if ($stock_qty == '' || $stock_qty == 0) {
									$new_available_qty = 0;
								} else if ($stock_qty > 0 && $stock_qty > $OldProductQtyData->qty) {
									$new_available_qty = $stock_qty - $old_ordered_qty;
								} else {
									$new_available_qty = $stock_qty - $old_ordered_qty;
									$new_available_qty = ($new_available_qty <= 0) ? 0 : $new_available_qty;
								}
							}
							$inventory_update = array('qty' => $stock_qty, 'available_qty' => $new_available_qty);
							$whr_qty_arr = array('product_id' => $product_id);
							// echo "<pre>";print_r($inventory_update);//die();
							if (isset($stock_qty) && $stock_qty != '') {
								$this->SellerProductModel->updateData('products_inventory', $whr_qty_arr, $inventory_update);
							}
						}
					} else {
						// echo "bye";exit();
						$arrResponse  = array('status' => 400, 'message' => 'Please upload valid csv file');
						echo json_encode($arrResponse);
						exit;
					}
				}
				$arrResponse  = array('status' => 200, 'message' => 'Product Imported Successfully.');
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Please have some data in csv file');
				echo json_encode($arrResponse);
				exit;
			}
		}
	}

	function updateproducts_1()
	{
		// print_r_custom($_FILES);

		$product_data = array();

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$shop_upload_path = 'shop' . $shop_id;
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}

			$headers = $product_data[0];

			$product_code_not_found = array();

			$product_code_list = array();

			// echo "<pre>"; print_r($product_data);exit;
			if (isset($product_data) && count($product_data) > 1) {
				foreach ($product_data as $index => $product) {
					if ($index == 0) {
						continue;
					}
					$product_code = $product[0];
					$launch_date = $product[1];
					$customer_types = $product[2];
					if (isset($launch_date)  && $launch_date != '') {
						if (strpos($launch_date, '/') !== false) {
							$launch_date =  date("d-m-Y", strtotime(str_replace('/', '-', $launch_date)));
						} else {
							$launch_date =  date("d-m-Y", strtotime($launch_date));
						}
					} else {
						$launch_date =  date("d-m-Y", strtotime(date('d-m-Y')));
					}
					// echo $launch_date;//exit;
					$whr_pdroduct_arr = array('product_code' => $product_code, 'remove_flag' => 0);

					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', $whr_pdroduct_arr, 'id,product_type,product_code,launch_date,customer_type_ids');
					// echo "<pre>";print_r($PC_Exist);//die();

					if (isset($PC_Exist) && $PC_Exist->id != '') {
						/***--------Update product start-------------------------------------------------***/
						$product_id = $PC_Exist->id;
						$updatedata = array(

							//'product_code'=>($product_code !='')? $product_code : $PC_Exist->product_code ,
							'launch_date' => isset($launch_date) ? strtotime($launch_date) : $PC_Exist->launch_date,
							'customer_type_ids' => ($customer_types != '') ? $customer_types : $PC_Exist->customer_type_ids,
							'updated_at' => time(),
							'ip' => $_SERVER['REMOTE_ADDR']
						);
						$where_arr = array('id' => $product_id);

						if ($PC_Exist->product_type == 'configurable') {
							$updatedata1 = array(
								'launch_date' => isset($launch_date) ? strtotime($launch_date) : $PC_Exist->launch_date,
								'ip' => $_SERVER['REMOTE_ADDR']
							);
							$where_arr1 = array('parent_id' => $product_id);
							$this->SellerProductModel->updateData('products', $where_arr1, $updatedata1);
						}
						// echo "<pre>";print_r($updatedata);
						// print_r($where_arr);
						//die();
						$this->SellerProductModel->updateData('products', $where_arr, $updatedata);
					} else {
						$arrResponse  = array('status' => 400, 'message' => 'Please upload valid csv file');
						echo json_encode($arrResponse);
						exit;
					}
				}
				$arrResponse  = array('status' => 200, 'message' => 'Product Imported Successfully.');
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Please have some data in csv file');
				echo json_encode($arrResponse);
				exit;
			}
		}
	}

	function UpdateProductsInv_type()
	{
		$product_data = array();

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$shop_upload_path = 'shop' . $shop_id;
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}

			$headers = $product_data[0];
			if (isset($product_data) && count($product_data) > 1) {
				foreach ($product_data as $index => $product) {
					if ($index == 0) {
						continue;
					}
					$sku = $product[0];
					// $launch_date=$product[1];
					$product_inv_type = $product[4];
					$whr_pdroduct_arr = array('sku' => $sku, 'remove_flag' => 0);
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', $whr_pdroduct_arr, 'id,product_inv_type');
					// print_r($product_inv_type);
					// echo "<pre>";print_r($PC_Exist);die();
					if (isset($PC_Exist) && $PC_Exist->id != '') {
						/***--------Update product start-------------------------------------------------***/
						$product_id = $PC_Exist->id;
						$updatedata = array(
							'product_inv_type' => ($product_inv_type != '') ? $product_inv_type : $PC_Exist->product_inv_type,
							'updated_at' => time(),
							'ip' => $_SERVER['REMOTE_ADDR']
						);
						$where_arr = array('id' => $product_id);
						// echo "<pre>";print_r($updatedata);
						// print_r($where_arr);
						//die();
						$this->SellerProductModel->updateData('products', $where_arr, $updatedata);
					} else {
						$arrResponse  = array('status' => 400, 'message' => 'Please upload valid csv file');
						echo json_encode($arrResponse);
						exit;
					}
				}
				$arrResponse  = array('status' => 200, 'message' => 'Product Imported Successfully.');
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Please have some data in csv file');
				echo json_encode($arrResponse);
				exit;
			}
		}
	}


	function UpdateProductsWeightLocation()
	{
		// print_r_custom($_FILES);
		$product_data = array();
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		$shop_upload_path = 'shop' . $shop_id;
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');
			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}

			$headers = $product_data[0];

			$sku_not_found = array();
			$sku_list = array();
			if (isset($product_data) && count($product_data) > 1) {
				foreach ($product_data as $index => $product) {
					if ($index == 0) {
						continue;
					}

					$sku = $product[0];
					$whr_pdroduct_arr = array('sku' => $sku, 'remove_flag' => 0);
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', $whr_pdroduct_arr, 'id,price,sku,weight,prod_location,tax_percent,webshop_price');
					if (isset($PC_Exist) && $PC_Exist->id != '') {
						$finalArray = array('updated_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
						if (in_array("weight", $headers)) {
							$key1 = array_search('weight', $headers);
							$weight = $product[$key1];
							if (isset($weight)) {
								$finalArray['weight'] = $weight;
							}
						}
						if (in_array("location", $headers)) {
							$key2 = array_search('location', $headers);
							$prod_location = $product[$key2];
							if (isset($prod_location)) {
								$finalArray['prod_location'] = $prod_location;
							}
						}
						if (in_array("cost_price", $headers)) {
							$key4 = array_search('cost_price', $headers);
							$cost_price = $product[$key4];
						}
						if (in_array("selling_price", $headers)) {
							$key3 = array_search('selling_price', $headers);
							$selling_price = $product[$key3];
						}
						if (in_array("tax_percent", $headers)) {
							$key5 = array_search('tax_percent', $headers);
							$tax_percent = $product[$key5];
						}

						$tax_amount = '';
						$webshop_price = '';
						if (isset($tax_percent) && $tax_percent != '' && isset($selling_price) && $selling_price != '') {
							$tax_amount = (floatval($tax_percent) / 100) * floatval($selling_price);
							$webshop_price = floatval($tax_amount) + floatval($selling_price);
						} elseif (isset($tax_percent) && $tax_percent != '') {
							$tax_amount = (floatval($tax_percent) / 100) * $PC_Exist->price;
							$webshop_price = $tax_amount + $PC_Exist->price;
						} elseif (isset($selling_price) && $selling_price != '') {
							$tax_amount = ($PC_Exist->tax_percent / 100) * floatval($selling_price);
							$webshop_price = $tax_amount + floatval($selling_price);
						}

						if (isset($selling_price) && $selling_price != '') {
							$finalArray['price'] = $selling_price;
						}
						if (isset($cost_price) && $cost_price != '') {
							$finalArray['cost_price'] = $cost_price;
						}
						if (isset($tax_percent) && $tax_percent != '') {
							$finalArray['tax_percent'] = $tax_percent;
						}
						if (isset($webshop_price) && $webshop_price != '') {
							$finalArray['webshop_price'] = $webshop_price;
						}
						if (isset($tax_amount) && $tax_amount != '') {
							$finalArray['tax_amount'] = $tax_amount;
						}

						$product_id = $PC_Exist->id;
						$product_sku = $PC_Exist->sku;
						$where_arr = array('id' => $product_id);
						$this->SellerProductModel->updateData('products', $where_arr, $finalArray);
					} else {
						$arrResponse  = array('status' => 400, 'message' => 'Please upload valid csv file');
						echo json_encode($arrResponse);
						exit;
					}
				}
				$arrResponse  = array('status' => 200, 'message' => 'Product Imported Successfully.');
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Please have some data in csv file');
				echo json_encode($arrResponse);
				exit;
			}
		}
	}

	function UpdateProductsAttributes()
	{
		$product_data = array();

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		$shop_upload_path = 'shop' . $shop_id;
		if (!empty($_FILES['upload_csv_file']['name'])) {
			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}
			$headers = $product_data[0];
			$attr_not_found = array();
			$attr_list = array();

			if (isset($headers) && count($headers) > 0) {
				foreach ($headers as $hk => $columns) {
					if (strpos($columns, 'attribute_') !== false) {
						$attr_code = str_replace('attribute_', '', $columns);
						//$CheckAttr=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>1),'id');
						$CheckAttr = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code($shop_id, 1, $attr_code);
						if (isset($CheckAttr) && $CheckAttr->id != '') {
							$attr_list[$hk] = $attr_code;
						} else {
							$attr_not_found[] = $columns;
						}
					} else {
						continue;
					}
				}
			}

			// echo "<pre>"; print_r($product_data);exit;
			if (isset($product_data) && count($product_data) > 1) {
				foreach ($product_data as $index => $product) {
					if ($index == 0) {
						continue;
					}
					$product_code = $product[0];

					if (in_array("product_name", $headers)) {
						$key1 = array_search('product_name', $headers);
						$product_name = $product[$key1];
					}
					if (in_array("gender", $headers)) {
						$key1 = array_search('gender', $headers);
						$gender = $product[$key1];
					}
					if (in_array("description", $headers)) {
						$key1 = array_search('description', $headers);
						$description = $product[$key1];
					}
					if (in_array("highlights", $headers)) {
						$key1 = array_search('highlights', $headers);
						$highlights = $product[$key1];
					}
					if (in_array("product_reviews_code", $headers)) {
						$key1 = array_search('product_reviews_code', $headers);
						$product_reviews_code = $product[$key1];
					}
					if (in_array("launch_date", $headers)) {
						$key1 = array_search('launch_date', $headers);
						$launch_date = $product[$key1];
						if (isset($launch_date)  && $launch_date != '') {
							if (strpos($launch_date, '/') !== false) {
								$launch_date =  date("d-m-Y", strtotime(str_replace('/', '-', $launch_date)));
							} else {
								$launch_date =  date("d-m-Y", strtotime($launch_date));
							}
						} else {
							$launch_date =  date("d-m-Y", strtotime(date('d-m-Y')));
						}
					}
					if (in_array("customer_types", $headers)) {
						$key1 = array_search('customer_types', $headers);
						$customer_types = $product[$key1];
					}
					if (in_array("estimate_delivery_time", $headers)) {
						$key1 = array_search('estimate_delivery_time', $headers);
						$estimate_delivery_time = $product[$key1];
					}
					if (in_array("product_drop_shipment", $headers)) {
						$key1 = array_search('product_drop_shipment', $headers);
						$product_drop_shipment = $product[$key1];
					}
					if (in_array("meta_title", $headers)) {
						$key1 = array_search('meta_title', $headers);
						$meta_title = $product[$key1];
					}
					if (in_array("meta_keyword", $headers)) {
						$key1 = array_search('meta_keyword', $headers);
						$meta_keyword = $product[$key1];
					}
					if (in_array("meta_description", $headers)) {
						$key1 = array_search('meta_description', $headers);
						$meta_description = $product[$key1];
					}
					if (in_array("search_keywords", $headers)) {
						$key1 = array_search('search_keywords', $headers);
						$search_keywords = $product[$key1];
					}
					if (in_array("promo_reference", $headers)) {
						$key1 = array_search('promo_reference', $headers);
						$promo_reference = $product[$key1];
					}
					if (in_array("can_be_returned", $headers)) {
						$key1 = array_search('can_be_returned', $headers);
						$can_be_returned = $product[$key1];
					}
					if (in_array("coming_soon_flag", $headers)) {
						$key1 = array_search('coming_soon_flag', $headers);
						$coming_product = $product[$key1];
					}
					// if(in_array("weight", $headers))
					// {
					// 	$key1 = array_search('weight', $headers);
					// 	$weight= $product[$key1];
					// }
					// if(in_array("location", $headers))
					// {
					// 	$key1 = array_search('location', $headers);
					// 	$location= $product[$key1];
					// }


					$whr_pdroduct_arr = array('product_code' => $product_code); //,'remove_flag'=>0

					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', $whr_pdroduct_arr, 'id,product_code,product_type,name,description,highlights,gender,product_reviews_code,launch_date,customer_type_ids,estimate_delivery_time,product_drop_shipment,meta_title,meta_keyword,meta_description,search_keywords,promo_reference,can_be_returned,coming_soon_flag');
					// echo "<pre>";print_r($PC_Exist);//die();

					if (isset($PC_Exist) && $PC_Exist->id != '') {
						/***--------Update product start-------------------------------------------------***/
						$product_id = $PC_Exist->id;
						$updatedata = array(

							//'product_code'=>($product_code !='')? $product_code : $PC_Exist->product_code ,
							'name' => isset($product_name) ? $product_name : $PC_Exist->name,
							'description' => isset($description) ? $description : $PC_Exist->description,
							'highlights' => isset($highlights) ? $highlights : $PC_Exist->highlights,
							'gender' => isset($gender) ? $gender : $PC_Exist->gender,
							'product_reviews_code' => isset($product_reviews_code) ? $product_reviews_code : $PC_Exist->product_reviews_code,
							'launch_date' => isset($launch_date) ? strtotime($launch_date) : $PC_Exist->launch_date,
							'customer_type_ids' => (isset($customer_types) && $customer_types != '') ? $customer_types : $PC_Exist->customer_type_ids,
							'estimate_delivery_time' => isset($estimate_delivery_time) ? $estimate_delivery_time : $PC_Exist->estimate_delivery_time,
							'product_drop_shipment' => isset($product_drop_shipment) ? $product_drop_shipment : $PC_Exist->product_drop_shipment,
							'meta_title' => isset($meta_title) ? $meta_title : $PC_Exist->meta_title,
							'meta_keyword' => isset($meta_keyword) ? $meta_keyword : $PC_Exist->meta_keyword,
							'meta_description' => isset($meta_description) ? $meta_description : $PC_Exist->meta_description,
							'search_keywords' => isset($search_keywords) ? $search_keywords : $PC_Exist->search_keywords,
							'promo_reference' => isset($promo_reference) ? $promo_reference : $PC_Exist->promo_reference,
							'can_be_returned' => isset($can_be_returned) ? $can_be_returned : $PC_Exist->can_be_returned,
							'coming_soon_flag' => isset($coming_product) ? $coming_product : $PC_Exist->coming_soon_flag,
							// 'weight'=>isset($weight) ? $weight : '',
							// 'prod_location'=>isset($location) ? $location : '',
							'updated_at' => time(),
							'ip' => $_SERVER['REMOTE_ADDR']
						);
						$where_arr = array('id' => $product_id);
						// echo "<pre>";print_r($updatedata);
						// print_r($where_arr);
						// die();
						$this->SellerProductModel->updateData('products', $where_arr, $updatedata);	//uncomment
						if ($PC_Exist->product_type == 'configurable') {
							$updatedata_conf_simple = array(
								'name' => isset($product_name) ? $product_name : $PC_Exist->name,
								'launch_date' => isset($launch_date) ? strtotime($launch_date) : $PC_Exist->launch_date,
								'can_be_returned' => isset($can_be_returned) ? $can_be_returned : $PC_Exist->can_be_returned,
								'updated_at' => time(),
								'ip' => $_SERVER['REMOTE_ADDR']
							);
							$where_arr_conf_simple = array('parent_id' => $product_id);
							$this->SellerProductModel->updateData('products', $where_arr_conf_simple, $updatedata_conf_simple);	//uncomment
						}

						if (isset($attr_list) && count($attr_list) > 0) {
							$OldAttr = $this->SellerProductModel->getMultiDataById('products_attributes', array('product_id' => $product_id), 'attr_id');
							$prev_attr_ids = array();

							if (isset($OldAttr) && count($OldAttr) > 0) {
								foreach ($OldAttr as $val) {
									$prev_attr_ids[] = $val->attr_id;
								}
							}
							foreach ($attr_list as $row_key => $attr_code) {
								if ($product[$row_key] != '') {
									$attr_value = $product[$row_key];
									//$AttrData=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>1),'id,attr_name,attr_code,attr_properties');

									$AttrData = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code($shop_id, 1, $attr_code);

									if ($AttrData) {
										$attr_id = $AttrData->id;
										$attr_properties = $AttrData->attr_properties;
										if (!empty($attr_value) && $attr_properties == 5) {

											$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_seller($shop_id, $attr_id, $attr_value);

											if (isset($IsOptionExist) && $IsOptionExist->id != '') {

												$option_id =	$IsOptionExist->id;
												$attr_value = $option_id;
											} else {

												$attributesData = array(
													'attr_id'    		=> $attr_id,
													'attr_options_name'	=> $attr_value,
													'created_by' 		=> $fbc_user_id,
													'shop_id'			=> $shop_id,
													'created_by_type' 	=> 1,
													'status'			=> 1,
													'created_at'		=> time(),
													'ip'				=> $_SERVER['REMOTE_ADDR'],
												);

												$option_id =	$this->CommonModel->insertData('eav_attributes_options', $attributesData);
												$attr_value = $option_id;
											}
										} else if (!empty($attr_value) && $attr_properties == 6) {
											if (strpos($attr_value, ',') !== false) {
												$attr_value_arr = explode(',', $attr_value);
											} else {
												$attr_value_arr[] = $attr_value;
											}

											array_filter($attr_value_arr);

											$attr_value_ids = array();

											if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
												foreach ($attr_value_arr as $attr_value_option) {
													$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_seller($shop_id, $attr_id, $attr_value_option);
													if (isset($IsOptionExist) && $IsOptionExist->id != '') {
														$option_id =	$IsOptionExist->id;
														$attr_value_ids[] = $option_id;
													} else {

														$attributesData = array(
															'attr_id'    		=> $attr_id,
															'attr_options_name'	=> $attr_value_option,
															'created_by' 		=> $fbc_user_id,
															'shop_id'			=> $shop_id,
															'created_by_type' 	=> 1,
															'status'			=> 1,
															'created_at'		=> time(),
															'ip'				=> $_SERVER['REMOTE_ADDR'],
														);
														$option_id =	$this->CommonModel->insertData('eav_attributes_options', $attributesData);
														$attr_value_ids[] = $option_id;
													}
												}

												$attr_value = implode(',', $attr_value_ids);
											}
										}

										if ((isset($prev_attr_ids) && count($prev_attr_ids) > 0) &&  in_array($attr_id, $prev_attr_ids)) {
											//echo $attr_id.'=======Up======='.$attr_value.'<br>';
											$attr_update = array('attr_value' => $attr_value);
											$wh_at = array('product_id' => $product_id, 'attr_id' => $attr_id);
											$this->SellerProductModel->updateData('products_attributes', $wh_at, $attr_update);
										} else {
											//echo $attr_id.'=======IN======='.$attr_value.'<br>';
											$attr_insert = array('product_id' => $product_id, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
											$this->SellerProductModel->insertData('products_attributes', $attr_insert);
										}
									}
								} else {

									$attr_value = $product[$row_key];
									//$AttrData=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>1),'id,attr_name,attr_code,attr_properties');

									$AttrData = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code($shop_id, 1, $attr_code);

									if ($AttrData) {
										$attr_id = $AttrData->id;
										if ((isset($prev_attr_ids) && count($prev_attr_ids) > 0) &&  in_array($attr_id, $prev_attr_ids)) {
											//echo $attr_id.'=======Up======='.$attr_value.'<br>';

											$wh_at = array('product_id' => $product_id, 'attr_id' => $attr_id);
											$this->SellerProductModel->deleteDataById('products_attributes', $wh_at);
										}
									}
								}
							}
						}
					} else {
						$arrResponse  = array('status' => 400, 'message' => 'Please upload valid csv file');
						echo json_encode($arrResponse);
						exit;
					}
				}
				$arrResponse  = array('status' => 200, 'message' => 'Product Updated Successfully.');
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Please have some data in csv file');
				echo json_encode($arrResponse);
				exit;
			}
		}
	}

	function importproducts()
	{

			// echo "<hi>";die;
		$product_data = array();
		$LoginID	=	$this->session->userdata('LoginID');
		$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag();
		// $shop_upload_path='shop'.$shop_id;

		$delivery_identifier = 'product_delivery_duration';
		$product_delivery_duration = $this->CommonModel->getSingleShopDataByID('custom_variables as cv', array('identifier' => $delivery_identifier), 'cv.*');
		$product_delivery_duration = (isset($product_delivery_duration) && $product_delivery_duration->value != '') ? $product_delivery_duration->value : '';

		if (!empty($_FILES['upload_csv_file']['name'])) {
			$file_data = fopen($_FILES['upload_csv_file']['tmp_name'], 'r');

			while ($row = fgetcsv($file_data)) {
				$product_data[] = $row;
			}

			$headers = $product_data[0];

			$attr_not_found = array();
			$variant_not_found = array();

			$attr_list = array();
			$var_list = array();

			$master_variant_key = '';
			

			if (isset($headers) && count($headers) > 0) {
				foreach ($headers as $hk => $columns) {
					if (strpos($columns, 'attribute_') !== false) {
						$attr_code = str_replace('attribute_', '', $columns);

						$CheckAttr = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code(1, $attr_code);
						if (isset($CheckAttr) && $CheckAttr->id != '') {
							$attr_list[$hk] = $attr_code;
						} else {
							$attr_not_found[] = $columns;
						}
					} else if (strpos($columns, 'variant_') !== false) {
						$attr_code = str_replace('variant_', '', $columns);

						$CheckAttr = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code(2, $attr_code);
						if (isset($CheckAttr) && $CheckAttr->id != '') {
							$var_list[$hk] = $attr_code;
						} else {
							$variant_not_found[] = $columns;
						}
					} else if ($columns == 'master_variant') {
						$master_variant_key = $hk;
					} else {
						continue;
					}
				}
			}
			// echo "<pre>";
			// print_r($product_data);die;

			if (isset($product_data) && count($product_data) > 1) {


				foreach ($product_data as $index => $product) {

					if ($index == 0) {
						continue;
					}

					if (in_array("product_type", $headers)) {
						$key1 = array_search('product_type', $headers);
						$product_type = $product[$key1];
					}
					if (in_array("product_name", $headers)) {
						$key2 = array_search('product_name', $headers);
						$product_name = $product[$key2];
					}
					if (in_array("product_code", haystack: $headers)) {
						$key3 = array_search('product_code', $headers);
						$product_code = $product[$key3];
					}
					if (in_array("category", $headers)) {
						$key4 = array_search('category', $headers);
						$category_id = $product[$key4];
					}
					if (in_array("sub_category", $headers)) {
						$key5 = array_search('sub_category', $headers);
						$sub_category_id = $product[$key5];
					}
					// if(in_array("tags",$headers)){
					// 	$key6 = array_search('tags', $headers);
					// 	$child_category= $product[$key6];
					// }
					// if(in_array("gender",$headers)){
					// 	$key7 = array_search('gender', $headers);
					// 	$gender= $product[$key7];
					// }
					
					if (in_array("description", $headers)) {
						$key6 = array_search('description', $headers);
						$description = $product[$key6];
					}
					if (in_array("highlights", $headers)) {
						$key7 = array_search('highlights', $headers);
						$highlights = $product[$key7];
					}
					if (in_array("product_reviews_code", $headers)) {
						$key8 = array_search('product_reviews_code', $headers);
						$product_reviews_code = $product[$key8];
					}
					if (in_array("launch_date", $headers)) {
						$key9 = array_search('launch_date', $headers);
						$launch_date = $product[$key9];
					}
					if (in_array("estimate_delivery_time", $headers)) {
						$key10 = array_search('estimate_delivery_time', $headers);
						$estimate_delivery_time = $product[$key10];
					}

					if (in_array("sku", $headers)) {
						$key11 = array_search('sku', $headers);
						$sku = $product[$key11];
					}
					// if (in_array("barcode", $headers)) {
					// 	$key15 = array_search('barcode', $headers);
					// 	$barcode = $product[$key15];
					// }
					if (in_array("inventory", $headers)) {
						$key12 = array_search('inventory', $headers);
						$stock_qty = $product[$key12];
					}

					if (in_array("cost_price", $headers)) {
						$key13 = array_search('cost_price', $headers);
						$cost_price = $product[$key13];
					}
					if (in_array("selling_price", $headers)) {
						$key14 = array_search('selling_price', $headers);
						$price = $product[$key14];
					}
					if (in_array("tax_percent", $headers)) {
						$key15 = array_search('tax_percent', $headers);
						$tax_percent = $product[$key15];
					}
					// if (in_array("images", $headers)) {
					// 	$key20 = array_search('images', $headers);
					// 	$images = $product[$key20];
					// }


					// if (in_array("default_img_name", $headers)) {
					// 	$key21 = array_search('default_img_name', $headers);
					// 	$default_image = $product[$key21];
					// }
					if (in_array("status", $headers)) {
						$key16 = array_search('status', $headers);
						$status = $product[$key16];
					}
					// if (in_array("customer_types", $headers)) {
					// 	$key23 = array_search('customer_types', $headers);
					// 	$customer_types = $product[$key23];
					// }
					if (in_array("meta_title", $headers)) {
						$key17 = array_search('meta_title', $headers);
						$meta_title = $product[$key17];
					}
					if (in_array("meta_keyword", $headers)) {
						$key18 = array_search('meta_keyword', $headers);
						$meta_keyword = $product[$key18];
					}
					if (in_array("meta_description", $headers)) {
						$key19 = array_search('meta_description', $headers);
						$meta_description = $product[$key19];
					}
					if (in_array("search_keywords", $headers)) {
						$key20 = array_search('search_keywords', $headers);
						$search_keywords = $product[$key20];
					}
					if (in_array("promo_reference", $headers)) {
						$key21 = array_search('promo_reference', $headers);
						$promo_reference = $product[$key21];
					}
					if (in_array("publisher_id", $headers)) {
						$key22 = array_search('publisher_id', $headers);
						$publisher_id = $product[$key22];
					}
					if (in_array("pub_com_per_type", $headers)) {
						$key23 = array_search('pub_com_per_type', $headers);
						$pub_com_per_type = $product[$key23];
					}
					if (in_array("pub_com_percent", $headers)) {
						$key24 = array_search('pub_com_percent', $headers);
						$pub_com_percent = $product[$key24];
					}

					if (in_array("attr_code", $headers)) {
						$key25 = array_search('attr_code', $headers);
						$attr_code = $product[$key25];
					}
					if (in_array("attr_value", $headers)) {
						$key26 = array_search('attr_value', $headers);
						$attr_value = $product[$key26];
					}
					// if (in_array("weight", $headers)) {
					// 	$key29 = array_search('weight', $headers);
					// 	$weight = $product[$key29];
					// }
					// if (in_array("gift_id", $headers)) {
					// 	$key98 = array_search('gift_id', $headers);
					// 	$giftid = $product[$key98];
					// }
					// if (in_array("sub_issues", $headers)) {
					// 	$key97 = array_search('sub_issues', $headers);
					// 	$sub_issues = $product[$key97];
					// }



					if (isset($launch_date)  && $launch_date != '') {
						if (strpos($launch_date, '/') !== false) {
							$launch_date =  date("d-m-Y", strtotime(str_replace('/', '-', $launch_date)));
						} else {
							$launch_date =  date("d-m-Y", strtotime($launch_date));
						}
					}

					if (strpos($price, ',') !== false) {
						$price = str_replace(',', '.', $price);
					}

					if (strpos($cost_price, ',') !== false) {
						$cost_price = str_replace(',', '.', $cost_price);
					}

					if (strpos($tax_percent, ',') !== false) {
						$tax_percent = str_replace(',', '.', $tax_percent);
					}

					//added by ketan

					if ($pub_com_per_type == 0) {
						$comm = $this->SellerProductModel->get_publication_by_id($publisher_id);
						$pub_com_percent = $comm;
					} else {
						$pub_com_percent;
					}
					// print_r($pub_com_percent);die;

				
					if ($product_type == 'simple' || $product_type == 'conf-simple') {



						if ($Rounded_price_flag == 1) {

							$RowInfo = $this->SellerProductModel->calculate_webshop_price($price, $tax_percent);

							$tax_amount = $RowInfo['tax_amount'];
							$webshop_price = $RowInfo['webshop_price'];
						} else {


							$webshop_price = $price;
							$tax_amount = 0;
							if ($price > 0 && $tax_percent > 0) {
								$tax_amount = ($tax_percent / 100) * $price;
								$webshop_price = $tax_amount + $price;
							}
						}
					} else {
						$webshop_price = 0;
						$tax_amount = 0;
						$tax_percent = 0;
					}




					$estimate_delivery_time = (isset($estimate_delivery_time) && $estimate_delivery_time != '' && $estimate_delivery_time != '0') ? $estimate_delivery_time : $product_delivery_duration;


					if ($product_type == 'simple' || $product_type == 'configurable') {


						$MainCat = $this->CategoryModel->check_category_exist_by_level(0, $category_id);
						if (isset($MainCat) && $MainCat['id'] != '') {

							$category = $MainCat['id'];
						} else {
							$category = '';
						}

						$SubCat = $this->CategoryModel->check_child_category_exist($category, 1, $sub_category_id);
						if (isset($SubCat) && $SubCat['id'] != '') {
							$sub_category = $SubCat['id'];
						} else {
							$sub_category = '';
						}



						//echo $product_code;die();
						$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('product_code' => $product_code, 'remove_flag' => 0), 'id,name,url_key,base_image');

						//echo $PC_Exist;die();
						if (isset($PC_Exist) && $PC_Exist->id != '') {
							//echo "under";die();
							$old_product_name = $PC_Exist->name;


							$product_id = $PC_Exist->id;
							$url_key = url_title($product_name);
							$url_key = strtolower($url_key);

							$base_image = $PC_Exist->base_image;

							if ($old_product_name != $product_name) {
								$slug = url_title($product_name);
								$url_key = strtolower($slug);


								$slugcount = $this->SellerProductModel->productslugcount($product_name, $product_id);
								if ($slugcount > 0) {

									$url_key = $url_key . "-" . $slugcount;
								} else {
									$url_key = $url_key . "-0";
								}
							} else {
								$url_key = $PC_Exist->url_key;
							}


							/***--------Update product start-------------------------------------------------***/

							$updatedata = array(
								'name' => $product_name,
								'product_code' => $product_code,
								'url_key' => $url_key,
								'meta_title' => $meta_title,
								'meta_keyword' => $meta_keyword,
								'meta_description' => $meta_description,
								'search_keywords' => $search_keywords,
								'promo_reference' => $promo_reference,
								'sku' => $sku,
								'price' => $price,
								'tax_percent' => $tax_percent,
								'tax_amount' => $tax_amount,
								'webshop_price' => $webshop_price,
								'cost_price' => $cost_price,
								'gender' => '',
								'publisher_id' => $publisher_id,
								'pub_com_per_type' => $pub_com_per_type,
								'pub_com_percent' => $pub_com_percent,
								'description' => $description,
								'highlights' => $highlights,
								'product_reviews_code' => $product_reviews_code,
								'launch_date' => isset($launch_date) ? strtotime($launch_date) : '',
								'estimate_delivery_time' => $estimate_delivery_time,
								'status' => $status,
								'updated_at' => time(),
								'ip' => $_SERVER['REMOTE_ADDR']
							);
							// echo "<pre>";print_r($updatedata);die;
							$where_arr = array('id' => $product_id);
							$this->SellerProductModel->updateData('products', $where_arr, $updatedata);

							if ($product_id) {

								if ($product_type == 'simple' || $product_type == 'configurable') {

									if (isset($category) && $category != '') {
										$check_cat_exist = $this->SellerProductModel->getSingleDataByID('products_category', array('category_ids' => $category, 'level' => 0, 'product_id' => $product_id), '');

										if (empty($check_cat_exist)) {

											$root_cat_insert = array('product_id' => $product_id, 'category_ids' => $category, 'level' => 0);
											$this->SellerProductModel->insertData('products_category', $root_cat_insert);

											// $checkbtb_level_zero=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$category,'level'=>0),'id');

											// if(empty($checkbtb_level_zero)){
											// $fbc_cat_insert=array('category_id'=>$category,'level'=>0,'fbc_user_id'=>$fbc_user_id);
											// $this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_cat_insert);
											// }

										}
									}

									if (isset($sub_category) && $sub_category != '') {
										$check_cat_exist = $this->SellerProductModel->getSingleDataByID('products_category', array('category_ids' => $sub_category, 'level' => 1, 'product_id' => $product_id), '');

										if (empty($check_cat_exist)) {
											$sub_cat_insert = array('product_id' => $product_id, 'category_ids' => $sub_category, 'level' => 1);
											$this->SellerProductModel->insertData('products_category', $sub_cat_insert);

											// $checkbtb_level_one=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$sub_category,'level'=>1),'id');
											// if(empty($checkbtb_level_one)){
											// 	$fbc_subcat_insert=array('category_id'=>$sub_category,'level'=>1,'fbc_user_id'=>$fbc_user_id);
											// 	$this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_subcat_insert);
											// }
										}
									}



									/********************Tags category insert*************************************************/


									if (isset($child_category) && $child_category != '') {
										$selected_tags_arr = array();
										$OldTags = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 2), 'id,category_ids');
										if (isset($OldTags) && count($OldTags) > 0) {
											foreach ($OldTags as $val) {
												$CatInfo = $this->CategoryModel->check_category_exist_by_cat_id(2, $val->category_ids);
												if (isset($CatInfo) && $CatInfo['id'] != '') {
													$selected_tags_arr[] = $CatInfo['cat_name'];
												}
											}
										} else {
											$selected_tags_arr = array();
										}


										$child_cat_id_str = '';
										$child_cat_id_arr = array();
										if (strpos($child_category, ',') !== false) {
											$child_category_arr = explode(',', $child_category);
										} else {
											$child_category_arr[] = $child_category;
										}

										if (count($child_category_arr) > 0) {

											$selected_tags_arr = array_values(array_filter($selected_tags_arr));

											$child_category_arr = array_values(array_filter($child_category_arr));

											$result = array_diff_assoc($selected_tags_arr, $child_category_arr);

											if (count($result) > 0) {
												foreach ($result as $val) {

													$CatInfo = $this->CategoryModel->check_category_exist_by_level(2, $val);
													if (isset($CatInfo) && $CatInfo['id'] != '') {
														$cdeletedata = array('category_ids' => $CatInfo['id'], 'level' => 2, 'product_id' => $product_id);

														$this->SellerProductModel->deleteDataById('products_category', $cdeletedata);
													}
												}
											}



											$child_category_arr = array_values(array_filter($child_category_arr));


											if (isset($child_category_arr) && count($child_category_arr) > 0) {

												foreach ($child_category_arr as $cat_name) {
													$IsChildExist = $this->CategoryModel->check_child_category_exist($sub_category, 2, $cat_name);
													if (isset($IsChildExist) && $IsChildExist['id'] != '') {
														$cid = $IsChildExist['id'];
														$child_cat_id_arr[] = $cid;

														$CategoryAssigned = $this->SellerProductModel->getMultiDataById('products_category', array('category_ids' => $cid, 'product_id' => $product_id, 'level' => 2), 'id,category_ids');

														if (empty($CategoryAssigned)) {

															$child_cat_insert = array('product_id' => $product_id, 'category_ids' => $cid, 'level' => 2);
															$this->SellerProductModel->insertData('products_category', $child_cat_insert);
														}
													} else {
														$cc_slug = url_title($cat_name);
														$cc_slug = strtolower($cc_slug);
														$child_cat_insert = array('cat_name' => $cat_name, 'slug' => $cc_slug, 'parent_id' => $sub_category, 'main_parent_id' => $category, 'cat_level' => 2, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
														$this->db->insert('category', $child_cat_insert);
														$cid = $this->db->insert_id();
														$child_cat_id_arr[] = $cid;

														// $checkbtb_level_two=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cid,'level'=>2),'id');

														// if(empty($checkbtb_level_two)){

														// 	$fbc_childcat_insert=array('category_id'=>$cid,'level'=>2,'fbc_user_id'=>$fbc_user_id);
														// 	$this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_childcat_insert);
														// }

														//$child_cat_insert=array('product_id'=>$product_id,'category_ids'=>$cid,'level'=>2);
														//$this->SellerProductModel->insertData('products_category',$child_cat_insert);

													}
												}
											}
										}
									}

									/***************************Tags End*************************************/
								}

								/*--------------------Main product created-------------------------------------------------------*/

								if ($product_type == 'simple') {

									$OldProductQtyData = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $product_id), 'qty,available_qty');

									if ($OldProductQtyData->qty == $stock_qty) {
									} else {

										if ($OldProductQtyData->available_qty == $OldProductQtyData->qty) {
											$new_available_qty = $stock_qty;
										} else {

											if (isset($OldProductQtyData) && $OldProductQtyData->available_qty > 0) {
												$old_available_qty = $OldProductQtyData->available_qty;

												$old_ordered_qty = $OldProductQtyData->qty - $old_available_qty;
											} else {
												$old_ordered_qty = 0;
											}

											if ($stock_qty == '' || $stock_qty == 0) {
												$new_available_qty = 0;
											} else if ($stock_qty > 0 && $stock_qty > $OldProductQtyData->qty) {
												$new_available_qty = $stock_qty - $old_ordered_qty;
											} else {
												$new_available_qty = $stock_qty - $old_ordered_qty;
												$new_available_qty = ($new_available_qty <= 0) ? 0 : $new_available_qty;
											}
										}


										$stock_update = array('qty' => $stock_qty, 'available_qty' => $new_available_qty);
										$whr_qty_arr = array('product_id' => $product_id);
										$this->SellerProductModel->updateData('products_inventory', $whr_qty_arr, $stock_update);
									}
								}
							}

							if (isset($attr_list) && count($attr_list) > 0) {




								$OldAttr = $this->SellerProductModel->getMultiDataById('products_attributes', array('product_id' => $product_id), 'attr_id');
								$prev_attr_ids = array();

								if (isset($OldAttr) && count($OldAttr) > 0) {
									foreach ($OldAttr as $val) {
										$prev_attr_ids[] = $val->attr_id;
									}
								}

								/*

									$new_attr_ids_arr=array();

									$new_attr_ids=$this->EavAttributesModel->getAttrDataByAttrCode($attr_list,$shop_id);
									if(isset($new_attr_ids) && count($new_attr_ids)>0){
										foreach($new_attr_ids as $attr){
											$new_attr_ids_arr[]=$attr['id'];
										}
									}
									$new_attr_ids_arr = array_values(array_filter($new_attr_ids_arr));
									$result=array_diff_assoc($prev_attr_ids,$new_attr_ids_arr);
									if(count($result)>0){
										foreach($result as $val){
											$at_whr = array('product_id'=>$product_id,'attr_id'=>$val);
											$this->SellerProductModel->deleteDataById('products_attributes',$at_whr);
										}
									}
									*/

								foreach ($attr_list as $row_key => $attr_code) {

									if ($product[$row_key] != '') {
										$attr_value = $product[$row_key];
										//$AttrData=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>1),'id,attr_name,attr_code,attr_properties');

										$AttrData = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code(1, $attr_code);

										if ($AttrData) {
											$attr_id = $AttrData->id;
											$attr_properties = $AttrData->attr_properties;
											if (!empty($attr_value) && $attr_properties == 5) {

												$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_seller($attr_id, $attr_value);
												if (isset($IsOptionExist) && $IsOptionExist->id != '') {
													$option_id =	$IsOptionExist->id;
													$attr_value = $option_id;
												} else {

													if (isset($IsOptionExist) && $IsOptionExist->id != '') {
														$attributesData = array(
															'attr_id'    		=> $attr_id,
															'attr_options_name'	=> $attr_value,
															'created_by' 		=> $fbc_user_id,
															//'shop_id'			=> $shop_id,
															//	'created_by_type' 	=> 1,
															'status'			=> 1,
															'created_at'		=> time(),
															'ip'				=> $_SERVER['REMOTE_ADDR'],
														);
														$option_id =	$this->db->insert('eav_attributes_options', $attributesData);
														$attr_value = $option_id;
													}
												}
											} else if (!empty($attr_value) && $attr_properties == 6) {

												if (strpos($attr_value, ',') !== false) {
													$attr_value_arr = explode(',', $attr_value);
												} else {
													$attr_value_arr[] = $attr_value;
												}

												array_filter($attr_value_arr);

												$attr_value_ids = array();

												if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
													foreach ($attr_value_arr as $attr_value_option) {
														$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_seller($shop_id, $attr_id, $attr_value_option);
														if (isset($IsOptionExist) && $IsOptionExist->id != '') {
															$option_id =	$IsOptionExist->id;
															$attr_value_ids[] = $option_id;
														} else {

															$attributesData = array(
																'attr_id'    		=> $attr_id,
																'attr_options_name'	=> $attr_value_option,
																//'created_by' 		=> $fbc_user_id,
																//'shop_id'			=> $shop_id,
																'created_by_type' 	=> 1,
																'status'			=> 1,
																'created_at'		=> time(),
																'ip'				=> $_SERVER['REMOTE_ADDR'],
															);
															$option_id =	$this->CommonModel->insertData('eav_attributes_options', $attributesData);
															$attr_value_ids[] = $option_id;
														}
													}

													$attr_value = implode(',', $attr_value_ids);
												}
											}



											if ((isset($prev_attr_ids) && count($prev_attr_ids) > 0) &&  in_array($attr_id, $prev_attr_ids)) {
												//echo $attr_id.'=======Up======='.$attr_value.'<br>';
												$attr_update = array('attr_value' => $attr_value);
												$wh_at = array('product_id' => $product_id, 'attr_id' => $attr_id);
												$this->SellerProductModel->updateData('products_attributes', $wh_at, $attr_update);
											} else {
												//echo $attr_id.'=======IN======='.$attr_value.'<br>';
												$attr_insert = array('product_id' => $product_id, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
												$this->SellerProductModel->insertData('products_attributes', $attr_insert);
											}
										}
									} else {

										$attr_value = $product[$row_key];
										//$AttrData=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>1),'id,attr_name,attr_code,attr_properties');

										$AttrData = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code(1, $attr_code);

										if ($AttrData) {
											$attr_id = $AttrData->id;
											if ((isset($prev_attr_ids) && count($prev_attr_ids) > 0) &&  in_array($attr_id, $prev_attr_ids)) {
												//echo $attr_id.'=======Up======='.$attr_value.'<br>';

												$wh_at = array('product_id' => $product_id, 'attr_id' => $attr_id);
												$this->SellerProductModel->deleteDataById('products_attributes', $wh_at);
											}
										}
									}
								}
							}



							/*-----------------------upload media imgs---------------------------------------*/

							if (isset($images) && $images != '') {
								$images = explode(',', $images);
								array_filter(array_unique($images));

								if (isset($images) && count($images) > 0) {

									$img_count = 0;

									/* Store the path of source file */
									$importfilePath = $_SERVER['DOCUMENT_ROOT'] . '/' . IMPORTS_PATH;

									/* Store the path of destination file */
									$destinationFilePath = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'original/';

									foreach ($images as $ig => $orig_name) {

										if (filter_var($orig_name, FILTER_VALIDATE_URL) !== false) {
											$image_name =  basename($orig_name);

											$img_ext_arr = explode('.', $image_name);
											$extension = end($img_ext_arr);

											$new_file_name = $default_image;

											$thumb_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'thumb/';
											$medium_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'medium/';
											$large_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'large/';
											$original_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'original/';



											$img_data_from_url = file_get_contents_curl($orig_name);
											file_put_contents($original_folder . $new_file_name, $img_data_from_url);

											$this->makeThumbnail($new_file_name, $original_folder, $thumb_folder, $width = '300', $height = '300');
											$this->makeThumbnail($new_file_name, $original_folder, $medium_folder, $width = '500', $height = '500');
											$this->makeThumbnail($new_file_name, $original_folder, $large_folder, $width = '800', $height = '800');

											if ($default_image == $image_name) {
												$is_default = 1;
												$is_base_image = 1;
												$base_image = $new_file_name;
											} else {
												$is_default = 0;
												$is_base_image = 0;
											}

											$media_insert = array('product_id' => $product_id, 'image' => $new_file_name, 'image_title' => $image_name, 'image_position' => $img_count, 'is_default' => $is_default, 'is_base_image' => $is_base_image);
											$this->SellerProductModel->insertData('products_media_gallery', $media_insert);
											$img_count++;
										} else {

											$img_ext_arr = explode('.', $orig_name);
											$extension = end($img_ext_arr);

											$img_exist = $this->SellerProductModel->getSingleDataByID('products_media_gallery', array('product_id' => $product_id, 'image_title' => $orig_name), 'id,image_title');
											if (isset($img_exist) && $img_exist->id != '') {
												//continue;

											} else {

												$new_file_name = $default_image;
												$source_file = $importfilePath . $orig_name;
												$destination_file = $destinationFilePath . $new_file_name;

												if ($default_image == $orig_name) {
													$is_default = 1;
													$is_base_image = 1;
												} else {
													$is_default = 0;
													$is_base_image = 0;
												}


												/* Move File from images to copyImages folder ---------------- comment for demo
															if( !rename($source_file, $destination_file) ) {
																//echo "File can't be moved!";
															}
															else {
																//echo "File has been moved!";

																$thumb_folder=SIS_SERVER_PATH.'/'.$shop_upload_path.PRODUCT_GALLERY_PATH.'thumb/';
																$original_folder=SIS_SERVER_PATH.'/'.$shop_upload_path.PRODUCT_GALLERY_PATH.'original/';
																$this->makeThumbnail($new_file_name,$original_folder,$thumb_folder,$width='300',$height='300');


																$media_insert=array('product_id'=>$product_id,'image'=>$new_file_name,'image_title'=>$orig_name,'image_position'=>$img_count,'is_default'=>$is_default,'is_base_image'=>$is_base_image);
																$this->SellerProductModel->insertData('products_media_gallery',$media_insert);
																$img_count++;
															}
															*/
											}
										}
									}
								}

								if ($base_image != '') {
									$product_img = array('base_image' => $base_image);
									$where_arr = array('id' => $product_id);
									$this->SellerProductModel->updateData('products', $where_arr, $product_img);

									//if((array_sum($_FILES['gallery_image']['error']) > 0) || (array_sum($_FILES['variant_image']['error']) > 0)){

									$img_row = $this->SellerProductModel->getSingleDataByID('products_media_gallery', array('product_id' => $product_id, 'image' => $base_image), '');

									if (isset($img_row) && $img_row->id != '') {

										$img_id = $img_row->id;

										$product_img_deff = array('is_default' => 0, 'is_base_image' => 0);
										$where_arr_deff = array('product_id' => $product_id);
										$this->SellerProductModel->updateData('products_media_gallery', $where_arr_deff, $product_img_deff);

										$product_img_def = array('is_default' => 1, 'is_base_image' => 1);
										$where_arr_def = array('id' => $img_id);
										$this->SellerProductModel->updateData('products_media_gallery', $where_arr_def, $product_img_def);
									}

									//}

								}
							}

							/***---------Update Product End------------------------------------------------***/
						} else {


							// echo 'insert'; exit();
							/***---------Insert Product start------------------------------------------------***/
							$url_key = url_title($product_name);
							$url_key = strtolower($url_key);
							$slugcount = $this->SellerProductModel->productslugcount($product_name);
							if ($slugcount > 0) {
								$slugcount = $slugcount + 1;
								$url_key = $url_key . "-" . $slugcount;
							} else {
								$url_key = $url_key . "-0";
							}

							$insertdata = array(
								'name' => $product_name,
								'product_code' => $product_code,
								'url_key' => $url_key,

								'meta_title' => $meta_title,
								'meta_keyword' => $meta_keyword,
								'meta_description' => $meta_description,
								'search_keywords' => $search_keywords,
								'promo_reference' => $promo_reference,
								'sku' => $sku,
								'price' => $price,
								'tax_percent' => $tax_percent,
								'tax_amount' => $tax_amount,
								'webshop_price' => $webshop_price,
								'cost_price' => $cost_price,
								'gender' => '',
								'publisher_id' => $publisher_id,
								'pub_com_per_type' => $pub_com_per_type,
								'pub_com_percent' => $pub_com_percent,
								'base_image' => '',
								'description' => $description,
								'highlights' => $highlights,
								'product_reviews_code' => $product_reviews_code,
								'launch_date' => strtotime($launch_date),
								'estimate_delivery_time' => $estimate_delivery_time,
								//'product_return_time'=>$product_return_time,
								'product_type' => $product_type,
								'product_inv_type' => 'buy',
								'status' => $status,
								'created_at' => time(),
								'ip' => $_SERVER['REMOTE_ADDR']
							);
							$product_id = $this->SellerProductModel->insertData('products', $insertdata);



							if ($product_id) {

								// $product_log_insert=array('product_id'=>$product_id,'fbc_user_id'=>$fbc_user_id,'shop_id'=>$shop_id,'created_at'=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
								// $this->db->insert('products_logs',$product_log_insert);
								// $this->db->reset_query();

								if ($product_type == 'simple') {
									$stock_insert = array('product_id' => $product_id, 'qty' => $stock_qty, 'available_qty' => $stock_qty, 'min_qty' => 0, 'is_in_stock' => 1);
									$this->SellerProductModel->insertData('products_inventory', $stock_insert);
								}

								$root_cat_insert = array('product_id' => $product_id, 'category_ids' => $category, 'level' => 0);
								$this->SellerProductModel->insertData('products_category', $root_cat_insert);

								if (isset($sub_category) && $sub_category != '') {
									$sub_cat_insert = array('product_id' => $product_id, 'category_ids' => $sub_category, 'level' => 1);
									$this->SellerProductModel->insertData('products_category', $sub_cat_insert);
								}


								// $checkbtb_level_zero=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$category,'level'=>0),'id');

								// if(empty($checkbtb_level_zero)){
								// $fbc_cat_insert=array('category_id'=>$category,'level'=>0,'fbc_user_id'=>$fbc_user_id);
								// $this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_cat_insert);
								// }

								// $checkbtb_level_one=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$sub_category,'level'=>1),'id');
								// if(empty($checkbtb_level_one)){
								// $fbc_subcat_insert=array('category_id'=>$sub_category,'level'=>1,'fbc_user_id'=>$fbc_user_id);
								// $this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_subcat_insert);
								// }

								/***---------------------------------Tags ------------------------------------------------****/
								if (isset($child_category) && $child_category != '') {


									$child_cat_id_str = '';
									$child_cat_id_arr = array();
									if (strpos($child_category, ',') !== false) {
										$child_category_arr = explode(',', $child_category);
									} else {
										$child_category_arr[] = $child_category;
									}

									if (count($child_category_arr) > 0) {


										$child_category_arr = array_values(array_filter($child_category_arr));


										if (isset($child_category_arr) && count($child_category_arr) > 0) {

											foreach ($child_category_arr as $cat_name) {
												$IsChildExist = $this->CategoryModel->check_child_category_exist($sub_category, 2, $cat_name);
												if (isset($IsChildExist) && $IsChildExist['id'] != '') {
													$cid = $IsChildExist['id'];
													$child_cat_id_arr[] = $cid;

													$CategoryAssigned = $this->SellerProductModel->getMultiDataById('products_category', array('category_ids' => $cid, 'product_id' => $product_id, 'level' => 2), 'id,category_ids');

													if (empty($CategoryAssigned)) {

														$child_cat_insert = array('product_id' => $product_id, 'category_ids' => $cid, 'level' => 2);
														$this->SellerProductModel->insertData('products_category', $child_cat_insert);
													}
												} else {
													$cc_slug = url_title($cat_name);
													$cc_slug = strtolower($cc_slug);
													$child_cat_insert = array('cat_name' => $cat_name, 'slug' => $cc_slug, 'parent_id' => $sub_category, 'main_parent_id' => $category, 'cat_level' => 2, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
													$this->db->insert('category', $child_cat_insert);
													$cid = $this->db->insert_id();
													$child_cat_id_arr[] = $cid;

													// $checkbtb_level_two=$this->SellerProductModel->getSingleDataByID('fbc_users_category_b2b',array('category_id'=>$cid,'level'=>2),'id');

													// if(empty($checkbtb_level_two)){

													// 	$fbc_childcat_insert=array('category_id'=>$cid,'level'=>2,'fbc_user_id'=>$fbc_user_id);
													// 	$this->SellerProductModel->insertData('fbc_users_category_b2b',$fbc_childcat_insert);
													// }

													//	$child_cat_insert=array('product_id'=>$product_id,'category_ids'=>$cid,'level'=>2);
													//$this->SellerProductModel->insertData('products_category',$child_cat_insert);

												}
											}
										}
									}
								}

								/*-----------------------upload media imgs---------------------------------------*/


								if ($product_type == 'configurable' && $master_variant_key != '') {

									$added_variant_arr = array();
									$added_variant = $product[$master_variant_key];

									if (strpos($added_variant, ',') !== false) {
										$added_variant_arr = explode(',', $added_variant);
									} else {
										$added_variant_arr[] = $added_variant;
									}

									array_filter($added_variant_arr);

									if (isset($added_variant_arr) && count($added_variant_arr) > 0) {
										$VariantList = $this->EavAttributesModel->getVariantDataByAttrCode($added_variant_arr);

										if (isset($VariantList) && count($VariantList) > 0) {
											foreach ($VariantList as $attr) {
												// if(in_array("search_keywords",$headers)){
												// 	$key99 = array_search('search_keywords', $headers);
												// 	$variant_key= $product[$key99];
												// }
												$attr_id = $attr['id'];
												$pvm_insert = array('product_id' => $product_id, 'attr_id' => $attr_id, 'position' => 0);
												$this->SellerProductModel->insertData('products_variants_master', $pvm_insert);
											}
										}
									}
								}



								if (isset($attr_list) && count($attr_list) > 0) {
									foreach ($attr_list as $row_key => $attr_code) {
										if ($product[$row_key] != '') {
											$attr_value = $product[$row_key];
											//$AttrData=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>1),'id,attr_name,attr_code,attr_properties');

											$AttrData = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code(1, $attr_code);

											if ($AttrData) {
												//echo "attdata";die();
												$attr_id = $AttrData->id;
												$attr_properties = $AttrData->attr_properties;
												//echo $attr_properties;die();


												if (!empty($attr_value) && $attr_properties == 5) {

													$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_seller($attr_id, $attr_value);

													if (isset($IsOptionExist) && $IsOptionExist->id != '') {
														$option_id =	$IsOptionExist->id;
														$attr_value = $option_id;
													} else {

														$attributesData = array(
															'attr_id'    		=> $attr_id,
															'attr_options_name'	=> $attr_value,
															'created_by' 		=> $fbc_user_id,
															//'shop_id'			=> $shop_id,
															//'created_by_type' 	=> 1,
															'status'			=> 1,
															'created_at'		=> time(),
															'ip'				=> $_SERVER['REMOTE_ADDR'],
														);

														$option_id =	$this->CommonModel->insertData('eav_attributes_options', $attributesData);
														$attr_value = $option_id;
													}
												} else if (!empty($attr_value) && $attr_properties == 6) {

													if (strpos($attr_value, ',') !== false) {
														$attr_value_arr = explode(',', $attr_value);
													} else {
														$attr_value_arr[] = $attr_value;
													}

													array_filter($attr_value_arr);

													$attr_value_ids = array();

													if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
														foreach ($attr_value_arr as $attr_value_option) {
															$IsOptionExist = $this->EavAttributesModel->check_attributes_options_exist_by_seller($attr_id, $attr_value_option);
															if (isset($IsOptionExist) && $IsOptionExist->id != '') {
																$option_id =	$IsOptionExist->id;
																$attr_value_ids[] = $option_id;
															} else {

																$attributesData = array(
																	'attr_id'    		=> $attr_id,
																	'attr_options_name'	=> $attr_value_option,
																	'created_by' 		=> $fbc_user_id,
																	//'shop_id'			=> $shop_id,
																	//	'created_by_type' 	=> 1,
																	'status'			=> 1,
																	'created_at'		=> time(),
																	'ip'				=> $_SERVER['REMOTE_ADDR'],
																);
																$option_id =	$this->CommonModel->insertData('eav_attributes_options', $attributesData);
																$attr_value_ids[] = $option_id;
															}
														}

														$attr_value = implode(',', $attr_value_ids);
													}
												}

												$attr_insert = array('product_id' => $product_id, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
												$this->SellerProductModel->insertData('products_attributes', $attr_insert);
											}
										}
									}
								}


								$base_image = '';
								if (isset($images) && $images != '') {



									$images = explode(',', $images);
									array_filter(array_unique($images));

									if (isset($images) && count($images) > 0) {

										$img_count = 0;

										/* Store the path of source file */
										$importfilePath = $_SERVER['DOCUMENT_ROOT'] . '/' . IMPORTS_PATH;

										/* Store the path of destination file */
										$destinationFilePath = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'original/';

										foreach ($images as $ig => $orig_name) {


											if (filter_var($orig_name, FILTER_VALIDATE_URL) !== false) {


												$image_name =  basename($orig_name);


												$img_ext_arr = explode('.', $image_name);
												$extension = end($img_ext_arr);

												$new_file_name = $default_image;

												$thumb_folder = SIS_SERVER_PATH . '/' . PRODUCT_GALLERY_PATH . 'thumb/';
												$medium_folder = SIS_SERVER_PATH . '/' . PRODUCT_GALLERY_PATH . 'medium/';
												$large_folder = SIS_SERVER_PATH . '/' . PRODUCT_GALLERY_PATH . 'large/';
												$original_folder = SIS_SERVER_PATH . '/' . PRODUCT_GALLERY_PATH . 'original/';



												$img_data_from_url = file_get_contents_curl($orig_name);
												file_put_contents($original_folder . $new_file_name, $img_data_from_url);

												$this->makeThumbnail($new_file_name, $original_folder, $thumb_folder, $width = '300', $height = '300');
												$this->makeThumbnail($new_file_name, $original_folder, $medium_folder, $width = '500', $height = '500');
												$this->makeThumbnail($new_file_name, $original_folder, $large_folder, $width = '800', $height = '800');

												if ($default_image == $image_name) {
													$is_default = 1;
													$is_base_image = 1;
													$base_image = $new_file_name;
												} else {
													$is_default = 0;
													$is_base_image = 0;
												}

												$media_insert = array('product_id' => $product_id, 'image' => $new_file_name, 'image_title' => $image_name, 'image_position' => $img_count, 'is_default' => $is_default, 'is_base_image' => $is_base_image);
												$this->SellerProductModel->insertData('products_media_gallery', $media_insert);


												$img_count++;
											} else {

												$img_ext_arr = explode('.', $orig_name);
												$extension = end($img_ext_arr);


												$new_file_name = $default_image;
												$source_file = $importfilePath . $orig_name;
												$destination_file = $destinationFilePath . $new_file_name;

												if ($default_image == $orig_name) {
													$is_default = 1;
													$is_base_image = 1;
													//$base_image=$new_file_name;
												} else {
													$is_default = 0;
													$is_base_image = 0;
												}

												$image_name =  basename($orig_name);


												$img_ext_arr = explode('.', $image_name);
												$extension = end($img_ext_arr);

												$new_file_name = $default_image;

												$thumb_folder = SIS_SERVER_PATH . PRODUCT_GALLERY_PATH . 'thumb/';
												$medium_folder = SIS_SERVER_PATH . PRODUCT_GALLERY_PATH . 'medium/';
												$large_folder = SIS_SERVER_PATH . PRODUCT_GALLERY_PATH . 'large/';
												$original_folder = SIS_SERVER_PATH . PRODUCT_GALLERY_PATH . 'original/';



												$img_data_from_url = file_get_contents_curl($orig_name);
												//file_put_contents( $original_folder.$new_file_name, $img_data_from_url );

												$this->makeThumbnail($new_file_name, $original_folder, $thumb_folder, $width = '300', $height = '300');
												$this->makeThumbnail($new_file_name, $original_folder, $medium_folder, $width = '500', $height = '500');
												$this->makeThumbnail($new_file_name, $original_folder, $large_folder, $width = '800', $height = '800');

												if ($default_image == $image_name) {
													$is_default = 1;
													$is_base_image = 1;
													$base_image = $new_file_name;
												} else {
													$is_default = 0;
													$is_base_image = 0;
												}

												$media_insert = array('product_id' => $product_id, 'image' => $new_file_name, 'image_title' => $image_name, 'image_position' => $img_count, 'is_default' => $is_default, 'is_base_image' => $is_base_image);
												$this->SellerProductModel->insertData('products_media_gallery', $media_insert);


												$img_count++;

												//Move File from images to copyImages folder ---------------- comment for demo
												// if( !rename($source_file, $destination_file) ) {

												// 	echo "File can't be moved!";
												// 	die();
												// 	//continue;
												// }
												// else {
												// 	//echo "File has been moved!";

												// 	$thumb_folder=SIS_SERVER_PATH.'/'.$shop_upload_path.PRODUCT_GALLERY_PATH.'thumb/';
												// 	$original_folder=SIS_SERVER_PATH.'/'.$shop_upload_path.PRODUCT_GALLERY_PATH.'original/';
												// 	$this->makeThumbnail($new_file_name,$original_folder,$thumb_folder,$width='300',$height='300');


												// 	$media_insert=array('product_id'=>$product_id,'image'=>$new_file_name,'image_title'=>$orig_name,'image_position'=>$img_count,'is_default'=>$is_default,'is_base_image'=>$is_base_image);
												// 	$this->SellerProductModel->insertData('products_media_gallery',$media_insert);
												// 	$img_count++;
												// }


											}
										}
									}
								}


								// if ($product_type = 'simple') {


								// 	$barcode_update = array('barcode' => $barcode, 'updated_at' => time());
								// 	$where_arr = array('id' => $product_id);
								// }



								if ($base_image != '') {
									$product_img = array('base_image' => $base_image);
									$where_arr = array('id' => $product_id);
									$this->SellerProductModel->updateData('products', $where_arr, $product_img);

									//if((array_sum($_FILES['gallery_image']['error']) > 0) || (array_sum($_FILES['variant_image']['error']) > 0)){

									$img_row = $this->SellerProductModel->getSingleDataByID('products_media_gallery', array('product_id' => $product_id, 'image' => $base_image), '');

									if (isset($img_row) && $img_row->id != '') {

										$img_id = $img_row->id;

										$product_img_deff = array('is_default' => 0, 'is_base_image' => 0);
										$where_arr_deff = array('product_id' => $product_id);
										$this->SellerProductModel->updateData('products_media_gallery', $where_arr_deff, $product_img_deff);

										$product_img_def = array('is_default' => 1, 'is_base_image' => 1);
										$where_arr_def = array('id' => $img_id);
										$this->SellerProductModel->updateData('products_media_gallery', $where_arr_def, $product_img_def);
									}

									//}

								}
							}
							//product id end


							/***---------Insert Product End------------------------------------------------***/
						}
					} else if ($product_type == 'conf-simple') {

						$PC_Exist = $this->SellerProductModel->getSingleDataByID('products', array('product_code' => $product_code, 'remove_flag' => 0), 'id,name,base_image');

						$simple_sku = $sku;
						$simple_stock = $stock_qty;
						$simple_cost_price = $cost_price;
						$simple_price = $price;
						// $simple_barcode = $barcode;
						$simple_tax_percent = $tax_percent;

						$simple_tax_amount = $tax_amount;
						$simple_webshop_price = $webshop_price;

						// $simple_weight = $weight;



						if (isset($PC_Exist) && $PC_Exist->id != '') {

							$product_id = $PC_Exist->id;

							$PC_Child_Exist = $this->SellerProductModel->getSingleDataByID('products', array('parent_id' => $product_id, 'sku' => $simple_sku), 'id,name,barcode');

							if (isset($PC_Child_Exist)  && $PC_Child_Exist->id != '') {

								/*****-----------------Conf-Simple proudct Update start--------------------------------------------****/
								$old_simple_barcode = $PC_Child_Exist->barcode;
								$simple_product_id = $PC_Child_Exist->id;

								$updatesimpleproduct = array(
									'name' => $product_name,
									'parent_id' => $product_id,
									'gift_id' => $giftid,
									'sub_issues' => $sub_issues,
									'sku' => $simple_sku,
									// 'weight' => $simple_weight,
									'cost_price' => $simple_cost_price,
									'price' => $simple_price,
									'tax_amount' => $simple_tax_amount,
									'webshop_price' => $simple_webshop_price,
									'tax_percent' => $simple_tax_percent,
									// 'barcode' => $simple_barcode,
									'launch_date' => isset($launch_date) ? strtotime($launch_date) : '',
									'updated_at' => time(),
									'ip' => $_SERVER['REMOTE_ADDR']
								);
								$conf_where = array('id' => $simple_product_id);

								$this->SellerProductModel->updateData('products', $conf_where, $updatesimpleproduct);


								$OldProductQtyData = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $simple_product_id), 'qty,available_qty');

								if ($OldProductQtyData->qty == $simple_stock) {
								} else {
									if ($OldProductQtyData->available_qty == $OldProductQtyData->qty) {
										$new_available_qty = $simple_stock;
									} else {

										if (isset($OldProductQtyData) && $OldProductQtyData->available_qty > 0) {
											$old_available_qty = $OldProductQtyData->available_qty;

											$old_ordered_qty = $OldProductQtyData->qty - $old_available_qty;
										} else {
											$old_ordered_qty = 0;
										}


										if ($simple_stock == '' || $simple_stock == 0) {
											$new_available_qty = 0;
										} else if ($simple_stock > 0 && $simple_stock > $OldProductQtyData->qty) {
											$new_available_qty = $simple_stock - $old_ordered_qty;
										} else {
											$new_available_qty = $simple_stock - $old_ordered_qty;
											$new_available_qty = ($new_available_qty <= 0) ? 0 : $new_available_qty;
										}
									}


									$stock_update = array('qty' => $simple_stock, 'available_qty' => $new_available_qty);
									$whr_qty_arr = array('product_id' => $simple_product_id);
									$this->SellerProductModel->updateData('products_inventory', $whr_qty_arr, $stock_update);
								}


								/*------------------Update variant start------------------------------------------*/
								if (isset($var_list) && count($var_list) > 0) {
									foreach ($var_list as $row_key => $attr_code) {
										if ($product[$row_key] != '') {
											$attr_value = $product[$row_key];
											//$VarData=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>2),'id,attr_name,attr_code,attr_properties');

											$VarData = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code(2, $attr_code);

											if ($VarData) {
												$attr_id = $VarData->id;
												$attr_properties = $VarData->attr_properties;
												//$variation_selected=$attr_value;

												$OptionSelected = $this->SellerProductModel->getSingleDataByID('products_variants', array('product_id' => $simple_product_id, 'parent_id' => $product_id, 'attr_id' => $attr_id), 'id,attr_value');

												//$OptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_id'=>$attr_id,'attr_options_name'=>$attr_value),'id,attr_id,attr_options_name');

												$OptionData = $this->EavAttributesModel->check_attributes_options_exist_by_seller($attr_id, $attr_value);

												if (isset($OptionData) && $OptionData->attr_options_name != '') {
													$variation_selected = $OptionData->id;
												} else {

													$option_insert = array(
														'attr_id'    		=> $attr_id,
														'attr_options_name'	=> $attr_value,
														'created_by' 		=> $fbc_user_id,
														//'shop_id'			=> $shop_id,
														//'created_by_type' 	=> 1,
														'status'			=> 1,
														'created_at'		=> time(),
														'ip'				=> $_SERVER['REMOTE_ADDR'],
													);

													$this->db->insert('eav_attributes_options', $option_insert);
													$variation_selected = $this->db->insert_id();
													$this->db->reset_query();
												}

												if ($variation_selected > 0) {

													$pv_update = array('attr_id' => $attr_id, 'attr_value' => $variation_selected);
													$whr_pv = array('id' => $OptionSelected->id);
													$this->SellerProductModel->updateData('products_variants', $whr_pv, $pv_update);
												}
											}
										}
									}
								}


								/*-----------------------upload media imgs---------------------------------------*/

								if (isset($images) && $images != '') {
									$images = explode(',', $images);
									array_filter(array_unique($images));

									if (isset($images) && count($images) > 0) {

										$img_count = 0;

										/* Store the path of source file */
										$importfilePath = $_SERVER['DOCUMENT_ROOT'] . '/' . IMPORTS_PATH;

										/* Store the path of destination file */
										$destinationFilePath = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'original/';

										foreach ($images as $ig => $orig_name) {


											if (filter_var($orig_name, FILTER_VALIDATE_URL) !== false) {
												$image_name =  basename($orig_name);

												$img_ext_arr = explode('.', $image_name);
												$extension = end($img_ext_arr);

												$new_file_name = $default_image;

												$thumb_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'thumb/';
												$medium_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'medium/';
												$large_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'large/';
												$original_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'original/';

												$img_data_from_url = file_get_contents_curl($orig_name);
												file_put_contents($original_folder . $new_file_name, $img_data_from_url);

												$this->makeThumbnail($new_file_name, $original_folder, $thumb_folder, $width = '300', $height = '300');
												$this->makeThumbnail($new_file_name, $original_folder, $medium_folder, $width = '500', $height = '500');
												$this->makeThumbnail($new_file_name, $original_folder, $large_folder, $width = '800', $height = '800');

												if ($default_image == $image_name) {
													$is_default = 1;
													$is_base_image = 1;
												} else {
													$is_default = 0;
													$is_base_image = 0;
												}

												$media_insert = array('product_id' => $product_id, 'child_id' => $simple_product_id, 'image' => $new_file_name, 'image_title' => $image_name, 'image_position' => $img_count, 'is_default' => $is_default, 'is_base_image' => $is_base_image);
												$this->SellerProductModel->insertData('products_media_gallery', $media_insert);
												$img_count++;
											} else {

												$img_ext_arr = explode('.', $orig_name);
												$extension = end($img_ext_arr);

												$img_exist = $this->SellerProductModel->getSingleDataByID('products_media_gallery', array('product_id' => $product_id, 'child_id' => $simple_product_id, 'image_title' => $orig_name), 'id,image_title');
												if (isset($img_exist) && $img_exist->id != '') {
													//continue;

												} else {

													$new_file_name = $default_image;
													$source_file = $importfilePath . $orig_name;
													$destination_file = $destinationFilePath . $new_file_name;

													if ($default_image == $orig_name) {
														$is_default = 1;
														$is_base_image = 1;
													} else {
														$is_default = 0;
														$is_base_image = 0;
													}


													/* Move File from images to copyImages folder  ---comment for demo
															if( !rename($source_file, $destination_file) ) {
																//echo "File can't be moved!";
															}
															else {
																//echo "File has been moved!";

																$thumb_folder=SIS_SERVER_PATH.'/'.$shop_upload_path.PRODUCT_GALLERY_PATH.'thumb/';
																$original_folder=SIS_SERVER_PATH.'/'.$shop_upload_path.PRODUCT_GALLERY_PATH.'original/';
																$this->makeThumbnail($new_file_name,$original_folder,$thumb_folder,$width='300',$height='300');


																$media_insert=array('child_id'=>$simple_product_id,'product_id'=>$product_id,'image'=>$new_file_name,'image_title'=>$orig_name,'image_position'=>$img_count,'is_default'=>$is_default,'is_base_image'=>$is_base_image);
																$this->SellerProductModel->insertData('products_media_gallery',$media_insert);
																$img_count++;
															}

															*/
												}
											}
										}
									}
								}
							} else {

								/*****-----------------Conf-Simple proudct insert-:start-------------------------------------------****/

								$simple_sku = $sku;
								$simple_stock = $stock_qty;
								$simple_cost_price = $cost_price;
								$simple_price = $price;
								$simple_barcode = $barcode;
								$simple_tax_percent = $tax_percent;

								$simple_tax_amount = $tax_amount;
								$simple_webshop_price = $webshop_price;

								$simple_weight = $weight;

								$insertsimpleproduct = array(
									'name' => $product_name,
									'parent_id' => $product_id,
									'gift_id' => $giftid,
									'sub_issues' => $sub_issues,
									'sku' => $simple_sku,
									'weight' => $simple_weight,
									'price' => $simple_price,
									'tax_percent' => $simple_tax_percent,
									'tax_amount' => $simple_tax_amount,
									'webshop_price' => $simple_webshop_price,
									'cost_price' => $simple_cost_price,
									'barcode' => $simple_barcode,
									'launch_date' => isset($launch_date) ? strtotime($launch_date) : '',
									'product_type' => 'conf-simple',
									'product_inv_type' => 'buy',
									'status' => $status,
									'created_at' => time(),
									'ip' => $_SERVER['REMOTE_ADDR']
								);
								$simple_product_id = $this->SellerProductModel->insertData('products', $insertsimpleproduct);



								if ($simple_product_id) {


									$stock_insert2 = array('product_id' => $simple_product_id, 'qty' => $simple_stock, 'available_qty' => $simple_stock, 'min_qty' => 0, 'is_in_stock' => 1);
									$this->SellerProductModel->insertData('products_inventory', $stock_insert2);


									if (isset($var_list) && count($var_list) > 0) {
										foreach ($var_list as $row_key => $attr_code) {
											if ($product[$row_key] != '') {
												$attr_value = $product[$row_key];
												//$VarData=$this->CommonModel->getSingleDataByID('eav_attributes',array('attr_code'=>$attr_code,'attr_type'=>2),'id,attr_name,attr_code,attr_properties');

												$VarData = $this->EavAttributesModel->check_attr_exist_by_shop_and_attr_code(2, $attr_code);


												if ($VarData) {
													$attr_id = $VarData->id;
													$attr_properties = $VarData->attr_properties;
													//$variation_selected=$attr_value;

													//$OptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_id'=>$attr_id,'attr_options_name'=>$attr_value),'id,attr_id,attr_options_name');

													$OptionData = $this->EavAttributesModel->check_attributes_options_exist_by_seller($attr_id, $attr_value);

													if (isset($OptionData) && $OptionData->attr_options_name != '') {
														$variation_selected = $OptionData->id;
													} else {

														$option_insert = array(
															'attr_id'    		=> $attr_id,
															'attr_options_name'	=> $attr_value,
															//'shop_id'			=> $shop_id,
															'created_by' 		=> 1,
															//'created_by_type' 	=> 1,
															'status'			=> 1,
															'created_at'		=> time(),
															'ip'				=> $_SERVER['REMOTE_ADDR'],
														);

														$this->db->insert('eav_attributes_options', $option_insert);
														$variation_selected = $this->db->insert_id();
														$this->db->reset_query();
													}

													if ($variation_selected > 0) {
														$pv_insert = array('product_id' => $simple_product_id, 'parent_id' => $product_id, 'attr_id' => $attr_id, 'attr_value' => $variation_selected);
														$this->SellerProductModel->insertData('products_variants', $pv_insert);
													}
												}
											}
										}
									}


									/*-----------------------upload media imgs---------------------------------------*/

									if (isset($images) && $images != '') {
										$images = explode(',', $images);
										array_filter(array_unique($images));

										if (isset($images) && count($images) > 0) {

											$img_count = 0;

											/* Store the path of source file */
											$importfilePath = $_SERVER['DOCUMENT_ROOT'] . '/' . IMPORTS_PATH;

											/* Store the path of destination file */
											$destinationFilePath = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'original/';

											foreach ($images as $ig => $orig_name) {

												if (filter_var($orig_name, FILTER_VALIDATE_URL) !== false) {
													$image_name =  basename($orig_name);

													$img_ext_arr = explode('.', $image_name);
													$extension = end($img_ext_arr);

													$new_file_name = $default_image;

													$thumb_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'thumb/';
													$medium_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'medium/';
													$large_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'large/';
													$original_folder = $_SERVER['DOCUMENT_ROOT'] . '' . PRODUCT_GALLERY_PATH . 'original/';



													$img_data_from_url = file_get_contents_curl($orig_name);
													file_put_contents($original_folder . $new_file_name, $img_data_from_url);

													$this->makeThumbnail($new_file_name, $original_folder, $thumb_folder, $width = '300', $height = '300');
													$this->makeThumbnail($new_file_name, $original_folder, $medium_folder, $width = '500', $height = '500');
													$this->makeThumbnail($new_file_name, $original_folder, $large_folder, $width = '800', $height = '800');

													if ($default_image == $image_name) {
														$is_default = 1;
														$is_base_image = 1;
													} else {
														$is_default = 0;
														$is_base_image = 0;
													}

													$media_insert = array('product_id' => $product_id, 'child_id' => $simple_product_id, 'image' => $new_file_name, 'image_title' => $image_name, 'image_position' => $img_count, 'is_default' => $is_default, 'is_base_image' => $is_base_image);
													$this->SellerProductModel->insertData('products_media_gallery', $media_insert);
													$img_count++;
												} else {

													$img_ext_arr = explode('.', $orig_name);
													$extension = end($img_ext_arr);

													$img_exist = $this->SellerProductModel->getSingleDataByID('products_media_gallery', array('product_id' => $product_id, 'child_id' => $simple_product_id, 'image_title' => $orig_name), 'id,image_title');
													if (isset($img_exist) && $img_exist->id != '') {
														//continue;

													} else {

														$new_file_name = $default_image;
														$source_file = $importfilePath . $orig_name;
														$destination_file = $destinationFilePath . $new_file_name;

														if ($default_image == $orig_name) {
															$is_default = 1;
															$is_base_image = 1;
														} else {
															$is_default = 0;
															$is_base_image = 0;
														}


														/* Move File from images to copyImages folder  ------comment for demo
															if( !rename($source_file, $destination_file) ) {
																//echo "File can't be moved!";
															}
															else {
																//echo "File has been moved!";

																$thumb_folder=SIS_SERVER_PATH.'/'.$shop_upload_path.PRODUCT_GALLERY_PATH.'thumb/';
																$original_folder=SIS_SERVER_PATH.'/'.$shop_upload_path.PRODUCT_GALLERY_PATH.'original/';
																$this->makeThumbnail($new_file_name,$original_folder,$thumb_folder,$width='300',$height='300');


																$media_insert=array('child_id'=>$simple_product_id,'product_id'=>$product_id,'image'=>$new_file_name,'image_title'=>$orig_name,'image_position'=>$img_count,'is_default'=>$is_default,'is_base_image'=>$is_base_image);
																$this->SellerProductModel->insertData('products_media_gallery',$media_insert);
																$img_count++;
															}

															*/
													}
												}
											}
										}
									}
								}
								//simple product end


								/*****-----------------Conf-Simple proudct insert-:end-------------------------------------------****/
							}
						}

						//parent product end

					} else {
						// skip row
						continue;
					}
				}


				$arrResponse  = array('status' => 200, 'message' => 'Product Imported Successfully.');
				echo json_encode($arrResponse);
				exit;
			} else {
				$arrResponse  = array('status' => 400, 'message' => 'Please have some data in csv file');
				echo json_encode($arrResponse);
				exit;
			}

			//print_r_custom($product_data);
		} else {
			$arrResponse  = array('status' => 400, 'message' => 'Please upload valid csv file');
			echo json_encode($arrResponse);
			exit;
		}
	}


	function checkstockqty()
	{
		if (isset($_POST['stock_qty']) &&  $_POST['stock_qty'] != '') {

			if ($_POST['product_type'] == 'simple') {
				if ($_POST['flag'] == 'add') {

					echo 'true';
					exit;
				} else if ($_POST['flag'] == 'edit') {
					$pid = $_POST['pid'];
					$stock_qty = $_POST['stock_qty'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $pid), 'qty,available_qty');
					if ($stock_qty < $PC_Exist->qty) {
						$old_ordered_qty = $PC_Exist->qty - $PC_Exist->available_qty;
						//$new_available_qty=$stock_qty-$old_ordered_qty;
						//	echo $stock_qty.'=========='.$old_ordered_qty;exit;
						if ($old_ordered_qty <= 0) {
							echo 'true';
							exit;
						}

						if ($stock_qty < $old_ordered_qty) {
							echo 'false';
							exit;
						} else {
							echo 'true';
							exit;
						}
					} else {
						echo 'true';
						exit;
					}
				} else {
					echo 'true';
					exit;
				}
			} else if ($_POST['product_type'] == 'configurable') {
				if ($_POST['flag'] == 'add') {
					echo 'true';
					exit;
				} else if ($_POST['flag'] == 'edit') {
					$pid = $_POST['pid'];
					$stock_qty = $_POST['stock_qty'];
					$PC_Exist = $this->SellerProductModel->getSingleDataByID('products_inventory', array('product_id' => $pid), 'qty,available_qty');

					if ($stock_qty < $PC_Exist->qty) {

						$old_ordered_qty = $PC_Exist->qty - $PC_Exist->available_qty;
						if ($old_ordered_qty <= 0) {
							echo 'true';
							exit;
						}
						//$new_available_qty=$stock_qty-$old_ordered_qty;
						//	echo $stock_qty.'=========='.$old_ordered_qty;exit;

						if ($stock_qty < $old_ordered_qty) {
							echo 'false';
							exit;
						} else {
							echo 'true';
							exit;
						}
					} else {
						echo 'true';
						exit;
					}
				}
			} else {
				echo 'true';
				exit;
			}
		} else {
			echo 'true';
			exit;
		}
	}

	function deleteProduct()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		if (isset($_POST['product_id']) && $_POST['product_id'] != '') {
			$product_id = $_POST['product_id'];
			$ProductData = $this->SellerProductModel->getSingleDataByID('products', array('id' => $product_id), '');
			if ($ProductData->product_type == 'configurable') {
				// $VariantMaster=$this->SellerProductModel->getVariantMasterForProducts($product_id);

				// if(isset($VariantMaster) && count($VariantMaster)>0){
				// 	foreach($VariantMaster as $value){
				// 		$pid=$value['id'];

				// 		$pv_update=array('remove_flag'=>1,'remove_date'=>time());
				// 		$whr_pv=array('id'=>$pid);
				// 		$this->SellerProductModel->updateData('products',$whr_pv,$pv_update);

				// 	}
				// }
				$pv_update = array('remove_flag' => 1, 'remove_date' => time());
				$whr_pv = array('parent_id' => $product_id);
				$this->SellerProductModel->updateData('products', $whr_pv, $pv_update);
			}

			$pv_update = array('remove_flag' => 1, 'remove_date' => time());
			$whr_pv = array('id' => $product_id);
			$this->SellerProductModel->updateData('products', $whr_pv, $pv_update);

			echo "success";
			exit;
		} else {
			echo "error";
			exit;
		}
	}

	function getvariantlist()
	{
		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');
		//$CategoryDetail=$this->CategoryModel->get_category_detail($_POST['category_id']);
		$data['CategoryDetail'] = array();

		$data['flag'] = $flag = isset($_POST['flag']) ? $_POST['flag'] : '';

		$data['selected_variants'] = array();

		if (isset($_POST['flag'])  && $flag == 'edit_variant') {

			$product_id = $_POST['pid'];
			$VariantMaster = $this->SellerProductModel->getVariantMasterForProducts($product_id);
			$data['VariantMaster'] = $VariantMaster;
		} else {

			$data['VariantsBySeller'] = $this->EavAttributesModel->get_variant_masters($shop_id);
		}

		$data['Rounded_price_flag'] = $this->CommonModel->getRoundedPriceFlag();

		$View = $this->load->view('seller/products/variant_popup', $data, true);
		$this->output->set_output($View);
	}

	function getattributelist()
	{

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		/*
			if(isset($CategoryDetail) && $CategoryDetail->selected_attributes!=''){

				$selected_attributes=$CategoryDetail->selected_attributes;
				$selected_attributes = substr($selected_attributes, 1, -1);
				if( strpos($selected_attributes, ',') !== false ) {
					$selected_attributes_arr=explode(',',$selected_attributes);
				}else{
					$selected_attributes_arr[]=$selected_attributes;
				}
				if(count($selected_attributes_arr)>0){

					$AttributesList=$this->EavAttributesModel->get_attributes_for_seller($selected_attributes_arr);
					$data['AttributesList']=$AttributesList;
				}else{
					$data['AttributesList']=array();
				}
			}else{
				$data['AttributesList']=$this->EavAttributesModel->get_default_attributes();
			}
			*/



		$data['flag'] = $flag = isset($_POST['flag']) ? $_POST['flag'] : '';
		if (isset($flag) && $flag == 'edit_attr') {
			$data['AttributesBySeller'] = $this->EavAttributesModel->get_attributes_masters($shop_id);
		} else {
			$data['AttributesList'] = $this->EavAttributesModel->get_attributes_masters($shop_id);
		}
		$View = $this->load->view('seller/products/attribute_select_list', $data, true);
		$this->output->set_output($View);
	}

	function refreshcategorytree()
	{

		$fbc_user_id	=	$this->session->userdata('LoginID');
		$shop_id		=	$this->session->userdata('ShopID');

		if (isset($_POST['flag']) && $_POST['flag'] != '') {
			$data['flag'] = $flag = $_POST['flag'];
			$data['pid'] = $product_id = $_POST['pid'];

			$data['CategoryTree'] = $this->CategoryModel->get_categories_for_shop($shop_id);

			if (isset($product_id) && $product_id > 0) {

				$data['ProductData'] = $ProductData = $this->SellerProductModel->getSingleDataByID('products', array('id' => $product_id), '');
				$MainCat = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 0), 'category_ids');
				$root_cat_arr = array();
				if (isset($MainCat) && count($MainCat)) {
					foreach ($MainCat as $val) {
						$root_cat_arr[] = $val->category_ids;
					}
				}
				$data['cat_level_zero_selected'] = $root_cat_arr;

				$SubCat = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 1), 'category_ids');
				$sub_cat_arr = array();
				if (isset($SubCat) && count($SubCat)) {
					foreach ($SubCat as $val) {
						$sub_cat_arr[] = $val->category_ids;
					}
				}
				$data['cat_level_one_selected'] = $sub_cat_arr;


				$TagsCat = $this->SellerProductModel->getMultiDataById('products_category', array('product_id' => $product_id, 'level' => 2), 'category_ids');
				$tags_cat_arr = array();
				if (isset($TagsCat) && count($TagsCat)) {
					foreach ($TagsCat as $val) {
						$tags_cat_arr[] = $val->category_ids;
					}
				}
				$data['cat_level_two_selected'] = $tags_cat_arr;
			}

			$View = $this->load->view('seller/products/category_checkbox', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
		}
	}

	function getcatproductcount()
	{
		if (isset($_POST['id'])  && $_POST['id'] != '' && $_POST['id'] > 0) {
			$product_count = $this->SellerProductModel->getCategoryProductCount($_POST['id'], $_POST['cat_level']);
			print_r($product_count);
			exit;
			echo $product_count;
			exit;
		} else {
			echo 'error';
			exit;
		}
	}

	function deletecategory()
	{
		if (isset($_POST['id'])  && $_POST['id'] != '' && $_POST['id'] > 0) {
			$category_id = $_POST['id'];
			$cat_level = $_POST['cat_level'];

			$pdeletedata = array('category_ids' => $category_id, 'level' => $cat_level);
			$this->SellerProductModel->deleteDataById('products_category', $pdeletedata);

			if ($cat_level == 0) {
				$c2deletedata = array('parent_id' => $category_id, 'cat_level' => 2);
				$this->db->delete('category', $c2deletedata);
				$this->db->reset_query();


				$c1deletedata = array('parent_id' => $category_id, 'cat_level' => 1);
				$this->db->delete('category', $c1deletedata);
				$this->db->reset_query();

				$c1deletedata = array('parent_id' => $category_id, 'cat_level' => 1);
				$this->db->delete('category', $c1deletedata);
				$this->db->reset_query();

				$cdeletedata = array('id' => $category_id, 'cat_level' => 0);
				$this->db->delete('category', $cdeletedata);
				$this->db->reset_query();
			} else if ($cat_level == 1) {
				$c2deletedata = array('parent_id' => $category_id, 'cat_level' => 2);
				$this->db->delete('category', $c2deletedata);
				$this->db->reset_query();

				$c1deletedata = array('id' => $category_id, 'cat_level' => 1);
				$this->db->delete('category', $c1deletedata);
				$this->db->reset_query();
			} else if ($cat_level == 2) {

				$cdeletedata = array('id' => $category_id, 'cat_level' => 2);
				$this->db->delete('category', $cdeletedata);
				$this->db->reset_query();
			}
			echo 'success';
			exit;
		} else {
			echo 'error';
			exit;
		}
	}

	public function openproductmediapopup()
	{
		if (isset($_POST['product_id']) && $_POST['product_id'] != '') {
			$product_id = $_POST['product_id'];
			$data['product_id'] = $product_id;
			$data['getProduct'] =  $this->SellerProductModel->getVariantMasterForProducts($product_id);
			$data['Product_media_variant'] =  $this->SellerProductModel->getProductMediaVariants($product_id);
			$View = $this->load->view('seller/products/product_manage_media', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	public function openproductmediavariantpopup()
	{
		if (isset($_POST['product_id']) && isset($_POST['variant_id'])) {
			$data['product_id'] = $product_id = $_POST['product_id'];
			$variant_id = $data['variant_id'] = $_POST['variant_id'];
			$data['variant_options_name'] = $this->SellerProductModel->getVariantDetailsByProductID($product_id, $variant_id);
			$data['ProductMedia'] = $this->SellerProductModel->getMultiDataById('products_media_gallery', array('product_id' => $product_id), '');
			$View = $this->load->view('seller/products/product_variant_media', $data, true);
			$this->output->set_output($View);
		} else {
			echo "error";
			exit;
		}
	}

	public function saveProductMediaVariant()
	{

		if (isset($_POST['variant_id']) && isset($_POST['product_id'])) {
			$product_id = $data['product_id'] = $_POST['product_id'];
			$variant_id = $data['variant_id'] = $_POST['variant_id'];

			$update_data = array(
				'media_variant_id' => $variant_id
			);
			$where_arr = array('id' => $product_id);

			if ($variant_id > 0) {

				$checkProductMediaVariantID = $this->SellerProductModel->getProductMediaVariants($product_id);
				if ($checkProductMediaVariantID->media_variant_id != $variant_id) {
					$reset_media_data = array(
						'attr_option_id' => 0,
						'is_default_variant' => 0
					);
					$where_array = array('product_id' => $product_id);
					$this->SellerProductModel->updateData('products_media_gallery', $where_array, $reset_media_data);
				}

				$this->SellerProductModel->updateData('products', $where_arr, $update_data);
				$data['variant_options_name'] = $this->SellerProductModel->getVariantDetailsByProductID($product_id, $variant_id);
				$data['ProductMedia'] = $this->SellerProductModel->getMultiDataById('products_media_gallery', array('product_id' => $product_id), '');
				$View = $this->load->view('seller/products/product_variant_media', $data, true);
				$this->output->set_output($View);
			} else {
				$this->SellerProductModel->updateData('products', $where_arr, $update_data);
				echo json_encode(array('status' => 400, 'message' => "Media Variants Updated."));
				exit;
			}
		} else {
			echo json_encode(array('status' => 600, 'message' => "something went Wrong."));
			exit;
		}
	}

	public function saveProductMediaAttrValue()
	{
		if (isset($_POST)) {
			$product_id = $_POST['product_id'];
			$attr_option = $_POST['attr_option'];

			$attr_option_empty_cnt = 0;
			$attr_option_notempty_cnt = 0;

			for ($i = 0; $i < sizeof($attr_option); $i++) {
				$attr_option_id = $attr_option[$i];
				$selected_media_id = 'media_attr_' . $attr_option[$i];
				$deafult_variant_id = 'default_variant_' . $attr_option[$i];

				$media_id = isset($_POST[$selected_media_id]) && $_POST[$selected_media_id] != '' ? $_POST[$selected_media_id] : '';
				$deafult_variant = isset($_POST[$deafult_variant_id]) && $_POST[$deafult_variant_id] != '' ? $_POST[$deafult_variant_id] : '';

				if ($media_id == '') {
					$attr_option_empty_cnt = $attr_option_empty_cnt + 1;
				} else {
					$attr_option_notempty_cnt = $attr_option_notempty_cnt + 1;
					if ($attr_option_notempty_cnt == 1) {
						$reset_media_data = array(
							'attr_option_id' => 0,
							'is_default_variant' => 0
						);
						$where_arr = array('product_id' => $product_id);
						$this->SellerProductModel->updateData('products_media_gallery', $where_arr, $reset_media_data);
					}

					$media_cnt = 0;
					if ($media_id != '') {
						foreach ($media_id as $key => $media) {
							$media_cnt++;

							if ($media_cnt == 1 && $deafult_variant == '') {
								$update_data = array(
									'attr_option_id' => $attr_option_id,
									'is_default_variant' => 1
								);
							} else {
								$update_data = array(
									'attr_option_id' => $attr_option_id,
								);
							}

							$where_arr = array('product_id' => $_POST['product_id'], 'id' => $media_id[$key]);
							$this->SellerProductModel->updateData('products_media_gallery', $where_arr, $update_data);
						}
					}

					if ($media_id != '' && $deafult_variant != '') {
						$update_data = array(
							'is_default_variant' => 1
						);
						$where_arr = array('id' => $deafult_variant);
						$this->SellerProductModel->updateData('products_media_gallery', $where_arr, $update_data);
					}
				}

				if ($attr_option_empty_cnt == count($attr_option)) {
					echo json_encode(array('status' => 400, 'message' => "Please Assign images for variants"));
					exit;
				}
			}
			echo json_encode(array('status' => 200, 'message' => "Images Assign to Variants."));
			exit;
		} else {
			echo json_encode(array('status' => 400, 'message' => "Something Went Wrong."));
			exit;
		}
	}

	function get_publisher_data()
	{
		$id = $this->input->post('id');

		$data = $this->SellerProductModel->get_publication_by_id($id);

		echo json_encode(array('status' => 200, 'message' => "Success", 'data' => $data));
		exit();
	}

	function get_product_commission_data()
	{
		$id = $this->input->post('id');

		$data = $this->SellerProductModel->get_product_commission_by_id($id);

		echo json_encode(array('status' => 200, 'message' => "Success", 'data' => $data));
		exit();
	}

	function addgifts()
	{
		$data = $this->SellerProductModel->get_gifts_data();

		echo json_encode($data);
		exit();
	}

	//Add Input and RadioButton OnChange of Publisher
	function addCommissionInputField()
	{
		$output = '	<div class="container col-sm-12">
							<h2>Type of Commission</h2>
						</div>
						<div class="container col-sm-12">
			  				<form name="form_commission" id="form_commission">
								<label class="radio-inline">
									<input type="radio" id="type_of_commission" name="type-of-commission" checked value="0">Merchant Commission
									<span class="checkmark"></span></label>
								<label class="radio-inline">
									<input type="radio" id="type_of_commission" name="type-of-commission" value="1" >Product Commission
									<span class="checkmark"></span></label>
			  				</form>
						</div>
		
						<div class="col-sm-12">
							<h2>Merchant Commission Percentage</h2>
								<input type="text" class="form-control" name="pub_com_percentage" id="pub_com_percentage" value="" placeholder="Merchant Commission Percentage" readonly="readonly">
						</div>';
		echo $output;
		exit();
	}

	public function removedBundleProductItem()
	{
		if (isset($_POST)) {
			$bundleId = $_POST['bundleId'];
			if (!empty($bundleId) && $bundleId != '') {
				$resultData = $this->SellerProductModel->removedBundleProductItemById($bundleId);
				if (isset($resultData) && !empty($resultData)) {
					// bundle product item data
					$id = $resultData->id;
					$price = $resultData->price;
					$tax_percent = $resultData->tax_percent;
					$tax_amount = $resultData->tax_amount;
					$webshop_price = $resultData->webshop_price;
					$default_qty = $resultData->default_qty;

					// main product table data
					$mproduct_id = $resultData->bundle_product_id;
					$mprice = $resultData->pprice;
					$mcost_price = $resultData->pcost_price;
					$mtax_percent = $resultData->ptax_percent;
					$mtax_amount = $resultData->ptax_amount;
					$mwebshop_price = $resultData->pwebshop_price;


					$uprice = $mprice - ($default_qty * $price);
					$ucost_price = $uprice;
					$utax_amount = $mtax_amount - ($default_qty * $tax_amount);
					$uwebshop_price = $mwebshop_price - ($default_qty * $webshop_price);

					$main_product_update = array(
						'price' => $uprice,
						'cost_price' => $ucost_price,
						'tax_amount' => $utax_amount,
						'webshop_price' => $uwebshop_price,
						'updated_at' => time()
					);
					$main_where_arr = array('id' => $mproduct_id);
					$this->SellerProductModel->updateData('products', $main_where_arr, $main_product_update);
					$products_bundles_where_arr = array('id' => $id);
					$this->SellerProductModel->deleteDataById('products_bundles', $products_bundles_where_arr);
				}

				//print_r($resultData);exit();
				//$whereArray=array('id'=>$_POST['productId'],'remove_flag'=>0,'product_type'=>'configurable');
				//parent_id
				//$whereArray=array('parent_id'=>$_POST['productId'],'remove_flag'=>0,'product_type !='=>'configurable');
			}
			//$result_data=$this->SellerProductModel->getSingleDataByID('products',$whereArray,'*');
		}
	}
}
