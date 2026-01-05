<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->post('/webshop/register', function (Request $request, Response $response){

	$posted_data = $request->getParsedBody();
	extract($posted_data);
	$error='';
	if(empty($email) || empty($password) || empty($first_name) || empty($last_name)){
		$error='Please enter all mandatory / compulsory fields.';
	} elseif( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)){
		$error='Please enter a valid Email address.';
	}
     else {
        $lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';

        $webshop_obj = new DbCommonFeature();
		$webmail_obj = new DbEmailFeature();
        $IsEmailExists = $webshop_obj->CustomerDetailsByEmailId($email);

        if ($IsEmailExists !== false) {
            $error = 'User already registered with this email address';
        } else {

            $time = time();
            $status = 1;
            $HashPassword = md5($password);
            $insert_user = array(
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email_id" => $email,
                "status" => $status,
                "password" => $HashPassword,
                "country_code" => $country_code,
                "created_at" => $time,
                'ip' => $_SERVER['REMOTE_ADDR']
            );



            $insert_customer = $webshop_obj->insert_customer($insert_user);

            if ($insert_customer != false) {
                $webshop_name = 'India Mags';
                $site_logo = '';
                $name = $first_name . ' ' . $last_name;

                $email_code = "customer-register-successful";
                $TempVars = array('##CUSTOMERNAME##', '##WEBSHOPNAME##');
                $DynamicVars = array($name, $webshop_name);
                $CommonVars = array($site_logo, $webshop_name);

                
                $emailSendStatusFlag=$webmail_obj->get_email_code_status($email_code);


                if($emailSendStatusFlag==1){
                    $send_email = $webmail_obj->sendCommonHTMLEmail($email, $email_code, $TempVars, $DynamicVars, $webshop_name, '', $CommonVars,$lang_code);
                }
            } else {
                $error = "Registration failed.";
            }
        }
    }

	if($error !== '' ){
		$message['statusCode'] = '500';
   		$message['is_success'] = 'false';
		$message['message'] = $error;
		exit(json_encode($message));
   	}
    else{
        $message['statusCode'] = '200';
        $message['is_success'] = 'true';
        $message['message'] = "Account created successfully.";
        exit(json_encode($message));
    }


});
