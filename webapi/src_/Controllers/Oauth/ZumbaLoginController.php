<?php

namespace App\Controllers\Oauth;

use DbCommonFeature;
use DbOauthLoginModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ZumbaLoginController
{
    private $OauthLoginModel;
    private $CommonFeature;

    public function __construct(){
        $this->OauthLoginModel = new DbOauthLoginModel();
        $this->CommonFeature = new DbCommonFeature();
    }

    public function attempt_login(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $token = $this->getAccessTokenFromCode($data['code'], $data['redirect_uri']);

        if(!isset($token->access_token)){
            abort("No good result from Zumba");
        }

        $user_details = $this->getUserDetails($token);
        $license_details = $this->getLicenseDetails($token);

        $oauth_user = $this->OauthLoginModel->find_oauth_login_by_oauth_id($data['shopcode'], 'zumba', $user_details->id);

        if(!is_null($oauth_user)){
            $oauth_user = $this->OauthLoginModel->update_oauth_login($data['shopcode'], $oauth_user['id'], $token, $user_details, $license_details);
        }

        if(is_null($oauth_user)){
            $oauth_user = $this->OauthLoginModel->create_oauth_login($data['shopcode'], 'zumba', $token, $user_details, $license_details);
        }

        if(!is_null($oauth_user['customer_id'])){
            $customer = $this->CommonFeature->getCustomerDetailById($data['shopcode'], $oauth_user['customer_id']);

            if($customer['customer_type_id'] < 3) {
                $this->CommonFeature->update_customer_values($data['shopcode'], $customer['id'], [
                    'customer_type_id' => $user_details->isZES ? 8 : ($user_details->isZIN || $user_details->isPreZIN || $user_details->isSYNC ? 3 : 2),
                    'updated_at' => time(),
                ]);
            }

            $message['statusCode'] = '200';
            $message['is_success'] = 'true';
            $message['action'] = 'redirect_to_login';
            $message['customer_id'] = $oauth_user['customer_id'];
            $message['customer_email'] = base64_encode($customer['email_id']);
            exit(json_encode($message));
        }

        $message['statusCode'] = '200';
        $message['is_success'] = 'true';
        $message['action'] = 'request_user_email';
        $message['instructor_id'] = $user_details->id;
        exit(json_encode($message));
    }

    public function confirm_user_email(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $email = trim($data['email']);
        $instructor_id = $data['instructor_id'];

        $customer = $this->CommonFeature->CustomerDetailsByEmailId($data['shopcode'], $email);

        $oauth_login = $this->OauthLoginModel->find_oauth_login_by_oauth_id($data['shopcode'], 'zumba', $instructor_id);
        $user_details = json_decode($oauth_login['user_details']);

        if($customer === false){
            $this->CommonFeature->insert_customer($data['shopcode'], [
                'first_name' => $user_details->first_name,
                'last_name' => $user_details->last_name,
                'email_id' => $email,
                'password' => 'ZUMBA_LOGIN',
                'customer_type_id' => $user_details->isZES ? 8 : ($user_details->isZIN  || $user_details->isPreZIN || $user_details->isSYNC ? 3 : 2),
                'status' => 1,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            $customer = $this->CommonFeature->CustomerDetailsByEmailId($data['shopcode'], $email);
        } else {
            if($customer['customer_type_id'] < 3) {
                $this->CommonFeature->update_customer_values($data['shopcode'], $customer['id'], [
                    'customer_type_id' => $user_details->isZES ? 8 : ($user_details->isZIN || $user_details->isPreZIN || $user_details->isSYNC ? 3 : 2),
                    'updated_at' => time(),
                ]);
            }
        }

        $this->OauthLoginModel->update_oath_login_customer_id($data['shopcode'], $instructor_id, $customer['id']);

        $message['statusCode'] = '200';
        $message['is_success'] = 'true';
        $message['action'] = 'redirect_to_login';
        $message['customer_id'] = $customer['id'];
        $message['customer_email'] = base64_encode($customer['email_id']);
        exit(json_encode($message));
    }


    private function getAccessTokenFromCode($code, $redirect_uri)
    {
        $postfields = [
            'client_id' => ZUMBA_CLIENT_ID,
            "client_secret" => ZUMBA_CLIENT_SECRET,
            "grant_type" => "authorization_code",
            "code" => $code,
            "redirect_uri" => $redirect_uri,
        ];

        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.zumba.com/v7/oauth/access_token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postfields),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $token = json_decode($response);

        curl_close($curl);

        return $token;
    }

    private function getUserDetails($token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.zumba.com/v7/user',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token->access_token
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }

    private function getLicenseDetails($token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.zumba.com/v7/user/licenses',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token->access_token
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

}
