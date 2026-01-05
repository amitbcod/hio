<?php

namespace App\Controllers;

use DbProductFeature;
use DbProductReviewFeature;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductFiltersController
{
	private DbProductFeature $webshop_obj;
	private DbProductReviewFeature $block_obj;

	public function productNavFilters(Request $request, Response $response, $args){
		$data = $request->getParsedBody();
		extract($data);

		$lang_code ??= '';
		$filter_type ??= '';
		$categoryid ??= '';
		$search_term ??= '';

		if($filter_type == '' && $categoryid=='')
		{
			abort('Please pass all the mandatory values');
		}

		$this->webshop_obj = new DbProductFeature();
		$this->block_obj = new DbProductReviewFeature();

		$featured_ids = '';
		if($filter_type == 'featured'){
			$productBlock = $this->block_obj->getProductsBlock($filter_type);
			$id_string = trim($productBlock['assigned_products'],",");
			$id_arr = explode(",",$id_string);
			$featured_ids = "'".implode("','", $id_arr)."'";
		}

		$product_array = [];

		$attribut_array = $this->getAttributeArray($lang_code, $categoryid, $search_term,$filter_type,$featured_ids);
		$variantArray = $this->getVariantArray($categoryid, $lang_code, $search_term,$filter_type,$featured_ids);

		$product_array['price_range']['min_price_range'] = 0;
		$product_array['price_range']['max_price_range'] = 0;

		if(!empty($variantArray)){
			foreach($variantArray as $k=>$v){
				$unique = array_values(array_unique($v, SORT_REGULAR));
				$unique_arr[$k]=$unique;
			}
			$product_array['variant_listing'] = $unique_arr;
		}else{
			$product_array['variant_listing'] = $variantArray;
		}

		if(!empty($attribut_array) && count($attribut_array)>0){

			foreach($attribut_array as $attr_key=>$attr_val){
				$attr_unique = array_values(array_unique($attr_val, SORT_REGULAR));
				$attr_unique_arr[$attr_key]=$attr_unique;
			}
			$product_array['attribute_listing'] = (is_array($attr_unique_arr) && $attr_unique_arr!='[]')?$attr_unique_arr:NULL;
		}else{
			$product_array['attribute_listing'] = (is_array($attribut_array) && $attribut_array!='[]')?$attribut_array:NULL;
		}

		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Product available';
		$message['productCatalogFilter'] = $product_array;
		exit(json_encode($message));
	}

	private function getVariantArray(string $categoryid, string $lang_code, string $search_term, string $filter_type, string $featured_ids): array
	{
		$variantArray = [];
		$variantProduct = $this->webshop_obj->getFiltersVariantMaster($categoryid, $lang_code);

		if (is_array($variantProduct) && count($variantProduct) > 0 && $variantProduct != false) {
			$variant_value_ids = array_column($variantProduct, 'id');
			$variantOption = $this->webshop_obj->getOptionsByVariantIdForMultiple($variant_value_ids);
			$variant_options_ids = array_group($variantOption, 'attr_id');

			foreach ($variantProduct as $variant) {
				if (isset($variant['multi_attr_name']) && $variant['multi_attr_name'] != '') {
					$attr_name = $variant['multi_attr_name'];
				} else {
					$attr_name = $variant['attr_name'];
				}
				$attr_code = $variant['attr_code'];

				if (isset($variant_options_ids[$variant['id']])) {
					$variantOptionData = $variant_options_ids[$variant['id']];

					if ($variantOptionData != false && count($variantOptionData) > 0) {
						$variant_value_ids = array_column($variantOptionData, 'id');

						$ProductCountVar = $this->webshop_obj->checkProductCountByVariantOption($variant['id'], $variant_value_ids, $categoryid, $search_term, $filter_type, $featured_ids);
						$ProductCountVar = array_key_by($ProductCountVar, 'attr_value');

						foreach ($variantOptionData as $var_val) {
							if (isset($ProductCountVar[$var_val['id']])) {
								$arr['variant_id'] = $variant['id'];
								$arr['attr_value'] = $var_val['id'];
								$arr['attr_options_name'] = $var_val['attr_options_name'];
								$variantArray[$attr_code . "__" . $attr_name][] = $arr;
							}
						}
					}
				}
			}
		}
		return $variantArray;
	}

	private function getAttributeArray(string $lang_code, string $categoryid, string $search_term, string $filter_type, string $featured_ids): array
	{
		$attribut_array = [];
		$attributData = $this->webshop_obj->getFiltersAttributeMasterOfShop($lang_code);

		if (is_array($attributData) && count($attributData) > 0 && $attributData != false) {
			$attr_ids = array_column($attributData, 'id');
			$AttrOption = $this->webshop_obj->getOptionsByVariantIdForMultiple($attr_ids);

			$attr_match_options_ids = array_column($AttrOption, 'attr_id');
			$attr_options_ids = array_group($AttrOption, 'attr_id');

			foreach ($attributData as $attribute) {
				if (isset($attribute['multi_attr_name']) && $attribute['multi_attr_name'] != '') {
					$attr_name = $attribute['multi_attr_name'];
				} else {
					$attr_name = $attribute['attr_name'];
				}
				$attr_code = $attribute['attr_code'];
				$attr_id = $attribute['id'];
				if (in_array($attribute['id'], $attr_match_options_ids, true)) {
					$attrOptionValueData = $attr_options_ids[$attr_id];
					$attrOptionsIds = array_column($attrOptionValueData, 'id');
					if (isset($attrOptionValueData) && count($attrOptionValueData) > 0) {
						$ProductCountMultipleData = [];
						if (isset($attribute['attr_properties']) && $attribute['attr_properties'] != 6) {
							$ProductCountMultiple = $this->webshop_obj->checkProductCountByAttributeMultiple($attr_id, $attrOptionsIds, $categoryid, $search_term, $filter_type, $featured_ids);

							if (isset($ProductCountMultiple) && is_array($ProductCountMultiple)) {
								$ProductCountMultipleData = array_column($ProductCountMultiple, 'attr_value');
							}
							$multiSelectFlag = 0;
						} else {
							$multiSelectFlag = 1;
						}
						foreach ($attrOptionValueData as $attr_val) {
							if ($multiSelectFlag === 1) {
								$ProductCount = (int)$this->webshop_obj->checkProductCountByAttributeOption($attribute['id'], $attr_val['id'], $categoryid, $search_term, $filter_type,$featured_ids);
							} else {
								$ProductCount = 0;
								if (isset($ProductCountMultipleData) && !empty($ProductCountMultipleData) && is_array($ProductCountMultiple)) {
									if (in_array($attr_val['id'], $ProductCountMultipleData)) {
										$ProductCount = 1;
									}
								}
							}
							if ($ProductCount != false && $ProductCount > 0) {
								$arr['variant_id'] = $attribute['id'];
								$arr['attr_value'] = $attr_val['id'];
								$arr['attr_options_name'] = $attr_val['attr_options_name'];
								$attribut_array[$attr_code . "__" . $attr_name][] = $arr;
							}
						}
					}
				}
			}
		}
		return $attribut_array;
	}
}
