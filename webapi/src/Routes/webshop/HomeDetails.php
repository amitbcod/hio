<?php

use App\Controllers\HomeListingController;
use App\Controllers\ProductListingController;

use Psr\Http\Message\ResponseInterface as Response;

use Psr\Http\Message\ServerRequestInterface as Request;

error_reporting(E_ERROR | E_PARSE);


$app->post('/webshop/newsletter_subscribe', function (Request $request, Response $response, $args){
	$data = $request->getParsedBody();
	extract($data);

	$error='';
	if(empty($email) )
	{
		$error='Please enter all mandatory / compulsory fields.';
	}else{
		$webshop_obj = new DbHomeFeature();
		$Record = $webshop_obj->getDataByEmail($email);
		if($Record!=false){
			if($Record['status']==2){
				$updateData = $webshop_obj->updateDataByEmail($email);
				if($updateData){
					$msg = "Successfully subscribed";
				}
			}else{
				$error = "Already subscribed";
			}

		}else{
			$insertData = $webshop_obj->insertData($email);
			if($insertData){
				$msg = "Successfully subscribed";
			}
		}

	}

	if($error != ''){
		$message['statusCode'] = '200';
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



$app->post('/webshop/get_banners', function (Request $request, Response $response, $args){
		
	$data = $request->getParsedBody();
	extract($data);
	$error='';
	if($banner_type=='')
	{
		$error='Please pass all the mandatory values';

	}else{
		if(!isset($category_id) || (isset($category_id) && $category_id == "")) { $category_id = ""; }
		if(isset($customer_type_id) && $customer_type_id!=''){$customer_type_id=$customer_type_id;}else{$customer_type_id=1;}
		$webshop_obj = new DbHomeFeature();

		$getBanners = $webshop_obj->getShopBanners($banner_type,$category_id,$customer_type_id);
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


$app->get('/webshop/get_static_block/{identifier}[/{lang_code}]', function (Request $request, Response $response, $args){


	$identifier = $args['identifier'];
	$lang_code =  (isset($args['lang_code']) ? $args['lang_code'] : '');

	$error='';

	if($identifier=='')

	{

		$error='Please pass all the mandatory values';



	}else{

		$webshop_obj = new DbHomeFeature();

		$getStaticBlock = $webshop_obj->getStaticBlock($identifier,$lang_code);

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
$app->post('/webshop/ProductWithGifts', HomeListingController::class .':ProductWithGifts');
$app->post('/webshop/hindi_magazines', HomeListingController::class .':hindi_magazines');
$app->post('/webshop/regional_magazine', ProductListingController::class .':regional_magazine');


	
$app->post('/webshop/get_menus', function (Request $request, Response $response, $args){
	$data = $request->getParsedBody();
	extract($data);
	$error='';
	if($Identifier=='')

	{
		$error='Please pass all the mandatory values';


	}else{

		$final_arr = array();
		$webshop_obj = new DbHomeFeature();

		$getStaticBlock = $webshop_obj->getStaticBlock($Identifier);

        $lang_code = $lang_code ?? '';

		if($getStaticBlock == false)

		{

			$error='No static block found';

		}else{

			foreach ($getStaticBlock as $block) {

				$final_arr = array();
				
                switch ((int) $block['menu_type']) {

                    case 1:

                        $customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);

                        if (isset($Identifier) && $Identifier != '' && isset($customer_type_id) && $customer_type_id != '') {
                        	
                            $getAllMenus = $webshop_obj->getAllCategories($block['id'], $Identifier, $customer_type_id, $lang_code);

                        } else {
                        	
                            $getAllMenus = $webshop_obj->getAllCategories($block['id']);

                        }
                        $type = "category_menu";
                        break;
                    case 2:

                        $getAllMenus = $webshop_obj->getCustomMenus($block['id'], $lang_code);
                        $type = "custom_menu";
                        break;
                    default:
                        $error = 'No menu selected';

                        break;

                }

			}

		}

	}



	if($error != '' ){

		$message['statusCode'] = '500';

		$message['is_success'] = 'false';

		$message['message'] = $error;

		exit(json_encode($message));

	}



    $message['statusCode'] = '200';

    $message['is_success'] = 'true';

    $message['message'] = 'Menu available';

    $message['menu_type'] = $type;

    $message['AllMenuLevels'] = $getAllMenus;

    exit(json_encode($message));

});


$app->post('/webshop/get_promo_text_banners', function (Request $request, Response $response, $args){	
	$error='';

		$webshop_obj = new DbHomeFeature();
		$Record = $webshop_obj->promoTextBanners();
		if(!isset($Record)){
			$error = 'Something went wrong';
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

