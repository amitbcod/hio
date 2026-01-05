<?php

class ZumbaLoginController extends CI_Controller
{
    public function login_with_zumba(){
        $this->load->library('user_agent');

        if (!$this->agent->is_referral())
        {
            $referrer = $this->agent->referrer();
            if(mb_strpos($referrer, '/customer/') === false) {
                $this->session->set_userdata(['login_referrer' => $referrer]);
            }            
        }

        $redirect_uri = urlencode(base_url('/oauth/zumba/login'));
        return redirect("https://www.zumba.com/oauth/authorize?client_id=" . ZUMBA_CLIENT_ID . "&redirect_uri=$redirect_uri&state=" . md5(mt_rand()));
    }

    public function login(){
        $redirect_uri = urlencode(base_url('/oauth/zumba/login'));
        $attempt_login_response = OauthRepository::attempt_login($_GET['code'], $redirect_uri);

        if($attempt_login_response->is_success !== "true") {
            return redirect('/customer/login');
        }

        switch ($attempt_login_response->action) {
            case "request_user_email":
                $this->template->load('oauth/zumba/request_user_email', [
                    'PageTitle' => 'Enter account email',
                    'instructor_id' => $attempt_login_response->instructor_id
                ]);

                break;
            case "redirect_to_login";
                return redirect('/CustomerController/autoLogin?z=1&email=' . $attempt_login_response->customer_email);
        }

    }

    public function confirm_user_email(){
        $email = $_POST['email'];
        $instructor_id = $_POST['instructor_id'];

        $confirm_user_email_response = OauthRepository::confirm_user_email($email, $instructor_id);

        if($confirm_user_email_response->action === 'redirect_to_login') {
            return redirect('/CustomerController/autoLogin?z=1&email=' . $confirm_user_email_response->customer_email);
        } else {
            return redirect('/customer/login');
        }
    }
}
