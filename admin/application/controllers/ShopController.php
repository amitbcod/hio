<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ShopController extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if($this->session->userdata('LoginID')==''){
			redirect(base_url());
		}
		
	   	$this->load->model('CategoryModel');
		$this->load->model('EavAttributesModel');
		
		
		
	}
	
	public function productdetail()
	{
		$data['side_menu']='product_view';
		$data['PageTitle']='Product Detail';
		$product_shop_id=$this->uri->segment(3);
		$product_id=$this->uri->segment(4);
		//echo $product_shop_id.'-------'.$product_id;exit;
		
		if(empty($product_id)){
			redirect('/');
		}else{
			$cur_fbc_user_id	=	$this->session->userdata('LoginID');
			$cur_shop_id		=	$this->session->userdata('ShopID');
			
			$ProductLog=$this->CommonModel->getSingleDataByID('products_logs',array('product_id'=>$product_id,'shop_id'=>$product_shop_id),'');
			if(empty($ProductLog)){
				redirect('/');
			}
			
			$product_created_by=$ProductLog->fbc_user_id;
			$product_shop_id=$ProductLog->shop_id;
			
			$args['shop_id']	=	$product_shop_id;
			$args['fbc_user_id']	=	$product_created_by;
			
			$this->load->model('ShopProductModel');
			$this->ShopProductModel->init($args); 
			
			$shop_upload_path='shop'.$product_shop_id;
			
			$data['fbc_user_id']=$fbc_user_id=$product_created_by;
			$data['shop_id']=$shop_id=$product_shop_id;
			
			$data['CategoryTree']=$this->CategoryModel->get_categories_for_shop($product_shop_id);
			
				$data['ProductData']=$ProductData=$this->ShopProductModel->getSingleDataByID('products',array('id'=>$product_id),'');
				$MainCat=$this->ShopProductModel->getMultiDataById('products_category',array('product_id'=>$product_id,'level'=>0),'category_ids');
				$root_cat_arr=array();
				if(isset($MainCat) && count($MainCat)){
					foreach($MainCat as $val){
						$root_cat_arr[]=$val->category_ids;
						
					}
				}
				$data['cat_level_zero_selected']=$root_cat_arr;
				
				$SubCat=$this->ShopProductModel->getMultiDataById('products_category',array('product_id'=>$product_id,'level'=>1),'category_ids');
				$sub_cat_arr=array();
				if(isset($SubCat) && count($SubCat)){
					foreach($SubCat as $val){
						$sub_cat_arr[]=$val->category_ids;
						
					}
				}
				$data['cat_level_one_selected']=$sub_cat_arr;
				
				
				$TagsCat=$this->ShopProductModel->getMultiDataById('products_category',array('product_id'=>$product_id,'level'=>2),'category_ids');
				$tags_cat_arr=array();
				if(isset($TagsCat) && count($TagsCat)){
					foreach($TagsCat as $val){
						$tags_cat_arr[]=$val->category_ids;
						
					}
				}
				$data['cat_level_two_selected']=$tags_cat_arr;
				
				$ProductAttr=$this->ShopProductModel->getMultiDataById('products_attributes',array('product_id'=>$product_id),'');
				$data['AttributesList']=$ProductAttr;
				
				$ProductMedia=$this->ShopProductModel->getMultiDataById('products_media_gallery',array('product_id'=>$product_id),'');
				$data['ProductMedia']=$ProductMedia;
				
				if($ProductData->product_type=='configurable'){
					$VariantMaster=$this->ShopProductModel->getVariantMasterForProducts($product_id);
					$data['VariantMaster']=$VariantMaster;
					
					$VariantProductsRow=$this->ShopProductModel->getVariantProducts($product_id);
					if(isset($VariantProductsRow->product_ids) && $VariantProductsRow->product_ids!='')
					{
						
						if( strpos($VariantProductsRow->product_ids, ',') !== false ) {
							$VariantProductsIds=explode(',',$VariantProductsRow->product_ids);
						}else{
							$VariantProductsIds[]=$VariantProductsRow->product_ids;
						}	
						$VariantProducts=$this->ShopProductModel->getVariantProductsByIds($VariantProductsIds);
					}else{
						$VariantProducts=array();
					}
					
					$data['VariantProducts']=$VariantProducts;
					
					
				}else{
					$ProductStock=$this->ShopProductModel->getSingleDataByID('products_inventory',array('product_id'=>$product_id),'qty,available_qty');
					$data['ProductStock']=$ProductStock;
					
				}
				$this->load->view('seller/products/product_detail',$data);
			
		}
		
	}



	
}
