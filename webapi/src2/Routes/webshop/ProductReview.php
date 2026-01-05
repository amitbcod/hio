<?php 
use App\Controllers\HomeListingController;
use App\Controllers\ProductFiltersController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/add_product_review', function (Request $request, Response $response, $args){
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode=='' || $shopid=='' || $LoginToken=='' || $LoginID=='' || $product_id=='' || $rating=='' || $reviews=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbProductReviewFeature();	
		$addProductReview = $webshop_obj->addProductReview($shopcode,$shopid,$LoginToken,$LoginID,$product_id,$rating,$reviews);
		
		if($addProductReview == false)
		{	
			$error='Error while adding review. please try again.';			
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;      
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Review added successfully, it will show up in 5-10 minutes.';
		$message['lastInsertId'] = $addProductReview;
		exit(json_encode($message));
	}
	
});

$app->post('/webshop/productReview_notification', function (Request $request, Response $response, $args){	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode=='' || $shopid=='' || $product_link=='' || $product_name=='' || $rating=='' || $review_content=='' || $customer_id =='' || $review_date=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$lang_code =  (isset($lang_code) ? $lang_code : '');

		$Common_obj = new DbCommonFeature();
		$webshopName = $Common_obj->getWebShopName($shopcode,$shopid);
		if($webshopName!=false){
			$webshop_name = $webshopName['org_shop_name'];
		}else{
			$webshop_name = '';
		}
		
		$webshop_obj = new DbEmailFeature();	
		$template_code = "product_reviews";
		
		$customerData = $Common_obj->getCustomerDetailById($shopcode,$customer_id);
		$customer_name = $customerData['first_name']." ".$customerData['last_name'];	
		
		$TempVars=array('##RATING##','##REVIEW##','##PRODUCTNAME##','##PRODUCTLINK##','##CUSTOMERNAME##','##DATE##');
		$DynamicVars=array($rating, $review_content,$product_name,$product_link,$customer_name,$review_date);
		$CommonVars=array($site_logo, $webshop_name);
		
		$EmailToAdmin=$webshop_obj->get_custom_variable($shopcode,'review_contact_recipient');
		
		if($EmailToAdmin==false){
			$EmailTo='no-reply@shopinshop.co';
		}else{
			$EmailTo=$EmailToAdmin['value'];
			$commanseperated = (explode(",",$EmailTo));
			$emailNotSentFlag = 0;
			foreach($commanseperated as $email){
				$emailSendStatusFlag=$webmail_obj->get_email_code_status($shopcode,$template_code);
				if($emailSendStatusFlag==1){
					$get_email =  $webshop_obj->sendCommonHTMLEmail($shopcode,$email,$template_code,$TempVars,$DynamicVars,'','',$CommonVars,$lang_code);
					if($get_email == false){
						$emailNotSentFlag = 1;
					}
				}
			}
		}
		
		if($emailNotSentFlag == 1)
		{
			$error = 'Mail Not Send' ;
		}
	}

	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;      
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Review notification sent.';
		
		exit(json_encode($message));
	}
	
});

$app->post('/webshop/get_product_reviews', function (Request $request, Response $response, $args){
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode=='' || $shopid=='' || $product_id=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbProductReviewFeature();	
		$idsByReviewCode = $webshop_obj->productidsByReviewCode($shopcode,$shopid,$product_id);
		
		$review_id = (isset($review_id)) ? $review_id : '';
		$limit = (isset($limit)) ? $limit : '';
		
		if($idsByReviewCode === false){
			$error='There is something wrong!';
		}else{
			$review_array = array();
			if(empty($idsByReviewCode)){
				$getProductReview = $webshop_obj->getProductReviews($shopcode,$shopid,$product_id,$limit,$review_id);
				//echo "<pre>";print_r($getProductReview);//exit;
				if($getProductReview == false)
				{	
					$error='Error while getting review.';
				}else{
					foreach($getProductReview as $review){
						$revw_date = date("j F Y",$review['created_at']);
						$rev_arr['id'] = $review['id'];
						$rev_arr['product_id'] = $review['product_id'];
						$rev_arr['rating'] = $review['rating'];
						$rev_arr['review'] = $review['review'];
						$rev_arr['reviwedby'] = $review['first_name'].' '.$review['last_name'];
						$rev_arr['reviewed_on'] = $revw_date;
						
						$review_array[] = $rev_arr;
					}
				}
			}else{
				foreach($idsByReviewCode as $ids){
					$p_id[] = $ids['id'];
				}
				$getProductReview = $webshop_obj->getProductReviews($shopcode,$shopid,$p_id,$limit,$review_id);
				if($getProductReview == false)
				{	
					$error='Error while getting review.';
				}else{
					foreach($getProductReview as $review){
						$revw_date = date("j F Y",$review['created_at']);
						$rev_arr['id'] = $review['id'];
						$rev_arr['product_id'] = $review['product_id'];
						$rev_arr['rating'] = $review['rating'];
						$rev_arr['review'] = $review['review'];
						$rev_arr['reviwedby'] = $review['first_name'].' '.$review['last_name'];
						$rev_arr['reviewed_on'] = $revw_date;
						
						$review_array[] = $rev_arr;
					}
				}
			}
		}
			
	}
	
			
	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;      
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Review data available';
		$message['productReviewsList'] = $review_array;
		exit(json_encode($message));
	}
	
});
	
