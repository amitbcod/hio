<?php
class UserModel extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function checkUserIdentifierExist($identifier)
	{
		$result = $this->db->get_where('fbc_users', array('identifier' => $identifier))->result();
		//echo $this->db->last_query();
		return $result;
	}

	public function checkIdentifierExistDynamic($tableName, $identifier)
	{
		$result = $this->db->get_where($tableName, array('identifier' => $identifier))->result();
		return $result;
	}

	function updateUserIdentifierByUserId($fbc_user_id, $identifier)
	{

		$data	=  array('identifier' => $identifier);
		$this->db->where('fbc_user_id', $fbc_user_id);
		$this->db->update('fbc_users', $data);
		return true;
	}

	function updateRememberToken($id, $remember_token)
	{

		$data	=  array('remember_token' => $remember_token);
		$this->db->where('id', $id);
		$this->db->update('adminusers', $data);
		return true;
	}

	function updatePasswordResetToken($fbc_user_id, $password_reset_token)
	{

		$data	=  array('password_reset_token' => $password_reset_token);
		$this->db->where('fbc_user_id', $fbc_user_id);
		$this->db->update('fbc_users', $data);
		return true;
	}

	function updatePassword($fbc_user_id, $password)
	{

		$data	=  array('password' => $password, 'password_reset_date' => strtotime(date('Y-m-d H:i:s')), 'password_reset_token' => null);
		$this->db->where('fbc_user_id', $fbc_user_id);
		$this->db->update('fbc_users', $data);
		return true;
	}

	public function insertIntoLoginSession($accessToken, $login_id)
	{
		$insertdata = array(
			'sessionid'		=> $accessToken,
			'login_id'		=> $login_id,
			'login_time'	=> strtotime(date('Y-m-d H:i:s')),
			'ip'			=> $_SERVER['REMOTE_ADDR']
		);
		$this->db->insert('adminsession', $insertdata);
	}

	public function getUserByEmail($email)
	{
		$result = $this->db->get_where('adminusers', array('email' => $email))->row();
		return $result;
	}

	public function getShopDetailsByShopId($shop_id)
	{
		$result = $this->db->get_where('fbc_users_shop', array('shop_id' => $shop_id))->row();
		return $result;
	}

	public function getShopDetailsByfbcuserid($fbc_user_id)
	{
		$result = $this->db->get_where('fbc_users_shop', array('fbc_user_id' => $fbc_user_id))->row();
		return $result;
	}

	public function checkUserExistByEmail($email)
	{
		$result = $this->db->get_where('fbc_users', array('email' => $email))->num_rows();
		return $result;
	}

	public function getUserByUserId($fbc_user_id)
	{
		$result = $this->db->get_where('fbc_users', array('fbc_user_id' => $fbc_user_id))->row();
		return $result;
	}

	public function getUserDetails($fbc_user_id, $email)
	{
		$result = $this->db->get_where('fbc_users', array('fbc_user_id' => $fbc_user_id, 'email' => $email))->row();
		//echo $this->db->last_query();
		return $result;
	}

	public function getActiveUsersWithoutDB()
	{
		$this->db->select('FU.fbc_user_id, FUS.shop_id');
		$this->db->from('fbc_users FU');
		$this->db->join('fbc_users_shop FUS', 'FU.fbc_user_id = FUS.fbc_user_id');
		//$this->db->where(array('FU.status' => 1, 'FU.email_verification_status' => 1, 'FUS.database_name' => null));
		$this->db->where(array('FU.status' => 1, 'FU.email_verification_status' => 1));
		$this->db->where('FUS.database_name', null);
		$this->db->or_where('FUS.database_name', '');
		$this->db->order_by('FU.fbc_user_id', 'ASC');
		$query = $this->db->get();
		$result = $query->result();

		return $result;
	}

	function updateDBName($shop_id, $db_name)
	{

		$data = array('database_name' => $db_name);
		$this->db->where('shop_id', $shop_id);
		$this->db->update('fbc_users_shop', $data);

		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function ip_visitor_country()
	{
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];
		$country  = "Unknown";
		if (filter_var($client, FILTER_VALIDATE_IP)) {
			$ip = $client;
		} elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
			$ip = $forward;
		} else {
			$ip = $remote;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.geoplugin.net/json.gp?ip=" . $ip);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$ip_data_in = curl_exec($ch); // string
		curl_close($ch);

		$ip_data = json_decode($ip_data_in, true);
		$ip_data = str_replace('&quot;', '"', $ip_data);
		$cCode = "";
		if ($ip_data && $ip_data['geoplugin_countryName'] != null) {
			$country = $ip_data['geoplugin_countryName'];
			$cCode = $ip_data['geoplugin_countryCode'];
		}
		return $cCode;
	}

	public function update_fbc_users($update_array)
	{
		$this->db->where('fbc_user_id', $_SESSION['LoginID']);
		$query = $this->db->update('fbc_users', $update_array);

		return $query;
	}

	public function update_fbc_users_shop($update_array)
	{
		$this->db->where('fbc_user_id', $_SESSION['LoginID']);
		$query = $this->db->update('fbc_users_shop', $update_array);

		return $query;
	}

	public function insert_employee($insert_array_fbc_users, $identifier)
	{
		$insert_query = $this->db->insert('fbc_users', $insert_array_fbc_users);
		if ($insert_query) {
			$fbc_user_id = $this->db->insert_id();
			$Identifier = $identifier . "-" . $fbc_user_id;
			$update_query = $this->UserModel->updateUserIdentifierByUserId($fbc_user_id, $Identifier);
			return $update_query;
		}
	}

	public function insert_employee_details($fbc_users_emp_details)
	{
		$insert_query = $this->db->insert('fbc_users_emp_details', $fbc_users_emp_details);
		return $insert_query;
	}

	public function update_employee($update_array, $user_id)
	{
		$this->db->where('fbc_user_id', $user_id);
		$query = $this->db->update('fbc_users', $update_array);
		// echo $this->db->last_query();
		return $query;
	}

	public function update_employee_details($update_array, $user_id)
	{
		$this->db->where('fbc_user_id', $user_id);
		$query = $this->db->update('fbc_users_emp_details', $update_array);
		// echo $this->db->last_query();
		return $query;
	}

	public function update_password($update_date, $fbc_user_id)
	{
		$this->db->where('fbc_user_id', $fbc_user_id);
		$query = $this->db->update('fbc_users', $update_date);
		// echo $this->db->last_query();
		return $query;
	}

	public function update_email($update_data, $fbc_user_id)
	{
		$this->db->where('fbc_user_id', $fbc_user_id);
		$query = $this->db->update('fbc_users', $update_data);
		// echo $this->db->last_query();
		return $query;
	}

	// public function getShopEmployeesDetails($fbc_user_id)
	// {


	// 	$fbc_user_id	=	$this->session->userdata('ShopOwnerId');  //old LoginID

	// 	$shop_id		=	$this->session->userdata('ShopID');



	// 	$FBCData=$this->CommonModel->getSingleDataByID('fbc_users_shop',array('fbc_user_id'=>$fbc_user_id),'shop_id,fbc_user_id,database_name');

	// 	if(isset($FBCData) && $FBCData->database_name!='')

	// 	{

	// 		$fbc_user_database=$FBCData->database_name;



	// 		$this->load->database();

	// 		$config_app = fbc_switch_db_dynamic(DB_PREFIX.$fbc_user_database);		

	// 		$this->seller_db = $this->load->database($config_app,TRUE);

	// 		if($this->seller_db->conn_id) {

	// 			//do something

	// 		} else {

	// 			redirect(base_url());

	// 		}

	// 	}else{

	// 		redirect(base_url());

	// 	}

	// 	$main_db_name=$this->seller_db->database;
	// 	$this->seller_db->select();
	// 	// $this->db->select($main_db_name.'.employee_role_master.*');
	// 	$this->db->where('parent_id', $fbc_user_id);	
	// 	$this->db->from('fbc_users fu');
	// 	$this->db->join('fbc_users_emp_details fued', 'fu.fbc_user_id = fued.fbc_user_id');
	// 	$this->db->join($main_db_name.'.employee_role_master emp', 'emp.id = fued.role_in_company');

	// 	$query = $this->db->get(); 
	// 	// echo $this->db->last_query();die();
	// 	if ($query->num_rows() > 0)
	// 	   {
	// 			$result = $query->result_array();
	// 			return $result;
	// 	   }
	// 	   else{
	// 		   return false;
	// 	   }
	// }

	public function change_employee_status($status, $fbc_user_id)
	{
		$this->db->set('status', $status);
		$this->db->where('fbc_user_id', $fbc_user_id);
		$query = $this->db->update('fbc_users');
		return $query;
	}

	public function email_exists($new_email)
	{
		$this->db->where('email', $new_email);
		$query = $this->db->get('fbc_users');
		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getSaleQuote($flag, $date)
	{
		$this->db->select('quote_id');
		$this->db->from('sales_quote');
		if ($flag > 0) {
			$this->db->where('customer_id >', 0);
			$this->db->where('updated_at <', $date);
		} else {
			$this->db->where('customer_id', 0);
			$this->db->where('updated_at <', $date);
		}
		$query = $this->db->get();
		//echo $this->db->last_query();
		return $query->result();
	}

	public function deleteData($quote_ids)
	{

		$this->db->where_in('quote_id', $quote_ids);
		$this->db->delete('sales_quote_address');

		$this->db->where_in('quote_id', $quote_ids);
		$this->db->delete('sales_quote_items');

		$this->db->where_in('quote_id', $quote_ids);
		$this->db->delete('sales_quote_payment');

		$this->db->where_in('quote_id', $quote_ids);
		$this->db->delete('sales_quote');
	}

	public function deleteLoginSession($login_time, $logout_time)
	{

		$this->db->where('login_time <', $login_time);
		$this->db->or_where('logout_time <', $logout_time and 'logout_time' > 0);
		$this->db->delete('login_session');
		if ($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
	public function insertForceLogin($randomNumber, $email)
	{

		$insertForceLogindata = array(
			'hash_key'		=> $randomNumber,
			'email'		=> $email,
			'admin_panel'		=> 'Business_World',
			'created_at'	=> strtotime(date('Y-m-d H:i:s')),
			'ip'			=> $_SERVER['REMOTE_ADDR']
		);
		return $this->db->insert('force_login', $insertForceLogindata);
		// echo $this->db->last_query();die;

	}
	public function insertNextGenPublishingForceLogin($randomNumber, $email)
	{

		$insertForceLogindata = array(
			'hash_key'		=> $randomNumber,
			'email'		=> $email,
			'admin_panel'		=> 'Next_Gen_Publishing',
			'created_at'	=> strtotime(date('Y-m-d H:i:s')),
			'ip'			=> $_SERVER['REMOTE_ADDR']
		);
		return $this->db->insert('next_gen_publishing_force_login', $insertForceLogindata);
		// echo $this->db->last_query();die;

	}
	public function insertSpentaMultiMediaForceLogin($randomNumber, $email)
	{

		$insertForceLogindata = array(
			'hash_key'		=> $randomNumber,
			'email'		=> $email,
			'admin_panel'		=> 'Spenta_Multi_Media',
			'created_at'	=> strtotime(date('Y-m-d H:i:s')),
			'ip'			=> $_SERVER['REMOTE_ADDR']
		);
		return $this->db->insert('spenta_multi_media_force_login', $insertForceLogindata);
		// echo $this->db->last_query();die;

	}
	public function insertMediaStarForceLogin($randomNumber, $email)
	{

		$insertForceLogindata = array(
			'hash_key'		=> $randomNumber,
			'email'		=> $email,
			'admin_panel'		=> 'Media_Star',
			'created_at'	=> strtotime(date('Y-m-d H:i:s')),
			'ip'			=> $_SERVER['REMOTE_ADDR']
		);
		return $this->db->insert('media_star_force_login', $insertForceLogindata);
		// echo $this->db->last_query();die;

	}
}
