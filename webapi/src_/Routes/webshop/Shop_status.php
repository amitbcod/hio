<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/webshop/shop_livestatus', function (Request $request, Response $response, $args){

	$error='';
	$webshop_obj = new DbCommonFeature();
	$getShopStatus = $webshop_obj->getShopLiveStatus();

	if($getShopStatus == false)
	{
		$error='Shop does not exist';
	}
	else if($getShopStatus == "N")
	{
		$error='Shop does not exist';
	}
	if($error != '' ){
		$message['statusCode'] = '500';
		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
	}else{
		$message['statusCode'] = '200';
		$message['is_success'] = 'true';
		$message['message'] = 'Shop exist';
		$message['ShopDetails'] = $getShopStatus;
		exit(json_encode($message));
	}
});
