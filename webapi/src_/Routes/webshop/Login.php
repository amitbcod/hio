<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/login', function (Request $request, Response $response){
	$posted_data = $request->getParsedBody();
	extract($posted_data);

	$error='';
	if(empty($email) || empty($password)){
		$error='Please enter all mandatory / compulsory fields.';
	}else if( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)){
		$error='Please enter a valid Email address.';
	}else{
		$remember = (isset($remember) && $remember!='') ? 'Checked' : '';
		$quote_id = (isset($quote_id) && $quote_id!='') ? $quote_id : '';
		$vat_percent_session = (isset($vat_percent_session) && $vat_percent_session!='') ? $vat_percent_session : '';

		$currency_name =  ((isset($currency_name) && $currency_name!='') ? $currency_name : '');
		$currency_code_session = (isset($currency_code_session) && $currency_code_session!='') ? $currency_code_session : '';
		$currency_conversion_rate = (isset($currency_conversion_rate) && $currency_conversion_rate!='') ? $currency_conversion_rate : '';
		$currency_symbol = (isset($currency_symbol) && $currency_symbol!='') ? $currency_symbol : '';
		$default_currency_flag = (isset($default_currency_flag) && $default_currency_flag!='') ? $default_currency_flag : '';

		$responseData = "Y" ;
		if($responseData == "Y")
		{
			$webshop_obj = new DbCommonFeature();
			$webmail_obj = new DbEmailFeature();

			$UserDetails = $webshop_obj->CustomerDetailsByEmailId($email);
			if($UserDetails==false){
				$error='No user found with this details';
			}else{
				if((isset($UserDetails) && $UserDetails['id'] !='') && ($UserDetails['password'] == md5($password)))
				{
					if($UserDetails['status'] != 1){
						$error = "You are not allowed to login.";
					}else{
						$userDetailsArr = $webshop_obj->commonLoginFunction($UserDetails['id'],$UserDetails['first_name'],$UserDetails['last_name'],$email,$UserDetails['customer_type_id'],$quote_id,$remember,$vat_percent_session,$currency_name,$currency_code_session,$currency_conversion_rate,$default_currency_flag,$currency_symbol);
					}

				}else{
					$error= "Invalid Email or Password.";
				}
			}
		}else{
			$error= "Robot verification failed, please try again.";
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
		$message['message'] = "Logged In successfully.";
		$message['userdetails'] = $userDetailsArr;
		exit(json_encode($message));
   	}

});


$app->post('/webshop/logout', function (Request $request, Response $response){
	$posted_data = $request->getParsedBody();
	extract($posted_data);
	$error='';
	if( empty($shopcode)  || empty($shopid)  || empty($LoginToken)){
		$error='Details Missing!';
	}
	else if($LoginToken != '')
	{
		$time = time();
		$session_id = $LoginToken;
		$webshop_obj = new DbCommonFeature();
		$update_session_time = $webshop_obj->update_session_time($shopcode,$time,$session_id);
		if($update_session_time == 1)
		{
			$error='Session id not found';
		}
		// session_destroy();

	}

	if($error != '' ){
		$message['statusCode'] = '500';
   		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
   	}else{
		$message['statusCode'] = '200';
   		$message['is_success'] = 'true';
		$message['message'] = "Logged out successfully.";
		// $message['userdetails'] = array('LoginToken '=>$LoginToken);
		exit(json_encode($message));
   	}



});

$app->post('/webshop/forgot_password', function (Request $request, Response $response){
	$posted_data = $request->getParsedBody();
	extract($posted_data);
	$error='';
	if( empty($email) || empty($site_url)){
		$error='Please enter all mandatory / compulsory fields.';
	}
	else if( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)){
		$error='Please enter a valid Email address.';
	}
	else
	{
		$webshop_obj = new DbCommonFeature();
		$webmail_obj = new DbEmailFeature();
		$check_email_id  =  $webshop_obj->CustomerDetailsByEmailId($email);
		if(!$check_email_id)
		{
			$error = "User has not registered with this email address." ;
		}else{
			$lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';
		 	$name = $check_email_id['first_name'].' '.$check_email_id['last_name'];
			$id = $check_email_id['id'];
			$data['id'] = $check_email_id['id'];
			$data['token'] = $email;
			$encoded_data = rtrim(base64_encode(json_encode($data)), '=');

			$reset_url = $site_url."customer/reset-password/".$encoded_data;


			$email_code = "customer-reset_password";
			$TempVars=array('##CUSTOMERNAME##','##RESETPASSWORDLINK##','##WEBSHOPNAME##');
			$DynamicVars=array($name, $reset_url, 'India Mags');
			$CommonVars=array('', 'India Mags');
			$emailSendStatusFlag=$webmail_obj->get_email_code_status($email_code);
			if($emailSendStatusFlag==1){
				$send_email=$webmail_obj->sendCommonHTMLEmail($email,$email_code,$TempVars,$DynamicVars,'India Mags','',$CommonVars,$lang_code);
			}
			if($send_email == false)
			{
				$error = 'Mail Not Send';
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
		// $message['message'] = "Logged out successfully.";
		$message['userdetails'] = array('id'=>$id,'Name'=>$name,'Email' => $email,'encoded_data' =>$encoded_data);
		exit(json_encode($message));
   	}


});

$app->post('/webshop/reset_password', function (Request $request, Response $response) {
	$posted_data = $request->getParsedBody();
	extract($posted_data);
	$error='';
	if(empty($email) || empty($password) ){
		$error='Please enter all mandatory / compulsory fields.';
	}
	else if( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)){
		$error='Please enter a valid Email address.';
	}
	else{
		$new_password = md5($password);
		$webshop_obj = new DbCommonFeature();
		$update_password = $webshop_obj->update_password($email,$new_password);
	}

	if($error != '' ){
		$message['statusCode'] = '500';
   		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
   	}else{
		$message['statusCode'] = '200';
   		$message['is_success'] = 'true';
		$message['message'] = "Password changed successfully.";
		exit(json_encode($message));
   	}
});

