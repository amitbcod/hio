<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->post('/webshop/register', function (Request $request, Response $response){

	// $message = $request->getParsedBody()['email'];

	$posted_data = $request->getParsedBody();
	extract($posted_data);
	// $GC_SECRETE_KEY = "6LeTNPcZAAAAAG6cVuxilV5g8F3GjUB3HDkmvJWE";
	$error='';


	if( empty($shopcode)  || empty($shopid)  || empty($email) || empty($password) || empty($first_name) || empty($last_name)){
		$error='Please enter all mandatory / compulsory fields.';
	} elseif( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)){
		$error='Please enter a valid Email address.';
	} else {
        $lang_code = (isset($lang_code) && $lang_code != '') ? $lang_code : '';	

        $webshop_obj = new DbCommonFeature();

        $rob_verfiy_flag = 'yes';

        $webmail_obj = new DbEmailFeature();
        $IsEmailExists = $webshop_obj->CustomerDetailsByEmailId($shopcode, $email);

        if ($IsEmailExists !== false) {
            $error = 'User already registered with this email address';
        } else {
            $time = time();
            $status = 1;
            //$HashPassword = password_hash($password, PASSWORD_DEFAULT);
            $HashPassword = md5($password);
            $insert_user = array(
                "first_name" => "'" . $first_name . "'",
                "last_name" => "'" . $last_name . "'",
                "email_id" => "'" . $email . "'",
                "status" => "'" . $status . "'",
                "password" => "'" . $HashPassword . "'",
                "country_code" => "'" . $country_code . "'",
                "created_at" => $time,
                'ip' => "'" . $_SERVER['REMOTE_ADDR'] . "'"

            );

            $insert_customer = $webshop_obj->insert_customer($shopcode, $insert_user);
            if ($insert_customer != false) {
                $webshopName = $webshop_obj->getWebShopName($shopcode, $shopid);
                if ($webshopName != false) {
                    $webshop_name = $webshopName['org_shop_name'];
                } else {
                    $webshop_name = '';
                }

                $name = $first_name . ' ' . $last_name;

                $email_code = "customer-register-successful";
                $TempVars = array('##CUSTOMERNAME##', '##WEBSHOPNAME##');
                $DynamicVars = array($name, $webshop_name);
                $CommonVars = array($site_logo, $webshop_name);
                $emailSendStatusFlag=$webmail_obj->get_email_code_status($shopcode,$email_code);
                if($emailSendStatusFlag==1){
                    $send_email = $webmail_obj->sendCommonHTMLEmail($shopcode, $email, $email_code, $TempVars, $DynamicVars, $webshop_name, '', $CommonVars,$lang_code);
                }
                // if($send_email == false)
                // {
                // 	$error = 'Mail Not Send';
                // }
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

    $message['statusCode'] = '200';
    $message['is_success'] = 'true';
    $message['message'] = "Account created successfully.";
    // $message['userdetails'] = array('fbc_user_id'=>$fbc_user_id,'shop_id'=>$shop_id);
    exit(json_encode($message));
});