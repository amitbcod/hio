<?php

class LoggedInUserFromRememberMe {
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        if (!isset($this->CI->session)) {  //Check if session lib is loaded or not
              $this->CI->load->library('session');  //If not loaded, then load it here
        }
    }

    function handle(){

        $shopcode = SHOPCODE;
        $shop_id = SHOP_ID;

        if(!empty($this->CI->session->userdata('LoginID'))){
            return;
        }
        
        if(empty($_COOKIE['auth_token'])){
            return ;
        }


        // if exists, try to log in with this cookie
        $loginResponse = LoginRepository::loginWithAuthToken($shopcode, $shop_id, $_COOKIE['auth_token']);


        if($loginResponse->is_success == 'true'){
            $userdetails = $loginResponse->userdetails;
            $LoginID = $userdetails->LoginID;
            $LoginToken = $userdetails->LoginToken;
            $FirstName = $userdetails->FirstName;
            $LastName = $userdetails->LastName;
            $EmailID = $userdetails->EmailID;
            $customer_type_id = $userdetails->customer_type_id;
            $QuoteId = $userdetails->QuoteId;

            if(!empty($QuoteId)){
                $this->CI->session->set_userdata(['QuoteId'=>$QuoteId]);
            }

            $sessionArr = ['LoginToken' => $LoginToken,'LoginID' => $LoginID,'FirstName' => $FirstName,'LastName' => $LastName,'EmailID' =>$EmailID,'CustomerTypeID'=>$customer_type_id];
            $this->CI->session->set_userdata($sessionArr);

            if($this->CI->session->userdata('QuoteId')){
                $cartArr = ['quote_id'=>$this->CI->session->userdata('QuoteId'),'customer_id'=>$LoginID,'session_id'=>$LoginToken,'checkout_method'=>'login'];
                $cart = CheckoutRepository::update_quote_customer_id($shopcode,$shop_id,$cartArr);
            }
        }
        // if fails => delete the cookie
    }
}
