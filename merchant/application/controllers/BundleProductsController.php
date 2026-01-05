<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class BundleProductsController extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
		$this->load->model('SellerProductModel');
		$this->load->model('BundleProductsModel');
		$this->load->model('CommonModel');
	}


	public function checkBundleProduct(){
		$result='';
		$whereArray='';
		if(isset($_POST)){
				if(!empty($_POST['barcode_code']) && $_POST['barcode_code']!=''){
					$whereArray=array('barcode'=>$_POST['barcode_code'],'remove_flag'=>0,'product_type !='=>'configurable','product_inv_type'=>'buy');
				}elseif(!empty($_POST['sku']) && $_POST['sku']!=''){
					$whereArray=array('sku'=>$_POST['sku'],'remove_flag'=>0,'product_type !='=>'configurable','product_inv_type'=>'buy');
				}
				$result_data=$this->SellerProductModel->getSingleDataByID('products',$whereArray,'*');

				
				if(!empty($result_data) && $result_data!=''){
					$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag($this->session->userdata('ShopID'));
					$product_id=$result_data->id;
					$id= rand ( 1000 , 9999 );
					$sku=$result_data->sku;
					$barcode=$result_data->barcode;
					$product_type=$result_data->product_type;
					if(isset($product_type) && !empty($product_type) && $product_type=='conf-simple'){
						$parent_id=$result_data->parent_id;
					}else{
						$parent_id=0;
					}
					$tax_amount=$result_data->tax_amount;
					
					$productName=$result_data->name;
					$defaultQty='<input type="text" name="bundle_default_qty[]" id="stock_qty_'.$id.'" value="1" class="form-control" onkeypress="return isNumberKey(event);" onblur="calculate_bundle_selling_price_qty('.$id.','.$Rounded_price_flag.');">';
					$sellingPrice='<input type="text" name="price[]" id="price_'.$id.'" value="'.$result_data->price.'" class="form-control" onkeypress="return isNumberKey(event);" onblur="calculate_bundle_webshop_price('.$Rounded_price_flag.','.$id.');" >';
					$tax_percent='<input type="text" name="bundle_tax_percent[]" id="tax_percent_'.$id.'" value="'.$result_data->tax_percent.'" class="form-control" onkeypress="return isNumberKey(event);" onblur="calculate_bundle_webshop_price('.$Rounded_price_flag.','.$id.');" >';
					$webshopPrice='<input type="text" name="bundle_webshop_price[]" id="webshop_price_'.$id.'" value="'.$result_data->webshop_price.'" class="form-control " onkeypress="return isNumberKey(event);" readonly>'; //onkeypress="return isNumberKey(event);" readonly=""
					$position='<input type="number" onkeypress="return isNumberKey(event);" id="bundle_position_'.$id.'" name="bundle_position[]" class="form-control" value="0">';
					$removedData="$(this).closest('tr')";
					$VariantsRestrict='';
					$removed='<input type="button" onClick="remove('.$removedData.','.$Rounded_price_flag.');" value="Remove" >';
					$hiddenValues='	<input type="hidden"  name="bundle_row_type[]"  value="new">
									<input type="hidden" name="bundle_product_type[]"  value="'.$product_type.'">
									<input type="hidden" id="bundle_sku_'.$id.'" name="bundle_sku[]"  value="'.$sku.'">
									<input type="hidden" id="bundle_barcode_'.$id.'" name="bundle_barcode[]"  value="'.$barcode.'">
									<input type="hidden" id="bundle_product_id_'.$id.'" name="bundle_product_id[]"  value="'.$product_id.'">
									<input type="hidden" id="bundle_product_parent_id_'.$id.'" name="bundle_product_parent_id[]"  value="'.$parent_id.'">
									<input type="hidden" id="bundle_variant_options_'.$id.'" name="bundle_variant_options[]"  value="">
									<input type="hidden" id="bundle_tax_amount_'.$id.'" name="bundle_tax_amount[]"  value="'.$tax_amount.'">
									<input type="hidden" class="row_tax_amount" id="tax_amount_'.$id.'" name="tax_amount[]"  value="'.$tax_amount.'">';
					$hiddenQtyTotalValues='<input type="hidden" id="qty_total_'.$id.'"  class="bundleSellingPrice" value="'.$result_data->price.'"> <input type="hidden" id="webshop_qty_total_'.$id.'"  class="bundleWebshopPrice" value="'.$result_data->webshop_price.'">';


					$trData='<tr><td>'.$sku.'</td><td>'.$barcode.'</td><td>'.$productName.'</td><td>'.$VariantsRestrict.'</td><td>'.$defaultQty.'</td><td>'.$sellingPrice.'</td><td>'.$tax_percent.'</td><td>'.$webshopPrice.'</td><td>'.$position.'</td><td>'.$removed.'</td>'.$hiddenValues.$hiddenQtyTotalValues.'</tr>';

					$result=$trData;
					echo json_encode(array('data'=>$result, 'flag'=>1,'product_type'=>$product_type));
					exit;
				}
			}
		echo json_encode(array('status'=>400, 'message'=>"Something Went Wrong."));
		exit;
	}

	

	public function getProductBundleChildSku(){
		$term_requested = $_GET['term'];
		$search_for_barcode_flag=(isset($_GET['search_for_barcode_flag']) && $_GET['search_for_barcode_flag'] != '') ? $_GET['search_for_barcode_flag'] : '';

		$ProductName_Sku = $this->BundleProductsModel->getProductNameBundleSku($term_requested,$search_for_barcode_flag);

		echo json_encode($ProductName_Sku); exit();

	}

	public function getProductChildSkuConfig()
	{
		$term_requested = $_GET['term'];
		$search_for_barcode_flag=(isset($_GET['search_for_barcode_flag']) && $_GET['search_for_barcode_flag'] != '') ? $_GET['search_for_barcode_flag'] : '';
		$ProductName_Sku = $this->BundleProductsModel->getProductNameConfig($term_requested, $search_for_barcode_flag);
		echo json_encode($ProductName_Sku);
		exit();
	}

	public function getBundleProductVariant(){
		$id = $_POST['product_id'];
		$ProductVariant = $this->BundleProductsModel->getProductConfigVariantList($id);
		if(isset($ProductVariant) && count($ProductVariant) >0){
			$finalData=[];
			foreach ($ProductVariant as $variant) {
				$getfnc=''.$id.','.$variant->attr_id;
				$varientListItem='<div class="varientMainItemList"  id="varient_'.$variant->attr_id.'"><input type="checkbox" class="varientListMainItem" name="varient_name[]" value="'.$variant->attr_id.'" id="varientListMainItem" onchange="myVarientItemLists('.$getfnc.')"> '.$variant->attr_name.' <div class="varientMainItemList" id="config-data-inner_'.$variant->attr_id.'"></div></div>';
				array_push($finalData, $varientListItem);
			}
		}
		echo json_encode($finalData); exit();
	}

	public function getBundleProductVariantItemList(){
		if(isset($_POST)){
			$product_id = $_POST['product_id'];
			$varient_id = $_POST['varient_id'];

			$ProductConfigVarientItemList = $this->BundleProductsModel->getBundleProductVariantItemListData($product_id,$varient_id);
			if(isset($ProductConfigVarientItemList) && count($ProductConfigVarientItemList) >0){
				$finalData=[];
				foreach ($ProductConfigVarientItemList as $variant) {
					$varientListItem='<input type="checkbox" class="varientListItem" name="varient_item[]" value="'.$variant->attr_value.'" id="varientListItem_'.$varient_id.'" >'.$variant->attr_options_name.'<br/>';
					array_push($finalData, $varientListItem);
				}
			}
			echo json_encode($finalData); exit();
		}
	}


	public function checkBundleConfigProduct(){
		$result='';
		$whereArray='';
		if(isset($_POST)){
			// if(!empty($_POST['barcode_code']) && $_POST['barcode_code']!=''){
			// 	$whereArray=array('barcode'=>$_POST['barcode_code'],'remove_flag'=>0,'product_type !='=>'configurable');
			// }elseif(!empty($_POST['sku']) && $_POST['sku']!=''){
			// 	$whereArray=array('sku'=>$_POST['sku'],'remove_flag'=>0,'product_type !='=>'configurable');
			// }

			if(!empty($_POST['productId']) && $_POST['productId']!=''){
				//$whereArray=array('id'=>$_POST['productId'],'remove_flag'=>0,'product_type'=>'configurable');
				//parent_id
				$whereArray=array('parent_id'=>$_POST['productId'],'remove_flag'=>0,'product_type !='=>'configurable');

			}

			$result_data=$this->SellerProductModel->getSingleDataByID('products',$whereArray,'*');
			if(!empty($result_data) && $result_data!=''){
				$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag($this->session->userdata('ShopID'));
				// $id=$result_data->id;
				$id= rand ( 1000 , 9999 );
				$product_id=$_POST['productId'];
				$sku='';
				// $sku=$result_data->sku;
				// $barcode=$result_data->barcode;
				$barcode='';
				//$product_type=$result_data->product_type; //configurable
				$product_type='configurable';
				if(isset($product_type) && !empty($product_type) && $product_type=='conf-simple'){
					$parent_id=$result_data->parent_id;
				}else{
					$parent_id=0;
				}
				//if(isset($_POST['finalVarientData']) && !empty){}
				$bundle_variant_options_get=$_POST['finalVarientData'];
				$tax_amount=$result_data->tax_amount;
				$productName=$_POST['sku'];
				$productCode=$_POST['productCode'];
				//$productName='<input type="text" name="productName[]" id="productName_'.$id.'" value="'.$result_data->name.'" class="form-control" onkeypress="return isNumberKey(event);">';

				$defaultQty='<input type="text" name="bundle_default_qty[]" id="stock_qty_'.$id.'" value="1" class="form-control" onkeypress="return isNumberKey(event);" onblur="calculate_bundle_selling_price_qty('.$id.','.$Rounded_price_flag.');">';
				$sellingPrice='<input type="text" name="price[]" id="price_'.$id.'" value="'.$result_data->price.'" class="form-control" onkeypress="return isNumberKey(event);" onblur="calculate_bundle_webshop_price('.$Rounded_price_flag.','.$id.');" >';
				$tax_percent='<input type="text" name="bundle_tax_percent[]" id="tax_percent_'.$id.'" value="'.$result_data->tax_percent.'" class="form-control" onkeypress="return isNumberKey(event);" onblur="calculate_bundle_webshop_price('.$Rounded_price_flag.','.$id.');" >';
				$webshopPrice='<input type="text" name="bundle_webshop_price[]" id="webshop_price_'.$id.'" value="'.$result_data->webshop_price.'" class="form-control " onkeypress="return isNumberKey(event);" readonly>'; //onkeypress="return isNumberKey(event);" readonly=""
				$position='<input type="number" onkeypress="return isNumberKey(event);" id="bundle_position_'.$id.'" name="bundle_position[]" class="form-control" value="0">';
				$removedData="$(this).closest('tr')";
				//$VariantsRestrictData=$bundle_variant_options;
				if(isset($bundle_variant_options_get) && !empty($bundle_variant_options_get)){
					$bundle_variant_options_change = str_replace("'", '"', $bundle_variant_options_get);


					$bundle_variant_options=$this->CommonModel->getAttributesOptions($bundle_variant_options_change);
				
				}else{
					$bundle_variant_options = '';
				}

				$VariantsRestrict=$bundle_variant_options;
				$removed='<input type="button" onClick="remove('.$removedData.','.$Rounded_price_flag.');" value="Remove" >';
				$hiddenValues=' <input type="hidden"  name="bundle_row_type[]"  value="new">
								<input type="hidden" name="bundle_product_type[]"  value="'.$product_type.'">
								<input type="hidden" id="bundle_sku_'.$id.'" name="bundle_sku[]"  value="'.$sku.'">
								<input type="hidden" id="bundle_barcode_'.$id.'" name="bundle_barcode[]"  value="'.$barcode.'">
								<input type="hidden" id="bundle_product_id_'.$id.'" name="bundle_product_id[]"  value="'.$product_id.'">
								<input type="hidden" id="bundle_product_parent_id_'.$id.'" name="bundle_product_parent_id[]"  value="'.$parent_id.'">
								<input type="hidden" id="bundle_variant_options_'.$id.'" name="bundle_variant_options[]"  value="'.$bundle_variant_options_get.'">
								<input type="hidden" id="bundle_tax_amount_'.$id.'" name="bundle_tax_amount[]"  value="'.$tax_amount.'">
								<input type="hidden" class="row_tax_amount" id="tax_amount_'.$id.'" name="tax_amount[]"  value="'.$tax_amount.'">';
								//variant_options

								// $hiddenValuesvariant_options="<input type='hidden' id='bundle_variant_options_".$id."' name='bundle_variant_options[]'  value='.$bundle_variant_options.'>";//variant_options
				$hiddenQtyTotalValues='<input type="hidden" id="qty_total_'.$id.'"  class="bundleSellingPrice" value="'.$result_data->price.'"> <input type="hidden" id="webshop_qty_total_'.$id.'"  class="bundleWebshopPrice" value="'.$result_data->webshop_price.'">';

				

				$trData='<tr><td>'.$productCode.'</td><td>'.$barcode.'</td><td>'.$productName.'</td><td>'.$VariantsRestrict.'</td><td>'.$defaultQty.'</td><td>'.$sellingPrice.'</td><td>'.$tax_percent.'</td><td>'.$webshopPrice.'</td><td>'.$position.'</td><td>'.$removed.'</td>'.$hiddenValues.$hiddenQtyTotalValues.'</tr>';

				$result=$trData;
				echo json_encode(array('data'=>$result, 'flag'=>1,'product_type'=>$product_type));
				exit;
			}
		}
		echo json_encode(array('status'=>400, 'message'=>"Something Went Wrong."));
		exit;
	}

	public function removedBundleProductItem(){
		if(isset($_POST)){
			$bundleId=$_POST['bundleId'];
			if(!empty($bundleId) && $bundleId!=''){
				$resultData=$this->SellerProductModel->removedBundleProductItemById($bundleId);
				if(isset($resultData) && !empty($resultData)){
					// bundle product item data
					$id=$resultData->id;
					$price=$resultData->price;
					$tax_percent=$resultData->tax_percent;
					$tax_amount=$resultData->tax_amount;
					$webshop_price=$resultData->webshop_price;
					$default_qty=$resultData->default_qty;

					// main product table data
					$mproduct_id=$resultData->bundle_product_id;
					$mprice=$resultData->pprice;
					$mcost_price=$resultData->pcost_price;
					$mtax_percent=$resultData->ptax_percent;
					$mtax_amount=$resultData->ptax_amount;
					$mwebshop_price=$resultData->pwebshop_price;


					$uprice= $mprice - ($default_qty * $price);
					$ucost_price=$uprice;
					$utax_amount=$mtax_amount - ($default_qty * $tax_amount);
					$uwebshop_price=$mwebshop_price - ($default_qty * $webshop_price);

					$main_product_update=array(
											'price'=>$uprice,
											'cost_price'=>$ucost_price,
											'tax_amount'=>$utax_amount,
											'webshop_price'=>$uwebshop_price,
											'updated_at'=>time()
										);
					$main_where_arr=array('id'=>$mproduct_id);
					$this->SellerProductModel->updateData('products',$main_where_arr,$main_product_update);
					$products_bundles_where_arr=array('id'=>$id);
					$this->SellerProductModel->deleteDataById('products_bundles',$products_bundles_where_arr);
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
