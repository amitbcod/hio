<?php 

use Psr\Http\Message\ResponseInterface as Response;

use Psr\Http\Message\ServerRequestInterface as Request;



$app->post('/webshop/get_shop_categories_new', function (Request $request, Response $response, $args){
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode =='' || $shopid=='' || $catlog_id=='' || $product_ids_str== '' )
	{
		$error='Please pass all the mandatory values';

	}else{
		$final_arr = array();
		// $customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
		
		$webshop_obj = new DbSpecialFeature();	
		$browseByCategory = $webshop_obj->getAllCategories($shopcode,$shopid,$catlog_id,$product_ids_str); //,$customer_type_id
		
		if($browseByCategory == false)
		{	
			$error='No category found';			
		}else{
			foreach ($browseByCategory as $cat) {
				$firstLevelCategory = $webshop_obj->firstLevelCategory($shopcode,$shopid,$cat['id']);
				
				if($firstLevelCategory != false)
				{
					foreach ($firstLevelCategory as $cat1) {
						
						$cat['cat_level_1'][] = $cat1;
					}
				}

				$final_arr[] = $cat;
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
		$message['message'] = 'Category available';
		$message['AllCategoryLevels'] = $final_arr;
		exit(json_encode($message));
	}
	
});

$app->post('/webshop/get_product_data_count', function (Request $request, Response $response, $args){
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode =='' ||  $barcode == '')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbSpecialFeature();	
		$product_count = $webshop_obj->get_product_count($shopcode,$barcode);
	
		if($product_count == false)
		{	
			$error='No data found';			
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
		$message['message'] = 'Data found';
		$message['product_count'] = $product_count;
		exit(json_encode($message));
	}
	
});


$app->post('/webshop/get_product_data', function (Request $request, Response $response, $args){
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode =='' ||  $barcode == '')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbSpecialFeature();	
		$product_data = $webshop_obj->get_product_data($shopcode,$barcode);
	
		if($product_data == false)
		{	
			$error='No data found';			
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
		$message['message'] = 'Data found';
		$message['product_data'] = $product_data;
		exit(json_encode($message));
	}
	
});

$app->post('/webshop/catlog_builder_delete', function (Request $request, Response $response, $args){
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode =='' || $catlog_id =='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbSpecialFeature();
		
		$delete_catlog = $webshop_obj->delete_catlog($shopcode, $catlog_id);
	
		if($delete_catlog == false){	
			$error='Something went wrong.';			
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
		$message['message'] = 'Catlog deleted successfully';
		exit(json_encode($message));
	}
	
});