$app->post('/webshop/autoLoginFromFBCAdmin', function (Request $request, Response $response){
	$posted_data = $request->getParsedBody();
	extract($posted_data);
	$error='';
	if( empty($email) ){
		$error='Please enter all mandatory / compulsory fields.';
	}
	else{
		$previous_QuoteId = (isset($previous_QuoteId) && $previous_QuoteId!='') ? $previous_QuoteId : '';
		$remember = (isset($remember) && $remember!='') ? 'Checked' : '';
		$quote_id = (isset($quote_id) && $quote_id!='') ? $quote_id : '';
		$vat_percent_session = (isset($vat_percent_session) && $vat_percent_session!='') ? $vat_percent_session : '';

		$currency_name =  ((isset($currency_name) && $currency_name!='') ? $currency_name : '');
		$currency_code_session = (isset($currency_code_session) && $currency_code_session!='') ? $currency_code_session : '';
		$currency_conversion_rate = (isset($currency_conversion_rate) && $currency_conversion_rate!='') ? $currency_conversion_rate : '';
		$currency_symbol = (isset($currency_symbol) && $currency_symbol!='') ? $currency_symbol : '';
		$default_currency_flag = (isset($default_currency_flag) && $default_currency_flag!='') ? $default_currency_flag : '';

		$webshop_obj = new DbCommonFeature();

		$UserDetails = $webshop_obj->CustomerDetailsByEmailId($email);
		if($UserDetails==false){
			$error='No user found with this details';
		}else{

			if($UserDetails['status'] != 1){
				$error = "You are not allowed to login.";
			}else{
				if(isset($previous_QuoteId) && $previous_QuoteId != ''){
					$webshop_obj->deleteQuoteOnAutoLogin($previous_QuoteId);
				}
				$userDetailsArr = $webshop_obj->commonLoginFunction($UserDetails['id'],$UserDetails['first_name'],$UserDetails['last_name'],$email,$UserDetails['customer_type_id'],$quote_id,$remember,$vat_percent_session,$currency_name,$currency_code_session,$currency_conversion_rate,$default_currency_flag,$currency_symbol);
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
		$message['message'] = "Logged In successfully.";
		$message['userdetails'] = $userDetailsArr;
		exit(json_encode($message));
   	}

});

$app->post('/webshop/loginWithAuthToken', function (Request $request, Response $response){

	$posted_data = $request->getParsedBody();
	extract($posted_data);
	$error='';
	if( empty($shopcode)  || empty($shopid)  || empty($auth_token) ){
		$error='Please enter all mandatory / compulsory fields.';
	}
	else{
		$webshop_obj = new DbCommonFeature();

		$UserDetails = $webshop_obj->CustomerDetailsByAuthToken($shopcode,$auth_token);

		if($UserDetails==false){
			$error='No user found with this details';
		}else{

			if($UserDetails['status'] != 1){
				$error = "You are not allowed to login.";
			}else{
				$userDetailsArr = $webshop_obj->commonLoginFunction($shopcode,$shopid,$UserDetails['id'],$UserDetails['first_name'],$UserDetails['last_name'],$email = $UserDetails['email_id'],$UserDetails['customer_type_id'],$quote_id ?? '',$remember ?? '',$vat_percent_session ?? '',$currency_name ?? '',$currency_code_session ?? '',$currency_conversion_rate ?? '',$default_currency_flag ?? '',$currency_symbol ?? '');
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
		$message['message'] = "Logged In successfully.";
		$message['userdetails'] = $userDetailsArr;
		exit(json_encode($message));
   	}

});
