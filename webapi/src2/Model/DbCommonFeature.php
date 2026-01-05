<?php
Class DbCommonFeature{
	private $dbl;

	public function __construct()
	{
		require_once 'Config/DbLibrary.php';
		$this->dbl = new DbLibrary();
	}

	public function base_url($shopcode)
  	{
		return "https://".$shopcode.".".BASE_ROOT_URL."/";
  	}

  	public function check_validity_coupon_code($shopcode,$coupon_code){
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param_one=array($coupon_code);
			$query_one = "SELECT s.end_date FROM $shop_db.salesrule as s INNER JOIN $shop_db.salesrule_coupon as sc ON sc.rule_id = s.rule_id   WHERE sc.coupon_code = ? AND s.remove_flag = 0 AND s.status = 1 limit 1 ";
			
			$coupon_row = $this->dbl->dbl_conn->rawQueryOne($query_one,$param_one);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $coupon_row;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

 	public function getEmailTemplateById($templateId)
  	{
  		$params = array($templateId);
  		$get_user = $this->dbl->dbl_conn->rawQuery("SELECT * FROM email_template WHERE id = ?",$params);
  		return $get_user;
  	}

  	public function getThemeByShopcode($shopcode)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$query = "SELECT $main_db.themes_master.*, $shop_db.themes_webshops.current_theme FROM $main_db.themes_master INNER JOIN $shop_db.themes_webshops ON $main_db.themes_master.id=$shop_db.themes_webshops.theme_id WHERE $shop_db.themes_webshops.current_theme = 1";

		$get_theme = $this->dbl->dbl_conn->rawQueryOne($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $get_theme;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function getShopLiveStatus($shopid)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.'shop'.$shopid; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$check_shop_exist =  "SELECT t1.* FROM `fbc_users_shop` t1, `fbc_users` t2 where  t1.`shop_id` = '$shopid' and t1.`fbc_user_id`=t2.`fbc_user_id` and t2.status=1";

		$Check_Shop_status = $this->dbl->dbl_conn->rawQueryOne($check_shop_exist);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0) {
				$query = "SELECT `webshop_status`, `shop_id`, `website_live`, `country_code`, `currency_code`, `currency_symbol`, `org_shop_name`, `enable_test_mode`,`test_mode_access_ips` FROM `fbc_users_shop` where webshop_status = 1 and `shop_id` = '$shopid'";
		$get_status = $this->dbl->dbl_conn->rawQueryOne($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $get_status;
			}else{
				return false;
			}
		}else{
			return false;
		}
			}else{
				$exist = "N";
				return $exist ;
			}
		}else{
			return false;
		}

	}

	 public function CustomerDetailsByEmailId($shopid,$email)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopid; // constant variable
  		$main_db = DB_NAME; //Constant variable
		 $check_email_exist =  "SELECT * FROM $shop_db.customers where  `email_id` = '$email'";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($check_email_exist);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $query;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	 public function insert_customer($shopid,$insert_data)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopid; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$table_name  = "'". $shop_db.".`customers`'" ;
		$columns = implode(", ",array_keys($insert_data));
		$escaped_values =  array_values($insert_data);
		$values  = implode(", ", $escaped_values);
		$query = "insert into $shop_db.customers ($columns) VALUES ($values) ";
		$this->dbl->dbl_conn->rawQueryOne($query);

		if($query)
		{
			return true;
		}else{
			return false;
		}
	 }
	 public function insert_login_session($shopcode,$insert_array)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$columns = implode(", ",array_keys($insert_array));
		$escaped_values =  array_values($insert_array);
		$values  = implode(", ", $escaped_values);
		 $insert_login = "insert into $shop_db.login_session ($columns) VALUES ($values) ";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($insert_login);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $query;
			}else{
				return false;
			}
		}else{
			return false;
		}
	 }

	 public function update_session_time($shopcode,$logout_time,$session_id)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$check_session_exist = "SELECT * from  $shop_db.login_session where sessionid = '$session_id' ";
		$query_check  = $this->dbl->dbl_conn->rawQueryOne($check_session_exist);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				$exist = 0;
			}else{
				$exist = 1;
				return $exist;
			}
		}else{
			return false;
		}
		if($exist == 0)
		{
			  $update_logout_time = "UPDATE $shop_db.login_session SET logout_time = '$logout_time' WHERE sessionid = '$session_id'";
			 $query  = $this->dbl->dbl_conn->rawQueryOne($update_logout_time);
			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				if ($this->dbl->dbl_conn->count > 0){
					return $query;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
	 }


	 public function update_password($shopcode,$email,$new_password)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$change_password = "UPDATE $shop_db.customers SET password = '$new_password' WHERE email_id = '$email'";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($change_password);
			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				if ($this->dbl->dbl_conn->count > 0){
					return $query;
				}else{
					return false;
				}
			}else{
				return false;
			}
	 }


	 public function update_email($shopcode,$email,$customer_id)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$change_password = "UPDATE $shop_db.customers SET email_id = '$email' WHERE id = '$customer_id'";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($change_password);
			if ($this->dbl->dbl_conn->getLastErrno() === 0){
				if ($this->dbl->dbl_conn->count > 0){
					return $query;
				}else{
					return false;
				}
			}else{
				return false;
			}
	 }


