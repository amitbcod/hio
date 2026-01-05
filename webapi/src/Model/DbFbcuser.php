<?php
Class DbFbcuser{
	private $dbl;
	
	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}


 public function getEmailTemplateById($templateId)
  {		
  		$params = array($templateId);
  		$get_user = $this->dbl->dbl_conn->rawQuery("SELECT * FROM email_template WHERE id = ?",$params);
  		//print_r($get_user);exit;
  		return $get_user;
  }
  
  public function add_row($table, $columns, $values, $params)
  {
  	
  	$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO $table ($columns) VALUES($values)",$params);
  
  	if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$last_insert_id = $this->dbl->dbl_conn->getInsertId();
			if ($this->dbl->dbl_conn->count > 0){
				return $last_insert_id;
			}else{
				return false;
			}
		} else {
		// echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();
		return false;
		}
  }
  
  
	public function insert_fbc_user($email,$password)
	{
		$params = array($email, $password, 0, time());
		//print_r($params);
		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO fbc_users (email, password,status, created_at) VALUES (?, ?, ?, ?)", $params);
			
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$last_insert_id = $this->dbl->dbl_conn->getInsertId();
			if ($this->dbl->dbl_conn->count > 0){
				return $last_insert_id;
			}else{
				return false;
			}
		} else {
			// echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();
			return false;
		}
	}
	
	public function insert_fbc_user_shop($fbc_user_id,$org_shop_name)
	{
		$params = array($fbc_user_id, $org_shop_name);
		//print_r($params);
		$insert_row = $this->dbl->dbl_conn->rawQuery("INSERT INTO fbc_users_shop (fbc_user_id, org_shop_name) VALUES (?, ?)", $params);
			
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$last_insert_id = $this->dbl->dbl_conn->getInsertId();
			if ($this->dbl->dbl_conn->count > 0){
				return $last_insert_id;
			}else{
				return false;
			}
		} else {
			// echo 'Insert in UST failed. Error: '. $this->dbl->dbl_conn->getLastError();
			return false;
		}
	}
	
	
	public  function seo_friendly_url($string){
		$string = str_replace(array('[\', \']'), '', $string);
		$string = preg_replace('/\[.*\]/U', '', $string);
		$string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
		$string = htmlentities($string, ENT_COMPAT, 'utf-8');
		$string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
		$string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
		return strtolower(trim($string, '-'));
	}
	
	 public function FbcUsers()
	{
		$params = array($goal_id);
		$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM fbc_users  ORDER BY id ASC",'');
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $result;
			}else{
				return false;
			}
				
		}else{
			return false;
		}

	}

	public function FbcUserDetailById($fbc_user_id)
	{
		$params = array($fbc_user_id);
		$result = $this->dbl->dbl_conn->rawQueryOne("SELECT * FROM fbc_users WHERE fbc_user_id = ?",$params);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $result;
			}else{
				return false;
			}
				
		}else{
			return false;
		}
	
	}
	
	public function FbcUserDetailByEmail($email)
	{
		$params = array($email);
		$result = $this->dbl->dbl_conn->rawQueryOne("SELECT * FROM fbc_users WHERE email = ?",$params);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $result;
			}else{
				
				return false;
			}
				
		}else{
			
			return false;
		}
	
	}
	
	
	public function UpdateShopIdForFbcUser($fbc_user_id,$shop_id,$identifier)
	{
		$updated_at = time();
		$params = array($shop_id,$identifier,$updated_at,$fbc_user_id);
		$update_goals = $this->dbl->dbl_conn->rawQuery("UPDATE fbc_users SET shop_id = ?,identifier = ?, updated_at = ? WHERE fbc_user_id = ?",$params);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			return true;
		} else {
			return false;
		}
	}
  
}
