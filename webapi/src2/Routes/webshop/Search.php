<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/save_search_term', function (Request $request, Response $response){	

$posted_data = $request->getParsedBody();	
extract($posted_data);	
$error='';	
	$msg = "";
	
	if( empty($shopcode)  || empty($shopid)  || empty($search_term) ){		
		$error='Please enter all mandatory / compulsory fields.';		
	}
	else if(strlen($search_term) < 2){
		$error='Please enter two letters.';	
	}	
	else
	{
		$webshop_obj = new DbSearchFeature();
		$str = strlen($search_term);
		
		$Record = $webshop_obj->getSearchTermBySearch($shopcode,$search_term);
		if($Record!=false && $Record > 0){
			$id = $Record['id'];
			$popularity = $Record['popularity'];
			$update_search = $webshop_obj->update_search_term($shopcode,$id,$popularity);
			$msg = "Search updated.";
		}
		else{
			if($str >= 2){
				$save_search = $webshop_obj->save_search_term($shopcode,$search_term);
				$msg = "Search saved.";
			}	
		}		

	}		
		
	if($error != ''){	
		$message['statusCode'] = '500';	
		$message['is_success'] = 'false';	
		$message['message'] = $error;      	
		exit(json_encode($message));	
	}
	else{	
		$message['statusCode'] = '200';		
		$message['is_success'] = 'true';
		$message['message'] = $msg;	
		exit(json_encode($message));	
	}
	
});


$app->get('/webshop/get_search_terms/{shopcode}/{shopid}/{search_term}', function (Request $request, Response $response, $args){
	 $shopcode 	= $args['shopcode'];
	 $shop_id 	= 	$args['shopid'];
	 $search_term = $args['search_term'];
	 $error='';
	if($shopcode == '' || $shop_id =='' || $search_term == '')
	{
		$error='Please pass all the mandatory values';

	}
	else if(strlen($search_term) < 2){
		$error='Please enter two letters.';	
	}
	else{
		$webshop_obj = new DbSearchFeature();	
		$get_search_terms = $webshop_obj->get_search_terms($shopcode,$search_term);
		if($get_search_terms)
		{
			foreach($get_search_terms as $value){
				$search_term_array[] = $value;
			}
		}
		else
		{
			$error = 'No result found';
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
		$message['message'] = 'Search result';
		$message['search_result'] = $search_term_array;
		exit(json_encode($message));
	}
});

$app->get('/webshop/get_prodcut_nextpre_products/{shopcode}/{shopid}/{product_id}/{customer_type_id}', function (Request $request, Response $response, $args){
 	$shopcode 	= $args['shopcode'];
 	$shop_id 	= 	$args['shopid'];
 	$product_id = $args['product_id'];
	$customer_type_id = $args['customer_type_id'];
	
 	$error='';
	if($shopcode == '' || $shop_id =='' || $product_id == '')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbSearchFeature();	
		$get_product_details = $webshop_obj->getPrevNextproductDetails($shopcode,$shop_id,$product_id,$customer_type_id);
		$final_arr = array();
		if($get_product_details==false)
		{
			$error = 'No result found';
		}else{
			foreach ($get_product_details as $value) {
				if($product_id > $value['id']){

					$arr['prev_arr'] = $value;

				}else if($product_id < $value['id']){

					$arr['next_arr'] = $value;
				}
				$final_arr = $arr;
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
		$message['message'] = 'Prev Next Product result';
		$message['ProductPrevNexList'] = $final_arr;
		exit(json_encode($message));
	}
});



$app->get('/webshop/get_prodcut_nextpre_products_Category/{shopcode}/{shopid}/{product_id}/{customer_type_id}/{categoryID}', function (Request $request, Response $response, $args){
	$shopcode 	= $args['shopcode'];
	$shop_id 	= 	$args['shopid'];
	$product_id = $args['product_id'];
   	$customer_type_id = $args['customer_type_id'];
	$categoryID = $args['categoryID'];   
   
	$error='';
   if($shopcode == '' || $shop_id =='' || $product_id == '')
   {
	   $error='Please pass all the mandatory values';

   }else{
	   $webshop_obj = new DbSearchFeature();	
	   $get_product_details = $webshop_obj->getPrevNextproductDetailsNew($shopcode,$shop_id,$product_id,$customer_type_id,$categoryID);
	   $final_arr = array();
	   if($get_product_details==false)
	   {
		   $error = 'No result found';
	   }else{
		   foreach ($get_product_details as $value) {
			   if($product_id > $value['id']){

				   $arr['prev_arr'] = $value;

			   }else if($product_id < $value['id']){

				   $arr['next_arr'] = $value;
			   }
			   $final_arr = $arr;
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
	   $message['message'] = 'Prev Next Product result';
	   $message['ProductPrevNexList'] = $final_arr;
	   exit(json_encode($message));
   }
});


?>