public function CustomerEmailExits($shopcode,$new_email)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		$check_email_exist =  "SELECT * FROM $shop_db.customers where  `email_id` = '$new_email'";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($check_email_exist);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $query;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}



	public function getCustomerDetailById($shopcode,$customer_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		 $check_email_exist =  "SELECT * FROM $shop_db.customers where  `id` = '$customer_id'";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($check_email_exist);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $query;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function getCustomerType($shopcode,$customer_group_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		 $customers_type_master =  "SELECT * FROM $shop_db.customers_type_master where  `id` = '$customer_group_id'";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($customers_type_master);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $query;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function update_row($shopcode, $table, $columns, $where, $params)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE $shop_db.$table SET $columns WHERE $where",$params);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $update_row;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function add_row($shopcode, $table, $columns, $values, $params)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$insert_row = $this->dbl->dbl_conn->rawQueryOne("INSERT INTO $shop_db.$table ($columns) VALUES($values)",$params);
		// print_r($this->dbl->dbl_conn->getLastErrno());
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$last_insert_id = $this->dbl->dbl_conn->getInsertId();
			if ($this->dbl->dbl_conn->count > 0){
				return $last_insert_id;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	public function getTableData($shopcode, $table_name, $database_flag, $where='', $order_by='', $params='',$select='')
  	{
  		if($database_flag == 'main'){
			if(empty($where) && empty($params)){
				$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $table_name");
				if(!empty($select)){
					$result = $this->dbl->dbl_conn->rawQuery("SELECT $select FROM $table_name");
				}
			}else{
				$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $table_name WHERE $where $order_by",$params);
				if(!empty($select)){
					$result = $this->dbl->dbl_conn->rawQuery("SELECT $select FROM $table_name WHERE $where $order_by",$params);

				}
			}
			//print_r($get_user);exit;
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

		if($database_flag == 'own'){
			$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
			$main_db = DB_NAME; //Constant variable
			if(empty($where) && empty($params)){
				$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $shop_db.$table_name");
				if(!empty($select)){
					$result = $this->dbl->dbl_conn->rawQuery("SELECT $select FROM $shop_db.$table_name");

				}
			}else{
				$result = $this->dbl->dbl_conn->rawQuery("SELECT * FROM $shop_db.$table_name WHERE $where $order_by",$params);
				if(!empty($select)){
					$result = $this->dbl->dbl_conn->rawQuery("SELECT $select FROM $shop_db.$table_name WHERE $where $order_by",$params);
				}
			}
			//print_r($this->dbl->dbl_conn->getLastErrno());exit;
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
  	}


	public function getCustomerAddressById($shopcode,$customer_address_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		 $sql =  "SELECT * FROM $shop_db.customers_address where  `id` = '$customer_address_id'";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $query;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function getWebShopEmailTemplateByCode($shopcode,$email_code)
  	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$params = array($email_code);
  		$Row = $this->dbl->dbl_conn->rawQueryOne("SELECT * FROM $shop_db.email_template WHERE email_code = ?",$params);

  		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Row;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

	public function getWebShopName($shopcode,$shopid)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($shopid);
  		$query = "SELECT fus.* FROM $main_db.fbc_users_shop as fus WHERE fus.shop_id = ?";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Row;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function getFbcUsersWebShopDetails($shopcode,$shopid)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($shopid);
  		$query = "SELECT fwd.* FROM $main_db.fbc_users_webshop_details as fwd WHERE fwd.shop_id = ?";

  		$Row = $this->dbl->dbl_conn->rawQueryOne($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Row;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function getFbcUsersWebsiteTexts($shopcode,$lang_code='')
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		if($lang_code !='')
  		{
			$query = "SELECT wt.*,mwt.message as other_lang_message,mwt.message2 as other_lang_message2,mwt.message3 as other_lang_message3,mwt.office_address as other_lang_office_address FROM $shop_db.website_texts as wt LEFT JOIN $shop_db.multi_lang_website_texts as mwt ON (wt.id=mwt.text_id and mwt.lang_code='$lang_code') ";
		}else{
			$query = "SELECT wt.* FROM $shop_db.website_texts as wt ";
		}
  		

  		$Row = $this->dbl->dbl_conn->rawQueryOne($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Row;
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function getExternalAPI($shopcode)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$query = "SELECT * FROM $main_db.external_apis";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Row;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function commonLoginFunction($shopcode,$shopid,$loginID,$first_name,$last_name,$email,$customer_type_id,$quote_id='',$remember='',$vat_percent_session='',$currency_name='',$currency_code_session='',$currency_conversion_rate='',$default_currency_flag='',$currency_symbol='')
  	{
		$cart_obj = new DbCart();
		$webshop_obj = new DbProductFeature();

  		$time = time();
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$Token = '';
		for ($i = 0; $i < 20; $i++) {
			$Token .= $characters[rand(0, $charactersLength - 1)];
		}

		$LoginToken = $Token;
		$insert_login_session = array(
			'sessionid' => "'".$LoginToken."'",
			'user_id' => "'".$loginID."'",
			'login_time' => $time,
			'ip' => "'".$_SERVER['REMOTE_ADDR']."'"
		);

		$this->insert_login_session($shopcode,$insert_login_session);

		$quote_ids_previous = '';
		$new_Quote_id ='';

		if($quote_id!=''){

			$new_Quote_id = $quote_id;

				$quote_ids_previous = $this->getAllQuoteByCustomerId($shopcode,$loginID,$quote_id);

				if(!empty($quote_ids_previous)){

					foreach($quote_ids_previous as $quote_ids){

						$array_quote_ids[] = $quote_ids['quote_id'];
					}

					$comma_seperated_quote_ids = implode(',',$array_quote_ids);
					$quote_item = $this->getAllQuoteItem($shopcode,$comma_seperated_quote_ids);

					$prelauch_product_list = $this->getPrelauchProductList($shopcode);
					$customer_active_prelauch = $this->getCustomerPrelaunchStatus($shopcode,$loginID);
					// $allow_catlog_builder = $this->getCustomerCatlogBuilderStatus($shopcode,$loginID);

					if(!empty($quote_item)) {

						foreach($quote_item as $item){

							$isPrelaunch = 0;
							$isInprelaunchList = '';

							if($item['product_type'] == 'simple'){
								$prelauch_product_id = $item['product_id'];
							}else{
								$prelauch_product_id = $item['parent_product_id'];
							}



							if(!empty($prelauch_product_list)){
								$HiddenProducts = explode(',',$prelauch_product_list['assigned_products']);
								if (in_array($prelauch_product_id, $HiddenProducts)) {
									$isInprelaunchList = "yes";
								} else {
									$isInprelaunchList = "no";
								}
							}

							if($customer_active_prelauch['access_prelanch_product'] == 1 && $isInprelaunchList == 'yes' && $item['prelaunch']){
								$isPrelaunch = 1;
							}

							$getProductsInfo = $this->getProductsDetails($shopcode,$item['product_id'],$isPrelaunch);

							if(!empty($getProductsInfo)){

								if($getProductsInfo['product_type'] == 'conf-simple'){
									$getConfigProductsInfo = $this->getProductsDetails($shopcode,$getProductsInfo['parent_id'],$isPrelaunch);
									$customer_type_ids = explode(",", $getConfigProductsInfo['customer_type_ids']);
								}else{
									$customer_type_ids = explode(",", $getProductsInfo['customer_type_ids']);
								}



								if(in_array($customer_type_id, $customer_type_ids) || $getProductsInfo['customer_type_ids'] == 0) {

									if($getProductsInfo['product_inv_type'] == 'buy'){
										$product_inv = $webshop_obj->getAvailableInventory($getProductsInfo['id'],$shopcode);

									}else{
										$seller_shopcode = 'shop'.$getProductsInfo['shop_id'];
										$product_inv = $webshop_obj->getAvailableInventory($getProductsInfo['id'],$shopcode,$seller_shopcode);

									}


									$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);
									if(isset($vat_percent_session) && $vat_percent_session !='') {

										if($specialPriceArr!=false){
											$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
											$price = $eu_special_price ;
											$total_price = $eu_special_price * $item['qty_ordered'];
										}else{
											$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
											$price = $eu_price;
											$total_price = $eu_price * $item['qty_ordered'];
										}

									}else{
										if($specialPriceArr!=false){

											$price = $specialPriceArr['special_price'];
											$total_price = $specialPriceArr['special_price'] * $item['qty_ordered'];
										}else{
											$price = $getProductsInfo['webshop_price'];
											$total_price=$getProductsInfo['webshop_price'] * $item['qty_ordered'];
										}
									}


									if($getProductsInfo['product_type'] == 'conf-simple'){

										$VariantInfo=$cart_obj->get_product_variant_details($shopcode,$getProductsInfo['parent_id'],$getProductsInfo['id']);

										$product_variants_arr=array();
										$product_variants_str='';

										if(is_array($VariantInfo) && count($VariantInfo)>0  && $VariantInfo!=false){
											foreach($VariantInfo  as $value){
												$attr_id=$value['attr_id'];
												$attr_value=$value['attr_value'];

												$AttrData=$cart_obj->getAttributeDetails($attr_id);

												if($AttrData==false){
													$attr_name='';
												}else{
													$attr_name=$AttrData['attr_name'];
												}


												$AttrOptionData=$cart_obj->getAttributeOptionDetails($attr_value);
												if($AttrOptionData==false){
													$attr_option_name='';
												}else{
													$attr_option_name=$AttrOptionData['attr_options_name'];
												}


												if($attr_name!='' && $attr_option_name!=''){
													$product_variants_arr[] = array($attr_name => $attr_option_name);
												}
											}
										}


										if(isset($product_variants_arr) && count($product_variants_arr)>0){
											$product_variants_str=json_encode($product_variants_arr);
										}else{
											$product_variants_str='';
										}

									}else{
										$product_variants_str='';
									}


									if($getProductsInfo['product_type'] == 'conf-simple'){
										$getConfigProductsInfo = $this->getProductsDetails($shopcode,$getProductsInfo['parent_id'],$isPrelaunch);
										$product_code=$getConfigProductsInfo['product_code'];
									}else{
										$product_code=$getProductsInfo['product_code'];
									}


									$cart_product_id=$getProductsInfo['id'];
									$product_name=$getProductsInfo['name'];
									$sku=$getProductsInfo['sku'];
									$barcode=$getProductsInfo['barcode'];
									if($getProductsInfo['parent_id'] > 0){
										$parent_product_id=$getProductsInfo['parent_id'];
									}else{
										$parent_product_id='';
									}
									$product_type=$getProductsInfo['product_type'];
									$product_inv_type=$getProductsInfo['product_inv_type'];
									$pro_shop_id=$getProductsInfo['shop_id'];
									if(isset($vat_percent_session) && $vat_percent_session !='') {
										$tax_percent=$vat_percent_session;
										$tax_amount=($getProductsInfo['price'] * $vat_percent_session/100);
									}else{
										$tax_percent=$getProductsInfo['tax_percent'];
										$tax_amount=$getProductsInfo['tax_amount'];
									}

									if($loginID!=''){
										$created_by=$loginID;
									}else{
										$created_by='';
									}

									//if(!empty($getProductsInfo) && $product_inv['available_qty'] > 0){
									if((!empty($getProductsInfo) && $product_inv['available_qty'] > 0) || $isPrelaunch == 1){

										$checkItemExist= $this->checkQuoteItemDataExistById($shopcode,$quote_id,$item['shop_id'],$item['product_id']);

										if($checkItemExist==false){

											if($isPrelaunch == 1){

												$quantity = $item['qty_ordered'];
												$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);
												if(isset($vat_percent_session) && $vat_percent_session !='') {

													if($specialPriceArr!=false){
														$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
														$price = $eu_special_price ;
														$total_price = $eu_special_price * $quantity;
													}else{
														$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
														$price = $eu_price;
														$total_price = $eu_price * $quantity;
													}

												}else{
													if($specialPriceArr!=false){
														$price = $specialPriceArr['special_price'];
														$total_price = $specialPriceArr['special_price'] * $quantity;
													}else{
														$price = $getProductsInfo['webshop_price'];
														$total_price=$getProductsInfo['webshop_price'] * $quantity;
													}

												}


											}else{

												if($product_inv['available_qty'] >= $item['qty_ordered']){

													$quantity = $item['qty_ordered'];
													$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);

													if(isset($vat_percent_session) && $vat_percent_session !='') {

														if($specialPriceArr!=false){
															$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
															$price = $eu_special_price ;
															$total_price = $eu_special_price * $quantity;
														}else{
															$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
															$price = $eu_price;
															$total_price = $eu_price * $quantity;
														}

													}else{
														if($specialPriceArr!=false){
															$price = $specialPriceArr['special_price'];
															$total_price = $specialPriceArr['special_price'] * $quantity;
														}else{
															$price = $getProductsInfo['webshop_price'];
															$total_price=$getProductsInfo['webshop_price'] * $quantity;
														}
													}

												}else{

													$quantity = $product_inv['available_qty'];
													$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);
													if(isset($vat_percent_session) && $vat_percent_session !='') {

														if($specialPriceArr!=false){
															$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
															$price = $eu_special_price ;
															$total_price = $eu_special_price * $quantity;
														}else{
															$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
															$price = $eu_price;
															$total_price = $eu_price * $quantity;
														}

													}else{
														if($specialPriceArr!=false){
															$price = $specialPriceArr['special_price'];
															$total_price = $specialPriceArr['special_price'] * $quantity;
														}else{
															$price = $getProductsInfo['webshop_price'];
															$total_price=$getProductsInfo['webshop_price'] * $quantity;
														}

													}

												}


											}

											$cart_item_added=$cart_obj->add_to_sales_quote_item($shopcode,$getProductsInfo['shop_id'],$quote_id,$product_type,$product_inv_type,$cart_product_id,$product_name,$product_code,$quantity,$sku,$barcode,$price,$total_price,$getProductsInfo['shop_id'],$created_by,$parent_product_id,$product_variants_str,$tax_percent,$tax_amount,$isPrelaunch);

										}else{


											$item_id=$checkItemExist['item_id'];
											$quantity=$checkItemExist['qty_ordered']+$item['qty_ordered'];
											/*$total_price=$checkItemExist['price']*$quantity;

											$cart_obj->updateCartItemQty($shopcode,$quote_id,$item_id,$quantity,$total_price,$tax_percent,$tax_amount);
											*/

											if($isPrelaunch == 1){

												$specialPirceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);

												if(isset($vat_percent_session) && $vat_percent_session !='') {

													if($specialPriceArr!=false){
														$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
														$price = $eu_special_price ;
														$total_price = $eu_special_price * $quantity;
													}else{
														$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
														$price = $eu_price;
														$total_price = $eu_price * $quantity;
													}

												}else{
													if($specialPriceArr!=false){

														$price = $specialPriceArr['special_price'];
														$total_price = $specialPriceArr['special_price'] * $quantity;
													}else{
														$price = $getProductsInfo['webshop_price'];
														$total_price=$getProductsInfo['webshop_price'] * $quantity;
													}

												}


												$this->updateCartItemQty($shopcode,$quote_id,$item_id,$quantity,$price,$total_price,$tax_percent,$tax_amount);


											}else{

												if($product_inv['available_qty'] >= $quantity){


													$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);

													if(isset($vat_percent_session) && $vat_percent_session !='') {

														if($specialPriceArr!=false){
															$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
															$price = $eu_special_price ;
															$total_price = $eu_special_price * $quantity;
														}else{
															$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
															$price = $eu_price;
															$total_price = $eu_price * $quantity;
														}

													}else{
														if($specialPriceArr!=false){

															$price = $specialPriceArr['special_price'];
															$total_price = $specialPriceArr['special_price'] * $quantity;
														}else{
															$price = $getProductsInfo['webshop_price'];
															$total_price=$getProductsInfo['webshop_price'] * $quantity;
														}
													}


													$this->updateCartItemQty($shopcode,$quote_id,$item_id,$quantity,$price,$total_price,$tax_percent,$tax_amount);


												}else{

													$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);

													if(isset($vat_percent_session) && $vat_percent_session !='') {

														if($specialPriceArr!=false){
															$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
															$price = $eu_special_price ;
															$total_price = $eu_special_price * $product_inv['available_qty'];
														}else{
															$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
															$price = $eu_price;
															$total_price = $eu_price * $product_inv['available_qty'];
														}

													}else{
														if($specialPriceArr!=false){

															$price = $specialPriceArr['special_price'];
															$total_price = $specialPriceArr['special_price'] * $product_inv['available_qty'];
														}else{
															$price = $getProductsInfo['webshop_price'];
															$total_price=$getProductsInfo['webshop_price'] * $product_inv['available_qty'];
														}

													}


													$this->updateCartItemQty($shopcode,$quote_id,$item_id,$product_inv['available_qty'],$price,$total_price,$tax_percent,$tax_amount);

												}


											}



										}

									}
								}

							}

						}//end foreach

					}

					if(isset($comma_seperated_quote_ids)){
						$this->deleteQuoteIds($shopcode,$comma_seperated_quote_ids);
						$this->deleteQuoteItemIds($shopcode,$comma_seperated_quote_ids);
						$this->deleteQuoteAddresssIds($shopcode,$comma_seperated_quote_ids);
						$this->deleteQuotePayment($shopcode,$comma_seperated_quote_ids);
					}

				}



			$this->updateCartDataBasedOnCustomerType($shopcode,$shopid,$customer_type_id,$quote_id, $vat_percent_session);

				if(isset($quote_id) && $quote_id != '')
				{
						$ch_obj = new DbCheckout();
						$ch_obj->updateTaxAndShippingCharges($shopcode,$shopid,$quote_id);
						$cart_obj->UpateQuoteTotal($shopcode,$quote_id);

						$updateCurrency = $cart_obj->updateQuoteCurrency($shopcode,$quote_id,$currency_name,$currency_code_session,$currency_conversion_rate,$default_currency_flag,$currency_symbol);
				}



		}else{


				$new_Quote_id = '';

				$quote_ids_previous = $this->getAllQuoteByCustomerId($shopcode,$loginID,$quote_id);


				if(!empty($quote_ids_previous)){

					foreach($quote_ids_previous as $quote_ids){

						$array_quote_ids[] = $quote_ids['quote_id'];
					}

					$comma_seperated_quote_ids = implode(',',$array_quote_ids);
					$quote_item = $this->getAllQuoteItem($shopcode,$comma_seperated_quote_ids);

					$prelauch_product_list = $this->getPrelauchProductList($shopcode);
					$customer_active_prelauch = $this->getCustomerPrelaunchStatus($shopcode,$loginID);
					// $allow_catlog_builder = $this->getCustomerCatlogBuilderStatus($shopcode,$loginID);

					if(!empty($quote_item)) {

						foreach($quote_item as $item){

							$isPrelaunch = 0;
							$isInprelaunchList = '';

							if($item['product_type'] == 'simple'){
								$prelauch_product_id = $item['product_id'];
							}else{
								$prelauch_product_id = $item['parent_product_id'];
							}



							if(!empty($prelauch_product_list)){
								$HiddenProducts = explode(',',$prelauch_product_list['assigned_products']);
								if (in_array($prelauch_product_id, $HiddenProducts)) {
									$isInprelaunchList = "yes";
								} else {
									$isInprelaunchList = "no";
								}
							}

							if($customer_active_prelauch['access_prelanch_product'] == 1 && $isInprelaunchList == 'yes' && $item['prelaunch']){
								$isPrelaunch = 1;
							}

							$getProductsInfo = $this->getProductsDetails($shopcode,$item['product_id'],$isPrelaunch);

							if(!empty($getProductsInfo)){

								if($getProductsInfo['product_type'] == 'conf-simple'){
									$getConfigProductsInfo = $this->getProductsDetails($shopcode,$getProductsInfo['parent_id'],$isPrelaunch);
									$customer_type_ids = explode(",", $getConfigProductsInfo['customer_type_ids']);
								}else{
									$customer_type_ids = explode(",", $getProductsInfo['customer_type_ids']);
								}



								if(in_array($customer_type_id, $customer_type_ids) || $getProductsInfo['customer_type_ids'] == 0) {

									if($getProductsInfo['product_inv_type'] == 'buy'){
										$product_inv = $webshop_obj->getAvailableInventory($getProductsInfo['id'],$shopcode);

									}else{
										$seller_shopcode = 'shop'.$getProductsInfo['shop_id'];
										$product_inv = $webshop_obj->getAvailableInventory($getProductsInfo['id'],$shopcode,$seller_shopcode);

									}


									$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);
									if(isset($vat_percent_session) && $vat_percent_session !='') {

										if($specialPriceArr!=false){
											$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
											$price = $eu_special_price ;
											$total_price = $eu_special_price * $item['qty_ordered'];
										}else{
											$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
											$price = $eu_price;
											$total_price = $eu_price * $item['qty_ordered'];
										}

									}else{
										if($specialPriceArr!=false){

											$price = $specialPriceArr['special_price'];
											$total_price = $specialPriceArr['special_price'] * $item['qty_ordered'];
										}else{
											$price = $getProductsInfo['webshop_price'];
											$total_price=$getProductsInfo['webshop_price'] * $item['qty_ordered'];
										}
									}


									if($getProductsInfo['product_type'] == 'conf-simple'){

										$VariantInfo=$cart_obj->get_product_variant_details($shopcode,$getProductsInfo['parent_id'],$getProductsInfo['id']);

										$product_variants_arr=array();
										$product_variants_str='';

										if(is_array($VariantInfo) && count($VariantInfo)>0  && $VariantInfo!=false){
											foreach($VariantInfo  as $value){
												$attr_id=$value['attr_id'];
												$attr_value=$value['attr_value'];

												$AttrData=$cart_obj->getAttributeDetails($attr_id);

												if($AttrData==false){
													$attr_name='';
												}else{
													$attr_name=$AttrData['attr_name'];
												}


												$AttrOptionData=$cart_obj->getAttributeOptionDetails($attr_value);
												if($AttrOptionData==false){
													$attr_option_name='';
												}else{
													$attr_option_name=$AttrOptionData['attr_options_name'];
												}


												if($attr_name!='' && $attr_option_name!=''){
													$product_variants_arr[] = array($attr_name => $attr_option_name);
												}
											}
										}


										if(isset($product_variants_arr) && count($product_variants_arr)>0){
											$product_variants_str=json_encode($product_variants_arr);
										}else{
											$product_variants_str='';
										}

									}else{
										$product_variants_str='';
									}


									if($getProductsInfo['product_type'] == 'conf-simple'){
										$getConfigProductsInfo = $this->getProductsDetails($shopcode,$getProductsInfo['parent_id'],$isPrelaunch);
										$product_code=$getConfigProductsInfo['product_code'];
									}else{
										$product_code=$getProductsInfo['product_code'];
									}


									$cart_product_id=$getProductsInfo['id'];
									//$product_variants_str='';
									$product_name=$getProductsInfo['name'];
									$sku=$getProductsInfo['sku'];
									$barcode=$getProductsInfo['barcode'];
									if($getProductsInfo['parent_id'] > 0){
										$parent_product_id=$getProductsInfo['parent_id'];
									}else{
										$parent_product_id='';
									}
									$product_type=$getProductsInfo['product_type'];
									$product_inv_type=$getProductsInfo['product_inv_type'];
									$pro_shop_id=$getProductsInfo['shop_id'];
									if(isset($vat_percent_session) && $vat_percent_session !='') {
										$tax_percent=$vat_percent_session;
										$tax_amount=($getProductsInfo['price'] * $vat_percent_session/100);
									}else{
										$tax_percent=$getProductsInfo['tax_percent'];
										$tax_amount=$getProductsInfo['tax_amount'];
									}

									if($loginID!=''){
										$created_by=$loginID;
									}else{
										$created_by='';
									}

									//if(!empty($getProductsInfo) && $product_inv['available_qty'] > 0){
									if((!empty($getProductsInfo) && $product_inv['available_qty'] > 0) || $isPrelaunch == 1){



										if($new_Quote_id==''){
											$new_Quote_id=$cart_obj->add_to_sales_quote($shopcode,$shopid,$LoginToken,$loginID);
										}


										if($isPrelaunch == 1){

											$quantity = $item['qty_ordered'];
											$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);
											if(isset($vat_percent_session) && $vat_percent_session !='') {

												if($specialPriceArr!=false){
													$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
													$price = $eu_special_price ;
													$total_price = $eu_special_price * $quantity;
												}else{
													$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
													$price = $eu_price;
													$total_price = $eu_price * $quantity;
												}

											}else{
												if($specialPriceArr!=false){
													$price = $specialPriceArr['special_price'];
													$total_price = $specialPriceArr['special_price'] * $quantity;
												}else{
													$price = $getProductsInfo['webshop_price'];
													$total_price=$getProductsInfo['webshop_price'] * $quantity;
												}
											}


										}else{

											if($product_inv['available_qty'] >= $item['qty_ordered']){

												$quantity = $item['qty_ordered'];
												$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);
												if(isset($vat_percent_session) && $vat_percent_session !='') {

													if($specialPriceArr!=false){
														$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
														$price = $eu_special_price ;
														$total_price = $eu_special_price * $quantity;
													}else{
														$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
														$price = $eu_price;
														$total_price = $eu_price * $quantity;
													}

												}else{
													if($specialPriceArr!=false){
														$price = $specialPriceArr['special_price'];
														$total_price = $specialPriceArr['special_price'] * $quantity;
													}else{
														$price = $getProductsInfo['webshop_price'];
														$total_price=$getProductsInfo['webshop_price'] * $quantity;
													}

												}

											}else{

												$quantity = $product_inv['available_qty'];
												$specialPriceArr = $webshop_obj->getSpecialPrices($shopcode,$getProductsInfo['id'],$customer_type_id);
												if(isset($vat_percent_session) && $vat_percent_session !='') {

													if($specialPriceArr!=false){
														$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent_session/100) + $specialPriceArr['special_price'];
														$price = $eu_special_price ;
														$total_price = $eu_special_price * $quantity;
													}else{
														$eu_price = ($getProductsInfo['price'] * $vat_percent_session/100) + $getProductsInfo['price'];
														$price = $eu_price;
														$total_price = $eu_price * $quantity;
													}

												}else{
													if($specialPriceArr!=false){
														$price = $specialPriceArr['special_price'];
														$total_price = $specialPriceArr['special_price'] * $quantity;
													}else{
														$price = $getProductsInfo['webshop_price'];
														$total_price=$getProductsInfo['webshop_price'] * $quantity;
													}
												}

											}


										}



										$cart_item_added=$cart_obj->add_to_sales_quote_item($shopcode,$getProductsInfo['shop_id'],$new_Quote_id,$product_type,$product_inv_type,$cart_product_id,$product_name,$product_code,$quantity,$sku,$barcode,$price,$total_price,$getProductsInfo['shop_id'],$created_by,$parent_product_id,$product_variants_str,$tax_percent,$tax_amount,$isPrelaunch);

									}
								}

							}

						}//end foreach

					}

					if(isset($new_Quote_id) && $new_Quote_id != '')
					{
						$ch_obj = new DbCheckout();
						$ch_obj->updateTaxAndShippingCharges($shopcode,$shopid,$new_Quote_id);
						$cart_obj->UpateQuoteTotal($shopcode,$new_Quote_id);

						$updateCurrency = $cart_obj->updateQuoteCurrency($shopcode,$new_Quote_id,$currency_name,$currency_code_session,$currency_conversion_rate,$default_currency_flag,$currency_symbol);
					}



					if(isset($comma_seperated_quote_ids)){
						$this->deleteQuoteIds($shopcode,$comma_seperated_quote_ids);
						$this->deleteQuoteItemIds($shopcode,$comma_seperated_quote_ids);
						$this->deleteQuoteAddresssIds($shopcode,$comma_seperated_quote_ids);
						$this->deleteQuotePayment($shopcode,$comma_seperated_quote_ids);
					}

				}

		}

		$userDetailsArr = array('LoginToken'=>$LoginToken,'LoginID'=>$loginID,'FirstName'=>$first_name,'LastName'=>$last_name,'EmailID'=>$email,'Remember'=>$remember,'customer_type_id'=>$customer_type_id,'QuoteId'=>$new_Quote_id);
