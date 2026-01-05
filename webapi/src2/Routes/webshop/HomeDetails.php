<?php 
use App\Controllers\HomeListingController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/get_banners', function (Request $request, Response $response, $args){
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode =='' || $shopid=='' || $banner_type=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		if(!isset($category_id) || (isset($category_id) && $category_id == "")) { $category_id = ""; }
		if(isset($lang_code) && $lang_code!=''){$lang_code=$lang_code;}else{$lang_code='';}
		
		$webshop_obj = new DbHomeFeature();	
		$getBanners = $webshop_obj->getShopBanners($shopcode,$shopid,$banner_type,$category_id,$lang_code);
	//	print_r($getBanners);exit;
		if($getBanners == false)
		{	
			$error='No banners found';			
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
		$message['message'] = 'Banners available';
		$message['ShopBannerDetails'] = $getBanners;
		exit(json_encode($message));
	}
	
});

$app->post('/webshop/get_promo_text_banners', function (Request $request, Response $response, $args){	
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if( empty($shopcode) || empty($shopid) || empty($country_code))
	{		
		$error='Please enter all mandatory / compulsory fields.';	
	}else{
		if(isset($lang_code) && $lang_code!=''){$lang_code=$lang_code;}else{$lang_code='';}

		$webshop_obj = new DbHomeFeature();
		
		$Record = $webshop_obj->promoTextBanners($shopcode,$shopid,$country_code,$lang_code);

	}		
		
	if($error != ''){	
		$message['statusCode'] = '500';	
		$message['is_success'] = 'false';	
		$message['message'] = $error;      	
		exit(json_encode($message));	
	}else{	
		$message['statusCode'] = '200';		
		$message['is_success'] = 'true';
		$message['message'] = $Record;	
		exit(json_encode($message));	
	}
	
});








$app->get('/webshop/get_static_block/{shopcode}/{shopid}/{identifier}[/{lang_code}]', function (Request $request, Response $response, $args){
	$shopcode 	= $args['shopcode'];
	$shop_id 	= 	$args['shopid'];
	$identifier = $args['identifier'];
	$lang_code =  (isset($args['lang_code']) ? $args['lang_code'] : '');

	$error='';
	if($shopcode =='' || $shop_id=='' || $identifier=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbHomeFeature();	
		$getStaticBlock = $webshop_obj->getStaticBlock($shopcode,$shop_id,$identifier,$lang_code);
		if($getStaticBlock == false)
		{	
			$error='No static block found';			
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
		$message['message'] = 'Static block available';
		$message['ShopStaticBlock'] = $getStaticBlock;
		exit(json_encode($message));
	}
	
});





$app->post('/webshop/new_arrivals', HomeListingController::class .':newArrivals');

$app->post('/webshop/get_menus', function (Request $request, Response $response, $args){ 
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if($shopcode =='' || $shopid=='' || $Identifier=='')
	{
		$error='Please pass all the mandatory values';

	}else{

		$final_arr = array();

		$webshop_obj = new DbHomeFeature();	
		$getStaticBlock = $webshop_obj->getStaticBlock($shopcode,$shopid,$Identifier); 
		if(isset($lang_code) && $lang_code!=''){$lang_code=$lang_code;}else{$lang_code='';}
		if($getStaticBlock == false)
		{	
			$error='No static block found';			
		}else{
			foreach ($getStaticBlock as $block) {
				$final_arr = array();
				if($block['menu_type']==1){
					
					
					$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
					if(isset($Identifier) && $Identifier !='' && isset($customer_type_id) && $customer_type_id!='' )
					{
						$getAllMenus = $webshop_obj->getAllCategories($shopcode,$shopid,$block['id'], $Identifier,$customer_type_id,$lang_code); 
					}
					else{
						$getAllMenus = $webshop_obj->getAllCategories($shopcode,$shopid,$block['id']); 	
					} 
					$type="category_menu";
				}else if($block['menu_type']==2) {
					$getAllMenus = $webshop_obj->getCustomMenus($shopcode,$shopid,$block['id'],$lang_code); 
					$type="custom_menu";
				}else{
					$error='No menu selected';	
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
		$message['message'] = 'Menu available';
		$message['menu_type'] = $type;
		$message['AllMenuLevels'] = $getAllMenus;
		exit(json_encode($message));
	}
	
});



$app->post('/webshop/newsletter_subscribe', function (Request $request, Response $response, $args){	
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if( empty($shopcode) || empty($shopid) || empty($email) )
	{		
		$error='Please enter all mandatory / compulsory fields.';	
	}else{
		$webshop_obj = new DbHomeFeature();
		
		$Record = $webshop_obj->getDataByEmail($shopcode,$email);
		if($Record!=false){
			if($Record['status']==2){
				$updateData = $webshop_obj->updateDataByEmail($shopcode,$email);
				if($updateData){
					$msg = "Successfully subscribed";
				}
			}else{
				$error = "Already subscribed";
			}

		}else{
			$insertData = $webshop_obj->insertData($shopcode,$email);
			if($insertData){
				$msg = "Successfully subscribed";
			}
		}	

	}		
		
	if($error != ''){	
		$message['statusCode'] = '500';	
		$message['is_success'] = 'false';	
		$message['message'] = $error;      	
		exit(json_encode($message));	
	}else{	
		$message['statusCode'] = '200';		
		$message['is_success'] = 'true';
		$message['message'] = $msg;	
		exit(json_encode($message));	
	}
	
});


?>