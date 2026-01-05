<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/webshop/get_theme', function (Request $request, Response $response, $args){

	$error='';

	$webshop_obj = new DbCommonFeature();
	$getTheme = $webshop_obj->getThemeByShopcode();

	if($getTheme == false)
	{
		$error='There is no theme available';
	}

	if($error != '' ){
		$message['statusCode'] = '500';
   		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
   	}else{
		$message['statusCode'] = '200';
   		$message['is_success'] = 'true';
		$message['message'] = 'Theme available';
		$message['themeDetail'] = $getTheme;
		exit(json_encode($message));
   	}


});
