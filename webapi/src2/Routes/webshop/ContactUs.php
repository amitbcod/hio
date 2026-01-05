<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/contact_us', function (Request $request, Response $response){
	$posted_data = $request->getParsedBody();
	extract($posted_data);

	if (empty($shopcode)  || empty($shopid)  || empty($email) || empty($name)) {
		abort('Please enter all mandatory / compulsory fields.');
	}

	if(!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)) {
		abort('Please enter a valid Email address.');
	}

	$lang_code =  (isset($lang_code) ? $lang_code : '');

	$Common_obj = new DbCommonFeature();
	$webshopName = $Common_obj->getWebShopName($shopcode, $shopid);
	$webshop_name = $webshopName['org_shop_name'] ?? '';

	$webshop_obj = new DbEmailFeature();

	$content = $content ?? '';
	$order_flag = $order_flag ?? '';
	$order_increment_id = $order_increment_id ?? '';
	$post_lcode = $post_lcode ?? '';
	   
	$languageName='';
	if($post_lcode!='')
	{
	   $webshop_obj = new DbEmailFeature();
	   $LangCodeName = $webshop_obj->getCodeByLanguageName($shopcode,$shopid,$post_lcode);
	   $CodeName = $LangCodeName['name'];
	   $languageName = 'Communication Language: '.$CodeName;
	}
	
	if(isset($order_flag) && !empty($order_flag)){
				$orderflag = 'Yes';
			}else{
				$orderflag = 'No';
			}if(isset($order_increment_id) && !empty($order_increment_id)){
				$orderID = $order_increment_id;
			}else{
				$orderID = '-';
			}

	$TempVars = array('##NAME##','##EMAIL##','##ORDERQESFLAG##','##ORDERID##','##CONTENT##','##COM_LANGUAGE##');
	$DynamicVars = array($name, $email,$orderflag,$orderID,$content,$languageName);
	$CommonVars = array($site_logo, $webshop_name);

	$EmailToAdmin = $webshop_obj->get_custom_variable($shopcode, 'contact_us_email');

	$EmailTo = $EmailToAdmin['value'] ?? 'no-reply@shopinshop.com';

	$identifier='contact_us';
	$emailSendStatusFlag=$webshop_obj->get_email_code_status($shopcode,$identifier);
	if($emailSendStatusFlag==1){
		$get_email = $webshop_obj->sendCommonHTMLEmail($shopcode, $EmailTo, "contact_us", $TempVars, $DynamicVars, '', '', $CommonVars,$lang_code);
	}else{
		$get_email == false;
	}
	if ($get_email == false) {
		abort('Mail Not Sent');
	}

	$save_email = $webshop_obj->save_contact_us(
		$shopcode,
		$email,
		$name,
		$mobile_no ?? '',
		$customer_id ?? '',
		$content,
		$order_flag ?? '',
		$order_increment_id ?? '',
		$post_lcode ?? '', 
	);

	if (!$save_email) {
		abort('Details Not Saved');
		exit;
	}

	$message['statusCode'] = '200';
	$message['is_success'] = 'true';
	$message['message'] = 'Email Sent';
	exit(json_encode($message));
});


$app->post('/webshop/get_communication_lang_select', function (Request $request, Response $response, $args){	
	
	$data = $request->getParsedBody();
	extract($data);
	
	$error='';
	if( empty($shopcode) || empty($shopid) )
	{		
		$error='Please enter all mandatory / compulsory fields.';	
	}else{

		$webshop_obj = new DbEmailFeature();
		$Record = $webshop_obj->getMultiLanguage($shopcode,$shopid);

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