<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_DB_driver $db
 * @property B2BImportModel $B2BImportModel
 * @property CategoryModel $CategoryModel
 * @property CommonModel $CommonModel
 * @property EavAttributesModel $EavAttributesModel
 * @property S3_filesystem $s3_filesystem
 * @property SupplierModel $SupplierModel
 * @property UserModel $UserModel
 */
class B2BImportController extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
        $this->load->model('B2BImportModel');
        $this->load->model('SupplierModel');
        $this->load->model('UserModel');
        $this->load->model('CategoryModel');
        $this->load->model('EavAttributesModel');
		$this->load->library('S3_filesystem');

		if(empty($this->session->userdata('LoginID'))){
			redirect(base_url());
		}
	}

    public function index()
	{
		$data['PageTitle']='B2Webshop Import';
		$data['side_menu']='b2webshop-import';
        $shopDetails = $this->B2BImportModel->getUserShopDetails();
        $shop_id =	$this->session->userdata('ShopID');
		$SellerQuickPageImport = array();

		foreach ($shopDetails as $shop) {
			$seller_shop_id = $shop->shop_id;
			$seller_db_name = $shop->database_name;
			if ($seller_shop_id !== $shop_id) {
				$getb2b_customers = $this->B2BImportModel->getB2BCustomer($seller_db_name, $shop_id);
				if (isset($getb2b_customers) && $getb2b_customers->import_through_quickpage == 1) {
					$SellerQuickPageImport[] = $shop;
				}
			}
		}

        $data['B2BCustomer_Details'] = "";
        $data['SellerQuickPageImport'] = $SellerQuickPageImport;

		$this->load->view('seller/B2Bimport/supplierList',$data);
	}

	public function getB2BCustomerDetails()
	{
		$supplier_shop_id = $this->input->post('shop_id');
		$shop_id = $this->session->userdata('ShopID');
		$data['B2BCustomer_Details'] = $this->B2BImportModel->getB2BCustomerDetails($supplier_shop_id, $shop_id);
		$data['B2B_Terms'] = $this->B2BImportModel->getB2Bterms($supplier_shop_id);
		$shopData = $this->UserModel->getShopDetailsByShopId($supplier_shop_id);
		$data['supplier_currency_code'] = $shopData->currency_code;
		$BuyerShopData = $this->UserModel->getShopDetailsByShopId($shop_id);
		$data['buyer_currency_code'] = $BuyerShopData->currency_code;

		$this->load->view('seller/B2Bimport/b2bCutomerDetails', $data);
	}

	public function getProductSku()
	{
		$term_requested = $_GET['term'];
		$supplier_shop_id = $_GET['supplier_shop_id'];
		$ProductName_Sku = $this->B2BImportModel->getProductNameSku($supplier_shop_id, $term_requested);

		echo json_encode($ProductName_Sku); exit();
	}

    public function AddProductB2b(){
		$tax_percent = '0.00';
        $barcode =  $this->input->post('barcode_code');
        $sku = $this->input->post('sku');
        $supplier_shop_id = $this->input->post('supplier_shop_id');
        $shop_id =	$this->session->userdata('ShopID');
        $import_id = $this->input->post('import_id');

		$new_import_id = '';
        if(!empty($import_id)){
            $new_import_id = $import_id;
            $where=array(
                'import_id'=>$new_import_id
            );

            $this->B2BImportModel->deleteDataById('single_b2b_imports_items',$where);
        }

		$B2BCustomer_Details  = $this->B2BImportModel->getB2BCustomerDetails($supplier_shop_id,$shop_id);

        $disabled = ($B2BCustomer_Details->allow_dropship==0 && $B2BCustomer_Details->allow_buyin==0)?'disabled':'';

        if($B2BCustomer_Details->allow_dropship==1 || $B2BCustomer_Details->allow_buyin==1){
            $checked ='checked';

            if($B2BCustomer_Details->allow_buyin==1 && $B2BCustomer_Details->allow_dropship==0){
                $disabled_buyin = '';
                $disabled_dropship = 'disabled';
                $checked_buyin = 'checked';
                $checked_drop_ship = '';
                $product_type = 'buy';
            } elseif($B2BCustomer_Details->allow_dropship==1 && $B2BCustomer_Details->allow_buyin==0) {
                $disabled_buyin = 'disabled';
                $disabled_dropship = '';
                $checked_buyin = '';
                $checked_drop_ship = 'checked';
                $product_type = 'dropship';
            } else {
                $disabled_buyin = '';
                $disabled_dropship = '';
                $checked_buyin = 'checked';
                $checked_drop_ship = '';
                $product_type = 'buy';
            }

        } else {
            $checked ='';
            $disabled_buyin = 'disabled';
            $disabled_dropship = 'disabled';
            $checked_buyin = '';
            $checked_drop_ship = '';
            $product_type = '';
        }

        $shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$supplier_shop_id),'fbc_user_id,currency_symbol,currency_code');
	  	$currency_symbol = $shopData->currency_symbol ?? $shopData->currency_code;

		$productData = $this->B2BImportModel->CheckProductsAvailable($barcode,$sku,$supplier_shop_id);

        if(isset($productData) && count($productData) > 0) {
            if($new_import_id === ""){
                $insertdata=array(
                    'created_at'=>time(),
                );
                $new_import_id = $this->B2BImportModel->insertData('single_b2b_imports',$insertdata);
            }

			$row ='';

			foreach($productData as $item){
				$tax_percent = $item->tax_percent;
				$launch_date = (isset($item->launch_date) && $item->launch_date != 0 ) ? date('d-m-Y' ,$item->launch_date) : " ";

				$VariantMaster=$this->SupplierModel->getVariantDetailsForProducts($supplier_shop_id,$item->id);
				$variants_html = '';
				$OptionValue = array();

				if(isset($VariantMaster) && count($VariantMaster)>0){
					foreach($VariantMaster as $attr){
						$OptionValue[] = array('label' => $attr->attr_name, 'value' => $attr->attr_options_name);
					}
				}

				if(is_array($OptionValue) && count($OptionValue) >0){
					foreach($OptionValue as $val){
						$variants_html .= '<span class="variant-item">'.$val['label'].' - '.$val['value'].'</span><br>';
					}
				} else {
					$variants_html = '-';
				}

				$product_id = ($item->parent_id != 0)?$item->parent_id:$item->id;
				$href = BASE_URL.'product/detail/'.$supplier_shop_id.'/'.$product_id;
				$stock = ($item->qty > 0)  ? "In Stock" : "Out of Stock";

				$row .= '<tr>';
				$row.='<td><label class="checkbox"><input type="checkbox" class="form-control chk-line-'.$item->id.' main-checkbox pid-'.$item->category_id.'-'.$item->id.'" data-product_id="'.$item->id.'" value="'.$item->id.'" id="checkedProduct_'.$item->category_id.'_'.$item->id.'" name="checkedProduct[]" onclick="getProductCheckUncheck(this.value,'.$item->category_id.')" '.$disabled.' '.$checked.'><span class="checked"></span></label><input type="hidden" value="'.$product_type.'" id="product_type_'.$item->id.'" name="product_type_'.$item->id.'"></td>';
				$row.='<td><a class="link-purple" href="'.$href.'" target="_blank">View</a></td>';
				$row.='<td>'.$item->sku.'</td>';
				$row.='<td>'.$item->name.'</td>';
				$row.='<td>'.$item->cat_name.'</td>';
				$row.='<td>'.$variants_html.'</td>';
				$row.='<td>'.$stock.'</td>';
				$row.='<td>'.$launch_date.'</td>';
				$row.='<td><label class="checkbox"><input type="checkbox" class="form-control chk-line-'.$item->id.' buyin-checkbox pid-'.$item->id.' chk-'.$item->category_id.'-'.$item->id.'" data-product_id="'.$item->id.'"  id="buyin_'.$item->category_id.'_'.$item->id.'" name="buyin_'.$item->category_id.'_'.$item->id.'" value="'.$item->id.'" onclick="getCheckboxCheckUncheck(this.value,'.$item->category_id.')" '.$checked_buyin.' '.$disabled_buyin.'><span class="checked"></span></label></td>';
				$row.='<td><label class="checkbox"><input type="checkbox" class="form-control chk-line-'.$item->id.' virtual-checkbox pid-'.$item->id.' chk-'.$item->category_id.'-'.$item->id.'" data-product_id="<?php echo $value->id;?>"  id="virtual_'.$item->category_id.'_'.$item->id.'" name="virtual_'.$item->category_id.'_'.$item->id.'" value="'.$item->id.'" onclick="getCheckboxCheckUncheck(this.value,'.$item->category_id.')" '.$disabled.'><span class="checked" ></span></label></td>';
				$row.='<td><label class="checkbox"><input type="checkbox" class="form-control chk-line-'.$item->id.'  dropship-checkbox pid-'.$item->id.' chk-'.$item->category_id.'-'.$item->id.'" id="dropship_'.$item->category_id.'_'.$item->id.'" name="dropship_'.$item->category_id.'_'.$item->id.'" value="'.$item->id.'" onclick="getCheckboxCheckUncheck(this.value,'.$item->category_id.')" data-product_id="'.$item->id.'"  '.$checked_drop_ship.' '.$disabled_dropship.'><span class="checked"></span></label></td>';
				$row.='<td>'.$currency_symbol." ".number_format($item->price,2).'</td>';
				$row.= '</tr>';

				$insertdataitem=array(
					'import_id'=>$new_import_id,
					'product_id'=>$item->id,
					'parent_id'=>$item->parent_id,
				);

				$this->B2BImportModel->insertData('single_b2b_imports_items',$insertdataitem);
			}

			echo json_encode(['status' =>200 ,'message'=>'Barcode scanned successfully.','import_id'=>$new_import_id,'product_id'=>$product_id ?? 0,'printHTML'=>$row,'tax_percent'=>$tax_percent]);
			exit;
        }

		echo json_encode(['status' =>500 ,'message'=>'No product Found']);
		exit;
	}

    public function getCustomerType(){
        $import_id = $this->input->post('import_id');
        $supplier_shop_id = $this->input->post('supplier_shop_id');
		$LoginID = $this->session->userdata('LoginID');
        $shopData = $this->UserModel->getShopDetailsByShopId($supplier_shop_id);
        $data['webshop_name']= $shopData->org_shop_name;
		$CurrentuserData = $this->UserModel->getUserByUserId($LoginID);
        $Current_user_shop_id = $CurrentuserData->shop_id;
        $Current_user_shopData = $this->UserModel->getShopDetailsByShopId($Current_user_shop_id);
        $data['current_user_webshop_name']= $Current_user_shopData->org_shop_name;
        $b2bProductIds = $this->B2BImportModel->b2b_imports_items($import_id);

        $getSupplierCustomerTypes = $this->SupplierModel->getCustomerTypesByProductsId($supplier_shop_id,$b2bProductIds);

		if($getSupplierCustomerTypes->customer_type !== null){
            $uniqueCustomerTypes = implode(',', array_unique(explode(',', $getSupplierCustomerTypes->customer_type)));
        } else {
            $uniqueCustomerTypes = 0;
        }

        $data['seller_selected_Customer_type'] = $this->SupplierModel->get_customer_types_selected($supplier_shop_id,$uniqueCustomerTypes);
        $seller_selected_Customer_type = explode(',', $uniqueCustomerTypes);

        if (in_array(0, $seller_selected_Customer_type)) {
            $data['AllExist'] = 'yes';
        } else {
            $data['AllExist'] = 'no';
        }

        $data['seller_ct_count'] = count($data['seller_selected_Customer_type']);
        $data['buyer_Customer_type'] = $this->CommonModel->get_customer_types();

        $this->load->view('seller/B2Bimport/customer_types',$data);
    }

    function importProductSubmit(){
        if(!isset($_POST)) {
			echo json_encode(array("flag" => 0, "msg" => "Error while importing."));
			exit;
		}

		$to_shop_id = $this->session->userdata('ShopID');
		$to_fbc_user_id = $this->session->userdata('LoginID');
		$import_id = $_POST['import_id_submitted'];
		$ShopID = $_POST['supplier_shop_id_submitted'];

		if (isset($_POST['price_converted'])) {
			$this->session->set_userdata('b2b_single_price_converter', $_POST['price_converted']);
		}

		$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag($to_shop_id);
		$price_converted = $_POST['price_converted'] ?? 0;

		$shopData = $this->UserModel->getShopDetailsByShopId($ShopID);
		$seller_shop_currency_code = $shopData->currency_code;
		$BuyerShopData = $this->UserModel->getShopDetailsByShopId($to_shop_id);
		$buyer_shop_currency_code = $BuyerShopData->currency_code;
		$delivery_identifier = 'product_delivery_duration';
		$product_delivery_duration = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'custom_variables', array('identifier' => $delivery_identifier), '');
		$product_delivery_duration = $product_delivery_duration->value ?? '';
		$shop_source_bucket = get_s3_bucket($ShopID);
		$shop_upload_bucket = get_s3_bucket($to_shop_id);
		$b2b_customers = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'b2b_customers', array('shop_id' => $to_shop_id), '');
		$FbcUserB2BData = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'b2b_customers_details', array('customer_id' => $b2b_customers->id), '');
		$shop_buyin_discount_percent = ($FbcUserB2BData->buyin_discount > 0) ? $FbcUserB2BData->buyin_discount : 0;
		$shop_buyin_del_time_in_days = $FbcUserB2BData->buyin_del_time;
		$shop_dropship_discount_percent = ($FbcUserB2BData->dropship_discount > 0) ? $FbcUserB2BData->dropship_discount : 0;
		$shop_dropship_del_time_in_days = $FbcUserB2BData->dropship_del_time;
		$shop_display_catalog_overseas = $FbcUserB2BData->display_catalog_overseas;
		$shop_perm_to_change_price = $FbcUserB2BData->perm_to_change_price;
		$shop_can_increase_price = $FbcUserB2BData->can_increase_price;
		$shop_can_decrease_price = $FbcUserB2BData->can_decrease_price;

		if (is_array($_POST['checkedProduct']) && count($_POST['checkedProduct']) > 0) {
			foreach ($_POST['checkedProduct'] as $item) {
				$where = array(
					'import_id' => $import_id,
					'product_id' => $item
				);

				$import_items_data = $this->B2BImportModel->getSingleDataByID('single_b2b_imports_items', $where, '');

				$product_inv_type = $_POST['product_type_' . $item];
				if ($import_items_data->parent_id == 0) {
					$product_id = $import_items_data->product_id;
					$product_type = 'simple';
				} else {
					$product_id = $import_items_data->parent_id;
					$product_type = 'configurable';
				}

				$catData = $this->B2BImportModel->getMultiDataByIdSupplier($ShopID, 'products_category', array('product_id' => $product_id), '');
				$cat_id_arr = array();
				$sub_cat_id_arr = array();
				$child_cat_id_arr = array();

				if (is_array($catData) && count($catData) > 0) {
					foreach ($catData as $cat) {
						$category_id = $cat->category_ids;
						$CatInfo = $this->CommonModel->getSingleDataByID('category', array('id' => $category_id), '');
						$cat_name = $CatInfo->cat_name;
						$slug = $CatInfo->slug;
						$cat_description = $CatInfo->cat_description;

						if ($CatInfo->created_by_type == 0) {
							if ($CatInfo->cat_level == 0) {
								$cat_id_arr[] = $category_id;
								$cat_id = $category_id;
								$sub_cat_id = '';
								$child_cat_id = '';
							} elseif ($CatInfo->cat_level == 1) {
								$sub_cat_id_arr[] = $category_id;
								$cat_id = $CatInfo->parent_id;
								$sub_cat_id = $category_id;
								$child_cat_id = '';
							} elseif ($CatInfo->cat_level == 2) {
								$child_cat_id_arr[] = $category_id;
								$cat_id = $CatInfo->main_parent_id;
								$sub_cat_id = $CatInfo->parent_id;
								$child_cat_id = $category_id;
							}

						} else {
							if ($CatInfo->cat_level == 0) {
								$mainCatExist = $this->CommonModel->getSingleDataByID('category', array('cat_name' => $cat_name, 'cat_level' => 0, 'shop_id' => $to_shop_id), '');
								if (empty($mainCatExist)) {
									$insertArr = array('cat_name' => $cat_name, 'slug' => $slug, 'cat_level' => 0, 'cat_description' => $cat_description, 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
									$cat_id = $this->CategoryModel->insertData('category', $insertArr);
									$cat_id_arr[] = $cat_id;
								} else {
									$cat_id = $mainCatExist->id;
									$cat_id_arr[] = $cat_id;
								}
							} elseif ($CatInfo->cat_level == 1) {
								$subCatExist = $this->CommonModel->getSingleDataByID('category', array('cat_name' => $cat_name, 'cat_level' => 1, 'shop_id' => $to_shop_id), '');

								if (empty($subCatExist)) {
									$insertArr = array('cat_name' => $cat_name, 'slug' => $slug, 'cat_level' => 1, 'parent_id' => $cat_id, 'cat_description' => '', 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
									$sub_cat_id = $this->CategoryModel->insertData('category', $insertArr);
									$sub_cat_id_arr[] = $sub_cat_id;
								} else {
									$sub_cat_id = $subCatExist->id;
									$sub_cat_id_arr[] = $sub_cat_id;
								}

							} elseif ($CatInfo->cat_level == 2) {

								$IsChildExist = $this->CategoryModel->check_child_category_exist($to_shop_id, $sub_cat_id, 2, $cat_name);

								if (isset($IsChildExist) && $IsChildExist['id'] != '') {
									$cid = $IsChildExist['id'];
									$child_cat_id_arr[] = $cid;
								} else {
									$cc_slug = url_title($cat_name);
									$url_key = strtolower($cc_slug);
									$slugcount = $this->CategoryModel->check_category_exist_by_slug($to_shop_id, 2, $cat_name);

									if ($slugcount > 0) {
										$slugcount = $slugcount + 1;
										$url_key = $url_key . "-" . $slugcount;
									}

									$child_cat_insert = array('cat_name' => $cat_name, 'slug' => $url_key, 'parent_id' => $sub_cat_id, 'main_parent_id' => $cat_id, 'cat_level' => 2, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'shop_id' => $to_shop_id, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
									$this->db->insert('category', $child_cat_insert);
									$cid = $this->db->insert_id();
									$child_cat_id_arr[] = $cid;
								}
							}
						}
					}
				}

				$shopData = $this->CommonModel->getSingleDataByID('fbc_users_shop', array('shop_id' => $ShopID), 'fbc_user_id,currency_symbol,currency_code');
				$shop_currency = $shopData->currency_symbol ?? $shopData->currency_code;

				if ($import_items_data->parent_id == 0) {
					$productDetail = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'products', array('id' => $import_items_data->product_id), '');
				} else {
					$productDetail = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'products', array('id' => $import_items_data->parent_id), '');
				}

				$buyer_count_array = $_POST['buyer_count'];
				asort($buyer_count_array);

				$sortted_buyer_count_array = [];
				foreach ($buyer_count_array as $key => $val) {
					$sortted_buyer_count_array[] = $val;
				}
				$customer_type_ids = implode(',', array_unique($sortted_buyer_count_array));

				if (isset($productDetail)) {
					$product_code = $productDetail->product_code;
					$product_name = $productDetail->name;
					$product_slug = url_title($product_name);
					$url_key = strtolower($product_slug);
					$PD_Exist = $this->B2BImportModel->getSingleDataByID('products', array('shop_product_id' => $product_id, 'imported_from' => $ShopID, 'remove_flag' => 0), 'id,name');

					if (isset($PD_Exist) && $PD_Exist->id != '') {
						$slugcount = $this->B2BImportModel->productslugcount($product_name, $PD_Exist->id);

						if ($slugcount > 0) {
							$slugcount = $slugcount + 1;
							$url_key = $url_key . "-" . $slugcount;
						} else {
							$url_key = $url_key . "-0";
						}

						/*****---------calculate cost price-----------------------****/
						if ((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount > 0) && $productDetail->price > 0) {
							$RowTotalData = $this->CommonModel->calculate_percent_data($productDetail->price, $FbcUserB2BData->buyin_discount);

							if ($seller_shop_currency_code != $buyer_shop_currency_code) {
								$percent_amount = (isset($price_converted) && $price_converted > 0) ? ($RowTotalData['percent_amount'] * $price_converted) : $RowTotalData['percent_amount'];
								$converted_price = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
								$cost_price = $converted_price - $percent_amount;
							} else {
								$percent_amount = $RowTotalData['percent_amount'];
								$cost_price = $productDetail->price - $percent_amount;
							}
						} else {
							$percent_amount = 0;
							$cost_price = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
						}

						if ($_POST['tax_percent'] !== $_POST['tax_percent_hidden']) {
							$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
							$tax_percent = $_POST['tax_percent'];
							$tax_amount = ($_POST['tax_percent'] / 100) * $price_alias;
							$webshop_price = $tax_amount + $price_alias;
						} else {
							$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
							$tax_percent = $productDetail->tax_percent;
							$tax_amount = ($productDetail->tax_percent / 100) * $price_alias;
							$webshop_price = $tax_amount + $price_alias;
						}

						$webshop_price_final = $Rounded_price_flag == 1 ? round($webshop_price) : $webshop_price;
						$estimate_delivery_time = $product_delivery_duration;

						/***--------Update product start-------------------------------------------------***/
						$updatedata = array(
							'name' => $productDetail->name,
							'product_code' => $product_code,
							'url_key' => $url_key,
							'meta_title' => $productDetail->meta_title,
							'meta_keyword' => $productDetail->meta_keyword,
							'meta_description' => $productDetail->meta_description,
							'search_keywords' => $productDetail->search_keywords,
							'promo_reference' => $productDetail->promo_reference,
							'weight' => $productDetail->weight,
							'sku' => $productDetail->sku,
							'price' => (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price,
							'barcode' => $productDetail->barcode,
							'gender' => $productDetail->gender,
							'base_image' => $productDetail->base_image,
							'description' => $productDetail->description,
							'highlights' => $productDetail->highlights,
							'product_reviews_code' => $productDetail->product_reviews_code,
							'launch_date' => $productDetail->launch_date,
							'estimate_delivery_time' => $estimate_delivery_time,
							'product_return_time' => $productDetail->product_return_time,
							'product_drop_shipment' => $productDetail->product_drop_shipment,
							'product_type' => $product_type,
							'product_inv_type' => $product_inv_type,
							'status' => 1,
							'shop_id' => $ShopID,
							'shop_product_id' => $product_id,
							'shop_price' => $productDetail->price,
							'shop_cost_price' => '',
							'shop_currency' => $shop_currency,
							'fbc_user_id' => $to_fbc_user_id,
							'cost_price' => $cost_price,
							'tax_percent' => $tax_percent,
							'tax_amount' => $tax_amount,
							'webshop_price' => $webshop_price_final,
							'shop_buyin_discount_percent' => $shop_buyin_discount_percent,
							'shop_buyin_del_time_in_days' => $shop_buyin_del_time_in_days,
							'shop_dropship_discount_percent' => $shop_dropship_discount_percent,
							'shop_dropship_del_time_in_days' => $shop_dropship_del_time_in_days,
							'shop_display_catalog_overseas' => $shop_display_catalog_overseas,
							'shop_perm_to_change_price' => $shop_perm_to_change_price,
							'shop_can_increase_price' => $shop_can_increase_price,
							'shop_can_decrease_price' => $shop_can_decrease_price,
							'customer_type_ids' => $customer_type_ids,
							'updated_at' => time(),
							'imported_from' => $ShopID,
							'ip' => $_SERVER['REMOTE_ADDR']
						);
						$where_arr = array('id' => $PD_Exist->id);

						$this->B2BImportModel->updateData('products', $where_arr, $updatedata);

						if ($product_type === 'simple') {

						} elseif ($productDetail->product_type === 'configurable') {
							$slugcount = $this->B2BImportModel->productslugcount($product_name);

							if ($slugcount > 0) {
								$slugcount = $slugcount + 1;
								$url_key = $url_key . "-" . $slugcount;
							} else {
								$url_key = $url_key . "-0";
							}

							$productDetail = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'products', array('id' => $import_items_data->product_id), '');
							$SP_Exist = $this->B2BImportModel->getSingleDataByID('products', array('shop_product_id' => $import_items_data->product_id, 'imported_from' => $ShopID, 'remove_flag' => 0), 'id,name');

							if (isset($SP_Exist) && $SP_Exist->id != '') {
								$simpleProductId = $SP_Exist->id;
								if ($_POST['tax_percent'] != $_POST['tax_percent_hidden']) {
									$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
									$tax_percent = $_POST['tax_percent'];
									$tax_amount = ($_POST['tax_percent'] / 100) * $price_alias;
									$webshop_price = $tax_amount + $price_alias;
								} else {
									$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
									$tax_percent = $productDetail->tax_percent;
									$tax_amount = ($productDetail->tax_percent / 100) * $price_alias;
									$webshop_price = $tax_amount + $price_alias;
								}

								$webshop_price_final = $Rounded_price_flag == 1 ? round($webshop_price) : $webshop_price;

								$updatedata = array(
									'name' => $productDetail->name,
									'meta_title' => $productDetail->meta_title,
									'meta_keyword' => $productDetail->meta_keyword,
									'meta_description' => $productDetail->meta_description,
									'search_keywords' => $productDetail->search_keywords,
									'promo_reference' => $productDetail->promo_reference,
									'weight' => $productDetail->weight,
									'parent_id' => $PD_Exist->id,
									'sku' => $productDetail->sku,
									'price' => (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price,
									'cost_price' => (isset($price_converted) && $price_converted > 0) ? ($productDetail->cost_price * $price_converted) : $productDetail->cost_price,
									'barcode' => $productDetail->barcode,
									'product_type' => 'conf-simple',
									'product_inv_type' => $product_inv_type,
									'status' => 1,
									'launch_date' => $productDetail->launch_date,
									'shop_id' => $ShopID,
									'shop_product_id' => $import_items_data->product_id,
									'shop_price' => $productDetail->price,
									'shop_cost_price' => '',
									'shop_currency' => $shop_currency,
									'fbc_user_id' => $to_fbc_user_id,
									'tax_percent' => $tax_percent,
									'tax_amount' => $tax_amount,
									'webshop_price' => $webshop_price_final,
									'shop_buyin_discount_percent' => $shop_buyin_discount_percent,
									'shop_buyin_del_time_in_days' => $shop_buyin_del_time_in_days,
									'shop_dropship_discount_percent' => $shop_dropship_discount_percent,
									'shop_dropship_del_time_in_days' => $shop_dropship_del_time_in_days,
									'shop_display_catalog_overseas' => $shop_display_catalog_overseas,
									'shop_perm_to_change_price' => $shop_perm_to_change_price,
									'shop_can_increase_price' => $shop_can_increase_price,
									'shop_can_decrease_price' => $shop_can_decrease_price,
									'updated_at' => time(),
									'imported_from' => $ShopID,
									'ip' => $_SERVER['REMOTE_ADDR']
								);

								$where = array('id' => $simpleProductId);
								$this->B2BImportModel->updateData('products', $where, $updatedata);
							} else {

								/*****---------calculate cost price-----------------------****/
								if ((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount > 0) && $productDetail->price > 0) {
									$RowTotalData = $this->CommonModel->calculate_percent_data($productDetail->price, $FbcUserB2BData->buyin_discount);
									if ($seller_shop_currency_code != $buyer_shop_currency_code) {
										$percent_amount = (isset($price_converted) && $price_converted > 0) ? ($RowTotalData['percent_amount'] * $price_converted) : $RowTotalData['percent_amount'];
										$converted_price = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
										$cost_price = $converted_price - $percent_amount;
									} else {
										$percent_amount = $RowTotalData['percent_amount'];
										$cost_price = $productDetail->price - $percent_amount;
									}
								} else {
									$percent_amount = 0;
									$cost_price = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
								}

								if ($_POST['tax_percent'] != $_POST['tax_percent_hidden']) {
									$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
									$tax_percent = $_POST['tax_percent'];
									$tax_amount = ($_POST['tax_percent'] / 100) * $price_alias;
									$webshop_price = $tax_amount + $price_alias;
								} else {
									$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
									$tax_percent = $productDetail->tax_percent;
									$tax_amount = ($productDetail->tax_percent / 100) * $price_alias;
									$webshop_price = $tax_amount + $price_alias;
								}

								$webshop_price_final = $Rounded_price_flag == 1 ? round($webshop_price) : $webshop_price;

								$insertdata = array(
									'name' => $productDetail->name,
									'meta_title' => $productDetail->meta_title,
									'meta_keyword' => $productDetail->meta_keyword,
									'meta_description' => $productDetail->meta_description,
									'search_keywords' => $productDetail->search_keywords,
									'promo_reference' => $productDetail->promo_reference,
									'weight' => $productDetail->weight,
									'parent_id' => $PD_Exist->id,
									'sku' => $productDetail->sku,
									'price' => (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price,
									'cost_price' => $cost_price,
									'barcode' => $productDetail->barcode,
									'product_type' => 'conf-simple',
									'product_inv_type' => $product_inv_type,
									'status' => 1,
									'launch_date' => $productDetail->launch_date,
									'shop_id' => $ShopID,
									'shop_product_id' => $import_items_data->product_id,
									'shop_price' => $productDetail->price,
									'shop_cost_price' => '',
									'shop_currency' => $shop_currency,
									'tax_percent' => $tax_percent,
									'tax_amount' => $tax_amount,
									'webshop_price' => $webshop_price_final,
									'shop_buyin_discount_percent' => $shop_buyin_discount_percent,
									'shop_buyin_del_time_in_days' => $shop_buyin_del_time_in_days,
									'shop_dropship_discount_percent' => $shop_dropship_discount_percent,
									'shop_dropship_del_time_in_days' => $shop_dropship_del_time_in_days,
									'shop_display_catalog_overseas' => $shop_display_catalog_overseas,
									'shop_perm_to_change_price' => $shop_perm_to_change_price,
									'shop_can_increase_price' => $shop_can_increase_price,
									'shop_can_decrease_price' => $shop_can_decrease_price,
									'fbc_user_id' => $to_fbc_user_id,
									'created_at' => time(),
									'imported_from' => $ShopID,
									'ip' => $_SERVER['REMOTE_ADDR']
								);
								$simpleProductId = $this->B2BImportModel->insertData('products', $insertdata);
							}

							if ($simpleProductId) {
								$SP_Inv_Exist = $this->B2BImportModel->getSingleDataByID('products_inventory', array('product_id' => $simpleProductId), 'id,qty,available_qty');

								if (!isset($SP_Inv_Exist) || $SP_Inv_Exist->id == 0) {
									$stock_insert = array('product_id' => $simpleProductId, 'qty' => 0, 'available_qty' => 0, 'min_qty' => 0, 'is_in_stock' => 1);
									$this->B2BImportModel->insertData('products_inventory', $stock_insert);
								}

								$variantMaster = $this->B2BImportModel->getMultiDataByIdSupplier($ShopID, 'products_variants_master', array('product_id' => $product_id), '');

								if (is_array($variantMaster) && count($variantMaster) > 0) {
									foreach ($variantMaster as $variant) {
										$attr_id = $variant->attr_id;
										$seller_attr_id = $variant->attr_id;
										$attrDetails=$this->EavAttributesModel->get_attribute_detail_ownshop_admin($variant->attr_id,$ShopID);
										if (empty($attrDetails)) {
											continue;
										}

										$attr_code = $attrDetails->attr_code;
										$attr_name = $attrDetails->attr_name;
										$attr_description = $attrDetails->attr_description;
										$attr_properties = $attrDetails->attr_properties;

										if ($attrDetails->created_by_type != 0) {
											$attrExist = $this->CommonModel->getSingleDataByID('eav_attributes', array('attr_code' => $attr_code, 'attr_type' => 2, 'shop_id' => $to_shop_id), '');

											if (empty($attrExist)) {
												$insertArr = array('attr_code' => $attr_code, 'attr_name' => $attr_name, 'attr_description' => $attr_description, 'attr_properties' => $attr_properties, 'attr_type' => 2, 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
												$attr_id = $this->CategoryModel->insertData('eav_attributes', $insertArr);
											} else {
												$attr_id = $attrExist->id;
											}
										}

										$variantAttrExist = $this->B2BImportModel->getSingleDataByID('products_variants_master', array('product_id' => $PD_Exist->id, 'attr_id' => $attr_id), 'id');

										if (isset($variantAttrExist) && $variantAttrExist->id != 0) {
											$variantMasterId = $variantAttrExist->id;
											$varaint_master_update = array('product_id' => $PD_Exist->id, 'attr_id' => $attr_id);
											$attr_whr = array('id' => $variantAttrExist->id, 'product_id' => $PD_Exist->id);
											$this->B2BImportModel->updateData('products_variants_master', $attr_whr, $varaint_master_update);
										} else {
											$varaint_master_insert = array('product_id' => $PD_Exist->id, 'attr_id' => $attr_id);
											$this->B2BImportModel->insertData('products_variants_master', $varaint_master_insert);
										}

										$OptionSelected = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'products_variants', array('product_id' => $import_items_data->product_id, 'parent_id' => $product_id, 'attr_id' => $seller_attr_id), 'id,attr_value');

										if (isset($OptionSelected) && $OptionSelected->id != 0) {
											$OptionData = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('id' => $OptionSelected->attr_value, 'attr_id' => $seller_attr_id), '');

											if (isset($OptionData) && $OptionData->id != 0) {
												$attr_value = $OptionData->id;

												if ($OptionData->created_by_type != 0) {
													$optionExist = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('attr_options_name' => $OptionData->attr_options_name, 'shop_id' => $to_shop_id), '');

													if (empty($optionExist)) {
														$insertArr = array('attr_id' => $attr_id, 'attr_options_name' => $OptionData->attr_options_name, 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
														$attr_value = $this->CategoryModel->insertData('eav_attributes_options', $insertArr);
													} else {
														$attr_value = $optionExist->id;
														$updateArr = array('attr_id' => $attr_id, 'attr_options_name' => $OptionData->attr_options_name, 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'updated_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
														$whr_arr = array('id' => $attr_value);
														$this->db->where($whr_arr);
														$this->db->update('eav_attributes_options', $updateArr);
													}
												}

												$productVariant = $this->B2BImportModel->getSingleDataByID('products_variants', array('product_id' => $simpleProductId, 'parent_id' => $PD_Exist->id, 'attr_id' => $attr_id), 'id,attr_value');

												if (isset($productVariant) && $productVariant->id != 0) {
													$pv_update = array('attr_id' => $attr_id, 'attr_value' => $attr_value);
													$whr_pv = array('id' => $productVariant->id);
													$this->B2BImportModel->updateData('products_variants', $whr_pv, $pv_update);
												} else {
													$pv_insert = array('product_id' => $simpleProductId, 'parent_id' => $PD_Exist->id, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
													$this->B2BImportModel->insertData('products_variants', $pv_insert);
												}
											}
										}
									}
								}

								//$import_items_data
								$confGalleryImages = $this->B2BImportModel->getMultiDataByIdSupplier($ShopID, 'products_media_gallery', array('product_id' => $product_id, 'child_id' => $import_items_data->product_id), '');

								if (is_array($confGalleryImages) && count($confGalleryImages) > 0) {

									$oldConfGalleryImages = $this->B2BImportModel->getMultiDataByID('products_media_gallery', array('product_id' => $PD_Exist->id, 'child_id' => $simpleProductId), 'id,image');

									if (isset($oldConfGalleryImages) && count($oldConfGalleryImages) > 0) {
										foreach ($oldConfGalleryImages as $val) {
											$at_whr = array('id' => $val->id);
											$this->B2BImportModel->deleteDataById('products_media_gallery', $at_whr);
										}
									}

									foreach ($confGalleryImages as $val) {
										$copied = $this->copyProductImage($val, $shop_source_bucket, $shop_upload_bucket);

										if($copied === true) {
											$media_insert = array('product_id' => $PD_Exist->id, 'child_id' => $simpleProductId, 'image' => $val->image, 'image_title' => $val->image_title, 'image_position' => $val->image_position, 'is_default' => $val->is_default, 'is_base_image' => $val->is_base_image);
											$this->B2BImportModel->insertData('products_media_gallery', $media_insert);
										}
									}
								}
							}
						}

						if (is_array($cat_id_arr) && count($cat_id_arr) > 0) {
							foreach ($cat_id_arr as $cat_id) {
								$checkCatExist = $this->B2BImportModel->getSingleDataByID('products_category', array('product_id' => $PD_Exist->id, 'category_ids' => $cat_id, 'level' => 0), 'id');

								if (isset($checkCatExist) && $checkCatExist->id != 0) {
									$root_cat_update = array('category_ids' => $cat_id);
									$whr_root_cat = array('product_id' => $PD_Exist->id, 'category_ids' => $cat_id, 'level' => 0);
									$this->B2BImportModel->updateData('products_category', $whr_root_cat, $root_cat_update);
								} else {
									$root_cat_insert = array('product_id' => $PD_Exist->id, 'category_ids' => $cat_id, 'level' => 0);
									$this->B2BImportModel->insertData('products_category', $root_cat_insert);
								}

								$checkbtb_level_zero = $this->B2BImportModel->getSingleDataByID('fbc_users_category_b2b', array('category_id' => $cat_id, 'level' => 0), 'id');

								if (empty($checkbtb_level_zero)) {
									$fbc_cat_insert = array('category_id' => $cat_id, 'level' => 0, 'fbc_user_id' => $to_fbc_user_id);
									$this->B2BImportModel->insertData('fbc_users_category_b2b', $fbc_cat_insert);
								}
							}
						}

						if (is_array($sub_cat_id_arr) && count($sub_cat_id_arr) > 0) {
							foreach ($sub_cat_id_arr as $sub_cat_id) {
								$checkSubCatExist = $this->B2BImportModel->getSingleDataByID('products_category', array('product_id' => $PD_Exist->id, 'category_ids' => $sub_cat_id, 'level' => 1), 'id');
								if (isset($checkSubCatExist) && $checkSubCatExist->id != 0) {
									$sub_cat_update = array('category_ids' => $sub_cat_id);
									$whr_sub_cat = array('product_id' => $PD_Exist->id, 'category_ids' => $sub_cat_id, 'level' => 1);
									$this->B2BImportModel->updateData('products_category', $whr_sub_cat, $sub_cat_update);
								} else {
									$sub_cat_insert = array('product_id' => $PD_Exist->id, 'category_ids' => $sub_cat_id, 'level' => 1);
									$this->B2BImportModel->insertData('products_category', $sub_cat_insert);
								}

								$checkbtb_level_one = $this->B2BImportModel->getSingleDataByID('fbc_users_category_b2b', array('category_id' => $sub_cat_id, 'level' => 1), 'id');

								if (empty($checkbtb_level_one)) {
									$fbc_subcat_insert = array('category_id' => $sub_cat_id, 'level' => 1, 'fbc_user_id' => $to_fbc_user_id);
									$this->B2BImportModel->insertData('fbc_users_category_b2b', $fbc_subcat_insert);
								}
							}
						}

						if (is_array($child_cat_id_arr) && count($child_cat_id_arr) > 0) {

							foreach ($child_cat_id_arr as $child_cat_id) {
								$checkChildCatExist = $this->B2BImportModel->getSingleDataByID('products_category', array('product_id' => $PD_Exist->id, 'category_ids' => $child_cat_id, 'level' => 2), 'id');

								if (isset($checkChildCatExist) && $checkChildCatExist->id != 0) {
									$child_update = array('category_ids' => $child_cat_id);
									$wh_cu = array('level' => 2, 'category_ids' => $cat_id, 'product_id' => $PD_Exist->id);
									$this->B2BImportModel->updateData('products_category', $wh_cu, $child_update);
								} else {
									$child_insert = array('product_id' => $PD_Exist->id, 'category_ids' => $child_cat_id, 'level' => 2);
									$this->B2BImportModel->insertData('products_category', $child_insert);
								}

								$checkbtb_level_two = $this->B2BImportModel->getSingleDataByID('fbc_users_category_b2b', array('category_id' => $cid, 'level' => 2), 'id');
								if (empty($checkbtb_level_two)) {
									$fbc_childcat_insert = array('category_id' => $cid, 'level' => 2, 'fbc_user_id' => $to_fbc_user_id);
									$this->B2BImportModel->insertData('fbc_users_category_b2b', $fbc_childcat_insert);
								}
							}
						}

						$productAttr = $this->B2BImportModel->getMultiDataByIdSupplier($ShopID, 'products_attributes', array('product_id' => $product_id), '');

						if (is_array($productAttr) && count($productAttr) > 0) {
							foreach ($productAttr as $val) {
								$attr_id = $from_attr_id = $val->attr_id;
								$attr_value = $val->attr_value;
								$attrDetails=$this->EavAttributesModel->get_attribute_detail_ownshop_admin($attr_id,$ShopID);
								if (empty($attrDetails)) {
									continue;
								}

								$seller_attr_id = $attr_id;
								$attr_code = $attrDetails->attr_code;
								$attr_name = $attrDetails->attr_name;
								$attr_description = $attrDetails->attr_description;
								$attr_properties = $attrDetails->attr_properties;

								if ($attrDetails->created_by_type != 0) {
									$attrExist = $this->CommonModel->getSingleDataByID('eav_attributes', array('attr_code' => $attr_code, 'attr_type' => 1, 'shop_id' => $to_shop_id), '');
									if (empty($attrExist)) {
										$insertArr = array('attr_code' => $attr_code, 'attr_name' => $attr_name, 'attr_description' => $attr_description, 'attr_properties' => $attr_properties, 'attr_type' => 1, 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
										$attr_id = $this->CategoryModel->insertData('eav_attributes', $insertArr);
									} else {
										$attr_id = $attrExist->id;
									}

									$checkbtb_visiblity = $this->B2BImportModel->getSingleDataByID('fbc_users_attributes_visibility', array('attr_id' => $attr_id), 'id');

									if (empty($checkbtb_visiblity)) {
										$seller_attr_visibility = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'fbc_users_attributes_visibility', array('attr_id' => $seller_attr_id), '');

										$fbc_attr_insert = array(
											'attr_id' => $attr_id,
											'display_on_frontend' => $seller_attr_visibility->display_on_frontend,
											'filterable_with_results' => $seller_attr_visibility->filterable_with_results,
											'created_at' => time(),
											'created_by' => $to_fbc_user_id
										);

										$this->B2BImportModel->insertData('fbc_users_attributes_visibility', $fbc_attr_insert);
									}
								}

								if (!empty($attr_value) && $attr_properties == 5) {
									$AttrOptionSelected = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'products_attributes', array('product_id' => $product_id, 'attr_id' => $seller_attr_id), 'id,attr_value');
									$AttrOptionData = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('id' => $AttrOptionSelected->attr_value, 'attr_id' => $seller_attr_id), '');

									if ($AttrOptionData->created_by_type != 0) {

										$optionExist = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('attr_id' => $attr_id, 'attr_options_name' => $AttrOptionData->attr_options_name, 'shop_id' => $to_shop_id), '');

										if (empty($optionExist)) {
											$attributesData = array(
												'attr_id' => $attr_id,
												'attr_options_name' => $AttrOptionData->attr_options_name,
												'created_by' => $to_fbc_user_id,
												'shop_id' => $to_shop_id,
												'created_by_type' => 1,
												'status' => 1,
												'created_at' => time(),
												'ip' => $_SERVER['REMOTE_ADDR'],
											);

											$this->db->insert('eav_attributes_options', $attributesData);
											$option_id = $this->db->insert_id();
											$attr_value = $option_id;
										} else {
											$attr_value = $optionExist->id;
										}
									}

								} elseif (!empty($attr_value) && $attr_properties == 6) {
									if (strpos($attr_value, ',') !== false) {
										$attr_value_arr = explode(',', $attr_value);
									} else {
										$attr_value_arr[] = $attr_value;
									}

									array_filter($attr_value_arr);
									$attr_value_ids = array();

									if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
										foreach ($attr_value_arr as $attr_value_option) {
											$AttrOptionData = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('id' => $attr_value_option), '');

											if ($AttrOptionData->created_by_type != 0) {
												$optionExist = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('attr_id' => $attr_id, 'attr_options_name' => $AttrOptionData->attr_options_name, 'shop_id' => $to_shop_id), '');

												if (empty($optionExist)) {
													$attributesData = array(
														'attr_id' => $attr_id,
														'attr_options_name' => $AttrOptionData->attr_options_name,
														'created_by' => $to_fbc_user_id,
														'shop_id' => $to_shop_id,
														'created_by_type' => 1,
														'status' => 1,
														'created_at' => time(),
														'ip' => $_SERVER['REMOTE_ADDR'],
													);

													$this->db->insert('eav_attributes_options', $attributesData);
													$option_id = $this->db->insert_id();
													$attr_value_ids[] = $option_id;

												} else {
													$option_id = $optionExist->id;
													$attr_value_ids[] = $option_id;
												}
											}
										}
										$attr_value = implode(',', $attr_value_ids);
									}
								}

								$productAttrExist = $this->B2BImportModel->getSingleDataByID('products_attributes', array('product_id' => $PD_Exist->id, 'attr_id' => $attr_id), 'id');

								if (isset($productAttrExist) && $productAttrExist->id != 0) {
									$attr_update = array('product_id' => $PD_Exist->id, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
									$attr_whr = array('id' => $productAttrExist->id, 'product_id' => $PD_Exist->id);
									$this->B2BImportModel->updateData('products_attributes', $attr_whr, $attr_update);
								} else {
									$attr_insert = array('product_id' => $PD_Exist->id, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
									$this->B2BImportModel->insertData('products_attributes', $attr_insert);
								}
							}
						}

						$galleryImages = $this->B2BImportModel->getMultiDataByIdSupplier($ShopID, 'products_media_gallery', array('product_id' => $product_id, 'child_id' => null), '');

						if (is_array($galleryImages) && count($galleryImages) > 0) {
							$oldGalleryImages = $this->B2BImportModel->getMultiDataByID('products_media_gallery', array('product_id' => $PD_Exist->id, 'child_id' => null), 'id,image');

							if (isset($oldGalleryImages) && count($oldGalleryImages) > 0) {
								foreach ($oldGalleryImages as $val) {
									$at_whr = array('id' => $val->id);
									$this->B2BImportModel->deleteDataById('products_media_gallery', $at_whr);
								}
							}

							foreach ($galleryImages as $val) {
								$copied = $this->copyProductImage($val, $shop_source_bucket, $shop_upload_bucket);

								/* Copy File from images to copyImages folder */
								if ($copied === true) {
									$media_insert = array('product_id' => $PD_Exist->id, 'image' => $val->image, 'image_title' => $val->image_title, 'image_position' => $val->image_position, 'is_default' => $val->is_default, 'is_base_image' => $val->is_base_image);
									$this->B2BImportModel->insertData('products_media_gallery', $media_insert);
								}
							}
						}
					} else {  // isset PD_Exist end
						$PC_Exist = $this->B2BImportModel->getSingleDataByID('products', array('product_code' => $product_code, 'remove_flag' => 0), 'id,name');

						if (isset($PC_Exist) && $PC_Exist->id != '') {
							$slugcount = $this->B2BImportModel->productslugcount($product_name, $PC_Exist->id);
							if ($slugcount > 0) {
								$slugcount = $slugcount + 1;
								$url_key = $url_key . "-" . $slugcount;

							} else {
								$url_key = $url_key . "-0";
							}

							$new_product_code = 'B2B-' . $product_code;
							$new_product_sku = 'B2B-' . $productDetail->sku;
						} else {
							$slugcount = $this->B2BImportModel->productslugcount($product_name);
							if ($slugcount > 0) {
								$slugcount = $slugcount + 1;
								$url_key = $url_key . "-" . $slugcount;
							} else {
								$url_key = $url_key . "-0";
							}
							$new_product_code = $product_code;
							$new_product_sku = $productDetail->sku;
						}

						$estimate_delivery_time = $product_delivery_duration;

						if ($_POST['tax_percent'] != $_POST['tax_percent_hidden']) {
							$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
							$tax_percent = $_POST['tax_percent'];
							$tax_amount = ($_POST['tax_percent'] / 100) * $price_alias;
							$webshop_price = $tax_amount + $price_alias;
						} else {
							$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
							$tax_percent = $productDetail->tax_percent;
							$tax_amount = ($productDetail->tax_percent / 100) * $price_alias;
							$webshop_price = $tax_amount + $price_alias;
						}

						$webshop_price_final = $Rounded_price_flag == 1 ? round($webshop_price) : $webshop_price;

						$insertdata = array(
							'name' => $productDetail->name,
							'product_code' => $new_product_code,
							'url_key' => $url_key,
							'meta_title' => $productDetail->meta_title,
							'meta_keyword' => $productDetail->meta_keyword,
							'meta_description' => $productDetail->meta_description,
							'search_keywords' => $productDetail->search_keywords,
							'promo_reference' => $productDetail->promo_reference,
							'weight' => $productDetail->weight,
							'sku' => $productDetail->sku,
							'price' => (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price,
							'cost_price' => (isset($price_converted) && $price_converted > 0) ? ($productDetail->cost_price * $price_converted) : $productDetail->cost_price,
							'barcode' => $productDetail->barcode,
							'gender' => $productDetail->gender,
							'base_image' => $productDetail->base_image,
							'description' => $productDetail->description,
							'highlights' => $productDetail->highlights,
							'product_reviews_code' => $productDetail->product_reviews_code,
							'launch_date' => $productDetail->launch_date,
							'estimate_delivery_time' => $estimate_delivery_time,
							'product_return_time' => $productDetail->product_return_time,
							'product_drop_shipment' => $productDetail->product_drop_shipment,
							'product_type' => $product_type,
							'product_inv_type' => $product_inv_type,
							'status' => 1,
							'shop_id' => $ShopID,
							'shop_product_id' => $product_id,
							'shop_price' => $productDetail->price,
							'shop_cost_price' => '',
							'shop_currency' => $shop_currency,
							'fbc_user_id' => $to_fbc_user_id,
							'tax_percent' => $tax_percent,
							'tax_amount' => $tax_amount,
							'webshop_price' => $webshop_price_final,
							'shop_buyin_discount_percent' => $shop_buyin_discount_percent,
							'shop_buyin_del_time_in_days' => $shop_buyin_del_time_in_days,
							'shop_dropship_discount_percent' => $shop_dropship_discount_percent,
							'shop_dropship_del_time_in_days' => $shop_dropship_del_time_in_days,
							'shop_display_catalog_overseas' => $shop_display_catalog_overseas,
							'shop_perm_to_change_price' => $shop_perm_to_change_price,
							'shop_can_increase_price' => $shop_can_increase_price,
							'shop_can_decrease_price' => $shop_can_decrease_price,
							'customer_type_ids' => $customer_type_ids,
							'created_at' => time(),
							'imported_from' => $ShopID,
							'ip' => $_SERVER['REMOTE_ADDR']
						);

						$insertedProductID = $this->B2BImportModel->insertData('products', $insertdata);

						if ($insertedProductID) {
							$product_log_insert = array('product_id' => $insertedProductID, 'fbc_user_id' => $to_fbc_user_id, 'shop_id' => $to_shop_id, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
							$this->db->insert('products_logs', $product_log_insert);

							if ($productDetail->product_type === 'simple') {
								$stock_insert = array('product_id' => $insertedProductID, 'qty' => 0, 'available_qty' => 0, 'min_qty' => 0, 'is_in_stock' => 1);
								$this->B2BImportModel->insertData('products_inventory', $stock_insert);

							} elseif ($productDetail->product_type === 'configurable') {
								$productDetail = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'products', array('id' => $import_items_data->product_id), '');

								/*****---------calculate cost price-----------------------****/
								if ((isset($FbcUserB2BData->buyin_discount) && $FbcUserB2BData->buyin_discount > 0) && $productDetail->price > 0) {
									$RowTotalData = $this->CommonModel->calculate_percent_data($productDetail->price, $FbcUserB2BData->buyin_discount);
									if ($seller_shop_currency_code != $buyer_shop_currency_code) {
										$percent_amount = (isset($price_converted) && $price_converted > 0) ? ($RowTotalData['percent_amount'] * $price_converted) : $RowTotalData['percent_amount'];
										$converted_price = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
										if ($converted_price > $percent_amount) {
											$cost_price = $converted_price - $percent_amount;  //debug on
										} else {
											$cost_price = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
										}
									} else {
										$percent_amount = $RowTotalData['percent_amount'];
										$cost_price = $productDetail->price - $percent_amount;
									}
								} else {
									$percent_amount = 0;
									$cost_price = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
								}

								if ($_POST['tax_percent'] !== $_POST['tax_percent_hidden']) {
									$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
									$tax_percent = $_POST['tax_percent'];
									$tax_amount = ($_POST['tax_percent'] / 100) * $price_alias;
									$webshop_price = $tax_amount + $price_alias;
								} else {
									$price_alias = (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price;
									$tax_percent = $productDetail->tax_percent;
									$tax_amount = ($productDetail->tax_percent / 100) * $price_alias;
									$webshop_price = $tax_amount + $price_alias;
								}

								$webshop_price_final = $Rounded_price_flag == 1 ? round($webshop_price) : $webshop_price;

								$insertdata = array(
									'name' => $productDetail->name,
									'parent_id' => $insertedProductID,
									'meta_title' => $productDetail->meta_title,
									'meta_keyword' => $productDetail->meta_keyword,
									'meta_description' => $productDetail->meta_description,
									'search_keywords' => $productDetail->search_keywords,
									'promo_reference' => $productDetail->promo_reference,
									'weight' => $productDetail->weight,
									'sku' => $productDetail->sku,
									'price' => (isset($price_converted) && $price_converted > 0) ? ($productDetail->price * $price_converted) : $productDetail->price,
									'cost_price' => $cost_price,
									'barcode' => $productDetail->barcode,
									'product_type' => 'conf-simple',
									'product_inv_type' => $product_inv_type,
									'status' => 1,
									'launch_date' => $productDetail->launch_date,
									'shop_id' => $ShopID,
									'shop_product_id' => $import_items_data->product_id,
									'shop_price' => $productDetail->price,
									'shop_cost_price' => '',
									'shop_currency' => $shop_currency,
									'fbc_user_id' => $to_fbc_user_id,
									'tax_percent' => $tax_percent,
									'tax_amount' => $tax_amount,
									'webshop_price' => $webshop_price_final,
									'shop_buyin_discount_percent' => $shop_buyin_discount_percent,
									'shop_buyin_del_time_in_days' => $shop_buyin_del_time_in_days,
									'shop_dropship_discount_percent' => $shop_dropship_discount_percent,
									'shop_dropship_del_time_in_days' => $shop_dropship_del_time_in_days,
									'shop_display_catalog_overseas' => $shop_display_catalog_overseas,
									'shop_perm_to_change_price' => $shop_perm_to_change_price,
									'shop_can_increase_price' => $shop_can_increase_price,
									'shop_can_decrease_price' => $shop_can_decrease_price,
									'created_at' => time(),
									'imported_from' => $ShopID,
									'ip' => $_SERVER['REMOTE_ADDR']
								);

								$simpleProductId = $this->B2BImportModel->insertData('products', $insertdata);

								if ($simpleProductId) {
									$stock_insert2 = array('product_id' => $simpleProductId, 'qty' => 0, 'available_qty' => 0, 'min_qty' => 0, 'is_in_stock' => 1);
									$this->B2BImportModel->insertData('products_inventory', $stock_insert2);

									$variantMaster = $this->B2BImportModel->getMultiDataByIdSupplier($ShopID, 'products_variants_master', array('product_id' => $product_id), '');

									if (is_array($variantMaster) && count($variantMaster) > 0) {
										foreach ($variantMaster as $variant) {
											$attr_id = $variant->attr_id;
											$seller_attr_id = $variant->attr_id;
											$attrDetails=$this->EavAttributesModel->get_attribute_detail_ownshop_admin($variant->attr_id,$ShopID);
											if (empty($attrDetails)) {
												continue;
											}

											$attr_code = $attrDetails->attr_code;
											$attr_name = $attrDetails->attr_name;
											$attr_description = $attrDetails->attr_description;
											$attr_properties = $attrDetails->attr_properties;

											if ($attrDetails->created_by_type != 0) {
												$attrExist = $this->CommonModel->getSingleDataByID('eav_attributes', array('attr_code' => $attr_code, 'attr_type' => 2, 'shop_id' => $to_shop_id,), '');

												if (empty($attrExist)) {
													$insertArr = array('attr_code' => $attr_code, 'attr_name' => $attr_name, 'attr_description' => $attr_description, 'attr_properties' => $attr_properties, 'attr_type' => 2, 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
													$attr_id = $this->CategoryModel->insertData('eav_attributes', $insertArr);
												} else {
													$attr_id = $attrExist->id;
												}
											}

											$variantAttrExist = $this->B2BImportModel->getSingleDataByID('products_variants_master', array('product_id' => $insertedProductID, 'attr_id' => $attr_id), 'id');  // by al
											if (isset($variantAttrExist) && $variantAttrExist->id != 0) {
												$variantMasterId = $variantAttrExist->id;
												$varaint_master_update = array('product_id' => $insertedProductID, 'attr_id' => $attr_id);
												$attr_whr = array('id' => $variantAttrExist->id, 'product_id' => $PD_Exist->id);
												$this->B2BImportModel->updateData('products_variants_master', $attr_whr, $varaint_master_update);
											} else {
												$varaint_master_insert = array('product_id' => $insertedProductID, 'attr_id' => $attr_id);
												$this->B2BImportModel->insertData('products_variants_master', $varaint_master_insert);
											}

											$OptionSelected = $this->B2BImportModel->getSingleDataByIdSupplier($ShopID, 'products_variants', array('product_id' => $value->product_id ?? 0, 'parent_id' => $product_id, 'attr_id' => $seller_attr_id), 'id,attr_value');
											if (isset($OptionSelected) && $OptionSelected->id != 0) {

												$OptionData = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('id' => $OptionSelected->attr_value, 'attr_id' => $seller_attr_id), '');
												if (isset($OptionData) && $OptionData->id != 0) {
													$attr_value = $OptionData->id;
													if ($OptionData->created_by_type != 0) {
														$optionExist = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('attr_options_name' => $OptionData->attr_options_name, 'shop_id' => $to_shop_id), '');
														if (empty($optionExist)) {
															$insertArr = array('attr_id' => $attr_id, 'attr_options_name' => $OptionData->attr_options_name, 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
															$attr_value = $this->CategoryModel->insertData('eav_attributes_options', $insertArr);
														} else {
															$attr_value = $optionExist->id;
															$updateArr = array('attr_id' => $attr_id, 'attr_options_name' => $OptionData->attr_options_name, 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'updated_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
															$whr_arr = array('id' => $attr_value);
															$this->db->where($whr_arr);
															$this->db->update('eav_attributes_options', $updateArr);
														}
													}

													$productVariant = $this->B2BImportModel->getSingleDataByID('products_variants', array('product_id' => $simpleProductId, 'parent_id' => $insertedProductID, 'attr_id' => $attr_id), 'id,attr_value');
													if (isset($productVariant) && $productVariant->id != 0) {
														$pv_update = array('attr_id' => $attr_id, 'attr_value' => $attr_value);
														$whr_pv = array('id' => $productVariant->id);
														$this->B2BImportModel->updateData('products_variants', $whr_pv, $pv_update);
													} else {
														$pv_insert = array('product_id' => $simpleProductId, 'parent_id' => $insertedProductID, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
														$this->B2BImportModel->insertData('products_variants', $pv_insert);
													}
												}
											}
										}
									}

									$confGalleryImages = $this->B2BImportModel->getMultiDataByIDSupplier($ShopID, 'products_media_gallery', array('product_id' => $product_id, 'child_id' => $value->product_id), '');

									if (is_array($confGalleryImages) && count($confGalleryImages) > 0) {
										foreach ($confGalleryImages as $val) {
											$copied = $this->copyProductImage($val, $shop_source_bucket, $shop_upload_bucket);

											if ($copied === true) {
												$media_insert = array('product_id' => $insertedProductID, 'child_id' => $simpleProductId, 'image' => $val->image, 'image_title' => $val->image_title, 'image_position' => $val->image_position, 'is_default' => $val->is_default, 'is_base_image' => $val->is_base_image);
												$this->B2BImportModel->insertData('products_media_gallery', $media_insert);
											}
										}
									}
								}
							} // configrable check end

							if (isset($cat_id_arr) && count($cat_id_arr) > 0) {
								foreach ($cat_id_arr as $cat_id) {
									$root_cat_insert = array('product_id' => $insertedProductID, 'category_ids' => $cat_id, 'level' => 0);
									$this->B2BImportModel->insertData('products_category', $root_cat_insert);
									$checkbtb_level_zero = $this->B2BImportModel->getSingleDataByID('fbc_users_category_b2b', array('category_id' => $cat_id, 'level' => 0), 'id');

									if (empty($checkbtb_level_zero)) {
										$fbc_cat_insert = array('category_id' => $cat_id, 'level' => 0, 'fbc_user_id' => $to_fbc_user_id);
										$this->B2BImportModel->insertData('fbc_users_category_b2b', $fbc_cat_insert);
									}
								}
							}

							if (isset($sub_cat_id_arr) && count($sub_cat_id_arr) > 0) {
								foreach ($sub_cat_id_arr as $sub_cat_id) {
									$sub_cat_insert = array('product_id' => $insertedProductID, 'category_ids' => $sub_cat_id, 'level' => 1);
									$this->B2BImportModel->insertData('products_category', $sub_cat_insert);
									$checkbtb_level_one = $this->B2BImportModel->getSingleDataByID('fbc_users_category_b2b', array('category_id' => $sub_cat_id, 'level' => 1), 'id');

									if (empty($checkbtb_level_one)) {
										$fbc_subcat_insert = array('category_id' => $sub_cat_id, 'level' => 1, 'fbc_user_id' => $to_fbc_user_id);
										$this->B2BImportModel->insertData('fbc_users_category_b2b', $fbc_subcat_insert);
									}
								}
							}

							if (isset($child_cat_id_arr) && count($child_cat_id_arr) > 0) {
								foreach ($child_cat_id_arr as $child_cat_id) {
									$child_insert = array('product_id' => $insertedProductID, 'category_ids' => $child_cat_id, 'level' => 2);
									$this->B2BImportModel->insertData('products_category', $child_insert);
									$checkbtb_level_two = $this->B2BImportModel->getSingleDataByID('fbc_users_category_b2b', array('category_id' => $child_cat_id, 'level' => 2), 'id');

									if (empty($checkbtb_level_two)) {
										$fbc_childcat_insert = array('category_id' => $child_cat_id, 'level' => 2, 'fbc_user_id' => $to_fbc_user_id);
										$this->B2BImportModel->insertData('fbc_users_category_b2b', $fbc_childcat_insert);
									}
								}
							}

							$productAttr = $this->B2BImportModel->getMultiDataByIDSupplier($ShopID, 'products_attributes', array('product_id' => $product_id), '');;

							if (is_array($productAttr) && count($productAttr) > 0) {

								foreach ($productAttr as $val) {
									$attr_id = $from_attr_id = $val->attr_id;
									$attr_value = (isset($val->attr_value) && $val->attr_value != '') ? $val->attr_value : '';
									$attrDetails=$this->EavAttributesModel->get_attribute_detail_ownshop_admin($attr_id,$ShopID);
									if (empty($attrDetails)) {
										continue;
									}

									$seller_attr_id = $attr_id;
									$attr_code = $attrDetails->attr_code;
									$attr_name = $attrDetails->attr_name;
									$attr_description = $attrDetails->attr_description;
									$attr_properties = $attrDetails->attr_properties;

									if ($attrDetails->created_by_type != 0) {
										$attrExist = $this->CommonModel->getSingleDataByID('eav_attributes', array('attr_code' => $attr_code, 'attr_type' => 1, 'shop_id' => $to_shop_id), '');
										if (empty($attrExist)) {
											$insertArr = array('attr_code' => $attr_code, 'attr_name' => $attr_name, 'attr_type' => 1, 'attr_description' => $attr_description, 'attr_properties' => $attr_properties, 'shop_id' => $to_shop_id, 'created_by' => $to_fbc_user_id, 'created_by_type' => 1, 'status' => 1, 'created_at' => time(), 'ip' => $_SERVER['REMOTE_ADDR']);
											$attr_id = $this->CategoryModel->insertData('eav_attributes', $insertArr);
										} else {
											$attr_id = $attrExist->id;
										}

										$checkbtb_visiblity = $this->B2BImportModel->getSingleDataByID('fbc_users_attributes_visibility', array('attr_id' => $attr_id), 'id');
										if (empty($checkbtb_visiblity)) {
											$seller_attr_visibility = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'fbc_users_attributes_visibility', array('attr_id' => $seller_attr_id), '');
											$fbc_attr_insert = array(
												'attr_id' => $attr_id,
												'display_on_frontend' => $seller_attr_visibility->display_on_frontend,
												'filterable_with_results' => $seller_attr_visibility->filterable_with_results,
												'created_at' => time(),
												'created_by' => $to_fbc_user_id
											);

											$this->B2BImportModel->insertData('fbc_users_attributes_visibility', $fbc_attr_insert);
										}
									}

									if (!empty($attr_value) && $attr_properties == 5) {
										$AttrOptionSelected = $this->B2BImportModel->getSingleDataByIDSupplier($ShopID, 'products_attributes', array('product_id' => $product_id, 'attr_id' => $seller_attr_id), 'id,attr_value');
										$AttrOptionData = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('id' => $AttrOptionSelected->attr_value, 'attr_id' => $seller_attr_id), '');

										if ($AttrOptionData->created_by_type != 0) {
											$optionExist = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('attr_id' => $attr_id, 'attr_options_name' => $AttrOptionData->attr_options_name, 'shop_id' => $to_shop_id), '');
											if (empty($optionExist)) {
												$attributesData = array(
													'attr_id' => $attr_id,
													'attr_options_name' => $AttrOptionData->attr_options_name,
													'created_by' => $to_fbc_user_id,
													'shop_id' => $to_shop_id,
													'created_by_type' => 1,
													'status' => 1,
													'created_at' => time(),
													'ip' => $_SERVER['REMOTE_ADDR'],
												);

												$this->db->insert('eav_attributes_options', $attributesData);
												$option_id = $this->db->insert_id();
												$attr_value = $option_id;
											} else {
												$attr_value = $optionExist->id;
											}
										}
									} elseif (!empty($attr_value) && $attr_properties == 6) {
										if (strpos($attr_value, ',') !== false) {
											$attr_value_arr = explode(',', $attr_value);
										} else {
											$attr_value_arr[] = $attr_value;
										}

										array_filter($attr_value_arr);
										$attr_value_ids = array();

										if (isset($attr_value_arr) && count($attr_value_arr) > 0) {
											foreach ($attr_value_arr as $attr_value_option) {
												$AttrOptionData = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('id' => $attr_value_option), '');

												if ($AttrOptionData->created_by_type != 0) {
													$optionExist = $this->CommonModel->getSingleDataByID('eav_attributes_options', array('attr_id' => $attr_id, 'attr_options_name' => $AttrOptionData->attr_options_name, 'shop_id' => $to_shop_id), '');

													if (empty($optionExist)) {
														$attributesData = array(
															'attr_id' => $attr_id,
															'attr_options_name' => $AttrOptionData->attr_options_name,
															'created_by' => $to_fbc_user_id,
															'shop_id' => $to_shop_id,
															'created_by_type' => 1,
															'status' => 1,
															'created_at' => time(),
															'ip' => $_SERVER['REMOTE_ADDR'],
														);

														$this->db->insert('eav_attributes_options', $attributesData);
														$attr_value_ids[] = $this->db->insert_id();
													} else {
														$attr_value_ids[] = $optionExist->id;
													}
												}
											}

											$attr_value = implode(',', $attr_value_ids);
										}
									}

									$attr_insert = array('product_id' => $insertedProductID, 'attr_id' => $attr_id, 'attr_value' => $attr_value);
									$this->B2BImportModel->insertData('products_attributes', $attr_insert);
								}
							}

							$galleryImages = $this->B2BImportModel->getMultiDataByIDSupplier($ShopID, 'products_media_gallery', array('product_id' => $product_id, 'child_id' => null), '');

							if (is_array($galleryImages) && count($galleryImages) > 0) {
								foreach ($galleryImages as $val) {
									$copied = $this->copyProductImage($val, $shop_source_bucket, $shop_upload_bucket);

									if($copied === true) {
										$media_insert = array('product_id' => $insertedProductID, 'image' => $val->image, 'image_title' => $val->image_title, 'image_position' => $val->image_position, 'is_default' => $val->is_default, 'is_base_image' => $val->is_base_image);
										$this->B2BImportModel->insertData('products_media_gallery', $media_insert);
									}
								}
							}
						}///if inserted new products
					}    //empty PD_Exist end
				}//isset productDetail close
			}//end foreach
		}

		echo json_encode(array("flag" => 1, "msg" => "Products imported successfully."));
		exit;

	}

	private function copyProductImage($val, string $shop_source_bucket, string $shop_upload_bucket): bool
	{
		foreach(['original', 'thumb', 'medium', 'large'] as $folder){
			$this->s3_filesystem->client->copy(
				$shop_source_bucket,
				'products/' . $folder . '/' . $val->image,
				$shop_upload_bucket,
				'products/' . $folder . '/' . $val->image,
			);
		}

		return true;
	}
}
