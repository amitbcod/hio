<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/get-email-template/{id}', function (Request $request, Response $response) {

		$id = $request->getAttribute('id');
	
	if(isset($id) && $id!=='' && is_numeric($id))
	{

		$fbc_obj=new DbFbcuser();	
	
		$template_data = $fbc_obj->getEmailTemplateById($id);

		if(empty($template_data))
		{
			$template_data = NULL;
		}
	}else{
		$template_data = NULL;
	}
	
	 if($template_data == NULL ){
		$message['statusCode'] = '500';
   		$message['is_success'] = 'false';
		$message['error'] = 'No data found';      
		exit(json_encode($message));
   	}else{
		$message['statusCode'] = '200';
   		$message['is_success'] = 'true';
		$message['data'] = $template_data;
		exit(json_encode($message));
   	}
});


$app->get('/testing', function (Request $request, Response $response) {
		$fbc_obj=new DbFbcuser();	
		$IsEmailExists = $fbc_obj->FbcUserDetailByEmail('testing@gmail.com');
		var_dump($IsEmailExists);
		exit;
		
});
