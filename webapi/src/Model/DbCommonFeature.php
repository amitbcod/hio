<?php
class DbCommonFeature
{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}


	// public function blogChildDetails($blog_id){
	// 	$param = array($blog_id);
	// 	$query = "SELECT blogs_details.* FROM blogs_details where blogs_details.blog_id=? ";
	// 	$blogChildDetails = $this->dbl->dbl_conn->rawQuery($query,$param);
	// 	if ($this->dbl->dbl_conn->getLastErrno() === 0){
	// 	  if ($this->dbl->dbl_conn->count > 0){
	// 		  return $blogChildDetails;
	// 	  }else{
	// 		  return false;
	// 	  }
	//   }else{
	// 	  return false;
	//   }
	// }

	// public function blogDetails($url_key){
	// 	$param = array($url_key,1);
	// 	$query = "SELECT blogs.* FROM blogs where blogs.url_key=? AND blogs.status=?";
	// 	$blogDetails = $this->dbl->dbl_conn->rawQueryOne($query,$param);
	// 	if ($this->dbl->dbl_conn->getLastErrno() === 0){
	// 	  if ($this->dbl->dbl_conn->count > 0){
	// 		  return $blogDetails;
	// 	  }else{
	// 		  return false;
	// 	  }
	//   }else{
	// 	  return false;
	//   }
	// }


	// public function getBlogListing($page='',$page_size=''){

	// 	if(!empty($page) || !empty($page_size))
	// 	{
	// 		$limit=" LIMIT $page , $page_size";
	// 	}else{
	// 		$limit=" ";
	// 	}

	// 	$query = "SELECT blogs.* FROM blogs $limit";
	// 	$blog_data = $this->dbl->dbl_conn->rawQuery($query);
	// 	if ($this->dbl->dbl_conn->getLastErrno() === 0){
	// 	  if ($this->dbl->dbl_conn->count > 0){
	// 		  return $blog_data;
	// 	  }else{
	// 		  return false;
	// 	  }
	//   }else{
	// 	  return false;
	//   }
	// }

	public function get_customer_signup_otp($mobile_no)
	{
		$param = array($mobile_no);
		$query = "SELECT customer_signup_otp.* FROM customer_signup_otp where mobile_no= ?  ORDER BY id DESC LIMIT 0, 1";
		$customer_signup_otp_data = $this->dbl->dbl_conn->rawQueryOne($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $customer_signup_otp_data;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function check_validity_coupon_code($shopcode, $coupon_code)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$param_one = array($coupon_code);
		$query_one = "SELECT s.end_date FROM $shop_db.salesrule as s INNER JOIN $shop_db.salesrule_coupon as sc ON sc.rule_id = s.rule_id   WHERE sc.coupon_code = ? AND s.remove_flag = 0 AND s.status = 1 limit 1 ";

		$coupon_row = $this->dbl->dbl_conn->rawQueryOne($query_one, $param_one);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $coupon_row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getThemeByShopcode()
	{
		$query = "SELECT themes_master.*, themes_webshops.current_theme FROM themes_master INNER JOIN themes_webshops ON themes_master.id=themes_webshops.theme_id WHERE themes_webshops.current_theme = 1";
		$get_theme = $this->dbl->dbl_conn->rawQueryOne($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $get_theme;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getShopLiveStatus()
	{

		$query = "SELECT `webshop_status`, `website_live`, `country_code`, `currency_code`, `currency_symbol`,`enable_test_mode`,`test_mode_access_ips` FROM `shop_details` where webshop_status = 1 ";
		$get_status = $this->dbl->dbl_conn->rawQueryOne($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $get_status;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function CustomerDetailsByEmailId($email = '', $mobile_no = '', $emailmobile = '')
	{

		if (!empty($emailmobile)) {
			$check_email_exist =  "SELECT * FROM customers where  ((`mobile_no` = '$emailmobile') OR (`email_id` = '$emailmobile'))";
		}
		if (!empty($email) && !empty($mobile_no)) {
			$check_email_exist =  "SELECT * FROM customers where  ((`mobile_no` = '$mobile_no') OR (`email_id` = '$email'))";
		} else if (!empty($mobile_no) && empty($email)) {
			$check_email_exist =  "SELECT * FROM customers where  `mobile_no` = '$mobile_no' ";
		} else if (!empty($email) && empty($mobile_no)) {
			$check_email_exist =  "SELECT * FROM customers where  `email_id` = '$email' ";
		}
		$query  = $this->dbl->dbl_conn->rawQueryOne($check_email_exist);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function MerchantDetailsByEmailId($email = '', $mobile_no = '', $emailmobile = '')
	{

		if (!empty($emailmobile)) {
			$check_email_exist =  "SELECT * FROM publisher where  ((`phone_no` = '$emailmobile') OR (`email` = '$emailmobile'))";
		}
		if (!empty($email) && !empty($mobile_no)) {
			$check_email_exist =  "SELECT * FROM publisher where  ((`phone_no` = '$mobile_no') OR (`email` = '$email'))";
		} else if (!empty($mobile_no) && empty($email)) {
			$check_email_exist =  "SELECT * FROM publisher where  `phone_no` = '$mobile_no' ";
		} else if (!empty($email) && empty($mobile_no)) {
			$check_email_exist =  "SELECT * FROM publisher where  `email` = '$email' ";
		}
		$query  = $this->dbl->dbl_conn->rawQueryOne($check_email_exist);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function CustomerDetailsByAuthToken($shopcode, $auth_token)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode;

		[$selector, $validator] = explode(':', $auth_token);


		$check_token_exist =  "SELECT * FROM $shop_db.customer_auth_tokens where  `selector` = ?";
		$token_row  = $this->dbl->dbl_conn->rawQueryOne($check_token_exist, [$selector]);
		if ($this->dbl->dbl_conn->getLastErrno() !== 0 || count($token_row) === 0) {
			return false;
		}



		if (!hash_equals($token_row['hashedValidator'], hash('sha256', $validator))) {
			return false;
		}


		$get_user =  "SELECT * FROM $shop_db.customers where  `id` = ?";
		$query  = $this->dbl->dbl_conn->rawQueryOne($get_user, [$token_row['customer_id']]);

		if ($this->dbl->dbl_conn->getLastErrno() === 0 && $this->dbl->dbl_conn->count > 0) {
			return $query;
		}
		return false;
	}


	public function insert_customer($insert_data)
	{
		$this->dbl->dbl_conn->insert('customers', $insert_data);
		return $this->dbl->dbl_conn->getInsertId();
	}


	public function update_customer_values($shopid, $customer_id, $update_data)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopid;
		$table_name  = $shop_db . ".`customers`";

		return (bool) $this->dbl->dbl_conn
			->where('id', $customer_id)
			->update($table_name, $update_data);
	}

	public function insert_login_session($insert_array)
	{
		$columns = implode(", ", array_keys($insert_array));
		$escaped_values =  array_values($insert_array);
		$values  = implode(", ", $escaped_values);
		$insert_login = "insert into login_session ($columns) VALUES ($values) ";
		$query  = $this->dbl->dbl_conn->rawQueryOne($insert_login);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function update_session_time($logout_time, $session_id)
	{

		$check_session_exist = "SELECT * from  login_session where sessionid = '$session_id' ";
		$query_check  = $this->dbl->dbl_conn->rawQueryOne($check_session_exist);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				$exist = 0;
			} else {
				$exist = 1;
				return $exist;
			}
		} else {
			return false;
		}
		if ($exist == 0) {
			$update_logout_time = "UPDATE login_session SET logout_time = '$logout_time' WHERE sessionid = '$session_id'";
			$query  = $this->dbl->dbl_conn->rawQueryOne($update_logout_time);
			if ($this->dbl->dbl_conn->getLastErrno() === 0) {
				if ($this->dbl->dbl_conn->count > 0) {
					return $query;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}

	public function update_password($email, $new_password)
	{

		$change_password = "UPDATE customers SET password = '$new_password' WHERE email_id = '$email'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($change_password);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function merchant_update_password($email, $new_password)
	{

		$change_password = "UPDATE publisher SET password = '$new_password' WHERE email = '$email'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($change_password);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function update_email($email, $customer_id)
	{
		$change_password = "UPDATE customers SET email_id = '$email' WHERE id = '$customer_id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($change_password);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getCustomerDetailById($customer_id)
	{
		$check_email_exist =  "SELECT * FROM customers where  `id` = '$customer_id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($check_email_exist);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getCustomerType($customer_group_id)
	{
		$customers_type_master =  "SELECT * FROM customers_type_master where  `id` = '$customer_group_id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($customers_type_master);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function update_row($table, $columns, $where, $params)
	{
		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $table SET $columns WHERE $where", $params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {

				return $update_row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function add_row($table, $columns, $values, $params)
	{
		$insert_row = $this->dbl->dbl_conn->rawQueryOne("INSERT INTO $table ($columns) VALUES($values)", $params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			$last_insert_id = $this->dbl->dbl_conn->getInsertId();
			if ($this->dbl->dbl_conn->count > 0) {
				return $last_insert_id;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getTableData($table_name, $database_flag, $where = '', $order_by = '', $params = '', $select = '')
	{
		if ($database_flag == 'main') {
			if (empty($where) && empty($params)) {
				$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $table_name");
				if (!empty($select)) {
					$result = $this->dbl->dbl_conn->rawQuery("SELECT $select FROM $table_name");
				}
			} else {
				$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $table_name WHERE $where $order_by", $params);
				if (!empty($select)) {
					$result = $this->dbl->dbl_conn->rawQuery("SELECT $select FROM $table_name WHERE $where $order_by", $params);
				}
			}
			if ($this->dbl->dbl_conn->getLastErrno() === 0) {
				if ($this->dbl->dbl_conn->count > 0) {
					return $result;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		if ($database_flag == 'own') {

			if (empty($where) && empty($params)) {
				$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $table_name");
				if (!empty($select)) {
					$result = $this->dbl->dbl_conn->rawQuery("SELECT $select FROM $table_name");
				}
			} else {
				$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $table_name WHERE $where $order_by", $params);
				if (!empty($select)) {
					$result = $this->dbl->dbl_conn->rawQuery("SELECT $select FROM $table_name WHERE $where $order_by", $params);
				}
			}
			if ($this->dbl->dbl_conn->getLastErrno() === 0) {
				if ($this->dbl->dbl_conn->count > 0) {
					return $result;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}


	public function getCustomerAddressById($customer_address_id)
	{
		$sql =  "SELECT * FROM customers_address where  `id` = '$customer_address_id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getWebShopEmailTemplateByCode($shopcode, $email_code)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$params = array($email_code);
		$Row = $this->dbl->dbl_conn->rawQueryOne("SELECT * FROM $shop_db.email_template WHERE email_code = ?", $params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $Row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getWebShopName()
	{
		$query = "SELECT * FROM shop_details";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $Row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getFbcUsersWebShopDetails()
	{
		$query = "SELECT wsd.* FROM `webshop_details` as wsd ";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query);


		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $Row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getFbcUsersWebsiteTexts($lang_code = '')
	{
		// $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		if ($lang_code != '') {
			$query = "SELECT wt.*,mwt.message as other_lang_message,mwt.message2 as other_lang_message2,mwt.message3 as other_lang_message3,mwt.office_address as other_lang_office_address FROM website_texts as wt LEFT JOIN multi_lang_website_texts as mwt ON (wt.id=mwt.text_id and mwt.lang_code='$lang_code') ";
		} else {
			$query = "SELECT wt.* FROM website_texts as wt ";
		}
		// echo $query ; die();
		$Row = $this->dbl->dbl_conn->rawQueryOne($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $Row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getExternalAPI($shopcode)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$query = "SELECT * FROM $main_db.external_apis";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $Row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	private function generateAuthToken($customer_id)
	{


		$selector = $this->generateToken(12);
		$validator = $this->generateSecureToken(32);

		$values = [
			$selector,
			hash('sha256', $validator),
			$customer_id,
			date('Y-m-d H:i:s', strtotime('+100 days')),
		];

		$this->dbl->dbl_conn->rawQuery("insert into customer_auth_tokens (selector, hashedValidator, customer_id, expires) VALUES (?, ?, ?, ?)", $values);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			return "$selector:$validator";
		} else {
			return false;
		}
	}

	private function generateToken($length = 20)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$Token = '';
		for ($i = 0; $i < $length; $i++) {
			$Token .= $characters[rand(0, $charactersLength - 1)];
		}

		return $Token;
	}

	private function generateSecureToken($length = 20)
	{
		return bin2hex(random_bytes($length));
	}

	public function commonLoginFunction($loginID, $first_name, $last_name, $email, $customer_type_id, $quote_id = '', $remember = '')
	{
		$cart_obj = new DbCart();
		$webshop_obj = new DbProductFeature();
		$LoginToken = $this->generateToken();

		$insert_login_session = array(
			'sessionid' => "'" . $LoginToken . "'",
			'user_id' => "'" . $loginID . "'",
			'login_time' => time(),
			'ip' => "'" . $_SERVER['REMOTE_ADDR'] . "'"
		);

		$this->insert_login_session($insert_login_session);

		$auth_token = $remember !== '' ? $this->generateAuthToken($loginID) : '';

		$quote_ids_previous = '';
		$new_Quote_id = '';

		if ($quote_id != '') {
			$new_Quote_id = $quote_id;
			$quote_ids_previous = $this->getAllQuoteByCustomerId($loginID, $quote_id);


			if (!empty($quote_ids_previous)) {

				foreach ($quote_ids_previous as $quote_ids) {
					$array_quote_ids[] = $quote_ids['quote_id'];
				}


				$comma_seperated_quote_ids = implode(',', $array_quote_ids);
				$quote_item = $this->getAllQuoteItem($comma_seperated_quote_ids);

				if (!empty($quote_item)) {

					foreach ($quote_item as $item) {

						if ($item['product_type'] == 'bundle') {
							$checkItemExist = $this->checkQuoteItemDataExistById($quote_id, $item['product_id']);
							if ($checkItemExist == true) {
								continue;
							}
						}

						$getProductsInfo = $this->getProductsDetails($item['product_id']);

						if ($item['product_type'] == 'bundle') {
							$bundle_disabled = $this->CheckBundleProductDisabledCount($item['product_id']);
							if ($bundle_disabled != null && count($bundle_disabled) > 0) {
								continue;
							}
						}


						if (!empty($getProductsInfo)) {

							if ($getProductsInfo['product_type'] == 'conf-simple') {
								$getConfigProductsInfo = $this->getProductsDetails($getProductsInfo['parent_id']);
							}

							if ($getProductsInfo['product_inv_type'] == 'buy' && $getProductsInfo['product_type'] != 'bundle') {
								$product_inv = $webshop_obj->getAvailableInventory($getProductsInfo['id']);
							} elseif ($getProductsInfo['product_type'] == 'bundle') {

								$bundle_qty_flag = 1;
								$bundle_child_details = array();
								$bundleProductsCartItem = $cart_obj->getQuoteItemDataById($item['item_id']);
								$bundleProducts = json_decode($bundleProductsCartItem['bundle_child_details']);
								foreach ($bundleProducts as $value) {
									$bundle_row =  $webshop_obj->bundleProductById($value->bundle_child_product_id);
									$product_inv = $webshop_obj->getAvailableInventory($value->product_id);
									$available_qty = $product_inv['available_qty'];
									$default_qty = $bundle_row['default_qty'];

									$total_qty = ($item['qty_ordered'] * $default_qty);
									$total_other_qty = 0;
									if (isset($quote_id) && $quote_id != '') {
										$total_other_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($quote_id, $item['product_id'], $value->product_id, $value->parent_id);
									}

									$total_qty = $total_qty + $total_other_qty;
									if ($total_qty > $available_qty) {
										$bundle_qty_flag = 0;
									}
								}
							} else {
								$product_inv = $webshop_obj->getAvailableInventory($getProductsInfo['id']);
							}


							$total_bundle_qty = 0;
							if (isset($quote_id) && $quote_id != '' && $getProductsInfo['product_type'] != 'bundle') {
								$total_bundle_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($quote_id, '', $getProductsInfo['id'], $getProductsInfo['parent_id']);
								$quantity_total_check = $item['qty_ordered'] + $total_bundle_qty;
							} else {
								$quantity_total_check = $item['qty_ordered'];
							}

							$specialPriceArr = $webshop_obj->getSpecialPrices($getProductsInfo['id']);

							if ($specialPriceArr != false) {
								$price = $specialPriceArr['special_price'];
								$total_price = $specialPriceArr['special_price'] * $item['qty_ordered'];
							} else {
								$price = $getProductsInfo['webshop_price'];
								$total_price = $getProductsInfo['webshop_price'] * $item['qty_ordered'];
							}
							if ($getProductsInfo['product_type'] == 'conf-simple') {

								$VariantInfo = $cart_obj->get_product_variant_details($getProductsInfo['parent_id'], $getProductsInfo['id']);
								$product_variants_arr = array();
								$product_variants_str = '';
								if (is_array($VariantInfo) && count($VariantInfo) > 0  && $VariantInfo != false) {
									foreach ($VariantInfo  as $value) {
										$attr_id = $value['attr_id'];
										$attr_value = $value['attr_value'];
										$AttrData = $cart_obj->getAttributeDetails($attr_id);
										if ($AttrData == false) {
											$attr_name = '';
										} else {
											$attr_name = $AttrData['attr_name'];
										}
										$AttrOptionData = $cart_obj->getAttributeOptionDetails($attr_value);
										if ($AttrOptionData == false) {
											$attr_option_name = '';
										} else {
											$attr_option_name = $AttrOptionData['attr_options_name'];
										}

										if ($attr_name != '' && $attr_option_name != '') {
											$product_variants_arr[] = array($attr_name => $attr_option_name);
										}
									}
								}
								if (isset($product_variants_arr) && count($product_variants_arr) > 0) {

									$product_variants_str = json_encode($product_variants_arr);
								} else {
									$product_variants_str = '';
								}
							} else {
								$product_variants_str = '';
							}
							if ($getProductsInfo['product_type'] == 'conf-simple') {
								$getConfigProductsInfo = $this->getProductsDetails($getProductsInfo['parent_id']);
								$product_code = $getConfigProductsInfo['product_code'];
							} else {
								$product_code = $getProductsInfo['product_code'];
							}

							$cart_product_id = $getProductsInfo['id'];
							$product_name = $getProductsInfo['name'];
							$sku = $getProductsInfo['sku'];
							$barcode = $getProductsInfo['barcode'];
							if ($getProductsInfo['parent_id'] > 0) {
								$parent_product_id = $getProductsInfo['parent_id'];
							} else {
								$parent_product_id = '';
							}
							$product_type = $getProductsInfo['product_type'];
							$product_inv_type = $getProductsInfo['product_inv_type'];
							$tax_percent = $getProductsInfo['tax_percent'];
							$tax_amount = $getProductsInfo['tax_amount'];

							if ($loginID != '') {
								$created_by = $loginID;
							} else {
								$created_by = '';
							}


							if ((!empty($getProductsInfo) &&  isset($product_inv) && isset($quantity_total_check) && $product_inv['available_qty'] >= $quantity_total_check && $getProductsInfo['product_type'] != 'bundle') || ($bundle_qty_flag  == 1 && $getProductsInfo['product_type'] == 'bundle')) {


								$checkItemExist = $this->checkQuoteItemDataExistById($quote_id, $item['product_id']);

								if ($checkItemExist == false) {

									if ($getProductsInfo['product_type'] == 'bundle') {
										$quantity = $item['qty_ordered'];
										$price = $getProductsInfo['webshop_price'];
										$total_price = $getProductsInfo['webshop_price'] * $quantity;
									} else {
										if ($product_inv['available_qty'] >= $item['qty_ordered']) {
											$quantity = $item['qty_ordered'];
											$specialPriceArr = $webshop_obj->getSpecialPrices($getProductsInfo['id']);

											if ($specialPriceArr != false) {
												$price = $specialPriceArr['special_price'];
												$total_price = $specialPriceArr['special_price'] * $quantity;
											} else {
												$price = $getProductsInfo['webshop_price'];
												$total_price = $getProductsInfo['webshop_price'] * $quantity;
											}
										} else {
											$quantity = $product_inv['available_qty'];
											$specialPriceArr = $webshop_obj->getSpecialPrices($getProductsInfo['id']);
											if ($specialPriceArr != false) {
												$price = $specialPriceArr['special_price'];
												$total_price = $specialPriceArr['special_price'] * $quantity;
											} else {
												$price = $getProductsInfo['webshop_price'];
												$total_price = $getProductsInfo['webshop_price'] * $quantity;
											}
										}
									}
									$cart_item_added = $cart_obj->add_to_sales_quote_item($quote_id, $product_type, $product_inv_type, $cart_product_id, $product_name, $product_code, $quantity, $sku, $barcode, $price, $total_price, $created_by, $parent_product_id, $product_variants_str, $tax_percent, $tax_amount, $item['bundle_child_details']);
								} else {
									$item_id = $checkItemExist['item_id'];
									$quantity = $checkItemExist['qty_ordered'] + $item['qty_ordered'];

									if ($product_inv['available_qty'] >= $quantity) {

										$specialPriceArr = $webshop_obj->getSpecialPrices($getProductsInfo['id']);

										if ($specialPriceArr != false) {

											$price = $specialPriceArr['special_price'];
											$total_price = $specialPriceArr['special_price'] * $quantity;
										} else {
											$price = $getProductsInfo['webshop_price'];
											$total_price = $getProductsInfo['webshop_price'] * $quantity;
										}
										$this->updateCartItemQty($quote_id, $item_id, $quantity, $price, $total_price, $tax_percent, $tax_amount);
									} else {

										$specialPriceArr = $webshop_obj->getSpecialPrices($getProductsInfo['id']);

										if ($specialPriceArr != false) {

											$price = $specialPriceArr['special_price'];
											$total_price = $specialPriceArr['special_price'] * $product_inv['available_qty'];
										} else {
											$price = $getProductsInfo['webshop_price'];
											$total_price = $getProductsInfo['webshop_price'] * $product_inv['available_qty'];
										}

										$this->updateCartItemQty($quote_id, $item_id, $product_inv['available_qty'], $price, $total_price, $tax_percent, $tax_amount);
									}
								}
							}
						}
					} //end foreach
				}

				if (isset($comma_seperated_quote_ids)) {
					$this->deleteQuoteIds($comma_seperated_quote_ids);
					$this->deleteQuoteItemIds($comma_seperated_quote_ids);
					$this->deleteQuoteAddresssIds($comma_seperated_quote_ids);
					$this->deleteQuotePayment($comma_seperated_quote_ids);
				}
			}
			$this->updateCartDataBasedOnCustomerType($quote_id);

			if (isset($quote_id) && $quote_id != '') {
				$ch_obj = new DbCheckout();
				$ch_obj->updateTaxAndShippingCharges($quote_id);
				$cart_obj->UpateQuoteTotal($quote_id);
			}
		} else {
			$new_Quote_id = '';
			$quote_ids_previous = $this->getAllQuoteByCustomerId($loginID, $quote_id);

			if (!empty($quote_ids_previous)) {

				foreach ($quote_ids_previous as $quote_ids) {

					$array_quote_ids[] = $quote_ids['quote_id'];
				}
				$comma_seperated_quote_ids = implode(',', $array_quote_ids);
				$quote_item = $this->getAllQuoteItem($comma_seperated_quote_ids);

				if (!empty($quote_item)) {

					foreach ($quote_item as $item) {
						$getProductsInfo = $this->getProductsDetails($item['product_id']);

						if ($item['product_type'] == 'bundle') {
							$bundle_disabled = $this->CheckBundleProductDisabledCount($item['product_id']);
							if ($bundle_disabled != null && count($bundle_disabled) > 0) {
								continue;
							}
						}
						if (!empty($getProductsInfo)) {
							if ($getProductsInfo['product_type'] == 'conf-simple') {
								$getConfigProductsInfo = $this->getProductsDetails($getProductsInfo['parent_id']);
							}

							$bundle_qty_flag = 1;

							if ($getProductsInfo['product_inv_type'] == 'buy' && $getProductsInfo['product_type'] != 'bundle') {
								$product_inv = $webshop_obj->getAvailableInventory($getProductsInfo['id']);
							} elseif ($getProductsInfo['product_type'] == 'bundle') {
								$bundle_child_details = array();
								$bundleProductsCartItem = $cart_obj->getQuoteItemDataById($item['item_id']);
								$bundleProducts = json_decode($bundleProductsCartItem['bundle_child_details']);

								foreach ($bundleProducts as $value) {
									$bundle_row =  $webshop_obj->bundleProductById($value->bundle_child_product_id);
									$product_inv = $webshop_obj->getAvailableInventory($value->product_id);
									$available_qty = $product_inv['available_qty'];
									$default_qty = $bundle_row['default_qty'] ?? 1;

									$total_qty = ($item['qty_ordered'] * $default_qty);
									$total_other_qty = 0;
									if (isset($new_Quote_id) && $new_Quote_id != '') {
										$total_other_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($new_Quote_id, $item['product_id'], $value->product_id, $value->parent_id);
									}
									$total_qty = $total_qty + $total_other_qty;
									if ($total_qty > $available_qty) {
										$bundle_qty_flag = 0;
									}
								}
							} else {
								$product_inv = $webshop_obj->getAvailableInventory($getProductsInfo['id']);
							}
							$total_bundle_qty = 0;

							if (isset($new_Quote_id) && $new_Quote_id != '' && $getProductsInfo['product_type'] != 'bundle') {
								$total_bundle_qty = $webshop_obj->GetTotalQuoteAddedInventoryExceptCurrentId($new_Quote_id, '', $getProductsInfo['id'], $getProductsInfo['parent_id']);
								$quantity_total_check = $item['qty_ordered'] + $total_bundle_qty;
							} else {
								$quantity_total_check = $item['qty_ordered'];
							}
							$specialPriceArr = $webshop_obj->getSpecialPrices($getProductsInfo['id']);
							if ($specialPriceArr != false) {

								$price = $specialPriceArr['special_price'];
								$total_price = $specialPriceArr['special_price'] * $item['qty_ordered'];
							} else {
								$price = $getProductsInfo['webshop_price'];
								$total_price = $getProductsInfo['webshop_price'] * $item['qty_ordered'];
							}

							if ($getProductsInfo['product_type'] == 'conf-simple') {
								$VariantInfo = $cart_obj->get_product_variant_details($getProductsInfo['parent_id'], $getProductsInfo['id']);
								$product_variants_arr = array();
								$product_variants_str = '';

								if (is_array($VariantInfo) && count($VariantInfo) > 0  && $VariantInfo != false) {
									foreach ($VariantInfo  as $value) {
										$attr_id = $value['attr_id'];
										$attr_value = $value['attr_value'];

										$AttrData = $cart_obj->getAttributeDetails($attr_id);

										if ($AttrData == false) {
											$attr_name = '';
										} else {
											$attr_name = $AttrData['attr_name'];
										}
										$AttrOptionData = $cart_obj->getAttributeOptionDetails($attr_value);
										if ($AttrOptionData == false) {
											$attr_option_name = '';
										} else {
											$attr_option_name = $AttrOptionData['attr_options_name'];
										}
										if ($attr_name != '' && $attr_option_name != '') {
											$product_variants_arr[] = array($attr_name => $attr_option_name);
										}
									}
								}
								if (isset($product_variants_arr) && count($product_variants_arr) > 0) {
									$product_variants_str = json_encode($product_variants_arr);
								} else {
									$product_variants_str = '';
								}
							} else {
								$product_variants_str = '';
							}
							if ($getProductsInfo['product_type'] == 'conf-simple') {
								$getConfigProductsInfo = $this->getProductsDetails($getProductsInfo['parent_id']);
								$product_code = $getConfigProductsInfo['product_code'];
							} else {
								$product_code = $getProductsInfo['product_code'];
							}

							$cart_product_id = $getProductsInfo['id'];
							$product_name = $getProductsInfo['name'];
							$sku = $getProductsInfo['sku'];
							$barcode = $getProductsInfo['barcode'];
							if ($getProductsInfo['parent_id'] > 0) {
								$parent_product_id = $getProductsInfo['parent_id'];
							} else {
								$parent_product_id = '';
							}
							$product_type = $getProductsInfo['product_type'];
							$product_inv_type = $getProductsInfo['product_inv_type'];
							$tax_percent = $getProductsInfo['tax_percent'];
							$tax_amount = $getProductsInfo['tax_amount'];

							if ($loginID != '') {
								$created_by = $loginID;
							} else {
								$created_by = '';
							}
							if ((!empty($getProductsInfo) && isset($product_inv) && isset($quantity_total_check) && $product_inv['available_qty'] >= $quantity_total_check && $getProductsInfo['product_type'] != 'bundle') || ($bundle_qty_flag  == 1 && $getProductsInfo['product_type'] == 'bundle') || $isPrelaunch == 1) {

								if ($new_Quote_id == '') {
									$new_Quote_id = $cart_obj->add_to_sales_quote($LoginToken, $loginID);
								}

								if ($getProductsInfo['product_type'] == 'bundle') {
									$quantity = $item['qty_ordered'];
									$price = $getProductsInfo['webshop_price'];
									$total_price = $getProductsInfo['webshop_price'] * $quantity;
								} else {
									if ($product_inv['available_qty'] >= $item['qty_ordered']) {
										$quantity = $item['qty_ordered'];
										$specialPriceArr = $webshop_obj->getSpecialPrices($getProductsInfo['id']);

										if ($specialPriceArr != false) {
											$price = $specialPriceArr['special_price'];
											$total_price = $specialPriceArr['special_price'] * $quantity;
										} else {
											$price = $getProductsInfo['webshop_price'];
											$total_price = $getProductsInfo['webshop_price'] * $quantity;
										}
									} else {
										$quantity = $product_inv['available_qty'];
										$specialPriceArr = $webshop_obj->getSpecialPrices($getProductsInfo['id']);
										if ($specialPriceArr != false) {
											$price = $specialPriceArr['special_price'];
											$total_price = $specialPriceArr['special_price'] * $quantity;
										} else {
											$price = $getProductsInfo['webshop_price'];
											$total_price = $getProductsInfo['webshop_price'] * $quantity;
										}
									}
								}
								$cart_item_added = $cart_obj->add_to_sales_quote_item($new_Quote_id, $product_type, $product_inv_type, $cart_product_id, $product_name, $product_code, $quantity, $sku, $barcode, $price, $total_price, $created_by, $parent_product_id, $product_variants_str, $tax_percent, $tax_amount, $item['bundle_child_details']);
							}
						}
					} //end foreach	
				}
				if (isset($new_Quote_id) && $new_Quote_id != '') {
					$ch_obj = new DbCheckout();
					$ch_obj->updateTaxAndShippingCharges($new_Quote_id);
					$cart_obj->UpateQuoteTotal($new_Quote_id);
				}
				if (isset($comma_seperated_quote_ids)) {
					$this->deleteQuoteIds($comma_seperated_quote_ids);
					$this->deleteQuoteItemIds($comma_seperated_quote_ids);
					$this->deleteQuoteAddresssIds($comma_seperated_quote_ids);
					$this->deleteQuotePayment($comma_seperated_quote_ids);
				}
			}
		}
		$userDetailsArr = array('AuthToken' => $auth_token, 'LoginToken' => $LoginToken, 'LoginID' => $loginID, 'FirstName' => $first_name, 'LastName' => $last_name, 'EmailID' => $email, 'Remember' => $remember, 'customer_type_id' => $customer_type_id, 'QuoteId' => $new_Quote_id);
		return $userDetailsArr;
	}

	public function getAllQuoteByCustomerId($loginID, $quote_id = '')
	{
		$sub_query = '';
		if ($quote_id != '') {
			$sub_query = "and quote_id != $quote_id";
		}
		$param = array($loginID);
		$query = "SELECT sales_quote.quote_id FROM sales_quote WHERE sales_quote.customer_id = ? $sub_query";

		// echo $query;
		// print_r ($loginID);
		// print_r ($quote_id);
		// exit;

		$quote_ids = $this->dbl->dbl_conn->rawQuery($query, $param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $quote_ids;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getAllQuoteItem($quote_ids)
	{
		$query = "SELECT sales_quote_items.item_id, sales_quote_items.sku, sales_quote_items.product_id, sales_quote_items.parent_product_id, sales_quote_items.product_type, SUM(sales_quote_items.qty_ordered) as qty_ordered, sales_quote_items.bundle_child_details FROM sales_quote_items WHERE quote_id in ($quote_ids) group by product_id";

		$quote_ids = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $quote_ids;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getProductsDetails($product_id)
	{

		$date = strtotime(date('d-m-Y'));

		$query = "SELECT products.* FROM products WHERE products.id = $product_id AND products.remove_flag = 0 AND products.status = 1";
		$quote_ids = $this->dbl->dbl_conn->rawQueryOne($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $quote_ids;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function checkQuoteItemDataExistById($quote_id, $product_id)
	{
		$sql =  "SELECT * FROM sales_quote_items where `product_id` = '$product_id'  AND quote_id = $quote_id ";
		$row  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function deleteQuoteIds($comma_seperated_quote_ids)
	{
		$query = "Delete FROM sales_quote WHERE quote_id in ($comma_seperated_quote_ids)";
		$this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function deleteQuoteItemIds($comma_seperated_quote_ids)
	{

		$query = "Delete FROM sales_quote_items WHERE quote_id in ($comma_seperated_quote_ids)";
		$this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function deleteQuoteAddresssIds($comma_seperated_quote_ids)
	{

		$query = "Delete FROM sales_quote_address WHERE quote_id in ($comma_seperated_quote_ids)";
		$this->dbl->dbl_conn->rawQuery($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function deleteQuotePayment($comma_seperated_quote_ids)
	{

		$query = "Delete  FROM sales_quote_payment WHERE quote_id in ($comma_seperated_quote_ids)";
		$this->dbl->dbl_conn->rawQuery($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function updateCartItemQty($quote_id, $item_id, $qty_ordered, $price, $total_price, $tax_percent, $tax_amount)
	{

		$params = array($qty_ordered, $price, $total_price, $tax_percent, $tax_amount, time(), $quote_id, $item_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  sales_quote_items set qty_ordered = ?, price = ?, total_price = ?, tax_percent = ?, tax_amount = ?, updated_at = ?  where quote_id= ? AND item_id = ? ", $params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			$flag = true;
			if ($this->dbl->dbl_conn->count > 0) {
				return $flag;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getPrelauchProductList()
	{

		$query = "SELECT assigned_products FROM products_block_master join products_block_details on products_block_master.id = products_block_details.pb_master_id where products_block_master.block_identifier = 'prelauch'";

		$row = $this->dbl->dbl_conn->rawQueryOne($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function getCustomerPrelaunchStatus($loginID)
	{

		$param = array($loginID);
		$query = "SELECT access_prelanch_product FROM customers where id = ?";
		$row = $this->dbl->dbl_conn->rawQueryOne($query, $param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getCustomerCatlogBuilderStatus($shopcode, $loginID)
	{

		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable

		$param = array($loginID);
		$query = "SELECT allow_catlog_builder FROM $shop_db.customers where id = ?";
		$row = $this->dbl->dbl_conn->rawQueryOne($query, $param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function updateCartDataBasedOnCustomerType($quote_id)
	{
		$webshop_obj = new DbCheckout();
		$product_obj = new DbProductFeature();
		$cart_obj = new DbCart();

		$OrderItems = $webshop_obj->get_sales_quote_items($quote_id);

		if ($OrderItems != false) {
			foreach ($OrderItems as $value) {
				$item_id = $value['item_id'];
				if ($value['product_type'] == 'conf-simple') {
					$product_id = $value['parent_product_id'];
				} else {
					$product_id = $value['product_id'];
				}
				$product_datails = $product_obj->getproductDetailsById($product_id);

				$specialPriceArr = $product_obj->getSpecialPrices($value['product_id']);

				if ($specialPriceArr != false) {
					$price = $specialPriceArr['special_price'];
					$total_price = $specialPriceArr['special_price'] * $value['qty_ordered'];

					$cart_obj->updateCartItems($quote_id, $item_id, $price, $total_price);
					$webshop_obj->updateTaxAndShippingCharges($quote_id);
					$cart_obj->UpateQuoteTotal($quote_id);
				}
			}
		}
	}

	public function checkzin($shopcode, $email, $password)
	{
		$external_api = $this->getExternalAPI($shopcode);

		$salt = $external_api['api_salt'];
		$token = $external_api['api_token'];
		$requester_domain = $external_api['requester_domain'];

		$dhash = time();
		//$login= 'login';
		$login = 'verify_user';

		$key = sha1($email . $password . $dhash . $salt);

		$post = [
			'username' 	=> $email,
			'password' 	=> $password,
			'action'   	=> $login,
			'api_key' 	=> $token,
			'dhash'	 	=> $dhash,
			'key'  	 	=> $key,
		];
		$ch = curl_init();
		// curl_setopt($ch, CURLOPT_URL,'https://api.zumba.com/api/v2/users/info.xml');
		curl_setopt($ch, CURLOPT_URL, $external_api['api_url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
		curl_setopt($ch, CURLOPT_REFERER, $requester_domain);  // USE DOMAIN OF REQUESTER
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: */*']);

		// curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		$xml = curl_exec($ch);
		curl_close($ch);

		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$array = json_decode(json_encode($xml));

		return $array;
	}


	public function checkzin1($shopcode, $email, $password)
	{
		$external_api = $this->getExternalAPI($shopcode);
		echo '<pre>' . print_r($external_api, '\n') . '</pre>';

		$salt = $external_api['api_salt'];
		$token = $external_api['api_token'];
		$requester_domain = $external_api['requester_domain'];

		$dhash = time();
		//$login= 'login';
		$login = 'verify_user';

		$key = sha1($email . $password . $dhash . $salt);

		$post = [
			'username' 	=> $email,
			'password' 	=> $password,
			'action'   	=> $login,
			'api_key' 	=> $token,
			'dhash'	 	=> $dhash,
			'key'  	 	=> $key,
		];
		$ch = curl_init();
		// curl_setopt($ch, CURLOPT_URL,'https://api.zumba.com/api/v2/users/info.xml');
		curl_setopt($ch, CURLOPT_URL, $external_api['api_url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
		curl_setopt($ch, CURLOPT_REFERER, $requester_domain);  // USE DOMAIN OF REQUESTER
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: */*']);

		// curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		$xml = curl_exec($ch);
		curl_close($ch);

		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$array = json_decode(json_encode($xml));

		echo '<pre>' . print_r($array, '\n') . '</pre>';
		exit;

		return $array;
	}

	public function ip_visitor_country()
	{
		$remote  = $_SERVER['REMOTE_ADDR'];
		$country  = "Unknown";

		$ip = $remote;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.geoplugin.net/json.gp?ip=" . $ip);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$ip_data_in = curl_exec($ch); // string
		curl_close($ch);

		$ip_data = json_decode($ip_data_in, true);
		$ip_data = str_replace('&quot;', '"', $ip_data);
		if ($ip_data && $ip_data['geoplugin_countryName'] != null) {
			$country = $ip_data['geoplugin_countryName'];
			$cCode = $ip_data['geoplugin_countryCode'];
		}
		return $cCode;
	}


	public function file_get_contents_curl($url)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

	public function  getproductscountsbycategoryid($shopcode, $category_id, $product_ids_str)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		//$main_db = DB_NAME; //Constant variable


		$param = array($category_id, 0);

		$sub_query = "AND pc.product_id  IN (" . $product_ids_str . ") ";

		$query = "SELECT COUNT(prod.id) as product_count from $shop_db.products as prod, $shop_db.products_category as pc where prod.id = pc.product_id
			AND pc.category_ids= ?
			AND prod.remove_flag=?
			$sub_query
			";

		$product_count = $this->dbl->dbl_conn->rawQuery($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				// echo $this->dbl->dbl_conn->getLastQuery();
				return $product_count;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function  getproductsbycategoryid($shopcode, $category_id, $product_ids_str)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		//$main_db = DB_NAME; //Constant variable
		$param = array($category_id, 0);
		$sub_query = "AND pc.product_id  IN (" . $product_ids_str . ") ";
		$query = "SELECT prod.product_type,pc.product_id  from $shop_db.products as prod, $shop_db.products_category as pc where prod.id = pc.product_id
			AND pc.category_ids= ?
			AND prod.remove_flag=?
			$sub_query ";

		$product_data = $this->dbl->dbl_conn->rawQuery($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $product_data;
				// echo $this->dbl->dbl_conn->getLastQuery();
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getAllCategories($shopcode, $shopid, $catlog_id, $product_ids_str)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$param = array(1, 0);

		$query = "SELECT main_cat.id,main_cat.cat_name,main_cat.cat_level,main_cat.slug FROM $main_db.category as main_cat INNER JOIN $shop_db.fbc_users_category_b2b as b2b ON main_cat.id=b2b.category_id  WHERE main_cat.status=? AND b2b.level=? ";


		$mainCatMenu = $this->dbl->dbl_conn->rawQuery($query, $param);
		if ($this->dbl->dbl_conn->count > 0) {
			$final_arr = array();

			foreach ($mainCatMenu as $cat) {
				$arr = array();
				$arr['id'] = $cat['id'];
				$arr['menu_name'] = $cat['cat_name'];
				$arr['menu_level'] = $cat['cat_level'];
				$arr['slug'] = $cat['slug'];
				$arr['category_id'] = $cat['id'];

				$product_count = $this->getproductscountsbycategoryid($shopcode, $arr['category_id'], $product_ids_str);
				$arr['product_count'] = $product_count[0]['product_count'];

				if ($arr['product_count'] != 0) {
					$product_data = $this->getproductsbycategoryid($shopcode, $arr['category_id'], $product_ids_str);
					$arr['product_data'] = $product_data;
				}
				$firstLevelCategory = $this->firstLevelCategory($shopcode, $cat['id']);
				$firstLevelCatePro = array();

				if ($firstLevelCategory != false) {
					foreach ($firstLevelCategory as $cat1) {
						$arr1 = array();
						$arr1['id'] = $cat1['id'];
						$arr1['menu_name'] = $cat1['cat_name'];
						$arr1['menu_level'] = $cat1['cat_level'];
						$arr1['slug'] = $cat1['slug'];
						$arr1['category_id'] = $cat1['id'];

						$product_count1 = $this->getproductscountsbycategoryid($shopcode, $arr1['category_id'], $product_ids_str);
						$arr1['product_count1'] = $product_count1[0]['product_count'];

						if ($arr1['product_count1'] != 0) {
							$product_data1 = $this->getproductsbycategoryid($shopcode, $arr1['category_id'], $product_ids_str);
							$arr1['product_data1'] = $product_data1;

							foreach ($product_data1 as $product) {
								$final_product_data1 = array();

								$firstLevelCatePro[$product['product_id']] = $product['product_id'];

								$real_product_data1 = $this->getproductsbyid($shopcode, $product['product_id']);

								$collection_name = $this->getcollection_name($shopcode, $shopid, $product['product_id']);
								if (isset($collection_name) && $collection_name != '') {
									$real_product_data1['collection_name'] = $collection_name[0]['attr_options_name'];
								} else {
									$real_product_data1['collection_name'] = '';
								}

								$style_code = $this->getstylecodeById($shopcode, $product['product_id']);
								if (isset($style_code) && $style_code != '') {
									$real_product_data1['style_code'] = $style_code[0]['attr_value'];
								} else {
									$real_product_data1['style_code'] = '';
								}

								$final_product_data1['real_product_data1'] = $real_product_data1;

								$table = 'catalog_builder_items';
								$flag = 'own';
								$where = '';
								if ($product['product_type'] == 'configurable') {
									$where = 'parent_id  = ' . $product['product_id'] . ' AND catalog_builder_id =' . $catlog_id . '';
								} else {
									$where = 'product_id  = ' . $product['product_id'] . ' AND catalog_builder_id =' . $catlog_id . '';
								}

								$catalog_builder_items  = $this->getTableData($shopcode, $table, $flag, $where);


								if ($product['product_type'] == 'simple' || $product['product_type'] == 'bundle') {
									$available_quantity = $this->getProductInventory($shopcode, $product['product_id']);
									$catalog_builder_items[0]['available_quantity'] = $available_quantity['available_qty'];
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items[0];
								} else {

									$item_count = count($catalog_builder_items);
									for ($i = 0; $i < $item_count; $i++) {
										$product_data2 = $this->getproductsbyid($shopcode, $catalog_builder_items[$i]['product_id']);
										$available_quantity = $this->getProductInventory($shopcode, $catalog_builder_items[$i]['product_id']);
										$product_data2[$i]['available_quantity'] = $available_quantity['available_qty'];
										$catalog_builder_items[$i]['available_quantity'] = $available_quantity['available_qty'];
									}
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items;
								}
								$arr1['final_product_data1'][] = $final_product_data1;
							}
						}



						$secondLevelCategory = $this->secondLevelCategory($shopcode, $cat1['id'], $cat['id']);
						if ($secondLevelCategory != false) {
							foreach ($secondLevelCategory as $cat2) {
								$arr2['id'] = $cat2['id'];
								$arr2['menu_name'] = $cat2['cat_name'];
								$arr2['menu_level'] = $cat2['cat_level'];
								$arr2['slug'] = $cat2['slug'];
								$arr2['category_id'] = isset($cat2['id']) ? $cat2['id'] : '';

								$product_count2 = $this->getproductscountsbycategoryid($shopcode, $arr2['category_id'], $product_ids_str);
								$arr2['product_count2'] = $product_count2[0]['product_count'];
								// }
								$arr1['menu_level_2'][] = $arr2;
							}
						}
						$arr['menu_level_1'][] = $arr1;
					}
				} else {
					if ($arr['product_count'] != 0) {
						// $product_data 33, 1
						// $firstLevelCatePro 1
						foreach ($product_data as $product) {
							$final_product_data1 = array();

							$real_product_data1 = $this->getproductsbyid($shopcode, $product['product_id']);

							$collection_name = $this->getcollection_name($shopcode, $shopid, $product['product_id']);
							if (isset($collection_name) && $collection_name != '') {
								$real_product_data1['collection_name'] = $collection_name[0]['attr_options_name'];
							} else {
								$real_product_data1['collection_name'] = '';
							}

							$style_code = $this->getstylecodeById($shopcode, $product['product_id']);
							if (isset($style_code) && $style_code != '') {
								$real_product_data1['style_code'] = $style_code[0]['attr_value'];
							} else {
								$real_product_data1['style_code'] = '';
							}

							$final_product_data1['real_product_data1'] = $real_product_data1;

							$table = 'catalog_builder_items';
							$flag = 'own';
							$where = '';
							if ($product['product_type'] == 'configurable') {
								$where = 'parent_id  = ' . $product['product_id'] . ' AND catalog_builder_id =' . $catlog_id . '';
							} else {
								$where = 'product_id  = ' . $product['product_id'] . ' AND catalog_builder_id =' . $catlog_id . '';
							}

							$catalog_builder_items  = $this->getTableData($shopcode, $table, $flag, $where);


							if ($product['product_type'] == 'simple'  || $product['product_type'] == 'bundle') {
								$available_quantity = $this->getProductInventory($shopcode, $product['product_id']);
								$catalog_builder_items[0]['available_quantity'] = $available_quantity['available_qty'];
								$final_product_data1['catalog_builder_items'] = $catalog_builder_items[0];
							} else {

								$item_count = count($catalog_builder_items);
								for ($i = 0; $i < $item_count; $i++) {
									$product_data2 = $this->getproductsbyid($shopcode, $catalog_builder_items[$i]['product_id']);
									$available_quantity = $this->getProductInventory($shopcode, $catalog_builder_items[$i]['product_id']);
									$product_data2[$i]['available_quantity'] = $available_quantity['available_qty'];
									$catalog_builder_items[$i]['available_quantity'] = $available_quantity['available_qty'];
								}
								$final_product_data1['catalog_builder_items'] = $catalog_builder_items;
							}
							$arr['final_product_data'][] = $final_product_data1;
						}
					}
				}

				$final_arr[] = $arr;
			}

			return $final_arr;
		} else {
			return false;
		}
	}

	public function firstLevelCategory($shopcode, $category_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$param = array(1, 1, $category_id);

		$query = "SELECT cat_level1.id,cat_level1.cat_name,cat_level1.cat_level,cat_level1.slug FROM $main_db.category as cat_level1 INNER JOIN $shop_db.fbc_users_category_b2b as b2b ON cat_level1.id=b2b.category_id  WHERE cat_level1.status=? AND b2b.level=? AND cat_level1.parent_id=? ";

		$level1CatMenu = $this->dbl->dbl_conn->rawQuery($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $level1CatMenu;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function secondLevelCategory($shopcode, $cat_parent_id, $cat_main_parent_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$param = array(1, 2, $cat_parent_id, $cat_main_parent_id);

		$query = "SELECT cat_level2.id,cat_level2.cat_name,cat_level2.cat_level,cat_level2.slug FROM $main_db.category as cat_level2 INNER JOIN $shop_db.fbc_users_category_b2b as b2b ON cat_level2.id=b2b.category_id WHERE cat_level2.status=? AND b2b.level=? AND cat_level2.parent_id=? AND cat_level2.main_parent_id=? ";

		$level2CatMenu = $this->dbl->dbl_conn->rawQuery($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $level2CatMenu;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getproductsbyid($shopcode, $id)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		//$main_db = DB_NAME; //Constant variable
		$param = array($id, 0);

		$query = "SELECT prod.id,prod.product_type,prod.name,prod.base_image,prod.product_code,prod.sku,prod.webshop_price from $shop_db.products as prod WHERE
			  prod.id=? AND prod.remove_flag=? ";

		$product_data = $this->dbl->dbl_conn->rawQuery($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $product_data;
				// echo $this->dbl->dbl_conn->getLastQuery();
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getProductInventory($shopcode, $product_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$param = array($product_id);
		$query = "SELECT $shop_db.products_inventory.available_qty FROM $shop_db.products_inventory WHERE $shop_db.products_inventory.product_id = ?";
		$inventory = $this->dbl->dbl_conn->rawQueryOne($query, $param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $inventory; // return result
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getProductVariantByProductId($shopcode, $product_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$param = array($product_id);
		$query = "SELECT eav_attr.id, eav_attr.attr_code, eav_attr.attr_name FROM $shop_db.products_variants_master as pv_m INNER JOIN $main_db.eav_attributes as eav_attr ON eav_attr.id=pv_m.attr_id WHERE pv_m.product_id=? ORDER BY position ASC";

		$variant_prod = $this->dbl->dbl_conn->rawQuery($query, $param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $variant_prod;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getstylecodeById($shopcode, $product_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$param = array($product_id);

		$query = "SELECT prd_attr.* FROM $shop_db.products_attributes as prd_attr, $main_db.eav_attributes as eav_attr WHERE eav_attr.attr_code='style_code' AND  prd_attr.product_id=?  AND prd_attr.attr_id=eav_attr.id;";

		// $query = "SELECT prd_attr.* FROM $shop_db.products_attributes as prd_attr  WHERE prd_attr.product_id=?  AND prd_attr.attr_id=8 ";

		$attr_data = $this->dbl->dbl_conn->rawQuery($query, $param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $attr_data;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getcollection_name($shopcode, $shopid, $product_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$param = array($product_id, $shopid);
		$query = "SELECT attr_opt.attr_options_name FROM $shop_db.products_attributes as prd_attr, $main_db.eav_attributes_options as attr_opt ,$main_db.eav_attributes as eav_attr WHERE prd_attr.product_id=? AND eav_attr.attr_code='collection_name' AND eav_attr.shop_id=? AND prd_attr.attr_value=attr_opt.id AND prd_attr.attr_id=attr_opt.attr_id";

		// $query = "SELECT attr_opt.attr_options_name FROM $shop_db.products_attributes as prd_attr, $main_db.eav_attributes_options as attr_opt WHERE prd_attr.product_id=?  AND prd_attr.attr_id=1244 AND prd_attr.attr_value=attr_opt.id";

		$attr_data = $this->dbl->dbl_conn->rawQuery($query, $param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $attr_data;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getFbcUsersShopDetails($shopid)
	{

		$main_db = DB_NAME; //Constant variable

		$param = array($shopid);
		$query = "SELECT fsd.* FROM $main_db.fbc_users_shop as fsd WHERE fsd.shop_id = ?";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $Row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function getShopVatDetails($shopcode, $shop_id, $country_code)
	{

		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable

		$param = array($country_code);
		$query = "SELECT * FROM $shop_db.vat_settings  WHERE country_code = ?";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query, $param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $Row;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function getDelayWarehouseTime()
	{
		static $results = [];

		$get_cust_variable =  "SELECT * FROM custom_variables where  `identifier` = 'delay_warehouse'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($get_cust_variable);
		if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {
			return $query;
		}

		return false;
	}

	public function get_currency_list($shopcode)
	{
		$table_name = 'multi_currencies';
		$database_flag = 'own';
		$where = 'status = 1 and remove_flag = 0';
		$currencies = $this->getTableData($shopcode, $table_name, $database_flag, $where);
		$result = [];
		foreach ($currencies as $currency) {
			$result[$currency['code']] = $currency;
		}

		return $result;
	}


	public function getProductVariants($shopcode, $product_id): array
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$db = DB_NAME;

		$sql = <<<SQL
            SELECT
                attr_name, attr_options_name
            FROM
                $shop_db.products_variants
                INNER JOIN $db.eav_attributes ON $shop_db.products_variants.attr_id = $db.eav_attributes.id
                INNER JOIN $db.eav_attributes_options ON $shop_db.products_variants.attr_value = $db.eav_attributes_options.id
            WHERE
                $shop_db.products_variants.product_id = $product_id
SQL;


		$dbProductAttributes = $this->dbl->dbl_conn->rawQuery($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				$results = [];
				foreach ($dbProductAttributes as $attribute) {
					$results[] = [$attribute['attr_name'] => $attribute['attr_options_name']];
				}

				return $results;
			}
		}
		return [];
	}

	public function getShippingMethodName($shopcode, $shipping_method_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE . $shopcode; // constant variable
		$query =  "SELECT * FROM $shop_db.shipping_methods WHERE id = ?";

		$shipping_method = $this->dbl->dbl_conn->rawQueryOne($query, [$shipping_method_id]);

		return $shipping_method['ship_method_name'] ?? '';
	}

	public function getGeneralLogZinApiStatus($shopcode)
	{
		// $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		// $main_db = DB_NAME; //Constant variable
		$get_cust_variable =  "SELECT * FROM custom_variables where  `identifier` = 'general_log_zinapi'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($get_cust_variable);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	public function general_log_zinapi_insert($shopcode, $email, $password, $checkzin)
	{
		$externalApi = $this->getExternalAPI($shopcode);
		$saltApi = $externalApi['api_salt'];
		$tokenApi = $externalApi['api_token'];
		$requester_domainApi = $externalApi['requester_domain'];
		$dhashApi = time();
		$loginApi = 'verify_user';
		$keyApi = sha1($email . $password . $dhashApi . $saltApi);
		$post = [
			'username' 	=> $email,
			'password' 	=> $password,
			'action'   	=> $loginApi,
			'api_key' 	=> $tokenApi,
			'dhash'	 	=> $dhashApi,
			'key'  	 	=> $keyApi,
		];

		$type = 'zumba_api';
		$input_param = json_encode($post);
		$output_param = json_encode($checkzin);
		$api_url = $externalApi['api_url'];
		$requester_domain = $requester_domainApi;
		$table = 'general_logs';
		$columns = 'type, input_param, output_param, api_url, requester_domain';
		$values = '?, ?, ?, ?, ?';
		$params = array($type, $input_param, $output_param, $api_url, $requester_domain);
		$insert_general_log = $this->add_row($shopcode, $table, $columns, $values, $params);
	}

	public function deleteQuoteOnAutoLogin($id)
	{

		$this->deleteQuoteIds($id);
		$this->deleteQuoteItemIds($id);
		$this->deleteQuoteAddresssIds($id);
		$this->deleteQuotePayment($id);
	}

	public function CheckBundleProductDisabledCount($bundle_product_id)
	{

		$sql = "SELECT DISTINCT pb.bundle_product_id FROM products as p INNER JOIN products_bundles pb ON pb.product_id = p.id WHERE pb.bundle_product_id = $bundle_product_id AND(p.status<>1 || p.remove_flag=1)";
		$query  = $this->dbl->dbl_conn->rawQueryOne($sql);

		if (($this->dbl->dbl_conn->getLastErrno() === 0) && $this->dbl->dbl_conn->count > 0) {
			return $query;
		}

		return false;
	}

	public function custom_filter_input($data)
	{
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);

		return $data;
	}

	public function getOrderDataByINCId($order_id)

	{



		$sql =  "SELECT * FROM sales_order where `increment_id` = '$order_id'";

		$row  = $this->dbl->dbl_conn->rawQueryOne($sql);

		if ($this->dbl->dbl_conn->getLastErrno() === 0) {

			if ($this->dbl->dbl_conn->count > 0) {

				return $row;
			} else {

				return false;
			}
		} else {

			return false;
		}
	}

	public function update_transaction_id($order_id, $transaction_id)
	{

		$change_password = "UPDATE `sales_order_payment` SET `transaction_id` = '$transaction_id' WHERE order_id = '$order_id'";
		$query  = $this->dbl->dbl_conn->rawQueryOne($change_password);
		if ($this->dbl->dbl_conn->getLastErrno() === 0) {
			if ($this->dbl->dbl_conn->count > 0) {
				return $query;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
