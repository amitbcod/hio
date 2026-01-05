<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->post('/webshop/register', function (Request $request, Response $response) {
    $posted_data = $request->getParsedBody();
    extract($posted_data);
    $error = '';
    if (empty($password) || empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'Please enter all mandatory / compulsory fields...';
    } else {
        $email = (isset($email) && $email != '') ? $email : '';
        $mobile_no = (isset($mobile_no) && $mobile_no != '') ? $mobile_no : '';
        $webshop_obj = new DbCommonFeature();
        $webmail_obj = new DbEmailFeature();


        $IsEmailExists = $webshop_obj->CustomerDetailsByEmailId($email, $mobile_no);
        if ($IsEmailExists !== false) {
            $error = 'User already registered with this email address OR Mobile number';
        } else {

            $time = time();
            $status = 1;
            $HashPassword = md5($password);
            $insert_user = array(
                "first_name" => $first_name,
                "last_name" => $last_name,
                "phone_prefix" => $phone_prefix,
                "mobile_no" => $mobile_no,
                "email_id" => $email,
                "status" => $status,
                "password" => $HashPassword,
                "country_code" => $country_code,
                "created_at" => $time,
                'ip' => $ip
            );


            $insert_customer = $webshop_obj->insert_customer($insert_user);
            if ($insert_customer != false) {
                $webshop_name = 'Indiamags';
                $site_logo = '';
                $name = $first_name . ' ' . $last_name;
                $email_code = "customer-register-successful";
                $TempVars = array('##CUSTOMERNAME##', '##WEBSHOPNAME##');
                $DynamicVars = array($name, $webshop_name);
                $CommonVars = array($site_logo, $webshop_name);

                $emailSendStatusFlag = $webmail_obj->get_email_code_status($email_code);
                if ($emailSendStatusFlag == 1) {
                    $send_email = $webmail_obj->sendCommonHTMLEmail($email, $email_code, $TempVars, $DynamicVars, $webshop_name, '', $CommonVars, $lang_code);
                }
            } else {
                $error = "Registration failed.";
            }
        }
    }
    if ($error !== '') {
        $message['statusCode'] = '500';
        $message['is_success'] = 'false';
        $message['message'] = $error;
        exit(json_encode($message));
    } else {
        $message['statusCode'] = '200';
        $message['is_success'] = 'true';
        $message['message'] = "Account created successfully.";
        exit(json_encode($message));
    }
});
