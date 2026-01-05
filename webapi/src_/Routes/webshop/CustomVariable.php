<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/webshop/get_custom_variable/{shopcode}/{shopid}/{identifier}', function (Request $request, Response $response, $args){
	$shopcode 	= $args['shopcode'];
	$shop_id 	= $args['shopid'];
	$identifier = $args['identifier'];
	$error='';
	if($shopcode == '' || $shop_id =='' || $identifier == '')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbGlobalFeature();
		$get_custom_variable = $webshop_obj->get_custom_variable($shopcode,$identifier);
		if(!$get_custom_variable)
		{
			$error = 'No such custom variable found';
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
		// $message['message'] = ".";
		$message['custom_variable'] = $get_custom_variable;
		exit(json_encode($message));
   	}

});

$app->get('/webshop/get_custom_variables', function (Request $request, Response $response, $args){

    $webshop_obj = new DbGlobalFeature();
    $custom_variables = $webshop_obj->get_custom_variables();

    if($custom_variables === false)
    {
        abort('No custom variables found');
    }
    $message['statusCode'] = '200';
    $message['is_success'] = 'true';
    $message['custom_variables'] = $custom_variables;

    $response->getBody()->write(json_encode($message));

    return $response;
});