// ,'access_prelanch_product'=>$customer_active_prelauch['access_prelanch_product'],'allow_catlog_builder'=>$allow_catlog_builder['allow_catlog_builder']
		return $userDetailsArr;

  	}

	/*public function commonLoginFunction($shopcode,$shopid,$loginID,$first_name,$last_name,$email,$customer_type_id,$quote_id='',$remember='')
  	{
  		$time = time();
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$Token = '';
		for ($i = 0; $i < 20; $i++) {
			$Token .= $characters[rand(0, $charactersLength - 1)];
		}

		$LoginToken = $Token;
		$insert_login_session = array(
			'sessionid' => "'".$LoginToken."'",
			'user_id' => "'".$loginID."'",
			'login_time' => $time,
			'ip' => "'".$_SERVER['REMOTE_ADDR']."'"
		);

		$this->insert_login_session($shopcode,$insert_login_session);

		if($quote_id!=''){
			$this->updateCartDataBasedOnCustomerType($shopcode,$shopid,$customer_type_id,$quote_id);
		}

		$userDetailsArr = array('LoginToken'=>$LoginToken,'LoginID'=>$loginID,'FirstName'=>$first_name,'LastName'=>$last_name,'EmailID'=>$email,'Remember'=>$remember,'customer_type_id'=>$customer_type_id);

		return $userDetailsArr;

  	}
	 */


	  public function getAllQuoteByCustomerId($shopcode,$loginID,$quote_id='')
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

		$sub_query ='';
		if($quote_id !=''){
			$sub_query = "and quote_id != $quote_id";
		}

  		$param = array($loginID);
		$query = "SELECT $shop_db.sales_quote.quote_id FROM $shop_db.sales_quote WHERE $shop_db.sales_quote.customer_id = ? $sub_query";


  		$quote_ids = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $quote_ids;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	  public function getAllQuoteItem($shopcode,$quote_ids)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		//$param = array($loginID,$quote_id);
		$query = "SELECT $shop_db.sales_quote_items.sku, $shop_db.sales_quote_items.product_id, $shop_db.sales_quote_items.parent_product_id, $shop_db.sales_quote_items.shop_id, $shop_db.sales_quote_items.prelaunch, $shop_db.sales_quote_items.product_type, SUM($shop_db.sales_quote_items.qty_ordered) as qty_ordered FROM $shop_db.sales_quote_items WHERE quote_id in ($quote_ids) group by shop_id, product_id";

  		$quote_ids = $this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $quote_ids;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function getProductsDetails($shopcode,$product_id,$isPrelaunch){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		//$param = array($loginID,$quote_id);
		$date = strtotime(date('d-m-Y'));

		$sub_query = '';
		if($isPrelaunch != 1){
			$sub_query = "AND $shop_db.products.status = 1 AND $shop_db.products.launch_date <= $date ";
		}


		$query = "SELECT $shop_db.products.* FROM $shop_db.products WHERE $shop_db.products.id = $product_id AND $shop_db.products.remove_flag = 0 $sub_query";


  		$quote_ids = $this->dbl->dbl_conn->rawQueryOne($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $quote_ids;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function checkQuoteItemDataExistById($shopcode,$quote_id, $shop_id, $product_id)
	 {
		 $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		if(isset($parent_product_id) && $parent_product_id!=''){
			$sql =  "SELECT * FROM $shop_db.sales_quote_items where shop_id = '$shop_id' AND `product_id` = '$product_id'  AND quote_id = $quote_id ";
		}else{
			 $sql =  "SELECT * FROM $shop_db.sales_quote_items where  shop_id = '$shop_id' AND `product_id` = '$product_id' AND quote_id = $quote_id ";
		}
		 $row  = $this->dbl->dbl_conn->rawQueryOne($sql);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	 public function deleteQuoteIds($shopcode,$comma_seperated_quote_ids){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		//$param = array($loginID,$quote_id);
		$query = "Delete FROM $shop_db.sales_quote WHERE quote_id in ($comma_seperated_quote_ids)";


  		$this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	public function deleteQuoteItemIds($shopcode,$comma_seperated_quote_ids){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		//$param = array($loginID,$quote_id);
		$query = "Delete FROM $shop_db.sales_quote_items WHERE quote_id in ($comma_seperated_quote_ids)";


  		$this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function deleteQuoteAddresssIds($shopcode,$comma_seperated_quote_ids){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		//$param = array($loginID,$quote_id);
		$query = "Delete FROM $shop_db.sales_quote_address WHERE quote_id in ($comma_seperated_quote_ids)";


  		$this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function deleteQuotePayment($shopcode,$comma_seperated_quote_ids){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		//$param = array($loginID,$quote_id);
		$query = "Delete  FROM $shop_db.sales_quote_payment WHERE quote_id in ($comma_seperated_quote_ids)";


  		$this->dbl->dbl_conn->rawQuery($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}


	public function updateCartItemQty($shopcode,$quote_id,$item_id,$qty_ordered,$price,$total_price,$tax_percent,$tax_amount){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
		$main_db = DB_NAME; //Constant variable

		$params=array($qty_ordered,$price,$total_price,$tax_percent,$tax_amount,time(),$quote_id,$item_id);

		$update_row = $this->dbl->dbl_conn->rawQueryOne("UPDATE  $shop_db.sales_quote_items set qty_ordered = ?, price = ?, total_price = ?, tax_percent = ?, tax_amount = ?, updated_at = ?  where quote_id= ? AND item_id = ? ",$params);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			$flag=true;
			if ($this->dbl->dbl_conn->count > 0){
				return $flag;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function getPrelauchProductList($shopcode){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable

		$query = "SELECT assigned_products FROM $shop_db.products_block_master join $shop_db.products_block_details on products_block_master.id = products_block_details.pb_master_id where products_block_master.block_identifier = 'prelauch'";

		$row = $this->dbl->dbl_conn->rawQueryOne($query);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}


	public function getCustomerPrelaunchStatus($shopcode,$loginID){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable

		$param = array($loginID);
		$query = "SELECT access_prelanch_product FROM $shop_db.customers where id = ?";
		$row = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row;
			}else{
				return false;
			}
		}else{
			return false;
		}


	}

	public function getCustomerCatlogBuilderStatus($shopcode,$loginID){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable

		$param = array($loginID);
		$query = "SELECT allow_catlog_builder FROM $shop_db.customers where id = ?";
		$row = $this->dbl->dbl_conn->rawQueryOne($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $row;
			}else{
				return false;
			}
		}else{
			return false;
		}


	}

  	public function updateCartDataBasedOnCustomerType($shopcode,$shopid,$customer_type_id,$quote_id, $vat_percent='')
  	{
  		$webshop_obj = new DbCheckout();
		$product_obj = new DbProductFeature();
		$cart_obj = new DbCart();

		$OrderItems = $webshop_obj->get_sales_quote_items($shopcode,$quote_id);

		if($OrderItems!=false){
			foreach ($OrderItems as $value) {
				$item_id = $value['item_id'];
				if($value['product_type']=='simple'){
					$product_id = $value['product_id'];
				}else{
					$product_id = $value['parent_product_id'];
				}

				$product_datails = $product_obj->getproductDetailsById($shopcode,$shopid,$product_id);

				$customer_type_ids = explode(",", $product_datails['customer_type_ids']);

				if(in_array($customer_type_id, $customer_type_ids) || $product_datails['customer_type_ids'] == 0) {

					$specialPriceArr = $product_obj->getSpecialPrices($shopcode,$value['product_id'],$customer_type_id);

					if($specialPriceArr!=false){
						//$price = $specialPriceArr['special_price'];
						//$total_price = $specialPriceArr['special_price'] * $value['qty_ordered'];

						if(isset($vat_percent) && $vat_percent !='') {
							$eu_special_price = ($specialPriceArr['special_price'] * $vat_percent/100) + $specialPriceArr['special_price'];
							$price = $eu_special_price ;
							$total_price = $eu_special_price * $value['qty_ordered'];
						}else{
							$price = $specialPriceArr['special_price'];
							$total_price = $specialPriceArr['special_price'] * $value['qty_ordered'];
						}	

						$cart_obj->updateCartItems($shopcode,$quote_id,$item_id,$price,$total_price);
						$webshop_obj->updateTaxAndShippingCharges($shopcode,$shopid,$quote_id);
						$cart_obj->UpateQuoteTotal($shopcode,$quote_id);
					}
				}else{
					$cartData = $cart_obj->removeCartItem($shopcode,$quote_id,$item_id);
					$webshop_obj->updateTaxAndShippingCharges($shopcode,$shopid,$quote_id);
					$cart_obj->UpateQuoteTotal($shopcode,$quote_id);
				}
			}
		}
  	}

 	public function checkzin($shopcode,$email,$password)
    {
		$external_api = $this->getExternalAPI($shopcode);

		$salt = $external_api['api_salt'];
		$token = $external_api['api_token'];
		$requester_domain = $external_api['requester_domain'];

		$dhash = time();
		//$login= 'login';
		$login= 'verify_user';

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
		curl_setopt($ch, CURLOPT_URL,$external_api['api_url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
    	curl_setopt($ch, CURLOPT_REFERER, $requester_domain);  // USE DOMAIN OF REQUESTER
    	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: */*']);

		// curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		$xml = curl_exec($ch);
		curl_close($ch);

		$xml = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
		$array = json_decode(json_encode($xml));

		return $array;
	}


	public function checkzin1($shopcode,$email,$password)
    {
		echo "2222";
		$external_api = $this->getExternalAPI($shopcode);
		echo '<pre>'.print_r($external_api, '\n').'</pre>';

		$salt = $external_api['api_salt'];
		$token = $external_api['api_token'];
		$requester_domain = $external_api['requester_domain'];

		$dhash = time();
		//$login= 'login';
		$login= 'verify_user';

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
		curl_setopt($ch, CURLOPT_URL,$external_api['api_url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
    	curl_setopt($ch, CURLOPT_REFERER, $requester_domain);  // USE DOMAIN OF REQUESTER
    	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: */*']);

		// curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		$xml = curl_exec($ch);
		curl_close($ch);

		$xml = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
		$array = json_decode(json_encode($xml));

		echo '<pre>'.print_r($array, '\n').'</pre>'; exit;

		return $array;
	}

	public function ip_visitor_country()
	{
		$remote  = $_SERVER['REMOTE_ADDR'];
		$country  = "Unknown";

		$ip = $remote;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.geoplugin.net/json.gp?ip=".$ip);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$ip_data_in = curl_exec($ch); // string
		curl_close($ch);

		$ip_data = json_decode($ip_data_in,true);
		$ip_data = str_replace('&quot;', '"', $ip_data);
		if($ip_data && $ip_data['geoplugin_countryName'] != null ) {
			$country = $ip_data['geoplugin_countryName'];
			$cCode = $ip_data['geoplugin_countryCode'];

		}
		return $cCode;
	}


	public function file_get_contents_curl($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

	public function  getproductscountsbycategoryid($shopcode,$category_id,$product_ids_str)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		//$main_db = DB_NAME; //Constant variable


  		$param = array($category_id,0);

  		$sub_query= "AND pc.product_id  IN (".$product_ids_str.") ";

  		$query = "SELECT COUNT(prod.id) as product_count from $shop_db.products as prod, $shop_db.products_category as pc where prod.id = pc.product_id 
			AND pc.category_ids= ?  
			AND prod.remove_flag=? 
			$sub_query
			";

  		$product_count = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				// echo $this->dbl->dbl_conn->getLastQuery();
				return $product_count;

			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function  getproductsbycategoryid($shopcode,$category_id,$product_ids_str)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		//$main_db = DB_NAME; //Constant variable
		$param = array($category_id,0);
		$sub_query= "AND pc.product_id  IN (".$product_ids_str.") ";
  		$query = "SELECT prod.product_type,pc.product_id  from $shop_db.products as prod, $shop_db.products_category as pc where prod.id = pc.product_id 
			AND pc.category_ids= ?  
			AND prod.remove_flag=? 
			$sub_query ";

  		$product_data = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $product_data;
				// echo $this->dbl->dbl_conn->getLastQuery();
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

	public function getAllCategories($shopcode,$shopid,$catlog_id,$product_ids_str)
  	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array(1,0);

  		$query = "SELECT main_cat.id,main_cat.cat_name,main_cat.cat_level,main_cat.slug FROM $main_db.category as main_cat INNER JOIN $shop_db.fbc_users_category_b2b as b2b ON main_cat.id=b2b.category_id  WHERE main_cat.status=? AND b2b.level=? ";


		$mainCatMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->count > 0)
		{
			$final_arr = array();

			foreach($mainCatMenu as $cat)
			{
				// print_r($cat);
				$arr = array();
				$arr['id'] = $cat['id'];
				$arr['menu_name'] = $cat['cat_name'];
				$arr['menu_level'] = $cat['cat_level'];
				$arr['slug'] = $cat['slug'];
				$arr['category_id'] = $cat['id'];

				$product_count = $this->getproductscountsbycategoryid($shopcode,$arr['category_id'],$product_ids_str);
				$arr['product_count'] = $product_count[0]['product_count'];

				if($arr['product_count'] !=0 )
				{
					$product_data = $this->getproductsbycategoryid($shopcode,$arr['category_id'],$product_ids_str);
					$arr['product_data'] = $product_data;
				}
				$firstLevelCategory = $this->firstLevelCategory($shopcode,$cat['id']);
				$firstLevelCatePro = array();

				if($firstLevelCategory != false)
				{
					foreach($firstLevelCategory as $cat1)
					{
						$arr1 = array();
						$arr1['id'] = $cat1['id'];
						$arr1['menu_name'] = $cat1['cat_name'];
						$arr1['menu_level'] = $cat1['cat_level'];
						$arr1['slug'] = $cat1['slug'];
						$arr1['category_id'] = $cat1['id'];

						$product_count1 = $this->getproductscountsbycategoryid($shopcode,$arr1['category_id'],$product_ids_str);
						$arr1['product_count1'] = $product_count1[0]['product_count'];

						if($arr1['product_count1'] !=0 )
						{
							$product_data1 = $this->getproductsbycategoryid($shopcode,$arr1['category_id'],$product_ids_str);
							$arr1['product_data1'] = $product_data1;

							foreach($product_data1 as $product)
							{
								$final_product_data1 = array();

								$firstLevelCatePro[$product['product_id']] = $product['product_id'];

								$real_product_data1 = $this->getproductsbyid($shopcode,$product['product_id']);

								$collection_name = $this->getcollection_name($shopcode,$shopid,$product['product_id']);
								if(isset($collection_name) && $collection_name !='')
								{
									$real_product_data1['collection_name'] = $collection_name[0]['attr_options_name'];
								}else{
									$real_product_data1['collection_name']='';
								}

								$style_code = $this->getstylecodeById($shopcode,$product['product_id']);
								if(isset($style_code) && $style_code !='')
								{
									$real_product_data1['style_code'] = $style_code[0]['attr_value'];
								}else{
									$real_product_data1['style_code']='';
								}

								$final_product_data1['real_product_data1'] = $real_product_data1;

								$table = 'catalog_builder_items';
								$flag = 'own';
								$where='';
								if($product['product_type'] == 'simple'){
									$where = 'product_id  = '.$product['product_id'].' AND catalog_builder_id ='.$catlog_id.'';
								}else
								{
									$where = 'parent_id  = '.$product['product_id'].' AND catalog_builder_id ='.$catlog_id.'';
								}

								$catalog_builder_items  = $this->getTableData($shopcode,$table,$flag,$where);


								if($product['product_type'] == 'simple'){
									$available_quantity= $this->getProductInventory($shopcode,$product['product_id']);
									$catalog_builder_items[0]['available_quantity']= $available_quantity['available_qty'];
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items[0];

								}else{

									$item_count = count($catalog_builder_items);
									for($i=0;$i<$item_count;$i++)
									{
										// print_r($catalog_builder_items[$i]);
										$product_data2=$this->getproductsbyid($shopcode,$catalog_builder_items[$i]['product_id']);
										$available_quantity= $this->getProductInventory($shopcode,$catalog_builder_items[$i]['product_id']);
										$product_data2[$i]['available_quantity']=$available_quantity['available_qty'];
										$catalog_builder_items[$i]['available_quantity'] = $available_quantity['available_qty'];

									}
									// print_r($catalog_builder_items);die();
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items;
								}




								// print_r($final_product_data1);//die();
								$arr1['final_product_data1'][] = $final_product_data1;

							}

						}



						$secondLevelCategory = $this->secondLevelCategory($shopcode,$cat1['id'],$cat['id']);
						if($secondLevelCategory != false)
						{
							foreach($secondLevelCategory as $cat2) {
								$arr2['id'] = $cat2['id'];
								$arr2['menu_name'] = $cat2['cat_name'];
								$arr2['menu_level'] = $cat2['cat_level'];
								$arr2['slug'] = $cat2['slug'];
								$arr2['category_id'] = isset($cat2['id']) ? $cat2['id'] : '';
								// $arr2['category_id'] = isset($cat2['category_id']) ? $cat2['category_id'] : '';
							// 	if($Identifier !='' && $Identifier !=false ) {
							// 		$customer_type_id = (isset($customer_type_id) ? $customer_type_id : 1);
							// 		echo "<pre>";
							// print_r($customer_type_id);
							// echo "<br>";
							// print_r($Identifier);
							// echo "<br>";
							// print_r($arr2['category_id']);
									$product_count2 = $this->getproductscountsbycategoryid($shopcode,$arr2['category_id'],$product_ids_str );
							// print_r($product_count2);
									$arr2['product_count2'] = $product_count2[0]['product_count'];
								// }
								$arr1['menu_level_2'][] = $arr2;
							}
						}
						// print_r($arr1);
						$arr['menu_level_1'][] = $arr1;
					}

					// print_r($firstLevelCatePro); echo count($firstLevelCatePro);
				} else {
					if($arr['product_count'] !=0 )
					{
						    // $product_data 33, 1
							// $firstLevelCatePro 1
							foreach($product_data as $product)
							{
								$final_product_data1 = array();

								$real_product_data1 = $this->getproductsbyid($shopcode,$product['product_id']);

								$collection_name = $this->getcollection_name($shopcode,$shopid,$product['product_id']);
								if(isset($collection_name) && $collection_name !='')
								{
									$real_product_data1['collection_name'] = $collection_name[0]['attr_options_name'];
								}else{
									$real_product_data1['collection_name']='';
								}

								$style_code = $this->getstylecodeById($shopcode,$product['product_id']);
								if(isset($style_code) && $style_code !='')
								{
									$real_product_data1['style_code'] = $style_code[0]['attr_value'];
								}else{
									$real_product_data1['style_code']='';
								}

								$final_product_data1['real_product_data1'] = $real_product_data1;

								$table = 'catalog_builder_items';
								$flag = 'own';
								$where='';
								if($product['product_type'] == 'simple'){
									$where = 'product_id  = '.$product['product_id'].' AND catalog_builder_id ='.$catlog_id.'';
								}else
								{
									$where = 'parent_id  = '.$product['product_id'].' AND catalog_builder_id ='.$catlog_id.'';
								}

								$catalog_builder_items  = $this->getTableData($shopcode,$table,$flag,$where);


								if($product['product_type'] == 'simple'){
									$available_quantity= $this->getProductInventory($shopcode,$product['product_id']);
									$catalog_builder_items[0]['available_quantity']= $available_quantity['available_qty'];
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items[0];

								}else{

									$item_count = count($catalog_builder_items);
									for($i=0;$i<$item_count;$i++)
									{
										// print_r($catalog_builder_items[$i]);
										$product_data2=$this->getproductsbyid($shopcode,$catalog_builder_items[$i]['product_id']);
										$available_quantity= $this->getProductInventory($shopcode,$catalog_builder_items[$i]['product_id']);
										$product_data2[$i]['available_quantity']=$available_quantity['available_qty'];
										$catalog_builder_items[$i]['available_quantity'] = $available_quantity['available_qty'];

									}
									// print_r($catalog_builder_items);die();
									$final_product_data1['catalog_builder_items'] = $catalog_builder_items;
								}




								// print_r($final_product_data1);//die();
								$arr['final_product_data'][] = $final_product_data1;

							}

					}
				}

				$final_arr[] = $arr;
			}

			return $final_arr;
		}else{
			return false;
		}

  	}

  	public function firstLevelCategory($shopcode,$category_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array(1,1,$category_id);

  		$query = "SELECT cat_level1.id,cat_level1.cat_name,cat_level1.cat_level,cat_level1.slug FROM $main_db.category as cat_level1 INNER JOIN $shop_db.fbc_users_category_b2b as b2b ON cat_level1.id=b2b.category_id  WHERE cat_level1.status=? AND b2b.level=? AND cat_level1.parent_id=? ";

		$level1CatMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $level1CatMenu;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function secondLevelCategory($shopcode,$cat_parent_id,$cat_main_parent_id)
	{
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array(1,2,$cat_parent_id,$cat_main_parent_id);

  		$query = "SELECT cat_level2.id,cat_level2.cat_name,cat_level2.cat_level,cat_level2.slug FROM $main_db.category as cat_level2 INNER JOIN $shop_db.fbc_users_category_b2b as b2b ON cat_level2.id=b2b.category_id WHERE cat_level2.status=? AND b2b.level=? AND cat_level2.parent_id=? AND cat_level2.main_parent_id=? ";

		$level2CatMenu = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $level2CatMenu;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function getproductsbyid($shopcode,$id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		//$main_db = DB_NAME; //Constant variable
		$param = array($id,0);

  		$query = "SELECT prod.id,prod.product_type,prod.name,prod.base_image,prod.product_code,prod.sku,prod.webshop_price from $shop_db.products as prod WHERE
			  prod.id=? AND prod.remove_flag=? ";

  		$product_data = $this->dbl->dbl_conn->rawQuery($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				 return $product_data;
				// echo $this->dbl->dbl_conn->getLastQuery();
			}else{
				return false;
			}
		}else{
			return false;
		}
  	}

  	public function getProductInventory($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);
		$query = "SELECT $shop_db.products_inventory.available_qty FROM $shop_db.products_inventory WHERE $shop_db.products_inventory.product_id = ?";
		$inventory = $this->dbl->dbl_conn->rawQueryOne($query,$param);

  		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $inventory; // return result
			}else{
				return false;
			}

		}else{
			return false;
		}

  	}

  	public function getProductVariantByProductId($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);
		$query = "SELECT eav_attr.id, eav_attr.attr_code, eav_attr.attr_name FROM $shop_db.products_variants_master as pv_m INNER JOIN $main_db.eav_attributes as eav_attr ON eav_attr.id=pv_m.attr_id WHERE pv_m.product_id=? ORDER BY position ASC";

  		$variant_prod = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $variant_prod;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function getstylecodeById($shopcode,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id);

  		$query = "SELECT prd_attr.* FROM $shop_db.products_attributes as prd_attr, $main_db.eav_attributes as eav_attr WHERE eav_attr.attr_code='style_code' AND  prd_attr.product_id=?  AND prd_attr.attr_id=eav_attr.id;";

		// $query = "SELECT prd_attr.* FROM $shop_db.products_attributes as prd_attr  WHERE prd_attr.product_id=?  AND prd_attr.attr_id=8 ";

  		$attr_data = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $attr_data;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

  	public function getcollection_name($shopcode,$shopid,$product_id)
  	{
  		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable

  		$param = array($product_id,$shopid);
  		$query="SELECT attr_opt.attr_options_name FROM $shop_db.products_attributes as prd_attr, $main_db.eav_attributes_options as attr_opt ,$main_db.eav_attributes as eav_attr WHERE prd_attr.product_id=? AND eav_attr.attr_code='collection_name' AND eav_attr.shop_id=? AND prd_attr.attr_value=attr_opt.id AND prd_attr.attr_id=attr_opt.attr_id";

		// $query = "SELECT attr_opt.attr_options_name FROM $shop_db.products_attributes as prd_attr, $main_db.eav_attributes_options as attr_opt WHERE prd_attr.product_id=?  AND prd_attr.attr_id=1244 AND prd_attr.attr_value=attr_opt.id";

  		$attr_data = $this->dbl->dbl_conn->rawQuery($query,$param);

		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $attr_data;
			}else{
				return false;
			}
		}else{
			return false;
		}

  	}

	public function getFbcUsersShopDetails($shopid)
	{

		$main_db = DB_NAME; //Constant variable

		$param = array($shopid);
		$query = "SELECT fsd.* FROM $main_db.fbc_users_shop as fsd WHERE fsd.shop_id = ?";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Row;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	public function getShopVatDetails($shopcode,$shop_id,$country_code){

		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable

		$param = array($country_code);
		$query = "SELECT * FROM $shop_db.vat_settings  WHERE country_code = ?";

		$Row = $this->dbl->dbl_conn->rawQueryOne($query,$param);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $Row;
			}else{
				return false;
			}
		}else{
			return false;
		}


	}


	public function getDelayWarehouseTime($shopcode)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		 $get_cust_variable =  "SELECT * FROM $shop_db.custom_variables where  `identifier` = 'delay_warehouse'";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($get_cust_variable);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $query;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

     public function get_currency_list($shopcode){
         $table_name = 'multi_currencies';
         $database_flag = 'own';
         $where = 'status = 1 and remove_flag = 0';
         $currencies = $this->getTableData($shopcode, $table_name, $database_flag, $where);
         $result = [];
         foreach($currencies as $currency){
             $result[$currency['code']] = $currency;
         }

         return $result;
     }


    public function getProductVariants($shopcode, $product_id): array
    {
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
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
        if ($this->dbl->dbl_conn->getLastErrno() === 0){
            if ($this->dbl->dbl_conn->count > 0){
                $results = [];
                foreach($dbProductAttributes as $attribute){
                    $results[] = [$attribute['attr_name'] => $attribute['attr_options_name']];
                }

                return $results;
            }
        }
        return [];
    }

    public function getShippingMethodName($shopcode, $shipping_method_id) {
        $shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
        $query =  "SELECT * FROM $shop_db.shipping_methods WHERE id = ?";

        $shipping_method = $this->dbl->dbl_conn->rawQueryOne($query, [$shipping_method_id]);

        return $shipping_method['ship_method_name'] ?? '';
    }

    public function getGeneralLogZinApiStatus($shopcode)
	 {
		$shop_db =  DB_NAME_SHOP_PRE.$shopcode; // constant variable
  		$main_db = DB_NAME; //Constant variable
		 $get_cust_variable =  "SELECT * FROM $shop_db.custom_variables where  `identifier` = 'general_log_zinapi'";
		 $query  = $this->dbl->dbl_conn->rawQueryOne($get_cust_variable);
		if ($this->dbl->dbl_conn->getLastErrno() === 0){
			if ($this->dbl->dbl_conn->count > 0){
				return $query;
			}else{
				return false;
			}
		}else{
			return false;
		}

	 }

	// 
	 public function general_log_zinapi_insert($shopcode,$email,$password,$checkzin)
    {
    	$externalApi = $this->getExternalAPI($shopcode);
		$saltApi = $externalApi['api_salt'];
		$tokenApi = $externalApi['api_token'];
		$requester_domainApi = $externalApi['requester_domain'];
		$dhashApi = time();
		$loginApi= 'verify_user';
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
		$input_param =json_encode($post);
		$output_param =json_encode($checkzin);
		$api_url = $externalApi['api_url'];
		$requester_domain = $requester_domainApi;
		$table = 'general_logs';
		$columns = 'type, input_param, output_param, api_url, requester_domain';
		$values = '?, ?, ?, ?, ?';
		$params = array($type, $input_param, $output_param, $api_url, $requester_domain);
		$insert_general_log = $this->add_row($shopcode, $table, $columns, $values, $params);
	}

}