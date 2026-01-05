<?php

namespace App\Controllers;

use DbProductFeature;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductFiltersController
{
	private $webshop_obj;
	private $shopcode;
	private $shopid;
	private $customer_type_id = 0;
	private $categoryid;
	private $attr_options_ids;
	private $variant_options_ids;

	public function __invoke(Request $request, Response $response, $args){
	}

	public function productNavFilters(Request $request, Response $response, $args){
			$data = $request->getParsedBody();
			extract($data);
			$error='';
			if(isset($lang_code) && $lang_code!=''){$lang_code=$lang_code;}else{$lang_code='';}
			if($shopcode =='' || $shopid=='' || $categoryid=='')
			{
				abort('Please pass all the mandatory values');
			}
				$options = (isset($options) ? $options : '');
				$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
				$this->shopcode = $shopcode;
				$this->shopid = $shopid;
				$this->categoryid = $categoryid;
				$this->customer_type_id = $customer_type_id ?? 1;
				$this->webshop_obj = new DbProductFeature();
				$product_array = [];		
				$price_array = [];
				$variantArray = [];
				$attribut_array = [];		
				$filtered_array=[];	
				$filtered_variant_array=[];
					$attributData = $this->webshop_obj->getFiltersAttributeMasterOfShop($this->shopcode,$this->shopid,$lang_code);					
					if(is_array($attributData) && count($attributData)>0  && $attributData!=false){	
						$attr_ids = array_column($attributData, 'id');
						$AttrOption = $this->webshop_obj->getOptionsByVariantIdForMultiple($this->shopcode,$this->shopid,$attr_ids);
						$attr_option_ids = array_column($AttrOption, 'id');
						$attr_match_options_ids = array_column($AttrOption, 'attr_id');
						$this->attr_options_ids = array_group($AttrOption, 'attr_id');
						foreach($attributData as $attribute){
							if(isset($attribute['multi_attr_name']) && $attribute['multi_attr_name']!=''){
								$attr_name = $attribute['multi_attr_name'];
							}else{
								$attr_name = $attribute['attr_name'];
							}
							$attr_code = $attribute['attr_code'];
							$attr_id = $attribute['id'];	
							if(in_array($attribute['id'], $attr_match_options_ids, true)){
								$attrOptionValueData = $this->attr_options_ids[$attr_id];
								$attrOptionsIds=array_column($attrOptionValueData, 'id');
								if(isset($attrOptionValueData) && count($attrOptionValueData) > 0 ){
									$multiSelectFlag=0;
									$ProductCountMultipleData=[];
									if(isset($attribute['attr_properties']) && $attribute['attr_properties']!=6){
										$ProductCountMultiple=$this->webshop_obj->checkProductCountByAttributeMultiple($this->shopcode,$this->shopid,$attr_id,$attrOptionsIds,$this->categoryid,$this->customer_type_id);
										if(isset($ProductCountMultiple) && is_array($ProductCountMultiple)){
											$ProductCountMultipleData=array_column($ProductCountMultiple, 'attr_value');
										}
										$multiSelectFlag=0;
									}else{
										$multiSelectFlag=1;
									}
									foreach($attrOptionValueData as $attr_val){
										$value['attr_id']=$attr_id;
										$value['attr_option_id']=$attr_val['id'];
										if(isset($multiSelectFlag) && $multiSelectFlag==1){
											$ProductCount=(int) $this->webshop_obj->checkProductCountByAttributeOption($this->shopcode,$this->shopid,$attribute['id'],$attr_val['id'],$this->categoryid,$this->customer_type_id);
										}else{
											$ProductCount=0;
											if (isset($ProductCountMultipleData) && !empty($ProductCountMultipleData) && is_array($ProductCountMultiple)){
												if(in_array($attr_val['id'], $ProductCountMultipleData)){
													$ProductCount=1;
												}
											}
										}
										if(isset($ProductCount) && $ProductCount!=false && $ProductCount>0){
											$arr['variant_id'] = $attribute['id'];
											$arr['attr_value'] = $attr_val['id'];
											$arr['attr_options_name'] = $attr_val['attr_options_name'];
											$attribut_array[$attr_code."__".$attr_name][] = $arr;
										}
									}
								} 
							}		
						}
					}
					
					$variantProduct = $this->webshop_obj->getFiltersVariantMaster($this->shopcode,$this->shopid,$this->categoryid,$lang_code);					
					if(is_array($variantProduct) && count($variantProduct)>0  && $variantProduct!=false){
						$variant_ids = array_column($variantProduct, 'id');
						$variantOption = $this->webshop_obj->getOptionsByVariantIdForMultiple($this->shopcode,$this->shopid,$variant_ids);
						$variant_option_ids = array_column($variantOption, 'id');
						$variant_match_options_ids = array_column($variantOption, 'attr_id');
						$this->variant_options_ids = array_group($variantOption, 'attr_id');
						foreach ($variantProduct as $variant){
							if(isset($variant['multi_attr_name']) && $attribute['multi_attr_name']!=''){
								$attr_name = $variant['multi_attr_name'];
							}else{
								$attr_name = $variant['attr_name'];
							}
							$attr_code = $variant['attr_code'];
							$variant_id = $variant['id'];

							if(in_array($variant['id'], $variant_match_options_ids, true)){
								$multiVerSelectFlag=0;
								$ProductVarCountMultipleData=[];
								$variantOptionData=$this->variant_options_ids[$variant['id']];
								$varOptionsIds=array_column($variantOptionData, 'id');
								if(isset($variant['attr_properties']) && $variant['attr_properties']!=6){
									$ProductVariantCountMultiple=$this->webshop_obj->checkProductCountByVariantMultiple($this->shopcode,$this->shopid,$variant_id,$varOptionsIds,$this->categoryid,$this->customer_type_id);
									if(isset($ProductVarCountMultipleData) && is_array($ProductVarCountMultipleData)){
										$ProductVarCountMultipleData=array_column($ProductCountMultiple, 'attr_value');
									}
									$multiVerSelectFlag=0;
								}else{
									$multiVerSelectFlag=1;
								}

								if($variantOptionData!=false && count($variantOptionData)>0){
									foreach($variantOptionData as $var_val){
											$valueVar['variant_id']=$variant_id;
											$valueVar['variant_option_id']=$var_val['id'];

											if(isset($multiVerSelectFlag) && $multiVerSelectFlag==1){
												$ProductCountVar=(int) $this->webshop_obj->checkProductCountByVariantOption($this->shopcode,$this->shopid,$variant['id'],$var_val['id'],$this->categoryid,$customer_type_id);
											}else{
												$ProductCountVar=0;
												if (isset($ProductVarCountMultipleData) && !empty($ProductVarCountMultipleData) && is_array($ProductVarCountMultipleData)){
													if(in_array($var_val['id'], $ProductVarCountMultipleData)){
														$ProductCountVar=1;
													}
												}
											}

											if($ProductCountVar!=false && $ProductCountVar>0){
												$arr['variant_id'] = $variant['id'];
												$arr['attr_value'] = $var_val['id'];
												$arr['attr_options_name'] = $var_val['attr_options_name'];
												$variantArray[$attr_code."__".$attr_name][] = $arr;
											 }
									}
								}
							}
						}
					}	
					
					$product_array['price_range']['min_price_range'] = 0;
					$product_array['price_range']['max_price_range'] = 0;
					
					if(!empty($variantArray) && is_array($variantArray)){
						foreach($variantArray as $k=>$v){
							$unique = array_values(array_unique($v, SORT_REGULAR));
							$unique_arr[$k]=$unique;
						}	
						$product_array['variant_listing'] = $unique_arr;
					}else{
						$product_array['variant_listing'] = $variantArray;
					}
					
					if(!empty($attribut_array) && is_array($attribut_array)  && count($attribut_array)>0){
						
						foreach($attribut_array as $attr_key=>$attr_val){
							$attr_unique = array_values(array_unique($attr_val, SORT_REGULAR));
							$attr_unique_arr[$attr_key]=$attr_unique;
						}	
						$product_array['attribute_listing'] = (is_array($attr_unique_arr) && $attr_unique_arr!='[]')?$attr_unique_arr:NULL;
					}else{
						$product_array['attribute_listing'] = (is_array($attribut_array) && $attribut_array!='[]')?$attribut_array:NULL;
					}		
			if($error != '' ){
				$message['statusCode'] = '500';
				$message['is_success'] = 'false';
				$message['message'] = $error;      
				exit(json_encode($message));
			}else{
				$message['statusCode'] = '200';
				$message['is_success'] = 'true';
				$message['message'] = 'Product available';
				$message['productCatalogFilter'] = $product_array;
			}
				exit(json_encode($message));
	}
}
