<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/webshop/shop_livestatus/{shopid}', function (Request $request, Response $response, $args){
	$shopid = $args['shopid'];
	// $data = $request->getParsedBody();
	// extract($data);

	$error='';
	if(empty($shopid) || $shopid =='')
	{
		$error='Please enter a valid shop id';
	}
	else
	{
		$webshop_obj = new DbCommonFeature();
		$getShopStatus = $webshop_obj->getShopLiveStatus($shopid);

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
	}

});
?>
