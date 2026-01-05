<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/webshop/get_cms_page/{identifier}[/{lang_code}]', function (Request $request, Response $response, $args){
	 $identifier = $args['identifier'];
	 $lang_code =  (isset($args['lang_code']) ? $args['lang_code'] : '');

	$error='';
	if($identifier == '')
	{
		$error='Please pass all the mandatory values';

	}else{
		$webshop_obj = new DbCMSFeature();	
		$get_cms_page = $webshop_obj->get_cms_page($identifier,$lang_code);
		if($get_cms_page)
		{
			$CMSPage = $get_cms_page;
			
		}
		else
		{
			$error = 'No CMS page found';
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
		$message['message'] = 'CMS page available';
		$message['cms_page_detail'] = $CMSPage;
		exit(json_encode($message));
	}
});
