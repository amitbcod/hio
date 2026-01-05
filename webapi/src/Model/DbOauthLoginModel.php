<?php

Class DbOauthLoginModel
{
    private $dbl;

    public function __construct()
    {
        require_once 'Config/DbLibrary.php';
        $this->dbl = new DbLibrary();
    }

    public function find_oauth_login_by_oauth_id($shopcode, $provider, $oauth_user_id){
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode;

        return $this->dbl->dbl_conn
            ->where('provider', $provider)
            ->where('oauth_user_id', $oauth_user_id)
            ->getOne($shop_db.'.oauth_login');
    }

    public function create_oauth_login($shopcode, $provider, $token, $user_details, $license_details)
    {
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode;

        $params = [
            'provider' => $provider,
            'oauth_user_id' => $user_details->id,
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'token_details' => json_encode($token),
            'user_details' => json_encode($user_details),
            'license_details' => json_encode($license_details),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $this->dbl->dbl_conn->insert($shop_db.'.oauth_login', $params);
        $insert_id = $this->dbl->dbl_conn->getInsertId();

        return $this->dbl->dbl_conn->rawQueryOne("SELECT * FROM $shop_db.oauth_login WHERE id = $insert_id");
    }

    public function update_oauth_login($shopcode, $oauth_login_id, $token, $user_details, $license_details)
    {
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode;

        $this->dbl->dbl_conn
            ->where('id', $oauth_login_id)
            ->update($shop_db.'.oauth_login', [
                'oauth_user_id' => $user_details->id,
                'access_token' => $token->access_token,
                'refresh_token' => $token->refresh_token,
                'token_details' => json_encode($token),
                'user_details' => json_encode($user_details),
                'license_details' => json_encode($license_details),
                'updated_at' => time(),
            ]);

        return $this->dbl->dbl_conn->rawQueryOne("SELECT * FROM $shop_db.oauth_login WHERE id = $oauth_login_id");
    }

    public function update_oath_login_customer_id($shopcode, $instructor_id, $customer_id)
    {
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode;

        $this->dbl->dbl_conn
            ->where('oauth_user_id', $instructor_id)
            ->update($shop_db.'.oauth_login', [
                    'customer_id' => $customer_id,
                    'updated_at' => time(),
            ]);
    }

}