$app->post('/webshop/get_product_blocks', HomeListingController::class .':featuredProducts');

$app->post('/webshop/get_catalog_filters_old', function (Request $request, Response $response, $args){
	
	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if($shopcode =='' || $shopid=='' || $categoryid=='')
	{
		$error='Please pass all the mandatory values';
	}else{
	
		$options = (isset($options) ? $options : '');
		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
		
		$webshop_obj = new DbProductFeature();	
		$product_listing = $webshop_obj->productListing($shopcode,$shopid,$categoryid,$options,$customer_type_id);
		
		$product_array = array();
		if($product_listing == false)
		{	
			$error='New product not available';			
		}else{
			$price_array = array();
			$variantArray = array();
			$attribut_array = array();
			
			
			
			
			$attributData = $webshop_obj->getFiltersAttributeMasterOfShop($shopcode,$shopid);
			if(is_array($attributData) && count($attributData)>0  && $attributData!=false){
				
				foreach($attributData as $attribute){
					$attr_name = $attribute['attr_name'];
					
					$attr_id = $attribute['attr_name'];
					// print_r($variant);
					$AttrOption = $webshop_obj->getOptionsByVariantId($shopcode,$shopid,$attribute['id']);
					
					if($AttrOption!=false){
						foreach($AttrOption as $attr_val){
							
							$ProductCount=$webshop_obj->checkProductCountByAttributeOption($shopcode,$shopid,$attribute['id'],$attr_val['id'],$categoryid,$customer_type_id);
							if($ProductCount!=false && $ProductCount>0){
								$arr['variant_id'] = $attribute['id'];
								$arr['attr_value'] = $attr_val['id'];
								$arr['attr_options_name'] = $attr_val['attr_options_name'];
								$attribut_array[$attr_name][] = $arr;
							}
						}
					}
					/*
					$attr['id'] = $attribute['id'];
					$attr['attr_value'] = $attribute['attr_value'];
					
					$attribut_array[$attr_name][] = $attr;
					*/
					
				}
			}
			
			$variantProduct = $webshop_obj->getFiltersVariantMaster($shopcode,$shopid,$categoryid);
			
			if(is_array($variantProduct) && count($variantProduct)>0  && $variantProduct!=false){
				foreach ($variantProduct as $variant) {
					$attr_name = $variant['attr_name'];
					// print_r($variant);
					$variantOption = $webshop_obj->getOptionsByVariantId($shopcode,$shopid,$variant['id']);
					
					if($variantOption!=false){
						foreach($variantOption as $var_val){
							
							$ProductCount=$webshop_obj->checkProductCountByVariantOption($shopcode,$shopid,$variant['id'],$var_val['id'],$categoryid,$customer_type_id);
							if($ProductCount!=false && $ProductCount>0){
								$arr['variant_id'] = $variant['id'];
								$arr['attr_value'] = $var_val['id'];
								$arr['attr_options_name'] = $var_val['attr_options_name'];
								$variantArray[$attr_name][] = $arr;
							}
						}
					}
				}
			}
					
				
			foreach($product_listing as $value)
			{
				$product_id=$value['id'];
				if($value['product_type'] == 'configurable'){
					$minPrice = $webshop_obj->getMinPrice($shopcode,$shopid,$value['id']);
					if($minPrice!=false){
					$price_arr['min_price'] = $minPrice['min_price'];
					$price_arr['max_price'] = $minPrice['max_price'];
					}
					
				}else{
					$price_arr['min_price'] = $value['webshop_price'];
					$price_arr['max_price'] = $value['webshop_price'];
				}
				
				$price_array[] = $price_arr;
				
				
				
			}
			
			$prices = array_column($price_array, 'min_price');
			$max_prices = array_column($price_array, 'max_price');
			
			$product_array['price_range']['min_price_range'] = min($prices);
			$product_array['price_range']['max_price_range'] = max($max_prices);
			
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
		}
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
	
});


$app->post('/webshop/get_catalog_filters', ProductFiltersController::class .':productNavFilters');
	
?>